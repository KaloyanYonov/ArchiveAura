

let container = document.getElementById("form-container");
let darkMode = document.getElementById("dark-mode");
let hideBtn = document.getElementById("hideBtn");

let isHidden = false;
let isDark = false;

darkMode.addEventListener("click", function(){
    if(!isDark){
        document.body.style.background = "black";
        document.body.style.color = "white";
        darkMode.textContent = "Swtich to light mode";
        isDark = true;
    }
    else{
        document.body.style.background = "white";
        document.body.style.color = "black";
        darkMode.textContent = "Swtich to Dark mode";
        isDark = false;
    }

    
})

hideBtn.addEventListener("click" , function(){
    if(!isHidden){
        container.style.visibility = "hidden";
        hideBtn.textContent = "Show";
        isHidden = true;
    }
    else{
        container.style.visibility = "visible";
        hideBtn.textContent = "X";
        isHidden = false;
    }
    
})

