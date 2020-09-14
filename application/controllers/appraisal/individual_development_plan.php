<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Individual_development_plan extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists Individual Development Plan.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about Individual Development Plan';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about Individual Development Plan';

		if (!$this->user_access[$this->module_id]['post']) {
			// $this->filter = $this->db->dbprefix.$this->module_table.'.employee_id = ' . $this->userinfo['user_id'];
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
			$subordinate_id = array();
			if( count($subordinates) > 0 ){

				$subordinate_id = array();

				foreach ($subordinates as $subordinate) {
						$subordinate_id[] = $subordinate['user_id'];
				}
				$subordinate_id[] = $this->userinfo['user_id'];
			}
			$subordinate_list = implode(',', $subordinate_id);

			if( $subordinate_list != "" ){
				$this->has_sub = true;
				$this->filter = $this->db->dbprefix.$this->module_table.'.employee_id IN ('.$subordinate_list.')';
			}
			else{
				$this->filter = $this->db->dbprefix.$this->module_table.'.employee_id = ' . $this->userinfo['user_id'];
			}
		}
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

	function detail($record_id = "")
	{
		if($record_id != ""){
			$_POST['record_id'] = $record_id;
		}
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';
		$data['buttons'] = 'employees/appraisal/idp/detail-buttons';
		//other views to load
		$data['views'] = array();
		
		$data['dropdowns'] = $this->get_dropdown();
		$records = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));
 		if ($records && $records->num_rows() > 0) {
 			$data['records'] = $records->row();
 			$data['idp_details'] = json_decode($records->row()->individual_development_plan, true);
 			$record = $records->row_array();
 		}

 		$data['can_approve'] = $this->_can_approve($record);
		$data['can_decline'] = $this->_can_decline($record); 
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
			$data['buttons'] = 'employees/appraisal/idp/edit-buttons';
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$data['dropdowns'] = $this->get_dropdown();

			$records = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));
     		if ($records && $records->num_rows() > 0) {
     			$data['records'] = $records->row();
     			$data['idp_details'] = json_decode($records->row()->individual_development_plan, true);
     			$record = $records->row_array();
     		}

     		$data['is_hr'] = $this->user_access[$this->module_id]['post'];
     		$data['is_head'] = $this->is_head($this->userinfo['user_id']);
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

	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{
			$column = strtolower( $search['column'] );
			if ($column == 'hr_individual_development_plan.employee_id'){
				$search_string[] = 'firstname LIKE "%'. $value .'%"' ;
				$search_string[] = 'lastname LIKE "%'. $value .'%"' ;
			}
			else{
				if(sizeof(explode(' as ', $column)) > 1){
					$as_part = explode(' as ', $column);
					$search['column'] = strtolower( trim( $as_part[0] ) );
				}				
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"';
		}
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function _set_left_join()
	{
		$this->db->join('user', 'individual_development_plan.employee_id = user.employee_id');

		parent::_set_left_join();
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
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

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
							}
							elseif ($detail['name'] == 'idp_status'){
								$this->db->where('record_id', $row['individual_development_plan_id']);
								// $this->db->where('approver', $this->user->user_id);
								$this->db->where('module_id', $this->module_id);
								$this->db->join('user', 'user.employee_id=employee_appraisal_approver.approver', 'left');
								$approvers = $this->db->get('employee_appraisal_approver');
								
								$status = $row[$detail['name']];
								$cell[$cell_ctr] = $status;
								if ($approvers && $approvers->num_rows() > 0) {

									foreach($approvers->result() as $approver){
										$add_status = false;
										switch( $row['idp_status'] ){
											
											case 'For Approval':
												
												if($approver->focus == 0) {
													$status = "Waiting...";
													$add_status = true;
													$class = 'orange';
												}
												elseif($approver->focus == '1' && $approver->status == '4'){
													$status = "Approved";
													$add_status = true;
													$class = 'green';
												}
												else{
													$add_status = true;
													$status = $row[$detail['name']];
													$class = 'green';
												}

												break;
											case 'Decline':
													
													if($approver->status == '6') {
														$status = $row[$detail['name']];;
														$class = 'red';
														$add_status = true;
													}
											break;
											default:
												$status = $row[$detail['name']];
												break;
										}

										if( $add_status ){
											$cell[$cell_ctr] .= '<br/><em class="small">';
											$cell[$cell_ctr] .= $approver->firstname . ' '. $approver->lastname .': ';
											$cell[$cell_ctr] .= '<span class="'.$class.'">'. $status .'</span>';
											$cell[$cell_ctr] .= '</em>';
										}	

									}
								}

								

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
	
	function ajax_save()
	{
		$employee_id = $this->input->post('employee_id');
		$approvers_per_position = $this->system->get_approvers_and_condition($employee_id, $this->module_id);
		
		$where = 'idp_status NOT IN ("Cancelled","Decline")'; 
		$this->db->where($where);
		$check_records = $this->db->get_where($this->module_table, array('employee_id' => $employee_id, 'year' => date('Y'), 'deleted' => 0));

		if( empty($approvers_per_position) ){
            $response->msg = "Please contact HR Admin. Approver has not been set.";
            $response->msg_type = "error";
            $response->page_refresh = "true";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
        }

        if (($check_records && $check_records->num_rows() > 0) && $this->input->post('record_id') == '-1') {
        	$response->msg = "IDP for the year already created";
            $response->msg_type = "error";
            $response->page_refresh = "true";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
        }

        $idp_details = "";
		if($this->input->post('idp')){
			$idp_details = json_encode($this->input->post('idp'));
		}

		parent::ajax_save();
		$details['individual_development_plan'] = $idp_details;
		$details['year'] = date('Y');

		if ($this->input->post('record_id') == '-1') {

			$details['idp_status'] = 'Draft';
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $details);

			foreach( $approvers_per_position as $approver ){
				$approver_sequence = $approver['sequence'];
				$approver_id = $approver['approver'];
			
				$approver['record_id'] = $this->key_field_val;
				if ($this->user_access[$this->module_id]['post'] && $employee_id != $this->userinfo['user_id']) {
					if ($approver['focus'] == 1) {
						$approver['status'] = '3'; // approved
					}else{
						$approver['status'] = '1'; // draft

					}
				}
				$approver['module_id'] = $this->module_id;
				$this->db->insert('employee_appraisal_approver', $approver);
			}

		}else{
			$details['date_modified'] = date('Y-m-d H:i:s');

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $details);
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

	function get_employee_details()
	{
		if (IS_AJAX) {
			$employee_id = $this->input->post('employee_id');

			$this->db->select('user.division_id, department_id, company_id, position_id, rank_id, section_id, firstname, lastname, employed_date');
			$this->db->where('user.employee_id', $employee_id);
			$this->db->join('employee', 'employee.employee_id=user.employee_id');
			$employee = $this->db->get('user')->row_array();

			$this->db->where('FIND_IN_SET('.$employee['rank_id'].', rank)');
			// $this->db->where('training_type_id', $type);
			$this->db->where('deleted', 0);
			$training_budget = $this->db->get('training_budget');
			
			$amount_other = 0;
			$amount_it = 0;
			$amount_ct = 0;

			if ($training_budget && $training_budget->num_rows() > 0) {
				foreach ($training_budget->result() as $value) {

					switch ($value->training_type_id) {
						case 1:
							$amount_it = $value->budget_amount;	
							break;
						case 2:
							$amount_ct = $value->budget_amount;
							break;
						default:
							$amount_other = $value->budget_amount;

							break;
					}
					
				}
			}

			$response->department = $employee['department_id'];
			$response->division = $employee['division_id'];
			$response->company = $employee['company_id'];
			$response->rank = $employee['rank_id'];
			$response->position = $employee['position_id'];
			$response->name = $employee['firstname'] .' '. $employee['lastname'] ;
			$response->itb = $amount_it;
			$response->ctb = $amount_ct;
			$response->stb = $amount_other;
			$response->employed_date = date('F d, Y',strtotime($employee['employed_date']));

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
		

	}

	function get_dropdown()
	{
		$data = array();

		$rating = $this->db->get_where('appraisal_core_value_rating', array('deleted' => 0));
		$data['rating'] = ($rating && $rating->num_rows() > 0) ? $rating->result() : array();

		$this->db->order_by('appraisal_areas_development');
		$appraisal_areas_development = $this->db->get_where('appraisal_areas_development', array('deleted' => 0));
		$data['appraisal_areas_development'] = ($appraisal_areas_development && $appraisal_areas_development->num_rows() > 0) ? $appraisal_areas_development->result() : array();

		$this->db->order_by('learning_mode');
		$learning_mode = $this->db->get_where('appraisal_learning_mode', array('deleted' => 0));
		$data['learning_mode'] = ($learning_mode && $learning_mode->num_rows() > 0) ? $learning_mode->result() : array();
		
		$this->db->order_by('training_category');
		$competencies = $this->db->get_where('training_category', array('deleted' => 0));
		$data['competencies'] = ($competencies && $competencies->num_rows() > 0) ? $competencies->result() : array();

		$this->db->order_by('appraisal_development_category');
		$appraisal_development_category = $this->db->get_where('appraisal_development_category', array('deleted' => 0));
		$data['appraisal_development_category'] = ($appraisal_development_category && $appraisal_development_category->num_rows() > 0) ? $appraisal_development_category->result() : array();

		$this->db->order_by('target_completion');
		$target_completion = $this->db->get_where('appraisal_target_completion', array('deleted' => 0));
		$data['target_completion'] = ($target_completion && $target_completion->num_rows() > 0) ? $target_completion->result() : array();

		$budget_allocation = $this->db->get_where('training_type', array('deleted' => 0));
		$data['budget_allocation'] = ($budget_allocation && $budget_allocation->num_rows() > 0) ? $budget_allocation->result() : array();

		return $data;

	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		$is_head = $this->is_head($this->userinfo['user_id']);

		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$with_live = false;

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
       
		if ( $this->user_access[$this->module_id]['edit'] ) {
			if (($record['idp_status'] == 'Draft' ) && $record['employee_id'] == $this->userinfo['user_id']) {
				$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
			}elseif( ($this->user_access[$this->module_id]['post'] == 1) && !(in_array($record['idp_status'] , array('Cancelled','Decline','For Approval'))) && ($record['employee_id'] != $this->userinfo['user_id'])){
				 $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
			}elseif (($is_head && ($record['employee_id'] != $this->userinfo['user_id']) ) && in_array($record['idp_status'] , array('Approved','For Approval')) ) {
				$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
			}
           
        } 
		if ($this->user_access[$this->module_id]['delete']) {		
	        if ($record['idp_status'] == 'Draft' || ($record['idp_status'] == 'For Approval' && $this->user_access[$this->module_id]['post'])){
	        	
		            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
		        
	        }else if ($record['idp_status'] == 'Draft' &&  ($record['employee_id'] == $this->userinfo['user_id'])) {
	        	
		            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
		        
	        }
		}
        if ($record['idp_status'] == 'For Approval' && ($record['employee_id'] != $this->userinfo['user_id'])) {
        	
        	if ($this->_can_approve($record)) {
        		$actions .= '<a class="icon-button icon-16-approve approve-single"  record_id="'.$record['individual_development_plan_id'].'" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}

        	if ($this->_can_decline($record)) {
        		$tooltip = 'Disapprove';
				$actions .= '<a class="icon-button icon-16-disapprove cancel-single" record_id="'.$record['individual_development_plan_id'].'" tooltip="' . $tooltip . '" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}
				
        }

    	if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }  

        $actions .= '</span>';

		return $actions;
	}

	function send_email() {
		if (IS_AJAX) {

			$record_id = $this->input->post('record_id');
			
			$records = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row_array();
     		
     		$employee = $this->db->get_where('user',array("user_id"=>$records['employee_id']))->row_array();
			$vars['employee'] =  $employee['salutation'] .' '. $employee['firstname']. ' ' . $employee['lastname'];
	
			$vars['requested_date'] = date('F d, Y' , strtotime($records['date_requested']));

			$status = 1;
			
			if ($records['idp_status'] == 'Draft')
			{
				$this->send_hr_review($records);
				return;
			}
			elseif($records['idp_status'] == 'HR Review'){
				$template_code = 'idp_for_approval ';
				// $status = 4;				
			}

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				$this->db->where_in('record_id', $record_id);
				$this->db->where('status !=', 4);
				$approvers = $this->db->get('employee_appraisal_approver');

				// if ($approvers && $approvers->num_rows() == 1) {
				// 	$template_code = 'tad_for_approval_div_head';
				// }	

				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template(0, $template_code);

				$this->db->where_in('record_id', $record_id);
				$this->db->where('focus', 1);
				$this->db->where('module_id', $this->module_id);
                $this->db->order_by('sequence', 'desc');
				$approver_user = $this->db->get('employee_appraisal_approver'); //->result();
				$cnt = 0;
				if( $approver_user && $approver_user->num_rows() > 0  ){
					foreach ($approver_user->result() as $a) {
						if ($a->status == 1) {
							$cnt++;
						}
                        switch($a->condition){
                            case 1:
                                if(!isset( $app_array) ) $app_array[] = $a->approver;
                                break;
                            case 2:
                            case 3:
                                $app_array[] = $a->approver;
                                break;
                        }
                    }

                    $app_array = array_unique($app_array);

                    if( is_array( $app_array ) && sizeof($app_array) > 0 ){
	                    $this->db->where_in('user_id', $app_array);
	                    $result = $this->db->get('user');
	                    $result = $result->result_array();

	                    foreach ($result as $row) {
	                    	$recepients[] = trim($row['email']);
	                        $vars['approver'] = $row['salutation']." ".$row['firstname']." ".$row['lastname'];
	                       
	                        $message = $this->template->prep_message($template['body'], $vars);
							// $emailed = $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);                   
	                    }
					}
					// If queued successfully set the status to For Approval.
					if (true) {

						$this->db->where_in('approver', $app_array);
	                    $this->db->where(array('record_id' => $record_id));   
	                    $this->db->where('module_id', $this->module_id);
	                    $this->db->update('employee_appraisal_approver', array('status' => '3'));

						$this->db->where($this->key_field, $record_id);
						$this->db->update($this->module_table, array('idp_status' => 'For Approval', 'date_modified' => date('Y-m-d')));

						$response->record_id  = $record_id;
						$response->msg_type = 'success';
						$response->msg = 'Individual Development Planning Request Sent.';

					}
				}
			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function send_hr_review($records)
	{
		if (IS_AJAX) {
			$record_id = $records['individual_development_plan_id'];
			$employee = $this->db->get_where('user',array("user_id"=>$records['employee_id']))->row_array();
			$vars['employee'] =  $employee['salutation'] .' '. $employee['firstname']. ' ' . $employee['lastname'];
			$vars['requested_date'] = date('F d, Y');

			$this->db->where('FIND_IN_SET('.$records['division_id'].', division_id)');
			$hr_details = $this->db->get('training_email_settings')->row();

			$hr = array();

			$hr_ids = explode(',', $hr_details->email_to);
			foreach ($hr_ids as $id) {
				$hrs = $this->db->get_where('user',array("user_id"=>$id))->row_array();
				$hr['email'][] = $hrs['email'];
				$hr['hr_name'][] = $hrs['salutation'] .' '. $hrs['firstname']. ' ' . $hrs['lastname'];
			}
			
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template(0, 'idp_hr_review');
				
				for ($i=0; $i < count($hr['email']); $i++) { 
					$recepients = $hr['email'][$i];
					$vars['hr_name'] = $hr['hr_name'][$i];

					$message = $this->template->prep_message($template['body'], $vars);
					$this->template->queue($recepients, '', $template['subject'], $message);

				}

                    $this->db->where($this->key_field, $record_id);
					$this->db->update($this->module_table, array('idp_status' => 'HR Review', 'date_modified' => date('Y-m-d'), 'date_requested' => date('Y-m-d')));

					$response->msg_type = 'success';
					$response->msg = 'Individual Development Planning Request Sent.';

			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	
				
		}else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}


	function send_status_email($record_id, $status) {
		if (IS_AJAX) {

			$records = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row_array();
     		
     		$employee = $this->db->get_where('user',array("user_id"=>$records['employee_id']))->row_array();
			$vars['employee'] =  $employee['salutation'] .' '. $employee['firstname']. ' ' . $employee['lastname'];

			$vars['requested_date'] = date('F d, Y', strtotime($records['date_requested']));

			$this->db->where('FIND_IN_SET('.$records['division_id'].', division_id)');
			$hr_details = $this->db->get('training_email_settings')->row();

			$hr = array();

			$hr_ids = explode(',', $hr_details->email_to);
			foreach ($hr_ids as $id) {
				$hrs = $this->db->get_where('user',array("user_id"=>$id))->row_array();
				$hr['email'][] = $hrs['email'];
				
			}
			$cc_email = implode(',', $hr['email']);

			switch ($status) {
				case 5:
					$template_code = 'tad_approved';
					$msg = 'Approved';
					break;
				case 6:
					$template_code = 'tad_disapproved';
					$msg = 'Disapproved';
					break;
			}

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template(0, $template_code);

				$message = $this->template->prep_message($template['body'], $vars);
				$emailed = $this->template->queue($employee['email'], $cc_email, $template['subject'], $message);     

				// If queued successfully set the status to For Approval.
				if (true) {
					$response->record_id = $record_id;
					$response->msg_type = 'success';
					$response->msg = 'Individual Development Planning '. $msg;
				}
			
			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function change_status() {

		if (IS_AJAX) {

			$record_id = $this->input->post('record_id');
			$idp_status  = $this->input->post('status');
			$remarks  = $this->input->post('remarks_approver');
			switch ($idp_status) {
				case 'Approved':
					$status = 4;	
				break;
				case 'Decline':
					$status = 6;	
				break;
			}

			$approver = $this->db->get_where('employee_appraisal_approver', array('approver' => $this->user->user_id, 'record_id' => $record_id, 'module_id' => $this->module_id))->row();
			
			$this->db->where($this->key_field, $record_id);
			$record = $this->db->get($this->module_table)->row();	

			if (!isset($record_id) && $record_id <= 0) {
				$response->msg      = 'No record specified.';
				$response->msg_type = 'error';				
			}
			else {
				$this->db->update('employee_appraisal_approver', array('status' => $status), array('approver' => $this->user->user_id, 'record_id' => $record_id, 'module_id' => $this->module_id));
				$approver_details = $this->system->get_employee($this->user->user_id);
				$approver_name = $approver_details['firstname'].' '.$approver_details['lastname'];
				 switch( $status ){
                    case 4:
                    	if ($this->user_access[$this->module_id]['post']) { // hr
                    		$data['approver_remarks'] = $remarks;
                    		$data['idp_status'] = 'Approved';
                            $data['date_approved'] = date('Y-m-d H:i:s');
                            $this->db->where($this->key_field, $record_id);
                            $this->db->update($this->module_table, $data);

                            $this->db->update('employee_appraisal_approver', array('status' => 4), array('record_id' => $record_id, 'module_id' => $this->module_id));

                            $this->send_status_email($record_id, 'Approved');
                    	}else{
	                        switch( $approver->condition ){
	                            case 1: //by level
	                                //get next approver
	                                $next_approver = $this->db->get_where('employee_appraisal_approver', array('sequence' => ($approver->sequence+1), 'record_id' => $record_id, 'module_id' => $this->module_id));
	                                if( $next_approver->num_rows() == 1 ){
	                                    $next_approver = $next_approver->row();
	                                    $this->db->update('employee_appraisal_approver', array('focus' => 1, 'status' => 4), array('sequence' => $next_approver->sequence, 'record_id' => $record_id, 'module_id' => $this->module_id));
	                                    //email next approver
	                                    $this->send_email();

	                                    $data['approver_remarks'] = $approver_name.' - '.$remarks;
	                                    $this->db->where($this->key_field, $record_id);
	                                    $this->db->update($this->module_table, $data);
	                                }
	                                else{
	                                    //this is last approver
	                                    $data['approver_remarks'] = $record->approver_remarks .'<br/>'.  $approver_name.' - '.$remarks;
	                                    $data['idp_status'] = 'Approved';
	                                    $data['date_approved'] = date('Y-m-d H:i:s');
	                                    $this->db->where($this->key_field, $record_id);
	                                    $this->db->update($this->module_table, $data);
	                                    $this->send_status_email($record_id, 'Approved');
	                                }
	                                break;
	                            case 2: // Either
	                            	$data['approver_remarks'] = $remarks;
	                                $data['idp_status'] = 'Approved';
	                                $data['date_approved'] = date('Y-m-d H:i:s');
	                                $this->db->where($this->key_field, $record_id);
	                                $this->db->update($this->module_table, $data);
	                                $this->send_status_email($record_id,'Approved');
	                                break;
	                            case 3: // All
	                                $qry = "SELECT * FROM {$this->db->dbprefix}employee_appraisal_approver where record_id = {$record_id} and status != 4 AND module_id = {$this->module_id}";
	                                $all_approvers = $this->db->query( $qry );
	                                if( $all_approvers->num_rows() == 0 ){
	                                	$data['approver_remarks'] = $remarks;
	                                    $data['idp_status'] = 'Approved';
	                                    $data['date_approved'] = date('Y-m-d H:i:s');
	                                    $this->db->where($this->key_field, $record_id);
	                                    $this->db->update($this->module_table, $data);  
	                                    $this->send_status_email($record_id, 'Approved');
	                                }
	                                break;  
	                        }
	                    }
                        break;
                    case 6:
                        $data['approver_remarks'] = $remarks;
                        $data['date_approved'] = date('Y-m-d H:i:s');
                        $data['idp_status'] = 'Decline';
                        $this->db->where($this->key_field, $record_id);
                        $this->db->update($this->module_table, $data);
                        $this->send_status_email($record_id,'Decline');
                        break;
                    /*case 2:
                        $data['approver_remarks'] = $remarks;
                        $data['date_modified'] = date('Y-m-d');
                        $data['idp_status'] = 2;
                        $this->db->where($this->key_field, $record_id);
                        $this->db->update($this->module_table, $data);
                        $this->send_status_email($record_id,6);
                        break;
                    case 7:
                        $data['approver_remarks'] = $remarks;
                        $data['date_modified'] = date('Y-m-d');
                        $data['idp_status'] = 7;
                        $this->db->where($this->key_field, $record_id);
                        $this->db->update($this->module_table, $data);
                        $this->send_status_email($record_id,7);
                        break;*/
                    }

			}

			$this->load->view('template/ajax', array('json' => $response));

		} else {

			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);

		}
	}

	private function _can_approve($records)
	{
		$record_id = $records['individual_development_plan_id'];
		$employee  = $records['employee_id'];
		$status  = $records['idp_status'];

		if ($status != 'For Approval') {
			return false;
		}

		$this->db->where_in('record_id', $record_id);
		$this->db->where_in('approver', $this->user->user_id);
		$this->db->where('module_id', $this->module_id);
		$approver_user = $this->db->get('employee_appraisal_approver');

		if ($approver_user && $approver_user->num_rows() > 0) {
			$approver = $approver_user->row();
			if ($approver->status == 3) {
				return true;
			}
		}

		if($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['approve'] && ($employee != $this->user->user_id)){
			return true;
		}
	}

	private function _can_decline($records)
	{
		$record_id = $records['individual_development_plan_id'];
		$employee  = $records['employee_id'];
		$status  = $records['idp_status'];

		// if (!in_array($status, array(4,5))) {
		// 	return false;
		// }
		if (!in_array($status, array('For Approval'))) {
			return false;
		}

		$this->db->where_in('record_id', $record_id);
		$this->db->where_in('approver', $this->user->user_id);
		$this->db->where('module_id', $this->module_id);
		$approver_user = $this->db->get('employee_appraisal_approver');

		if ($approver_user && $approver_user->num_rows() > 0) {
			$approver = $approver_user->row();
			if (in_array($approver->status, array(3))) {
				return true;
			}
		}

		if($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['decline'] && ($employee != $this->user->user_id)){
			return true;
		}
	}


	function check_training()
	{
		$employee_id = $this->input->post('employee_id');
		$employee = $this->hdicore->_get_userinfo($employee_id);

		$check_training = $this->db->get_where('training_application', array('employee_id' => $employee_id, 'YEAR(date_from)' => date('Y'), 'deleted' => 0, 'status' => 5));
		$response->msg = "";
		$response->msg_type = "";

		if ($check_training && $check_training->num_rows() > 0) {
        	$response->msg = "IDP application is already approved. Please review first the approved EPAF of ".$employee->firstname." ". $employee->lastname.", before editing this IDP";
            $response->msg_type = "attention";
        }
        
        $data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

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

	function print_record($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		$template = $this->template->get_module_template($this->module_id, 'idp' );
		
		$check_record = $this->_record_exist($record_id);
		$this->load->library('parser');


		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$ids = get_record_detail_array($record_id, true);

			$dropdowns = $this->get_dropdown();
			$records = $this->db->get_where($this->module_table, array($this->key_field => $record_id));

			$vars['ratings'] = '<table style="width:100%" border="1">';
				$vars['ratings'] .= '<tr>';
				foreach ($dropdowns['rating'] as $key => $scale) {
					$vars['ratings'] .= '<th style="background-color:#ccc;" align="center">'.$scale->definition_rating.'</th>';
				}	
				$vars['ratings'] .= '</tr>';
				
				$vars['ratings'] .= '<tr>';
				foreach ($dropdowns['rating'] as $key => $scale) {
					$vars['ratings'] .= '<td align="center">'.$scale->rating.'</td>';
				}
				$vars['ratings'] .= '</tr>';

				$vars['ratings'] .= '<tr>';
				foreach ($dropdowns['rating'] as $key => $scale) {
					$vars['ratings'] .= '<td align="center"><small>'.$scale->criteria_standard.'</small></td>';
				}	
					$vars['ratings'] .= '</tr>';
				
				
			$vars['ratings'] .= "</table>";
			
			$vars['individual'] = "";
	 		if ($records && $records->num_rows() > 0) {
	 			$idp_details = json_decode($records->row()->individual_development_plan, true);
	 			$record = $records->row_array();
	 			$vars['idp_year'] = $record['year'];
	 		}
		  
		    $vars['individual'] =  '<table style="width: 100%;" border="1" cellpadding="10">';
		    $vars['individual'] .=  '<tbody>
		    							<tr>
						                    <th style="background-color:#ccc;width:10%" align="center"><strong>% Distribution</strong></th>
						                    <th style="background-color:#ccc;width:25%" align="center"><strong>Areas for Development / Improvement / Training Need</strong></th>
						                    <th style="background-color:#ccc;width:13%" align="center"><strong>Rating</strong></th>
						                    <th style="background-color:#ccc;width:10%" align="center"><strong>Learning Mode</strong></th>
						                    <th style="background-color:#ccc;width:10%" align="center"><strong>Competency Focus</strong><small> (HR Use)</small> </th>
						                    <th style="background-color:#ccc;width:15%" align="center"><strong>Budget Allocation</strong></th>
						                    <th style="background-color:#ccc;width:17%" align="center"><strong>Remarks</strong></th>
						                </tr>';
				foreach ($idp_details['percent_distribution'] as $idp_key => $percent_distribution):
					$ratings 			= $this->db->get_where('appraisal_core_value_rating', array('deleted' => 0, 'rating' => $idp_details['rating'][$idp_key]))->row();
					$learnings 			= $this->db->get_where('appraisal_learning_mode', array('deleted' => 0, 'learning_mode_id' => $idp_details['learning_mode'][$idp_key]))->row();
					$competencies 		= $this->db->get_where('training_category', array('deleted' => 0, 'training_category_id' => $idp_details['competencies'][$idp_key]))->row();
					$budget_allocation 	= $this->db->get_where('training_type', array('deleted' => 0, 'training_type_id' => $idp_details['budget_allocation'][$idp_key]))->row();

	                $vars['individual'] .= '<tr>';
						$vars['individual'] .= '<td style="width:10%">'.$percent_distribution.'</td>
												<td style="width:25%">'.$idp_details['areas_development'][$idp_key].'</td>';
						$vars['individual'] .= '<td style="width:13%">'.$ratings->rating .' - '. $ratings->definition_rating.'</td>';
						$vars['individual'] .= '<td style="width:10%">'.$learnings->learning_mode.'</td>';	                
		                $vars['individual'] .= '<td style="width:10%">'.$competencies->training_category.'</td>';
		                $vars['individual'] .= '<td style="width:15%">'.$budget_allocation->training_type.'-'. $budget_allocation->training_type_code.'</td>';
						$vars['individual'] .= '<td style="width:17%">'.$idp_details['remarks'][$idp_key].'</td>';
					$vars['individual'] .= '</tr> ';			
                   
                endforeach;		                
		    $vars['individual'] .=  '</table>';

		    $this->db->where('record_id', $record_id);
			// $this->db->where('approver', $this->user->user_id);
			$this->db->where('module_id', $this->module_id);
			$this->db->join('user', 'user.employee_id=employee_appraisal_approver.approver', 'left');
			$this->db->join('user_position', 'user_position.position_id=user.position_id', 'left');
			$approvers = $this->db->get('employee_appraisal_approver');
			$vars['approvers'] = "<p></p>";
			if ($approvers && $approvers->num_rows() > 0)
			{
				$vars['approvers'] .= '<table cellpadding="10" cellspacing="20" style="width:100%;" >';
										
					$vars['approvers'] .= '<tr>';
					$vars['approvers'] .= '<td><strong>Employee: </strong></td>';
					foreach ($approvers->result() as $key => $approver)
					{
						$vars['approvers'] .= '<td><strong> Approved By: </strong></td>';
					}				
					$vars['approvers'] .= '</tr>';
					$vars['approvers'] .= '<tr><td colspan="'.($approvers->num_rows() + 1).'" ></td></tr>';
					$vars['approvers'] .= '<tr>';
					$vars['approvers'] .= '<td> <span><b>'.$vars['employee_id'].'</b></span> <br> '.$vars['position_id'].'</td>';
					foreach ($approvers->result() as $key => $approver)
					{
						$vars['approvers'] .= '<td> <span><b>'.$approver->firstname. ' '.$approver->lastname.'</b></span> <br> '.$approver->position.'</td>';
					}				
					$vars['approvers'] .= '</tr>';
				$vars['approvers'] .= '</table>';
			}			
			
			$logo_2 = get_branding();
			$company_id = $ids['company_id'];
			$company_qry = $this->db->get_where('user_company', array('company_id' => $company_id))->row();
			if(!empty($company_qry->logo)) {
			  $logo_2 = '<img alt="" src="./'.$company_qry->logo.'">';
			}
			$vars['logo_2'] =  $logo_2;
			
			$this->db->join('user', 'user.user_id=user_company_division.division_manager_id', 'left');
			$this->db->where('user_company_division.division_id',$ids['division_id']);
			$this->db->where('user_company_division.deleted',0);
			$division = $this->db->get('user_company_division');

			if ($division && $division->num_rows() > 0){
				$vars['division_head'] =  $division->row()->salutation.' '.$division->row()->firstname.' '.$division->row()->lastname;
			}else{
				$vars['division_head'] = '';
			}

			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.			
			// $this->pdf->setPrintHeader(TRUE);
			// $this->pdf->SetAutoPageBreak(true, 25.4);
			$this->pdf->SetMargins( 7,7 );
			$this->pdf->addPage('L', 'LETTER', true);				
			$this->pdf->SetFontSize(8);	
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	

	// END custom module funtions

}
/* End of file */
/* Location: system/application */