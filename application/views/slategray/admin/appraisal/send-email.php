

<div class="icon-label-group">
    
    <div class="icon-label">
        <a rel="record-save" class="icon-16-disk" href="javascript:void(0);" onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save</span>
        </a>
    </div> 
    
    <div class="icon-label">
        <a href="javascript:void(0)" onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>, goto_detail )" class="icon-16-disk-back" rel="record-save-email">
            <span>Save &amp; Send</span>
        </a>
    </div>    

    <div class="icon-label">
        <a  class="icon-16-listback" href="javascript:void(0);" rel="action-back">
            <span>Back to list</span>
        </a>
    </div> 
</div>

<div class="or-cancel">
    <span class="or">or</span>
    <a class="cancel" href="javascript:void(0)" rel="action-back">Cancel</a>
</div>