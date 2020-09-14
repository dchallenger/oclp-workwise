$(document).ready(function () {
    // Unbind the default method for clicking on a listview row.
    $('.icon-16-info').live('click', function(){
	record_action("detail", $(this).parent().parent().parent().attr("id"), 'recruitment/manpower');
    });    
});