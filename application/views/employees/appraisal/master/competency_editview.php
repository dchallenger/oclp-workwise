<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="icon-label-group">
    <div class="icon-label add-more-div" style="display: block;">
        <a rel="level" class="icon-16-add icon-16-add-listview add-more" href="javascript:void(0);" count="<?=$rand?>">
            <span>Add Level</span>
        </a>
    </div>
</div>


<?php 
    if ($this->input->post('record_id') != -1):
        if ($competency_levels):
            foreach ($competency_levels as $key => $level):?>
        <div class="form-multiple-add-level" style="display: block;">   
            <h3 class="form-head">
                <div class="align-right">
                    <span class="fh-delete">
                        <a href="javascript:void(0)" class="delete-level" rel="item">DELETE</a>
                        <input type="hidden" name="" value="" />
                    </span>
                </div>
            </h3>

            <div class="level_div">
                <div class="form-item odd ">
                    <label class="label-desc gray" for="date">Level:<span class="red font-large">*</span></label>
                    <div class="text-input-wrap">               
                        <input type="text" class="input-text competency_level" value="<?=$level->appraisal_competency_level?>" competency-level="<?=$level->appraisal_competency_level_id?>" name="old_competency_level[<?=$level->appraisal_competency_level_id?>]">
                    </div>                                    
                </div>
                <div class="form-item even ">
                    <label class="label-desc gray" for="date">Description:<span class="red font-large">*</span></label>
                    <div class="text-input-wrap">               
                        <textarea class="input-text competency_level_description" name="old_competency_level_description[<?=$level->appraisal_competency_level_id?>]"><?=$level->description?></textarea>
                    </div>                                    
                </div>
             </div> 
            <div class="clear"></div>
        </div>
         
<?php
            endforeach;
       endif;
    endif;
?>
<div id="competency-div"></div>
