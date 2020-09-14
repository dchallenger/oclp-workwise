<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ot_allowances extends MY_Controller
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
		$data['content'] = 'dtr/ot_allowances/listview';

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

	function listview()
	{
		$this->load->helper('time_upload');

        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

        $total_pages = 12 > 0 ? ceil(12/$limit) : 0;
        $response->page = $page > $total_pages ? $total_pages : $page;
        $response->total = $total_pages;
        $response->records = 0;                         

        $response->msg = "";

		$search = 1;

		$this->db->select('u.employee_id');
		$this->db->select('CONCAT(u.firstname, " ",u.lastname) as employee_name', false);
		$this->db->from('user u');
		$this->db->join('employee_dtr dtr','u.employee_id = dtr.employee_id');
		$this->db->where('overtime >=',4);	
		$this->db->where('u.deleted = 0 AND '.$search);	

		switch ($this->input->post('category')) {
			case 1:
					if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('u.company_id ',$this->input->post('company'));
				break;
			case 2:
					if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('u.division_id ',$this->input->post('division'));		
				break;
			case 3:
					if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('u.department_id ',$this->input->post('department'));
				break;
			case 4:
					if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));		
				break;								
		}
	
		$this->db->group_by("employee_id");       
        
        $result_employee = $this->db->get();

        $ctr = 0;
        foreach ($result_employee->result() as $row_employee) {
        	$response->rows[$ctr]['id'] = $row_employee->employee_id;
            $response->rows[$ctr]['cell'][0] = 'employee-'.$row_employee->employee_id;
            $response->rows[$ctr]['cell'][1] = '<strong>'.$row_employee->employee_name.'</strong>';
            $response->rows[$ctr]['cell'][2] = "";
            $response->rows[$ctr]['cell'][3] = "";
            $response->rows[$ctr]['cell'][4] = "";
            $response->rows[$ctr]['cell'][5] = "";
            $response->rows[$ctr]['cell'][6] = "0";
            $response->rows[$ctr]['cell'][7] = null;
            $response->rows[$ctr]['cell'][8] = false;            
            $response->rows[$ctr]['cell'][9] = true;
			$ctr++;	                    	

			$this->db->select('oot.date_created AS date_applied,oot.date AS ot_date');
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX(oot.datetime_from'.'," ",2)," ",-1) as "start"',false);
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX(oot.datetime_to'.'," ",2)," ",-1) as "end"',false);
			$this->db->select('dtr.overtime AS ot_rendered');			
			$this->db->select('(ndot + ndot_excess) AS ndot');
			$this->db->from('employee_dtr dtr');
			$this->db->join('employee_oot oot','dtr.employee_id = oot.employee_id AND dtr.date = oot.date');			
			$this->db->join('dtr_daily_summary dds','dtr.employee_id = dds.employee_id AND dtr.date = dds.date');			
			$this->db->where('dtr.deleted',0);		
			$this->db->where('oot.deleted',0);
			$this->db->where('dtr.overtime >=',4);
			$this->db->where('dtr.employee_id',$row_employee->employee_id);		
			$this->db->where('(dtr.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" )');
			$result = $this->db->get();

			foreach ($result->result() as $row) {
				$response->rows[$ctr]['id'] = $row_employee->employee_id.$ctr;
	            $response->rows[$ctr]['cell'][0] = '';
	            $response->rows[$ctr]['cell'][1] = date($this->config->item('display_date_hdi'),strtotime($row->date_applied));
	            $response->rows[$ctr]['cell'][2] = "asdf";
	            $response->rows[$ctr]['cell'][3] = "asdf";
	            $response->rows[$ctr]['cell'][4] = "asdf";
	            $response->rows[$ctr]['cell'][5] = "asdf";
	            $response->rows[$ctr]['cell'][6] = 1; //level
	            $response->rows[$ctr]['cell'][7] = 'employee-'.$row_employee->employee_id; //parent
	            $response->rows[$ctr]['cell'][8] = true; //leaf
	            $response->rows[$ctr]['cell'][9] = false; //expanded field	            
	            $ctr++;
        	}
        }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
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

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
	            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
	        }        
    	}
        
        $buttons .= "</div>";

        $buttons="";
                
		return $buttons;
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
		    	$this->db->join('employee','employee.user_id = user.user_id','left');
		    	$this->db->where_in('employee.employee_type',array('2','3'));
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

	// export called using ajax
	function excel_ajax_export()
	{	


		$search = 1;

		$this->db->select('u.employee_id');
		$this->db->select('CONCAT(u.firstname, " ",u.lastname) as "Employee Name"', false);
		$this->db->select('e.employee_type');
		$this->db->from('user u');
		$this->db->join('employee_dtr dtr','u.employee_id = dtr.employee_id');
		$this->db->join('employee e','e.user_id = u.user_id','left');
		$this->db->where_in('e.employee_type',array('2','3'));
		$this->db->where('overtime >=',240);	
		$this->db->where('u.deleted = 0 AND '.$search);	
		$this->db->where('(dtr.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'" )');

		switch ($this->input->post('category')) {
			case 1:
					if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('u.company_id ',$this->input->post('company'));
				break;
			case 2:
					if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('u.division_id ',$this->input->post('division'));		
				break;
			case 3:
					if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('u.department_id ',$this->input->post('department'));
				break;
			case 4:
					if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));		
				break;								
		}

		$this->db->group_by("u.employee_id"); 

		/*
        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            $this->db->order_by($sidx . ' ' . $sord);
        }
        */

        $this->db->order_by('u.firstname','ASC');

		$q = $this->db->get();

		$query  = $q;

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("OT and Allowances")
		            ->setDescription("OT and Allowances");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
