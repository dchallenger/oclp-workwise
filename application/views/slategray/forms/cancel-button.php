<div class="form-submit-btn">
	<?php $show_or = false; ?>

  <div class="icon-label-group">
    <?php if($this->user_access[$this->module_id]['cancel'] == 1) {
      $show_or = true;
      $qry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_obt_date WHERE employee_obt_id = '{$this->key_field_val}'");
      if($qry->num_rows() > 1) { ?>
        <div class="icon-label">
              <a class="icon-16-cancel" href="javascript:void(0);" onclick="change_status_cancellation(<?php echo $this->key_field_val?>, 'multiple')">
                  <span>Cancel</span>
              </a>            
          </div>
      <?php } else { ?>
        <div class="icon-label">
              <a class="icon-16-cancel" href="javascript:void(0);" onclick="change_status_cancellation(<?php echo $this->key_field_val?>, 'single')">
                  <span>Cancel</span>
              </a>            
          </div>
       <?php  } 
      } ?>
  </div>
  <div class="or-cancel">
      <?php if($show_or) :?><span class="or">or</span><?php endif; ?>
      <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
  </div>
</div>
