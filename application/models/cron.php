<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class cron extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function _execute_task( $task ){
		//set status to running
		//$this->db->update('scheduled_task', array('task_status' => 2), array('scheduled_task_id' => $task->scheduled_task_id));

		//standard parameters
		$var_args = array(
			'template_id' => $task->template_id,
			'email_to' =>$task->email_to,
			'email_cc' =>$task->email_cc,
			'email_bcc' =>$task->email_bcc,
		);
		

		//get other variables of task
		$variables = $this->db->get_where('scheduled_task_variable', array('scheduled_task_id' => $task->scheduled_task_id));
		if( $variables->num_rows() > 0 ){
			foreach( $variables->result() as $var ){
				$var_args[ $var->crontask_variable ] = $var->value;
			}	
		}
	

		//execute function
		$function = $task->crontask_function;
		eval( '$response = $this->'. $function .'( $var_args );' );
		
		
		//update last run and set to ready status
		$last_run = date('Y-m-d H:i:s');
		$this->db->update('scheduled_task', array('task_status' => 1, 'last_run' => $last_run), array('scheduled_task_id' => $task->scheduled_task_id));
		$this->db->insert('scheduled_task_history', array('scheduled_task_id' => $task->scheduled_task_id, 'datetime' => $last_run));
		
		return $response;
	}

	private function _emailer( $param )
	{

		$this->load->model('template');
		$mail = $this->template->get_queued();
		if($mail->num_rows() > 0)
		{
			$mail = $mail->row();
			$this->template->change_status($mail->timein, 'sending');			

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			$meta = $this->hdicore->_get_meta();

			$this->load->library('email', $mail_config);
			$this->email->set_newline("\r\n");
			$this->email->from($mail_config['smtp_user'], $meta['title']);

			if (trim($mail->to) == '' && trim($mail->cc) == '') {
				$this->email->to($mail_config['smtp_user']);
			} else {
				$this->email->to($mail->to);
				$this->email->cc($mail->cc);
			}

			$this->email->subject($mail->subject);
			$this->email->message($mail->body);
			if ( !$this->email->send() )
			{
				log_message('error', $this->email->print_debugger());
				$this->template->change_status($mail->timein, 'queued');
			}
			else{
				$this->template->delete_from_queue( $mail->timein, $mail->to, $mail->cc, $mail->bcc, $mail->subject, $mail->body);
				$this->template->change_status($mail->timein, 'queued');
			}
		}

		$response->msg = "Success";
		$response->msg_type = "success";

		return $response;
	}

	private function _raw_dtr_to_dtr( $param )
	{		
		ini_set('memory_limit', '256M');
		$this->load->helper('file');
		$this->load->helper('time_upload');

		// create log
		$folder = 'logs/biometric_logs';
		$log_file = $folder.'/'.date('Y-m-d').'.txt';
		if(!file_exists($folder)) 
			mkdir($folder, 0777, true);

		
		// log message
		$log_msg = date('Ymd H:i:s')." START UPLOADING \r\n";
		write_file($log_file, $log_msg, 'a');

		
		$this->db->where('location_id', 1);
		$this->db->where('deleted', 0);
		$location = $this->db->get('timekeeping_location')->row();

		$db2 = $this->load->database('ms_sql', TRUE);
		$sql = $location->query;

		$qry = $db2->query($sql);

		if( $qry->num_rows > 0 ){
			$msdata = $qry->result();
		}

		$not_exist_biometrics = array();
		$records_read = 0;
		
		// log message
		$log_msg = date('Ymd H:i:s').' '.$qry->num_rows." record(s) read. \r\n";
		write_file($log_file, $log_msg, 'a');

		$qry->free_result();

		$biometric_array = array();
		$insert_array = array();


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

					//if(!in_array($row->employee_id, $not_exist_biometrics))
					//	$not_exist_biometrics[] = $row->employee_id;

					// log message
					$log_msg = date('Ymd H:i:s').' '.$row->employee_id." does not exist. \r\n";
					write_file($log_file, $log_msg, 'a');

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


		if(  sizeof($insert_array) > 0) $this->db->insert_batch('employee_dtr_raw', $insert_array);

		if ($this->db->_error_message() == '') {
			// log message
			$log_msg = date('Ymd H:i:s')." Start running process time. \r\n";
			write_file($log_file, $log_msg, 'a');

			// if ($this->config->item('client_no') == 2){
			// 	process_time_raw_oams(1, 0);
			// }				
			// else{
			// 	process_time_raw(1, 0);	
			// }
			process_time_biometrics(1,0);

			// log message
			$log_msg = date('Ymd H:i:s')." End process time. \r\n";
			write_file($log_file, $log_msg, 'a');
			$response->msg = 'Task successfully excuted.';
			$response->msg_type = "success";	
		}
		else {
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";				
		}

		// log message
		$log_msg = date('Ymd H:i:s')." END UPLOADING \r\n";
		write_file($log_file, $log_msg, 'a');		
		

		return $response;
	}

	private function _employee_movement(){
		$response = new stdClass;
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
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		} else if ($results->num_rows() == 0) {
			$response->msg = 'No results found.';
			$response->msg_type = "attention";
		} else {
			$requests = $results->result();
			$complete = array();			

			foreach ($requests as $request) {
				$response->msg[] = 'Processing request for: ' . $request->name;
				$response->msg_type[] = "attention";
				
				$this->db->where('employee_id', $request->employee_id);
				$this->db->where('deleted', 0);
				
				$this->db->limit(1);

				$employee  = $this->db->get('employee');
				$completed = TRUE;

				if ($employee->num_rows() == 0) {
					if ($this->db->insert('employee', array('employee_id' => $request->employee_id, 'user_id' => $request->employee_id))) {
						$response->msg[] = 'Employee record created.';
						$response->msg_type[] = "success";
						break;					
					} else {
						$response->msg[] = 'Failed to create employee record. Aborting...';
						$response->msg_type[] = "error";
						break;
					}
				}				

				if ($request->compensation_effectivity_date <= $curr_date) {
					$response = $this->_employee_update_compensation($request, $response);
				}

				if ($request->transfer_effectivity_date <= $curr_date) {
					$response = $this->_employee_update_transfer($request, $employee->row(), $response);
				}

				if ($completed) {
					$complete[] = $request->employee_movement_id;
				}
			}	
			$this->db->flush_cache();
			$this->db->where_in('employee_movement_id', $complete);
			$this->db->update('employee_movement', array('status' => 6, 'processed' => 1));
		}
	
		return $response;
	}

	private function _employee_update_transfer($request, $employee, $response)
	{
		// initialize flags
		$user_flag = false;
		$employee_flag = false;
		$did_update = false;
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
			$this->db->where('project_name_id',$request->project_name_id);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('user',array("project_name_id"=>$request->project_name_id));
		}

		if ($request->group_name_id > 0){
			$this->db->where('group_name_id',$request->group_name_id);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('user',array("group_name_id"=>$request->group_name_id));
		}

		if ($request->division_id > 0){
			$this->db->where('division_id',$request->division_id);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('user',array("division_id"=>$request->division_id));
		}		

		if ($request->department_id > 0){
			$this->db->where('department_id',$request->department_id);
			$this->db->where('employee_id',$request->employee_id);
			$this->db->update('user',array("department_id"=>$request->department_id));
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
				$response->msg[] =  "No Previous Approver"; // .$this->db->last_query();
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
			$this->db->update('employee');
			if( $this->db->_error_message() == "" ){
				$response->msg[] = 'Employee regularization success.';
				$response->msg_type[] = "success";
			}
			else{
				$response->msg[] = $this->db->_error_message();
				$response->msg_type[] = "error";
			}

			return $response;
		}

		// Process LEAVES FOR PROMOTION. Orayt.!
		if (in_array(3, $movement_types)) {
			// set promotion effectivity
			$this->db->set('last_promotion_date', date('Y-m-d', strtotime($request->transfer_effectivity_date)));
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('employee');

			// let's brag that promotion!
			$this->_save_memo(4, 'Promoted', $request->employee_id, date('Y-m-d', strtotime($request->transfer_effectivity_date)));

			// if you are promoted and have lower inexcess. system automatically computes that
			$leave_setup = $this->db->get_where('employee_type_leave_setup', array('employee_type_id' => $request->employee_type, 'application_form_id' => 1));
			if($leave_setup && $leave_setup->num_rows() > 0)
			{
				$elb = $this->db->get_where('employee_leave_balance', array('employee_id' => $request->employee_id, 'year' => date('Y', strtotime($request->transfer_effectivity_date))));
				if($elb && $elb->num_rows() == 0) {
					$response->msg[] = 'Employee transferred.';
					$response->msg_type[] = "success";
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
					if( $check_bal->num_rows() == 0){
						$this->db->insert('employee_leave_balance', $data);
						if( $this->db->_error_message() == "" ){
							$response->msg[] = 'Employee leave credits updated.';
							$response->msg_type[] = "success";
						}
						else{
							$response->msg[] = $this->db->_error_message();
							$response->msg_type[] = "error";
						}
					}
					else{
						$this->db->update('employee_leave_balance', $data, array('employee_id' => $request->employee_id, 'year' => date('Y', $promotion_d)));
						$this->db->insert('employee_leave_balance', $data);
						if( $this->db->_error_message() == "" ){
							$response->msg[] = 'Employee leave credits updated.';
							$response->msg_type[] = "success";
						}
						else{
							$response->msg[] = $this->db->_error_message();
							$response->msg_type[] = "error";
						}
					}
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
			$this->_save_memo(3, $memo_title, $request->employee_id, date('Y-m-d', strtotime($request->transfer_effectivity_date)));

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

					$response->msg[] = 'Processing signatories.';

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
				if($this->config->item('client_no') != 1){
					$response->msg[] = 'Employee transferred.';
					$response->msg_type[] = "success";
				}

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

				$response->msg[] = 'Employee transferred.';
				$response->msg_type[] = "success";
			}
			else{
				$response->msg[] = 'Employee transferred.';
				$response->msg_type[] = "success";	
			}
		}


		// SALARY INCREASE
		if (in_array(2, $movement_types)) {

			$this->db->set('status_effectivity', date('Y-m-d', strtotime($request->compensation_effectivity_date)));
			$this->db->where('employee_id', $request->employee_id);
			$this->db->update('employee');

			if( $this->db->_error_message() == "" ){
				$response->msg[] = 'Employee salary updated.';
				$response->msg_type[] = "success";
			}
			else{
				$response->msg[] = $this->db->_error_message();
				$response->msg_type[] = "error";
			}
		}

		// did it? (i re-code this part since it always return TRUE)
		if(!$did_update){ // Seriously..?! >_<
			$response->msg[] =  "Nothing to update. Proceeding...";
			$response->msg_type[] = "error";
		} 
		
		return $response;
	}

	private function _save_memo($memo_type_id, $title = null, $memo_for = null, $publish_date_from = false, $publish = 1)
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

	protected function _employee_update_compensation($request, $response)
	{
		$updates = array();
		if ($request->new_position_id > 0) {
			$updates['position_id'] = $request->new_position_id;
			$this->db->update('user', $updates, array('employee_id' => $request->employee_id));			
		}

		// Put other info here....
		$this->db->update('employee_payroll', array('salary' => $this->encrypt->encode($request->new_basic_salary) ), array('employee_id' => $request->employee_id));

		$this->db->where('employee_movement_id', $request->employee_movement_id);
		$movement_benefit = $this->db->get('employee_movement_benefit');

		if (!$movement_benefit || $this->db->_error_message() != '') {
			$response->msg[] = $this->db->_error_message();
			$response->msg_type[] = "error";
		} else if ($movement_benefit->num_rows() == 0) {
			$response->msg[] = 'No result was found.';
			$response->msg_type[] = "attention";
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

			$response->msg[] = 'Compensation updated';
			$response->msg_type[] = "success";
		}

		return $response;
	}

	/**
	 * Run every day to check each of employees for accumulation of leaves
	 * First time leave credit
	 */
	function _leave_credit( $param ){
		$current_date = date('Y-m-d');

		$this->db->join('employee_form_type','employee_form_type.application_form_id = employee_type_leave_setup.application_form_id','left');
		$this->db->where('employee_type_leave_setup.deleted',0);
		$leave_setups = $this->db->get('employee_type_leave_setup');

		if( $leave_setups->num_rows() > 0 ){
			foreach( $leave_setups->result() as $leave_setup ){
				//build query to include which employees it applies
				if( !empty($leave_setup->employee_type_id) ){
					$employee_type_list = explode(',',$leave_setup->employee_type_id);
					$this->db->where_in('employee.employee_type', $employee_type_list);
				}

				if( !empty($leave_setup->employment_status_id) ){
					$employee_status_list = explode(',',$leave_setup->employment_status_id);
					$this->db->where_in('employee.status_id', $employee_status_list);
				}

				if( !empty( $leave_setup->tenure ) ){
					$date_tenure = date('Y-m-d', strtotime('-'.$leave_setup->tenure.' months', strtotime($current_date)));
					switch($leave_setup->tenure_from_id){
						case 1: //hiring date
							$this->db->where('employee.employed_date', $date_tenure);
							break;
						case 2: //regularization date
							$this->db->where('employee.regular_date', $date_tenure);
							break;
					}
				}

				$this->db->join('user','employee.employee_id = user.employee_id','left');
				$this->db->join('employee_leave_balance','employee.employee_id = employee_leave_balance.employee_id','left');
				$this->db->where('user.deleted', 0);
				$this->db->where('employee_leave_balance.employee_id is null');
				$this->db->where('employee.resigned', 0);
				$this->db->where('employee.resigned_date is null');
				$this->db->select('employee.*, user.*');
				$employees = $this->db->get('employee');
				
				if( $employees->num_rows() > 0 ){
					foreach($employees->result() as $employee){
						$response->msg[] = 'Trying to update leave balance of '.$employee->firstname.' ' .$employee->lastname;
						$response->msg_type[] = 'success';
						
						if($leave_setup->prorated == 1){

						}
						else{
							$credit_value = $leave_setup->base;
						}

						switch($leave_setup->application_form_id){
							case 1:
								$credit['sl'] = $credit_value;
								break;
							case 2:
								$credit['vl'] = $credit_value;
								break;
							case 3:
								$credit['el'] = $credit_value;
								break;
							case 4:
								$credit['bl'] = $credit_value;
								break;
							case 5:
								$credit['mpl'] = $credit_value;
								break;
							case 6:
								$credit['mpl'] = $credit_value;
								break;
						}

						if(isset( $credit )){
							//check if balance exists
							$balance = $this->db->get_where('employee_leave_balance', array('deleted' => 0, 'employee_id' => $employee->employee_id, 'year' => date('Y', strtotime($current_date))));
							if( $balance->num_rows() == 1 ){
								$this->db->update('employee_leave_balance', $credit, array('deleted' => 0, 'employee_id' => $employee->employee_id, 'year' => date('Y', strtotime($current_date))));
							}
							else{
								$credit['employee_id'] = $employee->employee_id;
								$credit['year'] = date('Y', strtotime($current_date));
								$this->db->insert('employee_leave_balance', $credit);
							}

							if( $this->db->_error_message() != "" ){
								$response->msg[] = $this->db->_error_message();
								$response->msg_type[] = 'error';
							}
							else{
								$response->msg[] = 'Successfully update leave balance.';
								$response->msg_type[] = 'success';
							}

							unset($credit);
						}
					}
				}
			}
		}
		else{
			$response->msg = 'No leave setup found.';
			$response->msg_type = 'attention';
		}

		return $response;
	}



	function email_planning_period_setup() {
		$this->load->model('template');
		$appraisal_email_reminder = $this->db->query(" SELECT * FROM {$this->db->dbprefix}appraisal_email_reminder a 
													LEFT JOIN {$this->db->dbprefix}appraisal_planning_period b
													ON a.planning_period_id = b.planning_period_id
													WHERE a.email_sent = '0' AND b.period_status = '1'");

		if( $appraisal_email_reminder && $appraisal_email_reminder->num_rows() > 0) {

			$date_today = date('Y-m-d');
			foreach ($appraisal_email_reminder->result() as $key => $value) {
				$planning_period_id 			= $value->planning_period_id;				
				$appraisal_email_reminder_id 	= $value->appraisal_email_reminder_id;
			    $appraisal_email_reminder 		= $value->appraisal_email_reminder;
			    $appraisal_email_reminder_date 	= $value->date;
			    $uploaded_file 					= $value->uploaded_file;
			    $template_id					= $value->template_id;
				$date_from 						= $value->date_from;
				$date_to  						= $value->date_to;
				$year 							= $value->year;
				$tag_run = 0;

			    if($appraisal_email_reminder_date != '0000-00-00') {

				    if($appraisal_email_reminder_date == $date_today) {
				    	$tag_run = 1;
				    }			    	
			    }


			    if($tag_run) {
			    	$employees = explode(',', $value->employee_id);

			    	foreach ($employees as $key => $emp_id) {
			    		$request = array();
			    		$result = $this->db->get_where('user',array('user_id'=>$emp_id));
			    		$recipients = '';
				    	if ($result && $result->num_rows() > 0){
				    		$single_row = $result->row();
				    		$user = $result->row_array();

				    		if ($single_row->email != ''){
								$recipients = $single_row->email;
				    		}
				    	}

				    	$request['full_name'] = $user['firstname'].' '.$user['middleinitial'].' '.$user['lastname'];
				    	$request['year'] = $year;
				    	$request['link'] = base_url().'appraisal/appraisal_planning/edit/'.$emp_id.'/'.$planning_period_id;
				    	// $recipients
				    	if(!empty($uploaded_file)) { //image only
				    		$link = $request['link'];
				    		$logo = base_url().$uploaded_file;
				    		$message = '<p>
				    						<a href="'.$link.'">
				    							<img src = "'.$logo.'"/>
				    						</a>
				    					</p>';
				    	} else { // dummy template

				    		$this->db->where('template_id',$template_id);
				    		$this->db->where('deleted',0);
				    		$template_result = $this->db->get('template');

				    		if( $template_result->num_rows() > 0 ){
				    			$template_info = $template_result->row();
				            	$template = $this->template->get_module_template($template_info->module_id, $template_info->code);
				            	$message = $this->template->prep_message($template['body'], $request);
				            	$subject = $template_info->subject;

				            	$this->template->queue($recipients, '', $subject , $message);
				        	}
				        	else{
				        		$template = $this->template->get_module_template(0, 'planning_period');
				            	$message = $this->template->prep_message($template['body'], $request);

				            	$this->template->queue($recipients, '', 'Planning Period ('.$year.')' , $message);
				        	}


				    	}
				    	
			    	}
			    	$this->db->update('appraisal_email_reminder', array('email_sent' => 1), array('appraisal_email_reminder_id' => $appraisal_email_reminder_id));
			    }
			}
		}
		$response->msg = "Task is successfully executed";
		$response->msg_type = 'success';
		return $response;
	}

	function email_appraisal_period_setup() {
		$this->load->model('template');
		$appraisal_email_reminder = $this->db->query(" SELECT a.*, b.*, a.date AS date_reminder FROM {$this->db->dbprefix}appraisal_employee_email_reminder a 
													LEFT JOIN {$this->db->dbprefix}employee_appraisal_period b
													ON a.employee_appraisal_period_id = b.employee_appraisal_period_id
													WHERE a.email_sent = '0' AND b.employee_appraisal_period_status_id = '1'");
		// dbug($this->db->last_query());
		if( $appraisal_email_reminder && $appraisal_email_reminder->num_rows() > 0) {
			$date_today = date('Y-m-d');
			foreach ($appraisal_email_reminder->result() as $key => $value) {
				$employee_appraisal_period_id	= $value->employee_appraisal_period_id;				
				$appraisal_email_reminder_id 	= $value->appraisal_email_reminder_id;
			    $appraisal_email_reminder 		= $value->appraisal_email_reminder;
			    $appraisal_email_reminder_date 	= $value->date_reminder;
			    $uploaded_file 					= $value->uploaded_file;
			    $template_id					= $value->template_id;
				$date_from 						= $value->date_from;
				$date_to  						= $value->date_to;
				$year 							= $value->year;
				$planning_period_id             = $value->planning_period_id;
				$tag_run = 0;

			    if($appraisal_email_reminder_date != '0000-00-00') {
				    if($appraisal_email_reminder_date == $date_today) {
				    	$tag_run = 1;
				    }			    	
			    } 

			    if($tag_run) {
			    	$planning = $this->db->query(" SELECT * FROM {$this->db->dbprefix}appraisal_planning_period
													WHERE planning_period_id = '{$planning_period_id}'")->row();
			    	
			    	$employees = explode(',', $planning->employee_id);

			    	foreach ($employees as $key => $emp_id) {
			    		$request = array();
			    		$result = $this->db->get_where('user',array('user_id'=>$emp_id));
			    		$recipients = '';
				    	if ($result && $result->num_rows() > 0){
				    		$single_row = $result->row();
				    		$user = $result->row_array();

				    		if ($single_row->email != ''){
								$recipients = $single_row->email;
				    		}
				    	}
				    	$request['full_name'] = $user['firstname'].' '.$user['middleinitial'].' '.$user['lastname'];
				    	$request['year'] = $year;
				    	$request['link'] = base_url().'employee/appraisal/edit/'.$emp_id.'/'.$employee_appraisal_period_id;
				    	// $recipients
				    	if(!empty($uploaded_file)) { //image only
				    		$link = $request['link'];
				    		$logo = base_url().$uploaded_file;
				    		$message = '<p>
				    						<a href="'.$link.'">
				    							<img src = "'.$logo.'"/>
				    						</a>
				    					</p>';
				    	} else { // dummy template
				            
				            $this->db->where('template_id',$template_id);
				    		$this->db->where('deleted',0);
				    		$template_result = $this->db->get('template');

				    		if( $template_result->num_rows() > 0 ){
				    			$template_info = $template_result->row();
				            	$template = $this->template->get_module_template($template_info->module_id, $template_info->code);
				            	$message = $this->template->prep_message($template['body'], $request);
				            	$subject = $template_info->subject;

				            	$this->template->queue($recipients, '', $subject , $message);
				        	}
				        	else{
				        		$template = $this->template->get_module_template(0, 'appraisal_period');
				            	$message = $this->template->prep_message($template['body'], $request);

				            	$this->template->queue($recipients, '', 'Appraisal Period ('.$year.')' , $message);
				        	}

				    	}
				    	
			    	}
			    	$this->db->update('appraisal_employee_email_reminder', array('email_sent' => 1), array('appraisal_email_reminder_id' => $appraisal_email_reminder_id));
			    }
			}
		}
		$response->msg = "Task is successfully executed";
		$response->msg_type = 'success';
		return $response;
	}










	

}
?>