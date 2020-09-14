$( document ).ready(function(){

	if ( module.get_value('view') == "edit" ){
		$('.add_row').live('click',function() {
			var elem = $(this);
			var id = $(elem).attr("columnid");
			var cnt = $('#counter').val();

			$('.insert_delete_row').show();
			$('.update_delete_row').show();

			$.ajax({
			        url: module.get_value('base_url') + module.get_value('module_link') + '/get_benefit_parameters',
			        data: '',
			        dataType: 'json',
			        type: 'post',
			        async: false,          
			        success: function ( response ) 
			        {
						var html = '<div>'+
								'<table style="width:100%" class="default-table boxtype" id="details-list">'+
								    '<tr>'+
								    	'<td>'+
											'<label class="label-desc gray" for="insert_benefit[]"> Benefits: </label>'+
											'<div class="select-input-wrap">';

						html = html + response.benefit_html;

						html = html + '</div>'+
								    	'</td>'+
								    	'<td>'+
											'<label class="label-desc gray" for="insert_benefit_description[]"> Description: </label>'+
											'<div class="text-input-wrap">'+
												'<input id="insert_benefit_description[]" class="input-text" type="text" name="insert_benefit_description[]">'+
											'</div>'+
								    	'</td>'+
								    	'<td>'+
											'<label class="label-desc gray" for="insert_delete[]"> </label>'+
											'<div class="text-input-wrap">'+
											'	<a class="icon-16-delete icon-button insert_delete_row" href="javascript:void(0)" original-title=""></a>'+
											'</div>'+
								    	'</td>'+
									'</tr>'+
								'</table>'+
							'<div>';



						$('.details-div').after(html);
			        }
			    });

			var counter = parseInt(cnt)+1;

			$('#counter').val(counter);
		});

		$('.insert_delete_row').live('click',function(){
			$(this).parent().parent().parent().parent().parent().parent().remove();
		});

		$('.update_delete_row').live('click',function(){
			var recruitment_benefit_package_detail_id = $(this).attr('id');
			$.ajax({
			        url: module.get_value('base_url') + module.get_value('module_link') + '/delete_benefit_package_details',
			        data: 'recruitment_benefit_package_detail_id='+recruitment_benefit_package_detail_id,
			        dataType: 'json',
			        type: 'post',
			        async: false,          
			        success: function ( response ) 
			        {
						$(this).parent().parent().parent().parent().parent().parent().remove();
			        }
			    });
		});
	}
});