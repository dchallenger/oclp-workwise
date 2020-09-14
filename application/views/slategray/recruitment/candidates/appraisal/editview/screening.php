<?php
	$mrf_id = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $this->input->post('candidate_id')))->row();
?>
<input type="hidden" value="<?= $mrf_id->mrf_id; ?>" id="mrf_id" />
<div class="clear"></div>
<input type="text" size="2" value="0" name="screener_total" readonly/>Total

<script type="text/javascript">
	$(document).ready(function () {
		/*
		$('.icon-16-disk-back').live('click', function(){
			window.location = module.get_value('base_url') + "recruitment/candidates/index/" + $('#mrf_id').val();
		});
*/
		$('.icon-16-listback').live('click', function(){
			window.location = module.get_value('base_url') + "recruitment/candidates/index/" + $('#mrf_id').val();
		});
		$('a[rel="action-back"]').live('click', function(){
			window.location = module.get_value('base_url') + "recruitment/candidates/index/" + $('#mrf_id').val();
		});

		$('#screener_id').val(user.get_value('user_id'));			

		$('#record-form').append($('<input type="hidden" name="final_interviewer_id" />').val($('select[name="final_interviewer_id"]').val()));
		$('#record-form').append($('<input type="hidden" name="final_datetime" />').val($('input[name="final_datetime"]').val()));

		$('select[name="final_interviewer_id"]').change(function () {
			$('input[name="final_interviewer_id"]').val($(this).val());
		});

		$('input[name="final_datetime"]').change(function () {
			$('input[name="final_datetime"]').val($(this).val());
		});		

		var screener_total = 0;

		$('input[name^="screener"]').not('input[name="screener_adapt_change"]').change(function () {
			screener_total = 0;
			
			$('input[name^="screener"]:checked').not('input[name="screener_adapt_change"]').each(function (index, elem) {			
				screener_total += parseInt($(elem).val());
			});	

			$('input[name="screener_total"]').val(screener_total);
			
			if (screener_total >= <?=$this->config->item("MIN_APPRAISAL_SCORE")?>) {
				$('select[name="final_interviewer_id"], #final_datetime').removeAttr('disabled');
			} else {
				$('select[name="final_interviewer_id"], #final_datetime').attr('disabled', 'disabled');
			}
		});

		$('input[name^="screener"]:checked').not('input[name="screener_adapt_change"]').each(function (index, elem) {			
			screener_total += parseInt($(elem).val());
		});		

		$('input[name="screener_total"]').val(screener_total);

		if (screener_total >= <?=$this->config->item("MIN_APPRAISAL_SCORE")?>) {
			$('select[name="final_interviewer_id"],  #final_datetime').removeAttr('disabled');
		}
			$('#final_datetime').datetimepicker({                            
                        changeMonth: true,
                        changeYear: true,
                        showOtherMonths: true,
                        showButtonPanel: true,
                        showAnim: 'slideDown',
                        selectOtherMonths: true,
                        showOn: "both",
                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                        buttonImageOnly: true,  
                        buttonText: '',                    
                        hourGrid: 4,
                        minuteGrid: 10,
                        timeFormat: 'hh:mm tt',
                        ampm: true,
                        yearRange: 'c-90:c+10'                        
                    });		

		$('select[name="final_interviewer_id"],  #final_datetime').attr('disabled', 'disabled');

        if ($('#screening_datetime').val() == "") {
        	setTimeout(
            	function () { $('#screening_datetime').datetimepicker('setDate', new Date());},
            	100
        		);
        }
	});

	function check_score(param1, param2) {

		if ($('input[name="screener_total"]').val() < <?=$this->config->item("MIN_APPRAISAL_SCORE")?>) {
			positions = <?=json_encode($positions);?>;
			options = $('<select name="af_pos_id"></select>');

			$.each(positions, function (index, item) {
				options.append($('<option></option>').val(index).text(item));
			});
			options.prepend('<option value="">Select&hellip;</option>');

			Boxy.ask('Applicant did not qualify. Set as Active File?'
					+'<div><strong>Applicant\'s Name</strong><br /><?php echo $firstname . " " . $last; ?></div>'
					+'<div><strong>Position Applied For</strong><br /><?php echo $position; ?></div>'
			 		+'<div><strong>Position (Recommended)</strong></div><select name="af_pos_id" style="width:300px">' + options.html() + '</select><br /><br />', 
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
								//go_to_previous_page('');	
								window.location = module.get_value('base_url') + "recruitment/candidates/index/" + $('#mrf_id').val();						
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