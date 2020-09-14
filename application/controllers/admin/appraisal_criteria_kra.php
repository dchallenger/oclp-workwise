<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal_criteria_kra extends MY_Controller
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
		
		// $sub = $this->hdicore->get_subordinates( $this->userinfo['position_id'], $$this->userinfo['user_id']);
		// dbug($sub);die();
		$this->filter = "employee_appraisal_criteria.deleted = 0 ";

		if (!$this->is_superadmin) {
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
			$this->_subordinate_id = array();

			$this->_subordinate_id[] = $this->userinfo['user_id'];

				foreach ($subordinates as $subordinate) {
					$this->_subordinate_id[] = $subordinate['user_id'];
				}

				$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_approver 
						   WHERE module_id = 145
						   AND approver_employee_id = ".$this->userinfo['user_id']." 
						   AND deleted = 0");

				if ($result && $result->num_rows() > 0){
					foreach ($result->result() as $row) {
						if (!in_array($row->employee_id, $this->_subordinate_id)){
							$this->_subordinate_id[] = $row->employee_id;
						}
					}
				}
			
			$this->filter .= " AND hr_user.user_id IN (".implode(',', $this->_subordinate_id).")"; // $this->db->where_in('user.user_id', );
				
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

	function detail()
	{
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

		$record = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();
		$this->db->order_by('employee_appraisal_criteria_question_id');
		$data['questions'] = $this->db->get_where('employee_appraisal_criteria_question', array('employee_appraisal_criteria_question_header_id' => $record->employee_appraisal_criteria_question_header_id, 'deleted' => 0))->result();
	

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
			$data['buttons'] = 'employees/appraisal/edit-buttons';
			
			//load variables to env
			if ($this->input->post('record_id') != '-1') {
				$record = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();

				$this->db->select('distribution as equal');
				$data['distribution'] = $this->db->get_where('employee_appraisal_criteria_question_header', array('employee_appraisal_criteria_question_header_id' => $record->employee_appraisal_criteria_question_header_id, 'deleted' => 0))->row();
				$data['questions'] = $this->db->get_where('employee_appraisal_criteria_question', array('employee_appraisal_criteria_question_header_id' => $record->employee_appraisal_criteria_question_header_id, 'deleted' => 0))->result();
			}

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
		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

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
							}elseif($detail['name'] == 'employee_id'){
								$cell[$cell_ctr++] = $row['firstname'] . ' ' . $row['lastname'];
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

	function ajax_save()
	{

		$distribution = $this->get_criteria($this->input->post('employee_appraisal_criteria_id'));
		
			if ($this->input->post('record_id') != "-1") {

				$qp = $this->input->post('old_question_percentage');
				$record = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')))->row();
				$header_id = $record->employee_appraisal_criteria_question_header_id;

				$percent = $this->_get_percentage($this->input->post('employee_appraisal_criteria_id'), $this->input->post('employee_id'), $header_id);
				$header_details = array('header' => $this->input->post('employee_appraisal_criteria_question_header_id'), 'distribution' => $this->input->post('distribution'), 'percentage' => $this->input->post('percentage'), 'employee_appraisal_criteria_id' => $this->input->post('employee_appraisal_criteria_id'));
				$this->db->where('employee_appraisal_criteria_question_header_id', $header_id);
				$this->db->update('employee_appraisal_criteria_question_header', $header_details);

			}else{
				$qp = $this->input->post('question_percentage');
				$percent = $this->_get_percentage($this->input->post('employee_appraisal_criteria_id'), $this->input->post('employee_id'));
			}
			
			$total_questions = array_sum($qp);

			$percentage = ($percent['percent'] + $this->input->post('percentage'));
			
			if ($percentage > 100)
			{
				$response->msg = 'Percentage exceeds 100% <br/><br/>';
				$response->msg .= implode('<br>', $percent['header']);
				$response->msg_type = 'error';
		        $data['json'] = $response;

				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);				
			}
			elseif (intval($total_questions) > intval(100))
			{
				$response->msg = 'The sum of Question Percentage must be equal to 100%<br/>';
				// $response->msg .= 'Percentage = '.$this->input->post('percentage') ;
				$response->msg .= 'Question Percentage (sum) = '.$total_questions ;
				$response->msg_type = 'error';
		        $data['json'] = $response;

				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
			}
			else
			{

				parent::ajax_save();
				if ($this->input->post('record_id') != "-1") {
					$this->db->where($this->key_field, $this->input->post('record_id'));
					$this->db->set('employee_appraisal_criteria_question_header_id', $header_id);
					$this->db->update($this->module_table);
				}
			
			}



		//additional module save routine here

	}

	function after_ajax_save()
	{
			$questions = $this->input->post('question');
			$tooltip = $this->input->post('tooltip');
			$percentage = $this->input->post('question_percentage');
			$details = array();
			$ids = array();

			$details['employee_appraisal_criteria_id'] = $this->input->post('employee_appraisal_criteria_id');

			

			if ($this->input->post('record_id') == "-1") {
				$header_details = array('header' => $this->input->post('employee_appraisal_criteria_question_header_id'), 'distribution' => $this->input->post('distribution'), 'percentage' => $this->input->post('percentage'), 'employee_appraisal_criteria_id' => $this->input->post('employee_appraisal_criteria_id'));

				$this->db->insert('employee_appraisal_criteria_question_header', $header_details);
				$header_id = $this->db->insert_id();
				
			}else{
				// $record = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();
				$header_id = $this->input->post('header_id');
			
			}


			$details['employee_appraisal_criteria_question_header_id'] = $header_id;

			if ($this->input->post('old_question')) {
				$percentage_old = $this->input->post('old_question_percentage');
				$tooltip_old = $this->input->post('old_tooltip');

				foreach ($this->input->post('old_question') as $key => $q) {
					$details['question'] = $q;
					$details['tooltip'] = $tooltip_old[$key];
					$details['question_percentage'] = $percentage_old[$key];

					$this->db->where('employee_appraisal_criteria_question_id', $key);
					$this->db->update('employee_appraisal_criteria_question', $details);

					$ids[] = $key;
				}

				if (count($questions) > 0) {
					foreach ($questions as $key => $question) {
					
					$details['question'] = $question;
					$details['tooltip'] = $tooltip[$key];

						$this->db->insert('employee_appraisal_criteria_question', $details);
					}
				}
			}

			if ($this->input->post('del_question')) {
				foreach ($this->input->post('del_question') as $q_id => $value) {
					$this->db->where('employee_appraisal_criteria_question_id', $q_id);
					$this->db->set('deleted', '1');
					$this->db->update('employee_appraisal_criteria_question');

				}
				
			}
		
			if ($this->input->post('record_id') == '-1' && !$this->input->post('old_question')) {
				foreach ($questions as $key => $question) {
					
					$details['question'] = $question;
					$details['tooltip'] = $tooltip[$key];
					$details['question_percentage'] = $percentage[$key];
					$this->db->insert('employee_appraisal_criteria_question', $details);
					$ids[] = $this->db->insert_id();
					}
			}
			// dbug($this->key_field_val);die();
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->set('employee_appraisal_criteria_question_id', implode(',', $ids));
			if ($this->input->post('record_id') == "-1") {
				$this->db->set('employee_appraisal_criteria_question_header_id', $header_id);
			}
			$this->db->update($this->module_table);

		parent::after_ajax_save();
	}


	private function _get_percentage( $criteria_id, $employee, $question_id = null)
	{
	
		$this->db->where('employee_appraisal_criteria_question_header.employee_appraisal_criteria_id',$criteria_id);

		if ($question_id != null) {
			$this->db->where('employee_appraisal_criteria_question_header.employee_appraisal_criteria_question_header_id != ',$question_id);
		}
		$this->db->where('employee_id', $employee);
		$this->db->where('employee_appraisal_criteria_question_header.deleted',0);
		$this->db->where('employee_appraisal_criteria_kra.deleted',0);
		$this->db->join('employee_appraisal_criteria_kra', 'employee_appraisal_criteria_question_header.employee_appraisal_criteria_question_header_id = employee_appraisal_criteria_kra.employee_appraisal_criteria_question_header_id');
		$criterias = $this->db->get('employee_appraisal_criteria_question_header')->result();
		
		$percentage = array();
		$header = array();

		foreach ($criterias as $key => $criteria) {
			$percentage[] = $criteria->percentage;
			$header[] = $criteria->header.' ('.$criteria->percentage.'%)';
		}

		$result['percent'] = array_sum($percentage);
		$result['header'] = $header;

		return $result;

	}

	function get_criteria( $criteria_id = null, $is_ajax = false)
	{
		if ($criteria_id == null) {
			$criteria_id = $this->input->post('criteria');
			$is_ajax = true;
		}

		$criteria = $this->db->get_where('employee_appraisal_criteria', array('employee_appraisal_criteria_id' => $criteria_id))->row();
		$equal = 'no';
		if ($criteria->individual == 1) {
			$equal = 'yes';
		}

		if ($is_ajax) {
			$response->individual = $equal;
	        $data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}else{
			return $equal;
		}
		
	}

	function get_header()
	{
		$header_id = $this->input->post('header');
		$this->db->where('employee_appraisal_criteria_question_header_id', $header_id);
		$header = $this->db->get('employee_appraisal_criteria_question_header')->row();

		$response->header = $header->header;
		$response->percent = $header->percentage;
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function _append_to_select()
	{
		//$this->listview_qry .= ', employee_appraisal.employee_id, user.position_id';
		$this->listview_qry .= ', user.firstname';
		$this->listview_qry .= ', user.lastname';

	}

	function _set_left_join()
	{
		$this->db->join('user', 'employee_appraisal_criteria_kra.employee_id = user.employee_id');
		$this->db->join('employee_appraisal_criteria', 'employee_appraisal_criteria_kra.employee_appraisal_criteria_id = employee_appraisal_criteria.employee_appraisal_criteria_id');

		parent::_set_left_join();
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	// END custom module funtions

}

/* End of file */
/* Location: system/application */