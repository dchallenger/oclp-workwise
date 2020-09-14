$(document).ready(function(){

	$('.print-record-custom').live('click',function(){
		var record_id = $(this).attr('record_id');
		Boxy.ask("<div class='select-input-wrap' style='margin-top:5px'><select style='margin-top:20px' name='employee_export' class='chzn-select' id='employee_export' style='width:75%'><option value='nte'>Notice to Explain</option><option value='ntu'>Notice of Ultimatum</option><option value='nte_form'>Notice to Explain Form</option></select></div>", ["Print", "Cancel"],function( choice ) {
		if(choice == "Print") {
				data = "&record_id="+record_id;

				if($('#employee_export').val() == 'nte')
					loc = "print_record_nte";
				else if($('#employee_export').val() == 'ntu')
					loc = "print_record_ntu";
				else
					loc = "print_record_nte_form";
				
				$.ajax({
					url: module.get_value('base_url') +'employee/disciplinary_action/'+loc,
			        data: data,
			        dataType: 'json',
			        type: 'post',
			        success: function (response) {
			        	file_path = response.data;
		                window.location = module.get_value('base_url')+response.data;
			        }
				});
				
		    }
		},
		{
		    title: "Printing"
		});
	});

	window.onload = function(){

		if( module.get_value('view') == 'edit' ){

/*			if(  ( $('#overide-no').attr('checked') == 'checked' ) ){

				$("#authorize").find('option').attr('disabled','disabled').removeAttr('selected');
				$("#authorize").trigger("liszt:updated");	

			}*/

			get_sanction_dates();

		}

		if( module.get_value('view') == 'detail' ){

			get_sanction_info();

		}

	}
	

	if( module.get_value('view') == 'edit' ){

		get_sanction_ddlb();
		$('#offence_id').change(function(){
			$('#sanctions-container').children().remove();
			$('#offence_no').val('');
			get_sanction_ddlb(); 
		});
		$('#offence_no').change(function(){ 
			$('#sanctions-container').children().remove();
			get_sanction_ddlb(); 
		});
		$('#suspension').change(function(){
			var value = parseFloat( $(this).val() );
			var minval =  parseFloat( $(this).attr('min') );
			var maxval =  parseFloat( $(this).attr('max') );
			if( value < minval ) $('#suspension').val( minval );
			if( value > maxval ) $('#suspension').val( maxval );
		});

		$('select[name="offence_sanction_id"]').die().live('change', function(){
			$('#suspension').attr('readonly', true);
		});

		$('#suspension, #new_suspension').focusout(function(){
			var sus_val = $(this).val();
			$('#sanctions-container').children().remove();
			for(var x=1;x<=sus_val;x++)
			{
				$('#sanctions-container').append('<span>Date Sanction : </span><div class="text-input-wrap"><input type="text" class="input-text datepicker date d date sanction" name="sanction-date[]"></div>');
				init_datepick();
			}
		});

		$('.sanction').live('change', function(){
			var emp_id = $('#employee_id').val();
			if(emp_id > 0) {
				var sdate = $(this).attr('for','im_this');
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/validate_day',
					type: 'post',
					dataType: 'json',
					data: 'sanctiondate=' + $(this).val() + '&employee_id=' + emp_id,
					success: function (response) {
						if (response.msg_type != 'success') {
	               			$('#message-container').html(message_growl(response.msg_type, response.msg));
	               			$('input[for="im_this"]').val('');
	               			$('input[for="im_this"]').removeAttr('for');
	               		} else 
	               			$('input[for="im_this"]').removeAttr('for');
					}
				});
			} else {
				$('.sanction').val('');
				$('#message-container').html(message_growl('attention', 'No Employee Selected'));
			}
		});


		// override sanction for openaccess. - jr
		$('#overide-yes').live('click', function(){
			$('#suspension').prop('readonly', true);
			$('#suspension').val('');
			$('#sanctions-container').children().remove();
			$('#new_suspension').parent().parent().show();
			$('#new_sanction').parent().parent().show();
		});

		$('#overide-no').live('click', function(){
			$('#suspension').prop('readonly', false);
			$('#new_suspension').val('');
			$('#new_sanction').val('');
			$('#new_suspension').parent().parent().hide();
			$('#new_sanction').parent().parent().hide();
		});

		$('#new_sanction').live('change', function(){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_sanction_suspension',
				type: 'post',
				dataType: 'json',
				data: 'offence_sanction_id='+$(this).val(),
				success: function ( data ) {
					if(data.has_suspension == 1){
						if( data.min == data.max ) 
							$('#new_suspension').val(data.min)
						else{
							$('#new_suspension').focus();
							$('#new_suspension').attr('min', data.min);
							$('#new_suspension').attr('max', data.max);
							$('#new_suspension').attr('readonly', false);
						}
					}
					else{
						$('#new_suspension').val('');	
						$('#new_suspension').attr('min', 0);
						$('#new_suspension').attr('max', 0);
					}
				}
			});
		});

		$('#new_suspension').change( function() {
			var value = parseFloat( $(this).val() );
			var minval =  parseFloat( $(this).attr('min') );
			var maxval =  parseFloat( $(this).attr('max') );
			if( value < minval ) $('#new_suspension').val( minval );
			if( value > maxval ) $('#new_suspension').val( maxval );
		});

		
	}

	$('#employee_id').change(function(){

		if( $('#overide-yes').attr('checked') == 'checked' ){

		$("#authorize").find('option').removeAttr('selected');

		 $.ajax({
	            url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_approvers',
	            type: 'post',
	            dataType: 'json',
	            data: 'employee_id=' + $('#employee_id').val(),
	            success: function (response) {

	            	if( response != "" ){

	            		for( var i in response ){

		            		$("#authorize").find('option').each(function(){

		            			if( $(this).val() == response[i] ){

		            				$(this).attr('selected','selected');
		            				$(this).removeAttr('disabled');
		            			}

		            		});

	            		}

	            		$("#authorize").trigger("liszt:updated");
	            	}

	            }
	        });

		 	$('#authorize').find('option').each(function(){

		 		if( $(this).attr('selected') != 'selected' ){

		 			$(this).attr('disabled','disabled')

		 		}

		 	});

			$("#authorize").trigger("liszt:updated");

		}


	});

	$('#overide-yes').click(function(){

		$("#authorize").find('option').removeAttr('selected');

		 $.ajax({
	            url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_approvers',
	            type: 'post',
	            dataType: 'json',
	            data: 'employee_id=' + $('#employee_id').val(),
	            success: function (response) {

	            	if( response != "" ){

	            		for( var i in response ){

		            		$("#authorize").find('option').each(function(){

		            			if( $(this).val() == response[i] ){

		            				$(this).attr('selected','selected');
		            				$(this).removeAttr('disabled');
		            			}

		            		});

	            		}

	            		$("#authorize").trigger("liszt:updated");
	            	}

	            }
	        });

		 	$('#authorize').find('option').each(function(){

		 		if( $(this).attr('selected') != 'selected' ){

		 			$(this).attr('disabled','disabled')

		 		}

		 	});

			$("#authorize").trigger("liszt:updated");

	});

	$('#overide-no').click(function(){

		$("#authorize").find('option').attr('disabled','disabled').removeAttr('selected');
		$("#authorize").trigger("liszt:updated");

	});

});


