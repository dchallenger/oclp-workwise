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
            $('label[for="payroll_date"]').parent().before(report_type);

        }
    });
    
    $('select[name="company_id"]').change(function()
    {
        if($(this).val() != '')
        {
            var data = $('#report-form').serialize();
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/employee_multiple',
                type:"POST",
                data: data,
                dataType: "json",
                async: false,
                success: function( response )
                {
                    $('label[for="employee_id"]').parent().remove();

                    var employee = '<div class="form-item even ">';
                    employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                    employee = employee + '<div class="multiselect-input-wrap">';
                    employee = employee + response.employee_html;
                    employee = employee + '</div>';
                    $('label[for="company_id"]').parent().after(employee);
                    $('#employee_id').multiselect().multiselect({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });
                }
            });
        }
        else
        {
            $('label[for="employee_id"]').parent().remove();

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