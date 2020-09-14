$( document ).ready( function() {

	window.onload = function(){
        $(".multi-select").multiselect({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });
    }

    $('#category').live('change',function(){
        var items = {1 : "Company",2 : "Division",3 : "Department",4 : "Employee",5 : "Project",6 : "Group"};
        var category_id = $(this).val();
        var category = items[category_id];

        var eleid = category.toLowerCase()

        if (category_id > 0){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_category_record',
                data: 'category_id=' + category_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
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
        }
        else{
            $('#multi-select-main-container').hide();
            $('#category_selected').html('');
            $('#multi-select-container').html('');          
        }   
    }); 


});

function export_list(){

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

	$('#export-form').hasClass('export-search');
	list_search_grid( 'jqgridcontainer' );
	$('#export-form').removeClass('export-search');
	return false;

}

function list_search_grid( jqgridcontainer ){

	$("#jqgridcontainer").jqGrid('clearGridData', {	clearfooter: true});
	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {
			category : $('#category').val(),
			department : $('#department').val(),
            company : $('#company').val(),
            division : $('#division').val(),
            employee : $('#employee').val(),
            project : $('#project').val(),  
            group : $('#group').val()
		}, 	
	}).trigger("reloadGrid");
}