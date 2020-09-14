
<div class="form-multiple-add" style="display: block;">
     <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete"><a href="#" class="delete-detail">DELETE</a></span>
        </div>
    </h3>

   <div class="form-item odd">
        <label class="label-desc gray" for="test_profile[exam_type][]">
            Exam Type:
        </label>
        <div class="select-input-wrap">                        
            <?php 
            $options = array(
                    ''        => 'Select&hellip;',
                    'Professional Exam'  => 'Professional Exam',
                    'Pre-Employment Exam'  => 'Pre-Employment Exam',
                    'Promotion Exam'  => 'Promotion Exam',
                );
            
            echo form_dropdown('test_profile[exam_type][]', $options);
            ?>
        </div>                    
        <!-- <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['test_taken'] ?>" name="test_profile[test_taken][]"></div> -->
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="test_profile[exam_title][]">
            Exam Title:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="test_profile[exam_title][]"></div>
    </div>                
    <div class="form-item odd">
        <label class="label-desc gray" for="test_profile[date_taken][]">
            Date Taken:
        </label>
        <div class="text-input-wrap">
            <input type="text" readonly="readonly" class="input-text <?= (CLIENT_DIR == "firstbalfour" ? 'month-year date_from' : ' datepicker date'); ?>" value="" name="test_profile[date_taken][]" /> <span></span>
        </div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="test_profile[given_by][]">
            Given by:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="test_profile[given_by][]"></div>
    </div>                  
    <div class="form-item odd">
        <label class="label-desc gray" for="test_profile[location][]">
            Location:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="test_profile[location][]"></div>
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="test_profile[score_rating][]">
            Score/Rating:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="test_profile[score_rating][]"></div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="test_profile[result][]">
            Result:
        </label>
        <div class="select-input-wrap">                        
            <?php 
            $options = array(
                    ''        => 'Select&hellip;',
                    'Passed'  => 'Passed',
                    'Failed'  => 'Failed',
                );
            
            echo form_dropdown('test_profile[result][]', $options);
            ?>
        </div>         
    </div>
    <div class="form-item even">
        <label class="label-desc gray" for="test_profile[result_attach][]">
            Result Attachment:
        </label>
        <div id="error-photo"></div>
        <div class="nomargin image-wrap" id="photo-upload-container_1<?=$count;?>"></div>
        <div class="clear"></div> 
        <input id="result_attach<?=$count;?>" type="hidden" class="input-text" value="<?= $data['result_attach'] ?>" name="test_profile[result_attach][]">        
        <div><input id="test_profile-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>" /></div>
    </div>  
    <div class="form-item odd">
        <label class="label-desc gray" for="test_profile[remarks][]">
            Remarks:
        </label>
        <div class="text-input-wrap"><textarea type="text" class="input-text" value="" name="test_profile[remarks][]"></textarea></div>
    </div>
    <div class="clear"></div>
    <hr />
</div>
