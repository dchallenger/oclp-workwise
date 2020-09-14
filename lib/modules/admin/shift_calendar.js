$(document).ready(function(){
	$('#sunday_shift_id,#monday_shift_id,#tuesday_shift_id,#wednesday_shift_id,#thursday_shift_id,#friday_shift_id,#saturday_shift_id').width('65%').parent().parent().width('71%');
	$('<input type="checkbox" value="1" name="sunday_shift_id_ch"><span>Considered Rest Day</span>').insertAfter('#sunday_shift_id');
	$('<input type="checkbox" value="1" name="monday_shift_id_ch"><span>Considered Rest Day</span>').insertAfter('#monday_shift_id');
	$('<input type="checkbox" value="1" name="tuesday_shift_id_ch"><span>Considered Rest Day</span>').insertAfter('#tuesday_shift_id');
	$('<input type="checkbox" value="1" name="wednesday_shift_id_ch"><span>Considered Rest Day</span>').insertAfter('#wednesday_shift_id');
	$('<input type="checkbox" value="1" name="thursday_shift_id_ch"><span>Considered Rest Day</span>').insertAfter('#thursday_shift_id');
	$('<input type="checkbox" value="1" name="friday_shift_id_ch"><span>Considered Rest Day</span>').insertAfter('#friday_shift_id');
	$('<input type="checkbox" value="1" name="saturday_shift_id_ch"><span>Considered Rest Day</span>').insertAfter('#saturday_shift_id');

	if (module.get_value("view") == "edit") {
		if($('#record_id').val() != -1){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_shift_schedule_override',
				data: 'record_id=' + $('#record_id').val(),
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					if (response.sunday_shift_id == 1) { $('input[name="sunday_shift_id_ch"]').prop('checked', true) }
					if (response.monday_shift_id == 1) { $('input[name="monday_shift_id_ch"]').prop('checked', true) }
					if (response.tuesday_shift_id == 1) { $('input[name="tuesday_shift_id_ch"]').prop('checked', true) }
					if (response.wednesday_shift_id == 1) { $('input[name="wednesday_shift_id_ch"]').prop('checked', true) }
					if (response.thursday_shift_id == 1) { $('input[name="thursday_shift_id_ch"]').prop('checked', true) }
					if (response.friday_shift_id == 1) { $('input[name="friday_shift_id_ch"]').prop('checked', true) }
					if (response.saturday_shift_id == 1) { $('input[name="saturday_shift_id_ch"]').prop('checked', true) }
				}
			});		
		}
	}

	if (module.get_value("view") == "detail"){
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_shift_schedule_override',
			data: 'record_id=' + $('#record_id').val(),
			dataType: 'json',
			type: 'post',
			async: false,
			beforeSend: function(){
			
			},								
			success: function ( response ) {
				if (response.sunday_shift_id == 1) { $('label[for="sunday_shift_id"]').next('div').append('&nbsp;<span>(Considered Rest Day)</span>') }
				if (response.monday_shift_id == 1) { $('label[for="monday_shift_id"]').next('div').append('&nbsp;<span>(Considered Rest Day)</span>') }
				if (response.tuesday_shift_id == 1) { $('label[for="tuesday_shift_id"]').next('div').append('&nbsp;<span>(Considered Rest Day)</span>') }
				if (response.wednesday_shift_id == 1) { $('label[for="wednesday_shift_id"]').next('div').append('&nbsp;<span>(Considered Rest Day)</span>') }
				if (response.thursday_shift_id == 1) { $('label[for="thursday_shift_id"]').next('div').append('&nbsp;<span>(Considered Rest Day)</span>') }
				if (response.friday_shift_id == 1) { $('label[for="friday_shift_id"]').next('div').append('&nbsp;<span>(Considered Rest Day)</span>') }
				if (response.saturday_shift_id == 1) { $('label[for="saturday_shift_id"]').next('div').append('&nbsp;<span>(Considered Rest Day)</span>') }
			}
		});	
	}
});