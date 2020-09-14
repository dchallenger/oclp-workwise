<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Due_for_ape extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = '';
		$this->listview_description = '';
		$this->jqgrid_title = "";
		$this->detailview_title = '';
		$this->detailview_description = '';
		$this->editview_title = '';
		$this->editview_description = '';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	// $data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/due_for_ape_listview';

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

		// $data['division'] = $this->db->get('user_company_division')->result_array();
		// $data['employee'] = $this->db->get('user')->result_array();
		// $data['company'] = $this->db->get('user_company')->result_array();
		// $data['department'] = $this->db->get('user_company_department')->result_array();

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
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        
		
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;			

		$query_id = '9';
        $sql = "";
		$sql_string	= "";

		$this->db->where('export_query_id', $query_id);

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);
		$sql.= " WHERE ";

		$sql_string .= "{$this->db->dbprefix}employee_health.health_type_status_id IN (1,2) AND {$this->db->dbprefix}employee_health.deleted = 0";

		$result = $this->db->query($sql.$sql_string);   
        
	    // die($this->db->last_query()); 
	    //dbug($this->db->last_query());
	    // return;

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
	        $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $result->num_rows();                        

	        $response->msg = "";

			$query_id = '9';
	        $sql = "";
			$sql_string	= "";

			$this->db->where('export_query_id', $query_id);

			$result = $this->db->get('export_query');
			$export = $result->row();
			$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);
			$sql.= " WHERE ";

			$sql_string .= "{$this->db->dbprefix}employee_health.health_type_status_id IN (1,2) AND {$this->db->dbprefix}employee_health.deleted = 0";

	        if ($this->input->post('sidx')) {
	        	if($this->input->post('sidx') == 'employee name') 
	        	{
	        		$sql_string .= " ORDER BY FULL_NAME ".$sord;
	        		// $sidx = $this->db->dbprefix . 'user.lastname';
		         //    $sord = $this->input->post('sord');
	        	}
	        	if($this->input->post('sidx') == 'date of birth')
	        	{	        
	        		$sql_string .= " ORDER BY birth_date ".$sord;		
		            // $sidx = $this->input->post('sidx');
		            // $sord = $this->input->post('sord');	
	        	}
	            // $this->db->order_by($sidx . ' ' . $sord);
	        }
	        else
	        {
	        	// $sql_string .= " ORDER BY birth_date ASC";		
	        	$this->db->order_by( $this->db->dbprefix . 'user.lastname ASC');
	        }

	        $start = $limit * $page - $limit;
	        $sql_string .= " LIMIT ".$start.", ".$limit;

	        // $this->db->limit($limit, $start);      

	        $result = $this->db->query($sql.$sql_string);  
	        
	        $ctr = 0;
	        if($result->num_rows() > 0)
	        {
		        foreach ($result->result() as $row) {
		            $response->rows[$ctr]['cell'][0] = $row->{'full_name'};
		            $response->rows[$ctr]['cell'][1] = date($this->config->item('display_date_format'), strtotime($row->{'birth_date'}));
		            $response->rows[$ctr]['cell'][2] = $row->{'department'};
		            $response->rows[$ctr]['cell'][3] = $row->{'job_rank'};
		            $response->rows[$ctr]['cell'][4] = 'hp'; //$row->{'health_provider'};
		            $ctr++;
		        }
		    }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	 function _set_listview_query($listview_id = '', $view_actions = true) {
		//$this->listview_column_names = array('Department', 'Full Name', 'Position', 'Birthdate', 'Date Hired', 'Reg Date', 'Rank Code', 'Rank');
		$this->listview_column_names = array('Employee Name', 'Date of Birth', 'Department/Branch', 'Rank', 'Health Provider');

		$this->listview_columns = array(
					
				array('name' => 'Employee Name', 'width' => '180','align' => 'left'),
				array('name' => 'Date of Birth', 'width' => '180','align' => 'left'),
				array('name' => 'Department/Branch', 'width' => '180','align' => 'left'),
				array('name' => 'Rank', 'width' => '180','align' => 'left'),
				array('name' => 'Health Provider', 'width' => '180','align' => 'left')
				// array('name' => 'position'),
				// array('name' => 'birth_date'),
				// array('name' => 'employed_date'),
				// array('name' => 'regular_date'),
				// array('name' => 'job_rank'),
				// array('name' => 'job_rank_code')
				//array('name' => 'workshift')
			);                                     
    }

	// function _set_search_all_query()
	// {
	// 	$value =  $this->input->post('searchString');
	// 	$search_string = array();
	// 	foreach($this->search_columns as $search)
	// 	{
	// 		$column = strtolower( $search['column'] );
	// 		if(sizeof(explode(' as ', $column)) > 1){
	// 			$as_part = explode(' as ', $column);
	// 			$search['column'] = strtolower( trim( $as_part[0] ) );
	// 		}
	// 		$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
	// 	}
	// 	$search_string[] = $this->db->dbprefix .'user.firstname LIKE "%' . $value . '%"';
	// 	$search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';
	// 	$search_string[] = $this->db->dbprefix .'user_company_department.department LIKE "%' . $value . '%"';
	// 	$search_string = '('. implode(' OR ', $search_string) .')';
	// 	return $search_string;
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
		//campaign is company
		$query_id = '9';

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where('export_query_id', $query_id);



        $sql = "";
		$sql_string	= "";

		$this->db->where('export_query_id', $query_id);

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);
		$sql.= " WHERE ";

		$sql_string .= "MONTH(".$this->db->dbprefix."user.birth_date) = MONTH(CURDATE())";

		$result = $this->db->query($sql.$sql_string);  

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $result;
		$this->_excel_export();
	}
	
	private function _excel_export()
	{

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;
		$company_code = $this->_company;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		$query = $query->result();

		//header
		$alphabet  = range('A','Z');
		// foreach($alphabet as $letter)
		// 	array_push($alphabet, 'A'.$letter);
		// foreach($alphabet as $letter)
		// 	array_push($alphabet, 'B'.$letter);

		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);

		$headers = array(
					'Employee Name', 
					'Date of Birth', 
					'Department/Branch', 
					'Rank', 
					'Health Provider'
					);


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

		

		$top_ctr = 0;

		foreach($headers as $key => $header)
		{
			$activeSheet->setCellValueExplicit($alphabet[$key].'4', $header, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->getStyle($alphabet[$key].'4')->applyFromArray($headerstyle);
		}
		
		// echo $this->_company."|"; exit();

		$activeSheet->setCellValue('A1', 'Due for Annual Physical Exam');

		$line_ctr = 5;
		$name = "";

		foreach($query as $info)
		{
			$activeSheet->setCellValueExplicit('A'.$line_ctr, $info->full_name, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('B'.$line_ctr, date($this->config->item('display_date_format'), strtotime($info->birth_date)), PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('C'.$line_ctr, $info->department, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('D'.$line_ctr, $info->job_rank, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('E'.$line_ctr, 'HP', PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('H5', 'Changes', PHPExcel_Cell_DataType::TYPE_STRING);
			$line_ctr++;
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

    	if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	        	// $buttons .= "<div class='icon-label'><a rel='record-save' class='icon-16-export' href='javascript:void(0);' onclick='export_list();'><span>Export</span></a></div>";

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