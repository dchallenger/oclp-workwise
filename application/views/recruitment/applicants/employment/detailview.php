<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($employment) > 0):
        foreach ($employment as $data):
            ?>

            <div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[company][]">
                        Name of Employer: <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><?= $data['company'] ?></div>
                </div>
               <!--  <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[address][]">
                        Address: <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><?= $data['address'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[contact_number][]">
                        Contact Number: <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><?= $data['contact_number'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[nature_of_business][]">
                        Type of Industry:
                    </label>
                    <div class="text-input-wrap"><?= $data['nature_of_business'] ?></div>
                </div> -->
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[position][]">
                        Job Title:
                    </label>
                    <div class="text-input-wrap"><?= $data['position'] ?></div>
                </div>
               <!--  <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[from_date][]">
                        Employment Dates :
                    </label>
                    <div class="text-input-wrap"><?= display_date('F Y', strtotime($data['from_date'])) ?>  <?= display_date('F Y', strtotime($data['to_date'])) ?></div>
                </div> -->
<!--                 <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[to_date][]">
                        Date To:
                    </label>
                    <div class="text-input-wrap"></div>
                </div> 
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[last_employment_status][]">
                        Employment Status:
                    </label>
                    <div class="text-input-wrap"><?= $data['last_employment_status'] ?></div>
                </div>-->
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[last_salary][]">
                        Basic Salary:
                    </label>
                    <div class="text-input-wrap"><?= $data['last_salary'] ?></div>
                </div>
               <!--  <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[allowance][]">
                        Allowance:
                    </label>
                    <div class="text-input-wrap"><?= $data['allowance'] ?></div>
                </div>
                
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[reason_for_leaving][]">
                        Reason for Leaving:
                    </label>
                    <div class="textarea-input-wrap"><?= $data['reason_for_leaving'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[duties][]">
                        Responsibilities:
                    </label>
                    <div class="textarea-input-wrap"><?= nl2br( $data['duties'] ) ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[accomplishment][]">
                        Accomplishments:
                    </label>
                    <div class="textarea-input-wrap"><?= nl2br($data['accomplishment']) ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[most_like_job][]">
                        What do/did you like most of your job:
                    </label>
                    <div class="textarea-input-wrap"><?= nl2br($data['most_like_job']) ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[least_enjoy][]">
                         What do/did you least enjoy:
                    </label>
                    <div class="textarea-input-wrap"><?= nl2br($data['least_enjoy'])?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[supervisor_name][]">
                        Name of Superior:
                    </label>
                    <div class="text-input-wrap"><?= $data['supervisor_name'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[supervisor_contact][]">
                        Contact Number:
                    </label>
                    <div class="text-input-wrap"><?= $data['supervisor_contact'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[supervisor_position][]">
                        Title:
                    </label>
                    <div class="text-input-wrap"><?= $data['supervisor_position'] ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[supervisor_rate][]">
                        How do he/she support and help you with your responsibilities?<br> How would you rate him/her as your supervisor?:
                    </label>
                    <div class="textarea-input-wrap"><?= $data['supervisor_rate'] ?>         
                    </div>
                </div> -->
                                
            </div>
            <div class="clear"></div>
  
        <?php endforeach; ?>
    <?php endif; ?>
</div>
