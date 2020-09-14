$( document ).ready( function() {

	if( module.get_value('view') == 'edit' ){
		$.blockUI({
		    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
		}); 

		setTimeout($.unblockUI, 100);

		window.onload = function(){



			if( module.get_value('record_id') == "-1" ){

				$('label[for="department_id"]').parent().hide();

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_alternate_contact',
					data: 'record_id=' + $('#record_id').val() + '&view=' + module.get_value('view') + '&alternate_contact=' + $('#alternate_contact').val() + '&primary_contact=' + $('#primary_contact').val(),
					dataType: 'json',
					type: 'post',
					async: false,
					beforeSend: function(){

					},								
					success: function ( response ) {
						$('.alternate_contacts').html(response.html);
						
					}
				});

			}
			else{

				
				if( $('label[for="primary_contact"]').val() != " " ){
					get_primary_info(module.get_value('view'));
				}
				else{
					$('label[for="primary_contact_employee_name"]').val("");
					$('label[for="primary_contact_primary_no"]').val("");
					$('label[for="primary_contact_alternate_no"]').val("");
					$('label[for="primary_contact_address1"]').val("");
					$('label[for="primary_contact_address2"]').val("");
					$('label[for="primary_contact_city"]').val("");
					$('label[for="primary_contact_province"]').val("");
				}

				var company_id = $('#company_id').val();
				var department_id = $('#department_id').val();
									

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_alternate_contact',
					data: 'record_id=' + $('#record_id').val() + '&view=' + module.get_value('view') + '&alternate_contact=' + $('#alternate_contact').val() + '&primary_contact=' + $('#primary_contact').val(),
					dataType: 'json',
					type: 'post',
					async: false,
					beforeSend: function(){

					},								
					success: function ( response ) {
						$('.alternate_contacts').html(response.html);
						
					}
				});

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/update_primary_alternate_option',
					data: '&alternate_contact=' + $('#alternate_contact').val() + '&primary_contact=' + $('#primary_contact').val() + '&company_id=' + company_id + '&department_id=' + department_id + $('#record_id').val(),
					dataType: 'json',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {
						$('#primary_contact').empty().html(response.primary_html);
						$('#alternate_contact').empty().html(response.alternate_html);

						$('#primary_contact').trigger('liszt:updated');
						$('#alternate_contact').trigger('liszt:updated');
					}
				});

				var company_id = $('#company_id').val();
				var department_id = $('#department_id').val();

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/update_department_field',
					data: 'company_id=' + company_id + '&department_id=' + department_id,
					dataType: 'html',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {

						$('#department_id').empty().html( response );
						$('#department_id').trigger("liszt:updated");

						if( company_id == " " ){
							$('label[for="department_id"]').parent().hide();
						}
						else{
							$('label[for="department_id"]').parent().show();
						}
						
					}
				});
// end
			}

		}

		$('#company_id').live('change',function(){


			var company_id = $(this).val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/update_department_field',
				data: 'company_id=' + company_id,
				dataType: 'html',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {

					$('#department_id').empty().html( response );
					$('#department_id').trigger("liszt:updated");

					if( company_id == " " ){
						$('label[for="department_id"]').parent().hide();
					}
					else{
						$('label[for="department_id"]').parent().show();
					}
					
				}
			});	

		});

		$('#primary_contact').live('change',function(){

			if( $('#primary_contact').val() != " " ){

				get_primary_info(module.get_value('view'));

			}

			var company_id = $('#company_id').val();
			var department_id = $('#department_id').val();		

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/update_primary_alternate_option',
				data: '&alternate_contact=' + $('#alternate_contact').val() + '&primary_contact=' + $('#primary_contact').val() + '&company_id=' + company_id + '&department_id=' + department_id + $('#record_id').val(),
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('#primary_contact').empty().html(response.primary_html);
					$('#alternate_contact').empty().html(response.alternate_html);

					$('#primary_contact').trigger('liszt:updated');
					$('#alternate_contact').trigger('liszt:updated');
				}
			});



		});

		$('#alternate_contact').live('change',function(){

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_alternate_contact',
				data: 'record_id=' + $('#record_id').val() + '&view=' + module.get_value('view') + '&alternate_contact=' + $('#alternate_contact').val() + '&primary_contact=' + $('#primary_contact').val(),
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){

				},								
				success: function ( response ) {
					$('.alternate_contacts').html(response.html);
					
				}
			});

			var company_id = $('#company_id').val();
			var department_id = $('#department_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/update_primary_alternate_option',
				data: '&alternate_contact=' + $('#alternate_contact').val() + '&primary_contact=' + $('#primary_contact').val() + '&company_id=' + company_id + '&department_id=' + department_id + $('#record_id').val(),
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('#primary_contact').empty().html(response.primary_html);
					$('#alternate_contact').empty().html(response.alternate_html);

					$('#primary_contact').trigger('liszt:updated');
					$('#alternate_contact').trigger('liszt:updated');
				}
			});


		});
		
		$('#department_id').live('change',function(){
			var company_id = $('#company_id').val();
			var department_id = $(this).val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee',
				data: '&company_id=' + company_id + '&department_id=' + department_id,
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('#primary_contact').empty().html(response.primary_html);
					$('#primary_contact').trigger('liszt:updated');
				}
			});			
		})
	}

	if( module.get_value('view') == 'detail' ){

		$.blockUI({
		    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
		}); 

		setTimeout($.unblockUI, 100);

		window.onload = function(){


				if( $('label[for="primary_contact').val() != " " ){
					get_primary_info(module.get_value('view'));
				}
				else{
					$('label[for="primary_contact_employee_name"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_primary_no"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_alternate_no"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_address1"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_address2"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_city"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_province"]').parent().find('.text-input-wrap').html("");
				}

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_alternate_contact',
					data: 'record_id=' + $('#record_id').val() + '&view=' + module.get_value('view') + '&alternate_contact=' + $('#alternate_contact').val() + '&primary_contact=' + $('#primary_contact').val(),
					dataType: 'json',
					type: 'post',
					async: false,
					beforeSend: function(){

					},								
					success: function ( response ) {
						$('.alternate_contacts').html(response.html);
						$('label[for="alternate_contact"]').parent().find('.text-input-wrap').html(response.alternate);
					}
				});

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_other_contact',
					data: 'record_id=' + $('#record_id').val(),
					dataType: 'html',
					type: 'post',
					async: false,
					beforeSend: function(){

					},								
					success: function ( response ) {
						$('.other_contacts').html(response);
						
					}
				});	




		}

	}

});

