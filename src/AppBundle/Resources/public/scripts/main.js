// executed when page is fully loaded
$(function() {

    // display alerts on page load
    notification.initAlerts();

    // Replace exceeding text with ellipsis (three dots)
    $('.ellipsis').dotdotdot();

    // convert rating scores with stars
    ui.renderRatings();

    // header account popins don't disappear with auto suggestions
    ui.initPopins();

    // init slide panel behaviour
    ui.initSlidePanel();

    // init category menu toggling behaviour
    ui.initCategoryToggle();

    // init slick behaviour
    ui.initSlick();
    ui.initSlickShowcase(); // legacy

    // init reCaptcha behaviour
    reCaptcha.init();

    // init basket behaviour
    basket.init();
});
