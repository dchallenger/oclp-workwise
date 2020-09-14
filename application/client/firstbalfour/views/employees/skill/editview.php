<script>init_datepick;</script>
<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<div>
    <span style="font-weight:bold;font-size:14px" class="gray">Please indicate skills related to the position that you are applying for.</span>
</div>
<div class="form-multiple-add-skill">
    <input type="hidden" class="add-more-flag" value="skill" />
    <?php

    $ctr = 0;

    if (count($skill) > 0):
        foreach ($skill as $data):
            if(!isset($enable_edit) && ($enable_edit != 1)){?>
            <fieldset>
                 <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete">
                                <a href="javascript:void(0)" class="delete-detail">DELETE</a>
                            </span>
                        </div>
                    </h3>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="skill[skill_type_id][]">
                            Skill Type:
                        </label>
                        <div class="select-input-wrap">
                            <?php 
                                echo form_dropdown('skill[skill_type_id][]', $skill_type, $data['skill_type_id']);
                            ?>
                        </div>
                    </div>                    
                    <div class="form-item even">
                        <label class="label-desc gray" for="skill[remarks][]">
                            Remarks:
                        </label>
                        <div class="textarea-input-wrap">
                        <textarea id="skill_remarks" class="input-textarea" name="skill[remarks][]" rows="7"><?= $data['remarks'] ?></textarea>
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="skill[skill_name][]">
                            Skill Name:
                        </label>
                        <div class="text-input-wrap">
                            <input type="text" style="opacity:0.5;" readonly="readonly" class="input-text" value="<?= $data['skill_name'] ?>" name="skill[skill_name][]">
                        </div>                        
                    </div>                     
                    <div class="form-item odd">
                    <label class="label-desc gray" for="skill[proficiency][]">
                        Proficiency Level:
                    </label>
                        <div class="select-input-wrap">
                            <?php echo form_dropdown('skill[proficiency][]', array('Beginner' => 'Beginner', 'Intermediate' => 'Intermediate', 'Advance' => 'Advance'), $data['proficiency'], 'style="width:360px;"')?>
                        </div>
                    </div>
                    <div class="clear"></div>
                 </div>
                 <div class="clear"></div>   
            </fieldset>
             <?php 
                }else{
             ?>
              <fieldset>
                 <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete">
                                <a href="javascript:void(0)" class="delete-detail">DELETE</a>
                            </span>
                        </div>
                    </h3>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="skill[skill_type][]">
                            Skill Type:
                        </label>
                        <div class="text-input-wrap">
                            <input type="text" class="input-text" style="opacity:0.5;"  readonly="readonly" value="<?= $data['skill_type'] ?>" name="skill[skill_type][]">
                        </div>
                    </div>
                    <div class="form-item even">
                        <label class="label-desc gray" for="skill[remarks][]">
                            Remarks:
                        </label>
                        <div class="textarea-input-wrap">
                        <textarea id="skill_remarks" class="input-textarea" name="skill[remarks][]" rows="7" readonly='readonly'><?= $data['remarks'] ?></textarea>
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="skill[skill_name][]">
                            Skill Name:
                        </label>
                        <div class="text-input-wrap"><input type="text" style="opacity:0.5;"  readonly="readonly" class="input-text" value="<?= $data['skill_name'] ?>" name="skill[skill_name][]">
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="skill[proficiency][]">
                            Proficiency Level:
                        </label>
                        <div class="select-input-wrap">
                            <input type="hidden" class="active_hidden" name="skill[proficiency][]" value="<?= $data['proficiency'] ?>" />
                             <?php echo form_dropdown('skill[proficiency][]', array('Advance' => 'Advance', 'Beginner' => 'Beginner', 'Intermediate' => 'Intermediate' ), $data['proficiency'],'disabled="disabled" style="width:360px;"')?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>  
            </fieldset>
             <?php } ?>
        <?php 
          $ctr++;
        endforeach; ?>
    <?php endif; ?>
</div>
