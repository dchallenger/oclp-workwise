<?php

include (APPPATH . 'controllers/employees.php');

class Firstbalfour_employees extends Employees
{
	function __construct() {
		parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array('employee' => 'employee_id'); //table => field format

		$this->listview_title         = $this->module_name;
		$this->listview_description   = $this->module_name;
		$this->jqgrid_title           = "";
		$this->detailview_title       = $this->module_name;
		$this->detailview_description = $this->module_name;
		$this->editview_title         = 'Add/Edit Employee';
		$this->editview_description   = '';
		$this->_detail_types          = array('education', 'employment', 'family', 'references', 'training', 'affiliates', 'skill','test_profile', 'otherinfo1', 'insurance','accountabilities','attachment');		

		$this->load->helper('form');

		$dbprefix = $this->db->dbprefix;
		$this->filter = $dbprefix."user.inactive = 0 AND ".$dbprefix."employee.resigned = 0";
		if( $this->input->post('filter') && $this->input->post('filter') == "inactive" ) $this->filter = $dbprefix."user.inactive = 1 AND ".$dbprefix."employee.resigned = 0";;
		if( $this->input->post('filter') && $this->input->post('filter') == "resigned" ) $this->filter = $dbprefix."employee.resigned = 1";

		if($this->config->item('with_floating') == 1)
		{
			$result = $this->hdicore->get_all_floating(date('Y-m-d'));

			if($result && count($result) > 0)
			{
				$floating = implode(',',$result);
				if( $this->input->post('filter') && $this->input->post('filter') == "floating" ) 
					$this->filter = $dbprefix."user.employee_id IN (".$floating.")";
			}
		}

		if( $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['publish'] != 1 && $this->user_access[$this->module_id]['project_hr'] != 1){
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
			$subordinate_id = array(0);
			if( count($subordinates) > 0 ){

				$subordinate_id = array();

				foreach ($subordinates as $subordinate) {
						$subordinate_id[] = $subordinate['user_id'];
				}
			}
			$subordinate_list = implode(',', $subordinate_id);
			if( $subordinate_list != "" )
				$this->filter .= ' AND '. $dbprefix.'user.employee_id IN ('.$subordinate_list.')';
			else
				$this->filter .= ' AND '. $dbprefix.'user.employee_id IN (0)';
		}

		if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
			$subordinate_id = array();
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->system->get_subordinates_by_project($emp->employee_id);
			$subordinate_id = array(0);
			if( count($subordinates) > 0 ){

				$subordinate_id = array();

				foreach ($subordinates as $subordinate) {
						$subordinate_id[] = $subordinate['employee_id'];
				}
			}		

			$subordinate_list = implode(',', $subordinate_id);
			if( $subordinate_list != "" || $subordinate_list != 0){
				$this->filter .= ' AND '. $dbprefix.'user.employee_id IN ('.$subordinate_list.')';
			}
			else{
				if ($subordinates == false ) {
					$this->filter .= ' AND '. $dbprefix.'user.employee_id IN (0)';
				}
				
			}
		}	
	}
	
	// START - default module functions
	// default jqgrid controller method
	function index($holder = false) {

		if( $this->user_access[$this->module_id]['list'] != 1 ){
			$this->my201();
		}
		else{

			$data['scripts'][] = multiselect_script();
			$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
			$data['content'] = 'listview';
			$data['scripts'][] = chosen_script();
			$data['additional_search_options'] = array("department"=>"Department","group_name"=>"Group","division"=>"Division","project_name"=>"Project");				
			
			if ($this->session->flashdata('flashdata')) {

				$info['flashdata'] = $this->session->flashdata('flashdata');
				$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
			}

			//set default columnlist
			$this->_set_listview_query();
	
			//set grid buttons
			$data['jqg_buttons'] = $this->_default_grid_buttons();
	
			//set load jqgrid loadComplete callback
			$data['jqgrid_loadComplete'] = 'init_filter_tabs();';
			
			$tabs = array();
			$tabs[] = '<li class="active" filter="active"><a href="javascript:void(0)">Active</li>';
            $tabs[] = '<li filter="inactive"><a href="javascript:void(0)">Inactive</li>';
            $tabs[] = '<li filter="resigned"><a href="javascript:void(0)">Resigned</li>';
            if($this->config->item('with_floating') == 1)
            	$tabs[] = '<li filter="floating"><a href="javascript:void(0)">Floating</li>';
            if( sizeof( $tabs ) > 1 ) $data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
			
			//load variables to env
			$this->load->vars($data);
			
			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');
	
			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
			
		}
	}

	/* START List View Functions */
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
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions ); 
		
		$this->listview_qry .= ',department, division,group_name,project_name';	

		$arr_lvq = explode(',', $this->listview_qry);
		$arr_lvq_count = count($arr_lvq) - 4;

		$arr = array();
		for ($i=0; $i < $arr_lvq_count; $i++) { 
			$field = $arr_lvq[$i];
			preg_match('/(?<= AS )\S+/i', $field, $match);	
			$new_field = $match[0];

			if ($new_field != ''){
				$arr[] = $new_field;
			}
			else{
				$arr[] = $field;
			}
		}

		$groupby = implode(',', $arr);

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

		$this->db->join('employee_work_assignment', 'employee_work_assignment.employee_id='. $this->module_table .'.employee_id','left');							
		$this->db->join('user_company_department', 'user_company_department.department_id=employee_work_assignment.department_id','left');				
		$this->db->join('user_company_division', 'user_company_division.division_id=employee_work_assignment.division_id','left');				
		$this->db->join('group_name', 'group_name.group_name_id=employee_work_assignment.group_name_id','left');				
		$this->db->join('project_name', 'project_name.project_name_id=employee_work_assignment.project_name_id','left');				

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
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

		$this->db->group_by($groupby,TRUE);
		
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

			$this->db->join('employee_work_assignment', 'employee_work_assignment.employee_id='. $this->module_table .'.employee_id','left');							
			$this->db->join('user_company_department', 'user_company_department.department_id=employee_work_assignment.department_id','left');				
			$this->db->join('user_company_division', 'user_company_division.division_id=employee_work_assignment.division_id','left');				
			$this->db->join('group_name', 'group_name.group_name_id=employee_work_assignment.group_name_id','left');				
			$this->db->join('project_name', 'project_name.project_name_id=employee_work_assignment.project_name_id','left');

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

			$this->db->group_by($groupby,TRUE);

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
	
	function get_query_fields2($record_id = 0) {
		if ($record_id == 0) {
			$record_id = $this->input->post('export_query_id');
		}

		$this->db->where('parent_module_id', $record_id);
		$this->db->where('deleted', 0);
		$result = $this->db->get('export_query');

		if ($result->num_rows() > 0) {
			$export_query_id = $result->row()->export_query_id;
		}

		$this->db->where('export_query_id', $export_query_id);
		$this->db->limit(1);
		$export_query = $this->db->get('export_query')->row_array();		

		$this->db->where('export_query_id', $export_query_id);
		$this->db->order_by('field','asc');
		$result = $this->db->get('export_query_fields')->result();

		$fields = array();
		foreach ($result as $field) {
			$fields[$field->field] = $field->field;
		}

		$this->db->select('user.firstname');
		$this->db->select('user.lastname');
		$this->db->select('user.user_id');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');


		if( $this->input->post('criteria') == 'active' ){
			$this->db->where('user.inactive',0);
			$this->db->where('employee.resigned',0);
		}
		elseif( $this->input->post('criteria') == 'inactive' ){
			$this->db->where('user.inactive',1);
			$this->db->where('employee.resigned',0);
		}
		elseif( $this->input->post('criteria') == 'resigned' ){
			// $this->db->where('user.inactive',0);
			$this->db->where('employee.resigned',1);
		}
		$this->db->where($this->filter);
		$this->db->where('employee.deleted', 0); // exclude deleted employees on dropdown
		$this->db->order_by('user.firstname','ASC');
		$user_list = $this->db->get('user');

		if (!IS_AJAX) {
			return 	array(					
					'fields' 		 => $fields,
					'description'    => $export_query['description'],
					'export_query_id' => $export_query['export_query_id']
				);
		} else {
			$data['html'] = $this->load->view(
								'employees/export_fields_employees', //added export_fields_employees.php to filter all employees for export in all clients
								array(
									'fields' 		  => $fields, 
									'description'     => ucfirst($this->input->post('criteria')),
									'export_query_id' => $export_query['export_query_id'],
									'users'           => $user_list->result(),
									'total_users'     => $user_list->num_rows()
									), 
								TRUE
							);

			$this->load->view('template/ajax', array('json' => $data));
		}
	}
}


?>