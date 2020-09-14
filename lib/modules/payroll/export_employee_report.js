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
            $('label[for="paycode_id"]').parent().remove();
            var paycode = '<div class="form-item odd ">';
            paycode = paycode + '<label class="label-desc gray" for="paycode_id"> Payment Code: <span class="red">*</span></label>';
            paycode = paycode + '<div class="multiselect-input-wrap">';
            paycode = paycode + response.paycode_html;
            paycode = paycode + '</div>';
            $('label[for="company_id"]').parent().before(paycode);
            $('#paycode_id').multiselect().multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });
           
            $('label[for="company_id"]').parent().remove();
            var company = '<div class="form-item odd ">';
            company = company + '<label class="label-desc gray" for="company_id"> Company: <span class="red">*</span></label>';
            company = company + '<div class="multiselect-input-wrap">';
            company = company + response.company_html;
            company = company + '</div>';
            $('label[for="user_id"]').parent().before(company);
            $('#company_id').multiselect().multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });

            var employee = '<div class="form-item odd ">';
            employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
            employee = employee + '<div class="multiselect-input-wrap">';
            employee = employee + response.employee_html;
            employee = employee + '</div>';
            $('label[for="company_id"]').parent().after(employee);
            $('#employee_id').multiselect().multiselectfilter({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });           
        }
    });

    $("#paycode_id").multiselect({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1,
        close:function(event, ui)
        {
            var data = $('#report-form').serialize();
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_parameters_paycode',
                type:"POST",
                data: data,
                dataType: "json",
                async: false,
                success: function( response )
                {
                    $('label[for="company_id"]').parent().remove();
                    var company = '<div class="form-item odd ">';
                    company = company + '<label class="label-desc gray" for="company_id"> Company: <span class="red">*</span></label>';
                    company = company + '<div class="multiselect-input-wrap">';
                    company = company + response.company_html;
                    company = company + '</div>';
                    $('label[for="paycode_id"]').parent().after(company);
                    $('#company_id').multiselect().multiselect({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });

                    $('label[for="employee_id"]').parent().remove();
                    var employee = '<div class="form-item odd ">';
                    employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                    employee = employee + '<div class="multiselect-input-wrap">';
                    employee = employee + response.employee_html;
                    employee = employee + '</div>';
                    $('label[for="company_id"]').parent().after(employee);
                    $('#employee_id').multiselect().multiselectfilter({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });

                    $("#company_id").multiselect({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1,
                        close:function(event, ui)
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
                                    var employee = '<div class="form-item odd ">';
                                    employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                                    employee = employee + '<div class="multiselect-input-wrap">';
                                    employee = employee + response.employee_html;
                                    employee = employee + '</div>';
                                    $('label[for="company_id"]').parent().after(employee);
                                    $('#employee_id').multiselect().multiselectfilter({
                                        show:['blind',250],
                                        hide:['blind',250],
                                        selectedList: 1
                                    });
                                }
                            });
                        }
                    })
                }
            });
        }
    })

    $("#company_id").multiselect({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1,
        close:function(event, ui)
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
                    var employee = '<div class="form-item odd ">';
                    employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                    employee = employee + '<div class="multiselect-input-wrap">';
                    employee = employee + response.employee_html;
                    employee = employee + '</div>';
                    $('label[for="company_id"]').parent().after(employee);
                    $('#employee_id').multiselect().multiselectfilter({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });
                }
            });
        }
    })
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
    if(isset($('#paycode_id').val())) {
        if(isset($('#company_id').val())) {
            if($('#user_id').val() != '') {             
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
        $('#message-container').html(message_growl('error', 'Select Payment Code!'));
    }
}

function isset() {
    var a = arguments,
        l = a.length,        
        i = 0,
        undef; 
    if (l === 0)  {
        throw new Error('Empty isset');    
    }
 
    while (i !== l)  {
        if (a[i] === undef || a[i] === null)  {
            return false;        
        }
        i++;
    }
    return true;
}