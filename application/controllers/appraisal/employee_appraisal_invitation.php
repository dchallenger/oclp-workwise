<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_appraisal_invitation extends MY_Controller
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
	function index($ids = NULL)
    {
    	if (is_null($ids)) {
			//$this->session->set_flashdata('flashdata', 'No appraisal period specified.');
			redirect('employee/appraisal_period');
    	}

		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		$filter['ids'] = $ids;
		$this->filter = $filter['ids'];
		
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
			$data['buttons'] = 'admin/appraisal/send-email';
			//other views to load
			$data['views'] = array('employees/appraisal/editview_invitation');

			$data['views_outside_record_form'] = array();
			
			$record_id = $this->input->post('record_id');
			if ($record_id != -1) {
				$rec = $this->db->get_where($this->module_table, array('employee_appraisal_invitation_id' => $record_id))->row();
				$data['period_id'] = $rec->employee_appraisal_id;
				$ratee = $rec->appraisee_id;
				$data['invitation'] = $rec;
			}else{
				$data['period_id'] = $this->input->post('period_id');
				$ratee = $this->input->post('appraisee_id');
			}

			$appraisee = $this->system->get_employee($ratee);
			$data['appraisee'] = $appraisee;
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
		//additional module save routine here

		$update = array('appraisee_id' => $this->input->post('appraisee_id'), 'employee_appraisal_id' =>  $this->input->post('employee_appraisal_id'), 'employee_appraisal_criteria_question_item' =>  implode(',', $this->input->post('planning_item')));
		$this->db->where($this->key_field, $this->key_field_val);
		$this->db->update($this->module_table, $update);

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions


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
            $buttons .= "<a class='icon-16-add icon-16-add-listview' period_id='".$this->input->post('period_id')."' employeeid='".$this->uri->segment(4)."' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
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
        
		if ( $this->user_access[$this->module_id]['edit'] && !$record['email_sent']) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	function _append_to_select()
	{
		//$this->listview_qry .= ', employee_appraisal.employee_id, user.position_id';
		$this->listview_qry .= ', employee_appraisal_invitation.email_sent';
	}

	function send_email()
	{	
		if (IS_AJAX) {
			$record_id = $this->input->post('record_id');

			$this->db->where($this->key_field, $record_id);
			$record = $this->db->get($this->module_table);

			if ($record && $record->num_rows() > 0) {
				$this->load->model('template');

				$invitation = $record->row_array();
				$invited_rater = explode(',', $invitation['rater_id']);

				foreach ($invited_rater as $raters) {
					$rater = $this->db->get_where('user',array("user_id"=>$raters))->row_array();
					$recepients[] = $rater['email'];
					
					$contributors = array('employee_appraisal_period_id' => $invitation['employee_appraisal_id'], 'employee_appraisal_invitation_id' => $record_id, 'appraisee_id' => $invitation['appraisee_id'], 'contributor' => $raters);

					$this->db->insert('employee_appraisal_approver', $contributors);
				}

				$template['subject'] = "360 Email Invitation ";
				$template['body'] = $invitation['email_template'];
				$template['body'] .= "<strong>You may access the performance appraisal using the link below:</strong> <p></p>";
				$template['body'] .= "<a href='".site_url('employee/appraisal/edit/'.$invitation['appraisee_id'] . '/' . $invitation['employee_appraisal_id'])."'>Click to View the Employee Performance Appraisal Form</a>" ;


				$message = $this->template->prep_message($template['body'], $invitation);
	            $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);

	           	$this->db->where($this->key_field, $record_id);
				$this->db->update($this->module_table, array('email_sent' => 1));

				// $appraisal = $this->db->get_where('employee_appraisal_bsc', array('appraisal_period_id' => $invitation['employee_appraisal_id'], 'employee_id' => $invitation['appraisee_id']));
				
				
				

	            $response->msg = 'Invitation sent to rater/s.';
				$response->msg_tpe = 'success';

			}else{
				$response->msg 	    = 'Record not found. Invitation was not sent.';
				$response->msg_type = 'error';
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		} else {
			$this->session->set_flashdata('flashdata', 'Direct access is not allowed.');
			redirect(base_url());	
		}

		
	}

	// START custom module funtions
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


		//get subordinates
		$employees = array();
		$get_subordinate_circle = $this->system->get_employee_all_reporting_to($this->userinfo['user_id']);
		$filter = unserialize($this->encrypt->decode($this->input->post('filter')));
		$period_id = $filter['period_id'];

		$this->db->where('appraisal_planning_period.planning_period_id',$period_id);
		$this->db->where('appraisal_planning_period.deleted',0);
		$result = $this->db->get('appraisal_planning_period');

		$appraisal_planning_period_row = $result->row_array();

		if ($result && $result->num_rows() > 0){

			$appraisal_planning_period_employee = explode(',',$result->row()->employee_id);

			foreach( $appraisal_planning_period_employee as $employee_info ){
				if( ( in_array($employee_info, $get_subordinate_circle) || $employee_info == $this->userinfo['user_id'] ) || ( $this->is_admin || $this->is_superadmin ) ){

					array_push($employees,$employee_info);
				
				}
			}
		}

		//check contributors
		$this->db->where('employee_appraisal_invitation.employee_appraisal_id',$period_id);
		$this->db->where('employee_appraisal_invitation.deleted',0);
		$this->db->where('employee_appraisal_invitation.email_sent',1);
		$appraisal_invitation_result = $this->db->get('employee_appraisal_invitation');

		if( $appraisal_invitation_result && $appraisal_invitation_result->num_rows() > 0 ){

			foreach( $appraisal_invitation_result->result() as $invitation_info ){

				$contributor = explode(',',$invitation_info->rater_id);

				foreach( $contributor as $contributor_info ){
					if( ( $contributor_info == $this->userinfo['user_id'] && !in_array($invitation_info->appraisee_id, $employees) )  ){
						array_push($employees,$invitation_info->appraisee_id);
					}
				}
			}
		}

		if( count($employees) <= 0 ){
			$employees = 0;
		}


		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->join('employee_appraisal_criteria_question','employee_appraisal_criteria_question.employee_appraisal_criteria_question_id = employee_appraisal_invitation.employee_appraisal_criteria_question_id','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
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
		//$total_records =  $this->db->count_all_results();
		$this->db->get();
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
			$this->db->from($this->module_table);
			$this->db->join('employee_appraisal_criteria_question','employee_appraisal_criteria_question.employee_appraisal_criteria_question_id = employee_appraisal_invitation.employee_appraisal_criteria_question_id','left');
			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			
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
		}

		$ids = $this->encrypt->decode($this->input->post('filter'));
		$all_ids[] = explode("-", $ids);

		$this->db->where("employee_appraisal_invitation.appraisee_id = ".$all_ids[0][0]." AND hr_employee_appraisal_invitation.employee_appraisal_id =".$all_ids[0][1]);
	}

	function get_planning_item(){

		$performance_id = $this->input->post('performance_id');
		$appraisee_id = $this->input->post('appraisee_id'); 
		$period_id = $this->input->post('period_id'); 
		// $item_qry = "SELECT * FROM hr_appraisal_planning_item WHERE appraisal_criteria_question_id = ".$performance_id." AND appraisee_id = ".$appraisee_id;
		// $item_qry_result = $this->db->query($item_qry);

		// $planning_item = '';

		// $planning_item .= '<select id="planning_item" multiple="multiple" class="multi-select" name="planning_item[]">';
		// if( $item_qry_result && $item_qry_result->num_rows() > 0 ){
		// 	foreach ($item_qry_result->result() as $value) {
		// 		$planning_item .= '<option value="'.$value->appraisal_planning_item_id.'">'.$value->appraisal_planning_item.'</option>';
		// 	}
		// }
		// $planning_item .= '</select>';
		$this->db->where('appraisal_planning_period_id', $period_id);
		$this->db->where('employee_id', $appraisee_id);
		$item_qry = $this->db->get('employee_appraisal_planning')->row();
		$item_qry_result = unserialize($item_qry->employee_appraisal_criteria_question_item);

		$planning_item = '';

		$planning_item .= '<select id="planning_item" multiple="multiple" class="multi-select" name="planning_item[]">';
		
		foreach ($item_qry_result[$performance_id] as $key => $value) {
			$planning_item .= '<option value="'.$key.'">'.$value.'</option>';
		}

		$planning_item .= '</select>';

		$response->planning_item_html = $planning_item;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data); 		
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */