<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Headcount_summary extends MY_Controller
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
    	$data['scripts'][] = chosen_script();
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'recruitment/analytics/amp_listview';

		//$data['jqgrid'] = '';//'employees/appraisal/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$data['departments'] = $this->db->get_where('user_company_department', array('deleted' => 0))->result_array();
		$data['companies'] = $this->db->get_where('user_company', array('deleted' => 0))->result_array();
		$data['divisions'] = $this->db->get_where('user_company_division', array('deleted' => 0))->result_array();
		$data['positions'] = $this->db->get_where('user_position', array('deleted' => 0))->result_array();

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

		$search = 1;			
		$dbprefix = $this->db->dbprefix;
		$division_id = $this->input->post('division');

		$qry = "SELECT division, division_id FROM {$dbprefix}user_company_division WHERE deleted = 0";

		if($division_id && $division_id != 'null') {
          $qry .= " AND division_id IN (".implode(',', $division_id).")";
        }

        $result = $this->db->query($qry);   
     
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

			$qry = "SELECT division, division_id FROM {$dbprefix}user_company_division WHERE deleted = 0";

			if($division_id && $division_id != 'null') {
	          $qry .= " AND division_id IN (".implode(',', $division_id).")";
	        }

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        

			$result = $this->db->query($qry);	
			
			$numbers = array();
						
			$ctr = 0;	
	        
	        $year = date('Y');
		    

		   	if ($this->input->post('year')){
				 $year = $this->input->post('year');			
			}

			$prev_year = $year - 1;

	        foreach ($result->result() as $row) { 
	        	
				$existing = $this->get_incumbent($prev_year, $row->division_id);
				$budgeted 		  = $this->get_budgeted($prev_year, $row->division_id);
				$additional_count = $this->get_budgeted($year, $row->division_id);

		        $response->rows[$ctr]['cell'][0] = $row->division;
		        $response->rows[$ctr]['cell'][1] = $existing;
		        $response->rows[$ctr]['cell'][2] = ($budgeted) ? $budgeted : 0;
		        $response->rows[$ctr]['cell'][3] = ($budgeted - $existing);
				$response->rows[$ctr]['cell'][4] = ($additional_count) ? $additional_count : 0 ;
				$response->rows[$ctr]['cell'][5] = ($budgeted + $additional_count);
				$ctr++;

	        }

	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}


	function get_incumbent($year, $division_id)
	{
		$existing_sql = "SELECT * FROM {$this->db->dbprefix}annual_manpower_planning a
								  LEFT JOIN {$this->db->dbprefix}annual_manpower_planning_details pd
								    ON a.annual_manpower_planning_id = pd.annual_manpower_planning_id 
								   	WHERE a.year = '".$year."' AND a.division_id = ". $division_id . " AND a.deleted = 0";

		$existing_pos = $this->db->query($existing_sql);
		 
		$existing = ($existing_pos && $existing_pos->num_rows() > 0) ? $existing_pos->num_rows() : 0 ;

		return $existing;
	}

	function get_budgeted($year, $division_id)
	{
		$budgeted_sql = "SELECT SUM(total) AS budgeted FROM {$this->db->dbprefix}annual_manpower_planning a
								  LEFT JOIN {$this->db->dbprefix}annual_manpower_planning_position pp
								    ON a.annual_manpower_planning_id = pp.annual_manpower_planning_id 
								   	WHERE a.year = '".$year."'AND a.division_id = ". $division_id . " AND a.deleted = 0";

		$budgeted_pos = $this->db->query($budgeted_sql);

		$budgeted = ($budgeted_pos && $budgeted_pos->num_rows() > 0) ? $budgeted_pos->row()->budgeted : 0 ;

		return $budgeted;
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
    	$prev_year = date('Y')-1;
    	$curr_year = date('Y');
    	$dec31   = $prev_year. '-' . date('12-31');

    	$last_year = date('F d, Y', strtotime($dec31));
   
   		$this->listview_column_names = array('Division', 
   											 'Headcount as <br> of December 31, <span class='."last-year".'> '.$prev_year.'</span>', 
   											 'Budgeted <br> <span class='."last-year".'>'.$prev_year.'</span>', 
   											 'Carry-over from <span class='."last-year".'>'.$prev_year.'</span> <br> To hire <span class='."curr-year".'> '.$curr_year.'</span>', 
   											 'Additional Headcount <br> To hire in <span class='."curr-year".'>'.$curr_year.'</span>', 
   											 'Projected <br> <span class='."curr-year".'>'.$curr_year.'</span>');

		$this->listview_columns = array(
				array('name' => 'division'),
				array('name' => 'prev_headcount'),
				array('name' => 'budgeted'),
				array('name' => 'carry_over'),
				array('name' => 'additional'),
				array('name' => 'projected')
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

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Assessment Report")
		            ->setDescription("Assessment Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;
		$dbprefix = $this->db->dbprefix;
		//Default column width
		for ($col = 'A'; $col != 'J'; $col++) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		}		

		//Initialize style
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

		$year = date('Y');
		if ($this->input->post('year')){
		    $year = $this->input->post('year');
		}

	    $prev_year = $year-1;
	    $curr_year = $year;
	    $dec31   = $prev_year. '-' . date('12-31');

	    $last_year = date('F d, Y', strtotime($dec31));

		$fields = array('Division', 
							'Headcount as of '.$last_year, 
							'Budgeted '.$prev_year, 
							'Carry-over from '.$prev_year.' To hire '.$curr_year, 
							'Additional Headcount  To hire in '.$curr_year, 
							'Projected '.$curr_year);

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
		
		$activeSheet->setCellValue('A1', 'OHI  Headcount  Summary');
		$activeSheet->setCellValue('A2', $year.' Budget');

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);

		// contents.
		$line = 7;

		$division_id = $this->input->post('division');

		$qry = "SELECT division, division_id FROM {$dbprefix}user_company_division WHERE deleted = 0";

		if($division_id && $division_id != 'null') {
          $qry .= " AND division_id IN (".implode(',', $division_id).")";
        }

        $results = $this->db->query($qry);  

		$fields2 = array('division', 'prev_headcount', 'budgeted', 'carry_over', 'additional', 'projected');
		
		foreach ($results->result() as $key => $result) {
			$sub_ctr   = 0;			
			$alpha_ctr = 0;

			$existing = $this->get_incumbent($prev_year, $result->division_id);
			$budgeted 		  = $this->get_budgeted($prev_year, $result->division_id);
			$additional_count = $this->get_budgeted($year, $result->division_id);


			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $result->division);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $existing);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, ($budgeted) ? $budgeted : '0');
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, ($budgeted - $existing));
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, ($additional_count) ? $additional_count : 0 );
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $line, ($budgeted + $additional_count));

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
		header('Content-Disposition: attachment;filename=' . url_title("Headcount-Summary") .  date('Y-m-d') .'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	
	}

	// END custom module funtions
	
	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		 $buttons = '';
                
		return $buttons;
	}

}

/* End of file */
/* Location: system/application */
?>