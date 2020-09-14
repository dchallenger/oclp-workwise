<div class="form-submit-btn">
	<?php $show_or = false; ?>

  <div class="icon-label-group">
    <?php if($this->user_access[$this->module_id]['approve'] == 1):
      $show_or = true;?>
          <div class="icon-label">
              <a class="icon-16-approve" href="javascript:void(0);" onclick="change_status_boxy(<?php echo $this->key_field_val?>, 3, goto_detail)">
                  <span>Approve</span>
              </a>            
          </div>
    <?php endif; ?>

    <?php if($this->user_access[$this->module_id]['decline'] == 1):
      $show_or = true;?>
          <div class="icon-label">
              <a class="<?php echo (CLIENT_DIR == 'hdi' || CLIENT_DIR == 'basf' ? 'icon-16-disapprove' : 'icon-16-cancel'); ?>" href="javascript:void(0);" onclick="change_status_boxy(<?php echo $this->key_field_val?>, 4, goto_detail)">
                  <span>Disapprove</span>
              </a>            
          </div>
    <?php endif; ?>
  </div>
  <div class="or-cancel">
      <?php if($show_or) :?><span class="or">or</span><?php endif; ?>
      <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
  </div>
</div>
