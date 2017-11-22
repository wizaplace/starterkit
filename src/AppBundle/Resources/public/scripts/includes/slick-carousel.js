const slick = {
    init: function () {

        // home page banners
        $('[class*="js-slick-banner"]').slick({
            dots: true,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 6000,
            speed: 300, // transition speed
            slidesToShow: 1,
            slidesToScroll: 1,
        });
    }
};
