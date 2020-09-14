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
        <label class="label-desc gray" for="cost_center[cost_center_id][]">
            Main Cost Center:
        </label>
        <div class="select-input-wrap">
            <?php echo form_dropdown('cost_center[cost_center_id][]', $cost_center_list, '', 'style="width:425px;"')?>
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="cost_center[percentage][]">
            Percentage:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text cost_center_percentage" style="width:19%" value="" name="cost_center[percentage][]"><span>&nbsp; &#37;</span>
        </div>
    </div>
    <div class="clear"></div>
</div>