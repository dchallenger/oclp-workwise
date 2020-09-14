<script>init_datepick;</script>
<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="icon-label-group">
    <div style="display: block;" class="icon-label add-more-div">
        <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="category">
            <span>Add Category</span>
        </a>
    </div>
</div>

<div class="form-multiple-add-category-group">
    <input type="hidden" class="draft" name="draft" value="0" />
    <input type="hidden" class="category_count" value="<?= count($category); ?>" />
    <fieldset class="category">

        <?php 
            if (count($category) > 0):

            $category_count = 0;
            foreach ($category as $category_data):

        ?>



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
                    <input type="text" class="input-text category_name" value="<?= $category_data['revalida_category'] ?>"  name="category[revalida_category][]"  >
                </div>                                    
            </div>
            <div class="form-item even">
                <label class="label-desc gray" for="date">Weight:<span class="red font-large">*</span></label>
                <div class="text-input-wrap">               
                    <input type="text" class="input-text category_weigth" value="<?= $category_data['revalida_category_weight'] ?>" name="category[revalida_category_weight][]">
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
                <input type="hidden" class="item_count" value="<?= count($category_data['items']) ?>" />
                <input type="hidden" class="category_rand" name="category[item_rand][]" value="<?= $category_data['category_rand']; ?>" />
                <fieldset class="item">

                    <?php

                        if (count($category_data['items']) > 0):

                        $item_count = 0;
                        foreach ($category_data['items'] as $item_data):

                    ?>


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
                                <input type="text" readonly="" style="width:100px;" class="input-text item_no" value="<?= $item_data['training_revalida_item_no']; ?>" name="category[<?= $category_data['category_rand']; ?>][training_revalida_item_no][]"  >
                            </div>                                    
                        </div>
                        <div class="form-item even">
                            <label class="label-desc gray" for="date">Description:<span class="red font-large">*</span></label>
                            <div class="text-input-wrap">               
                                <input type="text" class="input-text item_description" value="<?= $item_data['description']; ?>" name="category[<?= $category_data['category_rand']; ?>][description][]">
                            </div>                                    
                        </div>
                        <div class="form-item odd ">
                            <label class="label-desc gray" for="date">Rating Type:<span class="red font-large">*</span></label>
                            <div class="text-input-wrap">               
                                <select class="score_type item_score_type" style="width:250px;" name="category[<?= $category_data['category_rand']; ?>][score_type][]">
                                    <option value="" selected>Please Select</option>
                                    <?php 
                                    foreach( $item_score_type_list as $item_score_type_info ){ ?>
                                        <option value="<?= $item_score_type_info['score_type_id'] ?>" <?php if( $item_data['score_type'] == $item_score_type_info['score_type_id'] ){ ?>selected<?php } ?> ><?= $item_score_type_info['score_type'] ?></option>
                                    <?php 
                                    } 
                                    ?>
                                </select>
                            </div>                                    
                        </div>
                        <div class="form-item even">
                            <label class="label-desc gray" for="date">Weight:<span class="red font-large">*</span></label>
                            <div class="text-input-wrap">               
                                <input type="text" class="input-text item_weigth" value="<?= $item_data['item_weight']; ?>" name="category[<?= $category_data['category_rand']; ?>][item_weigth][]">
                            </div>                                    
                        </div>
                        <div class="clear"></div>
                    </div>

                    <?php

                        $item_count++;

                        endforeach;
                        endif;
                    ?>

                </fieldset>
            </div>

            <div class="clear"></div>
        </div>

        <?php

            $category_count++;

            endforeach;
            endif;
        ?>


    <fieldset>

</div>


<h3 class="form-head"></h3>
<!--
<div class="icon-label-group">
    <div style="display: block;" class="icon-label add-more-div">
        <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="category">
            <span>Add Category</span>
        </a>
    </div>
</div>
-->
