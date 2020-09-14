<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Compliance_monitoring_report extends MY_Controller
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
		$data['content'] = 'recruitment/compliance_monitoring_report_listview';
		
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
        $months = array(
	    	1 => 'January',
	    	2 => 'February',
	    	3 => 'March',
	    	4 => 'April',
	    	5 => 'May',
	    	6 => 'June',
	    	7 => 'July',
	    	8 => 'August',
	    	9 => 'September',
	    	10 => 'October',
	    	11 => 'November',
	    	12 => 'December'
    	);

    	$year = $this->input->post('year');
    	$target_employees = array();
    	$array_value = array();
		
		//non officer    	
        foreach($months as $key => $month):
        	$result = $this->get_query($year,$key,0,3);
			if($result && $result->num_rows() > 0){
				$row = $result->row();
				$array_value[$month]['postion_required'] = $row->total_position_required;
				$array_value[$month]['postion_hired'] = $row->total_position_hired;
				$array_value[$month]['postion_hired_pending_beyond_tat'] = $row->total_position_hired_beyond_tat_and_pending;
			}	
		endforeach;

        $response->rows[0]['cell'][0] = "Non Officer/Non Technical positions required (R&F 30 days)";
        $response->rows[1]['cell'][0] = "No. of positions hired within expected TAT";
        $response->rows[2]['cell'][0] = "No. of positions hired/pending (beyond expected TAT)";
        $response->rows[3]['cell'][0] = "Compliance level";

        $cel = 1;
        $sum_no_officer = 0;
        $sum_no_ph = 0;
        $sum_no_pending = 0;
        foreach ($array_value as $key => $value) {
        	$percentage = 100;
        	$diff_percentage = 0;
			$response->rows[0]['cell'][$cel] =  ($value['postion_required'] == '' ? 0 : $value['postion_required']);
			$response->rows[1]['cell'][$cel] =  ($value['postion_hired'] == '' ? 0 : $value['postion_hired']);
			$response->rows[2]['cell'][$cel] =  ($value['postion_hired_pending_beyond_tat'] == '' ? 0 : $value['postion_hired_pending_beyond_tat']);
			$diff = (($value['postion_required'] - $value['postion_hired']) / $value['postion_required']) * 100;
			if ($diff > 0){
				$diff_percentage = $percentage - $diff;
			}
			$response->rows[3]['cell'][$cel] =  ceil($diff_percentage) . '%';
			$cel++;
	        $sum_no_officer += $value['postion_required'];
	        $sum_no_ph += $value['postion_hired'];
	        $sum_no_pending += $value['postion_hired_pending_beyond_tat'];
        }

