<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Candidate_job_offer extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		$this->load->helper('candidates');

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

		$this->filter = $this->module_table.".candidate_status_id = 5";
		$this->default_sort_col = array('t0firstnamelastname');	
		
		$statuses = get_candidate_statuses();
        foreach ($statuses as $index => $status) {

            if ($status['default'] == 1) {
                $this->_default_status_id = $status['candidate_status_id'];
            }

            if ($status['hired_flag'] == 1) {
                $this->_hired_status_id = $status['candidate_status_id'];
                unset($statuses[$index]);
            }

            if ($status['rejected_flag'] == 1) {
                $this->_rejected_status_id = $status['candidate_status_id'];                
            }

            if ($status['interview_flag'] == 1) {
                $this->_interview_status_id = $status['candidate_status_id'];
            }

            if ($status['evaluation_flag'] == 1) {
                $this->_evaluation_status_id = $status['candidate_status_id'];
            }            

            if ($status['joboffer_flag'] == 1) {
                $this->_joboffer_status_id = $status['candidate_status_id'];
            }               
        }		
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$this->load->helper('candidates');
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'listview';
		$data['jqgrid'] = 'recruitment/candidates/jqgrid';

		$array_tab_count = get_candidate_tab_count();
		
        $tabs[] = '<li filter="posted_jobs"><a href="'.base_url().'recruitment/candidate_postedjobs">Posted Jobs<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['posted_jobs'].'</span></li>';
        $tabs[] = '<li filter="schedule"><a href="'.base_url().'recruitment/candidate_schedule">Candidates<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['schedule'].'</span></li>';
        $tabs[] = '<li filter="exam"><a href="'.base_url().'recruitment/candidate_result">Assessment Profile<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['result'].'</span></li>';
        $tabs[] = '<li filter="bcheck"><a href="'.base_url().'recruitment/candidate_background_check">Background Check<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['bcheck'].'</span></li>';
        $tabs[] = '<li filter="job_offer" class="active"><a href="'.base_url().'recruitment/candidate_job_offer">Job Offer <span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['joboffer'].'</span></li>';
        $tabs[] = '<li filter="contract_sign"><a href="'.base_url().'recruitment/candidate_contract_signing">For Contract Signing<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['contractsigning'].'</span></li>';
        $tabs[] = '<li filter="others"><a href="'.base_url().'recruitment/candidate_others">Others <span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['others'].'</span></li>';
        $tabs[] = '<li filter="archive"><a href="'.base_url().'recruitment/candidate_archive">Archive <span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['archive'].'</span></li>';

		if( sizeof( $tabs ) > 1 ) $data['candidate_tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');

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

		$data['benefits'] = $this->get_benefits($this->input->post('record_id'));

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

	function ajax_save()
	{
		$this->related_table = array('recruitment_candidate_job_offer' => 'candidate_id');

		if ($this->input->post('record_id') == '-1') {
			$_POST['job_offer_status_id'] = 1;
		}

		parent::ajax_save();
		$job_offer_id = $this->db->get_where('recruitment_candidate_job_offer', array('candidate_id' => $this->input->post('record_id')))->row();
		//additional module save routine here
		$benefits = $this->input->post('benefit');
		$hours = $this->input->post('hours');
		$units = $this->input->post('units');
		if (is_array($benefits) && count($benefits) > 0) {
			$this->db->delete('recruitment_candidate_job_offer_benefit',array('job_offer_id' => $job_offer_id->job_offer_id));
			foreach( $benefits as $benefit_id => $value ){
				$unit = $units[$benefit_id];
				$data = array(
					'job_offer_id' => $job_offer_id->job_offer_id,
					'benefit_id' => $benefit_id,
					'value' => str_replace(',', '', $value),
					'units' => $unit		
				);
				$this->db->insert('recruitment_candidate_job_offer_benefit', $data);
			}
		}
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}

	function after_ajax_save() {
		// Various operations depending on status, only do if save is successful.
		if ($this->get_msg_type() == 'success' && $this->input->post('accepted')) {
			$x = $this->input->post('accepted');
			if ( $x == 1) {
				$status = 'accept';
			} else if ( $x == 2) {
				$status = 'reject';
			} else {
				$status = '';
			}

			$this->change_status($status);
			return;
		}

		parent::after_ajax_save();		
	}	
	// END - default module functions

	// START custom module funtions
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
		// $this->listview_qry .= ',IF('.$this->db->dbprefix.'recruitment_manpower_candidate.is_internal = 0, CONCAT( '.$this->db->dbprefix.'recruitment_applicant.firstname, " ", '.$this->db->dbprefix.'recruitment_applicant.lastname ), CONCAT( ' . $this->db->dbprefix . 'user.firstname, " ", ' . $this->db->dbprefix . 'user.lastname )) t0firstnamelastname';
		$this->listview_qry .= ',IF('.$this->db->dbprefix.'recruitment_manpower_candidate.is_internal = 0, CONCAT( '.$this->db->dbprefix.'recruitment_applicant.firstname, " ",REPLACE(CONCAT(UCASE(LEFT('.$this->db->dbprefix.'recruitment_applicant.middlename , 1))," .")," ", ""), " ", '.$this->db->dbprefix.'recruitment_applicant.lastname ), CONCAT( ' . $this->db->dbprefix . 'user.firstname, " ", ' . $this->db->dbprefix . 'user.middleinitial, " ", ' . $this->db->dbprefix . 'user.lastname )) t0firstnamelastname';


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
		$this->db->where('IF(is_internal = 0, 1, '.$this->db->dbprefix.'user.deleted = 0)');
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
				if( $sidx == 'recruitment_manpower_candidate.applicant_id'){
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort, $sord);
				}else{
					$this->db->order_by($sidx, $sord);	
				}
			}
			else{
				if( is_array($this->default_sort_col) ){
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);
			
			if (CLIENT_DIR === 'oams') {
				if (!$this->is_recruitment() && !$this->is_superadmin && $user_access[$this->module_id]['post'] == 0 ) {
				
					$this->db->where('( '.$this->db->dbprefix('recruitment_manpower').'.requested_by = '.$this->userinfo['user_id'].' )
					AND ( '.$this->db->dbprefix('recruitment_manpower').'.created_by = '.$this->userinfo['user_id'].' )
					');

				}
				if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			}

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
							} elseif ($detail['name'] == 'applicant_id') {
								$cell[$cell_ctr++] = $row['t0firstnamelastname'];								
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

	private function _can_appraise($row) {
		if (!isset($row['interview_date']) 
			|| is_null($row['interview_date']) 
			|| $row['candidate_status_id'] == $this->_hired_status_id
			|| $row['candidate_status_id'] == $this->_rejected_status_id
			|| $row['candidate_status_id'] == 9
			|| $row['interview_date'] < date('Y-m-d h:i:s', strtotime('-1 day', strtotime(date('Y-m-d h:i:s'))))
			) {
			return false;
		}

		if ($row['candidate_status_id'] == $this->_interview_status_id && $this->is_recruitment()) {
			return true;
		}		

		if ($row['candidate_status_id'] == $this->_evaluation_status_id && $this->userinfo['user_id'] == $row['final_interviewer_id']) {
			return true;
		}

		return false;
	}

	private $_can_print_jo = null;
	
	private function _can_print_jo() {
		if (is_null($this->_can_print_jo)) {
			$module = $this->hdicore->get_module('job_offer');

			if ($module) {
				$this->_can_print_jo = $this->user_access[$module->module_id]['print'];
			} else {
				$this->_can_print_jo = false;
			}
		}

		return $this->_can_print_jo;
	}

	private function _can_reschedule($row) {
		if ($row['candidate_status_id'] == $this->_evaluation_status_id && $this->userinfo['user_id'] == $row['final_interviewer_id']) {
			return true;
		}

		return false;
	}

	function _set_listview_query($listview_id = '', $view_actions = true) {
		parent::_set_listview_query($listview_id, $view_actions);

		$this->listview_qry .= ',job_offer_id,'.$this->db->dbprefix.'recruitment_manpower_candidate.candidate_status_id,is_internal,job_offer_status_id';
	}

	function _set_left_join()
	{
		//build left join for related tables
		foreach($this->related_table as $table => $column){
			if ($table == 'recruitment_candidates_appraisal_exams'){
				$this->db->join('recruitment_candidates_appraisal', 'recruitment_candidates_appraisal.candidate_id='. $this->module_table .'.candidate_id', 'left');				
				$this->db->join('recruitment_candidates_appraisal_exams', 'recruitment_candidates_appraisal_exams.appraisal_id = recruitment_candidates_appraisal.appraisal_id', 'left');				
			}
			elseif ($table == 'recruitment_candidates_appraisal_comments'){
				$this->db->join('recruitment_candidates_appraisal_comments', 'recruitment_candidates_appraisal_comments.appraisal_id = recruitment_candidates_appraisal.appraisal_id', 'left');				
			}
			else{
				if( strpos($table, " ") ){
					$letter = explode(" ", $table);
					if($this->module == "module" && $column == "module_id" && $this->module == "module")
						$this->db->join($table, $letter[1] .'.module_id='. $this->module_table .'.parent_id', 'left');
					else
						$this->db->join($table, $letter[1] .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
				}
				else{
					$this->db->join($table, $table .'.'. $column[0] .'='. $this->module_table .'.'. $column[1], 'left');
				}
			}
		}

		$this->db->join('user', 'user.employee_id = recruitment_manpower_candidate.employee_id', 'left');
		$this->db->join('recruitment_applicant','recruitment_applicant.applicant_id = '.$this->db->dbprefix.'recruitment_manpower_candidate.applicant_id','left');
		$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = recruitment_manpower_candidate.mrf_id', 'left');					
	}	

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "";                    
                            
		return $buttons;
	}

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
		$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';

		if( $row['is_internal'] == 0 ){
			$actions .= '<a class="icon-button icon-16-users" candidate_id="'.$row['candidate_id'].'" module_link="' . $module_link . '" tooltip="View Applicant Details" href="javascript:void(0)"></a>';
		}

        if ($this->user_access[$this->module_id]['edit']) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a>';
        }

		// if ($this->user_access[$this->module_id]['print'] == 1) {
		// 	$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print Applicant Form" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';			
		// }

		if ($this->_can_appraise($row)) {
			$actions .= '<a class="icon-button icon-16-document-view show-appraisal" tooltip="Appraisal" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
		}

		if (($row['candidate_status_id'] == $this->config->item('contract_signing_status_id')
			|| $row['candidate_status_id'] == $this->_joboffer_status_id
			|| $row['candidate_status_id'] == 13) && $this->_can_print_jo()) {
			$actions .= '<a class="icon-button icon-16-document-stack" tooltip="Print Contract" onclick="return false;" href="javascript:void(0)" module_link="' . $module_link . '" joboffer_id="' . $row['job_offer_id'] . '"></a>';
		}

		if ( ($row['candidate_status_id'] == $this->_joboffer_status_id ) && in_array($row['job_offer_status_id'], array(0,1,2)) && ($this->is_recruitment() || $this->is_superadmin)) {

			if ($row['candidate_status_id'] == $this->config->item('contract_signing_status_id')) {
				$tooltip_jo_accept = 'Accept Employment';
				$tooltip_jo_reject = 'Reject Employment';
			} elseif ($row['candidate_status_id'] == $this->_joboffer_status_id) {
				$tooltip_jo_accept = 'Accept Job Offer';
				$tooltip_jo_reject = 'Reject Job Offer';
			}			
			
			$actions .= '<a class="icon-button icon-16-approve" tooltip="' . $tooltip_jo_accept . '" href="javascript:void(0)" module_link="' . $module_link . '" joboffer_id="' . $row['job_offer_id'] . '"></a>';
			$actions .= '<a class="icon-button icon-16-disapprove" tooltip="' . $tooltip_jo_reject .  '" href="javascript:void(0)" module_link="' . $module_link . '" joboffer_id="' . $row['job_offer_id'] . '"></a>';					
		}

		if (!in_array($row['candidate_status_id'], array($this->_hired_status_id, $this->_rejected_status_id))) {

		 	if ($row['candidate_status_id'] != $this->_joboffer_status_id && ($this->is_recruitment() || $this->is_admin || $this->is_superadmin)) {
				//$actions .= '<a class="icon-button icon-16-calendar-add" tooltip="Set Interview Schedule" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';				
		 	}
 			
 			if (1==2) { //($this->is_recruitment() || $row['approved_by'] == $this->userinfo['user_id'] ) {
				$actions .= '<a class="icon-button icon-16-user-add" tooltip="Hire" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
				$actions .= '<a class="icon-button icon-16-user-remove" tooltip="Reject" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
		 	}		 	
		}

		if ($this->_can_reschedule($row)) {
			$actions .= '<a class="icon-button icon-16-calendar-month" tooltip="Reschedule" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="' . $row['candidate_id'] . '"></a>';
		}

		if ($this->user_access[$this->module_id]['delete'] == 1) {
			if($row['candidate_status_id']==1 || $row['candidate_status_id']==2){
				$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a></span>';
			}
		}

		return $actions;
	}

	function print_record($candidate_id = 0) {
		// Get from $_POST when the URI is not present.
		if ($candidate_id == 0) {
			$candidate_id = $this->input->post('record_id');
		}

		if (CLIENT_DIR === 'oams') {
			$this->oams_print_record($candidate_id);
		}

		if ($candidate_id > 0 && $this->_record_exist($candidate_id)) {
			// Get the applicant ID form the candidate ID and redirect to applicants print function.
			$this->db->where($this->key_field, $candidate_id);
			$this->db->where('deleted', 0);

			$result = $this->db->get($this->module_table);
			$applicant = $result->row_array();

			redirect(site_url('recruitment/applicants/print_record/' . $applicant['applicant_id']));
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function oams_print_record($record_id = 0) {
		// Get from $_POST when the URI is not present.

		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}
		// $record_id = 5;
		
		$tpl_file = 'JOB_OFFER'; //default template
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		if( $this->uri->rsegment(4) )
			$template = $this->template->get_template( $this->uri->rsegment(4) );	
		else
			$template = $this->template->get_module_template($this->module_id, $tpl_file );
		
		// Get applicant details. (This returns the fieldgroup array.

		$check_record = $this->_record_exist($record_id);
		
		if ($check_record->exist) {

			$jo = $this->db->get_where('recruitment_candidate_job_offer', array('job_offer_id' => $record_id))->row();

			$candidate = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $jo->candidate_id ))->row();		
			
			$vars = get_record_detail_array($jo->candidate_id);

			$total = $vars['basic'];
			$tax = $total * .02;

			$vars['basic'] = number_format( $vars['basic'], 2, '.', ',' );
			$vars['tax'] = number_format( $vars['tax'], 2, '.', ',' );
			$vars['date'] = date( $this->config->item('display_date_format') );
			$vars['time'] = date( $this->config->item('display_time_format') );
			
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate->mrf_id ))->row();
			$campaign = $this->db->get_where('campaign', array('campaign_id' => $mrf->campaign_id ))->row();
			$vars['campaign'] = $campaign->campaign;
			$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
			$company = $this->db->get_where('user_company', array('company_id' => $position->company_id))->row();

			$meta = $this->config->item('meta');

			$vars['company'] = $meta['description'];
			$vars['position'] = $position->position;
			$vars['date_start'] = $vars['date_from'] = date( $this->config->item('display_date_format'), strtotime($jo->date_from) );
			$vars['date_end'] = $vars['date_to'] = date( $this->config->item('display_date_format'), strtotime($jo->date_to) );
			$facilitator = $this->hdicore->_get_userinfo( $this->user->user_id );
			$vars['facilitated_by'] = $facilitator->firstname.' '.$facilitator->lastname;
			$vars['benefits'] = "";
			$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $jo->job_offer_id));
			if( $benefits->num_rows() > 0 ){
				foreach( $benefits->result() as $benefit ){
					$benefit_detail = $this->db->get_where('benefit', array( 'benefit_id' => $benefit->benefit_id ))->row();
					$total += $benefit->value; 
					$vars['benefits'] .= '<tr>
							<td >'. $benefit_detail->benefit .': &nbsp;</td>
							<td align="right">'. number_format( $benefit->value, 2, '.', ',') .'</td>
						</tr>';
				}
			}
			$vars['total'] = number_format( $total, 2, '.', ',' );
			$vars['fancy_date'] = date('jS \d\a\y \o\f F Y');

			$html = $this->template->prep_message($template['body'], $vars, false, true);
			
			// Prepare and output the PDF.
			$this->pdf->setPrintHeader(TRUE);
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Job Offer.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	
	function print_contract($record_id = 0) {
		
		// Get from $_POST when the URI is not present.
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		// if (!$this->input->post('record_id')) $candidate_record_id = $this->uri->rsegment(5);
		$candidate_record_id = $this->uri->rsegment(3);

		//default template
		$tpl_file = 'PROBI_CONTRACT_NO_VOICE';
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		if( $this->uri->rsegment(4) )
			$template = $this->template->get_template( $this->uri->rsegment(4) );	
		else
			$template = $this->template->get_module_template($this->module_id, $tpl_file );
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($candidate_record_id);

		$this->load->library('parser');

		if ($record_id) {
			$this->db->select('recruitment_candidate_job_offer.date_from as date_start, recruitment_candidate_job_offer.date_to as date_end,  recruitment_manpower_candidate.*, recruitment_candidate_job_offer.*, recruitment_manpower.*');
			$this->db->join('recruitment_manpower_candidate','recruitment_candidate_job_offer.candidate_id = recruitment_manpower_candidate.candidate_id');						
			$this->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');			
			$jo = $this->db->get_where('recruitment_candidate_job_offer', array('job_offer_id' => $record_id))->row();

			$candidate = $this->db->get_where('recruitment_manpower_candidate', array('candidate_id' => $jo->candidate_id ))->row();

			$vars = get_record_detail_array($jo->candidate_id );

			$total = $vars['basic'];
			$tax = $total * .02;

			$number_word = number_to_words($vars['basic']);
			$vars['number_word'] = ucwords($number_word);
			$vars['tax'] = number_format( $tax, 2, '.', ',' );
			$vars['basic'] = number_format( $vars['basic'], 2, '.', ',' );
			$vars['pay_per_annum'] = $jo->pay_per_annum . ' months';
			$vars['increase'] = $jo->increase;
			$vars['increase_upon'] = ($vars['increase'] != "") ? "Increase Upon Regularization" : "";
			$vars['date'] = date( $this->config->item('display_date_format') );
			$vars['fancy_date'] = date('jS \d\a\y \o\f F Y');
			$vars['time'] = date( $this->config->item('display_time_format') );
			
			$this->db->join('cities','recruitment_applicant.pres_city = cities.city_id','left');
			$this->db->join('province','province.province_id = cities.province_id','left');
			$applicant = $this->db->get_where('recruitment_applicant', array('applicant_id' => $candidate->applicant_id ))->row();
			$applicant_education = $this->db->get_where('recruitment_applicant_education', array('applicant_id' => $candidate->applicant_id ))->row();


			if( !empty($applicant_education->school) ){ 
				$vars['school_name'] = $applicant_education->school;
			}
			else{
				$vars['school_name'] = "&nbsp;";
			}

			if( !empty($applicant_education->course) ){ 
				$vars['course'] = $applicant_education->course;
			}else{
				$vars['course'] = "&nbsp;";
			}

			$vars['address'] = $applicant->pres_address1;
			if( !empty($applicant->pres_address2) ) $vars['address'] .= '<br/>'. $applicant->pres_address2;
			if( !empty($applicant->pres_city) ) $vars['address'] .= '<br/>'. $applicant->city;
			if( !empty($applicant->province) ) $vars['address'] .= ', '. $applicant->province;
			if( !empty($applicant->zipcode) ) $vars['address'] .= ' '. $applicant->zipcode;
			if( empty($vars['address']) ) $vars['address'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			
			$vars['lastname'] = $applicant->lastname. ',';

			$vars['sss'] = $applicant->sss;
			$vars['tin'] = $applicant->tin;
			$vars['philhealth'] = $applicant->philhealth;
			$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate->mrf_id ))->row();
			
			$rank = $this->db->get_where('user_rank', array('job_rank_id' => $mrf->job_rank_id))->row();
			
			$logo_2 = get_branding();
			$company_id = $mrf->company_id;
			$company_qry = $this->db->get_where('user_company', array('company_id' => $company_id))->row();
			if(!empty($company_qry->logo)) {
			  $logo_2 = '<img alt="" src="./'.$company_qry->logo.'">';
			}
			$vars['logo_2'] = '<table style="width:100%">'.$logo_2.'</table>';

			$vars['div_dept'] = '';
			$vars['rank'] = $rank->job_rank;
			$applicant_name = '';
		

			if ($jo->is_internal == 0){
				$this->db->join('cities','recruitment_applicant.pres_city = cities.city_id','left');
				$this->db->join('province','province.province_id = cities.province_id','left');
				$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id' => $jo->applicant_id));
				if ($applicant_info && $applicant_info->num_rows() > 0){
					$applicant_info_row = $applicant_info->row();
					$applicant_name = $applicant_info_row->firstname.' '. $applicant_info_row->middleinitial. ' '. $applicant_info_row->lastname. ' '. $applicant_info_row->aux;
					$array_address[] = $applicant_info_row->pres_address1;
					$array_address[] = $applicant_info_row->pres_address2;
					$array_address[] = $applicant_info_row->city;
					$array_address[] = $applicant_info_row->province;
					$address = implode(',', array_filter($array_address));
					$salutation = ($applicant_info_row->sex == 'female' ? 'Ms' : 'Mr');
					$surname = $salutation. '. ' .$applicant_info_row->lastname;
					$sss = $applicant_info_row->sss;
					$tin = $applicant_info_row->tin;
				}
			}
			else{
				$this->db->join('employee','user.employee_id = employee.employee_id','left');
				$this->db->join('cities','employee.pres_city = cities.city_id','left');
				$this->db->join('province','province.province_id = cities.province_id','left');
				$user_info = $this->db->get_where('user',array('employee_id' => $jo->employee_id));
				if ($user_info && $user_info->num_rows() > 0){
					$user_info_row = $user_info->row();
					$applicant_name = $applicant_info_row->firstname.' '. $applicant_info_row->middleinitial. ' '. $applicant_info_row->lastname. ' '. $applicant_info_row->aux;
					$array_address[] = $user_info_row->pres_address1;
					$array_address[] = $user_info_row->pres_address2;
					$array_address[] = $user_info_row->city;
					$array_address[] = $applicant_info_row->province;
					$address = implode(',', array_filter($array_address));
					$salutation = ($user_info_row->sex == 'female' ? 'Ms' : 'Mr');
					$surname = $salutation. '. ' .$user_info_row->lastname;
					$sss = $user_info_row->sss;
					$tin = $user_info_row->tin;										
				}				
			}

			$campaign = $this->db->get_where('campaign', array('campaign_id' => $mrf->campaign_id ))->row();
			$vars['campaign'] = $campaign->campaign;
			$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
			$company = $this->db->get_where('user_company', array('company_id' => $position->company_id))->row();
			$department = $this->db->get_where('user_company_department', array('department_id' => $mrf->department_id))->row();
			

			$this->db->join('user', 'user.user_id=user_company_division.division_manager_id');
			$this->db->join('user_position', 'user.position_id=user_position.position_id');
			$div_head = $this->db->get_where('user_company_division', array('user_company_division.division_id' =>  $mrf->division_id))->row();
			$vars['division_head'] = strtoupper($div_head->salutation.' '.$div_head->firstname.' '.$div_head->middleinitial.' '.$div_head->lastname);
			$vars['division_position'] = $div_head->position;
			$meta = $this->config->item('meta');

			$vars['salutation'] = $salutation.'.';
			$vars['applicant_name'] = strtoupper($applicant_name);
			$vars['company'] = $meta['description'];
			$vars['department'] = $department->department;

			$this->db->join('user_position', 'user_position.position_id=user.position_id');
			$dept_head = $this->db->get_where('user',array("user_id"=>$department->dm_user_id))->row();

			$vars['dept_head'] = $dept_head->firstname .' '.$dept_head->lastname;
			$vars['dept_head_pos'] = $dept_head->position;

			$vars['position'] = $position->position;
			$vars['date_start'] = $vars['date_from'] = date( $this->config->item('display_date_format'), strtotime($jo->date_start) );
			$vars['date_end'] = $vars['date_to'] = date( $this->config->item('display_date_format'), strtotime($jo->date_end) );
			$facilitator = $this->hdicore->_get_userinfo( $this->user->user_id );
			$vars['facilitated_by'] = $facilitator->firstname.' '.$facilitator->lastname;


			$package = $this->db->get_where('recruitment_benefit_packages', array('deleted'=> 0, "recruitment_benefit_package_id" => $jo->benefit_package_id))->row();
			$vars['company_initiated'] = $package->description;

			$vars['government_mandated'] = '<table width="100%"><tbody><tr>
												<td >&nbsp;</td>
												<td>&nbsp;</td>
											</tr>';
			$vars['duties']   = $position->duties_responsibilities;
			$vars['company_initiated_total'] = 0;
			$vars['government_mandated_total'] = 0;
			$vars['total_plus_premium'] = 0;
			$vars['monthly_total'] = '';
			$vars['yearly_total'] = '';

			if($jo->date_from == "0000-00-00") $vars['date_from'] = '&nbsp;&nbsp;&nbsp;';
			if($jo->date_to == "0000-00-00") $vars['date_to'] = '&nbsp;&nbsp;&nbsp;';

			$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $jo->job_offer_id));

			$vars['government_mandated'] .= '</table></tbody>';
			$vars['monthly_total'] = number_format( $vars['company_initiated_total'] + $vars['government_mandated_total'] + $jo->basic, 2, '.', ',');
			$monthly = number_format($vars['company_initiated_total'] + $vars['government_mandated_total'] + $jo->basic,2,'.','');
			$vars['yearly_total'] = number_format( ($monthly * 12), 2, '.', ',');
			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.			
			$this->pdf->SetMargins(10, 20);
			$this->pdf->addPage('P', 'LETTER', true);
			$this->pdf->SetFontSize(11);						
			$this->pdf->setFont('Calibri');							
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' '.$template['subject'] . ' - '.$vars['candidate_id'].'.pdf', 'D');
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function change_status($status = null) {

		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else {
			if (is_null($status)) {
				$status = $this->input->post('status');
			}

			$this->db->where('job_offer_id', $this->input->post('record_id'));
			$this->db->join('recruitment_manpower_candidate', 'recruitment_manpower_candidate.candidate_id = recruitment_candidate_job_offer.candidate_id');			
			$result = $this->db->get('recruitment_candidate_job_offer');

			if ($result && $result->num_rows() > 0){
				$is_internal = $result->row()->is_internal;
			}

			$this->db->join('recruitment_manpower_candidate', 'recruitment_manpower_candidate.candidate_id = recruitment_candidate_job_offer.candidate_id');
			if ($is_internal):
				$this->db->join('user', 'user.user_id = recruitment_manpower_candidate.employee_id');				
			else:
				$this->db->join('recruitment_applicant', 'recruitment_applicant.applicant_id = recruitment_manpower_candidate.applicant_id');				
			endif;
			$this->db->where('job_offer_id', $this->input->post('record_id'));
			$this->db->where('recruitment_candidate_job_offer.deleted', 0);
			$record = $this->db->get('recruitment_candidate_job_offer')->row();

			$response->msg_type = 'success';
			switch ($status) {
				case 'accept':
					if ($record->job_offer_status_id == '1' || $record->job_offer_status_id == '0') {
						// $status = '2';
						// $candidate_status_id = 12;
						// $this->db->update('recruitment_manpower_candidate',
						// 	array('candidate_status_id' => 12),
						// 	array('candidate_id' => $record->candidate_id)
						// 	);
						$status = '3';
						$candidate_status_id = 13;
						$this->db->update('recruitment_manpower_candidate',
							array('candidate_status_id' => 13, 'jo_accepted_date' => date('Y-m-d')),
							array('candidate_id' => $record->candidate_id)
							);			
						if($is_internal){
							$this->db->insert('recruitment_preemployment', array('candidate_id' => $record->candidate_id, 'has_201' => 1));
						}else{
							$this->db->insert('recruitment_preemployment', array('candidate_id' => $record->candidate_id));
						}
					}
					$response->msg = 'Job offer accepted.';
					break;
				case 'reject':	
					$status = '4';	
					if ($record->job_offer_status_id == '1') {					
						$candidate_status_id = 15;	
					}
					
					$this->db->update('recruitment_manpower_candidate',
						array('candidate_status_id' => $candidate_status_id),
						array('candidate_id' => $record->candidate_id)
						);

					if (!$is_internal){
						$this->db->update('recruitment_applicant',
							array('application_status_id' => 5),
							array('applicant_id' => $record->applicant_id)
							);
					}

					if ($is_internal):
						$applicant_id = $record->employee_id;
					else:
						$applicant_id = $record->applicant_id;
					endif;

					$candidate_info = $this->db->get_where('recruitment_manpower_candidate',array('candidate_id' => $record->candidate_id))->row();

					$recent_position_applied = $this->db->get_where('recruitment_applicant_application', array( 'applicant_id'=>$applicant_id, 'lstatus' => 0 ))->row();

					$this->db->update('recruitment_applicant_application', array('lstatus' => 1 ), array('applicant_id' => $applicant_id));

					$data = array(
			            'applicant_id' => $applicant_id,
			            'position_applied' => $recent_position_applied->position_applied,
			            'applied_date' => date('Y-m-d H:i:s'),
			            'status' => 5,
			            'mrf_id' => 0
			        );

			        //save aaplication
					$this->db->insert('recruitment_applicant_application',$data);

					$this->db->insert('recruitment_applicant_history',
						array(
							'applicant_id' 			=> $applicant_id,
							'application_status_id' => 5,
							'candidate_status_id'   => $candidate_status_id,
							'remark'				=> $this->input->post('remark')
							)
						);

					$response->msg = 'Job offer rejected.';
					break;
				default:					
					$response->msg = 'Job offer prepared.';
			}

			//change status of applicant 
			$this->system->update_application_status( $applicant_id, $candidate_status_id);

			$this->db->where('job_offer_id', $this->input->post('record_id'));
			$this->db->update('recruitment_candidate_job_offer', array('job_offer_status_id' => $status));

			$this->load->view('template/ajax', array('json' => $response));
		}
	}
	
	function add_benefit_field(){
		$response->field = $this->load->view( $this->userinfo['rtheme'].'/'.$this->module_link.'/benefit_field', array('benefit_id' => $this->input->post('benefit_id')), true );

		$response->selected_benefits = $this->input->post('selected_benefits');
		if( ! empty( $response->selected_benefits ) )
			$response->selected_benefits .= ',' . $this->input->post('benefit_id');
		else
			$response->selected_benefits = $this->input->post('benefit_id');
		$this->db->where('deleted', 0);
		$this->db->where('benefit_id not in ('. $response->selected_benefits .')');
		$benefits = $this->db->get('benefit');
   	$response->benefitddlb = '<option value="">Select...</option>';
	  foreach($benefits->result() as $benefit):
      $response->benefitddlb .= '<option value="'.$benefit->benefit_id.'">'.$benefit->benefit.'</option>';
    endforeach;
		
		$this->load->view('template/ajax', array('json' => $response));
	}
	
	function get_benefit_field(){
		$response->field = '';
		$response->benefitddlb = '<option value="">Select...</option>';
		$response->selected_benefits = array();
		$job_offer_id = $this->db->get_where('recruitment_candidate_job_offer', array('candidate_id' => $this->input->post('job_offer_id'), 'deleted' => 0))->row();
		$benefits = $this->db->get_where('recruitment_candidate_job_offer_benefit', array('job_offer_id' => $job_offer_id->job_offer_id));
		if( $benefits->num_rows() > 0 ){
			foreach( $benefits->result() as $benefit ){				
				$response->field .= $this->load->view( $this->userinfo['rtheme'].'/'.$this->module_link.'/benefit_field', 
						array('benefit_id' => $benefit->benefit_id, 'value' => $benefit->value, 'hours' => $benefit->hours_required,'units' => $benefit->units), 
						true );
				$response->selected_benefits[] = $benefit->benefit_id;
			}
		}
		$response->selected_benefits = implode(',', $response->selected_benefits);
		$this->db->where('deleted', 0);
		if( !empty($response->selected_benefits) ) $this->db->where('benefit_id not in ('. $response->selected_benefits .')');
		$benefits = $this->db->get('benefit');
	  	if($benefits->num_rows() > 0){
		  foreach($benefits->result() as $benefit):
	      $response->benefitddlb .= '<option value="'.$benefit->benefit_id.'">'.$benefit->benefit.'</option>';
	    endforeach;
		}
		$this->load->view('template/ajax', array('json' => $response));
	}
	
	function get_template_form(){

		$this->db->join('recruitment_candidate_job_offer',$this->module_table.'.candidate_id = recruitment_candidate_job_offer.candidate_id');
		$jo = $this->db->get_where($this->module_table, array($this->module_table. '.' .$this->key_field => $this->input->post('record_id')));

		$response->form = $this->load->view( $this->userinfo['rtheme'].'/'.$this->module_link.'/template_form',array('jo' => $jo->row()), true );
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_benefits($candidate_id){
		$this->db->where('recruitment_candidate_job_offer.deleted',0);
		$this->db->where('candidate_id',$this->input->post('record_id'));
		$this->db->join('recruitment_candidate_job_offer_benefit','recruitment_candidate_job_offer_benefit.job_offer_id = recruitment_candidate_job_offer.job_offer_id');
		$this->db->join('benefit','benefit.benefit_id = recruitment_candidate_job_offer_benefit.benefit_id');
		$result = $this->db->get('recruitment_candidate_job_offer');
		return $result;
	}

	function get_rfp_detail(){

		$response->project_dept = '';

		$this->db->join('recruitment_manpower','recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id','left');
		$candidate_info = $this->db->get_where('recruitment_manpower_candidate',array("candidate_id" => $this->input->post('candidate_id')));

		if ($candidate_info && $candidate_info->num_rows() > 0){
			$candidate_info_row = $candidate_info->row();
			$department = $this->db->get_where('user_company_department', array('department_id' =>  $candidate_info_row->department_id))->row();
			// $cat_info = $this->system->get_recruitment_category($candidate_info_row->category_id,$candidate_info_row->category_value_id);
			$response->project_dept = $department->department; //$cat_info['cat_value'];
		}

		$this->load->view('template/ajax', array('json' => $response));		
	}	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */