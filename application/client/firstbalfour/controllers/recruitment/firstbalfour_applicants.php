<?php

include (APPPATH . 'controllers/recruitment/applicants.php');

class Firstbalfour_applicants extends applicants
{
	public function __construct() {
		parent::__construct();
	}	

	function listview()
	{
		if( $this->input->post('mrf') ){
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $this->input->post('mrf')))->row();
			$position_id = $mrf->position_id;
		}
		
		
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions );
		$this->listview_qry .= ',afpos.position as af_position, '.$this->db->dbprefix.$this->module_table.'.application_status_id';		
		$this->listview_qry .= ', bs.recruitment_candidate_blacklist_status';

		if ($this->input->post('searchField') == 'recruitment_applicant.position_id'){
			$_POST['searchField'] = 'position';
		}

		if ($this->input->post('searchField') == 'recruitment_applicant.application_date'){
			$output = preg_replace( '/[^0-9]/', '', $this->input->post('searchString'));
			switch (strlen($output)) {
				case 6:
						$_POST['searchField'] = 'DATE('.$this->db->dbprefix.'recruitment_applicant.application_date)';
						$_POST['searchString'] = date('Y-m-d',strtotime($this->input->post('searchString')));
					break;
				case 4:
						$_POST['searchField'] = 'YEAR('.$this->db->dbprefix.'recruitment_applicant.application_date)';
						$_POST['searchString'] = $this->input->post('searchString');
					break;	
				case 2:
						$_POST['searchField'] = 'MONTH('.$this->db->dbprefix.'recruitment_applicant.application_date)';
						$_POST['searchString'] = preg_replace( '/[0]/', '', $this->input->post('searchString'));
					break;
			}
		}
		
