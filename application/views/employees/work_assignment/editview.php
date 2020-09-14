<fieldset>
<div class="form-multiple-add-work_assignment">
    <input type="hidden" class="add-more-flag" value="work_assignment" />
    <input type="hidden" class="" id="no_work_assignment" value="<?php echo (count($work_assignment) > 0 ? count($work_assignment) - 1 : 0)?>" />
    <?php
    if (count($work_assignment) > 0):
        $ctr = 0;
        $no = 0;
        foreach ($work_assignment as $data): ?>
             <div style="display: block;" class="form-multiple-add">
                <h3 class="form-head">
                    <div class="align-right">
                        <span class="fh-delete">
                            <a class="delete-detail" href="javascript:void(0)">DELETE</a>
                        </span>
                    </div>
                </h3>
            <!--     <div class="form-item odd ">
                    <label class="label-desc gray" for="work_assignment[employee_id][]">
                        Employee:
                        <span class="red font-large">*</span>                                                        
                    </label>
                    <div class="select-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $user = $this->db->get('user')->result_array();        
                            print '<select id="work_assignment" name="work_assignment[employee_id][]"><option value="">Select…</option>';
                                foreach($user as $user_record){
                                    print '<option value="'.$user_record["employee_id"].'">'.$user_record["firstname"].'&nbsp'.$user_record["lastname"].'</option>';
                                }
                            print '</select>';                                       
                        ?>                
                    </div>
                </div> -->
                <div class="form-item odd">
                    <label class="label-desc gray" for="work_assignment[assignment]">
                        Assignment:
                        <span class="red font-large">*</span>
                    </label>
                    <input type="radio" value="1" name="work_assignment[assignment][<?php echo $ctr ?>]" class="assignment" <?php echo ($data['assignment'] == 1 ? 'CHECKED' : '') ?>>Primary
                    <br>
                    <input type="radio" value="2" name="work_assignment[assignment][<?php echo $ctr ?>]" class="assignment" <?php echo ($data['assignment'] == 2 ? 'CHECKED' : '') ?>>Concurrent
                    <br>                                    
                </div>             
                <div class="form-item even">
                    <label class="label-desc gray" for="work_assignment[employee_work_assignment_category_id][]">
                        Assignment Category:
                    </label>
                    <div class="select-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->order_by('employee_work_assignment_category','ASC');
                            $assignment_category_id = $this->db->get('employee_work_assignment_category')->result_array();        
                            print '<select id="employee_work_assignment_category_id" name="work_assignment[employee_work_assignment_category_id][]" class="work_assignment"><option value="">Select…</option>';
                                foreach($assignment_category_id as $assignment_category_id_record){
                                    print '<option value="'.$assignment_category_id_record["employee_work_assignment_category_id"].'" '.($assignment_category_id_record["employee_work_assignment_category_id"] == $data['employee_work_assignment_category_id'] ? 'SELECTED' : '').'>'.$assignment_category_id_record["employee_work_assignment_category"].'</option>';
                                }
                            print '</select>';                                       
                        ?>                 
                    </div>                                    
                </div>                
                <div class="form-item odd ">
                    <label class="label-desc gray" for="work_assignment[division_id][]">
                        Division:
                    </label>
                    <div class="select-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->order_by('division','ASC');
                            $division = $this->db->get('user_company_division')->result_array();        
                            print '<select id="work_assignment[division_id][]" name="work_assignment[division_id][]" class="division_id"><option value="">Select…</option>';
                                foreach($division as $division_record){
                                    print '<option value="'.$division_record["division_id"].'" '.($division_record["division_id"] == $data['division_id'] ? 'SELECTED' : '').'>'.$division_record["division"].'</option>';
                                }
                            print '</select>';                                       
                        ?>             
                    </div>                                    
                </div>    
                <div class="form-item even" <?php echo ($data['assignment'] == 2 ? 'style="display:block"' : 'style="display:none"') ?> >
                    <label class="label-desc gray" for="work_assignment[cost_code-division][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap">
                        <?php   $this->db->where('deleted',0);
                                $this->db->where('department_id',$data['division_id']);
                                $result = $this->db->get('user_company_department');
                                $div_cost_code = "";
                                if ($result && $result->num_rows() > 0){
                                    $row = $result->row();
                                    $div_cost_code = $row->department_code;           
                                }
                        ?>
                        <input type="text" class="input-text cost_code-division" readonly="readonly" value="<?=$div_cost_code?>" >
                    </div>                                    
                </div>              
                <div class="form-item <?php echo ($data['assignment'] == 2 ? 'odd' : 'even') ?>">
                    <label class="label-desc gray" for="work_assignment[project_name_id][]">
                        Project:
                    </label>
                    <div class="select-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->order_by('project_name','ASC');
                            $project_name = $this->db->get('project_name')->result_array();        
                            print '<select id="work_assignment[project_name_id][]" name="work_assignment[project_name_id][]" class="project_name_id"><option value="">Select…</option>';
                                foreach($project_name as $project_name_record){
                                    print '<option value="'.$project_name_record["project_name_id"].'" '.($project_name_record["project_name_id"] == $data['project_name_id'] ? 'SELECTED' : '').'>'.$project_name_record["project_name"].'</option>';
                                }
                            print '</select>';                                       
                        ?>              
                    </div>                                    
                </div> 
                <div class="form-item even" <?php echo ($data['assignment'] == 2 ? 'style="display:block"' : 'style="display:none"') ?> >
                    <label class="label-desc gray" for="work_assignment[cost_code-project][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap">
                        <?php   $this->db->where('deleted',0);
                                $this->db->where('project_name_id', $data['project_name_id']);
                                $result = $this->db->get('project_name');
                                $proj_cost_code = "";
                                if ($result && $result->num_rows() > 0){
                                    $row = $result->row();
                                    $proj_cost_code = $row->cost_code;           
                                }
                        ?>                    
                        <input type="text" class="input-text cost_code-project" readonly="readonly" value="<?php echo $proj_cost_code ?>" >
                    </div>                                    
                </div> 

                <div class="form-item odd ">
                    <label class="label-desc gray" for="work_assignment[group_name_id][]">
                        Group:
                    </label>
                    <div class="select-input-wrap" name="tmp">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->order_by('group_name','ASC');
                            $group_name = $this->db->get('group_name')->result_array();        
                            print '<select id="work_assignment[group_name_id][]" name="work_assignment[group_name_id][]" class="group_name_id"><option value="">Select…</option>';
                                foreach($group_name as $group_name_record){
                                    print '<option value="'.$group_name_record["group_name_id"].'" '.($group_name_record["group_name_id"] == $data['group_name_id'] ? 'SELECTED' : '').'>'.$group_name_record["group_name"].'</option>';
                                }
                            print '</select>';                                       
                        ?>             
                    </div>                                    
                </div>   
                <div class="form-item even" <?php echo ($data['assignment'] == 2 ? 'style="display:block"' : 'style="display:none"') ?> >
                    <label class="label-desc gray" for="work_assignment[cost_code-group][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap">
                        <?php   $this->db->where('deleted',0);
                                $this->db->where('group_name_id', $data['group_name_id']);
                                $result = $this->db->get('group_name');
                                $grp_cost_code = "";
                                if ($result && $result->num_rows() > 0){
                                    $row = $result->row();
                                    $grp_cost_code = $row->group_code;           
                                }
                        ?>

                        <input type="text" class="input-text cost_code-group" readonly="readonly" value="<?=$grp_cost_code?>" >
                    </div>                                    
                </div>             
                <div class="form-item <?php echo ($data['assignment'] == 2 ? 'odd' : 'even') ?>">
                    <label class="label-desc gray" for="work_assignment[department_id][]">
                        Department:
                    </label>
                    <div class="select-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->order_by('department','ASC');
                            $department = $this->db->get('user_company_department')->result_array();        
                            print '<select id="work_assignment[department_id][]" name="work_assignment[department_id][]" class="department_id"><option value="">Select…</option>';
                                foreach($department as $department_record){
                                    print '<option value="'.$department_record["department_id"].'" '.($department_record["department_id"] == $data['department_id'] ? 'SELECTED' : '').'>'.$department_record["department"].'</option>';
                                }
                            print '</select>';                                       
                        ?>               
                    </div>                                    
                </div>
                <div class="form-item even" <?php echo ($data['assignment'] == 2 ? 'style="display:block"' : 'style="display:none"') ?> >
                    <label class="label-desc gray" for="work_assignment[cost_code-department][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap">
                        <?php   $this->db->where('deleted',0);
                                $this->db->where('department_id',$data['department_id']);
                                $result = $this->db->get('user_company_department');
                                $dept_cost_code = "";
                                if ($result && $result->num_rows() > 0){
                                    $row = $result->row();
                                    $dept_cost_code = $row->department_code;           
                                }
                        ?>                    
                        <input type="text" class="input-text cost_code-department" readonly="readonly" value="<?=$dept_cost_code?>" >
                    </div>                                    
                </div>
                <?php if ($data['assignment'] == 1) {?>
                <div class="form-item odd"  >
                    <label class="label-desc gray" for="work_assignment[cost_code][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text cost_code" readonly="readonly" value="<?php echo $data['cost_code'] ?>" id="work_assignment[cost_code][]" name="work_assignment[cost_code][]">
                    </div>                                    
                </div>    
                <?php }?>
                <?php if ($data['assignment'] != 1) {?>
                <div class="form-item odd hidden" >
                    <label class="label-desc gray" for="work_assignment[cost_code][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" class="input-text cost_code" readonly="readonly" value="" id="work_assignment[cost_code][]" name="work_assignment[cost_code][]">
                    </div>                                    
                </div>    
                <?php }?>                
                <div class="form-item even" >
                    <label class="label-desc gray" for="work_assignment[code_status_id][]">
                        Code Status:
                    </label>
                    <div class="select-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->order_by('code_status','ASC');
                            $code_status = $this->db->get('code_status')->result_array();        
                            print '<select id="work_assignment[code_status_id][]" name="work_assignment[code_status_id][]" class="code_status_id"><option value="">Select…</option>';
                                foreach($code_status as $code_status_record){
                                    print '<option value="'.$code_status_record["code_status_id"].'" '.($code_status_record["code_status_id"] == $data['code_status_id'] ? 'SELECTED' : '').'>'.$code_status_record["code_status"].'</option>';
                                }
                            print '</select>';                                       
                        ?>              
                    </div>                                    
                </div>                
                <div class="form-item odd">
                    <label class="label-desc gray" for="work_assignment[start_date][]">
                        Start Date:
                        <span class="red font-large">*</span>                                                        
                    </label>
                    <div class="text-input-wrap">
                        <input type="text" name="work_assignment[start_date][]" class="input-text datepicker date start_date" value="<?php echo ($data['start_date'] != '' && $data['start_date'] != '1970-01-01' && $data['start_date'] != '0000-00-00' ? date('m/d/Y',strtotime($data['start_date'])) : '')?>">
                    </div>                                    
                </div>                
                <div class="form-item even " name="tmp">
                    <label class="label-desc gray" for="work_assignment[end_date][]">
                        End Date:
                    </label>
                    <div class="text-input-wrap" name="tmp">
                        <input type="text" name="work_assignment[end_date][]" class="input-text datepicker date end_date" value="<?php echo ($data['end_date'] != '' && $data['end_date'] != '1970-01-01' && $data['end_date'] != '0000-00-00' ? date('m/d/Y',strtotime($data['end_date'])) : '')?>">
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
             <?php $ctr++; $no++;  ?>
        <?php endforeach; ?>
<?php endif; ?>
</div>
</fieldset>