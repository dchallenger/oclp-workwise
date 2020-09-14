<div class="spacer"></div>

<div id="form-div">                        
	<div class="" id="fg-143" fg_id="143">
		<h3 class="form-head">Applicant Information</h3>

	<div class="spacer"></div>

	<div class="col-2-form">
		<div class="form-item odd">
			<label class="label-desc gray">Name:</label>
			<div class="select-input-wrap"><?php echo $lastname . ', ' . $firstname . ' ' . $middlename; ?></div>
		</div>
		<div class="form-item even">
			<label class="label-desc gray">Date and Time:</label>
			<div class="text-input-wrap"><input type="text" class="input-text datetimepicker" name="screening_datetime" id="screening_datetime" /></div>
		</div>		
		<div class="form-item odd">
			<label class="label-desc gray">Position Applying For:</label>
			<div class="text-input-wrap"><?php echo $position; ?></div>
		</div>
		<div class="form-item even">
			<label class="label-desc gray">Interviewer:</label>
			<?php if (!is_null($position_hierarchy)):?>
				<div class="select-input-wrap"><?=form_dropdown('interviewer_id', $position_hierarchy);?></div>
			<?php else:?>
				<span class="red">No interviewer.</span>
			<?php endif;?>			
		</div>			
	</div>


	<div class="spacer"></div>
	<button id="endorse">Endorse for Job Offer</button>
	<div class="spacer"></div>
	<span class="small">
		(This button above will forward current application to Job Offer status.)</span>
	<div class="spacer"></div>
	<div class="spacer"></div>

</div>

<?php if ($this->_interview_status_id == $candidate_status_id):?>

	<div id="form-div">                        
		<div class="" id="fg-143" fg_id="143">
			<h3 class="form-head">Final Interview</h3>

		<div class="col-2-form">
			<div class="form-item odd">
				<label class="label-desc gray" for="final_interview_id">For further interview:</label>
				<?php if (!is_null($position_hierarchy)):?>
					<div class="select-input-wrap"><?=form_dropdown('final_interviewer_id', $position_hierarchy);?></div>
				<?php else:?>
					<span class="red">No final interviewer set under position settings.</span>
				<?php endif;?>
			</div>
			<div class="form-item even">
				<label class="label-desc gray" for="final_date_time">Date and Time:</label>
				<div class="text-input-wrap"><input type="text" class="input-text datetimepicker" name="final_datetime" id="final_datetime" /></div>
			</div>	
		</div>

	</div>

<?php endif;?>
<script type="text/javascript">
	$(document).ready(function () {		
		$('#endorse').click(function () {
			Boxy.confirm(
				'<div id="boxyhtml" height="50px">Endorse this candidate for job offer?</div>',
				function () {
					hash = '<?php echo md5($this->session->userdata("session_id") . $this->input->post("record_id"));?>';
					$('#record-form')
						.append($('<input type="hidden" name="hash"></input>').val(hash))
						.attr('action', 
							module.get_value('base_url') + module.get_value('module_link') + '/endorse_candidate')
						.submit();
				}			
			);
			return false;
		});
	});

	// Override Ajax save event to check if candidate can be endorsed to other position.
	
</script>