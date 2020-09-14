<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Billable_hours_calls extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'OT Billable Hours And Calls Report';
		$this->listview_description = 'This module lists all defined biometrics report(s).';
		$this->jqgrid_title = "OT Billable Hours And Calls List";
		$this->detailview_title = 'OT Billable Hours And Calls Info';
		$this->detailview_description = 'This page shows detailed information about a particular biometrics report.';
		$this->editview_title = 'OT Billable Hours And Calls Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about biometrics report(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';
		$data['scripts'][] = chosen_script();

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


	
	// function listview()
	// {
	// 	$response->msg = "";

	// 	$page = $this->input->post('page');
	// 	$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
	// 	$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
	// 	$sord = $this->input->post('sord'); // get the direction
	// 	$related_module = ( $this->input->post('related_module') ? true : false );

	// 	$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

	// 	//set columnlist and select qry
	// 	$this->_set_listview_query( '', $view_actions );

	// 	//set Search Qry string
	// 	if($this->input->post('_search') == "true")
	// 		$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
	// 	else
	// 		$search = 1;

	// 	if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


	// 	if (method_exists($this, '_append_to_select')) {
	// 		// Append fields to the SELECT statement via $this->listview_qry
	// 		$this->_append_to_select();
	// 	}

	// 	if (method_exists($this, '_custom_join')) {
	// 		$this->_custom_join();
	// 	}

	// 	// count query 
	// 	//build query
	// 	$this->_set_left_join();
	// 	//$this->db->select('CONCAT( '.$this->db->dbprefix('user').'.firstname," ",'.$this->db->dbprefix('user').'.lastname ) as "employee"',FALSE); 
	// 	$this->db->select($this->listview_qry, false);
	// 	$this->db->select('uc.company');
	// 	$this->db->select('ucd.department');
	// 	$this->db->select('ucdv.division');
	// 	$this->db->select('u.firstname');
	// 	$this->db->select('u.lastname');
	// 	$this->db->from($this->module_table);
	// 	$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
	// 	$this->db->join('user_company uc','uc.company_id = u.company_id','left');
	// 	$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
	// 	$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
	// 	$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
	// 	$this->db->where($this->module_table.'.date',date('Y-m-d'));
	// 	$this->db->where($this->module_table.'.time_in1 != ""');
		
	// 	if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
	// 	if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
	// 	if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
	// 	if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));

	// 	/*
	// 	if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
	// 		$this->db->where('( ( ('.$this->db->dbprefix($this->module_table).'.time_in1 >= "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" OR '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) AND '.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ) OR ( ('.$this->db->dbprefix($this->module_table).'.time_out1 <= "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" OR '.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ) AND '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) )');
	// 	}
	// 	else{
	// 		$this->db->where('(( '.$this->db->dbprefix($this->module_table).'.time_in1 IS NULL ) OR ('.$this->db->dbprefix($this->module_table).'.time_out1 IS NULL ))');
	// 	}
	// 	*/

	// 	if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

	// 	if (method_exists($this, '_set_filter')) {
	// 		$this->_set_filter();
	// 	}

	// 	//get list
	// 	$result = $this->db->get();
	// 	//$response->last_query = $this->db->last_query();

	// 	if( $this->db->_error_message() != "" ){
	// 		$response->msg = $this->db->_error_message();
	// 		$response->msg_type = "error";
	// 	}
	// 	else{
	// 		$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
	// 		$response->page = $page > $total_pages ? $total_pages : $page;
	// 		$response->total = $total_pages;
	// 		$response->records = $result->num_rows();

	// 		// record query 
	// 		//build query
	// 		$this->_set_left_join();
	// 		//$this->db->select('CONCAT( '.$this->db->dbprefix('user').'.firstname," ",'.$this->db->dbprefix('user').'.lastname ) as "employee"',FALSE); 
	// 		$this->db->select($this->listview_qry, false);
	// 		$this->db->select('uc.company');
	// 		$this->db->select('ucd.department');
	// 		$this->db->select('ucdv.division');
	// 		$this->db->select('u.firstname');
	// 		$this->db->select('u.lastname');
	// 		$this->db->from($this->module_table);
	// 		$this->db->join('user u','u.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id','left');
	// 		$this->db->join('user_company uc','uc.company_id = u.company_id','left');
	// 		$this->db->join('user_company_department ucd','ucd.department_id = u.department_id','left');
	// 		$this->db->join('user_company_division ucdv','ucdv.division_id = u.division_id','left');
	// 		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
	// 		$this->db->where($this->module_table.'.date',date('Y-m-d'));
	// 		$this->db->where($this->module_table.'.time_in1 != ""');

	// 		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

	// 		if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('ucd.department_id ',$this->input->post('department'));
	// 		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('uc.company_id ',$this->input->post('company'));
	// 		if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('ucdv.division_id ',$this->input->post('division'));
	// 		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));

	// 		if (method_exists($this, '_set_filter')) {
	// 			$this->_set_filter();
	// 		}

	// 		if (method_exists($this, '_custom_join')) {
	// 			// Append fields to the SELECT statement via $this->listview_qry
	// 			$this->_custom_join();
	// 		}
			
	// 		if($sidx != ""){
	// 			$this->db->order_by($sidx, $sord);
	// 		}
	// 		else{
	// 			if( is_array($this->default_sort_col) ){
	// 				$sort = implode(', ', $this->default_sort_col);
	// 				$this->db->order_by($sort);
	// 			}
	// 		}
	// 		$start = $limit * $page - $limit;
	// 		$this->db->limit($limit, $start);
			
	// 		$result = $this->db->get();

	// 		//$response->last_query = $this->db->last_query();

	// 		//check what column to add if this is a related module
	// 		if($related_module){
	// 			foreach($this->listview_columns as $column){                                    
	// 				if($column['name'] != "action"){
	// 					$temp = explode('.', $column['name']);
	// 					if(strpos($this->input->post('column'), ',')){
	// 						$column_lists = explode( ',', $this->input->post('column'));
	// 						if( sizeof($temp) > 1 && in_array($temp[1], $column_lists ) ) $column_to_add[] = $column['name'];
	// 					}
	// 					else{
	// 						if( sizeof($temp) > 1  && $temp[1] == $this->input->post('column')) $this->related_module_add_column = $column['name'];
	// 					}
	// 				}
	// 			}
	// 			//in case specified related column not in listview columns, default to 1st column
	// 			if( !isset($this->related_module_add_column) ){
	// 				if(sizeof($column_to_add) > 0)
	// 					$this->related_module_add_column = implode('~', $column_to_add );
	// 				else
	// 					$this->related_module_add_column = $this->listview_columns[0]['name'];
	// 			}
	// 		}

	// 		if( $this->db->_error_message() != "" ){
	// 			$response->msg = $this->db->_error_message();
	// 			$response->msg_type = "error";
	// 		}
	// 		else{
	// 			$response->rows = array();
	// 			if($result->num_rows() > 0){
	// 				$columns_data = $result->field_data();
	// 				$column_type = array();
	// 				foreach($columns_data as $column_data){
	// 					$column_type[$column_data->name] = $column_data->type;
	// 				}
	// 				$this->load->model('uitype_listview');
	// 				$ctr = 0;
	// 				foreach ($result->result_array() as $row){
	// 					$cell = array();
	// 					$cell_ctr = 0;
	// 					foreach($this->listview_columns as $column => $detail){
	// 						if( preg_match('/\./', $detail['name'] ) ) {
	// 							$temp = explode('.', $detail['name']);
	// 							$detail['name'] = $temp[1];
	// 						}
							
	// 						if(sizeof(explode(' AS ', $detail['name'])) > 1 ){
	// 							$as_part = explode(' AS ', $detail['name']);
	// 							$detail['name'] = strtolower( trim( $as_part[1] ) );
	// 						}
	// 						else if(sizeof(explode(' as ', $detail['name'])) > 1 ){
	// 							$as_part = explode(' as ', $detail['name']);
	// 							$detail['name'] = strtolower( trim( $as_part[1] ) );
	// 						}
							
	// 						if( $detail['name'] == 'action'  ){
	// 							if( $view_actions ){
	// 								$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );
	// 								$cell_ctr++;
	// 							}
	// 						}else{
	// 							if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39) ) ){
	// 								$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
	// 								$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
	// 							}
	// 							else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 3 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] ) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' ) ){
	// 								$cell[$cell_ctr] = "";
	// 								foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
	// 								{
	// 									if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
	// 								}
	// 							}
	// 							else{
	// 								$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
	// 							}
	// 							$cell_ctr++;
	// 						}
	// 					}
	// 					$response->rows[$ctr]['id'] = $row[$this->key_field];
	// 					$response->rows[$ctr]['cell'] = $cell;
	// 					$ctr++;
	// 				}
	// 			}
	// 		}
	// 	}
		
	// 	$data['json'] = $response;                		
	// 	$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	// }

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
		$query_id = '13';

		$data = $this->db->get_where('billable_period', array('billable_period_id' => $this->uri->rsegment(3)))->row();

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where('export_query_id', $query_id);

		$campaign = "";

		if(isset($data->campaign) && $data->campaign != 0)
			$campaign = $data->campaign;

		$result = $this->db->get('export_query');

		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		$sql.= " WHERE ";
		$sql_string .= $this->db->dbprefix."employee_dtr.date >= '".date('Y-m-d', strtotime($data->from))."' AND ";
		$sql_string .= $this->db->dbprefix."employee_dtr.date <= '".date('Y-m-d', strtotime($data->to))."'";
		if(isset($data->campaign) && $data->campaign != 0)
			$sql_string .= " AND ".$this->db->dbprefix."employee.campaign_id = '".$data->campaign."'";
		$sql_string .= " ORDER BY ".$this->db->dbprefix."user.lastname ASC";

		$query  = $this->db->query($sql.$sql_string);

		$fields = $query->list_fields();

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;
		$this->_data = $data;

		if (CLIENT_DIR == 'oams') {
				$this->_oams_excel_export();	
		}else{
			$this->_excel_export();
		}
		// $this->_excel_export();
	}
	
	private function _excel_export()
	{

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		$_POST['date_period_start'] = $this->_data->from;
		$_POST['date_period_end'] = $this->_data->to;
		$_POST['campaign'] = $this->_data->campaign;

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
		foreach($alphabet as $letter)
			array_push($alphabet, 'A'.$letter);
		foreach($alphabet as $letter)
			array_push($alphabet, 'B'.$letter);

		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);


		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$cellarray = array(
			'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'CCC'),
            ),

			'font' => array(
				'bold' => true,
			),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$totalnumstyle = array(
			'font' => array(
				'bold' => true,
				),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				)
			);

		$totaltitlestyle = array(
			'font' => array(
				'bold' => true,
				),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);

		$headerstyle = array(
			'font' => array(
				'bold' => true,
				),
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  )
			);
		$HorizontalCenter = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$styleArrayBorder = array(
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  )
			);		
		
		$top_ctr = 0;
		$activeSheet->setCellValue('A6', 'No.');
		$activeSheet->setCellValue('B6', 'Name');
		$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($headerstyle);
		$cdate = $this->input->post('date_period_start');
		$mdate = strtotime($this->input->post('date_period_start'));
		$alpha_ctr = 2;
		$edate = $this->input->post('date_period_end');
		$ctr=0;
		$dates_affected = array();
		$stop=true;
		$position_start_ot_date = "";

		for($x=0;$x<2;$x++) {
			$ctr_for_date = 0;
			while($mdate <= strtotime($edate)) {
				$xcoor = $alphabet[$alpha_ctr];
				$cdate = date('d-M-y', strtotime($cdate));
				$activeSheet->setCellValue($xcoor.'6', $cdate);
				// style
				$objPHPExcel->getActiveSheet()->getStyle($xcoor.'6')->applyFromArray($headerstyle);
				// style
				// echo $xcoor."|";
				if($stop == true)
					$dates_affected[] = $cdate;
				$cdate = date('d-M-y', strtotime('+1 day', strtotime($cdate)));
				$mdate = strtotime($cdate);
				$alpha_ctr++;
				$ctr_for_date++;
			}

			$stop=false;
			$xcoor = $alphabet[$alpha_ctr];
			if($x==0) {
				$position_tot_reg = $alphabet[$alpha_ctr];
				$activeSheet->setCellValue($xcoor.'6', 'Total Regular Hours' );
				$objPHPExcel->getActiveSheet()->getStyle($xcoor.'6')->applyFromArray($headerstyle);
			} else
			{
				$position_tot_ot = $alphabet[$alpha_ctr];
				$activeSheet->setCellValue($xcoor.'6', 'Total OT Hours');
				$objPHPExcel->getActiveSheet()->getStyle($xcoor.'6')->applyFromArray($headerstyle);
			}
			$cdate = $this->input->post('date_period_start');
			$mdate = strtotime($this->input->post('date_period_start'));
			$edate = $this->input->post('date_period_end');
			$alpha_ctr++;
		}

		$last_col = $xcoor;

		$alpha_ctr=4;
		for($ctr=1; $ctr<4; $ctr++){
			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);
		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		//$activeSheet->setCellValue('A1', 'Pioneer Insurance');
		$this->db->where('campaign_id',$this->input->post('campaign'));
		$campaign = $this->db->get('campaign')->row();
		$activeSheet->setCellValue('A1', $campaign->campaign);
		$activeSheet->setCellValue('A2', 'Billable Hours');
		$activeSheet->setCellValue('A3', 'For the range of '.date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		$number_ctr = 1;
		$pos_ctr = 1;
		$all_data = $query->result();

		$employee_affected = array();
		foreach($all_data as $get_employee)
			$employee_affected[] = $get_employee->employee_id;
		foreach($all_data as $get_employee)
			$position_affected[] = $get_employee->position_id;

		$position_level = array_unique($position_level);
		$employee_affected = array_unique($employee_affected);
		$position_affected = array_unique($position_affected);


		$reg_days = true;
		$no_day=true;
		//team leader
		// $no_tl = false;
		$tl_position = 0;

		// check if no tl
		$this->db->select('employee_dtr.date, employee_dtr.hours_worked AS hours_worked, round(('.$this->db->dbprefix.'employee_dtr.overtime/60), 2) as overtime, employee.campaign_id, user.firstname, user.lastname, employee.employee_id, user.position_id', false);
		$this->db->join('employee','employee.employee_id = employee_dtr.employee_id');
		$this->db->join('user','employee_dtr.employee_id = user.employee_id');
		$this->db->join('user_position','user_position.position_id = user.position_id');
		$this->db->where('employee_dtr.date >= ', date('Y-m-d', strtotime($this->input->post('date_period_start'))) );
		$this->db->where('employee_dtr.date <= ', date('Y-m-d', strtotime($this->input->post('date_period_end'))) );
		$this->db->where('employee.campaign_id = ', $this->input->post('campaign') );
		$this->db->where('user_position.position_level_id = ', 3);
		$this->db->group_by('user.employee_id');
		$is_there_tl = $this->db->get('employee_dtr');

		if($is_there_tl && $is_there_tl->num_rows() > 0)
		{
			$tl_employees = $is_there_tl->result();
			foreach($tl_employees as $tl_employee)
				$tl_emp_affected[] = $tl_employee->employee_id;

			foreach($tl_emp_affected as $curr_employee) {
				$this->db->select('employee_dtr.date, employee_dtr.hours_worked AS hours_worked, round(('.$this->db->dbprefix.'employee_dtr.overtime/60), 2) as overtime, employee.campaign_id, user.firstname, user.lastname, employee.employee_id, user.position_id', false);
				$this->db->join('employee','employee.employee_id = employee_dtr.employee_id');
				$this->db->join('user','employee_dtr.employee_id = user.employee_id');
				$this->db->join('user_position','user_position.position_id = user.position_id');
				$this->db->where('employee_dtr.date >= ', date('Y-m-d', strtotime($this->input->post('date_period_start'))) );
				$this->db->where('employee_dtr.date <= ', date('Y-m-d', strtotime($this->input->post('date_period_end'))) );
				$this->db->where('employee.campaign_id = ', $this->input->post('campaign') );
				$this->db->where('employee_dtr.employee_id = ', $curr_employee);
				$this->db->where('user_position.position_level_id = ', 3);
				$per_emp_data = $this->db->get('employee_dtr');

					if($per_emp_data->num_rows() > 0) {
						$per_emp_data = $per_emp_data->result_array();
						$alpha_ctr = 1;
						$total_reg_hrs = 0;
						$total_ot_hrs = 0;
						$tl_position = $per_emp_data[0]['position_id'];
						$no_day=true;
						$xcoor = $alphabet[$alpha_ctr-1];
						$activeSheet->setCellValue($xcoor . $line, $number_ctr);			
						$xcoor = $alphabet[$alpha_ctr];

						$activeSheet->setCellValue($xcoor . $line, $per_emp_data[0]['firstname']." ".$per_emp_data[0]['middlename']." ".$per_emp_data[0]['lastname']."(Team Leader)");
						foreach($dates_affected as $date_affected) {
							foreach($per_emp_data as $data) {
								
								if(date('d-M-y', strtotime($data['date'])) == date('d-M-y', strtotime($date_affected)))
								{
									$curr_date = date('Y-m-d', strtotime($data['date']));
									$qry = "SELECT hr_employee_leaves_duration.credit
											FROM hr_employee_leaves
											  LEFT JOIN hr_employee_leaves_dates
											    ON hr_employee_leaves.employee_leave_id = hr_employee_leaves_dates.employee_leave_id
											  LEFT JOIN hr_employee_leaves_duration
											    ON hr_employee_leaves_dates.duration_id = hr_employee_leaves_duration.duration_id
											WHERE hr_employee_leaves.employee_id = ".$curr_employee."
											    AND hr_employee_leaves.form_status_id = 3
											    AND hr_employee_leaves.deleted = 0
											    AND hr_employee_leaves_dates.date = '".$curr_date."'";

									$leave = $this->db->query($qry);

									if($leave && $leave->num_rows() > 0) {
										// DO NOT REMOVE. THIS CAN BE USED TO SATISFY CLIENT NEEDS
										// $alpha_ctr++;
										// $xcoor = $alphabet[$alpha_ctr];
										// $hours_worked = $data['hours_worked'] - $leave->credit;
										// $activeSheet->setCellValue($xcoor . $line, $hours_worked);
										// $total_reg_hrs += $hours_worked;
										// $no_day = false;
										// DO NOT REMOVE. THIS CAN BE USED TO SATISFY CLIENT NEEDS
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ' ');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
										$no_day = false;
									}else {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, $data['hours_worked']);		
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
										$total_reg_hrs += $data['hours_worked'];
										$no_day = false;
									}
								}
							}
							if($no_day)
							{
								$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
								$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
								$rd = $this->db->get('employee_dtr_setup')->row();
								$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
									if($rd->{$day} == 0) {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, 'RD');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
									} else {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ' ');
									}
								}
							$no_day = true;
						}
						$alpha_ctr++;
						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $total_reg_hrs);
						$grand_total_reg += $total_reg_hrs;
						$no_day=true;
						foreach($dates_affected as $date_affected) {
							foreach($per_emp_data as $data) {
								if(date('d-M-y', strtotime($data['date'])) == date('d-M-y', strtotime($date_affected)))
								{
									$alpha_ctr++;
									$xcoor = $alphabet[$alpha_ctr];
									$activeSheet->setCellValue($xcoor . $line, $data['overtime']);			
									$total_ot_hrs += $data['overtime'];
									$no_day = false;
								}
							}
							if($no_day)
							{
								$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
								$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
								$rd = $this->db->get('employee_dtr_setup')->row();
								$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
									if($rd->{$day} == 0) {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, 'RD');
									} else {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ' ');
									}
								}
							$no_day = true;
						}
						$alpha_ctr++;
						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $total_ot_hrs);
						$grand_total_ot += $total_ot_hrs;
						$number_ctr++;
						$line++;
						// echo $curr_employee,",";
					} // else
						// $no_tl = true;
			} 

			// if(!$no_tl) {
				$this->db->where('position_id', $tl_position);
				$position = $this->db->get('user_position')->row();
				$xcoor = $alphabet[0];
				$activeSheet->setCellValue($xcoor . $line, 'TOTAL - Team Leader');
				$activeSheet->setCellValue($position_tot_reg.$line, $grand_total_reg);
				$activeSheet->setCellValue($position_tot_ot.$line, $grand_total_ot);
				//style 
				$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totaltitlestyle);
				$objPHPExcel->getActiveSheet()->getStyle($position_tot_reg.$line)->applyFromArray($totalnumstyle);
				$objPHPExcel->getActiveSheet()->getStyle($position_tot_ot.$line)->applyFromArray($totalnumstyle);
				//style
				$grandest_total_reg += $grand_total_reg;
				$grandest_total_ot += $grand_total_ot;
				$number_ctr = 1;
				$line++;
			// }
			// $next_line = $line;
			//end team leader
		}
		


		// $line = 7;
		$reg_days = true;
		$no_day=true;
		$mod_xcoor = '';
		foreach($position_affected as $curr_position) {
			if($curr_position != $tl_position) {
				$grand_total_reg = 0;
				$grand_total_ot = 0;
				foreach($employee_affected as $curr_employee) {
				$this->db->select('employee_dtr.date, employee_dtr.hours_worked AS hours_worked, round(('.$this->db->dbprefix.'employee_dtr.overtime/60), 2) as overtime, employee.campaign_id, user.firstname, user.lastname, employee.employee_id, user.position_id', false);
				$this->db->join('employee','employee.employee_id = employee_dtr.employee_id');
				$this->db->join('user','employee_dtr.employee_id = user.employee_id');
				$this->db->join('user_position','user_position.position_id = user.position_id');
				$this->db->where('employee_dtr.date >= ', date('Y-m-d', strtotime($this->input->post('date_period_start'))) );
				$this->db->where('employee_dtr.date <= ', date('Y-m-d', strtotime($this->input->post('date_period_end'))) );
				$this->db->where('employee.campaign_id = ', $this->input->post('campaign') );
				$this->db->where('user.position_id = ', $curr_position);
				$this->db->where('employee_dtr.employee_id = ', $curr_employee);
				$this->db->where('user_position.position_level_id <> ', 3);
				$per_emp_data = $this->db->get('employee_dtr');

					if($per_emp_data->num_rows() > 0) {
						$per_emp_data = $per_emp_data->result_array();
						$alpha_ctr = 1;
						$total_reg_hrs = 0;
						$total_ot_hrs = 0;

						$no_day=true;
						$xcoor = $alphabet[$alpha_ctr-1];
						$activeSheet->setCellValue($xcoor . $line, $number_ctr);			
						$xcoor = $alphabet[$alpha_ctr];
						//echo $per_emp_data[0]['firstname']." ".$per_emp_data[0]['middlename']." ".$per_emp_data[0]['lastname']." ".$xcoor." ".$line."|";
						$activeSheet->setCellValue($xcoor . $line, $per_emp_data[0]['firstname']." ".$per_emp_data[0]['middlename']." ".$per_emp_data[0]['lastname']);
						foreach($dates_affected as $date_affected) {
							foreach($per_emp_data as $data) {
								if(date('d-M-y', strtotime($data['date'])) == date('d-M-y', strtotime($date_affected)))
								{
									$curr_date = $data['date'];
									$qry = "SELECT hr_employee_leaves_duration.credit
											FROM hr_employee_leaves
											  LEFT JOIN hr_employee_leaves_dates
											    ON hr_employee_leaves.employee_leave_id = hr_employee_leaves_dates.employee_leave_id
											  LEFT JOIN hr_employee_leaves_duration
											    ON hr_employee_leaves_dates.duration_id = hr_employee_leaves_duration.duration_id
											WHERE hr_employee_leaves.employee_id = ".$curr_employee."
											    AND hr_employee_leaves.form_status_id = 3
											    AND hr_employee_leaves.deleted = 0
											    AND hr_employee_leaves_dates.date = '".$curr_date."'";

									$leave = $this->db->query($qry);
									if($leave && $leave->num_rows() > 0) {
										// DO NOT REMOVE. THIS CAN BE USED TO SATISFY CLIENT NEEDS
										// $alpha_ctr++;
										// $xcoor = $alphabet[$alpha_ctr];
										// $hours_worked = $data['hours_worked'] - $leave->credit;
										// $activeSheet->setCellValue($xcoor . $line, $hours_worked);
										// $total_reg_hrs += $hours_worked;
										// $no_day = false;
										// DO NOT REMOVE. THIS CAN BE USED TO SATISFY CLIENT NEEDS
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ' ');
										$no_day = false;
									} else {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ($data['hours_worked'] > 0 ? $data['hours_worked'] : ''));
										$total_reg_hrs += $data['hours_worked'];
										$no_day = false;
									}
								}
							}
							if($no_day)
							{
								$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
								$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
								$rd = $this->db->get('employee_dtr_setup')->row();
								$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
								if($rd->{$day} == 0) {
									$alpha_ctr++;
									$xcoor = $alphabet[$alpha_ctr];
									$activeSheet->setCellValue($xcoor . $line, 'RD');
									$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
								} else {
									$alpha_ctr++;
									$xcoor = $alphabet[$alpha_ctr];
									$activeSheet->setCellValue($xcoor . $line, ' ');
								}
							}
							$no_day = true;
						}
						$alpha_ctr++;
						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $total_reg_hrs);
						$grand_total_reg += $total_reg_hrs;
						$no_day=true;
						foreach($dates_affected as $date_affected) {
							foreach($per_emp_data as $data) {
								if(date('d-M-y', strtotime($data['date'])) == date('d-M-y', strtotime($date_affected)))
								{
									$alpha_ctr++;
									$xcoor = $alphabet[$alpha_ctr];
									$activeSheet->setCellValue($xcoor . $line, ($data['overtime'] > 0 ? $data['overtime'] : ''));
									$total_ot_hrs += $data['overtime'];
									$no_day = false;
								}
							}
							if($no_day)
							{
								$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
								$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
								$rd = $this->db->get('employee_dtr_setup')->row();
								$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
									if($rd->{$day} == 0) {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, 'RD');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
									} else {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ' ');
									}
								}
							$no_day = true;
						}
						$alpha_ctr++;
						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $total_ot_hrs);
						$grand_total_ot += $total_ot_hrs;
						$number_ctr++;
						$line++;
						// echo $curr_employee,",";
					}
				}
					$this->db->where('position_id', $curr_position);
					$position = $this->db->get('user_position')->row();
					$xcoor = $alphabet[0];
					$activeSheet->setCellValue($xcoor . $line, 'TOTAL - '.$position->position);
					$activeSheet->setCellValue($position_tot_reg.$line, $grand_total_reg);
					$activeSheet->setCellValue($position_tot_ot.$line, $grand_total_ot);
					//style 
					$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totaltitlestyle);
					$objPHPExcel->getActiveSheet()->getStyle($position_tot_reg.$line)->applyFromArray($totalnumstyle);
					$objPHPExcel->getActiveSheet()->getStyle($position_tot_ot.$line)->applyFromArray($totalnumstyle);
					//style
					$grandest_total_reg += $grand_total_reg;
					$grandest_total_ot += $grand_total_ot;
					$number_ctr = 1;
					$line++;
				// echo " | ".$line." -  ".$curr_position." - ".$xcoor." | ";
			}
		}

		if ($mod_xcoor == ''){
			$mod_xcoor = $xcoor;
		}

		$activeSheet->setCellValue($xcoor . $line, 'GRAND TOTAL ');
		$activeSheet->setCellValue($position_tot_reg.$line, $grandest_total_reg);
		$activeSheet->setCellValue($position_tot_ot.$line, $grandest_total_ot);
		//style 
		$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totaltitlestyle);
		$objPHPExcel->getActiveSheet()->getStyle($position_tot_reg.$line)->applyFromArray($totalnumstyle);
		$objPHPExcel->getActiveSheet()->getStyle($position_tot_ot.$line)->applyFromArray($totalnumstyle);
		$objPHPExcel->getActiveSheet()->getStyle('A7:'.$last_col.($line - 2))->applyFromArray($styleArrayBorder);
		//$objPHPExcel->getActiveSheet()->getStyle('A7:'.$mod_xcoor.($line - 3))->applyFromArray($styleArrayBorder);
		//style

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=Billable.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	private function _oams_excel_export()
	{

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		$_POST['date_period_start'] = $this->_data->from;
		$_POST['date_period_end'] = $this->_data->to;
		$_POST['campaign'] = $this->_data->campaign;

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
		foreach($alphabet as $letter)
			array_push($alphabet, 'A'.$letter);
		foreach($alphabet as $letter)
			array_push($alphabet, 'B'.$letter);

		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);


		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$cellarray = array(
			'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'CCC'),
            ),

			'font' => array(
				'bold' => true,
			),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$totalnumstyle = array(
			'font' => array(
				'bold' => true,
				),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				)
			);

		$totaltitlestyle = array(
			'font' => array(
				'bold' => true,
				),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);

		$headerstyle = array(
			'font' => array(
				'bold' => true,
				),
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  )
			);
		$HorizontalCenter = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$styleArrayBorder = array(
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  )
			);		
		
		$top_ctr = 0;
		$activeSheet->setCellValue('A6', 'No.');
		$activeSheet->setCellValue('B6', 'Employee ID');
		$activeSheet->setCellValue('C6', 'Name');
		$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($headerstyle);
		$cdate = $this->input->post('date_period_start');
		$mdate = strtotime($this->input->post('date_period_start'));
		$alpha_ctr = 2;
		$edate = $this->input->post('date_period_end');
		$ctr=0;
		$dates_affected = array();
		$stop=true;
		$position_start_ot_date = "";
		$daily_reg_hrs_total = array();
		$daily_ot_hrs_total = array();
		for($x=0;$x<2;$x++) {
			$ctr_for_date = 0;
			while($mdate <= strtotime($edate)) {
				$xcoor = $alphabet[$alpha_ctr+1];
					
				$cdate = date('d-M-y', strtotime($cdate));
				$activeSheet->setCellValue($xcoor.'6', $cdate);
				// style
				$objPHPExcel->getActiveSheet()->getStyle($xcoor.'6')->applyFromArray($headerstyle);
				// style
				// echo $xcoor."|";
				if($stop == true)
					$dates_affected[] = $cdate;
				$cdate = date('d-M-y', strtotime('+1 day', strtotime($cdate)));
				$mdate = strtotime($cdate);
				$alpha_ctr++;
				$ctr_for_date++;
			}

			$stop=false;
			$xcoor = $alphabet[$alpha_ctr+1];
			if($x==0) {
				$otctr = $alpha_ctr+1;
				$position_tot_reg = $alphabet[$alpha_ctr+1];
				$activeSheet->setCellValue($xcoor.'6', 'Total Regular Hours' );
				$objPHPExcel->getActiveSheet()->getStyle($xcoor.'6')->applyFromArray($headerstyle);
			} else
			{
				$position_tot_ot = $alphabet[$alpha_ctr+1];
				$activeSheet->setCellValue($xcoor.'6', 'Total OT Hours');
				$objPHPExcel->getActiveSheet()->getStyle($xcoor.'6')->applyFromArray($headerstyle);
			}
			$cdate = $this->input->post('date_period_start');
			$mdate = strtotime($this->input->post('date_period_start'));
			$edate = $this->input->post('date_period_end');
			$alpha_ctr++;
		}

		$last_col = $xcoor;

		$alpha_ctr=5;
		for($ctr=1; $ctr<4; $ctr++){
			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);
		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		//$activeSheet->setCellValue('A1', 'Pioneer Insurance');
		$this->db->where('campaign_id',$this->input->post('campaign'));
		$campaign = $this->db->get('campaign')->row();
		$activeSheet->setCellValue('A1', $campaign->campaign);
		$activeSheet->setCellValue('A2', 'Billable Hours');
		$activeSheet->setCellValue('A3', 'For the range of '.date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		$number_ctr = 1;
		$pos_ctr = 1;
		$all_data = $query->result();

		$employee_affected = array();
		$employees = array();
		$resigned = array();
		foreach($all_data as $get_employee){
			$employees[] = $get_employee->employee_id;
		}

		foreach($all_data as $get_employee)
			$position_affected[] = $get_employee->position_id;

		
		$employees = array_unique($employees);
		foreach ($employees as $employee) {
			$this->db->select('SUM('.$this->db->dbprefix.'employee_dtr.hours_worked) AS total_hours');
			$this->db->join('employee','employee.employee_id = employee_dtr.employee_id');
			$this->db->where('employee_dtr.date >= ', date('Y-m-d', strtotime($this->input->post('date_period_start'))) );
			$this->db->where('employee_dtr.date <= ', date('Y-m-d', strtotime($this->input->post('date_period_end'))) );
			$this->db->where('employee.campaign_id = ', $this->input->post('campaign') );
			$this->db->where('employee.resigned = ', 1 );
			$this->db->where('employee_dtr.employee_id = ', $employee);
			$hrs_resigned = $this->db->get('employee_dtr')->row();

			if($hrs_resigned->total_hours != '0.00'){
				$employee_affected[] = $employee;
			}

		}

		$position_level = array_unique($position_level);
		$position_affected = array_unique($position_affected);

		$reg_days = true;
		$no_day=true;
		//team leader
		// $no_tl = false;
		$tl_position = 0;

		// check if no tl
		$this->db->select('employee_dtr.date, employee_dtr.hours_worked AS hours_worked, round(('.$this->db->dbprefix.'employee_dtr.overtime/60), 2) as overtime, employee.campaign_id, user.firstname, user.lastname, employee.employee_id, user.position_id', false);
		$this->db->join('employee','employee.employee_id = employee_dtr.employee_id');
		$this->db->join('user','employee_dtr.employee_id = user.employee_id');
		$this->db->join('user_position','user_position.position_id = user.position_id');
		$this->db->where('employee_dtr.date >= ', date('Y-m-d', strtotime($this->input->post('date_period_start'))) );
		$this->db->where('employee_dtr.date <= ', date('Y-m-d', strtotime($this->input->post('date_period_end'))) );
		$this->db->where('employee.campaign_id = ', $this->input->post('campaign') );
		$this->db->where('user_position.position_level_id = ', 3);
		$this->db->group_by('user.employee_id');
		$this->db->order_by('user.lastname', 'ASC');
		$is_there_tl = $this->db->get('employee_dtr');
		
		$daily_reg_hrs = array();
		$daily_ot_hrs = array();
		
		if($is_there_tl && $is_there_tl->num_rows() > 0)
		{
			$tl_employees = $is_there_tl->result();

			foreach($tl_employees as $tl_employee){

				$this->db->select('SUM('.$this->db->dbprefix.'employee_dtr.hours_worked) AS total_hours');
				$this->db->join('employee','employee.employee_id = employee_dtr.employee_id');
				$this->db->where('employee_dtr.date >= ', date('Y-m-d', strtotime($this->input->post('date_period_start'))) );
				$this->db->where('employee_dtr.date <= ', date('Y-m-d', strtotime($this->input->post('date_period_end'))) );
				$this->db->where('employee.campaign_id = ', $this->input->post('campaign') );
				$this->db->where('employee.resigned = ', 1 );
				$this->db->where('employee_dtr.employee_id = ', $tl_employee->employee_id);
				$hrs_resigned = $this->db->get('employee_dtr')->row();

				if($hrs_resigned->total_hours != '0.00'){
					$tl_emp_affected[] = $tl_employee->employee_id;
				}
			}
			
			foreach($tl_emp_affected as $curr_employee) {
				$this->db->select('employee_dtr.date, employee_dtr.hours_worked AS hours_worked, round(('.$this->db->dbprefix.'employee_dtr.overtime/60), 2) as overtime, employee.campaign_id, user.firstname, user.lastname, employee.employee_id, employee.biometric_id, user.position_id, round(('.$this->db->dbprefix.'employee_dtr.lates/60), 2) AS lates, round(('.$this->db->dbprefix.'employee_dtr.undertime/60), 2) AS undertime', false);
				$this->db->join('employee','employee.employee_id = employee_dtr.employee_id');
				$this->db->join('user','employee_dtr.employee_id = user.employee_id');
				$this->db->join('user_position','user_position.position_id = user.position_id');
				$this->db->where('employee_dtr.date >= ', date('Y-m-d', strtotime($this->input->post('date_period_start'))) );
				$this->db->where('employee_dtr.date <= ', date('Y-m-d', strtotime($this->input->post('date_period_end'))) );
				$this->db->where('employee.campaign_id = ', $this->input->post('campaign') );
				$this->db->where('employee_dtr.employee_id = ', $curr_employee);
				$this->db->where('user_position.position_level_id = ', 3);
				$per_emp_data = $this->db->get('employee_dtr');

					if($per_emp_data->num_rows() > 0) {
						$per_emp_data = $per_emp_data->result_array();
						$alpha_ctr =2;
						$total_reg_hrs = 0;
						$total_ot_hrs = 0;

						$tl_position = $per_emp_data[0]['position_id'];
						$no_day=true;
						$xcoor = $alphabet[$alpha_ctr-2];
						$activeSheet->setCellValue($xcoor . $line, $number_ctr);			
						
						$xcoor = $alphabet[$alpha_ctr-1];
						$activeSheet->setCellValue($xcoor . $line, $per_emp_data[0]['biometric_id']);

						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $per_emp_data[0]['lastname'] . " , " . $per_emp_data[0]['firstname']."(Team Leader)");
						$hours = array();
						$hours_ot = array();
						foreach($dates_affected as $date_affected) {

							foreach($per_emp_data as $data) {
								
								if(date('d-M-y', strtotime($data['date'])) == date('d-M-y', strtotime($date_affected)))
								{

									$curr_date = date('Y-m-d', strtotime($data['date']));
									$qry = "SELECT hr_employee_leaves_duration.credit
											FROM hr_employee_leaves
											  LEFT JOIN hr_employee_leaves_dates
											    ON hr_employee_leaves.employee_leave_id = hr_employee_leaves_dates.employee_leave_id
											  LEFT JOIN hr_employee_leaves_duration
											    ON hr_employee_leaves_dates.duration_id = hr_employee_leaves_duration.duration_id
											WHERE hr_employee_leaves.employee_id = ".$curr_employee."
											    AND hr_employee_leaves.form_status_id = 3
											    AND hr_employee_leaves.deleted = 0
											    AND hr_employee_leaves_dates.date = '".$curr_date."'";

									$leave = $this->db->query($qry);

									if($leave && $leave->num_rows() > 0) {
										// DO NOT REMOVE. THIS CAN BE USED TO SATISFY CLIENT NEEDS
										// $alpha_ctr++;
										// $xcoor = $alphabet[$alpha_ctr];
										// $hours_worked = $data['hours_worked'] - $leave->credit;
										// $activeSheet->setCellValue($xcoor . $line, $hours_worked);
										// $total_reg_hrs += $hours_worked;
										// $no_day = false;
										// DO NOT REMOVE. THIS CAN BE USED TO SATISFY CLIENT NEEDS
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, 'LEAVE');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
										$no_day = false;
										$hours[] = 0;
									}else {

										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];

										$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
										$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
										$rd = $this->db->get('employee_dtr_setup')->row();
										$day = strtolower(date('l', strtotime($date_affected))."_shift_id");

										$total_work_hours = $data['hours_worked'] + $data['undertime'] + $data['lates'];
										$hr_work =  $total_work_hours - $data['hours_worked'];
										
										if($hr_work <= 1){
											$data['hours_worked'] = $total_work_hours;
										}else{
											$data['hours_worked'] = $data['hours_worked'];
										}
										
										if ($rd->{$day} == 0 && $data['hours_worked'] == '0.00' && $data['overtime'] == '0.00' ) {
											$activeSheet->setCellValue($xcoor . $line, 'RD');
											
										}elseif ($rd->{$day} == 0 && $data['overtime'] != '0.00') {
											$activeSheet->setCellValue($xcoor . $line, ' ');
											
										}	
										else{
											$activeSheet->setCellValue($xcoor . $line, $data['hours_worked']);		
										}

										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
										$total_reg_hrs += $data['hours_worked'];
										$no_day = false;
										
										$hours[] = $data['hours_worked'];

									}
									
								}
								
							}
							

							if($no_day)
							{
								$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
								$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
								$rd = $this->db->get('employee_dtr_setup')->row();
								$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
									if($rd->{$day} == 0) {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, 'RD');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
										
									} else {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ' ');

									}
									$hours[] = 0;
							}
					
							$no_day = true;
							
						}
						for ($hr=0; $hr < count($hours); $hr++) { 
							$daily_reg_hrs[$hr][] = $hours[$hr];
						}
						
						$alpha_ctr++;
						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $total_reg_hrs);
						$grand_total_reg += $total_reg_hrs;

						$no_day=true;
						foreach($dates_affected as $date_affected) {
							foreach($per_emp_data as $data) {
								if(date('d-M-y', strtotime($data['date'])) == date('d-M-y', strtotime($date_affected)))
								{
									$holiday = $this->system->holiday_check( $data['date'], $curr_employee);

									if ($holiday[0]['date_set'] === $data['date']) {
										$total_work_hours = $data['hours_worked'] + $data['undertime'] + $data['lates'];
										if ($total_work_hours == $data['overtime']) {
											$data['overtime'] = 0;
										}elseif ($total_work_hours < $data['overtime']) {
											$data['overtime'] = $data['overtime'] - $total_work_hours;
										}
									}

									$alpha_ctr++;
									$xcoor = $alphabet[$alpha_ctr];
									
									$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
									$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
									$rd = $this->db->get('employee_dtr_setup')->row();
									$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
									
									if ($rd->{$day} == 0 && $data['hours_worked'] == '0.00' && $data['overtime'] == '0.00' ) {
										$activeSheet->setCellValue($xcoor . $line, 'RD');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
									}	
									else{
										$activeSheet->setCellValue($xcoor . $line, $data['overtime']);				
									}
									$total_ot_hrs += $data['overtime'];
									$hours_ot[] = $data['overtime'];
									$no_day = false;
								}
							}
							if($no_day)
							{
								$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
								$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
								$rd = $this->db->get('employee_dtr_setup')->row();
								$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
									if($rd->{$day} == 0) {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, 'RD');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
									} else {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ' ');
									}
									$hours_ot[] = 0;
								}
							$no_day = true;
						}
						
						for ($ot=0; $ot < count($hours_ot); $ot++) { 
							$daily_ot_hrs[$ot][] = $hours_ot[$ot];
						}
											

						$alpha_ctr++;
						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $total_ot_hrs);
						$grand_total_ot += $total_ot_hrs;
						$number_ctr++;
						$line++;
						// echo $curr_employee,",";
					
					} // else
				// $no_tl = true;

			} 

			// if(!$no_tl) {
				$this->db->where('position_id', $tl_position);
				$position = $this->db->get('user_position')->row();
				// $alpha_ctr++;
				$dailyctr = 3;
				$xcoor = $alphabet[0];
				$activeSheet->setCellValue($xcoor . $line, 'TOTAL - Team Leader');
				$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totaltitlestyle);
				
				foreach ($daily_reg_hrs as $key => $value) {
					$daily_reg_hrs_total[][$key] = array_sum($value);
					// $xcoor = $alphabet[$dailyctr];
					// $activeSheet->setCellValue($xcoor . $line,  array_sum($value));
					// $objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totalnumstyle);
					// $dailyctr++;
				}

				$activeSheet->setCellValue($position_tot_reg.$line, $grand_total_reg);
				
				//style 
				$objPHPExcel->getActiveSheet()->getStyle($position_tot_reg.$line)->applyFromArray($totalnumstyle);

				foreach ($daily_ot_hrs as  $key=>$value) {
					$daily_ot_hrs_total[][$key] = array_sum($value);
					// $xcoor = $alphabet[$otctr+1];
					// $activeSheet->setCellValue($xcoor . $line,  array_sum($value));
					// $objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totalnumstyle);
					// $otctr++;
				}
			
				$activeSheet->setCellValue($position_tot_ot.$line, $grand_total_ot);

				$objPHPExcel->getActiveSheet()->getStyle($position_tot_ot.$line)->applyFromArray($totalnumstyle);
				//style

				$grandest_total_reg += $grand_total_reg;
				$grandest_total_ot += $grand_total_ot;
				$number_ctr = 1;
				$line++;

			// }
			// $next_line = $line;
			//end team leader
		}
		
		// $line = 7;
		$reg_days = true;
		$no_day=true;
		$mod_xcoor = '';

		foreach($position_affected as $curr_position) {
			$daily_reg_hrs = array();
			$daily_ot_hrs = array();
			if($curr_position != $tl_position) {
				$grand_total_reg = 0;
				$grand_total_ot = 0;
				
				foreach($employee_affected as $curr_employee) {
				$where = "(hr_user_position.position_level_id <>  3 OR hr_user_position.position_level_id IS NULL)";
				$this->db->select('employee_dtr.date, employee_dtr.hours_worked AS hours_worked, round(('.$this->db->dbprefix.'employee_dtr.overtime/60), 2) as overtime, employee.campaign_id, user.firstname, user.lastname, employee.employee_id, employee.biometric_id, user.position_id, round(('.$this->db->dbprefix.'employee_dtr.lates/60), 2) AS lates, round(('.$this->db->dbprefix.'employee_dtr.undertime/60), 2) AS undertime', false);
				$this->db->join('employee','employee.employee_id = employee_dtr.employee_id');
				$this->db->join('user','employee_dtr.employee_id = user.employee_id');
				$this->db->join('user_position','user_position.position_id = user.position_id');
				$this->db->where('employee_dtr.date >= ', date('Y-m-d', strtotime($this->input->post('date_period_start'))) );
				$this->db->where('employee_dtr.date <= ', date('Y-m-d', strtotime($this->input->post('date_period_end'))) );
				$this->db->where('employee.campaign_id = ', $this->input->post('campaign') );
				$this->db->where('user.position_id = ', $curr_position);
				$this->db->where('employee_dtr.employee_id = ', $curr_employee);
				$this->db->where($where);
				$per_emp_data = $this->db->get('employee_dtr');

					if($per_emp_data->num_rows() > 0) {
						$per_emp_data = $per_emp_data->result_array();
						$alpha_ctr = 2;
						$total_reg_hrs = 0;
						$total_ot_hrs = 0;
			
						$no_day=true;
						$xcoor = $alphabet[$alpha_ctr-2];
						$activeSheet->setCellValue($xcoor . $line, $number_ctr);			
						
						$xcoor = $alphabet[$alpha_ctr-1];
						$activeSheet->setCellValue($xcoor . $line, $per_emp_data[0]['biometric_id']);

						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $per_emp_data[0]['lastname'] . " , " . $per_emp_data[0]['firstname']);
						$hours = array();
						$hours_ot = array();

						foreach($dates_affected as $date_affected) {
							foreach($per_emp_data as $data) {
								if(date('d-M-y', strtotime($data['date'])) == date('d-M-y', strtotime($date_affected)))
								{
									$curr_date = $data['date'];
									$qry = "SELECT hr_employee_leaves_duration.credit
											FROM hr_employee_leaves
											  LEFT JOIN hr_employee_leaves_dates
											    ON hr_employee_leaves.employee_leave_id = hr_employee_leaves_dates.employee_leave_id
											  LEFT JOIN hr_employee_leaves_duration
											    ON hr_employee_leaves_dates.duration_id = hr_employee_leaves_duration.duration_id
											WHERE hr_employee_leaves.employee_id = ".$curr_employee."
											    AND hr_employee_leaves.form_status_id = 3
											    AND hr_employee_leaves.deleted = 0
											    AND hr_employee_leaves_dates.date = '".$curr_date."'";

									$leave = $this->db->query($qry);
									if($leave && $leave->num_rows() > 0) {
										// DO NOT REMOVE. THIS CAN BE USED TO SATISFY CLIENT NEEDS
										// $alpha_ctr++;
										// $xcoor = $alphabet[$alpha_ctr];
										// $hours_worked = $data['hours_worked'] - $leave->credit;
										// $activeSheet->setCellValue($xcoor . $line, $hours_worked);
										// $total_reg_hrs += $hours_worked;
										// $no_day = false;
										// DO NOT REMOVE. THIS CAN BE USED TO SATISFY CLIENT NEEDS
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, 'LEAVE');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
										$no_day = false;
										$hours[] = 0;
									} else {
										$total_work_hours = $data['hours_worked'] + $data['undertime'] + $data['lates'];
										$hr_work =  $total_work_hours - $data['hours_worked'];

										if($hr_work <= 1){
											$data['hours_worked'] = $total_work_hours;
										}else{
											$data['hours_worked'] = $data['hours_worked'];
										}

										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];

										$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
										$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
										$rd = $this->db->get('employee_dtr_setup')->row();
										$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
										
										if ($rd->{$day} == 0 && $data['hours_worked'] == '0.00' && $data['overtime'] == '0.00' ) {
											$activeSheet->setCellValue($xcoor . $line, 'RD');
											$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
											
										}elseif ($rd->{$day} == 0 && $data['overtime'] != '0.00') {
											$activeSheet->setCellValue($xcoor . $line, ' ');
											
										}	
										else{
											$activeSheet->setCellValue($xcoor . $line, $data['hours_worked']);		
										}

										
										$total_reg_hrs += $data['hours_worked'];
										$no_day = false;
										$hours[] = $data['hours_worked'];
									}
									
								}
							}
							if($no_day)
							{
								$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
								$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
								$rd = $this->db->get('employee_dtr_setup')->row();
								$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
								if($rd->{$day} == 0) {
									$alpha_ctr++;
									$xcoor = $alphabet[$alpha_ctr];
									$activeSheet->setCellValue($xcoor . $line, 'RD');
									$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
								} else {
									$alpha_ctr++;
									$xcoor = $alphabet[$alpha_ctr];
									$activeSheet->setCellValue($xcoor . $line, ' ');
								}
								$hours[] = 0;
							}
							$no_day = true;
						}
						for ($h=0; $h < count($hours); $h++) { 
							$daily_reg_hrs[$h][] = $hours[$h];
						}

						$alpha_ctr++;
						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $total_reg_hrs);
						$grand_total_reg += $total_reg_hrs;
						$no_day=true;
						foreach($dates_affected as $date_affected) {
							foreach($per_emp_data as $data) {
								if(date('d-M-y', strtotime($data['date'])) == date('d-M-y', strtotime($date_affected)))
								{
									$holiday = $this->system->holiday_check( $data['date'], $curr_employee);

									if ($holiday[0]['date_set'] === $data['date']) {
										$total_work_hours = $data['hours_worked'] + $data['undertime'] + $data['lates'];
										if ($total_work_hours == $data['overtime']) {
											$data['overtime'] = 0;
										}elseif ($total_work_hours < $data['overtime']) {
											$data['overtime'] = $data['overtime'] - $total_work_hours;
										}
									}

									$alpha_ctr++;
									$xcoor = $alphabet[$alpha_ctr];

									$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
									$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
									$rd = $this->db->get('employee_dtr_setup')->row();
									$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
									
									if ($rd->{$day} == 0 && $data['hours_worked'] == '0.00' && $data['overtime'] == '0.00' ) {
										$activeSheet->setCellValue($xcoor . $line, 'RD');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
									}	
									else{
										$activeSheet->setCellValue($xcoor . $line, ($data['overtime'] > 0 ? $data['overtime'] : ''));
									}
									
									$total_ot_hrs += $data['overtime'];
									$hours_ot[] = $data['overtime'];
									$no_day = false;
								}
							}

							if($no_day)
							{
								$this->db->where('employee_dtr_setup.employee_id', $curr_employee);
								$this->db->join('timekeeping_shift_calendar', 'employee_dtr_setup.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id');
								$rd = $this->db->get('employee_dtr_setup')->row();
								$day = strtolower(date('l', strtotime($date_affected))."_shift_id");
									if($rd->{$day} == 0) {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, 'RD');
										$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($HorizontalCenter);
									} else {
										$alpha_ctr++;
										$xcoor = $alphabet[$alpha_ctr];
										$activeSheet->setCellValue($xcoor . $line, ' ');
									}
									$hours_ot[] = 0;
							}
							$no_day = true;
						}
						
						for ($ot=0; $ot < count($hours_ot); $ot++) { 
							$daily_ot_hrs[$ot][] = $hours_ot[$ot];
						}

						$alpha_ctr++;
						$xcoor = $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . $line, $total_ot_hrs);
						$grand_total_ot += $total_ot_hrs;
						$number_ctr++;
						$line++;
						
					}
				}
					$this->db->where('position_id', $curr_position);
					$position = $this->db->get('user_position')->row();
					$xcoor = $alphabet[0];
					$activeSheet->setCellValue($xcoor . $line, 'TOTAL - '.$position->position);
					$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totaltitlestyle);
					$dailyctr = 3;
					// dbug($daily_reg_hrs);
					foreach ($daily_reg_hrs as $key => $value) {
						$daily_reg_hrs_total[][$key] = array_sum($value);
						// $xcoor = $alphabet[$dailyctr];
						// $activeSheet->setCellValue($xcoor . $line,  array_sum($value));
						// $objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totalnumstyle);
						// $dailyctr++;
					}
				
					$activeSheet->setCellValue($position_tot_reg.$line, $grand_total_reg);
					$objPHPExcel->getActiveSheet()->getStyle($position_tot_reg.$line)->applyFromArray($totalnumstyle);

					$otctr1 = $dailyctr;
					foreach ($daily_ot_hrs as $key => $value) {
						$daily_ot_hrs_total[][$key] = array_sum($value);
						// $xcoor = $alphabet[$otctr1+1];
						// $activeSheet->setCellValue($xcoor . $line,  array_sum($value));
						// $objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totalnumstyle);
						// $otctr1++;
					}

					$activeSheet->setCellValue($position_tot_ot.$line, $grand_total_ot);
					$objPHPExcel->getActiveSheet()->getStyle($position_tot_ot.$line)->applyFromArray($totalnumstyle);
					//style
					$grandest_total_reg += $grand_total_reg;
					$grandest_total_ot += $grand_total_ot;
					$number_ctr = 1;
					$line++;
				// echo " | ".$line." -  ".$curr_position." - ".$xcoor." | ";
			}

		}
		$daily_reg_hrs_totals = array();
		$daily_ot_hrs_totals = array();
		
		foreach ($daily_reg_hrs_total as $key => $value) {
			for ($drh=0; $drh < count($dates_affected); $drh++) { 
				$daily_reg_hrs_totals[$drh][] = $value[$drh];
			}
		}

		foreach ($daily_ot_hrs_total as $key => $value) {
			for ($doh=0; $doh < count($dates_affected); $doh++) { 
				$daily_ot_hrs_totals[$doh][] = $value[$doh];
			}
		}


		if ($mod_xcoor == ''){
			$mod_xcoor = $xcoor;
		}

		$activeSheet->setCellValue($xcoor . $line, 'GRAND TOTAL ');
		$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totaltitlestyle);
		$cnt = 3;
		
		foreach ($daily_reg_hrs_totals as $key => $value) {
			$xcoor = $alphabet[$cnt];
			$activeSheet->setCellValue($xcoor . $line, array_sum($value));
			$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totalnumstyle);	
			$cnt++;
		}

		foreach ($daily_ot_hrs_totals as $key => $value) {
			// dbug(array_sum($value));
			$xcoor = $alphabet[$cnt+1];
			$activeSheet->setCellValue($xcoor . $line, array_sum($value));
			$objPHPExcel->getActiveSheet()->getStyle($xcoor.$line)->applyFromArray($totalnumstyle);	
			$cnt++;
		}

		$activeSheet->setCellValue($position_tot_reg.$line, $grandest_total_reg);
		$activeSheet->setCellValue($position_tot_ot.$line, $grandest_total_ot);
		
		//style 
		
		$objPHPExcel->getActiveSheet()->getStyle($position_tot_reg.$line)->applyFromArray($totalnumstyle);
		$objPHPExcel->getActiveSheet()->getStyle($position_tot_ot.$line)->applyFromArray($totalnumstyle);
		$objPHPExcel->getActiveSheet()->getStyle('A7:'.$last_col.($line - 2))->applyFromArray($styleArrayBorder);
		//$objPHPExcel->getActiveSheet()->getStyle('A7:'.$mod_xcoor.($line - 3))->applyFromArray($styleArrayBorder);
		//style

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=Billable.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}


	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

		$res = $this->db->get_where('billable_period', array('billable_period_id' => $record['billable_period_id']))->row();
		
        $actions .= '<a class="icon-button icon-16-export export-billable" record_id="'.$record['billable_period_id'].'" module_link="'.$module_link.'" href="javascript:void(0)" tooltip="Export" original-title=""></a>';
        
        if (CLIENT_DIR == "oams") {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }


        if ($this->user_access[$this->module_id]['edit'] && !$res->closed) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['approve'] && !$res->closed) {
        	$actions .= '<a class="icon-button icon-16-tick-button" record_id="'.$record['billable_period_id'].'" module_link="'.$module_link.'" href="javascript:void(0)" tooltip="Close" onClick="closeform('.$record['billable_period_id'].')" original-title=""></a>';
        }

        if ($this->user_access[$this->module_id]['delete'] && !$res->closed) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }


        $actions .= '</span>';

		return $actions;
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    

 		if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }

        $buttons .= "</div>";
                
		return $buttons;
	}

	function close_form()
	{
		$this->db->update('billable_period', array('closed' => 1), array('billable_period_id' => $this->input->post('id')));
	}

	function get_billable()
	{
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$billable = $billable = $this->db->get_where('billable_period', array('billable_period_id' => $this->input->post('billable_id')))->row();
		$response->closed = $billable->closed;
		$response->path = $billable->filename;

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);		
	}

	// function _set_specific_search_query()
	// {
	// 	$field = $this->input->post('searchField');
	// 	$operator =  $this->input->post('searchOper');
	// 	$value =  $this->input->post('searchString');


	// 	if($field == "employee_dtr.time_in1"){

	// 		$value = date('Y-m-d h:i:s',strtotime($value));

	// 	}
		

	// 	foreach( $this->search_columns as $search )
	// 	{
	// 		if($search['jq_index'] == $field) $field = $search['column'];
	// 	}

	// 	$field = strtolower( $field );
	// 	if(sizeof(explode(' as ', $field)) > 1){
	// 		$as_part = explode(' as ', $field);
	// 		$field = strtolower( trim( $as_part[0] ) );
	// 	}


	// 	switch ($operator) {
	// 		case 'eq':
	// 			return $field . ' = "'.$value.'"';
	// 			break;
	// 		case 'ne':
	// 			return $field . ' != "'.$value.'"';
	// 			break;
	// 		case 'lt':
	// 			return $field . ' < "'.$value.'"';
	// 			break;
	// 		case 'le':
	// 			return $field . ' <= "'.$value.'"';
	// 			break;
	// 		case 'gt':
	// 			return $field . ' > "'.$value.'"';
	// 			break;
	// 		case 'ge':
	// 			return $field . ' >= "'.$value.'"';
	// 			break;
	// 		case 'bw':
	// 			return $field . ' REGEXP "^'. $value .'"';
	// 			break;
	// 		case 'bn':
	// 			return $field . ' NOT REGEXP "^'. $value .'"';
	// 			break;
	// 		case 'in':
	// 			return $field . ' IN ('. $value .')';
	// 			break;
	// 		case 'ni':
	// 			return $field . ' NOT IN ('. $value .')';
	// 			break;
	// 		case 'ew':
	// 			return $field . ' LIKE "%'. $value  .'"';
	// 			break;
	// 		case 'en':
	// 			return $field . ' NOT LIKE "%'. $value  .'"';
	// 			break;
	// 		case 'cn':
	// 			return $field . ' LIKE "%'. $value .'%"';
	// 			break;
	// 		case 'nc':
	// 			return $field . ' NOT LIKE "%'. $value .'%"';
	// 			break;
	// 		default:
	// 			return $field . ' LIKE %'. $value .'%';
	// 	}
	// }

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>