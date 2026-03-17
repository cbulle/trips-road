/**
 * @file User profile management optimization
 * @description Handles image previewing, profile sidebar toggling, and form validation.
 */

/**
 * Toggles the visibility of the profile sidebar on mobile devices.
 * Accessible globally via onclick attributes.
 * @function toggleSidebar
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.profil-sidebar');
    if (sidebar) sidebar.classList.toggle('active');
}

/**
 * Generates a preview of the selected profile image.
 * @function previewImage
 * @param {HTMLInputElement} input - The file input element
 * @param {HTMLElement} previewElement - The element where the background image will be applied
 */
function previewImage(input, previewElement) {
    const file = input.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewElement.style.backgroundImage = `url('${e.target.result}')`;
        };
        reader.readAsDataURL(file);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const profilForm = document.getElementById('profilForm');
    const fileInput = document.getElementById('image');
    const avatarPreview = document.querySelector('.avatar-circle.large');
    const passField = document.getElementById('password');
    const confirmField = document.getElementById('confirm-password');

    /**
     * Listener for profile picture change to trigger preview.
     */
    if (fileInput && avatarPreview) {
        fileInput.addEventListener('change', () => previewImage(fileInput, avatarPreview));
    }

    /**
     * Validates the profile form before submission.
     * Ensures passwords match if a new password is provided.
     */
    if (profilForm) {
        profilForm.addEventListener('submit', (e) => {
            if (passField && confirmField && passField.value !== "") {
                if (passField.value !== confirmField.value) {
                    e.preventDefault();
                    alert("Passwords do not match.");
                    confirmField.focus();
                }
            }
        });
    }

    /**
     * Automatically applies the 'active' class to the navigation link
     * matching the current URL path.
     */
    const currentPath = window.location.pathname;
    document.querySelectorAll('.profil-nav a').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
});
