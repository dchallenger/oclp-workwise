<?php $record_id = $this->input->post('record_id'); ?>
<div class="icon-label-group">
<span class="<?php echo ($record_id < 0) ? 'form-submit-btn' : '' ?> <?php echo $show_wizard_control && isset($fieldgroups) && sizeof($fieldgroups) > 1 && $record_id < 0 ? 'hidden' : '' ?>">
    <div class="icon-label">
        <a rel="record-save-back" class="icon-16-disk-back" href="javascript:void(0);" onclick="ajax_save('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
            <span>Save &amp; Back</span>
        </a>
    </div>


    <?php if($records->status == 4):?>
        <?php if ($can_approve ): ?>
            <div class="icon-label">
                <a class="icon-16-approve" href="javascript:void(0);" onclick="forApproval(<?=$records->training_application_id?>, 5)">
                    <span>Approve</span>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($can_decline): ?>
            <div class="icon-label">
                <a class="icon-16-disapprove" href="javascript:void(0);" onclick="forApproval(<?=$records->training_application_id?>,6)">
                    <span>Disapprove</span>
                </a>
            </div>
        <?php endif; ?>
    <?php else:?>
        <div class="icon-label">
            <a rel="record-save-back" class="icon-16-send-email" href="javascript:void(0);" onclick="ajax_save('email', <?php echo $show_wizard_control ? 1 : 0 ?>)">
                <span>Send Request</span>
            </a>
        </div>
    <?php endif; ?>



    <?php if (($this->user_access[$this->module_id]['post'] == 1 && $this->userinfo['user_id'] != $records->employee_id)  && $records->status == 3):?>
        <div class="icon-label">
            <a class="icon-16-cancel" href="javascript:void(0);" onclick="forApproval(<?php echo $this->key_field_val?>, 2)">
                <span>Invalid Request</span>
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