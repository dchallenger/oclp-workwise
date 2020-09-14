<?php

include (APPPATH . 'controllers/recruitment/annual_manpower_planning.php');

class Firstbalfour_annual_manpower_planning extends Annual_manpower_planning
{
	public function __construct() {
		parent::__construct();
	}	


	function get_amp_user_type(){
		$amp_user_type = "";

		if( $this->user_access[$this->module_id]['post'] == 1 ){
			$amp_user_type = "hr";
			$response['employee_id'] = "";			
		}
		else{
			$response['category_id'] = "";
			$response['category_value_id'] = "";
			$amp_user_type = "employee";
			$response['employee_id'] = $this->userinfo['user_id'];

			// commented since employee_work_assignment_category will not be used
			// $this->db->join('employee_work_assignment_category', 'employee_work_assignment_category.employee_work_assignment_category_id = employee_work_assignment.employee_work_assignment_category_id', 'left');
			// $category = $this->db->get_where('employee_work_assignment', array('employee_id'=> $this->userinfo['user_id'], 'assignment' => 1, 'employee_work_assignment.deleted' => 0));
			
			// if ($category && $category->num_rows() > 0) {
			// 	$category = $category->row();
			// 	$response['category_id'] = $category->employee_work_assignment_category_id;

			// 		switch ($category->employee_work_assignment_category_id) {
			// 			case 1: //by division
			// 				$response['category_value_id'] = $category->division_id;
			// 				break;
			// 			case 2: //by project
			// 				$response['category_value_id'] = $category->project_name_id;
			// 				break;	
			// 			case 3: //by group
			// 				$response['category_value_id'] = $category->group_name_id;
			// 				break;
			// 			case 4: //by department
			// 				$response['category_value_id'] = $category->department_id;
			// 				break;
																		
			// 		}				    
				
			// }
			
		}

		$response['amp_user_type'] = $amp_user_type;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}


	function get_category_value(){

		$category_id = $this->input->post('category_id');
		
		$defaults = array();
		$options = '';
		$options = '<option value=" "> </option>';

		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('annual_manpower_planning',array("annual_manpower_planning_id" => $this->input->post('record_id')))->row();	
			$annual_manpower_planning_category = $result->category_id;
			$annual_manpower_planning_category_value = $result->category_value_id;			
		}else{
			$category_value = $this->input->post('category_value');
			if ($category_value != "null") {
				$annual_manpower_planning_category = $this->input->post('category_id');
				$annual_manpower_planning_category_value = $category_value;			
			}
			
		}

