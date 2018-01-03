import helper from './helper';

const reCaptcha = {
    init: function() {
        let $reCaptchaButtons = $('.trigger-recaptcha');
        let $reCaptchaForms = $('form:has(.trigger-recaptcha)');

        let self = this;

        // trigger form submission behaviour...
        // ====================================

        // ...on recaptcha button click
        $reCaptchaButtons.on('click', function() {
            self.submitForm($(this));
        });

        // ...on standard form submission
        $reCaptchaForms.on('submit', function() {

            // intercept submission if the form is not validated yet
            if (! $(this).hasClass('validated')) {
                self.submitForm($(this).find(".trigger-recaptcha"));
                return false;
            }
        });
    },

    submitForm: function($reCaptcha) {
        let $form = $reCaptcha.closest('form');
        let $submitButton = $form.find('[type="submit"]');

        if (! helper.isFormValid($form)) {
            $submitButton.click(); // force browser form validation

        } else {

            // flag form as ready to be submitted
            $form.addClass('validated');

            // add required class to reCaptcha
            $('.trigger-recaptcha').addClass('g-recaptcha');

            // recaptcha and form submission
            grecaptcha.render($reCaptcha.attr('id'), {
                'sitekey': $reCaptcha.data('sitekey'), // supplied by Twig extension
                'callback': function(token) {
                    $form.find('.g-recaptcha-response').val(token); // register token
                    $form.submit();
                }
            });

            grecaptcha.execute();
        }
    }
};

export default reCaptcha;
