<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_health extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Employee Health Information';
		$this->listview_description = 'This module lists employee health information.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an employee health information.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an employee health information.';
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
		$search_string[] = $this->db->dbprefix .'user.firstname LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';
		// $search_string[] = $this->db->dbprefix .'user_company_department.department LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}
	
	function _set_left_join() {
		parent::_set_left_join();

		$this->db->join('user', 'user.user_id = ' . $this->module_table . '.employee_id', 'left');
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
	function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			$data['updated_by']   = $this->userinfo['user_id'];
			$data['updated_date'] = date('Y-m-d H:i:s');

			if ($this->input->post('record_id') == '-1') {
				$data['created_by']   = $this->userinfo['user_id'];
				$data['created_date'] = date('Y-m-d H:i:s');
			}

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $data);
		}

		parent::after_ajax_save();
	}

	function get_status_ddlb(){
		$health_type = $this->input->post('health_type');
		switch( $health_type ){
			case 1:
				$this->db->where('preemp', '1');
				break;
			case 2:
				$this->db->where('ape', '1');
				break;
			case 3:
				$this->db->where('others', '1');
				break;
		}

		$this->db->where('deleted', 0 );
		$status = $this->db->get('employee_health_type_status');
		
		$response['ddlb'] = '<option value="">Select...</option>';
		if($status->num_rows() > 0){
			foreach( $status->result() as  $row ){
				$response['ddlb'] .= '<option value="'.$row->health_type_status_id.'">'.$row->health_type_status.'</option>';
			}
		}

		$data['json'] = $response;
		$this->load->view( $this->userinfo['rtheme'].'/template/ajax', $data );
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */