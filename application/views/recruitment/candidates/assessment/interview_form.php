<div class="interviewer-container">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete">
                <a href="javascript:void(0)" class="delete-detail delete_row" >DELETE</a>
                <input type="hidden" name="" value="" />
            </span>
        </div>
    </h3>
        <div class="form-item odd ">
            <label class="label-desc gray" for="interview_type">
                Interview Type:<span class="red font-large">*</span>
            </label>
            <div class="text-input-wrap">   
               <input type="text" name="interview[type][]" value="" class="input-text interview_type">
            </div>
        </div>
        <div class="form-item even ">
            <label class="label-desc gray" for="tooltip">
                Interviewer:
            </label>
            <div class="select-input-wrap">
                <?php if (count($interviewer) > 0): ?>
                    <select class="interviewer" name="interview[interviewer][]">
                        <option value="">Select...</option>
                        <?php foreach ($interviewer as $key => $value) { ?>
                            <option value="<?=$value['user_id'] ?>"><?=$value['lastname'] ?>,&nbsp;<?=$value['firstname'] ?></option>
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
                    <option value="1">Passed</option>
                    <option value="2">Failed</option>
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
                    <option value="<?=$rec->recommendation_id?>"><?=$rec->recommendation?></option>
                    <?php endforeach;?>
                </select>
            </div> 
        </div>
       <div class="form-item even">
            <label class="label-desc gray" for="attachment[]">
                Attachment:
            </label>
            <div id="error-photo"></div>
            <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>"></div>
            <div class="clear"></div>
            <input id="dir_path<?=$count;?>" type="hidden" class="input-text" value="" name="interview[attachment][]">                    
            <div><input id="attachment-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>"/></div>
        </div> 
            <div class="clear"></div>
</div>