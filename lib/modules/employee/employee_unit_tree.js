$(document).ready(function () {
	// var myGrid = $("#list");
	// var selRowId = myGrid.jqGrid('getGridParam','selrow');
	// myGrid.jqGrid('setRowData',selRowId,newData);
	$('#department').attr('disabled',true);
	$('#fg-288').hide();
	$('#department_id').attr('disabled',true);
	if(module.get_value('view')=='detail')
	{
		$('.form-head').eq(1).hide();
	}
	$('.form-head').eq(2).hide();
	//$('#jqgridcontainer_user_company_department.company_id').remove();
	//$('#jqgridcontainer').find('tbody').find('#1').children().remove();
});