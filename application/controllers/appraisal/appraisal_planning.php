<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Appraisal_planning extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists Core values.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about Core values';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about Core values';

    }

	// START - default module functions
	// default jqgrid controller method
	function index($period_id = NULL)
    {

    	if (is_null($period_id)) {
			redirect('appraisal/appraisal_planning_period');
    	}

		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
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
		$data['content'] = 'employees/appraisal/planning/detailview';

		if (is_null($user_id) || is_null($period_id) || $user_id <= 0 || $period_id <= 0) {
			$this->session->set_flashdata('flashdata', 'Invalid parameters.');
			redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));
		}

		if($this->user_access[$this->module_id]['edit'] == 1){
			$this->db->where('planning_period_id', $period_id);
			$this->db->where('appraisal_planning_period.deleted', 0);

			$period = $this->db->get('appraisal_planning_period');

			if ($period->num_rows() == 0) {
				$this->session->set_flashdata('flashdata', 'Period not defined.');
				redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));				
			}

			$data['period'] = $period->row();


			$record = $this->_get_record_bsc($user_id, $period_id);
			$user = $this->get_appraisee($user_id, $record);

			$data['appraisee'] = $user;

			$org = $this->db->get_where('user_company_segment_2', array('segment_2_id' => $user['segment_2_id']));
			if ($org && $org->num_rows() > 0) {
				$data['organization'] = $org->row()->segment_2;
			}
			$approver = $this->system->get_approvers_and_condition($user_id,$this->module_id);

			$appraiser_id = $approver[0]['approver']; //$this->system->get_reporting_to($user_id);

			$this->db->select(array('user.*', 'position'));
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user.position_id = user_position.position_id', 'left');			
			$this->db->where(array('user.user_id' => $appraiser_id, 'user.deleted' => 0));

			$data['appraiser'] = $this->db->get('user')->row();

			$appraiser_direct_superior_user_id = $this->system->get_reporting_to($appraiser_id);

			$data['appraiser_direct_superior'] = $this->system->get_employee($appraiser_direct_superior_user_id);
			$template = $this->_get_appraisal_criteria($user);

			if (!$template) {
                $this->session->set_flashdata('flashdata', 'No template has been set for this position.');
                redirect(base_url() . 'appraisal/appraisal_planning_period');
            }

            $data['form'] = $template;

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
            		}
            	}

            	if ($row->is_core) {
            		$rating_scale = $this->db->get_where('appraisal_core_value_rating', array('deleted' => 0));
            		if ($rating_scale && $rating_scale->num_rows() > 0){            	
            		foreach ($rating_scale->result() as $rscale) {
            			// if ($rscale->rating_scale_title != ''){
	            			$ratingscale[$row->employee_appraisal_criteria_id]['title'][] = $rscale->definition_rating;
	            			// }
	            			$ratingscale[$row->employee_appraisal_criteria_id]['scale'][] = $rscale->rating;
	            		}            		
	            	}
            	}else{
            		$this->db->where('employee_appraisal_criteria_id',$row->employee_appraisal_criteria_id);
	            	$this->db->where('deleted',0);
	            	$rating_scale = $this->db->get('employee_appraisal_rating_scale');
	            	if ($rating_scale && $rating_scale->num_rows() > 0){            	
            		foreach ($rating_scale->result() as $rscale) {
            			// if ($rscale->rating_scale_title != ''){
	            			$ratingscale[$row->employee_appraisal_criteria_id]['title'][] = $rscale->rating_scale_title;
	            			// }
	            			$ratingscale[$row->employee_appraisal_criteria_id]['scale'][] = $rscale->rating_scale;
	            		}            		
	            	}
	            	
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
            			$columnames[$row->employee_appraisal_criteria_id]['column_code'][] = $column_name->column_code;
            			$columnames[$row->employee_appraisal_criteria_id]['column_name_header'][] = $column_name->column_name_header;            			
            			$columnames[$row->employee_appraisal_criteria_id]['column_name'][] = $column_name->column_name;
            			$columnames[$row->employee_appraisal_criteria_id]['column_type'][] = $column_name->employee_appraisal_criteria_column_type_id;
            			$columnames[$row->employee_appraisal_criteria_id]['payroll_period'][] = $column_name->appraisal_period;
            			$columnames[$row->employee_appraisal_criteria_id]['field_required'][] = $column_name->required;
            			$columnames[$row->employee_appraisal_criteria_id]['column_tooltip'][] = $column_name->column_tooltip;
            			$columnames[$row->employee_appraisal_criteria_id]['class'][] = $column_name->class;
            		}
            	}    

            	$core[$row->employee_appraisal_criteria_id] = $this->core_values($row->competency_master_id);        	
            }

            $data['rating_scale'] = $ratingscale;

            $data['criteria_questions'] = $criterias;

            $data['criteria_columns'] = $columnames;

			$employee_appraisal_settings = $this->hdicore->_get_config('employee_appraisal_settings');

            $data['multiplier'] = $employee_appraisal_settings['multiplier'];

            $data['core_values'] = $core;
            // $data['period_count'] = $this->_get_period_type_count($employee_appraisal_settings['periods']);

            $get_rating_scale = $this->get_rating_scale();
			$data['criteria_questions_options'] = (is_array($get_rating_scale)) ? $get_rating_scale : array();

			// $record = $this->_get_record_bsc($user_id, $period_id);
			$rater = false;
			$is_rater = false;
			
			if ($record) {

				$data['record_id'] = $record['employee_appraisal_planning_id'];
                $data['record']    = $record;

                if( $record['planning_status'] == 2 ){

	                $this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
					$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_planning_id']);
					$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
					$this->db->order_by('sequence','asc');
					$approver_result = $this->db->get('employee_appraisal_approver');

					$data['approvers'] = $approver_result;

					if( $approver_result->num_rows() > 0 ){

						foreach( $approver_result->result() as $approver_info ){

							if( $approver_info->status == 2 && $approver_info->approver == $this->userinfo['user_id'] ){
								$rater = true;
							}
							else{

								$this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
								$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_planning_id']);
								$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
								$this->db->where('status',0);
								$this->db->where('focus',0);
								$this->db->order_by('sequence','asc');
								$this->db->limit(1);
								$approver_result = $this->db->get('employee_appraisal_approver');

								if( $approver_result->num_rows() > 0 ){

									$approver_info = $approver_result->row();

									if( $approver_info->approver == $this->userinfo['user_id'] ){

										$rater = true;
									}


								}

							}
						}
					}

				}
				elseif( $record['planning_status'] == 3 ){

					$this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
					$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_planning_id']);
					$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
					$this->db->where('approver',$this->userinfo['user_id']);
					$this->db->order_by('sequence','asc');
					$approver_result = $this->db->get('employee_appraisal_approver');

					if( $approver_result->num_rows() > 0 ){

						$is_rater = true;

					}

				}
				

			} else {
				$data['record_id'] = '-1';
			}

			$data['rater'] = $rater;
			$data['is_rater'] = $is_rater;

			if ($user_id == $this->userinfo['user_id']){
				$data['personal'] = true;
			}
			else{
				$data['personal'] = false;	
				// if ($record && $record['planning_status'] == 1){
				// 	$this->session->set_flashdata('flashdata', 'Insufficient data supplied');
    //             	redirect(base_url() . 'appraisal/appraisal_planning_period');
				// }
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
		}
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

	function edit($user_id, $period_id, $duplicate_id = 0)
	{
		if (is_null($user_id) || is_null($period_id) || $user_id <= 0 || $period_id <= 0) {
			$this->session->set_flashdata('flashdata', 'Invalid parameters.');
			redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));
		}

		if($this->user_access[$this->module_id]['edit'] == 1){
			parent::edit();

			$this->db->where('planning_period_id', $period_id);
			$this->db->where('appraisal_planning_period.deleted', 0);

			$period = $this->db->get('appraisal_planning_period');

			if ($period->num_rows() == 0) {
				$this->session->set_flashdata('flashdata', 'Period not defined.');
				redirect(base_url().$this->module_link . '/index/' . $this->uri->segment(4));				
			}

			$data['period'] = $period->row();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'employees/appraisal/planning/editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$record = $this->_get_record_bsc($user_id, $period_id);
			$user = $this->get_appraisee($user_id, $record);

			$data['appraisee'] = $user;

			$approver = $this->system->get_approvers_and_condition($user_id,$this->module_id);

			$appraiser_id = $approver[0]['approver']; 

			$this->db->select(array('user.*', 'position'));
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user.position_id = user_position.position_id', 'left');			
			$this->db->where(array('user.user_id' => $appraiser_id, 'user.deleted' => 0));

			$data['appraiser'] = $this->db->get('user')->row();

			$appraiser_direct_superior_user_id = $this->system->get_reporting_to($appraiser_id);

			$data['appraiser_direct_superior'] = $this->system->get_employee($appraiser_direct_superior_user_id);
			$template = $this->_get_appraisal_criteria($user);

			if (!$template) {
                $this->session->set_flashdata('flashdata', 'No template has been set for this position classification.');
                redirect(base_url() . 'appraisal/appraisal_planning_period');
            }

            $data['form'] = $template;

            foreach ($template->result() as $row) {
            	$this->db->where('employee_appraisal_criteria_question.employee_appraisal_criteria_id',$row->employee_appraisal_criteria_id);
            	$this->db->where('employee_appraisal_criteria_question.deleted',0); 
            	$questions = $this->db->get('employee_appraisal_criteria_question');
            
            	if ($questions && $questions->num_rows() > 0){
            		foreach ($questions->result() as $question) {
            			if ($question->header_text != ''){
            				$criterias[$row->employee_appraisal_criteria_id]['headers'][$question->employee_appraisal_criteria_question_id] = $question->header_text;
            			}
            			$criterias[$row->employee_appraisal_criteria_id]['tooltip'][$question->employee_appraisal_criteria_question_id] = $question->tooltip;
            			$criterias[$row->employee_appraisal_criteria_id]['questions'][$question->employee_appraisal_criteria_question_id] = $question->question;
            			$criterias[$row->employee_appraisal_criteria_id]['placeholder'][$question->employee_appraisal_criteria_question_id][$question->employee_appraisal_criteria_column_id] = $question->placeholder;
            			$criterias[$row->employee_appraisal_criteria_id]['deleted'][$question->employee_appraisal_criteria_question_id] = $question->deleted;            			
            		}
            	}

            	if ($row->is_core) {
            		$rating_scale = $this->db->get_where('appraisal_core_value_rating', array('deleted' => 0));
            		if ($rating_scale && $rating_scale->num_rows() > 0){            	
            		foreach ($rating_scale->result() as $rscale) {
	            			$ratingscale[$row->employee_appraisal_criteria_id]['title'][] = $rscale->definition_rating;
	            			$ratingscale[$row->employee_appraisal_criteria_id]['scale'][] = $rscale->rating;
	            		}            		
	            	}
            	}else{
            		$this->db->where('employee_appraisal_criteria_id',$row->employee_appraisal_criteria_id);
	            	$this->db->where('deleted',0);
	            	$rating_scale = $this->db->get('employee_appraisal_rating_scale');
	            	if ($rating_scale && $rating_scale->num_rows() > 0){            	
            		foreach ($rating_scale->result() as $rscale) {
	            			$ratingscale[$row->employee_appraisal_criteria_id]['title'][] = $rscale->rating_scale_title;
	            			$ratingscale[$row->employee_appraisal_criteria_id]['scale'][] = $rscale->rating_scale;
	            		}            		
	            	}
	            	
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
            			$columnames[$row->employee_appraisal_criteria_id]['column_code'][] = $column_name->column_code;
            			$columnames[$row->employee_appraisal_criteria_id]['column_id'][] = $column_name->employee_appraisal_criteria_column_id;
            			$columnames[$row->employee_appraisal_criteria_id]['column_name_header'][] = $column_name->column_name_header;            			
            			$columnames[$row->employee_appraisal_criteria_id]['column_name'][] = $column_name->column_name;
            			$columnames[$row->employee_appraisal_criteria_id]['column_type'][] = $column_name->employee_appraisal_criteria_column_type_id;
            			$columnames[$row->employee_appraisal_criteria_id]['payroll_period'][] = $column_name->appraisal_period;
            			$columnames[$row->employee_appraisal_criteria_id]['field_required'][] = $column_name->required;
            			$columnames[$row->employee_appraisal_criteria_id]['column_tooltip'][] = $column_name->column_tooltip;
            			$columnames[$row->employee_appraisal_criteria_id]['class'][] = $column_name->class;
            		}
            	}         

            	$core[$row->employee_appraisal_criteria_id] = $this->core_values($row->competency_master_id);   	
            }

            $data['rating_scale'] = $ratingscale;

            $data['criteria_questions'] = $criterias;

            $data['criteria_columns'] = $columnames;

			$employee_appraisal_settings = $this->hdicore->_get_config('employee_appraisal_settings');

            $data['multiplier'] = $employee_appraisal_settings['multiplier'];

            $data['core_values'] = $core;
            // $data['period_count'] = $this->_get_period_type_count($employee_appraisal_settings['periods']);

	        $get_rating_scale = $this->get_rating_scale();
			$data['criteria_questions_options'] = (is_array($get_rating_scale)) ? $get_rating_scale : array();

            $rater = false;
				
			if ($record) {

				$data['record_id'] = $record['employee_appraisal_planning_id'];
                $data['record']    = $record;


                if( $record['planning_status'] == 2 || $record['planning_status'] == 3 ){

	                $this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
					$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_planning_id']);
					$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
					$this->db->order_by('sequence','asc');
					$approver_result = $this->db->get('employee_appraisal_approver');

					$data['approvers'] = $approver_result;

					if( $approver_result->num_rows() > 0 ){

						foreach( $approver_result->result() as $approver_info ){

							if( ($approver_info->status == 2 || $approver_info->status == 3) && $approver_info->approver == $this->userinfo['user_id'] ){
								$rater = true;
							}
							else{

								$this->db->join('user','user.employee_id = employee_appraisal_approver.approver','left');
								$this->db->where('employee_appraisal_approver.record_id',$record['employee_appraisal_planning_id']);
								$this->db->where('employee_appraisal_approver.module_id',$this->module_id);
								$this->db->where('status',0);
								$this->db->where('focus',1);
								$this->db->order_by('sequence','asc');
								$this->db->limit(1);
								$approver_result = $this->db->get('employee_appraisal_approver');

								if( $approver_result->num_rows() > 0 ){

									$approver_info = $approver_result->row();

									if( $approver_info->approver == $this->userinfo['user_id'] ){

										$rater = true;
									}


								}

							}
						}
					}

				}
			
			} else {
				$data['record_id'] = '-1';
				if ($duplicate_id > 0) {
					$duplicate_record = $this->_get_record_bsc($user_id, $duplicate_id);
					$duplicate_record['planning_status'] = 1;
					$data['record']    = $duplicate_record;

				}

			}

			$data['rater'] = $rater;

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

		$this->key_field = 'employee_appraisal_planning_id';

		// Save appraisal planning info		

		$record_id = $this->input->post('record_id');
		$data['status'] = $this->input->post('status');

		if( $this->input->post('status') == 4 && $this->user_access[$this->module_id]['post']){

			$this->db->where('record_id',$record_id);
			$this->db->where('module_id',$this->module_id);
			$this->db->update('employee_appraisal_approver',array('focus'=>0,'status'=>0));

			// update 1st approver status
			$this->db->where('record_id',$record_id);
			$this->db->where('module_id',$this->module_id);
			$this->db->where('sequence',1);
			$this->db->update('employee_appraisal_approver',array('focus'=>1,'status'=>0));

			$appraisee_id = $this->input->post('employee_id');
			$appraisal_planning_period_id = $this->input->post('period_id');

			$emp = $this->db->get_where('user', array('user_id' => $this->input->post('employee_id')))->row_array();
			$this->_send_email($this->input->post('period_id'), $this->input->post('employee_id'), 'discussed_to_ratee', $emp['email'], array());
		}

		$data['appraiser_id']   	   									= $this->input->post('appraiser_id');
		$data['appraisal_planning_period_id'] 							= $this->input->post('period_id');
		$data['employee_id']   		   									= $this->input->post('employee_id');
		$data['position_id']   		   									= $this->input->post('position_id');
		$data['rank_id']   		   										= $this->input->post('rank_id');
		$data['position_class_id']   		   							= $this->input->post('position_class_id');
		$data['department_id']   		   								= $this->input->post('department_id');
		$data['division_id']   		   									= $this->input->post('division_id');
		$data['last_appraisal_date']   		   							= date('Y-m-d', strtotime($this->input->post('last_appraisal_date')));

		if( $this->input->post('criteria') != "" ){
			$data['employee_appraisal_criteria_array']						= serialize($this->input->post('criteria'));
		}
		if( $this->input->post('actual') != "" ){
			$data['employee_appraisal_criteria_actual_question_array']		= serialize($this->input->post('actual'));
		}
		if( $this->input->post('cq') != "" ){
			$data['employee_appraisal_criteria_question_array']				= serialize($this->input->post('cq'));
		}
		if( $this->input->post('item_name') != "" ){
			$data['employee_appraisal_criteria_question_item']				= serialize($this->input->post('item_name'));
		}
		if( $this->input->post('section_rating') != "" ){
			$data['employee_appraisal_criteria_question_sec_rating_array']	= serialize($this->input->post('section_rating'));
		}
		if( $this->input->post('attendance_mid_year') != "" ){
			$data['employee_appraisal_competency_mid_comment_array']		= serialize($this->input->post('attendance_mid_year'));
		}
		if( $this->input->post('competency') != "" ){
			$data['employee_appraisal_competency_array']					= serialize($this->input->post('competency'));
		}
		if( $this->input->post('expected_level') != "" ){
			$data['employee_appraisal_expected_level_array']				= serialize($this->input->post('expected_level'));
		}
		if( $this->input->post('weight') != "" ){
			$data['employee_appraisal_criteria_weight_array']				= serialize($this->input->post('weight'));
		}
		if( $this->input->post('employee_strength') != "" ){
			$data['employee_appraisal_employee_strength']				    = serialize($this->input->post('employee_strength'));
		}
		if( $this->input->post('areas_improvement') != "" ){
			$data['employee_appraisal_areas_improvement']				    = serialize($this->input->post('areas_improvement'));
		}
		if( $this->input->post('coach_strength') != "" ){
			$data['employee_appraisal_coach_strength']				        = serialize($this->input->post('coach_strength'));
		}
		if( $this->input->post('coach_improvement') != "" ){
			$data['employee_appraisal_coach_improvement']				    = serialize($this->input->post('coach_improvement'));
		}
		

		if( $this->input->post('period_id') > 0 ){
			$appraisal_planning_period_result = $this->db->get_where('appraisal_planning_period',array('planning_period_id'=>$this->input->post('period_id')));
		
			if( $appraisal_planning_period_result->num_rows() > 0 ){

				$planning_period_info = $appraisal_planning_period_result->row();

				if( strtotime(date('Y-m-d')) >= strtotime($planning_period_info->mid_date_from) && strtotime(date('Y-m-d')) <= strtotime($planning_period_info->mid_date_to) ){

					if( $this->input->post('mid_year_comments') != "" ){

						$data['employee_appraisal_criteria_mid_year_comments']			= serialize($this->input->post('mid_year_comments'));
					}
				}
			}
		}

		

		$items = $this->input->post('item_name');

		$cat_item['appraisee_id']					= $data['employee_id'];
		$cat_item['appraisal_planning_id']			= $data['appraisal_planning_period_id'];
		
		// parent::ajax_save();

		//additional module save routine here

		$this->db->set($data);

		if ($record_id > '0') {
			$this->db->where('employee_appraisal_planning_id', $record_id);
			$this->db->update('employee_appraisal_planning');
			$this->key_field_val = $record_id;

		} else {
			$this->db->where('employee_id',$data['employee_id']);
			$this->db->where('appraisal_planning_period_id',$data['appraisal_planning_period_id']);
			$result = $this->db->get('employee_appraisal_planning');

			if ($result && $result->num_rows() > 0) {
				$row_planning = $result->row();
				$this->db->where('employee_appraisal_planning_id', $row_planning->employee_appraisal_planning_id);
				$this->db->update('employee_appraisal_planning');
				$this->key_field_val = $row_planning->employee_appraisal_planning_id;
			}
			else {
				$this->db->insert('employee_appraisal_planning');
				$this->key_field_val = $this->db->insert_id();
			}
		}
		
		$response->msg = 'Data has been successfully saved.';
		$response->msg_type = 'success';
		$response->record_id = $this->key_field_val;
		$this->set_message($response);
		$this->after_ajax_save();

		
	}


	protected function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			if ($this->input->post('record_id') == '-1') {
				$created['created_date'] = date('Y-m-d H:i:s');
				$created['created_by']   = $this->userinfo['user_id'];

				$this->db->where('employee_appraisal_planning_id', $this->key_field_val);
				$this->db->update('employee_appraisal_planning', $created);


				$approvers = $this->system->get_approvers_and_condition($this->input->post('employee_id'), $this->module_id);

                foreach ($approvers as $key => $value) {
					$value['record_id'] = $this->key_field_val;
					$value['module_id'] = $this->module_id;
					$value['approver'] = $value['approver'];
					$value['sequence'] = $value['sequence'];
					// $value['focus'] = 0;
					$value['status'] = 0;
			
					$this->db->insert('employee_appraisal_approver', $value);
				}
			}

			if ($this->input->post('status') == 2 && ($this->userinfo['user_id'] == $this->input->post('employee_id'))) {
				$appraisee_id = $this->input->post('employee_id');
				$appraisal_planning_period_id = $this->input->post('period_id');

				//approver 
				$this->db->where('record_id', $this->key_field_val);
				$this->db->where('module_id',$this->module_id);
				$this->db->where('sequence',1);
				$this->db->where('focus',1);
				$coach = $this->db->get('employee_appraisal_approver')->row();
				$rater = $this->system->get_employee($coach->approver);
				
				
				$this->_send_email($appraisal_planning_period_id, $appraisee_id, 'appraisal_planning_to_rater', $rater['email'], $rater);
				

			}

		}

		parent::after_ajax_save();
	}

	function send_email()
	{

		if (IS_AJAX) {
			$record_id = $this->input->post('record_id');

			$this->db->where('employee_appraisal_planning_id', $record_id);
			$this->db->where('deleted', 0);
			$record = $this->db->get('employee_appraisal_planning');

			if (!$record || $record->num_rows() == 0) {
				$response->msg 	    = 'Record not found. Appraisal was not sent.';
				$response->msg_type = 'error';
			} else {
				// Send email.
                // Load the template.       
				$record = $record->row();
                $this->db->where('approver', $this->userinfo['user_id']);
				$this->db->where('module_id',$this->module_id);
				$this->db->where('record_id', $record_id);
				$this->db->update('employee_appraisal_approver', array('status' => 3, 'focus' => 2));

				$this->db->where('module_id',$this->module_id);
				$this->db->where('record_id', $record_id);
				$this->db->where('status !=',3);
				$this->db->order_by('sequence','asc');
				$approver_result = $this->db->get('employee_appraisal_approver');

				if($approver_result && $approver_result->num_rows() > 0 ){

					$approver_info = $approver_result->row();

					$this->db->where('record_id',$approver_info->record_id);
					$this->db->where('module_id',$this->module_id);
					$this->db->where('approver',$approver_info->approver);
					$this->db->update('employee_appraisal_approver',array('status'=>2, 'focus' => 2));

					$rater = $this->system->get_employee($approver_info->approver);
					$this->_send_email($record->appraisal_planning_period_id, $record->employee_id, 'appraisal_planning_to_rater', $rater['email'], $rater);

				}
				else{

					$this->db->where('employee_appraisal_planning_id',$record_id);
					$this->db->where('deleted',0);
					$this->db->update('employee_appraisal_planning',array('status'=>3));

					$emp = $this->db->get_where('user', array('user_id' => $record->employee_id))->row_array();

					$this->_send_email($record->appraisal_planning_period_id, $record->employee_id, 'ig_employee_notif', $emp['email'], array());
				}

			}
			
		} else {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());
		}
	}

	function _send_email($appraisal_planning_period_id, $appraisee_id, $template_code, $recepient, $rater)
	{
		$this->load->model('template');

		$period_info = $this->db->get_where('appraisal_planning_period', array('planning_period_id' => $appraisal_planning_period_id));
		
		$appraisee_info_result = $this->db->get_where('user',array("user_id"=>$appraisee_id));
		if ($appraisee_info_result && $appraisee_info_result->num_rows() > 0){
			$appraisee_info = $appraisee_info_result->row_array();
		}

		$template = $this->template->get_module_template(0, $template_code);

		$recepients[] = $recepient;
		$request['appraisee'] = $appraisee_info['firstname']." ".$appraisee_info['lastname'];
        $request['rater'] = $rater['firstname']." ".$rater['lastname']; 	
        $request['period'] = $period_info->row()->planning_period;
        $request['year'] = $period_info->row()->year;
		$request['here']=base_url().'appraisal/appraisal_planning/edit/'.$appraisee_id.'/'.$appraisal_planning_period_id;
		
		$message = $this->template->prep_message($template['body'], $request);
	    $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);

	    $response->msg = 'Appraisal sent';
		$response->msg_tpe = 'success'; 
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}

	// END - default module functions

	// START custom module funtions
	function get_appraisee($user_id, $record)
	{
		$user = $this->system->get_employee($user_id);

		$user['last_appraisal_date'] = '0000-00-00';

		$this->db->select('MAX(final_approval_date) as last_pa_date', false);
		$this->db->where('employee_id', $user_id);
		$last_pa = $this->db->get('employee_appraisal_bsc');

		if ($last_pa && $last_pa->num_rows() > 0) {
			$last_pa = $last_pa->row();
			$user['last_appraisal_date'] = $last_pa->last_pa_date;
		}

		if ($record) {

			$user['last_appraisal_date'] = $record['last_appraisal_date'];
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

		
		if ($has_appraisal) {
			$this->db->where('employee_appraisal_id !=',$record['employee_appraisal_id']);
		}
		

			
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

	function get_comments()
	{		
		$appraisee	 = $this->input->post('appraisee');
		$criteria_id = $this->input->post('criteria_id');
		$question_id = $this->input->post('question_id');
		$period_id = $this->input->post('period_id');
		$appraisal_year = $this->input->post('appraisal_year');

		$response->msg = '';

		$this->db->where('period_id', $period_id);
		$this->db->where('employee_appraisal_criteria_id', $criteria_id);
		$this->db->where('employee_appraisal_criteria_question_id', $question_id);
		$this->db->where('appraisal_year', $appraisal_year);
		$this->db->where('appraisee_id', $appraisee);
		$this->db->where('user.inactive', 0);
		$this->db->where('user.deleted', 0);
		$this->db->join('user', 'user.user_id = appraisal_planning_comment.user_id');
		$appraisal_comments = $this->db->get('appraisal_planning_comment');

		$comments = "";

		if ($appraisal_comments && $appraisal_comments->num_rows() > 0) {
			$comments = $appraisal_comments->result();	
		};

		$response->comments = $comments;

		if ($this->input->post('boxy') == 1)
		{
			$data = array('comments' => $comments, 'criteria_id' => $criteria_id, 'appraisee' => $appraisee, 'period_id' => $period_id, 'appraisal_year' => $appraisal_year, 'question_id' => $question_id,'view' => $this->input->post('view'));
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

			$this->db->insert('appraisal_planning_comment', $insert);

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
		$this->listview_qry .= ', user.position_id';
	}

	function _set_filter()
	{

		$filter = unserialize($this->encrypt->decode($this->input->post('filter')));

		$period_id = $filter['period_id'];

	
		$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}appraisal_planning_period WHERE planning_period_id = {$period_id} AND deleted = 0");

		if ($result && $result->num_rows() > 0){
			$employees = $result->row()->employee_id;
		}
	}

	function listview()
	{

		if (!$this->is_superadmin || (!$this->user_access[$this->module_id]['publish'] && !$this->user_access[$this->module_id]['post'])) {
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->system->get_employee_all_reporting_to($this->userinfo['user_id']);//$this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
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

		if( $this->module == "user" && (!$this->user_access[$this->module_id]['post']) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


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
		$emp = $this->db->get_Where('employee', array('employee_id' => $this->userinfo['user_id'] ))->row();
		$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->userinfo['user_id']);
		
		foreach( $subordinates as $subordinate_info ){

			if (!in_array($subordinate_info['user_id'], $get_subordinate_circle)) {
				array_push($get_subordinate_circle, $subordinate_info['user_id']);
			}

		}

		$filter = unserialize($this->encrypt->decode($this->input->post('filter')));
		$period_id = $filter['period_id'];

		$this->db->where('appraisal_planning_period.planning_period_id',$period_id);
		$this->db->where('appraisal_planning_period.deleted',0);
		$result = $this->db->get('appraisal_planning_period');

		
		if ($result && $result->num_rows() > 0){
			$appraisal_planning_period_row = $result->row_array();
			$appraisal_planning_period_employee = explode(',',$result->row()->employee_id);

			foreach( $appraisal_planning_period_employee as $employee_info ){


				if( in_array($employee_info, $get_subordinate_circle) ){
					
					$this->db->join('user','user.user_id = employee_appraisal_planning.employee_id','left');
					$this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
					$this->db->where('employee_appraisal_planning.employee_id',$employee_info);
					$this->db->where('employee_appraisal_planning.appraisal_planning_period_id',$period_id);
					$this->db->where('employee_appraisal_planning.deleted',0);
					$appraisal = $this->db->get('employee_appraisal_planning');

					if( $appraisal && $appraisal->num_rows() > 0 ){

						$appraisal_approver_info = $appraisal->row();

						if( $appraisal_approver_info->status != 1 ){
							array_push($employees,$employee_info);
						}
						elseif($this->user_access[$this->module_id]['publish'] && $this->user_access[$this->module_id]['post']){
							array_push($employees,$employee_info);	
						}

					}elseif($this->user_access[$this->module_id]['publish'] && $this->user_access[$this->module_id]['post']){
						array_push($employees,$employee_info);	
					}

				}
				elseif( $employee_info == $this->userinfo['user_id'] || ( ($this->user_access[$this->module_id]['publish'] && $this->user_access[$this->module_id]['post'])) ){
					array_push($employees,$employee_info);
				}
			}
		}
		// dbug($get_subordinate_circle);
		if( count($employees) <= 0 ){
			$employees = 0;
		}

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->select('user.division_id');
		$this->db->select('employee_appraisal_planning.status');
		$this->db->from($this->module_table);
		$this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
		$this->db->join('employee_appraisal_planning','employee_appraisal_planning.employee_id = user.employee_id && '.$this->db->dbprefix('employee_appraisal_planning').'.appraisal_planning_period_id = "'.$period_id.'"','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		$this->db->where_in('user.user_id',$employees);

		//get list
		$total_records =  $this->db->count_all_results();
		// $response->last_query = $this->db->last_query();
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
			$this->db->select('employee_appraisal_planning.status');
			$this->db->from($this->module_table);
			$this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
			$this->db->join('employee_appraisal_planning','employee_appraisal_planning.employee_id = user.employee_id && '.$this->db->dbprefix('employee_appraisal_planning').'.appraisal_planning_period_id = "'.$period_id.'"','left');
			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}

			$this->db->where_in('user.user_id',$employees);

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
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row, $appraisal_planning_period_row ) );
									$cell_ctr++;
								}
							}else{
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

									
									if ($detail['name'] == 't5employment_status') {

										$template = array();
										if ($row['status'] != "") {
											$qry = "SELECT appraisal_planning_status_id,appraisal_planning_status 
												FROM {$this->db->dbprefix}employee_appraisal_planning eap 
												INNER JOIN {$this->db->dbprefix}employee_appraisal_planning_status es ON eap.status = es.appraisal_planning_status_id
												 WHERE eap.deleted = 0 AND appraisal_planning_status_id = ".$row['status']."";
											$res = $this->db->query($qry);

											if ( $res && $res->num_rows > 0 ) {
												foreach ($res->result() as $value) {
													$template = $value->appraisal_planning_status;
												}
											}
										}
										else{
												$template = 'Pending';
											}
											
										$cell[$cell_ctr] = $template;
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


	function _default_grid_actions( $module_link = "",  $container = "", $record = array(), $period = array() )
	{

		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$has_appraisal = true;//$this->get_appraisal_record($record['employee_id'], $period['planning_period_id']);

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-document-view" pid="' . $record['position_id'] . '" module_link="'.$module_link.'" tooltip="View job description" href="javascript:void(0)"></a>';
            $actions .= '<a class="icon-button icon-16-info icon-appraisal" tooltip="View Plan" href="' . site_url($this->module_link . '/detail/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
            	
        }
		

 		if ( ($this->user_access[$this->module_id]['edit'])) {
        
 			$div_head_info = $this->get_division_head($record['division_id']);
 			$approver = $this->system->get_approvers_and_condition($record['employee_id'],$this->module_id);
 			$approver_list = array();


			foreach( $approver as $approver_info ){

				array_push($approver_list, $approver_info['approver']);

			}
			
 			if( ($record['employee_id'] == $this->userinfo['user_id'] && ( $record['status'] == 1 || $record['status'] == 4 || $record['status'] == ""))){
 				if ($has_appraisal && $record['status'] ==  3) {
 					
 				}else{
 					
 					if(!$record['status'] && $record['status'] == "" ){
 						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="Create Plan" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
 						
 						

 						if ($period['duplicate_id'] > 0){
 							$actions .= '<a class="icon-button icon-16-users icon-appraisdal" tooltip="Duplicate Plan" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . '/' .$period['planning_period_id'].'/'.$period['duplicate_id'].'"></a>';
 						}
 						
 						
 					}else{
 						$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="Edit Plan" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
 						
 					}
	            	
        		}
 			}
        	elseif( (in_array($this->userinfo['user_id'], $approver_list) || $record['employee_id'] == $this->userinfo['user_id']) && ( $record['status'] == 3 )){
        		if ($has_appraisal && $record['status'] ==  3) {
 					
 				}else{
        		$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="Edit Plan" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
        		}
        	}
/*        	elseif( in_array($this->userinfo['user_id'], $approver_list) && ( $record['status'] == 2 )){
        		$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="Edit Plan" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
        	}  */      	
        	elseif( $this->_can_approve($record,$period) && ( $record['status'] == 2 ) ){

        		$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="Edit Plan" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
        	}
        }

        // temporary add to edit with al or employees with publish 01-18-2019
/*        if (($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['publish']) && $period['period_status'] > 0) {
        	$actions .= '<a class="icon-button icon-16-user-business icon-appraisal" tooltip="Edit Plan" href="' . site_url($this->module_link . '/edit/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
        }*/
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print icon-appraisal" tooltip="Print" href="' . site_url($this->module_link . '/print_record/' . $record['employee_id']) . $this->uri->segment(4).'"></a>';
        }        

	    // $actions .= '<a class="icon-button icon-16-list-view icon-view-log-access" tooltip="View Log Access" href="javascript:void(0)" employeeid="'.$record['employee_id'].'" period_id=""></a>';

        $actions .= '</span>';

		return $actions;
	}

	function _can_approve( $record, $period ){


		$this->db->where('appraisal_planning_period_id',$period['planning_period_id']);
		$this->db->where('employee_id',$record['employee_id']);
		$this->db->where('deleted',0);
		$appraisal_result = $this->db->get('employee_appraisal_planning');
		
		if( $appraisal_result->num_rows() > 0 &&  $appraisal_result->row()->status == 2 ){

			$appraisal_info = $appraisal_result->row_array();

			$this->db->where('module_id',$this->module_id);
			$this->db->where('record_id',$appraisal_info['employee_appraisal_planning_id']);
			$this->db->where('focus',2);
			$this->db->where('status',2);
			$this->db->where('approver',$this->userinfo['user_id']);
			$approver_result = $this->db->get('employee_appraisal_approver');

			if( $approver_result->num_rows() > 0 ){

				return true;
			}
			else{

				$this->db->where('module_id',$this->module_id);
				$this->db->where('record_id',$appraisal_info['employee_appraisal_planning_id']);
				$this->db->where('focus',1);
				$this->db->where('status',0);
				$this->db->order_by('sequence','desc');
				$this->db->limit(1);
				$approver_result = $this->db->get('employee_appraisal_approver');

				if( $approver_result->num_rows() > 0 ){

					$approver_info = $approver_result->row();

					if( $approver_info->approver == $this->userinfo['user_id'] ){
						return true;
					}
				}

			}

			return false;

		}

		return false;

	}


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

		if ($template && $template->num_rows() > 0) {
			return $template;
		} else {
			return FALSE;
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

	function core_values($master_id)
	{
		$this->db->where('appraisal_competency_master_id', $master_id);
		$this->db->where('deleted', 0);
		$core = $this->db->get('appraisal_competency_value')->result();

		return $core;
	}


	private function _get_record_bsc($user_id, $period_id)
	{

		$this->db->where('employee_id', $user_id);
		$this->db->where('appraisal_planning_period_id', $period_id);
		$this->db->where('deleted', 0);
		$record = $this->db->get('employee_appraisal_planning');

		if ($record->num_rows() == 0) {
			return FALSE;
		} else {
			$result = $record->row_array();

			$result['employee_appraisal_criteria_array'] = ($result['employee_appraisal_criteria_array'] != '' ? unserialize($result['employee_appraisal_criteria_array']): '');
			$result['employee_appraisal_criteria_mid_year_comments'] = ($result['employee_appraisal_criteria_mid_year_comments'] != '' ? unserialize($result['employee_appraisal_criteria_mid_year_comments']): '');
			$result['employee_appraisal_criteria_actual_question_array'] = ($result['employee_appraisal_criteria_actual_question_array'] != '' ? unserialize($result['employee_appraisal_criteria_actual_question_array']): '');
			$result['employee_appraisal_criteria_question_array'] = ($result['employee_appraisal_criteria_question_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_array']): '');
			$result['employee_appraisal_competency_array'] = ($result['employee_appraisal_competency_array'] != '' ? unserialize($result['employee_appraisal_competency_array']): '');
			$result['employee_appraisal_competency_mid_comment_array'] = ($result['employee_appraisal_competency_mid_comment_array'] != '' ? unserialize($result['employee_appraisal_competency_mid_comment_array']): '');
			$result['employee_appraisal_expected_level_array'] = ($result['employee_appraisal_expected_level_array'] != '' ? unserialize($result['employee_appraisal_expected_level_array']): '');
			$result['employee_appraisal_criteria_question_sec_rating_array'] = ($result['employee_appraisal_criteria_question_sec_rating_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_sec_rating_array']): '');
			$result['employee_appraisal_criteria_question_item'] = ($result['employee_appraisal_criteria_question_item'] != '' ? unserialize($result['employee_appraisal_criteria_question_item']): '');
			$result['employee_appraisal_criteria_weight_array'] = ($result['employee_appraisal_criteria_weight_array'] != '' ? unserialize($result['employee_appraisal_criteria_weight_array']): '');
			$result['employee_appraisal_employee_strength'] = ($result['employee_appraisal_employee_strength'] != '' ? unserialize($result['employee_appraisal_employee_strength']): '');
			$result['employee_appraisal_areas_improvement'] = ($result['employee_appraisal_areas_improvement'] != '' ? unserialize($result['employee_appraisal_areas_improvement']): '');
			$result['employee_appraisal_coach_strength'] = ($result['employee_appraisal_coach_strength'] != '' ? unserialize($result['employee_appraisal_coach_strength']): '');
			$result['employee_appraisal_coach_improvement'] = ($result['employee_appraisal_coach_improvement'] != '' ? unserialize($result['employee_appraisal_coach_improvement']): '');
			$result['planning_status'] = $result['status'];
		
			return $result;
		}
	}	

	private function _get_record_bsc_latest($user_id)
	{

		$this->db->where('employee_id', $user_id);
		$this->db->where('status', 6);
		$this->db->where('deleted', 0);
		$this->db->order_by('date_created', 'desc');
		$this->db->limit(1);
		$record = $this->db->get('employee_appraisal_planning');

		if ($record->num_rows() == 0) {
			return FALSE;
		} else {
			$result = $record->row_array();
			
			$unserialize_weight = ($result['employee_appraisal_criteria_weight_array'] != '' ? unserialize($result['employee_appraisal_criteria_weight_array']): '');
			
			$this->recursiveRemoval($unserialize_weight);

			$result['employee_appraisal_criteria_array'] = ($result['employee_appraisal_criteria_array'] != '' ? unserialize($result['employee_appraisal_criteria_array']): '');
			$result['employee_appraisal_criteria_mid_year_comments'] = ($result['employee_appraisal_criteria_mid_year_comments'] != '' ? unserialize($result['employee_appraisal_criteria_mid_year_comments']): '');
			$result['employee_appraisal_criteria_actual_question_array'] = ($result['employee_appraisal_criteria_actual_question_array'] != '' ? unserialize($result['employee_appraisal_criteria_actual_question_array']): '');
			$result['employee_appraisal_criteria_question_array'] = ($result['employee_appraisal_criteria_question_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_array']): '');
			$result['employee_appraisal_competency_array'] = ($result['employee_appraisal_competency_array'] != '' ? unserialize($result['employee_appraisal_competency_array']): '');
			$result['employee_appraisal_competency_mid_comment_array'] = ($result['employee_appraisal_competency_mid_comment_array'] != '' ? unserialize($result['employee_appraisal_competency_mid_comment_array']): '');
			$result['employee_appraisal_expected_level_array'] = ($result['employee_appraisal_expected_level_array'] != '' ? unserialize($result['employee_appraisal_expected_level_array']): '');
			$result['employee_appraisal_criteria_question_sec_rating_array'] = ($result['employee_appraisal_criteria_question_sec_rating_array'] != '' ? unserialize($result['employee_appraisal_criteria_question_sec_rating_array']): '');
			$result['employee_appraisal_criteria_question_item'] = ($result['employee_appraisal_criteria_question_item'] != '' ? unserialize($result['employee_appraisal_criteria_question_item']): '');
			$result['employee_appraisal_criteria_weight_array'] = $unserialize_weight;
			$result['planning_status'] = '';
		
			return $result;
		}
	}	

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
        $buttons .= "</div>";
                
		return $buttons;
	}

	function get_jd_html() {
		$record_id = $this->input->post('record_id');

		$data['json']['jd_items'] = $this->_jd_html($record_id);

		$this->load->view('template/ajax', $data);
	}

	/**
	 * Employee JD
	 * For Viewing Purposes
	 *  
	 * @return void
	 */
	function _jd_html( $record_id ){
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template(0, 'JD_APPRAISAL');
		
		$vars = get_record_detail_array( $record_id );

		$position = $this->db->get_where('user_position', array( 'position_id' =>  $record_id))->row();
		$vars['position'] = $position->position;
		$vars['purpose'] = $position->purpose;
		$vars['next_level_superior'] = "";
		$next_level_superiors = $this->db->get_where('user_position', array( 'position_id' =>  $position->reporting_to));
		$next_level_superiors = $next_level_superiors->row();

		$vars['reporting_to'] = $next_level_superiors->position;

		//$this->db->where('position_id',$next_level_superiors->reporting_to );
		$next_level_superior = $this->db->get_where('user_position', array( 'position_id' =>  $next_level_superiors->reporting_to ));
		if( $next_level_superior->num_rows() == 1 ){
			$next_level_superior = $next_level_superior->row();
			$vars['next_level_superior'] = $next_level_superior->position;
		}
		
		$company = $this->db->get_where('user_company', array( 'company_id' =>  $position->company_id))->row();
		$vars['company'] = $company->company;
		
		//supervises
		$vars['ps1'] = "None";
		$vars['ps2'] = "";
		$vars['ps3'] = "";
		$vars['ps4'] = "";
		$vars['ps5'] = "";
		$vars['supervises'] = "";
		if( !empty( $position->supervises ) ){
			$supervises_id = explode( ',', $position->supervises );
			foreach( $supervises_id as $index => $position_id ){
				$supervise = $this->db->get_where('user_position', array( 'position_id' =>  $position_id))->row();
				$vars['ps'.($index+1)] = $supervise->position;
				$vars['supervises'] .= '<tr>
					<td width="7%" align="right">'. (chr( $index + 65)) .'.</td>
					<td width="3%">&nbsp;</td>
					<td width="90%">'. $supervise->position .'</td>
				</tr>';
			}
		}
		$vars['pc1'] = "None";
		$vars['pc2'] = "";
		$vars['pc3'] = "";
		$vars['pc4'] = "";
		$vars['pc5'] = "";
		$vars['coordinates_with'] = "";
		if( !empty( $position->coordinates_with ) ){
			$coordinates_with_id = explode( ',', $position->coordinates_with );
			foreach( $coordinates_with_id as $index => $position_id ){
				$coordinates_with = $this->db->get_where('user_position', array( 'position_id' =>  $position_id))->row();
				$vars['pc'.($index+1)] = $coordinates_with->position;
				$vars['coordinates_with'] .= '<tr>
					<td width="7%" align="right">'. (chr( $index + 65)) .'.</td>
					<td width="3%">&nbsp;</td>
					<td width="90%">'. $coordinates_with->position .'</td>
				</tr>';
			}
		}
		
		$vars['jd_details'] = $this->_jd_items( $record_id, false );
		$html = $this->template->prep_message($template['body'], $vars, false, true);

		return $html;
	}

	function _jd_items( $position_id = 0, $show_action = true ){
		$jd_items = "";
		//get the jd group
		$jdgroups = $this->db->get_where('user_position_jdgroup', array('deleted' => 0));

		if( $jdgroups->num_rows() > 0 ){
			$jdgroups = $jdgroups->result_array();
			foreach( $jdgroups as $jdg_index => $jdgroup ){
				$jdgroups[$jdg_index]['has_item'] = false;
				//get subgroup/key competencies
				$jdsubgroups = $this->db->get_where('user_position_jdsubgroup', array('deleted' => 0, 'jdgroup_id' => $jdgroup['jdgroup_id']));
				if( $jdsubgroups->num_rows() > 0 ){
					$jdsubgroups = $jdsubgroups->result_array();
					foreach( $jdsubgroups as $jdsg_index => $jdsubgroup ){
						$jdsubgroups[$jdsg_index]['has_item'] = false;
						//get items for subgroup
						$jditems = $this->db->get_where('user_position_jditem', array('deleted' => 0, 'jdsubgroup_id' => $jdsubgroup['jdsubgroup_id'], 'position_id' => $position_id ));
							
						if( $jditems->num_rows() > 0 ){
							$jditems = $jditems->result_array();
							$jdsubgroups[$jdsg_index]['has_item'] = true;
							$jdgroups[$jdg_index]['has_item'] = true;
							$jdsubgroups[$jdsg_index]['jditems'] = $jditems;
							$jdgroups[$jdg_index]['jdsubgroups'][$jdsg_index] = $jdsubgroups[$jdsg_index];
						}
					}
				}
			}
			
			foreach( $jdgroups as $jdg_index => $jdgroup ){
				if($jdgroup['has_item']){
					if( $show_action ){
						$w1 = 'width="5%"';
						$w2 = 'width="70%"';
						$w3 = 'width="10%"';
						$w12 = 'width="75%"';
					}
					else{
						$w1 = 'width="5%"';
						$w2 = 'width="80%"';
						$w3 = 'width="15%"';
						$w12 = 'width="85%"';
					}
					
					$jd_items .= '<tr style="background-color: #333333; color: white"><td colspan="2" '.$w12.'><strong>'.$jdgroup['jdgroup'].'</strong></td><td align="center" '.$w3.'>WEIGHTS</td>';
					if( $show_action ) $jd_items .= '<td width="15%">&nbsp;<td/>';
					$jd_items .= '</tr>';
					
					foreach( $jdgroup['jdsubgroups'] as $jdsg_index => $jdsubgroup ){
						if( $show_action )
							$jd_items .= '<tr style="background: #c0c0c0"><td colspan="4"><em>'. $jdsubgroup['jdsubgroup'] .'</em></td></tr>';
						else
							$jd_items .= '<tr style="background: #c0c0c0"><td colspan="3"><em>'. $jdsubgroup['jdsubgroup'] .'</em></td></tr>';
						foreach( $jdsubgroup['jditems'] as $item_index => $jditem ){
							$jd_items .= '<tr>';
							$jd_items .= '<td align="center" '.$w1.'>'. ($item_index + 1) .'</td>';
							$jd_items .= '<td align="left" '.$w2.'>'. $jditem['jditem'] .'</td>';
							$jd_items .= '<td align="center" '.$w3.'>'. ( $jditem['weight'] > 0 ? $jditem['weight'] .'%' : "" ) .'</td>';
							if( $show_action ) $jd_items .= '<td align="center" width="15%"><a onclick="edit_jditem_detail( \'-1\', '. $jditem['jditem_id'] .' )" href="javascript:void(0);" class="icon-button icon-16-add">Add Item Detail</a><a onclick="edit_jd_item( '. $jditem['jditem_id'] .', '. $position_id .' )" href="javascript:void(0);" class="icon-button icon-16-edit">Edit Item</a><a onclick="delete_jditem( '. $jditem['jditem_id'] .' )" href="javascript:void(0);" class="icon-button icon-16-delete">Delete Item</a></td>';
							$jd_items .= '</tr>';
							//check for item details
							$jditem_details = $this->db->get_where('user_position_jditem_detail', array('deleted' => 0, 'jditem_id' => $jditem['jditem_id']));
							if( $jditem_details->num_rows() > 0 ){
								foreach( $jditem_details->result_array() as $index => $detail ){
									$jd_items .= '<tr>';
									$jd_items .= '<td align="right"  '.$w1.'>'. ($item_index + 1) . (chr( $index + 65)) .'.&nbsp;</td>';
									$jd_items .= '<td align="left"  '.$w2.'>'. $detail['jditem_detail'] .'</td>';
									$jd_items .= '<td align="center"  '.$w3.'></td>';
									if( $show_action ) $jd_items .= '<td align="center" width="15"><a onclick="edit_jditem_detail( '. $detail['jditem_detail_id'] .', '. $jditem['jditem_id'] .')" href="javascript:void(0);" class="icon-button icon-16-edit">Edit Item Detail</a><a onclick="delete_jditem_detail( '. $detail['jditem_detail_id'] .' )" href="javascript:void(0);" class="icon-button icon-16-delete">Delete Item Detail</a></td>';
									$jd_items .= '</tr>';
								}
							}
						}
					}
				}
			}
		}
		
		if( !empty( $jd_items ) ){
			$jd_items = '<table cellpadding="jd-table" width="100%;" border="1" cellpadding="10" cellspacing="0">'. $jd_items .'</table>';
		}
		

		return $jd_items;
	}


	function get_competency_level(){

		$html = '<option value="">Select... </option>';
		$competency_level = $this->db->get_where('appraisal_competency_level', array('deleted' => 0, 'appraisal_competency_id' => $this->input->post('competency_id') ));


		if($competency_level && $competency_level->num_rows() > 0 ){

			foreach( $competency_level->result() as $competency_info ){

				$html .= '<option value="'.$competency_info->appraisal_competency_level_id.'" description="'.$competency_info->description.'">'.$competency_info->appraisal_competency_level.'</option>';

			}

		}

		$data['html'] = $html;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);


	}

	function  get_competencies()
	{
		$html = '<div class="default-table boxtype">';
		// $this->db->join('appraisal_competency_level', 'appraisal_competency_level.appraisal_competency_value_id=appraisal_competency_value.competency_value_id');
		$values = $this->db->get_where('appraisal_competency_value', array('appraisal_competency_value.deleted' => 0, 'appraisal_competency_master_id' => $this->input->post('master_id') ));


		if($values && $values->num_rows() > 0 ){
			$html .='<table class="default-table boxtype" cellspacing="0" cellpadding="10" border="1" style="width: 100%;" >';
			// $html .='<thead>
			// 			<tr><th align="left">Values</th><th align="left">competencies</th><th align="left">Levels</th></tr>
			// 		</thead><tbody>';

			foreach( $values->result() as $value ){
				$competencies = $this->db->get_where('appraisal_competency', array('appraisal_competency_value_id' => $value->competency_value_id));
				$html .= '<tr><th colspan="2">' . $value->competency_value . ' </th>';
				$html .= '</tr>';
				if ($competencies && $competencies->num_rows() > 0) {
	            	foreach ($competencies->result() as $competency) { 
	            		$html .= '<tr><td><b>' . $competency->competency . '<b></td>';
	            		$html .= '<td> - ' . $competency->description . '</td></tr>';
						
					$html .= '<tr>';
					$competency_level = $this->db->get_where('appraisal_competency_level', array('deleted' => 0, 'appraisal_competency_id' => $competency->competency_id ));
						$html .= '<td>Levels';
						$html .= '</td>';
						$html .= '<td>';
						if( $competency_level && $competency_level->num_rows() > 0 ){
							$html .= '<table>';
							foreach( $competency_level->result() as $competency_info ){
								$html .= '<tr><td><b>'.$competency_info->appraisal_competency_level.'</b></td>';
								$html .=  '<td> - '.$competency_info->description.'</td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
						}
						$html .= '</td>';
						$html .= '</tr>';

	            	}
	            }
				
				
				

			}
			$html .= '</tbody></table>';
		}
		$html .='</div>';
		$response->contents = $html; 
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function recursiveRemoval(&$array)
	{
	    if(is_array($array))
	    {
	        foreach($array as $key=>&$arrayElement)
	        {
	            if(is_array($arrayElement))
	            {
	                $this->recursiveRemoval($arrayElement);
	            }
	            else
	            {
	                $array[$key] = '';
	            }
	        }
	    }
	}

	function get_appraisal_record($employee_id, $period_id)
	{
		$check_appraisal = $this->db->get_Where('employee_appraisal_bsc', array('employee_id' => $employee_id, 'appraisal_period_id' => $period_id, 'deleted' => 0));

		if ($check_appraisal && $check_appraisal->num_rows() > 0) {
			return true;
		}else{
			return false;
		}
	}

	function get_rating_scale()
	{
		$this->db->where('employee_appraisal_scale.deleted',0);
		$this->db->where('employee_appraisal_rating_scale.deleted',0);
        $this->db->where('employee_appraisal_criteria.is_core', 0);
        $this->db->order_by('employee_appraisal_scale.appraisal_scale_id', 'DESC');
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

	function update_approver($employee_id,$record_id)
	{
		$approvers = $this->system->get_approvers_and_condition($employee_id, 485);

		$this->db->where('module_id',485);
		$this->db->where('record_id',$record_id);
		$this->db->delete('employee_appraisal_approver');

        foreach ($approvers as $key => $value) {
			$value['record_id'] = $record_id;
			$value['module_id'] = 485;
			$value['approver'] = $value['approver'];
			$value['sequence'] = $value['sequence'];
			// $value['focus'] = 0;
			$value['status'] = 0;
	
			$this->db->insert('employee_appraisal_approver', $value);
		}		
	}

	// END custom module funtions


}

/* End of file */
/* Location: system/application */