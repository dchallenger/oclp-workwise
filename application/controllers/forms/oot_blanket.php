<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Oot_blanket extends MY_Controller
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

		$this->project = 0;

		if (CLIENT_DIR == "firstbalfour") {
			if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
				$dbprefix = $this->db->dbprefix;
				$subordinate_id = array();
				$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
				$subordinates = $this->system->get_subordinates_by_project($emp->employee_id);
				$subordinate_id = array(0);
				if( count($subordinates) > 0 && $subordinates != false){
					$subordinate_id = array();
					$blanket_id = array();
					foreach ($subordinates as $subordinate) {
							$subordinate_id[] = $subordinate['employee_id'];
							$sql = "SELECT * FROM {$dbprefix}{$this->module_table} WHERE FIND_IN_SET({$subordinate['employee_id']}, employee_id)";
							$blanket = $this->db->query($sql);
							
							if ($blanket && $blanket->num_rows() > 0) {
								foreach ($blanket->result() as $value) {
									$blanket_id[] = $value->ootblanket_id;	
								}
								
							}
					}

					$blankets = array_unique($blanket_id);

					$this->filter .= $dbprefix.'employee_oot_blanket.ootblanket_id IN ('.implode(',', $blankets).')';
				}else{
					$this->filter .= $dbprefix.'employee_oot_blanket.ootblanket_id IN (0)';
				}	
			}	
		}
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
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/modules/forms/blanket.js"></script>';
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

	function ajax_save(){
		parent::ajax_save();
		//additional module save routine here
	}

	function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->delete('employee_oot');

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->where('deleted', 0);

			$record = $this->db->get($this->module_table)->row();
			$users = explode(',', $record->employee_id);

			$data['form_status_id']		 = 3;			
			$data['ootblanket_id'] 		 = $this->key_field_val;
			$data['date']				 = $record->date;
			$data['datetime_from']		 = $record->datetime_from;
			$data['datetime_to']		 = $record->datetime_to;
			$data['reason']			 	 = $record->reason;
			//$data['documents']			 = $record->documents;

			$insert = array();
			foreach ($users as $user) {
				$data['employee_id'] = $user;
				$insert[] = $data;
			}

			$this->db->insert_batch('employee_oot', $insert);
		}

		parent::after_ajax_save();
	}

	function delete(){
		if( IS_AJAX ){
			if($this->user_access[$this->module_id]['delete'] == 1){
				if($this->input->post('record_id')){
					$record_id = explode(',', $this->input->post('record_id'));
					$this->db->where_in($this->key_field, $record_id);
					$this->db->update($this->module_table, array('deleted' => 1));

					$this->db->where_in($this->key_field, $record_id);
					$this->db->update('employee_oot', array('deleted' => 1));
					if( $this->db->_error_message() == "" ){
						$response->msg = "Record(s) has been deleted.";
						$response->msg_type = 'success';
					}
					else{
						$response->msg = $this->db->_error_message();
						$response->msg_type = 'error';
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		//additional module delete routine here
	}

	function get_category_value(){
		$category_id = $this->input->post('category_id');

		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('employee_oot_blanket',array("ootblanket_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$oot_blanket_category_id = $row->oot_blanket_category_id;

				if ($category_id == 1){
					$defaults = explode(',', $row->employee_id);
				}
				else{
					$defaults = explode(',', $row->oot_blanket_category_value_id);
				}
			}
		}

		$sub_list = '1=1';

		if (CLIENT_DIR == "firstbalfour") {
			if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
				$subordinate_id = array();
				$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
				$subordinates = $this->system->get_subordinates_by_project($emp->employee_id);
				$subordinate_id = array(0);
				if( count($subordinates) > 0 && $subordinates != false){

					$subordinate_id = array();

					foreach ($subordinates as $subordinate) {
							$subordinate_id[] = $subordinate['employee_id'];
					}
				}		

				$subordinate_list = implode(',', $subordinate_id);
				if( $subordinate_list != "" || $subordinate_list != 0){
					$sub_list = $this->db->dbprefix.'employee.employee_id IN ('.$subordinate_list.')';
				}
				else{
					if ($subordinates == false ) {
						$sub_list = $this->db->dbprefix.'employee.employee_id IN (0)';
					}
					
				}
			}	
		}


		$options = '';
		switch ($category_id) {
			case 1:	//by employee
				$this->db->select('employee.employee_id,firstname,lastname');
				$this->db->where('user.deleted',0);
				$this->db->where('employee.deleted',0);
				$this->db->where('employee.resigned',0);
				$this->db->where($sub_list, '', false);
				$this->db->join('employee','user.employee_id = employee.employee_id');
				$result = $this->db->get('user');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($oot_blanket_category_id == $category_id){
							$selected = (in_array($row->employee_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->employee_id . '"' . $selected . '>' . $row->firstname . '&nbsp;'. $row->lastname .'</option>';
					}
				}
				break;
			case 2: //by company
				$this->db->where('deleted',0);
				$result = $this->db->get('user_company');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($oot_blanket_category_id == $category_id){
							$selected = (in_array($row->company_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->company_id . '"' . $selected . '>' . $row->company . '</option>';
					}
				}
				break;
			case 3: //by division
				$this->db->where('deleted',0);
				$result = $this->db->get('user_company_division');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($oot_blanket_category_id == $category_id){
							$selected = (in_array($row->division_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->division_id . '"' . $selected . '>' . $row->division . '</option>';
					}
				}
				break;
			case 4: //by group
				$this->db->where('deleted',0);
				$result = $this->db->get('group_name');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($oot_blanket_category_id == $category_id){
							$selected = (in_array($row->group_name_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->group_name_id . '"' . $selected . '>' . $row->group_name . '</option>';
					}
				}
				break;
			case 5: //by department
				$this->db->where('deleted',0);
				$result = $this->db->get('user_company_department');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($oot_blanket_category_id == $category_id){
							$selected = (in_array($row->department_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->department_id . '"' . $selected . '>' . $row->department . '</option>';
					}
				}
				break;
			case 6: //by project
				$this->db->where('deleted',0);
				if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
					if ($this->project != 0 && is_array($this->project)) {
						$project_name_id = 'project_name_id IN ('.implode(',', $this->project).')';
						$this->db->where($project_name_id);
					}
					else{
						$this->db->where('project_name_id', 0);
					}
				}
				

				$result = $this->db->get('project_name');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if ($oot_blanket_category_id == $category_id){
							$selected = (in_array($row->project_name_id, $defaults)) ? ' selected="selected"' : '';
						}
						$options .= '<option value="' . $row->project_name_id . '"' . $selected . '>' . $row->project_name . '</option>';
					}
				}
				break;																			
		}
		$this->load->view('template/ajax', array('html' => $options));
	}

	function get_employee_position_by_category(){
		$category_id = $this->input->post('category_id');
		$category_value_id = implode('","', explode(',', $this->input->post('category_value')));
		$position_id = implode('","', explode(',', $this->input->post('position_id')));

		$defaults = array();
		if ($this->input->post('record_id') != '-1'){
			$result = $this->db->get_where('employee_oot_blanket',array("ootblanket_id" => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$defaults = explode(',', $row->employee_id);

				if ($category_id == $row->oot_blanket_category_id){
					$category_value_id = implode('","', explode(',', $row->oot_blanket_category_value_id));
				}
			}
		}

		$where = "";
		switch ($category_id) {
			case 2: //by company
				$where = 'u.company_id IN ("' . $category_value_id . '")';
				break;
			case 3: //by division
				$where = 'u.division_id IN ("' . $category_value_id . '")';
				break;
			case 4: //by group
				$where = 'u.group_name_id IN ("' . $category_value_id . '")';
				break;
			case 5: //by department
				$where = 'u.department_id IN ("' . $category_value_id . '")';
				break;
			case 6: //by project
				$where = 'u.project_name_id IN ("' . $category_value_id . '")';
				break;																			
		}

		//added to limit employee by selected position
		if($this->input->post('position_id') != "" && $this->input->post('position_id') != 'undefined'){
			if($where != ""){
				$where .= ' AND u.position_id IN ("' . $position_id . '")';
			}else{
				$where = 'u.position_id IN ("' . $position_id . '")';
			}
		}

		$this->db->select('e.employee_id, CONCAT(u.firstname, " ", u.lastname) as name', false);
		$this->db->from('employee e');
		$this->db->join('user u', 'u.user_id = e.employee_id', 'left');
		// $this->db->join('employee_work_assignment ewa', 'e.employee_id = ewa.employee_id', 'left');
		$this->db->where('e.deleted', 0);
		$this->db->where('resigned', 0);

		if ($where != ""){
			$this->db->where($where, '', false);
		}			

		$this->db->group_by('e.employee_id');
		$this->db->order_by('name');
		
		$employees = $this->db->get();
		$options["emp"] = '';
		
		if ($category_value_id != ""){
			if ($employees && $employees->num_rows() > 0) {
				foreach ($employees->result() as $employee) {
					$selected = (in_array($employee->employee_id, $defaults)) ? ' selected="selected"' : '';
					$options["emp"] .= '<option value="' . $employee->employee_id . '"' . $selected . '>' . $employee->name . '</option>';
				}			
			}			
		}

		//Query to get positions
		
		$where = "";
		switch ($category_id) {
			case 2: //by company
				$where = 'u.company_id IN ("' . $category_value_id . '")';
				break;
			case 3: //by division
				$where = 'u.division_id IN ("' . $category_value_id . '")';
				break;
			case 4: //by group
				$where = 'u.group_name_id IN ("' . $category_value_id . '")';
				break;
			case 5: //by department
				$where = 'u.department_id IN ("' . $category_value_id . '")';
				break;
			case 6: //by project
				$where = 'u.project_name_id IN ("' . $category_value_id . '")';
				break;																			
		}

		$this->db->select('DISTINCT(u.position_id), up.position', false);
		$this->db->from('employee e');
		$this->db->join('user u', 'u.user_id = e.employee_id', 'left');
		$this->db->join('user_position up', 'up.position_id = u.position_id', 'left');
		// $this->db->join('employee_work_assignment ewa', 'e.employee_id = ewa.employee_id', 'left');
		$this->db->where('e.deleted', 0);
		$this->db->where('resigned', 0);	

		if ($where != ""){
			$this->db->where($where, '', false);
		}			

		$this->db->group_by('e.employee_id');
		$this->db->order_by('position');

		$positions = $this->db->get();
		$options["pos"] = '';
		
		if ($category_value_id != ""){
			if ($positions && $positions->num_rows() > 0) {
				foreach ($positions->result() as $position) {
					if ($position->position != ''){
						$selected = (in_array($position->position_id, $defaults)) ? ' selected="selected"' : '';
						$options["pos"] .= '<option value="' . $position->position_id . '"' . $selected . '>' . $position->position . '</option>';
					}
				}			
			}			
		}

		$this->load->view('template/ajax', array('json' => $options));	
	}

	function is_projecthr()
	{
		$data['project_hr'] = false;

		if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
			$data['project_hr'] = true;
		}

		$this->load->view('template/ajax', array('json' => $data));
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */