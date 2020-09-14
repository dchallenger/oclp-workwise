<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Roster1 extends MY_Controller
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
    	// dbug('orig');
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/roster1/listview';

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

		$this->db->order_by('department');
		$department = $this->db->get('user_company_department')->result_array();

		$data['department'] = $department;

		if (!$this->superadmin){
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
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

/*		$subordinates = 0;
		if ($this->userinfo['login'] != "superadmin"){
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');	
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}*/

		//set Search Qry string
/*		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;*/
		
		$search = 1;			

		$this->db->select(''.$this->db->dbprefix('user_company_department').'.department'.' as "Department",CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as "Full Name"', false);
		$this->db->select(''.$this->db->dbprefix. 'user_position.position'.' as "Position",'.$this->db->dbprefix. 'user.birth_date'.' as Birthdate,'.$this->db->dbprefix. 'employee.employed_date'.' as "Date Hired",'.$this->db->dbprefix. 'employee.regular_date'.' as "Reg Date"');
		$this->db->select(''.$this->db->dbprefix. 'user_rank_code.job_rank_code'.' as "Rank Code",'.$this->db->dbprefix. 'user_rank.job_rank'.' as Rank');
		$this->db->from('user');
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('user_position'),$this->db->dbprefix('user').'.position_id = '.$this->db->dbprefix('user_position').'.position_id',"left");		
		$this->db->join($this->db->dbprefix('user_rank_code'),$this->db->dbprefix('employee').'.rank_code = '.$this->db->dbprefix('user_rank_code').'.job_rank_code_id',"left");		
		$this->db->join('user_rank','employee.rank_id = user_rank.job_rank_id','left');
		$this->db->where('employee.resigned', 0);
		$this->db->where('user.inactive', 0);
		$this->db->where('user.deleted = 0 AND '.$search);	

		if ($this->userinfo['login'] != "superadmin"){
			$this->db->where_in($this->db->dbprefix('user_company_department').'.department_id ', $this->input->post('department'));
		}
		else{
			if ($this->input->post('department') || $this->input->post('department') != ''){
				$this->db->where_in($this->db->dbprefix('user_company_department').'.department_id ',$this->input->post('department'));
				//$this->db->where($this->db->dbprefix('user_company_department').'.department_id ', $this->input->post('department'));
			}
		}

        $result = $this->db->get();   

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

			$this->db->select(''.$this->db->dbprefix('user_company_department').'.department'.' as "Department",CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as "Full Name"', false);
			$this->db->select(''.$this->db->dbprefix. 'user_position.position'.' as "Position",'.$this->db->dbprefix. 'user.birth_date'.' as Birthdate,'.$this->db->dbprefix. 'employee.employed_date'.' as "Date Hired",'.$this->db->dbprefix. 'employee.regular_date'.' as "Reg Date"');
			$this->db->select(''.$this->db->dbprefix. 'user_rank_code.job_rank_code'.' as "Rank Code",'.$this->db->dbprefix. 'user_rank.job_rank'.' as Rank');
			$this->db->from('user');
			$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
			$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
			$this->db->join($this->db->dbprefix('user_position'),$this->db->dbprefix('user').'.position_id = '.$this->db->dbprefix('user_position').'.position_id',"left");		
			$this->db->join($this->db->dbprefix('user_rank_code'),$this->db->dbprefix('employee').'.rank_code = '.$this->db->dbprefix('user_rank_code').'.job_rank_code_id',"left");		
			$this->db->join('user_rank','employee.rank_id = user_rank.job_rank_id','left');
			$this->db->where('employee.resigned', 0);
			$this->db->where('user.inactive', 0);
			$this->db->where('user.deleted = 0 AND '.$search);	

			if ($this->userinfo['login'] != "superadmin"){
				$this->db->where_in($this->db->dbprefix('user_company_department').'.department_id ', $this->input->post('department'));
			}
			else{
				if ($this->input->post('department') || $this->input->post('department') != ''){
					$this->db->where_in($this->db->dbprefix('user_company_department').'.department_id ',$this->input->post('department'));
					//$this->db->where($this->db->dbprefix('user_company_department').'.department_id ', $this->input->post('department'));
				}
			}

	        if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');
	            $this->db->order_by($sidx . ' ' . $sord);
	        }
	        else{
	        	$this->db->order_by('rank_index DESC');
	        }

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        
	        
	        $result = $this->db->get();


