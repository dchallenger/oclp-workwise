<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal_planning_reminders extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists Appraisal Reminder.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about Appraisal Reminder';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about Appraisal Reminder';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';

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

	function edit()
	{
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

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

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
		else{
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function ajax_save()
	{
		if (strtotime($this->input->post('date_to')) < strtotime($this->input->post('date_from'))) {
			$data['msg'] = 'Publish Date : Invalid date range.';
			$data['msg_type'] = 'error';
			$data['json'] = $data;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		}
		
		parent::ajax_save();

		//additional module save routine here

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function get_employees()
	{
		if (IS_AJAX)
		{
			$planning_period_id = $this->input->post('planning_period_id');
			// $status_id = $this->input->post('status_id');
			$record_id = $this->input->post('record_id');

			$appraisal_planning_period = $this->db->query(" SELECT * FROM {$this->db->dbprefix}appraisal_planning_period WHERE planning_period_id = '{$planning_period_id}'")->row();
			
			$options = '';

			$employee_company =  $this->db->query(" SELECT * FROM {$this->db->dbprefix}user_company WHERE company_id IN ({$appraisal_planning_period->company_id})");
			$company = array();
			if ($employee_company && $employee_company->num_rows() > 0) {
				foreach ($employee_company->result() as $_company) {
					$company[] = $_company->company;
				}
				$response['employee_company'] = implode(',', $company);
			}else{
				$response['employee_company'] = "";
			}
			

			$where = "company_id IN (".$appraisal_planning_period->company_id.") AND status_id IN (" .$appraisal_planning_period->employment_status_id. ")";
			$this->db->where($where);
			$this->db->where('inactive', 0);
			$this->db->where('user.deleted', 0);
			$this->db->join('employee', 'employee.employee_id = user.employee_id');
			$this->db->order_by('firstname,lastname', 'ASC');
			$result = $this->db->get('user');
			


			$this->db->where($this->key_field, $record_id);
			$record = $this->db->get($this->module_table);
			$response['employees'] = '';
			if ($record && $record->num_rows() > 0) {
				$rec = $record->row();
				$employees = $rec->employee_id;
				$response['employees'] = explode(',', $employees);
			}

			if ($result && $result->num_rows() > 0) {
				$employee = $result->result();
				
				foreach ($employee as $emp) {
					$options .= '<option value="'.$emp->employee_id.'">'.$emp->firstname." ".$emp->middleinitial." ".$emp->lastname. " ".$emp->aux.'</option>';
				}

				$response['result'] = $options;
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
				
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */