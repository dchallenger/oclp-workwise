<?php

include (APPPATH . 'controllers/dtr/periods.php');

class Firstbalfour_periods extends periods
{
	public function __construct() {
		parent::__construct();
	}	

	// ------------------------------------------------------------------------

	function process()
	{
		ini_set("max_execution_time", 3600);
		ini_set("memory_limit", "512M");
		$this->load->helper('file');

		if (!$this->user_access[$this->module_id]['post']) {
			if (IS_AJAX) {
				header('HTTP/1.1 403 Forbidden');
			} else {
				$this->session->set_flashdata('flashdata', 'Insufficient access! Please contact the System Administrator.');
				redirect( base_url() );
			}
		} else {	
			
			$period_id = $this->input->post('period_id');
			$this->db->where('period_id', $period_id);
			$this->db->where('deleted', 0);
			$period = $this->db->get('timekeeping_period');

			if( $period->num_rows() > 0 ){
				$period = $period->row();
			}
			else{
				$response->msg_type = 'error';
				$response->msg = 'Specified period does not exists, please call the Administrator';	
				$this->load->view('template/ajax', array('json' => $response));
				return;
			}

			$type = $this->input->post('type');


			$values = implode(',', $this->input->post('values'));
			$this->db->where($this->db->dbprefix.'user.deleted', 0);

			$this->db->join($this->db->dbprefix.'employee', $this->db->dbprefix."user.employee_id = ".$this->db->dbprefix."employee.employee_id");
			$this->db->join($this->db->dbprefix.'employment_status', $this->db->dbprefix."employment_status.employment_status_id = ".$this->db->dbprefix."employee.status_id");

			if( sizeof($this->input->post('status')) > 0 && $this->input->post('status') != ''){
				$this->db->where_in($this->db->dbprefix."employment_status.employment_status_id", $this->input->post('status'));
			}

			$this->db->select($this->db->dbprefix.'user.*, '.$this->db->dbprefix.'employee.*');
			$this->db->where_in($this->db->dbprefix."user.".$this->input->post('type'), $this->input->post('values'));
			$this->db->order_by('user.lastname');
			$records = $this->db->get('user');				

			$total = 0;
			if ($records){
				$total = $records->num_rows();
			}

			if ($total > 0) {
				$employees = $records->result();
				switch($type){
					case 'employee_id':
						$otfolder = 'employee';
						break;
					case 'company_id':
						$otfolder = 'company';
						break;
					case 'division_id':
						$otfolder = 'division';
						break;
					case 'department_id':
						$otfolder = 'department';
						break;
					case 'group_name_id':
						$otfolder = 'project_name';
						break;
					case 'project_name_id':
						$otfolder = 'project_name';
						break;												
				}

				$otfolder = 'uploads/otfile/' . $otfolder;
				if(!file_exists( $otfolder ) ) mkdir( $otfolder , 0777, true);
				$otfile = $otfolder. '/' . $this->input->post('filename') . '.txt';
				$otfile_row =array();
				if(file_exists($otfile)){ unlink($otfile); }
				$otfile_row['current'][] = array('Employee No', 'Date', 'OT Code', 'OT Hours', 'Leave Type', 'Leave Hours' );

				$progressfile = 'uploads/'.$this->input->post('filename').'-progresslog.txt';
				$ctr = 1;
				$employee_ids = array();

				$day_types = $this->db->get('day_type_and_rates');
				$otcode = array();
				foreach( $day_types->result_array() as $day_type ){
					$prfx = $day_type['day_prefix'];
					foreach($day_type as $col => $value){
						if( !in_array( $col, array('day_type_id', 'day_type', 'day_prefix')) && !strpos($col, '_code') ){
							$otcode[$prfx."_".$col] = $day_type[$col.'_code'];
						}
					}
				}

				$this->otcode = $otcode;
				foreach ($employees as $employee) {
					$employee_ids[] = $employee->employee_id;
					write_file($progressfile, number_format(($ctr++ / $total) * 100, 2).' ('.$ctr.'/'.$total.' employee/ current:'.$employee->employee_id.')');

					$s = $this->_formProcess($employee, $period, $otfile_row, $otcode);
					if ($s) {
						$summary[] = $s['summary_update'];
						$otfile_row = $s['otfile_row'];
					}
				}

				$ot_string = "";
				foreach( $otfile_row['current'] as $otrow ){
					$ot_string .= implode("\t", $otrow) . "\r\n";
				}
				
				if( isset($otfile_row['lates']) ){
					foreach( $otfile_row['lates'] as $otrow ){
						$ot_string .= implode("\t", $otrow) . "\r\n";
					}
				}

				if( write_file($otfile, $ot_string, 'w+') ){
					$response->otfile = $otfile;	
					if(file_exists($progressfile)) unlink($progressfile);
				}

				$this->db->where('period_id', $period_id);
				$this->db->where_in('employee_id', $employee_ids);
				$this->db->delete('timekeeping_period_summary');

				foreach($summary as $row){
					$this->db->insert('timekeeping_period_summary', $row);
				}

				if ($this->db->_error_message() == '') {
					$response->msg_type = 'success';
					$response->msg =  'Successfully processed ' . $total . ' records.';

					$this->db->where('period_id', $this->input->post('period_id'));
					$this->db->update('timekeeping_period', array('processed' => 1));
				} else {
					$response->msg_type = 'error';
					$response->msg = 'Period processing failed. ' . $this->db->last_query();
				}	
			}else{
				$response->msg_type = 'error';
				$response->msg = 'No employees to process in specified period';	
			}				
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function getprogress()
	{
		$period_id = $this->input->post('period_id');
		$this->db->where('period_id', $period_id);
		$this->db->where('deleted', 0);
		$period = $this->db->get('timekeeping_period')->row();

		$type = $this->input->post('type');
		$status = $this->input->post('status');
		$values = implode(',', $this->input->post('values'));

		switch($type){
			case 'employee_id':
				$otfolder = 'employee';
				break;
			case 'company_id':
				$otfolder = 'company';
				break;
			case 'division_id':
				$otfolder = 'division';
				break;
			case 'department_id':
				$otfolder = 'department';
				break;
			case 'group_name_id':
				$otfolder = 'project_name';
				break;
			case 'project_name_id':
				$otfolder = 'project_name';
				break;					
		}

		$otfolder = 'uploads/otfile/' . $otfolder;
		if(!file_exists( $otfolder ) ) mkdir( $otfolder , 0777, true);
		$otfile = $otfolder. '/' . $this->input->post('filename') . '.txt';
		$data['otfile'] = $otfile;

		$this->load->helper('file');
		$progress = read_file('uploads/'.$this->input->post('filename').'-progresslog.txt');
		$data['progress'] = $progress;
		if( !$progress && file_exists('uploads/'.$this->input->post('filename').'-progresslog.txt') ) unlink('uploads/'.$this->input->post('filename').'-progresslog.txt');
		$this->load->view('template/ajax', array('json' => $data));
	}

	private function _formProcess($employee, $p, $otfile_row, $otcode)
	{
		$employee_id = $employee->employee_id;
		$employee_no = $employee->id_number;
		$employee_department = $employee->department_id;

		//check if supervisor
		$supervisor = false;
		$regular = false;
		$actual_hours = false;
		// check if resigned
		$resigned = $this->system->check_if_resigned($employee_id);
		if($resigned)
			$resigned_date = $resigned->resigned_date;
		else
			$resigned_date = null;

		//for overtime of non regular employee
		$non_regular_eot = false;
		
		if( in_array($employee->employee_type, $this->config->item('emp_type_no_late_ut')) ){
			$supervisor = true;
		}

		if( in_array($employee->employee_type, $this->config->item('emp_type_actual_hours')) ){
			$actual_hours = true;
		}

		if( ( $employee->status_id == 1 ) || ( $employee->status_id == 2 ) ){
			$regular = true;
		}

		$grace_period_emp = $this->system->get_grace_period($employee_id);
		$period_id = $p->period_id;
		$payroll_date = $p->payroll_date;

		$summary_update = array(
			'employee_id' => $employee_id, 
			'period_id' => $period_id,
			'payroll_date' => $payroll_date,
			'hours_worked' => 0, 
			'lates' => 0, 
			'undertime' => 0, 				
			'overtime' => 0,
			'absences' => 0,
			'lwop' => 0,
			'lwp' => 0,
			'reg_ot' => 0,
			'reg_nd' => 0,
			'reg_ndot' => 0,
			'rd_ot' => 0,
			'rd_ot_excess' => 0,
			'rd_ndot' => 0,
			'rd_ndot_excess' => 0,
			'leg_ot' => 0,
			'leg_ot_excess' => 0,
			'leg_ndot' => 0,
			'leg_ndot_excess' => 0,
			'spe_ot' => 0,
			'spe_ot_excess' => 0,
			'spe_ndot' => 0,
			'spe_ndot_excess' => 0,
			'legrd_ot' => 0,
			'legrd_ot_excess' => 0,
			'legrd_ndot' => 0,
			'legrd_ndot_excess' => 0,
			'sperd_ot' => 0,
			'sperd_ot_excess' => 0,
			'sperd_ndot' => 0,
			'sperd_ndot_excess' => 0,
			'dob_ot' => 0,
			'dob_ot_excess' => 0,
			'dob_ndot' => 0,
			'dob_ndot_excess' => 0,
			'dobrd_ot' => 0,
			'dobrd_ot_excess' => 0,
			'dobrd_ndot' => 0,				
			'dobrd_ndot_excess' => 0,
		);

		$infraction = array();

		// $cdate = current date in loop, need this to keep track
		$cdate = $p->date_from;
		$consecutive_absent = 0;

		if($this->config->item('client_no') == 2) {
			$config = $this->hdicore->_get_config('habitual_tardiness_configuration');
			if($config['consec_days_awol'] == null || !isset($config['consec_days_awol']) || trim($config['consec_days_awol']) == '' || $config['consec_days_awol'] == 0)
				$consec_days_awol = $this->config->item('consec_days_for_awol');
			else
				$consec_days_awol = $config['consec_days_awol'];
		} else
			$consec_days_awol = $this->config->item('consec_days_for_awol');

		$tstampdateto = strtotime($p->date_to);			
		$tstamp_cdate = strtotime($cdate);

		// as discussed with marvin. openaccess processing should not process current date.
		if($this->config->item('client_no') == 2)
			$date_today = strtotime('-1 day', strtotime(date('Y-m-d')));
		else
			$date_today = strtotime(date('Y-m-d'));

		// Start "the loop"
		while ($tstamp_cdate <=  $tstampdateto && $tstamp_cdate <= $date_today) {
			$otfile_date = date('m/d/Y', $tstamp_cdate);
			if( $tstamp_cdate >= strtotime( $employee->employed_date) ){
				$schedule = $this->system->get_employee_worksched($employee_id, $cdate);
				if( $schedule ){
					$dtr_p = $this->_processDtr($employee_id, $cdate, $tstamp_cdate, $regular, $schedule, $summary_update, false, $p, $otfile_row, $employee_no, $supervisor, $resigned_date, $employee_department, $actual_hours);
					if (isset($dtr_p['absent_dtr'])) {					
						$absent_dtr[] = $dtr_p['absent_dtr'];
					} else {
						$absent_dtr = array();					
					}
					
					if (count($absent_dtr) >= $consec_days_awol && !$supervisor) { 
						// AWOL
						$this->db->where_in('id', $absent_dtr);
						$this->db->update('employee_dtr', array('awol' => true));

						// Set notification date to be sent
						$this->db->where('id', $dtr_p['absent_dtr']);
						$this->db->update('employee_dtr', array('send_awol_notification' => true));					
					}

					if (!is_null($dtr_p['summary_update']) && isset($dtr_p['summary_update'])) {
						$summary_update = $dtr_p['summary_update'];
						$otfile_row = $dtr_p['otfile_row'];
					}
				}
			}
			else{
				$schedule = $this->system->get_employee_worksched($employee_id, $cdate, true);
				if( $schedule ){
					$get_worked_hours = $this->system->get_cred_duration($employee_id, $cdate);

					if(CLIENT_DIR == 'oams' && isset($get_worked_hours->total_work_hours) && !is_null($get_worked_hours->total_work_hours))
						$total_work_hours = $get_worked_hours->total_work_hours;
					else
						$total_work_hours = 8;

					$summary_update['lwop'] += $total_work_hours;
					//$otfile_row['current'][] = array($employee_no, $otfile_date, '', '', 'LWP', number_format(8,2, '.', '')); //commented out, no use for client, just in case
				}	
			}
			$cdate = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
			$tstamp_cdate = strtotime($cdate);

			if(isset($dtr_p['infraction'])) $infraction = array_merge_recursive($infraction, $dtr_p['infraction']);

		}
		// Get late filing.
		// Get only the dates where there is any instance of a late filing so we can loop through these dates instead.
		
		//get cutoff of previous period
		$qry = "SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE cutoff < '{$p->cutoff}' AND DELETED = 0 ORDER BY cutoff DESC LIMIT 1";
		$prevcutof = $this->db->query($qry)->row();
		$prevcutof = date('Y-m-d', strtotime('+1 day', strtotime($prevcutof->cutoff)));
		$sql = "select * from (

				SELECT date AS date, 'oot' as type FROM {$this->db->dbprefix}employee_oot 
				WHERE 
					employee_id = {$employee_id} AND
					date_approved BETWEEN '". $prevcutof . " 00:00:00' AND '" . $p->cutoff . " 23:59:59'
					AND (date NOT BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "')
					AND date < '". $p->date_from . "'
					AND form_status_id = 3
				UNION
				SELECT DATE, 'out' as type FROM {$this->db->dbprefix}employee_out 
				WHERE 
					employee_id = {$employee_id} AND
					date_approved BETWEEN '". $prevcutof . " 00:00:00' AND '" . $p->cutoff . " 23:59:59'
					AND (DATE NOT BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "')
					AND DATE < '". $p->date_from . "'	
					AND form_status_id = 3
				UNION 
				SELECT datelate, 'et' as type FROM {$this->db->dbprefix}employee_et 
				WHERE 
					employee_id = {$employee_id} AND
					date_approved BETWEEN '". $prevcutof . " 00:00:00' AND '" . $p->cutoff . " 23:59:59'
					AND (datelate NOT BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "')
					AND datelate < '". $p->date_from . "'
					AND form_status_id = 3			
				UNION 
				SELECT date, 'obt' as type FROM {$this->db->dbprefix}employee_obt_date a
				LEFT JOIN {$this->db->dbprefix}employee_obt b ON b.employee_obt_id = a.employee_obt_id
				WHERE 
					b.employee_id = {$employee_id} AND
					b.date_approved BETWEEN '". $prevcutof . " 00:00:00' AND '" . $p->cutoff . " 23:59:59'
					AND (CONCAT(a.date, ' ', a.time_start) NOT BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00')
					AND a.date < '". $p->date_from . "'
					AND b.form_status_id = 3		
				UNION
				SELECT a.date, 'leave' as type FROM {$this->db->dbprefix}employee_leaves_dates a
				LEFT JOIN {$this->db->dbprefix}employee_leaves b ON b.employee_leave_id = a.employee_leave_id
				WHERE 
					b.employee_id = {$employee_id} AND
					b.date_approved BETWEEN '". $prevcutof . " 00:00:00' AND '" . $p->cutoff . " 23:59:59'
					AND (a.date NOT BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "')
					AND a.date < '". $p->date_from . "'
					AND b.form_status_id = 3

				UNION
				SELECT date, 'dtrp' as type FROM {$this->db->dbprefix}employee_dtrp
				WHERE 
					employee_id = {$employee_id} AND
					date_approved BETWEEN '". $prevcutof . " 00:00:00' AND '" . $p->cutoff . " 23:59:59'
					AND (date NOT BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "')
					AND date < '". $p->date_from . "'
					AND form_status_id = 3

				) as t_all group by date";

		$lf_dates_qry = $this->db->query($sql);

		if ($lf_dates_qry->num_rows() > 0) {
			$lf_dates = $lf_dates_qry->result();
			foreach ($lf_dates as $lf_date) {
				$schedule = $this->system->get_employee_worksched($employee_id, $lf_date->date);
				if( $schedule || in_array($lf_date->type, array('oot', 'obt')) ){
					$dtr_p = $this->_processDtr($employee_id, $lf_date->date, strtotime($lf_date->date), $regular, $schedule, $summary_update, true, $p, $otfile_row, $employee_no, $supervisor, $resigned_date, $employee_department, $actual_hours);
		
					if (!is_null($dtr_p['summary_update']) && isset($dtr_p['summary_update'])) {
						$summary_update = $dtr_p['summary_update'];
						$otfile_row = $dtr_p['otfile_row'];
					}
				}
			}
		}	
		// End late filing

		if ($p->apply_lates == 1) {
			if (count($infraction) > 0) {
				$this->db->where_not_in('date', $infraction['date']);
			}
			$this->db->where('employee_id', $employee_id);
			$this->db->where('(date BETWEEN \'' . $p->apply_late_from . '\' AND \'' . $p->apply_late_to . '\')', '', false);
			$this->db->where('deleted', 0);				

			$result = $this->db->get('employee_dtr');
			$dummy_p->date_from = $p->apply_late_from;
			$dummy_p->date_to = $p->apply_late_to;

			if ($result->num_rows() > 0) {
				$a_result = $result->result();
				foreach ($a_result as $dtr) {
					if (isset($shift_cache[$dtr->date])) {
						$shift_start = $shift_cache[$dtr->date]['start'];
						$shift_end = $shift_cache[$dtr->date]['end'];
						$grace_period = $shift_cache[$dtr->date]['grace_period'];
						$grace_period_s = $shift_cache[$dtr->date]['grace_period_s'];
					} else {
						// get specific day of the week so we can query against the shift_calendar table for the proper shift ie monday_shift_id
						$day = strtolower(date('l', strtotime($dtr->date)));
						// Get default shift for this day.
						$this->db->where('shift_calendar_id', $schedule->shift_calendar_id);
						$this->db->join('timekeeping_shift', 
							'timekeeping_shift.shift_id = timekeeping_shift_calendar.' . $day . '_shift_id');
						$this->db->where('timekeeping_shift_calendar.deleted', 0);

						$result = $this->db->get('timekeeping_shift_calendar');

						// Check for group workschedule.						
						$this->db->where('(\'' . $dtr->date . '\' BETWEEN date_from AND date_to)');						
						$this->db->where('timekeeping_shift.deleted', 0);
						$this->db->where('employee_id', $employee_id);
						$this->db->join('timekeeping_shift_calendar', 
							'timekeeping_shift_calendar.shift_calendar_id = workschedule_employee.shift_calendar_id');
						$this->db->join('timekeeping_shift', 
							'timekeeping_shift.shift_id = timekeeping_shift_calendar.' . $day . '_shift_id');
						$this->db->order_by('date_from', 'asc');
						$gws = $this->db->get('workschedule_employee');
						
						if ($gws->num_rows() > 0) {
							$result = $gws;
						}

						// Check CWS
						$cws = get_form($employee_id, 'cws', $dummy_p, $dtr->date);

						if ($cws->num_rows() > 0) {
							$cws = $cws->row();
							// Set shift to cws shift id
							$this->db->select('timekeeping_shift.shifttime_start, timekeeping_shift.shifttime_end, timekeeping_shift.shift_grace_period, timekeeping_shift.noon_start, timekeeping_shift.noon_end');
							$this->db->where('shift_id', $cws->shift_id);
							$this->db->where('timekeeping_shift.deleted', 0);
							$result = $this->db->get('timekeeping_shift');
						}

						$shift_start = $result->row()->shifttime_start;
						$shift_end = $result->row()->shifttime_end;
						$grace_period = date('i', strtotime($result->row()->shift_grace_period));
						if ($result->row()->shift_grace_period != ''){
							$grace_period_s = ($grace_period_emp !== 0 ? hoursToSeconds($grace_period_emp) : hoursToSeconds($result->row()->shift_grace_period));
						}
						else{
							$grace_period_s = ($grace_period_emp !== 0 ? date('i', strtotime($grace_period_emp)) : '00:00:00');
						}
					}
					
					// Check late
					// Get excused tardiness first.
					$et = get_form($employee_id, 'et', $dummy_p, $dtr->date);

					if ($et->num_rows() == 0 && $dtr->time_in1 != ''){
						if (strtotime(date('H:i:s', strtotime($dtr->time_in1))) > 
							strtotime('+' . $grace_period_s . ' seconds', strtotime($shift_start))){
							$infraction['date'][] = $dtr->date;
							$infraction['time'][] = $dtr->time_in1;
						}
					}
				}
			}

			//$this->_ir_creation($infraction, $employee_id, $p);
		}

		return array('summary_update' => $summary_update, 'otfile_row' => $otfile_row);
	}

	protected function _ir_creation($infraction, $employee_id, $period = false)
	{
		//if (count($infraction['date']) >= $this->config->item('maximum_late_per_month') || array_sum($infraction['min_lates']) > 60) {
		if (count($infraction['date']) >= $this->config->item('maximum_late_per_month')) {
			// Create IR
			$ir_data['offence_id'] = $this->config->item('ir_for_late');
			$ir_data['location']   = 'Office';
			$ir_data['complainants'] = $this->userinfo['user_id'];
			$ir_data['involved_employees'] = $employee_id;
			$offence = $this->db->get_where('offence', array("offence_id" => $this->config->item('ir_for_late')));
			$ir_data['details'] = $offence->row()->offence; // details of violation to be the same as offence
			$ir_data['ir_status_id'] = '6';
			$ir_data['date_sent'] = date('Y-m-d H:i:s');
			$this->db->insert('employee_ir', $ir_data);
			$this->_send_email($employee_id, $infraction, $period);				
		}
	}


    /**
     * Send the email to employees,immediate superior and hr.
     */
    protected function _send_email($employee_id = false, $infraction = false, $period = array()) {
    	if (!$employee_id){
    		return false;
    	}

        // $request['date'] = date($this->config->item('display_date_format'), strtotime($date_email));

    	$recipients = array();
    	$result = $this->db->get_where('user',array('user_id'=>$employee_id));
    	if ($result && $result->num_rows() > 0){
    		$single_row = $result->row();
    		$request = $result->row_array();

    		if ($single_row->email != ''){
				$recipients[] = $single_row->email;
    		}

    		$immediate_superior = $this->system->get_reporting_to($employee_id);

    		if ($immediate_superior){
    			$result = $this->db->get_where('user',array('user_id'=>$immediate_superior));
		    	if ($result && $result->num_rows() > 0){
		    		$single_row = $result->row();
		    		if ($single_row->email != ''){
						$recipients[] = $single_row->email;
		    		}    			
		    	}
    		}
    	}

        $request['start_date_tardy'] = date('M.Y',strtotime($infraction['date'][0]));
        $request['count_tardy'] = count($infraction['date']);

        $request['processed_date'] = $period->apply_late_from;
        
        $list_tardy_html = '';
        foreach ($infraction['date'] as $key => $value) {
        	$list_tardy_html .= '<p>'.date('M.d',strtotime($value)).' -  '.date($this->config->item('display_datetime_format_email'),strtotime($infraction['time'][$key])).' - '. ($infraction['min_lates'][$key] / 60) .'min(s)</p>';
        }

        $request['list_tardy'] = $list_tardy_html;

    	$dtr_notification_settings = $this->hdicore->_get_config('dtr_notification_settings');

		$emailto_list = array();
		$emailcc_list = array();

		foreach( $dtr_notification_settings['email_to'] as $email_to_settings ){
			$recipients[] = $email_to_settings['email'];
		}

		foreach( $dtr_notification_settings['email_cc'] as $email_cc_settings ){
			$emailcc_list[] = $email_cc_settings['email'];
		}

		$email_cc_recipient = implode(', ',$emailcc_list);

        $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
        if ($mail_config) {
            // Load the template.            
            $this->load->model('template');
            $template = $this->template->get_module_template(184, 'habitual_tardiness');
            $message = $this->template->prep_message($template['body'], $request);
            $this->template->queue(implode(',', $recipients), $email_cc_recipient, $template['subject'], $message);
        }
    }

	private function _processDtr($employee_id, $cdate, $tstamp_cdate, $regular, $schedule, $summary_update, $for_late_filing = false, $p, $otfile_row, $employee_no, $supervisor, $resigned_date, $employee_department = false, $actual_hours)
	{
		$this->db->delete('dtr_daily_summary_lf', array('period_id' => $p->period_id, 'employee_id' => $employee_id, 'date' => $cdate));
		if ($for_late_filing) {
			$get_form = 'get_late_file_form';			
		} else {
			$get_form = 'get_form';
			$this->db->delete('dtr_daily_summary', array('period_id' => $p->period_id, 'employee_id' => $employee_id, 'date' => $cdate));
		}

		$otfile_date = date('m/d/Y', $tstamp_cdate);		
		$undertime = 0;
		$overtime = 0;
		$total_overtime = 0;
		$lates = 0;	
		$fixed_lates = 0;
		$restday = false;
		$minutes_worked = 0;
		$day_ot = 0;
		$hour_past_nd = 0;
		$nd = 0;				
		$ndot = 0;
		$ot_excess = 0;
		$ndot_excess = 0;	
		$late_infraction = false;
		$undertime_infraction = false;
		$non_regular_eot = false;
		$absent = false;
		$by_pass_holiday = false;
		$out_num_rows = 0;
		$et_num_rows = 0;
		$excused_tardiness = 0;
		$lates_display = 0;
		$approved_undertime = 0;
		$undertime_display = 0;

		$dailysummary = array(
			'period_id' => $p->period_id,
			'employee_id' => $employee_id,
			'employee_no' => $employee_no,
			'date' => $cdate,
			'lates' => 0,
			'undertime' => 0,
			'lwp' => 0,
			'lwop' => 0,
			'hours_worked' => 0,
			'ot' => 0,
			'ot_excess' => 0,
			'nd' => 0,
			'ndot' => 0,
			'ndot_excess' => 0,
			'absent' => 0
		);		

		$get_worked_hours = $this->system->get_cred_duration($employee_id, $cdate);

		if($supervisor) {
			$hours_worked = (strtotime($get_worked_hours->shifttime_end) - strtotime($get_worked_hours->shifttime_start)) /60 /60;
			$hours_worked = $hours_worked - 1;
			$hours_with_break = $hours_worked + 1;
			$hours_leave_worked = 8; // I think this should be fix on 8. but incase i haven't think of all the possible scenario. we can easily change it.
		} else {
			$hours_worked = 8;
			$hours_with_break = 9;
			$hours_leave_worked = 8; // I think this should be fix on 8. but incase i haven't think of all the possible scenario. we can easily change it.
		}

		$grace_period_emp = $this->system->get_grace_period($employee_id);
		$day = strtolower(date('l', $tstamp_cdate));
		$tomorrow = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
		$day_prefix = 'reg';

		// check holiday.
		$holiday = $this->system->holiday_check($cdate, $employee_id);

		if ($holiday) {
			// what type of holuiday					
			if (count($holiday) > 1) {
				$day_prefix = 'dob';
			} else {
				$day_prefix = ($holiday[0]['legal_holiday'] == '1') ? 'leg' : 'spe';
			}
		}

		if ($cdate == '2020-07-02') {
			dbug($day_prefix);
			dbug($holiday);
			die();
		}
		// Check if suspended
		$suspended = get_employee_suspended($employee_id, $cdate);
		if($suspended){
			$this->db->where('employee_id', $employee_id);
			$this->db->where('date', $cdate);
			$this->db->set('suspended', 1);
			$this->db->update('employee_dtr');
			$cdate = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
			$tstamp_cdate = strtotime($cdate);
			return;
		}

		// get DTR for IN/OUT
		$dtr = get_employee_dtr_from($employee_id, $cdate);

		// check if resigned
		if($resigned_date != null)
		{
			if($resigned_date < $cdate)
			{
				$resigned_data = array(
									"employee_id" => $employee_id,
									"date" => $cdate,
									"resigned" => 1,
									"hours_worked" => 0,
									"lates" => 0,
									"overtime" => 0,
									"undertime" => 0,
									"reg_nd" => 0,
									"ot_nd" => 0,
									"restday" => 0,
									"awol" => 0,
									"suspended" => 0,
									"undertime_infraction" => 0,
									"late_infraction" => 0
									);

				$dtr = get_employee_dtr_from($employee_id, $cdate);
				if($dtr && $dtr->num_rows() == 0) {
					$this->db->insert('employee_dtr', $resigned_data);
				} else {
					$this->db->where('employee_id', $employee_id);
					$this->db->where('date', $cdate);
					$this->db->update('employee_dtr', $resigned_data);
				}
				$cdate = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
				$tstamp_cdate = strtotime($cdate);
				
				$summary_update['lwop'] += $hours_worked;
				$r = array('summary_update' => $summary_update, 'otfile_row' => $otfile_row);
				return $r;
			}
		}

		// check if floating
		if($this->config->item('with_floating') == 1)
		{
			$is_floating = $this->hdicore->check_if_floating_period($employee_id, $cdate);
			if($is_floating)
			{
				$cdate = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
				$tstamp_cdate = strtotime($cdate);
				return;
			}

		}

		// GET OBT and DTRP
		$obt = $get_form($employee_id, 'obt', $p, $cdate, true, $for_late_filing, true);
		$dtrp = $get_form($employee_id, 'dtrp', $p, $cdate, true, $for_late_filing, true);

		if(isset($schedule->has_cws)){
			$day_shift_id = $schedule->shift_id;
		} else if(isset($schedule->has_cal_shift)) {
			$day_shift_id = $schedule->shift_id;
		} else{
			$day_shift_id = $schedule->{$day . '_shift_id'};
		}

		if($day_shift_id == 1)
			$day_shift_id = 0;

		if ($day_shift_id == 0) {
			$restday = true;

			if ($this->config->item('client_no') == 2){
				$this->db->where('employee_id', $employee_id);
				$this->db->where(array('date' => $cdate, 'deleted' => 0));
				$edtr = $this->db->get('employee_dtr');
				if ($edtr->num_rows() == 0) {
					$this->db->insert('employee_dtr', array('restday' => $restday));
					$dtr_id = $this->db->insert_id();
				} else {
					$dtr_id = $edtr->row()->id;
					$this->db->where('employee_id', $employee_id);
					$this->db->where('date', $cdate);
					$this->db->update('employee_dtr', array('restday' => $restday, 'awol' => 0));
				}
			}
		
			if ( ($dtr->num_rows() > 0 && $dtr->row()->time_out1 != null) || $dtrp->num_rows() > 0) {
				
				$absent  = false;

				if ($holiday) {
					$day_prefix = $day_prefix . 'rd';							
				} else {
					$day_prefix = 'rd';							
				}

			}/* else {

				//Check for maternity leaves
				$this->db->where('employee_id', $employee_id);
				$this->db->where('(\'' . $cdate . '\' BETWEEN date_from AND date_to)', '', false);
				$this->db->where('form_status_id', 3);
				$this->db->where('application_form_id', 5);
				$this->db->where('deleted', 0);
				$leave = $this->db->get('employee_leaves');

				if( $obt->num_rows() == 0 && $leave->num_rows() == 0 ){
					return;
				}
			}
*/
		}

		if($day_shift_id == 0) $day_shift_id = 1;
		$result = $this->db->get_where('timekeeping_shift', array( 'shift_id'  => $day_shift_id  ));
		$workshift = $result->row();

		$grace_period = ($grace_period_emp !== 0 ? date('i', strtotime($grace_period_emp)) : date('i', strtotime($workshift->shift_grace_period)));
		$grace_period_s = ($grace_period_emp !== 0 ? hoursToSeconds($grace_period_emp) : hoursToSeconds($workshift->shift_grace_period));

		//tirso : employee cws override with shift schedule that's why I change the day_prefix into reg
		if(isset($schedule->has_cws)){
			if ($this->config->item('client_no') == 1){
				if (!$holiday){
					$day_prefix = 'reg';
					$restday = false;
				}
			}
			else {
				if ($workshift->shift != "RESTDAY" && !$holiday){
					$day_prefix = 'reg';
					$restday = false;
				}
			}
		}

		$dailysummary['day_type'] = $day_prefix;

		//time
		$shift_start = $workshift->shifttime_start;
		$shift_end 	 = $workshift->shifttime_end;
		$halfday_time = $workshift->halfday;
		$halfday_minutes = $workshift->minimum_halfday_minutes * 60;

		//tirso
		$halfday_minutes_undertime = 0;
		if (isset($workshift->minimum_minutes_undertime_consider_halfday)){
			$halfday_minutes_undertime = $workshift->minimum_minutes_undertime_consider_halfday * 60;
		}

		$night_shift = false;
		// Get exact datetime to use in case of overlapping days for shift.
		$shift_datetime_start = $cdate . ' ' . $shift_start;
		$halfday_datetime = $cdate.' '.$halfday_time;
		if (strtotime($shift_start) > strtotime($shift_end)) {
			$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $shift_end)));

			if($shift_datetime_start > $halfday_datetime)
				$halfday_datetime = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $halfday_time)));

			$night_shift = true;
		} else {
			$shift_datetime_end = $cdate . ' ' . $shift_end;
		}

		// set dtr in and out
		$dtr_in = '';
		$dtr_out = '';
		if ($dtr->num_rows() > 0 ){
			$record = $dtr->row();
			if( is_valid_time($record->time_in1) ) $dtr_in = $record->time_in1;
			if( is_valid_time($record->time_out1) ) $dtr_out = $record->time_out1;
		}

		if ($obt->num_rows() > 0) {
			foreach($obt->result() as $ob){
				// If no time in set start of obt as time in
				if( empty($dtr_in) 
					|| $dtr_in == '0000-00-00 00:00:00'
					|| strtotime($dtr_in) > strtotime($cdate . ' ' . $ob->time_start)
					) {
					$dtr_in = $cdate . ' ' . $ob->time_start;
				}

				// If no time out set start of obt as time out
				if( empty($dtr_out)
					|| $dtr_out == '0000-00-00 00:00:00'
					|| strtotime($ob->time_end) > strtotime(date('H:i:s', strtotime($dtr_out)))
					) {									
					$dtr_out = $cdate . ' ' . $ob->time_end;
				}	
			}
		}

		if ($dtrp->num_rows() > 0) {
			// process
			foreach ($dtrp->result() as $entry) {
				if ($entry->time_set_id == 1) {
					// If no time in set start of obt as time in
					if( empty($dtr_in) 
						|| $dtr_in == '0000-00-00 00:00:00'
						|| strtotime($dtr_in) > strtotime($entry->time)
						) {
						$dtr_in = $entry->time;
					}
				} else {
					if( empty($dtr_out)
						|| $dtr_out == '0000-00-00 00:00:00'
						|| strtotime($dtr_out) < strtotime($entry->time)
						) {									
						$dtr_out = $entry->time;
					}
				}
			}
		}

		if ( is_valid_time($dtr_in) && is_valid_time($dtr_out) ) { 
			if ($result->num_rows() > 0 || $restday) {

				// remove seconds on time in and out for openaccess
				if($this->config->item('client_no') == 2) {
					$dtr_in = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i', strtotime($dtr_in))));
					$dtr_out = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i', strtotime($dtr_out))));
				}

				$r = $result->row();

				$break = 0;
				if (!$restday) {
					$break = (strtotime($r->noon_end) - strtotime($r->noon_start));
				} else { // Restday break every 5 hours = 1 hour break (legacy) NEW - if > 5 hours break = 1
					$break = (strtotime($dtr_out) - strtotime($dtr_in)) / 60 / 60;
					if ($break > 5) {
						$break = 60 * 60;
					}
				}
				
				//get forms
				//$oot = $get_form($employee_id, 'oot', $p, date('Y-m-d',strtotime($dtr_in)),true,$for_late_filing,true);
				$oot = $get_form($employee_id, 'oot', $p, $cdate,true,$for_late_filing,true);

/*dbug($cdate );
dbug($oot->num_rows());*/

				$otcdate = date('Y-m-d',strtotime($dtr_in));
				if( date('Y-m-d',strtotime($dtr_in)) == date('Y-m-d',strtotime($dtr_out) ) ){
					$otcdate = date('Y-m-d',strtotime($dtr_in));
				}
				else{
					//search if what date an overtime is filed
					if($oot->num_rows() > 0){
						foreach( $oot->result_array() as $oot_rec ){						
							if( ( strtotime($dtr_in) <= strtotime($oot_rec['datetime_from']) 
								&&  strtotime($oot_rec['datetime_from']) <= strtotime($dtr_out) ) 
								&& ( strtotime($dtr_in) <= strtotime($oot_rec['datetime_to']) 
									&&  strtotime($oot_rec['datetime_to']) <= strtotime($dtr_out) ) ) {
								$otcdate = date('Y-m-d',strtotime($dtr_in));

							}
						}
					}
					else{
						$otcdate = date('Y-m-d',strtotime($dtr_out));
					}
				}

				$ndstart = strtotime(date('Y-m-d 22:00:00', $tstamp_cdate));
				$ndend   = strtotime(date('Y-m-d 06:00:00', strtotime($tomorrow)));

				// for processing of employee's with late focus date. openaccess scenario.
				if($this->config->item('client_no') == 2)
				{
					if($shift_start >= "00:00:00" && $shift_start <= "14:00:00" && $shift_end >= "00:00:00" && $shift_end <= "14:00:00" && $day_shift_id != 1)
					{
						$shift_datetime_start = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($shift_datetime_start)));
						$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($shift_datetime_end)));

						// $ndstart = strtotime(date('Y-m-d 22:00:00', strtotime('-1 day', $tstamp_cdate)));
						// $ndend = strtotime(date('Y-m-d 06:00:00', strtotime('-1 day', strtotime($tomorrow))));
					}
				}

