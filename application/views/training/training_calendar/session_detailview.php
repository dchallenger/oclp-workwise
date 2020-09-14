<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php 

        if (count($session) > 0):

        $session_count = 0;
        foreach ($session as $data):

            $rand = rand(1,10000000);
    ?>
            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_type][]">
                        Session No.: 
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['session_no'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="skill[skill_type][]">
                        Training Date: 
                    </label>
                    <div class="text-input-wrap">
                    	<?= date('d F Y', strtotime($data['session_date']) ) ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[active][]">
                        Session Time:
                    </label>
                    <div class="text-input-wrap">
                    	<?= date('h:i a', strtotime($data['sessiontime_from']) ) ?> to <?= date('h:i a', strtotime($data['sessiontime_to'] ) ) ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="skill[proficiency][]">
                        Breaktime:
                    </label>
                    <div class="text-input-wrap">
                        <?= date('h:i a', strtotime($data['breaktime_from']) ) ?> to <?= date('h:i a', strtotime($data['breaktime_to'] ) ) ?>
                    </div>
                </div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 10px;"></div>
            <div style="height: 10px; border-top: 2px solid #CCCCCC;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div>
	<div class="form-item view odd">
        <label class="label-desc view gray" for="skill[skill_name][]">
            Total Training Hours:
        </label>
        <div class="text-input-wrap">
        	<?= $session_total_hours ?>
        </div>
    </div>
    <div class="form-item view odd">
        <label class="label-desc view gray" for="skill[skill_name][]">
            Total Breaks:
        </label>
        <div class="text-input-wrap">
        	<?= $session_total_breaks ?>
        </div>
    </div>
</div>
