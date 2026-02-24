/**
 * modals.js
 * -------------------------------------------------------
 * Gestion centralisée des modales.
 *
 * Les fonctions openModal / closeModal / closeAllModals sont
 * déclarées IMMÉDIATEMENT (pas dans DOMContentLoaded) pour
 * fonctionner avec les attributs onclick="" du HTML.
 *
 * Le bloc DOMContentLoaded ajoute les listeners sur les
 * boutons qui n'ont pas d'onclick inline.
 * -------------------------------------------------------
 */

/* ----------------------------------------------------------
   API PUBLIQUE — disponible dès le chargement du fichier
---------------------------------------------------------- */

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
    /* Ne remet le scroll que si toutes les modales sont fermées */
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

/* ----------------------------------------------------------
   LISTENERS — ajoutés au chargement du DOM
   (complément aux onclick inline, pour les boutons sans onclick)
---------------------------------------------------------- */

document.addEventListener('DOMContentLoaded', function () {

    /* Escape ferme tout */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            window.closeAllModals();
        }
    });

    /* Sécurité : clic fond pour les modales sans onclick inline */
    document.querySelectorAll('.custom-modal').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                window.closeModal(modal.id);
            }
        });
    });
});
