<?php
    $this->db->select('employee_movement.*,employee_movement_type.*, c.department AS old_department_name, d.department AS new_department_name, a.position AS old_position_name, b.position AS new_position_name, o_rank.job_rank AS old_rank, n_rank.job_rank AS new_rank, o_job_level.description AS old_job_level, n_job_level.description AS new_job_level, o_rank_code.job_rank_code AS old_rank_code, n_rank_code.job_rank_code AS new_rank_code, o_cmpny.company AS old_cmpny, n_cmpny.company AS new_cmpny, o_division.division AS old_division, n_division.division AS new_division, o_location.location AS old_location, n_location.location AS new_location, o_segment_1.segment_1 AS old_segment_1, n_segment_1.segment_1 AS new_segment_1, o_segment_2.segment_2 AS old_segment_2, n_segment_2.segment_2 AS new_segment_2');
    $where = '('.$this->db->dbprefix.'employee_movement.status = 3 OR '.$this->db->dbprefix.'employee_movement.status = 6) AND '.$this->db->dbprefix.'employee_movement.employee_id ='.($this->input->post('record_id') == '' || $this->input->post('record_id') == null ? $this->userinfo['user_id'] : $this->input->post('record_id'));
    $this->db->where($where);
    $this->db->join('employee_movement_type','employee_movement.employee_movement_type_id = employee_movement_type.employee_movement_type_id', 'left');
    $this->db->join('user_position AS a','employee_movement.current_position_id = a.position_id', 'left');
    $this->db->join('user_position AS b','employee_movement.new_position_id = b.position_id', 'left');
    $this->db->join('user_company_department AS c','employee_movement.current_department_id = c.department_id', 'left');
    $this->db->join('user_company_department AS d','employee_movement.transfer_to = d.department_id', 'left');
    $this->db->join('user_rank AS o_rank','employee_movement.current_rank_dummy = o_rank.job_rank_id', 'left');
    $this->db->join('user_rank AS n_rank','employee_movement.rank_id = n_rank.job_rank_id', 'left');
    $this->db->join('user_job_level AS o_job_level','employee_movement.current_job_level_dummy = o_job_level.job_level_id', 'left');
    $this->db->join('user_job_level AS n_job_level','employee_movement.job_level = n_job_level.job_level_id', 'left');
    $this->db->join('user_rank_code AS o_rank_code','employee_movement.current_rank_code_dummy = o_rank_code.job_rank_code_id', 'left');
    $this->db->join('user_rank_code AS n_rank_code','employee_movement.rank_code = n_rank_code.job_rank_code_id', 'left');
    $this->db->join('user_company AS o_cmpny','employee_movement.current_company_dummy = o_cmpny.company_id', 'left');
    $this->db->join('user_company AS n_cmpny','employee_movement.company_id = n_cmpny.company_id', 'left');
    $this->db->join('user_company_division AS o_division','employee_movement.current_division_dummy = o_division.division_id', 'left');
    $this->db->join('user_company_division AS n_division','employee_movement.division_id = n_division.division_id', 'left');
    $this->db->join('user_location AS o_location','employee_movement.current_location_dummy = o_location.location_id', 'left');
    $this->db->join('user_location AS n_location','employee_movement.location_id = n_location.location_id', 'left');
    $this->db->join('user_company_segment_1 AS o_segment_1','employee_movement.current_segment_1_dummy = o_segment_1.segment_1_id', 'left');
    $this->db->join('user_company_segment_1 AS n_segment_1','employee_movement.segment_1_id = n_segment_1.segment_1_id', 'left');
    $this->db->join('user_company_segment_2 AS o_segment_2','employee_movement.current_segment_2_dummy = o_segment_2.segment_2_id', 'left');
    $this->db->join('user_company_segment_2 AS n_segment_2','employee_movement.segment_2_id = n_segment_2.segment_2_id', 'left');
    $this->db->order_by('created_date','DESC');

    $employee=$this->db->get('employee_movement')->result_array();

    foreach($employee as $data)
    {
        if($data['new_department_name']!=null && $data['transfer_to']!=0 )
        {
?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_department_name'] != null ? $data['old_department_name'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_department_name'] != null ? $data['new_department_name'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }     
        if($data['rank_id']!=0 && $data['new_rank']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_rank'] != null ? $data['old_rank'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_rank'] != null ? $data['new_rank'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
        if($data['job_level']!=0 && $data['new_job_level']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_job_level'] != null ? $data['old_job_level'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_job_level'] != null ? $data['new_job_level'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }    
        if($data['rank_code']!=0 && $data['new_rank_code']!=null )
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_rank_code'] != null ? $data['old_rank_code'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_rank_code'] != null ? $data['new_rank_code'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
        if($data['company_id']!=0 && $data['new_cmpny']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_cmpny'] != null ? $data['old_cmpny'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_cmpny'] != null ? $data['new_cmpny'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
        if($data['division_id']!=0 && $data['new_division']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_division'] != null ? $data['old_division'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_division'] != null ? $data['new_division'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
        if($data['location_id']!=0 && $data['new_location']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_location'] != null ? $data['old_location'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_location'] != null ? $data['new_location'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
        if($data['segment_1_id']!=0 && $data['new_segment_1']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_segment_1'] != null ? $data['old_segment_1'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_segment_1'] != null ? $data['new_segment_1'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
        if($data['segment_2_id']!=0 && $data['new_segment_2']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_segment_2'] != null ? $data['old_segment_2'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_segment_2'] != null ? $data['new_segment_2'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
        if($data['new_position_name']!=null && $data['new_position_id']!=0)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['transfer_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['transfer_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= ($data['old_position_name'] != null ? $data['old_position_name'] : "&nbsp;") ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= ($data['new_position_name'] != null ? $data['new_position_name'] : "&nbsp;") ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
        if($data['new_total']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['compensation_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['compensation_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= $data['current_total'] ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= $data['new_total'] ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }   
        if($data['new_basic_salary']!=null)
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['compensation_effectivity_date']!=null ? date($this->config->item('display_date_format'), strtotime($data['compensation_effectivity_date'])) : "No Specified Date") ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[from][]">
                        From:
                    </label>
                    <div class="text-input-wrap"><?= $data['current_basic_salary'] ?></div> <span></span>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[to][]">
                        To:
                    </label>
                    <div class="text-input-wrap"><?= $data['new_basic_salary'] ?></div>
                </div>
                           
                <div class="clear"></div>
                
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }   
        if( $data['employee_movement_type_id'] == 6 || $data['employee_movement_type_id'] == 7 )
        {
    ?>
            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[type][]">
                        Movement Type:
                    </label>
                    <div class="text-input-wrap"><?= $data['movement_type'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[effectivity][]">
                        Effectivity:
                    </label>
                    <div class="text-input-wrap"><?= ($data['last_day'] != null ? date($this->config->item('display_date_format'), strtotime($data['last_day'])) : "&nbsp;") ?></div>
                </div>
                        
                <div class="clear"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="movement[remarks_leaving][]">
                        Remarks:
                    </label>
                    <div class="text-input-wrap"><?= $data['remarks_leaving'] ?></div>
                </div>
                 <div class="form-item view even">
                    <label class="label-desc view gray" for="movement[further_reason_leaving][]">
                        Reason for Leaving:
                    </label>
                    <div class="text-input-wrap"><?= $data['further_reason_leaving'] ?></div>
                </div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>

    <?php
        }  
      }
    ?>