/*        $response->rows[4]['id'] = 'wborder';*/
        $response->rows[4]['cell'][0] = "Total of R and F Requirement:";
        $response->rows[5]['cell'][0] = "Hired:";
        $response->rows[6]['cell'][0] = "Beyong TAT";
        $response->rows[7]['cell'][0] = "Percentage:";

        $diff = $sum_no_pending / $sum_no_officer * 100;
		if ($diff > 0){
			$diff_percentage = $percentage - $diff;
		}

        $response->rows[4]['cell'][1] = $sum_no_officer;
        $response->rows[5]['cell'][1] = $sum_no_ph;
        $response->rows[6]['cell'][1] = $sum_no_pending;        
        $response->rows[7]['cell'][1] = ceil($diff_percentage) . '%';

        for ($i=0; $i < 13; $i++) { 
        	$response->rows[8]['cell'][$i] = "";
        }
        // end of non officer

		//sup officer    	
        foreach($months as $key => $month):
        	$result = $this->get_query($year,$key,0,2);
			if($result && $result->num_rows() > 0){
				$row = $result->row();
				$array_value[$month]['postion_required'] = $row->total_position_required;
				$array_value[$month]['postion_hired'] = $row->total_position_hired;
				$array_value[$month]['postion_hired_pending_beyond_tat'] = $row->total_position_hired_beyond_tat_and_pending;
			}	
		endforeach;

        $response->rows[9]['cell'][0] = "Sup-Officer/Non Technical positions required (45 days)";
        $response->rows[10]['cell'][0] = "No. of positions hired within expected TAT";
        $response->rows[11]['cell'][0] = "No. of positions hired/pending (beyond expected TAT)";
        $response->rows[12]['cell'][0] = "Compliance level";

        $cel = 1;
        $sum_no_officer = 0;
        $sum_no_ph = 0;
        $sum_no_pending = 0;
        foreach ($array_value as $key => $value) {
        	$percentage = 100;
        	$diff_percentage = 0;
			$response->rows[9]['cell'][$cel] =  ($value['postion_required'] == '' ? 0 : $value['postion_required']);
			$response->rows[10]['cell'][$cel] =  ($value['postion_hired'] == '' ? 0 : $value['postion_hired']);
			$response->rows[11]['cell'][$cel] =  ($value['postion_hired_pending_beyond_tat'] == '' ? 0 : $value['postion_hired_pending_beyond_tat']);
			$diff = (($value['postion_required'] - $value['postion_hired']) / $value['postion_required']) * 100;
			if ($diff > 0){
				$diff_percentage = $percentage - $diff;
			}
			$response->rows[12]['cell'][$cel] =  ceil($diff_percentage) . '%';
			$cel++;
	        $sum_no_officer += $value['postion_required'];
	        $sum_no_ph += $value['postion_hired'];
	        $sum_no_pending += $value['postion_hired_pending_beyond_tat'];
        }

        $response->rows[13]['cell'][0] = "Total of Sup/Officer Requirement:";
        $response->rows[14]['cell'][0] = "Hired:";
        $response->rows[15]['cell'][0] = "Beyong TAT";
        $response->rows[16]['cell'][0] = "Percentage:";

        $diff = $sum_no_pending / $sum_no_officer * 100;
		if ($diff > 0){
			$diff_percentage = $percentage - $diff;
		}

        $response->rows[13]['cell'][1] = $sum_no_officer;
        $response->rows[14]['cell'][1] = $sum_no_ph;
        $response->rows[15]['cell'][1] = $sum_no_pending;        
        $response->rows[16]['cell'][1] = ceil($diff_percentage) . '%';

        for ($i=0; $i < 13; $i++) { 
        	$response->rows[17]['cell'][$i] = "";
        }
        // end of sup officer

		//IT officer    	
        foreach($months as $key => $month):
        	$result = $this->get_query($year,$key,1,2);
			if($result && $result->num_rows() > 0){
				$row = $result->row();
				$array_value[$month]['postion_required'] = $row->total_position_required;
				$array_value[$month]['postion_hired'] = $row->total_position_hired;
				$array_value[$month]['postion_hired_pending_beyond_tat'] = $row->total_position_hired_beyond_tat_and_pending;
			}	
		endforeach;

        $response->rows[18]['cell'][0] = "IT-Officer/Technical positions required (60 days)";
        $response->rows[19]['cell'][0] = "No. of positions hired within expected TAT";
        $response->rows[20]['cell'][0] = "No. of positions hired/pending (beyond expected TAT)";
        $response->rows[21]['cell'][0] = "Compliance level";

        $cel = 1;
        $sum_no_officer = 0;
        $sum_no_ph = 0;
        $sum_no_pending = 0;
        foreach ($array_value as $key => $value) {
        	$percentage = 100;
        	$diff_percentage = 0;
			$response->rows[18]['cell'][$cel] =  ($value['postion_required'] == '' ? 0 : $value['postion_required']);
			$response->rows[19]['cell'][$cel] =  ($value['postion_hired'] == '' ? 0 : $value['postion_hired']);
			$response->rows[20]['cell'][$cel] =  ($value['postion_hired_pending_beyond_tat'] == '' ? 0 : $value['postion_hired_pending_beyond_tat']);
			$diff = (($value['postion_required'] - $value['postion_hired']) / $value['postion_required']) * 100;
			if ($diff > 0){
				$diff_percentage = $percentage - $diff;
			}
			$response->rows[21]['cell'][$cel] =  ceil($diff_percentage) . '%';
			$cel++;
	        $sum_no_officer += $value['postion_required'];
	        $sum_no_ph += $value['postion_hired'];
	        $sum_no_pending += $value['postion_hired_pending_beyond_tat'];
        }

        $response->rows[22]['cell'][0] = "Total of IT/Officer Technical Requirement:";
        $response->rows[23]['cell'][0] = "Hired:";
        $response->rows[24]['cell'][0] = "Beyong TAT";
        $response->rows[25]['cell'][0] = "Percentage:";

        $diff = $sum_no_pending / $sum_no_officer * 100;
		if ($diff > 0){
			$diff_percentage = $percentage - $diff;
		}

        $response->rows[22]['cell'][1] = $sum_no_officer;
        $response->rows[23]['cell'][1] = $sum_no_ph;
        $response->rows[24]['cell'][1] = $sum_no_pending;        
        $response->rows[25]['cell'][1] = ceil($diff_percentage) . '%';
        // end of IT officer        

		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{		
        $months = array(
	    	1 => 'January',
	    	2 => 'February',
	    	3 => 'March',
	    	4 => 'April',
	    	5 => 'May',
	    	6 => 'June',
	    	7 => 'July',
	    	8 => 'August',
	    	9 => 'September',
	    	10 => 'October',
	    	11 => 'November',
	    	12 => 'December'
    	);

    	$year = $this->input->post('date_year');
    	$target_employees = array();
    	$array_value = array();
		
		//non officer    	
        foreach($months as $key => $month):
        	$result = $this->get_query($year,$key,0,3);
			if($result && $result->num_rows() > 0){
				$row = $result->row();
				$array_value[$month]['postion_required'] = $row->total_position_required;
				$array_value[$month]['postion_hired'] = $row->total_position_hired;
				$array_value[$month]['postion_hired_pending_beyond_tat'] = $row->total_position_hired_beyond_tat_and_pending;
			}	
		endforeach;

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
		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);					

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

 		foreach ($array_value as $key => $value) {			
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			$activeSheet->setCellValue($xcoor . '6', $key);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', 'Pioneer Insurance');
		$activeSheet->setCellValue('A2', $this->input->post('date_year') . ' Yearly Compliance Monitoring Report');
		$activeSheet->setCellValue('A3', 'As of ' . date('M d, Y'));

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;

		//non officer	        
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Non Officer/Non Technical positions required (R&F 30 days)");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "No. of positions hired within expected TAT");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "No. of positions hired/pending (beyond expected TAT)");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Compliance level");

        $sum_no_officer = 0;
        $sum_no_ph = 0;
        $sum_no_pending = 0;
		$sub_ctr   = 0;			
		$alpha_ctr = 1;

        foreach ($array_value as $key => $value) {
			$line = 7;        	
			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

        	$percentage = 100;
        	$diff_percentage = 0;
			$diff = (($value['postion_required'] - $value['postion_hired']) / $value['postion_required']) * 100;
			if ($diff > 0){
				$diff_percentage = $percentage - $diff;
			}        	
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_required'] == '' ? 0 : $value['postion_required']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_hired'] == '' ? 0 : $value['postion_hired']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_hired_pending_beyond_tat'] == '' ? 0 : $value['postion_hired_pending_beyond_tat']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ceil($diff_percentage) . '%');
	        $sum_no_officer += $value['postion_required'];
	        $sum_no_ph += $value['postion_hired'];
	        $sum_no_pending += $value['postion_hired_pending_beyond_tat'];
	        $alpha_ctr++;
        }

		$objPHPExcel->getActiveSheet()->getStyle('B10:M'.$line)->applyFromArray($HorizontalRight);
		$objPHPExcel->getActiveSheet()->getStyle('B10:M'.$line)->applyFromArray($fontBold);

        $tline = $line++;
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Total of R and F Requirement:");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Hired:");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Beyong TAT");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Percentage:");

        $diff = $sum_no_pending / $sum_no_officer * 100;
		if ($diff > 0){
			$diff_percentage = $percentage - $diff;
		}

		$tline++;
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_officer);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_ph);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_pending);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, ceil($diff_percentage) . '%');

        $objPHPExcel->getActiveSheet()->getStyle('B15:M'.$line)->applyFromArray($HorizontalRight);
        $objPHPExcel->getActiveSheet()->getStyle('A12:B15')->applyFromArray($styleArrayBorder);
        $objPHPExcel->getActiveSheet()->getStyle('B12:B15')->applyFromArray($fontBold);
        //End non officer

		//Sup officer	
		$tline = $line++;		
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Sup-Officer/Non Technical positions required (45 days)");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "No. of positions hired within expected TAT");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "No. of positions hired/pending (beyond expected TAT)");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Compliance level");

        $sum_no_officer = 0;
        $sum_no_ph = 0;
        $sum_no_pending = 0;
		$sub_ctr   = 0;			
		$alpha_ctr = 1;

        foreach($months as $key => $month):
        	$result = $this->get_query($year,$key,0,2);
			if($result && $result->num_rows() > 0){
				$row = $result->row();
				$array_value[$month]['postion_required'] = $row->total_position_required;
				$array_value[$month]['postion_hired'] = $row->total_position_hired;
				$array_value[$month]['postion_hired_pending_beyond_tat'] = $row->total_position_hired_beyond_tat_and_pending;
			}	
		endforeach;

        foreach ($array_value as $key => $value) {      	
			$line = $tline;
			$line++;        	
			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

        	$percentage = 100;
        	$diff_percentage = 0;
			$diff = (($value['postion_required'] - $value['postion_hired']) / $value['postion_required']) * 100;
			if ($diff > 0){
				$diff_percentage = $percentage - $diff;
			}        	
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_required'] == '' ? 0 : $value['postion_required']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_hired'] == '' ? 0 : $value['postion_hired']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_hired_pending_beyond_tat'] == '' ? 0 : $value['postion_hired_pending_beyond_tat']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ceil($diff_percentage) . '%');
	        $sum_no_officer += $value['postion_required'];
	        $sum_no_ph += $value['postion_hired'];
	        $sum_no_pending += $value['postion_hired_pending_beyond_tat'];
	        $alpha_ctr++;
        }

		$objPHPExcel->getActiveSheet()->getStyle('B20:M'.$line)->applyFromArray($HorizontalRight);
		$objPHPExcel->getActiveSheet()->getStyle('B20:M'.$line)->applyFromArray($fontBold);

        $tline = $line++;
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Total of Sup/ Officer Requirement:");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Hired:");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Beyong TAT");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Percentage:");

        $diff = $sum_no_pending / $sum_no_officer * 100;
		if ($diff > 0){
			$diff_percentage = $percentage - $diff;
		}

		$tline++;
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_officer);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_ph);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_pending);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, ceil($diff_percentage) . '%');

        $objPHPExcel->getActiveSheet()->getStyle('B25:M'.$line)->applyFromArray($HorizontalRight);
        $objPHPExcel->getActiveSheet()->getStyle('A22:B25')->applyFromArray($styleArrayBorder);
        $objPHPExcel->getActiveSheet()->getStyle('B22:B25')->applyFromArray($fontBold);
        //End Sup officer

		//IT officer	
		$tline = $line++;		
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "IT Officer/Technical positions required (60 days)");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "No. of positions hired within expected TAT");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "No. of positions hired/pending (beyond expected TAT)");
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Compliance level");

        $sum_no_officer = 0;
        $sum_no_ph = 0;
        $sum_no_pending = 0;
		$sub_ctr   = 0;			
		$alpha_ctr = 1;

        foreach($months as $key => $month):
        	$result = $this->get_query($year,$key,1,2);
			if($result && $result->num_rows() > 0){
				$row = $result->row();
				$array_value[$month]['postion_required'] = $row->total_position_required;
				$array_value[$month]['postion_hired'] = $row->total_position_hired;
				$array_value[$month]['postion_hired_pending_beyond_tat'] = $row->total_position_hired_beyond_tat_and_pending;
			}	
		endforeach;

        foreach ($array_value as $key => $value) {      	
			$line = $tline;
			$line++;        	
			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

        	$percentage = 100;
        	$diff_percentage = 0;
			$diff = (($value['postion_required'] - $value['postion_hired']) / $value['postion_required']) * 100;
			if ($diff > 0){
				$diff_percentage = $percentage - $diff;
			}        	
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_required'] == '' ? 0 : $value['postion_required']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_hired'] == '' ? 0 : $value['postion_hired']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ($value['postion_hired_pending_beyond_tat'] == '' ? 0 : $value['postion_hired_pending_beyond_tat']));
        	$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line++, ceil($diff_percentage) . '%');
	        $sum_no_officer += $value['postion_required'];
	        $sum_no_ph += $value['postion_hired'];
	        $sum_no_pending += $value['postion_hired_pending_beyond_tat'];
	        $alpha_ctr++;
        }

		$objPHPExcel->getActiveSheet()->getStyle('B30:M'.$line)->applyFromArray($HorizontalRight);
		$objPHPExcel->getActiveSheet()->getStyle('B30:M'.$line)->applyFromArray($fontBold);

        $tline = $line++;
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Total of IT/ Technical Requirement:");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Hired:");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Beyong TAT");
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $line++, "Percentage:");

        $diff = $sum_no_pending / $sum_no_officer * 100;
		if ($diff > 0){
			$diff_percentage = $percentage - $diff;
		}

		$tline++;
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_officer);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_ph);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, $sum_no_pending);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $tline++, ceil($diff_percentage) . '%');

        $objPHPExcel->getActiveSheet()->getStyle('B25:M'.$line)->applyFromArray($HorizontalRight);
        $objPHPExcel->getActiveSheet()->getStyle('A32:B35')->applyFromArray($styleArrayBorder);
        $objPHPExcel->getActiveSheet()->getStyle('B32:B35')->applyFromArray($fontBold);
        //End IT officer

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

	function get_query($year,$month,$tn = 0,$employee_type = 3){
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