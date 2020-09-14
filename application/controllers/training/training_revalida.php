<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_revalida extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Training Revalida';
		$this->listview_description = 'This module lists all defined training revalida(s).';
		$this->jqgrid_title = "Training Revalida List";
		$this->detailview_title = 'Training Revalida Info';
		$this->detailview_description = 'This page shows detailed information about a particular training revalida.';
		$this->editview_title = 'Training Revalida Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about training revalida(s).';
    
		if(!$this->user_access[$this->module_id]['post'] && !$this->user_access[$this->module_id]['approve'] ){
			$this->filter = "training_revalida.employee_id = 0";
		}

    }

	// START - default module functions
	// default jqgrid controller method
	function index()
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
	
	function detail() {        
        show_error('Function does not exist.');
    }

    function edit() {
        show_error('Function does not exist.');
    }	
	
	function ajax_save() {
        show_error('Function does not exist.');
    }

    function delete() {
        show_error('Function does not exist.');
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

		if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){

			$training_revalida_list = array();

			$sql = '
				SELECT *
				FROM '.$this->db->dbprefix('training_revalida').'
				LEFT JOIN '.$this->db->dbprefix('training_calendar').' ON '.$this->db->dbprefix('training_calendar').'.training_calendar_id = '.$this->db->dbprefix('training_revalida').'.training_calendar_id
				LEFT JOIN '.$this->db->dbprefix('employee').' ON  '.$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('training_revalida').'.employee_id
				WHERE ( '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].'%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].'%"
			OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix('employee').'.reporting_to LIKE "%,'.$this->userinfo['user_id'].',%" )';
				
				$participant_subordinate_result = $this->db->query($sql);

			if( $participant_subordinate_result->num_rows() > 0 ){
				foreach( $participant_subordinate_result->result() as $subordinate_info ){
					array_push($training_revalida_list, $subordinate_info->training_calendar_id);
				}
			}

		}


		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->join('training_revalida','training_revalida.training_calendar_id = '.$this->module_table.'.training_calendar_id','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){
			if( count($training_revalida_list) != 0 ){
				$this->db->where_in($this->module_table.'.training_calendar_id',$training_revalida_list);
			}
			else{
				$this->db->where($this->module_table.'.training_calendar_id = 0');
			}
		}
		$this->db->where($this->module_table.'.revalida_date <= ',date('Y-m-d'));
		$this->db->where($this->module_table.'.closed',1);
		$this->db->group_by($this->module_table.'.training_calendar_id');
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

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
			$this->db->join('training_revalida','training_revalida.training_calendar_id = '.$this->module_table.'.training_calendar_id','left');
			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if( $this->user_access[$this->module_id]['post'] == 0 && $this->user_access[$this->module_id]['approve'] == 1 ){
				if( count($training_revalida_list) != 0 ){
					$this->db->where_in($this->module_table.'.training_calendar_id',$training_revalida_list);
				}
				else{
					$this->db->where($this->module_table.'.training_calendar_id = 0');
				}
			}
			$this->db->where($this->module_table.'.revalida_date <= ',date('Y-m-d'));
			$this->db->where($this->module_table.'.closed',1);
			$this->db->group_by($this->module_table.'.training_calendar_id');
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
						$custom_column_cnt = 0;
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

							
							if( $detail['name'] == 'topic'  ){

								$custom_column_cnt = 0;

							}

							if( $detail['name'] == 'action'  ){
								if( $view_actions ){
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );
									$cell_ctr++;
								}
							}
							elseif(  $detail['name'] == "training_calendar_id" ){

								switch( $custom_column_cnt ){
									case 0:
										//Start Date
										$training_calendar_id = $row[$detail['name']];

										$this->db->where('training_calendar_id',$training_calendar_id);
										$this->db->order_by('session_date','ASC');
										$start_date_result = $this->db->get('training_calendar_session');

										if( $start_date_result->num_rows() > 0 ){
											$start_date_info = $start_date_result->row();
											$cell[$cell_ctr] = date($this->config->item('display_date_format') ,strtotime($start_date_info->session_date));
										}
										else{
											$cell[$cell_ctr] = '';
										}

									break;
									case 1:
										//End Date
										$training_calendar_id = $row[$detail['name']];

										$this->db->where('training_calendar_id',$training_calendar_id);
										$this->db->order_by('session_date','DESC');
										$end_date_result = $this->db->get('training_calendar_session');

										if( $end_date_result->num_rows() > 0 ){
											$end_date_info = $end_date_result->row();
											$cell[$cell_ctr] = date($this->config->item('display_date_format') ,strtotime($end_date_info->session_date));
										}
										else{
											$cell[$cell_ctr] = '';
										}
									break;
									case 2:
										//Training Session
										$training_calendar_id = $row[$detail['name']];

										$this->db->where('training_calendar_id',$training_calendar_id);
										$training_session_result = $this->db->get('training_calendar_session');

										if( $training_session_result->num_rows() > 0 ){
											$training_session_list = $training_session_result->result();
											$training_session = "";

											foreach( $training_session_list as $training_session_info ){

												$training_session .= '&bull; '.date('h:i a', strtotime( $training_session_info->sessiontime_from )).' - '.date('h:i a', strtotime( $training_session_info->sessiontime_to )).'<br />';

											}

											$cell[$cell_ctr] = $training_session;
										}
										else{
											$cell[$cell_ctr] = '';
										}
									break;
									case 3:
										//Instructor
										$training_calendar_id = $row[$detail['name']];

										$this->db->where('training_calendar_id',$training_calendar_id);
										$training_calendar_session_result = $this->db->get('training_calendar_session');

										if( $training_calendar_session_result->num_rows() > 0 ){
											$training_calendar_session_list = $training_calendar_session_result->result();
											$instructor = "";
											$instructor_list = array();

											foreach( $training_calendar_session_list as $training_calendar_session_info ){

												$instructor_temp_list = explode(',',$training_calendar_session_info->instructor);

												foreach( $instructor_temp_list as $instructor_temp_id ){

													if( ! in_array( $instructor_temp_id, $instructor_list ) ){
														$instructor_list[] = $instructor_temp_id;
													}
												}

											}

											$this->db->where("training_instructor_id IN ('".implode("','",$instructor_list)."')");
											$training_instructor_result = $this->db->get('training_instructor')->result();

											foreach( $training_instructor_result as $training_instructor_info ){
												$instructor .= '&bull; '.$training_instructor_info->training_instructor.'<br />';
											}

											$cell[$cell_ctr] = $instructor;
										}
										else{
											$cell[$cell_ctr] = '';
										}
									break;
								}

								$custom_column_cnt++;
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

	// END - default module functions
	
	// START custom module funtions
	
	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                          
        
        $buttons .= "</div>";
                
		return $buttons;
	}


	function _default_grid_actions($module_link = "", $container = "", $record = array()) {


		$training_calendar = $this->db->get_where($this->module_table, array($this->key_field => $record['training_calendar_id']));

		if( $training_calendar->num_rows() == 1 ){
			$training_calendar = $training_calendar->row();
		}

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$training_revalida = $this->db->get_where('training_revalida',array('training_calendar_id'=>$training_calendar->training_calendar_id, 'employee_id'=>$this->userinfo['user_id']));
		$revalida_participant = false;

		if( $training_revalida->num_rows() > 0 ){
			$revalida_participant = true;
		}

		$actions = '<span class="icon-group">';

		if ($this->user_access[$this->module_id]['post'] || $this->user_access[$this->module_id]['approve']) {
			$actions .= '<a class="icon-button icon-16-search search_participants" tooltip="Search Participants" module_link="'.$module_link.'" onclick="" href="javascript:void(0)"></a>';
		}

		/*
		if ($this->user_access[$this->module_id]['view'] && $revalida_participant) {
			$actions .= '<a class="icon-button icon-16-info view_revalida" tooltip="View Feedback" onclick="" module_link="training/training_revalida_participants" tooltip="Delete" href="javascript:void(0)"></a>';
		}

		if ($this->user_access[$this->module_id]['edit'] && $revalida_participant) {
			$actions .= '<a class="icon-button icon-16-edit edit_revalida" tooltip="Edit Feedback" onclick="" module_link="training/training_revalida_participants" href="javascript:void(0)"></a>';
		}
		*/

		$actions .= '</span>';

		return $actions;
	}

	function get_revalida_participant_id(){

		$calendar_id = $this->input->post('calendar_id');

		$training_revalida = $this->db->get_where('training_revalida',array('training_calendar_id'=>$calendar_id, 'employee_id'=>$this->userinfo['user_id']));

		if( $training_revalida->num_rows() > 0 ){
			$training_revalida = $training_revalida->row();
			$response->training_revalida_id = $training_revalida->training_revalida_id;
		}
		else{
			$response->training_revalida_id = 0;
		}

		$this->load->view('template/ajax', array('json' => $response));

	}

	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>