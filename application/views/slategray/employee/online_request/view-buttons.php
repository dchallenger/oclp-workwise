<?php
  dbug($approver);
?>
<div class="form-submit-btn ">
<div class="icon-label-group">

    <?php if( $status == 1 || $status == -1  ): ?>

    <div class="icon-label">
         <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
              <span>Edit</span>
          </a>
    </div>

    <?php endif?>

    <?php if( $this->user_access[$this->module_id]['approve'] == 1 && $status == 2 && $approver == true  ): ?>

      <div class="icon-label">
            <a rel="save-draft" class="icon-16-disk" href="javascript:void(0);" onclick="approve_request('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
                <span>Approve Request</span>
            </a>
      </div>
      <div class="icon-label">
            <a rel="save-draft" class="icon-16-disk" href="javascript:void(0);" onclick="decline_request('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
                <span>Decline Request</span>
            </a>
      </div>

    <?php endif?>

    <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
</div>
</div>


<script>

   function approve_request( on_success, is_wizard , callback ){

      $('form#record-form').append('<input type="hidden" name="approve_request" value="true" />');
      ajax_save( on_success, is_wizard , callback );
   }

   function decline_request( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="decline_request" value="true" />');
      ajax_save( on_success, is_wizard , callback );
   }

</script>