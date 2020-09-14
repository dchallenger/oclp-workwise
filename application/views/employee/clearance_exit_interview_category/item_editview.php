<script>init_datepick;</script>
<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="icon-label-group">
   <div style="display: block;" class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="item"><span>Add Item</span></a></div>
</div>

<div class="form-multiple-add-item">

    <input type="hidden" class="add-more-flag" value="item" />

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
                                <input type="hidden" name="item[clearance_exit_interview_item_id][]" value="<?= $data['clearance_exit_interview_item_id'] ?>" />
                            </span>
                        </div>
                    </h3>
                    <div class="form-item odd ">
                        <label class="label-desc gray" for="date">Exit Interview Item No.:</label>
                        <div class="text-input-wrap">               
                            <input type="text" readonly="" class="input-text item_no" style="width:100px;" value="<?= $data['clearance_exit_interview_item_no'] ?>" name="item[clearance_exit_interview_item_no][]"  >
                        </div>                                    
                    </div>
                    <div class="form-item even">
                        <label class="label-desc gray" for="date">Exit Interview Item:<span class="red font-large">*</span></label>
                        <div class="text-input-wrap">               
                            <input type="text" class="input-text clearance_exit_interview_item" value="<?= $data['clearance_exit_interview_item'] ?>" id="clearance_exit_interview_item" name="item[clearance_exit_interview_item][]" >
                        </div>                                    
                    </div>
                    <div class="form-item odd" style="display: none;">
                        <label class="label-desc gray" for="date">Rating Type:<span class="red font-large">*</span></label>
                        <div class="text-input-wrap">
                        <?php if( $data['used'] == 0 ){ ?>
                           <select name="item[exit_interview_score_type][]" class="exit_interview_score_type" style="width:250px;">
                                <!-- <option value="">Please Select</option> -->
                                <?php 
                                $item_score_type = $data['exit_interview_score_type'];
                                foreach( $item_score_type_list as $item_score_type_info ){ ?>
                                    <option value="<?= $item_score_type_info['exit_interview_score_type_id'] ?>" <?php if( $item_score_type_info['exit_interview_score_type_id'] == $item_score_type ){ echo "selected"; } ?> ><?= $item_score_type_info['exit_interview_score_type'] ?></option>
                                <?php } ?>
                            </select>
                        <?php }else{ ?>
                            <?php 
                            $item_score_type = $data['exit_interview_score_type'];
                            foreach( $item_score_type_list as $item_score_type_info ){ ?>
                                <?php if( $item_score_type_info['exit_interview_score_type_id'] == $item_score_type ){  ?>
                                    <input type="text" class="input-text" readonly="" value="<?= $item_score_type_info['exit_interview_score_type'] ?>" />
                                    <input type="hidden" name="item[exit_interview_score_type][]" value="<?= $item_score_type_info['exit_interview_score_type_id'] ?>" />
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>   
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
<!--
<div class="icon-label-group">
   <div style="display: block;" class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="item"><span>Add Item</span></a></div>
</div>
-->
