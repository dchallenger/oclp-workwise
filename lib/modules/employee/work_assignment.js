$(document).ready(function(){
	$('#employee_id').live('change',function(){
		var employee_id = $(this).val();
		var search_val = false;
		if (employee_id != ''){
			var search_val = true;
		}
	    $('#jqgridcontainer').jqGrid('setGridParam', 
	    {
	    	datatype: 'json',
	    	page: 1,
	        search: search_val,
	        postData: {
	        	searchOper:'eq',
	        	searchField:'employee_work_assignment.employee_id',
	        	searchString:employee_id,
	            employee_id: $('select[name="employee_id"]').val(),
	        },  
	    }).trigger("reloadGrid"); 		
	});
			
	if (module.get_value('view') == 'edit'){

	}
});