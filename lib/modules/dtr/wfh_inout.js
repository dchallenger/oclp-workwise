$(document).ready(function(){
	$('#digiclock').jdigiclock({
        clockImagesPath : module.get_value('base_url')+'lib/jclock/images/clock/',
        weatherImagesPath : module.get_value('base_url')+'lib/jclock/images/weather/',
        am_pm : true
    });
});

function time_in_out(type)
{
	var data = "type="+type;
	$.ajax({
		url : module.get_value('base_url')+module.get_value('module_link')+'/time_in_out',
		type : "post",
		data : data,
		beforeSend : function(){
			show_saving_blockui();
		},
		success: function()
		{
			$.unblockUI();
			go_to_previous_page("Time In/Out is done");
		}
	});
}