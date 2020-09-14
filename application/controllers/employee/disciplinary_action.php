<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Disciplinary_action extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Disciplinary Action';
		$this->listview_description = 'This module lists disciplinary actions.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a disciplinary action';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a disciplinary action';

		if( $this->user_access[$this->module_id]['post'] != 1){
			$this->filter = $this->db->dbprefix.'employee_da.employee_id = '.$this->user->user_id;	
		}

		if(!$this->is_superadmin && !$this->is_admin && $this->user_access[$this->module_id]['post'] != 1){
			$this->filter = 'u.company_id = '.$this->userinfo['company_id'];
        }

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

		$data['buttons'] = $this->module_link . '/da-view-buttons';

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
			
			$data['buttons'] = $this->module_link . '/da-edit-buttons';



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
		if ($this->input->post('record_id') == '-1') {
			$_POST['date_created'] 		= date($this->config->item('edit_date_format'));
			$_POST['date_created-temp'] = date($this->config->item('edit_date_format'));

			$this->db->where('user_id', $this->input->post('employee_id'));
			$employee = $this->db->get('user')->row();

			$this->db->where('user_id', $this->input->post('reported_by'));
			$reporter = $this->db->get('user')->row();

			$_POST['employee_position_id'] = $employee->position_id;
			$_POST['reporter_position_id'] = $reporter->position_id;
		}

		parent::ajax_save();

		// echo $this->input->post('record_id');
		// foreach($this->input->post('sanction-date') as $sanction_date){
		if(count($this->input->post('sanction-date')) > 0) {
			// $sd = implode(',',$this->input->post('sanction-date'));
			$this->db->delete('sanction_date', array('da_id' => $this->key_field_val));

			foreach($this->input->post('sanction-date') as $sanction_date) {
				$this->db->set('sanction_date', date('Y-m-d', strtotime($sanction_date)));
				$this->db->set('da_id',$this->key_field_val);
				$this->db->insert('sanction_date');

			}

		}

		

		//additional module save routine here

	}

	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function get_sanction_info(){

		$this->db->join($this->db->dbprefix('offence_sanction'),$this->db->dbprefix('offence_sanction').'.offence_sanction_id = '.$this->db->dbprefix('employee_da').'.offence_sanction_id','left');
		$this->db->where($this->db->dbprefix('employee_da').'.da_id',$this->input->post('record_id'));
		$da_info = $this->db->get($this->db->dbprefix('employee_da'));

		if( $da_info->num_rows() > 0 ){
			$response->sanction = $da_info->row()->offence_sanction;
		}
		else{
			$response->sanction = '';
		}

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);

	}

	function get_sanction_dates(){

		$this->db->where('da_id',$this->input->post('record_id'));
		$this->db->order_by('sanction_date','asc');
		$sanction_dates = $this->db->get('sanction_date');

		$html = "";

		if( $sanction_dates->num_rows() > 0 ){

			foreach( $sanction_dates->result() as $sanction_date ){

				$html .= '<span>Date Sanction : </span><div class="text-input-wrap"><input type="text" class="input-text datepicker date d date sanction" value="'.date('m/d/Y',strtotime($sanction_date->sanction_date)).'" name="sanction-date[]"></div>';

			}

		}

		$data['html'] = $html;

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		

	}

	function get_sanction_ddlb(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		//get level of offence
		$offence = $this->db->get_where('offence', array('offence_id' => $this->input->post('offence_id')))->row();
		$response->sanction_ddlb = '<select name="offence_sanction_id_temp" disabled="disabled">';
		$response->sanction_ddlb .= '<option value="">Select...</option>';
		$sanctions = $this->db->get_where('offence_sanction', array('deleted' => 0, 'offence_level_id' => $offence->offence_level_id ));
		$sanction_id = 0;
		if( $sanctions->num_rows() > 0 ){
			foreach( $sanctions->result() as $sanction ){

				if( $this->input->post('offence_no') == $sanction->offence_no ){
					$seleceted = 'selected="selected"';
					$sanction_id = $sanction->offence_sanction_id;
				}
				else{
					$seleceted = '';
				}
				$response->sanction_ddlb .= '<option value="'. $sanction->offence_sanction_id .'" '. $seleceted .'>'. $sanction->offence_sanction .'</option>';	
			}
		}
		$response->sanction_ddlb .= '</select><input type="hidden" id="offence_sanction_id" name="offence_sanction_id" value="'. $sanction_id .'" />';
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	
	function get_sanction_suspension(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$sanction = $this->db->get_where('offence_sanction', array('offence_sanction_id' => $this->input->post('offence_sanction_id')))->row();
		$response->has_suspension = 0;
		if(!empty( $sanction->suspension_to )){
			$response->has_suspension = 1;
			$response->min =  $sanction->suspension_from;
			$response->max =  $sanction->suspension_to;
		}
		
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	
	function print_record( $record_id = 0 ){
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}
		
		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));
		
		$template = $this->template->get_module_template($this->module_id, 'DA_FORM');
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);
		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$vars['date'] = date( $this->config->item('display_datetime_format') );
			$da = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			$nte = $this->db->get_where('employee_nte', array('nte_id' => $da->nte_id))->row();
			$ir = $this->db->get_where('employee_ir', array('ir_id' => $nte ->ir_id))->row();
			$immediate_superior = $this->hdicore->_get_userinfo( $ir->immediate_superior );
			$employee = $this->hdicore->_get_userinfo( $da->employee_id );
			$vars['position'] = $employee->position;
			$vars['department'] = $employee->department;
			$vars['company'] = $employee->company;
			$employment_status = $this->system->get_employment_status( $da->employee_id );
			$vars['employment_status'] = $employment_status->employment_status;
			$vars['immediate_superior'] = $immediate_superior->firstname.' '.$immediate_superior->lastname;
			$offence = $this->db->get_where('offence', array('offence_id' => $ir->offence_id))->row();
			switch( $da->offence_no ){
				case 1;
					$vars['offence_no'] = "1st";
					break;
				case 2;
					$vars['offence_no'] = "2nd";
					break;
				case 3;
					$vars['offence_no'] = "1rd";
					break;
				case 4;
					$vars['offence_no'] = "4th";
					break;
				case 5;
					$vars['offence_no'] = "5th";
					break;	
			}
			$vars['offence'] = $offence->offence;
			$sanction = $this->db->get_where('offence_sanction', array('offence_sanction_id' => $da->offence_sanction_id))->row();
			$sanctionli[] = '<li>'.$sanction->offence_sanction.'</li>';
			if( !empty( $sanction->suspension_to ) ) $sanctionli[] = '<li><u> '. $da->suspension .' </u> day/s Suspension to be served on _________________________.</li>';
			if( $da->payment_for_damages > 0 ) $sanctionli[] = '<li>To pay the amount of <u> PHP '. number_format($da->payment_for_damages, 2, '.', ',') .' </u> as restitution/penalty for loss/destruction of and/or damage to Company Property and business.</li>';
			$vars['sanction'] = '<ul>'.implode('', $sanctionli).'</ul>';
			$vars['ir_date'] = date( $this->config->item('display_datetime_format'), strtotime( $ir->date_prepared ) );
			$html = $this->template->prep_message($template['body'], $vars, false, true);
			
			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date($this->config->item('display_datetime_format_compact')).'-NTE-'. $record_id .'.pdf', 'D');
		}
		else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_record_nte($record_id = 0) {
		if(!file_exists('uploads/employee/da'))
			mkdir('uploads/employee/da', 0777, true);

		// Get from $_POST when the URI is not present.
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}

		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'nte');

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$vars['date'] = date( $this->config->item('display_datetime_format') );
			$da = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			$nte = $this->db->get_where('employee_nte', array('nte_id' => $da->nte_id))->row();
			$ir = $this->db->get_where('employee_ir', array('ir_id' => $da ->ir_id))->row();
			$immediate_superior = $this->hdicore->_get_userinfo( $ir->immediate_superior );
			$employee = $this->hdicore->_get_userinfo( $da->employee_id );
			$vars['position'] = $employee->position;
			$vars['department'] = $employee->department;
			$vars['company'] = $employee->company;
			$employment_status = $this->system->get_employment_status( $da->employee_id );
			$vars['employment_status'] = $employment_status->employment_status;
			$vars['immediate_superior'] = $immediate_superior->firstname.' '.$immediate_superior->lastname;

			$this->db->join('offence_category','offence.offence_category_id = offence_category.offence_category_id');
			$offence = $this->db->get_where('offence', array('offence_id' => $ir->offence_id))->row();

			switch( $da->offence_no ){
				case 1;
					$vars['offence_no'] = "1st";
					break;
				case 2;
					$vars['offence_no'] = "2nd";
					break;
				case 3;
					$vars['offence_no'] = "1rd";
					break;
				case 4;
					$vars['offence_no'] = "4th";
					break;
				case 5;
					$vars['offence_no'] = "5th";
					break;	
			}

			$vars['offence'] = $ir->details;

			//tirso modification
			$vars['offence_category'] = $offence->offence_category;
			$vars['offence_section'] = $offence->section;
			$vars['offence_description'] = $offence->offence;

			$sanction = $this->db->get_where('offence_sanction', array('offence_sanction_id' => $da->offence_sanction_id))->row();
			$sanctionli[] = '<li>'.$sanction->offence_sanction.'</li>';
			if( !empty( $sanction->suspension_to ) ) $sanctionli[] = '<li><u> '. $da->suspension .' </u> day/s Suspension to be served on _________________________.</li>';
			if( $da->payment_for_damages > 0 ) $sanctionli[] = '<li>To pay the amount of <u> PHP '. number_format($da->payment_for_damages, 2, '.', ',') .' </u> as restitution/penalty for loss/destruction of and/or damage to Company Property and business.</li>';
			$vars['sanction'] = '<ul>'.implode('', $sanctionli).'</ul>';
			$vars['ir_date'] = date( $this->config->item('display_datetime_format'), strtotime( $ir->date_prepared ) );
			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output('uploads/employee/da/da-nte.pdf', 'F');
			$path = 'uploads/employee/da/da-nte.pdf';
			$response->msg_type = 'success';
			$response->data = $path;

			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_record_ntu($record_id = 0) {
		if(!file_exists('uploads/employee/da'))
			mkdir('uploads/employee/da', 0777, true);

		// Get from $_POST when the URI is not present.
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}

		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'ntu');

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$vars['date'] = date( $this->config->item('display_datetime_format') );
			$da = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			$nte = $this->db->get_where('employee_nte', array('nte_id' => $da->nte_id))->row();
			$ir = $this->db->get_where('employee_ir', array('ir_id' => $da ->ir_id))->row();
			$immediate_superior = $this->hdicore->_get_userinfo( $ir->immediate_superior );
			$employee = $this->hdicore->_get_userinfo( $da->employee_id );
			$vars['position'] = $employee->position;
			$vars['department'] = $employee->department;
			$vars['company'] = $employee->company;
			$employment_status = $this->system->get_employment_status( $da->employee_id );
			$vars['employment_status'] = $employment_status->employment_status;
			$vars['immediate_superior'] = $immediate_superior->firstname.' '.$immediate_superior->lastname;

			$this->db->join('offence_category','offence.offence_category_id = offence_category.offence_category_id');
			$offence = $this->db->get_where('offence', array('offence_id' => $ir->offence_id))->row();

			switch( $da->offence_no ){
				case 1;
					$vars['offence_no'] = "1st";
					break;
				case 2;
					$vars['offence_no'] = "2nd";
					break;
				case 3;
					$vars['offence_no'] = "1rd";
					break;
				case 4;
					$vars['offence_no'] = "4th";
					break;
				case 5;
					$vars['offence_no'] = "5th";
					break;	
			}

			$vars['offence'] = $ir->details;

			//tirso modification
			$vars['offence_category'] = $offence->offence_category;
			$vars['offence_section'] = $offence->section;
			$vars['offence_description'] = $offence->offence;

			$sanction = $this->db->get_where('offence_sanction', array('offence_sanction_id' => $da->offence_sanction_id))->row();
			$sanctionli[] = '<li>'.$sanction->offence_sanction.'</li>';
			if( !empty( $sanction->suspension_to ) ) $sanctionli[] = '<li><u> '. $da->suspension .' </u> day/s Suspension to be served on _________________________.</li>';
			if( $da->payment_for_damages > 0 ) $sanctionli[] = '<li>To pay the amount of <u> PHP '. number_format($da->payment_for_damages, 2, '.', ',') .' </u> as restitution/penalty for loss/destruction of and/or damage to Company Property and business.</li>';
			$vars['sanction'] = '<ul>'.implode('', $sanctionli).'</ul>';
			$vars['ir_date'] = date( $this->config->item('display_datetime_format'), strtotime( $ir->date_prepared ) );
			$html = $this->template->prep_message($template['body'], $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output('uploads/employee/da/da-ntu.pdf', 'F');
			$path = 'uploads/employee/da/da-ntu.pdf';
			$response->msg_type = 'success';
			$response->data = $path;

			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_record_nte_form($record_id = 0) {
		
		if (CLIENT_DIR == "oams"){
			$this->print_record_nte_oams();
			return;
		}

		if(!file_exists('uploads/employee/ir'))
			mkdir('uploads/employee/ir', 0777, true);

		// Get from $_POST when the URI is not present.
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}

		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template(115, 'nte_on_ir');

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {

			$vars = get_record_detail_array($record_id);
			$vars['date'] = date( $this->config->item('display_datetime_format') );
			
			$this->db->join('employee_ir', 'employee_ir.ir_id = '.$this->module_table.'.ir_id', 'left');
			$this->db->join('offence', 'offence.offence_id = '.$this->module_table.'.offence_id', 'left');
			$this->db->join('offence_category', 'offence_category.offence_category_id = offence.offence_category_id', 'left');
			$this->db->join('offence_level', 'offence_level.offence_level_id = offence.offence_level_id', 'left');
			$ir =  $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();

			$immediate_superior = $this->hdicore->_get_userinfo( $ir->immediate_superior );
			$vars['immediate_superior'] = $immediate_superior->firstname.' '.$immediate_superior->lastname;
			$vars['immediate_superior_position'] = $immediate_superior->position;

/*			$complainant_data = array();
			$complainants = array();
			$complainant_dept = array();
			$complainant_campaign = array();

			foreach(explode(',', $ir->complainants) as $complainant)
			{
				$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
				$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
				$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
				$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
				$user_data = $this->db->get_where('user', array('user.user_id' => $complainant))->row();
				$complainant_data[] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname.' <br />'.$user_data->position;
				$complainants[] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname;
				$complainant_dept[] = $user_data->department;
				$complainant_campaign[] = $user_data->campaign;
			}*/

			// involved employees
			$involved_array = array();
			$im_head_reporting_to_html = '';
			$im_head_head_reporting_to_html = '';

			$employee_id = $ir->employee_id;
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->join('employment_status', 'employment_status.employment_status_id = employee.status_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
			$user_data = $this->db->get_where('user', array('user.user_id' => $employee_id))->row();

			$involved_array[] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname;
			$involved_position[] = $user_data->position;
			$involved_es[] = $user_data->employment_status;


			$involved_department[] = $user_data->department;
			$involved_campaign[] = $user_data->campaign;

			$im_head_reporting_to = $this->system->get_reporting_to($employee_id);
			$rto_array = explode(',',$im_head_reporting_to);

			$this->db->select('employee.*, user.user_id, user.firstname, user.lastname, position, department, user.position_id, user.department_id, user.company_id, user_company.company, user.sex');
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->where_in('user.user_id', $rto_array);
			$this->db->where('user.deleted', 0);

			$employee_reporting_to = $this->db->get('user');

			if ($employee_reporting_to && $employee_reporting_to->num_rows() > 0){
				foreach ($employee_reporting_to->result() as $row) {
					$im_head_reporting_to_html .= '<p>'.$row->firstname .' '. $row->lastname .' - '. $row->position.'</p>';

					$im_head_head_reporting_to = $this->system->get_reporting_to($row->employee_id);
					$rto_head_array = explode(',',$im_head_head_reporting_to);

					$this->db->select('employee.*, user.user_id, user.firstname, user.lastname, position, department, user.position_id, user.department_id, user.company_id, user_company.company, user.sex');
					$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
					$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
					$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
					$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
					$this->db->where_in('user.user_id', $rto_head_array);
					$this->db->where('user.deleted', 0);

					$employee_head_head_reporting_to = $this->db->get('user');

					if ($employee_head_head_reporting_to && $employee_head_head_reporting_to->num_rows() > 0){
						foreach ($employee_head_head_reporting_to->result() as $row_head) {
							$im_head_head_reporting_to_html .= '<p>'.$row_head->firstname .' '. $row_head->lastname .' - '. $row_head->position.'</p>';
						}
					}											
				}
			}			

			// $this->db->get_where('employee', array('employee_id' => $ir))

			$vars['offence'] = $this->db->get_where('offence', array('offence_id' => $ir->offence_id))->row()->offence;

			$vars['involved_employees'] = implode(', ', $involved_array);
			$vars['involved_position'] = implode(', ', $involved_position);
			$vars['involved_es'] = implode(', ', $involved_es);

			// not needed but just in case
			$vars['involved_department'] = implode(', ', $involved_department);
			$vars['involved_campaign'] = $user_data->id_number .' - '. implode(', ', $involved_campaign);

			$vars['prepared_by'] = implode('<br />', $complainant_data);
			$vars['complainants'] = implode(', ', $complainants);
			$vars['complainant_dept'] = implode(', ', $complainant_dept);
			$vars['complainant_campaign'] = implode(', ', $complainant_campaign);

			$vars['approver_position'] = $approver->position;
			$vars['approver'] = $approver->firstname.' '.$approver->lastname;

			$vars['details_of_violation'] = $ir->details;
			$vars['offense_datetime'] = date($this->config->item('display_datetime_format'), strtotime($ir->offense_datetime));

			// offence
			$vars['offence_category'] = $ir->offence_category;
			$vars['offence_level'] = $ir->offence_level;

/*			dbug($vars);
			die();*/

			$offence_no = 0;
			$offence_no = $this->system->get_da_offence_count($ir->offence_id, $employee_id);
            //$offence_no = $offence_no+1;

            // initialize offence flag
            foreach(range(1,4) as $num)
            	$vars['offence_'.$num] = '';
            
            if($offence_no > 4)
            {
            	$vars['offence_4'] = 'X';
            } else {
	            $offence_key = 'offence_'.$offence_no;
	            $vars[$offence_key] = 'X';
	            
	            switch ($offence_no) {
	            	case 1:
	            		$vars['offence_num'] = "second offense";
	            		break; 
	            	case 2:
	            		$vars['offence_num'] = "third offense ";
	            		break; 
	            	case 3:
	            		$vars['offence_num'] = "forth offense ";
	            		break; 
	            	default:
	            		$vars['offence_num'] = '';
	            		break;
	            }
	        }

            // first offence
            $offence_sanction = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id, 'offence_no' => $offence_no))->row()->offence_sanction;
			$vars['offence_sanction'] = $offence_sanction;

			// initialize sanction flag
			foreach($this->db->get('offence_sanction')->result() as $offence_flag)
				$vars[strtolower(str_replace(' ', '_', $offence_flag->offence_sanction)).'_flag'] = '';

			// iabang daw
			$vars['verbal_warning_flag'] = '';

			$offence_key = strtolower(str_replace(' ', '_', $offence_sanction)).'_flag';
			$vars[$offence_key] = 'X';

			// second offence
			$offence_no = $offence_no+1;
			$offence_qry = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id, 'offence_no' => $offence_no));
			if($offence_qry && $offence_qry->num_rows() > 0)
				$offence_sanction = $offence_qry->row()->offence_sanction;
			$vars['second_offence_sanction'] = $offence_sanction;

			// third offence
			$offence_no = $offence_no+1;
			$offence_qry = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id, 'offence_no' => $offence_no));
			if($offence_qry && $offence_qry->num_rows() > 0)
				$offence_sanction = $offence_qry->row()->offence_sanction;

			$vars['third_offence_sanction'] = $offence_sanction;


			// $this->db->join('', '', 'left');
			// $this->db->join('', '', 'left');
			// $this->db->get_where('offence', array('offence_id' => $ir->))
			$off = $ir->offence_category.' - '.$ir->offence;
			if ($ir->section != ''){
				$off = $ir->offence_category.','.$ir->section.' - '.$ir->offence;
			}

			$vars['offence_desc'] = $off;
			$vars['immediate_head'] = $im_head_reporting_to_html;
			$vars['immediate_head_head'] = $im_head_head_reporting_to_html;
			
			$html = $this->template->prep_message($template['body'], $vars, false, false);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output('uploads/employee/ir/NTE.pdf', 'F');
			$path = 'uploads/employee/ir/NTE.pdf';
			$response->msg_type = 'success';
			$response->data = $path;

			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	

