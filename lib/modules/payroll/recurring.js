$(document).ready(function(){
	if( module.get_value('view') == 'edit' ){
		if(module.get_value('record_id') != "-1"){
			zebra();
		}

		$('select[name="temp-employee_id"]').chosen({allow_single_deselect: true});
		$('select[name="temp-employee_id"]').change(function(){
			var employee_ids = $(this).val();
			var employee_id = '';
			var name = '';
			var mainamount = $('#mainamount').val();
			var new_row = '';
			
			for( var i in employee_ids ){
				employee_id = employee_ids[i];
				name = $('select[name="temp-employee_id"] option[value='+employee_id+']').html();
				new_row = '';
				if(employee_id == 'all'){
					for(var employee_id in empdata){
						if( $('#employee_row-'+employee_id).length == 0 ){
							new_row = new_row + '<tr id="employee_row-'+employee_id+'">';
							new_row = new_row + '<td align="right">'+empdata[employee_id].id_no+'</td>';
							new_row = new_row + '<td><input type="hidden" value="'+employee_id+'" name="employee_id[]">'+empdata[employee_id].fullname+'</td>';
							new_row = new_row + '<td align="center"><input type="text" value="'+mainamount+'" name="amount[]" style="width: 80%; text-align: right" class="input-text"></td>';
							new_row = new_row + '<td align="center"><span class="icon-group"><a href="javascript:delete_employee_row('+employee_id+')" tooltip="Delete" class="icon-button icon-16-delete"></a></span></td>';
							new_row = new_row + '</tr>';

							$('select[name="temp-employee_id"] option[value='+employee_id+']').remove();
						}	
					}
				}
				else{
					new_row = new_row + '<tr id="employee_row-'+employee_id+'">';
					new_row = new_row + '<td align="right">'+empdata[employee_id].id_no+'</td>';
					new_row = new_row + '<td><input type="hidden" value="'+employee_id+'" name="employee_id[]">'+empdata[employee_id].fullname+'</td>';
					new_row = new_row + '<td align="center"><input type="text" value="'+mainamount+'" name="amount[]" style="width: 80%; text-align: right" class="input-text"></td>';
					new_row = new_row + '<td align="center"><span class="icon-group"><a href="javascript:delete_employee_row('+employee_id+')" tooltip="Delete" class="icon-button icon-16-delete"></a></span></td>';
					new_row = new_row + '</tr>';

					$('select[name="temp-employee_id"] option[value='+employee_id+']').remove();	
				}

				$('#employee-list').prepend(new_row);
			}

			$('select[name="temp-employee_id"]').trigger("liszt:updated");

			ddlb_controller();

			$('input[name="amount[]"]').each(function(){
				$(this).keyup( maskFloat );
			});
			zebra();
		});

		ddlb_controller();
	}
});

function ddlb_controller(){
	$('.chzn-container .chzn-results .group-result').css('cursor', 'pointer');
	$('.chzn-container .chzn-results .group-result').click(function(){
		var department = $(this).html();
		department = decodeEntities(department);
		$('#temp-employee_id optgroup[label="'+department+'"] option').each(function(){
			$(this).attr('selected', true);
		});

		$('select[name="temp-employee_id"]').trigger("change");
	});
}

function delete_employee_row( employee_id ){
	$('select[name="temp-employee_id"] optgroup[label="'+empdata[employee_id].department+'"]').append('<option value="'+employee_id+'">'+empdata[employee_id].fullname+'</option>');
	$('select[name="temp-employee_id"]').trigger("liszt:updated");
	$('#employee_row-'+employee_id).remove();
	ddlb_controller();
	zebra();
}

function zebra(){
	var ctr = 0;
	var trclass = '';
	$('#employee-list tr').each(function(){
		$(this).removeClass('odd');
		$(this).removeClass('even');

		if( ctr % 2 == 0 ){
			trclass = 'even';
		}
		else{
			trclass = 'odd';
		}

		$(this).addClass(trclass);
		ctr++;
	});
}