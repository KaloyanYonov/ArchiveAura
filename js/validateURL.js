function validateURL() {
    const urlInput = document.getElementById('url').value.trim();
    const urlError = document.getElementById('url-error');

    const urlPattern = /^(https?:\/\/)[^\s/$.?#].[^\s]*$/i;

    if (!urlPattern.test(urlInput)) {
        urlError.textContent = "Моля въведете валиден URL (започващ с http:// или https://)";
        return false; 
    }

    urlError.textContent = "";

    return true;
}
