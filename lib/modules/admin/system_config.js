$( document ).ready( function() {
	if( view == 'edit' ){
		$('#uploadify-logo').uploadify({
			'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
			'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify.php',
			'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
			'folder'    : 'uploads/system',
			'fileExt'	: '*.jpg;*.gif;*.png',
			'fileDesc'    : 'Web Image Files (.JPG, .GIF, .PNG)',
			'auto'      : true,
			'scriptData': {module: "system_config", fullpath: module.get_value('fullpath'), path: "uploads/system", field:"logo"},
			'onComplete': function(event, ID, fileObj, response, data)
			{
				$('#logo').val(response);
				$('#logo-img').attr('src', module.get_value('base_url') + response);
			}
		});
	}
	
});

function validate_form()
{	
	return true;
}