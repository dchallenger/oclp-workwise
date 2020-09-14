$( document ).ready( function() {

    window.onload = function(){
        $(".multi-select").multiselect().multiselectfilter({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });   

       // $('#department').chosen();  
        //$('#division').chosen(); 
        $('#position').chosen();

        init_datepick(); 


    }
});

function generate_list(){
	$('#export-form').hasClass('export-search');
	list_search_grid( 'jqgridcontainer' );
	$('#export-form').removeClass('export-search');
	return false;
}

function list_search_grid( jqgridcontainer ){
	$("#"+jqgridcontainer).jqGrid('setGridParam', { postData: null });

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {
			department : $('#department').val(),
			company : $('#company').val(),
			division : $('#division').val(),
			date_start : $('#date_start').val(),
			date_end : $('#date_end').val()
		}, 	
	}).trigger("reloadGrid");

}

function export_list()
{

	$('#export-form').attr('action', $('#export_link').val());
    // 
    $('#export-form').submit();
    $('#export-form').attr('action', '');
    
	

	return false;
}