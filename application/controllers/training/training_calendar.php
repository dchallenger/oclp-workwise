<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_calendar extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Calendar';
		$this->listview_description = 'This module lists all defined training calendar(s).';
		$this->jqgrid_title = "Training Calendar List";
		$this->detailview_title = 'Training Calendar Info';
		$this->detailview_description = 'This page shows detailed information about a particular training calendar.';
		$this->editview_title = 'Training Calendar Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training calendar(s).';
    	$this->detail_type = array('session', 'budget');	

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
		$data['content'] = 'detailview';
		
		//other views to load
		$data['views'] = array();

		$instructor_result = $this->db->get('training_instructor');
		$instructor_list = $instructor_result->result_array();
		$instructor_list['count'] = $instructor_result->num_rows();
		$data['instructor_list'] = $instructor_list;

		$data['buttons'] = $this->module_link . '/detail-buttons';


		foreach( $this->detail_type as $detail ){
			$data[$detail] = $this->_get_training_detail($this->input->post('record_id'),$detail);
		}

		$training_calendar_details = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')));

		$data['training_calendar_details'] = $training_calendar_details->row();

		if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){

			$this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
			$this->db->join('employee','employee.employee_id = training_calendar_participant.employee_id','left');
			
			$this->db->where('( '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].'%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].'%"
			OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].',%" )');

		}


		$this->db->where('training_calendar_participant.training_calendar_id',$this->input->post('record_id'));
			$participant = $this->db->get('training_calendar_participant')->result_array();

			foreach( $participant as $key => $val ){

				$user_info = $this->db->get_where('user',array('employee_id' => $val['employee_id'] ))->row_array();

				$participant[$key]['name'] = $user_info['firstname'].' '.$user_info['middleinitial'].' '.$user_info['lastname'].' '.$user_info['aux'];

			}

			$data['participant'] = $participant;


			$data['participant_status_list'] = $this->db->get('training_calendar_participant_status')->result_array();


		if( $training_calendar_details->num_rows() > 0 ){
			$training_calendar_details = $training_calendar_details->row();
			$data['session_total_hours'] = $training_calendar_details->total_session_hours;
			$data['session_total_breaks'] = $training_calendar_details->total_session_breaks;
			$data['budget_total_cost'] = $training_calendar_details->total_cost;
			$data['budget_total_pax'] = $training_calendar_details->total_pax;
		}
		else{
			$data['session_total_hours'] = '0:00';
			$data['session_total_breaks'] = '0:00';
			$data['budget_total_cost'] = '0.00';
			$data['budget_total_pax'] = '0';
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

			$data['scripts'][] = multiselect_script();
			$data['scripts'][] = chosen_script();

			$data['content'] = 'editview';

			$data['buttons'] = $this->module_link . '/edit-buttons';
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$instructor_result = $this->db->get('training_instructor');
			$instructor_list = $instructor_result->result_array();
			$instructor_list['count'] = $instructor_result->num_rows();
			$data['instructor_list'] = $instructor_list;

			foreach( $this->detail_type as $detail ){
				$data[$detail] = $this->_get_training_detail($this->input->post('record_id'),$detail);
			}

			if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){

				$this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
				$this->db->join('employee','employee.employee_id = training_calendar_participant.employee_id','left');
				
				$this->db->where('( '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].'%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].'%"
				OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].',%" )');

			}

			$this->db->where('training_calendar_participant.training_calendar_id',$this->input->post('record_id'));
			$participant = $this->db->get('training_calendar_participant')->result_array();


			foreach( $participant as $key => $val ){

				$user_info = $this->db->get_where('user',array('employee_id' => $val['employee_id'] ))->row_array();

				$this->db->join('training_feedback','training_feedback.feedback_id = training_feedback_score.feedback_id');
				$this->db->where('training_feedback.employee_id',$participant[$key]['employee_id']);
				$feedback_info = $this->db->get('training_feedback_score');

				if( $feedback_info->num_rows > 0 ){
					$participant[$key]['feedback'] = 1;
				}
				else{
					$participant[$key]['feedback'] = 0;
				}

				$participant[$key]['name'] = $user_info['firstname'].' '.$user_info['middleinitial'].' '.$user_info['lastname'].' '.$user_info['aux'];

			}

			$data['participant'] = $participant;
			$this->db->where_not_in('participant_status_id',array(3));
			$this->db->where('deleted',0);
			$data['participant_status_list'] = $this->db->get('training_calendar_participant_status')->result_array();

			$training_calendar_details = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')));
			$data['training_calendar_details'] = $training_calendar_details->row();

			if( $training_calendar_details->num_rows() > 0 ){
				$training_calendar_details = $training_calendar_details->row();
				$data['session_total_hours'] = $training_calendar_details->total_session_hours;
				$data['session_total_breaks'] = $training_calendar_details->total_session_breaks;
				$data['budget_total_cost'] = $training_calendar_details->total_cost;
				$data['budget_total_pax'] = $training_calendar_details->total_pax;
			}
			else{
				$data['session_total_hours'] = '0:00';
				$data['session_total_breaks'] = '0:00';
				$data['budget_total_cost'] = '0.00';
				$data['budget_total_pax'] = '0';
			}

			$data['immediate_superior'] = 0;

			if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){
				$data['immediate_superior'] = 1;
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

		if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){

			$training_calendar_list = array();

			$this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
			$this->db->join('employee','employee.employee_id = training_calendar_participant.employee_id','left');
			$this->db->like('employee.reporting_to',$this->userinfo['user_id']);
			$this->db->or_like('employee.reporting_to',','.$this->userinfo['user_id']);
			$this->db->or_like('employee.reporting_to',','.$this->userinfo['user_id'].',');
			$this->db->or_like('employee.reporting_to',$this->userinfo['user_id'].',');
			$this->db->group_by('training_calendar_participant.training_calendar_id');
			$participant_subordinate_result = $this->db->get('training_calendar_participant');

			if( $participant_subordinate_result->num_rows() > 0 ){
				foreach( $participant_subordinate_result->result() as $subordinate_info ){
					array_push($training_calendar_list, $subordinate_info->training_calendar_id);
				}
			}

		}

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){
			$this->db->where_in($this->module_table.'.training_calendar_id',$training_calendar_list);
		}
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
		$total_records =  $this->db->count_all_results();
		//$response->last_query = $this->db->last_query();
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
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){
				$this->db->where_in($this->module_table.'.training_calendar_id',$training_calendar_list);
			}
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
	
	function ajax_save()
	{

		unset($_POST['training_provider']);

		parent::ajax_save();

		if( $this->input->post('publish') == 1 ){
			$this->db->update('training_calendar',array( 'published' => 1 ), array( $this->key_field => $this->key_field_val ) );
		}

		if( $this->input->post('confirm') == 1 ){
			//$this->db->update('training_calendar',array( 'confirmed' => 1 ), array( $this->key_field => $this->key_field_val ) );
		}


		$with_session = 0;
		$with_budget = 0;
		$session_info = array();
		$session_change_value = false;

		if( $this->user_access[$this->module_id]['post'] == "1" ){

			//Check if there are changes in session that is subjected for notification
			if( $this->input->post('record_id') != -1 ){

				$previous_session_result = $this->db->get_where('training_calendar_session',array('training_calendar_id'=>$this->key_field_val))->result_array();
				$current_session_result = $this->_rebuild_array($this->input->post('session'), 'session');

				if( count($previous_session_result) == count($current_session_result) ){

					$same_session = 0;

					foreach( $current_session_result as $current_session_info ){

						foreach( $previous_session_result as $previous_session_info ){

							if( date('Y-m-d',strtotime($previous_session_info['session_date'])) == date('Y-m-d',strtotime($current_session_info['session_date'])) ){
								if( date('H:i:s',strtotime($previous_session_info['sessiontime_from'])) == date('H:i:s',strtotime($current_session_info['sessiontime_from'])) ){
									if( date('H:i:s',strtotime($previous_session_info['sessiontime_to'])) == date('H:i:s',strtotime($current_session_info['sessiontime_to'])) ){
										$same_session++;
										break;
									}
								}
							}

						}

					}

					if( count($previous_session_result) != $same_session ){
						$session_change_value = true;
					}

				}
				else{
					$session_change_value = true;
				}

			}

			foreach( $this->detail_type as $detail ){

				$table = 'training_calendar_'.$detail;

				if ($this->db->table_exists($table)) {
					$this->db->delete($table, array('training_calendar_id' => $this->key_field_val));
					$post = $this->input->post($detail);

					if (!is_null($post) && is_array($post)) {
						// Handle the dates.
						foreach ($post as $key => $value) {
							
							$key_string_segments = explode('_', $key);

							if ( ( $key == 'session_date' || $key == 'session_date') ) {
								foreach ($post[$key] as &$date){
									if($date != ""){
										$date = date('Y-m-d', strtotime($date));
									}else{
										$date = $date;
									}
								}
							}
							elseif ( ( $key == 'sessiontime_from' || $key == 'sessiontime_to' || $key == 'breaktime_from' || $key == 'breaktime_to') ) {
								foreach ($post[$key] as &$time){
									if($time != ""){
										$time = date('H:i:s', strtotime($time));
									}else{
										$time = $time;
									}
								}
							}
							elseif( ( $key == 'session_rand' ) ){

								foreach ($post[$key] as $rand_key => $rand_val ){

									if( count($post['instructor_list'][$rand_val]) > 0 ){
										$post['instructor'][$rand_key] = implode(',',$post['instructor_list'][$rand_val]);
									}else{
										$post['instructor'][$rand_key] = $post['instructor_list'][$rand_val];
									}
								}
							}
						}

						if( $detail == 'session' ){

							//remove unecessary variables not use in saving data
							unset($post['instructor_list']);
							unset($post['session_rand']);

							$data = $this->_rebuild_array($post, $this->key_field_val);

							if( count($data) > 0 ){
								$with_session = 1;
							}

							$session_info = $data;
							$this->db->update($this->module_table, array( 'with_session' => $with_session ), array($this->key_field => $this->key_field_val));
							
						}
						elseif( $detail == 'budget' ){

							$data = $this->_rebuild_array($post, $this->key_field_val);

							if( count($data) > 0 ){
								$with_budget = 1;
							}

							$this->db->update($this->module_table, array( 'with_budget' => $with_budget ), array($this->key_field => $this->key_field_val));

						}
						elseif( $detail == 'participant' ){

							$data = $this->_rebuild_array($post, $this->key_field_val);

						}

						$this->db->insert_batch($table, $data);		
					}
				}

			}

		}

		//get start date
		$this->db->where('training_calendar_id',$this->key_field_val);
		$this->db->order_by('session_date','asc');
		$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

		$start_date = date('Y-m-d',strtotime($training_calendar_session_info->session_date));



		//Save total hours and toatl breaks
		$this->db->update($this->module_table, array( 'start_date'=> $start_date, 'total_session_hours' => $this->input->post('total_session_hours'), 'total_session_breaks' => $this->input->post('total_session_breaks'), 'total_cost' => $this->input->post('total_cost'), 'total_pax' => $this->input->post('total_pax') ), array($this->key_field => $this->key_field_val));
		
		//Add participants
		$participant_list = $this->input->post('participants');

		if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){

			$this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
			$this->db->join('employee','employee.employee_id = training_calendar_participant.employee_id','left');
			
			$this->db->where('( '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].'%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].'%"
			OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].',%" )');

		}

		$this->db->where('training_calendar_participant.training_calendar_id',$this->key_field_val);
		$calendar_participant_list = $this->db->get('training_calendar_participant');

		if( $calendar_participant_list->num_rows() > 0 ){

			$participant_count = 0;
			$calendar_participant_list = $calendar_participant_list->result();

			foreach( $calendar_participant_list as $calendar_participant_info ){

				$participant_count = 0;

				foreach( $participant_list as $participant_info ){
					if( $participant_info['id'] == $calendar_participant_info->employee_id ){
						$participant_count++;
					}
				}

				if( $participant_count == 0 ){

					$this->db->delete('training_calendar_participant',array('training_calendar_id' => $this->key_field_val, 'employee_id'=>$calendar_participant_info->employee_id));

				}

			}

		}
		

		foreach( $participant_list as $participant_info ){

			$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
			$this->db->where('training_calendar_participant.training_calendar_id',$this->key_field_val);
			$this->db->where('training_calendar_participant.employee_id',$participant_info['id']);
			$calendar_participant_info = $this->db->get('training_calendar_participant');

			if( $calendar_participant_info->num_rows() > 0 ){

				$calendar_participant_info_result = $calendar_participant_info->row();

				$participant_status = $participant_info['status'];

				if( $participant_status == "" ){
					$participant_status = $calendar_participant_info_result->participant_status_id;
				}

				$previous_participant_status = $calendar_participant_info_result->participant_status_id;
				$no_show = "";

				if( $participant_info['no_show'] != 1 && $participant_info['no_show'] != 0  ){

					if( $calendar_participant_info_result->no_show == 1 ){
						$no_show = $calendar_participant_info_result->no_show;
					}
					else{
						$no_show = 0;
					}

				}
				else{

					$no_show = $participant_info['no_show'];

				}

				$data = array(
					'participant_status_id' => $participant_status,
					'no_show' => $no_show,
					'training_application_id' => $participant_info['training_application_id'],
					'previous_participant_status_id' => $previous_participant_status,
					'remarks' => $participant_info['remarks']
				);

				if( $participant_status != $calendar_participant_info_result->participant_status_id ){

					$template_data = array();

					$this->load->model('template');
					$template = $this->template->get_module_template($this->module_id, 'notify_participant_status_notification');

					$template_data['participant_name'] = $calendar_participant_info_result->firstname.' '.$calendar_participant_info_result->lastname;
					$template_data['training_topic'] = $this->input->post('topic');
					$template_data['course_title'] = $this->db->get_where('training_subject',array('training_subject_id'=>$this->input->post('training_subject_id')))->row()->training_subject;

					$template_data['status'] = "";

					switch($participant_status){
						case 1:
							$template_data['status'] = "enrolled";
						break;
						case 2:
							$template_data['status'] = "confirmed";
						break;
						case 4:
							$template_data['status'] = "moved";
						break;
					}

					$cc = "";
					$cc_array = array();

					$approver_result = $this->system->get_approvers_and_condition($participant_info->employee_id,$this->module_id);

					if( count($approver_result) > 0 ){

						foreach( $approver_result as $approver_info ){

							$this->db->where('user_id',$approver_info['approver']);
							$this->db->where('deleted',0);
							$user_cc_result = $this->db->get('user');

							if( $user_cc_result->num_rows() > 0 ){
								$user_cc_info = $user_cc_result->row();
								array_push($cc_array, $user_cc_info->email);
							}

						}

						$cc = implode(',', $cc_array);


					}

					$message = $this->template->prep_message($template['body'], $template_data);
		            $this->template->queue($calendar_participant_info_result->email, '', $template['subject'], $message);
		            //$this->template->queue($calendar_participant_info_result->email, $cc, $template['subject'], $message);

				}

				$this->db->update('training_calendar_participant',$data,array('training_calendar_id' => $this->key_field_val,'employee_id'=>$participant_info['id']));

			}
			else{

				$this->load->model('template');
				$template = $this->template->get_module_template($this->module_id, 'new_participant_notification');

				$this->db->where('user_id',$participant_info['id']);
				$this->db->where('deleted',0);
				$participant_user_info = $this->db->get('user')->row();

				$template_data['firstname'] = $participant_user_info->firstname;
				$template_data['lastname'] = $participant_user_info->lastname;
				$template_data['participant_name'] = $participant_user_info->firstname.' '.$participant_user_info->lastname;
				$template_data['venue'] = $this->input->post('venue');
				$template_data['training_topic'] = $this->input->post('topic');
				$template_data['course_title'] = $this->db->get_where('training_subject',array('training_subject_id'=>$this->input->post('training_subject_id')))->row()->training_subject;
				$template_data['training_dates'] = "";

				$training_calendar_session_result = $this->db->get_where('training_calendar_session',array('training_calendar_id'=>$this->key_field_val));

				if( $training_calendar_session_result->num_rows() > 0 ){

					$template_data['training_dates'] .= "<table style='margin-left:.5in;'>";

					foreach( $training_calendar_session_result->result() as $calendar_session_info ){

						$template_data['training_dates'] .= "<tr><td>".date('F d Y',strtotime($calendar_session_info->session_date))." : ".date('h:i a',strtotime($calendar_session_info->sessiontime_from))." - ".date('h:i a',strtotime($calendar_session_info->sessiontime_to))."</td></tr>";

					}

					$template_data['training_dates'] .= "</table>";

				}

				$cc = "";
				$cc_array = array();

				$approver_result = $this->system->get_approvers_and_condition($participant_user_info->employee_id,$this->module_id);
				
					if( count($approver_result) > 0 ){

						foreach( $approver_result as $approver_info ){

							$this->db->where('user_id',$approver_info['approver']);
							$this->db->where('deleted',0);
							$user_cc_result = $this->db->get('user');

							if( $user_cc_result->num_rows() > 0 ){
								$user_cc_info = $user_cc_result->row();
								array_push($cc_array, $user_cc_info->email);
							}

						}

						$cc = implode(',', $cc_array);


					}

				$data = array(
					'training_calendar_id'=>$this->key_field_val,
					'participant_status_id' => $participant_info['status'],
					'training_application_id' => $participant_info['training_application_id'],
					'employee_id' => $participant_info['id'],
					'no_show' => $participant_info['no_show'],
					'nominate' => $participant_info['nominate'],
					'remarks' => $participant_info['remarks'],
					'previous_participant_status_id' => $participant_info['status']
				);

				$this->db->insert('training_calendar_participant',$data);


				$message = $this->template->prep_message($template['body'], $template_data);
		        $this->template->queue($participant_user_info->email, '', $template['subject'], $message);

			}

		}

		if( $session_change_value == true ){

			$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
	    	$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
	    	$this->db->join('training_calendar_type','training_calendar_type.calendar_type_id = training_calendar.calendar_type_id','left');
			$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id','left');
			$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
			$this->db->where('training_calendar.training_calendar_id',$this->key_field_val);
			$this->db->where_not_in('training_calendar_participant.participant_status_id',array(3,4));
			$training_calendar_participant_result = $this->db->get('training_calendar');

			if( $training_calendar_participant_result->num_rows() > 0 ){

				foreach( $training_calendar_participant_result->result() as  $participant_info ){

					$data = array();

					$training_calendar_session = $this->db->get('training_calendar_session',array('training_calendar_id'=>$participant_info->training_calendar_id));
					$no_of_days = $training_calendar_session->num_rows();

					$data['training_topic'] = $participant_info->topic;
					$data['participant_name'] = $participant_info->firstname.' '.$participant_info->lastname;
					$data['course_title'] = $participant_info->training_subject;
					$data['description'] = $participant_info->remarks;
					$data['training_dates'] = "";
					$data['no_of_days'] = $no_of_days;
					$data['venue'] = $participant_info->venue;
					$data['type'] = $participant_info->calendar_type;
					$data['provider'] = $participant_info->training_provider;
					$data['training_bond'] = $participant_info->rls;
					$data['cost'] = $participant_info->cost_per_pax;
					$data['last_registration_date'] = date('F d Y',strtotime($participant_info->last_registration_date));

					$training_calendar_session_result = $this->db->get_where('training_calendar_session',array('training_calendar_id'=>$participant_info->training_calendar_id));

					if( $training_calendar_session_result->num_rows() > 0 ){

						$data['training_dates'] .= "<table style='margin-left:.5in;'>";

						foreach( $training_calendar_session_result->result() as $calendar_session_info ){

							$data['training_dates'] .= "<tr><td>".date('F d Y',strtotime($calendar_session_info->session_date))." : ".date('h:i a',strtotime($calendar_session_info->sessiontime_from))." - ".date('h:i a',strtotime($calendar_session_info->sessiontime_to))."</td></tr>";

						}

						$data['training_dates'] .= "</table>";

					}


					$this->load->model('template');
					$template = $this->template->get_module_template($this->module_id, 'send_training_moved_notification');


					$data['firstname'] = $participant_info->firstname;
					$data['lastname'] = $participant_info->lastname;

					$cc = "";
					$cc_array = array();

					$approver_result = $this->system->get_approvers_and_condition($participant_info->employee_id,$this->module_id);

					if( count($approver_result) > 0 ){

						foreach( $approver_result as $approver_info ){

							$this->db->where('user_id',$approver_info['approver']);
							$this->db->where('deleted',0);
							$user_cc_result = $this->db->get('user');

							if( $user_cc_result->num_rows() > 0 ){
								$user_cc_info = $user_cc_result->row();
								array_push($cc_array, $user_cc_info->email);
							}

						}

						$cc = implode(',', $cc_array);


					}


		            $message = $this->template->prep_message($template['body'], $data);
		            $this->template->queue($participant_info->email, $cc, $template['subject'], $message);

		            $this->db->where('calendar_participant_id',$participant_info->calendar_participant_id);
		            $this->db->update('training_calendar_participant',array('send_confirm_status'=>1));

				}

			}

		}

	}

	function delete_participant(){

		if( $this->input->post('training_calendar_id') > 0 ){

			$this->db->where('employee_id',$this->input->post('participant_id'));
			$this->db->where('training_calendar_id',$this->input->post('training_calendar_id'));
			$this->db->where('deleted',0);
			$participant_result = $this->db->get('training_calendar_participant');

			if( $participant_result->num_rows() > 0 ){

				$this->db->delete('training_calendar_participant',array('training_calendar_id' => $this->input->post('training_calendar_id'), 'employee_id'=>$this->input->post('participant_id')));

			}

		}

		$response->msg_type = "success";
		$response->msg = "Participant successfully deleted";

		$this->load->view('template/ajax', array('json' => $response));

	}

	function delete_all_participants(){

		if( $this->input->post('training_calendar_id') > 0 ){

			$this->db->where('training_calendar_id',$this->input->post('training_calendar_id'));
			$this->db->where('deleted',0);
			$participant_result = $this->db->get('training_calendar_participant');

			if( $participant_result->num_rows() > 0 ){

				$this->db->delete('training_calendar_participant',array('training_calendar_id' => $this->input->post('training_calendar_id')));

			}

		}

		$response->msg_type = "success";
		$response->msg = "Participant successfully deleted";

		$this->load->view('template/ajax', array('json' => $response));




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
         
        /*
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }
        */

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }        
        
        $buttons .= "</div>";
                
		return $buttons;
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}

	function send_email(){

		if (IS_AJAX){

			/*

			$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id','left');
			$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
			$this->db->where('training_calendar_participant.send_nominate_status',0);
			$this->db->where('training_calendar_participant.nominate',1);
			$this->db->where('training_calendar.training_calendar_id',$this->input->post('record_id'));
			$training_calendar_participant_result = $this->db->get('training_calendar');

			if( $training_calendar_participant_result->num_rows() > 0 ){

				foreach( $training_calendar_participant_result->result() as  $participant_info ){

					$data = array();

					$participant_reporting_to = $this->system->get_reporting_to($participant_info->employee_id);
					$participant_reporting_to = explode(',',$participant_reporting_to);

					$this->load->model('template');
		            $template = $this->template->get_module_template($this->module_id, 'send_nominate_reporting_to');

					foreach( $participant_reporting_to as $reporting_to ){

						$data = array();

						$reporting_to_info = $this->db->get_where('user',array('employee_id'=>$reporting_to))->row();

						$data['firstname'] = $reporting_to_info->firstname;
						$data['lastname'] = $reporting_to_info->lastname;

		                $message = $this->template->prep_message($template['body'], $data);
		  
		                $this->template->queue($reporting_to_info->email, '', $template['subject'], $message);

					}

					$data = array();

					$template = $this->template->get_module_template($this->module_id, 'send_nominate_participant');
					$data['firstname'] = $participant_info->firstname;
					$data['lastname'] = $participant_info->lastname;

		            $message = $this->template->prep_message($template['body'], $data);
		            $this->template->queue($participant_info->email, '', $template['subject'], $message);

		            $this->db->where('calendar_participant_id',$participant_info->calendar_participant_id);
		            $this->db->update('training_calendar_participant',array('send_nominate_status'=>1));

				}

			}

			*/

			$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
			$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id','left');
			$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
			$this->db->where('training_calendar_participant.send_reject_status',0);
			$this->db->where('training_calendar_participant.nominate',0);
			$this->db->where('training_calendar.training_calendar_id',$this->input->post('record_id'));
			$training_calendar_participant_result = $this->db->get('training_calendar');

			if( $training_calendar_participant_result->num_rows() > 0 ){

				foreach( $training_calendar_participant_result->result() as  $participant_info ){

					$data = array();

					$participant_reporting_to = $this->system->get_reporting_to($participant_info->employee_id);
					$participant_reporting_to = explode(',',$participant_reporting_to);

					if( in_array($this->user->user_id, $participant_reporting_to) ){

						$this->load->model('template');
						$template = $this->template->get_module_template($this->module_id, 'send_reject_participant');
						$data['firstname'] = $participant_info->firstname;
						$data['lastname'] = $participant_info->lastname;
						$data['participant_name'] = $participant_info->firstname.' '.$participant_info->lastname;
						$data['training_topic'] = $participant_info->topic;
						$data['course_title'] = $participant_info->training_subject;

			            $message = $this->template->prep_message($template['body'], $data);
			            $this->template->queue($participant_info->email, '', $template['subject'], $message);

			            $this->db->where('calendar_participant_id',$participant_info->calendar_participant_id);
			            $this->db->update('training_calendar_participant',array('send_reject_status'=>1));

		        	}

				}
			}

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function send_confirm_email(){

		if (IS_AJAX){

			$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
	    	$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
	    	$this->db->join('training_calendar_type','training_calendar_type.calendar_type_id = training_calendar.calendar_type_id','left');
			$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id','left');
			$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
			$this->db->where('training_calendar_participant.participant_status_id != '.$this->db->dbprefix('training_calendar_participant').'.previous_participant_status_id');
			$this->db->where('( '.$this->db->dbprefix('training_calendar_participant').'.participant_status_id = 2 OR '.$this->db->dbprefix('training_calendar_participant').'.participant_status_id = 6 )');
			$this->db->where('training_calendar.training_calendar_id',$this->input->post('record_id'));
			$training_calendar_participant_result = $this->db->get('training_calendar');


			if( $training_calendar_participant_result->num_rows() > 0 ){

				foreach( $training_calendar_participant_result->result() as  $participant_info ){

					$data = array();

					$training_calendar_session = $this->db->get('training_calendar_session',array('training_calendar_id'=>$participant_info->training_calendar_id));
					$no_of_days = $training_calendar_session->num_rows();

					$data['training_topic'] = $participant_info->topic;
					$data['participant_name'] = $participant_info->firstname.' '.$participant_info->lastname;
					$data['course_title'] = $participant_info->training_subject;
					$data['description'] = $participant_info->remarks;
					$data['training_dates'] = "";
					$data['no_of_days'] = $no_of_days;
					$data['venue'] = $participant_info->venue;
					$data['type'] = $participant_info->calendar_type;
					$data['provider'] = $participant_info->training_provider;
					$data['training_bond'] = $participant_info->rls;
					$data['cost'] = $participant_info->cost_per_pax;
					$data['last_registration_date'] = date('F d Y',strtotime($participant_info->last_registration_date));

					$training_calendar_session_result = $this->db->get_where('training_calendar_session',array('training_calendar_id'=>$participant_info->training_calendar_id));

					if( $training_calendar_session_result->num_rows() > 0 ){

						$data['training_dates'] .= "<table style='margin-left:.5in;'>";

						foreach( $training_calendar_session_result->result() as $calendar_session_info ){

							$data['training_dates'] .= "<tr><td>".date('F d Y',strtotime($calendar_session_info->session_date))." : ".date('h:i a',strtotime($calendar_session_info->sessiontime_from))." - ".date('h:i a',strtotime($calendar_session_info->sessiontime_to))."</td></tr>";

						}

						$data['training_dates'] .= "</table>";

					}

					//$participant_reporting_to = $this->system->get_reporting_to($participant_info->employee_id);
					//$participant_reporting_to = explode(',',$participant_reporting_to);

					$this->load->model('template');
		            //$template = $this->template->get_module_template($this->module_id, 'send_status_reporting_to');

		            /*
					foreach( $participant_reporting_to as $reporting_to ){

						$reporting_to_info = $this->db->get_where('user',array('employee_id'=>$reporting_to))->row();

						$data['immediate_name'] = $reporting_to_info->firstname.' '.$reporting_to_info->lastname;

		                $message = $this->template->prep_message($template['body'], $data);
		  
		                $this->template->queue($reporting_to_info->email, '', $template['subject'], $message);

					}
					*/

					switch($participant_info->participant_status_id){
						case 2:
							$template = $this->template->get_module_template($this->module_id, 'send_confirm_status_participant');
						break;
						case 6:
							$template = $this->template->get_module_template($this->module_id, 'send_disapprove_status_participant');
						break;
						/*
						default:
							$template = $this->template->get_module_template($this->module_id, 'send_status_participant');
						break;
						*/
					}


					$data['firstname'] = $participant_info->firstname;
					$data['lastname'] = $participant_info->lastname;

		            $message = $this->template->prep_message($template['body'], $data);
		            $this->template->queue($participant_info->email, '', $template['subject'], $message);

		            $this->db->where('calendar_participant_id',$participant_info->calendar_participant_id);
		            $this->db->update('training_calendar_participant',array('send_confirm_status'=>1));

				}

			}

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{


		$training_calendar = $this->db->get_where($this->module_table, array($this->key_field => $record['training_calendar_id']));

		if( $training_calendar->num_rows() == 1 ){
			$training_calendar = $training_calendar->row();
		}

		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
        

		if ($this->user_access[$this->module_id]['post']) {
            $actions .= '<a class="icon-button icon-16-document-stack" module_link="'.$module_link.'" tooltip="Duplicate Training" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['post'] && $training_calendar->closed == 2 && $training_calendar->cancelled == 2 ) {
        	$actions .= '<a class="icon-button icon-16-close" module_link="'.$module_link.'" tooltip="Close Training" href="javascript:void(0)"></a>';
        	$actions .= '<a class="icon-button icon-16-cancel" module_link="'.$module_link.'" tooltip="Cancel Training" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] && $training_calendar->closed == 2 && $training_calendar->cancelled == 2 ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
		
		/*
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
        */       
        
        if ($this->user_access[$this->module_id]['delete'] && $training_calendar->closed == 2 && $training_calendar->cancelled == 2 ) {
            $actions .= '<a class="icon-button icon-16-trash delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record') ) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        

        $actions .= '</span>';

		return $actions;
	}
	// END - default module functions
	
	// START custom module funtions

	function get_template_form(){

		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->where('training_calendar.training_calendar_id',$this->input->post('calendar_id'));
		$calendar_info_result = $this->db->get('training_calendar');

		$calendar_participant_result = $this->db->get_where('training_calendar_participant',array('training_calendar_id'=>$this->input->post('calendar_id')));


		if( $calendar_info_result->num_rows > 0 ){

			$response->form = $this->load->view( $this->userinfo['rtheme'].'/training/training_calendar/template_form',array('calendar_info' => $calendar_info_result->row(), 'participant_count' => $calendar_participant_result->num_rows(), 'participants' => $calendar_participant_result->result()), true );
			$this->load->view('template/ajax', array('json' => $response));

		}

	}
	
	function get_category_filter(){
		$html = '';

		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1:
		    	$this->db->where('deleted', 0);
				$company = $this->db->get('user_company')->result_array();		
                $html .= '<select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2:
		    	$this->db->where('deleted', 0);
		    	$this->db->where('employee_work_assignment_category_id',1);
		    	$this->db->where('assignment',1);
		    	$division = $this->db->get('employee_work_assignment');

		    	$this->db->where('deleted', 0);
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		    	break;
		    case 3:
		    	$this->db->where('deleted', 0);
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;		        
		    case 4:
		    	$this->db->where('deleted', 0);
				$employee = $this->db->get('user')->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	if ($employee_record["firstname"] != "Super Admin"){
                        	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["middleinitial"].'&nbsp;'.$employee_record["lastname"].'&nbsp;'.$employee_record["aux"].'</option>';
                    	}
                    }
                $html .= '</select>';	
		        break;
		    case 5:

		    	$this->db->where('deleted',0);
                $this->db->order_by('job_rank','ASC');
                $rank_name = $this->db->get('user_rank')->result_array(); 	
                $html .= '<select id="rank" multiple="multiple" class="multi-select" style="width:400px;" name="rank[]">';
                    foreach($rank_name as $rank_name_record){
                        $html .= '<option value="'.$rank_name_record["job_rank_id"].'">'.$rank_name_record["job_rank"].'</option>';
                    }
                $html .= '</select>';				
		        break;     		        
		}	

        $data['html'] = $html;

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function get_employee_filter(){

		$type = $this->input->post('type');
		$id = $this->input->post('id');

		$this->db->join('user','user.employee_id = training_application.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.user_id','left');

		if( $this->input->post('nid') ){
			$nid = $this->input->post('nid');

			$this->db->where_not_in('user.employee_id',explode(',',$this->input->post('nid')));
		}

		if( $type == 'company' ){
			$this->db->where_in('user.company_id',explode(',',$this->input->post('id')));
		}
		elseif( $type == 'department' ){
			$this->db->where_in('user.department_id',explode(',',$this->input->post('id')));
		}
		elseif( $type == 'division' ){
			$this->db->where_in('user.division_id',explode(',',$this->input->post('id')));
		}
		elseif( $type == 'rank' ){
			$this->db->where_in('employee.rank_id',explode(',',$this->input->post('id')));
		}

		$existing_participant = array();

		$this->db->where('employee.resigned',0);
		$this->db->where('employee.resigned_date is null');
		$this->db->where('training_application.training_type',$this->input->post('training_type_id'));
		$this->db->where('training_application.training_course_id',$this->input->post('course_id'));
		$this->db->where('training_application.deleted',0);
		$this->db->where('training_application.status',5);
		$this->db->order_by('training_application.date_created','asc');

		$employee = $this->db->get('training_application')->result_array();	

        $html .= '<select id="employee" multiple="multiple" class="multi-select"  style="width:400px;" name="employee[]">';
            foreach($employee as $employee_record){

				$this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
				$this->db->where('training_calendar_participant.training_application_id',$employee_record['training_application_id']);
				$this->db->where('training_calendar.cancelled',2);
				$this->db->where('training_calendar.deleted',0);
				$this->db->where('training_calendar_participant.deleted',0);
				$existing_epaf_result = $this->db->get('training_calendar_participant');

				if( $existing_epaf_result->num_rows() == 0 ){

	            	if ($employee_record["firstname"] != "Super Admin" && !(in_array($employee_record["employee_id"], $existing_participant)) ){

	            		array_push($existing_participant, $employee_record["employee_id"]);
	                	$html .= '<option value="'.$employee_record["training_application_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["middleinitial"].'&nbsp;'.$employee_record["lastname"].'&nbsp;'.$employee_record["aux"].'</option>';
	            	
	            	}

            	}

            }
        $html .= '</select>';

        $data['html'] = $html;

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

	}

	function add_course_participants(){

		$excluded_participants = explode(',',$this->input->post('participant_list'));
		$existing_participant = array();

		$this->db->join('user','user.employee_id = training_application.employee_id','left');
		$this->db->where('training_application.training_type',$this->input->post('training_type_id'));
		$this->db->where('training_application.training_course_id',$this->input->post('training_subject_id'));
		$this->db->where_not_in('user.user_id',$excluded_participants);
		$this->db->where('training_application.status',5);
		$this->db->order_by('training_application.date_created','asc');
		$this->db->where('training_application.deleted',0);
		$training_application_result = $this->db->get('training_application')->result();

		$html = "";

		$participant_status_list = $this->db->get('training_calendar_participant_status')->result();

		foreach( $training_application_result as $employee_info ){

			$this->db->join('training_calendar','training_calendar.training_calendar_id = training_calendar_participant.training_calendar_id','left');
			$this->db->where('training_calendar_participant.training_application_id',$employee_info->training_application_id);
			$this->db->where('training_calendar.cancelled',2);
			$this->db->where('training_calendar.deleted',0);
			$this->db->where('training_calendar_participant.deleted',0);
			$existing_epaf_result = $this->db->get('training_calendar_participant');

			if( $existing_epaf_result->num_rows() == 0 ){

				if( !( in_array( $employee_info->employee_id, $existing_participant) )  ){

					array_push($existing_participant, $employee_info->employee_id);

					$rand = rand(1,10000);

					$html .= '<tr>
			    		<td style="text-align:center; vertical-align: middle;">'.$employee_info->firstname.' '.$employee_info->middleinitial.' '.$employee_info->lastname.' '.$employee_info->aux.'</td>';
			    	$html .= '
			    		<td style="text-align:center; vertical-align: middle;">
			    			<select name="participants['.$rand.'][status]" class="participant_status">
			    			';
			    			foreach( $participant_status_list as $participant_status ){
			    				$html .= '<option value="'.$participant_status->participant_status_id.'">'.$participant_status->participant_status.'</option>';
			    			}
			    	$html .= '</select>
			    		</td>
			    		<td style="text-align:center; vertical-align: middle;">
			    			<input type="radio" name="participants['.$rand.'][no_show]" class="no_show_yes" value="1" />Yes
			    			<input type="radio" name="participants['.$rand.'][no_show]" class="no_show_no" value="0" checked/>No
			    		</td>
			    		<td style="text-align:center; vertical-align: middle;">
			    			<textarea name="participants['.$rand.'][remarks]" class="participant_remarks"></textarea>
			    		</td>
			    		<td style="text-align:center; vertical-align: middle;">
			    			<a class="icon-button icon-16-delete delete-single delete-participant" href="javascript:void(0)" container="jqgridcontainer" tooltip="Delete"></a>
			    			<input type="hidden" class="participants" name="participants['.$rand.'][id]" value="'.$employee_info->employee_id.'" />
			    			<input type="hidden" class="training_application_id" name="participants['.$rand.'][training_application_id]" value="'.$employee_info->training_application_id.'" />
			    		</td>
			    	</tr>';

		    	}

	    	}

		}

		$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

	}

function add_participants(){

		$employee_id = explode(',',$this->input->post('employee_id'));
		$html = "";

		$this->db->join('user','user.employee_id = training_application.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.user_id','left');
		$this->db->where('training_application.training_type',$this->input->post('training_type_id'));
		$this->db->where('training_application.training_course_id',$this->input->post('course_id'));
		$this->db->where_in('training_application.training_application_id',$employee_id);
		$this->db->where('training_application.status',5);
		$this->db->group_by('training_application.employee_id');
		$this->db->order_by('training_application.date_created','asc');
		$this->db->where('training_application.deleted',0);
		$this->db->where('employee.resigned',0);
		$this->db->where('employee.resigned_date is null');
		$employee_list = $this->db->get('training_application')->result();

		$participant_status_list = $this->db->get('training_calendar_participant_status')->result();

		foreach( $employee_list as $employee_info ){

			$rand = rand(1,10000);

			$html .= '<tr>
	    		<td style="text-align:center; vertical-align: middle;">'.$employee_info->firstname.' '.$employee_info->middleinitial.' '.$employee_info->lastname.' '.$employee_info->aux.'</td>';

	    		
	    	$html .= '
	    		<td style="text-align:center; vertical-align: middle;">
	    			<select name="participants['.$rand.'][status]" class="participant_status">
	    			';
	    			foreach( $participant_status_list as $participant_status ){
	    				$html .= '<option value="'.$participant_status->participant_status_id.'">'.$participant_status->participant_status.'</option>';
	    			}
	    	$html .= '</select>
	    		</td>
	    		<td style="text-align:center; vertical-align: middle;">
	    			<input type="radio" name="participants['.$rand.'][no_show]" class="no_show_yes" value="1" />Yes
	    			<input type="radio" name="participants['.$rand.'][no_show]" class="no_show_no" value="0" checked/>No
	    		</td>
	    		<td style="text-align:center; vertical-align: middle;">
	    			<textarea name="participants['.$rand.'][remarks]" class="participant_remarks"></textarea>
	    		</td>
	    		<td style="text-align:center; vertical-align: middle;">
	    			<a class="icon-button icon-16-delete delete-single delete-participant" href="javascript:void(0)" container="jqgridcontainer" tooltip="Delete"></a>
	    			<input type="hidden" class="participants" name="participants['.$rand.'][id]" value="'.$employee_info->employee_id.'" />
	    			<input type="hidden" class="training_application_id" name="participants['.$rand.'][training_application_id]" value="'.$employee_info->training_application_id.'" />
	    		</td>
	    	</tr>';
		}

		$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

	}

	function calculate_total_hours(){

		$post = $this->input->post('session');

		if (!is_null($post) && is_array($post)) {
			// Handle the dates.
			foreach ($post as $key => $value) {
				
				$key_string_segments = explode('_', $key);

				if ( ( $key == 'session_date' || $key == 'session_date') ) {
					foreach ($post[$key] as &$date){
						if($date != ""){
							$date = date('Y-m-d', strtotime($date));
						}else{
							$date = $date;
						}
					}
				}
				elseif ( ( $key == 'sessiontime_from' || $key == 'sessiontime_to' || $key == 'breaktime_from' || $key == 'breaktime_to') ) {
					foreach ($post[$key] as &$time){
						if($time != ""){
							$time = date('H:i:s', strtotime($time));
						}else{
							$time = $time;
						}
					}
				}
				elseif( ( $key == 'session_rand' ) ){

					foreach ($post[$key] as $rand_key => $rand_val ){

						if( count($post['instructor_list'][$rand_val]) > 0 ){
							$post['instructor'][$rand_key] = implode(',',$post['instructor_list'][$rand_val]);
						}else{
							$post['instructor'][$rand_key] = $post['instructor_list'][$rand_val];
						}
					}
				}
			}


			$data = $this->_rebuild_array($post, $this->key_field_val);
	
		}

		//Calculate total hours and total breaks
		foreach( $data as $session_info ){
			if( ( strtotime($session_info['sessiontime_to']) - strtotime($session_info['sessiontime_from']) ) >= 0 ){
				$subtotal_session_seconds +=  ( strtotime($session_info['sessiontime_to']) - strtotime($session_info['sessiontime_from']) );
			}

			if( ( strtotime($session_info['breaktime_to']) - strtotime($session_info['breaktime_from']) ) >= 0 ){
				$subtotal_break_seconds +=  ( strtotime($session_info['breaktime_to']) - strtotime($session_info['breaktime_from']) );
			}
		}

		$subtotal_session_hours = floor( ( $subtotal_session_seconds ) / 3600 );
		$subtotal_session_minutes = floor( ( ( $subtotal_session_seconds ) % 3600 ) / 60 );
		$subtotal_break_hours = floor( ( $subtotal_break_seconds ) / 3600 );
		$subtotal_break_minutes = floor( ( ( $subtotal_break_seconds ) % 3600 ) / 60 );

		$total_session_hours = str_pad($subtotal_session_hours, 2, "0", STR_PAD_LEFT).":".str_pad($subtotal_session_minutes, 2, "0", STR_PAD_LEFT);
		$total_break_hours = str_pad($subtotal_break_hours, 2, "0", STR_PAD_LEFT).":".str_pad($subtotal_break_minutes, 2, "0", STR_PAD_LEFT);

		$response->total_session_hours = $total_session_hours;
		$response->total_break_hours = $total_break_hours;
		
		$this->load->view('template/ajax', array('json' => $response));

	}

	function calculate_total_cost_pax(){

		$post = $this->input->post('budget');

		if (!is_null($post) && is_array($post)) {
			// Handle the dates.
			foreach ($post as $key => $value) {
				
				$key_string_segments = explode('_', $key);

			}


			$data = $this->_rebuild_array($post, $this->key_field_val);
	
		}

		foreach( $data as $budget_info ){

			$subtotal_budget_cost += $budget_info['total'];
			$subtotal_budget_pax += $budget_info['pax'];

		}
		
		
		$response->total_cost = number_format($subtotal_budget_cost,2,'.','');
		$response->total_pax = $subtotal_budget_pax;
		
		$this->load->view('template/ajax', array('json' => $response));

	}

	function get_form($type) {
		if (IS_AJAX) {
			if ($type == '') {
				show_error("Insufficient data supplied.");
			} else {

				if( $type == 'session' ){

					$instructor_result = $this->db->get('training_instructor');
					$instructor_list = $instructor_result->result_array();
					$instructor_list['count'] = $instructor_result->num_rows();
					$data['instructor_list'] = $instructor_list;
					$data['session_count'] = $this->input->post('session_count');
					$data['session_rand'] = rand(1,10000000);

				}
				elseif( $type == 'budget' ){


				}

				$response = $this->load->view($this->userinfo['rtheme'] . '/training/training_calendar/'.$type.'_form', $data);

				$data['html'] = $response;

				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	private function _rebuild_array($array, $fkey = null) {
		if (!is_array($array)) {
			return array();
		}

		$new_array = array();

		$count = count(end($array));
		$index = 0;

		while ($count >= $index) {
			foreach ($array as $key => $value) {

				if( isset( $array[$key][$index] ) ){

					$new_array[$index][$key] = $array[$key][$index];
					if (!is_null($fkey)) {
						$new_array[$index][$this->key_field] = $fkey;
					}

				}
				else{

					continue;

				}
			}

			$index++;
		}

		return $new_array;
	}

	private function _get_training_detail($record_id = 0, $detail_type = "") {
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$response = array();

		$table = 'training_calendar_'.$detail_type;
		$this->db->where('training_calendar_id', $record_id);

		if( $detail_type == 'session' ){
			$this->db->order_by('session_no', 'ASC');
		}

		$result = $this->db->get($table);

		if ($result){
			$response = $result->result_array();
		}
		

		return $response;
	}

	function duplicate_calendar(){

		$training_calendar_id = $this->input->post('training_calendar_id');

		$sql = 'INSERT '.$this->db->dbprefix('training_calendar').' (training_subject_id, topic, calendar_type_id, venue, room, equipment, min_training_capacity, training_capacity,
		with_certification, with_budget, with_session) 
		SELECT training_subject_id, topic, calendar_type_id, venue, room, equipment, min_training_capacity, training_capacity,
		with_certification, with_budget, with_session
		FROM '.$this->db->dbprefix('training_calendar').' WHERE training_calendar_id = '.$training_calendar_id;

		$result = $this->db->query($sql);

		$new_training_calendar_id = $this->db->insert_id();

		$sql2 = 'INSERT '.$this->db->dbprefix('training_calendar_participant').' (training_calendar_id, training_application_id, employee_id, participant_status_id, no_show)
		SELECT "'.$new_training_calendar_id.'" as training_calendar_id, '.$this->db->dbprefix('training_calendar_participant').'.training_application_id, '.$this->db->dbprefix('training_calendar_participant').'.employee_id, "1", "0"
		FROM '.$this->db->dbprefix('training_calendar_participant').' 
		LEFT JOIN '.$this->db->dbprefix('employee').'  ON '.$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('training_calendar_participant').'.employee_id
		WHERE '.$this->db->dbprefix('training_calendar_participant').'.training_calendar_id = '.$training_calendar_id.'
		AND '.$this->db->dbprefix('employee').'.resigned = 0
		AND '.$this->db->dbprefix('training_calendar_participant').'.participant_status_id = 4';

		$response->last_query = $sql2;

		$result2 = $this->db->query($sql2);

		if( $result && $result2 ){
			$response->msg_type = 'success';
			$responseg = 'Training Calendar was successfully duplicated';
		}
		else{
			$response->msg_type = 'error';
			$response->msg = 'Error duplicating training calendar';
		}

		$this->load->view('template/ajax', array('json' => $response));

	}

	function cancel_calendar(){

		$training_calendar_id = $this->input->post('training_calendar_id');

		$this->db->where('training_calendar_id',$training_calendar_id);
		$result = $this->db->update('training_calendar',array('closed'=>3,'cancelled_date'=>date('Y-m-d')));

		if( $result ){

			$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
	    	$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
	    	$this->db->join('training_calendar_type','training_calendar_type.calendar_type_id = training_calendar.calendar_type_id','left');
			$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id','left');
			$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
			$this->db->where('training_calendar.training_calendar_id',$training_calendar_id);
			$training_calendar_participant_result = $this->db->get('training_calendar');

			if( $training_calendar_participant_result->num_rows() > 0 ){

				foreach( $training_calendar_participant_result->result() as  $participant_info ){

					$data = array();

					$data['course_title'] = $participant_info->training_subject;
					$data['training_topic'] = $participant_info->topic;
					$data['participant_name'] = $participant_info->firstname.' '.$participant_info->lastname;

					$this->load->model('template');
					$template = $this->template->get_module_template($this->module_id, 'send_training_cancelled_notification');

					$data['firstname'] = $participant_info->firstname;
					$data['lastname'] = $participant_info->lastname;

		            $message = $this->template->prep_message($template['body'], $data);
		            $this->template->queue($participant_info->email, '', $template['subject'], $message);

				}

			}

			$response->msg_type = 'success';
			$response->msg = 'Training Calendar was successfully cancelled';

		}
		else{

			$response->msg_type = 'error';
			$response->msg = 'Training Calendar was unsuccessfully cancelled';

		}


		$this->load->view('template/ajax', array('json' => $response));

	}

	function close_calendar(){

		$training_calendar_id = $this->input->post('training_calendar_id');

		//check if there are existing participants in the training calendar
		$this->db->where('training_calendar_id',$training_calendar_id);
		$this->db->where('deleted',0);
		$training_calendar_participant = $this->db->get('training_calendar_participant');

		if( $training_calendar_participant->num_rows() > 0 ){

			//check if there are still existing participants that are still in enrolled status
			$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id','left');
			$this->db->where('training_calendar.training_calendar_id',$training_calendar_id);
			$this->db->where('training_calendar_participant.participant_status_id',1);
			$enrolled_participant = $this->db->get('training_calendar');

			if( $enrolled_participant->num_rows() == 0 ){

				$this->db->select('user.user_id, user.employee_id, user.department_id, user.division_id, user_rank.job_rank_id, employment_status.employment_status_id, user_position.position_id, training_subject.rls, training_calendar.training_calendar_id, training_calendar.cost_per_pax, training_calendar_participant.training_application_id, training_calendar.venue, training_calendar.training_subject_id, training_subject.training_provider_id ');
				$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id','left');
				$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
				$this->db->join('employee','employee.employee_id = user.employee_id','left');
				$this->db->join('user_position','user_position.position_id = user.position_id','left');
				$this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id','left');
				$this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');
				$this->db->join('user_company_department','user_company_department.department_id = user.department_id','left');
				$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
				$this->db->where('training_calendar_participant.participant_status_id',2);
				$this->db->where('training_calendar.training_calendar_id',$training_calendar_id);
				$training_calendar_participant_result = $this->db->get('training_calendar');

				$total_employee = 0;

				if( $training_calendar_participant_result->num_rows() > 0 ){

					foreach( $training_calendar_participant_result->result() as $training_participant_info ){


						$this->db->where('training_application.training_application_id',$training_participant_info->training_application_id);
						$this->db->where('training_application.status',5);
						$training_application_result = $this->db->get('training_application');

						if( $training_application_result && $training_application_result->num_rows() > 0 ){

							$training_application_info = $training_application_result->row();

							$course = $this->db->get_where('training_subject', array('training_subject_id' => $training_application_info->training_course_id));
							$subject = ($course && $course->num_rows() > 0) ? $course->row()->training_subject : '' ;

							//get start date
							$this->db->where('training_calendar_id',$training_participant_info->training_calendar_id);
							$this->db->order_by('session_date','asc');
							$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

							$start_date = date('Y-m-d',strtotime($training_calendar_session_info->session_date));

							//get end date
							$this->db->where('training_calendar_id',$training_participant_info->training_calendar_id);
							$this->db->order_by('session_date','desc');
							$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

							$end_date = date('Y-m-d',strtotime($training_calendar_session_info->session_date));

							$total_training_hours = 0;

							//get total training hours
							$this->db->where('training_calendar_id',$training_participant_info->training_calendar_id);
							$this->db->order_by('session_date','asc');
							$training_calendar_session_result = $this->db->get('training_calendar_session');

							if( $training_calendar_session_result->num_rows() > 0 ){

								foreach( $training_calendar_session_result->result() as $session_info ){

									$session_start = strtotime($session_info->session_date.' '.$session_info->sessiontime_from);
									$session_end = strtotime($session_info->session_date.' '.$session_info->sessiontime_to);

									$total_training_hours += ( $session_end - $session_start ) / 60 / 60;
								}

							}


							$department_id = "";
							$division_id = "";
							$training_live_status = 1;

							//Commented due to even training with less than 4 hours are considered to be evaluated
							//If total training hours is less than or equal to 4 hours, theres no need to evaluate training
							//if( $total_training_hours <= 4 ){
							//	$training_live_status = 4;
							//}


							if( $training_participant_info->department_id != NULL || $training_participant_info->department_id != 0 ){
								$department_id = $training_participant_info->department_id;
							}

							if( $training_participant_info->division_id != NULL || $training_participant_info->division_id != 0 ){
								$division_id = $training_participant_info->division_id;
							}

							if( $training_participant_info->no_show == 0 ){

								$data = array(
									'training_application_id' => $training_application_info->training_application_id,
									'training_calendar_id' => $training_participant_info->training_calendar_id,
									'employee_id' => $training_participant_info->employee_id,
									'position_id' => $training_application_info->position_id,
									'division_id' => $training_application_info->division_id,
									'department_id' => $training_application_info->department_id,
									'rank_id' => $training_application_info->rank_id,
									'course' => $subject,
									'training_subject_id' => $training_participant_info->training_subject_id,
									'training_provider_id' => $training_participant_info->training_provider_id,
									'training_date' => $start_date,
									'venue' => $training_participant_info->venue,
									'competency' => $training_application_info->competency,
									'training_objectives' => $training_application_info->training_objectives,
									'knowledge_transfer' => $training_application_info->knowledge_transfer,
									'investment' => $training_application_info->investment,
									'training_hours' => $total_training_hours,
									'status_id' => $training_live_status,
									'evaluation_date' => date('Y-m-d', strtotime('+90 day', strtotime($end_date))),
									'training_application_type_id' => 1
								);
								
								$this->db->insert('training_live',$data);


								$employee_idp = $this->db->get_where('individual_development_plan', array('employee_id' => $training_participant_info->employee_id, 'year' => date('Y', strtotime($start_date)), 'deleted' => 0, 'idp_status' => 'Approved'));
								
								
								if($employee_idp && $employee_idp->num_rows > 0 ){
									$idp = $employee_idp->row();
									$percent = ($idp->idp_completed_planned) ? $idp->idp_completed_planned : 0 ;
									$percent_idp = ($percent + $training_application_info->idp_completion);
									
									$this->db->where('individual_development_plan_id', $idp->individual_development_plan_id);
									$this->db->update('individual_development_plan', array('idp_completed_planned' => $percent_idp));
								}
							}

							$training_balance_cost = 0;


							switch( $training_application_info->training_type ){
								case 1:
									$training_balance_cost = $training_application_info->itb - $training_application_info->investment;
								break;
								case 2:
									$training_balance_cost = $training_application_info->ctb - $training_application_info->investment;
								break;
								case 3:
									$training_balance_cost = $training_application_info->stb - $training_application_info->investment;
								break;
							}

							if( $training_balance_cost < 0 ){
								$training_balance_cost = 0.00;
							}
							else{
								$training_balance_cost = number_format($training_balance_cost, 2, '.', '');
							}

							$data = array(
								'training_application_id' => $training_application_info->training_application_id,
								'training_calendar_id' => $training_participant_info->training_calendar_id,
								'training_course' => $subject,
								'employee_id' => $training_participant_info->employee_id,
								'position_id' => $training_application_info->position_id,
								'division_id' => $training_application_info->division_id,
								'department_id' => $training_application_info->department_id,
								'rank_id' => $training_application_info->rank_id,
								'status_id' => $training_participant_info->employment_status_id,
								'start_date' => $start_date,
								'end_date' => $end_date,
								'training_balance' => $training_balance_cost,
								'service_bond' => $training_application_info->service_bond,
								'investment' => $training_application_info->investment,
								'stb' => $training_application_info->stb,
								'remaining_stb' => $training_application_info->remaining_stb,
								'excess_stb' => $training_application_info->excess_stb,
								'itb' => $training_application_info->itb,
								'remaining_itb' => $training_application_info->remaining_itb,
								'excess_itb' => $training_application_info->excess_itb,
								'ctb' => $training_application_info->ctb,
								'remaining_ctb' => $training_application_info->remaining_ctb,
								'excess_ctb' => $training_application_info->excess_ctb,
								'budgeted' => $training_application_info->budgeted,
								'allocated' => $training_application_info->allocated,
								'remaining_allocated' => $training_application_info->remaining_allocated,
								'remarks' => $training_application_info->remarks,
								'idp_completion' => $training_application_info->idp_completion,
							);

							$insert_training_employee_database = $this->db->insert('training_employee_database',$data);

							if( $insert_training_employee_database ){
								$total_employee++;
							}

						}


					}

				}

				if( $total_employee == $training_calendar_participant_result->num_rows() ){

					$this->db->where('training_calendar_id',$training_calendar_id);
					$result = $this->db->update('training_calendar',array('closed'=>1,'closed_date'=>date('Y-m-d')));

				}

				if( $result ){
					$response->msg_type = 'success';
					$response->msg = 'Training Calendar was successfully closed';
				}
				else{
					$response->msg_type = 'error';
					$response->msg = 'Error closing training calendar';
				}

			}
			else{

				$response->msg_type = 'error';
				$response->msg = 'There are still enrolled participants in the chosen training calendar. Please finish all pending participants.';


			}

		}
		else{

			$response->msg_type = 'error';
			$response->msg = 'Please ensured that there are participants in the chosen training calendar.';

		}

		$this->load->view('template/ajax', array('json' => $response));

	}

	function send_status_email(){

		$participants = $this->input->post('participants');

		foreach( $participants as $participants_info ){

			$this->db->where('training_calendar_id',$this->input->post('record_id'));
			$this->db->where('employee_id',$participants_info['id']);
			$training_participant = $this->db->get('training_calendar_participant');

			if( $training_participant->num_rows() > 0 ){

				$training_participant_info = $training_participant->row();

				if( $training_participant->send_nominate_status == 0 ){

					/*

					$this->load->model('template');
	                $template = $this->template->get_module_template($this->module_id, 'update201_status');
	                $message = $this->template->prep_message($template['body'], $requestor);
	  
	                // send email
	                $this->template->queue($requestor['email'], '', $template['subject'], $message);
					
					*/


				}


			}

		}

	}

	function get_training_subject_info(){


		if( $this->input->post('view') == 'edit' ){

			if( $this->input->post('training_subject_id') ){

				$training_subject_id = $this->input->post('training_subject_id');

				$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
				$this->db->join('training_category','training_category.training_category_id = training_subject.training_category_id','left');
				$this->db->where('training_subject.training_subject_id',$training_subject_id);
				$training_subject_info = $this->db->get('training_subject')->row();

				$response->training_provider = $training_subject_info->training_provider;
				$response->training_category = $training_subject_info->training_category;

			}
			else{

				$training_calendar_id = $this->input->post('record_id');

				$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
				$this->db->join('training_category','training_category.training_category_id = training_subject.training_category_id','left');
				$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
				$this->db->where('training_calendar.training_calendar_id',$training_calendar_id);
				$training_calendar_info = $this->db->get('training_calendar')->row();

				$response->training_provider = $training_calendar_info->training_provider;
				$response->training_category = $training_calendar_info->training_category;

			}

		}
		else{

			$training_calendar_id = $this->input->post('record_id');

			$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
			$this->db->join('training_category','training_category.training_category_id = training_subject.training_category_id','left');
			$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
			$this->db->where('training_calendar.training_calendar_id',$training_calendar_id);
			$training_calendar_info = $this->db->get('training_calendar')->row();

			$response->training_provider = $training_calendar_info->training_provider;
			$response->training_category = $training_calendar_info->training_category;


		}

		$this->load->view('template/ajax', array('json' => $response));

	}



	function print_record($record_id){

    	$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Training Calendar Export")
		            ->setDescription("Training Calendar Export");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;


        $styleTitleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

        $styleHeaderArray = array(
			'font' => array(
				'bold' => true,
			),
			'fill' => array(
		            'type' => PHPExcel_Style_Fill::FILL_SOLID,
		            'color' => array('rgb' => 'C5D9F1')
		        ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$styleFieldArray = array(
			'font' => array(
				'bold' => true,
			)
		);

		


        $activeSheet->setCellValue('A1', 'OCLP HOLDINGS, INC.');
		$activeSheet->setCellValue('A2', 'Training Calendar Export');

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleTitleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleTitleArray);

		$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
		$objPHPExcel->getActiveSheet()->mergeCells('A2:D2');

        $this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		$this->db->where('training_calendar.training_calendar_id',$record_id);
		$training_calendar = $this->db->get('training_calendar');


		$training_calendar_result = $training_calendar->row_array();


		//get start date
		$this->db->where('training_calendar_id',$record_id);
		$this->db->order_by('session_date','asc');
		$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

		$start_date = date('F d Y',strtotime($training_calendar_session_info->session_date));

		//get end date
		$this->db->where('training_calendar_id',$record_id);
		$this->db->order_by('session_date','desc');
		$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

		$end_date = date('F d Y',strtotime($training_calendar_session_info->session_date));


		$activeSheet->setCellValue('A4', 'Training Course');
	    $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleFieldArray);

	    $activeSheet->setCellValue('B4', $training_calendar_result['training_subject']);
	    //$objPHPExcel->getActiveSheet()->getStyle('A' . $line)->applyFromArray($styleDivisionArray);


	    $activeSheet->setCellValue('A5', 'Training Dates');
	    $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($styleFieldArray);

	    $activeSheet->setCellValue('B5', $start_date.' - '.$end_date);
	    //$objPHPExcel->getActiveSheet()->getStyle('A' . $line)->applyFromArray($styleDivisionArray);


	    $line = 7;


        $fields = array('Employee Name','Status','No Show','Remarks');

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

			$activeSheet->setCellValue($xcoor . $line, $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->applyFromArray($styleHeaderArray);
			
			$alpha_ctr++;
		}


		$this->db->join('training_calendar_participant_status','training_calendar_participant_status.participant_status_id = training_calendar_participant.participant_status_id','left');
		$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
		$this->db->where('training_calendar_participant.training_calendar_id',$record_id);
		$this->db->where('training_calendar_participant.deleted',0);
		$training_calendar_participant_result = $this->db->get('training_calendar_participant');


        $line++;
        $alpha_ctr = 0;

        foreach( $training_calendar_participant_result->result() as $participant_info ){

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

				$no_show = ( $participant_info->no_show == 1 )? "Yes" : "No";

        		switch( $field ){
        			case 'Employee Name':
        				$activeSheet->setCellValue($xcoor . $line, $participant_info->firstname.' '.$participant_info->lastname);
        			break;
        			case 'Status':
        				$activeSheet->setCellValue($xcoor . $line, $participant_info->participant_status);
        			break;
        			case 'No Show':
        				$activeSheet->setCellValue($xcoor . $line, $no_show);
        			break;
        			case 'Remarks':
        				$activeSheet->setCellValue($xcoor . $line, $participant_info->remarks);
        			break;
        		}

				
				$alpha_ctr++;

			}

			$line++;
			$alpha_ctr = 0;

        }


        // Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=Training_Calendar_Export'.date('Y-m-d').'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	


    }


	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>