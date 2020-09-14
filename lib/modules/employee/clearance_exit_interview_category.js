$( document ).ready( function() {
	
	init_datepick();

	if(module.get_value('view') == 'edit'){

		init_ui();

		$('a.add-more').click(function(event) {

			event.preventDefault();
			var url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/' + $(this).attr('rel');
			var type = $(this).attr('rel');


			if( $(this).attr('rel') == 'item' ){
				var data = 'item_count=' + ( parseInt($('.item_count').val()) + 1 );
			}

			$.ajax({
				url: url,
				dataType: 'html',
				type:"POST",
				data: data,
				success: function (response) {

					if( type == 'item' ){

						var item_count = parseInt($('.item_count').val());
						$('.item_count').val( item_count + 1 );
						$('.form-multiple-add-item fieldset').append(response);
						init_ui();

					}
				}

			});

		});

		$('a.delete-detail').live('click', function () {

			var type = $(this).attr('rel');

			if( type == 'item' ){

				var remove_item_no = parseInt($(this).parents('div.form-multiple-add').find('.item_no').val());
				$(this).parents('div.form-multiple-add').remove();

				var session_count = parseInt($('.item_count').val());
				$('.item_count').val( session_count - 1 );

				$('.item_no').each(function(){

					if( parseInt($(this).val()) > remove_item_no ){
						var item_no = parseInt($(this).val());
						$(this).val( item_no - 1 );
					}

				});


			}


		});

		$('.item_inactive').live('click',function(){

			var value = $(this).val();
			$(this).parent().find('.item_inactive_hidden').val(value);

		});

		$('#active-no').live('click', function () {
			$('input[name="active"]').val(0)
		});
		if($('#active-no').is(':checked')) {
			$('input[name="active"]').val(0)
		}


		$('#active-yes').live('click', function () {
			$('input[name="active"]').val(1)
		});
		if($('#active-yes').is(':checked')) {
			$('input[name="active"]').val(1)
		}

	}

});


function save_clearance_exit_interview_category(on_success, is_wizard , callback){

	$('.clearance_exit_interview_item').each(function(){

		if( $(this).val() == '' ){

			add_error('clearance_exit_interview_item', 'Exit Interview Item', "This field is mandatory.");

		}

	});

	$('.exit_interview_score_type').each(function(){

		if( $(this).val() == '' ){

			add_error('exit_interview_score_type', 'Rating Type', "This field is mandatory.");

		}

	});


	ajax_save( on_success, is_wizard , callback );

}


function init_ui(){

	$('.exit_interview_score_type').chosen();


}