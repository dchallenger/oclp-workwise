<?php
    $this->db->where('deleted', 0);
    $this->db->where('employee_update_id', $this->input->post('record_id'));
    $arr=$this->db->get('employee_update_personal')->result_array();
    foreach($arr as $fields=>$fieldval){
?>
    <div class="form-item view odd ">
       <label for="personal_fname" class="label-desc view gray"> First Name </label>
       <div class="text-input-wrap">
          <?= $fieldval['personal_fName']; ?>
       </div>        
    </div>
    <div class="form-item view even ">
       <label for="personal_mname" class="label-desc view gray"> Middle name </label>
       <div class="text-input-wrap">
          <?= $fieldval['personal_mName']; ?>
       </div>        
    </div>
    <div class="form-item view odd ">
       <label for="personal_lname" class="label-desc view gray"> Last Name </label>
       <div class="text-input-wrap">
          <?= $fieldval['personal_lName']; ?>
       </div>        
    </div>
    <div class="form-item view even ">
       <label for="personal_dom" class="label-desc view gray"> Date of Marriage </label>
       <div class="text-input-wrap">
          <?= $fieldval['personal_dom']; ?>
       </div>        
    </div>
<?php  } ?>