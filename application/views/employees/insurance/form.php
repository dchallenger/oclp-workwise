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
        <label class="label-desc gray" for="insurance[company][]">
            Insurance Company:            
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" id="insurance[company][]" name="insurance[company][]">
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="insurance[type][]">
            Insurance Type:
        </label>
        <div class="select-input-wrap">
           <?php echo form_dropdown('insurance[type][]', array('1' => 'Life Insurance', '2' => 'Non-Life Insurance' ), $data['type'])?>
        </div>
    </div>
</div>
<div class="clear"></div>