<fieldset>
<div class="form-multiple-add-test_profile">
    <input type="hidden" class="add-more-flag" value="test_profile" />
    <input type="hidden" class="count_test_profile" value="<?=count($test_profile);?>" />
    <?php
    if (count($test_profile) > 0):
        $count = 1;
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
                        <input type="text" readonly="readonly" class="input-text <?= (CLIENT_DIR == "firstbalfour" ? 'month-year date_from' : ' datepicker date'); ?>" value="<?= ($data['date_taken'] == "0000-00-00" || $data['date_taken'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['date_taken']))) ?>" name="test_profile[date_taken][]" /> <span></span>
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
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><textarea type="text" class="input-text" name="test_profile[remarks][]"><?= $data['remarks'] ?></textarea></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[result_attach][]">
                        Result Attachment:
                    </label>
                    <div id="error-photo"></div>
                    <?php if ($data['result_attach'] != "") 
                    {
                        $path_info = pathinfo($data['result_attach']);
                        if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
                        {?>
                            <div class="nomargin image-wrap" id="photo-upload-container_1<?=$count;?>">
                                <img id="file-photo-<?=$count;?>" src="<?= base_url().$data['result_attach'] ?>" width="100px">
                                <div class="delete-image nomargin" field="result_attach<?=$count;?>" style="display: none;"></div>
                            </div>
                          <?php 
                        }
                        else
                        {?>
                            <div class="nomargin image-wrap" id="photo-upload-container_1<?=$count;?>">
                                <a id="file-photo-<?=$count;?>" href="<?= base_url().$data['result_attach'] ?>" width="100px"><img src="<?= base_url()?>themes/slategray/images/file-icon-md.png"></a>
                                <div class="delete-image nomargin" field="result_attach<?=$count;?>"></div>
                            </div>
                    <?php }?>
                    <div class="clear"></div>                    
                    <?php }else{ ?>
                         <div class="nomargin image-wrap" id="photo-upload-container_1<?=$count;?>"></div>
                         <div class="clear"></div>                    
                    <?php } ?>                    
                    <input id="result_attach<?=$count;?>" type="hidden" class="input-text" value="<?= $data['result_attach'] ?>" name="test_profile[result_attach][]">                    
                    <div><input id="test_profile-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>" /></div>                    
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
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><textarea type="text" class="input-text" name="test_profile[remarks][]"><?= $data['remarks'] ?></textarea></div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[result_attach][]">
                        Result Attachment:
                    </label>
                    <div id="error-photo"></div>
                    <?php if ($data['result_attach'] != "") 
                    {
                        $path_info = pathinfo($data['result_attach']);
                        if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
                        {?>
                            <div class="nomargin image-wrap" id="photo-upload-container_1<?=$count;?>">
                                <img id="file-photo-<?=$count;?>" src="<?= base_url().$data['result_attach'] ?>" width="100px">
                                <div class="delete-image nomargin" field="result_attach<?=$count;?>" style="display: none;"></div>
                            </div>
                          <?php 
                        }
                        else
                        {?>
                            <div class="nomargin image-wrap" id="photo-upload-container_1<?=$count;?>">
                                <a id="file-photo-<?=$count;?>" href="<?= base_url().$data['result_attach'] ?>" width="100px"><img src="<?= base_url()?>themes/slategray/images/file-icon-md.png"></a>
                                <div class="delete-image nomargin" field="result_attach<?=$count;?>"></div>
                            </div>
                    <?php }?>
                    <div class="clear"></div>                    
                    <?php }else{ ?>
                         <div class="nomargin image-wrap" id="photo-upload-container_1<?=$count;?>"></div>
                         <div class="clear"></div>                    
                    <?php } ?>                       
                    <input id="result_attach<?=$count;?>" type="hidden" class="input-text" value="<?= $data['result_attach'] ?>" name="test_profile[result_attach][]">
                    <div><input id="test_profile-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>" /></div>                    
                </div>  
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
             <?php } 
             $count++;?>
        <?php endforeach; ?>
<?php endif; ?>
</div>
</fieldset>
