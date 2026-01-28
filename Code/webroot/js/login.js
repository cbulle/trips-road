function handleCredentialResponse(response) {
    const token = response.credential;

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
            window.location.href = '/profil';
        } else {
            console.error("Erreur Google : " + data.message);
            alert("Erreur : " + data.message);
        }
    });
}