				//compute regular nd
				if( $day_shift_id != 1 && !$holiday ){
					//check sched should fall during ND hours
					if( 
						(strtotime($shift_datetime_start) >= $ndstart && strtotime($shift_datetime_start) <= $ndend)
						||
						(strtotime($shift_datetime_end) >= $ndstart && strtotime($shift_datetime_end) <= $ndend)
						||
						($ndstart >= strtotime($shift_datetime_start) && $ndstart <= strtotime($shift_datetime_end)) 
						|| 
						($ndend >= strtotime($shift_datetime_start) && $ndend <= strtotime($shift_datetime_end))
					){
						//compute start and end of ND
						//default
						// $ndshift_start = strtotime($dtr_in) >= $ndstart ? strtotime($dtr_in) : $ndstart;
						if($this->config->item('client_no') == 2)
						{
							if(strtotime($dtr_in) > strtotime('+' . $grace_period_s . ' seconds', strtotime($shift_datetime_start)))
								$lates = (strtotime($dtr_in) - strtotime($shift_datetime_start));

							if(strtotime($dtr_in) >= $ndstart) {
								$temp_in = ($halfday_minutes < $lates ? $halfday_datetime : $dtr_in);
								$ndshift_start = (strtotime($temp_in) > strtotime($shift_datetime_start) ? strtotime($temp_in) : strtotime($shift_datetime_start));
							} else {
								$ndshift_start = (strtotime($shift_datetime_start) < $ndstart ? $ndstart : strtotime($shift_datetime_start));
							}

							// $ndshift_end = strtotime($dtr_out) <= $ndend ? strtotime($dtr_out) : $ndend;
							if(strtotime($dtr_out) <= $ndend) {
								$ndshift_end = (strtotime($dtr_out) > strtotime($shift_datetime_end) ? strtotime($shift_datetime_end) : strtotime($dtr_out));
							} else {
								$ndshift_end = (strtotime($shift_datetime_end) > $ndend ? $ndend : strtotime($shift_datetime_end));
							}

							$nd = ($ndshift_end - $ndshift_start);							
						}
						else{
							if(strtotime($dtr_in) >= $ndstart) {
								$ndshift_start = (strtotime($dtr_in) > strtotime($shift_datetime_start) ? strtotime($dtr_in) : strtotime($shift_datetime_start));
							} else {
								$ndshift_start = (strtotime($shift_datetime_start) < $ndstart ? $ndstart : strtotime($shift_datetime_start));
							}

							// $ndshift_end = strtotime($dtr_out) <= $ndend ? strtotime($dtr_out) : $ndend;
							if(strtotime($dtr_out) <= $ndend) {
								$ndshift_end = (strtotime($dtr_out) > strtotime($shift_datetime_end) ? strtotime($shift_datetime_end) : strtotime($dtr_out));
							} else {
								$ndshift_end = (strtotime($shift_datetime_end) > $ndend ? $ndend : strtotime($shift_datetime_end));
							}
						}

						//customization
						if ($this->config->item('client_no') == 1){

							if(strtotime($dtr_in) >= $ndstart) {
								$temp_in = ($halfday_minutes < $lates ? $halfday_datetime : $dtr_in);
								$ndshift_start = (strtotime($temp_in) > strtotime($shift_datetime_start) ? strtotime($temp_in) : strtotime($shift_datetime_start));
							} else {
								$ndshift_start = (strtotime($shift_datetime_start) < $ndstart ? $ndstart : strtotime($shift_datetime_start));
							}

							// $ndshift_end = strtotime($dtr_out) <= $ndend ? strtotime($dtr_out) : $ndend;
							if(strtotime($dtr_out) <= $ndend) {
								$ndshift_end = (strtotime($dtr_out) > strtotime($shift_datetime_end) ? strtotime($shift_datetime_end) : strtotime($dtr_out));
							} else {
								$ndshift_end = (strtotime($shift_datetime_end) > $ndend ? $ndend : strtotime($shift_datetime_end));
							}

							$get_break=$this->system->get_break($shift_start, $workshift->noon_start,$workshift->noon_end, $cdate);
							if (strtotime(date('Y-m-d ' . $workshift->noon_start, strtotime($get_break['break_start_date']))) < $ndstart) {
								$nd = ($ndshift_end - $ndstart);
							} else if ($restday || $holiday) {	
								$nd = ($ndshift_end - $ndshift_start - $break);
							}					
						}
					}
				}else{
					// REST DAY ND
					if($day_shift_id == 1 && $ndstart <= strtotime($dtr_out) && strtotime($dtr_out) <= $ndend){
						$row_time_out = $dtr_out;
						$time_out_hrs = date('H', strtotime($dtr_out));
						$time_out_mins = date('i', strtotime($dtr_out));

						if($time_out_mins >= 0 && $time_out_mins < 15) $row_time_out = strtotime(date($cdate.' '.$time_out_hrs.':00:00', $dtr_out));
						else if($time_out_mins >= 15 && $time_out_mins < 30) $row_time_out = strtotime(date($cdate.' '.$time_out_hrs.':15:00', $dtr_out));
						else if($time_out_mins >= 30 && $time_out_mins < 45) $row_time_out = strtotime(date($cdate.' '.$time_out_hrs.':30:00', $dtr_out));
						else if($time_out_mins >= 45 && $time_out_mins < 60) $row_time_out = strtotime(date($cdate.' '.$time_out_hrs.':45:00', $dtr_out));

						$nd = $row_time_out - $ndstart;
						$nd = $nd > 0 ? $nd : 0;
					}
				}

