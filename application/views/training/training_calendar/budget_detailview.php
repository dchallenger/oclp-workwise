<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php 

        if (count($budget) > 0):

        $budget_count = 0;
        foreach ($budget as $data):

    ?>
            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_type][]">
                        Training Cost Name: 
                    </label>
                    <div class="text-input-wrap">
                    	<?= $data['training_cost_name'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[active][]">
                        Investment Cost:
                    </label>
                    <div class="text-input-wrap">
                    	<?= $data['cost'] ?>
                    </div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_name][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap">
                    	<?= $data['remarks'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="skill[proficiency][]">
                        No. of Particulars:
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['pax'] ?> 
                    </div>
                </div>

                <div class="form-item view even">
                    <label class="label-desc view gray" for="skill[proficiency][]">
                        Total:
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['total'] ?>
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
            Total Investment Cost:
        </label>
        <div class="text-input-wrap">
        	<?= $budget_total_cost ?>
        </div>
    </div>
    <div class="form-item view odd">
        <label class="label-desc view gray" for="skill[skill_name][]">
            Total No. of Particulars:
        </label>
        <div class="text-input-wrap">
        	<?= $budget_total_pax ?>
        </div>
    </div>
</div>
