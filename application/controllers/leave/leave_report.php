<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class leave_report extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Leave Report';
		$this->listview_description = 'This module lists all defined leave report(s).';
		$this->jqgrid_title = "Leave Report List";
		$this->detailview_title = 'Leave Report Info';
		$this->detailview_description = 'This page shows detailed information about a particular leave report.';
		$this->editview_title = 'Leave Report Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about leave report(s).';
		$this->default_sort_col = array('company');
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'leaves/leave_report/listview';

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

		$data['form_status'] = $this->db->get('form_status')->result_array();
		$this->db->where('is_leave = 1');
		$data['form_type'] = $this->db->get('employee_form_type')->result_array();

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
	
	function ajax_save()
	{	
		parent::ajax_save();
		
		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
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

		$search = 1;

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


		if (method_exists($this, '_append_to_select')) {
			// Append fields to the SELECT statement via $this->listview_qry
			$this->_append_to_select();
		}

		if (method_exists($this, '_custom_join')) {
			$this->_custom_join();
		}

		// $this->listview_qry .= ', IF(t5.form_status != "Approved", " ",   '.$this->db->dbprefix.'employee_leaves.date_approved) AS approved_date';
		$this->listview_qry .= ', '.$this->db->dbprefix.'employee_leaves.date_approved AS approved_date';

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

		if( $this->input->post('leaveType') && $this->input->post('leaveType') != 'null' ) $this->db->where_in($this->module_table.'.application_form_id ',$this->input->post('leaveType'));
		if( $this->input->post('leaveStatus') && $this->input->post('leaveStatus') != 'null' ){ 
			$this->db->where_in($this->module_table.'.form_status_id',$this->input->post('leaveStatus'));
		}

		if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){ 
			$where_date_start = $this->db->dbprefix.$this->module_table.".date_from BETWEEN '".date('Y-m-d',strtotime($this->input->post('dateStart')))."' AND '".date('Y-m-d',strtotime($this->input->post('dateEnd')))."'";
			$where_date_end = $this->db->dbprefix.$this->module_table.".date_to BETWEEN '".date('Y-m-d',strtotime($this->input->post('dateStart')))."' AND '".date('Y-m-d',strtotime($this->input->post('dateEnd')))."'"; 
			$this->db->where( '('. $where_date_start .' OR '. $where_date_end . ')');
		}

		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) 
			$this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       

        if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.employee_type ', $this->input->post('employee_type'));       
        if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.status_id ', $this->input->post('employment_status'));       

		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$result = $this->db->get();

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

			if( $this->input->post('leaveType') && $this->input->post('leaveType') != 'null' ) $this->db->where_in($this->module_table.'.application_form_id ',$this->input->post('leaveType'));
			if( $this->input->post('leaveStatus') && $this->input->post('leaveStatus') != 'null' ){ 
				$this->db->where_in($this->module_table.'.form_status_id',$this->input->post('leaveStatus'));
			}

			if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){ 
				$where_date_start = $this->db->dbprefix.$this->module_table.".date_from BETWEEN '".date('Y-m-d',strtotime($this->input->post('dateStart')))."' AND '".date('Y-m-d',strtotime($this->input->post('dateEnd')))."'";
				$where_date_end = $this->db->dbprefix.$this->module_table.".date_to BETWEEN '".date('Y-m-d',strtotime($this->input->post('dateStart')))."' AND '".date('Y-m-d',strtotime($this->input->post('dateEnd')))."'";
				$this->db->where( '('. $where_date_start .' OR '. $where_date_end . ')');
			}

			if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) 
				$this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       

	        if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.employee_type ', $this->input->post('employee_type'));       
	        if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.status_id ', $this->input->post('employment_status'));       


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
				}elseif(CLIENT_DIR == 'pioneer'){
					$sort = $this->db->dbprefix."user.company_id AND " .$this->db->dbprefix.$this->module_table.".employee_id";
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);
			
			$result = $this->db->get();


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
					$columns_data = $result->field_data();
					$column_type = array();
					foreach($columns_data as $column_data){
						$column_type[$column_data->name] = $column_data->type;
					}
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
							
							else{

								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39) ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
								
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );

									if ($detail['name'] == 'date_approved') {
										if ($row['approved_date'] != '0000-00-00 00:00:00') {
											$cell[$cell_ctr] =  date('d F Y' , strtotime($row['approved_date']));
										}else{
											$cell[$cell_ctr] =  ' ';//$row['approved_date'];
										}
									}

									if ($detail['name'] == 'employee_leave_id') {
										$this->db->select('SUM(credit) as credit');
										$credits = $this->db->get_where('employee_leaves_dates', array('employee_leave_id' => $row['employee_leave_id']))->row_array();
										$cell[$cell_ctr] = $credits['credit'] ;
									}

									if(CLIENT_DIR == "firstbalfour"){
										if ($detail['name'] == 'approvers'){
											// $cell[$cell_ctr] = "chu".$row[$detail['name']];
											$leave = $this->db->get_where($this->module_table, array($this->key_field => $row['employee_leave_id']))->row();
											
											$approvers = array();
											switch( $leave->form_status_id ){
												//display all leaves approvers if draft, for approval and cancelled 
												case 1: 
												case 2: 
													$approvers = $this->system->get_approvers_and_condition($row['employee_id'], 55);
													break;
												case 3: //approved
													$this->db->where("leave_id = {$row['employee_leave_id']} AND (status = 3 OR status = 1)");
													$leave_app = $this->db->get("{$this->db->dbprefix}leave_approver");
														foreach($leave_app->result() as $row_app){
															$approvers[] = array(
												    			'approver' => $row_app->approver,
												    			'sequence' => $row_app->sequence,
												    			'condition' => $row_app->condition,
												    			'focus' => $row_app->focus,
												    			'status' => $row_app->status
												    		);
													}
													break;
												case 4: //disapproved
													$this->db->where("leave_id = {$row['employee_leave_id']} AND status = 4");
													$leave_app = $this->db->get("{$this->db->dbprefix}leave_approver");
														foreach($leave_app->result() as $row_app){
															$approvers[] = array(
												    			'approver' => $row_app->approver,
												    			'sequence' => $row_app->sequence,
												    			'condition' => $row_app->condition,
												    			'focus' => $row_app->focus,
												    			'status' => $row_app->status
												    		);
													}
													break;
												case 5: //cancelled
													$this->db->where("leave_id = {$row['employee_leave_id']}");
													$leave_app = $this->db->get("{$this->db->dbprefix}leave_approver");
														foreach($leave_app->result() as $row_app){
															$approvers[] = array(
												    			'approver' => $row_app->approver,
												    			'sequence' => $row_app->sequence,
												    			'condition' => $row_app->condition,
												    			'focus' => $row_app->focus,
												    			'status' => $row_app->status
												    		);
													}
													break;
												default:
													$this->db->where("leave_id = {$row['employee_leave_id']}");
													$leave_app = $this->db->get("{$this->db->dbprefix}leave_approver");
														foreach($leave_app->result() as $row_app){
															$approvers[] = array(
												    			'approver' => $row_app->approver,
												    			'sequence' => $row_app->sequence,
												    			'condition' => $row_app->condition,
												    			'focus' => $row_app->focus,
												    			'status' => $row_app->status
												    		);
													}
													break;
											}
											
											if(count($approvers) > 0){
												foreach($approvers as $approver){
													$approver_status = $this->db->query( "SELECT form_status FROM  {$this->db->dbprefix}form_status s WHERE s.form_status_id = ".$approver["status"])->row();
													if($approver["status"] == 3) { $class_color = 'green'; }
													else if($approver["status"] == 4 || $approver["status"] == 5) { $class_color = 'red'; }
													else { $class_color = 'orange'; }

													if($approver["status"] > 2) { $approver_final_status = $approver_status->form_status; }
													else  { $approver_final_status = ""; }

													if($approver["condition"] == 2){
														if($leave->form_status_id == 3 || $leave->form_status_id == 4){
															$qry_app = "SELECT  CONCAT(c.firstname, ' ', c.lastname) as name
															 		FROM {$this->db->dbprefix}user c WHERE c.user_id = ".$approver["approver"];
															$approver_name = $this->db->query( $qry_app );
															if($approver_name->num_rows() > 0){
																	$cell[$cell_ctr] .= '<br/><em class="small">';
																foreach($approver_name->result() as $approvers_name){
																	$cell[$cell_ctr] .= $approvers_name->name.' : <div style="width: 99%; text-align: right;" class="'.$class_color.'">'.$approver_final_status.'</div>';
																	$cell[$cell_ctr] .= '</em>';
																}
															}
														}else{
															$qry_app = "SELECT  CONCAT(c.firstname, ' ', c.lastname) as name
															 		FROM {$this->db->dbprefix}user c WHERE c.user_id = ".$approver["approver"];
															$approver_name = $this->db->query( $qry_app );
															if($approver_name->num_rows() > 0){
																	$cell[$cell_ctr] .= '<br/><em class="small">';
																foreach($approver_name->result() as $approvers_name){
																	$cell[$cell_ctr] .= $approvers_name->name.' : <div style="width: 99%; text-align: right;" class="'.$class_color.'">'.$approver_final_status.'</div>';
																	$cell[$cell_ctr] .= '</em>';
																}
															}
													}
													}else{
														$qry_app = "SELECT  CONCAT(c.firstname, ' ', c.lastname) as name
														 		FROM {$this->db->dbprefix}user c WHERE c.user_id = ".$approver["approver"];
														$approver_name = $this->db->query( $qry_app );
														if($approver_name->num_rows() > 0){
																$cell[$cell_ctr] .= '<br/><em class="small">';
															foreach($approver_name->result() as $approvers_name){
																$cell[$cell_ctr] .= $approvers_name->name.' : <div style="width: 99%; text-align: right;" class="'.$class_color.'">'.$approver_final_status.'</div>';
																$cell[$cell_ctr] .= '</em>';
															}
														}
													}
												}
															$cell[$cell_ctr] = substr($cell[$cell_ctr], 0, -7);
															$cell[$cell_ctr] .= '</em>';
											}

										}
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 3 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] ) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else{
									$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];

									if (CLIENT_DIR == 'pioneer' || CLIENT_DIR == 'firstbalfour'){
										if ($detail['name'] == 'remarks') {
											$cell[$cell_ctr] =  $row['company'];
										}									
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

	function export() {	
		$query_id = '5';

		if (!$query_id || $query_id < 0) {
			show_error('No ID specified');
		}

		$this->db->where('export_query_id', $query_id);

		$leave_type = "";
		$leave_status = "";

		if( $this->input->post('leave_type') ){
			$leave_type = implode(',',$this->input->post('leave_type'));
		}

		if( $this->input->post('leave_status') ){
			$leave_status = implode(',',$this->input->post('leave_status'));
		}

		$result = $this->db->get('export_query');


		$export = $result->row();
		$sql = str_replace('{dbprefix}', $this->db->dbprefix, $export->query_string);

		// $sql .= " LEFT JOIN {$this->db->dbprefix}employee e ON u.employee_id = e.employee_id";

		$sql.= " WHERE ";
		$sql_string = "el.deleted=0 AND 1 ";

		if( $leave_type != "" ){
			if( $sql_string == "" ){
				$sql_string .= "el.application_form_id IN (".$leave_type.")";
			}
			else{
				$sql_string .= "AND el.application_form_id IN (".$leave_type.")";
			}
		}


		if( $leave_status != "" ){
			if( $sql_string == "" ){
				$sql_string .= "el.form_status_id IN (".$leave_status.")";
			}
			else{
				$sql_string .= "AND el.form_status_id IN (".$leave_status.")";
			}
		}


		if( $this->input->post('leave_period_start') != "" && $this->input->post('leave_period_end') != ""){
			$where_date_start = "el.date_from BETWEEN '".date('Y-m-d',strtotime($this->input->post('leave_period_start')))."' AND '".date('Y-m-d',strtotime($this->input->post('leave_period_end')))."'";
			$where_date_end = "el.date_to BETWEEN '".date('Y-m-d',strtotime($this->input->post('leave_period_start')))."' AND '".date('Y-m-d',strtotime($this->input->post('leave_period_end')))."'"; 

			if( $sql_string == "" ){
				$sql_string .= "(".$where_date_start." OR ".$where_date_end.")";
			}
			else{
				$sql_string .= " AND (".$where_date_start." OR ".$where_date_end.")";
			}
		}

		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $sql_string .= ' AND u.employee_id  IN ('.implode(",",$this->input->post('employee')).')';		

		if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $sql_string .= ' AND e.status_id IN ('.implode(",",$this->input->post('employment_status')).')';
		if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $sql_string .= ' AND e.employee_type IN ('.implode(",",$this->input->post('employee_type')).')';

		if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            if ($sidx == "t2application_form"){
            	$sidx = "ft.application_form";
            }
            if ($sidx == "t4form_status"){
            	$sidx = "fs.form_status";
            }

            if ($sidx == "employee_leaves.employee_id") {
            	$sidx = 'el.employee_id';
            }

            if (CLIENT_DIR == "pioneer" || CLIENT_DIR == 'firstbalfour') {

            	if ($sidx == "t3application_form"){
	            	$sidx = "ft.application_form";
	            }

            	if ($sidx == "t5form_status"){
            		$sidx = "fs.form_status";
            	}


            }

            	
            //$this->db->order_by($sidx . ' ' . $sord);
            $sql_string .= "  ORDER BY ".$sidx . ' ' . $sord;
        }else{
        	if(CLIENT_DIR == 'pioneer') {
        	 $sql_string .= " ORDER BY c.company_id,u.lastname ";
        	}
        } 

		$query  = $this->db->query($sql.$sql_string);

		$fields = $query->list_fields();

		$this->_fields = $fields;
		$this->_export = $export;
		$this->_query  = $query;
		// dbug($this->db->last_query());
		// die();
		$this->_excel_export();
	}
	
	private function _excel_export()
	{
		$userinfo = $this->userinfo;
		$query  = $this->_query;
		$fields = $this->_fields;
		$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setTitle('asd')->setDescription('asd');
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		foreach ($fields as $field) {
			if($field != 'employee_id'){
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				// $activeSheet->setCellValueExplicit($xcoor . '6', $field, PHPExcel_Cell_DataType::TYPE_STRING); 
				$activeSheet->setCellValueExplicit($xcoor . '6', ($field == 'Date Approved') ? "Date Approved/ Cancelled/ Disapproved" : $field , PHPExcel_Cell_DataType::TYPE_STRING); 

				$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
				
				$alpha_ctr++;
			}
		}

		for($ctr=1; $ctr<6; $ctr++){
			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);
		}

		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo['firstname'].' '.$userinfo['lastname'].' &RPage &P of &N');
		$activeSheet->setCellValueExplicit('A2', 'Leave Report', PHPExcel_Cell_DataType::TYPE_STRING); 




		if( $this->input->post('leave_period_start') != "" && $this->input->post('leave_period_end') != "" ){
			$activeSheet->setCellValueExplicit('A3',  date('F d,Y',strtotime($this->input->post('leave_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('leave_period_end'))), PHPExcel_Cell_DataType::TYPE_STRING); 


		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);


		// contents.
		$line = 7;

		foreach ($query->result() as $row) {
			$sub_ctr   = 0;
			$alpha_ctr = 0;			
			
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

				
				if( $field == 'Division' ){

					$division_array = array();
					$division_record = "";
					$division_list = explode(',',$row->{$field});

					foreach( $division_list as $division ){
						if( $division > 0 ){
							$division_result = $this->db->get_where('user_company_division',array('division_id' => $division));
							$division_row = $division_result->row();
							array_push($division_array,$division_row->division);
						}
					}

					$division_record = implode(',',$division_array);

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $division_record, PHPExcel_Cell_DataType::TYPE_STRING); 

				}
				else if( $field == 'Department' ){

					$department_array = array();
					$department_record = "";
					$department_list = explode(',',$row->{$field});

					foreach( $department_list as $department ){
						if( $department > 0 ){
							$department_result = $this->db->get_where('user_company_department',array('department_id' => $department));
							$department_row = $department_result->row();
							array_push($department_array,$department_row->department);
						}
					}

					$department_record = implode(',',$department_array);

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $department_record, PHPExcel_Cell_DataType::TYPE_STRING); 
				}
				else if( ( $field == 'Date From' || $field == 'Date To' || $field == 'Date Created')  ){

					if( ( $row->{$field} != "" && $row->{$field} != "0000-00-00" )  ){

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date($this->config->item('display_date_format'),strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 

					}
				}
				else if( ($field == 'Date Approved')  ){
					
					if( ( $row->{$field} != "0000-00-00 00:00:00" )  ){

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date($this->config->item('display_date_format'),strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 

					}else{
						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, ' ', PHPExcel_Cell_DataType::TYPE_STRING); 
					}
				}
				else if( ($field == '# of Days Applied')  ){
					$employee_leave_id = $row->{$field};
					$this->db->select('SUM(credit) as credit');
					$credits = $this->db->get_where('employee_leaves_dates', array('employee_leave_id' => $row->{$field}))->row();
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $credits->credit, PHPExcel_Cell_DataType::TYPE_NUMERIC); 
				}	
				else if($field == 'employee_id') {
					$employee_id = $row->{$field};
				}
				else if($field == 'Approver'){
									if(CLIENT_DIR == "firstbalfour"){
											$leave = $this->db->get_where($this->module_table, array($this->key_field => $employee_leave_id))->row();
											$approvers = array();
											$leave_approver_name = "";
											switch( $leave->form_status_id ){
												//display all leaves approvers if draft, for approval and cancelled 
												case 1: 
												case 2: 
													$approvers = $this->system->get_approvers_and_condition($row->employee_id, 55);
													break;
												case 3: //approved
													$this->db->where("leave_id = $employee_leave_id AND (status = 3 OR status = 1)");
													$leave_app = $this->db->get("{$this->db->dbprefix}leave_approver");
														foreach($leave_app->result() as $row_app){
															$approvers[] = array(
												    			'approver' => $row_app->approver,
												    			'sequence' => $row_app->sequence,
												    			'condition' => $row_app->condition,
												    			'focus' => $row_app->focus,
												    			'status' => $row_app->status
												    		);
													}
													break;
												case 4: //disapproved
													$this->db->where("leave_id = $employee_leave_id AND status = 4");
													$leave_app = $this->db->get("{$this->db->dbprefix}leave_approver");
														foreach($leave_app->result() as $row_app){
															$approvers[] = array(
												    			'approver' => $row_app->approver,
												    			'sequence' => $row_app->sequence,
												    			'condition' => $row_app->condition,
												    			'focus' => $row_app->focus,
												    			'status' => $row_app->status
												    		);
													}
													break;
												case 5: //cancelled
												default:
													$this->db->where("leave_id = $employee_leave_id");
													$leave_app = $this->db->get("{$this->db->dbprefix}leave_approver");
														foreach($leave_app->result() as $row_app){
															$approvers[] = array(
												    			'approver' => $row_app->approver,
												    			'sequence' => $row_app->sequence,
												    			'condition' => $row_app->condition,
												    			'focus' => $row_app->focus,
												    			'status' => $row_app->status
												    		);
													}
													break;
											}

											if(count($approvers) > 0){
												foreach($approvers as $approver){
													$approver_status = $this->db->query( "SELECT form_status FROM  {$this->db->dbprefix}form_status s WHERE s.form_status_id = ".$approver["status"])->row();
													if($approver["status"] == 3) { $class_color = 'green'; }
													else if($approver["status"] == 4 || $approver["status"] == 5) { $class_color = 'red'; }
													else { $class_color = 'orange'; }

													if($approver["status"] > 2) { $approver_final_status = $approver_status->form_status; }
													else  { $approver_final_status = ""; }

													if($approver["condition"] == 2){
														if($leave->form_status_id == 3 || $leave->form_status_id == 4){
															$qry_app = "SELECT  CONCAT(c.firstname, ' ', c.lastname) as name
															 		FROM {$this->db->dbprefix}user c WHERE c.user_id = ".$approver["approver"];
															$approver_name = $this->db->query( $qry_app );
															if($approver_name->num_rows() > 0){
																foreach($approver_name->result() as $approvers_name){
																	$leave_approver_name .= $approvers_name->name.' : '.$approver_final_status.', ';
																}
															}
														}else{
														$qry_app = "SELECT  CONCAT(c.firstname, ' ', c.lastname) as name
														 		FROM {$this->db->dbprefix}user c WHERE c.user_id = ".$approver["approver"];
														$approver_name = $this->db->query( $qry_app );
														if($approver_name && $approver_name->num_rows() > 0){
															foreach($approver_name->result() as $approvers_name){
																$leave_approver_name .= $approvers_name->name.' : '.$approver_final_status.', ';
															}
														}
													}
													}else{
														$qry_app = "SELECT  CONCAT(c.firstname, ' ', c.lastname) as name
														 		FROM {$this->db->dbprefix}user c WHERE c.user_id = ".$approver["approver"];
														$approver_name = $this->db->query( $qry_app );
														if($approver_name->num_rows() > 0){
															foreach($approver_name->result() as $approvers_name){
																$leave_approver_name .= $approvers_name->name.' : '.$approver_final_status.', ';
															}
														}
													}
												}
												$leave_approver_name = substr($leave_approver_name, 0, -2);
											}
										$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $leave_approver_name, PHPExcel_Cell_DataType::TYPE_STRING); 
									}else{
										$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 

									}

				}	
				else{

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 
				}


				$alpha_ctr++;
			}

			$line++;
		}


		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . '' .url_title($export->description) . '.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
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
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
	            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
	        }        
    	}
        
        $buttons .= "</div>";

        $buttons="";
                
		return $buttons;
	}

    function populate_category()
    {
        $html = '';
        switch ($this->input->post('category_id')) {
            case 0:
                $html .= '';    
                break;
            case 1: // company
                $this->db->where('deleted', 0);
                $company = $this->db->get('user_company')->result_array();      
                $html .= '<select id="user_company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 2: // division
                $this->db->where('deleted', 0);
                $division = $this->db->get('user_company_division')->result_array();        
                $html .= '<select id="user_company_division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 3: // department
                $this->db->where('deleted', 0);
                $department = $this->db->get('user_company_department')->result_array();        
                $html .= '<select id="user_company_department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';               
                break;                                          
            case 4: // section
                $this->db->where('deleted', 0);
                $company = $this->db->get('user_section')->result_array();      
                $html .= '<select id="user_section" multiple="multiple" class="multi-select" style="width:400px;" name="section[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["section_id"].'">'.$company_record["section"].'</option>';
                    }
                $html .= '</select>';   
                break;   
            case 5: // level
                $this->db->where('deleted', 0);
                $employee_type = $this->db->get('employee_type')->result_array();       
                $html .= '<select id="employee_type" multiple="multiple" class="multi-select" style="width:400px;" name="employee_type[]">';
                    foreach($employee_type as $employee_type_record){
                        $html .= '<option value="'.$employee_type_record["employee_type_id"].'">'.$employee_type_record["employee_type"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 6: // employment status
                $this->db->where('deleted', 0);
                $employment_status = $this->db->get('employment_status')->result_array();       
                $html .= '<select id="employment_status" multiple="multiple" class="multi-select" style="width:400px;" name="employment_status[]">';
                    foreach($employment_status as $employment_status_record){
                        $html .= '<option value="'.$employment_status_record["employment_status_id"].'">'.$employment_status_record["employment_status"].'</option>';
                    }
                $html .= '</select>';   
                break;                                 
            case 7: // employee
                $this->db->where('user.deleted', 0);
                $this->db->join('employee', 'employee.employee_id = user.employee_id');
                $employee = $this->db->get('user')->result_array();     
                $html .= '<select id="user" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                $html .= '</select>';   
                break;  
        }       

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
    }

	function get_employees()
	{
		if (IS_AJAX)
		{
			$html = '';
			if ($this->input->post('category_id') != 'null') {
                switch ($this->input->post('category')) {
                    case 0:
                        $html .= '';    
                        break;
                    case 1: // company
                        $where = 'user.company_id IN ('.$this->input->post('category_id').')';
                        break;
                    case 2: // division
                        $where = 'user.division_id IN ('.$this->input->post('category_id').')';
                        break;
                    case 3: // department
                        $where = 'user.department_id IN ('.$this->input->post('category_id').')';
                        break;  
                    case 4: // section
                        $where = 'user.section_id IN ('.$this->input->post('category_id').')';
                        break;                      
                    case 5: // level
                        $where = 'employee_type IN ('.$this->input->post('category_id').')';
                        break;
                    case 6: // employment status
                        $where = 'status_id IN ('.$this->input->post('category_id').')';
                        break;                                                                                                      
                }	
				$this->db->where($where);
                $this->db->where('user.deleted', 0);
                $this->db->join('employee','user.employee_id = employee.employee_id');
				$result = $this->db->get('user');		

                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';

                if ($result && $result->num_rows() > 0){
                    $employee = $result->result_array();
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                }
                
                $html .= '</select>';  
			}

            $data['html'] = $html;
    		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function get_employee_time_record(){
		$html = '';
		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1:
		    	$this->db->where('deleted',0);
				$company = $this->db->get('user_company')->result_array();		
                $html .= '<select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2:
		    	$this->db->where('deleted',0);
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 3:
		    	$this->db->where('deleted',0);
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;		        
		    case 4:
		    	$this->db->where('deleted',0);
				$employee = $this->db->get('user')->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	if ($employee_record["firstname"] != "Super Admin"){
                        	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    	}
                    }
                $html .= '</select>';	
                break;
		    case 5: //project
		    	$this->db->where('deleted',0);
				$project = $this->db->get('project_name')->result_array();		
                $html .= '<select id="project" multiple="multiple" class="multi-select" style="width:400px;" name="project[]">';
                    foreach($project as $project_record){
                        	$html .= '<option value="'.$project_record["project_name_id"].'">'.$project_record["project_name"].'</option>';
                    }
                $html .= '</select>';	
                break;
		    case 6: //group
		    	$this->db->where('deleted',0);
				$group = $this->db->get('group_name')->result_array();		
                $html .= '<select id="group" multiple="multiple" class="multi-select" style="width:400px;" name="group[]">';
                    foreach($group as $group_record){
                        	$html .= '<option value="'.$group_record["group_name_id"].'">'.$group_record["group_name"].'</option>';
                    }
                $html .= '</select>';
		        break;	        		        		        		        
		}	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function _set_left_join()
	{
		parent::_set_left_join();
		$this->db->join('user', 'user.employee_id = '. $this->module_table .'.employee_id');
		$this->db->join('employee', 'employee.employee_id = user.employee_id','left');
		$this->db->join('user_company', 'user_company.company_id = user.company_id','left');
	}

	function _set_listview_query( $listview_id = '', $view_actions = true ) 
	{
		parent::_set_listview_query();

		$this->listview_qry .= ',company';
	}	
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>