				$ot_converted = false;
				//compute OT
				if ($oot && $oot->num_rows() > 0) {
					// process					
					$break_with_out = 0;			
					foreach ($oot->result() as $ot) {
						//check if within cutoff
						if(strtotime($ot->datetime_from) >= strtotime($shift_datetime_start) && strtotime($ot->datetime_from) < strtotime($shift_datetime_end)){
							if($this->config->item('allow_ot_on_undertime_blanket') == 1){
								//check if there's OUT
								$out = $get_form($employee_id, 'out', $p, $cdate);
								if ($out->num_rows() > 0) {
									$out = $out->row();
									$out_start = $out->date.' '.$out->time_start;
									$out_end = $out->date.' '.$out->time_end;
									// Get actual time worked w/in the OT application.								
									if (strtotime($dtr_out) >= strtotime($ot->datetime_to)) {
										$otend = $ot->datetime_to;
									} else {
										$otend = $dtr_out;
									}

									if(strtotime($out_start) >= strtotime($ot->datetime_from)){
										$otstart = $out_start;
									}
									else{
										$otstart = $ot->datetime_from;
									}

									$noon_break_start = $cdate.' '.$workshift->noon_start;
									$noon_break_end = $cdate.' '.$workshift->noon_end;
									//deduct 1 hour if OUT start within noon breaks
									if($break_with_out == 0 && strtotime($out_start) >= strtotime($noon_break_start) && strtotime($out_start) <= strtotime($noon_break_end)){
										$otend = date('Y-m-d H:i:s',strtotime($otend) - 3600);
										$break_with_out++;
									}
								}
								else{
									// Get actual time worked w/in the OT application.								
									if (strtotime($dtr_out) >= strtotime($ot->datetime_to)) {
										$otend = $ot->datetime_to;
									} else {
										$otend = $dtr_out;
									}

									if(strtotime($dtr_in) >= strtotime($ot->datetime_from)){
										$otstart = $dtr_in;
									}
									else{
										$otstart = $ot->datetime_from;
									}								
								}								
							}
							else{
								// Get actual time worked w/in the OT application.								
								if (strtotime($dtr_out) >= strtotime($ot->datetime_to)) {
									$otend = $ot->datetime_to;
								} else {
									$otend = $dtr_out;
								}

								if(strtotime($dtr_in) >= strtotime($ot->datetime_from)){
									$otstart = $dtr_in;
								}
								else{
									$otstart = $ot->datetime_from;
								}								
							}
						}else{
							// Get actual time worked w/in the OT application.								
							if (strtotime($dtr_out) >= strtotime($ot->datetime_to)) {
								$otend = $ot->datetime_to;
							} else {
								$otend = $dtr_out;
							}

							if(strtotime($dtr_in) >= strtotime($ot->datetime_from)){
								$otstart = $dtr_in;
							}
							else{
								$otstart = $ot->datetime_from;
							}
						}


/*						if( !$regular && $restday && date('D', strtotime($cdate)) != "Sun"){

							$time_in = new DateTime($dtr_in);
							$time_out = new DateTime($dtr_out);
							$diff = $time_in->diff($time_out);

							//if overtym exceeds 5 hours, deduct 1 hour for break
							if( $diff->h >= 5 ){
								$total_hours = $diff->h - 1;
							}
							else{
								$total_hours = $diff->h;
							}

							if( $total_hours >= $hours_worked ){
								$non_regular_eot = true;
								$hours_with_break = $hours_worked + 1;
								$otstart = date('Y-m-d H:i:s', strtotime ( '+'.$hours_with_break.' hours' , strtotime ( $dtr_in ) ));
							}	
						}*/

						$ot_application = ((strtotime($otend) - strtotime($otstart)));

						if (($ot_application /60) < ($workshift->minimum_min_allow_ot)) { // if less than minimum OT set ot application = 0
							$ot_application = 0;
						}
						
						$overtime += $ot_application;

						// $overtime += ((strtotime($otend) - strtotime($otstart)));

						$otbreak_deducted = false;
						if ( $restday || $holiday ) {
							// temporary comment cause for ticket 1803 - Tirso
/*							$actual_ot = strtotime($dtr_out) - strtotime($dtr_in);
							$app_ot = strtotime($ot->datetime_to) - strtotime($ot->datetime_from);

							if($actual_ot <= $app_ot){
								$overtime = $actual_ot;
								$otstart = $dtr_in;
								$otend = $dtr_out;
							}
							else{
								$overtime = $app_ot;
								$otstart = $ot->datetime_from;
								$otend = $ot->datetime_to;
							}*/

							// Subtract 30 mins if total is greater than 4
							if( ($overtime/60/60) > 4 && floor((strtotime($ot->datetime_to) - strtotime($ot->datetime_from)) / 60 / 60 / 4) * 60 > 1 ){
								$ot_checkpoint = floor((strtotime($ot->datetime_to) - strtotime($ot->datetime_from)) / 60 / 60 / 8);

								$ot_decimal = $overtime/60/60/8 - $ot_checkpoint;
								$overtime -= ( ( 60 * 60 ) * $ot_checkpoint );

								if ($ot_decimal > .5){
									$overtime -= ( 30 * 60 );
								}
								$otbreak_deducted = true;
							}
							/*
							// Subtract one hour if total is greater than 8
							if (($overtime/60/60) >= 5 && floor((strtotime($ot->datetime_to) - strtotime($ot->datetime_from)) / 60 / 60 / 5) * 60 > 1) {
								$overtime -= $break;
								$otbreak_deducted = true;
							}
							*/

							if($overtime > 0){
								$mins = $overtime / 60;
								if ($mins > 14){
									$base = $mins / 15;
									$base = floor( $base );
									$base = $base * 15;

									if( $mins % 15 > 15 )
									{
										$base += 15;
									} 
									
									$overtime = $base * 60;
									$ot_converted = true;
								}
								else{
									$overtime = 0;
								}
							}

							//compute ot excess
							if(($overtime / 60 / 60) > $hours_worked){
								$ot_excess = ($overtime / 60 / 60) - $hours_worked;
								//get the start of ot excess
								$ot_excess_start = strtotime($otstart) + ( $hours_worked*60*60 );	
								if( $otbreak_deducted ){
									$ot_excess_start += $break;	
								}
								$ot_excess = (($overtime / 60 / 60) - $hours_worked ) * 60 * 60;
							}
						}

						//compute ndot
						//check OT should fall during ND hour
						if( 
							(strtotime($otstart) >= $ndstart && strtotime($otstart) <= $ndend)
							||
							(strtotime($otend) >= $ndstart && strtotime($otend) <= $ndend)
							||
							($ndstart >= strtotime($otstart) && $ndstart <= strtotime($otend)) 
							|| 
							($ndend >= strtotime($otstart) && $ndend <= strtotime($otend))
							){
							//compute start and end of ND
							//default
							$otndshift_start = strtotime($otstart) >= $ndstart ? strtotime($otstart) : $ndstart;
							$otndshift_end = strtotime($otend) <= $ndend ? strtotime($otend) : $ndend;

							$ndot += ($otndshift_end - $otndshift_start);

							//prevent errors, may happen if OT/DTRP error in filling
							if($ndot < 0) $ndot = 0;

							//compute compute ndot excess
							if( $ot_excess > 0 &&
								(
									($ot_excess_start <= $ndstart && strtotime($otend) > $ndstart)

								||

									(($ot_excess_start > $ndstart && $ot_excess_start  < $ndend)
									&&
									(strtotime($otend) > $ndstart))
								)
							){
								$otnd_excess_start = $ot_excess_start >= $ndstart ? $ot_excess_start : $ndstart;
								$otnd_excess_end = strtotime($otend) <= $ndend ? strtotime($otend) : $ndend;
								$ndot_excess += ($otnd_excess_end - $otnd_excess_start);
								
								$ndot -= $ndot_excess;
								$ot_excess -= $ndot_excess;
								//$ot_excess -= $ndot;
							}
						}			
					}

					if(!$ot_converted && $overtime > 0){
						$mins = $overtime / 60;
						if ($mins > 14){
							$base = $mins / 15;
							$base = floor( $base );
							$base = $base * 15;

							if( $mins % 15 > 15 )
							{
								$base += 15;
							} 
							
							$overtime = $base * 60;
							$ot_converted = true;
						}
						else{
							$overtime = 0;
						}
					}

					if($ndot > 0){
						$mins = $ndot / 60;
						if ($mins > 14){
							$base = $mins / 15;
							$base = floor( $base );
							$base = $base * 15;

							if( $mins % 15 > 15 )
							{
								$base += 15;
							} 
							
							$ndot = $base * 60;
						}
						else{
							$ndot = 0;
						}
					}

					if($ot_excess > 0){
						$mins = $ot_excess / 60;
						if ($mins > 14){
							$base = $mins / 15;
							$base = floor( $base );
							$base = $base * 15;

							if( $mins % 15 > 15 )
							{
								$base += 15;
							} 
							
							$ot_excess = $base * 60;
						}
						else{
							$ot_excess = 0;
						}
					}

					//subtract ndot excess from regular ot
					$overtime -= $ot_excess;

					//subtract ndot from regular ot
					$overtime -= $ndot;

					//subtract ndot excess from regular ot
					$overtime -= $ndot_excess;	

					//prevent negative overtime, issue may arise if has OT filed but timein timeout error
					if($overtime < 0) $overtime = 0;

					// return real nd
					$ndstart = strtotime(date('Y-m-d 22:00:00', $tstamp_cdate));
					$ndend   = strtotime(date('Y-m-d 06:00:00', strtotime($tomorrow)));
				}
				else if ($holiday && $workshift->holiday_override){
					//if holiday_override then overtime automatically approved even without filling.
					$otstart = $dtr_in;
					$otend = $dtr_out;

					$overtime = (strtotime($otend) - strtotime($otstart));

					// Subtract one hour if total is greater than 8
					if (($overtime/60/60) >= 5 && floor((strtotime($otend) - strtotime($otstart)) / 60 / 60 / 5) * 60 > 1) {
						$overtime -= (60 * 60);
					}

					if($overtime > 0){
						$mins = $overtime / 60;
						if ($mins > 14){
							$base = $mins / 15;
							$base = floor( $base );
							$base = $base * 15;

							if( $mins % 15 > 15 )
							{
								$base += 15;
							} 
							
							$overtime = $base * 60;
						}
						else{
							$overtime = 0;
						}
					}										
				}

