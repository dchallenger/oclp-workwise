<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fill_rate extends MY_Controller
{
	function __construct()
    {
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
	function index()
    { 	
    	$data['scripts'][] = chosen_script();
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'recruitment/analytics/hit_rate_listview';

		$data['jqgrid'] = 'recruitment/analytics/jqgrid';//'employees/appraisal/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$data['departments'] = $this->db->get_where('user_company_department', array('deleted' => 0))->result_array();
		$data['companies'] = $this->db->get_where('user_company', array('deleted' => 0))->result_array();
		$data['divisions'] = $this->db->get_where('user_company_division', array('deleted' => 0))->result_array();
		$data['positions'] = $this->db->get_where('user_position', array('deleted' => 0))->result_array();

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$data['department'] = $this->db->get('user_company_department')->result_array();

		if($this->user_access[$this->module_id]['post'] != 1) {
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');			
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}

		$data['w_subordinates'] = $subordinates;

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
		$this->load->helper('time_upload');

        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		$search = 1;			
		$dbprefix = $this->db->dbprefix;
		$company_id = $this->input->post('company');

		$qry = "SELECT c.applicant_name, c.candidate_id, c.jo_accepted_date, up.position as position_for, r.job_rank , d.department, di.division
			FROM {$dbprefix}recruitment_manpower_candidate c 
			LEFT JOIN {$dbprefix}recruitment_manpower m ON c.mrf_id = m.request_id
			LEFT JOIN {$dbprefix}user_position up  ON up.position_id = m.position_id
			LEFT JOIN {$dbprefix}user_company_department d  ON d.department_id = m.department_id
			LEFT JOIN {$dbprefix}user_company_division di  ON di.division_id = m.division_id
			LEFT JOIN {$dbprefix}user_rank r  ON r.job_rank_id = m.job_rank_id
			WHERE m.deleted = 0 AND c.deleted = 0  ";

		if($company_id && $company_id != 'null') {
          $qry .= " AND m.company_id IN (".implode(',', $company_id).")";
        }
        if($this->input->post('division')) {
            $division_id = $this->input->post('division'); // implode(',', $this->input->post('division')); -- AND a.screening_datetime != '0000-00-00 00:00:00'
            $qry .= " AND m.division_id IN (".$division_id.")";
        }			
      
        if ($this->input->post('department')){
        	$department_id = $this->input->post('department');
			$qry .= " AND m.department_id IN (".$department_id.")";
		}

		if ($this->input->post('position')){
        	$position_id = $this->input->post('position');
			$qry .= " AND m.position_id IN (".$position_id.")";
		}

		if ($this->input->post('year')){
			$qry .= " AND YEAR(m.requested_date) = ".$this->input->post('year')."";			
		}

        $result = $this->db->query($qry);   
     	// $response->query = $this->db->last_query();
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

			$qry = "SELECT c.applicant_name, c.candidate_id, c.date_schedule, c.jo_accepted_date, up.position, r.job_rank , d.department, di.division, m.*
				FROM {$dbprefix}recruitment_manpower_candidate c 
				LEFT JOIN {$dbprefix}recruitment_manpower m ON c.mrf_id = m.request_id
				LEFT JOIN {$dbprefix}user_position up  ON up.position_id = m.position_id
				LEFT JOIN {$dbprefix}user_company_department d  ON d.department_id = m.department_id
				LEFT JOIN {$dbprefix}user_company_division di  ON di.division_id = m.division_id
				LEFT JOIN {$dbprefix}user_rank r  ON r.job_rank_id = m.job_rank_id
				WHERE m.deleted = 0 AND c.deleted = 0 ";

			if($company_id && $company_id != 'null') {
	          $qry .= " AND m.company_id IN (".implode(',', $company_id).")";
	        }
	        if($this->input->post('division')) {
	            $division_id = $this->input->post('division'); // implode(',', $this->input->post('division'));
	            $qry .= " AND m.division_id IN (".$division_id.")";
	        }			
	      
	        if ($this->input->post('department')){
	        	$department_id = $this->input->post('department');
				$qry .= " AND m.department_id IN (".$department_id.")";
			}

			if ($this->input->post('position')){
	        	$position_id = $this->input->post('position');
				$qry .= " AND m.position_id IN (".$position_id.")";
			}

			if ($this->input->post('year')){
				$qry .= " AND YEAR(m.requested_date) = ".$this->input->post('year')."";			
			}

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        

			$result = $this->db->query($qry);	

			$ctr = 0;	
	       		   	   
	        foreach ($result->result() as $row) {

	        	$exam_details = json_decode($row->exam_details, true);
	        	$interview_details = json_decode($row->interview_details, true);
	        	
		        $response->rows[$ctr]['cell'][0] = $ctr+1;
		        $response->rows[$ctr]['cell'][1] = $row->applicant_name;
		        $response->rows[$ctr]['cell'][2] = $row->position;
		        $response->rows[$ctr]['cell'][3] = $row->job_rank;
		        $response->rows[$ctr]['cell'][4] = $row->department;
		        $response->rows[$ctr]['cell'][5] = $row->division;
		        $response->rows[$ctr]['cell'][6] = (!is_null($row->requested_date) && $row->requested_date != '0000-00-00') ? date('F d, Y', strtotime($row->date_needed)) : '' ;

		        $response->rows[$ctr]['cell'][7] = (!is_null($row->date_needed) && $row->date_needed != '0000-00-00') ? date('F d, Y', strtotime($row->date_needed)) : '' ;

		        $response->rows[$ctr]['cell'][8] = (!is_null($row->contract_received) && $row->contract_received != '0000-00-00') ? date('F d, Y', strtotime($row->contract_received)) : '' ;

		         
		        $response->rows[$ctr]['cell'][9] = (!is_null($row->date_from) && $row->date_from != '0000-00-00') ? date('F d, Y', strtotime($row->date_from)) : '' ;
		        $response->rows[$ctr]['cell'][10] = (!is_null($row->date_to) && $row->date_to != '0000-00-00') ? date('F d, Y', strtotime($row->date_to)) : '' ;
		        $response->rows[$ctr]['cell'][11] = (!is_null($row->date_schedule) && $row->date_schedule != '0000-00-00 00:00:00') ? date('F d, Y', strtotime($row->date_schedule)) : '' ;
		        $response->rows[$ctr]['cell'][12] = (!is_null($row->jo_accepted_date) && $row->jo_accepted_date != '0000-00-00') ? date('F d, Y', strtotime($row->jo_accepted_date)) : '' ;

				// dbug($row->date_from);
				// $date_from = () ? date('F d, Y', strtotime($row->date_from)) : '' ;
				
				if (!is_null($row->date_from) && $row->date_from != '0000-00-00') {
					$start_fill_rate = strtotime($row->date_from);
					$jo_accepted_date = strtotime($row->jo_accepted_date);

					if ($jo_accepted_date >= $start_fill_rate) {
						$days_passed = ($jo_accepted_date - $start_fill_rate) / (60 * 60 * 24);
					}else{
						$days_passed = 0;
					}
					$weekends = $this->get_weekends($row->date_from, $days_passed);

				}else{
					$days_passed = 0;
					$weekends = 0;
				}
				

	

				$tat_computed = $days_passed - $weekends;
				// $tat_computed = ($row->turn_around_time - $days_passed) + $weekends;

		        $response->rows[$ctr]['cell'][13] = $tat_computed. ' day(s)';
		        // $response->rows[$ctr]['cell'][11] = date('F d, Y', strtotime($row->end_fill_rate));
				$ctr++;	

	        }

	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
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

    function _set_listview_query($listview_id = '', $view_actions = true) {
   		$this->listview_column_names = array("NO.", "NAME", "POSITION", "LEVEL", "DEPARTMENT", "DIVISION", "DATE OF MRF", "DATE NEEDED", "DATE OF MRF RECEIPT", "START FILL RATE", "END FILL RATE", "ACTUAL DATE OF APPLICATION", "JOB OFFER SIGNED DATE", "FILL RATE");

		$this->listview_columns = array(
				array('name' => 'no','width' => '50px'),				
				array('name' => 'name'),				
				array('name' => 'position'),				
				array('name' => 'level','align' => 'left'),
				array('name' => 'department','align' => 'left'),
				array('name' => 'division','align' => 'left'),
				array('name' => 'date_mrf'),
				array('name' => 'date_needed'),
				array('name' => 'date_receipt'),
				array('name' => 'start_fill_rate'),
				array('name' => 'end_fill_rate'),
				array('name' => 'application'),
				array('name' => 'jo_date'),
				array('name' => 'fill_rate')
			);                                     
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
		$search_string[] = $this->db->dbprefix .'user.firstname LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function export() {	

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Fill Rate Report")
		            ->setDescription("Fill Rate Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;
		$dbprefix = $this->db->dbprefix;
		//Default column width
		for ($col = 'A'; $col != 'J'; $col++) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		}		

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$leftstyleArray = array(
			'font' => array(
				'italic' => true,
			),
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

		$fields = array("NO.", "NAME", "POSITION", "LEVEL", "DEPARTMENT", "DIVISION", "DATE OF MRF", "DATE NEEDED", "DATE OF MRF RECEIPT",  "START FILL RATE", "END FILL RATE", "ACTUAL DATE OF APPLICATION", "JOB OFFER SIGNED DATE", "FILL RATE", "REMARKS");

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

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');
		
		$activeSheet->setCellValue('A1', 'OCLP');
		$activeSheet->setCellValue('A2', 'Fill Rate Report');

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		$company_id = $this->input->post('company');
		
		$qry = "SELECT c.applicant_name, c.candidate_id, c.jo_accepted_date, c.date_schedule, up.position, r.job_rank , d.department, di.division, m.*
				FROM {$dbprefix}recruitment_manpower_candidate c 
				LEFT JOIN {$dbprefix}recruitment_manpower m ON c.mrf_id = m.request_id
				LEFT JOIN {$dbprefix}user_position up  ON up.position_id = m.position_id
				LEFT JOIN {$dbprefix}user_company_department d  ON d.department_id = m.department_id
				LEFT JOIN {$dbprefix}user_company_division di  ON di.division_id = m.division_id
				LEFT JOIN {$dbprefix}user_rank r  ON r.job_rank_id = m.job_rank_id
				WHERE m.deleted = 0 AND c.deleted = 0 ";

			if($company_id && $company_id != 'null') {
	          $qry .= " AND m.company_id IN (".implode(',', $company_id).")";
	        }
	        if($this->input->post('division')) {
	            $division_id = $this->input->post('division'); // implode(',', $this->input->post('division'));
	            $qry .= " AND m.division_id IN (".$division_id.")";
	        }			
	      
	        if ($this->input->post('department')){
	        	$department_id = $this->input->post('department');
				$qry .= " AND m.department_id IN (".$department_id.")";
			}

			if ($this->input->post('position')){
	        	$position_id = $this->input->post('position');
				$qry .= " AND m.position_id IN (".$position_id.")";
			}

			if ($this->input->post('year')){
				$qry .= " AND YEAR(m.requested_date) = ".$this->input->post('year')."";			
			}

			$results = $this->db->query($qry);	


		$fields2 = array("no", "applicant_name", "position", "job_rank", "department", "division", "requested_date", "date_needed", "contract_received", "date_from", "date_to", "date_schedule", "jo_accepted_date", "turn_around_time", "remarks");


		foreach ($results->result() as $key => $result) {
			$sub_ctr   = 0;			
			$alpha_ctr = 0;
			foreach ($fields2 as $field) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}
				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $result->{$field});
				
				if ($field == 'requested_date' || $field == 'contract_received' || $field == 'date_from' || $field == 'date_to' || $field == 'date_schedule' || $field == 'date_needed' ) {
					if (!is_null($result->{$field}) && ($result->{$field} != '0000-00-00 00:00:00')) {
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, date('F d, Y', strtotime($result->{$field})));
					}else{
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '');
					}
				}
				if ($field == 'jo_accepted_date') {
					# code...;
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (!is_null($result->jo_accepted_date)) ? date('F d, Y', strtotime($result->jo_accepted_date)) : '' );
				}

				if ($field == 'turn_around_time') {
					$start_fill_rate = strtotime($result->date_from);
					$jo_accepted_date = strtotime($result->jo_accepted_date);

					if ($jo_accepted_date >= $start_fill_rate) {
						$days_passed = ($jo_accepted_date - $start_fill_rate) / (60 * 60 * 24);
					}else{
						$days_passed = 0;
					}

					$weekends = $this->get_weekends($result->date_from, $days_passed);

					// $tat_computed = ($result->turn_around_time - $days_passed) + $weekends;
					$tat_computed = $days_passed - $weekends;

					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $tat_computed. ' day(s)');

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
		header('Content-Disposition: attachment;filename=' . url_title("Fill-Rate-Report") .  date('Y-m-d') .'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	
	}

	// END custom module funtions
	
	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		 $buttons = '';
                
		return $buttons;
	}

	function get_weekends($start, $tat, $ajax = false)
	{	
		if ($this->input->post('is_ajax')) $ajax = true;
		
		if ($ajax) {			
			$start = date ("Y-m-d", strtotime($this->input->post('start_date'))); 
			$tat = $this->input->post('tat');
		}

		$count = 0;
		$flag = 1;
		
		$weekends = array();

		while ($flag <= $tat) 
		{
			$timestamp = strtotime($start);  
			$day = date('D', $timestamp); 

			$holiday = $this->system->holiday_check($start);

			if($day=='Sat' || $day=='Sun' || $holiday)
			{   
				$count++ ; 

			}
			else{

				if ($ajax) $flag++;
			}

			$start = date ("Y-m-d", strtotime("+1 day", strtotime($start)));
			
			if (!$ajax) {
				$flag++;
			}
						
		}

		$end_date = date('m/d/Y', strtotime($start));

		if ($ajax) {
			$response->end_date = $end_date;
			$this->load->view('template/ajax', array('json' => $response));			
		}else{
			return $count;
		}

	}
}

/* End of file */
/* Location: system/application */
?>