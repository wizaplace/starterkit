import init from './global/init';
import header from './global/header';

// when libraries and template are fully loaded
$(function() {

    // launch helpers (notifications, slick, reCaptcha, etc.)
    init();

    // header behaviour (search features)
    header();
});
