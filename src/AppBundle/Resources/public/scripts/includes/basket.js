const basket = {

    init: function() {

        let self = this;

        // remove a product from basket
        // TODO: not functional
        $('.update-quantity').on('click', function() {
            let $product = $(this).closest('.product');
            let productId = $product.data('id');
            let quantity = $product.data('quantity');

            self.updateQuantity(productId, quantity);
        });
    },

    // update quantity
    // TODO: add a mechanism to make the page reload optional (use case: product removal from basket popin)
    // TODO: add CSRF token
    updateQuantity: function(productId, quantity) {
        $.post(
            "/basket/update", {
                product_id: productId,
                quantity: quantity
            }

        ).done(function () {
            window.location.reload();

        }).fail(function() {
            notification.createAlert("Une erreur est survenue.", "danger");
        });
    },
};

export default basket;
