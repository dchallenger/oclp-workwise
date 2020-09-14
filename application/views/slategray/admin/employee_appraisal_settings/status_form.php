<div class="list_row align-left">
    <div class="align-left status_name_html" style="width:200px;"><?php echo $status_name; ?></div>
    <div class="align-left status_action"  style="width:150px; text-align:center;">
        <input type="hidden" name="status[status_id][]" class="status_id" value="<?php echo $status_id; ?>" />
        <input type="hidden" name="status[status][]" class="status_name" value="<?php echo $status_name; ?>" />
        <span class="icon-group">
            <a href="javascript:void(0)" tooltip="Apply Changes" style="display:none;" class="appraisal_save_status icon-button icon-16-disk"></a>
            <a href="javascript:void(0)" tooltip="Cancel Changes" style="display:none;" class="appraisal_cancel_status icon-button icon-16-cancel"></a>
            <a href="javascript:void(0)" tooltip="Edit Status" class="appraisal_edit_status icon-button icon-16-edit"></a>
            <a href="javascript:void(0)" tooltip="Delete Status" class="appraisal_delete_status icon-button icon-16-delete"></a>   
        </span>
    </div>
    <div class="align-left" style="width:250px;">&nbsp;</div>
</div>