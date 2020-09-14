<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Candidate_result extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists payroll accounts.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a payroll account';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a payroll account';

		$this->filter = $this->module_table.".candidate_status_id IN (20)";
		$this->default_sort_col = array('t0firstnamelastname');	
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$this->load->helper('candidates');
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';
		$data['jqgrid'] = 'recruitment/candidates/jqgrid';

		$array_tab_count = get_candidate_tab_count();
		
        $tabs[] = '<li filter="posted_jobs"><a href="'.base_url().'recruitment/candidate_postedjobs">Posted Jobs<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['posted_jobs'].'</span></li>';
        $tabs[] = '<li filter="schedule"><a href="'.base_url().'recruitment/candidate_schedule">Candidates<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['schedule'].'</span></li>';
        $tabs[] = '<li filter="exam" class="active"><a href="'.base_url().'recruitment/candidate_result">Assessment Profile<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['result'].'</span></li>';
        $tabs[] = '<li filter="bcheck"><a href="'.base_url().'recruitment/candidate_background_check">Background Check<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['bcheck'].'</span></li>';
        $tabs[] = '<li filter="job_offer"><a href="'.base_url().'recruitment/candidate_job_offer">Job Offer <span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['joboffer'].'</span></li>';
        $tabs[] = '<li filter="contract_sign"><a href="'.base_url().'recruitment/candidate_contract_signing">For Contract Signing<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['contractsigning'].'</span></li>';
        $tabs[] = '<li filter="others"><a href="'.base_url().'recruitment/candidate_others">Others <span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['others'].'</span></li>';
        $tabs[] = '<li filter="archive"><a href="'.base_url().'recruitment/candidate_archive">Archive <span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['archive'].'</span></li>';

		if( sizeof( $tabs ) > 1 ) $data['candidate_tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');

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
		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

		$exams = $this->get_exam_type();
		$details = array();

		$data['exam_details'] = false;
		$exam_details = $this->db->get_where('recruitment_candidates_appraisal',array('candidate_id'=>$this->key_field_val));
		if ($exam_details && $exam_details->num_rows() > 0) {
			$exam_detail = json_decode($exam_details->row()->exam_details);
			$reccommendation = json_decode($exam_details->row()->exam_details);
			$type = $exam_detail->type;
		    $results = $exam_detail->result;
		    $details['percent'] = $exam_detail->percentile;

			foreach ($type as $id => $value) {
				foreach ($exams as $exam) {
					if ($value == $exam->recruitment_exam_type_id) {
						$details['type'][] = $exam->recruitment_exam_type;
					}
				}
			}
			
			foreach ($results as $result) {
				$details['result'][] = ($result == '1') ? 'Passed' : 'Failed' ;
			}
			
			$data['recommendation'] = $this->get_recommendation();
			
			$data['exam_details'] = $details;

			$data['appraisal'] = $exam_details->row();
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
			$data['scripts'][] = uploadify_script();
			$data['content'] = 'editview';
			$data['buttons'] = 'recruitment/template/assessment-edit-buttons';
			$interviewer_arr = array();

			$data['default_interviewer'] = false;

			// $this->db->where('user_id <>',$user_info->row()->user_id);
			// $this->db->where('department_id',$user_info->row()->department_id);
			$this->db->where('user.deleted',0);
			$this->db->where('user.inactive',0);
			$this->db->join('employee','employee.employee_id = user.employee_id');
			$result = $this->db->get('user');
			if ($result && $result->num_rows() > 0){
				foreach ($result->result_array() as $key => $value) {
					$interviewer_arr[] = $value;
				}
			}

			//check if default interviewer already set.
			if ($this->user_access[$this->module_id]['post'] != 1) {
				$this->db->where('user_id',$this->userinfo['user_id']);
			}
			$this->db->where('candidate_id',$this->input->post('record_id'));
			$result = $this->db->get('recruitment_manpower_candidate_interviewer');

			if ($result && $result->num_rows() > 0){
				$data['default_interviewer'] = true;
			}

			$data['with_sched'] = 0;
			$data['interviewer'] = $interviewer_arr;
			$data['candidate_interviewer'] = $this->get_candidate_interviewer($this->input->post('record_id'));
			$data['interview_result'] = $this->db->get_where('recruitment_candidate_result',array("deleted"=>0));			
			$data['interview_type'] = $this->db->get_where('recruitment_interview_type',array("deleted"=>0))->result();			
			$data['candidate_info'] = $this->get_candidate_info($this->input->post('record_id'));
			$data['with_sched'] = 1;
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();


			$data['exams'] = $this->get_exam_type();
			$data['exam_details'] = false;
			$exam_details = $this->db->get_where('recruitment_candidates_appraisal',array('candidate_id'=>$this->key_field_val));
			if ($exam_details && $exam_details->num_rows() > 0) {
				$data['exam_details'] = $exam_details->row()->exam_details;
				$data['interview_details'] = json_decode($exam_details->row()->interview_details, true);
				$data['appraisal'] = $exam_details->row();
			}
			// dbug($data['exam_details']);
			// die();
			$data['recommendation'] = $this->get_recommendation();

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

		parent::ajax_save();

		$result = $this->db->get_where('recruitment_candidates_appraisal',array('candidate_id'=>$this->key_field_val));
		if ($result && $result->num_rows() > 0){
			$appraisal_id = $result->row()->appraisal_id;
			
			$exams = json_encode($this->input->post('exam'));
			$interview = json_encode($this->input->post('interview'));
			$exam_date = date('Y-m-d H:i:s', strtotime($this->input->post('exam_date')));
			$date_from = ($this->input->post('employment_period_start') != "") ? date('Y-m-d H:i:s', strtotime($this->input->post('employment_period_start'))) : " " ;
			$date_to = ($this->input->post('employment_period_end') != "") ? date('Y-m-d H:i:s', strtotime($this->input->post('employment_period_end'))) : " " ; 
			$benifits = json_encode($this->input->post('benefit'));
			$others = json_encode($this->input->post('others'));

            $appraisal = array(
                    "exam_details"=>$exams,
                    "interview_details"=>$interview,
                    "screening_datetime"=>$exam_date,
                    "company"=>$this->input->post("company"),
                    "salary"=>$this->input->post("salary"),
                    "industry"=>$this->input->post("industry"),
                    "date_from"=>$date_from,
                    "date_to"=>$date_to,
                    "position"=>$this->input->post("position"),
                    "previous_emp_status"=>$this->input->post("prev_status"),
                    "level"=>$this->input->post("level"),
                    "benefits"=>$benifits,
                    "other_benefits"=>$others
                );
			
			$this->db->where('appraisal_id', $appraisal_id);
			$this->db->update('recruitment_candidates_appraisal', $appraisal);

            // $this->db->delete('recruitment_candidates_appraisal_exams', array('appraisal_id' => $appraisal_id));
            // $this->db->insert('recruitment_candidates_appraisal_exams', $interview_action_sheet);

            // $interview_comments = array(
            //         "appraisal_id"=>$appraisal_id,
            //         "strength"=>$this->input->post("strength"),
            //         "areas_improvement"=>$this->input->post("areas_improvement"),
            //         "job_fit"=>$this->input->post("job_fit")
            //     );

            // $this->db->delete('recruitment_candidates_appraisal_comments', array('appraisal_id' => $appraisal_id));
            // $this->db->insert('recruitment_candidates_appraisal_comments', $interview_comments);

			
			// $this->db->where('candidate_id', $this->key_field_val);				
			//$this->db->update('recruitment_manpower_candidate', array('candidate_result_id'=>$this->input->post('candidate_result_id'),'candidate_status_id'=>$this->input->post('candidate_status_id')));
			// $this->db->update('recruitment_manpower_candidate', array('candidate_result_id'=>$this->input->post('candidate_result_id')));
			

			/* //Disable automatic cange to job offer feature for the time being
			if ($this->input->post('exam_percentile_total') > $this->config->item('MIN_APPRAISAL_SCORE') && $this->input->post('candidate_result_id') == 1){
				$this->db->where('candidate_id', $this->key_field_val);				
				$this->db->update('recruitment_manpower_candidate', array('candidate_status_id'=>5));
			}
			elseif ($this->input->post('exam_percentile_total') > $this->config->item('MIN_APPRAISAL_SCORE')){
				$this->db->where('candidate_id', $this->key_field_val);				
				$this->db->update('recruitment_manpower_candidate', array('candidate_status_id'=>5));				
			}
			elseif ($this->input->post('exam_percentile_total') < $this->config->item('MIN_APPRAISAL_SCORE') && $this->input->post('candidate_result_id') == 1){
				$this->db->where('candidate_id', $this->key_field_val);				
				$this->db->update('recruitment_manpower_candidate', array('candidate_status_id'=>3));				
			}p
			*/

			if ($this->input->post('candidate_status_id')) {
				$this->db->where('candidate_id', $this->key_field_val);				
				$this->db->update('recruitment_manpower_candidate', array('candidate_status_id'=>$this->input->post('candidate_status_id')));		
			}

			if ($this->input->post('interviewer_id')){
				$interview_date_arr = $this->input->post('interview_date');				
				$interviewer_info = array();				

            	$this->db->delete('recruitment_manpower_candidate_interviewer', array('candidate_id' => $this->key_field_val));

				foreach ($this->input->post('interviewer_id') as $key => $value) {
					if ($value != '' ){
						$interviewer_info["candidate_id"] = $this->key_field_val;
						$interviewer_info["user_id"] = $value;
						$interviewer_info["datetime"] = date('Y-m-d h-i-s',strtotime($interview_date_arr[$key]));
						$this->db->insert('recruitment_manpower_candidate_interviewer', $interviewer_info);
					}
				}
			}
		}	
		//additional module save routine here
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
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
		// $this->listview_qry .= ',IF('.$this->db->dbprefix.'recruitment_manpower_candidate.is_internal = 0, CONCAT( '.$this->db->dbprefix.'recruitment_applicant.firstname, " ", '.$this->db->dbprefix.'recruitment_applicant.lastname ), CONCAT( ' . $this->db->dbprefix . 'user.firstname, " ", ' . $this->db->dbprefix . 'user.lastname )) t0firstnamelastname';
		$this->listview_qry .= ',IF('.$this->db->dbprefix.'recruitment_manpower_candidate.is_internal = 0, CONCAT( '.$this->db->dbprefix.'recruitment_applicant.firstname, " ",REPLACE(CONCAT(UCASE(LEFT('.$this->db->dbprefix.'recruitment_applicant.middlename , 1))," .")," ", ""), " ", '.$this->db->dbprefix.'recruitment_applicant.lastname ), CONCAT( ' . $this->db->dbprefix . 'user.firstname, " ", ' . $this->db->dbprefix . 'user.middleinitial, " ", ' . $this->db->dbprefix . 'user.lastname )) t0firstnamelastname';

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
		$this->db->where('IF(is_internal = 0, 1, '.$this->db->dbprefix.'user.deleted = 0)');
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
				
				if( $sidx == 'recruitment_manpower_candidate.applicant_id'){
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort, $sord);
				}else{
					$this->db->order_by($sidx, $sord);	
				}
			}
			else{

				if( is_array($this->default_sort_col)){
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
							} elseif ($detail['name'] == 'applicant_id') {
								$cell[$cell_ctr++] = $row['t0firstnamelastname'];								
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

	function _set_left_join()
	{
		//build left join for related tables
		foreach($this->related_table as $table => $column){
			if ($table == 'recruitment_candidates_appraisal_exams'){
				$this->db->join('recruitment_candidates_appraisal', 'recruitment_candidates_appraisal.candidate_id='. $this->module_table .'.candidate_id', 'left');				
				$this->db->join('recruitment_candidates_appraisal_exams', 'recruitment_candidates_appraisal_exams.appraisal_id = recruitment_candidates_appraisal.appraisal_id', 'left');				
			}
			elseif ($table == 'recruitment_candidates_appraisal_comments'){
				$this->db->join('recruitment_candidates_appraisal_comments', 'recruitment_candidates_appraisal_comments.appraisal_id = recruitment_candidates_appraisal.appraisal_id', 'left');				
			}
			else{
				if( strpos($table, " ") ){
					$letter = explode(" ", $table);
					if($this->module == "module" && $column == "module_id" && $this->module == "module")
						$this->db->join($table, $letter[1] .'.module_id='. $this->module_table .'.parent_id', 'left');
					else
						$this->db->join($table, $letter[1] .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
				}
				else{
					$this->db->join($table, $table .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
				}
			}
		}

		$this->db->join('user', 'user.employee_id = recruitment_manpower_candidate.employee_id', 'left');
		$this->db->join('recruitment_applicant','recruitment_applicant.applicant_id = '.$this->db->dbprefix.'recruitment_manpower_candidate.applicant_id','left');			
	}	

	function get_interviewer($mrf_id){
		$this->db->where('recruitment_manpower.deleted',0);
		$this->db->where('request_id',$mrf_id);
		// $this->db->join('user','user.user_id = recruitment_manpower.requested_by');
		$result = $this->db->get('recruitment_manpower');

		if ($result && $result->num_rows() > 0){
			$result_arr = $result->row_array();

			// $this->db->where('department_id',$result_arr['department_id']);
			// $this->db->where('division_id',$result_arr['division_id']);
			$this->db->where_not_in('employee_id', array(1,2));
			$this->db->where('inactive ',0);	
			$this->db->where('deleted ',0);	
			$this->db->order_by('lastname,firstname');	
			$result_user = $this->db->get('user');
			
			if ($result_user && $result_user->num_rows() > 0){
				return $result_user->row_array();
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}

	function get_interviewer_form(){

		$html = '';

		$interviewer_arr = array();

		$user_info = $this->db->get_where('user',array("user_id"=>$this->userinfo['user_id']));
		if ($user_info && $user_info->num_rows() > 0){
			$interviewer_arr[] = $user_info->row_array();

			$this->db->where('user_id <>',$user_info->row()->user_id);
			$this->db->where('department_id',$user_info->row()->department_id);
			$result = $this->db->get('user');

			if ($result && $result->num_rows() > 0){
				foreach ($result->result_array() as $key => $value) {
					$interviewer_arr[] = $value;
				}
			}

			//check if default interviewer already set.
			$this->db->where('user_id',$this->userinfo['user_id']);
			$this->db->where('candidate_id',$this->input->post('record_id'));
			$result = $this->db->get('recruitment_manpower_candidate_interviewer');
			if ($result && $result->num_rows() > 0){
				$data['default_interviewer'] = true;
			}
		}

		if ($this->input->post('record_id') != '-1'){
			$candidate_info = $this->db->get_where('recruitment_manpower_candidate',array("candidate_id"=>$this->input->post('record_id'),"deleted"=>0));
			if ($candidate_info && $candidate_info->num_rows() > 0){
				$mrf_info = $this->get_interviewer($candidate_info->row()->mrf_id); //request_id
				$this->db->where('division_id',$mrf_info['division_id']);
				$this->db->where('department_id',$mrf_info['department_id']);
				$result = $this->db->get('user');
				if ($result && $result->num_rows() > 0){
					foreach ($result->result_array() as $key => $value) {
						$interviewer_arr[] = $value;
					}
				}
			}
		}

		$html .= '<div class="parent_container">
			<div class="form-item odd">
				<label class="label-desc gray" for="final_interview_id">
					Interviewer: 	
			  	</label>
				<div class="select-input-wrap">';

		if (count($interviewer_arr) > 0){
				$html .= '<select id="interviewer_id" name="interviewer_id[]">
					<option value="">Select...</option>';
						foreach ($interviewer_arr as $key => $value) { 
							$html .= '<option value="'.$value['user_id'].'">'.$value['firstname'].'&nbsp;'.$value['lastname'].'</option>';
						}
				$html .= '</select>';
		
		}else{
				$html .= '<span class="red">No interviewer set under position settings.</span>';
		}
		$html .= '</div>
			</div>
			<div class="form-item1 even">
				<label class="label-desc gray" for="final_date_time">Date and Time:<span class="red font-large">*</span></label>
				<div class="text-input-wrap"><input type="text" class="input-text" name="interview_date[]"/></div>
			</div>
			
			<div class="form-item2">
				<div style="padding-top:13px; width:100px;" class="icon-group">
						<a onclick="delete_benefit( $(this) )" href="javascript:void(0);" class="icon-button icon-16-minus"></a>
						<a class="icon-button icon-16-disk-back icon-send" href="javascript:void(0)"></a>
				</div>
			</div>
							
		</div>	';

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function get_candidate_interviewer($candidate_id){
		$this->db->select('recruitment_manpower_candidate_interviewer.candidate_interviewer_id,firstname,lastname,datetime,recruitment_manpower_candidate_interviewer.current_candidate_status,interviewer_type, user.user_id');
		$this->db->where('recruitment_manpower_candidate_interviewer.deleted',0);
		$this->db->where('recruitment_manpower_candidate_interviewer.candidate_id',$candidate_id);
		$this->db->join('user','recruitment_manpower_candidate_interviewer.user_id = user.user_id');
		$this->db->join('recruitment_manpower_candidate','recruitment_manpower_candidate_interviewer.candidate_id = recruitment_manpower_candidate.candidate_id');
		$result = $this->db->get('recruitment_manpower_candidate_interviewer');

		if ($result && $result->num_rows() > 0){
			return $result;
		}
		else{
			return false;
		}
	}	

	function get_candidate_info($candidate_id){
		$this->db->where('recruitment_manpower_candidate.deleted',0);
		$this->db->where('candidate_id',$candidate_id);
		$result = $this->db->get('recruitment_manpower_candidate');
		if ($result && $result->num_rows() > 0){
			return $result->row_array();
		}
		else{
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
			$employee_id = $this->input->post('employee_id');

			$this->db->where('employee_id', $employee_id);
			$this->db->where('deleted', 0);
			$record = $this->db->get('user');

			if (!$record || $record->num_rows() == 0) {
				$response->msg 	    = 'Record not found. Request was not sent.';
				$response->msg_type = 'error';
			} else {
				// Send email.
                // Load the template.            
                $this->load->model('template');

                $employee_info = $record->row_array();

                $template = $this->template->get_module_template($this->module_id, 'candidate_result_interview');
                $message = $template['body'];
                //$message = $this->template->prep_message($template['body'], $request);

				$recepients[] = $employee_info['email'];
                $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);

				$this->db->where('candidate_id', $this->input->post('candidate_id'));
				$this->db->where('user_id', $this->input->post('employee_id'));
				$this->db->update('recruitment_manpower_candidate_interviewer', array("email_sent_to_interviewer" => 1, "datetime_email_sent_to_interviewer" => date('Y-m-d G:i:s')));

				$response->msg = 'Sent to Interviewer.';
				$response->msg_type = 'success';                	
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}	
	}	

	function interview_form(){
		$this->db->where('recruitment_manpower_candidate_interviewer.candidate_interviewer_id',$this->input->post('candidate_interviewer_id'));
		$this->db->join('user','recruitment_manpower_candidate_interviewer.user_id = user.user_id');
		$this->db->join('recruitment_candidates_appraisal_comments','recruitment_manpower_candidate_interviewer.candidate_interviewer_id = recruitment_candidates_appraisal_comments.candidate_interviewer_id','left');
		$result = $this->db->get('recruitment_manpower_candidate_interviewer');

		if ($result && $result->num_rows() > 0){
			$row = $result->row();
		}

		$html = '<fieldset id="advance_search_container" style="width:800px">
					<div class="form-multiple-add" style="display: block;">
						<div class="col-2-form">
							<div class="form-item odd">
			                    <label for="interviewer" class="label-desc gray">
			                        Interviewer:
			                    </label>
			                    <div class="text-input-wrap">                        
			                    	<input id="fullname" class="input-text" type="text" value="'.$row->firstname.' '.$row->lastname.'" name="fullname">
			                	</div>
		                	</div>
							<div class="form-item even">
			                    <label for="status" class="label-desc gray">
			                        Result:
			                    </label>
			                    <div class="select-input-wrap">                        
									<input type="radio" name="result" value="Passed" '.($row->current_candidate_status == 'Passed' ? 'checked' : '').'>Passed
									<input type="radio" name="result" value="Failed" '.($row->current_candidate_status == 'Failed' ? 'checked' : '').'>Failed 
			                	</div>
		                	</div>
							<div class="form-item odd">
			                    <label for="gender" class="label-desc gray">
			                        Strength:
			                    </label>
								<div class="textarea-input-wrap">
									<textarea class="input-textarea" id="strength" name="strength" rows="5">'.$row->strength.'</textarea>
								</div>
		                	</div>	
							<div class="form-item even">
			                    <label for="age" class="label-desc gray">
			                        Areas of Improvement:
			                    </label>
								<div class="textarea-input-wrap">
									<textarea class="input-textarea" id="areas_improvement" name="areas_improvement" rows="5">'.$row->areas_improvement.'</textarea>
								</div>
		                	</div>
							<div class="form-item odd">
			                    <label for="gender" class="label-desc gray">
			                        Final Recomendation:
			                    </label>
								<div class="textarea-input-wrap">
									<textarea class="input-textarea" id="recommendation" name="recommendation" rows="5">'.$row->job_fit.'</textarea>
								</div>
		                	</div>			                				                		                	
	                	</div>	                	
					</div>
					<div class="form-submit-btn ">
					    <div class="icon-label-group"> 
					        <div class="icon-label">
					            <a onclick="save_interviewer('. $this->input->post("candidate_interviewer_id").')" id="" href="javascript:void(0);" class="icon-16-disk">
					              <span>Save</span>
					            </a>
					        </div>
					    </div>
					</div>					
				 </fieldset>';
		$data['html'] = $html;
		$this->load->view('template/ajax', $data);		
	}	

	function save_interviewer(){
        $interview_result = array(
                "interviewer" => $this->input->post('fullname'),
                "current_candidate_status" => $this->input->post('result')
            );

        $this->db->where('candidate_interviewer_id',$this->input->post('candidate_interviewer_id'));
        $this->db->update('recruitment_manpower_candidate_interviewer',$interview_result);

        $interview_comments = array(
                "strength" => $this->input->post('strength'),
                "areas_improvement" => $this->input->post('areas_improvement'),
                "job_fit" => $this->input->post('recommendation')
            );        

        $this->db->where('candidate_interviewer_id',$this->input->post('candidate_interviewer_id'));
        $result = $this->db->get('recruitment_candidates_appraisal_comments');

        if ($result && $result->num_rows() > 0){
	        $this->db->where('candidate_interviewer_id',$this->input->post('candidate_interviewer_id'));
	        $this->db->update('recruitment_candidates_appraisal_comments',$interview_comments);
        }
        else{
	        $interview_comments = array(
	        		"candidate_interviewer_id" => $this->input->post('candidate_interviewer_id'),
	                "strength" => $this->input->post('strength'),
	                "areas_improvement" => $this->input->post('areas_improvement'),
	                "job_fit" => $this->input->post('recommendation')
	            );  

        	$this->db->insert('recruitment_candidates_appraisal_comments',$interview_comments);
        }

		$response->msg = 'Record successfully saved.';
		$response->msg_type = "success";

		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);        
	}	

	function get_exam_type()
	{
		$this->db->where('deleted', 0);
		$exam = $this->db->get('recruitment_exam_type');

		if ($exam && $exam->num_rows() > 0) {
			return $exam->result();
		}else{
			return array();
		}
		
	}

	function get_recommendation()
	{
		$this->db->where('deleted', 0);
		$recommendation = $this->db->get('recruitment_recommendation');

		if ($recommendation && $recommendation->num_rows() > 0) {
			return $recommendation->result();
		}else{
			return array();
		}
		
	}

	function get_exam_form()
	{
		if (IS_AJAX) {
			$type = $this->input->post('type');
			$data['exams'] = $this->get_exam_type();
			$data['recommendation'] = $this->get_recommendation();
			if ($this->input->post('counter_line')) {
				$data['count'] = $this->input->post('counter_line');
			
					$this->db->where('user.deleted',0);
					$this->db->where('user.inactive',0);
					$this->db->join('employee','employee.employee_id = user.employee_id');
					$result = $this->db->get('user');
					if ($result && $result->num_rows() > 0){
						foreach ($result->result_array() as $key => $value) {
							$interviewer_arr[] = $value;
						}
					}
				$data['interviewer'] = $interviewer_arr;
			}
			

			$response = $this->load->view($this->userinfo['rtheme'] . '/recruitment/candidates/assessment/'.$type.'_form', $data);

			$data['html'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function add_benefit_field(){
		$response->field = $this->load->view( $this->userinfo['rtheme'].'/recruitment/candidates/assessment/benefit_field', array('benefit_id' => $this->input->post('benefit_id')), true );

		$response->selected_benefits = $this->input->post('selected_benefits');
		if( ! empty( $response->selected_benefits ) )
			$response->selected_benefits .= ',' . $this->input->post('benefit_id');
		else
			$response->selected_benefits = $this->input->post('benefit_id');
		$this->db->where('deleted', 0);
		$this->db->where('recruitment_other_benefits_id not in ('. $response->selected_benefits .')');
		$benefits = $this->db->get('recruitment_other_benefits');
   	$response->benefitddlb = '<option value="">Select...</option>';
	  foreach($benefits->result() as $benefit):
      $response->benefitddlb .= '<option value="'.$benefit->recruitment_other_benefits_id.'">'.$benefit->benefits_from.'</option>';
    endforeach;
		$response->benefitddlb .= '<option value="0">Others</option>';

		$this->load->view('template/ajax', array('json' => $response));
	}


	// END custom module funtions   recruitment_candidates_appraisal_exams

}

/* End of file */
/* Location: system/application */