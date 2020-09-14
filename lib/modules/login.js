$(document).ready( function(){
	$("#login-username").focus();
	
	$("#submit-login").live('click', function(){
		if( $("#login-username").val() == ''){
			// Shake the login form!
			$( '.login-form' ).effect( 'shake', '',100, function(){
				$("#login-username").focus();
			});
		
			$('#message-container').show();
			message_box( 'error', 'Login field cannot be empty, please try again.' );
			return false;
		}
		else if( $("#login-password").val() == '' ){
			// Shake the login form!
			$( '.login-form' ).effect( 'shake', '',100, function(){
				$("#login-password").focus();
			});
	
			$('#message-container').show();
			message_box( 'error', 'Password field cannot be empty, please try again.' );
			return false;
		}
		else{
			showMessage('Signing in, please wait...');
			validate_login();
		}
		return false;		
	});
		
	$(".forgot-link").click(function(){
		if( typeof Recaptcha != 'undefined' ){
			Recaptcha.create(
			"6LevvcUSAAAAAM7EfLidpDzQTVhUDi-MDIyg44gU",
			"captcha-div",
			{
				theme: "white",
				callback: Recaptcha.focus_response_field
			});
		}
		
		if($('#message-container').css('display') != "none") $('#message-container').hide();
	
		$(".fieldset-login").fadeOut('normal',function(){
			$(".form-head").text("Forgot Password");
			$(".fieldset-forgot").fadeIn();
			var newHeight = $(".fieldset-forgot").outerHeight();
			$("#form-login").stop().animate({height:newHeight});
		});
	});
    
    $(".remember-link").click(function(){
		var currentHeight = $("#form-login").outerHeight();
		$("#form-login").height(currentHeight);
	
		if($('#message-container').css('display') != "none") $('#message-container').hide();
		$(".fieldset-forgot").fadeOut('normal',function(){
			$(".form-head").text("Log In");
			$(".fieldset-login").fadeIn();
			var newHeight = $(".fieldset-login").outerHeight();
			$("#form-login").stop().animate({height:newHeight});
		});
	});
	
	$("#send-link").live('click', function(){
		if( typeof Recaptcha != 'undefined' ){
			var privatekey = "privatekey=6LevvcUSAAAAAOmWQbsJLR-4HZmqlfu0H22Gs9zk";
			var challenge = "&challenge="+Recaptcha.get_challenge();
			var response  = "&response="+Recaptcha.get_response();
		
			//validate captcha
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') +'/validate_recaptcha',
				type: 'POST',
				dataType: 'json',
				data: privatekey + challenge + response,
				beforeSend: function(){
					showMessage('Validating form inputs...');
					Recaptcha.reload();
				},
				success: function ( data ){
					if(data.valid == "true")
						validate_email();
					else{
						$(".fieldset-forgot").fadeIn();
						$(".loading-content").hide();
						$('#message-container').show();
						message_box( 'error', data.raw_msg );
				
						var newHeight = $(".fieldset-forgot").outerHeight();
						$("#form-login").stop().animate({height:newHeight});
					}
				}
			});
		}
		else{
			validate_email();
		}
		return false;
	});
});

function validate_login(){
	var data = $('#form-login').serialize();
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/validate_login',
		type: 'POST',
		dataType: 'json',
		data: data,
		beforeSend: function(){},
		success: function ( data ){
			validate( data );	
		}
	});
}


function showMessage( str )
{
	$('#message-container').hide();
	$('.fieldset-login').hide();
	$('.fieldset-forgot').hide();
	$(".loading-content").fadeIn("normal");
	$(".loading-content span").text(str);
	var newHeight = $(".loading-content").outerHeight();
	$("#form-login").stop().animate({height:newHeight});
}

function validate_email(){
	var data = $('#form-login').serialize();
	$.ajax({
		type: 'post',
		dataType: 'json',
		data: data,
		beforeSend: function(){
			if($("#forgot-email").val() == ''){
				// Shake the login form!
				$( '.login-form' ).effect( 'shake', '',100, function(){
					$("#forgot-email").focus();
				});
				
				$(".fieldset-forgot").fadeIn();
				$(".loading-content").hide();
				var newHeight = $(".fieldset-forgot").outerHeight();
				$("#form-login").stop().animate({height:newHeight});
				
				$('#message-container').show();
				message_box( 'error', 'Email field cannot be empty, please try again.' );
				
				return false;
			}
			else{
				showMessage( 'Validating email address, please wait...' );
			}	
		},
		url: module.get_value('base_url') + module.get_value('module_link') +'/verify_email',
		success: function(data){
			if(data.msg != ""){
				$(".fieldset-forgot").fadeIn();
				$(".loading-content").hide();
				var newHeight = $(".fieldset-forgot").outerHeight();
				$("#form-login").stop().animate({height:newHeight});
				$('#message-container').show();    
				message_box( data.msg_type, data.msg );
				$('#forgot-email').focus();
			}
			else {
				// sending requested info through email

				var email = "";

				if( data.email != "" ){
					var email = data.email;
					showMessage('Sending how to reset password, please wait...');
					sendResetPassLink(email);
				}
				else{
					showMessage('Sending how to reset password, please wait...');
					sendResetPassLink(email);
				}

				
			}
			
			return false;
		}
	});	
}

