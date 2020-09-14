<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($training) > 0):
        foreach ($training as $data):
            $date_from = ($data['from_date'] == "0000-00-00" || $data['from_date'] == "1970-01-01" ? "" : date((CLIENT_DIR == "firstbalfour" ? $this->config->item('display_month_year_date_format') : $this->config->item('display_date_format')), strtotime($data['from_date'])));
            $date_to = ($data['to_date'] == "0000-00-00" || $data['to_date'] == "1970-01-01" ? "" : date((CLIENT_DIR == "firstbalfour" ? $this->config->item('display_month_year_date_format') : $this->config->item('display_date_format')), strtotime($data['to_date'])));                    
            if ($date_from == "" || $date_to == ""):
                $date_from_to = "";
            else:
                $date_from_to = $date_from . " to " . $date_to;
            endif        
            ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[course][]">
                        Course:
                    </label>
                    <div class="text-input-wrap">
                        <?
                            $this->db->where('course_id',$data['course_id']);
                            $this->db->where('deleted',0);
                            $result = $this->db->get('course');
                            if ($result && $result->num_rows() > 0){
                                $row = $result->row();
                                echo $row->course;
                            }
                        ?>

                        <?= $data['course'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="training[institution][]">
                        Institution:
                    </label>
                    <div class="text-input-wrap"><?= $data['institution'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap"><?= $data['address'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="training[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><?= nl2br($data['remarks']) ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[from_date][]">
                        Date Attended:
                    </label>
                    <div class="text-input-wrap"><?= $date_from_to ?></div>
                </div>
                <?php if ($this->config->item('additional_forms_exists') == 1): ?>
                    <div class="form-item view even">
                        <label class="label-desc view gray" for="training[employee_training_status_id][]">
                            Training Status:
                        </label>
                        <div class="text-input-wrap">
                            <?
                                $this->db->where('employee_training_status_id',$data['employee_training_status_id']);
                                $this->db->where('deleted',0);
                                $result = $this->db->get('employee_training_status');
                                if ($result && $result->num_rows() > 0){
                                    $row = $result->row();
                                    echo $row->employee_training_status;
                                }
                            ?>
                        </div>
                    </div>  
                <?php endif; ?>              
            </div>              
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
    if( count($additional_training) > 0 ){
        foreach( $additional_training as $employee_training_info ){
?>

<div class="col-2-form view">     
    <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
    <div class="form-item view odd ">
        <label class="label-desc view gray" for="training_course_id">Course:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['training_subject'] ?>                  
        </div>      
    </div>  

    <div class="form-item view even">
        <label class="label-desc view gray" for="provider">Provider:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['training_provider'] ?>
        </div>      
    </div>  

    <div class="form-item view odd">
        <label class="label-desc view gray" for="venue">Venue:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['venue'] ?>                  
        </div>      
    </div>  

    <div class="form-item view even">
        <label class="label-desc view gray" for="schedule">Schedule:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['training_subject_schedule'] ?>                        
        </div>      
    </div>  

    <div class="form-item view even">
        <label class="label-desc view gray" for="venue">Training Balance:</label>
        <div class="text-input-wrap">
            <?= $employee_training_info['training_balance'] ?>
        </div>      
    </div>  

</div>

<?php
        }
?>

<div class="col-2-form view">     
    <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
    <div class="form-item view even">
        <label class="label-desc view gray" for="schedule">Total Running Balance:</label>
        <div class="text-input-wrap">
            <?= $total_running_balance ?>                     
        </div>      
    </div>  
</div>

<?php
    }

    
?>