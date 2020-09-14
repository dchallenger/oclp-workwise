<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_company_department extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Company Department';
		$this->listview_description = 'This module lists all defined company department(s).';
		$this->jqgrid_title = "Department List";
		$this->detailview_title = 'Company Department Info';
		$this->detailview_description = 'This page shows detailed information about a particular department.';
		$this->editview_title = 'Company Department Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about department(s).';
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
			$data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			$data['scripts'][] = chosen_script();
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
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
				
	}

	function after_ajax_save()
	{	
		if( $this->input->post('transaction_id') ){
			$this->db->delete('department_account_mapping', array('department_id' => $this->key_field_val));
			$transactions = $this->input->post('transaction_id');
			$debit = $this->input->post('debit_account_id');
			$credit = $this->input->post('credit_account_id');
			$transaction_label_override = $this->input->post('transaction_label_override');
			foreach($transactions as $transaction_id => $value){
				$mapping = array(
					'department_id' => $this->key_field_val,
					'transaction_id' => $transaction_id,
					'credit_account_id' => $credit[$transaction_id],
					'debit_account_id' => $debit[$transaction_id],
					'transaction_label_override' => $transaction_label_override[$transaction_id]
				);
				$this->db->insert('department_account_mapping', $mapping);
			}
		}
		parent::after_ajax_save();				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions
	function get_position(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		$response = $this->hdicore->_get_userinfo( $this->input->post('user_id') );
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function get_user_via_position(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$qry = "select a.user_id
		FROM {$this->db->dbprefix}user a
		LEFT JOIN {$this->db->dbprefix}employee b on b.user_id = a.user_id
		WHERE a.deleted = 0 AND a.inactive = 0 AND a.position_id = {$this->input->post('position_id')}
		AND b.resigned = 0 and b.resigned_date is null";
		$users = $this->db->query( $qry )->row();

		$response = $users;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);	
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>