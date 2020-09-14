
function add_error(fieldname, fieldlabel, msg)
{
	error[error_ctr] = new Array(fieldname, fieldlabel, msg);
	error_ctr++;
}

function maskInteger( e )
{
	var value = $(this).val();
	var str_length = value.length;
	if( (e.keyCode <= 57 && e.keyCode >= 48) || (e.keyCode <= 105 && e.keyCode >= 96) ){
		value = value.replace(/\,/g,'');
		value = addCommas(value);
	}
	else if(e.keyCode == 109 || e.keyCode == 173 || e.keyCode == 189){
		if( str_length > 1 ){
			value = value.slice(value, str_length-1);	
		}
	}
	else if( (e.keyCode <=31 && e.keyCode >= 8) ||  (e.keyCode <=40 && e.keyCode >= 37) || (e.keyCode <= 93 && e.keyCode >= 91) || (e.keyCode <= 145 && e.keyCode >= 112) ){
		// do nothing
		value= value.replace(/\,/g,'');
		value = addCommas(value);
	}
	else{
		value = value.slice(value, str_length-1);
	}
	$(this).val(value);
}

function maskFloat( e )
{
	var value = $(this).val();
	var str_length = value.length;
	if( (e.keyCode <= 57 && e.keyCode >= 48) || (e.keyCode <= 105 && e.keyCode >= 96) || e.keyCode == 190 || e.keyCode == 110){
		value = value.replace(/\,/g,'');
		value = addCommas(value);
	}
	else if(e.keyCode == 109 || e.keyCode == 173 || e.keyCode == 189){
		if( str_length > 1 ){
			value = value.slice(value, str_length-1);	
		}
	}
	else if( (e.keyCode <=31 && e.keyCode >= 8) ||  (e.keyCode <=40 && e.keyCode >= 37) || (e.keyCode <= 93 && e.keyCode >= 91) || (e.keyCode <= 145 && e.keyCode >= 112) ){
		// do nothing
		value = value.replace(/\,/g,'');
		value = addCommas(value);
	}
	else{
		value = value.slice(value, str_length-1);
	}
	$(this).val(value);
}

function numeric_only(e) 
{
	if( (e.keyCode <= 57 && e.keyCode >= 48) || (e.keyCode <= 105 && e.keyCode >= 96) || e.keyCode == 190 || e.keyCode == 110){
	 	return true;
	}
	
	if( (e.keyCode <=31 && e.keyCode >= 8) ||  (e.keyCode <=40 && e.keyCode >= 37) || (e.keyCode <= 93 && e.keyCode >= 91) || (e.keyCode <= 145 && e.keyCode >= 112) ){
		return true;
	}
	
	e.returnValue = false;
		
	e.preventDefault();			
}

function not_percentage(e)
{
	if (e.keyCode != 37 && e.keyCode != 64 && e.keyCode != 35 && e.keyCode != 36 && e.keyCode != 33 && e.keyCode != 94 && e.keyCode != 38 && e.keyCode != 42 && e.keyCode != 40 && e.keyCode != 41) {
		return true;
	}
	
	e.returnValue = false;

	e.preventDefault();			
}

