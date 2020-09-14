<?php

/**
 * Description of Leaves
 *
 * @author jconsador
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Candidate_procedure extends MY_Controller {
	function __construct() {
		parent::__construct();
		
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title         = $this->module_name;
		$this->listview_description   = '';
		$this->jqgrid_title           = "";
		$this->detailview_title       = '';
		$this->detailview_description = '';
		$this->editview_title         = 'Add/Edit ' . $this->module_name;
		$this->editview_description   = '';
		$this->default_sort_col       = array('date_created desc');
		
/*		if( in_array($this->method, array('index', 'listview')) ){

			if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1){
	            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";
	        }

	        //for approval
	        $leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '!= 1', 'in (0,1)' );
	        if( $leaves_to_approve ){
	        	if( $this->input->post('filter') && $this->input->post('filter') == "for_approval" ){
	        		$leaves_to_approve = $this->system->get_leaves_to_approve( $this->user->user_id, '!= 1', '= 1' );
	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 2";	
	        	}

	        	if( $this->input->post('filter') && $this->input->post('filter') == "approved" ){
	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 3";	
	        	}

	        	if( $this->input->post('filter') && $this->input->post('filter') == "disapproved" ){
	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 4";	
	        	}

	        	if( $this->input->post('filter') && $this->input->post('filter') == "cancelled" ){
	        		$this->filter = $this->db->dbprefix.$this->module_table.".employee_leave_id IN (".implode(',', $leaves_to_approve).") AND ".$this->db->dbprefix.$this->module_table.".form_status_id = 5";	
	        	}
	        }

	        if( $this->input->post('filter') && $this->input->post('filter') == "personal" ){
	            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";    
	        }

	        if( $this->input->post('filter') && $this->input->post('filter') == "employees" ){
	            $this->filter = $this->module_table.".employee_id <> {$this->user->user_id}";    
	        }        

	        if($this->input->post('filter') && $this->input->post('filter') == "subordinates" && $this->user_access[$this->module_id]['post'] == 1){
				$pos_subordinates = $this->db->get_where('user_position', array("reporting_to" => $this->userinfo['position_id']))->result();
				foreach($pos_subordinates as $pos_sub)
				{
					$sub_ids = $this->db->get_where('user', array("position_id" => $pos_sub->position_id))->result();
	        		foreach ($sub_ids as $sub_id) 
        				$subs[] = $sub_id->employee_id;
        		}
	        	$this->filter = $this->module_table.".employee_id IN (". implode(',', $subs) .")";
	        }

	        if( $this->input->post('filter') && $this->input->post('filter') == "for_validation" && ($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'])){
	            $this->filter = $this->module_table.".form_status_id = 6";    
	        }

	        if( $this->input->post('filter') && $this->input->post('filter') == "maternity" && ($this->user_access[$this->module_id]['post'] == 1 || $this->user_access[$this->module_id]['hr_health'])){
	            $this->filter = $this->module_table.".application_form_id = 5";    
	        }
        }*/
	}

	function _set_filter() {
		$this->db->where('IF(' . $this->db->dbprefix . $this->module_table .'.employee_id = ' . $this->userinfo['user_id'] . ', 1, '.$this->db->dbprefix . $this->module_table .'.form_status_id <> 1)', '', false);
	}

	// START - default module functions
	// default jqgrid controller method
	function index() {
		$data['scripts'][] = jqgrid_listview(); // load jqgrid js and default grid js
		$data['content'] = 'listview';
		$data['jqgrid'] = 'recruitment/candidates/jqgrid';

		//Tabs for Listview

        $tabs = array();
        $tabs[] = '<li filter="posted_jobs" class="active"><a href="javascript:void(0)">Request for Personnel</li>';
        $tabs[] = '<li filter="schedule"><a href="'.base_url().'recruitment/candidate_schedule">Interview  Schedule</li>';
        $tabs[] = '<li filter="exam"><a href="javascript:void(0)">Assesment Profile</li>';
        $tabs[] = '<li filter="job_offer"><a href="javascript:void(0)">Job Offer</li>';
        $tabs[] = '<li filter="contract_sign"><a href="javascript:void(0)">For Hiring</li>';
        $tabs[] = '<li filter="others"><a href="javascript:void(0)">Others</li>';
        $tabs[] = '<li filter="archive"><a href="javascript:void(0)">Archive</li>';

        if( sizeof( $tabs ) > 1 ) $data['candidate_tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');

		if ($this->session->flashdata('flashdata')) {
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'] . '/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = 'init_filter_tabs();';

		//load variables to env
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function detail() {
		parent::detail();

		//additional module detail routine here
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/detailview.js"></script>';

		$data['content'] = 'leaves/detailview';
		$data['record'] = $this->db->get_where($this->module_table, array( $this->key_field => $this->key_field_val ))->row();
		//other views to load
		$data['views'] = array();

		//load variables to env			
		$this->load->vars($data);

		//load the final view
		//load header
		$this->load->view($this->userinfo['rtheme'] . '/template/header');
		$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

		//load page content
		$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

		//load footer
		$this->load->view($this->userinfo['rtheme'] . '/template/footer');
	}

	function edit() {
		if ($this->user_access[$this->module_id]['edit'] == 1) {
			$this->load->helper('form');
			if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
			
			if( $this->input->post('record_id') != -1 ){
				//check status
				$rec = $this->db->get_where( $this->module_table, array( $this->key_field => $this->input->post('record_id') ) )->row();
				if( $rec->form_status_id != 1 ){
					if( $rec->application_form_id != 5 && $this->user_access[$this->module_id]['hr_health'] != 1 ){
						$this->session->set_flashdata( 'flashdata', 'Data is locked for editing, please call the Administrator.' );
						redirect( base_url().$this->module_link.'/detail/'. $this->input->post('record_id') );
					}
				}
				$data['approvers'] = $this->system->get_approvers_and_condition( $rec->employee_id, $this->module_id );
			}

			parent::edit();

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview.js"></script>';
			$data['scripts'][] = multiselect_script();

			if (!empty($this->module_wizard_form) && $this->input->post('record_id') == '-1') {
				$data['show_wizard_control'] = true;
				$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'leaves/editview';
			if( $_POST['filter'] == "employees" || $_POST['filter'] == "personal" || $_POST['filter'] == "for_approval" || $rec->form_status_id == 1 )
				$data['buttons'] = 'leaves/template/send-request';
			
			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			if ($this->input->post('record_id') != '-1') {
				$this->db->where($this->key_field, $this->input->post('record_id'));
				$record = $this->db->get($this->module_table)->row_array();

				$data['email_sent'] = $record['email_sent'];
			}

			if( $this->input->post('record_id') != '-1' ){
				$dates_affected = $this->db->get_where('employee_leaves_dates', array( $this->key_field => $this->key_field_val));
				$data['dates_affected'] = $dates_affected->result_array();
			}

			//load variables to env
			$this->load->vars($data);

			//load the final view
			//load header
			$this->load->view($this->userinfo['rtheme'] . '/template/header');
			$this->load->view($this->userinfo['rtheme'] . '/template/header-nav');

			//load page content
			$this->load->view($this->userinfo['rtheme'] . '/template/page-content');

			//load footer
			$this->load->view($this->userinfo['rtheme'] . '/template/footer');
		} else {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function ajax_save() {


	}

	function delete() {

		$record = $this->db->get_where('employee_leaves', array('employee_leave_id'=>$this->input->post('record_id')))->row();

		if( $record->form_status_id != 3 ){

			parent::delete();

		}
		else{

			$response['msg'] = 'Cannot delete approved leaves';
			$response['msg_type'] = 'attention';		
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

		}
	}

	// END - default module functions
	// START custom module funtions

	private function _custom_ajax_save() {
		if (IS_AJAX) {		
		}
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

        $buttons .= "</div>";
                
		return $buttons;
	}

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		// Right align action buttons.
		$actions = '<span class="icon-group">';

/*		if ($this->user_access[$this->module_id]['print']) {
			$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if ($this->user_access[$this->module_id]['edit']) {
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if($this->user_access[$this->module_id]['edit'])
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';

		if ($this->user_access[$this->module_id]['delete']) {
			$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		}*/

		$actions .= '<a class="icon-button icon-16-application-small goto-schedule" tooltip="Next" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		$actions .= '</span>';

		return $actions;
	}
	/* START List View Functions */
	/**
	 * Available methods to override listview.
	 * 
	 * A. _append_to_select() - Append fields to the SELECT statement via $this->listview_qry
	 * B. _set_filter()       - Add aditional WHERE clauses	 
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

		//$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;
		$view_actions = true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions );

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		$this->db->select("request_id, requested_date, CONCAT(firstname, ' ',lastname) as requested_by, position, date_needed, status, document_number, manpower_priority, status, approved_by",false);
		$this->db->join("user","user.user_id = recruitment_manpower.requested_by");
		$this->db->join("user_position","user_position.position_id = recruitment_manpower.position_id");
		$this->db->join("recruitment_manpower_priority","recruitment_manpower_priority.manpower_priority_id = recruitment_manpower.status_id");
		$this->db->from("recruitment_manpower");
		$this->db->where('recruitment_manpower.deleted = 0 AND '.$search);			

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

			$this->db->select("request_id, requested_date, CONCAT(firstname, ' ',lastname) as requested_by, position, date_needed, status, document_number, manpower_priority, status, approved_by",false);
			$this->db->join("user","user.user_id = recruitment_manpower.requested_by");
			$this->db->join("user_position","user_position.position_id = recruitment_manpower.position_id");
			$this->db->join("recruitment_manpower_priority","recruitment_manpower_priority.manpower_priority_id = recruitment_manpower.status_id");
			$this->db->from("recruitment_manpower");
			$this->db->where('recruitment_manpower.deleted = 0 AND '.$search);		
			
			if($sidx != ""){
				$this->db->order_by($sidx, $sord);
			}

			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);
			
			$result = $this->db->get();

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
								$cell[$cell_ctr] =  $row[$detail['name']];
								$cell_ctr++;
							}
						}
						$response->rows[$ctr]['id'] = $row['request_id'];
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}
		
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function _set_listview_query(){
		$this->listview_columns = array();
		$this->listview_column_names = array();

		$this->listview_columns[] = array("name" =>  'requested_date', "width" => 100, "align" => "center", 'encrypt' => 0);		
		$this->listview_columns[] = array("name" =>  'requested_by', "width" => 100, "align" => "center", 'encrypt' => 0);		
		$this->listview_columns[] = array("name" =>  'position', "width" => 100, "align" => "center", 'encrypt' => 0);		
		$this->listview_columns[] = array("name" =>  'date_needed', "width" => 100, "align" => "center", 'encrypt' => 0);		
		$this->listview_columns[] = array("name" =>  'status', "width" => 100, "align" => "center", 'encrypt' => 0);		
		$this->listview_columns[] = array("name" =>  'document_number', "width" => 100, "align" => "center", 'encrypt' => 0);		
		$this->listview_columns[] = array("name" =>  'manpower_priority', "width" => 100, "align" => "center", 'encrypt' => 0);		
		$this->listview_columns['action'] = array(
			"name" => "action",
			"align" => "center",
			"width" => 100,
			"sortable" => 'false',
			"classes" => "td-action"
		);		

		$this->listview_column_names[] = "Requested Date";		
		$this->listview_column_names[] = "Requested By";		
		$this->listview_column_names[] = "Position Title";
		$this->listview_column_names[] = "Date Needed";
		$this->listview_column_names[] = "Status";
		$this->listview_column_names[] = "Document Number";
		$this->listview_column_names[] = "Priority";
		$this->listview_column_names[] = "Actions";
	}
}

/* End of file */
/* Location: system/application */
