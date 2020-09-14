$(document).ready(function () {
	$('label[for="employee_appraisal_template_company_id"]').next().html($('#company_name').val());
	var department_id_selected = $('#department_id').val();
	var position_id_selected = $('#position_id').val();

    if (module.get_value('view') !== "index") {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_department',
            data: 'company_id=' + $('#company_id').val() + '&department_id_selected='+department_id_selected ,
            dataType: 'html',
            type: 'post',
            async: false,              
            success: function ( response ) {
                $('#department_id').html(response);
    			$("#department_id").trigger("liszt:updated");

                 $.ajax({
                 url: module.get_value('base_url') + module.get_value('module_link') + '/get_position',
                    data: 'company_id=' + $('#company_id').val() + '&position_id_selected='+ position_id_selected,
                    dataType: 'html',
                    type: 'post',
                    async: false,                             
                    success: function ( response ) {
                        $('#position_id').html(response);
                        $("#position_id").trigger("liszt:updated");
                    }
                });

            },
                 
        });	
    };

});