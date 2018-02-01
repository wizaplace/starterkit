const ui = {

    // init star rating lib
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
};
