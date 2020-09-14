<?php if($this->input->post('record_id') != "-1"): ?>
	<p class="form-group-description align-left">Add/Delete Items as needed.</p>
  <div class="form-submit-btn align-right nopadding has_top_level">
    <div class="icon-label-group">
      <div class="icon-label">
        <a onclick="edit_cst( '-1', <?php echo $this->input->post('record_id')?> )" class="icon-16-add" href="javascript:void(0)">                        
          <span>Add Custom</span>
        </a>            
      </div>
    </div>
  </div>
  <div class="clear"></div>
  <div class="cst-div"></div>
<?php ; else : ?>
	<div class="custom-transaction-div"></div>
<?php endif;?>