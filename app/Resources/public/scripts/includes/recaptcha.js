let $reCaptchaButtons = $('.trigger-recaptcha');

// trigger form submission behaviour on recaptcha button click
$reCaptchaButtons.on('click', function() {
    submitForm($(this))
});

function submitForm($reCaptcha) {

    let $form = $reCaptcha.closest('form');
    let $submitButton = $form.find('[type="submit"]');

    if(! isFormValid($form)) {
        $submitButton.click(); // force browser form validation

    } else {
        // add required class
        $('.trigger-recaptcha').addClass('g-recaptcha');

        // recaptcha and form submission
        grecaptcha.render($reCaptcha.attr('id'), {
            'sitekey': $(this).data('sitekey'), // supplied by Twig extension
            'callback': function(token) {
            $form.find('.g-recaptcha-response').val(token); // register token
            $form.submit();
        }
    });

        grecaptcha.execute();
    }
}
