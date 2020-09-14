$(document).ready(function() {
if( module.get_value('view') != "index" ){toggleOn();}

if( module.get_value('view') == "edit" )
{
	
	var annual_manpower_planning_id = $('#record_id').val();
	var with_incumbent = $("#with_incumbent").val();
	var existing = $("#existing").val();


	$('#add_existing_position').chosen()
	$('#add_existing_position_chzn').css('width','300px');
	$('#add_existing_position_chzn').css('text-align','left');
	$('#add_existing_position_chzn').children().css('width', '290.767px');
    $('#add_existing_position_chzn .chzn-search input[type="text"]').css('width', '250.767px');
    $('.add_new_job_container').hide();


	$("#department_id").live('change', function () {
		var department_id = $(this).val();
		var type = 'department';
		get_head(department_id, type);
		
		var year = $("#year").val();
		if (year != "") {
			validation(department_id, year);
			get_existing_headcount(department_id, annual_manpower_planning_id, year);
		};
		get_position_per_department(department_id);
	});

	$("#division_id").live('change', function () {
		var division_id = $(this).val();
		var type = 'division';

		get_head(division_id, type);
		// $.ajax({
		// 	url: module.get_value('base_url') + module.get_value('module_link') + '/get_department_list',
		// 	data: 'division_id=' + division_id,
		// 	dataType: 'html',
		// 	type: 'post',
		// 	async: false,
		// 	beforeSend: function(){
			
		// 	},								
		// 	success: function ( response ) {

		// 		// $('#department_id').parent().parent().show();
		// 		$('#department_id').parent().empty();
		// 		$('label[for="department_id"]').parent().find('.select-input-wrap').html(response);
		// 		$('#department_id').chosen();

		// 	}
		// });	

	});
		
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_year',
			data: 'annual_manpower_planning_id=' + annual_manpower_planning_id,
			dataType: 'json',
			type: 'post',
			async: false,
			beforeSend: function(){
			// $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
			},								
			success: function ( response ) {
				$.unblockUI();
				$('label[for="year"]').next().removeClass('text-input-wrap').addClass('select-input-wrap');
				$('label[for="year"]').next().html(response.year);

		
				$('label[for="created_by"]').next().removeClass('select-input-wrap').addClass('text-input-wrap');
				$('label[for="created_by"]').next().html(response.created_by +  response.employee );
			
				
			}
		});	

	$('#year').live('change', function(){	
		var department_id = $("#department_id").val();
		validation(department_id, $(this).val());
		get_existing_headcount(department_id, annual_manpower_planning_id, year);
	}).css('width','77%');

		if (annual_manpower_planning_id != '-1') {
			var department_id = $("#department_id").val();
			var year = $("#year").val();
			$('#annual_user_division_id').attr('disabled', true);
			$('#annual_user_department_id').attr('disabled', true);
			$('#department_id').attr('disabled', true);
			$('#year').attr('disabled', true).css('width','77%');

			if (with_incumbent != 1) {
				get_position_per_department(department_id);
			};
			
			if (existing != 1) {
				get_existing_headcount(department_id, annual_manpower_planning_id, year);
			};

		}
			// get_previous_headcount(department_id, year);
		$('.rank_count').live('keydown',numeric_only ); 
		$('.budget').live('keydown',maskFloat ); 
		$('.rank_details').live('click', function () {
			var element = $(this); //$('#detail').html() //$(this).next('div').html();
			var width   = $(window).width()*.7;
			var position_id = $(this).attr('position');
			var title =  $(this).attr('atitle');
			var amp_pos_id = $(this).attr('amp-position-id');


			$.ajax({
	                url: module.get_value('base_url') + module.get_value('module_link') + '/get_rank_details',
	                data:  'position_id=' + position_id + '&record_id=' + annual_manpower_planning_id + '&amp_pos_id=' + amp_pos_id,
	                type: 'post',
	                dataType: 'json',
	                beforeSend: function(){
						$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
					},	
	                success: function(response) { 
	                	$.unblockUI();
	                		
		                   var template_form = new Boxy.confirm('<div id="boxyhtml" style="width:100%">' + response.html +'</div>', 
										function () {
											
											var details = $("#boxyhtml").find('input');

											var approved_hc = element.parents('tr').find('.existing_headcount_month_total').val();
											var incumbent = element.parents('tr').find('.existing_job_headcount_previous').val();
											

											var total = 0;
											var count = 0;
											var rank_count = $("#boxyhtml").find('.rank_count');

											rank_count.each(function (index, element) {
												var option = $(element);
												var val = option.val();
												if (val != ''){
													count += parseInt(val);
												}
													
											})
											if (approved_hc != undefined) {
												total = parseInt(approved_hc) - parseInt(incumbent);
											}else{
												total = element.parents('tr').find('.new_headcount_month_total').val();
											};

											// console.log(incumbent);
											if (parseInt(count) == parseInt(total)) {

												url = module.get_value('base_url') + module.get_value('module_link') + '/save_rank_details';

									            $.ajax({
									                url: url,
									                data:  details.serialize() + '&amp_id=' + annual_manpower_planning_id + '&amp_position_id='+amp_pos_id + '&position_id=' + position_id,
									                type: 'post',
									                dataType: 'json',
									                beforeSend: function(){
														$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
													},
									                success: function(response) { 
									                	$.unblockUI();
									                    message_growl(response.msg_type, response.msg);
									                    
									                    if (typeof(callback) == typeof(Function))
									                        callback(response);
									                }
									            });

								        	}else{
								        		 message_growl('error', 'Total Quantity must be equal to Jan-Dec total');
								        	}
								        },
										{		
											title: title,					
											draggable: false,
						                    modal: true,
						                    center: true,
						                    unloadOnHide: true,
						                    beforeUnload: function (){
						                        template_form = false;
						                    }				
										});
	                }
	            });				
			
		});
			
		$('.add_new_headcount_job').live('click',function(){				
			$('.new_headcount_position_empty').hide();

			var department_id = $('#department_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_form_new_headcount_position',
				data: 'department_id=' + department_id,
				dataType: 'html',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('#module-new-headcount').append(response);
				}
			});	
			return false;
		});

		$('.add_new_job').live('click',function(){				

			$('#module-headcount').show();
			$('.new_job_headcount').show();

			var department_id = $('#department_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_form_new_position',
				data: 'department_id=' + department_id,
				dataType: 'html',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('.new_job_headcount').append(response);
				}
			});	

			return false;

		});

		$('.add_existing_job').live('click',function(){

			$('#module-headcount').show();
			$('.existing_job_headcount').show();			

			var department_id = $('#department_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_form_existing_position',
				data: 'department_id=' + department_id,
				dataType: 'html',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('.budget').live('keydown',maskFloat ); 
					$('.existing_job_headcount').append(response);
				}
			});	

			return false;

		});


		$('.delete-single').live('click',function(){
			var obj = $(this);
			Boxy.ask("Do you want to delete this row?",["Yes","No"],
				function(choice){
					if (choice == "Yes"){

						if( obj.hasClass('delete_new_position') ){

							$(obj).parent().parent().remove();
							zebra_structure_list();

							var form_count_new = $('.new_job_form').length;
							var form_count_existing = $('.existing_job_form').length;
											 
							if( form_count_new == 0 ){

								if( form_count_existing == 0 ){

									$('#module-headcount').hide();

								}

								$('.new_job_headcount').hide();

							}

						}
						else if( obj.hasClass('delete_existing_position') ){

							$(obj).parent().parent().remove();
							zebra_structure_list();

							var form_count_new = $('.new_job_form').length;
							var form_count_existing = $('.existing_job_form').length;

							if( form_count_existing == 0 ){

								if( form_count_new == 0 ){

									$('#module-headcount').hide();

								}

								$('.existing_job_headcount').hide();

							}

						}
						else if( obj.hasClass('delete_new_headcount_position') ){

							$(obj).parent().parent().parent().remove();
							zebra_structure_list();

							var new_headcount_position_row = $('.new_headcount_position_row').length;

							if( new_headcount_position_row == 0 ){

								$('.new_headcount_position_empty').show();

							}

						}

					}
				}
			);

		});

		$('.existing_headcount_month_value').live('change',function(){

			var parent_node = $(this).parents('tr');
			var total_headcount = 0;
			var count = 0;

			parent_node.find('.existing_headcount_month_value').each(function(){

				var value = $.trim($(this).val());
				var parse = parseInt(value);

				if( value != '' ){
					if( parse || value == 0 ){
						$(this).val(parse);
						total_headcount = parseInt(total_headcount) + parse;
					}
					else{
						$(this).val(0);
						count++;
					}
				}
				else{
					$(this).val(0);
				}
			});

			parent_node.find('.existing_job_headcount_previous').each(function(){

				var parse = parseInt($(this).val());

				if( $(this).val() != '' ){
					if( parse || $(this).val() == 0 ){
						$(this).val(parse);
						total_headcount = parseInt(total_headcount) + parse;
					}
					else{
						$(this).val(0);
						count++;
					}
				}
			});

			if( count > 0 ){
				message_growl('error','Headcount must be a number');
			}

			parent_node.find('.existing_headcount_month_total').val(total_headcount);

		});

		$('.new_headcount_month_value').live('change',function(){

			var parent_node = $(this).parents('tbody');
			var total_headcount = 0;
			var count = 0;

			parent_node.find('.new_headcount_month_value').each(function(){

				var parse = parseInt($(this).val());

				if( $(this).val() != '' ){
					if( parse || $(this).val() == 0 ){
						$(this).val(parse);
						total_headcount = total_headcount + parse;
					}
					else{
						$(this).val(0);
						count++;
					}
				}
			});
			if( count > 0 ){
				message_growl('error','Headcount must be a number');
			}

			parent_node.find('.new_headcount_month_total').val(total_headcount);

		});

		
		$('.manpower_setup').live('change',function(){

			var parent = $(this).parents('tr');
			update_remarks_list(parent);

		});

		// };
}

	if (module.get_value('view') == "detail") {
		$('.rank_details').live('click', function () {
			var element = $(this); 
			var width   = $(window).width()*.7;
			var position_id = $(this).attr('position');
			var title =  $(this).attr('atitle');
			var amp_pos_id = $(this).attr('amp-position-id');

			$.ajax({
	                url: module.get_value('base_url') + module.get_value('module_link') + '/get_rank_details/detail',
	                data:  'position_id=' + position_id + '&record_id=' + annual_manpower_planning_id + '&amp_pos_id=' + amp_pos_id,
	                type: 'post',
	                dataType: 'json',
	                beforeSend: function(){
						$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
					},	
	                success: function(response) { 
	                	$.unblockUI();

		                   var template_form = new Boxy('<div id="boxyhtml" style="width:'+width+'">' + response.html +'</div>', 
										{		
											title: title,					
											draggable: false,
						                    modal: true,
						                    center: true,
						                    unloadOnHide: true,
						                    beforeUnload: function (){
						                        template_form = false;
						                    }				
										});
	                }
	            });	

		});
	};

});

