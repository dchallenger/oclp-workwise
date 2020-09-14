<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resignation_turnover_reports extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Resignation Turn-over Report';
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
		$data['scripts'][] = multiselect_script();
		$data['content'] = 'employee/movement/resignation_listview';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();
		$data['department'] = $this->db->get('user_company_department')->result_array();
        $data['company'] = $this->db->get('user_company')->result_array();
        $data['division'] = $this->db->get('user_company_division')->result_array();
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
	
	function get_division()
    {
        $division = $this->db->query('SELECT b.division_id, b.division FROM '.$this->db->dbprefix('user').' a LEFT JOIN  '.$this->db->dbprefix('user_company_division').' b ON a.division_id = b.division_id WHERE a.company_id IN ('.$this->input->post("div_id_delimited").') AND b.division_id IS NOT NULL GROUP BY b.division_id')->result_array();
        $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
            foreach($division as $division_record){
                $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
            }
        $html .= '</select>';   

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);         
    }

    function get_department()
    {
        $department = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company_department').' WHERE '.$this->db->dbprefix('user_company_department').'.division_id IN ('.$this->input->post("div_id_delimited").')')->result_array();
        $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
            foreach($department as $department_record){
                $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
            }
        $html .= '</select>';   

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);         
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

		$query_id = '12';

		$this->db->where('export_query_id', $query_id);

		$company = "";

		if($this->input->post('company'))
			$campaign = $this->input->post('company');

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		$sql.= " WHERE ";
		if($this->input->post('date_start') != "" && $this->input->post('date_end') != "")
		{
			$sql_string .= "YEAR(".$this->db->dbprefix."employee.resigned_date) >= '".$this->input->post('date_start')."'AND YEAR(".$this->db->dbprefix."employee.resigned_date) <= '".$this->input->post('date_end')."' AND ";
		}

		if($this->input->post('company') && $this->input->post('company') != 'null')
		{
			$sql_string .= $this->db->dbprefix."user.company_id IN (".implode(",", $this->input->post('company')).") AND ";
		}
		if($this->input->post('division') && $this->input->post('division') != 'null')
		{

			$sql_string .= $this->db->dbprefix."user.division_id IN (".implode(",", $this->input->post('division')).") AND ";
		}
		if($this->input->post('department') && $this->input->post('department') != 'null')
		{
			$sql_string .= $this->db->dbprefix."user.department_id IN (".implode(",", $this->input->post('department')).") AND ";
		}
		$sql_string .= $this->db->dbprefix."employee_movement.employee_movement_type_id = 6 AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.status = 6";
		
		$result  = $this->db->query($sql.$sql_string);


		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
	        $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $total_num_rows;                        

	        $sql_string = "";
	        $sql_limit = "";

	        $response->msg = "";

			$query_id = '12';

			$this->db->where('export_query_id', $query_id);

			$company = "";

			if($this->input->post('company'))
				$campaign = $this->input->post('company');

			$result = $this->db->get('export_query');
			$export = $result->row();
			$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

			$sql.= " WHERE ";
			if($this->input->post('date_start') != "" && $this->input->post('date_end') != "")
			{
				$sql_string .= "YEAR(".$this->db->dbprefix."employee.resigned_date) >= '".$this->input->post('date_start')."'AND YEAR(".$this->db->dbprefix."employee.resigned_date) <= '".$this->input->post('date_end')."' AND ";
			}

			if($this->input->post('company') && $this->input->post('company') != 'null')
			{
				$sql_string .= $this->db->dbprefix."user.company_id IN (".implode(",", $this->input->post('company')).") AND ";
			}
			if($this->input->post('division') && $this->input->post('division') != 'null')
			{

				$sql_string .= $this->db->dbprefix."user.division_id IN (".implode(",", $this->input->post('division')).") AND ";
			}
			if($this->input->post('department') && $this->input->post('department') != 'null')
			{
				$sql_string .= $this->db->dbprefix."user.department_id IN (".implode(",", $this->input->post('department')).") AND ";
			}
			$sql_string .= $this->db->dbprefix."employee_movement.employee_movement_type_id = 6 AND ";
			$sql_string .= $this->db->dbprefix."employee_movement.status = 6";
			$sql_string .= " ORDER BY firstname,middlename,lastname";

	        $start = $limit * $page - $limit;
	        $sql_limit = " LIMIT ".$start.", ".$limit;

	        $result  = $this->db->query($sql.$sql_string.$sql_limit);

	        $ctr = 0;

	        foreach($result->result() as $data)
	        {
	        	$response->rows[$ctr]['cell'][0] = $data->firstname." ".$data->middlename." ".$data->lastname;
	        	$response->rows[$ctr]['cell'][1] = $data->position_title; // ($data->job_title_name == "" ? $data->position : $data->job_title_name);
	        	$response->rows[$ctr]['cell'][2] = date($this->config->item('display_date_format'), strtotime($data->employed_date))." - ".date($this->config->item('display_date_format'), strtotime($data->transfer_effectivity_date));
	        	$difference = $this->get_date_diff($data->employed_date, $data->transfer_effectivity_date);	        	
	        	$response->rows[$ctr]['cell'][3] = ($difference->y == 0 ? "" : $difference->y." Years &")." ".($difference->m > 1 ? $difference->m." Months" : $difference->m." Month");
	        	$response->rows[$ctr]['cell'][4] = $data->reason;
	        	$response->rows[$ctr]['cell'][5] = $data->remarks_leaving;

	        	$ctr++;
	    	}
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function get_date_diff($employed_date, $resigned_date)
	{
		// issues when round. be careful.
		$start = new DateTime($employed_date);
    	$end = new DateTime($resigned_date);
    	$difference = $start->diff($end);
    	return $difference;
	}

	// created because get_date_diff Datetime issues on older version of xampp when round.
	function get_date_diff_xampp_compatibility($employed_date, $resigned_date)
	{
		return date('Y', strtotime($resigned_date)) - date('Y', strtotime($employed_date));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Name of Resigned Employee', 'Position', 'Employment Date', 'Tenure', 'Reasons for leaving', 'Remarks');

		$this->listview_columns = array(
				array('name' => 'Name of Resigned Employee', 'width' => '180','align' => 'center'),
				array('name' => 'Position', 'width' => '180','align' => 'center'),
				array('name' => 'Employment Date', 'width' => '180','align' => 'center'),
				array('name' => 'Tenure', 'width' => '180','align' => 'center'),
				array('name' => 'Reasons for leaving', 'width' => '180','align' => 'center'),
				array('name' => 'Remarks', 'width' => '180','align' => 'center'),
			);                                     
    }

    function resigned_list()
    {
    	$title_for_all = "PIONEER GROUP";
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
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);

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


		$activeSheet->setCellValueExplicit('A1', ($this->input->post('company') == "" ? $title_for_all : $this->db->get_where('user_company', array("company_id" => $this->input->post('company')))->row()->company), PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A2', 'EMPLOYEE TURN-OVER REPORT', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A3', 'For the period of January to December '.$this->input->post('date_year'), PHPExcel_Cell_DataType::TYPE_STRING);

		$activeSheet->setCellValueExplicit('A5', 'Name of Resigned Employee', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('B5', 'Position', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('C5', 'Employment Date', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('D5', 'Tenure', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('E5', 'Reason for Leaving', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('F5', 'Remarks', PHPExcel_Cell_DataType::TYPE_STRING);

		$objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('B5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('C5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('D5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('E5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('F5')->applyFromArray($headerstyle);

    	$query_id = '12';

		$this->db->where('export_query_id', $query_id);

		$company = "";

		if($this->input->post('company'))
			$campaign = $this->input->post('company');

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		$sql.= " WHERE ";
		if($this->input->post('date_start') != "" && $this->input->post('date_end') != "")
		{
			$sql_string .= "YEAR(".$this->db->dbprefix."employee.resigned_date) >= '".$this->input->post('date_start')."'AND YEAR(".$this->db->dbprefix."employee.resigned_date) <= '".$this->input->post('date_end')."' AND ";
		}

		if($this->input->post('company') && $this->input->post('company') != 'null')
		{
			$sql_string .= $this->db->dbprefix."user.company_id IN (".$this->input->post('company').") AND ";
		}
		if($this->input->post('division') && $this->input->post('division') != 'null')
		{

			$sql_string .= $this->db->dbprefix."user.division_id IN (".$this->input->post('division').") AND ";
		}
		if($this->input->post('department') && $this->input->post('department') != 'null')
		{
			$sql_string .= $this->db->dbprefix."user.department_id IN (".$this->input->post('department').") AND ";
		}

		$sql_string .= $this->db->dbprefix."employee_movement.employee_movement_type_id = 6 AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.status = 6";
		
		$result  = $this->db->query($sql.$sql_string);

		$ctr  = 7;
		foreach($result->result() as $data)
		{
			$activeSheet->setCellValueExplicit('A'.$ctr, $data->firstname." ".$data->middlename." ".$data->lastname, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('B'.$ctr, $data->position_title, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('C'.$ctr, date($this->config->item('display_date_format'), strtotime($data->employed_date))." - ".date($this->config->item('display_date_format'), strtotime($data->transfer_effectivity_date)), PHPExcel_Cell_DataType::TYPE_STRING); 
			$difference = $this->get_date_diff($data->employed_date, $data->transfer_effectivity_date);	        	
			$activeSheet->setCellValueExplicit('D'.$ctr, ($difference->y == 0 ? "" : $difference->y." Years &")." ".($difference->m > 1 ? $difference->m." Months" : $difference->m." Month"), PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('E'.$ctr, $data->reason, PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->setCellValueExplicit('F'.$ctr, $data->remarks_leaving, PHPExcel_Cell_DataType::TYPE_STRING);

        	$ctr++;
		}

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=turnover_report'.date('Y-m-d-h-i-s').'.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/dtr_summary/turnover_report-'.date('Y-m-d-g-i-s').'.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));
    }

    function turnover_report()
    {
    	$title_for_all = "PIONEER GROUP";
    	$difference = 0;
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
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension('A')->setWidth(35);


		//Initialize style
		$singlerightborder = array(
			'borders' => array(
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$bottomstyle = array(
			'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$styleArray = array(
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$headerarray = array(
			'font' => array(
				'bold' => true,
			)
		);

		$cellarray = array(
			// 'fill' => array(
   //                  'type' => PHPExcel_Style_Fill::FILL_SOLID,
   //                  'color' => array('rgb'=>'CCC'),
   //          ),
			'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),

			'font' => array(
				'bold' => true,
			),	

			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
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

		$topborder = array(
			'borders' => array(
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK)
				)
			);
		$leftborder = array(
			'borders' => array(
				'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK)
				)
			);
		$rightborder = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				),
			'borders' => array(
				'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK)
				)
			);
		$btmborder = array(
			// 'font' => array(
			// 	'bold' => true,
			// 	),
			'borders' => array(
				'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK)
				)
			);

		$query_id = '12';

		$this->db->where('export_query_id', $query_id);

		$company = "";

		if($this->input->post('company'))
			$campaign = $this->input->post('company');

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		$sql.= " WHERE ";
		if($this->input->post('date_start') != "" && $this->input->post('date_end') != "")
		{
			$sql_string .= "YEAR(".$this->db->dbprefix."employee.resigned_date) >= '".$this->input->post('date_start')."'AND YEAR(".$this->db->dbprefix."employee.resigned_date) <= '".$this->input->post('date_end')."' AND ";
		}

		if($this->input->post('company') && $this->input->post('company') != 'null')
		{
			$sql_string .= $this->db->dbprefix."user.company_id IN (".$this->input->post('company').") AND ";
		}
		if($this->input->post('division') && $this->input->post('division') != 'null')
		{
			$sql_string .= $this->db->dbprefix."user.division_id IN (".$this->input->post('division').") AND ";
		}
		if($this->input->post('department') && $this->input->post('department') != 'null')
		{
			$sql_string .= $this->db->dbprefix."user.department_id IN (".$this->input->post('department').") AND ";
		}

		$sql_string .= $this->db->dbprefix."employee_movement.employee_movement_type_id = 6 AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.status = 6";
		
		$result  = $this->db->query($sql.$sql_string);


		$activeSheet->setCellValueExplicit('A1', ($this->input->post('company') == "" ? $title_for_all : $this->db->get_where('user_company', array("company_id" => $this->input->post('company')))->row()->company), PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A2', 'January to December '.$this->input->post('date_end'), PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->getStyle('A1:A2')->applyFromArray($headerarray);

		$activeSheet->setCellValueExplicit('A4', 'No. of Resignations:', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A5', 'Average No. of Employees:', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A6', 'Turn-over Rate:', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A7', '> Market Data/General Industry (12%):', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('A8', 'Average Tenure of Resigned Employees:', PHPExcel_Cell_DataType::TYPE_STRING);

		$activeSheet->setCellValueExplicit('A10', 'Reasons for Leaving', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('B10', 'Number of Resignations', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('C10', '%', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('D10', 'Action Taken/Next Steps', PHPExcel_Cell_DataType::TYPE_STRING);

		$objPHPExcel->getActiveSheet()->getStyle('A4:C4')->applyFromArray($topborder);
		$objPHPExcel->getActiveSheet()->getStyle('A4:A8')->applyFromArray($leftborder);
		$objPHPExcel->getActiveSheet()->getStyle('C4:C8')->applyFromArray($rightborder);
		$objPHPExcel->getActiveSheet()->getStyle('A8:C8')->applyFromArray($btmborder);
		$objPHPExcel->getActiveSheet()->getStyle('A10:D10')->applyFromArray($cellarray);
		$objPHPExcel->getActiveSheet()->getStyle('A10:D20')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A21:D21')->applyFromArray($bottomstyle);
		$objPHPExcel->getActiveSheet()->getStyle('D21')->applyFromArray($singlerightborder);
		

		$activeSheet->setCellValueExplicit('C4', $result->num_rows(), PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('C5', $avg = $this->average_emp_num($this->input->post('date_start'), $this->input->post('date_end'), $this->input->post('company')), PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('C6', round(($result->num_rows()/$avg)*100, 2)."%", PHPExcel_Cell_DataType::TYPE_STRING);

		foreach($result->result() as $emp_tenure)
		{
			$difference = $this->get_date_diff_xampp_compatibility($emp_tenure->employed_date, $emp_tenure->resigned_date);
			$total_tenure = $total_tenure + $difference;
		}

		$avg_tenure = round($total_tenure/$result->num_rows(), 2);

		$activeSheet->setCellValueExplicit('C8', ($avg_tenure <= 1 ? $avg_tenure." Year" : $avg_tenure." Years"), PHPExcel_Cell_DataType::TYPE_STRING);

		$ctr = 11;

		$reason_for_leaving = $this->db->get_where('reason_for_leaving', array("deleted" => 0))->result();

		// $for_without_reason = $this->db->get_where('employee_movement')
		foreach($reason_for_leaving as $rfl)
		{
			$activeSheet->setCellValueExplicit('A'.$ctr, ($ctr-10).". ".$rfl->reason_for_leaving, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('B'.$ctr, $number_reason = $this->employee_based_on_reason($sql, $sql_string, $rfl->reason_for_leaving_id), PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('C'.$ctr, round(($number_reason/$result->num_rows())*100, 2)."%", PHPExcel_Cell_DataType::TYPE_STRING); 

			$total = ($number_reason/$result->num_rows()*100) + $total;
        	$ctr++;
		}

		$activeSheet->setCellValueExplicit('A'.$ctr, "Total Number of Resignations", PHPExcel_Cell_DataType::TYPE_STRING); 
		$activeSheet->setCellValueExplicit('B'.$ctr, $result->num_rows(), PHPExcel_Cell_DataType::TYPE_STRING); 
		$activeSheet->setCellValueExplicit('C'.$ctr, round($total, 2)."%", PHPExcel_Cell_DataType::TYPE_STRING); 

    	// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=turnover_rate'.date('Y-m-d-H-i-s').'.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/dtr_summary/Turnover Rate-'.date('Y-m-d-H-i-s').'.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));
    }

    function employee_based_on_reason($sql, $sql_string, $reason_for_leaving_id = null)
    {
    	if($reason_for_leaving_id != null)
    	{
    		$sql_string .= " AND ".$this->db->dbprefix."employee_movement.reason_for_leaving = ".$reason_for_leaving_id;
    		$emp = $this->db->query($sql.$sql_string);
    		// $emp = $this->db->get_where('employee_movement', array("reason_for_leaving" => $reason_for_leaving_id, "deleted" => 0, "status" => 6));
    		if($emp && $emp->num_rows() > 0)
    			return $emp->num_rows();
    		else 
    			return 0;
    	}
    	else
    		return FALSE;
    }

    function average_emp_num($year_start, $year_end, $company = null, $division = null, $department = null)
    {
    	$s_qry = "SELECT
				  employed_date,
				  hr_user.company_id
				FROM hr_employee
				  LEFT JOIN hr_user
				    ON hr_user.employee_id = hr_employee.employee_id
				WHERE ";

		if($company != null)
			$s_qry .= $this->db->dbprefix."user.company_id IN (".$this->input->post('company').") AND ";

		if($division != null)
			$sql_string .= $this->db->dbprefix."user.division_id IN (".$this->input->post('division').") AND ";

		if($department != null)
			$sql_string .= $this->db->dbprefix."user.department_id IN (".$this->input->post('department').") AND ";


		$s_qry .= "((employed_date < '".$year_start."-01-01'
			          AND resigned_date IS NULL
			          AND hr_employee.deleted = 0
			          AND hr_employee.employee_id <> 1)
			          OR (employed_date < '".$year_start."-01-01'
			              AND resigned_date >= '".$year_start."-01-01'
			              AND hr_employee.deleted = 0
			              AND hr_employee.employee_id <> 1))";

    	$emp_start_year = $this->db->query($s_qry);

    	$e_qry = "SELECT
				  employed_date,
				  resigned_date
				FROM hr_employee
				  LEFT JOIN hr_user
				    ON hr_user.employee_id = hr_employee.employee_id
				WHERE ";

		if($company != null)
			$s_qry .= $this->db->dbprefix."user.company_id IN (".$this->input->post('company').") AND ";

		if($division != null)
			$sql_string .= $this->db->dbprefix."user.division_id IN (".$this->input->post('division').") AND ";

		if($department != null)
			$sql_string .= $this->db->dbprefix."user.department_id IN (".$this->input->post('department').") AND ";

		$e_qry .= "((employed_date BETWEEN '".$year_end."-01-01'
			          AND '".$year_end."-12-31'
			          AND hr_employee.deleted = 0
			          AND hr_employee.employee_id <> 1)
			          OR (employed_date < '".$year_end."-01-01'
			              AND resigned_date >= '".$year_end."-01-01'
			              AND resigned_date <= '".$year_end."-12-31'
			              AND hr_employee.deleted = 0
			              AND hr_employee.employee_id <> 1))";

    	$emp_end_year = $this->db->query($e_qry);

    	return round(($emp_start_year->num_rows() + ($emp_start_year->num_rows() - $emp_end_year->num_rows())) / 2);
    }

	function export() {	
		//campaign is company
		$query_id = '11';

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where('export_query_id', $query_id);

		$campaign = "";

		if($this->input->post('campaign'))
			$campaign = $this->input->post('campaign');

		$result = $this->db->get('export_query');
		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		$sql.= " WHERE ";
		$sql_string .= "((".$this->db->dbprefix."employee_movement.compensation_effectivity_date >= '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.compensation_effectivity_date <= '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."') OR ";
		$sql_string .= "(".$this->db->dbprefix."employee_movement.movement_effectivity_date >= '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.movement_effectivity_date <= '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."') OR ";
		$sql_string .= "(".$this->db->dbprefix."employee_movement.transfer_effectivity_date >= '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.transfer_effectivity_date <= '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."')) AND ";

		$sql_string .= $this->db->dbprefix."user.company_id = '".$this->input->post('campaign')."' AND ";
		$sql_string .= $this->db->dbprefix."employee_movement.deleted = 0 AND ";
		$sql_string .= "(".$this->db->dbprefix."employee_movement.status = 3 OR ";
		$sql_string .= $this->db->dbprefix."employee_movement.status = 6)";

		$sql_string .= " ORDER BY ".$this->db->dbprefix."user.lastname DESC";

		$query  = $this->db->query($sql.$sql_string);

		$fields = $query->list_fields();

		$qry = "SELECT *
				FROM {dbprefix}employee 
				LEFT JOIN {dbprefix}user
				ON {dbprefix}employee.employee_id = {dbprefix}user.employee_id
				LEFT JOIN {dbprefix}user_company_department
				ON {dbprefix}user.department_id = {dbprefix}user_company_department.department_id
				WHERE {dbprefix}employee.deleted = 0
				AND {dbprefix}user.inactive = 0";
				if($this->input->post('campaign') != "")
					$qry .= " AND {dbprefix}user.company_id = ".$this->input->post('campaign');
				if($this->input->post('date_period_start') != "")
					$qry.= " AND {dbprefix}employee.employed_date BETWEEN '".date('Y-m-d', strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d', strtotime($this->input->post('date_period_end')))."'";
				else 
					$qry.= " AND {dbprefix}employee.employed_date BETWEEN '".date('Y-m')."-01' AND '".date('Y-m')."-30'";

		$qry = str_replace('{dbprefix}', $this->db->dbprefix, $qry);

		$hiring = $this->db->query($qry);

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;
		$this->_company = $this->input->post('campaign');
		$this->_hiring = $hiring;
		$this->_excel_export();
	}
	
	private function _excel_export()
	{

		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;
		$hiring = $this->_hiring;
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
		$hiring = $hiring->result();

		//header
		$alphabet  = range('A','Z');
		// foreach($alphabet as $letter)
		// 	array_push($alphabet, 'A'.$letter);
		// foreach($alphabet as $letter)
		// 	array_push($alphabet, 'B'.$letter);

		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(35);

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
		$activeSheet->setCellValueExplicit('A5', 'Date', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('B5', 'Company/Div/Dept/Branch', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('C5', 'Movement', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('D5', 'Employee', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('E5', 'Position', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('F5', 'Rank Code', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('G5', 'Range of Rank', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('H5', 'Changes', PHPExcel_Cell_DataType::TYPE_STRING);

		$objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('B5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('C5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('D5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('E5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('F5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('G5')->applyFromArray($headerstyle);
		$objPHPExcel->getActiveSheet()->getStyle('H5')->applyFromArray($headerstyle);
		// echo $this->_company."|"; exit();

		$company_code = $this->db->get_where('user_company', array('company_id' => $company_code))->row();
		$code = $company_code->company_code;

		$activeSheet->setCellValueExplicit('A1', $code.' Movements For the period covering from '.date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))), PHPExcel_Cell_DataType::TYPE_STRING); 

		$line_ctr = 6;
		$name = "";

		foreach($query as $movement) {
			$changes = "";
			$current = "";
			$changes_data = array();
			$old_data = array();

			if($movement->compensation_effectivity_date	!= null)
				$activeSheet->setCellValueExplicit('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($movement->compensation_effectivity_date)), PHPExcel_Cell_DataType::TYPE_STRING); 
			if($movement->movement_effectivity_date	!= null)
				$activeSheet->setCellValueExplicit('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($movement->movement_effectivity_date)), PHPExcel_Cell_DataType::TYPE_STRING); 
			if($movement->transfer_effectivity_date	!= null)
				$activeSheet->setCellValueExplicit('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($movement->transfer_effectivity_date)), PHPExcel_Cell_DataType::TYPE_STRING); 
			if($movement->last_day != null)
				$activeSheet->setCellValueExplicit('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($movement->last_day)), PHPExcel_Cell_DataType::TYPE_STRING); 


			$activeSheet->setCellValueExplicit('B'.$line_ctr, $movement->show_dept, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('C'.$line_ctr, $movement->movement_type, PHPExcel_Cell_DataType::TYPE_STRING); 
			$name = $movement->firstname." ".$movement->middlename." ".$movement->lastname;
			$activeSheet->setCellValueExplicit('D'.$line_ctr, $name, PHPExcel_Cell_DataType::TYPE_STRING); 

			$user_pos = $this->db->get_where('user_position', array('position_id' => $movement->position_id))->row();
				$activeSheet->setCellValueExplicit('E'.$line_ctr, $user_pos->position, PHPExcel_Cell_DataType::TYPE_STRING); 

			$show_rank = $this->db->get_where('user_rank_code', array('job_rank_code_id' => $movement->show_rank))->row();
				$activeSheet->setCellValueExplicit('F'.$line_ctr, $show_rank->job_rank_code, PHPExcel_Cell_DataType::TYPE_STRING); 

			$show_ror = $this->db->get_where('user_rank_range', array('job_rank_range_id' => $movement->show_ror))->row();
				$activeSheet->setCellValueExplicit('G'.$line_ctr, $show_ror->job_rank_range, PHPExcel_Cell_DataType::TYPE_STRING); 


				if($movement->transfer_to != 0)
				{
					$result_data = $this->db->get_where('user_company_department', array("department_id" => $movement->transfer_to))->row();
					$changes_data['department'] = $result_data->department;
					$result_data = $this->db->get_where('user_company_department', array("department_id" => $movement->current_department_id))->row();
					$old_data['department'] = $result_data->department;
				}
				if($movement->new_position_id != 0)
				{
					$result_data = $this->db->get_where('user_position', array("position_id" => $movement->new_position_id))->row();
					$changes_data['position'] = $result_data->position;
					$result_data = $this->db->get_where('user_position', array("position_id" => $movement->current_position_id))->row();
					$old_data['position'] = $result_data->position;
				}
				if($movement->rank_id != 0)
				{
					$result_data = $this->db->get_where('user_rank', array("job_rank_id" => $movement->rank_id))->row();
					$changes_data['rank'] = $result_data->job_rank;
					// $result_data = $this->db->get_where('user_rank', array("job_rank_id" => $movement->rank_id));->row();
					// $old_data[] = $result_data->job_rank;
					$old_data['rank'] = $movement->current_rank_dummy;
				}
				if($movement->job_level != 0)
				{
					$result_data = $this->db->get_where('user_job_level', array("job_level_id" => $movement->job_level))->row();
					$changes_data['job_level'] = $result_data->job_level;
					$old_data['job_level'] = $movement->current_job_level_dummy;
				}
				if($movement->range_of_rank != 0)
				{
					$result_data = $this->db->get_where('user_rank_range', array("job_rank_range_id" => $movement->range_of_rank))->row();
					$changes_data['range_of_rank'] = $result_data->job_rank_range;
					$old_data['range_of_rank'] = $movement->current_range_of_rank_dummy;
				}
				if($movement->rank_code != 0)
				{
					$result_data = $this->db->get_where('user_rank_code', array("job_rank_code_id" => $movement->rank_code))->row();
					$changes_data['rank_code'] = $result_data->job_rank_code;
					$old_data['rank_code'] = $movement->current_rank_code_dummy;
				}
				if($movement->location_id != 0)
				{
					$result_data = $this->db->get_where('user_location', array("location_id" => $movement->location_id))->row();
					$changes_data['location'] = $result_data->location;
					$old_data['location'] = $movement->current_location_dummy;
				}
				if($movement->company_id != 0)
				{
					$result_data = $this->db->get_where('user_company', array("user_company" => $movement->company_id))->row();
					$changes_data['company'] = $result_data->company;
					$old_data['company'] = $movement->current_company_dummy;
				}
				if($movement->segment_1_id != 0)
				{
					$result_data = $this->db->get_where('user_company_segment_1', array("segment_1_id" => $movement->segment_1_id))->row();
					$changes_data['segment_1'] = $result_data->segment_1;
					$old_data['segment_1'] = $movement->current_segment_1_dummy;
				}
				if($movement->segment_2_id != 0)
				{
					$result_data = $this->db->get_where('user_company_segment_2', array("segment_2_id" => $movement->segment_2_id))->row();
					$changes_data['segment_2'] = $result_data->segment_2;
					$old_data['segment_2'] = $movement->current_segment_2_dummy;
				}
				if($movement->division_id != 0)
				{
					$result_data = $this->db->get_where('user_company_division', array("division_id" => $movement->division_id))->row();
					$changes_data['division'] = $result_data->division;
					$old_data['division'] = $movement->current_division_dummy;
				}

				//printing
				$old_data = array_filter($old_data);
				$changes_data = array_filter($changes_data);
				if(count($changes_data) > 0)
				{
					$format_change = "";
					foreach($changes_data as $key=>$changes_data)
					{
						$format_change .= "From : ".$old_data[$key]."\n To : ".$changes_data."\n";
						// $changes = $changes_data.", ".$changes;
						// $current = $old_data[$key].", ".$current;
					}
					$activeSheet->setCellValueExplicit('H'.$line_ctr, $format_change, PHPExcel_Cell_DataType::TYPE_STRING); 
				}
			$line_ctr++;
		}

		foreach($hiring as $employed_date)
		{

			$activeSheet->setCellValueExplicit('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($employed_date->employed_date)), PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('B'.$line_ctr, $employed_date->department, PHPExcel_Cell_DataType::TYPE_STRING); 
			$activeSheet->setCellValueExplicit('C'.$line_ctr, 'Hiring', PHPExcel_Cell_DataType::TYPE_STRING); 
			$name = $employed_date->firstname." ".$employed_date->middlename." ".$employed_date->lastname;
			$activeSheet->setCellValueExplicit('D'.$line_ctr, $name, PHPExcel_Cell_DataType::TYPE_STRING); 

			$user_pos = $this->db->get_where('user_position', array('position_id' => $employed_date->position_id))->row();
				$activeSheet->setCellValueExplicit('E'.$line_ctr, $user_pos->position, PHPExcel_Cell_DataType::TYPE_STRING); 

			$show_rank = $this->db->get_where('user_rank_code', array('job_rank_code_id' => $employed_date->rank_code))->row();
				$activeSheet->setCellValueExplicit('F'.$line_ctr, $show_rank->job_rank_code, PHPExcel_Cell_DataType::TYPE_STRING); 

			$show_ror = $this->db->get_where('user_rank_range', array('job_rank_range_id' => $employed_date->range_of_rank))->row();
				$activeSheet->setCellValueExplicit('G'.$line_ctr, $show_ror->job_rank_range, PHPExcel_Cell_DataType::TYPE_STRING); 

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
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' . url_title($export->description) . '.xls');
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