				if( $day_shift_id != 1 ){
					if ($actual_hours) {
						$s_start = strtotime($dtr_in);
						$s_end   = strtotime($dtr_out);	
						$total_actual_hours = ($s_end - $s_start - $break);
					}

						$s_start = strtotime($shift_start);
						$s_end   = strtotime($shift_end);	
	

					if ($s_end < $s_start) {
						$s_end = strtotime(date('Y-m-02 H:i:s', $s_end));
					} else {
						$s_end = strtotime(date('Y-m-01 H:i:s', $s_end));
					}

					$s_start = strtotime(date('Y-m-01 H:i:s', $s_start));

					$shift_cache[$cdate]['start'] = $cdate . ' ' . date('H:i:s', $s_start);
					$shift_cache[$cdate]['end'] = $cdate . ' ' . date('H:i:s', $s_end);

					$minutes_worked = ($s_end - $s_start - $break);
					
					
				}



				$grace_period = ($grace_period_emp !== 0 ? date('i', strtotime($grace_period_emp)) : date('i', strtotime($r->shift_grace_period)));
				$grace_period_s = ($grace_period_emp !== 0 ? hoursToSeconds($grace_period_emp) : hoursToSeconds($r->shift_grace_period));
				$shift_cache[$cdate]['grace_period'] = $grace_period;
				$shift_cache[$cdate]['grace_period_s'] = $grace_period_s;

