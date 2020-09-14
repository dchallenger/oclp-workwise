<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Role extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Roles List';
		$this->listview_description = 'This module lists all defined role(s).';
		$this->jqgrid_title = "Role List";
		$this->detailview_title = 'Role Info';
		$this->detailview_description = 'This page shows detailed information about a particular role.';
		$this->editview_title = 'Role Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about roles.';
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

		//additional module save routine here
		//to reset user access, delete user access file of role
		
		//save role to profile
		$profiles = $this->input->post('profile_assoc');
		$profiles = explode( ',', $profiles );
		$this->db->delete('role_profile', array($this->key_field => $this->key_field_val) );
		foreach($profiles as $profile){
			$this->db->insert('role_profile', array($this->key_field => $this->key_field_val, 'profile_id' => $profile));
		}
		
		$app_directories =  $this->hdicore->_get_config('app_directories');

		//delete access files of affected users
		$this->db->select('user_id');
		$affected_users = $this->db->get_where('user', array($this->key_field => $this->key_field_val));
		if( $affected_users->num_rows() > 0 ){
			foreach($affected_users->result() as $user){
				if( file_exists( $app_directories['user_settings_dir'] . $user->user_id . '.php' ) ) unlink( $app_directories['user_settings_dir'] . $user->user_id . '.php' );
			}
		}

		//delete role settings
		if( file_exists( $app_directories['role_settings_dir'] . $this->key_field_val . '.php' ) ) unlink( $app_directories['role_settings_dir'] . $this->key_field_val . '.php' );
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