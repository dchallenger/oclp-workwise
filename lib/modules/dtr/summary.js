$(document).ready(function () {
	$('.icon-16-info').die().live('click', function () {
		var basicInfo = false;
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_summary_details',
			data: 'record_id=' + $(this).parents('tr').attr('id'),
			type: 'post',
			dataType: 'json',
			success: function (response) {
				if (response.success == 1) {
					basicInfo = new Boxy($('<div id="boxyhtml"/></div>').html(response.html),
					{
						modal: true,
						center: true,
						title: "Period Summary",
						unloadOnHide: true,
						beforeUnload: function(){ $('.tipsy').remove(); }
					});
										
					basicInfo.center();					
				}
			}
		});
	});
});