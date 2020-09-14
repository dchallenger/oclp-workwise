<div class="form-submit-btn">
  <div class="icon-label-group">
	<?php  $show_or = false;  ?>
  
  <?php if($this->user_access[$this->module_id]['print'] == 1 && method_exists(get_instance(), 'print_record')):
      $show_or = true; ?>
        <div class="icon-label">
            <a href="javascript:void(0);" onclick="print()" class="icon-16-print">
                <span>Print</span>
            </a>
        </div>
  <?php endif; ?>
  <?php if(($this->user_access[$this->module_id]['edit'] == 1  ) && ( $ir_status_id == 1  ) ):
    $show_or = true; ?>
        <div class="icon-label">
            <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                <span>Edit</span>
            </a>            
        </div>
  <?php endif; ?>
  <?php if(($this->user_access[$this->module_id]['edit'] == 1 && $this->user_access[$this->module_id]['post'] == 1 && $this->user_access[$this->module_id]['approve'] == 1 ) && ( $ir_status_id == 2  ) && $this->config->item('client_no') != 2 ):
    $show_or = true; ?>
        <div class="icon-label">
            <a class="icon-16-document-stack" href="javascript:void(0);" onclick="edit()">
                <span>Attach Document</span>
            </a>            
        </div>
  <?php endif; ?>

  <?php if(($this->user_access[$this->module_id]['edit'] == 1 && $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['approve'] == 1 ) && ( $ir_status_id == 6 && $approvers == 1 )  && $this->config->item('client_no') == 2 ):
    $show_or = true; ?>
        <div class="icon-label">
            <a class="icon-16-document-stack" href="javascript:void(0);" onclick="edit()">
                <span>Add Attachment/Remarks</span>
            </a>            
        </div>
  <?php endif; ?>
  <?php if(($this->user_access[$this->module_id]['edit'] == 1 && $this->user_access[$this->module_id]['post'] == 1 && $this->user_access[$this->module_id]['approve'] == 1 ) && ( $ir_status_id == 2  ) ):
    $show_or = true; ?>
        <div class="icon-label">
            <a class="icon-16-document-stack" href="javascript:void(0);" onclick="edit()">
                <span>Add Attachment/Remarks</span>
            </a>            
        </div>
  <?php endif; ?>

  <?php if($this->user_access[$this->module_id]['post'] == 1 && ( $ir_status_id == 2 || $ir_status_id == 1 ) ): ?>
    <div class="icon-label">
          <a rel="record-save-back" class="icon-16-notify" href="javascript:void(0);" onclick="notify_concerned('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Notify Concerned</span>
          </a>
    </div>
  <?php endif?>
  <?php  if( ($this->user_access[$this->module_id]['add'] == 1) && ( $this->user_access[$this->module_id]['approve'] != 1 ) && ( $ir_status_id == 1 ) ){ ?>
      <div class="icon-label">
          <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="send_hr('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>For Validation</span>
          </a>
      </div>
  <?php }; ?>
  <?php if( $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['approve'] == 1 && ( $ir_status_id == 1 || $ir_status_id == -1 || $ir_status_id == 6 ) ): ?>
    <div class="icon-label">
          <a rel="record-save-back" class="icon-16-notify" href="javascript:void(0);" onclick="validate_hr('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>For Validation</span>
          </a>
    </div>
  <?php endif  ?>
  <?php if( ( ( $ir_status_id == 6 && $approvers == 1 ) && $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['approve'] == 1 ) || ( $this->user_access[$this->module_id]['post'] == 1 && $this->user_access[$this->module_id]['approve'] == 1 && ( $ir_status_id == 2 ) ) ): ?>
      <div class="icon-label">
          <a rel="record-save-back" class="icon-16-cancel" href="javascript:void(0);" onclick="close_ir('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Close</span>
           </a>
      </div>
  <?php endif?>
  <?php if( ( ( $this->user_access[$this->module_id]['cancel'] == 1 && $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['approve'] != 1 && ( $ir_status_id == 1 ) ) ) || ( ( $this->user_access[$this->module_id]['cancel'] == 1 && $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['approve'] == 1 && ( $ir_status_id == 1 || $ir_status_id == 6 ) ) ) || ( ( $this->user_access[$this->module_id]['cancel'] == 1 && $this->user_access[$this->module_id]['post'] == 1 && $this->user_access[$this->module_id]['approve'] == 1 && ( $ir_status_id == 1 || $ir_status_id == 2 ) ) ) ): ?>
      <div class="icon-label">
          <a rel="record-save-back" class="icon-16-cancel" href="javascript:void(0);" onclick="cancel_ir('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Cancel</span>
           </a>
      </div>
  <?php endif  ?>
  <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
  <!--
  <div class="or-cancel">
      <?php if($show_or) :?><span class="or">or</span><?php endif; ?>
      <a class="cancel" href="javascript:void(0)" rel="action-back">Go Back</a>
  </div>
  -->
  </div>
</div>

<script>
  <?php if($this->user_access[$this->module_id]['approve'] == 1 && ( $ir_status_id == 2 || $ir_status_id == 1) ) : ?>
    function notify_concerned( on_success, is_wizard , callback ){
       Boxy.ask("Are you sure you want to continue?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
                $('form#record-form').append('<input type="hidden" name="notify" value="true" />');
                 $('form#record-form').append('<input type="hidden" name="notify_hr" value="true" />');
              ajax_save( on_success, is_wizard , callback );
            }
        },
        {
            title: "Notify Concerned"
        });

    }
  <?php endif?>

  <?php if($this->user_access[$this->module_id]['approve'] == 1 && ( $ir_status_id == 1 || $ir_status_id == -1  ) ): ?>
    function notify_hr_concerned( on_success, is_wizard , callback ){

       Boxy.ask("Are you sure you want to continue?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
            $('form#record-form').append('<input type="hidden" name="notify" value="true" />');
            $('form#record-form').append('<input type="hidden" name="notify_hr" value="true" />');
            ajax_save( on_success, is_wizard , callback );
            }
        },
        {
            title: "Notify Concerned"
        });
    }
  <?php endif?>
  
  <?php if($this->user_access[$this->module_id]['cancel'] == 1): ?>
    function cancel_ir( on_success, is_wizard , callback ){
      Boxy.ask("Are you sure you want to continue?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
              $('form#record-form').append('<input type="hidden" name="cancel" value="true" />');
              ajax_save( on_success, is_wizard , callback );
            }
        },
        {
            title: "Cancel Incident Report"
        });
    }
  <?php endif?>

  function send_hr( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="send" value="true" />');
      ajax_save( on_success, is_wizard , callback )
  }

  function validate_hr( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="validate" value="true" />');
      ajax_save( on_success, is_wizard , callback )
  }

  function close_ir( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="close" value="true" />');
      ajax_save( on_success, is_wizard , callback )
  }

</script>
