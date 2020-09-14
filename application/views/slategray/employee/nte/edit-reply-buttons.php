<div class="form-submit-btn">
<div class="icon-label-group">
	<?php if($this->user_access[$this->module_id]['edit'] == 1): ?>
		<div class="icon-label">
            <a rel="record-save-back" class="icon-16-notify" href="javascript:void(0);" onclick="save_reply('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
                <span>Send Reply</span>
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
<!--
<div class="or-cancel">
    <span class="or">or</span>
    <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
</div>
-->

<script>
	<?php if($this->user_access[$this->module_id]['edit'] == 1): ?>
		function save_reply( on_success, is_wizard , callback ){

            Boxy.ask("Are you sure you want to continue?", ["Yes", "No"],function( choice ) {
            if(choice == "Yes"){
                    $('form#record-form').append('<input type="hidden" name="save_reply" value="true" />');
                    ajax_save( on_success, is_wizard , callback )
                }
            },
            {
                title: "Send Reply"
            });

		}
	<?php endif?>
</script>

