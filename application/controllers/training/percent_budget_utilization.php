<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Percent_budget_utilization extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Percent Budget Utilization';
		$this->listview_description = 'This module lists all defined percent budget utilization(s).';
		$this->jqgrid_title = "Percent Budget Utilization List";
		$this->detailview_title = 'Percent Budget Utilization Info';
		$this->detailview_description = 'This page shows detailed information about a particular percent budget utilization.';
		$this->editview_title = 'Percent Budget Utilization Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about percent budget utilization(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = chosen_script();
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';
		$data['content'] = 'training/percent_budget_utilization/listview';
		$data['jqgrid'] = 'training/percent_budget_utilization/jqgrid';
		
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
	        $result = $this->db->get('training_application');
	        $response->last_query = $this->db->last_query();

	        $record = array();
	        $record_division = array();

	        foreach ($result->result() as $row) {
	        	
	        	$record[$row->department_id]['department_id'] = $row->department_id;
	        	$record[$row->department_id]['division_id'] = $row->division_id;
	        	$record[$row->department_id]['row_labels'] = $row->department;
	        	$record[$row->department_id]['itb'] += $row->itb;
	        	$record[$row->department_id]['ctb'] += $row->ctb;
	        	$record[$row->department_id]['stb'] += $row->stb;
	        	$record[$row->department_id]['iti'] += $row->itb - $row->remaining_itb;
	        	$record[$row->department_id]['cti'] += $row->ctb - $row->remaining_ctb;
	        	$record[$row->department_id]['sti'] += $row->stb - $row->remaining_stb;
	        	$record[$row->department_id]['sum_total_investment'] += ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb );
	        	$record[$row->department_id]['remaining_itb'] += $row->remaining_itb;
	        	$record[$row->department_id]['remaining_ctb'] += $row->remaining_ctb;
	        	$record[$row->department_id]['remaining_stb'] += $row->remaining_stb;
	        	$record[$row->department_id]['itb_utilization'] += $row->investment / $row->itb;
	        	$record[$row->department_id]['ctb_utilization'] += $row->investment / $row->ctb;
	        	$record[$row->department_id]['stb_utilization'] += $row->investment / $row->stb;
	        	$record[$row->department_id]['sum_total_budget'] += $row->itb + $row->ctb + $row->stb;
	        	$record[$row->department_id]['percent_utilization'] += $row->investment / ( $row->itb + $row->ctb + $row->stb );
	        	$record[$row->department_id]['idi'] += $row->idp_completion;

	        	if( $row->training_caledar_id > 0 ){
	        		$record[$row->department_id]['actual_accomplished'] += $row->$row->idp_completion;
	        	}
	        	else{
	        		$record[$row->department_id]['actual_accomplished'] += 0;
	        	}
	        	
	        	$record_division[$row->division_id]['department_id'] = $row->department_id;
	        	$record_division[$row->division_id]['division_id'] = $row->division_id;
	        	$record_division[$row->division_id]['row_labels'] = $row->division;
	        	$record_division[$row->division_id]['itb'] += $row->itb;
	        	$record_division[$row->division_id]['ctb'] += $row->ctb;
	        	$record_division[$row->division_id]['stb'] += $row->stb;
	        	$record_division[$row->division_id]['iti'] += $row->itb - $row->remaining_itb;
	        	$record_division[$row->division_id]['cti'] += $row->ctb - $row->remaining_ctb;
	        	$record_division[$row->division_id]['sti'] += $row->stb - $row->remaining_stb;
	        	$record_division[$row->division_id]['sum_total_investment'] += ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb );
	        	$record_division[$row->division_id]['remaining_itb'] += $row->remaining_itb;
	        	$record_division[$row->division_id]['remaining_ctb'] += $row->remaining_ctb;
	        	$record_division[$row->division_id]['remaining_stb'] += $row->remaining_stb;
	        	$record_division[$row->division_id]['itb_utilization'] += $row->investment / $row->itb;
	        	$record_division[$row->division_id]['ctb_utilization'] += $row->investment / $row->ctb;
	        	$record_division[$row->division_id]['stb_utilization'] += $row->investment / $row->stb;
	        	$record_division[$row->division_id]['sum_total_budget'] += $row->itb + $row->ctb + $row->stb;
	        	$record_division[$row->division_id]['percent_utilization'] += number_format(( $row->investment / ( $row->itb + $row->ctb + $row->stb )),2,'.','');
	        	$record_division[$row->division_id]['idi'] += $row->idp_completion;

	        	if( $row->training_caledar_id > 0 ){
	        		$record_division[$row->division_id]['actual_accomplished'] += $row->$row->idp_completion;
	        	}
	        	else{
	        		$record_division[$row->division_id]['actual_accomplished'] += 0;
	        	}

	        }



	       	$ctr = 0;
	       	$current_division = 0;

	        foreach( $record as $budget_info ){

	        	if( $current_division != $budget_info['division_id'] ){

	        		$current_division = $budget_info['division_id'];

	        		$response->rows[$ctr]['cell'][0] = "<strong>".$record_division[$current_division]['row_labels']."</strong>";
	        		$response->rows[$ctr]['cell'][1] = $record_division[$current_division]['sum_total_budget'];
					$response->rows[$ctr]['cell'][2] = $record_division[$current_division]['sum_total_investment'];
					$response->rows[$ctr]['cell'][3] = number_format(( ( $record_division[$current_division]['sum_total_investment'] / $record_division[$current_division]['sum_total_budget'] ) * 100 ),2,'.','');
					$response->rows[$ctr]['cell'][4] = $record_division[$current_division]['idi'];
					$response->rows[$ctr]['cell'][5] = $record_division[$current_division]['actual_accomplished'];

					$ctr++;

	        	}

	        	$response->rows[$ctr]['cell'][0] = $budget_info['row_labels'];
	        	$response->rows[$ctr]['cell'][1] = $budget_info['sum_total_budget'];
				$response->rows[$ctr]['cell'][2] = $budget_info['sum_total_investment'];
				$response->rows[$ctr]['cell'][3] = number_format(( ( $budget_info['sum_total_investment'] / $budget_info['sum_total_budget'] ) * 100 ),2,'.','');
				$response->rows[$ctr]['cell'][4] = $budget_info['idi'];
				$response->rows[$ctr]['cell'][5] = $budget_info['actual_accomplished'];

				$ctr++;
	        }
	
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {

		$this->listview_column_names = array('Row Labels','Sum of Total Training Budget','Sum of Total Investment','% Utilization','INDIVIDUAL DEVELOPMENT INITIATIVES','ACTUAL IDP ACCOMPLISHED');

		$this->listview_columns = array(
				array('name' => 'row_labels', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'sum_of_total_training_budget', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'sum_of_total_investment', 'width' => '180','align' => 'center', 'sortable' => 'false'),		
				array('name' => 'percent_utilization', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'individual_development_initiatives', 'width' => '180','align' => 'center', 'sortable' => 'false'),
				array('name' => 'actual_accomplished', 'width' => '180','align' => 'center', 'sortable' => 'false')
			); 
                          
    }

    function export(){

    	$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Percent Budget Utilization")
		            ->setDescription("Percent Budget Utilization");
		               
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
		$activeSheet->setCellValue('A2', 'Budget Utilization');

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleTitleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleTitleArray);

		$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
		$objPHPExcel->getActiveSheet()->mergeCells('A2:D2');

        $line = 4;

        $fields = array('Row Labels','Sum of Total Budget','Sum of Total Investment','% Utilization','Individual Development Initiatives','Actual IDP Accomplished');


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
        $result = $this->db->get('training_application');

        $record = array();
        $record_division = array();
        $grand_total = array();

        foreach ($result->result() as $row) {
        	
        	$record[$row->department_id]['department_id'] = $row->department_id;
        	$record[$row->department_id]['division_id'] = $row->division_id;
        	$record[$row->department_id]['row_labels'] = $row->department;
        	$record[$row->department_id]['itb'] += $row->itb;
        	$record[$row->department_id]['ctb'] += $row->ctb;
        	$record[$row->department_id]['stb'] += $row->stb;
        	$record[$row->department_id]['iti'] += $row->itb - $row->remaining_itb;
        	$record[$row->department_id]['cti'] += $row->ctb - $row->remaining_ctb;
        	$record[$row->department_id]['sti'] += $row->stb - $row->remaining_stb;
        	$record[$row->department_id]['sum_total_investment'] += ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb );
        	$record[$row->department_id]['remaining_itb'] += $row->remaining_itb;
        	$record[$row->department_id]['remaining_ctb'] += $row->remaining_ctb;
        	$record[$row->department_id]['remaining_stb'] += $row->remaining_stb;
        	$record[$row->department_id]['itb_utilization'] += $row->investment / $row->itb;
        	$record[$row->department_id]['ctb_utilization'] += $row->investment / $row->ctb;
        	$record[$row->department_id]['stb_utilization'] += $row->investment / $row->stb;
        	$record[$row->department_id]['sum_total_budget'] += $row->itb + $row->ctb + $row->stb;
        	$record[$row->department_id]['percent_utilization'] += $row->investment / ( $row->itb + $row->ctb + $row->stb );
        	$record[$row->department_id]['percent_utilization'] += $row->investment / ( $row->itb + $row->ctb + $row->stb );
        	$record[$row->department_id]['idi'] += $row->idp_completion;

        	if( $row->training_caledar_id > 0 ){
        		$record[$row->department_id]['actual_accomplished'] += $row->$row->idp_completion;
        	}
        	else{
        		$record[$row->department_id]['actual_accomplished'] += 0;
        	}
        	
        	$record_division[$row->division_id]['department_id'] = $row->department_id;
        	$record_division[$row->division_id]['division_id'] = $row->division_id;
        	$record_division[$row->division_id]['row_labels'] = $row->division;
        	$record_division[$row->division_id]['itb'] += $row->itb;
        	$record_division[$row->division_id]['ctb'] += $row->ctb;
        	$record_division[$row->division_id]['stb'] += $row->stb;
        	$record_division[$row->division_id]['iti'] += $row->itb - $row->remaining_itb;
        	$record_division[$row->division_id]['cti'] += $row->ctb - $row->remaining_ctb;
        	$record_division[$row->division_id]['sti'] += $row->stb - $row->remaining_stb;
        	$record_division[$row->division_id]['sum_total_investment'] += ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb );
        	$record_division[$row->division_id]['remaining_itb'] += $row->remaining_itb;
        	$record_division[$row->division_id]['remaining_ctb'] += $row->remaining_ctb;
        	$record_division[$row->division_id]['remaining_stb'] += $row->remaining_stb;
        	$record_division[$row->division_id]['itb_utilization'] += $row->investment / $row->itb;
        	$record_division[$row->division_id]['ctb_utilization'] += $row->investment / $row->ctb;
        	$record_division[$row->division_id]['stb_utilization'] += $row->investment / $row->stb;
        	$record_division[$row->division_id]['sum_total_budget'] += $row->itb + $row->ctb + $row->stb;
        	$record_division[$row->division_id]['percent_utilization'] += $row->investment / ( $row->itb + $row->ctb + $row->stb );
        	$record_division[$row->division_id]['idi'] += $row->idp_completion;

        	if( $row->training_caledar_id > 0 ){
        		$record_division[$row->division_id]['actual_accomplished'] += $row->$row->idp_completion;
        	}
        	else{
        		$record_division[$row->division_id]['actual_accomplished'] += 0;
        	}

        	$grand_total['itb'] += $row->itb;
        	$grand_total['ctb'] += $row->ctb;
        	$grand_total['stb'] += $row->stb;
        	$grand_total['iti'] += $row->itb - $row->remaining_itb;
        	$grand_total['cti'] += $row->ctb - $row->remaining_ctb;
        	$grand_total['sti'] += $row->stb - $row->remaining_stb;
        	$grand_total['sum_total_investment'] += ( $row->itb - $row->remaining_itb ) + ( $row->ctb - $row->remaining_ctb ) + ( $row->stb - $row->remaining_stb );
        	$grand_total['remaining_itb'] += $row->remaining_itb;
        	$grand_total['remaining_ctb'] += $row->remaining_ctb;
        	$grand_total['remaining_stb'] += $row->remaining_stb;
        	$grand_total['itb_utilization'] += $row->investment / $row->itb;
        	$grand_total['ctb_utilization'] += $row->investment / $row->ctb;
        	$grand_total['stb_utilization'] += $row->investment / $row->stb;
        	$grand_total['sum_total_budget'] += $row->itb + $row->ctb + $row->stb;
        	$grand_total['percent_utilization'] += $row->investment / ( $row->itb + $row->ctb + $row->stb );
			$grand_total['idi'] += $row->idp_completion;

			if( $row->training_caledar_id > 0 ){
        		$grand_total['actual_accomplished'] += $row->$row->idp_completion;
        	}
        	else{
        		$grand_total['actual_accomplished'] += 0;
        	}


        }

        $current_division = 0;
        $line++;
        $alpha_ctr = 0;

        foreach( $record as $budget_info ){

        	if( $current_division != $budget_info['division_id'] ){

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


	        		$current_division = $budget_info['division_id'];

	        		switch( $field ){
	        			case 'Row Labels':
	        				$activeSheet->setCellValue($xcoor . $line, $record_division[$current_division]['row_labels']);
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionArray);
	        			break;
	        			case 'Sum of Total Budget':
	        				$activeSheet->setCellValue($xcoor . $line, $record_division[$current_division]['sum_total_budget']);
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        			case 'Sum of Total Investment':
	        				$activeSheet->setCellValue($xcoor . $line, $record_division[$current_division]['sum_total_investment']);
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        			case '% Utilization':
	        				$activeSheet->setCellValue($xcoor . $line, number_format(( ( $record_division[$current_division]['sum_total_investment'] / $record_division[$current_division]['sum_total_budget'] ) * 100 ),2,'.','') );
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        			case 'Individual Development Initiatives':
	        				$activeSheet->setCellValue($xcoor . $line, $record_division[$current_division]['idi']);
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        			case 'Actual IDP Accomplished':
	        				$activeSheet->setCellValue($xcoor . $line, $record_division[$current_division]['actual_accomplished'] );
	        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDivisionFieldArray);
	        			break;
	        		}

					
					$alpha_ctr++;

				}

				$line++;
				$alpha_ctr = 0;

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
        			case 'Row Labels':
        				$activeSheet->setCellValue($xcoor . $line, $budget_info['row_labels']);
        				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getAlignment()->setIndent(5);
        			break;
        			case 'Sum of Total Budget':
        				$activeSheet->setCellValue($xcoor . $line, $budget_info['sum_total_budget']);
        			break;
        			case 'Sum of Total Investment':
        				$activeSheet->setCellValue($xcoor . $line, $budget_info['sum_total_investment']);
        			break;
        			case '% Utilization':
        				$activeSheet->setCellValue($xcoor . $line, number_format(( ( $budget_info['sum_total_investment'] / $budget_info['sum_total_budget'] ) * 100 ),2,'.',''));
        			break;
        			case 'Individual Development Initiatives':
        				$activeSheet->setCellValue($xcoor . $line, $budget_info['idi']);
        			break;
        			case 'Actual IDP Accomplished':
        				$activeSheet->setCellValue($xcoor . $line, $budget_info['actual_accomplished'] );
        			break;
        		}
				
				$alpha_ctr++;
			}

			$alpha_ctr = 0;
			$line++;

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

        	switch( $field ){
    			case 'Row Labels':
    				$activeSheet->setCellValue($xcoor . $line, 'Grand Total');
    				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleGrandTotalArray);
    			break;
    			case 'Sum of Total Budget':
    				$activeSheet->setCellValue($xcoor . $line, $grand_total['sum_total_budget']);
    				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleGrandTotalNumberArray);
    			break;
    			case 'Sum of Total Investment':
    				$activeSheet->setCellValue($xcoor . $line, $grand_total['sum_total_investment']);
    				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleGrandTotalNumberArray);
    			break;
    			case '% Utilization':
    				$activeSheet->setCellValue($xcoor . $line, number_format(( ( $grand_total['sum_total_investment'] / $grand_total['sum_total_budget'] ) * 100 ),2,'.','')  );
    				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleGrandTotalNumberArray);
    			break;
    			case 'Individual Development Initiatives':
    				$activeSheet->setCellValue($xcoor . $line, $grand_total['idi']);
    				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleGrandTotalNumberArray);
    			break;
    			case 'Actual IDP Accomplished':
    				$activeSheet->setCellValue($xcoor . $line, $grand_total['actual_accomplished'] );
    				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleGrandTotalNumberArray);
    			break;
    		}

    		
			
			$alpha_ctr++;
		}




        // Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=Percent_Budget_Utilization'.date('Y-m-d').'.xls');
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