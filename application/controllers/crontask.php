<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Process requests from cron.php
 *
 * Usage:
 * (Windows)
 * C:\xampp\php\php.exe <HRIS_DIR>cron.php -u <USERNAME> -p <PASSWORD> -m <METHOD>
 *
 * Note:
 * Methods called by cron.php should be declared as private and must begin with an underscore.
 *
 */

class Crontask extends CI_Controller
{

	// ------------------------------------------------------------------------
	
	public function __construct()
	{
		parent::__construct();		

		$this->load->add_package_path(MODPATH . CLIENT_DIR);

		// Reload the client.php config, this time the client packages have been added to
		// the config file paths, if any, this will override the default config/client.php
		$this->load->config('client');				
	}

	function scheduled_task(){
		$datetime = strtotime( date('Y-m-d H:i') );
		$minute = intval( date('i', $datetime ) );
		$hour = intval( date('H', $datetime ) );
		$day_of_month = date('j', $datetime );
		$month = date('n', $datetime );
		$day_of_week = date('N', $datetime );

		$qry = "select a.scheduled_task_id, a.template_id, a.email_to, a.email_cc, a.email_bcc, g.crontask_function	
		FROM {$this->db->dbprefix}scheduled_task a
		LEFT JOIN {$this->db->dbprefix}scheduled_task_minute b ON b.scheduled_task_id = a.scheduled_task_id
		LEFT JOIN {$this->db->dbprefix}scheduled_task_hour c ON c.scheduled_task_id = a.scheduled_task_id
		LEFT JOIN {$this->db->dbprefix}scheduled_task_day_of_month d ON d.scheduled_task_id = a.scheduled_task_id
		LEFT JOIN {$this->db->dbprefix}scheduled_task_month e ON e.scheduled_task_id = a.scheduled_task_id
		LEFT JOIN {$this->db->dbprefix}scheduled_task_day_of_week f ON f.scheduled_task_id = a.scheduled_task_id
		LEFT JOIN {$this->db->dbprefix}crontask_function g ON g.crontask_function_id = a.crontask_function_id
		WHERE a.deleted = 0 and a.task_status = 1
		and b.minute = {$minute} and c.hour = {$hour} and d.day_of_month = {$day_of_month} and e.month = {$month} and f.day_of_week = {$day_of_week}";

		$scheduled_tasks = $this->db->query( $qry );

		if( $scheduled_tasks->num_rows() > 0 ){
			if( !$this->load->model(CLIENT_DIR.'_cron', 'cron', false, true) ) $this->load->model('cron');
			foreach( $scheduled_tasks->result() as $task ){
				$this->cron->_execute_task( $task );
			}
		}
	}

	
	// ------------------------------------------------------------------------

	/**
	 * Checks user credentials and routes method to use.
	 *  
	 * @return void
	 */
	public function index()
	{		

		$user = $this->hdicore->_verify_login(
			$this->input->post('u'), $this->input->post('p')
			);

		if ($user !== FALSE) {
			$this->session->set_userdata('user', $user);
			$method = $this->input->post('m');

			print ('Verifying method: _' . $method . '() .' . "\n");

			if (!method_exists($this, '_' . $method)) {
				print ('Method \'_' . $method . '()\' does not exist.' . "\n" );
			} else {
				print ('Calling method: _' . $method . '() .' . "\n");
				// Call method. (from "-c" option)
				call_user_func(array($this, '_' . $method));
			}			

			$this->session->unset_userdata('user');
			$this->session->sess_destroy();

		} else {			
			die('Login failed.' . "\n");
		}
		
	}

	// ------------------------------------------------------------------------

	/*
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m all
	 * 
	 * Runs all tasks.
	 */
	private function _all()
	{
		$this->_employee_movement();
		$this->_awol_notifier();
		$this->_ut_notifier();
	}

	function call_processing(){
		$date_today = date('Y-m-d');
		$date_month = date('m');

		$this->db->where('period_month', $date_month);
		$this->db->where('date_from <', $date_today);
		$this->db->where('date_to >', $date_today);
		$this->db->where('deleted', 0);
		$result=$this->db->get('timekeeping_period');
		if($result->num_rows() > 0) {
			$result=$result->row_array();

			$this->db->where('deleted', 0);
			$employee_id=$this->db->get('user')->result_array();
			$employees=array();
			foreach($employee_id as $emp_id) {
				$employees[] = $emp_id['employee_id'];
			}
				$this->system->call_processing($result['period_id'], 'employee_id', $employees);
		}
	}

	function scheduler(){

		$this->db->join('crontask_function','crontask_function.crontask_function_id = alert_frequency.crontask_function_id','left');
		$this->db->where('alert_frequency.deleted',0);
		$scheduler_result = $this->db->get('alert_frequency');

		if( $scheduler_result->num_rows() > 0 ){

			foreach( $scheduler_result->result() as $scheduler_info ){

				//One Time
				if( $scheduler_info->hour_implement_type_id == 1 ){

					//Check if scheduled day is equal to current day
					$day_list = explode(',', $scheduler_info->day_id );

					$this->db->select('day');
					$this->db->where_in('day_id',$day_list);
					$this->db->where('deleted',0);
					$days_result = $this->db->get('day')->result_array();

					foreach( $days_result as $days_info ){

						if( in_array(date('l'), $days_info) ){

							//Check if scheduled time is equal to current time
							if( date('H:i',strtotime($scheduler_info->time)) == date('H:i') ){

								//Check if method exist
								if(method_exists($this, $scheduler_info->crontask_function)){
						            
									$data = array();

									//Get variables from alert frequency
						            $this->db->join('crontask_variable','crontask_variable.crontask_variable_id = alert_frequency_variable.crontask_variable_id','left');
									$this->db->where('alert_frequency_variable.deleted',0);
									$this->db->where('alert_frequency_variable.alert_frequency_id',$scheduler_info->alert_frequency_id);
									$variable_result = $this->db->get('alert_frequency_variable');

									if( $variable_result->num_rows() > 0 ){

										foreach( $variable_result->result() as $variable_info ){
											$value = ( $variable_info->value == '' || $variable_info->value == null )? $variable_info->default_value : $variable_info->value;
											$data[$variable_info->crontask_variable] = $value;
										}

									}

									$this->{$scheduler_info->crontask_function}();

									$data = array(
										'alert_frequency_id' => $scheduler_info->alert_frequency_id,
										'datetime' => date('Y-m-d H:i:d')
									);

									$this->db->insert('alert_frequency_history',$data);

						        }
							}
							
						}

					}
				}
				//Recurring
				elseif( $scheduler_info->hour_implement_type_id == 2 ){

					//Check if scheduled day is equal to current day
					$day_list = explode(',', $scheduler_info->day_id );

					$this->db->select('day');
					$this->db->where_in('day_id',$day_list);
					$this->db->where('deleted',0);
					$days_result = $this->db->get('day')->result_array();

					foreach( $days_result as $days_info ){

						if( in_array(date('l'), $days_info) ){

							$this->db->where('alert_frequency_id',$scheduler_info->alert_frequency_id);
							$this->db->where('deleted',0);
							$this->db->order_by('datetime','desc');
							$history_result = $this->db->get('alert_frequency_history');

							if( $history_result->num_rows() > 0 ){

								$history_info = $history_result->row();

								$today = new DateTime();
								$history_date = new DateTime($history_info->datetime);
								$date_diff = $history_date->diff($today);

								if( $date_diff->h >= $scheduler_info->hour && $date_diff->i >= $scheduler_info->minute ){

									//Check if method exist
									if(method_exists($this, $scheduler_info->crontask_function)){

										$data = array();

										//Get variables from alert frequency
							            $this->db->join('crontask_variable','crontask_variable.crontask_variable_id = alert_frequency_variable.crontask_variable_id','left');
										$this->db->where('alert_frequency_variable.deleted',0);
										$this->db->where('alert_frequency_variable.alert_frequency_id',$scheduler_info->alert_frequency_id);
										$variable_result = $this->db->get('alert_frequency_variable');

										if( $variable_result->num_rows() > 0 ){

											foreach( $variable_result->result() as $variable_info ){
												$value = ( $variable_info->value == '' || $variable_info->value == null )? $variable_info->default_value : $variable_info->value;
												$data[$variable_info->crontask_variable] = $value;
											}

										}
							            
										$this->{$scheduler_info->crontask_function}();

										$data = array(
											'alert_frequency_id' => $scheduler_info->alert_frequency_id,
											'datetime' => date('Y-m-d H:i:d')
										);

										$this->db->insert('alert_frequency_history',$data);
							        }
							        
								}

							}
							else{

								//Check if method exist
								if(method_exists($this, $scheduler_info->crontask_function)){
						            
									$data = array();

									//Get variables from alert frequency
						            $this->db->join('crontask_variable','crontask_variable.crontask_variable_id = alert_frequency_variable.crontask_variable_id','left');
									$this->db->where('alert_frequency_variable.deleted',0);
									$this->db->where('alert_frequency_variable.alert_frequency_id',$scheduler_info->alert_frequency_id);
									$variable_result = $this->db->get('alert_frequency_variable');

									if( $variable_result->num_rows() > 0 ){

										foreach( $variable_result->result() as $variable_info ){
											$value = ( $variable_info->value == '' || $variable_info->value == null )? $variable_info->default_value : $variable_info->value;
											$data[$variable_info->crontask_variable] = $value;
										}

									}

									$this->{$scheduler_info->crontask_function}();

									$data = array(
										'alert_frequency_id' => $scheduler_info->alert_frequency_id,
										'datetime' => date('Y-m-d H:i:d')
									);

									$this->db->insert('alert_frequency_history',$data);

						        }
							}
						}
					}
				}
			}
		}

	}

	// ------------------------------------------------------------------------

	/**
	 * Queries the employee_movement table for "approved" requests and performs various tasks depending 
	 * on the nature of the movement.
	 *
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m employee_movement
	 * 
	 * @return void
	 * 
	 */
	private function _employee_movement()
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
			print ('ERROR: ' . $this->db->_error_message() . "\n");
			print ('QUERY: ' . $this->db->last_query() . "\n");
		} else if ($results->num_rows() == 0) {
			print ('No results found.' . "\n");
		} else {
			$requests = $results->result();
			$complete = array();			

			foreach ($requests as $request) {
				print ('Processing request for: ' . $request->name . "\n");				

				$this->db->where('employee_id', $request->employee_id);
				$this->db->where('deleted', 0);
				
				$this->db->limit(1);

				$employee  = $this->db->get('employee');
				$completed = TRUE;

				if ($employee->num_rows() == 0) {
					if ($this->db->insert('employee', array('employee_id' => $request->employee_id, 'user_id' => $request->employee_id))) {
						print ("\t" . 'Employee record created.'. "\n");						
					} else {
						print ("\t" . 'Failed to create employee record. Aborting...'. "\n");
						exit();
					}
				}				

				if ($request->compensation_effectivity_date <= $curr_date) {
					if ($this->_employee_update_compensation($request)) {
						print ("\t" . 'Compensation updated.' . "\n");
					} else {
						print ("\t" . 'Compensation update failed.' . "\n");
						$completed = FALSE;
					}
				}

				// if ($request->movement_effectivity_date <= $curr_date) {
				// 	if ($this->_employee_update_movement($request, $employee->row())) {
				// 		print ("\t" . 'Employee record updated.' . "\n");
				// 	} else {
				// 		print ("\t" . 'Employee record update failed.' . "\n");
				// 		$completed = FALSE;
				// 	}
				// }

				if ($request->transfer_effectivity_date <= $curr_date) {
					if ($this->_employee_update_transfer($request, $employee->row())) {
						print ("\t" . 'Employee transferred.' . "\n");
						// $completed=true;
					} else {
						print ("\t" . 'Employee transfer failed.' . "\n");
						$completed = FALSE;
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
	}

	// ------------------------------------------------------------------------

	private function _employee_update_resignation($request)
	{
		print ("\t" . 'Processing resignation.' . "\n");

		$updates = array();
		$movement_types = explode(',', $request->employee_movement_type_id);		

		if (in_array(6, $movement_types)) {
			$this->db->set('status_id', 9);
		}

		if (in_array(7, $movement_types)) {
			$this->db->set('status_id', 10);
		}

		$this->db->set('resigned_date', $request->last_day);
		$this->db->set('resigned', 1);
		$this->db->where('employee_id', $request->employee_id);
		if($this->db->update('employee')){

			$clearance = $this->db->get_where('employee_clearance',array('employee_id' => $request->employee_id ));

			if($clearance->num_rows() == 0){
				$data = array(
					'employee_id' => $request->employee_id,
					'deleted' => '0'
				);

				print ("\t" . 'Processing signatories.' . "\n");

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
			
			// Pro-rate leave credits, use leave setup based on employee type.
			$this->db->select('et.*, ef.application_code');
			$this->db->from('employee_type_leave_setup et');
			$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
			$this->db->where('employee_type_id', $employee->employee_type);
			$this->db->where('et.prorated', 1);
			$this->db->where('ef.deleted', 0);
			
			$leave_setup = $this->db->get();

			$ld = strtotime($request->last_day);
			$t = date('t', $ld);
			$d = date('d', $ld);
			$n = date('n', $ld);

			if ($leave_setup->num_rows() > 0) {
				$data['year'] = date('Y');
				foreach ($leave_setup->result() as $leave_type) {
					if (!$leave_type->prorated) {
						$data[strtolower($leave_type->application_code)] = $leave_type->base;
					} else {
						// Formula for pro-rated
						$monthly = $leave_type->base / 12;
						$curr_month_credits = $monthly * (($t - $d) / $t);
						$year_remaining_credits = $monthly * (12 - $n);
						$credits = $curr_month_credits + $year_remaining_credits;
						$credits = round($credits * 2) / 2;

						$data[strtolower($leave_type->application_code)] = $credits;
					}
				}
			}		

			$this->db->where('employee_id', $request->employee_id);
			$this->db->where('year', date('Y'));
			$this->db->update('employee_leave_balance', $data);				
			
			return true;
		}
	}

	/** ------------------------------------------------------------------------
	 * Process All Movement.
	 */

	private function _employee_update_transfer($request, $employee)
	{
		// initialize flags
		$user_flag=false;
		$employee_flag=false;
		$did_update=false;
		$clearance = false;

		// get nature of movement
		$movement_types = explode(',', $request->employee_movement_type_id);

		/**
		 * 201 Movement
		 */

		// changes on hr_user
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

		// change on hr_employee
		if ($request->rank_id > 0)			{ $this->db->set('rank_id', $request->rank_id); $employee_flag=true; 		 	 }
		if ($request->employee_type > 0)	{ $this->db->set('employee_type', $request->employee_type); $employee_flag=true; }
		if ($request->job_level > 0)		{ $this->db->set('job_level', $request->job_level); $employee_flag=true;		 }
		if ($request->range_of_rank > 0)	{ $this->db->set('range_of_rank', $request->range_of_rank); $employee_flag=true; }
		if ($request->rank_code > 0)		{ $this->db->set('rank_code', $request->rank_code); $employee_flag=true;		 }
		if ($request->location_id > 0)		{ $this->db->set('location_id', $request->location_id); $employee_flag=true;	 }

		if($employee_flag){
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('employee');
			$did_update=true;
		}

		// Process Floating Employees
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

		// For client's with campaign
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

		//employee work assignment
		if ($request->project_name_id > 0){
			// $this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('user',array("project_name_id"=>$request->project_name_id));
		}

		if ($request->group_name_id > 0){
			// $this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('user',array("group_name_id"=>$request->group_name_id));
		}

		if ($request->division_id > 0){
			// $this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('user',array("division_id"=>$request->division_id));
		}		

		if ($request->transfer_to > 0){
			// $this->db->where('assignment',1);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('user',array("department_id"=>$request->transfer_to));
		}

		// Change User's Employee Approver 
		if($request->employee_approver > 0)
		{  
			$modules = $this->db->query("SELECT
										  DISTINCT module_id
										FROM ".$this->db->dbprefix."employee_approver
										WHERE employee_id = ".$request->employee_id."
										    AND deleted = 0");

			if($modules && $modules->num_rows() > 0) {
				$modules = $modules->result();
				$this->db->delete('employee_approver', array('employee_id' => $request->employee_id, 'deleted' => 0));
				foreach($modules as $module_id) {
					$insert_approver = array( 'employee_id' => $request->employee_id,
											  'approver_employee_id' => $request->employee_approver,
											  'module_id' => $module_id->module_id,
											  'condition' => 1,
											  'approver' => 1,
											  'email' => 1,
											  'deleted' => 0
											 );
					$this->db->insert('employee_approver', $insert_approver);
				}
			} else {
				echo "No Previous Approver"; // .$this->db->last_query();
			}
		}

		/**
		 * Processing of REGULARUZATION, PROMOTION(LEAVES), RESIGNATION, TERMINATION, ENDO, RETIREMENT
		 */

		// Process REGULARIZATION. Congrats!
		if (in_array(1, $movement_types)) {

			// Check if we need to save leave balance during ajax save.
			if(!$this->config->item('save_leave_regularization_during_saving'))
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

			// I-regular na yan!
			$this->db->set('status_id', 1);
			$this->db->set('status_effectivity', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
			$this->db->set('regular_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
			$this->db->where('employee_id', $request->employee_id);
			return $this->db->update('employee');

		}

		// Process LEAVES FOR PROMOTION. Orayt.!
		if (in_array(3, $movement_types)) {
			// set promotion effectivity
			$this->db->set('last_promotion_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('employee');

			// let's brag that promotion!
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
					if($elb->sl > $leave_setup->inexcess)
					{
						$paid_sl = $elb->sl - $leave_setup->inexcess;
						if($elb->paid_sl)
							$paid_sl = $paid_sl + $elb->paid_sl;
						
						$sl = $leave_setup->inexcess;
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
					foreach ($leave_setup->result() as $leave_type)
						$data[strtolower($leave_type->application_code)] = $leave_type->base;

					$check_bal = $this->db->get_where('employee_leave_balance',array('employee_id' => $request->employee_id, 'year' => date('Y', $promotion_d)));
					if( $check_bal->num_rows() == 0)
						return $this->db->insert('employee_leave_balance', $data);
					else
						return $this->db->update('employee_leave_balance', $data, array('employee_id' => $request->employee_id, 'year' => date('Y', $promotion_d)));
				} else {
					// Add leave credits, use leave setup based on employee type.
					$this->db->select('et.*, ef.application_code, et.application_form_id AS "app_form_id"');
					$this->db->from('employee_type_leave_setup et');
					$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
					$this->db->where('employee_type_id', $request->employee_type);
					$this->db->where('ef.deleted', 0);					
					$leave_setup = $this->db->get();

					// get date diff
					$date_diff = gregoriantojd(12, 31, date('Y', $promotion_d)) - gregoriantojd(date('m', $promotion_d), date('d', $promotion_d), date('Y', $promotion_d));
					$prev_et_date_diff = gregoriantojd(date('m', $promotion_d), date('d', $promotion_d), date('Y', $promotion_d)) - gregoriantojd(1, 1, date('Y', $promotion_d));

					if ($leave_setup->num_rows() > 0) {
						$data['year'] = date('Y', $promotion_d);
						foreach ($leave_setup->result() as $leave_type) {					
							if (!$leave_type->prorated) {
								$data[strtolower($leave_type->application_code)] = $leave_type->base;
							} else {
								// Compute for pro-rated (new employee type)
								$monthly = $leave_type->base / 365;
								$days_remaining = $monthly * $date_diff;
								$credits = round($days_remaining * 2) / 2;

								// get old employee type based on id
								$prev_etype = $this->system->get_old_id_on_hidden($request->hidden_id_current, 'employee_type');

								// get old data
								$this->db->select('et.*, ef.application_code');
								$this->db->from('employee_type_leave_setup et');
								$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
								$this->db->where('employee_type_id', $prev_etype);
								$this->db->where('et.application_form_id', $leave_type->app_form_id);
								$this->db->where('ef.deleted', 0);
								$old_leave_setup = $this->db->get()->row();

								// Compute for pro-rated (old employee type)
								$monthly = $old_leave_setup->base / 365;
								$days_remaining = $monthly * $prev_et_date_diff;
								$prev_et_credits = round($days_remaining * 2) / 2;

								// get new leave balance
								$data[strtolower($leave_type->application_code)] = $credits + $prev_et_credits;
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

		// Process RESIGNATION, TERMINATION, ENDO, RETIREMENT. Bye bye!
		if(in_array(6, $movement_types) || in_array(7, $movement_types) || in_array(10, $movement_types) || in_array(11, $movement_types))
		{
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

			// Tag employee as resigned.
			$this->db->set('resigned', 1);
			$this->db->set('resigned_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
			$this->db->set('status_effectivity', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('employee');

			// Let's inform everyone!
			$this->save_memo(3, $memo_title, $request->employee_id, date('Y-m-d', strtotime($request->transfer_effectivity_date)));

			// as for ticket, this functionality is enabled
			$lastname = $this->db->get_where('user', array("employee_id" => $request->employee_id))->row()->lastname." *";
			$this->db->set('lastname', $lastname);
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('user');

			// Let's remove those cool edit buttons on resigned employees
			$this->db->set('uneditable', 1);
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('employee_leave_balance');

			// say bye bye to our system. :p
			$this->db->set('inactive', 1);
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('user');

			// oh btw, return everything before you go. no souvenirs.. :/
			if($clearance){

				$clearance = $this->db->get_where('employee_clearance',array('employee_id' => $request->employee_id ));

				if($clearance->num_rows() == 0){
					$data = array(
						'employee_id' => $request->employee_id,
						'deleted' => '0'
					);

					print ("\t" . 'Processing signatories.' . "\n");

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
					
					$leave_setup = $this->db->get();

					$ld = strtotime($request->last_day);
					$t = date('t', $ld);
					$d = date('d', $ld);
					$n = date('n', $ld);

					if ($leave_setup->num_rows() > 0) {
						$data['year'] = date('Y', $ld);
						foreach ($leave_setup->result() as $leave_type) {
							if (!$leave_type->prorated) {
								$data[strtolower($leave_type->application_code)] = $leave_type->base;
							} else {
								// Formula for pro-rated
								$monthly = $leave_type->base / 12;
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


		// SALARY INCREASE
		if (in_array(2, $movement_types)) {

			$this->db->set('status_effectivity', date('Y-m-d', strtotime($request->compensation_effectivity_date)));
			
			$this->db->where('employee_id', $request->employee_id);
			
			return $this->db->update('employee');

		}

		// did it? (i re-code this part since it always return TRUE)
		if(!$did_update) // Seriously..?! >_<
			print ("\t" . 'Nothing to update. Proceeding...' . "\n");

		// at the end of the day.. we'll still return true.
		return TRUE;
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


	// ------------------------------------------------------------------------

	private function _employee_update_compensation($request)
	{
		$updates = array();
		if ($request->new_position_id > 0) {
			$updates['position_id'] = $request->new_position_id;
		}

		// Put other info here....
		$this->db->update('user', $updates, array('employee_id' => $request->employee_id));
		$this->db->update('employee_payroll', array('salary' => $request->new_basic_salary ), array('employee_id' => $request->employee_id));

		$this->db->where('employee_movement_id', $request->employee_movement_id);
		$movement_benefit = $this->db->get('employee_movement_benefit');

		if (!$movement_benefit || $this->db->_error_message() != '') {

			print ('ERROR: ' . $this->db->_error_message() . "\n");
			print ('QUERY: ' . $this->db->last_query() . "\n");

		} else if ($movement_benefit->num_rows() == 0) {
			print ('INFO: No Result was found' . "\n");
		} else {

			$movement_benefits = $movement_benefit->result();
			$employee_benefits = $this->db->get_where('employee_benefit', array('deleted' => 0))->result();
			
			$this->db->update('employee_benefit',array('deleted' => 1), array('employee_id' => $request->employee_id));
			$benefits=$this->db->get('benefit')->result_array();
			
			foreach($benefits as $benefit) {
				$this->db->set('employee_id', $request->employee_id);
				$this->db->set('benefit_id', $benefit['benefit_id']);
				$this->db->insert('employee_benefit');
			}

			foreach($movement_benefits as $movement_benefit) {
				$data = array(
					'employee_id' => $movement_benefit->employee_id,
					'benefit_id' => $movement_benefit->benefit_id, 
					'value' => $movement_benefit->value, 
					'updated_date' => date("Y-m-d H:i:s") 
					);

				$this->db->update('employee_benefit', $data, array('benefit_id' => $movement_benefit->benefit_id, 'employee_id' => $request->employee_id, 'deleted' => 0));
			}

			$this->db->update('employee_benefit', array('deleted' => 1), array('employee_id' => $request->employee_id, 'deleted' => 0, 'value' => null));
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	private function _employee_update_movement($request, $employee)
	{
		$updates = array();
		$movement_types = explode(',', $request->employee_movement_type_id);
		
		// movement_type_id == 'Regularization'
		if (in_array(1, $movement_types)) {
			$this->db->set('status_id', 1);
			$this->db->where('employee_id', $request->employee_id);
			
			$this->db->update('employee');
			
			$today = new DateTime();
			$employed_date = new DateTime($employee->employed_date);
			// 5.2 On the 6th month, earning at 0.84 per month and to be earned every first day of the month.
			$hired_date_diff = $employed_date->diff($today);				
			
			if ($hired_date_diff->y > 1 && $employee->employee_type != 1) {
				$new_value = $employee->{$appcode} + $employee->accumulation;
			}

			// Add leave credits, use leave setup based on employee type.
			$this->db->select('et.*, ef.application_code');
			$this->db->from('employee_type_leave_setup et');
			$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
			$this->db->where('employee_type_id', $employee->employee_type);
			$this->db->where('ef.deleted', 0);
			
			$leave_setup = $this->db->get();

			if ($leave_setup->num_rows() > 0) {
				$data['year'] = date('Y');
				foreach ($leave_setup->result() as $leave_type) {					
					if (!$leave_type->prorated) {
						$data[strtolower($leave_type->application_code)] = $leave_type->base;
					} else {
						// Formula for pro-rated
						$monthly = $leave_type->base / 12;
						//$curr_month_credits = $monthly * ((date('t') - date('d')) / date('t'));
						//$year_remaining_credits = $monthly * (12 - date('n'));
						//$credits = $curr_month_credits + $year_remaining_credits;	
						//$credits = round($credits * 2) / 2;											
						$credits = $monthly * 6; //6 months is considered as regular.

						$data[strtolower($leave_type->application_code)] = $credits;
					}
				}
			}

			$data['employee_id'] = $request->employee_id;

			return $this->db->insert('employee_leave_balance', $data);					
		}

		// movement_type_id == 'Promotion'
		if (in_array(3, $movement_types)) {
			$this->db->set('employee_type', $request->employee_type);
			$this->db->where('employee_id', $request->employee_id);
			
			$this->db->update('employee');
			
			// Add leave credits, use leave setup based on employee type.
			$this->db->select('et.*, ef.application_code');
			$this->db->from('employee_type_leave_setup et');
			$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
			$this->db->where('employee_type_id', $request->employee_type);
			$this->db->where('ef.deleted', 0);
			
			$leave_setup = $this->db->get();			

			if ($leave_setup->num_rows() > 0) {
				$data['year'] = date('Y');
				foreach ($leave_setup->result() as $leave_type) {
					if (!$leave_type->prorated) {
						$data[strtolower($leave_type->application_code)] = $leave_type->base;
					} else {						
						// Formula for pro-rated
						$monthly = $leave_type->base / 12;
						$curr_month_credits = $monthly * ((date('t') - date('d')) / date('t'));
						$year_remaining_credits = $monthly * (12 - date('n'));
						$credits = $curr_month_credits + $year_remaining_credits;
						$credits = round($credits * 2) / 2;

						$data[strtolower($leave_type->application_code)] = $credits;
					}
				}
			}

			$data['employee_id'] = $request->employee_id;

			return $this->db->insert('employee_leave_balance', $data);			
		}

		return;		
	}

	// ------------------------------------------------------------------------
	/**
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m reset_leaves
	 * 
	 * @return [type] [description]
	 */
	private function _reset_leaves()
	{
		if( !isset($_POST['year']) && $this->uri->rsegment(3) ) $_POST['year'] = $this->uri->rsegment(3);
		$year = $this->input->post('year');
		
		$qry = "SELECT a.*, b.sex
		FROM {$this->db->dbprefix}employee a
		LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
		WHERE b.deleted = 0";
		$employees = $this->db->query($qry);
		
		if($employees->num_rows() > 0){
			$this->db->delete('employee_leave_balance', array('year' => $year));
			
			//common
			$data['year'] = $year;
			$data['sl'] = 15;
			$data['el'] = 5;

			foreach($employees->result() as $employee){
				$data['employee_id'] = $employee->employee_id;
				switch($employee->employee_type){
					case 1:
						$data['vl'] = 20;
						break;
					case 2:
						$data['vl'] = 15;
						break;
					default:
						$data['vl'] = 15;
						break;

				}

				switch($employee->sex){
					case 'male':
						$data['mpl'] = 0;
						break;
					case 'female':
						$data['mpl'] = 60;
						break;	
				}
				$this->db->insert('employee_leave_balance', $data);
			}	

		}

	}

	// ------------------------------------------------------------------------

	/**
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m ut_notifier
	 * 
	 * @return [type] [description]
	 */
	// private function _ut_notifier()
	// {
	// 	$this->load->helper('time_upload');

	// 	$offenders = array();

	// 	$curr_date = date('Y-m-d');
	// 	$day = strtolower(date('l', strtotime($curr_date)));		
	// 	// Get all employees with dtr, to loop for workschedule.
	// 	$this->db->select('
	// 		employee_dtr.*,
	// 		employee_dtr.employee_id,
	// 		employee_cws.shift_id cws,
	// 		employee_dtr_setup.shift_calendar_id shift, 
	// 		workschedule_employee.shift_calendar_id gws,
	// 		CONCAT(user.firstname, " ", user.lastname) name',
	// 		false);
	// 	$this->db->join('employee_dtr_setup', 'employee_dtr.employee_id = employee_dtr_setup.employee_id', 'left');		
	// 	$this->db->join('workschedule_employee', 
	// 		'workschedule_employee.employee_id = employee_dtr.employee_id AND \'' . $curr_date . '\' BETWEEN '. $this->db->dbprefix . 'workschedule_employee.date_from AND '. $this->db->dbprefix . 'workschedule_employee.date_to', 
	// 		'left');
	// 	$this->db->join('employee_cws', 
	// 		'employee_cws.employee_id = employee_dtr.employee_id AND \'' . $curr_date . '\' BETWEEN '. $this->db->dbprefix . 'employee_cws.date_from AND '. $this->db->dbprefix . 'employee_cws.date_to',
	// 		'left');
	// 	$this->db->join('user user', 'user.employee_id = employee_dtr.employee_id', 'left');
	// 	$this->db->where('employee_dtr.date', $curr_date);
	// 	$this->db->where('employee_dtr.deleted', 0);

	// 	$emp_with_dtr = $this->db->get('employee_dtr');

 //        if ($emp_with_dtr && $emp_with_dtr->num_rows() > 0) {
 //            $emp_with_dtr = $emp_with_dtr->result();
 //            foreach ($emp_with_dtr as $emp_dtr) {
 //                $recipients = array();                
 //                if (!is_null($emp_dtr->cws)) {
 //                    $this->db->where('timekeeping_shift.shift_id', $emp_dtr->cws);
 //                    $schedule = $this->db->get('timekeeping_shift');
 //                } else {
 //                    if (!is_null($emp_dtr->gws)) {
 //                        $calendar_id = $emp_dtr->gws;
 //                    } else {
 //                        $calendar_id = $emp_dtr->shift;
 //                    }

 //                    $this->db->where('timekeeping_shift_calendar.shift_calendar_id', $calendar_id);
 //                    $this->db->join('timekeeping_shift', 'timekeeping_shift.shift_id = timekeeping_shift_calendar.' . $day . '_shift_id');

 //                    $schedule = $this->db->get('timekeeping_shift_calendar');
 //                }

 //                if ($schedule && $schedule->num_rows() > 0) {
 //                    $schedule = $schedule->row();
 //                    // Check for undertime.
 //                    $undertime = (strtotime($schedule->shifttime_end) - strtotime(date('H:i:s', strtotime($emp_dtr->time_out1)))) / 60;

 //                    $out = get_form($emp_dtr->employee_id, 'out', null, $curr_date);

 //                    if ($out->num_rows() == 0 && $undertime > 0) {
 //                        $offenders[]['employee'] = $emp_dtr->name;

 //                        $result = $this->db->get_where('user',array('user_id'=>$emp_dtr->employee_id));
 //                        if ($result && $result->num_rows() > 0){
 //                            $single_row = $result->row();
 //                            if ($single_row->email != ''){
 //                                $recipients[] = $single_row->email;
 //                            }

 //                            $immediate_superior = $this->system->get_reporting_to($emp_dtr->employee_id);
 //                            if ($immediate_superior){
 //                                $result = $this->db->get_where('user',array('user_id'=>$immediate_superior));
 //                                if ($result && $result->num_rows() > 0){
 //                                    $single_row = $result->row();
 //                                    if ($single_row->email != ''){
 //                                        $recipients[] = $single_row->email;
 //                                    }               
 //                                }
 //                            }

	// 				    	$dtr_notification_settings = $this->hdicore->_get_config('dtr_notification_settings');

	// 						$emailto_list = array();
	// 						$emailcc_list = array();

	// 						foreach( $dtr_notification_settings['email_to'] as $email_to_settings ){
	// 							$recipients[] = $email_to_settings['email'];
	// 						}

	// 						foreach( $dtr_notification_settings['email_cc'] as $email_cc_settings ){
	// 							$emailcc_list[] = $email_cc_settings['email'];
	// 						}

	// 						$email_cc_recipient = implode(', ',$emailcc_list);

 //                            if (count($recipients) > 0){
 //                                $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
 //                                if ($mail_config) {
 //                                    // Load the template.            
 //                                    $this->load->model('template');
 //                                    // $template = $this->template->get_module_template(62, 'daily_undertime_notification_email');
 //                                    $ut_employees[] = $emp_dtr->name;
 //                                    $date_email = $emp_dtr->date;
 //                                    $recipients_email = implode(',', $recipients);
 //                                    // $message = $this->template->prep_message($template['body'], $request);                                                                      
 //                                    // $this->template->queue(implode(',', $recipients), $email_cc_recipient, $template['subject'], $message);
 //                                }                        
 //                            }
 //                        }                        
 //                    }
 //                }
 //            }

 //            $employee_email = "";
 //            if(count($ut_employees))
 //            {
	//             foreach($ut_employees as $ut_employee)
	//             	$employee_email .=  "<li> ".$ut_employee."</li>"; //"<tr><td>".$ut_employee."</tr></td>"; 

	//             $request['employee'] = $employee_email;
	//             $request['date'] = date($this->config->item('display_date_format'), strtotime($date_email));

	//             $template = $this->template->get_module_template(62, 'daily_undertime_report');
	//             $message = $this->template->prep_message($template['body'], $request);                                                                      
	//             if($this->template->queue(implode(',', $recipients), $email_cc_recipient, $template['subject'], $message))
	//             	echo 'E-Mail Sent';
	//         }

 //            // if (count($offenders) > 0) {
 //            //     $this->_notify_hr($offenders, 'daily_undertime_report');
 //            // }
 //        }      
	// }

	// ------------------------------------------------------------------------

	/*
	 * [optional] Added $additional_recipients. to add recipient that is not hr or not set on dtr_notification_settings. @Jr
	 */
	function _notify_hr($offenders, $template, $additional_recipients = array())
	{
        $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {            
            $module = $this->hdicore->get_module('daily_time_record');
            // Load the template.  
            $this->load->model('template');

            $params = array(
            	'date' => date('Y-m-d'),
            	'employees' => $offenders
            	);

            $template = $this->template->get_module_template(0, $template);
            $message = $this->template->prep_message($template['body'], $params, true, true);
            $dtr_notification_settings = $this->hdicore->_get_config('dtr_notification_settings');

			$emailto_list = array();
			$emailcc_list = array();

			foreach( $dtr_notification_settings['email_to'] as $email_to_settings ){
				$recepients[] = $email_to_settings['email'];
			}

			foreach( $dtr_notification_settings['email_cc'] as $email_cc_settings ){
				$emailcc_list[] = $email_cc_settings['email'];
			}

			$email_cc_reciepient = implode(', ',$emailcc_list);

            $recepients[] = $this->config->item('recepient');

            if(count($additional_recipients) > 0)
            {
            	foreach($additional_recipients as $additional_recipient)
            		$recepients[] = $additional_recipient;
            }

        	if ($this->template->queue(implode(',', $recepients), $email_cc_reciepient, $template['subject'], $message)) {
           		print('sent');
           	}
        }		
	}

	// ------------------------------------------------------------------------

	public function awol_notifier() {$this->_awol_notifier();}
	public function ut_notifier() {$this->_ut_notifier();}
	public function yearly_leave_earning() {$this->_monthly_leave_earning();}
	public function early_yearly_earning() {$this->_early_yearly_leave_accumulation();}

	/**
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m awol_notifier
	 * 
	 * @return [type] [description]
	 */
	private function _awol_notifier()
	{				
		$awol_employees = array();

		$this->db->select('CONCAT(u.firstname, " ", u.lastname) name, employee_dtr.id, employee_dtr.employee_id, u.email, employee_dtr.date', false);
		$this->db->where('send_awol_notification', 1);
		$this->db->where('awol_notification_sent', 0);
		$this->db->where('employee_dtr.deleted', 0);
		$this->db->from('employee_dtr');
		$this->db->join('user u', 'employee_dtr.employee_id = u.user_id', 'left');

		$emp_wo_in = $this->db->get();

		if ($emp_wo_in->num_rows() == 0) {			
			return;
		} else {			

			$emp_wo_in = $emp_wo_in->result();
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
					
					$emp_names[] = $employee->name;
					$dtr_id[] = $employee->id;
					$user_id[] = $employee->employee_id;

					
					// email for immediate superior and offender
					unset($recipients);
					$immediate_superior = $this->system->get_reporting_to($employee->employee_id);
			        if ($immediate_superior){
			            $result = $this->db->get_where('user',array('user_id'=>$immediate_superior));
			            if ($result && $result->num_rows() > 0){
			                $single_row = $result->row();
			                if ($single_row->email != ''){
			                    $recipients[] = $single_row->email;
			        			// $this->send_email_offender_and_superior($employee->name, 'daily_awol_report_for_immediate_superior', $recipients);
			                }               
			            }
			        }

			      	$recipients[] = $employee->email;
			        // $this->send_email_offender_and_superior($employee->name, 'daily_awol_report_for_offender', $recipients);

		    	}

			}
		}

		if (count($emp_names) > 0) {	
			//create incident report for awol
			foreach( $user_id as $employee_id ){


			 	$approvers = $this->system->get_approvers_and_condition($employee_id,115);

	            //$approvers = $this->system->get_approvers($this->userinfo['position_id'], $this->module_id);
	            $approver_array = array();

	            foreach($approvers as $approver){
	                array_push($approver_array, $approver['approver']);
	            }

				$approver_list = implode(',', $approver_array);

				$data = array(
					'ir_status_id' => 6,
					'offence_id' => 2,
					'complainants' => 2,
					'involved_employees' => $employee_id,
					'details' => 'Absence without leave (AWOL) for five (5) consecutive days.',
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

			}

			
			//$this->db->where_in('id', $dtr_id);
			//$this->db->update('employee_dtr', array('awol_notification_sent' => true));


			foreach($emp_names as $emp_name)
				 $emp_offender .= "<li>".$emp_name."</li>";

			$awol_employees['employees']['employee'] = $emp_offender;

			// email for hr
		    // $this->_notify_hr($awol_employees, 'daily_awol_report_hr', $added_recipients);
		}
	}

	/*
	 * send email on superior and offender
	 *
	 */
	function send_email_offender_and_superior($offender, $template, $recipients = array())
	{
		if($recipients > 0)
		{
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
	        if ($mail_config) {
	        	// Load the template.            
	            $this->load->model('template');
	            $template = $this->template->get_module_template(0, $template);

	            // foreach($offenders as $offender)
	            $request['employee'] = $offender;

	            $request['date'] = date('Y-m-d');

	            $message = $this->template->prep_message($template['body'], $request, true, true);                                                                      
	            $this->template->queue(implode(',', $recipients), $email_cc_recipient, $template['subject'], $message);

	        }     
	    }
	}

	// ------------------------------------------------------------------------	

	/**
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m import_from_ms
	 */
	private function _import_from_ms()
	{
		ini_set('memory_limit', '256M');
		$this->load->helper('time_upload');

		$this->db->where('location_id', 1);
		$this->db->where('deleted', 0);
		$location = $this->db->get('timekeeping_location')->row();

		$db2 = $this->load->database('ms_sql', TRUE);
		$sql = $location->query;

		$qry = $db2->query($sql);

		if( $qry->num_rows > 0 ){
			$msdata = $qry->result();
		}

		$qry->free_result();

		$biometric_array = array();
		$insert_array = array();

		// create log
		$folder = 'logs/biometric_logs';
		if(!file_exists($folder)) 
			mkdir($folder, 0777, true);

		$log_file = $folder.'/'.date('Ymd_Gis').'.txt';

		$not_exist_biometrics = array();

		$records_read = 0;

		$log_msg = date('Ymd H:i:s')." START UPLOADING \r\n";

		$log_msg .= $qry->num_rows()." record(s) read. \r\n";

		foreach ($msdata as $row) {
			$row->processed = 0;

			if (!array_key_exists($row->employee_id, $biometric_array)) {
				$this->db->where('biometric_id', $row->employee_id);
				$this->db->where('resigned', 0);
				$this->db->where('deleted', 0);
		
				$employee = $this->db->get('employee');

				if ($employee->num_rows() > 0) {
					$biometric_array[$row->employee_id] = $employee->row()->employee_id;
				} else {
					$biometric_array[$row->employee_id] = '';

					if(!in_array($biometric_id, $not_exist_biometrics))
						$not_exist_biometrics[] = $biometric_id;
				}
			}

			$row->employee_id = $biometric_array[$row->employee_id];			

			//check if data exists
			$where = array(
				'employee_id' => $row->employee_id,
				'checktime' => $row->checktime,
				'checktype' => $row->checktype
			);
			$data = $this->db->get_where('employee_dtr_raw', $where);
			if($data->num_rows() == 0) $insert_array[] = (array) $row;
		}

		$log_msg .= implode(" Does not exist \r\n", $not_exist_biometrics);

		$log_msg .= "END UPLOADING";

		write_file($log_file, $log_msg, 'w+');

		if(  sizeof($insert_array) > 0) $this->db->insert_batch('employee_dtr_raw', $insert_array);

		if ($this->db->_error_message() == '') {
			print ('Running...');

			if ($this->config->item('client_no') == 1){
				process_time_raw(1, 0);
			}
			else if ($this->config->item('client_no') == 2){
				print ('Executing process_time_raw...');
				process_time_raw_oams(1, 0);
			}				
			
		}
		else{
			print ('ERROR: ' . $this->db->_error_message() . "\n");			
		}
	}	

	// ------------------------------------------------------------------------	

	/**
	 * Run every first day of month to increment leaves.
	 * 
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m import_from_ms
	 */
	private function _monthly_leave_earning() {
		// Get employee types with monthly leave accumulation.	

		$query = '
			SELECT 
				e.employee_id, 
				employee_type, 
				es.*, 
				ef.application_code, 
				eb.*, 
				e.regular_date			
			FROM hr_employee e
			LEFT JOIN '.$this->db->dbprefix.'employee_type_leave_setup es ON es.employee_type_id = e.employee_type
			LEFT JOIN '.$this->db->dbprefix.'employee_form_type ef ON ef.application_form_id = es.application_form_id
			LEFT JOIN '.$this->db->dbprefix.'employee_leave_balance eb ON eb.employee_id = e.employee_id
			WHERE 
				es.accumulation_type_id = '.($this->config->item('client_no') == 2 ? 1 : 4).' AND es.deleted = 0 AND eb.deleted = 0
				AND e.deleted = 0 AND e.status_id = 1';		

		if ($this->config->item('client_no') == 2)
			$query .= ' AND DATE_ADD(DATE(e.employed_date + INTERVAL 6 MONTH), INTERVAL 1 DAY) <= CURDATE()'; // with interval 6 days AND DATE_ADD(DATE(e.employed_date + INTERVAL 6 MONTH), INTERVAL 6 DAY) <= CURDATE()';

		if($this->config->item('accumulate_with_carried') == 1)
			$query .= ' AND eb.year = ' . date('Y');

		$query .= ' ORDER BY e.employee_id DESC ';

		$result = $this->db->query($query);

		if ($result->num_rows() == 0) {
			print "Nothing to update.";			
		} else {			
			$employees = $result->result();

			$today = new DateTime();

			foreach ($employees as $employee) {		

				$appcode = strtolower($employee->application_code);
				$new_value = $employee->{$appcode};

				$regular_date = new DateTime($employee->regular_date);
				// 5.2 On the 6th month, earning at 0.84 per month and to be earned every first day of the month.
				$reg_date_diff = $regular_date->diff($today);

				if ($this->config->item('client_no') == 1){
					if ($reg_date_diff->y > 1 || $reg_date_diff->m >= 6) {
						$new_value = $employee->{$appcode} + $employee->accumulation;
					}
				}
				elseif ($this->config->item('client_no') == 2){
					if(date('Y-m') != date('Y-m', strtotime($employee->regular_date)))
					{
						$new_value = $employee->{$appcode} + $employee->accumulation;
					}
				}

				if ($this->config->item('client_no') == 2){
					// 5.3 No attendance for two weeks, no earning
					if(date('Y-m', strtotime('+ 1 month '.$employee->regular_date)) != date('Y-m'))
					{
						if(date('Y-m') != date('Y-m', strtotime($employee->regular_date)))
						{
							$no_attendance_two_weeks = $this->_no_attendance_two_weeks($employee->employee_id);

							if($no_attendance_two_weeks){
								$new_value = $new_value - ($employee->accumulation);
							}
						}
					}
				}
				
				// 5.4 At 30 days vacation leave credits, earning stops.
				if ($this->config->item('client_no') == 1){
					if ($appcode == 'vl' && $new_value > 30) {
						$new_value = 30;
					}
				}
				elseif ($this->config->item('accumulate_with_carried') == 1){
					if ( 
						 ((($new_value - $employee->vl_used) + $employee->carried_vl) > 30 && $appcode == 'vl') 
						 || 
						 ((($new_value - $employee->sl_used) + $employee->carried_sl) > 30 && $appcode == 'sl') 
						) {

						$carried_field = 'carried_'.$appcode;
						$used_field = $appcode.'_used';
						$current_new_value = (30 + $employee->{$used_field}) - $employee->{$carried_field};
						$new_value = $current_new_value;
					}					

					if(($new_value + $employee->carried_vl) > 28 && $appcode == 'vl')
						$this->_send_email_near_maximum_leave($employee->employee_id, ($new_value + $employee->carried_vl));
				}

				// Add how much credits.
				$this->db->set($appcode, $new_value, false);
				$this->db->where('year', date('Y'));
				$this->db->where('employee_id', $employee->employee_id);
				// $this->db->where('leave_balance_id', $employee->leave_balance_id);
				$this->db->update('employee_leave_balance');

			}


		}
		return;
	}

	private function _send_email_near_maximum_leave($emp_id, $leave_val)
	{
		$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
        	// Load the template.            
            $this->load->model('template');
            $template = $this->template->get_module_template(0, 'near_max_credits');

            $user = $this->db->get_where('user', array('employee_id' => $emp_id))->row_array();

            $request['employee'] = $user['firstname']." ".$user['middlename']." ".$user['lastname'];
            $request['leave_val'] = $leave_val;
            $request['date'] = date('Y-m-d');

            $recipients = $user['email'];

            $message = $this->template->prep_message($template['body'], $request, true, false);                                                                      
            
            $this->template->queue($recipients, '', $template['subject'], $message);

	    }     
	}

	function send_near_end_date(){
		$this->_send_near_end_date();
	}
	/**
	 * Run every first day of month to send with the hr.
	 * 
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m send_near_end_date
	 */
	private function _send_near_end_date(){
		$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {           
	    	$date_from = date('Y-m-01');
	    	$date_to = date('Y-m-t',strtotime ('+1 month',strtotime($date_from)));

	    	$qry = "SELECT * FROM {$this->db->dbprefix}user u
	    			INNER JOIN {$this->db->dbprefix}employee e
	                ON u.employee_id = e.employee_id 
	    			WHERE e.end_date between '".$date_from."' and '".$date_to."'";

	        $result = $this->db->query($qry);

	        $html = '<table border="1" style="border-collapse:collapse" cellpadding="5">
                        <tr>
                            <td align="center">Name</td>
                            <td align="center">End Date</td>
                            <td align="center">Days Till End Date</td>
                        </tr>';
	                    if ($result && $result->num_rows() > 0){
	                        foreach ($result->result() as $row) {
	                        	$secs = strtotime($row->end_date) -  strtotime($date_from);
	                        	$days = $secs / 86400;

	                            $html .= '<tr>
	                                        <td>'.$row->firstname.'&nbsp'.$row->lastname.'</td>
	                                        <td>'.date($this->config->item("display_date_format"),strtotime($row->end_date)).'</td>
	                                        <td>'.$days.' days to go</td>
	                                    </tr>';
	                        }
	                    }

	        $html .= '</table>';        

        	// Load the template. 
			$this->load->model('template');

	        $template = $this->template->get_module_template(0, 'list_employee_with_end_date');

			$recepients[] = $this->config->item('recepient');

	        $request['month_from'] = date($this->config->item("display_date_format"),strtotime($date_from));
	        $request['month_to'] = date($this->config->item("display_date_format"),strtotime($date_to));	                
	        $request['employees'] = $html;

	        $message = $this->template->prep_message($template['body'], $request);
	        $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);
	    }   		
	}
	// ------------------------------------------------------------------------	

	/**
	 * Run every first day of december every year to get the credits for the next year. It will only allow 2 credits
	 * 
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m early_yearly_leave_accumulation
	 */
	private function _early_yearly_leave_accumulation() {
		// Get employee types with monthly leave accumulation.	

		$query = '
			SELECT 
				e.employee_id AS emp_id, e.status_id, es.accumulation_type_id, employee_type, es.*, 
				ef.application_code, eb.*, e.regular_date, e.employed_date, e.employee_type
			FROM hr_employee e
			LEFT JOIN '.$this->db->dbprefix.'employee_type_leave_setup es ON es.employee_type_id = e.employee_type
			LEFT JOIN '.$this->db->dbprefix.'employee_form_type ef ON ef.application_form_id = es.application_form_id
			LEFT JOIN '.$this->db->dbprefix.'employee_leave_balance eb ON eb.employee_id = e.employee_id
			WHERE 
				es.accumulation_type_id = '.($this->config->item('client_no') == 2 ? 1 : 4).' AND es.deleted = 0
				AND e.deleted = 0 			
				AND e.resigned=0';
		if($this->config->item('accumulate_with_carried') == 1 || $this->config->item('hdi_carried_leave') == 1)
			$query .= ' AND eb.year = YEAR(NOW()) '; //' AND eb.year = YEAR(NOW()) ';
		$query .= ' ORDER BY e.employee_id DESC ';

		$result = $this->db->query($query);

		if ($result->num_rows() == 0) {
			print "Nothing to update.";			
		} else {			

			$employees = $result->result();

			$end_of_year = new DateTime(date('Y-m-d', mktime( 0,0,0,12,31,date('Y'))));

			foreach ($employees as $employee) {		

				$appcode = strtolower($employee->application_code);

				if( $appcode == 'pl' || $appcode == 'ml' ){
					$appcode = 'mpl';
				}

				$leave_credits = $employee->{$appcode};
				$previous_accumulated_fields = 0;
				$to_be_added_credits = 0;
				$new_value = $leave_credits;

				if ($this->config->item('accumulate_with_carried') == 1){

					if( $employee->status_id == 1 ){

						if( $employee->application_code == "VL" ){
							$appcode = "carried_vl";
							$new_value = ($employee->carried_vl + $employee->vl) - $employee->vl_used;
						}

						if( $employee->application_code == "SL" ){
							$appcode = "carried_sl";
							$sl_balance = $employee->sl - $employee->sl_used;
							if($sl_balance >= 7)
							{
								// process to add paid leaves on next year
								$next_year = date('Y', strtotime('+1 year', strtotime(date('Y'))));
								$sql_results = $this->db->get_where('employee_leave_balance',array('year'=>$next_year,'employee_id'=>$employee->emp_id));

								if( $sql_results->num_rows() > 0 ){
									$record = $sql_results->row();
									$this->db->set('paid_sl', 5);
									$this->db->where('leave_balance_id', $record->leave_balance_id);
									$this->db->update('employee_leave_balance');
								} else {
									$data = array(
										'year'=>$next_year,
										'employee_id'=> $employee->emp_id,
										'paid_sl' => 5,
										'deleted' => 0
									);
									$this->db->insert('employee_leave_balance',$data);
								}
								
								$new_value = (($employee->carried_sl - 5) + $employee->sl) - $employee->sl_used;
									
							} else
								$new_value = ($employee->carried_sl + $employee->sl) - $employee->sl_used;
						}
					}

				} elseif ($this->config->item('accumulate_with_carried') == 0){

					if( $employee->status_id == 1 ){

						if( $employee->employee_type == 1 ){

						//For Pioneer
							$new_value = $employee->base;

						}
						else{

							$regular_date = new DateTime($employee->employed_date);
							$reg_date_diff = $regular_date->diff($end_of_year);

							$employed_date_year = date('Y', strtotime($employee->employed_date));

							if( $reg_date_diff->y >= 1 ){

								$new_value = $employee->base;

							}elseif ($next_year - $employed_date_year == 1 && CLIENT_DIR == "pioneer") { // rank and file employee type

								$date_diff = gregoriantojd(12, 31, date('Y', strtotime($employee->employed_date))) - gregoriantojd(date('m', strtotime($employee->employed_date)), date('d', strtotime($employee->employed_date)), date('Y', strtotime($employee->employed_date)));
								if (!$employee->prorated) {
									$new_value = $employee->base;
								}else{

									// Formula for pro-rated
									$monthly = $employee->base / 365;
									$days_remaining = $monthly * $date_diff;
									$credits = round($days_remaining * 2) / 2;
									$new_value = $credits;
								}
								
							}
							else{

								$new_value = 0.0;

							}

						}

					}
					else{

						$new_value = 0.0;

					}

				}

				if($this->config->item('earn_using_leave_credit_table') == 1)
				{
					// do not forget to add multiple config here.
					if( $employee->status_id == 1 ){
						$regular_date = new DateTime($employee->employed_date);
						$reg_date_diff = $regular_date->diff($end_of_year);

						if($reg_date_diff->y >= 1) {

							$this->db->order_by('tenure', 'DESC');
							$etlc = $this->db->get_where('employee_type_leave_credit', array('leave_setup_id' => $employee->leave_setup_id, 'leave_type' => $employee->application_form_id, 'tenure <=' => $reg_date_diff->y));

							if($etlc && $etlc->num_rows() > 0)
								$new_value = $etlc->row()->leave_accumulated;
						}
					}

				}

				// Add how much credits.

				$next_year = date('Y', strtotime('+1 year', strtotime(date('Y'))));

				$sql_results = $this->db->get_where('employee_leave_balance',array('year'=>$next_year,'employee_id'=>$employee->emp_id,'deleted'=>0));

				if( $sql_results->num_rows() > 0 ){
					$record = $sql_results->row();
					$this->db->set($appcode, $new_value, false);
					$this->db->where('leave_balance_id', $record->leave_balance_id);
					$this->db->update('employee_leave_balance');

					$leave_bal_id = $record->leave_balance_id;
				}
				else{
					$data = array(
						'year'=>$next_year,
						'employee_id'=> $employee->emp_id,
						$appcode => $new_value,
						'deleted' => 0
					);
					$this->db->insert('employee_leave_balance',$data);

					$leave_bal_id = $this->db->insert_id();
				}

				if($this->config->item('hdi_carried_leave') == 1 && ($employee->carried_vl > 0 || $employee->carried_sl > 0))
				{
					$carried_vl = 0; 
					$carried_sl = 0;

					if( $employee->application_code == "VL") {
						$carried_vl = (($employee->vl - $employee->vl_used) < 0 ? $employee->carried_vl + ($employee->vl - $employee->vl_used) : $employee->carried_vl);
						$this->db->update('employee_leave_balance', array('carried_vl' => $carried_vl), array('leave_balance_id' => $leave_bal_id, 'year' => $next_year));
					}

					if( $employee->application_code == "SL" ) {
						$carried_sl = (($employee->sl - $employee->sl_used) < 0 ? $employee->carried_sl + ($employee->sl - $employee->sl_used) : $employee->carried_sl);
						$this->db->update('employee_leave_balance', array('carried_sl' => $carried_sl), array('leave_balance_id' => $leave_bal_id, 'year' => $next_year));
					}
				}
			}
		}
		
		return;
	}

public function rf_early_yearly_earning() {
	
		$query = 'SELECT 
				e.employee_id AS emp_id, e.status_id, es.accumulation_type_id, employee_type, es.*, 
				ef.application_code, eb.*, e.regular_date, e.employed_date, e.employee_type
				FROM hr_employee e
				LEFT JOIN '.$this->db->dbprefix.'employee_type_leave_setup es ON es.employee_type_id = e.employee_type
				LEFT JOIN '.$this->db->dbprefix.'employee_form_type ef ON ef.application_form_id = es.application_form_id
				LEFT JOIN '.$this->db->dbprefix.'employee_leave_balance eb ON eb.employee_id = e.employee_id
				WHERE 
					es.accumulation_type_id = 4 
					AND es.deleted = 0
					AND e.deleted = 0 			
					AND e.resigned=0     
					AND e.status_id = 1
					AND e.employee_type != 1
					AND ((YEAR(CURDATE())+1) - (YEAR(employed_date)) = 1 )';

		if($this->config->item('accumulate_with_carried') == 1 || $this->config->item('hdi_carried_leave') == 1)
			$query .= ' AND eb.year = YEAR(NOW()) '; //' AND eb.year = YEAR(NOW()) ';
		$query .= ' ORDER BY e.employee_id DESC ';

		$result = $this->db->query($query);
		dbug($query);die();
		if ($result->num_rows() == 0) {
			print "Nothing to update.";			
		} else {			

			$employees = $result->result();

			$end_of_year = new DateTime(date('Y-m-d', mktime( 0,0,0,12,31,date('Y'))));

			foreach ($employees as $employee) {		

				$appcode = strtolower($employee->application_code);

				if( $appcode == 'pl' || $appcode == 'ml' ){
					$appcode = 'mpl';
				}

				$leave_credits = $employee->{$appcode};
				$previous_accumulated_fields = 0;
				$to_be_added_credits = 0;
				$new_value = $leave_credits;
				
				if ($this->config->item('accumulate_with_carried') == 0){
							$regular_date = new DateTime($employee->employed_date);
							$reg_date_diff = $regular_date->diff($end_of_year);

							$employed_date_year = date('Y', strtotime($employee->employed_date));

							if( $reg_date_diff->y >= 1 ){

								$new_value = $employee->base;

							}elseif ($next_year - $employed_date_year == 1 && $employee->employee_type == 3) { // rank and file employee type

								
								$date_diff = gregoriantojd(12, 31, date('Y', strtotime($employee->employed_date))) - gregoriantojd(date('m', strtotime($employee->employed_date)), date('d', strtotime($employee->employed_date)), date('Y', strtotime($employee->employed_date)));
								if (!$employee->prorated) {
									$new_value = $employee->base;
								}else{
									// Formula for pro-rated
									$monthly = $employee->base / 365;
									$days_remaining = $monthly * $date_diff;
									$credits = round($days_remaining * 2) / 2;
									$new_value = $credits;
								}
								
							}
							else{
								$new_value = 0.0;
							}
				}

				if($this->config->item('earn_using_leave_credit_table') == 1)
				{
					// do not forget to add multiple config here.
					if( $employee->status_id == 1 ){
						$regular_date = new DateTime($employee->employed_date);
						$reg_date_diff = $regular_date->diff($end_of_year);

						if($reg_date_diff->y >= 1) {

							$this->db->order_by('tenure', 'DESC');
							$etlc = $this->db->get_where('employee_type_leave_credit', array('leave_setup_id' => $employee->leave_setup_id, 'leave_type' => $employee->application_form_id, 'tenure <=' => $reg_date_diff->y));

							if($etlc && $etlc->num_rows() > 0)
								$new_value = $etlc->row()->leave_accumulated;
						}
					}
				}

				// Add how much credits.

				$next_year = date('Y', strtotime('+1 year', strtotime(date('Y'))));

				$sql_results = $this->db->get_where('employee_leave_balance',array('year'=>$next_year,'employee_id'=>$employee->emp_id,'deleted'=>0));

				if( $sql_results->num_rows() > 0 ){
					$record = $sql_results->row();
					$this->db->set($appcode, $new_value, false);
					$this->db->where('leave_balance_id', $record->leave_balance_id);
					$this->db->update('employee_leave_balance');
					$leave_bal_id = $record->leave_balance_id;
				}
				else{
					$data = array(
						'year'=>$next_year,
						'employee_id'=> $employee->emp_id,
						$appcode => $new_value,
						'deleted' => 0
					);
					$this->db->insert('employee_leave_balance',$data);
					$leave_bal_id = $this->db->insert_id();
				}

				if($this->config->item('hdi_carried_leave') == 1 && ($employee->carried_vl > 0 || $employee->carried_sl > 0))
				{
					$carried_vl = 0; 
					$carried_sl = 0;

					if( $employee->application_code == "VL") {
						$carried_vl = (($employee->vl - $employee->vl_used) < 0 ? $employee->carried_vl + ($employee->vl - $employee->vl_used) : $employee->carried_vl);
						$this->db->update('employee_leave_balance', array('carried_vl' => $carried_vl), array('leave_balance_id' => $leave_bal_id, 'year' => $next_year));
					}

					if( $employee->application_code == "SL" ) {
						$carried_sl = (($employee->sl - $employee->sl_used) < 0 ? $employee->carried_sl + ($employee->sl - $employee->sl_used) : $employee->carried_sl);
						$this->db->update('employee_leave_balance', array('carried_sl' => $carried_sl), array('leave_balance_id' => $leave_bal_id, 'year' => $next_year));
					}
				}
			}
		}
		
		return;
	}

	/**
	 * Run every first day of january every year to get the credits from previous year. It will only allow 2 credits
	 * 
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m yearly_leave_accumulation
	 */
	public function yearly_leave_accumulation() {
		// Get employee types with monthly leave accumulation.	

		$query = '
			SELECT 
				e.employee_id, e.status_id, es.accumulation_type_id, employee_type, es.*, 
				ef.application_code, eb.*, e.regular_date, e.employed_date, e.employee_type			
			FROM hr_employee e
			LEFT JOIN '.$this->db->dbprefix.'employee_type_leave_setup es ON es.employee_type_id = e.employee_type
			LEFT JOIN '.$this->db->dbprefix.'employee_form_type ef ON ef.application_form_id = es.application_form_id
			LEFT JOIN '.$this->db->dbprefix.'employee_leave_balance eb ON eb.employee_id = e.employee_id
			WHERE 
				es.accumulation_type_id = 4 AND es.deleted = 0
				AND e.deleted = 0 				
				AND eb.year = YEAR(CURRENT_DATE - INTERVAL 0 YEAR) 
				AND e.resigned = 0
			ORDER BY e.employee_id DESC ';

		$result = $this->db->query($query);

		if ($result->num_rows() == 0) {
			print "Nothing to update.";			
		} else {			

			$employees = $result->result();

			$end_of_year = new DateTime(date('Y-m-d', mktime( 0,0,0,12,31,date('Y'))));

			foreach ($employees as $employee) {		

				$appcode = strtolower($employee->application_code);
				$leave_credits = $employee->{$appcode};
				$previous_accumulated_fields = 0;
				$to_be_added_credits = 0;
				$new_value = $leave_credits;

				if ($this->config->item('client_no') == 2){

					if( $employee->status_id == 1 ){

						if( $employee->application_code == "SL" ){

							//If Sick Leave and current leave credits is greater than 6, 
							//deduct 5 credits for cash convertion, the rest will be accumulated for the next year
							if( $leave_credits >= 5 ){

								$new_value = $leave_credits - 5;
							}
						}
					}
				}
				elseif ($this->config->item('client_no') == 1){

					if( $employee->status_id == 1 ){

						if( $employee->employee_type == 1 ){

						//For Pioneer
							$new_value = $employee->base;

						}
						else{

							$regular_date = new DateTime($employee->employed_date);
							$reg_date_diff = $regular_date->diff($end_of_year);


							if( $reg_date_diff->y >= 1 ){

								$new_value = $employee->base;

							}
							else{

								$new_value = 0.0;

							}

						}

					}
					else{

						$new_value = 0.0;

					}
					

				}

				// Add how much credits.

				$result = $this->db->get_where('employee_leave_balance',array('year'=>date('Y'),'employee_id'=>$employee->employee_id));

				if( $result->num_rows() == 1 ){
					$record = $result->row();
					$this->db->set($appcode, $new_value, false);
					$this->db->where('leave_balance_id', $record->leave_balance_id);
					$this->db->update('employee_leave_balance');

				}
				else{
					$data = array(
						'year'=> date('Y'),
						'employee_id'=> $employee->employee_id,
						$appcode => $new_value,
						'deleted' => 0
					);

					$this->db->insert('employee_leave_balance',$data);

				}
				
			}
		}
		
		return;
	}

	// ------------------------------------------------------------------------	

	/**
	 * Add uniform order for employees for the current year
	 * 
	 */

	public function insert_uniform_orders(){

		$this->db->where('employee_type != 1');
		$this->db->where('deleted',0);
		$result = $this->db->get('employee');

		if ($result->num_rows() == 0) {
			print "Nothing to insert.";			
		} else {			

			$employees = $result->result();

			foreach($employees as $employee){

				$uniform_order = $this->db->get_where('employee_uniform_order',array('employee_id'=>$employee->employee_id,'year'=>date('Y')))->num_rows();

				if( $uniform_order == 0 ){

					$data = array(
						'year'=>date('Y'),
						'employee_id' => $employee->employee_id,
						'order_status_id' => 1,
						'date_ordered' => date('Y-m-d h:i:s')
					);

					$this->db->insert('employee_uniform_order',$data);

				}

			}

		}


	}


	// ------------------------------------------------------------------------	

	private function _no_attendance_two_weeks($emp_id)
	{
		$qry = "SELECT * FROM hr_employee_dtr
		WHERE YEAR(date) = YEAR(CURDATE() - INTERVAL 1 MONTH)
		AND MONTH(date) = MONTH(CURDATE() - INTERVAL 1 MONTH)
		AND employee_id = ".$emp_id."
		ORDER BY date";

		//print $emp_id;
		$emp_list = $this->db->query($qry)->result();

		$total_attendance = 0;

		foreach($emp_list as $aa)
		{
			// remove for now, because there is no point of checking if it is employee's restday
		 	// $hours=$this->system->get_employee_all_worksched($emp_id, $aa->date);
		 	// $shift_id = strtolower(date('l', strtotime($aa->date)))."_shift_id";

			if( $aa->awol == 0 && $aa->hours_worked > 0){
				$total_attendance++;
			}
		}

		// if total attendance is below two weeks or 10 days
		if( $total_attendance < 10 ){
			return true;
		}
		else{
			return false;
		}

	}

	public function upload_lotus_notes_public(){
		$this->_upload_lotus_notes();
	}
	/**
	 * 
	 * COMMAND: php cron.php -u superadmin -p <PASSWORD> -m upload_lotus_notes
	 */
	private function _upload_lotus_notes() {
		$dir = './uploads/time_keeping/3/';
		$destination = './uploads/time_keeping/3/uploaded/';
		if ($handle = opendir($dir)) {
		    while (false !== ($entry = readdir($handle))) {
				if ($entry != '.' && $entry != '..' && !is_dir($dir.$entry)){
					if(preg_match( '/\.txt$/', $entry)){
						$filename_arr[] = $entry;
					}
				}		        
		    }
		    closedir($handle);
		}
		print "File found." . "\n";
		if (count($filename_arr) > 0){
			print "Beginning upload." . "\n";
			foreach ($filename_arr as $filename) {
				$time_keeping_exist = $this->db->get_where('timekeeping_uploads', array('filename' => $filename));
				print "Processing " . $filename  . "\n";
				if ($time_keeping_exist){
					if($time_keeping_exist->num_rows() > 0) 
					{
						print "File previously uploaded, skipping file." . "\n";
						continue;
					}
				}

				$biometric_id_array = array();

				$ci=& get_instance();
				$location_id = 3;
				$user_id = $ci->userinfo['user_id'];
				$file_w_dir = $dir . $filename;

				$file = file($file_w_dir);
				if ($file != false)
				{
					$insert='';
					//for .txt lotus notes
					$ctr = 0;			
					$count = count($file) - 1;
					$date_R = array();
					foreach ($file as $index => $val)
					{
						$val_exp 		= explode(", ", $val);
						$biometric_id 	= $val_exp[0];

						// Store in array to prevent duplicate query for employee id.
						if (!array_key_exists($biometric_id, $biometric_id_array)) {
							$ci->db->where('biometric_id', $biometric_id);
							$ci->db->where('resigned', 0);
							$ci->db->where('deleted', 0);

							$employee = $ci->db->get('employee');

							if ($employee->num_rows() > 0) {
								$biometric_id_array[$biometric_id] = $employee->row()->employee_id;
							}
						} 
						
						$employee_id   = $biometric_id_array[$biometric_id];
						$date 		   = date("Y-m-d",strtotime($val_exp[1]));
						$timein 	   = date("G:i:s",strtotime($val_exp[3]));
						$timeout 	   = date("G:i:s",strtotime($val_exp[4]));
						$checktimein   = $date .' '. $timein;
						$checktimeout  = $date .' '. $timeout;
						$checktype_in  = "C/In";
						$checktype_out = "C/Out";
						$datetimein = $val_exp[3];
						$datetimeout = $val_exp[4];
						$unixtimein = strtotime( $datetimein );
						$unixtimeout = strtotime( $datetimeout );

						if ( FALSE == $unixtimein )
						{
						    $checktimein = '';
						}  

						if ( FALSE == $unixtimeout )
						{
						    $checktimeout = '';
						}  						

						if ($employee_id != '' && $date != '')
						{
							if($index == $count)
							{
								$insert .= "('$employee_id','{$date}','{$checktimein}','{$checktype_in}','{$location_id}'),";	
								$insert .= "('$employee_id','{$date}','{$checktimeout}','{$checktype_out}','{$location_id}')";	
							}
							else
							{
								$insert .= "('$employee_id','{$date}','{$checktimein}','{$checktype_in}','{$location_id}'),";	
								$insert .= "('$employee_id','{$date}','{$checktimeout}','{$checktype_out}','{$location_id}'),";
							}
							$date_R[] = strtotime($date);
							$ctr++;				
						}
					}
					
					if (count($date_R) > 0)
					{
						$lowest = date("Y-m-d",min($date_R));
						$highest = date("Y-m-d",max($date_R));
					}
					else
					{
						$lowest = 0;
						$highest = 0;			
					}

					if ($insert != ''){
						$qry_dtr = "INSERT INTO `{$ci->db->dbprefix}employee_dtr_raw` (`employee_id`, `date`, `checktime`, `checktype`, `location_id`) VALUES".$insert;
						$ci->db->query($qry_dtr);

						$date = date('Y-m-d H:i:s');				
						$qry_dtr = "INSERT INTO `{$ci->db->dbprefix}timekeeping_uploads` (`location_id`, `log_date_start`, `log_date_end`, `log_count`, `filename`, `created_date`,`created_by`) VALUES ('$location_id','{$lowest}','{$highest}','{$ctr}','{$filename}','{$date}','{$user_id}')";
						$ci->db->query($qry_dtr);
						$this->process_time_raw_lotus_notes($location_id,$user_id);
						if (copy($dir.$filename, $destination.$filename)) {
							$delete[] = $dir.$filename;
						}				
						print "End Upload."  . "\n";							
					}
				}
			}
		    foreach ($delete as $file) {
		    	unlink($file);
		    }			
		}
	}

	function process_time_raw_lotus_notes($location_id,$user_id)
	{
		$ci=& get_instance();

		$dtr_raw = $ci->db->get_where('employee_dtr_raw', array('location_id' => $location_id, 'processed' => 0));
		$o_dtr_raw = $dtr_raw->result();

		$e_dtr = array();	

		foreach ($o_dtr_raw as $dtr_entry) {		
			if ($dtr_entry->checktype == 'C/In') {
				$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['in'] = $dtr_entry->checktime;
			} else {
				$e_dtr[$dtr_entry->employee_id][$dtr_entry->date]['out'] = $dtr_entry->checktime;
			}		
		}
		
		foreach ($e_dtr as $employee_id => $employee_day_record) {
			foreach ($employee_day_record as $date => $e_dtr_entry) {	
				$ci->db->where('employee_id', $employee_id);
				$ci->db->where('date', $date);
				$ci->db->where('deleted', 0);

				$result = $ci->db->get('employee_dtr');

				$employee_dtr_row = array(
						'location_id' => $location_id,
						'date' => $date,
						'time_in1' => $e_dtr_entry['in'],
						'time_out1' => $e_dtr_entry['out'],
						'upload_by' => $user_id		
						);

				if ($result->num_rows() > 0) {
					$entry = $result->row();
					$ci->db->where('id', $entry->id);
					$ci->db->update('employee_dtr', $employee_dtr_row);
				} else {
					$employee_dtr_row['employee_id'] = $employee_id;
					$ci->db->insert('employee_dtr', $employee_dtr_row);
				}				
			}		
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
			print ('ERROR: ' . $this->db->_error_message() . "\n");
			print ('QUERY: ' . $this->db->last_query() . "\n");
		} else if ($results->num_rows() == 0) {
			print ('No results found.' . "\n");
		} else {
			$requests = $results->result();
			$complete = array();			

			foreach ($requests as $request) {
				print ('Processing request for: ' . $request->name . "\n");				

				$this->db->where('employee_id', $request->employee_id);
				$this->db->where('deleted', 0);
				
				$this->db->limit(1);

				$employee  = $this->db->get('employee');
				$completed = TRUE;

				if ($employee->num_rows() == 0) {
					if ($this->db->insert('employee', array('employee_id' => $request->employee_id, 'user_id' => $request->employee_id))) {
						print ("\t" . 'Employee record created.'. "\n");						
					} else {
						print ("\t" . 'Failed to create employee record. Aborting...'. "\n");
						exit();
					}
				}				

				// if ($this->config->item('client_number') == 3){
				// 	if ($request->compensation_effectivity_date <= $curr_date) {
				// 		if ($this->_employee_update_compensation($request)) {
				// 			print ("\t" . 'Compensation updated.' . "\n");
				// 		} else {
				// 			print ("\t" . 'Compensation update failed.' . "\n");
				// 			$completed = FALSE;
				// 		}
				// 	}
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
					if ($this->_employee_update_transfer($request, $employee->row())) {
						print ("\t" . 'Movement Processed.' . "\n");
						// $completed=true;
					} else {
						print ("\t" . 'Movement Processed failed.' . "\n");
						$completed = FALSE;
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
	}


	/*
	 * run every 1st of the month
	 */
	private function _due_for_ape(){
		$query_id = '9';
        $sql = "";
		$sql_string	= "";

		$this->db->where('export_query_id', $query_id);

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);
		$sql.= " WHERE ";

		$sql_string .= "MONTH(".$this->db->dbprefix."user.birth_date) = MONTH(CURDATE())";

		$result = $this->db->query($sql.$sql_string);

		if( $result->num_rows() > 0 ){
			foreach($result->result() as $row){
				//check for pending ape
				$where['deleted'] = 0;
				$where['employee_id'] = $row->user_id;
				$this->db->where($where);
				$this->db->where_in('health_type_status_id', array('1', '2'));
				$pending = $this->db->get('employee_health');
				if( $pending->num_rows() == 0 ){
					$health = array(
						'employee_id' => $row->user_id,
						'health_type' => 2,
						'health_type_status_id' => 2,
						'created_by' => 1
					);

					$this->db->insert('employee_health', $health);
				}
			}
		}
	}

	public function check_employee_log($ll)
	{
		$this->_check_employee_log_issue($ll);
	}
	
	private function _check_employee_log_issue($logdate=-1)
	{
		//echo $logdate.'<br>';
		$ldate = $logdate < 0 ? strtotime("{$logdate} day", strtotime(date('Y-m-d'))) : strtotime(date('Y-m-d'));
		$body = "";

		$prev_day = date('Y-m-d', strtotime( "-1 day", strtotime( date('Y-m-d') ) ) );

		//Get all employee present yesterday
		$this->db->select('*');
		$this->db->where('(( '.$this->db->dbprefix('employee_dtr').'.time_in1 IS NOT NULL AND '.$this->db->dbprefix('employee_dtr').'.time_out1 IS NOT NULL )) ');
		$this->db->where($this->db->dbprefix('employee_dtr').'.date',$prev_day);
		$this->db->where($this->db->dbprefix('employee_dtr').'.deleted','0');
		$present_result = $this->db->get($this->db->dbprefix('employee_dtr'));
		$total_employees_count = $present_result->num_rows();

/*		$this->db->where('date',date('Y-m-d', strtotime( "$logdate day", strtotime( date('Y-m-d') ) ) ));
		$this->db->where('deleted','0');
		$this->db->group_by('employee_id');
		$present_result = $this->db->get('employee_dtr');
		$total_employees_count = $present_result->num_rows();*/

		//Check if an employee has no time in or time out yesterday
		$this->db->select($this->db->dbprefix('employee').'.id_number, '.$this->db->dbprefix('user').'.firstname, '.$this->db->dbprefix('user').'.lastname, '.$this->db->dbprefix('employee_dtr').'.time_in1, '.$this->db->dbprefix('employee_dtr').'.time_out1');
		$this->db->where('(( '.$this->db->dbprefix('employee_dtr').'.time_in1 IS NULL AND NOT '.$this->db->dbprefix('employee_dtr').'.time_out1 IS NULL ) OR ( '.$this->db->dbprefix('employee_dtr').'.time_out1 IS NULL AND NOT '.$this->db->dbprefix('employee_dtr').'.time_in1 IS NULL )) ');
		$this->db->where($this->db->dbprefix('employee_dtr').'.date',date('Y-m-d', $ldate ));
		$this->db->where($this->db->dbprefix('employee_dtr').'.deleted','0');
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id ','left');
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id ','left');
		$this->db->group_by($this->db->dbprefix('employee_dtr').'.employee_id');
		$log_issue_result = $this->db->get($this->db->dbprefix('employee_dtr'));

		$total_log_issue_count = $log_issue_result->num_rows();

		$body .= "<p>Total of ".$total_employees_count." employees with login and logout</p>";
		$body .= "<p>Total of ".$total_log_issue_count." employees with no login or logout</p>";

		$body .= "<table border='1'>
					<thead>
						<tr>
							<td>ID No.</td>
							<td>Name</td>
							<td>Login</td>
							<td>Logout</td>
						</tr>
					</thead>
					<tbody>";

		if( $total_log_issue_count > 0 ){

			foreach( $log_issue_result->result() as $log_result ){

				$body .= "<tr>
					<td>".$log_result->id_number."</td>
					<td>".$log_result->firstname." ".$log_result->lastname."</td>";

				if( $log_result->time_in1 == "" ){
					$body .= "<td> N/A </td>";
				}
				else{
					$body .= "<td>".$log_result->time_in1."</td>";
				}

				if( $log_result->time_out1 == "" ){
					$body .= "<td> N/A </td>";
				}
				else{
					$body .= "<td>".$log_result->time_out1."</td>";
				}

				$body .= "</tr>";

			}

		}
		else{

			$body .= "<tr>
					<td colspan='4'> No log issue found </td>
				</tr>";
		}

		$body .= "</tbody></table>";

		$body .= "<p><em>Note: This is a system generated message from CRON. You need not to reply.</em></p>";

		$mail_config = array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.gmail.com',
			'smtp_port' => '465',
			'smtp_user' => 'noreply@hdisystech.com',
			'smtp_pass' => 'hdisystech',
			'mailtype' => 'html',
		);

		$meta = $this->hdicore->_get_meta();

		$this->load->library('email', $mail_config);
		$this->email->set_newline("\r\n");
		$this->email->from($mail_config['smtp_user'], $meta['title']);

		$this->email->to($this->config->item('email_timereport_to'));
		$this->email->cc($this->config->item('email_timereport_cc'));
		$this->email->bcc($this->config->item('email_timereport_bcc'));
		
		$this->email->subject('Timekeeping Report (BIOMETRICS) As Of '.date($this->config->item('display_date_format_email'), $ldate ));
		$this->email->message($body);
		if ( !$this->email->send() )
		{
			echo 'Email was not successfully sent<br />';
		}
		else{
			echo 'Email was successfully sent<br />';
		}

	}

	// this habitual tardiness is also applicable with firstbalfour. Just change the habitual tardiness config.
	// run daily 
	private function _habitual_tardiness_oams()
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
					AND ht_flag = 0
					AND lates > 0 
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
					
					$this->db->select(' tardy_employee_id, employee_id, first_occurence, minutes_tardy, number_occured, DATE_ADD(first_occurence, INTERVAL '.$months_within.' MONTH) AS tardiness_within ', false);
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
						$total_min_tardy = $latest_tardy->minutes_tardy + $tardy_employee->lates;
						$total_occured_tardy = $latest_tardy->number_occured + 1;

						$data = array( 'minutes_tardy' => $total_min_tardy,
									   'number_occured' => $total_occured_tardy
									   );

						$this->db->update('tardy_employee', $data, array('tardy_employee_id' => $latest_tardy->tardy_employee_id));

						// if occured is more than config.
						if($total_min_tardy >= $minutes_tardy || $total_occured_tardy >= $instances) {
							// create ir here
							$this->_create_ir($tardy_employee->employee_id);

						}

						// change ht_flag to true, so it will not be counted next time
						$this->db->update('employee_dtr', array('ht_flag' => 1), array('id' => $tardy_employee->id));

					} else { // when tardiness is commited for the first time, or employee's previous tardiness is reset
						
						if($tardy_employee->lates >= $minutes_tardy) {
							// create ir here, no need to save on tardy_employee because employee is already on ir.
							$this->_create_ir($tardy_employee->employee_id);

							// change ht_flag to true, so it will not be counted next time
							$this->db->update('employee_dtr', array('ht_flag' => 1), array('id' => $tardy_employee->id));

						} else {

							$data = array( 'employee_id' => $tardy_employee->employee_id,
										   'first_occurence' => $tardy_employee->date,
										   'minutes_tardy' => $tardy_employee->lates,
										   'number_occured' => 1
										   );

							$this->db->insert('tardy_employee', $data);

							// change ht_flag to true, so it will not be counted next time
							$this->db->update('employee_dtr', array('ht_flag' => 1), array('id' => $tardy_employee->id));

						}

					}

				} // endif of with ncns

			} // end foreach

		} // end if

	} // end function

	// default for haibitual tardiness
	private function _create_ir($employee_id = false, $module_id = 115, $ir_status_id = 6, $offence_id = 5, $complainants = array(6), $details = "Habitual Tardiness")
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
		} else
			return false;

	}

	// run every first day of the month. openaccess
	private function _absenteeism()
	{

		$sql = "SELECT
				  com.employee_id,
				  COUNT(*)
				FROM (SELECT
				        employee_id
				      FROM {$this->db->dbprefix}employee_dtr
				      WHERE deleted = 0
				          AND awol = 1
				          AND hours_worked = 0
				          AND YEAR(`date`) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
				          AND MONTH(`date`) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))UNION ALL SELECT
				                                                          employee_id
				                                                        FROM {$this->db->dbprefix}employee_leaves
				                                                          LEFT JOIN {$this->db->dbprefix}employee_leaves_dates
				                                                            ON {$this->db->dbprefix}employee_leaves.employee_leave_id = {$this->db->dbprefix}employee_leaves_dates.employee_leave_id
				                                                        WHERE {$this->db->dbprefix}employee_leaves.deleted = 0
				                                                            AND {$this->db->dbprefix}employee_leaves.form_status_id = 3
				                                                            AND {$this->db->dbprefix}employee_leaves.application_form_id = 1
				                                                            AND MONTH({$this->db->dbprefix}employee_leaves_dates.date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) AS com
				GROUP BY employee_id
				HAVING COUNT( * ) > 1
				";

		$absenteeism = $this->db->query($sql);

		if($absenteeism && $absenteeism->num_rows() > 0)
		{
			foreach($absenteeism->result() as $emp_absenteeism)	
			{
				$qry = "SELECT
						  com.employee_id,
						  COUNT(*)
						FROM (SELECT
						        employee_id
						      FROM {$this->db->dbprefix}employee_dtr
						      WHERE deleted = 0
						          AND awol = 1
						          AND hours_worked = 0
						          AND YEAR(`date`) = YEAR(CURDATE())
						          AND MONTH(`date`) = MONTH(CURDATE())UNION ALL SELECT
						                                                          employee_id
						                                                        FROM {$this->db->dbprefix}employee_leaves
						                                                          LEFT JOIN {$this->db->dbprefix}employee_leaves_dates
						                                                            ON {$this->db->dbprefix}employee_leaves.employee_leave_id = {$this->db->dbprefix}employee_leaves_dates.employee_leave_id
						                                                        WHERE {$this->db->dbprefix}employee_leaves.deleted = 0
						                                                            AND {$this->db->dbprefix}employee_leaves.form_status_id = 3
						                                                            AND {$this->db->dbprefix}employee_leaves.application_form_id = 1
						                                                            AND MONTH({$this->db->dbprefix}employee_leaves_dates.date) = MONTH(CURDATE())) AS com
						WHERE employee_id = {$emp_absenteeism->employee_id}
						GROUP BY employee_id
						HAVING COUNT( * ) > 1
						";

				$result = $this->db->query($qry);

				if($result && $result->num_rows() > 0)
					$this->_create_ir($emp_absenteeism->employee_id, 115, 6, 5, array(6), "absenteeism");
			}
		}
	}


	function balfour_monthly_accumulation()
	{

		// get leave types
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_type_leave_setup.application_form_id', 'left');
		$this->db->order_by('employee_type_leave_setup.application_form_id', 'ASC');
		$this->db->where('accumulation_type_id', 1);
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

			// validate employees to get
			if($leave_type->employee_type_id)
				$this->db->where('employee_type', $leave_type->employee_type_id);

			if($leave_type->employment_status_id)
				$this->db->where('status_id', $leave_type->employment_status_id);

			if($this->config->item('use_cbe_cba'))
			{
				$this->db->where('CBE', $leave_type->CBE);
				$this->db->where('ECF', $leave_type->CBA);
			}

			$users = $this->db->get('user');

			if(!$users && $users->num_rows() == 0)
				continue;

			// insert accumulation on all employee's satisfied
			foreach($users->result() as $user) 
			{
				// get data
				$tenure = ($user->tenureship == 0 ? 1 : $user->tenureship);
				$accumulation = $leave_type->accumulation;

				$this->db->order_by('tenure', 'DESC');
				$etlc = $this->db->get_where('employee_type_leave_credit', array('leave_setup_id' => $leave_type->leave_setup_id, 'leave_type' => $leave_type->application_form_id, 'tenure <=' => $tenure));

				if($etlc && $etlc->num_rows() > 0)
					$accumulation = $etlc->row()->leave_accumulated;

				$new_value = $accumulation;

				$e_balance = $this->db->get_where('employee_leave_balance', array('year' => date('Y'), 'employee_id' => $user->user_id, 'deleted' => 0));

				if($e_balance->num_rows() > 0) {

					$balance = $e_balance->row();

					$new_value = $accumulation + $balance->{$appcode};

					if($leave_type->maximum > 0) {

						$remaining_bal = $balance->{$appcode} - $balance->{$appcode_used};

						$new_value = $accumulation + $remaining_bal;

						if($new_value >= $leave_type->maximum) {
							if(!$leave_type->convertible)
								$new_value = $leave_type->maximum;
						}

					}

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

	}

	function balfour_pay_all_excess()
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

	}

	private function _balfour_carried($year = false)
	{

		if(!$year)
			return;

		$e_balance = $this->db->get_where('employee_leave_balance', array('year' => $year, 'uneditable' => 0));

		if($e_balance && $e_balance->num_rows() > 0)
		{
			foreach($e_balance->result() as $carried_balance)
			{
				// check if balance exist.
				$bal_exist = $this->db->get_where('employee_leave_balance', array('year' => $year, 'employee_id' => $carried_balance->employee_id));
				
				$data = array( 'employee_id' => $carried_balance->employee_id,
							   'vl' => $carried_balance->vl,
							   'sl' => $carried_balance->sl,
							   'year' => date('Y')
							   );

				if($bal_exist && $bal_exist->num_rows() > 0) {
					$this->db->update('employee_leave_balance', $data, array('leave_balance_id' => $bal_exist->row()->leave_balance_id));
				} else {
					$this->db->insert('employee_leave_balance', $data);
				}

			}

		}

	}



	// run every first day of the year.
	function balfour_yearly_accumulation()
	{
		// get leave types
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_type_leave_setup.application_form_id', 'left');
		$this->db->order_by('employee_type_leave_setup.application_form_id', 'ASC');
		$this->db->where('accumulation_type_id', 4);
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

			// validate employees to get
			if($leave_type->employee_type_id)
				$this->db->where('employee_type', $leave_type->employee_type_id);

			if($leave_type->employment_status_id)
				$this->db->where_in('status_id', $leave_type->employment_status_id);

			if($leave_type->position_id != 0)
				$this->db->where_in('position_id', $leave_type->position_id);

			if($this->config->item('use_cbe_cba'))
			{
				$this->db->where('CBE', $leave_type->CBE);
				$this->db->where('ECF', $leave_type->CBA);
			}

			$users = $this->db->get('user');

			if(!$users && $users->num_rows() == 0)
				continue;

			// insert accumulation on all employee's satisfied
			foreach($users->result() as $user) 
			{
				// get data
				$tenure = ($user->tenureship == 0 ? 1 : $user->tenureship);
				$base_accumulation = $leave_type->base;

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
	}

}
