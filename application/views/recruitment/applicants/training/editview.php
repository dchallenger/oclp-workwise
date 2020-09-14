<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<div class="form-multiple-add-training">
    <input type="hidden" class="add-more-flag" value="training" />
    <?php
    if (count($training) > 0):
        foreach ($training as $data):
               $date_from = '';
                $date_to = '';
                if ($data['from_date'] != '0000-00-00' && $data['from_date'] != '' && $data['from_date'] != NULL && $data['from_date'] != '1970-01-01'){
                    $date_from = date('M Y', strtotime($data['from_date']));
                }
                if ($data['to_date'] != '0000-00-00' && $data['to_date'] != '' && $data['to_date'] != NULL && $data['to_date'] != '1970-01-01'){
                    $date_to = date('M Y', strtotime($data['to_date']));
                }                                   
            ?>
            <fieldset>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <div class="form-item odd">
                    <label class="label-desc gray" for="training[course][]">
                        Course/Seminar Title:
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
                                                    
                            echo form_dropdown('training[course_id][]', $course, $data['course_id']);
                        ?>
                    </div>                    
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="training[institution][]">
                        Facilitators/Company:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['institution'] ?>" name="training[institution][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="training[address][]">
                        Venue:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['address'] ?>" name="training[address][]">
                    </div>
                </div>
                <div class="form-item even" style="display: none;">
                    <label class="label-desc gray" for="training[remarks][]">
                        Remarks:
                    </label>
                    <div class="textarea-input-wrap">
                        <textarea class="input-textarea" name="training[remarks][]"><?= $data['remarks'] ?></textarea>
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="training[from_date][]">
                        Inclusive Dates:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" name="training[from_date][]" id="" value="<?= $date_from ?>" class="input-text <?= (CLIENT_DIR == "firstbalfour" ? 'month-year date_from' : ' datepicker date'); ?>"/>
                        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
                        <input type="text" name="training[to_date][]" id="" value="<?= $date_to ?>" class="input-text <?= (CLIENT_DIR == "firstbalfour" ? 'month-year date_from' : ' datepicker date'); ?>" />
                    </div>                 
                </div>
                <div class="form-item even" >
                    <label class="label-desc gray" for="training[no_of_hours][]">
                        No. of Hours:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" name="training[no_of_hours][]" value="<?= $data['no_of_hours'] ?>">
                    </div>
                </div>
                <div class="clear"></div>
       
            </div>
            </fieldset>
            <div class="spacer"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>