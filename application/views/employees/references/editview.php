<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<fieldset>
<div class="form-multiple-add-references" >
    <input type="hidden" class="add-more-flag" value="references" />
    <?php
    if (count($references) > 0):
        foreach ($references as $data):
            if(!isset($enable_edit) && ($enable_edit != 1)){
            ?>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[name][]">
                        Name:                        
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
                    <label class="label-desc gray" for="references[company_name][]">
                        Company Name:            
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['company_name'] ?>" id="references[company_name][]" name="references[company_name][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="references[email_address][]">
                        Email Address:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['email_address'] ?>" id="references[email_address][]" name="references[email_address][]">
                    </div>
                </div>                  
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[telephone][]">
                        Telephone:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['telephone'] ?>" name="references[telephone][]"></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="references[occupation][]">
                        Occupation:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['occupation'] ?>" name="references[occupation][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[years_known][]">
                        Years Known:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['years_known'] ?>"  name="references[years_known][]">
                    </div>
                </div>
                 <div class="form-item even">
                    <label class="label-desc gray"> </label>
                    <div class="text-input-wrap"><br/><em>Note* Character reference should not be a relative.</em></div>
                </div>                
            </div>
            <div class="clear"></div>
            <?php 
                }else{
             ?>
             <h3 class="form-head">
                <div class="align-right">
                    <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                </div>
            </h3>
            <div class="form-multiple-add" style="display: block;">
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[name][]">
                        Name:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['name'] ?>" name="references[name][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="references[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['address'] ?>" name="references[address][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[telephone][]">
                        Telephone:
                    </label>
                    <div class="text-input-wrap"><input type="text"  readonly="readonly" style="opacity:0.5;" class="input-text" value="<?= $data['telephone'] ?>" name="references[telephone][]"></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="references[occupation][]">
                        Occupation:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['occupation'] ?>" name="references[occupation][]">
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="references[years_known][]">
                        Years Known:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['years_known'] ?>"  name="references[years_known][]">
                    </div>
                </div> 
                 <div class="form-item even">
                    <label class="label-desc gray"> </label>
                    <div class="text-input-wrap"><br/><em>Note* Character reference should not be a relative.</em></div>
                </div>               
            </div>
            <div class="clear"></div>
             <?php } ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</fieldset>
