<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Completed_ape extends MY_Controller
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
		$data['content'] = 'employee/completed_ape_listview';

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

		if( $this->input->post('health_type') != '' ){
			$health_type = $this->db->dbprefix."employee_health.health_type = ".$this->input->post('health_type');
		}
		else{
			$health_type = "(".$this->db->dbprefix."employee_health.health_type = 1 
			 	 OR ".$this->db->dbprefix."employee_health.health_type = 2)";
		}

		$where = $this->db->dbprefix."employee_health.deleted = 0 
				 AND {$health_type} 
				 AND ".$this->db->dbprefix."employee_health.date_of_completion IS NOT NULL AND ".$this->db->dbprefix."employee_health.health_type_status_id = 3
			 	 ";

		$this->db->where($where);
		if($this->input->post('date_period_end') != "" && $this->input->post('date_period_start') != "")
			$this->db->where("employee_health.date_of_completion BETWEEN '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."'");

		$this->db->join('employee_health_type', 'employee_health.health_type = employee_health_type.employee_health_type_id', 'left');
		$this->db->join('user','employee_health.employee_id = user.employee_id','left');
		$completed_ape = $this->db->get('employee_health');

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
	        $total_pages = $completed_ape->num_rows() > 0 ? ceil($completed_ape->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $completed_ape->num_rows();                      

	        $response->msg = "";

	        $where = $this->db->dbprefix."employee_health.deleted = 0 
				 AND {$health_type}  
				 AND ".$this->db->dbprefix."employee_health.date_of_completion IS NOT NULL AND ".$this->db->dbprefix."employee_health.health_type_status_id = 3
			 	 ";

			$this->db->where($where);

			if($this->input->post('date_period_end') != "" && $this->input->post('date_period_start') != "" )
				$this->db->where("employee_health.date_of_completion BETWEEN '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."'");

			$this->db->join('employee_health_type', 'employee_health.health_type = employee_health_type.employee_health_type_id', 'left');
			$this->db->join('user','employee_health.employee_id = user.employee_id','left');
			$completed_ape = $this->db->get('employee_health');

			$completed_ape = $completed_ape->result();
			$ctr = 0;
			foreach($completed_ape as $complete)
			{
				$response->rows[$ctr]['cell'][0] = $complete->firstname." ".$complete->middlename." ".$complete->lastname;
				$response->rows[$ctr]['cell'][1] = $complete->employee_health_type;
				$response->rows[$ctr]['cell'][2] = date($this->config->item('display_date_format'), strtotime($complete->date_of_completion));
				$response->rows[$ctr]['cell'][3] = $complete->health_provider;
				$ctr++;
			}
	    }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		//$this->listview_column_names = array('Department', 'Full Name', 'Position', 'Birthdate', 'Date Hired', 'Reg Date', 'Rank Code', 'Rank');
		$this->listview_column_names = array('Name', 'Health Type', 'Date of Completion', 'Health Provider');

		$this->listview_columns = array(
				array('name' => 'Name', 'width' => '180','align' => 'middle'),
				array('name' => 'Health Type', 'width' => '180','align' => 'middle'),
				array('name' => 'Date of Completion', 'width' => '180','align' => 'middle'),
				array('name' => 'Health Provider', 'width' => '180','align' => 'middle')
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

	function export() {	
		//campaign is company
		$query_id = '8';

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		if( $this->input->post('health_type') != '' ){
			$health_type = $this->db->dbprefix."employee_health.health_type = ".$this->input->post('health_type');
		}
		else{
			$health_type = "(".$this->db->dbprefix."employee_health.health_type = 1 
			 	 OR ".$this->db->dbprefix."employee_health.health_type = 2)";
		}

		$where = $this->db->dbprefix."employee_health.deleted = 0 
				 AND {$health_type}
				 AND ".$this->db->dbprefix."employee_health.date_of_completion IS NOT NULL
			 	 ";

		$this->db->where($where);
		if($this->input->post('date_period_end') != "" && $this->input->post('date_period_start') != "")
			$this->db->where("employee_health.date_of_completion BETWEEN '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."'");

		$this->db->join('employee_health_type', 'employee_health.health_type = employee_health_type.employee_health_type_id', 'left');
		$this->db->join('user','employee_health.employee_id = user.employee_id','left');
		$query = $this->db->get('employee_health');

		// $query  = $this->db->query($sql.$sql_string);

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

		$query = $query->result();

		//header
		$alphabet  = range('A','Z');

		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);


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
		$activeSheet->setCellValueExplicit('A5', 'Name', PHPExcel_Cell_DataType::TYPE_STRING); 
		$activeSheet->setCellValueExplicit('B5', 'Health Type', PHPExcel_Cell_DataType::TYPE_STRING); 
		$activeSheet->setCellValueExplicit('C5', 'Date of Completion', PHPExcel_Cell_DataType::TYPE_STRING); 
		$activeSheet->setCellValueExplicit('D5', 'Health Provider', PHPExcel_Cell_DataType::TYPE_STRING); 
		

		$objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('B5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('C5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('D5')->applyFromArray($headerstyle);


		$activeSheet->setCellValueExplicit('A1', "Completed Annual Physical Exam / Pre-Employment Exam From ".date($this->config->item('display_date_format'), strtotime($this->input->post('date_period_start')))." To ".date($this->config->item('display_date_format'), strtotime($this->input->post('date_period_end'))));

		$line_ctr = 6;

		foreach($query as $completed)
		{
			$name = $completed->firstname." ".$completed->middlename." ".$completed->lastname;
			$activeSheet->setCellValueExplicit('A'.$line_ctr, $name, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('B'.$line_ctr, $completed->employee_health_type, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('C'.$line_ctr, date($this->config->item('display_date_format'),strtotime($completed->date_of_completion)), PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('D'.$line_ctr, $completed->health_provider, PHPExcel_Cell_DataType::TYPE_STRING);

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
		header('Content-Disposition: attachment;filename='.date('Y-m-d') . ' ' .'Completed-APE-PEE.xls');
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

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>