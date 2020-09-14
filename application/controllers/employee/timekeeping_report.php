<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timekeeping_report extends MY_Controller
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
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/jqtreeview/jquery.cookie.js"></script>';    	
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/timekeeping_report/listview';

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

		$data['department'] = $this->db->get('user_company_department')->result_array();

		if (!$this->is_superadmin){
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

		$sql = 'SELECT CONCAT(firstname, " ",lastname) as "Agent Name",e.biometric_id as "Employee ID No.",edt.*,Campaign
				FROM '.$this->db->dbprefix('user').' u
				LEFT OUTER JOIN '.$this->db->dbprefix('employee').' e ON u.employee_id = e.employee_id
				LEFT OUTER JOIN '.$this->db->dbprefix('campaign').' c ON e.campaign_id = c.campaign_id
				INNER JOIN (SELECT employee_id,COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) - SUM(restday) AS "Absent",
							COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) * 8 - SUM(restday) * 8 AS "Absent/Hr",
							SUM(lates) AS "Min Tardy",SUM(CASE WHEN hours_worked > 0 THEN hours_worked ELSE 0 END) AS "Work Hrs",SUM(reg_nd) AS "Night Dif. Hrs",
							SUM(overtime) AS "OT/hrs (Reg)"
							FROM '.$this->db->dbprefix('employee_dtr').'
							WHERE deleted = 0 AND '.$search.'';
		
							if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
								$sql .= ' AND date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'"';
							}

							$sql .= ' GROUP BY employee_id) as edt ON u.employee_id = edt.employee_id';

		$result = $this->db->query($sql);

        //$response->last_query = $this->db->last_query();
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

			$sql = 'SELECT CONCAT(firstname, " ",lastname) as "Agent Name",e.biometric_id as "Employee ID No.",edt.*,campaign as "Campaign Assignment"
					FROM '.$this->db->dbprefix('user').' u
					LEFT OUTER JOIN '.$this->db->dbprefix('employee').' e ON u.employee_id = e.employee_id
					LEFT OUTER JOIN '.$this->db->dbprefix('campaign').' c ON e.campaign_id = c.campaign_id
					INNER JOIN (SELECT employee_id,COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) - SUM(restday) AS "Absent",
								COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) * 8 - SUM(restday) * 8 AS "Absent/Hr",
								SUM(lates) AS "Min Tardy",SUM(CASE WHEN hours_worked > 0 THEN hours_worked ELSE 0 END) AS "Work Hrs",SUM(reg_nd) AS "Night Dif. Hrs",
								SUM(overtime) AS "OT/hrs (Reg)"
								FROM '.$this->db->dbprefix('employee_dtr').'
								WHERE deleted = 0 AND '.$search.'';
			
								if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
									$sql .= ' AND date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'"';
								}

								$sql .= ' GROUP BY employee_id) as edt ON u.employee_id = edt.employee_id';

	        if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');
	            $sql .= ' ORDER BY '.$sidx . ' ' . $sord.'';
	        }

	        $start = $limit * $page - $limit;
			$sql .= ' LIMIT '.$start.','.$limit.'';	        
	        
	        $result = $this->db->query($sql);

