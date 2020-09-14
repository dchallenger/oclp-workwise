<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Periods extends MY_Controller
{
	private $otcode;

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

		$this->load->helper('time_upload');
	}

	function fix_obt_date(){
		//$this->db->where('date', '!= ""');
		//$this->db->delete('employee_obt_date');
		$obts = $this->db->get('employee_obt');
		foreach( $obts->result() as $obt ){
			$dstart = $obt->date_from;
			while( $dstart <= $obt->date_to ){
				$this->db->insert('employee_obt_date', array('employee_obt_id' => $obt->employee_obt_id, 'date' => $dstart));
				$dstart = date('Y-m-d',strtotime('+1 day', strtotime($dstart)));
			}
		}
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
		$data['content'] = 'listview';
		
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
		
		$tabs[] = '<li class="active"><a href="javascript:void(0)">Periods</li>';
		//$tabs[] = '<li><a href="' . site_url('employee/dtr/manage') .'">Manage DTR</li>';
		$tabs[] = '<li><a href="' . site_url('dtr/uploading') .'">Uploading</li>';

		$data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
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

	function detail(){
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'dtr/period_summary';

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

	function edit(){
		parent::edit();
	
		//additional module edit routine here		
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
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

	function ajax_save() {
		$date_from = date('Y-m-d', strtotime($this->input->post('date_from')));
		$date_to   = date('Y-m-d', strtotime($this->input->post('date_to')));
		
		// Check dates affected.
		$where = '(\'' . $date_from . '\' BETWEEN date_from AND date_to
						OR 
					 \''. $date_to . '\'  BETWEEN date_from AND date_to)';

		$this->db->where($where, '', false);
		$this->db->where('deleted', 0);

		if ($this->input->post('record_id') != '-1') {
			$this->db->where($this->key_field . ' <>', $this->input->post('record_id'));
		}
		
		$result = $this->db->get('timekeeping_period');

		if ($result->num_rows() > 0) {
			$error = true;
			$response->msg = 'Unable to save, dates are overlapping other periods.';
			$response->msg_type = 'error';				
		}


		//if($this->config->item('with_floating') == 1)	// tirso - temporary comment this line. cause of error not saving //

		if ($this->config->item('overlap_periods') == 1 || !$error) {
			parent::ajax_save();
		} else {
			parent::set_message($response);
			parent::after_ajax_save();			
		}

		//additional module save routine here
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        //$buttons .= "<div class='icon-label'><a class='icon-16-default' id='populate' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Populate Period</span></a></div>";

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }        
        
        $buttons .= "</div>";
                
		return $buttons;
	}	

	// ------------------------------------------------------------------------

	function _default_grid_actions( $module_link = "",  $container = "", $row = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";
		
		$actions = '<span class="icon-group">';
		
		$actions .= '<a tooltip="Close Period" href="javascript:void(0)" class="icon-button '. ($row['closed'] == 0 ? 'icon-16-xgreen-orb" onclick="closePeriod($(this), \''.$row['period_id'].'\')"' : 'icon-16-active"') .'></a>';
		
		if (!$row['closed']) {
			$actions .= '<a class="process-period icon-button icon-16-settings" module_link="'.$module_link.'" tooltip="Process" href="javascript:void(0)"></a>';
		}
		
		$actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View Summary" href="javascript:void(0)"></a>';
		
		if (!$row['closed']) {
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a>';
		}
		
		$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a></span>';
		return $actions;
	}	

	// ------------------------------------------------------------------------

	function _set_listview_query( $listview_id = '', $view_actions = true ) 
	{
		parent::_set_listview_query( $listview_id = '', $view_actions = true );

		$this->listview_qry .= ',closed';
	}

	// ------------------------------------------------------------------------

	function closePeriod()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access not allowed! Please contact the System Administrator.');
			redirect( base_url() );			
		}		

		$record_id = $this->input->post('period_id');

		$this->db->where($this->key_field, $record_id);
		$this->db->update($this->module_table, array('closed' => 1));

		$period_setting = $this->db->get_where("timekeeping_period" , array("period_id" => $record_id))->row();
		$date_from = $period_setting->date_from;

		$this->db->query("DELETE FROM {$this->db->dbprefix}employee_dtr_raw WHERE date < '{$date_from}'");

		$response->msg = 'Period closed';
		$response->msg_type = 'success';

		$this->load->view('template/ajax', array('json' => $response));
	}

	// ------------------------------------------------------------------------

	function process()
	{
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
				}

				$otfolder = 'uploads/otfile/' . $otfolder;
				if(!file_exists( $otfolder ) ) mkdir( $otfolder , 0777, true);
				$otfile = $otfolder. '/' . $this->input->post('filename') . '.txt';
				$otfile_row =array();
				if(file_exists($otfile)){ unlink($otfile); }
				$otfile_row['current'][] = array('Employee No', 'Date', 'OT Code', 'OT Hours', 'Leave Type', 'Leave Hours' );

				$progressfile = 'uploads/'.$this->input->post('filename').'-progresslog.txt';
				$ctr = 0;
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

				write_file($progressfile, number_format(($ctr / $total) * 100, 2).' ('.$ctr.'/'.$total.' employee/ current:'.$employee->employee_id.')');

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

	// ------------------------------------------------------------------------

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

	// ------------------------------------------------------------------------

	private function _formProcess($employee, $p, $otfile_row, $otcode)
	{
		$employee_id = $employee->employee_id;
		$employee_no = $employee->id_number;
		$employee_department = $employee->department_id;

		// check if resigned
		$resigned = $this->system->check_if_resigned($employee_id);
		if($resigned)
			$resigned_date = $resigned->resigned_date;
		else
			$resigned_date = null;

		//for overtime of non regular employee
		$non_regular_eot = false;
		
		$regular = false;
		if( ( $employee->status_id == 1 ) || ( $employee->status_id == 2 ) ){
			$regular = true;
		}

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
				//check if supervisor
				$supervisor = false;
				$employee_type = $this->system->get_employee_type_by_date($employee, $cdate);
				
				if( in_array($employee_type, $this->config->item('emp_type_no_late_ut')) ){
					$supervisor = true;
				}

				$schedule = $this->system->get_employee_worksched($employee_id, $cdate);
				if( $schedule ){
					$dtr_p = $this->_processDtr($employee_id, $cdate, $tstamp_cdate, $regular, $schedule, $summary_update, false, $p, $otfile_row, $employee_no, $supervisor, $resigned_date, $employee_department, $employee_type);
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

			if(isset($dtr_p['infraction'])) $infraction = array_merge($infraction, $dtr_p['infraction']);

		}


		// Get late filing.
		// Get only the dates where there is any instance of a late filing so we can loop through these dates instead.
		
		//get cutoff of previous period
		$qry = "SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE cutoff < '{$p->cutoff}' AND deleted = 0 ORDER BY cutoff DESC LIMIT 1";
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
					AND (CONCAT(a.date, ' ', time_start) NOT BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00')
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
					
					//check if supervisor
					$supervisor = false;
					$employee_type = $this->system->get_employee_type_by_date($employee, $lf_date->date);
					
					if( in_array($employee_type, $this->config->item('emp_type_no_late_ut')) ){
						$supervisor = true;
					}

					$dtr_p = $this->_processDtr($employee_id, $lf_date->date, strtotime($lf_date->date), $regular, $schedule, $summary_update, true, $p, $otfile_row, $employee_no, $supervisor, $resigned_date, $employee_department, $employee_type);
		
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
				$this->db->where_not_in('date', $infraction);
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
							$grace_period_s = hoursToSeconds($result->row()->shift_grace_period);
						}
						else{
							$grace_period_s = '00:00:00';
						}
					}
					
					// Check late
					// Get excused tardiness first.
					$et = get_form($employee_id, 'et', $dummy_p, $dtr->date);

					if ($et->num_rows() == 0 && 
						strtotime(date('H:i:s', strtotime($dtr->time_in1))) > 
						strtotime('+' . $grace_period_s . ' seconds', strtotime($shift_start))) {
						$infraction[] = $dtr->date;
					}
				}
			}
			$this->_ir_creation($infraction, $employee_id, $p);
		}

		return array('summary_update' => $summary_update, 'otfile_row' => $otfile_row);
	}

	protected function _ir_creation($infraction, $employee_id, $period = false)
	{
		if (count($infraction) >= $this->config->item('maximum_late_per_month')) {
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
			// $this->_send_email($employee_id, $infraction, $period);				
		}
	}


    /**
     * Send the email to employees,immediate superior and hr.
     */
    protected function _send_email($employee_id = false, $infraction = false, $period = array()) {
    	if (!$employee_id){
    		return false;
    	}

        $request['tardy_employee'] = $employee_id;
        $request['processed_date'] = $period->apply_late_from;
        

        // $request['date'] = date($this->config->item('display_date_format'), strtotime($date_email));


    	$recipients = array();
    	$result = $this->db->get_where('user',array('user_id'=>$employee_id));
    	if ($result && $result->num_rows() > 0){
    		$single_row = $result->row();
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

	private function _processDtr($employee_id, $cdate, $tstamp_cdate, $regular, $schedule, $summary_update, $for_late_filing = false, $p, $otfile_row, $employee_no, $supervisor, $resigned_date, $employee_department = false, $employee_type = 3)
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

		$dailysummary = array(
			'period_id' => $p->period_id,
			'employee_id' => $employee_id,
			'employee_no' => $employee_no,
			'employee_type' => $employee_type,
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

		// currently fixed for openaccess but i think this should be a standard. btw if you'll use this, don't forget to include the columns on database. (^_~) -jr
		if(CLIENT_DIR == 'oams' && isset($get_worked_hours->total_work_hours) && !is_null($get_worked_hours->total_work_hours)) {
			$hours_worked = $get_worked_hours->total_work_hours;
			$hours_with_break = $hours_worked + 1;
			$hours_leave_worked = 8; // I think this should be fix on 8. but incase i haven't think of all the possible scenario. we can easily change it.
			$hours_worked_first_half_day = $get_worked_hours->total_first_half;
			$hours_worked_second_half_day = $get_worked_hours->total_second_half;
		} else {
			$hours_worked = 8;
			$hours_with_break = 9;
			$hours_leave_worked = 8; // I think this should be fix on 8. but incase i haven't think of all the possible scenario. we can easily change it.
			$hours_worked_first_half_day = 4;
			$hours_worked_second_half_day = 4;
		}

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

		if($this->config->item('client_no') == 2 && $day_shift_id == 1)
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
					$this->db->update('employee_dtr', array('restday' => $restday, 'awol' => 0, 'undertime' => 0, 'late' => 0));
				}
			}

			if (CLIENT_DIR == 'pioneer'){
				$this->db->where('employee_id', $employee_id);
				$this->db->where(array('date' => $cdate, 'deleted' => 0));
				$edtr = $this->db->get('employee_dtr');

				if ($edtr->num_rows() == 0) {
					$this->db->insert('employee_dtr', array('employee_id' => $employee_id, 'date' => $cdate, 'restday' => $restday));
					$dtr_id = $this->db->insert_id();
				} else {
					$dtr_id = $edtr->row()->id;
					$this->db->where('employee_id', $employee_id);
					$this->db->where('date', $cdate);
					$this->db->update('employee_dtr', array('employee_id' => $employee_id, 'date' => $cdate, 'restday' => $restday));
				}
			}

			if ( ($dtr->num_rows() > 0 && $dtr->row()->time_out1 != null) || $dtrp->num_rows() > 0) {
				
				$absent  = false;

				if ($holiday) {
					$day_prefix = $day_prefix . 'rd';							
				} else {
					$day_prefix = 'rd';							
				}

			} else {

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

		}

		if($day_shift_id == 0) $day_shift_id = 1;
		$result = $this->db->get_where('timekeeping_shift', array( 'shift_id'  => $day_shift_id  ));
		$workshift = $result->row();
		
		$grace_period = date('i', strtotime($workshift->shift_grace_period));
		$grace_period_s = hoursToSeconds($workshift->shift_grace_period);

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

		if( CLIENT_DIR == 'asianshipping' ){
			$shift_breakstart = $workshift->noon_start;
			$shift_breakend = $workshift->noon_end;

			$shift_datetime_noonstart = $cdate . ' ' . $shift_breakstart;
			$shift_datetime_noonend = $cdate . ' ' . $shift_breakend;
		}

		//tirso
		$halfday_minutes_undertime = 0;
		if (isset($workshift->minimum_minutes_consider_halfday)){
			$halfday_minutes_undertime = $workshift->minimum_minutes_consider_halfday * 60;
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

		if( CLIENT_DIR == 'asianshipping' ){
			$dtr_break_in = '';
			$dtr_break_out = '';
		}

		if ($dtr->num_rows() > 0 ){
			$record = $dtr->row();
			if( is_valid_time($record->time_in1) ) $dtr_in = $record->time_in1;
			if( is_valid_time($record->time_out1) ) $dtr_out = $record->time_out1;

			if( CLIENT_DIR == 'asianshipping' ){
				if( is_valid_time($record->time_in2) ) $dtr_break_in = $record->time_in2;
				if( is_valid_time($record->time_out2) ) $dtr_break_out = $record->time_out2;
			}

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
				if (!$restday && !$holiday) {
					$break = (strtotime($r->noon_end) - strtotime($r->noon_start));
				} else { // Restday break every 5 hours = 1 hour break (legacy) NEW - if > 5 hours break = 1
					$break = (strtotime($dtr_out) - strtotime($dtr_in)) / 60 / 60;
					switch( CLIENT_DIR ){
						case 'pioneerx':
							if ($break >= 5) {
								$break = floor( $break / 5 );
								$break = $break * 60 * 60;
							}
							break;
						default:
							if ($break > 5) {
								$break = 60 * 60;
							}
					}
				}
				
				//get forms
				$oot = $get_form($employee_id, 'oot', $p, $cdate, true, $for_late_filing, true);
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

				$ndstart = strtotime(date('Y-m-d 22:00:01', $tstamp_cdate)); //added a second beyond 10PM so as not to conflict with 1-10PM schedule, 
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
						}

						$nd = ($ndshift_end - $ndshift_start);

						//customization
						if ($this->config->item('client_no') == 1){
							$get_break=$this->system->get_break($shift_start, $workshift->noon_start,$workshift->noon_end, $cdate);
							if (strtotime(date('Y-m-d ' . $workshift->noon_start, strtotime($get_break['break_start_date']))) < $ndstart) {
								$nd = ($ndshift_end - $ndstart);
							} else if ($restday || $holiday) {	
								$nd = ($ndshift_end - $ndshift_star - $break);
							}					
						}
					}
				}

				//compute OT
				if ($oot->num_rows() > 0) {
					// process								
					foreach ($oot->result() as $ot) {
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

						if( !$regular && $restday && date('D', strtotime($cdate)) != "Sun"){

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
						}

						//$overtime += ((strtotime($otend) - strtotime($otstart)));
						//$curform_overtime = ((strtotime($otend) - strtotime($otstart)));

						$otbreak_deducted = false;
						if ( $restday || $holiday ) {
							$actual_ot = strtotime($dtr_out) - strtotime($dtr_in);
							$app_ot = strtotime($ot->datetime_to) - strtotime($ot->datetime_from);

							if($actual_ot <= $app_ot){
								$overtime += $actual_ot;
								$curform_overtime = $actual_ot;
								$otstart = $dtr_in;
								$otend = $dtr_out;
							}
							else{
								$overtime += $app_ot;
								$curform_overtime = $app_ot;
								$otstart = $ot->datetime_from;
								$otend = $ot->datetime_to;
							}

							// Subtract one hour if total is greater than 8
							if (($overtime/60/60) >= 5 && floor((strtotime($ot->datetime_to) - strtotime($ot->datetime_from)) / 60 / 60 / 5) * 60 > 1) {
								$overtime -= $break;
								$curform_overtime -= $break;
								$otbreak_deducted = true;
							}

							//compute ot excess
							if(($overtime / 60 / 60) > 8){
								$ot_excess = ($overtime / 60 / 60) - 8;

								//get the start of ot excess
								$ot_excess_start = strtotime($otstart) + (8*60*60);	
								if( $otbreak_deducted ){
									$ot_excess_start += $break;	
								}
								$ot_excess = (($overtime / 60 / 60) - 8) * 60 * 60;
							}
						}
						else{
							$overtime += ((strtotime($otend) - strtotime($otstart)));
							$curform_overtime = ((strtotime($otend) - strtotime($otstart)));	
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

						//compute ot allowance
						$qry = "SELECT a.*
						FROM {$this->db->dbprefix}payroll_ot_allowance_setup a
						WHERE ({$curform_overtime}/60/60) between min_hour AND max_hour AND {$employee_type} in (a.employee_type)";
						$ot_setups = $this->db->query( $qry );
						if( $this->db->_error_message() == "" && $ot_setups->num_rows() > 0 ){
							foreach($ot_setups->result() as $ot_setup){
								$this->db->delete('payroll_ot_allowance', array('employee_oot_id' => $ot->employee_oot_id, 'ot_allowance_id' => $ot_setup->ot_allowance_id));
								$ot_insert = array(
									'ot_allowance_id' => $ot_setup->ot_allowance_id,
									'employee_oot_id' => $ot->employee_oot_id,
									'payroll_date' => $p->payroll_date,
									'date' => $cdate,
									'employee_id' => $employee_id,
									'transaction_id' => $ot_setup->transaction_id,
									'amount' => $ot_setup->amount,
									'ot_total' => round($curform_overtime/60/60, 2)
								);
								$this->db->insert('payroll_ot_allowance', $ot_insert );
							}
						}
					}

					// if($this->config->item('client_no') != 2)
					// {
						//subtract ndot excess from regular ot
						$overtime -= $ot_excess;

						//subtract ndot from regular ot
						$overtime -= $ndot;

						//subtract ndot excess from regular ot
						$overtime -= $ndot_excess;	
					// }

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
				}

				if( $day_shift_id != 1 ){
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

				$grace_period = date('i', strtotime($r->shift_grace_period));
				$grace_period_s = hoursToSeconds($r->shift_grace_period);

				if( CLIENT_DIR == 'asianshipping' ){
					$grace_period_n = hoursToSeconds($r->noon_grace_period);
					$shift_cache[$cdate]['grace_period_n'] = $grace_period_n;
				}

				$shift_cache[$cdate]['grace_period'] = $grace_period;
				$shift_cache[$cdate]['grace_period_s'] = $grace_period_s;

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
				if( CLIENT_DIR == 'asianshipping' ){

					if(strtotime($dtr_in) > 
					strtotime('+' . $grace_period_s . ' seconds', strtotime($shift_datetime_start))){

						$lates = (strtotime($dtr_in) - strtotime($shift_datetime_start));

						$this->db->where( 'min_minutes_late <= '.( $lates / 60 )  );
						$this->db->where( 'max_minutes_late >= '.( $lates / 60 )  );
						$this->db->where('deleted',0);
						$this->db->where('late_type',1);
						$this->db->where('shift_id',$day_shift_id);
						//$this->db->order_by('max_minutes_late','desc');
						$shift_late_result = $this->db->get('timekeeping_shift_lates');

						if( $shift_late_result->num_rows() > 0 ){
							$shift_late_info = $shift_late_result->row();
							$lates = $shift_late_info->equivalent_minutes_late*60;
						}
						else{
							$lates = (strtotime($dtr_in) - strtotime($shift_datetime_start));
						}

					}

					if(strtotime($dtr_break_in) > 
					strtotime('+' . $grace_period_n . ' seconds', strtotime($shift_datetime_noonend))){

						$noon_lates = (strtotime($dtr_break_in) - strtotime($shift_datetime_noonend));

						$this->db->where( 'min_minutes_late <= '.( $noon_lates / 60 )  );
						$this->db->where( 'max_minutes_late >= '.( $noon_lates / 60 )  );
						$this->db->where('deleted',0);
						$this->db->where('late_type',2);
						$this->db->where('shift_id',$day_shift_id);
						//$this->db->order_by('minutes_late','desc');
						$shift_late_result = $this->db->get('timekeeping_shift_lates');

						if( $shift_late_result->num_rows() > 0 ){
							$shift_late_info = $shift_late_result->row();
							$lates += $shift_late_info->equivalent_minutes_late*60;
						}
						else{
							$lates += $noon_lates;
						}

					}

				}
				else{
					if(strtotime($dtr_in) > 
					strtotime('+' . $grace_period_s . ' seconds', strtotime($shift_datetime_start))){
						$lates = (strtotime($dtr_in) - strtotime($shift_datetime_start));
					}
				}


				//check if need to remove break from lates
				if( strtotime($dtr_in) >= strtotime(date('Y-m-d ' .$r->noon_end, strtotime($dtr_out)))){
					$lates -= $break;
				}else if( strtotime($dtr_in) > strtotime(date('Y-m-d ' .$r->noon_start, strtotime($dtr_out))) ){
					$lates -= strtotime($dtr_in) - strtotime(date('Y-m-d ' .$r->noon_start, strtotime($dtr_out)));	
				}

				// Check UT
				if (!$restday){
					if (strtotime(date('Y-m-d H:i:s', strtotime($dtr_out))) < strtotime($shift_datetime_end)) {
						//check that dtrout is after shift start
						if( strtotime(date('Y-m-d H:i:s', strtotime($dtr_out))) <= strtotime($shift_datetime_start) ){
							$undertime = strtotime($shift_datetime_end) - strtotime($shift_datetime_start);
							$undertime -= $break;
						}
						else{

							// get proper break. this is standard but i'll limit the changes for openaccess first.
							if(CLIENT_DIR == 'oams') {
								if(date('Y-m-d ' .$r->noon_start, strtotime($dtr_out)) > $dtr_in && date('Y-m-d ' .$r->noon_start, strtotime($dtr_out)) < $dtr_out) {
									$break_with_date_in = date('Y-m-d '.$r->noon_start, strtotime($dtr_out));
									$break_with_date_out = date('Y-m-d '.$r->noon_end, strtotime($dtr_out));
								} else {
									$break_with_date_in = $cdate.' '.$r->noon_start;
									$break_with_date_out = $cdate.' '.$r->noon_end;
								}
							} else {
								$break_with_date_in = date('Y-m-d ' .$r->noon_start, strtotime($dtr_out));
								$break_with_date_out = date('Y-m-d ' .$r->noon_end, strtotime($dtr_out));
							}

							$undertime = (strtotime($shift_datetime_end) - strtotime(date('Y-m-d H:i:s', strtotime($dtr_out))));

							if((strtotime($dtr_out) >= strtotime($break_with_date_in)) && (strtotime($dtr_out) < strtotime($break_with_date_out))) {
								$undertime -= strtotime(date('Y-m-d ' .$r->noon_end, strtotime($dtr_out))) - strtotime($dtr_out);
							}
							else if(strtotime($dtr_out) <= strtotime($break_with_date_in)) {
								$undertime -= $break;
							}

						}
					}
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

				//check wether undertime is greater than worked hours(8)
				if( ($undertime / 60 / 60) >= $hours_worked ){
					$undertime = $hours_worked * 60 * 60;
					$lates = 0;
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
					
					$hours_with_break = ($hours_worked * 60 * 60) + $break;
					if($hourdiff >= $hours_with_break)
						$hourdiff = $hourdiff - $break;

					$overtime += $hourdiff;

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
							$undertime = 0; //reset undertime
							if( strtotime(date('H:i:s', strtotime($dtr_out))) < strtotime($r->halfday) ){
								$undertime = strtotime($r->halfday) - strtotime(date('H:i:s', strtotime($dtr_out)));
							}
							
							// lates should not be recomputed in this scenario
							//$lates = (strtotime(date('H:i:s', strtotime($dtr_in))) - strtotime($r->noon_end));
						} elseif ($lv->duration_id == 2) { // If on first half, clear all lates.
							$lates = 0;
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
						if(!$for_late_filing) $infraction[] = $cdate;
					}
				}
				else if( $et->num_rows() == 1 ){
					$employee_et = $et->row();
					if( !empty($employee_et->etblanket_id) ){
						$lates = 0;
					}
				}

				if ($day_prefix != 'reg') {
					if ($restday) {
						if ($overtime > 0) {
							$minutes_worked = $overtime - $lates;
						} else {
							$minutes_worked = 0; 
						}
					}
				}

				if ( ($minutes_worked / 60 /60 ) > $hours_worked) {
					$minutes_worked = 60 * $hours_worked * 60;
				}
				
				if(!$by_pass_holiday) {
					if($day_prefix == 'leg' || ($day_prefix == 'spe' && $regular)) {
						$undertime = $lates = 0;					
						$minutes_worked = 60 * $hours_worked * 60;
					}
				}

				if ($restday) {
					if( !$regular  ){
						if(date('D', strtotime($cdate)) != "Sun")
						{
							if( $non_regular_eot ){
								$minutes_worked = 480 * 60;
							}
							else{

								$time_in = new DateTime($dtr_in);
								$time_out = new DateTime($dtr_out);
								$diff = $time_in->diff($time_out);

								$sh_start = strtotime ( $dtr_in );
								$sh_end = strtotime ( '+'.$diff->h.' hours' , strtotime ( $dtr_in ) );

								if( $diff->h >= 5 ){

									$overtime = 0;

									if( $diff->h > $hours_worked ){
										$minutes_worked = $hours_worked * 60 * 60;
									}
									else{
										$minutes_worked = ($sh_end - $sh_start - 3600);
									}
								}
								else{
									$overtime = 0;
									$minutes_worked = ($sh_end - $sh_start);
								}
							}
						} else{
							$minutes_worked = 0;
						}

					}
					else{
						$minutes_worked = 0;
					}
				}

				if ($overtime < 0) {$overtime = 0;}

				//For regular employees, if obt was present, during holiday or restday
				if( ( ( $obt->num_rows() > 0 ) && ( $holiday || $restday ) ) && $regular ){
					$minutes_worked = 0;
				}			

				//tirso - if restday then automatic minutes worked calculated into 0
				if (CLIENT_DIR == 'oams'){
					if ($restday){
						$minutes_worked = 0;
					}
				}

				//check el blanket
				$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leave_blanket WHERE FIND_IN_SET('".$employee_id."',employee_id) AND '".$cdate."' BETWEEN date_from AND date_to; ");
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
			if ($this->config->item('before_after_holiday')){
				if ($holiday){
					$prev_working_days = $this->system->get_prev_working_day($employee_id,$cdate);
					$next_working_days = $this->system->get_next_working_day($employee_id,$cdate);

					$dtr_prev_working_days = get_employee_dtr_from($employee_id, $prev_working_days);
					$dtr_next_working_days = get_employee_dtr_from($employee_id, $next_working_days);
					
					if ($dtr_prev_working_days && $dtr_prev_working_days->num_rows() > 0){
						if ($dtr_prev_working_days->row()->time_in1 == "" || $dtr_prev_working_days->row()->time_out1 == ""){
							$obt_prev_working_days = $get_form($employee_id, 'obt', $p, $prev_working_days, true, $for_late_filing, true);
							$dtrp_prev_working_days = $get_form($employee_id, 'dtrp', $p, $prev_working_days, true, $for_late_filing, true);

							// Check leave for whole day previous working days
							$this->db->select('duration_id,employee_leaves_dates.employee_leave_date_id, employee_leaves_dates.credit, employee_leaves_dates.employee_leave_date_id, employee_leaves.application_form_id, employee_form_type.application_code');
							$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');

							$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
							$this->db->where('employee_id', $employee_id);
							$this->db->where('(\'' . $prev_working_days . '\' BETWEEN date_from AND date_to)', '', false);
							$this->db->where('employee_leaves_dates.date', $cdate);
							$this->db->where('form_status_id', 3);
							$this->db->where('employee_leaves_dates.deleted', 0);
							$this->db->where('employee_leaves.deleted', 0);

							if( !$for_late_filing ){
								//always check forms for current period
								$this->db->where('(DATE_FORMAT(date_approved, "%Y-%m-%d") <= \'' . $p->cutoff .'\')', '', false);	
							}

							$leave_prev_working_days = $this->db->get('employee_leaves');

							if ($obt_prev_working_days->num_rows() == 0 || $dtrp_prev_working_days == 0 || $leave_prev_working_days == 0){
								$holiday = false;
								$holiday_to_pay = false;
							}									
						}
					}

					if ($dtr_next_working_days && $dtr_next_working_days->num_rows() > 0){
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
					}
				}
			}
			//to check if holiday then check previous and next working days is absent

			//if after obt and dtrp still incomplete timein timeout, mark as absent unless a holiday or restday
			$absent = $holiday || $restday ? false : true;

			//tirso - holiday but no pay since absent either before or after of holiday date. to avoid awol.
			if (!$holiday_to_pay){
				$absent = false;
			}

			// Check leave for whole day
			$this->db->select('duration_id,employee_leaves_dates.employee_leave_date_id, employee_leaves_dates.credit, employee_leaves_dates.credit_back, employee_leaves_dates.employee_leave_date_id, employee_leaves.application_form_id, employee_form_type.application_code');
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
				$ml_on_holiday = false;
				$absent = false;
				$undertime = $hours_worked * 60 * 60; //initialize full undertime, in case leave is half
				$tbl_columns = $this->db->list_fields('employee_leave_balance');
				foreach( $leave->result() as $lv ){

					// if department is excluded need to 
					if($holiday && $employee_department && in_array($employee_department, explode(',', $holiday[0]['excluded'])))
					{
						if($lv->application_form_id != 5 && $lv->application_form_id != 7)
						{
							$overtime = $lv->credit * $hours_leave_worked * 60 * 60;
							$dailysummary['lwp'] += $lv->credit * $hours_leave_worked;
							$summary_update['lwp'] += $lv->credit * $hours_leave_worked;
						}

						if (CLIENT_DIR == 'oams' && $lv->application_form_id == 7){
							$minutes_worked += $lv->credit * 60 * 60 * $hours_leave_worked;
						}

					} else if ($holiday) {
						if($lv->application_form_id == 5 || $lv->application_form_id == 6) $lv->application_code = 'MPL';

						// Return leave credit.
						if( !in_array($lv->application_code, array('LWOP', 'MPL', 'SLW')) && in_array($lv->application_code . '_used', $tbl_columns) && $lv->credit_back == 0 ){
							$this->db->set(strtolower($lv->application_code) . '_used' , strtolower($lv->application_code) . '_used - ' . $lv->credit, false);
							$this->db->where('employee_id', $employee_id);
							$this->db->where('year', date('Y'));
							$this->db->update('employee_leave_balance');
							
							$this->db->where('employee_leave_date_id', $lv->employee_leave_date_id);
							$this->db->update('employee_leaves_dates', array('credit_back' => 1));

							log_message('error', 'Leave balance credit back');
						}

						$ml_on_holiday = true;
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

					if (CLIENT_DIR == 'oams'){
						$undertime = 0;
					}

					if($supervisor && ($lv->application_form_id == 7 || $lv->application_form_id == 5) ){
						$explicit_app = true;
					}
				}

				//remove undertime if undertime blanket and is coupled with a leave
				if ($this->config->item('remove_undertime_if_out')) {
					// Check OUT
					$out = $get_form($employee_id, 'out', $p, $cdate);
					if ($out->num_rows() > 0) {
						$out_num_rows = $out->num_rows();
						$out = $out->row();
						if( !empty($out->outblanket_id) ){
							$undertime = 0; 
						}
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
				if (CLIENT_DIR == 'oams'){
					if ($ml_on_holiday){
						$minutes_worked = 0;
					}
				}
				else{
					$minutes_worked = $hours_worked * 60 * 60;
				}
			}

			//check el blanket
			$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leave_blanket WHERE FIND_IN_SET('".$employee_id."',employee_id) AND '".$cdate."' BETWEEN date_from AND date_to; ");
			if ($result && $result->num_rows() > 0){
				$minutes_worked = $hours_worked*60*60;					
				$absent = false;
			}

			// check return date of base-off cdate must be tuesday. to make sure that there is attendance on monday.
			// $this->_base_off();
		} // End DTR check

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
				$dailysummary['absent'] = 1;
				$summary_update['absences'] += 1;
				$minutes_worked = 0;
				if(!$for_late_filing){
					$otfile_row['current'][] = array($employee_no, $otfile_date, '', '', 'AWL', $hours_worked.'.00' );
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

		//if employee is supervisor, no lates and undertime applied
		if($supervisor){
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
				$minutes_worked = $hours_worked_first_half_day * 60 * 60; // used for more than or less than 8 hours worked : benjie(currently fix on openaccess)
				$lates = 0;
			}
		}

		//check half day for undertime : tirso
		if($halfday_minutes_undertime > 0){
			if( !$absent && ( ( $undertime > $halfday_minutes_undertime ) && ( $undertime <= 14400  ) ) ){
				$minutes_worked = $hours_worked_second_half_day * 60 * 60; // used for more than or less than 8 hours worked : benjie(currently fix on openaccess)
				$undertime = 0;
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
		if ($nd < 0) {$nd = 0;}
		if ($overtime < 0) {$overtime = 0;}
		if ($minutes_worked < 0) {$minutes_worked = 0;}
		if ($undertime < 0) {$undertime = 0;}
		if( ($lates / 60 / 60) > $hours_worked ) $lates = $hours_worked * 60 * 60; // happens if dtr us after shift sched
		if($restday) $lates = 0;
	
		//modify by tirso
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
		}

		$undertime_infraction = ($undertime > 0 && !$supervisor && $out_num_rows == 0);
		$late_infraction = ($lates > 0 && !$supervisor && $et_num_rows == 0);

		//convert to hours and minutes
		$minutes_worked /= 60;
		$lates = $lates / 60 / 60;
		$fixed_lates = $fixed_lates / 60 / 60;
		$undertime = $undertime / 60 / 60;
		$overtime = $overtime / 60 / 60;
		$ot_excess = $ot_excess / 60 / 60;
		$nd = $nd / 60 / 60;
		$ndot = $ndot / 60 / 60;
		$ndot_excess = $ndot_excess / 60 / 60;
		$total_overtime = $overtime + $ot_excess + $ndot + $ndot_excess;		

		$dailysummary['hours_worked'] = $minutes_worked > 0 ? $minutes_worked / 60 : 0;
		$dailysummary['lates'] = $lates;
		$dailysummary['undertime'] = $undertime;
		$dailysummary['ot'] = $overtime;
		$dailysummary['nd'] = $nd;
		$dailysummary['ndot'] = $ndot;
		$dailysummary['ot_excess'] = $ot_excess;
		$dailysummary['ndot_excess'] = $ndot_excess;
		
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
						case $dailysummary['lwop'] == 8:
							//do nothing
							break;
						case $dailysummary['lwop'] > 0 && $dailysummary['lwop'] - $hours_worked != 0:
							$otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'AWL', number_format($dailysummary['lwop'] - $hours_worked, 2, '.', '') );
							$summary_update['absences'] += ($dailysummary['lwop'] - $hours_worked) / $hours_worked;
							break;
						case $dailysummary['lwp'] == 8 || ($dailysummary['lwp'] > 0 && $hours_worked == 8):
							$otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'AWL', number_format(8 * -1, 2, '.', '') );	
							$summary_update['absences'] -= 1;
							break;
						case $dailysummary['lwp'] > 0:
							$otfile_row['lates'][] = array($employee_no, $otfile_date, '', '', 'AWL', number_format($dailysummary['lwp'] * -1, 2, '.', '') );	
							$summary_update['absences'] += ($dailysummary['lwp'] * -1) / $hours_worked;
							break;
						default:
							//remove undertime and lates
							$ftime = -$hours_worked + $dailysummary['undertime'];

							if( $dailysummary['lates'] > ($this->config->item('deduction_lates') / 60) ){
								$ftime += $dailysummary['lates'];
							}

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
		$summary_update['lates'] += $dailysummary['lates'];
		$summary_update['undertime'] += $dailysummary['undertime'];

		if ($dtr->num_rows() == 0) {
			//tirso - replace $overtime with $total_overtime
			$final_data = array(
								'lates' => $lates*60, 'overtime' => $total_overtime*60, 'undertime' => $undertime*60,'reg_nd' => round($nd, 2),'ot_nd' => round($ndot,2),'restday' => $restday,
								'employee_id' => $employee_id, 'date' => $cdate, 'awol' => false,
								'hours_worked' => $minutes_worked / 60, 'undertime_infraction' => $undertime_infraction, 'late_infraction' => $late_infraction
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

	function get_period_option_ui()
	{
		if (!$this->user_access[$this->module_id]['post']) {
			if (IS_AJAX) {
				header('HTTP/1.1 403 Forbidden');
			} else {
				$this->session->set_flashdata('flashdata', 'Insufficient access! Please contact the System Administrator.');
				redirect( base_url() );
			}
		} else {

			//load variables to env

			$status = $this->db->get('employment_status')->result();

			$status_html = "<option value='0'>Please Select</option>";

			foreach( $status as $status_record ){
				$status_html .= "<option value='".$status_record->employment_status_id."'>".$status_record->employment_status."</option>";
			}

			$data['employment_status'] = $status_html;

			$result = $this->db->get_where('timekeeping_period', array("period_id" => $this->input->post('period_id')));
			if($result && $result->num_rows() > 0)
			{
				$result = $result->ROW();
				$data['period_date_from'] = $result->date_from;
				$data['period_date_to'] = $result->date_to;
			}

			$this->load->vars( $data );

			$response['html'] = $this->load->view('dtr/period_form', '', false);
			$this->load->view('template/ajax', $response);
		}
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
					$this->db->select($this->db->dbprefix . 'user.user_id AS value, CONCAT(' . $this->db->dbprefix .'user.firstname, " ", '. $this->db->dbprefix . 'user.middleinitial, " ", '. $this->db->dbprefix . 'user.lastname, " ", '. $this->db->dbprefix . 'user.aux) AS text', false);
					$where = '('.$this->db->dbprefix.'employee.resigned_date IS NULL OR '.$this->db->dbprefix.'employee.resigned_date >= "'.$this->input->post('date_from').'")';
					$this->db->where($where);
					$this->db->where('role_id <>', 1);
					$this->db->where('user.deleted', 0);

					if ($this->config->item('client_no') == 1){
						$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
					}
					elseif ($this->config->item('client_no') == 2){
						$this->db->join('employee', 'employee.user_id = user.user_id');	
					}
					else{
						$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
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
				default:
					# code...
					break;
			}

			$response['json']['options'] = $this->db->get($table)->result();

			$this->load->view('template/ajax', $response);
		}		
	}
	// END custom module funtions

	private function _flexi_employee($employee_id = false, $dtr_in = false, $dtr_out = false, $cdate = false, $tom = false, $ndstart = false, $ndend = false)
	{
		if($employee_id && $dtr_in && $dtr_out && $cdate && $tom && $ndstart && $ndend)
		{
			$data = array();
			$minutes_worked = strtotime($dtr_out) - strtotime($dtr_in);
			$data['double_shift_is_used'] = 0;

			// minus one for break time.
			$minutes_worked = $minutes_worked - (60 * 60);

			if($minutes_worked >= (17*60*60) && $this->hdicore->use_double_shift($employee_id, $tom, true)) {
				$data['minutes_worked'] = 8 * 60 * 60;
				$data['undertime'] = 0;
				$data['double_shift_is_used'] = 1;
				// $this->db->update('employee_dtr', array('double_shift_is_used' => 1), array('date' => $cdate, 'employee_id' => $employee_id, 'deleted' => 0));
			} else if($minutes_worked >= (8*60*60) && $minutes_worked <= (15*60*60)) { // normal shift
				$data['minutes_worked'] = 8 * 60 * 60;
				$data['undertime'] = 0;
			} else if($minutes_worked < (8*60*60)) { // undertime
				$data['minutes_worked'] = 8 * 60 * 60;
				$data['undertime'] = strtotime(date('Y-m-d H:i:s', strtotime('+ 9 hour '.$dtr_in))) - strtotime($dtr_out);
			} else if($minutes_worked >= (15*60*60)) {
				$data['minutes_worked'] = 8 * 60 * 60;
				$data['undertime'] = 0;
			}
			// for night diff computation

			// Check if sched fall on ND hours
			if(
				(strtotime($dtr_in) >= $ndstart && strtotime($dtr_in) <= $ndend) || (strtotime($dtr_out) >= $ndstart && strtotime($dtr_out) <= $ndend)
				||
				($ndstart >= strtotime($dtr_in) && $ndstart <= strtotime($dtr_out)) || ($ndend >= strtotime($dtr_in) && $ndend <= strtotime($dtr_out))
				) 
			{
				$ndshift_start = strtotime($dtr_in) >= $ndstart ? strtotime($dtr_in) : $ndstart;
				if(strtotime($dtr_out) <= $ndend) {
					$ndshift_end = strtotime($dtr_out); // (strtotime($dtr_out) > strtotime($shift_datetime_end) ? strtotime($shift_datetime_end) : strtotime($dtr_out));
				} else {
					$ndshift_end = $ndend;
				}

				$nd = ($ndshift_end - $ndshift_start);

				$data['night_diff'] = $nd;
			} else
				$data['night_diff'] = 0;

			return $data;
		} else
			echo "Missing parameter for flexi employees";
	}

	private function _base_off()
	{
		$this->db->select('employee_leave_base_off.employee_leave_id, employee_leave_base_off.*, employee_leaves.employee_id');
		$this->db->join('employee_leaves', 'employee_leaves.employee_leave_id = employee_leave_base_off.employee_leave_id', 'left');
		$this->db->where('employee_id', $employee_id);
		$this->db->where('return_date', date('Y-m-d', strtotime('-1 day', strtotime($cdate))));
		$this->db->where('form_status_id', 3);
		$e_base_off = $this->db->get_where('employee_leave_base_off');

		if($e_base_off && $e_base_off->num_rows() > 0) {
			$e_dtr = $this->db->get_where('employee_dtr', array('employee_id' => $employee_id, 'hours_worked <=' => 0, 'awol' => 1, 'date' => date('Y-m-d', strtotime('-1 day', strtotime($cdate)))));
			if($e_dtr && $e_dtr->num_rows() > 0)
				$this->db->update('employee_leaves', array('form_status_id' => 4, 'decline_remarks' => 'Auto-decline because no attendance on Monday'), array('employee_leave_id' => $e_base_off->row()->employee_leave_id));
		}
	}

	function get_apply_to(){
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		$apply_to = $this->input->post('apply_to_id');	
		$processing_type = $this->input->post('processing_type_id');

		switch( $apply_to ) {
				case '1':
					$this->db->select($this->db->dbprefix . 'user.user_id AS value, CONCAT(' . $this->db->dbprefix .'user.firstname, " ", '. $this->db->dbprefix . 'user.lastname) AS text', false);
					$where = '('.$this->db->dbprefix.'employee.resigned_date IS NULL OR '.$this->db->dbprefix.'employee.resigned_date >= "'.$this->input->post('date_from').'")';
					$this->db->where($where);
					$this->db->where('role_id <>', 1);
					$this->db->where('user.deleted', 0);

					switch(CLIENT_DIR){
						case 'pioneer':
							$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
							break;
						default:
							$this->db->join('employee', 'employee.user_id = user.user_id');
							break;	
					}
					$this->db->order_by('user.lastname','user.firstname');
					$table = 'user';
					break;
				case '2':
					$this->db->select('company as text, company_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company';
					break;
				case '3':
					$this->db->select('division as text, division_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company_division';	
					break;
				case '4':
					$this->db->select('department as text, department_id as value');
					$this->db->where('deleted', 0);
					$table = 'user_company_department';
					break;
				default:
					# code...
					break;
			}

			$response['json']['options'] = $this->db->get($table)->result();

			$this->load->view('template/ajax', $response);
	}

	function _append_to_select(){
		if( CLIENT_DIR == 'asianshipping' ) $this->listview_qry .= ', timekeeping_period.apply_to, timekeeping_period.apply_to_id';
	}

	/**
	 * Available methods to override listview.
	 * 
	 * A. _append_to_select() - Append fields to the SELECT statement via $this->listview_qry
	 * B. _set_filter()       - Add aditional WHERE clauses	 
	 * C. _custom_join
	 * 
	 * @return json
	 */
	function listview()
	{
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions );

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


		if (method_exists($this, '_append_to_select')) {
			// Append fields to the SELECT statement via $this->listview_qry
			$this->_append_to_select();
		}

		if (method_exists($this, '_custom_join')) {
			$this->_custom_join();
		}

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );
		if( $this->sensitivity_filter ){
			$fields = $this->db->list_fields($this->module_table);
			if(in_array('sensitivity', $fields) && isset($this->sensitivity[$this->module_id])){
				$this->db->where($this->module_table.'.sensitivity IN ('.implode(',', $this->sensitivity[$this->module_id]).')');
			}
			else{
				$this->db->where($this->module_table.'.sensitivity IN (0)');	
			}	
		}

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$result = $this->db->get();
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

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			if( $this->sensitivity_filter ){
				if(in_array('sensitivity', $fields) && isset($this->sensitivity[$this->module_id])){
					$this->db->where($this->module_table.'.sensitivity IN ('.implode(',', $this->sensitivity[$this->module_id]).')');
				}	
				else{
					$this->db->where($this->module_table.'.sensitivity IN (0)');	
				}	
			}

			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}

			if (method_exists($this, '_custom_join')) {
				// Append fields to the SELECT statement via $this->listview_qry
				$this->_custom_join();
			}
			
			if($sidx != ""){
				$this->db->order_by($sidx, $sord);
			}
			else{
				if( is_array($this->default_sort_col) ){
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);
			
			$result = $this->db->get();

			//$response->last_query = $this->db->last_query();

			//check what column to add if this is a related module
			if($related_module){
				foreach($this->listview_columns as $column){                                    
					if($column['name'] != "action"){
						$temp = explode('.', $column['name']);
						if(strpos($this->input->post('column'), ',')){
							$column_lists = explode( ',', $this->input->post('column'));
							if( sizeof($temp) > 1 && in_array($temp[1], $column_lists ) ) $column_to_add[] = $column['name'];
						}
						else{
							if( sizeof($temp) > 1  && $temp[1] == $this->input->post('column')) $this->related_module_add_column = $column['name'];
						}
					}
				}
				//in case specified related column not in listview columns, default to 1st column
				if( !isset($this->related_module_add_column) ){
					if(sizeof($column_to_add) > 0)
						$this->related_module_add_column = implode('~', $column_to_add );
					else
						$this->related_module_add_column = $this->listview_columns[0]['name'];
				}
			}

			if( $this->db->_error_message() != "" ){
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			}
			else{
				$response->rows = array();
				if($result->num_rows() > 0){
					$this->load->model('uitype_listview');
					$ctr = 0;
					foreach ($result->result_array() as $row){
						$cell = array();
						$cell_ctr = 0;
						foreach($this->listview_columns as $column => $detail){
							if( preg_match('/\./', $detail['name'] ) ) {
								$temp = explode('.', $detail['name']);
								$detail['name'] = $temp[1];
							}
							
							if(sizeof(explode(' AS ', $detail['name'])) > 1 ){
								$as_part = explode(' AS ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							else if(sizeof(explode(' as ', $detail['name'])) > 1 ){
								$as_part = explode(' as ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							
							if( $detail['name'] == 'action'  ){
								if( $view_actions ){
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );
									$cell_ctr++;
								}
							}else{
								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 40) ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 3 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] ) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 39 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['type'] ) && $this->listview_fields[$cell_ctr]['other_info']['type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 39 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['type'] ) && $this->listview_fields[$cell_ctr]['other_info']['type'] != 'Query' ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else{
									$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
								}

								if($cell_ctr == 0 && $detail['name'] == 't0apply_to'){
									$apply = array();
									switch($row['apply_to_id']){
										case 1: //employee
											$this->db->where_in('user_id', explode(',', $row['apply_to']));
											$apply_to = $this->db->get('user');
											$apply = array();
											foreach($apply_to->result() as $employee){
												$apply[] = $employee->firstname.' '. $employee->lastname;
											}
											break;
										case 2: //company
											$this->db->where_in('company_id', explode(',', $row['apply_to']));
											$apply_to = $this->db->get('user_company');
											$apply = array();
											foreach($apply_to->result() as $company){
												$apply[] = $company->company;
											}
											break;
										case 3: //Division.
											$this->db->where_in('division_id', explode(',', $row['apply_to']));
											$apply_to = $this->db->get('user_company_division');
											$apply = array();
											foreach($apply_to->result() as $division){
												$apply[] = $division->division;
											}
											break;
										case 4:	// Department
											$this->db->where_in('department_id', explode(',', $row['apply_to']));
											$apply_to = $this->db->get('user_company_department');
											$apply = array();
											foreach($apply_to->result() as $department){
												$apply[] = $department->department;
											}
											break;
									}
									$cell[$cell_ctr] = implode(', ', $apply);
								}

								$cell_ctr++;
							}
						}
						$response->rows[$ctr]['id'] = $row[$this->key_field];
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}
		
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
}

/* End of file */
/* Location: system/application */