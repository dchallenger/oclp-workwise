<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Competency extends MY_Controller
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

		$this->default_sort_col = array('appraisal_competency.appraisal_competency_value_id ASC');
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
		$record_id = $this->input->post('record_id');
		if ($record_id != -1) {
			
			$levels = $this->db->get_where('appraisal_competency_level', array('appraisal_competency_id' => $this->key_field_val, 'deleted' => 0));

			if ($levels && $levels->num_rows() > 0) {
				$data['competency_levels'] = $levels->result(); 
			}
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

			$record_id = $this->input->post('record_id');
			if ($record_id != -1) {
				
				$levels = $this->db->get_where('appraisal_competency_level', array('appraisal_competency_id' => $this->key_field_val, 'deleted' => 0));

				if ($levels && $levels->num_rows() > 0) {
					$data['competency_levels'] = $levels->result(); 
				}
				
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
		// dbug($_POST);
		// die();
		parent::ajax_save();

		//additional module save routine here

		$record_id = $this->input->post('record_id');
		$levels = $this->input->post('competency_level');
		$competency_level_description = $this->input->post('competency_level_description');
		$detail['appraisal_competency_id'] =  $this->key_field_val;
		$detail['appraisal_competency_value_id'] =  $this->input->post('appraisal_competency_value_id');

		if ($record_id == '-1') {
			foreach ($levels as $key => $level) {
				$detail['appraisal_competency_level'] = $level;
				$detail['description'] = $competency_level_description[$key];
				$this->db->insert('appraisal_competency_level', $detail);
			}
		}else{

			if ($this->input->post('old_competency_level')) {
				$desc = $this->input->post('old_competency_level_description');
				foreach ($this->input->post('old_competency_level') as $id => $comp_level) {
					$detail['appraisal_competency_level']  = $comp_level;
					$detail['description']  = $desc[$id];

					$this->db->where('appraisal_competency_level_id', $id);
					$this->db->update('appraisal_competency_level', $detail);
				}

			}

			if ($this->input->post('del_competency')) {
				foreach ($this->input->post('del_competency') as $comp_id => $value) {
					$this->db->where('appraisal_competency_level_id', $comp_id);
					$this->db->set('deleted', '1');
					$this->db->update('appraisal_competency_level');
				}
			}

			if (count($levels) > 0) {
				foreach ($levels as $key => $level) {
					$detail['appraisal_competency_level'] = $level;
					$detail['description'] = $competency_level_description[$key];
					$this->db->insert('appraisal_competency_level', $detail);
				}
			}
		}
	}

	function get_values()
	{
		

		$html = '<option value=" "> </option>';
		$values = $this->db->get_where('appraisal_competency_value', array('appraisal_competency_master_id' => $this->input->post('master_id'), 'deleted' => 0));

		if( $values && $values->num_rows() > 0 ){
			// $selected = '';
			foreach( $values->result() as $competency_info ){
				if ($this->input->post('value_id') == $competency_info->competency_value_id) {
					$selected = "selected=selected";
				}else{
					$selected = '';
				}
				$html .= '<option '. $selected .'  value="'.$competency_info->competency_value_id.'" >'.$competency_info->competency_value.'</option>';

			}

		}

		$data['html'] = $html;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}


	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}


	function get_form()
	{
		if (IS_AJAX) {


			$type = $this->input->post('type');

			if ($type == "competency") {
				$data['rand'] = rand(1,100000000);
			}else{
				$data['rand'] = $this->input->post('rand');
			}

			$count = $this->input->post('count');
			
			$data['count'] = $count;

			$response = $this->load->view($this->userinfo['rtheme'] . '/employees/appraisal/master/'.$type.'_form', $data);
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