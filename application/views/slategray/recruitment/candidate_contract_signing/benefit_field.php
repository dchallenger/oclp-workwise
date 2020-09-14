<style type="text/css">
  .select-input-wrap-mod select{
    border-color: #7C7C7C #C3C3C3 #DDDDDD;
    border-radius: 5px;
    border-style: solid;
    border-width: 1px;
    padding: 3px;   
  }
</style>

<?php
	$benefit = $this->db->get_where('benefit', array('benefit_id' => $benefit_id))->row();
?>
<div class="form-item odd ">
  <label class="label-desc gray" for="basic"><?php echo $benefit->benefit?>:</label>
  <div class="text-input-wrap">
  	<input type="hidden" name="benefit_id<?php echo $benefit_id?>" value="<?php echo $benefit_id?>">
    <input type="hidden" name="benefit_label<?php echo $benefit_id?>" value="<?php echo $benefit->benefit?>">
  	<input type="text" class="benefit-field input-text input-medium text-right" value="<?php echo isset($value) ? number_format($value, 2, '.', ',') : ''?>" id="" name="benefit[<?php echo $benefit_id?>]" /> 
    <?php if ($benefit->requires_hrs_work == 1):?><input type="text" class="input-text input-small gray hrs-req" value="<?php echo isset($hours) && $hours > 0? $hours : 'Hours Required'?>" name="hours[<?php echo $benefit_id?>]" /><?php endif;?>
  	   <span class="select-input-wrap-mod">
      <select name="units[<?php echo $benefit_id?>]">
        <option value="">Select...</option>
        <option value="weekly" <?php echo ($units == 'weekly' ? 'selected="SELECTED"' : '') ?>>Weekly</option>
        <option value="monthly" <?php echo ($units == 'monthly' ? 'selected="SELECTED"' : '') ?>>Monthly</option>
        <option value="quarterly" <?php echo ($units == 'quarterly' ? 'selected="SELECTED"' : '') ?>>Quarterly</option>
        <option value="yearly" <?php echo ($units == 'yearly' ? 'selected="SELECTED"' : '') ?>>Yearly</option>
      </select>
    </span>
    <span class="icon-group">  	
  		<a onclick="delete_benefit( $(this),  <?php echo $benefit_id?>)" href="javascript:void(0);" class="icon-button icon-16-minus"></a>
  	</span>
  </div>
</div>