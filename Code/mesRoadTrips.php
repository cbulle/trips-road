<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$show_share = $_GET['show_share'] ?? null;
$share_url = $_SESSION['share_url'] ?? null;

// --- Récupération des road trips ---
$stmt = $pdo->prepare("SELECT * FROM roadtrip WHERE id_utilisateur = :id ORDER BY id DESC");
$stmt->execute(['id' => $id_utilisateur]);
$roadtrips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Road Trips</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .btn-share {
            background: #9b59b6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .btn-share:hover {
            background: #8e44ad;
        }
        
        /* Modal de partage */
        .share-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .share-modal.active {
            display: flex;
        }
        .share-modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            position: relative;
        }
        .share-modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        .share-modal-close:hover {
            color: #333;
        }
        .share-url-container {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        .share-url-input {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--bleu_clair);
            border-radius: 8px;
            font-size: 14px;
        }
        .copy-btn {
            padding: 12px 20px;
            background: var(--bleu_clair);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .copy-btn:hover {
            background: var(--vert_btn);
        }
        .copy-success {
            display: none;
            color: green;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
        .share-info {
            background: #f0f8ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . "/modules/header.php"; ?>

<h1>Mes Road Trips</h1>

<?php if (empty($roadtrips)) : ?>
    <p>Aucun road trip pour le moment.</p>
<?php else : ?>
<div class="roadtrip-grid">
    <?php foreach ($roadtrips as $rt): ?>
        <div class="roadtrip-card">

            <?php if (!empty($rt['photo'])): ?>
                <img src="/uploads/roadtrips/<?= htmlspecialchars($rt['photo']) ?>" 
                     alt="Photo du road trip" class="roadtrip-photo">
            <?php endif; ?>

            <h3><?= htmlspecialchars($rt['titre']) ?></h3>
            <p><?= htmlspecialchars($rt['description']) ?></p>

            <div class="roadtrip-buttons">
                <a class="btn-view" href="vuRoadTrip.php?id=<?= $rt['id'] ?>">
                    Voir
                </a>

                <a class="btn-edit" href="roadtrip_edit.php?id=<?= $rt['id'] ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" stroke="black" stroke-width="2"/>
                        <path d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" stroke="black" stroke-width="2"/>
                    </svg>
                </a>
                
                <a class="btn-share" href="/formulaire/generate_share_link.php?id=<?= $rt['id'] ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z" fill="white"/>
                    </svg>
                    Partager
                </a>

                <a class="btn-delete" href="/formulaire/delete_RoadTrip.php?id=<?= $rt['id'] ?>" 
                   onclick="return confirm('Voulez-vous vraiment supprimer ce road trip ?');">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M3 6h18" stroke="white" stroke-width="2"/>
                        <path d="M8 6v14h8V6" stroke="white" stroke-width="2"/>
                        <path d="M10 10v6" stroke="white" stroke-width="2"/>
                        <path d="M14 10v6" stroke="white" stroke-width="2"/>
                        <path d="M9 6l1-2h4l1 2" stroke="white" stroke-width="2"/>
                    </svg>
                </a>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal de partage -->
<?php if ($show_share && $share_url): ?>
<div class="share-modal active" id="shareModal">
    <div class="share-modal-content">
        <span class="share-modal-close" onclick="closeShareModal()">&times;</span>
        <h2 style="color: var(--bleu_foncé); margin-bottom: 15px;">Partager votre road trip</h2>
        <p>Copiez ce lien pour partager votre road trip avec n'importe qui :</p>
        
        <div class="share-url-container">
            <input type="text" class="share-url-input" id="shareUrl" value="<?= htmlspecialchars($share_url) ?>" readonly>
            <button class="copy-btn" onclick="copyShareUrl()">Copier</button>
        </div>
        
        <div class="copy-success" id="copySuccess">✓ Lien copié !</div>
        
        <div class="share-info">
            <strong>📊 Statistiques :</strong> Le lien inclut un compteur de vues pour suivre combien de personnes ont consulté votre road trip.
        </div>
    </div>
</div>
<?php 
    unset($_SESSION['share_url']);
endif; 
?>

<script>
function closeShareModal() {
    document.getElementById('shareModal').classList.remove('active');
    // Retirer le paramètre de l'URL
    window.history.replaceState({}, document.title, window.location.pathname);
}

function copyShareUrl() {
    const input = document.getElementById('shareUrl');
    input.select();
    document.execCommand('copy');
    
    const success = document.getElementById('copySuccess');
    success.style.display = 'block';
    
    setTimeout(() => {
        success.style.display = 'none';
    }, 3000);
}

// Fermer le modal en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    const modal = document.getElementById('shareModal');
    if (modal && event.target === modal) {
        closeShareModal();
    }
});
</script>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

</body>
</html>