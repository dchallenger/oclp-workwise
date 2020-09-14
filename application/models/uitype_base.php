<?php

/**
 * Use this class to share functions across UI types.
 * 
 */

class Uitype_base extends CI_Model
{
	function get_user_approvers()
	{
		if (get_class($this) == 'uitype_edit') {		
			$approvers = $this->hdicore->get_approvers($this->userinfo['position_id']);

			return $approvers['admins'];
		} else {
			return $this->db->get('user')->result_array();
		}
	}

	function get_employees()
	{
		if (get_class($this) == 'uitype_edit') {		
			$this->db->where('user.deleted', 0);
			$this->db->where('resigned', 0);
			$this->db->where('role_id <>', 1);
		} 
		$this->db->join('employee', 'employee.user_id = user.user_id');	
		//$this->db->order_by('user.firstname', 'ASC')
		return $this->db->get('user')->result_array();
	}

	function get_all_managers()
	{
		if (get_class($this) == 'uitype_edit') {		
			$this->db->where('user.deleted', 0);
			$this->db->where('user_position.position_level_id <=', 6);
			$this->db->where('user_position.position_id <>', 1);
			$this->db->where('resigned', 0);
			$this->db->join('user_position', 'user_position.position_id = user.position_id');
		}
		
		return $this->db->get('user')->result_array();			
	}
	
	function get_all_subordinates()
	{
		if (get_class($this) == 'uitype_edit') {		
			//not permanent just to show the name list when super admin or post control is logged in
			if($this->userinfo['user_id']!=1){
				$this->db->select('user.user_id, position, firstname, middleinitial, lastname, aux');
				$this->db->join('user_position', 'user.position_id = user_position.position_id');
				$this->db->join('employee', 'user.employee_id = employee.employee_id', 'left');
				$this->db->where('user.deleted', 0);
				$this->db->where('employee.resigned', 0);
			}
			if($this->userinfo['user_id']==1){
				$this->db->select('user.user_id, position, firstname, middleinitial, lastname, aux');
				$this->db->join('user_position', 'user.position_id = user_position.position_id');
				$this->db->join('employee', 'user.employee_id = employee.employee_id', 'left');
				$this->db->where('user.deleted', 0);
				$this->db->where('employee.resigned', 0);
			}

		}
		return $this->db->get('user')->result_array();			
	}

	function get_employees_within_department()
	{
		if (get_class($this) == 'uitype_edit') {		
			//not permanent just to show the name list when super admin or post control is logged in

			if($this->userinfo['user_id']!=1 ){

				$this->db->select('user_id, department, firstname, middleinitial, lastname, aux');
				$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id');
				$this->db->where('user.deleted', 0);
				$this->db->where('user_company_department.department_id', $this->userinfo['department_id']); 

				$result = $this->db->get('user')->result_array();

			}
			if($this->userinfo['user_id']==1 || $this->user_access[$this->module_id]['post'] == 1){

				$this->db->select('user_id, department, firstname, middleinitial, lastname, aux');
				$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id');
				$this->db->where('user.deleted', 0);

				$result = $this->db->get('user')->result_array();
				
			}
		}

		return $result;
	}


	function get_employees_with_position_ecf_member()
	{
		if (get_class($this) == 'uitype_edit') {		
			$this->db->select('user.user_id, user_position.position, user.firstname, user.middleinitial, user.lastname, user.aux');
			$this->db->join('user_position', 'user.position_id = user_position.position_id');
			$this->db->join('employee', 'employee.employee_id = user.employee_id');
			$this->db->where('employee.ecf', 1);
			$this->db->where('user.deleted', 0);
			
			$this->db->where('user.role_id <>', 1);
		} 
		return $this->db->get('user')->result_array();	
	}

	function get_department()
	{
		if (get_class($this) == 'uitype_edit') {		
			$this->db->select('user.*, user_company_department.*');
			$this->db->join('user_company_department', 'user.department_id = user_company_department.department_id');
			$this->db->where('user.deleted', 0);
			$this->db->where('user.role_id <>', 1);
			
		} 
		
		return $this->db->get('user')->result_array();		
	}

