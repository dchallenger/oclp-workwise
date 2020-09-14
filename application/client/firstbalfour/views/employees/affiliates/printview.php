<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php


    if (count($affiliates) > 0):
        foreach ($affiliates as $data):

            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[company][]">
                        Name of Affiliation:
                    </label>
                    <div class="text-input-wrap"><?= $data['name'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[address][]">
                        Position:
                    </label>
                    <div class="text-input-wrap"><?= $data['position'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[date_joined][]">
                        Date Joined:
                    </label>
                    <div class="text-input-wrap">
                        <?php if($data['date_joined'] != "0000-00-00"){  ?>
                        <?= date('F Y', strtotime($data['date_joined'])); ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[active][]">
                        Status:
                    </label>
                    <div class="text-input-wrap"><?php if( isset($data['active']) && $data['active'] == 1 ){ ?>Yes<?php }else{ ?>No<?php } ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[date_resigned][]">
                        Date Resigned:
                    </label>
                    <div class="text-input-wrap">
                        <?php if($data['date_resigned'] != "0000-00-00"){  ?>
                        <?= date('F Y', strtotime($data['date_resigned'])); ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
