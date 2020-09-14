<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Annual_manpower_planning extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Annual Manpower Planning';
		$this->listview_description = 'List of Annual Manpower Planning.';
		$this->jqgrid_title = "Annual Manpower Planning List";
		$this->detailview_title = 'Annual Manpower Planning Info';
		$this->detailview_description = 'This page shows detailed information about a particular annual manpower planning.';
		$this->editview_title = 'Annual Manpower Planning Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about annual manpower planning..';

		if($this->userinfo['login'] != "webadmin")
		{
			if( $this->user_access[$this->module_id]['post'] != 1 ){
				$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
				$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
				$this->_subordinate_id = array();

				$this->_subordinate_id[] = $this->userinfo['user_id'];

					foreach ($subordinates as $subordinate) {
						$this->_subordinate_id[] = $subordinate['user_id'];
					}

					$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_approver 
							   WHERE module_id = 220
							   AND approver_employee_id = ".$this->userinfo['user_id']." 
							   AND deleted = 0");

					if ($result && $result->num_rows() > 0){
						foreach ($result->result() as $row) {
							if (!in_array($row->employee_id, $this->_subordinate_id)){
								$this->_subordinate_id[] = $row->employee_id;
							}
						}
					}
				$this->filter = "(" .$this->db->dbprefix."annual_manpower_planning.created_by = ".$this->userinfo['user_id']. " OR "
									.$this->db->dbprefix."annual_manpower_planning.annual_user_division_id = ".$this->userinfo['user_id']." OR "
									.$this->db->dbprefix."annual_manpower_planning.annual_user_department_id = ".$this->userinfo['user_id'].")";
				// $this->filter = "IF(".$this->db->dbprefix."annual_manpower_planning.created_by = ".$this->userinfo['user_id']." , ".$this->db->dbprefix."annual_manpower_planning.annual_manpower_planning_status_id IN (1,2,3,4,6),'1')";
				// $this->filter .= " OR IF(".$this->db->dbprefix."annual_manpower_planning.annual_user_division_id = ".$this->userinfo['user_id']." , ".$this->db->dbprefix."annual_manpower_planning.annual_manpower_planning_status_id IN (1,2,3,4,6),'1')";
				// $this->filter .= " OR ".$this->db->dbprefix."annual_manpower_planning.department_id = ".$this->userinfo['department_id'];;
				// $this->filter .= " AND (".$this->db->dbprefix."annual_manpower_planning.employee_id = ".$this->userinfo['user_id']." OR ".$this->db->dbprefix."annual_manpower_planning.annual_user_division_id = ".$this->userinfo['user_id']."
					// OR hr_annual_manpower_planning.employee_id IN (".implode(',', $this->_subordinate_id).")) ";

				
			}

			/*$draft_forms = $this->db->get_where('annual_manpower_planning_approver', array('approver' => $this->user->user_id, 'focus' => 0, 'status' => 1));
		
			if ($draft_forms && $draft_forms->num_rows() > 0) {
				$draft_ids = array();
				
				foreach ($draft_forms->result() as $key => $value) {
					$draft_ids[] = $value->amp_id;
				}

				$this->filter .= ' AND '.$this->db->dbprefix.$this->module_table.".{$this->key_field} NOT IN (".implode(',', $draft_ids).")";

			}*/



			/*			
			if( $this->user_access[$this->module_id]['approve'] != 1 ){
				$this->filter .= "AND ".$this->db->dbprefix."annual_manpower_planning.department_id = ".$this->userinfo['department_id'];
			}
			*/

		}
		//$this->filter = "IF(".$this->db->dbprefix."annual_manpower_planning.annual_user_division_id = ".$this->userinfo['user_id']." , '".$this->db->dbprefix."annual_manpower_planning.annual_manpower_planning_status_id IN (2,3,4)','1')";
		// }
		// else{
		// 	$this->filter = $this->db->dbprefix.'annual_manpower_planning.annual_manpower_planning_status_id > 1';			
		// }

	}

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
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
		$data['content'] = 'recruitment/annual_manpower_applicant/detailview';
		
		//other views to load
		$data['views'] = array('recruitment/annual_manpower_applicant/details_detailview');
		$data['views_outside_record_form'] = array();		

    	$this->db->where('annual_manpower_planning_id',$this->input->post('record_id'));
    	$result = $this->db->get('annual_manpower_planning');			
    	$year = $result->row()->year; 
	    $data['year'] = $year;
	    $data['department_id'] = $result->row()->department_id; 
	    if (CLIENT_DIR == 'firstbalfour') {
	    	$data['category_id'] = $result->row()->category_id; 	
	    }
		$data['annual_manpower_planning_id'] = $this->input->post('record_id');


		$this->db->join('user','user.employee_id = annual_manpower_planning.employee_id','left');
    	$this->db->where('annual_manpower_planning.annual_manpower_planning_id',$this->input->post('record_id'));
    	$annual_manpower_planning_head_info = $this->db->get('annual_manpower_planning')->row();

		$data['position_hierarchy'] = $this->_get_reporting_to_position_hierarchy($annual_manpower_planning_head_info->position_id);


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
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}	
			
			$data['content'] = 'editview';
			
			
			// $data['buttons'] = 'recruitment/annual_manpower_applicant/send_request';

			//other views to load
			// $data['views'] = '';
			// if ($this->input->post('record_id') != -1) {
				$data['views'] = array('recruitment/annual_manpower_applicant/module_annual_manpower_planning_gui');
			// }
			
			$data['ranks'] = $this->get_employee_ranks();
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
		else{
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}		
	
	function ajax_save()
	{	

		$error = 0;

	    if ($this->input->post('record_id') <> -1 ){

	    	$annual_manpower_planning_info = $this->db->get_where('annual_manpower_planning',array($this->key_field => $this->input->post('record_id')))->row();

	    	$year = $annual_manpower_planning_info->year;

	    	$this->db->where('department_id',$this->input->post('department_id'));		
			$this->db->where('year',$this->input->post('year'));
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');


            if( ( $result->num_rows() > 0 ) && ( $year != $this->input->post('year') ) ){

                $response->msg = "Attention: Department and Year already applied.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $error++;
            }
            else{
            	$_POST['created_by'] = $this->input->post('created_by');
                parent::ajax_save();
            }	    	


	    }
	    else{ 

			$this->db->where('department_id',$this->input->post('department_id'));		
			$this->db->where('year',$this->input->post('year'));
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');

            if($result->num_rows() > 0){
                $response->msg = "Attention: Department and Year already applied.";
                $response->msg_type = "error";
                $data['json'] = $response;
                $error++;
            }
            else{

            	$_POST['created_by'] = $this->input->post('created_by');

                parent::ajax_save();

            }	    	
	    }

	    if( $error == 0 ){

			$annual_manpower_planning_id = $this->key_field_val;

			$this->db->where('annual_manpower_planning_id',$annual_manpower_planning_id);
			$this->db->update('annual_manpower_planning',array('date_created'=>date('Y-m-d G:i:s')));

			// if ($this->input->post('record_id') <> -1){
			// 	$this->db->delete('annual_manpower_planning_details', array('annual_manpower_planning_id' => $annual_manpower_planning_id));
			// 	$this->db->delete('annual_manpower_planning_position', array('annual_manpower_planning_id' => $annual_manpower_planning_id));
			// }	

			$user_id = $this->input->post('user_id');
			$position_id = $this->input->post('position_id');
			$remarks_jan = $this->input->post('remarks_jan'); 
			$remarks_feb = $this->input->post('remarks_feb'); 
			$remarks_mar = $this->input->post('remarks_mar'); 
			$remarks_apr = $this->input->post('remarks_apr'); 
			$remarks_may = $this->input->post('remarks_may'); 
			$remarks_jun = $this->input->post('remarks_jun'); 	
			$remarks_jul = $this->input->post('remarks_jul'); 	
			$remarks_aug = $this->input->post('remarks_aug'); 
			$remarks_sep = $this->input->post('remarks_sep'); 
			$remarks_oct = $this->input->post('remarks_oct'); 
			$remarks_nov = $this->input->post('remarks_nov'); 
			$remarks_dec = $this->input->post('remarks_dec'); 																					
			$budget = $this->input->post('budget'); 																					
			$rank = $this->input->post('rank_id'); 																					

			if ($this->input->post('user_id'))
			{
				foreach ($this->input->post('user_id') as $index => $val){
					$array_info = array();
					$array_info['annual_manpower_planning_id'] = $annual_manpower_planning_id;
					$array_info['user_id'] = $user_id[$index];
					$array_info['position_id'] = $position_id[$index];
					$array_info['department_id'] = $this->input->post('department_id');
					$array_info['jan'] = $remarks_jan[$index];
					$array_info['feb'] = $remarks_feb[$index];
					$array_info['mar'] = $remarks_mar[$index];
					$array_info['apr'] = $remarks_apr[$index];
					$array_info['may'] = $remarks_may[$index];
					$array_info['jun'] = $remarks_jun[$index];
					$array_info['jul'] = $remarks_jul[$index];
					$array_info['aug'] = $remarks_aug[$index];
					$array_info['sep'] = $remarks_sep[$index];
					$array_info['oct'] = $remarks_oct[$index];
					$array_info['nov'] = $remarks_nov[$index];
					$array_info['dec'] = $remarks_dec[$index];
					$array_info['budget'] = $budget[$index];
					$array_info['rank_id'] = $rank[$index];

					if ($this->input->post('record_id') <> -1) {
						$this->db->where('annual_manpower_planning_details_id', $index);
						$this->db->update('annual_manpower_planning_details', $array_info);						
					}else{
						$this->db->insert('annual_manpower_planning_details',$array_info);			
					}
				}
			}


			if ($this->input->post('existing_position'))
			{

				$position_id = $this->input->post('existing_position');
				$existing_job_headcount_jan = $this->input->post('existing_job_headcount_jan'); 
				$existing_job_headcount_feb = $this->input->post('existing_job_headcount_feb'); 
				$existing_job_headcount_mar = $this->input->post('existing_job_headcount_mar'); 
				$existing_job_headcount_apr = $this->input->post('existing_job_headcount_apr'); 
				$existing_job_headcount_may = $this->input->post('existing_job_headcount_may'); 
				$existing_job_headcount_jun = $this->input->post('existing_job_headcount_jun'); 	
				$existing_job_headcount_jul = $this->input->post('existing_job_headcount_jul'); 	
				$existing_job_headcount_aug = $this->input->post('existing_job_headcount_aug'); 
				$existing_job_headcount_sep = $this->input->post('existing_job_headcount_sep'); 
				$existing_job_headcount_oct = $this->input->post('existing_job_headcount_oct'); 
				$existing_job_headcount_nov = $this->input->post('existing_job_headcount_nov'); 
				$existing_job_headcount_dec = $this->input->post('existing_job_headcount_dec'); 
				$existing_job_headcount_total = $this->input->post('existing_job_headcount_total'); 
				$existing_job_budget = $this->input->post('existing_job_budget'); 

				foreach ($this->input->post('existing_position') as $index => $val){
					$array_info = array();
					$array_info['annual_manpower_planning_id'] = $annual_manpower_planning_id;
					$array_info['position_id'] = $position_id[$index];
					$array_info['type'] = '2';
					$array_info['date_created'] = date('Y-m-d h:i:s');
					$array_info['jan'] = $existing_job_headcount_jan[$index];
					$array_info['feb'] = $existing_job_headcount_feb[$index];
					$array_info['mar'] = $existing_job_headcount_mar[$index];
					$array_info['apr'] = $existing_job_headcount_apr[$index];
					$array_info['may'] = $existing_job_headcount_may[$index];
					$array_info['jun'] = $existing_job_headcount_jun[$index];
					$array_info['jul'] = $existing_job_headcount_jul[$index];
					$array_info['aug'] = $existing_job_headcount_aug[$index];
					$array_info['sep'] = $existing_job_headcount_sep[$index];
					$array_info['oct'] = $existing_job_headcount_oct[$index];
					$array_info['nov'] = $existing_job_headcount_nov[$index];
					$array_info['dec'] = $existing_job_headcount_dec[$index];
					$array_info['total'] = $existing_job_headcount_total[$index];
					$array_info['budget'] = $existing_job_budget[$index];

					if ($this->input->post('record_id') <> -1) {
						$this->db->where('annual_manpower_planning_position_id', $index);
						$this->db->update('annual_manpower_planning_position', $array_info);						
					}else{
						$this->db->insert('annual_manpower_planning_position',$array_info);	
					}

								
				}
			}


			if ($this->input->post('new_position_name'))
			{

				$new_job_name = $this->input->post('new_position_name');
				$new_job_remarks = $this->input->post('new_position_remarks');
				$new_job_headcount_jan = $this->input->post('new_job_headcount_jan'); 
				$new_job_headcount_feb = $this->input->post('new_job_headcount_feb'); 
				$new_job_headcount_mar = $this->input->post('new_job_headcount_mar'); 
				$new_job_headcount_apr = $this->input->post('new_job_headcount_apr'); 
				$new_job_headcount_may = $this->input->post('new_job_headcount_may'); 
				$new_job_headcount_jun = $this->input->post('new_job_headcount_jun'); 	
				$new_job_headcount_jul = $this->input->post('new_job_headcount_jul'); 	
				$new_job_headcount_aug = $this->input->post('new_job_headcount_aug'); 
				$new_job_headcount_sep = $this->input->post('new_job_headcount_sep'); 
				$new_job_headcount_oct = $this->input->post('new_job_headcount_oct'); 
				$new_job_headcount_nov = $this->input->post('new_job_headcount_nov'); 
				$new_job_headcount_dec = $this->input->post('new_job_headcount_dec'); 
				$new_job_headcount_total = $this->input->post('new_job_headcount_total'); 
				$new_job_budget = $this->input->post('new_job_headcount_budget'); 
				$ids = array();
				foreach ($this->input->post('new_position_name') as $index => $val){
					$array_info = array();
					$array_info['annual_manpower_planning_id'] = $annual_manpower_planning_id;
					$array_info['position'] = $new_job_name[$index];
					$array_info['remarks'] = $new_job_remarks[$index];
					$array_info['type'] = '1';
					$array_info['date_created'] = date('Y-m-d h:i:s');
					$array_info['jan'] = $new_job_headcount_jan[$index];
					$array_info['feb'] = $new_job_headcount_feb[$index];
					$array_info['mar'] = $new_job_headcount_mar[$index];
					$array_info['apr'] = $new_job_headcount_apr[$index];
					$array_info['may'] = $new_job_headcount_may[$index];
					$array_info['jun'] = $new_job_headcount_jun[$index];
					$array_info['jul'] = $new_job_headcount_jul[$index];
					$array_info['aug'] = $new_job_headcount_aug[$index];
					$array_info['sep'] = $new_job_headcount_sep[$index];
					$array_info['oct'] = $new_job_headcount_oct[$index];
					$array_info['nov'] = $new_job_headcount_nov[$index];
					$array_info['dec'] = $new_job_headcount_dec[$index];
					$array_info['total'] = $new_job_headcount_total[$index];
					$array_info['budget'] = $new_job_budget[$index];
					$ids[]=$index;
					if ($this->input->post('record_id') <> -1) {
						$this->db->where('annual_manpower_planning_position_id', $index);
						$this->db->update('annual_manpower_planning_position', $array_info);	

						if ($this->db->affected_rows() == 0) {
							$this->db->insert('annual_manpower_planning_position',$array_info);	
						}					
					}else{
						$this->db->insert('annual_manpower_planning_position',$array_info);		
					}
						
				}
				$flag_to_delete = false;

				if (count($ids) == 1 && $ids[0] == 0) {
					$flag_to_delete = false;					
				}else{
					$flag_to_delete = true;
				}
				
				if ($flag_to_delete) {
					$where =  'annual_manpower_planning_position_id NOT IN ('.implode(',', $ids).')';
					$this->db->where($where);
					$this->db->where('annual_manpower_planning_id', $annual_manpower_planning_id);
					$this->db->where('type', 1);
					$this->db->update('annual_manpower_planning_position', array('deleted' => 1));
				}
				
				
			}


			
		}
		else{

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}
		
	}

    /**
     * Send the email to approvers.
     */
    function send_email() 
    {

        $this->db->join('user','user.employee_id=annual_manpower_planning.employee_id');
    	$this->db->join('user_position','user.position_id = user_position.position_id','left');                
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id','left');
    	$this->db->join('user_company_division','user_company_department.division_id = user_company_division.division_id','left');
        $this->db->join('annual_manpower_planning_status','annual_manpower_planning_status.annual_manpower_planning_status_id=annual_manpower_planning.annual_manpower_planning_status_id','left');        
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('annual_manpower_planning_id', $this->input->post('record_id'));
        $result = $this->db->get('annual_manpower_planning');

        if (IS_AJAX && !is_null($result) && $result->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $result->row_array();
				$amp_approver = array();
				$where = array();
				$ids = array();
                //get approvers via employee approver / position module
                $employee_approver_list = $this->system->get_approvers_emails_and_condition($request['employee_id'],$this->module_id);
                foreach( $employee_approver_list as $employee_approver ){                        	
                	

                	switch($employee_approver['condition']){
                        case 1:
                            if ($employee_approver['focus'] == 1) {
                           		$ids[] = $employee_approver['approver']; 	
                            }
                            break;
                        case 2:
                        case 3:
                   	       // $this->db->or_where('user_id', $employee_approver['approver'] );
                   	       	$ids[] = $employee_approver['approver'];
                           break;
                    }   
                }

				/*$this->db->or_where('user_id', $request['annual_user_division_id']);*/
				$where_ids = 'user_id IN('.implode(',', $ids).')';

				$this->db->where($where_ids);
                $result_sql = $this->db->get('user');
                
                $result = $result_sql->result_array();

               	foreach( $employee_approver_list as $employee_approver ){
               		$amp_approver['status'] = 2;
               	 	switch($employee_approver['condition']){
                        case 1:
                        	$where['amp_id'] = $this->input->post('record_id');
                            if ($employee_approver['focus'] == 1) {
                            	$where['approver'] = $employee_approver['approver'];
                            }
                            break;
                        case 2:
                        case 3:
                           $where['amp_id'] = $this->input->post('record_id');
                           break;
                    }                        	
                	
                	$this->db->update('annual_manpower_planning_approver', $amp_approver, $where);
                	
                }
                

                // Load the template.  
                $this->load->model('template');
               
                
                // return;
                // If queued successfully set the status to For Approval.
                if ($result_sql->num_rows() > 0) {

                	$annual_manpower_planning_info = $this->db->get_where('annual_manpower_planning',array('annual_manpower_planning_id' => $this->input->post('record_id')))->row();
                	$planning_info = $this->db->get_where('annual_manpower_planning_position',array('annual_manpower_planning_id' => $this->input->post('record_id'), 'type' => 1 ));

                	if( $annual_manpower_planning_info->annual_manpower_planning_status_id == 1 &&  ( ( $annual_manpower_planning_info->created_by == $this->userinfo['user_id'] && $annual_manpower_planning_info->annual_user_division_id == $this->userinfo['user_id'] ) ) ){

                		$data['annual_manpower_planning_status_id'] = 3;
                		$this->db->where($this->key_field, $request[$this->key_field]);
	                    $this->db->update($this->module_table, $data);
						

                	}
                	elseif( $annual_manpower_planning_info->annual_manpower_planning_status_id == 1 &&  $this->user_access[$this->module_id]['post'] == 1  ){

                		$data['annual_manpower_planning_status_id'] = 2;
	                    $data['email_sent'] = '1';
	                    $data['date_sent'] = date('Y-m-d G:i:s');                    
	                    $this->db->where($this->key_field, $request[$this->key_field]);
	                    $this->db->update($this->module_table, $data);

	                    $this->db->join('user','user.employee_id=annual_manpower_planning.employee_id');
				    	$this->db->join('user_position','user.position_id = user_position.position_id');                
				    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
				    	$this->db->join('user_company_division','user_company_department.division_id = user_company_division.division_id');
				        $this->db->join('annual_manpower_planning_status','annual_manpower_planning_status.annual_manpower_planning_status_id=annual_manpower_planning.annual_manpower_planning_status_id');        
				        $this->db->join('user_company','user_company.company_id=user.company_id');
				        $this->db->where('annual_manpower_planning_id', $this->input->post('record_id'));
				        $request = $this->db->get('annual_manpower_planning')->row_array();

				        foreach ($result as $row) {
				        	if ($row['email'] != ''){			        	
			                    $request['approver_user'] = $row['salutation']." ".$row['lastname'];
			                    
			                    $recepients = $row['email'];

			                    $request['here']=base_url().'recruitment/annual_manpower_planning/detail/'.$request['annual_manpower_planning_id'];

			                    $request['date_created'] = date('Y-m-d',strtotime($request['date_created']));

			                    $template = $this->template->get_module_template($this->module_id, 'amp_status_email');
			                	$message = $this->template->prep_message($template['body'], $request);
			                	
			                	$this->template->queue($recepients, '', $template['subject'], $message);
				        	}
		                }

                	}
                	else{

	                	if( $planning_info->num_rows > 0 ){

	                    	$data['annual_manpower_planning_status_id'] = 6;
	                    	$data['email_sent'] = '1';
	                    	$data['date_sent'] = date('Y-m-d G:i:s');                    
	                    	$this->db->where($this->key_field, $request[$this->key_field]);
	                    	$this->db->update($this->module_table, $data);

	                	}
	                	else{

	                		$data['annual_manpower_planning_status_id'] = 2;
	                    	$data['email_sent'] = '1';
	                    	$data['date_sent'] = date('Y-m-d G:i:s');                    
	                    	$this->db->where($this->key_field, $request[$this->key_field]);
	                    	$this->db->update($this->module_table, $data);

	                	}

	                	$this->db->join('user','user.employee_id=annual_manpower_planning.employee_id');
				    	$this->db->join('user_position','user.position_id = user_position.position_id');                
				    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
				    	$this->db->join('user_company_division','user_company_department.division_id = user_company_division.division_id');
				        $this->db->join('annual_manpower_planning_status','annual_manpower_planning_status.annual_manpower_planning_status_id=annual_manpower_planning.annual_manpower_planning_status_id');        
				        $this->db->join('user_company','user_company.company_id=user.company_id');
				        $this->db->where('annual_manpower_planning_id', $this->input->post('record_id'));
				        $request = $this->db->get('annual_manpower_planning')->row_array();

				        foreach ($result as $row) {
				        	if ($row['email'] != ''){
			                    $request['approver_user'] = $row['salutation']." ".$row['lastname'];
			                    
			                    $recepients = $row['email'];

			                    $request['here']=base_url().'recruitment/annual_manpower_planning/detail/'.$request['annual_manpower_planning_id'];

			                    $request['date_created'] = date('Y-m-d',strtotime($request['date_created']));

			                    $template = $this->template->get_module_template($this->module_id, 'amp_status_email');
			                	$message = $this->template->prep_message($template['body'], $request);
			                	
			                	$this->template->queue($recepients, '', $template['subject'], $message);
				        	}
		                }

	                }
	                
                }
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    function send_email_approve($record_id) 
    {
        $this->db->join('user','user.employee_id=annual_manpower_planning.employee_id');
    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');                
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
    	$this->db->join('user_company_division','user_company_department.division_id = user_company_division.division_id', 'left');
        $this->db->join('annual_manpower_planning_status','annual_manpower_planning_status.annual_manpower_planning_status_id=annual_manpower_planning.annual_manpower_planning_status_id');        
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('annual_manpower_planning_id', $record_id);
        $result = $this->db->get('annual_manpower_planning');
        
        if (IS_AJAX && !is_null($result) && $result->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {

            	// return;
                // If queued successfully set the status to For Approval.
                $data['annual_manpower_planning_status_id'] = 3;             
                $this->db->where($this->key_field, $record_id);
                $this->db->update($this->module_table, $data);

                $this->db->join('user','user.employee_id=annual_manpower_planning.employee_id');
		    	$this->db->join('user_position','user.position_id = user_position.position_id', 'left');                
		    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id', 'left');
		    	$this->db->join('user_company_division','user_company_department.division_id = user_company_division.division_id', 'left');
		        $this->db->join('annual_manpower_planning_status','annual_manpower_planning_status.annual_manpower_planning_status_id=annual_manpower_planning.annual_manpower_planning_status_id');        
		        $this->db->join('user_company','user_company.company_id=user.company_id');
		        $this->db->where('annual_manpower_planning_id', $record_id);
		        $result = $this->db->get('annual_manpower_planning');

                $recepients = array();
                $request = $result->row_array();

                //get approvers via employee approver / position module
                $employee_approver_list = $this->system->get_approvers_emails_and_condition($request['employee_id'],$this->module_id);
                foreach( $employee_approver_list as $employee_approver ){
                	if( $employee_approver['email'] == 1 ){
                		$this->db->or_where('user_id', $employee_approver['approver'] );
                	}
                }

                $this->db->or_where('user_id', $request['employee_id']);
                $result_sql = $this->db->get('user');
                $result = $result_sql->result_array();

                // Load the template.            
                $this->load->model('template');

                $template = $this->template->get_module_template($this->module_id, 'amp_status_email');

                foreach ($result as $row) {

                	if( $request['user_id'] != $row['user_id'] ){

	                    $request['approver_user'] = $row['salutation']." ".$row['lastname'];
	                    $recepients = $row['email'];
	                    $request['here']=base_url().'recruitment/annual_manpower_planning/detail/'.$request['annual_manpower_planning_id'];
	                    $message = $this->template->prep_message($template['body'], $request);

	                    $this->template->queue($recepients, '', $template['subject'], $message);
	                
                	}
                }

                $template = $this->template->get_module_template($this->module_id, 'amp_own_status_email');
				$request['approver_user'] = $request['salutation']." ".$request['lastname'];
                $recepients = $request['email'];
                $request['here']=base_url().'recruitment/annual_manpower_planning/detail/'.$request['annual_manpower_planning_id'];
                $message = $this->template->prep_message($template['body'], $request);

                $this->template->queue($recepients, '', $template['subject'], $message);


            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

    function send_email_disapprove($record_id) 
    {
        $this->db->join('user','user.employee_id=annual_manpower_planning.employee_id');
    	$this->db->join('user_position','user.position_id = user_position.position_id');                
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
    	$this->db->join('user_company_division','user_company_department.division_id = user_company_division.division_id');
        $this->db->join('annual_manpower_planning_status','annual_manpower_planning_status.annual_manpower_planning_status_id=annual_manpower_planning.annual_manpower_planning_status_id');        
        $this->db->join('user_company','user_company.company_id=user.company_id');
        $this->db->where('annual_manpower_planning_id', $record_id);
        $result = $this->db->get('annual_manpower_planning');

        if (IS_AJAX && !is_null($result) && $result->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {

            	// return;
                // If queued successfully set the status to For Approval.
                $data['annual_manpower_planning_status_id'] = 4;             
                $this->db->where($this->key_field, $record_id);
                $this->db->update($this->module_table, $data);

                $this->db->join('user','user.employee_id=annual_manpower_planning.employee_id');
		    	$this->db->join('user_position','user.position_id = user_position.position_id');                
		    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
		    	$this->db->join('user_company_division','user_company_department.division_id = user_company_division.division_id');
		        $this->db->join('annual_manpower_planning_status','annual_manpower_planning_status.annual_manpower_planning_status_id=annual_manpower_planning.annual_manpower_planning_status_id');        
		        $this->db->join('user_company','user_company.company_id=user.company_id');
		        $this->db->where('annual_manpower_planning_id', $record_id);
		        $result = $this->db->get('annual_manpower_planning');

                $recepients = array();
                $request = $result->row_array();

                //get approvers via employee approver / position module
                $employee_approver_list = $this->system->get_approvers_emails_and_condition($request['employee_id'],$this->module_id);
                foreach( $employee_approver_list as $employee_approver ){
                	if( $employee_approver['email'] == 1 ){
                		$this->db->or_where('user_id', $employee_approver['approver'] );
                	}
                }

                $this->db->or_where('user_id', $request['employee_id']);
                $result_sql = $this->db->get('user');
                $result = $result_sql->result_array();

                // Load the template.            
                $this->load->model('template');

                $template = $this->template->get_module_template($this->module_id, 'amp_status_email');

                foreach ($result as $row) {

                	if( $request['user_id'] != $row['user_id'] ){

	                    $request['approver_user'] = $row['salutation']." ".$row['lastname'];
	                    $recepients = $row['email'];
	                    $request['here']=base_url().'recruitment/annual_manpower_planning/detail/'.$request['annual_manpower_planning_id'];
	                    $message = $this->template->prep_message($template['body'], $request);

	                    $this->template->queue($recepients, '', $template['subject'], $message);
	                
                	}
                }

                $template = $this->template->get_module_template($this->module_id, 'amp_own_status_email');
				$request['approver_user'] = $request['salutation']." ".$request['lastname'];
                $recepients = $request['email'];
                $request['here']=base_url().'recruitment/annual_manpower_planning/detail/'.$request['annual_manpower_planning_id'];
                $message = $this->template->prep_message($template['body'], $request);

                $this->template->queue($recepients, '', $template['subject'], $message);


            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
    }

	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}

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
		$this->db->select('annual_manpower_planning.department_id',false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

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
			$this->db->select('annual_manpower_planning.department_id',false);
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
			$response->last_query = $this->db->last_query();

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

									if ($detail['name'] == 'annual_manpower_planning_status_id'){	
										$row[$detail['name']] = $row['amp_status'];
										
									}
									$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
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
	// END - default module functions
	
	// START custom module funtions
	
	function get_year(){
		$cur_year = date('Y');
		$buf_year = $cur_year + 5;


		$year = "";
		if ($this->input->post('annual_manpower_planning_id') != -1){
			$this->db->where('annual_manpower_planning_id',$this->input->post('annual_manpower_planning_id'));
	    	$result = $this->db->get('annual_manpower_planning');			
	    	$year = $result->row()->year; 
		}

		if ($this->input->post('annual_manpower_planning_id') == -1){
			$created_by = '<input id="employee_id" class="input-text" style="width:75%" type="text" name="employee_id" readonly="readonly" value="'.$this->userinfo['firstname']. ' ' .$this->userinfo['lastname'].'">';
       		$html_employee = '<input id="created_by" class="input-text" type="hidden" name="created_by" value="'.$this->userinfo['user_id'].'">';

		}else{
			$record = $this->db->get_where($this->module_table , array('annual_manpower_planning_id' => $this->input->post('annual_manpower_planning_id')))->row();
	
			$create = $this->system->get_employee($record->created_by);
			
			$created_by = '<input id="employee_id" class="input-text" style="width:75%" type="text" name="employee_id" readonly="readonly" value="'.$create['firstname']. ' ' .$create['lastname'].'">';
       		$html_employee = '<input id="created_by" class="input-text" type="hidden" name="created_by" value="'.$record->created_by.'">';
		}

		$html = '<select id="year" name="year"><option value="">Select…</option>';
			for ($i = $cur_year;$i <= $buf_year; $i++){
				$html .= '<option value="'.$i.'" '.($year == $i ? "SELECTED" : "").'>'.$i.'</option>';				
			}
		$html .= '</select>';

		

		 //$data['html'] = $html;

		$response['year'] = $html;
		$response['employee'] = $html_employee;
		$response['created_by'] = $created_by;
		$data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

	}

	function get_employee_per_dept(){
		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		$this->db->where('deleted',0);
		$this->db->order_by('annual_manpower_planning_remarks.sequence','asc');			
		$remarks = $this->db->get('annual_manpower_planning_remarks');

    	$this->db->select('user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
    	$this->db->join('user_position','user.position_id = user_position.position_id');
    	$this->db->where('user_company_department.department_id',$this->input->post('department_id'));
    	$position = $this->db->get('user');
    	$ctr = 1;

		$html = '<table id="module-access" style="width:100%" class="default-table boxtype">
		    <colgroup width="15%"></colgroup>
		    <thead>
			    <tr>
			        <th style="vertical-align:middle">Employee / Position</th>';
		            	// Display header
			            foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';
			    $html .= '<th class="even"><span>&nbsp;</span></th></tr>
		    </thead>
		    <tbody class="structure_list">';

		foreach($position->result() as $row):
			$html .= '<tr id="'.$row->user_id .'" class="'.($ctr % 2 == 0 ? "even" : "odd").'">
				<input type="hidden" name="user_id[]" value="'.$row->user_id.'">
				<input type="hidden" name="position_id[]" value="'.$row->position_id.'">
		        <th class="text-left" style="border-top: none"><span><span>'.$row->name.'</span><br /><span style="padding-left:10px;float:left">-'.$row->position.'</span></span></th>';
		        foreach( $list_month as $index => $month):
		            $html .= '<td class="text-center '.($index % 2 == 0 ? "even" : "odd").' " style="vertical-align:middle" axis="'.strtolower($month).'">
						<select class="manpower_setup" name="remarks_'.strtolower($month).'[]" style="width:53px">
							<option value="">Select</option>';
							foreach ($remarks->result() as $row_remarks):
								$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'">'.$row_remarks->remarks.'</option>';
							endforeach;
						$html .= '</select>
						<div style="padding-top:5px"><input type="text" name="hiring_'.strtolower($month).'[]" class="hiring" style="width:53px;display:none"></input><div>		            	
		            </td>';
		        endforeach;
			$html .= '<td style="vertical-align:middle"><span>&nbsp;</span></td></tr>'; 
		$ctr++; 
		endforeach;

		    $html .= '</tbody>
		</table>
		<div class="spacer"></div>';

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		
	}


	function get_employee_per_dept_edit(){
		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		$this->db->where('deleted',0);
		$this->db->order_by('annual_manpower_planning_remarks.sequence','asc');			
		$remarks = $this->db->get('annual_manpower_planning_remarks');

    	$this->db->select('annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
    	$this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');    	    	
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
    	$this->db->join('user_position','user.position_id = user_position.position_id');
    	$this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$this->input->post('annual_manpower_planning_id'));
    	$this->db->order_by('annual_manpower_planning_details_id','ASC');
    	$position = $this->db->get('annual_manpower_planning_details');

    	$ctr = 1;

		$html = '<table id="module-access" style="width:100%" class="default-table boxtype">
		    <colgroup width="15%"></colgroup>
		    <thead>
			    <tr>
			        <th style="vertical-align:middle">Employee / Position</th>';
		            	// Display header
			            foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';
			    $html .= '<th class="even"><span>&nbsp;</span></th></tr>
		    </thead>
		    <tbody class="structure_list">';

		foreach($position->result() as $row):
			$html .= '<tr id="'.$row->user_id .'" class="'.($ctr % 2 == 0 ? "even" : "odd").'">
				<input type="hidden" name="user_id[]" value="'.$row->user_id.'">
				<input type="hidden" name="position_id[]" value="'.$row->position_id.'">
		        <th class="text-left" style="border-top: none"><span><span>'.$row->name.'</span><br /><span style="padding-left:10px;float:left">-'.$row->position.'</span></span></th>';
		        foreach( $list_month as $index => $month):
		        	$monthsmall = strtolower($month);
		        	$arr_val = explode("||",$row->$monthsmall);
		            $html .= '<td class="text-center '.($index % 2 == 0 ? "even" : "odd").' " style="vertical-align:middle" axis="'.strtolower($month).'">
						<select class="manpower_setup" name="remarks_'.strtolower($month).'[]" style="width:53px">
							<option value="">Select</option>';
							foreach ($remarks->result() as $row_remarks):
								$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'" '.($arr_val[0] == $row_remarks->annual_manpower_planning_remarks_id ? "SELECTED" : "").'>'.$row_remarks->remarks.'</option>';
							endforeach;
						$html .= '</select>
						<div style="padding-top:5px"><input type="text" name="hiring_'.strtolower($month).'[]" class="hiring" style="width:53px; '.($arr_val[1] <> "" ? "" : "display:none").'" value="'.$arr_val[1].'"></input><div>		            	
		            </td>';
		        endforeach;
			$html .= '<td style="vertical-align:middle"><span>&nbsp;</span></td></tr>'; 
		$ctr++; 
		endforeach;

    	$this->db->select('annual_manpower_planning_position.annual_manpower_planning_position_id,position,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
    	$this->db->join('annual_manpower_planning','annual_manpower_planning_details.annual_manpower_planning_id = annual_manpower_planning.annual_manpower_planning_id');
    	$this->db->join('annual_manpower_planning_position','annual_manpower_planning_details.annual_manpower_planning_position_id = annual_manpower_planning_position.annual_manpower_planning_position_id');
    	$this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$this->input->post('annual_manpower_planning_id'));
    	$this->db->order_by('annual_manpower_planning_details_id','ASC');
    	$position = $this->db->get('annual_manpower_planning_details');

    	if ($position){
			foreach($position->result() as $row):
				$html .= '<tr id="'.$row->annual_manpower_planning_position_id .'" class="'.($ctr % 2 == 0 ? "even" : "odd").'">
			        <th class="text-left" style="border-top: none;vertical-align:middle"><input type="text" value="'.$row->position.'" name="position[]"></th>';
			        foreach( $list_month as $index => $month):
			        	$monthsmall = strtolower($month);
			        	$arr_val = explode("||",$row->$monthsmall);
			            $html .= '<td class="text-center '.($index % 2 == 0 ? "even" : "odd").' " style="vertical-align:middle;text-align:center" axis="'.strtolower($month).'">
			            	<span>Hire</span>
							<div style="padding-top:5px"><input type="text" name="hiring_'.strtolower($month).'[]" class="hiring" style="width:50px;" value="'.$arr_val[1].'"></input><div>		            	
			            </td>';
			        endforeach;
				$html .= '<td style="vertical-align:middle"><a class="icon-button icon-16-delete delete-single" href="javascript:void(0)" module_link="recruitment/annual_manpower_planning" container="jqgridcontainer" tooltip="Delete"></a></td></tr>'; 
			$ctr++; 
			endforeach;
    	}

		    $html .= '</tbody>
		</table>
		<div class="spacer"></div>';

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		
	}	



	function get_position_per_dept(){

		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		$this->db->where('deleted',0);
		$this->db->order_by('annual_manpower_planning_remarks.sequence','asc');			
		$remarks = $this->db->get('annual_manpower_planning_remarks');

		$this->db->select('user.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
    	$this->db->join('user_position','user.position_id = user_position.position_id');
    	$this->db->join('employee','employee.user_id = user.user_id');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
    	$this->db->where('user_company_department.department_id',$this->input->post('department_id'));
    	$this->db->where('user.inactive',0);
    	$this->db->where('user.deleted',0);
    	$this->db->order_by('user_rank.rank_index','DESC');
    	$this->db->group_by('position_id');
    	$position = $this->db->get('user');


    	$ctr = 1;

		$html = '<table id="module-access" style="width:100%" class="default-table boxtype">
		    <colgroup width="15%"></colgroup>
		    <thead>
		    	<tr>
		    		<th colspan="16" style="text-align:left;">Positions with Incumbent</th>
		    	</tr>
			    <tr>
			        <th style="vertical-align:middle">Employees</th><th class="action-name font-smaller odd"><div>Rank</div></th>';
		            	// Display header
			            foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';
			    $html .= '<th class="action-name font-smaller even"><div>Budget</div></th><th class="even"><span></span></th></tr>
		    </thead>
		    <tbody class="structure_list">';

		    foreach($position->result() as $position_row){

	    		$this->db->select('user.user_id, employee.employed_date, employment_status.employment_status, user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname, " ", middleinitial) name, user_rank.job_rank, employee.rank_id',false);
		    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
		    	$this->db->join('user_position','user.position_id = user_position.position_id');
		    	$this->db->join('employee','employee.user_id = user.user_id','left');
		    	$this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');
		    	$this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id','left');
		    	$this->db->where('user_company_department.department_id',$this->input->post('department_id'));
		    	$this->db->where('user_position.position_id',$position_row->position_id);
		    	$this->db->where('user.inactive',0);
		    	$this->db->where('user.deleted',0);
		    	$user = $this->db->get('user');

		    	$incumbent_count = $user->num_rows();

			    $html .= '<tr>
			        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="16">
			        	<span>
			        		<span>'.$position_row->position.' ( '.$incumbent_count.' ) </span>
			        	</span>
					</th>
			    </tr>';

			    	foreach($user->result() as $user_row){

			    		$tooltip = '<table>
							<tr>
								<td style=\'text-align:right; font-weight:bold;\'>Employment Status</td>
								<td> : </td>
								<td style=\'text-align:left;\'>'.$user_row->employment_status.'</td>
							</tr>
							<tr>
								<td style=\'text-align:right; font-weight:bold;\'>Hired Date</td>
								<td> : </td>
								<td style=\'text-align:left;\'>'.date('F d, Y',strtotime($user_row->employed_date)).'</td>
							</tr>
						</table>';

					    $html .= '<tr id="'.$user_row->user_id .'" class="'.($ctr % 2 == 0 ? "even" : "odd").' position_with_incumbent">
					    	<input type="hidden" name="user_id[]" value="'.$user_row->user_id.'">
							<input type="hidden" name="position_id[]" value="'.$user_row->position_id.'">

					        <th style="border-top: none;" class="text-left">
					        	<ul type="disc" style="font-size:11px; padding-left:20px;">
					        		<li><a href="javascript:void(0)" tooltip="'.$tooltip.'">&bull; '.$user_row->name.'</a></li>
					        	</ul>
					        </th>';
					    $html .= "<td><input type='hidden' name='rank_id[]' value='".$user_row->rank_id."'>
					    		<input type='text' readonly='readonly' value='".$user_row->job_rank."' ></td>";

					        foreach( $list_month as $index => $month){

								// if( $user_row->user_id != $this->userinfo['user_id'] ){

									$html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
										<select style="width:60px" name="remarks_'.strtolower($month).'[]" class="manpower_setup">
											<option value="">Select</option>';

											foreach ($remarks->result() as $row_remarks):
												$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'">'.$row_remarks->remarks.'</option>';
											endforeach;

									$html .= '</select>
										</td>';
							}
						$html .= "<td><input type='text' name='budget[]' class='budget' value='' style='width:60px'  ></td>";
					    $html .= '<td>&nbsp;</td></tr>';

					}
					


				$ctr++; 
			}



		$html .= '</tbody>
		</table>
		<div class="spacer"></div>';

		$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function get_position_per_dept_edit(){

		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		$this->db->where('deleted',0);
		$this->db->order_by('annual_manpower_planning_remarks.sequence','asc');		
		$remarks = $this->db->get('annual_manpower_planning_remarks');

    	$this->db->select('annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
    	$this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');    	    	
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
    	$this->db->join('user_position','user.position_id = user_position.position_id');
    	$this->db->join('employee','employee.user_id = user.user_id');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
    	$this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$this->input->post('annual_manpower_planning_id'));
    	$this->db->order_by('user_rank.rank_index','DESC');
    	$this->db->group_by('position_id');
    	$position = $this->db->get('annual_manpower_planning_details');

    	$ctr = 1;


    	$html = '<table id="module-access" style="width:100%" class="default-table boxtype">
		    <colgroup width="15%"></colgroup>
		    <thead>
			    <tr>
			        <th style="vertical-align:middle">Employee / Position</th>';
		            	// Display header
			            foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';
			    $html .= '<th class="even"><span>&nbsp;</span></th></tr>
		    </thead>
		    <tbody class="structure_list">';

		    foreach($position->result() as $position_row){

			    $html .= '<tr>



			        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14">
			        	<span>
			        		<span>'.$position_row->position.'</span>
			        	</span>
					</th>

			    </tr>';


			    	$this->db->select('employee.employed_date, employment_status.employment_status,annual_manpower_planning_details.disapproved, annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname, " ", middleinitial) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
			    	$this->db->join('user','user.user_id = annual_manpower_planning_details.user_id','left');
			    	$this->db->join('employee','employee.user_id = user.user_id','left');	    	
			    	$this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');             
			    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
			    	$this->db->join('user_position','user.position_id = user_position.position_id');
			    	$this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$this->input->post('annual_manpower_planning_id'));
			    	$this->db->where('user_position.position_id',$position_row->position_id);
			    	$this->db->order_by('annual_manpower_planning_details_id','ASC');
			    	$user = $this->db->get('annual_manpower_planning_details');


			    	foreach($user->result() as $user_row){

			    		$red="";

		    			if( $user_row->disapproved == 1 ){
							$red='red';
						}

						$tooltip = "<table>
							<tr>
								<td>Employment Status</td>
								<td> : ".$user_row->employment_status."</td>
								<td></td>
							</tr>
							<tr>
								<td>Hired Date</td>
								<td> : ".date('F d, Y',strtotime($user_row->employed_date))."</td>
								<td></td>
							</tr>
						</table>";

					    $html .= '<tr id="'.$user_row->user_id .'" class="'.($ctr % 2 == 0 ? "even" : "odd").' position_with_incumbent">
					    	<input type="hidden" name="user_id[]" value="'.$user_row->user_id.'">
							<input type="hidden" name="position_id[]" value="'.$user_row->position_id.'">

					        <th style="border-top: none;" class="text-left">
					        	<ul type="disc" style="font-size:11px; padding-left:20px;">
					        		<li><a href="javascript:void(0)" tooltip="'.$tooltip.'"><span class="'.$red.'">&bull; '.$user_row->name.'</span></a></li>
					        	</ul>
					        </th>';


					        foreach( $list_month as $index => $month){
					        	$monthsmall = strtolower($month);

					        	// if( $user_row->user_id != $this->userinfo['user_id'] ){

						        $html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
									<select style="width:60px" name="remarks_'.strtolower($month).'[]" class="manpower_setup">
										<option value="">Select</option>';

										foreach ($remarks->result() as $row_remarks):
											$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'" '.($user_row->$monthsmall == $row_remarks->annual_manpower_planning_remarks_id ? "SELECTED" : "").'>'.$row_remarks->remarks.'</option>';
										endforeach;

								$html .= '</select>
									</td>';

								// }
								/*else{


									$html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
									<select style="width:60px" disabled="" name="remarks_'.strtolower($month).'[]" class="manpower_setup">
										<option value="">Select</option>';

										foreach ($remarks->result() as $row_remarks):
											$html .= '<option value="'.$row_remarks->annual_manpower_planning_remarks_id.'" '.($user_row->$monthsmall == $row_remarks->annual_manpower_planning_remarks_id ? "SELECTED" : "").'>'.$row_remarks->remarks.'</option>';
										endforeach;

								$html .= '</select>
									</td>';

								}*/


							}

					    $html .= '<td>&nbsp;</td></tr>';

					}

				$ctr++; 
			}



		$html .= '</tbody>
		</table>
		<div class="spacer"></div>';


        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function _get_reporting_to_position_hierarchy( $position_id = 0, $subordinates = array() ){

		$position_hierarchy = $this->db->get_where('user_position',array( 'reporting_to'=> $position_id, 'deleted' => 0 ));

		if( $position_hierarchy->num_rows() > 0 ){

			$subordinates = array_merge( $subordinates, $position_hierarchy->result_array() );

			foreach( $position_hierarchy->result_array() as $position_hierarchy_record ){

				$subordinates = $this->_get_reporting_to_position_hierarchy( $position_hierarchy_record['position_id'], $subordinates );

			}

		}

		return $subordinates;

	}

	function get_department_list(){

		$division_id = 0;
		$html = "";


		if( $this->input->post('division_id') ){
			$division_id = $this->input->post('division_id');

			$this->db->where('division_id',$division_id);

		}

		
		$this->db->where('deleted',0);
		$department_result = $this->db->get('user_company_department');


		if( $department_result->num_rows() > 0 ){

			$html .= "<select id='department_id' name='department_id' style='' >";
			$html .= "<option value=''></option>";

			foreach( $department_result->result() as $department_info ){

				$html .= "<option value='".$department_info->department_id."'>".$department_info->department."</option>";

			}

			$html .= "</select>";

		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}


	function get_head()
	{
		if (IS_AJAX) {
			$id = $this->input->post('id');
			$type = $this->input->post('type');
			
			if ($id > 0) {
				$this->db->where($type.'_id', $id);
				$result   = $this->db->get('user_company_'.$type);

				if ($result && $result->num_rows() > 0) {
					$record = $result->row();

					switch ($type) {
						case 'division':
							$head_id = $record->division_manager_id;
					
							break;
						case 'department':
							$head_id = $record->dm_user_id;

							break;
					}
				
					$head = $this->system->get_employee($head_id);
					
					$response['head'] = $head['firstname'].' '.$head['lastname'];
					$response['head_id'] = $head['user_id'];
					$response['type'] = $type;

				}

			} else {
				$response = array();
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_previous_headcount(){

		$previous_headcount = array();

		$position_hierarchy = $this->_get_reporting_to_position_hierarchy($this->userinfo['position_id']);

		if( count($position_hierarchy) > 0 ){

			foreach( $position_hierarchy as $position_hierarchy_record ){

				$previous_amp = 0;

				if( $this->input->post('year') ){

					//$year = date('Y',strtotime('- 1 year',strtotime($this->input->post('year'))));
					$year = $this->input->post('year') - 1;

					$this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
					$this->db->where('annual_manpower_planning_position.position_id',$position_hierarchy_record['position_id']);
					$this->db->where('annual_manpower_planning_position.type',2);
					$this->db->where('annual_manpower_planning.year',$year);
					$this->db->where('annual_manpower_planning.department_id',$this->input->post('department_id'));
					$this->db->where('annual_manpower_planning.annual_manpower_planning_status_id',3);
					$previous_amp_result = $this->db->get('annual_manpower_planning_position');

					if( $previous_amp_result->num_rows() > 0 ){

						$previous_amp_record = $previous_amp_result->row_array();

						$previous_amp = $previous_amp_record['total'];

						$data = array(
							'position_id' => $position_hierarchy_record['position_id'],
							'previous_amp' => $previous_amp
						);

					}
					else{
						$data = array(
							'position_id' => $position_hierarchy_record['position_id'],
							'previous_amp' => $previous_amp
						);
					}

				}

				$previous_headcount[] = $data;
			}
		}

		$data['json'] = $previous_headcount;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}


	function get_new_headcount(){

		$record_id = $this->input->post('record_id');

		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		if( $record_id == '-1' ){

				$html .= '<table class="default-table boxtype" style="width:100%" id="module-new-headcount">
		        <colgroup width="15%"></colgroup>
		        <thead>
		            <tr class="">
		                <th style="text-align:left;" colspan="16">New Job</th>
		            </tr>
		            <tr class="">
		                <th style="vertical-align:middle">&nbsp;</th>
		                <th class="action-name font-smaller even"><div>Jan</div></th>
		                <th class="action-name font-smaller odd"><div>Feb</div></th>
		                <th class="action-name font-smaller even"><div>Mar</div></th>
		                <th class="action-name font-smaller odd"><div>Apr</div></th>
		                <th class="action-name font-smaller even"><div>May</div></th>
		                <th class="action-name font-smaller odd"><div>Jun</div></th>
		                <th class="action-name font-smaller even"><div>Jul</div></th>
		                <th class="action-name font-smaller odd"><div>Aug</div></th>
		                <th class="action-name font-smaller even"><div>Sep</div></th>
		                <th class="action-name font-smaller odd"><div>Oct</div></th>
		                <th class="action-name font-smaller even"><div>Nov</div></th>
		                <th class="action-name font-smaller odd"><div>Dec</div></th>
		                <th class="action-name font-smaller even"><span>Total</span></th>
		                <th class="action-name font-smaller odd"><div></div></th>
		                <th class="action-name font-smaller even"><span>&nbsp;</span></th>
		            </tr>
		        </thead>';

		        $html .= '<tbody class="new_headcount_position_empty" ><tr><td style="text-align:center; font-weight:bold;" colspan="17">No new job added</td></tr></tbody>';

		    $html .= '</table>';

		}
		else{

			$new_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $record_id, 'type' => 1, 'deleted' => 0 ) );

			$html .= '<table class="default-table boxtype" style="width:100%" id="module-new-headcount">
		        <colgroup width="15%"></colgroup>
		        <thead>
		            <tr class="">
		                <th style="text-align:left;" colspan="16">New Job</th>
		            </tr>
		            <tr class="">
		                <th style="vertical-align:middle">&nbsp;</th>
		                <th class="action-name font-smaller even"><div>Jan</div></th>
		                <th class="action-name font-smaller odd"><div>Feb</div></th>
		                <th class="action-name font-smaller even"><div>Mar</div></th>
		                <th class="action-name font-smaller odd"><div>Apr</div></th>
		                <th class="action-name font-smaller even"><div>May</div></th>
		                <th class="action-name font-smaller odd"><div>Jun</div></th>
		                <th class="action-name font-smaller even"><div>Jul</div></th>
		                <th class="action-name font-smaller odd"><div>Aug</div></th>
		                <th class="action-name font-smaller even"><div>Sep</div></th>
		                <th class="action-name font-smaller odd"><div>Oct</div></th>
		                <th class="action-name font-smaller even"><div>Nov</div></th>
		                <th class="action-name font-smaller odd"><div>Dec</div></th>
		                <th class="action-name font-smaller even"><span>Total</span></th>
		                <th class="action-name font-smaller odd"><div></div></th>
		                <th class="action-name font-smaller even"><span>&nbsp;</span></th>
		            </tr>
		        </thead>';

		        if( $new_position->num_rows() == 0 ){
		        	$html .= '<tbody class="new_headcount_position_empty" ><tr><td style="text-align:center; font-weight:bold;" colspan="17">No new job added</td></tr></tbody>';
		    	}
		    	else{

		    		foreach( $new_position->result_array() as $new_position_record ){

		    			$red="";

		    			if( $new_position_record['disapproved'] == 1 ){
							$red='red';
						}

			    		$html .= '
					        <tbody>
					            <tr>
					                <th style="vertical-align:middle; text-align:center; border-top: none; padding: 10px;" class="text-left even"><span class="'.$red.'">Position:</span></th>
					                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14"><input type="text" value="'.$new_position_record['position'].'" name="new_position_name[]" /></th>
					                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even"><a class="icon-button icon-16-delete delete-single delete_new_position" href="javascript:void(0)" module_link="recruitment/annual_manpower_planning" container="jqgridcontainer" tooltip="Delete"></a></th>
					            </tr>
					            <tr>
					                <td>Headcount</td>';

					    foreach( $list_month as $index => $month){

					    	$html .= '<td style="text-align:center"><input type="text" style="width:20px" value="'.$new_position_record[strtolower($month)].'" name="new_job_headcount_'.strtolower($month).'[]" /></td>';

					    }
					    
					    
					    $html .= '<td style="text-align:center"><input type="text" style="width:20px" value="'.$new_position_record['total'].'" name="new_job_headcount_total[]" /></td>
					                <td>&nbsp;</td>
					                <td>&nbsp;</td>
					            </tr>
					            <tr>
					                <td style="vertical-align:top;">Remarks</td>
					                <td colspan="16"><textarea name="new_position_remarks[]">'.$new_position_record['remarks'].'</textarea></td>
					            </tr>
					        </tbody>';

				    }

		    	}

		    $html .= '</table>';

		}

	    $data['html'] = $html;
	    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
		
	}

	function get_existing_headcount(){

		$record_id = $this->input->post('record_id');
		$department_id = $this->input->post('department_id');
		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");


   		// $existing_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $record_id, 'type' => 2, 'deleted' => 0 ) );

		// $this->db->join('user','user.employee_id = annual_manpower_planning.employee_id','left');
		// $this->db->where('annual_manpower_planning.annual_manpower_planning_id',$record_id);
		// $annual_manpower_planning_head_info = $this->db->get('annual_manpower_planning')->row();

		$this->db->where('department_id', $department_id);
		$department = $this->db->get('user_company_department')->row();

		$position_ids = $department->position_ids; 

		$where = "position_id IN (" .$position_ids.")";
		$this->db->where($where);
		$positions =  $this->db->get('user_position');


		$html = '
		<table class="default-table boxtype" style="width:100%" id="module-exist-headcount">
	        <colgroup width="15%"></colgroup>
	        <thead>
	            <tr class="">
	                <th style="text-align:left;" colspan="2" >Existing Job</th>
	                <th style="text-align:center;" colspan="15">To Hire</th>
	            </tr>
	            <tr class="">
	                <th style="vertical-align:middle"><small>&nbsp;</small></th>
	                <th class="action-name font-smaller even"><div>Incumbent</div></th>';

			foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';

			$html .='
			                <th class="action-name font-smaller even"><span>Approved HC</span></th>
			                <th class="action-name font-smaller odd"><span>Budget</span></th>
			         
			                <th class="action-name font-smaller odd"><span><small>&nbsp;</small></span></th>
			            </tr>
			        </thead>';

			 // $this->_get_reporting_to_position_hierarchy($annual_manpower_planning_head_info->position_id);

			if( $positions && $positions->num_rows() > 0 ){
				$position_hierarchy = $positions->result_array();
				foreach( $position_hierarchy as $position_hierarchy_record ){
					$this->db->where('department_id',$department_id);
					$this->db->where('deleted',0);
					$this->db->where('inactive',0);
	    			$this->db->where('position_id',$position_hierarchy_record['position_id']);
	    			$incumbent = $this->db->get('user');
	    			 
	    			$incumbent_count = ($incumbent && $incumbent->num_rows() > 0) ? $incumbent->num_rows()  : 0;

					$html .='
					        <tbody>
					            <tr>
					                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="17">'.$position_hierarchy_record['position'].'
					                <input type="hidden" name="existing_position[]" class="existing_position_id" value="'.$position_hierarchy_record['position_id'].'" />
					                </th>
					            </tr>
					            <tr>
					                <th style="border-top:none;">Headcount</th>
					                <td style="text-align:center"><input type="text" style="width:30px" readonly="" class="existing_job_headcount_previous" name="existing_job_headcount_previous[]" value="'.$incumbent_count.'" /></td>';

					foreach ( $list_month as $index => $month ){

					    $html .= '<td style="text-align:center"><input type="text" style="width:30px" class="existing_headcount_month_value" value="0" name="existing_job_headcount_'.strtolower($month).'[]" /></td>';

					}

					$html .= '<td style="text-align:center"><input type="text" style="width:30px" value="'.$incumbent_count.'" readonly="" class="existing_headcount_month_total" name="existing_job_headcount_total[]" /></td>
					          <td style="text-align:center"><input type="text" style="width:60px" class="" name="existing_job_budget[]" /></td>
					                <td>&nbsp;</td>
					                <td>&nbsp;</td>
					            </tr>
					        </tbody>';

				        
				}

			}
			else{

				$html .= '<tbody class="existing_headcount_position_empty" ><tr><td style="text-align:center; font-weight:bold;" colspan="17">No existing job available</td></tr></tbody>';


			}

			$html .='
			    </table>';   


    	$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function get_headcount(){

		$record_id = $this->input->post('record_id');

		if( $record_id == '-1' ){

			$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

			$html = '<table id="module-headcount" style="width:100%; display:none;" class="default-table boxtype">
		    <colgroup width="15%"></colgroup>
		    <thead>
			    <tr>
			        <th style="vertical-align:middle">Position</th>';
		            	// Display header
			            foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';
			    $html .= '<th class="even"><span>&nbsp;</span></th></tr>
		    </thead>
		    <tbody class="existing_job_headcount" style="display:none;">

		    	<tr>
			        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14">
			        	<span>
			        		<span>Existing Job</span>
			        	</span>
					</th>
			    </tr>
			    <tr>
			    	<td style="text-align:center; display:none;" class="no_existing_job_found" colspan="14">
			    		<span style="text-align:center;">No Existing Job Found</span>
			    	</td>
		    	</tr>
		    	</tbody>
		    	<tbody class="new_job_headcount" style="display:none;">
		    	<tr>
			        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14">
			        	<span>
			        		<span>New Job</span>
			        	</span>
					</th>
			    </tr>
			    <tr>
			    	<td style="text-align:center; display:none;" class="no_new_job_found" colspan="14">
			    		<span style="text-align:center;">No New Job Found</span>
			    	</td>
		    	</tr>
		    </tbody>
		</table>
		<div class="spacer"></div>';


		}
		else{

			$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

			$existing_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $record_id, 'type' => 2, 'deleted' => 0 ) );
			$new_position = $this->db->get_where('annual_manpower_planning_position', array('annual_manpower_planning_id' => $record_id, 'type' => 1, 'deleted' => 0 ) );
			$display = "";


			if( $existing_position->num_rows == 0 && $new_position->num_rows == 0 ){
				$display = "display:none;";
			}


			$this->db->select('user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
	    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
	    	$this->db->join('user_position','user.position_id = user_position.position_id');
	    	$this->db->where('user_company_department.department_id',$this->input->post('department_id'));
	    	$this->db->where('user.inactive',0);
	    	$this->db->where('user.deleted',0);
	    	$this->db->group_by('position_id');
	    	$position = $this->db->get('user');


			$html = '<table id="module-headcount" style="width:100%; '.$display.'" class="default-table boxtype">
		    <colgroup width="15%"></colgroup>
		     <thead>
			    <tr>
			        <th style="vertical-align:middle">Position</th>';
		            	// Display header
			            foreach ( $list_month as $index => $month ) $html .= '<th class="action-name font-smaller '.($index % 2 == 0 ? "even" : "odd").'"><div>'.($month).'</div></th>';
			    $html .= '<th class="even"><span>&nbsp;</span></th></tr>
		    </thead>
		    <tbody class="existing_job_headcount" style="'.$display.'">

		    	<tr>
			        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14">
			        	<span>
			        		<span>Existing Job</span>
			        	</span>
					</th>
			    </tr>';

		    	if( $existing_position->num_rows() > 0 ){

		    		$existing_position_list = $existing_position->result_array();

		    		foreach( $existing_position_list as $key => $val ){

						$html .= '<tr class="existing_job_form">
						    		<th style="border-top: none; text-align:left;">
						    			<label>Position</label><br />
						    			<select name="existing_position['.$key.']" style="width:90px;">
						    				<option value="">Select</option>';

						    				foreach($position->result() as $position_row){
						    					$html .= '<option value="'.$position_row->position_id.'" '.($position_row->position_id == $existing_position_list[$key]['position_id'] ? "selected" : "").' >'.$position_row->position.'</option>';
						    				}

						$html .= '</select>
						    		</th>';


						    		foreach( $list_month as $index => $month){
							        	$monthsmall = strtolower($month);
								        $html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
												<label>Headcount:</label><input type="text" name="existing_job_headcount_'.strtolower($month).'['.$key.']" value="'.$existing_position_list[$key][strtolower($month)].'" style="width:30px;" />
											</td>';
									}


						    	$html .= '<td style="vertical-align:middle;"><a class="icon-button icon-16-delete delete-single delete_existing_position" href="javascript:void(0)" module_link="recruitment/annual_manpower_planning" container="jqgridcontainer" tooltip="Delete"></a></td></tr>';

		    		}

		    	}
		    	else{

		    		$html .= '<tr>
			    	<td style="text-align:center; display:none;" class="no_existing_job_found" colspan="14">
			    		<span style="text-align:center;">No Existing Job Found</span>
			    	</td>
		    	</tr>';
		    	}

		    	$html .= '</tbody>
		    	<tbody class="new_job_headcount" style="'.$display.'">
		    	<tr>
			        <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14">
			        	<span>
			        		<span>New Job</span>
			        	</span>
					</th>
			    </tr>';

		    	
		    	if( $new_position->num_rows() > 0 ){

		    		$new_position_list = $new_position->result_array();

		    		foreach( $new_position_list as $key => $val ){

						$html .= '<tr class="new_job_form">
				    		<th style="border-top: none; text-align:left;">
				    			<label>Position</label><br />
				    			<input type="text" name="new_position_name['.$key.']" style="width:90px;" value="'.$new_position_list[$key]['position'].'" />
				    			<br />
				    			<label>Remarks</label><br />
				    			<textarea style="width:90px;" name="new_position_remarks['.$key.']">'.$new_position_list[$key]['remarks'].'</textarea><br />
				    		</th>';
				    		
				    		foreach( $list_month as $index => $month){
					        	$monthsmall = strtolower($month);
						        $html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
										<label>Headcount:</label><input type="text" name="new_job_headcount_'.strtolower($month).'['.$key.']" style="width:30px;" value="'.$new_position_list[$key][strtolower($month)].'" />
									</td>';
							}

				    	$html .= '<td style="vertical-align:middle;"><a class="icon-button icon-16-delete delete-single delete_new_position" href="javascript:void(0)" module_link="recruitment/annual_manpower_planning" container="jqgridcontainer" tooltip="Delete"></a></td></tr>';

		    		}

		    	}
		    	else{

		    		$html .= '<tr>
			    	<td style="text-align:center; display:none;" class="no_new_job_found" colspan="14">
			    		<span style="text-align:center;">No New Job Found</span>
			    	</td>
		    	</tr>';

		    	}

		    	

		    $html .= '</tbody>
		</table>
		<div class="spacer"></div>';

		}

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function get_form_existing_position(){

		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		$this->db->select('user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name',false);
    	$this->db->join('user_company_department','user.department_id = user_company_department.department_id');
    	$this->db->join('user_position','user.position_id = user_position.position_id');
    	$this->db->where('user.inactive',0);
	    $this->db->where('user.deleted',0);
    	$this->db->where('user_company_department.department_id',$this->input->post('department_id'));
    	$this->db->group_by('position_id');
    	$position = $this->db->get('user');

		$html = '<tr class="existing_job_form">
		    		<th style="border-top: none; text-align:left;">
		    			<label>Position</label><br />
		    			<select name="existing_position[]" style="width:90px;">
		    				<option value="">Select</option>';

		    				foreach($position->result() as $position_row){

		    					$html .= '<option value="'.$position_row->position_id.'">'.$position_row->position.'</option>';

		    				}

		$html .= '</select>
		    		</th>';


		    		foreach( $list_month as $index => $month){
			        	$monthsmall = strtolower($month);
				        $html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
								<label>Headcount:</label><input type="text" name="existing_job_headcount_'.strtolower($month).'[]" style="width:30px;" />
							</td>';
					}


		    	$html .= '<td style="vertical-align:middle;"><a class="icon-button icon-16-delete delete-single delete_existing_position" href="javascript:void(0)" module_link="recruitment/annual_manpower_planning" container="jqgridcontainer" tooltip="Delete"></a></td></tr>';

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function get_form_new_position(){

		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		$html = '<tr class="new_job_form">
		    		<th style="border-top: none; text-align:left;">
		    			<label>Position</label><br />
		    			<input type="text" name="new_position_name[]" style="width:90px;" />
		    			<br />
		    			<label>Remarks</label><br />
		    			<textarea style="width:90px;" name="new_position_remarks[]"></textarea><br />
		    		</th>';
		    		
		    		foreach( $list_month as $index => $month){
			        	$monthsmall = strtolower($month);
				        $html .= '<td axis="'.strtolower($month).'" style="vertical-align:middle; text-align:center;" class="text-center '.($index % 2 == 0 ? "even" : "odd").' ">
								<label>Headcount:</label><input type="text" name="new_job_headcount_'.strtolower($month).'[]" style="width:30px;" />
							</td>';
					}

		    	$html .= '<td style="vertical-align:middle;"><a class="icon-button icon-16-delete delete-single delete_new_position" href="javascript:void(0)" module_link="recruitment/annual_manpower_planning" container="jqgridcontainer" tooltip="Delete"></a></td></tr>';

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}


	function get_form_new_headcount_position(){

		$list_month = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		$html .= '
		        <tbody class="new_headcount_position_row">
		            <tr>
		                <th style="vertical-align:middle; text-align:center; border-top: none; padding: 10px;" class="text-left even">Position:<span class="red font-large">*</span></th>
		                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="14"><input type="text" class="new_headcount_position" name="new_position_name[]" /></th>
		                <th style="vertical-align:middle; border-top: none; padding: 10px;" class="text-left even" colspan="2"><a class="icon-button icon-16-delete delete-single delete_new_headcount_position" href="javascript:void(0)" module_link="recruitment/annual_manpower_planning" container="jqgridcontainer" tooltip="Delete"></a></th>
		            </tr>
		            <tr>
		                <th style="border-top:none;">Headcount</th>';

		    foreach( $list_month as $index => $month){

		    	$html .= '<td style="text-align:center"><input type="text" style="width:20px" class="new_headcount_month_value" value="0" name="new_job_headcount_'.strtolower($month).'[]" /></td>';

		    }
		    
		    
		    $html .= '<td style="text-align:center"><input type="text" style="width:20px" readonly="" class="new_headcount_month_total" value="0" name="new_job_headcount_total[]" /></td>
		    			<td style="text-align:center"><input type="text" style="width:60px"  class="budget" value="0" name="new_job_headcount_budget[]" /></td>
		                <td>&nbsp;</td>
		                <td>&nbsp;</td>
		            </tr>
		            <tr>
		                <th style="vertical-align:top; border-top:none;">Remarks</th>
		                <td colspan="16"><textarea name="new_position_remarks[]"></textarea></td>
		            </tr>
		        </tbody>';

		$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function get_user_info()
	{

		if( $this->input->post('amp_user_type') == 'division_head' ){

			if( $this->input->post('department_id') ){
				$department_id = $this->input->post('department_id');
			}
			else{
				$department_id = 0;
			}

			$this->db->join('user_company','user_company.company_id = user_company_department.company_id','left');
			$this->db->where('user_company_department.department_id',$department_id);
			$department_info = $this->db->get('user_company_department')->row();

			$response['department_id'] = $department_info->department_id;
			$response['department_name'] = $department_info->department;
			$response['company_name'] = $department_info->company;
			$response['company_id'] = $department_info->company_id;
			$dm_user_id = $department_info->dm_user_id;

			$this->db->where('user.user_id',$dm_user_id);
			$department_head_info = $this->db->get('user')->row();

			$response['user_id'] = $department_head_info->user_id;
			$response['lastname'] = $department_head_info->lastname;
			$response['firstname'] = $department_head_info->firstname;

			$this->db->where('division_id',$department_info->division_id);
			$division_info = $this->db->get('user_company_division')->row();

			$response['division_id'] = $division_info->division_id;
			$reponse['division'] = $division_info->division;
			$division_manager_id = $division_info->division_manager_id;	

			$this->db->where('user.user_id',$division_manager_id);
			$this->db->where('user.inactive',0);
	    	$this->db->where('user.deleted',0);
			$division_head_info = $this->db->get('user')->row();

			$response['division_name'] = $division_head_info->lastname.' '.$division_head_info->firstname;
			$response['division_id'] = $division_head_info->user_id;

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

		}
		else{

			$this->db->join('user_company','user_company.company_id = user_company_department.company_id','left');
			$this->db->where('user_company_department.department_id',$this->userinfo['department_id']);
			$department_info = $this->db->get('user_company_department')->row();

			$response['department_id'] = $department_info->department_id;
			$response['department_name'] = $department_info->department;
			$response['company_name'] = $department_info->company;
			$response['company_id'] = $department_info->company_id;
			$dm_user_id = $department_info->dm_user_id;

			$this->db->where('user.user_id',$dm_user_id);
			$department_head_info = $this->db->get('user')->row();

			$response['user_id'] = $department_head_info->user_id;
			$response['lastname'] = $department_head_info->lastname;
			$response['firstname'] = $department_head_info->firstname;

			$this->db->where('division_id',$department_info->division_id);
			$division_info = $this->db->get('user_company_division')->row();

			$response['division_id'] = $division_info->division_id;
			$reponse['division'] = $division_info->division;
			$division_manager_id = $division_info->division_manager_id;	

			$this->db->where('user.user_id',$division_manager_id);
			$this->db->where('user.inactive',0);
	    	$this->db->where('user.deleted',0);
			$division_head_info = $this->db->get('user')->row();

			$response['division_name'] = $division_head_info->lastname.' '.$division_head_info->firstname;
			$response['division_id'] = $division_head_info->user_id;

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

		}
	}

	function get_user_info_edit()
	{
		$annual_manpower_planning_id = $this->input->post('annual_manpower_planning_id');
		$annual_manpower_planning = $this->db->dbprefix('annual_manpower_planning');
		$annual_manpower_header = $this->db->query("SELECT * FROM {$annual_manpower_planning}  WHERE annual_manpower_planning_id = '{$annual_manpower_planning_id}'")->row();

		$this->db->where('user_company_department.department_id',$annual_manpower_header->department_id);
		$department_info = $this->db->get('user_company_department')->row();

		$response['department_id'] = $department_info->department_id;
		$response['department_name'] = $department_info->department;
		$dm_user_id = $department_info->dm_user_id;

		$this->db->where('user.user_id',$dm_user_id);
		$department_head_info = $this->db->get('user')->row();

		$response['user_id'] = $department_head_info->user_id;
		$response['lastname'] = $department_head_info->lastname;
		$response['firstname'] = $department_head_info->firstname;

		$this->db->where('division_id',$department_info->division_id);
		$division_info = $this->db->get('user_company_division')->row();

		$response['division_id'] = $division_info->division_id;
		$reponse['division'] = $division_info->division;
		$division_manager_id = $division_info->division_manager_id;	

		$this->db->where('user.user_id',$division_manager_id);
		$division_head_info = $this->db->get('user')->row();

		$response['division_name'] = $division_head_info->lastname.' '.$division_head_info->firstname;
		$response['division_id'] = $division_head_info->user_id;


		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function get_user_info_detail()
	{
		$annual_manpower_planning_id = $this->input->post('annual_manpower_planning_id');
		$annual_manpower_planning = $this->db->dbprefix('annual_manpower_planning');
		$annual_manpower_header = $this->db->query("SELECT * FROM {$annual_manpower_planning}  WHERE annual_manpower_planning_id = '{$annual_manpower_planning_id}'")->row();

		$department_id = $annual_manpower_header->employee_id;		
		$division_id = $annual_manpower_header->annual_user_division_id;		
		$user = $this->db->dbprefix('user');
		$department_user =$this->db->query("SELECT * FROM {$user}  WHERE user_id = '{$department_id}'")->row();
		$response['department_name'] = $department_user->lastname.', '.$department_user->firstname;
		$response['department_id'] = $department_user->user_id;
		$division_user =$this->db->query("SELECT * FROM {$user}  WHERE user_id = '{$division_id}'")->row();
		$response['division_name'] = $division_user->lastname.', '.$division_user->firstname;
		$response['division_id'] = $division_user->user_id;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function get_amp_user_type(){

		$amp_user_type = "";

		$department_id = $this->userinfo['department_id'];
		$user_id = $this->userinfo['user_id'];

		$user_info = $this->db->get_where('user',array('user_id'=>$user_id))->row();

		$department_info = $this->db->get_where('user_company_department',array('department_id'=>$department_id, 'dm_user_id'=>$user_id ));
		$division_info = $this->db->get_where('user_company_division',array('division_id'=>$user_info->division_id, 'division_manager_id'=>$user_id));


		if( $this->user_access[$this->module_id]['post'] == 1 ){

			$amp_user_type = "hr_admin";

		}
		else{
			if( $division_info->num_rows > 0 ){

				$amp_user_type = "division_head";

			}else{
				if( $department_info->num_rows > 0 ){

					$amp_user_type = "department_head";

				}
			}
		}

		$response['amp_user_type'] = $amp_user_type;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		//if((!$this->superadmin && $this->userinfo['login'] != "superadmin") && $this->userinfo['position_level'] != "Manager"){
			if($module_link == "") $module_link = $this->module_link;
			if($container == "") $container = "jqgridcontainer";

			$actions = '<span class="icon-group">';
	          
	        if ($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['publish']) {

		        if ($record['amp_status'] != "Closed"){
					$active = 'icon-16-active';
				}								
				else{
					$active = 'icon-16-xgreen-orb';
				}

		      	$actions .='<a class="icon-button  '.$active.'" onclick="change_status('.$record['annual_manpower_planning_id'].',8,false);"  href="javascript:void(0)" tooltip="Toggle State" original-title=""></a>';

		    }

	        if ($this->user_access[$this->module_id]['view']) 
	        {
	            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
	        }
	        
	        $annual_manpower_planning = $this->db->dbprefix('annual_manpower_planning');
	        $annual_manpower_planning_id = $record['annual_manpower_planning_id'];
			$annual_manpower_planning_header = $this->db->query("SELECT * FROM {$annual_manpower_planning}  WHERE annual_manpower_planning_id = '{$annual_manpower_planning_id}'")->row();	        

			$acount = 0;
    		$employee_approver_list = $this->system->get_approvers_emails_and_condition($annual_manpower_planning_header->employee_id,$this->module_id);
            foreach( $employee_approver_list as $employee_approver ){
            	if( $employee_approver['approver'] == $this->userinfo['user_id'] ){
            		$acount++;
            	}
            }
      


	        if ($record['amp_status'] == "Draft")
	        {
		        if ($this->user_access[$this->module_id]['delete']) 
		        {
		        	// if($annual_manpower_planning_header->created_by == $this->userinfo['user_id'])
		        	// {
		        		$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';		        		
		            	$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
		        	// }


		        }
	    	}

	    	if ($record['t2annual_manpower_planning_status'] == "For Approval")
	        {
				if ( $this->user_access[$this->module_id]['edit'] ) 
				{
					if($annual_manpower_planning_header->annual_user_division_id == $this->userinfo['user_id'])
		        	{
		            	//$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
		            }
		        } 

		        if ($this->user_access[$this->module_id]['approve']) 
		        {
		        	if($annual_manpower_planning_header->annual_user_division_id == $this->userinfo['user_id'] || $acount > 0 )
		        	{
		            	//$actions .= '<a class="icon-button icon-16-approve approve-class_list" module_link="'.$module_link.'" tooltip="Approved" href="javascript:void(0)"></a>';		            
		            }
		        }

		        if ( $this->user_access[$this->module_id]['decline'] ) 
		        {
		        	if($annual_manpower_planning_header->annual_user_division_id == $this->userinfo['user_id'] || $acount > 0 )
		        	{
		            	//$actions .= '<a class="icon-button icon-16-disapprove disapprove-class_list" tooltip="Decline" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
		            }
		        }
	    	}

	    	if ($record['t2annual_manpower_planning_status'] == "Declined")
	        {
		        if ($this->user_access[$this->module_id]['edit']) 
		        {
		        	if($annual_manpower_planning_header->employee_id == $this->userinfo['user_id'])
		        	{
		        		$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';		        		
		        	}
		        }
		        if($this->user_access[$this->module_id]['delete'])
		        {
		        	if($annual_manpower_planning_header->employee_id == $this->userinfo['user_id'])
		        	{
		            	$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
		        	}
		        }
	    	}

	    	if($record['t2annual_manpower_planning_status'] == "For HR Review"){

	    		if ($this->user_access[$this->module_id]['post']){

	    			if( $annual_manpower_planning_header->employee_id != $this->userinfo['user_id'] && $this->userinfo['user_id'] == 2 )
		        	{

	    				$actions .= '<a class="icon-button icon-16-tick" onclick="change_status('.$record['annual_manpower_planning_id'].',2,false);" tooltip="Mark as Reviewed" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';

	    			}

	    		}

	    		if ($this->user_access[$this->module_id]['approve']) 
		        {
		        	if( $acount > 0 )
		        	{
		            	//$actions .= '<a class="icon-button icon-16-approve approve-class_list" module_link="'.$module_link.'" tooltip="Approved" href="javascript:void(0)"></a>';		            
		            }
		        }

		        if ( $this->user_access[$this->module_id]['decline'] ) 
		        {
		        	if( $acount > 0 )
		        	{
		            	//$actions .= '<a class="icon-button icon-16-disapprove disapprove-class_list" tooltip="Decline" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
		            }
		        }

	    	}

	        $actions .= '</span>';			

		// }
		// else{
		// 	// set default
		// 	if($module_link == "") $module_link = $this->module_link;
		// 	if($container == "") $container = "jqgridcontainer";

		// 	$actions = '<span class="icon-group">';
	                
	 //        if ($this->user_access[$this->module_id]['view']) {
	 //            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
	 //        }

	 //        if ($record['t2annual_manpower_planning_status'] == "For Approval"){
		//         if ($this->user_access[$this->module_id]['approve']) {
		//             $actions .= '<a class="icon-button icon-16-approve" module_link="'.$module_link.'" tooltip="Approved" href="javascript:void(0)"></a>';
		//         }
	 //        }

		// 	if ($record['t2annual_manpower_planning_status'] == "Approved"){
		// 		if ( $this->user_access[$this->module_id]['decline'] ) {
		//             $actions .= '<a class="icon-button icon-16-cancel" tooltip="Decline" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
		//         } 				
		// 	}

	 //        $actions .= '</span>';
		// }

		return $actions;
	}


	function _append_to_select()
	{
		//$this->listview_qry .= ', employee_appraisal.employee_id, user.position_id';
		$this->listview_qry .= ', annual_manpower_planning_status.annual_manpower_planning_status as amp_status';
	}


	function _custom_join(){
		$this->db->join('annual_manpower_planning_status', 'annual_manpower_planning_status.annual_manpower_planning_status_id = annual_manpower_planning.annual_manpower_planning_status_id', 'left');
	}

	function change_status($record_id = 0) {

		if ($this->input->post('record_id')) {
			$record_id = $this->input->post('record_id');
		}

		// Check if current user is part of approvers.
		if ( IS_AJAX ) {

			$this->load->helper('date');

			$approver = $this->db->get_where('annual_manpower_planning_approver', array('approver' => $this->user->user_id,  'amp_id' => $record_id))->row();

			$this->db->update('annual_manpower_planning_approver', array('status' => $this->input->post('form_status_id')), array('approver' => $this->user->user_id,  'amp_id' => $record_id));
			
			$set = array();
			$set['status'] = $this->input->post('form_status_id');

			$data['date_approved'] = date('Y-m-d H:i:s', now());
			$data['annual_manpower_planning_status_id'] = $this->input->post('form_status_id');
			switch( $this->input->post('form_status_id') ){
				case 2:
					$returnstatus = 'Plan was successfully mark as for approval';
					$this->send_email_approve($record_id);
					break;
				case 3:
					$set['date_approved'] = date('Y-m-d H:i:s');
					$returnstatus = 'Plan was successfully approved';
					switch( $approver->condition ){
	                    case 1: //by level
	                        //get next approver
	                        $next_approver = $this->db->get_where('annual_manpower_planning_approver', array('sequence' => ($approver->sequence+1), 'amp_id' => $record_id));

	                        if( $next_approver->num_rows() == 1 ){
	                            $next_approver = $next_approver->row();
	                            $this->db->update('annual_manpower_planning_approver', array('focus' => 1, 'status' => 2), array('sequence' => $next_approver->sequence, 'amp_id' => $record_id));
	                           
	                            //email next approver
	                            $data['annual_manpower_planning_status_id'] = 2;
	                            $data['date_approved'] = '';
	                            $this->send_email();
	                        }
	                        else{
	                            //this is last approver
	                            $this->send_email_approve($record_id);
	                        }
	                        break;
	                    case 2: // Either
	                        $this->send_email_approve($record_id);
	                        break;
	                    case 3: // All
	                        $qry = "SELECT * FROM {$this->db->dbprefix}annual_manpower_planning_approver where amp_id = {$record_id} and status != 3";
	                        $all_approvers = $this->db->query( $qry );
	                        
	                        if( $all_approvers->num_rows() == 0 ){
	                       		$this->send_email_approve($record_id);
	                        }else{
	                        	$data['annual_manpower_planning_status_id'] = 2;
	                            $data['date_approved'] = '';
	                        }
	                        break;  
	                }
					
					
					break;
				case 4: 
					// $set['date_cancelled'] = date('Y-m-d H:i:s');
					$returnstatus = 'Plan was successfully mark as for evaluation';
					// $this->send_email_disapprove($record_id);
					break;
				case 5: 
					$set['date_cancelled'] = date('Y-m-d H:i:s');
					$returnstatus = 'Plan was successfully cancelled';
					break;
				case 6: 
					$returnstatus = 'Plan was successfully mark as reviewed';
					break;
				case 7: 
					$returnstatus = 'fit to work';
					break;			
				case 8: 
					$returnstatus = 'Plan was successfully closed';
					break;	
			}
			
			$this->db->update('annual_manpower_planning_approver', $set, array('approver' => $this->user->user_id, 'amp_id' => $record_id));

			if( $this->input->post('form_status_id') == 4 ){

				if( $this->input->post('incumbent_reevaluate') ){
					$incumbent_reevaluate = $this->input->post('incumbent_reevaluate');
					$this->db->where_in('annual_manpower_planning_details_id',$incumbent_reevaluate);
					$this->db->update('annual_manpower_planning_details',array('disapproved'=>1));
				}

				if( $this->input->post('existing_headcount_reevaluate') ){
					$existing_headcount_reevaluate = $this->input->post('existing_headcount_reevaluate');
					$this->db->where_in('annual_manpower_planning_position_id',$existing_headcount_reevaluate);
					$this->db->update('annual_manpower_planning_position',array('disapproved'=>1));
				}

				if( $this->input->post('new_headcount_reevaluate') ){
					$new_headcount_reevaluate = $this->input->post('new_headcount_reevaluate');
					$this->db->where_in('annual_manpower_planning_position_id',$new_headcount_reevaluate);
					$this->db->update('annual_manpower_planning_position',array('disapproved'=>1));
				}

				$remarks_data['annual_manpower_planning_id'] = $record_id;
				$remarks_data['remarks'] = $this->input->post('remarks');
				$remarks_data['remarked_by'] = $this->userinfo['user_id'];
				$remarks_data['date_remarked'] = date('Y-m-d H:i:s', now());

				$chk_remark = $this->db->get_where('annual_manpower_planning_evaluation_remarks', array('annual_manpower_planning_id' => $record_id));
				if ($chk_remark && $chk_remark->num_rows() > 0) {
					$this->db->update('annual_manpower_planning_evaluation_remarks',$remarks_data, array('annual_manpower_planning_id'=> $record_id));
				}else{
					$this->db->insert('annual_manpower_planning_evaluation_remarks',$remarks_data);	
				}
				


			}
			elseif( $this->input->post('form_status_id') == 3 ){

				$this->db->update('annual_manpower_planning_details',array('disapproved'=>0),array('annual_manpower_planning_id'=>$record_id));
				$this->db->update('annual_manpower_planning_position',array('disapproved'=>0),array('annual_manpower_planning_id'=>$record_id));

			}

			
			// $data['annual_manpower_planning_status_id'] = $this->input->post('form_status_id');
			$this->db->where($this->key_field, $record_id);
			$this->db->update($this->module_table, $data);

			$response['message'] = $returnstatus;

			$response['record_id'] = $this->input->post('record_id'); 
			$response['type'] = 'success';
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function validation(){

		if ($this->input->post('record_id') <> -1 ){

	    	$annual_manpower_planning_info = $this->db->get_where('annual_manpower_planning',array($this->key_field => $this->input->post('record_id')))->row();

	    	$year = $annual_manpower_planning_info->year;

	    	$this->db->where('department_id',$this->input->post('department_id'));		
			$this->db->where('year',$this->input->post('year'));
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');

            if( ( $result->num_rows() > 0 ) && ( $year != $this->input->post('year') ) ){

            	$response['err'] = 1;
                $response['message'] = "Attention: Department and Year already applied.";
                $response['type'] = "error";
                $data['json'] = $response;
                $error++;
            }
            else{
				$response['err'] = 0;
				$data['json'] = $response;		
			}
	    }
	    else{ 

			$this->db->where('department_id',$this->input->post('department_id'));		
			$this->db->where('year',$this->input->post('year'));
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');

            if($result->num_rows() > 0){
            	$response['err'] = 1;
                $response['message'] = "Attention: Department and Year already applied.";
                $response['type'] = "error";
                $data['json'] = $response;
                $error++;
            }
            else{
				$response['err'] = 0;
				$data['json'] = $response;	
			}
	    }

		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function check_existing_position(){
		$this->db->where('position',$this->input->post('position'));
		$result = $this->db->get('user_position');

		$err = 0;

		if ($result->num_rows() > 0){
	        $err = 1;
		}
		else{
			$this->db->where('position',$this->input->post('position'));
			$result = $this->db->get('annual_manpower_planning_position');
			if ($result->num_rows() > 0){
		        $err = 1;
		        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);					
			}
		}

		$data['html'] = $err;
	    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function get_remarks_form(){

		$response->form = $this->load->view( $this->userinfo['rtheme'].'/recruitment/annual_manpower_applicant/remarks_form',array('record_id' => $this->input->post('record_id')), true );
		$this->load->view('template/ajax', array('json' => $response));

	}

	function get_annual_manpower_remarks(){

		$this->db->where('deleted',0);
		$result = $this->db->get('annual_manpower_planning_remarks')->result_array();

		$this->load->view('template/ajax', array('json' => $result));

	}

	function excel_export($record_id = 0)
	{
        $this->db->select('department,year,company');
       // $this->db->join('user','user.user_id = annual_manpower_planning.employee_id');              
        $this->db->join('user_company_department','annual_manpower_planning.department_id = user_company_department.department_id');
        $this->db->join('user_company','user_company.company_id = annual_manpower_planning.company_id');
       // $this->db->join('user_position','user.position_id = user_position.position_id');
        $this->db->where('annual_manpower_planning_id',$record_id);
        $department_result = $this->db->get('annual_manpower_planning');
        $department_row = $department_result->row();

		$this->db->select('annual_manpower_planning_details.user_id,user_company_department.department_id,user_position.position_id,position,department,CONCAT(lastname, " ", firstname) name,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
        $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');              
        $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
        $this->db->join('user_position','user.position_id = user_position.position_id');
        $this->db->join('employee','employee.user_id = user.user_id');
        $this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id');
        $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$record_id);
        $this->db->order_by('user_rank.rank_index','DESC');
        $this->db->group_by('position_id');
        $position = $this->db->get('annual_manpower_planning_details');

        $dbfields = array('name','job_rank', 'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec', 'budget');
        $fields = array("Position / Employee", "Rank", "January","February","March","April","May","June","July","August","September","October","November","December", "Budget"); 

        $dbfields2 = array('position_id','position','remarks','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
        $fields2 = array("","January","February","March","April","May","June","July","August","September","October","November","December"); 

        $dbfields3 = array('position_id','position','remarks','previous_amp','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
        $fields3 = array("","Incumbent","January","February","March","April","May","June","July","August","September","October","November","December"); 

        $this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
        $this->db->where('annual_manpower_planning_position.annual_manpower_planning_id',$record_id);
        $this->db->where('annual_manpower_planning_position.type',2);
        $this->db->where('annual_manpower_planning_position.deleted',0);
        $existing_position = $this->db->get('annual_manpower_planning_position');

        $this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
        $this->db->where('annual_manpower_planning_position.annual_manpower_planning_id',$record_id);
        $this->db->where('annual_manpower_planning_position.type',1);
        $this->db->where('annual_manpower_planning_position.deleted',0);
        $new_position = $this->db->get('annual_manpower_planning_position');


        $remarks = array();
        $remarks_sql = $this->db->get('annual_manpower_planning_remarks')->result();

        foreach( $remarks_sql as $remarks_list ){
        	$remarks[$remarks_list->annual_manpower_planning_remarks_id] = $remarks_list->remarks;
        }

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Annual Manpower Planning List")
		            ->setDescription("Annual Manpower Planning List");
		               
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
		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);					

		//Initialize style
		$default_border = array(
		    'style' => PHPExcel_Style_Border::BORDER_THIN,
		    'color' => array('rgb'=>'000000')
		);

		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleArray1 = array(
			'font' => array(
				'bold' => true,
			)
		);

		$styleArrayBorder = array(
		  	'borders' => array(
		    	'allborders' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	)
		  	)
		);


		$styleDefault = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleDefaultLeft = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleDefaultLeftFill = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'fill' => array(
			  'type' => PHPExcel_Style_Fill::FILL_SOLID,
			   'color' => array(
			   	'rgb'=>'D8D8D8'
			   	), 
			   )
		);

		

		$styleHeader = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			)
		);

		$styleHeader = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
			  'type' => PHPExcel_Style_Fill::FILL_SOLID,
			   'color' => array(
			   	'rgb'=>'D8D8D8'
			   	), 
			   )
		);

		$styleHeaderFillTotal = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			)
		);

		$styleHeaderLeft = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			)
		);

		$styleHeaderLeftFill = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
			  'type' => PHPExcel_Style_Fill::FILL_SOLID,
			   'color' => array(
			   	'rgb'=>'A5A5A5'
			   	), 
			   )
		);

		$styleHeaderLeftFill2 = array(
			'borders' => array(
			  'bottom' => $default_border,
			  'left' => $default_border,
			  'top' => $default_border,
			  'right' => $default_border,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
			  'type' => PHPExcel_Style_Fill::FILL_SOLID,
			   'color' => array(
			   	'rgb'=>'BFBFBF'
			   	), 
			   )
		);

		

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

			$activeSheet->setCellValue($xcoor . '9', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '9')->applyFromArray($styleHeader);
			
			$alpha_ctr++;
		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', $department_row->company);
		$activeSheet->setCellValue('A2', 'Annual Manpower Planning Report');
		$activeSheet->setCellValue('A3', date('F d,Y'));

		$activeSheet->setCellValue('A6', 'Department : '.$department_row->department);
		$activeSheet->setCellValue('A7', 'Year : '.$department_row->year);

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);


		$line = 10;
		$incumbent = array();
		foreach($position->result() as $row){

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $row->position);
			
			foreach( $alphabet as $letters ){
				
				if($letters == 'P'){
					break;
				}

				$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill2);
			}

			$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'O'. $line);

			$line++;

			$this->db->select('CONCAT(lastname, " ", firstname) name, job_rank, budget, jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,`dec`',false);
            $this->db->join('user','user.user_id = annual_manpower_planning_details.user_id');              
            $this->db->join('user_company_department','user.department_id = user_company_department.department_id');
            $this->db->join('user_position','user.position_id = user_position.position_id');
            $this->db->join('user_rank','user_rank.job_rank_id = annual_manpower_planning_details.rank_id');
            $this->db->where('annual_manpower_planning_details.annual_manpower_planning_id',$record_id);
            $this->db->where('user_position.position_id',$row->position_id);
            $this->db->where('user.inactive',0);
	    	$this->db->where('user.deleted',0);
            $this->db->order_by('annual_manpower_planning_details_id','ASC');
            $user = $this->db->get('annual_manpower_planning_details');

           $incumbent[$row->position_id] = $user->num_rows();

            foreach($user->result() as $user_row){

            	$alpha_ctr = 0;			
				$sub_total = 0;
	
            	foreach( $dbfields as $field ){

            		
            		if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
					}

					if ($sub_ctr > 0) {
						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
					} else {
						$xcoor = $alphabet[$alpha_ctr];
					}
					

					if( $field == 'name' ){

						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, ' * '.$user_row->{$field});
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefaultLeftFill);

					}
					elseif ( $field == 'job_rank' || $field == 'budget' ) {
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $user_row->{$field});
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);
					}
					
					else{
						
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $remarks[$user_row->{$field}]);
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);

					}
				
					$alpha_ctr++;
					

            	}

            	$line++;
            }
       
		}

		//for existing position
		if( ( $existing_position && $existing_position->num_rows() > 0 ) ){

			$line = $line + 2;
			$alpha_ctr = 0;			
			$sub_total = 0;
			$total = array();

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, 'Existing Position');

			foreach( $alphabet as $letters ){
			
				if($letters == 'Q'){
					break;
				}

				$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill);
			}

			$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, 'To Hire');
			$objPHPExcel->getActiveSheet()->mergeCells('B'. $line.':'.'P'. $line);
			$line++;

			foreach ($fields3 as $field) {
				
				if ($alpha_ctr >= count($alphabet)) {

					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$activeSheet->setCellValue($xcoor . $line, $field);
				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleHeader);
				

				$alpha_ctr++;
			}

			$activeSheet->setCellValue('O' . $line, 'Approved HC');
			$objPHPExcel->getActiveSheet()->getStyle('O' . $line)->applyFromArray($styleHeader);
			$activeSheet->setCellValue('P' . $line, 'Budget');
			$objPHPExcel->getActiveSheet()->getStyle('P' . $line)->applyFromArray($styleHeader);
			$line++;

			foreach($existing_position->result() as $existing_position_info){

            	$alpha_ctr = 0;			
				$sub_total = 0;
				$sub_total_count = 0;

            	foreach( $dbfields3 as $field ){

            		
            		if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
					}

					if ($sub_ctr > 0) {
						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
					} else {
						$xcoor = $alphabet[$alpha_ctr];
					}
					

					if( $field == 'position_id' ){

						$position_name = $this->db->get_where('user_position',array('position_id' => $existing_position_info->{$field}))->row();

						$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $position_name->position);
						
						foreach( $alphabet as $letters ){
						
							if($letters == 'Q'){
								break;
							}

							$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill2);
						}

						
						$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'P'. $line);
						
						$line++;

						
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 'Headcount');
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefaultLeftFill);
						$alpha_ctr++;
					}
					elseif( $field == 'previous_amp' ){

						// $previous_amp = 0;
						$previous_amp = $incumbent[$existing_position_info->position_id];
						// if( $existing_position_info->year ){

						// 	$year = $existing_position_info->year - 1;

						// 	$this->db->join('annual_manpower_planning','annual_manpower_planning.annual_manpower_planning_id = annual_manpower_planning_position.annual_manpower_planning_id','left');
						// 	$this->db->where('annual_manpower_planning_position.position_id',$existing_position_info->position_id);
						// 	$this->db->where('annual_manpower_planning_position.type',2);
						// 	$this->db->where('annual_manpower_planning.year',$year);
						// 	$this->db->where('annual_manpower_planning.department_id',$existing_position_info->department_id);
						// 	$this->db->where('annual_manpower_planning.annual_manpower_planning_status_id',3);
						// 	$previous_amp_result = $this->db->get('annual_manpower_planning_position');

						// 	if( $previous_amp_result->num_rows() > 0 ){

						// 		$previous_amp_record = $previous_amp_result->row_array();

						// 		$previous_amp = $previous_amp_record['total'];

						// 	}

						// }

						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $previous_amp);
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);
						$alpha_ctr++;

					}
					elseif( $field == 'position' || $field == 'remarks' ){

					}
					else{

						$sub_total_count = $sub_total_count + $existing_position_info->{$field};
						$total[$field] += $existing_position_info->{$field};
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $existing_position_info->{$field});
						$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);
						$alpha_ctr++;
					}

            	}

            	$objPHPExcel->getActiveSheet()->setCellValue('O' . $line, $existing_position_info->total);
            	$objPHPExcel->getActiveSheet()->getStyle('O' . $line)->applyFromArray($styleHeaderFillTotal);
            	$total['grand_total'] += $sub_total_count;

            	$objPHPExcel->getActiveSheet()->setCellValue('P' . $line, $existing_position_info->budget);
            	$objPHPExcel->getActiveSheet()->getStyle('P' . $line)->applyFromArray($styleHeaderFillTotal);
            	$line++;
            }

            

		}

		//for new position
		if( ($new_position && $new_position->num_rows() > 0 ) ){

			$line = $line + 2;
			$alpha_ctr = 0;			
			$sub_total = 0;
			$total = array();

			$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, 'New Position');

			foreach( $alphabet as $letters ){
			
				if($letters == 'P'){
					break;
				}

				$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill);
			}


			$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'O'. $line);
			$line++;

			foreach ($fields2 as $field) {
				
				if ($alpha_ctr >= count($alphabet)) {

					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$activeSheet->setCellValue($xcoor . $line, $field);
				$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleHeader);
				

				$alpha_ctr++;
			}

			$activeSheet->setCellValue('N' . $line, 'Total');
			$objPHPExcel->getActiveSheet()->getStyle('N' . $line)->applyFromArray($styleHeader);
			$activeSheet->setCellValue('O' . $line, 'Budget');
			$objPHPExcel->getActiveSheet()->getStyle('O' . $line)->applyFromArray($styleHeader);
			$line++;


				foreach($new_position->result() as $new_position_info){

	            	$alpha_ctr = 0;			
					$sub_total = 0;
					$sub_total_count = 0;

	            	foreach( $dbfields2 as $field ){
	            		
	            		if ($alpha_ctr >= count($alphabet)) {
						$alpha_ctr = 0;
						$sub_ctr++;
						}

						if ($sub_ctr > 0) {
							$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
						} else {
							$xcoor = $alphabet[$alpha_ctr];
						}
						

						if( $field == 'position_id' || $field == 'remarks' ){

		
						}
						elseif( $field == 'position' ){

							$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, $new_position_info->{$field});

							foreach( $alphabet as $letters ){
							
								if($letters == 'P'){
									break;
								}

								$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleHeaderLeftFill2);
							}


							$objPHPExcel->getActiveSheet()->mergeCells('A'. $line.':'.'O'. $line);
							$line++;

							
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, 'Headcount');
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefaultLeft);
							$alpha_ctr++;

						}
						else{

							$sub_total_count = $sub_total_count + $new_position_info->{$field};
							$total[$field] += $new_position_info->{$field};
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $new_position_info->{$field});
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleDefault);
							$alpha_ctr++;
						}

	            	}

	            	$objPHPExcel->getActiveSheet()->setCellValue('N' . $line, $new_position_info->total);
	            	$objPHPExcel->getActiveSheet()->getStyle('N' . $line)->applyFromArray($styleHeaderFillTotal);

	            	$total['grand_total'] += $sub_total_count;

	            	$objPHPExcel->getActiveSheet()->setCellValue('O' . $line, $new_position_info->budget);
	            	$objPHPExcel->getActiveSheet()->getStyle('O' . $line)->applyFromArray($styleHeaderFillTotal);

	            	$line++;

	            	$objPHPExcel->getActiveSheet()->setCellValue('A' . $line, 'Remarks');
	            	$objPHPExcel->getActiveSheet()->setCellValue('B' . $line, $new_position_info->remarks);

					foreach( $alphabet as $letters ){
					
						if($letters == 'P'){
							break;
						}

						$objPHPExcel->getActiveSheet()->getStyle( $letters . $line)->applyFromArray($styleDefaultLeft);
					}


					$objPHPExcel->getActiveSheet()->mergeCells('B'. $line.':'.'O'. $line);
					$line++;


	            }

		}


		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . url_title("Annual Manpower Planning List") .  date('Y-m-d') .'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}	

	function get_anual_manpower_approver(){
		$ci=& get_instance();

		if (!isset($_POST['from_createdby'])) {	
			$approvers = $ci->system->get_approvers_and_condition($ci->userinfo['user_id'],$ci->module_id);

			foreach ($approvers as $key => $value) {
				$approver[] = $value['approver'];
			}

			$ci->db->where_in('user_id',$approver);
			$ci->db->where('deleted',0);
			$result = $ci->db->get('user');	

			if( $ci->user_access[$ci->module_id]['post'] != 1 ){
				if ($result  && $result->num_rows() > 0){
					return $result->result_array();
				}
				else{
					return array();
				}
			}
			else{
				return array();
			}			
		}
		else{
			$approvers = $ci->system->get_approvers_and_condition($ci->input->post('created_by'),$ci->module_id);

			foreach ($approvers as $key => $value) {
				$approver[] = $value['approver'];
			}

			$options = '';

			if (count($approver) > 0){
				$ci->db->where_in('user_id',$approver);
				$ci->db->where('deleted',0);
				$result = $ci->db->get('user');

				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						$options .= '<option value="'.$row->employee_id.'">'.$row->firstname.'&nbsp;'.$row->lastname.'</option>';
					}			
				}				
			}
			$data['html'] = $options;
		    $ci->load->view($ci->userinfo['rtheme'] . '/template/ajax', $data);				
		}
	}

	function get_employee_ranks()
	{
		$this->db->select('job_rank_id,job_rank');
		$this->db->where('deleted',0);
		$this->db->order_by('job_rank','ASC');
		$ranks = $this->db->get('user_rank')->result();
		return $ranks;
	}

	function get_rank_details()
	{
		$position_id = $this->input->post('position_id');
		$amp_pos_id = $this->input->post('amp_pos_id');
		$record_id = $this->input->post('amp_id');

		// $this->db->where('position_id',$position_id);
		$this->db->where('annual_manpower_planning_position_id',$amp_pos_id);
		$details = $this->db->get('annual_manpower_planning_ranks');
		
		$ranks = $this->get_employee_ranks();

		$html = '<table width="100%" class="default-table boxtype" ><tr><th align="center">Rank</th><th align="center">Quantity</th></tr>';

		if ($details && $details->num_rows() > 0) {
			$detail = $details->row();
			$html .= '<input type="hidden" value="'.$detail->annual_manpower_planning_rank_id.'" name="rank_record_id" class="rank_record_id" >';
			$rank_details = json_decode($detail->details, true);
			foreach ($ranks as $rank) {
		       
				if ($this->uri->segment(4) && $this->uri->segment(4) == 'detail') {
					$html .= '<tr>
			            <td>'.$rank->job_rank.'</td>
			            <td align="center">'.$rank_details['rank_count'][$rank->job_rank_id].'</td>
			        </tr>';
				}else{
					$html .= '<tr>
			            <td>
			            	<input type="hidden" value="'.$rank->job_rank_id.'" name="job_rank_id[]" class="job_rank_id" >'.$rank->job_rank.'</td>
			            <td align="center"><input type="text" style="width:100px" name="rank_count['.$rank->job_rank_id.']" value="'.$rank_details['rank_count'][$rank->job_rank_id].'" class="rank_count" ></td>
			        </tr>';
				}
	        }
		}else{
			$html .= '<input type="hidden" value="-1" name="rank_record_id" class="rank_record_id" >';
	        foreach ($ranks as $rank) {
		       	$html .= '<tr>
				            <td>
				            	<input type="hidden" value="'.$rank->job_rank_id.'" name="job_rank_id[]" class="job_rank_id" >'.$rank->job_rank.'</td>
				            <td><input type="text" name="rank_count['.$rank->job_rank_id.']" class="rank_count" ></td>
				        </tr>';
	        }
      	}
      	$html .=  '</table>';

      	$response->html = $html;
      	$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function save_rank_details()
	{
		$job_rank_id = $this->input->post('job_rank_id');
		$rank_count = $this->input->post('rank_count');

		$details['job_rank_id'] = $job_rank_id;
		$details['rank_count'] = $rank_count;

		$position_id = $this->input->post('position_id');
		$amp_id = $this->input->post('amp_id');
		$amp_position_id = $this->input->post('amp_position_id');
		$rank_record_id = $this->input->post('rank_record_id');

		$insert['position_id'] = $position_id;
		$insert['annual_manpower_planning_position_id'] = $amp_position_id;
		$insert['annual_manpower_planning_id'] = $amp_id;
		$insert['details'] = json_encode($details);

		if ($rank_record_id == -1) {
			$this->db->insert('annual_manpower_planning_ranks', $insert);
		}else{
			$this->db->where('annual_manpower_planning_rank_id', $rank_record_id);
			$this->db->update('annual_manpower_planning_ranks', $insert);
		}
		
		$response->msg = "Rank details was successfully saved.";
        $response->msg_type = "success";

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	// END custom module funtions
}

/* End of file */
/* Location: system/application */
?>