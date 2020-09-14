$(document).ready(function() {

	if(user.get_value('view') == 'edit')
	{	

		if(user.get_value('post_control') == 1)
			employee_within($.trim($('#department').val()), $.trim($('#campaign').val()), $('#record_id').val());

		$('#department').live('change', function(){
			var data = 'department_id=' + ($.trim($('#department').val()) != '' ? $('#department').val() : -1);
			data += '&campaign=' + ($.trim($('#campaign').val()) != '' ? $('#campaign').val() : -1);

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/employee_within',
				data: data,
				dataType: 'html',
				type: 'post',
				success: function ( response ) {
					$('#multiselect-employee_id option').remove();
					$('#multiselect-employee_id').append(response);
					$('#multiselect-employee_id').multiselect("refresh");
				}
			});
		});

		$('#campaign').live('change', function(){
			var data = 'department_id=' + ($.trim($('#department').val()) != '' ? $('#department').val() : -1);
			data += '&campaign=' + ($.trim($('#campaign').val()) != '' ? $('#campaign').val() : -1);

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/employee_within',
				data: data,
				dataType: 'html',
				type: 'post',
				success: function ( response ) {
					$('#multiselect-employee_id option').remove();
					$('#multiselect-employee_id').append(response);
					$('#multiselect-employee_id').multiselect("refresh");
				}
			});
		});

		if(user.get_value('post_control') != 1) {
			fixed_get_campaign();
			no_post_employee_dropdown();
		}

	}

	if(user.get_value('view') == 'detail')
	{
		var calendar = $('#calendar').fullCalendar({

			header: {
				left: 'prev,next',
				center: 'title',
				right: 'today'
			},

		    disableDragging: true,

		    eventSources: [
		        {
		            url: module.get_value('base_url')+module.get_value('module_link')+'/get_source_event/'+$('#record_id').val(), // use the `url` property
		            textColor: 'white'
		        }
		    ]
		});

		var data = $('#record_id').serialize();
	
		$.ajax({
			url: module.get_value('base_url')+module.get_value('module_link')+'/get_focus_calendar',
			data: data,
			type: 'post',
			dataType: 'json',
			success: function(response)
			{
				calendar.fullCalendar( 'gotoDate', response.f_year , response.f_month,  response.f_day  );
			}
		});
	}
	
	if(user.get_value('view') == 'edit')
	{
		onedit_emp_added();
		
		get_shift_list();

		var _get_calendar = new p_calendar();

		var calendar = $('#calendar').fullCalendar({

			header: {
				left: 'prev,next',
				center: 'title',
				right: 'today'
			},

			aspectRatio: 2,

			selectHelper: true,

			selectable: true,

			select: function(start, end, allDay, view)
			{
				if(_get_calendar.is_overlapping($('#date_emp_added').val(), start, end)) {
					Boxy.alert('There is already a specified shift for that date, delete or change the overlapping');
				} else if(_get_calendar.is_above_current(start, end)) {
					Boxy.alert('Changing of workschedule before current day is not allowed');
				} else {
					Boxy.ask("Shift : <br /><select id='shift' name='shift' style='width:50%'>"+$('#shift_list').val()+'</select>', ["Save", "Cancel"],function( choice ) {
			        	if(choice == "Save")
			        	{
							calendar.fullCalendar('renderEvent', 
							{
								title: $('#shift option:selected').text(),
								start: start,
								end: end,
								prev_start: new Date(start),
								prev_end: new Date(end),
								allDay: allDay,
								shift: $('#shift').val()

							}, 
							true ); // to make the event stick
							
							var formatted_dates = _get_calendar.sql_format_date(start, end);

							$('#date_emp_added').val($('#date_emp_added').val()+formatted_dates['start']+'/'+formatted_dates['end']+'='+$('#shift').val()+',');

					   }
			        },
			        {
			            title: "Specify Shift"
			        });
				}

				calendar.fullCalendar('unselect');

			},

			eventClick: function(event, element) {
				if(!event.holiday_event)
				{
					Boxy.ask("Shift : <br /><select id='shift' name='shift' style='width:50%'>"+$('#shift_list').val()+'</select>', ["Save", "Delete", "Cancel"],function( choice ) {
			        	if(choice == "Save") {
			        		var new_shift = $('#shift').val();
			        		var old_shift = event.shift;
			        		var end = (event.end == null ? event.start : event.end);
			        		var formatted_dates =  _get_calendar.sql_format_date(event.start, end);

							event.title = $('#shift option:selected').text();
							event.shift = $('#shift').val();

							$('#date_emp_added').val($('#date_emp_added').val().replace(formatted_dates['start']+'/'+formatted_dates['end']+'='+old_shift,' '));
							$('#date_emp_added').val($('#date_emp_added').val()+formatted_dates['start']+'/'+formatted_dates['end']+'='+new_shift+',');

							$('#calendar').fullCalendar('updateEvent', event);
					    } else if(choice == "Delete"){

					    	var end = (event.end == null ? event.start : event.end);
							var formatted_dates =  _get_calendar.sql_format_date(event.start, end);				    	

					    	$('#date_emp_added').val($('#date_emp_added').val().replace(formatted_dates['start']+'/'+formatted_dates['end']+'='+event.shift,' '));
					    	$('#date_emp_deleted').val($('#date_emp_deleted').val()+formatted_dates['start']+'/'+formatted_dates['end']+',');
							$('#calendar').fullCalendar('removeEvents',event._id);    	
					    }

			        },
			        {
			            title: "Specify Shift"
			       	});
				} else {
					Boxy.alert(event.full_title);
				}
		    },

		    disableDragging: true,

		    editable: true,

		    // eventDragStart: function(event,jsEvent,ui,view){
		    // 	var s = event.start;
		    // 	var e = (event.end == null ? event.start : event.end);

		    // 	var prev_start = (!event.prev_start ? event.start : event.prev_start);
		    // 	var prev_end = (!event.prev_end ? event.end : event.prev_end);

		    // 	$('#date_emp_deleted').val($('#date_emp_deleted').val()+prev_start.getFullYear()+'-'+(prev_start.getMonth()+1)+'-'+prev_start.getDate()+'/'+prev_end.getFullYear()+'-'+(prev_end.getMonth()+1)+'-'+prev_end.getDate()+',');

		    // 	$('#date_emp_added').val($('#date_emp_added').val().replace(s.getFullYear()+'-'+(s.getMonth()+1)+'-'+s.getDate()+'/'+e.getFullYear()+'-'+(e.getMonth()+1)+'-'+e.getDate()+'='+event.shift,' '));
		    // },

		    // eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
		    // 	var s = event.start;
		    // 	var e = (event.end == null ? event.start : event.end);
		    // 	$('#date_emp_added').val($('#date_emp_added').val()+s.getFullYear()+'-'+(s.getMonth()+1)+'-'+s.getDate()+'/'+e.getFullYear()+'-'+(e.getMonth()+1)+'-'+e.getDate()+'='+event.shift+',');
		    // },

		    disableResizing: true,

		    eventSources: [
		        {
		            url: module.get_value('base_url')+module.get_value('module_link')+'/get_source_event/'+$('#record_id').val(), // use the `url` property
		            textColor: 'white'
		            
		        }
		    ]
		});

		var data = $('#record_id').serialize();
	
		$.ajax({
			url: module.get_value('base_url')+module.get_value('module_link')+'/get_focus_calendar',
			data: data,
			type: 'post',
			dataType: 'json',
			success: function(response)
			{
				calendar.fullCalendar( 'gotoDate', response.f_year , response.f_month,  response.f_day  );
			}
		});

	}
});


