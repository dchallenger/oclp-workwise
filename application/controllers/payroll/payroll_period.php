<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payroll_period extends MY_Controller
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
		
		$tabs = array();
		$tabs[] = '<li class="active"><a href="javascript:void(0)">Periods</li>';
        $tabs[] = '<li><a href="'.base_url('payroll/dtr_summary').'">DTR Summary</li>';
        $tabs[] = '<li><a href="'.base_url('payroll/employee_loan').'">Employee Loans</li>';
        $tabs[] = '<li><a href="'.base_url('payroll/recurring').'">Recurring Transactions</li>';
        $tabs[] = '<li><a href="'.base_url('payroll/batch_entry').'">Batch Entries</li>';
        $tabs[] = '<li><a href="'.base_url('payroll/bonus').'">Bonuses</li>';
       	$data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');

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

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";
		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
				
		if ( $this->user_access[$this->module_id]['post'] ) {
            $actions .= '<a class="icon-button icon-16-settings" tooltip="Process" href="javascript:process_period('.$record[$this->key_field].')"></a>';
            if($record['period_status_id'] == 2){
           		$actions .= '<a class="icon-button icon-16-position" tooltip="Close" href="javascript:close_period('.$record[$this->key_field].')"></a>'; 	
            }
        }
        
		if ( $this->user_access[$this->module_id]['edit'] && $record['period_status_id'] != 3 ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] &&  method_exists($this, 'print_record')  && $record['period_status_id'] == 1) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete'] && $record['period_status_id'] == 1) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}
	
	/*
	 * Payroll Period Processing
	 *
	 * @return void
	 */
	function process(){
		set_time_limit( 3600 );
		ini_set("memory_limit", "2048M");
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if( !$this->user_access[$this->module_id]['post']) {
			$response->msg = "You do not have enough privilege to execute the requested action.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}
		
		//checked passed data
		$period_id = $this->input->post('period_id');
		if( empty( $period_id ) ){
			$response->msg = "Incomplete data submitted.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}
		
		//get period detail
		$period = $this->db->get_where('payroll_period',array('payroll_period_id' => $period_id))->row();
		
		if($period->period_status_id == 3){
			$response->msg = "Period cLosed already.";
			$response->msg_type = "error";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		$processed = $this->db->get_where('payroll_period', array('deleted' => 0, 'period_status_id' => 2, 'apply_to_id' => $period->apply_to_id, 'apply_to' => $period->apply_to, 'payroll_schedule_id' => $period->payroll_schedule_id, 'period_processing_type_id' => $period->period_processing_type_id, 'payroll_period_id !=' => $period->payroll_period_id));

		$active_period_id = false;
		if($processed->num_rows() > 0){
			$active_period_id = $processed->row()->payroll_period_id;
		}

		if($active_period_id && $active_period_id != $period_id){
			$response->msg = "There is already an active period of the same nature being processed. Please close that active periods first.";
			$response->msg_type = "error";

			$this->load->view('template/ajax', array('json' => $response));
			return;	
		}
		//for reprocessing, delete all previous transactions
		switch ($period->apply_to_id) {
			// Employee
			case '1':
				$this->db->query("DELETE FROM {$this->db->dbprefix}payroll_current_transaction WHERE period_id = $period_id AND employee_id IN ($period->apply_to)");
				$this->db->query("DELETE FROM {$this->db->dbprefix}employee_contribution WHERE payroll_period_id = $period_id AND employee_id IN ($period->apply_to)");
				break;
			// Company
			case '2':
				$this->db->delete('payroll_current_transaction', array( 'period_id' => $period_id ) );
				$this->db->delete('employee_contribution', array( 'payroll_period_id' => $period_id ) );
				break;
			// Division
			case '3':
				break;
			// Department
			case '4':
				break;
			// Paycode
			case '5':
				$this->db->query("DELETE FROM {$this->db->dbprefix}payroll_current_transaction WHERE period_id = $period_id AND paycode_id IN ($period->apply_to)");
				$this->db->query("DELETE FROM {$this->db->dbprefix}employee_contribution WHERE payroll_period_id = $period_id AND paycode_id IN ($period->apply_to)");
				break;
		}

		$this->load->model('payroll');
		
		switch( $period->period_processing_type_id ){
			case 1:
				$success = $this->payroll->regular_processing( $period );
				break;
			case 2:
				$success = $this->payroll->special_processing( $period );
				break;
			case 3:
				$success = $this->payroll->finalpay_processing( $period );
		}

		if( $success ){
			$response->msg = "Success";
			$response->msg_type = "success";
		}
		else{
			$response->msg = "Error";
			$response->msg_type = "error";	
		}

		$this->db->update('payroll_period', array('period_status_id' => 2), array('payroll_period_id' => $period_id));
		$this->load->view('template/ajax', array('json' => $response));
	}

	function close(){
		set_time_limit( 7200 );
		ini_set("memory_limit", "2048M");
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if( !$this->user_access[$this->module_id]['post']) {
			$response->msg = "You do not have enough privilege to execute the requested action.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}
		
		//checked passed data
		$period_id = $this->input->post('period_id');
		if( empty( $period_id ) ){
			$response->msg = "Incomplete data submitted.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}
		
		//get period detail
		$period = $this->db->get_where('payroll_period',array('payroll_period_id' => $period_id))->row();
		
		if($period->period_status_id == 3){
			$response->msg = "Period cLosed already.";
			$response->msg_type = "error";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		if($period->period_status_id == 1){
			$response->msg = "Period is not yet processed.";
			$response->msg_type = "error";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}
		$this->db->trans_start();
			$this->load->model('payroll');
			$this->payroll->close_period( $period );
		$this->db->trans_complete() ;

			if ($this->db->trans_status() === FALSE)
			{
				$response->msg = "Failed to Close.";
				$response->msg_type = "error";
			}else{
				$response->msg = "Success.";
				$response->msg_type = "success";
			}

		$response->msg = "Success.";
		$response->msg_type = "success";

		$this->load->view('template/ajax', array('json' => $response));
	}

	function getprogress()
	{
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		$this->load->helper('file');
		$period_id = $this->input->post('period_id');
		$progressfile = 'uploads/'.$this->user->user_id . '-' . $this->module.'-'. $period_id.'-progresslog.txt';
		$progress = read_file($progressfile);
		$data['progress'] = $progress;

		$this->load->view('template/ajax', array('json' => $data));
	}

	protected function _append_to_select() 
	{
		$this->listview_qry .= ',payroll_period.period_status_id';
	}

	/**
	 * Available methods to override listview.
	 * 
	 * A. _append_to_select() - Append fields to the SELECT statement via $this->listview_qry
	 * B. _set_filter()       - Add aditional WHERE clauses	 
	 * C. _custom_join
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
									if($row['period_status_id'] != 3){
										$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );	
									}
									else{
										$cell[$cell_ctr] = '';	
									}
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

	function get_apply_to(){
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		$apply_to = $this->input->post('apply_to_id');	
		$processing_type = $this->input->post('processing_type_id');

		switch( $apply_to ) {
				case '1':
					$this->db->select($this->db->dbprefix . 'user.user_id AS value, CONCAT(' . $this->db->dbprefix .'user.firstname, " ", '. $this->db->dbprefix .'user.middlename, " ", '.$this->db->dbprefix . 'user.lastname) AS text', false);
					$where = '('.$this->db->dbprefix.'employee.resigned_date IS NULL OR '.$this->db->dbprefix.'employee.resigned_date >= "'.$this->input->post('date_from').'")';
					$this->db->where($where);
					$this->db->where('role_id <>', 1);
					$this->db->where('user.deleted', 0);

					switch(CLIENT_DIR){
						case 'pioneer':
							$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
							break;
						default:
							$this->db->join('employee', 'employee.user_id = user.user_id');
							break;	
					}

					$this->db->order_by('user.lastname','user.firstname','user.middlename');
					
					$table = 'user';
					break;
				case '2':
					$this->db->select('company as text, company_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company';
					break;
				case '3':
					$this->db->select('division as text, division_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company_division';	
					break;
				case '4':
					$this->db->select('department as text, department_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company_department';
					break;
				case '5':
					$this->db->select('paycode as text, paycode_id as value');
					$this->db->where('deleted', 0);
					$table = 'payroll_paycode';
					break;
					
				default:
					# code...
					break;
			}

			$response['json']['options'] = $this->db->get($table)->result();

			$this->load->view('template/ajax', $response);
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */