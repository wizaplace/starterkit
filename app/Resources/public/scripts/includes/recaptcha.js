let $reCaptchaButtons = $('.trigger-recaptcha');
let $reCaptchaForms = $('form:has(.trigger-recaptcha)');

// trigger form submission behaviour...
// ====================================

// ...on recaptcha button click
$reCaptchaButtons.on('click', function() {
    submitForm($(this));
});

// ...on standard form submission
$reCaptchaForms.on('submit', function() {

    // intercept submission if the form is not validated yet
    if(! $(this).hasClass('validated')) {
        submitForm($(this).find(".trigger-recaptcha"));
        return false;
    }
});

function submitForm($reCaptcha) {

    let $form = $reCaptcha.closest('form');
    let $submitButton = $form.find('[type="submit"]');

    if(! isFormValid($form)) {
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