function validate_mandatory(fieldname, fieldlabel, form)
{		
	if( typeof( form ) === 'undefined' ) form = '';
		
	if($( form + ' input[name="'+fieldname+'"]').attr('type') == "checkbox" || $( form + ' input[name="'+fieldname+'"]').attr('type') == "radio"){
		var checked = 0;
		$(form + ' input[name="'+fieldname+'"]').each(function(){
			if($(this).attr('checked')) checked++;
		});
		
		if(checked == 0 && $( form + ' input[name="'+fieldname+'"]').attr('type') == "checkbox"){
			add_error(fieldname, fieldlabel, "This field is mandatory, select at least 1.");
			return false;
		}else if(checked == 0 && $( form + ' input[name="'+fieldname+'"]').attr('type') == "radio"){
			add_error(fieldname, fieldlabel, "This field is mandatory.");
			return false;
		}

	}
	else{
		
		if($(form + ' input[name="'+fieldname+'"]').length != 0 || 
            $(form + ' select[name="'+fieldname+'"]').length !== 0 || 
            $(form + ' textarea[name="'+fieldname+'"]').length != 0 ||
            $(form + ' input[name="'+fieldname+'"]:checked').length != 0 ){
			if($(form + ' input[name="'+fieldname+'"]').val() == "" || 
	                    $(form + ' select[name="'+fieldname+'"]').val() === "" || 
	                    $(form + ' textarea[name="'+fieldname+'"]').val() == "" ||
	                    $(form + ' input[name="'+fieldname+'"]:checked').val() == "" ){
				if( fieldname == "password" && $('.password-field-div').length > 0 ){
					if( $('.password-field-div').css('display') != "none"){
						add_error(fieldname, fieldlabel, "This field is mandatory.");
						return false;
					}
				}else{
					add_error(fieldname, fieldlabel, "This field is mandatory.");
					return false;
				}
			}
		}

		if($(form + ' input[name="'+fieldname+'_from"]').length != 0){
			var temp_fieldname = fieldname + "_from";
			if($(form + ' input[name="'+temp_fieldname+'"]').val() == "" || 
	                    $(form + ' select[name="'+temp_fieldname+'"]').val() === "" || 
	                    $(form + ' textarea[name="'+temp_fieldname+'"]').val() == "" ||
	                    $(form + ' input[name="'+temp_fieldname+'"]:checked').val() == ""){
				if( temp_fieldname == "password" && $('.password-field-div').length > 0 ){
					if( $('.password-field-div').css('display') != "none"){
						add_error(temp_fieldname, fieldlabel + " From", "This field is mandatory.");
					}
				}else{
					add_error(temp_fieldname, fieldlabel + " From", "This field is mandatory.");
				}
			}
		}

		if($(form + ' input[name="'+fieldname+'_to"]').length != 0){
			var temp_fieldname = fieldname + "_to";
			if($(form + ' input[name="'+temp_fieldname+'"]').val() == "" || 
	                    $(form + ' select[name="'+temp_fieldname+'"]').val() == "" || 
	                    $(form + ' textarea[name="'+temp_fieldname+'"]').val() == "" ||
	                    $(form + ' input[name="'+temp_fieldname+'"]:checked').val() == ""){
				if( temp_fieldname == "password" && $('.password-field-div').length > 0 ){
					if( $('.password-field-div').css('display') != "none"){
						add_error(temp_fieldname, fieldlabel + " To", "This field is mandatory.");
					}
				}else{
					add_error(temp_fieldname, fieldlabel + " To", "This field is mandatory.");
				}
			}
		}
		
		//additional for select element
		if($(form + ' select[name="'+fieldname+'"]').length != 0){
			if($(form + ' select[name="'+fieldname+'"]').val() === " "){
				add_error(fieldname, fieldlabel, "This field is mandatory.");
				return false;
			}
		}		
	}
	return true;
}

function validate_integer(fieldname, fieldlabel)	
{
	var fieldval = $('input[name="'+fieldname+'"]').val();
	var fieldlen = $('input[name="'+fieldname+'"]').length;
	
	if( fieldval !== "" && fieldlen > 0){
		// remove comma separations
		var integer_val = parseFloat(fieldval.replace(",", ""));	
		
		//test if integer
		var valid = /^-?\d+$/.test( integer_val );
		if( !valid ){
			add_error(fieldname, fieldlabel, "This field only accept integers.");
			return false;
		}
	}
	return true;
}

function validate_float(fieldname, fieldlabel)
{
	var fieldval = $('input[name="'+fieldname+'"]').val();
	if( fieldval != "" && typeof fieldval !== 'undefined'){
		var float_val = parseFloat(fieldval.replace(",", ""));
		
		//test if float
		var valid = /^[-+]?\d+(\.\d+)?$/.test( float_val );
		if( !valid ){
			add_error(fieldname, fieldlabel, "This field only accept floats or integers.");
			return false;
		}
	}
	return true;
}

