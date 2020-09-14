<div class="list_row align-left">
    <div class="align-left scale_name_html" style="width:200px;"><?php echo $scale_name; ?></div>
    <div class="align-left scale_times_html" style="width:100px;  text-align:center;"><?php echo $scale_times; ?></div>
    <div class="align-left scale_action"  style="width:150px; text-align:center;">
        <input type="hidden" name="scale[scale_id][]" class="scale_id" value="<?php echo $scale_id; ?>" />
        <input type="hidden" name="scale[scale][]" class="scale_name" value="<?php echo $scale_name; ?>" />
        <input type="hidden" name="scale[scale_times][]" class="scale_times" value="<?php echo $scale_times; ?>" />
        <span class="icon-group">
            <a href="javascript:void(0)" tooltip="Apply Changes" style="display:none;" class="appraisal_save_scale icon-button icon-16-disk"></a>
            <a href="javascript:void(0)" tooltip="Cancel Changes" style="display:none;" class="appraisal_cancel_scale icon-button icon-16-cancel"></a>
            <a href="javascript:void(0)" tooltip="Edit Status" class="appraisal_edit_scale icon-button icon-16-edit"></a>
            <a href="javascript:void(0)" tooltip="Delete Status" class="appraisal_delete_scale icon-button icon-16-delete"></a>   
        </span>
    </div>
    <div class="align-left" style="width:150px;">&nbsp;</div>
</div>