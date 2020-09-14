<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Field extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Field Manager';
		$this->listview_description = 'This module lists all defined field(s).';
		$this->jqgrid_title = "Fields List";
		$this->detailview_title = 'Field Info';
		$this->detailview_description = 'This page shows detailed information about a particular field';
		$this->editview_title = 'Field Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a field.';
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
		if($this->user_access[$this->module_id]['edit'] == 1)
		{
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
		$error = FALSE;

		// Save new column first before proceeding.
		if ($this->input->post('column') == 'add') {
			$_POST['column'] = $this->input->post('column_name');
			$_POST['column-hidden'] = $this->input->post('column_name');
									
			$this->load->dbforge();

			$column = array(
						$this->input->post('column_name') => array(
								'type' => $this->input->post('field_type'),
								'constraint' => $this->input->post('field_length')
								)
					);			

			$this->dbforge->add_column($this->input->post('table'), $column);

			if ($this->db->_error_message() != '') {
				$response->msg = $this->db->_error_message();
				$response->msg_type = 'error';
				
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));

				$error = TRUE;
			}						
		}

		if (!$error) {
			parent::ajax_save();
		}
		
		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions		

	function after_ajax_save() {
		if ($this->get_msg_type() == 'success') {
			// Update tabindex to check for duplicates.
			if ($this->input->post('tabindex') <= $this->input->post('o_tabindex')) {					
				$this->db->where('tabindex >=', $this->input->post('tabindex'));					
				$this->db->where('tabindex <', $this->input->post('o_tabindex'));
				$this->db->set('tabindex', 'tabindex + 1', FALSE);
			} else {
				$this->db->where('tabindex <=', $this->input->post('tabindex'));
				$this->db->where('tabindex >=', $this->input->post('o_tabindex'));
				$this->db->set('tabindex', 'tabindex - 1', FALSE);
			}

			$this->db->where('fieldgroup_id', $this->input->post('fieldgroup_id'));
			$this->db->where('field_id <>', $this->key_field_val);
			
			$this->db->update($this->module_table);
	
		}

		parent::after_ajax_save();
	}

	/**
	 * [get_table_columns description]	 
	 * 
	 * @return null
	 */
	function get_table_columns() {
		if (IS_AJAX) {
			$table = $this->input->post('table');

			if ($this->db->table_exists($table)) {
				$response = $this->db->field_data($table);
			} else {
				$response = '0';
			}

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed. Please contact the System Administrator.');
			redirect(base_url().$this->module_link);				
		}
	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>