function validate_email(fieldname, fieldlabel)
{
	var fieldval = $('input[name="'+fieldname+'"]').val();
	if( fieldval != "" ){
		var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
		var valid = pattern.test(fieldval);
		if( !valid ){
			add_error(fieldname, fieldlabel, "You entered an invalid email address.");
			return false;
		}
	}
	return true;
}

function validate_password(fieldname, fieldlabel)
{
	var fieldval = $('input[name="'+fieldname+'"]').val();
	var fieldval_confirm = $('input[name="'+fieldname+'-confirm"]').val();
	if( fieldval != "" || fieldval_confirm != ""){
		if( fieldval != fieldval_confirm ){
			add_error(fieldname, fieldlabel, "Did not match.");
			return false;
		}
	}
	return true;
}

function validate_ckeditor(fieldname, fieldlabel)
{
	
}

function validate_less_or_equal(fieldname, fieldlabel, value)
{
	var fieldval = $('input[name="'+fieldname+'"]').val();
	fieldval = parseFloat(fieldval);
	//test if float
	var valid = /^[-+]?\d+(\.\d+)?$/.test( fieldval );
	if( fieldval.toString() != "" && valid ){
		if( fieldval > value ){
			add_error(fieldname, fieldlabel, "This field should be less than or equal to "+ value +".");
			return false;
		}
	}
	return true;
}

function validate_less_than(fieldname, fieldlabel, value)
{
	var fieldval = $('input[name="'+fieldname+'"]').val();
	fieldval = parseFloat(fieldval);
	//test if float

	var valid = /^[-+]?\d+(\.\d+)?$/.test( fieldval );
	if( fieldval.toString() != "" && valid){
		if( fieldval >= value ){
			add_error(fieldname, fieldlabel, "This field should be less than "+ value +".");
			return false;
		}
	}
	return true;
}

function validate_greater_or_equal(fieldname, fieldlabel, value)
{
	var fieldval = $('input[name="'+fieldname+'"]').val();
	fieldval = parseFloat(fieldval);
	//test if float
	var valid = /^[-+]?\d+(\.\d+)?$/.test( fieldval );
	if( fieldval.toString() != "" && valid){
		if( fieldval < value ){
			add_error(fieldname, fieldlabel, "This field should be greater or equal to "+ value +".");
			return false;
		}
	}
	return true;
}

function validate_greater_than(fieldname, fieldlabel, value)
{
	var fieldval = $('input[name="'+fieldname+'"]').val();
	fieldval = parseFloat(fieldval);
	//test if float
	var valid = /^[-+]?\d+(\.\d+)?$/.test( fieldval );
	if( fieldval.toString() != "" && valid){
		if( fieldval <= value ){
			add_error(fieldname, fieldlabel, "This field should be greater than "+ value +".");
			return false;
		}
	}
	return true;
}

function validate_url(fieldname, fieldlabel) {
	var fieldval = $('input[name="'+fieldname+'"]').val();
	if( fieldval != "" ){
		var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
		var valid = pattern.test(fieldval);
		if( !valid ){
			add_error(fieldname, fieldlabel, "You entered an invalid URL.");
			return false;
		}
	}
	return true;
}

function validate_date_from(fieldname, fieldlabel, date_from, date_to) {	
	if (typeof(date_from) != 'object') {
		date_from = $('input[name="' + date_from + '"]');
	}

	if (typeof(date_to) != 'object') {		
		date_to = $('input[name="' + date_to + '"]');
	}	

	parse_date_from = date_from.val();
	parse_date_to   = date_to.val();

	if (isNaN(Date.parse(date_from.val()))) {
		parse_date_from = date_from.val() + ' 1';
	}

	if (isNaN(Date.parse(date_to.val()))) {
		parse_date_to = date_to.val() + ' 1';
	} 

	if (Date.parse(parse_date_from) > Date.parse(parse_date_to)) {
		add_error(fieldname, fieldlabel, "Invalid Date Range!\nStart Date cannot be after End Date!")
		return false;
	}

	return true;
}
