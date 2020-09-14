$(document).ready(function(){	

	$('#module_id').change( function (){
		var module_id = $(this).val();
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + "/fieldGroup_ddlb",
			type:"POST",
			data: 'module_id='+ module_id,
			dataType: "json",
			beforeSend: function(){
			
			},
			success: function(data){
				if( data.msg != "" ) $('#message-container').html(message_growl(data.msg_type, data.msg));
				if( data.fg_ddlb != "" ) $('.fieldgroup-div').html(data.fg_ddlb);
			}
		});
	});

	if ($('select[name="table"]').val() != '' && $('select[name="table"]').size() > 0 ) {		
		get_table_fields();
	}

	setTimeout(
		function () {
			console.log($('#tabindex').parents('form'));
			$('#tabindex').parents('form').append($('<input type="hidden" name="o_tabindex" />').val($('#tabindex').val()));
		},
		100
	);	

	$('select[name="table"]').die('change');
	$('select[name="table"]').live('change', get_table_fields);
	
	$('input[name="column"]').die('click');
	$('input[name="column"]').live('click', get_table_fields);

	$('select[name="column"]').live('change', function () {
		$('input[name="column-hidden"]').val($('select[name="column"]').val());

		field_types = '<div class="add-field-tmp select-input-wrap"><select name="field_type">' +
			'<option value="INT">INT</option>' +
			'<option value="VARCHAR">VARCHAR</option>' +
			'<option value="TEXT">TEXT</option>' +
			'<option value="DATE">DATE</option>' +
			'<optgroup label="NUMERIC">' +
				'<option value="TINYINT">TINYINT</option>' +
				'<option value="SMALLINT">SMALLINT</option>' +
				'<option value="MEDIUMINT">MEDIUMINT</option>' +
				'<option value="INT">INT</option>' +
				'<option value="BIGINT">BIGINT</option>' +
				'<option value="-">-</option>' +
				'<option value="DECIMAL">DECIMAL</option>' +
				'<option value="FLOAT">FLOAT</option>' +
				'<option value="DOUBLE">DOUBLE</option>' +
				'<option value="REAL">REAL</option>' +
				'<option value="-">-</option>' +
				'<option value="BIT">BIT</option>' +
				'<option value="BOOL">BOOL</option>' +
				'<option value="SERIAL">SERIAL</option>' +
			'</optgroup>' +
				'<optgroup label="DATE and TIME">' +
				'<option value="DATE">DATE</option>' +
				'<option value="DATETIME">DATETIME</option>' +
				'<option value="TIMESTAMP">TIMESTAMP</option>' +
				'<option value="TIME">TIME</option>' +
				'<option value="YEAR">YEAR</option>' +
			'</optgroup>' +
			'<optgroup label="STRING">' +
				'<option value="CHAR">CHAR</option>' +
				'<option value="VARCHAR">VARCHAR</option>' +				
				'<option value="TINYTEXT">TINYTEXT</option>' +
				'<option value="TEXT">TEXT</option>' +
				'<option value="MEDIUMTEXT">MEDIUMTEXT</option>' +
				'<option value="LONGTEXT">LONGTEXT</option>' +				
				'<option value="BINARY">BINARY</option>' +
				'<option value="VARBINARY">VARBINARY</option>' +				
				'<option value="TINYBLOB">TINYBLOB</option>' +
				'<option value="MEDIUMBLOB">MEDIUMBLOB</option>' +
				'<option value="BLOB">BLOB</option>' +
				'<option value="LONGBLOB">LONGBLOB</option>' +				
				'<option value="ENUM">ENUM</option>' +
				'<option value="SET">SET</option>' +
		'</select></div>';

		length_input = '<div class="add-field-tmp text-input-wrap"><input type="text" name="field_length" class="input-text" /></div>';
		name_input   = '<div class="add-field-tmp text-input-wrap"><input type="text" name="column_name" class="input-text" /></div>';

		// Manipulate the fields when Add new is selected.
		if ($(this).val() == 'add') {
			if ($('#field-prop').size() == 0) {
				// Append the db field definitions.
				$('label[for="column"]')
					.next()
					.after(
						$('<div id="field-prop"></div>')
							.append('<label class="add-field-tmp label-desc gray">New Column\'s Name</label>' + name_input)	
							.append('<label class="add-field-tmp label-desc gray">Type</label>' + field_types)					
							.append('<label class="add-field-tmp label-desc gray">Length</label>' + length_input)						

					);
				
				// Sometimes boxy does not show the scroll bar.
				$(this).parents('.boxy-content').css('overflow', 'scroll');
				$(this).parents('.boxy-content').css('height', '645px');				
			}
		} else {
			$('#fieldname').val($(this).val());
			$('#field-prop').remove();
		}
	});


	setTimeout(
		function () {

			if($('#module_id-name').val() == "Multiple Configuration") {
				$('#table').val("config");
				$('#column, #column-hidden').val("key");
				$('label[for="tbx_description"]').parent().show();
				$('label[for="fieldname"]').html('<label class="label-desc gray" for="fieldname">Configuration Variable Name:<span class="red font-large">*</span></label>');
			}
		},
		100
	);	

});

function get_table_fields() {
	$.ajax(
		{
			url: module.get_value('base_url') + 'admin/field/get_table_columns',
			type: 'post',
			dataType: 'json',
			data: $('select[name="table"]').serialize(),
			success: function (response) {
				if (response == 0) {
					return;
				}

				if ($('input[name="column"]').size() > 0) {
					$('input[name="column"]').remove();	
				}						
				
				if ($('select[name="column"]').size() == 0) {
					$('label[for="column"]').next().removeClass('text-input-wrap').addClass('select-input-wrap');
					$('label[for="column"]').next().append($('<select></select>').attr('name', 'column'));
				} else {
					$('select[name="column"] option').remove();
				}
				
				$('select[name="column"]').append($('<option></option>').val('').html('Select&hellip;'));

				$.each(response, function(index, field) {
					$('select[name="column"]').append($('<option></option>').val(field.name).text(field.name + ' ( ' + field.type + '[' + field.max_length +'] )'));
				});				

				$('select[name="column"]').val($('input[name="column-hidden"]').val());

				$('select[name="column"]').append($('<option></option>').val('add').html('Add new&hellip;'));
			}
		}
	);	
}