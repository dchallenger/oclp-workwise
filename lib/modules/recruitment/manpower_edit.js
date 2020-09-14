$(document).ready(function () {
	$('#referred_by_id').width('400px')

	is_hr();
	get_correct_approver();
	if (user.get_value('post_control') != 1) {
   		if (module.get_value('record_id') == -1 && module.get_value('view') == 'edit') {
            get_employee_details(user.get_value('user_id'));
        };
	}

	$('input[name="reason_for_request"]').live('click', function() {
		get_correct_approver();
	});

	window.onload = function(){
		$('#desire_date-temp').datepicker("option", "minDate", new Date());
		$('#desire_date-temp').datepicker("option", "maxDate", null);
	}

   
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_manpower_settings',
		dataType: 'json',
		success: function (settings) {
			$('input[name="date_needed-temp"]').datepicker('option', 'minDate', new Date(settings.lead_to_date));

			if ($('#record_id').val() < 0) {
				if (settings.concurred_receive_email == 1) {
					$('#record-form')
						.append($('<input type="hidden" />').attr('name', 'concurred_receive_notification').val(1));
				}

				if (settings.concurred_as_approver == 1) {
					$('#record-form')
					.append($('<input type="hidden" />').attr('name', 'concurred_as_approver').val(1));
				}
			}
		}
	});

    populate_company_relations($('#company_id').val());

	$('select[name="company_id"]').change(function () {
		populate_company_relations($(this).val());
	});  

	// Requested Date.
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_requested_date',
		data: 'record_id=' + $('#record_id').val(),
		type: 'post',
		dataTye: 'json',
		success: function(response) {
			$('label[for="requested_date"]').next('div.text-input-wrap').remove();
			$('label[for="requested_date"]').append('<input type="hidden" name="requested_date" value="' + response.input + '" />' + response.text)
		}
	});    

	/** 
	 * Autocomplete
	 * 
	 **/
	$.widget( "custom.catcomplete", $.ui.autocomplete, {
		_renderMenu: function( ul, items ) {
			var self = this,
				currentCategory = "";
			$.each( items, function( index, item ) {
				if ( item.category != currentCategory ) {
					ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
					currentCategory = item.category;
				}
				self._renderItem( ul, item );
			});
		}
	});

});
// End onload.
function get_employee_details(employee_id) {
	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_details',
        data: 'employee_id=' + employee_id,
        type: 'post',
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            });
       },
        success: function(data){
           	// $('#department_id, #division_id, #position_id, #rank_id').attr('disabled', true);

            $('#department_id').val(data.department);
            $('#division_id').val(data.division);
            $('#company_id').val(data.company);
            $('#section_id').val(data.section);

            $('#company_id').trigger("liszt:updated");
            $('#division_id').trigger("liszt:updated");
            $('#department_id').trigger("liszt:updated");
            $('#section_id').trigger("liszt:updated");

        	// $('label[for=division_id]').parent().find('.select-input-wrap').hide();
        	// $('label[for=division_id]').parent().append('<div class="text-input-wrap"><input type="text" class=" input-text " readonly value="'+$('#division_id :selected').text()+'" ></div>');

        	// $('label[for=company_id]').parent().find('.select-input-wrap').hide();
        	// $('label[for=company_id]').parent().append('<div class="text-input-wrap"><input type="text" class=" input-text " readonly value="'+$('#company_id :selected').text()+'" ></div>');
        	
        	// $('label[for=department_id]').parent().find('.select-input-wrap').hide();
        	// $('label[for=department_id]').parent().append('<div class="text-input-wrap"><input type="text" class=" input-text " readonly value="'+$('#department_id :selected').text()+'" ></div>');
             
            $.unblockUI();
        }           
    });
}

