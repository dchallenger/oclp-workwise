<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <input type="hidden" name="position_id" id="position_id" value="<?= $position_id ?>" />
    <?php 

        if (count($item) > 0):

        $item_count = 0;
        foreach ($item as $data):
    ?>
            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_type][]">
                        Item No.: 
                    </label>
                    <div class="text-input-wrap">
                    	<?= $data['skills_item_no'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="skill[active][]">
                        Criteria:
                    </label>
                    <div class="text-input-wrap">
                    	<?= $data['skills_item'] ?>
                    </div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_name][]">
                        Rating Type:
                    </label>
                    <div class="text-input-wrap">
                    	<?php 
                            $item_score_type = $data['score_type'];
                            foreach( $item_score_type_list as $item_score_type_info ){ ?>
                                <?php if( $item_score_type_info['score_type_id'] == $item_score_type ){ echo $item_score_type_info['score_type']; } ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-item view even" <?php if( $data['score_type'] == 6 || $data['score_type'] == 3 ){ ?>style="display:none;"<?php } ?> >
                    <label class="label-desc view gray" for="skill[active][]">
                        Weight:
                    </label>
                    <div class="text-input-wrap">
                        <?= $data['item_weight'] ?>
                    </div>
                </div>
                <div class="multiple_type" <?php if( $data['score_type'] != 6 ){ ?>style="display:none;"<?php } ?> >
                    <div class="form-item view odd">
                        <label class="label-desc view gray" for="skill[active][]">
                            Sub Criteria 1:
                        </label>
                        <div class="text-input-wrap">
                            <?= $data['subcriteria1'] ?>
                        </div>
                    </div>
                    <div class="form-item view even">
                        <label class="label-desc view gray" for="skill[active][]">
                            Sub Criteria 2:
                        </label>
                        <div class="text-input-wrap">
                            <?= $data['subcriteria2'] ?>
                        </div>
                    </div>
                    <div class="form-item view odd">
                        <label class="label-desc view gray" for="skill[active][]">
                            Sub Criteria 3:
                        </label>
                        <div class="text-input-wrap">
                            <?= $data['subcriteria3'] ?>
                        </div>
                    </div>
                    <div class="form-item view even">
                        <label class="label-desc view gray" for="skill[active][]">
                            Sub Criteria 4:
                        </label>
                        <div class="text-input-wrap">
                            <?= $data['subcriteria4'] ?>
                        </div>
                    </div>
                </div>

                
            </div>
            <div class="clear"></div>
            <div style="height: 10px;"></div>
            <div style="height: 10px; border-top: 2px solid #CCCCCC;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>