function get_head(id, type){

     $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_head',
        type: 'post',
        dataType: 'json',
        data: 'id=' + id + '&type=' + type,
        beforeSend: function () {
        	$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
			
        },
        success: function (response) {
        	$.unblockUI();
        	if (response.type == 'department') {
        		$('#annual_user_department_id').val(response.head_id);
        		$('#annual_user_department_id').attr('disabled', true);
				$('#annual_user_department_id').chosen().trigger("liszt:updated");

        	}else if (response.type == 'division') {
				$('#annual_user_division_id').val(response.head_id);
				$('#annual_user_division_id').attr('disabled', true);
				$('#annual_user_division_id').chosen().trigger("liszt:updated");
        	};				
        }
    });
}

function validation(department_id, year) {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/validation',
		data: 'department_id=' + department_id + '&year=' + year + '&record_id=' + module.get_value('record_id'),
		dataType: 'json',
		type: 'post',
		async: false,
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
		},	
		success: function ( response ) {
			$.unblockUI();
			if (response.err == 1){
				$('#department_id').val('');
				$('#department_id').chosen().trigger("liszt:updated");
				message_growl(response.type, response.message);			
			}
		}
	});			

}


function get_previous_headcount(department_id, year) {

	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_previous_headcount',
		data: 'department_id=' + department_id + '&year=' + year + '&record_id=' + module.get_value('record_id'),
		dataType: 'json',
		type: 'post',
		async: false,
		beforeSend: function(){
		
		},								
		success: function ( response ) {
			
			sum = 0;
			$.each(response, function(i, data) {
				$('.existing_position_id').each(function(){

					if( $(this).val() == data.position_id ){

						var parent = $(this).parents('tbody');

						parent.find('.existing_job_headcount_previous').val(data.previous_amp);

					}
				});
			});
		}
	});			
								
}

