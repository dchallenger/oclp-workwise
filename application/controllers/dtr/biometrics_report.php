<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Biometrics_report extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Biometrics Report';
		$this->listview_description = 'This module lists all defined biometrics report(s).';
		$this->jqgrid_title = "Biometrics Report List";
		$this->detailview_title = 'Biometrics Report Info';
		$this->detailview_description = 'This page shows detailed information about a particular biometrics report.';
		$this->editview_title = 'Biometrics Report Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about biometrics report(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'dtr/biometrics_report/listview';

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

		$data['division'] = $this->db->get('user_company_division')->result_array();
		$data['employee'] = $this->db->get('user')->result_array();
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

		// count query 
		//build query
		$this->_set_left_join();
		//$this->db->select('CONCAT( '.$this->db->dbprefix('user').'.firstname," ",'.$this->db->dbprefix('user').'.lastname ) as "employee"',FALSE); 
		$this->db->select($this->listview_qry, false);
		$this->db->select('uc.company');
		$this->db->select('ucd.department');
		$this->db->select('ucdv.division');
		$this->db->select('u.firstname');
		$this->db->select('u.lastname');
		$this->db->from($this->module_table);
		$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
		$this->db->join('employee e','u.employee_id = e.employee_id','left');
		$this->db->join('user_company uc','uc.company_id = u.company_id','left');
		$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
		$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		$this->db->where($this->module_table.'.date',date('Y-m-d'));
		$this->db->where($this->module_table.'.time_in1 != ""');
		
		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
		if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));

        if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $this->db->where_in('e.employee_type ', $this->input->post('employee_type'));       
        if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $this->db->where_in('e.status_id ', $this->input->post('employment_status'));       

		/*
		if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
			$this->db->where('( ( ('.$this->db->dbprefix($this->module_table).'.time_in1 >= "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" OR '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) AND '.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ) OR ( ('.$this->db->dbprefix($this->module_table).'.time_out1 <= "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" OR '.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ) AND '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) )');
		}
		else{
			$this->db->where('(( '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) OR ('.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ))');
		}
		*/

		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

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

			// record query 
			//build query
			$this->_set_left_join();
			//$this->db->select('CONCAT( '.$this->db->dbprefix('user').'.firstname," ",'.$this->db->dbprefix('user').'.lastname ) as "employee"',FALSE); 
			$this->db->select($this->listview_qry, false);
			$this->db->select('uc.company');
			$this->db->select('ucd.department');
			$this->db->select('ucdv.division');
			$this->db->select('u.firstname');
			$this->db->select('u.lastname');
			$this->db->from($this->module_table);
			$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
			$this->db->join('employee e','u.employee_id = e.employee_id','left');
			$this->db->join('user_company uc','uc.company_id = u.company_id','left');
			$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
			$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			$this->db->where($this->module_table.'.date',date('Y-m-d'));
			$this->db->where($this->module_table.'.time_in1 != ""');

			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

			if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
			if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
			if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
			if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));

	        if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $this->db->where_in('e.employee_type ', $this->input->post('employee_type'));       
	        if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $this->db->where_in('e.status_id ', $this->input->post('employment_status'));       

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
					$columns_data = $result->field_data();
					$column_type = array();
					foreach($columns_data as $column_data){
						$column_type[$column_data->name] = $column_data->type;
					}
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
								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39) ) ){
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

	function get_biometrics_report_filter(){
		$html = '';
		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1:
				$company = $this->db->get('user_company')->result_array();		
                $html .= '<select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2:
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 3:
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;		        
		    case 4:
				$employee = $this->db->get('user')->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	if ($employee_record["firstname"] != "Super Admin"){
                        	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    	}
                    }
                $html .= '</select>';	
		        break;		        		        
		}	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}
	

	function export() {	
		$query_id = '7';

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where('export_query_id', $query_id);

		$employee = "";
		$department = "";
		$company = "";
		$division = "";
		$employee_type = "";
		$employment_status = "";

		if( $this->input->post('department') ){
			$department = implode(',',$this->input->post('department'));
		}

		if( $this->input->post('employee') ){
			$employee = implode(',',$this->input->post('employee'));
		}

		if( $this->input->post('company') ){
			$company = implode(',',$this->input->post('company'));
		}

		if( $this->input->post('division') ){
			$division = implode(',',$this->input->post('division'));
		}

		if( $this->input->post('employment_status') ){
			$employee_type = implode(',',$this->input->post('employment_status'));
		}

		if( $this->input->post('employment_status') ){
			$employment_status = implode(',',$this->input->post('employment_status'));
		}

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		$sql.= " WHERE ";
		$sql_string = " ed.date = '".date('Y-m-d')."'";

		$sql_string .= ' AND ed.time_in1 != "" ';		
		//$sql_string = " 1 ";

		if( $department!= "" ){
			if( $sql_string == "" ){
				$sql_string .= "u.department_id IN (".$department.")";
			}
			else{
				$sql_string .= " AND u.department_id IN (".$department.")";
			}
		}

		if( $company!= "" ){
			if( $sql_string == "" ){
				$sql_string .= "u.company_id IN (".$company.")";
			}
			else{
				$sql_string .= " AND u.company_id IN (".$company.")";
			}
		}

		if( $employee!= "" ){
			if( $sql_string == "" ){
				$sql_string .= "u.employee_id IN (".$employee.")";
			}
			else{
				$sql_string .= " AND u.employee_id IN (".$employee.")";
			}
		}

		if( $division!= "" ){
			if( $sql_string == "" ){
				$sql_string .= "u.division_id IN (".$division.")";
			}
			else{
				$sql_string .= " AND u.division_id IN (".$division.")";
			}
		}

		if( $employee_type!= "" ){
			if( $sql_string == "" ){
				$sql_string .= "e.employee_type IN (".$employee_type.")";
			}
			else{
				$sql_string .= " AND e.employee_type IN (".$employee_type.")";
			}
		}

		if( $employment_status!= "" ){
			if( $sql_string == "" ){
				$sql_string .= "e.status_id IN (".$employment_status.")";
			}
			else{
				$sql_string .= " AND e.status_id IN (".$employment_status.")";
			}
		}		

		/*
		if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
			$this->db->where('( ( ('.$this->db->dbprefix($this->module_table).'.time_in1 >= "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" OR '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) AND '.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ) OR ( ('.$this->db->dbprefix($this->module_table).'.time_out1 <= "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" OR '.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ) AND '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) )');
		}
		else{
			$this->db->where('(( '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) OR ('.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ))');
		}

		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){

			if( $sql_string == "" ){
				$sql_string .= " ( ( ( ed.time_in1 >= '".date('Y-m-d',strtotime($this->input->post('date_period_start')))."' OR ed.time_in1 IS NULL ) AND ed.time_out1 IS NULL ) OR ( ( ed.time_out1 <= '".date('Y-m-d',strtotime($this->input->post('date_period_end')))."' OR ed.time_out1 IS NULL ) AND ed.time_in1 IS NULL ) )"; 
			}
			else{
				$sql_string .= " AND ( ( ( ed.time_in1 >= '".date('Y-m-d',strtotime($this->input->post('date_period_start')))."' OR ed.time_in1 IS NULL ) AND ed.time_out1 IS NULL ) OR ( ( ed.time_out1 <= '".date('Y-m-d',strtotime($this->input->post('date_period_end')))."' OR ed.time_out1 IS NULL ) AND ed.time_in1 IS NULL ) )"; 
			}

		}
		else{

			if( $sql_string == "" ){
				$sql_string .= " (( ed.time_in1 IS NULL ) OR ( ed.time_out1 IS NULL ))"; 
			}
			else{
				$sql_string .= " AND (( ed.time_in1 IS NULL ) OR ( ed.time_out1 IS NULL ))"; 
			}
		}
		*/
		/*
		if( $this->input->post('date_period_start') != "" ){
			if( $sql_string == "" ){
				$sql_string .= " ( ed.time_in1 >= '".date('Y-m-d',strtotime($this->input->post('date_period_start')))."')";
			}
			else{
				$sql_string .= " AND ( ed.time_in1 >= '".date('Y-m-d',strtotime($this->input->post('date_period_start')))."')";
			}
		}


		if( $this->input->post('date_period_end') != "" ){
			if( $sql_string == "" ){
				$sql_string .= " ( ed.time_out2 <= '".date('Y-m-d',strtotime($this->input->post('date_period_end')))."')";
			}
			else{
				$sql_string .= " AND ( ed.time_out2 <= '".date('Y-m-d',strtotime($this->input->post('date_period_end')))."')";
			}
		}
		*/


		$query  = $this->db->query($sql.$sql_string);

			//dbug($this->db->last_query());
			//die();


		//$query  = $this->db->query($sql);

		$fields = $query->list_fields();

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;

		$this->_excel_export();
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

			$activeSheet->setCellValueExplicit($xcoor . '6',  $field, PHPExcel_Cell_DataType::TYPE_STRING);


			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);


		}


		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValueExplicit('A2',  'Biometrics Report', PHPExcel_Cell_DataType::TYPE_STRING);

		//$activeSheet->setCellValue('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));

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


				if( $field == 'Department' ){

					$department_array = array();
					$department_record = "";
					$department_list = explode(',',$row->{$field});



					foreach( $department_list as $department ){
						if( $department > 0 ){
							$department_result = $this->db->query(" SELECT * FROM ".$this->db->dbprefix('user_company_department')." WHERE department_id = ".$department);
							$department_row = $department_result->row();

							array_push($department_array,$department_row->department);
						}
					}

					$department_record = implode(',',$department_array);

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $department_record, PHPExcel_Cell_DataType::TYPE_STRING);


				}
				else if( $field == 'Company' ){

					$company_array = array();
					$company_record = "";
					$company_list = explode(',',$row->{$field});



					foreach( $company_list as $company ){
						if( $company > 0 ){
							$company_result = $this->db->query(" SELECT * FROM ".$this->db->dbprefix('user_company')." WHERE company_id = ".$company);
							$company_row = $company_result->row();

							array_push($company_array,$company_row->company);
						}
					}

					$company_record = implode(',',$company_array);

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $company_record, PHPExcel_Cell_DataType::TYPE_STRING);

				}
				else if( $field == 'Division' ){

					$division_array = array();
					$division_record = "";
					$division_list = explode(',',$row->{$field});



					foreach( $division_list as $division ){
						if( $division > 0 ){
							$division_result = $this->db->query(" SELECT * FROM ".$this->db->dbprefix('user_company_division')." WHERE division_id = ".$division);
							$division_row = $division_result->row();

							array_push($division_array,$division_row->division);
						}
					}

					$division_record = implode(',',$division_array);

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $division_record, PHPExcel_Cell_DataType::TYPE_STRING); 

				}
				else if( $field == 'Time In' && $row->{$field} !="" ){

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date($this->config->item('display_datetime_format'),strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 

				}
				else{
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 
				}

				
				

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
    	*/

    	if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	        	$buttons .= "<div class='icon-label'><a rel='record-save' class='icon-16-export' href='javascript:void(0);' onclick='export_list();'><span>Export</span></a></div>";
	            //$buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
	            //$buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
	        }        
    	}

        
        $buttons .= "</div>";
                
		return $buttons;
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