		switch ($category_id) {
			case 1: //by division
				$this->db->where('deleted',0);
				$result = $this->db->get('user_company_division');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($annual_manpower_planning_category == $category_id){
							$selected = ($row->division_id == $annual_manpower_planning_category_value) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->division_id . '"' . $selected . '>' . $row->division . '</option>';
					}
				}
				break;
			case 3: //by group
				$this->db->where('deleted',0);
				$result = $this->db->get('group_name');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($annual_manpower_planning_category == $category_id){
							$selected = ($row->group_name_id == $annual_manpower_planning_category_value) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->group_name_id . '"' . $selected . '>' . $row->group_name . '</option>';
					}
				}
				break;
			case 4: //by department
				$this->db->where('deleted',0);
				$result = $this->db->get('user_company_department');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($annual_manpower_planning_category == $category_id){
							$selected = ($row->department_id == $annual_manpower_planning_category_value) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->department_id . '"' . $selected . '>' . $row->department . '</option>';
					}
				}
				break;
			case 2: //by project
				$this->db->where('deleted',0);
				$result = $this->db->get('project_name');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($annual_manpower_planning_category == $category_id){
							$selected = ($row->project_name_id == $annual_manpower_planning_category_value) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->project_name_id . '"' . $selected . '>' . $row->project_name . '</option>';
					}
				}
				break;																			
		}
		$this->load->view('template/ajax', array('html' => $options));
	}

	function get_position_per_category(){

		$category_id = $this->input->post('category_id');
		$category_value_id = $this->input->post('category_value');
		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		$this->db->where('deleted',0);
		$this->db->order_by('annual_manpower_planning_remarks.sequence','asc');			
		$remarks = $this->db->get('annual_manpower_planning_remarks');
		
		switch ($category_id) {
			case 1: //by division
				$where = $this->db->dbprefix.'user.division_id = ' . $category_value_id;
				break;		
			case 3: //by group
				$where = $this->db->dbprefix.'user.group_name_id  =' . $category_value_id;
				break;				
			case 4: //by department
				$where = $this->db->dbprefix.'user.department_id = ' . $category_value_id;
				break;	
			case 2: //by project
				$where = $this->db->dbprefix.'user.project_name_id =' . $category_value_id ;
				break;																			
		}

		$this->db->select('user.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');
    	$this->db->join('employee','employee.user_id = user.user_id');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
        // $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');
    	// $this->db->where('user_company_department.department_id',$this->input->post('department_id'));
    	$this->db->where('user.position_id !=', 0);
    	$this->db->where($where, '', false);
    	$this->db->order_by('user_rank.rank_index','DESC');
    	$this->db->group_by('user.position_id');

    	$position = $this->db->get('user');
		// dbug($this->db->last_query());die();

    	$ctr = 1;

		$html = '<table id="module-access" style="width:100%" class="default-table boxtype">
		    <colgroup width="15%"></colgroup>
		    <thead>
		    	<tr>
		    		<th colspan="14" style="text-align:left;">Positions with Incumbent</th>
		    	</tr>
			    <tr>
			        <th style="vertical-align:middle">Employees</th>';
		            	// Display header
			            foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';
			    $html .= '<th class="even"><span></span></th></tr>
		    </thead>
		    <tbody class="structure_list">';

		    if ($position && $position->num_rows() > 0){
			    foreach($position->result() as $position_row){

		    		$this->db->select('user.user_id, employee.employed_date, employment_status.employment_status, user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname, " ", middleinitial) name',false);
			    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
			    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');
			    	$this->db->join('employee','employee.user_id = user.user_id','left');
			    	$this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');
	        		// $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');

			    	// $this->db->where('user_company_department.department_id',$this->input->post('department_id'));
			    	$this->db->where($where, '', false);
			    	// $this->db->where('user.position_id !=', 0);
			    	$this->db->where('user_position.position_id',$position_row->position_id);
			    	$user = $this->db->get('user');
					
			    	$incumbent_count = $user->num_rows();

				    $html .= '<tr>



				        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14">
				        	<span>
				        		<span>'.$position_row->position.' ( '.$incumbent_count.' ) </span>
				        	</span>
						</th>

				    </tr>';

				    	foreach($user->result() as $user_row){

				    		$tooltip = '<table>
								<tr>
									<td style=\'text-align:right; font-weight:bold;\'>Employment Status</td>
									<td> : </td>
									<td style=\'text-align:left;\'>'.$user_row->employment_status.'</td>
								</tr>
								<tr>
									<td style=\'text-align:right; font-weight:bold;\'>Hired Date</td>
									<td> : </td>
									<td style=\'text-align:left;\'>'.date('F d, Y',strtotime($user_row->employed_date)).'</td>
								</tr>
							</table>';

						    $html .= '<tr id="'.$user_row->user_id .'" class="'.($ctr % 2 == 0 ? "even" : "odd").' position_with_incumbent">
						    	<input type="hidden" name="user_id[]" value="'.$user_row->user_id.'">
								<input type="hidden" name="position_id[]" value="'.$user_row->position_id.'">

						        <th style="border-top: none;" class="text-left">
						        	<ul type="disc" style="font-size:11px; padding-left:20px;">
						        		<li><a href="javascript:void(0)" tooltip="'.$tooltip.'">&bull; '.$user_row->name.'</a></li>
						        	</ul>
						        </th>';


						        foreach( $list_month as $index => $month){

									if( $user_row->user_id != $this->userinfo['user_id'] ){

										$html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
											<select style="width:60px" name="remarks_'.strtolower($month).'[]" class="manpower_setup">
												<option value="">Select</option>';

												foreach ($remarks->result() as $row_remarks):
													$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'">'.$row_remarks->remarks.'</option>';
												endforeach;

										$html .= '</select>
											</td>';

									}
									else{

										$html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
											<select style="width:60px" disabled="" name="remarks_'.strtolower($month).'[]" class="manpower_setup">
												<option value="">Select</option>';

												foreach ($remarks->result() as $row_remarks):
													$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'">'.$row_remarks->remarks.'</option>';
												endforeach;

										$html .= '</select>
											</td>';


									}
								}

						    $html .= '<td>&nbsp;</td></tr>';

						}
						


					$ctr++; 
				}
			}



		$html .= '</tbody>
		</table>
		<div class="spacer"></div>';
		$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function get_position_per_category_edit(){

		$category_id = $this->input->post('category_id');
		$category_value_id = $this->input->post('category_value');
		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		$this->db->where('deleted',0);
		$this->db->order_by('annual_manpower_planning_remarks.sequence','asc');			
		$remarks = $this->db->get('annual_manpower_planning_remarks');
		
		switch ($category_id) {
			case 1: //by division
				$where = $this->db->dbprefix.'user.division_id = ' . $category_value_id;
				break;		
			case 3: //by group
				$where = $this->db->dbprefix.'user.group_name_id  =' . $category_value_id;
				break;				
			case 4: //by department
				$where = $this->db->dbprefix.'user.department_id = ' . $category_value_id;
				break;	
			case 2: //by project
				$where = $this->db->dbprefix.'user.project_name_id =' . $category_value_id ;
				break;																			
		}

       $this->db->select('annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`, annual_manpower_planning_details.disapproved',false);
        $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');   
        $this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
        $this->db->join('user_position','user.position_id = user_position.position_id', 'left');
        $this->db->join('employee','employee.user_id = user.user_id');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
        // $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');
        // $this->db->where('user_company_department.department_id',$this->input->post('department_id'));
        $this->db->where('user.position_id !=', 0);
        $this->db->where($where, '', false);
        $this->db->order_by('user_rank.rank_index','DESC');
        $this->db->group_by('user.position_id');      

        $position = $this->db->get('annual_manpower_planning_details');

    	$ctr = 1;

		$html = '<table id="module-access" style="width:100%" class="default-table boxtype">
		    <colgroup width="15%"></colgroup>
		    <thead>
		    	<tr>
		    		<th colspan="14" style="text-align:left;">Positions with Incumbent</th>
		    	</tr>
			    <tr>
			        <th style="vertical-align:middle">Employees</th>';
		            	// Display header
			            foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';
			    $html .= '<th class="even"><span></span></th></tr>
		    </thead>
		    <tbody class="structure_list">';

		    if ($position && $position->num_rows() > 0){
			    foreach($position->result() as $position_row){

		    		$this->db->select('user.user_id, employee.employed_date, employment_status.employment_status, user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname, " ", middleinitial) name ',false);
			    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
			    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');
			    	$this->db->join('employee','employee.user_id = user.user_id','left');
			    	$this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');
	        		// $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');

			    	$this->db->where($where, '', false);
			    	// $this->db->where('user.position_id !=', 0);
			    	$this->db->where('user_position.position_id',$position_row->position_id);

			    	$user = $this->db->get('user');
			    	$incumbent_count = $user->num_rows();

				    $html .= '<tr>



				        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14">
				        	<span>
				        		<span>'.$position_row->position.' ( '.$incumbent_count.' ) </span>
				        	</span>
						</th>

				    </tr>';

				    	foreach($user->result() as $user_row){

				    		$tooltip = '<table>
								<tr>
									<td style=\'text-align:right; font-weight:bold;\'>Employment Status</td>
									<td> : </td>
									<td style=\'text-align:left;\'>'.$user_row->employment_status.'</td>
								</tr>
								<tr>
									<td style=\'text-align:right; font-weight:bold;\'>Hired Date</td>
									<td> : </td>
									<td style=\'text-align:left;\'>'.date('F d, Y',strtotime($user_row->employed_date)).'</td>
								</tr>
							</table>';
							$red =  '';
							if( $position_row->disapproved == 1 ){
								$red = 'class="red"';
							};
						    $html .= '<tr id="'.$user_row->user_id .'" class="'.($ctr % 2 == 0 ? "even" : "odd").' position_with_incumbent">
						    	<input type="hidden" name="user_id[]" value="'.$user_row->user_id.'">
								<input type="hidden" name="position_id[]" value="'.$user_row->position_id.'">

						        <th style="border-top: none;" class="text-left">
						        	<ul type="disc" style="font-size:11px; padding-left:20px;">
						        		<li><a href="javascript:void(0)" tooltip="'.$tooltip.'"><span '.$red.'>&bull; '.$user_row->name.'</span></a></li>
						        	</ul>
						        </th>';


						        foreach( $list_month as $index => $month){
						        	$monthsmall = strtolower($month);
									if( $user_row->user_id != $this->userinfo['user_id'] ){

										$html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
											<select style="width:60px" name="remarks_'.strtolower($month).'[]" class="manpower_setup">
												<option value="">Select</option>';

											foreach ($remarks->result() as $row_remarks):
												$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'" '.($position_row->$monthsmall == $row_remarks->annual_manpower_planning_remarks_id ? "SELECTED" : "").'>'.$row_remarks->remarks.'</option>';
											endforeach;

										$html .= '</select>
											</td>';

									}
									else{

										$html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
											<select style="width:60px" disabled="" name="remarks_'.strtolower($month).'[]" class="manpower_setup">
												<option value="">Select</option>';

											foreach ($remarks->result() as $row_remarks):
												$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'" '.($position_row->$monthsmall == $row_remarks->annual_manpower_planning_remarks_id ? "SELECTED" : "").'>'.$row_remarks->remarks.'</option>';
											endforeach;

										$html .= '</select>
											</td>';


									}
								}

						    $html .= '<td>&nbsp;</td></tr>';

						}
						


					$ctr++; 
				}
			}



		$html .= '</tbody>
		</table>
		<div class="spacer"></div>';
		$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function get_existing_headcount(){

		$record_id = $this->input->post('record_id');
		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		$category_id =  4; //$this->input->post('category_id');
		$category_value_id = $this->input->post('category_value');
		switch ($category_id) {
			case 1: //by division
				$where = $this->db->dbprefix.'user.division_id = ' . $category_value_id;
				break;		
			case 3: //by group
				$where = $this->db->dbprefix.'user.group_name_id  =' . $category_value_id;
				break;				
			case 4: //by department
				$where = $this->db->dbprefix.'user.department_id = ' . $category_value_id;
				break;	
			case 2: //by project
				$where = $this->db->dbprefix.'user.project_name_id =' . $category_value_id ;
				break;																			
		}

		if( $record_id == '-1' ){

			$html = '
				<table class="default-table boxtype" style="width:100%" id="module-exist-headcount">
			        <colgroup width="15%"></colgroup>
			        <thead>
			            <tr class="">
			                <th style="text-align:left;" colspan="17">Existing Job</th>
			            </tr>
			            <tr class="">
			                <th style="vertical-align:middle"><small>&nbsp;</small></th>
			                <th class="action-name font-smaller even"><div>Previous AMP</div></th>';

			foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';

			$html .='
			                <th class="action-name font-smaller even"><span>Total</span></th>
			                <th class="action-name font-smaller odd"><div></div></th>
			                <th class="action-name font-smaller even"><span><small>&nbsp;</small></span></th>
			            </tr>
			        </thead>';

			$this->db->select('user.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
	    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
	    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');
	    	$this->db->join('employee','employee.user_id = user.user_id');
	        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
	        // $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');
	    	// $this->db->where('user_company_department.department_id',$this->input->post('department_id'));
	    	$this->db->where('user.position_id !=', 0);
	    	$this->db->where($where, '', false);
	    	$this->db->order_by('user_rank.rank_index','DESC');
	    	$this->db->group_by('user.position_id');
	    	$result = $this->db->get('user');

	    	if ($result && $result->num_rows() > 0){
	    		$position_hierarchy = $result->result_array();
	    	}

			if( count($position_hierarchy) > 0 ){
				foreach( $position_hierarchy as $position_hierarchy_record ){
					
					$previous_amp = 0;

					if( $this->input->post('year') ){

						//$year = date('Y',strtotime('- 1 year',strtotime($this->input->post('year'))));
						$year = $this->input->post('year') - 1;

						$this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
						$this->db->where('annual_manpower_planning_position.position_id',$position_hierarchy_record['position_id']);
						$this->db->where('annual_manpower_planning_position.type',2);
						$this->db->where('annual_manpower_planning.year',$year);
						$this->db->where('annual_manpower_planning.category_id',$category_id);
						$this->db->where('annual_manpower_planning.annual_manpower_planning_status_id',3);
						$previous_amp_result = $this->db->get('annual_manpower_planning_position');

						if( $previous_amp_result->num_rows() > 0 ){
							$previous_amp_record = $previous_amp_result->row_array();

							$previous_amp = $previous_amp_record['total'];

						}
					}

					$html .='
					        <tbody>
					            <tr>
					                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="17">'.$position_hierarchy_record['position'].'
					                <input type="hidden" name="existing_position[]" class="existing_position_id" value="'.$position_hierarchy_record['position_id'].'" />
					                </th>
					            </tr>
					            <tr>
					                <th style="border-top:none;">Headcount</th>
					                <td style="text-align:center"><input type="text" style="width:30px" readonly="" value="'.$previous_amp.'" class="existing_job_headcount_previous" name="existing_job_headcount_previous[]" /></td>';

					foreach ( $list_month as $index => $month ){

					    $html .= '<td style="text-align:center"><input type="text" style="width:30px" class="existing_headcount_month_value" value="0" name="existing_job_headcount_'.strtolower($month).'[]" /></td>';

					}

					$html .= '<td style="text-align:center"><input type="text" style="width:30px" value="0" readonly="" class="existing_headcount_month_total" name="existing_job_headcount_total[]" /></td>
					                <td>&nbsp;</td>
					                <td>&nbsp;</td>
					            </tr>
					        </tbody>';
				        
				}
			}
			else{

				$html .= '<tbody class="existing_headcount_position_empty" ><tr><td style="text-align:center; font-weight:bold;" colspan="17">No existing job available</td></tr></tbody>';

			}

			$html .='</table>';
    	}

    	else{

    		$existing_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $record_id, 'type' => 2, 'deleted' => 0 ) );


    		$this->db->join('user','user.employee_id = annual_manpower_planning.employee_id','left');
    		$this->db->where('annual_manpower_planning.annual_manpower_planning_id',$record_id);
    		$annual_manpower_planning_head_info = $this->db->get('annual_manpower_planning')->row();
	    		$html = '
				<table class="default-table boxtype" style="width:100%" id="module-exist-headcount">
			        <colgroup width="15%"></colgroup>
			        <thead>
			            <tr class="">
			                <th style="text-align:left;" colspan="17">Existing Job</th>
			            </tr>
			            <tr class="">
			                <th style="vertical-align:middle"><small>&nbsp;</small></th>
			                <th class="action-name font-smaller even"><div>Previous AMP</div></th>';

			foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';

			$html .='
			                <th class="action-name font-smaller even"><span>Total</span></th>
			                <th class="action-name font-smaller odd"><div></div></th>
			                <th class="action-name font-smaller even"><span><small>&nbsp;</small></span></th>
			            </tr>
			        </thead>';

			// $position_hierarchy = $this->_get_reporting_to_position_hierarchy($annual_manpower_planning_head_info->position_id);
			$positions_array = array();
			foreach ($existing_position->result_array() as $key ) {
			       	$positions_array[]=$key['position_id'];
			       }
			$this->db->where_in('position_id', $positions_array);
	    	$position_hierarchy = $this->db->get('user_position')->result_array();	

			if( count($position_hierarchy) > 0 ){

				foreach( $position_hierarchy as $position_hierarchy_record ){

					$count = 0;

					foreach( $existing_position->result_array() as $existing_position_record ){

						if( $position_hierarchy_record['position_id'] == $existing_position_record['position_id'] ){

							$previous_amp = 0;

							if( $this->input->post('year') ){

								//$year = date('Y',strtotime('- 1 year',strtotime($this->input->post('year'))));
								$year = $this->input->post('year') - 1;

								$this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
								$this->db->where('annual_manpower_planning_position.position_id',$existing_position_record['position_id']);
								$this->db->where('annual_manpower_planning_position.type',2);
								$this->db->where('annual_manpower_planning.year',$year);
								$this->db->where('annual_manpower_planning.category_id',$category_id);
								$this->db->where('annual_manpower_planning.annual_manpower_planning_status_id',3);
								$previous_amp_result = $this->db->get('annual_manpower_planning_position');

								if( $previous_amp_result->num_rows() > 0 ){

									$previous_amp_record = $previous_amp_result->row_array();

									$previous_amp = $previous_amp_record['total'];

								}
							}
							$count++;
							$red = "";

							if( $existing_position_record['disapproved'] == 1 ){
								$red='red';
							}

							$html .='
							        <tbody>
							            <tr>
							                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="17"><span class="'.$red.'">'.$position_hierarchy_record['position'].'</span>
							                <input type="hidden" name="existing_position[]" class="existing_position_id" value="'.$position_hierarchy_record['position_id'].'" />
							                </th>
							            </tr>
							            <tr>
							                <th style="border-top:none;">Headcount</th>
							                <td style="text-align:center"><input type="text" style="width:30px" readonly="" value="'.$previous_amp.'" class="existing_job_headcount_previous" name="existing_job_headcount_previous[]" /></td>';

							foreach ( $list_month as $index => $month ){

							    $html .= '<td style="text-align:center"><input type="text" style="width:30px" class="existing_headcount_month_value" value="'.$existing_position_record[strtolower($month)].'" name="existing_job_headcount_'.strtolower($month).'[]" /></td>';

							}

							$html .= '<td style="text-align:center"><input type="text" style="width:30px" value="'.$existing_position_record['total'].'" readonly="" class="existing_headcount_month_total" name="existing_job_headcount_total[]" /></td>
							                <td>&nbsp;</td>
							                <td>&nbsp;</td>
							            </tr>
							        </tbody>';

						}

					}

					if( $count == 0 ){

						$html .='
						        <tbody>
						            <tr>
						                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="17">'.$position_hierarchy_record['position'].'
						                <input type="hidden" name="existing_position[]" class="existing_position_id" value="'.$position_hierarchy_record['position_id'].'" />
						                </th>
						            </tr>
						            <tr>
						                <th style="border-top:none;">Headcount</th>
						                <td style="text-align:center"><input type="text" style="width:30px" readonly="" class="existing_job_headcount_previous" name="existing_job_headcount_previous[]" /></td>';

						foreach ( $list_month as $index => $month ){

						    $html .= '<td style="text-align:center"><input type="text" style="width:30px" class="existing_headcount_month_value" value="0" name="existing_job_headcount_'.strtolower($month).'[]" /></td>';

						}

						$html .= '<td style="text-align:center"><input type="text" style="width:30px" value="0" readonly="" class="existing_headcount_month_total" name="existing_job_headcount_total[]" /></td>
						                <td>&nbsp;</td>
						                <td>&nbsp;</td>
						            </tr>
						        </tbody>';
					}
				        
				}
			}
			else{

				$html .= '<tbody class="existing_headcount_position_empty" ><tr><td style="text-align:center; font-weight:bold;" colspan="17">No existing job available</td></tr></tbody>';


			}

			$html .='
			    </table>';

    	}

    	$data['html'] = $html;

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function get_positions()
	{
		$category_id = $this->input->post('category_id');
		$category_value_id = $this->input->post('category_value');
		switch ($category_id) {
			case 1: //by division
				$where = $this->db->dbprefix.'user.division_id = ' . $category_value_id;
				break;		
			case 3: //by group
				$where = $this->db->dbprefix.'user.group_name_id  =' . $category_value_id;
				break;				
			case 4: //by department
				$where = $this->db->dbprefix.'user.department_id = ' . $category_value_id;
				break;	
			case 2: //by project
				$where = $this->db->dbprefix.'user.project_name_id =' . $category_value_id ;
				break;																			
		}

			$this->db->select('user.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
	    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
	    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');
	    	$this->db->join('employee','employee.user_id = user.user_id');
	        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
	        // $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');
	       	$this->db->where('user.position_id !=', 0);
	    	$this->db->where($where, '', false);
	    	$this->db->order_by('user_rank.rank_index','DESC');
	    	$this->db->group_by('user.position_id');

	    	$existing_positions = $this->db->get('user')->result_array();
			$positions_array = array();
			foreach ($existing_positions as $key) {
				$positions_array[] = $key['position_id'];	
			}

			if (count($positions_array) > 0) {
				$this->db->where_not_in('position_id', $positions_array);
			}
			$this->db->where('deleted', 0);
			$positions = $this->db->get('user_position')->result();	
			$options = '';
			foreach ($positions as $position) {
				$options .= '<option value="' . $position->position_id . '">' . $position->position . '</option>';
			}
			
			$this->load->view('template/ajax', array('html' => $options));
	}

	function get_form_existing_position(){

		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		$category_id = $this->input->post('category_id');
		$previous_amp = 0;

		if( $this->input->post('year') ){
			$year = $this->input->post('year') - 1;

			$this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
			$this->db->where('annual_manpower_planning_position.position_id',$this->input->post('position_id'));
			$this->db->where('annual_manpower_planning_position.type',2);
			$this->db->where('annual_manpower_planning.year',$year);
			$this->db->where('annual_manpower_planning.category_id',$category_id);
			$this->db->where('annual_manpower_planning.annual_manpower_planning_status_id',3);
			$previous_amp_result = $this->db->get('annual_manpower_planning_position');

			if( $previous_amp_result->num_rows() > 0 ){
				$previous_amp_record = $previous_amp_result->row_array();
				$previous_amp = $previous_amp_record['total'];
			}
		}

		$html .='<table class="default-table boxtype" style="width:100%" id="module-exist-headcount">
		<colgroup width="15%"></colgroup>
		        <tbody>
		            <tr>
		                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="19">'.$this->input->post('position') .'
		                <input type="hidden" name="existing_position[]" class="existing_position_id" value="'.$this->input->post('position_id').'" />
		                </th>
		            </tr>
		            <tr>
		                <th style="border-top:none;">Headcount</th>
		                <td>&nbsp;</td>
		                <td style="text-align:center"><input type="text" style="width:30px" readonly="" value="'.$previous_amp.'" class="existing_job_headcount_previous" name="existing_job_headcount_previous[]" /></td>
		                 <td>&nbsp;</td>';

		foreach ( $list_month as $index => $month ){

		    $html .= '<td style="text-align:center"><input type="text" style="width:30px" class="existing_headcount_month_value" value="0" name="existing_job_headcount_'.strtolower($month).'[]" /></td>';

		}
		$html .= '<td style="text-align:center"><input type="text" style="width:30px" value="0" readonly="" class="existing_headcount_month_total" name="existing_job_headcount_total[]" /></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
		            </tr>
		        </tbody></table>';
        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function get_previous_headcount(){

		$category_id = 4; //$this->input->post('category_id');
		$category_value_id = $this->input->post('department_id');

		switch ($category_id) {
			case 1: //by division
				$where = $this->db->dbprefix.'user.division_id = ' . $category_value_id;
				break;		
			case 3: //by group
				$where = $this->db->dbprefix.'user.group_name_id  =' . $category_value_id;
				break;				
			case 4: //by department
				$where = $this->db->dbprefix.'user.department_id = ' . $category_value_id;
				break;	
			case 2: //by project
				$where = $this->db->dbprefix.'user.project_name_id =' . $category_value_id ;
				break;			
													
		}			

			$this->db->select('user.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
	    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
	    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');
	    	$this->db->join('employee','employee.user_id = user.user_id');
	        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
	        // $this->db->join('employee_work_assignment','employee_work_assignment.employee_id = employee.employee_id', 'left');
	    	$this->db->where('user.position_id !=', 0);
	    	$this->db->where($where, '', false); 
	    	$this->db->order_by('user_rank.rank_index','DESC');
	    	$this->db->group_by('user.position_id');

	    $previous_headcount = array();
		$position_hierarchy = $this->db->get('user')->result_array();
		
		if( count($position_hierarchy) > 0 ){

			foreach( $position_hierarchy as $position_hierarchy_record ){
				$previous_amp = 0;
				if( $this->input->post('year') ){
					$year = $this->input->post('year') - 1;

					$this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
					$this->db->where('annual_manpower_planning_position.position_id',$position_hierarchy_record['position_id']);
					$this->db->where('annual_manpower_planning_position.type',2);
					$this->db->where('annual_manpower_planning.year',$year);
					$this->db->where('annual_manpower_planning.category_id',$category_id);
					// $this->db->where($where, '', false);
					$this->db->where('annual_manpower_planning.annual_manpower_planning_status_id',3);
					$previous_amp_result = $this->db->get('annual_manpower_planning_position');

					if( $previous_amp_result->num_rows() > 0 ){

						$previous_amp_record = $previous_amp_result->row_array();

						$previous_amp = $previous_amp_record['total'];

						$data = array(
							'position_id' => $position_hierarchy_record['position_id'],
							'previous_amp' => $previous_amp
						);

					}
					else{
						$data = array(
							'position_id' => $position_hierarchy_record['position_id'],
							'previous_amp' => $previous_amp
						);
					}
				}

				$previous_headcount[] = $data;
			}
		}

		$data['json'] = $previous_headcount;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function validation(){

		if ($this->input->post('record_id') <> -1 ){

	    	$annual_manpower_planning_info = $this->db->get_where('annual_manpower_planning',array($this->key_field => $this->input->post('record_id')))->row();

	    	$year = $annual_manpower_planning_info->year;

	    	$this->db->where('category_id',$this->input->post('category_id'));		
	    	$this->db->where('category_value_id',$this->input->post('category_value_id'));
			$this->db->where('year',$this->input->post('year'));
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');

            if( ( $result->num_rows() > 0 ) && ( $year != $this->input->post('year') ) ){

            	$response['err'] = 1;
                $response['message'] = "Attention: Category, Category Value and Year already applied.";
                $response['type'] = "error";
                $data['json'] = $response;
                $error++;
            }
            else{
				$response['err'] = 0;
				$data['json'] = $response;		
			}
	    }
	    else{ 

	    	$this->db->where('category_id',$this->input->post('category_id'));		
	    	$this->db->where('category_value_id',$this->input->post('category_value_id'));	
			$this->db->where('year',$this->input->post('year'));
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');

            if($result->num_rows() > 0){
            	$response['err'] = 1;
                $response['message'] = "Attention: Category, Category Value and Year already applied.";
                $response['type'] = "error";
                $data['json'] = $response;
                $error++;
            }
            else{
				$response['err'] = 0;
				$data['json'] = $response;	
			}
	    }

		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function ajax_save()
	{	
		$error = 0;

	    if ($this->input->post('record_id') <> -1 ){

	    	$annual_manpower_planning_info = $this->db->get_where('annual_manpower_planning',array($this->key_field => $this->input->post('record_id')))->row();

	    	$year = $annual_manpower_planning_info->year;

	    	$this->db->where('category_id',$this->input->post('category_id'));		
	    	$this->db->where('category_value_id',$this->input->post('category_value_id'));	
			$this->db->where('year',$this->input->post('year'));
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');


            if( ( $result->num_rows() > 0 ) && ( $year != $this->input->post('year') ) ){

                $response->msg = "Attention: Category, Category Value and Year already applied.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $error++;
            }
            else{

                my_controller::ajax_save();
            }	    	


	    }
	    else{ 

	    	$this->db->where('category_id',$this->input->post('category_id'));		
	    	$this->db->where('category_value_id',$this->input->post('category_value_id'));		
			$this->db->where('year',$this->input->post('year'));
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');

            if($result->num_rows() > 0){
                $response->msg = "Attention: Category, Category Value and Year already applied.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $error++;
            }
            else{
            	
            	$amp_approver = array();
            	$approvers = $this->system->get_approvers_and_condition( $this->userinfo['user_id'], $this->module_id );
            	foreach($approvers as $approver){
	                $amp_approver[] = $approver['approver'];
	            }

	            $_POST['approver_id'] = implode(',', $amp_approver);
            	$_POST['created_by'] = $this->userinfo['user_id'];

                my_controller::ajax_save();

                foreach($approvers as $approver){
	                $approver['amp_id'] = $this->key_field_val;
	                $this->db->insert('annual_manpower_planning_approver', $approver);
	            }

            }	    	
	    }

	    if( $error == 0 ){

			$annual_manpower_planning_id = $this->key_field_val;

			$this->db->where('annual_manpower_planning_id',$annual_manpower_planning_id);
			$this->db->update('annual_manpower_planning',array('date_created'=>date('Y-m-d G:i:s'), 'company_id'=>$this->input->post('company_id'),'employee_id' => $this->userinfo['user_id']));

			if ($this->input->post('record_id') <> -1){
				$this->db->delete('annual_manpower_planning_details', array('annual_manpower_planning_id' => $annual_manpower_planning_id));
				$this->db->delete('annual_manpower_planning_position', array('annual_manpower_planning_id' => $annual_manpower_planning_id));
			}	

			$user_id = $this->input->post('user_id');
			$position_id = $this->input->post('position_id');
			$remarks_jan = $this->input->post('remarks_jan'); 
			$remarks_feb = $this->input->post('remarks_feb'); 
			$remarks_mar = $this->input->post('remarks_mar'); 
			$remarks_apr = $this->input->post('remarks_apr'); 
			$remarks_may = $this->input->post('remarks_may'); 
			$remarks_jun = $this->input->post('remarks_jun'); 	
			$remarks_jul = $this->input->post('remarks_jul'); 	
			$remarks_aug = $this->input->post('remarks_aug'); 
			$remarks_sep = $this->input->post('remarks_sep'); 
			$remarks_oct = $this->input->post('remarks_oct'); 
			$remarks_nov = $this->input->post('remarks_nov'); 
			$remarks_dec = $this->input->post('remarks_dec'); 																					

			if ($this->input->post('user_id'))
			{
				foreach ($this->input->post('user_id') as $index => $val){
					$array_info = array();
					$array_info['annual_manpower_planning_id'] = $annual_manpower_planning_id;
					$array_info['user_id'] = $user_id[$index];
					$array_info['position_id'] = $position_id[$index];
					$array_info['jan'] = $remarks_jan[$index];
					$array_info['feb'] = $remarks_feb[$index];
					$array_info['mar'] = $remarks_mar[$index];
					$array_info['apr'] = $remarks_apr[$index];
					$array_info['may'] = $remarks_may[$index];
					$array_info['jun'] = $remarks_jun[$index];
					$array_info['jul'] = $remarks_jul[$index];
					$array_info['aug'] = $remarks_aug[$index];
					$array_info['sep'] = $remarks_sep[$index];
					$array_info['oct'] = $remarks_oct[$index];
					$array_info['nov'] = $remarks_nov[$index];
					$array_info['dec'] = $remarks_dec[$index];
					$this->db->insert('annual_manpower_planning_details',$array_info);			
				}
			}


			if ($this->input->post('existing_position'))
			{

				$position_id = $this->input->post('existing_position');
				$existing_job_headcount_jan = $this->input->post('existing_job_headcount_jan'); 
				$existing_job_headcount_feb = $this->input->post('existing_job_headcount_feb'); 
				$existing_job_headcount_mar = $this->input->post('existing_job_headcount_mar'); 
				$existing_job_headcount_apr = $this->input->post('existing_job_headcount_apr'); 
				$existing_job_headcount_may = $this->input->post('existing_job_headcount_may'); 
				$existing_job_headcount_jun = $this->input->post('existing_job_headcount_jun'); 	
				$existing_job_headcount_jul = $this->input->post('existing_job_headcount_jul'); 	
				$existing_job_headcount_aug = $this->input->post('existing_job_headcount_aug'); 
				$existing_job_headcount_sep = $this->input->post('existing_job_headcount_sep'); 
				$existing_job_headcount_oct = $this->input->post('existing_job_headcount_oct'); 
				$existing_job_headcount_nov = $this->input->post('existing_job_headcount_nov'); 
				$existing_job_headcount_dec = $this->input->post('existing_job_headcount_dec'); 
				$existing_job_headcount_total = $this->input->post('existing_job_headcount_total'); 

				foreach ($this->input->post('existing_position') as $index => $val){
					$array_info = array();
					$array_info['annual_manpower_planning_id'] = $annual_manpower_planning_id;
					$array_info['position_id'] = $position_id[$index];
					$array_info['type'] = '2';
					$array_info['date_created'] = date('Y-m-d h:i:s');
					$array_info['jan'] = $existing_job_headcount_jan[$index];
					$array_info['feb'] = $existing_job_headcount_feb[$index];
					$array_info['mar'] = $existing_job_headcount_mar[$index];
					$array_info['apr'] = $existing_job_headcount_apr[$index];
					$array_info['may'] = $existing_job_headcount_may[$index];
					$array_info['jun'] = $existing_job_headcount_jun[$index];
					$array_info['jul'] = $existing_job_headcount_jul[$index];
					$array_info['aug'] = $existing_job_headcount_aug[$index];
					$array_info['sep'] = $existing_job_headcount_sep[$index];
					$array_info['oct'] = $existing_job_headcount_oct[$index];
					$array_info['nov'] = $existing_job_headcount_nov[$index];
					$array_info['dec'] = $existing_job_headcount_dec[$index];
					$array_info['total'] = $existing_job_headcount_total[$index];
					$this->db->insert('annual_manpower_planning_position',$array_info);			
				}
			}


			if ($this->input->post('new_position_name'))
			{

				$new_job_name = $this->input->post('new_position_name');
				$new_job_remarks = $this->input->post('new_position_remarks');
				$new_job_headcount_jan = $this->input->post('new_job_headcount_jan'); 
				$new_job_headcount_feb = $this->input->post('new_job_headcount_feb'); 
				$new_job_headcount_mar = $this->input->post('new_job_headcount_mar'); 
				$new_job_headcount_apr = $this->input->post('new_job_headcount_apr'); 
				$new_job_headcount_may = $this->input->post('new_job_headcount_may'); 
				$new_job_headcount_jun = $this->input->post('new_job_headcount_jun'); 	
				$new_job_headcount_jul = $this->input->post('new_job_headcount_jul'); 	
				$new_job_headcount_aug = $this->input->post('new_job_headcount_aug'); 
				$new_job_headcount_sep = $this->input->post('new_job_headcount_sep'); 
				$new_job_headcount_oct = $this->input->post('new_job_headcount_oct'); 
				$new_job_headcount_nov = $this->input->post('new_job_headcount_nov'); 
				$new_job_headcount_dec = $this->input->post('new_job_headcount_dec'); 
				$new_job_headcount_total = $this->input->post('new_job_headcount_total'); 

				foreach ($this->input->post('new_position_name') as $index => $val){
					$array_info = array();
					$array_info['annual_manpower_planning_id'] = $annual_manpower_planning_id;
					$array_info['position'] = $new_job_name[$index];
					$array_info['remarks'] = $new_job_remarks[$index];
					$array_info['type'] = '1';
					$array_info['date_created'] = date('Y-m-d h:i:s');
					$array_info['jan'] = $new_job_headcount_jan[$index];
					$array_info['feb'] = $new_job_headcount_feb[$index];
					$array_info['mar'] = $new_job_headcount_mar[$index];
					$array_info['apr'] = $new_job_headcount_apr[$index];
					$array_info['may'] = $new_job_headcount_may[$index];
					$array_info['jun'] = $new_job_headcount_jun[$index];
					$array_info['jul'] = $new_job_headcount_jul[$index];
					$array_info['aug'] = $new_job_headcount_aug[$index];
					$array_info['sep'] = $new_job_headcount_sep[$index];
					$array_info['oct'] = $new_job_headcount_oct[$index];
					$array_info['nov'] = $new_job_headcount_nov[$index];
					$array_info['dec'] = $new_job_headcount_dec[$index];
					$array_info['total'] = $new_job_headcount_total[$index];
					$this->db->insert('annual_manpower_planning_position',$array_info);			
				}
			}

		}
		else{

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}
		
	}

	function excel_export($record_id = 0)
	{
		
		if ($this->input->post('record_id')) {
			$record_id = $this->input->post('record_id');
		}

		$this->db->where('annual_manpower_planning_id',$record_id);
        $result = $this->db->get('annual_manpower_planning')->row();

		switch ($result->category_id) {
			case 1: //by division
				$join = $this->db->join('user_company_division', 'user_company_division.division_id = annual_manpower_planning.category_value_id');
				$select = 'division as category_value';
				break;		
			case 2: //by group
				$join = $this->db->join('group_name', 'group_name.group_name_id = annual_manpower_planning.category_value_id');
				$select = 'group_name as category_value';
				break;				
			case 3: //by department
				$join = $this->db->join('user_company_department', 'user_company_department.department_id = annual_manpower_planning.category_value_id');
				$select = 'department as category_value';
				break;	
			case 4: //by project
				$join = $this->db->join('project_name', 'project_name.project_name_id = annual_manpower_planning.category_value_id');
				$select = 'project_name as category_value';
				break;																			
		}

        $this->db->select('year,company,organization_category as category,'.$select);
        // $this->db->join('user','user.user_id = annual_manpower_planning.employee_id');              
        // $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
        $this->db->join('user_company','user_company.company_id = annual_manpower_planning.company_id');
       	$join;
        $this->db->join('organization_category','organization_category.organization_category_id = annual_manpower_planning.category_id');
        // $this->db->join('user_position','user.position_id = user_position.position_id');
        $this->db->where('annual_manpower_planning_id',$record_id);
        $department_result = $this->db->get('annual_manpower_planning');
        $department_row = $department_result->row();
        
        // dbug($this->db->last_query());die();
		$this->db->select('annual_manpower_planning_details.user_id,user_position.position_id,position,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
        $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');              
        // $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
        $this->db->join('user_position','user.position_id = user_position.position_id');
        $this->db->join('employee','employee.user_id = user.user_id');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
        $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$record_id);
        $this->db->order_by('user_rank.rank_index','DESC');
        $this->db->group_by('position_id');
        $position = $this->db->get('annual_manpower_planning_details');


        $dbfields = array('name','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
        $fields = array("Position / Employee","January","February","March","April","May","June","July","August","September","October","November","December"); 

        $dbfields2 = array('position_id','position','remarks','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
        $fields2 = array("","January","February","March","April","May","June","July","August","September","October","November","December"); 

        $dbfields3 = array('position_id','position','remarks','previous_amp','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
        $fields3 = array("","Previous AMP","January","February","March","April","May","June","July","August","September","October","November","December"); 

        $this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
        $this->db->where('annual_manpower_planning_position.annual_manpower_planning_id',$record_id);
        $this->db->where('annual_manpower_planning_position.type',2);
        $this->db->where('annual_manpower_planning_position.deleted',0);
        $existing_position = $this->db->get('annual_manpower_planning_position');

        $this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
        $this->db->where('annual_manpower_planning_position.annual_manpower_planning_id',$record_id);
        $this->db->where('annual_manpower_planning_position.type',1);
        $this->db->where('annual_manpower_planning_position.deleted',0);
        $new_position = $this->db->get('annual_manpower_planning_position');


        $remarks = array();
        $remarks_sql = $this->db->get('annual_manpower_planning_remarks')->result();

        foreach( $remarks_sql as $remarks_list ){
        	$remarks[$remarks_list->annual_manpower_planning_remarks_id] = $remarks_list->remarks;
        }

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Annual Manpower Planning List")
		            ->setDescription("Annual Manpower Planning List");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);					
		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);					

		//Initialize style
		$default_border = array(
		    'style' => PHPExcel_Style_Border::BORDER_THIN,
		    'color' => array('rgb'=>'000000')
		);

		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleArray1 = array(
			'font' => array(
				'bold' => true,
			)
		);

		$styleArrayBorder = array(
		  	'borders' => array(
		    	'allborders' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	)
		  	)
		);


		$styleDefault = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleDefaultLeft = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleDefaultLeftFill = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'fill' => array(
			  'type' => PHPExcel_Style_Fill::FILL_SOLID,
			   'color' => array(
			   	'rgb'=>'D8D8D8'
			   	), 
			   )
		);

		

		$styleHeader = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			)
		);

		$styleHeader = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
			  'type' => PHPExcel_Style_Fill::FILL_SOLID,
			   'color' => array(
			   	'rgb'=>'D8D8D8'
			   	), 
			   )
		);

		$styleHeaderFillTotal = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			)
		);

		$styleHeaderLeft = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			)
		);

		$styleHeaderLeftFill = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
			  'type' => PHPExcel_Style_Fill::FILL_SOLID,
			   'color' => array(
			   	'rgb'=>'A5A5A5'
			   	), 
			   )
		);

		$styleHeaderLeftFill2 = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
			  'type' => PHPExcel_Style_Fill::FILL_SOLID,
			   'color' => array(
			   	'rgb'=>'BFBFBF'
			   	), 
			   )
		);

		

		foreach ($fields as $field) {
			
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			$activeSheet->setCellValue($xcoor . '9', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '9')->applyFromArray($styleHeader);
			
			$alpha_ctr++;
		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', $department_row->company);
		$activeSheet->setCellValue('A2', 'Annual Manpower Planning Report');
		$activeSheet->setCellValue('A3', date('F d,Y'));

		$activeSheet->setCellValue('A5', 'Category : '.$department_row->category);
		$activeSheet->setCellValue('A6', 'Category Value : '.$department_row->category_value);
		$activeSheet->setCellValue('A7', 'Year : '.$department_row->year);

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);


		$line = 10;

		foreach($position->result() as $row){

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $row->position);
			
			foreach( $alphabet as $letters ){
				
				if($letters == 'N'){
					break;
				}

				$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill2);
			}

			$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'M'. $line);

			$line++;

			$this->db->select('CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
            $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');              
            // $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
            $this->db->join('user_position','user.position_id = user_position.position_id');
            $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$record_id);
            $this->db->where('user_position.position_id',$row->position_id);
            $this->db->order_by('annual_manpower_planning_details_id','ASC');
            $user = $this->db->get('annual_manpower_planning_details');

           // dbug($user->result());die();
            

            foreach($user->result() as $user_row){

            	$alpha_ctr = 0;			
				$sub_total = 0;

            	foreach( $dbfields as $field ){

            		
            		if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
					}

					if ($sub_ctr > 0) {
						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
					} else {
						$xcoor = $alphabet[$alpha_ctr];
					}
					

					if( $field == 'name' ){

						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, ' * '.$user_row->{$field});
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefaultLeftFill);

					}
					else{

						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $remarks[$user_row->{$field}]);
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);

					}

					$alpha_ctr++;
					

            	}

            	$line++;
            }


		}

		//for existing position
		if( ( $existing_position->num_rows() > 0 ) ){

			$line = $line + 2;
			$alpha_ctr = 0;			
			$sub_total = 0;
			$total = array();

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, 'Existing Position');

			foreach( $alphabet as $letters ){
			
				if($letters == 'P'){
					break;
				}

				$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill);
			}


			$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'O'. $line);
			$line++;

			foreach ($fields3 as $field) {
				
				if ($alpha_ctr >= count($alphabet)) {

					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$activeSheet->setCellValue($xcoor . $line, $field);
				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleHeader);
				

				$alpha_ctr++;
			}

			$activeSheet->setCellValue('O' . $line, 'Total');
			$objPHPExcel->getActiveSheet()->getStyle('O' . $line)->applyFromArray($styleHeader);
			$line++;

			

			foreach($existing_position->result() as $existing_position_info){

            	$alpha_ctr = 0;			
				$sub_total = 0;
				$sub_total_count = 0;

            	foreach( $dbfields3 as $field ){

            		
            		if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
					}

					if ($sub_ctr > 0) {
						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
					} else {
						$xcoor = $alphabet[$alpha_ctr];
					}
					

					if( $field == 'position_id' ){

						$position_name = $this->db->get_where('user_position',array('position_id' => $existing_position_info->{$field}))->row();

						$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $position_name->position);

						foreach( $alphabet as $letters ){
						
							if($letters == 'P'){
								break;
							}

							$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill2);
						}


						$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'O'. $line);
						$line++;

						
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 'Headcount');
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefaultLeftFill);
						$alpha_ctr++;
					}
					elseif( $field == 'previous_amp' ){

						$previous_amp = 0;

						if( $existing_position_info->year ){

							$year = $existing_position_info->year - 1;

							$this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
							$this->db->where('annual_manpower_planning_position.position_id',$existing_position_info->position_id);
							$this->db->where('annual_manpower_planning_position.type',2);
							$this->db->where('annual_manpower_planning.year',$year);
							$this->db->where('annual_manpower_planning.department_id',$existing_position_info->department_id);
							$this->db->where('annual_manpower_planning.annual_manpower_planning_status_id',3);
							$previous_amp_result = $this->db->get('annual_manpower_planning_position');

							if( $previous_amp_result->num_rows() > 0 ){

								$previous_amp_record = $previous_amp_result->row_array();

								$previous_amp = $previous_amp_record['total'];

							}

						}

						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $previous_amp);
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);
						$alpha_ctr++;

					}
					elseif( $field == 'position' || $field == 'remarks' ){

					}
					else{

						$sub_total_count = $sub_total_count + $existing_position_info->{$field};
						$total[$field] += $existing_position_info->{$field};
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $existing_position_info->{$field});
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);
						$alpha_ctr++;
					}

            	}

            	$objPHPExcel->getActiveSheet()->setCellValue('O' . $line, $sub_total_count);
            	$objPHPExcel->getActiveSheet()->getStyle('O' . $line)->applyFromArray($styleHeaderFillTotal);
            	$total['grand_total'] += $sub_total_count;

            	$line++;
            }

            

		}

		//for new position
		if( ( $new_position->num_rows() > 0 ) ){

			$line = $line + 2;
			$alpha_ctr = 0;			
			$sub_total = 0;
			$total = array();

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, 'New Position');

			foreach( $alphabet as $letters ){
			
				if($letters == 'O'){
					break;
				}

				$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill);
			}


			$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'N'. $line);
			$line++;

			foreach ($fields2 as $field) {
				
				if ($alpha_ctr >= count($alphabet)) {

					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$activeSheet->setCellValue($xcoor . $line, $field);
				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleHeader);
				

				$alpha_ctr++;
			}

			$activeSheet->setCellValue('N' . $line, 'Total');
			$objPHPExcel->getActiveSheet()->getStyle('N' . $line)->applyFromArray($styleHeader);
			$line++;


				foreach($new_position->result() as $new_position_info){

	            	$alpha_ctr = 0;			
					$sub_total = 0;
					$sub_total_count = 0;

	            	foreach( $dbfields2 as $field ){
	            		
	            		if ($alpha_ctr >= count($alphabet)) {
						$alpha_ctr = 0;
						$sub_ctr++;
						}

						if ($sub_ctr > 0) {
							$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
						} else {
							$xcoor = $alphabet[$alpha_ctr];
						}
						

						if( $field == 'position_id' || $field == 'remarks' ){

		
						}
						elseif( $field == 'position' ){

							$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $new_position_info->{$field});

							foreach( $alphabet as $letters ){
							
								if($letters == 'O'){
									break;
								}

								$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill2);
							}


							$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'N'. $line);
							$line++;

							
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 'Headcount');
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefaultLeft);
							$alpha_ctr++;

						}
						else{

							$sub_total_count = $sub_total_count + $new_position_info->{$field};
							$total[$field] += $new_position_info->{$field};
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $new_position_info->{$field});
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);
							$alpha_ctr++;
						}

	            	}

	            	$objPHPExcel->getActiveSheet()->setCellValue('N' . $line, $sub_total_count);
	            	$objPHPExcel->getActiveSheet()->getStyle('N' . $line)->applyFromArray($styleHeaderFillTotal);
	            	$total['grand_total'] += $sub_total_count;

	            	$line++;

	            	$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, 'Remarks');
	            	$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $new_position_info->remarks);

					foreach( $alphabet as $letters ){
					
						if($letters == 'O'){
							break;
						}

						$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleDefaultLeft);
					}


					$objPHPExcel->getActiveSheet()->mergeCells('B'. $line.':'.'N'. $line);
					$line++;


	            }

		}

		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . url_title("Annual Manpower Planning List") .  date('Y-m-d') .'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}	

	//  END
}
?>