window.window.openRoadtripModal = function (id) {
    var el = document.getElementById(id);
    if (!el) {
        console.warn('[modals.js] openRoadtripModal : element introuvable → #' + id);
        return;
    }
    el.style.display = 'block';
    document.body.style.overflow = 'hidden';
};

window.window.closeRoadtripModal = function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'none';
    var anyOpen = document.querySelectorAll('.custom-modal[style*="display: block"]');
    if (anyOpen.length === 0) {
        document.body.style.overflow = '';
    }
};

window.window.closeAllRoadtripModals = function () {
    document.querySelectorAll('.custom-modal').forEach(function (m) {
        m.style.display = 'none';
    });
    document.body.style.overflow = '';
};

document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            window.closeAllModals();
        }
    });

    document.querySelectorAll('.custom-modal').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                window.closeModal(modal.id);
            }
        });
    });
});
