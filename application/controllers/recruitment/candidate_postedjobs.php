<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Candidate_postedjobs extends MY_Controller
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

		$this->filter = $this->db->dbprefix . 'recruitment_manpower.status IN ("Approved","In-Process")';
		
		if (CLIENT_DIR == 'firstbalfour') {
			$this->filter = $this->db->dbprefix . 'recruitment_manpower.status NOT IN ("Declined","Cancelled", "Closed")';	
		}

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

        $tabs[] = '<li filter="posted_jobs" class="active"><a href="'.base_url().'recruitment/candidate_postedjobs">Posted Jobs<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['posted_jobs'].'</span></li>';
        $tabs[] = '<li filter="schedule"><a href="'.base_url().'recruitment/candidate_schedule">Candidates<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['schedule'].'</span></li>';
        $tabs[] = '<li filter="exam"><a href="'.base_url().'recruitment/candidate_result">Assessment Profile<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['result'].'</span></li>';
        $tabs[] = '<li filter="bcheck"><a href="'.base_url().'recruitment/candidate_background_check">Background Check<span id="postedjobs-counter" class="bg-orange ctr-inline">'.$array_tab_count['bcheck'].'</span></li>';
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
		$this->listview_qry .= ',recruitment_manpower.approved_date';
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
			
			if (CLIENT_DIR === 'oams') {
				if (!$this->is_recruitment() && !$this->is_superadmin && $user_access[$this->module_id]['post'] == 0 ) {
				
					$this->db->where('( '.$this->db->dbprefix('recruitment_manpower').'.request_id IN ( SELECT request_id 
					FROM '.$this->db->dbprefix('recruitment_manpower_approver').'
					WHERE approver = '.$this->userinfo['user_id'].' )  ) OR ( '.$this->db->dbprefix('recruitment_manpower').'.requested_by = '.$this->userinfo['user_id'].' ) OR ( '.$this->db->dbprefix('recruitment_manpower').'.created_by = '.$this->userinfo['user_id'].' )');

				}
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );	

			}

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
							}elseif ($detail['name'] == 'date_needed') {
                                $this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
                                $date = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);

                                //$timespan = timespan(strtotime($row['approved_date']), strtotime($date), TRUE);
                                if (time() < strtotime($row['approved_date'])){
                                	$time = strtotime($row['approved_date']);
                                }
                                else{
                                	$time = time();
                                }

                                $timespan = timespan($time, strtotime($date), TRUE);

                                if ($timespan['text'] == '') {
                                    if ($detail['status'] != 'Closed') {                                    
                                        $timespan = '<span class="red"><small>Deadline missed.</small></span>';
                                    }
                                } else {                                                                        
                                    $timespan = '<span class="timespan '. (($timespan['days'] <= 7) ? 'blue' : 'green') .'"><small>' . $timespan['text'] . ' left.</small></span>';
                                }

                                $cell[$cell_ctr] = $date; // . '<br />' . $timespan;
                                $cell_ctr++;                                                                                        
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

/*	function _set_listview_query($listview_id = '', $view_actions = true) {
		parent::_set_listview_query($listview_id, $view_actions);

		$this->listview_qry .= ','.$this->db->dbprefix.'recruitment_candidate_status.candidate_status,'.$this->db->dbprefix.'user_position.position,'.$this->db->dbprefix.'recruitment_manpower.position_id,'.$this->db->dbprefix.'recruitment_manpower_candidate.final_interviewer_id,'.$this->db->dbprefix.'recruitment_manpower_candidate.final_interview_date,'.$this->db->dbprefix.'recruitment_manpower_candidate.interviewer_id,';
		
	}*/

    function _default_grid_actions($module_link = "", $container = "", $row = array()) {

        // set default
        if ($module_link == "")
            $module_link = $this->module_link;
        if ($container == "")
            $container = "jqgridcontainer";

        // Right align action buttons.
        $actions = '<span class="icon-group">';
        //$actions .= '<a class="icon-button icon-16-search search-candidates" module_link="' . $module_link . '" tooltip="Candidates" href="javascript:void(0)"></a>';
        $actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';
        $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
        $actions .= '<a class="icon-button icon-16-next" tooltip="Next" href="'.base_url().'recruitment/candidate_schedule/index/'.$row['request_id'].'"></a>';
        $actions .= '</span>';

        return $actions;
    }

    function print_record($record_id = 0) {
        // Get from $_POST when the URI is not present.
        if ($record_id == 0) {
            $record_id = $this->input->post('record_id');
        }

        redirect(site_url('recruitment/manpower/print_record/' . $record_id));
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
	// END custom module funtions

}

/* End of file */
/* Location: system/application */