<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php


    if (count($cost_center) > 0):
        foreach ($cost_center as $data):

            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="cost_center[cost_center_id][]">
                        Main Cost Center:
                    </label>
                    <div class="text-input-wrap"><?= $data['cost_center'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="cost_center[percentage][]">
                        Percentage:
                    </label>
                    <div class="text-input-wrap"><?= $data['percentage'] ?><span>&nbsp; &#37;</span></div>
                </div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div>
        <div class="form-item view even">
            <label class="label-desc view gray" for="cost_center[percentage][]">
                Total Percentage:
            </label>
            <div class="text-input-wrap">100<span>&nbsp; &#37;</span></div>
        </div>
    </div>

</div>