/*	        dbug( $this->db->last_query());
	        return;*/

	        $ctr = 0;
	        foreach ($result->result() as $row) {

	            $response->rows[$ctr]['cell'][0] = $row->{'Department'};
	            $response->rows[$ctr]['cell'][1] = $row->{'Full Name'};
	            $response->rows[$ctr]['cell'][2] = $row->{'Position'};

	            if( $row->{'Birthdate'} ){
	            	$response->rows[$ctr]['cell'][3] = date($this->config->item('display_date_format'),strtotime($row->{'Birthdate'}));
	            }
	            else{
	            	$response->rows[$ctr]['cell'][3] = "";
	            }

	            if( $row->{'Date Hired'} ){
	            	$response->rows[$ctr]['cell'][4] = date($this->config->item('display_date_format'),strtotime($row->{'Date Hired'}));
	            }
	            else{
	            	$response->rows[$ctr]['cell'][4] = "";
	            }

	            if( $row->{'Reg Date'} ){
	            	$response->rows[$ctr]['cell'][5] = date($this->config->item('display_date_format'),strtotime($row->{'Reg Date'}));
	            }
	            else{
	            	$response->rows[$ctr]['cell'][5] = "";
	            }

	            $response->rows[$ctr]['cell'][6] = $row->{'Rank Code'};
	            $response->rows[$ctr]['cell'][7] = $row->{'Rank'};
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Department', 'Full Name', 'Position', 'Birthdate', 'Date Hired', 'Reg Date', 'Rank Code', 'Rank');

		$this->listview_columns = array(
				array('name' => 'department', 'width' => '180','align' => 'center'),				
				array('name' => 'firstname'),
				array('name' => 'position'),
				array('name' => 'birth_date'),
				array('name' => 'employed_date'),
				array('name' => 'regular_date'),
				array('name' => 'job_rank'),
				array('name' => 'job_rank_code')
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
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{		
		// dbug('orig');
		$search = 1;

		$this->db->select(''.$this->db->dbprefix('user_company_department').'.department'.' as "Department",CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as "Full Name"', false);
		$this->db->select(''.$this->db->dbprefix. 'user_position.position'.' as "Position",'.$this->db->dbprefix. 'user.birth_date'.' as Birthdate,'.$this->db->dbprefix. 'employee.employed_date'.' as "Date Hired",'.$this->db->dbprefix. 'employee.regular_date'.' as "Reg Date"');
		$this->db->select(''.$this->db->dbprefix. 'user_rank_code.job_rank_code'.' as "Rank Code",'.$this->db->dbprefix. 'user_rank.job_rank'.' as Rank');
		$this->db->from('user');
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('user_position'),$this->db->dbprefix('user').'.position_id = '.$this->db->dbprefix('user_position').'.position_id',"left");		
		$this->db->join($this->db->dbprefix('user_rank_code'),$this->db->dbprefix('employee').'.rank_code = '.$this->db->dbprefix('user_rank_code').'.job_rank_code_id',"left");		
		$this->db->join('user_rank','employee.rank_id = user_rank.job_rank_id','left');
		$this->db->where('employee.resigned', 0);
		$this->db->where('user.inactive', 0);
		$this->db->where('user.deleted = 0 AND '.$search);	

		if ($this->userinfo['login'] != "superadmin"){
			$this->db->where_in($this->db->dbprefix('user_company_department').'.department_id ', $this->input->post('department'));
		}
		else{
			if ($this->input->post('department') || $this->input->post('department') != ''){
				$this->db->where_in($this->db->dbprefix('user_company_department').'.department_id ',$this->input->post('department'));
				//$this->db->where($this->db->dbprefix('user_company_department').'.department_id ', $this->input->post('department'));
			}
		}

        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            $this->db->order_by($sidx . ' ' . $sord);
        }
        else{
        	$this->db->order_by('rank_index DESC');
        }         

		$q = $this->db->get();

		$query  = $q;
		$fields = $q->list_fields();

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Employee Roster Report")
		            ->setDescription("Employee Roster Report");
		               
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
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);	
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

		$HorizontalLeft = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);				

		$HorizontalCenter = array(
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

		$activeSheet->setCellValue('A1', $this->config->item('title','meta'));
		$activeSheet->setCellValue('A2', 'Employee Roster Report');

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

           		if ($field == "Birthdate" || $field == "Date Hired" || $field == "Reg Date"){
           			if( $row->{$field} ){
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line,date($this->config->item('display_date_format'),strtotime($row->{$field})));
           			}
           			else{
           				$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line,"");
           			}
           		}
           		else{
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});
				}

				$alpha_ctr++;
			}

			$line++;
		}

		$objPHPExcel->getActiveSheet()->getStyle('D7:D'.$line)->applyFromArray($HorizontalCenter);
		$objPHPExcel->getActiveSheet()->getStyle('E7:E'.$line)->applyFromArray($HorizontalCenter);
		$objPHPExcel->getActiveSheet()->getStyle('F7:F'.$line)->applyFromArray($HorizontalCenter);
		$objPHPExcel->getActiveSheet()->getStyle('G7:G'.$line)->applyFromArray($HorizontalCenter);
		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.date('Y-m-d').'Employee_Roster_Report.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}		
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>