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
        <label class="label-desc gray" for="affiliates[name][]">
            Name of Affiliation:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" style="width:40%" name="affiliates[name][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="affiliates[active][]">
            Status
        </label>
        <div class="text-input-wrap">
            <input type="radio" name="active_radio[<?php echo $rand; ?>][]" value="1" class="affiliates_active" style="width:10%"/>Active
            <input type="radio" name="active_radio[<?php echo $rand; ?>][]" value="0" class="affiliates_active" style="width:10%" checked="checked" />Resigned
            <input type="hidden" class="active_hidden" name="affiliates[active][]" value="0" />
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="affiliates[position][]">
            Position:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" style="width:40%" value="" name="affiliates[position][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="education[date_resigned][]">
            Date Resigned:
        </label>                
        <div class="text-input-wrap">                                          
            <input type="text" name="affiliates[date_resigned][]" value="" id="affiliates_date_resigned" class="input-text month-year date_from date_resigned" />
        </div>                
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="affiliates[date_joined][]">
            Date Joined:
        </label>
        <div class="text-input-wrap">
             <input type="text" name="affiliates[date_joined][]" id="" value="" class="input-text month-year date_from" />
        </div>
    </div>
    
   
    <div class="clear"></div>
    <hr />
</div>