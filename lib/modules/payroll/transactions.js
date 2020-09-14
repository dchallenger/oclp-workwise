$(document).ready(function(){
	if(module.get_value('view') == 'index'){
		setTimeout( 'init_filter();', 500);
	}
});

function filter_grid()
{
    var jqgridcontainer = 'jqgridcontainer';
    var searchfield;
    var searchop;
    var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
    
    var transaction_class_id = $('select[name="transaction_class_id"]').val();
    
    if( $("form.search-options-"+ jqgridcontainer).hasClass("hidden") ){
        searchfield = "all";
        searchop = "";
    }else{
        searchfield = $('#searchfield-'+jqgridcontainer).val();
        searchop = $('#searchop-'+jqgridcontainer).val()    
    }

    //search history
    $('#prev_search_str').val(searchstring);
    $('#prev_search_field').val(searchfield);
    $('#prev_search_option').val(searchop);
    $('#prev_search_page').val( $("#"+jqgridcontainer).jqGrid("getGridParam", "page") );

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        search: true,
        postData: {
            searchField: searchfield, 
            searchOper: searchop, 
            searchString: searchstring,
            transaction_class_id: transaction_class_id,
        },  
    }).trigger("reloadGrid");   
}

function init_filter(){
	$('select[name="transaction_class_id"]').change(filter_grid);
	$('select[name="transaction_class_id"]').chosen();
}