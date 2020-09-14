<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<br />
<div>
    <?php
    if (count($insurance) > 0):
        foreach ($insurance as $data):
            ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="insurance[company][]">
                        Insurance Company:                        
                    </label>
                    <div class="text-input-wrap"><?= $data['company'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="insurance[relation][]">
                        Insurance Type:
                    </label>
                    <?php if( $data['type'] == 1 ){ ?>
                        <div class="text-input-wrap">Life Insurance</div>
                    <?php }else{ ?>
                         <div class="text-input-wrap">Non-Life Insurance</div>
                    <?php } ?>
                </div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
