<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_ecm extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists employee health information.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an employee health information.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an employee health information.';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {

		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/employee_contribution/listview';

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

		$company = array();
		$company[0] = "Please Select Company";

		$company_list = $this->db->get_where('user_company',array('deleted'=>0))->result();
		foreach( $company_list as $company_record ){
			$company[$company_record->company_id] = $company_record->company;
		}

		$data['company_list'] = $company;

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

		//$data['buttons'] = $this->module_link . '/detail-buttons';	

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

			//$data['buttons'] = $this->module_link . '/edit-buttons';

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
	function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			$data['updated_by']   = $this->userinfo['user_id'];
			$data['updated_date'] = date('Y-m-d H:i:s');

			if ($this->input->post('record_id') == '-1') {
				$data['created_by']   = $this->userinfo['user_id'];
				$data['created_date'] = date('Y-m-d H:i:s');
			}

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $data);
		}

		parent::after_ajax_save();
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";
		/*
		$buttons = "<div class='icon-label-group'>";                    
        $buttons .= "<div class='icon-label'><a class='icon-16-export module-export_2' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";        
        $buttons .= "</div>";
         */      
		return $buttons;
	}

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

		$this->db->select(''.$this->db->dbprefix('user_company_department').'.department'.' as "Department",CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",' . $this->db->dbprefix . 'user.middleinitial, " ",' . $this->db->dbprefix . 'user.lastname, " ",' . $this->db->dbprefix . 'user.aux) as "Full Name"', false);
		$this->db->select(''.$this->db->dbprefix. 'user.birth_date'.' as Birthdate,'.$this->db->dbprefix. 'employee.employed_date'.' as "Date Hired",'.$this->db->dbprefix. 'employee.regular_date'.' as "Reg Date"');
		$this->db->select(''.$this->db->dbprefix. 'user_company.company'.' as "Company"');
		$this->db->from('user');
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('user_company'),$this->db->dbprefix('user').'.company_id = '.$this->db->dbprefix('user_company').'.company_id',"left");
		$this->db->where('user.deleted = 0 AND '.$this->db->dbprefix. 'employee.ecf = 1 AND '.$this->db->dbprefix. 'employee.resigned = 0 AND '.$search);

		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('user_company.company_id ',$this->input->post('company'));

        $result = $this->db->get();

        $response->last_query = $this->db->last_query();
        
	    // die($this->db->last_query()); 
	    // dbug($this->db->last_query());
	    // return;

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

			$this->db->select(''.$this->db->dbprefix('user_company_department').'.department'.' as "Department",CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",' . $this->db->dbprefix . 'user.middleinitial, " ",' . $this->db->dbprefix . 'user.lastname, " ",' . $this->db->dbprefix . 'user.aux) as "Full Name"', false);
			$this->db->select(''.$this->db->dbprefix. 'user.birth_date'.' as Birthdate,'.$this->db->dbprefix. 'employee.employed_date'.' as "Date Hired",'.$this->db->dbprefix. 'employee.regular_date'.' as "Reg Date"');
			$this->db->select(''.$this->db->dbprefix. 'user_company.company'.' as "Company"');
			$this->db->from('user');
			$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
			$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
			$this->db->join($this->db->dbprefix('user_company'),$this->db->dbprefix('user').'.company_id = '.$this->db->dbprefix('user_company').'.company_id',"left");
			$this->db->where('user.deleted = 0 AND '.$this->db->dbprefix. 'employee.ecf = 1 AND '.$this->db->dbprefix. 'employee.resigned = 0 AND '.$search);

			if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('user_company.company_id ',$this->input->post('company'));

	        if ($this->input->post('sidx')) {
	        	if ($this->input->post('sidx') == 'firstname') 
	        	{
	        		$sidx = $this->db->dbprefix . 'user.lastname';
		            $sord = $this->input->post('sord');
	        	}
	        	else
	        	{	        		
		            $sidx = $this->input->post('sidx');
		            $sord = $this->input->post('sord');	
	        	}
	            $this->db->order_by($sidx . ' ' . $sord);
	        }
	        else
	        {
	        	$this->db->order_by( $this->db->dbprefix . 'user.lastname ASC');
	        }

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        
	        
	        $result = $this->db->get();
	        $ctr = 0;
	        foreach ($result->result() as $row) {

	            $response->rows[$ctr]['cell'][0] = $row->{'Full Name'};
	           // $response->rows[$ctr]['cell'][1] = $row->{'Company'};
	            $response->rows[$ctr]['cell'][1] = $row->{'Department'};
	            // $response->rows[$ctr]['cell'][2] = $row->{'Position'};
	            // $response->rows[$ctr]['cell'][3] = date('m-d-Y',strtotime($row->{'Birthdate'}));
	            // $response->rows[$ctr]['cell'][4] = date('m-d-Y',strtotime($row->{'Date Hired'}));
	            // $response->rows[$ctr]['cell'][5] = date('m-d-Y',strtotime($row->{'Reg Date'}));
	            // $response->rows[$ctr]['cell'][6] = $row->{'Rank Code'};
	            // $response->rows[$ctr]['cell'][7] = $row->{'Rank'};
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
		//$this->listview_column_names = array('Department', 'Full Name', 'Position', 'Birthdate', 'Date Hired', 'Reg Date', 'Rank Code', 'Rank');
		//$this->listview_column_names = array('Full Name', 'Company','Department');
		$this->listview_column_names = array('Full Name','Department');

		$this->listview_columns = array(
					
				array('name' => 'firstname', 'width' => '180','align' => 'left'),
				//array('name' => 'company', 'width' => '180','align' => 'left'),
				array('name' => 'department', 'width' => '180','align' => 'left')
				// array('name' => 'position'),
				// array('name' => 'birth_date'),
				// array('name' => 'employed_date'),
				// array('name' => 'regular_date'),
				// array('name' => 'job_rank'),
				// array('name' => 'job_rank_code')
				//array('name' => 'workshift')
			);                                     
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
		$search_string[] = $this->db->dbprefix .'user_company_department.department LIKE "%' . $value . '%"';
		//$search_string[] = $this->db->dbprefix .'user_company.company LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function export_list($record_id = 0)
	{	
	    $page = $this->input->post('search_page');
        $limit = 500; // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        
		
		if($this->input->post('search_str'))
		{
			if($this->input->post('search_field') == "all")
			{	
				$search = '('.$this->db->dbprefix .'user.firstname LIKE "%' .$this->input->post('search_str') . '%" OR '.$this->db->dbprefix .'user.lastname LIKE "%' . $this->input->post('search_str') . '%" OR '.$this->db->dbprefix .'user_company_department.department LIKE "%' . $this->input->post('search_str') . '%" )';
			} 
			else
			{
				$field = $this->input->post('search_field');
				$operator =  $this->input->post('search_option');
				$value =  $this->input->post('search_str');

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
						$search =  $field . ' = "'.$value.'"';
						break;
					case 'ne':
						$search =  $field . ' != "'.$value.'"';
						break;
					case 'lt':
						$search =  $field . ' < "'.$value.'"';
						break;
					case 'le':
						$search =  $field . ' <= "'.$value.'"';
						break;
					case 'gt':
						$search =  $field . ' > "'.$value.'"';
						break;
					case 'ge':
						$search =  $field . ' >= "'.$value.'"';
						break;
					case 'bw':
						$search =  $field . ' REGEXP "^'. $value .'"';
						break;
					case 'bn':
						$search =  $field . ' NOT REGEXP "^'. $value .'"';
						break;
					case 'in':
						$search =  $field . ' IN ('. $value .')';
						break;
					case 'ni':
						$search =  $field . ' NOT IN ('. $value .')';
						break;
					case 'ew':
						$search =  $field . ' LIKE "%'. $value  .'"';
						break;
					case 'en':
						$search =  $field . ' NOT LIKE "%'. $value  .'"';
						break;
					case 'cn':
						$search =  $field . ' LIKE "%'. $value .'%"';
						break;
					case 'nc':
						$search =  $field . ' NOT LIKE "%'. $value .'%"';
						break;
					default:
						$search =  $field . ' LIKE %'. $value .'%';
				}
			}
		}
		else
		{
			$search = 1;	
		}

		$this->db->select(''.$this->db->dbprefix('user_company_department').'.department'.' as "Department",CONCAT(' . $this->db->dbprefix . 'user.lastname, ", ",' . $this->db->dbprefix . 'user.firstname) as "Full_Name"', false);
		$this->db->select(''.$this->db->dbprefix. 'user.birth_date'.' as Birthdate,'.$this->db->dbprefix. 'employee.employed_date'.' as "Date Hired",'.$this->db->dbprefix. 'employee.regular_date'.' as "Reg Date"');
		$this->db->from('user');
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('user_company'),$this->db->dbprefix('user').'.company_id = '.$this->db->dbprefix('user_company').'.company_id',"left");
		$this->db->where('user.deleted = 0 AND '.$this->db->dbprefix. 'employee.ecf = 1 AND '.$this->db->dbprefix. 'employee.resigned = 0 AND '.$search);

		if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('user_company.company_id ',$this->input->post('company'));

        if ($this->input->post('sidx')) 
        {
        	if ($this->input->post('sidx') == 'firstname') 
        	{
        		$sidx = $this->db->dbprefix . 'user.lastname';
	            $sord = $this->input->post('sord');
        	}
        	else
        	{	        		
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');	
        	}
            $this->db->order_by($sidx . ' ' . $sord);
        }
        else
        {
        	$this->db->order_by( $this->db->dbprefix . 'user.lastname ASC');
        }

        $start = $limit * $page - $limit;
        $this->db->limit($limit, $start);

		$q = $this->db->get();
		// dbug($this->db->last_query());
		$query  = $q;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Employee Contribution Member Report")
		            ->setDescription("Employee Contribution Member Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$activeSheet->setCellValue('A1', 'Employee Contribution Member');
		$activeSheet->setCellValue('A3', 'Full Name');
		$activeSheet->setCellValue('B3', 'Department');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('B3')->applyFromArray($styleArray);
		// contents.
		$line = 4;
		foreach ($query->result() as $row) 
		{
			$activeSheet->setCellValue('A'.$line, $row->Full_Name);	
			$activeSheet->setCellValue('B'.$line, $row->Department);	
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
		header('Content-Disposition: attachment;filename=' . date('Y-m-d').'-'.url_title("Employee Contribution Member Report").'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */