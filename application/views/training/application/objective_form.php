<div class="objective_type">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="objective">
            Objective:<span class="red font-large">*</span>
        </label>
        <div class="text-input-wrap">   
           <input type="text" name="objective[objective][]" value="" class="input-text objective">
        </div>
    </div>

    <div class="form-item even ">
        <label class="label-desc gray" for="rating">
            Rating (Please do self-rate): <span class="red font-large">*</span>                                               
        </label>
        <div class="select-input-wrap">
            <select name="objective[rating][]" class="rating">
                <option value="">Select ... </option>
                <?php foreach ($ratings as $key => $rating):?>
                <option value="<?=$rating->training_rating_scale_id?>"><?=$rating->training_rating_scale?> - <?=$rating->description?></option>
                <?php endforeach;?>
            </select>
        </div> 
    </div>
    <div class="clear"></div>

</div>