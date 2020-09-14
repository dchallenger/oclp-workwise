<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php


    if (count($skill) > 0):
        foreach ($skill as $data):

            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_type_id][]">
                        Skill Type:
                    </label>
                    <div class="text-input-wrap">
                        <?
                            $this->db->where('skill_type_id',$data['skill_type_id']);
                            $this->db->where('deleted',0);
                            $result = $this->db->get('skill_type');
                            if ($result && $result->num_rows() > 0){
                                $row = $result->row();
                                echo $row->skill_type;
                            }
                        ?>
                    </div>                    
<!--                     <div class="text-input-wrap"><?= $data['skill_type'] ?></div> -->
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[active][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['remarks'] ?>
                    </div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_name_id][]">
                        Skill Name:
                    </label>
                    <div class="text-input-wrap">
                        <?
                            $this->db->where('skill_name_id',$data['skill_name_id']);
                            $this->db->where('deleted',0);
                            $result = $this->db->get('skill_name');
                            if ($result && $result->num_rows() > 0){
                                $row = $result->row();
                                echo $row->skill_name;
                            }
                        ?>
                    </div>                    
<!--                     <div class="text-input-wrap"><?= $data['skill_name'] ?></div> -->
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[proficiency][]">
                        Proficiency Level:
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['proficiency'] ?>
                    </div>
                </div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
