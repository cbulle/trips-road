<?php
require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

/** @var PDO $pdo */

$token = $_GET['token'] ?? '';
$token_hash = hash('sha256', $token);

$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE reset_token_hash = ? AND reset_expires_at > NOW()");
$stmt->execute([$token_hash]);
$user = $stmt->fetch();

if (!$user) {
    die("Lien invalide ou expiré.");
}
?>

<form action="formulaire/traitement_reset.php" method="POST">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <label>Nouveau mot de passe :</label>
    <input type="password" name="password" required minlength="8">
    <button type="submit">Confirmer</button>
</form>