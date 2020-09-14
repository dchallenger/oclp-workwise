<fieldset>
<div class="form-multiple-add-accountabilities">
    <input type="hidden" class="add-more-flag" value="accountabilities" />
    <?php
    if (count($accountabilities) > 0):
        foreach ($accountabilities as $data):
            $employee_clearance[0] = 'Select...';
            ksort($employee_clearance);
            if(!isset($enable_edit) && ($enable_edit != 1)){
            ?>

            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[equipment][]">
                        Item:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['equipment'] ?>" name="accountabilities[equipment][]"></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[tag_number][]">
                        Tag Number:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['tag_number'] ?>" name="accountabilities[tag_number][]"></div>
                </div>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[status][]">
                        Status:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['status'] ?>" name="accountabilities[status][]"/> <span></span>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[date_issued][]">
                        Date Issued:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" class="input-text datepicker date" value="<?= ($data['date_issued'] == "0000-00-00" || $data['date_issued'] == "1970-01-01" || $data['date_issued'] == ""  || $data['date_issued'] == "0000-00-00 00:00:00" ? "" : date('m/d/Y', strtotime($data['date_issued']))) ?>" name="accountabilities[date_issued][]" /> <span></span>
                    </div>
                </div>                
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[date_returned][]">
                        Date Returned:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" class="input-text datepicker date" value="<?= ($data['date_returned'] == "0000-00-00" || $data['date_returned'] == "1970-01-01" || $data['date_returned'] == "" || $data['date_returned'] == "0000-00-00 00:00:00" ? "" : date('m/d/Y', strtotime($data['date_returned']))) ?>" name="accountabilities[date_returned][]" /> <span></span>
                    </div>
                </div>                 
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[cost][]">
                        Cost:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['cost'] ?>" name="accountabilities[cost][]"></div>
                </div>
                <div class="form-item odd">
                     <label class="label-desc gray" for="accountabilities[quantity][]">
                        Quantity:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['quantity'] ?>" name="accountabilities[quantity][]"></div>
                </div> 
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[employee_clearance_form_checklist_id][]">
                        Clearance Approver:
                    </label>
                    <div class="select-input-wrap">                        
                        <?php 
                        echo form_dropdown('accountabilities[employee_clearance_form_checklist_id][]', $employee_clearance, $data['employee_clearance_form_checklist_id']);
                        ?>
                    </div>
                </div>                  
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><textarea class="input-textarea" name="accountabilities[remarks][]"><?= $data['remarks'] ?></textarea></div>
                </div>                              
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <?php
                }else{ 
            ?>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[equipment][]">
                        Item:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['equipment'] ?>" name="accountabilities[equipment][]"></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[tag_number][]">
                        Tag Number:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['tag_number'] ?>" name="accountabilities[tag_number][]"></div>
                </div>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[status][]">
                        Status:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['status'] ?>" name="accountabilities[status][]"/> <span></span>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[date_issued][]">
                        Date Issued:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" class="input-text datepicker date" value="<?= ($data['date_issued'] == "0000-00-00" || $data['date_issued'] == "1970-01-01" || $data['date_issued'] == "" || $data['date_issued'] == "0000-00-00 00:00:00" ? "" : date('m/d/Y', strtotime($data['date_issued']))) ?>" name="accountabilities[date_issued][]" /> <span></span>
                    </div>
                </div>                
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[date_returned][]">
                        Date Returned:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" class="input-text datepicker date" value="<?= ($data['date_returned'] == "0000-00-00" || $data['date_returned'] == "1970-01-01" || $data['date_returned'] == "" || $data['date_returned'] == "0000-00-00 00:00:00" ? "" : date('m/d/Y', strtotime($data['date_returned']))) ?>" name="accountabilities[date_returned][]" /> <span></span>
                    </div>
                </div>                 
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[cost][]">
                        Cost:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['cost'] ?>" name="accountabilities[cost][]"></div>
                </div>
                <div class="form-item odd">
                     <label class="label-desc gray" for="accountabilities[quantity][]">
                        Quantity:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['quantity'] ?>" name="accountabilities[quantity][]"></div>
                </div> 
                <div class="form-item even">
                    <label class="label-desc gray" for="accountabilities[employee_clearance_form_checklist_id][]">
                        Clearance Approver:
                    </label>
                    <div class="select-input-wrap">                        
                        <?php 
                        echo form_dropdown('accountabilities[employee_clearance_form_checklist_id][]', $employee_clearance, $data['employee_clearance_form_checklist_id']);
                        ?>
                    </div>
                </div>                  
                <div class="form-item odd">
                    <label class="label-desc gray" for="accountabilities[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><textarea class="input-textarea" name="accountabilities[remarks][]"><?= $data['remarks'] ?></textarea></div>
                </div> 
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <?php } ?>
        <?php endforeach; ?>
<?php endif; ?>
</div>
</fieldset>
