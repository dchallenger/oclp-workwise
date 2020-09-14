<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<!-- <div class="form-item odd ">
    <div class="icon-label-group">
        <div style="display: block;" class="icon-label add-more-div">
            <a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add_row" rel="interview" type="interview">
                <span>Add Interviewer</span>
            </a>
        </div>
    </div>
</div>  -->
<br>
<div class="clear"></div>
<div class="interviewer-container">

    <!--  <h3 class="form-head">
        <div class="align-right">&nbsp;
           <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
        </div>
    </h3> -->
    <?php 
        if ($candidate_interviewer && $candidate_interviewer->num_rows() > 0):
            
            foreach ($candidate_interviewer->result() as $int => $row_interviewer): 
                $count = $int+1;?>
        <div class="form-item odd ">
            <label class="label-desc gray" for="interview_type">
                Interview Type:<span class="red font-large">*</span>
            </label>
            <div class="select-input-wrap">   
               <!-- <input type="text" name="interview[type][]" value="<?=$interview_details['type'][$int]?>" class="input-text interview_type"> -->
               <select  class="interview_type" name="interview[type][]">
                    <option value="">Select ... </option>
               <?php foreach ($interview_type as $type) { ?>
                    <option value="<?=$type->recruitment_interview_type?>" <?=($interview_details['type'][$int] == $type->recruitment_interview_type ? 'SELECTED="SELECTED"' : '') ?>><?=$type->recruitment_interview_type?></option>
               <?php } ?>
                   
               </select>
            </div>
        </div>
        <div class="form-item even ">
            <label class="label-desc gray" for="tooltip">
                Interviewer:
            </label>
            <div class="select-input-wrap">
                <?php if (count($interviewer) > 0):  ?>
                    <select class="interviewer" name="interview[interviewer][]">
                        <option value="">Select...</option>
                        <?php foreach ($interviewer as $key => $value) { ?>
                            <option value="<?=$value['user_id'] ?>" <?=($value['user_id'] == $row_interviewer->user_id ? 'SELECTED="SELECTED"' : '') ?>><?php echo $value['lastname'] ?>,&nbsp;<?php echo $value['firstname'] ?></option>
                        <?php } ?>
                    </select>
                <?php else: ?>
                    <span class="red">No interviewer set under position settings.</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-item odd ">
            <label class="label-desc gray" for="tooltip">
                Result:
            </label>
            <div class="select-input-wrap">
                <select name="interview[result][]" class="result">
                    <option value="0">Select ...</option>
                    <option value="1" <?=($interview_details['result'][$int] == '1') ? 'selected="selected"' : "" ;?> >Passed</option>
                    <option value="2" <?=($interview_details['result'][$int] == '2') ? 'selected="selected"' : "" ;?> >Failed</option>
                </select>
            </div>  
        </div>
        <div class="form-item even ">
            <label class="label-desc gray" for="recommendation">
                Recommendation:
                <span class="red font-large">*</span>                                                        
            </label>
            <div class="select-input-wrap">
                <select name="interview[recommendation][]" class="exam">
                    <option value="">Select ... </option>
                    <?php foreach ($recommendation as $rec):?>
                    <option value="<?=$rec->recommendation_id?>" <?=($interview_details['recommendation'][$int] == $rec->recommendation_id ? 'SELECTED="SELECTED"' : '') ?>><?=$rec->recommendation?></option>
                    <?php endforeach;?>
                </select>
            </div> 
        </div>
        <div class="form-item even">
            <label class="label-desc gray" for="attachment[]">
                Attachment:
            </label>
                <div id="error-photo"></div>
                <?php if ($interview_details['attachment'][$int] != "") 
                    { 
                        $path_info = pathinfo($interview_details['attachment'][$int]);
                        if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
                        {?>
                            <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>">
                                <img id="file-photo-<?=$count;?>" src="<?= base_url().$interview_details['attachment'][$int] ?>" width="100px">
                                <div class="delete-image nomargin" field="dir_path<?=$count;?>" style="display: none;"></div>
                            </div>
                        <?php 
                        }
                        else
                        {?>
                            <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>">
                                <a id="file-photo-<?=$count;?>" href="<?= base_url().$interview_details['attachment'][$int] ?>" width="100px"><img src="<?= base_url()?>themes/slategray/images/file-icon-md.png"></a>
                                <div class="delete-image nomargin" field="dir_path<?=$count;?>"></div>
                            </div>
                        <?php }?>
                         <div class="clear"></div>
                  <?php  }else{ ?>
                        <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>"></div>
                         <div class="clear"></div> 
                  <?php }   ?>
               
                <input id="dir_path<?=$count;?>" type="hidden" class="input-text" value="<?=$interview_details['attachment'][$int]?>" name="interview[attachment][]">     
                <div><input id="attachment-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>"/></div>
            </div> 
            <div class="clear"></div> <h3 class="form-head"></h3>
    <?php 
            endforeach;
        endif;?>

</div>
<div id="interview"></div>
<input type="hidden" class="count_attachment" value="<?=$count;?>"> 