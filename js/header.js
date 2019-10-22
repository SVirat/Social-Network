function changeActive(id) {

    document.getElementById(id).classList.add("active");
    var icons = document.getElementById("icons").getElementsByTagName("a");

    for(var i = 0; i < icons.length; i++) {
        if(icons[i].id != id) {
            document.getElementById(icons[i].id).classList.remove("active");
        }
    }

}