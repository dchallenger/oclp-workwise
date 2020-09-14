<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); $record_id = $this->input->post('record_id');?>
<div class="icon-label-group align-left">
	 <strong>How do you intend to apply your learning from this program back to your job?</strong>
</div>
<div class="icon-label-group align-right">
<div class="form-item odd ">
    <div class="icon-label-group">
        <div style="display: block;" class="icon-label">
            <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add_row" rel="action" type="action">
                <span>Add Action Plan</span>
            </a>
        </div>
    </div>
</div> 
</div>
<div class="clear"></div>
<?php if ($record_id != '-1'): 
    $action_plan = json_decode($records->action_plan, true);
?>

<?php foreach ($action_plan['plan'] as $id => $plan):?>
<div class="action_type">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="action">
            Action Plan:<span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="action[plan][]" value="<?=$plan?>" class="input-text action">
        </div>
    </div>

    <div class="form-item even ">
        <label class="label-desc gray" for="objective">
            Remarks: 
        </label>
        <div class="text-input-wrap">   
            <textarea name="action[remarks][]" value="" class="input-text remarks"><?=$action_plan['remarks'][$id]?></textarea>
        </div>
    </div>
    <div class="clear"></div>

</div>
<?php 
        endforeach; ?>
<?php else:?>
<div class="action_type">
    <h3 class="form-head">
        <div class="align-right">
            <!-- <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span> -->
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="objective">
            Action Plan:<span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="action[plan][]" value="" class="input-text action">
        </div>
    </div>

    <div class="form-item even ">
        <label class="label-desc gray" for="objective">
            Remarks: 
        </label>
        <div class="text-input-wrap">   
           <textarea name="action[remarks][]" value="" class="input-text remarks"></textarea>
        </div>
    </div>
    <div class="clear"></div>

</div>

<?php endif;?>

<div id="training-action"></div>