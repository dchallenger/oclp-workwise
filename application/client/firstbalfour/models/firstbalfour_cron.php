<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include (APPPATH . 'models/cron.php');

class firstbalfour_cron extends cron
{
	function __construct()
	{
		parent::__construct();
	}

	function manpower_count(){
		//cbe resigned
		$today = date('Y-m-d');
		$this->db->where('employee.cbe',1);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date <=', $today);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$cbe_resigned = $result->num_rows();
		//cbe resigned
		$this->db->where('employee.cbe',1);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date', null);
		$this->db->where('employee.employed_date <=', $today);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$cbe = $result->num_rows() - $cbe_resigned;
		
		//non-cbe resigned
		$this->db->where('employee.cbe',0);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date <=', $today);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$non_cbe_resigned = $result->num_rows();
		//non-cbe
		$this->db->where('employee.cbe',0);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date', null);
		$this->db->where('employee.employed_date <=', $today);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$none_cbe = $result->num_rows() - $non_cbe_resigned;				

		//support resigned
		$where = "FIND_IN_SET('6', segment_2_id)";  
		$this->db->where($where);		
		$this->db->where('user.deleted',0);
		$this->db->where('user.division_id',0);
		$this->db->where('employee.resigned_date <=', $today);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$support_resigned = $result->num_rows();

		//support
		$where = "FIND_IN_SET('6', segment_2_id)";  
		$this->db->where($where);		
		$this->db->where('user.deleted',0);
		$this->db->where('user.division_id',0);
		$this->db->where('employee.resigned_date', null);
		$this->db->where('employee.employed_date <=', $today);
		$this->db->join('employee', 'employee.employee_id = user.user_id');	
		$result = $this->db->get('user');
		$support = $result->num_rows() - $support_resigned;		

		$this->db->where('year',date('Y'));
		$this->db->where('month',date('m'));
		$this->db->delete('manpower_count');

		$this->db->insert('manpower_count',array("year" => date('Y'),"month" => date('m'),"cbe" => $cbe,"non_cbe" => $none_cbe,"support" => $support));

		$result = $this->db->get_where('user_company_division',array("deleted"=>0));
		if ($result && $result->num_rows() > 0){

			$this->db->where('year',date('Y'));
			$this->db->where('month',date('m'));
			$this->db->delete('manpower_count_cbe');
							
			foreach ($result->result() as $row) {
				$expr = '/(?<=\s|^)[a-z]/i';
				preg_match_all($expr, $row->division, $matches);
				$result = implode('', $matches[0]);		

				$division_alias = strtoupper($result);
				$division_id = $row->division_id;
				//resigned
				$this->db->where('user.division_id',$row->division_id);		
				$this->db->where('user.deleted',0);
				$this->db->where('employee.deleted',0);	
				$this->db->where('employee.cbe',1);		
				$this->db->where('employee.resigned_date <=', $today);	
				$this->db->join('employee', 'user.user_id = employee.user_id');
				$result = $this->db->get('user');
				$resigned = $result->num_rows();

				$this->db->where('user.division_id',$row->division_id);		
				$this->db->where('user.deleted',0);
				$this->db->where('employee.deleted',0);	
				$this->db->where('employee.cbe',1);		
				$this->db->where('employee.resigned_date', null);
				$this->db->where('employee.employed_date <=', $today);
				$this->db->join('employee', 'user.user_id = employee.user_id');
				$result = $this->db->get('user');
				$count = $result->num_rows();	

				$total_count = $count - $resigned;

				$info = array(
						"year" => date('Y'),
						"month" => date('m'),
						"division_id" => $division_id,
						"division_alias" => $division_alias,
						"manpower_count" => $total_count
					);

				$this->db->insert('manpower_count_cbe',$info);				
			}
		}


		if( $this->db->_error_message() == "" ){
			$response->msg_type = "success";
			$response->msg = "Task is successfully executed";

			return $response;	
		}
	}

	function employee_movement()
	{
		$module = $this->hdicore->get_module('employee_movement');
		
		$curr_date   = date('Y-m-d');
		$date_fields = array('compensation_effectivity_date', 'movement_effectivity_date', 'transfer_effectivity_date');

 		// Fill $where with keys as $date_fields and value as $curr_date.
		$where = array_fill_keys($date_fields, $curr_date);

		$s_where = '(';
		foreach ($where as $t => $d) {
			$a_where[] = $t . " <= '" . $d . "'";
		}
		$s_where .= implode(' OR ', $a_where);
		$s_where .= ')';

		$this->db->select(array($module->table . '.*', 'CONCAT(user.firstname, " ", user.lastname) name'));		
		$this->db->where(
			array($module->table .'.deleted' => 0, 'status' => 3, 'processed' => 0)
			);
		$this->db->where($s_where, '', false);
		$this->db->join('user user', 'user.user_id = ' . $module->table . '.employee_id', 'left');

		$results = $this->db->get($module->table);
		
		if (!$results || $this->db->_error_message() != '') {

			$response->msg_type = "error";
			$response->msg = $this->db->_error_message();

		} else if ($results->num_rows() == 0) {

			$response->msg_type = "error";
			$response->msg = "No results found";

		} else {
			$requests = $results->result();
			$complete = array();			

			foreach ($requests as $request) {
				//print ('Processing request for: ' . $request->name . "\n");				

				$this->db->where('employee_id', $request->employee_id);
				$this->db->where('deleted', 0);
				
				$this->db->limit(1);

				$employee  = $this->db->get('employee');
				$completed = TRUE;

				if ($employee->num_rows() == 0) {
					if ($this->db->insert('employee', array('employee_id' => $request->employee_id, 'user_id' => $request->employee_id))) {
						//print ("\t" . 'Employee record created.'. "\n");						
					} else {
						$response->msg_type = "error";
						$response->msg = "Failed to create employee record.";
						return $response;
					}
				}				

				// if ($this->config->item('client_number') == 3){
					if ($request->compensation_effectivity_date <= $curr_date) {
						if ($this->_employee_update_compensation($request)) {
							// print ("\t" . 'Compensation updated.' . "\n");
						} else {
							print ("\t" . 'Compensation update failed.' . "\n");
							$completed = FALSE;
						}
					}
				// }

				// if ($request->movement_effectivity_date <= $curr_date) {
				// 	if ($this->_employee_update_movement($request, $employee->row())) {
				// 		print ("\t" . 'Employee record updated.' . "\n");
				// 	} else {
				// 		print ("\t" . 'Employee record update failed.' . "\n");
				// 		$completed = FALSE;
				// 	}
				// }

				if ($request->transfer_effectivity_date <= $curr_date) {
					if ($this->employee_update_transfer($request, $employee->row())) {
						//print ("\t" . 'Movement Processed.' . "\n");
						// $completed=true;
					} else {
						$completed = FALSE;
						$response->msg_type = "error";
						$response->msg = "Movement Processed failed.";
						return $response;
					}
				}

				// if ($request->last_day <= $curr_date) {
				// 	if ($this->_employee_update_resignation($request)) {
				// 		print ("\t" . 'Employee updated.' . "\n");
				// 	} else {
				// 		print ("\t" . 'Employee update failed.' . "\n");
				// 		$completed = FALSE;
				// 	}
				// }

				if ($completed) {
					$complete[] = $request->employee_movement_id;
				}
			}	

			$this->db->flush_cache();
			$this->db->where_in('employee_movement_id', $complete);
			$this->db->update('employee_movement', array('status' => 6, 'processed' => 1));
		}

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";

		return $response;

	}

	private function employee_update_transfer($request, $employee)
	{
		$user_flag=false;
		$employee_flag=false;
		$did_update=false;
		$movement_types = explode(',', $request->employee_movement_type_id);

		// Floating Employees
		if($this->config->item('with_floating') == 1)
		{
			if(in_array(13, $movement_types))
			{
				$employees = explode(',', $request->employee_id);

				foreach($employees as $emp)
				{
					$campaign_data = array();

					if($request->recall_department != null && $request->recall_department != '' && $request->recall_department != 0)
						$this->db->update('user', array('department_id' => $request->recall_department), array('employee_id' => $emp));

					if($request->recall_campaign != null && $request->recall_campaign != '' && $request->recall_campaign != 0)
						$this->db->update('employee', array('campaign_id' => $request->recall_campaign), array('employee_id' => $emp));

				}

			}
		}

		// For Campaign Movement
		if($this->config->item('with_campaign') == 1)
		{
			$campaign_data = array();

			if($request->campaign_id != null && $request->campaign_id != '' && $request->campaign_id != 0)
				$campaign_data['campaign_id'] = $request->campaign_id;

			if($request->subordinates_id != null && $request->subordinates_id != '' && $request->subordinates_id != 0)
				$campaign_data['direct_subordinates'] = $request->subordinates_id;

			if($request->employee_reporting_to != null && $request->employee_reporting_to != '' && $request->employee_reporting_to != 0)
				$campaign_data['reporting_to'] = $request->employee_reporting_to;

			if(count($campaign_data) > 0)
				$this->db->update('employee', $campaign_data, array('employee_id' => $request->employee_id));
		}

		// 201 Movement
		if ($request->transfer_to > 0)		{ $this->db->set('department_id', $request->transfer_to); $user_flag=true;	 }
		if ($request->new_position_id > 0)	{ $this->db->set('position_id', $request->new_position_id); $user_flag=true; }
		if ($request->company_id > 0)		{ $this->db->set('company_id', $request->company_id); $user_flag=true;		 }
		if ($request->division_id > 0)		{ $this->db->set('division_id', $request->division_id); $user_flag=true;	 }
		if ($request->role > 0)				{ $this->db->set('role_id', $request->role); $user_flag=true;				 }
		if ($request->segment_1_id > 0)		{ $this->db->set('segment_1_id', $request->segment_1_id); $user_flag=true;	 }
		if ($request->segment_2_id > 0)		{ $this->db->set('segment_2_id', $request->segment_2_id); $user_flag=true;	 }

		if($user_flag){
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('user');
			$did_update=true;
		}

		if ($request->rank_id > 0)			{ $this->db->set('rank_id', $request->rank_id); $employee_flag=true; 		 	 }
		if ($request->employee_type > 0)	{ $this->db->set('employee_type', $request->employee_type); $employee_flag=true; }
		if ($request->job_level > 0)		{ $this->db->set('job_level', $request->job_level); $employee_flag=true;		 }
		if ($request->range_of_rank > 0)	{ $this->db->set('range_of_rank', $request->range_of_rank); $employee_flag=true; }
		if ($request->rank_code > 0)		{ $this->db->set('rank_code', $request->rank_code); $employee_flag=true;		 }
		if ($request->location_id > 0)		{ $this->db->set('location_id', $request->location_id); $employee_flag=true;	 }
		$this->db->set('CBE', $request->new_cbe); $employee_flag=true;

		//probitionary appointment
		if ($request->new_employment_status_id > 0){ 
			$this->db->set('status_id', $request->new_employment_status_id); 
			$this->db->set('date_probitionary_appointment', date('Y-m-d',strtotime($request->transfer_effectivity_date))); 
			$employee_flag=true;	 
		}

		if ($request->extension_no_months > 0){
			if ($employee->end_date && $employee->end_date!= null && $employee->end_date !=''){
				$prev_end_date = $employee->end_date;
				$new_end_date = date('Y-m-d',strtotime($prev_end_date . '+' . $request->extension_no_months .'month'));
				$this->db->set('end_date', $new_end_date); 
				$employee_flag=true;
			}
			else{
				$new_end_date = date('Y-m-d',strtotime($request->transfer_effectivity_date . '+' . $request->extension_no_months .'month'));
				$this->db->set('end_date', $new_end_date); 
				$employee_flag=true;				
			}
		}
		
		if($employee_flag){
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('employee');
			$did_update=true;			
		}

		if ($request->employee_reporting_to > 0) { 
			$reporting_to_employee_id = $request->employee_reporting_to;
			$qry = "UPDATE {$this->db->dbprefix}employee SET reporting_to = IF(reporting_to <> '',CONCAT(reporting_to,',','{$reporting_to_employee_id}'),'{$reporting_to_employee_id}')
					WHERE employee_id = {$request->employee_id}";
			$this->db->query($qry);
		}

		if ($request->new_shift_calendar_id > 0){
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('hr_employee_dtr_setup',array("shift_calendar_id"=>$request->new_shift_calendar_id));
		}

		/*** commented since hr_employee_work_assignment will only be used as reference
		if ($employee_flag){
			$this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('employee_work_assignment',array("end_date"=>$new_end_date));				
		}
		**/

		//employee work assignment
		if ($request->project_name_id > 0){
			$cost_code = '';
			$this->db->where('deleted',0);
			$this->db->where('project_name_id',$request->project_name_id);
			$result = $this->db->get('project_name');
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$cost_code = $row->cost_code;					
			}

			// $this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			// $this->db->update('employee_work_assignment',array("project_name_id"=>$request->project_name_id,"division_id"=>$request->division_id,"group_name_id"=>0,"department_id"=>0,"cost_code"=>$cost_code,"employee_work_assignment_category_id"=>2,"start_date"=>date('Y-m-d',strtotime($request->transfer_effectivity_date))));
			$this->db->update('user',array("project_name_id"=>$request->project_name_id,"division_id"=>$request->division_id,"group_name_id"=>0,"department_id"=>0));
		}

