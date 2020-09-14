<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Aif extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Accounting Information Form';
		$this->listview_description = 'This module lists all defined accoutning information form(s).';
		$this->jqgrid_title = "Accounting Information Form List";
		$this->detailview_title = 'Accounting Information Form Info';
		$this->detailview_description = 'This page shows detailed information about a particular accounting information form.';
		$this->editview_title = 'Accounting Information Form Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about accounting information form(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/aif/listview';

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

		$this->db->order_by('firstname');
		$employee = $this->db->get('user')->result_array();

		$data['division'] = $this->db->get('user_company_division')->result_array();
		$data['employee'] = $employee;
		$data['company'] = $this->db->get('user_company')->result_array();
		$data['department'] = $this->db->get('user_company_department')->result_array();


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
		parent::ajax_save();
		
		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions


	
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
		$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);


		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->module_table.'.employee_id ',$this->input->post('employee'));

		if( $this->input->post('with_aif') && $this->input->post('with_aif') == '1' ) 
			$this->db->where_in($this->module_table.'.with_aif','1');
		else
			$this->db->where_in($this->module_table.'.with_aif','0');

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
			$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

			if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->module_table.'.employee_id ',$this->input->post('employee'));

			if( $this->input->post('with_aif') && $this->input->post('with_aif') == '1' ) 
				$this->db->where_in($this->module_table.'.with_aif','1');
			else
				$this->db->where_in($this->module_table.'.with_aif','0');

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


	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{
			$column = strtolower( $search['column'] );
			if(sizeof(explode(' as ', $column)) > 1){
				$as_part = explode(' as ', $column);
				$search['column'] = strtolower( trim( $as_part[0] ) );
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
		}
		$search_string[] = 'u.firstname LIKE "%' . $value . '%"';
		$search_string[] = 'u.lastname LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function export() {	

		$query_id = '11';

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where('export_query_id', $query_id);

		$employee = "";
		$department = "";
		$company = "";
		$division = "";


		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);
		
		$sql.= " WHERE ";

		$sql_string .= "a.employee_id = ".$this->input->post('export_employee_id');

		$sql_string .= " ";

		$query  = $this->db->query($sql.$sql_string);

		$fields = $query->list_fields();

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;

		$this->db->set('with_aif', 1);
		$this->db->where('employee_id', $this->input->post('export_employee_id'));
		$this->db->update('employee');

		$this->_pdf_export();
	}
	
	private function _pdf_export()
	{
		$export = $this->_export;

		$this->load->library('pdf');
		$html = $this->_get_html();

		// Prepare and output the PDF.
		$this->pdf->addPage();
		$this->pdf->writeHTML($html, true, false, true, false, '');
		$this->pdf->Output(date('Y-m-d').' '.$export->description . '.pdf', 'D');

	}

	private function _get_html()
	{
		$this->load->model('template');
		$template = $this->template->get_module_template($this->module_id, 'aif');

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		$results = $query->row_array();

		//Get previous employment
		$this->db->select('company');
		$this->db->select('DAYOFMONTH(to_date) AS "employed_day"');
		$this->db->select('MONTH(to_date) AS "employed_month"');
		$this->db->select('YEAR(to_date) AS "employed_year"');
		$this->db->where('employee_id',$this->input->post('export_employee_id'));
		$this->db->where('to_date = ( SELECT MAX(to_date) FROM '.$this->db->dbprefix.'employee_employment WHERE employee_id = '.$this->input->post('export_employee_id').' )');
		$employment_result = $this->db->get('employee_employment')->row();

		$results['employed_company'] = $employment_result->company;
		$results['employed_day'] = $employment_result->employed_day;
		$results['employed_month'] = $employment_result->employed_month;
		$results['employed_year'] = $employment_result->employed_year;

		// Get family dependent
		$this->db->select('name');
		$this->db->select('relationship');
		$this->db->select('birth_date');
		$this->db->select('ROUND(DATEDIFF( NOW(), birth_date ) / 365.25) AS "age"');
		$this->db->where('employee_id',$this->input->post('export_employee_id'));
		$family_result = $this->db->get('employee_family')->result();

		$family_html = "";
		$family_count = 1;

		foreach($family_result as $family){

			$family_html .= '<tr>
					<td><p style="font-family:Times; font-size:small;">'.$family_count.'.</p></td>
					<td><p style="font-family:Times; font-size:small;">'.$family->name.'</p></td>
					<td><p style="font-family:Times; font-size:small;">'.$family->relationship.'</p></td>
					<td><p style="font-family:Times; font-size:small;">'.date($this->config->item('display_date_format'),strtotime($family->birth_date)).'</p></td>
					<td><p style="font-family:Times; font-size:small;">'.$family->age.'</p></td>
			</tr>';

			$family_count++;

		}

		$results['family'] = $family_html;


		//Detect company
		$results['company_pisc'] = "";
		$results['company_piic'] = "";
		$results['company_pli'] = "";
		$results['company_bci'] = "";

		switch($results['company_id']){
			case 1:
			//'PIONEER INSURANCE & SURETY CORPORATION'
				$results['company_pisc'] = 'X';
			break;
			case 2:
			//'PIONEER INTERCONTINENTAL INSURANCE CORPORATION'
				$results['company_piic'] = 'X';
			break;
			case 3:
			//'PIONEER LIFE INC.'
				$results['company_pli'] = 'X';
			break;
			case 4:
			//'BLUE COW CO. INC.'
				$results['company_bci'] = 'X';
			break;
		}

		//Detect gender
		$results['gender_male'] = "";
		$results['gender_female'] = "";

		if( $results['gender'] == 'male' ){
			$results['gender_male'] = 'X';
		}
		else{
			$results['gender_female'] = 'X';	
		}

		//Detect With BIR Form
		$results['with_bir_form_yes'] = "";
		$results['with_bir_form_no'] = "";

		if( $results['with_bir_form'] == '1' ){
			$results['with_bir_form_yes'] = 'X';
		}
		else{
			$results['with_bir_form_no'] = 'X';	
		}

		//Detect Tax Status
		$results['tax_status_mshf'] = "";
		$results['tax_status_me1'] = "";
		$results['tax_status_me2'] = "";
		$results['tax_status_me3'] = "";
		$results['tax_status_me4'] = "";


		switch($results['tax_status']){
			case 1:
				$results['tax_status_mshf'] = 'X';
			break;
			case 2:
				$results['tax_status_me1'] = 'X';
			break;
			case 3:
				$results['tax_status_me2'] = 'X';
			break;
			case 4:
				$results['tax_status_me3'] = 'X';
			case 5:
				$results['tax_status_me4'] = 'X';
			break;
		}

		//Detect SSS Loan
		$results['sss_existing_loan_yes'] = "";
		$results['sss_existing_loan_no'] = "";

		if( $results['sss_existing_loan'] == '1' ){
			$results['sss_existing_loan_yes'] = 'X';
		}
		else{
			$results['sss_existing_loan_no'] = 'X';	
		}

		//Detect Pagibig Loan
		$results['pagibig_existing_loan_yes'] = "";
		$results['pagibig_existing_loan_no'] = "";

		if( $results['pagibig_existing_loan'] == '1' ){
			$results['pagibig_existing_loan_yes'] = 'X';
		}
		else{
			$results['pagibig_existing_loan_no'] = 'X';	
		}

		//Change Date Format
		if( ( $results['effectivity_date'] != "" && $results['effectivity_date'] != "0000-00-00" ) ){
			$results['effectivity_date'] = date($this->config->item('display_date_format'),strtotime($results['effectivity_date']));
		}

		if( ( $results['sss_current_date'] != "" && $results['sss_current_date'] != "0000-00-00" ) ){
			$results['sss_current_date'] = date($this->config->item('display_date_format'),strtotime($results['sss_current_date']));
		}

		if( ( $results['pagibig_current_date'] != "" && $results['pagibig_current_date'] != "0000-00-00" ) ){
			$results['pagibig_current_date'] = date($this->config->item('display_date_format'),strtotime($results['pagibig_current_date']));
		}

		$results['prepared_by'] = $this->userinfo['firstname'].' '.$this->userinfo['lastname'];
		$results['prepared_date'] = date($this->config->item('display_date_format'));

		$superior = $this->db->get_where('user',array('position_id'=>$results['reporting_to']))->row();

		$results['immediate_superior'] = $superior->firstname.' '.$superior->lastname;

		return $html = $this->template->prep_message($template['body'],$results);
	}

	function get_html()
	{
		$query_id = '11';

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where('export_query_id', $query_id);

		$employee = "";
		$department = "";
		$company = "";
		$division = "";


		$result = $this->db->get('export_query');
		$export = $result->row();

		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);
		
		$sql.= " WHERE ";

		$sql_string .= "a.employee_id = ".$this->input->post('export_employee_id');

		$sql_string .= " ";

		$query  = $this->db->query($sql.$sql_string);

		$fields = $query->list_fields();

		$this->load->model('template');
		$template = $this->template->get_module_template($this->module_id, 'aif');

		// $query  = $this->_query;
		// $fields = $this->_fields;
		// $export = $this->_export;

		$results = $query->row_array();

		//Get previous employment
		$this->db->select('company');
		$this->db->select('DAYOFMONTH(to_date) AS "employed_day"');
		$this->db->select('MONTH(to_date) AS "employed_month"');
		$this->db->select('YEAR(to_date) AS "employed_year"');
		$this->db->where('employee_id',$this->input->post('export_employee_id'));
		$this->db->where('to_date = ( SELECT MAX(to_date) FROM '.$this->db->dbprefix.'employee_employment WHERE employee_id = '.$this->input->post('export_employee_id').' )');
		$employment_result = $this->db->get('employee_employment')->row();

		$results['employed_company'] = $employment_result->company;
		$results['employed_day'] = $employment_result->employed_day;
		$results['employed_month'] = $employment_result->employed_month;
		$results['employed_year'] = $employment_result->employed_year;

		// Get family dependent
		$this->db->select('name');
		$this->db->select('relationship');
		$this->db->select('birth_date');
		$this->db->select('ROUND(DATEDIFF( NOW(), birth_date ) / 365.25) AS "age"');
		$this->db->where('employee_id',$this->input->post('export_employee_id'));
		$family_result = $this->db->get('employee_family')->result();

		$family_html = "";
		$family_count = 1;

		foreach($family_result as $family){

			$family_html .= '<tr>
					<td><p style="font-family:Times; font-size:small;">'.$family_count.'.</p></td>
					<td><p style="font-family:Times; font-size:small;">'.$family->name.'</p></td>
					<td><p style="font-family:Times; font-size:small;">'.$family->relationship.'</p></td>
					<td><p style="font-family:Times; font-size:small;">'.date($this->config->item('display_date_format'),strtotime($family->birth_date)).'</p></td>
					<td><p style="font-family:Times; font-size:small;">'.$family->age.'</p></td>
			</tr>';

			$family_count++;

		}

		$results['family'] = $family_html;


		//Detect company
		$results['company_pisc'] = "";
		$results['company_piic'] = "";
		$results['company_pli'] = "";
		$results['company_bci'] = "";

		switch($results['company_id']){
			case 1:
			//'PIONEER INSURANCE & SURETY CORPORATION'
				$results['company_pisc'] = 'X';
			break;
			case 2:
			//'PIONEER INTERCONTINENTAL INSURANCE CORPORATION'
				$results['company_piic'] = 'X';
			break;
			case 3:
			//'PIONEER LIFE INC.'
				$results['company_pli'] = 'X';
			break;
			case 4:
			//'BLUE COW CO. INC.'
				$results['company_bci'] = 'X';
			break;
		}

		//Detect gender
		$results['gender_male'] = "";
		$results['gender_female'] = "";

		if( $results['gender'] == 'male' ){
			$results['gender_male'] = 'X';
		}
		else{
			$results['gender_female'] = 'X';	
		}

		//Detect With BIR Form
		$results['with_bir_form_yes'] = "";
		$results['with_bir_form_no'] = "";

		if( $results['with_bir_form'] == '1' ){
			$results['with_bir_form_yes'] = 'X';
		}
		else{
			$results['with_bir_form_no'] = 'X';	
		}

		//Detect Tax Status
		$results['tax_status_mshf'] = "";
		$results['tax_status_me1'] = "";
		$results['tax_status_me2'] = "";
		$results['tax_status_me3'] = "";
		$results['tax_status_me4'] = "";


		switch($results['tax_status']){
			case 1:
				$results['tax_status_mshf'] = 'X';
			break;
			case 2:
				$results['tax_status_me1'] = 'X';
			break;
			case 3:
				$results['tax_status_me2'] = 'X';
			break;
			case 4:
				$results['tax_status_me3'] = 'X';
			case 5:
				$results['tax_status_me4'] = 'X';
			break;
		}

		//Detect SSS Loan
		$results['sss_existing_loan_yes'] = "";
		$results['sss_existing_loan_no'] = "";

		if( $results['sss_existing_loan'] == '1' ){
			$results['sss_existing_loan_yes'] = 'X';
		}
		else{
			$results['sss_existing_loan_no'] = 'X';	
		}

		//Detect Pagibig Loan
		$results['pagibig_existing_loan_yes'] = "";
		$results['pagibig_existing_loan_no'] = "";

		if( $results['pagibig_existing_loan'] == '1' ){
			$results['pagibig_existing_loan_yes'] = 'X';
		}
		else{
			$results['pagibig_existing_loan_no'] = 'X';	
		}

		//Change Date Format
		if( ( $results['effectivity_date'] != "" && $results['effectivity_date'] != "0000-00-00" ) ){
			$results['effectivity_date'] = date($this->config->item('display_date_format'),strtotime($results['effectivity_date']));
		}

		if( ( $results['sss_current_date'] != "" && $results['sss_current_date'] != "0000-00-00" ) ){
			$results['sss_current_date'] = date($this->config->item('display_date_format'),strtotime($results['sss_current_date']));
		}

		if( ( $results['pagibig_current_date'] != "" && $results['pagibig_current_date'] != "0000-00-00" ) ){
			$results['pagibig_current_date'] = date($this->config->item('display_date_format'),strtotime($results['pagibig_current_date']));
		}

		$results['prepared_by'] = $this->userinfo['firstname'].' '.$this->userinfo['lastname'];
		$results['prepared_date'] = date($this->config->item('display_date_format'));

		$superior = $this->db->get_where('user',array('position_id'=>$results['reporting_to']))->row();

		$results['immediate_superior'] = $superior->firstname.' '.$superior->lastname;

		$html = $this->template->prep_message($template['body'],$results);

		// $data['content'] = 'export_boxy';
		$response->html = $html;

		$this->load->view('template/ajax', array('json' => $response));
	}

	function module_export_options() {				
		$data['content'] = 'export_boxy';
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['module_id'] = $this->input->post('module_id');		

		if (IS_AJAX) {	
			$response['json']['html'] = $this->load->view($data['content'], $data, TRUE);
			$this->load->view('template/ajax', $response);
		}
	}


	private function _excel_export()
	{

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		foreach ($fields as $field) {
			
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			$activeSheet->setCellValue($xcoor . '6', $field);

			$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . '6',  $field, PHPExcel_Cell_DataType::TYPE_STRING);


			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);


		}


		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValueExplicit('A2',  'Service Award Report', PHPExcel_Cell_DataType::TYPE_STRING);


		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		

		// contents.
		$line = 7;

		foreach ($query->result() as $row) {
			$sub_ctr   = 0;
			$alpha_ctr = 0;			
			
			foreach ($fields as $field) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}


					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING);


				$alpha_ctr++;
			}

			$line++;
		}

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title($export->description) . '.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";



		$buttons = "<div class='icon-label-group'>";                    


		/*
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }

        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
	            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
	        }        
    	}
    	

    	if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	        	$buttons .= "<div class='icon-label'><a rel='record-save' class='icon-16-export' href='javascript:void(0);' onclick='export_list();'><span>Export</span></a></div>";
	            //$buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
	            //$buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
	        }        
    	}
		*/
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {
		$rec = $this->db->get_where( $this->module_table, array( $this->key_field => $row[$this->key_field] ) )->row();

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		// Right align action buttons.
		$actions = '<span class="icon-group">';

		$actions .= '<a class="icon-button icon-16-export" tooltip="Export" onclick="show_form('.$row[$this->key_field].')" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';

		$actions .= '</span>';

		return $actions;
	}

	

	function _set_specific_search_query()
	{
		$field = $this->input->post('searchField');
		$operator =  $this->input->post('searchOper');
		$value =  $this->input->post('searchString');


		if($field == "employee_dtr.time_in1"){

			$value = date('Y-m-d h:i:s',strtotime($value));

		}
		

		foreach( $this->search_columns as $search )
		{
			if($search['jq_index'] == $field) $field = $search['column'];
		}

		$field = strtolower( $field );
		if(sizeof(explode(' as ', $field)) > 1){
			$as_part = explode(' as ', $field);
			$field = strtolower( trim( $as_part[0] ) );
		}


		switch ($operator) {
			case 'eq':
				return $field . ' = "'.$value.'"';
				break;
			case 'ne':
				return $field . ' != "'.$value.'"';
				break;
			case 'lt':
				return $field . ' < "'.$value.'"';
				break;
			case 'le':
				return $field . ' <= "'.$value.'"';
				break;
			case 'gt':
				return $field . ' > "'.$value.'"';
				break;
			case 'ge':
				return $field . ' >= "'.$value.'"';
				break;
			case 'bw':
				return $field . ' REGEXP "^'. $value .'"';
				break;
			case 'bn':
				return $field . ' NOT REGEXP "^'. $value .'"';
				break;
			case 'in':
				return $field . ' IN ('. $value .')';
				break;
			case 'ni':
				return $field . ' NOT IN ('. $value .')';
				break;
			case 'ew':
				return $field . ' LIKE "%'. $value  .'"';
				break;
			case 'en':
				return $field . ' NOT LIKE "%'. $value  .'"';
				break;
			case 'cn':
				return $field . ' LIKE "%'. $value .'%"';
				break;
			case 'nc':
				return $field . ' NOT LIKE "%'. $value .'%"';
				break;
			default:
				return $field . ' LIKE %'. $value .'%';
		}
	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>