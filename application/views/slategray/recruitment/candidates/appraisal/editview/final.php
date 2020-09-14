<div class="clear"></div>
<input type="text" size="2" value="0" name="final_total" readonly/>Total

<script type="text/javascript">
	$(document).ready(function () {
		$('#final_id').val(user.get_value('user_id'));
		var final_total = 0;

		$('input[name^="final"]').not('input[name="final_adapt_change"]').change(function () {
			final_total = 0;
			
			$('input[name^="final"]:checked').not('input[name="final_adapt_change"]').each(function (index, elem) {			
				final_total += parseInt($(elem).val());
			});	

			$('input[name="final_total"]').val(final_total);
		});

		$('input[name^="final"]:checked').not('input[name="final_adapt_change"]').each(function (index, elem) {			
			final_total += parseInt($(elem).val());
		});		

		$('input[name="final_total"]').val(final_total);
	});

	function check_score(param1, param2) {
		if ($('input[name="final_total"]').val() < <?=$this->config->item("MIN_APPRAISAL_SCORE")?>) {
			positions = <?=json_encode($positions);?>;
			options = $('<select name="af_pos_id"></select>');

			$.each(positions, function (index, item) {
				options.append($('<option></option>').val(index).text(item));
			});
			options.prepend('<option value="">Select&hellip;</option>');

			Boxy.ask('Applicant did not qualify. Set as Active File?'
					+'<div><strong>Applicant\'s Name</strong><br /><?php echo $firstname . " " . $last; ?></div>'
					+'<div><strong>Position Applied For</strong><br /><?php echo $position; ?></div>'
			 		+'<div><strong>Position (Recommended)</strong></div><select name="af_pos_id">' + options.html() + '</select>', 
			 	["Yes", "No", "Cancel"] ,
				function( answer ) {
					if (answer == "Yes"){
						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') +"/active_file",
							type:"POST",
							dataType: "json",
							data: 'record_id='+ module.get_value('record_id') + '&af_pos_id=' + $('select[name="af_pos_id"]').val(),
							beforeSend: function(){
								$('.jqgfirstrow').removeClass('ui-state-highlight');
								$.blockUI({
									message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>'
								});
							},
							success: function(data){								
								go_to_previous_page('');							
							}
						});
					} else if (answer == "Cancel") {
						return;
					} else {						
						ajax_save(param1, param2);
					}
				},
				{
					title: "Set Applicant as Active File"
				}
			);					
		} else {
			ajax_save(param1, param2);
		}
	}
</script>