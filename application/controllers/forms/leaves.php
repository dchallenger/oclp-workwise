<?php

/**
 * Description of Leaves
 *
 * @author jconsador
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Leaves extends MY_Controller {

	function __construct() {
		parent::__construct();
		
		$this->load->model('forms/leaves_model', 'leaves');

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title         = $this->module_name; 
		$this->listview_description   = '';
		$this->jqgrid_title           = "";
		$this->detailview_title       = '';
		$this->detailview_description = '';
		$this->editview_title         = 'Add/Edit ' . $this->module_name;
		$this->editview_description   = '';
		$this->default_sort_col       = array('date_created desc');
		
		if( in_array($this->method, array('index', 'listview')) ){

			if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1){
	            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";
	        }

	        //for approval
	        $leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '!= 1', 'in (0,1)', false, $this->user_access[$this->module_id]['project_hr'] );
	        if( $leaves_to_approve ){
	        	if( $this->input->post('filter') && $this->input->post('filter') == "for_approval" ){
	        		$leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '!= 1', '= 1', false, $this->user_access[$this->module_id]['project_hr'] );

		        	if( ( !$this->is_superadmin && !$this->is_admin ) &&  $this->user_access[$this->module_id]['post'] != 1){
		        		$this->db->join('leave_approver','leave_approver.leave_id = '.$this->module_table.'.employee_leave_id');
		        		$this->db->where('leave_approver.focus',1);
		        		$this->db->where('leave_approver.status',2);
		        		$this->db->where('leave_approver.approver',$this->user->user_id);
		        	}
		        	
	        		$where = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).")";
	        		$this->db->where($where);
	        		$this->db->where('form_status_id', 2);
	        		$chk_leaves = $this->db->get($this->module_table);

	        		if ($chk_leaves && $chk_leaves->num_rows() > 0) {
	        			$counter = array();
	        			
	        			foreach ($chk_leaves->result() as $app) {
	        				$counter[] = $app->employee_leave_id;
/*	        				$check = $this->system->check_in_cutoff($app->date_from);
			        		if ($check != 2) {
			        			$counter[] = $app->employee_leave_id;
		        			}*/
	        			}
	        			$leaves_to_approve = $counter;
	        		}

	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 2";	

	        	}

	        	if( $this->input->post('filter') && $this->input->post('filter') == "approved" ){
	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 3";	
	        	}

	        	if( $this->input->post('filter') && $this->input->post('filter') == "disapproved" ){
	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 4";	
	        	}

	        	if( $this->input->post('filter') && $this->input->post('filter') == "cancelled" ){
	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 5";	
	        	}

	        	if( $this->input->post('filter') && $this->input->post('filter') == "invalid" ){
	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 8";	
	        	}	        	
	        }

	        if( $this->input->post('filter') && $this->input->post('filter') == "personal" ){
	            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";    
	        }

	        if( $this->input->post('filter') && $this->input->post('filter') == "employees" )
{	            $this->filter = $this->module_table.".employee_id <> {$this->user->user_id}";  
	            if (!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1){
	            	if ($leaves_to_approve){
						$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).")";	
					}else{
						$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (0)";	
					}
	            }  	              
	        }        

	        if($this->input->post('filter') && $this->input->post('filter') == "subordinates" && $this->user_access[$this->module_id]['post'] == 1){
				$pos_subordinates = $this->db->get_where('user_position', array("reporting_to" => $this->userinfo['position_id']))->result();
				foreach($pos_subordinates as $pos_sub)
				{
					$sub_ids = $this->db->get_where('user', array("position_id" => $pos_sub->position_id))->result();
	        		foreach ($sub_ids as $sub_id) 
        				$subs[] = $sub_id->employee_id;
        		}

        		if (count($subs) > 0){	// tirso - to check if $subs is not empty
	        		$this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .")";
	        	}
	        	
		        if (CLIENT_DIR == 'firstbalfour'){
			        if (!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1){
			        	if ($leaves_to_approve){
							$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).")";	
			        	}else{
			        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (0)";
			        	}
			        }
		        }	        	
	        }

	        if( $this->input->post('filter') && $this->input->post('filter') == "for_validation" && ($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'])){
	            $this->filter = $this->module_table.".form_status_id = 6";    
	        }

	        if( $this->input->post('filter') && $this->input->post('filter') == "maternity" && ($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'])){
	            $this->filter = $this->module_table.".application_form_id = 5";    
	        }
        }

        if (CLIENT_DIR == 'firstbalfour'){
	        if (!$this->input->post('filter') && !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1){
	        	if ($leaves_to_approve){
					$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).")";	
				}else{
					$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (0)";	
				}
	        }
        }    

        $this->default_sort_col = array('t0firstnamemiddleinitiallastnameaux');
	}

	function _set_filter() {
		$this->db->where('IF(' . $this->db->dbprefix . $this->module_table .'.employee_id = ' . $this->userinfo['user_id'] . ', 1, '.$this->db->dbprefix . $this->module_table .'.form_status_id <> 1)', '', false);
	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['content'] = $this->module_link.'/listview';
		$data['jqgrid'] = $this->module_link.'/jqgrid';

		//Tabs for Listview

        $tabs = array();
        if( ( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1){
        	//to get for hr validation application form
        	$leaves_array = array();
	    	$qry = "SELECT a.*
			FROM {$this->db->dbprefix}leave_approver a
			LEFT JOIN {$this->db->dbprefix}employee_leaves b ON b.employee_leave_id = a.leave_id
			WHERE {$this->user->user_id} AND b.form_status_id = 6 AND a.focus {$focus} AND
			b.deleted = 0 AND a.status = 6";
	        $leaves_to_validate = $this->db->query( $qry );
	       
	        if( $leaves_to_validate->num_rows() > 0 ){
	        	foreach( $leaves_to_validate->result() as $leave ){
	        		$leaves_array[] = $leave->leave_id;
	        	}
	        }    

			$for_validation_count = 0;
	        if (count($leaves_array) > 0){
	        	$this->db->where('form_status_id', 6);
	        	$this->db->where_in('employee_leave_id', $leaves_array);
	        	$for_validation = $this->db->get( $this->module_table );

	        	if ($for_validation  && $for_validation ->num_rows() > 0){
	        		$for_validation_count = $for_validation->num_rows();
	        	}	        	
        	}
        	//end to get for hr validation application form

            $data['filter'] = 'employees';
            $tabs[] = '<li filter="personal"><a href="javascript:void(0)">Personal</li>';
            $tabs[] = '<li filter="employees" class="active"><a href="javascript:void(0)">Employees</li>';
            $tabs[] = '<li filter="maternity"><a href="javascript:void(0)">Maternity</li>';
            $tabs[] = '<li filter="for_validation"><a href="javascript:void(0)">For Validation <span class="bg-orange ctr-inline" id="approval-counter">' . $for_validation_count . '</span></li>';
            $tabs[] = '<li filter="subordinates"><a href="javascript:void(0)">Subordinates</li>';
        }
        else if( $this->user_access[$this->module_id]['hr_health'] == 1 ){
        	$data['filter'] = 'personal';
        	$tabs[] = '<li filter="personal"><a href="javascript:void(0)">Personal</li>';
        	$tabs[] = '<li filter="maternity"><a href="javascript:void(0)">Maternity</li>';
        	$tabs[] = '<li filter="for_validation"><a href="javascript:void(0)">For Validation</li>';
        	if($this->user_access[$this->module_id]['publish'] == 1) $tabs[] = '<li filter="employees"><a href="javascript:void(0)">Employees</li>';
        }
        else{
            $data['filter'] = 'personal';
            $tabs[] = '<li class="active" filter="personal"><a href="javascript:void(0)">Personal</li>';
            if($this->user_access[$this->module_id]['publish'] == 1) $tabs[] = '<li filter="employees"><a href="javascript:void(0)">Employees</li>';
        }

        //for approval
        $leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '!= 1', 'in (0,1)' );
        if( $leaves_to_approve ){
        	$leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '= 2', '= 1' );
        	$this->db->where('form_status_id', 2);
        	$this->db->where_in('employee_leave_id', $leaves_to_approve);
        	$for_approval = $this->db->get( $this->module_table );
        	$approval_counter = "";
        	$counter = array();
        	if ($for_approval && $for_approval->num_rows() > 0) {

        		foreach ($for_approval->result() as $app) {
/*	        		$check = $this->system->check_in_cutoff($app->date_from);
	        		if ($check != 2) {
	        			$counter[] = $app->employee_leave_id;
        			}*/
        			$counter[] = $app->employee_leave_id;
        		}
        	}
        	

        	if(  $for_approval->num_rows() > 0 ) $approval_counter = '<span class="bg-orange ctr-inline" id="approval-counter">' . count($counter) . '</span>';
        	$tabs[] = '<li filter="for_approval"><a href="javascript:void(0)">For Approval '. $approval_counter .'</li>';
        	$tabs[] = '<li filter="approved"><a href="javascript:void(0)">Approved</li>';
        	$tabs[] = '<li filter="disapproved"><a href="javascript:void(0)">Disapproved</li>';
        	$tabs[] = '<li filter="cancelled"><a href="javascript:void(0)">Cancelled</li>';

        	if (CLIENT_DIR == 'firstbalfour'){
				$tabs[] = '<li filter="invalid"><a href="javascript:void(0)">Invalid</li>';        		
        	}	
        }

        if( sizeof( $tabs ) > 1 ) $data['leave_tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = 'init_filter_tabs();';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function detail() {
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';

		$data['content'] = 'leaves/detailview';
		$data['record'] = $this->db->get_where($this->module_table, array( $this->key_field => $this->key_field_val ))->row();
		//other views to load
		$data['views'] = array();

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
		}

		$data['record_id'] = $this->input->post('record_id');

		// Get leave dates
		$this->db->select('date,duration,cancelled,date_cancelled,employee_leave_date_id,remarks');
		$this->db->join('employee_leaves_duration', 'employee_leaves_dates.duration_id = employee_leaves_duration.duration_id', 'left');
		$this->db->where( array( $this->key_field => $this->key_field_val ) );
		$result = $this->db->get('employee_leaves_dates');
		
		if ($result->num_rows() > 0) {
			$data['dates_affected'] = $result->result_array();
		}

		//check status
		$data['rec'] = $rec = $this->db->get_where( $this->module_table, array( $this->key_field => $this->input->post('record_id') ) )->row();
		if( $rec->form_status_id == 2 ){
			if( $rec->employee_id == $this->user->user_id ){
				$data['buttons'] = 'template/detail-no-buttons';
			}
			else if($this->user_access[$this->module_id]['post'] == 1){
				// check if hr is approver
				$approver = $this->db->get_where('leave_approver', array('approver' => $this->user->user_id, 'leave_id' => $this->key_field_val));
				$approver = $approver->row();
				if( $approver->status == 2 ){
					$data['buttons'] = 'leaves/approve-button';
				}
				else{
					if (CLIENT_DIR == 'firstbalfour'){
						$data['buttons'] = 'leaves/approve-button';
					}
				}
			} else if($this->user_access[$this->module_id]['publish'] == 1) {
				$data['buttons'] = 'template/detail-no-buttons';
				// check if user(publish) is approver
				$approver = $this->db->get_where('leave_approver', array('approver' => $this->user->user_id, 'leave_id' => $this->key_field_val));
				$approver = $approver->row();
				if( $approver->status == 2 ){
					$data['buttons'] = 'leaves/approve-button';
				}
			} else{
				//check for approver buttons
				$approver = $this->db->get_where('leave_approver', array('approver' => $this->user->user_id, 'leave_id' => $this->key_field_val));
				if( $approver->num_rows() == 0 && !($this->is_admin || $this->is_superadmin)){
					$this->session->set_flashdata( 'flashdata', 'You do not have sufficient privilege to view the requested record! Please contact the System Administrator.' );
					redirect( base_url().$this->module_link );	
				}

				$approver = $approver->row();
				if( $approver->status == 2 ){
					$data['buttons'] = 'leaves/approve-button';
				}
				else{
					$data['buttons'] = 'template/detail-no-buttons';
				}
			}
		}

		// to prevent edit button on employee's with publish
		if( $rec->form_status_id == 3 && $this->user_access[$this->module_id]['publish']){
			$data['buttons'] = 'template/detail-no-buttons';
		}

		if( $rec->form_status_id == 3 && $rec->employee_id != $this->user->user_id && $this->user_access[$this->module_id]['cancel'] == 1 ){
			$data['buttons'] = 'leaves/cancel-button';
		}

		if( $rec->form_status_id == 3 && $rec->employee_id == $this->user->user_id ){
			$data['buttons'] = 'template/detail-no-buttons';
		}

		if( $rec->form_status_id > 3 || ( $rec->form_status_id == 1 && $rec->employee_id != $this->user->user_id ) ) $data['buttons'] = 'template/detail-no-buttons';

		if( $rec->form_status_id == 6 && $rec->employee_id != $this->user->user_id && ($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'] == 1) ){
			$data['buttons'] = 'leaves/validate-buttons';
		}

		$this->db->where('employee_id', $rec->employee_id);
		$this->db->where('year', date('Y'));
		$this->db->where('deleted', 0);

		

		$data['balance'] = $this->db->get('employee_leave_balance')->row();

		$this->db->order_by('sequence', 'asc');
        $approvers = $this->db->get_where( 'leave_approver', array('leave_id' => $this->key_field_val));
        foreach( $approvers->result() as $row ){
            $data['approvers'][] = array(
                'approver' => $row->approver,
                'sequence' => $row->sequence,
                'condition' => $row->condition,
                'focus' => $row->focus,
                'status' => $row->status
            );
        }

		//load variables to env			
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function edit() {
		if ($this->user_access[$this->module_id]['edit'] == 1) {
			$this->load->helper('form');
			if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
			
			$data['form_status_id'] = "";

			if( $this->input->post('record_id') != -1 ){
				//check status
				$rec = $this->db->get_where( $this->module_table, array( $this->key_field => $this->input->post('record_id') ) )->row();
				if( $rec->form_status_id != 1 ){
					if( $rec->application_form_id != 5 && $this->user_access[$this->module_id]['hr_health'] != 1 ){
						$this->session->set_flashdata( 'flashdata', 'Data is locked for editing, please call the Administrator.' );
						redirect( base_url().$this->module_link.'/detail/'. $this->input->post('record_id') );
					}
				}

				$data['form_status_id'] = $rec->form_status_id;

				$data['approvers'] = $this->system->get_approvers_and_condition( $rec->employee_id, $this->module_id );
			}

			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
			$data['scripts'][] = multiselect_script();

			if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'leaves/editview';
			if( $_POST['filter'] == "employees" || $_POST['filter'] == "personal" || $_POST['filter'] == "for_approval" || $rec->form_status_id == 1 )
				$data['buttons'] = 'leaves/template/send-request';
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$data['record_id'] = $this->input->post('record_id');

			if ($this->input->post('record_id') != '-1') {
				$this->db->where($this->key_field, $this->input->post('record_id'));
				$record = $this->db->get($this->module_table)->row_array();

				$data['email_sent'] = $record['email_sent'];
			}

			if( $this->input->post('record_id') != '-1' ){
				$dates_affected = $this->db->get_where('employee_leaves_dates', array( $this->key_field => $this->key_field_val));
				$data['dates_affected'] = $dates_affected->result_array();
			}

			//load variables to env
			$this->load->vars($data);
			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_leave_balance() {
		$this->leaves->_get_leave_balance();
	}

	function ajax_save() {
		// if ($bypass) {parent::ajax_save(); return;}

		$this->load->helper('date');
		$application_form_id = $this->input->post('application_form_id');
		
		if( ($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'] == 1) && $_POST['filter'] != 'personal' ){
			$no_validation = true;
			//$validate_leave = true;
		}
		else{
			$no_validation = false;	
		}

		// Validate leave credits
		// Note: This validation must not be bypass by anyone

		$validate_leave = array();
		$leave_duration = 0;
    	$next_leave_duration = 0;
    	$prev_leave_duration = 0;
    	$total_pending_leave = 0;
    	$total_pending_leave_next_year = 0;
    	$next_year=date('Y')+1;
    	$prev_year=date('Y')-1;
    	if( $this->input->post('duration_id') ){
        	$durations = $this->input->post('duration_id');

        	$year_check_ctr = 0;
			$affected_dates=$this->input->post('dates');
			$next_year_flag=false;
			$prev_year_flag=false;

        	foreach( $durations as $duration ){
        		if($next_year == date('Y', strtotime($affected_dates[$year_check_ctr])))
        			$next_year_flag=true;
        		if($prev_year == date('Y', strtotime($affected_dates[$year_check_ctr])))
        			$prev_year_flag=true;			        		

        		if(CLIENT_DIR == 'oams') {
        			$cred_dur = $this->system->get_cred_duration($this->input->post('employee_id'), date('Y-m-d',strtotime($affected_dates[$year_check_ctr])));
        			switch($duration)
        			{
        				case 1:
        					$day_credit = $cred_dur->total_work_hours;
        					break;
        				case 2:
        					$day_credit = $cred_dur->total_first_half;
        					break;
        				case 3:
        					$day_credit = $cred_dur->total_second_half;
        					break;
        			}
        		} else {
        			$dur = $this->db->get_where('employee_leaves_duration', array('duration_id' => $duration))->row();
        			$day_credit = $dur->credit;
        		}
        		
        		$cred = $day_credit / 8;

        		if( !$prev_year_flag && !$next_year_flag){
        			$leave_duration += $cred;
        		}else{
	        		(!$prev_year_flag && $next_year_flag ? $next_leave_duration += $cred : $leave_duration += $cred);
	        		(!$next_year_flag && $prev_year_flag ? $prev_leave_duration += $cred : $leave_duration += $cred);
        		}
        		
        		$year_check_ctr++;
        	}
        }

        $year_date = date('Y');

		if (CLIENT_DIR == 'firstbalfour') {
    		$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('employee_id')))->row();
    		$this->db->where('FIND_IN_SET('.$employee->status_id.', employment_status_id)');
        	$this->db->where('employee_type_id', $employee->employee_type);
        	$this->db->where('application_form_id', $application_form_id);
        	$this->db->where('deleted',0);
        	$leave_reset_setup = $this->db->get('employee_type_leave_setup');
        	
        	if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
    			$leave_reset = $leave_reset_setup->row();
    			$dateto =  date('Y-m-d', strtotime($this->input->post('date_to')));
    			$datefrom =  date('Y-m-d', strtotime($this->input->post('date_from')));

    			if (date('Y', strtotime($leave_reset->leave_reset_date)) == date('Y')) {
					$year_date = date('Y');
				}
				elseif ($dateto <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
    				$year_date = date('Y', strtotime($leave_reset->leave_reset_date))-1;
    				$app_year = $year_date;
    				

    			}
    			elseif ($datefrom <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date < $dateto  && $leave_reset->leave_reset_date != NULL ) {
    				$year_date = date('Y', strtotime($leave_reset->leave_reset_date))-1;
    				$app_year = $year_date;

    			}
    				
    		}
     	
		}
		
    	$date_diff = $this->hdicore->compare_date( $this->input->post('date_from'), $this->input->post('date_to') );
    	
    	$leave_balance = $this->system->get_leave_balance( $year_date, $this->input->post('employee_id') );

    	$nn_year=date('Y')+1;
    	$next_year_leave_balance = $this->system->get_leave_balance( $nn_year, $this->input->post('employee_id') );
    	$prev_year_leave_balance = $this->system->get_leave_balance( $prev_year, $this->input->post('employee_id') );

    	$date_from = date('Y-m-d',strtotime($this->input->post('date_from')));
    	$date_to = date('Y-m-d',strtotime($this->input->post('date_to')));

    	//Check multiple leave application
    	$this->db->where('(( date_from BETWEEN "'.$date_from.'" AND "'.$date_to.'" ) || ( date_to BETWEEN "'.$date_from.'" AND "'.$date_to.'" ))');
    	//$this->db->where('application_form_id',$this->input->post('application_form_id'));
    	$this->db->where('employee_id',$this->input->post('employee_id'));
    	$this->db->where_in('form_status_id',array(2,3,6));
    	$this->db->where('deleted', 0);
    	$same_result = $this->db->get('employee_leaves');

    	if( $same_result->num_rows() > 0 ){

    		// if( $same_result->num_rows() == 1 && $this->input->post('application_form_id') == '5' ){
    			// allow edit
    		// }
    		// else{
/*    			$tmp_msg = "You already have a pending leave application on the set date.";
    			$validate_leave[] = $tmp_msg;*/

    			$invalid = false;
    			$dates = $this->input->post('dates');
    			$durations = $this->input->post('duration_id');

    			if ($same_result->num_rows() > 0){
    				$rows = $same_result->result();

    				$leave_dates_array = array();
    				
					foreach ($rows as $row){
	        			$pending_leaves_date_affected = $this->db->get_where('employee_leaves_dates', array('employee_leave_id' => $row->employee_leave_id,'cancelled'=>0))->result();
	        			foreach($pending_leaves_date_affected as $affected_leave_dates)
	        			{

	        				foreach ($dates as $key => $value) {

			    				$ds = date('Y-m-d',strtotime($value));

			    				if ( $ds == $affected_leave_dates->date ) {
			    					$duration_id = $affected_leave_dates->duration_id;
			    					if ($duration_id == $durations[$key] || $durations[$key] == 1 || $duration_id == 1){
			    						$invalid = true;
			    					}
			    				}
			    			}
	        			}   	
        			}			
    			}
    			
    			if(($row->form_status_id == 3) && $invalid){
    				$tmp_msg = "You already have a leave application on the set date.";
	    			$validate_leave[] = $tmp_msg;
    			}elseif ($invalid){
	    			$tmp_msg = "You already have a pending leave application on the set date.";
	    			$validate_leave[] = $tmp_msg;
    			}

    		// }
    	}

    	switch( $application_form_id ){
			case 1:

				$pending_leaves = $this->db->get_where($this->module_table,array( 'employee_id' => $this->input->post('employee_id'), 'application_form_id' => $application_form_id, 'form_status_id' => 2, 'deleted' => 0 ));
	        	$total_pending_leave = 0;
	        	$total_pending_leave_next_year = 0;
				$total_pending_leave_prev_year = 0;

				if($pending_leaves && $pending_leaves->num_rows() > 0) {
	        	$pending_leaves = $pending_leaves->result();
	        		foreach($pending_leaves as $emp_leave_id)
	        		{
	        			$pending_leaves_date_affected = $this->db->get_where('employee_leaves_dates', array('employee_leave_id' => $emp_leave_id->employee_leave_id,'cancelled'=>0))->result();
	        			foreach($pending_leaves_date_affected as $affected_leave_dates)
	        			{
	        				if(date('Y', strtotime($affected_leave_dates->date)) == date('Y')){
/*	        					$check = $this->system->check_in_cutoff($affected_leave_dates->date);
				        		if ($check != 2) {
									$total_pending_leave += $affected_leave_dates->credit;
								}*/
								$total_pending_leave += $affected_leave_dates->credit;
	        				}
		        			else if(date('Y', strtotime($affected_leave_dates->date)) == $next_year){
		        				$total_pending_leave_next_year += $affected_leave_dates->credit;
		        			}
		        			else if(date('Y', strtotime($affected_leave_dates->date)) == $prev_year){
		        				$total_pending_leave_prev_year += $affected_leave_dates->credit;						        			
		        			}
		        		}
		        	}
		        }

	        	$used = $leave_balance->sl_used + $leave_duration + $total_pending_leave;
	        	$next_used = $next_year_leave_balance ? $next_year_leave_balance->sl_used + $next_leave_duration + $total_pending_leave_next_year : 0;
	        	$prev_used = $prev_year_leave_balance ? $prev_year_leave_balance->sl_used + $prev_leave_duration + $total_pending_leave_prev_year : 0;	        	
	        
		        $year_from = date('Y', strtotime($this->input->post('date_from')));
		        $year_to = date('Y', strtotime($this->input->post('date_to')));

		        if($year_from == date('Y') && date('Y') == $year_to)
		        {
		        	if($used > $leave_balance->sl)  {
	        			//$tmp_msg = "You do not have enough sick leave balance.";

	        			$tmp_msg = "You have ".$total_pending_leave." pending leave applications and you don't have enough leave balance. Please file as LWOP";
	        			
	        			if ($leave_duration >= 4) {
	        				$tmp_msg .= ' Please coordinate with HRA Health Services if you can apply for SSS Sickness Benefit.';
	        			}

	        			$validate_leave[] = $tmp_msg;
	        		}
		        } else {
		        	if (CLIENT_DIR == 'firstbalfour') {
		        		$next_year_leave_balance_sl = $next_year_leave_balance->sl;
		        	    	if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
				    			$leave_reset = $leave_reset_setup->row();
				    			$dateto =  date('Y-m-d', strtotime($this->input->post('date_to')));

				    			if ($dateto <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
				    				if ($next_year_leave_balance==false) {
				    					$next_year_leave_balance = true;
				    					$next_year_leave_balance_sl = $leave_balance->sl;
				    				}
				    				
				    			}
				    				
				    		}
				    	if( $used > $leave_balance->sl || ($next_used > $next_year_leave_balance_sl && $next_year_leave_balance!=false) || ($prev_used > $prev_year_leave_balance->sl && $prev_year_leave_balance!=false) || ($next_used != 0 && $next_year_leave_balance==false) )  {
		        			//$tmp_msg = "You do not have enough sick leave balance.";

		        			$tmp_msg = "You have ".$total_pending_leave." pending leave applications and you don't have enough leave balance. Please file as LWOP";
		        			
		        			if ($leave_duration >= 4) {
		        				$tmp_msg .= ' Please coordinate with HRA Health Services if you can apply for SSS Sickness Benefit.';
		        			}

		        			$validate_leave[] = $tmp_msg;
		        		}

		        	}else{
			        	if( $used > $leave_balance->sl || ($next_used > $next_year_leave_balance->sl && $next_year_leave_balance!=false) || ($prev_used > $prev_year_leave_balance->sl && $prev_year_leave_balance!=false) || ($next_used != 0 && $next_year_leave_balance==false) )  {
		        			//$tmp_msg = "You do not have enough sick leave balance.";

		        			$tmp_msg = "You have ".$total_pending_leave." pending leave applications and you don't have enough leave balance. Please file as LWOP";
		        			
		        			if ($leave_duration >= 4) {
		        				$tmp_msg .= ' Please coordinate with HRA Health Services if you can apply for SSS Sickness Benefit.';
		        			}

		        			$validate_leave[] = $tmp_msg;
		        		}
	        		}
	        	}

			break;
			case 2:	
				$pending_leaves = $this->db->get_where($this->module_table,array( 'employee_id' => $this->input->post('employee_id'), 'application_form_id' => $application_form_id, 'form_status_id' => 2, 'deleted' => 0 ));
	        	$total_pending_leave = 0;
	        	$total_pending_leave_next_year = 0;

	        	$total_pending_leave_prev_year = 0;
	        	if($pending_leaves && $pending_leaves->num_rows() > 0) {
	        	$pending_leaves = $pending_leaves->result();
	        		foreach($pending_leaves as $emp_leave_id)
	        		{
	        			$pending_leaves_date_affected = $this->db->get_where('employee_leaves_dates', array('employee_leave_id' => $emp_leave_id->employee_leave_id,'cancelled'=>0))->result();
	        			foreach($pending_leaves_date_affected as $affected_leave_dates)
	        			{
	        				$check = $this->system->check_in_cutoff($affected_leave_dates->date);
	        				if(date('Y', strtotime($affected_leave_dates->date)) == date('Y')){
/*				        		if ($check != 2) {
				        			$total_pending_leave += $affected_leave_dates->credit;
			        			}*/
								$total_pending_leave += $affected_leave_dates->credit;			        			
	        				}
		        			else if(date('Y', strtotime($affected_leave_dates->date)) == $next_year){
		        				$total_pending_leave_next_year += $affected_leave_dates->credit;
		        			}
		        			else if(date('Y', strtotime($affected_leave_dates->date)) == $prev_year){
		        				$total_pending_leave_prev_year += $affected_leave_dates->credit;						        			
		        			}
		        		}
		        	}
		        }
		       
	        	$used = ($leave_balance->vl_used+$leave_balance->el_used) + $leave_duration + $total_pending_leave;
	        	// 
	        	$next_used = ($next_year_leave_balance->vl_used+$next_year_leave_balance->el_used) + $next_leave_duration + $total_pending_leave_next_year;	        
	        	$prev_used = ($prev_year_leave_balance->vl_used+$prev_year_leave_balance->el_used) + $prev_leave_duration + $total_pending_leave_prev_year;	 
	        	$next_year_leave_balance_vl = $next_year_leave_balance->vl;
	        	
	        	$year_from = date('Y', strtotime($this->input->post('date_from')));
		        $year_to = date('Y', strtotime($this->input->post('date_to')));
		        if($year_from == date('Y') && date('Y') == $year_to)
		        {
		        	if($used > $leave_balance->vl){ 
		        		$validate_leave[] = "You do not have enough vacation leave balances, please apply for Leave Without Pay.";
		        	}
		        } else {
		        	if (CLIENT_DIR == 'firstbalfour') {
		        		$next_year_leave_balance_vl = $next_year_leave_balance->vl;
		        	    if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
				    			$leave_reset = $leave_reset_setup->row();
				    			$dateto =  date('Y-m-d', strtotime($this->input->post('date_to')));

				    			if ($dateto <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
				    				if ($next_year_leave_balance==false) {
				    					$next_year_leave_balance = true;
				    					$next_year_leave_balance_vl = $leave_balance->vl;
				    				}
				    				
				    			}
				    				
				    	}

				    	$check_date = $this->system->check_in_cutoff(date('Y-m-d', strtotime($this->input->post('date_from'))));
				    	
				    	if( $used > $leave_balance->vl || ($next_used > $next_year_leave_balance_vl && $next_year_leave_balance!=false) || ($next_used != 0 && $next_year_leave_balance==false)){ 

				    		$validate_leave[] = "You do not have enough vacation leave balances, please apply for Leave Without Pay.";
				    	}
				    	elseif (($prev_used > $prev_year_leave_balance->vl && $prev_year_leave_balance!=false)) {
				    		if ($check_date == 2) {
				    			$validate_leave[] = "Your vacation leave application is no longer within the allowable time to apply.";
				    		}
				    		 
				    	}


		        	}else{
		        	
		        		if( ($next_used > $next_year_leave_balance->vl && $next_year_leave_balance!=false) || ($prev_used > $prev_year_leave_balance->vl && $prev_year_leave_balance!=false) || ($next_used != 0 && $next_year_leave_balance==false)) 
		        		{
		        				$validate_leave[] = "You do not have enough vacation leave balances, please apply for Leave Without Pay.";
		        		}

		        		if ($year_from == date('Y') && date('Y') != $year_to) {
		        			$used_duration = $used - $next_used;
		        			if ($used_duration > $leave_balance->vl) {
		        				$validate_leave[] = "You do not have enough vacation leave balances, please apply for Leave Without Pay.";
		        			}
		        		}
		        	}
        		}

			break;
			case 3:
	        	$total_pending_leave = 0;
	        	$total_pending_leave_next_year = 0;
	        	$total_pending_leave_prev_year = 0;			
				if (CLIENT_DIR == 'pioneer'){
					$pending_leaves = $this->db->get_where($this->module_table,array( 'employee_id' => $this->input->post('employee_id'), 'application_form_id' => $application_form_id, 'form_status_id' => 2, 'deleted' => 0 ));
		        	if($pending_leaves && $pending_leaves->num_rows() > 0) {
		        	$pending_leaves = $pending_leaves->result();
		        		foreach($pending_leaves as $emp_leave_id)
		        		{
		        			$pending_leaves_date_affected = $this->db->get_where('employee_leaves_dates', array('employee_leave_id' => $emp_leave_id->employee_leave_id,'cancelled'=>0))->result();
		        			foreach($pending_leaves_date_affected as $affected_leave_dates)
		        			{
		        				if(date('Y', strtotime($affected_leave_dates->date)) == date('Y')){
/*		        					$check = $this->system->check_in_cutoff($affected_leave_dates->date);
				        			if ($check != 2) {
				        				$total_pending_leave += $affected_leave_dates->credit;
									}*/
			        				$total_pending_leave += $affected_leave_dates->credit;
		        				}
			        			else if(date('Y', strtotime($affected_leave_dates->date)) == $next_year){
			        				$total_pending_leave_next_year += $affected_leave_dates->credit;
			        			}
			        			else if(date('Y', strtotime($affected_leave_dates->date)) == $prev_year){
			        				$total_pending_leave_prev_year += $affected_leave_dates->credit;						        			
			        			}
			        		}
			        	}
			        }					
				}		

				$used = ($leave_balance->vl_used+$leave_balance->el_used) + $leave_duration + $total_pending_leave;
        		$next_used = ($next_year_leave_balance->vl_used+$next_year_leave_balance->el_used) + $next_leave_duration + $total_pending_leave_next_year;
        		$prev_used = ($prev_year_leave_balance->vl_used+$prev_year_leave_balance->el_used) + $prev_leave_duration + $total_pending_leave_prev_year;	        	
				$next_year_leave_balance_vl = $next_year_leave_balance->vl;

		        $year_from = date('Y', strtotime($this->input->post('date_from')));
		        $year_to = date('Y', strtotime($this->input->post('date_to')));

		        if($year_from == date('Y') && date('Y') == $year_to){
        			if($used > $leave_balance->vl) $validate_leave[] = "You do not have enough emergency leave balance left, please apply for Leave Without Pay.";
        		} else {
	        		$next_year_leave_balance_vl = $next_year_leave_balance->vl;
	        	    if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
			    			$leave_reset = $leave_reset_setup->row();
			    			$dateto =  date('Y-m-d', strtotime($this->input->post('date_to')));

			    			if ($dateto <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
			    				if ($next_year_leave_balance==false) {
			    					$next_year_leave_balance = true;
			    					$next_year_leave_balance_vl = $leave_balance->vl;
			    				}
			    				
			    			}
			    				
			    	}

			    	$check_date = $this->system->check_in_cutoff(date('Y-m-d', strtotime($this->input->post('date_from'))));
							    	
			    	if( $used > $leave_balance->vl || ($next_used > $next_year_leave_balance_vl && $next_year_leave_balance!=false) || ($next_used != 0 && $next_year_leave_balance==false)){ 

			    		$validate_leave[] = "You do not have enough emergency leave balances, please apply for Leave Without Pay.";
			    	}
			    	elseif (($prev_used > $prev_year_leave_balance->vl && $prev_year_leave_balance!=false)) {
			    		if ($check_date == 2) {
			    			$validate_leave[] = "Your emergency leave application is no longer within the allowable time to apply.";
			    		}
			    		 
			    	}
        		}
        	break;
			case 4:
				if (CLIENT_DIR == "firstbalfour") {
					$this->db->where('employee_id',$this->input->post('employee_id'));
					$result = $this->db->get('employee_leaves_funeral_initial_setup');

					if ($result && $result->num_rows() > 0){
						$leave_init = $result->row();
						$date_given = $leave_init->date_given;
						$date_to_reset = date('Y-m-d', strtotime('+60 days', strtotime($date_given)));

						if (date('Y-m-d') <= $date_to_reset){
				        	$used = $leave_balance->fl_used + $leave_duration;
			        		if( $used > $leave_balance->fl ) {
			        			$validate_leave[] = "You do not have enough Funeral Leave balance.";	
			        		}
						}	
					}
				}else{
					$used = $leave_balance->fl_used + $leave_duration;
					$next_used = $next_year_leave_balance->fl_used + $next_leave_duration;
					if( $used > $leave_balance->fl || ($next_used > $next_year_leave_balance->fl && $next_year_leave_balance==false) ) $validate_leave[] = "You do not have enough Funeral Leave balance left, please apply for Leave Without Pay.";
				}
			break;
		}

		if( ($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'] == 1) && ( $_POST['filter'] != 'personal' ) && ( count($validate_leave) == 0 ) ){
			$validate_leave = true;
		}

		//Other validation that can be bypass by hr

		if( !$no_validation && !$validate_leave ){

			$response['record_id'] = "";
			//validation

			if( $this->input->post('duration_id') || $application_form_id == 13){

				$validate_leave = $this->_validate_leave($this->input->post('employee_id'));
		        if( $validate_leave ){
		        	if( $application_form_id != 7 && $application_form_id != 6 && $application_form_id != 5){
		        		//check if probationary status
						if( !$this->is_superadmin && !$this->is_admin ){
							$emp = $this->db->get_where('employee', array('user_id' => $this->user->user_id))->row();
							if($emp->status_id != 1){
								$validate_leave[] = "You do not have leave credits yet. Please file as LWOP instead.";
							}
						}
		        	}


		        	$leave_duration = 0;
		        	$next_leave_duration = 0;
		        	$prev_leave_duration = 0;
		        	$total_pending_leave = 0;
		        	$total_pending_leave_next_year = 0;
		        	$next_year=date('Y')+1;
		        	$prev_year=date('Y')-1;
		        	if( $this->input->post('duration_id') ){
			        	$durations = $this->input->post('duration_id');

			        	$year_check_ctr = 0;
						$affected_dates=$this->input->post('dates');
						$next_year_flag=false;
						$prev_year_flag=false;

			        	foreach( $durations as $duration ){

			        		if($next_year == date('Y', strtotime($affected_dates[$year_check_ctr])))
			        			$next_year_flag=true;
			        		if($prev_year == date('Y', strtotime($affected_dates[$year_check_ctr])))
			        			$prev_year_flag=true;

			        		if(CLIENT_DIR == 'oams') {
			        			$cred_dur = $this->system->get_cred_duration($this->input->post('employee_id'), date('Y-m-d',strtotime($affected_dates[$year_check_ctr])));
			        			switch($duration)
			        			{
			        				case 1:
			        					$day_credit = $cred_dur->total_work_hours;
			        					break;
			        				case 2:
			        					$day_credit = $cred_dur->total_first_half;
			        					break;
			        				case 3:
			        					$day_credit = $cred_dur->total_second_half;
			        					break;
			        			}
			        		} else {
			        			$dur = $this->db->get_where('employee_leaves_duration', array('duration_id' => $duration))->row();
			        			$day_credit = $dur->credit;
			        		}
			        		
			        		$cred = $day_credit / 8;

			        		if( !$prev_year_flag && !$next_year_flag){
			        			$leave_duration += $cred;
			        		}else{
				        		(!$prev_year_flag && $next_year_flag ? $next_leave_duration += $cred : $leave_duration += $cred);
				        		(!$next_year_flag && $prev_year_flag ? $prev_leave_duration += $cred : $leave_duration += $cred);
			        		}
			        		
			        		$year_check_ctr++;
			        	}
			        }

		        	$date_diff = $this->hdicore->compare_date( $this->input->post('date_from'), $this->input->post('date_to') );
		        	$leave_balance = $this->system->get_leave_balance( $year_date, $this->input->post('employee_id') );
		        	$nn_year=date('Y')+1;
		        	$next_year_leave_balance = $this->system->get_leave_balance( $nn_year, $this->input->post('employee_id') );
		        	$prev_year_leave_balance = $this->system->get_leave_balance( $prev_year, $this->input->post('employee_id') );
		        	
		        	// First check for any approved leaves on requested days.
		        	$this->db->where('form_status_id', 3);
		        	//$this->db->where('( form_status_id = 3 OR form_status_id = 2 )');
		        	$this->db->where(
		        		'(
							("'. date('Y-m-d', strtotime($this->input->post('date_from'))) .'" BETWEEN date_from AND date_to)
							OR
							("'. date('Y-m-d', strtotime($this->input->post('date_to'))) .'" BETWEEN date_from AND date_to)
						 )',
		        		'', 
		        		false
		        		);
		        	$this->db->where('employee_id', $this->input->post('employee_id'));
		        	$this->db->where('deleted', 0);
		        	// added to avoid skipping error
		        	$validate_leave = array();

		        	$check_others = $this->db->get($this->module_table);
		        	
		        	if ($check_others->num_rows() > 0){
		        		if($check_others->row()->application_form_id != 5 && $check_others->row()->application_form_id != 14 && !($this->is_superadmin)) {
			        		// $validate_leave[] = 'You have already filed for a leave on the selected dates.';

			        		$rows = $check_others->result();

							foreach ($rows as $row){
			        			$pending_leaves_date_affected = $this->db->get_where('employee_leaves_dates', array('employee_leave_id' => $row->employee_leave_id,'cancelled'=>0))->result();
			        			
			        			foreach($pending_leaves_date_affected as $affected_leave_dates)
			        			{

			        				foreach ($dates as $key => $value) {
					    				$ds = date('Y-m-d',strtotime($value));
					    				if ( $ds == $affected_leave_dates->date ) {
					    					$duration_id = $affected_leave_dates->duration_id;
					    					if ($duration_id == $durations[$key] || $durations[$key] == 1 || $duration_id == 1){
					    						$invalid = true;
					    					}
					    				}
					    			}
			        			}   	
		        			}

		    				if ($invalid) {
		    					$validate_leave[] = 'You already have approved leave application on the set date/s.';
		    				}
	    				}	    				
		        	
		        	} else if( $leave_balance || $application_form_id == 7 || $application_form_id == 5 || $application_form_id == 14 || $application_form_id == 16 || $application_form_id == 17 || $application_form_id == 19 || $application_form_id == 21){
		        		
		        		 $policy_leave_setup = $this->system->get_policy_leave_setup($this->input->post('employee_id'),$this->input->post('application_form_id'),$this->input->post('date_from'));

				        switch( $application_form_id ){
				        	case 1:
				        			
		        					//$policy_leave_setup = $this->system->get_policy_leave_setup($this->input->post('employee_id'),$this->input->post('application_form_id'),$this->input->post('date_from'));

					        		// Check tenure
					        		$year = date('Y');
					        		$today = new DateTime( date('Y-m-d') );
					        		$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('employee_id')))->row();
					        		$user = $this->db->get_where('user', array('employee_id' => $this->input->post('employee_id')))->row();
					        		$hired = new DateTime( $employee->employed_date);
					        		$interval = $today->diff($hired);

									$vl_tenure = $policy_leave_setup['tenure'];

									$compared_date = $this->hdicore->compare_date($employee->employed_date, date('Y-m-d'));

									$employee = $this->system->get_employee($this->input->post('employee_id'));							
					        		if ($employee['employee_type'] >= 2 && $compared_date->difference_months < $vl_tenure) {
					        			$validate_leave[] = 'You do not have leave credits yet. Please file as LWOP instead.';
					        		} elseif ($employee['employee_type'] == 1 && $employee['status_id'] != 1) {
					        			$validate_leave[] = 'Sick leave only for regular status.';
					        		}

							        if ($this->input->post('reason') == ""){
										$validate_leave[] = 'Reason - This field is mandatory.';
					        		}

					        		//next cutoff validation -tirso	
						            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
										$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
						            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
						            	$validate_leave[] = 'Your sick leave application is no longer within the allowable time to apply.';
						            endif;

							        $from = new DateTime( date('Y-m-d', strtotime($this->input->post('date_from'))) );
						        	$to = new DateTime( date('Y-m-d', strtotime($this->input->post('date_to'))) );
									$interval = $to->diff($from);
									
									$duration = $this->input->post('duration_id');
									
									$cnt = array();
									$d = 0;
									foreach ($duration as $val) {
										if ($val != 1 ) {
											$d = 0.5;
										}else{
											$d = 1;
										}
										$cnt[] = $d;
									}
									
									$intval = array_sum($cnt);

									$compared_date = $this->hdicore->compare_date($employee->employed_date, date('Y-m-d'), false);

									if ($policy_leave_setup['with_attachment']) {
										if ( $this->input->post('documents') == ''){
											if ( ($intval) > $policy_leave_setup['no_days_required_attachment'] ){
												 $validate_leave[] = 'Supporting file is required when filed leave is greater than '.($policy_leave_setup['no_days_required_attachment']).' days'; 
											}
											//use temporary while multiconfig error, I add + 1 in mindays_sickleave_validation
											if ( ($intval) > $policy_leave_setup['no_days_required_attachment'] + 1){
												 $validate_leave[] = 'Fit to work document is required'; 
											}												
										}
									}
					        		break;
				        	case 2:			

				        		// Check tenure
				        		$year = date('Y');
				        		$today = new DateTime( date('Y-m-d') );
				        		$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('employee_id')))->row();
				        		$user = $this->db->get_where('user', array('employee_id' => $this->input->post('employee_id')))->row();
				        		$hired = new DateTime( $employee->employed_date);
				        		$interval = $today->diff($hired);		

								$vl_tenure = $policy_leave_setup['tenure'];

								$compared_date = $this->hdicore->compare_date($employee->employed_date, date('Y-m-d'), false);

								$employee = $this->system->get_employee($this->input->post('employee_id'));
				        		if ($employee['employee_type'] >= 2 && $compared_date->difference_months < $vl_tenure) {
				        			$validate_leave[] = "You have ".$total_pending_leave." pending leave applications and you don't have enough leave balance. Please file as LWOP";
				        		} elseif ($this->config->item('vl_for_regular') && $employee['employee_type'] == 1 && $employee['status_id'] != 1) {
				        			$validate_leave[] = "You have ".$total_pending_leave." pending leave applications and you don't have enough leave balance. Please file as LWOP";
				        		}

								$diff =  $today->diff(new DateTime($this->input->post('date_from')));
								
				        		//next cutoff validation -tirso	
					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your vacation leave application is no longer within the allowable time to apply.';
					            endif;

						    	if ($this->input->post('reason') == ""){
									$validate_leave[] = 'Reason - This field is mandatory.';
				        		}

				        		break;
				        	case 3:

				        		// Check tenure
				        		$year = date('Y');
				        		$today = new DateTime( date('Y-m-d') );
				        		$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('employee_id')))->row();
				        		$user = $this->db->get_where('user', array('employee_id' => $this->input->post('employee_id')))->row();
				        		$hired = new DateTime( $employee->employed_date);
				        		$interval = $today->diff($hired);		

								$vl_tenure = $policy_leave_setup['tenure'];

								$compared_date = $this->hdicore->compare_date($employee->employed_date, date('Y-m-d'), false);

								$employee = $this->system->get_employee($this->input->post('employee_id'));
				        		
				        		if ($employee['employee_type'] >= 1 && $compared_date->difference_months < $vl_tenure) {
				        			$validate_leave[] = 'You do not have enough emergency leave balance.';
				        		} elseif ($employee['employee_type'] == 1 && $employee['status_id'] != 1) {
				        			$validate_leave[] = 'You do not have emergency leave credits yet.';
				        		}

				        		if ($this->input->post('reason_type_id') == ""){
									$validate_leave[] = 'Reason - This field is mandatory.';
				        		}
				        		else{
				        			if ($this->input->post('reason_type_id') == 1){
						        		if ($this->input->post('name_relative') == ""){
											$validate_leave[] = 'Name of Relative - This field is mandatory.';
						        		}
						        		if ($this->input->post('relationship_id') == ""){
											$validate_leave[] = 'Relationship - This field is mandatory.';
						        		}	
				        			}		
				        			else if ($this->input->post('reason_type_id') == 2){
						        		if ($this->input->post('calamity_remarks') == ""){
											$validate_leave[] = 'Remarks - This field is mandatory.';
						        		}	
				        			}		        		
				        		}

				        		//next cutoff validation -tirso	
					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your application exceeded the grace period.';
					            endif;
				        		break;
				        	case 4:

					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your maternity leave application is no longer within the allowable time to apply.';
					            endif;

					        	if (CLIENT_DIR == "firstbalfour") {
					        		$employee = $this->system->get_employee($this->input->post('employee_id'));
					        		if ($employee['status_id'] != 1) {
					        			$validate_leave[] = 'You do not have Funeral leave credits yet.';
					        		}

					        	}
					        	break;
				        	case 5:
					        	//if hr health edit validation is remove
					        	if( $this->user_access[$this->module_id]['hr_health'] == 1 && $_POST['filter'] != 'personal' )
					        	{
					        		break;
					        	} else {
					        		//check type of delivery
									switch( $this->input->post('delivery_type_id') ){
										case '2':
											if( $date_diff->difference > 78 ) 
												$validate_leave[] = 'Maternity leave for Ceasarian section delivery should not exceed 78 days.';
											break;
										default:
											if( $date_diff->difference > 60 ) 
												$validate_leave[] = 'Maternity leave for normal delivery should not exceed 60 days.';
											break;
									}

									// remove as discussed with kaye, marvin and sir john
									//validate no. of times to file maternity leave
									// $maternity_leave = $this->db->get_where('employee_leaves',array('employee_id' => $this->input->post('employee_id'), 'application_form_id' => 5, 'form_status_id' => 3 ));
									// if( $maternity_leave->num_rows() > 0 ){
									// 	foreach( $maternity_leave->result() as $maternity_record ){
									// 		if( date('Y' , strtotime( $maternity_record->date_from )) == date('Y')  ){
									// 			$validate_leave[] = 'You can only file Maternity Leave once a year';
									// 			break;
									// 		}
									// 	}
									// }

									//Comment due to need to validate first to client
									$children = $this->system->get_children($this->input->post('employee_id'));
									if (($children && count($children) > 0) && $policy_leave_setup['maternity_max_no_children'] > 0) {
										$total_children =  count($children);
										// Check for twins,triplets or more?
										$bdays = array();
										foreach ($children as $child) {
											end($bdays);
											$prev_date = key($bdays);
											if ($prev_date != ''){
												$prev_date = new DateTime($prev_date);
												$current_date = new DateTime($child['birth_date']);
												$date_diff = $prev_date->diff($current_date);
												if ($date_diff->d <= 1){
													$total_children--;
												}
											}	

											$bdays[$child['birth_date']][] = $child['record_id'];

											// Check same bdays
	/*										if (count($bdays[$child['birth_date']]) > 1) {
												$total_children--;
											}	*/								
										}

										if ($total_children >= $policy_leave_setup['maternity_max_no_children']) {
											$validate_leave[] = 'You can only apply up to '.$policy_leave_setup['maternity_max_no_children'].'th child.';
										}
									}
									
									// remove as discussed with kaye and marvin
									// Validate employee status
									// $employee = $this->system->get_employee($this->input->post('employee_id'));
									// if ($employee['status_id'] != 1) {
									// 	$validate_leave[] = 'Only regular employees can apply for a Maternity Leave.';
									// }

									//tirso

						            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
										$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
						            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
						            	$validate_leave[] = 'Your maternity leave application is no longer within the allowable time to apply.';
						            endif;

									$days_8month = date("t", mktime(0,0,0, date("n") - 1));
									$days_7month = date("t", mktime(0,0,0, date("n") - 1));

									if (CLIENT_DIR == "hdi"){
										$no_days = $days_8month - 1;
									}
									else{
										$no_days = $days_8month + $days_7month - 1;
									}
						        	$date_to = date('Y-m-d');							
						        	$date_7month = date('Y-m-d',strtotime('-'.$no_days.'days', strtotime($this->input->post('expected_date'))));
						        	if($this->config->item('validate_ml_filing') == 1)
						        	{
							        	if($date_to > $date_7month )
						        		{
						        			if (CLIENT_DIR == "hdi"){
						        				$validate_leave[] = "You can only file maternity leave on your 8th month before expected delivery date.";
						        			}
						        			else{
						        				$validate_leave[] = "You can only file maternity leave on your 7th month before expected delivery date.";
						        			}
						        		}
						        	}

					        		if ($this->input->post('reason') == ""){
										$validate_leave[] = 'Reason - This field is mandatory.';
					        		}
					        	}
				        		break;
				        	case 6:

				        		$policy_leave_setup = $this->system->get_policy_leave_setup($this->input->post('employee_id'),$this->input->post('application_form_id'),$this->input->post('date_from'));

				        	
					        	if(!$this->input->post('actual_date_delivery')){
					        		$validate_leave[] = "Actual date for paternity leave is mandatory";
					        	}

/*					        	if(!$this->config->item('allow_paternity_before_delivery')){
					        		if($this->input->post('actual_date_delivery') > $this->input->post('date_from'))
					        			$validate_leave[] = "Paternity leave should be filed during or after wife's actual delivery date.";
					        	}

					        	$date_possible = date('Y-m-d',strtotime('+60 days', strtotime($this->input->post('actual_date_delivery'))));
					        	$date_to = date('Y-m-d',strtotime($this->input->post('date_to')));
					        	if($date_to > $date_possible )
				        		{
				        			$validate_leave[] = "Paternity leave should be filed within 60 days after wife's actual delivery date.";
				        		}*/

					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('actual_date_delivery')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('actual_date_delivery')) == 2):
					            	$validate_leave[] = 'Your Paternity leave application is no longer within the allowable time to apply.';
					            endif;

				        		$children = $this->system->get_children_sorted_date( $this->input->post('employee_id') );

				        		
				        		if( $children && count($children) >=  $policy_leave_setup['maternity_max_no_children'] )
				        		{

				        			$advance_birth_date='0000-00-00';
				        			$total_children = count($children);

					        		foreach ($children as $child) {
					        			$bdays[$child['birth_date']][] = $child['record_id'];

						        		if(count($bdays[$child['birth_date']]) > 1 || $advance_birth_date == $child['birth_date'])
						        			$total_children--;
					        			$advance_birth_date = date('Y-m-d', strtotime('+1 day', strtotime($child['birth_date']))); 

					        		}

					        		if( $total_children >=  $policy_leave_setup['maternity_max_no_children'] ) $validate_leave[] = "Paternity leave shall be up to first four pregnancies of legal wife.";
								}

								if( $leave_duration > $policy_leave_setup['max_no_days_to_avail'] ) $validate_leave[] = "Paternity Leave should not exceed ".$policy_leave_setup['max_no_days_to_avail']." days.";

								if (CLIENT_DIR == 'firstbalfour'){
									$this->db->where('employee_id',$this->input->post('employee_id'));
									$result = $this->db->get('employee_leaves_paternity_initial_setup');

									if ($result && $result->num_rows() > 0){
										$leave_init = $result->row();
										$date_given = $leave_init->date_given;
										$date_to_reset = date('Y-m-d', strtotime('+60 days', strtotime($date_given)));

										if (date('Y-m-d') <= $date_to_reset){
								        	$used = $leave_balance->mpl_used + $leave_duration;
							        		if( $used > $leave_balance->mpl ) {
							        			$validate_leave[] = "You do not have enough Paternity Leave balance.";	
							        		}
										}	
									}
								}
								else{
						        	$used = $leave_balance->mpl_used + $leave_duration;
					        		if( $used > $leave_balance->mpl ) {
					        			$validate_leave[] = "You do not have enough Paternity Leave balance.";	
					        		}
								}								

				        		if ($this->input->post('reason') == ""){
									$validate_leave[] = 'Reason - This field is mandatory.';
				        		}
				        		
				        		break;
				        	case 7:
				        		//next cutoff validation -tirso
					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your leave without application is no longer within the allowable time to apply.';
					            endif;

						        if ($this->input->post('reason') == ""){
									$validate_leave[] = 'Reason - This field is mandatory.';
				        		}

				        		break;				        		
				        	case 13:
				        		//check if allowed to apply with regards to tenure
				        		$year = date('Y');
				        		$today = new DateTime( date('Y-m-d') );
				        		$employee = $this->db->get_where('employee', array('employee_id' => $this->input->post('employee_id')))->row();
				        		$user = $this->db->get_where('user', array('employee_id' => $this->input->post('employee_id')))->row();
				        		$hired = new DateTime( $employee->employed_date);
				        		$interval = $today->diff($hired);

				     			//check if already filed
			        			$userinfo = $this->db->get_where('employee',array('employee_id'=>$this->input->post('employee_id')))->row();
			        			$qry = "select a.*
			        			FROM {$this->db->dbprefix}{$this->module_table} a
			        			WHERE application_form_id = 13 AND form_status_id = 3 AND date_from like '{$year }-%' AND employee_id = {$userinfo->employee_id}";
			        			$rec = $this->db->query( $qry );

			        			if($rec->num_rows() > 0) {
			        				$validate_leave[] = "You have already applied for a birthday leave.";	
			        			}

					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),date('Y-m-d')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),date('Y-m-d')) == 2):
					            	$validate_leave[] = 'Your bday leave application is no longer within the allowable time to apply.';
					            endif;

				        		//birthday should be on the day or a later date
			        			if($rec->num_rows() > 0) {
			        				$validate_leave[] = "You have already applied for a birthday leave.";	
			        			}

				        		if ($policy_leave_setup['bday_no_of_days_alowed_before_filing'] > 0 || $policy_leave_setup['bday_no_of_days_alowed_after_filing'] > 0){
				        			$filing_of_bday_leave_before = date('m-d', strtotime('- '.$policy_leave_setup['bday_no_of_days_alowed_before_filing'].' days', strtotime($user->birth_date)));
				        			$filing_of_bday_leave_before = date('Y-', strtotime($this->input->post('date_from'))).$filing_of_bday_leave_before;

				        			$filing_of_bday_leave_after = date('m-d', strtotime('+ '.$policy_leave_setup['bday_no_of_days_alowed_after_filing'].' days', strtotime($user->birth_date)));
				        			$filing_of_bday_leave_after = date('Y-', strtotime($this->input->post('date_from'))).$filing_of_bday_leave_after;

				        			if(CLIENT_DIR == "firstbalfour"){
				        				if(date('m',strtotime($user->birth_date)) == 01){
				        					if(date('Y-m-d', strtotime($this->input->post('date_from'))) < date('Y-m-d', strtotime(date('Y-', strtotime($this->input->post('date_from'))).date('m-d', strtotime($user->birth_date)))) || date('Y-m-d',strtotime($this->input->post('date_from'))) > date('Y-m-d', strtotime($filing_of_bday_leave_after))) { 
					        					$validate_leave[] = "Application should be within ".date('Y-m-d', strtotime(date('Y-', strtotime($this->input->post('date_from'))).date('m-d', strtotime($user->birth_date))))." and ".$filing_of_bday_leave_after;
					        				}
				        				}elseif(date('m',strtotime($user->birth_date)) == 12){
				        					if(date('Y-m-d', strtotime($this->input->post('date_from'))) > date('Y-m-d', strtotime(date('Y-', strtotime($this->input->post('date_from'))).date('m-d', strtotime($user->birth_date)))) || date('Y-m-d',strtotime($this->input->post('date_from'))) < date('Y-m-d', strtotime($filing_of_bday_leave_before)) ) { 
					        					$validate_leave[] = "Application should be within ".$filing_of_bday_leave_before." and ".date('Y-m-d', strtotime(date('Y-', strtotime($this->input->post('date_from'))).date('m-d', strtotime($user->birth_date))));
					        				}
				        				}else{
				        					if(date('Y-m-d',strtotime($this->input->post('date_from'))) < date('Y-m-d', strtotime($filing_of_bday_leave_before)) || date('Y-m-d',strtotime($this->input->post('date_from'))) > date('Y-m-d', strtotime($filing_of_bday_leave_after))) { 
					        					$validate_leave[] = "Application should be within ".$filing_of_bday_leave_before." and  ".$filing_of_bday_leave_after."";
					        				}
					        			}
				        			}else{
					        			if(date('Y-m-d',strtotime($this->input->post('date_from'))) < date('Y-m-d', strtotime($filing_of_bday_leave_before)) || date('Y-m-d',strtotime($this->input->post('date_from'))) > date('Y-m-d', strtotime($filing_of_bday_leave_after))) { 
					        				$validate_leave[] = "Application should be within ".$filing_of_bday_leave_before." and  ".$filing_of_bday_leave_after."";
					        			}
				        			}
				        		} elseif( strcmp(date('md'), date('md', strtotime( $user->birth_date) ) ) > 0 ) {
				        			$validate_leave[] = 'Birthday leave can only be taken on your actual birthdate.';
				        		}
				        		elseif( $interval->y < $policy_leave_setup['tenure']){
				        			$validate_leave[] = 'You are not yet entitled to a Birthday leave.';
				        		} else {
					        		if( $leave_duration > 1 ){
					        			$validate_leave[] = "You are entitled to just 1 Birthday Leave.";
					        			$response['leave_duration'] = $leave_duration;
					        		} else {
					        			//check if already filed
					        			$userinfo = $this->db->get_where('employee',array('employee_id'=>$this->input->post('employee_id')))->row();
					        			$qry = "select a.*
					        			FROM {$this->db->dbprefix}{$this->module_table} a
					        			WHERE application_form_id = 13 AND form_status_id = 3 AND date_from like '{$year }-%' AND employee_id = {$userinfo->employee_id}";
					        			$rec = $this->db->query( $qry );

					        			if( $rec->num_rows() == 0 ){
							       			//check if actual date is birthday
							        		if( date('m-d', strtotime( $user->birth_date ) ) == date('m-d', strtotime( $this->input->post('date_from') ) ) ){
							        			//check if bday holiday
							        			$bdate = date('-m-d', strtotime( $user->birth_date ));
							        			$qry = "SELECT a.*
							        			FROM {$this->db->dbprefix}holiday a
							        			LEFT JOIN {$this->db->dbprefix}holiday_employee b on b.holiday_id = a.holiday_id
							        			WHERE a.deleted = 0 AND a.inactive = 0 AND date_set like '%{$bdate}' AND IF (a.legal_holiday = 0 AND a.location_id <> '',LOCATE(CONCAT(',', ".$userinfo->location_id." ,','),CONCAT(',',location_id,',')) > 0,1)";
							        			$holidays = $this->db->query( $qry );
							        			if( $holidays->num_rows() != 0 ){
							        				//check the holiday applies
													$date_to_leave = date('Y-m-d', strtotime($this->input->post('date_from')));
							        				//loop to check next viable day to file birthday leave
							        				$validate_leave[] = "Your birthday falls on a holiday. Please schedule your birthday leave on the next working day.";

/*							        				$loop = true;
							        				$next_day = date('m-d', strtotime( '+1 day' . $user->birth_date));
							        				$next_day = date('Y-') . $next_day;
							        				while( $loop ){
							        					$_POST['date_from'] = $next_day;
							        					$_POST['date_to'] = $next_day;
							        					$affected =  $this->get_affected_dates( true );
							        					if(isset($affected['dates'] ) && sizeof($affected['dates']) == 1){
							        						$date_affected = $affected['dates'][0]['date'];
															$date_affected = date('Y-m-d', strtotime($date_affected));
															if($date_affected != $date_to_leave ){
																$viable_day = date('m/d/Y', strtotime($next_day));
																$validate_leave[] = "Your birthday falls on a holiday. Please schedule your birthday leave on the next working day.";	
															}
															$next_day = date('m/d/Y', strtotime($next_day));
															$_POST['date_from'] = $next_day;
							        						$_POST['date_to'] = $next_day;
															$loop = false;
							        					}else{
							        						$next_day = date('Y-m-d', strtotime( '+1 day' . $next_day));
							        					}
							        				}*/
							        			}
							        		}
							        		else{
							        			//check if bday holiday
							        			$bdate = date('-m-d', strtotime( $user->birth_date ));
							        			$qry = "SELECT a.*
							        			FROM {$this->db->dbprefix}holiday a
							        			LEFT JOIN {$this->db->dbprefix}holiday_employee b on b.holiday_id = a.holiday_id
							        			WHERE a.deleted = 0 AND a.inactive = 0 AND date_set like '%{$bdate}'";
							        			$holidays = $this->db->query( $qry );
							        			$on_holiday = false;
							        			if ($holidays){
								        			if( $holidays->num_rows() != 0 ){
								        				//check the holiday applies
								        				foreach($holidays->result() as $holiday){
								        					if( date('-m-d', strtotime( $holiday->date_set)) == date('-m-d', strtotime( $user->birth_date)) ){
									        					if( date("Y", strtotime( $holiday->date_set )) == date('Y') ){
									        						$on_holiday = $holiday->date_set;	
									        					}

									        					if( $holiday->annual == 1 ){
									        						$on_holiday = $holiday->date_set;
									        					}
																//additional checking for not legal holiday and base on location inputted //tirso
																if (!$holiday->legal_holiday && $holiday->location_id <> ''){
																	$location_array = explode(',',$holiday->location_id);
																	if (in_array($userinfo->location_id, $location_array)){
																		$on_holiday = $holiday->date_set;
																	}
																	else{
																		$on_holiday = false;	
																	}
																}									        					
									        				}									        				
								        				}
								        			}
							        			}

							        			// Check previously denied applications.
							        			$this->db->where('application_form_id', $application_form_id);
							        			$this->db->where('form_status_id', 4);
							        			$this->db->where('deleted', 0);
							        			$this->db->where('employee_id', $user->employee_id);

							        			$prev_applied_bday = ($this->db->get($this->module_table)->num_rows > 0);

							        			if (!$prev_applied_bday) {
													if($on_holiday) {
								        				$validate_leave[] = "You cannot file your birthday leave on a rest day or holiday.";
								        			} else{
								        				$date_to_leave = date('Y-m-d', strtotime($this->input->post('date_from')));
								        				//loop to check next viable day to file birthday leave
								        				
								        				if ($on_holiday === $date_to_leave) {
								        					$validate_leave[] = "Your birthday falls on a holiday. Please schedule your birthday leave on the next working day.";
								        				}

/*								        				$loop = true;
								        				$next_day = date('m-d', strtotime( '+1 day' . $user->birth_date));
								        				$next_day = date('Y-') . $next_day;
								        				while( $loop ){
								        					$_POST['date_from'] = $next_day;
								        					$_POST['date_to'] = $next_day;
								        					$affected =  $this->get_affected_dates( true );
								        					if(isset($affected['dates'] ) && sizeof($affected['dates']) == 1){
								        						$date_affected = $affected['dates'][0]['date'];
																$date_affected = date('Y-m-d', strtotime($date_affected));
																if($date_affected != $date_to_leave ){
																	$viable_day = date('m/d/Y', strtotime($next_day));
																	$validate_leave[] = "Your birthday falls on a holiday. Please schedule your birthday leave on the next working day.";	
																}
																$next_day = date('m/d/Y', strtotime($next_day));
																$_POST['date_from'] = $next_day;
								        						$_POST['date_to'] = $next_day;
																$loop = false;
								        					}else{
								        						$next_day = date('Y-m-d', strtotime( '+1 day' . $next_day));
								        					}
								        				}*/
								        			}
							        			}							        			
							        		}
							        	}
							        	else{
							        		$validate_leave[] = "You have already applied for a birthday leave.";
							        	}
						        	}
				        		}

				     			if ($this->input->post('reason') == ""){
									$validate_leave[] = 'Reason - This field is mandatory.';
				        		}

				        		break;
				        	case 14:
				        	
				        		if (isset($policy_leave_setup['max_no_days_to_avail'])){
				        			if( $date_diff->difference > $policy_leave_setup['max_no_days_to_avail'] ) $validate_leave[] = "Special leave for women should not exceed ".$policy_leave_setup['max_no_days_to_avail']." days.";
				        		}

				        		//validate no. of times to file maternity leave
								$special_leave = $this->db->get_where('employee_leaves',array('employee_id' => $this->input->post('employee_id'), 'application_form_id' => 14, 'form_status_id' => 3 ));

								if( $special_leave->num_rows() > 0 ){
									foreach( $special_leave->result() as $special_record ){
										if( date('Y' , strtotime( $special_record->date_from )) == date('Y')  ){
											$validate_leave[] = 'You can only file Special Leave For Woman once a year';
											break;
										}
									}
								}

						        $from = new DateTime( date('Y-m-d', strtotime($this->input->post('date_from'))) );
					        	$to = new DateTime( date('Y-m-d', strtotime($this->input->post('date_to'))) );
								$interval = $to->diff($from);

								$compared_date = $this->hdicore->compare_date($employee->employed_date, date('Y-m-d'), false);

								if (isset($policy_leave_setup['with_attachment']) && $policy_leave_setup['with_attachment']){
									if( ($interval->d + 1) >= $policy_leave_setup['no_days_required_attachment'] ){
										$validate_leave[] = 'Medical Certificate is required';
									}
								}

								if ($this->input->post('reason') == ""){
									$validate_leave[] = 'Reason - This field is mandatory.';
				        		}

				        		//next cutoff validation -tirso	
					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your Special Leave For Women application is no longer within the allowable time to apply.';
					            endif;

				        	break;
				        	case 16:

				        		if(count($this->input->post('duration_id')) > $this->input->post('base_off_allowed') || $this->input->post('base_off_allowed') == "")
				        			$validate_leave[] = 'Not enough base-off leave';

				        		if(date('Y-m-d', strtotime($this->input->post('project_to'))) > date('Y-m-d'))
				        			$validate_leave[] = 'project to leave can\'t be greater than current date';

				        		if(date('D', strtotime($this->input->post('date_from'))) != "Fri")
				        			$validate_leave[] = 'leave date from must be filed on friday';

								$qry = "SELECT * FROM {$this->db->dbprefix}employee_dtr
										WHERE `date` BETWEEN '".date('Y-m-d', strtotime($this->input->post('project_from')))."' AND '".date('Y-m-d', strtotime($this->input->post('project_to')))."'
										AND employee_id = {$this->input->post('employee_id')}
										AND (awol = 1 OR (hours_worked > 0
														  AND time_in1 IS NULL 
														  AND time_out1 IS NULL)
											)
										";

								$result = $this->db->query($qry);

								if($result && $result->num_rows() > 0)
									$validate_leave[] = "No Continous attendance";


								$qry = "SELECT * 
											FROM {$this->db->dbprefix}employee_leave_base_off
										LEFT JOIN {$this->db->dbprefix}employee_leaves
											ON {$this->db->dbprefix}employee_leaves.employee_leave_id = {$this->db->dbprefix}employee_leave_base_off.employee_leave_id
										WHERE ('".date('Y-m-d', strtotime($this->input->post('project_from')))."' BETWEEN project_from AND project_to
										OR '".date('Y-m-d', strtotime($this->input->post('project_to')))."' BETWEEN project_from AND project_to)
										AND employee_id = {$this->input->post('employee_id')}
										AND form_status_id = 3
										";

								$result = $this->db->query($qry);

								if($result && $result->num_rows() > 0)
									$validate_leave[] = "Overlaps with previous project range";

				        		//next cutoff validation -tirso	
					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your Base-off Leave application is no longer within the allowable time to apply.';
					            endif;

							break;
							case 17:

								$this->db->where('employee_id',$this->input->post('employee_id'));
								$result = $this->db->get('employee_leaves_plsp_initial_setup');

								if ($result && $result->num_rows() > 0){
									$leave_init = $result->row();
									$date_given = $leave_init->date_given;
									$date_to_reset = date('Y', strtotime($date_given));

									if (date('Y') <= $date_to_reset){
							        	$used = $leave_balance->plsp_used + $leave_duration;
						        		if( $used > $leave_balance->plsp ) {
						        			$validate_leave[] = "You do not have enough Parental Leave for Solo Parents Leave balance.";	
						        		}
									}	
								}

				        		//next cutoff validation -tirso	
					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your Parental Leave for Solo Parents is no longer within the allowable time to apply.';
					            endif;								
							break;
							case 19:
								if ($date_diff->difference > 10){									
									$validate_leave[] = "You can not file Leaves for Victims of Violence against Women more than 10 days including rest day and holiday.";	
								}	

				        		//next cutoff validation -tirso	
					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your Leaves for Victims of Violence against Women is no longer within the allowable time to apply.';
					            endif;																		
							break;	
							case 21:
								$this->db->where('employee_id',$this->input->post('employee_id'));
								$result = $this->db->get('employee_leaves_ul_initial_setup');

								if ($result && $result->num_rows() > 0){
									$leave_init = $result->row();
									$date_given = $leave_init->date_given;
									$date_to_reset = date('Y', strtotime($date_given));

									if (date('Y') <= $date_to_reset){
							        	$used = $leave_balance->ul_used + $leave_duration;
						        		if( $used > $leave_balance->ul ) {
						        			$validate_leave[] = "You do not have enough Union Leave balance.";	
						        		}
									}	
								}

				        		//next cutoff validation -tirso	
					            if ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 1):
									$validate_leave[] = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					            elseif ($this->system->check_cutoff_policy_ls($this->input->post('employee_id'),$this->input->post('form_status_id'),$this->input->post('application_form_id'),$this->input->post('date_from'),$this->input->post('date_to')) == 2):
					            	$validate_leave[] = 'Your Service Incentive Leave is no longer within the allowable time to apply.';
					            endif;																		
							break;											
				        }
			    	}
			    	else{
			    		$validate_leave[] = "You have already consumed all your Leave credits.";
			    	}

			        if( sizeof( $validate_leave ) == 0 ) $validate_leave = true;
			    }
			}
			else{
				if ($application_form_id == 13) {
					$validate_leave = array('You cannot file your birthday leave on a rest day.');
				} else {
					$validate_leave = array('Kindly specify dates for leave application.');
				}
			}
			//check if approvers exists
			$check_approvers = array();
			$check_approvers = $this->system->get_approvers_and_condition( $this->input->post('employee_id'), $this->module_id );
			if (!count($check_approvers) > 0){
				$validate_leave = array("Please contact HR Admin. Approver has not been set.");
			}
		}
		if ( $validate_leave === true ) {
			$policy_leave_setup = $this->system->get_policy_leave_setup($this->input->post('employee_id'),$this->input->post('application_form_id'),$this->input->post('date_from'));

			if( empty($_POST['form_status_id']) ) $data['form_status_id'] = 1;

			if (CLIENT_DIR == 'firstbalfour'){
				if ($application_form_id == 4 || $application_form_id == 5 || $application_form_id == 6 || $application_form_id == 14 || $application_form_id == 17 || $application_form_id == 18 || $application_form_id == 19 || $application_form_id == 20 || $application_form_id == 21){
					$_POST['form_status_id'] = 6;
				}
			}

			if ($this->input->post('record_id') == '-1') {
				$data['date_created'] = date('Y-m-d H:i:s', now());
				if( $this->user_access[$this->module_id]['post'] == 1 && $_POST['filter'] != 'personal' ){
					$data['form_status_id'] = 3;
					$data['date_approved'] = date('Y-m-d H:i:s', now());
				}
			} else {
				if( $this->user_access[$this->module_id]['hr_health'] == 1 && $_POST['filter'] != 'personal' )
					$data['form_status_id'] = 3;
			}

			// leave policy overriding for hr validation
			if ($policy_leave_setup['for_hr_validation'] && $this->user_access[$this->module_id]['post'] != 1){
				//check number of days if for validation
				if ( ($intval) > $policy_leave_setup['no_days_hr_validate'] ){
					$_POST['form_status_id'] = 6;
					$data['form_status_id'] = 6;					
				}
			}

			$response = $this->_custom_ajax_save();

			if ($this->key_field_val) {
				if ($this->input->post('record_id') == '-1') {
					$approvers = $this->system->get_approvers_and_condition( $this->input->post('employee_id'), $this->module_id );				
					$id_in_approver = false;
					foreach($approvers as $approver){
						$approver['leave_id'] = $this->key_field_val;	
						if (CLIENT_DIR == 'firstbalfour'){
							if ($application_form_id == 4 || $application_form_id == 5 || $application_form_id == 6 || $application_form_id == 14 || $application_form_id == 17 || $application_form_id == 18 || $application_form_id == 19 || $application_form_id == 20 || $application_form_id == 21){
								$approver['status'] = 6;
							}
                            //just display approver's name who filed the form
                            if( $this->user_access[$this->module_id]['post'] == 1 && $_POST['filter'] != 'personal' && $this->input->post('on_success') != 'goto_detail' && $this->user->user_id == $approver['approver']){
                                $approver['status'] = 3;
                                $id_in_approver = true;
                            }
						}else{							
							if( $this->user_access[$this->module_id]['post'] == 1 && $_POST['filter'] != 'personal' && $this->input->post('on_success') != 'goto_detail' ){
								$approver['status'] = 3;
							}	
						}	

						// leave policy overriding for hr validation
						if ($policy_leave_setup['for_hr_validation'] && $this->user_access[$this->module_id]['post'] != 1){
							$approver['status'] = 6;
						}

						$this->db->insert('leave_approver', $approver);
					}
				//insert approver not in employee approvers' list but has admin rights
                        if((!$id_in_approver) && $this->input->post("employee_id") != $this->user->user_id){                   
                            $filed_by_admin_rights = array(
                                'approver' => $this->user->user_id,
                                'sequence' => 1,
                                'condition' => 2,
                                'focus' => 1,
                                'status' => 3
                            );
                            $filed_by_admin_rights['leave_id'] = $this->key_field_val;

                            $this->db->insert('leave_approver', $filed_by_admin_rights);
                        }
				}
				// Set dates.
				$data['date_updated'] = date('Y-m-d H:i:s', now());

				$this->db->where($this->key_field, $this->key_field_val);
				$this->db->update($this->module_table, $data);

				// Prepare columns to save to employee_leaves_dates table.
				if ($this->input->post('dates') && $this->input->post('duration_id')) {
					$this->db->delete('employee_leaves_dates', array( $this->key_field => $this->key_field_val ));

					if( $application_form_id == 5){
						unset($_POST['employee_leave_date_id']);
						unset($_POST['dates']);
						unset($_POST['duration_id']);
						$s_date = date('Y-m-d', strtotime($_POST['date_from']));
						$e_date = date('Y-m-d', strtotime($_POST['date_to']));

						while($s_date <= $e_date){
							$_POST['employee_leave_date_id'][] = "0";
							$_POST['dates'][] = $s_date;
							$_POST['duration_id'][] = 1;
							$s_date = date('Y-m-d', strtotime($s_date .' +1 day'));
						}
					}

					$dates = $this->input->post('dates');
					$duration_id = $this->input->post('duration_id');
					$date_ids = $this->input->post('employee_leave_date_id');
					// Loop through date_ids and determine which should be inserted / updated.
					$ids = array();
					foreach ($date_ids as $key => $date_id) {
						$worksched = $this->system->get_employee_worksched(  $this->input->post('employee_id'), date('Y-m-d', strtotime( $dates[$key] ) ));

						//check shift base on work sched to remove days which fall on rests days					
						switch( date('N', strtotime( date('Y-m-d', strtotime( $dates[$key] ) ) )) ){
							case 1:
								if( !empty( $worksched->monday_shift_id ) ){
									$shift_id = $worksched->monday_shift_id;
								}
								break;
							case 2:
								if( !empty( $worksched->tuesday_shift_id ) ){
									$shift_id = $worksched->tuesday_shift_id;
								}
								break;
							case 3:
								if( !empty( $worksched->wednesday_shift_id ) ){
									$shift_id = $worksched->wednesday_shift_id;	
								}
								break;
							case 4:
								if( !empty( $worksched->thursday_shift_id ) ){
									$shift_id = $worksched->thursday_shift_id;
								}
								break;
							case 5:
								if( !empty( $worksched->friday_shift_id ) ){
									$shift_id = $worksched->friday_shift_id;
								}
								break;
							case 6:
								if( !empty( $worksched->saturday_shift_id ) ){
									$shift_id = $worksched->saturday_shift_id;
								}
								break;
							case 7:
								if( !empty( $worksched->sunday_shift_id ) ){
									$shift_id = $worksched->sunday_shift_id;
								}
								break;	
						}

						$leave_date = array(
						    'employee_leave_id' => $this->key_field_val,
						    'date' => date('Y-m-d', strtotime( $dates[$key] ) ),
						    'duration_id' => $duration_id[$key]
						);

						$shift = $this->db->get_where('timekeeping_shift', array('shift_id' => $shift_id))->row();
						switch( $duration_id[$key] ){
							case 1:
								$leave_date['time_start'] = $shift->shifttime_start;
								$leave_date['time_end'] = $shift->shifttime_end;
								break;
							case 2:
								$leave_date['time_start'] = $shift->shifttime_start;
								$leave_date['time_end'] = $shift->halfday;
								break;
							case 3:
								$leave_date['time_start'] = $shift->halfday;
								$leave_date['time_end'] = $shift->shifttime_end;
								break;
							// tirso - for first balfour : 2013/10/30
							case 4:
								$leave_date['time_start'] = $shift->shifttime_start;
								$leave_date['time_end'] = $shift->shifttime_end;
								break;								
							default:
								if($this->config->item('client_no') == 2)
								{
									$lv = $this->db->get_where('employee_leaves_duration', array('duration_id' => $duration_id[$key]));
									if($lv && $lv->num_rows() > 0)
									{
										$lv = $lv->row();
										$in_hours = round($lv->credit);
										$new_timeout = date('Y-m-d H:i:s', strtotime('- '.$in_hours.' hour '.$shift->shifttime_end));
										$leave_date['time_start'] = $shift->shifttime_start;
										$leave_date['time_end'] = $new_timeout;
									}
								}
								break;
						}

						if(CLIENT_DIR == 'oams') {
		        			$cred_dur = $this->system->get_cred_duration($this->input->post('employee_id'), date('Y-m-d',strtotime($dates[$key])));

		        			switch($duration_id[$key])
		        			{
		        				case 1:
		        					$day_credit = $cred_dur->total_work_hours;
		        					break;
		        				case 2:
		        					$day_credit = $cred_dur->total_first_half;
		        					break;
		        				case 3:
		        					$day_credit = $cred_dur->total_second_half;
		        					break;
		        				default:
			        				$duration = $this->db->get_where('employee_leaves_duration', array('duration_id' => $duration_id[$key]))->row();
			        				$day_credit = $duration->credit;		
			        				break;
		        			}
		        		} else {
		        			$duration = $this->db->get_where('employee_leaves_duration', array('duration_id' => $duration_id[$key]))->row();
		        			$day_credit = $duration->credit;
		        		}

						$leave_date['credit'] = $day_credit / 8;
						
						if ($date_id == '0') {
							$this->db->insert('employee_leaves_dates', $leave_date);
							$ids[] = $this->db->insert_id();
						} else {
							$this->db->where('employee_leave_date_id', $date_id);
							$this->db->update('employee_leaves_dates', $leave_date);
							$ids[] = $date_id;
						}

					}

					// Delete any entry that is no longer needed.
					$this->db->where_not_in('employee_leave_date_id', $ids);
					$this->db->where('employee_leave_id', $this->key_field_val);
					$this->db->update('employee_leaves_dates', array('deleted' => 1));

					$response->ids = $ids;
				}

				//change for paternity
				if($application_form_id == 6)
				{
					if ($this->key_field_val) {
						$this->db->set('employee_leave_id',$this->key_field_val);
						$this->db->set('actual_date_delivery',date('Y-m-d',strtotime($this->input->post('actual_date_delivery'))));
						$this->db->insert('employee_leaves_paternity');
					}
				}
				//change for paternity

				//change for maternity
				if($this->input->post('reason') == 'maternity')
				{
					if ($this->key_field_val) {
						$this->db->where('employee_leave_id',$this->key_field_val);
						$this->db->delete('employee_leaves_maternity');

						$info = array('employee_leave_id'=>$this->key_field_val,
									  'delivery_type_id'=>$this->input->post('delivery_type_id'),
									  'expected_date'=> ($this->input->post('expected_date') != '' ? date('Y-m-d',strtotime($this->input->post('expected_date'))) : ''),
									  'return_date'=>date('Y-m-d',strtotime($this->input->post('return_date'))),
									  'no_of_pregnancy'=>$this->input->post('no_of_pregnancy')
								);

						if ($this->input->post('actual_date') != ''){
							$info['actual_date'] = date('Y-m-d',strtotime($this->input->post('actual_date')));
						}

						$this->db->insert('employee_leaves_maternity',$info);
					}
				}
				//change for paternity

				if ($this->key_field_val) {
					$this->db->delete('employee_leaves_el', array('employee_leave_id' => $this->key_field_val));
					$this->db->set('employee_leave_id',$this->key_field_val);
					if (CLIENT_DIR == "firstbalfour"){
						$this->db->set(array('reason_type_id'=>$this->input->post('reason_type_id'),'name_relative'=>$this->input->post('name_relative'),'relationship_id'=>$this->input->post('relationship_id'),'others_reason'=>$this->input->post('others_reason'),'calamity_remarks'=>$this->input->post('calamity_remarks')));
					}
					else{
						$this->db->set(array('reason_type_id'=>$this->input->post('reason_type_id'),'name_relative'=>$this->input->post('name_relative'),'relationship_id'=>$this->input->post('relationship_id'),'calamity_remarks'=>$this->input->post('calamity_remarks')));						
					}
					$this->db->insert('employee_leaves_el');
				}

				if ($this->key_field_val && $this->input->post('project_from')) {
					$this->db->delete('employee_leave_base_off', array('employee_leave_id' => $this->key_field_val));
					$this->db->set('employee_leave_id', $this->key_field_val);
					$set = array(
								'project_from' => date('Y-m-d', strtotime($this->input->post('project_from'))), 
								'project_to' => date('Y-m-d', strtotime($this->input->post('project_to'))),
								'report_date' => $this->system->get_next_working_day($this->input->post('employee_id'), $this->input->post('date_to')),
								'base_off_allowed' => $this->input->post('base_off_allowed')
								 );
								// date('Y-m-d', strtotime('Next Monday', strtotime($this->input->post('date_from'))))
					$this->db->set($set);
					$this->db->insert('employee_leave_base_off');
				}
			
				if ($this->input->post('record_id') == '-1') {

					if( $this->user_access[$this->module_id]['post'] == 1 && $_POST['filter'] != 'personal' && $this->input->post('on_success') != 'goto_detail' && $this->input->post('on_success') != 'email' ){					
						//deduct leave credits
						$days = $this->db->get_where('employee_leaves_dates', array($this->key_field => $this->key_field_val, 'deleted' => 0));
						if( $days->num_rows() > 0 ){
							// Only update the record if validation is correct.
							foreach( $days->result() as $day ){	

								$date2 = $day->date;
								
								$year_date = date('Y', strtotime($day->date));

								if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
					    			$leave_reset = $leave_reset_setup->row();
					    			// $dateto =  date('Y-m-d', strtotime($this->input->post('date_to')));

					    			if ($date2 <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
					    				$year_date = date('Y', strtotime($leave_reset->leave_reset_date))-1;
					    				if (date('Y', strtotime($leave_reset->leave_reset_date)) == date('Y')) {
					    					$year_date = date('Y');
					    				}
					    			}	
					    		}	
								
								$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => $year_date, 'employee_id' => $this->input->post('employee_id'), 'deleted' => 0) );

								//$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => date('Y', strtotime($day->date)), 'employee_id' => $this->input->post('employee_id'), 'deleted' => 0) );						
								if( $emp_balance->num_rows() == 1 ){
									$emp_balance = $emp_balance->row_array();
									switch( $application_form_id ){
										case 1: //SL
											$emp_balance['sl_used'] += $day->credit;
											break;
										case 2: //VL
											$emp_balance['vl_used'] += $day->credit;
											break;	
										case 3: //EL								
											$emp_balance['el_used'] += $day->credit;
											break;
										case 4: //EL	
											if (CLIENT_DIR == 'firstbalfour'){
												$emp_balance['fl_used'] += $day->credit;
											}	
											else{
												$emp_balance['bl_used'] += $day->credit;												
											}						
											break;
										case 5: //ML
										case 6: //PL								
											$emp_balance['mpl_used'] += $day->credit;
											break;
										// added for balfour
										case 13:
											if (CLIENT_DIR == "firstbalfour") {
												$emp_balance['bl_used'] += $day->credit;
											}
											break;
									}							
									$this->db->where('leave_balance_id', $emp_balance['leave_balance_id']);
									$this->db->update('employee_leave_balance', $emp_balance);
								}
							}
						}
					}
				}
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$response['msg'] = implode('<br />', $validate_leave);
			$response['msg_type'] = 'attention';		
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
	}

	function delete() {

		$record_id = explode(',', $this->input->post('record_id'));

		$this->db->where_in('employee_leave_id',$record_id);
		$result = $this->db->get('employee_leaves');

		if( $result->num_rows() > 0 ){

			$status_count = 0;

			foreach( $result->result() as $record ){

				if( $record->form_status_id != 1 && $record->form_status_id != 2 ){
					$status_count++;
				}

			}

			if( $status_count > 0 ){

				$response['msg'] = 'Only draft and for approval application can be deleted.';
				$response['msg_type'] = 'attention';		
				$data['json'] = $response;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

			}
			else{

				parent::delete();

			}

		}
		else{
			$response['msg'] = 'No record found.';
			$response['msg_type'] = 'attention';		
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}

	}
	// END - default module functions
	// START custom module funtions

	private function _custom_ajax_save() {
		if (IS_AJAX) {		
			if ($this->user_access[$this->module_id]['edit'] == 1) {
				if ($_POST) {
					$quick_edit_flag = ( ( $this->input->post('quick_edit_flag') && $this->input->post('quick_edit_flag') == "true" ) ? true : false );

					foreach ($_POST as $fieldname => $fieldvalue) {
						$post[$fieldname] = $fieldvalue;
					}

					//set key field if adding new record
					if (strcmp($post['record_id'], '-1') == 0) {
						$new_record = true;
					} else {
						$new_record = false;
						$post[$this->key_field] = $post['record_id'];
						unset($post['record_id']);
					}

					// start insert/update
					//get the fields of main table
					$this->db->select('field.fieldname, field.column, field.uitype_id, field.datatype');
					$this->db->join('fieldgroup', 'fieldgroup.fieldgroup_id = field.fieldgroup_id');
					$where = array('field.module_id' => $this->module_id, "field.table" => $this->module_table, 'field.deleted' => 0, 'fieldgroup.deleted' => 0);
					if ($quick_edit_flag)
						$where['quick_edit'] = 1;
					$this->db->where($where);
					$this->db->from('field');
					$fieldset = $this->db->get();
					$fieldset = $fieldset->result_array();
					$row = array();
					foreach ($fieldset as $field_index => $field) {
						if (isset($post[$field['fieldname']]) || ($field['uitype_id'] == 24 && isset($post[$field['fieldname'] . '_from']) || isset($post[$field['fieldname'] . '_to']) ) || ($field['uitype_id'] == 26 && isset($post[$field['fieldname'] . '_start_hh']) || isset($post[$field['fieldname'] . '_start_mm']) || isset($post[$field['fieldname'] . '_end_hh']) || isset($post[$field['fieldname'] . '_end_mm']) )) {
							if ($field['uitype_id'] == 5) {
								//format date field to something db can read
								if ($post[$field['fieldname']] != "") {
									$temp = explode('/', $post[$field['fieldname']]);
									$temp = $temp[2] . '-' . $temp[0] . '-' . $temp[1];
									$row[$field['column']] = $temp;
								}
							} elseif ($field['uitype_id'] == 10) {
								//handle password field
								if ($post[$field['fieldname']] != "") {
									//set password md5
									$row[$field['column']] = md5($post[$field['fieldname']]);
								}
							} else if ($field['uitype_id'] == 19) {
								//handle days array field
								$row[$field['column']] = sizeof($post[$field['fieldname']]) > 0 ? serialize($post[$field['fieldname']]) : "";
							} else if ($field['uitype_id'] == 24) {
								//format date from and date to field to something db can read
								if ($post[$field['fieldname'] . '_from'] != "") {
									$temp = explode('/', $post[$field['fieldname'] . '_from']);
									$temp = $temp[2] . '-' . $temp[0] . '-' . $temp[1];
									$row[$field['column'] . '_from'] = $temp;
								} else {
									$row[$field['column'] . '_from'] = "";
								}

								if ($post[$field['fieldname'] . '_to'] != "") {
									$temp = explode('/', $post[$field['fieldname'] . '_to']);
									$temp = $temp[2] . '-' . $temp[0] . '-' . $temp[1];
									$row[$field['column'] . '_to'] = $temp;
								} else {
									$row[$field['column'] . '_to'] = "";
								}
							} else if ($field['uitype_id'] == 26) {
								//format time start and time to field to something db can read
								$starthh = $post[$field['fieldname'] . '_start_hh'];
								$startmm = $post[$field['fieldname'] . '_start_mm'];

								$endhh = $post[$field['fieldname'] . '_end_hh'];
								$endmm = $post[$field['fieldname'] . '_end_mm'];
								if ($starthh != "" && $startmm != "") {
									$temp = $starthh . ':' . $startmm;
									$row[$field['column'] . '_start'] = $temp;
								} else {
									$row[$field['column'] . '_start'] = "";
								}

								if ($endhh != "" && $endmm != "") {
									$temp = $endhh . ':' . $endmm;
									$row[$field['column'] . '_end'] = $temp;
								} else {
									$row[$field['column'] . '_end'] = "";
								}
							} else {
								//handle floats and integers, remove commas
								$numeric = false;
								$datatypes = explode('~', $field['datatype']);
								foreach ($datatypes as $datatype) {
									if ($datatype == "I" || $datatype == "F")
										$numeric = true;
								}
								if ($numeric)
									$post[$field['fieldname']] = str_replace(",", "", $post[$field['fieldname']]);
								$row[$field['column']] = $post[$field['fieldname']];
							}
						}
					}//end foreach fieldset
					//check if new record
					if ($new_record) {
						//insert to main table
						$this->db->insert($this->module_table, $row);
						$post[$this->key_field] = $this->db->insert_id();
					} else {
						//update main table
						unset($row['date_created']);						
						$this->db->update($this->module_table, $row, array($this->key_field => $post[$this->key_field]));
					}
					// end insert/update
					//handle other field saved in different table
					foreach ($this->related_table as $table => $key_field) {
						//get field set of table
						$this->db->select('field.fieldname, field.column, field.uitype_id, field.datatype');
						$this->db->join('fieldgroup', 'fieldgroup.fieldgroup_id = field.fieldgroup_id');
						$where = array('field.module_id' => $this->module_id, "field.table" => $table, 'field.deleted' => 0, 'fieldgroup.deleted' => 0);
						if ($quick_edit_flag)
							$where['quick_edit'] = 1;
						$this->db->where($where);
						$this->db->from('field');
						$fieldset = $this->db->get();
						$fieldset = $fieldset->result_array();
						$row = array($key_field => $post[$this->key_field]);
						foreach ($fieldset as $field_index => $field) {
							if (isset($post[$field['fieldname']]) || ($field['uitype_id'] == 24 && isset($post[$field['fieldname'] . '_from']) || isset($post[$field['fieldname'] . '_to']) ) || ($field['uitype_id'] == 26 && isset($post[$field['fieldname'] . '_start_hh']) || isset($post[$field['fieldname'] . '_start_mm']) || isset($post[$field['fieldname'] . '_end_hh']) || isset($post[$field['fieldname'] . '_end_mm']) )) {
								if ($field['uitype_id'] == 5) {
									//format date field to something db can read
									if ($post[$field['fieldname']] != "") {
										//set password md5
										$temp = explode('/', $post[$field['fieldname']]);
										$temp = $temp[2] . '-' . $temp[0] . '-' . $temp[1];
										$row[$field['column']] = $temp;
									}
								} elseif ($field['uitype_id'] == 10) {
									//handle password field
									if ($post[$field['fieldname']] != "") {
										//set password md5
										$row[$field['column']] = md5($post[$field['fieldname']]);
									}
								} else if ($field['uitype_id'] == 19) {
									//handle days array field
									$row[$field['column']] = sizeof($post[$field['fieldname']]) > 0 ? serialize($post[$field['fieldname']]) : "";
								} else if ($field['uitype_id'] == 24) {
									//format date from and date to field to something db can read
									if ($post[$field['fieldname'] . '_from'] != "") {
										$temp = explode('/', $post[$field['fieldname'] . '_from']);
										$temp = $temp[2] . '-' . $temp[0] . '-' . $temp[1];
										$row[$field['column'] . '_from'] = $temp;
									} else {
										$row[$field['column'] . '_from'] = "";
									}

									if ($post[$field['fieldname'] . '_to'] != "") {
										$temp = explode('/', $post[$field['fieldname'] . '_to']);
										$temp = $temp[2] . '-' . $temp[0] . '-' . $temp[1];
										$row[$field['column'] . '_to'] = $temp;
									} else {
										$row[$field['column'] . '_to'] = "";
									}
								} else if ($field['uitype_id'] == 26) {
									//format time start and time to field to something db can read
									$starthh = $post[$field['fieldname'] . '_start_hh'];
									$startmm = $post[$field['fieldname'] . '_start_mm'];

									$endhh = $post[$field['fieldname'] . '_end_hh'];
									$endmm = $post[$field['fieldname'] . '_end_mm'];
									if ($starthh != "" && $startmm != "") {
										$temp = $starthh . ':' . $startmm;
										$row[$field['column'] . '_start'] = $temp;
									} else {
										$row[$field['column'] . '_start'] = "";
									}

									if ($endhh != "" && $endmm != "") {
										$temp = $endhh . ':' . $endmm;
										$row[$field['column'] . '_end'] = $temp;
									} else {
										$row[$field['column'] . '_end'] = "";
									}
								} else {
									//handle floats and integers, remove commas
									$numeric = false;
									$datatypes = explode('~', $field['datatype']);
									foreach ($datatypes as $datatype) {
										if ($datatype == "I" || $datatype == "F")
											$numeric = true;
									}
									if ($numeric)
										$post[$field['fieldname']] = str_replace(",", "", $post[$field['fieldname']]);
									$row[$field['column']] = $post[$field['fieldname']];
								}
							}
						}//end foreach fieldset
						$this->db->where($key_field, $post[$this->key_field]);
						// Check if an entry already exists for this related table.j
						$record = $this->db->get($table);

						if ($new_record || !$record) {
							$this->db->insert($table, $row);
						} else {
							$this->db->update($table, $row);
						}

						unset($row);
					}
					// dbug($this->key_field_val);s
					

					if ($this->db->_error_message() == "") {
						if (!$post[$this->key_field]) {
							$response->record_id = NULL;
							$response->msg = "Insufficient data supplied.";
							$response->msg_type = 'error';

						}else{

							if ($this->input->post('on_success') == "email"){
								$response->msg = 'Data has been successfully saved and sent.';
							}
							else{
								$response->msg = 'Data has been successfully saved.';
							}
							$response->msg_type = 'success';
							$response->record_id = $post[$this->key_field];
							$this->key_field_val = $response->record_id;
						}
					} else {
						$response->msg = $this->db->_error_message();
						$response->msg_type = 'error';
					}
				} else {
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'error';
				}
			} else {
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}

			return $response;
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	/**
	 * Performs validation on leave request per employee_id
	 * 
	 * @param int $employee_id
	 * @return mixed 
	 */
	private function _validate_leave($employee_id) {
		$this->db->where('employee_id', $employee_id);
		$this->db->where($this->key_field . ' <>', $this->input->post('record_id'));
		$this->db->where('deleted', 0);

		$record = $this->db->get($this->module_table);

		$error = array();

		if (!isset($record) || $record->num_rows() == 0) {
			return true;
		} else {
			$leave_requests = $record->result_array();

			// Check for leave credits.
			// Check if leave has been placed on the date.
			$date_to = strtotime($this->input->post('date_to'));
			$date_from = strtotime($this->input->post('date_from'));
			foreach ($leave_requests as $request) {
				$request_date_from = strtotime($request['date_from']);
				$request_date_to = strtotime($request['date_to']);

				if ($date_from >= $request_date_from && $date_to <= $request_date_to) {
					$error[] = 'You have already filed a leave within the requested dates.';
					continue;
				}
			}
		}

		if (count($error) > 0) {
			return $error;
		}

		return true;
	}

	function change_status_multiple($record_id = 0){

		$form_status_id = $this->input->post('form_status_id');
		$record_id = explode(',',$this->input->post('record_id'));

		$this->db->where_in($this->key_field, $record_id);
		$result = $this->db->get($this->module_table);

		$err_ctr = 0;
		$err_msg = array();
		$success_ctr = 0;
		$status_record = "";
		$total_ctr = 0;

		$status = "";
		switch($form_status_id){
			case 3:
			$status = "Approved";
			break;
			case 4:
			$status = "Disapproved";
			break;
		}

		$response['sequence'] ="";

		foreach( $result->result_array() as $record ){

			$response['sequence'] .= ','.$record['employee_leave_id'];

			$rec = $this->db->get_where( $this->module_table, array( $this->key_field => $record['employee_leave_id'] ) )->row();

			if( $this->_can_approve($rec) || $this->_can_decline($rec)  ){
				$status_result = $this->change_status($record['employee_leave_id'],1);

				if( $status_result['json']['type'] == 'error' ){
					$err_ctr++;
				}
				else{
					$success_ctr++;
				}

			}else{
				$err_ctr++;
			}

			$total_ctr++;
			
		}

		if( $err_ctr == 0 ){

			if( $success_ctr > 1 ){
				$response['message'] = $success_ctr.' out of '.$total_ctr.' Leave Application(s) have been '.$status;
			}
			else{
				$response['message'] = $success_ctr.' out of '.$total_ctr.' Leave Application(s) has been '.$status;
			}
                              				
			$response['type'] = 'success';
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}
		else{

			if( $success_ctr > 1 ){
				$response['message'] = $success_ctr.' out of '.$total_ctr.' Leave Application(s) have been '.$status.'<br /> Please check on those not approved <br />'; 
			}
			else{
				$response['message'] = $success_ctr.' out of '.$total_ctr.' Leave Application(s) has been '.$status.'<br /> Please check on those not approved <br />'; 
			}                            				

			$response['type'] = 'error';
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}

	}

	function change_status($record_id = 0, $non_ajax = 0) {
		$this->leaves->_change_status( $record_id, $non_ajax );
	}


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

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }

        if ($this->user_access[$this->module_id]['approve']) {
        	$buttons .= "<div class='icon-label'><a class='icon-16-approve approve-array status-buttons' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Approve</span></a></div>";
        }

        if ($this->user_access[$this->module_id]['decline']) {
        	$buttons .= "<div class='icon-label'><a class='".(CLIENT_DIR == 'hdi' ? 'icon-16-disapprove' : 'icon-16-cancel')." disapprove-array status-buttons' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Disapprove</span></a></div>";
    	}

        $buttons .= "</div>";
                
		return $buttons;
	}

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {

		$this->db->join('hr_employee','hr_employee.employee_id = '.$this->module_table.'.employee_id');
		$this->db->where($this->key_field, $row[$this->key_field]);
		$rec = $this->db->get( $this->module_table)->row();

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		// Right align action buttons.
		$actions = '<span class="icon-group">';


		$from = new DateTime( date('Y-m-d', strtotime($rec->date_from)) );
    	$to = new DateTime( date('Y-m-d', strtotime($rec->date_to)) );
		$interval = $to->diff($from);


		if ($this->_can_approve( $rec )) {
			/*if($rec->remarked_by_hr == 0 && $rec->application_form_id == 1){
				$actions .= '<a class="icon-button icon-16-comments comments-single" tooltip="HR Validation" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}*/

			if( $rec->location_id == '13' ){
				if (($interval->d + 1) >= $this->config->item('mindays_sickleave_validation') && $rec->application_form_id == 1) {
					if ($this->config->item('require_documents_sl_validation') || $rec->documents == '' ) {
						$actions .= '<a class="icon-button icon-16-comments comments-single" tooltip="HR Validation" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
					}
				}
			}

			$actions .= '<a class="icon-button icon-16-approve approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		}

		if ( $this->_can_decline( $rec ) ) {
			$actions .= '<a class="icon-button icon-16-cancel decline-single" tooltip="Disapprove" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		}

		if ( $this->_can_cancel( $rec ) && $rec->employee_id != $this->user->user_id ) {
			$qry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leaves_dates WHERE employee_leave_id = '{$rec->employee_leave_id}'");
			if($qry->num_rows() > 1) {
				$actions .= '<a class="icon-button icon-16-cancel cancel-single" form_status="many" tooltip="Cancel" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			} else {
				$actions .= '<a class="icon-button icon-16-cancel cancel-single" form_status="one" tooltip="Cancel" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}	
		}

		if ($this->user_access[$this->module_id]['view']) {
			$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
		}

		if ( $this->user_access[$this->module_id]['hr_health'] && $rec->form_status_id == 6 ) {
			$actions .= '<a class="icon-button icon-16-send-email" module_link="' . $module_link . '" tooltip="Send To Approver" href="javascript:void(0)" onclick="javascript:sent_to_approver('.$rec->employee_leave_id.')"></a>';
		}

		if ($this->user_access[$this->module_id]['print']) {
			$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if ($this->user_access[$this->module_id]['edit'] && $rec->form_status_id == 1 && $rec->employee_id == $this->user->user_id) {
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if($this->user_access[$this->module_id]['edit'] && $rec->application_form_id == 5 && $this->user_access[$this->module_id]['hr_health'] && $rec->form_status_id == 3)
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';

		// remove delete to all user's with publish. :D
		if($this->user_access[$this->module_id]['publish'] && $this->user_access[$this->module_id]['delete'] && in_array($rec->form_status_id, array(1,2)) && $rec->employee_id == $this->user->user_id) {
			$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		} else if(($this->user_access[$this->module_id]['delete'] && in_array( $rec->form_status_id, array(1,2)) && $this->user_access[$this->module_id]['publish'] == 0)) {
			$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		}

		$actions .= '</span>';

		return $actions;
	}

	private function _can_approve( $rec ) {
		
		if( $rec->form_status_id == 2 && $this->user_access[$this->module_id]['approve'] == 1){
			$approver = $this->db->get_where('leave_approver', array('leave_id' => $rec->employee_leave_id, 'approver' => $this->user->user_id));
			if( $approver->num_rows() == 1 ){
				$approver = $approver->row();
				if( $approver->status == 2 ){
					return true;
				}
			}

            if (CLIENT_DIR == 'firstbalfour'){
                if ($this->user_access[$this->module_id]['post'] == 1){
                    return true;
                }
            }

			return false;
		}
		return false;
	}

	private function _can_decline( $rec ) {
		if( $rec->form_status_id == 2 && $this->user_access[$this->module_id]['decline'] == 1){
			$approver = $this->db->get_where('leave_approver', array('leave_id' => $rec->employee_leave_id, 'approver' => $this->user->user_id));
			if( $approver->num_rows() == 1 ){
				$approver = $approver->row();
				if( $approver->status == 2 ){
					return true;
				}
			}

            if (CLIENT_DIR == 'firstbalfour'){
                if ($this->user_access[$this->module_id]['post'] == 1){
                    return true;
                }
            }
            
			return false;
		}
		return false;
	}

	private function _can_cancel( $rec ) {
		if( $rec->form_status_id == 3 && $this->user_access[$this->module_id]['cancel'] == 1){
			return true;
		}
		return false;
	}

	/**
	 * Send the email to approvers.
	 */
	function send_email() {
		$this->leaves->_send_email();	
	}	

	function send_status_email( $record_id, $status_id, $decline_remarks = false){
		$this->leaves->_send_status_email( $record_id, $status_id, $decline_remarks);
    }

	function get_affected_dates( $call_from_within = false ) {

		$this->leaves->_get_affected_dates( $call_from_within );
	}

	function get_ml_specifics()
	{
		$this->leaves->_get_ml_specifics();
	}

	function get_remarks_form(){
		
		$fit_to_work = 0;
		$remarks = "";

		$result = $this->db->get_where($this->module_table,array('employee_leave_id'=>$this->input->post('record_id')))->row();

		if(!empty($result)){

			$fit_to_work = $result->fit_to_work;
			$remarks = $result->remarks;
		}

		$_POST['fit_to_work'] = $fit_to_work;
		$_POST['remarks'] = $remarks;

		$response['form'] = $this->load->view($this->userinfo['rtheme'] .'/'. $this->module_link.'/remarks-form', '', true);	
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function save_remarks(){
		$data = array(
			'fit_to_work' => $this->input->post('fit_to_work'),
			'remarks' => $this->input->post('remarks'),
			'remarked_by_hr' => 1,
		//	'form_status_id' => 2
		);

		$this->db->update( $this->module_table, $data, array($this->key_field => $this->input->post('record_id')) );
		$response->record_id = $this->input->post('record_id');
		$response->msg = "success";
		$response->msg_type = "success";
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function sent_to_approver(){
		if (CLIENT_DIR == 'firstbalfour'){
			$this->db->where('employee_leave_id',$this->input->post('record_id'));
			$this->db->where('deleted',0);
			$result = $this->db->get('employee_leaves');

			if ($result && $result->num_rows() > 0){
				$row = $result->row();

				if ($row->application_form_id == 6){
					$this->db->where('employee_id',$row->employee_id);
					$leave_initial_setup = $this->db->get('employee_leaves_paternity_initial_setup');

					if ($leave_initial_setup && $leave_initial_setup->num_rows() > 0){
						$leave_init = $leave_initial_setup->row();
						$date_given = $leave_init->date_given;
						$date_to_reset = date('Y-m-d', strtotime('+60 days', strtotime($date_given)));

						if (date('Y-m-d') > $date_to_reset){
							$this->db->where('employee_id',$row->employee_id);
							$this->db->update('employee_leaves_paternity_initial_setup',array("date_given" => $row->date_created));	

							$this->db->where('year', date('Y', strtotime($row->date_created)));
							$this->db->where('employee_id', $row->employee_id);
							$this->db->where('deleted', 0);
							$this->db->update('employee_leave_balance',array("mpl" => 7,"mpl_used" => 0));								
						}
					}
					else{
						$this->db->insert('employee_leaves_paternity_initial_setup',array("employee_id" => $row->employee_id,"date_given" => $row->date_created));

						$this->db->where('year', date('Y', strtotime($row->date_created)));
						$this->db->where('employee_id', $row->employee_id);
						$this->db->where('deleted', 0);
						$this->db->update('employee_leave_balance',array("mpl" => 7));						
					}
				}
				elseif ($row->application_form_id == 4){  // funeral leave
					$this->db->where('employee_id',$row->employee_id);
					$leave_initial_setup = $this->db->get('employee_leaves_funeral_initial_setup');

					if ($leave_initial_setup && $leave_initial_setup->num_rows() > 0){
						$leave_init = $leave_initial_setup->row();
						$date_given = $leave_init->date_given;
						$date_to_reset = date('Y', strtotime($date_given));

						if (date('Y') > $date_to_reset){
							$this->db->where('employee_id',$row->employee_id);
							$this->db->update('employee_leaves_funeral_initial_setup',array("date_given" => $row->date_created));	

							$this->db->where('year', date('Y', strtotime($row->date_created)));
							$this->db->where('employee_id', $row->employee_id);
							$this->db->where('deleted', 0);
							$this->db->update('employee_leave_balance',array("fl" => 5,"fl_used" => 0));								
						}
					}
					else{
						$this->db->insert('employee_leaves_funeral_initial_setup',array("employee_id" => $row->employee_id,"date_given" => $row->date_created));

						$this->db->where('year', date('Y', strtotime($row->date_created)));
						$this->db->where('employee_id', $row->employee_id);
						$this->db->where('deleted', 0);
						$this->db->update('employee_leave_balance',array("fl" => 5));						
					}					
				}
				elseif ($row->application_form_id == 17){
					$this->db->where('employee_id',$row->employee_id);
					$leave_initial_setup = $this->db->get('employee_leaves_plsp_initial_setup');

					if ($leave_initial_setup && $leave_initial_setup->num_rows() > 0){
						$leave_init = $leave_initial_setup->row();
						$date_given = $leave_init->date_given;
						$date_to_reset = date('Y', strtotime($date_given));

						if (date('Y') > $date_to_reset){
							$this->db->where('employee_id',$row->employee_id);
							$this->db->update('employee_leaves_plsp_initial_setup',array("date_given" => $row->date_created));	

							$this->db->where('year', date('Y', strtotime($row->date_created)));
							$this->db->where('employee_id', $row->employee_id);
							$this->db->where('deleted', 0);
							$this->db->update('employee_leave_balance',array("plsp" => 7,"plsp_used" => 0));								
						}
					}
					else{
						$this->db->insert('employee_leaves_plsp_initial_setup',array("employee_id" => $row->employee_id,"date_given" => $row->date_created));

						$this->db->where('year', date('Y', strtotime($row->date_created)));
						$this->db->where('employee_id', $row->employee_id);
						$this->db->where('deleted', 0);
						$this->db->update('employee_leave_balance',array("plsp" => 7));						
					}					
				}
				elseif ($row->application_form_id == 21){
					$this->db->where('employee_id',$row->employee_id);
					$leave_initial_setup = $this->db->get('employee_leaves_ul_initial_setup');

					if ($leave_initial_setup && $leave_initial_setup->num_rows() > 0){
						$leave_init = $leave_initial_setup->row();
						$date_given = $leave_init->date_given;
						$date_to_reset = date('Y', strtotime($date_given));

						if (date('Y') > $date_to_reset){
							$this->db->where('employee_id',$row->employee_id);
							$this->db->update('employee_leaves_ul_initial_setup',array("date_given" => $row->date_created));	

							$this->db->where('year', date('Y', strtotime($row->date_created)));
							$this->db->where('employee_id', $row->employee_id);
							$this->db->where('deleted', 0);
							$this->db->update('employee_leave_balance',array("ul" => 12,"ul_used" => 0));								
						}
					}
					else{
						$this->db->insert('employee_leaves_ul_initial_setup',array("employee_id" => $row->employee_id,"date_given" => $row->date_created));

						$this->db->where('year', date('Y', strtotime($row->date_created)));
						$this->db->where('employee_id', $row->employee_id);
						$this->db->where('deleted', 0);
						$this->db->update('employee_leave_balance',array("ul" => 12));						
					}					
				}

				$data = array(
					'form_status_id' => 2
				);

				$this->db->update( $this->module_table, $data, array($this->key_field => $this->input->post('record_id')) );
				$this->db->update('leave_approver', array('status' => 2), array( 'leave_id' => $this->input->post('record_id'))); 				
			}

			$this->send_email_to_approver_from_hr();
		}
		else{
			$data = array(
				'form_status_id' => 2
			);

			$this->db->update( $this->module_table, $data, array($this->key_field => $this->input->post('record_id')) );
			$this->db->update('leave_approver', array('status' => 2), array( 'leave_id' => $this->input->post('record_id'))); 			
		}

		$response->record_id = $this->input->post('record_id');
		$response->msg = "Success";
		$response->msg_type = "success";
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function invalid_request(){
		$data = array(
			'form_status_id' => 8
		);

		$this->db->update( $this->module_table, $data, array($this->key_field => $this->input->post('record_id')) );
		$this->db->update('leave_approver', array('status' => 8), array( 'leave_id' => $this->input->post('record_id'))); 			

		$this->send_email_to_requestor();

		$response->record_id = $this->input->post('record_id');
		$response->msg = "Success";
		$response->msg_type = "success";
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function send_email_to_approver_from_hr(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

		$profile_result = $this->db->get_where('profile', array('deleted' => 0))->result();
		$app_form_id = $this->db->get_where('employee_leaves', array('employee_leave_id' => $this->input->post('record_id')));//->row()->application_form_id;
		
		$this->db->join('user','user.employee_id=employee_leaves.employee_id', 'left');
		$this->db->join('employee','employee.employee_id=user.employee_id', 'left');
		$this->db->join('employee_leave_balance','employee_leave_balance.employee_id=employee_leaves.employee_id', 'left');
		$this->db->join('form_status','form_status.form_status_id=employee_leaves.form_status_id', 'left');
		$this->db->join('employee_form_type','employee_form_type.application_form_id=employee_leaves.application_form_id', 'left');
		$this->db->join('user_company','user_company.company_id=user.company_id', 'left');
		$this->db->where('employee_leaves.employee_leave_id', $this->input->post('record_id'));

		$request = $this->db->get('employee_leaves');

		if ( $request->num_rows() > 0) {
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$request = $request->row_array();
				$request['reason'] = 'Reason: '.$request['reason'];
				// get reason for el
				if($request['application_form_id'] == 3)
				{
					$this->db->select('employee_leaves_rt.reason_type,calamity_remarks');
					$this->db->join('employee_leaves_rt', 'employee_leaves_el.reason_type_id = employee_leaves_rt.reason_type_id', 'left');
					$el = $this->db->get_where('employee_leaves_el', array("employee_leaves_el.employee_leave_id" => $this->input->post('record_id')));
					if($el && $el->num_rows() > 0)
					{
						$el_reason = $el->row();
						$request['reason'] = 'Reason: '.$el_reason->reason_type.' <br/>Remarks:'.$el_reason->calamity_remarks;
					}
				}

				$this->db->where('leave_id', $this->input->post('record_id'));
				$this->db->where('focus', 1);
				$this->db->order_by('sequence', 'desc');
				$approver_user = $this->db->get('leave_approver');				
				$leave_type = $request['application_form'];
				if($approver_user && $approver_user->num_rows() > 0 ){
					foreach ($approver_user->result() as $a) {
						switch($a->condition){
							case 1:
								if(!isset( $app_array) ) $app_array[] = $a->approver;
								break;
							case 2:
							case 3:
								$app_array[] = $a->approver;
								break;
						}
					}

					$this->db->where_in('user_id', $app_array);
					$result = $this->db->get('user');
					$result = $result->result_array();
					foreach ($result as $row) {
						if (CLIENT_DIR  == 'firstbalfour'){
                            $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname'];
                            if ($row['aux'] != ''){
                                $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname']." ".$row['aux'];
                            }                            							
						}
						else{
							$request['approver_user'] = $row['salutation']." ".$row['lastname'];
						}
					}
				}

				$this->db->where('employee_id',$request['employee_id']);
                $this->db->where('form_status_id =','2');
                $arr=$this->db->get('employee_leaves');
                //echo count($arr);
                if($this->config->item('show_with_carried') == 0)
                {
	                if(count($arr)>0)
	                {
	                    $vl_pen=0;
	                    $sl_pen=0;
	                    $el_pen=0;
	                    $mpl_pen=0;
	                    $bl_pen=0;
	                    $arr=$arr->result_array();
	                    foreach($arr as $key_field=>$key_field_val)
	                    {
	                        if($key_field_val['application_form_id']==1)
	                            $vl_pen++;
	                        if($key_field_val['application_form_id']==2) 
	                            $sl_pen++;
	                        if($key_field_val['application_form_id']==3)
	                            $el_pen++;
	                        if($key_field_val['application_form_id']==4)
	                            $mpl_pen++;
	                        if($key_field_val['application_form_id']==5 || $key_field_val['application_form_id']==6)                
	                            $bl_pen++;
	                    }
	                    $request['vl_pen']=$vl_pen;
	                    $request['sl_pen']=$sl_pen;
	                    $request['el_pen']=$el_pen;
	                    $request['mpl_pen']=$mpl_pen;
	                    $request['bl_pen']=$bl_pen;
	                }
	                else
	                {
	                    $request['vl_pen']='0';
	                    $request['sl_pen']='0';
	                    $request['el_pen']='0';
	                    $request['mpl_pen']='0';
	                    $request['bl_pen']='0';
	                }
	                $total_vl_used = $request['vl_used']+$request['el_used'];
	                $request['vl_bal']=number_format($request['vl'] - $total_vl_used,2,'.',',');
	                $request['sl_bal']=number_format($request['sl'] - $request['sl_used'],2,'.',',');
	                $request['el_bal']=number_format($request['el'] - $request['el_used'],2,'.',',');
	                $request['mpl_bal']=number_format($request['mpl'] - $request['mpl_used'],2,'.',',');
	                $request['bl_bal']=number_format($request['bl'] - $request['bl_used'],2,'.',',');
	                
                } elseif($this->config->item('show_with_carried') == 1) {
                    if($request['vl'] < $request['vl_used'])
                    {
                        $deduct_previous = $request['vl_used'] - $request['vl'];
                        $request['vl_used'] = $request['vl'];
                        $request['prev_vl'] = $request['carried_vl'] - $deduct_previous;
                        $request['vl_bal'] = 0.00;
                    } else {
                    	$total_vl_used = $request['vl_used']+$request['el_used'];
                    	$request['vl_bal'] = number_format(($request['vl'] + $request['carried_vl']) - $total_vl_used,2,'.',',');
                    	$request['prev_vl'] = $request['carried_vl'];
                    }

                    if($request['sl'] < $request['sl_used'])
                    {
                        $deduct_previous = $request['sl_used'] - $request['sl'];
                        $request['sl_used'] = $request['sl'];
                        $request['prev_sl'] = $request['carried_sl'] - $deduct_previous;
                        $request['sl_bal'] = 0.00;
                    } else {
                    	$request['sl_bal'] = number_format(($request['sl'] + $request['carried_sl']) - $request['sl_used'],2,'.',',');
                    	$request['prev_sl'] = $request['carried_sl'];
                    }

                    if($request['bl'] < $request['bl_used'])
                    {
                        $deduct_previous = $request['bl_used'] - $request['bl'];
                        $request['bl_used'] = $request['bl'];
                        $request['prev_bl'] = $request['carried_bl'] - $deduct_previous;
                        $request['bl_bal'] = 0.00;
                    } else {
                    	$request['bl_bal'] = number_format(($request['bl'] + $request['carried_bl']) - $request['bl_used'],2,'.',',');
                    	$request['prev_bl'] = $request['carried_bl'];
                    }

                    $request['prev_el'] = '0.00';
                    $request['prev_mpl'] = '0.00';

                    // add by tirso. because balance appear as variables
	                $request['el_bal']=number_format($request['el'] - $request['el_used'],2,'.',',');
	                $request['mpl_bal']=number_format($request['mpl'] - $request['mpl_used'],2,'.',',');
	                $request['bl_bal']=number_format($request['bl'] - $request['bl_used'],2,'.',',');
	                $request['bol_bal']=number_format($request['bol'] - $request['bol_used'],2,'.',',');
	                $request['sil_bal']=number_format($request['sil'] - $request['sil_used'],2,'.',',');
	                $request['ul_bal']=number_format($request['ul'] - $request['ul_used'],2,'.',',');	                                     
	            }

                $request['here']=base_url().'forms/leaves/detail/'.$request['employee_leave_id'];
                $pieces=explode(" ",$request['date_created']);

                if (CLIENT_DIR == 'firstbalfour'){
                	$request['date_created']= date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
                }
                else{
                	$request['date_created']= date($this->config->item('display_date_format_email'),strtotime($pieces[0]));
                }

                $request['number_of_days']= floor((strtotime($request['date_to']) - strtotime($request['date_from'])) / (60 * 60 * 24)) + 1;
        	
				$request['number_of_days'] = 0;
                $leave_dates = $this->db->get_where('employee_leaves_dates', array('deleted' => 0, 'employee_leave_id' => $request['employee_leave_id']))->result();

                foreach( $leave_dates as $leave_date ){
                	$duration = $this->db->get_where('employee_leaves_duration', array('duration_id' => $leave_date->duration_id))->row();
                	$request['number_of_days'] += $duration->credit / 8;
            	}

            	$this->db->where("('".$request['date_to']."' >= date_from && '".$request['date_to']."' <= date_to)");
            	// $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
				$tkp = $this->db->get('timekeeping_period');

				if($tkp->num_rows() > 0 && $tkp) {
					$request['cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->cutoff));
					$request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
				} else {
					$request['cutoff'] = 'Cutoff not defined';
					$request['payroll_cutoff'] = 'Cutoff not defined';
				}


				$request['year'] = date('Y');
				if (CLIENT_DIR == 'firstbalfour'){
					$request['date_from']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_from']));
	                $request['date_to']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_to']));					
				}
				else{
					$request['date_from']= date($this->config->item('display_date_format_email'),strtotime($request['date_from']));
	                $request['date_to']= date($this->config->item('display_date_format_email'),strtotime($request['date_to']));
				}

				$ws = $this->system->get_employee_worksched_shift($request['employee_id'], date('Y-m-d'));  
				
				$request['shift_schedule'] = date('g:i',strtotime($ws->shifttime_start)) . "-" . date('g:i a',strtotime($ws->shifttime_end));
				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template($this->module_id, 'new_leave_request');

				// Approvers.				

				if( is_array( $app_array ) && sizeof($app_array) > 0 ){

					$from = new DateTime( date('Y-m-d', strtotime($request['date_from'])) );
		        	$to = new DateTime( date('Y-m-d', strtotime($request['date_to'])) );
					$interval = $to->diff($from);

					$data['form_status_id'] = 2;

					if( $data['form_status_id'] == 2 ){

						$this->db->where_in('user_id', $app_array);
					}

					if (CLIENT_DIR  == 'firstbalfour'){
						$request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname'];
						if ($request['aux'] != ''){
							$request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname']." ".$request['aux'];
						}
					}

					$result = $this->db->get('user')->result_array();

					foreach ($result as $row) {
						$recepients[] = $row['email'];
						if (CLIENT_DIR  == 'firstbalfour'){
                            $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname'];
                            if ($row['aux'] != ''){
                                $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname']." ".$row['aux'];
                            }    							
						}
						else{
							$request['approver_user'] = $row['salutation']." ".$row['lastname'];
						}						
						$message = $this->template->prep_message($template['body'], $request);

						$cc_copy = $this->system->get_approvers_and_condition($request['employee_id'], $this->module_id, 'email');
						if( ( is_array( $cc_copy  ) && sizeof($cc_copy) > 0 ) ){
							foreach( $cc_copy as $cc_user ){
								$cc_user = $this->db->get_where('user',array('user_id'=> $cc_user['approver']))->row();
								if( !in_array(trim($cc_user->email), $recepients ) && !in_array(trim($cc_user->email), $cc ) )  $cc[] = trim( $cc_user->email );
							}
						}
		           
		                $cc_copy = '';
		                if(isset($cc)) $cc_copy = implode(',', $cc);

						// $this->template->queue(trim($row['email']), $cc_copy, $leave_type." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
						$this->template->queue(trim($row['email']), "", $leave_type." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
					}

					// If queued successfully set the status to For Approval.
					// original subject = $template['subject']
					if ( true ) {

						$data['email_sent'] = '1';
                    	$data['date_sent'] = date('Y-m-d G:i:s');		

						$this->db->where($this->key_field, $request[$this->key_field]);
						$this->db->update('employee_leaves', $data);

						$this->db->where_in('approver', $app_array);
						$this->db->where('leave_id', $this->input->post('record_id'));
						$this->db->update('leave_approver', array('status' => 2) );
					}

				}
			}			
		}
	}

	function send_email_to_requestor(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

		$this->db->join('user','user.employee_id=employee_leaves.employee_id', 'left');
		$this->db->join('employee','employee.employee_id=user.employee_id', 'left');
		$this->db->join('employee_leave_balance','employee_leave_balance.employee_id=employee_leaves.employee_id', 'left');
		$this->db->join('form_status','form_status.form_status_id=employee_leaves.form_status_id', 'left');
		$this->db->join('employee_form_type','employee_form_type.application_form_id=employee_leaves.application_form_id', 'left');
		$this->db->join('user_company','user_company.company_id=user.company_id', 'left');
		$this->db->where('employee_leaves.employee_leave_id', $this->input->post('record_id'));

		$request = $this->db->get('employee_leaves');

		if ( $request->num_rows() > 0) {
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$request = $request->row_array();

                $request['requestor'] = $request['salutation']." ".$request['firstname']." ".$request['lastname'];
                if ($request['aux'] != ''){
                    $request['requestor'] = $request['salutation']." ".$request['firstname']." ".$request['lastname']." ".$request['aux'];
                }   

            	$pieces=explode(" ",$request['date_created']);
            	$request['date_created']= date($this->config->item('display_date_format_email_fb'),strtotime($pieces[0]));
            	
                $request['number_of_days']= floor((strtotime($request['date_to']) - strtotime($request['date_from'])) / (60 * 60 * 24)) + 1;
        	
				$request['number_of_days'] = 0;
                $leave_dates = $this->db->get_where('employee_leaves_dates', array('deleted' => 0, 'employee_leave_id' => $request['employee_leave_id']))->result();

                foreach( $leave_dates as $leave_date ){
                	$duration = $this->db->get_where('employee_leaves_duration', array('duration_id' => $leave_date->duration_id))->row();
                	$request['number_of_days'] += $duration->credit / 8;
            	}

				$request['date_from']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_from']));
                $request['date_to']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_to']));					

				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template($this->module_id, 'leave_request_invalid');				

				$message = $this->template->prep_message($template['body'], $request);
				$this->template->queue(trim($request['email']), '', $request['application_form']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);				
			}
		}
	}

	function get_leave_type_dropdown()
	{
		$this->load->model('uitype_edit');
		$data['types'] = $this->uitype_edit->get_leave_dropdown( $this->input->post('user_id') );
		
	    foreach ($data['types'] as $key=> $row) {
	        $sort_col[$key] = $row['application_form'];
	    }

	    array_multisort(array_map('strtolower',$sort_col), SORT_ASC, $data['types']);

		$this->load->view('template/ajax', array('json' => $data));		
	}

	function get_for_approval_count()
	{
        $ret = 0;
        $leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '= 2', 'in (0,1)' );

        if ($leaves_to_approve) {
        	$ret = count($leaves_to_approve);
        }


        return $this->load->view('template/ajax', array('json' => array('count' => $ret)));
	}

	function get_employees()
	{
		$this->load->helper('form');

		$this->db->select('user_id, CONCAT(firstname, " ", lastname) name', false);
		$this->db->where('deleted', 0);		
		$this->db->where('role_id <>', 1);
		$users = $this->db->get('user');

		foreach ($users->result() as $user) {
			$options[$user->user_id] = $user->name;
		}

		$data['html'] = form_dropdown('employee_id[]', $options, '', 'multiple="multiple"');

		$this->load->view('template/ajax', $data);
	}

	/* START List View Functions */
	/**
	 * Available methods to override listview.
	 * 
	 * A. _append_to_select() - Append fields to the SELECT statement via $this->listview_qry
	 * B. _set_filter()       - Add aditional WHERE clauses	 
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
		$this->db->select('count(*) as record_count', false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}


		$this->listview_qry .= ',blanket_id';

		//get list
		$total_records =  $this->db->count_all_results();

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $total_records > 0 ? ceil($total_records/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = intval($total_pages);
			$response->records = $total_records;

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			
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
			// $response->last_query2 = $this->db->last_query();

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
					$columns_data = $result->field_data();
					$column_type = array();
					foreach($columns_data as $column_data){
						$column_type[$column_data->name] = $column_data->type;
					}
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
								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39) ) ){
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
								else{
									$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
								}

								if ($detail['name'] == 'application_form_id' && !is_null($row['blanket_id'])) {
									$cell[$cell_ctr] .= ' (Blanket)';
								}

								if( $detail['name'] == "t4form_status" ){


									if( $row[$detail['name']] == "Approved" ){

										$employee_leave_info = $this->db->get_where('employee_leaves',array('employee_leave_id' => $row['employee_leave_id'] ))->row();

										if( $employee_leave_info->date_approved != '0000-00-00 00:00:00'){

											$cell[$cell_ctr] .= "<br /><p class='blue small'>As of ".date($this->config->item('display_date_format'),strtotime($employee_leave_info->date_approved))."</p>";

										}

									}

									//get approver and status
									$qry = "SELECT a.*, a.status, b.form_status, CONCAT(c.firstname, ' ', c.lastname) as name
									FROM {$this->db->dbprefix}leave_approver a
									LEFT JOIN {$this->db->dbprefix}form_status b on b.form_status_id = a.status
									LEFT JOIN {$this->db->dbprefix}user c on c.user_id = a.approver
									WHERE a.leave_id = {$row['employee_leave_id']} ORDER BY sequence ASC";
									$approvers = $this->db->query( $qry );
									$leave = $this->db->get_where($this->module_table, array($this->key_field => $row['employee_leave_id']))->row();
									if($approvers->num_rows() > 1){
										foreach($approvers->result() as $approver){
											$add_status = false;
											switch( $leave->form_status_id ){
												case 2: // for approval
												case 7: // fit to work
													if($approver->condition == 1){
														if($approver->focus == 0) $approver->form_status = "Waiting...";
														$add_status = true;
													}
													if($approver->condition == 2 && $approver->status == 3) $add_status = true;
													if($approver->condition == 3){
														if($approver->status == 2) $approver->form_status = "Waiting approval";
														$add_status = true;
													}
													break;
												case 3: // approved
                                                    if(CLIENT_DIR == "firstbalfour"){
                                                        if($approver->condition == 2){
                                                            $add_status = true;
                                                        }
                                                    }else{
                                                        if($approver->condition == 2 && $approver->status == 3 ){
                                                            $add_status = true;
                                                        }
                                                    }
													break;
												case 4: // Declined
													if($approver->status == 4) $add_status = true;
													break;
												case 5: // Declined
													if($approver->status == 5) $add_status = true;
													break;	
												
											}


											if( $add_status ){
												$cell[$cell_ctr] .= '<br/><em class="small">';
												$cell[$cell_ctr] .= $approver->name .': ';
												switch($approver->status){
													case 2:
													case 6:  
														$class = 'orange';
														break;
													case 3: 
														$class = 'green';
														break;	
													case 4:
													case 5: 
														$class = 'red';
														break;	
												}
                                                if(CLIENT_DIR == "firstbalfour"){
                                                    if($approver->status > 2){
                                                        $cell[$cell_ctr] .= '<span class="'.$class.'">'. $approver->form_status .'</span>';
                                                    }
                                                }else{
                                                        $cell[$cell_ctr] .= '<span class="'.$class.'">'. $approver->form_status .'</span>';
                                                }
												$cell[$cell_ctr] .= '</em>';
											}
										}
									}
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

	//change for paternity JR
	function get_actual_delivery_date() {
		$this->leaves->_get_actual_delivery_date();
	}

	function get_approvers(){
		$response->approvers = 'admin';

		if ($this->input->post('employee_id') != 1){
			$data['approvers'] = $this->system->get_approvers_and_condition( $this->input->post('employee_id'), $this->module_id );
			if (!empty($data['approvers'])){
				$response->approvers = $this->load->view($this->userinfo['rtheme'].'/forms/approvers', $data, true);
			}
			else{
				$response->approvers = '';
			}
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_user_info() {
		$this->leaves->_get_user_info();
	}

	function get_employee_info() {
		$this->leaves->_get_employee_info();
	}

	function get_user_info_via_leave() {
		$this->db->where('employee_leaves.employee_leave_id', $this->input->post('record_id'));
		$this->db->where('employee_leaves.deleted', 0);
		$this->db->join('user','user.employee_id = employee_leaves.employee_id','left');
		$userinfo = $this->db->get('employee_leaves');

		if ($userinfo->num_rows() > 0) {
			$response = $userinfo->row();
		} else {
			$response = false;
		}

		if (IS_AJAX) {
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			return $response;
		}
	}

	function check_if_hra()
	{
		if (!IS_AJAX) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$response->data = $this->user_access[$this->module_id]['hr_health'];

			$this->load->view('template/ajax', array('json' => $response));
		}
	}

	function get_bol_report_date()
	{
		if (!IS_AJAX) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$response->date = $this->system->get_next_working_day($this->input->post('employee_id'), $this->input->post('date'));

			$this->load->view('template/ajax', array('json' => $response));
		}
	}
	
	function fix_credit(){
		$year = date('Y');
		$qry = "select b.*, a.application_form_id, a.employee_id, a.form_status_id
		FROM {$this->db->dbprefix}employee_leaves a
		LEFT JOIN {$this->db->dbprefix}employee_leaves_dates b on b.employee_leave_id = a.employee_leave_id
		WHERE a.form_status_id = 3 AND a.deleted = 0 AND YEAR(b.date) = {$year} AND a.application_form_id <= 6";

		$result = $this->db->query( $qry );
		
		if( $result->num_rows() > 0 ){
			foreach( $result->result() as $row ){
				if( !isset( $emp[$row->employee_id] ) ){
					$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => date('Y', strtotime($row->date)), 'employee_id' => $row->employee_id, 'deleted' => 0) );	
					$emp[$row->employee_id] = $emp_balance->row_array();
					$emp[$row->employee_id]['sl_actual'] = 0;
					$emp[$row->employee_id]['vl_actual'] = 0;
					$emp[$row->employee_id]['el_actual'] = 0;
					$emp[$row->employee_id]['bl_actual'] = 0;
					$emp[$row->employee_id]['mpl_actual'] = 0;
				}
				
				switch( $row->application_form_id ){
					case 1: //SL
						$emp[$row->employee_id]['sl_actual'] += $row->credit;
						break;
					case 2: //VL
						$emp[$row->employee_id]['vl_actual'] += $row->credit;
						break;	
					case 3: //EL	
						$emp[$row->employee_id]['el_actual'] += $row->credit;
						break;
					case 4: //BL	
						$emp[$row->employee_id]['bl_actual'] += $row->credit;
						break;
					case 5: //ML
					case 6: //PL
						$emp[$row->employee_id]['mpl_actual'] += $row->credit;
						break;
				}
			}

			if(isset($emp)){
				$ctr = 0;
				foreach($emp as $emp_id => $bal){
					switch( true ){
						case (FLOAT)$bal['sl_actual'] != (FLOAT)$bal['sl_used'];
						case (FLOAT)$bal['vl_actual'] != (FLOAT)$bal['vl_used'];
						case (FLOAT)$bal['el_actual'] != (FLOAT)$bal['el_used'];
						case (FLOAT)$bal['bl_actual'] != (FLOAT)$bal['bl_used'];
						case (FLOAT)$bal['mpl_actual'] != (FLOAT)$bal['mpl_used'];
							$update = array(
								'sl_used' => $bal['sl_actual'],
								'vl_used' => $bal['vl_actual'],
								'el_used' => $bal['el_actual'],
								'bl_used' => $bal['bl_actual'],
								'mpl_used' => $bal['mpl_actual'],
							);
							//$this->db->update('employee_leave_balance', $update, array('employee_id' => $emp_id, 'year' => $year));
							//echo $this->db->last_query();
							// dbug($emp_id);
							// dbug($update);
							// dbug($bal);
							$ctr++;
							break;
						default:
					}
				}
				echo 'Total: '.$ctr;
			}
		}
	}

	function leave_credit_checker(){
		$qry = "SELECT b.employee_id, b.application_form_id, b.date_created, a.*
		FROM {$this->db->dbprefix}employee_leaves_dates a
		LEFT JOIN {$this->db->dbprefix}employee_leaves b ON b.employee_leave_id = a.employee_leave_id
		WHERE a.credit NOT IN (1.0, 0.5) AND b.form_status_id = 3 AND a.deleted = 0 AND b.deleted = 0";

		$anomalous = $this->db->query( $qry );
		if( $anomalous->num_rows() > 0 ){
			foreach( $anomalous->result() as $row ){
				$leave_balance = $this->db->get_where('employee_leave_balance', array('year' => 2012, 'employee_id' =>$row->employee_id))->row();

				switch($row->application_form_id){
					case 1: //sl
						$sl_used = $leave_balance->sl_used - .1;
						$corrected_sl_used = $sl_used + 1;
						$this->db->update('employee_leave_balance', array('sl_used' => $corrected_sl_used), array('leave_balance_id' => $leave_balance->leave_balance_id));
						echo $sl_used.' - '.$corrected_sl_used;
						break;
					case 2: //vl
						$vl_used = $leave_balance->vl_used - .1;
						$corrected_vl_used = $vl_used + 1;
						$this->db->update('employee_leave_balance', array('vl_used' => $corrected_vl_used), array('leave_balance_id' => $leave_balance->leave_balance_id));
						echo $vl_used.' - '.$corrected_vl_used;
						break;
				}
				$this->db->update('employee_leaves_dates', array('credit' => 1), array('employee_leave_date_id' => $row->employee_leave_date_id));
			}
		}
	}

	function _set_left_join()
	{
		//build left join for related tables
		foreach($this->related_table as $table => $column){
			if( strpos($table, " ") ){
				$letter = explode(" ", $table);
				if($this->module == "module" && $column == "module_id" && $this->module == "module")
					$this->db->join($table, $letter[1] .'.module_id='. $this->module_table .'.parent_id', 'left');
				else
					$this->db->join($table, $letter[1] .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
			}
			else{
				if( $this->method != 'listview') $this->db->join($table, $table .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
			}
		}
	}

	function check_if_hol(){
		$query = "SELECT *
				  FROM ".$this->db->dbprefix."holiday
				  WHERE date_set = '".date('Y-m-d',strtotime($this->input->post('date')))."' 
				  AND inactive = 0 
				  AND deleted = 0";

		$check_holiday = $this->db->query($query);

		$holiday = $this->system->holiday_check( $this->input->post('date'), $this->input->post('employee_id'), true);

		$response = false;
		
		if ($check_holiday && $check_holiday->num_rows() > 0 && !$holiday){
			$response = true;
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function quick_edit(){
		parent::quick_edit( $this->module_link );
	}

	function get_boxy_multiple_cancel() {
		if(IS_AJAX)
		{
			$response->msg = "";
			$employee_leave_id = $this->input->post('record_id');
			$hqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leaves WHERE employee_leave_id = '{$employee_leave_id}'")->row();			
			$dqry = $this->db->query("SELECT a.date, b.duration, a.employee_leave_date_id, a.date_cancelled, a.cancelled FROM {$this->db->dbprefix}employee_leaves_dates a 
										LEFT JOIN {$this->db->dbprefix}employee_leaves_duration b
										ON a.duration_id = b.duration_id
										WHERE a.employee_leave_id = '{$employee_leave_id}'")->result();
			$response->boxy_div = '<form class="style2 edit-view" name="multiple_cancel" id="multiple_cancel" method="post" enctype="multipart/form-data">
								    <div id="form-div">
								    <input type="hidden" name="employee_leave_id" id="employee_leave_id" value="'.$employee_leave_id.'">
								        <div class="col-1-form">      
								        	<div class="form-item">
								        		<label for="date_range" class="label-desc gray">Dates</label>			
								        		<div class="text-input-wrap">
									                <input id="date-temp-from" class="input-text datepicker disabled hasDatepicker" type="text" disabled="disabled" value="'.date("m/d/y",strtotime($hqry->date_from)).'" name="date-temp-from">
									                <img class="ui-datepicker-trigger" src="'.base_url().'themes/slategray/icons/calendar-month.png" alt="" title="">
									                &nbsp;&nbsp;<span class="to">to</span>&nbsp;&nbsp;
									                <input id="date-temp-to" class="input-text datepicker disabled hasDatepicker" type="text" disabled="disabled" value="'.date("m/d/y",strtotime($hqry->date_to)).'" name="date-temp-to">
									                <img class="ui-datepicker-trigger" src="'.base_url().'themes/slategray/icons/calendar-month.png" alt="" title="">
								                </div>
								            </div>
								            <div class="clear"></div>';
					$response->boxy_div .= '<div class="form-item" style="height:127px;overflow-y: auto;">
								        		<label for="inclusive_date" class="label-desc gray">Inclusive Dates :  <input id="check_all" name="check_all" type="checkbox"><strong>Select all dates</strong></label>';
								        		foreach ($dqry as $key => $value) {
								        			if($value->cancelled == 1) {
								        				$check = '<input id="chk_can2" d_id="" checked disabled type="checkbox" value="'.$value->employee_leave_date_id.'" name="chk_can[]">&nbsp;<span class="red">Cancelled</span>&nbsp<span class="blue"><i>Date : '.date("m/d/y",strtotime($value->date_cancelled)).'</i></span>';
								        			} else {
								        				$check = '<input id="chk_can" class="check_all" d_id="'.$value->employee_leave_date_id.'" type="checkbox" value="'.$value->employee_leave_date_id.'" name="chk_can[]">';
								        			}
								    $response->boxy_div .= '<div class="text-input-wrap">
									               	'.date("m/d/y",strtotime($value->date)).' - 
									               	<input type="text" name="duration_id[]" disabled value="'.$value->duration.'">&nbsp;
									               	'.$check.'
								                	</div>';
								        		}								        		
					$response->boxy_div .= '</div>
											<div class="clear"></div>
											<div class="form-item">
								        		<label for="reason" class="label-desc gray">Reason :</label>
												<div class="textarea-input-wrap">
													<textarea name="cancel_reason" id="cancel_reason" readonly>'.$hqry->reason.'</textarea>
												</div>
											</div>
											<div class="clear"></div>
											<div class="form-item">
								        		<label for="remarks" class="label-desc gray">Remarks : <span class="red">*</span></label>
												<div class="textarea-input-wrap">
													<textarea name="cancel_remarks" id="cancel_remarks"></textarea>
												</div>
											</div>
								        </div>
								    </div>
								    <div class="form-submit-btn">
								    	<div class="icon-label-group">
								    		<div class="icon-label">
								    			<a onclick="save_cancel_multiple()" href="javascript:void(0);" class="icon-16-add">
								    				<span>Save</span>
								   				 </a>            
								    		</div>
								    	</div>
								    	<div class="or-cancel">
								    		<span class="or">or</span>
								    		<a href="javascript:void(0)" class="cancel" onclick="Boxy.get(this).hide().unload();">Cancel</a>
								    	</div>
								    </div>
								    </form>';				
			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function save_cancel_multiple() {
		if(IS_AJAX) {
			$employee_leave_id = $this->input->post('employee_leave_id');
			$chk_can = $this->input->post('chk_can');
			$cancel_remarks = $this->input->post('cancel_remarks');
			$tag = 0;
			$to_check = true;
			$message = '';

			$hqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leaves WHERE employee_leave_id = '{$employee_leave_id}'")->row();

            if ($this->system->check_cutoff_policy_ls($hqry->employee_id,5,$hqry->application_form_id,$hqry->date_from,$hqry->date_to) == 1){
                    $to_check = false;                                   	
					$response['record_id'] = $this->input->post('record_id'); 
					$message = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					$msg_type = 'error';
			}
            elseif ($this->system->check_cutoff_policy_ls($hqry->employee_id,5,$hqry->application_form_id,$hqry->date_from,$hqry->date_to) == 2){
                    $to_check = false;                                   	
					$response['record_id'] = $this->input->post('record_id'); 
					$message = 'Sorry, your cancellation can no longer be processed. It exceeded the grace period';
					$msg_type = 'error';            	
            }

			if(!isset($_POST['chk_can'])) {
				$tag = 1;
				$message = 'No Inclusive Date Selected.';
				$msg_type = 'info';
			}

			if(!$tag && $to_check) {
				foreach($chk_can as $index => $employee_leave_date_id)
				{
					$this->db->update('employee_leaves_dates', array('cancelled'=>1,'deleted'=>1,'remarks'=>$cancel_remarks,'date_cancelled'=>date('Y-m-d H:i:s')), array('employee_leave_date_id' => $employee_leave_date_id));

					$days = $this->db->get_where('employee_leaves_dates', array('employee_leave_date_id' => $employee_leave_date_id));					
					if( $days->num_rows() > 0 ){
						foreach( $days->result() as $day ) {
						$dateto = $day->date;

							if($this->config->item('filing_with_carried') == 1)
								$day->date = date('Y-m-d');

							$year_date = date('Y', strtotime($day->date));

							$this->db->where('employee_leave_id', $employee_leave_id);
							$result = $this->db->get('employee_leaves');
							$request = $result->row_array();
							$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => $year_date, 'employee_id' => $request['employee_id'], 'deleted' => 0) );

							if( $emp_balance->num_rows() == 1 ){

								$emp_balance = $emp_balance->row();

								switch( $request['application_form_id'] ){
									case 1: //SL
										$emp_balance->sl_used = $emp_balance->sl_used - $day->credit;
										break;
									case 2: //VL
										$emp_balance->vl_used = $emp_balance->vl_used - $day->credit;
										break;	
									case 3: //EL
										$emp_balance->el_used = $emp_balance->el_used - $day->credit;
										break;
									case 4: //EL
										$emp_balance->bl_used = $emp_balance->bl_used - $day->credit;
										break;
									case 6: //PL
										$emp_balance->mpl_used = $emp_balance->mpl_used - $day->credit;
										break;
									// added for balfour
									case 13:
										break;
									case 17: // PLSP
										$emp_balance->plsp_used = $emp_balance->plsp_used - $day->credit;
										break;
									case 18: // WL
										$emp_balance->wl_used = $emp_balance->wl_used - $day->credit;
										break;
									case 19: // VML
										$emp_balance->vml_used = $emp_balance->vml_used - $day->credit;
										break;
									case 20: // SIL
										$emp_balance->sil_used = $emp_balance->sil_used - $day->credit;
										break;
									case 21: // UL
										$emp_balance->ul_used = $emp_balance->ul_used - $day->credit;
										break;
								}
								$this->db->update('employee_leave_balance', $emp_balance, array('leave_balance_id' => $emp_balance->leave_balance_id));
							}
						}
					}
				}
				$dqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leaves_dates WHERE employee_leave_id = '{$employee_leave_id}' AND cancelled = 0");
				if($dqry->num_rows() == 0) {
					$data['date_approved'] = date('Y-m-d H:i:s');
					$data['form_status_id'] = 5;
					$this->db->where('employee_leave_id', $employee_leave_id);
					$this->db->update('employee_leaves', $data);
				}
				$this->leaves->_send_status_email($employee_leave_id,5);
				$message = 'Cancelled Successfully.';
				$msg_type = 'success';
			}

			$response['tag'] = $tag;
			$response['msg_msg'] = $message;
			$response['msg_type'] = $msg_type;
			
			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function get_boxy_single_cancel() {
		if(IS_AJAX)
		{
			$response->msg = "";
			$employee_leave_id = $this->input->post('record_id');			
			$response->boxy_div = '<form class="style2 edit-view" name="single_cancel" id="single_cancel" method="post" enctype="multipart/form-data">
								    <div id="form-div">
								    <input type="hidden" name="employee_leave_id" id="employee_leave_id" value="'.$employee_leave_id.'">
								        <div class="col-1-form">      
								        	<div class="form-item">
								        		<label for="date_range" class="label-desc gray">Cancel Remarks : <span class="red">*</span></label>			
								        		<div class="textarea-input-wrap">
													<textarea name="cancel_remarks" id="cancel_remarks"></textarea>
												</div>
								            </div>
								        </div>
								    </div>
								    <div class="form-submit-btn">
								    	<div class="icon-label-group">
								    		<div class="icon-label">
								    			<a onclick="save_cancel_single()" href="javascript:void(0);" class="icon-16-add">
								    				<span>Save</span>
								   				 </a>            
								    		</div>
								    	</div>
								    	<div class="or-cancel">
								    		<span class="or">or</span>
								    		<a href="javascript:void(0)" class="cancel" onclick="Boxy.get(this).hide().unload();">Cancel</a>
								    	</div>
								    </div>
								    </form>';				
			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function save_cancel_single() {
		if(IS_AJAX) {
			$employee_leave_id = $this->input->post('employee_leave_id');
			$cancel_remarks = $this->input->post('cancel_remarks');
			$tag = 0;
			$to_check = true;
			$message = '';

			$hqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leaves WHERE employee_leave_id = '{$employee_leave_id}'")->row();

            if ($this->system->check_cutoff_policy_ls($hqry->employee_id,5,$hqry->application_form_id,$hqry->date_from,$hqry->date_to) == 1){
                    $to_check = false;                                   	
					$response['record_id'] = $this->input->post('record_id'); 
					$message = 'Next payroll cutoff not yet created in processing, please contact HRA.';
					$msg_type = 'error';
			}
            elseif ($this->system->check_cutoff_policy_ls($hqry->employee_id,5,$hqry->application_form_id,$hqry->date_from,$hqry->date_to) == 2){
                    $to_check = false;                                   	
					$response['record_id'] = $this->input->post('record_id'); 
					$message = 'Sorry, your cancellation can no longer be processed. It exceeded the grace period';
					$msg_type = 'error';            	
            }

			if(!$tag && $to_check) {
				$this->db->update('employee_leaves_dates', array('deleted'=>1,'cancelled'=>1,'remarks'=>$cancel_remarks,'date_cancelled'=>date('Y-m-d H:i:s')), array('employee_leave_id' => $employee_leave_id));

				$days = $this->db->get_where('employee_leaves_dates', array('employee_leave_id' => $employee_leave_id));					
				if( $days->num_rows() > 0 ){
					foreach( $days->result() as $day ) {
					$dateto = $day->date;

						if($this->config->item('filing_with_carried') == 1)
							$day->date = date('Y-m-d');

						$year_date = date('Y', strtotime($day->date));

						$this->db->where('employee_leave_id', $employee_leave_id);
						$result = $this->db->get('employee_leaves');
						$request = $result->row_array();
						$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => $year_date, 'employee_id' => $request['employee_id'], 'deleted' => 0) );

						if( $emp_balance->num_rows() == 1 ){

							$emp_balance = $emp_balance->row();

							switch( $request['application_form_id'] ){
								case 1: //SL
									$emp_balance->sl_used = $emp_balance->sl_used - $day->credit;
									break;
								case 2: //VL
									$emp_balance->vl_used = $emp_balance->vl_used - $day->credit;
									break;	
								case 3: //EL
									$emp_balance->el_used = $emp_balance->el_used - $day->credit;
									break;
								case 4: //EL
									$emp_balance->bl_used = $emp_balance->bl_used - $day->credit;
									break;
								case 6: //PL
									$emp_balance->mpl_used = $emp_balance->mpl_used - $day->credit;
									break;
								// added for balfour
								case 13:
									break;
								case 17: // PLSP
									$emp_balance->plsp_used = $emp_balance->plsp_used - $day->credit;
									break;
								case 18: // WL
									$emp_balance->wl_used = $emp_balance->wl_used - $day->credit;
									break;
								case 19: // VML
									$emp_balance->vml_used = $emp_balance->vml_used - $day->credit;
									break;
								case 20: // SIL
									$emp_balance->sil_used = $emp_balance->sil_used - $day->credit;
									break;
								case 21: // UL
									$emp_balance->ul_used = $emp_balance->ul_used - $day->credit;
									break;
							}
							$this->db->update('employee_leave_balance', $emp_balance, array('leave_balance_id' => $emp_balance->leave_balance_id));
						}
					}
				}
				$dqry = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_leaves_dates WHERE employee_leave_id = '{$employee_leave_id}' AND cancelled = 0");
				if($dqry->num_rows() == 0) {
					$data['date_approved'] = date('Y-m-d H:i:s');
					$data['form_status_id'] = 5;
					$this->db->where('employee_leave_id', $employee_leave_id);
					$this->db->update('employee_leaves', $data);
				}
				$this->leaves->_send_status_email($employee_leave_id,5);
				$message = 'Cancelled Successfully.';
				$msg_type = 'success';
			}

			$response['tag'] = $tag;
			$response['msg_msg'] = $message;
			$response['msg_type'] = $msg_type;

			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
}

/* End of file */
/* Location: system/application */
