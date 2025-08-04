const container = document.getElementById("form-container");
const darkModeBtn = document.getElementById("dark-mode");
const hideBtn = document.getElementById("hideBtn");

let mode = "normal"; 
let isCollapsed = false;

darkModeBtn.addEventListener("click", function () {
    if (mode === "normal") {
        document.body.style.background = "#0d1117";
        document.body.style.color = "#c9d1d9";
        darkModeBtn.textContent = "☀️ Светъл режим";
        mode = "dark";
    } else if (mode === "dark") {
        document.body.style.background = "#eeeeee";
        document.body.style.color = "#222222";
        darkModeBtn.textContent = "🔁 Нормален режим";
        mode = "light";
    } else {
        document.body.style.background = "linear-gradient(145deg, #0a2f2f, #0f3d3d)";
        document.body.style.color = "#e0f7fa";
        darkModeBtn.textContent = "🌙 Тъмен режим";
        mode = "normal";
    }
});

hideBtn.addEventListener("click", function () {
    if (!isCollapsed) {
        container.style.maxHeight = "0px";
        container.style.overflow = "hidden";
        container.style.transition = "max-height 0.5s ease";
        hideBtn.textContent = "▼ Show";
        isCollapsed = true;
    } else {
        container.style.maxHeight = "500px";
        container.style.transition = "max-height 0.5s ease";
        hideBtn.textContent = "✖️";
        isCollapsed = false;
    }
});