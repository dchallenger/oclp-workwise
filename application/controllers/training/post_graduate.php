<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Post_graduate extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Budget';
		$this->listview_description = 'This module lists all defined training budget(s).';
		$this->jqgrid_title = "Training Budget List";
		$this->detailview_title = 'Training Budget Info';
		$this->detailview_description = 'This page shows detailed information about a particular training budget.';
		$this->editview_title = 'Training Budget Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training budget(s).';
		$this->has_sub = false;
		$this->filter = $this->db->dbprefix.$this->module_table.'.training_application_type = 2 ';

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
				$this->filter .= ' AND ' . $this->db->dbprefix.$this->module_table.'.employee_id IN ('.$subordinate_list.')';
			}
			else{
				$this->filter .= ' AND ' . $this->db->dbprefix.$this->module_table.'.employee_id = ' . $this->userinfo['user_id'];
			}
	
		}

		if( $this->input->post('filter')){
			switch ($this->input->post('filter')) {
				case 'hr_validation':
					$this->filter .= ' AND ' .$this->db->dbprefix.$this->module_table.".status = 3";
					break;
				case 'for_approval':
					$this->filter .= ' AND ' .$this->db->dbprefix.$this->module_table.".status = 4";
					break;
				case 'approved':
					$this->filter .= ' AND ' .$this->db->dbprefix.$this->module_table.".status = 5";
					break;
				case 'disapproved':
					$this->filter .= ' AND ' .$this->db->dbprefix.$this->module_table.".status = 6";
					break;
				case 'invalid':
					$this->filter .= ' AND ' .$this->db->dbprefix.$this->module_table.".status = 2";
					break;
				case 'cancelled':
					$this->filter .= ' AND ' .$this->db->dbprefix.$this->module_table.".status = 7";
					break;
			}
    		
    	}
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';
		$data['jqgrid'] = 'training/application/jqgrid';

		if ($this->user_access[$this->module_id]['post'] || $this->has_sub) {
	    	$tabs[] = '<li filter="all" class="active"><a href="javascript:void(0)">All</li>';
	    	$tabs[] = '<li filter="hr_validation"><a href="javascript:void(0)">HR Validation</li>';
	    	$tabs[] = '<li filter="for_approval"><a href="javascript:void(0)">For Approval</li>';
	    	$tabs[] = '<li filter="approved"><a href="javascript:void(0)">Approved</li>';
	    	$tabs[] = '<li filter="disapproved"><a href="javascript:void(0)">Disapproved</li>';
	    	$tabs[] = '<li filter="invalid"><a href="javascript:void(0)">Invalid</li>';
	    	$tabs[] = '<li filter="cancelled"><a href="javascript:void(0)">Cancelled</li>';
	    	
	    	if( sizeof( $tabs ) > 1 ) $data['epaf_tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
    	}

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		//set default columnlist
		$this->_set_listview_query();
		
		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();
		
		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "init_filter_tabs();";
		
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
	
	function filter($to_filter=0)
    {
    	$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'listview';
		$data['jqgrid'] = 'training/application/jqgrid';
		$data['to_filter'] = $to_filter;

		if ($this->user_access[$this->module_id]['post'] || $this->has_sub) {
	    	$tabs[] = '<li filter="all" class="active"><a href="javascript:void(0)">All</li>';
	    	$tabs[] = '<li filter="hr_validation" id="hr_validation"><a href="javascript:void(0)">HR Validation</li>';
	    	$tabs[] = '<li filter="for_approval" id="for_approval"><a href="javascript:void(0)">For Approval</li>';
	    	$tabs[] = '<li filter="approved" id="approved"><a href="javascript:void(0)">Approved</li>';
	    	$tabs[] = '<li filter="disapproved"><a href="javascript:void(0)">Disapproved</li>';
	    	$tabs[] = '<li filter="invalid"><a href="javascript:void(0)">Invalid</li>';
	    	$tabs[] = '<li filter="cancelled"><a href="javascript:void(0)">Cancelled</li>';
	    	
	    	if( sizeof( $tabs ) > 1 ) $data['epaf_tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
    	}
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		//set default columnlist
		$this->_set_listview_query();
		
		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();
		
		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = "init_filter_tabs();";
		
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
	
		$data['show_wizard_control'] = true;
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form-custom.js"></script>';

		$data['content'] = 'training/application/compactview';

		$rating = $this->db->get_where('training_rating_scale', array('deleted' => 0));
		$transfer = $this->db->get_where('training_knowledge_transfer', array('deleted' => 0));
		$service_bond = $this->db->get_where('training_service_bond', array('deleted' => 0));
		
		$data['ratings'] = ($rating && $rating->num_rows() > 0) ? $rating->result() : array();
		$data['transfers'] = ($transfer && $transfer->num_rows() > 0) ? $transfer->result() : array();
		$data['service_bond'] = ($service_bond && $service_bond->num_rows() > 0) ? $service_bond->result() : array();

		$records = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));
 		if ($records && $records->num_rows() > 0) {
 			$data['records'] = $records->row();
 			$record = $records->row_array();
 		}

 		$with_live = false;
 		if (strtotime($record['date_from']) <= strtotime(date('Y-m-d')) ) {
					$with_live = true;
			}

 		$data['with_live'] 	 = $with_live;
 		$data['can_approve'] = $this->_can_approve($record);
		$data['can_decline'] = $this->_can_decline($record);
	
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
			// if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
			$data['show_wizard_control'] = true;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form-custom.js"></script>';
			// }
			$data['content'] = 'training/application/editview';
			$data['buttons'] = 'training/application/edit-buttons';
			
			$rating = $this->db->get_where('training_rating_scale', array('deleted' => 0));
			$transfer = $this->db->get_where('training_knowledge_transfer', array('deleted' => 0));
			$service_bond = $this->db->get_where('training_service_bond', array('deleted' => 0));

			$data['ratings'] = ($rating && $rating->num_rows() > 0) ? $rating->result() : array();
			$data['transfers'] = ($transfer && $transfer->num_rows() > 0) ? $transfer->result() : array();
			$data['service_bond'] = ($service_bond && $service_bond->num_rows() > 0) ? $service_bond->result() : array();

			$records = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));
     		if ($records && $records->num_rows() > 0) {
     			$data['records'] = $records->row();
     		}


			//other views to load
			$data['views'] = array();
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
							elseif ($detail['name'] == 't3training_application_status'){
								$this->db->where('training_application_id', $row['training_application_id']);
								// $this->db->where('approver', $this->user->user_id);
								$this->db->where('module_id', $this->module_id);
								$this->db->join('user', 'user.employee_id=training_approver.approver', 'left');
								$approvers = $this->db->get('training_approver');
								
								$status = $row[$detail['name']];
								$cell[$cell_ctr] = $status;
								if ($approvers && $approvers->num_rows() > 0) {

									foreach($approvers->result() as $approver){
										$add_status = false;
										switch( $row['status'] ){
											
											case '4':
												
												if($approver->focus == 0) {
													$status = "Waiting...";
													$add_status = true;
													$class = 'orange';
												}
												elseif($approver->focus == '1' && $approver->status == '5'){
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
											case '6':
													
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

		if( empty($approvers_per_position) ){
            $response->msg = "Please contact HR Admin. Approver has not been set.";
            $response->msg_type = "error";
            $response->page_refresh = "true";
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
        }

        $employee = $this->system->get_employee($employee_id);

        if ($this->user_access[$this->module_id]['post'] && ($employee_id != $this->userinfo['user_id'])) {
			$check_records = $this->db->get_where($this->module_table, array('employee_id' => $employee_id, 'status' => 4, 'deleted' => 0));
		
			if( $check_records && $check_records->num_rows > 0  ){
	            $response->msg = $employee['firstname'].' '.$employee['lastname']. " has a pending Training Application for approval.";
	            $response->msg_type = "error";
	            $response->page_refresh = "true";
	            $data['json'] = $response;
	            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	            return;
	        }
    	}

		if ($this->input->post('objective')) {
			$objectives = json_encode($this->input->post('objective'));
			$details['training_objectives'] = $objectives;
		}

		if ($this->input->post('action')) {
			$action = json_encode($this->input->post('action'));
			$details['action_plan'] 		= $action ;
		}

		if ($this->input->post('transfer')) {
			$transfer = json_encode($this->input->post('transfer'));
			$details['knowledge_transfer'] 	= $transfer;
		}

		parent::ajax_save();
		
		$details['training_application_type'] = 2; // PGSA
		if ($this->input->post('record_id') == '-1') {
			$details['status'] = 1;
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $details);	

			$approvers_per_position = $this->system->get_approvers_and_condition($employee_id, $this->module_id);

			foreach( $approvers_per_position as $approver ){
				$approver_sequence = $approver['sequence'];
				$approver_id = $approver['approver'];
			
				$approver['training_application_id'] = $this->key_field_val;
				if ($this->user_access[$this->module_id]['post'] && $employee_id != $this->userinfo['user_id']) {
					$approver['status'] = '4';
				}

				$approver['module_id'] = $this->module_id;
				$this->db->insert('training_approver', $approver);

			}
			$year = date('Y', strtotime($this->input->post('date_from')));

			$balance = $this->db->get_where('training_balance', array('employee_id' => $employee_id, 'year' => $year));
			// $balance = $this->db->get_where('training_balance', array('employee_id' => $employee_id, 'year' => $year, 'training_application_type_id' => 2));

			
			$budget['employee_id'] 					= $employee_id;
			// $budget['training_application_type_id'] = 2;
			$budget['year'] 						= $year;
			$budget['itb']							= $this->input->post('itb');
			$budget['ctb']							= $this->input->post('ctb');
			$budget['stb']							= $this->input->post('stb');
			$budget['remaining_itb']				= $this->input->post('remaining_itb');
			$budget['remaining_ctb']				= $this->input->post('remaining_ctb');
			$budget['remaining_stb']				= $this->input->post('remaining_stb');
			$budget['allocated'] 					= $this->input->post('allocated');
			
			if ($balance && $balance->num_rows() > 0) {
				
				// if ($this->user_access[$this->module_id]['post'] && $employee_id != $this->userinfo['user_id']){
				// 	$this->db->where('training_application_id',$balance->row()->training_application_id);
				// 	$this->db->update('training_balance', $budget); 
				// }		

			}else{
				$this->db->insert('training_balance', $budget);
			}


		}else{
			$details['date_modified'] = date('Y-m-d H:i:s');

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $details);
		}
		
		//additional module save routine here
				
	}

	function send_email() {
		if (IS_AJAX) {

			$record_id = $this->input->post('record_id');
			$this->db->join('training_application_type', 'training_application_type.training_application_type_id = training_application.training_application_type');
			$records = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row_array();

     		$employee = $this->db->get_where('user',array("user_id"=>$records['employee_id']))->row_array();
			$vars['employee'] =  $employee['salutation'] .' '. $employee['firstname']. ' ' . $employee['lastname'];
			$vars['training_application_type'] = $records['training_application_type'];
			$vars['training_code'] = $records['training_application_code'];
			$vars['requested_date'] = date('F d, Y' , strtotime($records['date_requested']));

			$status = 1;
			
			if (($records['status'] == 0 || $records['status'] == 1) && $records['employee_id'] == $this->userinfo['user_id'])
			{
				$this->send_hr_review($records);
				return;

			}
			elseif($records['status'] == 3){
				$template_code = 'tad_for_approval_dept_head';
				$status = 4;
			
			}

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				$this->db->where_in('training_application_id', $record_id);
				$this->db->where('status !=', 5);
				$this->db->where('module_id', $this->module_id);
				$approvers = $this->db->get('training_approver');

				if ($approvers && $approvers->num_rows() == 1) {
					$template_code = 'tad_for_approval_div_head';
				}	

				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template(0, $template_code);

				$this->db->where_in('training_application_id', $record_id);
				$this->db->where('focus', 1);
				$this->db->where('module_id', $this->module_id);
                $this->db->order_by('sequence', 'desc');
				$approver_user = $this->db->get('training_approver'); //->result();
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
							$emailed = $this->template->queue(implode(',', $recepients), '', $template['subject'], $message);                   
	                    }
					}
					// If queued successfully set the status to For Approval.
					if (true) {

						$this->db->where_in('approver', $app_array);
	                    $this->db->where(array('training_application_id' => $record_id));   
	                    $this->db->where('module_id', $this->module_id);
	                    $this->db->update('training_approver', array('status' => '4'));

						$this->db->where($this->key_field, $record_id);
						$this->db->update($this->module_table, array('status' => '4', 'date_modified' => date('Y-m-d')));

						$response->record_id  = $record_id;
						$response->msg_type = 'success';
						$response->msg = 'PGSA Request Sent.';

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
			$record_id = $records['training_application_id'];
			$employee = $this->db->get_where('user',array("user_id"=>$records['employee_id']))->row_array();
			$vars['employee'] =  $employee['salutation'] .' '. $employee['firstname']. ' ' . $employee['lastname'];
			$vars['training_application_type'] = $records['training_application_type'];
			$vars['training_code'] = $records['training_application_code'];
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
				$template = $this->template->get_module_template(0, 'tad_review');
				
				// $this->db->where_in('training_application_id', $record_id);
				// $approver_user = $this->db->get('training_approver');
				
				for ($i=0; $i < count($hr['email']); $i++) { 
					$recepients = $hr['email'][$i];
					$vars['hr_name'] = $hr['hr_name'][$i];

					$message = $this->template->prep_message($template['body'], $vars);
					$this->template->queue($recepients, '', $template['subject'], $message);

				}

                    $this->db->where($this->key_field, $record_id);
					$this->db->update($this->module_table, array('status' => '3', 'date_modified' => date('Y-m-d'), 'date_requested' => date('Y-m-d')));

					$response->msg_type = 'success';
					$response->msg = 'PGSA Request Sent.';

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

			$this->db->join('training_application_type', 'training_application_type.training_application_type_id = training_application.training_application_type');
			$records = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row_array();
     		
     		$employee = $this->db->get_where('user',array("user_id"=>$records['employee_id']))->row_array();
			$vars['employee'] =  $employee['salutation'] .' '. $employee['firstname']. ' ' . $employee['lastname'];
			$vars['training_application_type'] = $records['training_application_type'];
			$vars['training_code'] = $records['training_application_code'];
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

			$year = date('Y', strtotime($records['date_from']));

			$this->db->where('employee_id',$records['employee_id']);
            $this->db->where('year',$year);
            // $this->db->where('training_application_type_id',1);
            $training_balance =  $this->db->get('training_balance');
            $with_balance = false;
            if ($training_balance && $training_balance->num_rows() > 0) {
            	$balance_table = $training_balance->row();
            	$balance_id = $balance_table->training_balance_id;
            	$with_balance = true;
            }

			switch ($status) {
				case 5:
					$template_code = 'tad_approved';
					$msg = 'Approved';

					$bal = $records['stb'] - $records['investment'];
					$training_balance_cost = ($bal > 0) ? $bal : 0 ;

					$data = array(
								'training_application_id' => $records['training_application_id'],
								'training_application_type_id' => 2,
								'employee_id' => $records['employee_id'],
								'position_id' => $records['position_id'],
								'division_id' => $records['division_id'],
								'department_id' => $records['department_id'],
								'rank_id' => $records['rank_id'],
								'investment' => $records['investment'],
								'course' => $records['post_grad_course'],
								'training_date' => $records['date_from'],
								'training_objectives' => $records['training_objectives'],
								'knowledge_transfer' => $records['knowledge_transfer']
							);

							$this->db->insert('training_live',$data);

					$database = array(
								'training_application_id' => $records['training_application_id'],
								'employee_id' => $records['employee_id'],
								'position_id' => $records['position_id'],
								'division_id' => $records['division_id'],
								'department_id' => $records['department_id'],
								'rank_id' => $records['rank_id'],
								'start_date' => $records['date_from'],
								'end_date' => $records['date_to'],
								'training_balance' => $training_balance_cost,
								'service_bond' => $records['service_bond'],
								'investment' => $records['investment'],
								'stb' => $records['stb'],
								'remaining_stb' => $records['remaining_stb'],
								'excess_stb' => $records['excess_stb'],
								'itb' => $records['itb'],
								'remaining_itb' => $records['remaining_itb'],
								'excess_itb' => $records['excess_itb'],
								'ctb' => $records['ctb'], 
								'remaining_ctb' => $records['remaining_ctb'],
								'excess_ctb' => $records['excess_ctb'],
								'budgeted' => $records['budgeted'],
								'allocated' => $records['allocated'],
								'remaining_allocated' => $records['remaining_allocated'],
								'remarks' => $records['remarks'],
								'training_course' => $records['post_grad_course'],
								'idp_completion' => $records['idp_completion'],
							);

					$this->db->insert('training_employee_database',$database);


					$update['remaining_itb'] = $records['remaining_itb'];
     				$update['remaining_ctb'] = $records['remaining_ctb'];
     				$update['remaining_stb'] = $records['remaining_stb'];
     				$update['stb'] = $records['stb'];
     				$update['excess_itb'] = $records['excess_itb'];
     				$update['excess_ctb'] = $records['excess_ctb'];
					$update['excess_stb'] = $records['excess_stb'];
                  	
                  	
                    $this->db->where('training_balance_id',$balance_id);
					$this->db->update('training_balance', $update);

					$msg = 'Approved';
					break;
				case 6:
					$template_code = 'tad_disapproved';
					$msg = 'Disapproved';
					break;
				case 7:
					$template_code = 'tad_disapproved';
					
					$investment = $records['investment'];
					$allocated = $records['allocated'];

					if ($records['budgeted'] != 1) {
						$excess = $records['excess_stb']; 

						if ($excess != $investment) {
							$balance = ($investment - $excess) + $balance_table->remaining_stb; 
							$update['remaining_stb'] = $balance;
						}
						
						if ($allocated == 1) {
							$reallocate = $balance_table->remaining_itb + $excess;
							$update['remaining_itb'] = $reallocate;
						}elseif($allocated == 2){
							$reallocate = $balance_table->remaining_ctb + $excess;
							$update['remaining_ctb'] = $reallocate;							
						}					
						
						$this->db->where('training_balance_id',$balance_id);
						$this->db->update('training_balance', $update);
					}

					$this->db->where($this->key_field, $record_id);
					$this->db->update('training_live', array('deleted' => 1));

					$this->db->where($this->key_field, $record_id);
					$this->db->update('training_employee_database', array('deleted' => 1)); 

					$msg = 'Cancelled';
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
					$response->msg = 'PGSA '. $msg;

				}
			
			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		
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
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
        	if ($this->user_access[$this->module_id]['post']) {
            	$buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        	}
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
        
        $with_live = false;

 		if (strtotime($record['date_from']) <= strtotime(date('Y-m-d')) ) {
			$with_live = true;
		}


        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] ) {
			if ($record['status'] == 1 && $record['employee_id'] == $this->userinfo['user_id']) {
				$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
			}elseif($this->user_access[$this->module_id]['post'] == 1 && !(in_array($record['status'] , array(5,2,6,7))) ){
				 $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
			}
           
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }  

        if ($record['status'] == 1){
        	if ($this->user_access[$this->module_id]['delete']) {
	            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
	        }
        }

        if ($record['status'] == 4 && ($record['employee_id'] != $this->userinfo['user_id'])) {
        	
        	if ($this->_can_approve($record)) {
        		$actions .= '<a class="icon-button icon-16-approve approve-single"  record_id="'.$record['training_application_id'].'" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}

        	if ($this->_can_decline($record)) {
        		$tooltip = 'Disapprove';
				$actions .= '<a class="icon-button icon-16-disapprove cancel-single" record_id="'.$record['training_application_id'].'" tooltip="' . $tooltip . '" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			}
				
        }

        if (!$with_live) {
   			
	       	if ($record['status'] == 5 && ($record['employee_id'] != $this->userinfo['user_id']) && $this->user_access[$this->module_id]['post']) {
	        	
	        	// if ($this->_can_decline($record)) {
	        		$tooltip = 'Cancel';
					$actions .= '<a class="icon-button icon-16-cancel cancel-application" record_id="'.$record['training_application_id'].'" tooltip="' . $tooltip . '" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
				// }
					
	        } 
    	}

        $actions .= '</span>';

		return $actions;
	}

	function _append_to_select()
	{
		//$this->listview_qry .= ', employee_appraisal.employee_id, user.position_id';
		$this->listview_qry .= ', status, date_from'; 

	}
	
	function _set_left_join() 
	{
		$this->db->join('user', 'training_application.employee_id = user.employee_id', 'left');
		parent::_set_left_join();
	}	

	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{
			$column = strtolower( $search['column'] );
			if(sizeof(explode(' as ', $column)) > 1){
				$as_part = explode(' as ', $column);
				$search['column'] = strtolower( trim( $as_part[0] ) );
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;

		}
		$search_string[] = $this->db->dbprefix .'user.firstname LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';

		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}
	function get_employee_details()
	{
		if (IS_AJAX) {
			$current_date = date('Y-m-d');
			$employee_id = $this->input->post('employee_id');
			$this->db->select('user.division_id, department_id, position_id, rank_id, firstname, lastname, employed_date');
			$this->db->where('user.employee_id', $employee_id);
			$this->db->join('employee', 'employee.employee_id=user.employee_id');
			$employee = $this->db->get('user')->row_array();
			// $employee = $this->system->get_employee($employee_id);
		
			$year  = date('Y') - date('Y' , strtotime($employee['employed_date']));
			$month = date('m') - date('m', strtotime($employee['employed_date']));
			$tenure = ( ( $year * 12 ) + $month ) / 12;

        	$response->tenure = number_format($tenure, 2, '.', ',');

			$response->department = $employee['department_id'];
			$response->division = $employee['division_id'];
			$response->rank = $employee['rank_id'];
			$response->position = $employee['position_id'];
			$response->name = $employee['firstname'] .' '. $employee['lastname'] ;

			/*$response->allocate = "<option value=''>Select…</option>
								   <option value='itb'>Individual Training Budget </option>
								   <option value='ctb'>Common Training Budget </option>
								   <option value='stb'>Supplemental Training Budget </option>";*/
			
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
		

	}

	function get_budget()
	{
		if (IS_AJAX) {
			$rank_id  = $this->input->post('rank_id');
			$employee = $this->input->post('employee_id');
			// $course   = $this->input->post('course');
			$date     = $this->input->post('date');

			$this->db->where('FIND_IN_SET('.$rank_id.', rank)');
			// $this->db->where('training_type_id', $type);
			$this->db->where('deleted', 0);
			$training_budget = $this->db->get('training_budget');
			
			$year = date('Y', strtotime($date));

			$this->db->where('employee_id',$employee);
            $this->db->where('year',$year);
            // $this->db->where('training_application_type_id',2);
            $training_balance =  $this->db->get('training_balance');

            $with_balance = false;
            if ($training_balance && $training_balance->num_rows() > 0) {
            	$balance_table = $training_balance->row();
            	$balance_id = $balance_table->training_balance_id;
            	$with_balance = true;
            }	
            
			/*$this->db->where('training_subject_id', $course);
			$this->db->where('deleted', 0);
			$training_course_budget = $this->db->get('training_subject');
			
			$investment = 0;
			if ($training_course_budget && $training_course_budget->num_rows() > 0) {
				$course_budget = $training_course_budget->row();

				$investment = $course_budget->cost;
			}
*/
			$amount = 0; 
			$amount_it = 0;
			$amount_ct = 0;
			if ($training_budget && $training_budget->num_rows() > 0) {
				foreach ($training_budget->result() as $value) {
					switch ($value->training_type_id) {
						case 1:
							$amount_it = $value->budget_amount;	
							$remaining_it = $value->budget_amount;	
							break;
						case 2:
							$amount_ct = $value->budget_amount;
							$remaining_ct = $value->budget_amount;	
							break;
						default:
							$amount_other = $value->budget_amount;
							$remaining_other = $value->budget_amount;
							break;
					}
					
				}
			}

			if ($with_balance) {
				$amount_other = $balance_table->stb;

				$temp_itb = ($balance_table->itb - $balance_table->remaining_itb);
				$it_remain = ($amount_it - $temp_itb);

				$temp_ctb = ($balance_table->ctb - $balance_table->remaining_ctb);
				$ct_remain = ($amount_ct - $temp_ctb);
				
				$temp_stb = ($balance_table->stb - $balance_table->remaining_stb);
				$st_remain = ($amount_other - $temp_stb);

				$remaining_it = $it_remain;
				$remaining_ct = $ct_remain;
				$remaining_other = $st_remain;

				$excess_itb = $balance_table->excess_itb;
				$excess_ctb = $balance_table->excess_ctb;
				$excess_ctb = $balance_table->excess_ctb;
			}

			$response->budget_it = $amount_it;
			$response->remaining_it = $remaining_it;
			$response->budget_ct = $amount_ct;
			$response->remaining_ct = $remaining_ct;
			$response->budget_others = $amount_other;
			$response->remaining_others = $remaining_other;
			$response->investment = $investment;

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_form()
	{
		if (IS_AJAX) {
			$type = $this->input->post('type');
			$rating = $this->db->get_where('training_rating_scale', array('deleted' => 0));
			$transfer = $this->db->get_where('training_knowledge_transfer', array('deleted' => 0));
			
			$data['ratings'] = ($rating && $rating->num_rows() > 0) ? $rating->result() : array();
			$data['transfers'] = ($transfer && $transfer->num_rows() > 0) ? $transfer->result() : array();

			$response = $this->load->view($this->userinfo['rtheme'] . '/training/application/'.$type.'_form', $data);

			$data['html'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
		

	}


	function change_status() {

		if (IS_AJAX) {

			$record_id = $this->input->post('record_id');
			$status  = $this->input->post('status');
			$remarks  = $this->input->post('remarks_approver');

			$approver = $this->db->get_where('training_approver', array('approver' => $this->user->user_id, 'training_application_id' => $record_id, 'module_id' => $this->module_id))->row();
			
			$this->db->where($this->key_field, $record_id);
			$record = $this->db->get($this->module_table)->row();	

			if (!isset($record_id) && $record_id <= 0) {
				$response->msg      = 'No record specified.';
				$response->msg_type = 'error';				
			}
			else {
				$this->db->update('training_approver', array('status' => $status), array('approver' => $this->user->user_id, 'training_application_id' => $record_id, 'module_id' => $this->module_id));
				$approver_details = $this->system->get_employee($this->user->user_id);
				$approver_name = $approver_details['firstname'].' '.$approver_details['lastname'];
				 switch( $status ){
                    case 5:
                    	if ($this->user_access[$this->module_id]['post']) { // hr
                    		$data['approver_remarks'] = $remarks;
                    		$data['status'] = 5;
                            $data['date_approved'] = date('Y-m-d H:i:s');
                            $this->db->where($this->key_field, $record_id);
                            $this->db->update($this->module_table, $data);

                            $this->db->update('training_approver', array('status' => 5), array('training_application_id' => $record_id, 'module_id' => $this->module_id));

                            $this->send_status_email($record_id,5);
                    	}else{
	                        switch( $approver->condition ){
	                            case 1: //by level
	                                //get next approver
	                                $next_approver = $this->db->get_where('training_approver', array('sequence' => ($approver->sequence+1), 'training_application_id' => $record_id, 'module_id' => $this->module_id));
	                                if( $next_approver->num_rows() == 1 ){
	                                    $next_approver = $next_approver->row();
	                                    $this->db->update('training_approver', array('focus' => 1, 'status' => 4), array('sequence' => $next_approver->sequence, 'training_application_id' => $record_id, 'module_id' => $this->module_id));
	                                    //email next approver
	                                    $this->send_email();

	                                    $data['approver_remarks'] = $approver_name.' - '.$remarks;
	                                    $this->db->where($this->key_field, $record_id);
	                                    $this->db->update($this->module_table, $data);
	                                }
	                                else{
	                                    //this is last approver
	                                    $data['approver_remarks'] = $record->approver_remarks .'<br/>'.  $approver_name.' - '.$remarks;
	                                    $data['status'] = 5;
	                                    $data['date_approved'] = date('Y-m-d H:i:s');
	                                    $this->db->where($this->key_field, $record_id);
	                                    $this->db->update($this->module_table, $data);
	                                    $this->send_status_email($record_id, 5);
	                                }
	                                break;
	                            case 2: // Either
	                            	$data['approver_remarks'] = $remarks;
	                                $data['status'] = 5;
	                                $data['date_approved'] = date('Y-m-d H:i:s');
	                                $this->db->where($this->key_field, $record_id);
	                                $this->db->update($this->module_table, $data);
	                                $this->send_status_email($record_id,5);
	                                break;
	                            case 3: // All
	                                $qry = "SELECT * FROM {$this->db->dbprefix}training_approver where record_id = {$record_id} and status != 5 and module_id = {$this->module_id}";
	                                $all_approvers = $this->db->query( $qry );
	                                if( $all_approvers->num_rows() == 0 ){
	                                	$data['approver_remarks'] = $remarks;
	                                    $data['status'] = 5;
	                                    $data['date_approved'] = date('Y-m-d H:i:s');
	                                    $this->db->where($this->key_field, $record_id);
	                                    $this->db->update($this->module_table, $data);  
	                                    $this->send_status_email($record_id,5);
	                                }
	                                break;  
	                        }
	                    }
                        break;
                    case 6:
                        $data['approver_remarks'] = $remarks;
                        $data['date_approved'] = date('Y-m-d H:i:s');
                        $data['status'] = 6;
                        $this->db->where($this->key_field, $record_id);
                        $this->db->update($this->module_table, $data);
                        $this->send_status_email($record_id,6);
                        break;
                    case 2:
                        $data['approver_remarks'] = $remarks;
                        $data['date_modified'] = date('Y-m-d');
                        $data['status'] = 2;
                        $this->db->where($this->key_field, $record_id);
                        $this->db->update($this->module_table, $data);
                        $this->send_status_email($record_id,6);
                        break;
                    case 7:
                        $data['approver_remarks'] = $remarks;
                        $data['date_modified'] = date('Y-m-d');
                        $data['status'] = 7;
                        $this->db->where($this->key_field, $record_id);
                        $this->db->update($this->module_table, $data);
                        $this->send_status_email($record_id,7);
                        break;
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
		$record_id = $records['training_application_id'];
		$employee  = $records['employee_id'];
		$status  = $records['status'];

		if ($status != 4) {
			return false;
		}

		$this->db->where_in('training_application_id', $record_id);
		$this->db->where_in('approver', $this->user->user_id);
		$this->db->where('module_id', $this->module_id);
		$approver_user = $this->db->get('training_approver');

		if ($approver_user && $approver_user->num_rows() > 0) {
			$approver = $approver_user->row();
			if ($approver->status == 4) {
				return true;
			}
		}

		if($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['approve'] && ($employee != $this->user->user_id)){
			return true;
		}
	}

	private function _can_decline($records)
	{
		$record_id = $records['training_application_id'];
		$employee  = $records['employee_id'];
		$status  = $records['status'];

		if ($status != 4) {
			return false;
		}

		$this->db->where_in('training_application_id', $record_id);
		$this->db->where_in('approver', $this->user->user_id);
		$this->db->where('module_id', $this->module_id);
		$approver_user = $this->db->get('training_approver');

		if ($approver_user && $approver_user->num_rows() > 0) {
			$approver = $approver_user->row();
			if ($approver->status == 4) {
				return true;
			}
		}

		if($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['decline'] && ($employee != $this->user->user_id)){
			return true;
		}
	}

	function get_reallocation_from()
	{
		if (IS_AJAX) {
			$type = $this->input->post('type');
			
			$allocate = $this->db->get_where('training_type', array('deleted' => 0, 'training_type_id !=' => $type));
			
			$response = "";
			if ($allocate && $allocate->num_rows() > 0) {
				$response .= "<option value=''>Select..</option>";
				foreach ($allocate->result() as $key => $value) {
					$response .= "<option value='".$value->training_type_id."'>".$value->training_type." Budget </option>";
				}
			}
			$data['html'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
		

	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>