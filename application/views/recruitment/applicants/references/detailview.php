<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($references) > 0):
        foreach ($references as $data):
            ?>
            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="references[name][]">
                        Name:
                        <span class="red font-large">*</span>
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
                    <label class="label-desc view gray" for="references[telephone][]">
                        Contact Number: 
                    </label>
                    <div class="text-input-wrap"><?= $data['telephone'] ?></div>
                </div>
                <div class="form-item view even" style="display:none">
                    <label class="label-desc view gray" for="references[occupation][]">
                        Occupation:
                        
                    </label>
                    <div class="text-input-wrap"><?= $data['occupation'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="references[email][]">
                        Email :
                    </label>
                     <div class="text-input-wrap"><?= $data['email'] ?></div>
                    
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="references[company_name][]">
                        Company Name:
                    </label>
                    <div class="text-input-wrap"><?= $data['company_name'] ?></div>
                </div>
                
                <div class="form-item view even" style="display:none">
                    <label class="label-desc view gray" for="references[company_address][]">
                        Company Address:
                        <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><?= $data['company_address'] ?></div>
                </div>

                <div class="form-item view odd">
                    <label class="label-desc view gray" for="references[position][]">
                        Position:
                    </label>
                    <div class="text-input-wrap"><?= $data['position'] ?></div>
                </div>

                                
                <div class="form-item even">
                    <br/><br/><em>Note* Character reference should not be a relative.</em>
                </div>
            </div>
            <div class="clear"></div>
         
        <?php endforeach; ?>
    <?php endif; ?>
</div>
