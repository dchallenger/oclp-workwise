<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Work_assignment extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists payroll accounts.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a payroll account';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a payroll account';

		$this->default_sort_col = array('employee_name');
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/work_assignment/listview';

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

/*			dbug($this->db->last_query());
			die();*/

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
							elseif ($detail['name'] == 'employee_id'){
								$cell[$cell_ctr] = $row['employee_name'];
								$cell_ctr++;
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

	function _set_listview_query( $listview_id = '', $view_actions = true ) {
		parent::_set_listview_query($listview_id, $view_actions);

		$this->listview_qry .= ',CONCAT (firstname," ",lastname) AS employee_name';		
	}	

	function _set_left_join()
	{
		$this->db->join('user', 'employee_work_assignment.employee_id = user.employee_id');

		parent::_set_left_join();
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

	function get_cost_code_division(){
		$response->cost_code = '';
		$this->db->where('deleted',0);
		$this->db->where('project_name_id',$this->input->post('project_name_id'));
		$result = $this->db->get('project_name');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->cost_code = $row->cost_code;			
			$response->division_id = $row->division_id;

			$this->db->where('deleted',0);
			$this->db->where('division_id', $row->division_id);
			$div_result = $this->db->get('user_company_division');
			if ($div_result && $div_result->num_rows() > 0){
				$div = $div_result->row();

				$response->div_code = $div->dvision_code;
			}
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}

	function get_division_cost_code(){
		$response->cost_code = '';
		$this->db->where('deleted',0);
		$this->db->where('division_id',$this->input->post('division_id'));
		$result = $this->db->get('user_company_division');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->cost_code = $row->dvision_code;			
			$response->division_id = $row->division_id;			
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}

	function get_department_code_group(){
		$response->cost_code = '';
		$this->db->where('deleted',0);
		$this->db->where('department_id',$this->input->post('department_id'));
		$result = $this->db->get('user_company_department');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();

			$this->db->where('deleted',0);
			$this->db->where('group_name_id', $row->group_name_id);
			$grp_result = $this->db->get('group_name');
			if ($grp_result && $grp_result->num_rows() > 0){
				$grp = $grp_result->row();

				$response->grp_code = $grp->group_code;
			}

			$response->group_name_id = $row->group_name_id;		
			$response->cost_code = $row->department_code;		
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}

	function get_group_cost_code(){
		$response->cost_code = '';
		$this->db->where('deleted',0);
		$this->db->where('group_name_id',$this->input->post('group_name_id'));
		$result = $this->db->get('group_name');
		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$response->group_code = $row->group_code;	
			$response->cost_code = $row->group_code;			
		}
		$this->load->view('template/ajax', array('json' => $response));		
	}	

	function cost_code()
	{

		$department_code = '';
		$group_code = '';
		$project_code = '';
		$division_code = '';

		if ($this->input->post('record_id') != '-1') {

			if ($this->input->post('department_id') != '') {
				$this->db->where('deleted',0);
				$this->db->where('department_id',$this->input->post('department_id'));
				$department = $this->db->get('user_company_department')->row();
				$department_code = $department->department_code;
			}

			if ($this->input->post('group_name_id') != '') {
				$this->db->where('deleted',0);
				$this->db->where('group_name_id', $this->input->post('group_name_id'));
				$group = $this->db->get('group_name')->row();
				$group_code = $group->group_code;
			}

			if ($this->input->post('project_name_id') != '') {
				$this->db->where('deleted',0);
				$this->db->where('project_name_id',$this->input->post('project_name_id'));
				$project = $this->db->get('project_name')->row();
				$project_code = $project->cost_code;
			}

			if ($this->input->post('division_id') != '') {
				$this->db->where('deleted',0);
				$this->db->where('division_id', $this->input->post('division_id'));
				$division = $this->db->get('user_company_division')->row();
				$division_code = $division->dvision_code;
			}

		}
		$response->division = '<div class="form-item even" style="display: block;">
			                        <label class="label-desc gray" for="cost_code_division">Cost Code/Job Order: </label>
			                        <div class="text-input-wrap">
			                       		<input type="text" class="input-text " id="cost_code_division" value="'.$division_code.'" readonly="readonly">
			                        </div>                                    
			                    </div>';
		$response->project = '<div class="form-item even" style="display: block;">
			                        <label class="label-desc gray" for="cost_code_project">Cost Code/Job Order: </label>
			                        <div class="text-input-wrap">
			                       		<input type="text" class="input-text" id="cost_code_project" value="'.$project_code.'" readonly="readonly">
			                        </div>                                    
			                    </div>';
		$response->group = '<div class="form-item even" style="display: block;">
			                        <label class="label-desc gray" for="cost_code_group">Cost Code/Job Order: </label>
			                        <div class="text-input-wrap">
			                       		<input type="text" class="input-text" id="cost_code_group" value="'.$group_code.'" readonly="readonly">
			                        </div>                                    
			                    </div>';
		$response->department = '<div class="form-item even" style="display: block;">
			                        <label class="label-desc gray" for="cost_code_department">Cost Code/Job Order: </label>
			                        <div class="text-input-wrap">
			                       		<input type="text" class="input-text" id="cost_code_department" value="'.$department_code.'" readonly="readonly">
			                        </div>                                    
			                    </div>';
			                    			                    			                 
		$this->load->view('template/ajax', array('json' => $response));		
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */