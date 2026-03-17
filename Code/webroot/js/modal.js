/**
 * @file Modal management system
 * @description Provides global functions to open, close, and manage roadtrip-related modals.
 */

/**
 * Opens a specific roadtrip modal by ID.
 * Disables body scrolling.
 * @function openRoadtripModal
 * @param {string} id - The DOM ID of the modal to open
 */
window.openRoadtripModal = function (id) {
    var el = document.getElementById(id);
    if (!el) {
        console.warn('[modals.js] openRoadtripModal : element not found → #' + id);
        return;
    }
    el.style.display = 'block';
    document.body.style.overflow = 'hidden';
};

/**
 * Closes a specific roadtrip modal by ID.
 * Restores body scrolling if no other modals are open.
 * @function closeRoadtripModal
 * @param {string} id - The DOM ID of the modal to close
 */
window.closeRoadtripModal = function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'none';

    var anyOpen = document.querySelectorAll('.custom-modal[style*="display: block"]');
    if (anyOpen.length === 0) {
        document.body.style.overflow = '';
    }
};

/**
 * Closes all active modals and restores body scrolling.
 * @function closeAllRoadtripModals
 */
window.closeAllRoadtripModals = function () {
    document.querySelectorAll('.custom-modal').forEach(function (m) {
        m.style.display = 'none';
    });
    document.body.style.overflow = '';
};

document.addEventListener('DOMContentLoaded', function () {
    /**
     * Closes modals when the Escape key is pressed.
     */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            window.closeAllRoadtripModals();
        }
    });

    /**
     * Closes modals when clicking outside the content (on the backdrop).
     */
    document.querySelectorAll('.custom-modal').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                window.closeRoadtripModal(modal.id);
            }
        });
    });
});
