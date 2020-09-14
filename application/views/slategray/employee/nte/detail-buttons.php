<?php $show_or = false;?>
<div class="form-submit-btn">
  <div class="icon-label-group">
      <?php if($this->user_access[$this->module_id]['edit'] == 1 && $my_nte): 
				$show_or = true;?>
        <div class="icon-label">
          <a rel="record-save-back" class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
              <span>Reply</span>
          </a>
        </div>
      <?php endif?>
      <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
  </div>
   
  <!--
  <div class="or-cancel">
      <?php if($show_or) :?><span class="or">or</span><?php endif; ?>
      <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
  </div>
  -->
</div>

<script>

</script>