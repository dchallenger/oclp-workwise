<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Incident_report extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Incident Reports';
		$this->listview_description = 'This module lists all incident reports.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an incident report';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an incident report';

		// I re-code and remove the _set_filter() and change it to $this->filter. -jr
		// if(!$this->user_access[$this->module_id]['post'] && !$this->user_access[$this->module_id]['publish'])
		// {
		if($this->input->post('filter') && $this->input->post('filter') == 'personal')
		{
			$condition = '('.$this->db->dbprefix.'employee_ir.prepared_by = '.$this->user->user_id.' )';
			if($this->user_access[$this->module_id]['approve'] == 1)
				$condition .= ' OR ( '.$this->db->dbprefix.'employee_ir.ir_status_id != 1 AND ( '.$this->db->dbprefix.'employee_ir.approvers LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "%,'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "'.$this->userinfo['user_id'].'" ) AND ( '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "%,'.$this->userinfo['user_id'].',%" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "'.$this->userinfo['user_id'].',%" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "%,'.$this->userinfo['user_id'].'" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "'.$this->userinfo['user_id'].'" )  )';
			$this->filter = $condition;
		}

		if(!$this->user_access[$this->module_id]['post'] && !$this->user_access[$this->module_id]['publish']) {
			$condition = '('.$this->db->dbprefix.'employee_ir.prepared_by = '.$this->user->user_id.' )';
			if($this->user_access[$this->module_id]['approve'] == 1)
				$condition .= ' OR ( '.$this->db->dbprefix.'employee_ir.ir_status_id != 1 AND ( '.$this->db->dbprefix.'employee_ir.approvers LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "%,'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "'.$this->userinfo['user_id'].'" ) AND ( '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "%,'.$this->userinfo['user_id'].',%" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "'.$this->userinfo['user_id'].',%" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "%,'.$this->userinfo['user_id'].'" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "'.$this->userinfo['user_id'].'" )  )';
			$this->filter = $condition;
		}

		if(!$this->is_superadmin && !$this->is_admin && $this->user_access[$this->module_id]['post'] != 1){
			$this->filter = 'u.company_id = '.$this->userinfo['company_id'];
        }

		$this->default_sort_col = array('firstname');
		// }

    }    

	// START - default module functions
	// default jqgrid controller method
	function index(){

		//dbug($this->user_access[$this->module_id]['post']);
		//die();

		if( $this->user_access[$this->module_id]['list'] != 1 ){
			$_POST['record_id'] = "-1";
			$this->edit();
		}
		else{
			$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
			$data['content'] = 'listview';
	
			if($this->session->flashdata('flashdata')){
				$info['flashdata'] = $this->session->flashdata('flashdata');
				$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
			}

			//set default columnlist
			$this->_set_listview_query();

			if($this->user_access[$this->module_id]['post'] || $this->user_access[$this->module_id]['publish']) {
				$tabs[] = '<li filter="all" class="active"><a href="javascript:void(0)">All</li>';
				$tabs[] = '<li filter="personal"><a href="javascript:void(0)">Personal</li>';
			}

			if( sizeof( $tabs ) > 0 ) $data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');

			//set grid buttons
			$data['jqg_buttons'] = $this->_default_grid_buttons();
	
			//set load jqgrid loadComplete callback
			$data['jqgrid_loadComplete'] = "init_filter_tabs()";
	
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
	}

	function detail()
	{

		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

		//initialize ir_status
		$ir = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));

		if( $ir->num_rows() == 1 ){
			$ir = $ir->row();
			$data['ir_status_id'] = $ir->ir_status_id;
			$data['prepared_by'] = $ir->prepared_by;
			$data['ir_id'] =  $ir->ir_id;
			$approvers = explode(',', $ir->approvers);

			if(in_array( $this->userinfo['position_id'] , $approvers) && $this->config->item('client_no') != 2){
				$data['approvers'] = 1;
			} else if(in_array( $this->userinfo['user_id'] , $approvers) && $this->config->item('client_no') == 2){
				$data['approvers'] = 1;
			} else{
				$data['approvers'] = 0;
			}

		}

		//initialize buttons
		$data['buttons'] = $this->module_link . '/view-buttons';

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

		parent::edit();

		//additional module edit routine here
		$data['show_wizard_control'] = false;
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
			$data['show_wizard_control'] = true;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
		}
		$data['content'] = 'editview';

		if( $this->input->post('record_id') == "-1" ){
			$data['buttons'] = $this->module_link . '/edit-draft-buttons';
			$data['ir_status_id'] = -1;
		}
		else{

			$ir = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val));

			if( $ir->num_rows() == 1 ){
				$ir = $ir->row();
				switch( $ir->ir_status_id ){
					case 1:
						$data['buttons'] = $this->module_link . '/edit-draft-buttons';
						break;
					case 2:
						 $data['buttons'] = $this->module_link . '/edit-hrvalidation-buttons';
						break;
					case 3:
						$data['buttons'] = $this->module_link . '/cancel-buttons';
						break;
					case 6:
						$data['buttons'] = 'template/edit-buttons-default';
						break;
					case 4:
					case 5:
					default:
						$data['buttons'] = $this->module_link . '/goback-buttons';
						break;			
				}
			}

			$data['ir_status_id'] = $ir->ir_status_id;
		}

		$data['scripts'][] = '<script type="text/javascript">var ir_status = '.$data['ir_status_id'].';</script>';

		
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

	function ajax_save($bypass = false)
	{
		if ($bypass) {parent::ajax_save(); return;}

		if ($this->input->post('record_id') == '-1') {
			$_POST['ir_status_id'] 		 = 2;
			$_POST['prepared_by'] 		 = $this->user->user_id;
			$_POST['date_prepared'] 	 = date($this->config->item('edit_date_format'));
		}


		if( $this->input->post('notify') && $this->input->post('notify') == "true" && $this->user_access[$this->module_id]['approve'] == 1 ) $_POST['ir_status_id'] = 3;
		if( $this->input->post('cancel') && $this->input->post('cancel') == "true" && $this->user_access[$this->module_id]['cancel'] == 1 ) $_POST['ir_status_id'] = 5;
		if( $this->input->post('draft') && $this->input->post('draft') == "true" ) $_POST['ir_status_id'] = 1;
		if( $this->input->post('validate') && $this->input->post('validate') == "true" ) $_POST['ir_status_id'] = 2;
		if( $this->input->post('close') && $this->input->post('close') == "true" ) $_POST['ir_status_id'] = 4;
		if( $this->input->post('send') && $this->input->post('send') == "true" ){

			$approvers = $this->system->get_approvers_and_condition($this->user->user_id,$this->module_id);

            //$approvers = $this->system->get_approvers($this->userinfo['position_id'], $this->module_id);

			$involved_employees = $this->input->post('involved_employees');

			if(isset($involved_employees) && $involved_employees == ""){
				$ir = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')))->row();
				$involved_employees = explode(',', $ir->involved_employees );
			}

			$approver_ctr = 0;

			foreach($involved_employees as $employee_id){
				$employee_info = $this->system->get_employee($employee_id);
				foreach($approvers as $approver){
					if( $approver['approver'] == $employee_info['employee_id'] ){
						$approver_ctr++;
					}
				}
			}

			if( $approver_ctr > 0 ){
				$_POST['ir_status_id'] = 2;
			}
			else{
				$_POST['ir_status_id'] = 6;
			}

		}

		parent::ajax_save();

		//additional module save routine here
		if ($this->input->post('record_id') == '-1') {
			//add immediate supperiod and note by fields
			$ir_settings = $this->hdicore->_get_config('ir_settings');
			$this->db->where_in('employee_id', $this->input->post('complainants'));
			$complainant = $this->db->get('employee')->row();
			$info = array(
				'noted_by' => $ir_settings['approver'],
				'immediate_superior' => $complainant->supervisor_id
			);
			$this->db->update($this->module_table, $info, array($this->key_field => $this->key_field_val));
		}
		
		
		//add proper list of involved and concerned personnel
		$this->db->delete('employee_ir_complainant', array($this->key_field => $this->key_field_val));
		$this->db->delete('employee_ir_involved', array($this->key_field => $this->key_field_val));
		$this->db->delete('employee_ir_witness', array($this->key_field => $this->key_field_val));
		
		$ir = $this->db->get_where($this->module_table, array($this->key_field => $this->key_field_val))->row();

		$complainants = explode( ',', $ir->complainants );
		foreach($complainants as $employee_id){
			$complainant = array($this->key_field => $this->key_field_val, 'employee_id' => $employee_id);
			$this->db->insert('employee_ir_complainant', $complainant);
		}
		
		$involved_employees = explode( ',', $ir->involved_employees );
		foreach($involved_employees as $employee_id){
			$involved = array($this->key_field => $this->key_field_val, 'employee_id' => $employee_id);
			$this->db->insert('employee_ir_involved', $involved);
		}
		
		$witnesses = explode( ',', $ir->witnesses );
		foreach($witnesses as $employee_id){
			$witness = array($this->key_field => $this->key_field_val, 'employee_id' => $employee_id);
			$this->db->insert('employee_ir_witness', $witness);
		}
		

		if( $this->input->post('notify') && $this->input->post('notify') == "true" && $this->user_access[$this->module_id]['approve'] == 1 ){

			$hr_manager = $this->db->get_where('user', array('role_id' => 2, 'deleted' => 0, 'inactive' => 0))->row();


			foreach($involved_employees as $employee_id){
				$nte = array(
					'ir_id' => $this->key_field_val,
					'employee_id' => $employee_id,
					'issued_by' => $this->user->user_id,
					'date_issued' => date('Y-m-d H:i:s'),
					'noted_by' => $hr_manager->user_id,
					'nte_status_id' => 1
				);

				$this->db->insert('employee_nte', $nte);
			}

			
		}
		
		if( $this->input->post('cancel') && $this->input->post('cancel') == "true" && $this->user_access[$this->module_id]['cancel'] == 1 ){
			//check if nte already created, delete them
			$this->db->update('employee_nte', array('nte_status_id' => 5), array($this->key_field => $this->key_field_val));
		}

		if( ( $this->input->post('send') && $this->input->post('send') == "true" ) || ( $this->input->post('notify') && $this->input->post('notify') == "true" && $this->input->post('record_id') == -1 ) || ( $this->input->post('notify') && $this->input->post('notify') == "true" && $this->input->post('notify_hr') && $this->input->post('notify_hr') == "true" ) || ( $this->input->post('validate') && $this->input->post('validate') == "true" ) ){
			$this->db->update('employee_ir', array('date_sent' => date('Y-m-d H:i:s')), array($this->key_field => $this->key_field_val));
		}

		if( ($this->input->post('send') && $this->input->post('send') == "true") && $ir->prepared_by = $this->user->user_id ){

			$approver_array = array();

			foreach($this->input->post('involved_employees') as $involved_employee)
			{
				$approvers = $this->system->get_approvers_and_condition($involved_employee,$this->module_id);

	            //$approvers = $this->system->get_approvers($this->userinfo['position_id'], $this->module_id);

	            foreach($approvers as $approver){
	                array_push($approver_array, $approver['approver']);
	            }
	        }

			$approver_list = implode(',', $approver_array);

			$this->db->update('employee_ir', array('approvers' => $approver_list), array($this->key_field => $this->key_field_val));
		}
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function print_record($record_id = 0) {
		if(!file_exists('uploads/employee/ir')) {
			mkdir('uploads/employee/ir', 0777, true);
		}

		// Get from $_POST when the URI is not present.
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}

		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}

		if(!$record_id) {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);	
		}

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {

			$vars = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			switch ($vars->offence_id) {
				case '1': //tardiness
					$html = $this->print_tardiness($vars);
					break;				
				default:
					$this->session->set_flashdata('flashdata', 'No Template Available.<br/>Please contact the System Administrator.');
					redirect(base_url() . $this->module_link);
					break;
			}
			// dbug($html);
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, false, false, '');
			$this->pdf->Output('uploads/employee/ir/IR.pdf', 'D');				
		} else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function print_tardiness($ir) {
		$vars['date'] = date( $this->config->item('display_datetime_format') );			
		// $approver = $this->hdicore->_get_userinfo( $ir->noted_by );
		// $complainants = explode(',',$ir->complainants);
		// $complainant = $complainants[0]; //get the first complainant
		// $complainant = $this->hdicore->_get_userinfo( $complainant );
		// $vars['department'] = $complainant->department;
		// $vars['company'] = $complainant->company;

		// $complainant_data = array();
		// $complainants = array();
		// $complainant_dept = array();
		// $complainant_campaign = array();
		// $complainant_ims = array();
		// $complainant_ims_pos = array();
		// $noted_by = array();

		// foreach(explode(',', $ir->complainants) as $complainant)
		// {
		// 	$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
		// 	$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
		// 	$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
		// 	$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
		// 	$user_data = $this->db->get_where('user', array('user.user_id' => $complainant))->row();
		// 	$complainant_data[] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname.' <br />'.$user_data->position;
		// 	$complainants[] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname;

		// 	// $ims = $this->system->get_reporting_to($complainant);

		// 	$rto = $this->db->get_where('employee', array('employee_id' => $complainant))->row()->reporting_to;
		// 	foreach(explode(',', $rto) as $ims)
		// 	{
		// 		$immediate_superior = $this->hdicore->_get_userinfo($ims);
		// 		$noted_check = $immediate_superior->firstname.' '.$immediate_superior->lastname.'<br /> '.$immediate_superior->position; 
		// 		if(!in_array($noted_check, $noted_by))
		// 			$noted_by[] = $immediate_superior->firstname.' '.$immediate_superior->lastname.'<br /> '.$immediate_superior->position;
		// 	}
			

		// 	$complainant_dept[] = $user_data->department;
		// 	$complainant_campaign[] = $user_data->campaign;
		// }

		$template = $this->template->get_module_template($this->module_id, 'IR_tardiness');

		// involved employees
		$involved_array = array();
		$involved_department = array();
		$involved_campaign = array();
		$dept_head = array();
		$div_head = array();
		foreach(explode(',', $ir->involved_employees) as $involved)
		{
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
			$this->db->join('user_company_division', 'user_company_division.division_id = user.division_id', 'left');
			$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
			$user_data = $this->db->get_where('user', array('user.user_id' => $involved))->row();
			$involved_array[] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname;
			$involved_department[] = $user_data->department;
			$involved_campaign[] = $user_data->campaign;
			$dept_head[] = $user_data->dm_user_id;
			$div_head[] = $user_data->division_manager_id;
		}
		$qry_date_from = date('Y-m-1', strtotime("-1 month",strtotime($ir->offense_datetime)));
		$qry_date_to = date('Y-m-t', strtotime("-1 month",strtotime($ir->offense_datetime)));
		$date_to = date($this->config->item('display_date_format_email'),strtotime($qry_date_to));
		$date_from = date('F' ,strtotime($date_to)).' 1, '.date('Y' ,strtotime($date_to));

		$vars['date_range'] = $date_from.' To '.$date_to;
		$vars['date_now'] = date($this->config->item('display_date_format_email'));
		$vars['offence'] = $this->db->get_where('offence', array('offence_id' => $ir->offence_id))->row()->offence.' For the Period '.$vars['date_range'];
		$vars['involved_employees'] = implode(', ', $involved_array);
		$vars['month'] = date('F' ,strtotime($date_to));

		$qry_date_from = date('Y-m-1', strtotime("-1 month",strtotime($ir->offense_datetime)));
		$qry_date_to = date('Y-m-t', strtotime("-1 month",strtotime($ir->offense_datetime)));

		$dtr_result = $this->db->query(" SELECT * FROM {$this->db->dbprefix}employee_dtr WHERE employee_id = '{$ir->involved_employees}' AND (`date` >= '{$qry_date_from}' AND `date` <= '{$qry_date_to}') AND ht_flag = '1'");
		// dbug($this->db->last_query());
		$late_detail = '<table border="1" style="width:100%;" align="center">';
		$late_detail .= '<tr><td style="text-align:center;">No of times Tardy</td><td style="text-align:center;">Date Tardy</td><td style="text-align:center;">Time In</td><td style="text-align:center;">Minutes Late</td></tr>';
		$cnt = 1;
		$total_late = 0;
		foreach ($dtr_result->result() as $key => $value) {
			$late_detail .= '<tr><td style="text-align:center;">'.$cnt++.'</td><td style="text-align:center;">'.date($this->config->item('edit_date_format'),strtotime($value->date)).'</td><td style="text-align:center;">'.date('h:i',strtotime($value->time_in1)).'</td><td style="text-align:center;">'.round($value->lates_display, 0).'</td></tr>';
			$total_late += round($value->lates_display, 0);
		}
		$late_detail .= '<tr><td style="text-align:center;"><b>Total</b></td><td style="text-align:center;">&nbsp;</td><td style="text-align:center;">&nbsp;</td><td style="text-align:center;"><b>'.$total_late.'</b></td></tr>';
		$late_detail .= '</table>';
		$vars['late_detail'] = $late_detail;


		$vars['late_no'] = $cnt-1;
		$vars['late_number'] = $this->change_to_word($cnt-1);

		$logo = get_branding();		
		$employee_info = $this->hdicore->_get_userinfo( $ir->involved_employees );
		$company_id = $employee_info->company_id;
		$company_qry = $this->db->get_where('user_company', array('company_id' => $company_id))->row(); 
		if(!empty($company_qry->logo)) {
		  	$logo = '<img alt="" src="'.$company_qry->logo.'">';
		}
		$vars['image_logo'] = str_replace('<img alt=', '<img width="800px" alt=', $logo);

		$hr_specialist = $this->system->get_employee(75);
		$hr_admin = $this->system->get_employee(12);
		$department_head = '';
		if(!empty($dept_head[0])) {
			$department_head = $this->system->get_employee($dept_head[0]);	
		}
		$division_head = '';
		if(!empty($div_head[0])) {
			$division_head = $this->system->get_employee($dept_head[0]);	
		}

		$html_signatory = '<table syle="width:100%">';
		$html_signatory .= '<tr>';
		$html_signatory .= '<td style="width:25%">Prepared By:</td>';
		$html_signatory .= '<td style="width:25%">Reviewed By:</td>';
		$html_signatory .= '<td style="width:25%">Concurred By:</td>';
		$html_signatory .= '<td style="width:25%">Approved By:</td>';
		$html_signatory .= '</tr>';
		$html_signatory .= '<tr>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '</tr>';
		$html_signatory .= '<tr>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '</tr>';
		$html_signatory .= '<tr>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '<td>&nbsp;</td>';
		$html_signatory .= '</tr>';
		$html_signatory .= '<tr>';
		$html_signatory .= '<td style="text-align:center;">'.$hr_specialist['firstname'].' '.$hr_specialist['middleinitial'].' '.$hr_specialist['lastname'].'</td>';
		$html_signatory .= '<td style="text-align:center;">'.$hr_admin['firstname'].' '.$hr_admin['middleinitial'].' '.$hr_admin['lastname'].'</td>';		
		if(!empty($department_head)) {
			$html_signatory .= '<td style="text-align:center;">'.$department_head['firstname'].' '.$department_head['middleinitial'].' '.$department_head['lastname'].'</td>';
		} else {
			$html_signatory .= '<td style="text-align:center;">&nbsp;</td>';
		}
		if(!empty($division_head)) {
			$html_signatory .= '<td style="text-align:center;">'.$division_head['firstname'].' '.$division_head['middleinitial'].' '.$division_head['lastname'].'</td>';
		} else {
			$html_signatory .= '<td style="text-align:center;">&nbsp;</td>';
		}
		$html_signatory .= '</tr>';
		$html_signatory .= '<tr>';
		$html_signatory .= '<td style="text-align:center;">'.$hr_specialist['position'].'</td>';
		$html_signatory .= '<td style="text-align:center;">'.$hr_admin['position'].'</td>';		
		if(!empty($department_head)) {
			$html_signatory .= '<td style="text-align:center;">'.$department_head['position'].'</td>';
		} else {
			$html_signatory .= '<td style="text-align:center;">&nbsp;</td>';
		}
		if(!empty($division_head)) {
			$html_signatory .= '<td style="text-align:center;">'.$division_head['position'].'</td>';
		} else {
			$html_signatory .= '<td style="text-align:center;">&nbsp;</td>';
		}
		$html_signatory .= '</tr>';
		$html_signatory .= '</table>';

		$vars['signatory'] = $html_signatory;
		
		$html = $this->template->prep_message($template['body'], $vars, false, false);
		// dbug($html);
		return $html;
		// Prepare and output the PDF.
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

		if (method_exists($this, '_set_filter')) {

			$this->_set_filter();
		}

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->select('employee_ir.prepared_by');
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );


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
			$this->db->select('employee_ir.prepared_by');
			$this->db->select('employee_ir.approvers');
			$this->db->select('employee_ir.date_sent');
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			
			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
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

						if ($row['t3ir_status'] == 'Draft' && !$this->is_superadmin) {
							if ($row['prepared_by'] != $this->userinfo['user_id'])
								continue;
						}

						if ($row['t3ir_status'] == 'Cancelled' && $row['date_sent'] == NULL && !$this->is_superadmin) {
							if ($row['prepared_by'] != $this->userinfo['user_id'])
								continue;
						}

						
						if ($row['t3ir_status'] == 'For Initial Validation' ){
							$approvers = explode(',' , $row['approvers'] );
							if( ( !in_array( $this->userinfo['position_id'] , $approvers ) ) && ( $row['prepared_by'] != $this->userinfo['user_id'] ) ){
								if($this->user_access[$this->module_id]['approve'] != 1 && $this->user_access[$this->module_id]['post'] != 1  && $this->user_access[$this->module_id]['publish'] != 1){
									continue;
								}
							}
							
						}
						


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

	protected function _append_to_select()
	{
		$this->listview_qry .= ',u.firstname';
	}

	function _set_left_join() {
		parent::_set_left_join();

        $join_ot = "FIND_IN_SET(u.employee_id , {$this->db->dbprefix}employee_ir.involved_employees)"; 
        $this->db->join('user u', $join_ot, 'left',FALSE);
	}

	function _set_search_all_query()
	{
		$value =  $this->input->post('searchString');
		$search_string = array();
		foreach($this->search_columns as $search)
		{
			$column = strtolower( $search['column'] );
			if(sizeof(explode(' as ', $column)) > 1){
				$as_part = explode(' as ', $column);
				$search['column'] = strtolower( trim( $as_part[0] ) );
			}
			$search_string[] = $search['column'] . ' LIKE "%'. $value .'%"' ;
		}
		$search_string[] = 'u.firstname LIKE "%' . $value . '%"';
		$search_string[] = 'u.lastname LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}
	
	/**
	 * Send the email to approvers.
	 */
	function send_email() {

		$this->db->where($this->key_field, $this->input->post('record_id'));
		$request = $this->db->get($this->module_table);

		if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');

			if ($mail_config) {

				$recepients = array();
				$cc_reciepients = array();
				$request = $request->row_array();
				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template($this->module_id, 'IR');
				$message = $this->template->prep_message($template['body'], $request);

				// Approvers.
				$approvers = explode(',',$request['approvers']);

				if( ( is_array( $approvers  ) && sizeof($approvers) > 0 ) ){

					$this->db->where_in('position_id', $approvers);
					$result = $this->db->get('user');

					$result = $result->result_array();

					foreach ($result as $row) {

						//from reciepients
						$recepients[] = $row['email'];

					}

					//cc reciepients
					$cc_approvers = $this->system->get_email_approvers($this->userinfo['position_id'], $this->module_id);
					$cc_approver_array = array();

					if( !empty( $cc_approvers ) ){
						foreach($cc_approvers as $cc_approver){
							array_push($cc_approver_array, $cc_approver['approver_position_id']);
						}

						$cc_approver_list = $cc_approver_array; 

						$this->db->where_in('position_id', $cc_approver_list);
						$cc_result = $this->db->get('user');

						$cc_result = $cc_result->result_array();

						foreach($cc_result as $cc_row){
							$cc_reciepients[] = $cc_row['email'];
						}
					}

					// If queued successfully set the status to For Approval.
					if ($this->template->queue(implode(',', $recepients), implode(',', $cc_reciepients), $template['subject'], $message)) {
						$data['form_status_id'] = 2;
						$data['email_sent'] = '1';
                    	$data['date_sent'] = date('Y-m-d G:i:s');						
						$this->db->where($this->key_field, $request[$this->key_field]);
						$this->db->update($this->module_table, $data);
					}
				}
			}
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{

		$ir = $this->db->get_where($this->module_table, array($this->key_field => $record['ir_id']));

		if( $ir->num_rows() == 1 ){
			$ir = $ir->row();
		}

		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] && $ir->ir_status_id == 1 ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record') && CLIENT_DIR != 'oams') {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record') && CLIENT_DIR == 'oams') {
            $actions .= '<a class="icon-button icon-16-print" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)" onClick="callBoxy('.$record['ir_id'].')" record_id = "'.$record['ir_id'].'"></a>';
        }
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	// function _set_filter()
	// {

	// 	if(($this->user_access[$this->module_id]['approve'] == 1 && $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['publish'] != 1)){

	// 		$condition = '('.$this->db->dbprefix.'employee_ir.prepared_by = '.$this->user->user_id.' )';
	// 		$condition .= ' OR ( '.$this->db->dbprefix.'employee_ir.ir_status_id != 1 AND ( '.$this->db->dbprefix.'employee_ir.approvers LIKE "%,'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "'.$this->userinfo['user_id'].',%" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "%,'.$this->userinfo['user_id'].'" OR '.$this->db->dbprefix.'employee_ir.approvers LIKE "'.$this->userinfo['user_id'].'" ) AND ( '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "%,'.$this->userinfo['user_id'].',%" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "'.$this->userinfo['user_id'].',%" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "%,'.$this->userinfo['user_id'].'" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "'.$this->userinfo['user_id'].'" )  )';

	// 		$this->db->where($condition);

	// 	}	
	// 	elseif(($this->user_access[$this->module_id]['approve'] != 1 && $this->user_access[$this->module_id]['post'] != 1 && $this->user_access[$this->module_id]['publish'] != 1)){

	// 		$condition = '('.$this->db->dbprefix.'employee_ir.prepared_by = '.$this->user->user_id.' )';
	// 		$this->db->where($condition);

	// 	}
		
	// 	elseif( ( $this->user_access[$this->module_id]['approve'] == 1 ) && ( $this->user_access[$this->module_id]['post'] == 1 ) ){

	// 		$approvers = $this->system->get_approvers($this->userinfo['position_id'], $this->module_id);
	// 		$approver_subordinates = $this->system->get_approvers_subordinates($this->userinfo['position_id'], $this->module_id);

	// 		if( $approvers != "" ){

	// 			$condition = '('.$this->db->dbprefix.'employee_ir.prepared_by = '.$this->user->user_id.' )';
	// 			$condition .= ' OR ( '.$this->db->dbprefix.'employee_ir.ir_status_id != 1 AND ( '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "%,'.$this->userinfo['user_id'].',%" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "'.$this->userinfo['user_id'].',%" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "%,'.$this->userinfo['user_id'].'" AND '.$this->db->dbprefix.'employee_ir.involved_employees NOT LIKE "'.$this->userinfo['user_id'].'" )  )';

	// 			$this->db->where($condition);

	// 		}
	// 		else{

	// 			$condition = '('.$this->db->dbprefix.'employee_ir.prepared_by = '.$this->user->user_id.' )';

	// 			foreach($approver_subordinates as $subordinate_info){

	// 				$condition .= ' OR ( '.$this->db->dbprefix.'employee_ir.ir_status_id != 1 AND (  '.$this->db->dbprefix.'employee_ir.involved_employees LIKE "'.$subordinate_info->user_id.'" OR '.$this->db->dbprefix.'employee_ir.involved_employees LIKE "%,'.$subordinate_info->user_id.',%" OR '.$this->db->dbprefix.'employee_ir.involved_employees LIKE "'.$subordinate_info->user_id.',%" OR '.$this->db->dbprefix.'employee_ir.involved_employees LIKE "%,'.$subordinate_info->user_id.'" )  )';

	// 			}

	// 			$this->db->where($condition);
	// 		}

	// 	}

		

	// }

	function print_record_nte()
	{
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

		$template = $this->template->get_module_template($this->module_id, 'nte_on_ir');

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {

			$vars = get_record_detail_array($record_id);
			$vars['date'] = date( $this->config->item('display_datetime_format') );
			
			$this->db->join('offence', 'offence.offence_id = '.$this->module_table.'.offence_id', 'left');
			$this->db->join('offence_category', 'offence_category.offence_category_id = offence.offence_category_id', 'left');
			$this->db->join('offence_level', 'offence_level.offence_level_id = offence.offence_level_id', 'left');
			$ir =  $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();

			$immediate_superior = $this->hdicore->_get_userinfo( $ir->immediate_superior );
			$vars['immediate_superior'] = $immediate_superior->firstname.' '.$immediate_superior->lastname;
			$vars['immediate_superior_position'] = $immediate_superior->position;

			$complainant_data = array();
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
			}

			// involved employees
			$involved_array = array();
			foreach(explode(',', $ir->involved_employees) as $involved)
			{
				$employee_id = $involved;
				$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
				$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
				$this->db->join('employment_status', 'employment_status.employment_status_id = employee.status_id', 'left');
				$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
				$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
				$user_data = $this->db->get_where('user', array('user.user_id' => $involved))->row();

				$involved_array[] = $user_data->firstname.' '.$user_data->middlename.' '.$user_data->lastname;
				$involved_position[] = $user_data->position;
				$involved_es[] = $user_data->employment_status;


				$involved_department[] = $user_data->department;
				$involved_campaign[] = $user_data->campaign;
			}

			// $this->db->get_where('employee', array('employee_id' => $ir))

			$vars['offence'] = $this->db->get_where('offence', array('offence_id' => $ir->offence_id))->row()->offence;

			$vars['involved_employees'] = implode(', ', $involved_array);
			$vars['involved_position'] = implode(', ', $involved_position);
			$vars['involved_es'] = implode(', ', $involved_es);

			// not needed but just in case
			$vars['involved_department'] = implode(', ', $involved_department);
			$vars['involved_campaign'] = implode(', ', $involved_campaign);

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

			$offence_no = 0;
			$offence_no = $this->system->get_da_offence_count($ir->offence_id, $employee_id);
            $offence_no = $offence_no+1;

            // initialize offence flag
            foreach(range(1,4) as $num)
            	$vars['offence_'.$num] = '';
            
            if($offence_no > 4)
            {
            	$vars['offence_4'] = 'X';
            } else {
	            $offence_key = 'offence_'.$offence_no;
	            $vars[$offence_key] = 'X';
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
			$vars['offence_desc'] = $ir->offence_category.' - '.$ir->offence;

			
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

	function print_record_nte_oams()
	{
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

		$template = $this->template->get_module_template($this->module_id, 'nte_on_ir');

		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {

			$vars = get_record_detail_array($record_id);

			$vars['date'] = date( $this->config->item('display_datetime_format') );
			
			$this->db->join('offence', 'offence.offence_id = '.$this->module_table.'.offence_id', 'left');
			$this->db->join('offence_category', 'offence_category.offence_category_id = offence.offence_category_id', 'left');
			$this->db->join('offence_level', 'offence_level.offence_level_id = offence.offence_level_id', 'left');
			$ir =  $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			
			$immediate_superior = $this->hdicore->_get_userinfo( $ir->immediate_superior );
			$vars['immediate_superior'] = $immediate_superior->firstname.' '.$immediate_superior->lastname;
			$vars['immediate_superior_position'] = $immediate_superior->position;

			$complainant_data = array();
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
			}

			// involved employees
			$involved_array = array();
			$im_head_reporting_to_html = '';
			$im_head_head_reporting_to_html = '';
			foreach(explode(',', $ir->involved_employees) as $involved)
			{
				$employee_id = $involved;
				$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
				$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
				$this->db->join('employment_status', 'employment_status.employment_status_id = employee.status_id', 'left');
				$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
				$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');
				$user_data = $this->db->get_where('user', array('user.user_id' => $involved))->row();

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

			$offence_no = 0;
			$offence_no = $this->system->get_da_offence_count($ir->offence_id, $employee_id);

			$get_da = $this->db->get_where('employee_da', array('ir_id' => $record_id)); 
			if ($get_da && $get_da->num_rows() > 0) {
				$offence_no = $get_da->row()->offence_no;
			}
			
			$sanctions = $this->db->get_where('offence_sanction', array('offence_level_id' => $ir->offence_level_id));
			
			// $get_da = $this->db->get_where('employee_da', array('ir_id' => $ir->ir_id, 'involved_employees'));
			if ($vars['ir_status_id'] != 'Closed') {
				if ($offence_no < $sanctions->num_rows()) {
					$offence_no = $offence_no+1; 	
				}
			}
            

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
			


			// $this->db->join('', '', 'left');
			// $this->db->join('', '', 'left');
			// $this->db->get_where('offence', array('offence_id' => $ir->))
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

	function get_da()
	{
		$response->da_id = $this->db->get_where('employee_da', array('ir_id' => $this->input->post('record_id')))->row()->da_id;
        $this->load->view('template/ajax', array('json' => $response));
	}

	function change_to_word($no)
	{   
		$words = array('0'=> '' ,'1'=> 'one' ,'2'=> 'two' ,'3' => 'three','4' => 'four','5' => 'five','6' => 'six','7' => 'seven','8' => 'eight','9' => 'nine','10' => 'ten','11' => 'eleven','12' => 'twelve','13' => 'thirteen','14' => 'fouteen','15' => 'fifteen','16' => 'sixteen','17' => 'seventeen','18' => 'eighteen','19' => 'nineteen','20' => 'twenty','30' => 'thirty','40' => 'fourty','50' => 'fifty','60' => 'sixty','70' => 'seventy','80' => 'eighty','90' => 'ninty','100' => 'hundred &','1000' => 'thousand','100000' => 'lakh','10000000' => 'crore');
		if($no == 0) {
			return ' ';
		} else {  
			$novalue='';$highno=$no;$remainno=0;$value=100;$value1=1000;       
			while($no>=100) {
				if(($value <= $no) &&($no  < $value1)) {
					$novalue=$words["$value"];
					$highno = (int)($no/$value);
					$remainno = $no % $value;
					break;
				}
				$value= $value1;
				$value1 = $value * 100;
			}       
			if(array_key_exists("$highno",$words)) {
				return $words["$highno"]." ".$novalue." ".$this->change_to_word($remainno);
			} else {
				$unit=$highno%10;
				$ten =(int)($highno/10)*10;            
				return $words["$ten"]." ".$words["$unit"]." ".$novalue." ".$this->change_to_word($remainno);
			}
		}
	}
	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */