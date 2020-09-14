<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class My_id_numbers extends MY_Controller
{
	function __construct(){
		parent::__construct();
		
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about';
	}

	// START - default module functions
	// default jqgrid controller method
	function index(){
		$this->_edit201();
	}

	function detail(){

		//parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'employees/my_id_numbers/detailview';

		//other views to load
		$data['views'] = array();

		$result = $this->db->get_where('employee',array("employee_id"=>$this->user->user_id));

		if ($result && $result->num_rows() > 0){
			$record = $result->row();
		}

		$data['employee_record'] = $record;

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

	function ajax_save(){
		if($this->input->post('record_id')){
			$info = array(
					"sss"=>$this->input->post('sss'),
					"tin"=>$this->input->post('tin'),
					"sss_existing_loan"=>$this->input->post('sss_existing_loan'),
					"sss_current_balance"=>$this->input->post('sss_current_balance'),
					"sss_balance_date"=>date('Y-m-d',strtotime($this->input->post('sss_balance_date-temp'))),
					"tin_with_bir"=>$this->input->post('tin_with_bir'),
					"tax_status"=>$this->input->post('tax_status'),
					"philhealth"=>$this->input->post('philhealth'),
					"pagibig"=>$this->input->post('pagibig'),
					"bank_account_no"=>$this->input->post('bank_account_no'),
					"pagibig_existing_load"=>$this->input->post('pagibig_existing_load'),
					"pagibig_current_balance"=>$this->input->post('pagibig_current_balance'),
					"pagibig_balance_date"=>date('Y-m-d',strtotime($this->input->post('pagibig_balance_date-temp')))
				);

			$this->db->where('employee_id', $this->input->post('record_id'));
			$this->db->update('employee', $info);
		}

		if( $this->db->_error_message() == "" && $response->msg == "" ){
			if ($this->input->post('on_success') == "email"){
				$response->msg = 'Data has been successfully saved and sent.';
			}
			else{
				$response->msg = 'Data has been successfully saved.';
			}							
			$response->msg_type = 'success';
		}

		$response->record_id = $this->input->post('record_id');
		$this->set_message($response);
		$this->after_ajax_save();		
		//parent::ajax_save();		
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function _edit201(){

		$this->load->helper('form');
		$this->load->model( 'uitype_edit' );

		$result = $this->db->get_where('employee',array("employee_id"=>$this->user->user_id));

		if ($result && $result->num_rows() > 0){
			$record = $result->row();
		}

		$data['employee_record'] = $record;

		$data['enable_edit'] = 1;

		$this->load->vars($data);
		//additional module edit routine here
		$data['show_wizard_control'] = false;
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
		}

		$data['content'] = 'employees/my_id_numbers/editview';

		//other views to load
		$data['views'] = array();
		$data['views_outside_record_form'] = array();

		$data['buttons'] = $this->userinfo['rtheme'] . "/employees/my_id_numbers/edit-buttons";

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
	// END custom module funtions

}

/* End of file */
/* Location: system/application */