document.addEventListener("DOMContentLoaded", function() {

    function appliquerPreferences() {
        const savedTheme = localStorage.getItem("theme");
        const savedMalvoyant = localStorage.getItem("Police");
        const savedDaltonienType = localStorage.getItem("typeDaltonien");

        const checkboxSombre = document.getElementById("checkboxSombre");
        const checkboxMalvoyant = document.getElementById("checkboxMalvoyant");

        if (savedTheme === "dark") {
            document.documentElement.classList.add("dark");
            document.documentElement.classList.add("SombreBtn");
            if (checkboxSombre) checkboxSombre.checked = true;
        } else {
            document.documentElement.classList.remove("dark");
            document.documentElement.classList.remove("SombreBtn");
            if (checkboxSombre) checkboxSombre.checked = false;
        }

        if (savedMalvoyant === "malvoyant") {
            document.documentElement.classList.add("malvoyant");
            document.documentElement.classList.add("MalvoyantBtn");
            if (checkboxMalvoyant) checkboxMalvoyant.checked = true;
        } else {
            document.documentElement.classList.remove("malvoyant");
            document.documentElement.classList.remove("MalvoyantBtn");
            if (checkboxMalvoyant) checkboxMalvoyant.checked = false;
        }

        document.documentElement.classList.remove("daltonien", "protanopia", "deuteranopia", "tritanopia");

        if (savedDaltonienType && savedDaltonienType !== "aucun") {
            document.documentElement.classList.add("daltonien");
            document.documentElement.classList.add(savedDaltonienType);

            const radioToCheck = document.querySelector(`input[name="daltonism-type"][value="${savedDaltonienType}"]`);
            if (radioToCheck) radioToCheck.checked = true;
        } else {
            const radioAucun = document.querySelector('input[name="daltonism-type"][value="aucun"]');
            if (radioAucun) radioAucun.checked = true;
        }
    }

    appliquerPreferences();

    const form = document.getElementById("AccessForm");

    if (form) {
        form.addEventListener("submit", function(event) {
            const checkboxSombre = document.getElementById("checkboxSombre");
            const checkboxMalvoyant = document.getElementById("checkboxMalvoyant");
            const radioDaltonien = document.querySelector('input[name="daltonism-type"]:checked');

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

            if (radioDaltonien && radioDaltonien.value) {
                localStorage.setItem("typeDaltonien", radioDaltonien.value);
            } else {
                localStorage.removeItem("typeDaltonien");
            }
        });
    }
});
