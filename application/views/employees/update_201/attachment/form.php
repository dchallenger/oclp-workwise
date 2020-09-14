<div class="form-multiple-add" style="display: block;">
    <h3 class="form-head">
        <div class="align-right">
            <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
        </div>
    </h3>
    <div class="form-item odd">
        <label class="label-desc gray" for="attachment[folder_name][]">
            Tag:
        </label>
        <div class="text-input-wrap"><input type="text" class="input-text" value="" name="attachment[folder_name][]"></div>
    </div>
    <div class="form-item odd">
        <label class="label-desc gray" for="attachment[short_description][]">
            Description:
        </label>
        <div class="text-input-wrap">
            <textarea maxlength="150" width="50px" height="75px" name="attachment[short_description][]" class="input-text" ></textarea>
        </div>
    </div>   
    <div class="form-item odd">
        <label class="label-desc gray" for="attachment[dir_path][]">
            Attachment:
        </label>
        <div id="error-photo"></div>
        <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>"></div>
        <div class="clear"></div>
        <input id="dir_path<?=$count;?>" type="hidden" class="input-text" value="" name="attachment[dir_path][]">                    
        <div><input id="attachment-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>"/></div>
    </div>                
    <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <hr />
</div>