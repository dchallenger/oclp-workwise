$( document ).ready( function() {

    window.onload = function(){
        $(".multi-select").multiselect().multiselectfilter({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });   

        // $('#department').chosen();  
        // $('#benefit').chosen(); 
        $('#category').live('change', function(){
        	var selected = $(this).val();
       
        	if (selected == 'company') {
        		$('#company').parents('.form-item').removeClass('hide');
        		$('#benefit').parents('.form-item').addClass('hide');
        		$('#benefit').val('');
        	}else if(selected == 'benefit'){
        		$('#benefit').parents('.form-item').removeClass('hide');
        		$('#company').parents('.form-item').addClass('hide');
        		$('#company').val('');
        	}
        }); 
    
    $('.multi-select-company').bind("multiselectclose", function(event, ui){
	    var selected = $(this).val();
	    
	   // $.ajax({
	   //      url: module.get_value('base_url') + module.get_value('module_link') + '/get_benefits',
	   //      type: 'post',
	   //      dataType: 'html',
	   //      data: 'company=' + selected,
	   //      beforeSend:function() {
	   //      },
	   //      success: function (response) {
	   //      	$('#benefit option').remove();
	   //      	if (response !== null) {
				// 	$('#benefit').append(response);
	   //      	};
	   //  		$('#benefit').chosen().trigger("liszt:updated");
	        	
		//     }
		// });
		   		
	});	




    }
});

function generate_list(){ 

	$('#export-form').hasClass('export-search');
    if( ( $('#year').val() == "" )){
  
        $('#message-container').html(message_growl('error', 'Year - This field is mandatory.')); 

    }else{
	   list_search_grid( 'jqgridcontainer' );
    }
	$('#export-form').removeClass('export-search');
	return false;
}

function list_search_grid( jqgridcontainer ){
	$("#"+jqgridcontainer).jqGrid('setGridParam', { postData: null });
	var benefit = $('#benefit').parents('.form-item');
	var company = $('#company').parents('.form-item');

	var benefit_val = $('#benefit').val();
	var company_val = $('#company').val();
	
	if (benefit.hasClass('hide')) {
		benefit_val = '';
	};

	if (company.hasClass('hide')) {
		company_val = '';
	};

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {
			company : company_val,
			benefit : benefit_val
		}, 	
	}).trigger("reloadGrid");

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