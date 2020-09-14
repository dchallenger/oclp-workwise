<div class="icon-label-group">

    <?php if( !($this->input->post('related_id') > 0)){ ?>
    <div class="icon-label">
        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save</span>
        </a>
    </div>
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>
    
    <?php }
        if( $form_status_id != 3 && $this->user_access[$this->module_id]['post'] != 1 ){ ?>

    <div class="icon-label">
        <a href="javascript:void(0)" onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, goto_detail)" class="icon-16-disk-back   " rel="record-save-email">
            <span>Send Request</span>
        </a>
    </div>    

    <?php }elseif ($form_status_id != 3 && $this->input->post('filter') == "personal") {?>

    <div class="icon-label">
        <a href="javascript:void(0)" onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, goto_detail)" class="icon-16-disk-back   " rel="record-save-email">
            <span>Send Request</span>
        </a>
    </div> 

   <?php } ?>
    

    <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
</div>
<div class="or-cancel">
    <span class="or">or</span>
    <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
</div>