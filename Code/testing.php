<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Recherche de Road Trips</title>
</head>
<body>
    <div class="container">
        <h1>🚗 Recherche de Road Trips</h1>
        
        <div class="search-box">
            <input type="search" id="searchInput" class="search-input" placeholder="Rechercher un road trip..." autocomplete="off">
            <table class="search-results" id="results-table">
                <tbody>
                </tbody>
            </table> 
        </div>

        <div class="results" id="results"></div>
    </div>
<script>
    let data = [];

fetch("../bd/lec_bd.php")
    .then(response => response.json())
    .then(json => {
        data = json;
        console.log("Données chargées :", data);
    })
    .catch(error => console.error("Erreur fetch :", error));


const searchBox = document.getElementById('searchInput');
const resultsTableBody = document.querySelector('#results-table tbody');

searchBox.addEventListener('input', function(event) {

    const query = event.target.value.trim().toLowerCase();
    resultsTableBody.innerHTML = '';

    if (query.length < 2) return;

    const filteredData = data.filter(item =>
        item.titre.toLowerCase().includes(query) ||
        item.description.toLowerCase().includes(query)
    );

    if (filteredData.length > 0) {
        filteredData.forEach(item => {
            const row = document.createElement('tr');

            const nomCell = document.createElement('td');
            nomCell.textContent = item.titre;
            row.appendChild(nomCell);

            const descCell = document.createElement('td');
            descCell.textContent = item.description;
            row.appendChild(descCell);

            resultsTableBody.appendChild(row);
        });
    } else {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 2;
        cell.textContent = 'Aucun résultat trouvé.';
        row.appendChild(cell);
        resultsTableBody.appendChild(row);
    }
});
</script>
</body>
</html>