<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dtr extends MY_Controller
{
	function __construct(){
		parent::__construct();
		$this->load->model('forms/leaves_model', 'leaves');
		$this->load->model('forms/obt_model', 'obt');
		$this->load->model('forms/cws_model', 'cws');
		$this->load->model('forms/oot_model', 'oot');

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array('user' => 'employee_id'); //table => field format
		
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
		$data['scripts'][] = chosen_script();		
		$data['content'] = 'listview';
		$data['jqgrid'] = 'dtr/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		//set default columnlist

		//$this->listview_column_names = array('Date', 'IN', 'OUT', 'Lates', 'UT', 'OT', 'Work Shift', 'Apply Leaves / Forms');
		$this->listview_column_names = array('Date', 'IN', 'OUT', 'Hours Worked', 'ET (Hours)', 'Lates (Hours)', 'Authorized UT (Hours)', 'UT (Hours)', 'OT (Hours)', 'Work Shift', '');

		$this->listview_columns = array(
				array('name' => 'date'),
				array('name' => 'timein'),
				array('name' => 'timeout'),
				array('name' => 'hours_worked'),
				array('name' => 'lates'),
				array('name' => 'lates'),
				array('name' => 'undertime'),
				array('name' => 'undertime'),
				array('name' => 'overtime'),
				array('name' => 'workshift'),
				array('name' => 'forms', 'width' => '180','align' => 'center', 'classes' => 'td-action')
			);

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();
		
		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "";
		
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

	function edit(){
		parent::edit();
	
		//additional module edit routine here
		$data['show_wizard_control'] = false;
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

	function ajax_save(){
		parent::ajax_save();

		//additional module save routine here
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function listview() {
		$this->load->helper('time_upload');

		// Set default filter		
		$filter['employee_id'] 	= $employee_id = ($this->input->post('employee_id') != '') ? $this->input->post('employee_id') : $this->userinfo['user_id'];
		$filter['date_from >='] = $date_from = ($this->input->post('date_from') != '') ? date('Y-m-d' , strtotime($this->input->post('date_from'))): date('Y-m-01');
		$filter['date_to <=']   = $date_to = ($this->input->post('date_to') != '') ? date('Y-m-d', strtotime($this->input->post('date_to'))) : date('Y-m-t');

		$response->msg_type = 'success';
		$response->msg = '';
		$response->page = 1;
		$response->records = 0;
		

		if (strtotime($date_to) < strtotime($date_from)) {
			$response->msg_type = 'attention';
			$response->msg = 'Invalid date range.';

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		$cdate = $date_from;
		$cells = array();
		$cell_ctr = 0;	

		$records = 0;
		/*foreach ($a_schedule as $key => $shift_calendar) {						*/
		$workshift_day = array();
		$consecutive_absent = 0;
		$consecutive_absent_days = array();

		// Get employed date
		$this->db->where('employee_id', $employee_id);
		$this->db->where('deleted', 0);
		$result = $this->db->get('employee');

		$supervisor = false;
		$employed_date = '';

		if ($result){
			if ($result->num_rows() > 0){
				$employee = $result->row();

				if( in_array($employee->employee_type, $this->config->item('emp_type_no_late_ut')) ){
					$supervisor = true;
				}				

				$employed_date = $employee->employed_date;				
			}
		}

		// Get dtr starting from starting date
		while (strtotime($cdate) <= strtotime($date_to)) {
			// check holiday.
			$holiday = $this->system->holiday_check($cdate, $employee_id);

			$forms = array();

			$w_tin = FALSE;
			$w_tout = FALSE;
			$rd = FALSE;
			$remarks = "";

			$dummy_p->date_to = $date_to;
			$dummy_p->date_from = $date_from;				

			// Check IN/OUT
			$id = 0;
			$dtr = get_employee_dtr_from($filter['employee_id'], $cdate);

			if ($dtr && $dtr->num_rows() > 0){
				$id = $dtr->row()->id;
			}
			//$cells[$cell_ctr]['id'] = $cell_ctr;
			$cells[$cell_ctr]['id'] = $id;
			$cell[0] = "<span style='float:left'>".display_date('D, M j Y', strtotime($cdate))."</span>";
			$cell[1] = ''; // IN
			$cell[2] = ''; // OUT			
			$cell[3] = ''; // Hours Worked	
			$cell[4] = '0.00'; // lates
			$cell[5] = '0.00'; // undertime
			$cell[6] = '0.00'; // overtime
			$cell[7] = ''; // shift	
			$cell[8] = '';


			//get schedule & shift
			$schedule = $this->system->get_employee_worksched($employee_id, $cdate, true);
			$shift_id = $schedule->shift_id;
			$shift = $schedule->shift;
			
			// Try to find CWS to use as shift for current date					
			$cws = get_form($employee_id, 'cws', $dummy_p, $cdate, true);

			if ($cws->num_rows() > 0) {
				$cws = $cws->row();
				$shift_id = $schedule->shift_id;
				$shift = $schedule->shift;
				$forms['cws'] = $cws->employee_cws_id;
				$remarks = "cws";
				
				// back here
			}

			// to get for approval
			$cws_for_approval = get_form($employee_id, 'cws', $dummy_p, $cdate, false);
			if($cws_for_approval && $cws_for_approval->num_rows() > 0)
			{
				$cws_for_approval = $cws_for_approval->row();
				if($cws_for_approval->form_status_id == 2)
				{
					$forms['cws'] = $cws_for_approval->employee_cws_id;
					$remarks = "cws";
				}
			}

			if(! (strtotime(date('Y-m-d')) >= strtotime($cdate)) ) {
				$cell[1] = '';
			}

			if ($shift_id == 0) {// Rest day
				$cell[7] = 'Rest Day';
				//$w_tin = TRUE;
				//$w_tout = TRUE;
			} else if($shift_id == 1 && $this->config->item('client_no') == 2) {
				$cell[7] = 'Rest Day';
			} else if($this->hdicore->is_flexi($employee_id) && $this->config->item('with_flexi')) {
				$cell[7] = '<i>Flexible</i>';
				$cell[7] .= '<br /><small><i>'.$shift.'</i></small>';
			}else {
				$cell[7] = $shift;
			}			

			$dtr_in = '';
			$dtr_out = '';
			if ($dtr && $dtr->num_rows() > 0){
				$cell[3] = number_format($dtr->row()->hours_worked, 2);
				$cell[4] = number_format($dtr->row()->lates / 60, 2);
				$cell[5] = number_format($dtr->row()->undertime / 60, 2);
				$cell[6] = number_format($dtr->row()->overtime / 60, 2);


				$dtr_in = $dtr->row()->time_in1;
				$dtr_out = $dtr->row()->time_out1;
			}

			if ( $dtr->num_rows() > 0 ){
				if ($dtr->row()->time_in1 != '' &&
					!($dtr->row()->time_in1 == '0000-00-00 00:00:00' 
						|| $dtr->row()->time_in1 == '' || is_null($dtr->row()->time_in1))
						) {
					$cell[1] = date('h:i:s a', strtotime($dtr->row()->time_in1));
					$w_tin = TRUE;
				}

				if ($dtr->row()->time_out1 != '' &&
					!($dtr->row()->time_out1 == '0000-00-00 00:00:00' 
						|| $dtr->row()->time_out1 == '' || is_null($dtr->row()->time_out1))
						) {
					$cell[2] = date('h:i:s a', strtotime($dtr->row()->time_out1));
					$w_tout = TRUE;
				}

				// Check OBT
				$obt = get_form($employee_id, 'obt', $dummy_p, $cdate, true);

				if ($obt->num_rows() > 0) {
					$obts = $obt->result();
					foreach($obts as $obt)
					{
						if ($dtr->row()->time_in1 == '0000-00-00 00:00:00' || $dtr->row()->time_in1 == '' 
							|| is_null($dtr->row()->time_in1)
							|| strtotime($obt->time_start) < strtotime(date('H:i:s', strtotime($dtr->row()->time_in1)))
							) {
							$cell[1] = date('h:i:s a', strtotime($cdate . ' ' . $obt->time_start));
							$dtr_in = date('Y-m-d H:i:s', strtotime($cdate . ' ' . $obt->time_start));
							$w_tin = TRUE;
						}

						if ($dtr->row()->time_out1 == '0000-00-00 00:00:00' || $dtr->row()->time_out1 == '' 
							|| is_null($dtr->row()->time_out1) 
							|| strtotime($obt->time_end) > strtotime(date('H:i:s', strtotime($dtr->row()->time_out1)))
							) { 
							$cell[2] = date('h:i:s a', strtotime($cdate . ' ' .$obt->time_end));
							$dtr_out = date('Y-m-d H:i:s', strtotime($cdate . ' ' . $obt->time_end));
							$w_tout = TRUE;
						}

						$forms['obt'][] = $obt->employee_obt_id;
						$remarks = "obt";
					}
				}					

				//to get for approval
				$obt_for_approval = get_form($employee_id, 'obt', $dummy_p, $cdate, false);
				if($obt_for_approval && $obt_for_approval->num_rows() > 0)
				{
					$obt_for_approval = $obt_for_approval->row();
					if($obt_for_approval->form_status_id == 2)
					{
						$forms['obt'] = $obt_for_approval->employee_obt_id;
						$remarks = "obt";
					}
				}

				// Check leave for whole day
				$this->db->select('duration_id, employee_leaves.employee_leave_id, employee_leaves.form_status_id');
				$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
				$this->db->where('employee_id', $employee_id);
				$this->db->where('(\''. $cdate . '\' BETWEEN date_from and date_to)', '', false);
				$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $cdate . '\')', '',false);
				$this->db->where('(form_status_id = 3 OR form_status_id = 2 OR form_status_id = 4 )');
				$this->db->where('form_status_id <>', 1);
				$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
				$this->db->where('employee_leaves.deleted', 0);

				$leave = $this->db->get('employee_leaves');

				if ($leave->num_rows() > 0) {													
					if ($shift_id > 0 && $leave->row()->duration_id == 1 && $leave->row()->form_status_id == 3){ //if ($shift_id > 0) {													
						// if (strtolower($cell[1]) == 'no in' || strtolower($cell[1]) == 'absent') {
							$cell[1] = 'LEAVE';
							$w_tin = TRUE;
						// }

						// if (strtolower($cell[2]) == 'no out') {
							$cell[2] = '';
							$w_tout = TRUE;
						// }
					}

					foreach ($leave->result() as $leave) {
						$forms['leave'][] = $leave->employee_leave_id;
					}
				}

			} else { // No time record entry

				if ($dtr && $dtr->num_rows() > 0){
					$w_tin = is_valid_time($dtr->row()->time_in1);
					$w_tout = is_valid_time($dtr->row()->time_out1);
				}
				// Check leave for whole day
				$this->db->select('duration_id, employee_leaves.employee_leave_id, employee_leaves.form_status_id,employee_leaves.blanket_id');
				$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
				$this->db->where('employee_id', $employee_id);
				$this->db->where('(\''. $cdate . '\' BETWEEN date_from and date_to)', '', false);
				$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $cdate . '\')', '',false);
				$this->db->where('(form_status_id = 3 OR form_status_id = 2 OR form_status_id = 4)');
				$this->db->where('form_status_id <>', 1);
				$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
				$this->db->where('employee_leaves.deleted', 0);

				$leave = $this->db->get('employee_leaves');
				
				if ($leave->num_rows() > 0) {						
					$w_tin = TRUE;
					$w_tout = TRUE;

					foreach ($leave->result() as $leave) {
						$forms['leave'][] = $leave->employee_leave_id;

						if ($leave->duration_id == 1 && $leave->form_status_id == 3) {
							$cell[1] = 'LEAVE';							
							$remarks = "";						
						}

						if ($shift_id > 0 && $leave->form_status_id == 3) {
							if (strtolower($cell[1]) == 'no in') {
								$cell[1] = 'LEAVE';
							}

							if (strtolower($cell[2]) == 'no out') {
								$cell[2] = '';
							}
						}							
					}
				}


				// Check OBT
				$obt = get_form($employee_id, 'obt', $dummy_p, $cdate, true);

				if ($obt->num_rows() > 0) {
					$obts = $obt->result();
					foreach($obts as $obt)
					{
						if ($dtr->row()->time_in1 == '0000-00-00 00:00:00' || $dtr->row()->time_in1 == '' || is_null($dtr->row()->time_in1)) {
							$cell[1] = date('h:i:s a', strtotime($cdate . ' ' . $obt->time_start));
							$dtr_in = date('Y-m-d H:i:s', strtotime($cdate . ' ' . $obt->time_start));
						} else {
							$cell[1] = date('h:i:s a', strtotime($dtr->row()->time_in1));
						}

						if ($dtr->row()->time_out1 == '0000-00-00 00:00:00' || $dtr->row()->time_out1 == '' 
							|| is_null($dtr->row()->time_out1) 
							|| strtotime($obt->time_end) > strtotime(date('H:i:s', strtotime($dtr->row()->time_out1)))
							) { 
							$cell[2] = date('h:i:s a', strtotime($cdate . ' ' .$obt->time_end));
							$dtr_out = date('Y-m-d H:i:s', strtotime($cdate . ' ' . $obt->time_end));
						} else {
							$cell[2] = date('h:i:s a', strtotime($dtr->row()->time_out1));
						}
							
						$w_tin = TRUE;
						$w_tout = TRUE;	
						$forms['obt'][] = $obt->employee_obt_id;
					}
				}
			}

			//to get for approval
			$obt_for_approval = get_form($employee_id, 'obt', $dummy_p, $cdate, false);
			if($obt_for_approval && $obt_for_approval->num_rows() > 0)
			{
				$obt_for_approval = $obt_for_approval->row();
				if($obt_for_approval->form_status_id == 2)
				{
					$forms['obt'] = $obt_for_approval->employee_obt_id;
					$remarks = "obt";
				}
			}

			// Check other forms.
			$dtrp = get_form($employee_id, 'dtrp', $dummy_p, $cdate, false);
			if ($dtrp->num_rows() > 0) {
				foreach ($dtrp->result() as $_dtrp) {
					if( $_dtrp->form_status_id == 3 ){
						if ($_dtrp->time_set_id == 1) {
							if ($dtr->row()->time_in1 == '0000-00-00 00:00:00' || $dtr->row()->time_in1 == '' || is_null($dtr->row()->time_in1)){
								$cell[1] = date('h:i:s a', strtotime($_dtrp->time));
								$w_tin = TRUE;
								$dtr_in = $_dtrp->time;								
							}
							if (CLIENT_DIR == "oams") {
								$cell[1] = date('h:i:s a', strtotime($_dtrp->time));
								$w_tin = TRUE;
								$dtr_in = $_dtrp->time;		
							}
						} else {
							if ($dtr->row()->time_out1 == '0000-00-00 00:00:00' || $dtr->row()->time_out1 == '' || is_null($dtr->row()->time_out1)){
								$cell[2] = date('h:i:s a', strtotime($_dtrp->time));
								$w_tout = TRUE;
								$dtr_out = $_dtrp->time;
							}

							if (CLIENT_DIR == "oams") {
								$cell[2] = date('h:i:s a', strtotime($_dtrp->time));
								$w_tout = TRUE;
								$dtr_out = $_dtrp->time;
							}
						}


					}

					$forms['dtrp'][] = $_dtrp->employee_dtrp_id;
				}
			}			

			// Official overtime
			$oot = get_form($employee_id, 'oot', null, $cdate, false);

			if ($oot->num_rows() > 0) {
				foreach( $oot->result_array() as $oot_rec ){						
					$forms['oot'][] = $oot_rec['employee_oot_id'];						
				}
			}

			// Official undertime
			$out = get_form($employee_id, 'out', null, $cdate, false);

			if ($out->num_rows() > 0) {
				$forms['out'] = $out->row()->employee_out_id;
			}

			$et = get_form($employee_id, 'et', null, $cdate, false);

			if ($et->num_rows() > 0) {
				$forms['et'] = $et->row()->employee_et_id;
			}

			if($this->config->item('allow_ds') == 1)
			{
				$ds = get_form($employee_id, 'ds', null, $cdate, false);

				if($ds->num_rows() > 0) {
					$forms['ds'] = $ds->row()->employee_ds_id;
				}
			}

			if (count($forms) > 0) {
				$cell[8] = '<span class="icon-group" style="float:right"><a class="icon-button icon-16-info" rel="' . base64_encode(serialize($forms)) . '" tooltip="View Forms" href="javascript:void(0)"></a></span>';

			}

			if (strtolower($cell[1]) != 'absent' 
				&& strtotime($cdate) < strtotime(date('Y-m-d'))
				&& $cell[7] != 'Rest Day'
				&& !$holiday
				) {
				if (!$w_tin):
					$cell[1] = "Absent";
				endif;
				if (!$w_tout):
					$cell[2] = "Absent";
				endif;

				if( $cell[1] == 'Absent' && $cell[2] == 'Absent' && $this->config->item('hide_sup_absent') && $supervisor ){
					$cell[1] = $cell[2] = "";
				}
			}

			if ($w_tin && !$w_tout){						
				$cell[2] = "No Out";
			}

			if (!$w_tin && $w_tout){
				$cell[1] = "No In";
			}

			if ($dtr && $dtr->num_rows() > 0){
				if ($dtr->row()->awol) {
					if ($cell[7] != 'Rest Day') {
						if (!$supervisor){
							$cell[1] = 'AWOL';
						}
					}
				}
			}

			//suspended
			if ($dtr && $dtr->num_rows() > 0){
				if ($dtr->row()->suspended) {
						$cell[1] = 'Suspended';
				}
			}

			$a_h = array();

			$holiday_exclude = $this->system->holiday_check($cdate, $employee_id, true);

			if ($holiday) {
				foreach ($holiday as $h) {
					$a_h[] = $h['holiday'];
				}

				if ($cell[7] == 'Rest Day') {
					$cell[7] = '<strong>HOLIDAY / REST DAY</strong>';
					$rd = true;
				} else {
					$cell[7] = '<strong>HOLIDAY</strong>';
				}

				$cell[7] .= '<br />' . implode(', ', $a_h);

				if(!$holiday_exclude && !$rd){

					if( $this->hdicore->is_flexi($employee_id) && $this->config->item('with_flexi') ) {
						$cell[7] .= '<br /><i>Flexible</i>';
					}
					
					$cell[7] .= '<br /><small><i>'.$shift.'</i></small>';
				}
			}

			if (strtotime($employed_date) > strtotime($cdate)) {
				$cell[1] = $cell[2] = '';
				if ($this->config->item('client_no') == 2){
					$cell[3] = $cell[4] = $cell[5] = '0.00';
				}
			}

			if( $supervisor ){
				$cell[4] = '-'; // lates
				$cell[5] = '-'; // undertime
				$cell[6] = '-'; // overtime	
			}

			//resigned
			if ($dtr && $dtr->num_rows() > 0){
				if ($dtr->row()->resigned) {
					$cell[1] = 'Resigned';
					$cell[3] = '-';
					$cell[4] = '-'; 
					$cell[5] = '-'; 
					$cell[6] = '-'; 
					$cell[7] = '-';
				}
			}

			if($this->config->item('allow_ds') == 1)
			{
				if($this->hdicore->use_double_shift($employee_id, $cdate))
				{
					$cell[1] = 'Double';
					$cell[2] = 'Shift';
				}
			}

            if ((($dtr_in != "" && $dtr_in != '0000-00-00 00:00:00') && ($dtr_out != "" && $dtr_out != '0000-00-00 00:00:00')) && $cdate <> date ('Y-m-d',strtotime($dtr_out))){
                $cell[1] = date('M j Y h:i:s a', strtotime($dtr_in));
                $cell[2] = date('M j Y h:i:s a', strtotime($dtr_out));
            }

			// check if floating
			if($this->config->item('with_floating') == 1)
			{
				if($this->hdicore->check_if_floating_period($employee_id, $cdate) && $cell[1] != 'LEAVE')
				{
					$cell[1] = 'Floating';
					$cell[2] = '-';
					$cell[3] = '-';
					$cell[4] = '-'; 
					$cell[5] = '-'; 
					$cell[6] = '-'; 
					$cell[7] = '-';
				}
			}

			$cells[$cell_ctr++]['cell'] = $cell;

			$cdate = date('Y-m-d', strtotime('+1 day', strtotime($cdate)));
		}

		/*}*/

		$response->records = count($cells);
		$response->rows = $cells;

		// Get records

		if ($this->config->item('client_no') == 2){
			if (strtotime($employed_date) > strtotime($date_from)){
				$response->msg_type = 'attention';
				$response->msg = 'Employed date is advance with date from. IN and OUT with affected dates will display as empty<br /> from ' . $date_from .' to '. date('Y-m-d',strtotime('-1 day', strtotime($employed_date))) . ' .';
			}
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
	    $sort_col = array();
	    foreach ($arr as $key=> $row) {
	        $sort_col[$key] = $row[$col];
	    }

	    array_multisort(array_map('strtolower',$sort_col), $dir, $arr);
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		$this->load->helper('form');		
                            
        $this->load->model('uitype_base');

		$subordinates = array();

        $result = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ));

        if ($result){
        	if ($result->num_rows() > 0){
				$emp = $result->row();
				$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
				if (!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1){
					$subordinates = $this->system->get_subordinates_by_project($this->user->user_id);
				}
				$this->array_sort_by_column($subordinates, 'firstname');
        	}
        }

		$subordinate_array = array();
            
        $buttons = "<div>";
		
		if ($this->user_access[$this->module_id]['post']) {
			
			//$employees = $this->uitype_base->get_employees();
			
			$this->db->join('employee','employee.employee_id = user.employee_id','left');
			$this->db->where('user.deleted', 0);
			$this->db->where('role_id <>', 1);
			$this->db->where('resigned', 0);
			$this->db->order_by('firstname');
			$active_employees = $this->db->get('user')->result_array();

			foreach ($active_employees as $employee) {
				$options['Active'][$employee['user_id']] = $employee['firstname'] . ' '  . $employee['middleinitial'] . ' '  . $employee['lastname'] . ' '  . $employee['aux'];
			}

			$this->db->join('employee','employee.employee_id = user.employee_id','left');
			$this->db->where('user.deleted', 0);
			$this->db->where('role_id <>', 1);
			$this->db->where('resigned', 1);
			$this->db->order_by('firstname');
			$resigned_employees = $this->db->get('user')->result_array();

			foreach ($resigned_employees as $employee) {
				$options['Resigned'][$employee['user_id']] = $employee['firstname'] . ' '  . $employee['lastname'];
			}

			//asort($options);

         	$buttons .= '<div style="float:left;padding: 5px 5px 0 0">Employee:</div><div style="float:left">' . addslashes(str_replace("\n", "", 
         						form_dropdown('employee_id', 
         							$options,
         							set_value('employee_id', $this->userinfo['user_id']),
         							'style="width:400px;"'
         						)));
		} else if (count($subordinates) > 0) {			
			$subordinate_array['-------'][$this->userinfo['user_id']] = 'Me ' . '('. $this->userinfo['firstname'] . ' ' . $this->userinfo['lastname'] . ')';

			foreach ($subordinates as $s) {
				$subordinate_array[$s['position']][$s['employee_id']] = $s['firstname'] . ' ' . $s['lastname'];
			}       

         	$buttons .= '<div style="float:left;padding: 5px 5px 0 0">Employee:</div><div style="float:left">' . addslashes(str_replace("\n", "", 
         						form_dropdown('employee_id', 
         							$subordinate_array, 
         							set_value('employee_id', $this->userinfo['user_id']),
         							'style="width:400px;"'
         						)));
		}
            
     	$buttons .= '</div>&nbsp;<div style="float:left;padding-left:5px">Date: ' . '<input type="text" class="date" name="date_from" /> - <input type="text" class="date" name="date_to" />&nbsp;';
     	$buttons .= '&nbsp;<button id="filter-dtr">Filter</button></div>';
        $buttons .= "</div>";  

		return $buttons;
	}

	function fetch_forms()
	{
		$this->load->helper('time_upload');

		$forms = $this->input->post('forms');

		$applied_forms = unserialize(base64_decode($forms));

		$html = '';

		foreach ($applied_forms as $type => $id) {					
			if ($type != 'leave' && !is_array($id)) { 				
				$html .= get_form_by_id_type($id, $type);
				$html .= '<hr>';
			} else {
				foreach ($id as $leave) {
					$html .= get_form_by_id_type($leave, $type);
					$html .= '<hr>';
				}
			}			
		}

		$response['html'] = $html;
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_client_no() {
		$response = $this->config->item('client_no');
		$this->load->view('template/ajax', array('json' => $response));

	}	

	function check(){
		$this->load->helper('time_upload');
		check1();
	}

	function manage(){
		if($this->user_access[$this->get_manage_module_id()]['post'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}	

		$data['scripts'][] = chosen_script();
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/date.js"></script>';

		$data['is_project_hr'] = false;
        if ($this->user_access[$this->get_manage_module_id()]['project_hr']) {
        // if ($this->user_access[$this->module_id]['project_hr']) {
            $subordinates = $this->get_subordinates_by_project_dtr_manage($this->userinfo['user_id']);
            if (count($subordinates)>0 && $subordinates != false) {
                $data['project_hr'] = $subordinates;
                $data['is_project_hr'] = true;
            }else{
                $data['is_project_hr'] = false;
            }
        }

		$data['content'] = $this->module_link.'/manage';
		$this->load->helper('form');
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


	function get_manage_module_id(){
		$query = $this->db->get_where('module', array('class_name =' => $this->module, 'class_path' => $this->module_link.'/manage'));
		$ret = $query->row();
		
		return $ret->module_id;
	}

	function get_obt_detail($user_id=""){
		$employee = $this->system->get_employee($this->input->post('user_id'));
        $response->employee_id = $employee['employee_id'];
        $response->employee = '<div class="text-input-wrap"><input type="hidden" id="obt_employee_exist" name="obt_employee_exist" value="1"><input id="employee_id" class="input-text" type="hidden" value="'.$employee['employee_id'].'" name="employee_id"><input id="employee_name" class="input-text" type="text" value="'.$employee['firstname'] .' '. $employee['lastname'].'" name="employee_name" disabled="disabled"> </div>';

        $response->subordinates = '<option value="'.$employee['employee_id'].'">'.$employee['firstname'] .' '. $employee['lastname'].'</option>';

        $data['json'] = $response;                      
        $this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

    function get_subordinates_by_project_dtr_manage($employee_id){
    	$this->db->select('project_name_id');
		$this->db->where('employee_id',$employee_id);
		$this->db->where('user.deleted',0);
		$this->db->where('project_name_id <>',0);
		$result = $this->db->get('user');

		$project_name_id = array();
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$project_name_id[] = $row->project_name_id;
			}
		}

		if (count($project_name_id) > 0){

			$this->db->select('user.*, user.employee_id as employee_id,employee.rank_id, position, department');
			$this->db->where_in('user.project_name_id',$project_name_id);			
			$this->db->where('employee.resigned', 0);
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');			
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->group_by('employee.employee_id');
			$subordinates_by_project_result = $this->db->get('user');
			
			if( $subordinates_by_project_result && $subordinates_by_project_result->num_rows() > 0 ){
				return $subordinates_by_project_result->result_array();
			}		
			else{
				return false;
			}
		}
		return false;		
    }

	function get_manage_module_name(){
		$query = $this->db->get_where('module', array('class_name =' => $this->module, 'class_path' => $this->module_link.'/manage'));
		$ret = $query->row();

		$response = $ret->short_name;

        $data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function get_period(){
		$response->options = '<option value="">Select...</option>';
		$this->db->where('period_year',$this->input->post('period_year'));
		$this->db->where('deleted',0);
		
		if(CLIENT_DIR == 'asianshipping'){
			// only for asian shipping
			$this->db->where('apply_to_id',2);
			$this->db->where('apply_to',1);
		}

		$result = $this->db->get('timekeeping_period')->result_array();
        foreach($result as $data){
            $response->options .= '<option value="'.$data["period_id"].'">'.date($this->config->item('display_date_format'),strtotime($data["date_from"])).' to '.date($this->config->item('display_date_format'),strtotime($data["date_to"])).'</option>';
        }

        $data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function get_dtr(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
		
		if( $this->user_access[$this->get_manage_module_id()]['post'] ){
			$this->load->helper('time_upload');
			$response->dtr = $this->load->view( $this->module_link.'/manage_dtr', '', true);	
		}
		else{
			$response->msg = 'You dont have sufficient privilege, please contact the System Administrator.';
			$response->msg_type = 'error';
		}

		$data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function save_dtr(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
		
		if( $this->user_access[$this->get_manage_module_id()]['post'] ){
			$employee_id = $this->input->post('employee_id');
			$dates = $this->input->post('date');
			$time_in1 = $this->input->post('time_in1');
			$time_in2 = $this->input->post('time_in2');
			$time_out1 = $this->input->post('time_out1');
			$time_out2 = $this->input->post('time_out2');
			$awol = $this->input->post('awol');
			$ot_starts = $this->input->post('ot_start');
			$ot_ends = $this->input->post('ot_end');

			$safe_to_save = true;

			//check OT pairs
			foreach( $ot_starts as $index => $ot_start ){
				if( (!empty($ot_start) && empty($ot_ends[$index]) ) || (!empty($ot_ends[$index]) && empty($ot_start) )) {
					$safe_to_save = false;
					$response->msg = 'Please complete all pairings of OT Time-in and Time-out';
					$response->msg_type = 'error';
				}
			}
			
			foreach( $dates as $index => $date ){
					$dtr_row = array();
					$date = date('Y-m-d', strtotime($date));
					$dtr_row['date'] = $date;
					$dtr_row['employee_id'] = $employee_id;
					$work_shift = $this->system->get_employee_worksched_shift($employee_id, $date);  

					if(!empty($work_shift))
						$dtr_row['shift_id'] = $work_shift->shift_id;	
					else
						$dtr_row['shift_id'] = NULL;

					if(!empty($time_in1[$index]))
						$dtr_row['time_in1'] = date('Y-m-d H:i:s', strtotime($time_in1[$index]));
					else
						$dtr_row['time_in1'] = NULL;

					if(!empty($time_in2[$index]))
						$dtr_row['time_in2'] = date('Y-m-d H:i:s', strtotime($time_in2[$index]));
					else
						$dtr_row['time_in2'] = NULL;

					if(!empty($time_out1[$index]))
						$dtr_row['time_out1'] = date('Y-m-d H:i:s', strtotime($time_out1[$index]));
					else
						$dtr_row['time_out1'] = NULL;

					if(!empty($time_out2[$index]))
						$dtr_row['time_out2'] = date('Y-m-d H:i:s', strtotime($time_out2[$index]));
					else
						$dtr_row['time_out2'] = NULL;

					//$dtr_row['awol'] = (isset($_POST['awol'][$index])) ? 1 : 0;

					if( !empty($ot_ends[$index]) && !empty($ot_starts[$index]) ){
						$dtr_ot_start = date('Y-m-d H:i:s', strtotime($ot_starts[$index]));
						$dtr_ot_end = date('Y-m-d H:i:s', strtotime($ot_ends[$index]));

						$dtr_time_in = date('Y-m-d H:i:s', strtotime($time_in1[$index]));
						$dtr_time_out = date('Y-m-d H:i:s', strtotime($time_out1[$index]));
						
						
						/* Check if Overtime out is not more than the actual out */
						if($dtr_ot_end > $dtr_time_out){
							$safe_to_save = false;
							$response->msg = 'Please check your Overtime Time-Out';
							$response->msg_type = 'error';
							break;
						}

						/* Check if Overtime out is not more than the actual out */
						if($dtr_ot_start > $dtr_ot_end){
							$safe_to_save = false;
							$response->msg = 'Please check your Overtime Time-In/Time-Out';
							$response->msg_type = 'error';
							break;
						}

						/* Check if Overtime out is not equal to Overtime In */
						if($dtr_ot_start == $dtr_ot_end){
							$safe_to_save = false;
							$response->msg = 'Please check your Overtime Time-In/Time-Out';
							$response->msg_type = 'error';
							break;
						}
						
						$dtr_ot_start_m = strftime("%H:%M",strtotime($dtr_ot_start));
						$dtr_sched_out = strftime("%H:%M",strtotime($work_shift->shifttime_end));
						
						/* Check if Overtime In is not less than the Shceduled Out */
						if($dtr_ot_start_m < $dtr_sched_out){
							$safe_to_save = false;
							$response->msg = 'Please check your Overtime Time-In';
							$response->msg_type = 'error';
							break;
						}
					}
			}
			
			if( $safe_to_save ){
				foreach( $dates as $index => $date ){
					$dtr_row = array();
					$date = date('Y-m-d', strtotime($date));
					$dtr_row['date'] = $date;
					$dtr_row['employee_id'] = $employee_id;
					$work_shift = $this->system->get_employee_worksched_shift($employee_id, $date);  
					$dtr = $this->db->get_where('employee_dtr', array('deleted' => 0, 'employee_id' => $employee_id, 'date' => $date) );

					$dtr_ot_start = date('Y-m-d H:i:s', strtotime($ot_starts[$index]));
					$dtr_ot_end = date('Y-m-d H:i:s', strtotime($ot_ends[$index]));

					$dtr_time_in = date('Y-m-d H:i:s', strtotime($time_in1[$index]));
					$dtr_time_out = date('Y-m-d H:i:s', strtotime($time_out1[$index]));

					if( $awol && isset($awol[$index]) ){
						if($dtr->num_rows() == 1){
								$this->db->update('employee_dtr',
								array('time_in1' => NULL, 'time_in2' => NULL,'time_out1' => NULL, 'time_out2' => NULL,'awol'=> 1),
								array('deleted' => 0, 'employee_id' => $employee_id, 'date' => $date) );

						}
						else{
							$dtr_row = array(
								'employee_id' => $employee_id,
								'date' => $date,
								'shift_id' =>  $work_shift->shift_id,
								'time_in1' => NULL,
								'time_in2' => NULL,
								'time_out1' => NULL,
								'time_out2' => NULL,
								'awol'=> 1

							);

							$this->db->insert('employee_dtr', $dtr_row);
							
						}

							$this->db->delete('employee_dtr_ot',array('employee_id' => $employee_id, 'date' => $date));
							$this->db->delete('employee_oot',array('employee_id' => $employee_id, 'date' => $date));
					}else{
						if(!empty($work_shift))
							$dtr_row['shift_id'] = $work_shift->shift_id;	
						else
							$dtr_row['shift_id'] = NULL;

						if(!empty($time_in1[$index]))
							$dtr_row['time_in1'] = date('Y-m-d H:i:s', strtotime($time_in1[$index]));
						else
							$dtr_row['time_in1'] = NULL;

						if(!empty($time_in2[$index]))
							$dtr_row['time_in2'] = date('Y-m-d H:i:s', strtotime($time_in2[$index]));
						else
							$dtr_row['time_in2'] = NULL;

						if(!empty($time_out1[$index]))
							$dtr_row['time_out1'] = date('Y-m-d H:i:s', strtotime($time_out1[$index]));
						else
							$dtr_row['time_out1'] = NULL;

						if(!empty($time_out2[$index]))
							$dtr_row['time_out2'] = date('Y-m-d H:i:s', strtotime($time_out2[$index]));
						else
							$dtr_row['time_out2'] = NULL;

						if($dtr->num_rows() == 1){
								$this->db->update('employee_dtr', $dtr_row, array('deleted' => 0, 'employee_id' => $employee_id, 'date' => $date) );
						}
						else{
							$this->db->insert('employee_dtr', $dtr_row);
						}

						if( !empty($ot_ends[$index]) && !empty($ot_starts[$index]) ){
							/* BEGIN [Update/Insert]*/
							$dtr_ot = $this->db->get_where('employee_dtr_ot', array('employee_id' => $employee_id, 'date' => $date));
							
							/* Update employee_dtr_ot*/
							/* if existing => update overtime -> employee_dtr_ot */
							if( $dtr_ot->num_rows() == 1){
								$this->db->update('employee_dtr_ot', array(
									'ot_start' => $dtr_ot_start , 'ot_end' => $dtr_ot_end 
									), array('date' => $date, 'employee_id' => $employee_id)
								);	
							}
							
							/* Insert into employee_dtr_ot*/
							/* if not exist => insert overtime -> employee_dtr_ot */
							else{
								$this->db->insert('employee_dtr_ot', array(
									'date' => $date, 'employee_id' => $employee_id, 'ot_start' => $dtr_ot_start , 'ot_end' => $dtr_ot_end
									)
								);
							}
							
							/* update time and time out if has changes */
							$dtr = $this->db->get_where('employee_dtr', array('deleted' => 0, 'employee_id' => $employee_id, 'date' => $date) );
							/* Update employee_dtr */
							if($dtr->num_rows() == 1){
								$this->db->update('employee_dtr', $dtr_row, array('deleted' => 0, 'employee_id' => $employee_id, 'date' => $date));
							}
							/* Insert into employee_dtr */
							else{
								$this->db->insert('employee_dtr', $dtr_row);
							}
							/* END */

							/* BEGIN */
							$dtr_oot = $this->db->get_where('employee_oot', array('employee_id' => $employee_id, 'date' => $date));

							$get_date_today = getdate();
							$date_today = "$get_date_today[year]-$get_date_today[mon]-$get_date_today[mday] $get_date_today[hours]:$get_date_today[minutes]:$get_date_today[seconds]";

							//$date_today = getdate(timestamp);

							/* Update employee_oot */
							if( $dtr_oot->num_rows() == 1){
								$this->db->update('employee_oot', array(
									'datetime_from' => $dtr_ot_start , 'datetime_to' => $dtr_ot_end, 
									'date_created' => $date_today, 'date_updated' => $date_today, 'date_approved' => $date_today, 
									), array('date' => $date, 'employee_id' => $employee_id)
								);	
							}

							/* Insert into employee_oot */
							else{
								$this->db->insert('employee_oot', array(
									'employee_id' => $employee_id, 'reason' => "Auto Overtime Application", 'date' => $date,
									'datetime_from' => $dtr_ot_start , 'datetime_to' => $dtr_ot_end, 
									'date_created' => $date_today, 'date_updated' => $date_today, 'date_approved' => $date_today, 
									'form_status_id' => 3, 'email_sent' => 0,
									'date_approved' => $date_today 
									)
								);
							}
						}
					}	

					$response->msg = 'DTR successfully saved  and updated.';
					$response->msg_type = 'success';
				}
			}
		}
		else{
			$response->msg = 'You dont have sufficient privilege, please contact the System Administrator.';
			$response->msg_type = 'error';
		}

		$data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		
	}



	/*
	 * The following are for DTR Manage Function - Leaves
	 */
	function get_affected_dates( $call_from_within = false ) {
		$this->leaves->_get_affected_dates( $call_from_within );
	}

	function get_employee_info() {
		$this->leaves->_get_employee_info();
	}

	function get_approvers(){

			$data['approvers'] = $this->system->get_approvers_and_condition( $this->input->post('employee_id'), $this->get_manage_module_id() );
			$response->approvers = $this->load->view($this->userinfo['rtheme'].'/forms/approvers', $data, true);

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_user_info() {
		$this->leaves->_get_user_info();
	}

	function get_leave_balance() {
		$this->leaves->_get_leave_balance();
	}

	function get_leave_type_dropdown()
	{
		$this->load->model('uitype_edit');
		$data['types'] = $this->uitype_edit->get_leave_dropdown( $this->input->post('user_id') );
		
		$this->load->view('template/ajax', array('json' => $data));		
	}

	function check_if_hra()
	{
		if (!IS_AJAX) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$leave_module = $this->hdicore->get_module('Leaves');
			$response->data = $this->user_access[$leave_module->module_id]['hr_health'];
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_ml_specifics()
	{
		$this->leaves->_get_ml_specifics();
	}

	function get_actual_delivery_date() {
		$this->leaves->_get_actual_delivery_date();
	}

	/*
	 * The following are for DTR Manage Function - OBT
	 */
	 function validation_check(){
        switch($this->input->post('form')){
        	case 'obt':
        		$this->obt->_validation_check();
        		break;
        	case 'oot':
        		$this->validation_check_OT();
        		break;
        }   
    }


    function validation_check_OT(){
        $err = false;
        $msg = "";          
        $employee_id = $this->input->post('employee_id');
        $date_f = date('Y-m-d',strtotime($this->input->post('datetime_from')));
        $date_time_from = date('Y-m-d G:i:s',strtotime($this->input->post('datetime_from')));
        $date_time_to = date('Y-m-d G:i:s',strtotime($this->input->post('datetime_to')));
        $time_from = date('G:i:s',strtotime($this->input->post('datetime_from')));
        $time_to = date('G:i:s',strtotime($this->input->post('datetime_to')));

        $shit_sched = $this->system->get_employee_worksched_shift($employee_id,$this->input->post('datetime_from'));
        $shift_start = $shit_sched->shifttime_start;
        $shift_end = $shit_sched->shifttime_end;
        $shift_id = $shit_sched->shift_id;
        $holiday = false;

        //check for holiday
        $date = date('Y-m-d',strtotime($this->input->post('datetime_from')));
        $date_to = date('Y-m-d',strtotime($this->input->post('datetime_to')));

        while ( strtotime($date) <= strtotime($date_to) ) {           
            $holiday_check = $this->system->holiday_check(date('Y-m-d',$date), $employee_id);
            if( $holiday_check ){
                $holiday = true;
            }                                            
            $date = date('Y-m-d' , mktime(0,0,0,date('m',$date),date('d',$date)+1,date('Y',$date)));                           
            $date = strtotime($date);
        } 

        if ($employee_id == ""):
            $err = true;
            $msg = "Please select employee.";
        else:
            $qry = "SELECT *
            FROM {$this->db->dbprefix}employee_dtr
            WHERE deleted = 0 AND employee_id = '{$employee_id }' AND date = '{$date_f}'";
            $result = $this->db->query( $qry );

            if ($result->num_rows() > 0):
                $row = $result->row();
                //check if overtime application is within the next cutoff
                $to_check = true; 

                if ($to_check):
                    if (date('Y-m-d') > date('Y-m-d',strtotime($date_time_from))):
                        if ($this->system->check_in_cutoff($date_time_from) == 0):
                            //if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id != 0 ) ) ):
                            if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id > 1 ) ) ):
                                $err = true;
                                $msg = "Invalid overtime schedule. Kindly check date and hours of overtime work applied.";
                                $response = $holiday." ".$shift_id;
                            else:
                                $qry = "SELECT *
                                FROM {$this->db->dbprefix}employee_oot
                                WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND
                                ( 
                                    ('{$date_time_from}' BETWEEN datetime_from AND datetime_to) OR
                                    ('{$date_time_to}' BETWEEN datetime_from AND datetime_to)                    
                                )";
                                $result = $this->db->query( $qry );                
                                if ($result->num_rows() > 0):                    
                                    $err = true;
                                    $msg = "Overtime application has already been filed.";
                                else:
                                    $err = false;
                                    $msg = "";
                                endif;
                            endif;              
                        elseif ($this->system->check_in_cutoff($date_time_from) == 1):
                            $err = true;
                            $msg = "Next payroll cutoff not yet created in processing, please contact admin.";
                        elseif ($this->system->check_in_cutoff($date_time_from) == 2):
                            $err = true;
                            $msg = "Your Overtime application is no longer within the allowable time.";                        
                        endif;
                    else:
                        //if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id != 0 ) ) ):
                        if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id > 1 ) ) ):
                            $err = true;
                            $msg = "Invalid overtime schedule. Kindly check date and hours of overtime work applied.";
                            $response = $holiday." ".$shift_id;
                        else:
                            $qry = "SELECT *
                            FROM {$this->db->dbprefix}employee_oot
                            WHERE deleted = 0 AND form_status_id=3 AND employee_id = '{$employee_id }' AND
                            ( 
                                ('{$date_time_from}' BETWEEN datetime_from AND datetime_to) OR
                                ('{$date_time_to}' BETWEEN datetime_from AND datetime_to)                    
                            )";
                            $result = $this->db->query( $qry );                
                            if ($result->num_rows() > 0):                    
                                $err = true;
                                $msg = "Overtime application has already been filed.";
                            else:
                                $err = false;
                                $msg = "";
                            endif;
                        endif; 
                    endif;
                endif;
            else:   
                //if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id != 0 ) ) ):
                if ( ( $time_from > $shift_start AND $time_from < $shift_end ) && ( ( !$holiday ) && ( $shift_id > 1 ) ) ):                    
                    $err = true;
                    $msg = "Invalid overtime schedule. Kindly check date and hours of overtime work applied.";
                    $response = $holiday." ".$shift_id;
                else:
                    //only approved application
                    $qry = "SELECT *
                    FROM {$this->db->dbprefix}employee_oot
                    WHERE deleted = 0 AND employee_id = '{$employee_id }' AND form_status_id=3 AND
                    ( 
                        ('{$date_time_from}' BETWEEN datetime_from AND datetime_to) OR
                        ('{$date_time_to}' BETWEEN datetime_from AND datetime_to)                    
                    )";
                    $result = $this->db->query( $qry );                
                    if ($result->num_rows() > 0):                    
                        $err = true;
                        $msg = "Overtime application has already been filed.";
                    else:
                        $err = false;
                        $msg = "";
                    endif;               
                endif;             
            endif;        
        endif;

        //validate if within allowable time upon selecting of date
        if ($this->config->item('maxtime_to_apply_overtime') && $this->config->item('maxtime_to_apply_overtime') != '' && $this->config->item('maxtime_to_apply_overtime') != 0):
            $dates_affected = $this->system->get_affected_dates( $this->input->post('employee_id'), $this->input->post('date'), date('Y-m-d'), true, true );
                if (count($dates_affected) > $this->config->item('maxtime_to_apply_overtime')):
                    $err = true;
                    $msg = "Your application exceeded with the allowable time to apply."; 
                    $to_check = false; 
                endif;                  
        endif;

        $this->load->view('template/ajax', 
            array('json' => 
                array('err' => $err, 'msg_type' => $msg)
            )
        );   
    }
    function get_employee_sched( $return = false ){
        $this->cws->_get_employee_sched( $return );
    }

    function get_inclusive_worksched(){
        $this->oot->_get_inclusive_worksched();
    }

    function change_leave_status($record_id = 0, $non_ajax = 0) {
		$this->leaves->_change_status( $record_id, $non_ajax );
	}

	function change_oot_status(){
		if (!IS_AJAX) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		if($this->user_access[$this->get_manage_module_id()]['post'] != 1){
			$response->msg_type = 'error';
			$response->msg 		= 'You dont have sufficient privilege.';

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		$this->db->update('employee_oot', array('form_status_id' => $this->input->post('form_status_id')), array('employee_oot_id' => $this->input->post('record_id')));
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = 'error';
		}
		else{
			$response->msg = "Success!";
			$response->msg_type = 'success';		
		}
		
		$this->load->view('template/ajax', array('json' => $response));
	}

	function change_obt_status(){
		if (!IS_AJAX) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		if($this->user_access[$this->get_manage_module_id()]['post'] != 1){
			$response->msg_type = 'error';
			$response->msg 		= 'You dont have sufficient privilege.';

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		$this->db->update('employee_obt', array('form_status_id' => $this->input->post('form_status_id')), array('employee_obt_id' => $this->input->post('record_id')));
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = 'error';
		}
		else{
			$response->msg = "Success!";
			$response->msg_type = 'success';		
		}
		
		$this->load->view('template/ajax', array('json' => $response));
	}

	function change_cws_status(){
		if (!IS_AJAX) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		if($this->user_access[$this->get_manage_module_id()]['post'] != 1){
			$response->msg_type = 'error';
			$response->msg 		= 'You dont have sufficient privilege.';

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		$this->db->update('employee_cws', array('form_status_id' => $this->input->post('form_status_id')), array('employee_cws_id' => $this->input->post('record_id')));
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = 'error';
		}
		else{
			$response->msg = "Success!";
			$response->msg_type = 'success';		
		}
		
		$this->load->view('template/ajax', array('json' => $response));
	}

    // END custom module funtions

}
/* End of file */
/* Location: system/application */
