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
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[course][]">
                        Course/Seminar Title:
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

                    <div class="text-input-wrap"><?= $data['course'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="training[institution][]">
                        Facilitators/Company:
                    </label>
                    <div class="text-input-wrap"><?= $data['institution'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[address][]">
                        Venue:
                    </label>
                    <div class="text-input-wrap"><?= $data['address'] ?></div>
                </div>
                <div class="form-item view even" style="display: none;">
                    <label class="label-desc view gray" for="training[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><?= nl2br($data['remarks']) ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[from_date][]">
                       Inclusive Dates:
                    </label>
                    <div class="text-input-wrap"><?= $date_from_to ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="training[no_of_hours][]">
                       No. of Hours:
                    </label>
                    <div class="text-input-wrap"><?= $data['no_of_hours'] ?></div>
                </div>
            </div>
            <div class="clear"></div>
            
        <?php endforeach; ?>
    <?php endif; ?>
</div>