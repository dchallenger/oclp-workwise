$(document).ready(function () {
	$('#applicant_id').attr('disabled',true).trigger('liszt:updated');
	$('#candidate_status_id').attr('disabled',true);
	
	$('.icon-16-users').live('click', function(){
		window.location = module.get_value('base_url') + "recruitment/applicants/detail/" + $(this).attr('candidate_id') + "/2" ;
		
	});

	$('.print_contract').live('click', function(){
		var record_id = $(this).attr('joboffer_id');
		$.ajax({
			url: module.get_value('base_url') + 'recruitment/candidate_contract_signing/get_template_form',
			data: 'record_id=' + record_id,
			dataType: 'json',
			type: 'post',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});  		
			},			
			success: function ( data ) {				
				$.unblockUI();	
				var viewport_width 	= $(window).width();
				var width 			= .30 * viewport_width;
				if(!template_form){
					template_form = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.form +'</div>',
					{
						title: 'Print Manager',
						draggable: false,
						modal: true,
						center: true,
						unloadOnHide: true,
						beforeUnload: function (){
							template_form = false;
						}
					});
					boxyHeight(template_form, '#boxyhtml');			
				}
			}
		});	
	});	

	$('.icon-16-approve').live('click', function () {
		var obj = $(this);
		var candidate_id = obj.attr('candidate_id');
		var pre_employment = obj.attr('pre-employment');

		Boxy.ask(obj.attr('tooltip') + '?', ["Yes", "Cancel"],
			function( choice ) {
				if(choice == "Yes"){
					$.ajax({
						url: module.get_value('base_url') + 'recruitment/candidate_contract_signing/change_status',
						data: 'status=accept&record_id=' + obj.attr('joboffer_id'),
						dataType: 'json',
						type: 'post',
						beforeSend: function(){
							$.blockUI({
								message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
							});  		
						},			
						success: function (response) {
							page_refresh();
							$.unblockUI({
										onUnblock: function() {
											message_growl(response.msg_type, response.msg)
										}
									});								
							window.location = module.get_value('base_url') + "recruitment/preemployment/detail/" + pre_employment ;						
						}
					});
				}
			},
			{
				title: obj.attr('tooltip')
			}
		);
	});

	$('.icon-16-disapprove').live('click', function () {
		var obj = $(this);
		var width = $(window).width()*.3;

		Boxy.confirm(
			'<div id="boxyhtml" style="width:'+width+'px">'
			+ obj.attr('tooltip') + '?'
			+ '<div>Remarks</div><textarea style="height:100px;width:340px;" id="remarks" name="remarks"></textarea></div>',
			function() {				
				$.ajax({
					url: module.get_value('base_url') + 'recruitment/candidate_contract_signing/change_status',
					data: 'status=reject&record_id=' + obj.attr('joboffer_id') + '&remark=' + $('#remarks').val(),
					dataType: 'json',
					type: 'post',
					beforeSend: function(){
						$.blockUI({
							message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
						});  		
					},			
					success: function (response) {				
						$.unblockUI({
							onUnblock: function() {
								message_growl(response.msg_type, response.msg)
							}
						});				
						page_refresh();
					}
				});
			},
			{
				title: obj.attr('tooltip')
			}
		);

	});
	
	if(module.get_value('view') == 'index' && module.get_value('module_link') == 'recruitment/candidate_contract_signing'){
		$('.print_contract').die('click').live('click', function(){
			var record_id = $(this).parent().parent().parent().attr("id");
			$.ajax({
				url: module.get_value('base_url') + 'recruitment/candidate_contract_signing/get_template_form',
				data: 'record_id=' + record_id,
				dataType: 'json',
				type: 'post',
				beforeSend: function(){
					$.blockUI({
						message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
					});  		
				},			
				success: function ( data ) {				
					$.unblockUI();	
					var viewport_width 	= $(window).width();
					var width 			= .30 * viewport_width;
					if(!template_form){
						template_form = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.form +'</div>',
						{
							title: 'Print Manager',
							draggable: false,
							modal: true,
							center: true,
							unloadOnHide: true,
							beforeUnload: function (){
								template_form = false;
							}
						});
						boxyHeight(template_form, '#boxyhtml');			
					}
				}
			});	
		});
	}
		
	setTimeout(function () {
		$('#basic').keyup(function(){ 
			$('#basic').keyup(maskFloat);
		});
		
		if (module.get_value('view') != 'index') {
			$('#basic').val( $('#basic').val().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") );	
		
		
			$('#position_id').attr('disabled','disabled').trigger("liszt:updated");
			$('#candidate_id').attr('disabled','disabled');

			$.ajax({
				url: module.get_value('base_url') + 'recruitment/candidate_contract_signing/get_benefit_field',
				data: 'job_offer_id='+ $('.benefits-div').parents('form').find('#record_id').val(),
				dataType: 'json',
				type: 'post',
				beforeSend: function(){
								
				},			
				success: function (data) {				
					$('.benefits-div').parent().append(data.field);
					$('input.benefit-field').each(function(){
						$(this).keyup( maskFloat);
					});
					$('input[name=selected-benefits]').val( data.selected_benefits );
					$('#benefitddlb').html(data.benefitddlb);
					if (quickedit_boxy != undefined) {					
						boxyHeight(quickedit_boxy, '#boxyhtml');
					}
				}
			});		
		};	
	}, 100);
});

