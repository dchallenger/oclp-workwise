<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wfh_inout extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Work From Home In/Out';
		$this->listview_description = 'This module lists no call no show';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = '';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = '';

		if(!$this->user_access[$this->module_id]['post'])
			$this->filter = $this->module_table.'.created_by = '.$this->userinfo['user_id'];
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['scripts'][] ='<link rel="stylesheet" type="text/css" href="'.base_url().'lib/jclock/css/jquery.jdigiclock.css" />';
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/jclock/lib/jquery.jdigiclock.js"></script>';

		$data['buttons'] = 'dtr/login-buttons';
		$data['content'] = 'dtr/time-editview';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$result = $this->db->get_where('employee_dtr', array("employee_id" => $this->userinfo['user_id'], "date" => date('Y-m-d')));

		if($result && $result->num_rows() > 0)
		{
			$this->db->where('time_in1 IS NOT NULL');
			$type = $this->db->get_where('employee_dtr', array("employee_id" => $this->userinfo['user_id'], "date" => date('Y-m-d')));
			if($type && $type->num_rows() > 0)
				$data['button_name'] = "time_out1";
			else
				$data['button_name'] = "time_in1";
		} else
			$data['button_name'] = "time_in1";

		// $data['button_name'] = "Time In/Out";

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

		//$data['buttons'] = $this->module_link . '/detail-buttons';	

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

			//$data['buttons'] = $this->module_link . '/edit-buttons';

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

	function time_in_out()
	{
		date_default_timezone_set('Asia/Manila');
		$user = $this->userinfo['user_id'];
		$date = date('Y-m-d');
		$time_date = date('Y-m-d H:i:s');
		$type = $this->input->post('type');

		$result = $this->db->get_where('employee_dtr', array("employee_id" => $user, "date" => $date));
		if($result && $result->num_rows() > 0)
			$this->db->update('employee_dtr', array($type => $time_date), array("employee_id" => $user, "date" => $date));
		else
			$this->db->insert('employee_dtr', array($type => $time_date, "employee_id" => $user, "date"=> $date));
	}

	function ajax_save()
	{
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
	// END custom module funtions

}

/* End of file */
/* Location: system/application */