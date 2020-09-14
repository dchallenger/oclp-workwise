<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Competency_master extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists Competency.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about Competency';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about Competency';

		// $this->default_sort_col = array('t0.core_value_id asc');
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
		$data['competency_values'] = $this->db->get_where('appraisal_competency_value', array($this->key_field => $this->key_field_val, 'deleted' => 0));
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
			if ($this->input->post('record_id') != -1) {
				$data['competency_values'] = $this->db->get_where('appraisal_competency_value', array($this->key_field => $this->key_field_val, 'deleted' => 0));
			}
			
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
		$competency_values 		= $this->input->post('competency_value');
		$description 			= $this->input->post('competency_value_description');
		$placeholder 			= $this->input->post('competency_placeholder');

		$details['appraisal_competency_master_id']	= $this->key_field_val;

		if ($this->input->post('record_id') == '-1') { // new record
			
			foreach ($competency_values  as $key => $value) {
				$details['competency_value']  = $value;
				$details['competency_value_description']  = $description[$key];
				$details['results_placeholder'] = $placeholder[$key];

				$this->db->insert('appraisal_competency_value', $details);
			}
		}else{

			if ($this->input->post('old_competency_value')) {
				$desc	= $this->input->post('old_competency_value_description');
				$pholder = $this->input->post('old_competency_placeholder');
				foreach ($this->input->post('old_competency_value') as $id => $comp_value) {
					$details['competency_value']  = $comp_value;
					$details['competency_value_description']  = $desc[$id];
					$details['results_placeholder']  = $pholder[$id];

					$this->db->where('competency_value_id', $id);
					$this->db->update('appraisal_competency_value', $details);
				}

			}


			if ($this->input->post('del_competency')) {
				foreach ($this->input->post('del_competency') as $comp_id => $value) {
					$this->db->where('competency_value_id', $comp_id);
					$this->db->set('deleted', '1');
					$this->db->update('appraisal_competency_value');
				}
			}


			if (count($competency_values) > 0) {
				foreach ($competency_values  as $key => $value) {
					$details['competency_value']  = $value;
					$details['competency_value_description']  = $description[$key];
					$details['results_placeholder'] = $placeholder[$key];
						
					$this->db->insert('appraisal_competency_value', $details);
				}
			}
		
		}

		

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}



	function get_form()
	{
		if (IS_AJAX) {

			$response = $this->load->view($this->userinfo['rtheme'] . '/employees/appraisal/master/value_form');
			$data['html'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	// END - default module functions

	// START custom module funtions

	// END custom module funtions

}

/* End of file */
/* Location: system/application */