<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_employee_database extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Employee Database';
		$this->listview_description = 'This module lists all defined training employee database(s).';
		$this->jqgrid_title = "Training Employee Database List";
		$this->detailview_title = 'Training Employee Database Info';
		$this->detailview_description = 'This page shows detailed information about a particular training employee database.';
		$this->editview_title = 'Training Employee Database Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training employee database(s).';
   

		if( $this->input->post('filter')){
			switch ($this->input->post('filter')) {
				case 'epaf':
					$this->filter .= $this->db->dbprefix('training_application').".training_application_type = 1";
					break;
				case 'pgsa':
					$this->filter .= $this->db->dbprefix('training_application').".training_application_type = 2";
					break;
			}
    		
    	}
    	else{
    		$this->filter .= $this->db->dbprefix('training_application').".training_application_type = 1";
    	}


    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'training/training_employee_database/listview';
		$data['jqgrid'] = 'training/training_employee_database/jqgrid';
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

    	$tabs[] = '<li filter="epaf" class="active"><a href="javascript:void(0)">EPAF</li>';
    	$tabs[] = '<li filter="pgsa"><a href="javascript:void(0)">PGSA</li>';
    	
    	if( sizeof( $tabs ) > 1 ) $data['database_tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
    	
		
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
		$data['content'] = 'detailview';
		
		//other views to load
		$data['views'] = array();

		$this->db->join('training_calendar','training_calendar.training_calendar_id = training_employee_database.training_calendar_id','left');
		$this->db->join('training_type','training_type.training_type_id = training_calendar.calendar_type_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		$this->db->join('training_subject_schedule','training_subject_schedule.training_subject_schedule_id = training_subject.training_subject_schedule_id','left');
		$this->db->where('training_employee_database.training_employee_database_id',$this->input->post('record_id'));
		$employee_training = $this->db->get('training_employee_database');

		$data['employee_training_total'] = $employee_training->num_rows();

		if( $employee_training->num_rows() > 0 ){

			$employee_training_result = $employee_training->result_array();

			foreach( $employee_training_result as $key => $employee_training_info ){

				$employee_training_result[$key]['start_date'] = date('d F Y',strtotime($employee_training_result[$key]['start_date']));
				$employee_training_result[$key]['end_date'] = date('d F Y',strtotime($employee_training_result[$key]['end_date']));

			}

		}

		$data['employee_training'] = $employee_training_result;

		$this->db->where('training_employee_database.training_employee_database_id',$this->input->post('record_id'));
		$budget_training = $this->db->get('training_employee_database');


		$data['buttons'] = 'training/training_employee_database/detail-buttons';

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


		$employee_info = $this->db->get_where('employee',array('employee_id'=>$this->userinfo['user_id']))->row();

		$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'],$employee_info->rank_id, $this->userinfo['user_id']);
		$subordinate_list = array();

		if( count($subordinates) > 0 ){
			foreach( $subordinates as $subordinate_record ){
				array_push($subordinate_list, $subordinate_record['employee_id']);
			}

		}

		array_push($subordinate_list, $this->userinfo['user_id']);



		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->join('training_application','training_application.training_application_id = '.$this->db->dbprefix($this->module_table).'.training_application_id','left');
		$this->db->join('training_calendar','training_calendar.training_calendar_id = '.$this->module_table.'.training_calendar_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('user','user.employee_id = '.$this->module_table.'.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('user_position','user_position.position_id = user.position_id','left');
		$this->db->join('employee_work_assignment','employee_work_assignment.employee_id = user.employee_id','left');
		$this->db->join('user_company_department','user_company_department.department_id = '.$this->module_table.'.department_id','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		//$this->db->where_not_in('user.user_id',array(1,2,3));
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

		if( $this->user_access[$this->module_id]['post'] != 1 ){
			$this->db->where_in('user.employee_id',$subordinate_list);
		}


		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$total_records =  $this->db->count_all_results();
		$response->last_query = $this->db->last_query();

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
			$this->db->join('training_application','training_application.training_application_id = '.$this->db->dbprefix($this->module_table).'.training_application_id','left');
			$this->db->join('training_calendar','training_calendar.training_calendar_id = '.$this->module_table.'.training_calendar_id','left');
			$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
			$this->db->join('user','user.employee_id = '.$this->module_table.'.employee_id','left');
			$this->db->join('employee','employee.employee_id = user.employee_id','left');
			$this->db->join('user_position','user_position.position_id = user.position_id','left');
			$this->db->join('employee_work_assignment','employee_work_assignment.employee_id = user.employee_id','left');
			$this->db->join('user_company_department','user_company_department.department_id = '.$this->module_table.'.department_id','left');		
			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			//$this->db->where_not_in('user.user_id',array(1,2,3));
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

			if( $this->user_access[$this->module_id]['post'] != 1 ){
				$this->db->where_in('user.employee_id',$subordinate_list);
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
		parent::ajax_save();
		
		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
        
        $buttons .= "<div class='icon-label'><a class='icon-16-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
                  
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';

		$this->db->where('training_employee_database_id',$record['training_employee_database_id']);
		$employee_database_info = $this->db->get('training_employee_database')->row();
                
		if ($this->user_access[$this->module_id]['post']) {
			if( $employee_database_info->service_bond == 1 ){
            	$actions .= '<a class="icon-button icon-16-document-stack" module_link="'.$module_link.'" tooltip="Print Service Bond Agreement Form" href="javascript:void(0)"></a>';
        	}
        }

        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record') ) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        

        $actions .= '</span>';

		return $actions;
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
		$search_string[] = $this->db->dbprefix .'user_company_department.department LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'user_position.position LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'training_employee_database.training_course LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'training_employee_database.start_date LIKE "%' . date('Y-m-d',strtotime($value)) . '%"';
		$search_string[] = $this->db->dbprefix .'training_employee_database.end_date LIKE "%' . date('Y-m-d',strtotime($value)) . '%"';
		$search_string[] = $this->db->dbprefix .'training_employee_database.training_balance LIKE "%' . $value . '%"';


		$search_string = '('. implode(' OR ', $search_string) .')';


		return $search_string;
	}

	function _set_specific_search_query()
	{
		$field = $this->input->post('searchField');
		$operator =  $this->input->post('searchOper');
		$value =  $this->input->post('searchString');

		if($field == 't0firstnamelastname'){
			switch ($operator) {
				case 'eq':
					return '( '.$this->db->dbprefix("user").'.firstname == "'.$value.'" || '.$this->db->dbprefix("user").'.lastname == "'.$value.'" )';
					break;
				case 'ne':
					return '( '.$this->db->dbprefix("user").'.firstname != "'.$value.'" || '.$this->db->dbprefix("user").'.lastname != "'.$value.'" )';
					break;
				case 'ew':
					return '( '.$this->db->dbprefix("user").'.firstname LIKE "%'.$value.'" || '.$this->db->dbprefix("user").'.lastname LIKE "%'.$value.'" )';
					break;
				case 'en':
					return '( '.$this->db->dbprefix("user").'.firstname NOT LIKE "%'.$value.'" || '.$this->db->dbprefix("user").'.lastname NOT LIKE "'.$value.'%" )';
					break;
				case 'cn':
					return '( '.$this->db->dbprefix("user").'.firstname LIKE "%'.$value.'%" || '.$this->db->dbprefix("user").'.lastname LIKE "%'.$value.'%" )';
					break;
				case 'nc':
					return '( '.$this->db->dbprefix("user").'.firstname NOT LIKE "%'.$value.'%" || '.$this->db->dbprefix("user").'.lastname NOT LIKE "%'.$value.'%" )';
					break;
				default:
					return '( '.$this->db->dbprefix("user").'.firstname LIKE "%'.$value.'%" || '.$this->db->dbprefix("user").'.lastname LIKE "%'.$value.'%" )';
			}
		}

		if($field == "training_employee_database.employee_id1"){
			$field = $this->db->dbprefix("user_position").".position";
		}

		if($field == "training_employee_database.training_course"){
			$field = $this->db->dbprefix("training_employee_database")."training_course";
		}

		if($field == "t1position"){
			$field = $this->db->dbprefix("user_position").".position";
		}

		if($field=='t2department'){
			$field = $this->db->dbprefix("user_company_department").".department";
		}

		if($field == "training_employee_database.employee_id2"){
			$field = $this->db->dbprefix("user_company_department").".department";
		}

		if($field == "training_employee_database.start_date"){
			$value = date('Y-m-d',strtotime($value));
		}

		if($field == "training_employee_database.end_date"){
			$field = date('Y-m-d',strtotime($value));
		}

		foreach( $this->search_columns as $search )
		{
			if($search['jq_index'] == $field) $field = $search['column'];
		}

		$field = strtolower( $field );
		if(sizeof(explode(' as ', $field)) > 1){
			$as_part = explode(' as ', $field);
			$field = strtolower( trim( $as_part[0] ) );
		}

		switch ($operator) {
			case 'eq':
				return $field . ' = "'.$value.'"';
				break;
			case 'ne':
				return $field . ' != "'.$value.'"';
				break;
			case 'lt':
				return $field . ' < "'.$value.'"';
				break;
			case 'le':
				return $field . ' <= "'.$value.'"';
				break;
			case 'gt':
				return $field . ' > "'.$value.'"';
				break;
			case 'ge':
				return $field . ' >= "'.$value.'"';
				break;
			case 'bw':
				return $field . ' REGEXP "^'. $value .'"';
				break;
			case 'bn':
				return $field . ' NOT REGEXP "^'. $value .'"';
				break;
			case 'in':
				return $field . ' IN ('. $value .')';
				break;
			case 'ni':
				return $field . ' NOT IN ('. $value .')';
				break;
			case 'ew':
				return $field . ' LIKE "%'. $value  .'"';
				break;
			case 'en':
				return $field . ' NOT LIKE "%'. $value  .'"';
				break;
			case 'cn':
				return $field . ' LIKE "%'. $value .'%"';
				break;
			case 'nc':
				return $field . ' NOT LIKE "%'. $value .'%"';
				break;
			default:
				return $field . ' LIKE %'. $value .'%';
		}
	}
	// END - default module functions
	
	// START custom module funtions
	
	function excel_export(){

		if($this->input->post('prev_search_field') != "" ){

			$_POST['searchField'] = $this->input->post('prev_search_field');
			$_POST['searchOper'] = $this->input->post('prev_search_option');
			$_POST['searchString'] = $this->input->post('prev_search_str');

			$search = $this->input->post('prev_search_field') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		}
		else{
			$search = 1;
		}

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';

		$sidx = $this->input->post('sidx');
		$sord = $this->input->post('sord');

		$this->db->select('CONCAT('.$this->db->dbprefix('user').'.firstname," ",'.$this->db->dbprefix('user').'.lastname) as Employee',false);
		$this->db->select($this->db->dbprefix('user_position').'.position as Position');
		$this->db->select($this->db->dbprefix('user_company_department').'.department as Department');
		$this->db->select($this->db->dbprefix('training_employee_database').'.training_balance as "Training Balance"');
		$this->db->select($this->db->dbprefix('training_employee_database').'.training_course as "Training Course"');
		$this->db->select($this->db->dbprefix('training_employee_database').'.start_date as "Start Date"');
		$this->db->select($this->db->dbprefix('training_employee_database').'.end_date as "End Date"');
		$this->db->from($this->module_table);
		$this->db->join('training_application','training_application.training_application_id = '.$this->module_table.'.training_application_id','left');
		$this->db->join('training_calendar','training_calendar.training_calendar_id = '.$this->module_table.'.training_calendar_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('user','user.employee_id = '.$this->module_table.'.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('user_position','user_position.position_id = user.position_id','left');
		$this->db->join('user_company_department','user_company_department.department_id = '.$this->module_table.'.department_id','left');
		
		if( $this->input->post('filter')){
			switch ($this->input->post('filter')) {
				case 'epaf':
					$this->db->where($this->db->dbprefix('training_application').".training_application_type = 1");
					break;
				case 'pgsa':
					$this->db->where($this->db->dbprefix('training_application').".training_application_type = 2");
					break;
			}
    		
    	}
    	else{
    		$this->db->where($this->db->dbprefix('training_application').".training_application_type = 1");
    	}

		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);


		if($sidx != ""){
			
			switch( $sidx ){
				case "t0firstnamelastname":
					$sidx = "Employee";
				break;
				case "training_employee_database.employee_id1":
					$sidx = "Position";
				break;
				case "training_employee_database.training_course":
					$sidx = "Training Course";
				break;
				case "t1position":
					$sidx = "Position";
				break;
				case "t2department":
					$sidx = "Department";
				break;
				case "training_employee_database.employee_id2":
					$sidx = "Department";
				break;
			}

		}

		if($sidx != ""){
			$this->db->order_by($sidx, $sord);
		}

		$query = $this->db->get();


		$fields = $query->list_fields();

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Employee Training")
		            ->setDescription("Employee Training");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		
		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);							
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);	
		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);					

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			)
		);

		$styleArrayHeader = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);



		

		$activeSheet->setCellValueExplicit('A2',  'Employee Training Database Report', PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArrayHeader);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		$objPHPExcel->getActiveSheet()->mergeCells('A2:G2');

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

			$activeSheet->setCellValueExplicit($xcoor . '4',  $field, PHPExcel_Cell_DataType::TYPE_STRING);


			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '4')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		$line = 5;


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


				if( $field == 'Start Date' || $field == 'End Date' ){

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  date('d F Y',strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING);

				}else{

					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line,  $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING);

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
		header('Content-Disposition: attachment;filename=Employee_Training_Database_'.date('Y-m-d').'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');	


	}

	function print_record($record_id){

		$this->config->config['compress_output'] = 0;
		$this->load->library('pdf');
		$html = $this->_get_html_record($record_id);

		$this->db->join('user','user.employee_id = training_employee_database.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->where('training_employee_database.training_employee_database_id',$record_id);
		$employee_training = $this->db->get('training_employee_database')->row();

		$name = $employee_training->firstname.'_'.$employee_training->lastname;

		str_replace(' ', '_', $name);

		// Prepare and output the PDF.
		$this->pdf->addPage();
		$this->pdf->writeHTML($html, true, false, true, false, '');
		$this->pdf->Output('Employee_Training_Database_'.$name.'_'.date('Y-m-d').'.pdf', 'D');

	}

	function _get_html_record($record_id){

		$html = "";

		$this->db->join('user','user.user_id = training_employee_database.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('user_company','user_company.company_id = user.company_id','left');
		$this->db->join('user_position','user_position.position_id = user.position_id','left');
		$this->db->join('user_company_department','user_company_department.department_id = user.department_id','left');
		$this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
		$this->db->join('user_rank','user_rank.job_rank_id = employee.rank_id','left');
		$this->db->join('training_calendar','training_calendar.training_calendar_id = training_employee_database.training_calendar_id','left');
		$this->db->join('training_type','training_type.training_type_id = training_calendar.calendar_type_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		// $this->db->join('training_subject_schedule','training_subject_schedule.training_subject_schedule_id = training_subject.training_subject_schedule_id','left');
		$this->db->where('training_employee_database.training_employee_database_id',$record_id);
		$employee_training = $this->db->get('training_employee_database');

		$data['employee_training_total'] = $employee_training->num_rows();

		$employee_training_result = $employee_training->row_array();

		//get start date
		$this->db->where('training_calendar_id',$employee_training_result['training_calendar_id']);
		$this->db->order_by('session_date','asc');
		$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

		$start_date = date('F d Y',strtotime($training_calendar_session_info->session_date));

		//get end date
		$this->db->where('training_calendar_id',$employee_training_result['training_calendar_id']);
		$this->db->order_by('session_date','desc');
		$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

		$end_date = date('F d Y',strtotime($training_calendar_session_info->session_date));

		$logo = "";

		if(!empty($employee_training_result['logo'])) {
		  $logo = '<img alt="" src="'.base_url().''.$employee_training_result['logo'].'">';
		}

		// $type = $this->db->get_where('training_type', array('training_type_id' => $employee_training_result['allocated'] ))->row();
			if ($employee_training_result['allocated'] == "combined") {
				$this->db->where('training_type_id !=', $employee_training_result['training_type_id']);
			} else {
				$this->db->where('training_type_id', $employee_training_result['allocated']);
			}

			$training_types = $this->db->get('training_type');
			// dbug($this->db->last_query());
			$training_type = "";
			$reallocate = "";

			if ($training_types && $training_types->num_rows() > 0) {
				if ($training_types->num_rows() == 1) {
					$reallocate = $training_types->row()->training_type;
				} else {
					foreach ($training_types->result() as $type) {
						$training_type[] = $type->training_type;
					}
					$reallocate = implode(' / ', $training_type);
				}
				
			} 
	

		$budgeted = "";

		switch ($employee_training_result['budgeted']) {
            case '1':
                $budgeted = 'Not Budgeted';
                break;
            case '2':
                $budgeted = 'Re-allocation';
                break;
        }

		$data = array(
			'title' => 'Employee Training Database',
			'logo_2' => $logo,
			'employee_name' => $employee_training_result['firstname'].' '.$employee_training_result['lastname'],
			'position' => $employee_training_result['position'],
			'department' => $employee_training_result['department'],
			'division' => $employee_training_result['division'],
			'rank' => $employee_training_result['job_rank'],
			'training_course' => $employee_training_result['training_subject'],
			'training_provider' => $employee_training_result['training_provider'],
			'start_date' => $start_date,
			'end_date' => $end_date,
			'training_type' => $employee_training_result['training_type'],
			'venue' => $employee_training_result['venue'],
			'service_bond' => ( ( $employee_training_result['service_bond'] == 1 )? 'Yes' : 'No' ),
			'reallocation_from' => $reallocate . ' Budget',
			'investment' => $employee_training_result['investment'],
			'remaining_budget_reallocation' => number_format($employee_training_result['remaining_allocated'],2,'.',','),
			'budgeted' => $budgeted,
			'idp' => $employee_training_result['idp_completion'],
			'itb' => number_format($employee_training_result['itb'], 2, '.', ','),
			'remaining_itb' => number_format($employee_training_result['remaining_itb'], 2, '.', ','),
			'excess_itb' => number_format($employee_training_result['excess_itb'], 2, '.', ','),
			'ctb' => number_format($employee_training_result['ctb'], 2, '.', ','),
			'remaining_ctb' => number_format($employee_training_result['remaining_ctb'], 2, '.', ','),
			'excess_ctb' => number_format($employee_training_result['excess_ctb'], 2, '.', ','),
			'stb' => number_format($employee_training_result['stb'], 2, '.', ','),
			'remaining_stb' => number_format($employee_training_result['remaining_stb'], 2, '.', ','),
			'excess_stb' => number_format($employee_training_result['excess_stb'], 2, '.', ','),
			'total_budget' => number_format(( $employee_training_result['itb'] + $employee_training_result['ctb'] + $employee_training_result['stb'] ), 2, '.', ','),
			'remaining_budget' => number_format( ( $employee_training_result['remaining_itb'] + $employee_training_result['remaining_ctb'] + $employee_training_result['remaining_stb'] ) , 2, '.', ','),
			'excess_budget' => number_format( ( $employee_training_result['excess_itb'] + $employee_training_result['excess_ctb'] + $employee_training_result['excess_stb'] ) , 2, '.', ','),
		);

		$this->load->model('template');
		$template = $this->template->get_module_template($this->module_id, 'employee_training_database');

		$html = $this->template->prep_message($template['body'], $data);

		return $html;



	}

	function print_service_bond($record_id) {	

		

		$this->config->config['compress_output'] = 0;
		$this->load->library('pdf');
		$html = $this->_get_html($record_id);

		$this->db->join('user','user.employee_id = training_employee_database.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->where('training_employee_database.training_employee_database_id',$record_id);
		$employee_training = $this->db->get('training_employee_database')->row();

		$name = $employee_training->firstname.'_'.$employee_training->lastname;

		str_replace(' ', '_', $name);

		// Prepare and output the PDF.
		$this->pdf->addPage();
		$this->pdf->writeHTML($html, true, false, true, false, '');
		$this->pdf->Output('Service_Bond_Agreement_Form_'.$name.'_'.date('Y-m-d').'.pdf', 'D');
	}

	function _get_html($record_id){

		$html = "";

		$this->db->join('user','user.employee_id = training_employee_database.employee_id','left');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('user_company_department','user_company_department.department_id = user.department_id','left');
		$this->db->join('user_company_division','user_company_division.division_id = user.division_id','left');
		$this->db->join('training_application','training_application.training_application_id = training_employee_database.training_application_id','left');
		$this->db->join('user_position','user.position_id = user_position.position_id','left');
		$this->db->join('training_calendar','training_calendar.training_calendar_id = training_employee_database.training_calendar_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		$this->db->where('training_employee_database.training_employee_database_id',$record_id);
		$employee_training = $this->db->get('training_employee_database')->row();

		//get start date
		$this->db->where('training_calendar_id',$employee_training->training_calendar_id);
		$this->db->order_by('session_date','asc');
		$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

		$start_date = date('F d Y',strtotime($training_calendar_session_info->session_date));

		//get end date
		$this->db->where('training_calendar_id',$employee_training->training_calendar_id);
		$this->db->order_by('session_date','desc');
		$training_calendar_session_info = $this->db->get('training_calendar_session')->row();

		$end_date = date('F d Y',strtotime($training_calendar_session_info->session_date));

		$department_manager = "";

		//get department manager
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->where('user.deleted',0);
		$this->db->where('employee.resigned',0);
		$this->db->where('user.employee_id',$employee_training->dm_user_id);
		$this->db->where('employee.resigned_date is null');
		$department_manager_result = $this->db->get('user');

		if( $department_manager_result->num_rows() > 0 ){
			$department_manager_info = $department_manager_result->row();
			$department_manager = $department_manager_info->firstname.' '.$department_manager_info->lastname;
		}

		$division_manager = "";

		//get division manager
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->where('user.deleted',0);
		$this->db->where('employee.resigned',0);
		$this->db->where('user.employee_id',$employee_training->division_manager_id);
		$this->db->where('employee.resigned_date is null');
		$division_manager_result = $this->db->get('user');

		if( $division_manager_result->num_rows() > 0 ){
			$division_manager_info = $division_manager_result->row();
			$division_manager = $division_manager_info->firstname.' '.$division_manager_info->lastname;
		}



		$this->load->model('template');
		$template = $this->template->get_module_template($this->module_id, 'service_bond_agreement_form');


		$logo = get_branding();

		$company_qry = $this->db->get_where('user_company', array('company_id' => $employee_training->company_id))->row();
		if(!empty($company_qry->logo)) {
		  $logo = '<img alt="" src="./'.$company_qry->logo.'">';
		}

		$hr_head_name = "";

		//get hr head
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('user_position','user_position.position_id = user.position_id','left');
		$this->db->where('employee.resigned',0);
		$this->db->where('employee.resigned_date is null');
		$this->db->where('user.deleted',0);
		$this->db->where('user_position.position_code','hr_head');
		$hr_head_result = $this->db->get('user');

		if( $hr_head_result->num_rows() > 0 ){

			$hr_head_info = $hr_head_result->row();
			$hr_head_name = $hr_head_info->firstname.' '.$hr_head_info->lastname;

		}


		$data = array(
			'employee_name' => $employee_training->firstname.' '.$employee_training->lastname,
			'request_date' => date('F d Y'),
			'position' => $employee_training->position,
			'date_hired' => date('F d Y',strtotime($employee_training->employed_date)),
			'program_title' => $employee_training->training_subject,
			'investment' => number_format($employee_training->investment,2,'.',','),
			'provider' => $employee_training->training_provider,
			'inclusive_dates' => $start_date.' - '.$end_date,
			'hr_name' => '',
			'hr_head_name' => $hr_head_name,
			'department_manager' => $department_manager,
			'division_head' => $division_manager,
			'logo_2' => $logo
		);


		$html = $this->template->prep_message($template['body'], $data);

		return $html;



	}


	function calculate_total_training_cost(){

		$this->db->where('training_employee_database.training_employee_database_id',$this->input->post('record_id'));
		$employee_training = $this->db->get('training_employee_database')->row();

		$this->db->join('training_calendar','training_calendar.training_calendar_id = training_employee_database.training_calendar_id','left');
		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		$this->db->join('training_subject_schedule','training_subject_schedule.training_subject_schedule_id = training_subject.training_subject_schedule_id','left');
		$this->db->where('training_employee_database.employee_id',$employee_training->employee_id);
		$training_database_result = $this->db->get('training_employee_database');

		$total_training_cost = 0;

		if( $training_database_result->num_rows() > 0 ){

			foreach( $training_database_result->result() as $training_database_info ){

				$total_training_cost += $training_database_info->cost_per_pax;

			}

		}

		$employee_info = $this->db->get_where('employee',array('employee_id'=>$employee_training->employee_id))->row();

		$response->total_training_cost = number_format($total_training_cost,2);
		$response->total_running_balance = number_format($employee_info->total_training_running_balance,2);

		$this->load->view('template/ajax', array('json' => $response));

	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>