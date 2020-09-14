$(document).ready(function(){ 
	
	if(module.get_value('view')=='detail')
	{

		window.onload = function (){

			get_detail_workschedule($('#record_id').val());

		}

	}

	if(module.get_value('view')=='edit')
	{

            if(module.get_value('client_no') != 2) { // i'll remove this for openaccess. it's not working. kindly verify. -jr
      		$('#date-temp').datepicker({
                  changeMonth: true,
                  changeYear: true,
                  showOtherMonths: true,
                  showButtonPanel: true,
                  showAnim: 'slideDown',
                  selectOtherMonths: true,
                  showOn: "both",
                  buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                  buttonImageOnly: true,  
                  buttonText: '',
                  maxDate: new Date(),
                  yearRange: 'c-90:c+10',
                  beforeShow: function(input, inst) {                     
                      
                  },
                  onClose: function(dateText) {

                  }
              });
            }

        $('#time_in1').datetimepicker({                            
                        changeMonth: true,
                        changeYear: true,
                        showOtherMonths: true,
                        showButtonPanel: true,
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        maxDate: new Date(),
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10'                        
                    });

            $('#time_out1').datetimepicker({                            
                        changeMonth: true,
                        changeYear: true,
                        showOtherMonths: true,
                        showButtonPanel: true,
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        maxDate: new Date(),
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10'                        
                    });

            $('#time_in2').datetimepicker({                            
                        changeMonth: true,
                        changeYear: true,
                        showOtherMonths: true,
                        showButtonPanel: true,
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        maxDate: new Date(),
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10'                        
                    });

            $('#time_out2').datetimepicker({                            
                        changeMonth: true,
                        changeYear: true,
                        showOtherMonths: true,
                        showButtonPanel: true,
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        maxDate: new Date(),
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10'                        
                    });
            
            get_sub_by_project();

	}

	$("#employee_id").live("change", function(){ 

		get_leave_and_other_application();

	});

	$("#date-temp").live("change", function(){ 

		get_leave_and_other_application();

	});

});


function get_leave_and_other_application(){

	if( $("#date-temp").val() != "" && $("#employee_id").val() > 0 ){

		var td = $("#date-temp").val();
		var arr_date = td.split('/');
		var month = arr_date[0];
		var day = arr_date[1];
		var year = arr_date[2];
		var ndate = year + '-' + month + '-' + day	

		$.ajax({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/get_leave_and_other_application',
	        data: 'employee_id='+$("#employee_id").val() + '&date='+ndate ,
	        dataType: 'html',
	        type: 'post',
	        async: false,
	        beforeSend: function(){
	        
	        },                              
	        success: function ( html ) 
	        {
	        	if( html != "" ){

	        		$('.other_application').html(html);

	        	}
	        	else{

	        		$('.other_application').empty();

	        	}

	        }
	    }); 

	}
	else{

		$('.other_application').empty();

	}

}


function get_detail_workschedule( record_id ){

	if( record_id > 0 ){

		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_detail_workschedule',
			data: 'record_id=' + record_id ,
			dataType: 'json',
			type: 'post',
			async: false,							
			success: function (response) {

				$('label[for="work_shift"]').next().html(response.shift_schedule);

			}
		}); 

	}

}

function get_sub_by_project() {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_sub_by_project',
            data: 'project_hr=' + user.get_value('user_id'),
            type: 'post',
            dataType: 'json',
            success: function (data) {
                  if (data.is_projecthr == true) {
                        $('select[name="employee_id"]').html(data.subordinates);
                        $('select[name="employee_id"]').trigger("liszt:updated");         
                  
                  };
             
            }
        });  
}