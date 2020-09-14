<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_revalida_participants extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Revalida Participants';
		$this->listview_description = 'This module lists all defined training revalida participants(s).';
		$this->jqgrid_title = "Training Revalida Participants List";
		$this->detailview_title = 'Training Revalida Participants Info';
		$this->detailview_description = 'This page shows detailed information about a particular training revalida participants.';
		$this->editview_title = 'Training Revalida Participants Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training revalida participants(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index( $calendar_id = 0 )
    {


		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'training/training_revalida_participants/listview';
		
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

		if( $calendar_id != 0 ){
    		$data['default_query'] = true;
			$data['default_query_field'] = 'training_calendar_id';
			$data['default_query_val'] = $calendar_id;
			$data['calendar_id'] = $calendar_id;
    	}
    	else{
    		redirect('training/training_revalida/');
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

	
	function detail()
	{	
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';
		
		//other views to load
		$data['views'] = array();

		$data['buttons'] = $this->module_link . '/detail-buttons';

		//Get Participant and Calendar Details
		$this->db->select('user.employee_id, user.firstname, user.lastname, training_calendar.training_revalida_master_id, training_revalida.total_score, training_revalida.average_score');
		$this->db->join('training_calendar','training_calendar.training_calendar_id = training_revalida.training_calendar_id','left');
		$this->db->join('user','user.employee_id = training_revalida.employee_id','left');
		$this->db->where('training_revalida.training_revalida_id',$this->input->post('record_id'));
		$participant_details = $this->db->get('training_revalida')->row();

		$data['participant_id'] = $participant_details->employee_id;
		$data['participant_name'] = $participant_details->firstname." ".$participant_details->lastname;

		$data['total_score'] = $participant_details->total_score;
		$data['average_score'] = $participant_details->average_score;

		//Get Calendar Session Details
		$this->db->select('training_calendar_session.*');
		$this->db->where('training_revalida.training_revalida_id',$this->input->post('record_id'));
		$this->db->join('training_calendar_session','training_calendar_session.training_calendar_id = training_revalida.training_calendar_id');
		$calendar_details = $this->db->get('training_revalida');
		
		$data['calendar_session_details_count'] = $calendar_details->num_rows();

		if( $calendar_details->num_rows() > 0 ){
			$data['calendar_session_details'] = $calendar_details->result();
		}

		$data['instructor_list'] = $this->db->get_where('training_instructor',array('deleted'=>0))->result();
		

		//Get Feedback Questionnaire Items
		$answer_details_count = $this->db->get_where('training_revalida_score',array('training_revalida_id'=>$this->input->post('record_id')))->num_rows();

		$this->db->join('training_revalida_category','training_revalida_category.training_revalida_master_id = training_revalida_master.training_revalida_master_id','left');
		$this->db->join('training_revalida_item','training_revalida_item.training_revalida_category_id = training_revalida_category.training_revalida_category_id','left');
		$this->db->where('training_revalida_master.training_revalida_master_id',$participant_details->training_revalida_master_id);
		$this->db->group_by(array("training_revalida_category.training_revalida_category_id", "training_revalida_item.training_revalida_item_no")); 
		$questionnaire_details = $this->db->get('training_revalida_master');

		$data['revalida_questionnaire_item_count'] = $questionnaire_details->num_rows();

		if( $questionnaire_details->num_rows() > 0 ){
			$data['revalida_questionnaire_items'] = $questionnaire_details->result_array();

			foreach( $data['revalida_questionnaire_items'] as $key => $val ){

				$revalida_questionnaire_score = $this->db->get_where('training_revalida_score',array('training_revalida_id'=>$this->input->post('record_id'), 'training_revalida_item_id'=> $data['revalida_questionnaire_items'][$key]['training_revalida_item_id'] ));

				if( $revalida_questionnaire_score->num_rows() > 0 ){

					$revalida_questionnaire_score_info = $revalida_questionnaire_score->row();

					$data['revalida_questionnaire_items'][$key]['score'] = $revalida_questionnaire_score_info->score;
					$data['revalida_questionnaire_items'][$key]['remarks'] = $revalida_questionnaire_score_info->remarks;

				}
			}

		}


		$data['calendar_id'] = $this->input->post('calendar_id');

		if( $this->input->post('participant_direct') ){
			$data['employee_direct'] = $this->input->post('participant_direct');
		}
		else{
			$data['employee_direct'] = 0;
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
			$data['content'] = 'editview';
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$data['buttons'] = $this->module_link . '/edit-buttons';

			//Get Participant and Calendar Details
			$this->db->select('user.firstname, user.lastname, training_calendar.training_revalida_master_id, training_revalida.total_score, training_revalida.average_score');
			$this->db->join('training_calendar','training_calendar.training_calendar_id = training_revalida.training_calendar_id','left');
			$this->db->join('user','user.employee_id = training_revalida.employee_id','left');
			$this->db->where('training_revalida.training_revalida_id',$this->input->post('record_id'));
			$participant_details = $this->db->get('training_revalida')->row();

			$data['participant_name'] = $participant_details->firstname." ".$participant_details->lastname;

			$data['total_score'] = $participant_details->total_score;
			$data['average_score'] = $participant_details->average_score;

			//Get Calendar Session Details
			$this->db->select('training_calendar_session.*');
			$this->db->where('training_revalida.training_revalida_id',$this->input->post('record_id'));
			$this->db->join('training_calendar_session','training_calendar_session.training_calendar_id = training_revalida.training_calendar_id');
			$calendar_details = $this->db->get('training_revalida');
			
			$data['calendar_session_details_count'] = $calendar_details->num_rows();

			if( $calendar_details->num_rows() > 0 ){
				$data['calendar_session_details'] = $calendar_details->result();
			}


			$data['instructor_list'] = $this->db->get_where('training_instructor',array('deleted'=>0))->result();
			

			//Get Revalida Questionnaire Items
			$answer_details_count = $this->db->get_where('training_revalida_score',array('training_revalida_id'=>$this->input->post('record_id')))->num_rows();

			$this->db->join('training_revalida_category','training_revalida_category.training_revalida_master_id = training_revalida_master.training_revalida_master_id','left');
			$this->db->join('training_revalida_item','training_revalida_item.training_revalida_category_id = training_revalida_category.training_revalida_category_id','left');
			$this->db->where('training_revalida_master.training_revalida_master_id',$participant_details->training_revalida_master_id);
			$this->db->group_by(array("training_revalida_category.training_revalida_category_id", "training_revalida_item.training_revalida_item_no")); 
			$questionnaire_details = $this->db->get('training_revalida_master');

			$data['revalida_questionnaire_item_count'] = $questionnaire_details->num_rows();

			if( $questionnaire_details->num_rows() > 0 ){
				$data['revalida_questionnaire_items'] = $questionnaire_details->result_array();

				foreach( $data['revalida_questionnaire_items'] as $key => $val ){

					$revalida_questionnaire_score = $this->db->get_where('training_revalida_score',array('training_revalida_id'=>$this->input->post('record_id'), 'training_revalida_item_id'=> $data['revalida_questionnaire_items'][$key]['training_revalida_item_id'] ));

					if( $revalida_questionnaire_score->num_rows() > 0 ){

						$revalida_questionnaire_score_info = $revalida_questionnaire_score->row();

						$data['revalida_questionnaire_items'][$key]['score'] = $revalida_questionnaire_score_info->score;
						$data['revalida_questionnaire_items'][$key]['remarks'] = $revalida_questionnaire_score_info->remarks;

					}
				}

			}

			$data['calendar_id'] = $this->input->post('calendar_id');

			if( $this->input->post('participant_direct') ){
				$data['employee_direct'] = $this->input->post('participant_direct');
			}
			else{
				$data['employee_direct'] = 0;
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

		$revalida_item = $this->input->post('revalida_item');

		$this->db->where('training_revalida_id',$this->input->post('record_id'));
		$this->db->delete('training_revalida_score');



		//Get Participant and Calendar Details
		$this->db->select('user.firstname, user.lastname, training_calendar.training_revalida_master_id, training_revalida.total_score, training_revalida.average_score');
		$this->db->join('training_calendar','training_calendar.training_calendar_id = training_revalida.training_calendar_id','left');
		$this->db->join('user','user.employee_id = training_revalida.employee_id','left');
		$this->db->where('training_revalida.training_revalida_id',$this->input->post('record_id'));
		$participant_details = $this->db->get('training_revalida')->row();

		$this->db->join('training_revalida_category','training_revalida_category.training_revalida_master_id = training_revalida_master.training_revalida_master_id','left');
		$this->db->join('training_revalida_item','training_revalida_item.training_revalida_category_id = training_revalida_category.training_revalida_category_id','left');
		$this->db->where('training_revalida_master.training_revalida_master_id',$participant_details->training_revalida_master_id);
		$this->db->group_by(array("training_revalida_category.training_revalida_category_id", "training_revalida_item.training_revalida_item_no")); 
		$questionnaire_list = $this->db->get('training_revalida_master');
		$questionnaire_details = $questionnaire_list->result();

		foreach( $questionnaire_details as $questionnaire_detail_info ){

			if( in_array( $questionnaire_detail_info->score_type, array(1,2,4,5) ) ){
				$data = array(
					'training_revalida_id' => $this->key_field_val,
					'training_revalida_item_id' => $questionnaire_detail_info->training_revalida_item_id,
					'score' => $revalida_item[$questionnaire_detail_info->training_revalida_item_id]
				);
			}
			else{
				$data = array(
					'training_revalida_id' => $this->key_field_val,
					'training_revalida_item_id' => $questionnaire_detail_info->training_revalida_item_id,
					'remarks' => $revalida_item[$questionnaire_detail_info->training_revalida_item_id]
				);
			}

			$this->db->insert('training_revalida_score',$data);

		}
		
		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
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
        
		if ( $this->user_access[$this->module_id]['post'] || $this->user_access[$this->module_id]['approve'] ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        $actions .= '</span>';

		return $actions;
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
		$this->db->from($this->module_table);
		$this->db->join('employee','employee.employee_id = '.$this->module_table.'.employee_id','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

		if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){

			$this->db->where('( '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].'%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].'%"
     			OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].',%" )');

		}

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
			$this->db->join('employee','employee.employee_id = '.$this->module_table.'.employee_id','left');

			if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){

				$this->db->where('( '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].'%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].'%"
	     			OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].',%" )');

			}

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
	// END - default module functions
	
	// START custom module funtions
	
	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
           // $buttons .= "<div class='icon-label'><a class='icon-16-listback go_back_to_main' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Go Back</span></a></div>";                                
        
        
        $buttons .= "</div>";
                
		return $buttons;
	}


	function get_training_competencies(){

		if (IS_AJAX) {

			$employee_id = $this->input->post('employee_id');

			$employee_info = $this->system->get_employee($employee_id);

			//Get Participant and Calendar Details
			$this->db->select('user.firstname, user.lastname, training_calendar.training_revalida_master_id, training_revalida.total_score, training_revalida.average_score');
			$this->db->join('training_calendar','training_calendar.training_calendar_id = training_revalida.training_calendar_id','left');
			$this->db->join('user','user.employee_id = training_revalida.employee_id','left');
			$this->db->where('training_revalida.training_revalida_id',$this->input->post('record_id'));
			$participant_details = $this->db->get('training_revalida')->row();

			//Get Revalida Questionnaire Items
			$answer_details_count = $this->db->get_where('training_revalida_score',array('training_revalida_id'=>$this->input->post('record_id')))->num_rows();

			$this->db->join('training_revalida_category','training_revalida_category.training_revalida_master_id = training_revalida_master.training_revalida_master_id','left');
			$this->db->join('training_revalida_item','training_revalida_item.training_revalida_category_id = training_revalida_category.training_revalida_category_id','left');
			$this->db->where('training_revalida_master.training_revalida_master_id',$participant_details->training_revalida_master_id);
			$this->db->group_by(array("training_revalida_category.training_revalida_category_id", "training_revalida_item.training_revalida_item_no")); 
			$questionnaire_details = $this->db->get('training_revalida_master');

			$data['revalida_questionnaire_item_count'] = $questionnaire_details->num_rows();

			if( $questionnaire_details->num_rows() > 0 ){
				$data['revalida_questionnaire_items'] = $questionnaire_details->result_array();

				foreach( $data['revalida_questionnaire_items'] as $key => $val ){

					$revalida_questionnaire_score = $this->db->get_where('training_revalida_score',array('training_revalida_id'=>$this->input->post('record_id'), 'training_revalida_item_id'=> $data['revalida_questionnaire_items'][$key]['training_revalida_item_id'] ));

					if( $revalida_questionnaire_score->num_rows() > 0 ){

						$revalida_questionnaire_score_info = $revalida_questionnaire_score->row();

						$data['revalida_questionnaire_items'][$key]['score'] = $revalida_questionnaire_score_info->score;
						$data['revalida_questionnaire_items'][$key]['remarks'] = $revalida_questionnaire_score_info->remarks;

					}
				}

			}

			$data['type'] = $this->input->post('type');

			$response = $this->load->view($this->userinfo['rtheme'] . '/training/training_revalida/training_revalida_compentencies_form', $data);


			$data['html'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}


	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>