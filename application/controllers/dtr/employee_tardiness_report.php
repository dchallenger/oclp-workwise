<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_tardiness_report extends MY_Controller
{
	function __construct(){
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
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['scripts'][] = multiselect_script();
		$data['content'] = 'dtr/tardiness_report/by_employee';
		
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

	function get_employee_time_record(){
		$html = '';
		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1: // company
		    	$this->db->where('deleted', 0);
				$company = $this->db->get('user_company')->result_array();		
                $html .= '<select id="user_company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;	
		    case 2: // division
				$this->db->where('deleted', 0);
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="user_company_division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;	
		    case 3: // department
		    	$this->db->where('deleted', 0);
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="user_company_department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;			        	        	        
		    case 4: // section
		    	$this->db->where('deleted', 0);
				$company = $this->db->get('user_section')->result_array();		
                $html .= '<select id="user_section" multiple="multiple" class="multi-select" style="width:400px;" name="section[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["section_id"].'">'.$company_record["section"].'</option>';
                    }
                $html .= '</select>';	
		        break;	 
		    case 5: // level
		    	$this->db->where('deleted', 0);
				$employee_type = $this->db->get('employee_type')->result_array();		
                $html .= '<select id="employee_type" multiple="multiple" class="multi-select" style="width:400px;" name="employee_type[]">';
                    foreach($employee_type as $employee_type_record){
                        $html .= '<option value="'.$employee_type_record["employee_type_id"].'">'.$employee_type_record["employee_type"].'</option>';
                    }
                $html .= '</select>';	
		        break;	
		    case 6: // employment status
		    	$this->db->where('deleted', 0);
				$employment_status = $this->db->get('employment_status')->result_array();		
                $html .= '<select id="employment_status" multiple="multiple" class="multi-select" style="width:400px;" name="employment_status[]">';
                    foreach($employment_status as $employment_status_record){
                        $html .= '<option value="'.$employment_status_record["employment_status_id"].'">'.$employment_status_record["employment_status"].'</option>';
                    }
                $html .= '</select>';	
		        break;		        	               
		    case 7: // employee
 			   	$this->db->where('user.deleted', 0);
		    	$this->db->join('employee', 'employee.employee_id = user.employee_id');
				$employee = $this->db->get('user')->result_array();		
                $html .= '<select id="user" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                $html .= '</select>';	
		        break;	
		}	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}	

	// END - default module functions

	// START custom module funtions
	function get_report(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect( base_url().$this->module_link );
		}

		$this->load->helper('time_upload');
		$report_by = $this->input->post('category');
		$this->_tardiness_report_by_employee();
	
	}

	function get_employees()
	{
		if (IS_AJAX)
		{
			$html = '';
			if ($this->input->post('category_id') != 'null') {
				switch ($this->input->post('category')) {
				    case 0:
		                $html .= '';	
				        break;
				    case 1: // company
				    	$where = 'user.company_id IN ('.$this->input->post('category_id').')';
				        break;
				    case 2: // division
						$where = 'user.division_id IN ('.$this->input->post('category_id').')';
				        break;
				    case 3: // department
				    	$where = 'user.department_id IN ('.$this->input->post('category_id').')';
				        break;	
				    case 4: // section
				    	$where = 'user.section_id IN ('.$this->input->post('category_id').')';
				        break;				        
				    case 5: // level
				    	$where = 'employee_type IN ('.$this->input->post('category_id').')';
				        break;
				    case 6: // employment status
				    	$where = 'status_id IN ('.$this->input->post('category_id').')';
				        break;				        			        				        				        	        
				}	
				$this->db->where($where);
				$this->db->where('user.deleted', 0);
				$this->db->join('employee','user.employee_id = employee.employee_id');
				$employee = $this->db->get('user')->result_array();		

                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                $html .= '</select>';	

			}

            $data['html'] = $html;
    		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function export()
	{

		$employee_array = $this->_export_tardiness_report_by_employee();

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Tardiness Report")
		            ->setDescription("Tardiness Report");

		if (count($employee_array) > 0){
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
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);					

			//Initialize style
			$styleArray = array(
				'font' => array(
					'bold' => true,
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				)
			);		

			$HorizontalRight = array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				)
			);	

			$HorizontalLeft = array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);	

			$styleArrayBorder = array(
				'borders' => array(
				    'allborders' => array(
				      'style' => PHPExcel_Style_Border::BORDER_THIN
				    )
			    )
			);

			$fields = array("DEPARTMENT", "employee_id", "ID NO","NAME","DATE","TIME-IN","LATES (MIN)","INFRACTION");
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

				$activeSheet->setCellValueExplicit($xcoor . '6', $field, PHPExcel_Cell_DataType::TYPE_STRING); 


				$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
				
				$alpha_ctr++;
			}

			for($ctr=1; $ctr<6; $ctr++){

				$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 2].$ctr);

			}		

			//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
			$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

			$activeSheet->setCellValueExplicit('A2', 'Tardiness Report', PHPExcel_Cell_DataType::TYPE_STRING); 


			if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
				
				$activeSheet->setCellValueExplicit('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))), PHPExcel_Cell_DataType::TYPE_STRING); 
			}

			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);					

			// contents.
			$line = 7;
			$line2 = 0;
			$total_lates = 0;
			foreach ($employee_array as $key => $value) {
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

					if($xcoor == 'B'){
						$idno[$line2] = $value[$field];
					}

					if($xcoor == 'E'){
						$focus_date[$idno[$line2]][$line2] = $value[$field];
					}

					if($xcoor == 'F'){
						$focus_time[$idno[$line2]][$line2] = $value[$field];
					}

					if($xcoor == 'G'){
						$lates[$idno[$line2]][$line2] = $value[$field];
					}

					if($xcoor == 'H'){
						$infraction[$idno[$line2]][$line2] = $value[$field];
					}

					$alpha_ctr++;
				}

				if ( $idno[$line2] != $idno[$line2 - 1] ){
					$line = $line + 2;
					$xls_row[$line2] = $line - 1;
				}

				$line++;
				$line2++;
			}

			$objPHPExcel->getActiveSheet()->removeColumn('A');
			$objPHPExcel->getActiveSheet()->removeColumn('B');
			$objPHPExcel->getActiveSheet()->removeColumn('F');
				$objPHPExcel->getActiveSheet()->setCellValue('A6', 'ID NO');
			$total_infraction = 0;
			$line = 7;
			foreach($lates as $emp_id => $lates_idno){
				$emp_row = $this->db->query("SELECT
						hr_user_company_department.department,
						hr_employee.id_number,
						hr_user.lastname,
						hr_user.firstname,
						hr_user.middleinitial
					FROM hr_user 
					LEFT JOIN hr_employee
						ON hr_user.employee_id = hr_employee.employee_id
					LEFT JOIN hr_user_company_department
						ON hr_user.department_id = hr_user_company_department.department_id
					WHERE hr_user.employee_id = ".$emp_id)->row();
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$line, 'Department: '.$emp_row->department);
				$objPHPExcel->getActiveSheet()->mergeCells('A'.$line.':'.'B'.$line);
				$objPHPExcel->getActiveSheet()->getStyle('A'.$line)->applyFromArray($styleArray);

				$line++;
				foreach($lates_idno as $key => $late_idno){
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$line, $emp_row->id_number);
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$line, $emp_row->lastname.",".$emp_row->firstname." ".$emp_row->middleinitial);
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$line, $focus_date[$emp_id][$key]);
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$line, $focus_time[$emp_id][$key]);
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$line, $late_idno);
					
					$total_infraction = $infraction[$emp_id][$key];
					$total_lates = $total_lates + $late_idno;

					$line++;
				}

				$objPHPExcel->getActiveSheet()->setCellValue('B'.$line, 'Total Infraction');
				$objPHPExcel->getActiveSheet()->getStyle('B'.$line)->applyFromArray($HorizontalRight);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$line)->getFont()->setBold(true);
				
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$line, $total_infraction);
				$objPHPExcel->getActiveSheet()->getStyle('C'.$line)->getFont()->setBold(true);

				$objPHPExcel->getActiveSheet()->setCellValue('D'.$line, 'Total Lates');
				$objPHPExcel->getActiveSheet()->getStyle('D'.$line)->getFont()->setBold(true);
				
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$line, $total_lates);
				$objPHPExcel->getActiveSheet()->getStyle('E'.$line)->getFont()->setBold(true);

				$line++;
				$line++;

				$total_infraction = 0;
				$total_lates = 0;
			}

			// $objPHPExcel->getActiveSheet()->getStyle('A7:'.'E'.$line)->applyFromArray($HorizontalLeft);
			$objPHPExcel->getActiveSheet()->getStyle('D7:'.'E'.$line)->applyFromArray($HorizontalRight);
			$objPHPExcel->getActiveSheet()->getStyle('A6:'.'E'.($line - 1))->applyFromArray($styleArrayBorder);

			// Save it as an excel 2003 file
			$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment;filename='.date('Y-m-d').'_'.url_title("Tardiness Report").'.xls');
			header('Content-Transfer-Encoding: binary');
			
			$objWriter->save('php://output');				
		}
		else{

			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'No Record Found');

			$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment;filename='.date('Y-m-d').'_'.url_title("Tardiness Report").'.xls');
			header('Content-Transfer-Encoding: binary');
			
			$objWriter->save('php://output');				
		}
	}
	function _export_tardiness_report_by_employee(){
		//get employees

		if( $this->input->post('employee_id_multiple') ){
			$this->date_from = date('Y-m-d', strtotime($this->input->post('date_from')));
			$this->date_to = date('Y-m-d', strtotime($this->input->post('date_to')));

			$employee_ids = explode(',', $this->input->post('employee_id_multiple'));
			$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
			$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id', 'LEFT');
			$this->db->where_in($this->db->dbprefix('user').'.user_id', $employee_ids);
			$this->db->order_by('department');
			$this->db->order_by('lastname');
			$employees = $this->db->get('user')->result();
			$employee_array = array();

			foreach( $employees as $employee ){
				$infraction = 0;
				$employee_id = $employee->employee_id;
				$id_no = $employee->id_number;
				$department = $employee->department;
				$name = $employee->lastname .','. $employee->firstname;

				$qry = "SELECT count(*) as total_infraction
				FROM {$this->db->dbprefix}employee_dtr a
				WHERE a.employee_id = '{$employee_id}'
				AND a.lates > 0 AND a.lates IS NOT NULL
				AND a.date between '{$this->date_from}' AND '{$this->date_to}' AND a.deleted = 0 AND a.late_infraction = 1";
				$dtrs = $this->db->query($qry);
				if ($dtrs && $dtrs->num_rows > 0){
					$dtrs_row = $dtrs->row();
					$infraction = $dtrs_row->total_infraction;
				}

				if ($infraction > 0){
					$qry = "SELECT a.date,  YEAR(a.date) AS year, a.lates, a.time_in1
					FROM {$this->db->dbprefix}employee_dtr a
					WHERE a.employee_id = '{$employee->employee_id}'
					AND a.lates > 0 AND a.lates IS NOT NULL
					AND a.date between '{$this->date_from}' AND '{$this->date_to}' AND a.deleted = 0 AND a.late_infraction = 1
					ORDER BY a.date";
					$dtrs = $this->db->query($qry);
					
					if($dtrs->num_rows() > 0){
						foreach($dtrs->result() as $dtr){
							$date = date('m/d/Y', strtotime($dtr->date));
							$lates = intval($dtr->lates);
							$timein = $dtr->time_in1;

							$employee_array[] = array("DEPARTMENT" => $department, "employee_id" => $employee_id, "ID NO" => $id_no, "NAME" => $name, "DATE" => $date, "TIME-IN" => $timein, "LATES (MIN)" => $lates, "INFRACTION" => $infraction);
						}
					}
				}
				// else{
				// 		$employee_array[] = array("DEPARTMENT" => $department, "employee_id" => $employee_id, "ID NO" => $id_no, "NAME" => $name, "DATE" => '', "TIME-IN" => '', "LATES (MIN)" => '', "INFRACTION" => '');
				// }				
			}
			return $employee_array;
		}
		else{
			return array();
		}
	}

	function _export_tardiness_report_by_year(){
		if( $this->input->post('year') ){
			$this->year = $this->input->post('year');
			$qry = "select a.*, CONCAT(b.lastname, ', ', b.firstname) as employee,id_number
			FROM {$this->db->dbprefix}employee_dtr a
			LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
			LEFT JOIN {$this->db->dbprefix}employee c on b.employee_id = c.employee_id
			WHERE YEAR(a.date) = '{$this->year}' AND a.lates is not null and a.lates > 0 AND a.deleted = 0 AND a.late_infraction = 1
			ORDER BY b.employee_id,a.date";
			$dtrs = $this->db->query($qry);

			$employee_array = array();

			if($dtrs->num_rows() > 0){
				foreach($dtrs->result() as $dtr){
					$infraction = 0;
					$employee_id = $dtr->employee_id;
					$id_no = $dtr->id_number;
					$name = $dtr->employee;
					$date = date('m/d/Y', strtotime($dtr->date));
					$lates = intval($dtr->lates);
					$timein = $dtr->time_in1;

					$qry = "select count(*) as total_infraction
					FROM {$this->db->dbprefix}employee_dtr a
					LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
					LEFT JOIN {$this->db->dbprefix}employee c on b.employee_id = c.employee_id
					WHERE a.employee_id = '{$employee_id}'
					AND YEAR(a.date) = '{$this->year}' AND a.lates is not null and a.lates > 0 AND a.deleted = 0 AND a.late_infraction = 1
					ORDER BY a.date, b.lastname, b.employee_id";
					$infraction = $this->db->query($qry);

					if ($infraction && $infraction->num_rows > 0){
						$infraction_row = $infraction->row();
						$infraction = $infraction_row->total_infraction;
					}

					$employee_array[] = array("employee_id" => $employee_id, "ID NO" => $id_no, "NAME" => $name, "DATE" => $date, "TIME-IN" => $timein, "LATES (MIN)" => $lates, "INFRACTION" => $infraction);
				}
			}

			return $employee_array;			
		}
		else{
			return array();
		}

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function _tardiness_report_by_employee(){
		//get employees
		if ($this->input->post('employee') && $this->input->post('employee') != 'null'){
			$this->date_from = date('Y-m-d', strtotime($this->input->post('date_from')));
			$this->date_to = date('Y-m-d', strtotime($this->input->post('date_to')));

			$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');

            // switch ($this->input->post('category')) {
            //     case 1:
            //             if( $this->input->post('section') && $this->input->post('section') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.section_id ', $this->input->post('section'));
            //         break;
            //     case 2:
            //             if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.division_id ', $this->input->post('division'));       
            //         break;
            //     case 3:
            //             if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.department_id ', $this->input->post('department'));
            //         break;
            //     case 4:
            //             if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       
            //         break;                                                 
            // }
            
			$this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee')); 
			$this->db->order_by('lastname');
			$employees = $this->db->get('user')->result();
			
			$response->rows = array();
			$response->records = 0;
			foreach( $employees as $employee ){
				$response->rows[$response->records]['id'] = $cell[0] = 'employee-'.$employee->employee_id;
				$cell[1] =  $employee->lastname.', '.$employee->firstname;
				$cell[2] =  ''; //no of hours
				$cell[3] =  ''; // total
				$cell[4] =  ''; //status
				$cell[5] = '0'; //level
				$cell[6] = null; //parent
				$cell[7] = false; //leaf
				$cell[8] = true; //expanded field
				
				$response_index = $response->records;
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				
				$new_rows = $this->_employee_yearly_summary($employee->employee_id, $response);
				if($new_rows){
					$response = $new_rows;
				}
				else{
					$response->rows[$response_index]['cell'][7] = true;
				}
			}

			$response->page = 1;
			$response->total = 1;
		}
		else{
			$response->message = "Insufficient data supplied!<br/>Please call the Administrator.";
			$response->msg_type = "error";
		}

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);

	}


	function _employee_yearly_summary( $employee_id, $response ){
		$employee_index = $response->records - 1;

		$year_from = date('Y', strtotime($this->date_from));
		$year_to = date('Y', strtotime($this->date_to));

		$year = array();
		$total = array();
		while( $year_from <= $year_to ){
			$qry = "SELECT a.date,  YEAR(a.date) AS year, a.lates, a.time_in1
			FROM {$this->db->dbprefix}employee_dtr a
			WHERE a.employee_id = '{$employee_id}'
			AND a.lates > 0 AND a.lates IS NOT NULL
			AND a.date between '{$this->date_from}' AND '{$this->date_to}' AND a.deleted = 0 AND a.late_infraction = 1
			ORDER BY a.date";
			$dtrs = $this->db->query($qry);
			
			if($dtrs->num_rows() > 0){
				foreach($dtrs->result() as $dtr){
					$month = date('F', strtotime($dtr->date));
					$n = date('n', strtotime($dtr->date));
					$date = date('m/d/Y', strtotime($dtr->date));

					$lates = intval($dtr->lates);
					$year[$year_from][$n][] = array('late' => $lates, 'date' => $date);
					$total[$year_from]['year'] = !isset($total[$year_from]['year']) ? 1 : $total[$year_from]['year'] + 1;
					$total[$year_from]['month-'.$n] = !isset($total[$year_from]['month-'.$n]) ? 1 : $total[$year_from]['month-'.$n] + 1;
					$response->rows[$employee_index]['cell'][3] = $response->rows[$employee_index]['cell'][3] == "" ? 1 : $response->rows[$employee_index]['cell'][3] + 1;
				}
			}

			$year_from++;
		}

		//dbug($year);

		if(sizeof($year) > 0){
			foreach($year as $year_from => $month ){
				$response->rows[$response->records]['id'] = $cell[0] = 'year-'.$employee_id.'-'.$year_from;
				$cell[1] =  $year_from;
				$cell[2] =  ''; //no of hours	
				$cell[3] =  $total[$year_from]['year']; // total
				$cell[4] =  ''; //status
				$cell[5] = '1'; //level
				$cell[6] = 'employee-'.$employee_id; //parent
				$cell[7] = false; //leaf
				$cell[8] = true; //expanded field
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;

				foreach($month as $n => $dtr){
					$f = int_to_month($n, true);
					$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$employee_id.'-'.$year_from.'-'.$n;
					$cell[1] = $f;
					$cell[2] = ''; //no of hours				
					$cell[3] =   $total[$year_from]['month-'.$n]; // total
					$cell[4] =  ''; //status
					$cell[5] = '2'; //level
					$cell[6] = 'year-'.$employee_id.'-'.$year_from; //parent
					$cell[7] = false; //leaf
					$cell[8] = false; //expanded field
					$response->rows[$response->records]['cell'] = $cell;
					$response->records++;

					foreach($dtr as $day){
						$date = date('Y-n-j', strtotime($day['date']));
						$response->rows[$response->records]['id'] = $cell[0] = 'date-'.$employee_id.'-'.$date;
						$cell[1] = $day['date'];
						$cell[2] = round( $day['late'] / 60, 2); //no of hours				
						$cell[3] = ''; // total
						$cell[4] = 'No Tardiness Transaction'; //status
						$cell[5] = '3'; //level
						$cell[6] = 'month-'.$employee_id.'-'.$year_from.'-'.$n; //parent
						$cell[7] = true; //leaf
						$cell[8] = false; //expanded field
						$response->rows[$response->records]['cell'] = $cell;
						$response->records++;
					}
				} 

			}
			return $response;
		}

		return false;
	}

	function _tardiness_report_by_year(){
		if( $this->input->post('year') ){
			$this->year = $this->input->post('year');
			$qry = "select a.*, CONCAT(b.lastname, ', ', b.firstname) as employee
			FROM {$this->db->dbprefix}employee_dtr a
			LEFT JOIN {$this->db->dbprefix}user b on a.employee_id = b.employee_id
			LEFT JOIN {$this->db->dbprefix}employee c on b.employee_id = c.employee_id
			WHERE YEAR(a.date) = '{$this->year}' AND a.lates is not null and a.lates > 0 AND a.deleted = 0 AND a.late_infraction = 1
			ORDER BY a.date, b.lastname, b.employee_id";
			$dtrs = $this->db->query($qry);
			
			$response->rows = array();
			$response->records = 0;
			if($dtrs->num_rows() > 0){
				$month = array();
				$total = array();
				foreach($dtrs->result() as $dtr){
					// Get the wrok sched
					$ws = $this->system->get_employee_worksched( $dtr->employee_id, $dtr->date );
					if( $ws ){
						$n = date('n', strtotime($dtr->date));
						$day = date('N', strtotime($dtr->date));
						$date = date('m/d/Y', strtotime($dtr->date));	
						switch($day){
							case 1:
								$shift_id = $ws->monday_shift_id;
								break;
							case 2:
								$shift_id = $ws->tuesday_shift_id;
								break;
							case 3:
								$shift_id = $ws->wednesday_shift_id;
								break;
							case 4:
								$shift_id = $ws->thursday_shift_id;
								break;
							case 5:
								$shift_id = $ws->friday_shift_id;
								break;
							case 6:
								$shift_id = $ws->saturday_shift_id;
								break;
							case 7:
								$shift_id = $ws->sunday_shift_id;
								break;
						}

						$shift = $this->db->get_where('timekeeping_shift', array('shift_id' => $shift_id))->row();
						$shift_start = $shift->shifttime_start;
						$shift_end = $shift->shifttime_end;
						$grace_period = $shift->shift_grace_period;
						
						// Get excused tardiness first.
						$et = get_form($employee_id, 'et', NULL, $dtr->date);
						
						if ($et->num_rows() == 0 && 
							strtotime(date('H:i:s', strtotime($dtr->time_in1))) > 
							strtotime('+' . $grace_period . ' minutes', strtotime($shift_start))) {

							$lates = (strtotime(date('H:i:s', strtotime($dtr->time_in1))) - strtotime($shift_start)) / 60;
							$month[$n][$dtr->employee_id]['name'] = $dtr->employee;
							$month[$n][$dtr->employee_id]['lates'][] = array('late' => $lates, 'date' => $date);
							$total[$n]['infraction'] = !isset($total[$n]['infraction']) ? 1 : $total[$n]['infraction'] + 1;
							$total[$n]['employee-'.$dtr->employee_id] = !isset($total[$n]['employee-'.$dtr->employee_id]) ? 1 : $total[$n]['employee-'.$dtr->employee_id] + 1;					
						}
					}
				}
				foreach($month as $n => $employee){
					$f = int_to_month($n, true);
					$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$n;
					$cell[1] = $f;
					$cell[2] = ''; //no of hours				
					$cell[3] = $total[$n]['infraction']; // total
					$cell[4] = ''; //status
					$cell[5] = '0'; //level
					$cell[6] = null; //parent
					$cell[7] = false; //leaf
					$cell[8] = true; //expanded field
					$response->rows[$response->records]['cell'] = $cell;
					$response->records++;
					
					foreach($employee as $employee_id => $detail){
						$response->rows[$response->records]['id'] = $cell[0] = 'employee-'.$n.'-'.$employee_id;
						$cell[1] = $detail['name'];
						$cell[2] = ''; //no of hours				
						$cell[3] = $total[$n]['employee-'.$employee_id]; // total
						$cell[4] = ''; //status
						$cell[5] = '1'; //level
						$cell[6] = 'month-'.$n; //parent
						$cell[7] = false; //leaf
						$cell[8] = false; //expanded field
						$response->rows[$response->records]['cell'] = $cell;
						$response->records++;

						foreach($detail['lates'] as $day){
							$date = date('Y-n-j', strtotime($day['date']));
							$response->rows[$response->records]['id'] = $cell[0] = 'date-'.$employee_id.'-'.$date;
							$cell[1] = $day['date'];
							$cell[2] = round( $day['late'] / 60, 2); //no of hours				
							$cell[3] = ''; // total
							$cell[4] = 'No Tardiness Transaction'; //status
							$cell[5] = '3'; //level
							$cell[6] = 'employee-'.$n.'-'.$employee_id; //parent
							$cell[7] = true; //leaf
							$cell[8] = false; //expanded field
							$response->rows[$response->records]['cell'] = $cell;
							$response->records++;
						}	
					}
				}
			}

			$response->page = 1;
			$response->total = 1;
		}
		else{
			$response->message = "Insufficient data supplied!<br/>Please call the Administrator.";
			$response->msg_type = "error";
		}

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */
