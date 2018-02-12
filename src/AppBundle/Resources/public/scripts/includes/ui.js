const ui = {

    // init star rating lib
    renderRatings: function() {

        $('.js-rating').rating({
            min: 0,
            max: 5,
            step: 1,
            filledStar: '<i class="fa fa-star"></i>',
            emptyStar: '<i class="fa fa-star-o"></i>',
            showClear: false,
            showCaption: false
        });
    },
};

