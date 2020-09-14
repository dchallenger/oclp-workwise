$(document).ready(function () {
	//var datedetail = new Date(Date.Parse());
	//alert($.trim($('label[for=\"date\"]').siblings('.text-input-wrap').text()));
	var datedetail=$.datepicker.formatDate('yy-mm-dd', new Date($.trim($('label[for=\"date\"]').siblings('.text-input-wrap').text())));
	//$.datepicker.parseDate("DD, MM dd, yy",  "Sunday, February 28, 2010");
	//alert($.datepicker.formatDate( "MM/dd/yy", $.trim($('label[for=\"date\"]').siblings('.text-input-wrap').text())));
	//alert(module.get_value('record_id') + datedetail);
	//user
	
	$('#employee_id').change(function () {
		 var emp_id=$('#employee_id').val();
		 workshift(emp_id, datedetail);
		 regularworkshift(emp_id);
	});

	if(module.get_value('view')=='detail'){
		var sample=$('.text-input-wrap a').attr("onclick");
		var pieces=sample.split("'");
		var emp_id=pieces[1];
		workshift(emp_id, datedetail);
		regularworkshift(emp_id);
	}

	if(module.get_value('view')=='edit'){
		var emp_id=$('#employee_id').val();
		workshift(emp_id, datedetail);
		regularworkshift(emp_id);
	}

	//$(".ui-datepicker-trigger").attr("src",module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.PNG'");
	//alert(user.get_value('user_theme'));
	var sample=module.get_value('base_url') + user.get_value('user_theme')+"/";
	$(".ui-datepicker-trigger").attr("src",sample+"icons/clock32.png");
	$(".ui-datepicker-trigger").attr("width","16");
	$(".ui-datepicker-trigger").attr("height","16");
	
	//http://localhost/hdi.resource/themes/slategray/icons/calendar-month.png
	//http://localhost/hdi.resource/themes/slategray/icons/chart-pie.png
});

	function workshift(emp_id, date){
		var send='employee_id='+emp_id+'&date='+date;
		$.ajax({
   		    url: module.get_value('base_url') + 'employee/employee_dtr/get_workshift',
	        data: send,
	        dataType: 'json',
	        type: 'post',
	        success: function (response) {
	            if (response.msg_type == 'no') {
	                //$('#message-container').html(message_growl(response.msg_type, response.msg));
	                $('label[for="work_shift"]').siblings('.text-input-wrap').text(response.msg);
	            } else {
	                employee = response.data;
	                for(var i in employee){
                        //alert(employee[i].name);
                        //alert(employee[i].name);
                        var date=$.datepicker.formatDate('M d, yy', new Date(employee[i].effectivity_date));
                        $('label[for="work_shift"]').siblings('.text-input-wrap').text(employee[i].name+" effective ("+date+")");
                    }
	            }
	        }
	    });
	}

	function regularworkshift(emp_id){
		var send='employee_id='+emp_id;
		$.ajax({
   		    url: module.get_value('base_url') + 'employee/employee_dtr/regularworkshift',
	        data: send,
	        dataType: 'json',
	        type: 'post',
	        success: function (response) {
	            if (response.msg_type == 'error') {
	                //$('#message-container').html(message_growl(response.msg_type, response.msg));
	                $('label[for="regular_work_shift"]').siblings('.text-input-wrap').text(response.msg);
	            } else {
	                employee = response.data;
	                for(var i in employee){
                        $('label[for="regular_work_shift"]').siblings('.text-input-wrap').text(employee[i].name);
                    }
	            }
	        }
	    });
	}

