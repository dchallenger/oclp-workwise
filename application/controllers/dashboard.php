<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
	
  function __construct()
  {
		parent::__construct();

		$this->load->model('portlet');
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Dashboard';
		$this->listview_description = 'Simplified and organized data overview.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';
	}

	function index()
	{
		$data['content'] = 'dashboard';

		$data['scripts'][] = jquerttimers_script();

		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}

		//----------------------------------------------------------------------
		//get user db messages...
		//----------------------------------------------------------------------
		$myMsg = $this->_get_messages($this->user->user_id);

		if(is_array($myMsg) && count($myMsg)>0){
			foreach($myMsg as $_m){
				$data['alert_msg'][] = message_box($_m->message_type, $_m->message, 1, 'javascript:delMsg('.$_m->message_id.')');
			}

			$data['scripts'][] = '
				<script type="text/javascript">
					function delMsg(mId){
						if(mId!=undefined || mId.lenght>0){
							$.ajax({url: \''.base_url().'main/delMsg/\'+mId});
						}
					}
				</script>';
		}
		//----------------------------------------------------------------------

		$portlets = $this->portlet->get_user_portlet_state();

		//set left and right column
		$left_col = array();
		$right_col = array();
		$top = array();

		foreach( $portlets as $index => $portlet ){		
			$data['portlets'][$portlet['column']][]	= array(
				'portlet_id' => $portlet['portlet_id'],
				'portlet_name' =>  $portlet['portlet_name'],
				'portlet_file' =>  $portlet['portlet_file'],
				'portlet_class' =>  $portlet['portlet_class'],
				'is_folded' =>  $portlet['is_folded']
			);							
		}				

		//get portlet config of user defined from his position
		$this->db->select('portlet_config');
		$portlet_config = $this->db->get_where('user_position', array('position_id' => $this->userinfo['position_id']));
		if($portlet_config->num_rows() > 0){
			$portlet_config = $portlet_config->row();
			$data['portlet_config'] = unserialize($portlet_config->portlet_config);
			if( !is_array($data['portlet_config'])) $data['portlet_config'] = array();
		}
		else{
			$data['portlet_config'] = array();
		}

		$data['portlet_config'][10] = array('visible' => 1,'access' => 'all');

		$data['user_location'] = $this->portlet->get_user_location();

		//load variable to env
		$this->load->vars( $data );

		//load the final view
		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/header' );
		$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );

		//load page content
		$this->load->view( 'template/page-content' );

		//load footer
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );		
	}

	function get_portlet_content()
	{
		if(IS_AJAX){
			$this->load->model('portlet');
			$this->load->vars( $_POST );
			$this->load->view( $this->userinfo['rtheme']."/portlet/".$this->input->post('portlet_file') );
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function portlet_order()
	{
		if(IS_AJAX){
			$response->msg = "";
			if( isset( $_POST['column'] ) ){
				$portlet_order = $this->input->post('portlet') ? $this->input->post('portlet') : array();
				$portlets = $this->portlet->get_user_portlet_state();
				$portlet_state = array();
				foreach($portlet_order as $sequence => $portlet_id){
					foreach($portlets as $index => $portlet){
						if($portlet['portlet_id'] == $portlet_id ){
							$portlets[$index]['column'] = $this->input->post('column');
							$portlets[$index]['sequence'] = $sequence + 1;
						}
						$portlet_state[$portlets[$index]['portlet_id']] = array(
							'column' => $portlets[$index]['column'],
							'sequence' => $portlets[$index]['sequence'],
							'is_folded' => $portlets[$index]['is_folded']
						);
					}
				}
				$this->portlet->_update_user_config( 'portlet_state', base64_encode( serialize( $portlet_state ) ), $this->user->user_id );
			}
			else{
				$response->msg = "Insufficient data supplied.";
				$response->msg_type = 'attention';
			}

			$data['json'] = $response;
			$this->load->view( $this->userinfo['rtheme'].'/template/ajax', $data );
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function reset_portlet_state()
	{
		if(IS_AJAX){
			$response->msg = "";
			$this->portlet->_update_user_config( 'portlet_state', base64_encode( serialize( array() ) ), $this->user->user_id );

			if( $this->db->_error_message() != "" ){
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			}

			$data['json'] = $response;
			$this->load->view( $this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function portlet_fold_state()
	{
		if(IS_AJAX){
			$response->msg = "";
			if( $this->input->post('portlet_id') && isset($_POST['is_folded']) ){
				$portlets = $this->portlet->get_user_portlet_state();
				$portlet_state = array();
				foreach($portlets as $index => $portlet){
					if($portlet['portlet_id'] == $this->input->post('portlet_id')) $portlets[$index]['is_folded'] = $this->input->post('is_folded');
					$portlet_state[$portlets[$index]['portlet_id']] = array(
						'column' => $portlets[$index]['column'],
						'sequence' => $portlets[$index]['sequence'],
						'is_folded' => $portlets[$index]['is_folded']
					);
				}
				$this->portlet->_update_user_config( 'portlet_state', base64_encode( serialize( $portlet_state ) ), $this->user->user_id );

				if( $this->db->_error_message() != "" ){
					$response->msg = $this->db->_error_message();
					$response->msg_type = "error";
				}
			}
			else{
				$response->msg = "Insufficient data supplied.";
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

	function portlet_resize()
	{
		if (IS_AJAX) {
			$response->msg = "";
			if( $this->input->post('portlet_id') ){
				$portlets = $this->portlet->get_user_portlet_state();
				$portlet_state = array();

				foreach($portlets as $index => $portlet){
					if($portlet['portlet_id'] == $this->input->post('portlet_id'))  {
						$response->prev = $portlets[$index]['column'];
						$portlets[$index]['column'] = $response->class = ($portlets[$index]['column'] == 'top') ? 'left' : 'top';
					}

					$portlet_state[$portlets[$index]['portlet_id']] = array(
						'column' => $portlets[$index]['column'],
						'sequence' => $portlets[$index]['sequence'],
						'is_folded' => $portlets[$index]['is_folded']
					);
				}

				$this->portlet->_update_user_config( 'portlet_state', base64_encode( serialize( $portlet_state ) ), $this->user->user_id );

				if( $this->db->_error_message() != "" ){
					$response->msg = $this->db->_error_message();
					$response->msg_type = "error";
				}
			}
			else{
				$response->msg = "Insufficient data supplied.";
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

	//dashboards
	function employee(){
		$this->_dashboard( $this->method );	
	}

	function recruitment(){
		$this->_dashboard( $this->method );	
	}

	function dtr(){
		$this->_dashboard( $this->method );	
	}

	function _dashboard( $content ){
		$module = $this->db->get_where('module', array('class_path' => $this->uri->uri_string()));
		if( $module->num_rows() == 1 ){
			//if( false )
				$data['content'] = 'module_dashboard';	
			//else
			//	$data['content'] = 'dashboard/employee';

			$this->listview_title = $module->short_name .' Dashboard';
			$data['module'] = $module = $module->row();

			$data['scripts'][] = jquerttimers_script();

			//load variable to env
			$this->load->vars( $data );

			//load the final view
			//load header
			$this->load->view( $this->userinfo['rtheme'].'/template/header' );
			$this->load->view( $this->userinfo['rtheme'].'/template/header-nav' );

			//load page content
			$this->load->view( 'template/page-content' );

			//load footer
			$this->load->view( $this->userinfo['rtheme'].'/template/footer' );	
		}
		else{
			$this->session->set_flashdata('flashdata', 'Requested dashboard does not exists.<br/>Please contact the System Administrator.');
			redirect( base_url() );
		}
	}

	/**
	 * check wether user has overall access to modu;e
	 * @return void
	 */
	public function _visibility_check(){
		//do nothing, everyone has access to dashboard
	}

	function get_training_template_form(){

		$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
		$this->db->join('training_category','training_category.training_category_id = training_subject.training_category_id','left');
		$this->db->join('training_provider','training_provider.training_provider_id = training_subject.training_provider_id','left');
		$this->db->where('training_calendar.training_calendar_id',$this->input->post('calendar_id'));
		$calendar_info_result = $this->db->get('training_calendar');

		$calendar_participant_result = $this->db->get_where('training_calendar_participant',array('training_calendar_id'=>$this->input->post('calendar_id')));

		$this->db->where('training_calendar_session.training_calendar_id',$this->input->post('calendar_id'));
		$this->db->order_by('training_calendar_session.session_date','asc');
		$training_calendar_session_result = $this->db->get('training_calendar_session');
		$training_calendar_session_info = $training_calendar_session_result->row();

		$start_date = date('m/d/Y',strtotime($training_calendar_session_info->session_date));

		$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'],$employee_info->rank_id, $this->userinfo['user_id']);
		$subordinate_list = array();

		if( count($subordinates) > 0 ){
			foreach( $subordinates as $subordinate_record ){
				array_push($subordinate_list, $subordinate_record['employee_id']);
			}

		}

		if( $this->input->post('subordinate') > 0 ){

		$this->db->join('training_calendar_participant_status','training_calendar_participant_status.participant_status_id = training_calendar_participant.participant_status_id','left');
		$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
		$this->db->where('training_calendar_participant.training_calendar_id',$this->input->post('calendar_id'));
		$this->db->where_in('training_calendar_participant.employee_id',$subordinate_list);
		$this->db->where('training_calendar_participant.deleted',0);
		$participant_result = $this->db->get('training_calendar_participant');

		}
		else{

			$participant_result = "";

		}


		if( $calendar_info_result->num_rows > 0 ){

			$response->form = $this->load->view( $this->userinfo['rtheme'].'/training/training_calendar/template_form',array('calendar_info' => $calendar_info_result->row(), 'participant_count' => $calendar_participant_result->num_rows(), 'participants' => $calendar_participant_result->result(), 'session_result'=>$training_calendar_session_result, 'start_date' => $start_date, 'participant_result' => $participant_result, 'subordinate' => $this->input->post('subordinate')), true );
			$this->load->view('template/ajax', array('json' => $response));

		}

	}

	function join_quit_training(){

		if (IS_AJAX) {
			
			$status = "";
			$remarks = "";

			switch($this->input->post('status_id')){
				case 2:
					$status = "confirmed";
				break;
				case 3:
					$status = "declined";
				break;
				case 4:
					$status = "moved";
				break;
			}

			if( $this->input->post('remarks') != "" ){
				$remarks = $this->input->post('remarks');
			}

			$this->db->update('training_calendar_participant',array('participant_status_id'=>$this->input->post('status_id'),'remarks'=>$remarks), array('training_calendar_id'=>$this->input->post('calendar_id'), 'employee_id' => $this->userinfo['user_id'] ));

			$this->db->join('training_subject','training_subject.training_subject_id = training_calendar.training_subject_id','left');
			$this->db->join('training_calendar_participant','training_calendar_participant.training_calendar_id = training_calendar.training_calendar_id AND '.$this->db->dbprefix('training_calendar_participant').'.employee_id = '.$this->userinfo['user_id'] );
			$this->db->join('user','user.employee_id = training_calendar_participant.employee_id','left');
			$training_calendar_result = $this->db->get('training_calendar');

			if( $training_calendar_result->num_rows() > 0 ){

				$training_calendar_info = $training_calendar_result->row();

				$this->load->model('template');
				$template = $this->template->get_module_template($this->module_id, 'notify_hr_participant_confirmation');

				$template_data = array();

				$this->db->where('user.employee_id',$this->userinfo['user_id']);
				$participant_result = $this->db->get('user')->row();

				$this->db->where('( ( division_id LIKE "'.$participant_result->division_id .'" ) || ( division_id LIKE "%,'.$participant_result->division_id.'" ) || ( division_id LIKE "'.$participant_result->division_id.',%" ) || ( division_id LIKE "%,'.$participant_result->division_id.',%" ) )');
				$this->db->where('deleted = 0');
				$receipient_result = $this->db->get('training_email_settings');

				if( $receipient_result->num_rows() > 0 ){

					foreach( $receipient_result->result() as $receipients_info ){

						$receipient_count = 0;

						$receipient_list = explode(',',$receipients_info->email_to);

						if( count($receipient_list) > 0 ){

							foreach( $receipient_list as $receipient_id ){

								$this->db->join('employee','employee.employee_id = user.employee_id','left');
								$this->db->where('user.employee_id',$receipient_id);
								$this->db->where('user.deleted',0);
								$this->db->where('employee.resigned',0);
								$this->db->where('employee.resigned_date is null');
								$user_to_result = $this->db->get('user');

								if( $user_to_result->num_rows() > 0 ){

									$user_to_info = $user_to_result->row();

									$template_data['receipient_name'] = $user_to_info->firstname.' '.$user_to_info->lastname;
									$template_data['participant_name'] = $training_calendar_info->firstname.' '.$training_calendar_info->lastname;
									$template_data['venue'] = $training_calendar_info->venue;
									$template_data['course_title'] = $training_calendar_info->training_subject;
									$template_data['training_topic'] = $training_calendar_info->topic;
									$template_data['status'] = $status;
									$template_data['training_dates'] = "";

									$training_calendar_session_result = $this->db->get_where('training_calendar_session',array('training_calendar_id'=>$training_calendar_info->training_calendar_id));

									if( $training_calendar_session_result->num_rows() > 0 ){

										$template_data['training_dates'] .= "<table style='margin-left:.5in;'>";

										foreach( $training_calendar_session_result->result() as $calendar_session_info ){

											$template_data['training_dates'] .= "<tr><td>".date('F d Y',strtotime($calendar_session_info->session_date))." : ".date('h:i a',strtotime($calendar_session_info->sessiontime_from))." - ".date('h:i a',strtotime($calendar_session_info->sessiontime_to))."</td></tr>";

										}

										$template_data['training_dates'] .= "</table>";

									}

									$email_cc = array();
									$receipient_cc_list = explode(',',$receipients_info->email_cc);

									if( count($receipient_cc_list) > 0 ){

										foreach( $receipient_cc_list as $receipient_cc_id ){

											$this->db->join('employee','employee.employee_id = user.employee_id','left');
											$this->db->where('user.employee_id',$receipient_cc_id);
											$this->db->where('user.deleted',0);
											$this->db->where('employee.resigned',0);
											$this->db->where('employee.resigned_date is null');
											$user_cc_result = $this->db->get('user');

											if( $user_cc_result->num_rows() > 0 ){

												$user_cc_info = $user_cc_result->row();
												array_push($email_cc, $user_cc_info->email);

											}
										}
									}

									if( $receipient_count == 0 ){

										$this->db->where('code','Training_Calendar');
										$this->db->where('deleted',0);
										$training_calendar_module = $this->db->get('module')->row();

										$approver_result = $this->system->get_approvers_and_condition($this->userinfo['user_id'],$training_calendar_module->module_id);

										if( count($approver_result) > 0 ){

											foreach( $approver_result as $approver_info ){

												$this->db->where('user_id',$approver_info['approver']);
												$this->db->where('deleted',0);
												$user_cc_result = $this->db->get('user');

												if( $user_cc_result->num_rows() > 0 ){
													$user_cc_info = $user_cc_result->row();
													array_push($email_cc, $user_cc_info->email);
												}

											}

										}

									}

									$message = $this->template->prep_message($template['body'], $template_data);
		        					$this->template->queue($user_to_info->email, implode(',', $email_cc), $template['subject'], $message);

		        					$receipient_count++;

								}
							}
						}
					}

				}

			}


			$response->msg = "You've successfully ".$status." you're attendance in the selected training";
			$response->msg_type = 'success';

			$this->load->view('template/ajax', array('json' => $response));

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function get_sub_portlet(){
		$reminder_html = '';
		$planning_html = '';
		// $flag = false;
		 if ($this->user_access[$this->module_id]['post'] !== 1) {
       	 $where = "AND FIND_IN_SET (".$this->userinfo['user_id'].",employee_id)";
	      }else{
	        $where = "AND 1";
	      }

	    // planning
		$qry = "SELECT *
              FROM ({$this->db->dbprefix}employee_appraisal_planning_reminders)
              WHERE deleted = 0
              AND visible = 1 
              $where
              AND '".date("Y-m-d")."' BETWEEN date_from AND date_to";

		$result_planning = $this->db->query($qry);

		if ($result_planning && $result_planning->num_rows() > 0){
			foreach ($result_planning->result() as $row){
				$planning_html .= $row->memo_body;
				$planning_html .= '<br />';
			}
			$planning_reminder = true;
			$reminder = true;
		}
		else{
			$planning_reminder = false;
		}

		// appraisal
		$qry = "SELECT *
              FROM ({$this->db->dbprefix}employee_appraisal_reminders)
              WHERE deleted = 0
              AND visible = 1 
              $where
              AND '".date("Y-m-d")."' BETWEEN date_from AND date_to";

		$result = $this->db->query($qry);

		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $plan){
				$reminder_html .= $plan->memo_body;
				$reminder_html .= '<br />';
			}
			$appraisal_reminder = true;
			$reminder = true;
		}
		else{
			$appraisal_reminder = false;
		}


		$response->planning_reminder = $planning_reminder;
		$response->appraisal_reminder = $appraisal_reminder;
		$response->reminder = $reminder;

		$response->reminder_html = $reminder_html;
		$response->planning_html = $planning_html;
		$data['json'] = $response;
        $this->load->view('template/ajax', $data); 
	}

	function process_time_entry() {
		$time_in = '';
		$time_out = '';
		$time_complete = 0;

		$time_rec = date('Y-m-d H:i:s');
		$user_id = $this->user->user_id;
		$location_id = $this->input->post('location_id');
		$location = $this->input->post('location');

		$this->db->where('employee_id',$user_id);
		$this->db->where('date',date('Y-m-d',strtotime($time_rec)));
		$time_record_result = $this->db->get('employee_dtr');

		if ($time_record_result && $time_record_result->num_rows() > 0) {
			$time_record = $time_record_result->row();
			if ($time_record->time_in1 != '0000-00-00 00:00:00') {
				$this->db->where('id',$time_record->id);
				$this->db->update('employee_dtr',array('time_out1' => $time_rec, 'ht_flag' => 1));

				$time_in = $time_record->time_in1; 
				$time_out = $time_rec;
				$time_complete = 1;
			}
			else {
				$this->db->where('id',$time_record->id);
				$this->db->update('employee_dtr',array('time_in1' => $time_rec, 'ht_flag' => 1, 'user_location_id' => $location_id, 'user_location' => $location));

				$time_in = $time_rec;
			}
		}
		else {
			$this->db->insert('employee_dtr',array('employee_id' => $user_id, 'date' => date('Y-m-d',strtotime($time_rec)), 'time_in1' => $time_rec, 'ht_flag' => 1, 'user_location_id' => $location_id, 'user_location' => $location));

			$time_in = $time_rec;
		}

        $js = array("time_in" => $time_in,"time_out" => $time_out,"time_complete" => $time_complete);

        echo json_encode($js);		
	}

	/**
	 * find address using lat long
	 */
	public static function geolocationaddress($lat, $long)
	{
	    $geocode = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&sensor=false&key=AIzaSyDutOiKGJCAlOkn5uZ6NukkzgMErlqH_lA";
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $geocode);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    $response = curl_exec($ch);
	    curl_close($ch);
	    $output = json_decode($response);

		dbug($output);
		die();
			    
	    $dataarray = get_object_vars($output);
	    if ($dataarray['status'] != 'ZERO_RESULTS' && $dataarray['status'] != 'INVALID_REQUEST') {
	        if (isset($dataarray['results'][0]->formatted_address)) {

	            $address = $dataarray['results'][0]->formatted_address;

	        } else {
	            $address = 'Not Found';

	        }
	    } else {
	        $address = 'Not Found';
	    }

	    return $address;
	}	
}

/* End of file */
/* Location: system/application */