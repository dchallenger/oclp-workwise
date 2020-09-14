<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Request_training extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Request to Attend Training';
		$this->listview_description = 'This module lists all training requests.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a training request.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a training request.';
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
		// Do this to make sure employee id is not altered.
		if ($this->input->post('record_id') != '-1') {
			$this->db->where($this->key_field, $this->input->post('record_id'));
			$record = $this->db->get($this->module_table)->row();

			$_POST['employee_id'] = $record->employee_id;
		}

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
	function after_ajax_save()
	{	
		if ($this->get_msg_type() == 'success') {
			$data['updated_by']   = $this->userinfo['user_id'];
			$data['updated_date'] = date('Y-m-d H:i:s');			

			if ($this->input->post('record_id') == '-1') {
				$data['created_by']   = $this->userinfo['user_id'];
				$data['created_date'] = date('Y-m-d H:i:s');

				$data['employee_id']  = $this->userinfo['user_id'];
			}

			$this->db->set('total_cost', 'registration_cost + other_cost', FALSE);
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $data);
		}

		parent::after_ajax_save();
	}

	function _default_grid_actions($module_link = "", $container = "", $row = array()) {

		// set default
		if ($module_link == "")
			$module_link = $this->module_link;
		if ($container == "")
			$container = "jqgridcontainer";

		// Right align action buttons.
		$actions = '<span class="icon-group">';

		$actions .= '<a class="icon-button icon-16-info" module_link="' . $module_link . '" tooltip="View" href="javascript:void(0)"></a>';

		if ($this->user_access[$this->module_id]['edit']) {
			$actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}

		if ($this->user_access[$this->module_id]['print']) {
			$actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" href="javascript:void(0)" module_link="' . $module_link . '" ></a>';
		}		

		if ($this->userinfo['user_id'] == $row['approved_by'] && !$row['approved']) {
			$actions .= '<a class="icon-button icon-16-approve approve-single" tooltip="Approve" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
			$actions .= '<a class="icon-button icon-16-disapprove reject-single" tooltip="Reject" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		}		

		if ($this->user_access[$this->module_id]['delete']) {
			$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="' . $container . '" module_link="' . $module_link . '" href="javascript:void(0)"></a>';
		}

		$actions .= '</span>';

		return $actions;
	}	

	function _set_listview_query( $listview_id = '', $view_actions = true )	{
		parent::_set_listview_query( $listview_id, $view_actions );

		$this->listview_qry .= ', approved_by';
	}

	function change_status() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		} else {
			$record_id = $this->input->post('record_id');
			$approved  = $this->input->post('approved');

			if (!isset($record_id) && $record_id <= 0) {
				$response->msg      = 'No record specified.';
				$response->msg_type = 'error';				
			} else {
				$this->db->where($this->key_field, $record_id);
				
				if (!$this->db->update($this->module_table, array('approved' => $approved))) {
					$response->msg_type = 'error';
					$response->msg      = 'Update failed.';
				} else {
					$response->msg_type = 'success';
					$response->msg      = 'Update success.';
				}
			}

			$this->load->view('template/ajax', array('json' => $response));
		}
	}

	function print_record($record_id = 0) {
		// Get from $_POST when the URI is not present.
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'request_to_attend_training');

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);

			$this->db->where($this->key_field, $record_id);
			$this->db->where($this->module_table . '.deleted', 0);
			$this->db->join('user', 'user.employee_id = ' . $this->module_table . '.employee_id', 'left');			
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');			

			$record = $this->db->get($this->module_table)->row();

			$vars['employee']     = $record->firstname . ' ' . $record->lastname;
			$vars['department']   = $record->department;
			$vars['created_date'] = date($this->config->item('display_date_format'), strtotime($record->created_date));
			$vars['position'] 	  = $record->position;

			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Request to Attend Training.pdf', 'D');
		}
	}	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */