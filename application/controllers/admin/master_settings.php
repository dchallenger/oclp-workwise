<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Master_settings extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

        $this->listview_title = 'Settings';
	}

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['content'] = 'master_dashboard';
	
		$parent = $this->hdicore->get_module('master');

		$data['subgroups'] = $this->hdicore->_create_navigation($parent->module_id, $this->user_access);		

		$this->load->vars($data);
		
		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );
		
		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/template/page-content' );
		
		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
    }	
}

/* End of file */
/* Location: system/application */