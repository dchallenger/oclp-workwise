<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<?php if($this->input->post('record_id') != "-1"): ?>
  <p class="form-group-description align-left">Add/Delete Groups as needed.</p>
  <div class="form-submit-btn align-right nopadding">
    <div class="icon-label-group">
      <div class="icon-label">
        <a onclick="edit_appraisal_criteria( '-1', <?php echo $this->input->post('record_id')?> )" class="icon-16-add" href="javascript:void(0)">
          <span>Add New Criteria</span>
        </a>            
      </div>
    </div>
  </div>
  <div class="clear"></div>
  <div id="appraisal-template-criteria-container"></div>
<?php ; else : ?>
	<div id="appraisal-template-criteria-container"></div>
<?php endif;?>