$(document).ready(function () {
	$('#multiselect-city_id, #multiselect-location_id').bind('multiselectclose', function () {
		$('#city_id').val($("#multiselect-city_id").multiselect("getChecked").map(function(){
			   		return this.value;	
				}).get());

		$('#location_id').val($("#multiselect-location_id").multiselect("getChecked").map(function(){
				   return this.value;	
				}).get());

		var data = $('#city_id, #location_id, #record_id').serialize();

		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/filter_employees',
			data: data,
			type: 'post',
			dataType: 'html',
			success: function(response) {				
				$('#multiselect-employee_id option, #multiselect-employee_id optgroup').remove();				
				$('#multiselect-employee_id').append(response);				
				$('#multiselect-employee_id').multiselect('refresh');
			}
		});
	});

	if (module.get_value('view') == 'edit') {
		$('#multiselect-city_id, #multiselect-location_id').trigger('multiselectclose');
		generate_affected_dates();
	}

	$('.process-credits').live('click', function () {
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/update_leave_credits',
			data: 'record_id=' + $(this).parents('tr').attr('id'),
			dataType: 'json',
			type: 'post',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Processing, please wait...</div>'
				});
			},
			success: function(response){
				$.unblockUI();
				if(response.msg != "") $('#message-container').html(message_growl(response.msg_type, response.msg));

				$('#jqgridcontainer').jqGrid().trigger("reloadGrid");
			}
		});
	});


	var initial_load = true;

function generate_affected_dates() {
    var ok_to_proceed = true;
    if( initial_load ){
        if (module.get_value('record_id ') == '-1') {
            ok_to_proceed = false;
        }
        initial_load = false; 
    }

    if ($('input[name="date_from"]').val() != '' && $('input[name="date_to"]').val() != '' && ok_to_proceed) {

        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_affected_dates',
            type: 'post',
            data: $('input[name="date_from"], input[name="record_id"], input[name="date_to"], #application_form_id').serialize(),
            success: function (response) {
                selectValues = { "00": "00", "01": "01" };
                if (response.type == 'success') {
                    if ($('#dates-container').size() > 0) {
                        $('#dates-container').remove();
                    }
                    
                    $('label[for="label-dates-affected"]').after('<div id="dates-container"></div>');
                    var ctr = 1;
                    $.each(response.dates, function (index, data) {

                        date = '<div style="padding:2px 0" class="leave_inclusive_date_'+data.date2+'"><span style="padding-right:5px">' + data.date + '</span>';
                        
                        wd = (data.duration_id == 0) ? 'selected' : '';
                        fs = (data.duration_id == 1) ? 'selected' : '';
                        ss = (data.duration_id == 2) ? 'selected' : '';
                        
                        date += '<input type="hidden" name="employee_leave_date_id[]" value="' + data.employee_leave_date_id + '"/>';
                        date += '<input type="hidden" name="dates[]" value="' + data.date + '"/>';
                        date += '<span> - ';
                        date += response.duration;
                        date += '</span></div>';
                        
                        $('#dates-container').append(date);

                        $('.leave_inclusive_date_'+data.date2).find('select').val(data.duration_id);

                        ctr++;                                                
                    });
                    $(".duration option[value='4']").remove();
                }
            }
        });
    }

    return false;
}

    $('input[name="date-temp-to"]').change(function(){	

        generate_affected_dates();

    });

    $('input[name="date-temp-from"]').change(function(){

        generate_affected_dates();   
    });


    if ($('#div-detail-dates-affected').size() > 0) {
        $('label[for="label-dates-affected"]').next('div').append($('#div-detail-dates-affected').html());
    }
    
});

