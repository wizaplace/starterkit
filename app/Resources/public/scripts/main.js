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
    $('.ellipsis').dotdotdot();

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


    // notifications
    // =============

    // hide notification behaviour
    function showAlerts() {

        let $alerts = $(".notifications .alert");

        $alerts.addClass("in"); // animate in

        $alerts.each(function() {
            let $self = $(this);

            setTimeout(function(){
                removeAlert($self); // remove with time
            }, 6000);

            $(this).find('.close').on('click', function() {
                removeAlert($self); // remove on click
            });
        });
    }
    showAlerts(); // display alerts on page load

    // hide and remove alert
    function removeAlert($alert) {
        $alert.removeClass('in'); // animate out

        // wait 1 second for the hiding animation to be done
        setTimeout(function(){
            $alert.remove(); // remove from DOM
        }, 1000);
    }

    /**
     * helper to create notifications
     * uses Bootstrap classes: "success", "warning", "danger", eg.:
     * createAlert("Hello world!", "success");
     */
    function createAlert(message, type) {
        let $notifications = $(".notifications");
        let $alert = "<div class='alert alert-" + type + "'><span>" + message + "</span><i class='close-notification fa fa-close' data-dismiss='alert'></i></div>";

        $notifications.append($alert);

        // let some time for animation
        setTimeout(function() {
            showAlerts();
        }, 100);
    }


    // modify basket
    // =============

    // change quantity
    // TODO: add a mechanism to make the page reload optional (use case: product removal from basket popin)
    // TODO: add CSRF token
    function modifyQuantity(productId, quantity) {
        $.post(
            "/basket/update", {
                product_id: productId,
                quantity: quantity
            }

        ).done(function () {
            window.location.reload();

        }).fail(function() {
            createAlert("Une erreur est survenue.", "danger");
        });
    }

    // remove a product from basket
    $('.remove-from-basket').on('click', function() {
        let $product = $(this).closest('.product');
        let productId = $product.data('id');
        let quantity = $product.data('quantity');

        modifyQuantity(productId, quantity);
    });
});
