<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Candidate_background_check extends MY_Controller
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

		// $this->filter = $this->db->dbprefix . 'recruitment_manpower.status IN ("Approved","In-Process")';
		
		// if (CLIENT_DIR == 'firstbalfour') {
		// 	$this->filter = $this->db->dbprefix . 'recruitment_manpower.status NOT IN ("Declined","Cancelled", "Closed")';	
		// }
		

        $this->load->helper('recruitment');
        $statuses = get_candidate_statuses();
        
        $data['module_filter_title'] = 'Candidates';
		$data['module_filters'] = get_candidate_filters($statuses);
		$this->load->vars($data); 		
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

        $tabs = array();

        $tabs[] = '<li filter="posted_jobs" ><a href="'.base_url().'recruitment/candidate_postedjobs">Posted Jobs<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['posted_jobs'].'</span></li>';
        $tabs[] = '<li filter="schedule"><a href="'.base_url().'recruitment/candidate_schedule">Candidates<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['schedule'].'</span></li>';
        $tabs[] = '<li filter="exam"><a href="'.base_url().'recruitment/candidate_result">Assessment Profile<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['result'].'</span></li>';
      	$tabs[] = '<li filter="bcheck" class="active"><a href="'.base_url().'recruitment/candidate_background_check">Background Check<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['bcheck'].'</span></li>';
        $tabs[] = '<li filter="job_offer"><a href="'.base_url().'recruitment/candidate_job_offer">Job Offer <span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['joboffer'].'</span></li>';
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
			
		$this->db->where('candidate_id', $this->input->post('record_id'));
		$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = recruitment_manpower_candidate.mrf_id');
		$record = $this->db->get('recruitment_manpower_candidate')->row();

		$data['applicant_id'] = $record->applicant_id;

		$background_check_record = $this->db->get_where('recruitment_candidate_background_check', array('candidate_id' => $record->candidate_id, 'department_id' => $record->department_id, 'considered_position' => $record->position_id, 'deleted' => 0));

		if ($background_check_record && $background_check_record->num_rows() > 0) {
			$bc_record = $background_check_record->row();
			$data['record_id'] = $bc_record->candidate_background_check_id;
			$data['reference_ids'] = $bc_record->reference_id;
			$data['record_questions'] = json_decode($bc_record->questionnaires, true);
			$where  = "record_id IN(".$bc_record->reference_id.")";
			$this->db->where($where);
			$this->db->where('deleted', 0);
			$references = $this->db->get('recruitment_applicant_references');
			if ($references && $references->num_rows() > 0) {
				$data['references'] = $references->result();
			}
			$data['ref_q'] = $this->_questionnaires();
			// $data['mrf_details'] = $this->get_mrf_details($record->applicant_id);

		}else{
			$data['record_id'] = -1;
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


			$this->db->where('candidate_id', $this->input->post('record_id'));
			$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = recruitment_manpower_candidate.mrf_id');
			$record = $this->db->get('recruitment_manpower_candidate')->row();

			$data['candidate_id'] = $record->candidate_id;

			$background_check_record = $this->db->get_where('recruitment_candidate_background_check', array('candidate_id' => $record->candidate_id, 'department_id' => $record->department_id, 'considered_position' => $record->position_id, 'deleted' => 0));

			if ($background_check_record && $background_check_record->num_rows() > 0) {
				$bc_record = $background_check_record->row();
				$data['record_id'] = $bc_record->candidate_background_check_id;
				$data['reference_ids'] = $bc_record->reference_id;

				// $data['mrf_details'] = $this->get_mrf_details($record->applicant_id);

			}else{
				$data['record_id'] = -1;
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
		else{
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function ajax_save()
	{
		$this->module_table = 'recruitment_candidate_background_check';
		// $this->key_field = 'candidate_background_check_id';

		parent::ajax_save();

		$this->db->where('candidate_id', $this->key_field_val);
		$this->db->update($this->module_table, array( 'questionnaires' => json_encode($this->input->post('questions')), 
													  'candidate_status_id' => 4,
													  'reference_id' => implode(',', $this->input->post('reference_id') ) ));

		//additional module save routine here  

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function send_email() {

		$this->db->where('candidate_id', $this->input->post('record_id'));
		$request = $this->db->get($this->module_table);
		
		if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
			
			// 

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				$candidate_info = $request->row_array();
				

				$mrf = $this->db->get_where('recruitment_manpower', array('request_id' => $candidate_info['mrf_id'] ))->row();
				
				$position = $this->db->get_where('user_position', array('position_id' => $mrf->position_id))->row();
				$candidate_info['position'] = $position->position;

				$logo = get_branding();
				$company_qry = $this->db->get_where('user_company', array('company_id' => $mrf->company_id))->row();
				if(!empty($company_qry->logo)) {
				  $logo = '<img alt="" style="width:70%" src="./'.$company_qry->logo.'">';
				}


				$applicant_info = $this->db->get_where('recruitment_applicant',array('applicant_id' => $candidate_info['applicant_id']))->row();

				$references = $this->db->get_where('recruitment_applicant_references',array('applicant_id' => $candidate_info['applicant_id']));

				// Load the template.        
				$this->load->model('template');
				$template = $this->template->get_module_template('', 'background_check');		
				
				$candidate_info['position'] = $position->position;

				$ref = $this->_questionnaires();
	
				$questionnaires = $ref['questionnaires'];
				$html = "<table>";
				foreach ($questionnaires['questions'] as $title_key => $title)
				{
					$html .= "<tr><td><b>".$title."</b></td></tr>";
					foreach ($questionnaires[$title_key] as $key => $question) {
					$html .= "<tr><td>".$question->description."</td></tr><tr><td >&nbsp;</td></tr>";
						}	
				}
				
				$html .= "</table>";	

				$candidate_info['background_check'] = $html;
				$candidate_info['company_logo'] = $logo;
				if ($references && $references->num_rows() > 0) {
					
				    foreach ($references->result() as $key => $value) {
				    	$candidate_info['reference'] = $value->name;

				    	$message = $this->template->prep_message($template['body'], $candidate_info);

				    	$recepients = $value->email;
				    	$this->template->queue($recepients, '', $template['subject'], $message);

				    }
					
					$response->msg_type = 'success';
					$response->msg = 'Email Sent.';

				}else{

					$response->msg_type = 'notice';
					$response->msg = 'Sending failed.';
					$response->record_id = $this->input->post('record_id');
				}
				
			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
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
		// $this->listview_qry .= ',recruitment_manpower.approved_date';
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

	function _set_filter() {
		$this->db->where('t1.candidate_status_id ','4');
	}

	function _default_grid_actions( $module_link = "",  $container = "", $row = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
       
        if( $row['is_internal'] == 0 ){
			$actions .= '<a class="icon-button icon-16-users" candidate_id="'.$row['candidate_id'].'" module_link="' . $module_link . '" tooltip="View Applicant Details" href="javascript:void(0)"></a>';
		}

        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        // if ($this->user_access[$this->module_id]['delete']) {
        //     $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        // }

        if ( $this->user_access[$this->module_id]['post']) {
			$actions .= '<a class="icon-button icon-16-send-email" module_link="' . $module_link . '" tooltip="Send To Character Reference" href="javascript:void(0)"></a>';
		}

        $actions .= '<a class="icon-button icon-16-approve" tooltip="Endorsed to Job Offer" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="'.$row['candidate_id'].'"></a>';
			$actions .= '<a class="icon-button icon-16-disapprove" tooltip="Not Qualified" href="javascript:void(0)" module_link="' . $module_link . '" candidate_id="'.$row['candidate_id'].'"></a>';	

        $actions .= '</span>';

		return $actions;
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

	function get_mrf_details($applicant_id)
	{
		if ($this->input->post('applicant_id')) {
			$applicant_id = $this->input->post('applicant_id');
		}
		$ref_ids = array();
		if ($this->input->post('reference_ids') && $this->input->post('reference_ids') != 'undefined') {
			$ref_ids = explode(',', $this->input->post('reference_ids'));
		}
		$this->db->where('recruitment_manpower.deleted' , 0);
		$this->db->where('recruitment_manpower_candidate.applicant_id' , $applicant_id);
		$this->db->join('recruitment_manpower_candidate' , 'recruitment_manpower_candidate.mrf_id = recruitment_manpower.request_id');
		$mrf_details = $this->db->get('recruitment_manpower');
		$mrf = $mrf_details->row();

		$this->db->where('applicant_id', $applicant_id);
		$this->db->where('deleted', 0);
		$references = $this->db->get('recruitment_applicant_references');

		if (IS_AJAX) {
			$option = '<select id="reference_id" multiple="multiple" class="multi-select" style="width:400px;" name="reference_id[]">';
			$reference_name = array();
			if ($references && $references->num_rows() > 0) {
				foreach ($references->result() as $reference) {
					if (in_array($reference->record_id, $ref_ids)) {
						$option .= "<option value='".$reference->record_id."' selected='selected'> ".$reference->name." </option>";
						$reference_name[] = $reference->name;
					}else{
						$option .= "<option value='".$reference->record_id."'> ".$reference->name." </option>";
					}
					
				}
			}
			$option .= '</select>';
			$response->references = $option;

			$response->reference_name = implode(',', $reference_name);
			$response->department_id = $mrf->department_id;
			$response->position_id = $mrf->position_id;

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}else{

			$details = array();
			$details['mrf'] = $mrf;
			$details['reference'] = $references;

			return $details;
		}
		
	}

	function get_reference_info()
	{

		$ref = $this->_questionnaires();

		if ($this->input->post('record_id') != -1) {
			$record_details = $this->db->get_where('recruitment_candidate_background_check', array('candidate_background_check_id' => $this->input->post('record_id')))->row();
			$ref['record_questions'] = json_decode($record_details->questionnaires, true);


		}

		$where  = "record_id IN(".$this->input->post('reference_id').")";
		$this->db->where($where);
		$this->db->where('deleted', 0);
		$references = $this->db->get('recruitment_applicant_references');

		
		
		if ($references && $references->num_rows() > 0) {
			$ref['references'] = $references->result();
		}

		$response = $this->load->view($this->userinfo['rtheme'] . '/recruitment/candidates/background_check/form',$ref);
		$data['html'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	private function _questionnaires()
	{
		
		$relationships = $this->db->get_where('recruitment_background_relationship', array('deleted'=> 0));
		$efficiencies = $this->db->get_where('recruitment_background_efficiency', array('deleted'=> 0));
		$interpersonals = $this->db->get_where('recruitment_background_interpersonal', array('deleted'=> 0));
		$strengths = $this->db->get_where('recruitment_background_strengths', array('deleted'=> 0));
		$weaknesses = $this->db->get_where('recruitment_background_weakness', array('deleted'=> 0));
		$integrities = $this->db->get_where('recruitment_background_integrity ', array('deleted'=> 0));
		$others = $this->db->get_where('recruitment_background_others', array('deleted'=> 0));

		$references['questionnaires']['questions'] = array('relationships' => 'Relationship' ,
										'efficiencies' => 'Efficiency at Work', 
										'interpersonals' => 'Interpersonal Skills',
										'strengths' => 'Strengths/Technical Knowledge',
										'weaknesses' => 'Weaknesses/Candidates\' Potential',
										'integrities' => 'Integrity',
										'others' => 'If Applicable');

		$references['questionnaires']['relationships'] = ($relationships && $relationships->num_rows() > 0) ? $relationships->result() : array();
		$references['questionnaires']['efficiencies'] = ($efficiencies && $efficiencies->num_rows() > 0) ? $efficiencies->result() : array();
		$references['questionnaires']['interpersonals'] = ($interpersonals && $interpersonals->num_rows() > 0) ? $interpersonals->result() : array();
		$references['questionnaires']['strengths'] = ($strengths && $strengths->num_rows() > 0) ? $strengths->result() : array();
		$references['questionnaires']['weaknesses'] = ($weaknesses && $weaknesses->num_rows() > 0) ? $weaknesses->result() : array();
		$references['questionnaires']['integrities'] = ($integrities && $integrities->num_rows() > 0) ? $integrities->result() : array();
		$references['questionnaires']['others'] = ($others && $others->num_rows() > 0) ? $others->result() : array();


		return $references;
	}

	function change_status($status = null)
	{
		if (IS_AJAX) {

			if (is_null($status)) {
				$status = $this->input->post('status');
			}
			$candidate_id = $this->input->post('candidate_id');

			$this->db->where('candidate_id', $candidate_id);
			$record = $this->db->get('recruitment_manpower_candidate')->row();
			$is_internal = $result->is_internal;

			switch ($status) {
				case 'accept':

					$this->db->update('recruitment_manpower_candidate',
							array('candidate_status_id' => 5),
							array('candidate_id' => $record->candidate_id)
							);		
					$response->msg = 'Candidate endorsed for job offer.';
					break;

				case 'reject':	
					$this->db->update('recruitment_manpower_candidate',
						array('candidate_status_id' => 23),
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

					$response->msg = 'Contract rejected.';
					break;
				
			}

			$this->load->view('template/ajax', array('json' => $response));
		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
		
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */