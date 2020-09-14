<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Amp_report extends MY_Controller
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
		$data['content'] = 'recruitment/analytics/budgeted_listview';

		$data['jqgrid'] = 'recruitment/analytics/jqgrid';//'employees/appraisal/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$data['departments'] = $this->db->get_where('user_company_department', array('deleted' => 0))->result_array();
		$data['levels']    = $this->db->get_where('user_rank', array('deleted' => 0))->result_array();
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
		$rank_id = $this->input->post('rank');

		$qry = "SELECT job_rank_id, job_rank FROM {$dbprefix}user_rank WHERE deleted = 0";

		if($rank_id && $rank_id != 'null') {
          $qry .= " AND job_rank_id IN (".implode(',', $rank_id).")";
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

			$qry = "SELECT job_rank_id, job_rank FROM {$dbprefix}user_rank WHERE deleted = 0";

			if($rank_id && $rank_id != 'null') {
	          $qry .= " AND job_rank_id IN (".implode(',', $rank_id).")";
	        }

            $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        

			$result = $this->db->query($qry);	
			
	        $year = date('Y');
		    

		   	if ($this->input->post('year')){
				 $year = $this->input->post('year');			
			}

			$prev_year = $year - 1;

			$incumbent_scd = $this->get_incumbent($year, 6);
			$incumbent_red = $this->get_incumbent($year, 7);
			$incumbent_crd = $this->get_incumbent($year, 8);

			$existing_scd = $this->get_budgeted($year, 6 , 2);
			$existing_red = $this->get_budgeted($year, 7, 2);
			$existing_crd = $this->get_budgeted($year, 8, 2);

			$new_scd = $this->get_budgeted($year, 6 , 1);
			$new_red = $this->get_budgeted($year, 7, 1);
			$new_crd = $this->get_budgeted($year, 8, 1);

			$total_scd = array();
			$total_red = array();
			$total_crd = array();

			$response->rows[0]['cell'][0] = '<strong>Existing Personnel as of '. $year . '</strong>';
			$ctr = 1;
	        foreach ($result->result() as $row) { 
	        	$approve_hc = $incumbent_scd[$row->job_rank_id]+$incumbent_red[$row->job_rank_id]+$incumbent_crd[$row->job_rank_id];
	        	
	        	$response->rows[$ctr]['cell'][0] = $row->job_rank;
		       	$response->rows[$ctr]['cell'][1] = ($incumbent_scd[$row->job_rank_id]) ? $incumbent_scd[$row->job_rank_id] : 0 ;
		       	$response->rows[$ctr]['cell'][2] = ($incumbent_red[$row->job_rank_id]) ? $incumbent_red[$row->job_rank_id] : 0 ;
		       	$response->rows[$ctr]['cell'][3] = ($incumbent_crd[$row->job_rank_id]) ? $incumbent_crd[$row->job_rank_id] : 0 ;
		       	$response->rows[$ctr]['cell'][4] = $approve_hc;

		       	$total_scd[$row->job_rank_id][] = $incumbent_scd[$row->job_rank_id];
		       	$total_red[$row->job_rank_id][] = $incumbent_red[$row->job_rank_id];
		       	$total_crd[$row->job_rank_id][] = $incumbent_crd[$row->job_rank_id];
				
				$ctr++;

	        }
	    	
	    	$response->rows[$ctr]['cell'][0] = '<strong>Projected New Personnel <br><em>(current position)</em></strong>';
	    	$ctr = $ctr + 1;
	    	foreach ($result->result() as $row) { 
	    		$approve_hc = $existing_scd[$row->job_rank_id]+$existing_red[$row->job_rank_id]+$existing_crd[$row->job_rank_id];
		        
		        $response->rows[$ctr]['cell'][0] = $row->job_rank;
		        $response->rows[$ctr]['cell'][1] = ($existing_scd[$row->job_rank_id]) ? $existing_scd[$row->job_rank_id] : 0 ;
		        $response->rows[$ctr]['cell'][2] = ($existing_red[$row->job_rank_id]) ? $existing_red[$row->job_rank_id] : 0 ;
		        $response->rows[$ctr]['cell'][3] = ($existing_crd[$row->job_rank_id]) ? $existing_crd[$row->job_rank_id] : 0 ;
		        $response->rows[$ctr]['cell'][4] = $approve_hc;

		        $total_scd[$row->job_rank_id][] = $existing_scd[$row->job_rank_id];
		        $total_red[$row->job_rank_id][] = $existing_red[$row->job_rank_id];
		       	$total_crd[$row->job_rank_id][] = $existing_crd[$row->job_rank_id];
				$ctr++;

	        }

	        $response->rows[$ctr]['cell'][0] = '<strong>Projected New Personnel <br><em>(new position)</em></strong>';
	        $ctr = $ctr + 1;
	    	foreach ($result->result() as $row) { 
	        	
				$approve_hc = $new_scd[$row->job_rank_id]+$new_red[$row->job_rank_id]+$new_crd[$row->job_rank_id];
		        
		        $response->rows[$ctr]['cell'][0] = $row->job_rank;
		        $response->rows[$ctr]['cell'][1] = ($new_scd[$row->job_rank_id]) ? $new_scd[$row->job_rank_id] : 0 ;
		        $response->rows[$ctr]['cell'][2] = ($new_red[$row->job_rank_id]) ? $new_red[$row->job_rank_id] : 0 ;
		        $response->rows[$ctr]['cell'][3] = ($new_crd[$row->job_rank_id]) ? $new_crd[$row->job_rank_id] : 0 ;
		        $response->rows[$ctr]['cell'][4] = $approve_hc;

		        $total_scd[$row->job_rank_id][] = $new_scd[$row->job_rank_id];
		        $total_red[$row->job_rank_id][] = $new_red[$row->job_rank_id];
		       	$total_crd[$row->job_rank_id][] = $new_crd[$row->job_rank_id];
				$ctr++;

	        }	
	        
	        $response->rows[$ctr]['cell'][0] = '<strong>Total Budgeted Headcount for the Year</strong>';
	        $ctr = $ctr + 1;
	    	foreach ($result->result() as $row) { 
	        	$total_approved = array_sum($total_scd[$row->job_rank_id]) + array_sum($total_red[$row->job_rank_id]) + array_sum($total_crd[$row->job_rank_id]);

				$response->rows[$ctr]['cell'][0] = $row->job_rank;
		        $response->rows[$ctr]['cell'][1] = (array_sum($total_scd[$row->job_rank_id]) > 0) ? array_sum($total_scd[$row->job_rank_id]) : 0 ;
		        $response->rows[$ctr]['cell'][2] = (array_sum($total_red[$row->job_rank_id]) > 0) ? array_sum($total_red[$row->job_rank_id]) : 0 ;
		        $response->rows[$ctr]['cell'][3] = (array_sum($total_crd[$row->job_rank_id]) > 0) ? array_sum($total_crd[$row->job_rank_id]) : 0 ;
		        $response->rows[$ctr]['cell'][4] = $total_approved;

				$ctr++;

	        }	


	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function get_incumbent($year, $division_id)
	{
		/*$existing_sql = "SELECT * FROM {$this->db->dbprefix}annual_manpower_planning a
						LEFT JOIN {$this->db->dbprefix}annual_manpower_planning_details pd
						  ON a.annual_manpower_planning_id = pd.annual_manpower_planning_id 
					   	WHERE a.year = '".$year."' AND a.division_id = ". $division_id . " AND a.deleted = 0 ";*/
					   		
		$existing_sql = "SELECT * FROM {$this->db->dbprefix}user u 
							JOIN {$this->db->dbprefix}employee e ON u.employee_id = e.employee_id
							WHERE u.division_id = ". $division_id . " AND YEAR(e.employed_date) <= " .$year;

		if ($this->input->post('rank') && $this->input->post('rank') != "null") {
			$existing_sql .= " AND e.rank_id IN(".implode(',', $this->input->post('rank')).")";
		}

		if ($this->input->post('company') && $this->input->post('company') != "null") {
			$existing_sql .= " AND u.company_id IN(".implode(',', $this->input->post('company')).")";	
		}

		$existing_pos = $this->db->query($existing_sql);

		$levels = array();
		$ranks = $this->db->get_where('user_rank', array('deleted' => 0))->result_array();
		foreach ($ranks as $key => $value) {
			$levels[] = $value['job_rank_id'];
		}

		$tmp_arr = array();
		if ($existing_pos && $existing_pos->num_rows() > 0) {
			foreach ($existing_pos->result() as $amp) {
				$tmp_arr[] = $amp->rank_id;					
			}

		}
		$rank_arr = array_count_values($tmp_arr);

		$job_ranks = array();
		foreach ($levels as $key) {
			$job_ranks[$key] = $rank_arr[$key];
			
		}

		return $job_ranks;
	}

	function get_budgeted($year, $division_id , $type)
	{
		$existing_sql = "SELECT * FROM {$this->db->dbprefix}annual_manpower_planning a
							LEFT JOIN {$this->db->dbprefix}annual_manpower_planning_position pd
   								ON a.annual_manpower_planning_id = pd.annual_manpower_planning_id 
							LEFT JOIN {$this->db->dbprefix}annual_manpower_planning_ranks pr
							  	ON pr.annual_manpower_planning_position_id = pd.annual_manpower_planning_position_id
							WHERE a.year = '".$year."' AND a.division_id = ". $division_id . " AND a.deleted = 0 AND pd.type = ".$type;

		if ($this->input->post('rank')) {
			$existing_sql .= " AND pr.rank_id IN(".implode(',', $this->input->post('rank')).")";
		}

		if ($this->input->post('company')) {
			$existing_sql .= " AND a.company_id IN(".implode(',', $this->input->post('company')).")";	
		}
		$existing_pos = $this->db->query($existing_sql);

		$levels = array();
		$ranks = $this->db->get_where('user_rank', array('deleted' => 0))->result_array();
		foreach ($ranks as $key => $value) {
			$levels[] = $value['job_rank_id'];
		}

		$tmp_arr = array();
		if ($existing_pos && $existing_pos->num_rows() > 0) {
			foreach ($existing_pos->result() as $amp) {
				$rank_details = json_decode($amp->details, true);
				foreach ($rank_details['rank_count'] as $rank => $count) {
					$tmp_arr[$rank][] = $count;
				}				
			}

		}

		$job_ranks = array();
		foreach ($levels as $key) {
			$job_ranks[$key] = array_sum($tmp_arr[$key]);
			
		}

		return $job_ranks;
	}


    function _set_listview_query($listview_id = '', $view_actions = true) {
   		$this->listview_column_names = array("Level", "SCD", "RED", "CRD", "Approved Head Count");

		$this->listview_columns = array(
				array('name' => 'name', 'align' => 'left'),
				array('name' => 'scd'),
				array('name' => 'red'),
				array('name' => 'crd'),
				array('name' => 'company')
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

		$objPHPExcel->getProperties()->setTitle("Headcount Budget Report")
		            ->setDescription("Headcount Budget Report");
		               
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
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
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

		$fields = array("Level", "SCD", "RED", "CRD", "Approved Head Count");
		$activeSheet->setCellValue('A'. '5', 'Existing Personnel as of 2014');
		$objPHPExcel->getActiveSheet()->getStyle('A' . '5')->applyFromArray($styleArray);

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

		$year = date('Y');
		
	   	if ($this->input->post('year')){
			 $year = $this->input->post('year');			
		}

		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');
		
		$activeSheet->setCellValue('A1', 'OCLP HOLDINGS, INC.');
		$activeSheet->setCellValue('A2', 'HEADCOUNT BUDGET FOR YEAR'. $year);

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		$companies = implode(',', $this->input->post('company'));
		$company = str_replace(",", "','", $companies);

		$qry = "SELECT job_rank_id, job_rank FROM {$dbprefix}user_rank WHERE deleted = 0";

		$results = $this->db->query($qry);


		$incumbent_scd = $this->get_incumbent($year, 6);
		$incumbent_red = $this->get_incumbent($year, 7);
		$incumbent_crd = $this->get_incumbent($year, 8);

		$existing_scd = $this->get_budgeted($year, 6 , 2);
		$existing_red = $this->get_budgeted($year, 7, 2);
		$existing_crd = $this->get_budgeted($year, 8, 2);

		$new_scd = $this->get_budgeted($year, 6 , 1);
		$new_red = $this->get_budgeted($year, 7, 1);
		$new_crd = $this->get_budgeted($year, 8, 1);

		$total_scd = array();
		$total_red = array();
		$total_crd = array();

		foreach ($results->result() as $key => $result) {
			$approve_hc = $incumbent_scd[$result->job_rank_id]+$incumbent_red[$result->job_rank_id]+$incumbent_crd[$result->job_rank_id];
				
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $result->job_rank);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, ($incumbent_scd[$result->job_rank_id]) ? $incumbent_scd[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, ($incumbent_red[$result->job_rank_id]) ? $incumbent_red[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, ($incumbent_crd[$result->job_rank_id]) ? $incumbent_crd[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, $approve_hc);

			$total_scd[$result->job_rank_id][] = $incumbent_scd[$result->job_rank_id];
	       	$total_red[$result->job_rank_id][] = $incumbent_red[$result->job_rank_id];
	       	$total_crd[$result->job_rank_id][] = $incumbent_crd[$result->job_rank_id];

			$line++;
		}

		
		$activeSheet->setCellValue('A'. $line, 'Projected New Personnel (current position)');
		$objPHPExcel->getActiveSheet()->getStyle('A' . $line)->applyFromArray($styleArray);
		$line += 1;
		foreach ($results->result() as $key => $result) {
			$approve_hc = $existing_scd[$result->job_rank_id]+$existing_red[$result->job_rank_id]+$existing_crd[$result->job_rank_id];
				
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $result->job_rank);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, ($existing_scd[$result->job_rank_id]) ? $existing_scd[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, ($existing_red[$result->job_rank_id]) ? $existing_red[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, ($existing_crd[$result->job_rank_id]) ? $existing_crd[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, $approve_hc);

			$total_scd[$result->job_rank_id][] = $existing_scd[$result->job_rank_id];
	       	$total_red[$result->job_rank_id][] = $existing_red[$result->job_rank_id];
	       	$total_crd[$result->job_rank_id][] = $existing_crd[$result->job_rank_id];

			$line++;
		}

		$activeSheet->setCellValue('A'. $line, 'Projected New Personnel (new position)');
		$objPHPExcel->getActiveSheet()->getStyle('A' . $line)->applyFromArray($styleArray);
		$line += 1;
		foreach ($results->result() as $key => $result) {
			$approve_hc = $new_scd[$result->job_rank_id]+$new_red[$result->job_rank_id]+$new_crd[$result->job_rank_id];
				
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $result->job_rank);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, ($new_scd[$result->job_rank_id]) ? $new_scd[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, ($new_red[$result->job_rank_id]) ? $new_red[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, ($new_crd[$result->job_rank_id]) ? $new_crd[$result->job_rank_id] : 0);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, $approve_hc);

			$total_scd[$result->job_rank_id][] = $new_scd[$result->job_rank_id];
	       	$total_red[$result->job_rank_id][] = $new_red[$result->job_rank_id];
	       	$total_crd[$result->job_rank_id][] = $new_crd[$result->job_rank_id];
			$line++;
		}

		$activeSheet->setCellValue('A'. $line, 'Total Budgeted Headcount for the Year');
		$objPHPExcel->getActiveSheet()->getStyle('A' . $line)->applyFromArray($styleArray);
		$line += 1;
		foreach ($results->result() as $key => $result) {
			$total_approved = array_sum($total_scd[$result->job_rank_id]) + array_sum($total_red[$result->job_rank_id]) + array_sum($total_crd[$result->job_rank_id]);

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $result->job_rank);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, (array_sum($total_scd[$result->job_rank_id]) > 0) ? array_sum($total_scd[$result->job_rank_id]) : 0 );
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, (array_sum($total_red[$result->job_rank_id]) > 0) ? array_sum($total_red[$result->job_rank_id]) : 0 );
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $line, (array_sum($total_crd[$result->job_rank_id]) > 0) ? array_sum($total_crd[$result->job_rank_id]) : 0 );
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $line, $total_approved);
		
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
		header('Content-Disposition: attachment;filename=' . url_title("HEADCOUNT BUDGET") .  date('Y-m-d') .'.xls');
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