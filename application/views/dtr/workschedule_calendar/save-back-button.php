<div class="icon-label-group">
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>
    <?php if (isset($email_sent) && $email_sent != 1):?>
    <div class="icon-label"> <a href="javascript:void(0)" onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)" class="icon-16-disk-back" rel="record-save-email"> <span>Send Request</span> </a> </div>    
    <?php endif;?>
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