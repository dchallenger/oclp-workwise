$(document).ready(function () {
	$('.jqgrow').die('dblclick');		
	
	$('.jqgrow').live('dblclick', function(){
		$('#record_id').val($(this).attr('id'));
		$('#record-form').attr('action', module.get_value('base_url') + 'admin/module/detail/');
		$('#record-form').submit();
	});
		
	$('.icon-16-add-listview').die('click');
	
	$('.icon-16-add-listview').live('click', function(){
		$('#record-form').append('<input type="hidden" name="parent_id" value="'+ $(this).attr('parent') +' " />');
		record_action("edit", -1, $(this).attr('module_link'), $(this).attr('related_field'), $(this).attr('related_field_value'));
	});
	
});