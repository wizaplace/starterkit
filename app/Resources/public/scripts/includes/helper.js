// check if all required fields are filled
function isFormValid($form) {

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
}
