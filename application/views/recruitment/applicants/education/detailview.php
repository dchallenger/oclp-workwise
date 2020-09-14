<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($education) > 0):
        foreach ($education as $data):
            ?>
            <div style="display: block;">
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="education[education_level][]">
                        Educational Attainment:
                    </label>
                    <div class="text-input-wrap"><?= $data['education_level'] ?>
                    </div>
                </div>
                <div class="form-item view even <?php echo ($data['option_id'] < 10 ? 'hidden' : '')?>">
                    <label class="label-desc view gray" for="education[school][]">
                        <br />School:
                    </label>
                    <div class="text-input-wrap">
                        <?php
                            if ($data['education_school_id']){
                                $result = $this->db->query("SELECT education_school FROM {$this->db->dbprefix}education_school WHERE education_school_id = ".$data['education_school_id']." AND DELETED = 0");
                                if ($result && $result->num_rows() > 0){
                                    $row = $result->row();                                                           
                                    echo $row->education_school;
                                }elseif ($data['education_school_id'] == '-1') {
                                   echo 'Others';
                                 }
                            }
                            else{
                                echo '&nbsp;';
                            }
                        ?>                        
                    </div>
                </div> 
                <?php 
                    if ($data['option_id'] > 9) {
                        $class = "hidden";
                        if ($data['education_school_id'] == '-1') {
                           $class = "";
                        }
                    }else{
                        $class = '';
                    }
                ?>
                <div class="form-item view even <?php echo $class;?>">
                    <label class="label-desc view gray" for="education[school][]">
                        Name of School:
                    </label>
                    <div class="text-input-wrap"><?= $data['school'] ?>
                    </div>
                </div> 
          <!--      <?php
                    if ($data['education_level'] != 'Highschool' && $data['education_level'] != 'Elementary'){
                ?>
                        <div class="form-item view odd">
                            <label class="label-desc view gray" for="education[degree][]">
                                Degree / Course:
                            </label>
                            <div class="text-input-wrap">
                                <?php
                                    if ($data['employee_degree_obtained_id']){
                                        $result = $this->db->query("SELECT employee_degree_obtained FROM {$this->db->dbprefix}employee_degree_obtained WHERE employee_degree_obtained_id = ".$data['employee_degree_obtained_id']." AND DELETED = 0");
                                        if ($result && $result->num_rows() > 0){
                                            $row = $result->row();                                                           
                                            echo $row->employee_degree_obtained;
                                        }
                                    }
                                ?>  
                            </div>
                        </div>                
                <?                        
                    }
                ?>                             
             <div class="form-item view odd">
                    <label class="label-desc view gray" for="education[honors_received][]">
                        Honors / Awards:
                    </label>
                    <div class="text-input-wrap"><?= $data['honors_received'] ?>
                    </div>
                </div>
                <?php
                    $date_from = '';
                    $date_to = '';
                    if ($data['date_from'] != '0000-00-00' && $data['date_from'] != '' && $data['date_from'] != NULL && $data['date_from'] != '1970-01-01'){
                        $date_from = date('Y', strtotime($data['date_from']));
                    }
                    if ($data['date_to'] != '0000-00-00' && $data['date_to'] != '' && $data['date_to'] != NULL && $data['date_to'] != '1970-01-01'){
                        $date_to = date('Y', strtotime($data['date_to']));
                    }                                
                ?>                                           
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="education[date_from][]">
                        Date From:
                    </label>
                    <div class="text-input-wrap"><?= $date_from ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="education[date_to][]">
                        Date To:
                    </label>
                    <div class="text-input-wrap"><?= $date_to ?></div>
                </div>
               <div class="form-item view even">
                    <label class="label-desc view gray">
                        Graduate:
                    </label>
                    <div class="text-input-wrap"><?=($data['graduate'] == 1) ? 'Yes' : 'No'?></div>
                </div>          -->                      
            </div>
            <div class="clear"></div>
            
        <?php endforeach; ?>
    <?php endif; ?>
</div>