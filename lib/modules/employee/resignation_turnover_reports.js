$(document).ready(function(){
	init_datepick();
    $(".multi-select").multiselect({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1
    });
    // update_chart();
    $("#company").multiselect({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1,
        close:function(event, ui){
            var selectedOptions = $.map($('#company :selected'),
                   function(e) { return $(e).val(); } );
            var div_id_delimited1 = selectedOptions.join(',');
            if (div_id_delimited1)
            {
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_division',
                    data: 'div_id_delimited=' + div_id_delimited1,
                    dataType: 'html',
                    type: 'post',
                    async: false,
                    beforeSend: function(){
                    
                    },                              
                    success: function ( response ) 
                    {
                    	$('#company_srch').val(div_id_delimited1);
                        $('#multi-select-main-container1').show();
                        $('#multi-select-container1').html(response);
                        $('#division').multiselect().multiselect({
                            show:['blind',250],
                            hide:['blind',250],
                            selectedList: 1
                        });                            
                        $("#division").multiselect({
				            show:['blind',250],
				            hide:['blind',250],
				            selectedList: 1,
				            close:function(event, ui){
				                var selectedOptions = $.map($('#division :selected'),
				                       function(e) { return $(e).val(); } );
				                var div_id_delimited2 = selectedOptions.join(',');
				                if (div_id_delimited2)
				                {
				                    $.ajax({
				                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_department',
				                        data: 'div_id_delimited=' + div_id_delimited2,
				                        dataType: 'html',
				                        type: 'post',
				                        async: false,
				                        beforeSend: function(){
				                        
				                        },                              
				                        success: function ( response ) 
				                        {
				                        	$('#division_srch').val(div_id_delimited2);
				                            $('#multi-select-main-container2').show();
				                            $('#multi-select-container2').html(response);
				                            $('#department').multiselect().multiselect({
				                                show:['blind',250],
				                                hide:['blind',250],
				                                selectedList: 1
				                            }); 
				                        }
				                    });                    
				                }
				            }
				        });  
                    }
                });                    
            }
        }
    })

	$("#division").multiselect({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1,
        close:function(event, ui){
            var selectedOptions = $.map($('#division :selected'),
                   function(e) { return $(e).val(); } );
            var div_id_delimited2 = selectedOptions.join(',');
            if (div_id_delimited2){
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_department',
                    data: 'div_id_delimited=' + div_id_delimited,
                    dataType: 'html',
                    type: 'post',
                    async: false,
                    beforeSend: function(){
                    
                    },                              
                    success: function ( response ) 
                    {
                    	$('#division_srch').val(div_id_delimited2);
                        $('#multi-select-main-container2').show();
                        $('#multi-select-container2').html(response);
                        $('#department').multiselect().multiselect({
                            show:['blind',250],
                            hide:['blind',250],
                            selectedList: 1
                        });     
                    }
                });                    
            }
        }
    });   

	$('#date_start').live('change', function (){
		if($('#date_start').val() != '' && $('#date_end').val() != '')
		{
			date_validation($('#date_start').val(),$('#date_end').val());
		}
	});
	$('#date_end').live('change', function (){
		if($('#date_start').val() != '' && $('#date_end').val() != '')
		{
			date_validation($('#date_start').val(),$('#date_end').val());
		}
	});
});


function export_list()
{
	var ok_to_save = 1;
	if($('#date_start').val() == '' || $('#date_end').val() == '')
	{
		 message_growl("error","Date Period is Mandatory!");
		ok_to_save = 0;
	}
	else
	{
		if(!date_validation($('#date_start').val(),$('#date_end').val()))
		{
			ok_to_save = 0;
		}
	}
    if(ok_to_save)
    {
		$('#export-form').attr('action', $('#export_link').val());

		Boxy.ask("<div style='margin-left:45px'>Type of Report : <select style='margin-top:20px' name='export_type' id='export_type' style='width:75%'><option value='1'>Turn-over Report</option><option value='2'>Turn-over Rate</option></select></div>", ["Export", "Cancel"],function( choice ) {
		if(choice == "Export"){
				var data = "company="+$('#company').val();				
				if(isset($('#division').val()))
				{
					data = data+"&division="+$('#division').val();
				}
				if(isset($('#department').val()))
				{
					data = data+"&department="+$('#department').val();
				}
				data = data+"&date_start="+$('#date_start').val()+"&date_end="+$('#date_end').val();
				if($('#export_type').val() == 1)
				{
					var url = module.get_value('base_url')+module.get_value('module_link')+"/resigned_list";
				}
				else 
				{
					var url = module.get_value('base_url')+module.get_value('module_link')+"/turnover_report";
				}

				$.ajax({
					url: url,
			        data: data,
			        dataType: 'json',
			        type: 'post',
			        beforeSend: function(){
						$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});
					},		
			        success: function (response) {
			            var path = "/"+response.data;
		                window.location = module.get_value('base_url')+path;
		                $.unblockUI();
			        }
				});
				
		    }
		},
		{
		    title: "Export"
		});


		$('#export-form').attr('action', '');
		
		return false;
	}
}

function validate_form()
{
	//errors
	if(error.length > 0){
		var error_str = "Please correct the following errors:<br/><br/>";
		for(var i in error){
			if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
			error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
		}
		$('#message-container').html(message_growl('error', error_str));
			
		//reset errors
		error = new Array();
		error_ctr = 0
		return false;
	}
	
	//no error occurred
	return true;
}

function generate_list()
{
	var ok_to_save = 1;
	if($('#date_start').val() == '' || $('#date_end').val() == '')
	{
		 message_growl("error","Date Period is Mandatory!");
		ok_to_save = 0;
	}
	else
	{
		if(!date_validation($('#date_start').val(),$('#date_end').val()))
		{
			ok_to_save = 0;
		}
	}

	if( ok_to_save )
	{
		$('#export-form').hasClass('export-search');
		list_search_grid( 'jqgridcontainer' );
		$('#export-form').removeClass('export-search');
		return false;
	}
}

function list_search_grid( jqgridcontainer )
{
	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		//search: true,
		postData: {
			company : $('#company').val(),
			division : $('#division').val(),
			department : $('#department').val(),
			date_start : $('#date_start').val(),
			date_end : $('#date_end').val()
		}, 	
	}).trigger("reloadGrid");

}

function date_validation(date_from,date_to)
{
	parse_date_from = date_from;
	parse_date_to   = date_to;

	if (isNaN(parseFloat(date_from))) {
		parse_date_from = date_from + ' 1';
	}

	if (isNaN(parseFloat(date_to))) {
		parse_date_to = date_to + ' 1';
	} 

	if (parseFloat(parse_date_from) > parseFloat(parse_date_to)) 
	{
		 message_growl("error","Invalid Date Range!\nStart Date cannot be after End Date!");
	}
	else
	{
		return 1;
	}
}

function isset () 
{
    var a = arguments,
        l = a.length,        i = 0,
        undef; 
    if (l === 0) 
    {
        throw new Error('Empty isset');    
    }
 
    while (i !== l) 
    {
        if (a[i] === undef || a[i] === null) 
        {
            return false;        
        }
        i++;
    }
    return true;
}