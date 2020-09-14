<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

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
            <input type="text" class="input-text" value="" name="referral[name][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="referral[position][]">
            Position Applied for:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['position'] ?>" name="referral[position][]"></div>
    </div>  
    <div class="form-item odd">
        <label class="label-desc gray" for="referral[contact_no][]">
            Contact Number:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" onkeydown="numeric_only(event)" name="referral[contact_no][]"></div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="referral[email][]">
            E-mail:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value=""  name="referral[email][]"></div>
    </div>
    <div class="clear"></div>    
</div>