/*        die($this->db->last_query()); 
        dbug($this->db->last_query());
        return;*/

	        $ctr = 0;	        
	        foreach ($result->result() as $row) {
	            $response->rows[$ctr]['cell'][0] = $row->{'Agent Name'};
	            $response->rows[$ctr]['cell'][1] = $row->{'Employee ID No.'};
	            $response->rows[$ctr]['cell'][2] = $row->{'Absent'};
	            $response->rows[$ctr]['cell'][3] = $row->{'Absent/Hr'};
	            $response->rows[$ctr]['cell'][4] = number_format($row->{'Min Tardy'},2);
	            $response->rows[$ctr]['cell'][5] = number_format($row->{'Work Hrs'},2);
	            $response->rows[$ctr]['cell'][6] = number_format($row->{'Night Dif. Hrs'},2);
	            $response->rows[$ctr]['cell'][7] = number_format($row->{'OT/hrs (Reg)'},2);
	            $response->rows[$ctr]['cell'][8] = $row->{'Campaign Assignment'};
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Agent Name', 'Employee Id', 'Absent', 'Absent/Hr', 'Min Tardy', 'Work Hrs', 'Night Dif. Hrs', 'OT/hrs (Reg) ', 'Campaign Assignment'); //, 'Work Shift'

		$this->listview_columns = array(
				array('name' => 'agent_name', 'width' => '180','align' => 'center'),				
				array('name' => 'employee_id'),
				array('name' => 'absent'),
				array('name' => 'absent_hr'),
				array('name' => 'lates'),
				array('name' => 'hours_worked'),
				array('name' => 'night_diff'),
				array('name' => 'overtime'),
				array('name' => 'campaign_assesment')
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
		ini_set('memory_limit', '1024M');	
		ini_set('max_execution_time', 1800);	
		$this->load->helper('time_upload');		
		$search = 1;

		$sql = 'SELECT CONCAT(lastname, ", ",firstname) as "Agent Name",e.biometric_id as "Employee ID No.",u.employee_id as employeeid,edt.*,Campaign, resigned_date
				FROM '.$this->db->dbprefix('user').' u
				INNER JOIN '.$this->db->dbprefix('employee').' e ON u.employee_id = e.employee_id
				LEFT OUTER JOIN '.$this->db->dbprefix('campaign').' c ON e.campaign_id = c.campaign_id
				LEFT OUTER JOIN (SELECT employee_id,COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) - SUM(restday) AS "Absent",
							COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) * 8 - SUM(restday) * 8 AS "Absent/Hr",
							SUM(lates) AS "Min Tardy",SUM(CASE WHEN hours_worked > 0 THEN hours_worked ELSE 0 END) AS "Work Hrs",SUM(reg_nd) AS "Night Dif. Hrs",
							SUM(overtime) AS "OT/hrs (Reg)"
							FROM '.$this->db->dbprefix('employee_dtr').'';
							if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
								$sql .= ' WHERE date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'"';
								
							}
							$sql .= ' GROUP BY employee_id) as edt ON u.employee_id = edt.employee_id WHERE e.deleted = 0 AND '.$search.' AND u.employee_id IS NOT NULL AND e.biometric_id IS NOT NULL';
							$sql .= ' AND IF( resigned_date IS NOT NULL, resigned_date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'", 1)';

        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            $sql .= ' ORDER BY '.$sidx . ' ' . $sord.'';
        }
        else{
        	$sql .= ' ORDER BY lastname,firstname ASC';
        }

		$q = $this->db->query($sql);
		
		$query  = $q;
		$fields = $q->list_fields();

		//$export = $this->_export;
		
		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("DTR Summary Report")
		            ->setDescription("DTR Summary Report");
		               
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

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$HorizontalRight = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		);		

		$HorizontalCenter = array(
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

		$activeSheet->setCellValue('A6', '     ');
		$activeSheet->setCellValue('B6', 'Agent Name');
		$activeSheet->setCellValue('C6', 'Employee ID No.');

		$datefrom = $this->input->post('date_period_start');
		$dateto = $this->input->post('date_period_end');

		$array = array("Absent","Time In","Min Tardy","Time Out","PAY HRS","TIME");
		$alpha_ctr_ko = 3;
		while (strtotime($datefrom) <= strtotime($dateto)){
			$ctr = 1;
			for ($i = 0; $i <= 5;$i++){
				if ($alpha_ctr_ko >= count($alphabet)) {
					$alpha_ctr_ko = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr_ko];
				} else {
					$xcoor = $alphabet[$alpha_ctr_ko];
				}

				$activeSheet->setCellValue($xcoor . '6', $array[$i]);

				if ($ctr == 1){			
					$xcoor_prev = $xcoor;
					$activeSheet->setCellValue($xcoor . '5', date('l',strtotime($datefrom)) .' :  '. $datefrom);
				}

				if ($ctr == 4){
					$objPHPExcel->getActiveSheet()->mergeCells($xcoor_prev.'5'.':'.$xcoor.'5');
				}

				if ($ctr == 5){
					$activeSheet->setCellValue($xcoor . '5', 'NIGHT DIFF');
				}

				if ($ctr == 6){
					$activeSheet->setCellValue($xcoor . '5', 'PAID OVER');
				}				

				$ctr++;
				$alpha_ctr_ko++;				
			}
			$datefrom = date('m/d/Y',strtotime('+1 day',strtotime($datefrom)));			
		}

		// generate main report as time in and time out
		$datefrom = $this->input->post('date_period_start');
		$dateto = $this->input->post('date_period_end');

		$array = array("Absent","Time In","Min Tardy","Time Out","PAY HRS","TIME");
		$alpha_ctr_ko = 3;	
		$sub_ctr   = 0;	
		$employee_info = array();			
		while (strtotime($datefrom) <= strtotime($dateto)){
			$ctr = 1;
			for ($i = 0; $i <= 5;$i++){

				if ($alpha_ctr_ko >= count($alphabet)) {
					$alpha_ctr_ko = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr_ko];
				} else {
					$xcoor = $alphabet[$alpha_ctr_ko];
				}

				if ($ctr == 1){			
					$xcoor_prev = $xcoor;
					$activeSheet->setCellValue($xcoor . '5', date('l',strtotime($datefrom)) .' :  '. $datefrom);
				}	

				$activeSheet->setCellValue($xcoor . '6', $array[$i]);

				$sql = 'SELECT CONCAT(lastname, ", ",firstname) as "Agent Name",e.biometric_id as "Employee ID No.",u.employee_id as employeeid,department_id,status_id,edt.*,Campaign,ef.date_from as date_from_floating, ef.date_to as date_recalled,resigned_date,resigned
						FROM '.$this->db->dbprefix('user').' u
						INNER JOIN '.$this->db->dbprefix('employee').' e ON u.employee_id = e.employee_id
						LEFT OUTER JOIN '.$this->db->dbprefix('campaign').' c ON e.campaign_id = c.campaign_id
						LEFT OUTER JOIN '.$this->db->dbprefix('employee_floating').' ef ON e.employee_id = ef.employee_id
						LEFT OUTER JOIN (SELECT employee_id,date,time_in1,time_out1,hours_worked,lates,undertime,reg_nd,ot_nd,overtime
						FROM '.$this->db->dbprefix('employee_dtr').'
						WHERE deleted = 0 AND '.$search.'
						AND date = "'.date('Y-m-d',strtotime($datefrom)).'") as edt ON u.employee_id = edt.employee_id
						WHERE u.employee_id IS NOT NULL
						AND e.biometric_id IS NOT NULL
						AND e.deleted = 0
						AND IF( resigned_date IS NOT NULL, resigned_date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'", 1)
						GROUP BY u.employee_id
						ORDER BY lastname,firstname ASC';

				$q = $this->db->query($sql);

/*				dbug($this->db->last_query());
				return;*/

				$line = 7;
				$dummy_p->date_to = $this->input->post('date_period_start');
				$dummy_p->date_from = $this->input->post('date_period_end');					

				foreach ($q->result() as $row) {

					// flag
					$disregard_in_out = false;
					$half_day_leave = false;

					if ($row->employeeid != ''){
						$regular = false;
						if( ( $row->status_id == 1 ) || ( $row->status_id == 2 ) ){
							$regular = true;
						}

						$array_stack = array();
		           		$holiday = $this->system->holiday_check($row->date, $row->employeeid,true);

		           		if ($holiday){
		           			$holiday_type = ($holiday[0]['legal_holiday'] == '1') ? 'leg' : 'spe';
		           			if (!$regular && $holiday_type == 'spe'){
		           				$holiday = false;
		           			}
		           			else{
		           				$holiday = true;
		           			}
		           		}
						
						$day = strtolower(date('l', strtotime($datefrom)));
		           		$schedule = $this->system->get_employee_worksched($row->employeeid, date('Y-m-d',strtotime($datefrom)));  
						
						if(isset($schedule->has_cws)){
							$day_shift_id = $schedule->shift_id;
						} else if(isset($schedule->has_cal_shift)) {
							$day_shift_id = $schedule->shift_id;
						} else{
							$day_shift_id = $schedule->{$day . '_shift_id'};
						}
						if($this->config->item('client_no') == 2 && $day_shift_id == 1)
							$day_shift_id = 0;
						
		           		$restday = false;
						if (isset($day_shift_id) && $day_shift_id == 0) {
							$restday = true;		           		
						}

						$timein = '';
						$timeout = '';						
			            $absent = 0;
			            if (!$holiday && !$restday){
				            if ($row->hours_worked == 0 && number_format($row->overtime / 60,2) == 0){
								$absent = 1;
				            }
				            elseif ($row->hours_worked <= 4 && $row->hours_worked > 0){
				            	if ((number_format($row->lates / 60,2) + number_format($row->undertime / 60,2)) <= 4){
									$absent = 0.5;
				            	}
				            }
			        	}

						$remarks = "";

						// Check leave for whole day
						$is_leave = false;
						$sql = 'SELECT duration_id,el.employee_leave_id
								FROM '.$this->db->dbprefix('employee').' e
								LEFT OUTER JOIN '.$this->db->dbprefix('employee_leaves').' el ON e.employee_id = el.employee_id
								LEFT OUTER JOIN '.$this->db->dbprefix('employee_leaves_dates').' eld ON el.employee_leave_id = eld.employee_leave_id
								WHERE e.employee_id = "'.$row->employeeid.'"
								AND "'.$row->date.'" BETWEEN date_from and date_to
								AND IFNULL(blanket_id,eld.date = "'.$row->date.'")
								AND form_status_id = 3
								AND IFNULL(blanket_id,eld.deleted = 0)
								AND el.deleted = 0
								AND e.deleted = 0';

						$leave = $this->db->query($sql);

						if ($leave->num_rows() > 0) {
							$remarks = "leave";
							$is_leave = true;

							if($leave->row()->duration_id == 1)
								$disregard_in_out = true;
							// if($leave->row()->duration_id == 2 || $leave->row()->duration_id == 2)
							// 	$half_day_leave = true;
						}

						$out = get_form($row->employeeid, 'out', null, $row->date, false);
						if ($out->num_rows() > 0)
							$remarks = "out";

						$et = get_form($row->employeeid, 'et', null, $row->date, false);
						if ($et->num_rows() > 0)
							$remarks = "et";

						$floating = false;
						if ((date('Y-m-d',strtotime($datefrom)) >= $row->date_from_floating && $row->date_recalled == '' && $row->date_from_floating != '') || 
							(date('Y-m-d',strtotime($datefrom)) >= $row->date_from_floating && date('Y-m-d',strtotime($datefrom)) < $row->date_recalled && $row->date_from_floating != '')) {
							if (!$is_leave){
								$floating = true;
							}							
						}

						//$absent = $absent;
						$rl = '';
						if ($restday){
							$rl = 'Rest Day';
						}

						if($absent != ''){
							$rl = 'Absent';
						}

						if ($remarks == "leave"){
							$rl = 'Leave';
						}

						if ($row->time_in1 != NULL){
							$timein = date('H:i',strtotime($row->time_in1));
						}

						if ($row->time_out1 != NULL){
							$timeout = date('H:i',strtotime($row->time_out1));
						}

						// Check OBT
						$obt = get_form($row->employeeid, 'obt', $dummy_p, $row->date, true);

						if ($obt->num_rows() > 0) {
							$obts = $obt->result();
							foreach($obts as $obt)
							{
								if ($row->time_in1 == '0000-00-00 00:00:00' || $row->time_in1 == '' || is_null($row->time_in1)) {
									$timein = date('H:i',strtotime($datefrom . ' ' . $obt->time_start));
								} else {
									$timein = date('H:i',strtotime($row->time_in1));
								}

								if ($row->time_out1 == '0000-00-00 00:00:00' || $row->time_out1 == '' 
									|| is_null($row->time_out1) 
									|| strtotime($obt->time_end) > strtotime(date('H:i:s', strtotime($row->time_out1)))
									) {
									$timeout = date('H:i',strtotime($datefrom . ' ' . $obt->time_end)); 
								} else {
									$timeout = date('H:i',strtotime($row->time_out1)); 
								}
							}
						}

						// check dtrp
						$dtrp = get_form($row->employeeid, 'dtrp', $dummy_p, $row->date, false);
						if ($dtrp->num_rows() > 0) {
							foreach ($dtrp->result() as $_dtrp) {
								if( $_dtrp->form_status_id == 3 ){
									if ($_dtrp->time_set_id == 1) {
										$timein = date('H:i',strtotime($_dtrp->time));
									} else {
										$timeout = date('H:i',strtotime($_dtrp->time));
									}
								}
							}
						}	
						
						$min_tardy = $row->lates;
						if ($row->lates <= 0){
							$min_tardy = '';
						}

						$min_undertime = $row->undertime;
						if ($row->undertime <= 0){
							$min_undertime = '';
						}

						$night_diff = $row->reg_nd + $row->ot_nd;
						if ($night_diff <= 0){
							$night_diff = '';
						}

						$overtime = $row->overtime;
						if ($row->overtime <= 0){
							$overtime = '';
						}					

						if ($rl != ''){
							if (($timein == '' && $timeout == '') || $disregard_in_out){
								$timein = $rl;
								$timeout = $rl;
							}
						}

						if ($rl == 'Absent'){
							if ($timein == '' && $timeout != ''){
								$timein = 'No In';
							}
							elseif ($timein != '' && $timeout == ''){
								$timeout = 'No Out';
							}						
						}					

						if ($holiday && $timein == '' && $timeout == ''){
							$timein = 'Phil Holiday';
							$timeout = 'Phil Holiday';
						}

						if ($floating && $timein != 'Rest Day' && $timeout != 'Rest Day'){
							$timein = 'Floating';
							$timeout = 'Floating';
							$absent = 1;
						}

						if ($timein == 'Absent' && $timeout == 'Absent'){
							if ($row->resigned && strtotime($datefrom) > strtotime($row->resigned_date)){
								$timein = 'Resigned';
								$timeout = 'Resigned';
							}
						}

						$shift_schedule = $this->system->get_employee_worksched($row->employeeid, date('Y-m-d',strtotime($datefrom)),true);  
						if (isset($shift_schedule->total_work_hours) && $shift_schedule->total_work_hours > 0) {
							$total_works = $shift_schedule->total_work_hours / 2;
						}else{
							$total_works = 0;
						}

						if ((($min_tardy + $min_undertime) / 60) > ($total_works) ){
							$absent = 0;
						}

						switch ($array[$i]) {
							case 'Absent':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $absent);
								break;
							case 'Time In':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $timein);
								break;						
							case 'Min Tardy':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $min_tardy + $min_undertime);
								break;		
							case 'Time Out':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $timeout);
								break;																			
							case 'PAY HRS':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $night_diff);
								break;																			
							case 'TIME':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, round($overtime / 60,2));
								break;																																	
							default:
								# code...
								break;
						}
						
						$line++;
						$employee_info[$row->employeeid]['floating'] = $floating;
						$employee_info[$row->employeeid]['absent u'][$datefrom] = $absent;
						$employee_info[$row->employeeid]['absent hour u'][$datefrom] = $absent * 8;
						$employee_info[$row->employeeid]['min tardy u'][$datefrom] = $min_tardy + $min_undertime;
						$employee_info[$row->employeeid]['work hours u'][$datefrom] = $row->hours_worked;
						$employee_info[$row->employeeid]['night diff u'][$datefrom] = $night_diff;
						$employee_info[$row->employeeid]['ot reg u'][$datefrom] = $overtime;
						$employee_info[$row->employeeid]['absent'] = array_sum($employee_info[$row->employeeid]['absent u']);
						$employee_info[$row->employeeid]['absent hour'] = array_sum($employee_info[$row->employeeid]['absent hour u']);
						$employee_info[$row->employeeid]['min tardy'] = array_sum($employee_info[$row->employeeid]['min tardy u']);
						$employee_info[$row->employeeid]['work hours'] = array_sum($employee_info[$row->employeeid]['work hours u']);
						$employee_info[$row->employeeid]['night diff'] = array_sum($employee_info[$row->employeeid]['night diff u']);
						$employee_info[$row->employeeid]['ot reg'] = array_sum($employee_info[$row->employeeid]['ot reg u']) / 60;						
					}
				}				

				if ($ctr == 4){
					$objPHPExcel->getActiveSheet()->mergeCells($xcoor_prev.'5'.':'.$xcoor.'5');
				}

				if ($ctr == 5){
					$activeSheet->setCellValue($xcoor . '5', 'NIGHT DIFF');
				}

				if ($ctr == 6){
					$activeSheet->setCellValue($xcoor . '5', 'PAID OVER');
				}				

				$ctr++;
				$alpha_ctr_ko++;				
			}
			$datefrom = date('m/d/Y',strtotime('+1 day',strtotime($datefrom)));			
		}
		// end of generate main report as time in and time out

		//generate agent name and employee id no
		$sql = 'SELECT CONCAT(lastname, ", ",firstname) as "Agent Name",e.biometric_id as "Employee ID No.",u.employee_id as employeeid,edt.*,Campaign
				FROM '.$this->db->dbprefix('user').' u
				INNER JOIN '.$this->db->dbprefix('employee').' e ON u.employee_id = e.employee_id
				LEFT OUTER JOIN '.$this->db->dbprefix('campaign').' c ON e.campaign_id = c.campaign_id
				LEFT OUTER JOIN (SELECT employee_id,COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) - SUM(restday) AS "Absent",
							COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) * 8 - SUM(restday) * 8 AS "Absent/Hr",
							SUM(lates) AS "Min Tardy",SUM(CASE WHEN hours_worked > 0 THEN hours_worked ELSE 0 END) AS "Work Hrs",SUM(reg_nd) + SUM(ot_nd) AS "Night Dif. Hrs",
							SUM(overtime) AS "OT/hrs (Reg)"
							FROM '.$this->db->dbprefix('employee_dtr').'';
							if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
								$sql .= ' WHERE date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'"';
							}
							$sql .= ' GROUP BY employee_id) as edt ON u.employee_id = edt.employee_id 
									  WHERE e.deleted = 0 
									  AND '.$search.' 
									  AND u.employee_id IS NOT NULL 
									  AND e.biometric_id IS NOT NULL ';
							$sql .= ' AND IF( resigned_date IS NOT NULL, resigned_date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'", 1)';
            	$sql .= ' ORDER BY lastname,firstname ASC';         	

		$q = $this->db->query($sql);

		$line = 7;
		$ctr = 1;
		foreach ($q->result() as $row) { 
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $ctr);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $row->{'Agent Name'});
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $line, $row->{'Employee ID No.'});
			$line++;
			$ctr++;
		}
		//end of generate agent name and employee id no

		//generate total 
		$sql = 'SELECT u.employee_id as employeeid,edt.*,Campaign
				FROM '.$this->db->dbprefix('user').' u
				INNER JOIN '.$this->db->dbprefix('employee').' e ON u.employee_id = e.employee_id
				LEFT OUTER JOIN '.$this->db->dbprefix('campaign').' c ON e.campaign_id = c.campaign_id
				LEFT OUTER JOIN (SELECT employee_id,COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) - SUM(restday) AS "Absent",
							COUNT(CASE WHEN hours_worked = 0 THEN 973 ELSE NULL END) * 8 - SUM(restday) * 8 AS "Absent/Hr",
							SUM(lates) AS "Min Tardy",SUM(undertime) AS "Min Undertime",SUM(CASE WHEN hours_worked > 0 THEN hours_worked ELSE 0 END) AS "Work Hrs",SUM(reg_nd) + SUM(ot_nd) AS "Night Dif. Hrs",
							SUM(overtime) AS "OT/hrs (Reg)"
							FROM '.$this->db->dbprefix('employee_dtr').'';
							if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
								$sql .= ' WHERE date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'"  AND deleted = 0';
							}
							else{
								$sql .= ' WHERE deleted = 0';
							}
							$sql .= ' GROUP BY employee_id) as edt ON u.employee_id = edt.employee_id WHERE e.deleted = 0 AND '.$search.' AND u.employee_id IS NOT NULL AND e.biometric_id IS NOT NULL';
							$sql .= ' AND IF( resigned_date IS NOT NULL, resigned_date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'", 1)';
        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            $sql .= ' ORDER BY '.$sidx . ' ' . $sord.'';
        }
        else{
			$sql .= ' ORDER BY lastname,firstname ASC';        	
        }

		$query = $this->db->query($sql);

		$fields = $query->list_fields();

		$alpha_ctr_total = $alpha_ctr_ko;
		$sub_ctr_total = $sub_ctr;
		$alpha_ctr_1 = $alpha_ctr_ko;
		unset($fields[0]);
		unset($fields[1]);
		unset($fields[5]);
		$xcoor_check = false;
		foreach ($fields as $field) {
			if ($alpha_ctr_1 >= count($alphabet)) {
				$alpha_ctr_1 = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr_1];
			} else {
				$xcoor = $alphabet[$alpha_ctr_1];
			}

			if (!$xcoor_check){
				$xcoor_check = true;
				$xcoor_prev1 = $xcoor;
				$activeSheet->setCellValue($xcoor . '5', 'Total');								
			}

			$activeSheet->setCellValue($xcoor . '6', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr_1++;
		}

		$objPHPExcel->getActiveSheet()->mergeCells($xcoor_prev1.'5'.':'.$xcoor.'5');
		$objPHPExcel->getActiveSheet()->getColumnDimension($xcoor)->setAutoSize(true);

		$line = 7;
		$absent_tot = 0;
		$absent_hour_tot = 0;
		$tardy_min_tot = 0;
		$work_hour_tot = 0;
		$night_diff_tot = 0;
		$ot_hour_tot = 0;	
		$sub_apha = array();	

		foreach ($query->result() as $row) {  
			$sub_ctr   = $sub_ctr_total;			
			$alpha_ctr = $alpha_ctr_total;
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

				$sub_apha[] = $xcoor;

				$min_undertime = number_format($row->{'Min Undertime'},2);


				if ($field == "Min Tardy" || $field == "Work Hrs"){
					$number_field = number_format($row->{$field},2);
					if ($field == "Min Tardy"){
						$number_field = $employee_info[$row->employeeid]['min tardy'];
					}
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $number_field);
				}
				elseif ($field == "Night Dif. Hrs"){
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $employee_info[$row->employeeid]['night diff']);
				}								
				elseif ($field == "OT/hrs (Reg)"){
					$number_field = number_format($row->{$field} / 60,2);
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $number_field);
				}
				elseif ($field == "Absent"){
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $employee_info[$row->employeeid]['absent']);
				}				
				elseif ($field == "Absent/Hr"){
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $employee_info[$row->employeeid]['absent hour']);
				}								
				else{
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});
				}
				switch ($field) {
					case 'Absent':
						$absent_tot +=	$employee_info[$row->employeeid]['absent'];
						break;
					case 'Absent/Hr':
						$absent_hour_tot +=	$employee_info[$row->employeeid]['absent hour'];
						break;
					case 'Min Tardy':
						$tardy_min_tot +=	$row->{$field} + $min_undertime;
						break;
					case 'Work Hrs':
						$work_hour_tot +=	$row->{$field};
						break;
					case 'Night Dif. Hrs':
						$night_diff_tot +=	$row->{$field};
						break;
					case 'OT/hrs (Reg)':
						$ot_hour_tot +=	$row->{$field} / 60;	
						break;						
					default:
						# code...
						break;
				}
				$alpha_ctr++;
			}
			$line++;
		}

		$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, 'Total');
		$objPHPExcel->getActiveSheet()->setCellValue($sub_apha[0] . $line, number_format($absent_tot,2));
		$objPHPExcel->getActiveSheet()->setCellValue($sub_apha[1] . $line, number_format($absent_hour_tot,2));
		$objPHPExcel->getActiveSheet()->setCellValue($sub_apha[2] . $line, number_format($tardy_min_tot,2));
		$objPHPExcel->getActiveSheet()->setCellValue($sub_apha[3] . $line, number_format($work_hour_tot,2));
		$objPHPExcel->getActiveSheet()->setCellValue($sub_apha[4] . $line, number_format($night_diff_tot,2));
		$objPHPExcel->getActiveSheet()->setCellValue($sub_apha[5] . $line, number_format($ot_hour_tot,2));
		//end of generate total 

		$objPHPExcel->getActiveSheet()->getStyle('B'.$line)->applyFromArray($HorizontalCenter);

		$objPHPExcel->getActiveSheet()->getStyle('C7:'.$xcoor.$line)->applyFromArray($HorizontalCenter);
		$objPHPExcel->getActiveSheet()->getStyle('C7:C'.$line)->applyFromArray($HorizontalCenter);
		$objPHPExcel->getActiveSheet()->getStyle('A5:'.$xcoor.'5')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A6:'.$xcoor.'6')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A5:'.$xcoor.($line - 1))->applyFromArray($styleArrayBorder);
		$objPHPExcel->getActiveSheet()->getStyle('A'.$line.':'.$xcoor.$line)->applyFromArray($styleArray);
		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		setcookie ("fileDownloadToken", $this->input->post('download_token_value_id'));

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=tk_report.xls'); //' . date('Y-m-d') . ' ' . url_title("Timekeeping Report") . '
		header('Content-Transfer-Encoding: binary');
		$objWriter->save('php://output');		
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>