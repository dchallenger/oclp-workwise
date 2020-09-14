$(document).ready( function () {	
	$('.module-import-dialed').live('click', function () {
		$.ajax({
			url: module.get_value('base_url') + 'employee/import_dialed_hours/module_import_options',
			data: 'module_id=' + module.get_value('module_id'),
			type: 'post',
			dataType: 'json',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});  		
			},
			success: function(data){
				$.unblockUI();
				
				if(data.html != ""){
					var width = $(window).width()*.2;
					quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:100px;">'+ data.html +'</div>',
					{
						title: 'Import',
						draggable: false,
						modal: true,
						center: true,
						unloadOnHide: true,
						beforeUnload: function (){
							$('.tipsy').remove();
						}
					});
					boxyHeight(quickedit_boxy, '#boxyhtml');
				}
			}
		});
	});

	$('#import-form').die('submit');
});