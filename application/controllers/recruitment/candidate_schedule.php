<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Candidate_schedule extends MY_Controller
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
		$this->filter = $this->db->dbprefix . 'recruitment_manpower_candidate.candidate_status_id IN (1,2,3,4,5,11,12,13,17,18,19,20)';

        $this->load->helper('recruitment');
        $statuses = get_candidate_statuses();
        
		$data['module_filters'] = get_candidate_filters($statuses);
		$this->load->vars($data); 
    }

	// START - default module functions
	// default jqgrid controller method
	function index($mfr_id = 0)
    {
    	if ($mfr_id != 0){
    		$_POST['mrf_id_posted'] = $mfr_id;
			$this->session->set_userdata(array("mrf_id"=>$mfr_id));
    	}
    	else{
    		$this->session->unset_userdata('mrf_id');
    	}

		$this->load->helper('candidates');   
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';
		$data['jqgrid'] = 'recruitment/candidates/jqgrid';
		
		$array_tab_count = get_candidate_tab_count();

        $tabs = array();
        $tabs[] = '<li filter="posted_jobs"><a href="'.base_url().'recruitment/candidate_postedjobs">Posted Jobs<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['posted_jobs'].'</span></li>';
        $tabs[] = '<li filter="schedule" class="active"><a href="'.base_url().'recruitment/candidate_schedule">Candidates<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['schedule'].'</span></li>';
        $tabs[] = '<li filter="exam"><a href="'.base_url().'recruitment/candidate_result">Assessment Profile<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['result'].'</span></li>';
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
		$data['views_outside_record_form'] = array();

		$this->db->where('recruitment_manpower_candidate_interviewer.deleted',0);
		$this->db->where('interviewer_type',1);
		$this->db->where('candidate_id',$this->input->post('record_id'));
		$this->db->join('user','recruitment_manpower_candidate_interviewer.user_id = user.user_id','left');
		$interviewer = $this->db->get('recruitment_manpower_candidate_interviewer');

		$data['interviewer'] = $interviewer;

		$this->db->where('recruitment_manpower_candidate_interviewer.deleted',0);
		$this->db->where('interviewer_type',2);
		$this->db->where('candidate_id',$this->input->post('record_id'));
		$this->db->join('user','recruitment_manpower_candidate_interviewer.user_id = user.user_id','left');
		$technical_interviewer = $this->db->get('recruitment_manpower_candidate_interviewer');

		$data['technical_interviewer'] = $technical_interviewer;

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

			$data['buttons'] = $this->userinfo['rtheme'] . "/". $this->module_link . "/edit-buttons";

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$interviewer_arr = array();

			$data['default_interviewer'] = false;

			$user_info = $this->db->get_where('user',array("user_id"=>$this->userinfo['user_id']));
			if ($user_info && $user_info->num_rows() > 0){
				
				// if (!$this->is_admin){
					// $interviewer_arr[] = $user_info->row_array();
				// }

				// if (!$this->user_access[$this->module_id]['post']){
				// 	$this->db->where('department_id',$user_info->row()->department_id);
				// }	

				$this->db->where_not_in('employee_id', array(1,2));
				$this->db->where('inactive ',0);	
				$this->db->where('deleted ',0);	
				$this->db->order_by('lastname,firstname');	

				$result = $this->db->get('user');

				if ($result && $result->num_rows() > 0){
					foreach ($result->result_array() as $key => $value) {
						$interviewer_arr[] = $value;
					}
				}

				//check if default interviewer already set.
				$this->db->where('user_id',$this->userinfo['user_id']);
				$this->db->where('candidate_id',$this->input->post('record_id'));
				$this->db->order_by('interviewer ASC');
				$result = $this->db->get('recruitment_manpower_candidate_interviewer');
				if ($result && $result->num_rows() > 0){
					$data['default_interviewer'] = true;
				}
			}
			
			// if ($this->input->post('record_id') != '-1'){
			// 	$candidate_info = $this->db->get_where('recruitment_manpower_candidate',array("candidate_id"=>$this->input->post('record_id'),"deleted"=>0));
			// 	if ($candidate_info && $candidate_info->num_rows() > 0){
			// 		$mrf_info = $this->get_interviewer($candidate_info->row()->mrf_id); //request_id
			// 		if (!$this->user_access[$this->module_id]['post']){
			// 			$this->db->where('division_id',$mrf_info['division_id']);
			// 			$this->db->where('department_id',$mrf_info['department_id']);
			// 		}

			// 		$this->db->where('employee_id !=', 1);
			// 		$this->db->where('inactive ',0);	
			// 		$this->db->where('deleted ',0);	
			// 		$this->db->order_by('lastname,firstname');
			// 		$result = $this->db->get('user');
			// 		if ($result && $result->num_rows() > 0){
			// 			foreach ($result->result_array() as $key => $value) {
			// 				$interviewer_arr[] = $value;
			// 			}
			// 		}
			// 	}
			// }

					
			$data['with_sched'] = 0;
			$data['interviewer'] = $interviewer_arr;
			$data['candidate_interviewer'] = $this->get_candidate_interviewer($this->input->post('record_id'),1);
			$data['technical_candidate_interviewer'] = $this->get_candidate_interviewer($this->input->post('record_id'),2);
			
			if( $this->input->post('mrf_id') ){
				$data['current_mrf_id'] = $this->input->post('mrf_id');
			}
			else{
				$data['current_mrf_id'] = '-1';
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

		parent::ajax_save();
		
		if( $this->input->post('candidate_status_id') ){
			$candidate_status_info = $this->db->get_where('recruitment_candidate_status', array('candidate_status_id' => $this->input->post('candidate_status_id')))->row();
			$this->db->update('recruitment_applicant',array('application_status_id'=>$candidate_status_info->application_status_id),array('applicant_id'=>$this->input->post('applicant_id')));
		}

		$result = $this->db->get_where('recruitment_candidates_appraisal',array("candidate_id"=>$this->key_field_val));
		if (!$result || $result->num_rows() < 1){
			$this->db->insert('recruitment_candidates_appraisal',array('candidate_id'=>$this->key_field_val));			
		}

		if ($this->input->post('interviewer_id')){
			$interview_date_arr = $this->input->post('interview_date');				
			$interviewer_info = array();				

        	// $this->db->delete('recruitment_manpower_candidate_interviewer', array('candidate_id' => $this->key_field_val));
			$int_ids = implode(',', $this->input->post('interviewer_id'));
			$interviewers = $this->db->query("SELECT * FROM hr_recruitment_manpower_candidate_interviewer WHERE candidate_id = ".$this->key_field_val." AND user_id NOT IN (".$int_ids.") AND interviewer_type = 1");
			
			if ($interviewers && $interviewers->num_rows() > 0) {
				
				foreach ($interviewers->result() as $key => $value) {
					$this->db->delete('recruitment_manpower_candidate_interviewer', array('candidate_interviewer_id' => $value->candidate_interviewer_id));	
				}
				
			}

			foreach ($this->input->post('interviewer_id') as $key => $value) {
				if ($value != '' ){
					$this->db->where('candidate_id', $this->key_field_val);
					$this->db->where('user_id', $value);
					$this->db->where('interviewer_type', 1);
					$result = $this->db->get('recruitment_manpower_candidate_interviewer');

					if ($result && $result->num_rows() > 0){
						$this->db->where('candidate_id', $this->key_field_val);
						$this->db->where('user_id', $value);
						$this->db->where('interviewer_type', 1);					
						$this->db->update('recruitment_manpower_candidate_interviewer', array("email_sent_to_interviewer" => 1, "datetime" => date('Y-m-d h-i-s',strtotime($interview_date_arr[$key])), "datetime_email_sent_to_interviewer" => date('Y-m-d G:i:s')));
					}	
					else{			
						$interviewer_info["candidate_id"] = $this->key_field_val;
						$interviewer_info["user_id"] = $value;
						$interviewer_info["datetime"] = date('Y-m-d h-i-s',strtotime($interview_date_arr[$key]));
						$interviewer_info["interviewer_type"] = 1;
						$this->db->insert('recruitment_manpower_candidate_interviewer', $interviewer_info);
					}
				}
				
			}
		}	

		if ($this->input->post('final_interviewer_id')){
			
			$final_int_ids = implode(',', $this->input->post('final_interviewer_id'));
			$final_interviewers = $this->db->query("SELECT * FROM hr_recruitment_manpower_candidate_interviewer WHERE candidate_id = ".$this->key_field_val." AND user_id NOT IN (".$final_int_ids.") AND interviewer_type = 2");

			if ($final_interviewers && $final_interviewers->num_rows() > 0) {
				
				foreach ($final_interviewers->result() as $key => $value) {
					$this->db->delete('recruitment_manpower_candidate_interviewer', array('candidate_interviewer_id' => $value->candidate_interviewer_id));	
				}
				
			}

			$final_interview_date_arr = $this->input->post('final_interview_date');				
			$interviewer_info = array();				

        	//$this->db->delete('recruitment_manpower_candidate_interviewer', array('candidate_id' => $this->key_field_val));

			foreach ($this->input->post('final_interviewer_id') as $key => $value) {
				if ($value != '' ){
					$this->db->where('candidate_id', $this->key_field_val);
					$this->db->where('user_id', $value);
					$this->db->where('interviewer_type', 2);
					$result = $this->db->get('recruitment_manpower_candidate_interviewer');

					if ($result && $result->num_rows() > 0){
						$this->db->where('candidate_id', $this->key_field_val);
						$this->db->where('user_id', $value);
						$this->db->where('interviewer_type', 2);					
						$this->db->update('recruitment_manpower_candidate_interviewer', array("email_sent_to_interviewer" => 1, "datetime"=>date('Y-m-d h-i-s',strtotime($final_interview_date_arr[$key])) ,"datetime_email_sent_to_interviewer" => date('Y-m-d G:i:s')));
					}	
					else{			
						$interviewer_info["candidate_id"] = $this->key_field_val;
						$interviewer_info["user_id"] = $value;
						$interviewer_info["datetime"] = date('Y-m-d h-i-s',strtotime($final_interview_date_arr[$key]));
						$interviewer_info["interviewer_type"] = 2;
						$this->db->insert('recruitment_manpower_candidate_interviewer', $interviewer_info);
					}
				}
			}
		}

		$data = array(
			'applicant_id' => $this->input->post('applicant_id'),
			'applied_date' => date('Y-m-d H:i:s'),
			'status' => $this->input->post('candidate_status_id'),
			'mrf_id' => 0
		);

		//save aaplication
		$this->db->insert('recruitment_applicant_application',$data);

		$this->db->where('request_id',$this->input->post('mrf_id'));
		$this->db->update('recruitment_manpower',array("status"=>"In-Process"));

/*		$this->db->where('candidate_id',$this->key_field_val);
		$this->db->delete('recruitment_candidates_appraisal');

		$this->db->insert('recruitment_candidates_appraisal',array('candidate_id'=>$this->key_field_val));*/


		switch( $this->input->post('candidate_status_id') ){
			case 3: // for exam
				// $this->send_for_exam_email();
			break;
		}


		//additional module save routine here

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}

	function delete_interviewer()
	{
		if(IS_AJAX)
		{
			$response->msg = "";

			if ( $this->user_access[$this->module_id]['edit'] ) {
				if( isset($_POST['candidate']) && $this->input->post('interviewer') )
				{
					
					$this->db->delete('recruitment_manpower_candidate_interviewer', array('candidate_id' => $this->input->post('candidate'), 'user_id' => $this->input->post('interviewer')));
					
					$response->msg_type = 'success';
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient priviledge to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			$data['json'] = $response;
			$this->load->view('template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
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
		$mrf_id = $this->session->userdata('mrf_id');

		if ($mrf_id != 0){
			$this->filter = "request_id = ".$mrf_id."";
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
		$this->listview_qry .= ',IF('.$this->db->dbprefix.'recruitment_manpower_candidate.is_internal = 0, CONCAT( '.$this->db->dbprefix.'recruitment_applicant.firstname, " ",REPLACE(CONCAT(UCASE(LEFT('.$this->db->dbprefix.'recruitment_applicant.middlename , 1))," .")," ", ""), " ", '.$this->db->dbprefix.'recruitment_applicant.lastname ), CONCAT( ' . $this->db->dbprefix . 'user.firstname, " ", ' . $this->db->dbprefix . 'user.middleinitial, " ", ' . $this->db->dbprefix . 'user.lastname )) t0firstnamelastname';

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if( $this->module == "user" && (!$this->user_access[$this->module_id]['post'] && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


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

		// $response->last_query = $this->db->last_query();
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
							} elseif ($detail['name'] == 'applicant_id' && $row['is_internal'] == 1) {
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

	function _set_left_join() {
		parent::_set_left_join();
				
		$this->db->join('recruitment_candidate_job_offer jo', 'jo.candidate_id = ' . $this->db->dbprefix.$this->module_table . '.candidate_id', 'left');
		$this->db->join('user', 'user.employee_id = recruitment_manpower_candidate.employee_id', 'left');
		$this->db->join('recruitment_candidate_blacklist_status bs', 'bs.recruitment_candidate_blacklist_status_id = ' . $this->db->dbprefix.$this->module_table . '.recruitment_candidate_blacklist_status', 'left');
		$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = ' . $this->db->dbprefix.$this->module_table . '.mrf_id', 'left');
		$this->db->join('user_position', 'recruitment_manpower.position_id = user_position.position_id', 'left');	
		$this->db->join('recruitment_applicant','recruitment_applicant.applicant_id = '.$this->db->dbprefix.'recruitment_manpower_candidate.applicant_id','left');	
	}	

	function _set_listview_query($listview_id = '', $view_actions = true) {
		parent::_set_listview_query($listview_id, $view_actions);

		$this->listview_qry .= ','.$this->db->dbprefix.'recruitment_manpower_candidate.applicant_id, '.$this->db->dbprefix.'recruitment_manpower_candidate.employee_id';
	}


	function get_mrf_by_position(){
		$position_id = false;
		$html = '';
		if ($this->input->post('applicant_id')){
			$this->db->where('applicant_id',$this->input->post('applicant_id'));
			$position_id = $this->db->get('recruitment_applicant')->row()->position_id;
		}
		elseif ($this->input->post('employee_id')) {
			$this->db->where('user_id',$this->input->post('employee_id'));
			$position_id = $this->db->get('user')->row()->position_id;
		}

		if ($position_id){
			$this->db->where('position_id',$position_id);
			$result = $this->db->get('recruitment_manpower');
			if ($result && $result->num_rows() > 0){
				$html .= '<option value="">Select…</option>';
				foreach ($result->result() as $row) {
					$html .= '<option value="'.$row->request_id.'">'.$row->document_number.'</option>';
				}
			}
		}

		$data['html'] = $html;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);		
	}	

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>Add</span></a></div>";

            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add quick-add' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>Quick Add</span></a></div>";     

            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add external' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='recruitment/applicants' href='javascript:void(0)'>";
            $buttons .= "<span>External</span></a></div>";                    
        }
         
/*        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }*/

        if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export-employees' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
	            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
	        }        
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
        
		if ( $this->user_access[$this->module_id]['edit'] ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        /*

        if ($record['applicant_id'] != 0 || $record['employee_id'] != 0){
			$actions .= '<a class="icon-button icon-16-next" tooltip="Next" href="'.base_url().'recruitment/candidate_result"></a>';
        }

        */

        $actions .= '</span>';

		return $actions;
	}	

	function get_interviewer($mrf_id){
		$this->db->where('recruitment_manpower.deleted',0);
		$this->db->where('request_id',$mrf_id);
		$this->db->join('user','user.user_id = recruitment_manpower.requested_by');
		$result = $this->db->get('recruitment_manpower');
		if ($result && $result->num_rows() > 0){
			$result_arr = $result->row_array();

			$this->db->where('department_id',$result_arr['department_id']);
			$this->db->where('division_id',$result_arr['division_id']);
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

	function get_mrf_details(){

		if( IS_AJAX ){

			$html = '';

			$this->db->join('user_position','user_position.position_id = recruitment_manpower.position_id','left');
			$this->db->where('recruitment_manpower.request_id',$this->input->post('mrf_id'));
			$manpower_info = $this->db->get('recruitment_manpower')->row();

			$response['document_number'] = $manpower_info->document_number;


			$html .= '<div style="width: 1142px;" class="ui-userdata ui-state-default" id="t_jqgridcontainer">
			            <strong>Manpower Request: </strong><em>'.$manpower_info->document_number.'</em> - '.$manpower_info->position.'
			          </div><br />';

			$data['html'] = $html;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}
		else{
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());
		}

	}

	function get_candidate_interviewer($candidate_id,$interviewer_type = 1){
		$this->db->where('recruitment_manpower_candidate_interviewer.deleted',0);
		$this->db->where('candidate_id',$candidate_id);
		$this->db->where('interviewer_type',$interviewer_type);
		$this->db->order_by('interviewer','ASC');
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
                $applicant_id = $this->db->get_where('recruitment_manpower_candidate', array("candidate_id" => $this->input->post('candidate_id')))->row()->applicant_id;
                $applicant_info = $this->db->get_where('recruitment_applicant', array("applicant_id" => $applicant_id))->row_array();

                $request['interviewers_name'] = $employee_info['salutation']." ".$employee_info['firstname']." ".$employee_info['middleinitial']." ".$employee_info['lastname']." ".$employee_info['aux'];
                $request['applicant_name'] = $applicant_info['firstname']." ".$applicant_info['middleinitial']." ".$applicant_info['lastname']." ".$applicant_info['aux'];
                $request['position_applied'] = $this->db->get_where('user_position', array("position_id" => $applicant_info['position_id']))->row()->position; 
                $request['interview_date'] = date('F d, Y', strtotime($this->input->post('date_time')));
                $request['interview_time'] = date('h:i a', strtotime($this->input->post('date_time')));

                //START Interviewer schedule sending
                $template = $this->template->get_module_template($this->module_id, 'candidate_schedule_interview');
                $message = $this->template->prep_message($template['body'], $request);

                if ($employee_info['email'] != ''){
					$recepients[] = $employee_info['email'];  
                	$this->template->queue(implode(',', $recepients), '', $template['subject'], $message);					              	
                }


                //START Applicant schedule sending> $applicant_info['position_id']))->row()->position; 
                $request['interview_venue'] = "";
                $request['interview_number'] = "";

                $template_app = $this->template->get_module_template($this->module_id, 'applicant_schedule_interview');
                $message = $this->template->prep_message($template_app['body'], $request);
                unset($recepients);
                if ($applicant_info['email'] != ''){
					$recepients[] = $applicant_info['email'];    
					sleep(1);
					$this->template->queue(implode(',', $recepients), '', $template_app['subject'], $message);					
        	
                	
                }                

				$this->db->where('candidate_id', $this->input->post('candidate_id'));
				$this->db->where('user_id', $this->input->post('employee_id'));
				$result = $this->db->get('recruitment_manpower_candidate_interviewer');

				if ($result && $result->num_rows() > 0){
					$this->db->where('candidate_id', $this->input->post('candidate_id'));
					$this->db->where('user_id', $this->input->post('employee_id'));					
					$this->db->update('recruitment_manpower_candidate_interviewer', array("email_sent_to_interviewer" => 1, "datetime_email_sent_to_interviewer" => date('Y-m-d G:i:s')));
				}
				else{
					$info = array(
									"candidate_id" => $this->input->post('candidate_id'),
									"user_id" => $this->input->post('employee_id'),
									"email_sent_to_interviewer" => 1,
									"datetime" => $this->input->post('date_time'),
									"datetime_email_sent_to_interviewer" => date('Y-m-d G:i:s')
								);
					$this->db->insert('recruitment_manpower_candidate_interviewer',$info);
				}
				$response->msg = 'Sent to Interviewer.';
				$response->msg_type = 'success';                	
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}	
	}

	function send_for_exam_email()
	{	
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());
		} else {
			$applicant_id = $this->input->post('applicant_id');

			$this->db->join('user_position','user_position.position_id = recruitment_applicant.position_id','left');
			$this->db->where('recruitment_applicant.applicant_id', $applicant_id);
			$this->db->where('recruitment_applicant.deleted', 0);
			$record = $this->db->get('recruitment_applicant');

			if (!$record || $record->num_rows() == 0) {
				//$response->msg 	    = 'Record not found. Request was not sent.';
				//$response->msg_type = 'error';
			} else {
				// Send email.
                // Load the template.            
                $this->load->model('template');

                $applicant_info = $record->row_array();

                $request['applicant_name'] = $applicant_info['firstname']." ".$applicant_info['lastname'];
                $request['company_name'] = "Pioneer Insurance";
                $request['position_name'] = $applicant_info['position'];
                $request['exam_date'] = date('F d, Y h:i:s', strtotime($this->input->post('date_schedule')));

                $template = $this->template->get_module_template($this->module_id, 'candidate_exam_schedule');
                //$message = $template['body'];
                $message = $this->template->prep_message($template['body'], $request);

				$recepients[] = $applicant_info['email'];

                $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);               	
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}	
	}

	function get_applicant(){
		$this->db->where_in('application_status_id', array(1,5));
		if ($this->input->post('applicant_id') != ' '){
			$this->db->or_where('applicant_id',$this->input->post('applicant_id'));	
		}		
		$this->db->where('deleted',0);
		$result = $this->db->get('recruitment_applicant');

		if ($result && $result->num_rows() > 0){
			$html = '<option value=""></option>';
			foreach ($result->result() as $row) {
				$middle_initial = ($row->middlename != "" ? ucfirst(substr($row->middlename, 0, 1)).'.' : "");
				$html .= '<option value="'.$row->applicant_id.'" '.($row->applicant_id == $this->input->post('applicant_id') ? 'SELECTED="SELECTED"' : '').'>'.$row->firstname.' '.$middle_initial.' '.$row->lastname.'</option>';
			}
		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function check_applicant_status(){

		$html = '';

		$this->db->join('recruitment_manpower_candidate','recruitment_manpower_candidate.applicant_id = recruitment_applicant.applicant_id','left');
		$this->db->join('application_status','application_status.application_status_id = recruitment_applicant.application_status_id','left');
		$this->db->join('recruitment_candidate_status','recruitment_candidate_status.candidate_status_id = recruitment_manpower_candidate.candidate_status_id','left');
		$this->db->where('recruitment_applicant.applicant_id',$this->input->post('applicant_id'));
		$applicant_result = $this->db->get('recruitment_applicant');

		if( $applicant_result->num_rows() > 0 ){

			$applicant_info = $applicant_result->row();

			if( $applicant_info->application_status_id == 2 || $applicant_info->application_status_id == 5 || $applicant_info->application_status_id == 6 ){

				$html .= '<div class="applicant_application_status"><br/>
					<label>Current Application Status<label>
					<div>
						<input class="input-text" readonly="" value="'.$applicant_info->application_status.' - '.$applicant_info->candidate_status.'" />
					</div>
				</div>';

			}
			else{

				$html .= '<div class="applicant_application_status"><br/>
					<label>Current Application Status<label>
					<div>
						<input class="input-text" readonly="" value="'.$applicant_info->application_status.'" />
					</div>
				</div>';

			}

		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);


	}

	function check_applicant_availability(){

		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());
		} else {

			$candidate_info = $this->db->get_where('recruitment_manpower_candidate',array('candidate_id'=>$this->input->post('record_id')))->row();

			if( ( $candidate_info->mrf_id == $this->input->post('mrf_id') ) && ( $candidate_info->applicant_id == $this->input->post('applicant_id') ) ){

				$response->msg = 'Applicant Available';
				$response->msg_type = 'success';  

			}
			else{

				$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id'=>$this->input->post('applicant_id')))->row();

				if( $applicant_info->application_status_id == 1 || $applicant_info->application_status_id == 5 ){

					$response->msg = 'Applicant Available';
					$response->msg_type = 'success';  

				}
				else{

					$response->msg = 'Chosen applicant not available';
					$response->msg_type = 'error'; 

				}

			}

			$data['json'] = $response;                		
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);

		}

	}

	function get_interviewer_form(){

		$html = '';

		$interviewer_arr = array();

		$user_info = $this->db->get_where('user',array("user_id"=>$this->userinfo['user_id']));
		if ($user_info && $user_info->num_rows() > 0){
			if (!$this->is_admin){
				// $interviewer_arr[] = $user_info->row_array();
			}

			
			// if (!$this->user_access[$this->module_id]['post']){
			// 	$this->db->where('department_id',$user_info->row()->department_id);
			// }

			$this->db->where_not_in('employee_id', array(1,2));
			$this->db->where('inactive ',0);	
			$this->db->where('deleted ',0);	

			$this->db->order_by('lastname,firstname');
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

		// if ($this->input->post('record_id') != '-1'){
		// 	$candidate_info = $this->db->get_where('recruitment_manpower_candidate',array("candidate_id"=>$this->input->post('record_id'),"deleted"=>0));
		// 	if ($candidate_info && $candidate_info->num_rows() > 0){
		// 		$mrf_info = $this->get_interviewer($candidate_info->row()->mrf_id); //request_id
		// 		if (!$this->user_access[$this->module_id]['post']){
		// 			$this->db->where('division_id',$mrf_info['division_id']);
		// 			$this->db->where('department_id',$mrf_info['department_id']);
		// 		}	
		// 		$this->db->where('employee_id !=', 1);
		// 		$this->db->where('inactive ',0);	
		// 		$this->db->where('deleted ',0);	
		// 		// $this->db->where('employee.user_id <>',$user_info->row()->user_id);
		// 		$this->db->order_by('lastname,firstname');
		// 		$result = $this->db->get('user');
		// 		if ($result && $result->num_rows() > 0){
		// 			foreach ($result->result_array() as $key => $value) {
		// 				$interviewer_arr[] = $value;
		// 			}
		// 		}
		// 	}
		// }

		$html .= '<div class="parent_container">
			<div class="form-item odd">
				<label class="label-desc gray" for="final_interview_id">
					Interviewer: 	
			  	</label>
				<div class="select-input-wrap">';

		if (count($interviewer_arr) > 0){
			if ($this->input->post('technical')){
				$interviewer_name = 'final_interviewer_id[]';
				$interviewer_id = 'final_interviewer_id';
				$interviewer_date = 'final_interview_date[]';
				$technical = 'technical';
			}
			else{
				$interviewer_name = 'interviewer_id[]';
				$interviewer_id = 'interviewer_id';
				$interviewer_date = 'interview_date[]';
				$technical = '';
			}		
			$html .= '
				<select class="'.$interviewer_id.'" name="'.$interviewer_name.'">
					<option value="">Select...</option>';

					foreach ($interviewer_arr as $key => $value) { 
						$html .= '<option value="'.$value['user_id'].'">'.$value['lastname'].',&nbsp;'.$value['firstname'].'</option>';
					}
			$html .= '</select>';
		
		}else{
				$html .= '<span class="red">No interviewer set under position settings.</span>';
		}
		$html .= '</div>
			</div>
			<div class="form-item1 even">
				<label class="label-desc gray" for="final_date_time">Date and Time:<span class="red font-large">*</span></label>
				<div class="text-input-wrap"><input type="text" class="input-text" name="'.$interviewer_date.'"/></div>
			</div>
			
			<div class="form-item2">
				<div style="padding-top:13px; width:100px;" class="icon-group">
						<a onclick="delete_benefit( $(this) )" href="javascript:void(0);" class="icon-button icon-16-minus"></a>
						<a class="icon-button icon-16-disk-back icon-16-send-email icon-send '.$technical.'" href="javascript:void(0)"></a>
				</div>
			</div>
							
		</div>	';

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */