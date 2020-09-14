<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class System extends MY_Model
{
	function __construct()
	{
		parent::__construct();
		$this->get_config_for_all();
	}

	function get_config_for_all()
	{
		$configs = $this->hdicore->_get_config('multiple_configuration');

		foreach($configs as $key => $config)
		{
			$this->config->set_item($key, $config); 
		}

	}

	function _login_check( $user_id ){
		$response = new stdClass;
		$response->login = true;
		$this->db->join('employment_status','employee.status_id = employment_status.employment_status_id');
		$employee = $this->db->get_where('employee', array('employee_id' => $user_id));
		if( $employee->num_rows() == 1 ){
			$employee = $employee->row();
			if( $employee->resigned == 1 ){
				$response->msg  = 'This user is already resigned.';
				$response->msg_type = 'error';
				$response->error_field = 'login';
				$response->login = false;
			}
			else{
				if(!$employee->active){
					$response->msg_type = 'error';
					$response->error_field = 'login';
					$response->login = false;
					$response->msg  = 'This user is already ' . $employee->employment_status .'.';
				}
			}
		}
		return $response;
	}

	function set_system_env(){
		$this->user->is_srexecutive = false;
		$this->user->is_jrexecutive = false;
		$this->user->is_srmanager = false;
		$this->user->is_jrmanager = false;
		$this->user->is_supervisor = false;
		$this->user->is_teamlead = false;
		
		switch( $this->userinfo['position_level_id'] ){
			case 1:
				$this->user->is_srexecutive = true;
				break;
			case 2:
				$this->user->is_jrexecutive = true;
				break;
			case 3:
				$this->user->is_srmanager = true;
				break;
			case 4:
				$this->user->is_jrmanager = true;
				break;
			case 5:
				$this->user->is_supervisor = true;
				break;
			case 6:
				$this->user->is_teamlead = true;
				break;
			default:
				break;
		}
		$profiles = $this->hdicore->get_role_profile( $this->userinfo['role_id'] );

	}
	
	function mrf_count( $status = "" ){
		$where = array(
			'deleted' => 0
		);
		$this->db->where( $where );
		if( !empty( $status ) ) $this->db->where('status', $status);
		
		$this->db->from('recruitment_manpower');
		return $this->db->count_all_results();
	}

	function get_accepted_jo()
	{
		$this->db->select(array('mrf_id', 'COUNT('.$this->db->dbprefix.'recruitment_manpower_candidate.candidate_id) AS accepted_jo', 'number_required','MAX(jo_accepted_date) as accepted_date'));
		$this->db->join('recruitment_manpower_candidate', 'recruitment_manpower_candidate.mrf_id=recruitment_manpower.request_id');
		$this->db->join('recruitment_candidate_job_offer', 'recruitment_candidate_job_offer.candidate_id=recruitment_manpower_candidate.candidate_id');
		$this->db->where('recruitment_candidate_job_offer.job_offer_status_id', 3);
		$mrf = $this->db->get('recruitment_manpower');
		if ($mrf && $mrf->num_rows() > 0) {
			return $mrf->row();
		}else{
			return false;
		}
		
	}

	function letter_of_intent_count( $status_id = "" ){
		$where = array(
			'deleted' => 0
		);
		$this->db->where( $where );
		if( !empty( $status_id ) ) $this->db->where('status_id', $status_id);
		$this->db->where('( approver LIKE "%,'.$this->userinfo['user_id'].'" OR approver LIKE "%,'.$this->userinfo['user_id'].',%" OR approver LIKE "'.$this->userinfo['user_id'].',%" OR approver LIKE "'.$this->userinfo['user_id'].'" )');
		$this->db->from('recruitment_letter_of_intent');
		$count = $this->db->count_all_results();
		return $count;
	}

	function get_applicant_candidate_status( $id = "" ){

		if( !empty( $id ) ){

			$this->db->where(array('applicant_id' => $id));
			$this->db->order_by('candidate_id','desc');
			$result = $this->db->get('recruitment_manpower_candidate')->row();

			$candidate_status_id = $result->candidate_status_id;

			if(!empty($candidate_status_id)){

				$candidates = $this->db->get_where('recruitment_candidate_status',array('candidate_status_id'=>$candidate_status_id))->row();

				return $candidates->candidate_status;

			}

		}

		return false;

	}

	function annual_manpower_planning_count( $id = 0 ){

		if( !empty( $id ) ){

			$this->db->where('annual_manpower_planning_status_id',$id);
			$this->db->where('deleted',0);
			$result = $this->db->get('annual_manpower_planning');

			return $result->num_rows();

		}

		return false;

	}
	
	function candidate_count( $flag = "" ){
		$data = array();
		//get status
		$status = false;
		if( !empty( $flag ) ):
			$status = $this->db->get_where('recruitment_candidate_status', array($flag => 1) )->row();
			$data['candidate_status_id'] = $status->candidate_status_id;
			$data['candidate_status'] = $status->candidate_status;
		endif;
		
		$where = array(
			'recruitment_manpower_candidate.deleted' => 0,
			'recruitment_manpower.deleted' => 0
		);
		$this->db->where( $where );
		if( !empty( $status ) ) $this->db->where('candidate_status_id', $status->candidate_status_id);
		$this->db->join('recruitment_manpower', 'recruitment_manpower.request_id = recruitment_manpower_candidate.mrf_id');
		$this->db->from('recruitment_manpower_candidate');
		$data['count'] = $this->db->count_all_results();
		return $data;
	}
	
	function get_user_active_memo(){
		$is_group = $this->db->get_where('user_position', array('deleted' => 0, 'position_id' => $this->userinfo['position_id']))->row();
		$portlet_config = unserialize($is_group->portlet_config);
		$today = date('Y-m-d');
		$between = "{$this->db->dbprefix}memo.publish_from <= '{$today}' AND {$this->db->dbprefix}memo.publish_to >= '{$today}'";
		$this->db->join('memo_recipient', 'memo_recipient.memo_id = memo.memo_id', 'left');
		$this->db->where( $between, NULL, FALSE );
		$this->db->where( 'memo.memo_type_id', '1' );
		$this->db->where( 'memo.publish', '1' );
		$this->db->where( 'memo.deleted', '0' );

		if($portlet_config[5]['access'] == "all") {
			$company_id = $this->userinfo['company_id'];
			$this->db->where( 'IF(company_recipients <> "",FIND_IN_SET( '.$company_id.', company_recipients ), "")', NULL, FALSE );
		}

		if($portlet_config[5]['access'] == "personal") {
			$this->db->where( 'IF(recipients <> "",FIND_IN_SET( '.$this->user->user_id.', recipients ), "")', NULL, FALSE );
			// $this->db->where( 'memo_recipient.user_id', $this->user->user_id );
		}
		if($this->user_access[$this->module_id]['post'] == 1) {
			$this->db->group_by('memo.memo_id');
		}
		$qry = $this->db->get('memo');
		// dbug($this->db->last_query());
		if( $qry->num_rows() > 0 ){
			$memos = array();
			foreach( $qry->result_array() as $memo ){
				//determine if has been read or not
				$read = $this->db->get_where('memo_viewers', array('memo_id' => $memo['memo_id'], 'user_id' => $this->user->user_id));
				$memo['read'] = $read->num_rows() == 0 ? false :  true;
				$memos[] = $memo;
			}
			return $memos;
		}
		return false;
		
	}
	
	function get_employee_updates(){
		$is_group = $this->db->get_where('user_position', array('deleted' => 0, 'position_id' => $this->userinfo['position_id']))->row();
		$portlet_config = unserialize($is_group->portlet_config);
		$today = date('Y-m-d');
		$between = "{$this->db->dbprefix}memo.publish_from <= '{$today}' AND {$this->db->dbprefix}memo.publish_to >= '{$today}'";
		// $this->db->join('memo_recipient', 'memo_recipient.memo_id = memo.memo_id', 'left');
		$this->db->join('memo_type', 'memo_type.memo_type_id = memo.memo_type_id', 'left');
		$this->db->where( $between, NULL, FALSE );
		$this->db->where_in('memo_type.memo_type_id', array(2, 3, 4)); // I assumed all new, resigned and promoted employees are shown in employee movement tab. changes by jr
		$this->db->where( 'memo.publish', '1' );
		$this->db->where( 'memo.deleted', '0' );
		
		if($portlet_config[5]['access'] == "all") {
			$company_id = $this->userinfo['company_id'];
			$this->db->where( 'IF(company_recipients <> "",FIND_IN_SET( '.$company_id.', company_recipients ), "")', NULL, FALSE );
		}

		if($portlet_config[5]['access'] == "personal") {
			$this->db->where( 'IF(recipients <> "",FIND_IN_SET( '.$this->user->user_id.', recipients ), "")', NULL, FALSE );
			// $this->db->where( 'memo_recipient.user_id', $this->user->user_id );
		}
		if($this->user_access[$this->module_id]['post'] == 1) {
			$this->db->group_by('memo.memo_id');
		}
		$qry = $this->db->get('memo');
		// dbug($this->db->last_query());
		if( $qry->num_rows() > 0 ){
			$memos = array();
			foreach( $qry->result_array() as $memo ){
				//determine if has been read or not
				$read = $this->db->get_where('memo_viewers', array('memo_id' => $memo['memo_id'], 'user_id' => $this->user->user_id));
				$memo['read'] = $read->num_rows() == 0 ? false :  true;
				$memos[] = $memo;
			}
			return $memos;
		}
		return false;
		
	}
	
	function get_offence( $ir_id = 0 ){
		if(empty( $ir_id )) return false;
		
		$ir = $this->db->get_where('employee_ir', array('ir_id' => $ir_id ));
		if($ir->num_rows() == 1){
			$ir = $ir->row();
			$offence = $this->db->get_where('offence', array('offence_id' => $ir->offence_id) );
			if($offence->num_rows() == 1){
				$offence = $offence->row();
				return $offence->offence;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}

	function get_employee($employee_id) {
			
		$this->db->select('employee.*, user.user_id, user.firstname, user.salutation, user.email, user.middleinitial, user.lastname, position, department, user.position_id, user.department_id, user.company_id, user_company.company, user.sex, user_rank.job_rank, user.section_id, employee.rank_id, user.division_id, user_company_division.division');
		$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
		$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
		$this->db->join('user_rank', 'user_rank.job_rank_id = employee.rank_id', 'left');
		$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
		$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
		$this->db->join('user_company_division', 'user_company_division.division_id = user.division_id', 'left');
		$this->db->where('user.user_id', $employee_id);
		$this->db->where('user.deleted', 0);
		$this->db->limit(1);

		$employee = $this->db->get('user');

		if (!$employee || $employee->num_rows() == 0) {

			return 0;

		} else {

			return $employee->row_array();
		}			
	}

	function get_leaves($employee_id){

		$this->db->select('lb.employee_id, lb.vl, lb.sl, lb.el, lb.mpl, lb.bl');
		$this->db->from('employee_leave_balance lb');
		$this->db->join('user u', 'lb.employee_id = u.employee_id', 'left');
		$this->db->where('lb.employee_id', $employee_id);
		$this->db->where('lb.deleted', 0);
		$this->db->where('lb.year = YEAR(NOW())');
		$this->db->limit(1);

		$leaves = $this->db->get('user');

		$response->query = $this->db->last_query();

		if (!$leaves|| $leaves->num_rows() == 0) {

			return 0;

		} else {

			return $leaves->row_array();

		}

	}

	function get_user_position_details( $position_id ){

		if(empty( $position_id )) return false;
		
		$position_details = $this->db->get_where('user_position', array('position_id' => $position_id ));

		if($position_details->num_rows() == 1){
			$position_details = $position_details->row();
			return $position_details;
		}
		else{
			return false;
		}

	}

	function get_ir_details( $ir_id = 0 ){
		if(empty( $ir_id )) return false;
		
		$ir = $this->db->get_where('employee_ir', array('ir_id' => $ir_id ));
		if($ir->num_rows() == 1){
			$ir = $ir->row();
			return $ir->details;
		}
		else{
			return false;
		}
	}

	function get_approvers_only( $position_id, $module_id ){

		if(empty( $position_id )) return false;
		
		$approvers = $this->db->get_where('user_position_approvers', array('position_id' => $position_id, 'module_id' => $module_id ));

		if($approvers->num_rows() > 0){
			$approvers = $approvers->result_array();
			return $approvers;
		}
		else{
			return false;
		}
	}

	function get_approvers( $position_id, $module_id ){

		if(empty( $position_id )) return false;
		
		$approvers = $this->db->get_where('user_position_approvers', array('position_id' => $position_id, 'module_id' => $module_id , 'approver' => '1' ));

		if($approvers->num_rows() > 0){
			$approvers = $approvers->result_array();
			return $approvers;
		}
		else{
			return false;
		}
	}

	function get_email_approvers( $position_id, $module_id ){

		if(empty( $position_id )) return false;
		
		$approvers = $this->db->get_where('user_position_approvers', array('position_id' => $position_id, 'module_id' => $module_id , 'email' => '1', 'approver' => '0' ));

		if($approvers->num_rows() > 0){
			$approvers = $approvers->result_array();

			return $approvers;
		}
		else{
			return false;
		}
	}

	function get_approvers_subordinates( $position_id, $module_id ){

		if(empty( $position_id )) return false;
		
		$this->db->select('u.user_id, upa.position_id');
		$this->db->from('user u');
		$this->db->join('user_position_approvers upa','u.position_id = upa.position_id','left');
		$this->db->where('upa.approver_position_id',$position_id ); 
		$this->db->where('upa.module_id',$module_id );

		$approvers = $this->db->get();

		if($approvers->num_rows() > 0){
			$approvers = $approvers->result();
			return $approvers;
		}
		else{
			return false;
		}
	}

	function set_overdue_nte(){

		$now = date('Y-m-d h:i:s');
		$i_date = strtotime ( '-5 day' , strtotime ( $now ) ) ;
		$i_date = date ( 'Y-m-d h:i:s' , $i_date );

		$status_id = array(1,2);

		$this->db->from('employee_nte');
		$this->db->where('date_issued <= "' . $i_date . '"' );
		$this->db->where_in('nte_status_id',$status_id);
		$result = $this->db->get();

		foreach( $result->result() as $nte ){

			$this->db->update('employee_nte', array('nte_status_id' => 4), array('nte_id' => $nte->nte_id));

		}
	}

	function nte_pending_count( $ir_id = "" ){
		$where = array(
			'deleted' => 0,
		);
		$this->db->where( $where );
		if( !empty( $ir_id ) ) $this->db->where('ir_id', $ir_id);

		$this->db->where_in('nte_status_id',array(1,2,3,4));
		$this->db->from('employee_nte');

		return $this->db->count_all_results();
	}
	
	function get_reporting_to( $user_id = 0 ){
		if( empty( $user_id ) || $user_id < 0 ) return false;
		
		//check employee reporting to
		$emp = $this->db->get_where('employee', array('employee_id' => $user_id) );	
		if( $emp->num_rows() != 1 ) return false;
		
		$emp= $emp->row();

		//if( !empty($emp->reporting_to) ) return $emp->reporting_to;
		if( $emp->reporting_to != '""' ) return $emp->reporting_to; //tirso !empty doesn't work

		$user = $this->db->get_where('user', array('user_id' => $user_id));
		if( $user->num_rows() != 1 ) return false;
		$user = $user->row();
		
		$position = $this->db->get_where('user_position', array('position_id' => $user->position_id));
		if( $position->num_rows() != 1 ) return false;
		$position = $position->row();
		
		$user = $this->db->get_where('user', array('position_id' => $position->reporting_to));
		if( $user->num_rows() != 1 ) return false;
		$user = $user->row();
		return $user->user_id;
	}
	
	function get_da_offence_count( $offence_id = 0, $user_id = 0 ){
		if( empty($offence_id) || empty($user_id) ) return false;
		
		$offence = $this->db->get_where('offence', array('offence_id' => $offence_id))->row();
		
		$now = date('Y-m-d H:i:s');
		$ayearago = date('Y-m-d H:i:s', strtotime('last year'));
		$threeyearsago = date('Y-m-d H:i:s', strtotime('3 years ago'));
		
		switch( $offence->offence_level_id ){
			case 1:
				$retention = "AND a.date_issued BETWEEN '{$ayearago}' AND '{$now}'";
			case 2:
				$retention = "AND a.date_issued BETWEEN '{$threeyearsago}' AND '{$now}'";	
				break;
			case 3:
				$retention = "";		
		}
		
		//get the latest offence no 1
		$qry = "SELECT a.* 
		FROM {$this->db->dbprefix}employee_da a
		WHERE a.deleted = 0 AND a.employee_id = {$user_id}  AND a.offence_id = {$offence_id} AND a.offence_no = 1 ".$retention;
		$fst_offence = $this->db->query( $qry );
		
		if( $fst_offence->num_rows() == 0 ) return 0; //no offence was made for the last year
		if( $fst_offence->num_rows() != 1 ) return false; //wierd data
		
		//count all the 
		$qry = "SELECT a.* 
		FROM {$this->db->dbprefix}employee_da a
		WHERE a.deleted = 0 AND a.employee_id = {$user_id}  AND a.offence_id = {$offence_id} ".$retention;
		$no = $this->db->query( $qry );
		
		return $no->num_rows();
	}
	
	function whois_hrmanager(){
		$where = array(
			'deleted' => 0,
			'inactive' => 0,
			'role_id' => 2
		);
		$hr_manager = $this->db->get_where('user', $where);
		
		if( $hr_manager->num_rows() != 1 ) return false;
		
		return $hr_manager->row();
	}
	
	function update_application_status($applicant_id = 0, $candidate_status_id = 0){
		if( !empty( $candidate_status_id ) ){
			$candidate_status = $this->db->get_where('recruitment_candidate_status', array('candidate_status_id' => $candidate_status_id))->row();
			$this->db->update('recruitment_applicant', array('application_status_id' => $candidate_status->application_status_id ), array('applicant_id' => $applicant_id ));
		}
	}
	
	function set_candidate_status($candidate_id = 0, $candidate_status_id = 0){
		if(!empty( $candidate_status_id) && !empty($candidate_id)){
			$this->db->update('recruitment_manpower_candidate', array('candidate_status_id' =>  $candidate_status_id), array('candidate_id' => $candidate_id));
			$candidate = $this->db->get_where('recruitment_manpower_candidate',  array('candidate_id' => $candidate_id))->row();
			$this->update_application_status( $candidate->applicant_id,  $candidate_status_id);
		}
	}
	
	function get_employment_status( $employee_id = 0 ){
		if( empty( $employee_id ) ) return false;
		$qry = "select a.status_id, b.employment_status
		FROM {$this->db->dbprefix}employee a
		LEFT JOIN {$this->db->dbprefix}employment_status b on b.employment_status_id = a.status_id
		WHERE a.employee_id = {$employee_id}";
		$status = $this->db->query( $qry )->row();
		return $status;
	}
	
	function memo_type_filtertabs(){
		$memo_types = $this->db->get_where('memo_type', array('deleted' => 0));
		$memo_type_li = array();
		foreach( $memo_types->result() as $memo_type ){
			$memo_type_li[] = '<li class="active" filter_id="'. $memo_type->memo_type_id .'" filter_colum="memo_type_id"><a href="javascript:void(0)">'. $memo_type->memo_type .'</li>';
		}
		return addslashes('<ul id="grid-filter">'. implode('', $memo_type_li) .'</ul>');
	}

	/**
	 * Get the work schedule base on a date
	 * @param  integer $employee_id
	 * @param  string  $date
	 * @param  string  $cws_get_shift (I've seen that a lot of form uses this function, i use this to trap and get the shift instead of shift_calendar)
	 * @return standard object on success, otherwise false
	 */
	function get_employee_worksched( $employee_id = 0, $date = "", $cws_get_shift = false){
		if( empty( $employee_id ) ) return false;
		if( empty( $date ) ) return false;

		//tirso - to override grace period in timekeeping shift schedule if there is assign grace period in per employee type.
		$grace_period_per_employee_type = 0;

		if (CLIENT_DIR == 'firstbalfour'){
			$this->db->where('user.employee_id',$employee_id);
			$this->db->where('user.deleted',0);
			$this->db->join('employee','user.employee_id = employee.employee_id');
			$this->db->join('timekeeping_grace_period','employee.employee_type = timekeeping_grace_period.employee_type','left');
			$result = $this->db->get('user');

			if ($result && $result->num_rows() > 0){
				$row = $result->row();
				$grace_period_per_employee_type = $row->grace_period;
			}
		}		
		//

		//check if filed a cws for date
		$this->db->where('employee_cws.employee_id', $employee_id);
		$this->db->where( array( 'employee_cws.form_status_id' => 3, 'employee_cws.deleted' => 0) );
		$this->db->where( array( 'employee_cws_dates.date' => $date) );
		$this->db->join('employee_cws_dates', 'employee_cws_dates.employee_cws_id = employee_cws.employee_cws_id');
		$result = $this->db->get('employee_cws');

		if( $result->num_rows() == 0 ){

			// if employee have calendar worksched disregard everything below prioritize this.
			if($this->config->item('with_calendar_worksched') == 1)
			{
				$cal_result = $this->db->get_where('employee_dtr', array("employee_id" => $employee_id, "date" => $date, "shift_id <>" => -1));
				if($cal_result && $cal_result->num_rows() > 0)
				{
					$dtr_setup = $this->db->get_where('employee_dtr_setup', array('employee_id' => $employee_id, 'deleted' => 0))->row();
					$cal_result = $cal_result->row();
					if($cal_result->shift_id == 0)
						$cal_result->shift_id = 1;
					$worksched = $this->db->get_where('timekeeping_shift', array('shift_id' => $cal_result->shift_id))->row();
					$worksched->shift_calendar_id = $dtr_setup->shift_calendar_id;
					$worksched->has_cal_shift = true;

					//tirso - override shift schedule grace period.
					if($grace_period_per_employee_type > 0){
						$worksched->shift_grace_period = $grace_period_per_employee_type;
					}

					if (CLIENT_DIR == 'firstbalfour'){
						$worksched->considered_halfday = $this->check_considered_restday($worksched->shift_calendar_id,$date);
					}

					return $worksched;
				}
			}

			$qry = "SELECT *
			FROM {$this->db->dbprefix}workschedule_employee
			WHERE '{$date}' BETWEEN date_from AND date_to AND employee_id = '{$employee_id}' AND deleted = 0";
			$result = $this->db->query( $qry );
			
			if( $result->num_rows() == 0 ){
				$result = $this->db->get_where('employee_dtr_setup', array('employee_id' => $employee_id, 'deleted' => 0));
			}

			//return the first row
			if($cws_get_shift)
			{
				$worksched = $result->row();
				$worksched = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $worksched->shift_calendar_id))->row();
				$shift_calendar_id = $worksched->shift_calendar_id;
				switch( date('N', strtotime($date)) ){
					case 1:
						$shift_id = $worksched->monday_shift_id;
						break;
					case 2:
						$shift_id = $worksched->tuesday_shift_id;
						break;
					case 3:
						$shift_id = $worksched->wednesday_shift_id;
						break;
					case 4:
						$shift_id = $worksched->thursday_shift_id;
						break;
					case 5:
						$shift_id = $worksched->friday_shift_id;
						break;
					case 6:
						$shift_id = $worksched->saturday_shift_id;
						break;
					case 7:
						$shift_id = $worksched->sunday_shift_id;
						break;
				}

				//rest day
				if(empty($shift_id)) return false;

				$worksched = $this->db->get_where('timekeeping_shift', array('shift_id' => $shift_id))->row();
				$worksched->shift_calendar_id = $shift_calendar_id;

				//tirso - override shift schedule grace period.
				if($grace_period_per_employee_type > 0){
					$worksched->shift_grace_period = $grace_period_per_employee_type;
				}

				if (CLIENT_DIR == 'firstbalfour'){
					$worksched->considered_halfday = $this->check_considered_restday($worksched->shift_calendar_id,$date);
				}

				return $worksched;
			} else {
				$worksched = $result->row();
				$worksched = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $worksched->shift_calendar_id))->row();

				if (CLIENT_DIR == 'firstbalfour'){
					$worksched->considered_halfday = $this->check_considered_restday($worksched->shift_calendar_id,$date);
				}				

				return $worksched;	
			}

			//return the first row
			$worksched = $result->row();
			$worksched = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $worksched->shift_calendar_id))->row();

			if (CLIENT_DIR == 'firstbalfour'){
				$worksched->considered_halfday = $this->check_considered_restday($worksched->shift_calendar_id,$date);
			}

			return $worksched;			
		}
		else{
			$worksched = $result->row();
			$shift_calendar_id = $worksched->current_shift_calendar_id;
			$worksched = $this->db->get_where('timekeeping_shift', array('shift_id' => $worksched->shift_id))->row();
			$worksched->shift_calendar_id = $shift_calendar_id;
			$worksched->has_cws = true;

			//tirso - override shift schedule grace period.
			if($grace_period_per_employee_type > 0){
				$worksched->shift_grace_period = $grace_period_per_employee_type;
			}

			if (CLIENT_DIR == 'firstbalfour'){
				$worksched->considered_halfday = $this->check_considered_restday($worksched->shift_calendar_id,$date);
			}

			return $worksched;
		}
	}

	function get_grace_period($employee_id = false){
		$this->db->where('user.employee_id',$employee_id);
		$this->db->where('user.deleted',0);
		$this->db->join('employee','user.employee_id = employee.employee_id');
		$this->db->join('timekeeping_grace_period','employee.employee_type = timekeeping_grace_period.employee_type','left');
		$result = $this->db->get('user');

		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			if ($row->grace_period != ''){
				return $row->grace_period;
			}
			else{
				return 0;
			}
		}
		else{
			return 0;
		}
	}

	function check_considered_restday($shift_calendar_id,$date){
		$shift_id_considered_restday = 0;		
		$result = $this->db->get_where('timekeeping_shift_calendar_override', array('shift_calendar_id' => $shift_calendar_id));
		
		if ($result && $result->num_rows() > 0){
			$worksched_considered_restday = $result->row();

			switch( date('N', strtotime($date)) ){
				case 1:
					$shift_id_considered_restday = $worksched_considered_restday->monday_shift_id;
					break;
				case 2:
					$shift_id_considered_restday = $worksched_considered_restday->tuesday_shift_id;
					break;
				case 3:
					$shift_id_considered_restday = $worksched_considered_restday->wednesday_shift_id;
					break;
				case 4:
					$shift_id_considered_restday = $worksched_considered_restday->thursday_shift_id;
					break;
				case 5:
					$shift_id_considered_restday = $worksched_considered_restday->friday_shift_id;
					break;
				case 6:
					$shift_id_considered_restday = $worksched_considered_restday->saturday_shift_id;
					break;
				case 7:
					$shift_id_considered_restday = $worksched_considered_restday->sunday_shift_id;
					break;
			}			
		}		
		return $shift_id_considered_restday;
	}

	/**
	 * Get the work schedule base on a date
	 * @param  integer $employee_id
	 * @param  string  $date
	 * @return standard object on success, otherwise false
	 */
	function get_employee_all_worksched( $employee_id = 0, $date = "" ){
		if( empty( $employee_id ) ) return false;
		if( empty( $date ) ) return false;

		//check if filed a cws for date
		$this->db->where('employee_cws.employee_id', $employee_id);
		$this->db->where( array( 'employee_cws.form_status_id' => 3, 'employee_cws.deleted' => 0) );
		$this->db->where( array( 'employee_cws_dates.date' => $date) );
		$this->db->join('employee_cws_dates', 'employee_cws_dates.employee_cws_id = employee_cws.employee_cws_id');
		$result = $this->db->get('employee_cws');
		
		if( $result->num_rows() == 0 ){

			$qry = "SELECT *
			FROM {$this->db->dbprefix}workschedule_group
			WHERE '{$date}' BETWEEN date_from AND date_to AND ( ( employee_id LIKE '{$employee_id}' ) || ( employee_id LIKE '{$employee_id},%' ) || ( employee_id LIKE '%,{$employee_id}' )  || ( employee_id LIKE '%,{$employee_id},%' ) )";
			$result = $this->db->query( $qry );

			if( $result->num_rows() == 0 ){

				$qry = "SELECT *
				FROM {$this->db->dbprefix}workschedule_employee
				WHERE '{$date}' BETWEEN date_from AND date_to AND employee_id = '{$employee_id}'";
				$result = $this->db->query( $qry );
				if( $result->num_rows() == 0 ){
					$result = $this->db->get_where('employee_dtr_setup', array('employee_id' => $employee_id));
				}
				//original code before change work schedule modified
				if( $result->num_rows() > 0 ){
					//return the first row
					$worksched = $result->row();
					$worksched = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $worksched->shift_calendar_id))->row();
					return $worksched;
				}
				else{
					false;
				}		

			}	

			//original code before change work schedule modified
			if( $result->num_rows() > 0 ){
				//return the first row
				$worksched = $result->row();
				$worksched = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $worksched->shift_calendar_id))->row();
				return $worksched;
			}
			else{
				false;
			}	

		}
		else{
			if( $result->num_rows() > 0 ){
				//return the first row
				$worksched = $result->row();
				$worksched = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $worksched->shift_id))->row();
				return $worksched;
			}
			else{
				false;
			}
		}
	}

	/**
	 * Check wether a given holiday is a holiday
	 * @param  string $date, int $employee_id (optional **use for checking holiday by location**)
	 * @return mixed, false if not holiday, otherwise array
	 */
	function holiday_check( $date = "", $employee_id="" , $dont_get_exclude = false) {
		if( !empty($date) ) {
			$hol_id = 0;

			// get department to exclude
			$emp_department = $this->db->get_where('user', array('employee_id' => $employee_id))->row()->department_id;

			// check legal holiday
			if($dont_get_exclude)
				$this->db->where("(NOT FIND_IN_SET('".$emp_department."', excluded) OR excluded IS NULL)");
			
			$check_holiday = $this->db->get_where('holiday', array('date_set' => $date,	'inactive' => 0, 'deleted' => 0, 'legal_holiday' => 1))->result_array();

			if( !empty($check_holiday) ) // if legal holiday just return it
				return $check_holiday; 
			
			// check holiday without location
			$query = "SELECT *
					  FROM ".$this->db->dbprefix."holiday
					  WHERE date_set = '".$date."' 
					  AND inactive = 0 
					  AND deleted = 0 
					  AND legal_holiday = 0 
					  AND (location_id IS NULL 
					  	   or location_id = '')
					  ";

			$check_holiday = $this->db->query($query)->result_array();

			if( !empty($check_holiday) ) 
			{
				foreach($check_holiday as $holiday_affected)
					$hol_id=$holiday_affected['holiday_id'].",".$hol_id; 
			}

			// get holiday by location
			if( !empty($employee_id) ) {

				// get employee location
				$location = $this->db->get_where('employee', array('employee_id' => $employee_id))->row_array();


				// get affected location
				$query_2 = "SELECT * 
							FROM ".$this->db->dbprefix."holiday
							WHERE date_set = '".$date."' 
							AND inactive = 0 
							AND deleted = 0 
							AND location_id IS NOT NULL
							";

				$result = $this->db->query($query_2);
				$results = $result->result_array();
				$pieces = array();

				if( $result->num_rows() > 1 ) {

					foreach( $results as $super ) {
						$merge_me = explode(',', $super['location_id']);
						if(in_array($location['location_id'], $merge_me))
							$hol_id=$hol_id.",".$super['holiday_id'];
					}
				} else {	
					if(isset($results[0])){
						$pieces = explode(',', $results[0]['location_id']);
						if(in_array($location['location_id'], $pieces))
							$hol_id = $results[0]['holiday_id'].",".$hol_id;
					}
				}

				$qry = "SELECT * 
				        FROM ".$this->db->dbprefix."holiday
					    WHERE holiday_id IN( $hol_id )";

				if($dont_get_exclude)
				$qry .= "AND (NOT FIND_IN_SET('".$emp_department."', excluded)
					  		  OR excluded IS NULL)
					  	";

				$result=$this->db->query($qry);

				if($result && $result->num_rows() > 0)
					return $result->result_array();
				else 
					return false;

			} else {
				//if there is no assigned employee
				$result = $this->db->get_where('holiday', array('date_set' => $date, 'inactive' => 0, 'deleted' => 0));
				if( $result->num_rows() > 0 )
					return $result->result_array();					
				else
					return false;
			}
		} else 
			return false;
	}

	function get_affected_dates( $employee_id, $start_date, $end_date, $restday = false,$dont_get_exclude = false ){
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date = date('Y-m-d', strtotime($end_date));
		$days = array();
		$days_ctr = 0; 
		while( $start_date <= $end_date ){ //to start day count by the following day
			//check if holidays
			$on_holiday = $this->system->holiday_check( $start_date, $employee_id, $dont_get_exclude ); // check wether date is holiday and applies to employee specified

			if( !$on_holiday ){
				//get the work sched
				$worksched = $this->system->get_employee_worksched( $employee_id, $start_date, true);
				if( $worksched && (!empty( $worksched->shift_id ) || ( $worksched->shift_id == 0 && $restday)) ){
					if ($worksched->shift_id != 1 && $restday){
						$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
						$days[$days_ctr]['employee_leave_date_id'] = '0';
						$days_ctr++;
					}
				}
				
			}

			$start_date = date('Y-m-d', strtotime($start_date . '+1 day') );
		}
		return $days;
	}

	/**
	 * get the subordinates of a certain employee base on 201 supervisor
	 * @param  integer $user_id 
	 * @return array 	an array of users
	 */
	function get_supervised( $user_id = 0, $position_id = 0 ){
		$users = array();
		if( !empty( $user_id ) ) {
			$qry = "select a.*, b.supervisor_id
			FROM {$this->db->dbprefix}user a
			LEFT JOIN {$this->db->dbprefix}employee b on b.user_id = a.user_id
			where b.supervisor_id = {$user_id} AND a.deleted = 0 AND a.inactive = 0 AND b.resigned_date IS NULL";
			if( !empty( $position_id ) ) $qry .= " AND a.position_id = {$position_id}";
			$recs = $this->db->query( $qry );

			if( $recs->num_rows() > 0 ){
				foreach( $recs->result_array() as $rec ){
					$users[] = $rec;
					$subs = $this->get_supervised( $rec['user_id'] );
					if( sizeof( $subs ) > 0 ) $users = array_merge($users, $subs);
				}
			}
		}
		return $users;
	}

	/**
	 * get the subordinates of a certain employee base on 201 supervisor
	 * @param  integer $user_id 
	 * @return array 	an array of users
	 */
	function get_to_approve( $user_id = 0, $position_id = 0, $module_id = 0 ){
		$users = array();
		$positions = $this->db->get_where('user_position_approvers', array('approver_position_id' => $position_id, 'module_id' => $module_id, 'approver' => 1));
		if($positions->num_rows() > 0){
			foreach($positions->result() as $row){
				$employees = $this->db->get_where('user', array('deleted' => 0, 'inactive' => 0, 'position_id' => $row->position_id));
				if($employees->num_rows() > 0){
					foreach($employees->result_array() as $emp){
						$users[] = $emp;
					}
				}
			}
		}

		return $users;
	}

	function is_module_approver( $module_id, $position_id ){
		$rec = $this->db->get_where('user_position_approvers', array('approver_position_id' => $position_id, 'module_id' => $module_id));
		if( $rec->num_rows() > 0 )
			return $rec->result();
		else
			return false;
	}

	function get_module_approver( $module_id, $position_id ){
		$rec = $this->db->get_where('user_position_approvers', array('position_id' => $position_id, 'module_id' => $module_id));
		if( $rec->num_rows() > 0 )
			return $rec->result_array();
		else
			return false;
	}

	/** 
	 * This will check if employee is approver in employee approver and position approver.
	 * @return Boolean
	 */
	function check_if_approver($module_id = false, $position_id = false, $user_id = false) {
		$module_id = !$module_id ? $this->module_id : $module_id;
		$position_id = !$position_id ? $this->userinfo['position_id'] : $position_id;
		$user_id = !$user_id ? $this->userinfo['user_id'] : $user_id;

		$e_approver = $this->db->get_where('employee_approver', array('deleted' => 0, 'approver' => 1, 'approver_employee_id' => $user_id, 'module_id' => $module_id));

		if($e_approver && $e_approver->num_rows() > 0) {
			return true;
		} else {
			$p_approver = $this->db->get_where('user_position_approvers', array('position_id' => $position_id, 'approver' => 1, 'module_id' => $this->module_id));

			if($p_approver && $p_approver->num_rows() > 0) 
				return true;
			else 
				return false;
		}
	}

	function get_children( $user_id ){
		$this->db->where( array('employee_id' => $user_id) );
		$this->db->where('(relationship = "Daughter" OR relationship = "Son" OR relationship = "Child")', '',false);
		$this->db->order_by('birth_date','ASC');
		$recs = $this->db->get('employee_family');
		if( $recs->num_rows() > 0 ){
			return $recs->result_array();
		}
		else{
			return false;
		}
	}

	function get_children_sorted_date( $user_id ){
		$this->db->where( array('employee_id' => $user_id) );
		$this->db->where('(relationship = "Daughter" OR relationship = "Son" OR relationship = "Child")', '',false);
		$this->db->order_by('birth_date','asc');
		$recs = $this->db->get('employee_family');
		if( $recs->num_rows() > 0 ){
			return $recs->result_array();
		}
		else{
			return false;
		}
	}

	function get_leave_balance( $year, $employee_id ){
		if($this->config->item('filing_with_carried') == 0)
		{
			$balance = $this->db->get_where('employee_leave_balance', array('year' => $year, 'employee_id' => $employee_id, 'deleted' => 0));

			if( $balance->num_rows() == 1 )
				return $balance->row();
			else
				return false;
		} elseif($this->config->item('filing_with_carried') == 1) {

			$balance = $this->db->get_where('employee_leave_balance', array('year' => $year, 'employee_id' => $employee_id, 'deleted' => 0));

			if($balance->num_rows() > 0)
			{
				$balance = $balance->row();
				$balance->vl = $balance->vl + $balance->carried_vl;
				$balance->sl = $balance->sl + $balance->carried_sl;

				return $balance;
			} else
				return false;
		}
	}
	
	//change 1 to 0 since RESTDAY = 1 shift_id
    function get_employee_rest_day($employee_id = FALSE,$date = FALSE){
        $array_stack[] = array();
        $emp = $this->system->get_employee_worksched($employee_id, date('Y-m-d', strtotime($date)));  

        if ($emp->monday_shift_id == 1):
            $array_stack[] = "Mon";
        endif;
        if ($emp->tuesday_shift_id == 1):
            $array_stack[] = "Tue";
        endif;
        if ($emp->wednesday_shift_id == 1):
            $array_stack[] = "Wed";
        endif;
        if ($emp->thursday_shift_id == 1):
            $array_stack[] = "Thu";
        endif;
        if ($emp->friday_shift_id == 1):
            $array_stack[] = "Fri";
        endif;
        if ($emp->saturday_shift_id == 1):
            $array_stack[] = "Sat";
        endif;
        if ($emp->sunday_shift_id == 1):
            $array_stack[] = "Sun";
        endif;

        return $array_stack;
    }

    /*
    	Change Log:
    		2013-08-23	HDR 	Added $mode option, values are 'approver', 'email', 'both' or 'either'

     */
    function get_approvers_and_condition( $employee_id, $module_id, $mode = 'approver' ){
    	//get employee approver
    	$qry = "SELECT a.*, b.rank_id, c.rank_index
		FROM {$this->db->dbprefix}employee_approver a
		LEFT JOIN {$this->db->dbprefix}employee b ON b.employee_id = a.approver_employee_id
		LEFT JOIN {$this->db->dbprefix}user_rank c ON c.job_rank_id = b.rank_id
		WHERE a.deleted = 0 AND a.module_id = {$module_id}";
		if(!empty($employee_id)) {
			$qry .= " AND a.employee_id = {$employee_id} ";
		}
		switch( $mode ){
			case 'email':
				$qry .= " AND a.email = 1";
				break;
			case 'both':
				$qry .= " AND a.approver = 1 AND a.email = 1";
				break;
			case 'either':
				$qry .= " AND (a.approver = 1 OR a.email = 1)";
				break;
			case 'approver':
			default:
				$qry .= " AND a.approver = 1";
		}

		$qry .= " ORDER BY a.employee_approver_id asc";
		//ORDER BY c.rank_index asc";
    	$result = $this->db->query( $qry );
		 	
    	if( $result->num_rows() == 0 ){
    		// get approver base on position
    		$user = $this->db->get_where('user', array('user_id' => $employee_id))->row();
			$position_id = $user->position_id;

			switch( $mode ){
				case 'email':
					$this->db->where('email', 1);
					break;
				case 'both':
					$this->db->where('approver', 1);
					$this->db->where('email', 1);
					break;
				case 'either':
					$this->db->where('approver = 1 OR email = 1');
					break;
				case 'approver':
				default:
					$this->db->where('approver', 1);
			}
			$this->db->where( array('position_id' => $position_id, 'module_id' => $this->module_id) );
			$this->db->order_by('approver_no', 'asc');
			$result = $this->db->get('user_position_approvers');
			if ($result->num_rows() == 0) {
				//get default reporting to
				$position = $this->db->get_where('user_position', array('position_id' => $position_id))->row();

				if ($position->reporting_to != 0 && $position->reporting_to != null){
					$approver = $this->db->get_where('user', array('position_id' => $position->reporting_to))->row();
					$approvers[] = array(
		    			'approver' => $approver->user_id,
		    			'sequence' => 1,
		    			'condition' => 2,
		    			'focus' => 1,
		    			'status' => 1
		    		);
					return $approvers;
				}
				else{
					return array();
				}
			}

			$sequence = 1;
			foreach($result->result() as $row){
				$focus = 0;
	    		switch($row->condition){
	    			case 1:
	    				if($sequence == 1) $focus = 1;
	    				break;
	    			case 2:
	    			case 3:
	    			default:
	    				$focus = 1;
	    				break;
	    		}

				$approver = $this->db->get_where('user', array('position_id' => $row->approver_position_id))->row();
				$approvers[] = array(
	    			'approver' => $approver->user_id,
	    			'sequence' => $sequence,
	    			'condition' => empty($row->condition) ? 3: $row->condition,
	    			'focus' => $focus,
	    			'status' => 1
	    		);
				$sequence++;
			}

			return $approvers;
    	}

    	$sequence = 1;
    	foreach( $result->result() as $row ){
    		$focus = 0;
    		switch($row->condition){
    			case 1:
    				if($sequence == 1) $focus = 1;
    				break;
    			case 2:
    			case 3:
    				$focus = 1;
    				break;
    		}

    		$approvers[] = array(
    			'approver' => $row->approver_employee_id,
    			'sequence' => $sequence,
    			'condition' => $row->condition,
    			'focus' => $focus,
    			'status' => 1
    		);
    		$sequence++;
    	}
    	return $approvers;
    }

     function get_approvers_emails_and_condition( $employee_id, $module_id ){
    	//get employee approver
    	$qry = "SELECT a.*, b.rank_id, c.rank_index
		FROM {$this->db->dbprefix}employee_approver a
		LEFT JOIN {$this->db->dbprefix}employee b ON b.employee_id = a.approver_employee_id
		LEFT JOIN {$this->db->dbprefix}user_rank c ON c.job_rank_id = b.rank_id
		WHERE a.deleted = 0 AND ( a.approver = 1 OR a.email = 1 ) AND
		a.employee_id = {$employee_id} AND a.module_id = {$module_id}
		ORDER BY a.employee_approver_id asc";
		//ORDER BY c.rank_index asc";
    	$result = $this->db->query( $qry );
		 	
    	if( $result->num_rows() == 0 ){
    		// get approver base on position
    		$user = $this->db->get_where('user', array('user_id' => $employee_id))->row();
			$position_id = $user->position_id;

			$this->db->order_by('approver_no', 'asc');
			$this->db->where('position_id',$position_id);
			$this->db->where('module_id',$this->module_id);
			$this->db->or_where('approver',1);
			$this->db->or_where('email',1);
			$result = $this->db->get('user_position_approvers');
			if ($result->num_rows() == 0) {
				//get default reporting to
				$position = $this->db->get_where('user_position', array('position_id' => $position_id))->row();
				$approver = $this->db->get_where('user', array('position_id' => $position->reporting_to))->row();
				$approvers[] = array(
	    			'approver' => $approver->user_id,
	    			'sequence' => 1,
	    			'condition' => 2,
	    			'focus' => 1,
	    			'status' => 1
	    		);
				return $approvers;
			}

			$sequence = 1;
			foreach($result->result() as $row){
				$focus = 0;
	    		switch($row->condition){
	    			case 1:
	    				if($sequence == 1) $focus = 1;
	    				break;
	    			case 2:
	    			case 3:
	    			default:
	    				$focus = 1;
	    				break;
	    		}

				$approver = $this->db->get_where('user', array('position_id' => $row->approver_position_id))->row();
				$approvers[] = array(
	    			'approver' => $approver->user_id,
	    			'sequence' => $sequence,
	    			'condition' => empty($row->condition) ? 3: $row->condition,
	    			'focus' => $focus,
	    			'status' => 1,
	    			'approve' => $row->approver,
	    			'email' => $row->email
	    		);
				$sequence++;
			}

			return $approvers;
    	}

    	$sequence = 1;
    	foreach( $result->result() as $row ){
    		$focus = 0;
    		switch($row->condition){
    			case 1:
    				if($sequence == 1) $focus = 1;
    				break;
    			case 2:
    			case 3:
    				$focus = 1;
    				break;
    		}

    		$approvers[] = array(
    			'approver' => $row->approver_employee_id,
    			'sequence' => $sequence,
    			'condition' => $row->condition,
    			'focus' => $focus,
    			'status' => 1,
    			'approve' => $row->approver,
	    		'email' => $row->email
    		);
    		$sequence++;
    	}
    	return $approvers;
    }

    function get_leaves_to_approve($employee_id, $status, $focus, $from_dashboard = false, $project_hr = 0){
    	$orig_employee_id = $employee_id;
    	$leaves_from_project = array();
    	$leaves_from_reporting_to = array();
    	$leaves = array();

    	$employee_id = 'a.approver = '.$employee_id;

    	if( !$from_dashboard && (( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1)){
    		$employee_id = "1";
    		if ($project_hr == 1 && !( $this->is_superadmin || $this->is_admin )){
    			$employee_id = 'a.approver = '.$orig_employee_id;
    		}
    	}

        if ($project_hr == 1 && !( $this->is_superadmin || $this->is_admin )){
        	$leaves_from_project = $this->get_leaves_to_approve_by_project($employee_id, $status, $focus, $from_dashboard);
        }
        
        if (CLIENT_DIR == "firstbalfour"){
        	$leaves_from_reporting_to = $this->get_leaves_to_approve_by_reporting_to($employee_id, $status, $focus, $from_dashboard);
        }

    	$qry = "SELECT a.*
		FROM {$this->db->dbprefix}leave_approver a
		LEFT JOIN {$this->db->dbprefix}employee_leaves b ON b.employee_leave_id = a.leave_id
		WHERE {$employee_id} AND b.form_status_id != 1 AND b.form_status_id != 6 AND a.focus {$focus} AND
		b.deleted = 0 AND a.status {$status}";
        $leaves_to_approve = $this->db->query( $qry );

        if (CLIENT_DIR == "firstbalfour"){
	        if( $leaves_to_approve->num_rows() > 0 ){
	        	foreach( $leaves_to_approve->result() as $leave ){
	        		$leaves[] = $leave->leave_id;
	        	}
	        }

			$leaves = (is_array($leaves))?$leaves:array($leaves);
			$leaves_from_reporting_to = (is_array($leaves_from_reporting_to))?$leaves_from_reporting_to:array($leaves_from_reporting_to);
			$leaves_from_project = (is_array($leaves_from_project))?$leaves_from_project:array($leaves_from_project);

	        $leaves = array_filter(array_unique(array_merge($leaves, $leaves_from_reporting_to, $leaves_from_project)));

	        if (!empty($leaves)){
	        	return $leaves;
	        }
	        else{
	        	return false;
	        }
        }
        else{
	        if( $leaves_to_approve->num_rows() > 0 ){
	        	foreach( $leaves_to_approve->result() as $leave ){
	        		$leaves[] = $leave->leave_id;
	        	}
	        	return $leaves;
	        } 
    	}

	    return false;
    }

    function get_leaves_to_approve_by_reporting_to($employee_id, $status, $focus, $from_dashboard = false){
    	$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
    	$subordinates_id = array();

    	if (!empty($subordinates)){
    		foreach ($subordinates as $key => $value) {
    			$subordinates_id[] = $value['employee_id'];
    		}

    		$string = '"' . implode('","', $subordinates_id) . '"';
    		$employee_id = 'b.employee_id IN ('.$string.')';

/*	    	if( !$from_dashboard && (( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1)){
	    		$employee_id = "1";
	    	}*/

	    	$qry = "SELECT a.*
			FROM {$this->db->dbprefix}leave_approver a
			LEFT JOIN {$this->db->dbprefix}employee_leaves b ON b.employee_leave_id = a.leave_id
			WHERE {$employee_id} AND b.form_status_id != 1 AND b.form_status_id != 6 AND a.focus {$focus} AND
			b.deleted = 0 AND a.status {$status}";
	        $leaves_to_approve = $this->db->query( $qry );

	        if( $leaves_to_approve->num_rows() > 0 ){
	        	foreach( $leaves_to_approve->result() as $leave ){
	        		$leaves[] = $leave->leave_id;
	        	}
	        	return $leaves;
	        }
	        return false;    		
    	}
    	else{
    		return false;
    	}
    }

    function get_leaves_to_approve_by_project($employee_id, $status, $focus, $from_dashboard = false){
    	$subordinates = $this->get_subordinates_by_project($this->userinfo['user_id']);
    	$subordinates_id = array();

    	if (!empty($subordinates)){
    		foreach ($subordinates as $key => $value) {
    			$subordinates_id[] = $value['employee_id'];
    		}

    		$string = '"' . implode('","', $subordinates_id) . '"';
    		$employee_id = 'b.employee_id IN ('.$string.')';

/*	    	if( !$from_dashboard && (( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1)){
	    		$employee_id = "1";
	    	}*/

	    	$qry = "SELECT a.*
			FROM {$this->db->dbprefix}leave_approver a
			LEFT JOIN {$this->db->dbprefix}employee_leaves b ON b.employee_leave_id = a.leave_id
			WHERE {$employee_id} AND b.form_status_id != 1 AND b.form_status_id != 6 AND a.focus {$focus} AND
			b.deleted = 0 AND a.status {$status}";
	        $leaves_to_approve = $this->db->query( $qry );

	        if( $leaves_to_approve->num_rows() > 0 ){
	        	foreach( $leaves_to_approve->result() as $leave ){
	        		$leaves[] = $leave->leave_id;
	        	}
	        	return $leaves;
	        }
	        return false;    		
    	}
    	else{
    		return false;
    	}
    }

    function get_forms_to_approve($employee_id, $status, $focus, $project_hr = 0){
    	$orig_employee_id = $employee_id;
    	$forms_from_project = array();
    	$forms_from_reporting_to = array();
    	$forms = array();

    	if(( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1){
    		$employee_id = "1";
    		if ($project_hr == 1 && !( $this->is_superadmin || $this->is_admin )){
    			$employee_id = 'a.approver = '.$orig_employee_id;
    		}    		
    	}
    	else{
    		$employee_id = 'a.approver = '.$employee_id;
    	}

        if ($project_hr == 1 && !( $this->is_superadmin || $this->is_admin )){
        	$forms_from_project = $this->get_forms_to_approve_by_project($employee_id, $status, $focus);
        }
        
        if (CLIENT_DIR == "firstbalfour"){
        	$forms_from_reporting_to = $this->get_forms_to_approve_reporting_to($employee_id, $status, $focus);
        }

    	$qry = "SELECT a.*
		FROM {$this->db->dbprefix}form_approver a
		LEFT JOIN {$this->db->dbprefix}{$this->module_table} b ON b.{$this->key_field} = a.record_id
		WHERE {$employee_id} AND b.form_status_id != 1 AND a.focus {$focus} 
		AND a.module_id = {$this->module_id} AND b.deleted = 0 AND a.status {$status}";
        $forms_to_approve = $this->db->query( $qry );
       	
       	if (CLIENT_DIR == "firstbalfour"){
	        if( $forms_to_approve->num_rows() > 0 ){
	        	foreach( $forms_to_approve->result() as $form ){
	        		$forms[] = $form->record_id;
	        	}
		    }   

			$forms = (is_array($forms))?$forms:array($forms);
			$forms_from_reporting_to = (is_array($forms_from_reporting_to))?$forms_from_reporting_to:array($forms_from_reporting_to);
			$forms_from_project = (is_array($forms_from_project))?$forms_from_project:array($forms_from_project);

	        $forms = array_filter(array_unique(array_merge($forms, $forms_from_reporting_to, $forms_from_project)));

	        if (!empty($forms)){
	        	return $forms;
	        }
	        else{
	        	return false;
	        }

       	}
       	else{
	        if( $forms_to_approve->num_rows() > 0 ){
	        	foreach( $forms_to_approve->result() as $form ){
	        		$forms[] = $form->record_id;
	        	}
	        	return $forms;
	        }
       	}
        return false;
    }	

    function get_forms_to_approve_reporting_to($employee_id, $status, $focus){
    	$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
    	$subordinates_id = array();

    	if (!empty($subordinates)){
    		foreach ($subordinates as $key => $value) {
    			$subordinates_id[] = $value['employee_id'];
    		}

    		$string = '"' . implode('","', $subordinates_id) . '"';
    		$employee_id = 'b.employee_id IN ('.$string.')';

	    	$qry = "SELECT a.*
			FROM {$this->db->dbprefix}form_approver a
			LEFT JOIN {$this->db->dbprefix}{$this->module_table} b ON b.{$this->key_field} = a.record_id
			WHERE {$employee_id} AND b.form_status_id != 1 AND a.focus {$focus} 
			AND a.module_id = {$this->module_id} AND b.deleted = 0 AND a.status {$status}";
	        $forms_to_approve = $this->db->query( $qry );

	        if( $forms_to_approve->num_rows() > 0 ){
	        	foreach( $forms_to_approve->result() as $form ){
	        		$forms[] = $form->record_id;
	        	}
	        	return $forms;
	        }
	        return false;    		
    	}
    	else{
    		return false;
    	}
    }	

    function get_forms_to_approve_by_project($employee_id, $status, $focus){
    	$subordinates = $this->get_subordinates_by_project($this->userinfo['user_id']);
    	$subordinates_id = array();

    	if (!empty($subordinates)){
    		foreach ($subordinates as $key => $value) {
    			$subordinates_id[] = $value['employee_id'];
    		}

    		$string = '"' . implode('","', $subordinates_id) . '"';
    		$employee_id = 'b.employee_id IN ('.$string.')';

	    	$qry = "SELECT a.*
			FROM {$this->db->dbprefix}form_approver a
			LEFT JOIN {$this->db->dbprefix}{$this->module_table} b ON b.{$this->key_field} = a.record_id
			WHERE {$employee_id} AND b.form_status_id != 1 AND a.focus {$focus} 
			AND a.module_id = {$this->module_id} AND b.deleted = 0 AND a.status {$status}";
	        $forms_to_approve = $this->db->query( $qry );

	        if( $forms_to_approve->num_rows() > 0 ){
	        	foreach( $forms_to_approve->result() as $form ){
	        		$forms[] = $form->record_id;
	        	}
	        	return $forms;
	        }
	        return false;    		
    	}
    	else{
    		return false;
    	}
    }

    function get_subordinates_by_project($employee_id){
    	$this->db->select('project_name_id');
		$this->db->where('employee_id',$employee_id);
		$this->db->where('user.deleted',0);
		$this->db->where('project_name_id <>',0);
		$result = $this->db->get('user');

		$project_name_id = array();
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$project_name_id[] = $row->project_name_id;
			}
		}

		if (count($project_name_id) > 0){
			$this->db->select('user.*, employee.employee_id as employee_id,employee.rank_id, position');
			$this->db->where_in('user.project_name_id',$project_name_id);			
			$this->db->where('employee.resigned', 0);
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');			
			// $this->db->join('user', 'employee.employee_id = user.employee_id', 'left');			
			$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
			$this->db->group_by('employee.employee_id');
			$subordinates_by_project_result = $this->db->get('user');
			
			if( $subordinates_by_project_result && $subordinates_by_project_result->num_rows() > 0 ){
				return $subordinates_by_project_result->result_array();
			}		
			else{
				return false;
			}
		}
		return false;		
    }

    function get_employee_worksched_shift($employee_id = FALSE,$date = FALSE){
        return $this->get_employee_worksched($employee_id, date('Y-m-d', strtotime($date)), true);  
        
    }

    function get_next_working_day( $employee_id, $start_date ){
    	$loop = true;
    	//$year = date('Y-', strtotime($start_date));
		$next_day = date('Y-m-d', strtotime( '+1 day' . $start_date));
		//$next_day = $year . $next_day;
		$next_working_day = "";

		while( $loop ){
			$_POST['date_from'] = $next_day;
			$_POST['date_to'] = $next_day;
			$affected =  $this->get_affected_dates( $employee_id, $next_day, $next_day, true );
			if(sizeof($affected) == 1 ){
				$next_working_day = $next_day;
				$loop = false;
			}else{
				$next_day = date('Y-m-d', strtotime( '+1 day' . $next_day));
			}
		}
		return $next_working_day;
    } 

    function get_prev_working_day( $employee_id, $start_date ){
    	$loop = true;
    	//$year = date('Y-', strtotime($start_date));
		$prev_day = date('Y-m-d', strtotime( '-1 day' . $start_date));
		//$prev_day = $year . $prev_day;
		$prev_working_day = "";

		while( $loop ){
			$_POST['date_from'] = $prev_day;
			$_POST['date_to'] = $prev_day;
			$affected =  $this->get_affected_dates( $employee_id, $prev_day, $prev_day, true );
			if(sizeof($affected) == 1 ){
				$prev_working_day = $prev_day;
				$loop = false;
			}else{
				$prev_day = date('Y-m-d', strtotime( '-1 day' . $prev_day));
			}
		}
		return $prev_working_day;
    } 

	function get_working_days( $employee_id, $start_date, $end_date, $restday = true,$dont_get_exclude = false, $exclude_holiday_check=false ){
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date = date('Y-m-d', strtotime($end_date));
		$days = array();
		$days_ctr = 0; 
		while( $start_date <= $end_date ){ //to start day count by the following day
			if($exclude_holiday_check){
				//get the work sched
				$worksched = $this->system->get_employee_worksched( $employee_id, $start_date, true);
				if( $worksched && (!empty( $worksched->shift_id ) || ( $worksched->shift_id == 0 && $restday)) ){
					if ($worksched->shift_id != 1 && $restday){
							$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
							$days[$days_ctr]['employee_leave_date_id'] = '0';
							$days_ctr++;
					}
				}
			}else{
				//check if holidays
				$on_holiday = $this->system->holiday_check( $start_date, $employee_id, $dont_get_exclude ); // check wether date is holiday and applies to employee specified

				if( !$on_holiday ){
					//get the work sched
					$worksched = $this->system->get_employee_worksched( $employee_id, $start_date, true);
					if( $worksched && (!empty( $worksched->shift_id ) || ( $worksched->shift_id == 0 && $restday)) ){
						if ($worksched->shift_id != 1 && $restday){
								$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
								$days[$days_ctr]['employee_leave_date_id'] = '0';
								$days_ctr++;
						}
					}
				}
			}

			$start_date = date('Y-m-d', strtotime($start_date . '+1 day') );
		}
		return $days;
	}

    function get_previous_cut_off($date_from){
		//$where = '(\'' . $date_from . '\' BETWEEN date_from AND date_to AND \''. $date_to . '\'  BETWEEN date_from AND date_to)';
		//$this->db->where($where, '', false);

        $this->db->where('(\'' . date('Y-m-d',strtotime($date_from)) . '\' BETWEEN date_from AND date_to)', '', false);
        $result = $this->db->get('timekeeping_period');
        if($result):
            if ($result->num_rows() > 0):
                $row = $result->row();
                $prev_date_cutoff_p = date('Y-m-d', strtotime('-1 day', strtotime($row->date_from)));                        
                $this->db->where('date_from <=',$prev_date_cutoff_p);
                $this->db->order_by("date_from", "desc"); 
                $result = $this->db->get('timekeeping_period');                 
                $prev_date_cutoff = $result->row();                      
                return $prev_date_cutoff;
            else:
            	return false;                          
            endif;
        else:
			return false;
        endif;	
    }  

    function get_in_cut_off($date){
    	$payroll_period = false;
    	$this->db->where('deleted',0);
        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
        $result = $this->db->get('timekeeping_period');     
        $payroll_period = $result->result_array();                                            
        return $payroll_period;        
    }   

    function get_current_cut_off($date){
		//$where = '(\'' . $date_from . '\' BETWEEN date_from AND date_to AND \''. $date_to . '\'  BETWEEN date_from AND date_to)';
		//$this->db->where($where, '', false);    	
		$this->db->where('deleted',0);
        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
        $result = $this->db->get('timekeeping_period');
        if($result):
			return $current_date_cutoff;
        else:
			return false;
        endif;	
    }  

    function check_cutoff_policy_ls($employee_id,$form_status,$application_form_id,$date_from,$date_to = false){
    	$err = 0;
        $policy_leave_setup = $this->get_policy_leave_setup($employee_id,$application_form_id,$date_from);      

    	if (empty($policy_leave_setup)):
    		$err = 1;	//payroll cutoff not yeat created
    	else:
    		switch ($form_status) {
    			case 1:

    				if (array_key_exists('filing_advance_limit', $policy_leave_setup)) {
		    			if (date('Y-m-d', strtotime($date_from)) < $policy_leave_setup['filing_advance_limit']) {
		    				$err = 2; 
		    			}
		    		}

		    		if (array_key_exists('filing_before_date', $policy_leave_setup)){
						if (date('Y-m-d', strtotime($date_from)) < $policy_leave_setup['filing_before_date'])
							$err = 2;  //exceed in prev cutoff          			
						if (!$policy_leave_setup['filing_before_date'])
							$err = 1;
		    		}   

		    		if (array_key_exists('filing_after_date', $policy_leave_setup)){
						if (date('Y-m-d', strtotime($date_to)) > $policy_leave_setup['filing_after_date']){
							$err = 2;  //exceed in next cutoff         
						}

						if (!$policy_leave_setup['filing_after_date']){
							$err = 1;
						}
		    		} 

		    		if ($policy_leave_setup['filing_before_not_allowed']){
		    			if (date('Y-m-d',strtotime($date_from)) < date('Y-m-d')){
		    				$err = 2;  //exceed in prev cutoff 
		    			}
		    		} 	    		
		    		if ($policy_leave_setup['filing_after_not_allowed'] && $date_to){
		    			if (date('Y-m-d',strtotime($date_to)) > date('Y-m-d')){
		    				$err = 2;  //exceed in prev cutoff 
		    			}
		    		} 
    			break;
    			case 3:

		    		if (array_key_exists('approval_before_date', $policy_leave_setup)){
						if (date('Y-m-d') < $policy_leave_setup['approval_before_date'])
							$err = 2;  //exceed in prev cutoff    


						if (!$policy_leave_setup['approval_before_date'])
							$err = 1;
		    		}   
		    		
		    		if (array_key_exists('approval_after_date', $policy_leave_setup)){
						if (date('Y-m-d') > $policy_leave_setup['approval_after_date'])
							$err = 2;  //exceed in next cutoff          			
						if (!$policy_leave_setup['approval_after_date'])
							$err = 1;

		    		} 


		    		if ($policy_leave_setup['approval_before_not_allowed']){
		    			if (date('Y-m-d',strtotime($date_from)) > date('Y-m-d')){
		    				$err = 2;  //exceed in prev cutoff 
		    			}
		    		} 
		    		if ($policy_leave_setup['approval_after_not_allowed'] && $date_to){
		    			if (date('Y-m-d',strtotime($date_to)) < date('Y-m-d')){
		    				$err = 2;  //exceed in prev cutoff 
		    			}
		    		} 	    		
    			break;   
    			case 5:
		    		if (array_key_exists('cancellation_before_date', $policy_leave_setup)){
						if (date('Y-m-d') < $policy_leave_setup['cancellation_before_date'])
							$err = 2;  //exceed in prev cutoff          			
						if (!$policy_leave_setup['cancellation_before_date'])
							$err = 1;
		    		}   
		    		if (array_key_exists('cancellation_after_date', $policy_leave_setup)){
						if (date('Y-m-d') > $policy_leave_setup['cancellation_after_date'])
							$err = 2;  //exceed in next cutoff          			
						if (!$policy_leave_setup['cancellation_after_date'])
							$err = 1;
		    		} 

		    		if ($policy_leave_setup['cancellation_before_not_allowed']){
		    			if (date('Y-m-d',strtotime($date_from)) > date('Y-m-d')){
		    				$err = 2;  //exceed in prev cutoff 
		    			}
		    		} 
		    		if ($policy_leave_setup['cancellation_after_not_allowed'] && $date_to){
		    			if (date('Y-m-d',strtotime($date_to)) < date('Y-m-d')){
		    				$err = 2;  //exceed in prev cutoff 
		    			}
		    		} 		    		
    			break;    			 			
    		}        		    
    	endif;

        return $err;	
    }	

    function get_policy_leave_setup($employee_id,$application_form_id,$date){

    	$policy_ls_array = array();
    	$userinfo = $this->get_employee($employee_id);
		$company_id = $userinfo['company_id'];
		$employee_type_id = $userinfo['employee_type'];

		$where = "FIND_IN_SET($company_id, company_id)";  
		$this->db->where($where);	
		$this->db->where('application_form_id',$application_form_id);
		$where1 = "FIND_IN_SET($employee_type_id, employee_type_id)";  
		$this->db->where($where1);			
    	$result_leave_filing_setup = $this->db->get('employee_type_leave_filing_setup');

		$current_day = date('Y-m-d');

    	if ($result_leave_filing_setup && $result_leave_filing_setup->num_rows() > 0){
			$row_leave_filing_setup = $result_leave_filing_setup->row();   	

			$filing_advance_limit = false;

			if ($row_leave_filing_setup->filing_advance_limit > 0) {
				$filing_advance_limit = date('Y-m-d',strtotime('+'.$row_leave_filing_setup->filing_advance_limit.'days'));  	
			}
			

			$policy_ls_array['with_attachment'] = $row_leave_filing_setup->with_attachment;
			$policy_ls_array['for_hr_validation'] = $row_leave_filing_setup->for_hr_validation;
			$policy_ls_array['no_days_required_attachment'] = $row_leave_filing_setup->no_days_required_attachment;
			$policy_ls_array['no_days_hr_validate'] = $row_leave_filing_setup->no_days_hr_validate;
			$policy_ls_array['tenure'] = $row_leave_filing_setup->tenure;
			$policy_ls_array['maternity_max_no_children'] = $row_leave_filing_setup->maternity_max_no_children;
			$policy_ls_array['max_no_days_to_avail'] = $row_leave_filing_setup->max_no_days_to_avail;
			$policy_ls_array['calendar_days'] = $row_leave_filing_setup->calendar_days;
			$policy_ls_array['bday_no_of_days_alowed_before_filing'] = $row_leave_filing_setup->bday_no_of_days_alowed_before_filing;
			$policy_ls_array['bday_no_of_days_alowed_after_filing'] = $row_leave_filing_setup->bday_no_of_days_alowed_after_filing;
			
	    	$this->db->where('deleted',0);
	        $this->db->where('(\'' . date('Y-m-d',strtotime($current_day)) . '\' BETWEEN date_from AND date_to)', '', false);
	        $result_timekeeping_period = $this->db->get('timekeeping_period');  

	        if ($result_timekeeping_period && $result_timekeeping_period->num_rows() > 0){
		        $row_period = $result_timekeeping_period->row();
		        // filing before

	        	if ($row_leave_filing_setup->filing_before_days_or_month == 1){
	        		if ($row_leave_filing_setup->filing_before_no_days_or_cutoff > 0){
		        		$filing_date = date('Y-m-d',strtotime($current_day . '-'.$row_leave_filing_setup->filing_before_no_days_or_cutoff.'days'));  
		        		$policy_ls_array['filing_before_date'] = $filing_date;
				    }      		
	        	}

	        	if ($row_leave_filing_setup->filing_before_days_or_month == 2){

		        	$no_cutoff_to_check = $row_leave_filing_setup->filing_before_no_days_or_cutoff - 1;

		        	if ($row_leave_filing_setup->filing_before_no_days_or_cutoff > 0){
		        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from < '{$row_period->date_from}' ORDER BY date_from DESC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		

		        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
		        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
					        $policy_ls_array['filing_before_date'] = $result_timekeeping_period_prev_row->date_from;
		        		}
		        		else{
		        			$policy_ls_array['filing_before_date'] = 0;
		        		}			        			        		
		        	}
	        	}	

	        	if ($row_leave_filing_setup->filing_before_days_or_month == 3){
	        		$policy_ls_array['filing_before_not_allowed'] = true;
	        	}

	        	if ($row_leave_filing_setup->filing_before_days_or_month == 4){
			    	$this->db->where('deleted',0);
			        $this->db->where('(\'' . date('Y-m-d',strtotime($current_day)) . '\' BETWEEN date_from AND date_to)', '', false);
			        $current_cutoff = $this->db->get('timekeeping_period');  
			        if ($current_cutoff && $current_cutoff->num_rows() > 0){
			        	$current_cutoff_rows = $current_cutoff->row();
			        	$policy_ls_array['filing_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
			        	$policy_ls_array['filing_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
			        }	        		
	        	}	        	
	        	// filing before

	        	// filing after

	        	if ($row_leave_filing_setup->filing_after_days_or_month == 0) {
	        		if ($filing_advance_limit) {
	        			$policy_ls_array['filing_advance_limit'] = $filing_advance_limit;
	        		}
	        	}

	        	if ($row_leave_filing_setup->filing_after_days_or_month == 1){
	        		if ($row_leave_filing_setup->filing_after_no_days_or_cutoff > 0){
	        			$days = $row_leave_filing_setup->filing_after_no_days_or_cutoff + $row_leave_filing_setup->filing_advance_limit;
		        		$filing_date_af = date('Y-m-d',strtotime('+'.$days.'days'));  
		        		
		        		if ($filing_advance_limit) {
		        			$policy_ls_array['filing_advance_limit'] = $filing_advance_limit;
		        		}
		
		        		$policy_ls_array['filing_after_date'] = $filing_date_af;
				    }      		
	        	}
	        
	        	if ($row_leave_filing_setup->filing_after_days_or_month == 2){

		        	$no_cutoff_to_check = $row_leave_filing_setup->filing_after_no_days_or_cutoff - 1;
		        	
		        	if ($filing_advance_limit) {
		        		$policy_ls_array['filing_advance_limit'] = $filing_advance_limit;
		        	}

			
		        	if ($row_leave_filing_setup->filing_after_no_days_or_cutoff > 0){
		        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from > '{$row_period->date_to}' ORDER BY date_to ASC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		

		        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
		        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
					        $policy_ls_array['filing_after_date'] = $result_timekeeping_period_prev_row->date_to;
		        		}
		        		else{
		        			$policy_ls_array['filing_after_date'] = 0;
		        		}	        		
		        	}
	        	}

	        	if ($row_leave_filing_setup->filing_after_days_or_month == 3){
	        		$policy_ls_array['filing_after_not_allowed'] = true;
	        	}	

	        	if ($row_leave_filing_setup->filing_after_days_or_month == 4){

	        		if ($filing_advance_limit) {
		        		$policy_ls_array['filing_advance_limit'] = $filing_advance_limit;
		        	}

			    	$this->db->where('deleted',0);
			        $this->db->where('(\'' . date('Y-m-d',strtotime($current_day)) . '\' BETWEEN date_from AND date_to)', '', false);
			        $current_cutoff = $this->db->get('timekeeping_period');  
			        if ($current_cutoff && $current_cutoff->num_rows() > 0){
			        	$current_cutoff_rows = $current_cutoff->row();
			        	$policy_ls_array['filing_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
			        	$policy_ls_array['filing_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));;
			        }	        		
	        	}	        	        	   	
	        	// filing after	 

		    	$this->db->where('deleted',0);
		        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
		        $result_timekeeping_period = $this->db->get('timekeeping_period'); 

		        if ($result_timekeeping_period && $result_timekeeping_period->num_rows() > 0){
			        $row_period = $result_timekeeping_period->row();
			        
			        // approval before
		        	if ($row_leave_filing_setup->approval_before_days_or_month == 1){
		        		if ($row_leave_filing_setup->approval_before_no_days_or_cutoff > 0){
		        			$app_date = date('Y-m-d', strtotime($date));

		        				$filing_date = date('Y-m-d',strtotime($current_day . '-'.$row_leave_filing_setup->approval_before_no_days_or_cutoff.'days'));  
		        				$policy_ls_array['approval_before_date'] = $filing_date;		

					    }      		
		        	}	

		        	if ($row_leave_filing_setup->approval_before_days_or_month == 2){

			        	$no_cutoff_to_check = $row_leave_filing_setup->approval_before_no_days_or_cutoff - 1;

			        	if ($row_leave_filing_setup->approval_before_no_days_or_cutoff > 0){
			        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from < '{$row_period->date_from}' ORDER BY date_from DESC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		

			        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
			        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
						        $policy_ls_array['approval_before_date'] = $result_timekeeping_period_prev_row->date_from;
			        		}
			        		else{
			        			$policy_ls_array['approval_before_date'] = 0;
			        		}			        			        		
			        	}
		        	}	     

		        	if ($row_leave_filing_setup->approval_before_days_or_month == 3){
		        		$policy_ls_array['approval_before_not_allowed'] = true;
		        	}

		        	if ($row_leave_filing_setup->approval_before_days_or_month == 4){
				    	$this->db->where('deleted',0);
				        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
				        $current_cutoff = $this->db->get('timekeeping_period');  
				        if ($current_cutoff && $current_cutoff->num_rows() > 0){
				        	$current_cutoff_rows = $current_cutoff->row();
				        	$policy_ls_array['approval_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
				        	$policy_ls_array['approval_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
				        }	        		
		        	}	

		        	// approval before

		        	// approval after
		        	if ($row_leave_filing_setup->approval_after_days_or_month == 1){
		        		if ($row_leave_filing_setup->approval_after_no_days_or_cutoff > 0){
			        		$filing_date = date('Y-m-d',strtotime($current_day . '+'.$row_leave_filing_setup->approval_after_no_days_or_cutoff.'days'));  
			        		$policy_ls_array['approval_after_date'] = $filing_date;
					    }      		
		        	}	

		        	if ($row_leave_filing_setup->approval_after_days_or_month == 2){

			        	$no_cutoff_to_check = $row_leave_filing_setup->approval_after_no_days_or_cutoff - 1;

			        	if ($row_leave_filing_setup->approval_after_no_days_or_cutoff > 0){
			        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from > '{$row_period->date_to}' ORDER BY date_to ASC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		

			        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
			        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
						        $policy_ls_array['approval_after_date'] = $result_timekeeping_period_prev_row->date_to;
			        		}
			        		else{
			        			$policy_ls_array['approval_after_date'] = 0;
			        		}	        		
			        	}
		        	}  

		        	if ($row_leave_filing_setup->approval_after_days_or_month == 3){
		        		$policy_ls_array['approval_after_not_allowed'] = true;
		        	}	

		        	if ($row_leave_filing_setup->approval_after_days_or_month == 4){
				    	$this->db->where('deleted',0);
				        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
				        $current_cutoff = $this->db->get('timekeeping_period');  
				        if ($current_cutoff && $current_cutoff->num_rows() > 0){
				        	$current_cutoff_rows = $current_cutoff->row();
				        	$policy_ls_array['approval_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
				        	$policy_ls_array['approval_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
				        }	        		
		        	}		        	        	      	
		        	// approval after	 

		        	// cancellation before
		        	if ($row_leave_filing_setup->cancelation_before_days_or_month == 1){
		        		if ($row_leave_filing_setup->cancelation_before_days_or_month > 0){
			        		$filing_date = date('Y-m-d',strtotime($date . '-'.$row_leave_filing_setup->cancelation_before_no_days_or_cutoff.'days'));  
			        		$policy_ls_array['cancellation_before_date'] = $filing_date;
					    }      		
		        	}

		        	if ($row_leave_filing_setup->cancelation_before_days_or_month == 2){

			        	$no_cutoff_to_check = $row_leave_filing_setup->cancelation_before_no_days_or_cutoff - 1;

			        	if ($row_leave_filing_setup->cancelation_before_no_days_or_cutoff > 0){
			        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from < '{$row_period->date_from}' ORDER BY date_from DESC LIMIT 1 OFFSET {$no_cutoff_to_check}");

			        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
			        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
						        $policy_ls_array['cancellation_before_date'] = $result_timekeeping_period_prev_row->date_to;
			        		}
			        		else{
			        			$policy_ls_array['cancellation_before_date'] = 0;
			        		}		        			        		
			        	}
		        	}	

		        	if ($row_leave_filing_setup->cancelation_before_days_or_month == 3){
		        		$policy_ls_array['cancellation_before_not_allowed'] = true;
		        	}		

		        	if ($row_leave_filing_setup->cancelation_before_days_or_month == 4){
				    	$this->db->where('deleted',0);
				        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
				        $current_cutoff = $this->db->get('timekeeping_period');  
				        if ($current_cutoff && $current_cutoff->num_rows() > 0){
				        	$current_cutoff_rows = $current_cutoff->row();
				        	$policy_ls_array['cancellation_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
				        	$policy_ls_array['cancellation_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
				        }	        		
		        	}	        	        	        	
		        	// cancellation before

		        	// cancellation after
		        	if ($row_leave_filing_setup->cancelation_after_days_or_month == 1){
		        		if ($row_leave_filing_setup->cancelation_after_no_days_or_cutoff > 0){
			        		$filing_date = date('Y-m-d',strtotime($date . '+'.$row_leave_filing_setup->filing_after_no_days_or_cutoff.'days'));  
			        		$policy_ls_array['cancellation_after_date'] = $filing_date;
					    }      		
		        	}

		        	if ($row_leave_filing_setup->cancelation_after_days_or_month == 2){

			        	$no_cutoff_to_check = $row_leave_filing_setup->cancelation_after_no_days_or_cutoff - 1;

			        	if ($row_leave_filing_setup->cancelation_after_no_days_or_cutoff > 0){
			        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from > '{$row_period->date_to}' ORDER BY date_to ASC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		
			        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
			        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
						        $policy_ls_array['cancellation_after_date'] = $result_timekeeping_period_prev_row->date_to;
			        		}
			        		else{
			        			$policy_ls_array['cancellation_after_date'] = 0;
			        		}			        			        		
			        	}
		        	}	   

		        	if ($row_leave_filing_setup->cancelation_after_days_or_month == 3){
		        		$policy_ls_array['cancellation_after_not_allowed'] = true;
		        	}

		        	if ($row_leave_filing_setup->cancelation_after_days_or_month == 4){
				    	$this->db->where('deleted',0);
				        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
				        $current_cutoff = $this->db->get('timekeeping_period');  
				        if ($current_cutoff && $current_cutoff->num_rows() > 0){
				        	$current_cutoff_rows = $current_cutoff->row();
				        	$policy_ls_array['cancellation_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
				        	$policy_ls_array['cancellation_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
				        }	        		
		        	}	        		        	     	
	        		// cancellation after
	        	}
	        	else{
	        		return array("no validation");
	        	}	        			        			        	       
		    }
		    else{
		    	return array();
		    }
		    return $policy_ls_array;			
    	}
    	else{
    		return array("no validation");
    	}
    }	
	

    function check_cutoff_policy_forms($employee_id,$form_status,$application_form_id,$date_from,$date_to = false){
    	$err = 0;
        $policy_form_setup = $this->get_policy_form_setup($employee_id,$application_form_id,$date_from);      

        $shit_sched = $this->system->get_employee_worksched_shift($employee_id, date('Y-m-d'));
        $shift_start = date('Y-m-d') .' '. $shit_sched->shifttime_start;
        $shift_end = date('Y-m-d') .' '. $shit_sched->shifttime_end;
        
    	if (empty($policy_form_setup)):
    		$err = 1;	//payroll cutoff not yeat created
    	else:
    		switch ($form_status) {
    			case 1:
	    			if(strtotime($date_from) < strtotime(date('Y-m-d'))){
			    		if (array_key_exists('filing_before_date', $policy_form_setup) && $policy_form_setup['filing_before_date'] != 0){
							if (strtotime($date_from) < strtotime($policy_form_setup['filing_before_date'])) 
								$err = 2;  //exceed in prev cutoff    

							if (!$policy_form_setup['filing_before_date'])
								$err = 1;
			    		}
			    	}else if(strtotime($date_from) > strtotime(date('Y-m-d'))){
			    		if (array_key_exists('filing_after_date', $policy_form_setup) && $policy_form_setup['filing_after_date'] != 0){
							if (strtotime($date_from) > strtotime($policy_form_setup['filing_after_date']))
								$err = 2;  //exceed in next cutoff          			
							if (!$policy_form_setup['filing_after_date'])
								$err = 1;
			    		} 
			    	}else{
			    		if (array_key_exists('filling_not_allow_current_day', $policy_form_setup) && $policy_form_setup['filling_not_allow_current_day'] != 0){    			
							if ($policy_form_setup['filling_not_allow_current_day']){
								$err = 2;
							}
			    		}
			    	}

			    	if(array_key_exists('filing_before_not_allowed', $policy_form_setup) && $policy_form_setup['filing_before_not_allowed'] != 0){
				    	if ($policy_form_setup['filing_before_not_allowed']){
			    			if (date('Y-m-d',strtotime($date_from)) < date('Y-m-d')){
			    				$err = 2;  //exceed in prev cutoff 
			    			}
			    		} 	 
		    		}   	
		    		if(array_key_exists('filing_after_not_allowed', $policy_form_setup) && $policy_form_setup['filing_after_not_allowed'] != 0){	
			    		if ($policy_form_setup['filing_after_not_allowed'] && $date_to){
			    			if (date('Y-m-d',strtotime($date_to)) > date('Y-m-d')){
			    				$err = 2;  //exceed in prev cutoff 
			    			}
			    		} 
		    		}
    			break;
    			case 2:
    			case 3:
		    		if (array_key_exists('approval_before_date', $policy_form_setup) && $policy_form_setup['approval_before_date'] != 0){
						if (date('Y-m-d') < $policy_form_setup['approval_before_date'])
							$err = 2;  //exceed in prev cutoff          			
						if (!$policy_form_setup['approval_before_date'])
							$err = 1;
		    		}   
		    		
		    		if (array_key_exists('approval_after_date', $policy_form_setup) && $policy_form_setup['approval_after_date'] != 0){
						if (date('Y-m-d') > $policy_form_setup['approval_after_date'])
							$err = 2;  //exceed in next cutoff          			
						if (!$policy_form_setup['approval_after_date'])
							$err = 1;
		    		} 
		    		if(array_key_exists('approval_before_not_allowed', $policy_form_setup) && $policy_form_setup['approval_before_not_allowed'] != 0){
			    		if ($policy_form_setup['approval_before_not_allowed']){
			    			if (date('Y-m-d',strtotime($date_from)) > date('Y-m-d')){
			    				$err = 2;  //exceed in prev cutoff 
			    			}
			    		} 
			    	}
			    	if(array_key_exists('approval_after_not_allowed', $policy_form_setup) && $policy_form_setup['approval_after_not_allowed'] != 0){
			    		if ($policy_form_setup['approval_after_not_allowed'] && $date_to){
			    			if (date('Y-m-d',strtotime($date_to)) < date('Y-m-d')){
			    				$err = 2;  //exceed in prev cutoff 
			    			}
			    		} 	 
			    	}

    			break;   
    			case 5: 
		    		if (array_key_exists('cancellation_before_date', $policy_form_setup) && $policy_form_setup['cancellation_before_date'] != 0){
						if (date('Y-m-d') < $policy_form_setup['cancellation_before_date'])
							$err = 2;  //exceed in prev cutoff          			
						if (!$policy_form_setup['cancellation_before_date'])
							$err = 1;	 
		    		}   
		    		
		    		if (array_key_exists('cancellation_after_date', $policy_form_setup) && $policy_form_setup['cancellation_after_date'] != 0){
						if (date('Y-m-d') > $policy_form_setup['cancellation_after_date'])
							$err = 2;  //exceed in next cutoff          			
						if (!$policy_form_setup['cancellation_after_date'])
							$err = 1;
		    		} 
		    		if(array_key_exists('cancellation_before_not_allowed', $policy_form_setup) && $policy_form_setup['cancellation_before_not_allowed'] != 0){
			    		if ($policy_form_setup['cancellation_before_not_allowed']){
			    			if (date('Y-m-d',strtotime($date_from)) > date('Y-m-d')){
			    				$err = 2;  //exceed in prev cutoff 
			    			}
			    		} 
			    	}
			    	if(array_key_exists('cancellation_after_not_allowed', $policy_form_setup) && $policy_form_setup['cancellation_after_not_allowed'] != 0){
			    		if ($policy_form_setup['cancellation_after_not_allowed'] && $date_to){
			    			if (date('Y-m-d',strtotime($date_to)) < date('Y-m-d')){
			    				$err = 2;  //exceed in prev cutoff 
			    			}
			    		} 	
		    		}
    			break;    			 			
    		}

    		if(array_key_exists('filling_only_within_shift', $policy_form_setup) && $policy_form_setup['filling_only_within_shift']){
				if (strtotime($shift_start) > strtotime($shift_end)) {
					$shift_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $shift_end)));
				}    			
    			if (date('Y-m-d H:i:s') < $shift_start || date('Y-m-d H:i:s') > $shift_end){
    				$err = 2;  //filling not within the shift schedule 
    			}
    		}    		        		    
    	endif;

        return $err;	
    }

    function get_policy_form_setup($employee_id,$application_form_id,$date){

    	$policy_ls_array = array();
    	$userinfo = $this->get_employee($employee_id);
		$company_id = $userinfo['company_id'];
		$employee_type_id = $userinfo['employee_type'];

		$where = "FIND_IN_SET($company_id, company_id)";  
		$this->db->where($where);	
    	$this->db->where('application_form_id',$application_form_id);
    	$where1 = "FIND_IN_SET($employee_type_id, employee_type_id)";  
		$this->db->where($where1);	
    	$result_form_filing_setup = $this->db->get('employee_type_form_filing_setup');

    	if ($result_form_filing_setup && $result_form_filing_setup->num_rows() > 0){
			$row_form_filing_setup = $result_form_filing_setup->row();   	

			$policy_ls_array['with_attachment'] = $row_form_filing_setup->with_attachment;
			$policy_ls_array['for_hr_validation'] = $row_form_filing_setup->for_hr_validation;
			$policy_ls_array['no_days_required_attachment'] = $row_form_filing_setup->no_days_required_attachment;
			$policy_ls_array['tenure'] = $row_form_filing_setup->tenure;
			$policy_ls_array['max_no_days_to_avail'] = $row_form_filing_setupng_setup->max_no_days_to_avail;
			$policy_ls_array['calendar_days'] = $row_form_filing_setup->calendar_days;
			$policy_ls_array['max_no_of_filling'] = $row_form_filing_setup->max_no_of_filling;
			$policy_ls_array['filling_not_allow_current_day'] = $row_form_filing_setup->filling_not_allow_current_day;
			$policy_ls_array['filing_before_days_or_month'] = $row_form_filing_setup->filing_before_days_or_month;
			$policy_ls_array['filing_after_days_or_month'] = $row_form_filing_setup->filing_after_days_or_month;
			$policy_ls_array['filing_before_no_days_or_cutoff'] = $row_form_filing_setup->filing_before_no_days_or_cutoff;
			$policy_ls_array['filing_after_no_days_or_cutoff'] = $row_form_filing_setup->filing_after_no_days_or_cutoff;

			$policy_ls_array['approval_before_days_or_month'] = $row_form_filing_setup->approval_before_days_or_month;
			$policy_ls_array['approval_after_days_or_month'] = $row_form_filing_setup->approval_after_days_or_month;
			$policy_ls_array['approval_before_no_days_or_cutoff'] = $row_form_filing_setup->approval_before_no_days_or_cutoff;
			$policy_ls_array['approval_after_no_days_or_cutoff'] = $row_form_filing_setup->approval_after_no_days_or_cutoff;

			$policy_ls_array['cancelation_before_days_or_month'] = $row_form_filing_setup->cancelation_before_days_or_month;
			$policy_ls_array['cancelation_after_days_or_month'] = $row_form_filing_setup->cancelation_after_days_or_month;
			$policy_ls_array['cancelation_before_no_days_or_cutoff'] = $row_form_filing_setup->cancelation_before_no_days_or_cutoff;
			$policy_ls_array['cancelation_after_no_days_or_cutoff'] = $row_form_filing_setup->cancelation_after_no_days_or_cutoff;

			$policy_ls_array['company_list'] = $row_form_filing_setup->company_id;
			$policy_ls_array['employee_type_list'] = $row_form_filing_setup->employee_type_id;
			$policy_ls_array['working_days_only'] = $row_form_filing_setup->working_days_only;
			$policy_ls_array['max_no_hrs_to_avail'] = $row_form_filing_setup->max_no_hrs_to_avail;
			$policy_ls_array['min_no_hrs_to_avail'] = $row_form_filing_setup->min_no_hrs_to_avail;
			$policy_ls_array['min_no_hrs_to_avail_weekdays'] = $row_form_filing_setup->min_no_hrs_to_avail_weekdays;
			$policy_ls_array['min_no_hrs_to_avail_weekend'] = $row_form_filing_setup->min_no_hrs_to_avail_weekend;
			$policy_ls_array['filling_only_within_shift'] = $row_form_filing_setup->filling_only_within_shift;
			
	    	$this->db->where('deleted',0);
	        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
	        $result_timekeeping_period = $this->db->get('timekeeping_period');  

	        if ($result_timekeeping_period && $result_timekeeping_period->num_rows() > 0){
		        $row_period = $result_timekeeping_period->row();
		        $current_day = date('Y-m-d');
		        // filing before

	        	if ($row_form_filing_setup->filing_before_days_or_month == 1){
	        		if ($row_form_filing_setup->filing_before_no_days_or_cutoff > 0){
		        		$filing_date = date('Y-m-d',strtotime($current_day . '-'.$row_form_filing_setup->filing_before_no_days_or_cutoff.'days'));  
		        		$policy_ls_array['filing_before_date'] = $filing_date;
				    }      		
	        	}

	        	if ($row_form_filing_setup->filing_before_days_or_month == 2){

		        	$no_cutoff_to_check = $row_form_filing_setup->filing_before_no_days_or_cutoff - 1;

		        	if ($row_form_filing_setup->filing_before_no_days_or_cutoff > 0){
		        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from < '{$current_day}' ORDER BY date_from DESC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		

		        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
		        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
					        $policy_ls_array['filing_before_date'] = $result_timekeeping_period_prev_row->date_from;
		        		}
		        		else{
		        			$policy_ls_array['filing_before_date'] = 0;
		        		}			        			        		
		        	}
	        	}	

	        	if ($row_form_filing_setup->filing_before_days_or_month == 3){
	        		$policy_ls_array['filing_before_not_allowed'] = true;
	        	}

	        	if ($row_form_filing_setup->filing_before_days_or_month == 4){
			    	$this->db->where('deleted',0);
			        $this->db->where('(\'' . date('Y-m-d',strtotime($current_day)) . '\' BETWEEN date_from AND date_to)', '', false);
			        $current_cutoff = $this->db->get('timekeeping_period');  
			        if ($current_cutoff && $current_cutoff->num_rows() > 0){
			        	$current_cutoff_rows = $current_cutoff->row();
			        	$policy_ls_array['filing_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
			        	// $policy_ls_array['filing_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
			        }	        		
	        	}	        	
	        	// filing before

	        	// filing after
	        	if ($row_form_filing_setup->filing_after_days_or_month == 1){
	        		if ($row_form_filing_setup->filing_after_no_days_or_cutoff > 0){
		        		$filing_date = date('Y-m-d',strtotime($current_day . '+'.$row_form_filing_setup->filing_after_no_days_or_cutoff.'days'));  
		        		$policy_ls_array['filing_after_date'] = $filing_date;
				    }      		
	        	}

	        	if ($row_form_filing_setup->filing_after_days_or_month == 2){

		        	$no_cutoff_to_check = $row_form_filing_setup->filing_after_no_days_or_cutoff - 1;

		        	if ($row_form_filing_setup->filing_after_no_days_or_cutoff > 0){
		        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from > '{$current_day}' ORDER BY date_to ASC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		

		        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
		        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
					        $policy_ls_array['filing_after_date'] = $result_timekeeping_period_prev_row->date_to;
		        		}
		        		else{
		        			$policy_ls_array['filing_after_date'] = 0;
		        		}	        		
		        	}
	        	}

	        	if ($row_form_filing_setup->filing_after_days_or_month == 3){
	        		$policy_ls_array['filing_after_not_allowed'] = true;
	        	}	

	        	if ($row_form_filing_setup->filing_after_days_or_month == 4){
			    	$this->db->where('deleted',0);
			        $this->db->where('(\'' . date('Y-m-d',strtotime($current_day)) . '\' BETWEEN date_from AND date_to)', '', false);
			        $current_cutoff = $this->db->get('timekeeping_period');  
			        if ($current_cutoff && $current_cutoff->num_rows() > 0){
			        	$current_cutoff_rows = $current_cutoff->row();
			        	// $policy_ls_array['filing_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
			        	$policy_ls_array['filing_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));;
			        }	        		
	        	}	        	        	   	
	        	// filing after	 

		        // approval before
	        	if ($row_form_filing_setup->approval_before_days_or_month == 1){
	        		if ($row_form_filing_setup->approval_before_no_days_or_cutoff > 0){
		        		$filing_date = date('Y-m-d',strtotime($date . '-'.$row_form_filing_setup->approval_before_no_days_or_cutoff.'days'));  
		        		$policy_ls_array['approval_before_date'] = $filing_date;
				    }      		
	        	}	

	        	if ($row_form_filing_setup->approval_before_days_or_month == 2){

		        	$no_cutoff_to_check = $row_form_filing_setup->approval_before_no_days_or_cutoff - 1;

		        	if ($row_form_filing_setup->approval_before_no_days_or_cutoff > 0){
		        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from < '{$row_period->date_from}' ORDER BY date_from DESC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		

		        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
		        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
					        $policy_ls_array['approval_before_date'] = $result_timekeeping_period_prev_row->date_from;
		        		}
		        		else{
		        			$policy_ls_array['approval_before_date'] = 0;
		        		}			        			        		
		        	}
	        	}	     

	        	if ($row_form_filing_setup->approval_before_days_or_month == 3){
	        		$policy_ls_array['approval_before_not_allowed'] = true;
	        	}

	        	if ($row_form_filing_setup->approval_before_days_or_month == 4){
			    	$this->db->where('deleted',0);
			        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
			        $current_cutoff = $this->db->get('timekeeping_period');  
			        if ($current_cutoff && $current_cutoff->num_rows() > 0){
			        	$current_cutoff_rows = $current_cutoff->row();
			        	$policy_ls_array['approval_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
			        	$policy_ls_array['approval_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
			        }	        		
	        	}	

	        	// approval before

	        	// approval after
	        	if ($row_form_filing_setup->approval_after_days_or_month == 1){
	        		if ($row_form_filing_setup->approval_after_no_days_or_cutoff > 0){
		        		$filing_date = date('Y-m-d',strtotime($date . '+'.$row_form_filing_setup->approval_after_no_days_or_cutoff.'days'));  
		        		$policy_ls_array['approval_after_date'] = $filing_date;
				    }      		
	        	}	

	        	if ($row_form_filing_setup->approval_after_days_or_month == 2){

		        	$no_cutoff_to_check = $row_form_filing_setup->approval_after_no_days_or_cutoff - 1;

		        	if ($row_form_filing_setup->approval_after_no_days_or_cutoff > 0){
		        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from > '{$row_period->date_to}' ORDER BY date_to ASC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		

		        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
		        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
					        $policy_ls_array['approval_after_date'] = $result_timekeeping_period_prev_row->date_to;
		        		}
		        		else{
		        			$policy_ls_array['approval_after_date'] = 0;
		        		}	        		
		        	}
	        	}  

	        	if ($row_form_filing_setup->approval_after_days_or_month == 3){
	        		$policy_ls_array['approval_after_not_allowed'] = true;
	        	}	

	        	if ($row_form_filing_setup->approval_after_days_or_month == 4){
			    	$this->db->where('deleted',0);
			        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
			        $current_cutoff = $this->db->get('timekeeping_period');  
			        if ($current_cutoff && $current_cutoff->num_rows() > 0){
			        	$current_cutoff_rows = $current_cutoff->row();
			        	$policy_ls_array['approval_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
			        	$policy_ls_array['approval_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
			        }	        		
	        	}		        	        	      	
	        	// approval after	 

	        	// cancellation before
	        	if ($row_form_filing_setup->cancelation_before_days_or_month == 1){
	        		if ($row_form_filing_setup->cancelation_before_days_or_month > 0){
		        		$filing_date = date('Y-m-d',strtotime($date . '-'.$row_form_filing_setup->cancelation_before_no_days_or_cutoff.'days'));  
		        		$policy_ls_array['cancellation_before_date'] = $filing_date;
				    }      		
	        	}

	        	if ($row_form_filing_setup->cancelation_before_days_or_month == 2){

		        	$no_cutoff_to_check = $row_form_filing_setup->cancelation_before_no_days_or_cutoff - 1;

		        	if ($row_form_filing_setup->cancelation_before_no_days_or_cutoff > 0){
		        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from < '{$row_period->date_from}' ORDER BY date_from DESC LIMIT 1 OFFSET {$no_cutoff_to_check}");

		        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
		        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
					        $policy_ls_array['cancellation_before_date'] = $result_timekeeping_period_prev_row->date_to;
		        		}
		        		else{
		        			$policy_ls_array['cancellation_before_date'] = 0;
		        		}		        			        		
		        	}
	        	}	

	        	if ($row_form_filing_setup->cancelation_before_days_or_month == 3){
	        		$policy_ls_array['cancellation_before_not_allowed'] = true;
	        	}		

	        	if ($row_form_filing_setup->cancelation_before_days_or_month == 4){
			    	$this->db->where('deleted',0);
			        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
			        $current_cutoff = $this->db->get('timekeeping_period');  
			        if ($current_cutoff && $current_cutoff->num_rows() > 0){
			        	$current_cutoff_rows = $current_cutoff->row();
			        	$policy_ls_array['cancellation_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
			        	$policy_ls_array['cancellation_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
			        }	        		
	        	}	        	        	        	
	        	// cancellation before

	        	// cancellation after
	        	if ($row_form_filing_setup->cancelation_after_days_or_month == 1){
	        		if ($row_form_filing_setup->cancelation_after_no_days_or_cutoff > 0){
		        		$filing_date = date('Y-m-d',strtotime($date . '+'.$row_form_filing_setup->filing_after_no_days_or_cutoff.'days'));  
		        		$policy_ls_array['cancellation_after_date'] = $filing_date;
				    }      		
	        	}

	        	if ($row_form_filing_setup->cancelation_after_days_or_month == 2){

		        	$no_cutoff_to_check = $row_form_filing_setup->cancelation_after_no_days_or_cutoff - 1;

		        	if ($row_form_filing_setup->cancelation_after_no_days_or_cutoff > 0){
		        		$result_timekeeping_period_prev_result = $this->db->query("SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE date_from > '{$row_period->date_to}' ORDER BY date_to ASC LIMIT 1 OFFSET {$no_cutoff_to_check}");		        		
		        		if ($result_timekeeping_period_prev_result && $result_timekeeping_period_prev_result->num_rows() > 0){
		        			$result_timekeeping_period_prev_row = $result_timekeeping_period_prev_result->row();
					        $policy_ls_array['cancellation_after_date'] = $result_timekeeping_period_prev_row->date_to;
		        		}
		        		else{
		        			$policy_ls_array['cancellation_after_date'] = 0;
		        		}			        			        		
		        	}
	        	}	   

	        	if ($row_form_filing_setup->cancelation_after_days_or_month == 3){
	        		$policy_ls_array['cancellation_after_not_allowed'] = true;
	        	}

	        	if ($row_form_filing_setup->cancelation_after_days_or_month == 4){
			    	$this->db->where('deleted',0);
			        $this->db->where('(\'' . date('Y-m-d',strtotime($date)) . '\' BETWEEN date_from AND date_to)', '', false);
			        $current_cutoff = $this->db->get('timekeeping_period');  
			        if ($current_cutoff && $current_cutoff->num_rows() > 0){
			        	$current_cutoff_rows = $current_cutoff->row();
			        	$policy_ls_array['cancellation_before_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_from));
			        	$policy_ls_array['cancellation_after_date'] = date('Y-m-d',strtotime($current_cutoff_rows->date_to));
			        }	        		
	        	}	        		        	     	
	        	// cancellation after	        			        	       
		    }
		    else{
		    	return array();
		    }
		    return $policy_ls_array;			
    	}
    	else{
    		return array();
    	}

    }	

    function check_in_cutoff($date){
    	$err = 0;
        $payroll_period = $this->get_in_cut_off($date);           
    	if (empty($payroll_period)):
    		$err = 1;	//payroll cutoff not yeat created
    	else:      		   		
	        $payroll_cutoff_date = date('Y-m-d',strtotime($payroll_period[0]['period_cutoff']));   	    
	        if (date('Y-m-d') > $payroll_cutoff_date):
				$err = 2;  //exceed in nex cutoff           
	        endif;	    		    
    	endif;
        return $err;	
    }	

    function check_in_current_cutoff($date){
    	if (NEXT_CURRENT_CUTT_OFF_VALIDATION):
	        $current_date_cutoff = $this->get_current_cut_off($date);
	        $cdc_from = date('Y-m-d',strtotime($current_date_cutoff->date_from));
	        $cdc_to = date('Y-m-d',strtotime($current_date_cutoff->date_to));
	        if (date('Y-m-d') <= $ndc_to):
				return true;           
	        else:
	        	return false; 
	        endif;
        else:
        	return false;
        endif;    	
    } 

    function check_date_range_if_holiday($date_from,$date_to,$employee_id, $dont_get_exclude = false){
    	$holiday = false;
    	$tstampdateto = strtotime($date_to);	
    	$tstamp_cdate = strtotime($date_from);
		while ($tstamp_cdate <=  $tstampdateto) {
			$result = $this->system->holiday_check(date('Y-m-d',$tstamp_cdate),$employee_id, $dont_get_exclude);
			if (!empty($result)){
				$holiday = true;
			}
			$cdate = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
			$tstamp_cdate = strtotime($cdate);
		}
		return $holiday;
    }     

    // for ticket 223. check if date range is all holiday return false. if range have holiday but have regular days return true
    function check_if_range_all_holiday($date_from,$date_to,$employee_id, $dont_get_exclude = false){
    	$is_pure_holiday = true;
    	$tstampdateto = strtotime($date_to);	
    	$tstamp_cdate = strtotime($date_from);
		while ($tstamp_cdate <=  $tstampdateto) {
			$result = $this->system->holiday_check(date('Y-m-d', $tstamp_cdate), $employee_id, $dont_get_exclude);
			if (!$result){
				$is_pure_holiday = false;
			}
			$cdate = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
			$tstamp_cdate = strtotime($cdate);
		}
		return $is_pure_holiday;

		// return $holiday;
    }   

    function check_same_time($time_from,$time_to){
    	$err = false;    	
    	$time_from = strtotime($time_from);
    	$time_to = strtotime($time_to);
    	if ($time_from == $time_to)
    		$err = true;
    	return $err;
    }

    function check_same_erlier_date_time($date_time_from,$date_time_to){
    	$err = 0;    	
    	$date_time_from = strtotime($date_time_from);
    	$date_time_to = strtotime($date_time_to);
    	if ($date_time_from == $date_time_to)
    		$err = 1;  //equal date and time
    	if ($date_time_from > $date_time_to)
    		$err = 2; //date to is erlier against date from
    	if (date('H:i:s',$date_time_from) > date('H:i:s',$date_time_to))
    		$err = 3; //date to is erlier against date from
    	return $err;
    }

    function check_same_erlier_date($date_from,$date_to){
    	$err = false;    	
    	$date_from = strtotime($date_from);
    	$date_to = strtotime($date_to);
    	if ($date_from > $date_to)
    		$err = true;
    	return $err;
    }

    function check_if_resigned($emp_id = null) {
    	if($emp_id != null)
    	{
    		$this->db->select('employee_id, resigned_date, resigned');
    		$where = "resigned = 1 AND 
    				  resigned_date IS NOT NULL AND
    				  employee_id = ".$emp_id;
    		$this->db->where($where);
    		$resigned = $this->db->get('employee');
    		if($resigned && $resigned->num_rows() > 0)
    		{
    			$resigned = $resigned->row();
	    			return $resigned;
    		} else
    			return false;
    	} else 
    		return false;
    }

    function check_weekend($date){
    	$weekDay = date('w', strtotime($date));
    	return ($weekDay == 0 || $weekDay == 1);
    }

    function call_processing($period_id, $type, $values){
 
			$this->load->helper('file');
			$this->load->helper('time_upload_helper');

			$period_id = $period_id;
			$type = $type;	

			// $period_id = $this->input->post('period_id');
			// $type = $this->input->post('type');
			// $status = $this->input->post('status');
			// $values = implode(',', $this->input->post('values'));
			//18 | employee_id | 2,3
			//echo $period_id." | ".$type." | ".$values."<br />";
			$this->db->where($this->db->dbprefix.'user.deleted', 0);
			if (ENVIRONMENT == 'development') {
				//$this->db->where('employee_id', '159');
			}

			$this->db->where_in($this->db->dbprefix."user.".$type, $values);

			$records = $this->db->get('user');

			$total = $records->num_rows();

			if ($total > 0) {
				$employees = $records->result();
				$this->db->where('period_id', $period_id);
				$this->db->where('deleted', 0);

				$period = $this->db->get('timekeeping_period');
				
				switch($type){
					case 'employee_id':
						$otfolder = 'employee';
						break;
					case 'company_id':
						$otfolder = 'company';
						break;
					case 'division_id':
						$otfolder = 'division';
						break;
					case 'department_id':
						$otfolder = 'department';
						break;
				}

				$otfolder = 'uploads/otfile/' . $otfolder;
				if(!file_exists( $otfolder ) ) mkdir( $otfolder , 0777, true);
				$otfile = $otfolder. '/' . date('Y-m-d') . '.txt';
				
				if(file_exists($otfile)){ unlink($otfile); }
				$otfile_row[] = array('Employee No', 'Date', 'OT Code', 'OT Hours', 'Leave Type', 'Leave Hours' );

				$progressfile = 'uploads/progresslog.txt';
				$ctr = 1;
				$employee_ids = array();

				$day_types = $this->db->get('day_type_and_rates');
				$otcode = array();
				foreach( $day_types->result_array() as $day_type ){
					$prfx = $day_type['day_prefix'];
					foreach($day_type as $col => $value){
						if( !in_array( $col, array('day_type_id', 'day_type', 'day_prefix')) && !strpos($col, '_code') ){
							$otcode[$prfx."_".$col] = $day_type[$col.'_code'];
						}
					}
				}

				$this->otcode = $otcode;

				foreach ($employees as $employee) {
					$employee_ids[] = $employee->employee_id;
					write_file($progressfile, number_format(($ctr++ / $total) * 100, 2));
					$s = $this->_formProcess($employee->employee_id, $period, $otfile_row, $otcode);

					if ($s) {
						$summary[] = $s['summary_update'];
						$otfile_row = $s['otfile_row'];
					}
				}

				unlink($progressfile);
				$ot_string ="";
				foreach( $otfile_row as $otrow ){
					$ot_string .= implode("\t", $otrow) . "\r\n";
				}
				write_file($otfile, $ot_string, 'w+');
				$response->otfile = $otfile;
			}			

			$this->db->where('period_id', $period_id);
			$this->db->where_in('employee_id', $employee_ids);
			$this->db->delete('timekeeping_period_summary');

			$this->db->insert_batch('timekeeping_period_summary', $summary);			

			if ($this->db->_error_message() == '') {
				$response->msg_type = 'success';
				$response->msg =  'Successfully processed ' . $total . ' records.';

				$this->db->where('period_id', $this->input->post('period_id'));
				$this->db->update('timekeeping_period', array('processed' => 1));
			} else {
				$response->msg_type = 'error';
				$response->msg = 'Period processing failed. ' . $this->db->last_query();
			}			
    }

	private function _formProcess($employee_id=0, $result, $otfile_row, $otcode)
	{
		$p = false;
		if ($result && $result->num_rows() > 0) {
			$p = $result->row();
		}

		//check if supervisor
		$supervisor = false;
		$regular = false;

		//for overtime of non regular employee
		$non_regular_eot = false;

		if( $employee_id && $employee_id > 0 ){
			$employee_record = $this->db->get_where('employee',array('employee_id'=>$employee_id))->row();

			if( in_array($employee_record->employee_type, $this->config->item('emp_type_no_late_ut')) ){
				$supervisor = true;
			}

			if( ( $employee_record->status_id == 1 ) || ( $employee_record->status_id == 2 ) ){
				$regular = true;
			}
		}

		if($p)
		{       
			$period_id = $p->period_id;

			$summary_update = array(
				'employee_id' => $employee_id, 
				'period_id' => $period_id,
				'hours_worked' => 0, 
				'lates' => 0, 
				'undertime' => 0, 				
				'overtime' => 0,
				'leaves' => 0,
				'reg_ot' => 0,
				'reg_ot_excess' => 0,
				'reg_nd' => 0,
				'reg_ndot' => 0,
				'reg_ndot_excess' => 0,
				'rd' => 0,
				'rd_ot' => 0,
				'rd_ot_excess' => 0,
				'rd_nd' => 0,
				'rd_ndot' => 0,
				'rd_ndot_excess' => 0,
				'leg' => 0,
				'leg_ot' => 0,
				'leg_ot_excess' => 0,
				'leg_nd' => 0,
				'leg_ndot' => 0,
				'leg_ndot_excess' => 0,
				'spe' => 0,
				'spe_ot' => 0,
				'spe_ot_excess' => 0,
				'spe_nd' => 0,
				'spe_ndot' => 0,
				'spe_ndot_excess' => 0,
				'sperd' => 0,
				'sperd_ot' => 0,
				'sperd_ot_excess' => 0,
				'sperd_nd' => 0,
				'sperd_ndot' => 0,
				'sperd_ndot_excess' => 0,
				'legrd' => 0,
				'legrd_ot' => 0,
				'legrd_ot_excess' => 0,
				'legrd_nd' => 0,
				'legrd_ndot' => 0,
				'legrd_ndot_excess' => 0,
				'dob' => 0,
				'dob_ot' => 0,
				'dob_ot_excess' => 0,
				'dob_nd' => 0,
				'dob_ndot' => 0,
				'dob_ndot_excess' => 0,
				'dobrd_ot' => 0,
				'dobrd_ot_excess' => 0,
				'dobrd_nd' => 0,
				'dobrd_ndot' => 0,				
				'dobrd_ndot_excess' => 0,
			);
	
			$infraction = array();

			//get employee_no
			$employee = $this->db->get_where('user', array('employee_id' => $employee_id))->row();
			$employee_no = $employee->login;

			// Get employee's default schedule
			$this->db->where('employee_id', $employee_id);
			$this->db->where('employee_dtr_setup.deleted', 0);			
			$this->db->join('timekeeping_shift_calendar', 'timekeeping_shift_calendar.shift_calendar_id = employee_dtr_setup.shift_calendar_id', 'left');

			$schedule = $this->db->get('employee_dtr_setup');
			
			if ($schedule->num_rows() == 0) {
				return false;
			}

			$schedule = $schedule->row();
			// $cdate = current date in loop, need this to keep track
			$cdate = $p->date_from;

			$consecutive_absent = 0;
			$tstampdateto = strtotime($p->date_to);			
			$tstamp_cdate = strtotime($cdate);

			// Start "the loop"
			while ($tstamp_cdate <=  $tstampdateto && $tstamp_cdate <= strtotime(date('Y-m-d'))) {
				$otfile_date = date('m/d/Y', $tstamp_cdate);
				$dtr_p = $this->_processDtr($employee_id, $cdate, $regular, $schedule, $summary_update, false, $p, $otfile_row, $employee_no, $supervisor, $regular);

				if (isset($dtr_p['absent_dtr'])) {					
					$absent_dtr[] = $dtr_p['absent_dtr'];
					$otfile_row[] = array($employee_no, $otfile_date, '', '', 'AWL', '8' );
				} else {
					$absent_dtr = array();					
				}
				
				if (count($absent_dtr) >= $this->config->item('consec_days_for_awol')) { 
					// AWOL					
					$this->db->where_in('id', $absent_dtr);
					$this->db->update('employee_dtr', array('awol' => true));

					// Set notification date to be sent
					$this->db->where('id', $dtr_p['absent_dtr']);
					$this->db->update('employee_dtr', array('send_awol_notification' => true));					
				}
	
				if (!is_null($dtr_p['summary_update']) && isset($dtr_p['summary_update'])) {
					$summary_update = $dtr_p['summary_update'];
					$otfile_row = $dtr_p['otfile_row'];
				}

				$cdate = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
				$tstamp_cdate = strtotime($cdate);
			}

			// Get late filing.
			// Get only the dates where there is any instance of a late filing so we can loop through these dates instead.
			$sql = "SELECT datetime_from AS date FROM {$this->db->dbprefix}employee_oot 
					WHERE 
						date_approved BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00'
						AND (datetime_from NOT BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00')
						AND (datetime_to NOT BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00')
					UNION
					SELECT DATE FROM {$this->db->dbprefix}employee_out 
					WHERE 
						date_approved BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00'
						AND (DATE NOT BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00')	
					UNION 
					SELECT datelate FROM {$this->db->dbprefix}employee_et 
					WHERE 
						date_approved BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00'
						AND (datelate NOT BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00')			
					UNION 
					SELECT date_from FROM {$this->db->dbprefix}employee_obt
					WHERE 
						date_approved BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00'
						AND (CONCAT(date_from, ' ', time_start) NOT BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00')
						AND (CONCAT(date_to, ' ', time_end) NOT BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00')		
					UNION
					SELECT date_from FROM {$this->db->dbprefix}employee_leaves
					WHERE 
						date_approved BETWEEN '". $p->date_from . " 00:00:00' AND '" . $p->date_to . " 00:00:00'
						AND (date_from NOT BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "')
						AND (date_to NOT BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "')";

			$lf_dates_qry = $this->db->query($sql);

			if ($lf_dates_qry->num_rows() > 0) {
				$lf_dates = $lf_dates_qry->result();

				foreach ($lf_dates as $lf_date) {
					$dtr_p = $this->_processDtr($employee_id, $lf_date->date, $regular, $schedule, $summary_update, true, $p, $otfile_row, $employee_no, $supervisor, $regular);
		
					if (!is_null($dtr_p['summary_update']) && isset($dtr_p['summary_update'])) {
						$summary_update = $dtr_p['summary_update'];
						$otfile_row = $dtr_p['otfile_row'];
					}
				}
			}
			// End late filing

		}

		if ($p->apply_lates) {
			if (count($infraction) > 0) {
				$this->db->where_not_in('date', $infraction);
			}
			$this->db->where('employee_id', $employee_id);
			$this->db->where('(date BETWEEN \'' . $p->apply_late_from . '\' AND \'' . $p->apply_late_to . '\')', '', false);
			$this->db->where('deleted', 0);				

			$result = $this->db->get('employee_dtr');
			$dummy_p->date_from = $p->apply_late_from;
			$dummy_p->date_to = $p->apply_late_to;

			if ($result->num_rows() > 0) {
				foreach ($result->result() as $dtr) {
					if (isset($shift_cache[$dtr->date])) {
						$shift_start = $shift_cache[$dtr->date]['start'];
						$shift_end = $shift_cache[$dtr->date]['end'];
					} else {
						// get specific day of the week so we can query against the shift_calendar table for the proper shift ie monday_shift_id
						$day = strtolower(date('l', strtotime($dtr->date)));
						// Get default shift for this day.
						$this->db->where('shift_calendar_id', $schedule->shift_calendar_id);
						$this->db->join('timekeeping_shift', 
							'timekeeping_shift.shift_id = timekeeping_shift_calendar.' . $day . '_shift_id');
						$this->db->where('timekeeping_shift_calendar.deleted', 0);

						$result = $this->db->get('timekeeping_shift_calendar');

						// Check for group workschedule.						
						$this->db->where('(\'' . $dtr->date . '\' BETWEEN date_from AND date_to)');						
						$this->db->where('timekeeping_shift.deleted', 0);
						$this->db->where('employee_id', $employee_id);
						$this->db->join('timekeeping_shift_calendar', 
							'timekeeping_shift_calendar.shift_calendar_id = workschedule_employee.shift_calendar_id');
						$this->db->join('timekeeping_shift', 
							'timekeeping_shift.shift_id = timekeeping_shift_calendar.' . $day . '_shift_id');
						$this->db->order_by('date_from', 'asc');
						$gws = $this->db->get('workschedule_employee');
						
						if ($gws->num_rows() > 0) {
							$result = $gws;
						}

						// Check CWS
						$cws = get_form($employee_id, 'cws', $dummy_p, $dtr->date);

						if ($cws->num_rows() > 0) {
							$cws = $cws->row();
							// Set shift to cws shift id
							$this->db->select('timekeeping_shift.shifttime_start, timekeeping_shift.shifttime_end, timekeeping_shift.shift_grace_period, timekeeping_shift.noon_start, timekeeping_shift.noon_end');
							$this->db->where('shift_id', $cws->shift_id);
							$this->db->where('timekeeping_shift.deleted', 0);
							$result = $this->db->get('timekeeping_shift');
						}

						$shift_start = $result->row()->shifttime_start;
						$shift_end = $result->row()->shifttime_end;
					}

					// Check late
					// Get excused tardiness first.
					$et = get_form($employee_id, 'et', $dummy_p, $dtr->date);

					if ($et->num_rows() == 0 && 
						strtotime(date('H:i:s', strtotime($dtr->time_in1))) > 
						strtotime('+' . $grace_period . ' minutes', strtotime($shift_start))) {
						$infraction[] = $dtr->date;
					}
				}
			}

			if (count($infraction) >= $this->config->item('maximum_late_per_month')) {
				// Create IR
				$ir_data['offence_id'] = $this->config->item('ir_for_late');
				$ir_data['location']   = 'Office';
				$ir_data['complainants'] = $this->userinfo['user_id'];
				$ir_data['involved_employees'] = $employee_id;
				$ir_data['details'] = 'Infraction';
				$ir_data['ir_status_id'] = '3';
				$ir_data['date_sent'] = date('Y-m-d H:i:s');

				$this->db->insert('employee_ir', $ir_data);				
			}
		}

		//if employee is supervisor, no lates and undertime applied
		if($supervisor){
			$summary_update['lates'] = 0;
			$summary_update['undertime'] = 0;
		}

		return array('summary_update' => $summary_update, 'otfile_row' => $otfile_row);
	}

	private function _processDtr($employee_id, $cdate, $regular, $schedule, $summary_update, $for_late_filing = false, $p, $otfile_row, $employee_no, $supervisor, $regular)
	{
		if ($for_late_filing) {
			$get_form = 'get_late_file_form';			
		} else {
			$get_form = 'get_form';
		}

		$tstamp_cdate = strtotime($cdate);
		$otfile_date = date('m/d/Y', $tstamp_cdate);		
		$undertime = 0;
		$overtime = 0;
		$lates = 0;	
		$restday = false;
		$minutes_worked = 0;
		$day_ot = 0;
		$ndot = 0;
		$ot_excess = 0;
		$ndot_excess = 0;	
		$late_infraction = false;
		$undertime_infraction = false;
		$non_regular_eot = false;			

		$day = strtolower(date('l', $tstamp_cdate));
		$tomorrow = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
		$day_prefix = 'reg';
		$day_suffix = '';
	
		// check holiday.
		$holiday = $this->system->holiday_check($cdate, $employee_id);

		if ($holiday) {
			// what type of holuiday					
			if (count($holiday) > 1) {
				$day_prefix = 'dob';
			} else {
				$day_prefix = ($holiday[0]['legal_holiday'] == '1') ? 'leg' : 'spe';
			}
		}

		// Check IN/OUT
		$dtr = get_employee_dtr_from($employee_id, $cdate);

		if ($schedule->{$day . '_shift_id'} == 0) {
			if ($dtr->num_rows() > 0 && $dtr->row()->time_out1 != null) {
				$restday = true;
				$absent  = false;

				if ($holiday) {
					$day_prefix = $day_prefix . 'rd';							
				} else {
					$day_prefix = 'rd';							
				}
			} else {
				$cdate = date('Y-m-d', strtotime('+1 day', $tstamp_cdate));
				$tstamp_cdate = strtotime($cdate);
				return;
			}
		}

		// get specific day of the week so we can query against the shift_calendar table for the proper shift ie monday_shift_id
		$day = strtolower(date('l', $tstamp_cdate));
		// Get default shift for this day.
		$this->db->where('shift_calendar_id', $schedule->shift_calendar_id);
		$this->db->join('timekeeping_shift', 
			'timekeeping_shift.shift_id = timekeeping_shift_calendar.' . $day . '_shift_id');
		$this->db->where('timekeeping_shift_calendar.deleted', 0);

		$result = $this->db->get('timekeeping_shift_calendar');

		// Check for group workschedule.
		$this->db->where('(\'' . $cdate . '\' BETWEEN date_from AND date_to)', '', false);
		$this->db->where('timekeeping_shift.deleted', 0);
		$this->db->where('employee_id', $employee_id);
		$this->db->join('timekeeping_shift_calendar', 
			'timekeeping_shift_calendar.shift_calendar_id = workschedule_employee.shift_calendar_id');
		$this->db->join('timekeeping_shift', 
			'timekeeping_shift.shift_id = timekeeping_shift_calendar.' . $day . '_shift_id');
		$this->db->order_by('date_from', 'asc');
		$gws = $this->db->get('workschedule_employee');								

		if ($gws->num_rows() > 0) {
			$result = $gws;
		}

		// Check CWS
		$cws = get_form($employee_id, 'cws', $p, $cdate);

		if ($cws->num_rows() > 0) {
			$cws = $cws->row();
			// Set shift to cws shift id
			$this->db->select('timekeeping_shift.shifttime_start, timekeeping_shift.shifttime_end, timekeeping_shift.shift_grace_period, timekeeping_shift.noon_start, timekeeping_shift.noon_end');
			$this->db->where('shift_id', $cws->shift_id);
			$this->db->where('timekeeping_shift.deleted', 0);
			$result = $this->db->get('timekeeping_shift');
		}

		$workshift = $result->row();
		$shift_start = $workshift->shifttime_start;
		$shift_end 	 = $workshift->shifttime_end;

		// Get exact datetime to use in case of overlapping days for shift.
		$shift_datetime_start = $cdate . ' ' . $shift_start;
		if (strtotime($shift_start) > strtotime($shift_end)) {
			$shift_datetime_end = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($cdate . ' ' . $shift_end)));
		} else {
			$shift_datetime_end = $cdate . ' ' . $shift_end;
		}

		// Check OBT
		$obt = $get_form($employee_id, 'obt', $p, $cdate);
		if ($dtr->num_rows() > 0 
			&& (
				(is_valid_time($dtr->row()->time_in1)
				&& is_valid_time($dtr->row()->time_out1)
				) ||
				$obt->num_rows() > 0)
			) { // Mark as absent if no out.
			$record = $dtr->row();
			$undertime = 0;
			$overtime = 0;
			$lates = 0;					
			$absent  = false;

			if ($obt->num_rows() > 0) {
				// If no time in set start of obt as time in
				if ($record->time_in1 == '' || is_null($record->time_in1) 
					|| $record->time_in1 == '0000-00-00 00:00:00'
					|| strtotime($record->time_in1) > strtotime($record->date . ' ' . $obt->row()->time_start)
					) {
					$record->time_in1 = $record->date . ' ' . $obt->row()->time_start;
				}

				// If no time out set start of obt as time out
				if (($record->time_out1 == '' || is_null($record->time_out1) || $record->time_out1 == '0000-00-00 00:00:00')
					|| strtotime($obt->row()->time_end) > strtotime(date('H:i:s', strtotime($record->time_out1)))
					) {									
					$record->time_out1 = $record->date . ' ' . $obt->row()->time_end;
				}
			}

			if ($result->num_rows() > 0 || $restday) {
				$r = $result->row();

				$dtr_in = $dtr->row()->time_in1;
				$dtr_out = $dtr->row()->time_out1;

				$break = 0;
				if (!$restday) {
					$break = (strtotime($r->noon_end) - strtotime($r->noon_start));
				} else { // Restday break every 5 hours = 1 hour break (legacy) NEW - if > 5 hours break = 1
					$break = (strtotime($dtr_out) - strtotime($dtr_in)) / 60 / 60;
					if ($break > 5) {
						$break = 60 * 60;
					}
				}

				if( date('Y-m-d',strtotime($dtr_in)) == date('Y-m-d',strtotime($dtr_out) ) ){

					$otcdate = date('Y-m-d',strtotime($dtr_in));

				}
				else{

					//search if what date an overtime is filed
					$oot = $get_form($employee_id, 'oot', $p, date('Y-m-d',strtotime($dtr_in)));

					if($oot->num_rows() > 0){

						foreach( $oot->result_array() as $oot_rec ){						
							if( ( strtotime($dtr_in) <= strtotime($oot_rec['datetime_from']) 
								&&  strtotime($oot_rec['datetime_from']) <= strtotime($dtr_out) ) 
								&& ( strtotime($dtr_in) <= strtotime($oot_rec['datetime_to']) 
									&&  strtotime($oot_rec['datetime_to']) <= strtotime($dtr_out) ) ) {

								$otcdate = date('Y-m-d',strtotime($dtr_in));

							}
						}
					}
					else{

						$otcdate = date('Y-m-d',strtotime($dtr_out));
					}
				}

				$oot = $get_form($employee_id, 'oot', $p, date('Y-m-d', strtotime($otcdate)));
				
				if ($oot->num_rows() > 0) {
					// process								
					foreach ($oot->result() as $ot) {
						// Get actual time worked w/in the OT application.								
						if (strtotime($record->time_out1) >= strtotime($ot->datetime_to)) {
							$otend = $ot->datetime_to;
						} else {
							$otend = $record->time_out1;
						}

						$otstart = $ot->datetime_from;

						if( !$regular && $restday ){

							$time_in = new DateTime($dtr->row()->time_in1);
							$time_out = new DateTime($dtr->row()->time_out1);
							$diff = $time_in->diff($time_out);

							//if overtym exceeds 5 hours, deduct 1 hour for break
							if( $diff->h >= 5 ){
								$total_hours = $diff->h - 1;
							}
							else{
								$total_hours = $diff->h;
							}

							if( $total_hours >= 8 ){
								$non_regular_eot = true;
								$otstart = date('Y-m-d H:i:s', strtotime ( '+9 hours' , strtotime ( $dtr->row()->time_in1 ) ));

							}	
						}

						$overtime += (strtotime($otend) - strtotime($otstart));

						if ($restday || $holiday) {
							$overtime /= 60;

							// Subtract one hour if total is greater than 8
							if ($overtime/60 >= 5 && floor((strtotime($ot->datetime_to) - strtotime($ot->datetime_from)) / 60 / 60 / 5) * 60 > 1) {
								$overtime -= 60;
							}

						} else {
							if (strtotime($ot->datetime_from) < strtotime(date('Y-m-d ' .$r->noon_end, strtotime($cdate)))) {
								$overtime -= $break / 60 / 60;
							}

							$overtime /= 60;									
						}

						$ot_hrs = $overtime / 60;
						$ot_hrs = round($ot_hrs, 2);
						if($ot_hrs > 0){
							if( $ot_hrs >= 5 ) $ot_hrs--;
							if($ot_hrs > 8){
								$ot_hrs_excess = $ot_hrs - 8;
								$ot_hrs = 8;										
								$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], $ot_hrs, '', '');
								$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot_excess'], $ot_hrs_excess, '', '');
							}
							else{
								$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], $ot_hrs, '', '');
							}
						}					
					}

					// Restday must be filed as OT;
					if ($restday || ($holiday && $regular)) {
						$shift_start = date('H:i:s', strtotime($ot->datetime_from));
						$shift_end   = date('H:i:s', strtotime($ot->datetime_to));
					}

					$day_ot = $overtime / 60;
				}

				// Check DTRP
				$dtrp = $get_form($employee_id, 'dtrp', $p, $record->date);

				if ($dtrp->num_rows() > 0) {
					// process
					foreach ($dtrp->result() as $entry) {
						if ($entry->time_set_id == 1) {
							$record->time_in1 = '0000-00-00 ' . $dtrp->time;
						} else {
							$record->time_out1 = '0000-00-00 ' . $dtrp->time;
						}
					}
				}

				$s_start = strtotime($shift_start);
				$s_end   = strtotime($shift_end);


				if ($s_end < $s_start) {
					$s_end = strtotime(date('Y-m-02 H:i:s', $s_end));
				} else {
					$s_end = strtotime(date('Y-m-01 H:i:s', $s_end));
				}

				$s_start = strtotime(date('Y-m-01 H:i:s', $s_start));

				$shift_cache[$cdate]['start'] = $cdate . ' ' . date('H:i:s', $s_start);
				$shift_cache[$cdate]['end'] = $cdate . ' ' . date('H:i:s', $s_end);

				$minutes_worked = ($s_end - $s_start - $break) / 60;

				// Check UT
				if (strtotime(date('Y-m-d H:i:s', strtotime($record->time_out1))) < strtotime($shift_datetime_end)) {
					$undertime = (strtotime($shift_datetime_end) - strtotime(date('Y-m-d H:i:s', strtotime($record->time_out1))));
				}

				if ($this->config->item('remove_undertime_if_out')) {
					// Check OUT
					$out = $get_form($employee_id, 'out', $p, $record->date);

					if ($out->num_rows() > 0) {
						$out = $out->row();
						// process employee leaves before official undertime.
						if (strtotime($record->time_out1) < strtotime(date('Y-m-d ' .$out->time_start, strtotime($record->time_out1)))) {
							$undertime = (strtotime($out->time_start) - strtotime(date('H:i:s', strtotime($record->time_out1))));
						} else {
							$undertime = 0;
						}
					}
				}

				if ($undertime > 0) {							
					if (strtotime($record->time_out1) < strtotime(date('Y-m-d ' .$r->noon_end, strtotime($record->time_out1)))) {
						$undertime -= (strtotime($r->noon_end) - strtotime($r->noon_start));
					}

					$undertime /= 60;
				}						

				// Check late
				$lates = (strtotime(date('H:i:s', strtotime($record->time_in1))) - strtotime($shift_start)) / 60;

				// Check Leave for halfday
				$this->db->select('duration_id, application_form_id');
				$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
				$this->db->where('employee_id', $employee_id);
				$this->db->where('(\'' . $record->date . '\' BETWEEN date_from AND date_to)', '', false);
				$this->db->where('employee_leaves_dates.date', $record->date);
				$this->db->where('form_status_id', 3);
				$this->db->where('employee_leaves_dates.deleted', 0);
				$this->db->where('employee_leaves.deleted', 0);
				
				$leave = $this->db->get('employee_leaves');

				if ($leave->num_rows() > 0) {
					$leave = $leave->row();
					if ($leave->duration_id == 1) {
						$summary_update['hours_worked'] += 8;
						$undertime = 0;
						$lates = 0;
					} elseif ($leave->duration_id == 3) {
						$undertime = 0;
						$summary_update['hours_worked'] += 4;
						$lates = (strtotime(date('H:i:s', strtotime($record->time_in1))) - strtotime($r->noon_end)) / 60;
					} elseif ($leave->duration_id == 2) { // If on first half, clear all lates.
						$lates = 0;
						$summary_update['hours_worked'] += 4;
					}

					$summary_update['leaves'] += (($leave->duration_id == 1) ? 1 : .5) * 8;
					$leave_hours = (($leave->duration_id == 1) ? 1 : .5) * 8;
					switch($leave->application_form_id){
						case 7:
							$leave_type = 'LWOP';
							break;
						default:
							$leave_type = 'LWP';
							break;		
					}
					if($leave_type == "LWOP") $otfile_row[] = array($employee_no, $otfile_date, '', '', $leave_type, $leave_hours );
				}

				$grace_period = date('i', strtotime($r->shift_grace_period));

				if ($restday || $holiday) {
					$lates = 0;
				}
				
				// Get excused tardiness.
				$et = $get_form($employee_id, 'et', $p, $record->date);

				if (!$supervisor && $et->num_rows() == 0 && 
					strtotime(date('H:i:s', strtotime($record->time_in1))) > 
					strtotime('+' . $grace_period . ' minutes', strtotime($shift_start))) {							
					if ($p->apply_lates == 1 && 
						(strtotime($p->apply_late_from) >= $tstamp_cdate || $tstamp_cdate <= strtotime($p->apply_late_to))
						) {
						$infraction[] = $cdate;
					}
					
					if($lates > 0)
						$late_infraction = true;
				} else if ($et->num_rows() > 0 && $lates > 0) {
					$late_infraction = false;
				}

				// If late is less than an hour don't deduct
				if ($lates > 60) {
					$minutes_worked -= $lates;
				}

				if ($restday) {
					$overtime -= $lates;							
				} 

				$minutes_worked -= $undertime;

				if ($day_prefix != 'reg' && $day_suffix == '') {
					if ($restday) {
						if ($overtime > 0) {
							$minutes_worked = $overtime - $lates;
						} else {
							$minutes_worked = 0;
						}
					}

					$summary_update[$day_prefix] += $minutes_worked / 60;
				}

				// Night diff
				$ndstart = strtotime(date('Y-m-d 22:00:00', $tstamp_cdate));
				$ndend   = strtotime(date('Y-m-d 06:00:00', strtotime($tomorrow)));
				$nd = 0;

				// Start from or after ND
				if (strtotime($record->time_in1) >= $ndstart
					|| (strtotime($record->time_in1) < $ndstart
						&& strtotime($record->time_out1) > $ndstart
						)
					) {
					$ndshiftend = (strtotime($record->time_out1) <= $ndend) 
									? strtotime($record->time_out1) : $ndend;

					// Subtract break from ND

					$get_break=$this->system->get_break($shift_start, $workshift->noon_start,$workshift->noon_end, $cdate);

					if (strtotime(date('Y-m-d ' . $workshift->noon_start, strtotime($get_break['break_start_date']))) < $ndstart) {
						$nd = ($ndshiftend - $ndstart) / 60 / 60;
					} else {	
						$nd = ($ndshiftend - $ndstart - $break) / 60 / 60;
					}

					if ($restday || $holiday) {
						$nd -= floor(($ndshiftend - $ndstart) / 60 / 60 / 5);								
					}

					$w_ndot = false;
					
					if ($overtime > 0) {
						if (strtotime($otstart) < $ndstart
							&& strtotime($otend) > $ndstart									
						) {
							$w_ndot = true;	
						}
					}

					// get NDOT
					if ($w_ndot) {
						if (strtotime($dtr->row()->time_out1) >= strtotime(date('h:i:s', strtotime($ot->datetime_to)))) {
							$otend = $ot->datetime_from;
						} else {
							if ($restday) {
								$otend = $ot->datetime_to;
							} else {
								$otend = $dtr->row()->time_out1;
							}
						}

						if (strtotime($otend) > $ndshiftend) {
							$otend = $ndshiftend;									
						} else {
							$otend = strtotime($otend);
						}

						//$day_ot += ($ndstart - strtotime($ot->datetime_from)) / 60 / 60;
						if (strtotime(date('Y-m-d ' . $workshift->noon_start, strtotime($get_break['break_start_date']))) < $ndstart) {
							$ndot = ($otend - $ndstart) / 60 / 60;
						} else {
							$ndot = ($otend - $ndstart - $break) / 60 / 60;							
						}

						if ($ndot >= 1) {							
							// breakdown OT further into regular and night diff									
							$day_ot = ($overtime / 60) - $ndot;

							// Subtract the hours past night diff, we already have total overtime at this point, need to correct
							if (strtotime($dtr_out) > $ndshiftend) {
								$day_ot -= (strtotime($dtr_out) - $ndshiftend) / 60 / 60;
							}
						} else {
							$day_ot += $ndot;
							$overtime += $ndot * 60;
						}

						$otvar = $day_prefix . '_ot';
						$otvar = ($ndstart - strtotime($ot->datetime_from)) / 60 / 60 / 60;
						$otvar = $ot_hrs = round( $otvar, 2 );
						if( $day_prefix . '_ot' != 0 ){
							if( $ot_hrs >= 5 ) $ot_hrs--;
							if($ot_hrs > 8){
								$ot_hrs_excess = $ot_hrs - 8;
								$ot_hrs = 8;										
								$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], $ot_hrs, '', '');
								$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot_excess'], $ot_hrs_excess, '', '');
							}
							else{
								$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], $ot_hrs, '', '');
							}
						}
						$otvar = $day_prefix . '_ndot';
						$otvar = (strtotime($otend) - $ndstart) / 60 / 60 / 60;
						$otvar = round($otvar, 2);
						if( $day_prefix . '_ndot' != 0 ) $otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix . '_ndot'], $otvar, '', '' );

					} elseif (
						strtotime(date('Y-m-d '. $shift_start, $tstamp_cdate)) >= $ndstart
							|| (strtotime(date('Y-m-d '. $shift_start, $tstamp_cdate)) < $ndstart
								&& strtotime(date('Y-m-d '. $shift_end, strtotime($record->time_out1))) > $ndstart)
						) {		
						
						$summary_update[$day_prefix .'_nd'] += $nd;

						$otfilend = round($nd, 2);
						if($otfilend > 0)  $otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix . '_nd'], $otfilend, '', '' );
					}


					if ($overtime / 60 > 8) {
						$ot_excess += ($overtime / 60 - 8);

						if ($ot_excess < 0) {
							$ot_excess = 0;
						}

						if ($day_ot + $ndot > 8) {
							$ndot_excess = $overtime / 60 - 8 - $ot_excess;
							$ndot -= $ndot_excess;
							$day_ot -= $ot_excess;
						}
					}
										
				} else {

					if ($overtime / 60 > 8) {
						// Deduct excess overtime
						$day_ot -= $ot_excess += $overtime / 60 - 8;
					}
				}

				$summary_update['undertime'] += $undertime / 60;
				
				if ($minutes_worked / 60 > 8) {
					$minutes_worked = 60 * 8;
				}			
							
				
				if($day_prefix == 'leg' || ($day_prefix == 'spe' && $regular)) {
					$undertime = $lates = 0;					
					$minutes_worked = 60 * 8;
				}

				if ($restday) {
					if( !$regular  ){
						if( $non_regular_eot ){
							$minutes_worked = 480;
						}
						else{

							$time_in = new DateTime($dtr->row()->time_in1);
							$time_out = new DateTime($dtr->row()->time_out1);
							$diff = $time_in->diff($time_out);

							$sh_start = strtotime ( $dtr->row()->time_in1 );
							$sh_end = strtotime ( '+'.$diff->h.' hours' , strtotime ( $dtr->row()->time_in1 ) );

							if( $diff->h >= 5 ){

								$overtime = 0;

								if( $diff->h > 8 ){
									$minutes_worked = 480;
								}
								else{
									$minutes_worked = ($sh_end - $sh_start - 3600) / 60;
								}
							}
							else{
								$overtime = 0;
								$minutes_worked = ($sh_end - $sh_start) / 60;
							}
						}
					}
					else{
						$minutes_worked = 0;
					}
				}

				if ($overtime < 0) {$overtime = 0;}

				$summary_update[$day_prefix .'_ot'] += $day_ot;
				$summary_update[$day_prefix .'_ndot'] += $ndot;
				$summary_update[$day_prefix .'_ot_excess'] += $ot_excess;
				$summary_update[$day_prefix .'_ndot_excess'] += $ndot_excess;
				
				//to avoid negative lates
				if($lates < 0)
					$lates=0;

				$summary_update['overtime'] += $overtime / 60;
				$summary_update['lates'] += $lates / 60;
				$summary_update['hours_worked'] += $minutes_worked / 60;
			}
		} else { // No time record entry
			$absent = !($holiday ||
				($dtr->row()->time_in1 != null && is_valid_time($dtr->row()->time_in1))
				|| ($dtr->row()->time_out1 != null && is_valid_time($dtr->row()->time_out1))
				);
			// Check leave for whole day

			$this->db->select('duration_id,employee_leaves_dates.employee_leave_date_id, employee_leaves_dates.credit, employee_leaves_dates.employee_leave_date_id, employee_leaves.application_form_id, employee_form_type.application_code');
			$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id', 'left');

			$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
			$this->db->where('employee_id', $employee_id);
			$this->db->where('(\'' . $cdate . '\' BETWEEN date_from AND date_to)', '', false);
			$this->db->where('employee_leaves_dates.date', $cdate);
			$this->db->where('form_status_id', 3);
			$this->db->where('employee_leaves_dates.deleted', 0);
			$this->db->where('employee_leaves.deleted', 0);

			$leave = $this->db->get('employee_leaves');
			
			if ($leave->num_rows() > 0) {
				$absent = false;
				$leave = $leave->row();
				if ($leave->duration_id == 1) {							
					$hours_worked = 8;
				} else {							
					$hours_worked = 4;
				}
				
				$summary_update['hours_worked'] += $hours_worked;
				$minutes_worked = 8*60;

				if ($holiday) {
					// Return leave credit.
					$this->db->set(strtolower($leave->application_code) . '_used' , strtolower($leave->application_code) . '_used - ' . $leave->credit, false);
					$this->db->where('employee_id', $employee_id);
					$this->db->where('year', date('Y'));

					$this->db->update('employee_leave_balance');

					// Remove credit
					$this->db->where('employee_leave_date_id', $leave->employee_leave_date_id);
					$this->db->update('employee_leaves_dates', array('credit' => 0));
					// Send email.
				}
				$leave_hours = (($leave->duration_id == 1) ? 1 : .5) * 8;
				switch($leave->application_form_id){
					case 7:
						$leave_type = 'LWOP';
						break;
					default:
						$leave_type = 'LWP';
						break;		
				}
				if($leave_type == "LWOP") $otfile_row[] = array($employee_no, $otfile_date, '', '', $leave_type, $leave_hours );
			} else if ($holiday) {
				$summary_update['hours_worked'] += 8;
				$minutes_worked = 8*60;
			}	

			// Check OBT
			$obt = $get_form($employee_id, 'obt', $p, $cdate);

			if ($obt->num_rows() > 0) {
				$obt = $obt->row();
				$absent = false;						
				$minutes_worked = (strtotime($obt->time_end) - strtotime($obt->time_start)) / 60;
				
				$summary_update['hours_worked'] += $minutes_worked / 60;
				
				$et = $get_form($employee_id, 'et', $p, $record->date);

				if ($et->num_rows() == 0 && 
					strtotime(date('H:i:s', strtotime($record->time_in1))) > 
					strtotime('+' . $grace_period . ' minutes', strtotime($shift_start))) {

					$summary_update['lates'] += $lates = (strtotime(date('H:i:s', strtotime($obt->time_start))) - strtotime($shift_start)) / 60;
					if ($p->apply_lates == 1 && 
						(strtotime($p->apply_late_from) >= $tstamp_cdate || $tstamp_cdate <= strtotime($p->apply_late_to))
						) {
						$infraction[] = $cdate;
					}							
				}			

				// Check UT
				if (strtotime(date('H:i:s', strtotime($obt->time_end))) < strtotime($shift_end)) {
					$undertime = (strtotime($shift_end) - strtotime(date('H:i:s', strtotime($obt->time_end)))) / 60;
					if($undertime > 5) $undertime -= 1;
				}

				// Check OUT
				$out = $get_form($employee_id, 'out', $p, $cdate);

				if ($out->num_rows() > 0) {
					$out = $out->row();
					// process								
					$out = (strtotime($out->time_end) - strtotime(date('H:i:s', strtotime($obt->time_end)))) / 60;
					if ($out > 0) {
						$undertime = $out;
						if($undertime > 5) $undertime -= 1;
					} else {
						$undertime = 0;
					}
				}									
			}

			// Check OT
			$oot = $get_form($employee_id, 'oot', $p, $cdate);

			if ($oot->num_rows() > 0) {
				// process
				foreach ($oot->result() as $ot) {
					$overtime += (strtotime($ot->datetime_to) - strtotime($ot->datetime_from)) / 60;
					$otfileot = $overtime / 60;
					$otfileot = $ot_hrs = round($otfileot,2);
					if($otfileot > 0){
						if( $ot_hrs >= 5 ) $ot_hrs--;
						if($ot_hrs > 8){
							$ot_hrs_excess = $ot_hrs - 8;
							$ot_hrs = 8;										
							$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], $ot_hrs, '', '');
							$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot_excess'], $ot_hrs_excess, '', '');
						}
						else{
							$otfile_row[] = array($employee_no, $otfile_date, $this->otcode[$day_prefix.'_ot'], $ot_hrs, '', '');
						}
					}
				}

				$summary_update['overtime'] += number_format($overtime / 60, 2);
				$absent = false;
			}	

		} // End DTR check	

		if ($overtime < 0) {$overtime = 0;}

		$undertime_infraction = ($undertime > 0 && !$supervisor && isset($out) && $out->num_rows() > 0);

		//if employee is supervisor, no lates and undertime applied
		if($supervisor){
			$lates = 0;
			$undertime = 0;
		}

		if( !$regular && ( $day_prefix == "spe" ) && ( $overtime == 0 ) ){
			$lates = 0;
			$undertime = 0;
			$minutes_worked = 0;
		}

		if($undertime > 0){
			$otfile_row[] = array($employee_no, $otfile_date, '', '', 'UT', round($undertime / 60, 2) );
		}

		if( $lates > 0 ){
			$otfile_row[] = array($employee_no, $otfile_date, '', '', 'TDY', round($lates / 60, 2) );
		}

		$this->db->where('employee_id', $employee_id);
		$this->db->where('date', $cdate);
		$edtr = $this->db->get('employee_dtr');
		if ($edtr->num_rows() == 0) {
			$this->db->insert('employee_dtr', array(
				'lates' => $lates, 'overtime' => $overtime, 'undertime' => $undertime, 'reg_nd' =>$nd,
				'employee_id' => $employee_id, 'date' => $cdate, 'awol' => false,
				'hours_worked' => $minutes_worked / 60, 'undertime_infraction' => $undertime_infraction, 'late_infraction' => $late_infraction
				)
			);
			$dtr_id = $this->db->insert_id();
		} else {
			$dtr_id = $edtr->row()->id;
			$this->db->where('employee_id', $employee_id);
			$this->db->where('date', $cdate);
			$this->db->update('employee_dtr', array(
				'lates' => $lates, 'overtime' => $overtime, 
				'undertime' => $undertime, 'reg_nd' =>$nd, 'awol' => false,
				'hours_worked' => $minutes_worked / 60, 'undertime_infraction' => $undertime_infraction, 'late_infraction' => $late_infraction
				)
			);
		}

		$r = array('summary_update' => $summary_update, 'otfile_row' => $otfile_row);

		if ($absent || $restday || $holiday) {
			if (!$restday && !$holiday) {
				$r['absent_dtr'] = $dtr_id;
			}					
		}
		
		return $r;
	}


	function get_break($shift_start,$break_start,$break_end,$cdate)
	{
		$break=array();
		if($break_start < $shift_start) {
			$break['break_start_date']=date('Y-m-d', strtotime('+1 day', strtotime($cdate)));
			$break['break_end_date']=date('Y-m-d', strtotime('+1 day', strtotime($cdate)));
			$break['break_start_time']=$break_start;
			$break['break_end_time']=$break_end;
			$break['type'] = "Next Day";
			//strtotime(date('Y-m-d ' . $workshift->noon_start, $tstamp_cdate))
		} else {
			$break['break_start_date']=date('Y-m-d', strtotime($cdate));
			$break['break_end_date']=date('Y-m-d', strtotime($cdate));
			$break['break_start_time']=$break_start;
			$break['break_end_time']=$break_end;
			$break['type'] = "Same Day";
		}
		return $break;
	}

	function get_employee_circle( $user_id , $portlet_id){
		$is_group = $this->db->get_where('user_position', array('deleted' => 0, 'position_id' => $this->userinfo['position_id']))->row();
		$portlet_config = unserialize($is_group->portlet_config);
		$where_in = array();
			
		//get user info
		$user = $this->db->get_where('user', array('user_id' => $user_id))->row();
		$emp = $this->db->get_where('employee', array('employee_id' => $user_id))->row();
		$rank = $this->db->get_where('user_rank', array('job_rank_id' => $emp->rank_id))->row();
		$division_id = $user->division_id;
		$department_id = $user->department_id;
		$position_id = $user->position_id;
		$rank_id = $emp->rank_id;
		$rank_index = $rank->rank_index;

		$company_id = $this->userinfo['company_id'];

		if($portlet_config[$portlet_id]['access'] == "group") {
			//by default get the poeple in my department
			$qry = "SELECT a.* 
					FROM {$this->db->dbprefix}user a 
						LEFT JOIN {$this->db->dbprefix}employee b 
							ON b.user_id = a.user_id 
					WHERE a.deleted = 0 
						AND a.inactive = 0 
						AND '{$department_id}' IN (a.department_id) 
						AND company_id = '{$company_id}'
						AND b.resigned = 0";
			$emps = $this->db->query($qry);
			if($emps->num_rows() > 0){
				foreach($emps->result() as $row){
					$where_in[] = $row->user_id;
				}
			}

			//check if department head
			$this->db->where(array('company_id' => $company_id));
			$this->db->where('dm_user_id = '. $user_id . ' OR dm_position_id = ' .$position_id);
			$this->db->where(array('deleted' => 0));
			$depthead = $this->db->get('user_company_department');
			if( $depthead->num_rows() > 0 ){
				foreach($depthead->result() as $dep){
					if( $department_id !=  $dep->department_id){
						//by default get the poeple in my department
						$qry = "SELECT a.* 
								FROM {$this->db->dbprefix}user a 
									LEFT JOIN {$this->db->dbprefix}employee b 
										ON b.user_id = a.user_id 
								WHERE a.deleted = 0 
									AND a.inactive = 0 
									AND '{$dep->department_id}' IN (a.department_id) 
									AND b.resigned = 0";
						$emps = $this->db->query($qry);
						if($emps->num_rows() > 0){
							foreach($emps->result() as $row){
								$where_in[] = $row->user_id;
							}
						}
					}
		
					$division_id = $dep->division_id;
					//get other department heads
					if ($division_id != '' && $department_id != '' && $division_id != 0 && $department_id != 0){
						$deps = $this->db->get_where('user_company_department', array('division_id' => $division_id, 'department_id !=' => $department_id));
						if( $deps && $deps->num_rows() > 0 ){
							foreach( $deps->result() as $depdetail ){
								if( !empty($depdetail->dm_user_id) ){
									$where_in[] = $depdetail->dm_user_id;
								}
							}
						}
					}

					//get my division head
					$mydiv = $this->db->get_where('user_company_division', array('division_id' => $division_id))->row();
					if( !empty($mydiv->division_manager_id) ){
						$where_in[] = $mydiv->division_manager_id;
					}
				}
			}

			//check if divison head
			$this->db->where('(division_manager_id = '. $user_id . ' OR dm_position_id = ' .$position_id.')');
			$this->db->where(array('deleted' => 0));
			$divhead = $this->db->get('user_company_division');
			if( $divhead->num_rows() == 1 ){
				$div = $divhead->row();
				$division_id = $div->division_id;
				//every person in my division
				$qry = "SELECT a.* 
						FROM {$this->db->dbprefix}user a 
							LEFT JOIN {$this->db->dbprefix}employee b 
								ON b.user_id = a.user_id 
						WHERE a.deleted = 0 
							AND a.inactive = 0 
							AND '{$division_id}' IN (a.division_id) 
							AND b.resigned = 0";
				$emps = $this->db->query($qry);
				if($emps->num_rows() > 0){
					foreach($emps->result() as $row){
						if(!in_array($row->user_id, $where_in)) $where_in[] = $row->user_id;
					}
				}

				//get other division head heads
				$divs = $this->db->get_where('user_company_division', array('division_id' => $division_id, 'division_id !=' => $division_id));
				if( $divs->num_rows() > 0 ){
					foreach( $divs->result() as $div ){
						if( !empty($div->division_manager_id) ){
							$where_in[] = $div->division_manager_id;
						}
					}
				}

				//get my reporting to
				$reporting_to = $this->get_reporting_to( $user_id );
				if( $reporting_to ){
					$reporting_tos = explode(',', $reporting_to);
					foreach($reporting_tos as $reporting_to){
						if(!in_array($reporting_to, $where_in)) $where_in[] = $reporting_to;
					}
				}
			}
		}

		if($portlet_config[$portlet_id]['access'] == "all") {
			$qry = "SELECT a.* 
					FROM {$this->db->dbprefix}user a 
						LEFT JOIN {$this->db->dbprefix}employee b 
							ON b.user_id = a.user_id 
					WHERE a.deleted = 0 
						AND a.inactive = 0 
						AND company_id = '{$company_id}'
						AND b.resigned = 0";
			$emps = $this->db->query($qry);
			if($emps->num_rows() > 0){
				foreach($emps->result() as $row){
					$where_in[] = $row->user_id;
				}
			}
		}

		return $where_in;
	}

    function get_employee_worksched_shift_restday($employee_id = FALSE,$datetmp = FALSE){
    	$check = false;
    	while ($check = false) {
    		$date = date('Y-m-d',strtotime('-1 day',$datetmp));
        	$emp = $this->get_employee_worksched($employee_id, date('Y-m-d', strtotime($date))); 
        	if ($emp){
        		$check = true;
        	}
        	$datetmp = $date;
    	}

        switch( date('N', strtotime($date))){
            case 1:
                $where = array('shift_id' => $emp->monday_shift_id);
                break;
            case 2:
                $where = array('shift_id' => $emp->tuesday_shift_id);
                break;
            case 3:
                $where = array('shift_id' => $emp->wednesday_shift_id);
                break;
            case 4:
                $where = array('shift_id' => $emp->thursday_shift_id);
                break;
            case 5:
                $where = array('shift_id' => $emp->friday_shift_id);
                break;
            case 6:
                $where = array('shift_id' => $emp->saturday_shift_id);
                break;
            case 7:
                $where = array('shift_id' => $emp->sunday_shift_id);
                break;    
        }

        $shift = $this->db->get_where('timekeeping_shift', $where);
        $data_row = $shift->row();
        
        return $data_row;
    }	

	function get_applicant($applicant_id) {
		$this->db->select('*');
		$this->db->join('recruitment_applicant_education', 'recruitment_applicant_education.applicant_id = recruitment_applicant.applicant_id', 'left');
		$this->db->join('recruitment_applicant_employment', 'recruitment_applicant_employment.applicant_id = recruitment_applicant.applicant_id', 'left');
		$this->db->join('recruitment_applicant_family', 'hr_recruitment_applicant_family.applicant_id = recruitment_applicant.applicant_id', 'left');
		$this->db->join('recruitment_applicant_history', 'recruitment_applicant_history.applicant_id = recruitment_applicant.applicant_id', 'left');
		$this->db->join('recruitment_applicant_references', 'recruitment_applicant_references.applicant_id = recruitment_applicant.applicant_id', 'left');
		$this->db->join('recruitment_applicant_skills', 'recruitment_applicant_skills.applicant_id = recruitment_applicant.applicant_id', 'left');
		$this->db->join('recruitment_applicant_training', 'recruitment_applicant_training.applicant_id = recruitment_applicant.applicant_id', 'left');
		$this->db->where('recruitment_applicant.applicant_id', $applicant_id);
		$this->db->where('recruitment_applicant.deleted', 0);
		$this->db->limit(1);

		$applicant = $this->db->get('recruitment_applicant');

		if (!$applicant || $applicant->num_rows() == 0) {

			return 0;

		} else {

			return $applicant->row_array();
		}			
	}   

	function check_and_get_cashout($employee_id = false, $year = false)
	{
	    if($year)
	    {
	        if($employee_id)
	            $emp_id = $employee_id;
	        else
	            $emp_id = $this->userinfo['user_id'];

	        $response = array();

	        $cashout_leaves = $this->db->get_where('cashout_leaves', array("employee_id" => $emp_id, "year" => $year));
	        if($cashout_leaves && $cashout_leaves->num_rows() > 0)
	            return 5;
	        else
	            return 0;
	    } else
	        return false;
	}

	function get_cred_duration($emp_id = null, $date = null)
	{
		$emp_id = (is_null($emp_id) ? $this->user->user_id : $emp_id);
		$date = (is_null($date) ? date('Y-m-d') : $date);

		$schedule = $this->get_employee_worksched($emp_id, $date);

		$day = strtolower(date('l', strtotime($date)));

		if(isset($schedule->has_cws)){
			$day_shift_id = $schedule->shift_id;
		} else if(isset($schedule->has_cal_shift)) {
			$day_shift_id = $schedule->shift_id;
		} else{
			$day_shift_id = $schedule->{$day . '_shift_id'};
		}

		return $this->db->get_where('timekeeping_shift', array('shift_id' => $day_shift_id))->row();
	}

	function get_employee_type_by_date($employee, $date){
		$qry = "SELECT employee_id, employee_type, current_employee_type_dummy, transfer_effectivity_date, hidden_id_current
		FROM hr_employee_movement
		WHERE employee_id = {$employee->employee_id} AND '{$date}' <= transfer_effectivity_date
		AND processed = 1 AND deleted = 0
		ORDER BY transfer_effectivity_date ASC";
		$result = $this->db->query( $qry );
		
		if( $result->num_rows() > 0 ){
			$employee_type = 0;
			$employee_type_found = false;

			$movement = $result->row();
			if($date == $movement->transfer_effectivity_date){
				$employee_type = $movement->employee_type;
				$employee_type_found = true;
			}

			if( $employee_type == 0 ){
				$hidden_id_current = $movement->hidden_id_current;
				$vars = explode(', ', $hidden_id_current);
				foreach($vars as $var){
					$varpair = explode(' = ', $var);
					if($varpair[0] == 'employee_type'){
						$employee_type = $varpair[1];
						$employee_type_found = true;
						break;
					}
				}
			}

			if($employee_type_found){
				$type = $this->db->get_where('employee_type', array('employee_type_id' => $employee_type))->row();
			}else{
				$current_employee_type = $movement->current_employee_type_dummy;
				$type = $this->db->get_where('employee_type', array('employee_type' => $current_employee_type))->row();	
			}	

			return $type->employee_type_id;
		}

		return $employee->employee_type;
	}

	// currently this is fix. can be set on database if needed
	function get_movement_hidden_id_list()
	{
		return array(  'position_id' => 0,
					   'role_id' => 1,
					   'department_id' => 2,
					   'rank_id' => 3,
					   'employee_type' => 4,
					   'job_level' => 5,
					   'range_of_rank' => 6,
					   'rank_code' => 7,
					   'company_id' => 8,
					   'division_id' => 9,
					   'location_id' => 10,
					   'segment_1_id' => 11,
					   'segment_2_id' => 12,
					   'status_id' => 13
					   );
	}
	
	/**
	 * Will get the value of previous employee information before movement.
	 * @param hidden_id list (either from POST or dbase located at (hr_employee_movement))
	 * @param string (string you are looking for)
	 * @return integer (id of what you are looking for)
	 */
	function get_old_id_on_hidden($hidden_id_current, $looking_for)
	{
		$list_of_hidden_array = $this->get_movement_hidden_id_list();

		if(in_array($looking_for, $list_of_hidden_array)) {
			$pieces = explode(',', $hidden_id_current);
			$pieces_key = $list_of_hidden_array[$looking_for];

			$value = explode(' = ', $pieces[$pieces_key]);
			return $value[1];
		} else {
			return 0;
		}
	}

	function get_employe_leave_setup_exemption($employee_id,$leave_setup_id){
		$sql = "SELECT * FROM {$this->db->dbprefix}employee_type_leave_setup_exemption 
				WHERE FIND_IN_SET('".$employee_id."',employee_id) 
				AND leave_setup_id = {$leave_setup_id}
				AND deleted = 0
				ORDER BY date_created DESC";
		$exemption = $this->db->query($sql);

		if ($exemption && $exemption->num_rows > 0){
			$exemption_info = $exemption->row_array();
			return $exemption_info;
		}		
		else{
			return array("base"=>0,"accumulation"=>0,"maximum"=>0,"inexcess"=>0);
		}
	}

	function get_recruitment_category($category_id,$category_value_id){
		switch ($category_id) {
			case 1:	//by company
				$this->db->select('company_id as cat_id,company as cat_value');
				$this->db->where('company_id',$category_value_id);
				$result = $this->db->get('user_company');
				break;
			case 2:	//by division
				$this->db->select('division_id as cat_id,division as cat_value');			
				$this->db->where('division_id',$category_value_id);
				$result = $this->db->get('user_company_division');
				break;
			case 3:	//by group
				$this->db->select('group_name_id as cat_id,group_name as cat_value');			
				$this->db->where('group_name_id',$category_value_id);
				$result = $this->db->get('group_name');
				break;
			case 4:	//by department
				$this->db->select('department_id as cat_id,department as cat_value');
				$this->db->where('department_id',$category_value_id);
				$result = $this->db->get('user_company_department');
				break;
			case 5:	//by project
				$this->db->select('project_name_id as cat_id,project_name as cat_value');
				$this->db->where('project_name_id',$category_value_id);
				$result = $this->db->get('project_name');
				break;																
		}

		$cat = array('cat_id' => '','cat_value' => '');

		if ($result && $result->num_rows() > 0){
			$row = $result->row();
			$cat['cat_id'] = $row->cat_id;
			$cat['cat_value'] = $row->cat_value;
		}

		return $cat;
	}

	function get_employee_all_reporting_to( $user_id ){
		$where_in = array();
			
		//get user info
		$user = $this->db->get_where('user', array('user_id' => $user_id))->row();
		$emp = $this->db->get_where('employee', array('employee_id' => $user_id))->row();
		$rank = $this->db->get_where('user_rank', array('job_rank_id' => $emp->rank_id))->row();
		$division_id = $user->division_id;
		$department_id = $user->department_id;
		$position_id = $user->position_id;
		$rank_id = $emp->rank_id;
		$rank_index = $rank->rank_index;

		//check if divison head
		//$this->db->where('(division_manager_id = '. $user_id . ' OR dm_position_id = ' .$position_id.')');
/*		$this->db->where('(division_manager_id = '. $user_id . ')');
		$this->db->where(array('deleted' => 0));
		$divhead = $this->db->get('user_company_division');*/

		$query = "SELECT GROUP_CONCAT(division_id) AS division_id FROM {$this->db->dbprefix}user_company_division WHERE division_manager_id = {$user_id} GROUP BY division_manager_id";
		$divhead = $this->db->query($query);

		if( $divhead && $divhead->num_rows() > 0 ){
			$div = $divhead->row();
			$division_id = $div->division_id;
			//every person in my division
			$qry = "SELECT a.* 
			FROM {$this->db->dbprefix}user a 
			LEFT JOIN {$this->db->dbprefix}employee b ON b.user_id = a.user_id 
			WHERE a.deleted = 0 AND a.inactive = 0 AND a.division_id IN ({$division_id})
			AND b.resigned = 0";
			$emps = $this->db->query($qry);

			if($emps->num_rows() > 0){
				foreach($emps->result() as $row){
					if(!in_array($row->user_id, $where_in)) $where_in[] = $row->user_id;
				}
			}
			
		}

		//get my reporting to
		$reporting_tos = $this->get_position_subordinates( $user_id,$position_id );

		if( $reporting_tos ){

			foreach($reporting_tos as $reporting_to){
				if(!in_array($reporting_to, $where_in)) $where_in[] = $reporting_to;
			}
		}

		return $where_in;
	}

	function get_position_subordinates( $user_id, $position_id ){

		if(empty( $user_id )) return false;
		if(empty( $position_id )) return false;

		$this->db->select('user.user_id');
		$this->db->from('user');
		$this->db->join('employee','employee.employee_id = user.employee_id','left');
		$this->db->join('user_position','user_position.position_id = user.position_id','left');
		$this->db->where('user.deleted',0);
		$this->db->where('employee.resigned',0);
		$this->db->where('user_position.reporting_to',$position_id);

		$subordinates = $this->db->get();

		if($subordinates->num_rows() > 0){
			$subordinate_result = $subordinates->result_array();

			$subordinate = array();

			foreach( $subordinate_result as $subordinate_info ){

				array_push($subordinate, $subordinate_info['user_id']);

			}

			return $subordinate;
		}
		else{
			return false;
		}

	}
}

?>