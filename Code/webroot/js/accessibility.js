/**
 * @file Accessibility preferences management
 * @description
 * This script handles user accessibility preferences:
 * - Dark / Light theme
 * - Visually impaired mode (adapted size/contrast)
 * - Filters for different types of color blindness
 *
 * Preferences are stored in localStorage and automatically
 * reapplied when the page loads.
 */

/**
 * Applies saved preferences from localStorage to the document.
 * @function applyPreferences
 * @returns {void}
 */
function applyPreferences() {
    const savedTheme = localStorage.getItem("theme");
    const savedMalvoyant = localStorage.getItem("Police");
    const savedDaltonienType = localStorage.getItem("typeDaltonien");

    const checkboxSombre = document.getElementById("checkboxSombre");
    const checkboxMalvoyant = document.getElementById("checkboxMalvoyant");

    /* =========================
           Dark Theme Management
        ========================== */
    if (savedTheme === "dark") {
        document.documentElement.classList.add("dark", "SombreBtn");
        if (checkboxSombre) checkboxSombre.checked = true;
    } else {
        document.documentElement.classList.remove("dark", "SombreBtn");
        if (checkboxSombre) checkboxSombre.checked = false;
    }

    /* =========================
           Visually Impaired Mode
        ========================== */
    if (savedMalvoyant === "malvoyant") {
        document.documentElement.classList.add("malvoyant", "MalvoyantBtn");
        if (checkboxMalvoyant) checkboxMalvoyant.checked = true;
    } else {
        document.documentElement.classList.remove("malvoyant", "MalvoyantBtn");
        if (checkboxMalvoyant) checkboxMalvoyant.checked = false;
    }

    /* =========================
           Color Blindness Management
        ========================== */
    document.documentElement.classList.remove(
        "daltonien",
        "protanopia",
        "deuteranopia",
        "tritanopia"
    );

    if (savedDaltonienType && savedDaltonienType !== "aucun") {
        document.documentElement.classList.add("daltonien", savedDaltonienType);
        const radioToCheck = document.querySelector(`input[name="daltonism-type"][value="${savedDaltonienType}"]`);
        if (radioToCheck) radioToCheck.checked = true;
    } else {
        const radioAucun = document.querySelector('input[name="daltonism-type"][value="aucun"]');
        if (radioAucun) radioAucun.checked = true;
    }
}

/**
 * Saves user preferences to localStorage when the form is submitted.
 * @function savePreferences
 * @param {SubmitEvent} event - The form submission event
 * @returns {void}
 */
function savePreferences(event) {


    const checkboxSombre = document.getElementById("checkboxSombre");
    const checkboxMalvoyant = document.getElementById("checkboxMalvoyant");
    const radioDaltonien = document.querySelector('input[name="daltonism-type"]:checked');

    /* =========================
       Theme Save
    ========================== */
    if (checkboxSombre && checkboxSombre.checked) {
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }

    /* =========================
       Visually Impaired Save
    ========================== */
    if (checkboxMalvoyant && checkboxMalvoyant.checked) {
        localStorage.setItem("Police", "malvoyant");
    } else {
        localStorage.setItem("Police", "voyant");
    }

    /* =========================
      Color Blindness Type Save
    ========================== */
    if (radioDaltonien && radioDaltonien.value) {
        localStorage.setItem("typeDaltonien", radioDaltonien.value);
    } else {
        localStorage.removeItem("typeDaltonien");
    }

    applyPreferences();
}

/**
 * Initializes the script when the DOM is fully loaded.
 * @event DOMContentLoaded
 */
document.addEventListener("DOMContentLoaded", () => {
    applyPreferences();

    const form = document.getElementById("accessForm");

    if (form) {
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            savePreferences(event);
        });
    }
});
