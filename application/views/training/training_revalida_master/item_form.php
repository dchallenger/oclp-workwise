<div class="form-multiple-add-item" >
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail" rel="item">DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="date">Item No.:</label>
        <div class="text-input-wrap">               
            <input type="text" readonly="" style="width:100px;" class="input-text item_no" value="<?= $item_count; ?>" name="category[<?= $category_rand ?>][training_revalida_item_no][]"  >
        </div>                                    
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="date">Description:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text item_description" value="" id="" name="category[<?= $category_rand ?>][description][]">
        </div>                                    
    </div>
    <div class="form-item odd ">
        <label class="label-desc gray" for="date">Rating Type:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <select class="score_type item_score_type" style="width:250px;" name="category[<?= $category_rand ?>][score_type][]">
                <option value="" selected>Please Select</option>
                <?php 
                foreach( $item_score_type_list as $item_score_type_info ){ ?>
                    <option value="<?= $item_score_type_info['score_type_id'] ?>" ><?= $item_score_type_info['score_type'] ?></option>
                <?php 
                } 
                ?>
            </select>
        </div>                                    
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="date">Weight:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text item_weigth" value="" name="category[<?= $category_rand ?>][item_weigth][]">
        </div>                                    
    </div>
    <div class="clear"></div>
</div>
 