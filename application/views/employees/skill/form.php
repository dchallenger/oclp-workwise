<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); 
?>

<div class="form-multiple-add" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
        </div>
    </h3>
    <div class="form-item odd">
        <label class="label-desc gray" for="skill[skill_type][]">
            Skill Type:
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="<?= $data['skill_type'] ?>" name="skill[skill_type][]">
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
            <input type="text" class="input-text" value="<?= $data['skill_name'] ?>" name="skill[skill_name][]">
        </div>
    </div>       
    <div class="form-item odd">
        <label class="label-desc gray" for="skill[proficiency][]">
            Proficiency Level:
        </label>
            <div class="select-input-wrap">
                <?php echo form_dropdown('skill[proficiency][]', array('Beginner' => 'Beginner', 'Intermediate' => 'Intermediate', 'Advance' => 'Advance'), $data['proficiency'], 'style="width:425px;"')?>
            </div>
    </div>
    
    <div class="clear"></div>
    <hr />
</div>