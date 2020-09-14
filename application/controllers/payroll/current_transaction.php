<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Current_transaction extends MY_Controller
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
		$data['scripts'][] = chosen_script();
		$data['content'] = $this->module_link.'/listview';
		$data['jqgrid'] = $this->module_link.'/jqgrid';
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		$this->load->helper('form');
		$this->load->model('uitype_base');

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
	}

	function after_ajax_save(){
		$record = $this->db->get_where('payroll_current_transaction',array('current_transaction_id' => $this->key_field_val))->row();
		$period = $this->db->get_where('payroll_period', array('payroll_date' => $record->payroll_date, 'period_processing_type_id' => $record->processing_type_id))->row();
		
		if( empty($record->period_id) || $record->period_id == "" ){
			$transaction = $this->db->get_where('payroll_transaction', array('transaction_id' => $record->transaction_id))->row();
			$this->db->where( array($this->key_field => $this->key_field_val) );
			$data = array(
				'period_id' => $period->payroll_period_id,
				'transaction_type_id' => $transaction->transaction_type_id,
				'transaction_code' => $transaction->transaction_code
			);
			$this->db->update('payroll_current_transaction', $data);
		}

		$this->load->model('payroll');
		$this->payroll->_get_local_setup( $period );

		$qry = "select a.*, b.*, c.*
		FROM {$this->db->dbprefix}employee_payroll a
		LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
		LEFT JOIN {$this->db->dbprefix}employee c on c.employee_id = b.employee_id
		WHERE b.employee_id = {$record->employee_id} AND a.payroll_schedule_id = {$period->payroll_schedule_id}
		AND b.deleted = 0 AND b.inactive = 0 AND a.quitclaim = 0 AND c.employee_id is not null";
		$employee = $this->db->query( $qry )->row();

		//decode salaries
		$this->load->library('encrypt');
		$employee->salary = $this->encrypt->decode( $employee->salary );
		$employee->minimum_takehome = $this->encrypt->decode( $employee->minimum_takehome );

		if($record->transaction_code != 'WHTAX'){
			switch($employee->tax_mode){
				case '1': //tax table
					$whtax = $this->payroll->_get_whtax( $employee, $period );
					break;
				case '2': //annualized
					$salary =  $this->payroll->_get_employee_salary_for_period( $employee );
					$whtax = $this->payroll->_get_annualized_whtax( $employee, $period, $salary );
					break;
				case '3': //manual
					break;
				case '4': //cummulative
					break;
			}

			$this->db->where(array('transaction_code' => 'WHTAX', 'period_id' => $period->payroll_period_id));
			$this->db->update('payroll_current_transaction', array('unit_rate' => $whtax, 'amount' =>$whtax)); 
		}	
		
		//unhold all transaction
		$this->payroll->_unhold_transactions($employee, $period);
		$netpay = $this->payroll->_get_employee_netpay( $employee, $period );
		
		if( $netpay < $employee->minimum_takehome ){
			$deductions = $this->payroll->_get_transaction( $employee, $period, '3,4', 'result',  true, true, false );
			if($deductions){
				foreach( $deductions as $deduction ){
					$netpay += 	$deduction->amount;
					$this->db->update('payroll_current_transaction', array('on_hold' => 1), array('current_transaction_id' => $deduction->current_transaction_id ));
					if($netpay > $employee->minimum_takehome ) break;
				}
			}
		}

		$netpaytrans = $this->db->get_where('payroll_current_transaction', array('transaction_code' => 'NETPAY', 'period_id' => $period->payroll_period_id))->row_array();
		$netpaytrans['unit_rate'] = $netpaytrans['amount'] = $netpay;
		$this->db->delete('payroll_current_transaction', array('transaction_code' => 'NETPAY', 'period_id' => $period->payroll_period_id));
		unset($netpaytrans['current_transaction_id']);
		$this->db->insert('payroll_current_transaction', $netpaytrans);

		if( !empty( $employee->resigned_date ) ){
			if( $employee->resigned_date >= $period->date_from &&  $employee->resigned_date <= $period->date_to ){
				//hold all transaction
				$this->db->update('payroll_current_transaction', array('on_hold' => 1, 'processing_type_id' => 3), array('employee_id' => $employee->employee_id));
			}
		}

		parent::after_ajax_save();
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

		/**
	 * Available methods to override listview.
	 * 
	 * A. _append_to_select() - Append fields to the SELECT statement via $this->listview_qry
	 * B. _set_filter()       - Add aditional WHERE clauses	 
	 * 
	 * @return json
	 */
	function listview()
	{
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions );

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


		if (method_exists($this, '_append_to_select')) {
			// Append fields to the SELECT statement via $this->listview_qry
			$this->_append_to_select();
		}

		if (method_exists($this, '_custom_join')) {
			$this->_custom_join();
		}

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

		if($this->input->post('processing_type_id')){
			$this->db->where('payroll_current_transaction.processing_type_id', $this->input->post('processing_type_id'));
		}

		if($this->input->post('employee_id')){
			$this->db->where('payroll_current_transaction.employee_id', $this->input->post('employee_id'));
		}

		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$result = $this->db->get();
		//$response->last_query = $this->db->last_query();
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $result->num_rows();

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

			if($this->input->post('processing_type_id')){
				$this->db->where('payroll_current_transaction.processing_type_id', $this->input->post('processing_type_id'));
			}

			if($this->input->post('employee_id')){
				$this->db->where('payroll_current_transaction.employee_id', $this->input->post('employee_id'));
			}

			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			
			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}

			if (method_exists($this, '_custom_join')) {
				// Append fields to the SELECT statement via $this->listview_qry
				$this->_custom_join();
			}
			
			if($sidx != ""){
				$this->db->order_by($sidx, $sord);
			}
			else{
				if( is_array($this->default_sort_col) ){
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);
			
			$result = $this->db->get();
			//$response->last_query = $this->db->last_query();

			//check what column to add if this is a related module
			if($related_module){
				foreach($this->listview_columns as $column){                                    
					if($column['name'] != "action"){
						$temp = explode('.', $column['name']);
						if(strpos($this->input->post('column'), ',')){
							$column_lists = explode( ',', $this->input->post('column'));
							if( sizeof($temp) > 1 && in_array($temp[1], $column_lists ) ) $column_to_add[] = $column['name'];
						}
						else{
							if( sizeof($temp) > 1  && $temp[1] == $this->input->post('column')) $this->related_module_add_column = $column['name'];
						}
					}
				}
				//in case specified related column not in listview columns, default to 1st column
				if( !isset($this->related_module_add_column) ){
					if(sizeof($column_to_add) > 0)
						$this->related_module_add_column = implode('~', $column_to_add );
					else
						$this->related_module_add_column = $this->listview_columns[0]['name'];
				}
			}

			if( $this->db->_error_message() != "" ){
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			}
			else{
				$response->rows = array();
				if($result->num_rows() > 0){
					$this->load->model('uitype_listview');
					$ctr = 0;
					foreach ($result->result_array() as $row){
						$cell = array();
						$cell_ctr = 0;
						foreach($this->listview_columns as $column => $detail){
							if( preg_match('/\./', $detail['name'] ) ) {
								$temp = explode('.', $detail['name']);
								$detail['name'] = $temp[1];
							}
							
							if(sizeof(explode(' AS ', $detail['name'])) > 1 ){
								$as_part = explode(' AS ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							else if(sizeof(explode(' as ', $detail['name'])) > 1 ){
								$as_part = explode(' as ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							
							if( $detail['name'] == 'action'  ){
								if( $view_actions ){
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );
									$cell_ctr++;
								}
							}else{
								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 40) ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 3 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] ) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 39 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['type'] ) && $this->listview_fields[$cell_ctr]['other_info']['type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 39 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['type'] ) && $this->listview_fields[$cell_ctr]['other_info']['type'] != 'Query' ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else{
									$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
								}

								if($detail['name'] == 'transaction_id' && !empty($row['record_from']) && $row['record_from'] == 'employee_loan'){
									$cell[$cell_ctr] .= $this->_get_loan_detail($row);	
								}

								$cell_ctr++;
							}
						}
						$response->rows[$ctr]['id'] = $row[$this->key_field];
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}
		
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}


	protected function _append_to_select() 
	{
		$this->listview_qry .= ',payroll_current_transaction.period_id,payroll_current_transaction.inserted_from_id,payroll_current_transaction.record_from,payroll_current_transaction.record_id';
	}

	function _custom_join(){
		$this->db->join('payroll_period', 'payroll_period.payroll_period_id = payroll_current_transaction.period_id', 'left');
	}

	function _get_loan_detail( $record ){
		$emp_loan = $this->db->get_where('employee_loan', array('employee_loan_id' => $record['record_id']))->row();
		$loan = $this->db->get_where('payroll_loan', array('loan_id' => $emp_loan->loan_id))->row();
		return '<br/><em>'.$loan->loan.'</em>';
	}

	function get_unit_rate(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

		$employee_id = $this->input->post('employee_id');
		$transaction_id = $this->input->post('transaction_id');

		if( $employee_id == '' && $transaction_id == '' ){
			$response->msg = "";
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
			return;
		}

		$this->load->model('payroll');
		$this->payroll->_load_day_rates();

		//get employee detail
		$qry = "select a.*, b.*, c.*
		FROM {$this->db->dbprefix}employee_payroll a
		LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
		LEFT JOIN {$this->db->dbprefix}employee c on c.employee_id = b.employee_id
		WHERE a.employee_id = {$employee_id}
		AND b.deleted = 0 AND b.inactive = 0 AND a.quitclaim = 0 AND c.employee_id is not null";

		$employee = $this->db->query( $qry )->row();
		//decode salaries
		$employee->salary = $this->encrypt->decode( $employee->salary );
		$employee->minimum_takehome = $this->encrypt->decode( $employee->minimum_takehome );
		
		$total_year_days = (float)$employee->total_year_days;
		$employee->total_year_days = empty( $total_year_days ) ? $this->config->item('total_year_days')  : $employee->total_year_days;

		//get salary
		$salary =  $this->payroll->_get_employee_salary_for_period( $employee );

		$qry = "select a.*, b.transaction_class_code
		FROM {$this->db->dbprefix}payroll_transaction a
		LEFT JOIN {$this->db->dbprefix}payroll_transaction_class b ON b.transaction_class_id = a.transaction_class_id
		WHERE a.transaction_id = {$transaction_id}";
		$transaction = $this->db->query( $qry )->row();
		
		$response->unit_rate = $this->payroll->get_unit_rate( $transaction, $salary );

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function update_employee_ddlb(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
		
		$processing_type_id	= $this->input->post('processing_type_id');

		$response->option = '';

        if ($this->user_access[$this->module_id]['post']) {
         	$qry = "SELECT b.employee_id, b.firstname, b.lastname, b.middleinitial, b.aux
         	FROM {$this->db->dbprefix}payroll_current_transaction a
         	LEFT JOIN {$this->db->dbprefix}user b on b.employee_id = a.employee_id
         	WHERE a.processing_type_id = '{$processing_type_id}'
         	GROUP BY a.employee_id ORDER BY b.firstname";
         	
         	$employees = $this->db->query( $qry );
         	if( $employees->num_rows() > 0 ){
         		foreach($employees->result() as $row){
         			$response->option .= '<option value="'.$row->employee_id.'">'.$row->firstname.' '.$row->middleinitial.' '.$row->lastname.' '.$row->aux.'</option>';
         		}
         	}
        }
        else{
        	$subordinates = array();
			$result = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ));
			if ($result){
	            if ($result->num_rows() > 0){
	                $emp = $result->row();
	                $subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
	            }
	        }
        }

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */