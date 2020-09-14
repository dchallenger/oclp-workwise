$(document).ready(function () {
	// get_ecf_members();
	get_ecf_serialize();
	remove_opt();
	var dateT=$.datepicker.formatDate('mm/dd/yy', new Date($.now()));
	$('#ecf_date-temp').val(dateT);
    $('#ecf_date').val(dateT);

	$('#total_amount').val('50');
	$('#total_count').attr('readonly', true);

	$('#employee_id').change(function () {
		 call_dependents(this);
         get_ecf_members($(this).val());
	});

    // $('input["name:ecf_fire"]').change(function () {
    //     house_dependent();
    // });
    if(module.get_value('view') == "index"){
        $('.icon-16-export').live('click', function(){
            var record_id = $(this).attr('record_id');
            window.location = module.get_value('base_url')+module.get_value('module_link')+'/export_list/'+record_id;
        });
    }
    if(module.get_value('view') == "edit") {
        $('.form-head').after('<input type="radio" name="ecf_fire" value = "1" checked="checked">Death &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="ecf_fire" value = "2"/>Fire<br /><br/>');
    }

    if(module.get_value('view') == 'detail') {
        $('.icon-label, .or').remove();
    }

    $('#ecf_house_dependent').attr('readonly', true);

    $('input[name="ecf_fire"]').live('change', function(){
        if($('input[name="ecf_fire"]:checked').val() == 1) {
            $('label[for="ecf_affected_dependents"]').parent().show();
            $('label[for="ecf_house_dependent"]').parent().hide();
        } else {
            $('label[for="ecf_affected_dependents"]').parent().hide();
            $('label[for="ecf_house_dependent"]').parent().show();
        }
    });

    $('.icon-16-listback').live('click',function(){
        window.location = module.get_value('base_url')+module.get_value('module_link');
    });

    $('.cancel').live('click', function(){
        window.location = module.get_value('base_url')+module.get_value('module_link');
    });
});

function call_dependents(emp_id){
remove_opt();
 $.ajax({
        url: module.get_value('base_url') + 'employee/employee_ecf/call_dependents',
        data: $(emp_id).serialize(),
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
                $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                house = response.house;
                employee = response.data;
                $('label[for="ecf_affected_dependents"]').siblings('.select-input-wrap').replaceWith('<div class="select-input-wrap"><select id="ecf_affected_dependents" name="ecf_affected_dependents" class="chosen" multiple="true"></select></div>');
                for(var i in employee){
					$('#ecf_affected_dependents').append('<option value="'+employee[i].record_id+'">'+employee[i].name+'</option>');
                }
				jQuery(".chosen").chosen();
                if(house)
                    if(house.address_ecf_dependent == 1)
                        $('#ecf_house_dependent').val(house.pres_address1+' '+house.pres_address2+' '+house.present_city+' '+house.pres_province+', '+house.pres_zipcode);
                    if(house.address_ecf_dependent == 2)
                        $('#ecf_house_dependent').val(house.perm_address1+' '+house.perm_address2+' '+house.permanent_city+' '+house.pres_province+', '+house.perm_zipcode);
            }
        }
    });
}

function get_ecf_members(emp_id){
 $.ajax({
        url: module.get_value('base_url') + 'employee/employee_ecf/get_ecf_members',
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
                $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                $('#contributors').val('');
                var members=0;
                for(var i in employee){
                	members = members+1;
                    if(emp_id != employee[i].employee_id)
                        $('#contributors').val(employee[i].employee_id+', '+$('#contributors').val());
                }
                
                $('#total_count').val(members-1);
            }
        }
    });
}

function get_ecf_serialize(){
 $.ajax({
        url: module.get_value('base_url') + 'employee/employee_ecf/get_ecf_serialize',
        dataType: 'json',
        type: 'post',
        success: function (responde) {
            if (responde.msg_type == 'error') {
                $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = responde.data;
                $('#affected_employee').val(responde);                
            }
        }
    });
}

function remove_opt()
{
	$('#ecf_affected_dependents').find('option').remove();
	$('.chzn-drop:last').find('.chzn-results').find('li').remove();
}