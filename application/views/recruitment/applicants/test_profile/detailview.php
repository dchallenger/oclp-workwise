<div>
    <?php
    if (count($test_profile) > 0):
        foreach ($test_profile as $data):
            ?>

            <div>
                <!-- <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="test_profile[exam_type][]">
                        Exams Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['exam_type'] ?></div>                   
                </div>
                <div class="form-item view even <?php echo (($data['exam_type'] == 'Government Examination' || $data['exam_type'] == '') ? 'hidden' : '')?>">
                    <label class="label-desc view gray" for="test_profile[exam_title][]">
                        Exam Title:
                    </label>
                    <div class="text-input-wrap">
                        <?
                            $this->db->where('exam_title_id',$data['exam_title_id']);
                            $this->db->where('deleted',0);
                            $result = $this->db->get('exam_title');
                            if ($result && $result->num_rows() > 0){
                                $row = $result->row();
                                echo $row->exam_title;
                            }
                        ?>
                    </div>                    
                </div>  -->
                <div class="form-item odd view <?php // echo (($data['exam_type'] == 'Professional Exam' || $data['exam_type'] == '') ? 'hidden' : '')?>">
                    <label class="label-desc view gray" for="test_profile[exam_title][]">
                        Exam Title:
                    </label>
                    <div class="text-input-wrap"><?= $data['exam_title'] ?></div> 
                </div>                
              <!--  <div class="form-item view odd">
                    <label class="label-desc view gray" for="test_profile[date_taken][]">
                        Date Taken:
                    </label>
                    <div class="text-input-wrap"><?= ($data['date_taken'] == "0000-00-00" || $data['date_taken'] == "1970-01-01" || $data['date_taken'] == "" ? "" : date('M d, Y', strtotime($data['date_taken']))) ?></div> <span></span>
                </div>
                <div class="form-item even view" style="display:none">
                    <label class="label-desc view gray" for="test_profile[given_by][]">
                        Given by:
                    </label>
                    <div class="text-input-wrap"><?= $data['given_by'] ?></div> 
                </div>                  
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="test_profile[location][]">
                        Location:
                    </label>
                    <div class="text-input-wrap"><?= $data['location'] ?></div> 
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="test_profile[score_rating][]">
                        Rating:
                    </label>
                    <div class="text-input-wrap"><?= $data['score_rating'] ?></div> 
                </div>  --> 
                <div class="form-item even view ">
                    <label class="label-desc view gray" for="test_profile[license_no][]">
                        License No.:
                    </label>
                    <div class="text-input-wrap"><?= $data['license_no'] ?></div> 
                </div>
<!--                 <div class="form-item odd">
                    <label class="label-desc gray" for="test_profile[result][]">
                        Result:
                    </label>
                    <div class="text-input-wrap"><?= $data['result'] ?></div> 
                </div>
                <div class="form-item even view">
                    <label class="label-desc view gray" for="test_profile[result_attach][]">
                        Result Attachment:
                    </label>
                    <?php

                        $full_file = $data['result_attach'];
                        $file = explode("/",$data['result_attach']);
                        $data = $file[3];
                        $filename = base_url() . $data['result_attach'];
                    ?>
                    <div class="text-input-wrap"><a href="<?= site_url() ?>recruitment/applicants/download_file/<?= $data ?>"><?= $data ?></a></div> 
                </div>                 -->
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
<?php endif; ?>
</div>
