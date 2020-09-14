<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal_template_position extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists appraisal templates.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an appraisal template.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an appraisal template.';

    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	if (!$this->input->post('record_id')){
			$this->session->set_flashdata('flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.');
            redirect(base_url() . 'admin/appraisal_template');    		
    	}

		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		// $data['content'] = 'listview';
		$data['content'] = 'admin/appraisal/appraisal_template_position/listview';

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

		$data['employee_appraisal_template_company_id'] = $this->input->post('record_id');
			
		if ($this->input->post('employee_appraisal_template_company_id') && $this->input->post('employee_appraisal_template_company_id') != "") {
			$data['employee_appraisal_template_company_id'] = $this->input->post('employee_appraisal_template_company_id');
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
		$data['views'] = array('admin/appraisal/appraisal_template_position/detailview');

		$record = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val ))->row();
		
		$data['employee_appraisal_template_company_id'] = $record->employee_appraisal_template_company_id; //$this->input->post('employee_appraisal_template_company_id');
		$this->db->join('employee_appraisal_template_company', 'employee_appraisal_template_company.company_id=user_company.company_id');
		$result = $this->db->get_where('user_company',array("employee_appraisal_template_company.employee_appraisal_template_company_id"=>$record->employee_appraisal_template_company_id));

			if ($result && $result->num_rows() > 0){
				$company_name = $result->row()->company;
				$data['company_id'] = $result->row()->company_id;

			}
		$data['company_name'] = $company_name;
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
			$data['content'] = 'admin/appraisal/appraisal_template_position/editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$data['employee_appraisal_template_company_id'] = $this->input->post('employee_appraisal_template_company_id');

			if ($this->input->post('record_id') != '-1') {
				$record = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val ))->row();
				$data['employee_appraisal_template_company_id'] = $record->employee_appraisal_template_company_id;
			}

			$this->db->join('employee_appraisal_template_company', 'employee_appraisal_template_company.company_id=user_company.company_id');
			$result = $this->db->get_where('user_company',array("employee_appraisal_template_company.employee_appraisal_template_company_id"=>$data['employee_appraisal_template_company_id']));

			if ($result && $result->num_rows() > 0){
				$company_name = $result->row()->company;
				$data['company_id'] = $result->row()->company_id;

			}

			$data['company_name'] = $company_name;
		
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
		
		if ($this->input->post('employee_appraisal_template_company_id')) {
			$this->db->where("employee_appraisal_template_position.employee_appraisal_template_company_id", $this->input->post('employee_appraisal_template_company_id'));
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
		// $result = $this->db->get();
		// $total_records = $result->num_rows();
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

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if ($this->input->post('employee_appraisal_template_company_id')) {
				$this->db->where("employee_appraisal_template_position.employee_appraisal_template_company_id", $this->input->post('employee_appraisal_template_company_id'));
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

	function ajax_save()
	{

		$this->db->where('position_id',$this->input->post('position_id'));
        $this->db->where_in('employment_status_id',$this->input->post('employment_status_id'));
        $this->db->where('employee_appraisal_template_position_id !=',$this->input->post('record_id'));

        $this->db->where('deleted',0);
        $result = $this->db->get('employee_appraisal_template_position');

		if($result && $result->num_rows > 0){
			$response->msg_type = 'error';
 			$response->msg 		= 'Duplicate entry is not allowed. / Appraisal Template Position is already created.';
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
		}
		else{
			parent::ajax_save();
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

	function get_department(){
		$this->db->where('company_id',$this->input->post('company_id'));
		$this->db->where('deleted',0);
		$department = $this->db->get('user_company_department')->result_array();		
        $html = '<option value=""></option>';
        foreach($department as $department_record){
            $html .= '<option value="'.$department_record["department_id"].'" '.($department_record["department_id"] == $this->input->post('department_id_selected') ? 'SELECTED' : '').'>'.$department_record["department"].'</option>';
        }

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);        
	}

	function get_position(){
		$this->db->where('company_id',$this->input->post('company_id'));
		$this->db->where('deleted',0);
		$position = $this->db->get('user_position')->result_array();		

        $html = '<option value=""></option>';
        foreach($position as $position_record){
        	$pos_code = ($position_record["position_code"] && $position_record["position_code"] != " ") ? ' - '.$position_record["position_code"] : '' ;
            $html .= '<option value="'.$position_record["position_id"].'" '.($position_record["position_id"] == $this->input->post('position_id_selected') ? 'SELECTED' : '').'>'.$position_record["position"].$pos_code.'</option>';
        }
        
        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);        
	}

	/**
	 * Return the view file for template criterias in json format.
	 * 	 
	 * @return json
	 */
	function get_template_criterias()
	{
		$record_id = $this->input->post('record_id');		

		if (!$record_id || $record_id <= 0) {
			$response->msg 		= 'Invalid ID.';
			$response->msg_type = 'error';			
		} else {
			$response->msg_type = 'success';

			$this->db->where($this->key_field, $record_id);
			$this->db->where('deleted', 0);			

			$criterias = $this->db->get('employee_appraisal_criteria');
			
			$response->html = $this->_get_criteria_html($criterias);			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	/**
	 * Return the view file for template criterias.
	 * 
	 * @param  object CI_DB_mysql_result $criterias
	 * @return string
	 */
	private function _get_criteria_html($criterias)
	{
		if (!$criterias || $criterias->num_rows() == 0) {
			return '';
		} else {
			$criterias = $criterias->result();
			$view = '';
			foreach ($criterias as $criteria) {
				$data['questions'] = array();
				$data['criteria']  = $criteria;

				$this->db->where('employee_appraisal_criteria_id', $criteria->employee_appraisal_criteria_id);
				$this->db->where('deleted', 0);

				$question_o = $this->db->get('employee_appraisal_criteria_question');
				
				if ($question_o->num_rows() > 0) {
					$data['questions'] = $question_o->result();
				}

				$view .= $this->load->view('employees/appraisal/criteria', $data, TRUE, FALSE);
			}
		}

		return $view;
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */