<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profile extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'User Settings';
		$this->listview_description = 'This module lists all defined user groups.';
		$this->jqgrid_title = "Profile List";
		$this->detailview_title = 'Profile Info';
		$this->detailview_description = 'This page shows detailed information about a particular profile.';
		$this->editview_title = 'Profile Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about profile.';
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
			$data['views'] = array('admin/user/module_profile_access_gui');
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
		
		//additional module save routine here
		/* Start Save Module Access */
		$module_access = array();
		
		//get list of modules
		$this->db->order_by('parent_id, sequence');
		$this->db->select('module_id, short_name');
		$modulelist = $this->db->get('module')->result_array();
		
		// get list of module actions
		$this->db->order_by('id');
		$actionlist = $this->db->get('module_action')->result_array();
		
		foreach($modulelist as $index => $module ){
			$module_access[$module['module_id']] = array();
			foreach($actionlist as $index => $action){
				//check if any of checkbox was set
				if( $this->input->post($action['action']) ){
					$module_action = $this->input->post($action['action']);
					//check if checkbox for module is set
					if( isset($module_action[$module['module_id']]) ){
						$module_access[$module['module_id']][$action['action']] = 1;
					}
					else{
						$module_access[$module['module_id']][$action['action']] = 0;
					}
				}
				else{
					//no checkbox was check, no one has access to this action
					$module_access[$module['module_id']][$action['action']] = 0;
				}
			}
		}
		
		$module_access = serialize($module_access);
		$this->db->where($this->key_field, $this->key_field_val);
		$this->db->update($this->module_table, array('module_access' => $module_access));
		
		$sensitivity = '';
		if( $this->input->post('sensitivity') ){
			$sensitivity = $this->input->post('sensitivity');
			$sensitivity = serialize($sensitivity);
		}

		$this->db->where($this->key_field, $this->key_field_val);
		$this->db->update($this->module_table, array('record_sensitivity' => $sensitivity));
		
		//to reset user access, delete user access file of roles with associated profile
		$roles_affected = $this->db->get_where('role_profile', array($this->key_field => $this->key_field_val));
		if( $roles_affected->num_rows() > 0 ){
			$app_directories =  $this->hdicore->_get_config('app_directories');
			foreach($roles_affected->result() as $role){
				$affected_users = $this->db->get_where('user', array('role_id' => $role->role_id));
				if( $affected_users->num_rows() > 0 ){
					foreach($affected_users->result() as $user){
						if( file_exists( $app_directories['user_settings_dir'] . $user->user_id . '.php' ) ) unlink( $app_directories['user_settings_dir'] . $user->user_id . '.php' );
					}
				}

				if( file_exists( $app_directories['role_settings_dir'] . $role->role_id . '.php' ) ) unlink( $app_directories['role_settings_dir'] . $role->role_id . '.php' );
			}
		}
		/* END Save Module Access */	
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
?>