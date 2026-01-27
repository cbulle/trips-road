/*=======================================
  Formulaire d'inscription et de connexion
=======================================*/

function showLogin() {
    document.getElementById('btnLogin').classList.add('active');
    document.getElementById('btnRegister').classList.remove('active');
}

function showRegister() {
    document.getElementById('btnLogin').classList.remove('active');
    document.getElementById('btnRegister').classList.add('active');
}

 
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

/*==================================
    partage du lien MesRoadTrips
================================*/

function closeShareModal() {
    document.getElementById('shareModal').style.display = 'none';
    window.history.replaceState({}, document.title, window.location.pathname);
}

function copyShareUrl() {
    const copyText = document.getElementById("shareUrl");
    copyText.select();
    copyText.setSelectionRange(0, 99999); 
    navigator.clipboard.writeText(copyText.value).then(() => {
        const successMsg = document.getElementById("copySuccess");
        successMsg.style.display = "block";
        setTimeout(() => { successMsg.style.display = "none"; }, 2000);
    });
}
