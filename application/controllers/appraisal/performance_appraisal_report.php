<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class performance_appraisal_report extends MY_Controller
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

		$data['content'] = 'employees/appraisal/reports/listview';

		$data['jqgrid'] = 'employees/appraisal/reports/jqgrid_noshrink';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$where = array('deleted' => 0);
		$data['departments'] = $this->db->get_where('user_company_department', $where)->result_array();
		$data['companies'] = $this->db->get_where('user_company', $where)->result_array();

		$data['divisions'] = $this->db->get_where('user_company_division', $where)->result_array();

		$data['periods'] = $this->get_aprpaisal_period();
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

	function listview()	{
		$dbprefix = $this->db->dbprefix;
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		$search = 1;
		$company_ids = 1;
		$division_ids = 1;
		$department_ids = 1;
		$employee_ids = 1;

		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$company_ids = implode(',', $this->input->post('company_id'));
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$division_ids = implode(',', $this->input->post('division_id'));
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$department_ids = implode(',', $this->input->post('department_id'));
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_ids = implode(',', $this->input->post('employee_id'));
		}

		$planning_period_id = $this->input->post('planning_period_id');

		$employee_query = "SELECT e.employee_id,e.id_number,u.lastname, u.firstname, u.middleinitial,
							up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division, e.employed_date,TIMESTAMPDIFF(YEAR, employed_date, now()) AS service_years,
							eap.appraisal_year,eab.final_rating,e.last_promotion_date,eab.rating,eab.coach_rating
							FROM {$dbprefix}employee_appraisal_bsc eab
							LEFT JOIN {$dbprefix}user u ON eab.employee_id = u.employee_id
							LEFT JOIN {$dbprefix}employee e ON e.employee_id = u.employee_id							
							LEFT JOIN hr_user_position up ON up.position_id = u.position_id 
							LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id 
							LEFT JOIN hr_position_classification pc ON pc.position_class_id = e.position_class_id 
							LEFT JOIN hr_user_company_department ucd ON u.department_id = ucd.department_id 
							LEFT JOIN hr_user_company_division ucdi ON u.division_id = ucdi.division_id 							
							LEFT JOIN {$dbprefix}appraisal_planning_period app ON eab.appraisal_period_id = app.planning_period_id
							LEFT JOIN {$dbprefix}employee_appraisal_period eap ON app.planning_period_id = eap.planning_period_id
							WHERE u.deleted = 0 AND eab.deleted = 0 AND app.deleted = 0 AND eap.deleted = 0";
		
		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$employee_query .= " AND u.company_id IN (" .$company_ids. ")";	
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$employee_query .= " AND u.division_id IN (" .$division_ids. ")";
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$employee_query .= " AND u.department_id IN (" .$department_ids. ")";
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_query .= " AND u.employee_id IN (" .$employee_ids. ")";
		}

		if($this->input->post('planning_period_id') != '' && $this->input->post('planning_period_id') != 'null'){
			$employee_query .= " AND eap.planning_period_id = ".$planning_period_id."";
		}

		$employee_query .= " ORDER BY u.employee_id,u.company_id, u.department_id, u.division_id, eap.appraisal_year ";

		$employee_result_all = $this->db->query($employee_query);

		$start = $limit * $page - $limit;
		$employee_query .= " LIMIT ".$start.",".$limit."";

		$employee_result = $this->db->query($employee_query);

		$employees_array = array();
		if ($employee_result && $employee_result->num_rows() > 0) {
			foreach ($employee_result->result() as $row) {
				$employees_array[$row->employee_id] = array($row->id_number,$row->firstname. ' ' . $row->lastname,$row->position,$row->job_rank,$row->position_class,$row->department,$row->division,$row->employed_date,$row->service_years,$row->last_promotion_date,$row->rating,$row->coach_rating,$row->final_rating);
			}
		}

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
			$total_pages = $employee_result_all->num_rows() > 0 ? ceil($employee_result_all->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = count($employees_array);                        

	        $response->msg = "";

	        $ctr = 0;	        
	        
	        foreach ($employees_array as $employee_id => $info) {
	        	foreach ($info as $key => $value) {
					$response->rows[$ctr]['cell'][$key] = $value;
	        	}
				$ctr++;	        	
	        }
	    }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	// this is the old before new request 20200217
	function listview_org_org()	{
		$dbprefix = $this->db->dbprefix;
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		$search = 1;
		$company_ids = 1;
		$division_ids = 1;
		$department_ids = 1;
		$employee_ids = 1;

		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$company_ids = implode(',', $this->input->post('company_id'));
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$division_ids = implode(',', $this->input->post('division_id'));
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$department_ids = implode(',', $this->input->post('department_id'));
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_ids = implode(',', $this->input->post('employee_id'));
		}

		$planning_period_id = $this->input->post('planning_period_id');

		$employee_query = "SELECT e.employee_id,e.id_number,u.lastname, u.firstname, u.middleinitial,
							up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division, e.employed_date,TIMESTAMPDIFF(YEAR, employed_date, now()) AS service_years,
							eap.appraisal_year,eab.final_rating,e.last_promotion_date
							FROM {$dbprefix}employee_appraisal_bsc eab
							LEFT JOIN {$dbprefix}user u ON eab.employee_id = u.employee_id
							LEFT JOIN {$dbprefix}employee e ON e.employee_id = u.employee_id							
							LEFT JOIN hr_user_position up ON up.position_id = u.position_id 
							LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id 
							LEFT JOIN hr_position_classification pc ON pc.position_class_id = e.position_class_id 
							LEFT JOIN hr_user_company_department ucd ON u.department_id = ucd.department_id 
							LEFT JOIN hr_user_company_division ucdi ON u.division_id = ucdi.division_id 							
							LEFT JOIN {$dbprefix}appraisal_planning_period app ON eab.appraisal_period_id = app.planning_period_id
							LEFT JOIN {$dbprefix}employee_appraisal_period eap ON app.planning_period_id = eap.planning_period_id
							WHERE u.deleted = 0 AND eab.deleted = 0 AND app.deleted = 0 AND eap.deleted = 0";
		
		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$employee_query .= " AND u.company_id IN (" .$company_ids. ")";	
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$employee_query .= " AND u.division_id IN (" .$division_ids. ")";
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$employee_query .= " AND u.department_id IN (" .$department_ids. ")";
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_query .= " AND u.employee_id IN (" .$employee_ids. ")";
		}

		if($this->input->post('planning_period_id') != '' && $this->input->post('planning_period_id') != 'null'){
			$employee_query .= " AND eap.planning_period_id = ".$planning_period_id."";
		}

		$employee_query .= " GROUP BY eab.employee_id,eap.appraisal_year ORDER BY u.employee_id,u.company_id, u.department_id, u.division_id, eap.appraisal_year ";

		$employee_result_all = $this->db->query($employee_query);

		$start = $limit * $page - $limit;
		$employee_query .= " LIMIT ".$start.",".$limit."";

		$employee_result = $this->db->query($employee_query);

		$employees_array = array();
		if ($employee_result && $employee_result->num_rows() > 0) {
			foreach ($employee_result->result() as $row) {
				if (!array_key_exists($row->employee_id, $employees_array)) {
					$employees_array[$row->employee_id] = array($row->id_number,$row->firstname. ' ' . $row->lastname,$row->position,$row->job_rank,$row->position_class,$row->department,$row->division,$row->employed_date,$row->service_years,$row->last_promotion_date,$row->final_rating);
				}
				else {									
					if (!array_key_exists($row->appraisal_year, $employees_array[$row->emnployee_id][$row->appraisal_year])) {
						$employees_array[$row->employee_id][] = $row->final_rating;
					}
				}				
			}
		}

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
			$total_pages = $employee_result_all->num_rows() > 0 ? ceil($employee_result_all->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = count($employees_array);                        

	        $response->msg = "";

	        $ctr = 0;	        
	        
	        foreach ($employees_array as $employee_id => $info) {
	        	foreach ($info as $key => $value) {
					$response->rows[$ctr]['cell'][$key] = $value;
	        	}
				$ctr++;	        	
	        }
	    }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function listview_org()	{
		$dbprefix = $this->db->dbprefix;
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		$search = 1;
		$company_ids = 1;
		$division_ids = 1;
		$department_ids = 1;
		$employee_ids = 1;

		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$company_ids = implode(',', $this->input->post('company_id'));
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$division_ids = implode(',', $this->input->post('division_id'));
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$department_ids = implode(',', $this->input->post('department_id'));
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_ids = implode(',', $this->input->post('employee_id'));
		}

		$planning_period_id = $this->input->post('planning_period_id');
		
		$this->db->where('planning_period_id', $planning_period_id);
		$this->db->where('deleted', 0);
		$period_qry = $this->db->get('appraisal_planning_period');
		
		$employees = false;	
		$in_planning = false;
		if ($period_qry && $period_qry->num_rows() > 0) {
			$period_result = $period_qry->row();
			$employees = explode(',', $period_result->employee_id);
			$in_planning = explode(',', $period_result->employee_id);
		}

		if($this->input->post('employee_id') != " " &&  $this->input->post('employee_id') != "null"){
			$employees = $this->input->post('employee_id');
		}	

		if (is_array($employees)) {
			
			$employee_query = "SELECT *
								FROM {$dbprefix}user a 
								WHERE a.deleted = 0 AND a.employee_id IN (". implode(',', $employees). ")";
			
			if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
				$employee_query .= " AND a.company_id IN (" .$company_ids. ")";	
			}

			if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
				$employee_query .= " AND a.division_id IN (" .$division_ids. ")";
			}

			if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
				$employee_query .= " AND a.department_id IN (" .$department_ids. ")";
			}

			$employee_query .= " ORDER BY a.company_id, a.department_id, a.division_id ";
			
			$employee_result = $this->db->query($employee_query);
			if ($employee_result && $employee_result->num_rows() > 0) {
				$employees = array();
				foreach ($employee_result->result() as $key => $value) {
					$employees[] = $value->employee_id;
				}

				$employees = array_intersect($employees, $in_planning);
			}else{
				$employees = false;
			}
			
		}

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        

	        $total_pages = is_array($employees) ? ceil(count($employees)/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = count($employees);                        

	        $response->msg = "";

	        $ctr = 0;	        
	        
	        foreach ($employees as $employee_id) {
	        	$employee_qry = $this->db->query("SELECT 
						e.id_number,u.lastname, u.firstname, u.middleinitial,
						up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division, e.employed_date,TIMESTAMPDIFF(YEAR, employed_date, now()) AS service_years
					FROM hr_employee e
					LEFT JOIN hr_user u ON e.employee_id = u.employee_id
					LEFT JOIN hr_user_position up ON up.position_id = u.position_id 
					LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id 
					LEFT JOIN hr_position_classification pc ON pc.position_class_id = e.position_class_id 
					LEFT JOIN hr_user_company_department ucd ON u.department_id = ucd.department_id 
					LEFT JOIN hr_user_company_division ucdi ON u.division_id = ucdi.division_id 
					WHERE e.employee_id = ".$employee_id."");

	        	if($employee_qry && $employee_qry->num_rows() > 0){
					$employee = $employee_qry->row_array();
					
					$id_number 				 = $employee['id_number'];
		        	$employee_name 			 = $employee['firstname']. ' '. $employee['lastname'];
		        	$employee_position 		 = $employee['position'];
		        	$employee_rank 			 = $employee['job_rank'];
		        	$employee_position_class = $employee['position_class'];
		        	$employee_department	 = $employee['department'];
		        	$employee_division	 	 = $employee['division'];
		        	$employed_date 			 = $employee['employed_date'];
		        	$service_years 			 = $employee['service_years'];
		            
		            $response->rows[$ctr]['cell'][32] = "";
		        	
					$employee_appraisal_bsc_qry = $this->db->query("SELECT 
		    						eab.employee_id,
		    						eab.appraisal_period_id,
									up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division,
									IF(eab.employee_appraisal_status = 5, 1, 0) AS completed,
									eab.employee_appraisal_criteria_rating_weight_array,
									eab.employee_appraisal_criteria_rating_array,
									eab.employee_appraisal_or_raters_comments,
									eab.employee_appraisal_criteria_question_sec_rating_array,
									eab.employee_appraisal_criteria_year_end_comments,
									eap.employee_appraisal_criteria_mid_year_comments
								FROM hr_employee_appraisal_bsc eab 
								LEFT JOIN hr_user_position up ON up.position_id = eab.position_id 
								LEFT JOIN hr_user_rank ur ON ur.job_rank_id = eab.rank_id 
								LEFT JOIN hr_position_classification pc ON pc.position_class_id = eab.position_class_id 
								LEFT JOIN hr_user_company_department ucd ON eab.department_id = ucd.department_id 
								LEFT JOIN hr_user_company_division ucdi ON eab.division_id = ucdi.division_id 
								LEFT JOIN hr_employee_appraisal_status eas ON eab.employee_appraisal_status = eas.appraisal_status_id 
								LEFT JOIN hr_employee_appraisal_planning eap ON eab.appraisal_period_id = eap.appraisal_planning_period_id 
								WHERE eab.employee_id = ".$employee_id." AND eap.deleted = 0
									AND eab.appraisal_period_id = ".$planning_period_id );

					if ($employee_appraisal_bsc_qry && $employee_appraisal_bsc_qry->num_rows() > 0) {
						$employee_appraisal_bsc_row = $employee_appraisal_bsc_qry->row_array();

			        	$employee_position 		 = $employee_appraisal_bsc_row['position'];
			        	$employee_rank 			 = $employee_appraisal_bsc_row['job_rank'];
			        	$employee_position_class = $employee_appraisal_bsc_row['position_class'];
			        	$employee_department	 = $employee_appraisal_bsc_row['department'];
			        	$employee_division	 	 = $employee_appraisal_bsc_row['division'];

		    			$individual_development_plan = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_rating_weight_array']);
		    			$criteria_mid_year_comments = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_mid_year_comments']);
		    			$criteria_year_end_comments = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_year_end_comments']);
		    			
		    			$ctr_cell = 6;
						foreach ($individual_development_plan as $key => $value) {
							$employee_appraisal_criteria_question_row = $this->db->query("SELECT * FROM hr_employee_appraisal_criteria_question WHERE employee_appraisal_criteria_question_id = ".$key)->row();

							$response->rows[$ctr]['cell'][$ctr_cell] = $value;
		            		$ctr_cell++;
		            		$mid_comments = array();
		            		$year_comments = array();
		            		foreach ($criteria_mid_year_comments[$employee_appraisal_criteria_question_row->employee_appraisal_criteria_id][$key]['mid_year_comments'] as $approver_id => $comment) {
		            			$approver = $this->system->get_employee($approver_id);
		            			$year_end_comments = $criteria_year_end_comments[$employee_appraisal_criteria_question_row->employee_appraisal_criteria_id][$key]['year_end_comments'][$approver_id];
		            			$mid_comments[] = '<strong>'.$approver['firstname'].' '.$approver['lastname'] . '</strong> = ' .$comment;
		            			$year_comments[] = '<strong>'.$approver['firstname'].' '.$approver['lastname'] . '</strong> = ' .$year_end_comments;

		            		}

							$response->rows[$ctr]['cell'][$ctr_cell] = implode('<br>', $mid_comments);
		            		$ctr_cell++;
							
							$response->rows[$ctr]['cell'][$ctr_cell] = implode('<br>', $year_comments);
		            		$ctr_cell++;
						}
		    			// CORE
		    			$individual_development_plan_core = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_rating_array']);
						foreach ($individual_development_plan_core as $employee_appraisal_criteria_id => $employee_appraisal_criteria_array) {
							if($employee_appraisal_criteria_id == 12 || $employee_appraisal_criteria_id == 7){ $ctr_cell = 18; }
							if($employee_appraisal_criteria_id == 5){ $ctr_cell = 24; }
							if($employee_appraisal_criteria_id == 6){ $ctr_cell = 26; }

							$employee_appraisal_criteria_row = $this->db->query("SELECT * FROM hr_employee_appraisal_criteria WHERE employee_appraisal_criteria_id = ".$employee_appraisal_criteria_id)->row();
							if($employee_appraisal_criteria_row->is_core == 1){
								foreach ($employee_appraisal_criteria_array as $appraiser_id => $appraiser_array) {
									foreach ($appraiser_array as $competency_value_id => $competency_value_array) {
										$appraisal_competency_value_row = $this->db->query("SELECT * FROM hr_appraisal_competency_value WHERE competency_value_id = ".$competency_value_id)->row();
										if($employee_appraisal_criteria_id == 12 || $employee_appraisal_criteria_id == 7){
											$response->rows[$ctr]['cell'][$ctr_cell] = $competency_value_array['coach_rating'];
			            					$ctr_cell++;
											$response->rows[$ctr]['cell'][$ctr_cell] = $competency_value_array['coach_comment'];
										}
										else if($employee_appraisal_criteria_id == 6){
											$response->rows[$ctr]['cell'][$ctr_cell] = $competency_value_array['coach_rating'];
			            					$ctr_cell++;
											$response->rows[$ctr]['cell'][$ctr_cell] = $competency_value_array['coach_comment'];
										}
										else if($employee_appraisal_criteria_id == 5){
											$response->rows[$ctr]['cell'][$ctr_cell] = $competency_value_array['coach_rating'];
			            					$ctr_cell++;
											$response->rows[$ctr]['cell'][$ctr_cell] = $competency_value_array['year_end_comment'];
										}

		            					$ctr_cell++;
									}
								}
							}
						}

						$coach_rating_total = 0;
						$employee_appraisal_criteria_question_sec_rating_array = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_question_sec_rating_array']);
						foreach ($employee_appraisal_criteria_question_sec_rating_array as $criteria_id => $criteria_value) {
							$appraisal_criteria_row = $this->db->query("SELECT * FROM hr_employee_appraisal_criteria WHERE employee_appraisal_criteria_id = ".$criteria_id)->row();
							$coach_rating_total += $criteria_value * $appraisal_criteria_row->ratio_weighter_score;
						}

		            	$response->rows[$ctr]['cell'][32] = $coach_rating_total;
						$coach_comment = unserialize($employee_appraisal_bsc_row['employee_appraisal_or_raters_comments']);
		            	$response->rows[$ctr]['cell'][33] = $coach_comment[1];


					}
		            
		            $response->rows[$ctr]['cell'][0] = $id_number;
		            $response->rows[$ctr]['cell'][1] = $employee_name;
		            $response->rows[$ctr]['cell'][2] = $employee_position;
		            $response->rows[$ctr]['cell'][3] = $employee_rank;
		            $response->rows[$ctr]['cell'][4] = $employee_position_class;
		            $response->rows[$ctr]['cell'][5] = $employee_department;
		            $response->rows[$ctr]['cell'][6] = $employee_division;
		            $response->rows[$ctr]['cell'][7] = date($this->config->item('display_date_format_email_fb'),strtotime($employed_date));
		            $response->rows[$ctr]['cell'][8] = $service_years;
		            
		            $ctr++;
				}
	        }
	    }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {

		$this->listview_column_names = array('Employee No', 'Name', 'Position', 'Level', 'Classification', 'Department', 'Division', 'Date Hired', 'Service Years', 'Date of Last Promotion','Self Rating','Coach\'s Rating','Committee Rating');

		$this->listview_columns = array(
				array('name' => 'id_number'),				
				array('name' => 'employee_name'),				
				array('name' => 'position'),
				array('name' => 'level'),
				array('name' => 'classification'),
				array('name' => 'department_id'),
				array('name' => 'division_id'),
				array('name' => 'employed_date'),
				array('name' => 'service_years'),
				array('name' => 'date_last_promotion'),
				array('name' => 'rating'),
				array('name' => 'coach_rating'),
				array('name' => 'final_rating')
			);                                     
    }

    // this is the old before new request 20200217
	function _set_listview_query_org($listview_id = '', $view_actions = true) {

		$qry = "SELECT appraisal_year
							FROM hr_employee_appraisal_period						
							WHERE deleted = 0 
							GROUP BY appraisal_year ORDER BY appraisal_year";

		$period = $this->db->query($qry);

		$this->listview_column_names = array('Employee No', 'Name', 'Position', 'Level', 'Classification', 'Department', 'Division', 'Date Hired', 'Service Years', 'Date of Last Promotion');

		if ($period && $period->num_rows() > 0) {
			foreach ($period->result() as $row) {
				$this->listview_column_names[] = $row->appraisal_year . ' Results';
			}
		}

		$this->listview_columns = array(
				array('name' => 'id_number'),				
				array('name' => 'employee_name'),				
				array('name' => 'position'),
				array('name' => 'level'),
				array('name' => 'classification'),
				array('name' => 'department_id'),
				array('name' => 'division_id'),
				array('name' => 'employed_date'),
				array('name' => 'service_years'),
				array('name' => 'date_last_promotion')
			);                                     

		if ($period && $period->num_rows() > 0) {
			foreach ($period->result() as $row) {
				$this->listview_columns[] = array('name' => $row->appraisal_year);
			}
		}
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
        $this->load->library('encrypt');

        $objPHPExcel = new PHPExcel();
        
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            )
        );

        $styleArrayPageHeaderRight = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );

        $styleArrayHeader = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'borders' => array(
                'bottom' =>array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ),
            )
        );

        $styleBorder = array(
		  	'borders' => array(
		    	'allborders' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	)
		  	)
        );

        $styleArrayNumber = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );

        $objPHPExcel->getProperties()->setTitle("Performance Appraisal Report")
                    ->setDescription("Performance Appraisal Report");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

/*        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setAutoSize(true);*/
            
        //$objPHPExcel->getActiveSheet()->getStyle('A1:AH1')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A1',  'Performance Appraisal Report', PHPExcel_Cell_DataType::TYPE_STRING);

        // column header.
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		$fields = array('Employee No', 'Name', 'Position', 'Level', 'Classification', 'Department', 'Division', 'Date Hired', 'Service Years', 'Date of Last Promotion',
						'Self Rating (KRA)','Self Rating (Values)','Self Rating (Competencies)','Self Rating (Leadership Competencies)','Self Rating',
						'Coach\'s Rating (KRA)','Coach\'s Rating (Values)','Coach\'s Rating (Competencies)','Coach\'s Rating (Leadership Competencies)','Coach\'s Rating',
						'Committee Rating');

		foreach ($fields as $key => $field) {
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
			
			$objPHPExcel->getActiveSheet()->getColumnDimension($xcoor)->setAutoSize(true);

			$alpha_ctr++;
		}
 
        $objPHPExcel->getActiveSheet()->mergeCells('A1:'.$xcoor.'1');

 		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$company_ids = implode(',', $this->input->post('company_id'));
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$division_ids = implode(',', $this->input->post('division_id'));
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$department_ids = implode(',', $this->input->post('department_id'));
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_ids = implode(',', $this->input->post('employee_id'));
		}

		$planning_period_id = $this->input->post('planning_period_id');

		$employee_query = "SELECT e.employee_id,e.id_number,u.lastname, u.firstname, u.middleinitial,
							up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division, e.employed_date,TIMESTAMPDIFF(YEAR, employed_date, now()) AS service_years,
							eap.appraisal_year,eab.final_rating,e.last_promotion_date,eab.employee_appraisal_self_rating_kra,
							eab.employee_appraisal_self_rating_values,eab.employee_appraisal_self_rating_competencies,eab.employee_appraisal_self_rating_leadership,
							eab.employee_appraisal_coach_rating_kra,eab.employee_appraisal_coach_rating_values,eab.employee_appraisal_coach_rating_competencies,eab.employee_appraisal_coach_rating_leadership,
							eab.rating,eab.coach_rating
							FROM hr_employee_appraisal_bsc eab
							LEFT JOIN hr_user u ON eab.employee_id = u.employee_id
							LEFT JOIN hr_employee e ON e.employee_id = u.employee_id							
							LEFT JOIN hr_user_position up ON up.position_id = u.position_id 
							LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id 
							LEFT JOIN hr_position_classification pc ON pc.position_class_id = e.position_class_id 
							LEFT JOIN hr_user_company_department ucd ON u.department_id = ucd.department_id 
							LEFT JOIN hr_user_company_division ucdi ON u.division_id = ucdi.division_id 							
							LEFT JOIN hr_appraisal_planning_period app ON eab.appraisal_period_id = app.planning_period_id
							LEFT JOIN hr_employee_appraisal_period eap ON app.planning_period_id = eap.planning_period_id
							WHERE u.deleted = 0 AND eab.deleted = 0 AND app.deleted = 0 AND eap.deleted = 0";
		
		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$employee_query .= " AND u.company_id IN (" .$company_ids. ")";	
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$employee_query .= " AND u.division_id IN (" .$division_ids. ")";
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$employee_query .= " AND u.department_id IN (" .$department_ids. ")";
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_query .= " AND u.employee_id IN (" .$employee_ids. ")";
		}

		if($this->input->post('planning_period_id') != '' && $this->input->post('planning_period_id') != 'null'){
			$employee_query .= " AND eap.planning_period_id = ".$planning_period_id."";
		}

		$employee_query .= " ORDER BY u.employee_id,u.company_id, u.department_id, u.division_id, eap.appraisal_year ";

		$employee_result = $this->db->query($employee_query);

		$employees_array = array();
		if ($employee_result && $employee_result->num_rows() > 0) {
			foreach ($employee_result->result() as $row) {
				$employees_array[$row->employee_id] = array($row->id_number,$row->firstname. ' ' . $row->lastname,$row->position,$row->job_rank,$row->position_class,$row->department,$row->division,$row->employed_date,$row->service_years,$row->last_promotion_date,
															$row->employee_appraisal_self_rating_kra,$row->employee_appraisal_self_rating_values,$row->employee_appraisal_self_rating_competencies,$row->employee_appraisal_self_rating_leadership,$row->rating,
															$row->employee_appraisal_coach_rating_kra,$row->employee_appraisal_coach_rating_values,$row->employee_appraisal_coach_rating_competencies,$row->employee_appraisal_coach_rating_leadership,$row->coach_rating,
															$row->final_rating);
			}
		}

        $line = 7;	
		foreach ($employees_array as $employee_id => $info) {
			$alpha_ctr = 0;
			$sub_ctr   = 0;								
			foreach ($info as $key => $value) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$activeSheet->setCellValue($xcoor . $line, $value);
				if ($key != 8)
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $value, PHPExcel_Cell_DataType::TYPE_STRING); 
				else
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $value, PHPExcel_Cell_DataType::TYPE_STRING);
				
				$alpha_ctr++;
			}

			$line++;
    	}

    	$objPHPExcel->getActiveSheet()->getStyle('A6:'.$xcoor . ($line - 1) .'')->applyFromArray($styleBorder);

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename=Performance_Appraisal_Result_'.date('Y-m-d').'.xls');
        header('Content-Transfer-Encoding: binary');
    
        $objWriter->save('php://output');
	}

	// this is the old before new request 20200217
	function export_org_org() {
        $this->load->library('PHPExcel');
        $this->load->library('PHPExcel/IOFactory');
        $this->load->library('encrypt');

        $objPHPExcel = new PHPExcel();
        
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            )
        );

        $styleArrayPageHeaderRight = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );

        $styleArrayHeader = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'borders' => array(
                'bottom' =>array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ),
            )
        );

        $styleBorder = array(
		  	'borders' => array(
		    	'allborders' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	)
		  	)
        );

        $styleArrayNumber = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );

        $objPHPExcel->getProperties()->setTitle("Performance Appraisal Report")
                    ->setDescription("Performance Appraisal Report");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

