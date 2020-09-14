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
    <div class="form-item odd ">
        <label class="label-desc gray" for="rating">
            Knowledge Transfer:
                                                 
        </label>
        <div class="select-input-wrap">
            <select name="transfer[transfer][]" class="transfer">
                <option value="">Select ... </option>
                <?php foreach ($transfers as $key => $transfer):?>
                <option value="<?=$transfer->training_knowledge_transfer_id?>" <?=($transfer->training_knowledge_transfer_id == $knowledge) ? 'SELECTED="SELECTED"' : '' ?> ><?=$transfer->training_knowledge_transfer?> </option>
                <?php endforeach;?>
            </select>
        </div> 
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date_complete">
            Date Completed :
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="transfer[date_complete][]" value="<?=$knowledge_transfer['date_complete'][$id]?>" class="input-text datepicker date date_complete">
        </div>
    </div>
    <div class="form-item odd ">
        <label class="label-desc gray" for="remarks">
            Remarks:
        </label>
        <div class="text-input-wrap">   
           <textarea name="transfer[remarks][]" class="input-text remarks"><?=$knowledge_transfer['remarks'][$id]?></textarea>
        </div>
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
