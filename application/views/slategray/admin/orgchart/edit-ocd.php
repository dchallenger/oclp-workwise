<?php if($this->input->post('record_id') != "-1"): ?>
	<?php echo jOrgChart_script();?>
  <p class="form-group-description align-left">Add/Delete Items as needed.</p>
  <div class="form-submit-btn align-right nopadding has_top_level">
    <div class="icon-label-group">
      <div class="icon-label">
        <a onclick="add_top_level( <?php echo $this->input->post('record_id')?> )" class="icon-16-add" href="javascript:void(0)">                        
          <span>Add Top Level</span>
        </a>            
      </div>
    </div>
  </div>
  <div class="clear"></div>
  <div class="ocd-div"></div>
  <div id="chart" class="orgChart"></div>
<?php ; else : ?>
	<div class="orgchart-div"></div>
<?php endif;?>