function get_primary_info(view_type){


	if( view_type == 'edit' ){

		var primary_contact = $('#primary_contact').val();

		if( primary_contact == " " ){

			$('#primary_contact_employee_name').val("");
			$('#primary_contact_primary_no').val("");
			$('#primary_contact_alternate_no').val("");
			$('#primary_contact_address1').val("");
			$('#primary_contact_address2').val("");
			$('#primary_contact_city').val("");
			$('#primary_contact_province').val("");

		}
		else{

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_data',
				data: 'employee_id=' + primary_contact + '&info_type=primary&view_type='+view_type,
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){

				},								
				success: function ( response ) {
					if( response.msg_type != 'error'){

						$('#primary_contact_employee_name').val(response.employee_name);
						$('#primary_contact_primary_no').val(response.primary_no);
						$('#primary_contact_alternate_no').val(response.alternate_no);
						$('#primary_contact_address1').val(response.address1);
						$('#primary_contact_address2').val(response.address2);
						$('#primary_contact_city').val(response.city);
						$('#primary_contact_province').val(response.province);

					}
					else{

						$('#primary_contact_employee_name').val("");
						$('#primary_contact_primary_no').val("");
						$('#primary_contact_alternate_no').val("");
						$('#primary_contact_address1').val("");
						$('#primary_contact_address2').val("");
						$('#primary_contact_city').val("");
						$('#primary_contact_province').val("");

					}

				}
			});	

		}

	}
	else{

		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_contact_ids',
			data: 'id='+module.get_value('record_id'),
			dataType: 'json',
			type: 'post',
			async: false,
			beforeSend: function(){

			},								
			success: function ( response ) {
				var primary_contact = response.primary_contact;
				if( primary_contact == "" ){

					$('label[for="primary_contact_employee_name"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_primary_no"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_alternate_no"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_address1"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_address2"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_city"]').parent().find('.text-input-wrap').html("");
					$('label[for="primary_contact_province"]').parent().find('.text-input-wrap').html("");

				}
				else{

					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_data',
						data: 'employee_id=' + primary_contact + '&info_type=primary&view_type='+view_type,
						dataType: 'json',
						type: 'post',
						async: false,
						beforeSend: function(){

						},								
						success: function ( response ) {
							if( response.msg_type != 'error'){

								$('label[for="primary_contact_employee_name"]').parent().find('.text-input-wrap').html(response.employee_name);
								$('label[for="primary_contact_primary_no"]').parent().find('.text-input-wrap').html(response.primary_no);
								$('label[for="primary_contact_alternate_no"]').parent().find('.text-input-wrap').html(response.alternate_no);
								$('label[for="primary_contact_address1"]').parent().find('.text-input-wrap').html(response.address1);
								$('label[for="primary_contact_address2"]').parent().find('.text-input-wrap').html(response.address2);
								$('label[for="primary_contact_city"]').parent().find('.text-input-wrap').html(response.city);
								$('label[for="primary_contact_province"]').parent().find('.text-input-wrap').html(response.province);

							}
							else{

								$('label[for="primary_contact_employee_name"]').parent().find('.text-input-wrap').html("");
								$('label[for="primary_contact_primary_no"]').parent().find('.text-input-wrap').html("");
								$('label[for="primary_contact_alternate_no"]').parent().find('.text-input-wrap').html("");
								$('label[for="primary_contact_address1"]').parent().find('.text-input-wrap').html("");
								$('label[for="primary_contact_address2"]').parent().find('.text-input-wrap').html("");
								$('label[for="primary_contact_city"]').parent().find('.text-input-wrap').html("");
								$('label[for="primary_contact_province"]').parent().find('.text-input-wrap').html("");

							}

						}
					});	

				}
			}
		});

	}

}

