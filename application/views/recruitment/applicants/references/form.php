<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
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
            <input type="text" class="input-text" value="" id="references[name][]" name="references[name][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="references[address][]">
            Address:
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" id="references[address][]" name="references[address][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="references[telephone][]">
             Contact Number: <span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" id="references[telephone][]" name="references[telephone][]"></div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="references[email][]">
            Email : <span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="references[email][]"></div>
    </div> 

    <div class="form-item even" style="display:none">
        <label class="label-desc gray" for="references[occupation][]">
            Occupation:
            <span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" id="references[occupation][]" name="references[occupation][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="references[company_name][]">
            Company Name:
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
<!--     <div class="form-item odd">
        <label class="label-desc gray" for="references[years_known][]">
            Years Known:
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" id="references[years_known][]" name="references[years_known][]">
        </div>
    </div> -->
     <div class="form-item even">
        <br/><br/><em>Note: Character reference should not be a relative, preferably the immediate superior or HR.</em>
    </div>
    <div class="clear"></div>    
</div>