$( document ).ready( function() {
	if(module.get_value('view') == 'edit')
	{

		$('#form-div').children('div').find('.form-head').find('a').parent().parent().find('.col-1-form,.col-2-form').addClass("hidden");
        $('#form-div').children('div').find('.form-head').find('a').text("Show");

		$.ajax({
			url: module.get_value('base_url')+module.get_value('module_link')+'/get_placeholder',
			dataType: 'json',
			success: function(data) {
				$.each(data, function(index, value) {
					$('#'+index).attr('placeholder', value);
				});
			}
		});
		
	}
});