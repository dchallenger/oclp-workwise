<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<hr />
<h4>
	<?=$criteria->text?>
	<span class="align-right">
		<a onclick="edit_appraisal_criteria_question( '-1', <?=$criteria->employee_appraisal_criteria_id?> )" class="icon-button icon-16-add" href="javascript:void(0);"></a>
		<a onclick="edit_appraisal_criteria( '<?=$criteria->employee_appraisal_criteria_id?>', <?php echo $this->input->post('record_id')?> )" class="icon-button icon-16-edit" href="javascript:void(0);"></a>
		<a onclick="template_delete( '<?=$criteria->employee_appraisal_criteria_id?>', 'admin/appraisal_criteria')" class="icon-button icon-16-delete" href="javascript:void(0);"></a>
	</span>
</h4>
<table>
	<tr>
		<td>Ratio Weighted Score <b>(RWS)</b>:</td>
		<td><?=$criteria->rws?>%</td>
	</tr>
</table>
<div class="spacer"></div>
<h5>Questions</h5>

<table width="100%" class="default-table boxtype">
	<thead>
		<tr>			
			<th>Question Item</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($questions as $question):?>
		<tr>
			<td><?=$question->question?></td>
			<td style="text-align:center;">
				<a onclick="edit_appraisal_criteria_question( '<?=$question->employee_appraisal_criteria_question_id?>', <?=$criteria->employee_appraisal_criteria_id?> )" class="icon-button icon-16-edit" href="javascript:void(0);"></a>
				<a onclick="template_delete( '<?=$criteria->employee_appraisal_criteria_id?>', 'admin/appraisal_criteria_question')" class="icon-button icon-16-delete" href="javascript:void(0);"></a>				
			</td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>

<div class="spacer"></div>
