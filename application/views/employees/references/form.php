<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); 
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
        <label class="label-desc gray" for="references[company_name][]">
            Company Name:            
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" id="references[company_name][]" name="references[company_name][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="references[email_address][]">
            Email Address:
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" id="references[email_address][]" name="references[email_address][]">
        </div>
    </div>    
    <div class="form-item odd">
        <label class="label-desc gray" for="references[telephone][]">
            Telephone:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" id="references[telephone][]" name="references[telephone][]"></div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="references[occupation][]">
            Occupation:            
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" id="references[occupation][]" name="references[occupation][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="references[years_known][]">
            Years Known:
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" id="references[years_known][]" name="references[years_known][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray"> </label>
        <div class="text-input-wrap"><br/><em>Note* Character reference should not be a relative.</em></div>
    </div>
</div>
<div class="clear"></div>