function sendResetPassLink(email_add)
{

	if(email_add == ""){
		email_add = $("#forgot-email").val();
	}

    $.ajax({
        type: 'post',
        dataType: 'json',
        data: ({email:email_add}),
        url: module.get_value('base_url') + module.get_value('module_link') +'/send_reset_password',
        success: function(data)
        {
            $(".loading-content").hide();
            $(".fieldset-forgot").show();
            $('#forgot-email').focus();
            if(data.is_error == 0)
			{	
				$('#message-container').show();
           		message_box( 'success', data.system_msg );
		    	
				
				$(".fieldset-forgot").hide();
                $("#forgot-email").val('');
				$(".fieldset-login").show();
				$(".form-head").text("Log In");
				
				var newHeight = $(".fieldset-login").outerHeight();
				$("#form-login").stop().animate({height:newHeight});
			}
			else{
				$('#message-container').show();
           		message_box( 'error', data.system_msg );
			}
        }
    });
}


function sendPass(data)
{
    $.ajax({
        type       : 'post',
        dataType   : 'json',
        data       : ({email:$("#forgot-email").val()}),
        url        : 'emailPassChecking',
        success    : function(data)
        {
            $(".loading-content").hide();
            $(".fieldset-forgot").show();
            $('#forgot-email').focus();
            if(data.is_error == 0)
			{	
				$('#message-container').show();
           		message_box( 'success', data.system_msg );
				
				
				$(".fieldset-forgot").hide();
                $("#forgot-email").val('');
				$(".fieldset-login").show();
				$(".form-head").text("Log In");
				
				var newHeight = $(".fieldset-login").outerHeight();
				$("#form-login").stop().animate({height:newHeight});
			}
			else{
				$('#message-container').show();
           		message_box( 'error', data.system_msg );
			}
        }
    });
}

function validate( data )
{
	if(data.login == false ){
		$(".loading-content").hide();
		$(".fieldset-login").fadeIn("normal").fadeTo("normal", 1);
		var newHeight = $(".fieldset-login").outerHeight();
		$("#form-login").stop().animate({height:newHeight});
		
		if( data.msg != '' ){
			$('#message-container').show();
			message_box(data.msg_type, data.msg );
		}

		//reset error styles
		if( $('#login-username').hasClass('field-has-error') ) $('#login-username').removeClass('field-has-error');
		if( $('#login-password').hasClass('field-has-error') ) $('#login-password').removeClass('field-has-error');
		
		if( data.error_field == 'login' ) $('#login-username').addClass('field-has-error');
		if( data.error_field == 'password') $('#login-password').addClass('field-has-error');
		
		$('input[name="'+data.error_field+'"]').focus();

		// Shake the login form!
		$( '.login-form' ).effect( 'shake', 100 );
		
	}
	else {
		window.location = module.get_value('base_url');
	}
	return false;
}

/******************************************************/
/*  On keypress 'enter' when focussed in login/forgot fields for usability purposes
/******************************************************/
$(function()
{
	var userBox = $( "#login-username" );
	var passBox = $( "#login-password" );
	var forgotBox = $( "#forgot-email" );
	
	var code =null;
	userBox.keypress( function( e )
	{
		code = (e.keyCode ? e.keyCode : e.which);
		if ( code == 13 ) $( "#submit-login" ).trigger( "click" );		
	});
	
	passBox.keypress( function( e )
	{
		code = (e.keyCode ? e.keyCode : e.which);
		if ( code == 13 ) $( "#submit-login" ).trigger( "click" );		
	});
	
	forgotBox.keypress( function( e )
	{
		code = (e.keyCode ? e.keyCode : e.which);
		if ( code == 13 ) $( "#send-link" ).trigger( "click" );		
	});

});