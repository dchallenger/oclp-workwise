<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dialed_hours extends MY_Controller
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
		$data['scripts'][] = chosen_script();
		$data['content'] = 'employee/dialed_hours/listview';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}


		if(!$this->user_access[$this->module_id]['post']) {
			$camp_id = $this->db->get_where('employee', array('employee_id' => $this->user->user_id))->row()->campaign_id;
			$this->db->where('campaign_id', $camp_id);
		}

		$data['campaign'] = $this->db->get('campaign')->result();

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";

		$data['department'] = $this->db->get('user_company_department')->result_array();

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
	// END - default module functions
	
	// START custom module funtions

	function listview()
	{
		$this->load->helper('time_upload');

        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        
		
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;				

		if ($this->input->post('date') != ''){
			$date = date('Y-m-d',strtotime($this->input->post('date')));
		}
		else{
			$date = date('Y-m-d');
		}

		$sql = 'SELECT
			        CONCAT(firstname, " ",lastname) AS "Agent Name",
			        firstname,
			        SUBSTRING_INDEX(SUBSTRING_INDEX(edt.time_in1, " ", 2), " ", -1) AS "Time In",
			        SUBSTRING_INDEX(SUBSTRING_INDEX(edt.time_out1, " ", 2), " ", -1) AS "Time Out",
			        hours_worked       AS "Biometrics",
			        u.employee_id,
			        edt.date AS "date",
			        edt.id,
			        firstname,
			        -- COALESCE(edt.date, dh.date) AS "date",
			        -- edt.date AS "dtr_date",
			        dh.dialed_hours       AS "Dialed Hours",
			        dh.remarks            AS "Remarks",
			        dh.oe_for_discrepancy AS "OPERATIONS EXPLANATION FOR DISCREPANCY"
		 		    FROM hr_user u
		 		      	LEFT JOIN hr_employee e
		 	          		ON u.employee_id = e.employee_id
		 		        LEFT JOIN hr_employee_dtr edt
		 		          ON u.employee_id = edt.employee_id
				        LEFT JOIN hr_dialed_hours dh
				          ON edt.employee_id = dh.employee_id AND edt.date = dh.date
				    WHERE u.deleted = 0 AND '.$search;

		$sql .= ' AND edt.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_to'))).'"';
		$sql .= ' AND (edt.time_in1 IS NOT NULL OR edt.time_out1 IS NOT NULL)';

      	if($this->input->post('campaign_id') && $this->input->post('campaign_id') != 0)
			$sql .= ' AND e.campaign_id = '.$this->input->post('campaign_id');
		
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

	        $sql = 'SELECT
				        CONCAT(firstname, " ",lastname) AS "Agent Name",
				        firstname,
				        SUBSTRING_INDEX(SUBSTRING_INDEX(edt.time_in1, " ", 2), " ", -1) AS "Time In",
				        SUBSTRING_INDEX(SUBSTRING_INDEX(edt.time_out1, " ", 2), " ", -1) AS "Time Out",
				        hours_worked       AS "Biometrics",
				        u.employee_id,
				        edt.date AS "date",
				        edt.id,
				        firstname,
				        -- COALESCE(edt.date, dh.date) AS "date",
				        -- edt.date AS "dtr_date",
				        dh.dialed_hours       AS "Dialed Hours",
				        dh.remarks            AS "Remarks",
				        dh.oe_for_discrepancy AS "OPERATIONS EXPLANATION FOR DISCREPANCY"
			 		    FROM hr_user u
			 		      	LEFT JOIN hr_employee e
			 	          		ON u.employee_id = e.employee_id
			 		        LEFT JOIN hr_employee_dtr edt
			 		          ON u.employee_id = edt.employee_id
					        LEFT JOIN hr_dialed_hours dh
					          ON edt.employee_id = dh.employee_id AND edt.date = dh.date
					    WHERE u.deleted = 0 AND '.$search;

			$sql .= ' AND edt.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_to'))).'"';
			$sql .= ' AND (edt.time_in1 IS NOT NULL OR edt.time_out1 IS NOT NULL)';

          	if($this->input->post('campaign_id') && $this->input->post('campaign_id') != 0)
  				$sql .= ' AND e.campaign_id = '.$this->input->post('campaign_id');

	        if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            switch ($sidx) {
	            	case 'dialed_hours':
	            		$sortorder = "Dialed Hours";
	            		break;
	            }
	            $sord = $this->input->post('sord');
	            $sql .= ' ORDER BY `'.$sortorder .'` ' . $sord.'';
	        }
	        $sql .= ' ORDER BY edt.date';

	        $start = $limit * $page - $limit;
			$sql .= ' LIMIT '.$start.','.$limit.'';	 
						
			$result = $this->db->query($sql);
			// dbug($this->db->last_query());

	        $ctr = 0;
	        foreach ($result->result() as $row) {

	        	$dialed_hours = ($row->{'Dialed Hours'} != '' ? $row->{'Dialed Hours'} : '00:00:00');
	        	$biometrics = gmdate("H:i:s", $row->{'Biometrics'} * 60 * 60);

				$seconds = ($biometrics > $dialed_hours ? strtotime($biometrics) - strtotime($dialed_hours) : strtotime($dialed_hours) - strtotime($biometrics));

	        	$time_obj = $this->sec2hms($seconds);

	        	$response->rows[$ctr]['id'] = $row->employee_id.'/'.$row->id;
	            $response->rows[$ctr]['cell'][0] = $row->{'Agent Name'};
	            $response->rows[$ctr]['cell'][1] = $row->{'date'};

	           	$in_out = $this->_complete_in_and_out($row->employee_id, $row->{'date'}, $row->{'Time In'}, $row->{'Time Out'});

	            $response->rows[$ctr]['cell'][2] = $in_out['time_in'];
	            $response->rows[$ctr]['cell'][3] = $in_out['time_out'];
	            $response->rows[$ctr]['cell'][4] = $biometrics;
	            $response->rows[$ctr]['cell'][5] = $dialed_hours;
	            $response->rows[$ctr]['cell'][6] = $time_obj;
	            $response->rows[$ctr]['cell'][7] = $row->{'Remarks'};
	            $response->rows[$ctr]['cell'][8] = $row->{'OPERATIONS EXPLANATION FOR DISCREPANCY'};
	            
	            //$response->rows[$ctr]['cell'][7] = $shit_sched->shift;
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

	function _complete_in_and_out($employeeid, $date, $time_in, $time_out)
	{
		// $dummy_p->date_to = $this->input->post('date_period_start');
		// $dummy_p->date_from = $this->input->post('date_period_end');

		// Check OBT
		$obt = get_form($employeeid, 'obt', null, $date, true);

		$timein = $time_in;
		$timeout = $time_out;

		if ($obt->num_rows() > 0) {
			$obts = $obt->result();
			foreach($obts as $obt)
			{
				if ($timein == '0000-00-00 00:00:00' || $timein == '' || is_null($timein)) {
					$timein = date('H:i:s',strtotime($date . ' ' . $obt->time_start));
				} 
				// else {
				// 	$timein = date('H:i',strtotime($row->time_in1));
				// }

				if ($timeout == '0000-00-00 00:00:00' || $timeout == '' 
					|| is_null($timeout) 
					|| strtotime($obt->time_end) > strtotime(date('H:i:s', strtotime($timeout)))
					) {
					$timeout = date('H:i:s',strtotime($date . ' ' . $obt->time_end)); 
				} else {
					$timeout = date('H:i:s',strtotime($timeout)); 
				}
			}
		}

		// check dtrp
		$dtrp = get_form($employeeid, 'dtrp', null, $date, false);
		if ($dtrp->num_rows() > 0) {
			foreach ($dtrp->result() as $_dtrp) {
				if( $_dtrp->form_status_id == 3 ){
					if ($_dtrp->time_set_id == 1) {
						$timein = date('H:i:s',strtotime($_dtrp->time));
					} else {
						$timeout = date('H:i:s',strtotime($_dtrp->time));
					}
				}
			}
		}

		return array('time_in' => $timein, 'time_out' => $timeout);
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "";

		return $buttons;
	}	

  //   function _set_listview_query($listview_id = '', $view_actions = true) {
		// $this->listview_column_names = array('Agent Name', 'Employee Id', 'Absent', 'Absent/Hr', 'Min Tardy', 'Work Hrs', 'Night Dif. Hrs', 'OT/hrs (Reg) ', 'Campaign Assignment', 'date'); //, 'Work Shift'

		// $this->listview_columns = array(
		// 		array('name' => 'agent_name', 'width' => '180','align' => 'center'),				
		// 		array('name' => 'employee_id'),
		// 		array('name' => 'absent'),
		// 		array('name' => 'absent_hr'),
		// 		array('name' => 'lates'),
		// 		array('name' => 'hours_worked'),
		// 		array('name' => 'night_diff'),
		// 		array('name' => 'overtime'),
		// 		array('name' => 'campaign_assesment'),
		// 		array('name' => 'date')
		// 	);                                     
  //   }

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Agent Name', 'Time In', 'Time Out', 'Biometrics', 'Dialed Hours', 'Difference', 'Remarks', 'OE for Discrepancy', 'date');

		$this->listview_columns = array(
				array('name' => 'agent_name', 'width' => '180','align' => 'center'),				
				array('name' => 'time_in'),
				array('name' => 'time_out'),
				array('name' => 'biometrics'),
				array('name' => 'dialed_hours'),
				array('name' => 'difference'),
				array('name' => 'remarks'),
				array('name' => 'oe_for_discrepancy'),
				array('name' => 'date')
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
		$search_string[] = 'u.firstname LIKE "%' . $value . '%"';
		$search_string[] = 'u.lastname LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{	
		$this->load->helper('time_upload');

		$search = 1;

		$sql = 'SELECT *
				FROM (SELECT
				        CONCAT(firstname, " ",lastname) AS "Agent Name",
				        SUBSTRING_INDEX(SUBSTRING_INDEX(time_in1, " ", 2), " ", -1) AS "Time In",
				        SUBSTRING_INDEX(SUBSTRING_INDEX(time_out1, " ", 2), " ", -1) AS "Time Out",
				        hours_worked       AS "Biometrics",
				        dh.date,
				        dh.dialed_hours       AS "Dialed Hours",
				        dh.remarks            AS "Remarks",
				        dh.oe_for_discrepancy AS "OPERATIONS EXPLANATION FOR DISCREPANCY"
				      FROM hr_user u
				        LEFT JOIN hr_employee_dtr edt
				          ON u.employee_id = edt.employee_id
				        LEFT JOIN hr_dialed_hours dh
				          ON edt.employee_id = dh.employee_id
				      WHERE u.deleted = 0 AND '.$search.' AND edt.date = "'.date('Y-m-d',strtotime($this->input->post('date'))).'") AS tt WHERE DATE = "'.date('Y-m-d',strtotime($this->input->post('date'))).'"';					
		$q = $this->db->query($sql);

		$query  = $q;
		$fields = $q->list_fields();

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Daily Dialed Hours Report")
		            ->setDescription("Daily Dialed Hours Report");
		               
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
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleArrayBold = array(
			'font' => array(
				'bold' => true,
			),
		);		

		$styleArrayCenter = array(
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

		unset($fields[4]);
		array_splice($fields, 5, 0, "Difference");
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

		$activeSheet->setCellValue('A1', 'OPEN ACCESS');
		$activeSheet->setCellValue('A2', 'Daily Dialed Hours Report');
		$activeSheet->setCellValue('A3', date('l',strtotime($this->input->post('date'))) .' : '.date('F d,Y',strtotime($this->input->post('date'))));

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		$total_biometrics = 0;
		$biometrics_array = array();
		$total_dialed_hours = array();	
		$total_diff_hours = array();
		foreach ($query->result() as $row) {     				
			$sub_ctr   = 0;			
			$alpha_ctr = 0;
			$total_biometrics += $row->{"Biometrics"};
			$total_dialed_hours[] = $row->{"Dialed Hours"};			
			$biometrics = $row->{"Biometrics"};
			$dialed_hours = $this->hoursToMinutes($row->{"Dialed Hours"}) / 60;			
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

				if ($field == "Biometrics"){
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, gmdate("H:i:s", $row->{"$field"} * 60 * 60));
				}
				elseif ($field == "Difference"){
					$diff = ($biometrics > $dialed_hours ? $biometrics - $dialed_hours : $dialed_hours - $biometrics);
					$total_diff_hours[] = gmdate("H:i:s", $diff * 60 * 60);
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, gmdate("H:i:s", $diff * 60 * 60));
				}				
				else{
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{"$field"});
				}
				$alpha_ctr++;
			}
			$line++;
		}

		$objPHPExcel->getActiveSheet()->getStyle('A6:H'.($line - 1))->applyFromArray($styleArrayBorder);		
		$objPHPExcel->getActiveSheet()->getStyle('B7:F'.$line)->applyFromArray($styleArrayCenter);
		$objPHPExcel->getActiveSheet()->getStyle('A'.$line)->applyFromArray($styleArrayBold);

		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line, "Total");
		$objPHPExcel->getActiveSheet()->setCellValue("D" . $line, gmdate("H:i:s", $total_biometrics * 60 * 60));
		$objPHPExcel->getActiveSheet()->setCellValue("E" . $line, $this->sum_the_time($total_dialed_hours));
		$objPHPExcel->getActiveSheet()->setCellValue("F" . $line, $this->sum_the_time($total_diff_hours));
