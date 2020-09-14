<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exit_interview extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists exit interview forms.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an exit interview form.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an exit interview form.';
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
		
		$this->db->where($this->key_field, $this->input->post('record_id'));
		$this->db->where('deleted', 0);

		$record = $this->db->get($this->module_table)->row_array();
		$data['record'] = $record;

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
		$employee_clearance_id = $this->input->post('employee_clearance_id');
		if($this->user_access[$this->module_id]['edit'] == 1){

			if ($this->input->post('employee_id') != '' && $this->input->post('record_id') == '') {
				$this->db->where('employee_id', $this->input->post('employee_id'));
				$this->db->where('deleted', 0);
				$result = $this->db->get($this->module_table);

				if ($result->num_rows() > 0) {
					$_POST['record_id'] = $result->row()->{$this->key_field};

				} else {
					$_POST['record_id'] = '-1';		
				}				
			}
						
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

			$data['buttons'] = $this->module_link . '/edit-buttons';

			//Get Participant and Calendar Details
			// $this->db->select('user.firstname, user.lastname, training_calendar.feedback_category_id, training_feedback.total_score, training_feedback.average_score');
			// $this->db->join('training_calendar','training_calendar.training_calendar_id = training_feedback.training_calendar_id','left');
			// $this->db->join('user','user.employee_id = training_feedback.employee_id','left');
			// $this->db->where('training_feedback.feedback_id',$this->input->post('record_id'));
			// $participant_details = $this->db->get('training_feedback')->row();

			// $data['participant_name'] = $participant_details->firstname." ".$participant_details->lastname;

			// $data['total_score'] = $participant_details->total_score;
			// $data['average_score'] = $participant_details->average_score;

			//Get Calendar Session Details
			// $this->db->select('training_calendar_session.*');
			// $this->db->where('training_feedback.feedback_id',$this->input->post('record_id'));
			// $this->db->join('training_calendar_session','training_calendar_session.training_calendar_id = training_feedback.training_calendar_id');
			// $calendar_details = $this->db->get('training_feedback');
			
			// $data['calendar_session_details_count'] = $calendar_details->num_rows();

			// if( $calendar_details->num_rows() > 0 ){
			// 	$data['calendar_session_details'] = $calendar_details->result();
			// }

			// $data['instructor_list'] = $this->db->get_where('training_instructor',array('deleted'=>0))->result();
			

			//Get clearance_exit_interview Questionnaire Items
			$answer_details_count = $this->db->get_where('clearance_exit_interview_item_score',array('employee_exit_interview_id'=>$_POST['record_id']))->num_rows();
			$exit_interview_category = $this->db->get_where('clearance_exit_interview_category',array('active'=>1))->row();

			if( $answer_details_count > 0 ){

				$this->db->select('clearance_exit_interview_category.clearance_exit_interview_category_id, clearance_exit_interview_category.clearance_exit_interview_category, clearance_exit_interview_item.*');
				$this->db->join('clearance_exit_interview_item','clearance_exit_interview_item.clearance_exit_interview_category_id = clearance_exit_interview_category.clearance_exit_interview_category_id','left');
				$this->db->where_in('clearance_exit_interview_category.clearance_exit_interview_category_id',$exit_interview_category->clearance_exit_interview_category_id);
				$this->db->where('clearance_exit_interview_item.inactive != 1');
				$this->db->order_by('clearance_exit_interview_item.clearance_exit_interview_category_id','ASC');
				$this->db->order_by('clearance_exit_interview_item.clearance_exit_interview_item_no','ASC');
				$questionnaire_details = $this->db->get('clearance_exit_interview_category');

			}
			else{
				$this->db->select('clearance_exit_interview_category.clearance_exit_interview_category_id, clearance_exit_interview_category.clearance_exit_interview_category, clearance_exit_interview_item.*');
				$this->db->join('clearance_exit_interview_item','clearance_exit_interview_item.clearance_exit_interview_category_id = clearance_exit_interview_category.clearance_exit_interview_category_id','left');
				$this->db->where_in('clearance_exit_interview_category.clearance_exit_interview_category_id',$exit_interview_category->clearance_exit_interview_category_id);
				$this->db->where('clearance_exit_interview_item.inactive != 1');
				$this->db->order_by('clearance_exit_interview_item.clearance_exit_interview_category_id','ASC');
				$this->db->order_by('clearance_exit_interview_item.clearance_exit_interview_item_no','ASC');
				$questionnaire_details = $this->db->get('clearance_exit_interview_category');

			}

			$data['clearance_exit_interview_questionnaire_item_count'] = $questionnaire_details->num_rows();

			if( $questionnaire_details->num_rows() > 0 ){
				$data['clearance_exit_interview_questionnaire_items'] = $questionnaire_details->result_array();

				foreach( $data['clearance_exit_interview_questionnaire_items'] as $key => $val ){

					$clearance_exit_interview_questionnaire_score = $this->db->get_where('clearance_exit_interview_item_score',array('employee_exit_interview_id'=>$_POST['record_id'], 'clearance_exit_interview_item_id'=> $data['clearance_exit_interview_questionnaire_items'][$key]['clearance_exit_interview_item_id'] ));

					if( $clearance_exit_interview_questionnaire_score->num_rows() > 0 ){

						$clearance_exit_interview_questionnaire_score_info = $clearance_exit_interview_questionnaire_score->row();

						$data['clearance_exit_interview_questionnaire_items'][$key]['score'] = $clearance_exit_interview_questionnaire_score_info->score;
						$data['clearance_exit_interview_questionnaire_items'][$key]['remarks'] = $clearance_exit_interview_questionnaire_score_info->remarks;

					}
				}

			}


			// $data['calendar_id'] = $this->input->post('calendar_id');

			// if( $this->input->post('participant_direct') ){
			// 	$data['employee_direct'] = $this->input->post('participant_direct');
			// }
			// else{
			// 	$data['employee_direct'] = 0;
			// }


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
		$this->load->model(array('uitype_detail', 'template', 'system'));
		//additional module save routine here
		$clearance_exit_interview_item = $this->input->post('clearance_exit_interview_item');

		$this->db->where('employee_exit_interview_id',$this->input->post('record_id'));
		$this->db->delete('clearance_exit_interview_item_score');

		$exit_interview_category = $this->db->get_where('clearance_exit_interview_category',array('active'=>1))->row();

		$this->db->select('clearance_exit_interview_category.clearance_exit_interview_category_id, clearance_exit_interview_category.clearance_exit_interview_category, clearance_exit_interview_item.*');
		$this->db->join('clearance_exit_interview_item','clearance_exit_interview_item.clearance_exit_interview_category_id = clearance_exit_interview_category.clearance_exit_interview_category_id','left');
		$this->db->where_in('clearance_exit_interview_category.clearance_exit_interview_category_id',explode(',',$exit_interview_category->clearance_exit_interview_category_id));
		$this->db->order_by('clearance_exit_interview_item.clearance_exit_interview_item_no','ASC');
		$questionnaire_list = $this->db->get('clearance_exit_interview_category');
		$questionnaire_details = $questionnaire_list->result();

		foreach( $questionnaire_details as $questionnaire_detail_info ){

			// if( in_array( $questionnaire_detail_info->score_type, array(1,2,4) ) ){
				$data = array(
					'employee_exit_interview_id' => $this->key_field_val,
					'clearance_exit_interview_item_id' => $questionnaire_detail_info->clearance_exit_interview_item_id,
					'score' => $clearance_exit_interview_item[$questionnaire_detail_info->clearance_exit_interview_item_id]
				);
			// }
			// else{
			// 	$data = array(
			// 		'employee_exit_interview_id' => $this->key_field_val,
			// 		'clearance_exit_interview_item_id' => $questionnaire_detail_info->clearance_exit_interview_item_id,
			// 		'remarks' => $clearance_exit_interview_item[$questionnaire_detail_info->clearance_exit_interview_item_id]
			// 	);
			// }

			$this->db->insert('clearance_exit_interview_item_score',$data);

		}			

		$employee_id = $this->input->post('employee_id');
		$result = $this->db->query("SELECT division_manager_id,division,dm_user_id,department,CONCAT (udv.firstname,' ',udv.lastname) AS division_head,CONCAT (udp.firstname,' ',udp.lastname) AS department_head FROM {$this->db->dbprefix}user u
					JOIN {$this->db->dbprefix}user_company_division dv ON u.division_id = dv.division_id
					JOIN {$this->db->dbprefix}user_company_department dp ON u.department_id = dp.department_id
					LEFT OUTER JOIN {$this->db->dbprefix}user udv ON dv.division_manager_id = udv.employee_id
					LEFT OUTER JOIN {$this->db->dbprefix}user udp ON dp.dm_user_id = udp.employee_id
					WHERE u.employee_id = {$employee_id}
				");

		$employee_array = array();
		if($result && $result->num_rows() > 0) {
			foreach ($result->result() as $key => $value) {
				$employee_array[] = $value->division_manager_id;
			}
		}
		$employee_clearance_id = $this->input->post('record_id');
		$checklist = $this->db->query('SELECT * FROM '.$this->db->dbprefix.'employee_clearance WHERE employee_clearance_id = "'.$employee_clearance_id.'"')->row();
		foreach (explode(',',$checklist->signatories) as $value) {
			$employee_array[] = $value;
		}
		// $this->load->model('template');
  //       
  //       $message = $this->template->prep_message($template['body'], $request);
  //       // $this->template->queue($request['email'], $cc_copy, $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
  //       $this->template->queue($request['email'], "", $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
		if(!empty($employee_array)) {
			foreach ($employee_array as $approver_employee_id) {
				$final_accountabilities = '';
				$employee_accountabilities = $this->db->query('SELECT * FROM '.$this->db->dbprefix.'employee_accountabilities WHERE employee_id = "'.$approver_employee_id.'"');
				$user_info_app = $this->system->get_employee($approver_employee_id);
				$approver_email = $user_info_app['email'];
				$dear = $user_info_app['salutation'].' '.$user_info_app['lastname'];
				if($employee_accountabilities && $employee_accountabilities->num_rows()) {
					$accountabilities_array = array();
					foreach ($employee_accountabilities->result() as $accountabilities) {
						// dbug($accountabilities->equipment);
						$accountabilities_array[] = $accountabilities->equipment;
					}
					$final_accountabilities = '('.implode(', ', $accountabilities_array).')';
				}
				$clearance_employee = $checklist->employee_id;
				$user_info_employee = $this->system->get_employee($clearance_employee);
				$employee_name = $user_info_employee['firstname'].' '.$user_info_employee['lastname'];
				$employee_company = $user_info_employee['company'];
				$employee_email = $user_info_employee['email'];
				$html = '';

				$html .= '<table style="width:100%;">';
				$html .= '<tr>';
				$html .= '<td style="text-align:left;">';
				$html .= 'Dear '.$dear;				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td>';
				$html .= '&nbsp;';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td style="text-align:left;">';
				$html .= 'This is to inform you that '.$employee_name.' Clearance Form is waiting for your approval in the HRIS System. Kindly access the HRIS system and verify employee’s accountabilities with the '.$employee_company.' '.$final_accountabilities.' before approval.';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td>';
				$html .= '&nbsp;';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td>';
				$html .= '&nbsp;';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td style="text-align:left;">';
				$html .= 'Best Regards,';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td>';
				$html .= '&nbsp;';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td style="text-align:left;">';
				$html .= 'The Human Resources Team';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td>';
				$html .= '&nbsp;';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td style="text-align:left;font-size:10px;">';
				$html .= '<i>Note: This is a system generated message. You need not reply to this email.</i>';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td>';
				$html .= '<a href="'.base_url().'">'.base_url().'</a>';				
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '</table>';

				$this->template->queue($approver_email, '', 'Notification Template for Clearance', $html);
			}
		}
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
			$ratings = $this->input->post('rating');
			if ($rating != "") {
				$this->db->where($this->key_field, $this->key_field_val);
				$this->db->update($this->module_table, 	$ratings);
			}
			
		}		

		$this->db->where('employee_id', $this->input->post('employee_id'));
		$this->db->where('deleted', 0);		
		$employee_clearance = $this->db->get('employee_clearance')->row();

		if( $this->input->post('page_refresh') ) $response->page_refresh = "true";
		$response->msg = 'Data has been successfully saved.';
		$response->msg_type = 'success';
		if($this->input->post('on_success') == 'back'){
			$response->record_id = $employee_clearance->employee_clearance_id;
		}else{
			$response->record_id = $this->record_id;
		}
		$this->set_message($response);	
		parent::after_ajax_save();
	}

	// END custom module funtions

	function get_employee(){

		if(IS_AJAX){

			$id = $this->input->post('employee_id');
				$this->db->select('lastname,firstname');
				$this->db->where(array('user_id' => $id ));
				$employee = $this->db->get('user');

				$data = $employee->row_array();
			$data['client'] = CLIENT_DIR;			

			$this->load->view('template/ajax', array('json' => $data));

		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

	}

	function print_record() {

		$employee_id = $this->input->post('employee_id');
    	$record_id = $this->input->post('employee_clearance_id');

		$this->db->select('u.salutation, u.firstname, u.lastname, u.aux, u.middleinitial, c.company, e.employed_date, p.position as employee_position, d.department, eei.*, e.resigned_date');
		$this->db->where('ec.employee_clearance_id',$record_id,false);
		$this->db->from('employee_clearance ec');
		$this->db->join('employee_exit_interview eei','eei.employee_id = ec.employee_id','left');
		$this->db->join('user u','u.employee_id = ec.employee_id','left');
		$this->db->join('employee e','e.employee_id = u.employee_id','left');
		$this->db->join('user_company_department d','d.department_id = u.department_id','left');
		$this->db->join('user_position p','p.position_id = u.position_id','left');
		$this->db->join('user_company c','c.company_id = u.company_id','left');
		$result = $this->db->get();

		// $checklist = $this->_get_form_checklist($record_id);
		// dbug($checklist);

		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template', 'system'));

		$this->db->where('code', 'exit_interview');
		$this->db->where('deleted', 0);		
		$template = $this->db->get('template')->row();

		if( $result->num_rows() > 0 ) {


			$record = $result->row();
			$status = unserialize($record->status);
			$logo = get_branding();
			$user_info = $this->system->get_employee($employee_id);
			$company_id = $user_info['company_id'];
			$company_qry = $this->db->get_where('user_company', array('company_id' => $company_id))->row();
			if(!empty($company_qry->logo)) {
			  $logo = '<img alt="" src="'.base_url().''.$company_qry->logo.'">';
			}
			$image_table = '<table style="width:100%">'.$logo.'</table>';

			$this->db->select('last_day, transfer_effectivity_date');
			$this->db->where('employee_id', $employee_id);
			$this->db->where('employee_movement_type_id', 6);
			$this->db->from('employee_movement');
			$employee_movement = $this->db->get()->row();

			$this->db->select('ceii.clearance_exit_interview_item, ceiis.score, ceii.clearance_exit_interview_item_no');
			$this->db->where('employee_exit_interview_id', $record->employee_exit_interview_id);
			$this->db->order_by('clearance_exit_interview_item_no','ASC');
			$this->db->from('clearance_exit_interview_item_score ceiis');
			$this->db->join('clearance_exit_interview_item ceii','ceii.clearance_exit_interview_item_id = ceiis.clearance_exit_interview_item_id','left');		
			$exit_questionnaire = $this->db->get()->result_array();

			$employee_name = $record->firstname." ".$record->middleinitial." ".$record->lastname." ".$record->aux;
			$employee = $record->salutation." ".$record->lastname;
			$position = $record->employee_position;
			$made_you_stay = $record->made_you_stay;
			$employer_name = $record->employer_name;
			$attractive_new_job = $record->attractive_new_job;
			$recommendation = $record->recommendation;
			$date_resigned = date("F d, Y", strtotime($record->resigned_date == null ? $employee_movement->transfer_effectivity_date : $record->resigned_date));
			$date_hired = date("F d, Y", strtotime($record->employed_date));

			$question_count = 1;
			$table_detail1 = '<table border="0" style="align:center;width:100%;font-size:100px;">';
			$table_detail1 .= '<tr>';
			$table_detail1 .= '<td style="text-align:left;width:40%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>&nbsp;</strong></td>';
			$table_detail1 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Not at All</strong></td>';
			$table_detail1 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Small Degree</strong></td>';
			$table_detail1 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Moderate Degree</strong></td>';
			$table_detail1 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>High Degree</strong></td>';
			$table_detail1 .= '</tr>';
			foreach ($exit_questionnaire as $key => $value) {
				$not_at_all = $value['score'] == 1 ? 'X' : '';
				$small_degree = $value['score'] == 2 ? 'X' : '';
				$moderate_degree = $value['score'] == 3 ? 'X' : '';
				$high_degree = $value['score'] == 4 ? 'X' : '';

				if($value['clearance_exit_interview_item_no'] <= 15){
					$table_detail1 .= '<tr style="">';
					$table_detail1 .= '<td style="text-align:left;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$value['clearance_exit_interview_item'].'</td>';
					$table_detail1 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$not_at_all.'</td>';
					$table_detail1 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$small_degree.'</td>';
					$table_detail1 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$moderate_degree.'</td>';
					$table_detail1 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$high_degree.'</td>';
					$table_detail1 .= '</tr>';
				}elseif($value['clearance_exit_interview_item_no'] <= 22){
					$table_detail2 .= '<tr style="">';
					$table_detail2 .= '<td style="text-align:left;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$value['clearance_exit_interview_item'].'</td>';
					$table_detail2 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$not_at_all.'</td>';
					$table_detail2 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$small_degree.'</td>';
					$table_detail2 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$moderate_degree.'</td>';
					$table_detail2 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$high_degree.'</td>';
					$table_detail2 .= '</tr>';
				}else{
					$table_detail3 .= '<tr style="">';
					$table_detail3 .= '<td style="text-align:left;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$value['clearance_exit_interview_item'].'</td>';
					$table_detail3 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$not_at_all.'</td>';
					$table_detail3 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$small_degree.'</td>';
					$table_detail3 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$moderate_degree.'</td>';
					$table_detail3 .= '<td style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">'.$high_degree.'</td>';
					$table_detail3 .= '</tr>';
				}
				
				if($value['clearance_exit_interview_item_no'] == 15){
					$table_detail1 .= '</table>';
					// $table_detail .= '<br><br>';
					$table_detail2 = '<table border="0" style="align:center;width:100%;font-size:100px;">';
					$table_detail2 .= '<tr>';
					$table_detail2 .= '<td style="text-align:left;width:40%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>&nbsp;</strong></td>';
					$table_detail2 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Not at All</strong></td>';
					$table_detail2 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Small Degree</strong></td>';
					$table_detail2 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Moderate Degree</strong></td>';
					$table_detail2 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>High Degree</strong></td>';
					$table_detail2 .= '</tr>';
				}elseif($value['clearance_exit_interview_item_no'] == 22){
					$table_detail2 .= '</table>';
					// $table_detail .= '<br><br>';
					$table_detail3 = '<table border="0" style="align:center;width:100%;font-size:100px;">';
					$table_detail3 .= '<tr>';
					$table_detail3 .= '<td style="text-align:left;width:40%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>&nbsp;</strong></td>';
					$table_detail3 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Not at All</strong></td>';
					$table_detail3 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Small Degree</strong></td>';
					$table_detail3 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>Moderate Degree</strong></td>';
					$table_detail3 .= '<td style="text-align:center;width:15%;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;"><strong>High Degree</strong></td>';
					$table_detail3 .= '</tr>';
				}
				$question_count++;
			}
			$table_detail3 .= '</table>';

			// dbug($table_detail);
			$vars = array(
				'image_table' => $image_table,
				'table_detail1' => $table_detail1,
				'table_detail2' => $table_detail2,
				'table_detail3' => $table_detail3,
				'employee_name' => $employee_name,
				'employee' => $employee,
				'date_hired' => $date_hired,
				'position' => $position,
				'date_resigned' => $date_resigned,
				'made_you_stay' => $made_you_stay,
				'employer_name' => $employer_name,
				'recommendation' => $recommendation,
				'attractive_new_job' => $attractive_new_job,
			);
			// dbug($vars);
			$html = $this->template->prep_message($template->body, $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Exit Questionnaire' . '.pdf', 'D');
		}
	}

	private function _get_form_checklist($record_id)
	{		
		$this->db->where('employee_clearance.deleted', 0);
		$this->db->where('employee_clearance_id', $record_id);		
		
		$result = $this->db->get('employee_clearance');
		$employee_id = $result->row()->employee_id;
		$ret = false;

		if ($result->num_rows() > 0) {
/*			$this->db->where_in('employee_clearance_form_checklist_id', explode(',', $result->row()->signatories));
			$this->db->where('employee_clearance_form_checklist.deleted', 0);
			$this->db->join('user', 'user.user_id = employee_clearance_form_checklist.approver_id');

			if (!$this->user_access[$this->module_id]['post']) {
				$this->db->where('user.user_id', $this->userinfo['user_id']);
			}

			$ret = $this->db->get('employee_clearance_form_checklist');

			if ($ret->num_rows() == 0) {
				$ret = false;
			} else {
				$ret = $ret->result_array();
			}*/

			$sql = 'SELECT *,a.employee_clearance_form_checklist_id as ecfid FROM '.$this->db->dbprefix('employee_clearance_form_checklist').' a
							  JOIN '.$this->db->dbprefix('user').' b ON (b.user_id = a.approver_id)
							  LEFT JOIN (SELECT group_concat(equipment 	 separator ", ") AS equipment,employee_clearance_form_checklist_id FROM '.$this->db->dbprefix('employee_accountabilities').' WHERE employee_id = '.$employee_id.' GROUP BY employee_clearance_form_checklist_id) AS c ON (a.employee_clearance_form_checklist_id = c.employee_clearance_form_checklist_id)
							  WHERE a.employee_clearance_form_checklist_id IN ('.$result->row()->signatories.')';
/*			if (!$this->user_access[$this->module_id]['post']) {
				$sql .= ' AND b.user_id = '.$this->userinfo['user_id'].'';
			}*/
			$sql .= ' AND a.deleted =  0';

			$ret = $this->db->query($sql);
			// dbug($sql);

			if ($ret){
				if ($ret->num_rows() == 0) {
					$ret = false;
				} else {
					$ret = $ret->result_array();
				}							
			}
			else{
				$ret = false;				
			}
		}

		return $ret;		
	}

}

/* End of file */
/* Location: system/application */