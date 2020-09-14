<fieldset>
<div class="form-multiple-add-attachment">
    <input type="hidden" class="add-more-flag" value="attachment" />
    <input type="hidden" class="count_attachment" value="<?=count($attachment);?>" />
    <div style="float:left;" class="icon-label add-more-div">
        <a rel="family" href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more">
            <span>Add</span>
        </a>
    </div>
    <br style="clear:left"/>
    <div class="attachement-container">    
        <?php
        if (count($attachment) > 0):
            $count = 1;
            foreach ($attachment as $data):
                if(!isset($enable_edit) && ($enable_edit != 1)){
                ?>
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
                        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['folder_name'] ?>" name="attachment[folder_name][]"></div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="attachment[short_description][]">
                            Description:
                        </label>
                        <div class="text-input-wrap">
                            <!-- <input type="text" maxlength="100" class="input-text" value="<?= $data['short_description'] ?>" name="attachment[short_description][]"> -->
                            <textarea maxlength="150" width="50px" height="75px" name="attachment[short_description][]" class="input-text" ><?= $data['short_description'] ?></textarea>
                        </div>
                    </div>   
                    <div class="form-item odd">
                        <label class="label-desc gray" for="attachment[dir_path][]">
                            Attachment:
                        </label>
                        <div id="error-photo"></div>
                        <?php if ($data['dir_path'] != "") 
                        { 
                            $path_info = pathinfo($data['dir_path']);
                            if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
                            {?>
                                <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>">
                                    <img id="file-photo-<?=$count;?>" src="<?= base_url().$data['dir_path'] ?>" width="100px">
                                    <div class="delete-image nomargin" field="dir_path<?=$count;?>" style="display: none;"></div>
                                </div>
                            <?php 
                            }
                            else
                            {?>
                                <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>">
                                    <a id="file-photo-<?=$count;?>" href="<?= base_url().$data['dir_path'] ?>" width="100px"><img src="<?= base_url()?>themes/slategray/images/file-icon-md.png"></a>
                                    <div class="delete-image nomargin" field="dir_path<?=$count;?>"></div>
                                </div>
                            <?php }?>
                        <div class="clear"></div>                    
                        <?php }else{ ?>
                             <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>"></div>
                             <div class="clear"></div>                    
                        <?php } ?>                    
                        <input id="dir_path<?=$count;?>" type="hidden" class="input-text" value="<?= $data['dir_path'] ?>" name="attachment[dir_path][]">
                        <div><input id="attachment-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>" /></div>                 
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
                        <label class="label-desc gray" for="attachment[folder_name][]">
                           Tag:
                        </label>
                        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['folder_name'] ?>" name="attachment[folder_name][]"></div>
                    </div> 
                    <div class="form-item odd">
                        <label class="label-desc gray" for="attachment[short_description][]">
                            Description:
                        </label>
                        <div class="text-input-wrap">
                            <!-- <input type="text" maxlength="100" class="input-text" value="<?= $data['short_description'] ?>" name="attachment[short_description][]"> -->
                            <textarea maxlength="150" width="50px" height="75px" name="attachment[short_description][]" class="input-text" ><?= $data['short_description'] ?></textarea>
                        </div>
                    </div> 
                    <div class="form-item odd">
                        <label class="label-desc gray" for="attachment[dir_path][]">
                            Attachment:
                        </label>
                        <div id="error-photo"></div>
                        <?php if ($data['dir_path'] != "") {
                        $path_info = pathinfo($data['dir_path']);
                            if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
                            {?>
                                <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>">
                                    <img id="file-photo-<?=$count;?>" src="<?= base_url().$data['dir_path'] ?>" width="100px">
                                    <div class="delete-image nomargin" field="dir_path<?=$count;?>" style="display: none;"></div>
                                </div>
                            <?php 
                            }
                            else
                            {?>
                               <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>">
                                    <a id="file-photo-<?=$count;?>" href="<?= base_url().$data['dir_path'] ?>" width="100px"><img src="<?= base_url()?>themes/slategray/images/file-icon-md.png"></a>
                                    <div class="delete-image nomargin" field="dir_path<?=$count;?>"></div>
                                </div>
                            <?php }?>        
                        <div class="clear"></div>            
                        <?php }else{ ?>
                             <div class="nomargin image-wrap" id="photo-upload-container_2<?=$count;?>"></div>
                             <div class="clear"></div>                    
                        <?php } ?>                                       
                        <input id="dir_path<?=$count;?>" type="hidden" class="input-text" value="<?= $data['dir_path'] ?>" name="attachment[dir_path][]">
                        <div><input id="attachment-photo<?=$count;?>" name="photo" type="file" rel="<?=$count;?>" /></div>                    
                    </div>  
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
                 <?php } 
                 $count++;?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</fieldset>
