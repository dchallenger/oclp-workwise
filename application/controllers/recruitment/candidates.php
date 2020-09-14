<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Candidates extends MY_Controller {

	private $_hired_status_id, 
			$_rejected_status_id, 
			$_default_status_id, 
			$_interview_status_id,
			$_evaluation_status_id = 0,
			$_statuses,
			$_position_hierarchy = array();	

	function __construct() {
		parent::__construct();

		$this->load->helper('candidates');

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format                

		$this->listview_title         = 'Candidates';
		$this->listview_image         = 'icons/drawer.png';
		$this->listview_description   = 'Lists all candidates';
		$this->jqgrid_title           = "candidates List";
		$this->detailview_title       = 'candidate Info';
		$this->detailview_description = 'This page shows detailed information about a particular candidate';
		$this->editview_title         = 'candidate Add/Edit';
		$this->editview_description   = 'This page allows saving/editing information about an candidate';

		$statuses = get_candidate_statuses();
        foreach ($statuses as $index => $status) {

            if ($status['default'] == 1) {
                $this->_default_status_id = $status['candidate_status_id'];
            }

            if ($status['hired_flag'] == 1) {
                $this->_hired_status_id = $status['candidate_status_id'];
                unset($statuses[$index]);
            }

            if ($status['rejected_flag'] == 1) {
                $this->_rejected_status_id = $status['candidate_status_id'];                
            }

            if ($status['interview_flag'] == 1) {
                $this->_interview_status_id = $status['candidate_status_id'];
            }

            if ($status['evaluation_flag'] == 1) {
                $this->_evaluation_status_id = $status['candidate_status_id'];
            }            

            if ($status['joboffer_flag'] == 1) {
                $this->_joboffer_status_id = $status['candidate_status_id'];
            }               
        }
        $this->_statuses = $statuses;
		$data['module_filters'] = get_candidate_filters($this->_statuses);
		$data['module_filter_title'] = 'Candidates';
		$this->load->vars($data);
	}

	// START - default module functions
	// default jqgrid controller method
	function index($mrf_id = '') {
		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['scripts'][] = '<script src="'.base_url().'lib/modules/recruitment/candidates_listview.js" type="text/javascript"></script>';
		$data['content'] = 'recruitment/manpower/candidates/listview';
		$data['jqgrid'] = 'recruitment/manpower/candidates/template/jqgrid';

		if ($mrf_id > 0) {
			$candidate_list_module = $this->hdicore->get_module('candidate_list');

			$data['show_search'] = 	($this->user_access[$candidate_list_module->module_id]['visible'] == 1);
			$this->db->where('request_id', $mrf_id);
			$this->db->join('user_position', 'user_position.position_id = recruitment_manpower.position_id');

			$result = $this->db->get('recruitment_manpower');
			$mrf = $result->row_array();
			$data['mrf'] = $mrf;
		}

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		$data['default_query'] = true;
		$data['default_query_field'] = 'mrf_id';
		$data['default_query_val'] = $mrf_id;
		$data['mrf_id'] = $mrf_id;

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "jqgrid_loadComplete();";

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

	function detail()
	{
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

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

	function ajax_save() {

		$this->related_table = array('recruitment_candidate_job_offer' => 'candidate_id');
		$_POST['position_id'] = $_POST['mrf_position_id'];

		if ($this->input->post('candidate_status_id') == $this->_joboffer_status_id) {
			$_POST['job_offer_status_id'] = 1;
		}

		$applicants = $this->input->post('applicant_id');
		$employee_applicant = $this->input->post('employee_id');

		if( $applicants == '' && $employee_applicant == '' ){
			$candidate_info = $this->db->get_where($this->module_table, array('candidate_id'=>$this->input->post('record_id')))->row();
			$applicants = $candidate_info->applicant_id;
			$employee_applicant = $candidate_info->employee_id;
		}

		if (!is_array($employee_applicant)) {
			$employee_applicant = array($employee_applicant);
		}

		if (!is_array($applicants)) {
			$applicants = array($applicants);
		}

		if($this->input->post('is_internal')!=1){
			foreach ($applicants as $applicant_id):
				$_POST['applicant_id'] = $applicant_id;
				parent::ajax_save();
			endforeach;
		} else {
			foreach ($employee_applicant as $employee_applicant):
				$_POST['employee_applicant'] = $employee_applicant;
				parent::ajax_save();
				$employee = $this->db->get_where('employee', array('employee_id' => $employee_applicant) )->row();
				$this->db->where($this->key_field, $this->key_field_val);
				$this->db->update($this->module_table, array('applicant_id' => $employee->applicant_id, 'employee_id' => $employee_applicant));
			endforeach;
		}


		$result = $this->db->get_where('recruitment_candidates_appraisal',array('candidate_id'=>$this->key_field_val));
		if ($result && $result->num_rows() > 0){
			$appraisal_id = $result->row()->appraisal_id;
			
			$exams = json_encode($this->input->post('exam'));
			$interview = json_encode($this->input->post('interview'));
			$exam_date = date('Y-m-d H:i:s', strtotime($this->input->post('exam_date')));
			$date_from = ($this->input->post('employment_period_start') != "") ? date('Y-m-d H:i:s', strtotime($this->input->post('employment_period_start'))) : " " ;
			$date_to = ($this->input->post('employment_period_end') != "") ? date('Y-m-d H:i:s', strtotime($this->input->post('employment_period_end'))) : " " ; 
			$benifits = json_encode($this->input->post('benefit'));
			$others = json_encode($this->input->post('others'));

            $appraisal = array(
                    "exam_details"=>$exams,
                    "interview_details"=>$interview,
                    "screening_datetime"=>$exam_date,
                    "company"=>$this->input->post("company"),
                    "salary"=>$this->input->post("salary"),
                    "industry"=>$this->input->post("industry"),
                    "date_from"=>$date_from,
                    "date_to"=>$date_to,
                    "position"=>$this->input->post("position"),
                    "previous_emp_status"=>$this->input->post("prev_status"),
                    "level"=>$this->input->post("level"),
                    "benefits"=>$benifits,
                    "other_benefits"=>$others
                );
			
			$this->db->where('appraisal_id', $appraisal_id);
			$this->db->update('recruitment_candidates_appraisal', $appraisal);

			if ($this->input->post('candidate_status_id')) {
				$this->db->where('candidate_id', $this->key_field_val);				
				$this->db->update('recruitment_manpower_candidate', array('candidate_status_id'=>$this->input->post('candidate_status_id')));		
			}

			if ($this->input->post('attachment')) {
				$this->db->where('candidate_id', $this->key_field_val);				
				$this->db->update('recruitment_candidate_background_check', array('attachment'=>$this->input->post('attachment')));		
			}

			if ($this->input->post('interviewer_id')){
				$interview_date_arr = $this->input->post('interview_date');				
				$interviewer_info = array();				

            	$this->db->delete('recruitment_manpower_candidate_interviewer', array('candidate_id' => $this->key_field_val));

				foreach ($this->input->post('interviewer_id') as $key => $value) {
					if ($value != '' ){
						$interviewer_info["candidate_id"] = $this->key_field_val;
						$interviewer_info["user_id"] = $value;
						$interviewer_info["datetime"] = date('Y-m-d h-i-s',strtotime($interview_date_arr[$key]));
						$this->db->insert('recruitment_manpower_candidate_interviewer', $interviewer_info);
					}
				}
			}
		}
		

		$response = parent::get_message();		
		$response->page_refresh = "true";

		parent::set_message($response);
		parent::after_ajax_save();		
	}

	function send_for_interview_response_email(){

		$this->db->select('recruitment_manpower.position_id, user_position.position, recruitment_manpower.management_trainee');
		$this->db->where('recruitment_manpower.request_id',$this->input->post('mrf_id'));
		$this->db->join('user_position','user_position.position_id = recruitment_manpower.position_id');
		$mrf_info = $this->db->get('recruitment_manpower')->row();

		$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id'=>$this->input->post('applicant_id')))->row();

		$email_data = array(
			'name' => $applicant_info->firstname." ".$applicant_info->lastname,
			'position' => $mrf_info->position,
			'datetime' => date($this->config->item('display_datetime_format_email'),strtotime($this->input->post('interview_date')))
		);

		$this->load->model('template');
        $template = $this->template->get_module_template(38, 'scheduled_interview');
        $message = $this->template->prep_message($template['body'], $email_data);
        $recepients[] = $applicant_info->email;
        $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$applicant_info->firstname." ".$applicant_info->lastname, $message);

	}

	function after_ajax_save() {

		// Various operations depending on status, only do if save is successful.
		if ($this->get_msg_type() == 'success') {
			if ($this->input->post('record_id') == '-1') {
				if ($this->input->post('interview_date') != '') {
					$status = $this->_interview_status_id;
					$this->send_for_interview_response_email();
				} else {
					$status = $this->_default_status_id;
				}

				$this->db->where($this->key_field, $this->key_field_val);
				$this->db->update($this->module_table, array('candidate_status_id' => $status));

				// Set applicant status to "Candidate".
				$applicant_id = $this->input->post('applicant_id');
				$this->system->update_application_status($applicant_id, $status);
				$this->db->where('applicant_id', $applicant_id);
				$this->db->update('recruitment_applicant', array('application_status_id' => 2));

				$mrf_info = $this->db->get_where('recruitment_manpower',array('request_id' => $this->input->post('mrf_id')))->row();

				$this->db->where('lstatus',0);
				$this->db->where('status',1);
				$this->db->or_where('status',5);
				$this->db->update('recruitment_applicant_application', array('mrf_id' => $this->input->post('mrf_id'), 'status' => 2, 'position_applied' => $mrf_info->position_id ));
				
			}

			if ($this->input->post('final_interview_date') != '') {
				$this->db->where('candidate_id', $this->key_field_val);				

				$this->db->update('recruitment_candidates_appraisal', array('final_datetime' => $this->input->post('final_interview_date')));
			}

			// Blacklisted/Active file
			if ($this->input->post('blacklisted') != 1 && $this->input->post('active_file') != 1) {

				switch ($this->input->post('candidate_status_id')) {
					case $this->_hired_status_id: 
						$this->hire_candidate(true);						
					case $this->_interview_status_id:
						$this->load->library('form_validation');
						$this->form_validation->set_rules('interview_date', 'Initial interview date', 'trim|required');

						if (!$this->form_validation->run()) {
							$this->set_msg_text(form_error('interview_date'));
							$this->set_msg_type('error');
						} else {
							$this->set_msg_text('Candidate status set to For Interview.');
						}

						break;
					case $this->_evaluation_status_id:
						$this->load->library('form_validation');
						$this->form_validation->set_rules('final_interviewer_id', 'Final Interviewer', 'trim|required');
						$this->form_validation->set_rules('final_interview_date', 'Final interview date', 'trim|required');

						if (!$this->form_validation->run()) {
							$this->set_msg_text(validation_errors());
							$this->set_msg_type('error');
						} else {
							$this->set_msg_text('Candidate status set to For Final Interview.');
							//update appraisal for interview date
							$candidate = $this->db->get_where('recruitment_manpower_candidate', array( $this->key_field => $this->key_field_val) )->row();
							$this->db->update('recruitment_candidates_appraisal', array('final_datetime' => $candidate->final_interview_date), array('candidate_id' => $this->key_field_val));
						}					
						break;
					case $this->_rejected_status_id:
						$this->reject_candidate();
						return;		
					case $this->_default_status_id:
						if ($this->input->post('interview_date') != '') {
							$this->db->where($this->key_field, $this->key_field_val);
							$this->db->update($this->module_table, array('candidate_status_id' => $this->_interview_status_id));
							$this->send_for_interview_response_email();
						}								
				}				
			}
	
			//change status of applicant 
			$this->system->update_application_status($this->input->post('applicant_id'), $this->input->post('candidate_status_id'));
			
			$this->db->delete('recruitment_candidate_job_offer_benefit',array('job_offer_id' => $this->key_field_val));
			$benefits = $this->input->post('benefit');
			$hours = $this->input->post('hours');
			if (is_array($benefits) && count($benefits) > 0) {
				foreach( $benefits as $benefit_id => $value ){
					$data = array(
						'job_offer_id' => $this->key_field_val,
						'benefit_id' => $benefit_id,
						'hours_required' => ($hours[$benefit_id] != 'Hours Required') ? $hours[$benefit_id] : 0,
						'value' => str_replace(',', '', $value)				
					);
					$this->db->insert('recruitment_candidate_job_offer_benefit', $data);
				}
			}
			$module_filters = prepare_filters(get_candidate_filters($this->_statuses));

			ajax_push('update_candidate', array($module_filters));

			// Various operations depending on status, only do if save is successful.
			if ($this->input->post('accepted')) {
				$x = $this->input->post('accepted');
				if ( $x == 1) {
					$status = 'accept';
				} else if ( $x == 2) {
					$status = 'reject';
				} else {
					$status = '';
				}

				$this->change_jo_status($status);
				return;
			}

			$this->db->where($this->key_field, $this->key_field_val);
			$candidate = $this->db->get($this->module_table)->row();

			$this->db->where('request_id', $candidate->mrf_id);
			$this->db->update('recruitment_manpower', array('status' => 'In-Process'));

			if ($this->input->post('record_id') != '-1') {
				if ($this->input->post('blacklisted') == 1 && $this->input->post('active_file') == 1) {
					$response->msg = 'Status conflict.';
					$response->msg_type = 'error';

					parent::set_message($response);
				} else if ($this->input->post('blacklisted') == 1) {// Blacklisted

					if ($this->input->post('recruitment_candidate_blacklist_status') < 1) {
						$response->msg      = 'Candidate not set to blacklisted. Select reason.';
						$response->msg_type = 'error';

						parent::set_message($response);
					} else {
						// Update candidate and applicant record.
						$this->db->where($this->key_field, $this->key_field_val);
						$this->db->update($this->module_table, array('candidate_status_id' => 8));

						$this->db->where('applicant_id', $candidate->applicant_id);
						$this->db->update('recruitment_applicant', 
							array(
								'application_status_id' => 6,
								'recruitment_candidate_blacklist_status' => $this->input->post('recruitment_candidate_blacklist_status')
								)
							);

						$this->db->update('recruitment_applicant_application', array('status' => 6 ), array('applicant_id' => $this->input->post('applicant_id'), 'mrf_id' => $this->input->post('mrf_id'), 'lstatus' => 0 ));
					}

				} else if ($this->input->post('active_file') == 1) {

					if ($this->input->post('af_position_id') < 1) {
						$response->msg      = 'Candidate not set to active file. Select position.';
						$response->msg_type = 'error';

						parent::set_message($response);
					} else {

						// Update candidate and applicant record.
						$this->db->where($this->key_field, $this->key_field_val);
						$this->db->update($this->module_table, array('candidate_status_id' => 11, 'deleted' => 1));

						$this->db->where('applicant_id', $candidate->applicant_id);
						$this->db->update('recruitment_applicant', 
							array(
								'application_status_id' => 5,
								'af_pos_id' => $this->input->post('af_position_id')
								)
							);


						$this->db->update('recruitment_applicant_application', array('lstatus' => 1 ), array('applicant_id' => $this->input->post('applicant_id')));
						
						$data = array(
							'applicant_id' => $candidate->applicant_id,
							'position_applied' => $this->input->post('af_position_id'),
							'applied_date' => date('Y-m-d H:i:s'),
							'status' => 5,
							'mrf_id' => 0
						);

						//save application
						$this->db->insert('recruitment_applicant_application',$data);
					}					
				}
			}
		}
	}

	function delete() {

		$candidate_info = $this->db->get_where($this->module_table, array('candidate_id'=>$this->input->post('record_id')))->row();

		parent::delete();

		$candidate_id = $this->input->post('record_id');
		// Set all records to 0.
		$record_id = explode(',', $candidate_id);

		$this->db->where_in('candidate_id', $record_id);
		$this->db->update('recruitment_manpower_candidates_schedule', array('current' => 0, 'deleted' => 1));

		$this->db->where_in('candidate_id', $record_id);
		$this->db->update('recruitment_preemployment', array('deleted' => 1));

		$this->db->select('applicant_id');
		$this->db->where_in($this->key_field, $record_id);

		$result = $this->db->get($this->module_table);

		foreach ($result->result_array() as $record) {
			$records[] = $record['applicant_id'];
		}

		$this->db->where_in('applicant_id', $records);
		$this->db->update('recruitment_applicant', array('application_status_id' => 3));
		$this->db->update('recruitment_applicant_application', array('status' => 3 ), array('applicant_id' => $this->input->post('applicant_id'), 'mrf_id' => $this->input->post('mrf_id'), 'lstatus' => 0 ));

		//get candidate
		$candidate = $this->db->get_where($this->module_table, array($this->key_field => $candidate_id))->row();
		if( in_array($candidate->candidate_status_id, array(1,2))){
			$this->db->update('recruitment_applicant', array('application_status_id' => 1), array('applicant_id' => $candidate->applicant_id));
			$this->db->update('recruitment_applicant_application', array('status' => 3 ), array('applicant_id' => $this->input->post('applicant_id'), 'mrf_id' => $this->input->post('mrf_id'), 'lstatus' => 0 ));
		}

	}


	// END - default module functions
	// START custom module funtions    

	function _set_left_join() {
		parent::_set_left_join();
				
		$this->db->join('recruitment_candidate_job_offer jo', 'jo.candidate_id = ' . $this->db->dbprefix.$this->module_table . '.candidate_id', 'left');
		$this->db->join('user', 'user.employee_id = recruitment_manpower_candidate.employee_id', 'left');
		$this->db->join('recruitment_candidate_blacklist_status bs', 'bs.recruitment_candidate_blacklist_status_id = ' . $this->db->dbprefix.$this->module_table . '.recruitment_candidate_blacklist_status', 'left');
		$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = ' . $this->db->dbprefix.$this->module_table . '.mrf_id', 'left');
		$this->db->join('user_position', 'recruitment_manpower.position_id = user_position.position_id', 'left');	
		$this->db->join('recruitment_applicant','recruitment_applicant.applicant_id = '.$this->db->dbprefix.'recruitment_manpower_candidate.applicant_id','left');	
	}	

	private function _get_hired_candidates() {		
		$module = $this->hdicore->get_module('preemployment');

        // Get children modules and prepare the checklist data.
	    $module_children = $this->hdicore->get_module_child($module->module_id);

	    $ctr    = 0;
	    $e      = array();
	    $tables = array();

        foreach ($module_children as $checklist) {
			$checklist = get_checklist_data($checklist, true);

			if ($this->hdicore->module_active($checklist['module_id'])) {
				if ($checklist['code'] == 'preemployment_201' &&
					!$this->hdicore->module_active('hris_201')
				) {
					continue;
				}

				$tables[] = $checklist['table'];
			}

			$ctr++;
        }

        if (count($tables) > 0) {
        	$sql = 'SELECT candidate_id ';
        	$sql .= 'FROM ' . $this->db->dbprefix . $module->table;
        	$sql .= ' WHERE ';

        	$where = '';
        	$i = 0;
        	while ($i < count($tables)) {
        		$where .= $module->key_field . ' IN (SELECT ' . $module->key_field . ' FROM ';
        		$where .= $this->db->dbprefix . $tables[$i];
        		$where .= ' WHERE completed = 1 AND deleted = 0) ';
				
				if ($i != count($tables) - 1) {
					$where .= 'AND ';
				}

				$i++;
			}

			$where .= ' AND has_201 = 1 AND ' . $this->db->dbprefix . $module->table . '.deleted = 0';

			$sql .= $where;

			$o_completed = $this->db->query($sql);

			if ($o_completed && $o_completed->num_rows() > 0) {
				$result = $o_completed->result();
				foreach ($result as $exclude) {
					$e[] = $exclude->candidate_id;
				}				
			}			
        }

        return array();//$e;
	}

	function listview() {
		$response->msg = "";

		$hired_candidates = $this->_get_hired_candidates();
		
		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true;

		//set columnlist and select qry
		$this->_set_listview_query('', $view_actions);

		$this->listview_columns[3]['name'] = 'position_id';
		// Potential problems on this block is if the arrangement of fields changes, not sure, but possible.
		$this->listview_qry .= ','.$this->db->dbprefix.'user_position.position, recruitment_manpower.position_id, IF(jo.job_offer_id AND jo.deleted = 0, jo.job_offer_id, -1) job_offer_id, is_internal';
		$this->listview_qry .= ',IF('.$this->db->dbprefix.'recruitment_manpower_candidate.is_internal = 0, CONCAT( '.$this->db->dbprefix.'recruitment_applicant.firstname, " ", '.$this->db->dbprefix.'recruitment_applicant.lastname ), CONCAT( ' . $this->db->dbprefix . 'user.firstname, " ", ' . $this->db->dbprefix . 'user.lastname )) t0firstnamelastname';

		//set Search Qry string
		if ($this->input->post('_search') == "true" && $this->input->post('searchString') != '')
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
		$this->db->where('IF(is_internal = 0, 1, '.$this->db->dbprefix.'user.deleted = 0)');
		$this->db->where('recruitment_manpower.status <>', 'Closed');
		$this->db->where('( NOT '.$this->db->dbprefix($this->module_table).'.applicant_id = 0 OR NOT '.$this->db->dbprefix($this->module_table).'.employee_id = 0)');

		if (count($hired_candidates) > 0) {
			$this->db->where_not_in($this->module_table . '.candidate_id', $hired_candidates);
		}

		if ( !$this->is_recruitment() && !$this->is_superadmin ) {
			$this->db->where('(requested_by =' . $this->userinfo['user_id'] . ' OR approved_by = ' . $this->userinfo['user_id'] . ' OR final_interviewer_id = ' . $this->userinfo['user_id'] . ')');
		}

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$result = $this->db->get();
		//$response->last_query = $this->db->last_query();

		if ($this->db->_error_message() != "") {
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		} else {
			$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows() / $limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $result->num_rows();

			/* record query */
			//build query
			$this->_set_left_join();

			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table . '.deleted = 0 AND ' . $search);
			$this->db->where('IF(is_internal = 0, 1, '.$this->db->dbprefix.'user.deleted = 0)');
			$this->db->where('recruitment_manpower.status <>', 'Closed');
			$this->db->where('( NOT '.$this->db->dbprefix($this->module_table).'.applicant_id = 0 OR NOT '.$this->db->dbprefix($this->module_table).'.employee_id = 0)');

			if (count($hired_candidates) > 0) {
				$this->db->where_not_in($this->module_table . '.candidate_id', $hired_candidates);
			}

			if ( !$this->is_recruitment() && !$this->is_superadmin ) {
				$this->db->where('(requested_by =' . $this->userinfo['user_id'] . ' OR approved_by = ' . $this->userinfo['user_id'] . ' OR final_interviewer_id = ' . $this->userinfo['user_id'] . ')');
			}

			if ($sidx != "") {
				$this->db->order_by($sidx, $sord);
			} else {
				if (is_array($this->default_sort_col)) {
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}

				$this->db->order_by('t0firstnamelastname','asc');
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);

			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}

			$result = $this->db->get();
			//$response->qry = $this->db->last_query();

			//check what column to add if this is a related module
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
					if (isset($column_to_add) && sizeof($column_to_add) > 0)
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
							} elseif ($detail['name'] == 'applicant_id' && $row['is_internal'] == 1) {
								$cell[$cell_ctr++] = $row['t0firstnamelastname'];
							} else {
								if (in_array($this->listview_fields[$cell_ctr]['uitype_id'], array(2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 39))) {
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

								if ($detail['name'] == 'interview_date') {
									if ($row['candidate_status_id'] == $this->_default_status_id) {
										$cell[$cell_ctr] = "N/A";
									} else {
										$datestr = strtotime($cell[$cell_ctr]);
										
										$cell[$cell_ctr] = !empty($datestr) ? date('M j, Y g:i a', $datestr) : '';
								
										// Change color to red if date has passed or is today.
										if (strtotime(date('Y-m-d', $datestr)) <= strtotime(date('Y-m-d'))) {
											$cell[$cell_ctr] = '<span class="red">' . $cell[$cell_ctr] . '</span>';
										}

										if ($row['reschedule_date'] != null) {
											if ($this->is_recruitment()) {
												$reschedule_date = '<br />
													<span style="display:block;"><small style="vertical-align:top" class="resched_small">'
													. date($this->config->item('display_datetime_format'), strtotime($row['reschedule_date']))
													. '</small>'
													. '<a class="reschedule_date icon-button icon-16-question-button"></a></span>';
											} else {
												$reschedule_date = '<br />' . date($this->config->item('display_datetime_format'), strtotime($row['reschedule_date']));
											}
											$cell[$cell_ctr] .= $reschedule_date;
										}										
									}									
								}
								
								//var_dump($row['interview_date'], strtotime($row['interview_date']) < strtotime(date('Y-m-d H:i:s')));
								if($detail['name'] == "candidate_status" || $detail['name'] == 't1candidate_status') {
									switch($row['candidate_status_id']){
										case $this->_evaluation_status_id:
										case $this->_interview_status_id:
											if (strtotime(date('Y-m-d', strtotime($row['interview_date']))) < strtotime(date('Y-m-d'))) {
												$cell[$cell_ctr] .= '&nbsp;<a href="javascript:set_candidate_status(9, \''.$row[$this->key_field].'\')">(No Show)</a>';
												//set candidate status to 
												$cell[$cell_ctr] = 'No Show for Interview<script type="text/javascript">window.location = "' . site_url($this->module_link) . '"</script>';
												$this->system->set_candidate_status( $row[$this->key_field], 9 );								
											}
											
											break;	
										case 8:
											$cell[$cell_ctr] .= '<br /><small class="red">' . $row['recruitment_candidate_blacklist_status'] . '</small>';
/*										case $this->_joboffer_status_id:
											$cell[$cell_ctr] .= '&nbsp;<a href="javascript:set_candidate_status(10, \''.$row[$this->key_field].'\')">(Job Offer)</a>';
											break;*/
										case $this->config->item('active_file_status_id'):
											$this->db->where('applicant_id', $row['applicant_id']);
											$applicant = $this->db->get('recruitment_applicant')->row();

											if ($applicant->af_pos_id) {
												$this->db->where('position_id', $applicant->af_pos_id);
												$position = $this->db->get('user_position')->row();

												$cell[$cell_ctr] .= ' <br />(' . $position->position . ')';
											}
											break;										
										default:
											break;
									}
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
	
	function edit()
	{
		if($this->user_access[$this->module_id]['edit'] == 1){
			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			// $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/modules/recruitment/candidate_joboffer.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['scripts'][] = uploadify_script();
			$data['content'] = 'editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$interviewer_arr = array();

			$data['default_interviewer'] = false;

			$this->db->where('user.deleted',0);
			$this->db->where('user.inactive',0);
			$this->db->join('employee','employee.employee_id = user.employee_id');
			$result = $this->db->get('user');
			if ($result && $result->num_rows() > 0){
				foreach ($result->result_array() as $key => $value) {
					$interviewer_arr[] = $value;
				}
			}

			//check if default interviewer already set.
			if ($this->user_access[$this->module_id]['post'] != 1) {
				$this->db->where('user_id',$this->userinfo['user_id']);
			}
			$this->db->where('candidate_id',$this->input->post('record_id'));
			$result = $this->db->get('recruitment_manpower_candidate_interviewer');

			if ($result && $result->num_rows() > 0){
				$data['default_interviewer'] = true;
			}

			$data['with_sched'] = 0;
			$data['interviewer'] = $interviewer_arr;
			$data['candidate_interviewer'] = $this->get_candidate_interviewer($this->key_field_val);
			$data['interview_result'] = $this->db->get_where('recruitment_candidate_result',array("deleted"=>0));			
			$data['interview_type'] = $this->db->get_where('recruitment_interview_type',array("deleted"=>0))->result();		

			$data['exams'] = $this->get_exam_type();
			$data['exam_details'] = false;
			$exam_details = $this->db->get_where('recruitment_candidates_appraisal',array('candidate_id'=>$this->key_field_val));
			if ($exam_details && $exam_details->num_rows() > 0) {
				$data['exam_details'] = $exam_details->row()->exam_details;
				$data['interview_details'] = json_decode($exam_details->row()->interview_details, true);
				$data['appraisal'] = $exam_details->row();
			}

			$data['recommendation'] = $this->get_recommendation();

			//load variables to env
			$this->load->vars( $data );

			$vars = $this->load->get_vars();	

			// Get default position_id for job offer.
			$this->db->select('recruitment_manpower.position_id, recruitment_manpower.management_trainee');

			$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = recruitment_manpower_candidate.mrf_id', 'left');						

			$this->db->where($this->key_field, $this->input->post('record_id'));
			$this->db->where($this->module_table . '.deleted', 0);

			$result = $this->db->get($this->module_table);

			foreach ($vars['fieldgroups'] as $index => &$fieldgroup) {
				foreach ($fieldgroup['fields'] as &$field) {										
					if ($field['column'] == 'mrf_pos_id') {						
						if ($result && $result->num_rows() > 0) {
							$x = $index;
							$field['value'] = $result->row()->position_id;							
						}
					}					
				}
			}

			$vars['management_trainee'] = $result->row()->management_trainee;

			$this->load->reload_vars($vars);			

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

	function get_final_interviewer() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		} else {
			$this->db->select('recruitment_manpower.position_id,user_position.reporting_to, final_interviewer_id');
			$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = ' . $this->module_table . '.mrf_id', 'left');
			$this->db->join('user_position', 'user_position.position_id = recruitment_manpower.position_id', 'left');
			$this->db->where($this->key_field, $this->input->post('record_id'));

			$result = $this->db->get($this->module_table);

			if ($result && $result->num_rows() > 0) {
				$this->load->helper('form');
				// Get module id of candidates appraisal module via the code.
				$appraisal_module = $this->hdicore->get_module('appraisal');

				$position_hierarchy = $this->hdicore->get_approvers($result->row()->position_id, $appraisal_module->module_id);
	
				if (count($position_hierarchy['admins']) > 0) {
	     			foreach ($position_hierarchy['admins'] as $user_position) {
	                    $data['position_hierarchy'][$user_position['position']][$user_position['user_id']] = $user_position['firstname'] . ' ' . $user_position['lastname'];
	                }
					$response = array('json' => array('msg_type' => 'success', 'html' => form_dropdown('final_interviewer_id', $data['position_hierarchy'], $result->row()->final_interviewer_id)));
				} else {
					$response = array('json' => array('msg' => $position_hierarchy['msg'], 'msg_type' => 'error'));
				}

			} else if (ENVIRONMENT == 'development') {
				$response = array('html' => $this->db->last_query());
			}

			$this->load->view('template/ajax', $response);
		}
	}	

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
		$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
		

		if( $row['is_internal'] == 0 ){
			$actions .= '<a class="icon-button icon-16-users" candidate_id="'.$row['candidate_id'].'" module_link="' . $module_link . '" tooltip="View Applicant Details" href="javascript:void(0)"></a>';
		}

        if ($this->user_access[$this->module_id]['edit']) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a>';
        }

		// if ($this->user_access[$this->module_id]['print'] == 1) {
		// 	$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print Applicant Form" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';			
		// }

		// if ($this->_can_appraise($row)) {
		// 	$actions .= '<a class="icon-button icon-16-document-view show-appraisal" tooltip="Appraisal" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
		// }

/*		if ((
			$row['candidate_status_id'] == $this->_joboffer_status_id ||
			$row['candidate_status_id'] == 13 ||
)
			&& $this->is_recruitment()) { // 4/12/2012
			$actions .= '<a class="icon-button icon-16-doc-text button-joboffer" tooltip="Joboffer" href="javascript:void(0)" module_link="recruitment/candidate_joboffer" joboffer_id="' . $row['job_offer_id'] . '" position_id="' . $row['position_id'] . '" applicant_id="' . $row['applicant_id'] . '"></a>';
		}*/

		if (($row['candidate_status_id'] == $this->config->item('contract_signing_status_id')
			|| $row['candidate_status_id'] == $this->_joboffer_status_id
			|| ($row['candidate_status_id'] == 13 || $row['candidate_status_id'] == 24)) && $this->_can_print_jo()) {
			$actions .= '<a class="icon-button icon-16-document-stack" tooltip="Print Contract" candidate_status="'.$row['t1candidate_status'].'"  onclick="return false;" href="javascript:void(0)" module_link="' . $module_link . '" joboffer_id="' . $row['job_offer_id'] . '"></a>';
		}

		// if ( ($row['candidate_status_id'] == $this->_joboffer_status_id || $row['candidate_status_id'] == $this->config->item('contract_signing_status_id'))
		// && ($this->is_recruitment() || $this->is_superadmin)) {						

		// 	if ($row['candidate_status_id'] == $this->config->item('contract_signing_status_id')) {
		// 		$tooltip_jo_accept = 'Accepted';
		// 		$tooltip_jo_reject = 'Declined';
		// 	} elseif ($row['candidate_status_id'] == $this->_joboffer_status_id) {
		// 		$tooltip_jo_accept = 'Accept Job Offer';
		// 		$tooltip_jo_reject = 'Reject Job Offer';
		// 	}			
			
		// 	$actions .= '<a class="icon-button icon-16-approve" tooltip="' . $tooltip_jo_accept . '" href="javascript:void(0)" module_link="' . $module_link . '" joboffer_id="' . $row['job_offer_id'] . '"></a>';
		// 	$actions .= '<a class="icon-button icon-16-disapprove" tooltip="' . $tooltip_jo_reject .  '" href="javascript:void(0)" module_link="' . $module_link . '" joboffer_id="' . $row['job_offer_id'] . '"></a>';					
		// }

		// if (!in_array($row['candidate_status_id'], array($this->_hired_status_id, $this->_rejected_status_id))) {

		//  	if ($row['candidate_status_id'] != $this->_joboffer_status_id && ($this->is_recruitment() || $this->is_admin || $this->is_superadmin)) {
		// 		//$actions .= '<a class="icon-button icon-16-calendar-add" tooltip="Set Interview Schedule" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';				
		//  	}
 			
 	// 		if (1==2) { //($this->is_recruitment() || $row['approved_by'] == $this->userinfo['user_id'] ) {
		// 		$actions .= '<a class="icon-button icon-16-user-add" tooltip="Hire" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
		// 		$actions .= '<a class="icon-button icon-16-user-remove" tooltip="Reject" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
		//  	}		 	
		// }

		// if ($this->_can_reschedule($row)) {
		// 	$actions .= '<a class="icon-button icon-16-calendar-month" tooltip="Reschedule" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
		// }

		if ($this->user_access[$this->module_id]['delete'] == 1) {
			if($row['candidate_status_id']==1 || $row['candidate_status_id']==2){
				$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a></span>';
			}
		}

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
                            
        // if ($this->user_access[$this->module_id]['add']) {
        //     $buttons .= "<div class='icon-label'>";
        //     $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
        //     $buttons .= "<span>".$addtext."</span></a></div>";
        // }
         
        // if ($this->user_access[$this->module_id]['delete']) {
        //     $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        // }

        // if ( get_export_options( $this->module_id ) ) {
        //     $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
        //     $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        // }        
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function applicant_detail_redirect() {
		if (IS_AJAX) {
			$this->db->where($this->key_field, $this->input->post('record_id'));
			$this->db->limit(1);

			$response->id = $this->db->get($this->module_table)->row()->applicant_id;

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_record($candidate_id = 0) {
		// Get from $_POST when the URI is not present.
		if ($candidate_id == 0) {
			$candidate_id = $this->input->post('record_id');
		}

		if ($candidate_id > 0 && $this->_record_exist($candidate_id)) {
			// Get the applicant ID form the candidate ID and redirect to applicants print function.
			$this->db->where($this->key_field, $candidate_id);
			$this->db->where('deleted', 0);

			$result = $this->db->get($this->module_table);
			$applicant = $result->row_array();

			redirect(site_url('recruitment/applicants/print_record/' . $applicant['applicant_id']));
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function filter($type = null) {
		if ($type == null) {
			redirect('recruitment/candidates');
		}

		$statuses = get_candidate_statuses();

		foreach ($statuses as $status) {
			$status_array[$status['candidate_status_id']] = $status['candidate_status'];
		}

		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['scripts'][] = '<script src="'.base_url().'lib/modules/recruitment/candidates_listview.js" type="text/javascript"></script>';
		$data['content'] = 'recruitment/manpower/candidates/listview';
		$data['listview'] = 'recruitment/candidates/listview';
		$data['jqgrid'] = 'recruitment/manpower/candidates/template/jqgrid';

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "jqgrid_loadComplete();";

		$data['default_query'] = true;
		$data['default_query_field'] = 'candidate_status';
		$data['default_query_val'] = $status_array[$type];

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
	
	function set_candidate_status(){
		if(IS_AJAX){
			$response->msg = "";
			
			if( $this->user_access[$this->module_id]['edit'] == 1 ){
				$this->db->update($this->module_table, array('candidate_status_id' => $this->input->post('candidate_status_id')), array('candidate_id' => $this->input->post('candidate_id')));
				
				if(in_array($this->input->post('candidate_status_id'), array(9, 10, 14))){
					//black list applicant
					$candidate = $this->db->get_where($this->module_table, array('candidate_id' => $this->input->post('candidate_id')))->row();
					$this->db->update('recruitment_applicant', array('application_status_id' => 6 ), array('applicant_id' => $candidate->applicant_id ));
					$this->db->update('recruitment_applicant_application', array('status' => 6 ), array('applicant_id' => $candidate->applicant_id, 'mrf_id' => $candidate->mrf_id, 'lstatus' => 0 ));
				}
				
				if($this->db->_error_message() != ""){
					$response->msg = $this->db->_error_message();
					$response->msg_type = 'error';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}		
			
			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	private function _can_appraise($row) {
		if (!isset($row['interview_date']) 
			|| is_null($row['interview_date']) 
			|| $row['candidate_status_id'] == $this->_hired_status_id
			|| $row['candidate_status_id'] == $this->_rejected_status_id
			|| $row['candidate_status_id'] == 9
			|| $row['interview_date'] < date('Y-m-d h:i:s', strtotime('-1 day', strtotime(date('Y-m-d h:i:s'))))
			) {
			return false;
		}

		if ($row['candidate_status_id'] == $this->_interview_status_id && $this->is_recruitment()) {
			return true;
		}		

		if ($row['candidate_status_id'] == $this->_evaluation_status_id && $this->userinfo['user_id'] == $row['final_interviewer_id']) {
			return true;
		}

		return false;
	}

	private $_can_print_jo = null;
	
	private function _can_print_jo() {
		if (is_null($this->_can_print_jo)) {
			$module = $this->hdicore->get_module('job_offer');

			if ($module) {
				$this->_can_print_jo = $this->user_access[$module->module_id]['print'];
			} else {
				$this->_can_print_jo = false;
			}
		}

		return $this->_can_print_jo;
	}

	private function _can_reschedule($row) {
		if ($row['candidate_status_id'] == $this->_evaluation_status_id && $this->userinfo['user_id'] == $row['final_interviewer_id']) {
			return true;
		}

		return false;
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		parent::_set_listview_query($listview_id, $view_actions);

		$this->listview_qry .= ', requested_by, approved_by, final_interviewer_id, reschedule_date';
		$this->listview_qry .= ',' . $this->db->dbprefix . $this->module_table . '.candidate_status_id';
		$this->listview_qry .= ', IF(' . $this->db->dbprefix . $this->module_table . '.candidate_status_id = ' . $this->_evaluation_status_id . ', final_interview_date, interview_date) interview_date';
		$this->listview_qry .= ', bs.recruitment_candidate_blacklist_status';
	}

	function hire_candidate($internal = false) {
		if (IS_AJAX) {
			$candidate_id = $this->input->post('record_id');

			if ($candidate_id && $candidate_id > 0) {
				$this->db->where($this->key_field, $candidate_id);
				$this->db->where('deleted', 0);
				$this->db->limit(1);

				$result = $this->db->get($this->module_table);

				if ($result && $result->num_rows() > 0) {
					$this->db->where($this->key_field, $candidate_id);
					$this->db->update($this->module_table, array('candidate_status_id' => $this->_hired_status_id));


					$response = $this->_get_hired_message($result);

					// Set manpower request status to closed.					
					if ($response) {
						$candidate = $result->row();
						
						$this->db->select(array('mrf_id', 'COUNT(candidate_id) AS hired', 'number_required'));
						$this->db->from('recruitment_manpower mrf');
						$this->db->join('recruitment_manpower_candidate c', 'c.mrf_id = mrf.request_id', 'left');
						$this->db->where('mrf.deleted', 0);
						$this->db->where('c.deleted', 0);
						$this->db->where('c.candidate_status_id', $this->_hired_status_id);
						$this->db->where('mrf.request_id', $candidate->mrf_id);
						$this->db->group_by('c.mrf_id');
						
						$manpower_hired_total = $this->db->get();
						
						if ($manpower_hired_total->num_rows() > 0) {
							$total = $manpower_hired_total->row();
							if ($total->hired >= $total->number_required) {
								$this->db->where('request_id', $total->mrf_id);
								$this->db->update('recruitment_manpower', array('status' => 'Closed'));
							}
						}
					}
					
				} else {
					$response->msg_type = 'notice';
					$response->msg = 'Candidate not found.';
				}
			} else {
				$response->msg_type = 'notice';
				$response->msg = 'No candidate ID specified.';
			}
			if (!$internal) {				
				$data['json'] = $response;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		} else {
			$this->session->set_flashdata('flashdata', $this->lang->line('direct_access_denied'));
			redirect(base_url() . $this->module_link);
		}
	}

	private function _get_hired_message($applicant) {
		$response->msg_type = 'success';
		$response->msg 		= 'Applicant status set to Hired. ';
				
		if ($this->hdicore->module_active('hris_201')) {
			if ($this->_save_new_employee($applicant)) {
				$response->msg .= ' 201 file has been created successfully.';
			} else {
				$response->msg_type = 'notice';
				$response->msg .= ' <br />NOTICE: failed to create 201 file.';
			}
		}
		
		return $response;
	}

	private function _save_new_employee($applicant) {		
		$this->db->where('applicant_id', $applicant->row()->applicant_id);
		$result = $this->db->get('employee');		

		if ($result->num_rows() > 0) {
			return TRUE;
		}

		$this->db->where('applicant_id', $applicant->row()->applicant_id);
		$this->db->update('recruitment_applicant', array('application_status_id' => 4));
		$this->db->update('recruitment_applicant_application', array( 'status' => 4 ), array( 'applicant_id' => $applicant->row()->applicant_id, 'mrf_id' => $applicant->row()->mrf_id, 'lstatus' => 0 ));

		// Copy data from applicant to users table.
		// Get intersecting fields to use on SELECT statement.
		$user_fields = $this->db->list_fields('user');
		$applicant_fields = $this->db->list_fields('recruitment_applicant');
		$select = array_intersect($user_fields, $applicant_fields);

		$this->db->select($select);
		$this->db->where('applicant_id', $applicant->row()->applicant_id);
		$applicant_details = $this->db->get('recruitment_applicant')->row_array();

		// Get company details of MRF.
		$this->db->where('request_id', $applicant->row()->mrf_id);
		$this->db->limit(1);
		$mrf = $this->db->get('recruitment_manpower');

		$applicant_details['company_id'] 	= $mrf->row()->company_id;
		$applicant_details['department_id'] = $mrf->row()->department_id;
		$applicant_details['position_id']   = $mrf->row()->position_id;

		//get defailt role base on position
		$position = $this->db->get_where('user_position', array('position_id' => $mrf->row()->position_id))->row();
		
		$applicant_details['role_id'] 		= $position->default_role;
		$applicant_details['login']   	    = $position->position_code . $applicant->row()->applicant_id;
		$applicant_details['employed_date'] = date('Y-m-d');

		if( $this->db->insert('user', $applicant_details) ){
			$user_id = $this->db->insert_id();
			$this->db->update('user', array('employee_id' => $user_id), array('user_id' => $user_id));
			
			// Copy data from applicant to employee table.
			// Get intersecting fields to use on SELECT statement.
			$employee_fields = $this->db->list_fields('employee');
			$select = array_intersect($employee_fields, $applicant_fields);
	
			$this->db->select($select);
			$this->db->where('applicant_id', $applicant->row()->applicant_id);
			$applicant_details = $this->db->get('recruitment_applicant')->row_array();
			$applicant_details['user_id'] = $applicant_details['employee_id'] = $user_id;
	
			if ($this->db->insert('employee', $applicant_details)) {
				$data = array();
				$data['candidate_id'] = $applicant->row()->candidate_id;
				return $this->db->insert('recruitment_preemployment', $data);
			}
		}
		
		return FALSE;
	}
	
	function reject_candidate() {
		if (IS_AJAX) {
			$candidate_id = $this->input->post('record_id');

			if ($candidate_id && $candidate_id > 0) {
				$this->db->where($this->key_field, $candidate_id);
				$this->db->where('deleted', 0);
				$this->db->limit(1);

				$result = $this->db->get($this->module_table);

				if ($result && $result->num_rows() > 0) {
					$candidate = $result->row();
					$this->db->where($this->key_field, $candidate_id);
					$this->db->update($this->module_table, array('candidate_status_id' => $this->_rejected_status_id));			
					
					$this->system->update_application_status($candidate->applicant_id, $this->_rejected_status_id);		

					$response->msg_type = 'success';
					$response->msg = 'Application rejected.';
				} else {
					$response->msg_type = 'notice';
					$response->msg = 'Candidate not found.';
				}
			} else {
				$response->msg_type = 'notice';
				$response->msg = 'No candidate ID specified.';
			}
			

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', $this->lang->line('direct_access_denied'));
			redirect(base_url() . $this->module_link);
		}
	}

	function reschedule() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', $this->lang->line('direct_access_denied'));
			redirect(base_url() . $this->module_link);		
		} else {
			$candidate_id = $this->input->post('record_id');

			if (!isset($candidate_id) || $candidate_id < 0) {
				$response->msg 		= 'Failed to find candidate.';
				$response->msg_type = 'error';
			} elseif ($this->input->post('accept') == 0) {				
				$this->db->set('reschedule_date', null);
				$this->db->update($this->module_table);
				$this->db->where($this->key_field, $candidate_id);				

				$response->msg 		= 'Reschedule request rejected.';
				$response->msg_type = 'success';				
			} else {
				$this->db->set('final_interview_date', 'reschedule_date', FALSE);
				$this->db->set('reschedule_date', null);
				$this->db->update($this->module_table);
				$this->db->where($this->key_field, $candidate_id);				

				$response->msg 		= 'Interview schedule changed.';
				$response->msg_type = 'success';				
			}

			$data['json'] = $response;			
			$this->load->view('template/ajax', $data);
		}		
	}

	function change_jo_status($status = null) {		
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else {
			if (is_null($status)) {
				$status = $this->input->post('status');
			}

			$this->db->join('recruitment_manpower_candidate', 'recruitment_manpower_candidate.candidate_id = recruitment_candidate_job_offer.candidate_id');
			$this->db->join('recruitment_applicant', 'recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id');
			$this->db->where('job_offer_id', $this->input->post('record_id'));
			$this->db->where('recruitment_candidate_job_offer.deleted', 0);

			$record = $this->db->get('recruitment_candidate_job_offer')->row();

			$response->msg_type = 'success';
			switch ($status) {
				case 'accept':
					if ($record->job_offer_status_id == '1') {
						$status = '2';
						$this->db->update('recruitment_manpower_candidate',
							array('candidate_status_id' => 12),
							array('candidate_id' => $record->candidate_id)
							);
					} elseif ($record->job_offer_status_id == '2') {
						$status = '3';
						$this->db->update('recruitment_manpower_candidate',
							array('candidate_status_id' => 13),
							array('candidate_id' => $record->candidate_id)
							);						
						$this->db->insert('recruitment_preemployment', array('candidate_id' => $record->candidate_id));
					}
					
					$response->msg = 'Job offer accepted.';
					break;
				case 'reject':	
					$status = '4';	
					if ($record->job_offer_status_id == '1') {						
						$this->db->update('recruitment_manpower_candidate',
							array('candidate_status_id' => 15),
							array('candidate_id' => $record->candidate_id)
							);
					} elseif ($record->job_offer_status_id == '2') {						
						$this->db->update('recruitment_manpower_candidate',
							array('candidate_status_id' => 16),
							array('candidate_id' => $record->candidate_id)
							);
					}

					$this->db->update('recruitment_applicant',
						array('application_status_id' => 5),
						array('applicant_id' => $record->applicant_id)
						);


					$this->db->update('recruitment_applicant_application', array('lstatus' => 1 ), array('applicant_id' => $record->applicant_id ));
					
					$data = array(
						'applicant_id' => $record->applicant_id,
						'position_applied' => '',
						'applied_date' => date('Y-m-d H:i:s'),
						'status' => 5,
						'mrf_id' => 0
					);

					//save aaplication
					$this->db->insert('recruitment_applicant_application',$data);
					
					$response->msg = 'Job offer rejected.';
					break;
				default:					
					$response->msg = 'Job offer prepared.';
			}

			$this->db->where('job_offer_id', $this->input->post('record_id'));
			$this->db->update('recruitment_candidate_job_offer', array('job_offer_status_id' => $status));

			$this->load->view('template/ajax', array('json' => $response));
		}
	}	

	function get_applicant_id(){
		$result = $this->db->get_where("recruitment_manpower_candidate",array("candidate_id"=>$this->input->post("candidate_id")));
		$candidate_id = 0;
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$candidate_id = $row->applicant_id;
		}

		$response->candidate_id = $candidate_id;
		$data['json'] = $response;			
		$this->load->view('template/ajax', $data);		
	}

	function qualify_candidate_form(){

		//$this->db->where('recruitment_manpower.position_id',$this->input->post('position_id'));
		//$this->db->or_where_in('recruitment_manpower.position_id',$this->input->post('position2_id'));
		$this->db->where('deleted',0);
		$this->db->where_in('recruitment_manpower.status', array('Approved', 'In-Process'));
		$this->db->join('user','user.user_id = recruitment_manpower.requested_by','left');
		$result = $this->db->get('recruitment_manpower');
		//$response->last_query = $this->db->last_query();

		if( $result->num_rows() > 0 ){

				$select_form = '<select name="mrf_listing" id="mrf_listing">';
				$records = $result->result_array();
				$data['mrf_listing'] = $result->result_array();

				$select_form .= '<option value="0" selected="selected">Please Select</option>';

				foreach( $records as $record ){
					$select_form .= '<option value="'.$record['request_id'].'" '.($record['request_id'] == $this->input->post('mrf_from_posted_jobs') ? 'SELECTED="SELECTED"' : '').'>'.$record['document_number'].' - '.$record['firstname'].' '.$record['lastname'].'</option>';
				}

				$select_form .='</select>';

				$data['select_form'] = $select_form;

				$select_priority = '<select name="mt_priority_list" id="mt_priority_list">';

				$mt_priority = $this->db->get_where('recruitment_mt_priority',array('deleted'=>0))->result_array();

				$select_priority .= '<option value="0">Please Select</option>';

				foreach( $mt_priority as $priority_list ){
					$select_priority .= '<option value="'.$priority_list['mt_priority_id'].'">'.$priority_list['mt_priority'].'</option>';
				}

				$select_priority .='</select>';

				$data['select_priority'] = $select_priority ;

				$response->form = $this->load->view( $this->userinfo['rtheme'].'/recruitment/candidates/qualify_candidate_form',$data, true );
				$this->load->view('template/ajax', array('json' => $response));
		}
		else{

			$response->msg = "No MRF for preferred position";
	        $response->msg_type = "error";
	        $data['json'] = $response;

	        $this->load->view('template/ajax', array('json' => $response));

		}

	}

	function disqualify_candidate_form(){

		$response->form = $this->load->view( $this->userinfo['rtheme'].'/recruitment/candidates/disqualify_candidate_form',$data, true );
		$this->load->view('template/ajax', array('json' => $response));

	}

	function check_management_trainee(){

		$mrf_id = $this->input->post('mrf_id');

		$this->db->select('management_trainee');
		$manpower = $this->db->get_where('recruitment_manpower', array( 'request_id' => $mrf_id ))->row();

		$response->management_trainee = $manpower->management_trainee;

		$this->load->view('template/ajax', array('json' => $response));

	}

	function save_qualified_candidate(){

		if( $this->input->post('mrfid') ){
			$fullname = "";
			$mrf_id = $this->input->post('mrfid');
			$applicant_id = $this->input->post('applicant_id');
			$mt_priority_id = $this->input->post('mt_priority');

			$this->db->where('applicant_id',$applicant_id);
			$result = $this->db->get('recruitment_applicant');

			if ($result && $result->num_rows() > 0){
				$fullname = $result->row()->firstname .' '. $result->row()->lastname;
			}

			$data = array(
				'mrf_id' => $mrf_id,
				'applicant_id' => $applicant_id,
				'applicant_name' => $fullname,
				'is_internal' => 0,
				'employee_id' => 0,
				'contacted_thru' => 'Phone',
				'candidate_status_id' => 1,
				'mt_priority_id' => $mt_priority_id
			);

			$result = $this->db->insert('recruitment_manpower_candidate',$data);

			if( $result ){

				$this->system->update_application_status($this->input->post('applicant_id'), 1);
				
				$this->db->select('recruitment_manpower.position_id, user_position.position, recruitment_manpower.management_trainee');
				$this->db->where('recruitment_manpower.request_id',$mrf_id);
				$this->db->join('user_position','user_position.position_id = recruitment_manpower.position_id');
				$mrf_info = $this->db->get('recruitment_manpower')->row();

				// if( $mrf_info->management_trainee == 1  ){

				// 	//MT Positions
					
				// 	$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id'=>$applicant_id))->row();

				// 	$email_data = array(
				// 		'name' => $applicant_info->firstname." ".$applicant_info->lastname,
				// 		'position' => $mrf_info->position,
				// 		'no_of_days' => $this->config->item('applicant_prescreen_call_day_limit')
				// 	);

				// 	$this->load->model('template');
	   //              $template = $this->template->get_module_template(38, 'applicant_prescreen_mt');
	   //              $message = $this->template->prep_message($template['body'], $email_data);
	   //              $recepients[] = $applicant_info->email;
	   //              $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$this->userinfo['firstname']." ".$this->userinfo['lastname'], $message);
					
				// }
				// else{
				// 	//Other Positions

					
				// 	$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id'=>$applicant_id))->row();

				// 	$email_data = array(
				// 		'name' => $applicant_info->firstname." ".$applicant_info->lastname,
				// 		'position' => $mrf_info->position,
				// 		'no_of_days' => $this->config->item('applicant_prescreen_call_day_limit')
				// 	);

					
				// 	$this->load->model('template');
	   //              $template = $this->template->get_module_template(38, 'applicant_prescreen_non_mt');
	   //              $message = $this->template->prep_message($template['body'], $email_data);
	   //              $recepients[] = $applicant_info->email;
	   //              $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$applicant_info->firstname." ".$applicant_info->lastname, $message);
					
				// }

				$result = $this->db->get_where("recruitment_applicant_application",array("applicant_id"=>$applicant_id));

				if ($result->num_rows() < 1){
					$data = array(
						'applicant_id' => $applicant_id,
						'position_applied' => $mrf_info->position_id,
						'mrf_id' => $mrf_id,
						'applied_date' => date('Y-m-d H:i:s'),
						'status' => 2,
						'mrf_id' => 0
					);
					//save aaplication
					$this->db->insert('recruitment_applicant_application',$data);
				}
				else{
					$this->db->where('lstatus',0);
					$this->db->where('status',1);
					$this->db->or_where('status',5);
					$this->db->update('recruitment_applicant_application', array('mrf_id' => $mrf_id, 'status' => 2, 'position_applied' => $mrf_info->position_id ));
				}

				$this->db->where('request_id',$mrf_id);
				$this->db->update('recruitment_manpower',array("status"=>"In-Process"));				
				
				$response->msg = "Candidate is successfully added.";
			    $response->msg_type = "success";
			    $data['json'] = $response;

			}
			else{

				$response->msg = "There is an error in adding candidates in MRF.";
			    $response->msg_type = "error";
			    $data['json'] = $response;

			}


			$this->load->view('template/ajax', array('json' => $response));

		}
		else{

			$response->msg = "Manpower Request is Required.";
			$response->msg_type = "error";
			$data['json'] = $response;

			$this->load->view('template/ajax', array('json' => $response));

		}


	}

	function get_exam_type()
	{
		$this->db->where('deleted', 0);
		$exam = $this->db->get('recruitment_exam_type');

		if ($exam && $exam->num_rows() > 0) {
			return $exam->result();
		}else{
			return array();
		}
		
	}

	function get_recommendation()
	{
		$this->db->where('deleted', 0);
		$recommendation = $this->db->get('recruitment_recommendation');

		if ($recommendation && $recommendation->num_rows() > 0) {
			return $recommendation->result();
		}else{
			return array();
		}
		
	}

	function get_candidate_interviewer($candidate_id){
		$this->db->select('recruitment_manpower_candidate_interviewer.candidate_interviewer_id,firstname,lastname,datetime,recruitment_manpower_candidate_interviewer.current_candidate_status,interviewer_type, user.user_id');
		$this->db->where('recruitment_manpower_candidate_interviewer.deleted',0);
		$this->db->where('recruitment_manpower_candidate_interviewer.candidate_id',$candidate_id);
		$this->db->join('user','recruitment_manpower_candidate_interviewer.user_id = user.user_id');
		$this->db->join('recruitment_manpower_candidate','recruitment_manpower_candidate_interviewer.candidate_id = recruitment_manpower_candidate.candidate_id');
		$result = $this->db->get('recruitment_manpower_candidate_interviewer');

		if ($result && $result->num_rows() > 0){
			return $result;
		}
		else{
			return false;
		}
	}	

	function save_disqualified_candidate(){

		$applicant_status = $this->input->post('status');
		$applicant_id = $this->input->post('applicant_id');

		$this->db->select('recruitment_applicant.position_id, user_position.position');
		$this->db->where('applicant_id',$applicant_id);
		$this->db->join('user_position','user_position.position_id = recruitment_applicant.position_id');
		$applicant_info = $this->db->get('recruitment_applicant')->row();

		$this->system->update_application_status($this->input->post('applicant_id'), $applicant_status);

		$data = array(
			'applicant_id' => $applicant_id,
			'position_applied' => $applicant_info->position_id,
			'applied_date' => date('Y-m-d H:i:s'),
			'status' => $applicant_status,
			'mrf_id' => 0
		);

		//save aaplication
		$this->db->insert('recruitment_applicant_application',$data);

/*		//save aaplication
		$this->db->insert('recruitment_applicant_application',$data);

		if( $applicant_status == 5 ){ //Active File

			$this->db->where('applicant_id', $applicant_id);
			$this->db->update('recruitment_applicant', 
				array(
					'application_status_id' => $applicant_status,
					'af_pos_id' => $applicant_info->position_id
					)
				);

			$this->db->update('recruitment_applicant_application', array('lstatus' => 1 ), array('applicant_id' => $applicant_id ));

			$data = array(
				'applicant_id' => $applicant_id,
				'position_applied' => $applicant_info->position_id,
				'applied_date' => date('Y-m-d H:i:s'),
				'status' => $applicant_status,
				'mrf_id' => 0
			);

			//save aaplication
			$this->db->insert('recruitment_applicant_application',$data);

		}
		else{

			$this->db->where('applicant_id', $applicant_id);
			$this->db->update('recruitment_applicant', 
				array(
					'application_status_id' => 6
					)
				);

			$this->db->update('recruitment_applicant_application', array( 'status' => 6 ), array( 'applicant_id' => $applicant_id, 'status' => 1, 'lstatus' => 0 ));

		}*/
		
		//REJECTED status only
		if($applicant_status){
			$this->db->select('recruitment_applicant.firstname, recruitment_applicant.middleinitial, recruitment_applicant.lastname, recruitment_applicant.aux, user_position.position, recruitment_applicant.email');
			$this->db->where('applicant_id',$applicant_id);
			$this->db->join('user_position','user_position.position_id = recruitment_applicant.position_id');
			$applicant_info = $this->db->get('recruitment_applicant')->row();

			$email_data = array(
				'name' => $applicant_info->firstname." ".$applicant_info->middleinitial." ".$applicant_info->lastname." ".$applicant_info->aux,
				'position' => $applicant_info->position
			);

			$this->load->model('template');
	        $template = $this->template->get_module_template(38, 'applicant_prescreen_reject');
	        $message = $this->template->prep_message($template['body'], $email_data);
	        $recepients[] = $applicant_info->email;
	        $this->template->queue(implode(',', $recepients), '', $template['subject']." : ".$applicant_info->firstname." ".$applicant_info->lastname, $message);
    	}
		$response->msg = "Candidate is successfully added.";
	    $response->msg_type = "success";
	    $data['json'] = $response;


		$this->load->view('template/ajax', array('json' => $response));

	}

}

/* End of file */
/* Location: system/application */