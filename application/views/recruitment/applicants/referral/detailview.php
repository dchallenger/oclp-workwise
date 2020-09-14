<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($referral) > 0):
        foreach ($referral as $data):
            ?>
            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="referral[name][]">
                        Name:
                    </label>
                    <div class="text-input-wrap"><?= $data['name'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="referral[position][]">
                        Position Applied for:
                    </label>
                    <div class="text-input-wrap"><?= $data['position'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="referral[contact_no][]">
                         Contact Number:
                    </label>
                    <div class="text-input-wrap"><?= $data['contact_no'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="referral[email][]">
                        E-mail:
                    </label>
                    <div class="text-input-wrap"><?= $data['email'] ?></div>
                </div>
            </div>
            <div class="clear"></div>
         
        <?php endforeach; ?>
    <?php endif; ?>
</div>
