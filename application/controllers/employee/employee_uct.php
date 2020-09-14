<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_uct extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Unit Communication Tree';
		$this->listview_description = 'This module lists all defined unit communication tree(s).';
		$this->jqgrid_title = "Department List";
		$this->detailview_title = 'Unit Communication Tree Info';
		$this->detailview_description = 'This page shows detailed information about a particular unit communication tree.';
		$this->editview_title = 'Unit Communication Tree Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about unit communication tree(s).';
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
			$data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			$data['scripts'][] = chosen_script();
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
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
		
		if ($this->input->post('record_id') > 0 && $this->input->post('record_id') != -1) {
			$record_id = $this->input->post('record_id');
		}else{
			$record_id = $this->key_field_val;
		}

		$alternate_contact = implode(',',$this->input->post('alternate_contact'));
		$this->db->update('employee_uct',array('primary_contact'=>$this->input->post('primary_contact'), 'alternate_contact'=>$alternate_contact), array($this->key_field => $record_id));
		

		//additional module save routine here
				
	}

	function after_ajax_save()
	{	
		if( $this->input->post('transaction_id') ){
			$this->db->delete('unit communication tree_account_mapping', array('unit communication tree_id' => $this->key_field_val));
			$transactions = $this->input->post('transaction_id');
			$debit = $this->input->post('debit_account_id');
			$credit = $this->input->post('credit_account_id');
			$transaction_label_override = $this->input->post('transaction_label_override');
			foreach($transactions as $transaction_id => $value){
				$mapping = array(
					'unit communication tree_id' => $this->key_field_val,
					'transaction_id' => $transaction_id,
					'credit_account_id' => $credit[$transaction_id],
					'debit_account_id' => $debit[$transaction_id],
					'transaction_label_override' => $transaction_label_override[$transaction_id]
				);
				$this->db->insert('unit communication tree_account_mapping', $mapping);
			}
		}

		parent::after_ajax_save();				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
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
		$this->db->select(' CONCAT(up.firstname," ",up.lastname) as primary_contact, CONCAT(ua.firstname," ",ua.lastname) as alternate_contact',false);
		$this->db->from($this->module_table);
		$this->db->join('user up','up.employee_id = '.$this->module_table.'.primary_contact','left');
		$this->db->join('user ua','ua.employee_id = '.$this->module_table.'.alternate_contact','left');
		$this->db->where($this->module_table.'.deleted = 0 AND up.inactive = 0 AND '.$search);

		if( $this->user_access[$this->module_id]['post'] != 1 ){
			$department_id = explode(',',$this->userinfo['department_id']);
			$this->db->where_in('employee_uct.department_id',$department_id);
		}

		$this->db->or_where('( ('.$this->db->dbprefix($this->module_table).'.primary_contact = '.$this->userinfo['user_id'].') 
				|| ( ( '.$this->db->dbprefix($this->module_table).'.alternate_contact LIKE "'.$this->userinfo['user_id'].'" ) 
					|| ( '.$this->db->dbprefix($this->module_table).'.alternate_contact LIKE "%,'.$this->userinfo['user_id'].'%" ) 
					|| ( '.$this->db->dbprefix($this->module_table).'.alternate_contact LIKE "%'.$this->userinfo['user_id'].',%" ) 
					|| ( '.$this->db->dbprefix($this->module_table).'.alternate_contact LIKE "%,'.$this->userinfo['user_id'].',%" ) ) )');

		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}
		$this->db->group_by($this->module_table.'.department_id,'.$this->module_table.'.primary_contact');
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
			$this->db->select(' CONCAT(up.firstname," ",up.lastname) as primary_contact, CONCAT(ua.firstname," ",ua.lastname) as alternate_contact',false);
			$this->db->from($this->module_table);
			$this->db->join('user up','up.employee_id = '.$this->module_table.'.primary_contact','left');
			$this->db->join('user ua','ua.employee_id = '.$this->module_table.'.alternate_contact','left');
			$this->db->where($this->module_table.'.deleted = 0 AND up.inactive = 0 AND '.$search);

			if( $this->user_access[$this->module_id]['post'] != 1 ){
				$department_id = explode(',',$this->userinfo['department_id']);
				$this->db->where_in('employee_uct.department_id',$department_id);
			}

			$this->db->or_where('( ('.$this->db->dbprefix($this->module_table).'.primary_contact = '.$this->userinfo['user_id'].') 
				|| ( ( '.$this->db->dbprefix($this->module_table).'.alternate_contact LIKE "'.$this->userinfo['user_id'].'" ) 
					|| ( '.$this->db->dbprefix($this->module_table).'.alternate_contact LIKE "%,'.$this->userinfo['user_id'].'%" ) 
					|| ( '.$this->db->dbprefix($this->module_table).'.alternate_contact LIKE "%'.$this->userinfo['user_id'].',%" ) 
					|| ( '.$this->db->dbprefix($this->module_table).'.alternate_contact LIKE "%,'.$this->userinfo['user_id'].',%" ) ) )');


			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			
			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}

			if (method_exists($this, '_custom_join')) {
				// Append fields to the SELECT statement via $this->listview_qry
				$this->_custom_join();
			}
			$this->db->group_by($this->module_table.'.department_id,'.$this->module_table.'.primary_contact');
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
							elseif( $detail['name'] == 'primary_contact' ){

								$cell[$cell_ctr] = $row[$detail['name']];
								$cell_ctr++;

							}
							elseif( $detail['name'] == 'alternate_contact' ){

								$cell[$cell_ctr] = $row[$detail['name']];
								$cell_ctr++;

							}
							else{
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

	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{

			if ($search['column'] == $this->db->dbprefix.'employee_uct.primary_contact'){
				$search['column'] = 'CONCAT(up.firstname, " ", up.lastname)';
			}

			
			if ($search['column'] == $this->db->dbprefix.'employee_uct.alternate_contact'){
				$search['column'] = 'CONCAT(ua.firstname, " ", ua.lastname)';
			}


			$column = strtolower( $search['column'] );
			if(sizeof(explode(' as ', $column)) > 1){
				$as_part = explode(' as ', $column);
				$search['column'] = strtolower( trim( $as_part[0] ) );
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
		}
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function _set_specific_search_query()
	{
		$field = $this->input->post('searchField');
		$operator =  $this->input->post('searchOper');
		$value =  $this->input->post('searchString');

		if ($this->input->post('searchField') == 'employee_uct.primary_contact'){
			$field = 'CONCAT(up.firstname, " ", up.lastname)';
		}

		
		if ($this->input->post('searchField') == 'employee_uct.alternate_contact'){
			$field = 'CONCAT(ua.firstname, " ", ua.lastname)';
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

	function update_department_field(){

		if( $this->input->post('company_id') ){
			$this->db->where('company_id',$this->input->post('company_id'));
		}

		$result = $this->db->get('user_company_department')->result();

		$html = '';
		$html .= '<option value=" "> </option>';

		foreach( $result as $record ){

			if( $this->input->post('department_id') && $this->input->post('department_id') == $record->department_id  ){
				$html .= '<option value="'.$record->department_id.'" selected>'.$record->department.'</option>';
			}
			else{
				$html .= '<option value="'.$record->department_id.'">'.$record->department.'</option>';
			}


		}

		$data['html'] = $html;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

	}

	function get_employee_data(){

		$employee_id = $this->input->post('employee_id');
		$info_type = $this->input->post('info_type');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('cities','cities.city_id = employee.pres_city','left');
		$this->db->where('user.employee_id',$employee_id);
		$result = $this->db->get('user');

		if( $result->num_rows() > 0 ){

			$record = $result->row();
			$response->employee_name = $record->firstname." ".$record->lastname;
			$response->primary_no = $record->mobile;
			$response->alternate_no = $record->home_phone;
			$response->address1 = $record->pres_address1;
			$response->address2 = $record->pres_address2;
			$response->city = $record->city;
			$response->province = $record->pres_province;

		}
		else{

			$response->employee_name = "";
			$response->primary_no = "";
			$response->alternate_no = "";
			$response->address1 = "";
			$response->address2 = "";
			$response->city = "";
			$response->province = "";

			$response->msg = "No record found.";
	        $response->msg_type = "error";
	        $data['json'] = $response;

	        $this->load->view('template/ajax', array('json' => $response));

		}

		$this->load->view('template/ajax', array('json' => $response));

	}

	function get_employee_contact_ids(){

		$record_id = $this->input->post('id');

		$result = $this->db->get_where('employee_uct',array('employee_uct_id'=>$record_id))->row();

		$response->primary_contact = $result->primary_contact;
		$response->alternate_contact = $result->alternate_contact;

		$this->load->view('template/ajax', array('json' => $response));

	}

	function get_other_contact(){

		$html = "";
		$record_id = $this->input->post('record_id');

		$get_uct = $this->db->get_where('employee_uct',array('employee_uct_id'=>$record_id))->row();

		$primary_contact = $get_uct->primary_contact;
		$alternate_contact = explode(',',$get_uct->alternate_contact);

		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('cities','cities.city_id = employee.pres_city','left');
		$this->db->where('user.inactive',0);
		$this->db->where('employee.resigned',0);
		$this->db->where('user.department_id',$get_uct->department_id);
		$this->db->where_not_in('user.user_id',$primary_contact);
		$this->db->where_not_in('user.user_id',$alternate_contact);
		$result = $this->db->get('user');

		if( $result->num_rows() > 0 ){

			foreach( $result->result() as $record ){

				$html.='<div class="col-2-form view">     
				    <div class="form-item view odd ">
				        <label class="label-desc view gray" for="primary_contact_employee_name">Employee Name:</label>
				        <div class="text-input-wrap">'.$record->firstname.' '.$record->lastname.'</div>		
				    </div>	

				    <div class="form-item view odd ">
				        <label class="label-desc view gray" for="primary_contact_primary_no">Primary Contact Number:</label>
				        <div class="text-input-wrap">'.$record->mobile.'</div>		
				    </div>	

				    <div class="form-item view odd ">
				        <label class="label-desc view gray" for="primary_contact_alternate_no">Alternate Contact Number:</label>
				        <div class="text-input-wrap">'.$record->home_phone.'</div>		
				    </div>	

				    <div class="form-item view odd ">
				        <label class="label-desc view gray" for="primary_contact_address1">Address 1:</label>
				        <div class="text-input-wrap">'.$record->pres_address1.'</div>		
				    </div>	

				    <div class="form-item view odd ">
				        <label class="label-desc view gray" for="primary_contact_address2">Address 2:</label>
				        <div class="text-input-wrap">'.$record->pres_address2.'</div>		
				    </div>	

				    <div class="form-item view odd ">
				        <label class="label-desc view gray" for="primary_contact_city">City:</label>
				        <div class="text-input-wrap">'.$record->city.'</div>		
				    </div>	

				    <div class="form-item view odd ">
				        <label class="label-desc view gray" for="primary_contact_province">Province:</label>
				        <div class="text-input-wrap">'.$record->pres_province.'</div>		
				    </div>	
				</div><br /><br />';
			}


		}
		
		$data['html'] = $html;
 		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

	}

	function get_alternate_contact(){

		if($this->input->post('view') == 'edit'){


			$alternate_contact = explode(',',$this->input->post('alternate_contact'));

			$record_id = $this->input->post('record_id');

			if ($record_id > 0) {
				$get_uct = $this->db->get_where('employee_uct',array('employee_uct_id'=>$record_id))->row();

				$this->db->where('primary_contact', $get_uct->primary_contact);
				$this->db->where('department_id', $get_uct->department_id);
				$this->db->where('company_id', $get_uct->company_id);
				$this->db->where('employee_uct_id != ', $get_uct->employee_uct_id);
				$other_alternate = $this->db->get('employee_uct');

				if ($other_alternate && $other_alternate->num_rows() > 0) {
					$alternates = array();

					foreach ($other_alternate->result() as $alternate) { 
						
						$alternate_ids =  explode(',', $alternate->alternate_contact);
						foreach ($alternate_ids as $key => $value) {
								$alternates[] = $value;
						}
					
					}

					$alternate_contacts = array_merge($alternate_contact, $alternates);

					$alternate_contact = array_unique($alternate_contacts);
				}		
			}


			$contact_alternate = array();


			$html = "";
			

			if( count($alternate_contact) > 0 && $this->input->post('alternate_contact') != " "){

				foreach( $alternate_contact as $alternate_contact_id ){

					$this->db->join('employee','employee.employee_id = user.employee_id','left');
					$this->db->join('cities','cities.city_id = employee.pres_city','left');
					$this->db->where('user.inactive',0);
					$this->db->where('employee.resigned',0);
					$this->db->where('user.user_id = '.$alternate_contact_id);
					$result = $this->db->get('user');

					if( $result->num_rows() > 0 ){

						$record = $result->row();

						$html.='<div class="col-2-form">  

						    <div class="form-item odd ">
		                        <label class="label-desc gray" for="primary_contact_employee_name">Employee Name:</label>
		                        <div class="text-input-wrap">
		                        	<input type="text" readonly="readonly" class="input-text" value="'.$record->firstname.' '.$record->lastname.'" id="primary_contact_employee_name" name="primary_contact_employee_name">
		                        </div>                                    
		                    </div>

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_primary_no">Primary Contact Number:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="'.$record->mobile.'" id="primary_contact_primary_no" name="primary_contact_primary_no">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_alternate_no">Alternate Contact Number:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="'.$record->home_phone.'" id="primary_contact_alternate_no" name="primary_contact_alternate_no">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_address1">Address 1:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="'.$record->pres_address1.'" id="primary_contact_address1" name="primary_contact_address1">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_address2">Address 2:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="'.$record->pres_address2.'" id="primary_contact_address2" name="primary_contact_address2">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_city">City:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="'.$record->city.'" id="primary_contact_city" name="primary_contact_city">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_province">Province:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="'.$record->pres_province.'" id="primary_contact_province" name="primary_contact_province">
						        </div>		
						    </div>	
						</div><br /><br />';


					}
					else{

						$html.='<div class="col-2-form">  

						    <div class="form-item odd ">
				                <label class="label-desc gray" for="primary_contact_employee_name">Employee Name:</label>
				                <div class="text-input-wrap">
				                	<input type="text" readonly="readonly" class="input-text" value="" id="primary_contact_employee_name" name="primary_contact_employee_name">
				                </div>                                    
				            </div>

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_primary_no">Primary Contact Number:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="" id="primary_contact_primary_no" name="primary_contact_primary_no">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_alternate_no">Alternate Contact Number:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="" id="primary_contact_alternate_no" name="primary_contact_alternate_no">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_address1">Address 1:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="" id="primary_contact_address1" name="primary_contact_address1">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_address2">Address 2:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="" id="primary_contact_address2" name="primary_contact_address2">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_city">City:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="" id="primary_contact_city" name="primary_contact_city">
						        </div>		
						    </div>	

						    <div class="form-item odd ">
						        <label class="label-desc gray" for="primary_contact_province">Province:</label>
						        <div class="text-input-wrap">
						        	<input type="text" readonly="readonly" class="input-text" value="" id="primary_contact_province" name="primary_contact_province">
						        </div>		
						    </div>	
						</div><br /><br />';

					}

				}

			}

		}
		else{

			$html = "";
			$record_id = $this->input->post('record_id');
			$get_uct = $this->db->get_where('employee_uct',array('employee_uct_id'=>$record_id))->row();

			$alternate_contact = explode(',',$get_uct->alternate_contact);

			$this->db->where('primary_contact', $get_uct->primary_contact);
			$this->db->where('department_id', $get_uct->department_id);
			$this->db->where('company_id', $get_uct->company_id);
			$this->db->where('employee_uct_id != ', $get_uct->employee_uct_id);
			$other_alternate = $this->db->get('employee_uct');

			if ($other_alternate && $other_alternate->num_rows() > 0) {
				$alternates = array();

				foreach ($other_alternate->result() as $alternate) { 
					
					$alternate_ids =  explode(',', $alternate->alternate_contact);
					foreach ($alternate_ids as $key => $value) {
							$alternates[] = $value;
					}
				
				}

				$alternate_contacts = array_merge($alternate_contact, $alternates);

				$alternate_contact = array_unique($alternate_contacts);
			}

			$contact_alternate = array();

			if( count($alternate_contact) > 0 ){
				
				foreach( $alternate_contact as $alternate_contact_id ){

					$this->db->join('employee','employee.employee_id = user.employee_id','left');
					$this->db->join('cities','cities.city_id = employee.pres_city','left');
					$this->db->where('user.inactive',0);
					$this->db->where('employee.resigned',0);
					$this->db->where('user.user_id = '.$alternate_contact_id);
					$result = $this->db->get('user');

					if( $result->num_rows() > 0 ){

						$record = $result->row();

						$contact_alternate[] = $record->firstname.' '.$record->lastname;

						$html.='<div class="col-2-form">  

						    <div class="form-item view odd ">
		                        <label class="label-desc view gray" for="primary_contact_employee_name">Employee Name:</label>
		                        <div class="text-input-wrap">
		                        	'.$record->firstname.' '.$record->lastname.'
		                        </div>                                    
		                    </div>

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_primary_no">Primary Contact Number:</label>
						        <div class="text-input-wrap">
						        	'.$record->mobile.'
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_alternate_no">Alternate Contact Number:</label>
						        <div class="text-input-wrap">
						        	'.$record->home_phone.'
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_address1">Address 1:</label>
						        <div class="text-input-wrap">
						        	'.$record->pres_address1.'
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_address2">Address 2:</label>
						        <div class="text-input-wrap">
						        	'.$record->pres_address2.'
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_city">City:</label>
						        <div class="text-input-wrap">
						        	'.$record->city.'
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_province">Province:</label>
						        <div class="text-input-wrap">
						        	'.$record->pres_province.'
						        </div>		
						    </div>	
						</div><br /><br />';


					}
					else{

						$html.='<div class="col-2-form">  

						    <div class="form-item view odd ">
				                <label class="label-desc view gray" for="primary_contact_employee_name">Employee Name:</label>
				                <div class="text-input-wrap">
				                </div>                                    
				            </div>

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_primary_no">Primary Contact Number:</label>
						        <div class="text-input-wrap">
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_alternate_no">Alternate Contact Number:</label>
						        <div class="text-input-wrap">
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_address1">Address 1:</label>
						        <div class="text-input-wrap">
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_address2">Address 2:</label>
						        <div class="text-input-wrap">
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_city">City:</label>
						        <div class="text-input-wrap">
						        </div>		
						    </div>	

						    <div class="form-item view odd ">
						        <label class="label-desc view gray" for="primary_contact_province">Province:</label>
						        <div class="text-input-wrap">
						        </div>		
						    </div>	
						</div><br /><br />';

					}

				}

			}


		}

		$contacts = implode(', ', $contact_alternate);

		$response->html = $html;
		$response->alternate = $contacts;
 		$data['json'] = $response;
	    $this->load->view('template/ajax', array('json' => $response));


	}

	function update_primary_alternate_option(){

		$sql = "SELECT * 
		FROM hr_user
		LEFT JOIN hr_employee ON hr_employee.employee_id = hr_user.employee_id
		WHERE hr_employee.resigned = 0 AND hr_user.inactive = 0  
		AND ISNULL( hr_employee.resigned_date )
		-- AND company_id = {$this->input->post('company_id')}
		-- AND department_id = {$this->input->post('department_id')}";
		$list_result = $this->db->query($sql);

		$primary_option_html = "<option value=' '> </option>";
		$alternate_option_html = "<option value=' '> </option>";

		$primary_contact = $this->input->post('primary_contact');
		$department = $this->input->post('department_id');
		$company = $this->input->post('company_id');
		$alternate_contact = explode(',',$this->input->post('alternate_contact'));

		if ($this->input->post('alternate_contact') != "null" && $this->input->post('record_id') > 0) {
			$this->db->where('primary_contact', $primary_contact);
			$this->db->where('department_id', $department);
			$this->db->where('company_id', $company);
			$this->db->where('alternate_contact != ', $this->input->post('alternate_contact'));
			$other_alternate = $this->db->get('employee_uct');

			if ($other_alternate && $other_alternate->num_rows() > 0) {
				$alternates = array();

				foreach ($other_alternate->result() as $alternate) { 
					
					$alternate_ids =  explode(',', $alternate->alternate_contact);
					foreach ($alternate_ids as $key => $value) {
							$alternates[] = $value;
					}
				
				}

				$alternate_contacts = array_merge($alternate_contact, $alternates);

				$alternate_contact = array_unique($alternate_contacts);
			}
		}

		if( $list_result->num_rows > 0 ){

			foreach( $list_result->result() as $list_record ){

				if( $list_record->user_id == $primary_contact ){
					$primary_option_html .= '<option selected="" value="'.$list_record->user_id.'">'.$list_record->firstname.' '.$list_record->lastname.'</option>';
					$alternate_option_html .= '<option disabled="" value="'.$list_record->user_id.'">'.$list_record->firstname.' '.$list_record->lastname.'</option>';
				}
				elseif( in_array($list_record->user_id, $alternate_contact) ){
					$primary_option_html .= '<option disabled="" value="'.$list_record->user_id.'">'.$list_record->firstname.' '.$list_record->lastname.'</option>';
					$alternate_option_html .= '<option selected="" value="'.$list_record->user_id.'">'.$list_record->firstname.' '.$list_record->lastname.'</option>';
				}
				else{
					$primary_option_html .= '<option value="'.$list_record->user_id.'">'.$list_record->firstname.' '.$list_record->lastname.'</option>';
					$alternate_option_html .= '<option value="'.$list_record->user_id.'">'.$list_record->firstname.' '.$list_record->lastname.'</option>';
				}

			}

		}


		$response->primary_html = $primary_option_html;
		$response->alternate_html = $alternate_option_html;

 		$data['json'] = $response;
	    $this->load->view('template/ajax', array('json' => $response));


	}
	
	function get_employee(){

		// $department = $this->db->get_where('user_company_department', array('department_id' => $this->input->post('department_id')))->row(); 
		// $division = $this->db->get_where('user_company_division', array('division_id' => $department->division_id))->row();

		// $users = $this->system->get_employee_circle($division->division_manager_id);
		
		$sql = "SELECT * 
					FROM hr_user
					LEFT JOIN hr_employee ON hr_employee.employee_id = hr_user.employee_id
				WHERE hr_employee.resigned = 0 AND hr_user.inactive = 0  
				AND ISNULL( hr_employee.resigned_date )";
				// AND hr_user.employee_id IN (".implode(',', array_unique($users)).")";

		$list_result = $this->db->query($sql);
		
		$primary_option_html = "<option value=''> </option>";

		if( $list_result && $list_result->num_rows() > 0){
			foreach( $list_result->result() as $list_record ){
				$primary_option_html .= '<option value="'.$list_record->user_id.'">'.$list_record->firstname.' '.$list_record->lastname.'</option>';
			}
		}

		$response->primary_html = $primary_option_html;

 		$data['json'] = $response;
	    $this->load->view('template/ajax', array('json' => $response));
	}	
}

/* End of file */
/* Location: system/application */
?>