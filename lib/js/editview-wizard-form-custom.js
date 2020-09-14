$(document).ready(function(){    
	/**
     * Handles events when clicking on the wizard nav.
     */

	$('a.leftcol-control').live('click', function () {		        
		var current = $('.current-wizard');

		var fg_id = current.attr('fg_id');

		if ($('#record_id').val() < 0) {
			if (!$(this).parent('li').hasClass('wizard-passed')) {
				return;
			}
		}

		// Proceed if function does not exist.
		try {
			valid = eval('validate_fg' + fg_id + '()');		
		} catch (err) {
			valid = true;   
		}

		if(valid){
			current.addClass('prev-wizard');
			current.removeClass('current-wizard');

			$('a[rel="' + current.attr('id') + '"]').parent('li').removeClass('wizard-active');
			$(this).parent('li').addClass('wizard-active');

			$('.wizard-leftcol li:not(.wizard-active))').addClass('wizard-grayed');
			$(this).parent('li').removeClass('wizard-grayed');

			$('#' + $(this).attr('rel')).addClass('current-wizard');
			$('#' + $(this).attr('rel')).addClass('wizard-viewed');

			$('.prev-wizard').fadeOut('slow', function(){
				$('.current-wizard').fadeIn('slow');
				$('.prev-wizard').removeClass('prev-wizard');
			});

			// Show save button if its the last form.
			if( $('.current-wizard').hasClass('wizard-last') ){
				$('.btn-next').addClass('hidden');
				$('.btn-next-disabled').removeClass('hidden');

				$('.form-submit-btn').removeClass('hidden');
			}
			else{
				$('.btn-next').removeClass('hidden');
				$('.btn-next-disabled').addClass('hidden');
				$('.form-submit-btn').addClass('hidden');
			}

			// Deactivate prev button for the first page of the form
			if( $('.current-wizard').hasClass('wizard-first')){
				$('.btn-prev').addClass('hidden');
				$('.btn-prev-disabled').removeClass('hidden');
			} else {
				$('.btn-prev').removeClass('hidden');
				$('.btn-prev-disabled').addClass('hidden');
			}
			current = $('.current-wizard');
			if (typeof(activate_add_more) == typeof(Function)) {
				activate_add_more(current);
			}

			$(this).parent('li').prevAll('li.wizard-grayed').removeClass('wizard-grayed').addClass('wizard-prev');
			$(this).parent('li').nextAll('li.wizard-prev').removeClass('wizard-prev').addClass('wizard-grayed');
		}    

		$('#fglabel_span').text($('.wizard-active .wizard-label').text());
	});
	// End left nav function.

	setTimeout(
		function () {			
			$('#fglabel_span').text($('.wizard-active .wizard-label').text());
		}, 100);    	

	// Separated this because there are some needs to call this function again, like on some ajax calls that does not
	// return the whole layout (recruitment/applicants/detail via AJAX)
	init_wizard_buttons();
});

function edit_detail() {
	// Get current fg_id so we know which fieldgroup to open on edit
	var current = $('.current-wizard');

	var fg_id = current.attr('fg_id');

	$('#record-form').append('<input type="hidden" name="default_fg" value="' + fg_id +'" />');

	edit();
}

function init_wizard_buttons() {
	fg_id = 0;
	if ($('input[name="default_fg"]').size() > 0) {
		fg_id = $('input[name="default_fg"]').val();
		$('#fg-' + fg_id).fadeIn('slow').addClass('current-wizard');
		$('#fg-' + fg_id).addClass('wizard-first');
	} else {
		$('.wizard-type-form:first').fadeIn('slow').addClass('current-wizard');
		$('.wizard-type-form:first').addClass('wizard-first');
	}

	$('.wizard-type-form:last').addClass('wizard-last');

	var wizard_size = $('.wizard-type-form').size();

	if( wizard_size > 1 && fg_id == 0 ){
		$('.btn-prev').addClass('hidden');
		$('.btn-next-disabled').addClass('hidden');
	} else if(fg_id > 0) {
		if ($('.current-wizard').prev('div.wizard-type-form').size() > 0) {
			$('.btn-prev-disabled').addClass('hidden');
			$('.btn-prev').removeClass('hidden');
		}

		if ($('.current-wizard').next('div.wizard-type-form').size() == 0) {
			$('.btn-next').addClass('hidden');
			$('.btn-next-disabled').removeClass('hidden');

			$('.form-submit-btn').removeClass('hidden');
		}
	} else{
		$('.page-navigator').addClass('hidden');
	}

	current = $('.current-wizard');
	activate_add_more(current);
    
	$('a[rel="' + current.attr('id') + '"]').parent('li').addClass('wizard-active');

	$('.wizard-active').prevAll('li').removeClass('wizard-grayed').addClass('wizard-prev');
	$('.wizard-active').nextAll('li.wizard-prev').removeClass('wizard-prev').addClass('wizard-grayed');    

	$('.wizard-leftcol li:not(.wizard-active)').addClass('wizard-grayed');
}

