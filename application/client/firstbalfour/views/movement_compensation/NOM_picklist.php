  <?php
  	$html  = '';

  	if($this->config->item('with_campaign') == 1)
  	{
  		$html .= '<option value="current_campaign, campaign_id, subordinates_id, employee_reporting_to">Campaign</option>';
  	}
  ?>
  <div class="icon-label-group nom_pick" style="float:right">
    <div class="icon-label">
      <a onclick="show_fields()" class="icon-16-add" href="javascript:void(0)">                        
        <span>Show Fields</span>
      </a>            
    </div>
  </div> 
<!--   <div class="form-submit-btn align-right nopadding">
	<div class="icon-label-group">
    <div class="icon-label">
      <a onclick="add_benefit()" class="icon-16-add" href="javascript:void(0)">                        
      	<span>Add Benefit</span>
      </a>            
    </div>
  </div>
</div> -->
<!-- <input type="button" value="Show" onClick="show_fields()" style="float:right;"/> -->
<!-- <div class="form-item even"> -->
	<div class="select-input-wrap align-right nom_pick" style="width: auto;float:left;">
	<!-- <div class="select-input-wrap align-left" style="width:auto"> -->
		<label class="label-desc gray">Show Movement</label>
		<select name="show_pick" id="show_pick">
			<option value="">Select...</option>
			<!-- <option value="current_cbe, new_cbe">CBE</option>		 -->
			<!-- <option value="current_code_status_id, new_code_status_id">Code Status</option>				 -->
			<option value="current_department_dummy, transfer_to">Department</option>
			<option value="current_division_dummy, division_id">Division</option>
			<!-- <option value="current_employment_status_id, new_employment_status_id">Employment Status</option> -->
			<!-- <option value="current_group_name_id, group_name_id">Group</option> -->
			<option value="current_location_dummy, location_id">Location</option>
			<!-- <option value="current_segment_2_dummy, segment_2_id">Organization</option> -->
			<option value="current_position_id, new_position_id">Position</option>
			
			<option value="current_rank_dummy, rank_id, current_employee_type_dummy, employee_type_readonly">Rank, Employee Type</option>
			<option value="current_project_name_id, project_name_id">Project</option>
			<option value="current_employee_reporting_to,employee_reporting_to">Reports To</option>	
			<option value="current_role, role">Role</option>					
			<!-- <option value="current_shift_calendar_id,new_shift_calendar_id">Work Schedule</option> -->
<!-- 			<option value="current_rank_dummy, rank_id">Rank</option> -->
			<!-- <option value="current_employee_type_dummy, employee_type_readonly">Employee type</option> -->
			<!-- <option value="current_job_level_dummy, job_level,current_range_of_rank_dummy, range_of_rank_readonly">Job Level, Range of Rank</option> -->
			<!-- <option value="current_range_of_rank_dummy, range_of_rank_readonly">Range of Rank</option> -->
<!-- 			<option value="current_rank_code_dummy, rank_code">Rank Code</option> -->
			<!-- <option value="current_company_dummy, company_id">Company</option> -->
			<!-- <option value="current_segment_1_dummy, segment_1_id">Profit Center or Non-Profit Center</option> -->
			<?php echo $html; ?>
		</select>
	</div>
<!-- </div> -->
<div class="clear"></div>