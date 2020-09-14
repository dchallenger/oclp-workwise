$(document).ready(function () {  
	if($("#sss").size() > 0){
		$("#sss").mask("99-9999999-9", {placeholder: "x"});
		$("#philhealth").mask("99-999999999-9",{placeholder: "x"});
		$("#tin").mask("999-999-999-999", {placeholder: "x"});
		$("#pagibig").mask("9999-9999-9999", {placeholder: "x"});
	} 
	
	if( module.get_value('view') == "edit" || module.get_value('view') == "quick_edit" ){
		//if( $("#record_id").val() != "-1" ) get_custom_standard_transaction();
	
		$('label[for="employee_id"]').next().find('.icon-group').remove();

		var sss_label = '<div class="form-item odd sss_label"><div class="text-input-wrap"><strong>SSS Setup</strong><hr/></div></div>';
		var hdmf_label = '<div class="form-item even "><div class="text-input-wrap"><strong>HDMF Setup</strong><hr/></div></div>';
		var phic_label = '<div>&nbsp;</div><div>&nbsp;</div><div class="form-item odd phic_label"><div class="text-input-wrap"><strong>PHIC Setup</strong><hr/></div></div>';
		var ecola_label = '<div class="form-item even "><div class="text-input-wrap"><strong>E-cola Setup</strong><hr/></div></div>';
		var tax_label = '<div class="form-item even "><div>&nbsp;</div><div class="text-input-wrap"><strong>WHTAX Setup</strong><hr/></div></div>';
		$('label[for=sss_mode]').parent().before(sss_label);
		$('.sss_label').after(hdmf_label);
		$('label[for=phic_mode]').parent().before(phic_label);
		$('.phic_label').after(ecola_label);
		$('label[for=phic_amount]').parent().after(tax_label);

		$('#sss_amount').disable();
		$('#phic_amount').disable();
		$('#hdmf_amount').disable();
		$('#tax_amount').disable();

		$('#sss_mode').change(function(){
			if( $(this).val() == '3' ){
				$('#sss_amount').enable();
			}
			else{
				$('#sss_amount').val('');
				$('#sss_amount').disable();
			}
		});

		$('#hdmf_mode').change(function(){
			if( $(this).val() == '3' ){
				$('#hdmf_amount').enable();
			}
			else{
				$('#hdmf_amount').val('');
				$('#hdmf_amount').disable();
			}
		});

		$('#phic_mode').change(function(){
			if( $(this).val() == '3' ){
				$('#phic_amount').enable();
			}
			else{
				$('#phic_amount').val('');
				$('#phic_amount').disable();
			}
		});

		$('#sss_mode').trigger('change');
		$('#hdmf_mode').trigger('change');
		$('#phic_mode').trigger('change');
	}
});

function ajax_save_callback(){
	
}

/**
 * Quick Add/Edit Custom Standard Transaction
 * 
 * @return void
 */
function edit_cst( cst_id ){
	var data = 'record_id='+cst_id;
	if( cst_id == -1 ) data = data + '&employee_id='+$( '#employee_id' ).val();
	var module_url = module.get_value('base_url') + 'payroll/custom_standard_transaction/quick_edit';
	showQuickEditForm( module_url, data);
}

function quickedit_boxy_callback( e ){
	get_custom_standard_transaction();
}

/**
 * Get the Custom Standard Transaction
 * 
 * @return void
 */
function get_custom_standard_transaction(){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_custom_standard_transaction",
		type:"POST",	
		data: "employee_id="+$('#employee_id').val(),
		dataType: "json",
		beforeSend: function(){
			$('.cst-div').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function( data ){
			$('.cst-div').html( data.cst );
			$('.cst-div').unblock();	
			
			if( data.msg != "" ) $('#message-container').html(message_growl(data.msg_type, data.msg));
		}
	});
}

/**
 * Delete Custom Standard Transaction
 * 
 * @return void
 */
function delete_cst( cst_id )
{
	Boxy.ask("Delete selected custom standard transaction?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"payroll/custom_standard_transaction/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+cst_id,
				success: function(data){
					message_growl(data.msg_type, data.msg);
					get_custom_standard_transaction();	
				}
			});
		}
	},
	{
		title: "Delete Custom Standard Transation"
	});
}