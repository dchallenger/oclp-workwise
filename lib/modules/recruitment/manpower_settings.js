$(document).ready(function () {
/*    $.ajax({
        url: module.get_value('base_url') + 'recruitment/manpower_settings/get_settings',
        dataType: 'json',
        success: function (response) {
            notify = '';
            cc_to = '';
            email_to = '';
            
            if (response.notify.value !== undefined) {
                notify = response.notify.value;
            } 
            
            if (response.cc_to !== undefined) {
                cc_to = response.cc_to.value;
            }
            
            if (response.email_to !== undefined) {
                email_to = response.email_to.value;
            }            
            
            $('label[for="notify"]').next('div').
            removeClass('text-input-wrap').
            addClass('select-input-wrap').
            append('<select name="notify"></select>');
            
            $('select[name="notify"]').append($('<option></option>').val('').text('Please select...'));
            
            $.each(response.notify.admins, function(index, admin) {
                $('select[name="notify"]').
                append($('<option></option>').val(admin.user_id).text(admin.firstname + ' ' + admin.lastname));
            });
            
            $('select[name="notify"]').val(notify);
            $('textarea[name="cc_to"]').val(cc_to);
            $('textarea[name="email_to"]').val(email_to);
        }
    });*/
});

if (typeof(validate_mandatory) != typeof(Function)) {
    function validate_mandatory(fieldname, fieldlabel)
    {		
        error = new Array();
        if($('input[name="'+fieldname+'"]').attr('type') == "checkbox"){
            var checked = 0;
            $('input[name="'+fieldname+'"]').each(function(){
                if($(this).attr('checked')) checked++;
            });
		
            if(checked == 0){
                add_error(fieldname, fieldlabel, "This field is mandatory, select at least 1.");
                return false;
            }
        }
        else{
            if($('input[name="'+fieldname+'"]').val() == "" || 
                $('select[name="'+fieldname+'"]').val() == "" || 
                $('textarea[name="'+fieldname+'"]').val() == "" ||
                $('input[name="'+fieldname+'"]:checked').val() == ""){
                if( fieldname == "password" && $('.password-field-div').length > 0 ){
                    if( $('.password-field-div').css('display') != "none"){
                        add_error(fieldname, fieldlabel, "This field is mandatory.");
                        return false;
                    }
                }else{
                    add_error(fieldname, fieldlabel, "This field is mandatory.");
                    return false;
                }
            }
        }
        return true;
    }    
}