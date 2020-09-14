<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class meal_allowance_report extends MY_Controller
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
		$data['content'] = 'dtr/meal_allowance_report/listview';
		
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

	function get_category(){
		$html = '';
		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1: // section
		    	$this->db->where('deleted', 0);
				$company = $this->db->get('user_section')->result_array();		
                $html .= '<select id="section" multiple="multiple" class="multi-select" style="width:400px;" name="section[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["section_id"].'">'.$company_record["section"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2: // division
				$this->db->where('deleted', 0);
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 3: // department
		    	$this->db->where('deleted', 0);
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;		        
		    case 4: // employee
 			   	$this->db->where('user.deleted', 0);
		    	$this->db->join('employee', 'employee.employee_id = user.employee_id');
				$employee = $this->db->get('user')->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
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
    function populate_category()
    {
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
				$result = $this->db->get('user');		

                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';

                if ($result && $result->num_rows() > 0){
                    $employee = $result->result_array();
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
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
		$this->date_from = date('Y-m-d', strtotime($this->input->post('date_from')));
		$this->date_to = date('Y-m-d', strtotime($this->input->post('date_to')));

		$employee_ids = explode(',', $this->input->post('employee_id_multiple'));
		$employee_id = implode(',', $employee_ids);

		$qry = "SELECT e.id_number AS 'EMPLOYEE ID', CONCAT( u.lastname, ', ', u.firstname) AS NAME, ucdv.division AS DIVISION, ucd.department AS DEPARTMENT, et.employee_type AS LEVEL, CONCAT(DATE_FORMAT(ts.shifttime_start,'%h:%i %p '), ' - ', DATE_FORMAT(ts.shifttime_end,'%h:%i %p ')) AS SHIFT,
				GROUP_CONCAT(IF(extended_ot = 0, DATE_FORMAT(o.date,'%m/%d/%Y ') ,'') SEPARATOR '') AS 'WORK EXTENSION DATE',
				GROUP_CONCAT(IF(extended_ot = 0, DATE_FORMAT(o.datetime_from,'%h:%i %p ') ,'') SEPARATOR '') AS 'FROM',
				GROUP_CONCAT(IF(extended_ot = 0, DATE_FORMAT(o.datetime_to,'%h:%i %p ') ,'') SEPARATOR '') AS 'TO',
				GROUP_CONCAT(IF(extended_ot = 0, (TIMESTAMPDIFF(MINUTE, o.datetime_from, o.datetime_to)/60) ,'') SEPARATOR '') AS 'NUMBER OF HOURS',
				GROUP_CONCAT(IF(extended_ot = 0, DATE_FORMAT(o.date_approved,'%m/%d/%Y ') ,'') SEPARATOR '') AS 'DATE APPROVED',
				GROUP_CONCAT(IF(extended_ot = 0, o.employee_oot_id,'') SEPARATOR '') AS 'APPROVED BY',
				GROUP_CONCAT(IF(extended_ot = 1, DATE_FORMAT(o.datetime_from,'%h:%i %p ') ,'') SEPARATOR '') AS 'FROM (EXCESS)',
				GROUP_CONCAT(IF(extended_ot = 1, DATE_FORMAT(o.datetime_to,'%h:%i %p ') ,'') SEPARATOR '') AS 'TO (EXCESS)',
				GROUP_CONCAT(IF(extended_ot = 1, (TIMESTAMPDIFF(MINUTE, o.datetime_from, o.datetime_to)/60) ,'') SEPARATOR '') AS 'NUMBER OF HOURS (EXCESS)',
				GROUP_CONCAT(IF(extended_ot = 1, DATE_FORMAT(o.date_approved,'%m/%d/%Y ') ,'') SEPARATOR '') AS 'DATE APRROVED (EXCESS)',
				GROUP_CONCAT(IF(extended_ot = 1, o.employee_oot_id,'') SEPARATOR '') AS 'APPROVED BY (EXCESS)',
				ROUND(GROUP_CONCAT(IF(extended_ot = 0, FLOOR(TIMESTAMPDIFF(MINUTE, o.datetime_from, o.datetime_to)/60) ,'') SEPARATOR '') + GROUP_CONCAT(IF(extended_ot = 1, FLOOR(TIMESTAMPDIFF(MINUTE, o.datetime_from, o.datetime_to)/60) ,'') SEPARATOR '') ,4) AS 'MEAL ALLOWANCE (HOURS)'
				FROM {$this->db->dbprefix}employee_oot o
				LEFT JOIN {$this->db->dbprefix}employee e ON o.employee_id = e.employee_id
				LEFT JOIN {$this->db->dbprefix}user u ON o.employee_id = u.user_id
				LEFT JOIN {$this->db->dbprefix}user_company_division ucdv ON ucdv.division_id = u.division_id
				LEFT JOIN {$this->db->dbprefix}user_company_department ucd ON ucd.department_id = u.department_id
				LEFT JOIN {$this->db->dbprefix}employee_dtr_setup eds ON eds.employee_id = o.employee_id
				LEFT JOIN {$this->db->dbprefix}timekeeping_shift ts ON ts.shift_id = eds.shift_calendar_id
				LEFT JOIN {$this->db->dbprefix}employee_type et ON et.employee_type_id = e.employee_type
				WHERE o.deleted = 0 AND o.form_status_id = 3
				AND o.date BETWEEN '{$this->date_from}' AND '{$this->date_to}'
				AND o.employee_id IN ({$employee_id}) 
				GROUP BY o.employee_id, o.date";

		$res = $this->db->query($qry);

		$this->load->library('PHPExcel');       
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("Meal Allowance Report")
                    ->setDescription("Meal Allowance Report");

        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        //Initialize style
        $styleTotal = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $styleArray = array(
                    'font' => array(
                        'bold' => true,
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    )
                );

		if($res && $res->num_rows() > 0 ){
            
            $query = $res;
            $fields = $res->list_fields();

            //header
            $alpha_ctr = 0;
            $sub_ctr   = 0;
            $letters = array();
            $letter = 'A';
            while ($letter !== 'AAA') {
                $letters[] = $letter++;
            }

            //Default column width
            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(35); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(35);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30); 
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(25); 

            foreach ($fields as $field) {

                $xcoor = $letters[$alpha_ctr];
                $activeSheet->setCellValueExplicit($xcoor . '6', $field, PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
                $alpha_ctr++;
            }

            for($ctr=1; $ctr<5; $ctr++){

                $objPHPExcel->getActiveSheet()->mergeCells($letters[1].$ctr.':'.$letters[$alpha_ctr - 1].$ctr);

            }

            $activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');
            $mdate = getdate(date("U"));
            $mdate = "$mdate[month] $mdate[mday], $mdate[year]";
            
            $activeSheet->setCellValueExplicit('B1', $company, PHPExcel_Cell_DataType::TYPE_STRING);
            $activeSheet->setCellValueExplicit('B2', 'Meal Allowance Report', PHPExcel_Cell_DataType::TYPE_STRING); 
            $activeSheet->setCellValueExplicit('B3', date('F d, Y', strtotime($this->input->post('date_from'))) . ' to ' . date('F d, Y', strtotime($this->input->post('date_to'))), PHPExcel_Cell_DataType::TYPE_STRING); 
            $objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('B2')->applyFromArray($styleArray);

            // contents.
            $sline = 0;
            $line = 7;
            $sline = $line;
            $cnt = 1;

            foreach ($query->result() as $row) {
                $sub_ctr   = 0;         
                $alpha_ctr = 0;

                foreach ($fields as $field) {

                    if ($alpha_ctr >= count($letters)) {
                        $alpha_ctr = 0;
                        $sub_ctr++;
                    }

                    if ($sub_ctr > 0) {
                        $xcoor = $letters[$sub_ctr - 1] . $letters[$alpha_ctr];
                    } 
                    else {
                        $xcoor = $letters[$alpha_ctr];
                    }
                    
                    if( $xcoor == 'J' ||  $xcoor == 'O' || $xcoor == 'R'){

                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field} , PHPExcel_Cell_DataType::TYPE_NUMERIC); 
                        $objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    }
                    elseif( $xcoor == 'L' ||  $xcoor == 'Q')
                    {
                    	
                    	$approver = $this->get_approver($row->{$field});
                    	$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $approver , PHPExcel_Cell_DataType::TYPE_STRING); 
                    }else{
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field} , PHPExcel_Cell_DataType::TYPE_STRING); 
                    }
                    
                    $alpha_ctr++;     
                }

                $line++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueExplicit('I'. $line, 'TOTAL' , PHPExcel_Cell_DataType::TYPE_STRING); 
            $objPHPExcel->getActiveSheet()->setCellValue('J'. $line, '=SUM(J'.$sline.':'.'J'.($line-1).')' , PHPExcel_Cell_DataType::TYPE_NUMERIC); 
            $objPHPExcel->getActiveSheet()->getStyle('J'.$line)->applyFromArray($styleTotal);
            $objPHPExcel->getActiveSheet()->getStyle('J' . $line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('J' . $line)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $objPHPExcel->getActiveSheet()->setCellValue('O'. $line, '=SUM(O'.$sline.':'.'O'.($line-1).')' , PHPExcel_Cell_DataType::TYPE_NUMERIC); 
            $objPHPExcel->getActiveSheet()->getStyle('O'.$line)->applyFromArray($styleTotal);
            $objPHPExcel->getActiveSheet()->getStyle('O' . $line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('O' . $line)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $objPHPExcel->getActiveSheet()->setCellValue('R'. $line, '=SUM(R'.$sline.':'.'R'.($line-1).')' , PHPExcel_Cell_DataType::TYPE_NUMERIC); 
            $objPHPExcel->getActiveSheet()->getStyle('R'.$line)->applyFromArray($styleTotal);
            $objPHPExcel->getActiveSheet()->getStyle('R' . $line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('R' . $line)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);   

        }else {

        	$activeSheet->setCellValueExplicit('A1', 'No Record Found!', PHPExcel_Cell_DataType::TYPE_STRING);
        	$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray); 
        }

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename=MealAllowanceReport_'.$this->date_from.'_'.$this->date_to.'.xls');
        header('Content-Transfer-Encoding: binary');
        
        $objWriter->save('php://output'); 
	}	
	
	function get_approver($record_id){

		$approver = '';

		$qry = "SELECT GROUP_CONCAT(CONCAT(uf.lastname,', ',uf.firstname) SEPARATOR ' : ') AS app_name
				FROM {$this->db->dbprefix}form_approver fa 
				LEFT JOIN {$this->db->dbprefix}user uf ON fa.approver = uf.user_id
				WHERE fa.module_id = 60 AND fa.record_id = $record_id
				ORDER BY fa.sequence ASC";

		$res = $this->db->query($qry);

		if($res && $res->num_rows > 0){

			$result = $res->row();
			$approver = $result->app_name;
		}

		return $approver;
	}

	// END custom module funtions
}

/* End of file */
/* Location: system/application */
