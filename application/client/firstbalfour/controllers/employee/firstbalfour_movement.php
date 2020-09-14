<?php

include (APPPATH . 'controllers/employee/movement.php');

class Firstbalfour_movement extends Movement
{
	public function __construct() {
		parent::__construct();
	}	

	function get_employee() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('employee_payroll.salary, user_company_segment_1.segment_1, shift_calendar_id, user_company_segment_2.segment_2, user_location.location, user_company_division.division, user_rank_code.job_rank_code, user_rank_range.job_rank_range, user_rank_level.rank_level AS desc_job_level, employee_type.employee_type AS curr_employee_type, user_rank.job_rank, employee.*, user.user_id, user.firstname, user.lastname, position, department, user.position_id, user.department_id,user.division_id,user.project_name_id, user_company.company, user.role_id AS current_role_id, employment_status.employment_status, user.segment_1_id, user.segment_2_id, user.company_id, employee.job_level,employee.reporting_to as reporting_to');

			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');

			$this->db->join('user_rank', 'employee.rank_id = user_rank.job_rank_id', 'left');
			$this->db->join('employee_type', 'employee.employee_type = employee_type.employee_type_id', 'left');
			$this->db->join('user_rank_level', 'employee.job_level = user_rank_level.rank_level_id', 'left');
			$this->db->join('user_rank_range', 'employee.range_of_rank = user_rank_range.job_rank_range_id', 'left');
			$this->db->join('user_rank_code', 'employee.rank_code = user_rank_code.job_rank_code_id', 'left');
			$this->db->join('user_company_division', 'user.division_id = user_company_division.division_id', 'left');
			$this->db->join('user_location', 'employee.location_id = user_location.location_id', 'left');
			$this->db->join('user_company_segment_1', 'user.segment_1_id = user_company_segment_1.segment_1_id', 'left');
			$this->db->join('user_company_segment_2', 'user.segment_2_id = user_company_segment_2.segment_2_id', 'left');
			$this->db->join('employment_status', 'employee.status_id = employment_status.employment_status_id', 'left');
			$this->db->join('employee_dtr_setup', 'employee.employee_id = employee_dtr_setup.employee_id', 'left');
			$this->db->join('employee_payroll', 'employee.employee_id = employee_payroll.employee_id', 'left');

			// add campaign for openaccess
			if($this->config->item('with_campaign') == 1)
			{
				$this->db->select('campaign.campaign, campaign.campaign_id');
				$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
			}

			// $this->db->join('role', 'user.role_id = role.role_id', 'left');

			$this->db->where('user.user_id', $this->input->post('employee_id'));
			$this->db->where('user.deleted', 0);
			$this->db->limit(1);

			$employee = $this->db->get('user');

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= mysql_error();
			} else {
				$response->msg_type = 'success';

			$response->data = $employee->row_array();
				
				$employee=$employee->row();
				
				$approvers = $this->db->get_where('user_position_approvers', array('position_id' => $employee->position_id, 'module_id' => $this->module_id));
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
				} else $approvers="";

				$response->data['salary'] = str_replace( ',', '', $this->encrypt->decode($response->data['salary']));
				$response->data['approvers'] = $approvers;
			}						
		}

		$this->load->view('template/ajax', array('json' => $response));
	}	
}

?>