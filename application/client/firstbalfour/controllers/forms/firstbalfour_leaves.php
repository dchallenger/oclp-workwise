<?php

include (APPPATH . 'controllers/forms/leaves.php');

class Firstbalfour_leaves extends Leaves
{
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
			b.deleted = 0 -- AND a.status = 6";
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
        $leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '!= 1', 'in (0,1)', false, $this->user_access[$this->module_id]['project_hr'] );
        if( $leaves_to_approve ){
        	$leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '= 2', '= 1', false, $this->user_access[$this->module_id]['project_hr'] );
        	
        	if( ( !$this->is_superadmin && !$this->is_admin ) &&  $this->user_access[$this->module_id]['post'] != 1){
        		$this->db->join('leave_approver','leave_approver.leave_id = '.$this->module_table.'.employee_leave_id');
        		$this->db->where('leave_approver.focus',1);
        		$this->db->where('leave_approver.status',2);
        		$this->db->where('leave_approver.approver',$this->user->user_id);
        	}

        	$this->db->where('form_status_id', 2);
        	$this->db->where_in('employee_leave_id', $leaves_to_approve);
        	$for_approval = $this->db->get( $this->module_table );
        	$approval_counter = "";
        	$counter = array();
        	if ($for_approval && $for_approval->num_rows() > 0) {

        		foreach ($for_approval->result() as $app) {
        			$counter[] = $app->employee_leave_id;
/*	        		$check = $this->system->check_in_cutoff($app->date_from);
	        		if ($check != 2) {
	        			$counter[] = $app->employee_leave_id;
        			}*/
        		}
        	}
        	        	
        	if(  $for_approval->num_rows() > 0 ) $approval_counter = '<span class="bg-orange ctr-inline" id="approval-counter">' . count($counter) . '</span>';
        	$tabs[] = '<li filter="for_approval"><a href="javascript:void(0)">For Approval '. $approval_counter .'</li>';
        	$tabs[] = '<li filter="approved"><a href="javascript:void(0)">Approved</li>';
        	$tabs[] = '<li filter="disapproved"><a href="javascript:void(0)">Disapproved</li>';
        	$tabs[] = '<li filter="cancelled"><a href="javascript:void(0)">Cancelled</li>';	
        	$tabs[] = '<li filter="invalid"><a href="javascript:void(0)">Invalid</li>';	
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
}


?>