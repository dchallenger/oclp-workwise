<p class="form-group-description align-left">Add/Delete Benefits as needed.</p>
<div class="form-submit-btn align-right nopadding">
	<div class="icon-label-group">
    <div class="icon-label">
      <a onclick="add_benefit()" class="icon-16-add" href="javascript:void(0)">                        
      	<span>Add Benefit</span>
      </a>            
    </div>
  </div>
</div>
<!-- <div class="select-input-wrap align-right" style="width: auto;">
	<input type="hidden" value="" name="selected-benefits">
  <select name="benefitddlb" id="benefitddlb"> 
		<option value="">Select...</option><?php
    $benefits = $this->db->get_where('benefit', array('deleted' => 0));
    foreach($benefits->result() as $benefit): ?>
      <option value="<?php echo $benefit->benefit_id?>"><?php echo $benefit->benefit?></option><?php
    endforeach; ?>
  </select>
</div> -->
<div class="clear"></div>
<div class="benefits-div"></div>
<script type="text/javascript">
  $(document).ready(function () {
    $('.hrs-req').live('click', function () {
      if ($(this).val() == 'Hours Required') {
        $(this).val('');
      }
    });

  $('.hrs-req').live('blur', function () {
      if ($(this).val() == '') {
        $(this).val('Hours Required');
      }
    });    
  });

  $('.hrs-req').live('keydown', numeric_only);
</script>