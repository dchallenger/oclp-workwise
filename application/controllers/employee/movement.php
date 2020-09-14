<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Movement extends MY_Controller
{
	function __construct()
    {
        parent::__construct();                

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists employee movements.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an employee movement.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an employee movement.';
		if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";
        }

        $this->default_sort_col = array('firstname');
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';
		$data['scripts'][] = chosen_script();

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		//load variables to env
		$this->load->vars( $data );

		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );

		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );

		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
    }

	function detail()
	{
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = $this->module_link . '/detailview';

		//other views to load
		$data['views'] = array();

		//initialize buttons
		$data['buttons'] = $this->module_link . '/view-button';

		//initialize ir_status
		$movement = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));
		if( $movement->num_rows() == 1 ){
			$movement = $movement->row();
			$data['status'] = $movement->status;
		}

		//load variables to env
		$this->load->vars( $data );


		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );

		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );

		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
	}

	function edit($bypass = false)
	{		

		if( $bypass == true ){
			parent::edit();
			return;
		}

		if($this->user_access[$this->module_id]['edit'] == 1){
			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';

			if($this->config->item('client_no') != 2)
				$data['buttons'] = $this->userinfo['rtheme'].'/employee/movement/edit-button-approval';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			//load variables to env
			$this->load->vars( $data );

			$vars = $this->load->get_vars();
			$fieldgroups = $vars['fieldgroups'][1];

			//load the final view
			//load header
			$this->load->view( $this->userinfo['rtheme'].'/template/header' );
			$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );

			//load page content
			$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );

			//load footer
			$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
		}
		else{
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function ajax_save()
	{
		//Save Current Position and Department
		$employee = $this->system->get_employee($this->input->post('employee_id'));
		$role_id = $this->db->get_where('user', array("employee_id" => $this->input->post('employee_id')))->row()->role_id;
		$leaves = $this->system->get_leaves($this->input->post('employee_id'));
		$nature_movement = $this->input->post('employee_movement_nature_movement');
		$employees = $this->input->post('employee_id');
		
		if( trim($this->input->post('transfer_effectivity_date') == "")) {
			$response['msg'] = 'Please Input Effectivity Date';
			$response['msg_type'] = 'error';		
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		}

		// if floating is clicked
		if(is_array($nature_movement) && count($nature_movement) > 0 && in_array(12, $nature_movement)) {
			// reflect changes
			$_POST['transfer_effectivity_date'] = $this->input->post('floating_date_from');
			$_POST['employee_id'] = $this->input->post('employee_floating_id');

			parent::ajax_save();

			if($this->input->post('draft')) 
				$status = 1;
			else 
				$status = 3;

			$this->db->update('employee_movement', array('status' => $status, 'approved_by' => 1 ), array($this->key_field => $this->key_field_val ));

			if(!$this->input->post('draft')) 
				$this->db->update('employee_movement', array('status' => 6, 'processed' => 1), array($this->key_field => $this->key_field_val ));

		} else if(is_array($nature_movement) && count($nature_movement) > 0 && in_array(13, $nature_movement)) { // if recall button is clicked
			// reflect changes
			$_POST['transfer_effectivity_date'] = $this->input->post('floating_date_to');
			$_POST['employee_id'] = $this->input->post('employee_floating_id');

			if($this->input->post('draft')) 
				$status = 1;
			else 
				$status = 3;

			parent::ajax_save();

			if($this->input->post('draft')) 
				$status = 1;
			else 
				$status = 3;

			$this->db->update('employee_movement', array('status' => $status, 'approved_by' => 1 ), array($this->key_field => $this->key_field_val ));

			// $this->db->update('employee_movement', array('status' => 6, 'processed' => 1), array($this->key_field => $this->key_field_val ));

		} else {

			if( (trim($this->input->post('new_basic_salary')) != "" && $this->input->post('compensation_effectivity_date') == "") || (is_array($this->input->post('benefit')) > 0 && $this->input->post('compensation_effectivity_date') == "") ) {
				$response['msg'] = 'Please Input Effectivity Date';
				$response['msg_type'] = 'attention';		
				$data['json'] = $response;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
				return;
			}

			if( ($this->input->post('new_position_id') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('role') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('rank_id') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('job_level') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('rank_code') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('company_id') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('division_id') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('location_id') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('segment_1_id') != "" && $this->input->post('transfer_effectivity_date') == "") || ($this->input->post('segment_2_id') != "" && $this->input->post('transfer_effectivity_date') == "") || (in_array(1, $nature_movement) && $this->input->post('transfer_effectivity_date') == "") ) {
				$response['msg'] = 'Please Input Effectivity Date';
				$response['msg_type'] = 'attention';		
				$data['json'] = $response;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
				return;
			}

			if($this->config->item('save_leave_regularization_during_saving') == 1)
			{
				$transfer_effectivity_date = $this->input->post('transfer_effectivity_date');

				if(is_array($nature_movement) && count($nature_movement) > 0 && in_array(1, $nature_movement))
				{
					$effective = strtotime($transfer_effectivity_date);					
					$hired_date = $this->db->get_where('employee', array("employee_id" => $this->input->post('employee_id')))->row();

					$this->db->select('et.*, ef.application_code');
					$this->db->from('employee_type_leave_setup et');
					$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
					$this->db->where('employee_type_id', $hired_date->employee_type);
					//$this->db->where('leave_setup_id', 23);
					$this->db->where('ef.deleted', 0);
					$this->db->order_by('et.tenure','DESC');
					
					$leave_setup = $this->db->get();

					if ($leave_setup && $leave_setup->num_rows() > 0){
						foreach ($leave_setup->result() as $row) {
							$base = $row->base;
							if (CLIENT_DIR == 'firstbalfour'){
								//to override if exist in leave setup exemption - tirso
								$exemption_array = $this->system->get_employe_leave_setup_exemption($this->input->post('employee_id'),$row->leave_setup_id);

								$base = ($exemption_array['base'] !== 0 ? $exemption_array['base'] : $row->base);
								//
							}

							$vl_allowed = date("Y-m-d", strtotime(date("Y-m-d", strtotime($hired_date->employed_date)) . " + ".$row->tenure." month"));
							if ($row->tenure <> 0 && strtotime( $vl_allowed ) > strtotime($transfer_effectivity_date)){
								$date_diff = gregoriantojd(12, 31, date('Y', strtotime($vl_allowed))) - gregoriantojd(date('m', strtotime($vl_allowed)), date('d', strtotime($vl_allowed)), date('Y', strtotime($vl_allowed)));								
								$year = date('Y', strtotime($vl_allowed));								
							}
							else{
								$date_diff = gregoriantojd(12, 31, date('Y', $effective)) - gregoriantojd(date('m', $effective), date('d', $effective), date('Y', $effective));
								$year = date('Y');
							}

							$new_credit = 0;
							if (!$row->prorated) {
								$data[strtolower($row->application_code)] = $base;
								$new_credit = $base;
							} else {
								// Formula for pro-rated
								$monthly = $base / 365;
								$days_remaining = $monthly * $date_diff;
								$credits = round($days_remaining * 2) / 2;
								$data[strtolower($row->application_code)] = $credits;
								$new_credit = $credits;								
							}

							$data['year'] = $year;								
							$data['employee_id'] = $this->input->post('employee_id');

							$check_bal = $this->db->get_where('employee_leave_balance',array('employee_id' => $this->input->post('employee_id'), 'year' => $year));

							if( $check_bal->num_rows() == 0){
								$this->db->insert('employee_leave_balance', $data);
								unset($data[strtolower($row->application_code)]);
							}
							else{
								$app_code = strtolower($row->application_code);
																
								if (CLIENT_DIR == 'firstbalfour') {
									$bal = $check_bal->row();
									if ($app_code == 'el' && $bal->el > 0) {
										$new_credit = 0;
									}	
								}
								

								$qry = "UPDATE {$this->db->dbprefix}employee_leave_balance SET {$app_code} = {$app_code} + {$new_credit} WHERE employee_id = {$this->input->post('employee_id')} AND year = {$year}";
								$this->db->query($qry);
								unset($data[strtolower($row->application_code)]);								
/*								$this->db->update('employee_leave_balance', $data, array('employee_id' => $this->input->post('employee_id'), 'year' => $year));														
								unset($data[strtolower($row->application_code)]);*/
							}
						}
					}

/*					$hired_date = $this->db->get_where('employee', array("employee_id" => $this->input->post('employee_id')))->row();

					$leave_tenure = $this->db->get_where('employee_type_leave_setup', array("employee_type_id" => $hired_date->employee_type, "deleted" => 0, "tenure <>" => 0));
					if($leave_tenure->num_rows() > 0)
						$vl_allowed = date("Y-m-d", strtotime(date("Y-m-d", strtotime($hired_date->employed_date)) . " + ".$leave_tenure->row()->tenure." month"));
					else
						$vl_allowed = "0000-00-00";

					if($vl_allowed != '0000-00-00' && strtotime( $vl_allowed ) > strtotime($transfer_effectivity_date) ){
						// Add leave credits, use leave setup based on employee type.
						$this->db->select('et.*, ef.application_code');
						$this->db->from('employee_type_leave_setup et');
						$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
						$this->db->where('employee_type_id', $hired_date->employee_type);
						$this->db->where('ef.deleted', 0);
						
						$leave_setup = $this->db->get();

						$date_diff = gregoriantojd(12, 31, date('Y', strtotime($vl_allowed))) - gregoriantojd(date('m', strtotime($vl_allowed)), date('d', strtotime($vl_allowed)), date('Y', strtotime($vl_allowed)));
						if ($leave_setup->num_rows() > 0) {
							$data['year'] = date('Y', strtotime($vl_allowed));
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
							$data['employee_id'] = $this->input->post('employee_id');
							$check_bal = $this->db->get_where('employee_leave_balance',array('employee_id' => $this->input->post('employee_id'), 'year' => date('Y', strtotime($vl_allowed))));
							if( $check_bal->num_rows() == 0)
								$this->db->insert('employee_leave_balance', $data);
							else
								$this->db->update('employee_leave_balance', $data, array('employee_id' => $this->input->post('employee_id'), 'year' => date('Y', strtotime($vl_allowed))));
						}
					} else {
						// Add leave credits, use leave setup based on employee type.
						$this->db->select('et.*, ef.application_code');
						$this->db->from('employee_type_leave_setup et');
						$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
						$this->db->where('employee_type_id', $hired_date->employee_type);
						$this->db->where('ef.deleted', 0);
						// $date_diff = strtotime('2012-12-31') - strtotime('2012-12-06') / ( 60 * 60 * 24);
						// $date_tally = date('Y-m-d');
						$leave_setup = $this->db->get();
						
						$effective = strtotime($transfer_effectivity_date);

						$date_diff = gregoriantojd(12, 31, date('Y', $effective)) - gregoriantojd(date('m', $effective), date('d', $effective), date('Y', $effective));

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
							$data['employee_id'] = $this->input->post('employee_id');
							$check_bal = $this->db->get_where('employee_leave_balance',array('employee_id' => $this->input->post('employee_id'), 'year' => date('Y', $effective)));

							if( $check_bal->num_rows() == 0)
								$this->db->insert('employee_leave_balance', $data);
							else
								$this->db->update('employee_leave_balance', $data, array('employee_id' => $this->input->post('employee_id'), 'year' => date('Y', $effective)));
						}
					}*/
				}
			} 

			if(is_array($nature_movement) && count($nature_movement) > 0) {

					parent::ajax_save();
					
					if($this->key_field_val==null || $this->key_field_val==0 || $this->key_field_val!=-1)
					{
						//benefit
						$benefits = $this->input->post('benefit');
						if (is_array($benefits) && count($benefits) > 0) {
							$this->db->delete('employee_movement_benefit',array('employee_id' => $this->input->post('employee_id')));
							foreach( $benefits as $benefit_id => $value ){
								$data = array(
									'employee_movement_id' => $this->key_field_val,
									'employee_id' => $this->input->post('employee_id'),
									'benefit_id' => $benefit_id,
									'value' => str_replace(',', '', $value)				
								);
								$this->db->insert('employee_movement_benefit', $data);
							}
						}
						//end benefit
					}

					$employee_movement_type = implode(',',$nature_movement);

					// Removed For Approval Process
					if($this->input->post('draft')) 
						$status = 1;
					else 
						$status = 3;

					// if( $this->input->post('pending') ) $status = 2;
					$approved_by = 1;

					if(CLIENT_DIR == 'firstbalfour'){
						//is blacklisted if resigned, end of contract or terminated
						if($employee_movement_type == 6 || $employee_movement_type == 7 || $employee_movement_type == 10){
							$blacklisted = $this->input->post('blacklisted');
						}else{
							$blacklisted = 0;
						}

						$this->db->update('employee_movement', array('employee_id' => $employees, 'employee_movement_type_id' => $employee_movement_type,'current_position_id' => $employee['position_id'], 'current_role' => $role_id, 'current_department_id' => $employee['department_id'], 'current_sick_leave' => $leaves['sl'], 'current_vacation_leave' => $leaves['vl'], 'status' => $status, 'approved_by' => 1, 'blacklisted' => $blacklisted), array($this->key_field => $this->key_field_val ));
					}else{
						$this->db->update('employee_movement', array('employee_id' => $employees, 'employee_movement_type_id' => $employee_movement_type,'current_position_id' => $employee['position_id'], 'current_role' => $role_id, 'current_department_id' => $employee['department_id'], 'current_sick_leave' => $leaves['sl'], 'current_vacation_leave' => $leaves['vl'], 'status' => $status, 'approved_by' => 1), array($this->key_field => $this->key_field_val ));
					}

					if($this->input->post('record_id') != '-1')
						$this->db->update('employee_movement', array('status' => 3, 'processed' => 0), array($this->key_field => $this->key_field_val ));
			} else {
				$response['msg'] = 'Pick a nature of movement';
				$response['msg_type'] = 'attention';		
				$data['json'] = $response;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
			//additional module save routine here

			//Save Current Position and Department
			$employee = $this->system->get_employee($this->input->post('employee_id'));
			$leaves = $this->system->get_leaves($this->input->post('employee_id'));
			$employee_movement_type = implode(',',$this->input->post('employee_movement_nature_movement'));

			if($this->input->post('draft')) 
				$status = 1;
			else 
				$status = 3;

			$approved_by = 1;

			$this->db->update('employee_movement', array('employee_movement_type_id' => $employee_movement_type,'current_position_id' => $employee['position_id'], 'current_role' => $role_id, 'current_department_id' => $employee['department_id'], 'current_sick_leave' => $leaves['sl'], 'current_vacation_leave' => $leaves['vl'], 'status' => $status, 'approved_by' => $approved_by ), array($this->key_field => $this->key_field_val ));
		}

	}

	function delete()
	{
		$employee_movement_info = $this->db->get_where($this->module_table, array('employee_movement_id'=>$this->input->post('record_id')))->row();
		$clearance_result = $this->db->get_where('employee_clearance',array('employee_id'=>$employee_movement_info->employee_id, 'deleted'=>0));

		if( $clearance_result->num_rows() > 0 ){

			$this->db->where('employee_id',$employee_movement_info->employee_id);
			$this->db->update('employee_clearance',array('deleted'=>1));

		}

		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	/**
	 * Send the email to approver.
	 */
	function send_email() {
		$this->db->where($this->key_field, $this->input->post('record_id'));
		
		$record = $this->db->get($this->module_table);

		$movement_request = $record->row_array();

		if (IS_AJAX && !is_null($record) && $record->num_rows() > 0) {
			$response->type = 'notice';
			$response->msg = 'Sending failed.';
			$response->record_id = $this->input->post('record_id');

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');

			if ($mail_config) {
				$recepients = array();				
				
				// Get recepient.				
				$this->db->where_in('user_id', $movement_request['approved_by']);
				$result = $this->db->get('user')->result_array();

				foreach ($result as $row) {
					$recepients[] = $row['email'];
				}
			}
						
			$update['status'] 	    = '2';
			$update['updated_by']   = $this->userinfo['user_id'];
			$update['updated_date'] = date('Y-m-d H:i:s');
            $update['date_sent'] = date('Y-m-d G:i:s');

			$this->db->where($this->key_field, $movement_request[$this->key_field]);
			$this->db->update($this->module_table, $update);

			$response->msg_type = 'success';
			$response->msg = 'Employee Movement Sent.';			

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
	    $sort_col = array();
	    foreach ($arr as $key=> $row) {
	        $sort_col[$key] = $row[$col];
	    }

	    array_multisort(array_map('strtolower',$sort_col), $dir, $arr);
	}
	
	function get_subordinates()
	{
		if (is_null($this)) {
			$ci =& get_instance();
		} else {
			$ci = $this;
		}
		
		if ($ci->router->fetch_method() == 'edit') {
			$emp = $ci->db->get_Where('employee', array('employee_id' => $ci->user->user_id ))->row();
			$subs = $ci->hdicore->get_subordinates($ci->userinfo['position_id'], $emp->rank_id, $ci->user->user_id);

			$ci->array_sort_by_column($subs, 'firstname');

            return $subs;			
		} else {
			$ci->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$ci->db->select('user.*, user_company_department.department, user_company_department.department_id, position');
			$ci->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$ci->db->order_by('firstname');

			return $ci->db->get('user')->result_array();
		}
	}

	function change_status()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);			
		} elseif ($this->user_access[$this->module_id]['approve'] || $this->user_access[$this->module_id]['decline']) {
			$record_id = $this->input->post('record_id');

			$this->db->where($this->key_field, $record_id);
			$this->db->where('deleted', 0);

			$result = $this->db->get($this->module_table);

			if (!$result || $result->num_rows() == 0) {
				$response->msg 		= 'No record found.';
				$response->msg_type = 'notice';
			} elseif (
				(!$record = $result->row() 
					&& $this->userinfo['user_id'] != $record->approved_by)
				&& !$this->is_superadmin) {
				$response->msg      = 'You do not have access to this action.';
				$response->msg_type = 'error';
			} else {

				$results = $result->row();

				$this->db->where($this->key_field, $record_id);
				$this->db->update($this->module_table, 
				array(
					'updated_by'   => $this->userinfo['user_id'],
					'updated_date' => date('Y-m-d H:i:s'),
					'status'       => $this->input->post('status')
					)
				);


				$vl = 0;
				$sl = 0;

				if($results->vacation_leave != NULL ){
					$vl = $results->vacation_leave;
				
				}else{
					$vl = 0;
				}

				if($results->sick_leave != NULL ){
					$sl = $results->sick_leave;
				}else{
					$sl = 0;
				}

				$this->db->update('employee_leave_balance', array('vl' => $vl, 'sl' => $sl), array( 'employee_id' => $results->employee_id ));
				
				$response->msg      = 'Status changed.';
				$response->msg_type = 'success';
				
			}

			$this->load->view('template/ajax', array('json' => $response));

		} else {
			$this->session->set_flashdata('flashdata', 'Insufficient access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

		//$actions .= '<a class="icon-button" onClick="show_full_movement('.$record['employee_movement_id'].')" href="javascript:void(0)"></a>';
        
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] && $this->config->item('allow_edit_on_movement') == 1) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 

		/*if (CLIENT_DIR == "firstbalfour" || CLIENT_DIR == "oams"){        
	        if ($this->user_access[$this->module_id]['print'] 
	        		&& $record['employee_movement_type_id'] == '1') {
	            $actions .= '<a class="icon-button icon-16-print print-regularization" record_id="'.$record['employee_movement_id'].'" tooltip="Print Regularization Contract" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
	        }  			
		}
		else{
	         
    	}*/

    	if ($this->user_access[$this->module_id]['print']) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print Notice Of Personnel Action" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 

    	/*if (CLIENT_DIR == "oams" && $this->user_access[$this->module_id]['print'] ){
	        if($record['employee_movement_type_id'] == '10') {
	            $actions .= '<a class="icon-button icon-16-print print-end-contract" onClick="callBoxy('.$record['employee_movement_id'].')" record_id="'.$record['employee_movement_id'].'" tooltip="" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
	        }elseif ($record['employee_movement_type_id'] == '8' || $record['employee_movement_type_id'] == '13') {
	        	$actions .= '<a class="icon-button icon-16-print print-transfer" record_id="'.$record['employee_movement_id'].'" tooltip="" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)" movement = "'.$record['t1movement_type'].'"></a>';
	        }
	    }  

        if (CLIENT_DIR == "firstbalfour"){
	        if ($this->user_access[$this->module_id]['print'] 
	        		&& $record['employee_movement_type_id'] == '15') {
	            $actions .= '<a class="icon-button icon-16-print print-extension" record_id="'.$record['employee_movement_id'].'"  tooltip="Print Extension Contract" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
	        }  
    	}*/

        /*if ($this->user_access[$this->module_id]['print'] 
        		&& $record['employee_movement_type_id'] == '12') {
            $actions .= '<a class="icon-button icon-16-print print-floating" record_id="'.$record['employee_movement_id'].'" tooltip="Print Floating Employees" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }  */

        if ($this->user_access[$this->module_id]['post']) {
            $actions .= '<a class="icon-button icon-16-export export-icon" record_id="'.$record['employee_movement_id'].'" module_link="'.$module_link.'" href="javascript:void(0)" tooltip="Export Movement" original-title=""></a>';
        }        

        /*if ($this->config->item('client_number') == 3){
			if ($this->user_access[$this->module_id]['print']) {
				$actions .= '<a class="icon-button icon-16-print print-action-movement" tooltip="Print Personnel Action Movement" href="javascript:void(0)" record_id="'.$record['employee_movement_id'].'" module_link="' . $module_link . '" ></a>';
			}
		}*/

        if ($record['approved_by'] == $this->userinfo['user_id'] && $record['status_id'] == '2') {
			if ($this->user_access[$this->module_id]['approve'] ) {
         	   $actions .= '<a class="icon-button icon-16-approve" tooltip="Approve" href="javascript:void(0)"></a>';
        	}

        	if ($this->user_access[$this->module_id]['decline']) {
            	$actions .= '<a class="icon-button icon-16-disapprove" tooltip="Decline" href="javascript:void(0)"></a>';
        	}
        }
        
        if ($this->user_access[$this->module_id]['delete'] && $record['status_id'] != '6' && $record['status_id'] != '5' ) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ($this->user_access[$this->module_id]['post']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export' container='".$container."' module_link='".$module_link."' onClick='export_list()' href='javascript:void(0)'><span>Export</span></a></div>";
    	}
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	protected function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			if ($this->input->post('record_id') == '-1') {
				$update['created_date'] = date('Y-m-d H:i:s');
				$update['created_by']   = $this->userinfo['user_id'];
				$update['status']		= 1;
			}

			$update['updated_date'] = date('Y-m-d H:i:s');
			$update['updated_by']   = $this->userinfo['user_id'];
			//if(CLIENT_DIR == 'oams'){ $update['transfer_effectivity_date'] = $this->input->post('transfer_effectivity_date'); }
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $update);

		}

		$result = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();

		$employees = explode(',', $result->employee_id);

		$nature_movement = $result->employee_movement_type_id;

		if($nature_movement == 12)
		{

			$date = $result->floating_date_from;

			foreach($employees as $employee)
			{
				$is_floating = $this->hdicore->check_if_floating_period($employee, $date);

				if(!$is_floating) {
					$data = array(  'employee_id' => $employee,
									'date_from' => date('Y-m-d', strtotime($this->input->post('floating_date_from')))
									);

					$this->db->insert('employee_floating', $data);
				} 

			} 

		} else if($nature_movement == 13) {

			$date = $result->floating_date_to;

			foreach($employees as $employee)
			{

				$is_floating = $this->hdicore->check_if_floating_period($employee, $date);

				if($is_floating) {

					$date_to = date('Y-m-d', strtotime($this->input->post('floating_date_to')));

					$data = array('employee_id' => $employee,
								  'date_to' => $date_to
								  );

					$this->db->update('employee_floating', $data, array('employee_floating_id' => $is_floating->employee_floating_id));

				} else {

					$response['msg'] = 'One of the selected employee is not floating, refresh the page. they shouldn\'t be on the dropdown';
					$response['msg_type'] = 'attention';		
					$data['json'] = $response;
					$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
					return;

				}
			}

		} 
		else if ($nature_movement == 6 || $nature_movement == 7 || $nature_movement == 10 || $nature_movement == 11)
		{
					
			$this->db->select('employee_clearance_form_checklist_id');
			$d_signatories = $this->db->get_where('employee_clearance_form_checklist', array('deleted' => 0, 'default' => 1))->result_array();
			 
			$signatories = array();
			foreach ($d_signatories as $signatory) {
				$signatories[]=$signatory['employee_clearance_form_checklist_id'];
			}
			$data = array('employee_id' => $result->employee_id,
							'status' => '1',
							'turn_around_time' => date('Y-m-d'),
							'signatories' => implode(',', $signatories)
								  );
			$this->db->insert('employee_clearance', $data);
			
		}	

		parent::after_ajax_save();
	}

	// protected function _set_filter()
	// {
	// 	if (!$this->is_superadmin && !$this->is_admin && $this->user_access[$module_id]['post'] != 1) {
	// 		$this->db->where(
	// 			'(approved_by = ' . $this->userinfo['user_id'] 
	// 				. ' OR created_by = ' . $this->userinfo['user_id']
	// 				. ' OR updated_by = ' . $this->userinfo['user_id'] . ')'
	// 			);				
	// 	}
	// }	

	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{
			$column = strtolower( $search['column'] );
			if(sizeof(explode(' as ', $column)) > 1){
				$as_part = explode(' as ', $column);
				$search['column'] = strtolower( trim( $as_part[0] ) );
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
		}		

		$like = array(
			'firstname' => $value,
			'lastname' => $value
			);

		$this->db->or_like($like);
		$users = $this->db->get('user');

		if ($users->num_rows() > 0) {
			foreach ($users->result() as $user) {
				$uid[] = $user->user_id;
			}

			$search_string[] = $this->db->dbprefix . 'employee_movement.employee_id REGEXP ("(^|,)'.implode('|', $uid) . '(,|$)")';
		}

		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	protected function _append_to_select()
	{
		$this->listview_qry .= ',user.firstname,employee_movement.employee_movement_type_id, employee_movement.status as status_id, approved_by, created_by';
	}

	function _set_left_join()
	{
		$this->db->join('user', 'employee_movement.employee_id = user.employee_id');
		parent::_set_left_join();
	}

	function get_payroll() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('salary');
			$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('deleted', 0);
			$this->db->limit(1);
			
			$payroll = $this->db->get('employee_payroll');

			$response->query = $this->db->last_query();

			if (!$payroll|| $payroll->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'There are no available leaves found.';
				$response->num_rows = $payroll->num_rows();

			} else {
				$response->msg_type = 'success';
				$response->num_rows = $payroll->num_rows();
				$response->data = $payroll->row_array();
			}
						
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_leaves() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('lb.employee_id, lb.vl, lb.sl, lb.el, lb.mpl, lb.bl');
			$this->db->from('employee_leave_balance lb');
			$this->db->join('user u', 'lb.employee_id = u.employee_id', 'left');
			$this->db->where('lb.employee_id', $this->input->post('employee_id'));
			$this->db->where('lb.deleted', 0);
			$this->db->where('lb.year = YEAR(NOW())');
			$this->db->limit(1);

			$leaves = $this->db->get('user');

			$response->query = $this->db->last_query();

			if (!$leaves|| $leaves->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'There are no available leaves found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $leaves->row_array();
			}
						
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_employee_movement_type() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('employee_movement_type_id');
			$this->db->where('employee_movement_id', $this->input->post('record_id'));
			$this->db->where('deleted', 0);
			$this->db->limit(1);
			
			$movement = $this->db->get('employee_movement');

			$response->query = $this->db->last_query();

			if (!$movement|| $movement->num_rows() == 0) {
				$response->num_rows = $movement->num_rows();
			} else {
				$response->msg_type = 'success';
				$response->num_rows = $movement->num_rows();
				$response->data = $movement->row_array();
			}
						
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_current_benefits() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['edit'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			$record_id = $this->input->post('record_id');

			if( $record_id != -1){

				$this->db->select('MAX(eb.updated_date) as date, eb.benefit_id as benefit_id, eb.value as value, b.benefit as benefit, emb.value as em_benefit_value',FALSE);
				$this->db->from('employee_benefit eb');
				$this->db->join('benefit b', 'b.benefit_id = eb.benefit_id', 'left');
				$this->db->join('employee_movement_benefit emb', 'emb.benefit_id = eb.benefit_id', 'left');
				$this->db->where('eb.employee_id', $this->input->post('employee_id'));
				$this->db->where('emb.employee_movement_id',$record_id);
				$this->db->order_by('benefit');
				$this->db->group_by('eb.benefit_id');

			}
			else{

				$this->db->select('MAX(eb.updated_date) as date, eb.benefit_id as benefit_id, eb.value as value, b.benefit as benefit');
				$this->db->from('employee_benefit eb');
				$this->db->join('benefit b', 'b.benefit_id = eb.benefit_id', 'left');
				$this->db->where('eb.employee_id', $this->input->post('employee_id'));
				$this->db->order_by('benefit');
				$this->db->group_by('eb.benefit_id');

			}

			$employee_benefits = $this->db->get();

			$response->num_rows = $employee_benefits->num_rows();

			if (!$employee_benefits || $employee_benefits->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'No available benefits';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee_benefits->row_array();
				$response->array_data = $employee_benefits->result_array;

			}			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_employee() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('employee_payroll.salary,user_company_segment_1.segment_1, user_company_segment_2.segment_2, user_location.location, user_company_division.division, user_rank_code.job_rank_code, user_rank_range.job_rank_range, user_rank_level.rank_level AS desc_job_level, employee_type.employee_type AS curr_employee_type, user_rank.job_rank, employee.*, user.user_id, user.firstname, user.lastname, position, department, user.position_id, user.department_id, user_company.company, user.role_id AS current_role_id, employment_status.employment_status, user.segment_1_id, user.segment_2_id, user.company_id, employee.job_level');

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

				$response->data['approvers'] = $approvers;
			}
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_movement_type() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->where('employee_movement_type_id',$this->input->post('employee_movement_type_id'));
			$employee = $this->db->get('employee_movement_type');
			

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'Employee movement type not found';
			} else {
				$response->msg_type = 'success';
				$response->data = $employee->row_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_position_type() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->where('position_id',$this->input->post('position_id'));
			$employee = $this->db->get('user_position');
			
			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'hmm';
			} else {
				$response->msg_type = 'success';
				$response->data = $employee->row_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	//get division segment
	function get_division_segment() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('department_id', $this->input->post('department_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('user_company_department');

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= $this->input->post('department_id');
			} else {
				$response->msg_type = 'success';
				$response->data = $employee->row_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}
	//get division segment

	function get_rank_range(){

		if(IS_AJAX){

			$id = $this->input->post('rank_level');

			if($id == ""){
				$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('record_id') ));
				$record = $employee->row();
				$id = $record->job_level;
			}

			$this->db->from('user_rank_level r');
			$this->db->join('user_rank_range rr','r.rank_range_id = rr.job_rank_range_id','left');
			$this->db->where( array( 'r.rank_level_id' => $id ) );
			$result = $this->db->get();

			$data = $result->row_array();

			$this->load->view('template/ajax', array('json' => $data));


		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

	}

	function get_employee_type(){

		if(IS_AJAX){

			$id = $this->input->post('rank_id');

			if($id == ""){
				$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('record_id') ));

				$record = $employee->row();
				$id = $record->rank_id;
			}


			$this->db->from('user_rank r');
			$this->db->join('employee_type e','r.employee_type = e.employee_type_id','left');
			$this->db->where( array( 'r.job_rank_id' => $id ) );
			$result = $this->db->get();

			$data = $result->row_array();

			$this->load->view('template/ajax', array('json' => $data));


		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

	}

	function add_benefit_field(){
		$response->field = $this->load->view( $this->userinfo['rtheme'].'/movement_compensation/benefit_fields', array('benefit_id' => $this->input->post('benefit_id'), 'benefit_values' => $this->input->post('benefit_values')), true );
		$response->selected_benefits = $this->input->post('selected_benefits');
		if( ! empty( $response->selected_benefits ) )
			$response->selected_benefits .= ',' . $this->input->post('benefit_id');
		else
			$response->selected_benefits = $this->input->post('benefit_id');

		$this->db->where('deleted', 0);
		if ($response->selected_benefits != ''){
			$this->db->where('benefit_id not in ('. $response->selected_benefits .')');
		}
		$benefits = $this->db->get('benefit');
	   	$response->benefitddlb = '<option value="">Select...</option>';
		  foreach($benefits->result() as $benefit):
	      $response->benefitddlb .= '<option value="'.$benefit->benefit_id.'">'.$benefit->benefit.'</option>';
	    endforeach;
			
			$this->load->view('template/ajax', array('json' => $response));
	}

	function show_option_benefit(){
	   	$response->benefitddlb = '<option value="">Select...</option>';
	   			
		$this->db->order_by('benefit');
		$this->db->where('deleted', 0);
		$benefits = $this->db->get('benefit');

		foreach($benefits->result() as $benefit):
	      $response->benefitddlb .= '<option value="'.$benefit->benefit_id.'">'.$benefit->benefit.'</option>';
	    endforeach;
		
		$this->load->view('template/ajax', array('json' => $response));
	}

	function pro_rate_leaves()
	{
		$request = $this->db->get_where('employee', array("employee_id" => $this->input->post('employee_id')));

		if($request->num_rows() > 0)
		{
			$request = $request->row();

			$promotion_d = strtotime($this->input->post('transfer_effectivity_date'));

			// if promoted from sup to officer give full credit. ticket 490
			if(trim($this->input->post('current_employee_type_dummy')) == 'Supervisor' && trim($this->input->post('new_employee_type')) == 1)
			{
				$this->db->select('et.*, ef.application_code');
				$this->db->from('employee_type_leave_setup et');
				$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
				$this->db->where('employee_type_id', $this->input->post('new_employee_type'));
				$this->db->where('ef.deleted', 0);
				$leave_setup = $this->db->get();

				$data['year'] = date('Y', $promotion_d);
				$data['employee_id'] = $request->employee_id;
				foreach ($leave_setup->result() as $leave_type)
					$data[strtolower($leave_type->application_code)] = $leave_type->base;
				// $data = $this->_remove_leaves_used($data, $this->input->post('employee_id'), date('Y'));
			} else {
				// Add leave credits, use leave setup based on employee type.
				$this->db->select('et.*, ef.application_code, et.application_form_id AS "app_form_id"');
				$this->db->from('employee_type_leave_setup et');
				$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
				$this->db->where('employee_type_id', $this->input->post('new_employee_type'));
				$this->db->where('ef.deleted', 0);
				
				$leave_setup = $this->db->get();

				$date_diff = gregoriantojd(12, 31, date('Y', $promotion_d)) - gregoriantojd(date('m', $promotion_d), date('d', $promotion_d), date('Y', $promotion_d));

				$prev_et_date_diff = gregoriantojd(date('m', $promotion_d), date('d', $promotion_d), date('Y', $promotion_d)) - gregoriantojd(1, 1, date('Y', $promotion_d));

				if ($leave_setup->num_rows() > 0) {
					$data['year'] = date('Y', $promotion_d);
					foreach ($leave_setup->result() as $leave_type) {					
						if (!$leave_type->prorated) {
							$data[strtolower($leave_type->application_code)] = $leave_type->base;
						} else {
							// Formula for pro-rated
							$monthly = $leave_type->base / 365;
							$days_remaining = $monthly * $date_diff;
							$credits = round($days_remaining * 2) / 2;

							$prev_etype = $this->system->get_old_id_on_hidden($this->input->post('hidden_id_current'), 'employee_type');

							$this->db->select('et.*, ef.application_code');
							$this->db->from('employee_type_leave_setup et');
							$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
							$this->db->where('employee_type_id', $prev_etype);
							$this->db->where('et.application_form_id', $leave_type->app_form_id);
							$this->db->where('ef.deleted', 0);

							$old_leave_setup = $this->db->get()->row();

							$monthly = $old_leave_setup->base / 365;
							$days_remaining = $monthly * $prev_et_date_diff;
							$prev_et_credits = round($days_remaining * 2) / 2;

							$data[strtolower($leave_type->application_code)] = $credits + $prev_et_credits;

						}
					}
					$data['employee_id'] = $request->employee_id;
				}

				// $data = $this->_remove_leaves_used($data, $this->input->post('employee_id'), date('Y'));
			}
		} else {
			$data['year'] = "";
			$data['vl'] = "";
			$data['sl'] = "";
			$data['el'] = "";
		}

		$this->load->view('template/ajax', array('json' => $data));
	}

	function pro_rate_leaves_regularization()
	{
		$hired_date = $this->db->get_where('employee', array("employee_id" => $this->input->post('employee_id')))->row();
		$hired_date = $this->db->get_where('employee', array("employee_id" => $this->input->post('employee_id')))->row();

		$leave_tenure = $this->db->get_where('employee_type_leave_setup', array("employee_type_id" => $hired_date->employee_type, "deleted" => 0, "tenure <>" => 0));

		if($leave_tenure->num_rows() > 0)
			$vl_allowed = date("Y-m-d", strtotime(date("Y-m-d", strtotime($hired_date->employed_date)) . " + ".$leave_tenure->row()->tenure." month"));
		else
			$vl_allowed = "0000-00-00";

		if($vl_allowed <= date('Y-m-d'))
		{
			$reg_d = strtotime($this->input->post('transfer_effectivity_date'));
			// Add leave credits, use leave setup based on employee type.
			$this->db->select('et.*, ef.application_code');
			$this->db->from('employee_type_leave_setup et');
			$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
			$this->db->where('employee_type_id', $hired_date->employee_type);
			$this->db->where('ef.deleted', 0);

			$leave_setup = $this->db->get();

			$date_diff = gregoriantojd(12, 31, date('Y', $reg_d)) - gregoriantojd(date('m', $reg_d), date('d', $reg_d), date('Y', $reg_d));

			if ($leave_setup->num_rows() > 0) {
				$data['year'] = date('Y', $reg_d);
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

				$data['employee_id'] = $this->input->post('employee_id');
				// $data = $this->_remove_leaves_used($data, $this->input->post('employee_id'), date('Y', $reg_d));
			}
		}

		$this->load->view('template/ajax', array('json' => $data));
	}

	function pro_rate_leaves_resignation()
	{
		$emp_current = $this->db->get_where('employee', array("employee_id" => $this->input->post('employee_id')))->row();
		//if not regular, should not have credits.
		if($emp_current->status_id == 1)
		{
			$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('deleted', 0);
			$request = $this->db->get('employee');

			if($request && $request->num_rows() > 0)
			{
				$request = $request->row();
				// Pro-rate leave credits, use leave setup based on employee type.
				$this->db->select('et.*, ef.application_code');
				$this->db->from('employee_type_leave_setup et');
				$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
				$this->db->where('employee_type_id', $request->employee_type);
				$this->db->where('et.prorated', 1);
				$this->db->where('ef.deleted', 0);
				
				$leave_setup = $this->db->get();

				$ld = strtotime($this->input->post('transfer_effectivity_date'));
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
					// $data = $this->_remove_leaves_used($data, $this->input->post('employee_id'), date('Y', $ld));
				}	
			}
		}

		$this->load->view('template/ajax', array('json' => $data));
	}

	private function _remove_leaves_used($data, $emp_id = null, $year = "")
	{
		if($emp_id != null && count($data) > 0 && $year != "")
		{
			$previous_balance = $this->db->get_where('employee_leave_balance', array("employee_id" => $emp_id, "year" => $year));
			if($previous_balance && $previous_balance->num_rows() > 0)
			{
				$previous_balance = $previous_balance->row();
				$data['vl'] = $data['vl'] - $previous_balance->vl_used;
				$data['sl'] = $data['sl'] - $previous_balance->sl_used;
				$data['el'] = $data['el'] - $previous_balance->el_used;
				return $data;
			} else
				return $data;
		} else
			return $data;
	}

	function get_company_departments() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$company_id = $this->input->post('company_id');

			if ($company_id > 0) {
				$this->db->where('company_id', $company_id);
				$this->db->order_by('department', 'asc');
				$employee = $this->db->get('user_company_department');
			}
			if (!$employee || $employee->num_rows() == 0) {
				$this->db->order_by('department', 'asc');
				$employee=$this->db->get('user_company_department');
				$response->msg_type = 'error';
				$response->data = $employee->result_array();
			} else {
				$response->msg_type = 'success';
				$response->data = $employee->result_array();
			}
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function print_record($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		$template = $this->template->get_module_template($this->module_id, 'movement_report' );
		
		$check_record = $this->_record_exist($record_id);
		$this->load->library('parser');


		if ($check_record->exist) {
			$details = get_record_detail_array($record_id);
			$movement = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();

			$vars = $this->system->get_employee($movement->employee_id);
			$vars['date_prepared']  = date('F d, Y');
			$vars['date_hired'] 	= date('F d, Y', strtotime($vars['employed_date']));
			$vars['effectivity'] 	= date('F d, Y', strtotime($movement->transfer_effectivity_date));
			$vars['location'] = '';
			$vars['section'] = '';

			if ($vars['location_id'] > 0) {
				$user_location = $this->db->get_where('user_location', array('location_id' => $vars['location_id']))->row();
				$vars['location'] = $user_location->location;
			}

			if ($vars['section_id'] > 0) {
				$user_section = $this->db->get_where('user_section', array('section_id' => $vars['section_id']))->row();
				$vars['section'] = $user_section->section;
			}

			$logo_2 = get_branding();
			$company_id = $vars['company_id'];
			$company_qry = $this->db->get_where('user_company', array('company_id' => $company_id))->row();
			if(!empty($company_qry->logo)) {
			  $logo_2 = '<img alt="" src="./'.$company_qry->logo.'">';
			}
			$vars['logo_2'] =  $logo_2;
			
			$this->db->join('user', 'user.user_id=user_company_division.division_manager_id', 'left');
			$this->db->where('user_company_division.division_id',$vars['division_id']);
			$this->db->where('user_company_division.deleted',0);
			$division = $this->db->get('user_company_division');
			// dbug($this->db->last_query());
			if ($division && $division->num_rows() > 0){
				$vars['division_head'] =  $division->row()->firstname.' '.$division->row()->middleinitial.' '.$division->row()->lastname;
			}else{
				$vars['division_head'] = '';
			}

			$vars['movement_type'] = str_replace(', ', '<br>', $details['employee_movement_type_id']);

			$vars['from'] = '';
			$vars['to'] = '';
		
			$vars['new_position_id'] 			= ($details['new_position_id']) ? $details['new_position_id'] : ' - ';
			$vars['transfer_to'] 				= ($details['transfer_to']) ? $details['transfer_to'] : ' - ';
			$vars['new_shift_calendar_id'] 		= ($details['new_shift_calendar_id']) ? $details['new_shift_calendar_id'] : ' - ';
			$vars['rank_id'] 					= ($details['rank_id']) ? $details['rank_id'] : ' - ';
			$vars['new_employment_status_id'] 	= ($details['new_employment_status_id']) ? $details['new_employment_status_id'] : ' - ';
			$vars['division_id'] 				= ($details['division_id']) ? $details['division_id'] : ' - ';
			$vars['group_name_id'] 				= ($details['group_name_id']) ? $details['group_name_id'] : ' - ';
			$vars['location_id'] 				= ($details['location_id']) ? $details['location_id'] : ' - ';
			$vars['role'] 						= ($details['role']) ? $details['role'] : ' - ';
			$vars['employee_reporting_to'] 		= ($details['employee_reporting_to']) ? $details['employee_reporting_to'] : ' - ';

			$vars['current_employment_status'] 	= ($details['current_employment_status']) ? $details['current_employment_status'] : ' - ';
			$vars['current_position_id'] 		= ($details['current_position_id']) ? $details['current_position_id'] : ' - ';
			$vars['current_position'] 			= ($details['current_position_id']) ? $details['current_position_id'] : ' - ';
			$vars['current_department_dummy'] 	= ($details['current_department_dummy']) ? $details['current_department_dummy'] : ' - ';
			$vars['current_shift_calendar_id'] 	= ($details['current_shift_calendar_id']) ? $details['current_shift_calendar_id'] : ' - ';
			$vars['current_rank_dummy'] 		= ($details['current_rank_dummy']) ? $details['current_rank_dummy'] : ' - ';
			$vars['current_division_dummy'] 	= ($details['current_division_dummy']) ? $details['current_division_dummy'] : ' - ';
			$vars['current_group_name_id'] 		= ($details['current_group_name_id']) ? $details['current_group_name_id'] : ' - ';
			// $vars['current_code_status_id'] 	= $details['current_code_status_id'];
			$vars['current_location_dummy'] 	= ($details['current_location_dummy']) ? $details['current_location_dummy'] : ' - ';			
			$vars['current_role'] 				= ($details['current_role']) ? $details['current_role'] : ' - ';	
			$vars['current_employee_reporting_to']	= ($details['current_employee_reporting_to']) ? $details['current_employee_reporting_to'] : ' - ';	

			$vars['current_basic_salary'] =  ($details['current_basic_salary']) ? number_format($details['current_basic_salary'], 2, '.', ',' ) : ' - ';
			$vars['new_basic_salary'] = ($details['new_basic_salary']) ? number_format($details['new_basic_salary'], 2, '.', ',' )  : ' - ';

			$vars['reg_from'] = '';
			$vars['reg_to'] = '';
			if ($vars['movement_type'] == 'Regularization'){
				$vars['reg_from'] = 'Probationary';
				$vars['reg_to'] = 'Regular';
			}

			if ($details['new_position_id'] == ''){
				$vars['new_position_id'] = '';
				$vars['current_position_id'] = '';
			}

			if ($details['transfer_to'] == ''){
				$vars['current_department_dummy'] = '';
				$vars['transfer_to'] = '';
			}

			if ($details['new_shift_calendar_id'] == ''){
				$vars['current_shift_calendar_id'] = '';
				$vars['new_shift_calendar_id'] = '';
			}

			if ($details['rank_id'] == ''){
				$vars['rank_id'] = '';
				$vars['current_rank_dummy'] = '';
			}

			if ($details['new_employment_status_id'] == ''){
				$vars['current_employment_status'] = '';
				$vars['new_employment_status_id'] = '';
			}

			if ($details['division_id'] == ''){
				$vars['current_division_dummy'] = '';
				$vars['division_id'] = '';
			}

			if ($details['group_name_id'] == ''){
				$vars['current_group_name_id'] = '';
				$vars['group_name_id'] = '';
			}

			if ($details['location_id'] == ''){
				$vars['current_location_dummy'] = '';
				$vars['location_id'] = '';
			}

			if ($details['role'] == ''){
				$vars['current_role'] = '';
				$vars['role'] = '';
			}

			if ($details['employee_reporting_to'] == '' || $details['current_employee_reporting_to'] == $details['employee_reporting_to']){
				$vars['current_employee_reporting_to'] = '';
				$vars['employee_reporting_to'] = '';
			}
																		
			if ($details['new_basic_salary'] == 0 || $details['new_basic_salary'] == ''){
				$vars['new_basic_salary'] = '';
				$vars['current_basic_salary'] = '';
			}

			$this->db->join('user', 'user.position_id=user_position.position_id', 'left');
			$hr_pos = $this->db->get_where('user_position', array('user_position.position_id' => 409, 'user.deleted' => 0));
			
			$vars['hr_head_position'] = ($hr_pos && $hr_pos->num_rows() > 0) ? $hr_pos->row()->position : '';
			$vars['hr_head'] = ($hr_pos && $hr_pos->num_rows() > 0) ? $hr_pos->row()->firstname.' '.$hr_pos->row()->middleinitial.' '.$hr_pos->row()->lastname : '';


			$this->db->join('user', 'user.user_id=user_company_department.dm_user_id', 'left');
			$this->db->where('user_company_department.department_id',$vars['department_id']);
			$this->db->where('user_company_department.deleted',0);
			$department = $this->db->get('user_company_department');
			
			if ($department && $department->num_rows() > 0){
				$vars['department_head'] =  $department->row()->firstname.' '.$department->row()->middleinitial.' '.$department->row()->lastname;
			}else{
				$vars['department_head'] = "";
			}


			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.			
			// $this->pdf->setPrintHeader(TRUE);
			// $this->pdf->SetAutoPageBreak(true, 25.4);
			// $this->pdf->SetMargins( 19.05, 38.1 );
			$this->pdf->addPage('P', 'LETTER', true);					
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	

	function print_recall($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		//default template
		$tpl_file = 'regularization_contract';
	
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		if( $this->uri->rsegment(4)){
			$template = $this->template->get_template( $this->uri->rsegment(4) );	
			if (CLIENT_DIR == "oams") {
				$template = $this->template->get_module_template($this->module_id, $this->uri->rsegment(4));	
			}
		}
		else{
			$template = $this->template->get_module_template($this->module_id, $tpl_file );
		}
		

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		$this->load->library('parser');


		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$total = $vars['basic'];
			$tax = $total * .02;
			$vars['subject'] = "Notice of Recall and Campaign Transfer";
			$vars['tax'] = number_format( $tax, 2, '.', ',' );
			$vars['basic'] = number_format( $vars['basic'], 2, '.', ',' );
			$vars['date'] = date( $this->config->item('display_date_format') );
			$vars['current_date'] = date('M. d, Y');
			$vars['fancy_date'] = date('jS \d\a\y \o\f F Y');
			$vars['time'] = date( $this->config->item('display_time_format') );
			$jo = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			$candidate = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $jo->candidate_id ))->row();
			$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $candidate->applicant_id ))->row();
			$vars['address'] = $applicant->pres_address1;

			if( !empty($applicant->pres_address2) ) $vars['address'] .= '<br/>'. $applicant->pres_address2;
			if( !empty($applicant->pres_city) ) $vars['address'] .= '<br/>'. $applicant->pres_city;
			if( !empty($applicant->province) ) $vars['address'] .= ', '. $applicant->province;
			if( !empty($applicant->zipcode) ) $vars['address'] .= ' '. $applicant->zipcode;
			if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$vars['sss'] = $applicant->sss;
			$vars['tin'] = $applicant->tin;
			$vars['philhealth'] = $applicant->philhealth;
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate->mrf_id ))->row();
			$campaign = $this->db->get_where('campaign', array('campaign_id' => $mrf->campaign_id ))->row();
			$vars['campaign'] = $campaign->campaign;
			$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
			$company = $this->db->get_where('user_company', array('company_id' => $position->company_id))->row();
			
			$meta = $this->config->item('meta');

			$vars['company'] = $meta['description'];
			$vars['position'] = $position->position;
			$vars['date_start'] = $vars['date_from'] = date( $this->config->item('display_date_format'), strtotime($jo->date_from) );
			$vars['date_end'] = $vars['date_to'] = date( $this->config->item('display_date_format'), strtotime($jo->date_to) );
			$facilitator = $this->hdicore->_get_userinfo( $this->user->user_id );
			$vars['facilitated_by'] = $facilitator->firstname.' '.$facilitator->lastname;
			$vars['allowances'] = "";
			$vars['premiums'] = "";
			$vars['duties_responsibilities']   = $position->duties_responsibilities;
			$vars['allowance_total'] = 0;
			$vars['premium_total'] = 0;
			$vars['total_plus_premium'] = 0;
			$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $jo->job_offer_id));
			if( $benefits->num_rows() > 0 ){
				$asterisk = '*';

				foreach( $benefits->result() as $benefit ){
					$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
					switch($benefit_detail->benefit_type_id){
						case 1:
							$vars['allowance_total'] += $benefit->value;
							$vars['allowances'] .= '<tr>
									<td style="width: 50%;"><em>'.$benefit_detail->benefit.'</em></td>
									<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
								</tr>';
						break;
						case 2:
							$vars['premium_total'] += $benefit->value;
							$vars['premiums'] .= '<tr>
									<td style="width: 50%;"><em>'.$benefit_detail->benefit. $asterisk . '</em></td>
									<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
								</tr>';

							$conditions[] = $asterisk . ' ' . $this->parser->parse_string($benefit_detail->description, $benefit);
							$asterisk .= '*';
						break;
					}
				}
			}

			if(!empty( $vars['premium_total'] )){
				$vars['premiums'] = '<tr>
									<td style="width: 50%;"><strong><em>Total Premium</em></strong></td>
									<td style="width: 30%;" align="right"><strong>'. number_format( $vars['premium_total'], 2, '.', ',') .'</strong></td>
								</tr>' . $vars['premiums'];
			}
			
			$total += $vars['allowance_total'];

			$vars['total'] = number_format( $total, 2, '.', ',' );
			$vars['allowance_total'] = number_format($vars['allowance_total'], 2, '.', ',');
			
			$vars['total_plus_premium'] = number_format( ($total + $vars['premium_total']), 2, '.', ',' );
			$vars['premium_total'] = number_format( $vars['premium_total'], 2, '.', ',' );					

			$vars['conditions'] = '';
			if ($vars['premium_total'] > 0) {
				$vars['conditions'] = '<strong>Conditions</strong>:<br/><br/>';
			
				foreach ($conditions as $condition) {
					$vars['conditions'] .= '<br/>' . $this->parser->parse_string($condition, $vars);
				}
			}


			$movement = $this->db->get_where('employee_movement', array('employee_movement_id' => $record_id));
			if ($movement && $movement->num_rows > 0) {
				$movement = $movement->row_array();
				// dbug($movement);
				$where_in = $this->db->prefix.'user.employee_id IN('.$movement['employee_id'].')';

				$this->db->join('user_position','user_position.position_id=user.position_id');
				$this->db->join('user_company_department','user_company_department.department_id=user.department_id');
				$this->db->join('employee','employee.employee_id=user.employee_id');
				$this->db->join('campaign','employee.campaign_id=campaign.campaign_id');
				$this->db->where($where_in);
				$results = $this->db->get('user');

	        	if($results && $results->num_rows() > 0) {
	        		// $result = $result->row_array();

	        		foreach ($results->result_array() as $result) {

						$vars['receiver_name'] = $result['salutation']." ".$result['firstname']." ".substr($result['middlename'], 0, 1).". ".str_replace('*', ' ', $result['lastname']);
						$vars['firstname'] = $result['firstname'];
						$vars['middlename'] = $result['middlename'];
						$vars['lastname'] = $result['lastname'];
						$vars['employee_name'] = $result['firstname']." ".substr($result['middlename'], 0, 1).". ".str_replace('*', ' ', $result['lastname']);
						$vars['candidate_id'] = $result['firstname']." ".substr($result['middlename'], 0, 1).". ".str_replace('*', ' ', $result['lastname']);

						if($result['pres_city'] != 0 || $result['pres_city'] != null)
							$city=$this->db->get_where('cities', array('city_id' => $result['pres_city']))->row();

						if( !empty($result['pres_address1']) ) $vars['address'] .= '<br/>'. $result['pres_address1'];
						if( !empty($result['pres_address2']) ) $vars['address'] .= '<br/>'. $result['pres_address2'];
						if( !empty($city->city) ) $vars['address'] .= '<br/>'. $city->city;
						if( !empty($result['pres_province']) ) $vars['address'] .= ', '. $result['pres_province'];
						if( !empty($result['pres_zipcode']) ) $vars['address'] .= ' '. $result['pres_zipcode'];
						if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

						
						if($movement['new_basic_salary'] == 0 || $movement['new_basic_salary'] == null || trim($movement['new_basic_salary']) == "")
							$movement['new_basic_salary'] = $movement['current_basic_salary'];

						$vars['position'] = $result['position'];
						$vars['old_position'] = $result['position'];
			

						if ($movement['new_position_id'] != "" || $movement['new_position_id'] != null) {
							$position = $this->db->get_where('user_position', array('position_id' => $movement['new_position_id']));
							if ($position && $position->num_rows() > 0) {
								$position = $position->row();
								$vars['position'] =  $position->position;
							}
						}

						$vars['old_department'] = $result['department'];
						$vars['department'] = $result['department'];

						if ($movement['recall_department'] != "" || $movement['recall_department'] != null) {
							$department = $this->db->get_where('user_company_department', array('department_id' => $movement['recall_department']));
							if ($department && $department->num_rows() > 0) {
								$department = $department->row();
								$vars['department'] = $department->department;
							}
						}

						$vars['old_campaign'] = $result['campaign'];
						$vars['campaign'] = $result['campaign'];

						if ($movement['recall_campaign'] != "" || $movement['recall_campaign'] != null) {
							$campaign = $this->db->get_where('campaign', array('campaign_id' => $movement['recall_campaign']));
							if ($campaign && $campaign->num_rows > 0) {
								$campaign = $campaign->row();
								$vars['campaign'] = $campaign->campaign;
							}
						}

						$vars['description'] = $result['description'];
						$vars['duties_responsibilities'] = $result['duties_responsibilities'];
						$vars['effectivity_date'] = date('M. d, Y', strtotime($movement['transfer_effectivity_date']) ); 
						$vars['date_start'] = date('M. d, Y', strtotime($movement['transfer_effectivity_date'])); 
						$vars['id_number'] = $result['id_number'];
						
						
						$benefits = $this->db->get_where('employee_movement_benefit', array('employee_movement_id' => $record_id ));
						if( $benefits->num_rows() > 0 ){
							$asterisk = '*';

							foreach( $benefits->result() as $benefit ){
								$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
								switch($benefit_detail->benefit_type_id){
									case 1:
										$vars['allowance_total'] += $benefit->value;
										$vars['allowances'] .= '<tr>
												<td style="width: 50%;"><em>'.$benefit_detail->benefit.'</em></td>
												<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
											</tr>';
									break;
									case 2:
										$vars['premium_total'] += $benefit->value;
										$vars['premiums'] .= '<tr>
												<td style="width: 50%;"><em>'.$benefit_detail->benefit. $asterisk . '</em></td>
												<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
											</tr>';

										$conditions[] = $asterisk . ' ' . $this->parser->parse_string($benefit_detail->description, $benefit);
										$asterisk .= '*';
									break;
								}
							}
						}

						$total = $vars['allowance_total'] + $vars['new_basic_salary'];

						$vars['total'] = number_format( $total, 2, '.', ',' );
						$vars['allowance_total'] = number_format($vars['allowance_total'],2,'.',',');
						$vars['new_basic_salary'] = number_format($vars['new_basic_salary'],2,'.',',');
						
						$html = $this->template->prep_message($template['body'], $vars, false, true);
							// Prepare and output the PDF.			
						$this->pdf->setPrintHeader(TRUE);
						$this->pdf->SetAutoPageBreak(true, 25.4);
						$this->pdf->SetMargins( 19.05, 38.1 );
						$this->pdf->addPage('P', 'LETTER', true);					
						$this->pdf->writeHTML($html, true, false, true, false, '');
					}
				}


			}
			// Prepare and output the PDF.			
			// $this->pdf->setPrintHeader(TRUE);
			// $this->pdf->SetAutoPageBreak(true, 25.4);
			// $this->pdf->SetMargins( 19.05, 38.1 );
			// $this->pdf->addPage('P', 'LETTER', true);					
			// $this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	

	function print_action_movement($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		//default template
		$tpl_file = 'personnel_action_movement';
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'personnel_action_movement' );
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		
		$this->load->library('parser');

		if ($check_record->exist) {
			$records_detail = get_record_detail_array($record_id);

			$this->db->select('employee_movement.employee_movement_type_id,employed_date,id_number,description');
			$this->db->where('employee_movement.deleted',0);
			$this->db->where('employee_movement_id',$record_id);
			$this->db->join('user','employee_movement.employee_id = user.user_id');
			$this->db->join('employee','user.employee_id = employee.employee_id');
			$this->db->join('employee_movement_type','employee_movement.employee_movement_type_id = employee_movement_type.employee_movement_type_id');
			$result_info_movement = $this->db->get('employee_movement')->row_array();

			$movement_type_array = explode(',', $result_info_movement['employee_movement_type_id']);

			$vars['employee_id'] = $records_detail['employee_id'];
			$vars['employed_date'] = $result_info_movement['employed_date'];
			$vars['id_number'] = $result_info_movement['id_number'];

			$personnel_action_movement_html = '<tr>
					<td style="padding-left:30px;border-right:1px solid black;">('.(in_array(6, $movement_type_array) ? "x" : " ").') Regularization</td>
					<td style="padding-left:30px;">('.(in_array(8, $movement_type_array) ? "x" : " ").') Transfer/Relocation</td>
				</tr>
				<tr>
					<td style="padding-left:30px;border-right:1px solid black">('.(in_array(3, $movement_type_array) ? "x" : " ").') Promotion</td>
					<td style="padding-left:30px">('.(in_array(12, $movement_type_array) ? "x" : " ").') Temporary Assignment</td>
				</tr>
				<tr>
					<td style="padding-left:30px;border-right:1px solid black">('.(in_array(9, $movement_type_array) ? "x" : " ").') Probationary Appointment</td>
					<td style="padding-left:30px">&nbsp;</td>
				</tr>
				<tr>
					<td style="padding-left:30px;border-right:1px solid black">('.(in_array(11, $movement_type_array) ? "x" : " ").') Retirement</td>
					<td style="padding-left:30px">&nbsp;</td>
				</tr>
				<tr>
					<td style="padding-left:30px;border-right:1px solid black">('.(in_array(2, $movement_type_array) ? "x" : " ").') Salary Adjustment/Merit Increase</td>
					<td style="padding-left:30px">&nbsp;</td>
				</tr>			
				<tr>
					<td style="padding-left:30px;border-right:1px solid black">('.(in_array(10, $movement_type_array) ? "x" : " ").') End Contract/Resignation</td>
					<td style="padding-left:30px">&nbsp;</td>
				</tr>			
				<tr>
					<td style="padding-left:30px;border-right:1px solid black">('.(in_array(7, $movement_type_array) ? "x" : " ").') Termination</td>
					<td style="padding-left:30px">&nbsp;</td>
				</tr>';
			
			$this->db->where('employee_movement_id',$record_id);
			$this->db->join('benefit','employee_movement_benefit.benefit_id = benefit.benefit_id');
			$result_benefit = $this->db->get('employee_movement_benefit');

			$benefit_html = '';
			if ($result_benefit && $result_benefit->num_rows() > 0){
				foreach ($result_benefit->result() as $row) {
					$benefit_html .= '<tr>
						<td>(x) '.$row->benefit.'</td>
					</tr>';
				}
			}

			$vars['personnel_action_movement'] = $personnel_action_movement_html;
			$vars['position_from'] = $records_detail['current_position_id'];
			$vars['position_to'] = $records_detail['new_position_id'];
			$vars['e_status_from'] = $records_detail['current_employee_type_dummy'];
			$vars['e_status_to'] = $records_detail['employee_type'];
			$vars['g_d_from'] = $records_detail['current_division_dummy'];
			$vars['g_d_to'] = $records_detail['division_id'];
			$vars['d_p_from'] = $records_detail['current_department_id'];
			$vars['d_p_to'] = $records_detail['transfer_to'];	
			$vars['sal_from'] = $records_detail['current_basic_salary'];
			$vars['sal_to'] = $records_detail['new_basic_salary'];						
			$vars['eff_d_from'] = $records_detail['transfer_effectivity_date'];
			$vars['eff_d_to'] = ($records_detail['transfer_effectivity_date_to'] != NULL ? $records_detail['transfer_effectivity_date_to'] : '');
			$vars['details_action_movement'] = $result_info_movement['description'];
			$vars['allowances'] = $benefit_html;

			@$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.			
		/*	$this->pdf->setPrintHeader(TRUE);
			$this->pdf->SetAutoPageBreak(true, 25.4);
			$this->pdf->SetMargins( 19.05, 38.1 );
			$this->pdf->addPage('P', 'LETTER', true);					
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');*/
			$this->pdf->setLeftMargin('5.00');			
			$this->pdf->setRightMargin('5.00');			
			$this->pdf->addPage();

			$this->pdf->writeHTML($html, true, false, false, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$vars['employee_id'] . '.pdf', 'D');			
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	

	function export_list(){
		if($this->user_access[$this->module_id]['post'] == 1){
			
			$movement = $this->db->get_where('user', array("employee_id" => $this->input->post('employee_export')))->row();
			
			//getting all data on specific movement
			// $movement = $this->db->get_where('employee_movement', array("employee_movement_id" => $record_id))->row();
			//getting all data on specific movement

			// getting all movement
			$this->db->select('employee_movement.*, current_department.department AS current_department_name, new_department.department AS new_department_name, user_rank.job_rank, employee_movement_type.movement_type, user_job_level.description');
			$this->db->join('user_company_department AS current_department','employee_movement.current_department_id = current_department.department_id','left');
			$this->db->join('user_company_department AS new_department','employee_movement.transfer_to = new_department.department_id','left');
    		$this->db->join('user_rank','employee_movement.rank_id = user_rank.job_rank_id', 'left');
    		// $this->db->join('user_job_level AS current_job_level','employee_movement.current_job_level_dummy = current_job_level.job_level_id', 'left');
    		$this->db->join('user_job_level','employee_movement.job_level = user_job_level.job_level_id', 'left');
    		$this->db->join('employee_movement_type','employee_movement.employee_movement_type_id = employee_movement_type.employee_movement_type_id', 'left');
			$where = "{dbprefix}employee_movement.employee_id = ".$movement->employee_id." 
			AND {dbprefix}employee_movement.deleted = 0
			AND ({dbprefix}employee_movement.status = 3 
				 OR {dbprefix}employee_movement.status = 6)";

			$where = str_replace('{dbprefix}', $this->db->dbprefix, $where);
			$this->db->where($where);
			$this->db->order_by('employee_movement.compensation_effectivity_date', 'DESC');
			$this->db->order_by('employee_movement.movement_effectivity_date', 'DESC');
			$this->db->order_by('employee_movement.transfer_effectivity_date', 'DESC');
			$this->db->order_by('employee_movement.last_day', 'DESC');
			$all_movement = $this->db->get('employee_movement');
			
			// getting all movement

			//hiring
			$qry = "SELECT *
				FROM {dbprefix}employee 
				LEFT JOIN {dbprefix}user
				ON {dbprefix}employee.employee_id = {dbprefix}user.employee_id
				LEFT JOIN {dbprefix}user_company_department
				ON {dbprefix}user.department_id = {dbprefix}user_company_department.department_id
				WHERE {dbprefix}employee.deleted = 0
				AND {dbprefix}user.inactive = 0
				AND {dbprefix}employee.employee_id = ".$movement->employee_id;

			$qry = str_replace('{dbprefix}', $this->db->dbprefix, $qry);

			$hiring = $this->db->query($qry);
			//hiring

			//get employee current data
			$this->db->where('employee.employee_id', $movement->employee_id);
			$this->db->join('employee', 'user.employee_id = employee.employee_id', 'left');
			$this->db->join('user_job_title', 'employee.job_title = user_job_title.job_title_id', 'left');
			$emp_id = $this->db->get('user')->row();
			//get employee current data

			// $headers = array(
			// 	"A" => "Last Name",
			// 	"B" => "First Name",
			// 	"C" => "Middle Name",
			// 	"D" => "Unit",
			// 	"E" => "Job Title",
			// 	"F" => "Employee Status",
			// 	"G" => "Effectivity Date",
			// 	"H" => "Rank",
			// 	"I" => "Grade Level",
			// 	"J" => "Nature of Change"
			// 	);

			$headers = array(
				"A" => "Name",
				"B" => "Department",
				"C" => "Memo Date",
				"D" => "Effectivity Date",
				"E" => "Job Title",
				"F" => "Rank",
				"G" => "Grade Level",
				"H" => "Nature of Change"
				);

			if(!$all_movement && $all_movement->num_rows() == 0)
				return false;
			else
				$all_movement = $all_movement->result();

			$this->load->library('PHPExcel');		
			$this->load->library('PHPExcel/IOFactory');

			$objPHPExcel = new PHPExcel();

			$objPHPExcel->getProperties()->setTitle($query->description)
			            ->setDescription($query->description);
			               
			// Assign cell values
			$objPHPExcel->setActiveSheetIndex(0);
			$activeSheet = $objPHPExcel->getActiveSheet();

			//header
			$alphabet  = range('A','Z');

			$alpha_ctr = 0;
			$sub_ctr   = 0;

			// width setting
			$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
			// $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);

			//style for cell
			$styleArray = array(
				'font' => array(
					'bold' => true,
					'italic' => true,
				),			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);

			$headerstyle = array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN )
				),
				'font' => array(
					'bold' => true,
				),			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);

			$stylenumberingArray = array(
				'font' => array(
					'bold' => false,
				),			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);
			//style for cell

			// for merging
			// $alpha_ctr=5;
			// for($ctr=1; $ctr<5; $ctr++){
			// 	$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);
			// }
			// for merging

			$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

			foreach($headers as $letter => $header):
				$activeSheet->setCellValue($letter.'1', $header);	
				$objPHPExcel->getActiveSheet()->getStyle($letter.'1')->applyFromArray($headerstyle);
			endforeach;

			$activeSheet->setCellValue('A2', $emp_id->firstname." ".$emp_id->middlename." ".$emp_id->lastname);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);

			$line = 3;
			$regular = false;

			foreach($all_movement as $key => $movements)
			{
				$effectivity_date = array();
				$activeSheet->setCellValue('B'.$line, ($movements->transfer_to == null || $movements->tranfer_to == 0 || $movements->tranfer_to == "" ? $movements->current_department_name : $movements->new_department_name));
				$activeSheet->setCellValue('C'.$line, date($this->config->item('display_date_format'), strtotime($movements->created_date)));

				// Check effectivity all
				// $effectivity_date = $this->_check_effectivity_date($movements->compensation_effectivity_date, $movements->movement_effectivity_date, $movements->transfer_effectivity_date, $movements->last_day);
				$activeSheet->setCellValue('D'.$line, date($this->config->item('display_date_format'), strtotime($movements->transfer_effectivity_date)));

				// to be change to movement
				$activeSheet->setCellValue('E'.$line, $emp_id->job_title);

				$activeSheet->setCellValue('F'.$line, ($movements->rank_id == null || $movements->rank_id == 0 || $movements->rank_id == "" ? $movements->current_rank_dummy : $movements->job_rank));				
				$activeSheet->setCellValue('G'.$line, ($movements->job_level == null || $movements->job_level == 0 || $movements->job_level == "" ? $movements->current_job_level_dummy : $movements->job_level));				
				$activeSheet->setCellValue('H'.$line, $movements->movement_type);

				$hiring_job_title = $emp_id->job_title;
				$hiring_department = $movements->current_department_name;
				$hiring_rank_id = $movements->current_rank_dummy;
				$hiring_job_level = $movements->current_job_level_dummy;

				$line++;
			}				

			// for hiring
			if($hiring && $hiring->num_rows() > 0) {
				$hiring = $hiring->row();
				$activeSheet->setCellValue('B'.$line, $hiring_department);
				$activeSheet->setCellValue('C'.$line, date($this->config->item('display_date_format'), strtotime($hiring->created_date)));
				$activeSheet->setCellValue('D'.$line, date($this->config->item('display_date_format'), strtotime($hiring->employed_date)));
				$activeSheet->setCellValue('E'.$line, $hiring_job_title);
				$activeSheet->setCellValue('F'.$line, $hiring_rank_id);
				$activeSheet->setCellValue('G'.$line, $hiring_job_level);
				$activeSheet->setCellValue('H'.$line, 'Hiring');
			}
			// for hiring

			$filename = ($emp_id->firstname != '' || $emp_id->firstname != null ? str_replace(' ', '_', trim($emp_id->firstname)) : '_');
			$filename .= '_'.($emp_id->middlename != '' || $emp_id->firstname != null ? str_replace(' ', '_', trim($emp_id->middlename)) : '_');
			$filename .= '_'.($emp_id->lastname != '' || $emp_id->lastname != null ? str_replace(' ', '_', trim($emp_id->lastname)) : '_');

			// Save it as an excel 2003 file
			$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment;filename='.date('Y-m-d') . ' Movement_History_'.date('Y-m-d').'.xls');
			header('Content-Transfer-Encoding: binary');

			$path = 'uploads/dtr_summary/Movement_History_'.$filename.'.xls';
			
			$objWriter->save($path);

			$response->msg_type = 'success';
			$response->data = $path;
			
			$this->load->view('template/ajax', array('json' => $response));
		}
	}

	function export_muf(){
		// get record id
		if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
		$record_id = $this->input->post('record_id');
		// get record id
		
		if (CLIENT_DIR == 'firstbalfour'){
			$this->export_muf_fb($record_id);
		}

		//getting all data on specific movement
		$this->db->select('employee_movement.*,employee_movement.created_date as mm_created_date, employee.*, user.*, employee_movement_type.movement_type, user_rank.job_rank, employee_type.employee_type, a.position AS old_position, b.position AS new_position, c.department AS old_department, d.department AS new_department, user_company.company, employee_movement.created_by AS creator');
		$this->db->join('employee', 'employee_movement.employee_id = employee.employee_id', 'left');
		$this->db->join('user', 'employee_movement.employee_id = user.employee_id', 'left');
		$this->db->join('employee_movement_type', 'employee_movement.employee_movement_type_id = employee_movement_type.employee_movement_type_id', 'left');
		$this->db->join('user_company','employee_movement.company_id = user_company.company_id', 'left');

		$this->db->join('user_rank','employee_movement.rank_id = user_rank.job_rank_id', 'left');
		$this->db->join('employee_type','employee_movement.employee_type = employee_type.employee_type_id', 'left');

		$this->db->join('user_position AS a','employee_movement.current_position_id = a.position_id', 'left');
    	$this->db->join('user_position AS b','employee_movement.new_position_id = b.position_id', 'left');

		$this->db->join('user_company_department AS c','employee_movement.current_department_id = c.department_id', 'left');
    	$this->db->join('user_company_department AS d','employee_movement.transfer_to = d.department_id', 'left');

		$movement = $this->db->get_where('employee_movement', array("employee_movement_id" => $record_id));
		//getting all data on specific movement

		if(!$movement && $movement->num_rows() == 0)
			return false;
		else
			$movement = $movement->row();
		
		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');

		// width setting
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);


		//style for cell
		$styleArray = array(
			'font' => array(
				'bold' => true,
				'size' => 20,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$stylenumberingArray = array(
			'font' => array(
				'bold' => false,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$cellarray = array(
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
			'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'CCCCff'),
            ),

			'font' => array(
				'bold' => true,
			),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$coloredmerge = array(
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
			'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'CCCCCC'),
            ),
			// 'font' => array(
			// 	'bold' => true,
			// ),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$titleheaders = array(
						"Position",
						"Company",
						"Rank",
						"Grade Level",
						"Employment Type",
						"Department",
						"Employment Status",
						"Approver 1",
						"Approver 2",
						"Approver 3",
						"Effectivity Date",
						);

		// $currenttitleheaders = array(
		// 				"old_position" => "Position",
		// 				"current_company_dummy" => "Company",
		// 				"current_rank_dummy" => "Rank",
		// 				"current_job_level_dummy" => "Grade Level",
		// 				"current_employee_type_dummy" => "Employment Type",
		// 				"old_department" => "Department",
		// 				"current_employment_status" => "Employment Status",
		// 				"employee_approver" => "Approver 1",
		// 				"transfer_effectivity_date" => "Effectivity",
		// 				"remarks_leaving" => "Remarks",
		// 				"created_by" => "Entered By",
		// 				"noted" => "Noted By",
		// 				"created" => "Approved By",
		// 				);

		$activeSheet->setCellValue('A1', 'Movement');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);

		$activeSheet->setCellValue('A3', 'First Name');
		$activeSheet->setCellValue('B3', 'Surname');
		$activeSheet->setCellValue('C3', 'MI');
		$activeSheet->setCellValue('D3', 'Date');
		$activeSheet->setCellValue('A4', $movement->firstname);
		$activeSheet->setCellValue('B4', $movement->lastname);
		$activeSheet->setCellValue('C4', $movement->mi);
		$activeSheet->setCellValue('D4', date($this->config->item('display_date_format'), strtotime($movement->mm_created_date)));
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:D5');
		$activeSheet->setCellValue('A5', 'Date Hired : '.date($this->config->item('display_date_format'), strtotime($movement->employed_date)));

		// $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('B3')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('C3')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('D3')->applyFromArray($cellarray);

		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A7:D7');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A8:D8');
		$objPHPExcel->getActiveSheet()->getStyle('A7:D7')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('A8:D9')->applyFromArray($coloredmerge);
		// $objPHPExcel->getActiveSheet()->getStyle('A9')->applyFromArray($coloredmerge);
		// $objPHPExcel->getActiveSheet()->getStyle('C9')->applyFromArray($coloredmerge);

		// $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$ctr.':B'.$ctr);
		// $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C'.$ctr.':D'.$ctr);

		$activeSheet->setCellValue('A8', 'Type / Nature of Change : '.$movement->movement_type);
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A9:B9');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C9:D9');
		$activeSheet->setCellValue('A9', 'FROM');
		$activeSheet->setCellValue('C9', 'TO');

		$ctr = 10;
		foreach($titleheaders as $titleheader):
			if($titleheader != "Effectivity Date")
			{
				$activeSheet->setCellValue('A'.$ctr, $titleheader);
				$activeSheet->setCellValue('C'.$ctr, $titleheader);
			}

			switch($titleheader)
			{
				case "Position":
					$activeSheet->setCellValue('B'.$ctr, $movement->old_position);
					if($movement->new_position_id == 0)
						$activeSheet->setCellValue('D'.$ctr, $movement->old_position);
					else 
						$activeSheet->setCellValue('D'.$ctr, $movement->new_position);
					break;
				case "Company":
						$activeSheet->setCellValue('B'.$ctr, $movement->current_company_dummy);
						if($movement->company == null)
							$activeSheet->setCellValue('D'.$ctr, $movement->current_company_dummy);
						else
							$activeSheet->setCellValue('D'.$ctr, $movement->company);
					break;
				case "Rank":
						$activeSheet->setCellValue('B'.$ctr, $movement->current_rank_dummy);
						if($movement->job_rank == null)
							$activeSheet->setCellValue('D'.$ctr, $movement->current_rank_dummy);
						else
							$activeSheet->setCellValue('D'.$ctr, $movement->job_rank);
					break;
				case "Grade Level":
						$activeSheet->setCellValue('B'.$ctr, $movement->current_job_level_dummy);
						if($movement->description == null)
							$activeSheet->setCellValue('D'.$ctr, $movement->current_job_level_dummy);
						else
							$activeSheet->setCellValue('D'.$ctr, $movement->job_rank);
					break;
				case "Employment Type":
						$activeSheet->setCellValue('B'.$ctr, $movement->current_employee_type_dummy);
						if($movement->employee_type == null)
							$activeSheet->setCellValue('D'.$ctr, $movement->current_employee_type_dummy);
						else
							$activeSheet->setCellValue('D'.$ctr, $movement->employee_type);
					break;
				case "Department":
						$activeSheet->setCellValue('B'.$ctr, $movement->old_department);
						if($movement->new_department == null)
							$activeSheet->setCellValue('D'.$ctr, $movement->old_department);
						else
							$activeSheet->setCellValue('D'.$ctr, $movement->new_department);
					break;
				case "Employment Status":
						$activeSheet->setCellValue('B'.$ctr, $movement->current_employment_status);
						if($movement->employment_status == null)
							$activeSheet->setCellValue('D'.$ctr, $movement->current_employment_status);
						else
							$activeSheet->setCellValue('D'.$ctr, $movement->employment_status);
					break;
				case "Approver 1":
					if($movement->employee_approver == 0)
					{
						$this->db->join('user', 'user_position_approvers.approver_position_id = user.position_id', 'left');
						$get_approver_id = $this->db->get_where('user_position_approvers', array("user_position_approvers.position_id" => ($movement->new_position_id == 0 ? $movement->current_position_id : $movement->new_position_id)));

						if($get_approver_id && $get_approver_id->num_rows() > 0)
						{
							$get_approver_id = $get_approver_id->result();
							foreach($get_approver_id AS $approver):
								$activeSheet->setCellValue('B'.$ctr, $approver->firstname." ".$approver->middlename." ".$approver->lastname);
								$activeSheet->setCellValue('D'.$ctr, $approver->firstname." ".$approver->middlename." ".$approver->lastname);
								$ctr++;
							endforeach;
							$ctr = $ctr - 3;
						}
					} else {
						$this->db->join('user', 'user_position_approvers.approver_position_id = user.position_id', 'left');
						$get_approver_id = $this->db->get_where('user_position_approvers', array("user_position_approvers.position_id" => ($movement->new_position_id == 0 ? $movement->current_position_id : $movement->new_position_id)));

						if($get_approver_id && $get_approver_id->num_rows() > 0)
						{
							$get_approver_id = $get_approver_id->result();
							$new_approver = $this->db->get_where('user', array("user_id" => $movement->employee_approver))->row();
							$activeSheet->setCellValue('D'.$ctr, $new_approver->firstname." ".$new_approver->middlename." ".$new_approver->lastname);
							foreach($get_approver_id AS $approver):
								$activeSheet->setCellValue('B'.$ctr, $approver->firstname." ".$approver->middlename." ".$approver->lastname);
								$ctr++;
							endforeach;
							$ctr = $ctr - 3;
						}
					}
				break;
				case "Effectivity Date":
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B'.$ctr.':D'.$ctr);
					$activeSheet->setCellValue('A'.$ctr, "Effectivity : ");
					$activeSheet->setCellValue('B'.$ctr, date($this->config->item('display_date_format'), strtotime($movement->transfer_effectivity_date)));
					break;
			}
			$ctr++;
		endforeach;
						// case "Remarks":
			$to_here = $ctr + 2;
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$ctr.':B'.$to_here);
			$activeSheet->setCellValue('A'.$ctr, "Remarks: ".$movement->remarks_leaving);


			$created_by = $this->db->get_where('user', array("user_id" => $movement->creator))->row();
			$activeSheet->setCellValue('C'.$ctr, "Entered By: ");
			$activeSheet->setCellValue('D'.$ctr, $created_by->firstname." ".$created_by->middlename." ".$created_by->lastname);			
			$ctr++;
			$activeSheet->setCellValue('C'.$ctr, "Noted By: ");
			$ctr++;
			$activeSheet->setCellValue('C'.$ctr, "Approved By: ");


		$this->db->where('employee.employee_id', $movement->employee_id);
		$this->db->join('employee', 'user.employee_id = employee.employee_id', 'left');
		$this->db->join('user_job_title', 'employee.job_title = user_job_title.job_title_id', 'left');
		$emp_id = $this->db->get('user')->row();

		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		$filename = ($emp_id->firstname != '' || $emp_id->firstname != null ? str_replace(' ', '_', trim($emp_id->firstname)) : '_');
		$filename .= '_'.($emp_id->middlename != '' || $emp_id->firstname != null ? str_replace(' ', '_', trim($emp_id->middlename)) : '_');
		$filename .= '_'.($emp_id->lastname != '' || $emp_id->lastname != null ? str_replace(' ', '_', trim($emp_id->lastname)) : '_');
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.date('Y-m-d') . ' MUF_'.$filename.'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	
	}
	
	function export_muf_fb($record_id){
		//getting all data on specific movement
		$this->db->select('employee_movement.*,employee_movement.created_date as mm_created_date,nd.division as new_division,cgn.group_name as cur_group_name,ngn.group_name as new_group_name,cpn.project_name as cur_proj_name,npn.project_name as new_proj_name,employee.*, user.*,employee_movement_type.movement_type, user_rank.job_rank, employee_type.employee_type, a.position AS old_position, b.position AS new_position, c.department AS old_department, d.department AS new_department, user_company.company, employee_movement.created_by AS creator');
		$this->db->join('employee', 'employee_movement.employee_id = employee.employee_id', 'left');
		$this->db->join('user', 'employee_movement.employee_id = user.employee_id', 'left');
		$this->db->join('employee_movement_type', 'employee_movement.employee_movement_type_id = employee_movement_type.employee_movement_type_id', 'left');
		$this->db->join('user_company','employee_movement.company_id = user_company.company_id', 'left');

		$this->db->join('user_rank','employee_movement.rank_id = user_rank.job_rank_id', 'left');
		$this->db->join('employee_type','employee_movement.employee_type = employee_type.employee_type_id', 'left');

		$this->db->join('user_position AS a','employee_movement.current_position_id = a.position_id', 'left');
    	$this->db->join('user_position AS b','employee_movement.new_position_id = b.position_id', 'left');

		$this->db->join('user_company_department AS c','employee_movement.current_department_id = c.department_id', 'left');
    	$this->db->join('user_company_department AS d','employee_movement.transfer_to = d.department_id', 'left');

    	$this->db->join('user_company_division AS nd','employee_movement.division_id = nd.division_id', 'left');

		$this->db->join('group_name AS cgn','employee_movement.current_group_name_id = cgn.group_name_id', 'left');
		$this->db->join('group_name AS ngn','employee_movement.group_name_id = ngn.group_name_id', 'left');

		$this->db->join('project_name AS cpn','employee_movement.current_project_name_id = cpn.project_name_id', 'left');
		$this->db->join('project_name AS npn','employee_movement.project_name_id = npn.project_name_id', 'left');

		$movement = $this->db->get_where('employee_movement', array("employee_movement_id" => $record_id));

		//getting all data on specific movement

		if(!$movement && $movement->num_rows() == 0)
			return false;
		else
			$movement = $movement->row();
		
		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');

		// width setting
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);


		//style for cell
		$styleArray = array(
			'font' => array(
				'bold' => true,
				'size' => 20,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$stylenumberingArray = array(
			'font' => array(
				'bold' => false,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$cellarray = array(
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
			'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'CCCCff'),
            ),

			'font' => array(
				'bold' => true,
			),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$coloredmerge = array(
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
			'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'CCCCCC'),
            ),
			// 'font' => array(
			// 	'bold' => true,
			// ),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$titleheaders = array(
						"Position",
						"Company",
						"Rank",
						"Grade Level",
						"Division",
						"Department",
						"Project",
						"Group",
						"Default",
						"Approver 1",
						"Approver 2",
						"Approver 3",
						"Effectivity Date",
						);

		// $currenttitleheaders = array(
		// 				"old_position" => "Position",
		// 				"current_company_dummy" => "Company",
		// 				"current_rank_dummy" => "Rank",
		// 				"current_job_level_dummy" => "Grade Level",
		// 				"current_employee_type_dummy" => "Employment Type",
		// 				"old_department" => "Department",
		// 				"current_employment_status" => "Employment Status",
		// 				"employee_approver" => "Approver 1",
		// 				"transfer_effectivity_date" => "Effectivity",
		// 				"remarks_leaving" => "Remarks",
		// 				"created_by" => "Entered By",
		// 				"noted" => "Noted By",
		// 				"created" => "Approved By",
		// 				);

		$activeSheet->setCellValue('A1', 'Movement');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);

		$activeSheet->setCellValue('A3', 'First Name');
		$activeSheet->setCellValue('B3', 'Surname');
		$activeSheet->setCellValue('C3', 'MI');
		$activeSheet->setCellValue('D3', 'Date');
		$activeSheet->setCellValue('A4', $movement->firstname);
		$activeSheet->setCellValue('B4', $movement->lastname);
		$activeSheet->setCellValue('C4', $movement->mi);
		$activeSheet->setCellValue('D4', date($this->config->item('display_date_format'), strtotime($movement->mm_created_date)));
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:D5');
		$activeSheet->setCellValue('A5', 'Date Hired : '.date($this->config->item('display_date_format'), strtotime($movement->employed_date)));

		// $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('B3')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('C3')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('D3')->applyFromArray($cellarray);

		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A7:D7');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A8:D8');
		$objPHPExcel->getActiveSheet()->getStyle('A7:D7')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('A8:D9')->applyFromArray($coloredmerge);
		// $objPHPExcel->getActiveSheet()->getStyle('A9')->applyFromArray($coloredmerge);
		// $objPHPExcel->getActiveSheet()->getStyle('C9')->applyFromArray($coloredmerge);

		// $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$ctr.':B'.$ctr);
		// $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C'.$ctr.':D'.$ctr);

		$activeSheet->setCellValue('A8', 'Type / Nature of Change : '.$movement->movement_type);
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A9:B9');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C9:D9');
		$activeSheet->setCellValue('A9', 'FROM');
		$activeSheet->setCellValue('C9', 'TO');

		$ctr = 10;
		$check_default = false;
		foreach($titleheaders as $titleheader):
			/*	if($titleheader != "Effectivity Date")
				{
					$activeSheet->setCellValue('A'.$ctr, $titleheader);
					$activeSheet->setCellValue('C'.$ctr, $titleheader);
				}*/

				switch($titleheader)
				{
					case "Position":
							$activeSheet->setCellValue('A'.$ctr, $titleheader);
							$activeSheet->setCellValue('C'.$ctr, $titleheader);				
							$activeSheet->setCellValue('B'.$ctr, $movement->old_position);
							if($movement->new_position_id == 0)
								$activeSheet->setCellValue('D'.$ctr, $movement->old_position);
							else 
								$activeSheet->setCellValue('D'.$ctr, $movement->new_position);
						break;
					case "Company":
							$activeSheet->setCellValue('A'.$ctr, $titleheader);
							$activeSheet->setCellValue('C'.$ctr, $titleheader);				
							$activeSheet->setCellValue('B'.$ctr, $movement->current_company_dummy);
							if($movement->company == null)
								$activeSheet->setCellValue('D'.$ctr, $movement->current_company_dummy);
							else
								$activeSheet->setCellValue('D'.$ctr, $movement->company);
						break;
					case "Rank":
							$activeSheet->setCellValue('A'.$ctr, $titleheader);
							$activeSheet->setCellValue('C'.$ctr, $titleheader);				
							$activeSheet->setCellValue('B'.$ctr, $movement->current_rank_dummy);
							if($movement->job_rank == null)
								$activeSheet->setCellValue('D'.$ctr, $movement->current_rank_dummy);
							else
								$activeSheet->setCellValue('D'.$ctr, $movement->job_rank);
						break;
					case "Grade Level":
							$activeSheet->setCellValue('A'.$ctr, $titleheader);
							$activeSheet->setCellValue('C'.$ctr, $titleheader);				
							$activeSheet->setCellValue('B'.$ctr, $movement->current_job_level_dummy);
							if($movement->description == null)
								$activeSheet->setCellValue('D'.$ctr, $movement->current_job_level_dummy);
							else
								$activeSheet->setCellValue('D'.$ctr, $movement->job_rank);
						break;
					case "Employment Type":
							$activeSheet->setCellValue('A'.$ctr, $titleheader);
							$activeSheet->setCellValue('C'.$ctr, $titleheader);				
							$activeSheet->setCellValue('B'.$ctr, $movement->current_employee_type_dummy);
							if($movement->employee_type == null)
								$activeSheet->setCellValue('D'.$ctr, $movement->current_employee_type_dummy);
							else
								$activeSheet->setCellValue('D'.$ctr, $movement->employee_type);
						break;
					case "Division":
							if ($movement->current_division_dummy != null || $movement->division_id != 0){
								$check_default = true;
								$activeSheet->setCellValue('A'.$ctr, $titleheader);
								$activeSheet->setCellValue('C'.$ctr, $titleheader);							
								$activeSheet->setCellValue('B'.$ctr, $movement->current_division_dummy_val);
								if($movement->current_division_dummy == null)
									$activeSheet->setCellValue('D'.$ctr, $movement->current_division_dummy_val);
								else
									$activeSheet->setCellValue('D'.$ctr, $movement->new_division);
							}
							else{
								$ctr--;
							}						
						break;					
					case "Department":
							if ($movement->transfer_to != 0 || $movement->current_department_id != 0){
								$check_default = true;
								$activeSheet->setCellValue('A'.$ctr, $titleheader);
								$activeSheet->setCellValue('C'.$ctr, $titleheader);							
								$activeSheet->setCellValue('B'.$ctr, $movement->current_department_dummy_val);
								if($movement->new_department == null)
									$activeSheet->setCellValue('D'.$ctr, $movement->current_department_dummy_val);
								else
									$activeSheet->setCellValue('D'.$ctr, $movement->new_department);
							}
							else{
								$ctr--;
							}
						break;
					case "Project":
							if ($movement->current_project_name_id != 0 || $movement->group_name_id != 0){
								$check_default = true;
								$activeSheet->setCellValue('A'.$ctr, $titleheader);
								$activeSheet->setCellValue('C'.$ctr, $titleheader);							
								$activeSheet->setCellValue('B'.$ctr, $movement->cur_proj_name);
								if($movement->current_project_name_id == 0)
									$activeSheet->setCellValue('D'.$ctr, $movement->cur_proj_name);
								else
									$activeSheet->setCellValue('D'.$ctr, $movement->new_proj_name);
							}
							else{
								$ctr--;
							}						
						break;
					case "Group":
							if ($movement->current_group_name_id != 0 || $movement->group_name_id != 0){
								$check_default = true;
								$activeSheet->setCellValue('A'.$ctr, $titleheader);
								$activeSheet->setCellValue('C'.$ctr, $titleheader);							
								$activeSheet->setCellValue('B'.$ctr, $movement->cur_group_name);
								if($movement->current_group_name_id == 0)
									$activeSheet->setCellValue('D'.$ctr, $movement->cur_group_name);
								else
									$activeSheet->setCellValue('D'.$ctr, $movement->new_group_name);
							}
							else{
								$ctr--;
							}						
						break;										
					case "Employment Status":
							$activeSheet->setCellValue('A'.$ctr, $titleheader);
							$activeSheet->setCellValue('C'.$ctr, $titleheader);				
							$activeSheet->setCellValue('B'.$ctr, $movement->current_employment_status);
							if($movement->employment_status == null)
								$activeSheet->setCellValue('D'.$ctr, $movement->current_employment_status);
							else
								$activeSheet->setCellValue('D'.$ctr, $movement->employment_status);
						break;
					case "Default":
							if ($check_default == false){
								$this->db->where('employee_id',$movement->employee_id);
								$this->db->join('user_company_division','user.division_id = user_company_division.division_id','left');
								$this->db->join('user_company_department','user.department_id = user_company_department.department_id','left');
								$this->db->join('project_name','user.project_name_id = project_name.project_name_id','left');
								$this->db->join('group_name','user.group_name_id = group_name.group_name_id','left');
								$result = $this->db->get('user');

								if ($result && $result->num_rows() > 0){
									$row = $result->row();
									if ($row->project_name != ''){
										$activeSheet->setCellValue('A'.$ctr, "Project");
										$activeSheet->setCellValue('C'.$ctr, "Project");
										$activeSheet->setCellValue('B'.$ctr, $row->project_name);
										$activeSheet->setCellValue('D'.$ctr, "");									
									}
									elseif ($row->division != ''){
										$activeSheet->setCellValue('A'.$ctr, "Division");
										$activeSheet->setCellValue('C'.$ctr, "Division");									
										$activeSheet->setCellValue('B'.$ctr, $row->division);
										$activeSheet->setCellValue('D'.$ctr, "");
									}	
									elseif ($row->group_name != ''){
										$activeSheet->setCellValue('A'.$ctr, "Group");
										$activeSheet->setCellValue('C'.$ctr, "Group");									
										$activeSheet->setCellValue('B'.$ctr, $row->group_name);
										$activeSheet->setCellValue('D'.$ctr, "");
									}	
									elseif ($row->department != ''){
										$activeSheet->setCellValue('A'.$ctr, "Department");
										$activeSheet->setCellValue('C'.$ctr, "Department");									
										$activeSheet->setCellValue('B'.$ctr, $row->department);
										$activeSheet->setCellValue('D'.$ctr, "");
									}																								
								}
								else{
									$ctr--;
								}								
							}
							else{
								$ctr--;
							}						
						break;						
					case "Approver 1":
						$activeSheet->setCellValue('A'.$ctr, $titleheader);
						$activeSheet->setCellValue('C'.$ctr, $titleheader);				
						if($movement->employee_approver == 0)
						{
							$this->db->join('user', 'user_position_approvers.approver_position_id = user.position_id', 'left');
							$get_approver_id = $this->db->get_where('user_position_approvers', array("user_position_approvers.position_id" => ($movement->new_position_id == 0 ? $movement->current_position_id : $movement->new_position_id)));

							if($get_approver_id && $get_approver_id->num_rows() > 0)
							{
								$get_approver_id = $get_approver_id->result();
								foreach($get_approver_id AS $approver):
									$activeSheet->setCellValue('B'.$ctr, $approver->firstname." ".$approver->middlename." ".$approver->lastname);
									$activeSheet->setCellValue('D'.$ctr, $approver->firstname." ".$approver->middlename." ".$approver->lastname);
									$ctr++;
								endforeach;
								$ctr = $ctr - 3;
							}
						} else {
							$this->db->join('user', 'user_position_approvers.approver_position_id = user.position_id', 'left');
							$get_approver_id = $this->db->get_where('user_position_approvers', array("user_position_approvers.position_id" => ($movement->new_position_id == 0 ? $movement->current_position_id : $movement->new_position_id)));

							if($get_approver_id && $get_approver_id->num_rows() > 0)
							{
								$get_approver_id = $get_approver_id->result();
								$new_approver = $this->db->get_where('user', array("user_id" => $movement->employee_approver))->row();
								$activeSheet->setCellValue('D'.$ctr, $new_approver->firstname." ".$new_approver->middlename." ".$new_approver->lastname);
								foreach($get_approver_id AS $approver):
									$activeSheet->setCellValue('B'.$ctr, $approver->firstname." ".$approver->middlename." ".$approver->lastname);
									$ctr++;
								endforeach;
								$ctr = $ctr - 3;
							}
						}
					break;
					case "Effectivity Date":
						$activeSheet->setCellValue('A'.$ctr, $titleheader);
						$activeSheet->setCellValue('C'.$ctr, $titleheader);				
						$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B'.$ctr.':D'.$ctr);
						$activeSheet->setCellValue('A'.$ctr, "Effectivity : ");
						$activeSheet->setCellValue('B'.$ctr, date($this->config->item('display_date_format'), strtotime($movement->transfer_effectivity_date)));
						break;
				}
				$ctr++;
			endforeach;

		// case "Remarks":


		$to_here = $ctr + 2;
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$ctr.':B'.$to_here);
		$activeSheet->setCellValue('A'.$ctr, "Remarks: ".$movement->remarks_leaving);


		$created_by = $this->db->get_where('user', array("user_id" => $movement->creator))->row();
		$activeSheet->setCellValue('C'.$ctr, "Entered By: ");
		$activeSheet->setCellValue('D'.$ctr, $created_by->firstname." ".$created_by->middlename." ".$created_by->lastname);			
		$ctr++;
		$activeSheet->setCellValue('C'.$ctr, "Noted By: ");
		$ctr++;
		$activeSheet->setCellValue('C'.$ctr, "Approved By: ");
		$ctr++;
		$ctr++;

		//salary
		$activeSheet->setCellValue('A'.$ctr, "Basic Salary");
		$activeSheet->setCellValue('C'.$ctr, "Basic Salary");
		$activeSheet->setCellValue('B'.$ctr, ($movement->current_basic_salary == 0 ? number_format($movement->new_basic_salary,2) : number_format($movement->current_basic_salary,2)));
		$activeSheet->setCellValue('D'.$ctr, ($movement->new_basic_salary == 0 ? number_format($movement->current_basic_salary,2) : number_format($movement->new_basic_salary,2)));	
		$ctr++;
		$ctr++;

		//benifit
		$activeSheet->setCellValue('A'.$ctr, "Benefit: ");

		$ctr++;		
		$this->db->join('benefit','employee_movement_benefit.benefit_id = benefit.benefit_id');
		$benefit_result = $this->db->get_where('employee_movement_benefit', array("employee_movement_id" => $record_id));

		if ($benefit_result && $benefit_result->num_rows() > 0){
			foreach ($benefit_result->result() as $row) {
				$activeSheet->setCellValue('A'.$ctr, $row->benefit);
				$activeSheet->setCellValue('B'.$ctr, number_format($row->value,2));
				$ctr++;
			}
		}	

		//leaves
		$ctr++;
		$activeSheet->setCellValue('A'.$ctr, "Leaves: ");
		$ctr++;

		$this->db->select('lb.employee_id, lb.vl, lb.sl, lb.el, lb.mpl, lb.bl');
		$this->db->from('employee_leave_balance lb');
		$this->db->join('user u', 'lb.employee_id = u.employee_id', 'left');
		$this->db->where('lb.employee_id', $movement->employee_id);
		$this->db->where('lb.deleted', 0);
		$this->db->where('lb.year = YEAR(NOW())');
		$this->db->limit(1);
		$leaves = $this->db->get('user');

		$activeSheet->setCellValue('A'.$ctr, "Sick Leaves");
		$activeSheet->setCellValue('C'.$ctr, "Sick Leaves");
		$activeSheet->setCellValue('B'.$ctr, ($leaves->sl == 0 ? 0 : $leaves->sl ));
		$activeSheet->setCellValue('D'.$ctr, ($movement->sick_leave == 0 ? ($leaves->sl == 0 ? 0 : $leaves->sl) : $movement->sick_leave));	
		$ctr++;

		$activeSheet->setCellValue('A'.$ctr, "Vacation Leaves");
		$activeSheet->setCellValue('C'.$ctr, "Vacation Leaves");
		$activeSheet->setCellValue('B'.$ctr, ($leaves->vl == 0 ? 0 : $leaves->vl));
		$activeSheet->setCellValue('D'.$ctr, ($movement->vacation_leave == 0 ? ($leaves->vl == 0 ? 0 : $leaves->vl) : $movement->vacation_leave));	
		$ctr++;

		$activeSheet->setCellValue('A'.$ctr, "Emergency Leaves");
		$activeSheet->setCellValue('C'.$ctr, "Emergency Leaves");
		$activeSheet->setCellValue('B'.$ctr, ($leaves->el == 0 ? 0 : $leaves->el));
		$activeSheet->setCellValue('D'.$ctr, ($movement->emergency_leave == 0 ? ($leaves->el == 0 ? 0 : $leaves->el) : $movement->emergency_leave));	
		$ctr++;

		// foreach($titleheaders as $titleheader):
		// 	$activeSheet->setCellValue('A'.$ctr, $titleheader);
		// 	$activeSheet->setCellValue('B'.$ctr, $movement->{$key});
		// 	$activeSheet->setCellValue('C'.$ctr, $titleheader);
		// 	$activeSheet->setCellValue('D'.$ctr, $movement->{$key});
		// 	$ctr++;
		// endforeach;

		$this->db->where('employee.employee_id', $movement->employee_id);
		$this->db->join('employee', 'user.employee_id = employee.employee_id', 'left');
		$this->db->join('user_job_title', 'employee.job_title = user_job_title.job_title_id', 'left');
		$emp_id = $this->db->get('user')->row();

		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		$filename = ($emp_id->firstname != '' || $emp_id->firstname != null ? str_replace(' ', '_', trim($emp_id->firstname)) : '_');
		$filename .= '_'.($emp_id->middlename != '' || $emp_id->firstname != null ? str_replace(' ', '_', trim($emp_id->middlename)) : '_');
		$filename .= '_'.($emp_id->lastname != '' || $emp_id->lastname != null ? str_replace(' ', '_', trim($emp_id->lastname)) : '_');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.date('Y-m-d') . 'MUF_'.$filename.'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	private function _check_effectivity_date($compensation_effectivity_date = null, $movement_effectivity_date = null, $transfer_effectivity_date = null, $resign_date = null)
	{
		$effectivity_date = array();

		if($compensation_effectivity_date != null || $compensation_effectivity_date != "")
			$effectivity_date['compensation_effectivity_date'] = date($this->config->item('display_date_format'), strtotime($compensation_effectivity_date));
		if($movement_effectivity_date != null || $movement_effectivity_date != "")
			$effectivity_date['movement_effectivity_date'] = date($this->config->item('display_date_format'), strtotime($movement_effectivity_date));
		if($transfer_effectivity_date != null || $transfer_effectivity_date != "")
			$effectivity_date['transfer_effectivity_date'] = date($this->config->item('display_date_format'), strtotime($transfer_effectivity_date));
		if($resign_date != null || $resign_date != "")
			$effectivity_date['resign_date'] = date($this->config->item('display_date_format'), strtotime($resign_date));

		if(count($effectivity_date) > 0)
			return $effectivity_date;
		else
			return false;
	}

	function get_pos_reporting_to()
	{
		if(IS_AJAX)
		{
			$pos_id = $this->input->post('position_id');
			$space = ", ";
			$this->db->select('a_pos.position AS approver_position, s_pos.position AS selected_position, CONCAT(a_name.firstname, " ",a_name.middlename, " ",a_name.lastname) AS approver_name', false);
			$this->db->from('user_position AS s_pos');
			$this->db->join('user_position AS a_pos', 'a_pos.position_id = s_pos.reporting_to');
			$this->db->join('user AS a_name', 'a_name.position_id = s_pos.reporting_to');
			$this->db->where('s_pos.position_id', $pos_id);
			$positions = $this->db->get();
			
			if (!$positions || $positions->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'No Position Reporting To';
			} else {
				$response->msg_type = 'success';
				$response->data = $positions->row_array();
			}
			
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
	}

	function get_employee_list()
	{
		if(IS_AJAX)
		{
			$this->db->distinct();
			$this->db->select('employee_movement.employee_id');
			$this->db->select('user.*');
			$this->db->join('user', 'employee_movement.employee_id = user.employee_id', 'left');
			$this->db->where('employee_movement.deleted', 0);
			$result = $this->db->get('employee_movement');

			// $this->db->join('employee', 'user.employee_id = employee.employee_id', 'left');
			// $result = $this->db->get_where('user', array("user.deleted" => 0, "employee.resigned" => 0));

			$option = "";
			if($result && $result->num_rows() > 0)
			{
				foreach($result->result() as $emp_option)
				{
					$option .= "<option value='".$emp_option->employee_id."'>".$emp_option->firstname." ".$emp_option->middlename." ".$emp_option->lastname."</option>";
				}
				$response->msg_type = 'success';
				$response->data = $option;
			} else {
				$response->msg_type = 'error';
				$response->msg 		= 'No Employee Found';
			}
							
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
	}

	function fix_name_edit()
	{
		if(IS_AJAX)
		{
			$emp_info = $this->system->get_employee($this->db->get_where('employee_movement', array("employee_movement_id" => $this->input->post('record_id')))->row()->employee_id);			
			if($emp_info != 0)
			{
				$response->emp_id = $emp_info['employee_id'];
				$response->emp_name = $emp_info['firstname']." ".$emp_info['middlename']." ".$emp_info['lastname'];
				$this->load->view('template/ajax', array('json' => $response));
			} else {
				$this->session->set_flashdata('flashdata', 'Employee Not Known');
				redirect(base_url() . $this->module_link);	
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
	}
	// END custom module funtions

	// this will print 

	function one_time_routine()
	{
		$del_qry = "DELETE
					FROM ".$this->db->dbprefix."employee_leave_balance
					WHERE employee_id IN(SELECT
					                       employee_id
					                     FROM ".$this->db->dbprefix."employee
					                     WHERE status_id = 1
					                         AND employee_type > 1
					                         AND DATE_ADD(employed_date, INTERVAL 1 YEAR) > CURDATE())
					    AND `year` = 2013";

		if($this->db->query($del_qry))
			echo "Delete Success";
		else
			echo "Delete Failed";
		
		// dbug($this->db->last_query());

		echo "<br /><br />";
		echo "<b><i>status_id     1 = regular <br /> employee_type 1 = Officer, 2 = Supervisor, 3 = Rank & File <br /><br /></b></i>";

		$qry = "SELECT
				    employee_id
				FROM ".$this->db->dbprefix."employee
				WHERE status_id = 1
				    AND employee_type > 1
				    AND DATE_ADD(employed_date, INTERVAL 1 YEAR) > CURDATE()
				    ORDER BY employed_date ASC";

		$query = $this->db->query($qry)->result();

		foreach($query as $emp_id)
		{
			$hired_date = $this->db->get_where('employee', array("employee_id" => $emp_id->employee_id))->row();
			$vl_allowed = date("Y-m-d", strtotime(date("Y-m-d", strtotime($hired_date->employed_date)) . " + ".$this->config->item('vl_tenure_year')." year"));

			// Add leave credits, use leave setup based on employee type.
			$this->db->select('et.*, ef.application_code');
			$this->db->from('employee_type_leave_setup et');
			$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
			$this->db->where('employee_type_id', $hired_date->employee_type);
			$this->db->where('ef.deleted', 0);
			
			$leave_setup = $this->db->get();

			$date_diff = gregoriantojd(12, 31, date('Y', strtotime($vl_allowed))) - gregoriantojd(date('m', strtotime($vl_allowed)), date('d', strtotime($vl_allowed)), date('Y', strtotime($vl_allowed)));

			if ($leave_setup->num_rows() > 0) {
				$data['year'] = date('Y', strtotime($vl_allowed));
				foreach ($leave_setup->result() as $leave_type) {					
					// Formula for pro-rated
					$monthly = $leave_type->base / 365;
					$days_remaining = $monthly * $date_diff;
					$credits = round($days_remaining * 2) / 2;
					$data[strtolower($leave_type->application_code)] = $credits;
				}
				$data['employee_id'] = $emp_id->employee_id;

				$this->db->insert('employee_leave_balance', $data);
				// dbug($this->db->last_query());
			}
		}
	}

	function change_employee_dropdown()
	{
		$n_id = $this->input->post('nature_id');

		if($n_id == 12) {

			$qry = "SELECT *
					FROM {$this->db->dbprefix}user
					WHERE 
						deleted = 0
						AND employee_id NOT IN (1,2)
						AND employee_id NOT IN (SELECT u.employee_id
											FROM {$this->db->dbprefix}employee_floating ef
											LEFT JOIN {$this->db->dbprefix}user u
												ON ef.employee_id = u.employee_id
											WHERE ef.deleted = 0
												AND ef.date_from IS NOT NULL
												AND ef.date_to IS NULL
												AND u.deleted = 0)
					";

			$result = $this->db->query($qry);

			if($result && $result->num_rows() > 0)
			{
				foreach($result->result_array() as $user)
			        $html .= '<option value="'.$user["employee_id"].'">'.$user["firstname"].' '.$user["middlename"].' '.$user["lastname"].'</option>';

			} else
				return false;

		} else if($n_id == 13) {

			$qry = "SELECT *
					FROM {$this->db->dbprefix}employee_floating ef
					LEFT JOIN {$this->db->dbprefix}user u
						ON ef.employee_id = u.employee_id
					WHERE ef.deleted = 0
						AND ef.date_from IS NOT NULL
						AND ef.date_to IS NULL
						AND u.deleted = 0
					";

			$result = $this->db->query($qry);

			if($result && $result->num_rows() > 0)
			{
				foreach($result->result_array() as $user)
			        $html .= '<option value="'.$user["employee_id"].'">'.$user["firstname"].' '.$user["middlename"].' '.$user["lastname"].'</option>';
			} else
				return false;

		} else {
			$users = $this->db->get_where('user', array('deleted' => 0, 'inactive' => 0, 'employee_id <>' => 1, 'employee_id <>' => 2))->result_array();

			foreach($users as $user)
				$html .= '<option value="'.$user["employee_id"].'">'.$user["firstname"].' '.$user["middlename"].' '.$user["lastname"].'</option>';
		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function get_sub_repto_dropdown()
	{
		if (IS_AJAX) {
			$campaign_id = $this->input->post('campaign_id');

			if ($campaign_id > 0) {

				$html = '';

				$this->db->select('user.firstname, user.middlename, user.lastname, user.user_id');

				$this->db->join('user', 'user.employee_id = employee.employee_id', 'left');

				$result = $this->db->get_where('employee', array('campaign_id' => $campaign_id, 'user.deleted' => 0, 'inactive' => 0, 'resigned' => 0));

				if($result && $result->num_rows() > 0)
				{
					foreach($result->result_array() as $user)
						$html .= '<option value="'.$user["user_id"].'">'.$user["firstname"].' '.$user["middlename"].' '.$user["lastname"].'</option>';
				}
			} else {
				$response = stdClass();
			}

			$data['html'] = $html;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_floating($record_id = 0) 
	{
		// get record id
		if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) 
			$_POST['record_id'] = $this->uri->rsegment(3);

		$record_id = $this->input->post('record_id');
		
		//default template
		$tpl_file = 'floating_announcement';
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		$template = $this->template->get_module_template($this->module_id, $tpl_file );
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		$this->load->library('parser');

		if ($check_record->exist) {
			$movement = $this->db->get_where('employee_movement', array('employee_movement_id' => $record_id))->row();

			$f_employees = explode(',', $movement->employee_floating_id);

			foreach($f_employees as $f_employee)
			{
				// getting employee information
				// $this->db->join('user', 'user.employee_id = employee.employee_id', 'left');
				// $employee_floating_info = $this->db->get_where('employee', array('employee.employee_id' => $f_employee))->row();
				// $campaign = $this->db->get_where('campaign', array('campaign_id' => $employee_floating_info->campaign_id))->row()->campaign;
				$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
				$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
				$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
				$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
				$user_data = $this->db->get_where('user', array('user.user_id' => $f_employee))->row();

				// variables for template
				$vars['date'] = date($this->config->item('display_date_format'));
				$vars['floating_date'] = $movement->floating_date_from;
				$vars['floating_employee'] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname;
				$vars['floating_employee_pos'] = $user_data->position;
				$vars['movement_created_date'] = date($this->config->item('display_date_format'), strtotime($movement->created_date));
				$vars['movement_estimate_recall'] = date('Y-m-d', strtotime('+30 day', strtotime($movement->floating_date_from)));
				$vars['campaign'] = $user_data->campaign;

				$html = $this->template->prep_message($template['body'], $vars, false, false);

				// Prepare and output the PDF.		
				$this->pdf->addPage('P', 'LETTER', true);					
				$this->pdf->setPrintHeader(TRUE);
				$this->pdf->SetAutoPageBreak(true, 25.4);
				$this->pdf->SetMargins( 19.05, 38.1 );
				$this->pdf->writeHTML($html, true, false, true, false, '');
			}
			// to avoid PDF error
  			// ob_end_clean();
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	

	function get_project_per_employee(){
		$this->db->where('employee_id',$this->input->post('employee_id'));
		$this->db->where('project_name.project_name_id <>',0);
		$this->db->join('project_name','user.project_name_id = project_name.project_name_id');
		$result = $this->db->get('user');

		$html = '';
		$exist = false;
		if ($result && $result->num_rows() > 0){
			$exist = true;
			$html .= '<option value="">Select…</option>';
			foreach ($result->result() as $row) {
				$html .= '<option value="'.$row->project_name_id.'">'.$row->project_name.'</option>';
			}
		}

		$current_project = 0;
		$current_division = 0;
		$current_project_name = '';
		$current_division_name = '';

		$this->db->join('project_name','user.project_name_id = project_name.project_name_id');
		$this->db->join('user_company_division','user.division_id = user_company_division.division_id','left');
		// $this->db->where('assignment',1);
		$this->db->where('employee_id',$this->input->post('employee_id'));
		$primary_wa = $this->db->get('user');
		
		if ($primary_wa && $primary_wa->num_rows() > 0){
			$primary_row = $primary_wa->row();
			$current_project = $primary_row->project_name_id;
			$current_project_name = $primary_row->project_name;
			$current_division = $primary_row->division_id;
			$current_division_name = $primary_row->division;
		}

		$response->cur_proj_id = $current_project;
		$response->cur_proj_name = $current_project_name;		
		$response->cur_div_id = $current_division;
		$response->cur_div_name = $current_division_name;

		$response->html = $html;			
		$response->exist = $exist;			

		$this->load->view('template/ajax', array('json' => $response));			
	}

	function get_group_per_employee(){
		$this->db->where('employee_id',$this->input->post('employee_id'));
		$this->db->where('group_name.group_name_id <>',0);
		$this->db->join('group_name','user.group_name_id = group_name.group_name_id');
		$result = $this->db->get('user');

		$html = '';
		$exist = false;
		if ($result && $result->num_rows() > 0){
			$exist = true;
			$html .= '<option value="">Select…</option>';
			foreach ($result->result() as $row) {
				$html .= '<option value="'.$row->group_name_id.'">'.$row->group_name.'</option>';
			}
		}

		$current_group = 0;
		$current_department = 0;
		$current_group_name = '';
		$current_department_name = '';

		$this->db->select('user.group_name_id,group_name.group_name,user.department_id,user_company_department.department');
		$this->db->join('group_name','user.group_name_id = group_name.group_name_id');
		$this->db->join('user_company_department','user.department_id = user_company_department.department_id','left');
		// $this->db->where('assignment',1);
		$this->db->where('employee_id',$this->input->post('employee_id'));
		$primary_wa = $this->db->get('user');

		if ($primary_wa && $primary_wa->num_rows() > 0){
			$primary_row = $primary_wa->row();
			$current_group = $primary_row->group_name_id;
			$current_group_name = $primary_row->group_name;
			$current_department = $primary_row->department_id;
			$current_department_name = $primary_row->department;
		}

		$response->current_group_id = $current_group;
		$response->current_group_name = $current_group_name;		
		$response->current_department_id = $current_department;
		$response->current_department_name = $current_department_name;

		$response->html = $html;			
		$response->exist = $exist;			
		$this->load->view('template/ajax', array('json' => $response));			
	}	

	function get_department_code_group(){
		$response->cost_code = '';
		$this->db->where('deleted',0);
		$this->db->where('department_id',$this->input->post('department_id'));
		$result = $this->db->get('user_company_department');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->cost_code = $row->department_code;
			$response->group_name_id = $row->group_name_id;			
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}

	function get_cost_code_division(){
		$response->cost_code = '';
		$this->db->where('deleted',0);
		$this->db->where('project_name_id',$this->input->post('project_name_id'));
		$result = $this->db->get('project_name');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->cost_code = $row->cost_code;			
			$response->division_id = $row->division_id;			
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}	
	
	function print_regularization($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		//default template
		$tpl_file = 'regularization_contract';
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		if( $this->uri->rsegment(4) )
			$template = $this->template->get_template( $this->uri->rsegment(4) );	
		else
			$template = $this->template->get_module_template($this->module_id, $tpl_file );
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		$this->load->library('parser');

		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$total = $vars['basic'];
			$tax = $total * .02;
			
			$vars['tax'] = number_format( $tax, 2, '.', ',' );
			$vars['basic'] = number_format( $vars['basic'], 2, '.', ',' );
			$vars['date'] = date( $this->config->item('display_date_format') );
			$vars['fancy_date'] = date('jS \d\a\y \o\f F Y');
			$vars['time'] = date( $this->config->item('display_time_format') );
			$jo = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			$candidate = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $jo->candidate_id ))->row();
			$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $candidate->applicant_id ))->row();
			$vars['address'] = $applicant->pres_address1;

			if( !empty($applicant->pres_address2) ) $vars['address'] .= '<br/>'. $applicant->pres_address2;
			if( !empty($applicant->pres_city) ) $vars['address'] .= '<br/>'. $applicant->pres_city;
			if( !empty($applicant->province) ) $vars['address'] .= ', '. $applicant->province;
			if( !empty($applicant->zipcode) ) $vars['address'] .= ' '. $applicant->zipcode;
			if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$vars['sss'] = $applicant->sss;
			$vars['tin'] = $applicant->tin;
			$vars['philhealth'] = $applicant->philhealth;
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate->mrf_id ))->row();
			$campaign = $this->db->get_where('campaign', array('campaign_id' => $mrf->campaign_id ))->row();
			$vars['campaign'] = $campaign->campaign;
			$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
			$company = $this->db->get_where('user_company', array('company_id' => $position->company_id))->row();
			
			$meta = $this->config->item('meta');

			$vars['company'] = $meta['description'];
			$vars['position'] = $position->position;
			$vars['date_start'] = $vars['date_from'] = date( $this->config->item('display_date_format'), strtotime($jo->date_from) );
			$vars['date_end'] = $vars['date_to'] = date( $this->config->item('display_date_format'), strtotime($jo->date_to) );
			$facilitator = $this->hdicore->_get_userinfo( $this->user->user_id );
			$vars['facilitated_by'] = $facilitator->firstname.' '.$facilitator->lastname;
			$vars['allowances'] = "";
			$vars['premiums'] = "";
			$vars['duties_responsibilities']   = $position->duties_responsibilities;
			$vars['allowance_total'] = 0;
			$vars['premium_total'] = 0;
			$vars['total_plus_premium'] = 0;
			$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $jo->job_offer_id));
			if( $benefits->num_rows() > 0 ){
				$asterisk = '*';

				foreach( $benefits->result() as $benefit ){
					$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
					switch($benefit_detail->benefit_type_id){
						case 1:
							$vars['allowance_total'] += $benefit->value;
							$vars['allowances'] .= '<tr>
									<td style="width: 50%;"><em>'.$benefit_detail->benefit.'</em></td>
									<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
								</tr>';
						break;
						case 2:
							$vars['premium_total'] += $benefit->value;
							$vars['premiums'] .= '<tr>
									<td style="width: 50%;"><em>'.$benefit_detail->benefit. $asterisk . '</em></td>
									<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
								</tr>';

							$conditions[] = $asterisk . ' ' . $this->parser->parse_string($benefit_detail->description, $benefit);
							$asterisk .= '*';
						break;
					}
				}
			}

			if(!empty( $vars['premium_total'] )){
				$vars['premiums'] = '<tr>
									<td style="width: 50%;"><strong><em>Total Premium</em></strong></td>
									<td style="width: 30%;" align="right"><strong>'. number_format( $vars['premium_total'], 2, '.', ',') .'</strong></td>
								</tr>' . $vars['premiums'];
			}
			
			$total += $vars['allowance_total'];

			$vars['total'] = number_format( $total, 2, '.', ',' );
			$vars['allowance_total'] = number_format($vars['allowance_total'], 2, '.', ',');
			
			$vars['total_plus_premium'] = number_format( ($total + $vars['premium_total']), 2, '.', ',' );
			$vars['premium_total'] = number_format( $vars['premium_total'], 2, '.', ',' );					

			$vars['conditions'] = '';
			if ($vars['premium_total'] > 0) {
				$vars['conditions'] = '<strong>Conditions</strong>:<br/><br/>';
			
				foreach ($conditions as $condition) {
					$vars['conditions'] .= '<br/>' . $this->parser->parse_string($condition, $vars);
				}
			}

			$this->db->join('user','user.employee_id=employee_movement.employee_id');
			$this->db->join('employee','employee.employee_id=employee_movement.employee_id');
			$this->db->join('user_position','user_position.position_id=user.position_id');
        	$this->db->where('employee_movement.employee_movement_id', $record_id);
        	$result = $this->db->get('employee_movement');

        	if($result->num_rows() > 0) {
        		$result = $result->row_array();
  				$vars['receiver_name'] = $result['salutation']." ".$result['firstname']." ".substr($result['middlename'], 0, 1).". ".$result['lastname'];
				$vars['firstname'] = $result['firstname'];
				$vars['middlename'] = $result['middlename'];
				$vars['lastname'] = $result['lastname'];
				$vars['employee_name'] = $result['firstname']." ".substr($result['middlename'], 0, 1).". ".$result['lastname'];

				if($result['pres_city'] != 0 || $result['pres_city'] != null)
					$city=$this->db->get_where('cities', array('city_id' => $result['pres_city']))->row();

				if( !empty($result['pres_address1']) ) $vars['address'] .= '<br/>'. $result['pres_address1'];
				if( !empty($result['pres_address2']) ) $vars['address'] .= '<br/>'. $result['pres_address2'];
				if( !empty($city->city) ) $vars['address'] .= '<br/>'. $city->city;
				if( !empty($result['pres_province']) ) $vars['address'] .= ', '. $result['pres_province'];
				if( !empty($result['pres_zipcode']) ) $vars['address'] .= ' '. $result['pres_zipcode'];
				if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

				
				if($result['new_basic_salary'] == 0 || $result['new_basic_salary'] == null || trim($result['new_basic_salary']) == "")
					$vars['new_basic_salary'] = $result['current_basic_salary'];

				$vars['basic'] = number_format( $vars['new_basic_salary'], 2, '.', ','); 
				$vars['position'] = $result['position'];
				$vars['description'] = $result['description'];
				// $duties = explode(';', $result['duties_responsibilities']);
				$vars['duties_responsibilities'] = $result['duties_responsibilities'];  
				$vars['effectivity_date'] = date('M. d, Y', strtotime($result['transfer_effectivity_date']) ); 
				$benefits = $this->db->get_where('employee_movement_benefit', array('employee_movement_id' => $record_id ));
				if( $benefits->num_rows() > 0 ){
					$asterisk = '*';

					foreach( $benefits->result() as $benefit ){
						$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();

						switch($benefit_detail->benefit_type_id){
							case 1:
								$vars['allowance_total'] += $benefit->value;
								$vars['allowances'] .= '<tr>
										<td style="width: 50%;"><em>'.$benefit_detail->benefit.'</em></td>
										<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
									</tr>';
							break;
							case 2:
								$vars['premium_total'] += $benefit->value;
								$vars['premiums'] .= '<tr>
										<td style="width: 50%;"><em>'.$benefit_detail->benefit. $asterisk . '</em></td>
										<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
									</tr>';

								$conditions[] = $asterisk . ' ' . $this->parser->parse_string($benefit_detail->description, $benefit);
								$asterisk .= '*';
							break;
						}
					}
				}

			$vars['conditions'] = '';
			if ($vars['premium_total'] > 0) {
				$vars['conditions'] = '<strong>Conditions</strong>:<br/><br/>';
			
				foreach ($conditions as $condition) {
					$vars['conditions'] .= '<br/>' . $this->parser->parse_string($condition, $vars);
				}
			}

			$total = $vars['allowance_total'] + $vars['new_basic_salary'];

			$vars['total'] = number_format( $total, 2, '.', ',' );
			$vars['allowance_total'] = number_format($vars['allowance_total'],2,'.',',');
			$vars['new_basic_salary'] = number_format($vars['new_basic_salary'],2,'.',',');
			$vars['total_plus_premium'] = number_format( ($total + $vars['premium_total']), 2, '.', ',' );
			}

			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.			
			$this->pdf->setPrintHeader(TRUE);
			$this->pdf->SetAutoPageBreak(true, 25.4);
			$this->pdf->SetMargins( 19.05, 38.1 );
			$this->pdf->addPage('P', 'LETTER', true);					
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_extension($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		//default template
		$tpl_file = 'regularization_contract';
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		if( $this->uri->rsegment(4) )
			$template = $this->template->get_template( $this->uri->rsegment(4) );	
		else
			$template = $this->template->get_module_template($this->module_id, $tpl_file );
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		$this->load->library('parser');

		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$total = $vars['basic'];
			$tax = $total * .02;
			
			$vars['tax'] = number_format( $tax, 2, '.', ',' );
			$vars['basic'] = number_format( $vars['basic'], 2, '.', ',' );
			$vars['date'] = date( $this->config->item('display_date_format') );
			$vars['fancy_date'] = date('jS \d\a\y \o\f F Y');
			$vars['time'] = date( $this->config->item('display_time_format') );
			$jo = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			$candidate = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $jo->candidate_id ))->row();
			$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $candidate->applicant_id ))->row();
			$vars['address'] = $applicant->pres_address1;

			if( !empty($applicant->pres_address2) ) $vars['address'] .= '<br/>'. $applicant->pres_address2;
			if( !empty($applicant->pres_city) ) $vars['address'] .= '<br/>'. $applicant->pres_city;
			if( !empty($applicant->province) ) $vars['address'] .= ', '. $applicant->province;
			if( !empty($applicant->zipcode) ) $vars['address'] .= ' '. $applicant->zipcode;
			if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$vars['sss'] = $applicant->sss;
			$vars['tin'] = $applicant->tin;
			$vars['philhealth'] = $applicant->philhealth;
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate->mrf_id ))->row();
			$campaign = $this->db->get_where('campaign', array('campaign_id' => $mrf->campaign_id ))->row();
			$vars['campaign'] = $campaign->campaign;
			$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
			$company = $this->db->get_where('user_company', array('company_id' => $position->company_id))->row();
			
			$meta = $this->config->item('meta');

			$vars['company'] = $meta['description'];
			$vars['position'] = $position->position;
			$vars['date_start'] = $vars['date_from'] = date( $this->config->item('display_date_format'), strtotime($jo->date_from) );
			$vars['date_end'] = $vars['date_to'] = date( $this->config->item('display_date_format'), strtotime($jo->date_to) );
			$facilitator = $this->hdicore->_get_userinfo( $this->user->user_id );
			$vars['facilitated_by'] = $facilitator->firstname.' '.$facilitator->lastname;
			$vars['allowances'] = "";
			$vars['premiums'] = "";
			$vars['duties_responsibilities']   = $position->duties_responsibilities;
			$vars['allowance_total'] = 0;
			$vars['premium_total'] = 0;
			$vars['total_plus_premium'] = 0;
			$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $jo->job_offer_id));
			if( $benefits->num_rows() > 0 ){
				$asterisk = '*';

				foreach( $benefits->result() as $benefit ){
					$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
					switch($benefit_detail->benefit_type_id){
						case 1:
							$vars['allowance_total'] += $benefit->value;
							$vars['allowances'] .= '<tr>
									<td style="width: 50%;"><em>'.$benefit_detail->benefit.'</em></td>
									<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
								</tr>';
						break;
						case 2:
							$vars['premium_total'] += $benefit->value;
							$vars['premiums'] .= '<tr>
									<td style="width: 50%;"><em>'.$benefit_detail->benefit. $asterisk . '</em></td>
									<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
								</tr>';

							$conditions[] = $asterisk . ' ' . $this->parser->parse_string($benefit_detail->description, $benefit);
							$asterisk .= '*';
						break;
					}
				}
			}

			if(!empty( $vars['premium_total'] )){
				$vars['premiums'] = '<tr>
									<td style="width: 50%;"><strong><em>Total Premium</em></strong></td>
									<td style="width: 30%;" align="right"><strong>'. number_format( $vars['premium_total'], 2, '.', ',') .'</strong></td>
								</tr>' . $vars['premiums'];
			}
			
			$total += $vars['allowance_total'];

			$vars['total'] = number_format( $total, 2, '.', ',' );
			$vars['allowance_total'] = number_format($vars['allowance_total'], 2, '.', ',');
			
			$vars['total_plus_premium'] = number_format( ($total + $vars['premium_total']), 2, '.', ',' );
			$vars['premium_total'] = number_format( $vars['premium_total'], 2, '.', ',' );					

			$vars['conditions'] = '';
			if ($vars['premium_total'] > 0) {
				$vars['conditions'] = '<strong>Conditions</strong>:<br/><br/>';
			
				foreach ($conditions as $condition) {
					$vars['conditions'] .= '<br/>' . $this->parser->parse_string($condition, $vars);
				}
			}

			$this->db->join('user','user.employee_id=employee_movement.employee_id');
			$this->db->join('employee','employee.employee_id=employee_movement.employee_id');
			$this->db->join('user_position','user_position.position_id=user.position_id');
        	$this->db->where('employee_movement.employee_movement_id', $record_id);
        	$result = $this->db->get('employee_movement');

        	if($result->num_rows() > 0) {
        		$result = $result->row_array();
				$vars['receiver_name'] = $result['salutation']." ".$result['firstname']." ".substr($result['middlename'], 0, 1).". ".$result['lastname'];
				$vars['firstname'] = $result['firstname'];
				$vars['middlename'] = $result['middlename'];
				$vars['lastname'] = $result['lastname'];

				if($result['pres_city'] != 0 || $result['pres_city'] != null)
					$city=$this->db->get_where('cities', array('city_id' => $result['pres_city']))->row();

				if( !empty($result['pres_address2']) ) $vars['address'] .= '<br/>'. $result['pres_address2'];
				if( !empty($city->city) ) $vars['address'] .= '<br/>'. $city->city;
				if( !empty($result['pres_province']) ) $vars['address'] .= ', '. $result['pres_province'];
				if( !empty($result['pres_zipcode']) ) $vars['address'] .= ' '. $result['pres_zipcode'];
				if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

				
				if($result['new_basic_salary'] == 0 || $result['new_basic_salary'] == null || trim($result['new_basic_salary']) == "")
					$vars['new_basic_salary'] = $result['current_basic_salary'];

				$vars['position'] = $result['position'];
				$vars['description'] = $result['description'];
				$vars['duties_responsibilities'] = $result['duties_responsibilities'];

				$benefits = $this->db->get_where('employee_movement_benefit', array('employee_movement_id' => $record_id ));
				if( $benefits->num_rows() > 0 ){
					$asterisk = '*';

					foreach( $benefits->result() as $benefit ){
						$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
						switch($benefit_detail->benefit_type_id){
							case 1:
								$vars['allowance_total'] += $benefit->value;
								$vars['allowances'] .= '<tr>
										<td style="width: 50%;"><em>'.$benefit_detail->benefit.'</em></td>
										<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
									</tr>';
							break;
							case 2:
								$vars['premium_total'] += $benefit->value;
								$vars['premiums'] .= '<tr>
										<td style="width: 50%;"><em>'.$benefit_detail->benefit. $asterisk . '</em></td>
										<td style="width: 30%;" align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
									</tr>';

								$conditions[] = $asterisk . ' ' . $this->parser->parse_string($benefit_detail->description, $benefit);
								$asterisk .= '*';
							break;
						}
					}
				}
			$total = $vars['allowance_total'] + $vars['new_basic_salary'];

			$vars['total'] = number_format( $total, 2, '.', ',' );
			$vars['allowance_total'] = number_format($vars['allowance_total'],2,'.',',');
			$vars['new_basic_salary'] = number_format($vars['new_basic_salary'],2,'.',',');
			}

			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.			
			$this->pdf->setPrintHeader(TRUE);
			$this->pdf->SetAutoPageBreak(true, 25.4);
			$this->pdf->SetMargins( 19.05, 38.1 );
			$this->pdf->addPage('P', 'LETTER', true);					
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}			
}

/* End of file */
/* Location: system/application */
