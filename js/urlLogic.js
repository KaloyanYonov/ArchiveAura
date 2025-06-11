document.getElementById('archiveForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const urlInput = document.getElementById('url').value.trim();

    if (!/^https?:\/\//.test(urlInput)) {
        alert("Моля въведете валиден URL (http:// или https://)");
        return;
    }

    const slug = urlInput.replace(/[^a-zA-Z0-9]+/g, '_');

    const newUrl = window.location.pathname + '/' + encodeURIComponent(urlInput);
    window.history.pushState({}, '', newUrl);

    window.ARCHIVE_URL = urlInput;
    window.ARCHIVE_SLUG = slug;

    fetch('archive.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({url: urlInput})
    })
    .then(response => response.text())
    .then(html => {
        document.body.innerHTML = html;  
        window.history.pushState({}, '', newUrl);  
    });
});