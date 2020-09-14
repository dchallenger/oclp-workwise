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
  if ($benefit_id != 0):
    $benefit = $this->db->get_where('recruitment_other_benefits', array('recruitment_other_benefits_id' => $benefit_id))->row(); ?>

    <div class="form-item odd ">
      <label class="label-desc gray" for="basic"><?php echo $benefit->benefits_from?>:</label>
      <div class="text-input-wrap">
        <input type="hidden" name="benefit[benefit_id][]" value="<?php echo $benefit_id?>">
        <input type="hidden" name="benefit[benefit][]" value="<?php echo $benefit->benefits_from?>">
        <input type="text" class="benefit-field input-text input-medium text-right" value="" id="" name="benefit[benefit_amount][]" />
        <span class="icon-group">   
          <a onclick="delete_benefit( $(this),  <?php echo $benefit_id?>)" href="javascript:void(0);" class="icon-button icon-16-minus"></a>
        </span>
      </div>
    </div>
  <div class="clear"></div>
<?php else:?>

  <div class="form-item odd ">
    <label class="label-desc gray" for="basic">Others:</label>
    <div class="text-input-wrap">
      <input type="text" class="input-text input-medium" value="" id="" name="others[benefit][]" /></div>

      <label class="label-desc gray" for="basic">Amount:</label>
      <div class="text-input-wrap">
      <input type="text" class="benefit-field input-text input-medium text-right" value="" id="" name="others[amount][]" />
      <span class="icon-group">   
        <a onclick="delete_benefit( $(this),  <?php echo $benefit_id?>)" href="javascript:void(0);" class="icon-button icon-16-minus"></a>
      </span>
    </div>
  </div>
  <div class="clear"></div>

<?php endif;?>