function print_record_nte_oams($record_id = 0) {
		
		if(!file_exists('uploads/employee/ir'))
			mkdir('uploads/employee/ir', 0777, true);

		// Get from $_POST when the URI is not present.
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}

		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template(115, 'nte_on_ir');

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {

			$vars = get_record_detail_array($record_id);
			$vars['date'] = date( $this->config->item('display_datetime_format') );
			
			$this->db->join('employee_ir', 'employee_ir.ir_id = '.$this->module_table.'.ir_id', 'left');
			$this->db->join('offence', 'offence.offence_id = '.$this->module_table.'.offence_id', 'left');
			$this->db->join('offence_category', 'offence_category.offence_category_id = offence.offence_category_id', 'left');
			$this->db->join('offence_level', 'offence_level.offence_level_id = offence.offence_level_id', 'left');
			$ir =  $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();

			$immediate_superior = $this->hdicore->_get_userinfo( $ir->immediate_superior );
			$vars['immediate_superior'] = $immediate_superior->firstname.' '.$immediate_superior->lastname;
			$vars['immediate_superior_position'] = $immediate_superior->position;

			// involved employees
			$involved_array = array();
			$im_head_reporting_to_html = '';
			$im_head_head_reporting_to_html = '';

			$employee_id = $ir->employee_id;
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->join('employment_status', 'employment_status.employment_status_id = employee.status_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
			$user_data = $this->db->get_where('user', array('user.user_id' => $employee_id))->row();

			$involved_array[] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname;
			$involved_position[] = $user_data->position;
			$involved_es[] = $user_data->employment_status;


			$involved_department[] = $user_data->department;
			$involved_campaign[] = $user_data->campaign;

			$im_head_reporting_to = $this->system->get_reporting_to($employee_id);
			$rto_array = explode(',',$im_head_reporting_to);

			$this->db->select('employee.*, user.user_id, user.firstname, user.lastname, position, department, user.position_id, user.department_id, user.company_id, user_company.company, user.sex');
			$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->where_in('user.user_id', $rto_array);
			$this->db->where('user.deleted', 0);

			$employee_reporting_to = $this->db->get('user');

			if ($employee_reporting_to && $employee_reporting_to->num_rows() > 0){
				foreach ($employee_reporting_to->result() as $row) {
					$im_head_reporting_to_html .= '<p>'.$row->firstname .' '. $row->lastname .' - '. $row->position.'</p>';

					$im_head_head_reporting_to = $this->system->get_reporting_to($row->employee_id);
					$rto_head_array = explode(',',$im_head_head_reporting_to);

					$this->db->select('employee.*, user.user_id, user.firstname, user.lastname, position, department, user.position_id, user.department_id, user.company_id, user_company.company, user.sex');
					$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
					$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
					$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
					$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
					$this->db->where_in('user.user_id', $rto_head_array);
					$this->db->where('user.deleted', 0);

					$employee_head_head_reporting_to = $this->db->get('user');

					if ($employee_head_head_reporting_to && $employee_head_head_reporting_to->num_rows() > 0){
						foreach ($employee_head_head_reporting_to->result() as $row_head) {
							$im_head_head_reporting_to_html .= '<p>'.$row_head->firstname .' '. $row_head->lastname .' - '. $row_head->position.'</p>';
						}
					}											
				}
			}			


			$vars['offence'] = $this->db->get_where('offence', array('offence_id' => $ir->offence_id))->row()->offence;

			$vars['involved_employees'] = implode(', ', $involved_array);
			$vars['involved_position'] = implode(', ', $involved_position);
			$vars['involved_es'] = implode(', ', $involved_es);

			// not needed but just in case
			$vars['involved_department'] = implode(', ', $involved_department);
			$vars['involved_campaign'] = $user_data->id_number .' - '. implode(', ', $involved_campaign);

			$vars['prepared_by'] = implode('<br />', $complainant_data);
			$vars['complainants'] = implode(', ', $complainants);
			$vars['complainant_dept'] = implode(', ', $complainant_dept);
			$vars['complainant_campaign'] = implode(', ', $complainant_campaign);

			$vars['approver_position'] = $approver->position;
			$vars['approver'] = $approver->firstname.' '.$approver->lastname;

			$vars['details_of_violation'] = $ir->details;
			$vars['offense_datetime'] = date($this->config->item('display_datetime_format'), strtotime($ir->offense_datetime));
			
			$sanctions = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id));

			// offence
			$vars['offence_category'] = $ir->offence_category;
			$vars['offence_level'] = $ir->offence_level;


			$offence_no = 0;
			// $offence_no = $this->system->get_da_offence_count($ir->offence_id, $employee_id);

			$offence_no = $this->db->get_where('employee_da', array('da_id' => $record_id))->row()->offence_no;
            //$offence_no = $offence_no+1;

			if ($sanctions->num_rows() <= $offence_no) {
				$offence_no = $sanctions->num_rows();
			}

            // initialize offence flag
            foreach(range(1,5) as $num)
            	$vars['offence_'.$num] = '';
            
            if($offence_no > 5)
            {
            	$vars['offence_5'] = 'X';
            } else {
	            $offence_key = 'offence_'.$offence_no;
	            $vars[$offence_key] = 'X';
	            
	            switch ($offence_no) {
	            	case 1:
	            		$vars['offence_num'] = "second offense";
	            		break; 
	            	case 2:
	            		$vars['offence_num'] = "third offense ";
	            		break; 
	            	case 3:
	            		$vars['offence_num'] = "forth offense ";
	            		break; 
	            	case 4:
	            		$vars['offence_num'] = "fifth offense ";
	            		break; 
	            	default:
	            		$vars['offence_num'] = '';
	            		break;
	            }
	        }

            // first offence
            $offence_sanction = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id, 'offence_no' => $offence_no))->row()->offence_sanction;
			$vars['offence_sanction'] = $offence_sanction;

			$vars['sanction_1'] = "";
			$vars['sanction_2'] = "";
			$vars['sanction_3'] = "";
			$vars['sanction_4'] = "";
			$vars['sanction_5'] = "";
		
			$cnt = 1;
			// initialize sanction flag
			$sanction = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id));
			foreach( $sanction->result() as $offence_flag){
				$vars[strtolower(str_replace(' ', '_', $offence_flag->offence_sanction)).'_flag'] = '';
				$vars["sanction_".$cnt] = $offence_flag->offence_sanction;
				$cnt++;
			}

			// initialize sanction flag
			foreach($this->db->get('offence_sanction')->result() as $offence_flag)
				$vars[strtolower(str_replace(' ', '_', $offence_flag->offence_sanction)).'_flag'] = '';

			// iabang daw
			$vars['verbal_warning_flag'] = '';

			$offence_key = strtolower(str_replace(' ', '_', $offence_sanction)).'_flag';
			$vars[$offence_key] = 'X';
			$last_offense = 1;

			// second offence
			$offence_no = $offence_no+1;
			$offence_qry = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id, 'offence_no' => $offence_no));
			if($offence_qry && $offence_qry->num_rows() > 0){
				$offence_sanction = $offence_qry->row()->offence_sanction;
				$vars['second_offence_sanction'] = $offence_sanction;
				$last_offense = 0;
			}

			
			// third offence
			$offence_no = $offence_no+1;
			$offence_qry = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id, 'offence_no' => $offence_no));
			if($offence_qry && $offence_qry->num_rows() > 0){
				$offence_sanction = $offence_qry->row()->offence_sanction;
				$vars['third_offence_sanction'] = $offence_sanction;
				$last_offense = 0;
			}



			$off = $ir->offence_category.' - '.$ir->offence;
			if ($ir->section != ''){
				$off = $ir->offence_category.','.$ir->section.' - '.$ir->offence;
			}

			if ($last_offense) {
				
				$vars['sanction_remarks'] = '';
			}else{
				$vars['sanction_remarks'] = "Committing the same offense will merit you a <em>";
				$vars['sanction_remarks'] .= $vars['second_offence_sanction'];
				$vars['sanction_remarks'] .= "</em> on its <em>";
				$vars['sanction_remarks'] .= $vars['offence_num'];
				$vars['sanction_remarks'] .= "</em>.";
			}
			
			$vars['offence_desc'] = $off;
			$vars['immediate_head'] = $im_head_reporting_to_html;
			$vars['immediate_head_head'] = $im_head_head_reporting_to_html;
			
			$html = $this->template->prep_message($template['body'], $vars, false, false);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output('uploads/employee/ir/NTE.pdf', 'F');
			$path = 'uploads/employee/ir/NTE.pdf';
			$response->msg_type = 'success';
			$response->data = $path;

			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}	

	function get_employee_approvers(){

		if(IS_AJAX){

			$employee_id = $this->input->post('employee_id');


			$user = $this->db->get_where('user',array( 'employee_id' => $employee_id ))->row();

			$approvers = $this->system->get_approvers_only($user->position_id, $this->module_id);
			$approver_array = array();
			$data = "";

			if($approvers){

				foreach($approvers as $approver){
					array_push($approver_array, $approver['approver_position_id']);
				}

				$user_array = array();

				foreach($approver_array as $approver_record){

					$approver_info = $this->db->get_where('user',array( 'position_id' => $approver_record ))->row();
					array_push($user_array, $approver_info->employee_id);

				}

				$data = $user_array;

			}

			$this->load->view('template/ajax', array('json' => $data));


		}
		else{

			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}


	}

	function validate_day() {
		if(IS_AJAX) {
			$sample = $this->system->get_affected_dates($this->input->post('employee_id'), $this->input->post('sanctiondate'), $this->input->post('sanctiondate'));

			if (count($sample) == 0) {
				$response->msg_type = 'attention';
				$response->msg 		= 'No Work Shift On That Day';
			} else {
				$response->msg_type = 'success';
				$response->data = count($sample);
			}			
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record-custom" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)" record_id = "'.$record['da_id'].'"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	function _set_left_join() {
		parent::_set_left_join();

		if(!$this->is_superadmin && !$this->is_admin && $this->user_access[$this->module_id]['post'] != 1){
	        $join_ot = "u.employee_id = {$this->db->dbprefix}employee_da.employee_id"; 
	        $this->db->join('user u', $join_ot, 'left',FALSE);
	    }
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */