$( document ).ready( function() {
	window.onload = function(){
		$(".multi-select").multiselect({
			show:['blind',250],
			hide:['blind',250],
			selectedList: 1
		});
	}
	
	init_datepick();

    $('#category').live('change',function(){     
        var category_id = $(this).val();
        var category = $("#category option:selected").data("alias");
        var category_for_id = $("#category option:selected").data("aliasid");

        if (category_id > 0){
            var eleid = category_for_id.toLowerCase()   

            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/populate_category',
                data: 'category_id=' + category_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                    $('#multi-select-loader2').html('');                    
                    $('#multi-select-main-container2').hide();

                    $('#multi-select-main-container').hide();
                    $('#multi-select-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                },                              
                success: function ( response ) {
                    $('#multi-select-loader').html('');                    
                    $('#multi-select-main-container').show();
                    $('#category_selected').html(category + ':');
                    $('#multi-select-container').html(response);
                    $('#'+eleid).multiselect().multiselectfilter({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });
                }
            });

            if (category_id != 7) {
                $('#'+eleid).bind("multiselectclose", function(event, ui){
                     var selected = $(this).val();

                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employees',
                        data: 'category_id=' + selected + '&category='+category_id,
                        dataType: 'html',
                        type: 'post',
                        async: false,
                        beforeSend: function(){
                            $('#multi-select-main-container2').hide();
                            $('#multi-select-loader2').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                        },                              
                        success: function ( response ) {
                            $('#multi-select-loader2').html('');                    
                            $('#multi-select-main-container2').show();
                            $('#category_selected2').html('Employee:');
                            $('#multi-select-container2').html(response);
                            $('#employee').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });
                        }
                    });
                        
                }); 
                
            }else{
                $('#multi-select-loader2').html('');                    
                $('#multi-select-main-container2').hide();
            }
            // $('#employment_status_container').show();
            // $('#employee_type_container').show();
        }
        else{
            $('#multi-select-main-container').hide();
            $('#category_selected').html('');
            $('#multi-select-container').html('');          
        }   
    }); 

	$("#date_start").change(function(){
		$('#date_from').val($('#date_start').val());
	});

	$("#date_end").change(function(){
		$('#date_to').val($('#date_end').val());
	});

});


function validate_form()
{	

	return true;
}

function export_list(){

	if( ( $('#date_start').val() == "" && $('#date_end').val() != "" ) ||  ( $('#date_start').val() != "" && $('#date_end').val() == "" )  ){
		$('#message-container').html(message_growl('error', 'Invalid Date Period'));
		return false;
	}

	var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
    var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
    if (sortColumnName != ''){
        $('#previous_page').append('<input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord">');
    }

	$('#export-form').attr('action', $('#export_link').val());
	$('#export-form').submit();
	$('#export-form').attr('action', '');
	return false;
}

function generate_list(){

	if( ( $('#date_start').val() == "" && $('#date_end').val() != "" ) ||  ( $('#date_start').val() != "" && $('#date_end').val() == "" )  ){

		$('#message-container').html(message_growl('error', 'Invalid Date Period'));
		return false;

	}

	$('#export-form').hasClass('export-search');
	list_search_grid( 'jqgridcontainer' );
	$('#export-form').removeClass('export-search');
	return false;

}

function list_search_grid( jqgridcontainer ){
	$("#jqgridcontainer").jqGrid('clearGridData', {	clearfooter: true});
	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		postData: null
	});

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {
            employee : $('#employee').val(),  		
			leaveType : $('#leave_type').val(),
			leaveStatus : $('#leave_status').val(),
			dateStart : $('#date_start').val(),
			dateEnd : $('#date_end').val(),
		}, 	
	}).trigger("reloadGrid");
}