function get_position_per_department(department_id) {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_per_dept',
		data: 'department_id=' + department_id,
		dataType: 'html',
		type: 'post',
		async: false,
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
		},								
		success: function ( response ) {
			$.unblockUI();
			$('#module-access-container').html(response);

		    $( "#module-access" ).delegate( 'td, th','mouseover mouseleave', function(e) {
		        if ( e.type == 'mouseover' ) {
		          $( this ).parent().addClass( "hover" );
		        }
		        else {
		          $( this ).parent().removeClass( "hover" );
		        }
		    });				
		}
	});	
}

function get_existing_headcount(department_id, record_id, year) {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_existing_headcount',
		data: 'department_id=' + department_id + '&record_id=' + record_id + '&year=' + year,
		dataType: 'html',
		type: 'post',
		async: false,
		beforeSend: function(){
		
		},								
		success: function ( response ) {
			$('#headcount-existing-container').html(response);

		}
	});

}

function zebra_structure_list()
{
	var ctr = 0;
	$('.structure_list tr').each(function(){
		$(this).removeClass();
		if ((ctr % 2) == 1){
			$(this).addClass('odd');
		}
		else{
			$(this).addClass('even');
		}
		ctr++;
	})
}

// function disable_all()
// {
// 	if($('#record_id').val() != '-1')
// 	{
// 		// if($('#annual_status_id').val() != 1 && $('#annual_status_id').val() != 4)
// 		// {
// 			$('input').attr('disabled', true);
// 			$('select').attr('disabled', true);
// 			$('textarea').attr('disabled', true);
// 		// }
// 	}
// }
function update_remarks_list( parent ){

	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_annual_manpower_remarks',
		dataType: 'json',
		type: 'post',
		async: false,
		beforeSend: function(){
		
		},								
		success: function ( response ) {

			$.each(response, function() {

				var remarks = this.remarks;
				var remarks_id = this.annual_manpower_planning_remarks_id;
				var remark_selected = 0;

				parent.find('.manpower_setup').each(function(){

				 	if( ( $(this).val() == remarks_id ) ){

				 		$(this).addClass('main_selected');
				 		remark_selected++;
				 	}

				});

				if( remark_selected == 0 ){

					parent.find('.manpower_setup').each(function(){
						$(this).find('option[value="'+remarks_id+'"]').css('display','block');
					});

				}
				else{

					parent.find('.manpower_setup').each(function(){

						if( !( $(this).hasClass('main_selected') ) ){
							$(this).find('option[value="'+remarks_id+'"]').css('display','none');
						}

					});

					parent.find('.main_selected').removeClass('main_selected');

				}


			});

		}
	});

}

