<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="icon-label-group align-left">
	 <strong>How do you intend to transfer your knowledge/best practices gained from this program to your team?</strong>
	 
</div>
<div class="clear"></div>
<h3>&nbsp;</h3>
<div class="clear"></div>

<?php if ($record_id != '-1'): 
    $knowledge_transfer = json_decode($records->knowledge_transfer, true);

?>

<?php foreach ($knowledge_transfer['transfer'] as $id => $knowledge):?>
<div class="transfer_type">
    <div class="form-item odd view ">
        <label class="label-desc gray view" for="rating">Knowledge Transfer:</label>
        <div class="text-input-wrap">
            <?php foreach ($transfers as $key => $transfer):?>
                <?=($transfer->training_knowledge_transfer_id == $knowledge) ? $transfer->training_knowledge_transfer : '' ?>
            <?php endforeach;?>

        </div> 
    </div>
    <div class="form-item even view">
        <label class="label-desc gray view" for="remarks">
            Remarks:
        </label>
        <div class="text-input-wrap"><?=$knowledge_transfer['remarks'][$id]?></div>
    </div>
    <div class="clear"></div>

</div> 
<?php 
        endforeach;
    endif;?>

<div id="training-transfer"></div>
