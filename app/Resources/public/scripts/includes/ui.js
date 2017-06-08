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