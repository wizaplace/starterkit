export class PriceSlider {
    static init() {
        priceSlider();
    }
}

// private methods
// ===============

// TODO: isolate for eventual collisions
function priceSlider() {
    let priceSlider = $(".price-slider").slider({
        range: true,
        min: 0,
        max: 500,
        values: [ 75, 300 ],
        slide: function( event, ui ) {
            let minValue = ui.values[0];
            let maxValue = ui.values[1];

            $(".min-value").val(minValue);
            $(".max-value").val(maxValue);
        }
    });

    $(".min-value").on("change", function() {
        priceSlider.slider(
            "values", 0, $(this).val()
        );
    });

    $(".max-value").on("change", function() {
        priceSlider.slider(
            "values", 1, $(this).val()
        );
    });
}
