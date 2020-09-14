<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php


    if (count($skill) > 0):
        foreach ($skill as $data):

            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
<!--                 <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_type][]">
                        Skill Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['skill_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="skill[skill_name][]">
                        Skill Name:
                    </label>
                    <div class="text-input-wrap"><?= $data['skill_name'] ?></div>
                </div> -->
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[computer_skills][]">
                        Skills Acquired (Computer Programs, Equipment and Machines can operate):
                    </label>
                    <div class="text-input-wrap"><?= $data['computer_skills'] ?></div>
                </div>                
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[proficiency][]">
                        Proficiency Level:
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['proficiency'] ?>
                    </div>
                </div>
                <div class="form-item view even" style="display:none">
                    <label class="label-desc view gray" for="affiliates[active][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['remarks'] ?>
                    </div>
                </div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
