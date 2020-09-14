<div class="form-submit-btn">
	<?php $show_or = false; ?>
  <?php if($this->user_access[$this->module_id]['print'] == 1 && method_exists(get_instance(), 'print_record')):
      $show_or = true; ?>
      <div class="icon-label-group">
        <div class="icon-label">
            <a href="javascript:void(0);" onclick="print()" class="icon-16-print">
                <span>Print</span>
            </a>
        </div>
      </div>
  <?php endif; ?>
  
  <?php if( ( $this->user_access[$this->module_id]['post'] && $status != 3 ) || ($this->user_access[$this->module_id]['edit'] == 1 && $participant_id == $this->userinfo['user_id'] && $status != 3 ) ):
    $show_or = true; ?>
    <div class="icon-label-group">
        <div class="icon-label">
            <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                <span>Edit</span>
            </a>            
        </div>
    </div>
  <?php endif; ?>
  <div class="or-cancel">
      <?php if($show_or) :?><span class="or">or</span><?php endif; ?>
      <a class="cancel" href="javascript:go_back();">Go Back</a>
  </div>
</div>
