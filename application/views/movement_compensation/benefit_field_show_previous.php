<?php
	$benefit = $this->db->get_where('employee_movement_benefit', array('benefit_id' => $benefit_id))->row();
?>
<div class="form-item odd ">
  <label class="label-desc gray" for="basic"><?php echo $benefit->benefit?>:</label>
  <div class="text-input-wrap">
  	<input type="hidden" name="benefit_id<?php echo $benefit_id?>" value="<?php echo $benefit_id?>">
    <input type="hidden" name="benefit_label<?php echo $benefit_id?>" value="<?php echo $benefit->benefit?>">
  	<input type="text" class="benefit-field input-text text-right" value="<?php echo isset($value) ? number_format($value, 2, '.', ',') : ''?>" id="" name="benefit[<?php echo $benefit_id?>]"> <span class="icon-group"><a onclick="delete_benefit( $(this),  <?php echo $benefit_id?>)" href="javascript:void(0);" class="icon-button icon-16-minus"></a></span>
  </div>
</div>