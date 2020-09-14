<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<div class="form-multiple-add-training">
    <input type="hidden" class="add-more-flag" value="training" />
    <?php
    if (count($training) > 0):
        foreach ($training as $data):
            $date_from = ($data['from_date'] == "0000-00-00" || $data['from_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['from_date'])));
            $date_to = ($data['to_date'] == "0000-00-00" || $data['to_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['to_date'])));                    
            if(!isset($enable_edit) && ($enable_edit != 1)){
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
                                                    
                            echo form_dropdown('training[course_id][]', $course, $data['course_id']);
                        ?>
                    </div>                    
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="training[institution][]">
                        Institution:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['institution'] ?>" name="training[institution][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="training[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['address'] ?>" name="training[address][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="training[remarks][]">
                        Remarks:
                    </label>
                    <div class="textarea-input-wrap">
                        <textarea class="input-textarea" name="training[remarks][]"><?= $data['remarks'] ?></textarea>
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="training[from_date][]">
                        Date Attended:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" name="training[from_date][]" id="" value="<?= $date_from ?>" class="input-text <?= (CLIENT_DIR == "firstbalfour" ? 'month-year date_from' : ' datepicker date'); ?>"/>
                        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
                        <input type="text" name="training[to_date][]" id="" value="<?= $date_to ?>" class="input-text <?= (CLIENT_DIR == "firstbalfour" ? 'month-year date_from' : ' datepicker date'); ?>" />
                    </div>                
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="training[employee_training_status_id][]">
                        Training Status:
                    </label>
                    <div class="select-input-wrap">
                        <?php 
                            echo form_dropdown('training[employee_training_status_id][]', $training_status, $data['employee_training_status_id']);
                        ?>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            </fieldset>
            <?php 
                }else{
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
                        Course:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text"  readonly="readonly"  style="opacity:0.5;" value="<?= $data['course'] ?>" name="training[course][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="training[institution][]">
                        Institution:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" style="opacity:0.5;"  readonly="readonly" value="<?= $data['institution'] ?>" name="training[institution][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="training[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" style="opacity:0.5;"  readonly="readonly" value="<?= $data['address'] ?>" name="training[address][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="training[remarks][]">
                        Remarks:
                    </label>
                    <div class="textarea-input-wrap">
                        <textarea style="opacity:0.5;"  readonly="readonly" class="input-textarea" name="training[remarks][]"><?= $data['remarks'] ?></textarea>
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="training[from_date][]">
                        Date Attended:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" readonly="readonly" style="width:30%; opacity:0.5;" name="training[from_date][]" id="" value="<?= $date_from ?>" class="input-text"/>
                        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
                        <input type="text" readonly="readonly" style="width:30%; opacity:0.5;" name="training[to_date][]" id="" value="<?= $date_to ?>" class="input-text" />
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