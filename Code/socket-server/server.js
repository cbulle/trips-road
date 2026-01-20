// ========================================
// SERVEUR SOCKET.IO - MESSAGERIE TEMPS RÉEL
// Fichier : socket-server/server.js
// ========================================

// ============ IMPORTS ============
const express = require('express');
const app = express();
const http = require('http').createServer(app);
const io = require('socket.io')(http, {
  cors: {
    origin: process.env.ALLOWED_ORIGIN || "http://localhost",
    methods: ["GET", "POST"],
    credentials: true
  },
  pingTimeout: 60000,
  pingInterval: 25000
});
const mysql = require('mysql2/promise');
require('dotenv').config();

// ============ CONFIGURATION BDD ============
const dbConfig = {
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
};

// Créer le pool de connexions
const pool = mysql.createPool(dbConfig);

// Tester la connexion au démarrage
pool.getConnection()
  .then(connection => {
    console.log('✅ Connexion MySQL établie');
    connection.release();
  })
  .catch(err => {
    console.error('❌ Erreur connexion MySQL:', err.message);
    process.exit(1);
  });

// ============ STOCKAGE EN MÉMOIRE ============
// Map des utilisateurs connectés : userId -> { socketId, userName, status }
const connectedUsers = new Map();

// Map des utilisateurs qui écrivent : conversationId -> Set(userId)
const typingUsers = new Map();

// ============ MIDDLEWARE D'AUTHENTIFICATION ============
io.use(async (socket, next) => {
  const sessionId = socket.handshake.auth.sessionId;
  
  console.log('🔐 Tentative de connexion avec session:', sessionId);
  
  if (!sessionId) {
    console.log('❌ Aucun sessionId fourni');
    return next(new Error('Authentication error: No session ID'));
  }
  
  try {
    // Vérifier la session en base de données
    const [rows] = await pool.execute(
      `SELECT id, nom, prenom, photo_profil 
       FROM utilisateur 
       WHERE session_id = ? 
       LIMIT 1`,
      [sessionId]
    );
    
    if (rows.length === 0) {
      console.log('❌ Session invalide:', sessionId);
      return next(new Error('Invalid session'));
    }
    
    // Attacher les infos utilisateur au socket
    socket.userId = rows[0].id;
    socket.userName = `${rows[0].prenom} ${rows[0].nom}`;
    socket.userPhoto = rows[0].photo_profil;
    
    console.log(`Authentification réussie: ${socket.userName} (${socket.userId})`);
    next();
    
  } catch (error) {
    console.error('Erreur auth:', error);
    next(new Error('Database error'));
  }
});

