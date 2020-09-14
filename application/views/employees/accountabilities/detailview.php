<div>
    <?php
    if (count($accountabilities) > 0):
        foreach ($accountabilities as $data):
            $employee_clearance_form_checklist_id = '';
            if(!empty($data['employee_clearance_form_checklist_id'] )){
                $employee_clearance_form_checklist = $this->db->get_where('employee_clearance_form_checklist', array('employee_clearance_form_checklist_id' => $data['employee_clearance_form_checklist_id'] ))->row();
                $employee_clearance_form_checklist_id = $employee_clearance_form_checklist->name;
            }
            ?>
        
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[equipment][]">
                        Item:
                    </label>
                    <div class="text-input-wrap"><?= $data['equipment'] ?></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[tag_number][]">
                        Tag Number:
                    </label>
                    <div class="text-input-wrap"><?= $data['tag_number'] ?></div>
                </div>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[status][]">
                        Status:
                    </label>
                    <div class="text-input-wrap"><?= $data['status'] ?></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[date_issued][]">
                        Date Issued:
                    </label>
                    <div class="text-input-wrap"><?= ($data['date_issued'] == "0000-00-00" || $data['date_issued'] == "1970-01-01" || $data['date_issued'] == "0000-00-00 00:00:00" ? "" : date('m/d/Y', strtotime($data['date_issued']))) ?></div>
                </div>                
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[date_returned][]">
                        Date Returned:
                    </label>
                    <div class="text-input-wrap"><?= ($data['date_returned'] == "0000-00-00" || $data['date_returned'] == "1970-01-01" || $data['date_returned'] == "0000-00-00 00:00:00" ? "" : date('m/d/Y', strtotime($data['date_returned']))) ?></div>
                </div>                 
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[cost][]">
                        Cost:
                    </label>
                    <div class="text-input-wrap"><?= $data['cost'] ?></div>
                </div>
                <div class="form-item odd">
                     <label class="label-desc gray" for="accountabilities[quantity][]">
                        Quantity:
                    </label>
                    <div class="text-input-wrap"><?= $data['quantity'] ?></div>
                </div> 
                <div class="form-item even">
                     <label class="label-desc gray" for="accountabilities[employee_clearance_form_checklist_id][]">
                        Clearance Approver: 
                    </label>
                    <div class="text-input-wrap"><?= $employee_clearance_form_checklist_id?></div>
                </div> 
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><?= $data['remarks'] ?></div>
                </div>               
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
<?php endif; ?>
</div>
