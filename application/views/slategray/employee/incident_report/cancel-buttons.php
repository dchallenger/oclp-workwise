<div class="icon-label-group">
		<?php if( ( $this->user_access[$this->module_id]['cancel'] == 1 && ( $ir_status_id == 1 || $ir_status_id == -1  ) ) || ( $this->user_access[$this->module_id]['cancel'] == 1 && $this->user_access[$this->module_id]['approve'] == 1 && ( $ir_status_id == 1 || $ir_status_id == -1 || $ir_status_id == 2 ) ) ): ?>
		<div class="icon-label">
        <a rel="record-save-back" class="icon-16-cancel" href="javascript:void(0);" onclick="cancel_ir('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Cancel</span>
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
    <span class="or">or</span>
    <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
</div>
-->
<script>
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
</script>