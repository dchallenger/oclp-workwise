<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_unit_tree extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists employee health information.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an employee health information.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an employee health information.';

/*		if( $this->user_access[$this->module_id]['post'] != 1){
    		$this->filter = $this->db->dbprefix.'user_company_department.department_id = '.$this->userinfo['department_id'];
        }*/
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
		//$data['jqg_buttons'] = $this->_default_grid_buttons();

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

		$this->db->where('department_id',$this->input->post('department_get'));
		$query=$this->db->get('employee_unit_tree');
		$this->db->set('primary_contact', $this->input->post('primary_contact'));
		$this->db->set('department_id', $this->input->post('department_get'));	
		$this->db->set('alternate_contact', $this->input->post('alternate_contact'));
		if($query->num_rows()===0)
		{
			$this->db->insert('employee_unit_tree');
		}
		else
		{
			$this->db->where('department_id', $this->input->post('department_get'));
			$this->db->update('employee_unit_tree');
		}
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}

	function _default_grid_actions($module_link = "", $container = "", $record = array()) {
		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

		if ($this->user_access[$this->module_id]['view']) {
			$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
		}

		if ($this->user_access[$this->module_id]['edit']) {
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		$actions .= '</span>';

		return $actions;
	}

	function _set_filter(){
		if( $this->user_access[$this->module_id]['post'] != 1){
			$this->db->where_in('user_company_department.department_id', $this->userinfo['department_id']);
        }				
	}

	function get_employees_within_department()
	{
		if (is_null($this)) { 
			$ci =& get_instance();
			if($ci->userinfo['user_id']!=1 ){

				$ci->db->select('user_id, department, firstname, middleinitial, lastname, aux');
				$ci->db->join('user_company_department', 'user_company_department.department_id = user.department_id');
				$ci->db->where('user.deleted', 0);
				$ci->db->where('user_company_department.department_id', $ci->userinfo['department_id']); 
				$ci->db->order_by('firstname');
				$result = $ci->db->get('user')->result_array();

			}
			if($ci->userinfo['user_id']==1 || $ci->user_access[$ci->module_id]['post'] == 1){

				$ci->db->select('user_id, department, firstname, middleinitial, lastname, aux');
				$ci->db->join('user_company_department', 'user_company_department.department_id = user.department_id');
				$ci->db->where('user.deleted', 0);
				$ci->db->order_by('firstname');
				$result = $ci->db->get('user')->result_array();
				
			}

			return $result;
		}

		if($this->userinfo['user_id']!=1 ){

			$this->db->select('user_id, department, firstname, middleinitial, lastname, aux');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id');
			$this->db->where('user.deleted', 0);
			$this->db->where('user_company_department.department_id', $this->userinfo['department_id']); 
			$this->db->order_by('firstname');
			$result = $this->db->get('user')->result_array();

		}
		if($this->userinfo['user_id']==1 || $this->user_access[$this->module_id]['post'] == 1){

			$this->db->select('user_id, department, firstname, middleinitial, lastname, aux');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id');
			$this->db->where('user.deleted', 0);
			$this->db->order_by('firstname');
			$result = $this->db->get('user')->result_array();
			
		}

		return $result;
	}	
	// END - default module functions

	// START custom module funtions
}

/* End of file */
/* Location: system/application */