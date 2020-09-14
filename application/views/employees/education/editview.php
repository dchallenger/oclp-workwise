<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="clear"></div>
<div class="form-multiple-add-education" >
    <input type="hidden" class="add-more-flag" value="education" />
    <input type="hidden" class="" id="no_education" value="<?php echo (count($education) > 0 ? count($education) : 0)?>" />
    <?php
    $count = count($education);    
    if ($count > 0): 
        $no = 0;       
        foreach ($education as $index => $data): ?>
            <fieldset>
                <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                        </div>
                    </h3>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="education[education_level][]">
                            Educational Attainment:
                        </label>
                        <div class="select-input-wrap">
                            <?php 
                            $options = array(
                                        '' => 'Select&hellip;',
                                        'Tertiary' => array('10' => 'College', '11' => 'Graduate Studies', '12' => 'Vocational'),
                                        'Secondary' => array('9' => 'Highschool'), 
                                        'Primary' => array('8' => 'Elementary')
                                     );
                            echo form_dropdown('education[education_level][]', $options, $data['option_id'])?>
                        </div>
                    </div>
                    <div class="form-item even">
                        <label class="label-desc gray" for="education[school][]">
                            School:
                        </label>
                        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['school'] ?>" name="education[school][]">
                        </div>
                    </div>
                    <div class="form-item odd <?= ($data['option_id'] == 10 || $data['option_id'] == 11 || $data['option_id'] == 12 || $data['option_id'] == 9 ? '' : 'hidden') ?>">
                        <label class="label-desc gray">
                            &nbsp;
                        </label>                    
                        <div class="radio-input-wrap">
                            <?=form_radio('education[graduate][' . --$count . ']', 1, ($data['graduate'] == '1' ? true : false), 'class="radioG"')?>Graduate
                            <?=form_radio('education[graduate][' . $count .']', 0, ($data['graduate'] == '0' ? true : false), 'class="radioUG"')?>Undergraduate
                        </div>                
                    </div>                     
                    <div class="form-item even <?= ($data['option_id'] == 10 || $data['option_id'] == 11 || $data['option_id'] == 12 ? '' : 'hidden') ?>">
                        <label class="label-desc gray" for="education[degree][]">
                            Degree Obtained:
                        </label>
                        <div class="text-input-wrap">
                            <input type="text" class="input-text" value="<?= $data['degree'] ?>" name="education[degree][]" <?= ($data['graduate'] == 1 ? '' : 'readonly="false"'); ?> >
                        </div>
                    </div>
                    <div class="form-item odd <?= ($data['option_id'] == 10 || $data['option_id'] == 11 || $data['option_id'] == 12 ? '' : 'hidden') ?>">
                        <label class="label-desc gray" for="education[course][]">
                            Course Taken:
                        </label>
                        <div class="text-input-wrap">
                            <input type="text" class="input-text" value="<?= $data['course'] ?>" name="education[course][]">
                        </div>
                    </div>                   
                    <div class="form-item even">
                        <label class="label-desc gray" for="education[honors_received][]">
                            Honors Received:
                        </label>
                        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['honors_received'] ?>" name="education[honors_received][]">
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="education[date_from][]">
                            Date From:
                        </label>                
                        <div class="text-input-wrap">            
                            <input type="text" name="education[date_from][]" id="" value="<?=($data['date_from'] != '0000-00-00') ? date('F Y', strtotime($data['date_from'])) : ''?>" class="input-text month-year date_from"/>
                            &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
                            <input type="text" name="education[date_to][]" id="" value="<?=($data['date_to'] != '0000-00-00') ? date('F Y', strtotime($data['date_to'])) : ''?>" class="input-text month-year date_from" />
                        </div>                
                    </div>                
                    <div class="clear"></div>
                </div>                
    		</fieldset>
            <div class="spacer"></div>
            <?php $no++;  ?>
		<?php endforeach; ?>
    <?php endif; ?>
</div>