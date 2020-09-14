$(document).ready(function () {
    init();
});

function init() {
    var data = 'record_id=' + $('#record_id').val();
    is_hr();
    get_correct_approver();
    
    // Requested by.
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_requested_by',
        data: data,
        type: 'post',
        dataType: 'json',
        success: function (response) {
            $('label[for="requested_by"]').next('div.text-input-wrap').text(response.text);
        }    
    });        
    
    // Requested Date.
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_requested_date',
        data: 'record_id=' + $('#record_id').val(),
        type: 'post',
        dataTye: 'json',
        success: function(response) {
            $('label[for="requested_date"]').next('div.text-input-wrap').text(response.text);
        }
    });

    // Concurred by.
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_concurred_by',
        data: 'record_id=' + $('#record_id').val(),
        type: 'post',
        success: function (response) {            
            $('label[for="concurred_by"]').next('div').text(response.text);
        }
    });
    
    // Aproved by.
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_approved_by',
        data: data,
        type: 'post',
        dataTye: 'json',
        success: function(response) { 
            $('label[for="approved_by"]').next('div.text-input-wrap').text(response.text);
        }        
    });
}

function get_correct_approver()
{
    $.ajax({
        url: module.get_value('base_url')+module.get_value('module_link')+'/get_correct_approver',
        data: $('#record_id').serialize(),
        type: 'post',
        dataType: 'json',
        success: function(response) {
            $('label[for="approved_by"]').siblings().replaceWith('<span id="approved_by"> &nbsp;&nbsp; '+response.data+'</span>');
        }
    }); 
}

function is_hr()
{

    $.ajax({
        url: module.get_value('base_url')+module.get_value('module_link')+'/is_hr',
        dataType: 'json',
        success: function(response) {
            if(!response) {
                $(".wizard-last").prev().addClass('wizard-last');
                $(".wizard-last:last").hide();
                $('.last').remove();
                $("form-div .wizazrd-type-form:nth-last-child(1)").addClass('last');
                $("#record-form").append('<input type="hidden" id="is_hr" value="'+response+'">')
            }else{
                
            }
            // $("#status_hr").parent().parent().hide();
        }
    });
}