<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resignation_conversion extends MY_Controller
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
		$data['content'] = 'leaves/listview_resignation';
		$data['jqgrid'] = 'leaves/jqgrid_resignation';

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

    function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";

        if ($this->user_access[$this->module_id]['post']) {
	            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export-employees' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
    	}
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{	
		$this->load->helper('time_upload');
		$effectivity_from = $this->input->post('effectivity_from');
		$effectivity_to = $this->input->post('effectivity_to');
		$search_hidden = $this->input->post('search_hidden');

		$effectivity_date_qry = 1;
		if(!empty($effectivity_from) && !empty($effectivity_to)) {
			// $effectivity_date_qry = 'resigned_date <= "'.date("Y-m-d",strtotime($effectivity_date)).'"';
			$effectivity_date_qry = ' ("'.date("Y-m-d",strtotime($effectivity_from)).'" <= resigned_date AND "'.date("Y-m-d",strtotime($effectivity_to)).'" >= resigned_date)';
		}

		$search = 1;
		if($search_hidden != 'Search...') {
			$search = '( '.$this->db->dbprefix('user'). '.firstname LIKE "%'.$search_hidden.'%" OR '.$this->db->dbprefix('user'). '.middleinitial LIKE "%'.$search_hidden.'%" OR '.$this->db->dbprefix('user'). '.lastname LIKE "%'.$search_hidden.'%" OR '.$this->db->dbprefix('user'). '.aux LIKE "%'.$search_hidden.'%" OR '.$this->db->dbprefix('employee'). '.id_number LIKE "%'.$search_hidden.'%" )';
		}

		$this->db->select(''.$this->db->dbprefix('employee'). '.id_number AS employee_number', false);
		$this->db->select(''.$this->db->dbprefix('employee'). '.resigned_date AS resigned_date', false);
		$this->db->select("CONCAT({$this->db->dbprefix('user')}.firstname,' ', {$this->db->dbprefix('user')}.middleinitial,' ', {$this->db->dbprefix('user')}.lastname,' ', {$this->db->dbprefix('user')}.aux ) AS employee_name", false);
		$this->db->select(''.$this->db->dbprefix('employee_leave_balance'). '.termpay_vl AS vl_quantity', false);
		$this->db->select(''.$this->db->dbprefix('employee_leave_balance'). '.termpay_sl AS sl_quantity', false);
		$this->db->from('employee_leave_balance');
		$this->db->join('(SELECT employee_id,MAX(YEAR) AS YEAR FROM hr_employee_leave_balance WHERE deleted = 0 GROUP BY employee_id) c2', 'hr_employee_leave_balance.employee_id = c2.employee_id AND hr_employee_leave_balance.year = c2.year','INNER',false);
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('employee_leave_balance').'.employee_id',LEFT);
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_leave_balance').'.employee_id',LEFT);
		$this->db->join($this->db->dbprefix('employee_movement'), $this->db->dbprefix('employee_movement').'.employee_id = '.$this->db->dbprefix('employee_leave_balance').'.employee_id', LEFT);
		$this->db->where('employee_leave_balance.deleted = 0 AND '.$this->db->dbprefix('employee_movement').'.deleted = 0 AND '.$effectivity_date_qry.' AND employee_movement_type_id IN (6,11) AND resigned_date != "0000-00-00" AND resigned_date IS NOT NULL AND '.$search);       

		$q = $this->db->get();
		// $fields = $q->list_fields();
		$fields = array('employee_number','employee_name', 'resigned_date','vl_quantity','sl_quantity');

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Resignation Report")
		            ->setDescription("Resignation Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);	
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);		

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$activeSheet->setCellValueExplicit('A1', 'Resignation Report', PHPExcel_Cell_DataType::TYPE_STRING); 
		$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
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
			switch ($field) {
				case 'employee_number':
					$field = 'Employee Number';
					break;
				case 'employee_name':
					$field = 'Employee';
					break;
				case 'resigned_date':
					$field = 'Effectivity Date';
					break;
				case 'vl_quantity':
					$field = 'VL Quantity';
					break;
				case 'sl_quantity':
					$field = 'SL Quantity';
					break;
			}
			$activeSheet->setCellValueExplicit($xcoor . '2', $field, PHPExcel_Cell_DataType::TYPE_STRING); 
			
			$alpha_ctr++;
		}

		// contents.
		$line = 3;
		if($q) {
			$query  = $q->result();
			foreach ($query as $row) {
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

		            switch ($field) {
						case 'employee_number':
						case 'employee_name':
								$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING);
							break;
						case 'resigned_date':
								$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date("F d,Y", strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING);
							break;
						case 'vl_quantity':
							$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, number_format($row->{$field},2), PHPExcel_Cell_DataType::TYPE_STRING); 
							break;
						case 'sl_quantity':
							$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, number_format($row->{$field},2), PHPExcel_Cell_DataType::TYPE_STRING); 
							break;
					}
					$alpha_ctr++;
				}
				$line++;
			}
		}
		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.date('Y-m-d').'_'.url_title("Resignation Report").'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	function listview()
	{
		$response->msg = "";
		$effectivity_from = $_POST['effectivity_from'];
		$effectivity_to = $_POST['effectivity_to'];
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
		$effectivity_date_qry = '';
		if(!empty($effectivity_from) && !empty($effectivity_to)) {
			// $effectivity_date_qry = 'AND resigned_date <= "'.date("Y-m-d",strtotime($effectivity_date)).'"';
			$effectivity_date_qry = 'AND  ("'.date("Y-m-d",strtotime($effectivity_from)).'" <= resigned_date AND "'.date("Y-m-d",strtotime($effectivity_to)).'" >= resigned_date)';
		}

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry.' , resigned_date', false);
		$this->db->from($this->module_table);
		$this->db->join('(SELECT employee_id,MAX(YEAR) AS YEAR FROM hr_employee_leave_balance WHERE deleted = 0 GROUP BY employee_id) c2', 'hr_employee_leave_balance.employee_id = c2.employee_id AND hr_employee_leave_balance.year = c2.year','INNER',false);
		$this->db->join('employee_movement', 'employee_movement.employee_id = '.$this->module_table.'.employee_id', 'left');
		$this->db->where($this->module_table.'.deleted = 0 AND resigned_date != "0000-00-00" AND resigned_date IS NOT NULL '.$effectivity_date_qry.' AND employee_movement_type_id IN (6,11) AND '.$search);
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );
		if( $this->sensitivity_filter ){
			$fields = $this->db->list_fields($this->module_table);
			if(in_array('sensitivity', $fields) && isset($this->sensitivity[$this->module_id])){
				$this->db->where($this->module_table.'.sensitivity IN ('.implode(',', $this->sensitivity[$this->module_id]).')');
			}
			else{
				$this->db->where($this->module_table.'.sensitivity IN (0)');	
			}	
		}

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$total_records =  $this->db->count_all_results();
		// $response->last_query = $this->db->last_query();
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $total_records > 0 ? ceil($total_records/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $total_records;

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry.' , resigned_date', false);
			$this->db->from($this->module_table);
			$this->db->join('(SELECT employee_id,MAX(YEAR) AS YEAR FROM hr_employee_leave_balance WHERE deleted = 0 GROUP BY employee_id) c2', 'hr_employee_leave_balance.employee_id = c2.employee_id AND hr_employee_leave_balance.year = c2.year','INNER',false);
			$this->db->join('employee_movement', 'employee_movement.employee_id = '.$this->module_table.'.employee_id', 'left');
			$this->db->where($this->module_table.'.deleted = 0 AND resigned_date != "0000-00-00" AND resigned_date IS NOT NULL '.$effectivity_date_qry.' AND employee_movement_type_id IN (6,11) AND '.$search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			if( $this->sensitivity_filter ){
				if(in_array('sensitivity', $fields) && isset($this->sensitivity[$this->module_id])){
					$this->db->where($this->module_table.'.sensitivity IN ('.implode(',', $this->sensitivity[$this->module_id]).')');
				}	
				else{
					$this->db->where($this->module_table.'.sensitivity IN (0)');	
				}	
			}

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
						$response->rows[$ctr]['id'] = $row['leave_balance_id'];
	                    $response->rows[$ctr]['cell'][0] = $row['t0id_number'];
	                    $response->rows[$ctr]['cell'][1] = $row['t1firstnamemiddleinitiallastnameaux'];
	                    $response->rows[$ctr]['cell'][2] = date("F d,Y",strtotime($row['resigned_date']));
	                    $response->rows[$ctr]['cell'][3] = number_format($row['termpay_vl'],2,'.',',');    
	                    $response->rows[$ctr]['cell'][4] = number_format($row['termpay_sl'],2,'.',',');    
	                    $ctr++;
					}

				}
			}
		}
		
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function _set_listview_query( $listview_id = '', $view_actions = true ) {
        MY_Controller::_set_listview_query($listview_id, $view_actions);

       	$this->listview_column_names[2] = 'Effectivity Date';
        $this->listview_column_names[3] = 'VL Quantity';
        $this->listview_column_names[4] = 'SL Quantity';
    } 
	// END - default module functions

	// START custom module funtions

	// END custom module funtions

}

/* End of file */
/* Location: system/application */