  <form name="record-form" id="record-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="record_id" id="record_id"  value="<?= $this->input->post('record_id') ?>"/>
        <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>"/>
        <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?=$this->input->post('prev_search_str')?>"/>
        <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?=$this->input->post('prev_search_field')?>"/>
        <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?=$this->input->post('prev_search_option')?>"/>
    </form>

<?php

  if( $this->input->post('record_id') > 0 ){

//echo "hey!".$this->input->post('date');
  //echo "<script>$(document).ready(function () { alert(employee_id); }); </script>";
  $this->db->where('id', $this->input->post('record_id'));
  $row=$this->db->get('employee_dtr')->row_array();

  $this->db->select('employee_leaves.*,form_status.*,employee_form_type.*');
	$this->db->from('employee_leaves');
	$this->db->join('form_status', 'form_status.form_status_id = employee_leaves.form_status_id', 'left');
  $this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');
	$this->db->where('employee_leaves.deleted', 0);
	//note record_id is employee_id
  
  $this->db->where('employee_leaves.employee_id', $row['employee_id']);
  $this->db->where('employee_leaves.date_from <=', $row['date']);
  $this->db->where('employee_leaves.date_to >=', $row['date']);
  $arr=$this->db->get()->result_array();
  //$keynames = array_keys($arr[0]);
  foreach($arr as $fields=>$fieldval){
    echo "Leaves";
?>

<div class="col-2-form view other_application" style="padding-bottom:30px;">
<div class="form-item view odd ">
   <label for="perm_address1" class="label-desc view gray"> Form Type: </label>
   <div class="text-input-wrap">
      <?= $fieldval['application_form']; ?>
   </div>        
</div>
<div class="form-item view even ">
   <label for="perm_address1" class="label-desc view gray"> Effective Dates: </label>
   <div class="text-input-wrap">
      <?= date($this->config->item('display_date_format'), strtotime($fieldval['date_from'])); ?> To <?= date($this->config->item('display_date_format'), strtotime($fieldval['date_to'])); ?>
   </div>        
</div>
<div class="form-item view even ">
   <label for="perm_address1" class="label-desc view gray"> Number of Day(s): </label>
   <div class="text-input-wrap">
      <?php 
        $pieces=explode(' ',$fieldval['date_created']);
        $from = strtotime($pieces[0]);
        $to = strtotime($pieces[1]);
        $nod = $to - $from;
        echo floor($nod/(60*60*24));
      ?>
   </div>        
</div>
<div class="form-item view odd ">
   <label for="perm_address1" class="label-desc view gray"> Application Date: </label>
   <div class="text-input-wrap">
      <?php 
      //$pieces=explode(' ',$fieldval['date_created']);
      $newdate=date($this->config->item('display_datetime_format'), strtotime($fieldval['date_created']));
      echo $newdate;
      //echo $pieces[1];
      //date("Y-m-d", strtotime("2011-W17-6"))
      ?>
   </div>        
</div>
<div class="form-item view even ">
   <label for="perm_address1" class="label-desc view gray"> Reason: </label>
   <div class="text-input-wrap">
      <?= $fieldval['reason']; ?>
   </div>        
</div>
<div class="form-item view even ">
   <label for="perm_address1" class="label-desc view gray"> Status: </label>
   <div class="text-input-wrap">
      <?= $fieldval['form_status']; ?>
   </div>        
</div>
</div>
<?php
  }
}
else{
?>
<div class="col-2-form view other_application" style="padding-bottom:30px;">

</div>
<?php
}
?>