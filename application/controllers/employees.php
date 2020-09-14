<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Employees
 *
 * @author jconsador
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Employees extends MY_Controller {
	const DEPARTMENT_TABLE = 'user_company_department';
	const POSITIONS_TABLE = 'user_position';

	function __construct() {
		parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array('employee' => 'employee_id'); //table => field format

		$this->listview_title         = $this->module_name;
		$this->listview_description   = $this->module_name;
		$this->jqgrid_title           = "";
		$this->detailview_title       = $this->module_name;
		$this->detailview_description = $this->module_name;
		$this->editview_title         = 'Add/Edit Employee';
		$this->editview_description   = '';
		$this->_detail_types          = array('education', 'employment', 'family', 'references', 'training', 'affiliates', 'skill','test_profile', 'otherinfo1', 'insurance','accountabilities','attachment');		

		$this->load->helper('form');

		$dbprefix = $this->db->dbprefix;
		$this->filter = $dbprefix."user.inactive = 0 AND ".$dbprefix."employee.resigned = 0";
		if( $this->input->post('filter') && $this->input->post('filter') == "inactive" ) $this->filter = $dbprefix."user.inactive = 1 AND ".$dbprefix."employee.resigned = 0";;
		if( $this->input->post('filter') && $this->input->post('filter') == "resigned" ) $this->filter = $dbprefix."employee.resigned = 1";

		if($this->config->item('with_floating') == 1)
		{
			$result = $this->hdicore->get_all_floating(date('Y-m-d'));

			if($result && count($result) > 0)
			{
				$floating = implode(',',$result);
				if( $this->input->post('filter') && $this->input->post('filter') == "floating" ) 
					$this->filter = $dbprefix."user.employee_id IN (".$floating.")";
			}
		}

		if( $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['publish'] != 1){
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
			$subordinate_id = array(0);
			if( count($subordinates) > 0 ){

				$subordinate_id = array();

				foreach ($subordinates as $subordinate) {
						$subordinate_id[] = $subordinate['user_id'];
				}
			}
			$subordinate_list = implode(',', $subordinate_id);
			if( $subordinate_list != "" )
				$this->filter .= ' AND '. $dbprefix.'user.employee_id IN ('.$subordinate_list.')';
			else
				$this->filter .= ' AND '. $dbprefix.'user.employee_id IN (0)';
		}


	}

	// START - default module functions
	// default jqgrid controller method
	function index($holder = false) {

		if( $this->user_access[$this->module_id]['list'] != 1 ){
			$this->my201();
		}
		else{

			$data['scripts'][] = multiselect_script();
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
			$data['jqgrid_loadComplete'] = 'init_filter_tabs();';
			
			$tabs = array();
			$tabs[] = '<li class="active" filter="active"><a href="javascript:void(0)">Active</li>';
            $tabs[] = '<li filter="inactive"><a href="javascript:void(0)">Inactive</li>';
            $tabs[] = '<li filter="resigned"><a href="javascript:void(0)">Resigned</li>';
            if($this->config->item('with_floating') == 1)
            	$tabs[] = '<li filter="floating"><a href="javascript:void(0)">Floating</li>';
            if( sizeof( $tabs ) > 1 ) $data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
			
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
	}

	/**
	 * check wether user has overall access to modu;e
	 * @return void
	 */
	public function _visibility_check(){
		$excempt = array('my201', 'get_employee_type', 'get_rank_range', 'get_company_departments', 'get_company_positions', 'get_form', 'get_job_level_info');
		if(!in_array($this->method, $excempt) && !isset( $_POST['my201'] )){
			$this->hdicore->_visibility_check();
		}

		if( isset($_POST['my201']) ){
			$settings =  $this->hdicore->_get_config('edit_201_settings');
			$today = date('Y-m-d');
			$date_from = date('Y-m-d', strtotime($settings['date_from']));
			$date_to = date('Y-m-d', strtotime($settings['date_to']));
			if( $settings['enable_edit'] == 1 && $today >= $date_from && $today <= $date_to ){
				$this->user_access[$this->module_id]['edit'] = 1;
			}
		}
	}

	function detail() {
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/applicants_detailview.js"></script>';
		if( $this->user_access[$this->module_id]['post'] <> 1 ){
		 	$data['scripts'][] = '<script>$(document).ready(function () {  $(\'label[for="critical"]\').parent().remove(); });</script>'; 
		}

		$data['ws'] = $this->system->get_employee_worksched($this->key_field_val, date('Y-m-d'), true);

		if (IS_AJAX && $this->input->post('flag') == 0) {
			$data['content'] = 'employees/detailview';
		} else {
			$data['content'] = 'employees/compactview';
		}

		$data['show_buttons'] = true;

		//other views to load
		$data['views'] = array();

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
		}

		$record_id = $this->input->post('record_id');

		foreach ($this->_detail_types as $detail){
			$data[$detail] = $this->_get_employee_detail($detail);
		}

		$data['additional_training'] = $this->_get_additional_training();

		$data['employee_name'] = 'New Employee';

		if ($record_id > 0) {

			$employee_training = $this->db->get_where('employee', array('employee_id' => $record_id))->row();
			$data['total_running_balance'] = $employee_training->total_training_running_balance;

			$this->db->limit(1);
			$employee = $this->db->get_where('user', array('employee_id' => $record_id))->row();

			$data['employee_name'] = $employee->lastname . ', ' . $employee->firstname . ' ' . $employee->middleinitial ;
			$data['job_rank_id'] = $employee->job_rank_id;
		}

		$data['wizard_header'] = 'recruitment/applicants/wizard_header';

		if ( CLIENT_DIR == 'hdi' && $this->user_access[$this->module_id]['post'] != 1 ){
			$data['buttons'] = '/employees/detail-buttons';
		}

		//load variables to env
		$this->load->vars($data);

		if (!IS_AJAX) {
			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$data['html'] = $this->load->view($this->userinfo['rtheme'] . '/' . $data['content'], '', true);

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
	}

	function edit() {
		
		if ($this->user_access[$this->module_id]['edit'] == 1) {
			$this->load->helper('form');
			
			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = multiselect_script();
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';

			if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
			}

			$data['content'] = 'employees/editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$record_id = $this->key_field_val;

			$data['employee_name'] = 'New Employee';
			$employee = $this->hdicore->_get_userinfo( $this->key_field_val );
			$employee_record = $this->db->get_where('user', array('employee_id' => $this->key_field_val))->row();

			if ( $record_id > 0 && $employee) {
				$data['employee_name'] = $employee->lastname . ', ' . $employee->firstname . ' ' . $employee_record->middleinitial ;
				if ($employee->photo != '' && file_exists($employee->photo)) {
					$data['photo'] = $employee->photo;
				}			
			}

	
			$data['wizard_header'] = 'recruitment/applicants/wizard_header';

			foreach ($this->_detail_types as $detail) {
				$data[$detail] = $this->_get_employee_detail($detail);
			}

			// Get default fieldgroup to open, if any.
			$default_fg = $this->input->post('default_fg');
			if (isset($default_fg) && $default_fg > 0) {
				$data['default_fg'] = $default_fg;
			}

			// get clearance approver
			$result = $this->db->get('employee_clearance_form_checklist');

			$options = array();

            foreach ($result->result() as $row){
            	$options[$row->employee_clearance_form_checklist_id] = $row->name;
            }

            $data['employee_clearance'] = $options;
			// end get clearance approver

			// get position
			$this->db->order_by('position');
			$result =  $this->db->get_where('user_position',array("deleted"=>0));

			$rows_array = array();
			if ($result && $result->num_rows() > 0):
				$rows_array[$row->position_id] = "Select position...";
				foreach ($result->result() as $row) {
					$rows_array[$row->position_id] = $row->position;
				}
			endif;

			$data['position'] = $rows_array;
			// end get position

			if(CLIENT_DIR != 'oams') // added for the meantime. it's causing error for openaccess due to employee_training_status missing.
			{
			// get training status
				$this->db->order_by('employee_training_status');
				$result =  $this->db->get_where('employee_training_status',array("deleted"=>0));

				$rows_array = array();
				if ($result && $result->num_rows() > 0):
					$rows_array[$row->employee_training_status_id] = "Select status...";
					foreach ($result->result() as $row) {
						$rows_array[$row->employee_training_status_id] = $row->employee_training_status;
					}
				endif;

				$data['training_status'] = $rows_array;
				// end training status
			}
			// get skill type
			$result =  $this->db->get_where('skill_type',array("deleted"=>0));

			$rows_array = array();
			if ($result && $result->num_rows() > 0):
				$rows_array[$row->skill_type_id] = "Select skill type...";
				foreach ($result->result() as $row) {
					$rows_array[$row->skill_type_id] = $row->skill_type;
				}
			endif;
			
			$data['skill_type'] = $rows_array;
			// end skill type

			// get skill name
			$result =  $this->db->get_where('skill_name',array("deleted"=>0));

			$rows_array = array();
			if ($result && $result->num_rows() > 0):
				$rows_array[$row->skill_name_id] = "Select skill name...";
				foreach ($result->result() as $row) {
					$rows_array[$row->skill_name_id] = $row->skill_name;
				}
			endif;
			
			$data['skill_name'] = $rows_array;
			// end skill name

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
	
	function my201(){

		$settings =  $this->hdicore->_get_config('edit_201_settings');
		$today = date('Y-m-d');
		$date_from = date('Y-m-d', strtotime($settings['date_from']));
		$date_to = date('Y-m-d', strtotime($settings['date_to']));
		if( $settings['enable_edit'] == 1 && $today >= $date_from && $today <= $date_to ){
			$_POST['record_id'] = $this->user->user_id;
			$this->_edit201();
		}
		else{
			$this->_my201();
		}
	}

	function _edit201(){
		$this->load->helper('form');
		$this->load->model( 'uitype_edit' );
		$this->key_field_val = $this->input->post( 'record_id' );
		$check_record = $this->_record_exist( $this->input->post( 'record_id' ) );
		if( $check_record->exist || $this->input->post( 'record_id' ) == "-1" ){
			$data['fieldgroups'] = $this->_record_detail( $this->input->post( 'record_id' ) );
			$this->key_field_val = $this->input->post('record_id');
		}
		else{
			$data['error'] = $check_record->error_message;
			$data['error2'] = $check_record->error_message2;
		}

		if( $this->input->post('duplicate') ) $data['duplicate'] = TRUE;
		
		$this->load->vars($data);
		//additional module edit routine here
		$data['show_wizard_control'] = false;
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
		}

		$data['content'] = 'employees/editview';

		//other views to load
		$data['views'] = array();
		$data['views_outside_record_form'] = array();

		$record_id = $this->key_field_val;

		$data['employee_name'] = 'New Employee';
		$employee = $this->hdicore->_get_userinfo( $this->key_field_val );
		$employee_record = $this->db->get_where('user', array('employee_id' => $this->key_field_val))->row();
		if ( $record_id > 0 && $employee && $employee_record ) {
			$data['employee_name'] = $employee->lastname . ', ' . $employee->firstname . ' ' . $employee_record->middleinitial ;
			if ($employee->photo != '' && file_exists($employee->photo)) {
				$data['photo'] = $employee->photo;
			}			
		}

		$data['wizard_header'] = 'recruitment/applicants/wizard_header';

		foreach ($this->_detail_types as $detail) {
			$data[$detail] = $this->_get_employee_detail($detail);
			
		}

		// Get default fieldgroup to open, if any.
		$default_fg = $this->input->post('default_fg');
		if (isset($default_fg) && $default_fg > 0) {
			$data['default_fg'] = $default_fg;
		}

		$data['enable_edit'] = 1;

		$data['buttons'] = $this->userinfo['rtheme'] . "/". $this->module_link . '/my201/edit-buttons';

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

	function _my201(){
		$this->load->model( 'uitype_detail' );
		$check_record = $this->_record_exist( $this->user->user_id );			
		if( $check_record->exist ){
			$data['fieldgroups'] = $this->_record_detail(  $this->user->user_id );				
			$this->key_field_val = $this->user->user_id;
		}
		else{
			$data['error'] = $check_record->error_message;
			$data['error2'] = $check_record->error_message2;
		}
		$this->load->vars( $data );
		
		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/modules/recruitment/applicants_detailview.js"></script>';
		if( $this->user_access[$this->module_id]['post'] <> 1 ){
		 	$data['scripts'][] = '<script>$(document).ready(function () {  $(\'label[for="critical"]\').parent().remove(); });</script>'; 
		}


		if ( IS_AJAX && $this->input->post('flag') == 0) {
			$data['content'] = 'employees/detailview';
		} else {
			$data['content'] = 'employees/compactview';
		}
		$data['show_buttons'] = false;

		//other views to load
		$data['views'] = array();

		if ( !empty($this->module_wizard_form) ) {
			$data['show_wizard_control'] = true;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
		}

		$record_id = $this->user->user_id;

		$data['ws'] = $this->system->get_employee_worksched(  $this->key_field_val, date('Y-m-d'), true );
		
		foreach ($this->_detail_types as $detail) {
			$data[$detail] = $this->_get_employee_detail($detail, $this->user->user_id);
		}

		$data['employee_name'] = 'New Employee';

		if ($record_id > 0) {
			$this->db->limit(1);
			$employee = $this->db->get_where('user', array('employee_id' => $record_id))->row();

			$data['employee_name'] = $employee->lastname . ', ' . $employee->firstname . ' ' . $employee->middleinitial ;
		}

		$data['wizard_header'] = 'recruitment/applicants/wizard_header';

		//load variables to env
		$this->load->vars($data);

		if (!IS_AJAX) {
			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$data['html'] = $this->load->view($this->userinfo['rtheme'] . '/' . $data['content'], '', true);

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
	}

	function ajax_save($bypass_parent = false, $trans_started = false) {	

		if($trans_started == false) $this->db->trans_start();

		if (!isset($_POST['flexible_shift'])){
			$_POST['flexible_shift'] = 0;
		}

		if ($this->input->post('module_id')) {
			if($this->user_access[$this->input->post('module_id')]['edit'] === 1 && $this->user_access[$this->input->post('module_id')]['add'] === 1){

				$this->user_access[$this->module_id]['edit'] = 1;
				$this->user_access[$this->module_id]['add'] = 1;
			}
		}
		
		if ( !$this->user_access[$this->module_id]['add'] && $this->input->post('record_id') != $this->userinfo['user_id'] ) {
			show_error('The action you have requested is not allowed.');		
		}

		
		if ($this->input->post('record_id') != '-1'){
			$this->db->where('user_id',$this->input->post('record_id'));
			$result = $this->db->get('user');
			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				if ($this->input->post('role_id') != $row->role_id){
					$this->_delete_nav_and_access();
					$this->_set_admin_access();		
				}
			}
		}

		if ($this->input->post('record_id') == '-1' && $this->input->post('id_number') && $this->input->post('biometric_id'))
		{
			$ids = array();

			$user_check = $this->db->get_where('user', array('login' => $this->input->post('id_number')));

			if( $user_check->num_rows() > 0)
				$ids[] = 'ID NUMBER';

			$biometric_check = $this->db->get_where('employee', array('biometric_id' => $this->input->post('biometric_id')));

			if($biometric_check->num_rows() > 0)
				$ids[] = 'BIOMETRIC ID';	
		

			if ($this->input->post('previous_record_id')) 
			{
				$prev_user = $user_check->row()->employee_id;
				$prev_biometric_id = $biometric_check->row()->employee_id;

				$prev_login = $user_check->row()->login;
				$prev_idnum = $biometric_check->row()->id_number;
				$prev_biometric = $biometric_check->row()->biometric_id;

				if ($prev_user == $this->input->post('previous_record_id') && $prev_biometric_id == $this->input->post('previous_record_id')) 
				{
					$this->db->where('user_id',$this->input->post('previous_record_id'));
					$this->db->update('user',array("login" => $this->input->post('previous_record_id') .'-'. $prev_login));

					$this->db->where('employee_id', $this->input->post('previous_record_id'));
					$this->db->update('employee',array("biometric_id" => $prev_biometric .'-'. $this->input->post('previous_record_id'),"id_number" => $prev_idnum  .'-'. $this->input->post('previous_record_id')));

					$ids = array();
				}

			}

			if(count($ids) > 0)
			{
				$data['msg'] = implode(' And ', $ids).' already used';
				$data['msg_type'] = 'error';
				$data['json'] = $data;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
				return;
			}
		}
		if ($this->input->post('education')) {
			foreach ($_POST['education']['date_from'] as $key => $date) {
				if (strtotime($_POST['education']['date_to'][$key]) < strtotime($date)) {
					$data['msg'] = 'Education : Invalid date range.';
					$data['msg_type'] = 'error';
					$data['json'] = $data;
					$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
					return;
				}
			}
		}

		if ($bypass_parent) {	
			parent::ajax_save();
			return;
		}

		if( $this->input->post('employee_type_id') ) $_POST['employee_type'] = $this->input->post('employee_type_id'); 
		if( $this->input->post('job_level_id') ) $_POST['job_level'] = $this->input->post('job_level_id'); 
		if( $this->input->post('range_of_rank_id') ) $_POST['range_of_rank'] = $this->input->post('range_of_rank_id'); 
		if( $this->input->post('region') ) $_POST['region_id'] = $this->input->post('region'); 

		if( $this->input->post('direct_subordinates') ){
			$direct_subordinates = implode(',', $_POST['direct_subordinates'] );
			$_POST['direct_subordinates'] = $direct_subordinates;
		}
		else{
			$_POST['direct_subordinates'] = '';
		}

		if( $this->input->post('department_id') ){
			$department_id = implode(',', $_POST['department_id'] );
			$_POST['department_id'] = $department_id;
		}

		if( $this->input->post('division_id') ){
			$division_id = implode(',', $_POST['division_id'] );
			$_POST['division_id'] = $division_id;
		}

		if( $this->input->post('reporting_to') ){
			$reporting_to = implode(',', $_POST['reporting_to'] );
			$_POST['reporting_to'] = $reporting_to;
		}
		else{
			$_POST['reporting_to'] = '';	
		}

		if( $this->input->post('segment_1_id') ){
			$segment_1_id = implode(',', $_POST['segment_1_id'] );
			$_POST['segment_1_id'] = $segment_1_id;
		}

		if( $this->input->post('segment_2_id') ){
			$segment_2_id = implode(',', $_POST['segment_2_id'] );
			$_POST['segment_2_id'] = $segment_2_id;
		}
		else{
			$_POST['segment_2_id'] = '';	
		}


		$this->related_table = array(
			'employee' => 'employee_id',
			'employee_dtr_setup' => 'employee_id'
		);

		parent::ajax_save();

		if ($this->input->post('record_id') == '-1' && isset($this->key_field_val))
		{
			$this->db->where('user_id', $this->key_field_val);
			$this->db->update('user', array('login' => $this->input->post('id_number')));
		}

		$check_elb = $this->db->get_where('employee_leave_balance',array('employee_id' => $this->key_field_val, 'year' => date('Y')));

		if( $check_elb->num_rows() == 0){
			if( $this->input->post('status_id') == 1 ){

				// Add leave credits, use leave setup based on employee type.
				$this->db->select('et.*, ef.application_code');
				$this->db->from('employee_type_leave_setup et');
				$this->db->join('employee_form_type ef', 'ef.application_form_id = et.application_form_id');
				$this->db->where('employee_type_id', $this->input->post('employee_type_id'));
				$this->db->where('ef.deleted', 0);
				$this->db->where('et.deleted', 0);
				
				$leave_setup = $this->db->get();

				$employed_date = $this->input->post('employed_date');
				// $employed_date_year = date('Y', strtotime($employed_date));

				$date_diff = gregoriantojd(12, 31, date('Y', strtotime($employed_date))) - gregoriantojd(date('m', strtotime($employed_date)), date('d', strtotime($employed_date)), date('Y', strtotime($employed_date)));

				if ($leave_setup->num_rows() > 0) {
					$data['year'] = date('Y');

					if (CLIENT_DIR == "pioneer" && $this->input->post('employee_type_id') != 1) {
						$data['year'] = date('Y')+1;
					}

					foreach ($leave_setup->result() as $leave_type) {					
						if (!$leave_type->prorated) {
							$data[strtolower($leave_type->application_code)] = $leave_type->base;
						} else {
							// Formula for pro-rated
							$monthly = $leave_type->base / 365;
							$days_remaining = $monthly * $date_diff;
							$credits = round($days_remaining * 2) / 2;
							$data[strtolower($leave_type->application_code)] = $credits;
						}
					}
					$data['employee_id'] = $this->input->post('employee_id');
					$this->db->insert('employee_leave_balance', $data);
				}

				$data['employee_id'] = $this->key_field_val;

				$this->db->insert('employee_leave_balance', $data);	
			} else {
				$data['year'] = date('Y');
				$data['employee_id'] = $this->key_field_val;			
				$this->db->insert('employee_leave_balance', $data, array('employee_id' => $this->key_field_val));
			}
		} 

		// set true client request. ticket 336
		$_POST['with_job_description'] = 1;
		if( $this->input->post('with_job_description') != 1 ){

			$this->db->update('employee', array('job_level' => NULL, 'range_of_rank' => NULL, 'rank_code' => NULL,), array($this->key_field => $this->key_field_val));

		} 		

		//  save leave_accumulation_start_date even return value is NULL
		if( !$this->input->post('leave_accumulation_start_date') && $this->input->post('record_id') != '-1' ){

			$leave_accumulation_start_date = $this->input->post('leave_accumulation_start_date');
			$this->db->update('employee', array('leave_accumulation_start_date' => $leave_accumulation_start_date), array($this->key_field => $this->key_field_val));

		}

		//save last series no of employee id config
		if ($this->input->post('last_series_no')){
			$this->db->update('employee_id_number_format',array("id_number_format_series"=>$this->input->post('last_series_no')));
		}


		if($trans_started == false) 
		{
			$this->db->trans_complete() ;

			if ($this->db->trans_status() === FALSE)
			{
				$response->msg_type = 'error';
				$response->msg = "Unexpected database error occured.";
				if( $this->input->post('page_refresh') ) $response->page_refresh = "true";
				parent::set_message($response);		
				parent::after_ajax_save();		
			}else{
				$response = $this->get_message();
				if( $this->input->post('page_refresh') ) $response->page_refresh = "true";
				parent::set_message($response);		
				parent::after_ajax_save();	
			}
		}

	}
	
	/**
	 * Reset Access of admin
	 */
	function _set_admin_access()
	{
		$module_access = array();
		
		//get list of modules
		$this->db->order_by('parent_id, sequence');
		$this->db->select('module_id, short_name');
		$modulelist = $this->db->get('module')->result_array();
		
		// get list of module actions
		$this->db->order_by('id');
		$actionlist = $this->db->get('module_action')->result_array();
		foreach($modulelist as $index => $module )
		{
			$module_access[$module['module_id']] = array();
			foreach($actionlist as $index => $action)
			{
				$module_access[$module['module_id']][$action['action']] = 1;
			}
		}
		
		$module_access = serialize($module_access);
		$this->db->where('profile_id', '1');
		$this->db->update('profile', array('module_access' => $module_access));
	}

	function _delete_nav_and_access()
	{
		//delete all Access files to refresh access 
		$this->load->helper('file');
		$app_directories =  $this->hdicore->_get_config('app_directories');
		$user_nav_files = get_dir_file_info($app_directories['user_settings_dir']);
		foreach($user_nav_files as $filename => $file_detail)
		{
			unlink($app_directories['user_settings_dir'].$filename);
		}
	}

	function delete() {
		if ($this->input->post('applicant_id') && (trim($this->input->post('record_id')) == '')) {
			$this->db->where('applicant_id', $this->input->post('applicant_id'));
			$result = $this->db->get($this->module_table);
			if ($result && $result->num_rows() > 0) {
				$row = $result->row_array();
				$_POST['record_id'] = $row[$this->key_field];
			} else {
				$_POST['record_id'] = '-1';
			}
		}

		$this->db->where('deleted',0);		
		$this->db->where('employee_id',$this->input->post('record_id'));
		$result = $this->db->get('user');

		parent::delete();

		// $this->db->where('employee_id', $this->input->post('record_id'));
		// $this->db->delete('employee');	
		$this->db->update('user', array('deleted' => 1), array('employee_id' => $this->input->post('record_id')));

		$this->db->update('employee', array('deleted' => 1), array('employee_id' => $this->input->post('record_id')));

		if ($result && $result->num_rows() > 0){
			$userinfo = $result->row();

			$this->db->where('user_id',$userinfo->user_id);
			$this->db->update('user',array("login" => $userinfo->user_id .'-'. $userinfo->login));

			$result = $this->db->get_where('employee', array('employee_id' => $this->input->post('record_id')));
			if ($result && $result->num_rows() > 0){
				$employee = $result->row();
				$this->db->where('employee_id',$employee->employee_id);
				$this->db->update('employee',array("biometric_id" => $employee->biometric_id .'-'. $employee->employee_id,"id_number" => $employee->id_number .'-'. $employee->employee_id));
			}	
		}		
	}

	// END - default module functions
	// START custom module funtions
	function _set_left_join()
	{
		parent::_set_left_join();
		$this->db->join('user_position', 'user_position.position_id='. $this->module_table .'.position_id','left');
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

		if ($this->input->post('searchField') == 'all'){
			$search_string[] = $this->db->dbprefix . 'user_position.position LIKE "%'.$value.'%"';
		}

		$search_string = '('. implode(' OR ', $search_string) .')';

		return $search_string;
	}

/*	function _set_filter() {
		if ($this->input->post('searchField') == 'all'){
			$this->db->or_where('user_position.position LIKE', '%'.$this->input->post('searchString').'%');
		}
	}	*/

	function _set_specific_search_query()
	{
		$field = $this->input->post('searchField');
		$operator =  $this->input->post('searchOper');
		$value =  $this->input->post('searchString');

		if ($this->input->post('searchField') == 'user.position_id'){
			$field = ''.$this->db->dbprefix.'user_position.position';
		}

		foreach( $this->search_columns as $search )
		{
			if($search['jq_index'] == $field) $field = $search['column'];
		}

		$field = strtolower( $field );
		if(sizeof(explode(' as ', $field)) > 1){
			$as_part = explode(' as ', $field);
			$field = strtolower( trim( $as_part[0] ) );
		}

		switch ($operator) {
			case 'eq':
				return $field . ' = "'.$value.'"';
				break;
			case 'ne':
				return $field . ' != "'.$value.'"';
				break;
			case 'lt':
				return $field . ' < "'.$value.'"';
				break;
			case 'le':
				return $field . ' <= "'.$value.'"';
				break;
			case 'gt':
				return $field . ' > "'.$value.'"';
				break;
			case 'ge':
				return $field . ' >= "'.$value.'"';
				break;
			case 'bw':
				return $field . ' REGEXP "^'. $value .'"';
				break;
			case 'bn':
				return $field . ' NOT REGEXP "^'. $value .'"';
				break;
			case 'in':
				return $field . ' IN ('. $value .')';
				break;
			case 'ni':
				return $field . ' NOT IN ('. $value .')';
				break;
			case 'ew':
				return $field . ' LIKE "%'. $value  .'"';
				break;
			case 'en':
				return $field . ' NOT LIKE "%'. $value  .'"';
				break;
			case 'cn':
				return $field . ' LIKE "%'. $value .'%"';
				break;
			case 'nc':
				return $field . ' NOT LIKE "%'. $value .'%"';
				break;
			default:
				return $field . ' LIKE %'. $value .'%';
		}
	}

	protected function after_ajax_save() {

		if ($this->input->post('sex')){
			if ($this->input->post('sex') == 'male'){
				$salutation = 'Mr.';
			}
			else{
				$salutation = 'Miss';
			}
			$this->db->update('user', array('salutation' => $salutation), array( 'employee_id' => $this->key_field_val) );
		}

		if ($this->input->post('record_id') == '-1' && isset( $this->key_field_val )){
			$this->db->where('user_id', $this->key_field_val);
			$this->db->update('user', array('employee_id' => $this->key_field_val, 'login' => $this->input->post('id_number'), 'applicant_id' => $this->input->post('applicant_id')));
			
			$employed_date = $this->input->post('employed_date');
			if(empty($employed_date)){
				$this->db->update('employee', array('user_id' => $this->key_field_val, 'applicant_id' => $this->input->post('applicant_id'), 'employed_date' => date('Y-m-d')), array( 'employee_id' => $this->key_field_val) );
			}

			if (CLIENT_DIR == "firstbalfour"){
				if($this->input->post('birth_date') != ''){
					$password = md5(date('mdY',strtotime($this->input->post('birth_date'))));
					$this->db->update('user', array('password' => $password), array( 'employee_id' => $this->key_field_val) );
				}			
			}						
		}

		if (trim($this->input->post('aux')) != ''){
			if (trim($this->input->post('aux')) != '0'){
				$this->db->where('user_id', $this->key_field_val);
				$this->db->update('user', array('aux' => $this->input->post('aux')));
			}
			else{
				$this->db->where('user_id', $this->key_field_val);
				$this->db->update('user', array('aux' => ''));				
			}
		}

		if (CLIENT_DIR == "oams"){
			if ($this->input->post('record_id') != '-1' && $this->input->post('applicant_id') != '0'){
				$this->db->where('employee_id', $this->key_field_val);
				$this->db->update('employee', array('applicant_id' => $this->input->post('applicant_id')));				
			}		
		}

		if (isset($this->key_field_val) && $this->key_field_val > 0) {
			$employee_id = $this->key_field_val;

			// START.
			
			//department
			$this->db->delete('employee_department', array('employee_id' => $this->key_field_val));
			$deps = $this->input->post('department_id');
			if(!empty( $deps )){
				$deps = explode(',', $deps);
				foreach($deps as $dep_id){
					$this->db->insert('employee_department', array('employee_id' => $this->key_field_val, 'department_id' => $dep_id));
				}
			}
			
			//division
			$this->db->delete('employee_division', array('employee_id' => $this->key_field_val));
			$divs = $this->input->post('division_id');
			if(!empty( $divs )){
				$divs = explode(',', $divs);
				foreach($divs as $div_id){
					$this->db->insert('employee_division', array('employee_id' => $this->key_field_val, 'division_id' => $div_id));
				}
			}
			
			
			//reporting to
			$this->db->delete('employee_reporting_to', array('employee_id' => $this->key_field_val));
			$reps = $this->input->post('reporting_to');
			if(!empty( $reps )){
				$reps = explode(',', $reps);
				foreach($reps as $rep_id){
					$this->db->insert('employee_reporting_to', array('employee_id' => $this->key_field_val, 'reporting_to' => $rep_id));
				}
			}
			
			//segment 1
			$this->db->delete('employee_segment_1', array('employee_id' => $this->key_field_val));
			$seg1s = $this->input->post('segment_1_id');
			if(!empty( $seg1s )){
				$seg1s = explode(',', $seg1s);
				foreach($seg1s as $seg1_id){
					$this->db->insert('employee_segment_1', array('employee_id' => $this->key_field_val, 'segment_1_id' => $seg1_id));
				}
			}
			
			//segment 2
			$this->db->delete('employee_segment_2', array('employee_id' => $this->key_field_val));
			$seg2s = $this->input->post('segment_2_id');
			if(!empty( $seg2s )){
				$seg2s = explode(',', $seg2s);
				foreach($seg2s as $seg2_id){
					$this->db->insert('employee_segment_2', array('employee_id' => $this->key_field_val, 'segment_2_id' => $seg2_id));
				}
			}

			//employee work assignment
/*			if (CLIENT_DIR == 'firstbalfour'){	
				$ctr = 0;
				foreach ($_POST['work_assignment']['assignment'] as $key => $value) {
					$_POST['work_assignment']['assignment'][$ctr] = $value;
					$ctr++;
				}
				ksort($_POST['work_assignment']['assignment']);
				$data = $this->_rebuild_array($this->input->post('work_assignment'), $employee_id);
				if (count($data) > 0){
					$this->db->insert_batch('employee_work_assignment', $data);	
					$num = 0;			
					foreach ($data as $value) {
						if ($value['assignment'] == 1){
							$array_info = array( 
												'division_id' => $value['division_id'], 
												'department_id' => $value['department_id'],
												'project_name_id' => $value['project_name_id'], 
												'group_name_id' => $value['group_name_id'], 
											);

							$this->db->where('user_id', $this->key_field_val);
							$this->db->update('user',$array_info);						
						}	
						$num++;
					}				
				}
			}*/
			
			$ctr = 1000;
			foreach ($_POST['education']['graduate'] as $key => $value) {
				$_POST['education']['graduate'][$ctr] = $value;
				unset($_POST['education']['graduate'][$key]);
				$ctr++;
			}	

			$ctr = 0;
			foreach ($_POST['education']['graduate'] as $key => $value) {
				$_POST['education']['graduate'][$ctr] = $value;
				unset($_POST['education']['graduate'][$key]);
				$ctr++;
			}
		
			//end employee work assignment		

			// Process other details.
			foreach ($this->_detail_types as $detail) {
				$table = 'employee_' . $detail;

				if ($this->db->table_exists($table)) {
					if ($detail == 'employment'){
						$err_cnt = $this->vei_dates($post);

						if ($err_cnt < 1){		
							$this->db->delete($table, array('employee_id' => $employee_id));
						}
					}	
					elseif( $detail == 'training' && CLIENT_DIR == 'firstbalfour' ){

						$this->db->delete($table, array('employee_id' => $employee_id));

					}
					else{				
						$this->db->delete($table, array('employee_id' => $employee_id));
					}

					$post = $this->input->post($detail);

					if (!is_null($post) && is_array($post)) {
						// Handle the dates.
						foreach ($post as $key => $value) {
							$key_string_segments = explode('_', $key);

							if ($detail == 'education'){		

								if ((in_array(end($key_string_segments), array('from', 'to'))) || end($key_string_segments) == 'date'){
									foreach ($post[$key] as &$date){

										if($date != "" && $date != "1970-01-01" && $date != "0000-00-00" && $date !== "January 1970"){

											if (CLIENT_DIR == 'firstbalfour'){
												$date = $date . '-01-01';
											}
											else{
												$date = date('Y-m-d', strtotime($date));
											}

										}else{
											$date = 'NULL';
										}	

									}
								}
							}

							if ( $detail == 'affiliates' && ( $key == 'date_joined' || $key == 'date_resigned' ) ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										if (CLIENT_DIR == 'firstbalfour'){
											$date = $date . '-01-01';											
										}
										else{
											$date = date('Y-m-d', strtotime($date));
										}
									}else{
										$date = $date;
									}
								}
							}	

							if ( $detail == 'test_profile' && $key == 'date_taken' ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = $date;
									}
								}
							}

							if ( $detail == 'family' && $key == 'birth_date' ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = $date;
									}
								}
							}

							if ( $detail == 'accountabilities' && ( $key == 'date_issued' || $key == 'date_returned') ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = $date;
									}
								}
							}

/*							if ( $detail == 'work_assignment' && ( $key == 'start_date' || $key == 'end_date') ) {
								foreach ($post[$key] as &$date){
									if($date != "" && $date != "1970-01-01" && $date != "0000-00-00"){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = 'NULL';
									}
								}
							}*/

							if ( $detail == 'training' && ( $key == 'from_date' || $key == 'to_date') ) {
								foreach ($post[$key] as &$date){
									if($date != "" && $date != "1970-01-01" && $date != "0000-00-00"){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = 'NULL';
									}
								}
							}

							if ($detail == 'employment'){		
								if ((in_array(reset($key_string_segments), array('from', 'to'))) || reset($key_string_segments) == 'date'){
									foreach ($post[$key] as &$date){
										if($date != "" && $date != "1970-01-01" && $date != "0000-00-00" && $date !== "January 1970"){
											$date = date('Y-m-d', strtotime($date));
										}else{
											$date = 'NULL';
										}	

									}
								}
							}

						}

						if ($this->config->item('tbx_dropdown') == 1){
							if ( $detail == 'family' ) {
								for ($i=0; $i < count($post['name']); $i++) {
									if (count($post['family_benefit_id'][$i]) > 0){
										$post['family_benefit_id'][$i] = implode(',', $post['family_benefit_id'][$i]);
									} 
									else{
										$post['family_benefit_id'][$i] = 0;
									}
								}
								ksort($post['family_benefit_id']);	


								// foreach ($post['family_benefit_id'] as $key => $value) {							
								// 	if (count($value) > 0){
								// 		$post['family_benefit_id'][$ctr] = implode(',', $value);
								// 	}
								// 	else{
								// 		$post['family_benefit_id'][$ctr] = 0;
								// 	}
								// 	$ctr++;
								// }
								// ksort($post['family_benefit_id']);							

/*								if ($post['family_benefit_id'] == ''){
									unset($post['family_benefit_id']);
								}*/
							}								

/*							if ( $detail == 'education' ) {
								$ctr = 0;
								foreach ($post['education_school_id'] as $key => $value) {
									$post['education_school_id'][$ctr] = implode(',', $value);
									$ctr++;
								}
								ksort($post['education_school_id']);															
							}*/
						}

						// tirso - validate inclusive dates for employment history.
						$err_cnt = 0;
						if ($detail == 'employment'){
							$err_cnt = $this->vei_dates($post);

							if ($err_cnt > 0){		
								$response->msg 		= 'Invalid Inclusive Dates.';
								$response->msg_type = 'error';

								$this->set_message($response);
							}
						}
						if ($err_cnt == 0){	// tirso - added this line.
							$data = $this->_rebuild_array($post, $employee_id);
							if (count($data) > 0){			
								$this->db->insert_batch($table, $data);
							}
						}
					}
				}

			}


