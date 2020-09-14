<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Assessment_benefits extends MY_Controller
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
		$data['content'] = 'recruitment/analytics/benefits_listview';

		//$data['jqgrid'] = '';//'employees/appraisal/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$companies = array();
		$benefits = array();
		$records = $this->db->get_where('recruitment_candidates_appraisal', array('deleted' => 0));

		if ($records && $records->num_rows() > 0) {
			foreach ($records->result() as $key => $record) {
				$companies[] = $record->company;
				
				if ($record->other_benefits != false) {
					$benefit = json_decode($record->other_benefits);
					foreach ($benefit->benefit as $key => $value) {
						$benefits[] = $value;
					}
				}
			}
		}

		$data['benefits'] = array_unique($benefits);
		$data['companies'] = array_unique($companies);
		// $data['divisions'] = $this->db->get_where('user_company_division', array('deleted' => 0))->result_array();
		// $data['positions'] = $this->db->get_where('user_position', array('deleted' => 0))->result_array();

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		// $data['department'] = $this->db->get('user_company_department')->result_array();

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
		$companies = implode(',', $this->input->post('company'));
		$company = str_replace(",", "','", $companies);


		$qry = "SELECT c.applicant_name, c.candidate_id, a.*
			FROM {$dbprefix}recruitment_manpower_candidate c 
			LEFT JOIN {$dbprefix}recruitment_candidates_appraisal a ON c.candidate_id = a.candidate_id
			WHERE c.candidate_status_id IN (4,6,7,10,12,13,14,15,16,20,23,24) ";

		if($companies && $companies != 'null') {
			
			$qry .= " AND company IN ('".$company."')";
        }
	
		 if ($this->input->post('benefit')){
        	$benefits = $this->input->post('benefit');
        	$qry .= " AND (";
        	foreach ($benefits as $id => $benefit) {
        		if ($id == 0) {
        			$qry .= " other_benefits LIKE '%".$benefit."%'";	
        		}else{
        			$qry .= " OR other_benefits LIKE '%".$benefit."%'";
        		}
        		
        	}
        	$qry .= ")";
			
		}

        $result = $this->db->query($qry);   
     	$response->query = $this->db->last_query();



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

			$qry = "SELECT c.applicant_name, c.candidate_id, a.*
						FROM {$dbprefix}recruitment_manpower_candidate c 
						LEFT JOIN {$dbprefix}recruitment_candidates_appraisal a ON c.candidate_id = a.candidate_id
						WHERE c.candidate_status_id IN (4,6,7,10,12,13,14,15,16,20,23,24)";

			if($companies && $companies != 'null') {
	          	$qry .= " AND company IN ('".$company."')";
	        }

	        if ($this->input->post('benefit')){
	        	$benefits = $this->input->post('benefit');
	        	$qry .= " AND (";
	        	foreach ($benefits as $id => $benefit) {
	        		if ($id == 0) {
	        			$qry .= " other_benefits LIKE '%".$benefit."%'";	
	        		}else{
	        			$qry .= " OR other_benefits LIKE '%".$benefit."%'";
	        		}
	        		
	        	}
	        	$qry .= ")";
				
			}

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        

			$result = $this->db->query($qry);	
						
			$ctr = 0;	
	       		   	   
	        foreach ($result->result() as $row) {

	        	// $interview_details = json_decode($row->interview_details, true);
	        	
	        	$start  = $row->date_from;
	        	$end 	= $row->date_to;	
		        
		        	        	
	        	if ($start != '0000-00-00' && $end != '0000-00-00') {
	        		/*$tenure = date('Y' , strtotime($end)) - date('Y' , strtotime($start)) + 
	                            ((date('m' , strtotime($end)) - date('m', strtotime($start)))/12) +
	                            (date('d' , strtotime($end)) - date('d', strtotime($start)))/365.25;*/
	        	
	        		$year  = date('Y' , strtotime($end)) - date('Y' , strtotime($start));
	        		$month = date('m' , strtotime($end)) - date('m', strtotime($start));
	        		$tenure = ( ( $year * 12 ) + $month ) / 12;

	        	}
	        	
	        	$benefits = array();
	        	$amount = array();
	        	if ($row->other_benefits != false) {
	        		$other_benefits = json_decode($row->other_benefits, true);
	        		foreach ($other_benefits['benefit'] as $key => $other_benefit) {
	        			$benefits[] = $other_benefit;
	        			$amount[] = (intval($other_benefits['amount'][$key]) > 0 ) ? $other_benefits['amount'][$key] : 0 ;
	        		}
	        	}

		        $response->rows[$ctr]['cell'][0] = $row->applicant_name;
		        $response->rows[$ctr]['cell'][1] = $row->company;
				$response->rows[$ctr]['cell'][2] = $row->industry;
				$response->rows[$ctr]['cell'][3] = $row->position;
				$response->rows[$ctr]['cell'][4] = $row->level;
				$response->rows[$ctr]['cell'][5] = $row->previous_emp_status;
				$response->rows[$ctr]['cell'][6] = ($start != '0000-00-00' && $end != '0000-00-00') ? number_format($tenure, '2', '.', ',') : '';
				$response->rows[$ctr]['cell'][7] = (intval($row->salary) > 0) ? $row->salary : '';
				$response->rows[$ctr]['cell'][8] = '';
				$response->rows[$ctr]['cell'][9] = ($row->other_benefits != false) ? implode('<br>', $benefits): '';
				$response->rows[$ctr]['cell'][10] = ($row->other_benefits != false) ? implode('<br>', $amount): '';
				$ctr++;	

	        }

	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function get_benefits()
    {
        $benefits = array();
        $companies = $this->input->post('company');
        $company = str_replace(",", "','", $companies);

        $sql = "SELECT * FROM {$this->db->dbprefix}recruitment_candidates_appraisal
        			WHERE company IN ('".$company."')";
       	$records = $this->db->query($sql);

       	$benefits = array();
       	$html = '<option></option>';
       	
       	if ($records && $records->num_rows() > 0) {
       		foreach($records->result() as $record){
       			if ($record->other_benefits != false) {
					$benefit = json_decode($record->other_benefits);
					foreach ($benefit->benefit as $key => $value) {
						$benefits[] = $value;
					}
				}
            }
       	}

       	if (!empty($benefits)) {
	      	foreach ($benefits as $key => $benefit) {
	       	        $html .= '<option value="'.$benefit.'">'.$benefit.'</option>';
	       	}
        }
        
        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
   		$this->listview_column_names = array("Name", "Company", "Industry", "Position", "Level/Classification", "Status of Employment", "Tenure", "Basic Salary", "Learning & Development", "Benefits", "Amount");

		$this->listview_columns = array(
				array('name' => 'name'),
				array('name' => 'company'),				
				array('name' => 'industry','align' => 'left'),
				array('name' => 'position'),				
				array('name' => 'level'),
				array('name' => 'status'),
				array('name' => 'tenure'),
				array('name' => 'salary'),
				array('name' => 'learning'),
				array('name' => 'benefits', 'align' => 'left'),
				array('name' => 'amount', 'align' => 'left')
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

		$fields = array("Name", "Company", "Industry", "Position", "Level/Classification", "Status of Employment", "Tenure", "Basic Salary", "Learning & Development", "Benefits", "Amount");

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
		
		$activeSheet->setCellValue('A1', 'OCLP HOLDINGS, INC.');
		$activeSheet->setCellValue('A2', 'Assessment Benefits Report');

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		$companies = implode(',', $this->input->post('company'));
		$company = str_replace(",", "','", $companies);


		$qry = "SELECT c.applicant_name, c.candidate_id, a.*
			FROM {$dbprefix}recruitment_manpower_candidate c 
			LEFT JOIN {$dbprefix}recruitment_candidates_appraisal a ON c.candidate_id = a.candidate_id
			WHERE c.candidate_status_id IN (4,6,7,10,12,13,14,15,16,20,23,24) ";

		if($companies && $companies != 'null') {
			
			$qry .= " AND company IN ('".$company."')";
        }

        if ($this->input->post('benefit') ){
        	$benefits = $this->input->post('benefit');
        	$qry .= " AND (";
        	foreach ($benefits as $id => $benefit) {
        		if ($id == 0) {
        			$qry .= " other_benefits LIKE '%".$benefit."%'";	
        		}else{
        			$qry .= " OR other_benefits LIKE '%".$benefit."%'";
        		}
        		
        	}
        	$qry .= ")";
			
		}

		$results = $this->db->query($qry);

		$fields2 = array('applicant_name','company','industry','position','level','previous_emp_status','date_from','salary','learning','benefits','amounts');
		
		
		foreach ($results->result() as $key => $result) {
			$sub_ctr   = 0;			
			$alpha_ctr = 0;
			foreach ($fields2 as $field) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}
				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$start  = $result->date_from;
	        	$end 	= $result->date_to;	
		        
		        	        		
	        	if ($start != '0000-00-00' && $end != '0000-00-00') {
	        	
	        		$year  = date('Y' , strtotime($end)) - date('Y' , strtotime($start));
	        		$month = date('m' , strtotime($end)) - date('m', strtotime($start));
	        		$tenure = ( ( $year * 12 ) + $month ) / 12;

	        	}
	        	
	        	$benefits = array();
	        	/*if ($result->other_benefits != false) {
	        		$other_benefits = json_decode($result->other_benefits, true);
	        		foreach ($other_benefits['benefit'] as $key => $other_benefit) {
	        			$benefits[] = $other_benefit;
	        		}
	        	}*/
	 
		
				$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $result->{$field});
				if ($field == 'salary') {
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (intval($result->salary) > 0) ? $result->salary : '');
				}
						
				if ($field == 'date_from') {
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, ($start != '0000-00-00' && $end != '0000-00-00') ? number_format($tenure, '2', '.', ',') : '');
				}

				if ($field == 'benefits' || $field == 'amounts') {
					// $objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, ($result->other_benefits != false) ? implode(', ', $benefits): '');
					
					if ($result->other_benefits != 'false') {
		        		$other_benefits = json_decode($result->other_benefits, true);
							$cnt = count($other_benefits['benefit']);
			        		foreach ($other_benefits['benefit'] as $key => $other_benefit) {
			        			if ($field == 'benefits') {
				        			$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $other_benefit); 
				        		}
				        		if ($field == 'amounts') {
				        			$line2 = $line - $cnt ;
				        			$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line2, $other_benefits['amount'][$key]); 

				        		}
				        		 
				        		$line++;
				        						        		
			        		}
		        	}else{
		        		$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '');
		        	}

				}

				
				$alpha_ctr++;
			}
			$line++;
		}
	
		/*foreach ($results->result() as $key => $result) {
			
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			$other_benefits = json_decode($result->other_benefits, true);
	        // $interview_details = json_decode($result->interview_details, true);

			$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $result->applicant_name);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $result->company);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, $result->industry);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, $result->industry);
				$alpha_ctr++;
			$line++;
		}
*/
		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . url_title("Assessment-Report") .  date('Y-m-d') .'.xls');
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