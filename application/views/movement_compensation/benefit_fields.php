<?php
	$benefit = $this->db->get_where('benefit', array('benefit_id' => $benefit_id))->row();
?>
<div class="form-item odd clear_benefit">
  <label class="label-desc gray" for="basic"><?php echo $benefit->benefit?>:</label>
  <div class="text-input-wrap">
  	<input type="hidden" name="benefit_id<?php echo $benefit_id?>" value="<?php echo $benefit_id?>">
    <input type="hidden" name="benefit_label<?php echo $benefit_id?>" value="<?php echo $benefit->benefit?>">
  	<input type="text" class="benefit-field input-text text-right" value="<?php echo $benefit_values; ?><?php echo isset($value) ? number_format($value, 2, '.', ',') : ''?>" id="" name="benefit[<?php echo $benefit_id?>]"> <span class="icon-group"><a onclick="delete_benefit( $(this),  <?php echo $benefit_id?>)" href="javascript:void(0);" class="icon-button icon-16-minus"></a></span>
  	    <?php if ($benefit->requires_hrs_work == 1):?><input type="text" class="input-text input-small gray hrs-req" value="<?php echo isset($hours) && $hours > 0? $hours : 'Hours Required'?>" name="hours[<?php echo $benefit_id?>]" /><?php endif;?>
  </div>
</div>