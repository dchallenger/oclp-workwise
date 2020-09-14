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
	swfobject.embedSWF(module.get_value('base_url')+"lib/ofc2/swf/open-flash-chart.swf", "gender_chart",
		"100%", "200", "9.0.0", "expressInstall.swf",
		{"data-file":module.get_value('base_url') + module.get_value('module_link') + "/genderData"},
		{"wmode" : "transparent"}
	);
	swfobject.embedSWF(module.get_value('base_url')+"lib/ofc2/swf/open-flash-chart.swf", "age_chart",
		"100%", "200", "9.0.0", "expressInstall.swf",
		{"data-file":module.get_value('base_url') + module.get_value('module_link') + "/ageData"},
		{"wmode" : "transparent"}
	);	
	swfobject.embedSWF(module.get_value('base_url')+"lib/ofc2/swf/open-flash-chart.swf", "position_levels_chart",
		"100%", "200", "9.0.0", "expressInstall.swf",
		{"data-file":module.get_value('base_url') + module.get_value('module_link') + "/positionData"},
		{"wmode" : "transparent"}
	);
	swfobject.embedSWF(module.get_value('base_url')+"lib/ofc2/swf/open-flash-chart.swf", "tenure_chart",
		"100%", "200", "9.0.0", "expressInstall.swf",
		{"data-file":module.get_value('base_url') + module.get_value('module_link') + "/tenureData"},
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
				//gender
				chartGender = data.genderData;				
				$("#emp_count").html(data.emp_count);
                for(i in data.gender_statistics)
                {
                    $('#'+i+'_'+'probationary_count').text(data.gender_statistics[i]['probationary_count']);
                    $('#'+i+'_'+'regular_count').text(data.gender_statistics[i]['regular_count']);
                    $('#'+i+'_'+'consultant_count').text(data.gender_statistics[i]['consultant_count']);
                    $('#'+i+'_'+'project_employee_count').text(data.gender_statistics[i]['project_employee_count']);
                    $('#'+i+'_'+'contractual_direct_count').text(data.gender_statistics[i]['contractual_direct_count']);
                    $('#'+i+'_'+'contractual_agent_count').text(data.gender_statistics[i]['contractual_agent_count']);
                    $('#'+i+'_'+'ojt_count').text(data.gender_statistics[i]['ojt_count']);
                    var sum_total = parseFloat(data.gender_statistics[i]['probationary_count'])+parseFloat(data.gender_statistics[i]['regular_count'])+parseFloat(data.gender_statistics[i]['consultant_count'])+parseFloat(data.gender_statistics[i]['project_employee_count'])+parseFloat(data.gender_statistics[i]['contractual_direct_count'])+parseFloat(data.gender_statistics[i]['contractual_agent_count'])+parseFloat(data.gender_statistics[i]['ojt_count']);
                    var sum_percentage = (sum_total/parseFloat(data.emp_count))*100;
                    $('#'+i+'_group_count').text(sum_total);
                    $('#'+i+'_group_percentage').text(sum_percentage.toFixed(2)+'%');
                }

                //age
                chartAge = data.ageData;
                for(i in data.age_labels){
					$("#_"+i).html(data.age_labels[i]);
				}

				//position level
				chartPosition = data.positionData;
				$('#position_type_table').html('');
				var position_table = '';
				for(i in data.position_statistics)
				{
					var sum_total = parseFloat(data.position_statistics[i]['probationary_count'])+parseFloat(data.position_statistics[i]['regular_count']);
                    var sum_percentage = (sum_total/parseFloat(data.emp_count2))*100;
					position_table = position_table+'<td>';
	                position_table = position_table+'<nobr>';
					position_table = position_table+'<span class="font-large" id="_'+i+'">';
	                position_table = position_table+'<span id="'+i+'_group_percentage">';
					position_table = position_table+sum_percentage.toFixed(2)+'%';
					position_table = position_table+'</span>';
	      			position_table = position_table+' (<b><span id="'+i+'_group_count">'+sum_total+'</span></b>)';
					position_table = position_table+'</span>';
	                position_table = position_table+'<br/>';
	                position_table = position_table+'<span class="gray">Regular : ( <span id="'+i+'_regular_count">'+data.position_statistics[i]['regular_count']+'</span> )</span>';
	                 position_table = position_table+'<br />';
	                position_table = position_table+'<span class="gray">Probationary : ( <span id="'+i+'_probationary_count">'+data.position_statistics[i]['probationary_count']+'</span> )</span>';
	                 position_table = position_table+'<br />';
	                position_table = position_table+'<span class="gray">Consultant : ( <span id="'+i+'_consultant_count">'+data.position_statistics[i]['consultant_count']+'</span> )</span>';
	                 position_table = position_table+'<br />';
	                position_table = position_table+'<span class="gray">Project Employee : ( <span id="'+i+'_project_employee_count">'+data.position_statistics[i]['project_employee_count']+'</span> )</span>';
	                 position_table = position_table+'<br />';
	                position_table = position_table+'<span class="gray">Contractual (Direct Hired) : ( <span id="'+i+'_contractual_direct_count">'+data.position_statistics[i]['contractual_direct_count']+'</span> )</span>';
	                 position_table = position_table+'<br />';
	                position_table = position_table+'<span class="gray">Contractual (Agency Hired) : ( <span id="'+i+'_contractual_agent_count">'+data.position_statistics[i]['contractual_agent_count']+'</span> )</span>';
	                position_table = position_table+'<br />';
	                position_table = position_table+'<span class="gray">On-the-Job Training : ( <span id="'+i+'_ojt_count">'+data.position_statistics[i]['ojt_count']+'</span> )</span>';
	                position_table = position_table+'<br/>';
			        position_table = position_table+'<span class="gray"><b>Total '+ucwords(strtolower(i))+'</b></span>';
	                position_table = position_table+'</nobr>';
					position_table = position_table+'</td>';
				}
				$('#position_type_table').append(position_table);

				//tenure
				chartTenure = data.tenureData;
				for(i in data.tenure_labels){
					$("#_"+i).html(data.tenure_labels[i]);
				}

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
	//gender
	var gender = findSWF("gender_chart");
	
	if(typeof gender.load=='function'){
		try{
			var g = JSON.parse(chartGender);
			gender.load( JSON.stringify(g) );
		}catch(e){
			gender.load( JSON.stringify(chartGender) );
		}
	}

	//age
	var age = findSWF("age_chart");
	
	if(typeof age.load=='function'){
		try{
			var a = JSON.parse(chartAge);
			age.load( JSON.stringify(a) );
		}catch(e){
			age.load( JSON.stringify(chartAge) );
		}
	}

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

	//tenure
	var tenure = findSWF("tenure_chart");
	
	if(typeof tenure.load=='function'){
		try{
			var t = JSON.parse(chartTenure);
			tenure.load( JSON.stringify(t) );
		}catch(e){
			tenure.load( JSON.stringify(chartTenure) );
		}
	}
}

function date_validation(date_from,date_to)
{
	parse_date_from = date_from;
	parse_date_to   = date_to;

	if (isNaN(Date.parse(date_from))) {
		parse_date_from = date_from + ' 1';
	}

	if (isNaN(Date.parse(date_to))) {
		parse_date_to = date_to + ' 1';
	} 

	if (Date.parse(parse_date_from) > Date.parse(parse_date_to)) 
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