function populate_company_relations(company_id) {
	// Departments dropdown populate.
	// departments = module.get_value('base_url') + module.get_value('module_link') + '/get_company_departments';
	data = 'company_id=' + company_id + '&record_id=' + $('#record_id').val();
        
	// $('select[name="department_id"]').find('option').remove();   
	// $('select[name="department_id"]').
	// append($("<option></option>").attr("value",'').text("Select..."));             
        
	// $.ajax({
	// 	url: departments,
	// 	type: 'post',
	// 	data: data,
	// 	success: function (response) {                    
	// 		// Append the new values to department dropdown.
	// 		$.each(response.departments, function(index, value)
	// 		{   
	// 			$('select[name="department_id"]').
	// 			append($("<option></option>").attr("value",value.department_id).text(value.department)); 
	// 		});     
            
	// 		if (response.value != undefined) {
	// 			$('select[name="department_id"]').val(response.value);
	// 		}
	// 	}                
	// });
        
	// Position dropdown populate.
	positions = module.get_value('base_url') + module.get_value('module_link') + '/get_company_positions';  
	      
	$.ajax({
		url: positions,
		type: 'post',
		data: data,
		success: function (response) {                    
			// Append the new values to department dropdown.
			$.each(response.positions, function(index, value)
			{   
				$('select[name="position_id"]').
				append($("<option></option>").attr("value",value.position_id).text(value.position)); 
			});
            
            if (response.value >= 0 ){
				$('select[name="position_id"]').val(response.value).trigger("liszt:updated");;	
			}
		}
	});       
        
	// Requested by.
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_requested_by',
		data: data,
		type: 'post',
		dataType: 'json',
		success: function (response) {

			if ($('select[name="requested_by"]').size() > 0) {                                        
				$('select[name="requested_by"]').remove();
			}
            
			if (response.code == 0) {            
				$('label[for="requested_by"]').next('div.text-input-wrap').
				removeClass('text-input-wrap').addClass('select-input-wrap');                       
 
				$('label[for="requested_by"]').next('div.select-input-wrap').
				append('<select name="requested_by"></select>'); 
 
				$('select[name="requested_by"]').
				append($('<option></option>').
					val('').
					text('Please select...')
					);
 
				$.each(response.users, function (index, user) {
					$('select[name="requested_by"]').
					append($('<option></option>').
						val(user.user_id).
						text(user.firstname + ' ' + user.lastname)
						);
				});
                
				if(response.value > 0) {
					$('select[name="requested_by"]').val(response.value);
				}
			} else {
				$('label[for="requested_by"]').next('div').empty();
				$('label[for="requested_by"]').next('div').
				append('<input type="hidden" value="' + response.user_id + '" name="requested_by" />' + response.text);
			}             
		}    
	});       

	// Concurred by.
	setTimeout(
		function () {
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_concurred_by',
				data: 'company_id=' + company_id + '&record_id=' + $('#record_id').val() + '&approver=' + $('input[name="concurred_as_approver"]').val(),
				type: 'post',
				success: function (response) {			
					if ($('select[name="concurred_by"]').size() > 0) {                                        
						$('select[name="concurred_by"]').remove();
					}
		            
					if (response.code == 0) {        
						if ($('label[for="concurred_by"]').size() <= 0) {
							$('#record-form').
							append('<input type="hidden" value="' + response.user_id + '" name="concurred_by" />');

							return;
						}

						$('label[for="concurred_by"]').next('div.text-input-wrap').
						removeClass('text-input-wrap').addClass('select-input-wrap');                       
		 
						$('label[for="concurred_by"]').next('div.select-input-wrap').
						append('<select name="concurred_by"></select>'); 
		 
						$('select[name="concurred_by"]').
						append($('<option></option>').
							val('').
							text('Please select...')
							);
		 
						$.each(response.admins, function (index, user) {
							$('select[name="concurred_by"]').
							append($('<option></option>').
								val(user.user_id).
								text(user.firstname + ' ' + user.lastname)
								);
						});
		                
						if(response.value > 0) {
							$('select[name="concurred_by"]').val(response.value);
						}
					} else {
						$('#record-form').
						append('<input type="hidden" value="' + response.user_id + '" name="concurred_by" />');
					} 						
				}
			});
		},1000
	);
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
				$('label[for="hra_remarks"]').parent().hide();
            	$('label[for="approver_remarks"]').parent().hide();
            }else{
            	$('#approver_remarks').hide().attr('readonly', true);
            	$('label[for="approver_remarks"]').next().html($('#approver_remarks').val());

            }
            // $("#status_hr").parent().parent().hide();
        }
    });
}

function get_correct_approver()
{
	$.ajax({
        url: module.get_value('base_url')+module.get_value('module_link')+'/get_correct_approver',
        data: $('input[name=reason_for_request]:checked, #record_id, input[name="reason_for_request"], input[name="position_for"], #status_id').serialize(),
        type: 'post',
        dataType: 'json',
        success: function(response) {
            $('label[for="approved_by"]').siblings().replaceWith('<span id="approved_by">'+response.data+'</span>');
        }
    });	
}

