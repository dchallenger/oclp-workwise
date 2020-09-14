<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_budget_monitoring extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Budget Monitoring';
		$this->listview_description = 'This module lists all defined training budget monitoring(s).';
		$this->jqgrid_title = "Training Budget Monitoring List";
		$this->detailview_title = 'Training Budget Monitoring Info';
		$this->detailview_description = 'This page shows detailed information about a particular training budget monitoring.';
		$this->editview_title = 'Training Budget Monitoring Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training budget monitoring(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = chosen_script();
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';
		$data['content'] = 'training/training_budget_monitoring/listview';
		$data['jqgrid'] = 'training/training_budget_monitoring/jqgrid';
		
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
	        // $response->last_query = $this->db->last_query();

	        $ctr = 0;

	        foreach ($result->result() as $row) {

	        	$training_calendar_session_result = $this->db->get_where('training_calendar_session',array('training_calendar_id'=>$row->training_calendar_id));


	        	$training_dates = array();
	        	$total_training_hours = 0;
	        	$total_man_days = 0;

	        	if( $training_calendar_session_result->num_rows() > 0 ){
	        		foreach( $training_calendar_session_result->result() as $session_info ){

	        			array_push($training_dates, date('F d Y',strtotime($session_info->session_date)));

	        			$session_start = strtotime($session_info->session_date.' '.$session_info->sessiontime_from);
						$session_end = strtotime($session_info->session_date.' '.$session_info->sessiontime_to);
						$total_training_hours += ( $session_end - $session_start ) / 60 / 60;

						$total_man_days++;

	        		}
	        	}

	        	
	        	$response->rows[$ctr]['cell'][0] = '';
				$response->rows[$ctr]['cell'][1] = $row->lastname.', '.$row->firstname.' '.$row->middleinitial;
				$response->rows[$ctr]['cell'][2] = '';
				$response->rows[$ctr]['cell'][3] = $row->division;
				$response->rows[$ctr]['cell'][4] = $row->department;
				$response->rows[$ctr]['cell'][5] = $row->job_rank;
				$response->rows[$ctr]['cell'][6] = '';
				$response->rows[$ctr]['cell'][7] = $row->areas_development;
				$response->rows[$ctr]['cell'][8] = $row->training_subject;
				$response->rows[$ctr]['cell'][9] = $row->training_type_code;
				$response->rows[$ctr]['cell'][10] = $row->training_provider;
				$response->rows[$ctr]['cell'][11] = implode(', ', $training_dates);
				$response->rows[$ctr]['cell'][12] = $total_training_hours;
				$response->rows[$ctr]['cell'][13] = $total_man_days;
				$response->rows[$ctr]['cell'][14] = $row->itb;

				// $response->rows[$ctr]['cell'][15] = $row->itb - $row->remaining_itb;
				if( $rpw->training_type_id == 1 ){
					$response->rows[$ctr]['cell'][15] = $row->investment;
				}
				else{
					$response->rows[$ctr]['cell'][15] = 0;
				}

				$response->rows[$ctr]['cell'][16] = $row->remaining_itb;

				if( $rpw->training_type_id == 1 ){
					$response->rows[$ctr]['cell'][17] = number_format(( ( $row->investment / $row->itb ) * 100 ),2,'.','');
				}
				else{
					$response->rows[$ctr]['cell'][17] = 0;
				}

				$response->rows[$ctr]['cell'][18] = $row->ctb;
				// $response->rows[$ctr]['cell'][19] = $row->ctb - $row->remaining_ctb;
				if( $rpw->training_type_id == 2 ){
					$response->rows[$ctr]['cell'][19] = $row->investment;
				}
				else{
					$response->rows[$ctr]['cell'][19] = 0;
				}

				$response->rows[$ctr]['cell'][20] = $row->remaining_ctb;

				if( $rpw->training_type_id == 2 ){
					$response->rows[$ctr]['cell'][21] = number_format((($row->investment / $row->ctb)*100),2,'.','');
				}
				else{
					$response->rows[$ctr]['cell'][21] = 0;
				}

				$response->rows[$ctr]['cell'][22] = $row->stb;
				// $response->rows[$ctr]['cell'][23] = $row->stb - $row->remaining_stb;
				if( $rpw->training_type_id == 3 ){
					$response->rows[$ctr]['cell'][23] = $row->investment;
				}
				else{
					$response->rows[$ctr]['cell'][23] = 0;
				}
				$response->rows[$ctr]['cell'][24] = $row->remaining_stb;

				if( $rpw->training_type_id == 2 ){
					$response->rows[$ctr]['cell'][25] = number_format((($row->investment / $row->stb)*100),2,'.','');
				}
				else{
					$response->rows[$ctr]['cell'][25] = 0;
				}

				$response->rows[$ctr]['cell'][26] = $row->itb + $row->ctb + $row->stb;
				$response->rows[$ctr]['cell'][27] = ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb );
				$response->rows[$ctr]['cell'][28] = ( $row->itb + $row->ctb + $row->stb ) - ( ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb ) ) ;
				$response->rows[$ctr]['cell'][29] = number_format((( ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb ) ) / ( $row->itb + $row->ctb + $row->stb ) * 100 ),2,'.','');
				$response->rows[$ctr]['cell'][30] = $row->idp_completion;
				$response->rows[$ctr]['cell'][31] = ($row->training_calendar_id) ? $row->idp_completion : '' ; 
				$response->rows[$ctr]['cell'][32] = $row->remarks;
				$response->rows[$ctr]['cell'][33] = '';

				$ctr++;
	        	
	        }
	
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {

		$this->listview_column_names = array('No.','Names','No of Employees','DIV','DEPT','JOB LEVEL','% Distribution','DEVELOPMENT NEED','ACTUAL TRAINING ATTENDED','Training Budget','TRAINING PROVIDER','ACTUAL TRAINING DATE/S','TRAINING HOURS','Man-Days','INDIVIDUAL TRAINING BUDGET (ITB)','Actual Program Cost charged to ITB','Running Balance - ITB','% UTILIZATION vs. ITB','COMMON TRAINING BUDGET (CTB)','Actual Program Cost charged to CTB','Running Balance - CTB','% UTILIZATION vs. CTB','SUPPLEMENTAL TRAINING BUDGET (STB)','Actual Program Cost charged to STB','Running Balance - STB','% UTILIZATION vs. STB','TOTAL TRAINING BUDGET (ITB + CTB + STB)','TOTAL INVESTMENT','RUNNING BALANCE','% BUDGET UTILIZATION','% DISTRIBUTION OF INDIVIDUAL DEVELOPMENT INITIATIVES','ACTUAL IDP ACCOMPLISHED','REMARKS','OTHER COMMENTS');

		$this->listview_columns = array(
				array('name' => 'no', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'names', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'no_of_employees', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'div', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'dept', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'job_level', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'percent_distribution', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'development_need', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'actual_training_attended', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'training_budget', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'training_provider', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'actual_training_date', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'training_hours', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'man_days', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'itb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'actual_program_cost_itb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'running_balance_itb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'percent_utilization_itb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'ctb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'actual_program_cost_ctb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'running_balance_ctb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'percent_utilization_ctb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'stb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'actual_program_cost_stb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'running_balance_stb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'percent_utilization_stb', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'total_training_budget', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'total_investment', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'running_balance', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'percent_budget_utilization', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'percent_distribution_idi', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'actual_accomplished', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'remarks', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'other_comments', 'width' => '180','align' => 'center', 'sortable' => 'false')
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
		$activeSheet->setCellValue('A2', 'TRAINING BUDGET MONITORING');

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

        $fields = array('No.','Names','No of Employees','DIV','DEPT','JOB LEVEL','% Distribution','DEVELOPMENT NEED','ACTUAL TRAINING ATTENDED','Training Budget','TRAINING PROVIDER','ACTUAL TRAINING DATE/S','TRAINING HOURS','Man-Days','INDIVIDUAL TRAINING BUDGET (ITB)','Actual Program Cost charged to ITB','Running Balance - ITB','% UTILIZATION vs. ITB','COMMON TRAINING BUDGET (CTB)','Actual Program Cost charged to CTB','Running Balance - CTB','% UTILIZATION vs. CTB','SUPPLEMENTAL TRAINING BUDGET (STB)','Actual Program Cost charged to STB','Running Balance - STB','% UTILIZATION vs. STB','TOTAL TRAINING BUDGET (ITB + CTB + STB)','TOTAL INVESTMENT','RUNNING BALANCE','% BUDGET UTILIZATION','% DISTRIBUTION OF INDIVIDUAL DEVELOPMENT INITIATIVES','ACTUAL IDP ACCOMPLISHED','REMARKS','OTHER COMMENTS');


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

        foreach ($result->result() as $row) {

        	$alpha_ctr = 0;
        	$sub_ctr = 0;

        	$training_calendar_session_result = $this->db->get_where('training_calendar_session',array('training_calendar_id'=>$row->training_calendar_id));

        	$training_dates = array();
        	$total_training_hours = 0;
        	$total_man_days = 0;

        	if( $training_calendar_session_result->num_rows() > 0 ){
        		foreach( $training_calendar_session_result->result() as $session_info ){

        			array_push($training_dates, date('F d Y',strtotime($session_info->session_date)));

        			$session_start = strtotime($session_info->session_date.' '.$session_info->sessiontime_from);
					$session_end = strtotime($session_info->session_date.' '.$session_info->sessiontime_to);
					$total_training_hours += ( $session_end - $session_start ) / 60 / 60;

					$total_man_days++;

        		}
        	}
        	
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
	        		case 'No':
        				$activeSheet->setCellValue($xcoor . $line, '');
        			break;
	        		case 'Names':
        				$activeSheet->setCellValue($xcoor . $line, $row->lastname.', '.$row->firstname.' '.$row->middleinitial);
        			break;
        			case 'No of Employees':
        				$activeSheet->setCellValue($xcoor . $line, '');
        			break;
        			case 'DIV':
        				$activeSheet->setCellValue($xcoor . $line, $row->division);
        			break;
        			case 'DEPT':
        				$activeSheet->setCellValue($xcoor . $line, $row->department);
        			break;
        			case 'JOB LEVEL':
        				$activeSheet->setCellValue($xcoor . $line, $row->job_rank);
        			break;
        			case '% Distribution':
        				$activeSheet->setCellValue($xcoor . $line, '');
        			break;
        			case 'DEVELOPMENT NEED':
        				$activeSheet->setCellValue($xcoor . $line, $row->areas_development);
        			break;
        			case 'ACTUAL TRAINING ATTENDED':
        				$activeSheet->setCellValue($xcoor . $line, $row->training_subject);
        			break;
        			case 'Training Budget':
        				$activeSheet->setCellValue($xcoor . $line, $row->training_type_code);
        			break;
        			case 'TRAINING PROVIDER':
        				$activeSheet->setCellValue($xcoor . $line, $row->training_provider);
        			break;
        			case 'ACTUAL TRAINING DATE/S':
        				$activeSheet->setCellValue($xcoor . $line, implode(', ', $training_dates));
        			break;
        			case 'TRAINING HOURS':
        				$activeSheet->setCellValue($xcoor . $line, $total_training_hours);
        			break;
        			case 'Man-Days':
        				$activeSheet->setCellValue($xcoor . $line, $total_man_days);
        			break;
        			case 'INDIVIDUAL TRAINING BUDGET (ITB)':
        				$activeSheet->setCellValue($xcoor . $line, $row->itb);
        			break;
        			case 'Actual Program Cost charged to ITB':
        				// $activeSheet->setCellValue($xcoor . $line, $row->itb - $row->remaining_itb);
        				if( $row->training_type_id == 1 ){
        					$activeSheet->setCellValue($xcoor . $line, number_format($row->investment,2,'.','') );
        				}
        				else{
        					$activeSheet->setCellValue($xcoor . $line, '0.00');	
        				}
        			break;
        			case 'Running Balance - ITB':
        				$activeSheet->setCellValue($xcoor . $line, $row->remaining_itb);
        			break;
        			case '% UTILIZATION vs. ITB':
        				if( $row->training_type_id == 1 ){
        					$activeSheet->setCellValue($xcoor . $line, number_format(( ( $row->investment / $row->itb ) * 100 ),2,'.','') );
        				}
        				else{
        					$activeSheet->setCellValue($xcoor . $line, '0.00');	
        				}
        			break;
        			case 'COMMON TRAINING BUDGET (CTB)':
        				$activeSheet->setCellValue($xcoor . $line, $row->ctb);
        			break;
        			case 'Actual Program Cost charged to CTB':
        				if( $row->training_type_id == 2 ){
        					$activeSheet->setCellValue($xcoor . $line, number_format($row->investment,2,'.','') );
        				}
        				else{
        					$activeSheet->setCellValue($xcoor . $line, '0.00');	
        				}
        				// $activeSheet->setCellValue($xcoor . $line, $row->ctb - $row->remaining_ctb);
        			break;
        			case 'Running Balance - CTB':
        				$activeSheet->setCellValue($xcoor . $line, $row->remaining_ctb);
        			break;
        			case '% UTILIZATION vs. CTB':
        				if( $row->training_type_id == 2 ){
        					$activeSheet->setCellValue($xcoor . $line, number_format(( ( $row->investment / $row->ctb ) * 100 ),2,'.',''));
        				}
        				else{
        					$activeSheet->setCellValue($xcoor . $line, '0.00');
        				}
        			break;
        			case 'SUPPLEMENTAL TRAINING BUDGET (STB)':
        				$activeSheet->setCellValue($xcoor . $line, $row->stb);
        			break;
        			case 'Actual Program Cost charged to STB':
        				if( $row->training_type_id == 3 ){
        					$activeSheet->setCellValue($xcoor . $line, number_format($row->investment,2,'.','') );
        				}
        				else{
        					$activeSheet->setCellValue($xcoor . $line, '0.00');	
        				}
        				// $activeSheet->setCellValue($xcoor . $line, $row->stb - $row->remaining_stb);
        			break;
        			case 'Running Balance - STB':
        				$activeSheet->setCellValue($xcoor . $line, $row->remaining_stb);
        			break;
        			case '% UTILIZATION vs. STB':
        				if( $row->training_type_id == 3 ){
        					$activeSheet->setCellValue($xcoor . $line, number_format(( ( $row->investment / $row->stb ) * 100 ),2,'.',''));
        				}
        				else{
        					$activeSheet->setCellValue($xcoor . $line, '0.00');
        				}
        			break;
        			case 'TOTAL TRAINING BUDGET (ITB + CTB + STB)':
        				$activeSheet->setCellValue($xcoor . $line, $row->itb + $row->ctb + $row->stb);
        			break;
        			case 'TOTAL INVESTMENT':
        				$activeSheet->setCellValue($xcoor . $line, ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb ));
        			break;
        			case 'RUNNING BALANCE':
        				$activeSheet->setCellValue($xcoor . $line, ( $row->itb + $row->ctb + $row->stb ) - ( ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb ) ));
        			break;
        			case '% BUDGET UTILIZATION':
        				$activeSheet->setCellValue($xcoor . $line, number_format(( ( ( ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb ) ) / ( $row->itb + $row->ctb + $row->stb )  ) * 100 ),2,'.','')  );
        			break;
        			case '% DISTRIBUTION OF INDIVIDUAL DEVELOPMENT INITIATIVES':
        				$activeSheet->setCellValue($xcoor . $line, $row->idp_completion);
        			break;
        			case 'ACTUAL IDP ACCOMPLISHED':
        				if($row->training_calendar_id){
        					$activeSheet->setCellValue($xcoor . $line, $row->idp_completion);
        				}
        				
        			break;
        			case 'REMARKS':
        				$activeSheet->setCellValue($xcoor . $line, $row->remarks);
        			break;
        			case 'OTHER COMMENTS':
        				$activeSheet->setCellValue($xcoor . $line, '');
        			break;
        		}
				
				$alpha_ctr++;
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
		header('Content-Disposition: attachment;filename=Training_Budget_Monitoring_'.date('Y-m-d').'.xls');
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