function validateLogin() {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const error = document.getElementById('login-error');

    if (email === '' || password === '') {
        error.textContent = "Моля попълнете всички полета.";
        return false;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        error.textContent = "Моля въведете валиден email.";
        return false;
    }

    error.textContent = "";
    return true;
}
