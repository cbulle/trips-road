require('dotenv').config();
const mysql = require('mysql2/promise');

async function testDB() {
  try {
    const connection = await mysql.createConnection({
      host: process.env.DB_HOST,
      user: process.env.DB_USER,
      password: process.env.DB_PASSWORD,
      database: process.env.DB_NAME
    });
    
    console.log(' Connexion MySQL réussie !');
    
    const [rows] = await connection.execute('SELECT COUNT(*) as count FROM utilisateurs');
    console.log(` Nombre d'utilisateurs: ${rows[0].count}`);
    
    await connection.end();
  } catch (error) {
    console.error('Erreur connexion:', error.message);
  }
}

testDB();