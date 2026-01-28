/*=======================================
  Barre de recherche
=======================================*/
let data = [];
let userId = null;

// --- CSS injecté (CORRIGÉ POUR LE BUG DU RECTANGLE ORANGE) ---
const style = document.createElement('style');


// --- Chargement ---
fetch("/api/all_roadtrips")
    .then(response => response.json())
    .then(json => {
        userId = json.userId;
        data = json.roadtrips;
        console.log("✅ Données chargées :", data.length);
    })
    .catch(error => console.error("❌ Erreur fetch :", error));

const searchBox = document.getElementById('searchInput');

// Création du tableau s'il n'existe pas
let resultsTable = document.getElementById('results-table');
let resultsTableBody;

if (!resultsTable) {
    resultsTable = document.createElement('div');
    resultsTable.id = 'results-table';
    const innerTable = document.createElement('table');
    resultsTableBody = document.createElement('tbody');
    innerTable.appendChild(resultsTableBody);
    resultsTable.appendChild(innerTable);
    document.body.appendChild(resultsTable);
} else {
    resultsTableBody = resultsTable.querySelector('tbody');
    if (resultsTable.parentElement !== document.body) {
        document.body.appendChild(resultsTable);
    }
}

if (searchBox && resultsTableBody) {

    function positionnerTableau() {
        const rect = searchBox.getBoundingClientRect();
        resultsTable.style.top = (rect.bottom + window.scrollY) + "px";
        resultsTable.style.left = (rect.left + window.scrollX) + "px";
        resultsTable.style.width = rect.width + "px"; 
    }

    function effectuerRecherche() {
        const query = searchBox.value.trim().toLowerCase();
        resultsTableBody.innerHTML = '';

        if (query.length < 2) {
            resultsTable.style.display = 'none';
            return;
        }

        const filteredData = data.filter(item => {
            const matchTitle = item.titre.toLowerCase().includes(query);
            if (!matchTitle) return false;
            
            return true;
        });

        if (filteredData.length > 0) {
            positionnerTableau();
            resultsTable.style.display = 'block';
            
            filteredData.forEach(item => {
                const row = document.createElement('tr');
                
                row.addEventListener('mousedown', function(e) {
                    window.location.href = "/vuRoadTrip?id=" + item.id;
                });

                const cell = document.createElement('td');
                
                // TITRE + TAG
                let html = `<span class="trip-title">${item.titre}`;
                if (item.visibilite === 'prive') {
                    html += `<span class="tag-prive">(Privé)</span>`;
                } else if (item.visibilite === 'amis') {
                    html += `<span class="tag-amis">(Amis)</span>`;
                }
                html += `</span>`;
                
                // AUTEUR
                if (item.pseudo) {
                    html += `<span class="trip-author">Proposé par : ${item.pseudo}</span>`;
                }
                
                cell.innerHTML = html;
                row.appendChild(cell);
                resultsTableBody.appendChild(row);
            }); 
        } else {
            positionnerTableau();
            resultsTable.style.display = 'block';
            const row = document.createElement('tr');
            row.innerHTML = `<td style="color:#777; text-align:center;">Aucun résultat</td>`;
            resultsTableBody.appendChild(row);
        }
    }

    // --- ÉVÉNEMENTS ---
    window.addEventListener('resize', () => {
        if (resultsTable.style.display === 'block') positionnerTableau();
    });

    searchBox.addEventListener('input', effectuerRecherche);

    searchBox.addEventListener('focus', function() {
        if (searchBox.value.trim().length >= 2) {
            positionnerTableau();
            resultsTable.style.display = 'block';
            if (resultsTableBody.innerHTML === '') effectuerRecherche();
        }
    });

    searchBox.addEventListener('blur', function() {
        setTimeout(() => {
            resultsTable.style.display = 'none';
        }, 200);
    });
    
} else {
    console.error("❌ Erreur : Impossible de trouver #searchInput");
}

/*=======================================
  Changement de theme
=======================================*/
 
const savedTheme = localStorage.getItem("theme");
const toggleSombre = document.getElementById("checkboxSombre");
 
const savedMalvoyant = localStorage.getItem("Police");
const toggleMalvoyant = document.getElementById("checkboxMalvoyant");

const toggleDaltonien = document.getElementById("checkboxD");
const savedMode = localStorage.getItem("modeDaltonien");
const savedType = localStorage.getItem("typeDaltonien");

const daltonienTypes = {
    protanopia: 'protanopia',
    deutéranopia: 'deuteranopia',
    tritanopia: 'tritanopia',
};

 
if (savedTheme === "dark") {
    document.documentElement.classList.add("dark");
    document.documentElement.classList.add("SombreBtn");
}
 
if (toggleSombre) {
    toggleSombre.checked = savedTheme === "dark";
 
    toggleSombre.addEventListener("change", () => {
        if (toggleSombre.checked) {
            document.documentElement.classList.add("dark");
            document.documentElement.classList.add("SombreBtn");
            localStorage.setItem("theme", "dark");
        } else {
            document.documentElement.classList.remove("dark");
            document.documentElement.classList.remove("SombreBtn");
            localStorage.setItem("theme", "light");
        }
    });
}
 
if (savedMalvoyant === "malvoyant") {
    document.documentElement.classList.add("malvoyant");
    document.documentElement.classList.add("MalvoyantBtn");
}
 
if (toggleMalvoyant) {
    toggleMalvoyant.checked = savedMalvoyant === "malvoyant";
 
    toggleMalvoyant.addEventListener("change", () => {
        if (toggleMalvoyant.checked) {
            document.documentElement.classList.add("malvoyant");
            document.documentElement.classList.add("MalvoyantBtn");
            localStorage.setItem("Police", "voyant");
        } else {
            document.documentElement.classList.remove("malvoyant");
            document.documentElement.classList.remove("MalvoyantBtn");
            localStorage.setItem("Police", "voyant");
        }
    });
}


if (savedMode === "on") {
    document.documentElement.classList.add("daltonien");
    if (savedType) {
        document.documentElement.classList.add(savedType);
    }
    toggleDaltonien.checked = true;
}

toggleDaltonien.addEventListener("change", () => {
    const selectedType = document.querySelector('input[name="daltonism-type"]:checked').value;
    if (toggleDaltonien.checked) {
        document.documentElement.classList.add("daltonien");
        document.documentElement.classList.add(selectedType);
        localStorage.setItem("modeDaltonien", "on");
        localStorage.setItem("typeDaltonien", selectedType);
    } else {
        document.documentElement.classList.remove("daltonien");
        document.documentElement.classList.remove(daltonienTypes.protanopia);
        document.documentElement.classList.remove(daltonienTypes.deutéranopia);
        document.documentElement.classList.remove(daltonienTypes.tritanopia);
        localStorage.setItem("modeDaltonien", "off");
    }
});



 