		/*** commented since group will not be used anymore
		if ($request->group_name_id > 0 && ($request->transfer_to == 0 || $request->transfer_to == '')){
			$this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('employee_work_assignment',array("code_status_id"=>0,"project_name_id"=>0,"division_id"=>0,"group_name_id"=>$request->group_name_id,"department_id"=>0,"employee_work_assignment_category_id"=>3,"start_date"=>date('Y-m-d',strtotime($request->transfer_effectivity_date))));
		}
		*/

		if ($request->division_id > 0 && ($request->project_name_id == 0 || $request->project_name_id == '')){
			// $this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			// $this->db->update('employee_work_assignment',array("project_name_id"=>0,"division_id"=>$request->division_id,"group_name_id"=>0,"department_id"=>0,"employee_work_assignment_category_id"=>1,"start_date"=>date('Y-m-d',strtotime($request->transfer_effectivity_date))));
			$this->db->update('user',array("project_name_id"=>0,"division_id"=>$request->division_id,"group_name_id"=>0,"department_id"=>0));
		}		

		/*** commented since hr_employee_work_assignment will only be used as reference
		if ($request->new_code_status_id > 0){
			$this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('employee_work_assignment',array("code_status_id"=>$request->new_code_status_id));
		}
		**/

		if ($request->transfer_to > 0){	
			$cost_code = '';
			$this->db->where('deleted',0);
			$this->db->where('department_id',$request->transfer_to);
			$result = $this->db->get('user_company_department');
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$cost_code = $row->department_code;		
			}

