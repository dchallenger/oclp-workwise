<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_update extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists all employee update requests.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an employee update request.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an employee update request.';

		$position_approver = false;
		$employee_approver = false;
		$subs = array();
		$emp_subs = array();

		// if(!( $this->is_superadmin || $this->is_admin) && !$this->input->post('filter')){
		if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1){			
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";
        }

        if( $this->input->post('filter') && $this->input->post('filter') == "personal" ){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";    
        }

        if( $this->input->post('filter') && $this->input->post('filter') == "all"){
        	$this->filter = $this->module_table.".employee_id <> {$this->user->user_id}";    
        }

        $subs = array();

        $emp_approver = $this->_is_employee_approver($this->userinfo['user_id'], $this->module_id);
		if($emp_approver && count($emp_approver) > 0) {
			foreach($emp_approver as $row) {
				if ($row['employee_id'] != ''){
					$subs[] = $row['employee_id'];
				}
			}
		}

        $approver  = $this->system->is_module_approver( $this->module_id, $this->userinfo['position_id'] );
		if($approver && count($approver) > 0){
			foreach( $approver as $row ){
				$this->db->where('position_id',$row->position_id);
				$emp_id=$this->db->get('user')->result_array();
				foreach($emp_id as $id)
				{
					$have_emp_approver = $this->_have_employee_approver($id['employee_id'], $this->module_id);
					if(!$have_emp_approver){
						if ($id['employee_id'] != ''){
							$subs[] = $id['employee_id'];
						}
					}
				}
			}
		}

		if( $this->input->post('filter') && $this->input->post('filter') == "for_approval" ){
			if(( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1){
				$this->filter = $this->db->dbprefix.$this->module_table.".employee_update_status_id = 1";				
			}
			elseif( !( $this->is_superadmin || $this->is_admin ) && !empty($subs) ){
				$this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .") AND ".$this->db->dbprefix.$this->module_table.".employee_update_status_id = 1";				
			}
		}

		if( $this->input->post('filter') && $this->input->post('filter') == "approved" ){
			if(( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1){
				$this->filter = $this->db->dbprefix.$this->module_table.".employee_update_status_id = 2";			
			}
			elseif( !( $this->is_superadmin || $this->is_admin ) && !empty($subs) ){
				$this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .") AND ".$this->db->dbprefix.$this->module_table.".employee_update_status_id = 2";
			}
    	}

        if (CLIENT_DIR == 'firstbalfour'){

        	if($this->input->post('filter') && $this->input->post('filter') == "subordinates" ){

        		$subs = array();

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

        	}


	        if (!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1){

	        	$subs = array();

	        	$subordinates = $this->system->get_subordinates_by_project($this->userinfo['user_id']);
	        	if(count($subordinates) > 0 && $subordinates != false){
					foreach($subordinates as $id)
					{
						if ($id['employee_id'] != ''){
							$subs[] = $id['employee_id'];
						}
					}
					if( $this->input->post('filter') && $this->input->post('filter') == "for_approval" ){
							$this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .") AND ".$this->db->dbprefix.$this->module_table.".employee_update_status_id = 1";				
					}
					if( $this->input->post('filter') && $this->input->post('filter') == "approved" ){
							$this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .") AND ".$this->db->dbprefix.$this->module_table.".employee_update_status_id = 2";				
					}
					// $this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .")";
				}
	        }
        }     	
	}
	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
		$data['content'] = $this->module_link.'/listview';
		$data['jqgrid'] = $this->module_link.'/jqgrid';

		$approver = $this->system->is_module_approver( $this->module_id, $this->userinfo['position_id'] );
		$emp_approver = $this->_is_employee_approver($this->userinfo['user_id'], $this->module_id);
		$pos_subordinates = $this->db->get_where('user_position', array("reporting_to" => $this->userinfo['position_id']));
		

    	if (CLIENT_DIR == 'firstbalfour'){
	    	if( $pos_subordinates->num_rows() > 0 || $approver || $emp_approver || ( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1){
	    		
	    		$tab = '';

	    		if( $this->is_superadmin || $this->is_admin ||  $this->user_access[$this->module_id]['post'] == 1 ){
	    			$tab .= '<li class="active" filter="all"><a href="javascript:void(0)">All</li><li filter="personal"><a href="javascript:void(0)">Personal</li>';
	    		}
	    		else{
	    			$tab .= '<li filter="personal" class="active"><a href="javascript:void(0)">Personal</li>';
	    		}

	    		if( $pos_subordinates->num_rows() > 0 ){
	    			$tab .= '<li filter="subordinates"><a href="javascript:void(0)">Subordinates</li>';
	    		}

	    		if( $approver || $emp_approver || $this->is_superadmin || $this->is_admin ){
	    			$tab .= '<li id="approve_tab" filter="for_approval"><a href="javascript:void(0)">For Approval</li><li id="approve_tab" filter="approved"><a href="javascript:void(0)">Approved</li>';
	    		}

	    		$data['tab'] = addslashes('<ul id="grid-filter">'.$tab.'</ul>');
	    		
	    	}
    	}
    	else{
    		if($approver || $emp_approver){
				if(( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1)
					$data['tab'] = addslashes('<ul id="grid-filter"><li class="active" filter="all"><a href="javascript:void(0)">All</li><li id="approve_tab" filter="for_approval"><a href="javascript:void(0)">For Approval</li></ul>');
				else
		        	$data['tab'] = addslashes('<ul id="grid-filter"><li class="active" filter="personal"><a href="javascript:void(0)">Personal</li><li id="approve_tab" filter="for_approval"><a href="javascript:void(0)">For Approval</li><li id="approve_tab" filter="approved"><a href="javascript:void(0)">Approved</li></ul>');
	    	}
    	}
    	

		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = 'init_filter_tabs();';

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


		$this->db->where('employee_update_id',$this->input->post('record_id'));
		$result = $this->db->get('employee_update_attachment');

		$data['attachment'] = $result->result_array();

			
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
			$data['scripts'][] = uploadify_script();
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			$data['scripts'][] ='<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			if ($this->config->item('client_number') == 3){
				$this->db->where('employee_update_id',$this->input->post('record_id'));
				$result = $this->db->get('employee_update_attachment');

				$data['attachment'] = $result->result_array();
			}
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

	function ajax_save($bypass = false)
	{
		if ( !$this->user_access[$this->module_id]['post'] && $this->input->post('employee_id') != $this->userinfo['user_id']) {
			show_error('The action you have requested is not allowed.');		
		}

		if ($bypass) {parent::ajax_save(); return;}
		
		parent::ajax_save();
		//additional module save routine here

		//attachment
		$data = $this->_rebuild_array($this->input->post('attachment'), $this->input->post('employee_id'),'employee_update_id',$this->input->post('record_id'));
		if (count($data) > 0){
			$this->db->insert_batch('employee_update_attachment', $data);	
		}
		//end of attachment


		$this->db->where('employee_update_id', $this->key_field_val);
		$this->db->where('deleted', 0);
		$check_if_changes = $this->db->get('employee_update');
		$check_if_changes_val = $check_if_changes->result_array();

		if ($check_if_changes->num_rows() != 0){
			$to_be_inserted=$this->db->dbprefix."employee";
			$to_be_inserted_2=$this->db->dbprefix."user";
			$insert_here=$this->db->dbprefix."employee_bu";

			$empid=$check_if_changes_val[0]['employee_id'];
			$update_id=$check_if_changes_val[0]['employee_update_id'];
			
			$this->db->where('employee_update_id', $update_id);
			$check_bu = $this->db->get('employee_bu');
			if($check_bu->num_rows() > 0)
			{
				$query = "UPDATE {dbprefix}employee_bu, {dbprefix}user
				SET
				{dbprefix}employee_bu.firstname = {dbprefix}user.firstname, 
				{dbprefix}employee_bu.middlename = {dbprefix}user.middlename,
				{dbprefix}employee_bu.lastname = {dbprefix}user.lastname,
				{dbprefix}employee_bu.date_of_marriage = {dbprefix}user.date_of_marriage
				WHERE 
				{dbprefix}employee_bu.employee_update_id = ".$update_id." AND {dbprefix}employee_bu.employee_id = {dbprefix}user.employee_id";
				$query = str_replace('{dbprefix}', $this->db->dbprefix, $query);
				$this->db->query($query);

				$query = "UPDATE {dbprefix}employee_bu, {dbprefix}employee
				SET
				{dbprefix}employee_bu.civil_status_id = {dbprefix}employee.civil_status_id, 
				{dbprefix}employee_bu.mobile = {dbprefix}employee.mobile,
				{dbprefix}employee_bu.home_phone = {dbprefix}employee.home_phone,
				{dbprefix}employee_bu.emergency_name = {dbprefix}employee.emergency_name,
				{dbprefix}employee_bu.emergency_phone= {dbprefix}employee.emergency_phone,
				{dbprefix}employee_bu.emergency_relationship = {dbprefix}employee.emergency_relationship,
				{dbprefix}employee_bu.emergency_address = {dbprefix}employee.emergency_address,
				{dbprefix}employee_bu.pres_address1 = {dbprefix}employee.pres_address1,
				{dbprefix}employee_bu.pres_address2 = {dbprefix}employee.pres_address2,
				{dbprefix}employee_bu.pres_city = {dbprefix}employee.pres_city,
				{dbprefix}employee_bu.pres_province = {dbprefix}employee.pres_province,
				{dbprefix}employee_bu.pres_zipcode = {dbprefix}employee.pres_zipcode,
				{dbprefix}employee_bu.perm_address1 = {dbprefix}employee.perm_address1,
				{dbprefix}employee_bu.perm_address2 = {dbprefix}employee.perm_address2,
				{dbprefix}employee_bu.perm_city = {dbprefix}employee.perm_city,
				{dbprefix}employee_bu.perm_province = {dbprefix}employee.perm_province,
				{dbprefix}employee_bu.perm_zipcode = {dbprefix}employee.perm_zipcode
				WHERE 
				{dbprefix}employee_bu.employee_update_id = '". $update_id ."' AND {dbprefix}employee_bu.employee_id = {dbprefix}employee.employee_id";
				$query = str_replace('{dbprefix}', $this->db->dbprefix, $query);
				$this->db->query($query);
			}
			else
				$this->db->query("INSERT INTO $insert_here(employee_id, employee_update_id, firstname , middlename , lastname , date_of_marriage , civil_status_id , mobile , home_phone , emergency_name , emergency_phone , emergency_relationship , emergency_address , pres_address1 , pres_address2 , pres_city , pres_province , pres_zipcode , perm_address1 , perm_address2 , perm_city , perm_province , perm_zipcode) SELECT $empid , $update_id , $to_be_inserted_2.firstname , $to_be_inserted_2.middlename , $to_be_inserted_2.lastname , $to_be_inserted_2.date_of_marriage , $to_be_inserted.civil_status_id , $to_be_inserted.mobile , $to_be_inserted.home_phone , $to_be_inserted.emergency_name , $to_be_inserted.emergency_phone , $to_be_inserted.emergency_relationship , $to_be_inserted.emergency_address , $to_be_inserted.pres_address1 , $to_be_inserted.pres_address2 , $to_be_inserted.pres_city , $to_be_inserted.pres_province , $to_be_inserted.pres_zipcode , $to_be_inserted.perm_address1 , $to_be_inserted.perm_address2 , $to_be_inserted.perm_city , $to_be_inserted.perm_province , $to_be_inserted.perm_zipcode FROM $to_be_inserted LEFT JOIN $to_be_inserted_2 ON $to_be_inserted.employee_id = $to_be_inserted_2.employee_id WHERE $to_be_inserted.employee_id=$empid");

			// $this->db->set('firstname', $this->input->post('first_name'));
			// $this->db->set('middlename', $this->input->post('middle_name'));
			// $this->db->set('lastname', $this->input->post('last_name'));
			// $this->db->set('date_of_marriage', $this->input->post('date_of_marriage'));
			// $this->db->where('employee_id', $empid);
			// $this->db->update('user');

			// employee
			// $this->db->set('mobile', $this->input->post('mobile'));
			// $this->db->set('home_phone', $this->input->post('home_phone'));
			// $this->db->set('emergency_name', $this->input->post('emergency_name'));
			// $this->db->set('emergency_phone', $this->input->post('emergency_phone'));
			// $this->db->set('emergency_relationship', $this->input->post('emergency_relationship'));
			// $this->db->set('emergency_address', $this->input->post('emergency_address'));
			// $this->db->set('pres_address1', $this->input->post('pres_address1'));
			// $this->db->set('pres_address2', $this->input->post('pres_address2'));
			// $this->db->set('pres_city', $this->input->post('pres_city'));
			// $this->db->set('pres_province', $this->input->post('pres_province'));
			// $this->db->set('pres_zipcode', $this->input->post('pres_zipcode'));
			// $this->db->set('perm_address1', $this->input->post('perm_address1'));
			// $this->db->set('perm_address2', $this->input->post('perm_address2'));
			// $this->db->set('perm_city', $this->input->post('perm_city'));
			// $this->db->set('perm_province', $this->input->post('perm_province'));
			// $this->db->set('perm_zipcode', $this->input->post('perm_zipcode'));
			// $this->db->where('employee_id', $empid);
			// $this->db->update('employee');
			//employee

			// automatically converts status to approved ::: this will be change to for approval if family is changed
			// $this->db->set('employee_update_status_id', '2');
			// $this->db->where('employee_update_id',$this->input->post('record_id'));
			// $this->db->update('employee_update');
			// automatically converts status to approved ::: this will be change to for approval if family is changed
		}


		// if(count($this->input->post('family')) < 1){
		// 	$this->db->set('employee_update_status_id', '2');
		// 	$this->db->where('employee_update_id',$this->input->post('record_id'));
		// 	$this->db->update('employee_update');
		// }
		

		$ctr=1;
		$flag=false;
		$empty=false;
		//if($this->input->post('record_id') == -1)
		if( $this->input->post('record_id') == -1 )
		{
			$query = $this->db->get_where("employee_update", array('employee_update_id' => $this->key_field_val));
			$row = $query->row_array(); 
			$flag=true;
		}

		// Changes for Personal. This is retained so that it automatically show changed edited
		if($flag){
			$this->db->set('employee_id', $this->input->post('employee_id'));
			$this->db->set('employee_update_id', $this->key_field_val);
			$this->db->set('personal_fName', $this->input->post('first_name'));
			$this->db->set('personal_mName', $this->input->post('middle_name'));
			$this->db->set('personal_lName', $this->input->post('last_name'));
			$this->db->set('personal_dom', date( 'Y-m-d', strtotime($this->input->post('date_of_marriage'))));
			$this->db->insert('employee_update_personal');
		}
		else{
			$this->db->set('personal_fName', $this->input->post('first_name'));
			$this->db->set('personal_mName', $this->input->post('middle_name'));
			$this->db->set('personal_lName', $this->input->post('last_name'));
			$this->db->set('personal_dom', date( 'Y-m-d', strtotime($this->input->post('date_of_marriage'))));
			$this->db->where('employee_update_id', $this->key_field_val);
			$this->db->update('employee_update_personal');
		}
		// Changes for Personal. This is retained so that it automatically show changed edited


		// This is where Family is saved

 		foreach($this->input->post('family') as $valme)
		{
				if($ctr==1 && $valme!="") $this->db->set('name', $valme);
				elseif($ctr==1 && $valme=="")$empty=true;
				if($ctr==2)	$this->db->set('relationship', $valme);
				if($ctr==3) $this->db->set('birthdate', date( 'Y-m-d', strtotime( $valme )));
				if($ctr==4)	$this->db->set('occupation', $valme);
				if($ctr==5)	$this->db->set('employer', $valme);

				//changes for add entry
				if($ctr==6)	$this->db->set('educational_attainment', $valme);
				if($ctr==7)	$this->db->set('degree', $valme);
				if($ctr==8 && $empty==false) $this->db->set('ecf_dependent', $valme);
				if($ctr==9 && $empty==false) $this->db->set('bir_dependent', $valme);
				if($ctr==10 && $empty==false) $this->db->set('hospitalization_dependent', $valme);
				//changes for add entry

				if($ctr==11) $this->db->set('already_exist', $valme);
				if($ctr==12) $this->db->set('flagcount', $valme);

			$ctr++;
			if($ctr % 13 == 0)
			{
				
				if($flag)
				{
					if($empty==false){
						$this->db->set('employee_update_id', $row['employee_update_id']);
						$this->db->set('employee_id', $this->input->post('employee_id'));
						$this->db->insert('employee_update_family');
						$this->db->set('employee_update_status_id', '1');
						$this->db->where('employee_update_id',$this->input->post('record_id'));
						$this->db->update('employee_update');
					} else 
						$empty=false;
				}
				else
				{
					if($empty==false){
						//$this->db->set('employee_update_id', $this->input->post('record_id') );
						$this->db->where('employee_update_id', $this->input->post('record_id'));
						$this->db->where('flagcount',$valme);
						$this->db->update('employee_update_family');
						$this->db->set('employee_update_status_id', '1');
						$this->db->where('employee_update_id',$this->input->post('record_id'));
						$this->db->update('employee_update');
					} else 
						$empty = false;
				}
				$ctr=1;
			}
		}
		// This is where family is saved

		//delete user info file
		// $path = FCPATH.'settings\user\\' . $this->input->post('employee_id').".php";
		// unlink($path);
		
	}

    protected function _approve_request($request_id = 0)
    {


        //changes for bu
        // $this->db->where('employee_update_id', $request_id);
        // $this->db->where('deleted', 0);
        // $check_if_changes = $this->db->get('employee_update');
        // $check_if_changes_val = $check_if_changes->result_array();

        // if ($check_if_changes->num_rows() != 0){
        //  $to_be_inserted=$this->db->dbprefix."employee";
        //  $to_be_inserted_2=$this->db->dbprefix."user";
        //  $insert_here=$this->db->dbprefix."employee_bu";

        //  $empid=$check_if_changes_val[0]['employee_id'];
        //  $update_id=$check_if_changes_val[0]['employee_update_id'];
        //  $this->db->query("INSERT INTO $insert_here(employee_id, employee_update_id, firstname , middlename , lastname , date_of_marriage , civil_status_id , mobile , home_phone , emergency_name , emergency_phone , emergency_relationship , emergency_address , pres_address1 , pres_address2 , pres_city , pres_province , pres_zipcode , perm_address1 , perm_address2 , perm_city , perm_province , perm_zipcode) SELECT $empid , $update_id , $to_be_inserted_2.firstname , $to_be_inserted_2.middlename , $to_be_inserted_2.lastname , $to_be_inserted_2.date_of_marriage , $to_be_inserted.civil_status_id , $to_be_inserted.mobile , $to_be_inserted.home_phone , $to_be_inserted.emergency_name , $to_be_inserted.emergency_phone , $to_be_inserted.emergency_relationship , $to_be_inserted.emergency_address , $to_be_inserted.pres_address1 , $to_be_inserted.pres_address2 , $to_be_inserted.pres_city , $to_be_inserted.pres_province , $to_be_inserted.pres_zipcode , $to_be_inserted.perm_address1 , $to_be_inserted.perm_address2 , $to_be_inserted.perm_city , $to_be_inserted.perm_province , $to_be_inserted.perm_zipcode FROM $to_be_inserted LEFT JOIN $to_be_inserted_2 ON $to_be_inserted.employee_id = $to_be_inserted_2.employee_id WHERE $to_be_inserted.employee_id=$empid");
        // }
        //changes for bu

        // for whatever purpose it may serve family_bu
        $this->db->where('employee_update_id', $request_id);
        $this->db->where('deleted', 0);
        $check_if_changes = $this->db->get('employee_update');
        $check_if_changes_val = $check_if_changes->result_array();

        if ($check_if_changes->num_rows() != 0){
            $to_be_inserted=$this->db->dbprefix."employee";
            $to_be_inserted_2=$this->db->dbprefix."user";
            $insert_here=$this->db->dbprefix."employee_bu";

            $empid=$check_if_changes_val[0]['employee_id'];
            $update_id=$check_if_changes_val[0]['employee_update_id'];
        }
        // for whatever purpose it may serve family_bu

        $this->db->where($this->module_table.'.'.$this->key_field, $request_id);
        $this->db->where($this->module_table.'.deleted', 0);
        $this->db->join('employee_update_personal','employee_update_personal.employee_update_id = '.$this->module_table.'.employee_update_id');
        $request = $this->db->get($this->module_table);

        if (!$request && $request->num_rows() == 0) {
            return false;
        } else {
            foreach ($request->row() as $field => $value) {
                if ($field != 'date_created' && trim($value) != '') {
                    $data[$field] = $value;
                }
            }

            if (count($data) > 0) {
                $update_request_fields = array_keys($data);
                $employee_fields       = $this->db->list_fields('employee');

                $available_fields = array_intersect($update_request_fields, $employee_fields);
                $update_fields    = array();
                foreach ($available_fields as $field) {
                    $update_fields[$field] = $data[$field];
                }

                //$this->db->where('employee_id', $request->row()->employee_id);
                //$this->db->update('employee', $update_fields);

                $user_fields       = $this->db->list_fields('user');

                $available_fields = array_intersect($update_request_fields, $user_fields);

                $update_fields    = array();
                foreach ($available_fields as $field) {
                    $update_fields[$field] = $data[$field];
                }

                if ($data['personal_dom'] != '' && $data['personal_dom'] != 'NULL' && $data['personal_dom'] != "1970-01-01"){
                    $update_fields['date_of_marriage'] = $data['personal_dom'];
                }

                if ($data['personal_mName'] != '' && $data['personal_mName'] != 'NULL'){
                    $update_fields['middlename'] = $data['personal_mName'];
                    $update_fields['middleinitial'] = strtoupper(substr($data['personal_mName'], -1)).'.';
                }

                if ($data['personal_lName'] != '' && $data['personal_lName'] != 'NULL'){
                    $update_fields['lastname'] = $data['personal_lName'];
                }

                if ($data['personal_fName'] != '' && $data['personal_fName'] != 'NULL'){
                    $update_fields['firstname'] = $data['personal_fName'];
                }

                $this->db->where('employee_id', $request->row()->employee_id);
                $this->db->update('user', $update_fields);      


                if ($data['mobile'] != '' && $data['mobile'] != 'NULL'){
                    $update_employee_fields['mobile'] = $data['mobile'];
                }

                if ($data['home_phone'] != '' && $data['home_phone'] != 'NULL'){
                    $update_employee_fields['home_phone'] = $data['home_phone'];
                }

                if ($data['emergency_name'] != '' && $data['emergency_name'] != 'NULL'){
                    $update_employee_fields['emergency_name'] = $data['emergency_name'];
                }

                if ($data['emergency_phone'] != '' && $data['emergency_phone'] != 'NULL'){
                    $update_employee_fields['emergency_phone'] = $data['emergency_phone'];
                }

                if ($data['emergency_relationship'] != '' && $data['emergency_relationship'] != 'NULL'){
                    $update_employee_fields['emergency_relationship'] = $data['emergency_relationship'];
                }

                if ($data['emergency_address'] != '' && $data['emergency_address'] != 'NULL'){
                    $update_employee_fields['emergency_address'] = $data['emergency_address'];
                }

                if ($data['pres_address1'] != '' && $data['pres_address1'] != 'NULL'){
                    $update_employee_fields['pres_address1'] = $data['pres_address1'];
                }

                if ($data['pres_address2'] != '' && $data['pres_address2'] != 'NULL'){
                    $update_employee_fields['pres_address2'] = $data['pres_address2'];
                }

                if ($data['pres_city'] != '' && $data['pres_city'] != 'NULL'){
                    $update_employee_fields['pres_city'] = $data['pres_city'];
                }

                if ($data['pres_province'] != '' && $data['pres_province'] != 'NULL'){
                    $update_employee_fields['pres_province'] = $data['pres_province'];
                }

                if ($data['pres_zipcode'] != '' && $data['pres_zipcode'] != 'NULL'){
                    $update_employee_fields['pres_zipcode'] = $data['pres_zipcode'];
                }

                if ($data['perm_address1'] != '' && $data['perm_address1'] != 'NULL'){
                    $update_employee_fields['perm_address1'] = $data['perm_address1'];
                }

                if ($data['perm_address2'] != '' && $data['perm_address2'] != 'NULL'){
                    $update_employee_fields['perm_address2'] = $data['perm_address2'];
                }

                if ($data['perm_city'] != '' && $data['perm_city'] != 'NULL'){
                    $update_employee_fields['perm_city'] = $data['perm_city'];
                }

                if ($data['perm_province'] != '' && $data['perm_province'] != 'NULL'){
                    $update_employee_fields['perm_province'] = $data['perm_province'];
                }

                if ($data['perm_zipcode'] != '' && $data['perm_zipcode'] != 'NULL'){
                    $update_employee_fields['perm_zipcode'] = $data['perm_zipcode'];
                }


                $this->db->where('employee_id', $request->row()->employee_id);
                $this->db->update('employee', $update_employee_fields);  

            }

            
            //updates attachment
            $this->db->where('employee_update_id', $request_id);
            $this->db->where('deleted', 0);
            $update_201_attachment_result = $this->db->get('employee_update_attachment');           
            if ($update_201_attachment_result && $update_201_attachment_result->num_rows() > 0){
                foreach ($update_201_attachment_result->result_array() as $row_array) {
                    unset($row_array['attachment_id']);
                    unset($row_array['employee_update_id']);
                    unset($row_array['date_created']);
                    unset($row_array['deleted']);

                    $this->db->insert('employee_attachment',$row_array);
                }
            }
            //end updates attachment

            //updates
            $this->db->where('employee_update_id', $request_id);
            $this->db->where('deleted', 0);
            $updated_family = $this->db->get('employee_update_family');

            if (!$updated_family && $updated_family->num_rows() == 0) {
            } else {
                $updated_family_val = $updated_family->result_array();

                // $table=$this->db->dbprefix."employee_update_family";
                // $already_exist_value=$this->db->query("SELECT * FROM $table WHERE deleted=0 AND already_exist>0 AND employee_update_id=$request_id ORDER BY already_exist");
                $this->db->where('deleted', 0);
                $this->db->where('already_exist >', 0);
                $this->db->where('employee_update_id', $request_id);
                $this->db->order_by('already_exist');
                $already_exist_value = $this->db->get('employee_update_family');
                
                if($already_exist_value->num_rows() ==0)
                { } else {
                    $empid=$updated_family_val[0]['employee_id'];
                    $table1=$this->db->dbprefix."employee_family";
                    $old_fam=$this->db->query("SELECT * FROM $table1 WHERE deleted=0 AND employee_id=$empid ORDER BY name, birth_date");

                    //changes for bu
                    //$to_be_inserted_2=$this->db->dbprefix."user";
                    $insert_here=$this->db->dbprefix."employee_family_bu";
                    $this->db->query("INSERT INTO $insert_here(employee_id, employee_update_id , name , relationship , birth_date , occupation , employer , educational_attainment, degree, ecf_dependent , bir_dependent, hospitalization_dependent) SELECT $empid , $request_id , $table1.name , $table1.relationship , $table1.birth_date , $table1.occupation , $table1.employer, $table1.educational_attainment, $table1.degree, $table1.ecf_dependent, $table1.bir_dependents, $table1.hospitalization_dependents FROM $table1 WHERE $table1.employee_id=$empid");
                    //changes for bu

                    for($x=0;$x<$old_fam->num_rows();$x++)
                    {
                        $val=$old_fam->row_array($x);
                        $whattobesave=$already_exist_value->row_array($x);
                        $this->db->set('employee_id',$whattobesave['employee_id']);
                        $this->db->set('name',$whattobesave['name']);
                        $this->db->set('relationship',$whattobesave['relationship']);
                        $this->db->set('birth_date',$whattobesave['birthdate']);
                        $this->db->set('occupation',$whattobesave['occupation']);
                        $this->db->set('employer',$whattobesave['employer']);

                        $this->db->set('educational_attainment',$whattobesave['educational_attainment']);
                        $this->db->set('degree',$whattobesave['degree']);
                        $this->db->set('ecf_dependent',$whattobesave['ecf_dependent']);
                        $this->db->set('bir_dependents',$whattobesave['bir_dependent']);
                        $this->db->set('hospitalization_dependents',$whattobesave['hospitalization_dependent']);

                        $this->db->where('name',$val['name']);
                        $this->db->where('birth_date',$val['birth_date']);
                        $this->db->update('employee_family');
                    }
                }
                // $table2=$this->db->dbprefix."employee_update_family";
                // $already_exist_value=$this->db->query("SELECT * FROM $table2 WHERE deleted=0 AND already_exist=0 AND employee_update_id=$this->key_field_val ");
                $this->db->where('deleted', 0);
                $this->db->where('already_exist', 0);
                $this->db->where('employee_update_id', $request_id);
                // $this->db->order_by('already_exist');
                $already_exist_value = $this->db->get('employee_update_family');
                if($already_exist_value->num_rows() != 0)
                { 
                    $this->db->where('employee_update_id', $request_id);
                    $this->db->where('deleted', 0);
                    $this->db->where('already_exist', 0);
                    $updated_family = $this->db->get('employee_update_family');
                    foreach($updated_family->result_array() as $updated_val){
                         //$this->db->set('record_id', $request_id );
                         $this->db->set('employee_id', $updated_val['employee_id']);
                         $this->db->set('name', $updated_val['name']);
                         $this->db->set('relationship', $updated_val['relationship']);
                         $this->db->set('birth_date', $updated_val['birthdate']);
                         $this->db->set('occupation', $updated_val['occupation']);
                         $this->db->set('employer', $updated_val['employer']);

                         $this->db->set('educational_attainment',$updated_val['educational_attainment']);
                         $this->db->set('degree',$updated_val['degree']);
                         $this->db->set('ecf_dependent',$updated_val['ecf_dependent']);
                         $this->db->set('bir_dependents',$updated_val['bir_dependent']);
                         $this->db->set('hospitalization_dependents',$updated_val['hospitalization_dependent']);

                         $this->db->insert('employee_family');
                    }
                }
            }

            //updates

            //CHANGES FOR PERSONAL || This will only change date_of_marriage
            $this->db->where('employee_update_id', $request_id);
            $this->db->where('deleted', 0);
            $updated_personal = $this->db->get('employee_update');
            if($updated_personal->num_rows()!==0)
            {
                $updated_personal_val = $updated_personal->result_array();
                $empid=$updated_personal_val[0]['employee_id'];


                $this->db->set('civil_status_id',$updated_personal_val[0]['civil_status_id']);
                $this->db->where('employee_id', $empid);
                $this->db->where('deleted', 0);
                $this->db->update('employee');
            }


            //CHANGES FOR PERSONAL || This will only change date_of_marriage

            //changes for dateapproved
            $date_approved=date('Y-m-d H:i:s');
            $this->db->set('date_approved', $date_approved);
            $this->db->where('employee_update_id', $request_id);
            $this->db->update('Employee_update');
            //changes for dateapproved

            //to reset user access, delete user access file
            $app_directories =  $this->hdicore->_get_config('app_directories');
            if( file_exists( $app_directories['user_settings_dir'] . $request->row()->employee_id.'.php' ) ) unlink( $app_directories['user_settings_dir'] . $request->row()->employee_id.'.php');
            


            return true;
        }
        
    }
	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function after_ajax_save() 
	{
		if ($this->input->post('record_id') == '-1') {
			$data['employee_update_status_id'] = 1;
			$data['date_created'] = date('Y-m-d');
			$data['created_by'] = $this->userinfo['user_id'];

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $data);	
		}

		parent::after_ajax_save();
	}

	// function _set_filter()
	// {
	// 	if (!$this->is_recruitment() && !$this->is_superadmin) {
	// 	//if ($this->user->is_srmanager || $this->user->is_jrmanager || $this->user->is_supervisor) {
	// 		$this->db->where(
	// 			'(created_by = ' . $this->userinfo['user_id'] 
	// 				. ' OR employee_id = ' . $this->userinfo['user_id'] . ')'
	// 			);
	// 	}		
	// }

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

	  $approver = $this->system->is_module_approver( $this->module_id, $this->userinfo['position_id'] );
	  $emp_approver = $this->_is_employee_approver($this->userinfo['user_id'], $this->module_id);
	  if($approver || $emp_approver){
	  	$buttons .= "<div class='icon-label'><a class='icon-16-approve approve-array status-buttons' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Approve</span></a></div>";
        $buttons .= "<div class='icon-label'><a class='icon-16-disapprove disapprove-array status-buttons' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Declined</span></a></div>";	
	  }
	  
	  $buttons .= "</div>";
	              
	  return $buttons;
	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['edit'] && $record['t2employee_update_status'] == 'For Approval') {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a>';
        }

        if ( $record['t2employee_update_status'] == 'For Approval' && $this->input->post('filter') == "for_approval") {
            $actions .= '<a class="icon-button icon-16-approve" tooltip="Approve" href="javascript:void(0)"></a>';
        }

        if ( $record['t2employee_update_status'] == 'For Approval' && $this->input->post('filter') == "for_approval") {
            $actions .= '<a class="icon-button icon-16-disapprove" tooltip="Decline" href="javascript:void(0)"></a>';
        }                
        
        if ($this->user_access[$this->module_id]['delete'] && $record['t2employee_update_status'] == 'For Approval') {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	function get_previousinformation() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('*');
			//QUERY BEFORE pres_address1, pres_address2, pres_city, pres_province, pres_zipcode, perm_address1, perm_address2, perm_city, perm_province, perm_zipcode

			//$this->db->order_by("name", "asc");
			//$this->db->order_by("birth_date", "asc");
			$this->db->select('employee.*, a.city AS present_city, b.city AS permanent_city');
			$this->db->join('cities AS a','employee.pres_city = a.city_id','left');
			$this->db->join('cities AS b','employee.perm_city = b.city_id','left');
			$this->db->where('employee.employee_id', $this->input->post('employee_id'));
			$this->db->where('employee.deleted', 0);

			$employee = $this->db->get('employee');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_previous_family() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('name, birthdate, relationship, occupation, employer, educational_attainment, degree, ecf_dependent, bir_dependent, hospitalization_dependent, already_exist, flagcount');
			$this->db->order_by("name", "asc");
			$this->db->order_by("birthdate", "asc");
			$this->db->where('employee_update_id', $this->input->post('record_id'));
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee_update_family');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function show_previous_personal() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			//$this->db->select('*');
			$this->db->join('employee','employee.employee_id = user.employee_id','left');
			$this->db->where('user.employee_id', $this->input->post('employee_id'));
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('user.deleted', 0);

			$employee = $this->db->get('user');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->row_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function show_edited_personal() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			$this->db->select('*');
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			//$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('employee_update_id', $this->input->post('record_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee_update_personal');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->row_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	// This set of function will indicate the changes or red in detail view

	// This two functions (old_val_array_bu, old_val_family_bu) will indicate the changes when approved detailview is clicked
	function old_val_array_bu() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			$this->db->where('deleted',0);
			$this->db->where('employee_update_id',$this->input->post('record_id'));
			$emp_id=$this->db->get('employee_update')->row_array();

			$this->db->select('city_b.city AS perm_city_full, city_a.city AS pres_city_full, employee_bu.*, civil_status.civil_status');
			$this->db->join('civil_status','civil_status.civil_status_id = employee_bu.civil_status_id');
			$this->db->join('cities as city_a','employee_bu.pres_city = city_a.city_id', 'left');
			$this->db->join('cities as city_b','employee_bu.perm_city = city_b.city_id', 'left');
			$this->db->where('employee_bu.employee_update_id', $this->input->post('record_id'));

			$employee = $this->db->get('employee_bu');

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'No Old Employee Value';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->row_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function old_val_family_bu() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			$this->db->where('employee_update_id', $this->input->post('record_id'));
			$this->db->where('deleted', 0);
			$emp_id = $this->db->get('employee_update_family')->row_array();

			$this->db->where('employee_id', $emp_id['employee_id']);
			$this->db->where('employee_update_id', $this->input->post('record_id'));
			$this->db->order_by('name','asc');

			$employee = $this->db->get('employee_family_bu');


			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'No_changes';
				$response->msg 		= 'No Old Employee Value';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}
	// This two functions (old_val_array_bu, old_val_family_bu) will indicate the changes when approved detailview is clicked

	// This two functions (old_val_array, old_val_family) will indicate the changes when for approval detailview is clicked
	function old_val_array() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			$this->db->where('deleted',0);
			$this->db->where('employee_update_id',$this->input->post('record_id'));
			$emp_id=$this->db->get('employee_update')->row_array();

			$this->db->select('city_b.city AS perm_city_full, city_a.city AS pres_city_full, user.*, employee.*,civil_status.civil_status');
			$this->db->join('user','employee.employee_id = user.employee_id');
			$this->db->join('civil_status','civil_status.civil_status_id = employee.civil_status_id');
			$this->db->join('cities as city_a','employee.pres_city = city_a.city_id','left');
			$this->db->join('cities as city_b','employee.perm_city = city_b.city_id','left');
			$this->db->where('user.employee_id', $emp_id['employee_id']);
			$this->db->where('user.deleted', 0);

			$employee = $this->db->get('employee');



			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'No Old Employee Value';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->row_array();
			}			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function old_val_family() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			$this->db->where('employee_update_id', $this->input->post('record_id'));
			$this->db->where('deleted', 0);
			$emp_id = $this->db->get('employee_update_family')->row_array();

			$this->db->where('employee_id', $emp_id['employee_id']);
			$this->db->order_by('name','asc');
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee_family');


			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'No_changes';
				$response->msg 		= 'No Old Employee Value';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}
	// This two functions (old_val_array, old_val_family) will indicate the changes when for approval detailview is clicked

	// This set of function will indicate the changes or red in detail view

	function get_editedinfo() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('*');
			//$this->db->order_by("name", "asc");
			//$this->db->order_by("birth_date", "asc");
			$this->db->select('employee.*, a.city AS present_city, b.city AS permanent_city');
			$this->db->join('employee','employee.employee_id = employee_update_family.employee_id','left');
			$this->db->join('cities AS a','employee_update_family.pres_city = a.city_id','left');
			$this->db->join('cities AS b','employee_update_family.perm_city = b.city_id','left');
			$this->db->where('employee_update_family.employee_update_id', $this->input->post('employee_id'));
			$this->db->where('employee_update_family.deleted', 0);

			$employee = $this->db->get('employee_update_family');

			if (!$employee || $employee->num_rows() == 0) {
				//$response->msg_type = 'error';
				//$response->msg 		= 'Family not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_family() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			if (trim($this->input->post('employee_id')) != ''){
				$this->db->select('name, birth_date, relationship, occupation, employer, educational_attainment, degree, ecf_dependent, bir_dependents, hospitalization_dependents');
				$this->db->order_by("name", "asc");
				$this->db->order_by("birth_date", "asc");
				$this->db->where('employee_id', $this->input->post('employee_id'));
				$this->db->where('deleted', 0);

				$employee = $this->db->get('employee_family');

				if (!$employee || $employee->num_rows() == 0) {
					$response->msg_type = 'error';
					$response->msg 		= 'Family not found.';
				} else {
					$response->msg_type = 'success';
					$response->data = $employee->result_array();
				}	
			}
			else{
				$response->msg_type = 'success';
				$response->data = array();
			}
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function change_status_multiple($record_id = 0){

		$update_status_id = $this->input->post('update_status_id');
		$record_id = explode(',',$this->input->post('record_id'));

		$this->db->where_in($this->key_field, $record_id);
		$result = $this->db->get($this->module_table);

		$err_ctr = 0;
		$err_msg = array();
		$success_ctr = 0;
		$status_record = "";
		$total_ctr = 0;

		$status = "";
		switch($update_status_id){
			case 2:
			$status = "Approved";
			break;
			case 3:
			$status = "Declined";
			break;
		}

		$response['sequence'] ="";

		foreach( $result->result_array() as $record ){

			$response['sequence'] .= ','.$record[$this->key_field];

			$rec = $this->db->get_where( $this->module_table, array( $this->key_field => $record[$this->key_field] ) )->row();

			if( $rec->employee_update_status_id == 1 ){

				switch($update_status_id){
					case 2:
						$ustatus="approve";
					break;
					case 3:
						$ustatus="decline";
					break;
				}

				$status_result = $this->change_status($record[$this->key_field],$ustatus,1);

				if( $status_result->msg_type == 'error' ){
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
				$response['message'] = $success_ctr.' out of '.$total_ctr.' Employee Update(s) have been '.$status;
			}
			else{
				$response['message'] = $success_ctr.' out of '.$total_ctr.' Employee Update(s) has been '.$status;
			}
                              				
			$response['type'] = 'success';
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}
		else{

			if( $success_ctr > 1 ){
				$response['message'] = $success_ctr.' out of '.$total_ctr.' Employee Update(s) have been '.$status.'<br /> Please check on those not approved <br />'; 
			}
			else{
				$response['message'] = $success_ctr.' out of '.$total_ctr.' Employee Update(s) has been '.$status.'<br /> Please check on those not approved <br />'; 
			}                            				

			$response['type'] = 'error';
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}

	}

	function change_status($record_id = 0, $status = "", $non_ajax = 0) 
	{
		if( $non_ajax == 0 ){
			$status    = $this->input->post('status');
			$record_id = $this->input->post('record_id');
		}

		switch ($status) {
			case 'approve':					
					$data['employee_update_status_id'] = 2;
					if (!$this->_approve_request($record_id)) {
						$response->msg_type = 'error';
						$response->msg 		= 'Update failed. Contact the Administrator';
					} else {
						$this->db->where($this->key_field, $record_id);						
						$this->db->update($this->module_table, $data);

						$response->msg_type = 'success';						
						$response->msg  	= 'Employee 201 update request approved. Employee records updated.';
					}									
				break;
			case 'decline':					
					$data['employee_update_status_id'] = 3;
					$this->db->where($this->key_field, $record_id);
					
					if (!$this->db->update($this->module_table, $data)) {
						$response->msg_type = 'error';
						$response->msg 		= 'Update failed. Contact the Administrator';
					} else {
						$response->msg_type = 'success';
						$response->msg  	= 'Employee 201 update request denied.';
					}
			break;
		}

		if($response->msg_type == 'success')
			$this->_send_status_email($record_id);

		if( $non_ajax == 0){
			// if($this->input->post('bypass'))
				$this->load->view('template/ajax', array('json' => $response));
			// else
			// 	$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $response);
		}
		else{
			return $response;
		}
	}

	function check_status() 
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {

			$this->db->where('employee_update_id', $this->input->post('record_id'));
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee_update');
			
			if ($employee && $employee->num_rows() != 0) {
				$response->msg_type = 'success';
				$response->data = $employee->row();
			}			

			$this->load->view('template/ajax', array('json' => $response));	
		}
	}

	function get_employee() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('employee.*, user.user_id, user.firstname, user.lastname, position, department, user.position_id, user.department_id, user_company.company');
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->where('user.user_id', $this->input->post('employee_id'));
			$this->db->where('user.deleted', 0);
			$this->db->limit(1);

			$employee = $this->db->get('user');

			

			if (!$employee || $employee->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'Employee not found.';
			} else {
				$response->msg_type = 'success';

				$response->data = $employee->row_array();
			}			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	
	// END custom module funtions

 	function send_email() {
		$approvers=$this->system->get_module_approver( $this->module_id, $this->userinfo['position_id'] );
 		

        $this->db->join('user','user.employee_id=employee_update.employee_id');
        $this->db->where('user.employee_id', $this->userinfo['user_id']);
        $this->db->where('employee_update_id',$this->input->post('record_id'));
        $request = $this->db->get('employee_update');

        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                foreach($approvers as $approver)
                {
	                $this->db->where('position_id', $approver['approver_position_id']);
	                $emailApprover=$this->db->get('user')->result_array();
	                foreach ($emailApprover as $row_email) {
	                    $request['approver_user'] = $row_email['salutation']." ".$row_email['lastname'].", ".$request['approver_user'];
	                }
	                
	            }

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'update201');
                $message = $this->template->prep_message($template['body'], $request);

                // Approvers.

	            foreach($approvers as $approver)
	            {
	                $this->db->where('position_id', $approver['approver_position_id']);
	                $result = $this->db->get('user')->result_array();

	                foreach ($result as $row) {
	                    $recepients[] = $row['email'];
	                }
	            }

                // If queued successfully set the status to For Approval.
                if ($this->template->queue(implode(',', $recepients), '', $template['subject'], $message)) {
                    $data['employee_update_status_id'] = 1;
                    $data['email_sent'] = '1';
                    $data['date_sent'] = date('Y-m-d G:i:s');                    
                    $this->db->where($this->key_field, $request[$this->key_field]);
                    $this->db->update($this->module_table, $data);
                    $this->db->update('form_approver', array('status' => 2), array('module_id' => $this->module_id, 'record_id' => $this->input->post('record_id')) );
                } else {
                    $data['employee_update_status_id'] = 1;
                    $this->db->where($this->key_field, $request[$this->key_field]);
                    $this->db->update($this->module_table, $data);
                }
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    private function _send_status_email($record_id = false) 
    {
    	if(!$record_id) {
    		$this->session->set_flashdata('flashdata', 'No record id and status set. Status E-mail not sent');
            redirect(base_url() . $this->module_link);
    	}

    	$this->db->join('employee_update_status','employee_update.employee_update_status_id = employee_update_status.employee_update_status_id', 'left');
    	$this->db->join('user', 'user.employee_id = employee_update.employee_id', 'left');
    	$result = $this->db->get_where("employee_update", array("employee_update_id" => $record_id));

        if ($result && $result->num_rows() > 0) 
        {
        	
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');

            if ($mail_config) 
            {

                $requestor = $result->row_array();

                // get name of requestor and current day
				if (CLIENT_DIR  == 'firstbalfour'){
                    $requestor['full_name'] = $requestor['salutation'].' '.$requestor['firstname'].' '.$requestor['middlename'].' '.$requestor['lastname'];
                    if ($requestor['aux'] != ''){
                    	$requestor['full_name'] = $requestor['salutation'].' '.$requestor['firstname'].' '.$requestor['middlename'].' '.$requestor['lastname'].' '.$requestor['aux'];
                    }                            							
				}
				else{
					$requestor['full_name'] = $requestor['salutation'].' '.$requestor['firstname'].' '.$requestor['middlename'].' '.$requestor['lastname'];
				}                
                $requestor['date_today'] = date($this->config->item('display_date_format'));
                $requestor['date_created'] = date($this->config->item('display_date_format'), strtotime($requestor['date_created']));

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'update201_status');
                $message = $this->template->prep_message($template['body'], $requestor);

                // send email
                $this->template->queue($requestor['email'], '', $template['subject'], $message);

            }

        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    private function _is_employee_approver($employee_id, $module_id)
    {
    	$this->db->select('employee_id');
    	$result = $this->db->get_where('employee_approver', array('module_id' => $module_id, 'approver_employee_id' => $employee_id, 'deleted' => 0));
    	if($result->num_rows() > 0)
    		return $result->result_array();
    	else
    		return false;
    }

    // returns true or false
    private function _have_employee_approver($employee_id, $module_id)
    {
    	$result = $this->db->get_where('employee_approver', array('module_id' => $module_id, 'employee_id' => $employee_id, 'deleted' => 0));
    	if($result->num_rows() > 0)
    		return true;
    	else
    		return false;
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
		$search_string[] = $this->db->dbprefix .'employee_update_status.employee_update_status LIKE "%' . $value . '%"';
		// $search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';
		// $search_string[] = $this->db->dbprefix .'user_company_department.department LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';

		return $search_string;
	}

	function _custom_join(){
		$this->db->join('user', 'employee_update.employee_id = user.employee_id', 'left');
		$this->db->join('employee_update_status', 'employee_update.employee_update_status_id = employee_update_status.employee_update_status_id', 'left');
	}	

	/**
	 * Rearrange the array to a new array which can be used for insert_batch
	 *
	 * @param array $array
	 * @param int $key
	 *
	 * @return array
	 */
	private function _rebuild_array($array, $fkey = null,$parent_id_name = false,$parent_id_val = false) {
		if (!is_array($array)) {
			return array();
		}

		$new_array = array();

		$count = count(end($array));
		$index = 0;

		while ($count > $index) {
			foreach ($array as $key => $value) {
				$new_array[$index][$key] = $array[$key][$index];
				if (!is_null($fkey)) {
					$new_array[$index]['employee_id'] = $fkey;
				}
				if ($parent_id_name && $parent_id_val) {
					$new_array[$index][$parent_id_name] = $parent_id_val;
				}				
			}

			$index++;
		}

		return $new_array;
	}

	function get_form_attachment($type) {
		if (IS_AJAX) {
			$data['count'] = $this->input->post('counter_line');
			$data['rand'] = rand(1000,9999);

			$response = $this->load->view($this->userinfo['rtheme'] . '/employees/update_201/attachment/form', $data);

			$data['html'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	
}

/* End of file */
/* Location: system/application */