				// this is already set at the beginning
				// $shift_datetime_start = $cdate . ' ' . $shift_start;	

				if(($workshift->upto_flexi_minutes != 0 || $workshift->upto_flexi_minutes != "") && $this->hdicore->is_flexi($employee_id)) {

					$upto_flexi_minutes = date('Y-m-d H:i:s', strtotime('+' . $workshift->upto_flexi_minutes . ' minutes', strtotime($shift_datetime_start)));

					if($dtr_in > $upto_flexi_minutes) {
						$shift_datetime_start = $upto_flexi_minutes;
						$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+' . $workshift->upto_flexi_minutes . ' minutes', strtotime($shift_datetime_end)));
					} else {
						if($dtr_in >= $shift_datetime_start) {
							$minutes_difference = round(abs(strtotime(date('Y-m-d H:i', strtotime($dtr_in))) - strtotime(date('Y-m-d H:i', strtotime($shift_datetime_start)))) / 60);
							$shift_datetime_start = date('Y-m-d H:i:s', strtotime('+' . $minutes_difference . ' minutes', strtotime($shift_datetime_start)));
							$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+' . $minutes_difference . ' minutes', strtotime($shift_datetime_end)));
						}
					}
				}

				// Check late
				if(strtotime($dtr_in) > 
					strtotime('+' . $grace_period_s . ' seconds', strtotime($shift_datetime_start))){
						$lates = (strtotime($dtr_in) - strtotime($shift_datetime_start));

					// temporary comment by tirso
					 
/*					if(!$this->config->item('fixed_minutes')) {
						$lates = (strtotime($dtr_in) - strtotime($shift_datetime_start));
					} else {

						$mins_actual = date('i', strtotime($dtr_in));
						$mins = ($mins_actual > 0 ? $mins_actual - ($grace_period_s / 60) : 00);
						$mins = ($mins < 1 ? 0 : $mins );
						$add_h = 0;
						$ss = 00;

						$y = date('Y', strtotime($dtr_in));
						$m = date('m', strtotime($dtr_in));
						$d = date('d', strtotime($dtr_in));
						$h = date('H', strtotime($dtr_in));
						$i = date('i', strtotime($dtr_in));
						$s = date('s', strtotime($dtr_in));

						// create table for this.
						if($mins_actual >= 11 && $mins_actual <= 15) {
							$mins = 15;
						} else if($mins_actual >= 16 && $mins_actual <= 30) {
							$mins = 30;
						} else if($mins_actual >= 31 && $mins_actual <= 45) {
							$mins = 45;
						} else if($mins_actual >= 46 && $mins_actual <= 59) {
							$add_h = 1;
							$mins = 00;
						}

						$dtr_in = date('Y-m-d H:i:s', strtotime($y.'-'.$m.'-'.$d.' '.$h.':'.$mins.':'.$ss));
						$dtr_in = date('Y-m-d H:i:s', strtotime('+ '.$add_h.' hours', strtotime($dtr_in)));

						$lates = (strtotime($dtr_in) - strtotime($shift_datetime_start));
					}*/
				}

				//check if need to remove break from lates
				if( strtotime($dtr_in) >= strtotime(date('Y-m-d ' .$r->noon_end, strtotime($dtr_out)))){
					$lates -= $break;
				}else if( strtotime($dtr_in) > strtotime(date('Y-m-d ' .$r->noon_start, strtotime($dtr_out))) ){
					$lates -= strtotime($dtr_in) - strtotime(date('Y-m-d ' .$r->noon_start, strtotime($dtr_out)));	
				}

				// Check UT
				if (strtotime(date('Y-m-d H:i:s', strtotime($dtr_out))) < strtotime($shift_datetime_end)) {
					//check that dtrout is after shift start
					if( strtotime(date('Y-m-d H:i:s', strtotime($dtr_out))) <= strtotime($shift_datetime_start) ){
						$undertime = strtotime($shift_datetime_end) - strtotime($shift_datetime_start);
						$undertime -= $break;
					}
					else{
						$undertime = (strtotime($shift_datetime_end) - strtotime(date('Y-m-d H:i:s', strtotime($dtr_out))));
						if ( (strtotime($dtr_out) >= strtotime(date('Y-m-d ' .$r->noon_start, strtotime($dtr_out))) ) && (strtotime($dtr_out) < strtotime(date('Y-m-d ' .$r->noon_end, strtotime($dtr_out)))) ) {
							$undertime -= strtotime(date('Y-m-d ' .$r->noon_end, strtotime($dtr_out))) - strtotime($dtr_out);
						}
						else if( strtotime($dtr_out) <= strtotime(date('Y-m-d ' .$r->noon_start, strtotime($dtr_out)) )){
						$undertime -= $break;
						}
					}
				}

				if( ($undertime / 60 / 60) >= $hours_worked ){
					$undertime = $hours_worked * 60 * 60;
					$lates = 0;
				}

				if ($this->config->item('remove_undertime_if_out')) {
					// Check OUT
					$out = $get_form($employee_id, 'out', $p, $cdate);
					if ($out->num_rows() > 0) {
						$out_num_rows = $out->num_rows();
						$out = $out->row();
						if( !empty($out->outblanket_id) ){
							if (strtotime($dtr_out) < strtotime(date('Y-m-d ' .$out->time_start, strtotime($dtr_out)))) {
								$undertime = (strtotime($out->time_start) - strtotime(date('H:i:s', strtotime($dtr_out))));
							} else {
								$undertime = 0; 
							}
						}
					}
				}
			
				// for openaccess flexible employee.
				if($this->hdicore->is_flexi($employee_id) && $day_shift_id != 1 && !$holiday && $this->config->item('with_flexi'))
				{
					if($this->hdicore->use_double_shift($employee_id, $cdate)) {
						$undertime = 0;
						$lates = 0;
						$minutes_worked = 8*60*60;
					} else {
						$ndstart = strtotime(date('Y-m-d 22:00:00', $tstamp_cdate));
						$ndend   = strtotime(date('Y-m-d 06:00:00', strtotime($tomorrow)));
						
						// $this->_save_double_shift($employee_id, $cdate, $dtr_in, $dtr_out);
						$flexi_shift = $this->_flexi_employee($employee_id,$dtr_in,$dtr_out,$cdate, $tomorrow, $ndstart, $ndend);
						$double_shift_is_used = $flexi_shift['double_shift_is_used'];
						$minutes_worked = $flexi_shift['minutes_worked'];
						$undertime = $flexi_shift['undertime'];
						$nd = $flexi_shift['night_diff'];
						$lates = 0;
					}
				}

				// for department excluded on holiday
				if ($employee_department && in_array($employee_department, explode(',', $holiday[0]['excluded'])))
				{
					$no_oot = $overtime == 0;
					// end of ndot computation
					$ndstart = strtotime(date('Y-m-d 22:00:00', $tstamp_cdate));
					$ndend   = strtotime(date('Y-m-d 06:00:00', strtotime($tomorrow)));
					if($night_shift)
					{
						$ndshift_start = strtotime($dtr_in) >= $ndstart ? strtotime($dtr_in) : $ndstart;
						// $ndshift_end = strtotime($dtr_out) <= $ndend ? strtotime($dtr_out) : $ndend;
						if(strtotime($dtr_out) <= $ndend) {
							$ndshift_end = (strtotime($dtr_out) > strtotime($shift_datetime_end) ? strtotime($shift_datetime_end) : strtotime($dtr_out));
						} else {
							$ndshift_end = $ndend;
						}

						$nd = ($ndshift_end - $ndshift_start);

						if( strtotime($otstart) < $ndend && strtotime($otend) > $ndstart ){
							//compute start and end of ND
							//default
							$otndshift_start = strtotime($otstart) >= $ndstart ? strtotime($otstart) : $ndstart;
							$otndshift_end = strtotime($otend) <= $ndend ? strtotime($otend) : $ndend;

							$ndot += ($otndshift_end - $otndshift_start);

							//prevent errors, may happen if OT/DTRP error in filling
							if($ndot < 0) $ndot = 0;

							//compute compute ndot excess
							if( $ot_excess > 0 &&
								($ot_excess_start <= $ndstart && $ot_excess_start  < $ndend)
								&&
								(strtotime($otend) > $ndstart)
							){
								$otnd_excess_start = $ot_excess_start >= $ndstart ? $ot_excess_start : $ndstart;
								$otnd_excess_end = strtotime($otend) <= $ndend ? strtotime($otend) : $s;
								$ndot_excess += ($otnd_excess_end - $otnd_excess_start);
								
								$ndot -= $ndot_excess;
								$ot_excess -= $ndot_excess;
								$ot_excess -= $ndot;
							}
						}
					}
					// end of ndot computation

					$ot_in = (strtotime($shift_datetime_start) < strtotime($dtr_in) ? $dtr_in : $shift_datetime_start);				
					$ot_out = (strtotime($shift_datetime_end) > strtotime($dtr_out) ? $dtr_out : $shift_datetime_end);
					// dbug($ot_in.' '.$ot_out);

					$hourdiff = (strtotime($ot_out) - strtotime($ot_in));
					// dbug($hourdiff);

					if(($hourdiff / 60 / 60) >= $hours_with_break)
						$hourdiff = $hourdiff - (60 * 60);


					$overtime += $hourdiff;

					if($overtime > 0){
						$mins = $overtime / 60;
						if ($mins > 14){
							$base = $mins / 15;
							$base = floor( $base );
							$base = $base * 15;

							if( $mins % 15 > 15 )
							{
								$base += 15;
							} 
							
							$overtime = $base * 60;
						}
						else{
							$overtime = 0;
						}
					}

					if($shift_datetime_start < $dtr_in)
						$lates = (strtotime($shift_datetime_start) - strtotime($dtr_in)); // $lates = (8 *60 * 60) - $hourdiff;
					if($shift_datetime_end > $dtr_out)
						$undertime = (strtotime($shift_datetime_end) - strtotime($dtr_out));

					$by_pass_holiday = true;
				}

				// Check Leave
				$this->db->select('duration_id, credit, credit,application_form_id');
				$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
				$this->db->where('employee_id', $employee_id);
				$this->db->where('(\'' . $cdate . '\' BETWEEN date_from AND date_to)', '', false);
				$this->db->where('employee_leaves_dates.date', $cdate);
				$this->db->where('form_status_id', 3);
				$this->db->where('employee_leaves_dates.deleted', 0);
				$this->db->where('employee_leaves.deleted', 0);
				
				if( !$for_late_filing ){
					//always check forms for current period
					$this->db->where('(DATE_FORMAT(date_approved, "%Y-%m-%d") <= \'' . $p->cutoff .'\')', '', false);	
				}

				$leave = $this->db->get('employee_leaves');

				$new_timeout = '';
				if ($leave->num_rows() > 0) {
					foreach($leave->result() as $lv){
						$adjust_minutes_worked = false;
						if ($lv->duration_id == 3) {
							if(strtotime($dtr_in) > 
								strtotime('+' . $grace_period_s . ' seconds', strtotime($shift_datetime_start))){
									$lates = (strtotime(date('H:i',strtotime($dtr_in))) - strtotime(date('H:i',strtotime($shift_datetime_start))));
								}
							if (strtotime(date('H:i', strtotime($dtr_out))) < strtotime($r->noon_start)) {
								$undertime = strtotime($r->noon_start) - strtotime(date('H:i', strtotime($dtr_out)));
							}
							else{
								$undertime = 0;
							}				
						} elseif ($lv->duration_id == 2) { 
							if(strtotime(date('H:i', strtotime($dtr_in))) > strtotime($r->noon_end)){
								$lates = (strtotime(date('H:i', strtotime($dtr_in)))) - (strtotime($r->noon_end));
							}
							else{
								$lates = 0;
							}
							if (strtotime(date('Y-m-d H:i:s', strtotime($dtr_out))) < strtotime($shift_datetime_end)) {
								$undertime = strtotime($shift_datetime_end) - strtotime(date('Y-m-d H:i', strtotime($dtr_out)));
							}
						} else if($lv->duration_id == 1){ //files for wholeday leave then leaves office
							$nd = 0;
							$undertime = 0;	
							$lates = 0;
						} else if(($lv->duration_id >= 4 || $lv->duration_id <= 6) && CLIENT_DIR == 'oams') { // for openaccess, duration id = 4 is 1 hour undertime

							if($lv->application_form_id == 7 || $lv->application_form_id == 5) {
								$adjust_minutes_worked = true;
								$adjusted_minutes = $minutes_worked - $undertime;
							}

							$in_hours = $lv->credit * $hours_leave_worked;
							if($new_timeout == '')
								$new_timeout = date('Y-m-d H:i:s', strtotime('+ '.$in_hours.' hour '.$dtr_out));
							else 
								$new_timeout = date('Y-m-d H:i:s', strtotime('+ '.$in_hours.' hour '.date('Y-m-d H:i:s', strtotime($new_timeout))));

							if(strtotime($new_timeout) < strtotime($shift_datetime_end))
								$undertime = (strtotime($shift_datetime_end) - strtotime($new_timeout));
							else
								$undertime = 0;

						} 
						
						$leave_hours = $lv->credit * $hours_leave_worked;
						switch($lv->application_form_id){
							case 5:
							case 7:
								$dailysummary['lwop'] += $lv->credit * $hours_leave_worked;
								$summary_update['lwop'] += $lv->credit * $hours_leave_worked;
								$minutes_worked -= $lv->credit * 60 * 60 * $hours_leave_worked;
								break;
							default:
								$minutes_worked += $lv->credit * 60 * 60 * $hours_leave_worked;
								$dailysummary['lwp'] += $lv->credit * $hours_leave_worked;
								$summary_update['lwp'] += $lv->credit * $hours_leave_worked;
								break;		
						}

						if($employee_department && in_array($employee_department, explode(',', $holiday[0]['excluded'])) && $lv->application_form_id != 5 && $lv->application_form_id != 7) {
							$overtime += $lv->credit * 60 * 60 * $hours_leave_worked;

							if($no_oot && $overtime > $hours_worked)
								$overtime = $hours_worked * 60 * 60;
						}

						if($adjust_minutes_worked)
							$minutes_worked = $adjusted_minutes;
					}
				}

