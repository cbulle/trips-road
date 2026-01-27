// ========================================
// CHIFFREMENT END-TO-END AVEC LIBSODIUM
// Fichier : js/encryption.js
// ========================================

/**
 * Classe gérant le chiffrement end-to-end des messages
 * Utilise libsodium.js (courbe X25519 + ChaCha20-Poly1305)
 */
class MessageEncryption {
  constructor() {
    this.keyPair = null;              // Paire de clés (publique + privée)
    this.sharedKeys = new Map();      // Cache des clés partagées : userId -> sharedKey
    this.isReady = false;             // Sodium chargé et clés générées
    this.initPromise = this.init();   // Promesse d'initialisation
  }
  
  /**
   * Initialisation : charger sodium et les clés
   */
  async init() {
    try {
      // Attendre que sodium soit prêt
      await sodium.ready;
      console.log('✅ Sodium chargé');
      
      // Charger ou générer les clés
      await this.loadOrGenerateKeyPair();
      
      this.isReady = true;
      console.log('✅ Chiffrement initialisé');
      
      return true;
    } catch (error) {
      console.error('❌ Erreur init chiffrement:', error);
      return false;
    }
  }
  
  /**
   * Charger la paire de clés depuis localStorage ou en générer une nouvelle
   */
  async loadOrGenerateKeyPair() {
    const privateKeyB64 = localStorage.getItem('privateKey_v1');
    
    if (privateKeyB64) {
      // Charger les clés existantes
      console.log('🔑 Chargement des clés existantes...');
      
      try {
        const privateKey = sodium.from_base64(privateKeyB64, sodium.base64_variants.ORIGINAL);
        const publicKey = sodium.crypto_scalarmult_base(privateKey);
        
        this.keyPair = {
          publicKey,
          privateKey,
          keyType: 'x25519'
        };
        
        console.log('✅ Clés chargées depuis localStorage');
        
        // Vérifier si la clé publique est sauvegardée sur le serveur
        await this.ensurePublicKeyOnServer();
        
      } catch (error) {
        console.error('❌ Erreur chargement clés, régénération...', error);
        localStorage.removeItem('privateKey_v1');
        await this.generateKeyPair();
      }
      
    } else {
      // Générer de nouvelles clés
      await this.generateKeyPair();
    }
  }
  
  /**
   * Générer une nouvelle paire de clés
   */
  async generateKeyPair() {
    console.log('🔐 Génération d une nouvelle paire de clés...');
    
    // Générer une paire de clés X25519 (Diffie-Hellman)
    this.keyPair = sodium.crypto_box_keypair();
    
    // Sauvegarder la clé PRIVÉE localement (JAMAIS sur le serveur !)
    const privateKeyB64 = sodium.to_base64(
      this.keyPair.privateKey, 
      sodium.base64_variants.ORIGINAL
    );
    localStorage.setItem('privateKey_v1', privateKeyB64);
    
    console.log('✅ Clés générées et sauvegardées localement');
    
    // Sauvegarder la clé PUBLIQUE sur le serveur
    await this.savePublicKeyToServer();
  }
  
