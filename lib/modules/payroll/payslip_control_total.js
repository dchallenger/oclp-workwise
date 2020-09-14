

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
    if($('#code_status_id').val() != '') {
        if($('#company_id').val() != '') {
            if($('#payroll_date').val() != '') {             
                $('#report-form input[name="print_type"]').val('');
                $('#report-form').attr('target', '_blank');
                $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
                $('#report-form').trigger('submit');
                $('#report-form').attr('target', '_self'); 
            } else {
                $('#message-container').html(message_growl('error', 'Payroll Date is Needed!'));
            }
        } else {
            $('#message-container').html(message_growl('error', 'Select Company!'));  
        }
    } else {
        $('#message-container').html(message_growl('error', 'Select Code Status!'));
    }
}