/*			if (CLIENT_DIR == 'firstbalfour'){
				$this->db->where('assignment',1);
				$this->db->where('employee_id',$employee_id);
				$this->db->update('employee_work_assignment',array("end_date"=>date('Y-m-d',strtotime($this->input->post('end_date')))));			
			}*/
		}

/*		$data = $this->_rebuild_array($_POST['characteristic'], $employee_id);
		$this->db->insert_batch('employee_characteristic', $data);	*/	

		$image_config = array();
		// Resize image if a new one is submitted.		
		if (file_exists($this->input->post('photo'))) {
			$this->load->library('image_lib');

			$orig_path    = explode('/', $this->input->post('photo'));
			$orig_path[0] .= '/thumbs';
			$thumb_path   = implode('/', $orig_path);

			unset($orig_path[count($orig_path) - 1]);
			$thumb_dir = implode('/', $orig_path);			

			$image_config['source_image']   = $this->input->post('photo');
			$image_config['create_thumb']   = TRUE;
			$image_config['maintain_ratio'] = TRUE;
			$image_config['thumb_marker']   = '';
			$image_config['new_image']      = $thumb_path;
			$image_config['width']          = 50;
			$image_config['height']         = 50;
		}

		if (count($image_config) > 0) {
			if (!is_dir($thumb_dir)) {
				if (!mkdir($thumb_dir, 0755, true)) {
				$response->msg 		= 'Could not create directory. DIR:' . $thumb_dir;
				$response->msg_type = 'attention';
				}
			}

			$this->image_lib->initialize($image_config);

			if (!$this->image_lib->resize()) {
				// How to handle error?
				$response->msg 		= $this->image_lib->display_errors();
				$response->msg_type = 'attention';

				$this->set_message($response);
			}
		}

		if ($this->input->post('record_id') == '-1' && $this->get_msg_type() == 'success') {
			if( $this->input->post('applicant_id') ){
				$this->db->where('applicant_id', $this->input->post('applicant_id'));
				$this->db->update('recruitment_manpower_candidate', 
					array('candidate_status_id' => 6,'hired_date' => date('Y-m-d'))				
					);
				
				$this->db->where('applicant_id', $this->input->post('applicant_id'));
				$this->db->update('recruitment_applicant', 
					array('application_status_id' => '4')
					);

				// Set manpower request status to closed.
				$this->db->where('applicant_id', $this->input->post('applicant_id'));
				$this->db->where('candidate_status_id', 6);
				$this->db->where('deleted', 0);

				$result = $this->db->get('recruitment_manpower_candidate');
				$candidate = $result->row();
				
				$this->db->select(array('mrf_id', 'COUNT(candidate_id) AS hired', 'number_required'));
				$this->db->from('recruitment_manpower mrf');
				$this->db->join('recruitment_manpower_candidate c', 'c.mrf_id = mrf.request_id', 'left');
				$this->db->where('mrf.deleted', 0);
				$this->db->where('c.deleted', 0);
				$this->db->where('c.candidate_status_id', 6);
				$this->db->where('mrf.request_id', $candidate->mrf_id);
				$this->db->group_by('c.mrf_id');
				
				$manpower_hired_total = $this->db->get();

				$this->db->update('recruitment_applicant_application', array('status' => 4 ), array('applicant_id' => $this->input->post('applicant_id'), 'mrf_id' => $candidate->mrf_id, 'lstatus' => 0 ));
				
				if ($manpower_hired_total->num_rows() > 0) {
					$total = $manpower_hired_total->row();
					if ($total->hired >= $total->number_required) {
						$this->db->where('request_id', $total->mrf_id);
						$this->db->update('recruitment_manpower', array('status' => 'Closed', 'date_closed' => date('Y-m-d')));

						if (CLIENT_DIR == 'firstbalfour' || CLIENT_DIR == "oams"){
    						$qry = "UPDATE {$this->db->dbprefix}recruitment_applicant ra 
    									JOIN {$this->db->dbprefix}recruitment_manpower_candidate rmc
    									ON ra.applicant_id = rmc.applicant_id
    									SET ra.application_status_id = 5, rmc.deleted = 1
    									WHERE mrf_id = ".$total->mrf_id."
    									AND candidate_status_id <> 6";
    						$this->db->query($qry);
						}						
					}
				}	
							
				$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $this->input->post('applicant_id')))->row();

				$employee_fields = $this->db->list_fields('employee');
				$save_field = array();
				foreach ( $employee_fields as $emp_field ){
					if( !$this->input->post( $emp_field ) ) $save_field[] = $emp_field;
				}
				$applicant_fields = $this->db->list_fields('recruitment_applicant');
				$select = array_intersect($save_field, $applicant_fields);

				$this->db->select($select);
				$this->db->where('applicant_id', $applicant->applicant_id);
				$applicant_details = $this->db->get('recruitment_applicant')->row_array();

				$this->db->update('employee', $applicant_details, array( 'employee_id' => $this->key_field_val));

				//get family
				$family_fields = $this->db->list_fields('employee_family');
				$members = $this->db->get_where('recruitment_applicant_family', array('applicant_id' => $applicant->applicant_id));
				if( $members->num_rows() > 0 ){
					foreach($members->result() as $member){
						$memberdetail = array();
						foreach( $family_fields as $field ){
							$memberdetail[$field] = isset( $member->$field ) ? $member->$field : '';
						}
						$memberdetail['employee_id'] = $this->key_field_val;
						unset($memberdetail['record_id']);
						$this->db->insert('employee_family', $memberdetail);
					}
				}

				//get education
				$educ_fields = $this->db->list_fields('employee_education');
				$education = $this->db->get_where('recruitment_applicant_education', array('applicant_id' => $applicant->applicant_id));
				if( $education->num_rows() > 0 ){
					foreach($education->result() as $educ){
						$educdetail = array();
						foreach( $educ_fields as $field ){
							$educdetail[$field] = isset( $educ->$field ) ? $educ->$field : '';
						}
						$educdetail['employee_id'] = $this->key_field_val;
						unset($educdetail['education_id']);
						$this->db->insert('employee_education', $educdetail);
					}
				}

				//get training
				$training_fields = $this->db->list_fields('employee_training');
				$trainings = $this->db->get_where('recruitment_applicant_training', array('applicant_id' => $applicant->applicant_id));
				if( $trainings->num_rows() > 0 ){
					foreach($trainings->result() as $training){
						$trainingdetail = array();
						foreach( $training_fields as $field ){
							$trainingdetail[$field] = isset( $training->$field ) ? $training->$field : '';
						}
						$trainingdetail['employee_id'] = $this->key_field_val;
						unset($trainingdetail['training_id']);
						$this->db->insert('employee_training', $trainingdetail);
					}
				}

				//get employment history
				$history_fields = $this->db->list_fields('employee_employment');
				$history = $this->db->get_where('recruitment_applicant_employment', array('applicant_id' => $applicant->applicant_id));
				if( $history->num_rows() > 0 ){
					foreach($history->result() as $employment){
						$employmentdetail = array();
						foreach( $history_fields as $field ){
							$employmentdetail[$field] = isset( $employment->$field ) ? $employment->$field : '';
						}
						$employmentdetail['employee_id'] = $this->key_field_val;
						unset($employmentdetail['record_id']);
						$this->db->insert('employee_employment', $employmentdetail);
					}
				}

				//get character reference
				$reference_fields = $this->db->list_fields('employee_references');
				$references = $this->db->get_where('recruitment_applicant_references', array('applicant_id' => $applicant->applicant_id));
				if( $references->num_rows() > 0 ){
					foreach($references->result() as $reference){
						$referencesdetail = array();
						foreach( $reference_fields as $field ){
							$referencesdetail[$field] = isset( $reference->$field ) ? $reference->$field : '';
						}
						$referencesdetail['employee_id'] = $this->key_field_val;
						unset($referencesdetail['record_id']); 
						unset($employmentdetail['record_id']);
						// dbug($referencesdetail);die();
						$this->db->insert('employee_references', $referencesdetail);
					}
				}

				//get skills //should not copy as per ticket 1013.
