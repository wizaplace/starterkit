const slick = {
    init: function () {

        // home page banners
        $('.js-slick-banner').slick({
            dots: true,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 6000,
            speed: 300, // transition speed
            slidesToShow: 1,
            slidesToScroll: 1,
        });

        // product group
        $('.js-slick-product-group').slick({
            dots: true,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 6000,
            speed: 300, // transition speed
            slidesToShow: 4,
            slidesToScroll: 4,
        });

        // declination images
        var $declinationImages = $('.js-slick-declination-images');
        var $mainImage = $declinationImages.find('.slider-for');
        var $thumbnails = $declinationImages.find('.slider-nav');

        // main image
        $mainImage.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            asNavFor: '.slider-nav'
        });

        // thumbnails
        $thumbnails.slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            asNavFor: '.slider-for',
            dots: false,
            centerMode: true,
            focusOnSelect: true
        });
    }
};