// ============ CONNEXION D'UN UTILISATEUR ============
io.on('connection', async (socket) => {
  console.log(`\n🟢 Connexion: ${socket.userName} (Socket: ${socket.id})`);
  
  // Enregistrer l'utilisateur comme connecté
  connectedUsers.set(socket.userId, {
    socketId: socket.id,
    userName: socket.userName,
    userPhoto: socket.userPhoto,
    status: 'online',
    connectedAt: new Date()
  });
  
  // Mettre à jour la présence en BDD
  await updateUserPresence(socket.userId, socket.id, 'online');
  
  // Rejoindre les conversations de l'utilisateur
  await joinUserConversations(socket);
  
  // Notifier les amis que l'utilisateur est en ligne
  await notifyFriendsOnline(socket.userId, true);
  
  // Envoyer la liste des utilisateurs en ligne au nouveau connecté
  socket.emit('users:online', Array.from(connectedUsers.entries()).map(([userId, data]) => ({
    userId,
    userName: data.userName,
    status: data.status
  })));
  
  // ============ ÉVÉNEMENT : ENVOI DE MESSAGE ============
  socket.on('message:send', async (data) => {
    console.log(`Message de ${socket.userName}:`, {
      conv: data.conversationId,
      dest: data.destinataireId
    });
    
    try {
      const { conversationId, destinataireId, encryptedMessage } = data;
      
      // Validation
      if (!conversationId || !destinataireId || !encryptedMessage) {
        return socket.emit('message:error', { 
          error: 'Données manquantes' 
        });
      }
      
      // Vérifier que la conversation existe et que l'utilisateur y participe
      const [convCheck] = await pool.execute(
        `SELECT id FROM conversations 
         WHERE id = ? 
         AND (user1_id = ? OR user2_id = ?)`,
        [conversationId, socket.userId, socket.userId]
      );
      
      if (convCheck.length === 0) {
        return socket.emit('message:error', { 
          error: 'Conversation non trouvée' 
        });
      }
      
      // Insérer le message en BDD
      const [result] = await pool.execute(
        `INSERT INTO messages 
         (conversation_id, expediteur_id, destinataire_id, message, lu, date_envoi) 
         VALUES (?, ?, ?, ?, 0, NOW())`,
        [conversationId, socket.userId, destinataireId, encryptedMessage]
      );
      
      const messageId = result.insertId;
      
      // Mettre à jour la dernière activité de la conversation
      await pool.execute(
        'UPDATE conversations SET derniere_activite = NOW() WHERE id = ?',
        [conversationId]
      );
      
      // Créer l'objet message complet
      const messageData = {
        id: messageId,
        conversationId,
        expediteurId: socket.userId,
        expediteurNom: socket.userName,
        expediteurPhoto: socket.userPhoto,
        destinataireId,
        message: encryptedMessage,
        dateEnvoi: new Date(),
        lu: false
      };
      
      // Envoyer au destinataire s'il est connecté
      const destinataire = connectedUsers.get(destinataireId);
      if (destinataire) {
        console.log(`Envoi à ${destinataire.userName}`);
        io.to(destinataire.socketId).emit('message:received', messageData);
        
        // Marquer comme délivré
        await pool.execute(
          'UPDATE messages SET delivered_at = NOW() WHERE id = ?',
          [messageId]
        );
      } else {
        console.log(`Destinataire hors ligne (${destinataireId})`);
      }
      
      // Confirmer à l'expéditeur
      socket.emit('message:sent', {
        ...messageData,
        tempId: data.tempId // Pour mettre à jour l'UI optimiste
      });
      
      console.log(`Message ${messageId} envoyé`);
      
    } catch (error) {
      console.error('Erreur envoi message:', error);
      socket.emit('message:error', { 
        error: 'Erreur serveur',
        details: error.message 
      });
    }
  });
  
  // ============ ÉVÉNEMENT : UTILISATEUR ÉCRIT ============
  socket.on('typing:start', ({ conversationId, destinataireId }) => {
    console.log(` ${socket.userName} écrit dans conv ${conversationId}`);
    
    // Ajouter à la map des typeurs
    if (!typingUsers.has(conversationId)) {
      typingUsers.set(conversationId, new Set());
    }
    typingUsers.get(conversationId).add(socket.userId);
    
    // Notifier le destinataire
    const destinataire = connectedUsers.get(destinataireId);
    if (destinataire) {
      io.to(destinataire.socketId).emit('typing:user', {
        userId: socket.userId,
        userName: socket.userName,
        conversationId
      });
    }
  });
  
  socket.on('typing:stop', ({ conversationId, destinataireId }) => {
    console.log(`${socket.userName} arrête d'écrire`);
    
    // Retirer de la map
    if (typingUsers.has(conversationId)) {
      typingUsers.get(conversationId).delete(socket.userId);
      
      if (typingUsers.get(conversationId).size === 0) {
        typingUsers.delete(conversationId);
      }
    }
    
    // Notifier le destinataire
    const destinataire = connectedUsers.get(destinataireId);
    if (destinataire) {
      io.to(destinataire.socketId).emit('typing:stopped', {
        userId: socket.userId,
        conversationId
      });
    }
  });
  
  // ============ ÉVÉNEMENT : MARQUER COMME LU ============
  socket.on('message:read', async ({ messageId, conversationId }) => {
    console.log(`Message ${messageId} lu par ${socket.userName}`);
    
    try {
      // Mettre à jour en BDD
      await pool.execute(
        `UPDATE messages 
         SET lu = 1, read_at = NOW() 
         WHERE id = ? 
         AND destinataire_id = ?`,
        [messageId, socket.userId]
      );
      
      // Récupérer l'expéditeur
      const [msg] = await pool.execute(
        'SELECT expediteur_id FROM messages WHERE id = ?',
        [messageId]
      );
      
      if (msg.length > 0) {
        const expediteur = connectedUsers.get(msg[0].expediteur_id);
        
        // Notifier l'expéditeur
        if (expediteur) {
          io.to(expediteur.socketId).emit('message:read', {
            messageId,
            conversationId,
            readBy: socket.userId,
            readAt: new Date()
          });
        }
      }
      
    } catch (error) {
      console.error('❌ Erreur lecture message:', error);
    }
  });
  
  // ============ ÉVÉNEMENT : MARQUER CONVERSATION COMME LUE ============
  socket.on('conversation:read', async ({ conversationId }) => {
    console.log(`Conversation ${conversationId} lue par ${socket.userName}`);
    
    try {
      // Marquer tous les messages non lus de cette conversation
      const [result] = await pool.execute(
        `UPDATE messages 
         SET lu = 1, read_at = NOW() 
         WHERE conversation_id = ? 
         AND destinataire_id = ? 
         AND lu = 0`,
        [conversationId, socket.userId]
      );
      
      console.log(`${result.affectedRows} messages marqués comme lus`);
      
      // Récupérer l'autre participant
      const [conv] = await pool.execute(
        `SELECT 
          CASE 
            WHEN user1_id = ? THEN user2_id 
            ELSE user1_id 
          END as other_user_id
         FROM conversations 
         WHERE id = ?`,
        [socket.userId, conversationId]
      );
      
      if (conv.length > 0) {
        const otherUser = connectedUsers.get(conv[0].other_user_id);
        
        if (otherUser) {
          io.to(otherUser.socketId).emit('conversation:read', {
            conversationId,
            readBy: socket.userId
          });
        }
      }
      
    } catch (error) {
      console.error('Erreur lecture conversation:', error);
    }
  });
  
  // ============ DÉCONNEXION ============
  socket.on('disconnect', async () => {
    console.log(`Déconnexion: ${socket.userName}`);
    
    // Retirer de la map
    connectedUsers.delete(socket.userId);
    
    // Nettoyer les "typing"
    typingUsers.forEach((users, convId) => {
      users.delete(socket.userId);
      if (users.size === 0) {
        typingUsers.delete(convId);
      }
    });
    
    // Mettre à jour la présence
    await updateUserPresence(socket.userId, null, 'offline');
    
    // Notifier les amis
    await notifyFriendsOnline(socket.userId, false);
  });
  
  // ============ GESTION DES ERREURS ============
  socket.on('error', (error) => {
    console.error(`Erreur socket ${socket.userName}:`, error);
  });
});

