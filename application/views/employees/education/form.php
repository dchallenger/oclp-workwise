<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); 

    $key = strtotime(date('Y-m-d g:i:s')); ?>
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
            echo form_dropdown('education[education_level][]', $options)?>
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="education[school][]">
            School:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="education[school][]">
        </div>
    </div>    
    <div class="form-item odd hidden">
        <label class="label-desc gray">
            &nbsp;
        </label>
        <div class="radio-input-wrap">
            <input type="radio" name="education[graduate][<?= $key ?>]" value="1" class="radioG"/>Graduate
            <input type="radio" name="education[graduate][<?= $key ?>]" value="0" class="radioUG"/>Undergraduate
        </div>                
    </div>     
    <div class="form-item even hidden">
        <label class="label-desc gray" for="education[degree][]">
            Degree Obtained:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="education[degree][]">
        </div>
    </div>
    <div class="form-item odd hidden">
        <label class="label-desc gray" for="education[course][]">
            Course Taken:
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="" name="education[course][]">
        </div>
    </div>       
    <div class="form-item even">
        <label class="label-desc gray" for="education[honors_received][]">
            Honors Received:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="education[honors_received][]">
        </div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="education[date_from][]">
            Date From:
        </label>
        <div class="text-input-wrap">
            <input type="text" name="education[date_from][]" id="" value="" class="input-text month-year date_from"/>
            &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
            <input type="text" name="education[date_to][]" id="" value="" class="input-text month-year date_from" />
        </div>
    </div>   
    <div class="clear"></div>    
</div>