$(document).ready(function () {


	
    if(module.get_value('view') == "index"){
        $('.module-export_2').live('click', function()
        {
            //window.open(module.get_value('base_url')+module.get_value('module_link')+'/export_list', "Export SQL Script");
            var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');            
		    var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
		    var searchfield;
			var searchop;
			var searchstring = $('.search-jqgridcontainer').val() != "Search..." ? $('.search-jqgridcontainer').val() : "";
			var companyList = $('#company_list').val();

			if( $("form.search-options-jqgridcontainer").hasClass("hidden") )
			{
				searchfield = "all";
				searchop = "";
			}
			else
			{
				searchfield = $('#searchfield-jqgridcontainer').val();
				searchop = $('#searchop-jqgridcontainer').val()	
			}

	        $('#previous_page').append('<input id="company" type="hidden" value="'+ companyList +'" name="company"><input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord"><input id="search_str" type="hidden" value="'+ searchstring +'" name="search_str"><input id="search_field" type="hidden" value="'+ searchfield +'" name="search_field"><input id="search_option" type="hidden" value="'+ searchop +'" name="search_option"><input id="search_page" type="hidden" value="'+ $("#jqgridcontainer").jqGrid("getGridParam", "page") +'" name="search_page">');
			$('#export-form').attr('action', module.get_value('base_url')+module.get_value('module_link')+'/export_list');
			$('#export-form').submit();
			$('#export-form').attr('action', '');
			return false;
        });
    }
});

function generate_list(){


	ok_to_save = true;

	if( ok_to_save ){
		$('#export-form').hasClass('export-search');
		list_search_grid( 'jqgridcontainer' );
		$('#export-form').removeClass('export-search');
		return false;
	}
}

function list_search_grid( jqgridcontainer ){

	$("#jqgridcontainer").jqGrid('setGridParam', 
	{
		//search: true,
		postData: {
			company : $('#company_list').val()
		}, 	
	}).trigger("reloadGrid");

}