			// $this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			// $this->db->update('employee_work_assignment',array("code_status_id"=>0,"project_name_id"=>0,"division_id"=>0,"group_name_id"=>$request->group_name_id,"department_id"=>$request->transfer_to,"cost_code"=>$cost_code,"employee_work_assignment_category_id"=>4,"start_date"=>date('Y-m-d',strtotime($request->transfer_effectivity_date))));
			$this->db->update('user',array("project_name_id"=>0,"division_id"=>0,"group_name_id"=>$request->group_name_id,"department_id"=>$request->transfer_to));
		}			
		//employee work assignment

		// Change Employee Approver 
		if($request->employee_approver > 0)
		{
			$qry = "SELECT
					  DISTINCT module_id
					FROM ".$this->db->dbprefix."employee_approver
					WHERE employee_id = ".$request->employee_id."
					    AND deleted = 0";
			$modules = $this->db->query($qry);
			if($modules && $modules->num_rows() > 0)
			{
				$modules = $modules->result();
				$this->db->delete('employee_approver', array('employee_id' => $request->employee_id, 'deleted' => 0));
					foreach($modules as $module_id):
						$this->db->set('employee_id', $request->employee_id);
						$this->db->set('approver_employee_id', $request->employee_approver);
						$this->db->set('module_id', $module_id->module_id);
						$this->db->set('condition', 2);
						$this->db->set('approver', 1);
						$this->db->set('email', 1);
						$this->db->set('deleted', 0);
						$this->db->insert('employee_approver');
					endforeach;
			} else 
				echo "No Previous Approver : ".$this->db->last_query();
		}


		// Combine all for Pioneer now with openaccess
		// if($this->config->item('client_no') == 1)
		// {
			// LEAVES FOR REGULARIZATION
			if (in_array(1, $movement_types)) {

				if($this->config->item('save_leave_regularization_during_saving') == 0)
				{
					// Add leave credits, use leave setup based on employee type.
					$this->db->select('et.*, ef.application_code');
					$this->db->from('employee_type_leave_setup et');
					$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
					$this->db->where('employee_type_id', $employee->employee_type);
					$this->db->where('ef.deleted', 0);

					$leave_setup = $this->db->get();

					$date_diff = gregoriantojd(12, 31, date('Y')) - gregoriantojd(date('m'), date('d'), date('Y'));

					if ($leave_setup->num_rows() > 0) {
						$data['year'] = date('Y');
						foreach ($leave_setup->result() as $leave_type) {					
							if (!$leave_type->prorated) {
								$data[strtolower($leave_type->application_code)] = $leave_type->base;
							} else {
								// Formula for pro-rated
								$monthly = $leave_type->base / 365;
								$days_remaining = $monthly * $date_diff;
								$credits = round($days_remaining * 2) / 2;
								$data[strtolower($leave_type->application_code)] = $credits;
							}
						}

						$data['employee_id'] = $request->employee_id;

						$check_bal = $this->db->get_where('employee_leave_balance',array('employee_id' => $request->employee_id, 'year' => date('Y')));

						if( $check_bal->num_rows() == 0)
							$this->db->insert('employee_leave_balance', $data);
						else
							$this->db->update('employee_leave_balance', $data, array('employee_id' => $request->employee_id, 'year' => date('Y')));
					}
				}

				//give birthday leave credit
				$user = $this->db->get_where('user',array("user_id" => $employee->user_id));
				if ($user && $user->num_rows() > 0){
					$user_info = $user->row();
					
					$cur_year = date('Y',strtotime($request->transfer_effectivity_date));
					$bday = date('Y-m-d', strtotime($cur_year.'-'.date('m',strtotime($user_info->birth_date)).'-'.date('d',strtotime($user_info->birth_date))));

					if (strtotime($request->transfer_effectivity_date) < strtotime($bday)){
		    			$e_balance = $this->db->get_where('employee_leave_balance', array('year' => $cur_year, 'employee_id' => $employee->user_id, 'deleted' => 0));

						if($e_balance->num_rows() > 0) {
							$balance = $e_balance->row();
							$this->db->set('bl', 1);
							$this->db->where('leave_balance_id', $balance->leave_balance_id);
							$this->db->update('employee_leave_balance');
						} else {
							$data = array(
								'year' => $cur_year,
								'employee_id' => $employee->user_id,
								'bl' => 1,
								'deleted' => 0
							);
							$this->db->insert('employee_leave_balance', $data);
						}
					}
					else{
						$date_regular = new DateTime($request->transfer_effectivity_date);
						$birthdate = new DateTime($bday);
						$diff = $date_regular->diff($birthdate);	

						if ($diff->d <= 15){

			    			$e_balance = $this->db->get_where('employee_leave_balance', array('year' => $cur_year, 'employee_id' => $employee->user_id, 'deleted' => 0));

							if($e_balance->num_rows() > 0) {
								$balance = $e_balance->row();
								$this->db->set('bl', 1);
								$this->db->where('leave_balance_id', $balance->leave_balance_id);
								$this->db->update('employee_leave_balance');
							} else {
								$data = array(
									'year' => $cur_year,
									'employee_id' => $employee->user_id,
									'bl' => 1,
									'deleted' => 0
								);
								$this->db->insert('employee_leave_balance', $data);
							}
						}				
					}
				}
				//give birthday leave credit

				$this->db->set('status_id', 1);
				$this->db->set('status_effectivity', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
				$this->db->set('regular_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
				
				$this->db->where('employee_id', $request->employee_id);
				
				return $this->db->update('employee');
			}

			// LEAVES FOR PROMOTION
			// movement_type_id == 'Promotion'
			if (in_array(3, $movement_types)) {
				$this->db->set('last_promotion_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
				$this->db->where('employee_id', $request->employee_id);
				
				$this->db->update('employee');

				// $e_info = $this->hdicore->_get_userinfo($request->employee_id);
				$this->save_memo(4, 'Promoted', $request->employee_id, date('Y-m-d', strtotime($request->transfer_effectivity_date)));

				// if you are promoted and have lower inexcess. system automatically computes that
				$leave_setup = $this->db->get_where('employee_type_leave_setup', array('employee_type_id' => $request->employee_type, 'application_form_id' => 1));
				if($leave_setup && $leave_setup->num_rows() > 0)
				{
					$elb = $this->db->get_where('employee_leave_balance', array('employee_id' => $request->employee_id, 'year' => date('Y', strtotime($request->transfer_effectivity_date))));
					if($elb && $elb->num_rows() == 0) {
						return true;
					} else {
						$elb = $elb->row();
						$leave_setup = $leave_setup->row();

						//to override if exist in leave setup exemption - tirso
						$exemption_array = $this->system->get_employe_leave_setup_exemption($request->employee_id,$leave_setup->leave_setup_id);

						$inexcess_tmp = ($exemption_array['inexcess'] !== 0 ? $exemption_array['inexcess'] : $leave_setup->inexcess);
						//

						if($elb->sl > $inexcess_tmp)
						{
							$paid_sl = $elb->sl - $inexcess_tmp;
							if($elb->paid_sl)
								$paid_sl = $paid_sl + $elb->paid_sl;
							
							$sl = $inexcess_tmp;
							$lb_id = $elb->leave_balance_id;
							$this->db->update('employee_leave_balance', array('paid_sl' => $paid_sl, 'sl' => $sl), array('leave_balance_id' => $lb_id));
						}
					}
				}
				
				// if promoted from sup to officer give full credit. ticket 490 pioneer
				$promotion_d = strtotime($request->transfer_effectivity_date);
				if($this->config->item('client_no') == 1)
				{
					if(trim($request->current_employee_type_dummy) == 'Supervisor' && $request->employee_type == 1)
					{
						$this->db->select('et.*, ef.application_code');
						$this->db->from('employee_type_leave_setup et');
						$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
						$this->db->where('employee_type_id', $request->employee_type);
						$this->db->where('ef.deleted', 0);
						$leave_setup = $this->db->get();

						$data['year'] = date('Y', $promotion_d);
						$data['employee_id'] = $request->employee_id;
						foreach ($leave_setup->result() as $leave_type){
							//to override if exist in leave setup exemption - tirso
							$exemption_array = $this->system->get_employe_leave_setup_exemption($request->employee_id,$leave_type->leave_setup_id);

							$base_tmp = ($exemption_array['base'] !== 0 ? $exemption_array['base'] : $leave_type->base);
							//

							$data[strtolower($leave_type->application_code)] = $base_tmp;
						}
						$check_bal = $this->db->get_where('employee_leave_balance',array('employee_id' => $request->employee_id, 'year' => date('Y', $promotion_d)));
						if( $check_bal->num_rows() == 0)
							return $this->db->insert('employee_leave_balance', $data);
						else
							return $this->db->update('employee_leave_balance', $data, array('employee_id' => $request->employee_id, 'year' => date('Y', $promotion_d)));
					} else {
						// Add leave credits, use leave setup based on employee type.
						$this->db->select('et.*, ef.application_code');
						$this->db->from('employee_type_leave_setup et');
						$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
						$this->db->where('employee_type_id', $request->employee_type);
						$this->db->where('ef.deleted', 0);
						
						$leave_setup = $this->db->get();

						$date_diff = gregoriantojd(12, 31, date('Y', $promotion_d)) - gregoriantojd(date('m', $promotion_d), date('d', $promotion_d), date('Y', $promotion_d));

						if ($leave_setup->num_rows() > 0) {
							$data['year'] = date('Y', $promotion_d);
							foreach ($leave_setup->result() as $leave_type) {	

								//to override if exist in leave setup exemption - tirso
								$exemption_array = $this->system->get_employe_leave_setup_exemption($request->employee_id,$leave_type->leave_setup_id);

								$base_tmp = ($exemption_array['base'] !== 0 ? $exemption_array['base'] : $leave_type->base);
								//

								if (!$leave_type->prorated) {
									$data[strtolower($leave_type->application_code)] = $base_tmp;
								} else {
									// Formula for pro-rated
									$monthly = $base_tmp / 365;
									$days_remaining = $monthly * $date_diff;
									$credits = round($days_remaining * 2) / 2;
									$data[strtolower($leave_type->application_code)] = $credits;
								}
							}
							$data['employee_id'] = $request->employee_id;
							$check_bal = $this->db->get_where('employee_leave_balance',array('employee_id' => $request->employee_id, 'year' => date('Y', $promotion_d)));
							if( $check_bal->num_rows() == 0)
								$this->db->insert('employee_leave_balance', $data);
							else
								$this->db->update('employee_leave_balance', $data, array('employee_id' => $request->employee_id, 'year' => date('Y', $promotion_d)));
						}
					}
				}


			}

			//RESIGNATION, TERMINATION
			if(in_array(6, $movement_types) || in_array(7, $movement_types) || in_array(10, $movement_types) || in_array(11, $movement_types))
			{
				$current_employee_state = $this->db->get_where('employee', array("employee_id" => $request->employee_id))->row();


				if (in_array(6, $movement_types) || in_array(11, $movement_types)) {
					if ($request->current_employment_status_id == 1) {
						$this->_terminal_pay($current_employee_state->employee_type, $request->employee_id, date('Y-m-d', strtotime($request->transfer_effectivity_date)));
					}
				}

				$clearance = false;
				// Resignation
				if (in_array(6, $movement_types)) {
					$this->db->set('status_id', 8);
					if ($this->db->field_exists('end_date','employee')){
						$this->db->set('end_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
					}
					$clearance = true;
					$memo_title = 'Resigned';
				}

				// Termination
				if (in_array(7, $movement_types)) {
					$this->db->set('status_id', 11);
					if ($this->db->field_exists('end_date','employee')){
						$this->db->set('end_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
					}
					
				}

				// End of Contract
				if (in_array(10, $movement_types)) {
					$this->db->set('status_id', 10);
					$clearance = true;
				}

				// Retirement
				if (in_array(11, $movement_types)) {
					$this->db->set('status_id', 9);
					$clearance = true;
					$memo_title = 'Retirement';
				}



				

				$this->db->set('resigned', 1);
				$this->db->set('resigned_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
				$this->db->set('status_effectivity', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
				$this->db->where('employee_id', $request->employee_id);
				$this->db->update('employee');

				if($request->blacklisted == 1)
				{
					$this->db->set('blacklisted', 1);
					$this->db->where('employee_id', $request->employee_id);
					$this->db->update('employee');

				 	$applicant_id = $this->db->get_where('employee', array("employee_id" => $request->employee_id))->row()->applicant_id." *";
					$this->db->set('blacklisted', 1);
					$this->db->where('applicant_id', $applicant_id);
					$this->db->update('recruitment_applicant');
				}

				// save memo
				$this->save_memo(3, $memo_title, $request->employee_id, date('Y-m-d', strtotime($request->transfer_effectivity_date)));

				// as for ticket, this functionality is enabled
				 $lastname = $this->db->get_where('user', array("employee_id" => $request->employee_id))->row()->lastname." *";
				 $this->db->set('lastname', $lastname);
				 $this->db->where('employee_id', $request->employee_id);
				 $this->db->update('user');

				// all credits(regardless what year) will be tagged as uneditable/resigned on leave credits
				$this->db->set('uneditable', 1);
				$this->db->where('employee_id', $request->employee_id);
				$this->db->update('employee_leave_balance');

				$this->db->set('inactive', 1);
				$this->db->where('employee_id', $request->employee_id);
				$this->db->update('user');

				if($clearance){

					$clearance = $this->db->get_where('employee_clearance',array('employee_id' => $request->employee_id ));

					if($clearance->num_rows() == 0){
						$data = array(
							'employee_id' => $request->employee_id,
							'deleted' => '0'
						);

						//print ("\t" . 'Processing signatories.' . "\n");

						// Get default signatories.				
						$this->db->where('deleted', 0);
						$this->db->where('default', 1);
						$approvers = $this->db->get('employee_clearance_form_checklist');

						if ($approvers->num_rows() > 0) {
							$data['signatories'] = array();

							foreach ($approvers->result() as $r) {				
								$data['signatories'][] = $r->employee_clearance_form_checklist_id;
							}

							$data['signatories'] = implode(',', $data['signatories']);
						}

						$this->db->insert('employee_clearance',$data);

					}

					// date hired before config have special scenario
					if($this->config->item('pay_vl_date_hired_before') &&  $employee->employed_date < $this->config->item('pay_vl_date_hired_before')) {
						$elb = $this->db->get_where('employee_leave_balance', array('employee_id' => $request->employee_id, 'year' => date('Y', strtotime($request->transfer_effectivity_date))));
						if($elb && $elb->num_rows() > 0)
						{
							$elb = $elb->row();
							$paid_vl = $elb->vl;
							$lb_id = $elb->leave_balance_id;

							$this->db->update('employee_leave_balance', array('paid_vl' => $paid_vl, 'vl' => 0), array('leave_balance_id' => $lb_id));
						}
					}
					
					// skip part below
					if($this->config->item('client_no') != 1)
						return true;

					// Pro-rate leave credits, use leave setup based on employee type.
					$emp_current = $this->db->get_where('employee', array("employee_id" => $request->employee_id))->row();
					//if not regular, should not have credits.

					if($emp_current->status_id == 1)
					{
						$data = array();
						$data = array(
								'employee_id' => $request->employee_id,
								'deleted' => '0'
							);
						$this->db->select('et.*, ef.application_code');
						$this->db->from('employee_type_leave_setup et');
						$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
						$this->db->where('employee_type_id', $employee->employee_type);
						$this->db->where('et.prorated', 1);
						$this->db->where('ef.deleted', 0);
						$this->db->where('et.deleted', 0);
						
						$leave_setup = $this->db->get();
						
						$ld = strtotime($request->last_day);
						$t = date('t', $ld);
						$d = date('d', $ld);
						$n = date('n', $ld);

						if ($leave_setup->num_rows() > 0) {
							$data['year'] = date('Y', $ld);
							foreach ($leave_setup->result() as $leave_type) {


								//to override if exist in leave setup exemption - tirso
								$exemption_array = $this->system->get_employe_leave_setup_exemption($request->employee_id,$leave_type->leave_setup_id);

								$base_tmp = ($exemption_array['base'] !== 0 ? $exemption_array['base'] : $leave_type->base);
								//								
								if (!$leave_type->prorated) {
									$data[strtolower($leave_type->application_code)] = $base_tmp;
								} else {
									// Formula for pro-rated
									$monthly = $base_tmp / 12;
									$curr_month_credits = $monthly * (($t - $d) / $t);
									$year_remaining_credits = $monthly * (12 - $n);
									$credits = $curr_month_credits + $year_remaining_credits;
									$credits = round($credits * 2) / 2;

									$data[strtolower($leave_type->application_code)] = $credits;
								}
							}
						}		

						if(count($data) > 0)
							$this->db->update('employee_leave_balance', $data, array('employee_id' => $request->employee_id, 'year' => date('Y', $ld)));

					}

					return true;
				} else 
					return TRUE;
			}
			//RESIGNATION, TERMINATION
		// }

			// SALARY INCREASE
			if (in_array(2, $movement_types)) {

				$this->db->set('status_effectivity', date('Y-m-d', strtotime($request->compensation_effectivity_date)));
				
				$this->db->where('employee_id', $request->employee_id);
				
				return $this->db->update('employee');

			}

		if($did_update)
			return TRUE;
		else
		{
			//print ("\t" . 'Nothing to update. Proceeding...' . "\n");
			return TRUE;
		}
	}

	private function _terminal_pay($employee_type, $employee_id, $last_day)
	{
		$year = date('Y');

		$this->db->select('et.*, ef.application_code');
		$this->db->from('employee_type_leave_setup et');
		$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
		$this->db->where('employee_type_id', $employee_type);
		// $this->db->where('et.prorated', 1);
		$this->db->where('accumulation_type_id', 1);
		$this->db->where('ef.deleted', 0);	
		$this->db->where('et.deleted', 0);
		$leave_setup = $this->db->get();

		if ($leave_setup && $leave_setup->num_rows() > 0) {

			foreach ($leave_setup->result() as $leave_type) {

				$this->db->where('year', $year);
				$this->db->where('employee_id', $employee_id);
				$leave_balance_result = $this->db->get('employee_leave_balance');

				if ($leave_balance_result && $leave_balance_result->num_rows() > 0){
					$e_balance = $leave_balance_result->row();

					$appcode = strtolower($leave_type->application_code);
					$appcode_used = $appcode.'_used';
					$carried = 'carried_'.$appcode;
					$paid_appcode = 'paid_'.$appcode;
					$termpay = 'termpay_'.$appcode;
					
					if($appcode == 'vl'){
						$el_used = 'el_used';
						$total_leave = ($e_balance->{$carried} + $e_balance->{$appcode}) - ($e_balance->{$appcode_used} + $e_balance->{$el_used});	
					}else{
						$total_leave = ($e_balance->{$carried} + $e_balance->{$appcode}) - $e_balance->{$appcode_used};	
					}
					
					$reg_day = date('d', strtotime($last_day));
					$reg_month_year = date('Y-m', strtotime($last_day));
					$reg_month = date('m', strtotime($last_day));
					
					$work_days = $this->system->get_working_days($employee_id, date('Y-m-01', strtotime($last_day)), date('Y-m-d', (strtotime( '-1 day' , strtotime( $last_day) ))), true, false, true);

					$no_work_days = count($work_days);

					if ($no_work_days >= 12){
						$accumulation = $leave_type->credit_earned_12;
					}elseif($no_work_days >= 6){
						$accumulation = $leave_type->credit_earned_6;
					}else{
						$accumulation = 0;
					}

					$prorate_accumulation = 0;

					if (!$leave_type->prorated) {
						$remaining_months = (12 - $reg_month);
						$prorate_accumulation = $leave_type->accumulation * $remaining_months;	
						$term_pay = ($total_leave + $accumulation);	
						if ($term_pay < 0 ) {
							$term_pay = 0;	
						}				
					}else{ //yearly, managers
						$remaining_months = (12 - ($reg_month-1));
						$prorate_accumulation = $leave_type->accumulation * $remaining_months;	
						$term_pay = ($total_leave - $prorate_accumulation) + $accumulation;
						if ($term_pay < 0 && $e_balance->{$appcode_used} == 0 ) {
							$term_pay = 0;	
						}
					}

					// if (intval($employee_type) != 4) {
					// 	$term_pay = ($total_leave - $prorate_accumulation) - $accumulation;
					// }else{
					// 	$term_pay = ($total_leave + $accumulation);
					// }
					
					//commented due to ticket #1628 : reflect the negative value on the termpay
					// if ($term_pay < 0 ) {
					// 	$term_pay = 0;	
					// }
					
					$this->db->set($termpay, $term_pay, false);
					$this->db->where('leave_balance_id', $e_balance->leave_balance_id);
					$this->db->update('employee_leave_balance');
			
					
				}
			}
		}

	}

	/**
	 * Create Memo
	 * @param INT memo type id, @param STR memo title, @param INT , @param DATE publish date, @param INT if you want to publish or for peronal use..? >.<
	 */
	function save_memo($memo_type_id, $title = null, $memo_for = null, $publish_date_from = false, $publish = 1)
	{
		// initialize arrays
		$recipient_arr = array();
		$memo_arr = array(
			'memo_type_id' => $memo_type_id,
			'memo_title' => (is_null($title) ? 'Movement' : $title),
			'employee_id' => $memo_for,
			'publish_from' => (!$publish_date_from ? date('Y-m-d') : date('Y-m-d', strtotime($publish_date_from))),
			'publish_to' => (!$publish_date_from ? date('Y-m-d', strtotime('+1 week', strtotime(date('Y-m-d')))) : date('Y-m-d', strtotime('+1 week', strtotime($publish_date_from)))),
			'publish' => $publish,
			'created_by' => $this->user->user_id,
			'modified_by' => $this->user->user_id,			
		);

		// create memo
		$this->db->insert('memo', $memo_arr);
		$memo_id = $this->db->insert_id();

		// Get all employees
		$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
		$recipients = $this->db->get_where('user', array('user.deleted' => 0, 'inactive' => 0, 'employee.resigned' => 0));

		$recipient_arr['memo_id'] = $memo_id;
		foreach($recipients->result() as $recipient) {
			$recipient_arr['user_id'] = $recipient->user_id;
			$data[] = $recipient_arr;
		}

		// SHOW IT TO EVERYONE..!!!!!
		$this->db->insert_batch('memo_recipient', $data);
	}

	function balfour_monthly_accumulation()
	{

		$cdate = date('Y-m-d');
		$ymdate = date('Y-m');
		$month = date('m');
		$day = date('d');
		$prev_year = date('Y') - 1;
		$prev_ym = date('Y-m',strtotime('- 1 month',strtotime(date('Y-m'))));

		// get leave types
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_type_leave_setup.application_form_id', 'left');
		$this->db->order_by('employee_type_leave_setup.application_form_id', 'ASC');
		$this->db->where('accumulation_type_id', 1);
		$this->db->where('employee_type_leave_setup.deleted', 0);
		$leave_types = $this->db->get('employee_type_leave_setup')->result();

		foreach($leave_types as $leave_type)
		{
			// get data
			$appcode = strtolower($leave_type->application_code);
			$appcode_used = $appcode.'_used';
			$el_used = 'el_used';
			$carried = 'carried_'.$appcode;

			// initial query
			$this->db->select("
							  employee.*,
							  user.*,
							  (DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(employed_date)), '%Y') + 0) as tenureship
							  ", false);
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->where('user.deleted', 0);
			$this->db->where('user.employee_id <>', 1);
			$this->db->where('employee.employed_date <', $cdate);
			// $this->db->where('leave_accumulation_start_date <=', $cdate);
			// $this->db->where('leave_accumulation_start_date != "0000-00-00"');
			// $this->db->where('employee.CBE', $leave_type->CBE);
			$this->db->where('employee.resigned', 0);
			$this->db->where('user.inactive', 0);

			// validate employees to get
			if($leave_type->company_id)
				$this->db->where('company_id', $leave_type->company_id);

			if($leave_type->employee_type_id)
				$this->db->where('employee_type', $leave_type->employee_type_id);

			if($leave_type->employment_status_id)
				$this->db->where_in('status_id', explode(',',$leave_type->employment_status_id));

			// if($this->config->item('use_cbe_cba'))
			// {
			// 	$this->db->where('CBE', $leave_type->CBE);
			// 	$this->db->where('ECF', $leave_type->CBA);
			// }

			$users = $this->db->get('user');
			
			if(!$users && $users->num_rows() == 0)
				continue;

			// insert accumulation on all employee's satisfied
			foreach($users->result() as $user) 
			{
				// get data
				//to override if exist in leave setup exemption - tirso
				// $exemption_array = $this->system->get_employe_leave_setup_exemption($user->employee_id,$leave_type->leave_setup_id);

				// $maximum_tmp = ($exemption_array['maximum'] !== 0 ? $exemption_array['maximum'] : $leave_type->maximum);
				// $accumulation_tmp = ($exemption_array['accumulation'] !== 0 ? $exemption_array['accumulation'] : $leave_type->accumulation);
				$maximum_tmp = $leave_type->maximum;
				$accumulation_tmp = $leave_type->accumulation;

				$tenure = ($user->tenureship == 0 ? 1 : $user->tenureship);
				$accumulation = $accumulation_tmp;
				$prorate_accumulation = 0;

				// to compute and get pro rated
				// if ($user->leave_accumulation_start_date != '0000-00-00' && $user->leave_accumulation_start_date != null){
					$employed_date = $user->employed_date;
					$edate_month_year = date('Y-m', strtotime($employed_date));
					$edate_month = date('m', strtotime($employed_date));
					$edate_day = date('d', strtotime($employed_date));

					$prev_month = strtotime('-1 month');
					$prev_mont_start = date('Y-m-01',$prev_month);
					$prev_mont_end = date('Y-m-t',$prev_month);
					$date_end = new DateTime($prev_mont_end);
					$date_employed = new DateTime($employed_date);
					$no_days = date('t',$prev_month);

					$regularization = $user->regular_date;

					if ($edate_month_year === $prev_ym) {
						if ($edate_day <= 15){
							$accumulation = $leave_type->base_1_15;
						}else{
							$accumulation = $leave_type->base_16_31;
						}	
					}else{

						if ($regularization != '0000-00-00' && $regularization != null) {
							$reg_month_year = date('Y-m', strtotime($regularization));
							$reg_month = date('m', strtotime($regularization));

							if ($leave_type->prorated) {
								if ($reg_month_year === $ymdate) {
									$remaining_months = (12 - $reg_month);
									$prorate_accumulation = $accumulation * $remaining_months;
								}
							}
						}
						
						// if ($employed_date >= $prev_mont_start && $employed_date <= $prev_mont_end){
						// 	$diff = $date_end->diff($date_employed);
						// 	$no_days_to_prorate = $diff->d + 1;
						// 	$prorate_accumulation = number_format((($accumulation / $no_days) * $no_days_to_prorate),2,'.',',');
						// 	if ($prorate_accumulation < 0){
						// 		$prorate_accumulation = 0;
						// 	}
						// }
					}

				// }

				$this->db->order_by('tenure', 'DESC');
				$etlc = $this->db->get_where('employee_type_leave_credit', array('leave_setup_id' => $leave_type->leave_setup_id, 'leave_type' => $leave_type->application_form_id, 'tenure <=' => $tenure));

				if($etlc && $etlc->num_rows() > 0)
					$accumulation = $etlc->row()->leave_accumulated;

				$new_value = $accumulation + $prorate_accumulation;

				//for ticket 1691. if cron will run on month of january credit will be given on previous year
				if ($month == 01){
					$e_balance = $this->db->get_where('employee_leave_balance', array('year' => $prev_year, 'employee_id' => $user->user_id, 'deleted' => 0));
				}
				else{
					$e_balance = $this->db->get_where('employee_leave_balance', array('year' => date('Y'), 'employee_id' => $user->user_id, 'deleted' => 0));
				}

				if($e_balance->num_rows() > 0) {

					$balance = $e_balance->row();
					$total_balance = ($accumulation + $balance->{$appcode} + $balance->{$carried}) - ($balance->{$appcode_used} + $balance->{$el_used});
					$new_value = $accumulation + $balance->{$appcode} + $prorate_accumulation;

					if($maximum_tmp > 0) {
						if ((($balance->{$appcode} + $balance->{$carried}) - ($balance->{$appcode_used} + $balance->{$el_used})) >= $maximum_tmp){
							if(!$leave_type->convertible)
								$new_value = $balance->{$appcode};
						}
						else{
							if($total_balance >= $maximum_tmp) {
								if(!$leave_type->convertible)
									$new_value = ($maximum_tmp - (($balance->{$appcode} + $balance->{$carried}) - ($balance->{$appcode_used} + $balance->{$el_used}))) + $balance->{$appcode};
							}
						}
					}

					$this->db->set($appcode, $new_value, false);
					$this->db->where('leave_balance_id', $balance->leave_balance_id);
					$this->db->update('employee_leave_balance');
					
				} else {
					//for ticket 1691. if cron will run on month of january credit will be given on previous year
					if ($month == 01){
						$data = array(
							'year' => $prev_year,
							'employee_id' => $user->user_id,
							$appcode => $new_value,
							'deleted' => 0
						);						
					}
					else{
						$data = array(
							'year' => date('Y'),
							'employee_id' => $user->user_id,
							$appcode => $new_value,
							'deleted' => 0
						);
					}

					$this->db->insert('employee_leave_balance', $data);

				}

			}

		}

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		

		return $response;
	}




	function balfour_pay_all_excess()
	{

		$this->db->where('year',date(Y));
		$result = $this->db->get('employee_leave_balance_monitoring_carry_over');

		if (!$result || $result->num_rows() == 0){
			$response->msg_type = "attention";
			$response->msg = "Carry over not yet executed. Please run it first.";		
			
			return $response;			
		}

		// initialize variables
		$year = date('Y');
		$prev_year = $year - 1;

		// get leave types
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_type_leave_setup.application_form_id', 'left');
		$this->db->order_by('employee_type_leave_setup.application_form_id', 'ASC');
		$this->db->where('convertible', 1);
		$this->db->where('employee_type_leave_setup.deleted', 0);
		$leave_types = $this->db->get('employee_type_leave_setup');
		
		if(!$leave_types && $leave_types->num_rows() == 0)
			return;

		foreach($leave_types->result() as $leave_type)
		{
			// initial query
			$this->db->select("*");
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->where('user.deleted', 0);
			$this->db->where('user.employee_id <>', 1);
			$this->db->where('employee.resigned', 0);

			// validate employees to get
			if($leave_type->employee_type_id)
				$this->db->where('employee_type', $leave_type->employee_type_id);

			if($leave_type->employment_status_id)
				$this->db->where_in('status_id', explode(',',$leave_type->employment_status_id));

			$users = $this->db->get('user');
			

			if(!$users && $users->num_rows() == 0)
				continue;

			// insert accumulation on all employee's satisfied
			foreach($users->result() as $user) 
			{

				//to override if exist in leave setup exemption - tirso
				$exemption_array = $this->system->get_employe_leave_setup_exemption($user->employee_id,$leave_type->leave_setup_id);

				$inexcess_tmp = ($exemption_array['inexcess'] !== 0 ? $exemption_array['inexcess'] : $leave_type->inexcess);
				//

				if ($inexcess_tmp && $inexcess_tmp > 0){
					$this->db->where('year', $year);
					$this->db->where('employee_id', $user->employee_id);
					$leave_balance_result = $this->db->get('employee_leave_balance');

					if ($leave_balance_result && $leave_balance_result->num_rows() > 0){
						$e_balance = $leave_balance_result->row();

						$appcode = strtolower($leave_type->application_code);
						$appcode_used = $appcode.'_used';
						$carried = 'carried_'.$appcode;
						$paid_appcode = 'paid_'.$appcode;

						$total_leave = ($e_balance->{$carried} + $e_balance->{$appcode}) - $e_balance->{$appcode_used};

						$excess = $total_leave - $inexcess_tmp;

						if ($excess > 0){
							// fix to paid_sl for now because of limited data column.
							if ($excess > $e_balance->{$carried}){
								$new_carried = 0;
								$new_credits = $e_balance->{$appcode} - ($excess - $e_balance->{$carried});
								$this->db->update('employee_leave_balance', array($carried => $new_carried,$appcode => $new_credits, $paid_appcode => $excess), array('leave_balance_id' => $e_balance->leave_balance_id));
							}
							else{
								$new_carried = $e_balance->{$carried} - $excess;
								$this->db->update('employee_leave_balance', array($carried => $new_carried, $paid_appcode => $excess), array('leave_balance_id' => $e_balance->leave_balance_id));						
							}
						}

					}
				}
			}	
		}

		// for paid bil get all regular employees
		// $usersinfo = $this->db->get_where('employee', array('deleted' => 0, 'resigned' => 0, 'status_id' => 1))->result();

		// foreach($usersinfo as $userinfo)
		// {
		// 	// Check if there's a filed application
		// 	$qry = "select a.*
		// 			FROM {$this->db->dbprefix}employee_leaves a
		// 			WHERE application_form_id = 13 AND form_status_id = 3 AND date_from like '{$prev_year }-%' AND employee_id = {$userinfo->employee_id}";

		// 	$rec = $this->db->query( $qry );

		// 	if($rec && $rec->num_rows() == 0)
		// 		$this->db->update('employee_leave_balance', array('paid_bil' => 1, 'carried_bl' => 1), array('year' => $year, 'employee_id' => $userinfo->employee_id));
		// }

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		
		return $response;
	}

	function balfour_porfeiture()
	{

		// $this->db->where('year',date(Y));
		// $result = $this->db->get('employee_leave_balance_monitoring_carry_over');

		// if (!$result || $result->num_rows() == 0){
		// 	$response->msg_type = "attention";
		// 	$response->msg = "Carry over not yet executed. Please run it first.";		
			
		// 	return $response;			
		// }
		
		// initialize variables
		$year = date('Y');
		$prev_year = $year - 1;

		// get leave types
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_type_leave_setup.application_form_id', 'left');
		$this->db->order_by('employee_type_leave_setup.application_form_id', 'ASC');
		$this->db->where('convertible', 0);
		$this->db->where('employee_type_leave_setup.deleted', 0);
		$leave_types = $this->db->get('employee_type_leave_setup');
		

		if(!$leave_types && $leave_types->num_rows() == 0)
			return;

		foreach($leave_types->result() as $leave_type)
		{
			// initial query
			$this->db->select("*");
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->where('user.deleted', 0);
			$this->db->where('user.employee_id <>', 1);
			$this->db->where('employee.resigned', 0);
			// $this->db->where('employee.employee_id', 35);

			// validate employees to get
			if($leave_type->employee_type_id)
				$this->db->where('employee_type', $leave_type->employee_type_id);

			if($leave_type->employment_status_id)
				$this->db->where_in('status_id', explode(',',$leave_type->employment_status_id));

			$users = $this->db->get('user');
		
			if(!$users && $users->num_rows() == 0)
				continue;

			// insert accumulation on all employee's satisfied
			foreach($users->result() as $user) 
			{
				//to override if exist in leave setup exemption - tirso
				$exemption_array = $this->system->get_employe_leave_setup_exemption($user->employee_id,$leave_type->leave_setup_id);

				$inexcess_tmp = ($exemption_array['inexcess'] !== 0 ? $exemption_array['inexcess'] : $leave_type->inexcess);
				//
				
				if ($inexcess_tmp && $inexcess_tmp > 0){
					$this->db->where('year', $year);
					$this->db->where('employee_id', $user->employee_id);
					$leave_balance_result = $this->db->get('employee_leave_balance');

					if ($leave_balance_result && $leave_balance_result->num_rows() > 0){
						$e_balance = $leave_balance_result->row();

						$appcode = strtolower($leave_type->application_code);
						$appcode_used = $appcode.'_used';
						$carried = 'carried_'.$appcode;
						$paid_appcode = 'paid_'.$appcode;

						if ($appcode == "vl"){
							$total_leave = ($e_balance->{$carried} + $e_balance->{$appcode}) - ($e_balance->{$appcode_used} + $e_balance->el_used);
						}
						else{
							$total_leave = ($e_balance->{$carried} + $e_balance->{$appcode}) - $e_balance->{$appcode_used};
						}

						$excess = $total_leave - $inexcess_tmp;

						if ($excess > 0 && $e_balance->{$appcode} > 0){
							$new_credits = $e_balance->{$appcode} - $excess;
							$this->db->update('employee_leave_balance', array($appcode => $new_credits), array('leave_balance_id' => $e_balance->leave_balance_id));

							// fix to paid_sl for now because of limited data column.
/*							if ($excess > $e_balance->{$carried}){
								$new_carried = 0;
								$new_credits = $e_balance->{$appcode} - ($excess - $e_balance->{$carried});
								$this->db->update('employee_leave_balance', array($carried => $new_carried,$appcode => $new_credits), array('leave_balance_id' => $e_balance->leave_balance_id));
							}
							else{
								$new_carried = $e_balance->{$carried} - $excess;
								$this->db->update('employee_leave_balance', array($carried => $new_carried), array('leave_balance_id' => $e_balance->leave_balance_id));						
							}*/
						}
					}
				}
			}	
		}

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		
		return $response;
	}

/*	function balfour_pay_all_excess()
	{
		// if today is cutoff for excess
		if($this->config->item('pay_excess_cutoff'))
			$date = date('m-d', strtotime($this->config->item('pay_excess_cutoff')));
		else // default for balfour
			$date = "01-15";

		if(date('m-d') == $date)
		{
			// initialize variables
			$year = date('Y');
			$prev_year = $year - 1;

			// get leave types
			$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_type_leave_setup.application_form_id', 'left');
			$this->db->order_by('employee_type_leave_setup.application_form_id', 'ASC');
			$this->db->where('convertible', 1);
			$leave_types = $this->db->get('employee_type_leave_setup');

			if(!$leave_types && $leave_types->num_rows() == 0)
				return;

			foreach($leave_types->result() as $leave_type)
			{
				if($leave_type->inexcess && $leave_type->inexcess > 0)
				{
					// get data
					$appcode = strtolower($leave_type->application_code);
					$appcode_used = $appcode.'_used';
					$carried = 'carried_'.$appcode;

					// initial query
					$this->db->select(" *,
										(({$carried} + {$appcode}) - {$appcode_used}) AS total_leave
									  	", 
									  	false);

					$this->db->join('employee_leave_balance', 'employee_leave_balance.employee_id = user.employee_id', 'left');
					$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
					$this->db->where('user.deleted', 0);
					$this->db->where('user.employee_id <>', 1);
					$this->db->where('employee.resigned', 0);

					// added query for excess
					$this->db->where('year', $prev_year);
					$this->db->where("(({$carried} + {$appcode}) - {$appcode_used}) > {$leave_type->inexcess}");

					// validate employees to get
					if($leave_type->employee_type_id)
						$this->db->where('employee_type', $leave_type->employee_type_id);

					if($leave_type->employment_status_id)
						$this->db->where('status_id', $leave_type->employment_status_id);

					$e_balance = $this->db->get('user');

					$query = "SELECT 
								*,
								(({$carried} + {$appcode}) - {$appcode_used}) AS total_leave
							  FROM ".$this->db->dbprefix."employee_leave_balance
							  WHERE year = {$prev_year}
							  AND (({$carried} + {$appcode}) - {$appcode_used}) > {$leave_type->inexcess}
							  ";

					// $e_balance = $this->db->query($query);

					if($e_balance && $e_balance->num_rows() == 0)
						continue;

					foreach($e_balance->result() as $balance)
					{
						$paid = $balance->total_leave - $leave_type->inexcess;

						// fix to paid_sl for now because of limited data column.
						$this->db->update('employee_leave_balance', array($appcode => $leave_type->inexcess, 'paid_sl' => $paid), array('leave_balance_id' => $balance->leave_balance_id));

					}

				}
			}

			$this->_balfour_carried($prev_year);

			// for paid bil get all regular employees
			$usersinfo = $this->db->get_where('employee', array('deleted' => 0, 'resigned' => 0, 'status_id' => 1))->result();

			foreach($usersinfo as $userinfo)
			{
				// Check if there's a filed application
				$qry = "select a.*
						FROM {$this->db->dbprefix}employee_leaves a
						WHERE application_form_id = 13 AND form_status_id = 3 AND date_from like '{$prev_year }-%' AND employee_id = {$userinfo->employee_id}";

				$rec = $this->db->query( $qry );

				if($rec && $rec->num_rows() == 0)
					$this->db->update('employee_leave_balance', array('paid_bil' => 1), array('year' => $prev_year, 'employee_id' => $userinfo->employee_id));
			}

		}

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		
		return $response;
	}*/

	function balfour_carried()
	{

		$year = date('Y');
		$prev_year = $year - 1;

		$e_balance = $this->db->get_where('employee_leave_balance', array('year' => $prev_year, 'uneditable' => 0));

		if($e_balance && $e_balance->num_rows() > 0)
		{
			foreach($e_balance->result() as $carried_balance)
			{
				// check if balance exist.
				$bal_exist = $this->db->get_where('employee_leave_balance', array('year' => $year, 'employee_id' => $carried_balance->employee_id));
				
				$total_vl = $carried_balance->carried_vl + $carried_balance->vl - ($carried_balance->vl_used + $carried_balance->el_used + $carried_balance->paid_vl);
				$total_sl = $carried_balance->carried_sl + $carried_balance->sl - ($carried_balance->sl_used + $carried_balance->paid_sl);
				$total_bl = $carried_balance->bl - ($carried_balance->bl_used + $carried_balance->paid_bl);

				$data = array( 'employee_id' => $carried_balance->employee_id,
							   'carried_vl' => $total_vl,
							   'carried_sl' => $total_sl,
							   'carried_bl' => $total_bl,
							   'year' => date('Y')
							   );

				if($bal_exist && $bal_exist->num_rows() > 0) {
					$this->db->update('employee_leave_balance', $data, array('leave_balance_id' => $bal_exist->row()->leave_balance_id));
				} else {
					$this->db->insert('employee_leave_balance', $data);
				}

			}
			
			$this->db->where('year',$year);
			$result = $this->db->get('employee_leave_balance_monitoring_carry_over');
			if ($result && $result->num_rows() > 0){

			}
			else{
				$this->db->insert('employee_leave_balance_monitoring_carry_over',array("year" => $year,"executed" => 1));
			}
		}

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		
		return $response;
	}



	function salary_adjustment()
	{

		$year = date('Y');
		$curr_date = date('Y-m-d');

		$this->db->where('status',0);
		$this->db->where('effectivity_date <= ', $curr_date);
		$batch_salary = $this->db->get('employee_batch_salary');

		if ($batch_salary && $batch_salary->num_rows() > 0) {
			foreach ($batch_salary->result() as $key => $value) {

				if ($value->status == 0) {
					$where = "employee_id IN (".$value->employee_id.")";
					$this->db->where($where);
					$this->db->where('batch_salary_id', $value->batch_salary_id);
					$record = $this->db->get('employee_salary_adjustment');
					
					foreach ($record->result() as $key => $employee) {
						// $salary = $this->encrypt->decode($employee->salary);

						$this->db->where('employee_id', $employee->employee_id);
						$this->db->update('employee_payroll', array('salary' => $employee->salary));
					}

					$this->db->where('batch_salary_id', $value->batch_salary_id);
					$this->db->update('employee_batch_salary', array('status' => 1));
					
				}
			}
		}

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		
		return $response;
	}


	// run every first day of the year.
	function balfour_yearly_accumulation()
	{
		// get leave types
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_type_leave_setup.application_form_id', 'left');
		$this->db->order_by('employee_type_leave_setup.application_form_id', 'ASC');
		$this->db->where('accumulation_type_id', 4);
		$this->db->where('employee_type_leave_setup.deleted', 0);
		$leave_types = $this->db->get('employee_type_leave_setup')->result();
		
		foreach($leave_types as $leave_type)
		{
			// get data  
			$appcode = strtolower($leave_type->application_code);
			$appcode_used = $appcode.'_used';

			// initial query
			$this->db->select("
							  employee.*,
							  user.*,
							  (DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(employed_date)), '%Y') + 0) as tenureship
							  ", false);
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->where('user.deleted', 0);
			$this->db->where('user.employee_id <>', 1);
			$this->db->where('employee.resigned', 0);
			$this->db->where('user.inactive', 0);

			// validate employees to get
			if($leave_type->employee_type_id)
				$this->db->where('employee_type', $leave_type->employee_type_id);

			if($leave_type->employment_status_id)
				$this->db->where_in('status_id', explode(',',$leave_type->employment_status_id));

			if($leave_type->position_id != 0)
				$this->db->where_in('position_id', $leave_type->position_id);

			// if($this->config->item('use_cbe_cba'))
			// {
			// 	$this->db->where('CBE', $leave_type->CBE);
			// 	$this->db->where('ECF', $leave_type->CBA);
			// }

			$users = $this->db->get('user');

			if(!$users && $users->num_rows() == 0)
				continue;

			// insert accumulation on all employee's satisfied
			foreach($users->result() as $user) 
			{
				// get data
				//to override if exist in leave setup exemption - tirso
				$exemption_array = $this->system->get_employe_leave_setup_exemption($user->employee_id,$leave_type->leave_setup_id);

				$base_tmp = ($exemption_array['base'] !== 0 ? $exemption_array['base'] : $leave_type->base);
				//

				$tenure = ($user->tenureship == 0 ? 1 : $user->tenureship);
				$base_accumulation = $base_tmp;

				$this->db->order_by('tenure', 'DESC');
				$etlc = $this->db->get_where('employee_type_leave_credit', array('leave_setup_id' => $leave_type->leave_setup_id, 'leave_type' => $leave_type->application_form_id, 'tenure <=' => $tenure));

				if($etlc && $etlc->num_rows() > 0)
					$base_accumulation = $etlc->row()->leave_accumulated;

				$new_value = $base_accumulation;

				$e_balance = $this->db->get_where('employee_leave_balance', array('year' => date('Y'), 'employee_id' => $user->user_id, 'deleted' => 0));

				if($e_balance->num_rows() > 0) {

					$balance = $e_balance->row();

					$this->db->set($appcode, $new_value, false);
					$this->db->where('leave_balance_id', $balance->leave_balance_id);
					$this->db->update('employee_leave_balance');

				} else {

					$data = array(
						'year' => date('Y'),
						'employee_id' => $user->user_id,
						$appcode => $new_value,
						'deleted' => 0
					);

					$this->db->insert('employee_leave_balance', $data);

				}

			}
		}
		
		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		
		return $response;		
	}	

	/**
	 * Run every first day of month to send with the hr.
	 * 
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m send_near_end_date
	 * This will get all employees neard end date with current month from first day up next month end day
	 */
	function send_near_end_date($param){
		$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
        	$cdate = date('Y-m-d');           
	    	$date_from = date('Y-m-01');
	    	$date_to = date('Y-m-t',strtotime ('+1 month',strtotime($date_from)));

	    	$qry = "SELECT * FROM {$this->db->dbprefix}user u
	    			INNER JOIN {$this->db->dbprefix}employee e
	                	ON u.employee_id = e.employee_id 
	                LEFT JOIN {$this->db->dbprefix}user_position pos
	                	ON u.position_id = pos.position_id
	                LEFT JOIN {$this->db->dbprefix}employment_status es
	                	ON e.status_id = es.employment_status_id	
	                LEFT JOIN  {$this->db->dbprefix}employee_payroll ep
	                	ON e.employee_id = ep.employee_id
	    			WHERE e.end_date between '".$date_from."' and '".$date_to."' 
	    				AND (e.resigned_date > '".$date_from."' OR e.resigned = 0)
	    				AND u.deleted = 0";
	    			
	        $result = $this->db->query($qry);
	        
	        $html = '<table border="1" style="border-collapse:collapse;width:100%" cellpadding="5">
                        <tr>
                        	<td align="center">Employee ID</td>
                            <td align="center">Last <br /> Name</td>
                            <td align="center">First <br /> Name</td>
                            <td align="center">Position</td>
                            <td align="center">Work <br /> Assignment</td>
                            <td align="center">Employment <br /> Status</td>
                            <td align="center">Salary</td>
                            <td align="center" style="width:127px">Contract Start <br /> Date</td>
                            <td align="center" style="width:127px">Contract End <br /> Date</td>
                            <td align="center"  style="width:127px">No. of Days to go</td>
                        </tr>';
	                    if ($result && $result->num_rows() > 0){
	                        foreach ($result->result() as $row) {
	                        	$secs = strtotime($row->end_date) -  strtotime($cdate);
	                        	$days = $secs / 86400;
	                        	$project = '';

								// $assignment = $this->db->get_where('employee_work_assignment', array('employee_id' => $row->user_id, 'assignment' => 1))->row();
								$assignment = $this->db->get_where('user', array('employee_id' => $row->user_id))->row();
								
								//commented since employee_work_assignment_category_id is not being used and only project_name_id is used to reference employee project	
								// switch ($assignment->employee_work_assignment_category_id) {
								// 	case 1:
								if($assignment->division_id > 0){
										$result = $this->db->get_where('user_company_division',array('division_id' => $assignment->division_id));
										if ($result && $result->num_rows() > 0){
											$project = $result->row()->division;
										}
									}
								// 		break;										
								// 	case 2:
								if($assignment->project_name_id > 0){
										$result = $this->db->get_where('project_name',array('project_name_id' => $assignment->project_name_id));
										if ($result && $result->num_rows() > 0){
											$project = $result->row()->project_name;
										}
									}
										// break;
								// 	case 3:
								// 		$result = $this->db->get_where('group_name',array('group_name_id' => $assignment->group_name_id));
								// 		if ($result && $result->num_rows() > 0){
								// 			$project = $result->row()->group_name;
								// 		}
								// 		break;										
								// 	case 4:
								if($assignment->department_id > 0){
										$result = $this->db->get_where('user_company_department',array('department_id' => $assignment->department_id));
										if ($result && $result->num_rows() > 0){
											$project = $result->row()->department;
										}
									}
								// 		break;
								// }

	                            $html .= '<tr>
	                            			<td>'.$row->id_number.'</td>
	                                        <td>'.$row->lastname.'</td>
	                                        <td>'.$row->firstname.'</td>
	                                        <td>'.$row->position.'</td>
	                                        <td>'.$project.'</td>
	                                        <td>'.$row->employment_status.'</td>
	                                        <td>'.$this->encrypt->decode($row->salary).'</td>
	                                        <td style="width:127px">'.date($this->config->item("display_date_format"),strtotime($row->employed_date)).'</td>
	                                        <td style="width:127px">'.date($this->config->item("display_date_format"),strtotime($row->end_date)).'</td>
	                                        <td style="width:127px">'.$days.' days to go</td>
	                                    </tr>';
	                        }
	                    }

	        $html .= '</table>';        

	     
        	// Load the template. 
			$this->load->model('template');

			if( $param['email_cc'] ){
				$email_cc = $param['email_cc'];
			}

			if( $param['email_bcc'] ){
				$email_bcc = $param['email_bcc'];
			}

	        $template = $this->template->get_module_template(0, 'list_employee_with_end_date');

			$recepients[] = $param['email_to'];

	        $request['month_from'] = date($this->config->item("display_date_format"),strtotime($date_from));
	        $request['month_to'] = date($this->config->item("display_date_format"),strtotime($date_to));	                
	        $request['employees'] = $html;

	        $message = $this->template->prep_message($template['body'], $request);
	        $this->template->queue_with_bcc(implode(',', $recepients), $email_cc, $email_bcc, $template['subject'], $message);
	        //$this->template->queue(implode(',', $recepients), '', $template['subject'], $message);

			$response->msg_type = "success";
			$response->msg = "Task is successfully executed";		
			
			return $response;		        
	    }   		
	}

	function graceperiod_notification()
	{
		// setting config
		$config = $this->hdicore->_get_config('habitual_tardiness_configuration');

		$instances = $config['instances'];

		$minutes_tardy = $config['minutes_tardy'];

		$months_within = $config['months_within'];

		$sql = "SELECT *
				FROM {$this->db->dbprefix}employee_dtr
				WHERE date LIKE '%".date('Y-m-')."%'
					AND employee_id <> ''
					AND employee_id IS NOT NULL
					AND gp_flag = 0
					AND lates_display > 0 				
					ORDER BY employee_id ASC, date ASC
				";

		$result = $this->db->query($sql);

		if($result && $result->num_rows() > 0)
		{

			$tardy_employees = $result->result();

			foreach($tardy_employees AS $tardy_employee)
			{
				$this->db->select(' tardy_employee_id, employee_id, employee_dtr_id,first_occurence, minutes_tardy, number_occured, DATE_ADD(first_occurence, INTERVAL '.$months_within.' MONTH) AS tardiness_within ', false);
				$this->db->where("'".$tardy_employee->date."' BETWEEN first_occurence AND DATE_ADD(first_occurence, INTERVAL 1 MONTH)
								 AND employee_id = ".$tardy_employee->employee_id."
								 AND minutes_tardy < ".$minutes_tardy."
								 AND number_occured < ".$instances, ''
								 ,false );
				$this->db->order_by('first_occurence', 'DESC');
				$tardy_before = $this->db->get('graceperiod_employee'); 

				// when employee aleady commited an ht within 1 month.
				if($tardy_before && $tardy_before->num_rows() > 0) {
					$latest_tardy = $tardy_before->row();
					$total_min_tardy = $latest_tardy->minutes_tardy + $tardy_employee->lates_display;
					$total_occured_tardy = $latest_tardy->number_occured + 1;
					$employee_dtr_id = $latest_tardy->employee_dtr_id .','. $tardy_employee->id;

					$data = array( 'minutes_tardy' => $total_min_tardy,
								   'number_occured' => $total_occured_tardy,
								   'employee_dtr_id' => $employee_dtr_id
								);

					$this->db->update('graceperiod_employee', $data, array('tardy_employee_id' => $latest_tardy->tardy_employee_id));

					// if occured is more than config.
					if($total_min_tardy >= $minutes_tardy || $total_occured_tardy >= $instances) {
						// create notification here
						$this->_create_notification($tardy_employee->employee_id,$employee_dtr_id);
					}

					// change gp_flag to true, so it will not be counted next time
					$this->db->update('employee_dtr', array('gp_flag' => 1), array('id' => $tardy_employee->id));
				} else { // when tardiness is commited for the first time, or employee's previous tardiness is reset
					if($tardy_employee->lates >= $minutes_tardy) {
						// create notification here						
						$this->_create_notification($tardy_employee->employee_id,$tardy_employee->id);

						// change gp_flag to true, so it will not be counted next time
						$this->db->update('employee_dtr', array('gp_flag' => 1), array('id' => $tardy_employee->id));

					} else {
						$data = array( 'employee_id' => $tardy_employee->employee_id,
									   'first_occurence' => $tardy_employee->date,
									   'minutes_tardy' => $tardy_employee->lates_display,
									   'number_occured' => 1,
									   'employee_dtr_id' => $tardy_employee->id
									   );

						$this->db->insert('graceperiod_employee', $data);
						// change gp_flag to true, so it will not be counted next time
						$this->db->update('employee_dtr', array('gp_flag' => 1), array('id' => $tardy_employee->id));

					}
				}

			} // end foreach
		} // end if

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		
		return $response;			

	}

	private function _create_notification($employee_id = false,$employee_dtr_id = false)
	{
		if($employee_id)
		{	
			$infraction = array();
			if ( $employee_dtr_id){
				$dtr_id_array = explode(',', $employee_dtr_id);
				$this->db->where_in('id',$dtr_id_array);
				$this->db->order_by('date', 'ASC');
				$result = $this->db->get('employee_dtr');

				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						$infraction['date'][] = $row->date;
						$infraction['time'][] = $row->time_in1;
						$infraction['min_lates'][] = round($row->lates_display, 0);;

						$work_schedule = $this->system->get_employee_worksched($employee_id,$row->date,false);
						$schedule = '';

						if ($work_schedule){
							$work_schedule_array = explode('-',  $work_schedule->shift);
							$shift_start = $work_schedule_array[0];
							$shift_end = $work_schedule_array[1];
							$schedule = date('ga',strtotime($shift_start)) .'-'. date('ga',strtotime($shift_end));
						}

						$infraction['shift'][] = $work_schedule->shift;
					}
				}
				$this->_send_email_graceperiod_notification($employee_id,$infraction);
			}
		} else {
			return false;
		}

	}

	/**
     * Send the email to employees
     */
    protected function _send_email_graceperiod_notification($employee_id = false, $infraction = false) {
    	if (!$employee_id){
    		return false;
    	}


    	$recipients = array();
    	$result = $this->db->get_where('user',array('user_id'=>$employee_id));
    	if ($result && $result->num_rows() > 0){
    		$single_row = $result->row();
    		$request = $result->row_array();

    		if ($single_row->email != ''){
				$recipients[] = $single_row->email;
    		}

    		$immediate_superior = $this->system->get_reporting_to($employee_id);

    		if ($immediate_superior){
    			$result = $this->db->get_where('user',array('user_id'=>$immediate_superior));
		    	if ($result && $result->num_rows() > 0){
		    		$single_row = $result->row();
		    		if ($single_row->email != ''){
						$recipients[] = $single_row->email;
		    		}    			
		    	}
    		}
    	}

        $request['start_date_tardy'] = date('M.Y',strtotime($infraction['date'][0]));
        $request['count_tardy'] = count($infraction['date']);
        
		$list_tardy_html="<table border='0' cellpadding='0' cellspacing='0' style='width: 600px; border:1px solid gray '>
					<tr>
						<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
							<span style='font-family: sans-serif,arial; font-size: 13px; '>Date</span>
						</td>
						<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
							<span style='font-family: sans-serif,arial; font-size: 13px; '>Shift</span>
						</td>
						<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
							<span style='font-family: sans-serif,arial; font-size: 13px; '>Time-in</span>
						</td>
						<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
							<span style='font-family: sans-serif,arial; font-size: 13px; '>Minutes Late</span>
						</td>
					</tr>
		";        
        foreach ($infraction['date'] as $key => $value) {
			$list_tardy_html.="
				<tr>
					<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
						<span style='font-family: sans-serif,arial; font-size: 13px; '>".date('M.d',strtotime($value))."</span>
					</td>
					<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
						<span style='font-family: sans-serif,arial; font-size: 13px; '>".$infraction['shift'][$key]."</span>
					</td>
					<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
						<span style='font-family: sans-serif,arial; font-size: 13px; '>".date('h:i:s A',strtotime($infraction['time'][$key]))."</span>
					</td>
					<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
						<span style='font-family: sans-serif,arial; font-size: 13px; '>".$infraction['min_lates'][$key]."</span>
					</td>
				</tr>
			";   
        }
        $list_tardy_html .= '</table>';

        $request['list_tardy'] = $list_tardy_html;

        $emailto_list = array();
		$emailcc_list = array();

		foreach( $dtr_notification_settings['email_to'] as $email_to_settings ){
			if ($email_to_settings['email'] != ''){
				$recipients[] = $email_to_settings['email'];
			}
		}

		foreach( $dtr_notification_settings['email_cc'] as $email_cc_settings ){
			if ($email_cc_settings['email'] != ''){
				$emailcc_list[] = $email_cc_settings['email'];
			}
		}

		$email_cc_recipient = implode(', ',$emailcc_list);
    	
        $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
            // Load the template.            
            $this->load->model('template');
            $template = $this->template->get_module_template(184, 'habitual_tardiness');
            $message = $this->template->prep_message($template['body'], $request);
            $this->template->queue(implode(',', $recipients), $email_cc_recipient, 'Grace Period Notification', $message);
        }
    }

    function habitual_tardiness()
	{
		// setting config
		$config = $this->hdicore->_get_config('habitual_tardiness_configuration');

		$instances = $config['instances'];

		$minutes_tardy = $config['minutes_tardy'];

		$months_within = $config['months_within'];
		$date = date('Y-m-d');
        $date_now = date('Y-m-',strtotime('-1 month',strtotime($date)));
		$sql = "SELECT *
				FROM {$this->db->dbprefix}employee_dtr
				WHERE date LIKE '%".$date_now."%'
					AND employee_id <> ''
					AND employee_id IS NOT NULL
					AND ht_flag = 0
					AND lates_display > 0 				
					ORDER BY employee_id ASC, date ASC
				";

		$result = $this->db->query($sql);

		if($result && $result->num_rows() > 0)
		{

			$tardy_employees = $result->result();

			foreach($tardy_employees AS $tardy_employee)
			{
				// if with NCNS do not count
				$with_ncns = $this->db->get_where('employee_no_call_show', array('employee_id' => $tardy_employee->employee_id, 'date' => $tardy_employee->date, 'deleted' => 0));

				if($with_ncns->num_rows() == 0)
				{
					$this->db->select(' tardy_employee_id, employee_id, employee_dtr_id,first_occurence, minutes_tardy, number_occured, DATE_ADD(first_occurence, INTERVAL '.$months_within.' MONTH) AS tardiness_within ', false);
					$this->db->where("'".$tardy_employee->date."' BETWEEN first_occurence AND DATE_ADD(first_occurence, INTERVAL 1 MONTH)
									 AND employee_id = ".$tardy_employee->employee_id."
									 AND minutes_tardy < ".$minutes_tardy."
									 AND number_occured < ".$instances, ''
									 ,false );
					$this->db->order_by('first_occurence', 'DESC');
					$tardy_before = $this->db->get('tardy_employee');

					// when employee aleady commited an ht within 1 month.
					if($tardy_before && $tardy_before->num_rows() > 0)
					{
						$latest_tardy = $tardy_before->row();
						$total_min_tardy = $latest_tardy->minutes_tardy + $tardy_employee->lates_display;
						$total_occured_tardy = $latest_tardy->number_occured + 1;
						$employee_dtr_id = $latest_tardy->employee_dtr_id .','. $tardy_employee->id;

						$data = array( 'minutes_tardy' => $total_min_tardy,
									   'number_occured' => $total_occured_tardy,
									   'employee_dtr_id' => $employee_dtr_id
									);

						$this->db->update('tardy_employee', $data, array('tardy_employee_id' => $latest_tardy->tardy_employee_id));

						// if occured is more than config.
						if($total_min_tardy >= $minutes_tardy || $total_occured_tardy >= $instances) {
							// create ir here
							$this->_create_ir($tardy_employee->employee_id,115,6,1,$this->userinfo['user_id'],"Habitual Tardiness",$employee_dtr_id);
						}

						// change ht_flag to true, so it will not be counted next time
						$this->db->update('employee_dtr', array('ht_flag' => 1), array('id' => $tardy_employee->id));
					} else { // when tardiness is commited for the first time, or employee's previous tardiness is reset
						if($tardy_employee->lates >= $minutes_tardy) {
							// create ir here, no need to save on tardy_employee because employee is already on ir.
/*							$data = array( 'employee_id' => $tardy_employee->employee_id,
										   'first_occurence' => $tardy_employee->date,
										   'minutes_tardy' => $tardy_employee->lates,
										   'number_occured' => 1,
										   'employee_dtr_id' => $tardy_employee->id
										   );

							$this->db->insert('tardy_employee', $data);
							$tardy_employee_id = $this->db->insert_id;*/

							$this->_create_ir($tardy_employee->employee_id,115,6,1,$this->userinfo['user_id'],"Habitual Tardiness",$tardy_employee->id);

							// change ht_flag to true, so it will not be counted next time
							$this->db->update('employee_dtr', array('ht_flag' => 1), array('id' => $tardy_employee->id));

						} else {

							$data = array( 'employee_id' => $tardy_employee->employee_id,
										   'first_occurence' => $tardy_employee->date,
										   'minutes_tardy' => $tardy_employee->lates_display,
										   'number_occured' => 1,
										   'employee_dtr_id' => $tardy_employee->id
										   );

							$this->db->insert('tardy_employee', $data);
							// change ht_flag to true, so it will not be counted next time
							$this->db->update('employee_dtr', array('ht_flag' => 1), array('id' => $tardy_employee->id));

						}

					}

				} // endif of with ncns

			} // end foreach
		} // end if

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		
		return $response;			

	} // end function	

	

	// default for haibitual tardiness
	private function _create_ir($employee_id = false, $module_id = 115, $ir_status_id = 6, $offence_id = 1, $complainants = array(6), $details = "Habitual Tardiness",$employee_dtr_id = false)
	{
		if($employee_id)
		{
			$approvers = $this->system->get_approvers_and_condition($employee_id, $module_id);

	        //$approvers = $this->system->get_approvers($this->userinfo['position_id'], $this->module_id);
	        $approver_array = array();

	        foreach($approvers as $approver){
	            array_push($approver_array, $approver['approver']);
	        }

			$approver_list = implode(',', $approver_array);

			$data = array(
				'ir_status_id' => $ir_status_id,
				'offence_id' => $offence_id,
				'complainants' => implode(',',$complainants),
				'involved_employees' => $employee_id,
				'details' => $details,
				'date_sent' => date('Y-m-d h:i:s'),
				'offense_datetime' => date('Y-m-d h:i:s'),
				'approvers' => $approver_list
			);

			$this->db->insert('employee_ir',$data);
			$ir_id = $this->db->insert_id();

			$complainant = array('ir_id' => $ir_id, 'employee_id' => 2);
			$this->db->insert('employee_ir_complainant', $complainant);
			
			$involved = array('ir_id' => $ir_id, 'employee_id' => $employee_id);
			$this->db->insert('employee_ir_involved', $involved);

			$infraction = array();
			if ( $employee_dtr_id){
				$dtr_id_array = explode(',', $employee_dtr_id);
				$this->db->where_in('id',$dtr_id_array);
				$this->db->order_by('date', 'ASC');
				$result = $this->db->get('employee_dtr');

				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						$infraction['date'][] = $row->date;
						$infraction['time'][] = $row->time_in1;
						$infraction['min_lates'][] = round($row->lates_display, 0);;

						$work_schedule = $this->system->get_employee_worksched($employee_id,$row->date,false);
						$schedule = '';

						if ($work_schedule){
							$work_schedule_array = explode('-',  $work_schedule->shift);
							$shift_start = $work_schedule_array[0];
							$shift_end = $work_schedule_array[1];
							$schedule = date('ga',strtotime($shift_start)) .'-'. date('ga',strtotime($shift_end));
						}

						$infraction['shift'][] = $work_schedule->shift;
					}
				}

				//$this->_send_email_habitual_tardiness($employee_id,$infraction);
			}
		} else
			return false;

	}	

    /**
     * Send the email to employees,immediate superior and hr.
     */
    protected function _send_email_habitual_tardiness($employee_id = false, $infraction = false) {
    	if (!$employee_id){
    		return false;
    	}

        // $request['date'] = date($this->config->item('display_date_format'), strtotime($date_email));

    	$recipients = array();
    	$result = $this->db->get_where('user',array('user_id'=>$employee_id));
    	if ($result && $result->num_rows() > 0){
    		$single_row = $result->row();
    		$request = $result->row_array();

    		if ($single_row->email != ''){
				$recipients[] = $single_row->email;
    		}

    		$immediate_superior = $this->system->get_reporting_to($employee_id);

    		if ($immediate_superior){
    			$result = $this->db->get_where('user',array('user_id'=>$immediate_superior));
		    	if ($result && $result->num_rows() > 0){
		    		$single_row = $result->row();
		    		if ($single_row->email != ''){
						$recipients[] = $single_row->email;
		    		}    			
		    	}
    		}
    	}

        $request['start_date_tardy'] = date('M.Y',strtotime($infraction['date'][0]));
        $request['count_tardy'] = count($infraction['date']);
        
		$list_tardy_html="<table border='0' cellpadding='0' cellspacing='0' style='width: 600px; border:1px solid gray '>
					<tr>
						<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
							<span style='font-family: sans-serif,arial; font-size: 13px; '>Date</span>
						</td>
						<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
							<span style='font-family: sans-serif,arial; font-size: 13px; '>Shift</span>
						</td>
						<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
							<span style='font-family: sans-serif,arial; font-size: 13px; '>Time-in</span>
						</td>
						<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
							<span style='font-family: sans-serif,arial; font-size: 13px; '>Minutes Late</span>
						</td>
					</tr>
		";        
        foreach ($infraction['date'] as $key => $value) {
			$list_tardy_html.="
				<tr>
					<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
						<span style='font-family: sans-serif,arial; font-size: 13px; '>".date('M.d',strtotime($value))."</span>
					</td>
					<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
						<span style='font-family: sans-serif,arial; font-size: 13px; '>".$infraction['shift'][$key]."</span>
					</td>
					<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
						<span style='font-family: sans-serif,arial; font-size: 13px; '>".date('h:i:s A',strtotime($infraction['time'][$key]))."</span>
					</td>
					<td align='center' scope='col' style='width:25%;border-bottom:1px solid gray;border-right:1px solid gray'>
						<span style='font-family: sans-serif,arial; font-size: 13px; '>".$infraction['min_lates'][$key]."</span>
					</td>
				</tr>
			";   
        }
        $list_tardy_html .= '</table>';

        $request['list_tardy'] = $list_tardy_html;

    	$dtr_notification_settings = $this->hdicore->_get_config('dtr_notification_settings');

		$emailto_list = array();
		$emailcc_list = array();

		foreach( $dtr_notification_settings['email_to'] as $email_to_settings ){
			if ($email_to_settings['email'] != ''){
				$recipients[] = $email_to_settings['email'];
			}
		}

		foreach( $dtr_notification_settings['email_cc'] as $email_cc_settings ){
			if ($email_cc_settings['email'] != ''){
				$emailcc_list[] = $email_cc_settings['email'];
			}
		}

		$email_cc_recipient = implode(', ',$emailcc_list);

        $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
            // Load the template.            
            $this->load->model('template');
            $template = $this->template->get_module_template(184, 'habitual_tardiness');
            $message = $this->template->prep_message($template['body'], $request);
            $this->template->queue(implode(',', $recipients), $email_cc_recipient, $template['subject'], $message);
        }
    }

    // Initialy it will run on dec 15 every year to give credits of all employees for next year.
    // As for ticket no 719, it will run on 1st day of january for current year.

    function birthday_leave_earning(){

    	$cur_year = date('Y');

    	$date = date('Y-01-01');
    	$this->db->where('user.deleted',0);
    	$this->db->where('status_id',1);
    	$this->db->where('resigned_date',NULL);
    	$this->db->join('employee', 'employee.employee_id = user.user_id');
    	$user = $this->db->get('user');
    	
    	if ($user && $user->num_rows() > 0){
    		foreach ($user->result() as $row) {

    			$e_balance = $this->db->get_where('employee_leave_balance', array('year' => $cur_year, 'employee_id' => $row->user_id, 'deleted' => 0));

				if($e_balance->num_rows() > 0) {
					$balance = $e_balance->row();
					$this->db->set('bl', 1);
					$this->db->where('leave_balance_id', $balance->leave_balance_id);
					$this->db->update('employee_leave_balance');
				} else {
					$data = array(
						'year' => $cur_year,
						'employee_id' => $row->user_id,
						'bl' => 1,
						'deleted' => 0
					);

					$this->db->insert('employee_leave_balance', $data);
				}
    		}

			$response->msg_type = "success";
			$response->msg = "Task is successfully executed";		
			
			return $response;	    		
    	}
    }	

    function el_earning(){
    	$cur_year = date('Y');

    	$date = date('Y-01-01');
    	$this->db->where('user.deleted',0);
    	$this->db->where('user.inactive',0);
    	$this->db->where('user.employee_id <>', 1);
		$this->db->where('employee.resigned', 0);
    	$this->db->join('employee', 'employee.employee_id = user.user_id');
    	$user = $this->db->get('user');
    	
    	if ($user && $user->num_rows() > 0){
    		foreach ($user->result() as $row) {

    			$e_balance = $this->db->get_where('employee_leave_balance', array('year' => $cur_year, 'employee_id' => $row->user_id, 'deleted' => 0));

    			$this->db->where('application_form_id', 3);
    			$this->db->where('employee_type_id', $row->employee_type);
    			$this->db->where('employment_status_id', $row->status_id);
    			$this->db->where('deleted', 0);
    			$leave_type = $this->db->get('employee_type_leave_setup');
    			
    			$el_base = 0;
    			
    			if ($leave_type && $leave_type->num_rows() > 0) {
    				$leave_type = $leave_type->row();
    				$el_base = $leave_type->base;
    			}

				if($e_balance->num_rows() > 0) {
					$balance = $e_balance->row();
					$this->db->set('el', $el_base);
					$this->db->where('leave_balance_id', $balance->leave_balance_id);
					$this->db->update('employee_leave_balance');
				} else {
					$data = array(
						'year' => $cur_year,
						'employee_id' => $row->user_id,
						'el' => $el_base,
						'deleted' => 0
					);

					$this->db->insert('employee_leave_balance', $data);
				}
    		}

			$response->msg_type = "success";
			$response->msg = "Task is successfully executed";		
			
			return $response;	    		
    	}
    }	

	function leave_reset_date(){

    	$next_year = date('Y') + 1;
    	$cur_year = date('Y');

    	$not_null = 'leave_reset_date IS NOT NULL';
    	$this->db->where('leave_reset_date !=', '0000-00-00');
    	$this->db->where($not_null);
    	$leave_setup = $this->db->get('employee_type_leave_setup');

    	if ($leave_setup && $leave_setup->num_rows() > 0){
    		foreach ($leave_setup->result() as $setup) {
    			$curr_setup_md = date('m-d', strtotime($setup->leave_reset_date));
    			$next_reset_date = $next_year.'-'.$curr_setup_md;

    			$this->db->set('leave_reset_date', $next_reset_date);
    			$this->db->where('leave_setup_id', $setup->leave_setup_id);
    			$this->db->update('employee_type_leave_setup');

    		}

    		$response->msg_type = "success";
			$response->msg = "Task is successfully executed";		
			
			return $response;	   
    	}
	}	

	public function awol_notifier() {
		$this->_awol_notifier();

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		return $response;

	}

	public function leave_balance_notif()
	{
		$this->load->model('template');

		$year = date('Y');

		$this->db->where('(vl - vl_used) >=', 25);
		$this->db->where('employee_leave_balance.year', $year);
		$this->db->where('employee_leave_balance.deleted', 0);
		$this->db->join('user', 'user.employee_id = employee_leave_balance.employee_id');
		$leave_balance = $this->db->get('employee_leave_balance');
	
		if ($leave_balance && $leave_balance->num_rows() > 0) {
			
			$template = $this->template->get_module_template(0, 'leave_balance_notif');
			
			foreach ($leave_balance->result_array() as $request) {
				$request['employee'] = $request['firstname']." ".$request['middleinitial']." ".$request['lastname'];
		        $message = $this->template->prep_message($template['body'], $request);
		        $this->template->queue($request['email'], '', $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
			}
			
		}

        $response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		return $response;
	}

	private function _awol_notifier()
	{	
		
		$awol_employees = array();
		
		$wout_awol = implode(',', $this->config->item('emp_type_no_late_ut'));

		// $where = "employee_dtr.date LIKE '%".date('Y-m-')."%'";

		if (date('d') <= 15) {
			$where = "employee_dtr.date <= '".date('Y-m-15')."'";
		}else{
			$where = "employee_dtr.date > '".date('Y-m-15')."'";
		}

		$where .= " AND e.employee_type NOT IN (".$wout_awol.") AND (time_in1 IS NULL AND time_out1 IS NULL)";

		$this->db->select('CONCAT(u.firstname, " ", u.lastname) name, employee_dtr.id, employee_dtr.employee_id, u.email, employee_dtr.date', false);
		// $this->db->where('send_awol_notification', 1);
		// $this->db->where('awol', 1);
		$this->db->where('awol_notification_sent', 0);
		$this->db->where('employee_dtr.deleted', 0);
		$this->db->where('inactive', 0);
		$this->db->where("employee_dtr.date LIKE '%".date('Y-m-')."%'");
		$this->db->where($where);
		// $this->db->where('employee_dtr.employee_id', 187);
		$this->db->from('employee_dtr');
		$this->db->join('user u', 'employee_dtr.employee_id = u.user_id', 'left');
		$this->db->join('employee e', 'employee_dtr.employee_id = e.employee_id', 'left');

		$emp_wo_in = $this->db->get();

		if ($emp_wo_in->num_rows() == 0) {		

			return;

		} else {			
			$dates = array();
			$emp_wo_in = $emp_wo_in->result();
			$details = array();
			foreach ($emp_wo_in as $employee) {

				$exempt_with_ncns = false;

				if($this->config->item('exempt_ncns') == 1)
				{
					$with_ncns = $this->db->get_where('employee_no_call_show', array('employee_id' => $employee->employee_id, 'date' => $employee->date, 'deleted' => 0));
					if($with_ncns->num_rows() > 0)
						$exempt_with_ncns = true;
				}

				if(!$exempt_with_ncns)
				{
					
					$sched = $this->system->get_employee_worksched($employee->employee_id, $employee->date, true);
					$holiday = $this->system->holiday_check($employee->date, $employee->employee_id);
										
					if ($sched->shift_id == 1 || $holiday) {
						
					}else{
						
						// Check leave for whole day
						$this->db->select('duration_id, employee_leaves.employee_leave_id, employee_leaves.form_status_id,employee_leaves.blanket_id');
		                $this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
		                $this->db->where('employee_id', $employee->employee_id);
		                $this->db->where('(\''. $employee->date . '\' BETWEEN date_from and date_to)', '', false);
		                $this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $employee->date . '\')', '',false);
		                // $this->db->where('(form_status_id = 3 OR form_status_id = 2 OR form_status_id = 4)');
		                $this->db->where('form_status_id', 3);
		                $this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
		                $this->db->where('employee_leaves.deleted', 0);

		                $leave = $this->db->get('employee_leaves');
		                
		                if ($leave && $leave->num_rows > 0) {
		                	
		                }else{
		                	$emp_names[] = $employee->name;
							$dtr_id[$employee->employee_id][] = $employee->id;
							$user_id[] = $employee->employee_id;
							$dates[$employee->employee_id][] = $employee->date;
							$recipients[$employee->employee_id] = $employee->email;
							$details[$employee->employee_id][] = $employee->date.', '.$sched->shift;
						}


		            }
						
					// $dates['date'][] = $employee->date;
					
		    	} 

			}

				if (count($emp_names) > 0) {	
					
					$cnt_emp = $user_id;
					// $cnt_emp = array_count_values($user_id);

					foreach ($dates as $employee_id => $date) {
						$approvers = $this->system->get_approvers_and_condition($employee_id,115);
						$approver_array = array();
						
						foreach($approvers as $approver){
			                array_push($approver_array, $approver['approver']);
			            }
			            $approver_list = implode(',', $approver_array);

			            foreach ($date as $d) {
			            	
			            	$data = array(
								'ir_status_id' => 6,
								'offence_id' => 79,
								'complainants' => 2,
								'involved_employees' => $employee_id,
								'details' => 'Absence without leave.',
								'date_sent' => date('Y-m-d h:i:s'),
								'offense_datetime' => date('Y-m-d', strtotime($d)),
								'approvers' => $approver_list
							);

			            // create IR
							$this->db->insert('employee_ir',$data);
							$ir_id = $this->db->insert_id();

							$complainant = array('ir_id' => $ir_id, 'employee_id' => 2);
							$this->db->insert('employee_ir_complainant', $complainant);
							
							$involved = array('ir_id' => $ir_id, 'employee_id' => $employee_id);
							$this->db->insert('employee_ir_involved', $involved);
			        	}

			            $this->_send_email_offender($employee_id, 'awol_notification', $details[$employee_id]);

				       	$dtr_ids = implode(',', $dtr_id[$employee_id]);
						$where = "id IN (".$dtr_ids.")";
						$this->db->where($where);
						$this->db->update('employee_dtr', array('awol_notification_sent' => true));
					}


					// $response->msg_type = "success";
					// $response->msg = "Task is successfully executed";		
					// return $response;	
				
				}
		}
		

	}

	/*
	 * send email on offender
	 *
	 */
	function _send_email_offender($offender_id, $template, $dates)
	{

		$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
        	// Load the template.            
            $this->load->model('template');
            $template = $this->template->get_module_template(0, $template);

            $offender = $this->db->get_where('user', array('employee_id' => $offender_id))->row();
            // foreach($offenders as $offender)

            $recipients = $offender->email;
            $request['employee'] = $offender->salutation.' '.$offender->firstname.' '.$offender->lastname.' '.$offender->aux ;

            $request['date_incurred'] = date('M. Y');
          
            $request['incurred'] = "<table style='width=100%'>";
            $request['incurred'] .= "<tr><th>Date</th><th>Shift</th></tr>";
             foreach ($dates as $date) {
           		$shift_date = explode(',', $date);
           		$request['incurred'] .= "<tr>";
           		$request['incurred'] .= "<td style='width:95px;'> " . date('M d, Y', strtotime($shift_date[0])) . " </td>";
           		$request['incurred'] .= "<td style='width:95px;'> ".$shift_date[1]." </td>";
            	$request['incurred'] .= "</tr>";
           }
           $request['incurred'] .= "</table>";

            $message = $this->template->prep_message($template['body'], $request, true, true);                                                                      
            $this->template->queue($recipients, $email_cc_recipient, $template['subject'], $message);

        }     
	}

	function rfp_date_recieve(){
		$date_to_select = date('Y-m-d',strtotime(date('Y-m-d') . '-3 days'));	
		$this->db->where('DATE(created_date)',$date_to_select);
		$this->db->where('contract_received','');
		$this->db->or_where('contract_received',null);
		$this->db->update('recruitment_manpower',array('contract_received' => date('Y-m-d')));

		$date_to_select = date('Y-m-d',strtotime(date('Y-m-d') . '-3 days'));	
		$this->db->where('DATE(created_date)',$date_to_select);
		$this->db->where('approved_date','0000-00-00');
		$this->db->where('status','For HR Review');
		$this->db->update('recruitment_manpower',array('approved_date' => date('Y-m-d')));

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
		return $response;			
	}

    // Initialy it will run on dec 15 every year to give credits of all employees for next year.
