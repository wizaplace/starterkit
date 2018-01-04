import notification from '../includes/notification';
import slick from '../includes/slick-carousel';
import reCaptcha from '../includes/recaptcha';
import basket from '../includes/basket';

export default function init () {

    // display alerts on page load
    notification.initAlerts();

    // Replace exceeding text with ellipsis (three dots)
    $('.ellipsis').dotdotdot();

    // init slick behaviour
    slick.init();

    // init reCaptcha behaviour
    reCaptcha.init();
}
