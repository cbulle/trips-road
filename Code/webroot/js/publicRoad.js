function telechargerFichier(url) {
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    document.body.appendChild(iframe);

    setTimeout(() => {
        document.body.removeChild(iframe);
    }, 5000);
}

function telechargerPackExport(gpxUrl, pdfUrl) {
    telechargerFichier(gpxUrl);

    setTimeout(() => {
        telechargerFichier(pdfUrl);
    }, 1000);
}
