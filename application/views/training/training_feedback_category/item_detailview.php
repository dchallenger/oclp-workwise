<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php 

        if (count($item) > 0):

        $item_count = 0;
        foreach ($item as $data):
    ?>
            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="skill[skill_type][]">
                        Assessment Item No.: 
                    </label>
                    <div class="text-input-wrap">
                    	<?= $data['feedback_item_no'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[active][]">
                        Assessment Item:
                    </label>
                    <div class="text-input-wrap">
                    	<?= $data['feedback_item'] ?>
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
                
            </div>
            <div class="clear"></div>
            <div style="height: 10px;"></div>
            <div style="height: 10px; border-top: 2px solid #CCCCCC;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>