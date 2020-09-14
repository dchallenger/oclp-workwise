<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Leave_setup extends MY_Controller
{
	function __construct(){
		parent::__construct();
		
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';
	}

	// START - default module functions
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
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

	function detail(){
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

		// add config here
		if($this->config->item('earn_using_leave_credit_table') == 1)
			$data['values'] = $this->db->get_where('employee_type_leave_credit', array('leave_setup_id' => $this->input->post('record_id')))->result_array();

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

	function edit(){
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
		
		// with config
		if($this->input->post('record_id') != '-1' && $this->config->item('earn_using_leave_credit_table') == 1)
			$data['fields'] = $this->add_leave($this->input->post('record_id'));

		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
	}

	function ajax_save(){
		
		$where = array(
			'deleted' => 0,
			'employee_type_id' => $this->input->post('employee_type_id'),
			'application_form_id' => $this->input->post('application_form_id'),
			'accumulation_type_id' => $this->input->post('accumulation_type_id')
		);
		$rec = $this->db->get_where($this->module_table, $where);
		echo $this->db->_error_message();
		if( $rec->num_rows() == 1 ){
			$rec = $rec->row();
			$_POST['record_id'] = $rec->leave_setup_id;
		}

		parent::ajax_save();

		if($this->config->item('earn_using_leave_credit_table') == 1)
		{
			$this->db->delete('employee_type_leave_credit', array('leave_setup_id' => $this->key_field_val));
			if($this->input->post('incremented_leave') && count($this->input->post('incremented_leave')) > 0)
			{
				$incremented_leaves = $this->_rebuild_array($this->input->post('incremented_leave'), $this->key_field_val, $this->input->post('application_form_id'));

				$this->db->insert_batch('employee_type_leave_credit', $incremented_leaves);
			}
		}

		//additional module save routine here

	}

	private function _rebuild_array($array, $fkey = null, $leave_type) {
		if (!is_array($array)) {
			return array();
		}

		$new_array = array();

		$count = count(end($array));
		$index = 0;

		while ($count > $index) {
			foreach ($array as $key => $value) {
				$new_array[$index][$key] = $array[$key][$index];
				if (!is_null($fkey)) {
					$new_array[$index][$this->key_field] = $fkey;
				}
				$new_array[$index]['leave_type'] = $this->input->post('application_form_id');
			}

			$index++;
		}

		return $new_array;
	}


	function delete(){
		parent::delete();

		//additional module delete routine here
	}

	function add_leave($id = false)
	{
		if($id)
			$data = $this->db->get_where('employee_type_leave_credit', array('leave_setup_id' => $id))->result_array();
		else
			$data = false;

		$response = $this->load->view($this->userinfo['rtheme'].'/leaves/setup/leave_row', array('values' => $data), true);

		if($data)
			return $response;
		else
			$this->load->view('template/ajax', array('html' => $response));
	}
	// END - default module functions

	// START custom module funtions

	// END custom module funtions

}

/* End of file */
/* Location: system/application */