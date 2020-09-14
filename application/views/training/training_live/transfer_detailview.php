<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="icon-label-group align-left">
	Did the employee transfer the knowledge / best practices gained from the training to your team?	 
    <h3>&nbsp;</h3>
</div>

<div class="clear"></div>
<div class="clear"></div>

<?php if ($record_id != '-1'): 
    $knowledge_transfer = json_decode($records->knowledge_transfer, true);

?>

<?php foreach ($knowledge_transfer['transfer'] as $id => $knowledge):?>
<div class="transfer_type">
    <div class="form-item odd view">
        <label class="label-desc gray view" for="rating">
            Knowledge Transfer:
                                                 
        </label>
        <div class="input-input-wrap">
            <?php foreach ($transfers as $key => $transfer):?>
                <?=($transfer->training_knowledge_transfer_id == $knowledge) ? $transfer->training_knowledge_transfer : '' ?>
            <?php endforeach;?>
        </div> 
    </div>
    <div class="form-item even view ">
        <label class="label-desc gray view" for="date_complete">
            Date Completed :
        </label>
        <div class="text-input-wrap">
        <?php if($knowledge_transfer['date_complete'][$id]):?>
            <?=date('d F Y', strtotime($knowledge_transfer['date_complete'][$id]))?>
        <?php endif;?>    
        </div>
    </div>
    <div class="form-item odd view">
        <label class="label-desc gray view" for="remarks">
            Remarks:
        </label>
        <div class="text-input-wrap"><?=$knowledge_transfer['remarks'][$id]?></div>
    </div>
    <div class="clear"></div>
</div> 
    
<div class="clear"></div>
<h3 class="form-head">&nbsp;</h3>
<div class="clear"></div>
<?php 
        endforeach;
    endif;?>

<div id="training-transfer"></div>
