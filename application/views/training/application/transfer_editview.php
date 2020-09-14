<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); $record_id = $this->input->post('record_id');?>
<div class="icon-label-group align-left">
	 <strong>How do you intend to transfer your knowledge/best practices gained from this program to your team?</strong>
	 
</div>
<div class="icon-label-group align-right">
<div class="form-item odd ">
    <div class="icon-label-group">
        <div style="display: block;" class="icon-label">
            <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add_row" rel="transfer" type="transfer">
                <span>Add Knowledge Transfer</span>
            </a>
        </div>
    </div>
</div> 
</div>
<div class="clear"></div>
<?php if ($record_id != '-1'): 
    $knowledge_transfer = json_decode($records->knowledge_transfer, true);

?>

<?php foreach ($knowledge_transfer['transfer'] as $id => $knowledge):?>
<div class="transfer_type">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row">DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="rating">
            Knowledge Transfer:
            <span class="red font-large">*</span>                                                        
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
        <label class="label-desc gray" for="remarks">
            Remarks:
        </label>
        <div class="text-input-wrap">   
            <textarea name="transfer[remarks][]" value="" class="input-text remarks"><?=$knowledge_transfer['remarks'][$id]?></textarea> 
        </div>
    </div>
    <div class="clear"></div>

</div> 
<?php 
        endforeach; ?>
<?php else:?>
    <div class="transfer_type">
    <h3 class="form-head">
        <div class="align-right">
            <!-- <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span> -->
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="transfer">
            Knowledge Transfer:
            <span class="red font-large">*</span>                                                        
        </label>
        <div class="select-input-wrap">
            <select name="transfer[transfer][]" class="transfer">
                <option value="">Select ... </option>
                <?php foreach ($transfers as $key => $transfer):?>
                <option value="<?=$transfer->training_knowledge_transfer_id?>"><?=$transfer->training_knowledge_transfer?> </option>
                <?php endforeach;?>
            </select>
        </div> 
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="remarks">
            Remarks:
        </label>
        <div class="text-input-wrap">   
           <textarea name="transfer[remarks][]" value="" class="input-text remarks"></textarea> 
        </div>
    </div>
    <div class="clear"></div>

</div>
<?php endif;?>

<div id="training-transfer"></div>