function export_list()
{
	var record_id = $('#record_id').val();
	$('#record-form').attr('action', module.get_value('base_url') + module.get_value('module_link') + '/excel_export/'+ record_id +'');
	$('#record-form').submit();
	$('#record-form').attr('action', '');
	return false;
}

function change_status(record_id, form_status_id,from_detail,remarks) 
{

	if( module.get_value('view') == 'index' ){
		var data = 'record_id='+record_id;
	}
	else{
		var data = $('#record-form').serialize();
	}

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
        data: data + '&form_status_id=' + form_status_id + '&remarks=' + remarks,
        type: 'post',
        dataType: 'json',
        success: function(response) 
        {
  
    		if( from_detail == true ){
    			window.location.href = module.get_value('base_url') + module.get_value('module_link');    
    		}
    		else{
    			message_growl(response.type, response.message);
	            //window.location.reload( false );
	    		$('#jqgridcontainer').trigger('reloadGrid'); 
    		}
    		
        }
    });
    
}

function ajax_save( on_success, is_wizard , callback ){
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{
		ok_to_save = validate_form();
	}
	var count = 0;
	$('.new_headcount_position').each(function(){
		if( $(this).val() == '' ){
			count++;
		}
	});

	if( count > 0 ){
		ok_to_save = false;
	    $('#message-container').html(message_growl('error', 'Position - This field is mandatory.')); 

		
	}

	if( ok_to_save ) {		
		$('#record-form').find('.chzn-done').each(function (index, elem) {
			if (elem.multiple) {
				if ($(elem).attr('name') != $(elem).attr('id') + '[]') {
					$(elem).attr('name', $(elem).attr('name') + '[]');
				}
				
				var values = new Array();
				for(var i=0; i< elem.options.length; i++) {
					if(elem.options[i].selected == true) {
						values[values.length] = elem.options[i].value;
					}
				}
				$(elem).val(values);
			}
		});
		$('#annual_user_division_id').attr('disabled', false);
		$('#annual_user_department_id').attr('disabled', false);
		$('#department_id').attr('disabled', false);
		$('#year').attr('disabled', false);

		var data = $('#record-form').serialize();
		var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"		

		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			/**async: false, // Removed because loading box is not displayed when set to false **/
			beforeSend: function(){
					show_saving_blockui();
			},
			success: function(data){
				if(  data.record_id != null ){
					//check if new record, update record_id
					if($('#record_id').val() == -1 && data.record_id != ""){
						$('#record_id').val(data.record_id);
						$('#record_id').trigger('change');
						if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
					}
					else{
						$('#record_id').val( data.record_id );
					}
				}

				if( data.msg_type != "error"){					
					switch( on_success ){
						case 'back':
							go_to_previous_page( data.msg );
							break;
						case 'email':							
							if (data.record_id > 0 && data.record_id != '') {
								// Ajax request to send email.                    
								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
									data: 'record_id=' + data.record_id,
									dataType: 'json',
									type: 'post',
									async: false,
									beforeSend: function(){
											show_saving_blockui();
										},								
									success: function () {
									}
								});
							}							
							//custom ajax save callback
							if (typeof(callback) == typeof(Function)) callback( data );
						default:
							if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
									window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							}
							else{
								//generic ajax save callback
								if(typeof window.ajax_save_callback == 'function') ajax_save_callback();
								//custom ajax save callback
								if (typeof(callback) == typeof(Function)) callback( data );
								$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });

								window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							}
							break;
					}	
				}
				else{
					$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
				}
			}
		});
	}
	else{
		return false;
	}
	return true;
}

