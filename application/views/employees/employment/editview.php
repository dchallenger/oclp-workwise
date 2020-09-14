<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="form-multiple-add-employment">
    <input type="hidden" class="add-more-flag" value="employment" />
    <?php
    if (count($employment) > 0):
        foreach ($employment as $data):
             if(!isset($enable_edit) && ($enable_edit != 1)){
            ?>
            <br />
                        <fieldset>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <br />
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[company][]">
                        Company:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['company'] ?>" name="employment[company][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['address'] ?>" name="employment[address][]">
                    </div>
                </div>
                <?php if ($this->config->item('additional_forms_exists') == 1): ?>
                    <div class="form-item odd">
                            <label class="label-desc gray" for="employment[contact_no][]">
                            Contact No.:
                        </label>
                        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['contact_no'] ?>" name="employment[contact_no][]">
                        </div>
                    </div> 
                <?php endif; ?>                                
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[nature_of_business][]">
                        Nature of Business:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['nature_of_business'] ?>" name="employment[nature_of_business][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[position][]">
                        Position:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['position'] ?>" name="employment[position][]">
                    </div>
                </div>

                <div class="form-item even">
                    <label class="label-desc gray" for="education[date_from][]">
                        Inclusive Dates:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" name="employment[from_date][]" id="" value="<?= ($data['from_date'] == "0000-00-00" || $data['from_date'] == "1970-01-01" ? "" : date('F Y', strtotime($data['from_date']))) ?>" class="input-text month-year date_from"/>
                        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
                        <input type="text" name="employment[to_date][]" id="" value="<?= ($data['to_date'] == "0000-00-00" || $data['to_date'] == "1970-01-01" ? "" : date('F Y', strtotime($data['to_date']))) ?>" class="input-text month-year date_from" />
                    </div>                
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[supervisor_name][]">
                        Immediate Superior’s Name:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['supervisor_name'] ?>" name="employment[supervisor_name][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[reason_for_leaving][]">
                        Reason for Leaving:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['reason_for_leaving'] ?>" name="employment[reason_for_leaving][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[duties][]">
                        Duties:
                    </label>
                    <div class="textarea-input-wrap">
                        <textarea class="input-textarea" name="employment[duties][]"><?= $data['duties'] ?></textarea>
                    </div>
                </div>
                <?php if ($this->config->item('additional_forms_exists') == 1): ?>                
                    <div class="form-item even">
                        <label class="label-desc gray" for="employment[last_salary][]">
                            Last Salary:
                        </label>
                        <div class="textarea-input-wrap">
                            <input type="text" class="input-text" value="<?= $data['last_salary'] ?>" name="employment[last_salary][]">
                        </div>
                    </div>
                    <!-- div class="form-item even">
                        <label class="label-desc gray" for="employment[fb_equivalent_position_id][]">
                            Equivalent Position (FB):
                        </label>
                        <div class="select-input-wrap">
                            < ?php 
                                echo form_dropdown('employment[fb_equivalent_position_id][]', $position, $data['fb_equivalent_position_id']);
                            ?>
                        </div>
                    </div -->                                
                <?php endif; ?>
                <div class="clear"></div>                
            </div>
            <div class="clear"></div>
            </fieldset>
            <?php 
                }else{
             ?>
             <br />
            <fieldset>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <br />
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[company][]">
                        Company:
                    </label>
                    <div class="text-input-wrap"><input type="text"  style="opacity:0.5;" readonly="readonly" class="input-text" value="<?= $data['company'] ?>" name="employment[company][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap"><input type="text"  style="opacity:0.5;"  readonly="readonly" class="input-text" value="<?= $data['address'] ?>" name="employment[address][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[nature_of_business][]">
                        Nature of Business:
                    </label>
                    <div class="text-input-wrap"><input type="text"  style="opacity:0.5;"  readonly="readonly" class="input-text" value="<?= $data['nature_of_business'] ?>" name="employment[nature_of_business][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[position][]">
                        Position:
                    </label>
                    <div class="text-input-wrap"><input type="text"  style="opacity:0.5;"  readonly="readonly" class="input-text" value="<?= $data['position'] ?>" name="employment[position][]">
                    </div>
                </div>

                <div class="form-item odd">
                    <label class="label-desc gray" for="education[date_from][]">
                        Inclusive Dates:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" name="employment[from_date][]" readonly="readonly" id="" style="width:30%; opacity:0.5;" value="<?= ($data['from_date'] == "0000-00-00" || $data['from_date'] == "1970-01-01" ? "" : date('F Y', strtotime($data['from_date']))) ?>" class="input-text"/>
                        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
                        <input type="text" name="employment[to_date][]" readonly="readonly" id="" style="width:30%; opacity:0.5;" value="<?= ($data['to_date'] == "0000-00-00" || $data['to_date'] == "1970-01-01" ? "" : date('F Y', strtotime($data['to_date']))) ?>" class="input-text" />
                    </div>                
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[supervisor_name][]">
                        Immediate Superior’s Name:
                    </label>
                    <div class="text-input-wrap"><input type="text"  readonly="readonly" style="opacity:0.5;" class="input-text" value="<?= $data['supervisor_name'] ?>" name="employment[supervisor_name][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[reason_for_leaving][]">
                        Reason for Leaving:
                    </label>
                    <div class="text-input-wrap"><input type="text"  readonly="readonly" style="opacity:0.5;" class="input-text" value="<?= $data['reason_for_leaving'] ?>" name="employment[reason_for_leaving][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[duties][]">
                        Duties:
                    </label>
                    <div class="textarea-input-wrap">
                        <textarea readonly="readonly" style="opacity:0.5;" class="input-textarea" name="employment[duties][]"><?= $data['duties'] ?></textarea>
                    </div>
                </div>
                <div class="clear"></div>
           
            </div>
            <div class="clear"></div>
            </fieldset>
             <?php } ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