/*        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setAutoSize(true);*/
            
        //$objPHPExcel->getActiveSheet()->getStyle('A1:AH1')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A1',  'Performance Appraisal Report', PHPExcel_Cell_DataType::TYPE_STRING);

        // column header.
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		$qry = "SELECT appraisal_year
							FROM hr_employee_appraisal_period						
							WHERE deleted = 0 
							GROUP BY appraisal_year ORDER BY appraisal_year";

		$period = $this->db->query($qry);

		$fields = array('Employee No', 'Name', 'Position', 'Level', 'Classification', 'Department', 'Division', 'Date Hired', 'Service Years', 'Date of Last Promotion');

		if ($period && $period->num_rows() > 0) {
			foreach ($period->result() as $row) {
				$fields[] = $row->appraisal_year . ' Results';
			}
		}

		foreach ($fields as $key => $field) {
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
			
			$objPHPExcel->getActiveSheet()->getColumnDimension($xcoor)->setAutoSize(true);

			$alpha_ctr++;
		}
 
        $objPHPExcel->getActiveSheet()->mergeCells('A1:'.$xcoor.'1');

 		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$company_ids = implode(',', $this->input->post('company_id'));
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$division_ids = implode(',', $this->input->post('division_id'));
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$department_ids = implode(',', $this->input->post('department_id'));
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_ids = implode(',', $this->input->post('employee_id'));
		}

		$planning_period_id = $this->input->post('planning_period_id');

		$employee_query = "SELECT e.employee_id,e.id_number,u.lastname, u.firstname, u.middleinitial,
							up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division, e.employed_date,TIMESTAMPDIFF(YEAR, employed_date, now()) AS service_years,
							eap.appraisal_year,eab.final_rating,e.last_promotion_date
							FROM hr_employee_appraisal_bsc eab
							LEFT JOIN hr_user u ON eab.employee_id = u.employee_id
							LEFT JOIN hr_employee e ON e.employee_id = u.employee_id							
							LEFT JOIN hr_user_position up ON up.position_id = u.position_id 
							LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id 
							LEFT JOIN hr_position_classification pc ON pc.position_class_id = e.position_class_id 
							LEFT JOIN hr_user_company_department ucd ON u.department_id = ucd.department_id 
							LEFT JOIN hr_user_company_division ucdi ON u.division_id = ucdi.division_id 							
							LEFT JOIN hr_appraisal_planning_period app ON eab.appraisal_period_id = app.planning_period_id
							LEFT JOIN hr_employee_appraisal_period eap ON app.planning_period_id = eap.planning_period_id
							WHERE u.deleted = 0 AND eab.deleted = 0 AND app.deleted = 0 AND eap.deleted = 0";
		
		if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
			$employee_query .= " AND u.company_id IN (" .$company_ids. ")";	
		}

		if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
			$employee_query .= " AND u.division_id IN (" .$division_ids. ")";
		}

		if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
			$employee_query .= " AND u.department_id IN (" .$department_ids. ")";
		}

		if($this->input->post('employee_id') != '' && $this->input->post('employee_id') != 'null'){
			$employee_query .= " AND u.employee_id IN (" .$employee_ids. ")";
		}

		if($this->input->post('planning_period_id') != '' && $this->input->post('planning_period_id') != 'null'){
			$employee_query .= " AND eap.planning_period_id = ".$planning_period_id."";
		}

		$employee_query .= " GROUP BY eab.employee_id,eap.appraisal_year ORDER BY u.employee_id,u.company_id, u.department_id, u.division_id, eap.appraisal_year ";

		$employee_result = $this->db->query($employee_query);

		$employees_array = array();
		if ($employee_result && $employee_result->num_rows() > 0) {
			foreach ($employee_result->result() as $row) {
				if (!array_key_exists($row->employee_id, $employees_array)) {
					$employees_array[$row->employee_id] = array($row->id_number,$row->firstname. ' ' . $row->lastname,$row->position,$row->job_rank,$row->position_class,$row->department,$row->division,$row->employed_date,$row->service_years,$row->last_promotion_date,$row->final_rating);
				}
				else {									
					if (!array_key_exists($row->appraisal_year, $employees_array[$row->emnployee_id][$row->appraisal_year])) {
						$employees_array[$row->employee_id][] = $row->final_rating;
					}
				}				
			}
		}

        $line = 7;	
		foreach ($employees_array as $employee_id => $info) {
			$alpha_ctr = 0;
			$sub_ctr   = 0;								
			foreach ($info as $key => $value) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$activeSheet->setCellValue($xcoor . $line, $value);
				if ($key != 8)
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $value, PHPExcel_Cell_DataType::TYPE_STRING); 
				else
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $value, PHPExcel_Cell_DataType::TYPE_STRING);
				
				$alpha_ctr++;
			}

			$line++;
    	}

    	$objPHPExcel->getActiveSheet()->getStyle('A6:'.$xcoor . ($line - 1) .'')->applyFromArray($styleBorder);

        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename=Performance_Appraisal_Result_'.date('Y-m-d').'.xls');
        header('Content-Transfer-Encoding: binary');
    
        $objWriter->save('php://output');
	}

	function export_org() {
		$company_ids = implode(',', $this->input->post('company_id'));
		$division_ids = implode(',', $this->input->post('division_id'));
		$department_ids = implode(',', $this->input->post('department_id'));
		// $employee_ids = implode(',', $this->input->post('employee_id'));

        $this->load->library('PHPExcel');
        $this->load->library('PHPExcel/IOFactory');
        $this->load->library('encrypt');

        $objPHPExcel = new PHPExcel();
        
        //Initialize style
        $styleArray = array(
            'font' => array(
                'bold' => true,
            )
        );

        $styleArrayPageHeaderRight = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );

        $styleArrayHeader = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'borders' => array(
                'bottom' =>array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ),
            )
        );

        $styleArrayNumber = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );

        $objPHPExcel->getProperties()->setTitle("Performance Evaluation Rating")
                    ->setDescription("Performance Evaluation Rating");
                       
        // Assign cell values
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setAutoSize(true);
            
        $objPHPExcel->getActiveSheet()->mergeCells('A1:AH1');
        $objPHPExcel->getActiveSheet()->getStyle('A1:AH1')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A1',  'Performance Evaluation Rating', PHPExcel_Cell_DataType::TYPE_STRING);

        $objPHPExcel->getActiveSheet()->getStyle('A2:AH2')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:F3');
        $objPHPExcel->getActiveSheet()->mergeCells('G3:R3');
        $objPHPExcel->getActiveSheet()->mergeCells('S3:X3');
        $objPHPExcel->getActiveSheet()->mergeCells('AA3:AF3');
        $objPHPExcel->getActiveSheet()->getStyle('A3:AH3')->applyFromArray($styleArrayHeader);
        $objPHPExcel->getActiveSheet()->getStyle('A4:AH4')->applyFromArray($styleArrayHeader);

        // column header.
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A3', 'Employee Iinformation', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('G3', 'Company Objectives / Goals');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('S3', 'Core Competencies');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AA3', 'Corporate Values');

        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A4', 'Name', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B4', 'Position');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('C4', 'Level');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('D4', 'Classification');
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('E4', 'Department', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('F4', 'Division', PHPExcel_Cell_DataType::TYPE_STRING);

        $objPHPExcel->getActiveSheet()->setCellValueExplicit('G4', 'Financial Perspective', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('H4', 'Mid Year Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('I4', 'Year End Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('J4', 'Customer Perspective', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('K4', 'Mid Year Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('L4', 'Year End Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('M4', 'Best Business Practices Perspective', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('N4', 'Mid Year Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('O4', 'Year End Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('P4', 'Learning and Growth Perspective', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('Q4', 'Mid Year Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('R4', 'Year End Comment', PHPExcel_Cell_DataType::TYPE_STRING);

        $objPHPExcel->getActiveSheet()->setCellValueExplicit('S4', 'Customer Orientation', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('T4', 'Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('U4', 'Professional Competence', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('V4', 'Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('W4', 'Personal Management', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('X4', 'Comment', PHPExcel_Cell_DataType::TYPE_STRING);

        $objPHPExcel->getActiveSheet()->setCellValueExplicit('Y4', 'Attendance and Punctuality', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('Z4', 'Comment', PHPExcel_Cell_DataType::TYPE_STRING);

        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AA4', 'Innovation', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AB4', 'Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AC4', 'Relationships', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AD4', 'Comment', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AE4', 'Excellence', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AF4', 'Comment', PHPExcel_Cell_DataType::TYPE_STRING);

        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AG4', 'Coachs Rating', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AH4', 'Comments', PHPExcel_Cell_DataType::TYPE_STRING);

        $line = 6;
    	$this->db->where('planning_period_id', $_POST['planning_period_id']);
    	$appraisal_planning_period_qry = $this->db->get('appraisal_planning_period');

        if($appraisal_planning_period_qry && $appraisal_planning_period_qry->num_rows() > 0){
    		foreach ($appraisal_planning_period_qry->result() as $appraisal_planning_period_row) {
    			$employee_ids = explode(',',$appraisal_planning_period_row->employee_id);
					
				$employee_query = "SELECT *
									FROM hr_user a 
									WHERE a.deleted = 0 AND a.employee_id IN (". implode(',', $employee_ids). ")";
				
				if($this->input->post('company_id') != '' && $this->input->post('company_id') != 'null'){
					$employee_query .= " AND a.company_id IN (" .$company_ids. ")";	
				}

				if($this->input->post('division_id') != '' && $this->input->post('division_id') != 'null'){
					$employee_query .= " AND a.division_id IN (" .$division_ids. ")";
				}

				if($this->input->post('department_id') != '' && $this->input->post('department_id') != 'null'){
					$employee_query .= " AND a.department_id IN (" .$department_ids. ")";
				}

				$employee_query .= " ORDER BY a.company_id, a.department_id, a.division_id ";
				
				$employee_result = $this->db->query($employee_query);
				if ($employee_result && $employee_result->num_rows() > 0) {
					$employee_ids = array();
					foreach ($employee_result->result() as $employee_row) {
						$employee_ids[] = $employee_row->employee_id;
					}
				}

    			foreach ($employee_ids as $employee_id) {
    				$employee_qry = $this->db->query("SELECT 
    						u.lastname, u.firstname, u.middleinitial,
    						up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division
						FROM hr_employee e
						LEFT JOIN hr_user u ON e.employee_id = u.employee_id
						LEFT JOIN hr_user_position up ON up.position_id = u.position_id 
						LEFT JOIN hr_user_rank ur ON ur.job_rank_id = e.rank_id 
						LEFT JOIN hr_position_classification pc ON pc.position_class_id = e.position_class_id 
						LEFT JOIN hr_user_company_department ucd ON u.department_id = ucd.department_id 
						LEFT JOIN hr_user_company_division ucdi ON u.division_id = ucdi.division_id 
						WHERE e.employee_id = ".$employee_id);

    				if($employee_qry && $employee_qry->num_rows() > 0){
	    				$employee = $employee_qry->row_array();
	    				$employee_nam = $employee['lastname'].', '.$employee['firstname'].' '.$employee['middleinitial'];

	    				$employee_appraisal_bsc_qry = $this->db->query("SELECT 
	    						eab.employee_id,
	    						eab.appraisal_period_id,
								up.position, ur.job_rank, pc.position_class, ucd.department, ucdi.division,
								IF(eab.employee_appraisal_status = 5, 1, 0) AS completed,
								eab.employee_appraisal_criteria_rating_weight_array,
								eab.employee_appraisal_criteria_rating_array,
								eab.employee_appraisal_or_raters_comments,
								eab.employee_appraisal_criteria_question_sec_rating_array,
								eab.employee_appraisal_criteria_year_end_comments,
								eap.employee_appraisal_criteria_mid_year_comments
							FROM hr_employee_appraisal_bsc eab 
							LEFT JOIN hr_user_position up ON up.position_id = eab.position_id 
							LEFT JOIN hr_user_rank ur ON ur.job_rank_id = eab.rank_id 
							LEFT JOIN hr_position_classification pc ON pc.position_class_id = eab.position_class_id 
							LEFT JOIN hr_user_company_department ucd ON eab.department_id = ucd.department_id 
							LEFT JOIN hr_user_company_division ucdi ON eab.division_id = ucdi.division_id 
							LEFT JOIN hr_employee_appraisal_status eas ON eab.employee_appraisal_status = eas.appraisal_status_id 
							LEFT JOIN hr_employee_appraisal_planning eap ON eab.appraisal_period_id = eap.appraisal_planning_period_id AND eab.employee_id = eap.employee_id
							WHERE eab.employee_id = ".$employee_id." AND eap.deleted = 0
								AND eab.appraisal_period_id = ".$appraisal_planning_period_row->planning_period_id);

						$employee_pos = $employee['position'];
						$employee_lvl = $employee['job_rank'];
						$employee_cls = $employee['position_class'] == '' ? '' : $employee['position_class'];
						$employee_dep = $employee['department'];
						$employee_div = $employee['division'];
		    			$rows = array();
		    			$rows_comment = array();
		    			$coach_rating_total = "";
		    			$coach_comment = "";
		    			$ctr_cell = 1;
						
						if($employee_appraisal_bsc_qry && $employee_appraisal_bsc_qry->num_rows() > 0){
							$employee_appraisal_bsc_row = $employee_appraisal_bsc_qry->row_array();

							$employee_pos = $employee_appraisal_bsc_row['position'];
							$employee_lvl = $employee_appraisal_bsc_row['job_rank'];
							$employee_cls = $employee_appraisal_bsc_row['position_class'];
							$employee_dep = $employee_appraisal_bsc_row['department'];
							$employee_div = $employee_appraisal_bsc_row['division'];

							$individual_development_plan = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_rating_weight_array']);
			    			$criteria_mid_year_comments = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_mid_year_comments']);
			    			$criteria_year_end_comments = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_year_end_comments']);

			    			foreach ($individual_development_plan as $key => $value) {
								$employee_appraisal_criteria_question_row = $this->db->query("SELECT * FROM hr_employee_appraisal_criteria_question WHERE employee_appraisal_criteria_question_id = ".$key)->row();

								$rows[$ctr_cell] = $value;
			            		$ctr_cell++;
			            		
			            		$mid_comments = array();
			            		$year_comments = array();
			            		foreach ($criteria_mid_year_comments[$employee_appraisal_criteria_question_row->employee_appraisal_criteria_id][$key]['mid_year_comments'] as $approver_id => $comment) {
			            			$approver = $this->system->get_employee($approver_id);
			            			$year_end_comments = $criteria_year_end_comments[$employee_appraisal_criteria_question_row->employee_appraisal_criteria_id][$key]['year_end_comments'][$approver_id];
			            			$mid_comments[] = $approver['firstname'].' '.$approver['lastname'] . ' = ' .$comment;
			            			$year_comments[] = $approver['firstname'].' '.$approver['lastname'] . ' = ' .$year_end_comments;

		            			}

								$rows[$ctr_cell] = implode("\n", $mid_comments);
			            		$ctr_cell++;
								$rows[$ctr_cell] = implode("\n", $year_comments);
			            		$ctr_cell++;
							}
			    			// CORE
			    			$individual_development_plan_core = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_rating_array']);
							foreach ($individual_development_plan_core as $employee_appraisal_criteria_id => $employee_appraisal_criteria_array) {
								$employee_appraisal_criteria_row = $this->db->query("SELECT * FROM hr_employee_appraisal_criteria WHERE employee_appraisal_criteria_id = ".$employee_appraisal_criteria_id)->row();
								if($employee_appraisal_criteria_row->is_core == 1){
									foreach ($employee_appraisal_criteria_array as $appraiser_id => $appraiser_array) {
										foreach ($appraiser_array as $competency_value_id => $competency_value_array) {
											$appraisal_competency_value_row = $this->db->query("SELECT * FROM hr_appraisal_competency_value WHERE competency_value_id = ".$competency_value_id)->row();
											if($employee_appraisal_criteria_id == 12 || $employee_appraisal_criteria_id == 6 || $employee_appraisal_criteria_id == 7){
												$rows[$ctr_cell] = $competency_value_array['coach_rating'];
			            						$ctr_cell++;
												$rows[$ctr_cell] = $competency_value_array['coach_comment'];
			            						$ctr_cell++;
											}
											else if($employee_appraisal_criteria_id == 5){
												$rows[25] = $competency_value_array['coach_rating'];
												$rows[26] = $competency_value_array['year_end_comment'];
											}
										}
									}
								}
							}

							$coach_rating_total = 0;
							$employee_appraisal_criteria_question_sec_rating_array = unserialize($employee_appraisal_bsc_row['employee_appraisal_criteria_question_sec_rating_array']);
							foreach ($employee_appraisal_criteria_question_sec_rating_array as $criteria_id => $criteria_value) {
								$appraisal_criteria_row = $this->db->query("SELECT * FROM hr_employee_appraisal_criteria WHERE employee_appraisal_criteria_id = ".$criteria_id)->row();
								$coach_rating_total += $criteria_value * $appraisal_criteria_row->ratio_weighter_score;
							}

							$coach_comment = unserialize($employee_appraisal_bsc_row['employee_appraisal_or_raters_comments']);
						}

						$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$line, $employee_nam, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$line, $employee_pos, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$line, $employee_lvl, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$line, $employee_cls, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$line, $employee_dep, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$line, $employee_div, PHPExcel_Cell_DataType::TYPE_STRING);

						$objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$line, $rows[1], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$line, $rows[2], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$line, $rows[3], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$line, $rows[4], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('K'.$line, $rows[5], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('L'.$line, $rows[6], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('M'.$line, $rows[7], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('N'.$line, $rows[8], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('O'.$line, $rows[9], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('P'.$line, $rows[10], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('Q'.$line, $rows[11], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('R'.$line, $rows[12], PHPExcel_Cell_DataType::TYPE_STRING);

						$objPHPExcel->getActiveSheet()->setCellValueExplicit('S'.$line, $rows[13], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('T'.$line, $rows[14], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('U'.$line, $rows[15], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('V'.$line, $rows[16], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('W'.$line, $rows[17], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('X'.$line, $rows[18], PHPExcel_Cell_DataType::TYPE_STRING);


						$objPHPExcel->getActiveSheet()->setCellValueExplicit('Y'.$line, $rows[25], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('Z'.$line, $rows[26], PHPExcel_Cell_DataType::TYPE_STRING);


						$objPHPExcel->getActiveSheet()->setCellValueExplicit('AA'.$line, $rows[19], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('AB'.$line, $rows[20], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('AC'.$line, $rows[21], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('AD'.$line, $rows[22], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('AE'.$line, $rows[23], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('AF'.$line, $rows[24], PHPExcel_Cell_DataType::TYPE_STRING);

						$objPHPExcel->getActiveSheet()->setCellValueExplicit('AG'.$line, $coach_rating_total, PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('AH'.$line, $coach_comment[1], PHPExcel_Cell_DataType::TYPE_STRING);

						$line++;
					}
    			}
    		}
    	}
        // Save it as an excel 2003 file
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment;filename=Performance_Evaluation_Rating_'.date('Y-m-d').'.xls');
        header('Content-Transfer-Encoding: binary');
    
        $objWriter->save('php://output');
	}

	function get_employees()
	{

		if (IS_AJAX)
		{
			$department_id 	 = $this->input->post('department_id');
			$division_id	 = $this->input->post('division_id');
			$company_id 	 = $this->input->post('company_id');

			$options = '';
			if ($company_id && $company_id != 'null') {
				$company = 'company_id IN ('.$company_id.')';	
				$this->db->where($company);
			}

			if ($department_id && $department_id != 'null') {
				$this->db->where_in('department_id IN ('.$department_id.')');
			}

			if ($division_id && $division_id != 'null') {
				$this->db->where_in('user.division_id IN ('.$division_id.')');
			}

			$this->db->where('inactive', 0);
			$this->db->where('user.deleted', 0);
			$this->db->join('employee', 'employee.employee_id = user.employee_id');
			$this->db->order_by('firstname,lastname', 'ASC');
			$result = $this->db->get('user');
			
			
			// $this->db->where($this->key_field, $record_id);
			// $record = $this->db->get($this->module_table);
			$response['employees'] = '';
			// if ($record && $record->num_rows() > 0) {
			// 	$rec = $record->row();
			// 	$employees = $rec->employee_id;
			// 	$response['employees'] = explode(',', $employees);
			// }

			if ($result->num_rows() > 0) {
				$employee = $result->result();
				
				foreach ($employee as $emp) {
					$options .= '<option value="'.$emp->employee_id.'">'.$emp->firstname." ".$emp->middleinitial." ".$emp->lastname. " ".$emp->aux.'</option>';
				}

				$response['result'] = $options;
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

		
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


	function get_period()
	{
		$this->db->where('deleted', 0);
		$periods = $this->db->get('appraisal_planning_period');

		$pms_periods = false;

		if ($periods && $periods->num_rows() > 0) {
			$pms_periods = $periods->result();
		}
		
		return $pms_periods;
	}

	function get_aprpaisal_period()
	{
		$this->db->where('employee_appraisal_period.deleted', 0);
		$this->db->where('employee_appraisal_period.employee_appraisal_period_status_id', 2);
		$this->db->join('appraisal_planning_period','appraisal_planning_period.planning_period_id = employee_appraisal_period.planning_period_id','left');
		$periods = $this->db->get('employee_appraisal_period');

		$pms_periods = false;

		if ($periods && $periods->num_rows() > 0) {
			$pms_periods = $periods->result();
		}
		
		return $pms_periods;
	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>