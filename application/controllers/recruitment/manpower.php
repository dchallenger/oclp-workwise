<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Manpower extends MY_Controller {
	const DEPARTMENT_TABLE = 'user_company_department';
	const POSITIONS_TABLE = 'user_position';
	const MANPOWER_REQUEST_TABLE = 'recruitment_manpower';
	const EMAIL_TEMPLATE_ID = 4;
	//const PDF_TEMPLATE_ID = 'manpower_request_pdf';
	const PDF_TEMPLATE_ID = 'personnel_requisition_form';
	const APPROVAL_TEMPLATE_ID = 6; // Change this.

	private $_employment_statuses,
			$__request_statuses;

	function __construct() {
		parent::__construct();
		
		$this->load->helper('recruitment');

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Manpower Request';
		$this->listview_description = 'Lists all manpower requests';
		$this->jqgrid_title = "Manpower Request";
		$this->detailview_title = 'Manpower Request';
		$this->detailview_description = 'This page shows detailed information about a Manpower Request';
		$this->editview_title = 'Manpower Request Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a Manpower Request';
		$this->default_sort_col = array($this->key_field . ' desc');

		$this->_employment_statuses = array('Regular', 'Probationary', 'Contractual');
		$this->_request_statuses    = array('For Approval', 'Declined', 'Approved', 'Draft', 'In-Process','For HR Review', 'Closed');

		if (method_exists($this, 'print_record') && $this->user_access[$this->module_id]['print'] == 1) {
			$data['show_print'] = true;
		} else {
			$data['show_print'] = false;
		}

        $data['module_filters']      = get_manpower_filters($this->_request_statuses);
        $data['module_filter_title'] = 'Manpower Request';

		$this->load->vars($data);

	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['content'] = 'recruitment/manpower/listview';

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function filter($type = null) {
		if ($type == null) {
			redirect('recruitment/applicants');
		}		

		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['content'] = 'recruitment/manpower/listview';
		
		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		$data['filter_true'] = true;

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$data['default_query'] = true;
		$data['default_query_field'] = 'status';

		// // get mrf approvers 
		// $this->db->select('recruitment_manpower.status AS mrf_status, recruitment_manpower_approver.status AS approver_status');
		// $this->db->join('recruitment_manpower', 'recruitment_manpower.request_id=recruitment_manpower_approver.request_id');
		// $mrf_approvers = $this->db->get_where('recruitment_manpower_approver',array('approver'=>$this->user->user_id));
		

		$data['default_query_val'] = $this->_request_statuses[$type];

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function detail() {
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/manpower_detailview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';

		if (IS_AJAX && $this->input->post('flag') == 0) {
			$data['content'] = 'recruitment/detailview';
		} else {
			$data['content'] = 'recruitment/compactview';
		}

		//other views to load
		$data['views'] = array();
		$data['show_wizard_control'] = false;

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
		}

		// Check if current record can still be edited.
		$this->db->where('request_id', $this->input->post('record_id'));
		$this->db->where('deleted', 0);
		$result = $this->db->get(self::MANPOWER_REQUEST_TABLE);

		$data['can_edit'] = $this->_can_edit($result->row_array());
		$data['can_approve'] = $this->_can_approve($result->row_array());
		$data['can_decline'] = $this->_can_decline($result->row_array());
		$data['buttons'] = '/recruitment/template/manpower-detail-buttons';

		$data['status'] = 'Draft';
		
		if ($result && $result->row_array() > 0) {
			$mrf = $result->row();

			$data['status'] = $mrf->status;
			$data['created_by'] = $mrf->created_by;
			$data['starting_salary'] = $mrf->starting_salary;			
		}

		// for manpower served hra used.
		$this->db->where('mrf_id',$this->input->post('record_id'));
		$this->db->where('candidate_status_id',6);
		$this->db->join('recruitment_applicant','recruitment_manpower_candidate.applicant_id = recruitment_applicant.applicant_id');
		$this->db->join('referred_by','recruitment_applicant.referred_by_id = referred_by.referred_by_id','left');
		$result = $this->db->get('recruitment_manpower_candidate');
		$data['manpower_served'] = $result; 
		// for manpower served hra used.
			
		//load variables to env
		$this->load->vars($data);

		if (!IS_AJAX) {
			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$data['html'] = $this->load->view($this->userinfo['rtheme'] . '/' . $data['content'], '', true);

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
	}

	function edit() {

		if ($this->user_access[$this->module_id]['edit'] == 1) {
			parent::edit();

			//additional module edit routine here
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/manpower_edit.js"></script>';

			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
			$data['show_wizard_control'] = true;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
						
			$data['content'] = 'recruitment/editview';

			// Load custom buttons for editview.
			$data['buttons'] = 'recruitment/template/manpower-edit-buttons';
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$this->db->where($this->key_field, $this->input->post('record_id'));
			$this->db->where('deleted', 0);

			$response = $this->db->get($this->module_table);

			$data['status'] = 'Draft';
			
			if ($response && $response->num_rows() > 0) {
				$mrf = $response->row();

				$data['status'] = $mrf->status;
				$data['starting_salary'] = $mrf->starting_salary;
			}

			// Get default fieldgroup to open, if any.
			$default_fg = $this->input->post('default_fg');
			if (isset($default_fg) && $default_fg > 0) {
				$data['default_fg'] = $default_fg;
			}

			// for manpower served hra used.
			$this->db->where('mrf_id',$this->input->post('record_id'));
			$this->db->where('candidate_status_id',6);
			$this->db->join('recruitment_applicant','recruitment_manpower_candidate.applicant_id = recruitment_applicant.applicant_id');
			$this->db->join('referred_by','recruitment_applicant.referred_by_id = referred_by.referred_by_id','left');
			$result = $this->db->get('recruitment_manpower_candidate');
			$data['manpower_served'] = $result; 
			// for manpower served hra used.

			//load variables to env
			$this->load->vars($data);

			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	
	function ajax_save()
	{
		// $settings = $this->get_manpower_settings(TRUE);
		$this->db->trans_start();
		$approvers_per_position = $this->system->get_approvers_and_condition($this->userinfo['user_id'], $this->module_id);


		if( empty($approvers_per_position)  && $this->user_access[$this->module_id]['post'] != 1){
            $response->msg = "Please contact HR Admin. Approver has not been set.";
            $response->msg_type = "error";
            $response->page_refresh = "true";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
        }

		parent::ajax_save();

		if ($this->input->post('record_id') == '-1') {
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, 
				array(
					// 'concurred_optional' => $settings->concurred_optional, 
					// 'concurred_by'		 => $this->input->post('concurred_by'),
					'created_by' 		 => $this->userinfo['user_id']
					)
				);


				$this->db->where('position_id', $this->userinfo['position_id']);			
				$position_id  = $this->db->get('user_position')->row();

				//Note: In Setting the approver in employee approver, add the approver 
				// in the following sequence: hr, ceo, odpm, division head
				
				foreach( $approvers_per_position as $approver ){

					$info['request_id'] = $this->key_field_val;
					$info['approver'] = $approver['approver'];
					$info['sequence'] =	$approver['sequence'];
					if($approver['sequence'] == 1){
						$info['status'] = "For Approval";	
					}else{
						$info['status'] = "Draft";
					}
					// $info['status'] = 'For Approval';
					$this->db->insert('recruitment_manpower_approver', $info);
				}


		} else {
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, 
				array(									
					'modified_date' => date('Y-m-d H:i:s'),
					'modified_by'   => $this->userinfo['user_id']
					)
				);					
		}

		if ($this->input->post('reason_for_request') != 1){
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table,array('budgetted_new_position' => 0,'budgetted_ml_to' => 0));	
		}

		// Save document number.
		$this->db->where($this->key_field, $this->key_field_val);
		if (CLIENT_DIR == 'firstbalfour') {
			$this->db->update($this->module_table, 
						array(
							'modified_by'   => $this->userinfo['user_id'],
							'requested_date' => date('Y-m-d')
							));		
		}else{
			$this->db->update($this->module_table, 
						array(
							'requested_by' => $this->userinfo['user_id'], 
							'requested_date' => date('Y-m-d')
							));
		}
		$row = $this->db->get($this->module_table)->row_array();
		
		if ($this->input->post('record_id') == '-1' && (trim($row['document_number']) == '' || 1 == 1)) {
			// Get position code.
			$this->db->where('position_id', $row['position_id']);
			$position = $this->db->get('user_position')->row_array();
			$position_code = $position['position_code'];
			// Define document number as postion_code-(yy) .
			if ($position_code != ""){
				$document_number = $position_code . '-' . date('y') . '-';
			}
			else{
				$document_number = date('y') . '-';	
			}
			// Get increment ID.
			$this->db->where('document_number REGEXP "^'.$document_number . '[0-9][0-9][0-9][0-9]'.'"');
			$this->db->order_by('document_number', 'DESC');
			$latest = $this->db->get(self::MANPOWER_REQUEST_TABLE)->row_array();

			$last = explode('-', $latest['document_number']);
			$last = end($last);
			$new_increment = number_pad($last + 1);

			$document_number .= $new_increment;

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, array('document_number' => $document_number));

		}

		$job_position_description = array(
			'duties_responsibilities' => $this->input->post('duties'),
			'licensure'  => $this->input->post('licensure'),
			'education'  => $this->input->post('education'),
			'experience' => $this->input->post('qualification'),
			'others' 	 => $this->input->post('remarks')
		);

/*		$this->db->where('position_id', $this->input->post('position_id'));
		$this->db->update('user_position', $job_position_description);

		$result = $this->db->get_where("recruitment_applicant",array("position_id"=>$this->input->post('position_id'),"deleted"=>0,"manpower_request_status_sent"=>0));
		if ($result && $result->num_rows() > 0){
			$this->_send_email($result->result_array(),$this->userinfo['user_id']);
		}*/

		$this->db->trans_complete();
	}

   /**
     * Send the email to employees,immediate superior and hr.
     */
    protected function _send_email($array_list_candidate,$employee_id = false) {
    	if (!$employee_id){
    		return false;
    	}

    	$recepients = array();
    	$result = $this->db->get_where('user',array('user_id'=>$employee_id));
    	if ($result && $result->num_rows() > 0){
    		$single_row = $result->row();
    		if ($single_row->email != ''){
				$recepients[] = $single_row->email;
    		}
    	}

        $this->load->model('template');
        $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
        	$employees = '';
        	foreach ($array_list_candidate as $value) {
        		$employees .= '<tr><td>'.$value['firstname'].'&nbsp;'.$value['middlename'].'&nbsp;'.$value['lastname'].'</td></tr>';
        	}
            // Load the template. 
            $template = $this->template->get_module_template(184, 'habitual_tardiness');
            $request['employees'] = $employees;
            $message = $this->template->prep_message($template['body'], $request);
            $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);
        }
    }

	function delete() {
		parent::delete();

		//additional module delete routine here
	}

	// END - default module functions
	// START custom module funtions

	/**
	 * Returns a json encoded array of company departments
	 */
	function get_company_departments() {
		if (IS_AJAX) {
			$company_id = $this->input->post('company_id');

			if ($company_id > 0) {
				$this->db->where('company_id', $company_id);
				$this->db->order_by('department');
				$this->db->where('deleted', 0);
				$result = $this->db->get(self::DEPARTMENT_TABLE);

				$response = $this->_get_default('department_id');

				if ($result->num_rows() > 0) {
					$response['departments'] = $result->result_array();
				}
			} else {
				$response = array();
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_employee_details()
	{
		if (IS_AJAX) {
			$employee_id = $this->input->post('employee_id');
			$this->db->select('user.division_id, department_id, company_id, section_id, firstname, lastname');
			$this->db->where('user.employee_id', $employee_id);
			$this->db->join('employee', 'employee.employee_id=user.employee_id');
			$employee = $this->db->get('user')->row_array();

			$response->department = $employee['department_id'];
			$response->division = $employee['division_id'];
			$response->company = $employee['company_id'];
			$response->section = $employee['section_id'];
			$response->name = $employee['firstname'] .' '. $employee['lastname'] ;
			
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
		

	}
	/**
	 * Returns a json encoded array of company positions
	 */
	function get_company_positions() {
		if (IS_AJAX) {
			$company_id = $this->input->post('company_id');

			if ($company_id > 0) {
				$this->db->where('company_id', $company_id);
				$this->db->order_by('position');
				$this->db->where('deleted', 0);
				$result = $this->db->get(self::POSITIONS_TABLE);

				$response = $this->_get_default('position_id');

				if ($result->num_rows() > 0) {
					$response['positions'] = $result->result_array();
				}
			} else {
				$response = array();
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	/**
	 * Returns a json encoded array of company positions
	 */
	function get_positions() {
		if (IS_AJAX) {
			
			$this->db->where('deleted', 0);
			$result = $this->db->get(self::POSITIONS_TABLE);

			$response = $this->_get_default('position_id');

			if ($result->num_rows() > 0) {
				$response['positions'] = $result->result_array();
			}


			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	/**
	 * Returns a json encoded array of employment statuses.
	 */
	function get_employment_statuses() {
		if (IS_AJAX) {
			if ($this->input->post('record_id') > 0) {
				$response = $this->_get_default('status_id');
			}

			$response['statuses'] = $this->_employment_statuses;
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	/**
	 * Returns an array of managers or the name of the current manager signed in.
	 * 
	 * @param int $company_id
	 * @return void
	 */
	function get_requested_by($company_id = '') {
		if (IS_AJAX) {
			// If admin return a list of managers and supervisors for the company.
			$response = $this->_get_default('requested_by');

			if ($this->input->post('record_id') < 0) {
				$response['user_id'] = $this->userinfo['user_id'];
				$response['text'] = $this->userinfo['firstname'] . ' ' . $this->userinfo['lastname'];
			} else {
				$response['user_id'] = $response['value'];
			}

			$response['code'] = 1;
				
			$this->db->where('user_id', $response['value']);
			$result = $this->db->get('user');

			if ($result->num_rows() > 0) {
				$user = $result->row_array();
				$response['text'] = $user['firstname'] . ' ' . $user['lastname'];
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_company_dropdown() {
		if (IS_AJAX) {
			// If admin return a list of companies.
			if ( $this->is_superadmin || $this->is_admin ) {
				$response['code'] = 0;
			} else {
				if ($this->input->post('record_id') < 0) {
					$company_id = $this->userinfo['company_id'];
				} else {
					$company_id = $this->input->post('company_id');
				}

				$this->db->where('company_id', $company_id);
				$this->db->where('deleted', 0);
				$result = $this->db->get('user_company');

				if ($result->num_rows() > 0) {
					$company = $result->row_array();
					$text = $company['company'];
				} else {
					$company = '';
					$company_id = 0;
					$text = '';
				}

				$response['company_id'] = $company_id;
				$response['text'] = $text;
				$response['code'] = 1;
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_requested_date() {
		if (IS_AJAX) {
			$response['text'] = $response['input'] = date('m/d/Y', strtotime(date('Y-m-d')));

			if ($this->input->post('record_id') != '-1') {
				$this->db->where('request_id', $this->input->post('record_id'));
				$result = $this->db->get(self::MANPOWER_REQUEST_TABLE);

				if ($result->num_rows() > 0) {
					$manpower = $result->row_array();
					$response['text'] = $response['input'] = date('m/d/Y', strtotime($manpower['requested_date']));
				}
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_concurred_by() {
		if (IS_AJAX) {
			$response = $this->_get_default('concurred_by');
			$result = $this->db->get('recruitment_manpower_settings');

			if ($result->num_rows() > 0) {
				$settings = $result->row_array();

				if ($response['value'] == 0) {
					$response['value'] = $settings['notify'];
				}
			}

			$approvers = $this->_get_approvers();

			if (count($approvers) > 0) {
				$response['admins'] = $approvers;
			}

			if ($response['value'] > 0) {
				$this->db->where('user_id', $response['value']);
				$result = $this->db->get('user');

				if ($result->num_rows() > 0) {
					$default = $result->row_array();
					$response['text'] = $default['firstname'] . ' ' . $default['lastname'];

					$response['admins'][] = $result->row();
				}
			}

			$response['code'] = ($this->input->post('approver') == 1) ? 0 : 1;

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_approved_by() {
		if (IS_AJAX) {
			$this->db->where('position_id', $this->userinfo['position_id']);			

			$position_id  = $this->db->get('user_position')->row();
			$default 	  = $this->_get_default('approved_by');
			$response     = $this->hdicore->get_approvers($position_id->position_id, $this->module_id);
			$approvers    = $response['admins'];
			// If there has not been a saved value decide which approver. Notification > Reporting To
			if ($default['value'] <= 0) {
				if (count($approvers) > 0) {
					$default = $approvers[0];										
				}
			} else {
				$this->db->where('user_id', $default['value']);
				$result  = $this->db->get('user');
				$default = $result->row_array();
			}

			if ($default && count($default) > 0) {				
				$response['text'] 	  = $default['firstname'] . ' ' . $default['lastname'];
				$response['value']    = $default['user_id'];
			}

			$data['json'] = $response;
			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	
	/**
	 * @TODO refactor or delete sometime, only one function uses this now I think. JMC
	 * @return [type] [description]
	 */
	private function _get_approvers() {
		$company_id = '';
		if ($this->input->post('company_id') > 0) {
			$company_id = $this->input->post('company_id');
		} else {
			$record = $this->_get_default('company_id');
			$company_id = $record['value'];
		}

		$this->db->select('reporting_to');
		$this->db->where('user_id', $this->userinfo['user_id']);
		$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
		$this->db->limit(1);

		$user = $this->db->get('user');

		$approvers = array();

		if ($user->row()->reporting_to) {			
			$approver_position_hierarchy = $this->_get_approver_hierarchy($user->row()->reporting_to);
			
			foreach ($approver_position_hierarchy as $approver) {
				$this->db->where('position_id', $approver);
				$this->db->where('company_id', $company_id);

				$query = $this->db->get('user');

				if ($query->num_rows() > 0) {
					foreach ($query->result() as $r) {
						$approvers[] = $r;
					}
				}
			}
		}

		// Add other available approvers if admin.
		if ($this->is_admin) {
			$this->db->where('user.company_id', $company_id);
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->where_in('position_level_id', array(3,4,5));

			$add_approvers = $this->db->get('user');

			if ($add_approvers->num_rows() > 0) {
				foreach ($add_approvers->result() as $r) {
					$approvers[] = $r;
				}
			}
		}

		return $approvers;	
	}

	function get_employee_to_be_replaced() {
		if (IS_AJAX) {
			$response = $this->_get_default('replacement_name');

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	private function _get_default($type) {
		$record = $this->_get_mrf();
		$response['value'] = 0;
		if ($record) {
			$response['value'] = $record[$type];
		}

		return $response;
	}

	private function _get_mrf($record_id = 0) {
		if ($this->input->post('record_id') > 0 && $record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->db->where('request_id', $record_id);
		$result = $this->db->get(self::MANPOWER_REQUEST_TABLE);

		if ($result->num_rows() > 0) {
			return $result->row_array();
		}

		return false;
	}

	function _additional_listview_query() {

		if (!$this->is_recruitment() && !$this->is_superadmin && $user_access[$this->module_id]['post'] == 0 ) {
			
			$this->db->where('(( '.$this->db->dbprefix('recruitment_manpower').'.request_id IN ( SELECT request_id 
			FROM '.$this->db->dbprefix('recruitment_manpower_approver').'
			WHERE approver = '.$this->userinfo['user_id'].' )  ) OR ( '.$this->db->dbprefix('recruitment_manpower').'.requested_by = '.$this->userinfo['user_id'].' ) OR ( '.$this->db->dbprefix('recruitment_manpower').'.created_by = '.$this->userinfo['user_id'].' ))');

		}


		$this->listview_qry .= ',' . self::MANPOWER_REQUEST_TABLE . '.status, ' . self::MANPOWER_REQUEST_TABLE . '.approved_by,'
			. self::MANPOWER_REQUEST_TABLE . '.requested_by as rb_id,' 
			. 'CONCAT(rb.firstname, " ", rb.lastname) as requested_by, concurred_as_approver, concurred_by,'
			. ' concurred_approved, approver_approved, concurred_optional, ' . self::MANPOWER_REQUEST_TABLE . '.created_by';

		$this->db->join('user rb', 'rb.user_id = ' . self::MANPOWER_REQUEST_TABLE . '.requested_by');

		$this->search_columns[] = array(
		    'column' => 'CONCAT(rb.firstname, " ", rb.lastname)',
		    'jq_index' => 'requested_by'
		);

		return;
	}

	/* START List View Functions */

	function listview() {

		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true;

		//set columnlist and select qry
		$this->_set_listview_query('', $view_actions);

		if (method_exists($this, '_additional_listview_query')) {
			$this->_additional_listview_query();
		}

		//set Search Qry string
		if ($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if ($this->module == "user" && (!$this->is_admin && !$this->is_superadmin))
			$search .= ' AND ' . $this->db->dbprefix . 'user.user_id NOT IN (1,2)';

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table . '.deleted = 0 AND ' . $search);
		//$this->db->order_by($this->module_table .".created_date", "asc"); 

		//get list
		$result = $this->db->get();

		$response->last_query = $this->db->last_query();
		if ($this->db->_error_message() != "") {
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		} else {
			$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows() / $limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $result->num_rows();


			if (method_exists($this, '_additional_listview_query')) {
				$this->_additional_listview_query();
			}
			
			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table . '.deleted = 0 AND ' . $search);
			//$this->db->order_by($this->module_table .".created_date", "asc"); 

			if ($sidx != "") {
				$this->db->order_by($sidx, $sord);
			} else {
				if (is_array($this->default_sort_col)) {
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);

			$result = $this->db->get();
			// $response->last_query = $this->db->last_query();
			/*dbug($this->db->last_query());*/
			//check what column to add if this is a related module
			$column_to_add = array();
			if ($related_module) {
				foreach ($this->listview_columns as $column) {
					if ($column['name'] != "action") {
						$temp = explode('.', $column['name']);
						if (strpos($this->input->post('column'), ',')) {
							$column_lists = explode(',', $this->input->post('column'));
							if (sizeof($temp) > 1 && in_array($temp[1], $column_lists))
								$column_to_add[] = $column['name'];
						}
						else {
							if (sizeof($temp) > 1 && $temp[1] == $this->input->post('column'))
								$this->related_module_add_column = $column['name'];
						}
					}
				}
				//in case specified related column not in listview columns, default to 1st column
				if (!isset($this->related_module_add_column)) {
					if (sizeof($column_to_add) > 0)
						$this->related_module_add_column = implode('~', $column_to_add);
					else
						$this->related_module_add_column = $this->listview_columns[0]['name'];
				}
			}

			if ($this->db->_error_message() != "") {
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			} else {
				$response->rows = array();
				if ($result->num_rows() > 0) {
					$columns_data = $result->field_data();
					$column_type = array();
					foreach ($columns_data as $column_data) {
						$column_type[$column_data->name] = $column_data->type;
					}
					$this->load->model('uitype_listview');
					$ctr = 0;

					foreach ($result->result_array() as $row) {

						$sql = "SELECT * FROM {$this->db->dbprefix}recruitment_manpower
							    WHERE request_id = {$row['request_id']}";

						$manpower_info_result = $this->db->query($sql);

						if ($manpower_info_result && $manpower_info_result->num_rows() > 0){
							$manpower_info_row = $manpower_info_result->row();
						}

						if ($row['status'] == 'Draft' && !$this->is_superadmin) {
							if ($row['rb_id'] != $this->userinfo['user_id'] && $row['created_by'] != $this->userinfo['user_id'])
								continue;
						}

						$cell = array();
						$cell_ctr = 0;					
						foreach ($this->listview_columns as $column => $detail) {
							if (preg_match('/\./', $detail['name'])) {
								$temp = explode('.', $detail['name']);
								$detail['name'] = $temp[1];
							}
						
							if ($detail['name'] == 'action') {
								if ($view_actions) {
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions($row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr')) : $this->_default_grid_actions($this->module_link, $this->input->post('container'), $row) );
									$cell_ctr++;
								}
							} elseif ($detail['name'] == 'date_needed') {
								$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
								$date = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);

								$timespan = timespan(time(), strtotime($date), TRUE);
								
								if ( isset( $row['status'] ) && $row['status'] == 'Closed'){
									$timespan = "";
								}else{
									if ($timespan['text'] == '') {
										if (isset( $row['status'] ) && $row['status'] != 'Closed') {									
											$timespan = '<span class="red"><small>Deadline missed.</small></span>';
										}
									} else {																		
										$timespan = '<span class="timespan '. (($timespan['days'] <= 7) ? 'blue' : 'green') .'"><small>' . $timespan['text'] . ' left.</small></span>';
									}
								}
								$cell[$cell_ctr] = empty($timespan) ? $date : $date; //. '<br />' . $timespan;
								$cell_ctr++;								
							} elseif ($detail['name'] == 'status') {
							/*	if ($row['concurred_optional'] ) {
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
								} else {
									if ($row['concurred_as_approver'] == 1 
										&& $this->userinfo['user_id'] == $row['approved_by']
										&& $row['concurred_approved'] != 1
										&& $row['status'] == 'For Approval'
										) {
										$this->listview_fields[$cell_ctr]['value'] = 'Waiting';
									} else if($row[$detail['name']] == 'Waiting' && $this->userinfo['user_id'] == $row['approved_by']) {
										$this->listview_fields[$cell_ctr]['value'] = 'For Approval';
									} else {
										$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									}											
								}*/
								// $retVal = ($row[$detail['name']] == "") ? $row[$detail['name']] : $row[$detail['name']] ;
								$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
								$cell[$cell_ctr] = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);

								if( $row['status'] != 'For HR Review' && $row['status'] != 'Approved' && $row['status'] != 'Draft' && $row['status'] != ''){
									$this->db->where('request_id',$row['request_id']);
									$this->db->where('status <>','Approved');
									$this->db->join('user','recruitment_manpower_approver.approver = user.user_id');
									$result_approver_init = $this->db->get('recruitment_manpower_approver');
									if ($result_approver_init && $result_approver_init->num_rows() > 0){
										$this->db->where('request_id',$row['request_id']);
										$this->db->join('user','recruitment_manpower_approver.approver = user.user_id');
										$result_approver = $this->db->get('recruitment_manpower_approver');										
										foreach ($result_approver->result() as $row_approver) {
											switch($row_approver->status){
												case 'Draft': 
													$class = 'orange';
													$approver_status = 'Waiting ...';
													break;
												case 'For Approval': 
													$class = 'orange';
													$approver_status = $row_approver->status;
													break;
												case 'Approved': 
													$class = 'green';
													$approver_status = $row_approver->status;
													break;	
												case 'Cancelled':
												case 'Declined': 
													$class = 'red';
													$approver_status = $row_approver->status;
													break;	
											}		

											$user = $this->hdicore->_get_userinfo( $row_approver->approver );
											$user_access = $this->hdicore->_create_user_access_file( $user );

											// if( $user_access[$this->module_id]['post'] != 1 ){
												$cell[$cell_ctr] .= '<br /><span class="small italic">'.$row_approver->firstname.' '.$row_approver->lastname.'</span> : <span class="'.$class.' italic">'.$approver_status.'</span>';
											// }

										}
									}
								}

								$cell_ctr++;
							} elseif ($detail['name'] == 'with_amp') {
								$with_amp = 'No';
								if ($row['with_amp'] == 1){
									$with_amp = 'Yes';
								}
								$cell[$cell_ctr] = $with_amp;
								$cell_ctr++;	
							} elseif ($detail['name'] == 't1position') {
								$cell[$cell_ctr] = $row['t1position'];
								$cell_ctr++;	
							} elseif ($detail['name'] == 't2department') {
								// $cat_info = $this->system->get_recruitment_category($manpower_info_row->category_id,$manpower_info_row->category_value_id);
								// $cell[$cell_ctr] = $cat_info['cat_value'];
								$cell[$cell_ctr] = $row['t2department'];
								$cell_ctr++;															
							} elseif ($detail['name'] == 'date_served') {
								$date_served = '';
								if ( isset( $row['status'] ) && $row['status'] == 'Closed'){
									$sql = "SELECT max(hired_date) AS date_served FROM {$this->db->dbprefix}recruitment_manpower_candidate
										    WHERE mrf_id = {$row['request_id']}";

									$result_date = $this->db->query($sql);

									if ($result_date && $result_date->num_rows() > 0){
										$row_date = $result_date->row();
										$date_served = $row_date->date_served;
									}
								}
								$cell[$cell_ctr] = $date_served;
								$cell_ctr++;
							} /*elseif ($detail['name'] == 'turn_around_time') {
								// dbug($manpower_info_row->turn_around_time);
								// $user_rank_result = $this->db->get_where('user_rank',array('job_rank_id' => $manpower_info_row->job_rank_id));
								// $tat_computed = 0;
								// $tat = 0;

								// if ($user_rank_result && $user_rank_result->num_rows() > 0){
								// 	$user_rank_row = $user_rank_result->row();
								// 	$tat = $user_rank_row->tat;
								// }

								if ($manpower_info_row->contract_received && $manpower_info_row->contract_received != ""){
									$contract_received = new DateTime($manpower_info_row->date_from);
									$current_date = new DateTime();
									$date_diff = $contract_received->diff($current_date);

									$days_passed = $date_diff->d;

									$cur_date = date('Y-m-d');
									$count = 0;
									$received = $manpower_info_row->contract_received;

									// 
									$current = strtotime($cur_date);
									$start_fill_rate = strtotime($manpower_info_row->date_from);

									if ($current >= $start_fill_rate) {
										$days_passed = ($current - $start_fill_rate) / (60 * 60 * 24);
									}else{
										$days_passed = 0;
									}

									$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);

								}

								$jo = $this->system->get_accepted_jo();
								
								// if ( isset( $row['status'] ) && $row['status'] == 'Closed'){
								// 	$date_closed = new DateTime($manpower_info_row->date_closed);
								// 	$date_diff_closed = $date_closed->diff($contract_received);
								// 	$dayspassed = $date_diff_closed->d;
								// 	$tat_computed = $manpower_info_row->turn_around_time - $dayspassed;
								// }elseif ($jo->accepted_jo >= $manpower_info_row->number_required) {

								// 	$tat_computed = $jo->accepted_date;//$manpower_info_row->turn_around_time;

								// }elseif (in_array($row['status'], array('For Approval', 'For HR Review'))  || ($manpower_info_row->contract_received && $manpower_info_row->contract_received == "0000-00-00")) {
								// 	$tat_computed = 0;
								// }
								// else{
								// 	
								// }

								if (isset( $row['status'] ) && $row['status'] == 'In-Process') {
								
									
									if ($jo->accepted_jo >= $manpower_info_row->number_required){
										$days_passed = (strtotime($jo->accepted_date) - $start_fill_rate) / (60 * 60 * 24);								
										$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);
									}

									$tat_computed = ($manpower_info_row->turn_around_time - $days_passed) + $weekends;

								}elseif (isset( $row['status'] ) && $row['status'] == 'Closed') {

									$days_passed = (strtotime($manpower_info_row->date_closed) - $start_fill_rate) / (60 * 60 * 24);								
									$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);

									if ($jo->accepted_jo >= $manpower_info_row->number_required){
										$days_passed = (strtotime($jo->accepted_date) - $start_fill_rate) / (60 * 60 * 24);								
										$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);
									}
									
									$tat_computed = ($manpower_info_row->turn_around_time - $days_passed) + $weekends;

								}
								else{
									$tat_computed = ($manpower_info_row->turn_around_time) - ($days_passed + $weekends);
									if ($manpower_info_row->contract_received  == "0000-00-00" || $manpower_info_row->contract_received == "") {
										$tat_computed = ($manpower_info_row->turn_around_time);
									}
								}	
								

								$cell[$cell_ctr] = $tat_computed . ' day(s)';
								$cell_ctr++;																								
							}*/ else {
								if (in_array($this->listview_fields[$cell_ctr]['uitype_id'], array(2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39))) {
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);
								} else if (in_array($this->listview_fields[$cell_ctr]['uitype_id'], array(3)) && ( isset($this->listview_fields[$cell_ctr]['other_info']['picklist_type']) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' )) {
									$cell[$cell_ctr] = "";
									foreach ($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val) {
										if ($row[$detail['name']] == $picklist_val['id'])
											$cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else {
									$cell[$cell_ctr] = (is_numeric($row[$detail['name']]) && ($column_type[$detail['name']] != "253" && $column_type[$detail['name']] != "varchar") ) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
								}
								$cell_ctr++;
							}
						}
						$response->rows[$ctr]['id'] = $row[$this->key_field];
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}
		$data['json'] = $response;

		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		parent::_set_listview_query($listview_id, $view_actions);

		$this->listview_qry .= ','.$this->db->dbprefix.'recruitment_manpower.status_id, '.$this->db->dbprefix.'recruitment_manpower.position_for, '.$this->db->dbprefix.'recruitment_manpower.reason_for_request';
	}

	/**
	 * Send the email to approved_by and concurred_by.
	 */
	function send_email() {

		$this->db->where('request_id', $this->input->post('record_id'));
		$request = $this->db->get(self::MANPOWER_REQUEST_TABLE);

		if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
			$response->type = 'notice';
			$response->msg = 'Sending failed.';
			$response->record_id = $this->input->post('record_id');

			$manpower_request['status'] = "Draft";
			$this->db->where('request_id', $manpower_request['request_id']);
			$this->db->update(self::MANPOWER_REQUEST_TABLE, $manpower_request);

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				$manpower_request = $request->row_array();

				$requested_by = $this->system->get_employee($manpower_request['requested_by']);
				$mrf['requested_by'] = $requested_by['firstname']. ' ' .$requested_by['lastname'];

				$position = $this->db->get_where('user_position', array('position_id' => $manpower_request['position_id']))->row();
				$mrf['position'] = $position->position;

				$mrf['requested_date'] = date('F d, Y ', strtotime($manpower_request['requested_date']));
				// Load the template.            
				$this->load->model('template');
				

				$concurred_by = explode(',',$manpower_request['concurred_by']);

				// Get recepients.
				if( is_array( $concurred_by  ) && sizeof($concurred_by) > 0){

					foreach( $concurred_by as $concurred ){

						$concurred = $this->hdicore->_get_userinfo( $concurred );

						if($concurred->email != NULL){

							$recepients[] = $concurred['email'];

						}
					}
				}

				
				if ($manpower_request['status'] == "Draft" || $manpower_request['status'] == "") {
					$template = $this->template->get_module_template($this->module_id, 'mrf_for_hr_review');
					$manpower_request_status = 'For HR Review';
					
					// settings.
					$where_in = "FIND_IN_SET(" .$manpower_request['division_id']. ", division_id)";  //division_id IN (" .$manpower_request['division_id']. ")";
					$this->db->where($where_in);
					$settings = $this->db->get('recruitment_manpower_settings');
				
					if ($settings && $settings->num_rows() > 0) {
						$settings = $settings->row_array();
						$recepients[] = $settings['email_to'];
						$cc[] = $settings['cc_to'];

						$hr_info = $this->db->get_where('user', array('email' => $settings['email_to']));
						if ($hr_info && $hr_info->num_rows() > 0) {
							
							$mrf['hr'] = $hr_info->row()->salutation . ' '. $hr_info->row()->firstname .' '.$hr_info->row()->lastname;
						}
					}

				}else{

					$template = $this->template->get_template(self::EMAIL_TEMPLATE_ID);
					$manpower_request_status = 'For Approval';
					$cc = array();
					// Get approver as recepients.
					// $approvers_per_position = $this->system->get_approvers_and_condition($manpower_request['requested_by'], $this->module_id);
					$approvers = $this->db->get_where('recruitment_manpower_approver',array('request_id'=>$this->input->post('record_id'), 'status' => 'For Approval'))->result_array();
					foreach( $approvers as $approver ){

						$user = $this->hdicore->_get_userinfo( $approver['approver'] );

						$mrf['approver'] = $user->salutation. ' ' . $user->firstname . ' ' . $user->lastname;

						// $user_access = $this->hdicore->_create_user_access_file( $user );

					//	if( $user_access[$this->module_id]['post'] != 1 ){ //Not HR Approver
							if ($user->email != ''){
								$recepients[] = $user->email;
							}
					//	}
					}

				}

				
				$message = $this->template->prep_message($template['body'], $mrf);
				
				// If queued successfully set the status to For Approval.
				if (true) {
						if ($manpower_request['status'] == 'For HR Review') {
							$manpower_request['hra_id'] = $this->userinfo['user_id'];
						}
						$manpower_request['status'] = $manpower_request_status;
						$manpower_request['date_emailed'] = date('Y-m-d G:i:s');
						$this->db->where('request_id', $manpower_request['request_id']);
						$this->db->update(self::MANPOWER_REQUEST_TABLE, $manpower_request);

						$response->msg_type = 'success';
						$response->msg = 'Manpower Request Sent.';

				}

				$this->template->queue(implode(',', $recepients), implode(',', $cc), $template['subject'], $message);
				
			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	/**
	 * Send the email to requesitioning party.
	 */
	function send_email_requisitioning() {
		if (IS_AJAX) {
			$response->type = 'notice';
			$response->msg = 'Sending failed.';
			$response->record_id = $this->input->post('record_id');

		

			$requested_by = $this->system->get_employee($this->user->user_id);
			$mrf['requested_by'] = $requested_by['firstname']. ' ' .$requested_by['lastname'];
			$mrf['created_date'] = date('F d, Y');

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template($this->module_id, 'exceeded_not_part_amp');
				//$message = $this->template->prep_message($template['body'], $manpower_request);
				
				$division_id = $this->input->post('division_id');
				// settings.
				$where_in = "FIND_IN_SET(" .$division_id. ", division_id)";  //division_id IN (" .$manpower_request['division_id']. ")";
				$this->db->where($where_in);
				$settings = $this->db->get('recruitment_manpower_settings');
			
				if ($settings && $settings->num_rows() > 0) {
					$settings = $settings->row_array();
					$recepients[] = $settings['email_to'];
		
					$hr_info = $this->db->get_where('user', array('email' => $settings['email_to']));
					if ($hr_info && $hr_info->num_rows() > 0) {
						
						$mrf['hr'] = $hr_info->row()->salutation . ' '. $hr_info->row()->firstname .' '.$hr_info->row()->lastname;
					}
				}


				$message = $this->template->prep_message($template['body'], $mrf);
				// If queued successfully set the status to For Approval.
				if ($this->template->queue(implode(',', $recepients), '', $template['subject'], $message)) {
					$response->msg_type = 'success';
					$response->msg = 'Manpower Request Sent.';
				}
			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_record($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, self::PDF_TEMPLATE_ID);

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		if ($check_record->exist) {
			$vars = array();
			$this->db->where($this->key_field, $record_id);
			// Join approver positions.
			$this->db->select($this->module_table . '.*, 
								up.position AS position, dd.division,
								CONCAT(ur.firstname," ",ur.lastname) AS requested_by, d.department',false);

			$this->db->join('user_position up', 'up.position_id = '.$this->module_table .'.position_id', 'left');
			$this->db->join('user_company_department d', 'd.department_id = '.$this->module_table .'.department_id', 'left');
			$this->db->join('user_company_division dd', 'dd.division_id = '.$this->module_table .'.division_id', 'left');
			$this->db->join('user ur', 'ur.user_id = requested_by', 'left'); // requested by

			$result = $this->db->get($this->module_table);

			$raw_data = $result->row_array();

			$vars = $raw_data;
			
			$vars['education'] = '';
			if ($raw_data['recruitment_manpower_educational_id']) {
				$education = $this->db->get_where('recruitment_manpower_educational' , array('recruitment_manpower_educational_id' => $raw_data['recruitment_manpower_educational_id']))->row();
				$vars['education'] = $education->recruitment_manpower_educational;
			}
			
			$logo_2 = get_branding();

		
			$company = $this->db->get_where('user_company', array('company_id' => $raw_data['company_id']))->row();

			if(!empty($company->logo)) {
			  $logo_2 = '<img alt="" src="./'.$company->logo.'">';
			}
			$vars['company_logo'] = $logo_2;

			$employment_status = array("fixed_term","ojt","probationary","consultant","regular");

			foreach ($employment_status as $key => $value) {
				$vars[$value] = ' ';
			}

			switch($raw_data['status_id'])
			{
				case 7:
					$vars['ojt'] = 'X';
					break;
				case 9:
					$vars['fixed_term'] = 'X';
					break;
				case 2:
					$vars['probationary'] = 'X';
					break;
				case 11:
					$vars['consultant'] = 'X';
					break;
				case 1:
					$vars['regular'] = 'X';
					break;	
			}
			
			$vars['created_date'] = date( $this->config->item('display_date_format'), strtotime($vars['created_date']));
			$vars['date_needed'] = ($vars['date_needed'] != "0000-00-00") ? date( $this->config->item('display_date_format'), strtotime($vars['date_needed'])) : '' ;  
			$vars['requested_date'] = ($vars['requested_date'] != "0000-00-00") ? date( $this->config->item('display_date_format'), strtotime($vars['requested_date'])) : '' ; 
			 
			

			$hra = $this->system->get_employee($raw_data['hra_id']);
			$hra_received = ($raw_data['contract_received'] != "0000-00-00") ? date( $this->config->item('display_date_format'), strtotime($raw_data['contract_received'])) : '' ;
			$vars['hra'] = $hra['firstname']. ' ' .$hra['lastname'] . ' / '. $hra_received;
			$vars['contract_received'] = $hra_received ;
			
			$this->db->where('request_id',$raw_data['request_id']);
			$this->db->where('status','Approved');
			$approvers = $this->db->get('recruitment_manpower_approver');	

			$vars['manpower_approvers'] = '<table style="width:100%;">
												<tbody>';
			if ($approvers && $approvers->num_rows() > 0) {
				foreach ($approvers->result() as $key => $approver) {
					$approved_by =  $this->system->get_employee($approver->approver);
					$vars['approved_date'] = ($approver->date_approved != "0000-00-00") ? date( $this->config->item('display_date_format'), strtotime($approver->date_approved)) : '' ; 
					$vars['approved_by'] = $approved_by['firstname'].' '. $approved_by['lastname'] . ' / '. $vars['approved_date'];
					$vars['manpower_approvers'] .= '<tr>
														<td style="width:20%;">&nbsp;</td>
														<td style="width:80%;text-align:center;border-bottom:1px solid black;">'.$vars['approved_by'].'</td>
													</tr>
													<tr>
														<td style="width:20%;">&nbsp;</td>
														<td style="width:80%;text-align:center;">Authorized Signatory / Date</td>
													</tr>
													<tr>
														<td style="width:20%;">&nbsp;</td>
														<td style="width:80%;text-align:center;">&nbsp;</td>
													</tr>';
				}
			}
			$vars['manpower_approvers'] .= '		</tbody>
											</table>';

			$vars['salary_range'] = ($vars['starting_salary'] != 0) ? number_format($vars['starting_salary'], 2 , '.' , ',') : '' ;
			
			$vars['others'] = ' ';
			if (!$raw_data['status_id']) {
				$vars['others'] = 'X';
			}

			$vars['budggeted'] = ' ';
			$vars['not_budggeted'] = ' ';

			$vars['budggeted_request_by'] = '';
			$vars['not_budggeted_request_by'] = ' ';
			
			if ($raw_data['reason_for_request'] == 1){
				$vars['budggeted'] = 'X';
				$vars['budggeted_request_by'] = $vars['requested_by'] .' / '; //.$vars['requested_date'];
			}
			else{
				$vars['not_budggeted'] = 'X';
				$vars['not_budggeted_request_by'] = $vars['requested_by'].' / '; //.$vars['requested_date'];
			}

			$vars['budgetted_new_position'] = ' ';
			if ($raw_data['budgetted_new_position'] == 1){
				$vars['budgetted_new_position'] = 'X';
			}

			$vars['budgetted_original_req'] = ' ';
			if ($raw_data['budgetted_original_req'] == 1){
				$vars['budgetted_original_req'] = 'X';
			}

			$vars['budgetted_ml_to'] = ' ';
			if ($raw_data['budgetted_ml_to'] == 1){
				$vars['budgetted_ml_to'] = 'X';
			}

			$vars['license'] = ' ';
			if ($raw_data['licensed'] == 1){
				$vars['license'] = 'X';
			}

			$vars['license'] = ' ';
			$vars['not_license'] = ' ';
			if ($raw_data['licensed'] == 1){
				$vars['license'] = 'X';
			}
			else{
				$vars['not_license'] = 'X';				
			}
			
			$this->db->where('mrf_id',$raw_data['request_id']);
			$this->db->where('candidate_status_id',6);
			$this->db->join('recruitment_applicant','recruitment_manpower_candidate.applicant_id = recruitment_applicant.applicant_id');
			$this->db->join('referred_by','recruitment_applicant.referred_by_id = referred_by.referred_by_id','left');
			$hired = $this->db->get('recruitment_manpower_candidate');
			$manpower_served = $hired; 

			$vars['manpower_served'] = '';
			if ($manpower_served && $manpower_served->num_rows() > 0){

				foreach ($manpower_served->result() as $row) {
                    $vars['manpower_served'] .=  '<tr>';
                    $vars['manpower_served'] .=  '<td style="border-left:1px solid black;border-bottom:1px solid black;border-right:1px solid black;text-align:center;">'. $row->firstname.' '.$row->lastname. '</td>';
                    $vars['manpower_served'] .=  '<td style="border-bottom:1px solid black;border-right:1px solid black;text-align:center;">'. $row->hired_date. '</td>';
                    $vars['manpower_served'] .=  '<td style="border-bottom:1px solid black;border-right:1px solid black;text-align:center;">'. $row->referred_by. '</td>';
                    $vars['manpower_served'] .=  '<td style="border-bottom:1px solid black;border-right:1px solid black;text-align:center;">'. number_format($starting_salary,2, '.', ','). '</td>';
                    $vars['manpower_served'] .=  '</tr>'; 
              
                }
             

             }else{
	                $vars['manpower_served'] = '<tr>
						<td style="border-left:1px solid black;border-bottom:1px solid black;border-right:1px solid black;text-align:center;">
							&nbsp;</td>
						<td style="border-bottom:1px solid black;border-right:1px solid black;text-align:center;">
							&nbsp;</td>
						<td style="border-bottom:1px solid black;border-right:1px solid black;text-align:center;">
							&nbsp;</td>
						<td style="border-bottom:1px solid black;border-right:1px solid black;text-align:center;">
							&nbsp;</td>
					</tr>';

             }

			$html = $this->template->prep_message($template['body'], $vars, false);
			// dbug($html);
			// die();
			// Prepare and output the PDF.
			$this->pdf->SetMargins('5', '5', '5');
		
			//set auto page breaks
			$this->pdf->SetAutoPageBreak(TRUE, 5);
			
			// set font
			$this->pdf->SetFont('helvetica', '', 9);
			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Manpower Request - ' . $vars['document_number'] . '.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function evaluation_request($record_id = 0){

		if ($this->input->post('record_id')) {
			$record_id = $this->input->post('record_id');
		}

		$this->db->where('request_id', $record_id);
		$request = $this->db->get(self::MANPOWER_REQUEST_TABLE);

		if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

			$remarks_field = "";

			if( $this->config->item('hr') == $this->userinfo['user_id'] ){
				$remarks_field = "hra_remarks";
			}
			else{
				$remarks_field = "approver_remarks";
			}

			$this->db->update(self::MANPOWER_REQUEST_TABLE,array('status'=>'For Evaluation',$remarks_field=>$this->input->post('remarks')),array('request_id'=>$record_id));

			$response['type'] = 'success';
			$response['message'] = 'Manpower Request approved.';

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);


		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}


	}

	function review_request($record_id = 0){


		if ($this->input->post('record_id')) {
			$record_id = $this->input->post('record_id');
		}

		$this->db->where('request_id', $record_id);
		$request = $this->db->get(self::MANPOWER_REQUEST_TABLE);

		if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

			$this->db->delete('recruitment_manpower_approver',array('request_id'=>$record_id));

			$record = $request->row();

			if( ( $record->position_for == 1 ) || ( $record->position_for == 0 && $record->status_id == 1 ) ){ // Life and Non - Life / Regular

				$this->db->where('position_id', $this->userinfo['position_id']);			
				$position_id  = $this->db->get('user_position')->row();

				$record = $this->db->get_where($this->module_table,array('request_id'=>$record_id))->row();

				//Note: In Setting the approver in employee approver, add the approver
				// in the following sequence: hr, ceo, odpm, division head
				$approvers_per_position = $this->system->get_approvers_and_condition($record->requested_by, $this->module_id);

				foreach( $approvers_per_position as $approver ){

					//Check user access
					$user = $this->hdicore->_get_userinfo( $approver['approver'] );
					$user_access = $this->hdicore->_create_user_access_file( $user );
					$approvers[$approver['sequence']] = $approver['approver'];

				}

				foreach($approvers as $key => $val){
					$info['request_id'] = $record_id;
					$info['approver'] = $val;
					$info['sequence'] = $key;
					$info['status'] = "For Approval";
					$this->db->insert('recruitment_manpower_approver', $info);
				}

			}
			elseif( $record->position_for == 0 && $record->status_id != 1 ){ // Non-Life / Non - Regular

				$this->db->where('position_id', $this->userinfo['position_id']);			
				$position_id  = $this->db->get('user_position')->row();

				$record = $this->db->get_where($this->module_table,array('request_id'=>$record_id))->row();

				//Note: In Setting the approver in employee approver, add the approver
				// in the following sequence: hr, ceo, odpm, division head
				$approvers_per_position = $this->system->get_approvers_and_condition($record->requested_by, $this->module_id);

				foreach( $approvers_per_position as $approver ){

					$user = $this->hdicore->_get_userinfo( $approver['approver'] );
					$user_access = $this->hdicore->_create_user_access_file( $user );

					//if( $user_access[$this->module_id]['post'] != 1 ){ //Not HR Approver

						$approver_sequence = $approver['sequence'];
						$approver_id = $approver['approver'];

					//}
					//else{

					//	$approvers[$approver['sequence']] = $approver['approver'];

					//}
				}

				$approvers[$approver_sequence] = $approver_id;

				foreach($approvers as $key => $val){
					$info['request_id'] = $record_id;
					$info['approver'] = $val;
					$info['sequence'] = $key;
					if($key == 1){
						$info['status'] = "For Approval";	
					}else{
						$info['status'] = "Draft";
					}
					
					$this->db->insert('recruitment_manpower_approver', $info);
				}

			}

			$this->db->update(self::MANPOWER_REQUEST_TABLE,array('status'=>'For Approval'),array('request_id'=>$record_id));

			$response['type'] = 'success';
			$response['message'] = 'Manpower Request approved.';

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function approve_request($record_id = 0) {


		if ($this->input->post('record_id')) {
			$record_id = $this->input->post('record_id');
		}

		$this->db->where('request_id', $record_id);
		$request = $this->db->get(self::MANPOWER_REQUEST_TABLE);

		if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {			
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$manpower_request = $request->row_array();

				// Get request details. (This returns the fieldgroup array.
				$check_record = $this->_record_exist($record_id);
				if ($check_record->exist) {
					//For HR
					// remove result->num_rows() validation.. there is an issue with (existing->hr approve) not approving because during ajax_save user with post access we're not included on manpower_approver.. I assume there is a validation on grid button
					if($this->user_access[$this->module_id]['post'] == 1){

						$update['concurred_approved'] = 1;
						$update['concurred_date']     = date('Y-m-d');
						$update['status'] = 'Approved';		
						$update['approved_by'] = $this->userinfo['user_id'];						
						$update['approved_date'] = date('Y-m-d');								
						$update['approver_approved'] = 1;	
						$update['approver_remarks'] = $manpower_request['approver_remarks'] . '<br/>' .'Admin - '. $this->input->post('remarks');	

					}else{ // For Non HR
						$this->db->where('request_id',$manpower_request['request_id']);
						$this->db->where('approver',$this->userinfo['user_id']);
						$result= $this->db->get('recruitment_manpower_approver');
						$approver_name = $this->userinfo['firstname'].' '.$this->userinfo['lastname'];

						if ($result && $result->num_rows() > 0){
							$approver_cur = $result->row();
							$approver_arr = $result->result();

							$sql = "SELECT * FROM {$this->db->dbprefix}recruitment_manpower_approver rma 
									WHERE rma.request_id = {$manpower_request['request_id']}
									AND rma.approver <> {$this->userinfo['user_id']}
									AND rma.status <> 'Approved'";
							$result = $this->db->query($sql);

							if ($result->num_rows() < 1){
								$update['concurred_approved'] = 1;
								$update['concurred_date'] = date('Y-m-d');
								$update['status'] = 'Approved';
								$update['approved_by'] = $this->userinfo['user_id'];
								$update['approved_date'] = date('Y-m-d');
								$update['approver_approved'] = 1;
								$update['approver_remarks'] = $manpower_request['approver_remarks'] . '<br/>' .$approver_name .' - '. $this->input->post('remarks');
							}
							else{
								$update['status'] = 'For Approval';
								$update['approver_remarks'] = $manpower_request['approver_remarks'] . '<br/>' . $approver_name .' - '. $this->input->post('remarks');
							}

							$approver_update['status'] = 'Approved';
							$approver_update['date_approved'] = date('Y-m-d h-i-s');
							$this->db->update('recruitment_manpower_approver', $approver_update, array('request_id' => $record_id,'approver'=>$this->userinfo['user_id']));
							
							$next_approver = $this->db->get_where('recruitment_manpower_approver', array('sequence' => ($approver_cur->sequence+1), 'request_id' => $record_id));
							if ($next_approver && $next_approver->num_rows() == 1) {
								$this->db->update('recruitment_manpower_approver', array('status' => 'For Approval'), array('request_id' => $record_id,'approver'=>$next_approver->row()->approver));
							}
						}
					}

					$this->db->update(
						self::MANPOWER_REQUEST_TABLE, 
						$update, 
						array('request_id' => $record_id)
					);

					
					
					if($update['status'] == 'Approved'){
						$response['type'] = 'success';
						$response['message'] = 'Manpower Request approved.';
						// Send email. TO WHO??
						// This part could be combined with send_email() method.
						$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
						if ($mail_config) {
							$approver_info_array = $this->get_approvers($record_id);

							// settings.
							$where_in = "FIND_IN_SET(" .$manpower_request['division_id']. ", division_id)";  //division_id IN (" .$manpower_request['division_id']. ")";
							$this->db->where($where_in);
							$settings = $this->db->get('recruitment_manpower_settings');
						
							if ($settings && $settings->num_rows() > 0) {
								$settings = $settings->row_array();
								$cc = $settings['email_to'];
							}

							$vars = array('remarks' => $this->input->post('remarks'));
							$requested_by = $this->system->get_employee($manpower_request['requested_by']);
							
							$vars['requested_by'] = $requested_by['salutation']. ' ' . $requested_by['firstname']. ' ' .$requested_by['lastname'];
							$vars['requested_date'] = date('F d, Y ', strtotime($manpower_request['requested_date']));

							$vars['details'] = "This is to inform you that your Manpower Request Form dated ".$vars['requested_date']." has been approved.
							HR shall source for your manpower requirement. Interview schedules of shortlisted candidates will be coordinated with you.";

							// Load the template.            
							$this->load->model('template');
							$template = $this->template->get_template(self::APPROVAL_TEMPLATE_ID);
							$message = $this->template->prep_message($template['body'], $vars);

							// Get recepients.
							$recepients = array();
							foreach ($approver_info_array as $key => $value) {
								// $recepients[] = $value['approver'];
							}
							
							array_push($recepients, $manpower_request['requested_by']);

							$this->db->where_in('user_id', $recepients);
							$result = $this->db->get('user')->result_array();

							$recepients = array();

							foreach ($result as $row) {
								$recepients[] = $row['email'];
							}

							// If queued successfully set the status to Approved.
							$this->template->queue(implode(',', $recepients), $cc, $template['subject'], $message);											
						}	
					}else{
						$this->send_email();
					}											
				} else {
					$response['type'] = 'error';
					$response['message'] = 'Record does not exist.';
				}
			} else {
				$response['type'] = 'error';
				$response['message'] = 'Outgoing mail settings not configured.';
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	

	function decline_request($record_id = 0) {
		if ($this->input->post('record_id')) {
			$record_id = $this->input->post('record_id');
		}

		$this->db->where('request_id', $record_id);
		$request = $this->db->get(self::MANPOWER_REQUEST_TABLE);

		if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$manpower_request = $request->row_array();
				// Get request details. (This returns the fieldgroup array.
				$check_record = $this->_record_exist($record_id);
				if ($check_record->exist) {
					// Send email. TO WHO??
					// This part could be combined with send_email() method.
					$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
					if ($mail_config) {
						$vars = array('remarks' => $this->input->post('remarks'));

						$requested_by = $this->system->get_employee($manpower_request['requested_by']);
						$vars['requested_by'] = $requested_by['salutation']. ' ' . $requested_by['firstname']. ' ' .$requested_by['lastname'];
						$vars['requested_date'] = date('F d, Y ', strtotime($manpower_request['requested_date']));

						$vars['details'] = "We regret to inform you that your Manpower Request Form dated ".$vars['requested_date']." has been disapproved.
											Should you have any clarifications, please coordinate with the Human Resources Department.";
						// Load the template.            
						$this->load->model('template');
						$template = $this->template->get_template(self::APPROVAL_TEMPLATE_ID);
						$message = $this->template->prep_message($template['body'], $vars);

						// Get recepients.

						$approver_info_array = $this->get_approvers($record_id);

						$recepients_id = array();
						foreach ($approver_info_array as $key => $value) {
							// $recepients_id[] = $value['approver'];
						}
						array_push($recepients_id, $manpower_request['requested_by']);
						$this->db->where_in('user_id', $recepients_id);
						$result = $this->db->get('user')->result_array();

						$recepients = array();

						foreach ($result as $row) {
							$recepients[] = $row['email'];
						}

						// if (in_array($this->userinfo['user_id'],$recepients_id) && $manpower_request['requested_by'] != $this->userinfo['user_id']) {
							$status = 'Declined';
						// } 
						// else{
						// 	$status = 'Cancelled';
						// }
						
						// If queued successfully set the status to Approved.
						if ($this->template->queue(implode(',', $recepients), '', $template['subject'], $message)) {
							$this->db->update(
								self::MANPOWER_REQUEST_TABLE, array('status' => $status, 'approved_date' => '0000-00-00','approver_remarks' => $this->input->post('remarks')), array('request_id' => $record_id)
							);

							$approver_update['status'] = $status;
							$approver_update['date_cancelled'] = date('Y-m-d h-i-s');
							$this->db->update('recruitment_manpower_approver',$approver_update,array('request_id' => $record_id,'approver'=>$this->userinfo['user_id']));							
						}
					}

					$response['type'] = 'success';
					$response['message'] = 'Manpower Request cancelled.';
				} else {
					$response['type'] = 'error';
					$response['message'] = 'Record does not exist.';
				}
			} else {
				$response['type'] = 'error';
				$response['message'] = 'Outgoing mail settings not configured.';
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {

		$record = $this->db->get_where($this->module_table,array('request_id'=>$row['request_id']))->row();

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		// Right align action buttons.
		$actions = '<span class="icon-group">';

		$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';


		switch( $row['status'] ){

			case 'Draft':

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_edit($row)) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_delete($row)) {
					$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				}
				
			break;
			case 'For HR Review':

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_email($row)) {
					$actions .= '<a class="icon-button icon-16-send-email" tooltip="Send Request" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				$approvers_per_position = $this->system->get_approvers_and_condition($record->requested_by, $this->module_id);

				foreach( $approvers_per_position as $approver ){

					$user = $this->hdicore->_get_userinfo( $approver['approver'] );
					$user_access = $this->hdicore->_create_user_access_file( $user );

					if( $user_access[$this->module_id]['post'] == 1 && $approver['approver'] == $this->userinfo['user_id'] ){ // HR Approver

						$actions .= '<a class="icon-button icon-16-tick mark-reviewed" tooltip="Mark As Reviewed" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
						// $actions .= '<a class="icon-button icon-16-notify for-evaluation" tooltip="For Evaluation" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';

					}
				}

				if( $this->user_access[$this->module_id]['post'] == 1 ){ // HR Approver

					$actions .= '<a class="icon-button icon-16-tick mark-reviewed" tooltip="Mark As Reviewed" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
					// $actions .= '<a class="icon-button icon-16-notify for-evaluation" tooltip="For Evaluation" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';

				}

				if ($this->_can_edit($row) && $record->requested_by == $this->userinfo['user_id']) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}
			break;
			case 'For Approval':

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_email($row)) {
					$actions .= '<a class="icon-button icon-16-send-email" tooltip="Send Request" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_approve($row)) {
					// icon-16-tick-button
					$actions .= '<a class="icon-button icon-16-approve approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				}
				
				if ($this->_can_decline($row)) {
					// icon-16-cancel
					$tooltip = 'Disapprove';
					$actions .= '<a class="icon-button icon-16-disapprove cancel-single" tooltip="' . $tooltip . '" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				}

				/*if ($this->_can_decline($row)) {
					$actions .= '<a class="icon-button icon-16-notify for-evaluation" tooltip="For Evaluation" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				}*/

				/*if ($this->_can_edit($row)) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}*/

			break;
			case 'Approved':

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_email($row)) {
					$actions .= '<a class="icon-button icon-16-send-email" tooltip="Send Request" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				/*if ($this->_can_approve($row)) {
					$actions .= '<a class="icon-button icon-16-tick-button approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				}*/
				
				if ($this->_can_decline($row)) {
					$tooltip = 'Cancel';
					$actions .= '<a class="icon-button icon-16-disapprove cancel-single" tooltip="' . $tooltip . '" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				}

				if ($this->user_access[$this->module_id]['edit'] == 1 && $this->user_access[$this->module_id]['post'] == 1) { // && $this->system->check_if_approver($this->module_id)
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}
			break;
			case 'Declined':

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_email($row)) {
					$actions .= '<a class="icon-button icon-16-send-email" tooltip="Send Request" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				/*if ($this->_can_edit($row)) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}*/

			break;
			case 'For Evaluation':

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_email($row)) {
					$actions .= '<a class="icon-button icon-16-send-email" tooltip="Send Request" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				/*if ( ( $this->_can_edit($row) )  && ( $row['created_by'] == $this->userinfo['user_id'] ) || (CLIENT_DIR == 'firstbalfour' && $this->user_access[$this->module_id]['approve'] == 1  ) ) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}*/

				/*if ($this->_can_edit($row)) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}*/

			break;
			case 'Waiting':

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_email($row)) {
					$actions .= '<a class="icon-button icon-16-send-email" tooltip="Send Request" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				/*if ($this->_can_edit($row)) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}*/

			break;
			case 'In-Process':

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_email($row)) {
					$actions .= '<a class="icon-button icon-16-send-email" tooltip="Send Request" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				/*if ($this->_can_edit($row)) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}*/

			break;
			default:

				if ($this->user_access[$this->module_id]['print']) {
					$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				if ($this->_can_email($row)) {
					$actions .= '<a class="icon-button icon-16-send-email" tooltip="Send Request" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}

				/*if ($this->_can_edit($row)) {
					$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
				}*/

				if ($this->_can_delete($row)) {
					$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				}

				if ($row['status'] != 'Closed'){
					if ($this->_can_approve($row)) {
						// icon-16-tick-button
						// $actions .= '<a class="icon-button icon-16-approve  approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
					}
					
					if ($this->_can_decline($row)) {
						if ($row['status'] == 'Approved') {
							$tooltip = 'Cancel';
						} else {
							$tooltip = 'Decline';
						}

						$actions .= '<a class="icon-button icon-16-disapprove cancel-single" tooltip="' . $tooltip . '" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
					}
				}

			break;
		}

		if ($this->user_access[$this->module_id]['post']) {
			$actions .=  '<a href="javascript:void(0)" onclick="get_tat('.$row['request_id'].')" class="icon-button icon-16-document-stack"></a>';
		}
		$actions .= '</span>';

		return $actions;
	}


	function show_related_module() {

		if (IS_AJAX) {
			$data['container'] = $this->module . '-fmlink-container';
			$data['pager'] = $this->module . '-fmlink-pager';
			$data['fmlinkctr'] = $this->input->post('fmlinkctr');

			//set default columnlist
			$this->_set_listview_query();

			//set grid buttons
			$data['jqg_buttons'] = $this->_listview_in_boxy_grid_buttons();
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/listview_in_boxy.js"></script>';

			//set load jqgrid loadComplete callback
			$data['jqgrid_loadComplete'] = "";

			$this->load->vars($data);
			$boxy = $this->load->view($this->userinfo['rtheme'] . "/listview_in_boxy", $data, true);

			$data['html'] = $boxy;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}


	function get_position_description() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url().$this->module_link);
		} else {
			$record_id = $this->input->post('record_id');

			$this->db->select(array('duties_responsibilities', 'education', 'licensure', 'experience', 'others'));	
			$this->db->where('position_id', $record_id);

			$record = $this->db->get('user_position');

			$this->load->view('template/ajax', array('json' => $record->row()));
		}
	}		

	function get_manpower_settings($return = FALSE) {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url().$this->module_link);
		} else {
			$settings = $this->db->get('recruitment_manpower_settings')->row();

			$settings->lead_to_date = date('Y-m-d', strtotime('-' . $settings->lead_time . ' days', strtotime(date('Y-m-d'))));

			if (!$return) {
				$this->load->view('template/ajax', array('json' => $settings));				
			} else {
				return $settings;
			}
		}
	}

	/**
	 * Checks if the current manpower reequest can still be edited.
	 * 
	 * @param array $row
	 * @return boolean
	 */
	private function _can_edit($row = array()) {
		if (count($row) == 0) {
			return false;
		}
		if (CLIENT_DIR == 'firstbalfour' && $this->user_access[$this->module_id]['post'] == 1) {
			return true;
		}

		return (
			$this->user_access[$this->module_id]['edit'] == 1 &&
			(
				isset($row['status']) && 
				( $row['status'] == 'Draft' || $row['status'] == 'For Evaluation'
				) && $row['created_by'] == $this->userinfo['user_id']
			)
			);
	}

	/**
	 * Checks if the current manpower reequest can still be deleted.
	 * 
	 * @param array $row
	 * @return boolean
	 */
	private function _can_delete($row = array()) {
		if (count($row) == 0) {
			return false;
		}

		return (
			$this->user_access[$this->module_id]['delete'] == 1 &&
			isset($row['status']) && $row['status'] == 'Draft'
			);
	}

	/**
	 * Checks if the current user can send the email.
	 * 
	 * @param array $row
	 * @return boolean
	 */
	private function _can_email($row = array()) {
		if (count($row) == 0) {
			return false;
		}

		return (
			$this->user_access[$this->module_id]['email'] == 1 &&
			(isset($row['status']) && $row['status'] == 'Draft')
			);
	}

	/**
	 * Check if the current user can approve a request.
	 * 
	 * @param array $row
	 * @return boolean
	 */
	private function _can_approve($row = array()) {
		$odpm = $this->config->item('odpm');
		$ceo = $this->config->item('ceo');

		if (CLIENT_DIR === 'oams') {
			if ($this->user_access[$this->module_id]['approve'] && $row['created_by'] == $this->userinfo['user_id'] && $row['status'] != 'Declined' || $row['status'] != 'Approved') {
				return true;
			}		
		}
		if (CLIENT_DIR === 'firstbalfour') {
			if($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['approve']){
				return true;
			}
		}

		if (!$this->user_access[$this->module_id]['approve'] || $row['created_by'] == $this->userinfo['user_id'] || $row['status'] == 'Declined' || $row['status'] == 'Approved') {
			return false;
		}

		if (count($row) == 0) {
			return false;
		}


		//Life or Non-Life / Regular
		//if (($row['reason_for_request'] = 2 && $row['position_for'] == 1) || ($row['reason_for_request'] = 2 && $row['position_for'] == 0 && $row['status_id'] == 1)){
/*			if ($row['concurred_as_approver'] == 1 && ($this->userinfo['user_id'] == $odpm || $this->userinfo['user_id'] == $ceo))
			{
				return !$row['approver_approved'] && ($row['concurred_approved'] || $row['concurred_optional']);
			}*/


			if ( $this->userinfo['user_id'] != $row['approved_by'] ){

				$manpower_record = $this->db->get_where($this->module_table,array('request_id'=>$row['request_id']))->row();
				$approvers_per_position = $this->system->get_approvers_and_condition($manpower_record->requested_by, $this->module_id);
				$hr_approver = 0;

				foreach($approvers_per_position as $approver){

					$user = $this->hdicore->_get_userinfo( $approver['approver'] );
					$user_access = $this->hdicore->_create_user_access_file( $user );

					if( $user_access[$this->module_id]['post'] == 1 &&  $approver['approver'] == $this->userinfo['user_id'] ){

						$hr_approver = 1;

					}

				}

				if( $hr_approver == 1 ){

					return true;

				}else{

					$this->db->where('request_id',$row['request_id']);
					$this->db->where('approver',$this->userinfo['user_id']);
					$this->db->where('status','For Approval');
					$this->db->order_by('sequence','desc');
					$approvers_per_position = $this->db->get('recruitment_manpower_approver')->result_array();

					foreach( $approvers_per_position as $approver ){

						$user = $this->hdicore->_get_userinfo( $approver['approver'] );
						$user_access = $this->hdicore->_create_user_access_file( $user );


						if( $user_access[$this->module_id]['post'] != 1 ){

							if ( $approver['status'] != "Approved" && $approver['approver'] == $this->userinfo['user_id'] ){

								return true;

							}
							else{

								return false;

							}

						}
					}

				}

			}
			else {
				$this->db->where('request_id',$row['request_id']);
				$this->db->where('approver',$this->userinfo['user_id']);
				$result = $this->db->get('recruitment_manpower_approver');						
				if ($result && $result->num_rows() > 0){
					$row_approver = $result->row();
					if ($row_approver->status == "Approved" || $row_approver->status == 'Declined'){
						return false;
					}
					else{
						return true;
					}
				}
			}
		
		/*	
		}
		else{
			if ($row['concurred_as_approver'] == 1 && $this->userinfo['user_id'] == $row['approved_by'])
			{
				return !$row['approver_approved'] && ($row['concurred_approved'] || $row['concurred_optional']);
			}

			return (			
				isset($row['status']) && $row['status'] == 'For Approval' &&
				$row['status'] != 'Cancelled' &&
				(
					$this->userinfo['user_id'] == $row['approved_by'] 
					||
					($row['concurred_as_approver'] == 1 && $row['concurred_by'] == $this->userinfo['user_id'])
					)
				);
		}
		*/
	}

	private function _can_decline($row = array()) {	
		

		// if ($this->user_access[$this->module_id]['decline'] && $row['created_by'] == $this->userinfo['user_id'] && $row['status'] == 'For Approval') {
		// 	return true;			
		// }

		if (CLIENT_DIR === 'firstbalfour') {
			if($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['decline']){
				return true;
			}
		}

		if (!$this->user_access[$this->module_id]['decline'] || $row['status'] != 'For Approval' || $row['created_by'] == $this->userinfo['user_id'] ) {

			return false;			
		}

	
		if ( $this->userinfo['user_id'] != $row['approved_by'] ){

			$manpower_record = $this->db->get_where($this->module_table,array('request_id'=>$row['request_id']))->row();
			$approvers_per_position = $this->system->get_approvers_and_condition($manpower_record->requested_by, $this->module_id);

			$hr_approver = 0;

			if ($this->user_access[$this->module_id]['post']) {
				$hr_approver = 1;
			}

			foreach($approvers_per_position as $approver){

				$user = $this->hdicore->_get_userinfo( $approver['approver'] );
				$user_access = $this->hdicore->_create_user_access_file( $user );

				if( $user_access[$this->module_id]['post'] == 1 &&  $approver['approver'] == $this->userinfo['user_id'] ){

					$hr_approver = 1;

				}

			}


			if( $hr_approver == 1 ){

				return true;

			}else{

					$this->db->where('request_id',$row['request_id']);
					$this->db->where('approver',$this->userinfo['user_id']);
					$this->db->where('status','For Approval');
					$this->db->order_by('sequence','desc');
					$approvers_per_position = $this->db->get('recruitment_manpower_approver')->result_array();

					foreach( $approvers_per_position as $approver ){

						$user = $this->hdicore->_get_userinfo( $approver['approver'] );
						$user_access = $this->hdicore->_create_user_access_file( $user );


						if( $user_access[$this->module_id]['post'] != 1 ){

							if ( $approver['status'] != "Declined" && $approver['approver'] == $this->userinfo['user_id'] ){

								return true;

							}
							else{

								return false;

							}

						}
					}

				}

		}
		else {

			$this->db->where('request_id',$row['request_id']);
			$this->db->where('approver',$this->userinfo['user_id']);
			$result = $this->db->get('recruitment_manpower_approver');	
					
			if ($result && $result->num_rows() > 0){
				$row_approver = $result->row();
				if ($row_approver->status == "Approved" || $row_approver->status == 'Declined'){
					return false;
				}
				else{
					return true;
				}
			}
		}

		/*
		if ($row['status'] == "Approved" || $row['status'] == "For Approval"){
			return true;
		}
		else{
			return false;
		}

		*/


/*		if ($row['concurred_as_approver'] == 1 && $this->userinfo['user_id'] == $row['approved_by'])
		{
			return !$row['approver_approved'] && ($row['concurred_approved'] || $row['concurred_optional']);
		}

		return (
			(( $this->userinfo['user_id'] == $row['approved_by'] 
					|| ($row['concurred_as_approver'] == 1 && $row['concurred_by'] == $this->userinfo['user_id'])
				   )
			) && $row['status'] == 'For Approval'
			|| ($this->userinfo['user_id'] == $row['requested_by'] && $row['status'] == 'Draft')		
			);*/
	}

	function get_manpower_info() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url().$this->module_link);
		} else {
			$record_id = $this->input->post('record_id');
			$this->db->where('request_id', $record_id);
			$record = $this->db->get('recruitment_manpower');

			$this->load->view('template/ajax', array('json' => $record->row()));
		}
	}	

	function get_annual_manpower_planning() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url().$this->module_link);
		} else {
			$position_id = $this->input->post('position_id');
			$number_required = $this->input->post('number_required');
			$date_needed = $this->input->post('date_needed');

			$this->db->where('position_id', $position_id);
			$this->db->where('year', date('Y',strtotime($date_needed)));
			$this->db->join('annual_manpower_planning_position','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id');
			$record = $this->db->get('annual_manpower_planning');

			if ($record && $record->num_rows() > 0){
				$amp_info_arr = $record->row_array();

				$this->db->select_sum('number_required');
				$this->db->where('position_id', $position_id);
				$this->db->where('YEAR(approved_date)', date('Y',strtotime($date_needed)));
				$record = $this->db->get('recruitment_manpower');

				if ($record && $record->num_rows() > 0){
					$manpower = $record->row_array();
					$amp_info_arr['total_number_required_manpower'] = $manpower['number_required'] + $number_required;
				}
				else{
					$amp_info_arr['total_number_required_manpower'] = $manpower['number_required'];
				}
				$this->load->view('template/ajax', array('json' => $amp_info_arr));				
			}
		}
	}		

	function get_approvers($request_id){
		$this->db->where('request_id',$request_id);
		$result = $this->db->get('recruitment_manpower_approver');
		if ($result && $result->num_rows() > 0){
			return $result->result_array();
		}
		else{
			return false;
		}
	}

	/*
	 * current run of approver. base client
	 * Note: employee approver must be in this sequence: hr, ceo, odpm, division head
	 * Status: By Level
	 */
	function get_correct_approver()
	{
		// if($this->input->post('record_id') == -1) {
			// If long process of approval
			// if(($this->input->post('reason_for_request') == 2 && $this->input->post('position_for') == 1) || ($this->input->post('reason_for_request') == 2 && $this->input->post('position_for') == 0 && $this->input->post('status_id') == 1)){
				if($this->input->post('record_id') == -1){
					$user_id = $this->userinfo['user_id'];
				}else{
					$manpower = $this->db->get_where('recruitment_manpower', array('request_id' => $this->input->post('record_id')))->row_array();
					$user_id = $manpower['requested_by'];
				}
				$approvers_per_position = $this->system->get_approvers_and_condition($user_id, $this->module_id);
				$approvers_arr = array();

				foreach($approvers_per_position as $approver) {
					$user = $this->hdicore->_get_userinfo($approver['approver']);
					$user_access = $this->hdicore->_create_user_access_file($user);

					// if($user_access[$this->module_id]['post'] != 1){ //Not HR Approver
						$approver_sequence = $approver['sequence'];
						$approvers_arr[] = $user->firstname.' '.$user->middleinitial.' '.$user->lastname;;
					// }
				}

				$response->data = implode(', ', $approvers_arr);

				$this->load->view('template/ajax', array('json' => $response));

			// } else { // Shortcut approval

			// 	$approvers_per_position = $this->system->get_approvers_and_condition($this->userinfo['user_id'], $this->module_id);
			// 	$approvers_id = '';

			// 	foreach( $approvers_per_position as $approver ){
			// 		$user = $this->hdicore->_get_userinfo($approver['approver']);
			// 		$user_access = $this->hdicore->_create_user_access_file($user);
					
			// 		//if($user_access[$this->module_id]['post'] != 1){ //Not HR Approver
			// 			$approvers_id = $user->firstname.' '.$user->middlename.' '.$user->lastname;
			// 		//}
			// 	}

			// 	$response->data = $approvers_id;

			// 	$this->load->view('template/ajax', array('json' => $response));

			// }
		// } else {
		// 	// get record value
		// 	$manpower = $this->db->get_where('recruitment_manpower', array('request_id' => $this->input->post('record_id')))->row_array();
		// 	$user_id = $manpower['requested_by'];

		// 	// If long process of approval
		// 	if(($manpower['reason_for_request'] == 2 && $manpower['position_for'] == 1) || ($manpower['reason_for_request'] == 2 && $manpower['position_for'] == 0 && $manpower['status_id'] == 1)) {

		// 		$approvers_per_position = $this->system->get_approvers_and_condition($user_id, $this->module_id);
		// 		$approvers_arr = array();

		// 		foreach($approvers_per_position as $approver) {
		// 			$user = $this->hdicore->_get_userinfo($approver['approver']);
		// 			$user_access = $this->hdicore->_create_user_access_file($user);

		// 			if($user_access[$this->module_id]['post'] != 1) { //Not HR Approver
		// 				$approver_sequence = $approver['sequence'];
		// 				$approvers_arr[] = $user->firstname.' '.$user->middlename.' '.$user->lastname;;
		// 			}
		// 		}

		// 		$response->data = implode(',', $approvers_arr);

		// 		$this->load->view('template/ajax', array('json' => $response));

		// 	} else { // Shortcut approval

		// 		$approvers_per_position = $this->system->get_approvers_and_condition($user_id, $this->module_id);
		// 		$approvers_id = '';

		// 		foreach( $approvers_per_position as $approver ){
		// 			$user = $this->hdicore->_get_userinfo($approver['approver']);
		// 			$user_access = $this->hdicore->_create_user_access_file($user);
					
		// 			//if($user_access[$this->module_id]['post'] != 1) { //Not HR Approver
		// 				$approvers_id = $user->firstname.' '.$user->middlename.' '.$user->lastname;
		// 		//	}
		// 		}

		// 		$response->data = $approvers_id;

		// 		$this->load->view('template/ajax', array('json' => $response));

		// 	}
		// }

	}

	function is_hr() {
		if(!$this->user_access[$this->module_id]['post'] == 1)
			$status = false;
		else
			$status = true;

		/*if($this->system->check_if_approver($this->module_id))
			$status = true;
		else
			$status = false;*/

		$this->load->view('template/ajax', array('json' => $status));
	}

	function get_category_value(){
		$category_id = $this->input->post('category_id');

		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('recruitment_manpower',array("request_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$mapower_category_id = $row->category_id;
				$defaults = explode(',', $row->category_value_id);
			}
		}

		$options = '';
		switch ($category_id) {
			case 1: //by company
				$this->db->where('deleted',0);
				$result = $this->db->get('user_company');
				if ($result && $result->num_rows() > 0){
					$options .= '<option value="">Select...</option>';
					foreach ($result->result() as $row) {
						if ($mapower_category_id == $category_id){
							$selected = (in_array($row->company_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->company_id . '"' . $selected . '>' . $row->company . '</option>';
					}
				}
				break;
			case 2: //by division
				$this->db->where('deleted',0);
				$result = $this->db->get('user_company_division');
				if ($result && $result->num_rows() > 0){
					$options .= '<option value="">Select...</option>';
					foreach ($result->result() as $row) {
						if ($mapower_category_id == $category_id){
							$selected = (in_array($row->division_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->division_id . '"' . $selected . '>' . $row->division . '</option>';
					}
				}
				break;
			case 3: //by group
				$this->db->where('deleted',0);
				$result = $this->db->get('group_name');
				if ($result && $result->num_rows() > 0){
					$options .= '<option value="">Select...</option>';
					foreach ($result->result() as $row) {
						if ($mapower_category_id == $category_id){
							$selected = (in_array($row->group_name_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->group_name_id . '"' . $selected . '>' . $row->group_name . '</option>';
					}
				}
				break;
			case 4: //by department
				$this->db->where('deleted',0);
				$result = $this->db->get('user_company_department');
				if ($result && $result->num_rows() > 0){
					$options .= '<option value="">Select...</option>';
					foreach ($result->result() as $row) {
						if ($mapower_category_id == $category_id){
							$selected = (in_array($row->department_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->department_id . '"' . $selected . '>' . $row->department . '</option>';
					}
				}
				break;
			case 5: //by project
				if ($this->input->post('division_id')){
					$this->db->where('division_id',$this->input->post('division_id'));
				}
				$this->db->where('deleted',0);
				$result = $this->db->get('project_name');
				if ($result && $result->num_rows() > 0){
					$options .= '<option value="">Select...</option>';
					foreach ($result->result() as $row) {
						if ($mapower_category_id == $category_id){
							$selected = (in_array($row->project_name_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->project_name_id . '"' . $selected . '>' . $row->project_name . '</option>';
					}
				}
				break;																			
		}
		$this->load->view('template/ajax', array('html' => $options));
	}	

	function get_proj_dept()
	{
		$manpower_info_result = $this->db->get_where('recruitment_manpower',array('request_id' => $this->input->post('record_id')));

		if ($manpower_info_result && $manpower_info_result->num_rows() > 0){
			$manpower_info_row = $manpower_info_result->row();
			$cat_info = $this->system->get_recruitment_category($manpower_info_row->category_id,$manpower_info_row->category_value_id);
		}

		$response->data = $cat_info['cat_value'];

		$this->load->view('template/ajax', array('json' => $response));			
	}

	function get_tat()
	{
		$mrf_id = $this->input->post('record_id');
		$manpower_info_row = $this->db->get_where($this->module_table,array('request_id'=>$mrf_id))->row();
		
	
		if ($manpower_info_row->contract_received  != "0000-00-00" && $manpower_info_row->contract_received != "" ){

			$contract_received = new DateTime($manpower_info_row->date_from);
			$current_date = new DateTime();
			$date_diff = $contract_received->diff($current_date);

			$days_passed = $date_diff->d;

			$cur_date = date('Y-m-d');
			$count = 0;
			$received = $manpower_info_row->contract_received;
		
			$current = strtotime($cur_date);
			$start_fill_rate = strtotime($manpower_info_row->date_from);

			if ($current >= $start_fill_rate) {
				$days_passed = ceil(($current - $start_fill_rate) / (60 * 60 * 24));
			}else{
				$days_passed = 0;
			}
			
			$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);

		}else{
			$weekends = 0;
			$days_passed = 0;
		}

		$tat_computed = $days_passed - $weekends;

		$response->html = $tat_computed;

		$this->load->view('template/ajax', array('json' => $response));	
	}

	// Original get tat before ticket 3105 was do.
	function get_tat_org()
	{
		$mrf_id = $this->input->post('record_id');
		$manpower_info_row = $this->db->get_where($this->module_table,array('request_id'=>$mrf_id))->row();
		
	
		if ($manpower_info_row->contract_received  != "0000-00-00" && $manpower_info_row->contract_received != "" ){

			$contract_received = new DateTime($manpower_info_row->date_from);
			$current_date = new DateTime();
			$date_diff = $contract_received->diff($current_date);

			$days_passed = $date_diff->d;

			$cur_date = date('Y-m-d');
			$count = 0;
			$received = $manpower_info_row->contract_received;
		
			$current = strtotime($cur_date);
			$start_fill_rate = strtotime($manpower_info_row->date_from);

			if ($current >= $start_fill_rate) {

				$days_passed = ($current - $start_fill_rate) / (60 * 60 * 24);
			}else{
				$days_passed = 0;
			}
			
			$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);

		}else{
			$weekends = 0;
			$days_passed = 0;
		}


		$jo = $this->system->get_accepted_jo();
		
		if (isset( $manpower_info_row->status ) && $manpower_info_row->status == 'In-Process') {
			if($manpower_info_row->date_from != "0000-00-00" && $manpower_info_row->date_from != ""){
				$start_fill_rate = strtotime($manpower_info_row->date_from);
				if ($jo->accepted_jo >= $manpower_info_row->number_required){
					$days_passed = (strtotime($jo->accepted_date) - $start_fill_rate) / (60 * 60 * 24);								
					$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);
				}
			}else{
				$weekends = 0;
				$days_passed = 0;
			}
			

			$tat_computed = ($manpower_info_row->turn_around_time - $days_passed) + $weekends;

		}elseif (isset( $manpower_info_row->status ) && $manpower_info_row->status == 'Closed') {
			if($manpower_info_row->date_from != "0000-00-00" && $manpower_info_row->date_from != ""){
				$days_passed = (strtotime($manpower_info_row->date_closed) - $start_fill_rate) / (60 * 60 * 24);								
				$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);

				if ($jo->accepted_jo >= $manpower_info_row->number_required){
					$days_passed = (strtotime($jo->accepted_date) - $start_fill_rate) / (60 * 60 * 24);								
					$weekends = $this->get_weekends($manpower_info_row->date_from, $days_passed);
				}
			}else{
				$weekends = 0;
				$days_passed = 0;
			}
			
			$tat_computed = ($manpower_info_row->turn_around_time - $days_passed) + $weekends;

		}
		else{
			$tat_computed = ($manpower_info_row->turn_around_time) - ($days_passed + $weekends);
		}	

		$response->html = $tat_computed;

		$this->load->view('template/ajax', array('json' => $response));	
	}

	function get_weekends($start, $tat, $ajax = false)
	{	
		if ($this->input->post('is_ajax')) $ajax = true;
		
		if ($ajax) {			
			$start = date ("Y-m-d", strtotime($this->input->post('start_date'))); 
			$tat = $this->input->post('tat');
		}

		$count = 0;
		$flag = 1;
		
		$weekends = array();

		while ($flag <= $tat) 
		{
			$timestamp = strtotime($start);  
			$day = date('D', $timestamp); 

			$holiday = $this->system->holiday_check($start);

			if($day=='Sat' || $day=='Sun' || $holiday)
			{   
				$count++ ; 

			}
			else{

				if ($ajax) $flag++;
			}

			$start = date ("Y-m-d", strtotime("+1 day", strtotime($start)));
			
			if (!$ajax) {
				$flag++;
			}
						
		}

		$end_date = date('m/d/Y', strtotime($start));

		if ($ajax) {
			$response->end_date = $end_date;
			$this->load->view('template/ajax', array('json' => $response));			
		}else{
			return $count;
		}

	}

	function get_department_list(){

		$division_id = 0;
		$html = "";

		if( $this->input->post('division_id') ){
			$division_id = $this->input->post('division_id');
			$this->db->where('division_id',$division_id);
		}
	
		$this->db->where('deleted',0);
		$department_result = $this->db->get('user_company_department');

		if( $department_result->num_rows() > 0 ){
			$html .= "<select id='department_id' name='department_id' style='' >";
			$html .= "<option value=''></option>";

			foreach( $department_result->result() as $department_info ){
				$html .= "<option value='".$department_info->department_id."'>".$department_info->department."</option>";
			}

			$html .= "</select>";
		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

} 

/* End of file */
/* Location: system/application */