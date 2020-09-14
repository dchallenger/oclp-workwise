<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal_rating_summary_report extends MY_Controller
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
		$data['content'] = 'employees/appraisal/appraisal_rating_summary_listview';

		$data['jqgrid'] = 'employees/appraisal/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$data['department'] = $this->db->get('user_company_department')->result_array();
		$data['company'] = $this->db->get('user_company')->result_array();
		$data['division'] = $this->db->get('user_company_division')->result_array();

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

		$year1 = $this->input->post('date_year') - 2;
		$year2 = $this->input->post('date_year') - 1;

		$qry = "SELECT CONCAT(u.firstname,' ',u.lastname) as 'Name',up.position as 'Position',urc.job_rank_code as 'Rank Code',urr.job_rank_range as 'Range of Rank',
					ur.job_rank_short_code as 'Rank',emp.employed_date as 'Date Hired',emp.regular_date as 'Date Regularized',pa_rating1 as 'Annual Rating1',pa_rating2 as 'Annual Rating2'
				FROM {$this->db->dbprefix}employee_appraisal_bsc eab 
				LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap
					ON eab.appraisal_period_id = eap.employee_appraisal_period_id
				LEFT JOIN {$this->db->dbprefix}user u
					ON eab.employee_id = u.employee_id
				LEFT JOIN {$this->db->dbprefix}employee emp
					ON u.user_id = emp.employee_id						
				LEFT JOIN {$this->db->dbprefix}user_company_division ucdiv
					ON u.division_id = ucdiv.division_id
				LEFT JOIN {$this->db->dbprefix}user_company_department ucdep
					ON u.department_id = ucdep.department_id	
				LEFT JOIN {$this->db->dbprefix}user_position up
					ON u.position_id = up.position_id											
				LEFT JOIN {$this->db->dbprefix}user_rank_code urc
					ON emp.job_level = urc.job_rank_code_id
				LEFT JOIN {$this->db->dbprefix}user_rank_range urr
					ON emp.range_of_rank = urr.job_rank_range_id												
				LEFT JOIN {$this->db->dbprefix}user_rank ur
					ON emp.rank_id = ur.job_rank_id						
				LEFT JOIN (SELECT employee_appraisal_or_total_score as pa_rating1,employee_id FROM {$this->db->dbprefix}employee_appraisal_bsc eab1 LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap1 ON eab1.appraisal_period_id = eap1.employee_appraisal_period_id WHERE eap1.appraisal_year = ".$year1.") as eab2 ON (eab.employee_id = eab2.employee_id)
				LEFT JOIN (SELECT employee_appraisal_or_total_score as pa_rating2,employee_id FROM {$this->db->dbprefix}employee_appraisal_bsc eab3 LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap2 ON eab3.appraisal_period_id = eap2.employee_appraisal_period_id WHERE eap2.appraisal_year = ".$year2.") as eab4 ON (eab.employee_id = eab4.employee_id)
				WHERE eab.deleted = 0
				AND u.inactive = 0";

		if ($this->input->post('date_year')){
			$qry .= " AND eap.appraisal_year = ".$this->input->post('date_year')."";			
		}

		$company_id = '';
		if(isset($_POST['company'])) {
            $company_arr = array();
            foreach ($_POST['company'] as $value) 
            {
                $company_arr[] = $value;    
            }
          	$company_id = implode(',', $company_arr);
        }
        if (!empty($company_id)){
			$qry .= " AND u.company_id IN (".$company_id.")";
		}

		$division_id = '';
		if(isset($_POST['division'])) {
            $div_arr = array();
            foreach ($_POST['division'] as $value) 
            {
                $div_arr[] = $value;    
            }
          	$division_id = implode(',', $div_arr);
        }			
		if (!empty($division_id)){
			$qry .= " AND u.division_id IN (".$division_id.")";
		}

		$department_id = '';
		if(isset($_POST['department'])) {
            $dep_arr = array();
            foreach ($_POST['department'] as $value) 
            {
                $dep_arr[] = $value;    
            }
          	$department_id = implode(',', $dep_arr);
        }
		if (!empty($department_id)){
			$qry .= " AND u.department_id IN (".$department_id.")";
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

			$qry = "SELECT CONCAT(u.firstname,' ',u.lastname) as 'Name',up.position as 'Position',urc.job_rank_code as 'Rank Code',urr.job_rank_range as 'Range of Rank',
						ur.job_rank_short_code as 'Rank',emp.employed_date as 'Date Hired',emp.regular_date as 'Date Regularized',pa_rating1 as 'Annual Rating1',pa_rating2 as 'Annual Rating2'
					FROM {$this->db->dbprefix}employee_appraisal_bsc eab 
					LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap
						ON eab.appraisal_period_id = eap.employee_appraisal_period_id
					LEFT JOIN {$this->db->dbprefix}user u
						ON eab.employee_id = u.employee_id
					LEFT JOIN {$this->db->dbprefix}employee emp
						ON u.user_id = emp.employee_id						
					LEFT JOIN {$this->db->dbprefix}user_company_division ucdiv
						ON u.division_id = ucdiv.division_id
					LEFT JOIN {$this->db->dbprefix}user_company_department ucdep
						ON u.department_id = ucdep.department_id	
					LEFT JOIN {$this->db->dbprefix}user_position up
						ON u.position_id = up.position_id											
					LEFT JOIN {$this->db->dbprefix}user_rank_code urc
						ON emp.job_level = urc.job_rank_code_id
					LEFT JOIN {$this->db->dbprefix}user_rank_range urr
						ON emp.range_of_rank = urr.job_rank_range_id												
					LEFT JOIN {$this->db->dbprefix}user_rank ur
						ON emp.rank_id = ur.job_rank_id						
					LEFT JOIN (SELECT employee_appraisal_or_total_score as pa_rating1,employee_id FROM {$this->db->dbprefix}employee_appraisal_bsc eab1 LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap1 ON eab1.appraisal_period_id = eap1.employee_appraisal_period_id WHERE eap1.appraisal_year = ".$year1.") as eab2 ON (eab.employee_id = eab2.employee_id)
					LEFT JOIN (SELECT employee_appraisal_or_total_score as pa_rating2,employee_id FROM {$this->db->dbprefix}employee_appraisal_bsc eab3 LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap2 ON eab3.appraisal_period_id = eap2.employee_appraisal_period_id WHERE eap2.appraisal_year = ".$year2.") as eab4 ON (eab.employee_id = eab4.employee_id)
					WHERE eab.deleted = 0
					AND u.inactive = 0";

			if ($this->input->post('date_year')){
				$qry .= " AND eap.appraisal_year = ".$this->input->post('date_year')."";			
			}

			$company_id = '';
			if(isset($_POST['company'])) {
	            $company_arr = array();
	            foreach ($_POST['company'] as $value) 
	            {
	                $company_arr[] = $value;    
	            }
	          	$company_id = implode(',', $company_arr);
	        }
	        if (!empty($company_id)){
				$qry .= " AND u.company_id IN (".$company_id.")";
			}

			$division_id = '';
			if(isset($_POST['division'])) {
	            $div_arr = array();
	            foreach ($_POST['division'] as $value) 
	            {
	                $div_arr[] = $value;    
	            }
	          	$division_id = implode(',', $div_arr);
	        }			
			if (!empty($division_id)){
				$qry .= " AND u.division_id IN (".$division_id.")";
			}

			$department_id = '';
			if(isset($_POST['department'])) {
	            $dep_arr = array();
	            foreach ($_POST['department'] as $value) 
	            {
	                $dep_arr[] = $value;    
	            }
	          	$department_id = implode(',', $dep_arr);
	        }
			if (!empty($department_id)){
				$qry .= " AND u.department_id IN (".$department_id.")";
			}

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        

			$result = $this->db->query($qry);	

	        $ctr = 0;	        
	        foreach ($result->result() as $row) {

				$date_hired = new DateTime($row->{'Date Hired'});
				$as_of_now = new DateTime();
				$diff = $date_hired->diff($as_of_now);

				$year = $diff->y;
				$month = $diff->m + ($diff->d > 16 ? 1 : .5 );

	            $response->rows[$ctr]['cell'][0] = $row->{'Name'};
	            $response->rows[$ctr]['cell'][1] = $row->{'Position'};
	            $response->rows[$ctr]['cell'][2] = $row->{'Rank Code'};
	            $response->rows[$ctr]['cell'][3] = $row->{'Range of Rank'};
	            $response->rows[$ctr]['cell'][4] = $row->{'Rank'};
	            $response->rows[$ctr]['cell'][5] = $year;
	            $response->rows[$ctr]['cell'][6] = $month;
	            $response->rows[$ctr]['cell'][7] = date('d M Y',strtotime($row->{'Date Regularized'}));
	            $response->rows[$ctr]['cell'][8] = $row->{'Annual Rating1'};
	            $response->rows[$ctr]['cell'][9] = $row->{'Annual Rating2'};
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
    	$as_date = 'Position Title as of ' . date('M d,Y');
    	$prev_year = 'Annual PA Rating';
    	$prev_prev_year = 'Annual PA Rating';

		$this->listview_column_names = array('Name', $as_date,'Job Level','Rank','Rank','Years','Month','Regularized',$prev_year,$prev_prev_year);

		$this->listview_columns = array(
				array('name' => 'name', 'width' => '180','align' => 'center'),				
				array('name' => 'position', 'width' => '280','align' => 'center'),
				array('name' => 'job_level'),
				array('name' => 'range_of_rank'),
				array('name' => 'rank'),
				array('name' => 'years'),
				array('name' => 'month'),
				array('name' => 'regularized'),
				array('name' => 'prev_annual_pa_rating'),
				array('name' => 'prev_prev_annual_pa_rating')
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

	// export called using ajax
	function excel_ajax_export()
	{	
		ini_set('memory_limit', "512M");
		$this->load->helper('time_upload');

		$year1 = $this->input->post('date_year') - 2;
		$year2 = $this->input->post('date_year') - 1;
		
		$qry = "SELECT CONCAT(u.firstname,' ',u.lastname) as 'Name',up.position as 'Position',urc.job_rank_code as 'Rank Code',urr.job_rank_range as 'Range of Rank',
					ur.job_rank_short_code as 'Rank',emp.employed_date as 'Date Hired',emp.regular_date as 'Date Regularized',pa_rating1 as 'Annual Rating1',pa_rating2 as 'Annual Rating2'
				FROM {$this->db->dbprefix}employee_appraisal_bsc eab 
				LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap
					ON eab.appraisal_period_id = eap.employee_appraisal_period_id
				LEFT JOIN {$this->db->dbprefix}user u
					ON eab.employee_id = u.employee_id
				LEFT JOIN {$this->db->dbprefix}employee emp
					ON u.user_id = emp.employee_id						
				LEFT JOIN {$this->db->dbprefix}user_company_division ucdiv
					ON u.division_id = ucdiv.division_id
				LEFT JOIN {$this->db->dbprefix}user_company_department ucdep
					ON u.department_id = ucdep.department_id	
				LEFT JOIN {$this->db->dbprefix}user_position up
					ON u.position_id = up.position_id											
				LEFT JOIN {$this->db->dbprefix}user_rank_code urc
					ON emp.job_level = urc.job_rank_code_id
				LEFT JOIN {$this->db->dbprefix}user_rank_range urr
					ON emp.range_of_rank = urr.job_rank_range_id												
				LEFT JOIN {$this->db->dbprefix}user_rank ur
					ON emp.rank_id = ur.job_rank_id						
				LEFT JOIN (SELECT employee_appraisal_or_total_score as pa_rating1,employee_id FROM {$this->db->dbprefix}employee_appraisal_bsc eab1 LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap1 ON eab1.appraisal_period_id = eap1.employee_appraisal_period_id WHERE eap1.appraisal_year = ".$year1.") as eab2 ON (eab.employee_id = eab2.employee_id)
				LEFT JOIN (SELECT employee_appraisal_or_total_score as pa_rating2,employee_id FROM {$this->db->dbprefix}employee_appraisal_bsc eab3 LEFT JOIN {$this->db->dbprefix}employee_appraisal_period eap2 ON eab3.appraisal_period_id = eap2.employee_appraisal_period_id WHERE eap2.appraisal_year = ".$year2.") as eab4 ON (eab.employee_id = eab4.employee_id)
				WHERE eab.deleted = 0
				AND u.inactive = 0";

		if ($this->input->post('date_year')){
			$qry .= " AND eap.appraisal_year = ".$this->input->post('date_year')."";			
		}

		$company_id = '';
		if(isset($_POST['company'])) {
            $company_arr = array();
            foreach ($_POST['company'] as $value) 
            {
                $company_arr[] = $value;    
            }
          	$company_id = implode(',', $company_arr);
        }
        if (!empty($company_id)){
			$qry .= " AND u.company_id IN (".$company_id.")";
		}

		$division_id = '';
		if(isset($_POST['division'])) {
            $div_arr = array();
            foreach ($_POST['division'] as $value) 
            {
                $div_arr[] = $value;    
            }
          	$division_id = implode(',', $div_arr);
        }			
		if (!empty($division_id)){
			$qry .= " AND u.division_id IN (".$division_id.")";
		}

		$department_id = '';
		if(isset($_POST['department'])) {
            $dep_arr = array();
            foreach ($_POST['department'] as $value) 
            {
                $dep_arr[] = $value;    
            }
          	$department_id = implode(',', $dep_arr);
        }
		if (!empty($department_id)){
			$qry .= " AND u.department_id IN (".$department_id.")";
		}

        $q = $this->db->query($qry); 

		$query = $q;
		//$fields = $q->list_fields();

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Performance Appraisal Summary")
		            ->setDescription("Performance Appraisal Summary");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
/*		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);	*/				

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

		$fields = array("Name","Position Title as of " . date('M d, Y'),"Job Level / Rank Code or Rank","Range of Ranks","Rank","Tenure","Date Regularized",($this->input->post('date_year') - 2) . " Annual PA Rating",($this->input->post('date_year') - 1) . " Annual PA Rating");
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
		$activeSheet->setCellValue('A2', 'Performance Appraisal Summary');
		if( $this->input->post('date_year') ){
			$activeSheet->setCellValue('A3', 'As of ' . date('F d,Y',strtotime($this->input->post('date_year'))));
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

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
	
}

/* End of file */
/* Location: system/application */
?>