/*		if ($this->input->post('searchField') == 'recruitment_applicant.application_date'){
			$_POST['searchField'] = 'DATE('.$this->db->dbprefix.'recruitment_applicant.application_date)';
			$_POST['searchString'] = date('Y-m-d',strtotime($this->input->post('searchString')));
		}*/

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;		

		if ($this->input->post('other') == 'candidate') {
			$search .= ' AND ' . $this->db->dbprefix . $this->module_table . '.application_status_id IN (1,5) ';
		}
		
		if(isset($position_id)) {
			$search .= 
				' AND (' . $this->db->dbprefix . $this->module_table . '.position_id = '. $position_id 
					. ' OR ' . $this->db->dbprefix . $this->module_table . '.af_pos_id = ' . $position_id 
					. ')';
		}
		
		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);

		// for advance search
		if ($this->input->post('position') && $this->input->post('position') != 'null'){
			$this->db->where_in($this->db->dbprefix . $this->module_table.'.position_id',$this->input->post('position'));
		}

		if ($this->input->post('status') && $this->input->post('status') != 'null'){
			$this->db->where_in($this->db->dbprefix . $this->module_table.'.application_status_id',$this->input->post('status'));
		}
		else{
			$this->db->where( $this->module_table.'.application_status_id != 4' );			
		}

		if ($this->input->post('male')){
			$this->db->where($this->db->dbprefix . $this->module_table.'.sex',$this->input->post('male'));
		}	

		if ($this->input->post('female')){
			$this->db->where($this->db->dbprefix . $this->module_table.'.sex',$this->input->post('female'));
		}	

		if ($this->input->post('age')){
			$this->db->where($this->db->dbprefix . $this->module_table.'.age',$this->input->post('age'));
		}	

		if ($this->input->post('location') && $this->input->post('location') != 'null'){
			$this->db->like($this->db->dbprefix . $this->module_table.'.perm_city',$this->input->post('location'));
		}							
		// for advance search

		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

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

			// for advance search
			if ($this->input->post('position') && $this->input->post('position') != 'null'){
				$this->db->where_in($this->db->dbprefix . $this->module_table.'.position_id',$this->input->post('position'));
			}

			if ($this->input->post('status') && $this->input->post('status') != 'null'){
				$this->db->where_in($this->db->dbprefix . $this->module_table.'.application_status_id',$this->input->post('status'));
			}
			else{
				$this->db->where($this->module_table.'.application_status_id != 4');				
			}

			if ($this->input->post('male')){
				$this->db->where($this->db->dbprefix . $this->module_table.'.sex',$this->input->post('male'));
			}	

			if ($this->input->post('female')){
				$this->db->where($this->db->dbprefix . $this->module_table.'.sex',$this->input->post('female'));
			}	

			if ($this->input->post('age')){
				$this->db->where($this->db->dbprefix . $this->module_table.'.age',$this->input->post('age'));
			}	

			if ($this->input->post('location') && $this->input->post('location') != 'null'){
				$this->db->like($this->db->dbprefix . $this->module_table.'.perm_city',$this->input->post('location'));
			}								
			// for advance search

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);

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

			//dbug($this->db->last_query());

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
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row) );
									$cell_ctr++;
								}
							} else if ($detail['name'] == 't5application_status') {
								$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
								$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								
								$candidate_status = $this->system->get_applicant_candidate_status($row['applicant_id']);

								if ($row['application_status_id'] == 5) {
									if ($row['af_position'] != ''){
										$cell[$cell_ctr] .= '<br />(' . $row['af_position'] . ')';
									}
								}

								if ($row['application_status_id'] == '6') {
									$cell[$cell_ctr] .= '<br /><small class="red">' . $row['recruitment_candidate_blacklist_status'] . '</small>';
								}
								
								$cell_ctr++;
							} else if ($detail['name'] == 'candidate_status_id_tmp') {
								$candidate_status = $this->system->get_applicant_candidate_status($row['applicant_id']);

								$this->listview_fields[$cell_ctr]['value'] = ($candidate_status != '' ? $candidate_status : '');
								$cell[$cell_ctr] = ($candidate_status != '' ? $candidate_status : '');								
/*								if ($row['application_status_id'] == 2 || $row['application_status_id'] == 5) {
									$candidate_status = $this->system->get_applicant_candidate_status($row['applicant_id']);

									$this->listview_fields[$cell_ctr]['value'] = ($candidate_status != '' ? $candidate_status : '');
									$cell[$cell_ctr] = ($candidate_status != '' ? $candidate_status : '');
								}*/
								$cell_ctr++;								
							} else if ($detail['name'] == 'mrf_id_tmp') {

								$this->db->where('applicant_id',$row['applicant_id']);
								$this->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
								$mrf_doc_no = $this->db->get('recruitment_manpower_candidate')->row_array();

								$this->listview_fields[$cell_ctr]['value'] = $mrf_doc_no['document_number'];
								$cell[$cell_ctr] = $mrf_doc_no['document_number'];
								$cell_ctr++;

							} else if ($detail['name'] == 't8position_id') {

								$this->db->where('applicant_id',$row['applicant_id']);
								$this->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
								$this->db->join('user_position', 'user_position.position_id = recruitment_manpower.position_id');
								$mrf_doc_no = $this->db->get('recruitment_manpower_candidate')->row_array();

								$this->listview_fields[$cell_ctr]['value'] = $mrf_doc_no['position'];
								$cell[$cell_ctr] = $mrf_doc_no['position'];
								$cell_ctr++;
							}
							else if ($detail['name'] == 't9category_value_id') {

								// 
								$this->db->where('applicant_id',$row['applicant_id']);
								$this->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
								$mrf_doc_no = $this->db->get('recruitment_manpower_candidate')->row_array();
								
								$proj_dept = $this->system->get_recruitment_category($mrf_doc_no['category_id'], $mrf_doc_no['category_value_id']);

								$this->listview_fields[$cell_ctr]['value'] = $proj_dept['cat_value'];
								$cell[$cell_ctr] = $proj_dept['cat_value'];
								$cell_ctr++;
							}
							else{
								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39) ) ){
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
								else{
									$cell[$cell_ctr] = (is_numeric($row[$detail['name']]) && ($column_type[$detail['name']] != "253" && $column_type[$detail['name']] != "varchar") ) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
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
}
?>