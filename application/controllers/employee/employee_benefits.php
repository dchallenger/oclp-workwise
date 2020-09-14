<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_benefits extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists payroll accounts.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a payroll account';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a payroll account';
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
		$user_benefit = $this->input->post('user_benefit');
		if($this->input->post('record_id') == '-1') {
			$check_if_exist = $this->db->query("SELECT * FROM {$this->db->dbprefix}user_benefit a
												WHERE a.user_benefit = '{$user_benefit}' AND deleted = '0'");
		} else {
			$user_benefit_id = $this->input->post('record_id');
			$check_if_exist = $this->db->query("SELECT * FROM {$this->db->dbprefix}user_benefit a
												WHERE a.user_benefit = '{$user_benefit}' AND a.user_benefit_id != '{$user_benefit_id}' AND deleted = '0'");
		}
		if($check_if_exist->num_rows() == 0) {
			parent::ajax_save();
			//additional module save routine here		
		} else {
			$response->msg = "Benefit is already exist!";
			$response->msg_type = 'error';
			$this->set_message($response);
			$this->after_ajax_save();
		}
	}

	function delete()
	{
		$user_benefit_id = $this->input->post('record_id');
		$check_if_used = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee a
												WHERE a.user_benefit_id IN ({$user_benefit_id}) AND a.deleted = '0'");
		if($check_if_used->num_rows() == 0) {
			parent::delete();
		} else {
			if(strlen($master_id) > 1) {
				$response->msg = "One/More of the Benefit/s cannot be deleted.";	
			} else {
				$response->msg = "Benefit cannot be deleted.";	
			}
			$response->msg_type = 'error';
			$this->set_message($response);
			$this->after_ajax_save();
		}
	}
	// END - default module functions

	// START custom module funtions
	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */