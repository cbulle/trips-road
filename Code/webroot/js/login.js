function handleCredentialResponse(response) {
    const token = response.credential;

    // CORRECTION ICI : on appelle la route définie dans app.php
    fetch('/google-auth', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'credential=' + encodeURIComponent(token)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirection vers le profil si tout est OK
                window.location.href = '/profil';
            } else {
                console.error(data);
                alert("Erreur : " + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur Fetch:', error);
        });
}