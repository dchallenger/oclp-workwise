$( document ).ready( function() {

	$('#with_aif_no').attr('checked', true);

	window.onload = function(){
		$(".multi-select").multiselect({
			show:['blind',250],
			hide:['blind',250],
			selectedList: 1
		});
	}
	
	init_datepick();

	$('#with_aif_yes').click(function(){

		$('#with_aif_hidden').val($('#with_aif_yes').val());

	});

	$('#with_aif_no').click(function(){

		$('#with_aif_hidden').val($('#with_aif_no').val());

	});


});


function validate_form()
{	

	return true;
}

function export_list(employee_id){

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
	$('#export_employee_id').attr('value', employee_id)
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
		postData: {
			employee : $('#employee').val(),
			with_aif : $('#with_aif_hidden').val()
		}, 	
	}).trigger("reloadGrid");
}

function show_form(employee_id)
{
	var send_me = "export_employee_id="+employee_id;
	
	 $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + "/get_html",
            data: send_me,
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });         
            },
            success: function(response){
                $.unblockUI();
                
                // if(data != ""){
                    var width = $(window).width()*.7;
                    quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:600px;overflow-x:scroll;overflow-y:scroll"><span><i>*not actual size or position, because of pdf and browser difference</i></span>'+ response.html +'<br/><br/><div class="icon-label"><a href="javascript:void(0);" onclick="export_list('+employee_id+')" class="icon-16-print"><span>Print</span></a></div></div>',
                    {
                        title: 'Employee Profile View Form',
                        draggable: false,
                        modal: true,
                        center: true,
                        unloadOnHide: true,
                        beforeUnload: function (){
                            $('.tipsy').remove();
                            $('#jqgridcontainer').trigger( 'reloadGrid' );
                        }
                    });
                    boxyHeight(quickedit_boxy, '#boxyhtml');
                // }
            }
        });
}