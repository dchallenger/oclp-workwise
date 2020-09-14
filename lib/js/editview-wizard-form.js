$(document).ready(function(){
	$('.wizard-type-form:first').fadeIn('slow').addClass('current-wizard');
	$('.wizard-type-form:first').addClass('wizard-first');
	$('.wizard-type-form:last').addClass('wizard-last');
	
	var wizard_size = 0;
	$('.wizard-type-form').each(function(){
		wizard_size++;
	});
	
	if( wizard_size > 1 ){
		$('.btn-prev').addClass('hidden');
		$('.btn-next-disabled').addClass('hidden');
	}
	else{				
		$('.page-navigator').addClass('hidden');
	}
});

function next_wizard(){
	var current = $('.current-wizard');
	var fg_id = current.attr('fg_id');
	
	if(eval('validate_fg'+fg_id+'()')){
		current.addClass('prev-wizard');
		var current_pos = 0;
		
		var ctr = 1;
		$('.wizard-type-form').each(function(){
			if($(this).hasClass('current-wizard')) current_pos = ctr;
			ctr++;
		});
		var next_pos = current_pos + 1;
		ctr = 1;
		$('.wizard-type-form').each(function(){
			if( next_pos == ctr ) $(this).addClass('current-wizard');
			ctr++;
		});
		current.removeClass('current-wizard');
		
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
			$('.prev-wizard').removeClass('prev-wizard');
		});
	}
}

function prev_wizard(){
	var current = $('.current-wizard');
	current.addClass('prev-wizard');
	var current_pos = 0;
	
	var ctr = 1;
	$('.wizard-type-form').each(function(){
		if($(this).hasClass('current-wizard')) current_pos = ctr;
		ctr++;
	});
	var previous_pos = current_pos - 1;
	
	ctr = 1;
	$('.wizard-type-form').each(function(){
		if( previous_pos == ctr ) $(this).addClass('current-wizard');
		ctr++;
	});
	current.removeClass('current-wizard');
	
	if( $('.current-wizard').hasClass('wizard-first') ){
		$('.btn-prev').addClass('hidden');	
		$('.btn-prev-disabled').removeClass('hidden');
	}
	
	$('.btn-next').removeClass('hidden');	
	$('.btn-next-disabled').addClass('hidden');
	$('.form-submit-btn').addClass('hidden');
	
	$('.prev-wizard').fadeOut('slow', function(){
		$('.current-wizard').fadeIn('slow');
		$('.prev-wizard').removeClass('prev-wizard');
	});
}