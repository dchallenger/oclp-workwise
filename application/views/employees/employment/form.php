<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<br />
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
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="employment[company][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="employment[address][]">
            Address:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="employment[address][]">
        </div>
    </div>
    <?php if ($this->config->item('additional_forms_exists') == 1): ?>
        <div class="form-item odd">
                <label class="label-desc gray" for="employment[contact_no][]">
                Contact No.:
            </label>
            <div class="text-input-wrap"><input type="text" class="input-text" value="" name="employment[contact_no][]">
            </div>
        </div>   
    <?php endif; ?>
    <div class="form-item even">
            <label class="label-desc gray" for="employment[nature_of_business][]">
            Nature of Business:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="employment[nature_of_business][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="employment[position][]">
            Position:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="employment[position][]">
        </div>
    </div>
   <div class="form-item even">
        <label class="label-desc gray" for="education[date_from][]">
            Inclusive Dates:
        </label>                
        <div class="text-input-wrap">                   
            <input type="text" name="employment[from_date][]" id="" value="" class="input-text month-year date_from"/>
            &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
            <input type="text" name="employment[to_date][]" id="" value="" class="input-text month-year date_from" />
        </div>                
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="employment[supervisor_name][]">
            Immediate Superior’s Name:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="employment[supervisor_name][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="employment[reason_for_leaving][]">
            Reason for Leaving:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="employment[reason_for_leaving][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="employment[duties][]">
            Duties:
        </label>
        <div class="textarea-input-wrap">
            <textarea class="input-textarea" name="employment[duties][]"></textarea>
        </div>
    </div>
    <?php if ($this->config->item('additional_forms_exists') == 1): ?>
        <div class="form-item even">
            <label class="label-desc gray" for="employment[last_salary][]">
                Last Salary:
            </label>
            <div class="text-input-wrap"><input type="text" class="input-text" value="" name="employment[last_salary][]">
            </div>
        </div>
        <!-- div class="form-item even">
            <label class="label-desc gray" for="employment[fb_equivalent_position_id][]">
                Equivalent Position (FB):
            </label>
            <div class="select-input-wrap">
                < ?php
                    $result =  $this->db->get_where('user_position',array("deleted"=>0));

                    $rows_array = array();
                    if ($result && $result->num_rows() > 0):
                        $rows_array[$row->position_id] = "Select position...";
                        foreach ($result->result() as $row) {
                            $rows_array[$row->position_id] = $row->position;
                        }
                    endif;

                    $position = $rows_array;             
                    echo form_dropdown('employment[fb_equivalent_position_id][]', $position);
                ?>
            </div>
        </div -->
    <?php endif; ?>        
    <div class="clear"></div>
    <hr />
</div>