function get_alternate_info(view_type){

	if( view_type == 'edit' ){

		var alternate_contact = $('#alternate_contact').val();

		if( alternate_contact == " " ){

			$('#alternate_contact_employee_name').val("");
			$('#alternate_contact_primary_no').val("");
			$('#alternate_contact_alternate_no').val("");
			$('#alternate_contact_address1').val("");
			$('#alternate_contact_address2').val("");
			$('#alternate_contact_city').val("");
			$('#alternate_contact_province').val("");

		}
		else{

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_data',
				data: 'employee_id=' + alternate_contact + '&info_type=alternate&view_type='+view_type,
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){

				},								
				success: function ( response ) {
					if( response.msg_type != 'error'){

						$('#alternate_contact_employee_name').val(response.employee_name);
						$('#alternate_contact_primary_no').val(response.primary_no);
						$('#alternate_contact_alternate_no').val(response.alternate_no);
						$('#alternate_contact_address1').val(response.address1);
						$('#alternate_contact_address2').val(response.address2);
						$('#alternate_contact_city').val(response.city);
						$('#alternate_contact_province').val(response.province);

					}
					else{

						$('#alternate_contact_employee_name').val("");
						$('#alternate_contact_primary_no').val("");
						$('#alternate_contact_alternate_no').val("");
						$('#alternate_contact_address1').val("");
						$('#alternate_contact_address2').val("");
						$('#alternate_contact_city').val("");
						$('#alternate_contact_province').val("");

					}

				}
			});	

		}
	}
	else{

		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_contact_ids',
			data: 'id='+module.get_value('record_id'),
			dataType: 'json',
			type: 'post',
			async: false,
			beforeSend: function(){

			},								
			success: function ( response ) {
				var alternate_contact = response.alternate_contact;

				if( alternate_contact == "" ){

					$('label[for="alternate_contact_employee_name"]').parent().find('.text-input-wrap').html("");
					$('label[for="alternate_contact_primary_no"]').parent().find('.text-input-wrap').html("");
					$('label[for="alternate_contact_alternate_no"]').parent().find('.text-input-wrap').html("");
					$('label[for="alternate_contact_address1"]').parent().find('.text-input-wrap').html("");
					$('label[for="alternate_contact_address2"]').parent().find('.text-input-wrap').html("");
					$('label[for="alternate_contact_city"]').parent().find('.text-input-wrap').html("");
					$('label[for="alternate_contact_province"]').parent().find('.text-input-wrap').html("");

				}
				else{

					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_data',
						data: 'employee_id=' + alternate_contact + '&info_type=alternate&view_type='+view_type,
						dataType: 'json',
						type: 'post',
						async: false,
						beforeSend: function(){
 
						},								
						success: function ( response ) {
							if( response.msg_type != 'error'){

								$('label[for="alternate_contact_employee_name"]').parent().find('.text-input-wrap').html(response.employee_name);
								$('label[for="alternate_contact_primary_no"]').parent().find('.text-input-wrap').html(response.primary_no);
								$('label[for="alternate_contact_alternate_no"]').parent().find('.text-input-wrap').html(response.alternate_no);
								$('label[for="alternate_contact_address1"]').parent().find('.text-input-wrap').html(response.address1);
								$('label[for="alternate_contact_address2"]').parent().find('.text-input-wrap').html(response.address2);
								$('label[for="alternate_contact_city"]').parent().find('.text-input-wrap').html(response.city);
								$('label[for="alternate_contact_province"]').parent().find('.text-input-wrap').html(response.province);

							}
							else{

								$('label[for="alternate_contact_employee_name"]').parent().find('.text-input-wrap').html("");
								$('label[for="alternate_contact_primary_no"]').parent().find('.text-input-wrap').html("");
								$('label[for="alternate_contact_alternate_no"]').parent().find('.text-input-wrap').html("");
								$('label[for="alternate_contact_address1"]').parent().find('.text-input-wrap').html("");
								$('label[for="alternate_contact_address2"]').parent().find('.text-input-wrap').html("");
								$('label[for="alternate_contact_city"]').parent().find('.text-input-wrap').html("");
								$('label[for="alternate_contact_province"]').parent().find('.text-input-wrap').html("");


							}

						}
					});	

				}
			}
		});

	}

}

