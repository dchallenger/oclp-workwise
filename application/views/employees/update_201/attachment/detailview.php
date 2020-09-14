<div>
    <?php
    if (count($attachment) > 0):
        foreach ($attachment as $data):
            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="attachment[folder_name][]">
                        Tag:
                    </label>
                    <div class="text-input-wrap"><?= $data['folder_name'] ?></div>                   
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="attachment[short_description][]">
                        Description:
                    </label>
                    <div class="text-input-wrap"><?= $data['short_description'] ?></div> 
                </div>    
                <div class="form-item odd">
                    <label class="label-desc gray" for="attachment[dir_path][]">
                        Result Attachment:
                    </label>
                    <?php
                        $full_file = $data['dir_path'];
                        $file = explode("/",$data['dir_path']);
                        $data = $file[2];
                        $filename = base_url() . $data['dir_path'];
                    ?>
                    <div class="text-input-wrap"><a href="<?= site_url() ?>employees/download_file/<?= $data ?>"><?= $data ?></a></div> 
                </div>                
                <div class="clear"></div>
            </div>
        <?php endforeach; ?>
<?php endif; ?>
</div>
