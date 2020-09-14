<?php $record_id = $this->input->post('record_id'); ?>
<div class="icon-label-group">
<span class="<?php echo ($record_id < 0) ? 'form-submit-btn' : '' ?> <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 && $record_id < 0 ? 'hidden' : '' ?>">
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>
    <?php if ($records->status_id == 1):?>
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-send-email" href="javascript:void(0);" onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Send for Evaluation</span>
        </a>
    </div>
    <?php endif;?>
    <?php if ($can_approve):?>
    <div class="icon-label">
        <a class="icon-16-send-email" href="javascript:void(0);" onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Send</span>
        </a>           
    </div>
    <?php endif;?>
    
</span>
    <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
</div>