function validate_form(){
	if( $('#explaination').val() == ""){
		add_error('explanation', 'Explaination', "This field is mandatory.");
	}
	
	//errors
	if(error.length > 0){
		var error_str = "Please correct the following errors:<br/><br/>";
		for(var i in error){
			if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
			error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
		}
		$('#message-container').html(message_growl('error', error_str));
		
		//reset errors
		error = new Array();
		error_ctr = 0
		return false;
	}
	
	return true;
}