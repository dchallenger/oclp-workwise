<div class="form-multiple-add-category" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail" rel="category">DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
        </div>
    </h3>
    <div class="form-item odd ">
        <label class="label-desc gray" for="date">Category:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text category_name" value=""  name="category[revalida_category][]"  >
        </div>                                    
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="date">Weight:<span class="red font-large">*</span></label>
        <div class="text-input-wrap">               
            <input type="text" class="input-text category_weigth" value="" name="category[revalida_category_weight][]">
        </div>                                    
    </div>

    <div class="icon-label-group">
       <div style="display: block;" class="icon-label add-more-div">
            <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="item">
                <span>Add Item</span>
            </a>
        </div>
    </div>

    <div class="form-multiple-add-item-group" >
        <input type="hidden" class="item_count" value="0" />
        <input type="hidden" class="category_rand" name="category[item_rand][]" value="<?= $category_rand; ?>" />
        <fieldset class="item">
    	</fieldset>
    </div>

    <div class="clear"></div>
</div> 