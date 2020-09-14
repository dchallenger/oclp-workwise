<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ape_compliance_report extends MY_Controller
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
		$data['content'] = 'employee/ape_compliance_listview';
		
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
        foreach($months as $key => $month):
        	$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key;
			$cell[1] =  '<strong>'.$month.'</strong>';
			$cell[2] =  ''; //target
			$cell[3] =  ''; //complied
			$cell[4] =  ''; //percentage
			$cell[5] = '0'; //level
			$cell[6] = null; //parent
			$cell[7] = false; //leaf
			$cell[8] = true; //expanded field
			$response_index = $response->records;
			$response->rows[$response->records]['cell'] = $cell;
			$response->records++;
			unset($cell);

			//get officers
			$officer_complied = array();
			$officer_noncomplied = array();
			$this->db->select('user.user_id, user.lastname, user.firstname, user.birth_date');
	     	$this->db->join('employee','user.employee_id = employee.employee_id','left');
			$this->db->where('user.deleted', 0);
			$this->db->where('user.inactive', 0);
			$this->db->where('employee.status_id <', 8);
			$this->db->where('MONTH('.$this->db->dbprefix.'user.birth_date)', $key);
			$this->db->where_in('employee.employee_type', array(1,2));
			$result = $this->db->get('user');
			
			$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-officer';
			$cell[1] =  'Officer';
			$cell[2] =  $result->num_rows(); //target
			//get complied officers
			if($result->num_rows() > 0){
				foreach($result->result() as $employee){
					$bdate = date('-m-d', strtotime($employee->birth_date));
					$bday_this_year = $year . $bdate;
					$pivot_start = date('Y-m-d', strtotime( date('Y-m-d', strtotime($bday_this_year)) . ' -6 months'));
    				$pivot_end = date('Y-m-d', strtotime( date('Y-m-d', strtotime($bday_this_year)) . ' +6 months'));
					$qry = "SELECT a.*
					from {$this->db->dbprefix}employee_health a
					where a.health_type = 2 and a.deleted = 0 and a.health_type_status_id = 3
					and a.employee_id = {$employee->user_id} and a.date_of_completion between '{$pivot_start}' and '{$pivot_end}'";
					$health_info = $this->db->query($qry);
					if( $health_info->num_rows() > 0 ){
						//add to complied
						$officer_complied[] = array(
							'employee_id' => $employee->user_id,
							'name' => $employee->firstname .' '.$employee->lastname,
						);
					}
					else{
						//add to non-compliance
						$officer_noncomplied[] = array(
							'employee_id' => $employee->user_id,
							'name' => $employee->firstname .' '.$employee->lastname,
						);
					}
				}
			}

			$cell[3] =  sizeof( $officer_complied ); //complied
			$cell[4] =  $result->num_rows() > 0 ? sizeof( $officer_complied ) / $result->num_rows() : ''; //percentage
			$cell[5] = '1'; //level
			$cell[6] = 'month-'.$key; //parent
			$cell[7] = $result->num_rows() > 0 ? false : true; //leaf
			$cell[8] = false; //expanded field
			$response->rows[$response->records]['cell'] = $cell;
			$response->records++;
			unset($cell);

			if(sizeof( $officer_complied ) > 0){
				$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-officer-complied';
				$cell[1] =  'Compliant';
				$cell[2] =  sizeof( $officer_complied ); //target
				$cell[3] =  ''; //complied
				$cell[4] =  ''; //percentage
				$cell[5] = '2'; //level
				$cell[6] = 'month-'.$key.'-officer'; //parent
				$cell[7] = false; //leaf
				$cell[8] = false; //expanded field
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				unset($cell);
				foreach( $officer_complied as $employee ){
					$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-officer-complied-'.$employee['employee_id'];
					$cell[1] =  $employee['name'];
					$cell[2] =  ''; //target
					$cell[3] =  ''; //complied
					$cell[4] =  ''; //percentage
					$cell[5] = '3'; //level
					$cell[6] = 'month-'.$key.'-officer-complied'; //parent
					$cell[7] = true; //leaf
					$cell[8] = false; //expanded field
					$response->rows[$response->records]['cell'] = $cell;
					$response->records++;
					unset($cell);	
				}
			}

			if(sizeof( $officer_noncomplied ) > 0){
				$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-officer-noncompliant';
				$cell[1] =  'Non-compliant';
				$cell[2] =  sizeof( $officer_noncomplied ); //target
				$cell[3] =  ''; //complied
				$cell[4] =  ''; //percentage
				$cell[5] = '2'; //level
				$cell[6] = 'month-'.$key.'-officer'; //parent
				$cell[7] = false; //leaf
				$cell[8] = false; //expanded field
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				unset($cell);
				foreach( $officer_noncomplied as $employee ){
					$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-officer-noncompliant-'.$employee['employee_id'];
					$cell[1] =  $employee['name'];
					$cell[2] =  ''; //target
					$cell[3] =  ''; //complied
					$cell[4] =  ''; //percentage
					$cell[5] = '3'; //level
					$cell[6] = 'month-'.$key.'-officer-noncompliant'; //parent
					$cell[7] = true; //leaf
					$cell[8] = false; //expanded field
					$response->rows[$response->records]['cell'] = $cell;
					$response->records++;
					unset($cell);	
				}
			}

			//get non-officers
			$nonofficer_complied = array();
			$nonofficer_noncomplied = array();
			$this->db->select('user.user_id, user.lastname, user.firstname, user.birth_date');
	     	$this->db->join('employee','user.employee_id = employee.employee_id','left');
			$this->db->where('user.deleted', 0);
			$this->db->where('user.inactive', 0);
			$this->db->where('employee.status_id <', 8);
			$this->db->where('MONTH('.$this->db->dbprefix.'user.birth_date)', $key);
			$this->db->where_in('employee.employee_type', array(3));
			$result = $this->db->get('user');
			
			$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-nonofficer';
			$cell[1] =  'Non-officer';
			$cell[2] =  $result->num_rows(); //target
			//get complied nonofficers
			if($result->num_rows() > 0){
				foreach($result->result() as $employee){
					$bdate = date('-m-d', strtotime($employee->birth_date));
					$bday_this_year = $year . $bdate;
					$pivot_start = date('Y-m-d', strtotime( date('Y-m-d', strtotime($bday_this_year)) . ' -6 months'));
    				$pivot_end = date('Y-m-d', strtotime( date('Y-m-d', strtotime($bday_this_year)) . ' +6 months'));
					$qry = "SELECT a.*
					from {$this->db->dbprefix}employee_health a
					where a.health_type = 2 and a.deleted = 0 and a.health_type_status_id = 3
					and a.employee_id = {$employee->user_id} and a.date_of_completion between '{$pivot_start}' and '{$pivot_end}'";
					$health_info = $this->db->query($qry);
					if( $health_info->num_rows() > 0 ){
						//add to complied
						$nonofficer_complied[] = array(
							'employee_id' => $employee->user_id,
							'name' => $employee->firstname .' '.$employee->lastname,
						);
					}
					else{
						//add to non-compliance
						$nonofficer_noncomplied[] = array(
							'employee_id' => $employee->user_id,
							'name' => $employee->firstname .' '.$employee->lastname,
						);
					}
				}
			}
			$cell[3] =  sizeof( $nonofficer_complied );; //complied
			$cell[4] =  $result->num_rows() > 0 ? sizeof( $nonofficer_complied ) / $result->num_rows() : '';; //percentage
			$cell[5] = '1'; //level
			$cell[6] = 'month-'.$key; //parent
			$cell[7] = $result->num_rows() > 0 ? false : true; //leaf
			$cell[8] = false; //expanded field
			$response->rows[$response->records]['cell'] = $cell;
			$response->records++;
			unset($cell);

			if(sizeof( $nonofficer_complied ) > 0){
				$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-nonofficer-complied';
				$cell[1] =  'Compliant';
				$cell[2] =  sizeof( $nonofficer_complied ); //target
				$cell[3] =  ''; //complied
				$cell[4] =  ''; //percentage
				$cell[5] = '2'; //level
				$cell[6] = 'month-'.$key.'-nonofficer'; //parent
				$cell[7] = false; //leaf
				$cell[8] = false; //expanded field
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				unset($cell);
				foreach( $nonofficer_complied as $employee ){
					$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-nonofficer-complied-'.$employee['employee_id'];
					$cell[1] =  $employee['name'];
					$cell[2] =  ''; //target
					$cell[3] =  ''; //complied
					$cell[4] =  ''; //percentage
					$cell[5] = '3'; //level
					$cell[6] = 'month-'.$key.'-nonofficer-complied'; //parent
					$cell[7] = true; //leaf
					$cell[8] = false; //expanded field
					$response->rows[$response->records]['cell'] = $cell;
					$response->records++;
					unset($cell);	
				}
			}

			if(sizeof( $nonofficer_noncomplied ) > 0){
				$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-nonofficer-noncomplied';
				$cell[1] =  'Non-compliant';
				$cell[2] =  sizeof( $nonofficer_noncomplied ); //target
				$cell[3] =  ''; //complied
				$cell[4] =  ''; //percentage
				$cell[5] = '2'; //level
				$cell[6] = 'month-'.$key.'-nonofficer'; //parent
				$cell[7] = false; //leaf
				$cell[8] = false; //expanded field
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				unset($cell);
				foreach( $nonofficer_noncomplied as $employee ){
					$response->rows[$response->records]['id'] = $cell[0] = 'month-'.$key.'-nonofficer-noncomplied-'.$employee['employee_id'];
					$cell[1] =  $employee['name'];
					$cell[2] =  ''; //target
					$cell[3] =  ''; //complied
					$cell[4] =  ''; //percentage
					$cell[5] = '3'; //level
					$cell[6] = 'month-'.$key.'-nonofficer-noncomplied'; //parent
					$cell[7] = true; //leaf
					$cell[8] = false; //expanded field
					$response->rows[$response->records]['cell'] = $cell;
					$response->records++;
					unset($cell);	
				}
			}		
		endforeach;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function get_biometrics_report_filter(){
		$html = '';
		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1:
				$company = $this->db->get('user_company')->result_array();		
                $html .= '<select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2:
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 3:
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;		        
		    case 4:
				$employee = $this->db->get('user')->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	if ($employee_record["firstname"] != "Super Admin"){
                        	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    	}
                    }
                $html .= '</select>';	
		        break;		        		        
		}	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}
	

	function export() {	
		//campaign is company
		$query_id = '8';

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
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);


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
		$activeSheet->setCellValue('A5', 'Date');
		$activeSheet->setCellValue('B5', 'Company/Div/Dept/Branch');
		$activeSheet->setCellValue('C5', 'Movement');
		$activeSheet->setCellValue('D5', 'Employee');
		$activeSheet->setCellValue('E5', 'Position');
		$activeSheet->setCellValue('F5', 'Rank Code');
		$activeSheet->setCellValue('G5', 'Range of Rank');
		$activeSheet->setCellValue('H5', 'Changes');

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

		$activeSheet->setCellValue('A1', $code.' Movements For the period covering from '.date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));

		$line_ctr = 6;
		$name = "";

		foreach($query as $movement) {
			$changes = "";
			$current = "";
			$changes_data = array();
			$old_data = array();

			if($movement->compensation_effectivity_date	!= null)
				$activeSheet->setCellValue('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($movement->compensation_effectivity_date)));
			if($movement->movement_effectivity_date	!= null)
				$activeSheet->setCellValue('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($movement->movement_effectivity_date)));
			if($movement->transfer_effectivity_date	!= null)
				$activeSheet->setCellValue('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($movement->transfer_effectivity_date)));
			if($movement->last_day != null)
				$activeSheet->setCellValue('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($movement->last_day)));

			$activeSheet->setCellValue('B'.$line_ctr, $movement->show_dept);
			$activeSheet->setCellValue('C'.$line_ctr, $movement->movement_type);
			$name = $movement->firstname." ".$movement->middlename." ".$movement->lastname;
			$activeSheet->setCellValue('D'.$line_ctr, $name);

			$user_pos = $this->db->get_where('user_position', array('position_id' => $movement->position_id))->row();
				$activeSheet->setCellValue('E'.$line_ctr, $user_pos->position);

			$show_rank = $this->db->get_where('user_rank_code', array('job_rank_code_id' => $movement->show_rank))->row();
				$activeSheet->setCellValue('F'.$line_ctr, $show_rank->job_rank_code);

			$show_ror = $this->db->get_where('user_rank_range', array('job_rank_range_id' => $movement->show_ror))->row();
				$activeSheet->setCellValue('G'.$line_ctr, $show_ror->job_rank_range);


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
					$activeSheet->setCellValue('H'.$line_ctr, $format_change);
				}
			$line_ctr++;
		}

		foreach($hiring as $employed_date)
		{
			$activeSheet->setCellValue('A'.$line_ctr, date($this->config->item('display_date_format'), strtotime($employed_date->employed_date)));
			$activeSheet->setCellValue('B'.$line_ctr, $employed_date->department);
			$activeSheet->setCellValue('C'.$line_ctr, 'Hiring');
			$name = $employed_date->firstname." ".$employed_date->middlename." ".$employed_date->lastname;
			$activeSheet->setCellValue('D'.$line_ctr, $name);

			$user_pos = $this->db->get_where('user_position', array('position_id' => $employed_date->position_id))->row();
				$activeSheet->setCellValue('E'.$line_ctr, $user_pos->position);

			$show_rank = $this->db->get_where('user_rank_code', array('job_rank_code_id' => $employed_date->rank_code))->row();
				$activeSheet->setCellValue('F'.$line_ctr, $show_rank->job_rank_code);

			$show_ror = $this->db->get_where('user_rank_range', array('job_rank_range_id' => $employed_date->range_of_rank))->row();
				$activeSheet->setCellValue('G'.$line_ctr, $show_ror->job_rank_range);

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
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title($export->description) . '.xls');
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