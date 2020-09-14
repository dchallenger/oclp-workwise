<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import_manual extends CI_Controller
{
	function clean_up(){
		$this->db->where('user_id >',3);
		$this->db->delete('user');
		$this->db->query("ALTER TABLE hr_user AUTO_INCREMENT = 4");

		$this->db->truncate('employee');
		$this->db->truncate('employee_dtr_setup');
		$this->db->truncate('employee_union_master');
		$this->db->truncate('employee_union_master_detail');
		//$this->db->truncate('user_company');
		$this->db->truncate('user_position');
		//$this->db->truncate('user_location');
		$this->db->truncate('user_company_department');
		$this->db->truncate('user_rank');
		$this->db->truncate('user_rank_level');
		$this->db->truncate('user_company_division');
		$this->db->truncate('employee_cost_code');
		//$this->db->truncate('employment_status');
		//$this->db->truncate('timekeeping_shift');
		//$this->db->truncate('timekeeping_shift_calendar');
		$this->db->truncate('relationship');
		$this->db->truncate('employee_education');
		$this->db->truncate('employee_family');
		$this->db->truncate('employee_leaves');
		$this->db->truncate('employee_leaves_dates');
		$this->db->truncate('employee_leaves_el');
		$this->db->truncate('employee_leaves_funeral_initial_setup');
		$this->db->truncate('employee_leaves_maternity');
		$this->db->truncate('employee_leaves_paternity');
		$this->db->truncate('employee_leaves_paternity_initial_setup');
		$this->db->truncate('employee_leaves_plsp_initial_setup');
		$this->db->truncate('employee_movement');
		$this->db->truncate('employee_obt');
		$this->db->truncate('employee_obt_date');
		$this->db->truncate('employee_et');
		$this->db->truncate('employee_dtrp');
		$this->db->truncate('employee_dtr');
		$this->db->truncate('employee_dtr_raw');
		$this->db->truncate('employee_cws');
		$this->db->truncate('employee_cws_dates');
		$this->db->truncate('employee_oot');
		$this->db->truncate('employee_out');
		$this->db->truncate('employee_reporting_to');
		$this->db->truncate('employee_update');
		$this->db->truncate('employee_update_attachment');
		$this->db->truncate('employee_update_family');
		$this->db->truncate('employee_update_personal');
		$this->db->truncate('form_approver');
		$this->db->truncate('leave_approver');

		echo 'Done cleanup';
	}

	function import_all(){
		//$this->import_union_master();
		$this->import_company();
		$this->import_section();
		$this->import_position();
		$this->import_location();
		$this->import_department();
		$this->import_user_rank();
		//$this->import_user_rank_level();
		//$this->import_division();
		//$this->import_cost_code();
		//$this->import_employment_status();
		//$this->import_shift();
		//$this->import_shift_calendar();
		$this->import_employee();
		$this->import_contact_no();
		$this->import_personal_info();
		$this->import_id_no();
		//$this->import_relationship();
		$this->import_education();
		$this->import_family();
		$this->import_employment_history();
		$this->import_character_ref();
		echo 'Done Importing';
	}

	function import_approver(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\outsource\APPROVERS and SCHEDULE - for upload.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee Number':
								$valid_cells[] = 'employee_id';
								break;
							case 'Approver 1':
								$valid_cells[] = 'employee_approver1';
								break;								
							case 'Approver 2':
								$valid_cells[] = 'employee_approver2';
								break;																
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			$arr_field_val1 = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'employee_id':
						$result = $this->db->get_where('employee',array('biometric_id' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_user = $result->row();
							$row[$key] = $row_user->employee_id;
							$arr_field_val['employee_id'] = $row_user->employee_id;						
							$arr_field_val1['employee_id'] = $row_user->employee_id;
						}
						else{
							$row[$key] = '';
						}
						break;											
					case 'employee_approver1':
						$result = $this->db->get_where('employee',array('biometric_id' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_user = $result->row();
							$row[$key] = $row_user->employee_id;						
							$arr_field_val['approver_employee_id'] = $row_user->employee_id;
						}
						else{
							$row[$key] = '';
							$arr_field_val['approver_employee_id'] = '';
						}
						break;	
					case 'employee_approver2':
						$result = $this->db->get_where('employee',array('biometric_id' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_user = $result->row();
							$row[$key] = $row_user->employee_id;						
							$arr_field_val1['approver_employee_id'] = $row_user->employee_id;
						}
						else{
							$row[$key] = '';
							$arr_field_val1['approver_employee_id'] = '';
						}
						break;							
				}
			}

			if ($arr_field_val['employee_id'] != ''){
				$arr_field_val['module_id'] = 60;
				$arr_field_val['approver'] = 1;
				$arr_field_val['email'] = 1;
				$arr_field_val['condition'] = 1;
				$this->db->insert('employee_approver',$arr_field_val);
			}

			if ($arr_field_val1['employee_id'] != ''){
				$arr_field_val1['module_id'] = 60;
				$arr_field_val1['approver'] = 1;
				$arr_field_val1['email'] = 1;
				$arr_field_val1['condition'] = 1;

				//59,58,57,60

				$this->db->insert('employee_approver',$arr_field_val1);
			}
		}

		echo "Done.";	
	}

	function change_resign(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employeedataupdate\Resigned as of 8.21.15.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Date Left':
								$valid_cells[] = 'resigned_date';
								break;								
							case 'EmployeeId':
								$valid_cells[] = 'login';
								break;																																			
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		$afftected = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				if ($value == 'resigned_date'){
					$row[$key] = date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row[$key]));
				}				
				$arr_field_val[$value] = trim($row[$key]);
			}

			unset($arr_field_val['login']);

			$this->db->where('id_number',$row[1]);
			$this->db->update('employee',$arr_field_val);	
			$afftected += $this->db->affected_rows();
		}

		echo $afftected;	
	}

	function import_union_master(){
		$this->db->truncate('employee_union_master');
		$this->db->truncate('employee_union_master_detail');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\celine\employee data.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Union Name':
								$valid_cells[] = 'union_master';
								break;
							case 'Union Leave Credit':
								$valid_cells[] = 'union_leave_credit';
								break;
							case 'Union Leave Used':
								$valid_cells[] = 'union_leave_used';
								break;								
							case 'Union Leave Reset Date':
								$valid_cells[] = 'union_leave_reset_date';
								break;																
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			$arr_field_val_detail = array();
			foreach ($valid_cells as $key => $value) {
				if ($value == 'union_leave_reset_date'){
					$row[$key] = date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row[$key]));
				}

				$arr_field_val[$value] = $row[$key];
				$arr_field_val_detail[$value] = $row[$key];
				$arr_field_val_detail['year'] = date('Y');
			}

			unset($arr_field_val['union_leave_used']);
			unset($arr_field_val_detail['union_master']);

			$this->db->insert('employee_union_master',$arr_field_val);

			$arr_field_val_detail['union_master_id'] = $this->db->insert_id();

			$this->db->insert('employee_union_master_detail',$arr_field_val_detail);
		}

		echo "Done.";	
	}

	function import_section(){
		$this->db->truncate('user_section');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\celine\employee data.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Section Name':
								$valid_cells[] = 'Section';
								break;
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {			
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_section',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_area(){
		$this->db->truncate('user_area');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\celine\employee data.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Area Name':
								$valid_cells[] = 'area';
								break;								
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {			
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_area',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_generic_position(){
		$this->db->truncate('user_area');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\generic positions.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Generic Position':
								$valid_cells[] = 'position';
								break;								
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {			
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_generic_position',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_cost_code(){
		$this->db->truncate('employee_cost_code');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\rfm\RFMEmployeeDataforuploading\Employee Data (RFM01-Employee-RFv6).xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Cost Code ID':
								$valid_cells[] = 'cost_code';
								break;
							case 'Long Description':
								$valid_cells[] = 'cost_code_description';
								break;									
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {			
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('employee_cost_code',$arr_field_val);
		}

		echo "Done.";	
	}

	// above will not used //

	function import_company(){
		$this->db->truncate('user_company');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Company':
								$valid_cells[] = 'company';
								break;
							case 'Code':
								$valid_cells[] = 'company_code';
								break;
							case 'Address':
								$valid_cells[] = 'address';
								break;								
							case 'Zipcode':
								$valid_cells[] = 'zipcode';
								break;
							case 'Telephone':
								$valid_cells[] = 'telephone';
								break;
							case 'Mobile':
								$valid_cells[] = 'mobile';
								break;
							case 'Fax No':
								$valid_cells[] = 'fax_no';
								break;	
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				if ($value == 'employer_number'){
					$row[$key] = '';
				}
				$arr_field_val[$value] = $row[$key];
			}
			$this->db->insert('user_company',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_position(){
		$this->db->truncate('user_position');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Company':
								$valid_cells[] = 'company_id';
								break;	
							case 'Department':
								$valid_cells[] = 'department_id';
								break;															
							case 'Position':
								$valid_cells[] = 'position';
								break;
							case 'Code':
								$valid_cells[] = 'position_code';
								break;
							case 'Description':
								$valid_cells[] = 'description';
								break;															
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'company_id':
						$result = $this->db->get_where('user_company',array('company' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_company = $result->row();
							$row[$key] = $row_company->company_id;						
						}
						else{
							$row[$key] = '';
						}
						break;
					case 'department_id':
						$result = $this->db->get_where('user_company_department',array('department_id' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_department = $result->row();
							$row[$key] = $row_department->department_id;						
						}
						else{
							$row[$key] = '';
						}
						break;											
					case 'position_code':
						$row[$key] = '';
						break;				
				}			
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_position',$arr_field_val);

		}

		echo "Done.";	
	}	

	function import_location(){
		$this->db->truncate('user_location');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Location/Branch':
								$valid_cells[] = 'location';
								break;
							case 'Region':
								$valid_cells[] = 'region_id';
								break;							
							case 'Province':
								$valid_cells[] = 'province_id';
								break;
							case 'City':
								$valid_cells[] = 'city_id';
								break;
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {	
				switch ($value) {
					case 'province_id':
						$result = $this->db->get_where('province',array('province' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->province_id;						
						}
						else{
							$row[$key] = '';
						}
						break;
					case 'city_id':
						$result = $this->db->get_where('cities',array('city' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->city_id;						
						}
						else{
							$row[$key] = '';
						}
						break;	
				}					
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_location',$arr_field_val);

		}

		echo "Done.";	
	}

	function import_department(){
		$this->db->truncate('user_company_department');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Cost Code':
								$valid_cells[] = 'department_code';
								break;
							case 'Division':
								$valid_cells[] = 'division_id';
								break;								
							case 'Department Name':
								$valid_cells[] = 'department';
								break;							
							case 'Description':
								$valid_cells[] = 'description';
								break;								
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {	
				switch ($value) {			
					case 'company_id':
						$result = $this->db->get_where('user_company',array('company' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_company = $result->row();
							$row[$key] = $row_company->company_id;						
						}
						else{
							$row[$key] = '';
						}
						break;
					case 'division_id':
						$result = $this->db->get_where('user_company_division',array('division' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_division = $result->row();
							$row[$key] = $row_division->division_id;						
						}
						else{
							$row[$key] = '';
						}
						break;						
				}						
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_company_department',$arr_field_val);

		}

		echo "Done.";	
	}	

	function import_user_rank(){
		$this->db->truncate('user_rank');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Rank Short Code':
								$valid_cells[] = 'job_rank_short_code';	
								break;								
							case 'Rank':
								$valid_cells[] = 'job_rank';
								break;												
							case 'Rank Index':
								$valid_cells[] = 'rank_index';
								break;	
							case 'Level':
								$valid_cells[] = 'employee_type';
								break;
							case 'Description':
								$valid_cells[] = 'description';								
								break;														
/*							case 'Company':
								$valid_cells[] = 'company_id';
								break;*/								
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				if ($value == 'employee_type'){					
					$result = $this->db->get_where('employee_type',array('employee_type' => $row[$key]));
					if ($result && $result->num_rows() > 0){
						$row_company = $result->row();
						$row[$key] = $row_company->employee_type_id;						
					}
					else{
						$row[$key] = '';
					}
				}				
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_rank',$arr_field_val);

		}

		echo "Done.";	
	}	

	function import_user_rank_level(){
		$this->db->truncate('user_rank_level');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Level':
								$valid_cells[] = 'rank_level';
								break;
							case 'Description':
								$valid_cells[] = 'description';
								break;									
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {			
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_rank_level',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_division(){
		$this->db->truncate('user_company_division');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Company':
								$valid_cells[] = 'company_id';	
								break;						
							case 'Division Name':
								$valid_cells[] = 'division';
								break;							
							case 'Description':
								$valid_cells[] = 'description';
								break;								
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {	
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {	
				switch ($value) {
					case 'company_id':
						$result = $this->db->get_where('user_company',array('company' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_company = $result->row();
							$row[$key] = $row_company->company_id;						
						}
						else{
							$row[$key] = '';
						}
						break;			
				}					
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('user_company_division',$arr_field_val);

		}

		echo "Done.";	
	}

	function import_employment_status(){
		$this->db->truncate('employment_status');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employment Status':
								$valid_cells[] = 'employment_status';
								break;
							case 'Description':
								$valid_cells[] = 'description';
								break;									
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {			
				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('employment_status',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_shift(){
		$this->db->where('shift_id >',1);
		$this->db->delete('timekeeping_shift');
		$this->db->query("ALTER TABLE hr_timekeeping_shift AUTO_INCREMENT = 2");
		// $this->db->truncate('timekeeping_shift');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Shift Name':
								$valid_cells[] = 'shift';
								break;								
							case 'Shift Start':
								$valid_cells[] = 'shifttime_start';
								break;	
							case 'Shift End':
								$valid_cells[] = 'shifttime_end';
								break;
							case 'Noon Break Start':
								$valid_cells[] = 'noon_start';
								break;
							case 'Noon Break End':
								$valid_cells[] = 'noon_end';
								break;
							case 'Break A Start':
								$valid_cells[] = 'breaka_start';
								break;
							case 'Break A End':
								$valid_cells[] = 'breaka_end';
								break;
							case 'Break B Start':
								$valid_cells[] = 'breakb_start';
								break;
							case 'Break B End':
								$valid_cells[] = 'breakb_end';
								break;
							case 'Grace Period (minutes)':
								$valid_cells[] = 'shift_grace_period';
								break;
							case 'Halfday (start of halfday)':
								$valid_cells[] = 'halfday';
								break;
							case 'Pre-Shift OT':
								$valid_cells[] = 'max_preshift_ot';
								break;	
							case 'Post-Shift OT':
								$valid_cells[] = 'max_postshift_ot';
								break;															
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				if ($value == 'shifttime_start' || $value == 'shifttime_end' || $value == 'noon_start' || $value == 'noon_end' || $value == 'breaka_start' || $value == 'breaka_end' || $value == 'breakb_start' || $value == 'breakb_end' || $value == 'shift_grace_period' || $value == 'halfday' || $value == 'max_preshift_ot' || $value == 'max_postshift_ot'){
					$row[$key] = PHPExcel_Style_NumberFormat::toFormattedString($row[$key], 'H:i');
					if ($row[$key] == NULL){
						$row[$key] = '';
					}
				}

				$arr_field_val[$value] = $row[$key];
			}

			$this->db->insert('timekeeping_shift',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_shift_calendar(){
		$this->db->truncate('timekeeping_shift_calendar');

		$result = $this->db->get_where('timekeeping_shift');
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				/*$arr_fields['company_id'] =  $row->company_id;*/
				$arr_fields['shift_calendar'] =  $row->shift;
				$arr_fields['sunday_shift_id'] =  1;
				$arr_fields['monday_shift_id'] =  $row->shift_id;
				$arr_fields['tuesday_shift_id'] =  $row->shift_id;
				$arr_fields['wednesday_shift_id'] =  $row->shift_id;
				$arr_fields['thursday_shift_id'] =  $row->shift_id;
				$arr_fields['friday_shift_id'] =  $row->shift_id;
				$arr_fields['saturday_shift_id'] =  $row->shift_id;

				$this->db->insert('timekeeping_shift_calendar',$arr_fields);
			}
		}
	}

	function import_employee(){
/*		$this->db->where('employee_id >',3);
		$this->db->delete('user');

		$this->db->truncate('employee');
		$this->db->truncate('employee_dtr_setup');*/

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\outsource\ADDITIONAL EMPLOYEES for upload - 7.25.18.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No Agency':
								$valid_cells_user[] = 'id_no_agency';
								break;							
							case 'Employee No':
								$valid_cells_user[] = 'login';
								break;							
							case 'Last Name':
								$valid_cells_user[] = 'lastname';
								break;								
							case 'First Name':
								$valid_cells_user[] = 'firstname';
								break;	
							case 'Salutation':
								$valid_cells_user[] = 'salutation';
								break;
							case 'Middle Name':
								$valid_cells_user[] = 'middlename';
								break;
							case 'Middle Initial':
								$valid_cells_user[] = 'middleinitial';
								break;
							case 'Aux':
								$valid_cells_user[] = 'aux';
								break;	
							case 'Maiden Name':
								$valid_cells_user[] = 'maidenname';
								break;															
							case 'Nickname':
								$valid_cells_user[] = 'nickname';
								break;
							case 'HRIS Role':
								$valid_cells_user[] = 'role_id';
								break;									
							case 'Company Name':
								$valid_cells_user[] = 'company_id';
								break;	
							case 'Estate':
								$valid_cells_user[] = 'estate_id';
								break;	
							case 'SBU':
								$valid_cells_user[] = 'sbu_id';
								break;
							case 'Cost Center Name':
								$valid_cells_user[] = 'cost_center_id';
								break;	
							case 'Building':
								$valid_cells_user[] = 'building_id';
								break;	
							case 'Location':
								$valid_cells_employee[16] = 'location_id';
								break;
							case 'Agency':
								$valid_cells_user[17] = 'agency_id';
								break;	
							case 'Service Class':
								$valid_cells_user[] = 'service_class_id';
								break;	
							case 'Service Type':
								$valid_cells_user[] = 'service_type_id';
								break;	
							case 'Service Position':
								$valid_cells_user[] = 'service_position_id';
								break;	
							case 'Work Schedule':
								$valid_cells_dtr_setup[21] = 'shift_calendar_id';
								break;																
							case 'Biometrics  ID':
								$valid_cells_employee[22] = 'biometric_id';
								break;	
							case 'Date Hired':
								$valid_cells_employee[] = 'employed_date';
								break;
							case 'Date of Regularization':
								$valid_cells_employee[] = 'regular_date';
								break;	
							case 'Reports To':
								$valid_cells_employee[] = 'reporting_to';
								break;																						
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


/*		dbug($import_data[1]);
		dbug($valid_cells_user);
		dbug($valid_cells_dtr_setup);
		dbug($valid_cells_employee);
		die();*/

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells_user as $key => $value) {
				switch ($value) {
					case 'aux':
						if ($row[$key] == ''){
							$row[$key] = '';
						}
						break;					
					case 'role_id':
						$result = $this->db->get_where('role',array('role' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->role_id;						
						}
						else{
							$row[$key] = '';
						}
						break;		
					case 'company_id':
						$result = $this->db->get_where('user_company',array('company' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->company_id;						
						}
						else{
							$this->db->insert('user_company',array('company' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;
					case 'estate_id':
						$result = $this->db->get_where('user_estate',array('estate' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->estate_id;						
						}
						else{
							$this->db->insert('user_estate',array('estate' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;	
					case 'sbu_id':
						$result = $this->db->get_where('user_sbu',array('sbu' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->sbu_id;						
						}
						else{
							$this->db->insert('user_sbu',array('sbu' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;	
					case 'cost_center_id':
						$result = $this->db->get_where('user_cost_center',array('cost_center_name' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->cost_center_name_id;						
						}
						else{
							$this->db->insert('user_cost_center',array('cost_center_name' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;	
					case 'building_id':
						$result = $this->db->get_where('user_building',array('building' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->building_id;						
						}
						else{
							$this->db->insert('user_building',array('building' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;	
					case 'agency_id':
						$result = $this->db->get_where('user_agency',array('agency' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->agency_id;						
						}
						else{
							$this->db->insert('user_agency',array('agency' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;	
					case 'service_class_id':
						$result = $this->db->get_where('user_service_class',array('service_class' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->service_class_id;						
						}
						else{
							$this->db->insert('user_service_class',array('service_class' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;
					case 'service_type_id':
						$result = $this->db->get_where('user_service_type',array('service_type' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->service_type_id;						
						}
						else{
							$this->db->insert('user_service_type',array('service_type' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;
					case 'service_position_id':
						$result = $this->db->get_where('user_service_position',array('service_position' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->service_position_id;						
						}
						else{
							$this->db->insert('user_service_position',array('service_position' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;
					default:
						if ($row[$key] == ''){
							$row[$key] = '';
						}
						break;												
				}
				$arr_field_val[$value] = $row[$key];
			}

			$arr_field_val['inactive'] = 0;

			$this->db->insert('user',$arr_field_val);
			$user_id = $this->db->insert_id();	

			$this->db->where('user_id',$user_id);
			$this->db->update('user',array('employee_id' => $user_id));

			/********************************************************************/
			$arr_field_val = array();
			foreach ($valid_cells_dtr_setup as $key => $value) {
				switch ($value) {
					case 'shift_calendar_id':
						$result = $this->db->get_where('timekeeping_shift_calendar',array('shift_calendar' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->shift_calendar_id;						
						}
						else{
							$row[$key] = '';
						}
						break;											
				}
				$arr_field_val[$value] = $row[$key];
			}

			$arr_field_val['employee_id'] = $user_id;
			$this->db->insert('employee_dtr_setup',$arr_field_val);		
			/********************************************************************/

			$arr_field_val = array();
			foreach ($valid_cells_employee as $key => $value) {
				switch ($value) {
					case 'location_id':
						$result = $this->db->get_where('user_location',array('location' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->location_id;						
						}
						else{
							$this->db->insert('user_location',array('location' => $row[$key]));
							$insert_id = $this->db->insert_id();
							$row[$key] = $insert_id;
						}
						break;	
					case 'employed_date':
					case 'regular_date':
						if ($row[$key] != ''){
							$row[$key] = date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row[$key]));
						}
						else{
							$row[$key] = '';
						}
						break;
					case 'reporting_to':
						$reports_to = $row[$key];
						$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}user WHERE CONCAT(lastname,' ',aux, ', ',firstname, ' ', middleinitial) = '{$reports_to}'");
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->user_id;						
						}
						else{
							$row[$key] = '';
						}
						break;										
				}
				$arr_field_val[$value] = $row[$key];
				$arr_field_val['id_number'] = $row[1];
			}	

			$arr_field_val['user_id'] = $user_id;
			$arr_field_val['employee_id'] = $user_id;
			$this->db->insert('employee',$arr_field_val);							
		}

		echo "Done.";	
	}

	function import_contact_no(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\outsource\for upload new\Strikeforce_approvers.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;
							case 'Home Phone':
								$valid_cells[] = 'home_phone';
								break;								
							case 'Mobile':
								$valid_cells[] = 'mobile';
								break;	
							case 'Email':
								$valid_cells[] = 'email';
								break;
							case 'Office Phone':
								$valid_cells[] = 'office_phone';
								break;
							case 'UnitNo. / Bldg. Name / House No. Street':
								$valid_cells[] = 'pres_address1';
								break;
							case 'Subdivision / Village / Barangay':
								$valid_cells[] = 'pres_address2';
								break;
							case 'City/Municipality, Provinces':
								$valid_cells[] = 'pres_city';
								break;
							case 'Province':
								$valid_cells[] = 'perm_province_id';
								break;																							
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'perm_province_id':
						$result = $this->db->get_where('province',array('province' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->province_id;						
						}
						else{
							$row[$key] = '';
						}
						break;
					case 'pres_city':
						$this->db->select('city_id,CONCAT(city,", ",province) as city',false);
						$this->db->join('province ','cities.province_id = province.province_id','left');
						$this->db->where('CONCAT(city,", ",province) = ',$row[$key]);
						$result = $this->db->get('cities');

						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->city_id;						
						}
						else{
							$row[$key] = '';
						}
						break;						
				}

				$arr_field_val[$value] = $row[$key];
			}

			$this->db->where('login',$row[0]);
			$this->db->update('user',array('email' => $arr_field_val['email']));

			unset($arr_field_val['login']);
			unset($arr_field_val['email']);

			$this->db->where('id_number',$row[0]);
			$this->db->update('employee',$arr_field_val);
		}

		echo "Done.";	
	}	

	function import_personal_info(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\outsource\for upload new\Strikeforce_approvers.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 3) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;
							case 'Date of Birth':
								$valid_cells[] = 'birth_date';
								break;	
							case 'Place of Birth':
								$valid_cells[] = 'birth_place';
								break;	
							case 'Religion':
								$valid_cells[] = 'religion_id';
								break;																															
							case 'Gender':
								$valid_cells[] = 'sex';
								break;	
							case 'Nationality':
								$valid_cells[] = 'citizenship';
								break;								
							case 'Civil Status':
								$valid_cells[] = 'civil_status_id';
								break;
							case 'Tax Status':
								$valid_cells[] = 'tax_status';
								break;	
							case 'Height':
								$valid_cells[] = 'height';
								break;
							case 'Weight':
								$valid_cells[] = 'weight';
								break;
							case 'Blood Type':
								$valid_cells[] = 'blood_type';
								break;																																				
						}
					}
				}

				unset($import_data[0]);
				//unset($import_data[1]);
				//unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'birth_date':
						$row[$key] = date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row[$key]));
						break;
					case 'religion_id':
						$result = $this->db->get_where('religion',array('religion' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->religion_id;						
						}
						else{
							$row[$key] = '';
						}
						break;						
					case 'civil_status_id':
						$result = $this->db->get_where('civil_status',array('civil_status' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->civil_status_id;						
						}
						else{
							$row[$key] = '';
						}
						break;
					case 'tax_status':
						$result = $this->db->get_where('taxcode',array('taxcode' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_role = $result->row();
							$row[$key] = $row_role->taxcode_id;						
						}
						else{
							$row[$key] = '';
						}
						break;
				}

				$arr_field_val[$value] = trim($row[$key]);
			}

			$this->db->where('login',$row[0]);
			$this->db->update('user',array('birth_date' => $arr_field_val['birth_date'],'sex' => strtolower($arr_field_val['sex'])));

			unset($arr_field_val['login']);
			unset($arr_field_val['birth_date']);
			unset($arr_field_val['sex']);

			$this->db->where('id_number',$row[0]);
			$this->db->update('employee',$arr_field_val);

		}

		echo "Done.";	
	}	

	function import_emergency_contact(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\outsource\dtr for upload\Alcatraz -  ADB Time system.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {
				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;
							case 'Name':
								$valid_cells[] = 'emergency_name';
								break;	
							case 'Relationship':
								$valid_cells[] = 'relationship_id';
								break;	
							case 'Address':
								$valid_cells[] = 'emergency_address';
								break;																															
							case 'Phone No.':
								$valid_cells[] = 'emergency_phone';
								break;																																				
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'relationship_id':
						$result = $this->db->get_where('relationship',array('relationship' => $row[$key]));
						if ($result && $result->num_rows() > 0){
							$row_relationship = $result->row();
							$row[$key] = $row_relationship->relationship_id;						
						}
						else{
							$row[$key] = '';
						}
						break;						
				}

				$arr_field_val[$value] = trim($row[$key]);
			}

			unset($arr_field_val['login']);

			$this->db->where('id_number',$row[0]);
			$this->db->update('employee',$arr_field_val);
		}

		echo "Done.";	
	}	

	function import_id_no(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\outsource\dtr for upload\Alcatraz -  ADB Time system.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;
							case 'SSS':
								$valid_cells[] = 'sss';
								break;								
							case 'Pag-ibig':
								$valid_cells[] = 'pagibig';
								break;	
							case 'TIN':
								$valid_cells[] = 'tin';
								break;
							case 'With BIR Form 2316':
								$valid_cells[] = 'tin_with_bir';
								break;								
							case 'Philhealth':
								$valid_cells[] = 'philhealth';
								break;
							case 'Bank Name':
								$valid_cells[] = 'bank_account_name';
								break;
							case 'Bank Account No.':
								$valid_cells[] = 'bank_account_no';
								break;																
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'tin_with_bir':
						if ($row[$key] == 'Yes'){
							$row[$key] = 1;
						}
						else{
							$row[$key] = 0;
						}
						break;												
				}

				$arr_field_val[$value] = $row[$key];
			}

			unset($arr_field_val['login']);

			$this->db->where('id_number',$row[0]);
			$this->db->update('employee',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_reporting_to(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {
				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$id_number = $row[0];
			$reporting_to_employee = $row[1];

			$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}user WHERE CONCAT(lastname,' ',aux, ', ',firstname, ' ', middleinitial,'.') = '{$reporting_to_employee}'");

			if ($result && $result->num_rows() > 0){
				$row_role = $result->row();
				$reporting_to = $row_role->user_id;						

				$this->db->where('id_number',$id_number);
				$this->db->update('employee',array('reporting_to' => $reporting_to));
			}					
		}

		echo "Done.";	
	}	

	function import_relationship(){
		$this->db->truncate('education_school');

		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Relationship Description':
								$valid_cells[] = 'relationship';
								break;
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				$arr_field_val[$value] = $row[$key];
			}
			
			$this->db->insert('relationship',$arr_field_val);
		}

		echo "Done.";	
	}

	function import_education(){
		//$this->db->truncate('education_school');
		
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;							
							case 'Educational Attainment':
								$valid_cells[] = 'education_level';
								break;
							case 'Graduate / Undergraduate':
								$valid_cells[] = 'graduate';
								break;								
							case 'School':
								$valid_cells[] = 'education_school_id';
								break;
							case 'Degree Obtained':
								$valid_cells[] = 'employee_degree_obtained_id';
								break;	
							case 'From':
								$valid_cells[] = 'date_from';
								break;
							case 'To':
								$valid_cells[] = 'date_to';
								break;
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'education_level':
						switch ($row[$key]) {
							case 'College':
								$row[$key] = 10;
								break;
							case 'High School':
								$row[$key] = 9;
								break;
							case 'Vocational':
								$row[$key] = 12;
								break;	
							case 'Graduate Studies':
								$row[$key] = 11;
								break;
							case 'Elementary':
								$row[$key] = 8;
								break;
						}
						break;	
					case 'graduate':
						switch ($row[$key]) {
							case 'Graduate':
								$row[$key] = 1;
								break;
							case 'Undergraduate':
								$row[$key] = 0;
								break;															
						}
						break;	
					case 'education_school_id':
						if ($row[$key] != ''){
							$this->db->where('education_school',$row[$key]);
							$result = $this->db->get('education_school');
							if ($result && $result->num_rows() > 0){
								$row_education = $result->row();
								$education_school_id = $row_education->education_school_id;
							}
							else{
								$this->db->insert('education_school',array('education_school' => $row[$key]));
								$education_school_id = $this->db->insert_id;
							}
							$row[$key] = $education_school_id;
						}
						break;	
					case 'employee_degree_obtained_id':
						if ($row[$key] != ''){
							$this->db->where('employee_degree_obtained',$row[$key]);
							$result = $this->db->get('employee_degree_obtained');
							if ($result && $result->num_rows() > 0){
								$row_degree = $result->row();
								$degree_obtained_id = $row_degree->employee_degree_obtained_id;
							}
							else{
								$this->db->insert('employee_degree_obtained',array('employee_degree_obtained' => $row[$key]));
								$degree_obtained_id = $this->db->insert_id;
							}
							$row[$key] = $degree_obtained_id;
						}
						break;
				}					
				$arr_field_val[$value] = $row[$key];
			}

			unset($arr_field_val['login']);

			$this->db->where('login',$row[0]);
			$result = $this->db->get('user');

			if ($result && $result->num_rows() > 0){
				$row_user = $result->row();

				$arr_field_val['employee_id'] = $row_user->employee_id;

				$this->db->insert('employee_education',$arr_field_val);	
			}
		}

		echo "Done.";	
	}	

	function import_family(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;							
							case 'Name(Family Member)':
								$valid_cells[] = 'name';
								break;
							case 'Relationship':
								$valid_cells[] = 'relationship';
								break;
							case 'Date of Birth':
								$valid_cells[] = 'birth_date';
								break;																
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'birth_date':
						$row[$key] = date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row[$key]));
						break;																		
				}				
				$arr_field_val[$value] = $row[$key];
			}

			unset($arr_field_val['login']);

			$this->db->where('login',$row[0]);
			$result = $this->db->get('user');

			if ($result && $result->num_rows() > 0){
				$row_user = $result->row();
				$arr_field_val['employee_id'] = $row_user->employee_id;

				$this->db->insert('employee_family',$arr_field_val);	
			}
		}

		echo "Done.";	
	}																

	function import_employment_history(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 2) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;							
							case 'Company':
								$valid_cells[] = 'company';
								break;
							case 'Address':
								$valid_cells[] = 'address';
								break;
							case 'Concat No':
								$valid_cells[] = 'contact_no';
								break;							
							case 'Nature of Business':
								$valid_cells[] = 'nature_of_business';
								break;	
							case 'Position':
								$valid_cells[] = 'position';
								break;
							case 'From':
								$valid_cells[] = 'from_date';
								break;
							case 'To':
								$valid_cells[] = 'to_date';
								break;								
							case 'Reason for Leaving':
								$valid_cells[] = 'reason_for_leaving';
								break;
							case 'Duties':
								$valid_cells[] = 'duties';
								break;
							case 'Last Salary':
								$valid_cells[] = 'last_salary';
								break;								
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {	
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {
				switch ($value) {
					case 'from_date':
					case 'to_date':
						$row[$key] = date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row[$key]));
						break;																		
				}				
				$arr_field_val[$value] = $row[$key];
			}

			unset($arr_field_val['login']);

			$this->db->where('login',$row[0]);
			$result = $this->db->get('user');

			if ($result && $result->num_rows() > 0){
				$row_user = $result->row();
				$arr_field_val['employee_id'] = $row_user->employee_id;

				$this->db->insert('employee_employment',$arr_field_val);	
			}
		}

		echo "Done.";	
	}	

	function import_character_ref(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 2) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;							
							case 'Name':
								$valid_cells[] = 'name';
								break;
							case 'Address':
								$valid_cells[] = 'address';
								break;
							case 'Company Name':
								$valid_cells[] = 'company_name';
								break;	
							case 'Email Address':
								$valid_cells[] = 'email_address';
								break;
							case 'Telephone':
								$valid_cells[] = 'telephone';
								break;
							case 'Occupation':
								$valid_cells[] = 'occupation';
								break;								
							case 'Years Known':
								$valid_cells[] = 'years_known';
								break;
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {			
				$arr_field_val[$value] = $row[$key];
			}

			unset($arr_field_val['login']);

			$this->db->where('login',$row[0]);
			$result = $this->db->get('user');

			if ($result && $result->num_rows() > 0){
				$row_user = $result->row();
				$arr_field_val['employee_id'] = $row_user->employee_id;

				$this->db->insert('employee_references',$arr_field_val);	
			}
		}

		echo "Done.";	
	}

	function import_accountabilities(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\firefly\employee data-3.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee No':
								$valid_cells[] = 'login';
								break;							
							case 'Item':
								$valid_cells[] = 'equipment';
								break;
							case 'Tag Number':
								$valid_cells[] = 'tag_number';
								break;
							case 'Status':
								$valid_cells[] = 'status';
								break;	
							case 'Cost':
								$valid_cells[] = 'cost';
								break;
							case 'Date Issued':
								$valid_cells[] = 'date_issued';
								break;
							case 'Quantity':
								$valid_cells[] = 'quantity';
								break;								
							case 'Date Returned':
								$valid_cells[] = 'date_returned';
								break;
							case 'Remarks':
								$valid_cells[] = 'remarks';
								break;								
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}

		$ctr = 0;
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {	
				switch ($value) {
					case 'date_issued':
					case 'date_returned':
						$row[$key] = date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row[$key]));
						break;																		
				}						
				$arr_field_val[$value] = $row[$key];
			}

			unset($arr_field_val['login']);

			$this->db->where('login',$row[0]);
			$result = $this->db->get('user');

			if ($result && $result->num_rows() > 0){
				$row_user = $result->row();
				$arr_field_val['employee_id'] = $row_user->employee_id;

				$this->db->insert('employee_accountabilities',$arr_field_val);	
			}
		}

		echo "Done.";	
	}	

	function import_leave(){
		$this->load->library('PHPExcel');

		$objReader = new PHPExcel_Reader_Excel5;

		if (!$objReader) {
			show_error('Could not get reader.');
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load( 'D:\oclp\OLC employee_leave_balance_20200401-110400.xls' );
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
	
		$ctr = 0;	
		$import_data = array();

		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			
			$rowIndex = $row->getRowIndex();
			
			// Build the array to insert and check for validation errors as well.
			foreach ($cellIterator as $cell) {
				$import_data[$ctr][] = $cell->getCalculatedValue();
			}

			if ($rowIndex == 1) {

				foreach ($import_data as $row) {
					foreach ($row as $cell => $value) {
						switch ($value) {
							case 'Employee Number':
								$valid_cells[] = 'id_number';
								break;
							case 'vl':
								$valid_cells[] = 'vl';
								break;							
							case 'sl':
								$valid_cells[] = 'sl';
								break;															
						}
					}
				}

				unset($import_data[$ctr]);
			}

			$ctr++;
		}


		$ctr = 0;

		// Remove non-matching cells.
		foreach ($import_data as $row) {		
			$arr_field_val = array();
			foreach ($valid_cells as $key => $value) {	
				switch ($value) {
					case 'id_number':
						$result = $this->db->get_where('employee',array('id_number' => $row[$key]));
						$row_employee = $result->row();
						if ($result && $result->num_rows() > 0){
							$row_employee = $result->row();
							$user_id = $row_employee->user_id;	

							$this->db->where('employee_id',$user_id);
							$this->db->where('year',2020);
							$this->db->update('employee_leave_balance',array('vl' => $row[1],'sl' => $row[2]));
						}
				}					
			}
		}

		echo "Done.";	
	}

	function clean_up_ms() {
		$this->db->where('user_id >',3);
		$this->db->delete('user');
		$this->db->query("ALTER TABLE hr_user AUTO_INCREMENT = 4");

		$this->db->truncate('user_company_department');
		$this->db->where('position_id >',1);
		$this->db->delete('user_position');
		$this->db->query("ALTER TABLE hr_user_position AUTO_INCREMENT = 2");

		$this->db->where('shift_id >',1);
		$this->db->delete('timekeeping_shift');
		$this->db->query("ALTER TABLE hr_timekeeping_shift AUTO_INCREMENT = 2");

		$this->db->truncate('chat_personal');
		$this->db->truncate('comments');
		$this->db->truncate('employee_affiliates');
		$this->db->truncate('employee_alternate_contact');
		$this->db->truncate('employee_da');
		$this->db->truncate('employee_ir');
		$this->db->truncate('employee_ir_complainant');
		$this->db->truncate('employee_ir_involved');
		$this->db->truncate('employee_ir_witness');
		$this->db->truncate('employee_nte');
		$this->db->truncate('employee_update');
		$this->db->truncate('employee_update_attachment');
		$this->db->truncate('employee_update_family');
		$this->db->truncate('employee_update_personal');
		$this->db->truncate('file_upload');
		$this->db->truncate('password_reset_request');
		$this->db->truncate('workschedule_employee');
		$this->db->truncate('workschedule_group');
		$this->db->truncate('employee_clearance');
		$this->db->truncate('clearance_exit_interview_category');
		$this->db->truncate('clearance_exit_interview_item_score');
		$this->db->truncate('employee');
		$this->db->truncate('employee_clinic_records');
		$this->db->truncate('employee_comm_tree');
		$this->db->truncate('employee_exit_interview');
		$this->db->truncate('employee_approver_cbe');
		$this->db->truncate('employee_approver_petron');
		$this->db->truncate('employee_attachment');
		$this->db->truncate('employee_batch_salary');
		$this->db->truncate('employee_bu');
		$this->db->truncate('employee_department');
		$this->db->truncate('employee_division');
		$this->db->truncate('employee_ds');
		$this->db->truncate('employee_ecf');
		$this->db->truncate('employee_education');
		$this->db->truncate('employee_employment');
		$this->db->truncate('employee_family');
		$this->db->truncate('employee_family_bu');
		$this->db->truncate('employee_floating');
		$this->db->truncate('employee_health');
		$this->db->truncate('employee_insurance');
		$this->db->truncate('employee_movement');
		$this->db->truncate('employee_movement_benefit');
		$this->db->truncate('employee_no_call_show');
		$this->db->truncate('employee_online_request');
		$this->db->truncate('employee_otherinfo1');
		$this->db->truncate('employee_payroll');
		$this->db->truncate('employee_references');
		$this->db->truncate('employee_reporting_to');
		$this->db->truncate('employee_reporting_to_assignment');
		$this->db->truncate('employee_request_training');
		$this->db->truncate('employee_salary_adjustment');
		$this->db->truncate('employee_segment_1');
		$this->db->truncate('employee_segment_2');
		$this->db->truncate('employee_skill');
		$this->db->truncate('employee_skills');
		$this->db->truncate('employee_test_profile');
		$this->db->truncate('employee_uct');
		$this->db->truncate('employee_training');
		$this->db->truncate('employee_ufs_answer');
		$this->db->truncate('employee_ufs_main');
		$this->db->truncate('employee_uniform_order');
		$this->db->truncate('sanction_date');
		$this->db->truncate('user_company');
		$this->db->truncate('user_company_division');
		$this->db->truncate('annual_manpower_planning');
		$this->db->truncate('annual_manpower_planning_approver');
		$this->db->truncate('annual_manpower_planning_details');
		$this->db->truncate('annual_manpower_planning_evaluation_remarks');
		$this->db->truncate('annual_manpower_planning_position');
		$this->db->truncate('annual_manpower_planning_ranks');
		$this->db->truncate('employee_cfs_main');
		$this->db->truncate('employee_cfs_answer');
		$this->db->truncate('manpower_loading_schedule');
		$this->db->truncate('manpower_loading_schedule_approver');
		$this->db->truncate('manpower_loading_schedule_details');
		$this->db->truncate('appraisal_comment');
		$this->db->truncate('appraisal_competency');
		$this->db->truncate('appraisal_competency_level');
		$this->db->truncate('appraisal_email_reminder');
		$this->db->truncate('appraisal_employee_email_reminder');
		$this->db->truncate('appraisal_planning_comment');
		$this->db->truncate('appraisal_planning_period');
		$this->db->truncate('employee_appraisal');
		$this->db->truncate('employee_appraisal_approver');
		$this->db->truncate('employee_appraisal_bsc');
		$this->db->truncate('employee_appraisal_history');
		$this->db->truncate('employee_appraisal_period');
		$this->db->truncate('employee_appraisal_planning');
		$this->db->truncate('employee_appraisal_planning_reminders');
		$this->db->truncate('employee_appraisal_reminders');
		$this->db->truncate('employee_appraisal_template_company');
		$this->db->truncate('employee_appraisal_template_position');
		$this->db->truncate('dtr_daily_summary');
		$this->db->truncate('dtr_daily_summary_lf');
		$this->db->truncate('employee_dtr');
		$this->db->truncate('employee_dtrp');
		$this->db->truncate('employee_cws');
		$this->db->truncate('employee_et');
		$this->db->truncate('employee_et_blanket');
		$this->db->truncate('employee_leaves');
		$this->db->truncate('employee_leave_balance');
		$this->db->truncate('employee_leave_balance_monitoring_carry_over');
		$this->db->truncate('employee_leave_base_off');
		$this->db->truncate('employee_leave_blanket');
		$this->db->truncate('employee_leaves_dates');
		$this->db->truncate('employee_leaves_el');
		$this->db->truncate('employee_leaves_funeral_initial_setup');
		$this->db->truncate('employee_leaves_maternity');
		$this->db->truncate('employee_leaves_paternity');
		$this->db->truncate('employee_leaves_paternity_initial_setup');
		$this->db->truncate('employee_leaves_ul_initial_setup ');
		$this->db->truncate('employee_obt');
		$this->db->truncate('employee_obt_date');
		$this->db->truncate('employee_oot');
		$this->db->truncate('employee_oteol');
		$this->db->truncate('employee_out');
		$this->db->truncate('employee_out_blanket');
		$this->db->truncate('form_approver');
		$this->db->truncate('leave_approver');
		$this->db->truncate('leave_forfeiture');
		$this->db->truncate('timekeeping_uploads');
		$this->db->truncate('employee_cws_dates');
		$this->db->truncate('employee_dtr_ot');
		$this->db->truncate('employee_dtr_raw');
		$this->db->truncate('employee_dtr_setup');
		$this->db->truncate('employee_leaves_el');
		$this->db->truncate('employee_leaves_plsp_initial_setup');
		$this->db->truncate('tardy_employee');
		$this->db->truncate('timekeeping_period');
		$this->db->truncate('employee_cws_dates');
		$this->db->truncate('employee_clearance_form');
		$this->db->truncate('employee_clearance_form_checklist');
		$this->db->truncate('training_application');
		$this->db->truncate('training_approver');
		$this->db->truncate('training_balance');
		$this->db->truncate('training_bond_schedule');
		$this->db->truncate('training_calendar');
		$this->db->truncate('training_calendar_budget');
		$this->db->truncate('training_calendar_participant');
		$this->db->truncate('training_calendar_session');
		$this->db->truncate('training_email_settings');
		$this->db->truncate('training_employee_database');
		$this->db->truncate('training_evaluation');
		$this->db->truncate('training_evaluation_competence_score');
		$this->db->truncate('training_evaluation_subject_list');
		$this->db->truncate('training_feedback');
		$this->db->truncate('training_live');
		$this->db->truncate('training_plan');
		$this->db->truncate('recruitment_applicant');
		$this->db->truncate('recruitment_applicant_affiliates');
		$this->db->truncate('recruitment_applicant_education');
		$this->db->truncate('recruitment_applicant_employment');
		$this->db->truncate('recruitment_applicant_family');
		$this->db->truncate('recruitment_applicant_history');
		$this->db->truncate('recruitment_applicant_references');
		$this->db->truncate('recruitment_applicant_referral');
		$this->db->truncate('recruitment_applicant_skill');
		$this->db->truncate('recruitment_applicant_skills');
		$this->db->truncate('recruitment_applicant_test_profile');
		$this->db->truncate('recruitment_applicant_training');
		$this->db->truncate('recruitment_candidate_background_check');
		$this->db->truncate('recruitment_candidate_job_offer');
		$this->db->truncate('recruitment_candidates_appraisal');
		$this->db->truncate('recruitment_candidates_appraisal_comments');
		$this->db->truncate('recruitment_candidates_appraisal_exams');
		$this->db->truncate('recruitment_letter_of_intent');
		$this->db->truncate('recruitment_manpower');
		$this->db->truncate('recruitment_manpower_approver');
		$this->db->truncate('recruitment_manpower_candidate');
		$this->db->truncate('recruitment_manpower_candidate_interviewer');
		$this->db->truncate('recruitment_manpower_candidates_schedule');
		$this->db->truncate('recruitment_manpower_candidates_scheduler');
		$this->db->truncate('recruitment_manpower_settings');
		$this->db->truncate('recruitment_preemployment');
		$this->db->truncate('recruitment_preemployment_background');
		$this->db->truncate('recruitment_preemployment_buddy');
		$this->db->truncate('recruitment_preemployment_checklist');
		$this->db->truncate('recruitment_preemployment_onboarding');
		$this->db->truncate('recruitment_preemployment_orientation');
		$this->db->truncate('recruitment_preemployment_schoolverification');
		$this->db->truncate('employee_benefit');
		$this->db->truncate('employee_contribution');
		$this->db->truncate('employee_loan');
		$this->db->truncate('employee_loan_payment');
		$this->db->truncate('payrol_basic_ot_report');
		$this->db->truncate('payrol_batch_entry');
		$this->db->truncate('payrol_batch_entry_employee');
		$this->db->truncate('payrol_bonus');
		$this->db->truncate('payrol_bonus_accrual');
		$this->db->truncate('payrol_bonus_employee');
		$this->db->truncate('payrol_closed_summary');
		$this->db->truncate('payrol_closed_transactions');
		$this->db->truncate('payrol_current_transaction');
		$this->db->truncate('payrol_period');
		$this->db->truncate('payrol_recurring');
		$this->db->truncate('payrol_recurring_employee');
		$this->db->truncate('payrol_retro_pay');
		$this->db->truncate('timekeeping_period_summary');
		$this->db->truncate('cost_code');
		$this->db->truncate('department_code');
		$this->db->truncate('division_code');
		$this->db->truncate('downloadable_form');
		$this->db->truncate('email_queue');
		$this->db->truncate('employee_work_assignment');
		$this->db->truncate('payrol_salary_history');
		$this->db->truncate('project_name');
		$this->db->truncate('audit_log_trail');
		$this->db->truncate('calendar_event');
		$this->db->truncate('calendar_event_recipients');
		$this->db->truncate('cashout_leaves');
		$this->db->truncate('chat_personal');
		$this->db->truncate('comments');
		$this->db->truncate('memo');
		$this->db->truncate('memo_recipient');
		$this->db->truncate('memo_viewers');
		$this->db->truncate('safe_manhour');
		$this->db->truncate('sessions');
		$this->db->truncate('suggestion_box');
		$this->db->truncate('movement_approver');
		$this->db->truncate('employee_approver');
		echo "Done Clean Up!!!";
	}
}

/* End of file */
/* Location: system/application */