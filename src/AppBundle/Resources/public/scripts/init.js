import notification from './includes/notification';
import ui from './includes/ui';
import slick from './includes/slick-carousel';
import reCaptcha from './includes/recaptcha';
import basket from './includes/basket';

export default function init () {

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
    slick.init();
    ui.initSlickShowcase(); // legacy

    // init reCaptcha behaviour
    reCaptcha.init();

    // init basket behaviour
    basket.init();
}