	function get_leave_dropdown($user_id = null)
	{	
		if($user_id != null){
			$this->db->join('user','employee.employee_id = user.user_id');
			$user_information = $this->db->get_where('employee', array('employee.employee_id' => $user_id));
		}

		if (get_class($this) == 'uitype_edit') 
		{
			if (is_null($user_id) || trim($user_id) == '') {
				$user_id = $this->userinfo['user_id'];
			}
			
			$currYear=date('Y');

    		$year = date('Y');
    		$today = new DateTime( date('Y-m-d') );
    		$employee = $this->db->get_where('employee', array('employee_id' => $user_id))->row();
    		$user_info = $this->db->get_where('user', array('employee_id' => $user_id))->row();

    		$hired = new DateTime( $employee->employed_date);
    		$interval = $today->diff($hired);

			$this->db->select('user.sex, employee.civil_status_id, employee_leave_balance.*');
			$this->db->where('user.user_id', $user_id);
			
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');

			$this->db->join('employee_leave_balance', 'employee.employee_id = employee_leave_balance.employee_id', 'left');
			$this->db->where('employee_leave_balance.year',$currYear);
			$this->db->where('employee_leave_balance.employee_id', $user_id);

			$user = $this->db->get('user')->row();

			if (!empty($employee)){
				$where = "FIND_IN_SET(".$employee->status_id.", employment_status_id) AND FIND_IN_SET(".$employee->employee_type.", employee_type_id)";
				$this->db->where($where);
			}

			$this->db->where('employee_type_leave_setup.deleted', 0);

			$this->db->join('employee_form_type','employee_form_type.application_form_id = employee_type_leave_setup.application_form_id','LEFT');
			
			// $this->db->join('employee_form_type','employee_form_type.application_form_id = employee_type_leave_setup.application_form_id','LEFT');
			// $this->db->where(array("employee_type_leave_setup.employee_type_id" => $employee->employee_type, "employee_type_leave_setup.deleted" => 0));
			//$this->db->where(array("employee_type_leave_setup.employee_type_id" => $employee->employee_type, "employee_type_leave_setup.deleted" => 0, "employee_type_leave_setup.tenure <>" => 0));
			$leave_tenure = $this->db->get_where('employee_type_leave_setup');

			if($leave_tenure && $leave_tenure->num_rows() > 0)
				$vl_tenure = $leave_tenure->row()->tenure;
			else
				$vl_tenure = 0;


			$leave_info_result = $leave_tenure->result();

			
			$this->db->where('is_leave', 1);
			$this->db->where('deleted', 0);
			
			if ($user_info->sex == 'male') {
				$this->db->where('unique_women', 0);
				if ($user->civil_status_id != 2) {
					$this->db->where('unique_men', 0);				
				}
			} else {
				$this->db->where('unique_men', 0);
			}

			/* Code removed, client request */

			if( ($user->vl - $user->Vl_used) <= 0 && $user->carried_vl <= 0 )
				$this->db->where('application_code <>', 'EL');

			if (CLIENT_DIR != 'firstbalfour'){
				if( ($user->mpl - $user->mpl_used) <= 0 )
				{
					$this->db->where('application_code <>', 'PL');
					/*$this->db->where('application_code <>', 'ML');*/
				}
			}
			
			if( ($user->bl - $user->bl_used) <= 0 )
				$this->db->where('application_code <>', 'BL');

    		if( $interval->y < $this->config->item('mintenure_birthday_leave')) {
				$this->db->where('application_code <>', 'BiL');
	    	}

			// added for ticket 218
	    	if($user_information && $user_information->num_rows() > 0)
			{
				$user_information = $user_information->row();

				if( $user_information->status_id == 2 )
					$this->db->where('application_code <>', 'EL');
			
				if( CLIENT_DIR == 'firstbalfour'){
					$organization = explode(',', $user_information->segment_2_id);
					if (!in_array(4, $organization)){
						$this->db->where('application_code <>', 'UL');
					}
				}

				if($user_information->employee_type == 1)
				{
					
					if($user_information->status_id == 1)
					{

						foreach( $leave_info_result as $leave_info ){

							if( $leave_info->application_code != 'VL' && $leave_info->application_code != 'SL' ){

								$one_year = date('Y-m-d', strtotime(date("Y-m-d", strtotime($user_information->employed_date)) . " + ".$leave_info->tenure." month"));

								if( date('Y-m-d') < $one_year )
								{

									$this->db->where('application_code <>', $leave_info->application_code);
								}

							}

						}

					} else {

						$this->db->where('application_code <>', 'SL');
						$this->db->where('application_code <>', 'VL');
						$this->db->where('application_code <>', 'EL');
					}

				}
				else
				{
					if (CLIENT_DIR != 'firstbalfour'){
						if($user_information->status_id == 1 || $user_information->status_id == 2 || $user_information->status_id == 4 || $user_information->status_id == 13)
						{
							foreach( $leave_info_result as $leave_info ){

								$one_year = date('Y-m-d', strtotime(date("Y-m-d", strtotime($user_information->employed_date)) . " + ".$leave_info->tenure." month"));

								if(date('Y-m-d') < $one_year)
								{

									$this->db->where('application_code <>', $leave_info->application_code);
								}

							}

						} else {

							$this->db->where('application_code <>', 'SL');
							$this->db->where('application_code <>', 'VL');
							$this->db->where('application_code <>', 'EL');
						}						
					}
					else{
						foreach( $leave_info_result as $leave_info ){
							$employment_status = explode(',', $leave_info->employment_status_id);

							if(in_array($user_information->status_id, $employment_status) && $user_information->status_id != 2)
							{
								//$this->db->where('application_code', $leave_info->application_code);
							}
							else{
								$this->db->where('application_code <>', $leave_info->application_code);
							}
						}
					}
				}
			}

			// added for ticket 218
		}

		$this->db->where('is_leave', 1); // added because it only gets the leave forms
		$types = $this->db->get('employee_form_type')->result_array();

		return $types;

	}

