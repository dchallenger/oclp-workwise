<fieldset>
<div class="form-multiple-add-test_profile">
    <input type="hidden" class="add-more-flag" value="test_profile" />
    <?php
    if (count($test_profile) > 0):
        foreach ($test_profile as $data):
            if(!isset($enable_edit) && ($enable_edit != 1)){
            ?>

            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
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

                        echo form_dropdown('test_profile[exam_type][]', $options, $data['exam_type']);
                        ?>
                    </div>                    
                    <!-- <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['test_taken'] ?>" name="test_profile[test_taken][]"></div> -->
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[exam_title][]">
                        Exam Title:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['exam_title'] ?>" name="test_profile[exam_title][]"></div>
                </div>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[date_taken][]">
                        Date Taken:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" class="input-text datepicker date" value="<?= ($data['date_taken'] == "0000-00-00" || $data['date_taken'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['date_taken']))) ?>" name="test_profile[date_taken][]" /> <span></span>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[given_by][]">
                        Given by:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['given_by'] ?>" name="test_profile[given_by][]"></div>
                </div>                  
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[location][]">
                        Location:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['location'] ?>" name="test_profile[location][]"></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[score_rating][]">
                        Score/Rating:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['score_rating'] ?>" name="test_profile[score_rating][]"></div>
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

                        echo form_dropdown('test_profile[result][]', $options, $data['result']);
                        ?>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[result_attach][]">
                        Result Attachement:
                    </label>
                </div>                
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <?php 
                }else{
             ?>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
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

                        echo form_dropdown('test_profile[exam_type][]', $options, $data['exam_type']);
                        ?>
                    </div>                    
                    <!-- <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['test_taken'] ?>" name="test_profile[test_taken][]"></div> -->
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[exam_title][]">
                        Exam Title:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['exam_title'] ?>" name="test_profile[exam_title][]"></div>
                </div>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[date_taken][]">
                        Date Taken:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" readonly="readonly" class="input-text datepicker date" value="<?= ($data['date_taken'] == "0000-00-00" || $data['date_taken'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['date_taken']))) ?>" name="test_profile[date_taken][]" /> <span></span>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[given_by][]">
                        Given by:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['given_by'] ?>" name="test_profile[given_by][]"></div>
                </div>                  
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[location][]">
                        Location:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['location'] ?>" name="test_profile[location][]"></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[score_rating][]">
                        Score/Rating:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['score_rating'] ?>" name="test_profile[score_rating][]"></div>
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

                        echo form_dropdown('test_profile[result][]', $options, $data['result']);
                        ?>
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[result_attach][]">
                        Result Attachement:
                    </label>
                </div>  
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
             <?php } ?>
        <?php endforeach; ?>
<?php endif; ?>
</div>
</fieldset>
