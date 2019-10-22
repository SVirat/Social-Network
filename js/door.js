
function changeActive(id) {
    var navItem = document.getElementById(id);
    document.getElementById("about-navbar").classList.remove("active");
    document.getElementById("login-navbar").classList.remove("active");
    document.getElementById("signup-navbar").classList.remove("active");
    navItem.classList.add("active");
}

function load() {
    if(window.location.href.indexOf("#login") > -1) {
        show("login-navbar");
        changeActive("login-navbar");
    }
    else if (window.location.href.indexOf("#sign-up") > -1) {
        show("signup-navbar");
        changeActive("signup-navbar");
    }
    else if(window.location.href.indexOf("#about") > -1) {
        show("about-navbar");
        changeActive("about-navbar");
    }
}

function show(form) {
    var about = document.getElementById("about");
    var login = document.getElementById("login");
    var signup = document.getElementById("sign-up");
    if(form === "signup-navbar") {
        about.style.display = "none";
        login.style.display = "none";
        signup.style.display = "block";
    }
    else if(form === "login-navbar") {
        about.style.display = "none";
        signup.style.display = "none";
        login.style.display = "block";
    }
    else if(form === "about-navbar") {
        about.style.display = "block";
        signup.style.display = "none";
        login.style.display = "none";
    }
}