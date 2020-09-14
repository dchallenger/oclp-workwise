$( document ).ready( function() 
{
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_parameters',
        data: '',
        dataType: 'json',
        type: 'post',
        async: false,          
        success: function ( response ) 
        {   
            var report_type = '<div class="form-item odd ">';
            report_type = report_type + '<label class="label-desc gray" for="report_type"> Report: </label>';
            report_type = report_type + '<div class="multiselect-input-wrap">';
            report_type = report_type + response.report_type_html;
            report_type = report_type + '</div>';
            $('label[for="paycode_id"]').parent().before(report_type);

            var tran_type = '<div class="form-item even ">';
            tran_type = tran_type + '<label class="label-desc gray" for="tran_type"> Transaction Type: </label>';
            tran_type = tran_type + '<div class="multiselect-input-wrap">';
            tran_type = tran_type + response.tran_type_html
            tran_type = tran_type + '</div>';
            $('label[for="report_type"]').parent().after(tran_type);

        }
    });

});

function generate_report()
{
    if($('select[name="company_id"]').val() != '')
    {
        if($('#date_range_from').val() == '' || $('#date_range_to').val() == '')
        {
             $('#message-container').html(message_growl('error', 'Date is needed!'));
        }
        else
        {
            $('#report-form input[name="print_type"]').val('1');
            $('#report-form').attr('target', '_blank');
            $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
            $('#report-form').trigger('submit');
            $('#report-form').attr('target', '_self'); 
        }
    }
    else
    {
        $('#message-container').html(message_growl('error', 'Select Company!'));
    }
}

function export_report() {
    // if(isset($('#paycode_id').val()))
    if($('select[name="paycode_id"]').val() != '')
     {
        $('#report-form input[name="print_type"]').val('');
        $('#report-form').attr('target', '_blank');
        $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
        $('#report-form').trigger('submit');
        $('#report-form').attr('target', '_self'); 
    } else {
        $('#message-container').html(message_growl('error', 'Select Payment Code!'));
    }
}
