<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="form-multiple-add-referral">
    <input type="hidden" class="add-more-flag" value="referral" />
    <?php
    
    if (count($referral) > 0):
        foreach ($referral as $data):
            ?>
            <fieldset>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>            
                <div class="form-item odd">
                    <label class="label-desc gray" for="referral[name][]">
                        Name:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['name'] ?>" name="referral[name][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="referral[position][]">
                         Position Applied for:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['position'] ?>" name="referral[position][]"></div>
                </div>  
                <div class="form-item odd">
                    <label class="label-desc gray" for="referral[contact_no][]">
                        Contact Number:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" onkeydown="numeric_only(event)" value="<?= $data['contact_no'] ?>" name="referral[contact_no][]"></div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="referral[email][]">
                       E-mail:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['email'] ?>" name="referral[email][]"></div>
                </div>                                             
<!--                 <div class="form-item odd">
                    <label class="label-desc gray" for="referral[years_known][]">
                        Years Known:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" onkeydown="numeric_only(event)" value="<?= $data['years_known'] ?>"  name="referral[years_known][]">
                    </div>
                </div> -->
                <div class="clear"></div>
               
            </div>
            </fieldset>
            <div class="spacer"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

