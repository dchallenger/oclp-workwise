$(document).ready(function () {
    // Unbind the default method for clicking on a listview row.
    $('.jqgrow').die('dblclick');
    $('.icon-16-info').die('click');
    
    // Define module specific event.
    $('.jqgrow').live('dblclick', function () {
        window.location = module.get_value('base_url') + 'recruitment/candidates/index/' + $(this).attr('id');
    });
    
    $('a.icon-button.search-candidates').live('click', function () {
        window.location = module.get_value('base_url') + 'recruitment/candidates/index/' + $(this).parents('tr').attr('id');
    });
    
    $('.icon-16-info').live('click', function(){
	record_action("detail", $(this).parent().parent().parent().attr("id"), 'recruitment/manpower');
    });    
});