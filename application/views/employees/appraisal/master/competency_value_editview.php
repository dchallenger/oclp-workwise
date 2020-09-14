<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<div class="form-item odd ">
    <div class="icon-label-group">
        <div style="display: block;" class="icon-label add-more-div">
            <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add_row" rel="value">
                <span>Add Value</span>
            </a>
        </div>
    </div>
</div> 
<input type="hidden" id="counter" value="1">

<?php 
    if ($this->input->post('record_id') != -1):
        if ($competency_values && $competency_values->num_rows() > 0):
            foreach ($competency_values->result() as $key => $value):?>
            <div class="competency_values">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete">
                            <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                            <input type="hidden" name="" value="" />
                        </span>
                    </div>
                </h3>
                <div class="form-item odd ">
                    <label class="label-desc gray" for="question">
                        Value:
                        <span class="red font-large">*</span>                                                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text competency" value="<?=$value->competency_value?>" name="old_competency_value[<?=$value->competency_value_id?>]" competency-value="<?=$value->competency_value_id?>" style="width:100%">
                    </div> 
                </div>
                <div class="form-item even ">
                    <label class="label-desc gray" for="tooltip">
                        Tooltip/Description:
                    </label>
                    <div class="textarea-input-wrap">
                        <textarea class="input-textarea tooltip" name="old_competency_value_description[<?=$value->competency_value_id?>]" tabindex="4" rows="5" style="width:100%"><?=$value->competency_value_description?></textarea>
                    </div>
                </div>
                <!-- <div class="form-item even ">
                    <label class="label-desc gray" for="placeholder">
                        Results Placeholder:
                    </label>
                    <div class="textarea-input-wrap">
                        <textarea class="input-textarea tooltip" name="old_competency_placeholder[<?=$value->competency_value_id?>]" tabindex="4" rows="5" style="width:100%"><?=$value->results_placeholder?></textarea>
                    </div>
                </div> -->
                <div class="clear"></div>
            </div>            
<?php
            endforeach;
       endif;
    endif;
?>

<div id="competency_div"></div>
