<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_overtime_report extends MY_Controller
{
	function __construct(){
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
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['scripts'][] = multiselect_script();
		$data['content'] = 'dtr/ot_summary/overtime_report';
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		$this->load->model('uitype_edit');
		
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
	// END - default module functions

	// START custom module funtions
	function get_report(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect( base_url().$this->module_link );
		}

		$this->load->helper('time_upload');
		$report_by = $this->input->post('report_by');

		$this->date_from = date('Y-m-d', strtotime($this->input->post('date_from')));
		$this->date_to = date('Y-m-d', strtotime($this->input->post('date_to')));

		$this->db->select('department_code, department_id', false);
		$this->db->where('department_code != ""');
		$this->db->order_by('department_code');
		$this->db->group_by('department_code');
		$dept_res = $this->db->get('user_company_department')->result();

		$response->rows = array();
		$response->records = 0;
		foreach( $dept_res as $department ){
			$response->rows[$response->records]['id'] = $cell[0] = 'cost_code-'.$department->department_id;
			$cell[1] =  $department->department_code;//cost code
			$cell[2] =  ''; //ot_hours
			$cell[3] =  ''; //ot_code
			$cell[4] =  ''; //ot_amt
			$cell[5] = 0;
			$cell[6] = null; //parent
			$cell[7] = false; //leaf
			$cell[8] = true; //expanded field			
			
			$response_index = $response->records;
			$response->rows[$response->records]['cell'] = $cell;
			$response->records++;
			
			$new_rows = $this->_employee_name($department->department_id, $response);
			if($new_rows){
				$response = $new_rows;
			}
			else{
				$response->rows[$response_index]['cell'][7] = true;
			}
		}

		$response->page = 1;
		$response->total = 1;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);		
	}

	function _employee_name( $department_id, $response ){
		// $cost_code_index = $response->records - 1;

		$year_from = date('Y', strtotime($this->date_from));
		$year_to = date('Y', strtotime($this->date_to));
		$user = array();
		$qry = "SELECT CONCAT(lastname,', ',firstname,' ', middleinitial,' ', aux ) AS employee_name, b.id_number, c.department_code, a.department_id, a.employee_id
		FROM {$this->db->dbprefix}user a
			LEFT JOIN {$this->db->dbprefix}employee b
				ON a.employee_id = b.employee_id
			LEFT JOIN {$this->db->dbprefix}user_company_department c
				ON a.department_id = c.department_id
		WHERE a.department_id = '{$department_id}' -- AND a.department_id != '' AND a.department_id != '0' and a.employee_id = '97'
		ORDER BY  c.department_code, a.lastname";
		$employee = $this->db->query($qry);

		if($employee) {
			if($employee->num_rows() > 0){
				foreach($employee->result() as $employee_res){

					$department_id = $employee_res->department_id;
					$employee_name = $employee_res->employee_name;
					$id_number = $employee_res->id_number;
					$employee_id = $employee_res->employee_id;
					$user[] = array('id_number' => $id_number, 'employee_name' => $employee_name, 'department_id' => $department_id, 'employee_id' => $employee_id );
				}
			}	
		}
		// dbug($user);
		if(sizeof($user) > 0){
			foreach($user as $user_key => $user ) {
				$response->rows[$response->records]['id'] = $cell[0] = 'employee_id-'.$user['employee_id'];
				$cell[1] =  $user['employee_name'];
				$cell[2] =  ''; //ot_hours
				$cell[3] =  ''; //ot_code
				$cell[4] =  ''; //ot_amt
				$cell[5] = 1; //level
				$cell[6] = 'cost_code-'.$user['department_id']; //parent
				$cell[7] = false; //leaf
				$cell[8] = true; //expanded field	
				$response_index = $response->records;
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				
				$new_rows = $this->_employee_dates_summary($user['employee_id'], $response);
				if($new_rows){
					$response = $new_rows;
				}
				else{
					$response->rows[$response_index]['cell'][7] = true;
				}
			}
			return $response;
		}

		return false;
	}

	function _employee_dates_summary( $employee_id, $response ){
		$employee_index = $response->records - 1;

		$year = array();
		$total = array();

		$qry = "SELECT 
			e.date,  
			a.ot, 
			a.ndot, 
			e.employee_id,
			IF(MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved)) AS date_max 
		FROM {$this->db->dbprefix}employee_oot e
		LEFT JOIN (SELECT ot, ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
		    UNION ALL 
		    SELECT  ot, ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
		    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
		WHERE e.employee_id = '{$employee_id}'
		AND e.form_status_id = 3
		AND ( a.ot > 0 AND a.ot IS NOT NULL 
				OR
			  a.ndot > 0 AND a.ndot IS NOT NULL
			)
		GROUP BY a.employee_id , a.date
		HAVING DATE(date_max) BETWEEN '{$this->date_from}' AND '{$this->date_to}'
		ORDER BY a.date";
		// dbug($qry);
		$dtrs = $this->db->query($qry);

		if($dtrs) {
			if($dtrs->num_rows() > 0){
				foreach($dtrs->result() as $dtr){
					$date = $dtr->date;
					$employee_id = $dtr->employee_id;
					$year[] = array('date' => $date, 'employee_id' => $employee_id);
				}
			}
		}

		if(sizeof($year) > 0){
			foreach($year as $year_key => $year ) {
				$response->rows[$response->records]['id'] = $cell[0] = 'employee_date-'.$year['employee_id'].'-'.$year['date'];
				$cell[1] =  date("F d, Y", strtotime($year['date']));
				$cell[2] =  ''; //ot_hours
				$cell[3] =  ''; //ot_code
				$cell[4] =  ''; //ot_amt
				$cell[5] = 2; //level
				$cell[6] = 'employee_id-'.$year['employee_id']; //parent
				$cell[7] = false; //leaf
				$cell[8] = true; //expanded field	
				$response_index = $response->records;
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				
				$new_rows = $this->_employee_ot_summary($year['employee_id'], $year['date'], $response);
				if($new_rows){
					$response = $new_rows;
				}
				else{
					$response->rows[$response_index]['cell'][7] = true;
				}
			}
			return $response;
		}
		

		return false;
	}

	function _employee_ot_summary( $employee_id, $date, $response ){
		// $employee_index = $response->records - 1;

		$ot_array = array();
		$total = array();
		$qry = "SELECT 
				  a.date,
				  a.ot AS ot_res,
				  a.employee_id,
				  'REG OT' AS ot_code
				FROM
				(SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot > 0 
				    AND a.ot IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'reg'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot_excess AS ot_res,
				  a.employee_id,
				  'REG OTX' AS ot_code
				FROM
				(SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'reg'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot AS ot_res,
				  a.employee_id,
				  'REG ND OT' AS ot_code
				FROM
				(SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'reg'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot_excess AS ot_res,
				  a.employee_id,
				  'REG ND OTX' AS ot_code
				FROM
				(SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'reg'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot AS ot_res,
				  a.employee_id,
				  'REST OT' AS ot_code
				FROM
				(SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot > 0 
				    AND a.ot IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'rd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot_excess AS ot_res,
				  a.employee_id,
				  'REST OTX' AS ot_code
				FROM
				(SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'rd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot AS ot_res,
				  a.employee_id,
				  'REST ND OT' AS ot_code
				FROM
				(SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'rd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot_excess AS ot_res,
				  a.employee_id,
				  'REST ND OTX' AS ot_code
				FROM
				(SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'rd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot AS ot_res,
				  a.employee_id,
				  'LH OT' AS ot_code
				FROM
				(SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot > 0 
				    AND a.ot IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'leg'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot_excess AS ot_res,
				  a.employee_id,
				  'LH OTX' AS ot_code
				FROM
				(SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'leg'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot AS ot_res,
				  a.employee_id,
				  'LH ND OT' AS ot_code
				FROM
				(SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'leg'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot_excess AS ot_res,
				  a.employee_id,
				  'LH ND OTX' AS ot_code
				FROM
				(SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'leg'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot AS ot_res,
				  a.employee_id,
				  'SH OT' AS ot_code
				FROM
				(SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot > 0 
				    AND a.ot IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'spe'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot_excess AS ot_res,
				  a.employee_id,
				  'SH OTX' AS ot_code
				FROM
				(SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'spe'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot AS ot_res,
				  a.employee_id,
				  'SH ND OT' AS ot_code
				FROM
				(SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'spe'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot_excess AS ot_res,
				  a.employee_id,
				  'SH ND OTX' AS ot_code
				FROM
				(SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'spe'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot AS ot_res,
				  a.employee_id,
				  'LH RD OT' AS ot_code
				FROM
				(SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot > 0 
				    AND a.ot IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'legrd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot_excess AS ot_res,
				  a.employee_id,
				  'LH RD OTX' AS ot_code
				FROM
				(SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'legrd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot AS ot_res,
				  a.employee_id,
				  'LH RD ND OT' AS ot_code
				FROM
				(SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'legrd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot_excess AS ot_res,
				  a.employee_id,
				  'LH RD ND OTX' AS ot_code
				FROM
				(SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'legrd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot AS ot_res,
				  a.employee_id,
				  'SH RD OT' AS ot_code
				FROM
				(SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot > 0 
				    AND a.ot IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'sperd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ot_excess AS ot_res,
				  a.employee_id,
				  'SH RD OTX' AS ot_code
				FROM
				(SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				  AND a.day_type = 'sperd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot AS ot_res,
				  a.employee_id,
				  'SH RD ND OT' AS ot_code
				FROM
				(SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'sperd'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.ndot_excess AS ot_res,
				  a.employee_id,
				  'SH RD ND OTX' AS ot_code
				FROM
				(SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
			    UNION ALL 
			    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a  
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.date = '{$date}'
				  AND a.day_type = 'sperd'
				  ";
		$ots = $this->db->query($qry);

		if($ots) {
			if($ots->num_rows() > 0){
				foreach($ots->result() as $ot){
					$date = $ot->date;
					$employee_id = $ot->employee_id;
					$ot_res = $ot->ot_res;
					$ot_code = $ot->ot_code;
					$ot_array[] = array('date' => $date, 'employee_id' => $employee_id, 'ot_res' => $ot_res, 'ot_code' => $ot_code);				
					// $response->rows[$employee_index]['cell'][2] = $response->rows[$employee_index]['cell'][2] == "" ? $ot_res['ot_res'] : 'x' ;
				}
			}
		}

		if(sizeof($ot_array) > 0){
			foreach($ot_array as $year_key => $ot_res ) {
				$response->rows[$response->records]['id'] = $cell[0] = 'employee_ot-'.$ot_res['employee_id'].'-'.$ot_res['date'].'-'.$ot_res['ot_code'];
				$cell[1] =  '';
				$cell[2] =  $ot_res['ot_res']; //ot_hours
				$cell[3] =  $ot_res['ot_code']; //ot_code
				$cell[4] =  '0.00'; //ot_amt
				$cell[5] = 3; //level
				$cell[6] = 'employee_date-'.$ot_res['employee_id'].'-'.$ot_res['date']; //parent
				$cell[7] = true; //leaf
				$cell[8] = false; //expanded field	
				// $response_index = $response->records;
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				
			}
			return $response;
		}
		

		return false;
	}

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{	
		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$this->load->helper('time_upload');
		$effectivity_from = date('Y-m-d', strtotime($this->input->post('date_from')));
		$effectivity_to = date('Y-m-d', strtotime($this->input->post('date_to')));
		$effectivity_date_qry = 1;
		if(!empty($effectivity_from) && !empty($effectivity_to)) {
			// $effectivity_date_qry = 'resigned_date <= "'.date("Y-m-d",strtotime($effectivity_date)).'"';
			$effectivity_date_qry = ' date BETWEEN ("'.date("Y-m-d",strtotime($effectivity_from)).'" AND "'.date("Y-m-d",strtotime($effectivity_to)).'"';
		}

		$qry = "SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot AS ot_res,  
				  'REG OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot > 0 
				    AND a.ot IS NOT NULL) 
				  AND a.day_type = 'reg' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot_excess AS ot_res,  
				  'REG OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL) 
				  AND a.day_type = 'reg' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot AS ot_res,  
				  'REG ND OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.day_type = 'reg' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot_excess AS ot_res,  
				  'REG ND OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.day_type = 'reg' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot AS ot_res,  
				  'REST OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot > 0 
				    AND a.ot IS NOT NULL) 
				  AND a.day_type = 'rd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot_excess AS ot_res,  
				  'REST OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL) 
				  AND a.day_type = 'rd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot AS ot_res,  
				  'REST ND OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.day_type = 'rd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot_excess AS ot_res,  
				  'REST ND OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.day_type = 'rd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot AS ot_res,  
				  'LH OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot > 0 
				    AND a.ot IS NOT NULL) 
				  AND a.day_type = 'leg' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot_excess AS ot_res,  
				  'LH OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL) 
				  AND a.day_type = 'leg' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot AS ot_res,  
				  'LH ND OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.day_type = 'leg' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot_excess AS ot_res,  
				  'LH ND OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.day_type = 'leg' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot AS ot_res,  
				  'SH OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot > 0 
				    AND a.ot IS NOT NULL) 
				  AND a.day_type = 'spe' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot_excess AS ot_res,  
				  'SH OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL) 
				  AND a.day_type = 'spe' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot AS ot_res,  
				  'SH ND OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.day_type = 'spe' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot_excess AS ot_res,  
				  'SH ND OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.day_type = 'spe' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot AS ot_res,  
				  'LH RD OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot > 0 
				    AND a.ot IS NOT NULL) 
				   AND a.day_type = 'legrd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot_excess AS ot_res,  
				  'LH RD OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL) 
				   AND a.day_type = 'legrd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot AS ot_res,  
				  'LH RD ND OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				   AND a.day_type = 'legrd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot_excess AS ot_res,  
				  'LH RD ND OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				   AND a.day_type = 'legrd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot AS ot_res,  
				  'SH RD OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot > 0 
				    AND a.ot IS NOT NULL) 
				  AND a.day_type = 'sperd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ot_excess AS ot_res,  
				  'SH RD OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ot_excess > 0 
				    AND a.ot_excess IS NOT NULL) 
				  AND a.day_type = 'sperd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot AS ot_res,  
				  'SH RD ND OT' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot > 0 
				    AND a.ndot IS NOT NULL) 
				  AND a.day_type = 'sperd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				UNION ALL
				SELECT 
				  a.employee_id, a.date AS date_res, e.employee_id,
				  a.ndot_excess AS ot_res,  
				  'SH RD ND OTX' AS ot_code,
				  CONCAT(lastname,', ',firstname,' ',middleinitial,' ',aux) AS employee_name,
				  b.lastname AS last_name, b.firstname AS first_name, c.id_number, d.department_code AS dept_code,
				  IF( MAX(e.date) >= MAX(e.date_approved), MAX(e.date), MAX(e.date_approved) ) AS date_max 
				FROM {$this->db->dbprefix}employee_oot e 
				  LEFT JOIN (SELECT ndot_excess, day_type, `date`, employee_id  FROM {$this->db->dbprefix}dtr_daily_summary_lf 
				    UNION ALL 
				    SELECT  ndot_excess, day_type, `date`, employee_id FROM {$this->db->dbprefix}dtr_daily_summary WHERE `date` NOT IN (SELECT `date` FROM {$this->db->dbprefix}dtr_daily_summary_lf WHERE employee_id = {$this->db->dbprefix}dtr_daily_summary.`employee_id` AND `date` = {$this->db->dbprefix}dtr_daily_summary.date)) a 
				    ON ( e.`employee_id` = a.employee_id AND e.`date` = a.date ) 
				  LEFT JOIN {$this->db->dbprefix}user b ON b.employee_id = a.employee_id 
				  LEFT JOIN {$this->db->dbprefix}employee c ON c.employee_id = b.employee_id 
				  LEFT JOIN {$this->db->dbprefix}user_company_department d ON d.department_id = b.department_id 
				WHERE e.form_status_id = 3 
				AND (a.ndot_excess > 0 
				    AND a.ndot_excess IS NOT NULL) 
				  AND a.day_type = 'sperd' 
				GROUP BY e.`employee_id`, e.date 
				HAVING DATE(date_max) BETWEEN '{$effectivity_from}' AND '{$effectivity_to}'
				ORDER BY dept_code, last_name, first_name, date_res";
		$ots = $this->db->query($qry);

		$html = '';
		if($ots) {
			if($ots->num_rows() > 0) {
				$employee_id = '';
				$dept_code = '';
				$ot_hours = 0;
				$ot_dept_hours = 0;
				foreach ($ots->result() as $key => $value) {
					$date = date("m/d/Y",strtotime($value->date_res));
					if(!empty($employee_id)){
						if($employee_id != $value->employee_id) {
							$html .= '<table width="100%">
									<tr><td colspan="8" style="height:10px;">&nbsp;</td></tr>
									<tr>
										<td style="text-align:right;width:500px" colspan="4">Total Per Employee : </td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">'.$ot_hours.'</td>
										<td style="text-align:center;width:100px">&nbsp;</td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
									</tr>
									<tr><td colspan="8" style="height:10px;">&nbsp;</td></tr>
								  </table>';
							$ot_hours = 0;
						}
					}
					if(!empty($dept_code)) {
						if($dept_code != $value->dept_code) {
							$html .= '<table width="100%">
									<tr>
										<td style="text-align:right;width:500px" colspan="4">Total Per CC : </td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">'.$ot_dept_hours.'</td>
										<td style="text-align:center;width:100px">&nbsp;</td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
									</tr>
									<tr><td colspan="8" style="height:10px;">&nbsp;</td></tr>
								  </table>';
							$ot_dept_hours = 0;
						}
					}
					$html .= '<table border="1" width="100%">
									<tr>
										<td style="text-align:center;width:100px;">'.$value->id_number.'</td>
										<td style="text-align:center;width:200px;">'.$value->employee_name.'</td>
										<td style="text-align:center;width:100px"> &nbsp;'.$date.'&nbsp; </td>
										<td style="text-align:left;width:100px">'.$value->dept_code.'</td>
										<td style="text-align:right;width:100px">'.$value->ot_res.'</td>
										<td style="text-align:center;width:100px">'.$value->ot_code.'</td>
										<td style="text-align:right;width:100px">0.00</td>
										<td style="text-align:right;width:100px">0.00</td>
									</tr>
								  </table>';
					$employee_id = $value->employee_id;
					$ot_hours += $value->ot_res;

					$dept_code = $value->dept_code;
					$ot_dept_hours += $value->ot_res;
				}

				$html .= '<table width="100%">
							<tr><td colspan="8" style="height:10px;">&nbsp;</td></tr>
							<tr>
								<td style="text-align:right;width:500px" colspan="4">Total Per Employee : </td>
								<td style="text-align:right;width:100px;border-bottom:1px solid black;">'.$ot_hours.'</td>
								<td style="text-align:center;width:100px">&nbsp;</td>
								<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
								<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
							</tr>
							<tr><td colspan="8" style="height:10px;">&nbsp;</td></tr>
						  </table>';

				$html .= '<table width="100%">
									<tr>
										<td style="text-align:right;width:500px" colspan="4">Total Per CC : </td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">'.$ot_dept_hours.'</td>
										<td style="text-align:center;width:100px">&nbsp;</td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
									</tr>
									<tr><td colspan="8" style="height:10px;">&nbsp;</td></tr>
								  </table>';
							$ot_dept_hours = 0;
			}
		}
		$params['header_title'] = '<table>
									<tr>
										<td colspan="8" style="text-align:center;">
											<b>Unposted Overtime Transactions Detail per Employee</b>
										</td>
									</tr>
									<tr><td colspan="8" style="height:10px;">&nbsp;</td></tr>
									</table>
									<table border="1" width="100%">
									<tr>
										<td style="text-align:center;width:100px;">EMP ID#</td>
										<td style="text-align:center;width:200px;">EMPLOYEE NAME</td>
										<td style="text-align:center;width:100px">DATE</td>
										<td style="text-align:center;width:100px">CC</td>
										<td style="text-align:center;width:100px">OTHrs</td>
										<td style="text-align:center;width:100px">OT CODE</td>
										<td style="text-align:center;width:100px">OT AMT</td>
										<td style="text-align:center;width:100px">Total AMT</td>
									</tr>
									<tr><td colspan="8" style="height:10px;">&nbsp;</td></tr>
								  </table>';
		$params['table'] = $html;
		$this->output->set_header("Content-type: application/vnd.ms-excel");
        $this->output->set_header("Content-Disposition: inline; filename=employee_overtime_".date('Ymd-hms').".xls");
        $this->load->view('dtr/ot_summary/overtime_report_excel', $params);  
    }
	// END custom module funtions

}

/* End of file */
/* Location: system/application */
