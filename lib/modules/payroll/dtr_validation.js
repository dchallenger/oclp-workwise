function export_report()
{
    if($('#payroll_date').val() == '' )
    {
         $('#message-container').html(message_growl('error', 'Date is needed!'));
    }
    else
    {
        $('#report-form input[name="print_type"]').val('');
        $('#report-form').attr('target', '_blank');
        $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
        $('#report-form').trigger('submit');
        $('#report-form').attr('target', '_self'); 
    }

}