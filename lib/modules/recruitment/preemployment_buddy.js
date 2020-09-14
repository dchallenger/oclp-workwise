function validate_and_save() {
    if (validate_checklist()) {
        date = new Date();        
        $('form[name="record-form"]').append($('<input type="hidden" name="completed" />').val('1'));
        ajax_save();
    } else {
        message_growl('error', 'Please make sure everything has been completed.');
    }
}

function validate_checklist() {
    var valid = true;

    return valid;
}