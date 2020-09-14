<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="clear"></div>
<div class="form-multiple-add-education" >
    <input type="hidden" class="add-more-flag" value="education" />
    <input type="hidden" class="" id="no_education" value="<?php echo (count($education) > 0 ? count($education) : 0)?>" />
    <?php
    $count = count($education);    
    if ($count > 0):        
        foreach ($education as $index => $data): ?>
            <fieldset>
                <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete"><a href="javascript:void(0)" class="delete-detail">DELETE</a></span>
                        </div>
                    </h3>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="education[education_level][]">
                            Educational Attainment:
                        <span class="red font-large">*</span>
                        </label>
                        <div class="select-input-wrap">
                            <?php 
                            $options = array(
                                        '' => 'Select&hellip;',
                                        'Tertiary' => array('10' => 'College', '11' => 'Graduate Studies', '12' => 'Vocational'),
                                        'Secondary' => array('9' => 'Highschool'), 
                                        'Primary' => array('8' => 'Elementary')
                                     );
                            echo form_dropdown('education[education_level][]', $options, $data['option_id'])?>
                        </div>
                    </div>
                    <div class="form-item even <?php echo ($data['option_id'] < 10 ? 'hidden' : '')?>">
                        <label class="label-desc gray" for="education[education_school_id][]">School:</label>                      
                        <div class="select-input-wrap">
                            <?php                     
                                $this->db->where('deleted',0);
                                $this->db->order_by('education_school','ASC');
                                $education_school = $this->db->get('education_school')->result_array();        
                                print '<select id="education_school" class="education_school" name="education[education_school_id][]">
                                    <option value="" selected>Select School...</option>';
                                    foreach($education_school as $education_school_record){
                                        print '<option value="'.$education_school_record["education_school_id"].'" '.($education_school_record["education_school_id"] == $data['education_school_id'] ? 'SELECTED' : '').'>'.$education_school_record["education_school"].'</option>';
                                    }

                                
                                print '<option value="-1" '.($data['education_school_id'] == '-1' ? 'SELECTED' : '').'>Others</option>
                                        </select>';                                       
                            ?>
                        </div>
                    </div>       
                    <div class="form-item even <?php echo ($data['option_id'] > 9 ? 'hidden' : '')?>">
                        <label class="label-desc gray" for="education[education_school][]" >Name of School:</label>                      
                        <div class="text-input-wrap">
                            <input type="text" class="input-text" value="<?= $data['school']?>" name="education[school][]" /> <span></span>
                        </div>                        
                    </div>  
                   <!--  <div class="form-item odd hidden">
                        <label class="label-desc gray">
                            &nbsp;
                        </label>                    
                        <div class="radio-input-wrap">
                           <?=form_radio('education[graduate][' . --$count . ']', 1, ($data['graduate'] == '1' ? true : false), 'class="radioG"')?>Graduate
                            <?=form_radio('education[graduate][' . $count .']', 0, ($data['graduate'] == '0' ? true : false), 'class="radioUG"')?>Undergraduate
                        </div>                 
                    </div>    -->                  
                    <!-- <div class="form-item even <?= ($data['option_id'] == 10 || $data['option_id'] == 11 || $data['option_id'] == 12 ? '' : 'hidden') ?>">
                        <label class="label-desc gray" for="education[degree][]">
                            Degree / Course:
                        </label>
                        <div class="select-input-wrap">
                            <?php                     
                                $this->db->where('deleted',0);
                                $this->db->order_by('employee_degree_obtained','ASC');
                                $degree_obtained = $this->db->get('employee_degree_obtained')->result_array();     
                                print '<select id="degree_obtained" name="education[employee_degree_obtained_id][]">
                                    <option value="">Select Degree / Course...</option>';
                                    foreach($degree_obtained as $degree_obtained_record){
                                        print '<option value="'.$degree_obtained_record["employee_degree_obtained_id"].'" '.($degree_obtained_record["employee_degree_obtained_id"] == $data['employee_degree_obtained_id'] ? 'SELECTED' : '').'>'.$degree_obtained_record["employee_degree_obtained"].'</option>';
                                    }
                                print '</select>';                                       
                            ?>
                        </div>                        
                    </div> -->
<!--                     <div class="form-item odd hidden">
                        <label class="label-desc gray" for="education[course][]">
                            Course Taken:
                        </label>
                        <div class="text-input-wrap">
                            <input type="text" class="input-text" value="<?= $data['course'] ?>" name="education[course][]">
                        </div>
                    </div>   -->                 
                    <!-- <div class="form-item even">
                        <label class="label-desc gray" for="education[honors_received][]">
                            Honors / Awards:
                        </label>
                        <div class="text-input-wrap"><input type="text" class="input-text" value="<?= $data['honors_received'] ?>" name="education[honors_received][]">
                        </div>
                    </div>
                    <div class="form-item odd">
                        <label class="label-desc gray" for="education[date_from][]">
                            Date From:
                        </label>                
                        <div class="text-input-wrap">
                            <?php
                                $date_from = '';
                                $date_to = '';
                                if ($data['date_from'] != '0000-00-00' && $data['date_from'] != '' && $data['date_from'] != NULL && $data['date_from'] != '1970-01-01'){
                                    $date_from = date('Y', strtotime($data['date_from']));
                                }
                                if ($data['date_to'] != '0000-00-00' && $data['date_to'] != '' && $data['date_to'] != NULL && $data['date_to'] != '1970-01-01'){
                                    $date_to = date('Y', strtotime($data['date_to']));
                                }                                
                            ?>                   
                            <input type="text" name="education[date_from][]" id="" value="<?= $date_from ?>" class="input-text year-dtp date_from"/>
                            <img src="<?php echo base_url() ?>themes/slategray/icons/remove.png" alt="" title="" style="cursor:pointer;" class="clear-val-from">
                            &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;                        
                            <input type="text" name="education[date_to][]" id="" value="<?= $date_to ?>" class="input-text year-dtp date_from" />
                            <img src="<?php echo base_url() ?>themes/slategray/icons/remove.png" alt="" title="" style="cursor:pointer;" class="clear-val-to">
                        </div>                
                    </div>                
                    <div class="clear"></div>
                </div>    -->             
    		</fieldset>
            <div class="spacer"></div>
		<?php endforeach; ?>
    <?php endif; ?>
</div>