// ============ FONCTIONS UTILITAIRES ============

// Rejoindre les conversations de l'utilisateur
async function joinUserConversations(socket) {
  try {
    const [conversations] = await pool.execute(
      `SELECT id FROM conversations 
       WHERE user1_id = ? OR user2_id = ?`,
      [socket.userId, socket.userId]
    );
    
    conversations.forEach(conv => {
      const room = `conversation:${conv.id}`;
      socket.join(room);
      console.log(`  → Rejoint ${room}`);
    });
    
    console.log(`${socket.userName} a rejoint ${conversations.length} conversation(s)`);
    
  } catch (error) {
    console.error('Erreur joinUserConversations:', error);
  }
}

// Mettre à jour la présence utilisateur
async function updateUserPresence(userId, socketId, status) {
  try {
    await pool.execute(
      `INSERT INTO user_presence (user_id, socket_id, status, last_seen)
       VALUES (?, ?, ?, NOW())
       ON DUPLICATE KEY UPDATE 
         socket_id = VALUES(socket_id),
         status = VALUES(status),
         last_seen = NOW()`,
      [userId, socketId || '', status]
    );
  } catch (error) {
    console.error('Erreur updateUserPresence:', error);
  }
}

// Notifier les amis du changement de statut
async function notifyFriendsOnline(userId, isOnline) {
  try {
    // Récupérer les amis
    const [friends] = await pool.execute(
      `SELECT 
        CASE 
          WHEN id_utilisateur = ? THEN id_ami 
          ELSE id_utilisateur 
        END as friend_id
       FROM amis 
       WHERE (id_utilisateur = ? OR id_ami = ?) 
       AND statut = 'accepte'`,
      [userId, userId, userId]
    );
    
    // Récupérer les infos utilisateur
    const [user] = await pool.execute(
      'SELECT nom, prenom FROM utilisateur WHERE id = ?',
      [userId]
    );
    
    const userName = user.length > 0 ? `${user[0].prenom} ${user[0].nom}` : 'Utilisateur';
    
    // Notifier chaque ami connecté
    friends.forEach(friend => {
      const friendSocket = connectedUsers.get(friend.friend_id);
      if (friendSocket) {
        io.to(friendSocket.socketId).emit(
          isOnline ? 'user:online' : 'user:offline',
          { userId, userName }
        );
      }
    });
    
    console.log(`${friends.length} ami(s) notifié(s) du statut de ${userName}`);
    
  } catch (error) {
    console.error('Erreur notifyFriendsOnline:', error);
  }
}

// ============ ROUTE HTTP DE TEST ============
app.get('/health', (req, res) => {
  res.json({
    status: 'OK',
    connectedUsers: connectedUsers.size,
    activeConversations: typingUsers.size,
    uptime: process.uptime()
  });
});

app.get('/stats', (req, res) => {
  const users = Array.from(connectedUsers.entries()).map(([id, data]) => ({
    userId: id,
    userName: data.userName,
    status: data.status,
    connectedAt: data.connectedAt
  }));
  
  res.json({
    totalConnected: connectedUsers.size,
    users
  });
});

// ============ DÉMARRAGE DU SERVEUR ============
const PORT = process.env.SOCKET_PORT || 3000;

http.listen(PORT, () => {
  console.log('\n=================================');
  console.log('Serveur Socket.IO démarré !');
  console.log(`Port: ${PORT}`);
  console.log(`Origine autorisée: ${process.env.ALLOWED_ORIGIN}`);
  console.log(`BDD: ${process.env.DB_NAME}@${process.env.DB_HOST}`);
  console.log('=================================\n');
  console.log('Routes de monitoring:');
  console.log(`   http://localhost:${PORT}/health`);
  console.log(`   http://localhost:${PORT}/stats`);
  console.log('\n  En attente de connexions...\n');
});

// ============ GESTION ARRÊT PROPRE ============
process.on('SIGINT', async () => {
  console.log('\n\nArrêt du serveur...');
  
  // Mettre tous les utilisateurs hors ligne
  for (const [userId] of connectedUsers) {
    await updateUserPresence(userId, null, 'offline');
  }
  
  console.log('Nettoyage terminé');
  process.exit(0);
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('Promesse rejetée non gérée:', reason);
});

process.on('uncaughtException', (error) => {
  console.error('Exception non capturée:', error);
  process.exit(1);
});