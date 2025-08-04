document.getElementById('screenshotBtn').addEventListener('click', () => {
    const iframe = document.querySelector('iframe');
    const filename = iframe.getAttribute('data-filename') || 'screenshot';
    console.log("📸 Filename from data-filename:", filename);

    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

    html2canvas(iframeDoc.body).then(canvas => {
        const link = document.createElement('a');
        link.download = `${filename}.png`;
        link.href = canvas.toDataURL();
        link.click();
    });
});
