// executed when page is fully loaded
$(function() {

    // display alerts on page load
    notification.initAlerts();

    // Replace exceeding text with ellipsis (three dots)
    $('.ellipsis').dotdotdot();

    // init star rating lib
    ui.renderRatings();

    // init slick behaviour
    slick.init();

    // init reCaptcha behaviour
    recaptcha.init();
    window.recaptchaCallback = recaptcha.callback; // used by google recaptcha script

    // init basket behaviour
    basket.init();
});
