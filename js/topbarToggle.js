const toggleBtn = document.getElementById("toggleBar");
const topbar = document.getElementById("topbar");
const toggleContainer = document.getElementById("toggleContainer");

function moveToggleInside() {
    topbar.appendChild(toggleBtn);
    toggleBtn.innerHTML = "⬆️ Скрий лентата";
}

function moveToggleOutside() {
    toggleContainer.appendChild(toggleBtn);
    toggleBtn.innerHTML = "⬇️ Покажи лентата";
}

toggleBtn.addEventListener("click", () => {
    if (topbar.style.display === "none") {
        topbar.style.display = "flex";
        moveToggleInside();
    } else {
        topbar.style.display = "none";
        moveToggleOutside();
    }
});

moveToggleInside();