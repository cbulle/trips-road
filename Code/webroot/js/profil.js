/*=======================================
  Formulaire d'inscription et de connexion
=======================================*/

function openModal() {
    const modal = document.querySelector('.formulaire'); 
    if (modal) {
        modal.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    openModal();
    showRegister();
});
    /*==================================
    Mes road 
    ================================*/

function closeShareModal() {
    document.getElementById('shareModal').classList.remove('active');
    window.history.replaceState({}, document.title, window.location.pathname);
}

function copyShareUrl() {
    const input = document.getElementById('shareUrl');
    navigator.clipboard.writeText(input.value).then(() => {
        const success = document.getElementById('copySuccess');
        success.style.display = 'block';
        
        setTimeout(() => {
            success.style.display = 'none';
        }, 3000);
    }).catch(err => {
        console.error('Erreur lors de la copie du texte : ', err);
    });
}

document.addEventListener('click', function(event) {
    const modal = document.getElementById('shareModal');
    if (modal && !modal.contains(event.target)) {
        closeShareModal();
    }
});

