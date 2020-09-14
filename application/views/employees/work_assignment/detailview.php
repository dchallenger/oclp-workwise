<div>
    <?php
    if (count($work_assignment) > 0):
        foreach ($work_assignment as $data):
            ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="work_assignment[assignment]">
                        Assignment:
                        <span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><?= ($data['assignment'] == 1 ? "Primary" : "Concurrent") ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="work_assignment[employee_work_assignment_category_id][]">
                        Assignment Category:
                    </label>
                    <div class="text-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->where('employee_work_assignment_category_id',$data['employee_work_assignment_category_id']);
                            $assignment_category = $this->db->get('employee_work_assignment_category')->row_array();        
                            echo $assignment_category['employee_work_assignment_category'];                                      
                        ?>                       
                    </div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="work_assignment[division_id][]">
                        Division:
                    </label>
                    <div class="text-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->where('division_id',$data['division_id']);                            
                            $division = $this->db->get('user_company_division')->row_array();        
                            echo $division['division'];
                        ?>
                    </div>
                </div>

                <?php if($data['assignment'] == 2):?>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="work_assignment[cost_code-division][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap"><?= $division['dvision_code'] ?></div>
                </div>
                <?php endif;?>
                
                <div class="form-item view <?= ($data['assignment'] == 1 ? 'even' : 'odd') ?>">
                    <label class="label-desc view gray" for="work_assignment[project_name_id][]">
                        Project:
                    </label>
                    <div class="text-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->where('project_name_id',$data['project_name_id']);
                            $project_name = $this->db->get('project_name')->row_array();
                            echo $project_name['project_name'];                                      
                        ?>                         
                    </div>
                </div>
                
                <?php if($data['assignment'] == 2):?>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="work_assignment[cost_code-project][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap"><?= $project_name['cost_code'] ?></div>
                </div>
                <?php endif;?>

                <div class="form-item view odd">
                    <label class="label-desc view gray" for="work_assignment[group_name_id][]">
                        Group:
                    </label>
                    <div class="text-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->where('group_name_id',$data['group_name_id']);
                            $group_name = $this->db->get('group_name')->row_array();  
                            echo $group_name['group_name'];                                             
                        ?>                         
                    </div>
                </div>

                <?php if($data['assignment'] == 2):?>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="work_assignment[cost_code-group][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap"><?= $group_name['group_code'] ?></div>
                </div>
                <?php endif;?>

                 <div class="form-item view <?= ($data['assignment'] == 1 ? 'even' : 'odd') ?>">
                    <label class="label-desc view gray" for="work_assignment[department_id][]">
                        Department:
                    </label>
                    <div class="text-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->where('department_id',$data['department_id']);
                            $department = $this->db->get('user_company_department')->row_array();
                            echo $department['department'];
                        ?>
                    </div>
                </div>

                <?php if($data['assignment'] == 2):?>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="work_assignment[cost_code-department][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap"><?= $department['department_code'] ?></div>
                </div>
                <?php endif;?>

                <?php if($data['assignment'] == 1):?>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="work_assignment[cost_code][]">
                        Cost Code/Job Order:
                    </label>
                    <div class="text-input-wrap">
                        <?php switch ($data['employee_work_assignment_category_id']) {
                            case '1':
                                echo $division['dvision_code'];
                                break;
                            case '2':
                                echo $project_name['cost_code'];
                                break;
                            case '3':
                                echo $group_name['group_code'];
                                break;
                            case '4':
                                echo $department['department_code'];
                                break;
                        }?>
                    </div>
                </div>
                <?php endif;?>

                 <div class="form-item view even">
                    <label class="label-desc view gray" for="work_assignment[code_status_id][]">
                        Code Status:
                    </label>
                    <div class="text-input-wrap">
                        <?php
                            $this->db->where('deleted',0);
                            $this->db->where('code_status_id',$data['code_status_id']);
                            $code_status = $this->db->get('code_status')->row_array();
                            echo $code_status['code_status'];                                             
                        ?>  
                    </div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="work_assignment[start_date][]">
                        Start Date:
                    </label>
                    <?php
                        if ($data['start_date'] != NULL && $data['start_date'] != '1970-01-01'){
                            $start_date = date($this->config->item('display_date_format'),strtotime($data['start_date']));
                        }
                        else{
                            $start_date = '';
                        }
                    ?>
                    <div class="text-input-wrap"><?= $start_date ?></div>
                </div>  
                <div class="form-item view even">
                    <label class="label-desc view gray" for="work_assignment[end_date][]">
                        End Date:
                    </label>
                    <?php 
                        if ($data['end_date'] != NULL && $data['end_date'] != '1970-01-01' && $data['end_date'] != '0000-00-00'){
                            $end_date = date($this->config->item('display_date_format'),strtotime($data['end_date']));
                        }
                        else{
                            $end_date = '';
                        }
                    ?>                    
                    <div class="text-input-wrap"><?= $end_date ?></div>
                </div>                                              
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
<?php endif; ?>
</div>
