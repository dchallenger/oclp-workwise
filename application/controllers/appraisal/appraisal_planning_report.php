<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal_planning_report extends MY_Controller
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

		$data['content'] = 'employees/appraisal/reports/listview';

		$data['jqgrid'] = 'employees/appraisal/reports/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$where = array('deleted' => 0);
		$data['departments'] = $this->db->get_where('user_company_department', $where)->result_array();
		$data['companies'] = $this->db->get_where('user_company', $where)->result_array();

		$data['divisions'] = $this->db->get_where('user_company_division', $where)->result_array();

		$data['periods'] = $this->get_period();
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
		

		$dbprefix = $this->db->dbprefix;
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		$search = 1;	

		$year = 0;
		if ($this->input->post('planning_period_id')) {
			$year = $this->input->post('planning_period_id');	
		}
		
		$this->db->where('planning_period_id', $year);
		$this->db->where('deleted', 0);
		$period_qry = $this->db->get('appraisal_planning_period');
		
		$employees = false;	
		$in_planning = false;
		if ($period_qry && $period_qry->num_rows() > 0) {
			$period_result = $period_qry->row();
	
			$employees = explode(',', $period_result->employee_id);
			$in_planning = explode(',', $period_result->employee_id);
		}

		if($this->input->post('employee_id') != "" &&  $this->input->post('employee_id') != "null"){
			$employees = $this->input->post('employee_id');
		}	

		if (is_array($employees)) {
			
			$employee_qry = "SELECT *
								FROM {$dbprefix}user a 
								WHERE a.deleted = 0 AND a.employee_id IN (". implode(',', $employees). ")";
			
			if($this->input->post('company_id') != "" &&  $this->input->post('company_id') != "null" ){
				$company_id = implode(',', $this->input->post('company_id'));
				$employee_qry .= " AND a.company_id IN (" .$company_id. ")";
			}

			if($this->input->post('division_id') != "" && $this->input->post('division_id') != "null"){
				$division_id = implode(',', $this->input->post('division_id'));
				$employee_qry .= " AND a.division_id IN (" .$division_id. ")";
			}

			if($this->input->post('department_id') != "" && $this->input->post('department_id') != "null"){
				$department_id = implode(',', $this->input->post('department_id'));
				$employee_qry .= " AND a.department_id IN (" .$department_id. ")";
			}
			
			$employee_result = $this->db->query($employee_qry);
			if ($employee_result && $employee_result->num_rows() > 0) {
				$employees = array();
				foreach ($employee_result->result() as $key => $value) {
					$employees[] = $value->employee_id;
				}

				$employees = array_intersect($employees, $in_planning);
			}else{
				$employees = false;
			}
			
		}

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        

	        $total_pages = is_array($employees) ? ceil(count($employees)/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = count($employees);                        

	        $response->msg = "";

	        $ctr = 0;	        
	        
	        foreach ($employees as $employee_id) {
	        	$employee = $this->system->get_employee($employee_id);

	        	$employee_name 			 = $employee['firstname']. ' '. $employee['lastname'];
	        	$employee_position 		 = $employee['position'];
	        	$employee_rank 			 = $employee['job_rank'];
	        	$employee_position_class = $employee['position_class'];
	        	$employee_department	 = $employee['department'];
	        	$employee_division	 	 = $employee['division'];
	            $status 				 = 'Pending';
	            // $pending 				 = 1;
	            // $for_approval			 = '';

	            $planning_qry = "SELECT a.status as planning_status, b.firstname, b.lastname, c.position, f.job_rank, e.position_class, d.department, g.division
									FROM {$dbprefix}employee_appraisal_planning a
									JOIN {$dbprefix}user b ON a.employee_id = b.employee_id 
									JOIN {$dbprefix}user_position c  ON c.position_id = a.position_id
									JOIN {$dbprefix}user_company_department d ON d.department_id = a.department_id
									JOIN {$dbprefix}position_classification e ON e.position_class_id = a.position_class_id
									JOIN {$dbprefix}user_rank f ON f.job_rank_id = a.rank_id
									JOIN {$dbprefix}user_company_division g ON g.division_id = a.division_id
								WHERE a.deleted = 0 AND a.employee_id = ".$employee_id. 
									" AND a.appraisal_planning_period_id = ". $year;
							
				$has_planning = $this->db->query($planning_qry);				
				if ($has_planning && $has_planning->num_rows() > 0) {
					$employee = $has_planning->row_array();
					
					$employee_name 			 = $employee['firstname']. ' '. $employee['lastname'];
		        	$employee_position 		 = $employee['position'];
		        	$employee_rank 			 = $employee['job_rank'];
		        	$employee_position_class = $employee['position_class'];
		        	$employee_department	 = $employee['department'];
		        	$employee_division	 	 = $employee['division'];
		        	
		        	$status = $this->db->get_where('employee_appraisal_planning_status', array('appraisal_planning_status_id' => $employee['planning_status']))->row()->appraisal_planning_status;
		            // $status				 	= $employee['planning_status'] = 3 ? 1 : '';
		            // $pending 				 = '';
		            // $for_approval			 = ($employee['planning_status'] = 2) || ($employee['planning_status'] = 4)  ? 1 : '';
		            
				}
	            
	            $response->rows[$ctr]['cell'][0] = $employee_name;
	            $response->rows[$ctr]['cell'][1] = $employee_position;
	            $response->rows[$ctr]['cell'][2] = $employee_rank;
	            $response->rows[$ctr]['cell'][3] = $employee_position_class;
	            $response->rows[$ctr]['cell'][4] = $employee_department;
	            $response->rows[$ctr]['cell'][5] = $employee_division;
	            $response->rows[$ctr]['cell'][6] = $status;
	            // $response->rows[$ctr]['cell'][7] = $for_approval;
	            // $response->rows[$ctr]['cell'][8] = $pending;
	            
	            $ctr++;
	        }
	    }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Name', 'Position', 'Level','Classification','Department','Division', 'Status');

		$this->listview_columns = array(
				array('name' => 'employee_name'),				
				array('name' => 'position'),
				array('name' => 'level'),
				array('name' => 'classification'),
				array('name' => 'department_id'),
				array('name' => 'division_id'),
				array('name' => 'status')
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

		$this->_excel_export();
	}

	// export called using ajax
	function _excel_export()
	{	
		$dbprefix = $this->db->dbprefix;
		$year = 0;
		if ($this->input->post('planning_period_id')) {
			$year = $this->input->post('planning_period_id');	
		}
		
		$this->db->where('planning_period_id', $year);
		$this->db->where('deleted', 0);
		$period_qry = $this->db->get('appraisal_planning_period');
		
		$employees = false;	
		$in_planning = false;
		if ($period_qry && $period_qry->num_rows() > 0) {
			$period_result = $period_qry->row();
	
			$employees = explode(',', $period_result->employee_id);
			$in_planning = explode(',', $period_result->employee_id);
		}

		if($this->input->post('employee_id') != "" &&  $this->input->post('employee_id') != "null"){
			$employees = $this->input->post('employee_id');
		}	

		if (is_array($employees)) {
			
			$employee_qry = "SELECT *
								FROM {$dbprefix}user a 
								WHERE a.deleted = 0 AND a.employee_id IN (". implode(',', $employees). ")";
			
			if($this->input->post('company_id') != "" &&  $this->input->post('company_id') != "null" ){
				$company_id = implode(',', $this->input->post('company_id'));
				$employee_qry .= " AND a.company_id IN (" .$company_id. ")";
			}

			if($this->input->post('division_id') != "" && $this->input->post('division_id') != "null"){
				$division_id = implode(',', $this->input->post('division_id'));
				$employee_qry .= " AND a.division_id IN (" .$division_id. ")";
			}

			if($this->input->post('department_id') != "" && $this->input->post('department_id') != "null"){
				$department_id = implode(',', $this->input->post('department_id'));
				$employee_qry .= " AND a.department_id IN (" .$department_id. ")";
			}
			
			$employee_result = $this->db->query($employee_qry);

			if ($employee_result && $employee_result->num_rows() > 0) {
				$employees = array();
				foreach ($employee_result->result() as $key => $value) {
					$employees[] = $value->employee_id;
				}

				$employees = array_intersect($employees, $in_planning);
			}else{
				$employees = false;
			}
			
		}
		

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Performance Appraisal Summary")
		            ->setDescription("Performance Appraisal Summary");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;


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

		$fields = array('Name', 'Position', 'Level','Classification','Department','Division', 'Status');

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

		$activeSheet->setCellValue('A1', '');
		$activeSheet->setCellValue('A2', 'Performance Appraisal Planning Summary');

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		// $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		//$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($leftstyleArray);
		
		// contents.
		$line = 7;
		$fields = array('Name', 'Position', 'Level','Classification','Department','Division', 'Status');
		
			
		foreach ($employees as $employee_id) {
			$employee = $this->system->get_employee($employee_id);

        	$employee_name 			 = $employee['firstname']. ' '. $employee['lastname'];
        	$employee_position 		 = $employee['position'];
        	$employee_rank 			 = $employee['job_rank'];
        	$employee_position_class = $employee['position_class'];
        	$employee_department	 = $employee['department'];
        	$employee_division	 	 = $employee['division'];
            $status 				 = 'Pending';

            $planning_qry = "SELECT a.status as planning_status, b.firstname, b.lastname, c.position, f.job_rank, e.position_class, d.department, g.division
								FROM {$dbprefix}employee_appraisal_planning a
								JOIN {$dbprefix}user b ON a.employee_id = b.employee_id 
								JOIN {$dbprefix}user_position c  ON c.position_id = a.position_id
								JOIN {$dbprefix}user_company_department d ON d.department_id = a.department_id
								JOIN {$dbprefix}position_classification e ON e.position_class_id = a.position_class_id
								JOIN {$dbprefix}user_rank f ON f.job_rank_id = a.rank_id
								JOIN {$dbprefix}user_company_division g ON g.division_id = a.division_id
							WHERE a.deleted = 0 AND a.employee_id = ".$employee_id. 
								" AND a.appraisal_planning_period_id = ". $year;
						
			$has_planning = $this->db->query($planning_qry);				
			if ($has_planning && $has_planning->num_rows() > 0) {
				$employee = $has_planning->row_array();
				
				$employee_name 			 = $employee['firstname']. ' '. $employee['lastname'];
	        	$employee_position 		 = $employee['position'];
	        	$employee_rank 			 = $employee['job_rank'];
	        	$employee_position_class = $employee['position_class'];
	        	$employee_department	 = $employee['department'];
	        	$employee_division	 	 = $employee['division'];
	        	
	        	$status = $this->db->get_where('employee_appraisal_planning_status', array('appraisal_planning_status_id' => $employee['planning_status']))->row()->appraisal_planning_status;
			}

		
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $employee_name);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $employee_position);		
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, $employee_rank);		
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, $employee_position_class);		
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, $employee_department);		
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $line, $employee_division);		
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $line, $status);

			$line++;
			
		}

		$objPHPExcel->getActiveSheet()->getStyle('A6:'.$xcoor.($line))->applyFromArray($styleArrayBorder);

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title("Performance Appraisal Summary") . '.xls');
		header('Content-Transfer-Encoding: binary');

		// $path = 'uploads/performance_appraisal_summary/'.url_title("Performance Appraisal Summary").'-'.date('Y-m-d').'.xls';
		
		// $objWriter->save($path);
		$objWriter->save('php://output');	

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));
	}	

	function get_employees()
	{

		if (IS_AJAX)
		{
			$department_id 	 = $this->input->post('department_id');
			$division_id	 = $this->input->post('division_id');
			$company_id 	 = $this->input->post('company_id');

			$options = '';
			if ($company_id && $company_id != 'null') {
				$company = 'company_id IN ('.$company_id.')';	
				$this->db->where($company);
			}

			if ($department_id && $department_id != 'null') {
				$this->db->where_in('department_id IN ('.$department_id.')');
			}

			if ($division_id && $division_id != 'null') {
				$this->db->where_in('user.division_id IN ('.$division_id.')');
			}

			$this->db->where('inactive', 0);
			$this->db->where('user.deleted', 0);
			$this->db->join('employee', 'employee.employee_id = user.employee_id');
			$this->db->order_by('firstname,lastname', 'ASC');
			$result = $this->db->get('user');
			
			
			// $this->db->where($this->key_field, $record_id);
			// $record = $this->db->get($this->module_table);
			$response['employees'] = '';
			// if ($record && $record->num_rows() > 0) {
			// 	$rec = $record->row();
			// 	$employees = $rec->employee_id;
			// 	$response['employees'] = explode(',', $employees);
			// }

			if ($result->num_rows() > 0) {
				$employee = $result->result();
				
				foreach ($employee as $emp) {
					$options .= '<option value="'.$emp->employee_id.'">'.$emp->firstname." ".$emp->middleinitial." ".$emp->lastname. " ".$emp->aux.'</option>';
				}

				$response['result'] = $options;
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

		
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


	function get_period()
	{
		$this->db->where('deleted', 0);
		$periods = $this->db->get('appraisal_planning_period');

		$pms_periods = false;

		if ($periods && $periods->num_rows() > 0) {
			$pms_periods = $periods->result();
		}
		
		return $pms_periods;
	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>