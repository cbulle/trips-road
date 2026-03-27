/**
 * @file Public Roadtrips Export Management
 * @description Handles the downloading of GPX and PDF files for public roadtrips without leaving the page.
 */

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.js-export-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default anchor behavior

            const gpxUrl = this.getAttribute('data-gpx');
            const pdfUrl = this.getAttribute('data-pdf');

            if (gpxUrl && pdfUrl) {
                downloadExportPack(gpxUrl, pdfUrl);
            }
        });
    });
});

/**
 * Creates an invisible iframe to trigger a file download.
 * This technique prevents the browser from navigating away or opening a blank tab.
 * * @function downloadFile
 * @param {string} url - The URL of the file to download
 */
function downloadFile(url) {
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    document.body.appendChild(iframe);

    setTimeout(() => {
        document.body.removeChild(iframe);
    }, 5000);
}

/**
 * Downloads both GPX and PDF files sequentially.
 * A slight delay is added to ensure the browser processes multiple downloads correctly.
 * * @function downloadExportPack
 * @param {string} gpxUrl - The URL of the GPX file
 * @param {string} pdfUrl - The URL of the PDF file
 */
function downloadExportPack(gpxUrl, pdfUrl) {
    downloadFile(gpxUrl);

    setTimeout(() => {
        downloadFile(pdfUrl);
    }, 1000);
}
