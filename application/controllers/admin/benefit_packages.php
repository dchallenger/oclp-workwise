<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class benefit_packages extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists payroll accounts.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a payroll account';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a payroll account';
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

			$rec = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();
		
			$data['benefits'] = $this->db->get_where('recruitment_benefit_package_details', array('recruitment_benefit_package_id' => $rec->recruitment_benefit_package_id, 'deleted' => 0))->result(); 

		//other views to load
		$data['views'] = "";//array($this->module_link.'/detailview');

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

			$rec = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();
		
			$data['benefits'] = $this->db->get_where('recruitment_benefit_package_details', array('recruitment_benefit_package_id' => $rec->recruitment_benefit_package_id, 'deleted' => 0))->result(); 

			//other views to load
			$data['views'] = ''; //array($this->module_link.'/editview');
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
		// dbug($this->input->post('insert_benefit'));
		// dbug($this->input->post('insert_benefit_description'));
		// dbug($this->input->post('update_benefit'));
		// dbug($this->input->post('update_benefit_description'));
		// dbug($this->input->post('detail_record_id'));die;
		parent::ajax_save();

		$record_id = $this->input->post('record_id');

		$insert_benefits = $this->input->post('insert_benefit');
		$insert_benefit_descriptions = $this->input->post('insert_benefit_description');

		foreach ($insert_benefits as $key => $user_benefit_id) {
			$this->db->insert('recruitment_benefit_package_details', array('user_benefit_id' => $user_benefit_id, 'description' => $insert_benefit_descriptions[$key], 'recruitment_benefit_package_id' => $record_id));
		}

		$update_benefits = $this->input->post('update_benefit');
		$update_benefit_descriptions = $this->input->post('update_benefit_description');

		$detail_record_ids = $this->input->post('detail_record_id');

		foreach ($update_benefits as $key => $user_benefit_id) {
			$update_set_value = array('user_benefit_id' => $user_benefit_id, 'description' => $update_benefit_descriptions[$key]);
			$this->db->where('recruitment_benefit_package_detail_id', $detail_record_ids[$key]);
			$this->db->update('recruitment_benefit_package_details', $update_set_value); 
		}
	}

	function delete()
	{
		parent::delete();
	}
	// END - default module functions

	// START custom module funtions
	function delete_benefit_package_details(){
		$detail_record_id = $this->input->post('recruitment_benefit_package_detail_id');

		$this->db->delete('recruitment_benefit_package_details', array('recruitment_benefit_package_detail_id' => $detail_record_id)); 
	}

	function get_benefit_parameters(){
        $benefit = $user_benefit = $this->db->get('user_benefit');
        $benefit_html = '<select id="insert_benefit[]" class="select" name="insert_benefit[]">';

        foreach( $benefit->result() as $benefit_record ){
            $benefit_html .= '<option value="'.$benefit_record->user_benefit_id.'">'.$benefit_record->user_benefit.'</option>';
        }
        $benefit_html .= '</select>';
        $response->benefit_html = $benefit_html;

        $data['json'] = $response;
        $this->load->view('template/ajax', $data); 
	}
	// END custom module funtions
}

/* End of file */
/* Location: system/application */