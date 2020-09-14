<style>
.form-item1,.form-item2 {
	float: left;
	margin-bottom: 10px;
}

.form-item1 {
	width: 45% !important;
}

.form-item2 {
	width: 5%;
}

</style>

<div class="spacer"></div>
<?php if ($this->_interview_status_id == $candidate_status_id): ?>
	
	<input id="ctr_handler" value="1" type="hidden">

	<div id="form-div">                        
		<div class="" id="fg-143" fg_id="143">
			<div class="col-2-form">
				<div class="form-item odd">
					<div class="form-submit-btn align-left nopadding">
						<div class="icon-label-group">
					    <div class="icon-label">
					      <a href="javascript:void(0)" class="icon-16-add" onclick="add_interviewer()">                        
					      	<span>Add Interviewer</span>
					      </a>            
					    </div>
					  </div>
					</div>				
				</div>					
				<div id="interviewer-container">
					<?php
						if ($candidate_interviewer && $candidate_interviewer->num_rows() > 0){
							foreach ($candidate_interviewer->result() as $row_interviewer) {
					?>
								<div class="parent_container">
									<div class="form-item odd">
										<label class="label-desc gray" for="final_interview_id">
											Interviewer:
										</label>
											<div class="select-input-wrap">
												<?php if (count($interviewer) > 0): ?>
													<select id="interviewer_id" name="interviewer_id[]">
														<option value="">Select...</option>
														<?php foreach ($interviewer as $key => $value) { ?>
															<option value="<?php echo $value['user_id'] ?>" <?php echo ($value['user_id'] == $row_interviewer->user_id ? 'SELECTED="SELECTED"' : '') ?>><?php echo $value['firstname'] ?>&nbsp;<?php echo $value['lastname'] ?></option>
														<?php } ?>
													</select>
												<?php else: ?>
													<span class="red">No interviewer set under position settings.</span>
												<?php endif; ?>
											</div>
									</div>
									<div class="form-item1 even">
										<label class="label-desc gray" for="final_date_time">Date and Time:<span class="red font-large">*</span></label>
										<div class="text-input-wrap"><input type="text" class="input-text datetimepicker" name="interview_date[]" value="<?php echo $row_interviewer->datetime ?>"/></div>
									</div>
									
									<div class="form-item2">
										<div style="padding-top:13px; width:100px;" class="icon-group">
												<a onclick="delete_benefit( $(this) )" href="javascript:void(0);" class="icon-button icon-16-minus"></a>
												<a class="icon-button icon-16-disk-back icon-send" href="javascript:void(0)"></a>
										</div>
									</div>	
																	
								</div>			
					<?php
							}
						}
					?>
				</div>
				<?php if ($with_sched): ?>
					<div class="form-item odd">
						<label class="label-desc gray" for="final_interview_id">Result:</label>
							<div class="select-input-wrap">
								<?php if ($interview_result && $interview_result->num_rows() > 0): ?>
									<select id="candidate_result_id" name="candidate_result_id">
										<option value="">Select...</option>
										<?php foreach ($interview_result->result() as $row) { ?>
											<option value="<?php echo $row->candidate_result_id ?>" <?php echo ($row->candidate_result_id == $candidate_info['candidate_result_id'] ? 'SELECTED="SELECTED"' : '') ?>><?php echo $row->candidate_result ?></option>
										<?php } ?>
									</select>
								<?php endif; ?>
							</div>
					</div>			
				<?php endif; ?>
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