<div class="form-submit-btn">
  <div class="icon-label-group">
	<?php $show_or = false; ?>
  <?php if($this->user_access[$this->module_id]['print'] == 1 && method_exists(get_instance(), 'print_record')):
      $show_or = true; ?>
        <div class="icon-label">
            <a href="javascript:void(0);" onclick="print()" class="icon-16-print">
                <span>Print</span>
            </a>
        </div>
  <?php endif; ?>
  
  <?php if($this->user_access[$this->module_id]['edit'] == 1):
    $show_or = true; ?>
        <div class="icon-label">
            <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                <span>Edit</span>
            </a>            
        </div>
  <?php endif; ?>
  <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
    </div>
</div>