/*		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);	*/				
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);					

		//Initialize style
		$HorizontalCenter = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$leftstyleArray = array(
			'font' => array(
				'italic' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleArrayBorder = array(
		  	'borders' => array(
		    	'allborders' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	)
		  	)
		);

		$styleArrayBorderGen = array(
			'borders' => array(
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN,
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN,
			    ),
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN,
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN,
			    ),
			) 			
		);

		$styleArrayBorderTopBottom = array(
		  	'borders' => array(
		    	'bottom' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	),
		    	'top' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	),		    	
		  	)
		);

		$styleArrayBorderRight = array(
		  	'borders' => array(
		    	'right' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	),	    	
		  	)
		);

		$styleArrayBorderBottom = array(
		  	'borders' => array(
		    	'bottom' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	),	    	
		  	)
		);

		$fields = array("Date","OT","OT","","Actual OT","Meal","Transpo");
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

			$activeSheet->setCellValue($xcoor . '6', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArrayBorderRight);
			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '7')->applyFromArray($styleArrayBorderRight);
			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		$alpha_header = $alpha_ctr;

		$objPHPExcel->getActiveSheet()->mergeCells('C6:D6');

		$objPHPExcel->getActiveSheet()->mergeCells('E6:E7');
		$objPHPExcel->getActiveSheet()->getStyle('E6:E7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);		
		$objPHPExcel->getActiveSheet()->mergeCells('F6:F7');
		$objPHPExcel->getActiveSheet()->getStyle('F6:F7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getActiveSheet()->mergeCells('G6:G7');
		$objPHPExcel->getActiveSheet()->getStyle('G6:G7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$sub_ctr   = 0;			
		$alpha_ctr = 0;
		$fields = array("Applied","Date","Start","End");
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

			$activeSheet->setCellValue($xcoor . '7', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '7')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_header - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', 'HDI GROUP');
		$activeSheet->setCellValue('A2', 'OT and Allowances');
		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			$activeSheet->setCellValue('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		$fields = array("date_applied","ot_date","start","end","ot_rendered","meal","transpo");

		// contents.
		$line = 8;
		foreach ($query->result() as $row_employee) {
			$otexcess_pay_meal = 0;			
			$otexcess_pay_transpo = 0;			
			$total_meal_pay = 0;
			$total_transpo_pay = 0;
			$sub_ctr   = 0;			
			$alpha_ctr = 0;

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			/*
			$this->db->select('oot.date_created AS date_applied,oot.date AS ot_date');
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX(oot.datetime_from'.'," ",2)," ",-1) as "start"',false);
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX(oot.datetime_to'.'," ",2)," ",-1) as "end"',false);
			$this->db->select('dtr.overtime AS ot_rendered');			
			$this->db->select('(ndot + ndot_excess) AS ndot');
			$this->db->from('employee_dtr dtr');
			$this->db->join('employee_oot oot','dtr.employee_id = oot.employee_id AND dtr.date = oot.date');			
			$this->db->join('dtr_daily_summary dds','dtr.employee_id = dds.employee_id AND dtr.date = dds.date');			
			$this->db->where('dtr.deleted',0);		
			$this->db->where('oot.deleted',0);
			$this->db->where('oot.form_status_id',3);
			$this->db->where('dtr.overtime >=',240);
			$this->db->where('dtr.employee_id',$row_employee->employee_id);		
			$this->db->where('(oot.date_approved BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'" )');
			$result = $this->db->get();
			*/

			$sql = 'SELECT poam.ot_allowance_id, poam.employee_oot_id, ootm.date_created, ootm.datetime_from, ootm.datetime_to, poam.date, poam.employee_id , poam.ot_total, poam.amount AS meal_allowance, poat.amount AS transpo_allowance
					FROM '.$this->db->dbprefix('user').' u
					LEFT JOIN '.$this->db->dbprefix('payroll_ot_allowance').' poam ON poam.employee_id = u.employee_id AND poam.transaction_id = (
						SELECT ptm.transaction_id FROM '.$this->db->dbprefix('payroll_transaction').' ptm WHERE ptm.transaction_code = "ALLOWANCE_MEAL"
					)
					LEFT JOIN hr_payroll_ot_allowance poat ON poat.employee_id = u.employee_id AND poat.date = poam.date AND poat.transaction_id = (
						SELECT ptm.transaction_id FROM '.$this->db->dbprefix('payroll_transaction').' ptm WHERE ptm.transaction_code = "ALLOWANCE_TRANSPO"
					)
					LEFT JOIN '.$this->db->dbprefix('employee_oot').' ootm ON ootm.employee_oot_id = poam.employee_oot_id
					LEFT JOIN '.$this->db->dbprefix('employee_oot').' oott ON oott.employee_oot_id = poat.employee_oot_id
					WHERE ( poam.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'" ) AND u.employee_id = '.$row_employee->employee_id;

			$result = $this->db->query($sql);

			if ($result && $result->num_rows() > 0){

				$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row_employee->{"Employee Name"});

				$line++;
				$total_ot = 0;
				$total_meal = 0;
				$total_transpo = 0;
				foreach ($result->result() as $row) {

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

						switch ($field) {
							case 'date_applied':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '         ' . date($this->config->item('display_date_hdi'),strtotime($row->{'date_created'})));
								break;
							case 'ot_date':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, date('F-d D',strtotime($row->{'date'})));
								break;		
							case 'ot_rendered':
								$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($row->{'ot_total'}, 2));
								$total_ot += number_format($row->{'ot_total'}, 2);
								break;																			
							case 'start':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, date('h:i a', strtotime($row->{'datetime_from'})));
								break;
							case 'end':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, date('h:i a', strtotime($row->{'datetime_to'})));
								break;
							case 'meal':
								$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($row->{'meal_allowance'},2));
								$total_meal += number_format($row->{'meal_allowance'},2);
								break;
							case 'transpo':
								$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($row->{'transpo_allowance'},2));
								$total_transpo += number_format($row->{'transpo_allowance'},2);
								break;																					
							default:
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});
								break;
						}
						$alpha_ctr++;
					}
					$line++;
				}

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

					switch ($field) {
						case 'ot_rendered':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($total_ot, 2));
							break;																			
						case 'meal':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($total_meal,2));
							break;
						case 'transpo':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($total_transpo,2));
							break;																					
					}
					$alpha_ctr++;					
				}	
				$line++;
			}							
		}

		$objPHPExcel->getActiveSheet()->getStyle('C6:D6')->applyFromArray($styleArrayBorderBottom);
		$objPHPExcel->getActiveSheet()->getStyle('A6:G7')->applyFromArray($styleArrayBorderGen);
		$objPHPExcel->getActiveSheet()->getStyle('A8:G'.($line - 1))->applyFromArray($styleArrayBorder);
		$objPHPExcel->getActiveSheet()->getStyle('B8:D'.$line)->applyFromArray($HorizontalCenter);
		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title("DTR Summary Report") . '.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/ot_allowances/'.url_title("DTR Summary Report").'-'.date('Y-m-d').'.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));

		
	}	

	private function _excel_export($record_id = 0)
	{	
		$this->load->helper('time_upload');
		$subordinates = 0;
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

		$search = 1;

		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.employee_id'.',CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as "Employee Name"', false);
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.' as "Date"');		
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as "IN"',false);
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as "OUT"',false);
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.hours_worked'.' as  "Hours Worked",'.$this->db->dbprefix('employee_dtr'). '.lates'.' as "Lates(Hours)",'.$this->db->dbprefix('employee_dtr'). '.undertime'.' as "UT(Hours)",'.$this->db->dbprefix('employee_dtr'). '.overtime'.' as "OT(Hours)"');		
		$this->db->from('employee_dtr');
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");
		$this->db->where('employee_dtr.deleted = 0 AND '.$search);
		$this->db->where('IF(resigned_date IS NULL, 1, `date` <= resigned_date)');	

		if ($subordinates == 0){
			$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
		}

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

		switch ($this->input->post('category1')) {
			case 2:
					$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
				break;
			case 3:
					$this->db->where('(hours_worked <= 4 AND overtime = 0 AND (lates + overtime) <= 4)', '', false);
				break;				
			case 4:
					$this->db->where($this->db->dbprefix('employee_dtr').'.lates >',"0");			
				break;	
			case 5:
					$this->db->where($this->db->dbprefix('employee_dtr').'.undertime >',"0");					
				break;
			case 6:
				$this->db->where($this->db->dbprefix('employee_dtr').'.overtime >',"0");
				break;																
		}

		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			$this->db->where('('.$this->db->dbprefix('employee_dtr').'.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'" )');
		}

        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            if ($sidx == "absent"){
            	$sidx = "hours_worked";
            }
            $this->db->order_by($sidx . ' ' . $sord);
        } 

		$q = $this->db->get();
		$query  = $q;
		$fields = $q->list_fields();

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("DTR Summary Report")
		            ->setDescription("DTR Summary Report");
		               
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

		if ($this->input->post('dynamic')){
			switch ($this->input->post('category1')) {
				case 2:
						$fields = array_merge(array_diff($fields, array("Absent","Lates(Hours)","UT(Hours)","OT(Hours)")));				
					break;
				case 3:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Lates(Hours)","UT(Hours)","OT(Hours)")));				
					break;					
				case 4:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","UT(Hours)","OT(Hours)")));				
					break;
				case 5:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","Lates(Hours)","OT(Hours)")));				
					break;															
				case 6:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","Lates(Hours)","UT(Hours)")));				
					break;					
			}			
		}
		else{
			array_splice($fields, 6, 0, "Absent");			
		}

		unset($fields[0]);
		$fields[] = "Remarks";
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

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValueExplicit('A2', 'DTR Summary Report', PHPExcel_Cell_DataType::TYPE_STRING); 


		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			
			$activeSheet->setCellValueExplicit('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))), PHPExcel_Cell_DataType::TYPE_STRING); 
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		foreach ($query->result() as $row) {
       		$day = date('D',strtotime($row->Date));
       		$holiday = $this->system->holiday_check($row->Date, $row->employee_id);
       		$array_stack = $this->system->get_employee_rest_day($row->employee_id,$row->Date);
       		$restday = false;
       		if (in_array($day, $array_stack)){
       			$restday = true;
       		}	
			$obt = get_form($row->employee_id, 'obt', $dummy_p, $row->Date, false);

			$remarks = "";
			if ($obt->num_rows() > 0)
				$remarks = "obt";

			// Check leave for whole day
			$this->db->select('duration_id, employee_leaves.employee_leave_id');
			$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
			$this->db->where('employee_id', $row->employee_id);
			$this->db->where('(\''. $row->Date . '\' BETWEEN date_from and date_to)', '', false);
			$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $row->Date . '\')', '',false);
			$this->db->where('form_status_id', 3);
			$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
			$this->db->where('employee_leaves.deleted', 0);

			$leave = $this->db->get('employee_leaves');
			if ($leave->num_rows() > 0)
				$remarks = "leave";

			$dtrp = get_form($row->employee_id, 'dtrp', $dummy_p, $row->Date, false);
			if ($dtrp->num_rows() > 0)
				$remarks = "dtrp";

			$out = get_form($row->employee_id, 'out', null, $row->Date, false);
			if ($out->num_rows() > 0)
				$remarks = "out";

			$et = get_form($row->employee_id, 'et', null, $row->Date, false);
			if ($et->num_rows() > 0)
				$remarks = "et";       				
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

	            $absent = 0;
	            if (!$holiday && !$restday){	            
		            if ($row->{'Hours Worked'} == 0 && number_format($row->{'OT(Hours)'} / 60,2) == 0){
						$absent = 8;
		            }
		            elseif ($row->{'Hours Worked'} <= 4 && $row->{'Hours Worked'} > 0){
		            	if ((number_format($row->{'Lates(Hours)'} / 60,2) + number_format($row->{'UT(Hours)'} / 60,2)) <= 4){
							$absent = 4;
		            	}
		            }
	        	}

	           	if ($field != "Absent" && $field != "Remarks"){
	           		if ($field == "Lates(Hours)" || $field == "UT(Hours)" || $field == "OT(Hours)"){

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, number_format($row->{$field} / 60,2), PHPExcel_Cell_DataType::TYPE_STRING); 
	           		}
	           		elseif ($field == "Date"){

	           			$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date($this->config->item('display_date_format')), PHPExcel_Cell_DataType::TYPE_STRING); 
	
					}
					else{

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 
					}
	           	}
	           	elseif ($field == "Absent"){
	           		$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $absent, PHPExcel_Cell_DataType::TYPE_STRING); 
	           	}
				elseif ($field == "Remarks"){

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $remarks, PHPExcel_Cell_DataType::TYPE_STRING); 
				}
				//$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});

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
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title("DTR Summary Report") . '.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}		
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>