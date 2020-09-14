<div>
    <?php
    if (count($test_profile) > 0):
        foreach ($test_profile as $data):
            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[exam_type][]">
                        Exam Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['exam_type'] ?></div>                   
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[exam_title][]">
                        Exam Title:
                    </label>
                    <div class="text-input-wrap"><?= $data['exam_title'] ?></div> 
                </div>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[date_taken][]">
                        Date Taken:
                    </label>
                    <div class="text-input-wrap"><?= ($data['date_taken'] == "0000-00-00" || $data['date_taken'] == "1970-01-01" ? "" : date((CLIENT_DIR == "firstbalfour" ? $this->config->item('display_month_year_date_format') : $this->config->item('display_date_format')), strtotime($data['date_taken']))) ?></div> <span></span>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[given_by][]">
                        Given by:
                    </label>
                    <div class="text-input-wrap"><?= $data['given_by'] ?></div> 
                </div>                  
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[location][]">
                        Location:
                    </label>
                    <div class="text-input-wrap"><?= $data['location'] ?></div> 
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[score_rating][]">
                        Score/Rating:
                    </label>
                    <div class="text-input-wrap"><?= $data['score_rating'] ?></div> 
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[result][]">
                        Result:
                    </label>
                    <div class="text-input-wrap"><?= $data['result'] ?></div> 
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[remarks][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><?= $data['remarks'] ?></div> 
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="test_profile[result_attach][]">
                        Result Attachment:
                    </label>
                    <?php
                        $full_file = $data['result_attach'];
                        $file = explode("/",$data['result_attach']);
                        $data = $file[2];
                        $filename = base_url() . $data['result_attach'];
                    ?>
                    <div class="text-input-wrap"><a href="<?= site_url() ?>employees/download_file/<?= $data ?>"><?= $data ?></a></div> 
                </div>                
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
<?php endif; ?>
</div>
