<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="icon-label-group align-left">
	 <strong>How do you intend to apply your learning from this program back to your job?</strong>
</div>
<div class="clear"></div>
<h3>&nbsp;</h3>
<div class="clear"></div>

<?php if ($record_id != '-1'): 
    $action_plan = json_decode($records->action_plan, true);
?>

<?php foreach ($action_plan['plan'] as $id => $plan):?>
<div class="action_type">
    <div class="form-item view odd ">
        <label class="label-desc view gray" for="action">
            Action Plan:
        </label>
        <div class="text-input-wrap"><?=$plan?></div>
    </div>

    <div class="form-item even view">
        <label class="label-desc view gray" for="objective">
            Remarks: 
        </label>
        <div class="text-input-wrap"><?=$action_plan['remarks'][$id]?></div>
    </div>
    <div class="clear"></div>

</div>
<?php 
        endforeach;
    endif;?>

<div id="training-action"></div>