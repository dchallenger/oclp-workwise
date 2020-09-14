<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($references) > 0):
        foreach ($references as $data):
            ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="references[name][]">
                        Name:                        
                    </label>
                    <div class="text-input-wrap"><?= $data['name'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="references[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap"><?= $data['address'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="references[company_name][]">
                        Company Name:                        
                    </label>
                    <div class="text-input-wrap"><?= $data['company_name'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="references[email_address][]">
                        Email Address:
                    </label>
                    <div class="text-input-wrap"><?= $data['email_address'] ?></div>
                </div>                
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="references[telephone][]">
                        Telephone:
                    </label>
                    <div class="text-input-wrap"><?= $data['telephone'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="references[occupation][]">
                        Occupation:                        
                    </label>
                    <div class="text-input-wrap"><?= $data['occupation'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="references[years_known][]">
                        Years Known:
                    </label>
                    <div class="text-input-wrap"><?= $data['years_known'] ?></div>
                </div>
                <div class="form-item even">
                    <br/><br/><em>Note* Character reference should not be a relative.</em>
                </div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
