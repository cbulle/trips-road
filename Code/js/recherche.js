/*=======================================
  Barre de recherche
=======================================*/
 
let data = [];
let userId = null; 
 
fetch("../bd/rech_bd.php")
    .then(response => response.json())
    .then(json => {
        userId = json.userId;  
        data = json.roadtrips;  
 
        console.log("Connecté avec l'ID :", userId);
        console.log("Liste des roadtrips chargés :", data);
    })
    .catch(error => console.error("Erreur fetch :", error));
 
const searchBox = document.getElementById('searchInput');
const resultsTableBody = document.querySelector('#results-table tbody');
 
if (searchBox && resultsTableBody) {
    searchBox.addEventListener('input', function(event) {
        const query = event.target.value.trim().toLowerCase();
        resultsTableBody.innerHTML = '';
 
        if (query.length < 2) return;
 
        const filteredData = data.filter(item => {
            const match = item.titre.toLowerCase().includes(query);
 
    const myId = userId; 
 
    const filteredData = data.filter(item => {
        const matchTitle = item.titre.toLowerCase().includes(query);
        if (!matchTitle) return false;
 
 
        if (item.visibilite === "public") {
            return true;
        }
 
 
        if (item.visibilite === "prive" && myId !== null && item.id_utilisateur == myId) {
            return true;
        }
 
 
        return false;
    });
 
 
    if (filteredData.length > 0) {
        filteredData.forEach(item => {
            const row = document.createElement('tr');
 
 
            const nomCell = document.createElement('td');
            nomCell.textContent = item.titre + ' (Road-Trip)';
 
 
            if (item.visibilite === 'prive') {
                nomCell.textContent += ' (Privé)';
                nomCell.style.fontStyle = 'italic';
            }
 
            row.appendChild(nomCell);
            resultsTableBody.appendChild(row);
        });
    }
});
    });
}

/*=======================================
  Changement de th�me
=======================================*/
 
const savedTheme = localStorage.getItem("theme");
const toggleSombre = document.getElementById("checkboxSombre");
 
const savedMalvoyant = localStorage.getItem("Police");
const toggleMalvoyant = document.getElementById("checkboxMalvoyant");
 
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
            localStorage.setItem("Police", "malvoyant");
        } else {
            document.documentElement.classList.remove("malvoyant");
            document.documentElement.classList.remove("MalvoyantBtn");
            localStorage.setItem("Police", "voyant");
        }
    });
}
/*=======================================
  Formulaire d'inscription et de connexion
=======================================*/
 
function showLogin() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.style.display = 'block';
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('btnLogin').classList.add('active');
        document.getElementById('btnRegister').classList.remove('active');
    }
}
 
function showRegister() {
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        document.getElementById('loginForm').style.display = 'none';
        regForm.style.display = 'block';
        document.getElementById('btnLogin').classList.remove('active');
        document.getElementById('btnRegister').classList.add('active');
    }
}
 
function openModal() {
    const modal = document.querySelector('.formulaire'); 
    if (modal) {
        modal.style.display = 'block';
    }
}
 
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.formulaire')) {
        openModal();
        showLogin(); 
    }
});