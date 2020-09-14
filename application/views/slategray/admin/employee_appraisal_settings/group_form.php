<div class="list_row align-left">
    <div class="align-left group_name_html" style="width:200px;"><?php echo $group_name; ?></div>
    <div class="align-left group_action"  style="width:150px; text-align:center;">
        <input type="hidden" name="group[group_id][]" class="group_id" value="<?php echo $group_id; ?>" />
        <input type="hidden" name="group[group][]" class="group_name" value="<?php echo $group_name; ?>" />
        <span class="icon-group">
            <a href="javascript:void(0)" tooltip="Apply Changes" style="display:none;" class="appraisal_save_group icon-button icon-16-disk"></a>
            <a href="javascript:void(0)" tooltip="Cancel Changes" style="display:none;" class="appraisal_cancel_group icon-button icon-16-cancel"></a>
            <a href="javascript:void(0)" tooltip="Edit group" class="appraisal_edit_group icon-button icon-16-edit"></a>
            <a href="javascript:void(0)" tooltip="Delete group" class="appraisal_delete_group icon-button icon-16-delete"></a>   
        </span>
    </div>
    <div class="align-left" style="width:250px;">&nbsp;</div>
</div>