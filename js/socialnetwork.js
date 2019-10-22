$(document).ready(function() {

    $(".search-button-holder").on('click', function() {
        document.search_form.submit();
    });


});

$(document).click(function(e) {
    if(e.target.class != "search_results" && e.target.id != "search_text_input") {
        $(".search_results").html("");
        $(".search_results_footer").html("");
        $(".search_results_footer_empty").html("");
        $(".search_results_footer").toggleClass("search_results_footer_empty");
        $(".search_results_footer").toggleClass("search_results_footer");
    }
    if(e.target.class != "dropdown-data-window") {
        $(".dropdown-data-window").html("");
        $(".dropdown-data-window").css({"padding": "0px", "height": "0px"});
    }
    if(e.target.class != "dropdown-data-window-notification") {
        $(".dropdown-data-window-notification").html("");
        $(".dropdown-data-window-notification").css({"padding": "0px", "height": "0px"});
    }
});


function getLiveSearchUsers(value, user) {
    $.post("ajax_search.php", {
        query: value,
        user_handle: user
    }, function(data) {
        if($(".search_results_footer_empty")[0]) {
            $(".search_results_footer_empty").toggleClass("search_results_footer");
            $(".search_results_footer_empty").toggleClass("search_results_footer_empty");
        }
        $(".search_results").html(data);
        $(".search_results_footer").html("<a href='search.php?q=" + value + "'><span style='background:#F2F3F4;color:blue;'>See All Results<span></a>");

        if(data == "") {
            $(".search_results").html("");
            $(".search_results_footer").toggleClass("search_results_footer_empty");
            $(".search_results_footer").toggleClass("search_results_footer");
        }
    });
}

function getDropdownData(userHandle) {
    if($(".dropdown-data-window").css("height") == "0px") {
        
        pageName = "ajax_load_messages.php";
        $("span").remove("#unread-messages");

        var ajaxreq = $.ajax({
            url: pageName,
            type: "POST",
            data: "page=1&user_handle=" + userHandle,
            cache: false,

            success: function(response) {
                $(".dropdown-data-window").html(response);
                $(".dropdown-data-window").css({"padding": "0px", "height": "280px"});
                $(".dropdown-data-window-notification").html("");
                $(".dropdown-data-window-notification").css({"padding": "0px", "height": "0px"});;
            }
        });
    }
    else {
        $(".dropdown-data-window").html("");
        $(".dropdown-data-window").css({"padding": "0px", "height": "0px"});;
    }
}


function getDropdownNotifications(userHandle) {
    if($(".dropdown-data-window-notification").css("height") == "0px") {
        var pageName;
        
        pageName = "ajax_load_notifications.php";
        $("span").remove("#unread-notifications");

        var ajaxreq = $.ajax({
            url: pageName,
            type: "POST",
            data: "page=1&user_handle=" + userHandle,
            cache: false,

            success: function(response) {
                $(".dropdown-data-window-notification").html(response);
                $(".dropdown-data-window-notification").css({"padding": "0px", "height": "280px"});
                $(".dropdown-data-window").html("");
                $(".dropdown-data-window").css({"padding": "0px", "height": "0px"});;
            }
        });
    }
    else {
        $(".dropdown-data-window-notification").html("");
        $(".dropdown-data-window-notification").css({"padding": "0px", "height": "0px"});;
    }
}