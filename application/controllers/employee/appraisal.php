<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Employee Performance Appraisal';
		$this->listview_description = 'This module lists appraisals.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an appraisal.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an appraisal.';	

		if (in_array($this->userinfo['user_id'], array(12,408))) {
			$this->user_access[$this->module_id]['project_hr'] = 1;	
		}
    }

	// START - default module functions
	// default jqgrid controller method
	function index($period_id = NULL)
    {
    	if (is_null($period_id)) {
			redirect('employee/appraisal_period');
    	}

		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';
		$data['scripts'][] = uploadify_script();

		if($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		$this->db->where('employee_appraisal_period_id', $period_id);
		$this->db->where('deleted', 0);
		$period = $this->db->get('employee_appraisal_period');

		$filter['period_id'] = $this->uri->segment(4);

		$this->filter = serialize($filter);

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = 'insert_period_id(' . $this->uri->segment(4) . ');';

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

	function detail($user_id, $period_id)
	{
		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'employees/appraisal/detailview';

		$data['show_wizard_control'] = true;
		$data['scripts'][] = uploadify_script();
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';

		if (is_null($user_id) || is_null($period_id) || $user_id <= 0 || $period_id <= 0) {
			$this->session->set_flashdata('flashdata', 'Invalid parameters.');
			redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));
		}

		if($this->user_access[$this->module_id]['edit'] == 1) {
			$period = $this->_get__appraisal_period($period_id);

			if (!$period) {
				$this->session->set_flashdata('flashdata', 'Period not defined.');
				redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));				
			}

			$data['period'] = $period->row();

			$this->load->helper('form');		
			
			$record = $this->_get_appraisal_planning($user_id, $period_id);
			$user = $this->get_appraisee($user_id, $record);

			$data['appraisee'] = $user;	
			
			$approver = $this->system->get_approvers_and_condition($user_id,$this->module_id);

			$appraiser_id = $approver[0]['approver']; //$this->system->get_reporting_to($user_id);

			$this->db->select(array('user.*', 'position'));
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user.position_id = user_position.position_id', 'left');			
			$this->db->where(array('user.user_id' => $appraiser_id, 'user.deleted' => 0));

			$data['appraiser'] = $this->db->get('user')->row();

			$appraiser_direct_superior_user_id = $this->system->get_reporting_to($appraiser_id);

			$data['appraiser_direct_superior'] = $this->get_division_head($user['division_id']);

			$template = $this->_get_appraisal_criteria($user);

          	if (!$template) {
                $this->session->set_flashdata('flashdata', 'No template has been set for this position.');
                redirect(base_url() . 'employee/appraisal_period');
            }

            $data['form'] = $template;
			
			$template_array =  $this->_build_template($template, $user_id, $period_id);

			$data['core_values'] = $template_array['core'];
            $data['rating_scale'] = $template_array['ratingscale'];
            $data['criteria_questions'] = $template_array['criterias'];
            $data['criteria_columns'] = $template_array['columnames'];

			$employee_appraisal_settings = $this->hdicore->_get_config('employee_appraisal_settings');

            $data['multiplier'] = $employee_appraisal_settings['multiplier'];

            $data['period_count'] = $this->_get_period_type_count($employee_appraisal_settings['periods']);

            $get_rating_scale = $this->get_rating_scale();
			$data['criteria_questions_options'] = (is_array($get_rating_scale)) ? $get_rating_scale : array();

			// $record = $this->_get_appraisal_planning($user_id, $period_id);
			$commenter = false;
            $last_commenter = false;

			if ($record) {
				if ($record['employee_appraisal_id']) {
					 $data['record_id'] = $record['employee_appraisal_id'];

				}else{
					$data['record_id'] = '-1';
				}


					$this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
					$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_id']);
					$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
					$this->db->order_by('sequence','asc');
					$approver_result = $this->db->get('employee_appraisal_approver');

					$data['approvers'] = $approver_result;

					if( $approver_result->num_rows() > 0 ){
						foreach( $approver_result->result() as $approver_info ){
							if( $approver_info->status == 2 && $approver_info->approver == $this->userinfo['user_id'] ){
								$commenter = true;
							}
						}
					}

					$this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
					$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_id']);
					$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
					$this->db->where('status !=',3);
					$this->db->order_by('sequence','asc');
					$last_approver_result = $this->db->get('employee_appraisal_approver');
					
					if( $last_approver_result->num_rows() == 1 ){
						$last_commenter = true;
					}


                $data['record']    = $record;
				$data['apraisee_comments'] = $this->db->get_where('employee_appraisal_ratees_comments',array("employee_appraisal_id"=>$record['employee_appraisal_id']));
				$data['apraiser_comments'] = $this->db->get_where('employee_appraisal_raters_comments',array("employee_appraisal_id"=>$record['employee_appraisal_id']));				
			} else {
				$data['record_id'] = '-1';
			}

			$data['commenter'] = $commenter;
			$data['last_commenter'] = $last_commenter;

			$is_contributor = false;
			$contributors = array();
			$contributors = $this->db->get_where('employee_appraisal_invitation', array('employee_appraisal_id' => $period_id, 'appraisee_id' => $user_id,  'email_sent' => 1));

            if ($contributors && $contributors->num_rows() > 0) {
                foreach ($contributors->result() as $con) {

                	$contributor = explode(',',$con->rater_id);

                	if( in_array($this->userinfo['user_id'], $contributor) ){

                		$is_contributor = true;

                	}

                }
                $data['rater_contributors'] = $contributors->result();
            }     

            $data['is_contributor'] = $is_contributor;
           

			if ($user_id == $this->userinfo['user_id']){
				$data['personal'] = true;
			}
			else{
				$data['personal'] = false;	
			}

			if ($period->row()->employee_appraisal_period_status_id == 2){
				$data['closed'] = true;
			}
			else{
				$data['closed'] = false;	
			}

			$position_array = array();
			$this->db->where('deleted',0);
			$result = $this->db->get('user_position');

			if ($result && $result->num_rows() > 0){
				$position_array[0] = "Please select position";
				foreach ($result->result() as $row) {
					$position_array[$row->position_id] = $row->position;
				}
			}

			$data['position_array'] = $position_array;

			$div_head_info = $this->get_division_head($user['division_id']);

			$data['division_head'] = $div_head_info;

			$dept_head_info = $this->get_department_head($user['department_id']);

			$data['department_head'] = $dept_head_info;

			$this->db->select_max('appraisal_period');
			$result = $this->db->get('employee_appraisal_criteria_column');

			if ($result && $result->num_rows() > 0){
				$data['max_payroll_period'] =  $result->row()->appraisal_period;
			}

		
			$data['level'] = $this->db->get_where('appraisal_competency_level', array('deleted' => 0));
    		$data['core_rating'] = $this->db->get_where('appraisal_core_value_rating', array('deleted' => 0));
    		$data['competency_master'] = $this->db->get_where('appraisal_competency_master', array('deleted' => 0))->result();
			$data['areas_for_development'] = $this->db->get_where('appraisal_areas_development', array('deleted' => 0))->result();
    		$data['development_plan'] = $this->db->get_where('appraisal_development_plan', array('deleted' => 0))->result();
			$transaction = array(
									"employee_appraisal_id"=>$record['employee_appraisal_id'],
									"transaction_type"=>2,
									"viewed_date"=>date('Y-m-d'),
									"viewed_by"=>$this->userinfo['user_id']
								);

			$this->db->insert('employee_appraisal_history',$transaction);

			// parent::detail();
	
			//additional module detail routine here
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';
			
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

	function edit($user_id, $period_id)
	{		

		if (is_null($user_id) || is_null($period_id) || $user_id <= 0 || $period_id <= 0) {
			$this->session->set_flashdata('flashdata', 'Invalid parameters.');
			redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));
		}

		if($this->user_access[$this->module_id]['edit'] == 1) {
			$period = $this->_get__appraisal_period($period_id);

			if (!$period) {
				$this->session->set_flashdata('flashdata', 'Period not defined.');
				redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));				
			}

			$data['period'] = $period->row();
			
			$this->load->helper('form');
			//additional module edit routine here

			$data['show_wizard_control'] = true;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			$data['scripts'][] = uploadify_script();
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';
			
			$data['content'] = 'employees/appraisal/editview_bsc';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();			

			$record = $this->_get_appraisal_planning($user_id, $period_id);
			$user = $this->get_appraisee($user_id, $record);

			$data['appraisee'] = $user;	

			$approver = $this->system->get_approvers_and_condition($user_id,$this->module_id);

			$appraiser_id = $approver[0]['approver']; 

			$appraiser_next_id = $approver[1]['approver']; 

			$this->db->select(array('user.*', 'position'));
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user.position_id = user_position.position_id', 'left');			
			$this->db->where(array('user.user_id' => $appraiser_id, 'user.deleted' => 0));

			$data['appraiser'] = $this->db->get('user')->row();

			$this->db->select(array('user.*', 'position'));
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user.position_id = user_position.position_id', 'left');			
			$this->db->where(array('user.user_id' => $appraiser_next_id, 'user.deleted' => 0));

			$data['appraiser_next'] = $this->db->get('user')->row();

			$data['appraiser_direct_superior'] = $this->get_division_head($user['division_id']); 
			$div_head_info = $this->get_division_head($user['division_id']);
			$data['division_head'] = $div_head_info;

			$dept_head_info = $this->get_department_head($user['department_id']);
			$data['department_head'] = $dept_head_info;
			
			$get_rating_scale = $this->get_rating_scale();
			$data['criteria_questions_options'] = (is_array($get_rating_scale)) ? $get_rating_scale : array();

			$template = $this->_get_appraisal_criteria($user);

          	if (!$template) {
                $this->session->set_flashdata('flashdata', 'No template has been set for this position.');
                redirect(base_url() . 'employee/appraisal_period');
            }

            $data['form'] = $template;

            // template   

			$template_array =  $this->_build_template($template, $user_id, $period_id);

			$data['core_values'] = $template_array['core'];
            $data['rating_scale'] = $template_array['ratingscale'];
            $data['criteria_questions'] = $template_array['criterias'];
            $data['criteria_columns'] = $template_array['columnames'];

			$employee_appraisal_settings = $this->hdicore->_get_config('employee_appraisal_settings');

            $data['multiplier'] = $employee_appraisal_settings['multiplier'];
            $data['period_count'] = $this->_get_period_type_count($employee_appraisal_settings['periods']);

            
            $commenter = false;
            $last_commenter = false;

			if ($record) {

					if ($record['employee_appraisal_id']) {
						 $data['record_id'] = $record['employee_appraisal_id'];

					}else{
						$data['record_id'] = '-1';
						// $approver = $this->system->get_approvers_and_condition($record['employee_id'],$this->module_id);
 					// 	$appraiser_id = $approver[0]['approver'];
					}

					$this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
					$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_id']);
					$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
					$this->db->order_by('sequence','asc');
					$approver_result = $this->db->get('employee_appraisal_approver');

					$data['approvers'] = $approver_result;

					if( $approver_result->num_rows() > 0 ){
						foreach( $approver_result->result() as $approver_info ){
							if( $approver_info->status == 2 && $approver_info->approver == $this->userinfo['user_id'] ){
								$commenter = true;
							}
						}
					}

					$this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
					$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_id']);
					$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
					$this->db->where('status !=',3);
					$this->db->order_by('sequence','asc');
					$last_approver_result = $this->db->get('employee_appraisal_approver');
					
					if( $last_approver_result->num_rows() == 1 ){
						$last_commenter = true;
					}

					
				$data['pending'] = $is_not_approved;
                $data['record']    = $record;
				$data['apraisee_comments'] = $this->db->get_where('employee_appraisal_ratees_comments',array("employee_appraisal_id"=>$record['employee_appraisal_id']));
				$data['apraiser_comments'] = $this->db->get_where('employee_appraisal_raters_comments',array("employee_appraisal_id"=>$record['employee_appraisal_id']));				
			
			} else {
				$data['record_id'] = '-1';
			}

			$data['commenter'] = $commenter;
			$data['last_commenter'] = $last_commenter;

			if ($user_id == $this->userinfo['user_id']){
				$data['personal'] = true;
			}
			else{
				$data['personal'] = false;	
			}

			if ($period->row()->employee_appraisal_period_status_id == 2){
				$data['closed'] = true;
			}
			else{
				$data['closed'] = false;	
			}

			$position_array = array();
			$this->db->where('deleted',0);
			$result = $this->db->get('user_position');

			if ($result && $result->num_rows() > 0){
				$position_array[0] = "Please select position";
				foreach ($result->result() as $row) {
					$position_array[$row->position_id] = $row->position;
				}
			}

			$data['position_array'] = $position_array;

			$this->db->select_max('appraisal_period');
			$result = $this->db->get('employee_appraisal_criteria_column');

			if ($result && $result->num_rows() > 0){
				$data['max_payroll_period'] =  $result->row()->appraisal_period;
			}
			
			$data['competency_master'] = $this->db->get_where('appraisal_competency_master', array('deleted' => 0))->result();
			$data['level'] = $this->db->get_where('appraisal_competency_level', array('deleted' => 0));
    		$data['core_rating'] = $this->db->get_where('appraisal_core_value_rating', array('deleted' => 0));
    		$data['areas_for_development'] = $this->db->get_where('appraisal_areas_development', array('deleted' => 0))->result();
    		$data['development_plan'] = $this->db->get_where('appraisal_development_plan', array('deleted' => 0))->result();
			
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

		$this->ajax_save_bsc();

		$response->msg = 'Data has been successfully saved.';
		$response->msg_type = 'success';
		$response->record_id = $this->key_field_val;
		$this->set_message($response);

		$this->after_ajax_save();

	}

	function ajax_save_standard(){

		$this->db->where('employee_appraisal_period_id', $this->input->post('period_id'));
		$this->db->where('employee_appraisal_period.deleted', 0);
		$period = $this->db->get('employee_appraisal_period');

		$appraisal_period = 0;

		if ($period && $period->num_rows() > 0){
			$appraisal_period = $period->row()->appraisal_period;
		}

		$this->key_field = 'employee_appraisal_id';
		// Save appraisal info		
		$record_id = $this->input->post('record_id');

		if ($this->input->post('employee_id') != $this->userinfo['user_id']){
			$data['appraiser_id']   	   									= $this->input->post('appraiser_id');
			$data['employee_id']   		   									= $this->input->post('employee_id');
			$data['appraisal_period'] 										= $appraisal_period;
			$data['appraisal_period_id'] 									= $this->input->post('period_id');
			$data['employee_appraisal_criteria_array']						= serialize($this->input->post('criteria'));
			$data['employee_appraisal_criteria_question_array']				= serialize($this->input->post('q'));
			$data['employee_appraisal_criteria_question_weight']			= serialize($this->input->post('cq_weight'));
			$data['employee_appraisal_criteria_question_average']			= serialize($this->input->post('cq_ave'));
			$data['employee_appraisal_criteria_question_average_total']		= serialize($this->input->post('cq_ave_total'));
			$data['employee_appraisal_criteria_total_weighted_score'] 		= serialize($this->input->post('total_weight_score'));
			$data['employee_appraisal_final_pa_score'] 						= serialize($this->input->post('final_pa_score'));
			$data['employee_appraisal_final_pa_score_average'] 		 		= $this->input->post('final_pa_score_average');
			$data['key_strengths'] 		   									= $this->input->post('key_strengths');
			$data['individual_development_plan']							= serialize($this->input->post('individual_dp'));
			$data['target_completion_date']									= serialize($this->input->post('target_cd'));
			$data['areas_for_improvement'] 									= $this->input->post('areas_for_improvement');
	        $data['appraiser_summary']     									= $this->input->post('appraiser_summary');
	        $data['new_position_id']     									= $this->input->post('new_position_id');
	        $data['demotion_position_id']     								= $this->input->post('demotion_position_id');
	        $data['new_position_date']     									= ($this->input->post('new_position_date') ? date('Y-m-d',strtotime($this->input->post('new_position_date'))) : '');
	        $data['demotion_recommended_date']     							= ($this->input->post('demotion_recommended_date') ? date('Y-m-d',strtotime($this->input->post('demotion_recommended_date'))) : '');
	        $data['termination_effective_date']     						= ($this->input->post('termination_effective_date') ? date('Y-m-d',strtotime($this->input->post('termination_effective_date'))) : '');
	        $data['transfer_effective_date']     							= ($this->input->post('transfer_effective_date') ? date('Y-m-d',strtotime($this->input->post('transfer_effective_date'))) : '');
	        $data['division_head_id']     									= $this->input->post('division_head_id');
		}
		else{
			$data['appraisee_summary']     									= $this->input->post('appraisee_summary');			
		}	

		$this->db->set($data);

		if ($record_id > '0') {
			$this->db->where('employee_appraisal_id', $record_id);
			$this->db->update('employee_appraisal');
			$this->key_field_val = $record_id;
		} else {
			$this->db->insert('employee_appraisal');
			$this->key_field_val = $this->db->insert_id();
		}
	}

	function ajax_save_bsc(){

		$data['employee_appraisal_status'] = $this->input->post('status');

		$this->key_field = 'employee_appraisal_id';
		// Save appraisal info		
		$record_id = $this->input->post('record_id');

		$data['appraiser_id']   	   										= $this->input->post('appraiser_id');
		$data['employee_id']   		   										= $this->input->post('employee_id');
		$data['position_id']   		   										= $this->input->post('position_id');
		$data['rank_id']   		   											= $this->input->post('rank_id');
		$data['position_class_id']   		   								= $this->input->post('position_class_id');
		$data['department_id']   		   									= $this->input->post('department_id');
		$data['division_id']   		   										= $this->input->post('division_id');
		$data['coach_rating']   		   									= $this->input->post('appraisal_rating');
		$data['final_rating']   		   									= $this->input->post('apraisal_final_rating');
		$data['appraisal_period_id'] 										= $this->input->post('period_id');
		$data['employee_appraisal_recommended_action_array']				= $this->input->post('employee_appraisal_recommended_action_array');
		$data['employee_appraisal_justify']									= $this->input->post('justify');

		$data['employee_appraisal_self_rating_kra']   		   				= $this->input->post('employee_appraisal_self_rating_kra');
		$data['employee_appraisal_self_rating_values']   		   			= $this->input->post('employee_appraisal_self_rating_values');
		$data['employee_appraisal_self_rating_competencies']   				= $this->input->post('employee_appraisal_self_rating_competencies');
		$data['employee_appraisal_self_rating_leadership']   				= $this->input->post('employee_appraisal_self_rating_leadership');
		$data['employee_appraisal_coach_rating_kra']   		   				= $this->input->post('employee_appraisal_coach_rating_kra');
		$data['employee_appraisal_coach_rating_values']   		   			= $this->input->post('employee_appraisal_coach_rating_values');
		$data['employee_appraisal_coach_rating_competencies']   			= $this->input->post('employee_appraisal_coach_rating_competencies');
		$data['employee_appraisal_coach_rating_leadership']   				= $this->input->post('employee_appraisal_coach_rating_leadership');

		if( $this->input->post('actual_result') != "" ){
			$data['employee_appraisal_criteria_actual_result_array']			= serialize($this->input->post('actual_result'));
		}

		if( $this->input->post('self_rating') != "" ){
			$data['employee_appraisal_criteria_self_rating_array']				= serialize($this->input->post('self_rating'));
		}

		if( $this->input->post('self_achieved') != "" ){
			$data['employee_appraisal_criteria_self_achieved_array']			= serialize($this->input->post('self_achieved'));
		}

		if( $this->input->post('self_weight_average') != "" ){
			$data['employee_appraisal_criteria_self_weight_average_array']		= serialize($this->input->post('self_weight_average'));
		}

		if( $this->input->post('rating') != "" ){
			$data['employee_appraisal_criteria_rating_array']					= serialize($this->input->post('rating'));
		}

		if( $this->input->post('coach_achieved') != "" ){
			$data['employee_appraisal_criteria_achieved_array']					= serialize($this->input->post('coach_achieved'));
		}

		if( $this->input->post('coach_weight_average') != "" ){
			$data['employee_appraisal_criteria_weight_average_array']			= serialize($this->input->post('coach_weight_average'));
		}

		if( $this->input->post('year_end_comments') != "" ){
			$data['employee_appraisal_criteria_year_end_comments']				= serialize($this->input->post('year_end_comments'));
		}

		if( $this->input->post('weight') != "" ){
			$data['employee_appraisal_criteria_weight_array']					= serialize($this->input->post('weight'));
		}

		if( $this->input->post('rating_weight') != "" ){
			$data['employee_appraisal_criteria_rating_weight_array']			= serialize($this->input->post('rating_weight'));
		}
		
		if( $this->input->post('self_rating_weight') != "" ){
			$data['employee_appraisal_criteria_self_rating_weight_array']		= serialize($this->input->post('self_rating_weight'));
		}

		if( $this->input->post('self_weighted_score') != "" ){
			$data['employee_appraisal_criteria_self_weighted_score_array']			= serialize($this->input->post('self_weighted_score'));
		}

		if( $this->input->post('coach_weighted_score') != "" ){
			$data['employee_appraisal_criteria_weighted_score_array']			= serialize($this->input->post('coach_weighted_score'));
		}

		if( $this->input->post('actual_level') != "" ){
			$data['employee_appraisal_criteria_actual_level_array']				= serialize($this->input->post('actual_level'));
		}

		if( $this->input->post('comments') != "" ){
			$data['employee_appraisal_criteria_actual_level_comment_array']		= serialize($this->input->post('comments'));
		}

		if( $this->input->post('core_rating') != "" ){
			$data['employee_appraisal_criteria_core_rating_array']				= serialize($this->input->post('core_rating'));
		}

		if( $this->input->post('section_rating') != "" ){
			$data['employee_appraisal_criteria_question_sec_rating_array']		= serialize($this->input->post('section_rating'));
		}

		if( $this->input->post('overall_rating') != "" ){
			$data['employee_appraisal_criteria_question_overal_rating_array']	= serialize($this->input->post('overall_rating'));
		}

		if( $this->input->post('employee_appraisal_or_ratees_remarks') != "" ){
			$data['employee_appraisal_or_ratees_remarks']     					= $this->input->post('employee_appraisal_or_ratees_remarks');
		}

		if( $this->input->post('employee_appraisal_or_ratees_comments') != "" ){
			$data['employee_appraisal_or_ratees_comments']     					= $this->input->post('employee_appraisal_or_ratees_comments');
		}

		if( $this->input->post('employee_appraisal_or_gen_comments') != "" ){
			$data['employee_appraisal_or_gen_comments']     					= $this->input->post('employee_appraisal_or_gen_comments');
		}

		if( $this->input->post('employee_appraisal_or_raters_comments') != "" ){
			$data['employee_appraisal_or_raters_comments']     					= serialize($this->input->post('employee_appraisal_or_raters_comments'));
		}

		if( $this->input->post('employee_appraisal_raters_comments') != "" ){
			$data['employee_appraisal_raters_comments']     					= serialize($this->input->post('employee_appraisal_raters_comments'));
		}
	
		
		$data['employee_appraisal_or_div_dep_comments']     				= $this->input->post('employee_appraisal_or_div_dep_comments');
		$data['employee_appraisal_or_hr_comments']     						= $this->input->post('employee_appraisal_or_hr_comments');
		$data['employee_appraisal_or_rates_sign_date']     					= ($this->input->post('employee_appraisal_or_rates_sign_date') != "" ? date('Y-m-d',strtotime($this->input->post('employee_appraisal_or_rates_sign_date'))) : "");
		// $data['employee_appraisal_or_raters_sign_date']     				= ($this->input->post('employee_appraisal_or_raters_sign_date') != "" ? date('Y-m-d',strtotime($this->input->post('employee_appraisal_or_raters_sign_date'))) : "");
		$data['employee_appraisal_or_raters_sign_date']     				= serialize($this->input->post('employee_appraisal_or_raters_sign_date'));
		$data['employee_appraisal_or_total_score']							= $this->input->post('total_score');
		$data['employee_appraisal_pdp_comp1_array']							= serialize($this->input->post('pdp_comp1'));
		$data['employee_appraisal_pdp_ds1_array']							= serialize($this->input->post('pdp_ds1'));
		$data['employee_appraisal_pdp_dp1_array']							= serialize($this->input->post('pdp_dp1'));
		$data['employee_appraisal_pdp_resources1_array']					= serialize($this->input->post('pdp_resources1'));
		$data['employee_appraisal_pdp_val1_array']							= serialize($this->input->post('pdp_val1'));
		$data['employee_appraisal_criteria_question_mprc_array']			= serialize($this->input->post('mprc'));
		$data['employee_appraisal_criteria_question_ypac_array']			= serialize($this->input->post('ypac'));

		$data['employee_appraisal_pdp_comp2_array']							= serialize($this->input->post('pdp_comp2'));
		$data['employee_appraisal_pdp_ds2_array']							= serialize($this->input->post('pdp_ds2'));
		$data['employee_appraisal_pdp_dp2_array']							= serialize($this->input->post('pdp_dp2'));
		$data['employee_appraisal_pdp_resources2_array']					= serialize($this->input->post('pdp_resources2'));
		$data['employee_appraisal_pdp_val2_array']							= serialize($this->input->post('pdp_val2'));

		$data['employee_appraisal_pdp_rates_sign_date']     				= ($this->input->post('employee_appraisal_pdp_rates_sign_date') != "" ? date('Y-m-d',strtotime($this->input->post('employee_appraisal_pdp_rates_sign_date'))) : "");
		$data['employee_appraisal_pdp_raters_sign_date']     				= ($this->input->post('employee_appraisal_pdp_raters_sign_date') != "" ? date('Y-m-d',strtotime($this->input->post('employee_appraisal_pdp_raters_sign_date'))) : "");			
        $data['division_head_id']     										= $this->input->post('division_head_id');
		$data['apraiser_approval']     										= $this->input->post('raters_approval');
		$data['final_approval_remarks']     								= $this->input->post('final_approval_remarks');
		$data['attachment']     											= $_POST['attachment'][0];


		if($this->input->post('status') == 5){
			$data['final_approval_date'] = date('Y-m-d');
		}else{
			$data['final_approval_date'] = '0000-00-00';
		}

		$this->db->set($data);

		if ($record_id > '0') {
			$this->db->where('employee_appraisal_id', $record_id);
			$this->db->update('employee_appraisal_bsc');
			$this->key_field_val = $record_id;
		} else {	
			$this->db->insert('employee_appraisal_bsc');
			$this->key_field_val = $this->db->insert_id();
		}

		if ($this->input->post('ratees_comments') != ""){
			$ratees_comments_array = array(
											 	"employee_appraisal_id"=>$this->key_field_val,
												"appraisee_comments"=>$this->input->post('ratees_comments'),											
											 );	
			if ($this->input->post('employee_id') != $this->userinfo['user_id']){
				$this->db->insert('employee_appraisal_raters_comments',$raters_comments_array);			
			}
			else{
				$this->db->insert('employee_appraisal_ratees_comments',$ratees_comments_array);			
			}			
		}

		if ($this->input->post('raters_comments') != ""){		
			$raters_comments_array = array(
											 	"employee_appraisal_id"=>$this->key_field_val,
												"appraiser_comments"=>$this->input->post('raters_comments'),											
											 );	
			if ($this->input->post('employee_id') != $this->userinfo['user_id']){
				$this->db->insert('employee_appraisal_raters_comments',$raters_comments_array);			
			}
			else{
				$this->db->insert('employee_appraisal_ratees_comments',$ratees_comments_array);			
			}			
		}
		

		$transaction = array(
								"employee_appraisal_id"=>$record_id,
								"transaction_type"=>1,
								"updated_date"=>date('Y-m-d'),
								"updated_by"=>$this->userinfo['user_id']
							);

		$this->db->insert('employee_appraisal_history',$transaction);		

		$this->db->where('employee_appraisal_id <= ',  0);
		$this->db->delete('employee_appraisal_history');

		if( $this->input->post('employee_strength') != "" ){
			$planning['employee_appraisal_employee_strength']				    = serialize($this->input->post('employee_strength'));
		}
		if( $this->input->post('areas_improvement') != "" ){
			$planning['employee_appraisal_areas_improvement']				    = serialize($this->input->post('areas_improvement'));
		}
		if( $this->input->post('coach_strength') != "" ){
			$planning['employee_appraisal_coach_strength']				        = serialize($this->input->post('coach_strength'));
		}
		if( $this->input->post('coach_improvement') != "" ){
			$planning['employee_appraisal_coach_improvement']				    = serialize($this->input->post('coach_improvement'));
		}

		if ($planning && is_array($planning)) {
			$this->db->where('appraisal_planning_period_id', $this->input->post('period_id'));
			$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->update('employee_appraisal_planning', $planning);
		}

		if (in_array($this->input->post('status'), array(6,7,2,9)) ) {
			if ($this->input->post('status') == 6) {
				$department_head_info = $this->get_department_head($this->input->post('department_id'));
				$reviewer_id = $department_head_info['user_id'];
			}elseif($this->input->post('status') == 7){
				$division_head_info = $this->get_division_head($this->input->post('division_id'));
				$reviewer_id = $division_head_info['user_id'];
			}elseif($this->input->post('status') == 9){
				$reviewer_id = $this->input->post('appraiser_next_id');
			}else{
				$reviewer_id = $this->input->post('appraiser_id');
			}

			$this->send_approved('performance_appraisal_for_review ', $this->key_field_val, $reviewer_id, false);
			
		}elseif($this->input->post('status') == 5){

			$this->send_approved('performance_appraisal_final ', $this->key_field_val, $this->input->post('employee_id'), false);
		}

	}

	function delete()
	{
		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	private function _get__appraisal_period($period_id)
	{
		$this->db->where('appraisal_planning_period.planning_period_id', $period_id);
		$this->db->where('employee_appraisal_period.deleted', 0);
		$this->db->join('appraisal_planning_period', 'appraisal_planning_period.planning_period_id=employee_appraisal_period.planning_period_id');
		$period = $this->db->get('employee_appraisal_period');

		if ($period && $period->num_rows() > 0) {
			return $period;
		}else{
			return false;
		}
		
	}


	function get_appraisee($user_id, $record)
	{
		$user = $this->system->get_employee($user_id);

		if ($record && $record['employee_appraisal_id']) {

			// Level/Rank
			$job_rank = $this->db->get_where('user_rank', array('job_rank_id' => $record['rank_id']))->row();
			$user['job_rank'] = $job_rank->job_rank;
			$user['rank_id'] = $job_rank->job_rank_id;
			
			// Division
			$division = $this->db->get_where('user_company_division', array('division_id' => $record['division_id']))->row();
			$user['division'] = $division->division;
			$user['division_id'] = $division->division_id;

			// Division
			$department = $this->db->get_where('user_company_department', array('department_id' => $record['department_id']))->row();
			$user['department'] = $department->department;
			$user['department_id'] = $department->department_id;

			//  position classification
			$user['position_class_id'] = $record['position_class_id'];
			
			// position
			$position = $this->db->get_where('user_position', array('position_id' => $record['position_id']))->row();
			$user['position'] = $position->position;
			$user['position_id'] = $position->position_id;

		}

		$user['last_appraisal_date'] = $record['last_appraisal_date'];

		$user['position_class'] = "";

		$position_class = $this->db->get_where('position_classification',array('deleted'=>0,'position_class_id'=>$user['position_class_id']));

		if( $position_class->num_rows() > 0 ){
			$user['position_class'] = $position_class->row()->position_class;
			$user['position_class_id'] = $position_class->row()->position_class_id;
		}

		$current_date = new DateTime(date('Y-m-d'));
		$employed_date = new DateTime(date('Y-m-d',strtotime( $user['employed_date'] ) ));

		$diff = $employed_date->diff($current_date);

		$user['tenure'] = $diff->y;
		return $user;
	}

	private function _build_template($template, $user_id, $period_id)
	{
		$arrays = array();
		$contributors = array();
	    foreach ($template->result() as $row) {
        	$this->db->where('employee_appraisal_criteria_id',$row->employee_appraisal_criteria_id);
        	$this->db->where('deleted',0); // comment this if standard appraisal
        	$questions = $this->db->get('employee_appraisal_criteria_question');
        	if ($questions && $questions->num_rows() > 0){
        		foreach ($questions->result() as $question) {
        			if ($question->header_text != ''){
        				$criterias[$row->employee_appraisal_criteria_id]['headers'][$question->employee_appraisal_criteria_question_id] = $question->header_text;
        			}
        			$criterias[$row->employee_appraisal_criteria_id]['tooltip'][$question->employee_appraisal_criteria_question_id] = $question->tooltip;
        			$criterias[$row->employee_appraisal_criteria_id]['questions'][$question->employee_appraisal_criteria_question_id] = $question->question;
        			$criterias[$row->employee_appraisal_criteria_id]['deleted'][$question->employee_appraisal_criteria_question_id] = $question->deleted;            			
        			

        			$contributor = $this->db->get_where('employee_appraisal_invitation', array('employee_appraisal_id' => $period_id, 'appraisee_id' => $user_id,  'employee_appraisal_criteria_question_id' => $question->employee_appraisal_criteria_question_id,  'email_sent' => 1));

                        if ($contributor && $contributor->num_rows() > 0) {
                        	
                            foreach ($contributor->result() as $con) {
                            	$contributors[] = $con->rater_id;
                            	$raters = explode(',', $con->rater_id);
                            	foreach ($raters as $key => $value) {
                           			$criterias[$row->employee_appraisal_criteria_id]['contributor_item'][$question->employee_appraisal_criteria_question_id][$value] = explode(',', $con->employee_appraisal_criteria_question_item);
                           		}
                            }
                      		
                           $criterias[$row->employee_appraisal_criteria_id]['contributors'][$question->employee_appraisal_criteria_question_id] = $contributors;
                           
                        }          			
        		}
        		$arrays['criterias'] = $criterias;
        	}
        	

    		$this->db->where('employee_appraisal_criteria_id',$row->employee_appraisal_criteria_id);
        	$this->db->where('deleted',0);
        	$this->db->order_by('employee_appraisal_criteria_id','asc');
        	$this->db->order_by('rating_scale','asc');
        	$rating_scale = $this->db->get('employee_appraisal_rating_scale');
        	if ($rating_scale && $rating_scale->num_rows() > 0){            	
    		foreach ($rating_scale->result() as $rscale) {
        			$ratingscale[$row->employee_appraisal_criteria_id]['title'][] = $rscale->rating_scale_title;
        			$ratingscale[$row->employee_appraisal_criteria_id]['scale'][] = $rscale->rating_scale;
        		}       
        		$arrays['ratingscale'] = $ratingscale;	     		
        	}
            
        	

        	$this->db->where('employee_appraisal_criteria_id',$row->employee_appraisal_criteria_id);
        	$this->db->where('deleted',0);
        	$this->db->order_by('employee_appraisal_criteria_id','ASC');
        	$this->db->order_by('sequence','ASC');
        	$column_names = $this->db->get('employee_appraisal_criteria_column');
        	if ($column_names && $column_names->num_rows() > 0){
        		foreach ($column_names->result() as $column_name) {
        			if ($column_name->column_name_header != ''){
						$columnames[$row->employee_appraisal_criteria_id]['column_name_header_check'] = 1;								
        				$columnames['column_name_header'][] = $column_name->column_name_header;
        			}
        			$columnames[$row->employee_appraisal_criteria_id]['column_name_header'][] = $column_name->column_name_header;            			
        			$columnames[$row->employee_appraisal_criteria_id]['column_name'][] = $column_name->column_name;
        			$columnames[$row->employee_appraisal_criteria_id]['column_code'][] = $column_name->column_code;
        			$columnames[$row->employee_appraisal_criteria_id]['column_type'][] = $column_name->employee_appraisal_criteria_column_type_id;
        			$columnames[$row->employee_appraisal_criteria_id]['payroll_period'][] = $column_name->appraisal_period;
        			$columnames[$row->employee_appraisal_criteria_id]['field_required'][] = $column_name->required;
        			$columnames[$row->employee_appraisal_criteria_id]['column_tooltip'][] = $column_name->column_tooltip;
        			$columnames[$row->employee_appraisal_criteria_id]['class'][] = $column_name->class;
        		}
        		$arrays['columnames'] = $columnames;
        	}  
        	$core[$row->employee_appraisal_criteria_id] =  $this->db->get_where('appraisal_competency_value', array('appraisal_competency_master_id' => $row->competency_master_id, 'deleted' => 0))->result();
        	$arrays['core'] = $core;
        }

        return $arrays;
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

	function _default_grid_actions( $module_link = "",  $container = "", $record = array(), $period = array() )
	{		
		$dept_head_info = $this->get_department_head($record['department_id']);

		$department_head = $dept_head_info['user_id'];

		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
        
		$appraisee_info = $this->db->get_where('employee',array('employee_id'=>$record['employee_id']))->row();
		$div_head_info = $this->get_division_head($record['division_id']);
		$division_head = $div_head_info['user_id'];

		$with_button = true;

		if (!in_array($record['employee_appraisal_status'],array(1,5)) && $record['employee_appraisal_status'] != '' && $record['employee_appraisal_status'] != 0 && $record['employee_id'] == $this->userinfo['user_id']) {
			$with_button = false;
			if ($record['employee_appraisal_status'] == 8 && $record['employee_id'] !=  408) {
				$with_button = false;
			}
			elseif ($record['employee_appraisal_status'] == 8 && $record['employee_id'] ==  408) {
				$with_button = true;
			}
		}

		if ($with_button){
	        if ($this->user_access[$this->module_id]['view']) {
	        	$actions .= '<a class="icon-button icon-16-document-view" pid="' . $record['position_id'] . '" module_link="'.$module_link.'" tooltip="View job description" href="javascript:void(0)"></a>';
	        	if ( ($record['employee_id'] == $this->userinfo['user_id']) && ($record['employee_appraisal_status'] == 4)) {
	        		$actions .= '<a class="icon-button icon-16-info icon-appraisal" tooltip="View Appraisal" href="' . site_url($this->module_link . '/detail/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
	            }else{
	            	$actions .= '<a class="icon-button icon-16-info icon-appraisal" tooltip="View Appraisal" href="' . site_url($this->module_link . '/detail/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
	        		
	            }    
	            	
	        }
		

	 		if ( $this->user_access[$this->module_id]['edit'] ) {

	 			$approver = $this->system->get_approvers_and_condition($record['employee_id'],$this->module_id);
	 			$appraiser_id = $approver[0]['approver'];
	 			$appraiser_next_id = $approver[1]['approver'];

				$tooltip = (!$record['employee_appraisal_status'] && $record['employee_appraisal_status'] == "" ) ? 'Create Appraisal' : 'Edit Appraisal';

				//If self rating
				if( $appraisee_info->rank_id >= 1 ){


					if( ( $record['employee_appraisal_status'] == "" || $record['employee_appraisal_status'] == 1 || $record['employee_appraisal_status'] == 0) && ( $record['employee_id'] == $this->userinfo['user_id'] ) ){

						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';

					}
					elseif( $record['employee_appraisal_status'] == 2 && ( $appraiser_id == $this->userinfo['user_id'] ) ){

						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';

					}
					elseif( $record['employee_appraisal_status'] == 9 && ( $appraiser_next_id == $this->userinfo['user_id'] ) ){

						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';

					}				
					elseif( $record['employee_appraisal_status'] == 3 && $this->_can_approve($record, $period) ){

						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';

					}
					elseif( $record['employee_appraisal_status'] == 4 && ( $record['employee_id'] == $this->userinfo['user_id'] ) ){

						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';

					}elseif( $record['employee_appraisal_status'] == 6 && ($department_head == $this->userinfo['user_id'])){
						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
					}elseif( $record['employee_appraisal_status'] == 7 && ($division_head == $this->userinfo['user_id'])){
						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
					}


				}
				else{

					if( ( $record['employee_appraisal_status'] == "" || $record['employee_appraisal_status'] == 1 || $record['employee_appraisal_status'] == 0 ) && ( $appraiser_id == $this->userinfo['user_id'] ) ){

						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';

					}
					elseif( $record['employee_appraisal_status'] == 3 && $this->_can_approve($record, $period) ){

						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';

					}
					elseif( $record['employee_appraisal_status'] == 4 && ( $record['employee_id'] == $this->userinfo['user_id'] ) ){

						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';

					}elseif( $record['employee_appraisal_status'] == 6 && ($department_head == $this->userinfo['user_id'])){
						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
					}elseif( $record['employee_appraisal_status'] == 7 && ($division_head == $this->userinfo['user_id'])){
						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
					}			
				}

				if( $record['employee_appraisal_status'] == 8 && ($this->user_access[$this->module_id]['project_hr']) && $this->userinfo['user_id'] == 408){
					$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="'.$tooltip.'" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
				}				

	        }
					
	        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
	            $actions .= '<a class="icon-button icon-16-print icon-appraisal" tooltip="Print" href="' . site_url($this->module_link . '/print_record/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
	        }        

	            $actions .= '<a class="icon-button icon-16-list-view icon-view-log-access" tooltip="View Log Access" href="javascript:void(0)" employeeid="'.$record['employee_id'].'" period_id=""></a>';
	    }

        $actions .= '</span>';
		return $actions;
	}

	function _can_approve( $record, $period ){

		$this->db->where('appraisal_period_id',$period['planning_period_id']);
		$this->db->where('employee_id',$record['employee_id']);
		$this->db->where('deleted',0);
		$appraisal_result = $this->db->get('employee_appraisal_bsc');
		
		if( $appraisal_result->num_rows() > 0 &&  $appraisal_result->row()->employee_appraisal_status == 3 ){

			$appraisal_info = $appraisal_result->row_array();

			$this->db->where('module_id',$this->module_id);
			$this->db->where('record_id',$appraisal_info['employee_appraisal_id']);
			$this->db->where('focus',2);
			$this->db->where('status',2);
			$this->db->where('approver',$this->userinfo['user_id']);
			$approver_result = $this->db->get('employee_appraisal_approver');

			if( $approver_result->num_rows() > 0 ){
				return true;
			}

			return false;

		}

		return false;

	}

	function _set_left_join()
	{
		//build left join for related tables
		foreach($this->related_table as $table => $column){
			if( strpos($table, " ") ){
				$letter = explode(" ", $table);
				if($this->module == "module" && $column == "module_id" && $this->module == "module")
					$this->db->join($table, $letter[1] .'.module_id='. $this->module_table .'.parent_id', 'left');
				else{
					if ($this->module_table == 'user'){
						if ($this->db->field_exists($column[1], $this->module_table)){
							$this->db->join($table, $letter[1] .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');							
						}
						else{
							$this->db->join($table, $letter[1] .'.'. $column[0] .'='. 'hr_employee.'. $column[1], 'left');
						}
					}
					else{
						$this->db->join($table, $letter[1] .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');	
					}
				}
			}
			else{
				if ($this->module_table == 'user'){
					if ($this->db->field_exists($column[1], $this->module_table)){
						$this->db->join($table, $table .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
					}
					else{
						$this->db->join($table, $table .'.'. $column[0] .'='. 'hr_employee.'. $column[1], 'left');
					}		
				}
				else{
					$this->db->join($table, $table .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
				}		
			}
		}		
	}

	function _append_to_select()
	{
		$this->listview_qry .= ', user.position_id, '.$this->db->dbprefix.'employee_appraisal_bsc.department_id, ' .$this->db->dbprefix.'employee_appraisal_bsc.employee_appraisal_id';
	}

	/**
	 * Get template based on user position and employment status.
	 * @param  object $user
	 * @return object
	 */
	private function _get_appraisal_criteria($user) 
	{
		if (!$user) {
			return FALSE;
		}		

		$template = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_appraisal_template_position eatp
  									  JOIN {$this->db->dbprefix}employee_appraisal_template_company eatc
  								      ON eatp.employee_appraisal_template_company_id = eatc.employee_appraisal_template_company_id
  								      JOIN {$this->db->dbprefix}employee_appraisal_criteria eac
  								      ON FIND_IN_SET (eatp.employee_appraisal_template_position_id,eac.employee_appraisal_template_position_id)
  								      WHERE FIND_IN_SET(".$user['status_id'].", employment_status_id)  
  								      AND company_id = ".$user['company_id']."
  								      AND rank_id = ".$user['rank_id']."
  								   	  AND eatp.deleted = 0
  								      AND eac.deleted = 0
  								      AND eatp.position_classification_id = ".$user['position_class_id']."
  								      ORDER BY sequence_no ASC");

		if ($template->num_rows() == 0) {
			return FALSE;
		} else {
			return $template;
		}
	}

	private function _get_period_type_count($period_type){
		$period_count = 0;
		if ($period_type){
			switch ($period_type) {
				case 1:
					$period_count = 12;
					break;
				case 2:
					$period_count = 4;
					break;
				case 3:
					$period_count = 2;
					break;					
			}
		}

		return $period_count;
	}

	private function _get_record($user_id, $period_id)
	{
		$this->db->where('employee_id', $user_id);
		$this->db->where('appraisal_period', $period_id);
		$this->db->where('deleted', 0);

		$record = $this->db->get('employee_appraisal');

		if ($record->num_rows() == 0) {
			return FALSE;
		} else {
			$result = $record->row_array();
			$result['employee_appraisal_criteria_array'] = ($result['employee_appraisal_criteria_array'] != '' ? unserialize($result['employee_appraisal_criteria_array']): '');
			$result['employee_appraisal_criteria_question_array'] = ($result['employee_appraisal_criteria_question_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_array']): '');
			$result['employee_appraisal_criteria_question_weight'] = ($result['employee_appraisal_criteria_question_weight'] != '' ? unserialize($result['employee_appraisal_criteria_question_weight']): '');
			$result['employee_appraisal_criteria_question_average'] = ($result['employee_appraisal_criteria_question_average'] != '' ? unserialize($result['employee_appraisal_criteria_question_average']): '');
			$result['employee_appraisal_criteria_question_average_total'] = ($result['employee_appraisal_criteria_question_average_total'] != '' ? unserialize($result['employee_appraisal_criteria_question_average_total']): '');
			$result['employee_appraisal_criteria_total_weighted_score'] = ($result['employee_appraisal_criteria_total_weighted_score'] != '' ? unserialize($result['employee_appraisal_criteria_total_weighted_score']): '');
			$result['employee_appraisal_final_pa_score'] = ($result['employee_appraisal_final_pa_score'] != '' ? unserialize($result['employee_appraisal_final_pa_score']): '');
			$result['individual_development_plan'] = ($result['individual_development_plan'] != '' ? unserialize($result['individual_development_plan']): '');
			$result['target_completion_date'] = ($result['target_completion_date'] != '' ? unserialize($result['target_completion_date']): '');
			return $result;
		}
	}

	private function _get_record_bsc($user_id, $period_id,$employee_appraisal_id = FALSE)
	{
		if ($employee_appraisal_id){
			$this->db->where('employee_appraisal_id', $employee_appraisal_id);
			$this->db->where('deleted', 0);
		}
		else{
			$this->db->where('employee_id', $user_id);
			$this->db->where('appraisal_period_id', $period_id);
			$this->db->where('deleted', 0);
		}

		$record = $this->db->get('employee_appraisal_bsc');

		if ($record->num_rows() == 0) {
			return FALSE;
		} else {
			$result = $record->row_array();
			$result['employee_appraisal_recommended_action_array'] = ($result['employee_appraisal_recommended_action_array'] != '' ? unserialize($result['employee_appraisal_recommended_action_array']): '');
			
			$result['employee_appraisal_criteria_actual_question_array'] = ($result['employee_appraisal_criteria_actual_question_array'] != '' ? unserialize($result['employee_appraisal_criteria_actual_question_array']): '');


			$result['employee_appraisal_criteria_question_sec_rating_array'] = ($result['employee_appraisal_criteria_question_sec_rating_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_sec_rating_array']): '');
			$result['employee_appraisal_criteria_question_overal_rating_array'] = ($result['employee_appraisal_criteria_question_overal_rating_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_overal_rating_array']): '');
			$result['employee_appraisal_pdp_comp1_array'] = ($result['employee_appraisal_pdp_comp1_array'] != '' ? unserialize($result['employee_appraisal_pdp_comp1_array']): '');
			$result['employee_appraisal_pdp_ds1_array'] = ($result['employee_appraisal_pdp_ds1_array'] != '' ? unserialize($result['employee_appraisal_pdp_ds1_array']): '');
			$result['employee_appraisal_pdp_dp1_array']	= ($result['employee_appraisal_pdp_dp1_array'] != '' ? unserialize($result['employee_appraisal_pdp_dp1_array']): '');
			$result['employee_appraisal_pdp_resources1_array'] = ($result['employee_appraisal_pdp_resources1_array'] != '' ? unserialize($result['employee_appraisal_pdp_resources1_array']): '');
			$result['employee_appraisal_pdp_comp2_array'] = ($result['employee_appraisal_pdp_comp2_array'] != '' ? unserialize($result['employee_appraisal_pdp_comp2_array']): '');
			$result['employee_appraisal_pdp_ds2_array'] = ($result['employee_appraisal_pdp_ds2_array'] != '' ? unserialize($result['employee_appraisal_pdp_ds2_array']): '');
			$result['employee_appraisal_pdp_dp2_array']	= ($result['employee_appraisal_pdp_dp2_array'] != '' ? unserialize($result['employee_appraisal_pdp_dp2_array']): '');
			$result['employee_appraisal_pdp_resources2_array'] = ($result['employee_appraisal_pdp_resources2_array'] != '' ? unserialize($result['employee_appraisal_pdp_resources2_array']): '');

			return $result;
		}


	}	

	function print_record($user_id, $period_id) {	
		if (!$this->user_access[$this->module_id]['visible']) {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient access to the requested module. <span class="red">Please contact the System Administrator.</span>.');
			redirect(base_url() . $this->module_link);
		}

		if (is_null($user_id) || is_null($period_id) || $user_id <= 0 || $period_id <= 0) { 
			$this->session->set_flashdata('flashdata', 'Invalid parameters.');
			redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));
		}

		$period = $this->_get__appraisal_period($period_id);

		if (!$period) {
			$this->session->set_flashdata('flashdata', 'Period not defined.');
			redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));				
		}	


		if($this->user_access[$this->module_id]['print'] == 1) {
			$this->load->library('pdf');
			$this->load->model(array('uitype_detail', 'template'));

			$template = $this->template->get_module_template($this->module_id, 'appraisal');
			
			$record = $this->_get_appraisal_planning($user_id, $period_id);

			$appraisee = $this->get_appraisee($user_id,$record);

			$appraiser_direct_superior= $this->get_division_head($appraisee['division_id']);
			$div_head_info = $this->get_division_head($appraisee['division_id']);
			$dept_head_info = $this->get_department_head($appraisee['department_id']);


			$approver = $this->system->get_approvers_and_condition($user_id,$this->module_id);
			$appraiser_id = $approver[0]['approver'];

			$this->db->select(array('user.*', 'position'));
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user.position_id = user_position.position_id', 'left');			
			$this->db->where(array('user.user_id' => $appraiser_id, 'user.deleted' => 0));

			$appraiser = $this->db->get('user')->row();

			$appraisal_period = $period->row();
			
			$position_class = $this->db->get_where('position_classification',array('deleted'=>0,'position_class_id'=>$appraisee['position_class_id']));				
			
			$current_date = new DateTime(date('Y-m-d'));
			$employed_date = new DateTime(date('Y-m-d',strtotime( $appraisee['employed_date'] ) ));

			$diff = $employed_date->diff($current_date);

			$vars['tenure'] = $diff->y;
			$vars['employee']  = $appraisee['firstname'] . ' ' . $appraisee['lastname'];
			$vars['position']  = $appraisee['position'];
			$vars['rank']  = $appraisee['job_rank'];
			$vars['position_classification'] = ( $position_class && $position_class->num_rows() > 0 ) ? $position_class->row()->position_class : '';
			
			$vars['division_head'] = $div_head_info['firstname'] . ' ' . $div_head_info['lastname'];
			$vars['division'] = $appraisee['division'];

			$vars['department_head'] = $dept_head_info['firstname'] . ' ' . $dept_head_info['lastname'];
			$vars['department'] = $appraisee['department'];

			$vars['reports_to'] = $appraiser->firstname . ' ' . $appraiser->lastname;

			$vars['appraisal_period'] = date('F d, Y',strtotime($appraisal_period->date_from)) .' - '. date('F d, Y',strtotime($appraisal_period->date_to));
			
			$appraisal_template = $this->_get_appraisal_criteria($appraisee);
			$record = $this->_get_appraisal_planning($user_id, $period_id);

			$template_array =  $this->_build_template($appraisal_template, $user_id, $period_id);
			$core_values 		= $template_array['core'];
            $rating_scale 		= $template_array['ratingscale'];
            $criteria_questions = $template_array['criterias'];
            $criteria_columns 	= $template_array['columnames'];

			$vars['agree'] = ($record['employee_appraisal_or_gen_comments'] == 1 ? "x" : " ");
            $vars['not_agree'] = ($record['employee_appraisal_or_gen_comments'] == 1 ? " " : "x");
            $vars['employee_appraisal_or_ratees_remarks'] = $record['employee_appraisal_or_ratees_remarks'];
            $vars['employee_appraisal_or_ratees_comments'] = $record['employee_appraisal_or_ratees_comments'];
            $vars['employee_appraisal_or_raters_comments'] = $record['employee_appraisal_or_raters_comments'][0];
            $vars['employee_appraisal_or_raters_comments_is'] = $record['employee_appraisal_or_raters_comments'][1];
            $vars['overall_rating'] = $record['employee_appraisal_or_total_score'];
            $vars['employee_appraisal_or_rates_sign_date'] = (($record['employee_appraisal_or_rates_sign_date'] != "") && ($record['employee_appraisal_or_rates_sign_date'] != "0000-00-00")) ? date('F d, Y', strtotime($record['employee_appraisal_or_rates_sign_date'])) : "";
			$vars['employee_appraisal_or_raters_sign_date'] = (($record['employee_appraisal_or_raters_sign_date'][$appraiser->user_id] != "") && ($record['employee_appraisal_or_raters_sign_date'][$appraiser->user_id] != "0000-00-00")) ? date('F d, Y', strtotime($record['employee_appraisal_or_raters_sign_date'][$appraiser->user_id])) : "";
            $vars['final_approval_remarks'] = $record['final_approval_remarks'];
            $vars['final_rating'] = $record['final_rating'];
            $vars['coach_rating'] = $record['coach_rating'];
			
            // $vars['gen_comment'] 	= ($record['employee_appraisal_or_gen_comments'] == 1) ? 'recruitment/check.jpg' : 'recruitment/uncheck.jpg';
            $vars['gen_comment'] = $record['employee_appraisal_or_gen_comments'];
			$total_percentage = 0;
			
			$get_rating_scale = $this->get_rating_scale();
			$criteria_questions_options = (is_array($get_rating_scale)) ? $get_rating_scale : array();

			if( $appraisee['rank_id'] >= 1 ){
			    $self_rating = true;
			}

			$this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
			$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_id']);
			$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
			$this->db->order_by('sequence','asc');
			$approver_result = $this->db->get('employee_appraisal_approver');

			$vars['rating_scale'] =  '<table style="width:90%;" cellpadding="17" cellspacing="5" style="font-size:100%;" class="default-table boxtype">
						                <tr>
						                    <th style="width: 15%;">Qualitative Rating</th>
						                    <th style="width: 15%;">Quantitative Rating</th>
						                    <th style="width: 15%;">Total Weighted Score</th>
						                    <th style="width: 55%;">Criteria / Standard</th>
						                </tr>';
						                
                foreach ($criteria_questions_options['qualitative'] as $scale_id => $scale):
                	
	                $vars['rating_scale'] .= '<tr>
							                    <td style="text-align:center;vertical-align:middle;border:1px solid #ddd" >'.$scale.'</td>
							                    <td style="text-align:center;border:1px solid #ddd">'.implode('<br/>',$criteria_questions_options['quantitative'][$scale_id]).'</td>
							                    <td style="text-align:center;border:1px solid #ddd">'.implode('<br/>',$criteria_questions_options['weighted_score'][$scale_id]).'</td>
							                    <td style="vertical-align:middle;border:1px solid #ddd">'.$criteria_questions_options['criteria_standard'][$scale_id].'</td>
							                </tr>';
                 endforeach;
            $vars['rating_scale'] .= '</table>';
          

			$ctr = 1; 
			$vars['criteria_details'] = '';
			$vars['overall_rating_details'] = '<table cellpadding="10" cellspacing="20" style="font-size:100%;" width="100%">
													<tr><td><strong>GENERAL CRITERIA</strong></td>
														<td><strong>KEY IN WEIGHT</strong></td>
														<td><strong>SELF RATING</strong></td>
														<td><strong>COACH&#39;S RATING</strong></td>
                        								<td><strong>TOTAL WEIGHTED / AVERAGE</strong></td>
                        								<td><strong>COACH&#39;S SECTION RATING</strong></td>
                        								<td><strong>WEIGH IN (%)</strong></td>
								                        <td><strong>TOTAL WEIGHTED SCORE</strong></td>
								                        <td><strong>COACH&#39;S TOTAL WEIGHTED SCORE</strong></td>
													</tr>';
				
				$total_weighted_criteria_score = array();
                $total_weighted_self_rate_score = array();
				$total_weight = 0;

				$self_rate_cnt = 0;
           		foreach ($appraisal_template->result() as $key_criteria => $criteria) {
                    if(!$criteria->is_core):
                        foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $key => $question):
                            foreach ($record['employee_appraisal_criteria_self_weight_average_array'][$criteria->employee_appraisal_criteria_id] as $key5 => $value5) {
                                if (!empty($value5[$key]))
                                    $total_self_rate_core[$criteria->employee_appraisal_criteria_id][] = array_sum($value5[$key]);
                            }
                            foreach ($record['employee_appraisal_criteria_weight_average_array'][$criteria->employee_appraisal_criteria_id] as $key5 => $value5) {
                                if (!empty($value5[$key]))
                                    $total_coach_rate_core[$criteria->employee_appraisal_criteria_id][] = array_sum($value5[$key]);
                            }                                

                            $weight = ( $record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] != 0 )? $record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] : 0 ; 
                            if ($weight > 0) {
                                $total_weight += $weight;
                                $self_rate_cnt++;
                            }
                        endforeach;
                        $total_section_rate_init[$key_criteria] = (array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $total_weight) * 100;
                        $total_weighted_score_init[$key_criteria] = number_format(get_in_range((array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $total_weight) * 100) * $criteria->ratio_weighter_score / 100,2,'.','');

                        $total_coach_section_rate_init[$key_criteria] = (array_sum($total_coach_rate_core[$criteria->employee_appraisal_criteria_id]) / $total_weight) * 100;
                        $total_coach_weighted_score_init[$key_criteria] = number_format(get_in_range((array_sum($total_coach_rate_core[$criteria->employee_appraisal_criteria_id]) / $total_weight) * 100) * $criteria->ratio_weighter_score / 100,2,'.','');                            
                    else:
                        foreach ($core_values[$criteria->employee_appraisal_criteria_id] as $key => $values):
                            if (!empty($record['employee_appraisal_criteria_self_rating_array'])){
                                $total_self_rate_core[$criteria->employee_appraisal_criteria_id][] = number_format($record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'],2,'.','');
                            }
                            if (!empty($record['employee_appraisal_criteria_rating_array'])){
                                $total_coach_rate_core[$criteria->employee_appraisal_criteria_id][] = number_format($record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'],2,'.','');
                            }
                        endforeach;
                        $self_rate_cnt = count($total_self_rate_core[$criteria->employee_appraisal_criteria_id]);                            
                        
                        $total_section_rate_init[$key_criteria] = number_format((array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt),2,'.','');
                        $total_weighted_score_init[$key_criteria] = number_format(((array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt) * $criteria->ratio_weighter_score) / 100,2,'.','' );

                        $total_coach_section_rate_init[$key_criteria] = number_format((array_sum($total_coach_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt),2,'.','');
                        $total_coach_weighted_score_init[$key_criteria] = number_format(((array_sum($total_coach_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt) * $criteria->ratio_weighter_score) / 100,2,'.','' );                                                                                                            
                    endif;
                }

				foreach ($appraisal_template->result() as $key_criteria => $criteria) {
           			$total_self_rate = array(); 
           			$total_self_rate_core = array(); 
                    $total_self_rate_criteria = array();

           		 	$column_count = ($criteria->is_core==0) ? (count($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'])+3) : 8; 
           		 	if (!$self_rating && !$criteria->is_core) {
           		 		$column_count = $column_count - 1;
           		 	}
           		 	$vars['criteria_details'] .= '<table style="width:100%;" cellpadding="5" cellspacing="7" class="">
        										<thead>
        											<tr>
												      <td colspan="'.$column_count.'"  style="background-color: #333333;">
												        <strong><span style="color: #ffffff;">Section '.$ctr . ' - ' .$criteria->criteria_text.'</span></strong>
												       </td>
												    </tr>
											    </thead><tbody>';

					foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $key => $question){

                        $weight = ( $record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] != 0 )? $record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] : 0 ; 

                    }

					$vars['overall_rating_details'] .= '<tr>';
						$vars['overall_rating_details'] .= '<td><strong>'.$criteria->criteria_text.'</strong></td>';						    
						$vars['overall_rating_details'] .= '<td> </td>';						    
						$vars['overall_rating_details'] .= '<td> </td>';						    
						$vars['overall_rating_details'] .= '<td> </td>';			
						$vars['overall_rating_details'] .= '<td>'.$total_section_rate_init[$key_criteria].'</td>';
						$vars['overall_rating_details'] .= '<td>'. $total_coach_section_rate_init[$key_criteria] .'</td>';
						$vars['overall_rating_details'] .= '<td>'. $criteria->ratio_weighter_score .'</td>';
						$vars['overall_rating_details'] .= '<td>'. $total_weighted_score_init[$key_criteria] .'</td>';
						$vars['overall_rating_details'] .= '<td>'. $total_coach_weighted_score_init[$key_criteria] .'</td>';
						$overall_coach_rating[] = number_format($record['employee_appraisal_criteria_question_sec_rating_array'][$criteria->employee_appraisal_criteria_id] * $criteria->ratio_weighter_score ,2,'.','');
				    
					$vars['overall_rating_details'] .= '</tr>';	

					// performance objective
					if ($criteria->is_core == 0) {												    
						$vars['criteria_details'] .= '<tr>';
							$column_name_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'];
							$column_code_array = $criteria_columns[$criteria->employee_appraisal_criteria_id]['column_code'];
						                                                       
							foreach ($column_name_array as $key => $column){
								if (!in_array($column, array('Actual','% Achieved','% Weight Average')))
									$vars['criteria_details'] .= '<td><strong>'.$column.'</strong></td>';
							}
							if( $self_rating ){
								$vars['criteria_details'] .= '<td align="center"><strong>Self Rating</strong></td>';
								if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])) {
                                    foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $column_id => $column) {
                                        if (in_array($column, array('% Achieved','% Weight Average'))) {
											$vars['criteria_details'] .= '<td><strong>'.$column.'</strong></td>';
                                        }
                                    }									
								}
							}

							$vars['criteria_details'] .= '<td align="center"><strong>Coach Rating</strong></td>';
							if (isset($criteria_columns[$criteria->employee_appraisal_criteria_id])) {
                                foreach ($criteria_columns[$criteria->employee_appraisal_criteria_id]['column_name'] as $column_id => $column) {
									if (in_array($column, array('% Achieved','% Weight Average'))) {
										$vars['criteria_details'] .= '<td><strong>'.$column.'</strong></td>';
									}
                                }
							}

						$vars['criteria_details'] .= '</tr>';
						$qctr = 1; 

						array_shift($column_code_array);
                        array_shift($column_name_array);
						foreach($criteria_questions[$criteria->employee_appraisal_criteria_id]['questions'] as $key => $question){
							$vars['criteria_details'] .= '<tr>';
							$vars['criteria_details'] .= '<td valign="middle"  style="display: inline-block; vertical-align: middle;">'.$qctr++.'. '.$question.'
															<br><br>
															<table cellpadding="10"><tr><td><small>Key in Weight</small></td></tr>
																<tr><td style="border:1px solid #ddd;width:70%">'.$record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'].'</td></tr>
															</table>
														</td>';

							$column_array_val = $record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key];
							unset($column_array_val['key_weight']);

							$actual_column_array_val = (isset ($record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id]) ? $record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$key] : '');
							
							foreach ($column_name_array as $key1 => $column){
								if (!in_array($column_code_array[$key1], array('actual','achieved','weight_average'))) {
									if ($column_code_array[$key1] != 'actual' && $column_code_array[$key1] != 'actual_accomplished') {
										$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$column_array_val[$key1][0].'</td>';
										
									}else{
										$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.(isset($actual_column_array_val[$key1]) ? $actual_column_array_val[$key1][0] : '').'</td>';			
									}
								}
								
							}

							$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['employee_id']][$key][0].'</td>';

							if( $self_rating ){
								foreach ($column_name_array as $column_id => $column) {
									if (in_array($column_code_array[$column_id], array('achieved','weight_average'))) {
										$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$record['employee_appraisal_criteria_self_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$key][0].'</td>';
									}
								}
							}

							if ($record['employee_appraisal_criteria_rating_array'] != "") {
								$rating = $record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$key][0];
							}else{
								$rating = "";
							}

							$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$rating.'</td>';

							foreach ($column_name_array as $column_id => $column) {
								if (in_array($column_code_array[$column_id], array('achieved','weight_average'))) {
									if (isset($record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id]))
										$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$key][0].'</td>';
									else
										$vars['criteria_details'] .= '<td style="border:1px solid #ddd;"></td>';
								}
							}

							$vars['criteria_details'] .= '</tr>';

							$per_question_count = count($record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key][0]);
                            $per_question_count = ($per_question_count < 1 ? 1 : $per_question_count);
                           
                            if ($per_question_count > 1){
                                for ($i=1; $i < $per_question_count; $i++){
                                	$vars['criteria_details'] .= '<tr>';
                                	$vars['criteria_details'] .= '<td valign="middle"  style="display: inline-block; vertical-align: middle;">&nbsp;</td>';

									$column_array_val = $record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key];
									unset($column_array_val['key_weight']);

									if( isset($record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$key]) ){
		                                $actual_column_array_val = $record['employee_appraisal_criteria_actual_question_array'][$criteria->employee_appraisal_criteria_id][$key];
		                            }
									
									foreach ($column_name_array as $key1 => $column){
										if (!in_array($column_code_array[$key1], array('actual','achieved','weight_average'))) {
											if ($column_code_array[$key1] != 'actual' && $column_code_array[$key1] != 'actual_accomplished') {
												$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$column_array_val[$key1][$i].'</td>';
												
											}else{
												$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.(isset($actual_column_array_val[$key1]) ? $actual_column_array_val[$key1][$i] : '').'</td>';			
											}
										}
									}

									$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$key][$i].'</td>';

									if( $self_rating ){
										foreach ($column_name_array as $column_id => $column) {
											if (in_array($column_code_array[$column_id], array('achieved','weight_average'))) {
												$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$record['employee_appraisal_criteria_self_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$key][$i].'</td>';
											}
										}										
									}

									if ($record['employee_appraisal_criteria_rating_array'] != "") {
										$rating = $record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$key][$i];
									}else{
										$rating = "";
									}

									$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$rating.'</td>';

									foreach ($column_name_array as $column_id => $column) {
										if (in_array($column_code_array[$column_id], array('achieved','weight_average'))) {
											if (isset($record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id]))
												$vars['criteria_details'] .= '<td style="border:1px solid #ddd;">'.$record['employee_appraisal_criteria_'.$column_code_array[$column_id].'_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$key][$i].'</td>';
											else
												$vars['criteria_details'] .= '<td style="border:1px solid #ddd;"></td>';
										}
									}

                                	$vars['criteria_details'] .= '</tr>';
                                }
                            }

							$weight = ( $record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] != 0 )? $record['employee_appraisal_criteria_question_array'][$criteria->employee_appraisal_criteria_id][$key]['key_weight'] : 0 ; 
							$coach_rating = 0.00;
	                        $self_overall_rating = 0.00;
	                        if( $record['employee_appraisal_criteria_rating_array'] != "" ){
	                            foreach( $record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$key] as $perspective_rating ){
	                                if( $perspective_rating ){ $coach_rating += $perspective_rating * ( $weight * 0.01 ); }
	                            }
	                        }
	                        $self_overall_rate = ' ';
	                        if( $self_rating ){
	                            foreach( $record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$key] as $perspective_rating ){
	                                if( $perspective_rating > 0 ){ $self_overall_rating += $perspective_rating * ( $weight * 0.01 ); }
	                            }
	                           $self_overall_rate = number_format($self_overall_rating,2,'.','');
	                        }

							$vars['overall_rating_details'] .= '<tr>';
								$vars['overall_rating_details'] .= '<td>'.$question.'</td>';						    
								$vars['overall_rating_details'] .= '<td>'.$weight.'</td>';	
								if ($record['employee_appraisal_criteria_self_rating_weight_array']) {
									$vars['overall_rating_details'] .= '<td>'.$record['employee_appraisal_criteria_self_rating_weight_array'][$key].'</td>';	
									$total_self_rate[] = $record['employee_appraisal_criteria_self_rating_weight_array'][$key];
								}else{
									$vars['overall_rating_details'] .= '<td></td>';	
								}					    
													    
								$vars['overall_rating_details'] .= '<td>'.$record['employee_appraisal_criteria_rating_weight_array'][$key].'</td>';						    
								$vars['overall_rating_details'] .= '<td> </td>';						    
								$vars['overall_rating_details'] .= '<td> </td>';						    
								$vars['overall_rating_details'] .= '<td> </td>';						    
							$vars['overall_rating_details'] .= '</tr>';	
						}

					}
					// competencies / core
					else{
						$vars['criteria_details'] .= '<tr>';
						$competency_master_info = $this->db->get_where('appraisal_competency_master',array('appraisal_competency_master_id'=>$criteria->competency_master_id))->row();
						if( $competency_master_info->competency_master_code == 'attendance' ){
							$vars['criteria_details'] .= '<td colspan="3"><strong>Competencies / Values</strong></td>';
							$vars['criteria_details'] .= '<td><strong>Rating</strong></td>';
							$vars['criteria_details'] .= '<td colspan="4"><strong>Please Refer to Employees DTR Summary</strong></td>';
						}else{
							$vars['criteria_details'] .= '<td colspan="3"><strong>Competencies / Values</strong></td>';
							if( $self_rating ){ 
								$vars['criteria_details'] .= '<td align="center"><strong>Self Rating</strong></td>'; 
								$vars['criteria_details'] .= '<td align="center"><strong>Self Comment</strong></td>'; 

							}
							$vars['criteria_details'] .= '<td align="center"><strong>Coach Rating</strong></td>';
							$vars['criteria_details'] .= '<td align="center" colspan="2"><strong>Coach Comment</strong></td>';
							
						}
						$vars['criteria_details'] .= '</tr>';

						$core_ctr = 1;
                        foreach ($core_values[$criteria->employee_appraisal_criteria_id] as $values) {
                        	$vars['criteria_details'] .= '<tr>';
                        		$vars['criteria_details'] .= '<td colspan="3"><strong>'.$core_ctr.'. ' .$values->competency_value.'</strong><br>
                        										<span>'.$values->competency_value_description.'</span>
                        									  </td>';
                        	if( $competency_master_info->competency_master_code == 'attendance' ){
                        		$vars['criteria_details'] .= '<td style="border:1px solid #ddd;"> '.$record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'].' </td>';
                        		$vars['criteria_details'] .= '<td style="border:1px solid #ddd;" colspan="4"> '.$record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['year_end_comment'].' </td>';
								
                        	}else{
                        		if( $self_rating ){ 
                        			$self_rate = ($record['employee_appraisal_criteria_self_rating_array'] != "") ? $record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['employee_id']][$values->competency_value_id]['self_rating'] : '&nbsp;';
                        			$self_comment = ($record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['employee_id']][$values->competency_value_id]['self_comment']) ? $record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['employee_id']][$values->competency_value_id]['self_comment'] : '&nbsp;';
                        			
									$vars['criteria_details'] .= '<td style="border:1px solid #ddd;"> '.$self_rate.' </td>';
									$vars['criteria_details'] .= '<td style="border:1px solid #ddd;"> '.$self_comment.' </td>';
								}

								$coach_rating = ($record['employee_appraisal_criteria_rating_array'] != "") ? $record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'] : '&nbsp;';
                        		$coach_comment = ($record['employee_appraisal_criteria_rating_array'] != "") ? $record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_comment'] : '&nbsp;';

								$vars['criteria_details'] .= '<td style="border:1px solid #ddd;"> '.$coach_rating.' </td>';
								$vars['criteria_details'] .= '<td style="border:1px solid #ddd;" colspan="2"> '.$coach_comment.' </td>';
								
                        	}
                        	$vars['criteria_details'] .= '</tr>';
                        	$self_rate_core = ' ';
                        	if( $self_rating ){
                                if( $record['employee_appraisal_criteria_self_rating_array'] != "" ){

                                    $self_rate_core = number_format($record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'],2,'.','');
                                    $total_self_rating += $record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'];
                                }

                            }
                            $coach_rate_core = ' ';
                            if( $record['employee_appraisal_criteria_rating_array'] != "" ){
                                $coach_rate_core = number_format($record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'],2,'.','');
                                    $total_coach_rating += $record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'];
                            }

                        	$vars['overall_rating_details'] .= '<tr>';
								$vars['overall_rating_details'] .= '<td>'.$values->competency_value .'</td>';						    
								$vars['overall_rating_details'] .= '<td> </td>';	
								if ($self_rating) {
									if($record['employee_appraisal_criteria_self_rating_array'] != "" ){
										$vars['overall_rating_details'] .= '<td>'.number_format($record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'],2,'.','').'</td>';

										// $total_self_rate[] = number_format($record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'],2,'.','');
										$total_self_rate_core[$criteria->employee_appraisal_criteria_id][] = number_format($record['employee_appraisal_criteria_self_rating_array'][$criteria->employee_appraisal_criteria_id][$appraisee['user_id']][$values->competency_value_id]['self_rating'],2,'.','');
									}else{
										$vars['overall_rating_details'] .= '<td></td>';		
									}
								}else{
									$vars['overall_rating_details'] .= '<td></td>';						    
								}					    

								$vars['overall_rating_details'] .= '<td>'.(isset($record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id]) ? $record['employee_appraisal_criteria_rating_array'][$criteria->employee_appraisal_criteria_id][$appraiser->user_id][$values->competency_value_id]['coach_rating'] : '').'</td>';						    
								$vars['overall_rating_details'] .= '<td> </td>';						    
								$vars['overall_rating_details'] .= '<td> </td>';						    
								$vars['overall_rating_details'] .= '<td> </td>';						    
							$vars['overall_rating_details'] .= '</tr>';	

                        	$core_ctr++;
                        	
                        }
                        $self_rate_cnt = count($total_self_rate_core[$criteria->employee_appraisal_criteria_id]);
                        $total_self_rate_criteria[$criteria->employee_appraisal_criteria_id][] = array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt;
                        $total_self_rate[] = array_sum($total_self_rate_core[$criteria->employee_appraisal_criteria_id]) / $self_rate_cnt;

					}

					$vars['criteria_details'] .= '</tbody>
												</table>';

										

					$ctr++;
					$total_weighted_self_rate_score[] = number_format(array_sum($total_self_rate),2,'.','' )  * $criteria->ratio_weighter_score;
           		 }
           		
           		$total_weighted_criteria_score = array_sum($total_coach_weighted_score_init);
           		$vars['overall_rating_details'] .= '<tr>';
					$vars['overall_rating_details'] .= '<td><strong>Total Weighted Score</strong></td>';						    
					$vars['overall_rating_details'] .= '<td>100%</td>';	
					$vars['overall_rating_details'] .= '<td> </td>';
					$vars['overall_rating_details'] .= '<td> </td>';
					$vars['overall_rating_details'] .= '<td> </td>';
					$vars['overall_rating_details'] .= '<td><strong>Coach Rating</strong></td>';						    
					$vars['overall_rating_details'] .= '<td>'.number_format($total_weighted_criteria_score,2,'.','').'</td>';						    
					$vars['overall_rating_details'] .= '<td> </td>';						    
					$vars['overall_rating_details'] .= '<td> </td>';						    
				$vars['overall_rating_details'] .= '</tr>';	
				$vars['total_weighted_score'] = $total_weighted_criteria_score;

