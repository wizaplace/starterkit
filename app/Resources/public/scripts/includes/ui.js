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
