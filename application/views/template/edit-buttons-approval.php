<?php $record_id = $this->input->post('record_id'); ?>
<div class="icon-label-group">
    <span class="<?php echo ($record_id < 0) ? 'form-submit-btn' : '' ?> <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 && $record_id < 0? 'hidden' : '' ?>">
        <div class="icon-label"> <a onclick="ajax_save('', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk" rel="record-save"> <span>Save as Draft</span> </a> </div>
        <!-- div class="icon-label"> <a onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)" href="javascript:void(0);" class="icon-16-disk-back" rel="record-save-back"> <span>Save &amp; Back</span> </a> </div -->
        <div class="icon-label"> <a href="javascript:save_and_email(false);" class="icon-16-send-email" rel="record-save-email"> <span>Save &amp; Send Request</span> </a> </div>
    </span>
</div>

<div class="icon-label-group">
    <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back to list</span> </a> </div>
</div>
