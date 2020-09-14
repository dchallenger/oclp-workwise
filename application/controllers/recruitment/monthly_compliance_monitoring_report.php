<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monthly_compliance_monitoring_report extends MY_Controller
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
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'recruitment/monthly_compliance_monitoring_report_listview';
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$this->load->model('uitype_edit');
		
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
	// END - default module functions
	
	// START custom module funtions
	/**
	 * [listview description]
	 * Pivot Point - +/-6months from birthdate
	 * Pivot Point is to be considered for which health info to include in which year
	 * @return [type] [description]
	 */
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
      
        $total_pages = 12 > 0 ? ceil(12/$limit) : 0;
        $response->page = $page > $total_pages ? $total_pages : $page;
        $response->total = $total_pages;
        $response->records = 0;                      

        $response->msg = "";

        $start = $limit * $page - $limit;
        $this->db->limit($limit, $start);  

    	$year = $this->input->post('year');
    	$month = $this->input->post('month');

    	$result_total = $this->get_query_total($year,$month,0,3);
    	$percentage = 100;
    	$position_required = 0;
    	$position_hired = 0;
    	$position_pending = 0;
    	$compliance_level = 0;
    	if ($result_total && $result_total->num_rows() > 0){
    		$row = $result_total->row();
	    	$position_required = ($row->total_position_required == '' ? 0 : $row->total_position_required);
	    	$position_hired = ($row->total_position_hired == '' ? 0 : $row->total_position_hired);
	    	$position_pending = ($row->total_position_hired_beyond_tat_and_pending == '' ? 0 : $row->total_position_hired);
			$diff = (($row->total_position_required - $row->total_position_hired) / $row->total_position_required) * 100;
			if ($diff > 0){
				$compliance_level = $percentage - $diff;
			}	    	
    	}

        $response->rows[0]['cell'][0] = "Non Officer/Non Technical positions required (R&F 30 days)";
        $response->rows[1]['cell'][0] = "No. of positions hired within expected TAT";
        $response->rows[2]['cell'][0] = "No. of positions hired/pending (beyond expected TAT)";
        $response->rows[3]['cell'][0] = "Compliance level";

		$response->rows[0]['cell'][1] =  $position_required;
		$response->rows[1]['cell'][1] =  $position_hired;
		$response->rows[2]['cell'][1] =  $position_pending;
		$response->rows[3]['cell'][1] =  ceil($compliance_level) . '%';

		$result = $this->get_query($year,$month,0,3);
		
		$ctr = 4;
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$nr1 = '';
				$nr2 = '';
				$nr3 = '';
				switch ($row->reason_for_request) {
					case 1:
						$nr1 = 'Yes';
						break;
					case 2:
						$nr2 = 'Yes';
						break;
					case 3:
						$nr3 = 'Yes';
						break;												
				}
				$target = ($row->target > 1 ? $row->target . ' days' : $row->target . ' day' );
				$actual = ($row->actual > 1 ? $row->actual . ' days' : $row->actual . ' day' );

	            $response->rows[$ctr]['cell'][0] = $row->division .'/'. $row->department;
	            $response->rows[$ctr]['cell'][1] = $row->employment_status;
	            $response->rows[$ctr]['cell'][2] = $row->position;
	            $response->rows[$ctr]['cell'][3] = $row->document_number;
	            $response->rows[$ctr]['cell'][4] = $nr1;
	            $response->rows[$ctr]['cell'][5] = $nr2;
	            $response->rows[$ctr]['cell'][6] = $nr3;
	            $response->rows[$ctr]['cell'][7] = $row->name;
	            $response->rows[$ctr]['cell'][8] = $row->date_request_approved;
	            $response->rows[$ctr]['cell'][9] = $row->date_placement;
	            $response->rows[$ctr]['cell'][10] = $row->contract_date;
	            $response->rows[$ctr]['cell'][11] = $target;
	            $response->rows[$ctr]['cell'][12] = $actual;
	            $response->rows[$ctr]['cell'][13] = $row->contacted_thru;
	            $ctr++;
			}
		}
		else{
            $response->rows[$ctr]['cell'][0] = '';
            $response->rows[$ctr]['cell'][1] = '';
            $response->rows[$ctr]['cell'][2] = '';
            $response->rows[$ctr]['cell'][3] = '';
            $response->rows[$ctr]['cell'][4] = '';
            $response->rows[$ctr]['cell'][5] = '';
            $response->rows[$ctr]['cell'][6] = '';
            $response->rows[$ctr]['cell'][7] = '';
            $response->rows[$ctr]['cell'][8] = '';
            $response->rows[$ctr]['cell'][9] = '';
            $response->rows[$ctr]['cell'][10] = '';
            $response->rows[$ctr]['cell'][11] = '';
            $response->rows[$ctr]['cell'][12] = '';
            $response->rows[$ctr]['cell'][13] = '';
            $ctr++;
		}

        for ($i=0; $i < 13; $i++) { 
        	$response->rows[$ctr]['cell'][$i] = "";
        }		
        // end of non officer

		//sup officer    	
    	$result_total = $this->get_query_total($year,$month,0,2);
    	$percentage = 100;
    	$position_required = 0;
    	$position_hired = 0;
    	$position_pending = 0;
    	$compliance_level = 0;
    	if ($result_total && $result_total->num_rows() > 0){
    		$row = $result_total->row();
	    	$position_required = ($row->total_position_required == '' ? 0 : $row->total_position_required);
	    	$position_hired = ($row->total_position_hired == '' ? 0 : $row->total_position_hired);
	    	$position_pending = ($row->total_position_hired_beyond_tat_and_pending == '' ? 0 : $row->total_position_hired);
			$diff = (($row->total_position_required - $row->total_position_hired) / $row->total_position_required) * 100;
			if ($diff > 0){
				$compliance_level = $percentage - $diff;
			}	    	
    	}

    	$ctr_t = $ctr++;
    	$ctr_t++;
        $response->rows[$ctr++]['cell'][0] = "Sup-Officer/Non Technical positions required (45 days)";
        $response->rows[$ctr++]['cell'][0] = "No. of positions hired within expected TAT";
        $response->rows[$ctr++]['cell'][0] = "No. of positions hired/pending (beyond expected TAT)";
        $response->rows[$ctr++]['cell'][0] = "Compliance level";

		$response->rows[$ctr_t++]['cell'][1] =  $position_required;
		$response->rows[$ctr_t++]['cell'][1] =  $position_hired;
		$response->rows[$ctr_t++]['cell'][1] =  $position_pending;
		$response->rows[$ctr_t++]['cell'][1] =  ceil($compliance_level) . '%';

		$result = $this->get_query($year,$month,0,2);

		if ($result && $result->num_rows() > 0){
			$ctr = $ctr_t++;
			foreach ($result->result() as $row) {
				$nr1 = '';
				$nr2 = '';
				$nr3 = '';
				switch ($row->reason_for_request) {
					case 1:
						$nr1 = 'Yes';
						break;
					case 2:
						$nr2 = 'Yes';
						break;
					case 3:
						$nr3 = 'Yes';
						break;												
				}
				$target = ($row->target > 1 ? $row->target . ' days' : $row->target . ' day' );
				$actual = ($row->actual > 1 ? $row->actual . ' days' : $row->actual . ' day' );

	            $response->rows[$ctr]['cell'][0] = $row->division .'/'. $row->department;
	            $response->rows[$ctr]['cell'][1] = $row->employment_status;
	            $response->rows[$ctr]['cell'][2] = $row->position;
	            $response->rows[$ctr]['cell'][3] = $row->document_number;
	            $response->rows[$ctr]['cell'][4] = $nr1;
	            $response->rows[$ctr]['cell'][5] = $nr2;
	            $response->rows[$ctr]['cell'][6] = $nr3;
	            $response->rows[$ctr]['cell'][7] = $row->name;
	            $response->rows[$ctr]['cell'][8] = $row->date_request_approved;
	            $response->rows[$ctr]['cell'][9] = $row->date_placement;
	            $response->rows[$ctr]['cell'][10] = $row->contract_date;
	            $response->rows[$ctr]['cell'][11] = $target;
	            $response->rows[$ctr]['cell'][12] = $actual;
	            $response->rows[$ctr]['cell'][13] = $row->contacted_thru;
	            $ctr++;
			}
		}
		else{
            $response->rows[$ctr]['cell'][0] = '';
            $response->rows[$ctr]['cell'][1] = '';
            $response->rows[$ctr]['cell'][2] = '';
            $response->rows[$ctr]['cell'][3] = '';
            $response->rows[$ctr]['cell'][4] = '';
            $response->rows[$ctr]['cell'][5] = '';
            $response->rows[$ctr]['cell'][6] = '';
            $response->rows[$ctr]['cell'][7] = '';
            $response->rows[$ctr]['cell'][8] = '';
            $response->rows[$ctr]['cell'][9] = '';
            $response->rows[$ctr]['cell'][10] = '';
            $response->rows[$ctr]['cell'][11] = '';
            $response->rows[$ctr]['cell'][12] = '';
            $response->rows[$ctr]['cell'][13] = '';
            $ctr++;
		}		

        for ($i=0; $i < 13; $i++) { 
        	$response->rows[$ctr]['cell'][$i] = "";
        }	
        // end of sup officer

		//IT officer    	
    	$result_total = $this->get_query_total($year,$month,1,2);
    	$percentage = 100;
    	$position_required = 0;
    	$position_hired = 0;
    	$position_pending = 0;
    	$compliance_level = 0;
    	if ($result_total && $result_total->num_rows() > 0){
    		$row = $result_total->row();
	    	$position_required = ($row->total_position_required == '' ? 0 : $row->total_position_required);
	    	$position_hired = ($row->total_position_hired == '' ? 0 : $row->total_position_hired);
	    	$position_pending = ($row->total_position_hired_beyond_tat_and_pending == '' ? 0 : $row->total_position_hired);
			$diff = (($row->total_position_required - $row->total_position_hired) / $row->total_position_required) * 100;
			if ($diff > 0){
				$compliance_level = $percentage - $diff;
			}	    	
    	}

    	$ctr_t = $ctr++;
    	$ctr_t++;
        $response->rows[$ctr++]['cell'][0] = "IT Officer/Technical positions required (60 days)";
        $response->rows[$ctr++]['cell'][0] = "No. of positions hired within expected TAT";
        $response->rows[$ctr++]['cell'][0] = "No. of positions hired/pending (beyond expected TAT)";
        $response->rows[$ctr++]['cell'][0] = "Compliance level";

		$response->rows[$ctr_t++]['cell'][1] =  $position_required;
		$response->rows[$ctr_t++]['cell'][1] =  $position_hired;
		$response->rows[$ctr_t++]['cell'][1] =  $position_pending;
		$response->rows[$ctr_t++]['cell'][1] =  ceil($compliance_level) . '%';

		$result = $this->get_query($year,$month,1,2);

		if ($result && $result->num_rows() > 0){
			$ctr = $ctr_t++;
			foreach ($result->result() as $row) {
				$nr1 = '';
				$nr2 = '';
				$nr3 = '';
				switch ($row->reason_for_request) {
					case 1:
						$nr1 = 'Yes';
						break;
					case 2:
						$nr2 = 'Yes';
						break;
					case 3:
						$nr3 = 'Yes';
						break;												
				}
				$target = ($row->target > 1 ? $row->target . ' days' : $row->target . ' day' );
				$actual = ($row->actual > 1 ? $row->actual . ' days' : $row->actual . ' day' );

	            $response->rows[$ctr]['cell'][0] = $row->division .'/'. $row->department;
	            $response->rows[$ctr]['cell'][1] = $row->employment_status;
	            $response->rows[$ctr]['cell'][2] = $row->position;
	            $response->rows[$ctr]['cell'][3] = $row->document_number;
	            $response->rows[$ctr]['cell'][4] = $nr1;
	            $response->rows[$ctr]['cell'][5] = $nr2;
	            $response->rows[$ctr]['cell'][6] = $nr3;
	            $response->rows[$ctr]['cell'][7] = $row->name;
	            $response->rows[$ctr]['cell'][8] = $row->date_request_approved;
	            $response->rows[$ctr]['cell'][9] = $row->date_placement;
	            $response->rows[$ctr]['cell'][10] = $row->contract_date;
	            $response->rows[$ctr]['cell'][11] = $target;
	            $response->rows[$ctr]['cell'][12] = $actual;
	            $response->rows[$ctr]['cell'][13] = $row->contacted_thru;
	            $ctr++;
			}
		}
		else{
            $response->rows[$ctr]['cell'][0] = '';
            $response->rows[$ctr]['cell'][1] = '';
            $response->rows[$ctr]['cell'][2] = '';
            $response->rows[$ctr]['cell'][3] = '';
            $response->rows[$ctr]['cell'][4] = '';
            $response->rows[$ctr]['cell'][5] = '';
            $response->rows[$ctr]['cell'][6] = '';
            $response->rows[$ctr]['cell'][7] = '';
            $response->rows[$ctr]['cell'][8] = '';
            $response->rows[$ctr]['cell'][9] = '';
            $response->rows[$ctr]['cell'][10] = '';
            $response->rows[$ctr]['cell'][11] = '';
            $response->rows[$ctr]['cell'][12] = '';
            $response->rows[$ctr]['cell'][13] = '';
            $ctr++;
		}		

        for ($i=0; $i < 13; $i++) { 
        	$response->rows[$ctr]['cell'][$i] = "";
        }	
        // end of IT officer        

		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{		
		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Employee Roster Report")
		            ->setDescription("Employee Roster Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 1;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		//$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
		//$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
		//$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$fontBold = array(
			'font' => array(
				'bold' => true,
			)
		);

		$HorizontalLeft = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);				

		$HorizontalRight = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
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
		$vertical_center = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);		

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', 'Pioneer Insurance');
		$activeSheet->setCellValue('A2', 'Monthly Compliance Monitoring Report for (' . date('F',strtotime($this->input->post('date_month'))) .')');
		$activeSheet->setCellValue('A3', 'As of ' . date('M d, Y'));

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells('A'.$ctr.':'.'N'.$ctr);

		}
		
		$objPHPExcel->getActiveSheet()->getStyle('A6:L6')->applyFromArray($styleArray);

		// contents.
		$line = 7;

    	$year = $this->input->post('date_year');
    	$month = $this->input->post('date_month');

		//non officer	 
    	$result_total = $this->get_query_total($year,$month,0,3);
    	$percentage = 100;
    	$position_required = 0;
    	$position_hired = 0;
    	$position_pending = 0;
    	$compliance_level = 0;
    	if ($result_total && $result_total->num_rows() > 0){
    		$row = $result_total->row();
	    	$position_required = ($row->total_position_required == '' ? 0 : $row->total_position_required);
	    	$position_hired = ($row->total_position_hired == '' ? 0 : $row->total_position_hired);
	    	$position_pending = ($row->total_position_hired_beyond_tat_and_pending == '' ? 0 : $row->total_position_hired);
			$diff = (($row->total_position_required - $row->total_position_hired) / $row->total_position_required) * 100;
			if ($diff > 0){
				$compliance_level = $percentage - $diff;
			}	    	
    	}

		$line_t = $line;

		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "Non Officer/Non Technical positions required (R&F 30 days)");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "No. of positions hired within expected TAT");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "No. of positions hired/pending (beyond expected TAT)");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "Compliance level");

		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_required);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_hired);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_pending);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, ceil($compliance_level) . '%');

		$objPHPExcel->getActiveSheet()->getStyle('B' . ($line_t - 1))->applyFromArray($HorizontalRight);

		$line++;
		$activeSheet->setCellValue('A' . $line, "Division/Department");
		$activeSheet->setCellValue('B' . $line, "Employment Status");
		$activeSheet->setCellValue('C' . $line, "Position");
		$activeSheet->setCellValue('D' . $line, "MRF No.");
		$activeSheet->setCellValue('E' . $line, "Nature of Request");
		$activeSheet->setCellValue('E' . ($line + 1), "Existing Job");		
		$activeSheet->setCellValue('F' . ($line + 1), "Additional Head Count/Existing Job");				
		$activeSheet->setCellValue('G' . ($line + 1), "Additional Head Count/Newly Created Job");
		$activeSheet->setCellValue('H' . $line, "Status");
		$activeSheet->setCellValue('I' . $line, "Date of Request Approved");
		$activeSheet->setCellValue('J' . $line, "Date of Placement");
		$activeSheet->setCellValue('K' . $line, "Contract Date");
		$activeSheet->setCellValue('L' . $line, "TAT");
		$activeSheet->setCellValue('L' . ($line + 1), "Target");
		$activeSheet->setCellValue('M' . ($line + 1), "Actual");
		$activeSheet->setCellValue('N' . $line, "Source");

		$objPHPExcel->getActiveSheet()->mergeCells('E'.$line.':'.'G'.$line);
		$objPHPExcel->getActiveSheet()->mergeCells('L'.$line.':'.'M'.$line);
		$objPHPExcel->getActiveSheet()->getStyle('E' . $line .':' . 'G' . $line)->applyFromArray($HorizontalCenter);
		$objPHPExcel->getActiveSheet()->getStyle('L' . $line .':' . 'M' . $line)->applyFromArray($HorizontalCenter);

		$result = $this->get_query($year,$month,0,3);

		$org_line = $line;
		$line += 2;
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$nr1 = '';
				$nr2 = '';
				$nr3 = '';
				switch ($row->reason_for_request) {
					case 1:
						$nr1 = 'Yes';
						break;
					case 2:
						$nr2 = 'Yes';
						break;
					case 3:
						$nr3 = 'Yes';
						break;												
				}
				$target = ($row->target > 1 ? $row->target . ' days' : $row->target . ' day' );
				$actual = ($row->actual > 1 ? $row->actual . ' days' : $row->actual . ' day' );

				$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $row->division .'/'. $row->department);
				$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $row->employment_status);
				$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, $row->position);
				$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, $row->document_number);
				$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, $nr1);
				$objPHPExcel->getActiveSheet()->setCellValue('F' . $line, $nr2);
				$objPHPExcel->getActiveSheet()->setCellValue('G' . $line, $nr3);
				$objPHPExcel->getActiveSheet()->setCellValue('H' . $line, $row->name);
				$objPHPExcel->getActiveSheet()->setCellValue('I' . $line, $row->date_request_approved);
				$objPHPExcel->getActiveSheet()->setCellValue('J' . $line, $row->date_placement);
				$objPHPExcel->getActiveSheet()->setCellValue('K' . $line, $row->contract_date);
				$objPHPExcel->getActiveSheet()->setCellValue('L' . $line, $target);
				$objPHPExcel->getActiveSheet()->setCellValue('M' . $line, $actual);
				$objPHPExcel->getActiveSheet()->setCellValue('N' . $line, $row->contacted_thru);
	            $line++;
			}
		}

		$objPHPExcel->getActiveSheet()->mergeCells('A'.$org_line.':'.'A'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('B'.$org_line.':'.'B'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('C'.$org_line.':'.'C'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('D'.$org_line.':'.'D'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('H'.$org_line.':'.'H'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('I'.$org_line.':'.'I'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('J'.$org_line.':'.'J'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('K'.$org_line.':'.'K'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('N'.$org_line.':'.'N'.($org_line + 1));

		$objPHPExcel->getActiveSheet()->getStyle('A'.$org_line.':'.'A'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('B'.$org_line.':'.'B'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('C'.$org_line.':'.'C'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('D'.$org_line.':'.'D'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('H'.$org_line.':'.'H'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('I'.$org_line.':'.'I'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('J'.$org_line.':'.'J'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('K'.$org_line.':'.'K'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('N'.$org_line.':'.'N'.($org_line + 1))->applyFromArray($vertical_center);

		$objPHPExcel->getActiveSheet()->getStyle('A' . $org_line . ':' . 'N' . ($line - 1))->applyFromArray($styleArrayBorder);
		// end non officer

		//Sup officer	 
    	$result_total = $this->get_query_total($year,$month,0,2);
    	$percentage = 100;
    	$position_required = 0;
    	$position_hired = 0;
    	$position_pending = 0;
    	$compliance_level = 0;
    	if ($result_total && $result_total->num_rows() > 0){
    		$row = $result_total->row();
	    	$position_required = ($row->total_position_required == '' ? 0 : $row->total_position_required);
	    	$position_hired = ($row->total_position_hired == '' ? 0 : $row->total_position_hired);
	    	$position_pending = ($row->total_position_hired_beyond_tat_and_pending == '' ? 0 : $row->total_position_hired);
			$diff = (($row->total_position_required - $row->total_position_hired) / $row->total_position_required) * 100;
			if ($diff > 0){
				$compliance_level = $percentage - $diff;
			}	    	
    	}

    	$line++;
		$line_t = $line;

		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "Sup-Officer/Non Technical positions required (45 days)");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "No. of positions hired within expected TAT");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "No. of positions hired/pending (beyond expected TAT)");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "Compliance level");

		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_required);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_hired);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_pending);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, ceil($compliance_level) . '%');

		$objPHPExcel->getActiveSheet()->getStyle('B' . ($line_t - 1))->applyFromArray($HorizontalRight);

		$line++;
		$activeSheet->setCellValue('A' . $line, "Division/Department");
		$activeSheet->setCellValue('B' . $line, "Employment Status");
		$activeSheet->setCellValue('C' . $line, "Position");
		$activeSheet->setCellValue('D' . $line, "MRF No.");
		$activeSheet->setCellValue('E' . $line, "Nature of Request");
		$activeSheet->setCellValue('E' . ($line + 1), "Existing Job");		
		$activeSheet->setCellValue('F' . ($line + 1), "Additional Head Count/Existing Job");				
		$activeSheet->setCellValue('G' . ($line + 1), "Additional Head Count/Newly Created Job");
		$activeSheet->setCellValue('H' . $line, "Status");
		$activeSheet->setCellValue('I' . $line, "Date of Request Approved");
		$activeSheet->setCellValue('J' . $line, "Date of Placement");
		$activeSheet->setCellValue('K' . $line, "Contract Date");
		$activeSheet->setCellValue('L' . $line, "TAT");
		$activeSheet->setCellValue('L' . ($line + 1), "Target");
		$activeSheet->setCellValue('M' . ($line + 1), "Actual");
		$activeSheet->setCellValue('N' . $line, "Source");

		$objPHPExcel->getActiveSheet()->mergeCells('E'.$line.':'.'G'.$line);
		$objPHPExcel->getActiveSheet()->mergeCells('L'.$line.':'.'M'.$line);
		$objPHPExcel->getActiveSheet()->getStyle('E' . $line .':' . 'G' . $line)->applyFromArray($HorizontalCenter);
		$objPHPExcel->getActiveSheet()->getStyle('L' . $line .':' . 'M' . $line)->applyFromArray($HorizontalCenter);

		$result = $this->get_query($year,$month,0,2);

		$org_line = $line;
		$line += 2;
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$nr1 = '';
				$nr2 = '';
				$nr3 = '';
				switch ($row->reason_for_request) {
					case 1:
						$nr1 = 'Yes';
						break;
					case 2:
						$nr2 = 'Yes';
						break;
					case 3:
						$nr3 = 'Yes';
						break;												
				}
				$target = ($row->target > 1 ? $row->target . ' days' : $row->target . ' day' );
				$actual = ($row->actual > 1 ? $row->actual . ' days' : $row->actual . ' day' );

				$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $row->division .'/'. $row->department);
				$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $row->employment_status);
				$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, $row->position);
				$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, $row->document_number);
				$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, $nr1);
				$objPHPExcel->getActiveSheet()->setCellValue('F' . $line, $nr2);
				$objPHPExcel->getActiveSheet()->setCellValue('G' . $line, $nr3);
				$objPHPExcel->getActiveSheet()->setCellValue('H' . $line, $row->name);
				$objPHPExcel->getActiveSheet()->setCellValue('I' . $line, $row->date_request_approved);
				$objPHPExcel->getActiveSheet()->setCellValue('J' . $line, $row->date_placement);
				$objPHPExcel->getActiveSheet()->setCellValue('K' . $line, $row->contract_date);
				$objPHPExcel->getActiveSheet()->setCellValue('L' . $line, $target);
				$objPHPExcel->getActiveSheet()->setCellValue('M' . $line, $actual);
				$objPHPExcel->getActiveSheet()->setCellValue('N' . $line, $row->contacted_thru);
	            $line++;
			}
		}

		$objPHPExcel->getActiveSheet()->mergeCells('A'.$org_line.':'.'A'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('B'.$org_line.':'.'B'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('C'.$org_line.':'.'C'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('D'.$org_line.':'.'D'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('H'.$org_line.':'.'H'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('I'.$org_line.':'.'I'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('J'.$org_line.':'.'J'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('K'.$org_line.':'.'K'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('N'.$org_line.':'.'N'.($org_line + 1));

		$objPHPExcel->getActiveSheet()->getStyle('A'.$org_line.':'.'A'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('B'.$org_line.':'.'B'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('C'.$org_line.':'.'C'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('D'.$org_line.':'.'D'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('H'.$org_line.':'.'H'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('I'.$org_line.':'.'I'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('J'.$org_line.':'.'J'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('K'.$org_line.':'.'K'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('N'.$org_line.':'.'N'.($org_line + 1))->applyFromArray($vertical_center);

		$objPHPExcel->getActiveSheet()->getStyle('A' . $org_line . ':' . 'N' . ($line - 1))->applyFromArray($styleArrayBorder);
		// end Sup officer		

		//IT officer	 
    	$result_total = $this->get_query_total($year,$month,1,2);
    	$percentage = 100;
    	$position_required = 0;
    	$position_hired = 0;
    	$position_pending = 0;
    	$compliance_level = 0;
    	if ($result_total && $result_total->num_rows() > 0){
    		$row = $result_total->row();
	    	$position_required = ($row->total_position_required == '' ? 0 : $row->total_position_required);
	    	$position_hired = ($row->total_position_hired == '' ? 0 : $row->total_position_hired);
	    	$position_pending = ($row->total_position_hired_beyond_tat_and_pending == '' ? 0 : $row->total_position_hired);
			$diff = (($row->total_position_required - $row->total_position_hired) / $row->total_position_required) * 100;
			if ($diff > 0){
				$compliance_level = $percentage - $diff;
			}	    	
    	}

    	$line++;
		$line_t = $line;

		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "IT Officer/Technical positions required (60 days)");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "No. of positions hired within expected TAT");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "No. of positions hired/pending (beyond expected TAT)");
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $line++, "Compliance level");

		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_required);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_hired);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, $position_pending);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line_t++, ceil($compliance_level) . '%');

		$objPHPExcel->getActiveSheet()->getStyle('B' . ($line_t - 1))->applyFromArray($HorizontalRight);

		$line++;
		$activeSheet->setCellValue('A' . $line, "Division/Department");
		$activeSheet->setCellValue('B' . $line, "Employment Status");
		$activeSheet->setCellValue('C' . $line, "Position");
		$activeSheet->setCellValue('D' . $line, "MRF No.");
		$activeSheet->setCellValue('E' . $line, "Nature of Request");
		$activeSheet->setCellValue('E' . ($line + 1), "Existing Job");		
		$activeSheet->setCellValue('F' . ($line + 1), "Additional Head Count/Existing Job");				
		$activeSheet->setCellValue('G' . ($line + 1), "Additional Head Count/Newly Created Job");
		$activeSheet->setCellValue('H' . $line, "Status");
		$activeSheet->setCellValue('I' . $line, "Date of Request Approved");
		$activeSheet->setCellValue('J' . $line, "Date of Placement");
		$activeSheet->setCellValue('K' . $line, "Contract Date");
		$activeSheet->setCellValue('L' . $line, "TAT");
		$activeSheet->setCellValue('L' . ($line + 1), "Target");
		$activeSheet->setCellValue('M' . ($line + 1), "Actual");
		$activeSheet->setCellValue('N' . $line, "Source");

		$objPHPExcel->getActiveSheet()->mergeCells('E'.$line.':'.'G'.$line);
		$objPHPExcel->getActiveSheet()->mergeCells('L'.$line.':'.'M'.$line);
		$objPHPExcel->getActiveSheet()->getStyle('E' . $line .':' . 'G' . $line)->applyFromArray($HorizontalCenter);
		$objPHPExcel->getActiveSheet()->getStyle('L' . $line .':' . 'M' . $line)->applyFromArray($HorizontalCenter);

		$result = $this->get_query($year,$month,1,2);

		$org_line = $line;
		$line += 2;
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$nr1 = '';
				$nr2 = '';
				$nr3 = '';
				switch ($row->reason_for_request) {
					case 1:
						$nr1 = 'Yes';
						break;
					case 2:
						$nr2 = 'Yes';
						break;
					case 3:
						$nr3 = 'Yes';
						break;												
				}
				$target = ($row->target > 1 ? $row->target . ' days' : $row->target . ' day' );
				$actual = ($row->actual > 1 ? $row->actual . ' days' : $row->actual . ' day' );

				$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $row->division .'/'. $row->department);
				$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $row->employment_status);
				$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, $row->position);
				$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, $row->document_number);
				$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, $nr1);
				$objPHPExcel->getActiveSheet()->setCellValue('F' . $line, $nr2);
				$objPHPExcel->getActiveSheet()->setCellValue('G' . $line, $nr3);
				$objPHPExcel->getActiveSheet()->setCellValue('H' . $line, $row->name);
				$objPHPExcel->getActiveSheet()->setCellValue('I' . $line, $row->date_request_approved);
				$objPHPExcel->getActiveSheet()->setCellValue('J' . $line, $row->date_placement);
				$objPHPExcel->getActiveSheet()->setCellValue('K' . $line, $row->contract_date);
				$objPHPExcel->getActiveSheet()->setCellValue('L' . $line, $target);
				$objPHPExcel->getActiveSheet()->setCellValue('M' . $line, $actual);
				$objPHPExcel->getActiveSheet()->setCellValue('N' . $line, $row->contacted_thru);
	            $line++;
			}
		}

		$objPHPExcel->getActiveSheet()->mergeCells('A'.$org_line.':'.'A'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('B'.$org_line.':'.'B'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('C'.$org_line.':'.'C'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('D'.$org_line.':'.'D'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('H'.$org_line.':'.'H'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('I'.$org_line.':'.'I'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('J'.$org_line.':'.'J'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('K'.$org_line.':'.'K'.($org_line + 1));
		$objPHPExcel->getActiveSheet()->mergeCells('N'.$org_line.':'.'N'.($org_line + 1));

		$objPHPExcel->getActiveSheet()->getStyle('A'.$org_line.':'.'A'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('B'.$org_line.':'.'B'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('C'.$org_line.':'.'C'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('D'.$org_line.':'.'D'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('H'.$org_line.':'.'H'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('I'.$org_line.':'.'I'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('J'.$org_line.':'.'J'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('K'.$org_line.':'.'K'.($org_line + 1))->applyFromArray($vertical_center);
		$objPHPExcel->getActiveSheet()->getStyle('N'.$org_line.':'.'N'.($org_line + 1))->applyFromArray($vertical_center);

		$objPHPExcel->getActiveSheet()->getStyle('A' . $org_line . ':' . 'N' . ($line - 1))->applyFromArray($styleArrayBorder);
		// end IT officer	

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title("Yearly Compliance Monitoring Report") . '.xls');
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

	function get_query($year = 2013,$month = 1,$tn = 0,$employee_type = 3){
		$sql = "SELECT request_id, mrf_id,department,division,employment_status,position,document_number,reason_for_request,CONCAT(firstname, ' ',lastname) name,
		   			approved_date AS date_request_approved,hired_date AS date_placement,pre_employment_date AS contract_date,
		   			date_needed - approved_date AS target,hired_date - approved_date AS actual,contacted_thru
					FROM {$this->db->dbprefix}recruitment_manpower AS rm  
					LEFT JOIN {$this->db->dbprefix}user_company_department AS department
						ON rm.department_id = department.department_id
					LEFT JOIN {$this->db->dbprefix}user_company_division AS division
						ON department.division_id = division.division_id	
					LEFT JOIN {$this->db->dbprefix}employment_status AS es
						ON rm.status_id = es.employment_status_id														
					LEFT JOIN {$this->db->dbprefix}recruitment_manpower_candidate AS rmc
						ON (rm.request_id = rmc.mrf_id AND hired_date <= date_needed AND candidate_status_id = 6)
					LEFT JOIN {$this->db->dbprefix}recruitment_applicant AS ra 
						ON rmc.applicant_id = ra.applicant_id 										
					LEFT JOIN {$this->db->dbprefix}user_position AS up 
						ON ra.position_id = up.position_id 																				
					LEFT JOIN {$this->db->dbprefix}user_rank AS ur 
						ON rm.job_rank_id = ur.job_rank_id
					LEFT JOIN {$this->db->dbprefix}employee_type AS et 
						ON ur.employee_type = et.employee_type_id										 																														
					WHERE rm.deleted = 0
				    AND YEAR(rm.approved_date) = $year
				    AND MONTH(rm.approved_date) = $month						
				    AND (up.technical_non_technical = ".$tn." OR up.technical_non_technical IS NULL)
				    AND et.employee_type_id = ".$employee_type."";		
		
		$result = $this->db->query($sql);		
		return $result;
	}

	function get_query_total($year = 2013,$month = 1,$tn = 0,$employee_type = 3){
		$sql = "SELECT SUM(position_required) AS total_position_required,
					   SUM(position_hired) AS total_position_hired,
					   SUM(position_hired_beyond_tat_and_pending) AS total_position_hired_beyond_tat_and_pending 
					   FROM (
					   		SELECT request_id, mrf_id,number_required AS position_required,
					   			COUNT(candidate_status_id) AS position_hired,
					   			(number_required - COUNT(candidate_status_id)) AS position_hired_beyond_tat_and_pending
								FROM {$this->db->dbprefix}recruitment_manpower AS rm  
								LEFT JOIN {$this->db->dbprefix}recruitment_manpower_candidate AS rmc
									ON (rm.request_id = rmc.mrf_id AND hired_date <= date_needed AND candidate_status_id = 6)
								LEFT JOIN {$this->db->dbprefix}recruitment_applicant AS ra 
									ON rmc.applicant_id = ra.applicant_id 										
								LEFT JOIN {$this->db->dbprefix}user_position AS up 
									ON ra.position_id = up.position_id 																				
								LEFT JOIN {$this->db->dbprefix}user_rank AS ur 
									ON rm.job_rank_id = ur.job_rank_id
								LEFT JOIN {$this->db->dbprefix}employee_type AS et 
									ON ur.employee_type = et.employee_type_id										 																														
								WHERE rm.deleted = 0
							    AND YEAR(rm.approved_date) = $year
							    AND MONTH(rm.approved_date) = $month						
							    AND (up.technical_non_technical = ".$tn." OR up.technical_non_technical IS NULL)
							    AND et.employee_type_id = ".$employee_type."
								GROUP BY request_id) AS trm";		
		
		$result = $this->db->query($sql);		

		return $result;
	}	
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>