const helper = {

    // check if all required fields of a specific form are filled (including checkboxes)
    isFormValid: function($form) {

        let $requiredFields = $form.find('[required]');

        // create an array with empty required fields
        let $emptyRequiredFields = $requiredFields.filter(function() {

            if($(this).is(':checkbox')) {
                return ! $(this).is(':checked');

            } else {
                return ! $(this).val();
            }
        });

        // if some required fields are not filled then the form is not valid
        return ! $emptyRequiredFields.length;
    },

    generateSlug: function (string) {

        // Replace anything that isn't a word character by an underscore
        return string.replace(/\W/g,'_');
    },

    formatPrice: function formatPrice (price) {
        if (! $.isNumeric(price)) {
            return '';
        }
        price = price.toFixed(2) + '';
        return price.replace('.', ',') + '€';
    },

    // used in search results
    displayLoadingSpinner: function() {

        // display overlay to indicate an operation is running
        $('#overlay').addClass('is-visible');

        // forbid body to be scrolled
        $('body').addClass('is-locked');

        $('.loading-spinner').addClass('is-visible');
    },
};
