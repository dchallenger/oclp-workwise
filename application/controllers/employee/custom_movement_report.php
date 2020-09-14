<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class custom_movement_report extends MY_Controller{
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
        $data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>'.uploadify_script();
        $data['scripts'][] = multiselect_script();
        $data['content'] = 'slategray/payroll/report/report_view';  
        
        //other views to load
        $data['views'] = array();

        $this->load->model( 'uitype_edit' );
        $data['fieldgroups'] = $this->_record_detail( '-1' );
        
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

    function populate_sub_category()
    {
        if (IS_AJAX)
        {
            switch($this->input->post('category_item_id'))
            {
                case 1:
                    $category_item = $this->db->query("SELECT company_id AS item_id, company AS item_name FROM {$this->db->dbprefix}user_company WHERE deleted = 0;")->result_array();
                    break;
                case 2:
                    $category_item = $this->db->query("SELECT division_id AS item_id, division AS item_name FROM {$this->db->dbprefix}user_company_division WHERE deleted = 0;")->result_array();
                    break;
                case 3:
                    $category_item = $this->db->query("SELECT department_id AS item_id, department AS item_name FROM {$this->db->dbprefix}user_company_department WHERE deleted = 0;")->result_array();
                    break;
            }

            $category_item_html = '<select id="category_item_id" multiple="multiple" class="multi-select" name="category_item_id[]">';
            
            foreach($category_item as $category_item_record){
                $category_item_html .= '<option value="'.$category_item_record["item_id"].'">'.$category_item_record["item_name"].'</option>';
            }
            
            $category_item_html .= '</select>';

            $response['category_item_html'] = $category_item_html;

            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
        }
        else
        {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
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

    function export_report(){
    	$employee_ids = $this->input->post('employee_id_multiple');
    	$date_from = $this->input->post('date_from');
    	$date_to = $this->input->post('date_to');

        $this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		// Default column width
		$activeSheet->getDefaultColumnDimension()->setWidth(20);

		// Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$styleArrayLeft = array(
			'font' => array(
				'bold' => true,
			),			'alignment' => array(
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
			  ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
				)
			);

		$activeSheet->getStyle('A1:W1')->applyFromArray($styleArray);
		$activeSheet->setCellValueExplicit('A1', 'Movement Report', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->mergeCells('A1:W1');

		$activeSheet->getStyle('A2:K2')->applyFromArray($styleArrayLeft);
		$activeSheet->setCellValueExplicit('A2', 'For the period covering from '.date('Y-M-d', strtotime($date_from)).' to '.date('Y-M-d', strtotime($date_to)), PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->mergeCells('A2:K2');

		$activeSheet->getStyle('M2:W2')->applyFromArray($styleArrayLeft);
		$activeSheet->setCellValueExplicit('M2', '', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->mergeCells('M2:W2');

		$activeSheet->getStyle('A4:x4')->applyFromArray($headerstyle);
		$activeSheet->setCellValueExplicit('A4', 'Employee Name', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('B4', 'Date Hired', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('C4', 'Entry Date', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('D4', 'Nature Of Movement', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('E4', 'Effectivity Date', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('F4', 'Current Company', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('G4', 'New Company', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('H4', 'Current Division', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('I4', 'New Division', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('J4', 'Current Department', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('K4', 'New Department', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('L4', 'Current Location', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('M4', 'New Location', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('N4', 'Current Position', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('O4', 'New Position', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('P4', 'Current Project', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('Q4', 'New Project', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('R4', 'Current Rank', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('S4', 'New Rank', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('T4', 'Current Employee Type', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('U4', 'New Employee Type', PHPExcel_Cell_DataType::TYPE_STRING);
		// $activeSheet->setCellValueExplicit('V4', 'Compensation Adjustment', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('V4', 'Current Basic Salary', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('W4', 'New Basic Salary', PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheet->setCellValueExplicit('X4', 'ID Number', PHPExcel_Cell_DataType::TYPE_STRING);

        $query_str = $this->db->query("SELECT
					u.lastname, u.firstname, u.middleinitial,
					e.employed_date,
					em.created_date,
					emt.movement_type,
					-- em.movement_effectivity_date, em.transfer_effectivity_date, em.compensation_effectivity_date,
					IF(em.movement_effectivity_date IS NULL OR em.movement_effectivity_date = '0000-00-00',
						IF(em.transfer_effectivity_date IS NULL OR em.movement_effectivity_date = '0000-00-00',
							em.compensation_effectivity_date, 
							em.transfer_effectivity_date), movement_effectivity_date) AS effectivity_date,
					em.hidden_id_current,
					-- hidden_id_current company_id,
					em.company_id,
					-- hidden_id_current division_id,
					em.division_id,
					em.current_department_id, -- hidden_id_current department_id
					'new department_id',
					-- hidden_id_current location_id,
					em.location_id,
					-- hidden_id_current position_id,
					em.new_position_id,
					em.current_project_name_id,
					em.project_name_id,
					-- hidden_id_current rank_id,
					em.rank_id,
					-- hidden_id_current employee_type,
					em.employee_type,
					em.current_basic_salary,
					em.new_basic_salary,
					e.id_number
				FROM hr_employee_movement em
				LEFT JOIN hr_employee e ON em.employee_id = e.employee_id
				LEFT JOIN hr_user u ON em.employee_id = u.employee_id
				LEFT JOIN hr_employee_movement_type emt ON em.employee_movement_type_id = emt.employee_movement_type_id
				WHERE e.deleted = 0
				AND em.employee_id IN (".$employee_ids.")
				AND IF(em.movement_effectivity_date IS NULL OR em.movement_effectivity_date = '0000-00-00',
						IF(em.transfer_effectivity_date IS NULL OR em.movement_effectivity_date = '0000-00-00',
							em.compensation_effectivity_date, 
							em.transfer_effectivity_date), movement_effectivity_date) BETWEEN '".date('Y-m-d', strtotime($date_from))."' AND '".date('Y-m-d', strtotime($date_to))."'
				ORDER BY  IF(em.movement_effectivity_date IS NULL OR em.movement_effectivity_date = '0000-00-00',
						IF(em.transfer_effectivity_date IS NULL OR em.movement_effectivity_date = '0000-00-00',
							em.compensation_effectivity_date, 
							em.transfer_effectivity_date), movement_effectivity_date), u.lastname, u.firstname");

		if($query_str && $query_str->num_rows() > 0){
			$xcl_line = 5;

			foreach ($query_str->result() as $query_row) {
				$current_ids = explode(', ', $query_row->hidden_id_current);

				foreach ($current_ids as $current_id) {
					$curr_id = explode(' = ', $current_id);

					if($curr_id[0] == 'position_id'){
						$query_row_position_id = $curr_id[1];
					}

					if($curr_id[0] == 'role_id'){
						$query_row_role_id = $curr_id[1];
					}

					if($curr_id[0] == 'department_id'){
						$query_row_department_id = $curr_id[1];
					}

					if($curr_id[0] == 'rank_id'){
						$query_row_rank_id = $curr_id[1];
					}

					if($curr_id[0] == 'employee_type'){
						$query_row_employee_type = $curr_id[1];
					}

					if($curr_id[0] == 'job_level'){
						$query_row_job_level = $curr_id[1];
					}

					if($curr_id[0] == 'range_of_rank'){
						$query_row_range_of_rank = $curr_id[1];
					}

					if($curr_id[0] == 'rank_code'){
						$query_row_rank_code = $curr_id[1];
					}

					if($curr_id[0] == 'company_id'){
						$query_row_company_id = $curr_id[1];
					}

					if($curr_id[0] == 'division_id'){
						$query_row_division_id = $curr_id[1];
					}

					if($curr_id[0] == 'location_id'){
						$query_row_location_id = $curr_id[1];
					}

					if($curr_id[0] == 'segment_1_id'){
						$query_row_segment_1_id = $curr_id[1];
					}

					if($curr_id[0] == 'segment_2_id'){
						$query_row_segment_2_id	 = $curr_id[1];
					}
				}

				$activeSheet->setCellValueExplicit('A'.$xcl_line, $query_row->lastname.', '.$query_row->firstname.' '.$query_row->middleinitial, PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('B'.$xcl_line, date('d-M-Y', strtotime($query_row->employed_date)), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('C'.$xcl_line, date('d-M-Y', strtotime($query_row->created_date)), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('D'.$xcl_line, $query_row->movement_type, PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('E'.$xcl_line, date('d-M-Y', strtotime($query_row->effectivity_date)), PHPExcel_Cell_DataType::TYPE_STRING);

				$activeSheet->setCellValueExplicit('F'.$xcl_line, $this->_get_value('user_company', $query_row_company_id), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('G'.$xcl_line, $this->_get_value('user_company', $query_row->company_id), PHPExcel_Cell_DataType::TYPE_STRING);
				
				$activeSheet->setCellValueExplicit('H'.$xcl_line, $this->_get_value('user_company_division', $query_row_division_id), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('I'.$xcl_line, $this->_get_value('user_company_division', $query_row->division_id), PHPExcel_Cell_DataType::TYPE_STRING);
				
				$activeSheet->setCellValueExplicit('J'.$xcl_line, $this->_get_value('user_company_department', $query_row->current_department_id), PHPExcel_Cell_DataType::TYPE_STRING);
				// $activeSheet->setCellValueExplicit('K'.$xcl_line, $this->_get_value('user_company_department', $query_row 'new dept id'), PHPExcel_Cell_DataType::TYPE_STRING);

				$activeSheet->setCellValueExplicit('L'.$xcl_line, $this->_get_value('user_location', $query_row_location_id), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('M'.$xcl_line, $this->_get_value('user_location', $query_row->location_id), PHPExcel_Cell_DataType::TYPE_STRING);
				
				$activeSheet->setCellValueExplicit('N'.$xcl_line, $this->_get_value('user_position', $query_row_position_id), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('O'.$xcl_line, $this->_get_value('user_position', $query_row->new_position_id), PHPExcel_Cell_DataType::TYPE_STRING);

				$activeSheet->setCellValueExplicit('P'.$xcl_line, $this->_get_value('project_name', $query_row->current_project_name_id), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('Q'.$xcl_line, $this->_get_value('project_name', $query_row->project_name_id), PHPExcel_Cell_DataType::TYPE_STRING);
				
				$activeSheet->setCellValueExplicit('R'.$xcl_line, $this->_get_value('user_rank', $query_row_rank_id), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('S'.$xcl_line, $this->_get_value('user_rank', $query_row->rank_id), PHPExcel_Cell_DataType::TYPE_STRING);
				
				$activeSheet->setCellValueExplicit('T'.$xcl_line, $this->_get_value('employee_type', $query_row_employee_type), PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('U'.$xcl_line, $this->_get_value('employee_type', $query_row->employee_type), PHPExcel_Cell_DataType::TYPE_STRING);

				// $query_row_comp_adj = $query_row->compensation_effectivity_date;
				// $activeSheet->setCellValueExplicit('V'.$xcl_line, $query_row_comp_adj, PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('V'.$xcl_line, $query_row->current_basic_salary, PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('W'.$xcl_line, $query_row->new_basic_salary, PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit('X'.$xcl_line, $query_row->id_number, PHPExcel_Cell_DataType::TYPE_STRING);

				$xcl_line++;
			}
		}
		else{
			$activeSheet->setCellValueExplicit('A5', 'No Record Found', PHPExcel_Cell_DataType::TYPE_STRING);
			$activeSheet->mergeCells('A5:W5');
		}

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=Movement_Report_'.date('Y-m-d').'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	
    }

    function _get_value($table_name, $field_value) {
	    if($field_value == 0){
	    	return ' ';
	    }
	    else{
	    	switch ($table_name){
	    	case 'user_company': $this->db->select('company AS _value');
	    		$this->db->where('company_id', $field_value); break;
	    	case 'user_company_division': $this->db->select('division AS _value');
	    		$this->db->where('division_id', $field_value); break;
	    	case 'user_company_department': $this->db->select('department AS _value');
	    		$this->db->where('department_id', $field_value); break;
	    	case 'user_location': $this->db->select('location AS _value');
	    		$this->db->where('location_id', $field_value); break;
	    	case 'user_position': $this->db->select('position AS _value');
	    		$this->db->where('position_id', $field_value); break;
	    	case 'project_name': $this->db->select('project_name AS _value');
	    		$this->db->where('project_name_id', $field_value); break;
	    	case 'user_rank': $this->db->select('job_rank AS _value');
	    		$this->db->where('job_rank_id', $field_value); break;
	    	case 'employee_type': $this->db->select('employee_type AS _value');
	    		$this->db->where('employee_type_id', $field_value); break;
	    	}

	    	$table_qry = $this->db->get($table_name);

	    	if($table_qry && $table_qry->num_rows() > 0){
	    		$table_row = $table_qry->row();
	    		return $table_row->_value;
	    	}
	    	else{
	    		return ' ';
	    	}
	    }
	}
}

?>
