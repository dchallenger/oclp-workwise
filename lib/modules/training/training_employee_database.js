$( document ).ready( function() {

	if( module.get_value('view') == 'index' ){

		window.onload = function(){

			$('#searchfield-jqgridcontainer').find('option[value="training_employee_database.employee_id"]').each(function(){

				if( $(this).text() == "Position" ){

					$(this).val('training_employee_database.employee_id1');

				}
				else if( $(this).text() == "Department" ){

					$(this).val('training_employee_database.employee_id2');

				}

			});
		}

		$('.icon-16-export').live('click',function(){

			export_list($(this));

		});

		$('.icon-16-document-stack').live('click',function(){

			print_certificate($(this));

		});



	}

	if( module.get_value('view') == 'detail' ){

		window.onload = function(){

			calculate_total_training_cost();

		}

	}

});

function init_filter_tabs(){
    $('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
        $(this).addClass('active');
        $('#filter').val( $(this).attr('filter') );

        filter_grid( 'jqgridcontainer', $(this).attr('filter') );
    });
}

function filter_grid( jqgridcontainer, filter )
{
    var searchfield;
    var searchop;
    var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
    var filter;

    if( $("form.search-options-"+ jqgridcontainer).hasClass("hidden") ){
        searchfield = "all";
        searchop = "";
    }else{
        searchfield = $('#searchfield-'+jqgridcontainer).val();
        searchop = $('#searchop-'+jqgridcontainer).val()    
    }

    $('#grid-filter').find('li').each(function(){
    	if( $(this).hasClass('active') ){
    		filter = $(this).attr('filter');
    	}
    });

    //search history
    $('#prev_search_str').val(searchstring);
    $('#prev_search_field').val(searchfield);
    $('#prev_search_option').val(searchop);
    $('#prev_search_page').val( $("#jqgridcontainer").jqGrid("getGridParam", "page") );
    $('#filter').val(filter);

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        search: true,
        postData: {
            searchField: searchfield, 
            searchOper: searchop, 
            searchString: searchstring,
            filter: filter
        },  
    }).trigger("reloadGrid");   
}

function export_list(object)
{

	var searchfield;
    var searchop;
    var searchstring = $('.search-jqgridcontainer' ).val() != "Search..." ? $('.search-jqgridcontainer' ).val() : "";
    var filter;

    if( $("form.search-options-jqgridcontainer").hasClass("hidden") ){
        searchfield = "all";
        searchop = "";
    }else{
        searchfield = $('#searchfield-jqgridcontainer').val();
        searchop = $('#searchop-jqgridcontainer').val()    
    }

    $('#grid-filter').find('li').each(function(){
    	if( $(this).hasClass('active') ){
    		filter = $(this).attr('filter');
    	}
    });

    var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
    var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');

    if (sortColumnName != ''){
    	$('#sidx').val(sortColumnName);
    	$('#sord').val(sortOrder);
    }

    //search history
    $('#prev_search_str').val(searchstring);
    $('#prev_search_field').val(searchfield);
    $('#prev_search_option').val(searchop);
    $('#prev_search_page').val( $("#jqgridcontainer").jqGrid("getGridParam", "page") );
    $('#filter').val(filter);


	$('#record-form').attr('action', module.get_value('base_url') + module.get_value('module_link') + '/excel_export/');
	$('#record-form').submit();
	$('#record-form').attr('action', '');
	return false;
}

function print_certificate(object)
{
	var record_id = object.parents('tr').attr('id');
	$('#record-form').attr('action', module.get_value('base_url') + module.get_value('module_link') + '/print_service_bond/'+ record_id +'');
	$('#record-form').submit();
	$('#record-form').attr('action', '');
	return false;
}

function calculate_total_training_cost(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/calculate_total_training_cost';
    var data = $('#record-form').serialize();

	$.ajax({
	    url: url,
	    dataType: 'json',
	    type:"POST",
	    data: data,
	    success: function (response) {

	    	$('label[for="employee_id"]').each(function(){

	    		if($(this).text() == "Total Training Cost:"){

	    			$(this).parent().find('div').text(response.total_training_cost);

	    		}

	    		if($(this).text() == "Total Running Balance:"){

	    			$(this).parent().find('div').html(response.total_running_balance);

	    		}

	    	});

	    }
	});


}