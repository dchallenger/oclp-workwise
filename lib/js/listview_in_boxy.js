/*
 * Author: Harold D. Ramirez
 * Desc: Default functions and actions for grids/listview in fancybox
*/
$(document).ready( function () {
	$('.jqgrow').live('dblclick', function(){
		addRelatedModule(post_fieldname, $(this).attr('id'), post_column)		
	});
							 
	$(".search-trigger").live("click", function(e) {
		$("form.search-options").toggleClass("hidden");
		$(this).parent(".search-form").toggleClass("options-open");
		e.preventDefault();                            
	});	
	
	$('#search-btn').live('click', function (){
		$(this).parent().trigger('submit');
		return false; 									 
	});	
	
	$('.search').live('submit', function(){
		search_grid( $(this).attr('jqgridcontainer') );
		return false;									
	});
});

function quick_add( module_link, field, column, fmlinkctr )
{
	var data = "record_id=-1&field_to_fill="+field+"&column_value_from="+column+"&quick_add=true&fmlinkctr="+fmlinkctr;
	showQuickEditForm( module.get_value('base_url') + module_link + "/quick_edit", data );
}

function search_grid( jqgridcontainer )
{
	var searchfield;
	var searchop;
	var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
	
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

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {searchField: searchfield, searchOper: searchop, searchString: searchstring}, 	
	}).trigger("reloadGrid");	
}

function addRelatedModule(fieldname, fieldvalue, column, container, fmlinkctr)
{
	if( column.indexOf('~') > -1 ){
		var column_lists = column.split('~');
		var column_val = new Array();
		for(var i in column_lists ){
			column_val[i] =  $("#"+container).getCell(fieldvalue, column_lists[i]);
		}
		var celldata = column_val.join(' ');
	}
	else{
		var celldata = $("#"+container).getCell(fieldvalue, column);
	}
	
	if(celldata == "false" || celldata == false) celldata = $("#"+container).getCell(fieldvalue, 0);
	
	$('input[name="'+fieldname+'"]').val(fieldvalue);
	$('input[name="'+fieldname+'"]').trigger('change');
	$('input[name="'+fieldname+'-name"]').val(celldata);
	related_module_boxy[fmlinkctr].hide().unload();
	$('.tipsy-inner').parent().remove();
}

function post_gridcomplete_function(data, container)
{
	if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));	
	$(container).jqGrid("setGridWidth", $("#body-content-wrap").width());
}