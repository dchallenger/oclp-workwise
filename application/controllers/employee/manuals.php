<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manuals extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";http://localhost/hris/oclp/dashboard
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Policies and Procedures';
		$this->listview_description = 'This module lists all policies and procedures.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a policies and procedures.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a policies and procedures.';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {    	
    	$data['content'] = 'employee/manuals/index';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		if(!$this->is_superadmin && !$this->is_admin && $this->user_access[$this->module_id]['post'] != 1){
			$company_id = $this->userinfo['company_id'];
			$this->db->where( 'IF(company_recipients <> "",FIND_IN_SET( '.$company_id.', company_recipients ), "")', NULL, FALSE );
        }

		$this->db->where('deleted', 0);
		$this->db->where('content <>', '');

		$records = $this->db->get($this->module_table);
// dbug($this->db->last_query());
		if ($records && $records->num_rows() > 0) {
			$data['records']   = $records->result();
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/pdfobject.js"></script>';
			
			$data['side_nav_before'] = 'employee/manuals/sidebar';
		} else {
			$this->session->set_flashdata('flashdata', 'No manuals have been posted yet.');
			redirect ('/');
		}

		//load variables to env
		$this->load->vars( $data );

		//load the final view
		//load header
		$this->load->view( '/template/header' );
		$this->load->view( '/template/header-nav' );

		//load page content
		$this->load->view( '/template/page-content' );

		//load footer
		$this->load->view( '/template/footer' );
    }

	function detail()
	{
		redirect ($this->module_link);
	}

	function edit()
	{
		redirect ($this->module_link);
	}

	function ajax_save()
	{
		redirect ($this->module_link);
	}

	function delete()
	{
		redirect ($this->module_link);
	}
	// END - default module functions

	// START custom module funtions
	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */