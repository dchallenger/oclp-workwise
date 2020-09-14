<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Group_worksched extends MY_Controller
{
	function __construct(){
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

		$dbprefix = $this->db->dbprefix;
		if(!($this->is_admin || $this->is_superadmin || $this->user_access[$this->module_id]['post'] || $this->user_access[$this->module_id]['publish'])) {
			$this->filter = $dbprefix.$this->module_table.'.supervisor_id = ' . $this->user->user_id;
		}
	}

	// START - default module functions
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
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
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

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

	function detail(){
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

	function edit(){
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

	function ajax_save(){
		if( $this->input->post('record_id') == '-1' ){
			$_POST['supervisor_id'] = $this->user->user_id;
		}
		else{
			unset( $_POST['supervisor_id'] );
		}

		$shift_calendar_id = $this->input->post('shift_calendar_id');
		$date_from = date('Y-m-d', strtotime($this->input->post('date_from')));
		$date_to = date('Y-m-d', strtotime($this->input->post('date_to')));
		$employees = $this->input->post('employee_id');
		$employees = explode( ',', $employees );
		$conflict = array();
		foreach( $employees  as $employee_id){
			$qry = "SELECT *
			FROM {$this->db->dbprefix}workschedule_employee
			WHERE deleted = 0 AND employee_id = '{$employee_id }' AND
			(
				( '{$date_from}' BETWEEN date_from AND date_to OR '{$date_to}' BETWEEN date_from AND date_to )
				OR
				( date_from BETWEEN '{$date_from}' AND '{$date_to}' OR date_to BETWEEN '{$date_from}' AND '{$date_to}' )
			)";

			$to_check = true;

			$result = $this->db->query( $qry );
			if( $result->num_rows() > 1 ){
				$user = $this->db->get_where('user', array('user_id' => $employee_id ))->row();
				$conflict[] = $user->firstname.' '.$user->lastname . ' has conflicting schedule.';
			}
			else if( $result->num_rows() == 1 ){
				$temp =  $result->row();
				if( $temp->group_schedule_id != $this->input->post('record_id') ){
					$user = $this->db->get_where('user', array('user_id' => $employee_id ))->row();
					$conflict[] = $user->firstname.' '.$user->lastname . ' has conflicting schedule.';	
				}
			}	
		}

		if ($this->config->client_no == 1){
	        if (date('Y-m-d') > date('Y-m-d',strtotime($date_to))){
	            if (!$this->system->check_in_current_cutoff($date_to)){
	            	$conflict[] = 'Your WS application is no longer within the allowable time to apply.';	
	            	$to_check = false;
	            }
	        }	
        }			

        if (date('Y-m-d') > date('Y-m-d',strtotime($date_to)) || date('Y-m-d') > date('Y-m-d',strtotime($date_from))){
        	$conflict[] = 'Your application exceeded the grace period.';	
        	$to_check = false;
        }

        if (date('Y-m-d',strtotime($date_to)) < date('Y-m-d',strtotime($date_from))){
        	$conflict[] = '"Date to" shouldn\'t be less than "Date from".';	
        	$to_check = false;
        }



		if( sizeof($conflict ) > 0 ){
			if ($to_check){
				$response->msg = implode('<br/>', $conflict).'<br/><br/>Please check individual work schedule.';
			}
			else{
				$response->msg = implode('<br/>', $conflict);				
			}
			$response->msg_type = "error";
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else{
			parent::ajax_save();

			//additional module save routine here
			//insert/update individual workschedule
			foreach( $employees  as $employee_id){
				$data = array(
					'group_schedule_id' => $this->key_field_val,
					'employee_id' => $employee_id,
					'shift_calendar_id' => $shift_calendar_id,
					'date_from' => $date_from,
					'date_to' => $date_to
				);
				
				//check if record exists
				$rec_exists = $this->db->get_where('workschedule_employee', array( 'group_schedule_id' => $this->key_field_val, 'employee_id' => $employee_id ));
				if( $rec_exists->num_rows() == 1 ){
					$this->db->update('workschedule_employee', $data, array( 'group_schedule_id' => $this->key_field_val, 'employee_id' => $employee_id ));	
				}
				else{
					//check if date and employee pair already exists
					$rec_exists = $this->db->get_where('workschedule_employee', array( 'date_from' => $date_from,  'date_to' => $date_to, 'employee_id' => $employee_id, 'deleted' => 0 ));
					if( $rec_exists->num_rows() == 1 ){
						$rec = $rec_exists->row();
						$this->db->update('workschedule_employee', $data, array( 'workschedule_id' => $rec->workschedule_id) );	
					}
					else{
						$this->db->insert('workschedule_employee', $data);
					}
				}
				
			}
		}
	}

	function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			$data['updated_by']   = $this->userinfo['user_id'];
			$data['updated_date'] = date('Y-m-d H:i:s');

			if ($this->input->post('record_id') == '-1') {
				$data['created_by']   = $this->userinfo['user_id'];
				$data['created_date'] = date('Y-m-d H:i:s');
			}

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $data);
		}

		parent::after_ajax_save();
	}

	function delete(){
		parent::delete();

		if($this->user_access[$this->module_id]['delete'] == 1){
			if($this->input->post('record_id')){
				$records = explode(',', $this->input->post('record_id'));
				foreach( $records as $record_id ){
					$group_sched = $this->db->get_where('workschedule_group', array($this->key_field => $record_id));
					if( $group_sched->num_rows() == 1 ){
						$group_sched = $group_sched->row();
						$employees = explode(',', $group_sched->employee_id);
						foreach( $employees as $employee_id ){
							$this->db->update('workschedule_employee', array('deleted' => 1), array( 'group_schedule_id' => $record_id, 'employee_id' => $employee_id ));
						}
					}
				}
			}
			else{
				$response->msg = "Insufficient data supplied.";
				$response->msg_type = 'attention';
			}
		}
		else{
			$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
		}

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	// END - default module functions

	// START custom module funtions

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
	    $sort_col = array();
	    foreach ($arr as $key=> $row) {
	        $sort_col[$key] = $row[$col];
	    }

	    array_multisort(array_map('strtolower',$sort_col), $dir, $arr);
	}

	/**
	 * Get the subordinate for the multi-select checkbox ui
	 * @return json
	 */
	function get_subordinates(){
		// Called from model uitype.
		if (is_null($this)) { 
			$ci =& get_instance();
			$emp = $ci->db->get_Where('employee', array('employee_id' => $ci->user->user_id ))->row(); 
			return new DummyRecordCollection($ci->hdicore->get_subordinates($ci->userinfo['position_id'], $emp->rank_id, $ci->user->user_id));
		}

		$response->select = '';

		$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row(); 

		$subs = $this->hdicore->get_subordinates( $this->userinfo['position_id'], $emp->rank_id, $this->user->user_id  );

		if( is_array( $subs  ) && sizeof( $subs  ) > 0 ){
			$optgroup = array();
			$department = array();
			$check_dup = array();

			$this->array_sort_by_column($subs, 'firstname');

			foreach( $subs as $sub ){
				if (!in_array($sub['employee_id'], $check_dup)){
					$optgroup[$sub['department_id']][] = '<option value="'.$sub['user_id'].'">'. $sub['firstname'] .' '. $sub['middleinitial'] .' '. $sub['lastname'] .' '. $sub['aux'] .'</option>';
					$department[ $sub['department_id'] ] = $sub['department'];
					$check_dup[] = $sub['employee_id'];
				}
			}

			foreach( $department as $id => $label ){
				$response->select .= '<optgroup label="'. $label .'">';
				if(isset($optgroup[$id]) && sizeof($optgroup[$id]) > 0){
					foreach( $optgroup[$id] as $option ){
						$response->select .= $option;
					}
				}
				$response->select .= '</optgroup>';
			}
		}

		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function send_email() {
		$this->db->where('workschedule_group.group_workschedule_id',$this->input->post('record_id'));
       	$request = $this->db->get('workschedule_group');
        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

				$const = eval(GROUP_WORKSCHED_EMAIL);
				foreach($const as $sample)
				{
	                $this->db->where('user_id', $sample);
	                $emailApprover=$this->db->get('user')->row_array();
	                $request['approver_user'] = $emailApprover['salutation']." ".$emailApprover['lastname'].", ".$request['approver_user'];
	                $recepients[] = $emailApprover['email'];
	            }

                $pieces=explode(',', $request['employee_id']);
                foreach($pieces as $emp_id)
                {
                	$emp=$this->db->get_where('user', array('employee_id' => $emp_id))->row_array();
                	$request['employees_affected'] = $emp['firstname']." ".$emp['middleinitial']." ".$emp['lastname'].", ".$request['employees_affected'];
                }

              	$request['date_from'] = date($this->config->item('display_date_format'), strtotime($request['date_from']));
              	$request['date_to'] = date($this->config->item('display_date_format'), strtotime($request['date_to']));
              	$shift_id = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $request['shift_calendar_id']))->row_array();
              	$request['new_sched'] = $shift_id['shift_calendar'];
                // $this->db->where('employee_id', );
                // $to_be_filled=$this->db->get('user')->row_array();

                $request['here']=base_url().'dtr/group_worksched/detail/'.$this->input->post('record_id');

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'GWSE');
                $message = $this->template->prep_message($template['body'], $request);

                // If queued successfully set the status to For Approval.
               	$this->template->queue(implode(',', $recepients), '', $template['subject'], $message);
            
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
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
	
	function _set_left_join() {
		parent::_set_left_join();

		$this->db->join('user', 'user.user_id = ' . $this->module_table . '.employee_id', 'left');
	}
	// END custom module funtions

}

class DummyRecordCollection
{
	private $_result;

	public function __construct($records)
	{
		foreach ($records as $record) {
			$this->_result[] = (object) $record;
		}
	}

	public function result()
	{
		return $this->_result;
	}
}
/* End of file */
/* Location: system/application */