$(document).ready(function () {
    $('input[name="education_id"]').change(function () {
        var val = $(this).val();
        if (val > 0) {
            $.ajax({
                url: module.get_value('base_url') + 'recruitment/applicant_education/get_details',
                data: 'record_id=' + val,
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    $.each(response, function (index, value) {
                        $('input[name="' + index + '"]').val(value);
                    });

                    date_from = new Date(response.date_from);
                    date_to = new Date(response.date_to);
                    date_graduated = new Date(response.date_graduated);
                    
                    $('input[name="date-temp-from"] , input[name="date_from"]').val(date_from.getMonth() + '/' + date_from.getDate() + '/' + date_from.getFullYear());
                    $('input[name="date-temp-to"] , input[name="date_to"]').val(date_to.getMonth() + '/' + date_to.getDate() + '/' + date_to.getFullYear());
                    $('input[name="date_graduated-temp"] , input[name="date_graduated"]').val(date_graduated.getMonth() + '/' + date_graduated.getDate() + '/' + date_graduated.getFullYear());
                    
                    if (response.date_graduated != '0000-00-00') {
                        $('#graduated-yes').attr('checked','checked');
                    } else {
                        $('#graduated-no').attr('checked','checked');
                    }
                }
            });            
        }
    })
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