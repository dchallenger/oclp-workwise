$(document).ready(function () {

	if( module.get_value('view') == "edit" ){

		window.onload = function(){

			init_ui();

		}

		$('.add-more').live('click',function(event){

			if( $(this).attr('rel') == 'category' ){

				event.preventDefault();
	        	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/' + $(this).attr('rel');
	        	var type = $(this).attr('rel');
	        	var data = 'category_count=' + ( parseInt($('.category_count').val()) + 1 );

	        	$.ajax({
		            url: url,
		            dataType: 'html',
		            type:"POST",
		            data: data,
		            success: function (response) {

						var category_count = parseInt($('.category_count').val());
			            $('.category_count').val( category_count + 1 );

			            $('.form-multiple-add-category-group fieldset.category').append(response);
			            init_ui();

		            }

		        });

			}
			else if( $(this).attr('rel') == 'item' ){

				var this_item = $(this);

				event.preventDefault();
	        	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/' + $(this).attr('rel');
	        	var type = $(this).attr('rel');
	        	var data = 'category_rand='+this_item.parents('div.form-multiple-add-category').find('.category_rand').val()+'&item_count=' + ( parseInt(this_item.parents('div.form-multiple-add-category').find('.item_count').val()) + 1 );

	        	$.ajax({
		            url: url,
		            dataType: 'html',
		            type:"POST",
		            data: data,
		            success: function (response) {

		            	var item_count = parseInt(this_item.parents('div.form-multiple-add-category').find('.item_count').val());
			            this_item.parents('div.form-multiple-add-category').find('.item_count').val( item_count + 1 );
			            this_item.parents('div.form-multiple-add-category').find('div.form-multiple-add-item-group fieldset.item').append(response);
			            init_ui();

		            }

		        });

			}



		});

		$('a.delete-detail').live('click', function () {

    		var type = $(this).attr('rel');

    		if( type == 'category' ){

    			var category_count = parseInt($('.category_count').val());
			    $('.category_count').val( category_count - 1 );

			    $(this).parents('div.form-multiple-add-category').remove();

    		}
    		else if( type == 'item' ){

    			var item_count = parseInt($(this).parents('.form-multiple-add-item-group').find('.item_count').val());
    			var item_no = 0;
    			var item_group = $(this).parents('.form-multiple-add-item-group');

    			$(this).parents('.form-multiple-add-item-group').find('.item_count').val(item_count - 1);
    			$(this).parents('.form-multiple-add-item').remove();

    			item_group.find('.item_no').each(function(){

    				if( item_no < item_count ){
    					item_no++;
    				}

    				$(this).val(item_no);

    			});

    		}

    	});


	}


});

function init_ui(){

	$('.score_type').chosen();

}

function revalida_ajax_save( on_success, is_wizard , callback, draft ){

	if( draft == 0 ){

		var category_name_error = 0;
		var category_weigth_error = 0;
		var category_weigth_total_error = 0;
		var category_item_score_type_error = 0;
		var category_item_description_error = 0;
		var category_item_weigth_error = 0;
		var category_item_weigth_total_error = 0;
		var total_item_weigth = 0;
		var total_category_weigth = 0;

		$('.category_name').each(function(){
			if( $(this).val() == "" ){
				category_name_error++;
			}
		});

		$('.item_description').each(function(){
			if( $(this).val() == "" ){
				category_item_description_error++;
			}
		});



		$('.item_weigth').each(function(){

			var parse = parseInt($(this).val());

			if( $(this).val() == "" ){
				category_item_weigth_error++;
			}

			if( !parse ){
				category_weigth_total_error++;
			}

		});


		$('.category_weigth').each(function(){

			var parse = parseInt($(this).val());

			if( $(this).val() == "" ){
				category_weigth_error++;
			}

			if( !parse ){
				category_weigth_total_error++;
			}
			else{
				total_category_weigth += parse;
			}
		});

		$('.item_score_type').each(function(){
			if( $(this).val() == "" ){
				category_item_score_type_error++;
			}
		});

		

		if( category_name_error > 0 ){
			add_error('training_revalida_category_name', 'Category Name', "Please complete all category names.");
		}

		if( category_weigth_error > 0 ){
			add_error('training_revalida_category_weigth', 'Category Weight', "Please complete all category weights.");
		}
		else{
			if( total_category_weigth != 100 ){
				add_error('training_revalida_category_weigth', 'Category Weight', "All Category Weights must be total of 100%.");
			}
		}

		if( category_item_description_error > 0 ){
			add_error('training_revalida_item_description', 'Item Description', "Please complete all item descriptions.");
		}

		if( category_item_weigth_error > 0 ){
			add_error('training_revalida_item_description', 'Item Weight', "Please complete all item weights.");
		}
		else{

			$('fieldset.item').each(function(){

				total_item_weigth = 0;

				$(this).find('.item_weigth').each(function(){

					var parse = parseInt($(this).val());
					total_item_weigth += parse;

				});

				if(total_item_weigth != 100 ){
					category_item_weigth_total_error++;
				}

			});

		}

		if( category_item_weigth_total_error > 0 ){
			add_error('training_revalida_item_weigth', 'Item Weight', "All Item Weights per category must be total of 100%.");
		}

		if( category_item_score_type_error > 0 ){
			add_error('training_revalida_item_score_type', 'Item Rating Type', "Please complete all item rating types.");
		}

	}
	else{

		$('input[name="draft"]').val('1');

	}

	ajax_save( on_success, is_wizard , callback);

}