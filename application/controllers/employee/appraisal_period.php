<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal_period extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists all appraisal periods.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an appraisal period.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an appraisal period.';

		if (in_array($this->userinfo['user_id'], array(12,408))) {
			$this->user_access[$this->module_id]['project_hr'] = 1;	
		}

		if ((!$this->user_access[$this->module_id]['publish'] && !$this->user_access[$this->module_id]['post']) && !$this->user_access[$this->module_id]['project_hr'] && !$this->is_superadmin) {
			//
			$get_subordinate_circle = $this->system->get_employee_all_reporting_to($this->userinfo['user_id']);
			$appraisal_module = $this->hdicore->get_module('employee_appraisal');
			$module_id = $appraisal_module->module_id;

			// check if rater/coach
			$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_approver 
					   WHERE module_id = ".$module_id."
					   AND approver_employee_id = ".$this->userinfo['user_id']." 
					   AND deleted = 0");

			$employees = array();
			
			if (!empty($get_subordinate_circle)) {
				$this->filter = "FIND_IN_SET (".$this->userinfo['user_id'].", employee_id)";
				foreach ($get_subordinate_circle as $employee) {
					$this->filter .= " OR FIND_IN_SET (".$employee.", employee_id)";
				}
				
			}else{
				$this->filter = "FIND_IN_SET (".$this->userinfo['user_id'].", employee_id) ";	
			}

			if ($result && $result->num_rows() > 0){
				foreach ($result->result() as $row) {
					if (!in_array($row->employee_id, $this->_subordinate_id)){
						$employees[] = $row->employee_id;
						$this->filter .= " OR FIND_IN_SET (".$row->employee_id.", employee_id)";
					}
				}
				// $this->filter .= " OR employee_id IN (". implode(',', $employees).")";
			}

			/*if ($appraisal_invitation_result && $appraisal_invitation_result->num_rows() > 0) {
				foreach ($appraisal_invitation_result->result() as $contributor) {
					$this->filter .= " OR ". $contributor->employee_appraisal_id . " = t1.planning_period_id" ;
				}
			}*/

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
		if(!empty( $this->filter ) ) $this->db->where( "(". $this->filter .")");

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$result = $this->db->get();
		$response->last_query = $this->db->last_query();
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
			if(!empty( $this->filter ) ) $this->db->where( "(". $this->filter .")");;
			
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
			// $response->last_query = $this->db->last_query();

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
						$bsc = 0;
						if ($this->user_access[$this->module_id]['project_hr'] && !$this->is_superadmin) {
							$bsc = 1; //to show all appraisal period for project hr
							$this->db->where('user.user_id',$this->userinfo['user_id']);
							$this->db->where('appraisal_planning_period_id',$row['planning_period_id']);
							$this->db->join('employee_appraisal_planning','employee_appraisal_planning.employee_id = user.user_id','left');
							$appraisal_bsc1 = $this->db->get('user');
							if ($appraisal_bsc1 && $appraisal_bsc1->num_rows() > 0)
								$bsc = 1;
						}
											
						if ($this->user_access[$this->module_id]['project_hr'] && !$this->is_superadmin) {
							$this->db->where('appraisal_period_id',$row['planning_period_id']);
							//$this->db->where_in('employee_appraisal_status',array(8,5));
							$appraisal_bsc = $this->db->get('employee_appraisal_bsc');

							if (($appraisal_bsc && $appraisal_bsc->num_rows() > 0) || $bsc > 0) {

								$cell = array();
								$cell_ctr = 0;
								$count_appraised = 0;
								$count_conformed = 0;

								$this->db->where('appraisal_period_id',$row['employee_appraisal_period_id']);
								$this->db->where('email_sent_from_appraiser',1);
								$this->db->where('deleted',0);
								$result = $this->db->get('employee_appraisal');
								$count_appraised = $result->num_rows();

								$this->db->where('appraisal_period_id',$row['employee_appraisal_period_id']);
								$this->db->where('email_sent_from_appraisee',1);
								$this->db->where('deleted',0);
								$result = $this->db->get('employee_appraisal');
								$count_conformed = $result->num_rows();

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
											if ($detail['name'] == 't3employee_appraisal_period_status'){	
												if ($row['t3employee_appraisal_period_status'] == "Open"){
													$active = 'icon-16-active';
												}								
												else{
													$active = 'icon-16-xgreen-orb';
												}
												$cell[$cell_ctr] = '<span><a style="background-position: 50% 150%;display: inline-block;height: 19px;width: 30px;" class="'.$active.'" onclick="toggleModuleInactive($(this),'.$row["employee_appraisal_period_id"].')" href="javascript:void(0)" tooltip="Toggle State" original-title=""></a></span>' . (in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']]);
											}
											elseif ($detail['name'] == 'conformed') {
												$cell[$cell_ctr] = '<span>'.$count_conformed.'/'.$count_appraised.'</span>';
											}
											else{
												$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
											}
										}
										$cell_ctr++;
									}
								}
								$response->rows[$ctr]['id'] = $row[$this->key_field];
								$response->rows[$ctr]['cell'] = $cell;
								$ctr++;
							}
							$bsc = 0;
						}
						else {
							$cell = array();
							$cell_ctr = 0;
							$count_appraised = 0;
							$count_conformed = 0;

							$this->db->where('appraisal_period_id',$row['employee_appraisal_period_id']);
							$this->db->where('email_sent_from_appraiser',1);
							$this->db->where('deleted',0);
							$result = $this->db->get('employee_appraisal');
							$count_appraised = $result->num_rows();

							$this->db->where('appraisal_period_id',$row['employee_appraisal_period_id']);
							$this->db->where('email_sent_from_appraisee',1);
							$this->db->where('deleted',0);
							$result = $this->db->get('employee_appraisal');
							$count_conformed = $result->num_rows();

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
										if ($detail['name'] == 't3employee_appraisal_period_status'){	
											if ($row['t3employee_appraisal_period_status'] == "Open"){
												$active = 'icon-16-active';
											}								
											else{
												$active = 'icon-16-xgreen-orb';
											}
											$cell[$cell_ctr] = '<span><a style="background-position: 50% 150%;display: inline-block;height: 19px;width: 30px;" class="'.$active.'" onclick="toggleModuleInactive($(this),'.$row["employee_appraisal_period_id"].')" href="javascript:void(0)" tooltip="Toggle State" original-title=""></a></span>' . (in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']]);
										}
										elseif ($detail['name'] == 'conformed') {
											$cell[$cell_ctr] = '<span>'.$count_conformed.'/'.$count_appraised.'</span>';
										}
										else{
											$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
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
		}
		
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function detail()
	{
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();
		if ($this->key_field_val && $this->key_field_val > 0) {
				$this->db->where($this->key_field, $this->key_field_val);
				$reminder = $this->db->get('appraisal_employee_email_reminder');
				
				if ($reminder && $reminder->num_rows() > 0) {
					$data['reminder'] = $reminder->result();
				}
			}

		$this->db->where('module_id',$this->module_id);
		$this->db->where('deleted',0);
		$template_result = $this->db->get('template');

		$data['template_result'] = $template_result;

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

			//additional module edit routine here\
			$data['show_wizard_control'] = false;			
			$data['scripts'][] = uploadify_script();
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();
			$data['reminder'] = array();

			if ($this->key_field_val && $this->key_field_val > 0) {
				$this->db->where($this->key_field, $this->key_field_val);
				$reminder = $this->db->get('appraisal_employee_email_reminder');
				
				if ($reminder && $reminder->num_rows() > 0) {
					$data['reminder'] = $reminder->result();
				}
			}

			$this->db->where('module_id',$this->module_id);
			$this->db->where('deleted',0);
			$template_result = $this->db->get('template');

			$data['template_result'] = $template_result;

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
		$planning_date = $this->input->post('date');
		$planning = $this->input->post('planning');
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');

		$error_on_date_reminder = false;
		$reminder_date_row = '';

		if($error_on_date_reminder){
			$response->msg_type = 'error';
 			$response->msg 		= 'Reminder Date Error on: '.$reminder_date_row;
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
		}

        //check if there are available leave
        $this->db->where('appraisal_year',$this->input->post('appraisal_year'));
        $this->db->where('planning_period_id',$this->input->post('planning_period_id'));
        $this->db->where('deleted',0);
        $appraisal_period = $this->db->get('employee_appraisal_period');

        if ($this->input->post('record_id') == '-1') {
	        if( $appraisal_period->num_rows > 0  ){
	            $response->msg = "Appraisal period is already created.";
	            $response->msg_type = "error";
	            $data['json'] = $response;
	            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	            return;
	        }
	    }

		parent::ajax_save();

		//$this->_send_email($this->input->post('planning_period_id'), 'appraisal_period_to_appraisee');
		//additional module save routine here

	}

	function after_ajax_save()
	{


		$this->db->where($this->key_field, $this->key_field_val);
		$result = $this->db->get('appraisal_employee_email_reminder');

		if ($result && $result->num_rows() > 0) {
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->delete('appraisal_employee_email_reminder');
		}

		$reminder = $this->input->post('reminder');
		$planning = $this->input->post('planning');
		$attachment = $this->input->post('attachment');
		$template = $this->input->post('template');
		$date_array = $this->input->post('date');
		$email_sent = $this->input->post('email_sent');
		if ($reminder && $reminder != "") {
			foreach ($reminder as $key => $rem) {
				$date = '';
				if(!empty($date_array[$key])) {
					$date = date( 'Y-m-d', strtotime($date_array[$key]));
				}
				$data['appraisal_email_reminder'] = $planning[$key];
				$data['date'] = $date;
				$data['email_sent'] = $email_sent[$key];
				$data['uploaded_file'] = $attachment[$key];
				$data['template_id'] = $template[$key];
				$data['employee_appraisal_period_id'] = $this->key_field_val;
				$this->db->insert('appraisal_employee_email_reminder', $data);
			}	
		}

		parent::after_ajax_save();
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

/*	protected function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			if ($this->input->post('record_id') == '-1') {								
				$update['status'] = 'Open';							

				$this->db->where($this->key_field, $this->key_field_val);
				$this->db->update($this->module_table, $update);
			}
		}

		parent::after_ajax_save();
	}*/

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
        	if ($record['employee_appraisal_period_status'] != 2 ) {
            	$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        	}
        }

		$actions .= '<a class="icon-button icon-16-users" tooltip="View details of this record?" href="' . site_url('employee/appraisal/index/' . $record['planning_period_id']) . '"></a>';

        $actions .= '</span>';

		return $actions;
	}

	protected function _append_to_select() 
	{
		$this->listview_qry .= ',comment,hr_employee_appraisal_period.planning_period_id, employee_appraisal_period_status';
	}


	function toggleonoff(){
		if(IS_AJAX)
		{
			$response->msg = "";

			if ( $this->user_access[$this->module_id]['edit'] ) {
				if( isset($_POST['active']) && $this->input->post('record_id') )
				{
					$active = $this->input->post('active');
					$fieldgroup_id = $this->input->post('record_id');
					$this->db->where($this->key_field, $fieldgroup_id);
					$this->db->update($this->module_table, array('employee_appraisal_period_status_id' => $active));
					
					$response->msg = ( $_POST['active'] == 1 ? 'Appraisal set to open.' : 'Appraisal set to close.');
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

	function add_reminder_form(){

		$this->db->where('module_id',$this->module_id);
		$this->db->where('deleted',0);
		$template_result = $this->db->get('template');

		$data = array(
			'count' => $this->input->post('counter_line'),
			'template_result' => $template_result
		);

		$response->status_form = $this->load->view( $this->userinfo['rtheme'].'/admin/appraisal/reminder_form',$data, true );
		$this->load->view('template/ajax', array('json' => $response));

	}
	
	function get_appraisal_planning_period()
	{
		$record_id = $this->input->post('record_id');
		
		//$this->db->where('period_status',2);
		$this->db->where('deleted',0);
		$periods = $this->db->get('appraisal_planning_period');

		$html = "<option>Select..</option>";

		if ($periods && $periods->num_rows() > 0) {

			if ($record_id != -1) {
				$record = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
				$rec_plan_id = $record->planning_period_id;
			}

			$options = array();

			foreach ($periods->result() as $key => $value) {
				$exist_record = $this->db->get_where($this->module_table, array('planning_period_id' => $value->planning_period_id, 'deleted' => 0));
				
				
				if ($exist_record && $exist_record->num_rows() > 0) {
					// $exist = $exist_record->row();
					// $plan_id = $exist->planning_period_id;
				}else{
				
					if ($value->period_status == 2) {
					
						$options['planning_id'][] = $value->planning_period_id;
						$options['planning'][] = $value->planning_period;
					}
					
				}

				if ($rec_plan_id && ($value->planning_period_id == $rec_plan_id)){

					$html .= '<option value="' . $value->planning_period_id . '" selected="selected"> '.$value->planning_period.'</option>';
				}
			}
			
				foreach ($options['planning_id'] as $key => $option) {
					$html .= '<option value="' . $option . '"> '.$options['planning'][$key].'</option>';
				}
				

		}
		

		
		$response->html = $html;
		$data['json'] = $response;
		$this->load->view('template/ajax', $data);
	}

	function employee_status_change() {
		$appraisal_period = $this->input->post('appraisal_period');

		if( $appraisal_period > 0 ){

			$appraisal_planning_period = $this->db->query(" SELECT * FROM {$this->db->dbprefix}appraisal_planning_period WHERE planning_period_id = '{$appraisal_period}'")->row();
			
			$employment_status_result = $this->db->query(" SELECT * FROM {$this->db->dbprefix}employment_status WHERE active = '1' AND employment_status_id IN ({$appraisal_planning_period->employment_status_id})");
			$employee_company =  $this->db->query(" SELECT * FROM {$this->db->dbprefix}user_company WHERE company_id IN ({$appraisal_planning_period->company_id})");
			
			$status = array();
			$company = array();
			
			if( $employment_status_result->num_rows() > 0 ){

				foreach ($employment_status_result->result() as $key => $value) {
					$status[] = $value->employment_status;
				}

			}

			if ($employee_company && $employee_company->num_rows() > 0) {
				foreach ($employee_company->result() as $_company) {
					$company[] = $_company->company;
				}
			}

			$response['employment_status'] = implode(',', $status);
			$response['employee_company'] = implode(',', $company);

		}
		else{

			$response['employment_status'] = "";
			$response['employee_company'] = "";

		}

		
		$data['json'] = $response;
		$this->load->view('template/ajax', $data);
	}

	function employee_status_change_load() {
		$appraisal_period = $this->input->post('appraisal_period');
		
		$employment_status = $this->db->query(" SELECT * FROM {$this->db->dbprefix}employment_status
													WHERE active = '1' AND employment_status_id = '{$appraisal_period}'")->row();

		$response['employment_status'] = $employment_status->employment_status;
		$data['json'] = $response;
		$this->load->view('template/ajax', $data);
	}

	function _send_email($appraisal_planning_period_id, $template_code)
	{
		$this->load->model('template');

		$template = $this->template->get_module_template(0, $template_code);

		$this->db->where('planning_period_id',$appraisal_planning_period_id);
		$period_info_result = $this->db->get('appraisal_planning_period');

		if ($period_info_result && $period_info_result ->num_rows() > 0) {
			$period_info = $period_info_result->row();
			$appraisee = $period_info->employee_id;
			$appraisee = explode(',', $appraisee);
			foreach ($appraisee as $key => $value) {
				$appraisee_info_result = $this->db->get_where('user',array("user_id"=>$value));
				if ($appraisee_info_result && $appraisee_info_result->num_rows() > 0){
					$appraisee_info = $appraisee_info_result->row_array();
				}

				$request['appraisee'] = $appraisee_info['firstname']." ".$appraisee_info['lastname'];
		        $request['period'] = $period_info->planning_period;
		        $request['year'] = $period_info->year;
				$request['here']=base_url().'appraisal/appraisal_planning/edit/'.$value.'/'.$appraisal_planning_period_id;
				
				$message = $this->template->prep_message($template['body'], $request);
			    $this->template->queue($appraisee_info['email'], '', $template['subject'], $message);
			}
		}

	    $response->msg = 'Appraisal sent';
		$response->msg_tpe = 'success'; 
	}	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */