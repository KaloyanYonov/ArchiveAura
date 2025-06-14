document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleBar");
    const topbar = document.getElementById("topbar");

    toggleBtn.addEventListener("click", () => {
        topbar.classList.toggle("hidden");

        if (topbar.classList.contains("hidden")) {
            toggleBtn.innerHTML = "⬇️ Покажи лентата";
        } else {
            toggleBtn.innerHTML = "⬆️ Скрий лентата";
        }
    });
});