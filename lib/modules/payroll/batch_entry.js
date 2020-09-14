$(document).ready(function(){
	if(module.get_value('view') == "edit"){
		if(module.get_value('record_id') != "-1"){
			zebra();
			$('input[name="amount[]"]').each(function(){
				var value =  $(this).val();
				value = addCommas(value);
				$(this).addClass('text-right');
				$(this).val(value);
				$(this).keyup( maskFloat );
			});
		}

		$('select[name="temp-employee_id"]').chosen({allow_single_deselect: true });
		$('select[name="temp-employee_id"]').change(function(){
			var employee_ids = $(this).val();
			var employee_id = '';
			var new_row = '';
			var unit_rate = $('#unit_rate_main').val();
			unit_rate = addCommas(unit_rate);
			
			for( var i in employee_ids ){
				employee_id = employee_ids[i];
				new_row = '';
				if(employee_id == 'all'){
					for(var employee_id in empdata){
						if( $('#employee_row-'+employee_id).length == 0 ){
							new_row = new_row + '<tr id="employee_row-'+employee_id+'">';
							new_row = new_row + '<td align="right">'+empdata[employee_id].id_no+'</td>';
							new_row = new_row + '<td><input type="hidden" value="'+employee_id+'" name="employee_id[]">'+empdata[employee_id].fullname+'</td>';
							new_row = new_row + '<td align="center"><input type="text" value="1" name="quantity[]" style="width: 80%; text-align: right" class="input-text"></td>';
							new_row = new_row + '<td align="center"><input type="text" value="'+unit_rate+'" name="unit_rate[]" style="width: 80%; text-align: right" class="input-text"></td>';
							new_row = new_row + '<td align="center"><input type="text" value="'+unit_rate+'" name="amount[]" style="width: 80%; text-align: right" class="input-text" readonly></td>';
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
					new_row = new_row + '<td align="center"><input type="text" value="1" name="quantity[]" style="width: 80%; text-align: right" class="input-text"></td>';
					new_row = new_row + '<td align="center"><input type="text" value="'+unit_rate+'" name="unit_rate[]" style="width: 80%; text-align: right" class="input-text"></td>';
					new_row = new_row + '<td align="center"><input type="text" value="'+unit_rate+'" name="amount[]" style="width: 80%; text-align: right" class="input-text" readonly></td>';
					new_row = new_row + '<td align="center"><span class="icon-group"><a href="javascript:delete_employee_row('+employee_id+')" tooltip="Delete" class="icon-button icon-16-delete"></a></span></td>';
					new_row = new_row + '</tr>';

					$('select[name="temp-employee_id"] option[value='+employee_id+']').remove();	
				}

				$('#employee-list').prepend(new_row);
			}

			$('select[name="temp-employee_id"]').trigger("liszt:updated");

			ddlb_controller();

			$('input[name="quantity[]"]').each(function(){
				$(this).keyup( maskFloat );
			});
			$('input[name="unit_rate[]"]').each(function(){
				$(this).keyup( maskFloat );
			});

			zebra();
		});

		$('input[name="unit_rate[]"], input[name="quantity[]"], input[name="amount[]"]').live('keyup', function(){
			calculate_amount($(this));
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

function calculate_amount( row ){
	var quantity = row.parent().parent().find('input[name="quantity[]"]').val();
	var unit_rate = row.parent().parent().find('input[name="unit_rate[]"]').val();
	if(quantity != "" && unit_rate != ""){
		quantity = remove_commas( quantity );
		unit_rate = remove_commas( unit_rate );
		var amount = parseFloat( quantity ) * parseFloat( unit_rate );
		row.parent().parent().find('input[name="amount[]"]').val( addCommas( amount.toFixed(2) ) );
	}
	else{
		row.parent().parent().find('input[name="amount[]"]').val('');	
	}
}