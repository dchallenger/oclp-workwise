<div class="icon-label-group">
    <div class="icon-label">
        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="validate_ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save</span>
        </a>
    </div>
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="validate_ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>
    
    <div class="icon-label">
        <a href="javascript:void(0)" onclick="validate_ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, goto_detail)" class="icon-16-disk-back" rel="record-save-email">
            <span>Send Request</span>
        </a>
    </div>
</div>
<div class="or-cancel">
    <span class="or">or</span>
    <a class="cancel" href="javascript:void(0)" onclick="backtolistview()">Cancel</a>
</div>