				// Get excused tardiness.
				$et = $get_form($employee_id, 'et', $p, $cdate);
				$et_num_rows = $et->num_rows();
				if (!$supervisor && $et->num_rows() == 0 && 
					strtotime(date('H:i:s', strtotime($dtr_in))) > 
					strtotime('+' . $grace_period_s . ' seconds', strtotime($shift_start))) {	

					if ($p->apply_lates == 1 && 
						(strtotime($p->apply_late_from) >= $tstamp_cdate || $tstamp_cdate <= strtotime($p->apply_late_to))
						) {
						if(!$for_late_filing) {
							$infraction['date'][] = $cdate;
							$infraction['time'][] = $dtr_in;
							$infraction['min_lates'][] = $lates;
						}
					}
				}

				// Get excused tardiness.
				$et = $get_form($employee_id, 'et', $p, $cdate);
				$with_et = false;
				$et_blanket = 0;
				//get employee's excused tardiness
				if($et->num_rows() > 0){
					foreach ($et->result() as $entry) {					
						if( empty($entry->etblanket_id) ){
							if( $excused_tardiness == 0 
								|| $entry->time_difference > $excused_tardiness
								) {
								$excused_tardiness = $entry->time_difference;
								$with_et = true;
							}
						}
						else{
							$et_blanket = $entry->time_difference;
						}
					}
				}

				// Get official undertime
				$out = $get_form($employee_id, 'out', $p, $cdate);
				$with_out = false;
				//get employee's official undertime
				if($out->num_rows() > 0){
					foreach ($out->result() as $entry) {
						$out_time_difference = (strtotime($entry->time_end) - strtotime($entry->time_start));						
						if( empty($entry->outblanket_id) ){
							if( $approved_undertime == 0 
								|| $out_time_difference > $approved_undertime
								) {
								$approved_undertime = $out_time_difference;
								$with_out = true;
							}
						}
					}
				}

				//comment this group of code for the sake of ticket 1690
/*				if ($day_prefix != 'reg') {
					if ($restday) {
						if ($overtime > 0) {
							$minutes_worked = $overtime - $lates;
						} else {
							$minutes_worked = 0; 
						}
					}
				}*/


				if ( ($minutes_worked / 60 /60 ) > $hours_worked) {
					$minutes_worked = 60 * $hours_worked * 60;
				}
				
				if(!$by_pass_holiday) {
					if($day_prefix == 'leg' || ($day_prefix == 'spe' && $regular)) {
						$undertime = $lates = 0;					
						$minutes_worked = 60 * $hours_worked * 60;
					}
				}
//Commented to compute OT on Rest days regardless of employment status (ticket #764)
				// if ($restday) {
				// 	if( !$regular  ){
				// 		if(date('D', strtotime($cdate)) != "Sun")
				// 		{
				// 			if( $non_regular_eot ){
				// 				$minutes_worked = 480 * 60;
				// 			}
				// 			else{

				// 				$time_in = new DateTime($dtr_in);
				// 				$time_out = new DateTime($dtr_out);
				// 				$diff = $time_in->diff($time_out);

				// 				$sh_start = strtotime ( $dtr_in );
				// 				$sh_end = strtotime ( '+'.$diff->h.' hours' , strtotime ( $dtr_in ) );

				// 				if( $diff->h >= 5 ){

				// 					$overtime = 0;

				// 					if( $diff->h > $hours_worked ){
				// 						$minutes_worked = $hours_worked * 60 * 60;
				// 					}
				// 					else{
				// 						$minutes_worked = ($sh_end - $sh_start - 3600);
				// 					}
				// 				}
				// 				else{
				// 					$overtime = 0;
				// 					$minutes_worked = ($sh_end - $sh_start);
				// 				}
				// 			}
				// 		} else{
				// 			$minutes_worked = 0;
				// 		}

				// 	}
				// 	else{
				// 		$minutes_worked = 0;
				// 	}
				// }

				if ($overtime < 0) {$overtime = 0;}

				//For regular employees, if obt was present, during holiday or restday
				if( ( ( $obt->num_rows() > 0 ) && ( $holiday || $restday ) ) && $regular ){
					$minutes_worked = 0;
				}			

				//check el blanket
				$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leave_blanket WHERE FIND_IN_SET('".$employee_id."',employee_id) AND '".$cdate."' BETWEEN date_from AND date_to AND deleted = 0; ");
				if ($result && $result->num_rows() > 0){
					$minutes_worked = $hours_worked*60*60;					
					$absent = false;				
				}
				
				//to avoid negative lates
				if($lates < 0) $lates=0;
			}

			// check return date of base-off cdate must be tuesday. to make sure that there is attendance on monday.
			// $this->_base_off();

		} else { // No time record entry

			$holiday_to_pay = true;

			//to check if holiday then check previous and next working days is absent - tirso
			if ($this->config->item('after_holiday')){
				if ($holiday){

					$prev_working_days = $this->system->get_prev_working_day($employee_id,$cdate);
					$next_working_days = $this->system->get_next_working_day($employee_id,$cdate);

					$dtr_prev_working_days = get_employee_dtr_from($employee_id, $prev_working_days);
					$dtr_next_working_days = get_employee_dtr_from($employee_id, $next_working_days);
					
					if ($dtr_prev_working_days && $dtr_prev_working_days->num_rows() > 0){
						
						if ($dtr_prev_working_days->row()->time_in1 == "" || $dtr_prev_working_days->row()->time_in1 == "0000-00-00 00:00:00" || $dtr_prev_working_days->row()->time_out1 == "" || $dtr_prev_working_days->row()->time_out1 == "0000-00-00 00:00:00"){

							$obt_prev_working_days = $get_form($employee_id, 'obt', $p, $prev_working_days, true, $for_late_filing, true);
							$dtrp_prev_working_days = $get_form($employee_id, 'dtrp', $p, $prev_working_days, true, $for_late_filing, true);

							// Check leave for whole day previous working days
							$this->db->select('duration_id,employee_leaves_dates.employee_leave_date_id, employee_leaves_dates.credit, employee_leaves_dates.employee_leave_date_id, employee_leaves.application_form_id, employee_form_type.application_code');
							$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');

							$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
							$this->db->where('employee_id', $employee_id);
							$this->db->where('(\'' . $prev_working_days . '\' BETWEEN date_from AND date_to)', '', false);
							$this->db->where('employee_leaves_dates.date', $prev_working_days);
							$this->db->where('form_status_id', 3);
							$this->db->where('employee_leaves_dates.deleted', 0);
							$this->db->where('employee_leaves.deleted', 0);

							if( !$for_late_filing ){
								//always check forms for current period
								$this->db->where('(DATE_FORMAT(date_approved, "%Y-%m-%d") <= \'' . $p->cutoff .'\')', '', false);	
							}

							$leave_prev_working_days = $this->db->get('employee_leaves');

							if ($obt_prev_working_days->num_rows() == 0 && $dtrp_prev_working_days->num_rows() == 0 && $leave_prev_working_days->num_rows() == 0){
								$holiday = false;
								$holiday_to_pay = false;
								
							}									
						}
					}
					else{
						if ($dtr_prev_working_days->row()->time_in1 == "" || $dtr_prev_working_days->row()->time_in1 == "0000-00-00 00:00:00" || $dtr_prev_working_days->row()->time_out1 == "" || $dtr_prev_working_days->row()->time_out1 == "0000-00-00 00:00:00"){
							$obt_prev_working_days = $get_form($employee_id, 'obt', $p, $prev_working_days, true, $for_late_filing, true);
							$dtrp_prev_working_days = $get_form($employee_id, 'dtrp', $p, $prev_working_days, true, $for_late_filing, true);

							// Check leave for whole day previous working days
							$this->db->select('duration_id,employee_leaves_dates.employee_leave_date_id, employee_leaves_dates.credit, employee_leaves_dates.employee_leave_date_id, employee_leaves.application_form_id, employee_form_type.application_code');
							$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');

							$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
							$this->db->where('employee_id', $employee_id);
							$this->db->where('(\'' . $prev_working_days . '\' BETWEEN date_from AND date_to)', '', false);
							$this->db->where('employee_leaves_dates.date', $prev_working_days);
							$this->db->where('form_status_id', 3);
							$this->db->where('employee_leaves_dates.deleted', 0);
							$this->db->where('employee_leaves.deleted', 0);

							if( !$for_late_filing ){
								//always check forms for current period
								$this->db->where('(DATE_FORMAT(date_approved, "%Y-%m-%d") <= \'' . $p->cutoff .'\')', '', false);	
							}

							$leave_prev_working_days = $this->db->get('employee_leaves');

							if ($obt_prev_working_days->num_rows() == 0 && $dtrp_prev_working_days->num_rows() == 0 && $leave_prev_working_days->num_rows() == 0){
								$holiday = false;
								$holiday_to_pay = false;
							}									
						}						
					}

/*					if ($dtr_next_working_days && $dtr_next_working_days->num_rows() > 0){
						if ($dtr_next_working_days->row()->time_in1 == "" || $dtr_next_working_days->row()->time_out1 == ""){
							$obt_next_working_days = $get_form($employee_id, 'obt', $p, $next_working_days, true, $for_late_filing, true);
							$dtrp_next_working_days = $get_form($employee_id, 'dtrp', $p, $next_working_days, true, $for_late_filing, true);

							// Check leave for whole day next working days
							$this->db->select('duration_id,employee_leaves_dates.employee_leave_date_id, employee_leaves_dates.credit, employee_leaves_dates.employee_leave_date_id, employee_leaves.application_form_id, employee_form_type.application_code');
							$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');

							$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
							$this->db->where('employee_id', $employee_id);
							$this->db->where('(\'' . $next_working_days . '\' BETWEEN date_from AND date_to)', '', false);
							$this->db->where('employee_leaves_dates.date', $cdate);
							$this->db->where('form_status_id', 3);
							$this->db->where('employee_leaves_dates.deleted', 0);
							$this->db->where('employee_leaves.deleted', 0);

							if( !$for_late_filing ){
								//always check forms for current period
								$this->db->where('(DATE_FORMAT(date_approved, "%Y-%m-%d") <= \'' . $p->cutoff .'\')', '', false);	
							}

							$leave_next_working_days = $this->db->get('employee_leaves');							

							if ($obt_next_working_days->num_rows() == 0 || $dtrp_next_working_days == 0 || $leave_next_working_days == 0){
								$holiday = false;
								$holiday_to_pay = false;
							}							
						}
					}*/
				}
			}
			//to check if holiday then check previous and next working days is absent

			//if after obt and dtrp still incomplete timein timeout, mark as absent unless a holiday or restday
			// $absent = $holiday || $restday ? false : true;

			//tirso - holiday but no pay since absent either before or after of holiday date. to avoid awol.
			// if (!$holiday_to_pay){
			// 	$absent = false;
			// }

			// Check leave for whole day
			$this->db->select('duration_id,employee_leaves_dates.employee_leave_date_id, employee_leaves_dates.credit, employee_leaves_dates.employee_leave_date_id, employee_leaves.application_form_id, employee_form_type.application_code');
			$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');

			$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
			$this->db->where('employee_id', $employee_id);
			$this->db->where('(\'' . $cdate . '\' BETWEEN date_from AND date_to)', '', false);
			$this->db->where('employee_leaves_dates.date', $cdate);
			$this->db->where('form_status_id', 3);
			$this->db->where('employee_leaves_dates.deleted', 0);
			$this->db->where('employee_leaves.deleted', 0);

			if( !$for_late_filing ){
				//always check forms for current period
				$this->db->where('(DATE_FORMAT(date_approved, "%Y-%m-%d") <= \'' . $p->cutoff .'\')', '', false);	
			}

			$leave = $this->db->get('employee_leaves');

			if ($leave->num_rows() > 0) {
				$absent = false;
				$undertime = $hours_worked * 60 * 60; //initialize full undertime, in case leave is half
				foreach( $leave->result() as $lv ){

					// if department is excluded need to 
					if($employee_department && in_array($employee_department, explode(',', $holiday[0]['excluded'])))
					{
						if($lv->application_form_id != 5 && $lv->application_form_id != 7)
						{
							$overtime = $lv->credit * $hours_leave_worked * 60 * 60;

							if($overtime > 0){
								$mins = $overtime / 60;
								if ($mins > 14){
									$base = $mins / 15;
									$base = floor( $base );
									$base = $base * 15;

									if( $mins % 15 > 15 )
									{
										$base += 15;
									} 
									
									$overtime = $base * 60;
								}
								else{
									$overtime = 0;
								}
							}

							$dailysummary['lwp'] += $lv->credit * $hours_leave_worked;
							$summary_update['lwp'] += $lv->credit * $hours_leave_worked;
						}

					} else if ($holiday) {
						if($lv->application_form_id == 5 || $lv->application_form_id == 6) $lv->application_code = 'MPL';

						// Return leave credit.
						if($lv->application_code != 'LWOP'){
							$this->db->set(strtolower($lv->application_code) . '_used' , strtolower($lv->application_code) . '_used - ' . $lv->credit, false);
							$this->db->where('employee_id', $employee_id);
							$this->db->where('year', date('Y'));

							$this->db->update('employee_leave_balance');
						}
						// Remove credit
						$this->db->where('employee_leave_date_id', $lv->employee_leave_date_id);
						$this->db->update('employee_leaves_dates', array('credit' => 0));
					} 

					switch($lv->application_form_id){
						case 5:
						case 7:
							$dailysummary['lwop'] += $lv->credit * $hours_leave_worked;
							$summary_update['lwop'] += $lv->credit * $hours_leave_worked;
							break;
						default:
							$minutes_worked += $lv->credit * 60 * 60 * $hours_leave_worked;
							$dailysummary['lwp'] += $lv->credit * $hours_leave_worked;
							$summary_update['lwp'] += $lv->credit * $hours_leave_worked;
							break;		
					}

					$undertime -= $lv->credit * 60 * 60 * $hours_leave_worked;

					if ($holiday) {
						$undertime = 0;
					}

					if($supervisor && $lv->application_form_id == 7){
						$explicit_app = true;
					}
				}
			} else if ($holiday) {
				$minutes_worked = $hours_worked*60*60;
			}

			// if employee's department is excluded on that holiday they need to file 
			if($holiday && $leave->num_rows() ==  0 && $employee_department &&  in_array($employee_department, explode(',', $holiday[0]['excluded']))) {
				$minutes_worked = 0;
				$absent = true;
				$holiday = false;
			} else if($holiday && $leave->num_rows() >  0 && $employee_department &&  in_array($employee_department, explode(',', $holiday[0]['excluded']))) {
				$minutes_worked = $hours_worked * 60 * 60;

			} 

			//check el blanket
			$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leave_blanket WHERE FIND_IN_SET('".$employee_id."',employee_id) AND '".$cdate."' BETWEEN date_from AND date_to AND deleted = 0; ");
			if ($result && $result->num_rows() > 0){
				$minutes_worked = $hours_worked*60*60;					
				$absent = false;
			}

			// check return date of base-off cdate must be tuesday. to make sure that there is attendance on monday.
			// $this->_base_off();
		} // End DTR check	

		if ($et_blanket > 0 && $lates > 0){
			$lates = $lates - ($et_blanket * 60 * 60);
		}

		// for flexi employee with double shift.
		if($this->config->item('allow_ds') == 1 && !$holiday && !$restday && $absent)
		{
			if($this->hdicore->use_double_shift($employee_id, $cdate))
			{
				$minutes_worked = $hours_worked * 60 * 60;
				$absent = false;
			}
		}

		if($absent && !$restday && !$holiday){
			if( !$supervisor ){
				if (!$schedule->considered_halfday){
					$dailysummary['absent'] = 1;
					$summary_update['absences'] += 1;
					$minutes_worked = 0;
					if(!$for_late_filing){
						$otfile_row['current'][] = array($employee_no, $otfile_date, '', '', 'AWL', $hours_worked.'.00' );
					}
				}
			}
			else{
				if( isset($explicit_app) ){
					$dailysummary['absent'] = 1;
					$summary_update['absences'] += 1;
					$minutes_worked = 0;	
					if(!$for_late_filing){
						$otfile_row['current'][] = array($employee_no, $otfile_date, '', '', 'AWL', $hours_worked.'.00' );
					}
					unset($explicit_app);
				}
				else{
					//for supervisor and up even no in and out it will automatically computed as 8 hours
					$minutes_worked = $hours_worked*60*60;		
				}		
			}
		}

		if ($schedule->considered_halfday){
			$minutes_worked = 0;
			$dailysummary['absent'] = 0;
			$absent = false;
			$lates = 0;
			$undertime = 0;			
		}

		if( !$regular && ( $day_prefix == "spe" ) && ( $overtime == 0 ) ){
			$lates = 0;
			$undertime = 0;
			$minutes_worked = 0;
		}

		//check half day
		if($halfday_minutes){
			if( !$absent && ( ( $lates > $halfday_minutes ) && ( $lates <= 14400  ) ) ){
				$minutes_worked = 14400 ;
				$lates = 0;
			}
		}

		//check half day for undertime : tirso
		if($halfday_minutes_undertime > 0){
			if( !$absent && ( ( $undertime >= $halfday_minutes_undertime ) && ( $undertime <= 14400  ) ) ){
				$minutes_worked = 14400 ; 
				$undertime = 0;
			}
		}

		//modify by mauricio and harold
		//OCLP - client used fixed minutes for ET/AU
		if($this->config->item('fixed_minutes') ) {
			if($excused_tardiness > 0){
				$mins = $excused_tardiness * 60;
				if ($mins > 60){
					$base = $mins / 15;
					$base = floor( $base );
					$base = $base * 15;

					if( $mins % 15 != 0 )
					{
						$base += 15;
					} 
					
					$fixed_excused_tardiness = $base / 60;	
				}
				else{
					$fixed_excused_tardiness = 1;	
				}
			}
			if($approved_undertime > 0){
				$mins = $approved_undertime / 60;
				if ($mins > 60){
					$base = $mins / 15;
					$base = floor( $base );
					$base = $base * 15;

					if( $mins % 15 != 0 )
					{
						$base += 15;
					} 
					
					$fixed_approved_undertime = $base * 60;
				}else{					
					$fixed_approved_undertime = 3600; //one hour
				}	
			}
			if($undertime > 0){
				$mins = $undertime / 60;
				if ($mins > 60){
					$base = $mins / 15;
					$base = floor( $base );
					$base = $base * 15;

					if( $mins % 15 != 0 )
					{
						$base += 15;
					} 
					
					$undertime = $base * 60;	
				}else{
					$undertime = 3600; //one hour
				}
			}
		}
		// If late is less than an hour don't deduct
		if ( ($lates / 60) > $this->config->item('deduction_lates')) {
			$minutes_worked -= $lates;
		}
		else{
			//$lates = 0; //removed by Harold conflict in Payroll, do not deduct but reflect as is
		}
		

		$minutes_worked -= $undertime;

		//prevent negative worked hours and overtime
		if ($overtime < 0) {$overtime = 0;}
		if ($minutes_worked < 0) {$minutes_worked = 0;}
		if ($undertime < 0) {$undertime = 0;}
		if( ($lates / 60 / 60) > $hours_worked ) $lates = $hours_worked * 60 * 60; // happens if dtr us after shift sched
		if($restday || ($day_shift_id == 1)) $lates = 0;
		
			
