<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Skill_set_competencies extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Skill Set Competencies';
		$this->listview_description = 'This module lists all defined training skill set competencies(s).';
		$this->jqgrid_title = "Skill Set Competencies List";
		$this->detailview_title = 'Skill Set Competencies Info';
		$this->detailview_description = 'This page shows detailed information about a particular training skill set competencies.';
		$this->editview_title = 'Skill Set Competencies Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training skill set competencies(s).';
    	$this->detail_type = array('item');	

    }

	// START - default module functions
	// default jqgrid controller method
	function index( $position_id = 0)
    {
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['content'] = 'training/skill_set_competencies/listview';
		
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

		if( $position_id != 0 ){
    		$data['default_query'] = true;
			$data['default_query_field'] = 'position_id';
			$data['default_query_val'] = $position_id;
			$data['position_id'] = $position_id;
    	}
    	else{
    		redirect('training/position_list/');
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
		$data['views'] = array();

		$data['buttons'] = $this->module_link . '/detail-buttons';

		$this->db->order_by('score_type');
		$item_result = $this->db->get('training_evaluation_score_type');
		$item_list = $item_result->result_array();
		$item_list['count'] = $item_result->num_rows();
		$data['item_score_type_list'] = $item_list;
		$data['item_count'] = $this->input->post('item_count');
		$data['position_id'] = $this->input->post('position_id');

		foreach( $this->detail_type as $detail ){
			$data[$detail] = $this->_get_skills_detail($this->input->post('record_id'),$detail);

			if( $detail == 'item' ){

				foreach( $data[$detail] as $key => $val ){

					$this->db->where('skills_item_id',$data[$detail][$key]['skills_item_id']);
					$subcriteria_result = $this->db->get('training_position_skills_multiple_subcriteria');

					if( $subcriteria_result->num_rows() > 0 ){

						foreach( $subcriteria_result->result_array() as $subcriteria_info ){

							$data[$detail][$key]['subcriteria1'] = $subcriteria_info['sub_criteria1'];
							$data[$detail][$key]['subcriteria2'] = $subcriteria_info['sub_criteria2'];
							$data[$detail][$key]['subcriteria3'] = $subcriteria_info['sub_criteria3'];
							$data[$detail][$key]['subcriteria4'] = $subcriteria_info['sub_criteria4'];

						}

					}

				}

			}


		}
		

		$data['position_id'] = $this->input->post('position_id');

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
			$data['scripts'][] = chosen_script();

			$data['buttons'] = $this->module_link . '/edit-buttons';
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			$data['position_id'] = $this->input->post('position_id');


			foreach( $this->detail_type as $detail ){
				$data[$detail] = $this->_get_skills_detail($this->input->post('record_id'),$detail);

				
				if( $detail == 'item' ){

					foreach( $data[$detail] as $key => $val ){

						$training_skills_score = $this->db->get_where('training_evaluation_competence_score',array('skills_item_id'=>$data[$detail][$key]['skills_item_id']));

						if( $training_skills_score->num_rows() > 0 ){
							$data[$detail][$key]['used'] = 1;
						}
						else{
							$data[$detail][$key]['used'] = 0;
						}

						$this->db->where('skills_item_id',$data[$detail][$key]['skills_item_id']);
						$subcriteria_result = $this->db->get('training_position_skills_multiple_subcriteria');

						if( $subcriteria_result->num_rows() > 0 ){

							foreach( $subcriteria_result->result_array() as $subcriteria_info ){

								$data[$detail][$key]['subcriteria1'] = $subcriteria_info['sub_criteria1'];
								$data[$detail][$key]['subcriteria2'] = $subcriteria_info['sub_criteria2'];
								$data[$detail][$key]['subcriteria3'] = $subcriteria_info['sub_criteria3'];
								$data[$detail][$key]['subcriteria4'] = $subcriteria_info['sub_criteria4'];

							}

						}

					}

				}
				
			}


			$this->db->order_by('score_type');
			$item_result = $this->db->get('training_evaluation_score_type');
			$item_list = $item_result->result_array();
			$item_list['count'] = $item_result->num_rows();
			$data['item_score_type_list'] = $item_list;
			$data['item_count'] = $this->input->post('item_count');

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

		//Save position id
		$position_id = $this->input->post('position_id');
		$this->db->update($this->module_table ,array('position_id'=>$position_id),array($this->key_field=>$this->key_field_val));

		foreach( $this->detail_type as $detail ){

			$table = 'training_position_skills_'.$detail;
			$subcriteria = array();

			if ($this->db->table_exists($table)) {
				
				$post = $this->input->post($detail);

				if (!is_null($post) && is_array($post)) {
					// Handle the dates.
					foreach ($post as $key => $value) {
						
						$key_string_segments = explode('_', $key);

					}

					if( $detail == 'item' ){

						//remove unecessary variables not use in saving data
						$subcriteria = $post['subcriteria'];

						unset($post['item_rand']);
						unset($post['subcriteria']);

					}

					$data = $this->_rebuild_array($post, $this->key_field_val);

					if( $detail == 'item' ){

						

						$skills_item_list = $this->db->get_where($table,array('position_skills_id'=>$this->key_field_val));

						if( $skills_item_list > 0 ){

							$skills_item_list = $skills_item_list->result();

							foreach( $skills_item_list as $skills_item_list_info ){

								$this->db->where('skills_item_id',$skills_item_list_info->skills_item_id);
								$evaluation_competence_list = $this->db->get('training_evaluation_competence_score');

								if( $evaluation_competence_list->num_rows() == 0 ){

									$skills_item_count = 0;

									foreach( $data as $skills_item_info ){
										if( $skills_item_info['skills_item_id'] == $skills_item_list_info->skills_item_id ){
											$skills_item_count++;
										}
									}

									if( $skills_item_count == 0 ){

										$this->db->delete($table,array('skills_item_id'=>$skills_item_list_info->skills_item_id));

									}

								}
							}
						}
						

						foreach( $data as $skills_item_info ){

							if( $skills_item_info['skills_item_id'] == 0 ){
								unset($skills_item_info['skills_item_id']);
								$this->db->insert($table,$skills_item_info);

								$subcriteria_data = array(
									'skills_item_id' => $this->db->insert_id(),
									'sub_criteria1' => $subcriteria[$skills_item_info['skills_item_no']]['sub_criteria1'],
									'sub_criteria2' => $subcriteria[$skills_item_info['skills_item_no']]['sub_criteria2'],
									'sub_criteria3' => $subcriteria[$skills_item_info['skills_item_no']]['sub_criteria3'],
									'sub_criteria4' => $subcriteria[$skills_item_info['skills_item_no']]['sub_criteria4']
								);

								$this->db->insert('training_position_skills_multiple_subcriteria',$subcriteria_data);

							}
							else{

								$skills_item = $this->db->get_where($table,array('skills_item_id'=>$skills_item_info['skills_item_id']))->num_rows();

								if( $skills_item > 0 ){

									if( count($subcriteria) > 0 ){

										$this->db->delete('training_position_skills_multiple_subcriteria',array('skills_item_id'=>$skills_item_info['skills_item_id']));

										$subcriteria_data = array(
											'skills_item_id' => $skills_item_info['skills_item_id'],
											'sub_criteria1' => $subcriteria[$skills_item_info['skills_item_no']]['sub_criteria1'],
											'sub_criteria2' => $subcriteria[$skills_item_info['skills_item_no']]['sub_criteria2'],
											'sub_criteria3' => $subcriteria[$skills_item_info['skills_item_no']]['sub_criteria3'],
											'sub_criteria4' => $subcriteria[$skills_item_info['skills_item_no']]['sub_criteria4']
										);

										$this->db->insert('training_position_skills_multiple_subcriteria',$subcriteria_data);

									}

									$skills_item_id = $skills_item_info['skills_item_id'];
									unset($skills_item_info['skills_item_id']);

									$this->db->update($table,$skills_item_info,array('skills_item_id'=>$skills_item_id));

								}

							}


							
						}

					}	
				}
			}

		}



		//additional module save routine here
				
	}
	
	function delete()
	{
		parent::delete();

		$this->db->delete('training_position_skills_item',array($this->key_field=>$this->key_field_val));
		
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

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }

        $buttons .= "<div class='icon-label'><a class='icon-16-settings set_weight' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Set Weight</span></a></div>";
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	// END - default module functions
	
	// START custom module funtions
	

	function get_form($type) {
		if (IS_AJAX) {
			if ($type == '') {
				show_error("Insufficient data supplied.");
			} else {

				if( $type == 'item' ){

					$this->db->order_by('score_type');
					$item_result = $this->db->get('training_evaluation_score_type');

					$item_list = $item_result->result_array();
					$data['item_score_type_list'] = $item_list;
					$data['item_count'] = $this->input->post('item_count');
					$data['item_rand'] = $rand = rand(1,10000000);

				}


				$response = $this->load->view($this->userinfo['rtheme'] . '/training/skill_set_competencies/'.$type.'_form', $data);

				$data['html'] = $response;

				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	private function _rebuild_array($array, $fkey = null) {
		if (!is_array($array)) {
			return array();
		}

		$new_array = array();

		$count = count(end($array));
		$index = 0;

		while ($count >= $index) {
			foreach ($array as $key => $value) {

				if( isset( $array[$key][$index] ) ){

					$new_array[$index][$key] = $array[$key][$index];
					if (!is_null($fkey)) {
						$new_array[$index][$this->key_field] = $fkey;
					}

				}
				else{

					continue;

				}
			}

			$index++;
		}

		return $new_array;
	}

	private function _get_skills_detail($record_id = 0, $detail_type = "") {
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$response = array();

		$table = 'training_position_skills_'.$detail_type;
		$this->db->where('position_skills_id', $record_id);
		$this->db->where('deleted', 0);

		if( $detail_type == 'item' ){
			$this->db->order_by('skills_item_no', 'ASC');
		}

		$result = $this->db->get($table);

		if ($result){
			$response = $result->result_array();
		}
		

		return $response;
	}

	function count_skillset(){

		$position_id = $this->input->post('position_id');

		$position_skills = $this->db->get_where('training_position_skills',array('position_id'=>$position_id,'deleted'=>0));

		$response->skill_count = $position_skills->num_rows();

		$this->load->view('template/ajax', array('json' => $response));


	}

	function get_set_weight_template_form(){

		$position_id = $this->input->post('position_id');

		$result = $this->db->get_where('training_position_skills',array('position_id'=>$position_id,'deleted'=>0));

		if( $result->num_rows() > 0 ){

			$position_skills = $result->result();

			$total_weight = 0;

			foreach( $position_skills as $position_skills_info ){

				$total_weight = $total_weight + $position_skills_info->weight;
			}

			$response->form = $this->load->view( $this->userinfo['rtheme'].'/training/skill_set_competencies/set_weight_template_form',array('position_skills' => $result->result(), 'position_id'=> $position_id, 'total_weight'=>$total_weight), true );
			$this->load->view('template/ajax', array('json' => $response));
		}
		else{
			$response->msg = "There are no available Skills to be Set";
	        $response->msg_type = "error";
	        $data['json'] = $response;

	        $this->load->view('template/ajax', array('json' => $response));
		}

	}

	function save_weight(){

		$weight = $this->input->post('weight');

		foreach($weight as $key => $val){
			$this->db->update('training_position_skills',array('weight'=>$val),array('position_skills_id'=>$key));
		}

		$response->msg = "Weight was successfully saved";
	    $response->msg_type = "success";

		$this->load->view('template/ajax', array('json' => $response));

	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>