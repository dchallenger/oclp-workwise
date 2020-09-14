<?php // $questions = $this->db->get_where('employee_appraisal_criteria_question', array('employee_appraisal_criteria_question_header_id' => $this->input->post('record_id'), 'deleted' => 0))->result();?>


</div>
<div class="col-1-form view">   
<table border="1" class="default-table boxtype ">
<thead>
    <tr><th>Question</th><th colspan="2">Question Percentage</th><th>Tooltip</th></tr>
</thead>
<tbody>
    <?php foreach ($questions as $key => $question) {?>
    <tr>
        <td > <?=$key+1?>. <?=$question->question?></td>
        
        <td colspan="2"> <?=number_format($question->question_percentage, '2', '.' ,'')?>%</td>
        <td>  <?=$question->tooltip?> </td>
    </tr>
    <?php } ?>
</tbody>
</table>

 

</div>