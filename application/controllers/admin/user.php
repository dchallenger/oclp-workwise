<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'User Settings';
		$this->listview_description = 'This module lists all defined user(s).';
		$this->jqgrid_title = "Users List";
		$this->detailview_title = 'User Group Info';
		$this->detailview_description = 'This page shows detailed information about a particular user';
		$this->editview_title = 'User Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about users';

		$this->filter = $this->module_table.".inactive = 0";

		if (CLIENT_DIR == 'firstbalfour'){

			$dbprefix = $this->db->dbprefix;
			$this->filter = $this->module_table.".employee_id <> 3";

			if( !($this->is_superadmin || $this->is_admin) && $this->user_access[$this->module_id]['project_hr'] == 1 ){

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
				if( $subordinate_list != "" || $subordinate_list != 0 ){
					$this->filter .= ' AND '. $dbprefix.'user.employee_id IN ('.$subordinate_list.')';
				}else{
					if ($subordinates == false) {
						$this->filter .= ' AND '. $dbprefix.'user.employee_id IN (0)';
					}
				}
			}	

		}
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
	
	function profile()
	{
		if ($this->user_access[$this->module_id]['list'] != 1) {			
			$breadcrumbs[0]['url'] = base_url();
			$breadcrumbs[1]['url'] = current_url();
		}
		
		$this->session->set_userdata('breadcrumbs', $breadcrumbs);

		$this->_set_if_profile();
		$this->edit();
	}

	function edit()
	{
		if($this->user_access[$this->module_id]['edit'] == 1 || ($this->user && !isset($_POST['record_id'])))
		{
			if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
			if( $this->user && !isset( $_POST['record_id'] ) ) $_POST['record_id'] = $this->user->user_id;
			
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
			if( $this->user_access[$this->module_id]['edit'] == 1 && !$data['show_wizard_control'] ) $data['views'] = array('admin/user/user_personal_access_gui');
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
	
	/**
	 * Give permission to edit/save if user's own profile.
	 */
	private function _set_if_profile()
	{
		if ($this->router->fetch_method() == 'profile' 
			|| $this->userinfo['user_id'] == $this->input->post('record_id')) {
			$this->user_access[$this->module_id]['edit'] = 1;
			$this->user_access[$this->module_id]['add'] = 1;

			$_POST['record_id'] = $this->userinfo['user_id'];		
		}		
	}

	function ajax_save()
	{		
		$this->_set_if_profile();
		parent::ajax_save();
		
		//additional module save routine here
		//update employee_id column
		$this->db->update('user', array('employee_id' => $this->key_field_val), array( $this->key_field => $this->key_field_val) );

		/* Start Save Module Access */
		
		// reset user access
		$this->db->delete('user_access', array('user_id' => $this->key_field_val));
		
		// get list of module actions
		$this->db->order_by('id');
		$actionlist = $this->db->get('module_action')->result_array();
		
		foreach($actionlist as $index => $action){
			// check if any action is added for custom access
			if( $this->input->post($action['action']) ){
				$action_to_module = $this->input->post($action['action']);
				foreach($action_to_module as $module_id => $access){
					$data = array(
						'user_id' => $this->key_field_val,
						'module_id' => $module_id,
						'action' => $action['action'],
						'access' => $access
					);
					$this->db->insert('user_access', $data);
				}
			}
		}
		
		//to reset user access, delete user access file
		$app_directories =  $this->hdicore->_get_config('app_directories');
		if( file_exists( $app_directories['user_settings_dir'] . $this->key_field_val.'.php' ) ) unlink( $app_directories['user_settings_dir'] . $this->key_field_val.'.php');
		/* END Save Module Access */	
				
		if ($this->input->post('record_id') == '-1') {
			$this->db->update('user', array('employee_id' => $this->key_field_val), array($this->key_field => $this->key_field_val) );
			$this->db->update('employee', array('employee_id' => $this->key_field_val), array($this->key_field => $this->key_field_val) );
			
			//insert into employee payrol
			$this->db->insert('employee_payroll', array('employee_id' => $this->key_field_val));

			//insert into employee dtr setup
			$this->db->insert('employee_dtr_setup', array('employee_id' => $this->key_field_val));
		}			

		$this->db->update('employee', array('user_id' => $this->key_field_val), array( 'employee_id' => $this->key_field_val) );

		//update password system encrypted
		$msg_type = $this->get_msg_type();
		if( $msg_type == 'success' ){
			if($_POST['password'] != ''){
				$this->load->library('encrypt');
				$password = $this->encrypt->encode( $_POST['password'] );
				$this->db->update('user', array('system_encrypted_password' => $password), array( 'employee_id' => $this->key_field_val) );
			}
		}
		
	}
	
	function delete()
	{
		$this->db->where('deleted',0);		
		$this->db->where('user_id',$this->input->post('record_id'));
		$result = $this->db->get('user');

		parent::delete();

		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$this->db->update('employee', array('deleted' => 1), array('employee_id' => $row->employee_id)); 

			$this->db->where('user_id',$this->input->post('record_id'));
			$this->db->update('user',array("login" => $this->input->post('record_id') .'-'. $row->login));

			$result = $this->db->get_where('employee', array('employee_id' => $row->employee_id));
			if ($result && $result->num_rows() > 0){
				$employee = $result->row();
				$this->db->where('employee_id',$employee->employee_id);
				$this->db->update('employee',array("biometric_id" => $employee->biometric_id .'-'. $employee->employee_id,"id_number" => $employee->id_number .'-'. $employee->employee_id));
			}
		}

		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions

	function _set_listview_query( $listview_id = '', $view_actions = true ) 
	{
		parent::_set_listview_query();

		$this->listview_qry .= ',inactive';
	}
	
	function _default_grid_actions( $module_link = "",  $container = "", $row )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";
		
		$class = "icon-16-active";

		if ($row['inactive'] == 1){
			$class = "icon-16-xgreen-orb";
		}

		$actions = '<span class="icon-group"><a class="icon-button '.$class.'" tooltip="Toggle Active/Inactive" href="javascript:void(0)"></a><a class="icon-button icon-16-key" tooltip="Change password?" href="javascript:void(0)"></a><a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a><a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a><a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a></span>';
		
		return $actions;
	}
	
	function add_custom_access()
	{
		if( IS_AJAX ){
			$response->msg = "";
			$post = array();
			foreach($_POST as $k => $v){
				$post[$k] = $v;
			}
			$data['post'] = $post;
			$response->custom_access = $this->load->view($this->userinfo['rtheme'].'/admin/user/add_custom_access', $data, true);
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);	
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

    /**
     * Override. Control which fieldgroups to show depending on what action.
     * @param  integer $record_id
     * @param  boolean $quick_edit_flag 
     * @return array
     */
    function _record_detail( $record_id = 0, $quick_edit_flag = false ) {
        $fieldgroups = parent::_record_detail($record_id, $quick_edit_flag);

        if ($fieldgroups) {            
            if ($this->router->fetch_method() == 'profile') {
                unset($fieldgroups[0]);
            }

            return $fieldgroups;
        } else {
            return FALSE;
        }
    }	

    function toggle_active(){
    	$this->db->where('user_id',$this->input->post('user_id'));
    	$this->db->update('user',array('inactive'=>$this->input->post('val')));
    }

    /**
	 * check wether user has overall access to modu;e
	 * @return void
	 */
	public function _visibility_check(){
		$excempt = array('profile');
		$excempt = array_merge($excempt, $this->config->item('bypass_accesscheck'));
		if( !in_array($this->method, $excempt) ){
			if( $this->method == 'ajax_save'){
				if( $this->input->post('record_id') != $this->user->user_id ) $this->hdicore->_visibility_check();
			}
			else
				$this->hdicore->_visibility_check();
		}
	}

	function _default_field_module_link_actions($keyfield_id = 0, $container = '', $fmlinkctr = 0, $row_data = array())
	{
		$actions = '<span class="icon-group"><a class="icon-button icon-16-add" tooltip="Add" href="javascript:void(0)" onclick="addRelatedModule(\''.$this->input->post('fieldname').'\', \''. $keyfield_id .'\', \''. $this->related_module_add_column .'\', \''. $container .'\', \''. $fmlinkctr .'\')"></a></span>';
		return $actions;
		
	}


	function basf_user_upload(){
		$fdata = file('uploads/basf201/user_upload.txt');
		foreach($fdata as $row){
			$row_data = explode("\t", $row);
			foreach( $row_data as $index => $value ){
				$row_data[$index] = trim($value);
			}

			$name = explode(', ', $row_data[1]);
			$insert = array(
				'login' => $row_data[0],
				'lastname' => str_replace('"', '', trim( $name[0] ) ),
				'firstname' => str_replace('"', '', trim( $name[1] ) ),
				'middlename' => $row_data[2],
				'middleinitial' => !empty($row_data[2]) ? $row_data[2] . '.' : '',
				'division_id' => $row_data[3],
				'position_id' => $row_data[4],
				'birth_date' => date( 'Y-m-d', strtotime( $row_data[5] ) )
			);

			$check = $this->db->get_where('user', array('login' => $row_data[0]));
			if( $check->num_rows() == 1 ){
				$user = $check->row();
				$employee_id = $user->user_id;
				$this->db->update('user', $insert, array('user_id' => $employee_id));
			}
			else{
				$this->db->insert('user', $insert);
				$employee_id = $this->db->insert_id();
			}
			
			$this->db->update('user', array('employee_id' => $employee_id ), array('user_id' => $employee_id));

			$check = $this->db->get_where('employee', array('employee_id' => $employee_id));
			if( $check->num_rows() == 1 ){
				$this->db->update('employee', array('id_number' => $row_data[0], 'biometric_id' => $row_data[0]), array('user_id' => $employee_id));
			}
			else{
				$insert = array(
					'user_id' => $employee_id, 
					'employee_id' => $employee_id, 
					'id_number' => $row_data[0], 
					'biometric_id' => $row_data[0]
				);
				$this->db->insert('employee', $insert);
			}
		}
	}

	function basf_costcenter_upload(){
		$fdata = file('uploads/basf201/costcenter_upload.txt');
		foreach($fdata as $row){
			$row_data = explode("\t", $row);
			foreach( $row_data as $index => $value ){
				$row_data[$index] = trim($value);
			}
			$user = $this->db->get_where('user', array('login' => $row_data[0]))->row();
			$cost_center = $this->db->get_where('employee_cost_center', array('employee_id' => $user->employee_id));
			if( $cost_center->num_rows() > 0 ){
				$cost_center = $cost_center->row();
				$this->db->update('employee_cost_center', array('cost_center_id' => $row_data[1], 'percentage' => 100), array('employee_id' => $user->employee_id));
			}
			else{
				$this->db->insert('employee_cost_center', array('employee_id' => $user->employee_id, 'cost_center_id' => $row_data[1], 'percentage' => 100));
			}

		}	
	}

	function basf_employee_upload(){
		$fdata = file('uploads/basf201/employee_upload.txt');
		foreach($fdata as $row){
			$row_data = explode("\t", $row);
			foreach( $row_data as $index => $value ){
				$row_data[$index] = trim($value);
			}
		
			$update = array(
				'rank_id' => $row_data[1],
				'employed_date' => date( 'Y-m-d', strtotime( $row_data[2] ) ),
				'location_id' => $row_data[3],
				'status_id' => $row_data[4],
				'agency_id' => $row_data[5],
			);

			$user = $this->db->get_where('user', array('login' => $row_data[0]))->row();
			$employee = $this->db->get_where('employee', array('employee_id' => $user->employee_id));
			if( $employee->num_rows() == 1 ){
				$employee = $employee->row();
				$this->db->update('employee', $update, array('employee_id' => $user->employee_id));
			}
			else{
				echo 'User not found, please createuser first<br/>';
			}

		}	
	}

	function basf_email_upload(){
		$fdata = file('uploads/basf201/email_upload.txt');
		foreach($fdata as $row){
			$row_data = explode("\t", $row);
			foreach( $row_data as $index => $value ){
				$row_data[$index] = trim($value);
			}

			$user = $this->db->get_where('user', array('lastname' => $row_data[1]));
			if( $user->num_rows() == 1 ){
				$user = $user->row();
				$this->db->update('user', array('email' => $row_data[2]), array('employee_id' => $user->employee_id));
			}
			else if($user->num_rows() > 1){
				$user = $this->db->get_where('user', array('lastname' => $row_data[2], 'firstname' => $row_data[0]));
				if( $user->num_rows() == 1 ){
					$user = $user->row();
					$this->db->update('user', array('email' => $row_data[2]), array('employee_id' => $user->employee_id));
				}
				else if($user->num_rows() > 1){
					echo 'User '.$row_data[0].' too many, cant update who<br/>';
				}
				else{
					echo 'User '.$row_data[1].' '. $row_data[0] .' not found, please create user first<br/>';
				}
			}
			else{
				echo 'User '.$row_data[1].' '. $row_data[0] .' not found, please create user first<br/>';
			}
		}
	}

	function get_boxy_change_password()
	{
		if(IS_AJAX)
		{
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1)
			{
				$data['user_id'] 		= $this->input->post('user_id');	
				$users = $this->db->get_where('user', array('user_id' => $this->input->post('user_id')))->row();	
				$data['fullname']			= $users->lastname.', '.$users->firstname.' '.$users->middlename;
				$response->change_form		= $this->load->view('admin/change_password', $data, true);
				$response->access = 1;
			}
			else{
				$response->access = 0;
				$response->msg = "You dont have sufficient priviledge to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}		
			
			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function change_password()
	{
		if(IS_AJAX)
		{
			$response->msg = "";			
			if($this->user_access[$this->module_id]['edit'] == 1)
			{
				$this->load->library('encrypt');
				$password = md5( $_POST['password'] );
				$system_encrypted_password = $this->encrypt->encode( $_POST['password'] );
				// $this->db->update('user', array('password' => $this->encrypt->encode($this->input->post('password'))), array('user_id' => $this->input->post('users_id')));
				$this->db->update('user', array('system_encrypted_password' => $system_encrypted_password,'password'=>$password), array('user_id' => $this->input->post('users_id')) );
				// dbug($this->db->last_query());
				$response->msg = "Password was successfully updated.";
				$response->msg_type = 'success';
			}
			else
			{
				$response->msg = "You dont have sufficient priviledge to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}		
			
			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>