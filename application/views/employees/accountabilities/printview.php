<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($accountabilities) > 0):
        foreach ($accountabilities as $data):
            $date_issued = ($data['date_issued'] == "0000-00-00" || $data['date_issued'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['date_issued'])));
            $date_returned = ($data['date_returned'] == "0000-00-00" || $data['date_returned'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['date_returned']))); 
            $employee_clearance_form_checklist_id = '';
            if(!empty($data['employee_clearance_form_checklist_id'] )){
                $employee_clearance_form_checklist = $this->db->get_where('employee_clearance_form_checklist', array('employee_clearance_form_checklist_id' => $data['employee_clearance_form_checklist_id'] ))->row();
                $employee_clearance_form_checklist_id = $employee_clearance_form_checklist->name;
            }
        ?>
            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="accountabilities[equipment][]">
                        Item:
                    </label>
                    <div class="text-input-wrap"><?= $data['equipment'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="accountabilities[tag_number][]">
                        Tag Number:
                    </label>
                    <div class="text-input-wrap"><?= $data['tag_number'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="accountabilities[status][]">
                        Status:
                    </label>
                    <div class="text-input-wrap"><?= $data['status'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="accountabilities[date_issued][]">
                        Date Issued:
                    </label>
                    <div class="text-input-wrap"><?= $date_issued ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="accountabilities[cost][]">
                        Cost:
                    </label>
                    <div class="text-input-wrap"><?= $data['cost'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="accountabilities[date_returned][]">
                        Date Returned:
                    </label>
                    <div class="text-input-wrap"><?= $date_returned ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="accountabilities[quantity][]">
                        Quantity:
                    </label>
                    <div class="text-input-wrap"><?= $data['quantity'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="accountabilities[date_returned][]">
                       Clearance Approver:
                    </label>
                    <div class="text-input-wrap"><?= $employee_clearance_form_checklist_id ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[address][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><?= nl2br($data['remarks']) ?></div>
                </div>
            </div>
            <div class="clear"></div>
            <hr />
        <?php endforeach; ?>
    <?php endif; ?>
</div>