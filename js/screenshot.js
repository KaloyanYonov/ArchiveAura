document.getElementById('screenshotBtn').addEventListener('click', () => {
    const iframe = document.querySelector('iframe');
    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

    html2canvas(iframeDoc.body).then(canvas => {
        const link = document.createElement('a');
        link.download = 'screenshot.png';
        link.href = canvas.toDataURL();
        link.click();
    });
});
