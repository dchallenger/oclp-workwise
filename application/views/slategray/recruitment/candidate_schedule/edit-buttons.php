<div class="icon-label-group">
		<?php if($this->input->post('record_id') == '-1' && $this->user_access[$this->module_id]['add'] == 1): ?>
      <div class="icon-label">
          <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="validate_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Save</span>
          </a>
      </div>
      <div class="icon-label">
          <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="validate_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Save &amp; Back</span>
          </a>
      </div>
    <?php endif?>
    
    <?php if($this->input->post('record_id') > '0' && $this->user_access[$this->module_id]['edit'] == 1): ?>
      <div class="icon-label">
          <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="validate_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Save</span>
          </a>
      </div>
      <div class="icon-label">
          <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="validate_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Save &amp; Back</span>
          </a>
      </div>
    <?php endif?>
    
    <div class="icon-label">
        <a rel="back-to-schedule-list" class="icon-16-listback back_to_schedule_list" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
</div>

<div>
  <input type="hidden" name="current_mrf_id" id="current_mrf_id" value="<?= $current_mrf_id ?>" />
</div>