  /**
   * Sauvegarder la clé publique sur le serveur
   */
  async savePublicKeyToServer() {
    const publicKeyB64 = sodium.to_base64(
      this.keyPair.publicKey, 
      sodium.base64_variants.ORIGINAL
    );
    
    console.log('📤 Sauvegarde de la clé publique sur le serveur...');
    
    try {
      const response = await fetch('/formulaire/save_public_key.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          publicKey: publicKeyB64
        })
      });
      
      const data = await response.json();
      
      if (data.success) {
        console.log('✅ Clé publique sauvegardée sur le serveur');
      } else {
        console.error('❌ Erreur sauvegarde:', data.error);
      }
      
    } catch (error) {
      console.error('❌ Erreur réseau:', error);
    }
  }
  
  /**
   * Vérifier que la clé publique est bien sur le serveur
   */
  async ensurePublicKeyOnServer() {
    try {
      const response = await fetch('/formulaire/get_session_id.php');
      const data = await response.json();
      
      if (!data.success) {
        return;
      }
      
      const userId = data.userId;
      
      // Vérifier si la clé publique existe
      const keyResponse = await fetch(`/formulaire/get_public_key.php?user_id=${userId}`);
      const keyData = await keyResponse.json();
      
      if (!keyData.success || !keyData.publicKey) {
        console.log('⚠️ Clé publique manquante sur le serveur, envoi...');
        await this.savePublicKeyToServer();
      }
      
    } catch (error) {
      console.error('Erreur vérification clé publique:', error);
    }
  }
  
  /**
   * Calculer une clé partagée avec un autre utilisateur
   * Utilise Diffie-Hellman pour dériver une clé secrète commune
   */
  async getSharedKey(otherUserId) {
    // Vérifier si déjà en cache
    if (this.sharedKeys.has(otherUserId)) {
      return this.sharedKeys.get(otherUserId);
    }
    
    console.log(`🔑 Calcul de la clé partagée avec utilisateur ${otherUserId}...`);
    
    try {
      // Récupérer la clé publique de l'autre utilisateur
      const response = await fetch(`/formulaire/get_public_key.php?user_id=${otherUserId}`);
      const data = await response.json();
      
      if (!data.success || !data.publicKey) {
        throw new Error(`Clé publique introuvable pour utilisateur ${otherUserId}`);
      }
      
      const otherPublicKey = sodium.from_base64(
        data.publicKey, 
        sodium.base64_variants.ORIGINAL
      );
      
      // Calculer la clé partagée (Diffie-Hellman)
      // Cette opération produit la MÊME clé pour les deux utilisateurs
      const sharedKey = sodium.crypto_box_beforenm(
        otherPublicKey,
        this.keyPair.privateKey
      );
      
      // Mettre en cache
      this.sharedKeys.set(otherUserId, sharedKey);
      
      console.log(`✅ Clé partagée calculée avec ${data.userName}`);
      
      return sharedKey;
      
    } catch (error) {
      console.error('❌ Erreur calcul clé partagée:', error);
      throw error;
    }
  }
  
  /**
   * Chiffrer un message pour un destinataire
   * @param {string} message - Message en clair
   * @param {number} destinataireId - ID du destinataire
   * @returns {Object} - {nonce, ciphertext} en base64
   */
  async encrypt(message, destinataireId) {
    // Attendre que l'initialisation soit terminée
    if (!this.isReady) {
      await this.initPromise;
    }
    
    // Récupérer la clé partagée
    const sharedKey = await this.getSharedKey(destinataireId);
    
    // Générer un nonce aléatoire (24 bytes)
    const nonce = sodium.randombytes_buf(sodium.crypto_box_NONCEBYTES);
    
    // Convertir le message en Uint8Array
    const messageBytes = sodium.from_string(message);
    
    // Chiffrer avec ChaCha20-Poly1305
    const ciphertext = sodium.crypto_box_easy_afternm(
      messageBytes,
      nonce,
      sharedKey
    );
    
    // Retourner nonce + ciphertext en base64
    return {
      nonce: sodium.to_base64(nonce, sodium.base64_variants.ORIGINAL),
      ciphertext: sodium.to_base64(ciphertext, sodium.base64_variants.ORIGINAL),
      version: 1 // Pour compatibilité future
    };
  }
  
  /**
   * Déchiffrer un message
   * @param {Object} encryptedData - {nonce, ciphertext} en base64
   * @param {number} expediteurId - ID de l'expéditeur
   * @returns {string} - Message déchiffré
   */
  async decrypt(encryptedData, expediteurId) {
    // Attendre que l'initialisation soit terminée
    if (!this.isReady) {
      await this.initPromise;
    }
    
    try {
      // Récupérer la clé partagée
      const sharedKey = await this.getSharedKey(expediteurId);
      
      // Décoder depuis base64
      const nonce = sodium.from_base64(
        encryptedData.nonce, 
        sodium.base64_variants.ORIGINAL
      );
      const ciphertext = sodium.from_base64(
        encryptedData.ciphertext, 
        sodium.base64_variants.ORIGINAL
      );
      
      // Déchiffrer
      const decrypted = sodium.crypto_box_open_easy_afternm(
        ciphertext,
        nonce,
        sharedKey
      );
      
      // Convertir en string
      return sodium.to_string(decrypted);
      
    } catch (error) {
      console.error('❌ Erreur déchiffrement:', error);
      return '[Message chiffré - Erreur de déchiffrement]';
    }
  }
  
  /**
   * Vider le cache des clés partagées
   */
  clearSharedKeys() {
    this.sharedKeys.clear();
    console.log('🗑️ Cache des clés partagées vidé');
  }
  
  /**
   * Réinitialiser complètement (regénère tout)
   */
  async reset() {
    console.log('🔄 Réinitialisation du chiffrement...');
    
    // Supprimer les clés locales
    localStorage.removeItem('privateKey_v1');
    
    // Vider le cache
    this.clearSharedKeys();
    
    // Régénérer
    await this.generateKeyPair();
    
    console.log('✅ Chiffrement réinitialisé');
  }
  
  /**
   * Exporter la clé publique (pour debug)
   */
  getPublicKeyBase64() {
    if (!this.keyPair) {
      return null;
    }
    
    return sodium.to_base64(
      this.keyPair.publicKey, 
      sodium.base64_variants.ORIGINAL
    );
  }
  
  /**
   * Vérifier si le chiffrement est initialisé
   */
  async ensureReady() {
    if (!this.isReady) {
      await this.initPromise;
    }
  }
}

// ============================================
// INSTANCE GLOBALE
// ============================================

// Créer une instance unique accessible partout
const encryption = new MessageEncryption();

// Exposer pour debug dans la console
window.encryption = encryption;

// Log de statut
console.log('📦 Module de chiffrement chargé');

// ============================================
// FONCTIONS UTILITAIRES
// ============================================

/**
 * Tester le chiffrement (pour debug)
 */
async function testEncryption() {
  await encryption.ensureReady();
  
  console.log('🧪 Test de chiffrement...');
  
  const message = "Hello, world! 🌍";
  const destinataireId = 2; // ID fictif
  
  console.log('Message original:', message);
  
  // Chiffrer
  const encrypted = await encryption.encrypt(message, destinataireId);
  console.log('Chiffré:', encrypted);
  
  // Déchiffrer
  const decrypted = await encryption.decrypt(encrypted, destinataireId);
  console.log('Déchiffré:', decrypted);
  
  if (message === decrypted) {
    console.log('✅ Test réussi !');
  } else {
    console.error('❌ Test échoué !');
  }
} ;



