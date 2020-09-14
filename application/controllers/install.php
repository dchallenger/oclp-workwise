<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Install extends CI_Controller {

	function __construct() {		
		parent::__construct();
		
		$this->load->library('migration');
		$this->load->helper(array('form', 'string'));
	}

	function index() {
		$errors = FALSE;
		$data['content'] = 'install';
		// Add more validation here.		
		$data['install_script_exists'] = file_exists($this->db->install_file);		

		foreach ($data as $valid) {
			if (!$data) {
				$errors = TRUE;
				break;
			}
		}

		$data['errors'] = $errors;		
		$data['hash']   = random_string();
		$this->session->set_userdata('hash', $data['hash']);

		$this->load->vars($data);
		$this->load->view('install/base');
	}

	/**
	 * Displays a list of the modules for installation.
	 * @return void
	 */
	function select_modules() {
		if ($this->input->post('hash') == $this->session->userdata('hash')) {
			$data['content'] = 'modules';
			// Create the basic schema.
			$this->_install_schema();					
			
			$data['hash'] = random_string();

			$this->session->set_userdata('hash', $data['hash']);
			
			$data['modules'] = $this->_get_module_tree();

			$this->load->vars($data);
			$this->load->view('install/base');			
		} else {
			redirect('install');
		}
	}

	function create_admin() {
		if ($this->input->post('hash') == $this->session->userdata('hash')) {
			$this->_install_modules($this->input->post('module_ids'));

			$data['content'] = 'admin';

			$data['hash'] = random_string();

			$this->session->set_userdata('hash', $data['hash']);

			$this->load->vars($data);			
			$this->load->view('install/base');
		} else {
			redirect('install');
		}
	}

	function complete() {
		if ($this->input->post('hash') == $this->session->userdata('hash')) {
			$data['success'] = $this->_create_admin($this->input->post('admin'));
			if (!$data['success']) {
				$this->migration->version(0);	 
			} 

			$data['content'] = 'complete';

			$data['hash'] = random_string();

			$this->session->set_userdata('hash', $data['hash']);

			$this->load->vars($data);			
			$this->load->view('install/base');
		} else {
			redirect('install');
		}
	}	

	/**
	 * Update schema to whatever version is set on application/config/migration.php
	 * 	  
	 */
	function current() {
		if ( ! $this->migration->current())
		{
			show_error($this->migration->error_string());
		}
	}

	/**
	 * Update schema to newest version in filesystem application/migrations
	 * 	  
	 */
	function latest() {
		if ( ! $this->migration->latest())
		{
			show_error($this->migration->error_string());
		}
	}	

	private function _create_admin($params) {
		$data['password'] = md5($params['password']);
		$data['login']    = $params['username'];
		$data['role_id'] = 1;	
		$data['theme']    = 'blue';

		return $this->db->insert('user', $data);
	}

	/**
	 * Returns a multi-dimensional array of modules and their children.
	 * System and dashboard module are not included as these are necessary?.
	 * 
	 * @return array
	 */
	private function _get_module_tree() {
		$tree = array();
		// Fetch parent modules first.		
		$this->db->where('parent_id', 0);		

		$parents = $this->db->get('module')->result_array();

		foreach ($parents as $parent) {
			$tree[$parent['module_id']] = $parent;
			$tree[$parent['module_id']]['children'] = $this->hdicore->get_module_child($parent['module_id']);
		}

		return $tree;
	}

	private function _install_modules($modules = array()) {
		// Set all modules to deleted and inactive.			
		$this->db->where_not_in('module_id', $modules);

		if (!$this->db->update('module', array('deleted' => 1, 'inactive' => 1))) {
			if (ENVIRONMENT == 'development') {
				$error = $this->db->_error_message() . '<pre>' . $this->db->last_query() . '</pre>';
			} else {
				$error = 'Failed to install modules.';
			}

			show_error($error);
		}
	}

	/**
	 * Installs the basic database schema, inserts default values
	 * All modules are disabled at this point
	 * 
	 * @return [type]
	 */
	private function _install_schema() {
		if ( ! $this->migration->version(1))
		{
			show_error($this->migration->error_string());
		}
	}
}
