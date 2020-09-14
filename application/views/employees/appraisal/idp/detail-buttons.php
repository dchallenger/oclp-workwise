<div class="form-submit-btn">
    <?php $record_id = $this->input->post('record_id'); ?>
    <div class="icon-label-group">
        <?php if($records->idp_status == 'For Approval'):?>
            <?php if ($can_approve ): ?>
                <div class="icon-label">
                    <a class="icon-16-approve" href="javascript:void(0);" onclick="forApproval(<?=$records->individual_development_plan_id?>,'Approved')">
                        <span>Approve</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($can_decline): ?>
                <div class="icon-label">
                    <a class="icon-16-disapprove" href="javascript:void(0);" onclick="forApproval(<?=$records->individual_development_plan_id?>, 'Decline')">
                        <span>Disapprove</span>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </span>
    <?php if ( ($this->user_access[$this->module_id]['edit'] && $this->user_access[$this->module_id]['post']) && ($records->idp_status == 'HR Review') ){ ?>

        <div class="icon-label">
            <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
                <span>Edit</span>
            </a>            
        </div>
    <?php } ?>
        <div class="icon-label">
            <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
                <span>Back to list</span>
            </a>
        </div>
    </div>
</div>