function get_shift_list()
{
	$.ajax({
		url : module.get_value('base_url')+module.get_value('module_link')+'/get_shift_listing',
		dataType : 'json',
		success : function(response)
		{
			$('#shift_list').val(response.data);
		}
	});
}

function onedit_emp_added()
{
	if($('#record_id').val() != -1)
	{
		$.ajax({
			url: module.get_value('base_url')+module.get_value('module_link')+'/onedit_emp_added',
			data:  'record_id='+$('#record_id').val(),
			type: 'post',
			dataType: 'json',
			success: function(response)
			{
				$('#date_emp_added').val(response.data);
			}
		});
	}
}

function list_emp(ws_id = false)
{
	if(ws_id)
	{
		$.ajax({
			url: module.get_value('base_url')+module.get_value('module_link')+'/list_employee_affected',
			data:  'workschedule_calendar_id='+ws_id,
			type: 'post',
			dataType: 'json',
			success: function(response)
			{
				Boxy.alert(response.data);
			}
		});
	}
}

function employee_within(department_id = '', campaign = '', record_id = -1)
{
	if(record_id != -1)
	{
		var data = 'department_id=' + ($.trim(department_id) != '' ? department_id : false);
		data += '&campaign=' + ($.trim(campaign) != '' ? campaign : false);

		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/employee_within',
			data: data,
			dataType: 'html',
			type: 'post',
			success: function ( response ) {
				$('#multiselect-employee_id option').remove();
				$('#multiselect-employee_id').append(response);
				$('#multiselect-employee_id').multiselect("refresh");
				remain_selected();
			}
		});
	}
}

function no_post_employee_dropdown()
{
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/no_post_employee_dropdown',
		dataType: 'html',
		success: function ( response ) {
			$('#multiselect-employee_id option').remove();
			$('#multiselect-employee_id').append(response);
			$('#multiselect-employee_id').multiselect("refresh");
			remain_selected();
		}
	});
}

function remain_selected()
{
	var data = $('#record_id').serialize();

	if($('#record_id').val() != -1)
	{
		$.ajax({
			url: module.get_value('base_url')+module.get_value('module_link')+'/remain_selected',
			data: data,
			type: 'post',
			dataType: 'json',
			success: function(response)
			{
				$('#multiselect-employee_id').val(response.ids.split(','));
				$('#multiselect-employee_id').multiselect("refresh");
			}
		});
	}
}

function fixed_get_campaign()
{
	$.ajax({
		url: module.get_value('base_url')+module.get_value('module_link')+'/fixed_get_campaign',
		dataType: 'json',
		success: function(response)
		{
			$('#campaign_chzn').replaceWith('<span>'+response.campaign+'</span>');
			$('#campaign').val(response.campaign_id);
		}
	});
}