/*		//modify by tirso
		if($this->config->item('fixed_minutes')) {
			$mins = date('i', strtotime($dtr_in));
			$add_h = 0;

			$y = date('Y', strtotime($dtr_in));
			$m = date('m', strtotime($dtr_in));
			$d = date('d', strtotime($dtr_in));
			$h = date('H', strtotime($dtr_in));
			$i = date('i', strtotime($dtr_in));
			$s = date('s', strtotime($dtr_in));

			// create table for this.
			if($mins >= 11 && $mins <= 15) {
				$mins = 15;
			} else if($mins >= 16 && $mins <= 30) {
				$mins = 30;
			} else if($mins >= 31 && $mins <= 45) {
				$mins = 45;
			} else if($mins >= 46 && $mins <= 59) {
				$add_h = 1;
				$mins = 00;
			}

			$dtr_in = date('Y-m-d H:i:s', strtotime($y.'-'.$m.'-'.$d.' '.$h.':'.$mins.':'.$s));
			$dtr_in = date('Y-m-d H:i:s', strtotime('+ '.$add_h.' hours', strtotime($dtr_in)));

			$fixed_lates = (strtotime($dtr_in) - strtotime($shift_datetime_start));
		}*/

		$undertime_infraction = ($undertime > 0 && !$supervisor && $out_num_rows == 0);
		$late_infraction = ($lates > 0 && !$supervisor && $et_num_rows == 0);

		//convert to hours and minutes
		$minutes_worked /= 60;
		$lates = $lates / 60 / 60;
		$fixed_lates = $fixed_lates / 60 / 60;
		$undertime = $undertime / 60 / 60;
		$overtime = $overtime / 60 / 60;
		$ot_excess = $ot_excess / 60 / 60;

		if($overtime > 8){
			$ot_excess += $overtime - 8;
			$overtime = 8;
		}

		$nd = $nd / 60 / 60;
		$ndot = $ndot / 60 / 60;
		$ndot_excess = $ndot_excess / 60 / 60;
		$total_overtime = $overtime + $ot_excess + $ndot + $ndot_excess;	

		if($fixed_excused_tardiness > $lates){	
			$minutes_worked -= (($fixed_excused_tardiness-$lates) * 60);
		}

		$lates_display = (number_format($lates, 2, '.', '') - $excused_tardiness);
		$lates_display = ($lates_display > 0) ? $lates_display : 0;

		$fixed_computed_late = ($fixed_excused_tardiness + $lates_display) - number_format($lates, 2, '.', '');

		if($excused_tardiness < number_format($lates, 2, '.', '')){			
			$minutes_worked -= ($fixed_computed_late * 60);
		}

		$lates = $fixed_excused_tardiness + $lates_display;	

		$approved_undertime = $approved_undertime / 60 / 60;
		$fixed_approved_undertime = $fixed_approved_undertime / 60 / 60;

		if($fixed_approved_undertime > $undertime){
			$minutes_worked -= (($fixed_approved_undertime-$undertime) * 60);
		}

		if( $undertime  > $fixed_approved_undertime ) {
			$undertime_display = ($undertime - $fixed_approved_undertime);
		} else {
			$undertime_display = 0;	
		}

		$undertime_display = ($undertime_display > 0) ? $undertime_display : 0;

		$fixed_computed_undertime = ($fixed_approved_undertime + $lates_display) - $lates;
		// if($fixed_computed_undertime > 0){			
		// 	$minutes_worked -= ($fixed_computed_undertime * 60);
		// }
		$undertime = $fixed_approved_undertime + $undertime_display;

		//assigned computed fixed values
		$excused_tardiness = $fixed_excused_tardiness;
		$approved_undertime = $fixed_approved_undertime;

		//if employee is supervisor, no lates and undertime applied
		if($supervisor){
			$minutes_worked += ($undertime * 60);
			$minutes_worked += ($lates * 60);

			if(!$with_out){
				//added to remove undertime on hoursworked of supervisors if no application
				$approved_undertime = 0;
				$undertime = 0;
			}else{
				$minutes_worked -= ($fixed_approved_undertime * 60);
				$undertime_display = 0;
				$undertime = $fixed_approved_undertime;
			}

			// if(!$with_et){
				//added to remove lates on hoursworked of supervisors if no application
				$excused_tardiness = 0;
				$lates = 0;
				$lates_display = 0;
			// }else{
			// 	$minutes_worked -= ($fixed_excused_tardiness * 60);
			// 	$lates_display = 0;
			// 	$lates = $fixed_excused_tardiness;
			// }

			// if(!empty($dtr_in) && !empty($dtr_out)){
			// 	$time_in = new DateTime($dtr_in);
			// 	$time_out = new DateTime($dtr_out);
			// 	$diff = $time_in->diff($time_out);
			// 	$overtime = ($diff->h - ($break/60/60) - $hours_worked);
			// 	if ($overtime > 0){
			// 		$total_overtime = $overtime;
			// 	}
			// }
		}


		if ( ($total_actual_hours / 60 /60 ) > $hours_worked) {
			if ($actual_hours ) {
				$minutes_worked = $total_actual_hours / 60;
			}
		}

		

		$dailysummary['hours_worked'] = $minutes_worked > 0 ? $minutes_worked / 60 : 0;
		$dailysummary['lates'] = $lates;
		$dailysummary['undertime'] = $undertime;
		$dailysummary['ot'] = $overtime;
		$dailysummary['nd'] = $nd;
		$dailysummary['ndot'] = $ndot;
		$dailysummary['ot_excess'] = $ot_excess;
		$dailysummary['ndot_excess'] = $ndot_excess;

		// $dailysummary['excused_tardiness'] = $excused_tardiness;
		// $dailysummary['lates_display'] = $lates_display;
		// $dailysummary['approved_undertime'] = $approved_undertime;
		// $dailysummary['undertime_display'] = $undertime_display;
		
		if($for_late_filing){
			$this->db->insert('dtr_daily_summary_lf', $dailysummary);
			$current_summary = $this->db->get_where('dtr_daily_summary', array('employee_id' => $employee_id, 'date' => $cdate));
			if($current_summary->num_rows() == 1 || $holiday || $restday){
				if($current_summary->num_rows() == 1)
					$current_summary = $current_summary->row();
				else{
					//default 
					$current_summary = new stdClass();
					$current_summary->lates = 0;
					$current_summary->undertime = 0;
					$current_summary->lwp = 0;
					$current_summary->lwop = 0;
					$current_summary->hours_worked = 0;
					$current_summary->ot = 0;
					$current_summary->ot_excess = 0;
					$current_summary->nd = 0;
					$current_summary->ndot = 0;
					$current_summary->ndot_excess = 0;
					$current_summary->absent = 0;
				}
				
				$dailysummary['lwop'] -= $current_summary->lwop;			
				if( round($dailysummary['lwop'],2) != 0 && $current_summary->absent == 0 ){
					if($current_summary->lates > $current_summary->undertime){
						$adjustment = $dailysummary['lwop'] - $current_summary->lates;
						if( $adjustment >= 0 ){
							$dailysummary['lwop'] = 0;
							$current_summary->lates = 0;
						}
						else if( $adjustment < 0 ){
							$current_summary->lates = $current_summary->lates - $dailysummary['lwop'];
							$dailysummary['lwop'] = 0;
						}
					}
					else if($current_summary->undertime > $current_summary->lates){
						$adjustment = $dailysummary['lwop'] - $current_summary->undertime;
						if( $adjustment >= 0 ){
							$dailysummary['lwop'] = 0;
							$current_summary->undertime = 0;
						}
						else if( $adjustment < 0 ){
							$current_summary->undertime = $current_summary->undertime - $dailysummary['lwop'];
							$dailysummary['lwop'] = 0;
						}
					}
					else{
						$otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'LWP', number_format(round($dailysummary['lwop'], 2), 2, '.', '') );
						$summary_update['lwop'] += $dailysummary['lwop'];
					}
				}

				if($current_summary->absent == 1 && $dailysummary['absent'] == 0){
					switch( true ){
						case $dailysummary['lwop'] > 0 && $dailysummary['lwop'] - $hours_worked != 0:
							$otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'AWL', number_format($dailysummary['lwop'] - $hours_worked, 2, '.', '') );
							$summary_update['absences'] += ($dailysummary['lwop'] - $hours_worked) / $hours_worked;
							break;
						case $dailysummary['lwp'] > 0:
							$otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'AWL', number_format($dailysummary['lwp'] * -1, 2, '.', '') );	
							$summary_update['absences'] += ($dailysummary['lwp'] * -1) / $hours_worked;
							break;
						default:
							//remove undertime and lates
							$ftime = -$hours_worked + $dailysummary['lates'] + $dailysummary['undertime'];
							$dailysummary['lates'] = 0;
							$dailysummary['undertime'] = 0;
							if( round($ftime, 2) != 0 ) $otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'AWL', number_format(round($ftime, 2), 2, '.', '') );
							$summary_update['absences'] += $ftime / $hours_worked;
							break;
					}
				}

				if( $dailysummary['lates'] > ($this->config->item('deduction_lates') / 60) && $current_summary->lates > ($this->config->item('deduction_lates') / 60)){
					$dailysummary['lates'] -= $current_summary->lates;
					if(round($dailysummary['lates'], 2) != 0) $otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'TDY', number_format(round($dailysummary['lates'], 2), 2, '.', '') );
				}
				else if( $dailysummary['lates'] < ($this->config->item('deduction_lates') / 60) && $current_summary->lates > ($this->config->item('deduction_lates') / 60)){
					$dailysummary['lates'] = $current_summary->lates * -1;
					$otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'TDY', number_format(round($dailysummary['lates'], 2), 2, '.', '') );
					
				}
				else if( $dailysummary['lates'] > ($this->config->item('deduction_lates') / 60) && $current_summary->lates < ($this->config->item('deduction_lates') / 60)){
					if($dailysummary['lates'] != 0) $otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'TDY', number_format(round($dailysummary['lates'], 2), 2, '.', '') );

				}

				$dailysummary['undertime'] -= $current_summary->undertime;
				if( round($dailysummary['undertime'],2) != 0 ){
					$otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'UT', number_format(round($dailysummary['undertime'], 2), 2, '.', '') );
				}

				/*
				 * Removed, adjustment will be as Negative (LWOP) 
				 *
				if($current_summary->lwp != $dailysummary['lwp']){
					$dailysummary['lwp'] -= $current_summary->lwp;
					if(round($dailysummary['lwp'] * -1, 2) != 0) $otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'LWP', number_format(round($dailysummary['lwp'] * -1, 2), 2, '.', '') );
				}
				*/

				$dailysummary['ot'] -= $current_summary->ot;
				if( round($dailysummary['ot'],2) != 0 && !empty($this->otcode[$day_prefix.'_ot']) ){
					$otfile_row['lates'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], number_format(round($dailysummary['ot'], 2), 2, '.', ''), '', '');
				}

				$dailysummary['ot_excess'] -= $current_summary->ot_excess;
				if( round($dailysummary['ot_excess'],2) != 0 && !empty($this->otcode[$day_prefix.'_ot_excess']) ){
					$otfile_row['lates'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot_excess'], number_format(round($dailysummary['ot_excess'], 2), 2, '.', ''), '', '');
				}

				$dailysummary['nd'] -= $current_summary->nd;
				if( round($dailysummary['nd'],2) != 0 && !empty($this->otcode[$day_prefix.'_nd']) ){
					$otfile_row['lates'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_nd'], number_format(round($dailysummary['nd'], 2), 2, '.', ''), '', '');
				}

				$dailysummary['ndot'] -= $current_summary->ndot;
				if( round($dailysummary['ndot'],2) != 0 && !empty($this->otcode[$day_prefix.'_ndot']) ){
					$otfile_row['lates'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ndot'], number_format(round($dailysummary['ndot'], 2), 2, '.', ''), '', '');
				}

				$dailysummary['ndot_excess'] -= $current_summary->ndot_excess;
				if( round($dailysummary['ndot_excess'],2) != 0 && !empty($this->otcode[$day_prefix.'_ndot_excess']) ){
					$otfile_row['lates'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ndot_excess'], number_format(round($dailysummary['ndot_excess'], 2), 2, '.', ''), '', '');
				}
			}
		}
		else{
			$this->db->insert('dtr_daily_summary', $dailysummary);
			
			/* removed, this scenario should never happen
			if($day_prefix != 'reg' && $dailysummary['ot'] == 0 && $dailysummary['hours_worked'] > 0){
				$otfile_row['current'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], number_format(round($dailysummary['hours_worked'], 2), 2, '.', ''), '', '');	
			}
			*/

			if( round($dailysummary['lates'], 2) > ($this->config->item('deduction_lates') / 60) ){
				$otfile_row['current'][] = array($employee_no, $otfile_date, '', '', 'TDY', number_format(round($dailysummary['lates'], 2), 2, '.', '') );
			}

			if( round($dailysummary['undertime'], 2) > 0 ){
				$otfile_row['current'][] = array($employee_no, $otfile_date, '', '', 'UT', number_format(round($dailysummary['undertime'], 2), 2, '.', '') );
			}

			if( round($dailysummary['lwop'], 2) > 0 ){
				$otfile_row['current'][] = array($employee_no, $otfile_date, '', '', 'LWP', number_format(round($dailysummary['lwop'], 2), 2, '.', '') );
				$summary_update['lwop'] += $dailysummary['lwop'];
			}

			if( round($dailysummary['ot'], 2) > 0 && !empty($this->otcode[$day_prefix.'_ot']) ){
				$otfile_row['current'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], number_format(round($dailysummary['ot'], 2), 2, '.', ''), '', '');
			}

			if( round($dailysummary['ot_excess'], 2) > 0 && !empty($this->otcode[$day_prefix.'_ot_excess']) ){
				$otfile_row['current'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot_excess'], number_format(round($dailysummary['ot_excess'], 2), 2, '.', ''), '', '');
			}

			if( round($dailysummary['nd'], 2) > 0 && !empty($this->otcode[$day_prefix.'_nd']) ){
				$otfile_row['current'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_nd'], number_format(round($dailysummary['nd'], 2), 2, '.', ''), '', '');
			}

			if( round($dailysummary['ndot'], 2) > 0 && !empty($this->otcode[$day_prefix.'_ndot']) ){
				$otfile_row['current'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ndot'], number_format(round($dailysummary['ndot'], 2), 2, '.', ''), '', '');
			}

			if( round($dailysummary['ndot_excess'], 2) > 0 && !empty($this->otcode[$day_prefix.'_ndot_excess']) ){
				$otfile_row['current'][] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ndot_excess'], number_format(round($dailysummary['ndot_excess'], 2), 2, '.', ''), '', '');
			}
		}

		$summary_update['hours_worked'] += $dailysummary['hours_worked'];
		if($day_prefix == 'reg') $summary_update[$day_prefix .'_nd'] += $dailysummary['nd'];
		$summary_update[$day_prefix .'_ot'] += $dailysummary['ot'];
		$summary_update[$day_prefix .'_ndot'] += $dailysummary['ndot'];
		if($day_prefix != 'reg') $summary_update[$day_prefix .'_ot_excess'] += $dailysummary['ot_excess'];
		if($day_prefix != 'reg') $summary_update[$day_prefix .'_ndot_excess'] += $dailysummary['ndot_excess'];
		$summary_update['overtime'] += $dailysummary['ot'];
		//$summary_update['lates'] += $dailysummary['lates'];
		$summary_update['lates'] += $fixed_lates;
		$summary_update['undertime'] += $dailysummary['undertime'];

		if ($dtr->num_rows() == 0) {
			//tirso - replace $overtime with $total_overtime
			$final_data = array(
								'lates' => $lates*60, 'overtime' => $total_overtime*60, 'undertime' => $undertime*60,'reg_nd' => round($nd, 2),'ot_nd' => round($ndot,2),'restday' => $restday,
								'employee_id' => $employee_id, 'date' => $cdate, 'awol' => false,
								'hours_worked' => $minutes_worked / 60, 'undertime_infraction' => $undertime_infraction, 'late_infraction' => $late_infraction
								,'excused_tardiness' => $excused_tardiness*60, 'lates_display' => $lates_display*60
								,'approved_undertime' => $approved_undertime*60, 'undertime_display' => $undertime_display*60
								);

			if($this->config->item('client_no') == 2)
				$final_data['double_shift_is_used'] = $double_shift_is_used;

			$this->db->insert('employee_dtr', $final_data);
			$dtr_id = $this->db->insert_id();
		} else {

			$final_data = array(
								'lates' => $lates*60, 'overtime' => $total_overtime*60, 
								'undertime' => $undertime*60, 'reg_nd' => round($nd, 2),'ot_nd' => round($ndot,2),'restday' => $restday, 'awol' => false,
								'hours_worked' => $minutes_worked / 60, 'undertime_infraction' => $undertime_infraction, 'late_infraction' => $late_infraction
								,'excused_tardiness' => $excused_tardiness*60, 'lates_display' => $lates_display*60
								,'approved_undertime' => $approved_undertime*60, 'undertime_display' => $undertime_display*60
								);

			if($this->config->item('client_no') == 2)
				$final_data['double_shift_is_used'] = $double_shift_is_used;

			$dtr_id = $dtr->row()->id;
			$this->db->where('employee_id', $employee_id);
			$this->db->where('date', $cdate);
			$this->db->update('employee_dtr', $final_data);
		}

		$r = array('summary_update' => $summary_update, 'otfile_row' => $otfile_row);

		if ($absent || $restday || $holiday) {
			if (!$restday && !$holiday) {
				$r['absent_dtr'] = $dtr_id;
			}					
		}

		if(isset($infraction)) $r['infraction'] = $infraction;
		return $r;
	

	}

	// ------------------------------------------------------------------------

	function get_dropdown_options()
	{
		if (!$this->user_access[$this->module_id]['post']) {
			if (IS_AJAX) {
				header('HTTP/1.1 403 Forbidden');
			} else {
				$this->session->set_flashdata('flashdata', 'Insufficient access! Please contact the System Administrator.');
				redirect( base_url() );
			}
		} else {

			$status = $this->input->post('status');

			switch ($this->input->post('type')) {
				case 'employee_id':
					$this->db->select($this->db->dbprefix . 'user.user_id AS value, CONCAT(' . $this->db->dbprefix .'user.firstname, " ", '. $this->db->dbprefix . 'user.middleinitial, " ", '. $this->db->dbprefix . 'user.lastname, " ", IFNULL('. $this->db->dbprefix . 'user.aux," ")) AS text', false);
					$where = '('.$this->db->dbprefix.'employee.resigned_date IS NULL OR '.$this->db->dbprefix.'employee.resigned_date >= "'.$this->input->post('date_from').'")';
					$this->db->where($where);
					$this->db->where('role_id <>', 1);
					$this->db->where('user.inactive', 0);
					$this->db->where('user.deleted', 0);

					if ($this->config->item('client_no') == 1){
						$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
					}
					elseif ($this->config->item('client_no') == 2){
						$this->db->join('employee', 'employee.user_id = user.user_id');	
					}

					if( sizeof($this->input->post('status')) > 0 && $this->input->post('status') != ''){					
						$this->db->join('employment_status', "employment_status.employment_status_id = employee.status_id");
						$this->db->where_in($this->db->dbprefix."employment_status.employment_status_id", $this->input->post('status'));						
					}

					$table = 'user';
					break;
				case 'company_id':
					$this->db->select('company as text, company_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company';
					break;
				case 'department_id':
					$this->db->select('department as text, department_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company_department';
					break;
				case 'division_id':
					$this->db->select('division as text, division_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company_division';	
					break;
				case 'group_name_id':
					$this->db->select('group_name as text, group_name_id as value');
					$this->db->where('deleted', 0);
					$table = 'group_name';					
					break;
				case 'project_name_id':
					$this->db->select('project_name as text, project_name_id as value');
					$this->db->where('deleted', 0);
					$table = 'project_name';					
					break;					
				default:
					# code...
					break;
			}
			$results = $this->db->get($table)->result();
			// $response['json']['options'] = $this->db->get($table)->result();
			$response['json']['options'] = "";
			foreach ($results as $key => $value) {
				$response['json']['options'] .= "<option value='".$value->value."'>".$value->text."</option>";
			}

			$this->load->view('template/ajax', $response);
		}		
	}
	// END custom module funtions

}


?>