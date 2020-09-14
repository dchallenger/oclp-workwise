<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="form-item odd hidden">
     <table>
        <tr>
            <td style="width:35%;">
                <label class="label-desc gray"  style="width:100%; text-align:right;">Experience Level:</label>
            </td>
            <td  style="width:65%">
                 <input type="radio" value="1" name="no_work_experience" class="no_work_experience" <?php if( $no_work_experience == 1 ){ echo "checked"; }?> >I have been working since<br>
                 <input type="radio" value="2" name="no_work_experience" class="no_work_experience" <?php if( $no_work_experience == 2 ){ echo "checked"; }?> >I am a fresh graduate seeking my first job<br>
                 <input type="radio" value="3" name="no_work_experience" class="no_work_experience" <?php if( $no_work_experience == 3 ){ echo "checked"; }?> >I am a student seeking internship or part-time jobs
            </td>
        </tr>
    </table>
<br>                                    
</div>
    <div class="form-item odd" style="display:none">
         
         <div class="working_since_container"><div class="form-item even">
                    <label class="label-desc gray" for="working_since">Working Since:</label>
                    <div class="multiselect-input-wrap">
                        <select name="working_since_select" id="working_since_select" style="width:400px;">
                            <option value="" selected>Please select year</option>
                        </select>
                        <input type="hidden" value="<?php if( $working_since ){ echo $working_since; } ?>" name="working_since" id="working_since" />
                    </div>
                </div>
            </div>
    <br>                                    
</div>


<div class="clear"></div>
<div class="form-multiple-add-employment">
    <input type="hidden" class="add-more-flag" value="employment" />    
    <?php    
    if (count($employment) > 0):
        foreach ($employment as $data):
            ?>
						<fieldset>
            <div class="form-multiple-add" style="display: block;">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                    </div>
                </h3>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[company][]">
                        Name of Employer: <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?=$data['company']?>" name="employment[company][]">
                    </div>
                </div>
                <!-- <div class="form-item even">
                    <label class="label-desc gray" for="employment[address][]">
                        Address: <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?=$data['address']?>" name="employment[address][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[contact_number][]">
                        Contact Number: <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?=$data['contact_number']?>" name="employment[contact_number][]">
                    </div>
                </div>
                <div class="form-item odd">
                        <label class="label-desc gray" for="employment[nature_of_business][]">
                        Type of Industry:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?=$data['nature_of_business']?>" name="employment[nature_of_business][]">
                    </div>
                </div> -->
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[position][]">
                        Job Title:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?=$data['position']?>" name="employment[position][]">
                    </div>
                </div>
             <!--   <div class="form-item odd">
                    <label class="label-desc gray" for="education[date_from][]">
                        Employment Dates:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" name="employment[from_date][]" id="" value="<?=($data['from_date']) ? date('F Y', strtotime($data['from_date'])) : ''?>" class="input-text month-year date_from"/>
                        &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
                        <input type="text" name="employment[to_date][]" id="" value="<?=($data['to_date'] != '') ? date('F Y', strtotime($data['to_date'])) : ''?>" class="input-text month-year date_from" />
                    </div>                
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="education[last_employment_status][]">
                        Employment Status:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" name="employment[last_employment_status][]" id="" value="<?=$data['last_employment_status']?>" class="input-text"/>
                    </div>                
                </div> -->
                <div class="form-item odd">
                    <label class="label-desc gray" for="education[last_salary][]">
                        Basic Salary:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" name="employment[last_salary][]" id="" value="<?=$data['last_salary']?>" class="input-text"/>
                    </div>                
                </div>
                <!-- <div class="form-item odd">
                    <label class="label-desc gray" for="education[allowance][]">
                       Allowance:
                    </label>                
                    <div class="text-input-wrap">                   
                        <input type="text" name="employment[allowance][]" id="" value="<?=$data['allowance']?>" class="input-text"/>
                    </div>                
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[reason_for_leaving][]">
                        Reason for Leaving:
                    </label>
                    <div class="textarea-input-wrap"><textarea  class="input-textarea" rows="5" name="employment[reason_for_leaving][]"><?=$data['reason_for_leaving']?></textarea>           
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[duties][]">
                        Responsibilities:
                    </label>
                    <div class="textarea-input-wrap"><textarea  class="input-textarea" rows="5" name="employment[duties][]"><?=$data['duties']?></textarea>            
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[accomplishment][]">
                        Accomplishments:
                    </label>
                    <div class="textarea-input-wrap"><textarea  class="input-textarea" rows="5" name="employment[accomplishment][]"><?=$data['accomplishment']?></textarea>           
                    </div>
                </div>
                
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[most_like_job][]">
                        What do/did you like most of your job:
                    </label>
                    <div class="textarea-input-wrap"><textarea  class="input-textarea" rows="5" name="employment[most_like_job][]"><?=$data['most_like_job']?></textarea>           
                    </div>
                </div>
                <div class="form-item odd">
                    <label class="label-desc gray" for="employment[least_enjoy][]">
                        What do/did you least enjoy:
                    </label>
                    <div class="textarea-input-wrap"><textarea  class="input-textarea" rows="5" name="employment[least_enjoy][]"><?=$data['least_enjoy']?></textarea>           
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[supervisor_name][]">
                        Name of Superior :
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?=$data['supervisor_name']?>" name="employment[supervisor_name][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[supervisor_contact][]">
                        Contact Number:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?=$data['supervisor_contact']?>" name="employment[supervisor_contact][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[supervisor_position][]">
                        Title:
                    </label>
                    <div class="text-input-wrap"><input type="text" class="input-text" value="<?=$data['supervisor_position']?>" name="employment[supervisor_position][]">
                    </div>
                </div>
                <div class="form-item even">
                    <label class="label-desc gray" for="employment[supervisor_rate][]">
                        How do he/she support and help you with your responsibilities?<br> How would you rate him/her as your supervisor?:
                    </label>
                    <div class="textarea-input-wrap"><textarea  class="input-textarea" rows="5" name="employment[supervisor_rate][]"><?=$data['supervisor_rate']?></textarea>            
                    </div>
                </div>-->
                <div class="clear"></div>
           
            </div> 
            </fieldset>
            <div class="spacer"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
