<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Memorandum extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Memorandums and Employee Updates';
		$this->listview_description = 'This module lists all defined memorandum(s).';
		$this->jqgrid_title = "Memorandum List";
		$this->detailview_title = 'Memorandum Info';
		$this->detailview_description = 'This page shows detailed information about a particular memorandum';
		$this->editview_title = 'Memorandum Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about memorandums.';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
	{
		$data['memo_types_tab'] = $this->system->memo_type_filtertabs(); //get memo types/fiters
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = $this->module_link.'/listview';
		$data['jqgrid'] = $this->module_link.'/jqgrid';

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//set default columnlist
		$this->_set_listview_query();

		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();

		//set load jqgrid loadComplete callback
		$data['jqgrid_loadComplete'] = 'init_filter_tabs();';

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
		
	function listview(){
		$this->filter = '';
		if( $this->user_access[$this->module_id]['edit'] != 1 ){
			$this->filter = $this->db->dbprefix.$this->module_table.'.publish = 1 AND '.$this->db->dbprefix.'memo_recipient.user_id = '.$this->user->user_id;
		}

		if( $this->input->post('filter') )  $this->filter .= (!empty($this->filter) ? ' AND ' : '') . $this->db->dbprefix.$this->module_table.'.memo_type_id = '.$this->input->post('filter');
		parent::listview();
	}

	function _custom_join(){
		if( $this->user_access[$this->module_id]['edit'] != 1 ){
			$this->db->join('memo_recipient', 'memo_recipient.memo_id = memo.memo_id', 'left');
		}
	}

	function detail()
	{
		parent::detail();

		//additional module detail routine here
		//update memo view
		//check if already viewed by user
		$user_view = $this->db->get_where('memo_viewers', array($this->key_field => $this->key_field_val, 'user_id' => $this->user->user_id));
		if( $user_view->num_rows() == 0) $this->db->insert('memo_viewers', array( $this->key_field => $this->key_field_val, 'user_id' => $this->user->user_id) );
		
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
	
	function view_memo()
	{
		if(IS_AJAX){
			if($this->user_access[$this->module_id]['view'] == 1){
				$response->memo_detail = "";
				$response->msg = ""; 
				if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
		
				if( $this->input->post( 'record_id' ) ){
					$memo = $this->db->get_where('memo', array('deleted' => 0, $this->key_field => $this->input->post( 'record_id' )));
					if( $memo->num_rows() == 1 ){
						$data['memo'] = $memo->row();
						$response->memo_detail = $this->load->view( $this->userinfo['rtheme'].'/'.$this->module_link.'/memo_detail', $data, true );
						$user_view = $this->db->get_where('memo_viewers', array($this->key_field => $this->input->post( 'record_id' ), 'user_id' => $this->user->user_id));
						if( $user_view->num_rows() == 0) $this->db->insert('memo_viewers', array( $this->key_field => $this->input->post( 'record_id' ), 'user_id' => $this->user->user_id) );
					}
					else if( $memo->num_rows() ==  0){
						$response->msg = "No record was found!";
						$response->msg_type = 'error';
					}
					else{
						$response->msg = "Inconsistent data found!<br/>Please contact the System Administrator.";
						$response->msg_type = 'error';
					}
				}
				else{
					$response->msg = 'Insufficient data supplied!<br/>Please contact the System Administrator.';
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}	
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
		unset( $_POST['created_by'] );
		if( $this->input->post('record_id') && $this->input->post('record_id') == "-1" ) $_POST['created_by'] = $this->user->user_id;
		$_POST['modified_by'] = $this->user->user_id;	
		$_POST['modified_date'] = date('Y-m-d H:i:s');

		$memo_detail = $this->input->post('memo_body');

		if( $this->input->post('company_recipients') ){
			$company_recipients = implode(',', $_POST['company_recipients'] );
			$_POST['company_recipients'] = $company_recipients;
		}
		if( $this->input->post('recipients') ){
			$recipients = implode(',', $_POST['recipients'] );
			$_POST['recipients'] = $recipients;
		}
		parent::ajax_save();

		$memo_detail = str_replace("<ul>", "<ul class='memo_details'>", $memo_detail);
		$this->db->update('memo',array('memo_body'=>$memo_detail),array('memo_id'=>$this->key_field_val));

		//additional module save routine here
		$this->db->delete('memo_recipient', array($this->key_field => $this->key_field_val));
		$recipients = $this->input->post('recipients');
		$recipients = explode(',', $recipients);
		foreach( $recipients as $recipient ){
			$insert = array(
				$this->key_field => $this->key_field_val,
				'user_id' => $recipient
			);
			$this->db->insert('memo_recipient', $insert);
		}
	}
	

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function get_company() {
		$record_id = $this->input->post('record_id');
		$company = $this->db->query("SELECT company_id, company FROM {$this->db->dbprefix}user_company WHERE deleted = 0")->result_array();
		$company_html = '<select id="company_recipients" multiple="multiple" class="multi-select" name="company_recipients[]">';
		if($record_id != '-1') {
			$memo = $this->db->query("SELECT * FROM {$this->db->dbprefix}memo WHERE memo_id = '{$record_id}'")->row();
			$company_recipients = explode(',', $memo->company_recipients);
			foreach($company as $company_id => $company_value) {
				if(in_array($company_value["company_id"], $company_recipients)) {
					$company_html .= '<option selected value="'.$company_value["company_id"].'">'.$company_value["company"].'</option>';
				} else {
					$company_html .= '<option value="'.$company_value["company_id"].'">'.$company_value["company"].'</option>';
				}
			}
		} else {
			foreach($company as $company_id => $company_value) {
				$company_html .= '<option value="'.$company_value["company_id"].'">'.$company_value["company"].'</option>';
			}
		}
		$company_html .= '</select>'; 
		$response->company_html = $company_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);  
	}

	function get_employee() {
		$record_id = $this->input->post('record_id');
		if(isset($_POST['company_recipients'])) {
			$company_arr = array();
			foreach ($_POST['company_recipients'] as $value) {
				$company_arr[] = $value;    
			}
			$company_id = implode(',', $company_arr);
		}
		$company_recipients = ' AND 1';
		if(!empty($company_id)) {
			$company_recipients = ' AND a.company_id IN ('.$company_id.')';
		}
		$user = $this->db->query("SELECT a.*, b.division
								FROM {$this->db->dbprefix}user a
								LEFT JOIN {$this->db->dbprefix}user_company_division b on b.division_id = a.division_id
								where a.deleted = 0 AND a.inactive = 0 AND a.user_id NOT IN (1,2,3) {$company_recipients} ORDER BY lastname, firstname ")->result_array();
		$employee_html = '<select id="recipients" multiple="multiple" class="multi-select" name="recipients[]">';
		if($record_id != '-1') {
			$memo = $this->db->query("SELECT * FROM {$this->db->dbprefix}memo WHERE memo_id = '{$record_id}'")->row();
			$user_recipients = explode(',', $memo->recipients);
			foreach($user as $user_id => $user_value) {
				if(in_array($user_value["user_id"], $user_recipients)) {
					$employee_html .= '<option selected value="'.$user_value["user_id"].'">'.$user_value["firstname"].' '.$user_value["middleinitial"].' '.$user_value["lastname"].' '.$user_value["aux"].'</option>';
				} else {
					$employee_html .= '<option value="'.$user_value["user_id"].'">'.$user_value["firstname"].' '.$user_value["middleinitial"].' '.$user_value["lastname"].' '.$user_value["aux"].'</option>';
				}
			}
		} else {
			foreach($user as $user_id => $user_value) {
				$employee_html .= '<option value="'.$user_value["user_id"].'">'.$user_value["firstname"].' '.$user_value["middleinitial"].' '.$user_value["lastname"].' '.$user_value["aux"].'</option>';
			}
		}
		$employee_html .= '</select>'; 
		$response->employee_html = $employee_html;
        $data['json'] = $response;
        $this->load->view('template/ajax', $data);
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */