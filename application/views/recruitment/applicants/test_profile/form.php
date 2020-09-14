
<div class="form-multiple-add" style="display: block;">
     <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete"><a href="#" class="delete-detail">DELETE</a></span>
        </div>
    </h3>

 <!--   <div class="form-item odd">
        <label class="label-desc gray" for="test_profile[exam_type][]">
             Exams Type:
        </label>
        <div class="select-input-wrap">                        
            <?php 
            $options = array(
                    ''        => 'Select&hellip;',
                    'Government Examination'  => 'Government Examination',                                
                    'Professional Exam'  => 'Professional Exam'
                );
            
            echo form_dropdown('test_profile[exam_type][]', $options);
            ?>
        </div>                    
        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['test_taken'] ?>" name="test_profile[test_taken][]"></div> 
    </div>
    <div class="form-item even hidden" id="exam_title_id">
        <label class="label-desc gray" for="test_profile[exam_title][]">
            Exam Title:
        </label>
        <div class="select-input-wrap">
            <?php 
                $result =  $this->db->get_where('exam_title',array("deleted"=>0));

                $rows_array = array();
                if ($result && $result->num_rows() > 0):
                    $rows_array[$row->exam_title_id] = "Select exam title...";
                    foreach ($result->result() as $row) {
                        $rows_array[$row->exam_title_id] = $row->exam_title;
                    }
                endif;
                
                $exam_title = $rows_array; 
                                        
                echo form_dropdown('test_profile[exam_title_id][]', $exam_title);
            ?>
        </div> 
    </div>   -->
    <div class="form-item odd" id="exam_title">
        <label class="label-desc gray" for="test_profile[exam_title][]">
            Exam Title:
        </label>
        <div class="text-input-wrap">
            <input type="text" class="input-text" value="<?= $data['exam_title']?>" name="test_profile[exam_title][]" /> <span></span>
        </div>
    </div>                 
<!--    <div class="form-item odd">
        <label class="label-desc gray" for="test_profile[date_taken][]">
            Date Taken:
        </label>
        <div class="text-input-wrap">
            <input type="text" readonly="readonly" class="input-text datepicker date" value="" name="test_profile[date_taken][]" /> <span></span>
        </div>
    </div>
    <div class="form-item even" style="display:none">
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
            Rating:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="test_profile[score_rating][]" onkeydown="numeric_only(event)"></div>
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
        <input id="result_attach<?=$count;?>" type="hidden" class="input-text" value="<?= $data['result_attach'] ?>" name="test_profile[result_attach][]">        
        <div><input id="test_profile-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>" /></div>
    </div> -->
    <div class="form-item even">
        <label class="label-desc gray" for="test_profile[license_no][]">
            License No.:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['license_no'] ?>" name="test_profile[license_no][]"></div>
    </div> 
    <div class="clear"></div>
    <hr />
</div>
