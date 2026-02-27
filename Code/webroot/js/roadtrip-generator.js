/**
 * Assistant Roadtrip IA - Gestionnaire du Questionnaire Modal
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('roadtrip-ai-form');
    if (!form) return;

    // Éléments d'interface du questionnaire
    const steps = document.querySelectorAll('.step');
    const progressBar = document.getElementById('progress-bar');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const generateBtn = document.getElementById('generate-btn');
    let currentStep = 1;

    /**
     * 1. NAVIGATION DANS LE FORMULAIRE PAS À PAS
     */
    const updateUI = () => {
        // Barre de progression
        const percent = (currentStep / steps.length) * 100;
        if (progressBar) progressBar.style.width = percent + '%';

        // Visibilité des boutons
        if (prevBtn) prevBtn.classList.toggle('d-none', currentStep === 1);
        if (nextBtn) nextBtn.classList.toggle('d-none', currentStep === steps.length);
        if (generateBtn) generateBtn.classList.toggle('d-none', currentStep !== steps.length);
    };

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const currentEl = document.querySelector(`[data-step="${currentStep}"]`);
            // Validation simple : vérifie si les champs requis de l'étape actuelle sont remplis
            const inputs = currentEl.querySelectorAll('input[required], select[required]');
            let valid = true;
            inputs.forEach(input => { if(!input.value) valid = false; });

            if (valid) {
                currentEl.classList.add('d-none');
                currentStep++;
                document.querySelector(`[data-step="${currentStep}"]`).classList.remove('d-none');
                updateUI();
            } else {
                alert("Veuillez remplir les champs obligatoires.");
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            document.querySelector(`[data-step="${currentStep}"]`).classList.add('d-none');
            currentStep--;
            document.querySelector(`[data-step="${currentStep}"]`).classList.remove('d-none');
            updateUI();
        });
    }

    /**
     * 2. ENVOI DES DONNÉES ET RÉCEPTION DE L'ITINÉRAIRE
     */
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Sécurité CakePHP : Récupération du jeton CSRF
        const csrfTokenInput = document.querySelector('input[name="_csrfToken"]');
        if (!csrfTokenInput) {
            console.error("Erreur de sécurité : Jeton CSRF introuvable.");
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');

        // Préparation des données
        const formData = new FormData(this);
        const jsonData = Object.fromEntries(formData.entries());

        // État de chargement visuel dans le modal
        const container = document.getElementById('ai-form-container');
        const backupContent = container.innerHTML;
        container.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="h5">L'IA trace votre itinéraire personnalisé...</p>
                <p class="text-muted">Cela peut prendre quelques secondes.</p>
            </div>
        `;

        fetch('/roadtrips/generer-roadtrip-gratuit', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(jsonData)
        })
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau ou serveur.');
                return response.json();
            })
            .then(res => {
                if (res.success && res.rawSteps) {
                    // Fermeture du Modal Bootstrap 5
                    const modalEl = document.getElementById('iaModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    // Injection dans le formulaire principal de création
                    remplirFormulairePrincipal(res.rawSteps);

                    // Réinitialisation discrète du modal pour une utilisation future
                    setTimeout(() => { container.innerHTML = backupContent; }, 500);
                } else {
                    throw new Error(res.message || "L'IA n'a pas pu générer de réponse valide.");
                }
            })
            .catch(err => {
                console.error('Erreur Assistant IA:', err);
                alert("Erreur : " + err.message);
                container.innerHTML = backupContent; // Restauration en cas d'échec
            });
    });

    /**
     * 3. INJECTION AUTOMATIQUE DANS LA CARTE (Liaison avec map.js)
     */
    function remplirFormulairePrincipal(steps) {
        // On récupère le bouton d'ajout défini dans votre map.js
        const addTrajetBtn = document.getElementById('add-trajet-btn');
        if (!addTrajetBtn) {
            console.error("Bouton d'ajout de trajet introuvable dans form.php");
            return;
        }

        // On boucle sur les étapes générées par l'IA
        steps.forEach((step, index) => {
            // On laisse un petit délai entre chaque ajout pour ne pas saturer le navigateur
            setTimeout(() => {
                // Simule le clic pour créer un nouveau bloc de trajet (via template-trajet)
                addTrajetBtn.click();

                // On attend que map.js ait injecté le template dans le DOM
                setTimeout(() => {
                    const trajetItems = document.querySelectorAll('.trajet-item');
                    const lastItem = trajetItems[trajetItems.length - 1];

                    if (lastItem) {
                        // Remplissage des champs (Classes basées sur votre template-trajet)
                        const inputDep = lastItem.querySelector('.departure-input');
                        const inputArr = lastItem.querySelector('.arrival-input');
                        const selectMode = lastItem.querySelector('.transport-mode');

                        if (inputDep) inputDep.value = step.depart;
                        if (inputArr) inputArr.value = step.arrivee;
                        if (selectMode && step.mode) {
                            selectMode.value = step.mode.toLowerCase();
                        }

                        // TRÈS IMPORTANT : On déclenche l'événement 'change' sur l'arrivée
                        // pour forcer map.js à lancer le géocodage et le tracé de la route
                        if (inputArr) {
                            inputArr.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }, 150); // Petit délai pour le rendu du template
            }, index * 800); // On espace l'ajout de chaque trajet (0.8s) pour le confort visuel
        });
    }
});
