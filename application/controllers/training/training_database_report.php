<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_database_report extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';	
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {

    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'training/training_database_report/listview';
		$data['jqgrid'] = 'training/training_database_report/jqgrid';

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

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		$buttons = "";                    
                            
		return $buttons;
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
		$search_string =  $this->input->post('searchString');
		if($this->input->post('_search') == "true" && strlen(trim($search_string)) > 0)
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
		$this->db->join('user','user.employee_id = '.$this->module_table.'.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('employee_work_assignment','employee_work_assignment.employee_id = user.employee_id AND '.$this->db->dbprefix('employee_work_assignment').'.assignment = "1"','left');
		$this->db->where('user.deleted',0);
    	$this->db->where('employee.resigned',0);
    	$this->db->where('employee.resigned_date is null');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);


		switch ($this->input->post('category')) {
            case 1:
                    if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.company_id ', $this->input->post('company'));
                break;
            case 2:
                    if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.division_id ', $this->input->post('division'));       
                break;
            case 3:
                    if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.department_id ', $this->input->post('department'));
                break;
            case 4:
                    if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       
                break;   
            case 5: //project
                    if( $this->input->post('project') && $this->input->post('project') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.project_name_id ', $this->input->post('project'));       
                break;   
            case 6: //group
                    if( $this->input->post('group') && $this->input->post('group') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.group_name_id ', $this->input->post('group'));       
                break;                                                              
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
			$this->db->join('user','user.employee_id = '.$this->module_table.'.employee_id','left');
			$this->db->join('employee','employee.employee_id = user.employee_id','left');
			$this->db->join('employee_work_assignment','employee_work_assignment.employee_id = user.employee_id AND '.$this->db->dbprefix('employee_work_assignment').'.assignment = "1"','left');
			$this->db->where('user.deleted',0);
	    	$this->db->where('employee.resigned',0);
	    	$this->db->where('employee.resigned_date is null');

			switch ($this->input->post('category')) {
	            case 1:
	                    if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.company_id ', $this->input->post('company'));
	                break;
	            case 2:
	                    if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.division_id ', $this->input->post('division'));       
	                break;
	            case 3:
	                    if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.department_id ', $this->input->post('department'));
	                break;
	            case 4:
	                    if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       
	                break;   
	            case 5: //project
	                    if( $this->input->post('project') && $this->input->post('project') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.project_name_id ', $this->input->post('project'));       
	                break;   
	            case 6: //group
	                    if( $this->input->post('group') && $this->input->post('group') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.group_name_id ', $this->input->post('group'));       
	                break;                                                              
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
							}
							elseif( $detail['name'] == "department_id" ){

								$training_database_info = $this->db->get_where('training_employee_database',array('training_employee_database_id'=>$row['training_employee_database_id']))->row();

								if( $training_database_info->department_id > 0 ){

									$this->db->where('department_id',$training_database_info->department_id);
									$department_info = $this->db->get('user_company_department')->row();

									$cell[$cell_ctr] = $department_info->department;

								}
								elseif( $training_database_info->project_name_id > 0 ){

									$this->db->where('project_name_id',$training_database_info->project_name_id);
									$project_name_info = $this->db->get('project_name')->row();

									$cell[$cell_ctr] = $project_name_info->project_name;

								}
								else{

									$cell[$cell_ctr] = "";

								}

								
								$cell_ctr++;

							}
							elseif( $detail['name'] == 'training_calendar_id' && $cell_ctr == 8 ){

								$this->db->select('session_date');
								$this->db->where('training_calendar_id',$row['training_calendar_id']);
								$training_session_result = $this->db->get('training_calendar_session');

								if( $training_session_result->num_rows() > 0 ){

									$session_dates = array();

									foreach( $training_session_result->result_array() as $training_session_info ){
										$session_dates[] = date('m/d/Y',strtotime($training_session_info['session_date']));
									}

									$cell[$cell_ctr] = implode(',', $session_dates);

								}
								else{
									$cell[$cell_ctr] = "";
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

	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions


	function get_employee_category_record(){
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
		    	$this->db->join('employee','employee.employee_id = user.employee_id','left');
		    	$this->db->where('user.deleted',0);
		    	$this->db->where('employee.resigned',0);
		    	$this->db->where('employee.resigned_date is null');
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


	function export() {	

		$this->db->select('employee.id_number as "ID Number"');
		$this->db->select('CONCAT( '.$this->db->dbprefix('user').'.firstname, " ", '.$this->db->dbprefix('user').'.lastname ) as "Employee Name"',false);
		$this->db->select('IF( '.$this->db->dbprefix($this->module_table).'.department_id > 0, user_company_department.department, '.$this->db->dbprefix('project_name').'.project_name ) as "Department / Project"');
		$this->db->select('employee.employed_date as "Date Hired"');
		$this->db->select('employment_status.employment_status as "Employment Status"');
		$this->db->select('user_rank.job_rank as "Rank / Level"');
		$this->db->select('user_position.position as "Current Position"');
		$this->db->select('training_subject.training_subject as "Training Course Title"');
		$this->db->select('training_calendar.training_calendar_id as "Dates of Training"');
		$this->db->select('training_provider.training_provider as "Training Provider"');
		$this->db->select('training_calendar_type.calendar_type as "Training Type"');
		$this->db->select('training_category.training_category as "Training Category"');
		$this->db->select('training_calendar.venue as "Training Venue"');
		$this->db->select('training_calendar.cost_per_pax as "Training Cost"');
		$this->db->from($this->module_table);
		$this->db->join('user',$this->db->dbprefix('user').'.employee_id = '.$this->module_table.'.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('employee_work_assignment','employee_work_assignment.employee_id = user.employee_id AND '.$this->db->dbprefix('employee_work_assignment').'.assignment = "1"','left');
		$this->db->join('user_company_department','user_company_department.department_id = employee_work_assignment.department_id','left');
		$this->db->join('project_name','project_name.project_name_id = '.$this->module_table.'.project_name_id','left');
		$this->db->join('employment_status','employment_status.employment_status_id = employee.status_id','left');
		$this->db->join('user_rank','user_rank.job_rank_id = '.$this->module_table.'.rank_id','left');
		$this->db->join('user_position','user_position.position_id = '.$this->module_table.'.position_id','left');
		$this->db->join('training_calendar','training_calendar.training_calendar_id = '.$this->module_table.'.training_calendar_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		$this->db->join('training_calendar_type','training_calendar_type.calendar_type_id = training_calendar.calendar_type_id','left');
		$this->db->join('training_category','training_category.training_category_id = training_subject.training_category_id','left');
		$this->db->where($this->module_table.'.deleted = 0 ');
		$this->db->where('user.deleted',0);
    	$this->db->where('employee.resigned',0);
    	$this->db->where('employee.resigned_date is null');


		switch ($this->input->post('category')) {
            case 1:
                    if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.company_id ', $this->input->post('company'));
                break;
            case 2:
                    if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.division_id ', $this->input->post('division'));       
                break;
            case 3:
                    if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.department_id ', $this->input->post('department'));
                break;
            case 4:
                    if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       
                break;   
            case 5: //project
                    if( $this->input->post('project') && $this->input->post('project') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.project_name_id ', $this->input->post('project'));       
                break;   
            case 6: //group
                    if( $this->input->post('group') && $this->input->post('group') != 'null' ) $this->db->where_in($this->db->dbprefix('employee_work_assignment').'.group_name_id ', $this->input->post('group'));       
                break;                                                              
        }

		$query  = $this->db->get();

		$fields = $query->list_fields();

		$this->_fields = $fields;
		//$this->_export = $export;
		$this->_query  = $query;
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
		$activeSheet->setCellValueExplicit('A2', 'Training Database Report', PHPExcel_Cell_DataType::TYPE_STRING); 
		$activeSheet->setCellValueExplicit('A3',  date('F d, Y'), PHPExcel_Cell_DataType::TYPE_STRING); 

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


				if( $field == "Date Hired" ){


					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date('F d,Y',strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 

				}
				elseif( $field == "Dates of Training" ){

					$dates_of_training = "";

					$this->db->select('session_date');
					$this->db->where('training_calendar_id',$row->{$field});
					$training_session_result = $this->db->get('training_calendar_session');

					if( $training_session_result->num_rows() > 0 ){

						$session_dates = array();

						foreach( $training_session_result->result_array() as $training_session_info ){
							$session_dates[] = date('m/d/Y',strtotime($training_session_info['session_date']));
						}


						$dates_of_training = implode(',',$session_dates);

					}

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $dates_of_training, PHPExcel_Cell_DataType::TYPE_STRING); 

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
		header('Content-Disposition: attachment;filename=Training_Database_Report_'.date('Y-m-d').'xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}


}