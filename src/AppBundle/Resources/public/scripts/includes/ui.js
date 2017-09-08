const ui = {

    // slide panel
    initSlidePanel: function() {

        let $slidePanel = $(".slide-panel");
        let $openButton = $(".open-slide-panel");
        let $closeButton = $(".close-slide-panel");
        let $overlay = $("#overlay");

        $openButton.on("click", open);

        // slide panel can be closed with a click on close button or overlay
        $closeButton.on("click", close);
        $overlay.on("click", function() {

            // do nothing if there's a loading spinner
            if($('.loading-spinner').hasClass('is-visible')) { return false; }

            close();
        });

        function open() {
            show($overlay);
            show($slidePanel);
        }

        function close() {
            hide($slidePanel);
            hide($overlay);
        }

        function show($element) {
            $element.addClass("is-visible");
        }

        function hide($element) {
            $element.removeClass("is-visible");
        }
    },

    // toggle category menus behaviour
    initCategoryToggle: function() {
        $(document).on("click", ".menu-toggle", function(e) {

            e.preventDefault();

            let $category = $(this).closest(".category");

            // ignore toggling if no sub-menu
            if( ! $category.find(".category").length ) {
                return;
            }

            // toggle class
            $category.toggleClass("in");

            // animate icon
            setTimeout(function() {
                $category.find("i").toggleClass("fa-plus fa-minus");
            }, 100); // related to duration set in stylesheet (100 = .1s)

            // show/hide filter content
            $category.children(".wrapper").toggle("fast");
        });
    },

    // header account popins don't disappear with auto suggestions
    initPopins: function() {

        $('.quick-access').find('input, .btn').on('click', function () {

            let $quickAccess = $(this).closest('.quick-access');
            $quickAccess.addClass("in");

            $(this).on('blur', function() {
                $quickAccess.removeClass("in");
            });
        });
    },

    // convert ratings into stars
    renderRatings: function() {
        $('.js-rating').rating({
            min: 0,
            max: 5,
            step: 1,
            size: 'sm',
            showClear: false,
            showCaption: false
        });
    },

    // slick
    initSlick: function() {
        // home page banners
        $('.banners').find('[class*="-screens"]').slick({
            dots: true,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 6000,
            speed: 300, // transition speed
            slidesToShow: 1,
            slidesToScroll: 1,
        });
    },

    // slick legacy
    initSlickShowcase: function() {
        let $arrows = $('.arrows');
        let $next = $arrows.children('.products-next');
        let $prev = $arrows.children('.products-prev');

        let slick = $('.product-container').slick(
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
            let i = $next.index( this );
            slick.eq(i).slick('slickNext');
        });

        $('.products-prev').on('click', function () {
            let i = $prev.index( this );
            slick.eq(i).slick("slickPrev");
        });
    }
};