function next_wizard(){
	var current = $('.current-wizard');
	var fg_id = current.attr('fg_id');

	// Proceed if function does not exist.
	try {
		valid = eval('validate_fg' + fg_id + '()');
	} catch (err) {
		valid = true;   
	}    

	if(valid){
		$('.btn-next a').attr('onclick', '');

		current.addClass('prev-wizard');

		var current_pos = 0;

		var ctr = 1;
		$('.wizard-type-form').each(function(){
			if($(this).hasClass('current-wizard')) current_pos = ctr;
			ctr++;
		});
		var next_pos = current_pos + 1;

		current.removeClass('current-wizard');
		current.next('div.wizard-type-form').addClass('current-wizard');

		current = $('.current-wizard');

		if (typeof(activate_add_more) == typeof(Function)) {
			activate_add_more(current);
		}

		if( $('.current-wizard').hasClass('wizard-last') ){
			$('.btn-next').addClass('hidden');
			$('.btn-next-disabled').removeClass('hidden');

			$('.form-submit-btn').removeClass('hidden');
		}
		else{
			$('.btn-next').removeClass('hidden');
			$('.btn-next-disabled').addClass('hidden');
			$('.form-submit-btn').addClass('hidden');
		}

		if( current_pos > 0 ){
			$('.btn-prev').removeClass('hidden');
			$('.btn-prev-disabled').addClass('hidden');
		}

		$('.prev-wizard').fadeOut('slow', function(){
			$('.current-wizard').fadeIn('slow');
			previous = $('.prev-wizard');
			$('.prev-wizard').removeClass('prev-wizard');

			$('a[rel="' + previous.attr('id') + '"]').parent('li').removeClass('wizard-active');
			$('a[rel="' + current.attr('id') + '"]').parent('li').addClass('wizard-active');
			
			$('.wizard-active').prevAll('li').removeClass('wizard-grayed').addClass('wizard-prev').addClass('wizard-passed');

			$('.wizard-active').nextAll('li.wizard-prev').removeClass('wizard-prev').addClass('wizard-grayed');

			//$('.wizard-leftcol li:not(.wizard-active)').addClass('wizard-grayed');
			$('.wizard-active').removeClass('wizard-grayed').addClass('wizard-passed');

			$('.btn-next a').attr('onclick', 'next_wizard()');		

			$('#fglabel_span').text($('.wizard-active .wizard-label').text());
		});		
	}
}

function prev_wizard(){
	var current = $('.current-wizard');
	current.addClass('prev-wizard');
	var current_pos = 0;

	$('.btn-prev a').attr('onclick', '');

	current.removeClass('current-wizard');
	current.prev('div.wizard-type-form').addClass('current-wizard');

	current = $('.current-wizard');
	if (typeof(activate_add_more) == typeof(Function)) {
		activate_add_more(current);
	}

	if( $('.current-wizard').prev('div').size() == 0){
		$('.btn-prev').addClass('hidden');
		$('.btn-prev-disabled').removeClass('hidden');
	}

	$('.btn-next').removeClass('hidden');
	$('.btn-next-disabled').addClass('hidden');
	$('.form-submit-btn').addClass('hidden');

	$('.prev-wizard').fadeOut('slow', function(){

		$('.current-wizard').fadeIn('slow');
		previous = $('.prev-wizard');
		$('.prev-wizard').removeClass('prev-wizard');

		$('a[rel="' + previous.attr('id') + '"]').parent('li').removeClass('wizard-active');
		$('a[rel="' + current.attr('id') + '"]').parent('li').addClass('wizard-active');

		$('.wizard-leftcol li:not(.wizard-active)').addClass('wizard-grayed');
		$('.wizard-active').removeClass('wizard-grayed');		
		
		$('.wizard-active').nextAll('li.wizard-prev').removeClass('wizard-prev').addClass('wizard-grayed');

		$('.btn-prev a').attr('onclick', 'prev_wizard()');
		
		$('#fglabel_span').text($('.wizard-active .wizard-label').text());
	});

}