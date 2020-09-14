<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<fieldset>
<div class="form-multiple-add-insurance" >
    <input type="hidden" class="add-more-flag" value="insurance" />
    <?php
    if (count($insurance) > 0):
        foreach ($insurance as $data):
            if(!isset($enable_edit) && ($enable_edit != 1)){
            ?>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="insurance[company][]">
                        Insurance Company:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text" value="<?= $data['company'] ?>" name="insurance[company][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="insurance[type][]">
                        Insurance Type:
                    </label>
                    <div class="select-input-wrap">
                        <?php echo form_dropdown('insurance[type][]', array('1' => 'Life Insurance', '2' => 'Non-Life Insurance' ), $data['type'])?>
                    </div>
                </div>                
            </div>
            <div class="clear"></div>
            <?php 
                }else{
             ?>
             <h3 class="form-head">
                <div class="align-right">
                    <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                </div>
            </h3>
            <div class="form-multiple-add" style="display: block;">
                <div class="form-item odd">
                    <label class="label-desc gray" for="insurance[company][]">
                        Insurance Company:                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text"  readonly="readonly" style="opacity:0.5;" value="<?= $data['company'] ?>" name="insurance[company][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="insurance[relation][]">
                        Insurance Type:
                    </label>
                    <div class="select-input-wrap">
                       <?php echo form_dropdown('insurance[type][]', array('1' => 'Life Insurance', '2' => 'Non-Life Insurance' ), $data['type'],'disabled="disabled"')?>
                        <input type="hidden" class="active_hidden" name="insurance[type][]" value="<?= $data['type'] ?>" />
                    </div>
                </div>             
            </div>
            <div class="clear"></div>
             <?php } ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</fieldset>