/*		$line++;
		$objPHPExcel->getActiveSheet()->setCellValue("A" . $line, "Cost data");
		$objPHPExcel->getActiveSheet()->setCellValue("D" . $line, $total_biometrics);	*/	

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' . url_title("Daily Dialed Hours Report") . '.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}

	function sum_the_time($times) {
		$seconds = 0;
		foreach ($times as $time){
			list($hour,$minute,$second) = explode(':', $time);
			$seconds += $hour*3600;
			$seconds += $minute*60;
			$seconds += $second;
		}
		$hours = floor($seconds/3600);
		$seconds -= $hours*3600;
		$minutes  = floor($seconds/60);
		$seconds -= $minutes*60;
		return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
	}
	// Transform hours like "1:45" into the total number of minutes, "105".
	function hoursToMinutes($hours)
	{
	    $minutes = 0;
	    if (strpos($hours, ':') !== false)
	    {
	        // Split hours and minutes.
	        list($hours, $minutes, $seconds) = explode(':', $hours);
	    }
	    return $hours * 60 + $minutes + $seconds / 60;
	}

	function sec2hms ($sec, $padHours = false) 
	{
		// start with a blank string
		$hms = "";

		// do the hours first: there are 3600 seconds in an hour, so if we divide
		// the total number of seconds by 3600 and throw away the remainder, we're
		// left with the number of hours in those seconds
		$hours = intval(intval($sec) / 3600); 

		// add hours to $hms (with a leading 0 if asked for)
		$hms .= str_pad($hours, 2, "0", STR_PAD_LEFT). ":";

		// dividing the total seconds by 60 will give us the number of minutes
		// in total, but we're interested in *minutes past the hour* and to get
		// this, we have to divide by 60 again and then use the remainder
		$minutes = intval(($sec / 60) % 60); 

		// add minutes to $hms (with a leading 0 if needed)
		$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

		// seconds past the minute are found by dividing the total number of seconds
		// by 60 and using the remainder
		$seconds = intval($sec % 60); 

		// add seconds to $hms (with a leading 0 if needed)
		$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

		// done!
		return $hms;
	}

	function save_row(){
/*		$this->db->where('employee_id',$this->input->post('id'));
		$this->db->where('date',date('Y-m-d',strtotime($this->input->post('date'))));
		$result = $this->db->get('dialed_hours')->row();
*/
		$pieces = explode('/', $this->input->post('id'));
		$id = $pieces[0];
		$dtr_id = $pieces[1];

		$focus_date = $this->db->get_where('employee_dtr', array('id' => $dtr_id))->row()->date;

		$this->db->where('employee_id',$id);
		$this->db->where('date',date('Y-m-d',strtotime($focus_date)));
		// $this->db->delete('dialed_hours');
		$exists = $this->db->get('dialed_hours');
		if($exists && $exists->num_rows() > 0){

			$array_info = array();
			$array_info['remarks'] = $this->input->post('remarks');
			$array_info['oe_for_discrepancy'] = $this->input->post('oe_for_discrepancy');

			$this->db->update('dialed_hours', $array_info, array('employee_id' => $id, 'date' => $focus_date));
			
		} else {

			$array_info = array();
			$array_info['employee_id'] = $id;
			$array_info['date'] = date('Y-m-d',strtotime($focus_date));
			$array_info['remarks'] = $this->input->post('remarks');
			$array_info['oe_for_discrepancy'] = $this->input->post('oe_for_discrepancy');

			$this->db->insert('dialed_hours',$array_info);
		}
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>