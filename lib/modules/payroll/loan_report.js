$( document ).ready( function() 
{
    $('#with_out_form').remove();
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_parameters',
        data: '',
        dataType: 'json',
        type: 'post',
        async: false,          
        success: function ( response ) 
        {
            var report_type = '<div class="form-item even ">';
            report_type = report_type + '<label class="label-desc gray" for="report_type"> Loan Type: </label>';
            report_type = report_type + '<div class="multiselect-input-wrap">';
            report_type = report_type + response.report_type_html
            report_type = report_type + '</div>';
            $('label[for="company_id"]').parent().before(report_type);
            $('#report_type_id').multiselect().multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });

            var loan_status = '<div class="form-item even ">';
            loan_status = loan_status + '<label class="label-desc gray" for="loan_status"> Loan Status: </label>';
            loan_status = loan_status + '<div class="multiselect-input-wrap">';
            loan_status = loan_status + response.loan_status_html
            loan_status = loan_status + '</div>';
            $('label[for="report_type"]').parent().after(loan_status);
            $('#loan_status_id').multiselect().multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });

            $('label[for="company_id"]').parent().remove();

            var company = '<div class="form-item even ">';
            company = company + '<label class="label-desc gray" for="company_id"> Company: </label>';
            company = company + '<div class="multiselect-input-wrap">';
            company = company + response.company_html;
            company = company + '</div>';
            $('label[for="loan_status"]').parent().after(company);
            $('#company_id').multiselect().multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });
        }
    });
});

function generate_report()
{
    $('#report-form input[name="print_type"]').val('1');
    $('#report-form').attr('target', '_blank');
    $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
    $('#report-form').trigger('submit');
    $('#report-form').attr('target', '_self'); 
}

function export_report()
{
    $('#report-form input[name="print_type"]').val('');
    $('#report-form').attr('target', '_blank');
    $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
    $('#report-form').trigger('submit');
    $('#report-form').attr('target', '_self'); 
}