/*				$skills_fields = $this->db->list_fields('employee_skill');
				$skills = $this->db->get_where('recruitment_applicant_skill', array('applicant_id' => $applicant->applicant_id));
				if( $skills->num_rows() > 0 ){
					foreach($skills->result() as $skill){
						$skilldetail = array();
						foreach( $skills_fields as $field ){
							$skilldetail[$field] = isset( $skill->$field ) ? $skill->$field : '';
						}
						$skilldetail['employee_id'] = $this->key_field_val;
						unset($skilldetail['record_id']);
						$this->db->insert('employee_skill', $skilldetail);
					}
				}*/

				//get test profile
				$test_profiles_fields = $this->db->list_fields('employee_test_profile');
				$test_profiles = $this->db->get_where('recruitment_applicant_test_profile', array('applicant_id' => $applicant->applicant_id));
				if( $test_profiles->num_rows() > 0 ){
					foreach($test_profiles->result() as $test_profile){
						$test_profiledetail = array();
						foreach( $test_profiles_fields as $field ){
							$test_profiledetail[$field] = isset( $test_profile->$field ) ? $test_profile->$field : '';
						}
						$test_profiledetail['employee_id'] = $this->key_field_val;
						unset($test_profiledetail['record_id']);
						unset($test_profiledetail['employee_test_profile_id']);
						$this->db->insert('employee_test_profile', $test_profiledetail);
					}
				}

				//get affiliates
				$affiliatess_fields = $this->db->list_fields('employee_affiliates');
				$affiliatess = $this->db->get_where('recruitment_applicant_affiliates', array('applicant_id' => $applicant->applicant_id));
				if( $affiliatess->num_rows() > 0 ){
					foreach($affiliatess->result() as $affiliates){
						$affiliatesdetail = array();
						foreach( $affiliatess_fields as $field ){
							$affiliatesdetail[$field] = isset( $affiliates->$field ) ? $affiliates->$field : '';
						}
						$affiliatesdetail['employee_id'] = $this->key_field_val;
						unset($affiliatesdetail['record_id']);
						$this->db->insert('employee_affiliates', $affiliatesdetail);
					}
				}

				/*
				//get affiliates
				$family_fields = $this->db->list_fields('employee_affiliates');
				$members = $this->db->get_where('recruitment_applicant_affiliates', array('applicant_id' => $applicant->applicant_id));

				
				if( $members->num_rows() > 0 ){
					foreach($members->result() as $member){
						$memberdetail = array();
						foreach( $family_fields as $field ){
							$memberdetail[$field] = isset( $member->$field ) ? $member->$field : '';
						}
						$memberdetail['employee_id'] = $this->key_field_val;
						unset($memberdetail['record_id']);
						$this->db->insert('employee_affiliates', $memberdetail);
					}
				}
				*/

				$this->db->where('applicant_id', $this->input->post('applicant_id'));
				$this->db->join('recruitment_candidate_job_offer','recruitment_manpower_candidate.candidate_id = recruitment_candidate_job_offer.candidate_id');
				$result_job_offer = $this->db->get('recruitment_manpower_candidate');

				if ($result_job_offer && $result_job_offer->num_rows() > 0){
					$result_job_offer_row = $result_job_offer->row();
					$status_id = $result_job_offer_row->recruitment_candidate_job_offer_type_id;

					$this->db->update('employee', array('status_id' => $status_id), array( 'employee_id' => $this->key_field_val) );
				}

				//other data
				$others = array(
					'date_of_marriage' => $applicant->date_of_marriage,
					'photo' => $applicant->photo,
					'aux' => $this->input->post('aux'),
					'middleinitial' => $applicant->middleinitial,
				);

				$this->db->update('user', $others, array( 'user_id' => $this->key_field_val));

				//update preemployment set has 201 to yes
				$this->db->update('recruitment_preemployment', array('has_201' => 1), array('candidate_id' => $candidate->candidate_id));

				//get pre-emplyment medical check form scheduler and save in employee_health
				$info = array(
						'employee_id'=>$this->key_field_val,
						'health_type'=>1,
						'health_type_status_id'=>2
					);

				$this->db->join('recruitment_manpower_candidates_scheduler','recruitment_manpower_candidate.candidate_id = recruitment_manpower_candidates_scheduler.candidate_id');
				$this->db->where('applicant_id',$this->input->post('applicant_id'));
				$result = $this->db->get('recruitment_manpower_candidate');

				//if medical exam in pre employment already done.
				if ($result && $result->num_rows() > 0){
					$this->db->insert('employee_health',$info);
				}

			}

			//insert memo for new Employee
			$memo = array(
				'memo_type_id' => NEW_EMPLOYEE_MEMO_TYPE,
				'memo_title' => 'New Employee: '.$applicant->firstname.' '.$applicant->lastname,
				'employee_id' => $this->key_field_val,
				'publish_from' => date('Y-m-d'),
				'publish_to' => date('Y-m-d', strtotime('+1 week', strtotime( date('Y-m-d') ) ) ),
				'created_by' => $this->user->user_id,
				'modified_by' => $this->user->user_id,			
			);
			$this->db->insert('memo', $memo);
			
			//insert into employee payrol
			$this->db->insert('employee_payroll', array('employee_id' => $this->key_field_val, 'salary' => $this->encrypt->encode(str_replace(',', '', $this->input->post('salary')))));
		}

		if ($this->input->post('salary') && ($this->input->post('record_id') !== '-1')) {
			$this->db->where('employee_id',$this->key_field_val);
			$this->db->update('employee_payroll',array('salary' => $this->encrypt->encode(str_replace(',', '', $this->input->post('salary')))));	
		}
		$this->db->update('employee', array('user_id' => $this->key_field_val), array( 'employee_id' => $this->key_field_val) );
		
	}

	/**
	 *
	 * Match the columns from applicant to employee table,      
	 * 
	 * @param array $data
	 * @param string $table 
	 * 
	 * @return mixed
	 */
	private function _prepare_select($table) {
		$applicant_table = 'recruitment_applicant_' . $table;
		$employee_table  = 'employee_' . $table;

		$applicant_fields_data = $this->db->field_data($applicant_table);
		$employee_fields       = $this->db->list_fields($employee_table);

		foreach ($applicant_fields_data as $applicant_field_data) {
			// Do not include the table key.
			if ($applicant_field_data->primary_key != 1) {
				$applicant_fields[] = $applicant_field_data->name;
			}
		}

		$values = array();

		return array_intersect($applicant_fields, $employee_fields);
	}

	/**
	 * Rearrange the array to a new array which can be used for insert_batch
	 *
	 * @param array $array
	 * @param int $key
	 *
	 * @return array
	 */
	private function _rebuild_array($array, $fkey = null) {
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
			}

			$index++;
		}

		return $new_array;
	}

	/**
	 * Returns a json encoded array of company positions
	 */
	function get_company_positions() {
		if (IS_AJAX) {
			$company_id = $this->input->post('company_id');

			if ($company_id > 0) {
				//$this->db->where('company_id', $company_id);
				$this->db->order_by('position','asc');
				$result = $this->db->get(self::POSITIONS_TABLE);

				$response = $this->_get_default('position_id');

				if ($result->num_rows() > 0) {
					$response['positions'] = $result->result_array();
				}
			} else {
				$response = array();
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	/**
	 * Returns a json encoded array of company departments
	 */
	function get_company_departments() {
		if (IS_AJAX) {
			$company_id = $this->input->post('company_id');

			if ($company_id > 0) {
				$this->db->where('company_id', $company_id);

				$result   = $this->db->get(self::DEPARTMENT_TABLE);
				$response = $this->_get_default('department_id');

				if ($result->num_rows() > 0) {
					$response['departments'] = $result->result_array();
				}
			} else {
				$response = array();
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	/**
	 * Returns an array of managers.
	 * 
	 * @param int $company_id
	 * @return void
	 */
	function get_managers($company_id = '') {
		if (IS_AJAX) {
			// If admin return a list of managers and supervisors for the company.
			$response = $this->_get_default('manager_id');
			if ( $this->is_admin || $this->is_superadmin ) {
				if ($this->input->post('company_id')) {
					$company_id = $this->input->post('company_id');
				}

				$this->db->where('user.company_id', $company_id);
				$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
				$this->db->join('user_position_level', 'user_position_level.position_level_id = user_position.position_level_id', 'left');
				$this->db->where_in('user_position_level.position_level_id', array(3,4));

				$response['users'] = $this->db->get('user')->result_array();
				$response['code']  = 0;
			} else {
				if ($this->input->post('record_id') < 0) {
					$response['user_id'] = $this->userinfo['user_id'];
					$response['text']    = $this->userinfo['firstname'] . ' ' . $this->userinfo['lastname'];
				} else {
					$response['user_id'] = $response['value'];
				}

				$response['code'] = 1;
			}

			$this->db->where('user_id', $response['value']['raw']);
			
			$result = $this->db->get('user');

			if ($result->num_rows() > 0) {
				$user = $result->row_array();
				$response['text'] = $user['firstname'] . ' ' . $user['lastname'];
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_employee() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('employee.*, user.user_id, user.firstname, user.lastname, aux, position, department, user.position_id, user.department_id, user_company.company');
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->where('user.user_id', $this->input->post('employee_id'));
			$this->db->where('user.deleted', 0);
			$this->db->limit(1);

			$employee = $this->db->get('user');

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'Employee not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->row_array();
			}			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function check_status() 
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			//$this->db->select('employee_update_status_id');
			$this->db->where('employee_update_id', $this->input->post('record_id'));
			$this->db->where('deleted', 0);
			//$this->db->limit(1);
			$employee = $this->db->get('employee_update');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Employee not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->row_array();
			}			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_family() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('name, birth_date, relationship, occupation, employer');
			$this->db->order_by("name", "asc");
			$this->db->order_by("birth_date", "asc");
			$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee_family');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_previous_family() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('name, birthdate, relationship, occupation, employer');
			$this->db->order_by("name", "asc");
			$this->db->order_by("birthdate", "asc");
			$this->db->where('employee_update_id', $this->input->post('record_id'));
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee_update_family');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function show_position() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('employee.*, user.user_id, user.firstname, user.lastname, position, department, user.position_id, user.department_id, user_company.company');
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->where('user.user_id', $this->input->post('employee_id'));
			$this->db->where('user.deleted', 0);
			$this->db->limit(1);

			$employee = $this->db->get('employee_update_family');


			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}


	function get_previousinformation() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('pres_address1, pres_address2, pres_city, pres_province, pres_zipcode, perm_address1, perm_address2, perm_city, perm_province, perm_zipcode');
			//$this->db->order_by("name", "asc");
			//$this->db->order_by("birth_date", "asc");
			$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_editedinfo() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('pres_address1, pres_address2, pres_city, pres_province, pres_zipcode, perm_address1, perm_address2, perm_city, perm_province, perm_zipcode');
			//$this->db->order_by("name", "asc");
			//$this->db->order_by("birth_date", "asc");
			$this->db->where('employee_update_id', $this->input->post('employee_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee_update_family');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_shifts() {
		$this->db->where('deleted', 0);
		$result = $this->db->get('workshift');

		$data['json'] = $result->result_array();
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	private function _get_default($type) {
		$record = $this->_get_record();
		$response['value'] = 0;
		if ($record) {
			$response['value'] = $record[$type];
		}

		return $response;
	}

	private function _get_record($record_id = 0) {
		if ($this->input->post('record_id') > 0 && $record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$values = array();
		$details = $this->_record_detail($record_id);

		$this->load->model('uitype_detail');

		foreach ($details as $fieldgroup) {
			if (isset($fieldgroup['fields']) && count($fieldgroup['fields']) > 0) {
				foreach ($fieldgroup['fields'] as $field) {
					$values[$field['column']]['value'] = $this->uitype_detail->getFieldValue($field);
					$values[$field['column']]['raw']   = $field['value'];
				}
			}
		}

		if (count($values) > 0) {
			return $values;
		} else {
			return false;
		}
	}

	function _get_employee_detail($detail, $record_id = 0) {
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$response = array();

		if ($detail == '' && !in_array($detail, $this->_detail_types)) {
			show_error("Insufficient data supplied.");
		} else {
			$table = 'employee_' . $detail;

			$this->db->where('employee_id', $record_id);

			if ($detail == 'education') {
				if (CLIENT_DIR == "firstbalfour"){
					$this->db->select($table . '.degree,'
						. $table . '.date_from,'
						. $table . '.degree,'
						. $table . '.employee_degree_obtained_id,'
						. $table . '.date_to,'
						. $table . '.date_graduated,'
						. $table . '.course,'
	                    . $table . '.graduate,'
						. $table . '.school,'
						. $table . '.education_school_id,'
						. $table . '.honors_received,'
						. ', do.option_id, do.value as education_level');
					$this->db->join('dropdown_options do', 'do.option_id = ' . $table . '.education_level', 'left');
				}
				else{
					$this->db->select($table . '.degree,'
						. $table . '.date_from,'
						. $table . '.degree,'
						. $table . '.date_to,'
						. $table . '.date_graduated,'
						. $table . '.course,'
	                    . $table . '.graduate,'
						. $table . '.school,'
						. $table . '.education_school_id,'
						. $table . '.honors_received,'
						. ', do.option_id, do.value as education_level');
					$this->db->join('dropdown_options do', 'do.option_id = ' . $table . '.education_level', 'left');
				}
			}

			if($detail == 'affiliates'){
				$this->db->group_by('record_id');
			}

			if( $detail == 'cost_center' ){

				$this->db->join('cost_center','cost_center.cost_center_id = '.$table.'.cost_center_id','left');

			}
			if($detail=='employment')
			{
				$this->db->order_by("from_date", "desc"); 
			}

/*			if($detail=='training')
			{
				$this->db->where($table.'.training_calendar_id');
			}*/


			$result = $this->db->get($table);

			if ($result){
				$response = $result->result_array();
			}
		}

		return $response;
	}

	function get_form($type) {
		if (IS_AJAX) {
			if ($type == '' && !in_array($type, $this->_detail_types)) {
				show_error("Insufficient data supplied.");
			} else {

				$data['count'] = $this->input->post('counter_line');
				$data['rand'] = rand(1000,9999);

				if( $type == 'cost_center' ){

					// get cost center
					$result =  $this->db->get_where('cost_center',array("deleted"=>0));

					$rows_array = array();
					if ($result && $result->num_rows() > 0):
						$rows_array[$row->cost_center_id] = "Select Main Cost Center...";
						foreach ($result->result() as $row) {
							$rows_array[$row->cost_center_id] = $row->cost_center;
						}
					endif;
					
					$data['cost_center_list'] = $rows_array;
					// end cost center

				}
				if($type == 'accountabilities') {
					$result = $this->db->get('employee_clearance_form_checklist');

					$options = array(0=>'Select...');

		            foreach ($result->result() as $row){
		            	$options[$row->employee_clearance_form_checklist_id] = $row->name;
		            }

		            $data['employee_clearance'] = $options;
				}
				$response = $this->load->view($this->userinfo['rtheme'] . '/employees/' . $type . '/form', $data);

				$data['html'] = $response;

				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function quick_edit() {

		$customview = "";
		if ($this->input->post('module_id')) {
			if($this->user_access[$this->input->post('module_id')]['edit'] === 1 && $this->user_access[$this->input->post('module_id')]['add'] === 1){

				$this->user_access[$this->module_id]['edit'] = 1;
				$this->user_access[$this->module_id]['add'] = 1;
			}
		$module = $this->db->get_where('module',array('module_id' =>  $this->input->post('module_id')))->row();
			if ( $module->code == 'preemployment') {
				$customview = "/recruitment";
			}

		}

		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
		$this->load->vars($data);

		if ($this->input->post('applicant_id') != 0) {
			$this->db->where('applicant_id', $this->input->post('applicant_id'));

		}
		elseif ($this->input->post('employee_id') != 0){
			$this->db->where('user_id', $this->input->post('employee_id'));
		}

		$this->db->where('deleted', 0);
		$this->db->limit(1);
		$result = $this->db->get('user');

		if ($result && $result->num_rows() > 0) {
			$row = $result->row_array();
			$_POST['record_id'] = $row['user_id'];
		} else {
			$_POST['record_id'] = '-1';
		}

		parent::quick_edit($customview);
	}

	function print_record($employee_id = 0) {
		if (!$this->user_access[$this->module_id]['print']) {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient access to the requested module. <span class="red">Please contact the System Administrator.</span>.');
			redirect(base_url() . $this->module_link);
		}

		// Get from $_POST when the URI is not present.
		if ($employee_id == 0) {
			$employee_id = $this->input->post('record_id');
		}

		// View file under the folder $this->userinfo['rtheme'] . '/recruitment/employees/' . $detail
		$printview = 'printview';

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'employee_201');

		// Get employee details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($employee_id);
		if ($check_record->exist) {
			$vars = get_record_detail_array($employee_id);
		/*	
			// Get the vars to pass to the template.
			$this->db->select($this->module_table . '.*, employee.*, user_company.company, user_position.position');
			$this->db->where($this->module_table. '.' .$this->key_field, $employee_id);
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user.position_id = user_position.position_id', 'left');
			$this->db->join('user_company', 'user_position.company_id = user_company.company_id', 'left');

			$result = $this->db->get($this->module_table);*/

			if (!$vars || count($vars) == 0) {				
				$this->session->set_flashdata('flashdata', 'No record found.');
				redirect(base_url() . $this->module_link);
			}			

			//load variables to employee view files.
			foreach ($this->_detail_types as $detail) {
				$data[$detail] = $this->_get_employee_detail($detail, $employee_id);
				$vars[$detail] = $this->load->view(
					$this->userinfo['rtheme'] . '/employees/' . $detail . '/' . $printview, $data, true
				);
			}
			
			$vars['age'] = get_age($vars['birth_date']);

			$html = $this->template->prep_message($template['body'], $vars, false);

			// Prepare and output the PDF.
			$this->pdf->addPage();

			$this->pdf->writeHTML($html, true, false, false, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$vars['firstname'] . '_' . $vars['lastname'] . '.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function _default_grid_actions($module_link = "", $container = "", $record = array()) {
		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

		// $this->db->select('employment_status.employment_status, user_company.*, user.*, user_position.*, user_job_level.*, user_job_rank.*, employee.*');
		$this->db->select('employee.id_number, employee.employed_date, employee.regular_date, user_rank.job_rank, user_rank_level.rank_level, user_rank_code.job_rank_code, employment_status.employment_status, employee.quitclaim_received, user.inactive');
		$this->db->where('employee.employee_id',$record['employee_id']);
		$this->db->join('user','employee.employee_id = user.employee_id');
		$this->db->join('user_company','user_company.company_id = user.company_id');
		$this->db->join('user_position','user_position.position_id = user.position_id');
		$this->db->join('user_rank_code','employee.rank_code = user_rank_code.job_rank_code_id','left');
		$this->db->join('user_rank','employee.rank_id = user_rank.job_rank_id','left');
		$this->db->join('user_rank_level','employee.job_level = user_rank_level.rank_level_id','left');
		$this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');

		$employee=$this->db->get('employee')->row_array();

		if (CLIENT_DIR == 'firstbalfour' || CLIENT_DIR == 'hdi'){
			if (!empty($employee)){			
				if ($employee['inactive'] == 1){
					$actions .= '<a class="icon-button icon-16-xgreen-orb" tooltip="Active" href="javascript:void(0)"></a>';
				}
			}
		}

		$actions .= '<a class="icon-button icon-16-document-stack" tooltip="<table><tr><td>Employee ID </td><td>:</td> </td><td>'.$employee['id_number'].'</td></tr><tr><td>Date Hired </td><td>:</td> </td><td>'.($employee['employed_date'] == "" || $employee['employed_date'] == null ? "" : date($this->config->item('display_date_format'), strtotime($employee['employed_date']))).' </td></tr><tr><td>Date of Regularization </td><td>:</td> <td>'.($employee['regular_date'] == "" || $employee['regular_date'] == null ? "" : date($this->config->item('display_date_format'), strtotime($employee['regular_date']))).' </td></tr><tr><td>Rank</td><td>:</td><td>'.$employee['job_rank'].'</td></tr><tr><td> Rank Code <td>:</td> </td><td>'.$employee['job_rank_code'].'</td></tr><tr><td>Job Level</td><td>:</td><td>'.$employee['rank_level'].'</td></tr><tr><td>Employment Status<td>:</td> </td><td>'.$employee['employment_status'].'</td></tr></table>" href="javascript:void(0)"></a>';

		$actions .= '<a class="icon-button icon-16-document-stack" onClick="show_full_movement('.$record['employee_id'].')" href="javascript:void(0)"></a>';

		if($this->input->post('filter') && $this->input->post('filter') == "resigned") {
			$actions .= '<a class="icon-button icon-16-rehire" module_link="' . $module_link . '" tooltip="Re-Hire" href="javascript:void(0)"></a>';
		}

		if ($this->user_access[$this->module_id]['view']) {
			$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
		}

		if ($this->user_access[$this->module_id]['print']) {
			$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if ($this->user_access[$this->module_id]['edit']) {
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if (CLIENT_DIR == 'hdi'){
			if ($employee['inactive'] != 1){
				if ($this->user_access[$this->module_id]['delete']) {
					$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				}				
			}
		}
		else{
			if ($this->user_access[$this->module_id]['delete']) {
				$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}
		}

		if( $employee['quitclaim_received'] != 1 ){

			$this->db->where('employee_id',$record['employee_id']);
			$employee_clearance = $this->db->get('employee_clearance');

			if( $employee_clearance->num_rows() > 0 ){
				$actions .= '<a class="icon-button icon-16-approve quickclaim_received" employee_id="'.$record['employee_id'].'" tooltip="Received Quitclaim" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}

		}

		$actions .= '</span>';

		return $actions;
	}

	function print_record_applicant($record_id = 0) {
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		if ($record_id > 0) {
			$this->db->where('applicant_id', $record_id);
			$record = $this->db->get($this->module_table);

			if ($record && count($record->num_rows()) > 0) {
				$employee = $record->row_array();
				$this->print_record($employee[$this->key_field]);
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	/**
	 * Resets updates to employee record and copies over details from applicant table.
	 * @return json
	 */
	function reset() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);			
		} else if (!$this->user_access[$this->module_id]['delete']) {
			$response->msg      = 'You dont have sufficient access to the requested module. <span class="red">Please contact the System Administrator.</span>.';
			$response->msg_type = 'notice';					
		} else {					
			$this->db->where('applicant_id', $this->input->post('applicant_id'));
			$this->db->where('deleted', 0);

			$this->db->delete('employee');

			$this->db->where('applicant_id', $this->input->post('applicant_id'));
			$this->db->where('deleted', 0);

			$applicant = $this->db->get('recruitment_applicant');

			$response = $this->_get_hired_message($applicant);
		}	
		
		$data['json'] = $response;

		$this->load->view('template/ajax', $data);
	}

	/**
	 * Return a json string usable by jquery UI's autocomplete based on the query.
	 * @return json
	 */
	function search_autocomplete() {
		$string = array();
		$search = $this->input->get('term');

		$this->db->select(array(
					'user.employee_id', 
					'user.firstname', 
					'user.lastname', 
					'user_company.company', 
					'user_position.position',
					'user.position_id'
					)
		);		
		$this->db->join('user_company', 'user.company_id = user_company.company_id', 'left');
		$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
		$this->db->or_like(
			array(
				'firstname' => $search,
				'lastname'  => $search
				)
			);		

		$employees = $this->db->get('user');

		if ($employees->num_rows() > 0) {
			foreach ($employees->result() as $employee) {
				$position = ($employee->position_id > 0) ? ' (' . $employee->position . ')' : '';

				$string[] = array(
					'id' 	   => $employee->employee_id, 
					'value'    => $employee->firstname . ' ' . $employee->lastname, 
					'label'    => $employee->firstname . ' ' . $employee->lastname . $position,
					'category' => $employee->company
					);
			}
		}		

		if (!IS_AJAX) {
			return $string;
		} else {
			$this->load->view('template/ajax', array('json' => $string));
		}
	}

	/**
	 * Repeated code from application/conrollers/recruitment/candidates.php.  =)
	 * 	 
	 */
	
	private function _get_hired_message($applicant) {
		$response->msg_type = 'success';
		$response->msg 		= '201 File has been reset.';
				
		if ($this->hdicore->module_active('hris_201')) {
			if ($this->_save_new_employee($applicant)) {
				$response->msg .= ' 201 file has been created successfully.';
			} else {
				$response->msg_type = 'notice';
				$response->msg .= ' <br />NOTICE: failed to create 201 file.';
			}
		}
		
		return $response;
	}

	private function _save_new_employee($applicant) {		
		$this->db->where('applicant_id', $applicant->row()->applicant_id);
		$result = $this->db->get('employee');

		if ($result->num_rows() > 0) {
			return TRUE;
		}

		// Copy data from applicant to employee table.
		// Get intersecting fields to use on SELECT statement.
		$employee_fields = $this->db->list_fields('employee');
		$applicant_fields = $this->db->list_fields('recruitment_applicant');
		$select = array_intersect($employee_fields, $applicant_fields);

		$this->db->select($select);
		$this->db->where('applicant_id', $applicant->row()->applicant_id);
		$applicant_details = $this->db->get('recruitment_applicant')->row_array();

		$applicant_details['company_id'] = '';
		$applicant_details['department_id'] = '';

		return $this->db->insert('employee', $applicant_details);
	}

		//get division segment
	function get_division_segment() {
	if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('department_id', $this->input->post('department_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('user_company_department');

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= $this->input->post('department_id');
			} else {
				$response->msg_type = 'success';
				$response->data = $employee->row_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}
	//get division segment


	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export-employees' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
	            /*$buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";*/
	        }        
    	}
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function get_module_access(){

		if(!IS_AJAX){

			$request = $this->user_access[$this->module_id];
			$this->load->view('template/ajax', $request);

		}
		else{

			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	

		}

	}

	function get_job_level_info(){

		if(IS_AJAX){

			$id = $this->input->post('job_rank_id');
			$type = $this->input->post('type');

			if( $type == 'edit' ){

				$this->db->where(array('job_rank_id' => $id ));
				$result = $this->db->get('user_job_rank');

				$count = $result->num_rows();

				if( $count > 0 ){

					$data = $result->row_array();


				}
				else{
					$data = $count;
				}

				$this->load->view('template/ajax', array('json' => $data));

			}
			elseif( $type == 'detail' ){

				
					$this->db->where(array('employee_id' => $this->input->post('record_id') ));
					$result = $this->db->get('employee');
					$employee_detail = $result->row();

					$job_rank_id = $employee_detail->job_rank_id;

					if($job_rank_id != NULL){

						$this->db->where(array('job_rank_id' => $job_rank_id ));
						$result = $this->db->get('user_job_rank');


						$count = $result->num_rows();

						if( $count > 0 ){

							$data = $result->row_array();
						}
						else{
							$data = $count;
						}

						$this->load->view('template/ajax', array('json' => $data));

					}
					else{

						$data = 0;
						$this->load->view('template/ajax', array('json' => $data));

					}
			}

		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

	}

	function get_employee_type(){

		if(IS_AJAX){

			$id = $this->input->post('rank_id');

			if($id == ""){
				$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('record_id') ));

				$record = $employee->row();
				$id = $record->rank_id;
			}


			$this->db->from('user_rank r');
			$this->db->join('employee_type e','r.employee_type = e.employee_type_id','left');

			$job_level_auto = 0;

			if ($this->db->field_exists('rank_level_id', 'user_rank')){
				$this->db->join('user_rank_level rl','r.rank_level_id = rl.rank_level_id','left');
				$job_level_auto = 1;
			}

			$this->db->where( array( 'r.job_rank_id' => $id ) );
			$result = $this->db->get();

			$data = $result->row_array();

			$data['job_level_auto'] = $job_level_auto;

			$user_type = 0;
			if ( $this->is_admin || $this->is_superadmin || $this->user_access[$this->module_id]['post'] == 1) {
				$user_type = 1;
			}

			$data['user_type'] = $user_type;

			$data['client'] = CLIENT_DIR;			

			$this->load->view('template/ajax', array('json' => $data));

		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

	}

	function get_region_by_location(){

		if(IS_AJAX){

			$id = $this->input->post('location_id');

			if($id == ""){
				$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('record_id') ));

				$record = $employee->row();
				$id = $record->location_id;
			}

			$data = array();

			if ($this->db->table_exists('region')){
				$this->db->select('ul.region_id,r.region');
				$this->db->from('user_location ul');
				$this->db->join('region r','ul.region_id = r.region_id','left');
				$this->db->where( array( 'ul.location_id' => $id ) );
				$result = $this->db->get();
				
				$data = $result->row_array();
			}

			$this->load->view('template/ajax', array('json' => $data));
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

	}	

	function get_rank_range(){

		if(IS_AJAX){

			$id = $this->input->post('rank_level');

			if($id == ""){
				$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('record_id') ));
				$record = $employee->row();
				$id = $record->job_level;
			}

			$this->db->from('user_rank_level r');
			$this->db->join('user_rank_range rr','r.rank_range_id = rr.job_rank_range_id','left');
			$this->db->where( array( 'r.rank_level_id' => $id ) );
			$result = $this->db->get();

			$data = $result->row_array();

			$this->load->view('template/ajax', array('json' => $data));


		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

	}

	function with_job_description(){

		if(IS_AJAX){
			$id = $this->input->post('record_id');
			$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('record_id') ));
			$record = $employee->row();
			$data['with_job_description'] = $record->with_job_description;
			$this->load->view('template/ajax', array('json' => $data));
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

	}


	function show_tooltip() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			$this->db->select('employment_status.employment_status, user_company.*, user.*, user_position.*, user_job_level.*, user_job_rank.*, employee.*');
			$this->db->where('employee.employee_id',$this->input->post('employee_id'));
			$this->db->join('user','employee.employee_id = user.employee_id');
			$this->db->join('user_company','user_company.company_id = user.company_id');
			$this->db->join('user_position','user_position.position_id = user.position_id');
			$this->db->join('user_job_level','employee.job_level = user_job_level.job_level_id','left');
			$this->db->join('user_job_rank','employee.rank_code = user_job_rank.job_rank_id','left');
			$this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');

			$employee=$this->db->get('employee');

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'Employee not found.';
			} else {
				$response->msg_type = 'success';
				$response->data = $employee->row_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function show_full_movement() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('employee_movement.*,employee_movement_type.*, c.department AS old_department_name, d.department AS new_department_name, a.position AS old_position_name, b.position AS new_position_name, o_rank.job_rank AS old_rank, n_rank.job_rank AS new_rank, o_job_level.description AS old_job_level, n_job_level.description AS new_job_level, o_rank_code.job_rank_code AS old_rank_code, n_rank_code.job_rank_code AS new_rank_code, o_cmpny.company AS old_cmpny, n_cmpny.company AS new_cmpny, o_division.division AS old_division, n_division.division AS new_division, o_location.location AS old_location, n_location.location AS new_location, o_segment_1.segment_1 AS old_segment_1, n_segment_1.segment_1 AS new_segment_1, o_segment_2.segment_2 AS old_segment_2, n_segment_2.segment_2 AS new_segment_2');
    		$where = '('.$this->db->dbprefix.'employee_movement.status = 3 OR '.$this->db->dbprefix.'employee_movement.status = 6) AND '.$this->db->dbprefix.'employee_movement.employee_id ='.$this->input->post('employee_id');
    		$this->db->where($where);
			$this->db->join('employee_movement_type','employee_movement.employee_movement_type_id = employee_movement_type.employee_movement_type_id', 'left');
			$this->db->join('user_position AS a','employee_movement.current_position_id = a.position_id', 'left');
			$this->db->join('user_position AS b','employee_movement.new_position_id = b.position_id', 'left');
			$this->db->join('user_company_department AS c','employee_movement.current_department_id = c.department_id', 'left');
			$this->db->join('user_company_department AS d','employee_movement.transfer_to = d.department_id', 'left');
			$this->db->join('user_rank AS o_rank','employee_movement.current_rank_dummy = o_rank.job_rank_id', 'left');
			$this->db->join('user_rank AS n_rank','employee_movement.rank_id = n_rank.job_rank_id', 'left');
			$this->db->join('user_job_level AS o_job_level','employee_movement.current_job_level_dummy = o_job_level.job_level_id', 'left');
			$this->db->join('user_job_level AS n_job_level','employee_movement.job_level = n_job_level.job_level_id', 'left');
			$this->db->join('user_rank_code AS o_rank_code','employee_movement.current_rank_code_dummy = o_rank_code.job_rank_code_id', 'left');
			$this->db->join('user_rank_code AS n_rank_code','employee_movement.rank_code = n_rank_code.job_rank_code_id', 'left');
			$this->db->join('user_company AS o_cmpny','employee_movement.current_company_dummy = o_cmpny.company_id', 'left');
			$this->db->join('user_company AS n_cmpny','employee_movement.company_id = n_cmpny.company_id', 'left');
			$this->db->join('user_company_division AS o_division','employee_movement.current_division_dummy = o_division.division_id', 'left');
			$this->db->join('user_company_division AS n_division','employee_movement.division_id = n_division.division_id', 'left');
			$this->db->join('user_location AS o_location','employee_movement.current_location_dummy = o_location.location_id', 'left');
			$this->db->join('user_location AS n_location','employee_movement.location_id = n_location.location_id', 'left');
			$this->db->join('user_company_segment_1 AS o_segment_1','employee_movement.current_segment_1_dummy = o_segment_1.segment_1_id', 'left');
			$this->db->join('user_company_segment_1 AS n_segment_1','employee_movement.segment_1_id = n_segment_1.segment_1_id', 'left');
			$this->db->join('user_company_segment_2 AS o_segment_2','employee_movement.current_segment_2_dummy = o_segment_2.segment_2_id', 'left');
			$this->db->join('user_company_segment_2 AS n_segment_2','employee_movement.segment_2_id = n_segment_2.segment_2_id', 'left');
			$employee=$this->db->get('employee_movement');

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'attention';
				$response->msg 		= 'No Employee Update';
			} else {
				$response->msg_type = 'success';
				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}


	function download_file($file = false){
		$path = './uploads/' . $this->module_link . '/' . $file;
		header('Content-disposition: attachment; filename='.$file.'');
		header('Content-type: txt/pdf');
		readfile($path);
	}	

	function get_clearance_approver(){
		$this->db->where('employee_clearance_form_checklist.deleted', 0);
		$result = $this->db->get('employee_clearance_form_checklist');		
		$html = '<option value="">Select…</option>';		
		foreach ($result->result() as $row){
			$html .= '<option value="'.$row->employee_clearance_form_checklist_id.'">'.$row->name.'</option>';
		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		

		//$this->load->view('template/ajax', array('json' => $response));						
	}

	function module_export_options() {				
		$data['content'] = 'export_boxy';
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['module_id'] = $this->input->post('module_id');		

		if (IS_AJAX) {	
			$response['json']['html'] = $this->load->view($data['content'], $data, TRUE);
			$this->load->view('template/ajax', $response);
		}
	}

	function get_query_fields2($record_id = 0) {
		if ($record_id == 0) {
			$record_id = $this->input->post('export_query_id');
		}

		$this->db->where('parent_module_id', $record_id);
		$this->db->where('deleted', 0);
		$result = $this->db->get('export_query');

		if ($result->num_rows() > 0) {
			$export_query_id = $result->row()->export_query_id;
		}

		$this->db->where('export_query_id', $export_query_id);
		$this->db->limit(1);
		$export_query = $this->db->get('export_query')->row_array();		

		$this->db->where('export_query_id', $export_query_id);
		$this->db->order_by('field','asc');
		$result = $this->db->get('export_query_fields')->result();

		$fields = array();
		foreach ($result as $field) {
			$fields[$field->field] = $field->field;
		}

		$this->db->select('user.firstname');
		$this->db->select('user.lastname');
		$this->db->select('user.user_id');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');


		if( $this->input->post('criteria') == 'active' ){
			$this->db->where('user.inactive',0);
			$this->db->where('employee.resigned',0);
		}
		elseif( $this->input->post('criteria') == 'inactive' ){
			$this->db->where('user.inactive',1);
			$this->db->where('employee.resigned',0);
		}
		elseif( $this->input->post('criteria') == 'resigned' ){
			// $this->db->where('user.inactive',0);
			$this->db->where('employee.resigned',1);
		}
		$this->db->where($this->filter);
		$this->db->where('employee.deleted', 0); // exclude deleted employees on dropdown
		$this->db->order_by('user.firstname','ASC');
		$user_list = $this->db->get('user');

		if (!IS_AJAX) {
			return 	array(					
					'fields' 		 => $fields,
					'description'    => $export_query['description'],
					'export_query_id' => $export_query['export_query_id']
				);
		} else {
			$data['html'] = $this->load->view(
								'employees/export_fields_employees', //added export_fields_employees.php to filter all employees for export in all clients
								array(
									'fields' 		  => $fields, 
									'description'     => ucfirst($this->input->post('criteria')),
									'export_query_id' => $export_query['export_query_id'],
									'users'           => $user_list->result(),
									'total_users'     => $user_list->num_rows()
									), 
								TRUE
							);

			$this->load->view('template/ajax', array('json' => $data));
		}
	}

	function export2() {	
		$query_id = $this->input->post('export_query_id');

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$dbprefix = $this->db->dbprefix;

		$this->db->where('export_query_id', $query_id);
		$this->config->config['compress_output'] = 0;
		$result = $this->db->get('export_query');
		$export = $result->row();
		$query = $export->query_string;
		$query = str_replace("{dbprefix}", $dbprefix, $query);
		$additional_fields = array();
		$where_query = "";

		if($this->input->post('records_from') != ""){
			$query = "SELECT CONCAT(b.firstname,' ', IF(b.middlename IS NOT NULL, b.middlename, ''),' ',b.lastname) AS \"Employee's Name\"
						FROM hr_employee a LEFT JOIN hr_user b ON b.employee_id = a.employee_id";
			$additional_fields = array("Employee's Name");

			switch ( $this->input->post('records_from') ){
				case 'accountabilities':

					$select_query = ",
									  eac.equipment AS 'Equipment',
									  eac.tag_number AS 'Tag Number',
									  eac.status AS 'Status',
									  eac.date_issued AS 'Date Issued',
									  eac.date_returned AS 'Date Returned',
									  eac.cost AS 'Cost',
									  eac.quantity AS 'Quantity',
									  ecc.name AS 'Clearance Approver',
									  eac.remarks AS 'Remarks' FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_accountabilities')." eac ON eac.employee_id = a.employee_id
								LEFT JOIN ".$this->db->dbprefix('employee_clearance_form_checklist')." ecc ON eac.employee_clearance_form_checklist_id = ecc.employee_clearance_form_checklist_id";
				
					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// commented to include employees even without records - uncomment to exclude
					// $where_query = " AND employee_accountabilities_id IS NOT NULL";

				break;
				case 'affiliation':

					$select_query = ",
									  IF(eaf.affiliation_id IS NOT NULL, aff.affiliation, eaf.name) AS 'Name of Affiliation',
									  IF(eaf.active = 1, 'Active', IF(eaf.active = 0, 'Inactive', NULL)) AS 'Status',
									  eaf.position AS 'Position',
									  eaf.date_resigned AS 'Date Resigned' ,
									  eaf.date_joined AS 'Date Joined' FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_affiliates')." eaf ON eaf.employee_id = a.employee_id 
								LEFT JOIN ".$this->db->dbprefix('affiliation')." aff ON eaf.affiliation_id = aff.affiliation_id";
				
					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND record_id IS NOT NULL";

				break;
				case 'character_reference':

					$select_query = ",
									  ere.name AS 'Name',
									  ere.address AS 'Address',
									  ere.company_name AS 'Company',
									  ere.email_address AS 'Email Address',
									  ere.telephone AS 'Telephone',
									  ere.occupation AS 'Occupation',
									  ere.years_known AS 'Years Known'  FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_references')." ere ON ere.employee_id = a.employee_id";
				
					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND record_id IS NOT NULL";

				break;
				case 'education':
					$select_query = ",
									  CASE
									    eed.education_level 
									    WHEN 8 
									    THEN 'Elementary' 
									    WHEN 9 
									    THEN 'Highschool' 
									    WHEN 10
									    THEN 'College' 
									    WHEN 11
									    THEN 'Graduate Studies' 
									    WHEN 12
									    THEN 'Vocational' 
									    ELSE NULL 
									  END AS 'Educational Attainment',
									  IF((eed.education_level IN (8,9)), eed.school, eeds.education_school) AS 'School',
									  eed.honors_received AS 'Honors Received',
  									  IF(eed.graduate = 1, 'Graduate', IF(eed.graduate = 0, 'Undergraduate', NULL)) AS 'Graduate/Undergraduate',
  									  IF((eed.education_level IN (8,9)), eed.degree, eeddo.employee_degree_obtained) AS 'Degree Obtained',
									  eed.date_from AS 'Date From',
									  eed.date_to AS 'Date To'
									FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_education')." eed ON eed.employee_id = a.employee_id 
								LEFT JOIN ".$this->db->dbprefix('education_school')." eeds ON eed.education_school_id = eeds.education_school_id 
  								LEFT JOIN ".$this->db->dbprefix('employee_degree_obtained')." eeddo ON eed.employee_degree_obtained_id = eeddo.employee_degree_obtained_id ";
					
					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND education_id IS NOT NULL";

				break;
				case 'employee_trainings':

					$select_query = ",
									  IF(
									    etr.course_id IS NOT NULL,
									    eco.course,
									    etr.course
									  ) AS 'Course',
									  etr.institution AS 'Institution',
									  etr.address AS 'Address',
									  etr.remarks AS 'Remarks' ,
									  etr.from_date AS 'Date From',
									  etr.to_date AS 'Date To',
									  ets.employee_training_status AS 'Training Status' FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_training')." etr ON etr.employee_id = a.employee_id 
								LEFT JOIN ".$this->db->dbprefix('course')." eco ON eco.course_id = etr.course_id 
								LEFT JOIN ".$this->db->dbprefix('employee_training_status')." ets ON ets.employee_training_status_id = etr.employee_training_status_id";

					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND training_id IS NOT NULL";

				break;
				case 'employment_history':

					$select_query = ",
									  ehi.company AS 'Company',
									  ehi.address AS 'Address',
									  ehi.contact_no AS 'Contact No.',
									  ehi.nature_of_business AS 'Nature Of Business',
									  ehi.position AS 'Position',
									  ehi.from_date AS 'Date From',
									  ehi.to_date AS 'Date To',
									  ehi.supervisor_name AS \"Immediate Superior's Name\",
									  ehi.reason_for_leaving AS 'Reason For Leaving',
									  ehi.duties AS 'Duties' ,
									  ehi.last_salary AS 'Last Salary',
									  ups.position AS 'Equivalent Position (FB)' FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_employment')." ehi ON ehi.employee_id = a.employee_id 
								LEFT JOIN ".$this->db->dbprefix('user_position')." ups ON ups.position_id = ehi.fb_equivalent_position_id ";
				
					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND record_id IS NOT NULL";

				break;
				case 'family':
					$select_query = ",
									  efa.name AS \"Family Member's Name\",
									  efa.relationship AS 'Relationship',
									  efa.birth_date AS 'Date of Birth',
									  efa.occupation AS 'Occupation',
									  efa.employer AS 'Employer',
									  (SELECT GROUP_CONCAT(
									    fbt.family_benefit SEPARATOR ','
									  )
									  FROM hr_family_benefit fbt
									  WHERE FIND_IN_SET(
									      fbt.family_benefit_id,
									      efa.family_benefit_id
									    ) GROUP BY efa.record_id ) AS 'Family Benefit',
									  efa.degree AS 'Degree Obtained',
									    efa.educational_attainment AS 'Educational Attainment' FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_family')." efa ON efa.employee_id = a.employee_id ";

					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND record_id IS NOT NULL";

				break;
				case 'other_information':
				$select_query = ", eot.name as 'Name', eot.relation as 'Relation', eot.occupation  as 'Occupation', eot.company  as 'Company' FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_otherinfo1')." eot ON eot.employee_id = a.employee_id";

					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND record_id IS NOT NULL";

				break;
				case 'skill':
					$select_query = ",
								    IF(esk.skill_type_id IS NOT NULL, skt.skill_type, esk.skill_type) AS 'Skill Type',
								    IF(esk.skill_name_id IS NOT NULL, skn.skill_name, esk.skill_name) AS 'Skill Name',
								    esk.proficiency AS 'Proficiency Level',
								    esk.remarks AS 'Remarks'  FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_skill')." esk ON esk.employee_id = a.employee_id 
								LEFT JOIN ".$this->db->dbprefix('skill_type')." skt ON esk.skill_type_id = skt.skill_type_id 
								LEFT JOIN ".$this->db->dbprefix('skill_name')." skn ON esk.skill_name_id = skn.skill_name_id";

					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND record_id IS NOT NULL";

				break;
				case 'test_profile':
					$select_query = ",
								    etp.exam_type AS 'Exam Type',
								    IF(etp.exam_title_id IS NOT NULL, ext.exam_title, etp.exam_title) AS 'Exam Title',
								    etp.license_no AS 'License Number',
								    etp.date_taken AS 'Date Taken',
								    etp.given_by AS 'Given By',
								    etp.location AS 'Location',
								    etp.score_rating AS 'Score/Rating',
								    etp.result AS 'Result',
								    etp.remarks AS 'Remarks'  FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_test_profile')." etp ON etp.employee_id = a.employee_id 
								LEFT JOIN ".$this->db->dbprefix('exam_title')." ext ON ext.exam_title_id = etp.exam_title_id";

					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND employee_test_profile_id IS NOT NULL";

				break;
/*				case 'work_assignment':
					$select_query = ",
								    IF(ewa.assignment = 1, 'Primary', IF(ewa.assignment = 2, 'Concurrent', NULL)) AS 'Assignment',
								    ewac.employee_work_assignment_category AS 'Assignment Category',
								    ewadi.division AS 'Division',
								    IF(ewa.division_id > 0, ewa.cost_code, NULL) AS 'Division Cost Code',
								    ewapj.project_name AS 'Project',
								    IF(ewa.project_name_id > 0, ewa.cost_code, NULL) AS 'Project Cost Code' ,
								    ewagn.group_name AS 'Group',
								    IF(ewa.group_name_id > 0, ewa.cost_code, NULL) AS 'Group Cost Code',
								    ewad.department AS 'Department',
								    IF(ewa.department_id > 0, ewa.cost_code, NULL) AS 'Department Cost Code' ,
								    ewacs.code_status AS 'Code Status',
								    ewa.start_date AS 'Start Date',
								    ewa.end_date AS 'End Date' FROM";
					$query = str_replace("FROM", $select_query, $query);
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_work_assignment')." ewa ON ewa.employee_id = a.employee_id ";
					$query .= " LEFT JOIN ".$this->db->dbprefix('employee_work_assignment_category')." ewac ON ewac.employee_work_assignment_category_id = ewa.employee_work_assignment_category_id";
					$query .= " LEFT JOIN ".$this->db->dbprefix('user_company_division')." ewadi ON ewadi.division_id = ewa.division_id";
					$query .= " LEFT JOIN ".$this->db->dbprefix('project_name')." ewapj ON ewapj.project_name_id = ewa.project_name_id";
					$query .= " LEFT JOIN ".$this->db->dbprefix('group_name')." ewagn ON ewagn.group_name_id = ewa.group_name_id";
					$query .= " LEFT JOIN ".$this->db->dbprefix('user_company_department')." ewad ON ewad.department_id = ewa.department_id";
					$query .= " LEFT JOIN ".$this->db->dbprefix('code_status')." ewacs ON ewacs.code_status_id = ewa.code_status_id";

					foreach($this->input->post('records_fields') as $key => $fval){
						array_push($additional_fields,$fval);
					}
					// $where_query = " AND employee_work_assignment_id IS NOT NULL";

				break;*/
			}
		}

		switch ($this->input->post('criteria')) {
			case 'Active':
					$query .= " WHERE b.inactive = 0 AND a.resigned = 0 AND a.deleted = 0 AND b.deleted = 0";
				break;			
			case 'Inactive':
					$query .= " WHERE b.inactive = 1 AND a.resigned = 0 AND a.deleted = 0 AND b.deleted = 0";
				break;			
			case 'Resigned':
					$query .= " WHERE a.resigned = 1 AND a.deleted = 0 AND b.deleted = 0";
				break;
			default:
					$query .= " WHERE a.deleted = 0 AND b.deleted = 0";
				break;
		}

		if( count( $this->input->post('employees') ) > 0 ){
			$employee_list = implode("','",$this->input->post('employees'));
			$query .= " AND b.employee_id IN ('".$employee_list."')";
		}

		if($where_query != ""){
			$query .= $where_query;
		}
		
		$query  = $this->db->query($query);

		if (count($this->input->post('fields')) == 0) {		
			$fields = $query->list_fields();
		} else {
			$fields = $this->input->post('fields');
		}

		if( count( $additional_fields ) > 0 ){
			foreach( $additional_fields as $afields ){
				if(count($fields == 0)){
					$fields[] = $afields;
				}else{
					array_push($fields,$afields);
				}
			}
		}

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;

		switch ($this->input->post('export_type')) {
			case 'excel': $this->_excel_export(); break;
			case 'html' : $this->_html_export(); break;
			case 'pdf' : $this->_pdf_export(); break;
		}

	}

	private function _html_export()
	{
		$this->load->view('template/export_table', array('table' => $this->_get_html()));
	}

	private function _pdf_export()
	{
		$export = $this->_export;

		$this->load->library('pdf');
		$html = $this->_get_html();

		// Prepare and output the PDF.
		$this->pdf->addPage();
		$this->pdf->writeHTML($html, true, false, true, false, '');
		$this->pdf->Output(date('Y-m-d').' '.$export->description . '.pdf', 'D');
	}

	private function _get_html()
	{
		$this->load->library('table');

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		// Define table heading.
		$this->table->set_heading($fields);

		$results = $query->result();

		foreach ($results as $data) {
			$row = array();

			foreach ($fields as $field) {
				$row[] = $data->{$field};
			}

			$this->table->add_row($row);
		}
		
		$tmpl = array ( 
			'table_open'  => '<table cellpadding="5" border="0" width="100%" class="simple-table">',
			'heading_cell_start' => '<th bgcolor="#CCCCCC" scope="col">'
			);

		$this->table->set_template($tmpl);		

		return $this->table->generate();
	}

	function get_pos_reporting_to()
	{
		if(IS_AJAX)
		{
			$pos_id = $this->input->post('position_id');
			$space = ", ";
			$this->db->select('a_pos.position AS approver_position, s_pos.position AS selected_position, CONCAT(a_name.firstname, " ",a_name.middlename, " ",a_name.lastname) AS approver_name', false);
			$this->db->from('user_position AS s_pos');
			$this->db->join('user_position AS a_pos', 'a_pos.position_id = s_pos.reporting_to');
			$this->db->join('user AS a_name', 'a_name.position_id = s_pos.reporting_to');
			$this->db->where('s_pos.position_id', $pos_id);
			$positions = $this->db->get();
			
			if (!$positions || $positions->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'No Position Reporting To';
			} else {
				$response->msg_type = 'success';
				$response->data = $positions->row_array();
			}
			
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
	}

	function is_resigned()
	{
		if(IS_AJAX)
		{
			$resigned = $this->db->get_where('employee', array("employee_id" => $this->input->post("employee_id")));
			
			if (!$resigned || $resigned->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'user not found';
			} else {
				$resigned = $resigned->row();
				if($resigned->resigned == 1)
				{
					$response->msg_type = 'success';
					$response->is_resigned = true;
					$response->data = $resigned;
					$response->dropdown = $this->db->get_where('employment_status', array("active" => 0))->result();

				} else {
					$response->msg_type = 'success';
					$response->is_resigned = false;
				}
			}
			
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
	}

	private function _excel_export()
	{
		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		foreach ($fields as $field) {
			
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}
			//Initialize style
			$styleArray = array(
				'font' => array(
					'bold' => true,
					)
				);
			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '1')->applyFromArray($styleArray);
			$activeSheet->setCellValueExplicit($xcoor . '1', $field, PHPExcel_Cell_DataType::TYPE_STRING);
			
			$alpha_ctr++;
		}
		
		// contents.
		$line = 2;
		foreach ($query->result() as $row) {
			$sub_ctr   = 0;
			$alpha_ctr = 0;			
			
			foreach ($fields as $field) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}
				if($this->input->post('records_from') == ""){ //filter by fields

					if( ($field == 'Birth Date' || $field == 'Date Issued' || $field == 'Date of Marriage' || $field == 'Employed Date' || $field == 'Regular Date' || $field == 'Resigned Date' || $field == 'Date Hired' || $field == 'Date of Regularization')  ){

						if(( $row->{$field} != "" && strtotime($row->{$field}) != "" )){
								$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date(str_replace(' ', '-', $this->config->item('display_date_format')), strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 
						}

					}else{ 
						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 			
					}

				}else{ //filter by records from

					if($field == 'Date From' || $field == 'Date To' || $field == 'Date Issued' || $field == 'Date Returned' || $field == 'Date Resigned' || $field == 'Date Joined'
						|| $field == 'Date of Birth' || $field == 'Date Taken' || $field == 'Start Date' || $field == 'End Date'){
						if(( $row->{$field} != "" && strtotime($row->{$field}) != "" )){
							if(CLIENT_DIR == 'firstbalfour'){
								if($this->input->post('records_from') == 'education' || $this->input->post('records_from') == 'affiliation'){
									$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date('Y', strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING);
								}elseif($this->input->post('records_from') == 'accountabilities' || $this->input->post('records_from') == 'family'){
									$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date('M d, Y', strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING);
								}elseif($this->input->post('records_from') == 'employee_trainings' || $this->input->post('records_from') == 'employment_history' || $this->input->post('records_from') == 'test_profile'){
									$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date('F Y', strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING);
								}
							}else{
								$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date(str_replace(' ', '-', $this->config->item('display_date_format')), strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 
							}
						}
					}else{ 
						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 			
					}

				}

				$alpha_ctr++;
			}

			$line++;
		}

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . '_' . url_title($export->description) . '.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	function quitclaim_received(){

		if($this->input->post('record_id')){

			$this->db->update('employee',array('quitclaim_received'=>1),array('employee_id'=>$this->input->post('record_id')));
			$this->db->update('employee_clearance',array('quitclaim_received'=>1),array('employee_id'=>$this->input->post('record_id')));

			$response->msg_type = 'success';
			$response->msg 		= 'Received Quitclaim has successfully noted';
		
			$this->load->view('template/ajax', array('json' => $response));

		}

	}

	function get_cost_code_division(){
		$response->cost_code = '';
		$response->div_code = '';
		$this->db->where('deleted',0);
		$this->db->where('project_name_id',$this->input->post('project_name_id'));
		$result = $this->db->get('project_name');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->cost_code = $row->cost_code;			
			$response->division_id = $row->division_id;

			$this->db->where('deleted',0);
			$this->db->where('division_id', $row->division_id);
			$div_result = $this->db->get('user_company_division');
			if ($div_result && $div_result->num_rows() > 0){
				$div = $div_result->row();

				$response->div_code = $div->dvision_code;
			}					
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}

	function get_division_cost_code(){
		$response->cost_code = '';
		$this->db->where('deleted',0);
		$this->db->where('division_id',$this->input->post('division_id'));
		$result = $this->db->get('user_company_division');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->cost_code = $row->dvision_code;			
			$response->division_id = $row->division_id;			
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}

	function get_department_code_group(){
		$response->cost_code = '';
		$response->grp_code = '';
		$this->db->where('deleted',0);
		$this->db->where('department_id',$this->input->post('department_id'));
		$result = $this->db->get('user_company_department');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();

			$this->db->where('deleted',0);
			$this->db->where('group_name_id', $row->group_name_id);
			$grp_result = $this->db->get('group_name');
			if ($grp_result && $grp_result->num_rows() > 0){
				$grp = $grp_result->row();

				$response->grp_code = $grp->group_code;
			}

			$response->group_name_id = $row->group_name_id;	
			$response->cost_code = $row->department_code;	

		}
		$this->load->view('template/ajax', array('json' => $response));		
	}	

	function get_group_cost_code(){
		$response->cost_code = '';
		$this->db->where('deleted',0);
		$this->db->where('group_name_id',$this->input->post('group_name_id'));
		$result = $this->db->get('group_name');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->group_code = $row->group_code;	
			$response->cost_code = $row->group_code;			
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}	

	function get_employee_id_no(){
		$last_series_no = '';
		$result_format = $this->db->get('employee_id_number_format')->row();
		if ($result_format->id_number_format_series != ''){
			$length = strlen($result_format->id_number_format_series);
			$last_series_no = $result_format->id_number_format_series + 1;	
			$last_series_no = str_pad($last_series_no, $length, "0", STR_PAD_LEFT);
		}

		$result = $this->db->get('employee_id_number_config');
		if ($result && $result->num_rows() > 0){
			$str = '';
            $ctr = 1;
            $series = '';					
			foreach ($result->result() as $row) {
                if ($row->employee_id_number_config_value != ''){
	                if ($row->employee_id_number_config_type_id == 5){
	                	$str .= $last_series_no;
	                }  
	                else{
                    	$str .= $row->employee_id_number_config_value;
	                }                	
                }
                if ($ctr < $result->num_rows() && $row->employee_id_number_config_value!= ''){
                    $str .= '';   
                }
                $ctr++;
			}

            $response->employee_id_last_series = $str;	  
            $response->last_series = $last_series_no;
			$response->msg_type = 'success';
			$response->msg 		= 'Employee id config already setup';                			
		}
		else{
			$response->msg_type = 'error';
			$response->msg 		= 'Employee id config has not be setup already'; 			
		} 
		$this->load->view('template/ajax', array('json' => $response));						
	}	

	function get_skill_name(){
		$response->skill_name = '';
		$this->db->where('deleted',0);
		$this->db->where('skill_type_id',$this->input->post('skill_type_id'));
		$result = $this->db->get('skill_type');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->skill_name = $row->skill_name;			
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}

	function validate_employment_inclusive_dates(){

		$employment = $_POST['employment'];
		$cnt = 0;

		foreach( $employment['from_date'] as $key => $val ){
			$date_from_array = explode(' ',$val);
			$date_from_month = $date_from_array[0];
			$date_from_year = $date_from_array[1];

			$date_to_array = explode(' ',$employment['to_date'][$key]);
			$date_to_month = $date_to_array[0];
			$date_to_year = $date_to_array[1];

			// tirso - comment this line, it will not get correct date value
/*			$date_from_array = explode(' ',$val);
			$date_from_month = date('m',strtotime($date_from_array[0]));
			$date_from_year = date('Y',strtotime($date_from_array[1]));

			$date_to_array = explode(' ',$employment['to_date'][$key]);
			$date_to_month = date('m',strtotime($date_to_array[0]));
			$date_to_year = date('Y',strtotime($date_to_array[1]));*/

			if( !empty( $date_from_array ) && !empty( $date_to_array ) ){

				$date_from = date('Y-m-d',strtotime($date_from_year.'-'.$date_from_month.'-1'));
				$date_to = date('Y-m-d',strtotime($date_to_year.'-'.$date_to_month.'-1'));

				if( $date_from > $date_to ){
					$cnt++;
				}
			}
			else{
				$cnt++;
			}
		}

		if( $cnt > 0 ){

			$response->msg_type = 'error';
			$response->msg 		= 'Employee id config has not be setup already'; 

		}	
		else{

			$response->msg_type = 'success';

		}

		$this->load->view('template/ajax', array('json' => $response));

	}

	function vei_dates(){

		$employment = $_POST['employment'];
		$cnt = 0;

		foreach( $employment['from_date'] as $key => $val ){

			$date_from_array = explode(' ',$val);
			$date_from_month = $date_from_array[0];
			$date_from_year = $date_from_array[1];

			$date_to_array = explode(' ',$employment['to_date'][$key]);
			$date_to_month = $date_to_array[0];
			$date_to_year = $date_to_array[1];

			if( !empty( $date_from_array ) && !empty( $date_to_array ) ){
				$date_from = date('Y-m-d',strtotime($date_from_year.'-'.$date_from_month.'-1'));
				$date_to = date('Y-m-d',strtotime($date_to_year.'-'.$date_to_month.'-1'));
				if( $date_from > $date_to ){
					$cnt++;
				}
			}
			else{
				$cnt++;
			}
		}

		return $cnt;
	}

	function enable_status_options(){

		$view_type = $this->input->post('view_type');

		if( $view_type == 'edit' ){
			$status_id = $this->input->post('status_id');
		}
		elseif( $view_type == 'detail' ){

			$record_id = $this->input->post('record_id');
			$employee_info = $this->db->get_where('employee',array('employee_id'=>$record_id))->row();

			$status_id = $employee_info->status_id;

		}

		$status_result = $this->db->get_where('employment_status',array('employment_status_id'=>$status_id))->row();
		
		if ($status_result){
			$response->enable_delegates = $status_result->enable_delegates;
			$response->enable_terms = $status_result->enable_terms;
			$response->enable_email_notification = $status_result->enable_email_notification;
			$response->enable_agency = $status_result->enable_agency;
		}

		$this->load->view('template/ajax', array('json' => $response));	

	}

	function compute_end_date(){

		$terms = $this->input->post('terms');
		$date_hired = $this->input->post('date_hired');

		$end_date = date('m/d/Y',strtotime('+ '.$terms.' month',strtotime($date_hired)));

		$response->end_date = $end_date;
		$this->load->view('template/ajax', array('json' => $response));	

	}

    function toggle_active(){
    	$this->db->where('user_id',$this->input->post('user_id'));
    	$this->db->update('user',array('inactive'=>$this->input->post('val')));
    }	

    function _get_additional_training($record_id){

    	if( $record_id == null ){
    		$record_id = $this->input->post('record_id');
    	}

    	/*
    	$this->db->join('training_calendar','training_calendar.training_calendar_id = employee_training.training_calendar_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		$this->db->join('training_subject_schedule','training_subject_schedule.training_subject_schedule_id = training_subject.training_subject_schedule_id','left');
		$this->db->where('employee_training.training_calendar_id > 0');
		$this->db->where('employee_training.employee_id',$record_id);
		$employee_training = $this->db->get('employee_training');

		return $employee_training->result_array();
		*/

		$this->db->join('training_calendar','training_calendar.training_calendar_id = training_employee_database.training_calendar_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		$this->db->join('training_subject_schedule','training_subject_schedule.training_subject_schedule_id = training_subject.training_subject_schedule_id','left');
		$this->db->where('training_employee_database.employee_id',$this->input->post('record_id'));
		$employee_training = $this->db->get('training_employee_database');

		return $employee_training->result_array();

    }

    function get_benefits() {
    	$user_benefit = $this->db->get_where('user_benefit', array("user_benefit_id" => $this->input->post("user_benefit_id")))->row();
		$response->user_benefit_description = $user_benefit->user_benefit_description;
		$this->load->view('template/ajax', array('json' => $response));
    }

}

/* End of file */
/* Location: system/application */
