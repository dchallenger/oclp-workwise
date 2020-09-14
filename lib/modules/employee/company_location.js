$(document).ready(function(){
	$('#region_id').change(function(){
		var region_id = $(this).val();
		if( region_id != '' ){
			$.ajax({
				type: 'POST',
				url:  module.get_value('base_url')+module.get_value('module_link') + '/get_province',
				data: 'region_id='+region_id,
				dataType: 'json',
				success: function (response) {
					$('#province_id').html(response.ddlb);
					$('#province_id').trigger('liszt:updated');
				}
			});
		}
	});

	$('#province_id').change(function(){
		var province_id = $(this).val();
		if( province_id != '' ){
			$.ajax({
				type: 'POST',
				url:  module.get_value('base_url')+module.get_value('module_link') + '/get_cities',
				data: 'province_id='+province_id,
				dataType: 'json',
				success: function (response) {
					$('#city_id').html(response.ddlb);
					$('#city_id').trigger('liszt:updated');
				}
			});
		}
	});
});