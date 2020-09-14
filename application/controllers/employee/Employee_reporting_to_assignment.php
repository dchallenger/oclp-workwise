<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_reporting_to_assignment extends MY_Controller
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
		parent::ajax_save();

		$ar_merge = array();

		$reporting_to_employee_id = $this->input->post('employee_id_reporting_to');

		$this->db->where('employee_id',$reporting_to_employee_id);
		$result = $this->db->get('employee');

		if ($result && $result->num_rows() > 0){
			$reporting_to_info = $result->row();
			$reporting_to_initial_array = explode(',',$reporting_to_info->reporting_to);

			$subordinates_employee_id_array = explode(',',$this->input->post('subordinates_employee_id'));
			$subordinates_division_pos_employee_id_array = explode(',',$this->input->post('subordinates_division_pos_employee_id'));
			$subordinates_project_pos_employee_id_array = explode(',',$this->input->post('subordinates_project_pos_employee_id'));
			$subordinates_group_pos_employee_id_array = explode(',',$this->input->post('subordinates_group_pos_employee_id'));
			$subordinates_department_pos_employee_id_array = explode(',',$this->input->post('subordinates_department_pos_employee_id'));

			$ar_merge = array_merge($subordinates_employee_id_array,$subordinates_division_pos_employee_id_array,$subordinates_project_pos_employee_id_array,$subordinates_group_pos_employee_id_array,$subordinates_department_pos_employee_id_array);

			foreach ($ar_merge as $key => $value) {
			  if($value == "" || $value == '""') {
			     unset($ar_merge[$key]);
			  }
			}

			$qs = "'".implode("','",$ar_merge)."'";
			$qry = "UPDATE {$this->db->dbprefix}employee SET reporting_to = IF(reporting_to <> '',CONCAT(reporting_to,',','{$reporting_to_employee_id}'),'{$reporting_to_employee_id}')
					WHERE employee_id IN ({$qs})";
			$this->db->query($qry);

/*			$this->db->where_in('employee_id',$ar_merge);
			$this->db->update('employee',array("reporting_to" => $reporting_to_employee_id));*/
		}
		//additional module save routine here
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function get_category_value(){
		$category_id = $this->input->post('organization_category_id');

		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('employee_reporting_to_assignment',array("employee_rta_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$employee_rta_id = $row->organization_category_id;

				$defaults = explode(',', $row->organization_category_value_id);
			}
		}

		$options = '';
		switch ($category_id) {
			case 1: //by division
				$this->db->where('deleted',0);
				$this->db->order_by('division');
				$result = $this->db->get('user_company_division');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($employee_rta_id == $category_id){
							$selected = (in_array($row->division_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->division_id . '"' . $selected . '>' . $row->division . '</option>';
					}
				}
				break;
			case 2: //by group
				$this->db->where('deleted',0);
				$this->db->order_by('group_name');
				$result = $this->db->get('group_name');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($employee_rta_id == $category_id){
							$selected = (in_array($row->group_name_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->group_name_id . '"' . $selected . '>' . $row->group_name . '</option>';
					}
				}
				break;
			case 3: //by department
				$this->db->where('deleted',0);
				$this->db->order_by('department');
				$result = $this->db->get('user_company_department');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($employee_rta_id == $category_id){
							$selected = (in_array($row->department_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->department_id . '"' . $selected . '>' . $row->department . '</option>';
					}
				}
				break;
			case 4: //by project
				$this->db->where('deleted',0);
				$this->db->order_by('project_name');
				$result = $this->db->get('project_name');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($employee_rta_id == $category_id){
							$selected = (in_array($row->project_name_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->project_name_id . '"' . $selected . '>' . $row->project_name . '</option>';
					}
				}
				break;																			
		}
		$this->load->view('template/ajax', array('html' => $options));
	}

	function get_employee_by_position(){
		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('employee_reporting_to_assignment',array("employee_rta_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$defaults = explode(',', $row->employee_id_reporting_to);
			}
		}

		$options = '';
		$this->db->order_by('firstname,middlename,lastname');
		$result = $this->db->get_where('user',array("position_id" => $this->input->post('position_id'),"deleted" => 0));
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$selected = (in_array($row->employee_id, $defaults)) ? ' selected="selected"' : '';
				$options .= '<option value="' . $row->employee_id . '"' . $selected . '>' . $row->firstname . '&nbsp;' . $row->middleinitial  . '&nbsp;' . $row->lastname  . '&nbsp;' . $row->aux . '</option>';
			}
		}
		$this->load->view('template/ajax', array('html' => $options));		
	}

	function get_position_by_category(){
		$category_id = $this->input->post('organization_category_id');
		$category_value = explode(',',$this->input->post('organization_category_value_id'));
		$position_ids = explode(',',$this->input->post('position_id'));
		
		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('employee_reporting_to_assignment',array("employee_rta_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$defaults = explode(',', $row->subordinates_position_id);
			}
		}

		switch ($category_id) {
			case 1:
				$this->db->where_in('user.division_id',$category_value);
				break;
			case 2:
				$this->db->where_in('user.group_name_id',$category_value);
				break;	
			case 3:
				$this->db->where_in('user.department_id',$category_value);
				break;							
			case 4:
				$this->db->where_in('user.project_name_id',$category_value);
				break;			
		}

		$this->db->select('user.position_id,position');
		// $this->db->join('user','employee_work_assignment.employee_id = user.employee_id');
		$this->db->join('user_position','user.position_id = user_position.position_id');
		$this->db->group_by('user.position_id');
		$this->db->order_by('position');
		$result = $this->db->get('user');
		
		$options = '';
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$selected = (in_array($row->position_id, $defaults) || in_array($row->position_id, $position_ids)) ? ' selected="selected"' : '';
				$options .= '<option value="'. $row->position_id .'"'. $selected .'>'. $row->position .'</option>';
			}
		}

		if ($this->input->post('organization_category_value_id') == "") {
			$options = "";
		}

		$this->load->view('template/ajax', array('html' => $options));	
	}

	function get_user_by_position(){
		$position_id =  explode(',',$this->input->post('position_id'));
		$employee_ids = explode(',',$this->input->post('employee_id')); 

		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('employee_reporting_to_assignment',array("employee_rta_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$defaults = explode(',', $row->subordinates_employee_id);
			}
		}

		$options = '';

		$this->db->where_in('position_id',$position_id);
		$this->db->order_by('firstname,middlename,lastname');		
		$result = $this->db->get('user');

		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$selected = (in_array($row->employee_id, $defaults) || in_array($row->employee_id, $employee_ids)) ? ' selected="selected"' : '';
				$options .= '<option value="' . $row->employee_id . '"' . $selected . '>' . $row->firstname . '&nbsp;' . $row->middleinitial  . '&nbsp;' . $row->lastname  . '&nbsp;' . $row->aux . '</option>';
			}
		}
		$this->load->view('template/ajax', array('html' => $options));		
	}	

	function get_user_by_position_compli(){
		$category_id = $this->input->post('organization_category_id');
		$position_id =  explode(',',$this->input->post('position_id'));

		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('employee_reporting_to_assignment',array("employee_rta_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				switch ($category_id) {
					case 1:
						$defaults = explode(',', $row->subordinates_division_pos_employee_id);
						break;
					case 2:
						$defaults = explode(',', $row->subordinates_group_pos_employee_id);			
						break;	
					case 3:
						$defaults = explode(',', $row->subordinates_department_pos_employee_id);			
						break;							
					case 4:
						$defaults = explode(',', $row->subordinates_project_pos_employee_id);			
						break;			
				}				
			}
		}

		$options = '';

		$this->db->where_in('position_id',$position_id);
		$this->db->order_by('firstname,middlename,lastname');
		$result = $this->db->get('user');

		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$selected = (in_array($row->employee_id, $defaults)) ? ' selected="selected"' : '';
				$options .= '<option value="' . $row->employee_id . '"' . $selected . '>' . $row->firstname . '&nbsp;' . $row->middleinitial  . '&nbsp;' . $row->lastname  . '&nbsp;' . $row->aux . '</option>';
			}
		}
		$this->load->view('template/ajax', array('html' => $options));		
	}	

	function get_position_by_category_compli(){
		$category_id = $this->input->post('organization_category_id');
		$category_value = explode(',',$this->input->post('organization_category_value_id'));

		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('employee_reporting_to_assignment',array("employee_rta_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
			}
		}

		switch ($category_id) {
			case 1:
				$defaults = explode(',', $row->subordinates_division_position_id);			
				$this->db->where_in('user.division_id',$category_value);
				break;
			case 2:
				$defaults = explode(',', $row->subordinates_group_position_id);			
				$this->db->where_in('user.group_name_id',$category_value);
				break;	
			case 3:
				$defaults = explode(',', $row->subordinates_department_id);			
				$this->db->where_in('user.department_id',$category_value);
				break;							
			case 4:
				$defaults = explode(',', $row->subordinates_project_position_id);			
				$this->db->where_in('user.project_name_id',$category_value);
				break;			
		}

		$this->db->select('user.position_id,position');
		// $this->db->join('user','employee_work_assignment.employee_id = user.employee_id');
		$this->db->join('user_position','user.position_id = user_position.position_id');
		$this->db->group_by('user.position_id');
		$this->db->order_by('position');
		$result = $this->db->get('user');

		$options = '';
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$selected = (in_array($row->position_id, $defaults)) ? ' selected="selected"' : '';
				$options .= '<option value="'. $row->position_id .'"'. $selected .'>'. $row->position .'</option>';
			}
		}
		$this->load->view('template/ajax', array('html' => $options));	
	}	

	function get_reporting_to_position(){
		$this->db->where('employee_rta_id',$this->input->post('record_id'));
		$result = $this->db->get('employee_reporting_to_assignment');
		if ($result && $result->num_rows() > 0){
			$row = $result->row_array();
			$response->category_id = $row;

			switch ($row['organization_category_id']) {
				case 1:
					$this->db->select('GROUP_CONCAT(division) AS division');
					$this->db->where_in('division_id',explode(',',$row['organization_category_value_id']));	
					$this->db->order_by('division');			
					$cat_value = $this->db->get('user_company_division');
					if ($cat_value && $cat_value->num_rows() > 0){
						$value = $cat_value->row()->division;
					}	
					break;
				case 2:
					$this->db->select('GROUP_CONCAT(group_name) AS group_name');
					$this->db->where_in('group_name_id',explode(',',$row['organization_category_value_id']));	
					$this->db->order_by('group_name');			
					$cat_value = $this->db->get('group_name');
					if ($cat_value && $cat_value->num_rows() > 0){
						$value = $cat_value->row()->group_name;
					}	
					break;
				case 3:
					$this->db->select('GROUP_CONCAT(department) AS department');
					$this->db->where_in('department_id',explode(',',$row['organization_category_value_id']));	
					$this->db->order_by('department');			
					$cat_value = $this->db->get('user_company_department');
					if ($cat_value && $cat_value->num_rows() > 0){
						$value = $cat_value->row()->department;
					}	
					break;
				case 4:
					$this->db->select('GROUP_CONCAT(project_name) AS project_name');
					$this->db->where_in('project_name_id',explode(',',$row['organization_category_value_id']));	
					$this->db->order_by('project_name');						
					$cat_value = $this->db->get('project_name');
					if ($cat_value && $cat_value->num_rows() > 0){
						$value = $cat_value->row()->project_name;
					}	
					break;															
			}	

			$response->category_value = $value;					
		}

		$this->load->view('template/ajax', array('json' => $response));
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */