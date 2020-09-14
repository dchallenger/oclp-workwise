<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<div>
    <span style="font-weight:bold;font-size:14px" class="gray">List of friends / acquiantances and relatives working in other insurance companies</span>
</div>
<br />
<div>
    <?php
    if (count($otherinfo1) > 0):
        foreach ($otherinfo1 as $data):
            ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="otherinfo1[name][]">
                        Name:                        
                    </label>
                    <div class="text-input-wrap"><?= $data['name'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="otherinfo1[relation][]">
                        Relation:
                    </label>
                    <div class="text-input-wrap"><?= $data['relation'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="otherinfo1[occupation][]">
                        Occupation:                        
                    </label>
                    <div class="text-input-wrap"><?= $data['occupation'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="otherinfo1[company][]">
                        Company:
                    </label>
                    <div class="text-input-wrap"><?= $data['company'] ?></div>
                </div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
