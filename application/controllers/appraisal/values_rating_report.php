<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class values_rating_report extends MY_Controller
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

	function listview()	{
		$dbprefix = $this->db->dbprefix;
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		$search = 1;
		$company_ids = 1;
		$division_ids = 1;
		$department_ids = 1;
		$employee_ids = 1;

		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$company_ids = implode(',', $this->input->post('company_id'));
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$division_ids = implode(',', $this->input->post('division_id'));
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$department_ids = implode(',', $this->input->post('department_id'));
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_ids = implode(',', $this->input->post('employee_id'));
		}

		$planning_period_id = $this->input->post('planning_period_id');
		
		$this->db->where('planning_period_id', $planning_period_id);
		$this->db->where('deleted', 0);
		$period_qry = $this->db->get('appraisal_planning_period');
		
		$employees = false;	
		$in_planning = false;
		if ($period_qry && $period_qry->num_rows() > 0) {
			$period_result = $period_qry->row();
			$employees = explode(',', $period_result->employee_id);
			$in_planning = explode(',', $period_result->employee_id);
		}

		if($this->input->post('employee_id') != " " &&  $this->input->post('employee_id') != "null"){
			$employees = $this->input->post('employee_id');
		}	

		if (is_array($employees)) {
			
			$employee_query = "SELECT *
								FROM {$dbprefix}user a 
								WHERE a.deleted = 0 AND a.employee_id IN (". implode(',', $employees). ")";
			
			if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
				$employee_query .= " AND a.company_id IN (" .$company_ids. ")";	
			}

			if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
				$employee_query .= " AND a.division_id IN (" .$division_ids. ")";
			}

			if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
				$employee_query .= " AND a.department_id IN (" .$department_ids. ")";
			}

			$employee_query .= " ORDER BY a.company_id, a.department_id, a.division_id ";
			
			$employee_result = $this->db->query($employee_query);
			if ($employee_result && $employee_result->num_rows() > 0) {
				$employees = array();
				foreach ($employee_result->result() as $key => $value) {
					$employees[] = $value->employee_id;
				}

				// $employees = array_intersect($employees, $in_planning);
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

			$inno_count = 0;
	        $rela_count = 0;
	        $exce_count = 0;
	        $total_count = 0;     
	        
	        foreach ($employees as $employee_id) {
	        	$employee_qry = $this->db->query("SELECT 
						u.lastname, u.firstname, u.middleinitial,
						up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division
					FROM hr_employee e
					LEFT JOIN hr_user u ON e.employee_id = u.employee_id
					LEFT JOIN hr_user_position up ON up.position_id = u.position_id 
					LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id 
					LEFT JOIN hr_position_classification pc ON pc.position_class_id = e.position_class_id 
					LEFT JOIN hr_user_company_department ucd ON u.department_id = ucd.department_id 
					LEFT JOIN hr_user_company_division ucdi ON u.division_id = ucdi.division_id 
					WHERE e.employee_id = ".$employee_id."");

	        	if($employee_qry && $employee_qry->num_rows() > 0){
					$employee = $employee_qry->row_array();
				
		        	$employee_name 			 = $employee['firstname']. ' '. $employee['lastname'];
		        	$employee_position 		 = $employee['position'];
		        	$employee_rank 			 = $employee['job_rank'];
		        	$employee_position_class = $employee['position_class'];
		        	$employee_department	 = $employee['department'];
		        	$employee_division	 	 = $employee['division'];

					$employee_appraisal_bsc_qry = $this->db->query("SELECT 
									up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division,
									IF(eab.employee_appraisal_status = 5, 1, 0) AS completed,
									eab.employee_appraisal_criteria_rating_weight_array,
									eab.employee_appraisal_criteria_rating_array,
									eab.employee_appraisal_or_raters_comments,
									eab.employee_appraisal_criteria_question_sec_rating_array
								FROM hr_employee_appraisal_bsc eab 
								LEFT JOIN hr_user_position up ON up.position_id = eab.position_id 
								LEFT JOIN hr_user_rank ur ON ur.job_rank_id = eab.rank_id 
								LEFT JOIN hr_position_classification pc ON pc.position_class_id = eab.position_class_id 
								LEFT JOIN hr_user_company_department ucd ON eab.department_id = ucd.department_id 
								LEFT JOIN hr_user_company_division ucdi ON eab.division_id = ucdi.division_id 
								LEFT JOIN hr_employee_appraisal_status eas ON eab.employee_appraisal_status = eas.appraisal_status_id 
								WHERE eab.employee_id = ".$employee_id."
									AND eab.appraisal_period_id = ".$planning_period_id);
					
					$rows = array();
		    		$ctr_cell = 1;
					
					if ($employee_appraisal_bsc_qry && $employee_appraisal_bsc_qry->num_rows() > 0) {
						$employee_appraisal_bsc_row = $employee_appraisal_bsc_qry->row_array();

			        	$employee_position 		 = $employee_appraisal_bsc_row['position'];
			        	$employee_rank 			 = $employee_appraisal_bsc_row['job_rank'];
			        	$employee_position_class = $employee_appraisal_bsc_row['position_class'];
			        	$employee_department	 = $employee_appraisal_bsc_row['department'];
			        	$employee_division	 	 = $employee_appraisal_bsc_row['division'];
		    			

		    			// CORE
		    			$individual_development_plan_core = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_rating_array']);
						foreach ($individual_development_plan_core as $employee_appraisal_criteria_id => $employee_appraisal_criteria_array) {
							$employee_appraisal_criteria_row = $this->db->query("SELECT * FROM hr_employee_appraisal_criteria WHERE employee_appraisal_criteria_id = ".$employee_appraisal_criteria_id)->row();
							if($employee_appraisal_criteria_row->is_core == 1){
								foreach ($employee_appraisal_criteria_array as $appraiser_id => $appraiser_array) {
									foreach ($appraiser_array as $competency_value_id => $competency_value_array) {
										$appraisal_competency_value_row = $this->db->query("SELECT * FROM hr_appraisal_competency_value WHERE competency_value_id = ".$competency_value_id)->row();
										if($employee_appraisal_criteria_id == 6){
											$rows[$ctr_cell] = $competency_value_array['coach_rating'];
		            						$ctr_cell++;
										}
									}
								}
							}
						}
					}

		            $average = ($rows[1] + $rows[2] + $rows[3]);

		            $response->rows[$ctr]['cell'][0] = $employee_name;
		            $response->rows[$ctr]['cell'][1] = $employee_position;
		            $response->rows[$ctr]['cell'][2] = $employee_rank;
		            $response->rows[$ctr]['cell'][3] = $employee_position_class;
		            $response->rows[$ctr]['cell'][4] = $employee_department;
		            $response->rows[$ctr]['cell'][5] = $employee_division;
		            $response->rows[$ctr]['cell'][6] = $rows[3];
		            $response->rows[$ctr]['cell'][7] = $rows[1];
		            $response->rows[$ctr]['cell'][8] = $rows[2];
		            $response->rows[$ctr]['cell'][9] = number_format(($average / 3), 2);

		            $inno_count += $rows[1] > 0 ? $rows[1] : 0;
		            $rela_count += $rows[2] > 0 ? $rows[2] : 0;
		            $exce_count += $rows[3] > 0 ? $rows[3] : 0;
		            $total_count++;
		            
		            $ctr++;
				}
	        }

            $response->rows[$ctr]['cell'][6] = number_format($inno_count / $total_count, 2);
            $response->rows[$ctr]['cell'][7] = number_format($rela_count / $total_count, 2);
            $response->rows[$ctr]['cell'][8] = number_format($exce_count / $total_count, 2);

            $average = (number_format($inno_count / $total_count, 2) + number_format($rela_count / $total_count, 2) + number_format($exce_count / $total_count, 2)); 
            $response->rows[$ctr]['cell'][9] = number_format(($average / 3), 2);
            
            $ctr++;
	    }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Name', 'Position', 'Level','Classification','Department','Division', 'Relationships', 'Excellence', 'Innovation', 'Average');

		$this->listview_columns = array(
				array('name' => 'employee_name'),				
				array('name' => 'position'),
				array('name' => 'level'),
				array('name' => 'classification'),
				array('name' => 'department_id'),
				array('name' => 'division_id'),
				array('name' => 'relationships'),
				array('name' => 'excellence'),
				array('name' => 'innovation'),
				array('name' => 'average')
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
		$company_ids = implode(',', $this->input->post('company_id'));
		$division_ids = implode(',', $this->input->post('division_id'));
		$department_ids = implode(',', $this->input->post('department_id'));
		// $employee_ids = implode(',', $this->input->post('employee_id'));

        $this->load->library('PHPExcel');
        $this->load->library('PHPExcel/IOFactory');
        $this->load->library('encrypt');

        $objPHPExcel = new PHPExcel();
        
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            )
        );

        $styleArrayPageHeaderRight = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );

        $styleArrayHeader = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'borders' => array(
                'bottom' =>array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ),
            )
        );

        $styleArrayNumber = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );

        $objPHPExcel->getProperties()->setTitle("Values Rating")
                    ->setDescription("Values Rating");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
            
        $objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A1',  'Values Rating', PHPExcel_Cell_DataType::TYPE_STRING);

        $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:F3');
        $objPHPExcel->getActiveSheet()->mergeCells('G3:I3');
        $objPHPExcel->getActiveSheet()->getStyle('A3:F3')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('G3:I3')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('B4')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('D4')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('E4')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('F4')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('G4')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('H4')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('I4')->applyFromArray($styleArrayHeader);

        // column header.
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A3', 'Employee Iinformation', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('G3', 'Corporate Values');

        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A4', 'Name', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B4', 'Position');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('C4', 'Level');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('D4', 'Classification');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('E4', 'Department', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('F4', 'Division', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('G4', 'Relationships', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('H4', 'Excellence', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('I4', 'Innovation', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('J4', 'Average', PHPExcel_Cell_DataType::TYPE_STRING);

        $line = 6;
    	$this->db->where('planning_period_id', $_POST['planning_period_id']);
    	$this->db->where('deleted', 0);
    	$appraisal_planning_period_qry = $this->db->get('appraisal_planning_period');

		$inno_count = 0;
        $rela_count = 0;
        $exce_count = 0;
        $total_count = 0;

		if($appraisal_planning_period_qry && $appraisal_planning_period_qry->num_rows() > 0){
    		foreach ($appraisal_planning_period_qry->result() as $appraisal_planning_period_row) {
    			$employee_ids = explode(',',$appraisal_planning_period_row->employee_id);
					
				$employee_query = "SELECT *
									FROM hr_user a 
									WHERE a.deleted = 0 AND a.employee_id IN (". implode(',', $employee_ids). ")";
				
				if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
					$employee_query .= " AND a.company_id IN (" .$company_ids. ")";	
				}

				if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
					$employee_query .= " AND a.division_id IN (" .$division_ids. ")";
				}

				if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
					$employee_query .= " AND a.department_id IN (" .$department_ids. ")";
				}

				$employee_query .= " ORDER BY a.company_id, a.department_id, a.division_id ";
				
				$employee_result = $this->db->query($employee_query);
				if ($employee_result && $employee_result->num_rows() > 0) {
					$employee_ids = array();
					foreach ($employee_result->result() as $employee_row) {
						$employee_ids[] = $employee_row->employee_id;
					}
				}
					
    			foreach ($employee_ids as $employee_id) {
    				$employee_qry = $this->db->query("SELECT 
    						u.lastname, u.firstname, u.middleinitial,
    						up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division
						FROM hr_employee e
						LEFT JOIN hr_user u ON e.employee_id = u.employee_id
						LEFT JOIN hr_user_position up ON up.position_id = u.position_id 
						LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id 
						LEFT JOIN hr_position_classification pc ON pc.position_class_id = e.position_class_id 
						LEFT JOIN hr_user_company_department ucd ON u.department_id = ucd.department_id 
						LEFT JOIN hr_user_company_division ucdi ON u.division_id = ucdi.division_id 
						WHERE e.employee_id = ".$employee_id);

    				if($employee_qry && $employee_qry->num_rows() > 0){
	    				$employee = $employee_qry->row_array();
	    				$employee_nam = $employee['lastname'].', '.$employee['firstname'].' '.$employee['middleinitial'];

	    				$employee_appraisal_bsc_qry = $this->db->query("SELECT 
								up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division,
								IF(eab.employee_appraisal_status = 5, 1, 0) AS completed,
								eab.employee_appraisal_criteria_rating_weight_array,
								eab.employee_appraisal_criteria_rating_array,
								eab.employee_appraisal_or_raters_comments,
								eab.employee_appraisal_criteria_question_sec_rating_array
							FROM hr_employee_appraisal_bsc eab 
							LEFT JOIN hr_user_position up ON up.position_id = eab.position_id 
							LEFT JOIN hr_user_rank ur ON ur.job_rank_id = eab.rank_id 
							LEFT JOIN hr_position_classification pc ON pc.position_class_id = eab.position_class_id 
							LEFT JOIN hr_user_company_department ucd ON eab.department_id = ucd.department_id 
							LEFT JOIN hr_user_company_division ucdi ON eab.division_id = ucdi.division_id 
							LEFT JOIN hr_employee_appraisal_status eas ON eab.employee_appraisal_status = eas.appraisal_status_id 
							WHERE eab.employee_id = ".$employee_id."
								AND eab.appraisal_period_id = ".$appraisal_planning_period_row->planning_period_id);

						$employee_pos = $employee['position'];
						$employee_lvl = $employee['job_rank'];
						$employee_cls = $employee['position_class'] == '' ? '' : $employee['position_class'];
						$employee_dep = $employee['department'];
						$employee_div = $employee['division'];
		    			$rows = array();
		    			$ctr_cell = 1;
						
						if($employee_appraisal_bsc_qry && $employee_appraisal_bsc_qry->num_rows() > 0){
							$employee_appraisal_bsc_row = $employee_appraisal_bsc_qry->row_array();

							$employee_pos = $employee_appraisal_bsc_row['position'];
							$employee_lvl = $employee_appraisal_bsc_row['job_rank'];
							$employee_cls = $employee_appraisal_bsc_row['position_class'];
							$employee_dep = $employee_appraisal_bsc_row['department'];
							$employee_div = $employee_appraisal_bsc_row['division'];

			    			// CORE
			    			$individual_development_plan_core = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_rating_array']);
							foreach ($individual_development_plan_core as $employee_appraisal_criteria_id => $employee_appraisal_criteria_array) {
								$employee_appraisal_criteria_row = $this->db->query("SELECT * FROM hr_employee_appraisal_criteria WHERE employee_appraisal_criteria_id = ".$employee_appraisal_criteria_id)->row();
								if($employee_appraisal_criteria_row->is_core == 1){
									foreach ($employee_appraisal_criteria_array as $appraiser_id => $appraiser_array) {
										foreach ($appraiser_array as $competency_value_id => $competency_value_array) {
											$appraisal_competency_value_row = $this->db->query("SELECT * FROM hr_appraisal_competency_value WHERE competency_value_id = ".$competency_value_id)->row();
											if($employee_appraisal_criteria_id == 6){
												$rows[$ctr_cell] = $competency_value_array['coach_rating'];
			            						$ctr_cell++;
											}
										}
									}
								}
							}
						}
						$average = ($rows[1] + $rows[2] + $rows[3]);

						$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$line, $employee_nam, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$line, $employee_pos, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$line, $employee_lvl, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$line, $employee_cls, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$line, $employee_dep, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$line, $employee_div, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$line, $rows[3], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$line, $rows[1], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$line, $rows[2], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$line, number_format(($average / 3), 2), PHPExcel_Cell_DataType::TYPE_STRING);

			            $inno_count += $rows[1] > 0 ? $rows[1] : 0;
			            $rela_count += $rows[2] > 0 ? $rows[2] : 0;
			            $exce_count += $rows[3] > 0 ? $rows[3] : 0;
			            $total_count++; //$inno_count + $rela_count + $exce_count

						$line++;
					}
    			}
    			$average = (number_format($inno_count / $total_count, 2) + number_format($rela_count / $total_count, 2) + number_format($exce_count / $total_count, 2));

				$objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$line, number_format($inno_count / $total_count, 2), PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$line, number_format($rela_count / $total_count, 2), PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$line, number_format($exce_count / $total_count, 2), PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$line, number_format(($average / 3), 2), PHPExcel_Cell_DataType::TYPE_STRING);
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
        header('Content-Disposition: attachment;filename=Values_Rating_Report'.date('Y-m-d').'.xls');
        header('Content-Transfer-Encoding: binary');
    
        $objWriter->save('php://output');
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