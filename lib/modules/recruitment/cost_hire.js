$( document ).ready( function() {

    window.onload = function(){
        $(".multi-select").multiselect().multiselectfilter({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });    

        $('#department').chosen();  
        $('#division').chosen(); 
        $('#position').chosen(); 
    }
});

function generate_list(){

	$('#export-form').hasClass('export-search');
    if( ( $('#year').val() == "" )){
  
        $('#message-container').html(message_growl('error', 'Year - This field is mandatory.')); 

    }else{
	   list_search_grid( 'jqgridcontainer' );
    }
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
			position : $('#position').val(),
			year : $('#year').val()
		}, 	
	}).trigger("reloadGrid");

}


function export_list()
{
	if( ( $('#year').val() == "" )){
  
        $('#message-container').html(message_growl('error', 'Year - This field is mandatory.')); 

    }else{
        var url = module.get_value('base_url') + module.get_value('module_link') + '/export'
    	$('#export-form').attr('action', url);

	    $('#export-form').submit();
	    $('#export-form').attr('action', '');
    }
	

	return false;
}