<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php


    if ($technical_interviewer && $interviewer->num_rows() > 0):
        foreach ($technical_interviewer->result() as $row):

            ?>

            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_name_id][]">
                        Technical Interviewer:
                    </label>
                    <div class="text-input-wrap"><?= $row->firstname ?>&nbsp;<?= $row->middleinitial ?>&nbsp;<?= $row->lastname ?></div>                     
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="skill[proficiency][]">
                        Date:
                    </label>
                    <div class="text-input-wrap"><?= ($row->datetime != '0000-00-00 00:00:00' ? date($this->config->item('display_datetime_format'),strtotime($row->datetime)) : '') ?></div>
                </div>
            </div>
            <div class="clear"></div>
        <?php endforeach; ?>
            <h3 class="form-head">&nbsp;</h3>        
    <?php endif; ?>
</div>
