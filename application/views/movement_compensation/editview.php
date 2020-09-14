<div class="form-item even">
  <!-- style="margin-right:16.5%;width:300px;" -->
  <label class="label-desc gray" for="benefitddlb">Add Benefits</label>
  <!--<p class="form-group-description align-left">Add Benefits as needed.</p>-->
  <div class="select-input-wrap align-right" style="float:left;">
  	<input type="hidden" value="" name="selected-benefits">
    <select name="benefitddlb" id="benefitddlb"> 
  		<option value="">Select...</option><?php
      $this->db->order_by('benefit');
      $benefits = $this->db->get_where('benefit', array('deleted' => 0));
      foreach($benefits->result() as $benefit): ?>
        <option value="<?php echo $benefit->benefit_id?>"><?php echo $benefit->benefit?></option><?php
      endforeach; ?>
    </select>
  </div>
  <div class="form-submit-btn align-right nopadding">
  <div class="icon-label-group">
    <div class="icon-label">
      <a onclick="add_benefit()" class="icon-16-add" href="javascript:void(0)">                        
        <span>Add Benefit</span>
      </a>            
    </div>
  </div>
</div>
</div>
<div class="clear"></div>
<div class="benefits-div"></div>