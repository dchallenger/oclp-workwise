<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Percent_of_idp_accomplished extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Percent of IDP Accomplished';
		$this->listview_description = 'This module lists all defined percent of idp accomplished(s).';
		$this->jqgrid_title = "Percent of IDP Accomplished List";
		$this->detailview_title = 'Percent of IDP Accomplished Info';
		$this->detailview_description = 'This page shows detailed information about a particular percent of idp accomplished.';
		$this->editview_title = 'Percent of IDP Accomplished Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about percent of idp accomplished(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = chosen_script();
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';
		$data['content'] = 'training/percent_of_idp_accomplished/listview';
		$data['jqgrid'] = 'training/percent_of_idp_accomplished/jqgrid';
		
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

		$data['departments'] = $this->db->get_where('user_company_department', array('deleted' => 0))->result_array();
		$data['companies'] = $this->db->get_where('user_company', array('deleted' => 0))->result_array();
		$data['divisions'] = $this->db->get_where('user_company_division', array('deleted' => 0))->result_array();
		
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

	function listview()
	{
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        
		
        $this->db->select('user.employee_id, user.firstname, user.lastname, user.middleinitial, training_application.training_application_id, training_application.idp_completion, user_company_department.department_id,user_company_department.department, training_calendar.training_calendar_id');
        $this->db->join('user','user.employee_id = training_application.employee_id','left');
        $this->db->join('employee','employee.employee_id = user.employee_id','left');
        $this->db->join('user_company_department','user_company_department.department_id = user.department_id','left');
        $this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id','left');
        $this->db->join('training_calendar_participant','training_calendar_participant.training_application_id = training_application.training_application_id AND '.$this->db->dbprefix('training_calendar_participant').'.participant_status_id = 2','left');
        $this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
        $this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
        $this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
        $this->db->join('training_type','training_type.training_type_id = training_application.training_type','left');

        $company = implode(',', $this->input->post('company'));
        $division = implode(',', $this->input->post('division'));
	    $department = implode(',', $this->input->post('department'));
        $date_start = $this->input->post('date_start');
        $date_end = $this->input->post('date_end');

        if( $date_start != "" && $date_end != "" ){
        	$this->db->where('DATE_FORMAT('.$this->db->dbprefix('training_application').'.date_approved,"%Y-%m-%d") BETWEEN "'.date('Y-m-d',strtotime($date_start)).'" AND "'.date('Y-m-d',strtotime($date_end)).'"');
        }

        if( $company != "" ){
        	$this->db->where_in('user.company_id',explode(',', $company));
        }

        if( $division != "" ){
        	$this->db->where_in('user.division_id',explode(',', $division));
        }

        if( $department != "" ){
        	$this->db->where_in('user.department_id',explode(',', $department));
        }

        $this->db->where('user.deleted',0);
        $this->db->where('employee.resigned',0);
        $this->db->where('employee.resigned_date is null');
        $this->db->where('training_application.deleted',0);
        $this->db->where('training_application.status',5);
        $this->db->where('training_application.training_application_type',1);
        $this->db->order_by('user_company_division.division','asc');
	    $this->db->order_by('user_company_department.department','asc');
	    $this->db->order_by('user.lastname','asc');
	    $this->db->order_by('training_application.date_approved','asc');
        $result = $this->db->get('training_application');

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
			$total_num_rows = $result->num_rows();
	        $total_pages = $total_num_rows > 0 ? ceil($total_num_rows/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $total_num_rows;                        

	        $response->msg = "";

	        $this->db->select('user.employee_id, user.firstname, user.lastname, user.middleinitial, training_application.training_application_id, training_application.idp_completion, user_company_department.department_id,user_company_department.department, training_calendar.training_calendar_id');
	        $this->db->join('user','user.employee_id = training_application.employee_id','left');
	        $this->db->join('employee','employee.employee_id = user.employee_id','left');
	        $this->db->join('user_company_department','user_company_department.department_id = user.department_id','left');
	        $this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
	        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id','left');
	        $this->db->join('training_calendar_participant','training_calendar_participant.training_application_id = training_application.training_application_id AND '.$this->db->dbprefix('training_calendar_participant').'.participant_status_id = 2','left');
        	$this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
        	$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
        	$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
        	$this->db->join('training_type','training_type.training_type_id = training_application.training_type','left');

	        $company = implode(',', $this->input->post('company'));
	        $division = implode(',', $this->input->post('division'));
	    	$department = implode(',', $this->input->post('department'));
	        $date_start = $this->input->post('date_start');
	        $date_end = $this->input->post('date_end');

	        if( $date_start != "" && $date_end != "" ){
	        	$this->db->where('DATE_FORMAT('.$this->db->dbprefix('training_application').'.date_approved,"%Y-%m-%d") BETWEEN "'.date('Y-m-d',strtotime($date_start)).'" AND "'.date('Y-m-d',strtotime($date_end)).'"');
	        }

	        if( $company != "" ){
	        	$this->db->where_in('user.company_id',explode(',', $company));
	        }

	        if( $division != "" ){
	        	$this->db->where_in('user.division_id',explode(',', $division));
	        }

	        if( $department != "" ){
	        	$this->db->where_in('user.department_id',explode(',', $department));
	        }

	        $this->db->where('user.deleted',0);
	        $this->db->where('employee.resigned',0);
	        $this->db->where('employee.resigned_date is null');
	        $this->db->where('training_application.deleted',0);
	        $this->db->where('training_application.status',5);
	        $this->db->where('training_application.training_application_type',1);
	        $this->db->order_by('user_company_division.division','asc');
	        $this->db->order_by('user_company_department.department','asc');
	    	$this->db->order_by('user.lastname','asc');
	    	$this->db->order_by('training_application.date_approved','asc');
	        $result = $this->db->get('training_application');
	        $response->last_query = $this->db->last_query();

	        $ctr = 0;
	        $department_record = array();
	        $existing_employee = array();
	        $no_of_employee = array();


	        foreach( $result->result() as $row) {

	        	if( !( in_array($row->employee_id, $existing_employee) ) ){
	        		array_push($existing_employee, $row->employee_id);
	        		$no_of_employee[$row->department_id] += 1;	
	        	}

	        	$department_record[$row->department_id]['row_labels'] = $row->department;
	        	$department_record[$row->department_id]['idi'] += $row->idp_completion;

	        	if( $row->training_calendar_id > 0 ){
					$department_record[$row->department_id]['actual_accompished'] += $row->idp_completion;
				}
	        	
	        }


	        $current_department = 0;

	        foreach($result->result() as $row) {

	        	if( $current_department != $row->department_id ){

	        		$current_department = $row->department_id;

	        		$response->rows[$ctr]['cell'][0] = '<strong>'.$row->department.'</strong>';
					$response->rows[$ctr]['cell'][1] = $department_record[$row->department_id]['idi'];

					if( $department_record[$row->department_id]['actual_accompished'] > 0 ){
						$response->rows[$ctr]['cell'][2] = $department_record[$row->department_id]['actual_accompished'];
					}
					else{
						$response->rows[$ctr]['cell'][2] = 0;
					}

					$response->rows[$ctr]['cell'][3] = $no_of_employee[$row->department_id];
					$response->rows[$ctr]['cell'][4] = number_format(ceil( $response->rows[$ctr]['cell'][2] / $no_of_employee[$row->department_id]),2,'.','');

					$ctr++;

	        	}



	        	$response->rows[$ctr]['cell'][0] = $row->lastname.', '.$row->firstname.' '.$row->middleinitial;
				$response->rows[$ctr]['cell'][1] = $row->idp_completion;

				if( $row->training_calendar_id > 0 ){
					$response->rows[$ctr]['cell'][2] = $row->idp_completion;
				}
				else{
					$response->rows[$ctr]['cell'][2] = '';
				}

				$response->rows[$ctr]['cell'][3] = '';
				$response->rows[$ctr]['cell'][4] = '';

				$ctr++;
	        	
	        }
	
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {

		$this->listview_column_names = array('Row Labels','INDIVIDUAL DEVELOPMENT INITIATIVES','ACTUAL IDP ACCOMPLISHED','No of Employees','Departmental %');

		$this->listview_columns = array(
				array('name' => 'row_labels', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'individual_development_initiatives', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'actual_accomplished', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'no_of_employees', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'departmental_percent', 'width' => '180','align' => 'center', 'sortable' => 'false'),
			); 
                          
    }

     function export(){

    	$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Budget Utilization")
		            ->setDescription("Budget Utilization");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;


        $styleTitleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

        $styleTitleArray1 = array(
			'font' => array(
				'bold' => true,
			)
			// ,
			// 'fill' => array(
		 //            'type' => PHPExcel_Style_Fill::FILL_SOLID,
		 //            'color' => array('rgb' => 'C5D9F1')
		 //        ),
			
		);

        $styleHeaderArray = array(
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
		            'type' => PHPExcel_Style_Fill::FILL_SOLID,
		            'color' => array('rgb' => 'C5D9F1')
		        ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleDivisionArray = array(
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'DBE5F1')
	        )
		);

		$styleDivisionFieldArray = array(
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'DBE5F1')
	        )
		);

		$styleGrandTotalArray = array(
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
		            'type' => PHPExcel_Style_Fill::FILL_SOLID,
		            'color' => array('rgb' => 'C5D9F1')
		        ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleGrandTotalNumberArray = array(
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
		            'type' => PHPExcel_Style_Fill::FILL_SOLID,
		            'color' => array('rgb' => 'C5D9F1')
		        ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		);

		$styleGrandTotalPercentArray = array(
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
		            'type' => PHPExcel_Style_Fill::FILL_SOLID,
		            'color' => array('rgb' => 'C5D9F1')
		        ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);


        $activeSheet->setCellValue('A1', 'OCLP HOLDINGS, INC.');
		$activeSheet->setCellValue('A2', 'Percent IDP Accomplished');

		$date_start = $this->input->post('date_start');
        $date_end = $this->input->post('date_end');

        if( $date_start != "" && $date_end != "" ){
        	$activeSheet->setCellValue('A3', date('F d Y',strtotime($date_start)).' - '.date('F d Y',strtotime($date_end)));
        }

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleTitleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleTitleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleTitleArray);

		$objPHPExcel->getActiveSheet()->mergeCells('A1:AD1');
		$objPHPExcel->getActiveSheet()->mergeCells('A2:AD2');
		$objPHPExcel->getActiveSheet()->mergeCells('A3:AD3');

        $line = 5;

        $fields = array('Row Labels','INDIVIDUAL DEVELOPMENT INITIATIVES','ACTUAL IDP ACCOMPLISHED','No of Employees','Departmental %');

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

			$activeSheet->setCellValue($xcoor . $line, $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleHeaderArray);
			
			$alpha_ctr++;
		}


		$this->db->select('user.employee_id, user.firstname, user.lastname, user.middleinitial, training_application.training_application_id, training_application.idp_completion, user_company_department.department_id,user_company_department.department, training_calendar.training_calendar_id');
        $this->db->join('user','user.employee_id = training_application.employee_id','left');
        $this->db->join('employee','employee.employee_id = user.employee_id','left');
        $this->db->join('user_company_department','user_company_department.department_id = user.department_id','left');
        $this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id','left');
        $this->db->join('training_calendar_participant','training_calendar_participant.training_application_id = training_application.training_application_id AND '.$this->db->dbprefix('training_calendar_participant').'.participant_status_id = 2','left');
    	$this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
    	$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
    	$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
    	$this->db->join('training_type','training_type.training_type_id = training_application.training_type','left');

        $company = implode(',', $this->input->post('company'));
        $division = implode(',', $this->input->post('division'));
	    $department = implode(',', $this->input->post('department'));
        $date_start = $this->input->post('date_start');
        $date_end = $this->input->post('date_end');

        if( $date_start != "" && $date_end != "" ){
        	$this->db->where('DATE_FORMAT('.$this->db->dbprefix('training_application').'.date_approved,"%Y-%m-%d") BETWEEN "'.date('Y-m-d',strtotime($date_start)).'" AND "'.date('Y-m-d',strtotime($date_end)).'"');
        }

        if( $company != "" ){
        	$this->db->where_in('user.company_id',explode(',', $company));
        }

        if( $division != 0 ){
        	$this->db->where_in('user.division_id',explode(',', $division));
        }

        if( $department != 0 ){
        	$this->db->where_in('user.department_id',explode(',', $department));
        }

        $this->db->where('user.deleted',0);
        $this->db->where('employee.resigned',0);
        $this->db->where('employee.resigned_date is null');
        $this->db->where('training_application.deleted',0);
        $this->db->where('training_application.status',5);
        $this->db->where('training_application.training_application_type',1);
        $this->db->order_by('user_company_division.division','asc');
        $this->db->order_by('user_company_department.department','asc');
    	$this->db->order_by('user.lastname','asc');
    	$this->db->order_by('training_application.date_approved','asc');
        $result = $this->db->get('training_application');



        $alpha_ctr = 0;
        $sub_ctr = 0;
        $line++;


        $department_record = array();
        $existing_employee = array();
        $no_of_employee = array();
        $employee_record = array();

        
        foreach( $result->result() as $row) {
			
        	if( !( in_array($row->employee_id, $existing_employee) ) ){
        		array_push($existing_employee, $row->employee_id);
        		$no_of_employee[$row->department_id] += 1;	
        		// $total_per_emp += $row->idp_completion;

        	}
        	$department_record[$row->department_id]['employee_id'][$row->employee_id]['employees'][] = $row->lastname.', '.$row->firstname.' '.$row->middleinitial;
        	$department_record[$row->department_id]['employee_id'][$row->employee_id]['idis'][] = $row->idp_completion;
        	$department_record[$row->department_id]['row_labels'] = $row->department;
        	$department_record[$row->department_id]['idi'] += $row->idp_completion;

        	if( $row->training_calendar_id > 0 ){
				$department_record[$row->department_id]['actual_accompished'] += $row->idp_completion;
				$department_record[$row->department_id]['employee_id'][$row->employee_id]['actual_accompished'][] = $row->idp_completion;
			}

        	
        }
        // dbug($department_record);
        // die();
	    $current_department = 0;
	    $current_employee = 0;

	    foreach ($department_record as $key => $row) {
	    	$alpha_ctr = 0;
        	$sub_ctr = 0;

        	$actual_accompished = 0;	

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

					

		        	switch( $field ){
		        		case 'Row Labels':
	        				$activeSheet->setCellValue($xcoor . $line, $row['row_labels']);
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionArray);
	        			break;
		        		case 'INDIVIDUAL DEVELOPMENT INITIATIVES':
							$activeSheet->setCellValue($xcoor . $line, $row['idi']);
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        			case 'ACTUAL IDP ACCOMPLISHED':
	        				if( $row['actual_accompished'] > 0 ){
								$activeSheet->setCellValue($xcoor . $line, $row['actual_accompished']);
								$actual_accompished += $row['actual_accompished']; // $department_record[$row->department_id]['actual_accompished'];
							}
							else{
								$activeSheet->setCellValue($xcoor . $line, 0);
							}

							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        			case 'No of Employees':
	        				$activeSheet->setCellValue($xcoor . $line, $no_of_employee[$key]);
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        			case 'Departmental %':
	        				$activeSheet->setCellValue($xcoor . $line, number_format(ceil( $actual_accompished / $no_of_employee[$key]),2,'.',''));
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        		}
						
					$alpha_ctr++;
			}

				
				
				$cnt = 0;	
				foreach ($row['employee_id'] as $emp_id => $value) {
						
					$cnt = count($value['employees']);
					$idis = array_sum($value['idis']);
					for ($i=0; $i < $cnt; $i++) { 
						$alpha_ctr = 0;
        				$sub_ctr = 0;
        				$line++;
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
							
								switch( $field ){
					        		case 'Row Labels':
				        				$activeSheet->setCellValue($xcoor . $line, $value['employees'][$i]);
				        			break;
				        			case 'ACTUAL IDP ACCOMPLISHED':
				        				if ($value['actual_accompished']) {
				        					$activeSheet->setCellValue($xcoor . $line, $value['idis'][$i]);
				        					$actual_accompished_emp[$emp_id] = true; 
				        				}
				        				
				        			break;
					        		case 'INDIVIDUAL DEVELOPMENT INITIATIVES':
				        				// $activeSheet->setCellValue($xcoor . $line, $row->idp_completion);
					        			$activeSheet->setCellValue($xcoor . $line, $value['idis'][$i]);
				        			break;
				        		}
									
							$alpha_ctr++;
						}
						
					}
					$alpha_ctr = 0;
        				$sub_ctr = 0;
        				$line++;
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
							
								switch( $field ){
					        		case 'Row Labels':
				        				$activeSheet->setCellValue($xcoor . $line, 'Total per Employees');
				        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleTitleArray1);
				        			break;
				        			case 'ACTUAL IDP ACCOMPLISHED':
				        				if ($actual_accompished_emp[$emp_id]) {
				        					$activeSheet->setCellValue($xcoor . $line, $idis);
				        					$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleTitleArray1);
				        				}
				        				
				        			break;
					        		case 'INDIVIDUAL DEVELOPMENT INITIATIVES':
				        				$activeSheet->setCellValue($xcoor . $line, $idis);
					        			$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleTitleArray1);
				        			break;
				        		}
									
							$alpha_ctr++;
						}
					
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
		header('Content-Disposition: attachment;filename=Percent_IDP_Accomplished_'.date('Y-m-d').'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	


    }



	// END - default module functions
	
	// START custom module funtions
	
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>