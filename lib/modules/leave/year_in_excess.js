$(document).ready(function(){
	$( 'select[name="year_id"]' ).change( function(){
		list_search_grid( 'jqgridcontainer' );
	});

    $('.module-export-employees').live('click', function () {
        $('#search_hidden').val($('#search').val());
        $('#export-form').attr('action', $('#export_link').val());
        $('#export-form').submit();
        $('#export-form').attr('action', '');   
    });

});

function list_search_grid( jqgridcontainer ){
	
    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        datatype: 'json',
        postData: {
            year_id : $('#year_id').val(),
        },  
    }).trigger("reloadGrid");

}