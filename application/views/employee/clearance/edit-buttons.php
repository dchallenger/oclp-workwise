<div class="icon-label-group">
    <?php if($this->input->post('record_id') > '0' && $this->user_access[$this->module_id]['edit'] == 1): ?>
      <div class="icon-label">
          <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Save</span>
          </a>
      </div>
<!--       <div class="icon-label">
          <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Save &amp; Back</span>
          </a>
      </div> -->
      <div class="icon-label">
            <a rel="record-save-back" class="icon-16-send-email" href="javascript:void(0);" onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)">
                <span>Save &amp; Send</span>
            </a>
        </div>
    <?php endif?>
    
    <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:go_back();">
            <span>Back to list</span>
        </a>
    </div>
</div>