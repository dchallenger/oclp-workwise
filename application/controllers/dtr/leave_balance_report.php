<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Leave_balance_report extends MY_Controller
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
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'dtr/leave_balance_report/listview';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$data['department'] = $this->db->get('user_company_department')->result_array();
		$data['employment_status'] = $this->db->get('employment_status')->result_array();

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

		$this->db->where_in('application_code',array('ML','PL','SVL','ANL','CL','MTPL'));
		$form_type_result = $this->db->get('employee_form_type')->result();

		$leave_type = array();

		foreach( $form_type_result as $form_type_info ){
			$leave_type[$form_type_info->application_form_id] = $form_type_info->application_form;
		}

		$data['leave_type'] = $leave_type;

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

	function get_employee_time_record(){
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
		$this->_excel_export();
	}


	private function _excel_export($record_id = 0)
	{	

		$this->load->helper('time_upload');
		$search = 1;

		$fields = array(
			'Employee No.',
			'Firstname',
			'Lastname',
			'Type of Leave',
			'Previous Year Balance',
			'Starting Balance',
			'Used For',
			'Remaining Balance',
			'Converted Leave',
		);

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Leave Balance Report")
		            ->setDescription("Leave Balance Report");
		               
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

		$styleArrayNumber = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);


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

			if( $field == 'Used For' ){
				$activeSheet->setCellValueExplicit($xcoor . '6', $field, PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->mergeCells($xcoor . '6'.':'.$alphabet[$alpha_ctr+2] . '6');
				$alpha_ctr+=2;
			}
			else{
				$activeSheet->setCellValueExplicit($xcoor . '6', $field, PHPExcel_Cell_DataType::TYPE_STRING);
			}

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

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

			if( $field == 'Used For' ){
				$activeSheet->setCellValueExplicit($xcoor . '7', 'VL', PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit($alphabet[$alpha_ctr + 1] . '7', 'SL', PHPExcel_Cell_DataType::TYPE_STRING);
				$activeSheet->setCellValueExplicit($alphabet[$alpha_ctr + 2] . '7', 'EL', PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getStyle($xcoor . '7')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle($alphabet[$alpha_ctr + 1] . '7')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle($alphabet[$alpha_ctr + 2] . '7')->applyFromArray($styleArray);
			
				$alpha_ctr+=2;
			}
			else{
				$objPHPExcel->getActiveSheet()->mergeCells($xcoor . '6'.':'.$xcoor . '7');
			}

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '7')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValueExplicit('A2', 'Leave Balance Report', PHPExcel_Cell_DataType::TYPE_STRING); 


		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			
			$activeSheet->setCellValueExplicit('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))), PHPExcel_Cell_DataType::TYPE_STRING); 
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);


		// contents.
		$line = 8;

		$this->db->from($this->db->dbprefix('user'));
		$this->db->join($this->db->dbprefix('employee_leave_balance'),$this->db->dbprefix('employee_leave_balance').'.employee_id = '.$this->db->dbprefix('user').'.employee_id','left');
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id','left');
		$this->db->where($this->db->dbprefix('employee').'.resigned = 0');
		$this->db->where($this->db->dbprefix('employee_leave_balance').'.year = "'.date('Y').'"');
		$this->db->where($this->db->dbprefix('user').'.deleted = 0 AND '.$search);

		switch ($this->input->post('category')) {
			case 1:
					if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.company_id ',$this->input->post('company'));
				break;
			case 2:
					if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.division_id ',$this->input->post('division'));		
				break;
			case 3:
					if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.department_id ',$this->input->post('department'));
				break;
			case 4:
					if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ',$this->input->post('employee'));		
				break;												
		}

		if ($this->input->post('employment_status') && $this->input->post('employment_status') != 'null'){
			$this->db->where_in($this->db->dbprefix('employee').'.status_id ',$this->input->post('employment_status'));
		}

        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            $this->db->order_by($sidx . ' ' . $sord);
        }  
        else {
        	$this->db->order_by('user.firstname ASC');
        }         

		$q = $this->db->get();


		$query  = $q;

		$fields = array(
			'Employee No.',
			'Firstname',
			'Lastname',
			'Type of Leave',
			'Previous Year Balance',
			'Starting Balance',
			'Used For',
			'Remaining Balance',
			'Converted Leave'
		);

		foreach ($query->result() as $row) {

			$this->db->where_in('application_code',array('ML','PL','SVL','ANL','CL','MTPL'));
			$this->db->where_in('application_form_id',$this->input->post('leave_type'));
			$form_type_result = $this->db->get('employee_form_type')->result();

			foreach( $form_type_result as $form_type_info ){

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

					if( $field == 'Employee No.' ){
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->id_number);
					}
					elseif( $field == 'Firstname' ){
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->firstname);
					}
					elseif( $field == 'Lastname' ){
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->lastname);
					}
					elseif( $field == 'Type of Leave' ){
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $form_type_info->application_form);
					}
					elseif( $field == 'Starting Balance' ){
						if( $form_type_info->application_code == "MTPL" ){
							if( $row->{'vl'} == "" ){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 0);
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{'vl'});
							}
						}
						else{
							if( $row->{strtolower($form_type_info->application_code)} == "" ){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 0);
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{strtolower($form_type_info->application_code)});
							}
						}

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArrayNumber);
					}
					elseif( $field == 'Used For' ){
						if( $form_type_info->application_code == "MTPL" ){

							if( $row->{'vl_used'} == "" || $row->{'vl_used'} < 0 ){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 0);
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{'vl_used'});
							}

							if( $row->{'sl_used'} == "" || $row->{'sl_used'} < 0 ){
								$objPHPExcel->getActiveSheet()->setCellValue($alphabet[$alpha_ctr + 1] . $line, 0);
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($alphabet[$alpha_ctr + 1] . $line, $row->{'sl_used'});
							}

							if( $row->{'el_used'} == "" || $row->{'el_used'} < 0 ){
								$objPHPExcel->getActiveSheet()->setCellValue($alphabet[$alpha_ctr + 2] . $line, 0);
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($alphabet[$alpha_ctr + 2] . $line, $row->{'el_used'});
							}
								
						}

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArrayNumber);
						$objPHPExcel->getActiveSheet()->getStyle($alphabet[$alpha_ctr + 1] . $line)->applyFromArray($styleArrayNumber);
						$objPHPExcel->getActiveSheet()->getStyle($alphabet[$alpha_ctr + 2] . $line)->applyFromArray($styleArrayNumber);
					
						$alpha_ctr+=2;

					}
					elseif( $field == 'Remaining Balance' ){
						if( $form_type_info->application_code == "MTPL" ){
							$trb = $row->{'vl'} - ( $row->{'vl_used'} + $row->{'sl_used'} + $row->{'el_used'} );
							
							if( $trb <= 0 ){
								$trb = 0;
							}
							
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $trb);
						}
						else{
							$trb = $row->{strtolower($form_type_info->application_code)} - $row->{strtolower($form_type_info->application_code).'_used'};
							
							if( $trb <= 0 ){
								$trb = 0;
							}

							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $trb);
						}

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArrayNumber);
					}
					elseif( $field == 'Converted Leave' ){

						$this->db->join('payroll_leave_conversion_employee','payroll_leave_conversion_employee.leave_convert_id = payroll_leave_conversion.leave_convert_id','left');
						$this->db->join('employee_form_type','employee_form_type.application_form_id = payroll_leave_conversion.application_form_id','left');
						$this->db->where('payroll_leave_conversion_employee.employee_id',$row->employee_id);
						$this->db->where('payroll_leave_conversion.year',date('Y',strtotime($row->year)));
						$this->db->where('payroll_leave_conversion.application_form_id',$form_type_info->application_form_id);
						$this->db->order_by('payroll_leave_conversion.year','desc');
						$converted_leaves = $this->db->get('payroll_leave_conversion');

						if( $converted_leaves->num_rows() > 0 ){

							$converted_leaves_info = $converted_leaves->row();

							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $converted_leaves_info->amount);
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArrayNumber);

						}
						else{

							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 0);
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArrayNumber);

						}

					}
					elseif( $field == 'Previous Year Balance' ){

						if( $form_type_info->application_code == "MTPL" ){

							if( $row->{'carried_vl'} == '' || $row->{'carried_vl'} == 0 ){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 0);
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{'carried_vl'});
							}

						}
						else{

							if( !isset($row->{'carried_'.strtolower($form_type_info->application_code)}) || $row->{'carried_'.strtolower($form_type_info->application_code)} == '' || $row->{'carried_'.strtolower($form_type_info->application_code)} == 0 ){
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 0);
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{'carried_'.strtolower($form_type_info->application_code)});
							}

						}

						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleArrayNumber);
						
					}

					$alpha_ctr++;
				}

				$line++;

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
		header('Content-Disposition: attachment;filename='.date('Y-m-d').'_'.url_title("Leave Balance Report").'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}		

}

?>