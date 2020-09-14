<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<div class="form-item odd ">
    <div class="icon-label-group">
        <div style="display: block;" class="icon-label add-more-div">
            <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add_row" rel="exam" type="examination">
                <span>Add Exam</span>
            </a>
        </div>
    </div>
</div> 
<div class="clear"></div>

<?php if ($exam_details):
    $exam_details = json_decode($exam_details);
    $details = $exam_details->type;
    $results = $exam_details->result;
    $percents = $exam_details->percentile;

    foreach ($details as $id => $detail):?>

    <div class="exam_type">
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
                Exam Type:
                <span class="red font-large">*</span>                                                        
            </label>
            <div class="select-input-wrap">
                <select name="exam[type][]" class="exam">
                    <option value="">Select ... </option>
                    <?php foreach ($exams as $key => $exam):?>
                    <option value="<?=$exam->recruitment_exam_type_id?>" <?=($exam->recruitment_exam_type_id == $detail) ? 'selected="selected"' : "" ;?> ><?=$exam->recruitment_exam_type?></option>
                    <?php endforeach;?>
                </select>
            </div> 
        </div>
        <div class="form-item odd ">
            <label class="label-desc gray" for="tooltip">
                Result: 
            </label>
            <div class="select-input-wrap">
                <select name="exam[result][]" class="result">
                    <option value="0">Select ... </option>
                    <option value="1" <?=($results[$id] == '1') ? 'selected="selected"' : "" ;?> >Passed</option>
                    <option value="2" <?=($results[$id] == '2') ? 'selected="selected"' : "" ;?> >Failed</option>
                </select>
            </div> 
        </div>
        <div class="form-item even ">
            <label class="label-desc gray" for="placeholder">
                Percentiles:
            </label>
            <div class="text-input-wrap">   
               <input type="text" name="exam[percentile][]" value="<?=$percents[$id]?>" class="input-text percentile">
            </div>
        </div>

    </div>
   <div class="clear"></div>

<?php endforeach;
    endif;?>

<div id="examination"></div>