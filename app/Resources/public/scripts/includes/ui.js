// slide panel
(function slidePanel() {

    let $slidePanel = $(".slide-panel");
    let $openButton = $(".open-slide-panel");
    let $closeButton = $(".close-slide-panel");
    let $overlay = $("#overlay");

    $openButton.on("click", open);

    // slide panel can be closed with a click on close button or overlay
    $closeButton.on("click", close);
    $overlay.on("click", close);

    function open() {
        show($overlay);
        show($slidePanel);
    }

    function close() {
        hide($slidePanel);
        hide($overlay);
    }

    function show($element) {
        $element.addClass("in");
    }

    function hide($element) {
        $element.removeClass("in");
    }
})();

// toggle category menus
(function toggle() {

    $(document).on("click", ".menu-toggle", function(e) {

        e.preventDefault();

        let $category = $(this).closest(".category");

        // ignore toggling if no sub-menu
        if( ! $category.find(".category").length ) {
            return;
        }

        // toggle class
        $category.toggleClass("in");

        // animate icon
        setTimeout(function() {
            $category.find("i").toggleClass("fa-plus fa-minus");
        }, 100); // related to duration set in stylesheet (100 = .1s)

        // show/hide filter content
        $category.children(".wrapper").toggle("fast");
    });
})();

// header account popins don't disappear with auto suggestions
$('.quick-access').find('input, .btn').on('click', function () {

    let $quickAccess = $(this).closest('.quick-access');
    $quickAccess.addClass("in");

    $(this).on('blur', function() {
        $quickAccess.removeClass("in");
    });
});


// notifications
// =============

// hide notification behaviour
function showAlerts() {

    let $alerts = $(".notifications .alert");

    $alerts.addClass("in"); // animate in

    $alerts.each(function() {
        let $self = $(this);

        setTimeout(function(){
            removeAlert($self); // remove with time
        }, 6000);

        $(this).find('.close').on('click', function() {
            removeAlert($self); // remove on click
        });
    });
}

// display alerts on page load, with a small delay for animation
setTimeout(function() {
    showAlerts();
}, 100);

// hide and remove alert
function removeAlert($alert) {
    $alert.removeClass('in'); // animate out

    // wait 1 second for the animation to be done
    setTimeout(function(){
        $alert.remove(); // remove from DOM
    }, 1000);
}

/**
 * helper to create notifications
 * uses Bootstrap classes: "success", "warning", "danger", eg.:
 * createAlert("Hello world!", "success");
 */
function createAlert(message, type) {
    let $notifications = $(".notifications");
    let $alert = "<div class='alert alert-" + type + "'><span>" + message + "</span><i class='close-notification fa fa-close' data-dismiss='alert'></i></div>";

    $notifications.append($alert);

    // small delay is for animation to kick off
    setTimeout(function() {
        showAlerts();
    }, 100);
}
