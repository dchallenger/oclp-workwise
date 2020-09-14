<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Candidates_schedule extends MY_Controller {

	function __construct() {
		parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'Lists all';
		$this->jqgrid_title = "List";
		$this->detailview_title = 'Info';
		$this->detailview_description = '';
		$this->editview_title = 'Add/Edit';
		$this->editview_description = '';
	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['content'] = 'listview';

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

	function detail() {
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';

		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

		$record_id = $this->input->post('record_id');

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

	function edit() {
		if ($this->user_access[$this->module_id]['edit'] == 1) {
			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
			if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
				//$data['show_wizard_control'] = true;
			}
			$data['content'] = 'editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$record_id = $this->input->post('record_id');

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

	function ajax_save() {
		// Convert the date time to sql format.
		$datetime = $this->input->post('interview_datetime');
		$_POST['interview_datetime'] = date('Y-m-d H:i:s', strtotime($datetime));

		$candidate_id = $this->input->post('candidate_id');
		
		if ($this->input->post('for_reschedule') == FALSE) {
			// Set previous records to 0.
			$this->db->where('candidate_id', $candidate_id);
			$this->db->update($this->module_table, array('current' => 0));
		}

		parent::ajax_save();

		$this->db->where('interview_flag', 1);
		$this->db->limit(1);
		
		$result = $this->db->get('recruitment_candidate_status');
		
		if ($result && $result->num_rows() > 0) {
			$for_interview = $result->row()->candidate_status_id;
		} else { // Default to 1 if not exist.
			$for_interview = 1;
		}

		if ($this->input->post('for_reschedule') == TRUE) {			
			$data['reschedule_date'] = $this->input->post('interview_datetime');
		} else {
			$data['candidate_status_id'] = $for_interview;
			$data['interview_date'] 	 = $this->input->post('interview_datetime');
		}

		// Set status to For Interview.
		$this->db->where('candidate_id', $candidate_id);
		$this->db->update('recruitment_manpower_candidate', $data);
	}

	function delete() {
		parent::delete();
	}

	// END - default module functions
	// START custom module funtions

	/**
	 * Returns asarray of previous schedules of given candidate.
	 * 
	 * @param int $candidate_id Candidate_ID
	 * 
	 * @return mixed
	 */
	function _get_previous_schedules($candidate_id = 0) {
		if ($candidate_id == 0) {
			$candidate_id = $this->input->post('candidate_id');
		}

		if ($candidate_id < 0 && !$this->_candidate_exist($candidate_id)) {
			return false;
		} else {
			$this->db->where('candidate_id', $candidate_id);
			$this->db->where('current', 0);
			$this->db->where($this->module_table . '.deleted', 0);

			$this->db->join('contacted_thru', 'contacted_thru.contacted_thru_id = ' . $this->module_table . '.contacted_thru_id');

			$this->db->limit(5);

			$result = $this->db->get($this->module_table);

			if ($result->num_rows() > 0) {
				return $result->result_array();
			}
			return false;
		}
	}

	function quick_edit() {
		if (IS_AJAX) {
			if ($this->user_access[$this->module_id]['edit'] == 1) {
				$response->msg = "";

				if (!isset($_POST['record_id']) && $this->uri->rsegment(3))
					$_POST['record_id'] = $this->uri->rsegment(3);

				if ($this->input->post('record_id')) {
					$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/' . $this->module_link . '.js"></script>';

					$data['module'] = $this->module;
					$data['module_link'] = $this->module_link;
					$data['fmlinkctr'] = $this->input->post('fmlinkctr');

					$this->load->model('uitype_edit');
					$data['fieldgroups'] = $this->_record_detail($this->input->post('record_id'), true);

					$data['show_wizard_control'] = false;
					if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
						$data['show_wizard_control'] = true;
						$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form.js"></script>';
					}

					//other views to load
					$data['views'] = array('recruitment/candidates/previous_schedules');

					$data['previous_schedules'] = $this->_get_previous_schedules($this->input->post('candidate_id'));

					//load the final view
					$this->load->vars($data);

					if (isset($_POST['mode']) && $_POST['mode'] == 'copy')
						$_POST['record_id'] = -1;
					$response->quickedit_form = $this->load->view($this->userinfo['rtheme'] . "/quickedit", "", true);
				}
				else {
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			} else {
				$response->msg = "You dont have sufficient privilege to set interview schedule! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

}

/* End of file */
/* Location: system/application */