<div class="transfer_type">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
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