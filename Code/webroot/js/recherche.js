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



