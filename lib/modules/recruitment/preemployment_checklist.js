$(document).ready(function () {
	$('input[type="checkbox"]').parent().next().find('label').remove();	

    setTimeout(function () {
        $('#start_date-temp').datepicker('disable');
    }, 100);

    if(view == "edit"){
        $("#fg-82").find('.form-item.odd').css('border-bottom','dotted 1px #ccc').css('padding-bottom','10px');
        $("#fg-83").find('.form-item.odd').css('border-bottom','dotted 1px #ccc').css('padding-bottom','10px');
    }

});

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