$( document ).ready(function(){
	$('.icon-16-document-stack').die().live('click', function(){
		record_action("duplicate_record", $(this).parent().parent().parent().attr("id"), $(this).attr('module_link'));
	});

	$('input[name="legal_holiday"]').click(function () {
		enable_location($(this));
	});

	enable_location($('input[name="legal_holiday"]:checked'));
});

function enable_location(elem) {	
	//console.log(elem);
	if (elem.val() == '1') {
		$('#location_id').parents('.form-item').hide();
	} else {
		$('#location_id').parents('.form-item').show();
	}	
}

function populate_boxy(){
	 $.ajax({
		url: module.get_value('base_url') + 'admin/holiday/get_last_year',
		dataType: 'json',
		type: 'post',
		success: function (response) {
			   Boxy.ask("<center style='margin-top:30px'>Enter Year: <input type='textbox' name='year_populate' id='year_populate' value='"+response.data+"'/></center>", ["Populate!", "Cancel"],function( choice ) {
		       if(choice == "Populate!"){
		       			var send = 'year='+$('#year_populate').val();
		       			 $.ajax({
					        url: module.get_value('base_url') + 'admin/holiday/populate_year',
					        data: send,
					        dataType: 'json',
					        type: 'post',
					        beforeSend: function(){
								$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});
							},		
					        success: function (response) {
					            $.unblockUI();
					            $("#jqgridcontainer").jqGrid().trigger("reloadGrid");
					        }
					    });
		        }
		    },
		    {
		        title: "Enter Year To Populate"
		    });
		}
	});
}