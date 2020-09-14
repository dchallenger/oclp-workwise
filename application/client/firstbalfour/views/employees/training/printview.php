<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($training) > 0):
        foreach ($training as $data):
            $date_from = ($data['from_date'] == "0000-00-00" || $data['from_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['from_date'])));
            $date_to = ($data['date_to'] == "0000-00-00" || $data['date_from'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['date_to'])));           
        ?>
            <div>
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
            </div>
            <div class="clear"></div>
            <hr />
        <?php endforeach; ?>
    <?php endif; ?>
</div>