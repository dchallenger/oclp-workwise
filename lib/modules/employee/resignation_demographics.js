var chartGender = chartAge = chartPosition = chartTenure = {};
$(document).ready(function () 
{
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

	//create chart
	swfobject.embedSWF(module.get_value('base_url')+"lib/ofc2/swf/open-flash-chart.swf", "position_levels_chart",
		"100%", "200", "9.0.0", "expressInstall.swf",
		{"data-file":module.get_value('base_url') + module.get_value('module_link') + "/positionData"},
		{"wmode" : "transparent"}
	);
});

function update_chart()
{
	if(date_validation($('#date_start').val(),$('#date_end').val()))
	{
		var data = $('#demographics-form').serialize();
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/generateAll',
			type:"POST",
			data: data,
			dataType: "json",
			async: false,
			success: function(data)
			{	
				//position level
				chartPosition = data.positionData;
				$('#position_type_table').html('');				
				var position_table = '';
				position_table = position_table+'<td>';
                position_table = position_table+'<nobr>';
				position_table = position_table+'<span class="font-large">';
                position_table = position_table+'<span id="whole_count">';
				position_table = position_table+parseFloat(data.emp_count2);
				position_table = position_table+'</span>';				
                position_table = position_table+'</span>';
                position_table = position_table+'<br />';
                position_table = position_table+'<span class="gray">Total Resigned</span>';
                position_table = position_table+'<br/>';
                position_table = position_table+'</nobr>';
				position_table = position_table+'</td>';
				for(i in data.position_statistics)
				{
					
					if(data.emp_count2==0)
					{
						var group_count2 = 0;
						var sum_percentage = 0;	
					}
					else
					{
						var group_count2 = parseFloat(data.position_statistics[i]['resigned_count']);
						var sum_percentage = (group_count2*1/parseFloat(data.emp_count2)*100);	
					}					
					position_table = position_table+'<td>';
	                position_table = position_table+'<nobr>';
					position_table = position_table+'<span class="font-large">';
	                position_table = position_table+'<span id="group_percentage">';
					position_table = position_table+sum_percentage.toFixed(2)+'%';
					position_table = position_table+'</span>';
	      			position_table = position_table+' (<b><span id="group_count">'+group_count2+'</span></b>)';
					position_table = position_table+'</span>';
	                position_table = position_table+'<br />';
	                position_table = position_table+'<span class="gray">'+data.position_statistics[i]['resigned_date']+'</span>';
	                position_table = position_table+'<br/>';
	                position_table = position_table+'</nobr>';
					position_table = position_table+'</td>';
				}
				$('#position_type_table').append(position_table);

				showChart();
				$("#trigger").css('display','');
				$("#loading_content").css('display','none');
				return false;
			}
		});
	}
}

function findSWF(movieName)
{
	if (navigator.appName.indexOf("Microsoft")!= -1)
	  return window["ie_" + movieName];
	else
	  return document[movieName];
}

function showChart()
{
	//position
	var position = findSWF("position_levels_chart");
	
	if(typeof position.load=='function'){
		try{
			var p = JSON.parse(chartPosition);
			position.load( JSON.stringify(p) );
		}catch(e){
			position.load( JSON.stringify(chartPosition) );
		}
	}
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
		 message_growl("error","Invalid Date Range!\nStart Date cannot be after End Date!")
	}
	else
	{
		return 1;
	}
}

function strtolower (str) 
{
  	return (str + '').toLowerCase();
}


function ucwords (str) 
{
  	return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
    	return $1.toUpperCase();
  	});
}