	function get_family()
	{
		if (get_class($this) == 'uitype_edit') {
			$this->db->where('deleted', 0);
			$this->db->where('employee_id', $this->userinfo['user_id']);
		}
		
		return $this->db->get('employee_family')->result_array();			
	}

	function get_month_dropdown()
	{
		$months = array();
		for ($i = 1; $i <= 12;) {
			$months[$i - 1]['label'] = date('F', mktime(0, 0, 0, $i, 1, 2000));
			$months[$i - 1]['value'] = number_pad($i, 2);
			$i++;
		}

		return $months;
	}

	function get_clearance_signatories()
	{
		$this->db->select('ecfc.employee_clearance_form_checklist_id, CONCAT(firstname, " ",middleinitial, " ",lastname, " ", aux) as employee, ecfc.name, user.user_id', false);
		$this->db->from('employee_clearance_form_checklist as ecfc ');		
		$this->db->join('user user', 'user.user_id = ecfc.approver_id', 'left');
		$this->db->where('ecfc.deleted', 0);

		return $this->db->get()->result_array();
	}

	function get_employee_per_department()
	{
		//$this->db->select('ecfc.employee_clearance_form_checklist_id, CONCAT(user.firstname, " ", user.lastname) as employee, ecfc.name', false);
		$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
		$this->db->where('department_id', $this->user_info['department_id']);
		$this->db->where('resigned', 0);
		
		return $this->db->get('user')->result_array();
	}

	function get_subordinates()
	{
		if (get_class($this) == 'uitype_edit') {	
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			return $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
		} else {
			return $this->db->get('user')->result_array();
		}
	}

	function get_manpower_applicants()
	{
		if (get_class($this) == 'uitype_edit') {
			if ($this->input->post('record_id') == '-1') {
				if ($this->input->post('mrf_id') == '') {					
					return $this->db->get('recruitment_applicant')->result_array();
				} else {
					$this->db->select('recruitment_applicant.*');
					$this->db->join('recruitment_applicant', 'recruitment_applicant.position_id = recruitment_manpower.position_id', 'left');
					$this->db->where('request_id', $this->input->post('mrf_id'));
					$this->db->where_in('application_status_id', array(1,3,5));					
					
					return $this->db->get('recruitment_manpower')->result_array();
				}
			} else {
				/*$record = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $this->input->post('record_id')));

				$this->db->select('recruitment_applicant.*');
				$this->db->join('recruitment_applicant', 'recruitment_applicant.position_id = recruitment_manpower.position_id', 'left');
				$this->db->where('request_id', $record->row()->mrf_id);
				$this->db->where_in('application_status_id', array(1,2,3,5));
				
				return $this->db->get('recruitment_manpower')->result_array();
				*/
			}
		} /*else {
			return $this->db->get('recruitment_applicant')->result_array();
		}*/

		return $this->db->get('recruitment_applicant')->result_array();
	}	

	function org_all()
	{
		$this->db->select('employee_id');
		$this->db->where('deleted',0);
		$this->db->where('orgchart_id', $this->input->post('orgchart_id'));
		$employees=$this->db->get('orgchart_detail');
		if($employees->num_rows()>0) {
			$emp=$employees->result_array();
			$employee_shown=array();
			foreach($emp as $emp_id)
				$employee_shown[] = $emp_id['employee_id'];
			$this->db->where_not_in('employee_id', $employee_shown);

			return $this->db->get('user')->result_array();
		} else
			return $this->db->get('user')->result_array();
	}

	function get_module_approver()
	{
		if (get_class($this) == 'uitype_edit') {		
			$approvers = $this->db->get_where('user_position_approvers', array('position_id' => $this->userinfo['position_id'], 'module_id' => $this->module_id));

			if($approvers->num_rows() > 0) {
				$approvers = $approvers->result_array();
				$app=array();
				foreach($approvers as $row){
					$app_id = $this->db->get_where('user', array('position_id' => $row['approver_position_id']))->result_array();
					foreach($app_id as $id)
						$app[] = $id['employee_id'];
				}
				$this->db->where_in('employee_id',$app);
				$approvers = $this->db->get('user')->result_array();
				return $approvers;
			}
		} else {
			return $this->db->get('user')->result_array();
		}
	}

	function get_override_sanction()
	{
		$this->db->group_by('offence_sanction');
		return $this->db->get_where('offence_sanction')->result_array();
	}
}