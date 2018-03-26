/*
    To avoid conflicts, recaptcha inputs have no id attribute.
    The form is given a 'recaptcha-form' class only when it is submitted
    so its recaptcha input can be targeted by Google library to inject the response token as a value.
*/

const recaptcha = {
    init: function() {
        // get page eventual recaptcha inputs
        var $recaptchaInputs = $('[name="recaptcha_response"]');

        if ($recaptchaInputs.length) {

            // for each recaptcha input, get its related form
            $recaptchaInputs.each(function (input) {
                var $form = $(input).closest('form');

                $form.on('submit', function (e) {

                    // prevent form to be submitted
                    e.preventDefault();

                    // add a flag on the form to allow later targeting (see recaptcha.callback)
                    $form.addClass('recaptcha-form');

                    // execute Google script (available via main layout script tag)
                    grecaptcha.execute();
                });
            });
        }
    },

    callback: function (responseToken) {

        // target the form waiting to be submitted
        var form = document.querySelector('.recaptcha-form');

        // add response token to form
        var recaptchaInput = form.querySelector('[name="recaptcha_response"]');
        recaptchaInput.value = responseToken;

        // submit form
        form.submit();
    }
};
