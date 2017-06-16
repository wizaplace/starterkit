var W = {
    // For search results
    toggleLoadingBox: function toggleLoadingBox (action) {
        $('.ajax-loading-box').toggle(action);
    },

    truncate: function truncate (string, length) {
        if (string && string.length > length) {
            string = string.substring(0, length-1)+'…';
        }
        return string;
    },

    formatPrice: function formatPrice (price) {
        if (!$.isNumeric(price)) {
            return '';
        }
        price = price.toFixed(2) + '';
        return price.replace('.', ',') + '€';
    },

    generateSlug: function (string) {
        // Replace anything that isn't a word character by an underscore
        return string.replace(/\W/g,'_');
    },

    /**
     * Render the ratings with stars
     */
    renderRatings: function () {
        $('.js-rating').rating({
            min: 0,
            max: 5,
            step: 1,
            size: 'sm',
            showClear: false,
            showCaption: false
        });
    }
};

// executed when page is fully loaded
$(function() {

    // convert rating scores with stars
    W.renderRatings();

    // Replace exceeding text with ellipsis (three dots)
    function ellipsis() {
        $('.ellipsis').dotdotdot();
    }
    ellipsis(); // execute on page load


    // slick
    // =====

    var $arrows = $('.arrows');
    var $next = $arrows.children('.products-next');
    var $prev = $arrows.children('.products-prev');

    var slick = $('.product-container').slick(
        {
            dots: true,
            infinite: true,
            speed: 300,
            slidesToShow: 4,
            slidesToScroll: 4,
            arrows: false,
            appendArrows: $arrows,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                        infinite: true,
                        dots: true
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        }
    );

    $('.products-next').on('click', function () {
        var i = $next.index( this );
        slick.eq(i).slick('slickNext');
    });

    $('.products-prev').on('click', function () {
        var i = $prev.index( this );
        slick.eq(i).slick("slickPrev");
    });
});
