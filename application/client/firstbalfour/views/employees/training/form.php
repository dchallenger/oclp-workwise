<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<div class="form-multiple-add" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
        </div>
    </h3>
    <div class="form-item odd">
        <label class="label-desc gray" for="training[course][]">
            Course:
        </label>
        <div class="select-input-wrap">
            <?php 
                $this->db->order_by('course');
                $result =  $this->db->get_where('course',array("deleted"=>0));

                $rows_array = array();
                if ($result && $result->num_rows() > 0):
                    $rows_array[$row->course_id] = "Select training...";
                    foreach ($result->result() as $row) {
                        $rows_array[$row->course_id] = $row->course;
                    }
                endif;
                
                $course = $rows_array;            
                echo form_dropdown('training[course_id][]', $course);
            ?>
        </div>         
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="training[institution][]">
            Institution:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="training[institution][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="training[address][]">
            Address:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="training[address][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="training[remarks][]">
            Remarks:
        </label>
        <div class="textarea-input-wrap">
            <textarea class="input-textarea" name="training[remarks][]"></textarea>
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="education[date_from][]">
            Date Attended:
        </label>                
        <div class="text-input-wrap">                   
            <input type="text" name="training[from_date][]" id="" value="" class="input-text <?= (CLIENT_DIR == "firstbalfour" ? 'month-year date_from' : ' datepicker date'); ?>"/>
            &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
            <input type="text" name="training[to_date][]" id="" value="" class="input-text <?= (CLIENT_DIR == "firstbalfour" ? 'month-year date_from' : ' datepicker date'); ?>" />
        </div>                
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="training[employee_training_status_id][]">
            Training Status:
        </label>
        <div class="select-input-wrap">
            <?php 
                $this->db->order_by('employee_training_status');
                $result =  $this->db->get_where('employee_training_status',array("deleted"=>0));

                $rows_array = array();
                if ($result && $result->num_rows() > 0):
                    $rows_array[$row->employee_training_status_id] = "Select status...";
                    foreach ($result->result() as $row) {
                        $rows_array[$row->employee_training_status_id] = $row->employee_training_status;
                    }
                endif;
                
                $training_status = $rows_array;            
                echo form_dropdown('training[employee_training_status_id][]', $training_status);
            ?>
        </div>
    </div>
    <div class="clear"></div>
    <hr />
</div>