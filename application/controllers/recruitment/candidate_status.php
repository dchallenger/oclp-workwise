<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of candidate_status_options
 *
 * @author jconsador
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Candidate_status extends MY_Controller {

	function __construct() {
		parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = '';
		$this->jqgrid_title = "";
		$this->detailview_title = '';
		$this->detailview_description = '';
		$this->editview_title = 'Add/Edit';
		$this->editview_description = '';
	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
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
		$data['jqgrid_loadComplete'] = "";

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

	function detail() {
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';

		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
		}

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

	function edit() {
		if ($this->user_access[$this->module_id]['edit'] == 1) {
			$this->load->helper('form');

			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';

			if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

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

	function ajax_save() {
		parent::ajax_save();
		
		if ($this->input->post('hired_flag') == 1) {
			$this->db->where($this->key_field . ' !=', $this->key_field_val);
			$this->db->update($this->module_table, array ('hired_flag' => 0));
		}
		
		if ($this->input->post('rejected_flag') == 1) {
			$this->db->where($this->key_field . ' !=', $this->key_field_val);
			$this->db->update($this->module_table, array ('rejected_flag' => 0));
		}	
		
		if ($this->input->post('interview_flag') == 1) {
			$this->db->where($this->key_field . ' !=', $this->key_field_val);
			$this->db->update($this->module_table, array ('interview_flag' => 0));
		}
		
		if ($this->input->post('default') == 1) {
			$this->db->where($this->key_field . ' !=', $this->key_field_val);
			$this->db->update($this->module_table, array ('default' => 0));
		}		
	}

	function delete() {
		parent::delete();
	}

	// END - default module functions
	// START custom module funtions
}

/* End of file */
/* Location: system/application */