var template_form = false;

function add_benefit(){
	if( $('#benefitddlb').val() != "" ){
		var benefit_id  = $('#benefitddlb').val();
		var selected_benefits  = $('input[name=selected-benefits]').val();
		$.ajax({
			url: module.get_value('base_url') + 'recruitment/candidate_contract_signing/add_benefit_field',
			data: 'benefit_id='+benefit_id+'&selected_benefits='+selected_benefits,
			dataType: 'json',
			type: 'post',
			beforeSend: function(){
							
			},			
			success: function (data) {				
				$('.benefits-div').parent().append(data.field);
				$('input.benefit-field').each(function(){
					$(this).keyup( maskFloat);
				});
				$('input[name=selected-benefits]').val( data.selected_benefits );
				$('#benefitddlb').html(data.benefitddlb);
				$('input[name="benefit['+benefit_id+']"]').focus();

				if (quickedit_boxy != undefined) {
					boxyHeight(quickedit_boxy, '#boxyhtml');
				}				
			}
		});	
	}
	else{
		Boxy.ask("Please select a benefit?", ["Cancel"],
		function( choice ) {
			
		},
		{
			title: "Select a Benefit"
		});
	}
}

function delete_benefit( field, benefit_id ){
	Boxy.ask("Are you sure you want to delete benefit?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			var benefitval = field.parent().parent().find('input[name=benefit_id'+benefit_id+']');
			var benefitlabel = field.parent().parent().find('input[name=benefit_label'+benefit_id+']');
			field.parent().parent().parent().remove();
			var option = '<option value="'+benefitval.val()+'">'+benefitlabel.val()+'</option>';
			$('#benefitddlb').append(option);
			var selected_benefits = new Array();
			var sb= 0;
			var temp = $('input[name=selected-benefits]').val().split(','); 
			for(var i in temp){
				if( temp[i] != benefit_id ){
					selected_benefits[sb] = temp[i];
					sb++;
				}
			}
			$('input[name=selected-benefits]').val( selected_benefits.join(',') );
		}
	},
	{
		title: "Delete Benefit"
	});
}

function print_job_offer(){
	var job_offer_id =  $('form[name="print-jocontract"] input[name="jocontract-job_offer_id"]').val();
	var jo_template_id =  $('form[name="print-jocontract"] select[name="jo_template_id"]').val();
	if( jo_template_id != "" ){
		var url = module.get_value('base_url') + 'recruitment/candidate_contract_signing/print_record/' + job_offer_id + '/' + jo_template_id;
		window.open( url, '_blank');
	}
	else{
		Boxy.ask("Please select a template to use?", ["Cancel"],
		function( choice ) {
			
		},
		{
			title: "Select Template"
		});
	}
}

if (typeof(print_contract) != typeof(Function)) {
	function print_contract(){
		var job_offer_id =  $('form[name="print-jocontract"] input[name="jocontract-job_offer_id"]').val();
		var record_id =  $('form[name="print-jocontract"] input[name="jocontract-record_id"]').val();
		var contract_template_id =  $('form[name="print-jocontract"] select[name="contract_template_id"]').val();
		if( contract_template_id != "" ){
			var url = module.get_value('base_url') + module.get_value('module_link') + '/print_contract/' + job_offer_id + '/' + contract_template_id + '/' + record_id;
			window.open( url, '_blank');
		}
		else{
			Boxy.ask("Please select a template to use?", ["Cancel"],
			function( choice ) {
				
			},
			{
				title: "Select Template"
			});
		}
	}
}

if (typeof(print_hiring_approval) != typeof(Function)) {
	function print_hiring_approval(candidate_id){
		var url = module.get_value('base_url') + module.get_value('module_link') + '/print_hiring_approval/'+ candidate_id;
		window.open( url, '_blank');

	}
}

function date_from_close(dateText) {	
 	var date1 = new Date(dateText);
 	var month;
 	// "+7" = + 6 months 	
 	if( (date1.getMonth() + 7) > 12 ){ 
		date1.setMonth( (date1.getMonth()+7)-12); 
		date1.setFullYear( date1.getFullYear() + 1);
		month = date1.getMonth();
	} else if (date1.getMonth() == 5) {
		month = 12;
 	} else {
 		date1.setMonth(date1.getMonth() + 7);
 		month = date1.getMonth();
 	}
 	
 	// Timeout is needed calendar does not update when two instances are open.
 	setTimeout(function () {
 		$('#date-temp-to').datepicker('setDate', month + '/' + date1.getDate() + '/' + date1.getFullYear()); 	
 	}, 100);
}