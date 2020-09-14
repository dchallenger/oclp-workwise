<br/>
<input type="hidden" id="created_by" name="created_by" value="<?=$this->userinfo['user_id']?>">
<?php
if ($this->input->post('record_id') == -1):
?>
<input type="hidden" id="counter" value="1">
<!-- <div class="form-item odd " id="employees">
	<label class="label-desc gray" for="employee">
        Employee:
        <span class="red font-large">*</span>                                                        
    </label>
	<div class="select-input-wrap">
		<select id="employee_id" name="employee_id" style="width:62%">
			<option value=" "> </option>
		<?php 
			$where = "employee_id NOT IN (1,2,3)";
			$this->db->where($where);
			$this->db->order_by("lastname", "asc"); 
			$users = $this->db->get_where('user', array('deleted' => 0,'inactive' => 0))->result_array();

			foreach ($users as $emp):
		?>
			<option value="<?=$emp['employee_id']?>"><?=$emp['firstname']." ".$emp['lastname']?></option>
		<?php endforeach;?>
		</select>
	</div>
</div> -->

<div class="form-item odd ">
	<label class="label-desc gray" for="question">
        Question:
        <span class="red font-large">*</span>                                                        
    </label>
	<div class="text-input-wrap">
		<input type="text" class="input-text question input-medium" value="" name="question[]" question="0" >
		<span style="display: inline-block; vertical-align: middle;">
			<a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_row" columnid="1" question="q"></a>
		</span>
		
	</div> 
	<div class="percent">
	<label class="label-desc gray" for="tooltip">
		Question Percentage:
	</label>
	<div class="textarea-input-wrap">
		<input type="hidden" class="input-text question_percentage " Style="width:30%" value="" name="question_percentage[]" question_percentage="0" >
		<input type="text" class="input-text question_percentage_temp " Style="width:30%" value="" name="question_percentage_temp[]" question_percentage="0" >
	</div>		
	</div>


	<label class="label-desc gray" for="tooltip">
		Tooltip/Description:
	</label>
	<div class="textarea-input-wrap">
		<textarea class="input-textarea tooltip" name="tooltip[]" tabindex="4" rows="5"></textarea>
	</div>
</div>
<!-- <div class="form-item odd ">
	<div class="text-input-wrap">
		<input type="text" class="input-text input-medium question" value="" name="question[]" >
	</div> 
</div> -->
<?php 
else:
	// $this->db->select('distribution as equal');
	// $distribution = $this->db->get_where('employee_appraisal_criteria_question_header', array('employee_appraisal_criteria_question_header_id' => $this->input->post('record_id'), 'deleted' => 0))->row();
	// $questions = $this->db->get_where('employee_appraisal_criteria_question', array('employee_appraisal_criteria_question_header_id' => $this->input->post('record_id'), 'deleted' => 0))->result();
	$flag = count($questions);
?>

<input type="hidden" id="counter" value="<?=$flag?>">
<input type="hidden" id="distribution" value="<?=$distribution->equal?>">

<!-- <div class="form-item odd " id="employees">
	<label class="label-desc gray" for="employee">
        Employee:
        <span class="red font-large">*</span>                                                        
    </label>
	<div class="select-input-wrap">
		<select id="employee_id" name="employee_id" style="width:62%">
			<option value=" "> </option>
		<?php 
			$where = "employee_id NOT IN (1,2,3)";
			$this->db->where($where);
			$this->db->order_by("lastname", "asc"); 
			$users = $this->db->get_where('user', array('deleted' => 0,'inactive' => 0))->result_array();

			foreach ($users as $emp):
		?>
			<option value="<?=$emp['employee_id']?>"><?=$emp['firstname']." ".$emp['lastname']?></option>
		<?php endforeach;?>
		</select>
	</div>
</div> -->
<?php foreach ($questions as $key => $question): ?>

	<div class="form-item odd ">
		<label class="label-desc gray" for="question">
	        Question:
	        <span class="red font-large">*</span>                                                        
	    </label>
		<div class="text-input-wrap">
			<input type="text" value="<?=$question->question?>" class="input-text question input-medium" name="old_question[<?=$question->employee_appraisal_criteria_question_id?>]" question="<?=$question->employee_appraisal_criteria_question_id?>">
			<span style="display: inline-block; vertical-align: middle;">
				<a tooltip="Add row" href="javascript:void(0)" class="icon-16-add icon-button add_row" columnid="question" question="q"></a>
				<?php if ($flag > 1) { ?>
					<a class="icon-16-delete icon-button delete_row" href="javascript:void(0)" original-title=""></a>
				<?php } ?>
				
			</span>
		</div> 

		<div class="percent">
			<label class="label-desc gray" for="tooltip">
				Question Percentage:
			</label>
			<div class="textarea-input-wrap">
			<!-- 	<input type="text" class="input-text question_percentage " Style="width:30%" value="<?=number_format($question->question_percentage, '2', '.', '')?>" name="old_question_percentage[<?=$question->employee_appraisal_criteria_question_id?>]" question_percentage="<?=$question->employee_appraisal_criteria_question_id?>"  > -->
				<input type="text" class="input-text question_percentage_temp " Style="width:30%" value="<?=number_format($question->question_percentage, '2', '.', '')?>" name="old_question_percentage_temp[<?=$question->employee_appraisal_criteria_question_id?>]" question_percentage="<?=$question->employee_appraisal_criteria_question_id?>"  >
				<input type="hidden" class="input-text question_percentage " Style="width:30%" value="<?=$question->question_percentage?>" name="old_question_percentage[<?=$question->employee_appraisal_criteria_question_id?>]" question_percentage="<?=$question->employee_appraisal_criteria_question_id?>"  >
			</div>		
		</div>
		<label class="label-desc gray" for="tooltip">
			Tooltip/Description:
		</label>
		<div class="textarea-input-wrap">
			<textarea class="input-textarea tooltip" name="old_tooltip[<?=$question->employee_appraisal_criteria_question_id?>]" tabindex="4" rows="5"><?=$question->tooltip?></textarea>
		</div>
	</div>

<?php 
	endforeach;
endif; ?>
