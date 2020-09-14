<div class="form-multiple-add" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail" rel="budget">DELETE</a>
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="date">Training Cost Name:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text training_cost_name" value="<?= $data['training_cost_name'] ?>" id="training_cost_name" name="budget[training_cost_name][]">
        </div>                                    
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date">Investment Cost:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text cost" style="width:20%;" value="0.00" name="budget[cost][]">
        </div>                                    
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="date">Remarks:</label>
        <div class="select-input-wrap">               
           <textarea name="budget[remarks][]" class="remarks" ></textarea>
        </div>                                    
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date">No. of Particulars:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text pax" style="width:20%;" value="0" name="budget[pax][]">
        </div>                                    
    </div>
    <div class="form-item even ">
        <label class="label-desc gray" for="date">Total:</label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text total" readonly="" style="width:20%;" value="0.00" name="budget[total][]">
        </div>                                    
    </div>
    <div class="clear"></div>
</div>
<div class="clear"></div>  
            