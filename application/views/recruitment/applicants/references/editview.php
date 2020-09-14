<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="form-multiple-add-references">
    <input type="hidden" class="add-more-flag" value="references" />
    <?php
    
    if (count($references) > 0):
        foreach ($references as $data):
            ?>
            <fieldset>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>            
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[name][]">
                        Name:
                        <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['name'] ?>" name="references[name][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="references[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['address'] ?>" name="references[address][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[telephone][]">
                        Contact Number:<span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" onkeydown="numeric_only(event)" value="<?= $data['telephone'] ?>" name="references[telephone][]"></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="references[email][]">
                        Email : <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['email'] ?>" name="references[email][]"></div>
                </div> 
                <div class="form-item even" style="display:none">
                    <label class="label-desc gray" for="references[occupation][]">
                        Occupation:
                        <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['occupation'] ?>" name="references[occupation][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[company_name][]">
                        Company:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['company_name'] ?>" name="references[company_name][]"></div>
                </div>
                <div class="form-item even" style="display:none">
                    <label class="label-desc gray" for="references[company_address][]">
                       Company Address:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['company_address'] ?>" name="references[company_address][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[position][]">
                        Position:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['position'] ?>" name="references[position][]"></div>
                </div>   
                                             
<!--                 <div class="form-item odd">
                    <label class="label-desc gray" for="references[years_known][]">
                        Years Known:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" onkeydown="numeric_only(event)" value="<?= $data['years_known'] ?>"  name="references[years_known][]">
                    </div>
                </div> -->
                <div class="form-item even">
                    <br/><br/><em>Note: Character reference should not be a relative, preferably the immediate superior or HR.</em>
                </div>
                <div class="clear"></div>
               
            </div>
            </fieldset>
            <div class="spacer"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
