$( document ).ready( function() {

    window.onload = function(){
        $(".multi-select").multiselect().multiselectfilter({
          show:['blind',250],
          hide:['blind',250],
          selectedList: 1
        });

        $("#company_id").bind("multiselectclose", function(event, ui){
        	var company_id 	  	= $(this).val();
         	var division_id   	= $('#division_id').val();
         	var department_id 	= $('#department_id').val();

         	get_employees(company_id, division_id, department_id);
       });

       $('#division_id').bind("multiselectclose", function(event, ui){
        	var division_id  	= $(this).val();
         	var company_id   	= $('#company_id').val();
         	var department_id 	= $('#department_id').val();

        	get_employees(company_id, division_id, department_id);
       });

      $('#department_id').bind("multiselectclose", function(event, ui){
        	var department_id	= $(this).val();
        	var division_id   	= $('#division_id').val();
         	var company_id 	= $('#company_id').val();

        	get_employees(company_id, division_id, department_id);
      });

      $("#jqgridcontainer").jqGrid('setGroupHeaders', {
            useColSpanStyle: false, 
            groupHeaders:[
              {startColumnName: 'employee_name', numberOfColumns: 6, titleText: '<span style="font-weight:bold;">Employee Information</span>'}
            ]
      }).trigger("reloadGrid");
    }
});

function generate_list(){

	$('#export-form').hasClass('export-search');
    list_search_grid( 'jqgridcontainer' );
	$('#export-form').removeClass('export-search');
	return false;
}

function list_search_grid( jqgridcontainer ){
	$("#"+jqgridcontainer).jqGrid('setGridParam', { postData: null });

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {
			department_id : $('#department_id').val(),
			company_id : $('#company_id').val(),
			division_id : $('#division_id').val(),
			employee_id : $('#employee_id').val()
		}, 	
	}).trigger("reloadGrid");

}

function get_employees(company, division, department){
	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employees',
        type: 'post',
        dataType: 'json',
        data: 'company_id=' + company + '&division_id='+division + '&department_id=' + department,
        beforeSend:function() {
        },
        success: function (response) {

        	$('#employee_id option').remove();
        	if (response !== null) {
				$('#employee_id').append(response.result);

                if (response.employees !== '') {
                    $.each(response.employees, function(index, values){
                        $('#employee_id option[value="' + values + '"]').attr('selected','selected');
                    });
                };
        	};
        	$('#employee_id').multiselect("refresh");

        }
    });
}


function export_list()
{
	if( ( $('#year').val() == "" )){
  
        $('#message-container').html(message_growl('error', 'Year - This field is mandatory.')); 

    }else{
        var url = module.get_value('base_url') + module.get_value('module_link') + '/export'
    	$('#export-form').attr('action', url);

	    $('#export-form').submit();
	    $('#export-form').attr('action', '');
    }
	

	return false;
}