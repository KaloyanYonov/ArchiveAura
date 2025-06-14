function openCalendar() {
    document.getElementById("calendarModal").style.display = "flex";
    const today = new Date();
    loadCalendar(today.getFullYear(), today.getMonth() + 1);
}

function closeCalendar() {
    document.getElementById("calendarModal").style.display = "none";
}

function loadCalendar(year, month) {
    fetch(`calendar.php?year=${year}&month=${month}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("calendarContent").innerHTML = html;
        });
}