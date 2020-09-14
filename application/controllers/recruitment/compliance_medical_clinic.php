<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Compliance_medical_clinic extends MY_Controller
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
		//$data['content'] = 'employee/dtr_summary/listview';
		$data['content'] = 'recruitment/compliance_medical_clinic_listview';

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

		$this->db->select('CONCAT(ra.firstname, " ",ra.lastname) as "Applicant Name"', false);
		$this->db->select('schedule_date as "Date Schedule",date_of_completion as "Date of Completion",clinic_name as "Name of Clinic",health_type_status as "Status",remarks as "Remarks"');
		$this->db->from('recruitment_manpower_candidates_scheduler rmcs');
		$this->db->join($this->db->dbprefix('recruitment_applicant ra'),'ra.applicant_id = rmcs.candidate_id');
		$this->db->join($this->db->dbprefix('recruitment_manpower_candidates_schedule_type sched'),'sched.schedule_type_id = rmcs.schedule_type_id');
		$this->db->join($this->db->dbprefix('employee emp'),'ra.applicant_id = emp.applicant_id','left');
		$this->db->join($this->db->dbprefix('employee_health eh'),'emp.employee_id = eh.employee_id','left');
		$this->db->join($this->db->dbprefix('employee_health_type_status ehts'),'eh.health_type_status_id = ehts.health_type_status_id','left');
		$this->db->where('rmcs.schedule_type_id = 2');	
		$this->db->where('rmcs.deleted = 0 AND '.$search);	

		if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
			$this->db->where('(DATE(rmcs.schedule_date) BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" )');
		}

        $result = $this->db->get();   
        //$response->last_query = $this->db->last_query();
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
	        $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $result->num_rows();                        

	        $response->msg = "";

			$this->db->select('CONCAT(ra.firstname, " ",ra.lastname) as "Applicant Name"', false);
			$this->db->select('schedule_date as "Date Schedule",date_of_completion as "Date of Completion",clinic_name as "Name of Clinic",health_type_status as "Status",remarks as "Remarks"');
			$this->db->from('recruitment_manpower_candidates_scheduler rmcs');
			$this->db->join($this->db->dbprefix('recruitment_applicant ra'),'ra.applicant_id = rmcs.candidate_id');
			$this->db->join($this->db->dbprefix('recruitment_manpower_candidates_schedule_type sched'),'sched.schedule_type_id = rmcs.schedule_type_id');
			$this->db->join($this->db->dbprefix('employee emp'),'ra.applicant_id = emp.applicant_id','left');
			$this->db->join($this->db->dbprefix('employee_health eh'),'emp.employee_id = eh.employee_id','left');
			$this->db->join($this->db->dbprefix('employee_health_type_status ehts'),'eh.health_type_status_id = ehts.health_type_status_id','left');
			$this->db->where('rmcs.schedule_type_id = 2');	
			$this->db->where('rmcs.deleted = 0 AND '.$search);		

			if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
				$this->db->where('(DATE(rmcs.schedule_date) BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" )');
			}

	        if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');
	            $this->db->order_by($sidx . ' ' . $sord);
	        }

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        
	        
	        $result = $this->db->get();
	        $ctr = 0;        
	        foreach ($result->result() as $row) {
	            $response->rows[$ctr]['cell'][0] = $row->{'Applicant Name'};
	            $response->rows[$ctr]['cell'][1] = ($row->{'Date Schedule'} != NULL && $row->{'Date Schedule'} != '' ? date($this->config->item('display_date_format'),strtotime($row->{'Date Schedule'})) : '');
	            $response->rows[$ctr]['cell'][2] = ($row->{'Date of Completion'} != NULL && $row->{'Date of Completion'} != '' ? date($this->config->item('display_date_format'),strtotime($row->{'Date of Completion'})) : '');
	            $response->rows[$ctr]['cell'][3] = $row->{'Name of Clinic'};
	            $response->rows[$ctr]['cell'][4] = $row->{'Status'};
	            $response->rows[$ctr]['cell'][5] = $row->{'Remarks'};
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
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

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Applicant Name', 'Date Schedule', 'Date of Completion', 'Name of Clinic', 'Status', 'Remarks'); //, 'Work Shift'

		$this->listview_columns = array(
				array('name' => 'applicant_name', 'width' => '180','align' => 'center'),				
				array('name' => 'date_schedule'),
				array('name' => 'date_completion'),
				array('name' => 'name_clinic'),
				array('name' => 'status'),
				array('name' => 'remarks')
			);                                     
    }

	function export() {	
		$this->_excel_export();
	}

	// export called using ajax
	function excel_ajax_export()
	{	
		ini_set('memory_limit', "512M");

		$this->db->select('CONCAT(ra.firstname, " ",ra.lastname) as "Applicant Name"', false);
		$this->db->select('schedule_date as "Date Schedule",date_of_completion as "Date of Completion",clinic_name as "Name of Clinic",health_type_status as "Status",remarks as "Remarks"');
		$this->db->from('recruitment_manpower_candidates_scheduler rmcs');
		$this->db->join($this->db->dbprefix('recruitment_applicant ra'),'ra.applicant_id = rmcs.candidate_id');
		$this->db->join($this->db->dbprefix('recruitment_manpower_candidates_schedule_type sched'),'sched.schedule_type_id = rmcs.schedule_type_id');
		$this->db->join($this->db->dbprefix('employee emp'),'ra.applicant_id = emp.applicant_id','left');
		$this->db->join($this->db->dbprefix('employee_health eh'),'emp.employee_id = eh.employee_id','left');
		$this->db->join($this->db->dbprefix('employee_health_type_status ehts'),'eh.health_type_status_id = ehts.health_type_status_id','left');
		$this->db->where('rmcs.schedule_type_id = 2');	
		$this->db->where('rmcs.deleted = 0');	

		if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
			$this->db->where('(DATE(rmcs.schedule_date) BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" )');
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

		$objPHPExcel->getProperties()->setTitle("Compliance Medical Report")
		            ->setDescription("Compliance Medical Report");
		               
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
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
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

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', 'Pioneer Insurance');
		$activeSheet->setCellValue('A2', 'Compliance Medical Report');
		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			$activeSheet->setCellValue('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		foreach ($query->result() as $row) {
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

	 			if ($field == "Date Schedule" && $field == "Date of Completion"){
	 				$date = ($row->{$field} != NULL && $row->{$field} != '' ? date($this->config->item('display_date_format'),strtotime($row->{$field})) : '');
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $date);
				}
				else{
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});
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
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title("Compliance Medical Report") . '.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/medical/'.url_title("Compliance Medical Report").'-'.strtotime(date('Y-m-d g:i:s')).'.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));

		
	}		
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>