<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hit_rate extends MY_Controller
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
		$data['content'] = 'recruitment/analytics/hit_rate_listview';

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
		$company_id = $this->input->post('company');

		$qry = "SELECT company, company_id FROM {$dbprefix}user_company WHERE deleted = 0";

		if($company_id && $company_id != 'null') {
          $qry .= " AND company_id IN (".implode(',', $company_id).")";
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

			$qry = "SELECT company, company_id FROM {$dbprefix}user_company WHERE deleted = 0";

			if($company_id && $company_id != 'null') {
	          $qry .= " AND company_id IN (".implode(',', $company_id).")";
	        }

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        

			$result = $this->db->query($qry);	
			
			$numbers = array();
						
			$ctr = 0;	
	       
		   	   
	        foreach ($result->result() as $row) {

	        	$sql = "SELECT * 
	        			FROM {$dbprefix}recruitment_manpower rm
						LEFT JOIN {$dbprefix}recruitment_manpower_candidate rmc
							ON rmc.mrf_id = rm.request_id
						LEFT JOIN {$dbprefix}recruitment_candidate_job_offer jo
					    	ON rmc.candidate_id = jo.candidate_id
					    WHERE rm.deleted = 0 AND rm.company_id = ". $row->company_id;
				
				if ($this->input->post('year')){
					$sql .= " AND YEAR(rm.date_needed) = ".$this->input->post('year')."";			
				}

				if($this->input->post('division')) {
		            $division_id = $this->input->post('division'); // implode(',', $this->input->post('division'));
		            $sql .= " AND rm.division_id IN (".$division_id.")";
		        }			
		      
		        if ($this->input->post('department')){
		        	$department_id = $this->input->post('department');
					$sql .= " AND rm.department_id IN (".$department_id.")";
				}

				if ($this->input->post('position')){
		        	$position_id = $this->input->post('position');
					$sql .= " AND rm.position_id IN (".$position_id.")";
				}

				$jo_stat = $this->db->query($sql);
				$response->last_query = $this->db->last_query();
				
				$accepted = 0;
				$rejected = 0;
		        $total_jo = 0;        
				$successful_jo = 0;

				if ($jo_stat && $jo_stat->num_rows() > 0)
	       		{
	       			$total_jo = $jo_stat->num_rows();
	       			foreach ($jo_stat->result() as $key => $value) {
	       			
			        	switch ($value->job_offer_status_id) {
			        		case 3:
			        			if ($value->candidate_status_id != 6) {
					        			$accepted++;
					        	}
			        		break;
			        		
			        		case 4:
			        			$rejected++;
			        			break;
			        	}

			        	if ($value->candidate_status_id == 6) {
			        		$successful_jo++;
			        	}
		        	}
	        	}

	        	$hit_rate = ($successful_jo / $total_jo) * 100;

		        $response->rows[$ctr]['cell'][0] = $row->company;
		        $response->rows[$ctr]['cell'][1] = $accepted;
		        $response->rows[$ctr]['cell'][2] = $rejected;
		        $response->rows[$ctr]['cell'][3] = $total_jo;
				$response->rows[$ctr]['cell'][4] = $successful_jo;
				$response->rows[$ctr]['cell'][5] = number_format($hit_rate, '2');
				$ctr++;

	        }

	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function get_division()
    {
        $division = $this->db->query('SELECT b.division_id, b.division FROM '.$this->db->dbprefix('user').' a LEFT JOIN  '.$this->db->dbprefix('user_company_division').' b ON a.division_id = b.division_id WHERE a.company_id IN ('.$this->input->post("div_id_delimited").') AND b.division_id IS NOT NULL GROUP BY b.division_id')->result_array();
        $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
            foreach($division as $division_record){
                $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
            }
        $html .= '</select>';	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function get_department()
	{
		$department = $this->db->query('SELECT * FROM '.$this->db->dbprefix('user_company_department').' WHERE '.$this->db->dbprefix('user_company_department').'.division_id IN ('.$this->input->post("div_id_delimited").')')->result_array();
        $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
            foreach($department as $department_record){
                $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
            }
        $html .= '</select>';	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
   		$this->listview_column_names = array("Company", "No. Of Accepted Job Offer", "No. Of Rejected Job Offer", "Total No. Of Job Offer", "No. Of Successful Offers", "Hit Rate");

		$this->listview_columns = array(
				array('name' => 'company', 'width' => '180','align' => 'center'),				
				array('name' => 'accepted_jo', 'width' => '180','align' => 'center'),				
				array('name' => 'rejected_jo', 'width' => '280','align' => 'center'),
				array('name' => 'total_jo'),
				array('name' => 'successful'),
				array('name' => 'hit_rate')
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

		$objPHPExcel->getProperties()->setTitle("Recruitment Report - Hit Rate")
		            ->setDescription("Recruitment Report - Hit Rate");
		               
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

		$fields = array("Company", "No. Of Accepted Job Offer", "No. Of Rejected Job Offer", "Total No. Of Job Offer", "No. Of Successful Offers", "Hit Rate");

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
		$activeSheet->setCellValue('A2', 'Recruitment Report - Hit Rate');
		// if( $this->input->post('year') ){
		// 	$activeSheet->setCellValue('A3', 'As of ' . date('F d,Y',strtotime($this->input->post('year'))));
		// }

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		// $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		$company_id = $this->input->post('company');
		


		$this->db->select('company, company_id');
		$this->db->where('deleted', 0);
		if($company_id && $company_id != 'null') {
          $qry = "company_id IN (".implode(',', $company_id).")";
          $this->db->where($qry);
        }

		$companies = $this->db->get('user_company');

		$fields2 = array('company','accepted','rejected','total','successful','hit_rate');
		foreach ($companies->result() as $key => $company) {
			$sub_ctr   = 0;			
			$alpha_ctr = 0;
			$sql = "SELECT * 
	        			FROM {$dbprefix}recruitment_manpower rm
						JOIN {$dbprefix}recruitment_manpower_candidate rmc
							ON rmc.mrf_id = rm.request_id
						JOIN {$dbprefix}recruitment_candidate_job_offer jo
					    	ON rmc.candidate_id = jo.candidate_id
					    WHERE rm.deleted = 0 AND rm.company_id = ". $company->company_id;
				
				if ($this->input->post('year')){
					$sql .= " AND YEAR(rm.date_needed) = ".$this->input->post('year')."";			
				}

				if($this->input->post('division')) {
		            $division_id = $this->input->post('division'); // implode(',', $this->input->post('division'));
		            $sql .= " AND rm.division_id IN (".$division_id.")";
		        }			
		      
		        if ($this->input->post('department')){
		        	$department_id = $this->input->post('department');
					$sql .= " AND rm.department_id IN (".$department_id.")";
				}

				if ($this->input->post('position')){
		        	$position_id = $this->input->post('position');
					$sql .= " AND rm.position_id IN (".$position_id.")";
				}

				$jo_stat = $this->db->query($sql);

				$accepted = 0;
				$rejected = 0;
		        $total_jo = 0;        
				$successful_jo = 0;

				if ($jo_stat && $jo_stat->num_rows() > 0)
	       		{
	       			$total_jo = $jo_stat->num_rows();
	       			foreach ($jo_stat->result() as $key => $value) {
	       			
			        	switch ($value->job_offer_status_id) {
			        		case 3:
			        			if ($value->candidate_status_id != 6) {
					        			$accepted++;
					        	}
			        		break;
			        		
			        		case 4:
			        			$rejected++;
			        			break;
			        	}

			        	if ($value->candidate_status_id == 6) {
			        		$successful_jo++;
			        	}
		        	}
	        	}

	        	$hit_rate = ($successful_jo / $total_jo) * 100;
	        		
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

				$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $company->{$field});
				if ($field == 'accepted') {
					
				}
				switch ($field) {
					case 'accepted':
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $accepted);
						break;
					case 'rejected':
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $rejected);
						break;
					case 'total':
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $total_jo);
						break;
					case 'successful':
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $successful_jo);
						break;
					case 'hit_rate':
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($hit_rate, '2'));
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
		header('Content-Disposition: attachment;filename=' . url_title("Hit Rate") .  date('Y-m-d') .'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	
	}

	// export called using ajax
	function excel_ajax_export()
	{	



		

		//$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($leftstyleArray);

		// contents.
		$line = 7;
		$fields = array("Name","Position","Rank Code","Range of Rank","Rank","Date Hired","Date Regularized","Annual Rating1","Annual Rating2");
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

				$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});

				$alpha_ctr++;
			}

			$line++;
		}

		$objPHPExcel->getActiveSheet()->getStyle('A6:'.$xcoor.($line - 1))->applyFromArray($styleArrayBorder);

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title("Performance Appraisal Summary") . '.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/performance_appraisal_summary/'.url_title("Performance Appraisal Summary").'-'.date('Y-m-d').'.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));
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