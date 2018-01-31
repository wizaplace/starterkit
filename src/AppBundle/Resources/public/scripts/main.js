// executed when page is fully loaded
$(function() {

    // display alerts on page load
    notification.initAlerts();

    // Replace exceeding text with ellipsis (three dots)
    $('.ellipsis').dotdotdot();

    // init slick behaviour
    slick.init();

    // init reCaptcha behaviour
    reCaptcha.init();

    // init basket behaviour
    basket.init();
});
