<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_misc_report extends MY_Controller
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
		$data['content'] = 'dtr/ot_summary/misc_report';
		
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
			$cell[3] =  ''; //misc code
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
		WHERE a.department_id = '{$department_id}' AND a.department_id != '' AND a.department_id != '0'
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
				$cell[3] =  ''; //misc code
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
		$qry = "SELECT a.date,  a.absent , a.lates, a.undertime, a.employee_id
		FROM {$this->db->dbprefix}dtr_daily_summary a
		WHERE a.employee_id = '{$employee_id}'
		AND ( a.absent > 0 AND a.absent IS NOT NULL 
				OR
			  a.lates > 0 AND a.lates IS NOT NULL
			    OR
			  a.undertime > 0 AND a.undertime IS NOT NULL
			)
		AND a.date between '{$this->date_from}' AND '{$this->date_to}'
		ORDER BY a.date";
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
				$cell[3] =  ''; //misc code
				$cell[4] =  ''; //ot_amt
				$cell[5] = 2; //level
				$cell[6] = 'employee_id-'.$year['employee_id']; //parent
				$cell[7] = false; //leaf
				$cell[8] = true; //expanded field	
				$response_index = $response->records;
				$response->rows[$response->records]['cell'] = $cell;
				$response->records++;
				
				$new_rows = $this->_employee_misc_summary($year['employee_id'], $year['date'], $response);
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

	function _employee_misc_summary( $employee_id, $date, $response ){
		// $employee_index = $response->records - 1;

		$misc_array = array();
		$total = array();
		$qry = "SELECT 
				  a.date,
				  a.absent AS misc_res,
				  a.employee_id,
				  'ABSENT' AS misc_code
				FROM
				  {$this->db->dbprefix}dtr_daily_summary a 
				WHERE a.employee_id = '{$employee_id}' 
				  AND (
				    a.absent > 0 
				    AND a.absent IS NOT NULL 
				  ) 
				  AND a.date = '{$date}' 
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.lates AS misc_res,
				  a.employee_id,
				  'TARDY' AS misc_code
				FROM
				  {$this->db->dbprefix}dtr_daily_summary a 
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.lates > 0 
				    AND a.lates IS NOT NULL) 
				  AND a.date = '{$date}'
				UNION
				ALL 
				SELECT 
				  a.date,
				  a.undertime AS misc_res,
				  a.employee_id,
				  'UNDERTIME' AS misc_code
				FROM
				  {$this->db->dbprefix}dtr_daily_summary a 
				WHERE a.employee_id = '{$employee_id}' 
				  AND (a.undertime > 0 
				    AND a.undertime IS NOT NULL) 
				  AND a.date = '{$date}' ";
		$miscs = $this->db->query($qry);

		if($miscs) {
			if($miscs->num_rows() > 0){
				foreach($miscs->result() as $misc){
					$date = $misc->date;
					$employee_id = $misc->employee_id;
					$misc_res = $misc->misc_res;
					$misc_code = $misc->misc_code;
					$misc_array[] = array('date' => $date, 'employee_id' => $employee_id, 'misc_res' => $misc_res, 'misc_code' => $misc_code);				
					// $response->rows[$employee_index]['cell'][2] = $response->rows[$employee_index]['cell'][2] == "" ? $ot_res['ot_res'] : 'x' ;
				}
			}
		}

		if(sizeof($misc_array) > 0){
			foreach($misc_array as $year_key => $misc_res ) {
				$response->rows[$response->records]['id'] = $cell[0] = 'employee_misc-'.$misc_res['employee_id'].'-'.$misc_res['date'].'-'.$misc_res['misc_code'];
				$cell[1] =  '';
				switch ($misc_res['misc_code']) {
					case 'ABSENT':
							// $cell[2] =  number_format($misc_res['misc_res'],2)."D"; //misc_hours
							$cell[2] =  $misc_res['misc_res']."D"; //misc_hours
						break;
					case 'TARDY':
					case 'UNDERTIME':
							// $cell[2] =  number_format($misc_res['misc_res'],2)."H"; //misc_hours
							$cell[2] =  $misc_res['misc_res']."H"; //misc_hours
						break;
				}
				$cell[3] =  $misc_res['misc_code']; //misc_code
				$cell[4] =  '0.00'; //ot_amt
				$cell[5] = 3; //level
				$cell[6] = 'employee_date-'.$misc_res['employee_id'].'-'.$misc_res['date']; //parent
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
		    	  a.employee_id,
				  a.date AS date_res,
				  a.absent AS misc_res,
				  a.employee_id,
				  'ABSENT' AS misc_code,
				  CONCAT(lastname,', ',firstname,' ', middleinitial,' ', aux ) AS employee_name,
				  b.firstname AS first_name,
				  b.lastname AS last_name,
				  c.id_number,
				  d.department_code AS dept_code
				FROM
				  {$this->db->dbprefix}dtr_daily_summary a 
				  LEFT JOIN  {$this->db->dbprefix}user b
				  	ON b.employee_id = a.employee_id
				  LEFT JOIN {$this->db->dbprefix}employee c
					ON c.employee_id = b.employee_id
				  LEFT JOIN {$this->db->dbprefix}user_company_department d
					ON d.department_id = b.department_id
				WHERE (
				    a.absent > 0 
				    AND a.absent IS NOT NULL 
				  ) 
				  AND a.date BETWEEN '{$effectivity_from}' AND '{$effectivity_to}' AND lastname IS NOT NULL
				UNION
				ALL 
				SELECT 
				  a.employee_id,
				  a.date AS date_res,
				  a.lates AS misc_res,
				  a.employee_id,
				  'TARDY' AS misc_code,
				  CONCAT(lastname,', ',firstname,' ', middleinitial,' ', aux ) AS employee_name,
				  b.firstname AS first_name,
				  b.lastname AS last_name,
				  c.id_number,
				  d.department_code AS dept_code
				FROM
				  {$this->db->dbprefix}dtr_daily_summary a
				  LEFT JOIN  {$this->db->dbprefix}user b
				  	ON b.employee_id = a.employee_id
				  LEFT JOIN {$this->db->dbprefix}employee c
					ON c.employee_id = b.employee_id
				  LEFT JOIN {$this->db->dbprefix}user_company_department d
					ON d.department_id = b.department_id
				WHERE (a.lates > 0 
				    AND a.lates IS NOT NULL) 
				  AND a.date BETWEEN '{$effectivity_from}' AND '{$effectivity_to}' AND lastname IS NOT NULL
				UNION
				ALL 
				SELECT 
				  a.employee_id,
				  a.date AS date_res,
				  a.undertime AS misc_res,
				  a.employee_id,
				  'UNDERTIME' AS misc_code,
				  CONCAT(lastname,', ',firstname,' ', middleinitial,' ', aux ) AS employee_name,
				  b.firstname AS first_name,
				  b.lastname AS last_name,
				  c.id_number,
				  d.department_code AS dept_code
				FROM
				  {$this->db->dbprefix}dtr_daily_summary a
				  LEFT JOIN  {$this->db->dbprefix}user b
				  	ON b.employee_id = a.employee_id
				  LEFT JOIN {$this->db->dbprefix}employee c
					ON c.employee_id = b.employee_id
				  LEFT JOIN {$this->db->dbprefix}user_company_department d
					ON d.department_id = b.department_id
				WHERE (a.undertime > 0 
				    AND a.undertime IS NOT NULL) 
				  AND a.date BETWEEN '{$effectivity_from}' AND '{$effectivity_to}' AND lastname IS NOT NULL
				 ORDER BY dept_code, last_name, first_name, date_res";
		$ots = $this->db->query($qry);

		$html = '';
		if($ots) {
			if($ots->num_rows() > 0) {
				$employee_id = '';
				$dept_code = '';
				$misc_hours = 0;
				$absent_total = 0;
				$tardy_U_total = 0;		

				$misc_dept_hours = 0;
				$absent_dept_total = 0;
				$tardy_U_dept_total = 0;			
				foreach ($ots->result() as $key => $value) {
					$date = date("m/d/Y",strtotime($value->date_res));
					if(!empty($employee_id)){
						if($employee_id != $value->employee_id) {							
							if($absent_total == 0) {
								$absent_total = '';
							}
							if($tardy_U_total == 0) {
								$tardy_U_total = '';
							}
							// $misc_hours = ($absent_total != ''? $absent_total.'D':'').' '.($tardy_U_total != ''? number_format($tardy_U_total,2).'H':'');
							$misc_hours = ($absent_total != ''? $absent_total.'D':'').' '.($tardy_U_total != ''? $tardy_U_total.'H':'');
							$html .= '<table width="100%">
									<tr><td colspan="7" style="height:10px;">&nbsp;</td></tr>
									<tr>
										<td style="text-align:right;width:500px" colspan="3">Total Per Employee : </td>
										<td style="text-align:right;width:300px;border-bottom:1px solid black;" colspan="2">'.$misc_hours.'</td>
										<td style="text-align:center;width:100px">&nbsp;</td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
									</tr>
									<tr><td colspan="7" style="height:10px;">&nbsp;</td></tr>
								  </table>';
							$absent_total = 0;
							$tardy_U_total = 0;							
						}
					}
					if(!empty($dept_code)) {
						if($dept_code != $value->dept_code) {
							if($absent_dept_total == 0) {
								$absent_dept_total = '';
							}
							if($tardy_U_dept_total == 0) {
								$tardy_U_dept_total = '';
							}
							// $misc_hours = ($absent_total != ''? $absent_total.'D':'').' '.($tardy_U_total != ''? number_format($tardy_U_total,2).'H':'');
							$misc_dept_hours = ($absent_dept_total != ''? $absent_dept_total.'D':'').' '.($tardy_U_dept_total != ''? $tardy_U_dept_total.'H':'');
							$html .= '<table width="100%">
									<tr>
										<td style="text-align:right;width:500px" colspan="3">Total Per CC : </td>
										<td style="text-align:right;width:300px;border-bottom:1px solid black;" colspan="2">'.$misc_dept_hours.'</td>
										<td style="text-align:center;width:100px">&nbsp;</td>
										<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
									</tr>
									<tr><td colspan="7" style="height:10px;">&nbsp;</td></tr>
								  </table>';
							$absent_dept_total = 0;
							$tardy_U_dept_total = 0;
						}
					}
					switch ($value->misc_code) {
						case 'ABSENT':
								// $misc_res_output = number_format($value->misc_res,2).'D';
								$misc_res_output = $value->misc_res.'D';
							break;
						case 'TARDY':						
						case 'UNDERTIME':
								// $misc_res_output = number_format($value->misc_res,2).'H';
								$misc_res_output = $value->misc_res.'H';
							break;
					}
					$html .= '<table border="1" width="100%">
									<tr>
										<td style="text-align:center;width:100px;">'.$value->id_number.'</td>
										<td style="text-align:center;width:200px;">'.$value->employee_name.'</td>
										<td style="text-align:center;width:100px"> &nbsp;'.$date.'&nbsp; </td>
										<td style="text-align:center;width:100px">'.$value->dept_code.'</td>
										<td style="text-align:right;width:200px">'.$misc_res_output.'</td>
										<td style="text-align:center;width:100px">'.$value->misc_code.'</td>
										<td style="text-align:right;width:100px">0.00</td>
									</tr>
								  </table>';
					$employee_id = $value->employee_id;
					switch ($value->misc_code) {
						case 'ABSENT':
								$absent_total += $value->misc_res;
							break;							
						case 'UNDERTIME':
						case 'TARDY':
								$tardy_U_total += $value->misc_res;
							break;
					}
					$dept_code = $value->dept_code;
					switch ($value->misc_code) {
						case 'ABSENT':
								$absent_dept_total += $value->misc_res;
							break;							
						case 'UNDERTIME':
						case 'TARDY':
								$tardy_U_dept_total += $value->misc_res;
							break;
					}
				}
				if($absent_total == 0) {
					$absent_total = '';
				}
				if($tardy_U_total == 0) {
					$tardy_U_total = '';
				}
				// $misc_hours = ($absent_total != ''? $absent_total.'D':'').' '.($tardy_U_total != ''? number_format($tardy_U_total,2).'H':'');
				$misc_hours = ($absent_total != ''? $absent_total.'D':'').' '.($tardy_U_total != ''? $tardy_U_total.'H':'');
				$html .= '<table width="100%">
							<tr><td colspan="7" style="height:10px;">&nbsp;</td></tr>
							<tr>
								<td style="text-align:right;width:500px" colspan="3">Total Per Employee : </td>
								<td style="text-align:right;width:300px;border-bottom:1px solid black;" colspan="2">'.$misc_hours.'</td>
								<td style="text-align:center;width:100px">&nbsp;</td>
								<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
							</tr>
							<tr><td colspan="7" style="height:10px;">&nbsp;</td></tr>
						  </table>';
				if($absent_dept_total == 0) {
					$absent_dept_total = '';
				}
				if($tardy_U_dept_total == 0) {
					$tardy_U_dept_total = '';
				}
				// $misc_hours = ($absent_total != ''? $absent_total.'D':'').' '.($tardy_U_total != ''? number_format($tardy_U_total,2).'H':'');
				$misc_dept_hours = ($absent_dept_total != ''? $absent_dept_total.'D':'').' '.($tardy_U_dept_total != ''? $tardy_U_dept_total.'H':'');
				$html .= '<table width="100%">
						<tr>
							<td style="text-align:right;width:500px" colspan="3">Total Per CC : </td>
							<td style="text-align:right;width:300px;border-bottom:1px solid black;" colspan="2">'.$misc_dept_hours.'</td>
							<td style="text-align:center;width:100px">&nbsp;</td>
							<td style="text-align:right;width:100px;border-bottom:1px solid black;">0.00</td>
						</tr>
						<tr><td colspan="7" style="height:10px;">&nbsp;</td></tr>
					  </table>';
				$absent_dept_total = 0;
				$tardy_U_dept_total = 0;
			}
		}
		$params['header_title'] = '<table>
									<tr>
										<td colspan="7" style="text-align:center;">
											<b>Unposted Misc. Transactions Detail per Employee per CC</b>
										</td>
									</tr>
									<tr><td colspan="7" style="height:10px;">&nbsp;</td></tr>
									</table>
									<table border="1" width="100%">
									<tr>
										<td style="text-align:center;width:100px;">EMP ID#</td>
										<td style="text-align:center;width:200px;">EMPLOYEE NAME</td>
										<td style="text-align:center;width:100px">DATE</td>
										<td style="text-align:center;width:100px">CC</td>
										<td style="text-align:center;width:100px">Day/Hr/M</td>
										<td style="text-align:center;width:100px">TRANS. TYPE</td>
										<td style="text-align:center;width:100px">AMOUNT</td>
									</tr>
									<tr><td colspan="7" style="height:10px;">&nbsp;</td></tr>
								  </table>';
		$params['table'] = $html;
		$this->output->set_header("Content-type: application/vnd.ms-excel");
        $this->output->set_header("Content-Disposition: inline; filename=employee_misc_".date('Ymd-hms').".xls");
        $this->load->view('dtr/ot_summary/misc_report_excel', $params);  
    }
	// END custom module funtions

}

/* End of file */
/* Location: system/application */
