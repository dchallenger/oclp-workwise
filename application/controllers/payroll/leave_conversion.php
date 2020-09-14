<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Leave_conversion extends MY_Controller
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
		$this->editview_description = 'This page allows saving/editing information about ';
	}

	// START - default module functions
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
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
 
	function detail(){
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

	function edit(){
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

	function ajax_save(){
		parent::ajax_save();

		//additional module save routine here
		$lc = $this->db->get_where('payroll_leave_conversion', array($this->key_field => $this->key_field_val))->row();

		$employees = $this->input->post('employee_id');
		$employees = explode(',', $employees);
		$amount = $this->input->post('amount');
		
		//make sure to remove deleted employees from the list
		$this->db->where('leave_convert_id', $this->key_field_val);
		$this->db->where_not_in('employee_id', $employees);
		$this->db->delete('payroll_leave_conversion_employee');
		
		foreach($employees as $employee_id){
			$this->db->where('leave_convert_id', $this->key_field_val);
			$this->db->where('employee_id', $employee_id);
			$old = $this->db->get('payroll_leave_conversion_employee');
			$discrepancy = 0;
			if($old->num_rows() == 1){
				$old = $old->row();
				//restock
				$lb = $this->db->get_where('employee_leave_balance', array('employee_id' => $employee_id, 'year' => $lc->year))->row_array();
				
				switch($lc->application_form_id){
					case 1; //sl
						$lb['sl_used'] -= $old->amount;
						break;
					case 2; //vl
					case 27; //mptl
						$lb['vl_used'] -= $old->amount;
						break;
					case 3; //el
						$lb['el_used'] -= $old->amount;
						break;
				}
				$this->db->update('employee_leave_balance', $lb, array('employee_id' => $employee_id, 'year' => $lc->year));
			}

			//deduct leave converted
			switch($lc->application_form_id){
				case 1; //sl
					$lb['sl_used'] += $amount[$employee_id];
					break;
				case 2; //vl
				case 27; //mptl
					$lb['vl_used'] += $amount[$employee_id];
					break;
				case 3; //el
					$lb['el_used'] += $amount[$employee_id];
					break;
			}
			$this->db->update('employee_leave_balance', $lb, array('employee_id' => $employee_id, 'year' => $lc->year));

			$data = array(
				'leave_convert_id' => $this->key_field_val,
				'employee_id' => $employee_id,
				'amount' => $amount[$employee_id]
			);
			$this->db->where('leave_convert_id', $this->key_field_val);
			$this->db->where('employee_id', $employee_id);
			$this->db->update('payroll_leave_conversion_employee', $data);
		}


	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function get_employee_list(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		$year = $this->input->post('year');
		$qry = "SELECT a.employee_id, b.*
		FROM {$this->db->dbprefix}employee a
		LEFT JOIN {$this->db->dbprefix}user b on b.employee_id = a.employee_id
		where YEAR(a.employed_date) < {$year} AND a.resigned_date is null and a.resigned = 0 AND a.status_id IN (1,2)
		ORDER BY b.lastname, b.firstname";

		$employees = $this->db->query( $qry );
		$option = array();
		foreach($employees->result() as $employee){
			$option[] = '<option value="'.$employee->employee_id.'">'.$employee->lastname.', '.$employee->firstname.'</option>';
		}

		$response->option = implode('', $option);

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function get_convert_form(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$data['form'] = $this->db->get_where('employee_form_type', array('application_form_id' =>  $this->input->post('application_form_id')))->row();
		$data['user'] = $this->db->get_where('user', array('employee_id' => $this->input->post('employee_id')))->row();
		$data['balance'] = $this->db->get_where('employee_leave_balance', array('year' => $this->input->post('year'), 'employee_id' => $this->input->post('employee_id')))->row();

		$response->convert_form = $this->load->view($this->userinfo['rtheme'] . '/payroll/leave_conversion/convert_form', $data, true);

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */