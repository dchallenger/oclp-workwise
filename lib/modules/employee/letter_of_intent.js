$(document).ready(function(){
	
	window.onload = function(){

		init_filter_tabs();

        if( module.get_value('view') == 'edit' ){

            if( user.get_value('post_control') == 0 ){

                $('select[name="employee_id"]').find('option').each(function(){

                    if( $(this).val() != user.get_value('user_id') ){

                        $(this).attr('disabled','disabled');

                    }

                });


                $('select[name="employee_id"]').trigger("liszt:updated");

            }

        }

	}

    $('a.approve-single').live('click', function () {   

        change_status($(this).parent().parent().parent().attr("id"),3,
            function () {
                $('#jqgridcontainer').trigger('reloadGrid');
            }
            );       
    });
    
    $('a.decline-single').live('click', function () {        
        var record_id = $(this).parent().parent().parent().attr("id");
        Boxy.ask("Are you sure you want to decline this request?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
              change_status(record_id, 4, function () { $('#jqgridcontainer').trigger('reloadGrid'); });
            }
        },
        {
            title: "Decline Request"
        });        
    });

    function change_status(record_id, form_status_id, callback) {
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
        data: 'record_id=' + record_id + '&form_status_id=' + form_status_id,
        type: 'post',
        dataType: 'json',
        success: function(response) {
            message_growl(response.type, response.message);
            response.record_id = record_id;
            if (typeof(callback) == typeof(Function))
                callback(response);

            if(response.type == 'success' && response.num_rows > 0 ){

                $('#for_approval span').html(response.num_rows);

            }
            else if(response.type == 'success' && response.num_rows <= 0 ){

               $('#for_approval span').hide();

            }

            
        }
    }); 
}

	function init_filter_tabs(){
	    $('ul#grid-filter li').click(function(){

	    //	alert('hey');

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
                filter: filter
            },  
        }).trigger("reloadGrid");   
    }




});