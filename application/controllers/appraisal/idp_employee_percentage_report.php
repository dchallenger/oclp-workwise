<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class idp_employee_percentage_report extends MY_Controller
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

		$data['content'] = 'employees/appraisal/reports/listview_idp';

		$data['jqgrid'] = 'employees/appraisal/reports/jqgrid_idp';

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

		if($this->input->post('year') != '' && $this->input->post('year') != 'null'){
			$where_year = " AND idp.year = ".$this->input->post('year')." ";
		}

		$individual_development_plan_qry = $this->db->query("SELECT
				DISTINCT idp.employee_id
			FROM hr_individual_development_plan idp
			LEFT JOIN hr_employee e ON idp.employee_id = e.employee_id
			LEFT JOIN hr_user u ON e.employee_id = u.employee_id
			LEFT JOIN hr_user_company_department ucd ON idp.department_id = ucd.department_id
			LEFT JOIN hr_user_company_division ucdi ON idp.division_id = ucdi.division_id
			LEFT JOIN hr_user_company uc ON idp.company_id = uc.company_id
			LEFT JOIN hr_user_position up ON idp.position_id = up.position_id
			LEFT JOIN hr_user_rank ur ON idp.rank_id = ur.job_rank_id
			LEFT JOIN hr_position_classification pc ON e.position_class_id = pc.position_class_id
			WHERE 1 ".$where_year);
		
		$employees = false;	
		$in_planning = false;
		if ($individual_development_plan_qry && $individual_development_plan_qry->num_rows() > 0) {
			$individual_development_plan_result_employee_id = array();

			foreach ($individual_development_plan_qry->result() as $individual_development_plan_row) {
			 	$individual_development_plan_result_employee_id[] = $individual_development_plan_row->employee_id;
			}

			$employees = $individual_development_plan_result_employee_id;
			$in_planning = $individual_development_plan_result_employee_id;
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

	        $completed_count = 0;
            $on_process_count = 0;
            $open_count = 0;
	        $total_count = 0;

	        foreach ($employees as $employee_id) {
	        	$employee_qry = $this->db->query("SELECT
						idp.employee_id,
						u.lastname, u.firstname, u.middleinitial, u.aux,
						up.position,
						ur.job_rank,
						pc.position_class,
						ucd.department,
						ucdi.division,
						IF(idp.idp_status = 'Approved' OR idp.idp_status = 'Cancelled' OR idp.idp_status = 'Decline',
							1, 0) AS completed,
						IF(idp.idp_status = 'For Approval' OR idp.idp_status = 'HR Review',
							1, 0) AS on_process,
						IF(idp.idp_status = 'Draft',
							1, 0) AS open
					FROM hr_individual_development_plan idp
					LEFT JOIN hr_employee e ON idp.employee_id = e.employee_id
					LEFT JOIN hr_user u ON e.employee_id = u.employee_id
					LEFT JOIN hr_user_company_department ucd ON idp.department_id = ucd.department_id
					LEFT JOIN hr_user_company_division ucdi ON idp.division_id = ucdi.division_id
					LEFT JOIN hr_user_company uc ON idp.company_id = uc.company_id
					LEFT JOIN hr_user_position up ON idp.position_id = up.position_id
					LEFT JOIN hr_user_rank ur ON idp.rank_id = ur.job_rank_id
					LEFT JOIN hr_position_classification pc ON e.position_class_id = pc.position_class_id
					WHERE idp.employee_id = ".$employee_id." ".$where_year);

	        	if($employee_qry && $employee_qry->num_rows() > 0){
					$employee = $employee_qry->row();
				
		        	$employee_name = $employee->lastname. ', '. $employee->firstname;
		            
		            $response->rows[$ctr]['cell'][0] = $employee_name;
		            $response->rows[$ctr]['cell'][1] = $employee->position;
		            $response->rows[$ctr]['cell'][2] = $employee->job_rank;
		            $response->rows[$ctr]['cell'][3] = $employee->position_class;
		            $response->rows[$ctr]['cell'][4] = $employee->department;
		            $response->rows[$ctr]['cell'][5] = $employee->division;
		            $response->rows[$ctr]['cell'][6] = $employee->completed;
		            $response->rows[$ctr]['cell'][7] = $employee->on_process;
		            $response->rows[$ctr]['cell'][8] = $employee->open;

		            $completed_count += $employee->completed == '1' ? 1 : 0;
		            $on_process_count += $employee->on_process == '1' ? 1 : 0;
		            $open_count += $employee->open == '1' ? 1 : 0;
		            $total_count++;
		            
		            $ctr++;
				}

	            $response->rows[$ctr]['cell'][6] = $completed_count / $total_count * 100;
	            $response->rows[$ctr]['cell'][7] = $on_process_count / $total_count * 100;
	            $response->rows[$ctr]['cell'][8] = $open_count / $total_count * 100;
	        }
	    }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Name', 'Position', 'Level','Classification','Department','Division','Completed', 'On Processing', 'Open');

		$this->listview_columns = array(
				array('name' => 'employee_name'),				
				array('name' => 'position'),
				array('name' => 'level'),
				array('name' => 'classification'),
				array('name' => 'department_id'),
				array('name' => 'division_id'),
				array('name' => 'completed'),
				array('name' => 'on_process'),
				array('name' => 'open')
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
		$company_ids = $this->input->post('company_id');
		$division_ids = $this->input->post('division_id');
		$department_ids = $this->input->post('department_id');
		$employee_ids = $this->input->post('employee_id');

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

        $objPHPExcel->getProperties()->setTitle("Individual Development Plan Employee Percentage")
                    ->setDescription("Individual Development Plan Employee Percentage");
                       
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
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A1',  'Individual Development Plan Employee Percentage', PHPExcel_Cell_DataType::TYPE_STRING);

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
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('G3', 'Status');

        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A4', 'Name', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B4', 'Position');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('C4', 'Level');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('D4', 'Classification');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('E4', 'Department', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('F4', 'Division', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('G4', 'Completed', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('H4', 'On-Process', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('I4', 'Open', PHPExcel_Cell_DataType::TYPE_STRING);

        if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$where .= " AND idp.company_id IN (".implode(',', $this->input->post('company_id')).")";
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$where .= " AND idp.division_id IN (".implode(',', $this->input->post('division_id')).")";
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$where .= " AND idp.department_id IN (".implode(',', $this->input->post('department_id')).")";
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$where .= " AND idp.employee_id IN (".implode(',', $this->input->post('employee_id')).")";
		}

		if($this->input->post('year') != '' && $this->input->post('year') != 'null'){
			$where_year .= " AND idp.year = ".$this->input->post('year')." ";
		}

        $line = 6;
    	$individual_development_plan_qry = $this->db->query("SELECT
				u.lastname, u.firstname, u.middleinitial, u.aux,
				up.position,
				ur.job_rank,
				pc.position_class,
				ucd.department,
				ucdi.division,
				IF(idp.idp_status = 'Approved' OR idp.idp_status = 'Cancelled' OR idp.idp_status = 'Decline',
					1, 0) AS completed,
				IF(idp.idp_status = 'For Approval' OR idp.idp_status = 'HR Review',
					1, 0) AS on_process,
				IF(idp.idp_status = 'Draft',
					1, 0) AS open
			FROM hr_individual_development_plan idp
			LEFT JOIN hr_employee e ON idp.employee_id = e.employee_id
			LEFT JOIN hr_user u ON e.employee_id = u.employee_id
			LEFT JOIN hr_user_company_department ucd ON idp.department_id = ucd.department_id
			LEFT JOIN hr_user_company_division ucdi ON idp.division_id = ucdi.division_id
			LEFT JOIN hr_user_company uc ON idp.company_id = uc.company_id
			LEFT JOIN hr_user_position up ON idp.position_id = up.position_id
			LEFT JOIN hr_user_rank ur ON idp.rank_id = ur.job_rank_id
			LEFT JOIN hr_position_classification pc ON e.position_class_id = pc.position_class_id
			WHERE 1 ".$where." ".$where_year."
			ORDER BY idp.company_id, idp.department_id, idp.division_id");

        if($individual_development_plan_qry && $individual_development_plan_qry->num_rows() > 0){
    		foreach ($individual_development_plan_qry->result() as $individual_development_plan_row) {
    			$employee_name = $individual_development_plan_row->lastname.', '.$individual_development_plan_row->firstname.' '.$individual_development_plan_row->middleinitial.' '.$individual_development_plan_row->aux;

    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$line, $employee_name, PHPExcel_Cell_DataType::TYPE_STRING);
    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$line, $individual_development_plan_row->position, PHPExcel_Cell_DataType::TYPE_STRING);
    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$line, $individual_development_plan_row->job_rank, PHPExcel_Cell_DataType::TYPE_STRING);
    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$line, $individual_development_plan_row->position_class, PHPExcel_Cell_DataType::TYPE_STRING);
    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$line, $individual_development_plan_row->department, PHPExcel_Cell_DataType::TYPE_STRING);
    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$line, $individual_development_plan_row->division, PHPExcel_Cell_DataType::TYPE_STRING);
    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$line, $individual_development_plan_row->completed, PHPExcel_Cell_DataType::TYPE_STRING);
    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$line, $individual_development_plan_row->on_process, PHPExcel_Cell_DataType::TYPE_STRING);
    			$objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$line, $individual_development_plan_row->open, PHPExcel_Cell_DataType::TYPE_STRING);

	            $completed_count += $individual_development_plan_row->completed == '1' ? 1 : 0;
	            $on_process_count += $individual_development_plan_row->on_process == '1' ? 1 : 0;
	            $open_count += $individual_development_plan_row->open == '1' ? 1 : 0;
	            $total_count++;

    			$line++;
    		}
    	}

		$objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$line, number_format($completed_count / $total_count * 100, 2).'%', PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$line, number_format($on_process_count / $total_count * 100, 2).'%', PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$line, number_format($open_count / $total_count * 100, 2).'%', PHPExcel_Cell_DataType::TYPE_STRING);

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename=Individual_Development_Plan_Employee_Percentage_'.date('Y-m-d').'.xls');
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
		$periods = $this->db->query('SELECT DISTINCT year FROM hr_individual_development_plan WHERE deleted = 0 ORDER BY year');

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