/*				$final_rating = 0.00;		                
                foreach ($criteria_questions_options['qualitative'] as $scale_id => $scale):
                	foreach ($criteria_questions_options['quantitative'][$scale_id] as $key => $rating_score):
	                    if( array_sum($overall_coach_rating) >= $criteria_questions_options['weighted_score_from'][$scale_id][$key] && ( $final_rating <= $rating_score ) ){
	                        $final_rating = $rating_score;
	                    }
                    endforeach;
				endforeach;
				$vars['final_rating'] = $final_rating;*/

				if($self_rating){
					$total_weighted_self_rate_score = array_sum($total_weighted_score_init);
                               
					$vars['overall_rating_details'] .= '<tr>';
						$vars['overall_rating_details'] .= '<td> </td>';						    
						$vars['overall_rating_details'] .= '<td> </td>';						    
						$vars['overall_rating_details'] .= '<td> </td>';						    
						$vars['overall_rating_details'] .= '<td> </td>';						    
						$vars['overall_rating_details'] .= '<td> </td>';						    
						$vars['overall_rating_details'] .= '<td><strong>Self Rating</strong></td>';						    
						$vars['overall_rating_details'] .= '<td>'.number_format($total_weighted_self_rate_score,2,'.','').'</td>';						    
					$vars['overall_rating_details'] .= '</tr>';	
				}

				$vars['overall_rating_details'] .= '</table>';		

           		$vars['employee_strength'] = '<table style="width:100%;" cellpadding="10" cellspacing="7" class="">';
           		 	foreach( $record['employee_appraisal_employee_strength'] as $employee_strength_info ){
           		 		$vars['employee_strength'] .= '<tr>';
           		 		$vars['employee_strength'] .= '<td style="border:1px solid #ddd;">'.$employee_strength_info.'</td>';
           		 		$vars['employee_strength'] .= '</tr>';
           		 	}
           		$vars['employee_strength'] .= '</table>';

           		$vars['areas_improvement'] = '<table style="width:100%;" cellpadding="10" cellspacing="7" class="">';
           		 	foreach( $record['employee_appraisal_areas_improvement'] as $areas_improvement_info ){
           		 		$vars['areas_improvement'] .= '<tr>';
           		 		$vars['areas_improvement'] .= '<td style="border:1px solid #ddd;">'.$areas_improvement_info.'</td>';
           		 		$vars['areas_improvement'] .= '</tr>';
           		 	}
           		$vars['areas_improvement'] .= '</table>';

           		$vars['coach_strength'] = '<table style="width:100%;" cellpadding="10" cellspacing="7" class="">';
           		 	foreach( $record['employee_appraisal_coach_strength'] as $coach_strength_info ){
           		 		$vars['coach_strength'] .= '<tr>';
           		 		$vars['coach_strength'] .= '<td style="border:1px solid #ddd;">'.$coach_strength_info.'</td>';
           		 		$vars['coach_strength'] .= '</tr>';
           		 	}
           		$vars['coach_strength'] .= '</table>';

           		$vars['coach_improvement'] = '<table style="width:100%;" cellpadding="10" cellspacing="7" class="">';
           		 	foreach( $record['employee_appraisal_coach_improvement'] as $coach_improvement_info ){
           		 		$vars['coach_improvement'] .= '<tr>';
           		 		$vars['coach_improvement'] .= '<td style="border:1px solid #ddd;">'.$coach_improvement_info.'</td>';
           		 		$vars['coach_improvement'] .= '</tr>';
           		 	}
           		$vars['coach_improvement'] .= '</table>';

           		$vars['approver_comments'] = "";
				if ($approver_result && $approver_result->num_rows() > 0) {
					$approvers = $approver_result->result();
	           		$vars['approver_comments'] .= '<table border="0" cellpadding="10" style="width: 100%;"><tbody>';

	           		$vars['signatories'] .= '<table border="0" cellpadding="10" style="width: 100%;"><tbody>';
	           			$ctr = 1;
	           			foreach( $approvers as $approver_info ){
	           				if ($ctr > 1) {
	           					$vars['approver_comments'] .= '<br/>';
	           				}
	           				$vars['approver_comments'] .= '<tr>
															<td align="center" colspan="6">
																	COMMENTS AND RECOMMENDATION ('.$approver_info->firstname . ' ' . $approver_info->lastname.')
															</td>
														</tr>';
							$vars['approver_comments'] .= '<tr>
															<td colspan="6" style="border:1px solid #ddd;">
																'.$record['employee_appraisal_raters_comments'][$approver_info->user_id].'</td>
														</tr>';
							$vars['signatories'] .= '<tr>
															<td  colspan="3" style="border:1px solid #ddd;">'.$approver_info->firstname . ' ' . $approver_info->lastname.'</td>
															<td  colspan="3" style="border:1px solid #ddd;">'.$record['employee_appraisal_or_raters_sign_date'][$approver_info->user_id].'</td>
														</tr>';
							
							$vars['signatories'] .= '<tr>
															<td colspan="3">COACH / RATER&#39;S SIGNATURE OVER PRINTED NAME</td>
															<td  colspan="3">DATE</td>
														</tr>';
							$vars['signatories'] .= '<tr><td  colspan="6" > </td></tr>
													<tr><td  colspan="6" > </td></tr>
													<tr><td  colspan="6" > </td></tr>';
							$ctr++;
						}
					$vars['approver_comments'] .='</tbody></table>';

					$vars['signatories'] .='</tbody></table>';
				}

			// $html = $this->template->prep_message($template['body'], $vars, false, true);
			// dbug($html);
			// die();
			// Prepare and output the PDF.
			$this->pdf->setLeftMargin('5.00');			
			$this->pdf->setRightMargin('5.00');			
			// $this->pdf->setPageOrientation('L');
			$this->print_details($vars);
			
			$this->pdf->Output(date('Y-m-d').' '.$appraisee['firstname'] . '_' . $appraisee['lastname'] . '.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_details($vars)
	{
		$html['header'] =	'<style type="text/css">
								.default-table { margin-top:5px;}
								.default-table th { background-color: #ddd; border-bottom: 1px solid #fff; border-top: 4px solid #ccc; color: #222; padding: 6px; text-shadow: 0 1px 0 #fff; }
								.default-table td { background-color: #fafafa; border-bottom: 1px solid #fff; color: #333; padding: 6px; }
								.default-table tr.hover td,.default-table colgroup.hover  { background-color: #E5E5E5 !important; }
								.default-table tfoot td { border-bottom: 2px solid #ccc; }
								.default-table .header { background: #ddd url(images/sort-bg.gif) right no-repeat; cursor: pointer; padding-right: 21px; }
								.default-table .header.headerSortDown { background: #ddd url(images/sort-desc.gif) right no-repeat; cursor: pointer; }
								.default-table .header.headerSortUp { background: #ddd url(images/sort-asc.gif) right no-repeat; cursor: pointer; }
								.default-table.boxtype td { border-right: 1px solid #fff; }
								.default-table tr.odd td { background: #eee;}
								.default-table tr.odd td.odd, .default-table tr.even td.odd, .default-table th.odd { background: #F3F3F3; }
								.default-table tr.odd td.even, .default-table th.even { background: #EEE; }</style>
								<h4>
									Performance Planning and Appraisal</h4>
								<table cellpadding="15" cellspacing="20" style="font-size:100%;" width="100%">
									<tbody>
										<tr>
											<td colspan="4" style="background-color: #333333;">
												<strong><span style="color: #ffffff;">JOB INFORMATION</span></strong></td>
										</tr>
									</tbody>
								</table>
								<table cellpadding="10" cellspacing="10" style="font-size:100%;" width="100%">
									<tbody>
										<tr>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;EMPLOYEE NAME:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['employee'].'</td>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;APPRAISAL PERIOD:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['appraisal_period'].'</td>
										</tr>
										<tr>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;RANK:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['rank'].'</td>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;POSITION CLASSIFICATION:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['position_classification'].'</td>
										</tr>
										<tr>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;POSITION:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['position'].'</td>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;LAST APPRAISAL DATE:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;</td>
										</tr>
										<tr>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;REPORTS TO:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['reports_to'].'</td>
											<td colspan="1" style="background-color: #c0c0c0;vertical-align:middle"><strong>&nbsp;LAST PROMOTION DATE:</strong></td>
											<td colspan="1" style="border:1px solid #ddd">&nbsp;</td>
										</tr>
										<tr>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;DEPARTMENT:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['department'].'</td>
											<td colspan="1" style="background-color: #c0c0c0;vertical-align:middle"><strong>&nbsp;TENURE:</strong></td>
											<td colspan="1" style="border:1px solid #ddd">&nbsp;'.$vars['tenure'].'</td>
										</tr>
										<tr>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;DEPARTMENT HEAD:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['department_head'].'</td>
											<td colspan="1" style="background-color: #c0c0c0;vertical-align:middle"><strong>&nbsp;COACH RATING:</strong></td>
											<td colspan="1" style="border:1px solid #ddd">&nbsp;'.$vars['coach_rating'].'</td>
										</tr>
										<tr>
											<td style="background-color: #c0c0c0;"><strong>&nbsp;DIVISION:</strong></td>
											<td style="border:1px solid #ddd">&nbsp;'.$vars['division'].'</td>
											<td colspan="1" rowspan="2" style="background-color: #c0c0c0;vertical-align:middle"><strong>&nbsp;COMMITTEE RATING:</strong></td>
											<td colspan="1" rowspan="2" style="border:1px solid #ddd">&nbsp;'.$vars['final_rating'].'</td>
										</tr>
										<tr>
											<td colspan="1" style="background-color: #c0c0c0;vertical-align:middle"><strong>&nbsp;DIVISION HEAD:</strong></td>
											<td colspan="1" style="border:1px solid #ddd">&nbsp;'.$vars['division_head'].'</td>
										</tr>
									</tbody>
								</table>
								<table cellpadding="10" cellspacing="20" style="font-size:100%;" width="100%">
									<tbody>
										<tr>
											<td colspan="4" style="background-color: #333333;">
												<strong><span style="color: #ffffff;">Rating Scale</span></strong></td>
										</tr>
									</tbody>
								</table>
								<p>'.$vars['rating_scale'].'</p>';
		$this->pdf->SetFontSize(7);									
		$this->pdf->addPage('L', 'LETTER', true);
		$this->pdf->writeHTML($html['header'], true, false, true, false, '');

		$html['criteria_details'] = '<p>'.$vars['criteria_details'].'</p>';
		$this->pdf->SetFontSize(7);		
		$this->pdf->addPage('L', 'LETTER', true);
		$this->pdf->writeHTML($html['criteria_details'], true, false, true, false, '');


		$html['strengths'] = '<table cellpadding="10" cellspacing="20" style="font-size:100%;" width="100%">
									<tbody>
										<tr>
											<td colspan="4" style="background-color: #333333;">
												<strong><span style="color: #ffffff;">Strengths and Areas For Improvement</span></strong></td>
										</tr>
										<tr>
											<td colspan="4">
												<strong>Specifying strengths and areas for improvement will not only guide you and your employee in clarifying steps for development of skills, but will also be a valuable input to various HRD programs. ( This also includes a feedback to Coach&#39;s own strengths and areas for improvements )</strong></td>
										</tr>
									</tbody>
								</table>
								<table width="100%">
									<tbody>
										<tr>
											<td><p><strong>1. What are the employees strengths?</strong></p>
												<p>'.$vars['employee_strength'].'</p>
											</td>
											<td><p><strong>2. What areas of performance needs enhancement or improvement?</strong></p>
												<p>'.$vars['areas_improvement'].'</p>
											</td>
										</tr>
										<tr>
											<td><p><strong>3. What are the coach&#39;s strengths?</strong></p>
												<p>'.$vars['coach_strength'].'</p>
											</td>
											<td><p><strong>4. What areas of coach&#39;s performance needs enhancement or improvement?</strong></p>
												<p>'.$vars['coach_improvement'].'</p>
											</td>
										</tr>
									</tbody>
								</table>';
		$this->pdf->SetFontSize(7);								
		$this->pdf->addPage('L', 'LETTER', true);
		$this->pdf->writeHTML($html['strengths'], true, false, true, false, '');

		$html['overall_rating_details'] = '<table cellpadding="10" cellspacing="20" style="font-size:100%;" width="100%">
												<tbody>
													<tr>
														<td colspan="8" style="background-color: #333333;">
															<strong><span style="color: #ffffff;">OVERALL RATING</span></strong></td>
													</tr>
												</tbody>
											</table>
											'.$vars['overall_rating_details'];
		$this->pdf->SetFontSize(7);											
		$this->pdf->addPage('L', 'LETTER', true);
		$this->pdf->writeHTML($html['overall_rating_details'], true, false, true, false, '');


		$html['sign_ratee'] = '<table border="0" cellpadding="10" style="width: 100%;">
									<tbody>
										<tr>
											<td align="center" colspan="6">RATEE&#39;S COMMENTS</td>
										</tr>
										<tr>
											<td colspan="6" style="border:1px solid #ddd;"><p>'.$vars['employee_appraisal_or_ratees_remarks'].'</p><p>&nbsp;</p></td>';

									$html['sign_ratee'] .= '</tr>
											<tr>
												<td align="center" colspan="6"></td>
											</tr>
											<tr>
												<td align="center" colspan="6">COACH / RATER&#39;S COMMENTS (to be accomplished only after the PA Discussion)</td>
											</tr>
											<tr>
												<td colspan="6" style="border:1px solid #ddd;"><p>'.$vars['employee_appraisal_or_raters_comments'][0].'</p><p>&nbsp;</p></td>
											</tr>
									</tbody>
								</table>';
		

		$html['sign_ratee'] .=  '<table border="0" cellpadding="10" style="width: 100%;">
								<tbody>
									<tr>
										<td align="center" colspan="6">
											<p>
												&nbsp;</p>
											<p>
												COMMENTS AND RECOMMENDATION OF IMMEDIATE SUPERIOR ('.$vars['reports_to'].')</p>
										</td>
									</tr>
									<tr>
										<td colspan="6" style="border:1px solid #ddd;">
											'.$vars['employee_appraisal_or_raters_comments_is'].'</td>
									</tr>
									<tr>
										<td colspan="6" ></td>
									</tr>
								</tbody>
							</table>';
		$html['sign_ratee'] .= $vars['approver_comments'];	
		$html['sign_ratee'] .=  '<table border="0" cellpadding="10" style="width: 100%;">
								<tbody>
									<tr>
										<td align="center" colspan="6">
											<p>&nbsp;</p>
											<p>COMMENTS AND RECOMMENDATION OF DIVISION HEAD ('.$vars['division_head'].')</p>
										</td>
									</tr>
									<tr>
										<td colspan="6" style="border:1px solid #ddd;">
											'.$vars['final_approval_remarks'].'</td>
									</tr>
								</tbody>
							</table>';	
		$this->pdf->SetFontSize(7);							
		$this->pdf->addPage('L', 'LETTER', true);
		$this->pdf->writeHTML($html['sign_ratee'], true, false, true, false, '');
		

	
				$html['signatories'] = 	'<p>&nbsp;</p>
										<table>
											
											<tr>
												<td colspan="6"><p>&nbsp;</p></td>
											</tr>
											<tr>
												<td colspan="3" style="border:1px solid #ddd;"><p>'.$vars['employee'].'</p></td>
												<td colspan="3" style="border:1px solid #ddd;"><p>'.$vars['employee_appraisal_or_rates_sign_date'].'</p></td>
											</tr>
											<tr>
												<td colspan="3"><p>RATEE&#39;S SIGNATURE OVER PRINTED NAME</p></td>
												<td colspan="3"><p>DATE</p></td>
											</tr>
										</table>

								<table border="0" cellpadding="10" style="width: 100%;">
									<tbody>										
										<tr>
											<td colspan="6"><p>&nbsp;</p><p>&nbsp;</p></td>
										</tr>
										<tr>
											<td colspan="3" style="border:1px solid #ddd;"><p>'.$vars['reports_to'].'</p></td>
											<td colspan="3" style="border:1px solid #ddd;"><p>'.$vars['employee_appraisal_or_raters_sign_date'].'</p></td>
										</tr>
										<tr>
											<td colspan="3"><p>&nbsp;COACH / RATER&#39;S SIGNATURE OVER PRINTED NAME</p></td>
											<td colspan="3"><p>DATE</p></td>
										</tr>
										<tr>
											<td colspan="6"><p>&nbsp;</p><p>&nbsp;</p></td>
										</tr>
									</tbody>
								</table>';
		$html['signatories'] .= $vars['signatories'];
			$this->pdf->SetFontSize(7);		
			$this->pdf->addPage('L', 'LETTER', true);
			$this->pdf->writeHTML($html['signatories'], true, false, true, false, '');

			return $html;
	}

	// END custom module funtions
	protected function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			if ($this->input->post('record_id') == '-1') {
				$created['created_date'] = date('Y-m-d H:i:s');
				$created['created_by']   = $this->userinfo['user_id'];

				$this->db->where('employee_appraisal_id', $this->key_field_val);
				$this->db->update('employee_appraisal_bsc', $created);

				$approvers = $this->system->get_approvers_and_condition($this->input->post('employee_id'), $this->module_id);

                foreach ($approvers as $key => $value) {
					$value['record_id'] = $this->key_field_val;
					$value['module_id'] = $this->module_id;
					$value['approver'] = $value['approver'];
					$value['sequence'] = $value['sequence'];
					$value['focus'] = 0;
					$value['status'] = 0;
			
					$this->db->insert('employee_appraisal_approver', $value);
				}

			}
			else{
				$update['updated_date'] = date('Y-m-d H:i:s');
				$update['updated_by']   = $this->userinfo['user_id'];
				
				$this->db->insert('employee_appraisal_history', $update);

				$this->db->where('employee_appraisal_id <= ',  0);
				$this->db->delete('employee_appraisal_history');
			}

			
		}

		parent::after_ajax_save();
	}



	function listview()
	{
		$filter = unserialize($this->encrypt->decode($this->input->post('filter')));
		$period_id = $filter['period_id'];

		if (!$this->user_access[$this->module_id]['post'] && !$this->user_access[$this->module_id]['project_hr']) {
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->system->get_employee_all_reporting_to($this->userinfo['user_id']); //$this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
			$this->_subordinate_id = array();
			
			foreach ($subordinates as $subordinate) {
				$this->_subordinate_id[] = $subordinate;
			}

			$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_approver 
					   WHERE module_id = ".$this->module_id."
					   AND approver_employee_id = ".$this->userinfo['user_id']." 
					   AND deleted = 0");

			if ($result && $result->num_rows() > 0){
				foreach ($result->result() as $row) {
					if (!in_array($row->employee_id, $this->_subordinate_id)){
						$this->_subordinate_id[] = $row->employee_id;
					}
				}
			}
		}

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


		//get subordinates
		$employees = array();
		$get_subordinate_circle = array();

		$get_subordinate_circle = $this->_subordinate_id;

		$this->db->where('appraisal_planning_period.planning_period_id',$period_id);
		$this->db->where('appraisal_planning_period.deleted',0);
		$result = $this->db->get('appraisal_planning_period');

		$appraisal_planning_period_row = array();
		if ($result && $result->num_rows() > 0){
			$appraisal_planning_period_row = $result->row_array();
			$appraisal_planning_period_employee = explode(',',$result->row()->employee_id);

			foreach( $appraisal_planning_period_employee as $employee_info ){
				$this->db->where('employee_id', $employee_info);
				$this->db->where('appraisal_planning_period_id', $period_id);
				$this->db->where('deleted', 0);
				$this->db->where('status', 3);
				$employee_planning = $this->db->get('employee_appraisal_planning');

				if( ( in_array($employee_info, $get_subordinate_circle) || $employee_info == $this->userinfo['user_id'] ) || ( $this->user_access[$this->module_id]['post'] ) || $this->user_access[$this->module_id]['project_hr']){

					$this->db->where('employee_id', $employee_info);
					$this->db->where('appraisal_planning_period_id', $period_id);
					$this->db->where('deleted', 0);
					$this->db->where('status', 3);
					$employee_planning = $this->db->get('employee_appraisal_planning');

					if ($employee_planning && $employee_planning->num_rows() > 0) {
						array_push($employees,$employee_info);
					}
				}		

			}
		}


		if( count($employees) <= 0 ){
			$employees = 0;
		}
		$final_appraisal = $this->get_final_appraisal(); 
		$is_head = $this->is_head($this->userinfo['user_id']);

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->select('user.division_id');
		$this->db->select('employee_appraisal_bsc.employee_appraisal_status');
		$this->db->from($this->module_table);
		$this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
		$this->db->join('employee_appraisal_bsc','employee_appraisal_bsc.employee_id = user.employee_id && '.$this->db->dbprefix('employee_appraisal_bsc').'.appraisal_period_id = "'.$period_id.'"','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

/*		if ($this->user_access[$this->module_id]['project_hr'] && !$this->is_superadmin) {
			$committee = 'employee_appraisal_status IN (8,5) OR hr_employee_appraisal_bsc.employee_id = '.$this->userinfo['user_id'].' OR hr_user.user_id = '.$this->userinfo['user_id'].'';
			$this->db->where($committee);
		}*/

/*		if (!$is_head && ($this->user_access[$this->module_id]['post'] == 1) && is_array($final_appraisal)) {
			$where_in = 'employee_appraisal_bsc.employee_appraisal_id NOT IN ('. implode(',', $final_appraisal) .')';
			$this->db->where($where_in);
		}*/
			// $this->db->where('user.role_id <>', 1);
			// $this->db->where('user.role_id <>', 11);
			//$this->db->where('employee.resigned', 0);

		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		// if( $this->user_access[$this->module_id]['post'] != 1 ){
		//if (!$this->user_access[$this->module_id]['project_hr'] || $this->is_superadmin) {
			$this->db->where_in('user.user_id',$employees);
		//}
		// }

		//get list
		$result = $this->db->get();

		// $total_records =  $result->num_rows();
		$response->last_query = $this->db->last_query();
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $total_records > 0 ? ceil($total_records/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $total_records;

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->select('user.division_id');
			$this->db->select('employee_appraisal_bsc.employee_appraisal_status');
			$this->db->from($this->module_table);
			$this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
			$this->db->join('employee_appraisal_bsc','employee_appraisal_bsc.employee_id = user.employee_id && '.$this->db->dbprefix('employee_appraisal_bsc').'.appraisal_period_id = "'.$period_id.'"','left');
			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

/*			if ($this->user_access[$this->module_id]['project_hr'] && !$this->is_superadmin) {
				$committee = 'employee_appraisal_status IN (8,5) OR hr_employee_appraisal_bsc.employee_id = '.$this->userinfo['user_id'].' OR hr_user.user_id = '.$this->userinfo['user_id'].'';
				$this->db->where($committee);
			}	*/		
			// $this->db->where('user.role_id <>', 1);
			// $this->db->where('user.role_id <>', 11);
			//$this->db->where('employee.resigned', 0);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}
/*			if (!$this->is_superadmin) {
				if (!$is_head && ($this->user_access[$this->module_id]['post'] == 1) && is_array($final_appraisal)) {
					$where_in = 'employee_appraisal_bsc.employee_appraisal_id NOT IN ('. implode(',', $final_appraisal) .')';
					$this->db->where($where_in);
				}
			}*/
			// if( $this->user_access[$this->module_id]['post'] != 1 ){
			//if (!$this->user_access[$this->module_id]['project_hr'] || $this->is_superadmin) {
				$this->db->where_in('user.user_id',$employees);
			//}
			// }

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
				}else{
					$this->db->order_by('t1firstnamelastname', 'ASC');
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
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row, $appraisal_planning_period_row ) );
									$cell_ctr++;
								}
							}
							elseif($detail['name'] == 't4employment_status') {
								$qry = "SELECT appraisal_status_id,appraisal_status 
										FROM {$this->db->dbprefix}employee_appraisal_bsc eap 
										INNER JOIN {$this->db->dbprefix}employee_appraisal_status es ON eap.employee_appraisal_status = es.appraisal_status_id
										 WHERE eap.deleted = 0 AND appraisal_status_id = '".$row['employee_appraisal_status']."'";
								$res = $this->db->query($qry);;

								$approver = $this->db->get_where('employee_appraisal_approver', array('record_id' => $row['employee_appraisal_id'], 'module_id' => $this->module_id, 'status' => 2));

								$endorse_to = "";
								if ($approver && $approver->num_rows() > 0) {
									$approver = $approver->row();
									$approver_name = $this->system->get_employee($approver->approver);
									$endorse_to = ' to '. $approver_name['firstname'] . ' ' . $approver_name['lastname'];
								}
								
								$template = "";
								if ( $res && $res->num_rows > 0 ) {
									foreach ($res->result() as $value) {
										$template = $value->appraisal_status;
										if ($value->appraisal_status_id == 3) {
											$template = $value->appraisal_status . $endorse_to; 
										}
									}
								}
								else{
										$template = 'Draft';
								}
								
								$cell[$cell_ctr] = $template;
								$cell_ctr++;
							}
							else{
								if( $this->listview_fields[$cell_ctr]['encrypt'] ){
									$row[$detail['name']] = $this->encrypt->decode( $row[$detail['name']] );
								}

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

	function _set_filter()
	{
		if (!$this->is_superadmin) {
			$this->_subordinate_id[] = $this->userinfo['user_id'];
			//$this->db->where_in('user.user_id', $this->_subordinate_id);
		}

		$filter = unserialize($this->encrypt->decode($this->input->post('filter')));

	}

	function get_final_appraisal()
	{	
		$this->db->where('employee_appraisal_status', 5);
		$this->db->where('rank_id >', 7);
		$final_appraisal = $this->db->get('employee_appraisal_bsc');
		$ids = false;
		
		if ($final_appraisal && $final_appraisal->num_rows() > 0) {
			$ids = array();
			foreach ($final_appraisal->result() as $key => $value) {
				$ids[] = $value->employee_appraisal_id;
			}
		}

		return $ids;

	}


	function is_head($user_id = 0)
	{
		if ($user_id) {
			
			$is_department_head = $this->db->get_where('user_company_department', array('dm_user_id' => $user_id, 'deleted' => 0));
			$is_division_head 	= $this->db->get_where('user_company_division', array('division_manager_id' => $user_id, 'deleted' => 0));

			if ( ($is_department_head && $is_department_head->num_rows() > 0) || ($is_division_head && $is_division_head->num_rows() > 0)   ) {
				return true;
			}else{
				return false;
			}
			
		}else{
			return false;
		}
	}

	/**
	 * Send email to appraised employee for conforme.
	 * @return json
	 */
	function send_email()
	{	
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());
		} else {
			$record_id = $this->input->post('record_id');

			$this->db->where('employee_appraisal_id', $record_id);
			$this->db->where('deleted', 0);
			$record = $this->db->get('employee_appraisal_bsc');

			if (!$record || $record->num_rows() == 0) {
				$response->msg 	    = 'Record not found. Appraisal was not sent.';
				$response->msg_type = 'error';
			} else {


				$this->db->where('approver', $this->userinfo['user_id']);
				$this->db->where('module_id',$this->module_id);
				$this->db->where('record_id', $record_id);
				$this->db->update('employee_appraisal_approver', array('status' => 3, 'focus' => 2));

				$this->db->where('module_id',$this->module_id);
				$this->db->where('record_id', $record_id);
				$this->db->where('status !=',3);
				$this->db->order_by('sequence','asc');
				$approver_result = $this->db->get('employee_appraisal_approver');

				if( $approver_result->num_rows() > 0 ){

					$approver_info = $approver_result->row();

					$this->db->where('record_id',$approver_info->record_id);
					$this->db->where('module_id',$this->module_id);
					$this->db->where('approver',$approver_info->approver);
					$this->db->update('employee_appraisal_approver',array('status'=>2, 'focus' => 2));

					$this->send_approved('performance_appraisal_for_review ', $record_id, $approver_info->approver, false);

				}
				else{

					$this->send_approved('employee_performance_appraisal_for_conform ', $record_id, $this->userinfo['user_id'], true);

					$this->db->where('employee_appraisal_id',$record_id);
					$this->db->where('deleted',0);
					$this->db->update('employee_appraisal_bsc',array('employee_appraisal_status'=>4));

				}

                $response->msg = 'Appraisal sent to approver.';
				$response->msg_tpe = 'success'; 
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}	
	}

	function send_conforme()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());			
		} else {
			$response->msg 		= 'No record found.';
			$response->msg_type = 'error';

			$record_id = $this->input->post('record_id');

			$this->db->where('employee_appraisal_id', $record_id);
			$this->db->where('deleted', 0);

			$record = $this->db->get('employee_appraisal_bsc');

			if ($record && $record->num_rows() > 0) {

				if ($record->row()->employee_id != $this->userinfo['user_id']) {
					$response->msg 		 = 'You are not authorized to execute this action.';
					$response->msg_type  = 'error';
					$response->record_id = $record_id;	
				} else {
					$update['status'] = 3;
                    $update['conformed_date'] = date('Y-m-d G:i:s');					
					$update['conformed_remarks'] = $this->input->post('conformed_remarks');

					$this->db->where('employee_appraisal_id', $record_id);
					if ($this->db->update('employee_appraisal_bsc', $update)) {
						$response->msg 		 = 'Data has been successfully saved.';
						$response->msg_type  = 'success';
						$response->record_id = $record_id;					
					}
				}			
			}		
			
			$this->load->view('template/ajax', array('json' => $response));
		}
	}

	function send_approved($template_code, $record_id, $coach_id,  $for_conforme=false)
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());
		} else { 

			$this->db->where('employee_appraisal_id', $record_id);
			$this->db->where('deleted', 0);
			$record = $this->db->get('employee_appraisal_bsc');

			if (!$record || $record->num_rows() == 0) {
				$response->msg 	    = 'Record not found. Appraisal was not sent.';
				$response->msg_type = 'error';
			} else {
				$record = $record->row();

				$appraisee_info_result = $this->db->get_where('user',array("user_id"=>$record->employee_id));
				if ($appraisee_info_result && $appraisee_info_result->num_rows() > 0){
					$appraisee_info = $appraisee_info_result->row_array();
				}

				$appraiseer_info_result = $this->db->get_where('user',array("user_id"=>$coach_id));

				if ($appraiseer_info_result && $appraiseer_info_result->num_rows() > 0){
					$appraiseer_info = $appraiseer_info_result->row_array();
				}
				
				$period_info = $this->db->get_where('appraisal_planning_period', array('planning_period_id' => $record->appraisal_period_id))->row();

				// Send email.
                // Load the template.            
                $this->load->model('template');

                $template = $this->template->get_module_template(0,$template_code);
                
                if ($for_conforme) {
					$recepients[] = $appraisee_info['email'];
                }else{
					$recepients[] = $appraiseer_info['email'];
				}

                $request['appraisee'] = $appraisee_info['salutation']." ".$appraisee_info['firstname']." ".$appraisee_info['lastname'];
                $request['rater'] = $appraiseer_info['salutation']." ".$appraiseer_info['firstname']." ".$appraiseer_info['lastname'];
                $request['period'] = $period_info->planning_period;
                $request['year'] = $period_info->year;	                
                $request['date'] = $record->created_date;
				$request['here']=base_url().'employee/appraisal/edit/'.$record->employee_id.'/'.$period_info->planning_period_id;
	
                $message = $this->template->prep_message($template['body'], $request);
              	$this->template->queue(implode(',', $recepients), '', $template['subject'], $message);

				$response->msg = 'Appraisal sent to HRA.';
				$response->msg_tpe = 'success';
			}

		}			
	}

	function get_department_head($department_id = false){
		if ($department_id){
			$this->db->where('department_id',$department_id);
			$this->db->where('deleted',0);
			$department = $this->db->get('user_company_department');

			if ($department && $department->num_rows() > 0){
				$dm_user_id = $department->row()->dm_user_id;
			}
		}

		if ($dm_user_id){
			$dm_user_info = $this->system->get_employee($dm_user_id);
			return $dm_user_info;					
		}
		else{
			return array();
		}
	}

	function get_division_head($division_id = false){
		if ($division_id){
			$this->db->where('division_id',$division_id);
			$this->db->where('deleted',0);
			$division = $this->db->get('user_company_division');

			if ($division && $division->num_rows() > 0){
				$div_head_id = $division->row()->division_manager_id;
			}
		}

		if ($div_head_id){
			$div_hed_info = $this->system->get_employee($div_head_id);
			return $div_hed_info;					
		}
		else{
			return array();
		}
	}

	function get_last_viewed(){
		$appraisal_info = $this->_get_record_bsc($this->input->post('employee_id'),$this->input->post('appraisal_period_id'));
		$employee_appraisal_id = $appraisal_info['employee_appraisal_id'];

		if ($employee_appraisal_id != '' || $employee_appraisal_id != NULL){
			//view
			$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_appraisal_history eah
										LEFT JOIN {$this->db->dbprefix}user u ON eah.viewed_by = u.user_id
									  	INNER JOIN (SELECT MAX(employee_appraisal_history_id) AS eaid 
									  		FROM hr_employee_appraisal_history WHERE viewed_by > 0 GROUP BY viewed_by) AS eahg 
									    	ON eah.employee_appraisal_history_id = eahg.eaid									
										WHERE employee_appraisal_id = ".$employee_appraisal_id."
										AND viewed_by > 0
									");
			$html = '<div>';
			$html .= '<div style="float:left"><h5>Viewed By</h3></div><div style="float:right;padding-right:10px"><h5>Date View</h3></div><br clear="all"><br />';
			if ($result && $result->num_rows() > 0){
				foreach ($result->result() as $row) {
					$html .= '<div style="float:left">'.$row->firstname.'&nbsp;'.$row->lastname.'</div><div style="float:right">'.date('d-M-Y',strtotime($row->date_created)).'</div>';
					$html .= '<br clear="all"><br /><div style="width:100%;border-bottom:1px solid black">&nbsp;</div><br />';
				}
			}

			//update
			$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_appraisal_history eah
										LEFT JOIN {$this->db->dbprefix}user u ON eah.updated_by = u.user_id
									  	INNER JOIN (SELECT MAX(employee_appraisal_history_id) AS eaid 
									  		FROM hr_employee_appraisal_history WHERE updated_by > 0 GROUP BY updated_by) AS eahg 
									    	ON eah.employee_appraisal_history_id = eahg.eaid									
										WHERE employee_appraisal_id = ".$employee_appraisal_id."
										AND updated_by > 0
									");

			$html .= '<div style="float:left"><h5>Updated By</h3></div><div style="float:right;padding-right:10px"><h5>Date Update</h3></div><br clear="all"><br />';
			if ($result && $result->num_rows() > 0){
				foreach ($result->result() as $row) {
					$html .= '<div style="float:left">'.$row->firstname.'&nbsp;'.$row->lastname.'</div><div style="float:right">'.date('d-M-Y',strtotime($row->date_created)).'</div>';
					$html .= '<br clear="all">';
				}
			}

			$html .= '</div>';
		}
		else{
			$html = '<div align="center">There is no appraisal yet.</div>';
		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		
	}

	function get_pending_pa(){
		$employee_id = $this->input->post('employee_id');
		$effectivity_date = ($this->input->post('date') != "" ? $this->input->post('date') : date('m/d/Y'));
		$effectivity_year = date('Y',strtotime($effectivity_date));

		$this->db->select('firstname,lastname,appraisal_year,employee_appraisal_bsc.appraisal_period,employee_appraisal_bsc.date_created');
		$this->db->where('employee_appraisal_bsc.deleted',0);
		$this->db->where('status <',3);
		$this->db->where(array("appraiser_id"=>$employee_id,"appraisal_year"=>$effectivity_year));
		$this->db->join('employee_appraisal_period', 'employee_appraisal_period.employee_appraisal_period_id = employee_appraisal_bsc.appraisal_period_id');
		$this->db->join('user', 'user.user_id = employee_appraisal_bsc.employee_id');
		$result = $this->db->get('employee_appraisal_bsc');

		if ($result && $result->num_rows() > 0){
			$html = '<div>';
			$html = '<h5>List of Pending Appraisal</h5><br />';
			$html = '<table style="width: 100%;" border="0" class="default-table boxtype" id="main">
						<thead>
							<tr>
								<th align="center">Name</td>
								<th align="center">Payroll Period</td>
								<th align="center">Year</td>
								<th align="center">Date Created</td>
							</tr>
						</thead>
					';
			foreach ($result->result() as $row) {
				$html .= '<tr>
							<td>'.$row->firstname.' '.$row->lastname.'</td>
							<td align="center">'.$row->appraisal_period.'</td>
							<td align="center">'.$row->appraisal_year.'</td>
							<td align="center">'.$row->date_created.'</td>
						  </tr>';
			}			
			$html .= '</table></div>';
		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}
	function get_comments()
	{		
		$appraisee	 = $this->input->post('appraisee');
		$criteria_id = $this->input->post('criteria_id');
		$period_id = $this->input->post('period_id');
		$appraisal_year = $this->input->post('appraisal_year');
		$question_id = $this->input->post('question_id');

		$response->msg = '';

		$this->db->where('period_id', $period_id);
		$this->db->where('employee_appraisal_criteria_id', $criteria_id);
		$this->db->where('employee_appraisal_criteria_question_id', $question_id);
		$this->db->where('appraisal_year', $appraisal_year);
		$this->db->where('appraisee_id', $appraisee);
		$this->db->where('user.inactive', 0);
		$this->db->where('user.deleted', 0);
		$this->db->join('user', 'user.user_id = appraisal_comment.user_id');
		$appraisal_comments = $this->db->get('appraisal_comment');

		$comments = "";

		if ($appraisal_comments && $appraisal_comments->num_rows() > 0) {
			$comments = $appraisal_comments->result();	
		};

		$response->comments = $comments;

		if ($this->input->post('boxy') == 1)
		{
			$data = array('comments' => $comments, 'criteria_id' => $criteria_id, 'appraisee' => $appraisee, 'period_id' => $period_id, 'appraisal_year' => $appraisal_year, 'question_id' => $question_id);
			$response->comment_box = $this->load->view($this->userinfo['rtheme'].'/admin/appraisal/comments_boxy', $data, TRUE);
		}

		$data['json'] = $response;

		$this->load->view('template/ajax', $data);
	}

	function ajax_save_comment()
	{
		if (IS_AJAX) {
			$insert['appraisal_comment'] = $this->input->post('comment');
			$insert['user_id'] = $this->input->post('user_id');
			$insert['appraisee_id'] = $this->input->post('appraisee_id');
			$insert['period_id'] = $this->input->post('period_id');
			$insert['employee_appraisal_criteria_id'] = $this->input->post('criteria_id');
			$insert['employee_appraisal_criteria_question_id'] = $this->input->post('question_id');
			$insert['appraisal_year'] = $this->input->post('appraisal_year');
			$date = date('Y-m-d');

			$this->db->insert('appraisal_comment', $insert);

			$response->msg = 'Data has been successfully saved.';
			$response->msg_type = 'success';
			
			$response->comment      = $this->input->post('comment');
			$response->created_date = date(
										$this->config->item('display_datetime_format'), 
										strtotime($date)
									);

			$this->db->where('user_id', $this->input->post('user_id'));

			$user = $this->db->get('user')->row();

			$response->name = $user->firstname . ' ' . $user->lastname;

			$data['json'] = $response;
			$this->load->view('template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());	
		}
		
	}

	function get_competency_value(){

		$html = '<option value="">Select... </option>';
		$competency_level = $this->db->get_where('appraisal_competency_value', array('deleted' => 0, 'appraisal_competency_master_id' => $this->input->post('competency_id') ));


		if( $competency_level->num_rows() > 0 ){
			// $selected = '';
			foreach( $competency_level->result() as $competency_info ){
				if ($this->input->post('competency_value') == $competency_info->competency_value_id) {
					$selected = "selected=selected";
				}else{
					$selected = '';
				}
				$html .= '<option '. $selected .'  value="'.$competency_info->competency_value_id.'" >'.$competency_info->competency_value.'</option>';

			}

		}

		$data['html'] = $html;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);


	}

	function get_resources(){

		$html = '';
		$resources = $this->input->post('resources_value');
		$competency = $this->input->post('master_id');

		switch ($this->input->post('plan_id')) {
			case 1: // self
				$html .= '<select class="required" cname="Support Needed" style="width: 327px" name="pdp_resources1['.$competency.'][]">
			                <option value=""> Select.. </option>
			            </select>';
				break;
			case 2: // others
				$html .= '<input type="text" placeholder="Who can help you?" class="required" cname="Support Needed" value="'.$resources.'" style="width: 320px" name="pdp_resources1['.$competency.'][]" />';
				break;
			case 3: // ojt
				$html .= '<input type="text" placeholder="What on-the-job experience/tasks do you neeed to do?" value="'.$resources.'" class="required" cname="Support Needed" style="width: 320px" name="pdp_resources1['.$competency.'][]"/>';
				break;
			case 4: // formal classroom
				$html .= '<select class="required" cname="Support Needed" style="width: 327px" name="pdp_resources1['.$competency.'][]">
			                <option value=""> Select.. </option>
			            </select>';
				break;
		}


		$data['html'] = $html;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);


	}

	function get_form()
	{
		if (IS_AJAX) {
			$data['master_id'] = $this->input->post('competency_id');
			$data['areas_for_development'] = $this->db->get_where('appraisal_areas_development', array('deleted' => 0))->result();
    		$data['development_plan'] = $this->db->get_where('appraisal_development_plan', array('deleted' => 0))->result();
    		$data['competency_level'] = $this->db->get_where('appraisal_competency_value', array('deleted' => 0, 'appraisal_competency_master_id' => $this->input->post('competency_id') ));
			$response = $this->load->view('employees/appraisal/pdp_form', $data);
			$data['html'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	private function _get_appraisal_planning($user_id, $period_id)
	{

		$this->db->where('employee_id', $user_id);
		$this->db->where('appraisal_planning_period_id', $period_id);
		$this->db->where('deleted', 0);
		$record = $this->db->get('employee_appraisal_planning');

		if ($record->num_rows() == 0) {
			return FALSE;
		} else {

			$this->db->where('employee_id', $user_id);
			$this->db->where('appraisal_period_id', $period_id);
			$this->db->where('deleted', 0);
			$appraisal_rec = $this->db->get('employee_appraisal_bsc');


			$result = $record->row_array();

			$result['employee_appraisal_criteria_array'] = ($result['employee_appraisal_criteria_array'] != '' ? unserialize($result['employee_appraisal_criteria_array']): '');
			$result['employee_appraisal_criteria_question_array'] = ($result['employee_appraisal_criteria_question_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_array']): '');

			$result['employee_appraisal_criteria_actual_question_array'] = ($result['employee_appraisal_criteria_actual_question_array'] != '' ? unserialize($result['employee_appraisal_criteria_actual_question_array']): '');

			$result['employee_appraisal_criteria_mid_year_comments'] = ($result['employee_appraisal_criteria_mid_year_comments'] != '' ? unserialize($result['employee_appraisal_criteria_mid_year_comments']): '');

			$result['employee_appraisal_competency_mid_comment_array'] = ($result['employee_appraisal_competency_mid_comment_array'] !=  '' ? unserialize($result['employee_appraisal_competency_mid_comment_array']): '');

			$result['employee_appraisal_competency_array'] = ($result['employee_appraisal_competency_array'] != '' ? unserialize($result['employee_appraisal_competency_array']): '');
			$result['employee_appraisal_expected_level_array'] = ($result['employee_appraisal_expected_level_array'] != '' ? unserialize($result['employee_appraisal_expected_level_array']): '');

			$result['employee_appraisal_coach_improvement'] = ($result['employee_appraisal_coach_improvement'] != '' ? unserialize($result['employee_appraisal_coach_improvement']): '');
			$result['employee_appraisal_coach_strength'] = ($result['employee_appraisal_coach_strength'] != '' ? unserialize($result['employee_appraisal_coach_strength']): '');
			$result['employee_appraisal_areas_improvement'] = ($result['employee_appraisal_areas_improvement'] != '' ? unserialize($result['employee_appraisal_areas_improvement']): '');
			$result['employee_appraisal_employee_strength'] = ($result['employee_appraisal_employee_strength'] != '' ? unserialize($result['employee_appraisal_employee_strength']): '');

			
			// $result['employee_appraisal_criteria_question_sec_rating_array'] = ($result['employee_appraisal_criteria_question_sec_rating_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_sec_rating_array']): '');
			$result['employee_appraisal_criteria_weight_array'] = ($result['employee_appraisal_criteria_weight_array'] != '' ? unserialize($result['employee_appraisal_criteria_weight_array']): '');
			$result['planning_status'] = $result['status'];
			$result['planning_period'] = $result['appraisal_planning_period_id'];
			$result['employee_appraisal_status'] = "";


			if ($appraisal_rec && $appraisal_rec->num_rows() > 0) {
				$appraisal = $appraisal_rec->row_array();
				unset($result['employee_appraisal_criteria_weight_array']);
				// $result = $appraisal;
				$result['employee_appraisal_id'] = $appraisal['employee_appraisal_id'];
				$result['position_id'] = $appraisal['position_id'];
				$result['rank_id'] = $appraisal['rank_id'];
				$result['position_class_id'] = $appraisal['position_class_id'];
				$result['department_id'] = $appraisal['department_id'];
				$result['rating'] = $appraisal['rating'];
				$result['coach_rating'] = $appraisal['coach_rating'];
				$result['final_rating'] = $appraisal['final_rating'];
				$result['division_id'] = $appraisal['division_id'];
				$result['employee_appraisal_status'] = $appraisal['employee_appraisal_status'];
				$result['employee_appraisal_justify'] = $appraisal['employee_appraisal_justify'];
				$result['employee_appraisal_recommended_action_array'] = $appraisal['employee_appraisal_recommended_action_array'];
				$result['employee_appraisal_criteria_actual_result_array'] = ($appraisal['employee_appraisal_criteria_actual_result_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_actual_result_array']): '');
				$result['employee_appraisal_criteria_rating_array'] = ($appraisal['employee_appraisal_criteria_rating_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_rating_array']): '');
				$result['employee_appraisal_criteria_achieved_array'] = ($appraisal['employee_appraisal_criteria_achieved_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_achieved_array']): '');
				$result['employee_appraisal_criteria_weight_average_array'] = ($appraisal['employee_appraisal_criteria_weight_average_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_weight_average_array']): '');

				$result['employee_appraisal_criteria_self_rating_array'] = ($appraisal['employee_appraisal_criteria_self_rating_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_self_rating_array']): '');
				$result['employee_appraisal_criteria_self_achieved_array'] = ($appraisal['employee_appraisal_criteria_self_achieved_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_self_achieved_array']): '');
				$result['employee_appraisal_criteria_self_weight_average_array'] = ($appraisal['employee_appraisal_criteria_self_weight_average_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_self_weight_average_array']): '');


				$result['employee_appraisal_criteria_year_end_comments'] = ($appraisal['employee_appraisal_criteria_year_end_comments'] != '' ? unserialize($appraisal['employee_appraisal_criteria_year_end_comments']): '');

				$result['employee_appraisal_criteria_weight_array'] = ($appraisal['employee_appraisal_criteria_weight_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_weight_array']): '');
				$result['employee_appraisal_criteria_actual_level_array'] = ($appraisal['employee_appraisal_criteria_actual_level_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_actual_level_array']): '');
				$result['employee_appraisal_criteria_rating_weight_array'] = ($appraisal['employee_appraisal_criteria_rating_weight_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_rating_weight_array']): '');
				$result['employee_appraisal_criteria_self_rating_weight_array'] = ($appraisal['employee_appraisal_criteria_self_rating_weight_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_self_rating_weight_array']): '');
				$result['employee_appraisal_criteria_self_weighted_score_array'] = ($appraisal['employee_appraisal_criteria_self_weighted_score_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_self_weighted_score_array']): '');
				$result['employee_appraisal_criteria_weighted_score_array'] = ($appraisal['employee_appraisal_criteria_weighted_score_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_weighted_score_array']): '');
				$result['employee_appraisal_criteria_actual_level_comment_array'] = ($appraisal['employee_appraisal_criteria_actual_level_comment_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_actual_level_comment_array']): '');
				$result['employee_appraisal_criteria_core_rating_array'] = ($appraisal['employee_appraisal_criteria_core_rating_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_core_rating_array']): '');
				$result['employee_appraisal_or_raters_comments'] = ($appraisal['employee_appraisal_or_raters_comments'] != '' ? unserialize($appraisal['employee_appraisal_or_raters_comments']): '');
				$result['employee_appraisal_raters_comments'] = ($appraisal['employee_appraisal_raters_comments'] != '' ? unserialize($appraisal['employee_appraisal_raters_comments']): '');
				$result['employee_appraisal_or_ratees_remarks'] = $appraisal['employee_appraisal_or_ratees_remarks'];
				$result['employee_appraisal_or_ratees_comments'] = $appraisal['employee_appraisal_or_ratees_comments'];
				// $result['employee_appraisal_or_raters_sign_date'] = $appraisal['employee_appraisal_or_raters_sign_date'];
				$result['employee_appraisal_or_raters_sign_date'] = ($appraisal['employee_appraisal_or_raters_sign_date'] != '' ? unserialize($appraisal['employee_appraisal_or_raters_sign_date']): '');
				$result['employee_appraisal_or_rates_sign_date'] = $appraisal['employee_appraisal_or_rates_sign_date'];
				$result['email_sent_from_appraiser'] = $appraisal['email_sent_from_appraiser'];
				$result['employee_appraisal_or_div_dep_comments']  = $appraisal['employee_appraisal_or_div_dep_comments'];
				$result['employee_appraisal_or_gen_comments'] = $appraisal['employee_appraisal_or_gen_comments'];
				
				$result['employee_appraisal_pdp_comp1_array'] = ($appraisal['employee_appraisal_pdp_comp1_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_comp1_array']): '');
				$result['employee_appraisal_pdp_ds1_array'] = ($appraisal['employee_appraisal_pdp_ds1_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_ds1_array']): '');
				$result['employee_appraisal_pdp_resources1_array'] = ($appraisal['employee_appraisal_pdp_resources1_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_resources1_array']): '');
				$result['employee_appraisal_pdp_dp1_array'] = ($appraisal['employee_appraisal_pdp_dp1_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_dp1_array']): '');
				$result['employee_appraisal_pdp_val1_array'] = ($appraisal['employee_appraisal_pdp_val1_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_val1_array']): '');

				$result['employee_appraisal_pdp_comp2_array'] = ($appraisal['employee_appraisal_pdp_comp2_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_comp2_array']): '');
				$result['employee_appraisal_pdp_ds2_array'] = ($appraisal['employee_appraisal_pdp_ds2_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_ds2_array']): '');
				$result['employee_appraisal_pdp_resources2_array'] = ($appraisal['employee_appraisal_pdp_resources2_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_resources2_array']): '');
				$result['employee_appraisal_pdp_dp2_array'] = ($appraisal['employee_appraisal_pdp_dp2_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_dp2_array']): '');
				$result['employee_appraisal_pdp_val2_array'] = ($appraisal['employee_appraisal_pdp_val2_array'] != '' ? unserialize($appraisal['employee_appraisal_pdp_val2_array']): '');
				
				$result['employee_appraisal_criteria_question_mprc_array'] = ($appraisal['employee_appraisal_criteria_question_mprc_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_question_mprc_array']): '');
				$result['employee_appraisal_criteria_question_ypac_array'] = ($appraisal['employee_appraisal_criteria_question_ypac_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_question_ypac_array']): '');
				

				$result['final_approval_remarks'] = $appraisal['final_approval_remarks'];
				$result['attachment'] = $appraisal['attachment'];
				$result['employee_appraisal_criteria_question_overal_rating_array'] = ($appraisal['employee_appraisal_criteria_question_overal_rating_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_question_overal_rating_array']): '');
				$result['employee_appraisal_criteria_question_sec_rating_array'] = ($appraisal['employee_appraisal_criteria_question_sec_rating_array'] != '' ? unserialize($appraisal['employee_appraisal_criteria_question_sec_rating_array']): '');
				$result['employee_appraisal_or_total_score'] = $appraisal['employee_appraisal_or_total_score'];

			}
			// dbug($result);die();
			return $result;
		}
	}

	function calculate_final_rating(){

		$final_rating = 0.00;
		$total_score = $this->input->post('total_score');

		$this->db->where('employee_appraisal_scale.deleted',0);
        $this->db->where('employee_appraisal_criteria.is_core', 0);
        $this->db->order_by('employee_appraisal_scale.appraisal_scale_id', 'DESC');
       /* $this->db->group_by('employee_appraisal_rating_scale.appraisal_scale_id', 'DESC');*/
        $this->db->join('employee_appraisal_rating_scale', 'employee_appraisal_rating_scale.appraisal_scale_id=employee_appraisal_scale.appraisal_scale_id');
        $this->db->join('employee_appraisal_criteria', 'employee_appraisal_criteria.employee_appraisal_criteria_id=employee_appraisal_rating_scale.employee_appraisal_criteria_id');
        $appraisal_scale = $this->db->get('employee_appraisal_scale');

        if ($appraisal_scale && $appraisal_scale->num_rows() > 0){
        	$scale_array = array();
        	foreach ($appraisal_scale->result() as $rate => $scale) {

        		if( $total_score >= $scale->weight_score_from && $final_rating <= $scale->rating_scale ){

        			$final_rating = $scale->rating_scale;

        		}

        	}
        	 	
        }

        $response->final_rating = $final_rating; 
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);

	}


	function get_rating_scale()
	{
		$this->db->where('employee_appraisal_scale.deleted',0);
		$this->db->where('employee_appraisal_rating_scale.deleted',0);
        $this->db->where('employee_appraisal_criteria.is_core', 0);
        //$this->db->order_by('employee_appraisal_scale.appraisal_scale_id', 'ASC');
        $this->db->order_by('rating_scale', 'DESC');
       /* $this->db->group_by('employee_appraisal_rating_scale.appraisal_scale_id', 'DESC');*/
        $this->db->join('employee_appraisal_rating_scale', 'employee_appraisal_rating_scale.appraisal_scale_id=employee_appraisal_scale.appraisal_scale_id');
        $this->db->join('employee_appraisal_criteria', 'employee_appraisal_criteria.employee_appraisal_criteria_id=employee_appraisal_rating_scale.employee_appraisal_criteria_id');
        $appraisal_scale = $this->db->get('employee_appraisal_scale');

        if ($appraisal_scale && $appraisal_scale->num_rows() > 0){
        	$scale_array = array();
        	foreach ($appraisal_scale->result() as $rate => $scale) {
        		$scale_array['qualitative'][$scale->appraisal_scale_id] = $scale->appraisal_scale;
        		$scale_array['criteria_standard'][$scale->appraisal_scale_id] = $scale->description;
        		$scale_array['quantitative'][$scale->appraisal_scale_id][] = $scale->rating_scale;
        		$scale_array['weighted_score'][$scale->appraisal_scale_id][] = $scale->total_weight_score;
        		$scale_array['weighted_score_from'][$scale->appraisal_scale_id][] = $scale->weight_score_from;
        		$scale_array['weighted_score_to'][$scale->appraisal_scale_id][] = $scale->weight_score_to;
        	}

        	return $scale_array;
        	 	
        }else{
        	return false;
        }
	}
	
}

/* End of file */
/* Location: system/application */