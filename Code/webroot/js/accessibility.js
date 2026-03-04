document.addEventListener("DOMContentLoaded", function() {

    const checkboxSombre = document.getElementById("checkboxSombre");
    const checkboxMalvoyant = document.getElementById("checkboxMalvoyant");
    const daltonienRadios = document.querySelectorAll('input[name="daltonism-type"]');

    // --- 1. CHARGEMENT INITIAL ---
    function appliquerPreferences() {
        const savedTheme = localStorage.getItem("theme");
        const savedMalvoyant = localStorage.getItem("Police");
        const savedDaltonienType = localStorage.getItem("typeDaltonien");

        if (savedTheme === "dark") {
            document.documentElement.classList.add("dark", "SombreBtn");
            if (checkboxSombre) checkboxSombre.checked = true;
        } else {
            document.documentElement.classList.remove("dark", "SombreBtn");
            if (checkboxSombre) checkboxSombre.checked = false;
        }

        if (savedMalvoyant === "malvoyant") {
            document.documentElement.classList.add("malvoyant", "MalvoyantBtn");
            if (checkboxMalvoyant) checkboxMalvoyant.checked = true;
        } else {
            document.documentElement.classList.remove("malvoyant", "MalvoyantBtn");
            if (checkboxMalvoyant) checkboxMalvoyant.checked = false;
        }

        document.documentElement.classList.remove("daltonien", "protanopia", "deuteranopia", "tritanopia");

        if (savedDaltonienType && savedDaltonienType !== "aucun") {
            document.documentElement.classList.add("daltonien", savedDaltonienType);
            const radioToCheck = document.querySelector(`input[name="daltonism-type"][value="${savedDaltonienType}"]`);
            if (radioToCheck) radioToCheck.checked = true;
        } else {
            const radioAucun = document.querySelector('input[name="daltonism-type"][value="aucun"]');
            if (radioAucun) radioAucun.checked = true;
        }
    }

    appliquerPreferences();

    // --- 2. APERÇU EN DIRECT (LIVE PREVIEW) ---
    if (checkboxSombre) {
        checkboxSombre.addEventListener("change", function() {
            if (this.checked) {
                document.documentElement.classList.add("dark", "SombreBtn");
            } else {
                document.documentElement.classList.remove("dark", "SombreBtn");
            }
        });
    }

    if (checkboxMalvoyant) {
        checkboxMalvoyant.addEventListener("change", function() {
            if (this.checked) {
                document.documentElement.classList.add("malvoyant", "MalvoyantBtn");
            } else {
                document.documentElement.classList.remove("malvoyant", "MalvoyantBtn");
            }
        });
    }

    daltonienRadios.forEach(radio => {
        radio.addEventListener("change", function() {
            document.documentElement.classList.remove("daltonien", "protanopia", "deuteranopia", "tritanopia");
            if (this.value !== "aucun") {
                document.documentElement.classList.add("daltonien", this.value);
            }
        });
    });

    // --- 3. SAUVEGARDE FINALE ---
    const form = document.getElementById("AccessForm");

    if (form) {
        form.addEventListener("submit", function() {
            if (checkboxSombre && checkboxSombre.checked) {
                localStorage.setItem("theme", "dark");
            } else {
                localStorage.setItem("theme", "light");
            }

            if (checkboxMalvoyant && checkboxMalvoyant.checked) {
                localStorage.setItem("Police", "malvoyant");
            } else {
                localStorage.setItem("Police", "voyant");
            }

            const radioDaltonienChecked = document.querySelector('input[name="daltonism-type"]:checked');
            if (radioDaltonienChecked && radioDaltonienChecked.value !== "aucun") {
                localStorage.setItem("typeDaltonien", radioDaltonienChecked.value);
            } else {
                localStorage.removeItem("typeDaltonien");
            }
        });
    }
});
