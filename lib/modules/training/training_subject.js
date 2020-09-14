$( document ).ready( function() {

	if( module.get_value('view') == 'edit' ){

		window.onload = function(){

			if( $('#training_bond-yes').attr('checked') == 'checked' ){

				$('#rls').removeAttr('disabled');

			}
			else if( $('#training_bond-no').attr('checked') == 'checked' ){

				$('#rls').val('');
				$('#rls').attr('disabled','disabled');

			}


		}

		$('#training_bond-yes').live('click',function(){

			$('#rls').removeAttr('disabled');
			calculate_rls();

		});

		$('#training_bond-no').live('click',function(){

			$('#rls').val('');
			$('#rls').attr('disabled','disabled');

		});

		$('#cost').live('change',function(){
			if( $('#training_bond-yes').attr('checked') == "checked" ){
				calculate_rls();
			}
		});


	}



});


function subject_ajax_save(on_success, is_wizard , callback){

	// if( $('#rls').attr('disabled') != 'disabled' && $('#rls').val() == '' ){

	// 	// message_growl('error', 'Given cost is of out range, Please set Training Bond.');

	// }
	// else{
	// 	calculate_rls();
	// 	ajax_save( on_success, is_wizard , callback );

	// }
	ajax_save( on_success, is_wizard , callback );
}


function calculate_rls(){


	var url = module.get_value('base_url') + module.get_value('module_link') + '/calculate_rls';
    var data = $('#record-form').serialize();

	$.ajax({
	    url: url,
	    dataType: 'json',
	    type:"POST",
	    data: data,
	    beforeSend: function(){
			$.blockUI({
				message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Calculating, please wait...</div>'
			});
		},
	    success: function (response) {
	    	$.unblockUI();
	    	if( response.rls != false ){
	    		$('#rls').val( response.rls );
	    	}
	    	else{
	    		$('#rls').val('');
	    		
	    		if( $('#rls').attr('disabled') != 'disabled' && $('#rls').val() == '' ){
	    			message_growl('error', 'Given cost is of out range, Please set Training Bond.');
	    		}
	    	}

	    }
	});

}