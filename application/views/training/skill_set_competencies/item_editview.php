<script>init_datepick;</script>
<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="icon-label-group">
   <div style="display: block;" class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="item"><span>Add Criteria</span></a></div>
</div>

<div class="form-multiple-add-item">

    <input type="hidden" class="add-more-flag" value="item" />
    <input type="hidden" name="position_id" id="position_id" value="<?= $position_id ?>" />

    <fieldset>
    <?php 
        if (count($item) > 0):

        $item_count = 0;
        foreach ($item as $data):

            $item_rand = rand(1,10000000);

    ?>


                <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete">
                                <?php if( $data['used'] == 0 ){ ?>
                                <a href="javascript:void(0)" class="delete-detail" rel="item">DELETE</a>
                                <?php } ?>
                                <input type="hidden" name="item[position_skills_id][]" value="<?= $data['position_skills_id'] ?>" />
                                <input type="hidden" name="item[skills_item_id][]" value="<?= $data['skills_item_id'] ?>" />
                            </span>
                        </div>
                    </h3>
                    <div class="form-item odd ">
                        <label class="label-desc gray" for="date">Item No.:</label>
                        <div class="text-input-wrap">               
                            <input type="text" readonly="" class="input-text item_no" style="width:100px;" value="<?= $data['skills_item_no'] ?>" name="item[skills_item_no][]"  >
                        </div>                                    
                    </div>
                    <div class="form-item even">
                        <label class="label-desc gray" for="date">Criteria:<span class="red font-large">*</span></label>
                        <div class="text-input-wrap">               
                            <input type="text" class="input-text skills_item" value="<?= $data['skills_item'] ?>" id="skills_item" name="item[skills_item][]" <?php if( $data['used'] == 1 ){ ?>readonly=""<?php } ?>>
                        </div>                                    
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="date">Rating Type:<span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                        <?php if( $data['used'] == 0 ){ ?>
                           <select name="item[score_type][]" class="score_type" style="width:250px;">
                                <option value="">Please Select</option>
                                <?php 
                                $item_score_type = $data['score_type'];
                                foreach( $item_score_type_list as $item_score_type_info ){ ?>
                                    <option value="<?= $item_score_type_info['score_type_id'] ?>" <?php if( $item_score_type_info['score_type_id'] == $item_score_type ){ echo "selected"; } ?> ><?= $item_score_type_info['score_type'] ?></option>
                                <?php } ?>
                            </select>
                        <?php }else{ ?>
                            <?php 
                            $item_score_type = $data['score_type'];
                            foreach( $item_score_type_list as $item_score_type_info ){ ?>
                                <?php if( $item_score_type_info['score_type_id'] == $item_score_type ){  ?>
                                    <input type="text" class="input-text" readonly="" value="<?= $item_score_type_info['score_type'] ?>" />
                                    <input type="hidden" name="item[score_type][]" value="<?= $item_score_type_info['score_type_id'] ?>" />
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>   
                        </div>                                    
                    </div>
                    <div class="form-item even" <?php if( $data['score_type'] == 6 || $data['score_type'] == 3 ){ ?>style="display:none;"<?php } ?> >
                        <label class="label-desc gray" for="date">Weight:<span class="red font-large">*</span></label>
                        <div class="text-input-wrap">               
                            <input type="text" class="input-text item_weight" value="<?= $data['item_weight'] ?>" id="item_weight" name="item[item_weight][]" <?php if( $data['used'] == 1 ){ ?>readonly=""<?php } ?>>
                        </div>                                    
                    </div>
                    <div class="multiple_type" <?php if( $data['score_type'] != 6 ){ ?>style="display:none;"<?php } ?> >
                        <div class="form-item odd ">
                            <label class="label-desc gray" for="date">Sub Criteria 1:<span class="red font-large">*</span></label>
                            <div class="text-input-wrap">               
                                <input type="text" class="input-text sub_criteria" value="<?= $data['subcriteria1'] ?>" id="sub_criteria1" name="item[subcriteria][<?= $data['skills_item_no'] ?>][sub_criteria1]">
                            </div>                                      
                        </div>
                        <div class="form-item even ">
                            <label class="label-desc gray" for="date">Sub Criteria 2:<span class="red font-large">*</span></label>
                            <div class="text-input-wrap">               
                                <input type="text" class="input-text sub_criteria" value="<?= $data['subcriteria2'] ?>" id="sub_criteria2" name="item[subcriteria][<?= $data['skills_item_no'] ?>][sub_criteria2]">
                            </div>                                      
                        </div>
                        <div class="form-item odd ">
                            <label class="label-desc gray" for="date">Sub Criteria 3:<span class="red font-large">*</span></label>
                            <div class="text-input-wrap">               
                                <input type="text" class="input-text sub_criteria" value="<?= $data['subcriteria3'] ?>" id="sub_criteria3" name="item[subcriteria][<?= $data['skills_item_no'] ?>][sub_criteria3]">
                            </div>                                      
                        </div>
                        <div class="form-item even ">
                            <label class="label-desc gray" for="date">Sub Criteria 4:<span class="red font-large">*</span></label>
                            <div class="text-input-wrap">               
                                <input type="text" class="input-text sub_criteria" value="<?= $data['subcriteria4'] ?>" id="sub_criteria4" name="item[subcriteria][<?= $data['skills_item_no'] ?>][sub_criteria4]">
                            </div>                                      
                        </div>
                    </div>


                    <div class="clear"></div>
                </div>
                <div class="clear"></div>     

    <?php

        $item_count++;

        endforeach;
        endif;
    ?>
    </fieldset>
    <input type="hidden" class="item_count" value="<?php echo ( ( $item_count > 0 ) ? $item_count : 0 ); ?>" />
</div>
<h3 class="form-head"></h3>
<!-- temporary disable bottom add criteria
<div class="icon-label-group">
   <div style="display: block;" class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="item"><span>Add Criteria</span></a></div>
</div>
-->