function clone() 
{
    if($('#date-container div.1f:first').find('input:text').val()!==""){
    	//$('#container').prepend($('#family_sample_form div.1f:first').clone(true));
        // $('#container').prepend($('#family_sample_form div.1f:first').clone(true));
        // $('#container div.1f:first').find('input:text').val('');
        // globalvar=globalvar+1;
        // $('#container div.1f:first').find('input:text').eq(5).val(globalvar);
        // $('#container div.1f:first').find('.add-more-div').show();    
        // $('#container div.1f:first').css('display','block');
        // $(".d").removeClass("hasDatepicker").attr('id','');
        // $(".d").addClass("date");
        // init_datepick();
        // $('.d').parent().find('.ui-datepicker-trigger:last').remove();
    }
}

function get_sanction_info(){

	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_sanction_info',
		type: 'post',
		dataType: 'json',
		data: 'record_id='+$('#record_id').val(),
		success: function ( data ) {

			$('label[for="offence_sanction_id"]').next().html(data.sanction);

		}
	});
}

function get_sanction_dates(){

	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_sanction_dates',
		type: 'post',
		dataType: 'html',
		data: 'record_id='+$('#record_id').val(),
		success: function ( data ) {

			$('#sanctions-container').append(data);
			init_datepick();

		}
	});



}

function get_sanction_ddlb(){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_sanction_ddlb',
		type: 'post',
		dataType: 'json',
		data: 'offence_id='+$('#offence_id').val()+'&offence_no='+$('#offence_no').val(),
		success: function ( data ) {
			$('label[for=offence_sanction_id]').next()
			.removeClass('text-input-wrap').addClass('select-input-wrap')
			.html( data.sanction_ddlb );
			$('select[name="offence_sanction_id"]').trigger('change');

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_sanction_suspension',
				type: 'post',
				dataType: 'json',
				data: 'offence_sanction_id='+$('#offence_sanction_id').val(),
				success: function ( data ) {
					if(data.has_suspension == 1){
						if( data.min == data.max ) 
							$('#suspension').val(data.min)
						else{
							
							if( $('#record_id').val() == -1 ){
								$('#suspension').focus();
							}

							$('#suspension').attr('min', data.min);
							$('#suspension').attr('max', data.max);
							$('#suspension').attr('readonly', false);
						}
					}
					else{
						$('#suspension').val('');	
						$('#suspension').attr('min', 0);
						$('#suspension').attr('max', 0);

						// for openaccess - jr
						if(module.get_value('client_no') == 2) {
							$('#new_suspension').attr('min', 0);
							$('#new_suspension').attr('max', 0);
						}
					}
				}
			});

		}
	});
}