/*    function birthday_leave_earning(){
    	$this->db->where('deleted',0);
    	$user = $this->db->get('user');

    	$next_year = date('Y') + 1;
    	$cur_year = date('Y');

    	if ($user && $user->num_rows() > 0){
    		foreach ($user->result() as $row) {
    			$bl_carried = 0;

    			$e_prev_balance = $this->db->get_where('employee_leave_balance', array('year' => $cur_year, 'employee_id' => $row->user_id, 'deleted' => 0));

    			$e_balance = $this->db->get_where('employee_leave_balance', array('year' => $next_year, 'employee_id' => $row->user_id, 'deleted' => 0));

    			if ($e_prev_balance && $e_prev_balance->num_rows() > 0){
    				$e_prev_balance_row = $e_prev_balance->row();
    				$bl_carried = $e_prev_balance_row->bl;
    			}

				if($e_balance->num_rows() > 0) {
					$balance = $e_balance->row();
					$this->db->set('bl', 1);
					$this->db->set('carried_bl', $bl_carried);
					$this->db->where('leave_balance_id', $balance->leave_balance_id);
					$this->db->update('employee_leave_balance');
				} else {
					$data = array(
						'year' => $next_year,
						'employee_id' => $row->user_id,
						'bl' => 1,
						'carried_bl' => $bl_carried,
						'deleted' => 0
					);

					$this->db->insert('employee_leave_balance', $data);
				}
    		}

			$response->msg_type = "success";
			$response->msg = "Task is successfully executed";		
			
			return $response;	    		
    	}
    }		*/	


    function training_send_reg_notification(){


    	$module_info = $this->db->get_where('module',array('code'=>'Training_Calendar'))->row();

    	$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
    	$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
    	$this->db->join('training_calendar_type','training_calendar_type.calendar_type_id = training_calendar.calendar_type_id','left');
    	$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id','left');
		$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
		$this->db->where('training_calendar_participant.send_reg_notification',0);
		$this->db->where('training_calendar.closed != 0');
		$training_calendar_participant_result = $this->db->get('training_calendar');

		if( $training_calendar_participant_result->num_rows() > 0 ){

			foreach( $training_calendar_participant_result->result() as  $participant_info ){

				$training_calendar_session = $this->db->get('training_calendar_session',array('training_calendar_id'=>$participant_info->training_calendar_id));
				$no_of_days = $training_calendar_session->num_rows();

				if( $participant_info->last_registration_date != "" && ( strtotime('+15 day',strtotime(date('Y-m-d'))) >= strtotime(date('Y-m-d',strtotime($participant_info->last_registration_date))) ) ){

					$data = array();

					$data['training_topic'] = $participant_info->topic;
					$data['participant_name'] = $participant_info->firstname.' '.$participant_info->lastname;
					$data['course_title'] = $participant_info->training_subject;
					$data['description'] = $participant_info->remarks;
					$data['training_dates'] = "";
					$data['no_of_days'] = $no_of_days;
					$data['venue'] = $participant_info->venue;
					$data['type'] = $participant_info->calendar_type;
					$data['provider'] = $participant_info->training_provider;
					$data['training_bond'] = $participant_info->rls;
					$data['cost'] = $participant_info->cost_per_pax;
					$data['last_registration_date'] = date('F d Y',strtotime($participant_info->last_registration_date));

					$training_calendar_session_result = $this->db->get_where('training_calendar_session',array('training_calendar_id'=>$participant_info->training_calendar_id));

					if( $training_calendar_session_result->num_rows() > 0 ){

						$data['training_dates'] .= "<table style='margin-left:.5in;'>";

						foreach( $training_calendar_session_result->result() as $calendar_session_info ){

							$data['training_dates'] .= "<tr><td>".date('F d Y',strtotime($calendar_session_info->session_date))." : ".date('h:i a',strtotime($calendar_session_info->sessiontime_from))." - ".date('h:i a',strtotime($calendar_session_info->sessiontime_to))."</td></tr>";

						}

						$data['training_dates'] .= "</table>";

					}

					$participant_reporting_to = $this->system->get_reporting_to($participant_info->employee_id);
					$participant_reporting_to = explode(',',$participant_reporting_to);

					$this->load->model('template');
		            $template = $this->template->get_module_template($module_info->module_id, 'send_last_reg_training_reporting_to');

					foreach( $participant_reporting_to as $reporting_to ){

						$reporting_to_info = $this->db->get_where('user',array('employee_id'=>$reporting_to))->row();

						$data['immediate_name'] = $reporting_to_info->firstname.' '.$reporting_to_info->lastname;

		                $message = $this->template->prep_message($template['body'], $data);
		  
		                $this->template->queue($reporting_to_info->email, '', $template['subject'], $message);

					}
					
					$template2 = $this->template->get_module_template($module_info->module_id, 'send_last_reg_training');

		            $message = $this->template->prep_message($template2['body'], $data);

		            $this->template->queue($participant_info->email, '', $template2['subject'], $message);
		            
		            $this->db->where('calendar_participant_id',$participant_info->calendar_participant_id);
		           $this->db->update('training_calendar_participant',array('send_reg_notification'=>1));
		            
	        	}

			}

		}
		


		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
			
		return $response;	

    }


    function training_calculate_running_balance(){


    	$this->db->select("*");
		$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
		$this->db->where('user.deleted', 0);
		$this->db->where('user.employee_id <>', 1);
		$this->db->where('employee.resigned', 0);
		$users_result = $this->db->get('user');
			
		foreach( $users_result->result() as $employee_training ){

	    	$this->db->join('training_calendar','training_calendar.training_calendar_id = training_employee_database.training_calendar_id','left');
			$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
			$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
			$this->db->join('training_subject_schedule','training_subject_schedule.training_subject_schedule_id = training_subject.training_subject_schedule_id','left');
			$this->db->where('training_employee_database.employee_id',$employee_training->employee_id);
			$training_database_result = $this->db->get('training_employee_database');

			$total_training_cost = 0;

			if( $training_database_result->num_rows() > 0 ){

				$total_running_balance = 0;

				foreach( $training_database_result->result() as $training_database_info ){

					if(  ( strtotime(date('d F Y')) > strtotime($training_database_info->bond_start_date) ) && ( strtotime(date('d F Y')) <= strtotime($training_database_info->bond_end_date) ) ){

						$act = $training_database_info->cost_per_pax;
						$als = ( strtotime($training_database_info->bond_end_date) - strtotime(date('d F Y')) ) / 60 / 60 / 24;
						$rls = $training_database_info->no_bond_days;
						$bls = $rls - $als;

						$running_balance = number_format( $act - ( ( $act * $bls ) / $rls ),2 );

						$total_running_balance += $running_balance;

						$this->db->where('training_employee_database_id',$training_database_info->training_employee_database_id);
						$this->db->update('training_employee_database',array('training_balance'=>$running_balance));

					}
					else{

						if( strtotime(date('d F Y')) > strtotime($training_database_info->bond_end_date)   ){

							$total_running_balance += 0;

							$this->db->where('training_employee_database_id',$training_database_info->training_employee_database_id);
							$this->db->update('training_employee_database',array('training_balance'=>0.00));

						}
						else{

							$total_running_balance += $training_database_info->cost_per_pax;

							$this->db->where('training_employee_database_id',$training_database_info->training_employee_database_id);
							$this->db->update('training_employee_database',array('training_balance'=>$training_database_info->cost_per_pax));

						}

						

					}

				}

				$this->db->where('employee_id',$employee_training->employee_id);
				$this->db->update('employee',array('total_training_running_balance'=>$total_running_balance));

			}

		}

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
			
		return $response;	

    }

    function send_training_live()
	{
		$current = date('Y-m-d');
		$yesterday = date('Y-m-d', strtotime('-1 day', strtotime($current)));

		$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
        	// Load the template.            
            $this->load->model('template');
            
            $template = $this->template->get_module_template(0, 'live_new');

            $this->db->where('evaluation_date >=', $yesterday);
            $this->db->where('email_sent', 0);
            $this->db->where('deleted', 0);
            $training_live = $this->db->get('training_live');

            if ($training_live && $training_live->num_rows() > 0) {
            	$live = $training_live->result();

            	foreach ($live as $key => $value) {
            		$this->db->where('FIND_IN_SET('.$value->division_id.', division_id)');
					$hr_details = $this->db->get('training_email_settings')->row();
					$email_cc_recipient = $hr_details->email;

            		$employee = $this->db->get_where('user', array('employee_id' => $value->employee_id))->row();

		            $recipients = $employee->email;
		            $request['employee'] = $employee->salutation.' '.$employee->firstname.' '.$employee->lastname;
		            $request['title'] = $value->course;
		            $request['training_dates'] = (!is_null($value->training_date)) ? date('F d, Y', strtotime($value->training_date)) : '' ;

		            $message = $this->template->prep_message($template['body'], $request, true, true);                                                                      
		            $this->template->queue($recipients, $email_cc_recipient, $template['subject'], $message);

		            if (true) {
		            	$this->db->where('training_live_id', $value->training_live_id);
		            	$this->db->update('training_live', array('email_sent' => 1));
		            }
		            
            	}
            }

        }     

        $response->msg_type = "success";
		$response->msg = "Task is successfully executed";		
			
		return $response;	

	}

    function fix_suffix(){
    	$this->db->where('aux','0');
    	$this->db->update('user',array('aux' => ''));
    	
		$response->msg = "Task is successfully executed";
		$response->msg_type = 'success';
		return $response;    	
    }
}
?>