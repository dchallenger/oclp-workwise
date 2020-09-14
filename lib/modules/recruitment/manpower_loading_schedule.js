$(document).ready(function () {
    $('a.approve-single').live('click', function () {
        change_status($(this).parent().parent().parent().attr("id"),3,
            function () {
                $('#jqgridcontainer').trigger('reloadGrid');
            }
        );
    });

    $('a.decline-single').live('click', function () {    
        var record_id = $(this).parent().parent().parent().attr("id");        
        Boxy.ask("Are you sure you want to disapprove this request?", ["Yes", "No"],function( choice ) {
            if(choice == "Yes"){
                Boxy.ask("Add Remarks: <br /> <textarea name='decline_remarks' id='decline_remarks'></textarea>", ["Send", "Cancel"],function( add ) {
                    if(add == "Send"){
                        change_status(record_id, 5, function () { $('#jqgridcontainer').trigger('reloadGrid'); }, $('#decline_remarks').val());
                    }
                },
                {
                    title: "Decline Remarks"
                });
            }
        },
        {
            title: "Decline Leave Request"
        });        
    });

    $.ajax({
        url:module.get_value('base_url') + module.get_value('module_link') + '/get_division_head',
        type: 'post',
        dataType: 'html',
        success: function(response){
            if (response != ''){
                $('#division_head_id option').remove();
                $('#division_head_id').append(response);
            }
        }
    });       
})

function change_status(record_id, form_status_id, callback, decline_remarks) {
    var data = 'record_id=' + record_id + '&form_status_id=' + form_status_id;

    if(decline_remarks){
        data += '&decline_remarks='+decline_remarks;
    }else{
        decline_remarks = false;
        data += '&decline_remarks='+decline_remarks;
    }

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
        data: data,
        type: 'post',
        dataType: 'json',
        success: function(response) {
            message_growl(response.type, response.message);
                    
            if (typeof(callback) == typeof(Function))
                callback(response);
        }
    }); 
}

function goto_detail( data )
{
    if (data.record_id > 0 && data.record_id != '') 
    {
        module.set_value('record_id', data.record_id);    
        window.location.href = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + module.get_value('record_id');         
    }
}

function backtolistview()
{
	window.location.href = module.get_value('base_url') + module.get_value('module_link');	
}

function validate_ajax_save( on_success, is_wizard , callback ){
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
	ajax_save( on_success, is_wizard , callback );
}