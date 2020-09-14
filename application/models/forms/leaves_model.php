<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Leaves_model extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function _get_affected_dates( $call_from_within = false ){
		if (IS_AJAX || $call_from_within) {
			$start_date = date('Y-m-d', strtotime($this->input->post('date_from')));
			$end_date = date('Y-m-d', strtotime($this->input->post('date_to')));
			$days = array();
			$days_ctr = 0;

			$userinfo = $this->db->get_where('employee',array('employee_id'=>$this->input->post('employee_id')))->row();
			$record = $this->db->get_where('employee_leaves',array('employee_leave_id'=>$this->input->post('record_id')))->row();

			while( $start_date <= $end_date ){
				//check if holidays
				$on_holiday = false;
				$holiday = $this->system->holiday_check( $start_date, $this->input->post('employee_id'), true);

				if( $holiday && $record->application_form_id != 5 ){
					//check wether holiday applies to employee
					
					foreach( $holiday as $day ){
						$on_holiday = true;

						$where = array('employee_id' => $this->input->post('employee_id'), 'holiday_id' => $day['holiday_id']);
						$emp_holiday = $this->db->get_where('holiday_employee', $where);
						if( $emp_holiday->num_rows() > 0){
							$on_holiday = true;
						}

						//additional checking for not legal holiday and base on location inputted //tirso
						if (!$day['legal_holiday'] && $day['location_id'] <> ''){
							$location_array = explode(',',$day['location_id']);
							if (in_array($userinfo->location_id, $location_array)){
								$on_holiday = true;
							}
							else{
								$on_holiday = false;	
							}
						}
					}
				}

				if( !$on_holiday ){
					//get the work sched
					$worksched = $this->system->get_employee_worksched(  $this->input->post('employee_id'), $start_date);
					
					if((isset($worksched->has_cws) && $worksched->has_cws)){
						$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
						$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
						$days[$days_ctr]['employee_leave_date_id'] = '0';

						if( $this->input->post('record_id') != "-1" ){
							$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
							$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
							$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
							$leave_date_result = $this->db->get('employee_leaves');

							if( $leave_date_result->num_rows() > 0 ){
								$leave_date_record = $leave_date_result->row();
								$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
							}
							else{
								$days[$days_ctr]['duration_id'] = 1;
							}
						}
						else{
							$days[$days_ctr]['duration_id'] = 1;
						}

						$days_ctr++;
					} else if(isset($worksched->has_cal_shift) && $worksched->has_cal_shift) {
						if(!empty($worksched->shift_id) && $worksched->shift_id != 1)
						{
							$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
							$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
							$days[$days_ctr]['employee_leave_date_id'] = '0';

							if( $this->input->post('record_id') != "-1" ){
								$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
								$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
								$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
								$leave_date_result = $this->db->get('employee_leaves');

								if( $leave_date_result->num_rows() > 0 ){
									$leave_date_record = $leave_date_result->row();
									$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
								}
								else{
									$days[$days_ctr]['duration_id'] = 1;
								}
							}
							else{
								$days[$days_ctr]['duration_id'] = 1;
							}

							$days_ctr++;
						}
					} else{
						//check shift base on work sched to remove days which fall on rests days

						switch( date('N', strtotime( $start_date )) ){
							case 1:
								if( !empty( $worksched->monday_shift_id ) && $worksched->monday_shift_id != 1 ){
									$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
									$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
									$days[$days_ctr]['employee_leave_date_id'] = '0';

									if( $this->input->post('record_id') != "-1" ){
										$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
										$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
										$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
										$leave_date_result = $this->db->get('employee_leaves');

										if( $leave_date_result->num_rows() > 0 ){
											$leave_date_record = $leave_date_result->row();
											$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
										}
										else{
											$days[$days_ctr]['duration_id'] = 1;
										}
									}
									else{
										$days[$days_ctr]['duration_id'] = 1;
									}

									// for firstbalfour 
									if ($worksched->considered_halfday){
										$days[$days_ctr]['considered_restday'] = 1;	
										$days[$days_ctr]['duration_id'] = 4;
									}

									$days_ctr++;

								}
								break;
							case 2:
								if( !empty( $worksched->tuesday_shift_id ) && $worksched->tuesday_shift_id != 1 ){
									$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
									$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
									$days[$days_ctr]['employee_leave_date_id'] = '0';

									if( $this->input->post('record_id') != "-1" ){
										$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
										$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
										$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
										$leave_date_result = $this->db->get('employee_leaves');

										if( $leave_date_result->num_rows() > 0 ){
											$leave_date_record = $leave_date_result->row();
											$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
										}
										else{
											$days[$days_ctr]['duration_id'] = 1;
										}
									}
									else{
										$days[$days_ctr]['duration_id'] = 1;
									}

									// for firstbalfour 
									if ($worksched->considered_halfday){
										$days[$days_ctr]['considered_restday'] = 1;	
										$days[$days_ctr]['duration_id'] = 4;
									}

									$days_ctr++;
								}
								break;
							case 3:
								if( !empty( $worksched->wednesday_shift_id ) && $worksched->wednesday_shift_id != 1 ){
									$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
									$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
									$days[$days_ctr]['employee_leave_date_id'] = '0';

									if( $this->input->post('record_id') != "-1" ){
										$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
										$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
										$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
										$leave_date_result = $this->db->get('employee_leaves');

										if( $leave_date_result->num_rows() > 0 ){
											$leave_date_record = $leave_date_result->row();
											$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
										}
										else{
											$days[$days_ctr]['duration_id'] = 1;
										}
									}
									else{
										$days[$days_ctr]['duration_id'] = 1;
									}

									// for firstbalfour 
									if ($worksched->considered_halfday){
										$days[$days_ctr]['considered_restday'] = 1;	
										$days[$days_ctr]['duration_id'] = 4;
									}

									$days_ctr++;

								}
								break;
							case 4:
								if( !empty( $worksched->thursday_shift_id ) && $worksched->thursday_shift_id != 1 ){
									$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
									$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
									$days[$days_ctr]['employee_leave_date_id'] = '0';

									if( $this->input->post('record_id') != "-1" ){
										$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
										$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
										$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
										$leave_date_result = $this->db->get('employee_leaves');

										if( $leave_date_result->num_rows() > 0 ){
											$leave_date_record = $leave_date_result->row();
											$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
										}
										else{
											$days[$days_ctr]['duration_id'] = 1;
										}
									}
									else{
										$days[$days_ctr]['duration_id'] = 1;
									}

									// for firstbalfour 
									if ($worksched->considered_halfday){
										$days[$days_ctr]['considered_restday'] = 1;	
										$days[$days_ctr]['duration_id'] = 4;
									}

									$days_ctr++;									

								}
								break;
							case 5:
								if( !empty( $worksched->friday_shift_id ) && $worksched->friday_shift_id != 1 ){
									$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
									$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
									$days[$days_ctr]['employee_leave_date_id'] = '0';

									if( $this->input->post('record_id') != "-1" ){
										$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
										$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
										$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
										$leave_date_result = $this->db->get('employee_leaves');

										if( $leave_date_result->num_rows() > 0 ){
											$leave_date_record = $leave_date_result->row();
											$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
										}
										else{
											$days[$days_ctr]['duration_id'] = 1;
										}
									}
									else{
										$days[$days_ctr]['duration_id'] = 1;
									}

									// for firstbalfour 
									if ($worksched->considered_halfday){
										$days[$days_ctr]['considered_restday'] = 1;	
										$days[$days_ctr]['duration_id'] = 4;
									}

									$days_ctr++;

								}
								break;
							case 6:
								if( !empty( $worksched->saturday_shift_id ) && $worksched->saturday_shift_id != 1 ){
									$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
									$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
									$days[$days_ctr]['employee_leave_date_id'] = '0';

									if( $this->input->post('record_id') != "-1" ){
										$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
										$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
										$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
										$leave_date_result = $this->db->get('employee_leaves');

										if( $leave_date_result->num_rows() > 0 ){
											$leave_date_record = $leave_date_result->row();
											$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
										}
										else{
											$days[$days_ctr]['duration_id'] = 1;
										}
									}
									else{
										$days[$days_ctr]['duration_id'] = 1;
									}

									// for firstbalfour 
									if ($worksched->considered_halfday){
										$days[$days_ctr]['considered_restday'] = 1;	
										$days[$days_ctr]['duration_id'] = 4;
									}

									$days_ctr++;

								}
								break;
							case 7:
								if( !empty( $worksched->sunday_shift_id ) && $worksched->sunday_shift_id != 1 ){
									$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
									$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
									$days[$days_ctr]['employee_leave_date_id'] = '0';

									if( $this->input->post('record_id') != "-1" ){
										$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
										$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
										$this->db->where('employee_leaves.employee_leave_id',$this->input->post('record_id'));
										$leave_date_result = $this->db->get('employee_leaves');

										if( $leave_date_result->num_rows() > 0 ){
											$leave_date_record = $leave_date_result->row();
											$days[$days_ctr]['duration_id'] = $leave_date_record->duration_id;
										}
										else{
											$days[$days_ctr]['duration_id'] = 1;
										}
									}
									else{
										$days[$days_ctr]['duration_id'] = 1;
									}

									// for firstbalfour 
									if ($worksched->considered_halfday){
										$days[$days_ctr]['considered_restday'] = 1;	
										$days[$days_ctr]['duration_id'] = 4;
									}

									$days_ctr++;
									
								}
							break;	
						}

						
					}
				}

				$start_date = date('Y-m-d', strtotime($start_date . ' +1 day') );

			}

			$dur = $this->db->get_where('employee_leaves_duration', array('deleted' => 0));
			if( $dur->num_rows() > 0 ){
				$response['duration'] = '<select name="duration_id[]" class="duration">';
				foreach($dur->result() as $row){
					$response['duration'] .= '<option value="'.$row->duration_id.'">'.$row->duration.'</option>';
				}
				$response['duration'] .= '</select>';
			}
			$response['dates'] = $days;
			$response['type'] = 'success';
			$response['client_no'] = $this->config->item('client_no');

			if($call_from_within)  return $response;
			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function _get_employee_info(){
		$this->db->join('employee','user.employee_id = employee.employee_id','left');
		$this->db->where('user.employee_id', $this->input->post('employee_id'));
		$this->db->where('user.deleted', 0);

		$userinfo = $this->db->get('user');

		if ($userinfo->num_rows() > 0) {
			$response = $userinfo->row();
		} else {
			$response = false;
		}

		if (IS_AJAX) {
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			return $response;
		}
	}

	function _get_user_info(){
		$this->db->where('employee_id', $this->input->post('employee_id'));
		$this->db->where('deleted', 0);

		$userinfo = $this->db->get('user');

		if ($userinfo->num_rows() > 0) {
			$response = $userinfo->row();
		} else {
			$response = false;
		}

		if (IS_AJAX) {
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			return $response;
		}
	}

	function _get_leave_balance(){
		// to get employee_id on detailview
		if ($this->input->post('record_id') > 0 && $this->input->post('record_id'))
			$employee_id = $this->db->get_where('employee_leaves', array('employee_leave_id' => $this->input->post('record_id')))->row()->employee_id;
		elseif ($this->input->post('employee_id') != '')
			$employee_id = $this->input->post('employee_id');
		else
			$employee_id = $this->userinfo['user_id'];

		$this->db->where('employee_id', $employee_id);
		$this->db->where('year', date('Y'));
		$this->db->where('deleted', 0);

		$balance = $this->db->get('employee_leave_balance');

		if ($balance->num_rows() > 0) {
			$response = $balance->row();

			$response->show_carried = ($this->config->item('show_with_carried') == 1 ? 2 : 1);

			$response->client_no = $this->config->item('client_no');

			// Check tenure
    		$year = date('Y');
    		$today = new DateTime( date('Y-m-d') );
    		$employee = $this->db->get_where('employee', array('employee_id' => $employee_id))->row();
    		$user = $this->db->get_where('user', array('employee_id' => $employee_id))->row();
    		$hired = new DateTime( $employee->employed_date);
    		$interval = $today->diff($hired);

    		$response->sex = $user->sex;

			$leave_tenure = $this->db->get_where('employee_type_leave_setup', array("employee_type_id" => $employee->employee_type, "deleted" => 0, "tenure <>" => 0));

			if($leave_tenure->num_rows() > 0)
				$vl_tenure = $leave_tenure->row()->tenure;
			else
				$vl_tenure = 0;

			$compared_date = $this->hdicore->compare_date($employee->employed_date, date('Y-m-d'));

			$employee = $this->system->get_employee($employee_id);		
    		if ($employee['employee_type'] >= 2 && $compared_date->difference_months < $vl_tenure) {
    			$response->sl = 0;
    			$response->el = 0;
    		}

    		if ($employee['employee_type'] == 1 && $employee['status_id'] != 1) {
    			$response->sl = 0;
    			$response->el = 0;
    		}

    		if ($this->config->item('vl_for_regular') && $employee['employee_type'] == 1 && $employee['status_id'] != 1) {
    			$response->vl = 0;
    		}
    		
		} else {
			$response = false;
		}

		if (IS_AJAX) {
			$this->load->view('template/ajax', array('json' => $response));
		} else {
			return $response;
		}
	}

	function _get_ml_specifics(){
		$date = date('Y-m-d',strtotime($this->input->post('date')));
		$sched = $this->system->get_employee_worksched($this->input->post('employee_id'), $date );

		$response['success'] = false;

		if ($sched) {
			$ndate = date('Y-m-d', strtotime('+1 day', strtotime($this->input->post('date'))));
			$day = strtolower(date('l', strtotime($ndate)));			
			
			//check if employee filed a change work schedule
			if(array_key_exists('shift_id', $sched)){
				$result = $this->db->get_where('employee_dtr_setup', array('employee_id' => $this->input->post('employee_id'), 'deleted' => 0));
				$sched = $result->row();
				$sched = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $sched->shift_calendar_id))->row();
			}

			while ($sched->{$day . '_shift_id'} == 0) {			
				$ndate = date('Y-m-d', strtotime('+1 day', strtotime($ndate)));
				$day   = strtolower(date('l', strtotime($ndate)));	
			}

			$response['success'] = true;
			$response['date'] = $ndate;
		}

		// Get number of pregnancies = number of children set in 201
		$this->db->where('employee_id', $this->input->post('employee_id'));
		$this->db->where('relationship', 'Child');
		$this->db->where('deleted', 0);

		$children = $this->db->get('employee_family')->num_rows();

		$response['pregnancies'] = $children + 1;

		$this->load->view('template/ajax', array('json' => $response));
	}

	function _get_actual_delivery_date(){
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$flag=0;
			$this->db->where('employee_id',$this->input->post('employee_id'));
			$this->db->where('application_form_id',6);
			$this->db->where('form_status_id',3);
			$this->db->where('deleted',0);
			$this->db->order_by('date_to','desc');
			$paternity_id=$this->db->get('employee_leaves')->row_array();

			if(count($paternity_id)==0)
				$flag=1;
			else
			{
				$this->db->where('employee_leave_id',$paternity_id['employee_leave_id']);
				$dp=$this->db->get('employee_leaves_paternity')->row_array();

				$date_possible=date('Y-m-d',strtotime('+60 days', strtotime($dp['actual_date_delivery'])));
				$this->db->where('employee_leave_id',$paternity_id['employee_leave_id']);
				$this->db->where('actual_date_delivery <=',$date_possible);
				$dd=$this->db->get('employee_leaves_paternity');

				$dateToday=date('Y-m-d');
				if( $dateToday >= $date_possible )
				{
					$flag=1;
				}
			}
			if ($flag==1) {
				$response->msg_type = 'new_record';
				$response->msg 		= '';
			} else {
				$response->msg_type = 'success';

				$response->data = $dd->row_array();
			}			
			$this->load->view('template/ajax', array('json' => $response));
		}
	}

	function _change_status($record_id = 0, $non_ajax = 0){

		if ( $this->input->post('record_id') && $non_ajax == 0 ) {
			$record_id = $this->input->post('record_id');
		}

		if( $this->module_table == '' ) $this->module_table = 'employee_leaves' ;
		if( $this->key_field == '' ) $this->key_field = 'employee_leave_id' ;

		$this->db->where($this->key_field, $record_id);
		$result = $this->db->get($this->module_table);
		$request = $result->row_array();

		$form_status_id = $this->input->post('form_status_id');
		$this->load->helper('date');
		$to_check = true;		
		$approver = $this->db->get_where('leave_approver', array('approver' => $this->user->user_id, 'leave_id' => $record_id));

		// Check if current user is part of approvers.
		if ( IS_AJAX && $approver->num_rows() > 0 || ($this->user_access[$this->module_id]['post'] == 1 && CLIENT_DIR == 'firstbalfour')) {

			if ($this->input->post('form_status_id') == 3){
	            //next cutoff validation -tirso              
/*	            if (date('Y-m-d') > date('Y-m-d',strtotime($request['date_from']))):
		            if ($this->system->check_in_cutoff($request['date_from']) == 1):
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Next payroll cutoff not yet created in processing, please contact HRA.';                              				
						$response['type'] = 'error';
						$data['json'] = $response;
		            elseif ($this->system->check_in_cutoff($request['date_from']) == 2):
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Sorry, your approval can no longer be processed. It exceeded the grace period';                              				
						$response['type'] = 'error';
						$data['json'] = $response;
		            endif;
	            endif; */

	            if ($this->system->check_cutoff_policy_ls($request['employee_id'],$this->input->post('form_status_id'),$request['application_form_id'],$request['date_from'],$request['date_to']) == 1){
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Next payroll cutoff not yet created in processing, please contact HRA.';                              				
						$response['type'] = 'error';
						$data['json'] = $response;
				}
	            elseif ($this->system->check_cutoff_policy_ls($request['employee_id'],$this->input->post('form_status_id'),$request['application_form_id'],$request['date_from'],$request['date_to']) == 2){
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Sorry, your approval can no longer be processed. It exceeded the grace period';                              				
						$response['type'] = 'error';
						$data['json'] = $response;	            	
	            }

	            // checking same application applied
				$this->db->where('employee_leaves.'.$this->key_field, $record_id);
	        	$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id');		        			        			        	
	        	$this->db->join('employee_form_type','employee_leaves.application_form_id = employee_form_type.application_form_id');
	        	$this->db->join('employee_leaves_duration','employee_leaves_dates.duration_id = employee_leaves_duration.duration_id');		        					
				$result = $this->db->get($this->module_table);

				if ($result->num_rows() > 0){
					foreach ($result->result() as $row){
			        	$this->db->where('form_status_id', 3);	
			        	$this->db->where('employee_leaves_dates.duration_id', $row->duration_id);	
			        	$this->db->where('employee_id', $row->employee_id);
			        	$this->db->where('employee_leaves.deleted', 0);
			        	$this->db->where('employee_leaves_dates.date', date('Y-m-d',strtotime($row->date)));
			        	$this->db->join('employee_leaves','employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id');		        			        			        	
			        	$this->db->join('employee_form_type','employee_leaves.application_form_id = employee_form_type.application_form_id');
			        	$this->db->join('employee_leaves_duration','employee_leaves_dates.duration_id = employee_leaves_duration.duration_id');		        	
			        	$result = $this->db->get('employee_leaves_dates');	
						if ($result->num_rows() > 0){						
							$row = $result->row();	
							$validate_to_approve[] = 'Application for ' . $row->date . ' as ' . $row->application_form . ' has already been approved.';
						}
					}
				}
				
	    		if (isset($validate_to_approve) && sizeof($validate_to_approve) > 0){
	                $to_check = false;                                   	
					$response['record_id'] = $this->input->post('record_id'); 
					$response['message'] = implode('<br />', $validate_to_approve);					
					$response['type'] = 'error';
					$data['json'] = $response;   		
	    		}
	    	} elseif ($this->input->post('form_status_id') == 4) {
/*	            if (date('Y-m-d') > date('Y-m-d',strtotime($request['date_from']))):
		            if ($this->system->check_in_cutoff($request['date_from']) == 1):
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Next payroll cutoff not yet created in processing, please contact HRA.';                              				
						$response['type'] = 'error';
						$data['json'] = $response;
		            elseif ($this->system->check_in_cutoff($request['date_from']) == 2):
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Sorry, your disapproval can no longer be processed. It exceeded the grace period.';                              				
						$response['type'] = 'error';
						$data['json'] = $response;
		            endif;
	            endif; */
	            if ($this->system->check_cutoff_policy_ls($request['employee_id'],$this->input->post('form_status_id'),$request['application_form_id'],$request['date_from'],$request['date_to']) == 1){
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Next payroll cutoff not yet created in processing, please contact HRA.';                              				
						$response['type'] = 'error';
						$data['json'] = $response;
				}
	            elseif ($this->system->check_cutoff_policy_ls($request['employee_id'],$this->input->post('form_status_id'),$request['application_form_id'],$request['date_from'],$request['date_to']) == 2){
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Sorry, your disapproval can no longer be processed. It exceeded the grace period';                              				
						$response['type'] = 'error';
						$data['json'] = $response;	            	
	            }	            
	    	}


			if ($to_check){				
				$approver = $approver->row();

				switch( $this->input->post('form_status_id') ){
					case 3:
						$returnstatus = 'approved';
						break;
					case 4: 
						$returnstatus = 'disapproved';
						break;
					case 5: 
						$returnstatus = 'cancelled';
						break;
					case 6: 
						$returnstatus = 'for HR validation';
						break;
					case 7: 
						$returnstatus = 'fit to work';
						break;			
				}

				$response['message'] = 'Request ' . $returnstatus;
				$this->db->update('leave_approver', array('status' => $this->input->post('form_status_id')), array('approver' => $this->user->user_id, 'leave_id' => $record_id)); 
				
				if (CLIENT_DIR == 'firstbalfour') {
					$app_form = $this->db->get_where('employee_leaves', array('employee_leave_id' => $record_id))->row();
		    		$employee = $this->db->get_where('employee', array('employee_id' => $app_form->employee_id))->row();

		    		$this->db->where('FIND_IN_SET('.$employee->status_id.', employment_status_id)');
		        	$this->db->where('employee_type_id', $employee->employee_type);
		        	$this->db->where('application_form_id', $app_form->application_form_id);
		        	$this->db->where('deleted',0);
		        	$leave_reset_setup = $this->db->get('employee_type_leave_setup');
		        		     	
				}

				switch( $this->input->post('form_status_id') ){
					case 3:
						$deduct_leaves = false;
						if (CLIENT_DIR == 'firstbalfour' && $this->user_access[$this->module_id]['post'] == 1){
	                        // commented to just display approver's name who approved the form
	                        // $this->db->update('leave_approver', array('status' => $this->input->post('form_status_id')), array('leave_id' => $record_id)); 
	                        $this->db->update('leave_approver', array('status' => $this->input->post('form_status_id')), array('approver' => $this->user->user_id, 'leave_id' => $record_id));
							$deduct_leaves = true;							
						}
						else{
							switch( $approver->condition ){
								case 1: //by level
									//get next approver
									$next_approver = $this->db->get_where('leave_approver', array('sequence' => ($approver->sequence+1), 'leave_id' => $record_id));
									if( $next_approver->num_rows() == 1 ){
										$next_approver = $next_approver->row();
										$this->db->update('leave_approver', array('focus' => 1, 'status' => 2), array('sequence' => $next_approver->sequence, 'leave_id' => $record_id));

										//email next approver
										$this->_send_email();
									}
									else{
										//this is last approver
										$deduct_leaves = true;
									}
									break;
								case 2: // Either
									$deduct_leaves = true;
									break;
								case 3: // All
									$qry = "SELECT * FROM {$this->db->dbprefix}leave_approver where leave_id = {$record_id} and status != 3";
									$all_approvers = $this->db->query( $qry );
									if( $all_approvers->num_rows() == 0 ){
										$deduct_leaves = true;
									}
									break;	
							}
						}

						// used for client openaccess. chill, just adjust the multi config to support other clients
						$select_qry = ' {dbtable}.vl AS old_vl,
										{dbtable}.sl AS old_sl,
										(IFNULL({dbtable}.vl, 0) + IFNULL({dbtable}.carried_vl, 0)) AS vl,
										(IFNULL({dbtable}.sl, 0) + IFNULL({dbtable}.carried_sl, 0)) AS sl,
										{dbtable}.leave_balance_id,
										{dbtable}.year,
										{dbtable}.employee_id,
										{dbtable}.carried_vl,
										{dbtable}.vl_used,
										{dbtable}.carried_sl,
										{dbtable}.sl_used,
										{dbtable}.el,
										{dbtable}.el_used,
										{dbtable}.mpl,
										{dbtable}.mpl_used,									
										{dbtable}.bl,
										{dbtable}.bl_used,
										{dbtable}.uneditable,
										{dbtable}.paid_sl,
										{dbtable}.deleted
										';
										
						if (CLIENT_DIR == 'firstbalfour'){
							$select_qry = ' {dbtable}.vl AS old_vl,
											{dbtable}.sl AS old_sl,
											(IFNULL({dbtable}.vl, 0) + IFNULL({dbtable}.carried_vl, 0)) AS vl,
											(IFNULL({dbtable}.sl, 0) + IFNULL({dbtable}.carried_sl, 0)) AS sl,
											{dbtable}.leave_balance_id,
											{dbtable}.year,
											{dbtable}.employee_id,
											{dbtable}.carried_vl,
											{dbtable}.vl_used,
											{dbtable}.carried_sl,
											{dbtable}.sl_used,
											{dbtable}.el,
											{dbtable}.el_used,
											{dbtable}.mpl,
											{dbtable}.mpl_used,
											{dbtable}.plsp,
											{dbtable}.plsp_used,										
											{dbtable}.bl,
											{dbtable}.bl_used,
											{dbtable}.fl,
											{dbtable}.fl_used,
											{dbtable}.wl,
											{dbtable}.wl_used,	
											{dbtable}.sil,
											{dbtable}.sil_used,																								
											{dbtable}.ul,
											{dbtable}.ul_used,																					
											{dbtable}.uneditable,
											{dbtable}.paid_sl,
											{dbtable}.deleted
											';							
						}

						$select_qry = str_replace('{dbtable}', $this->db->dbprefix('employee_leave_balance'), $select_qry);

						if( $deduct_leaves ){
							try {
								$error_catch = false;
								//update number of balance
								//get the affected days
								$days = $this->db->get_where('employee_leaves_dates', array($this->key_field => $record_id, 'deleted' => 0));

								if( $days->num_rows() > 0 ){
							
									foreach( $days->result() as $day ){
										$dateto = $day->date;
										
										if($this->config->item('filing_with_carried') == 1)
										{
											$this->db->select($select_qry, false);
											$day->date = date('Y-m-d');
										}
										
										$year_date = date('Y', strtotime($day->date));

										if (CLIENT_DIR == 'firstbalfour') {
											if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
								    			$leave_reset = $leave_reset_setup->row();
								    			// $dateto =  date('Y-m-d', strtotime($this->input->post('date_to')));

								    			if ($dateto <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
								    				$year_date = date('Y', strtotime($leave_reset->leave_reset_date))-1;

								    				// $app_year = $year_date;
								    				if (date('Y', strtotime($leave_reset->leave_reset_date)) == date('Y')) {
								    					$year_date = date('Y');
								    				}
								    			}
								    			$day->date = $dateto;	
								    		}	
										}
										
										$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => $year_date, 'employee_id' => $request['employee_id'], 'deleted' => 0) );

										if( $emp_balance->num_rows() == 1 ){
											$emp_balance = $emp_balance->row();

											switch( $request['application_form_id'] ){
												case 1: //SL										
													if ($emp_balance->sl < $emp_balance->sl_used + $day->credit) {
														throw new Exception('Not enough leave credits.');
													}
													break;
												case 2: //VL
													if (($emp_balance->vl + $emp_balance->carried_vl) < $emp_balance->vl_used + $day->credit) {  
														throw new Exception('Not enough leave credits.');
													}										
													break;	
												case 3: //EL
													if (($emp_balance->vl + $emp_balance->carried_vl) < $emp_balance->vl_used + $day->credit) {
														throw new Exception('Not enough leave credits.');
													}										
													break;
												case 4: //EL
													if (CLIENT_DIR == 'firstbalfour'){
														if ($emp_balance->fl < $emp_balance->fl_used + $day->credit) {
															throw new Exception('Not enough leave credits.');
														}	
													}
													else{
														if ($emp_balance->bl < $emp_balance->bl_used + $day->credit) {
															throw new Exception('Not enough leave credits.');
														}	
													}									
													break;
												// Removed, as discussed with marvin, ML credits should be 0.
												// case 5: //ML
												case 6: //PL
													if ($emp_balance->mpl < $emp_balance->mpl_used + $day->credit) {
														throw new Exception('Not enough leave credits.');
													}										
													break;
												// added for balfour
												case 13:
													if (CLIENT_DIR == "firstbalfour") {
														if ($emp_balance->bl < $emp_balance->bl_used + $day->credit) {
															throw new Exception('Not enough leave credits.');
														}
													}
													break;
												case 17: // PLSP
													if ($emp_balance->plsp < $emp_balance->plsp_used + $day->credit) {
														throw new Exception('Not enough leave credits.');
													}										
													break;
												case 18: // WL
													if ($emp_balance->wl < $emp_balance->wl_used + $day->credit) {
														throw new Exception('Not enough leave credits.');
													}										
													break;
/*												case 19: // VML
													if ($emp_balance->vml < $emp_balance->vml_used + $day->credit) {
														throw new Exception('Not enough leave credits.');
													}										
													break;*/
												case 20: // SIL
													if ($emp_balance->sil < $emp_balance->sil_used + $day->credit) {
														throw new Exception('Not enough leave credits.');
													}										
													break;
												case 21: // UL
													if ($emp_balance->ul < $emp_balance->ul_used + $day->credit) {
														throw new Exception('Not enough leave credits.');
													}										
													break;
											}							
										}
									}
									
									// Only update the record if validation is correct.
							
									foreach( $days->result() as $day ){			

										$date2 = $day->date;
										
										if($this->config->item('filing_with_carried') == 1)
										{
											$day->date = date('Y-m-d');
										}
										
										$year_date = date('Y', strtotime($day->date));

										if (CLIENT_DIR == 'firstbalfour') {
											if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
								    			$leave_reset = $leave_reset_setup->row();
								    			// $dateto =  date('Y-m-d', strtotime($this->input->post('date_to')));

								    			if ($date2 <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
								    				$year_date = date('Y', strtotime($leave_reset->leave_reset_date))-1;
								    				// $app_year = $year_date;
								    				if (date('Y', strtotime($leave_reset->leave_reset_date)) == date('Y')) {
								    					$year_date = date('Y');
								    				}
								    			}
								    				
								    		}	
										}
										
										$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => $year_date, 'employee_id' => $request['employee_id'], 'deleted' => 0) );
									
										if( $emp_balance->num_rows() == 1 ){

											$emp_balance = $emp_balance->row_array();

											switch( $request['application_form_id'] ){
												case 1: //SL
													$emp_balance['sl_used'] += $day->credit;
													break;
												case 2: //VL
													$emp_balance['vl_used'] += $day->credit;
													break;	
												case 3: //EL								
													$emp_balance['el_used'] += $day->credit;
													break;
												case 4: //EL	
													if (CLIENT_DIR == 'firstbalfour'){
														$emp_balance['fl_used'] += $day->credit;
													}	
													else{
														$emp_balance['bl_used'] += $day->credit;
													}						
													break;
												case 6: //PL								
													$emp_balance['mpl_used'] += $day->credit;
													break;
												// added for balfour
												case 13:
													if (CLIENT_DIR == "firstbalfour") {
														$emp_balance['bl_used'] += $day->credit;
													}
													break;
												case 17: // PLSP
													$emp_balance['plsp_used'] += $day->credit;
													break;
												case 18: // WL
													$emp_balance['wl_used'] += $day->credit;
													break;
												case 19: // VML
													$emp_balance['vml_used'] += $day->credit;
													break;
												case 20: // SIL
													$emp_balance['sil_used'] += $day->credit;
													break;
												case 21: // UL
													$emp_balance['ul_used'] += $day->credit;		
													break;
											}							
											$this->db->where('leave_balance_id', $emp_balance['leave_balance_id']);
											$this->db->update('employee_leave_balance', $emp_balance);		
																			
										}
									}
								}

								$data['form_status_id'] = 3;
								$data['date_approved'] = date('Y-m-d H:i:s', now());
								$this->db->where($this->key_field, $record_id);
								$this->db->update($this->module_table, $data);	
								$this->_send_status_email($record_id,3);	

							} catch (Exception $e) {
								$response['type'] = 'attention';
								$response['message'] = $e->getMessage();
								$this->db->update('leave_approver', array('status' => 2), array('approver' => $this->user->user_id, 'leave_id' => $record_id)); 
								$error_catch = true;
							}
						}
						break;
					case 4:
						$data['decline_remarks'] = $this->input->post('decline_remarks');
						$data['date_approved'] = date('Y-m-d H:i:s', now());
						$data['form_status_id'] = 4;
						$this->db->where($this->key_field, $record_id);
						$this->db->update($this->module_table, $data);
						$this->_send_status_email($record_id, 4, $this->input->post('decline_remarks'));	
						break;
					case 5:

						$data['date_approved'] = date('Y-m-d H:i:s', now());
						$data['form_status_id'] = 5;
						$this->db->where($this->key_field, $record_id);
						$this->db->update($this->module_table, $data);

						$this->_send_status_email($record_id,5);	

						//give credits back
						//get the affected days

						$days = $this->db->get_where('employee_leaves_dates', array($this->key_field => $record_id, 'deleted' => 0));
					
						if( $days->num_rows() > 0 ){
							foreach( $days->result() as $day ){
							$dateto = $day->date;

								if($this->config->item('filing_with_carried') == 1)
									$day->date = date('Y-m-d');

								$year_date = date('Y', strtotime($day->date));

								if (CLIENT_DIR == 'firstbalfour') {

									if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
						    			$leave_reset = $leave_reset_setup->row();

						    			if ($dateto <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
						    				$year_date = date('Y', strtotime($leave_reset->leave_reset_date))-1;

						    				if (date('Y', strtotime($leave_reset->leave_reset_date)) == date('Y')) {
								    			$year_date = date('Y');
								    		}

						    			}
						    				
						    		}	
								}
								
								$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => $year_date, 'employee_id' => $request['employee_id'], 'deleted' => 0) );

								if( $emp_balance->num_rows() == 1 ){

									$emp_balance = $emp_balance->row();

									switch( $request['application_form_id'] ){
										case 1: //SL
											$emp_balance->sl_used = $emp_balance->sl_used - $day->credit;
											break;
										case 2: //VL
											$emp_balance->vl_used = $emp_balance->vl_used - $day->credit;
											break;	
										case 3: //EL
											$emp_balance->el_used = $emp_balance->el_used - $day->credit;
											break;
										case 4: //EL
											if (CLIENT_DIR == 'firstbalfour'){
												$emp_balance->fl_used = $emp_balance->fl_used - $day->credit;
											}
											else{
												$emp_balance->bl_used = $emp_balance->bl_used - $day->credit;
											}
											break;
										case 6: //PL
											$emp_balance->mpl_used = $emp_balance->mpl_used - $day->credit;
											break;
										// added for balfour
										case 13:
											if (CLIENT_DIR == "firstbalfour") {
												$emp_balance->bl_used = $emp_balance->bl_used - $day->credit;
											}
											break;
										case 17: // PLSP
											$emp_balance->plsp_used = $emp_balance->plsp_used - $day->credit;
											break;
										case 18: // WL
											$emp_balance->wl_used = $emp_balance->wl_used - $day->credit;
											break;
										case 19: // VML
											$emp_balance->vml_used = $emp_balance->vml_used - $day->credit;
											break;
										case 20: // SIL
											$emp_balance->sil_used = $emp_balance->sil_used - $day->credit;
											break;
										case 21: // UL
											$emp_balance->ul_used = $emp_balance->ul_used - $day->credit;
											break;
									}
									$this->db->update('employee_leave_balance', $emp_balance, array('leave_balance_id' => $emp_balance->leave_balance_id));
								}
							}
						}
						break;
					case 6:
						$data['date_approved'] = date('Y-m-d H:i:s', now());
						$data['form_status_id'] = 6;
						$this->db->where($this->key_field, $record_id);
						$this->db->update($this->module_table, $data);
						break;
					case 7:
						$data['form_status_id'] = 7;
						$this->db->where($this->key_field, $record_id);
						$this->db->update($this->module_table, $data);
						$this->db->update('leave_approver', array('status' => 2), array('leave_id' => $record_id));
						break;							
				}

				if(!$error_catch)
				{
					$response['record_id'] = $this->input->post('record_id'); 
					$response['type'] = 'success';
					$data['json'] = $response;
				} else {
					$response['record_id'] = $this->input->post('record_id'); 
					$data['json'] = $response;
				}
			}

			if( $non_ajax == 0 ){
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
			else{
				return $data;
			}

		}
		else if( IS_AJAX && $this->user_access[$this->module_id]['post'] == 1 && $form_status_id == 5 ){
			if ($this->input->post('form_status_id') == 5){

				$this->db->where($this->key_field, $this->input->post('record_id'));
				$leave_res = $this->db->get($this->module_table, $data)->row();

	            //next cutoff validation -tirso                 
	            if (date('Y-m-d') > date('Y-m-d',strtotime($request['date_from']))):
		            if ($this->system->check_in_cutoff($request['date_from']) == 1):
	                    $to_check = false;                                   	
						$response['record_id'] = $this->input->post('record_id'); 
						$response['message'] = 'Next payroll cutoff not yet created in processing, please contact HRA.';                              				
						$response['type'] = 'error';
						$data['json'] = $response;
		            elseif ($this->system->check_in_cutoff($request['date_from']) == 2):
		            	if (CLIENT_DIR == 'pioneer' && $leave_res->form_status_id == 6):
		            		if ($this->user_access[$this->module_id]['post'] && $this->user_access[$this->module_id]['hr_health']):
		            			$data['decline_remarks'] = 'Cancelled by HR Admin';
		            		endif;
		                else:
		                    $to_check = false;                                   	
							$response['record_id'] = $this->input->post('record_id'); 
							$response['message'] = 'You can not cancel leave exceeded with the next payroll cutoff.<br/>Please call the Administrator.';                              				
							$response['type'] = 'error';
							$data['json'] = $response;
						endif;
		            endif;
		        endif; 

		       
					
	            
	        }

	        if ($to_check){
				$data['date_approved'] = date('Y-m-d H:i:s', now());
				$data['form_status_id'] = 5;
				$this->db->where($this->key_field, $record_id);
				$this->db->update($this->module_table, $data);
				$this->_send_status_email($record_id,5);	

				//give credits back
				//get the affected days
				$days = $this->db->get_where('employee_leaves_dates', array($this->key_field => $record_id, 'deleted' => 0));
				if( $days->num_rows() > 0 ){
					foreach( $days->result() as $day ){
						
						if($this->config->item('filing_with_carried') == 1)
							$day->date = date('Y-m-d');

						$emp_balance = $this->db->get_where('employee_leave_balance', array('year' => date('Y', strtotime($day->date)), 'employee_id' => $request['employee_id'], 'deleted' => 0) );
						if( $emp_balance->num_rows() == 1 ){
							$emp_balance = $emp_balance->row();
							switch( $request['application_form_id'] ){
								case 1: //SL
									$emp_balance->sl_used = $emp_balance->sl_used - $day->credit;
									break;
								case 2: //VL
									$emp_balance->vl_used = $emp_balance->vl_used - $day->credit;
									break;	
								case 3: //EL
									$emp_balance->el_used = $emp_balance->el_used - $day->credit;
									break;
								case 4: //EL
									if (CLIENT_DIR == 'firstbalfour'){
										$emp_balance->fl_used = $emp_balance->fl_used - $day->credit;
									}
									else{
										$emp_balance->bl_used = $emp_balance->bl_used - $day->credit;
									}
									break;
								case 6: //PL
									$emp_balance->mpl_used = $emp_balance->mpl_used - $day->credit;
									break;
								// added for balfour
								case 13:
									if (CLIENT_DIR == "firstbalfour") {
										$emp_balance->bl_used = $emp_balance->bl_used - $day->credit;
									}
									break;
								case 17: // PLSP
									$emp_balance->plsp_used = $emp_balance->plsp_used - $day->credit;
									break;
								case 18: // WL
									$emp_balance->wl_used = $emp_balance->wl_used - $day->credit;
									break;
								case 19: // VML
									$emp_balance->vml_used = $emp_balance->vml_used - $day->credit;
									break;
								case 20: // SIL
									$emp_balance->sil_used = $emp_balance->sil_used - $day->credit;
									break;
								case 21: // UL
									$emp_balance->ul_used = $emp_balance->ul_used - $day->credit;
									break;
							}
							$this->db->update('employee_leave_balance', $emp_balance, array('leave_balance_id' => $emp_balance->leave_balance_id));
						}
					}
				}
				$response['message'] = 'Request cancelled';
				$response['record_id'] = $this->input->post('record_id'); 
				$response['type'] = 'success';
				$data['json'] = $response;
			}

			if( $non_ajax == 0 ){
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
			else{
				return $data;
			}

		}else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function _send_status_email( $record_id, $status_id, $decline_remarks = false){
		$profile_result = $this->db->get('profile')->result();

		$app_form = $this->db->get_where('employee_leaves', array('employee_leave_id' => $this->input->post('record_id')))->row();
		$app_form_id = $app_form->application_form_id;
		$app_employee_id = $app_form->employee_id;

		$app_year = 'YEAR('.$this->db->dbprefix.'employee_leaves.date_to)';

    	if (CLIENT_DIR == 'firstbalfour') {
    		$employee = $this->db->get_where('employee', array('employee_id' => $app_employee_id))->row();
    		$this->db->where('FIND_IN_SET('.$employee->status_id.', employment_status_id)');
        	$this->db->where('employee_type_id', $employee->employee_type);
        	$this->db->where('application_form_id', $app_form_id);
        	$this->db->where('deleted',0);
        	$leave_reset_setup = $this->db->get('employee_type_leave_setup');

    		if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
    			$leave_reset = $leave_reset_setup->row();
    			$dateto =  $app_form->date_to;

    			if ($dateto <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
    				$year_date = date('Y', strtotime($leave_reset->leave_reset_date))-1;
    				$app_year = $year_date;
    			}
    				
    		}
    	}

    	
    	$this->db->select('*,employee_leaves.date_created AS leaves_date_created');
		$this->db->join('user','user.employee_id=employee_leaves.employee_id', 'left');
		$this->db->join('employee','employee.employee_id=user.employee_id', 'left');
		$this->db->join('employee_leave_balance','employee_leave_balance.employee_id=employee_leaves.employee_id', 'left');
		$this->db->join('form_status','form_status.form_status_id=employee_leaves.form_status_id', 'left');
		$this->db->join('employee_form_type','employee_form_type.application_form_id=employee_leaves.application_form_id', 'left');
		$this->db->join('user_company','user_company.company_id=user.company_id', 'left');
		$this->db->where('employee_leaves.employee_leave_id', $record_id);
		// $this->db->where( $this->db->dbprefix.'employee_leave_balance.year = '.$app_year.'');
		$this->db->where( $this->db->dbprefix.'employee_leave_balance.year = '.date('Y').'');

		$request = $this->db->get('employee_leaves');
		
        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {

             $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();
                $request['reason'] = 'Reason: '.$request['reason'];
                // get reason for el
				if($request['application_form_id'] == 3)
				{
					$this->db->select('employee_leaves_rt.reason_type,calamity_remarks');
					$this->db->join('employee_leaves_rt', 'employee_leaves_el.reason_type_id = employee_leaves_rt.reason_type_id', 'left');
					$el = $this->db->get_where('employee_leaves_el', array("employee_leaves_el.employee_leave_id" => $this->input->post('record_id')));
					if($el && $el->num_rows() > 0)
					{
						$el_reason = $el->row();
						$request['reason'] = 'Reason: '.$el_reason->reason_type.' <br/>Remarks:'.$el_reason->calamity_remarks;
					}
				}

                $this->db->where('employee_id',$request['employee_id']);
                $this->db->where('form_status_id =','2');
                $arr=$this->db->get('employee_leaves');
                //echo count($arr);
                if($this->config->item('show_with_carried') == 0)
                {
	                if(count($arr)>0)
	                {
	                    $vl_pen=0;
	                    $sl_pen=0;
	                    $el_pen=0;
	                    $mpl_pen=0;
	                    $bl_pen=0;
	                    $arr=$arr->result_array();
	                    foreach($arr as $key_field=>$key_field_val)
	                    {
	                        if($key_field_val['application_form_id']==1)
	                            $vl_pen++;
	                        if($key_field_val['application_form_id']==2) 
	                            $sl_pen++;
	                        if($key_field_val['application_form_id']==3)
	                            $el_pen++;
	                        if($key_field_val['application_form_id']==4)
	                            $mpl_pen++;
	                        if($key_field_val['application_form_id']==5 || $key_field_val['application_form_id']==6)                
	                            $bl_pen++;
	                    }
	                    $request['vl_pen']=$vl_pen;
	                    $request['sl_pen']=$sl_pen;
	                    $request['el_pen']=$el_pen;
	                    $request['mpl_pen']=$mpl_pen;
	                    $request['bl_pen']=$bl_pen;
	                }
	                else
	                {
	                    $request['vl_pen']='0';
	                    $request['sl_pen']='0';
	                    $request['el_pen']='0';
	                    $request['mpl_pen']='0';
	                    $request['bl_pen']='0';
	                }
	                $total_vl_used = $request['vl_used']+$request['el_used'];
	                $request['vl_bal']=number_format($request['vl'] - $total_vl_used,2,'.',',');
	                $request['sl_bal']=number_format($request['sl'] - $request['sl_used'],2,'.',',');
	                $request['el_bal']=number_format($request['el'] - $request['el_used'],2,'.',',');
	                $request['mpl_bal']=number_format($request['mpl'] - $request['mpl_used'],2,'.',',');
	                $request['bl_bal']=number_format($request['bl'] - $request['bl_used'],2,'.',',');
	            } elseif($this->config->item('show_with_carried') == 1) {
	            	if (CLIENT_DIR == 'firstbalfour'){
	            		$request['prev_vl'] = number_format($request['carried_vl'],2,'.',',');
	            		$request['vl_used'] = number_format($request['vl_used'],2,'.',',');
	            		$total_vl_used = $request['vl_used']+$request['el_used'];
	            		$request['vl_bal'] = number_format(($request['vl'] + $request['carried_vl']) - $total_vl_used,2,'.',',');

	            		$request['prev_sl'] = number_format($request['carried_sl'],2,'.',',');
	            		$request['sl_used'] = number_format($request['sl_used'],2,'.',',');
	            		$request['sl_bal'] = number_format(($request['sl'] + $request['carried_sl']) - $request['sl_used'],2,'.',',');

	            		$request['prev_bl'] = number_format($request['carried_bl'],2,'.',',');
	            		$request['bl_used'] = number_format($request['bl_used'],2,'.',',');
	            		$request['bl_bal'] = number_format(($request['bl'] + $request['carried_bl']) - $request['bl_used'],2,'.',',');	
	            	}
	            	else{
	                    if($request['vl'] < $request['vl_used'])
	                    {
	                        $deduct_previous = $request['vl_used'] - $request['vl'];
	                        $request['vl_used'] = $request['vl'];
	                        $request['prev_vl'] = $request['carried_vl'] - $deduct_previous;
	                        $request['vl_bal'] = 0.00;
	                    } else {
	            			$total_vl_used = $request['vl_used']+$request['el_used'];
	                    	$request['vl_bal'] = number_format(($request['vl'] + $request['carried_vl']) - $total_vl_used,2,'.',',');
	                    	$request['prev_vl'] = $request['carried_vl'];
	                    }

	                    if($request['sl'] < $request['sl_used'])
	                    {
	                        $deduct_previous = $request['sl_used'] - $request['sl'];
	                        $request['sl_used'] = $request['sl'];
	                        $request['prev_sl'] = $request['carried_sl'] - $deduct_previous;
	                        $request['sl_bal'] = 0.00;
	                    } else {
	                    	$request['sl_bal'] = number_format(($request['sl'] + $request['carried_sl']) - $request['sl_used'],2,'.',',');
	                    	$request['prev_sl'] = $request['carried_sl'];
	                    }

	                    if($request['bl'] < $request['bl_used'])
	                    {
	                        $deduct_previous = $request['bl_used'] - $request['bl'];
	                        $request['bl_used'] = $request['bl'];
	                        $request['prev_bl'] = $request['carried_bl'] - $deduct_previous;
	                        $request['bl_bal'] = 0.00;
	                    } else {
	                    	$request['bl_bal'] = number_format(($request['bl'] + $request['carried_bl']) - $request['bl_used'],2,'.',',');
	                    	$request['prev_bl'] = $request['carried_bl'];
	                    }
	            	}

                    $request['prev_el'] = '0.00';
                    $request['prev_mpl'] = '0.00';
                    // add by tirso. because balance appear as variables
	                $request['el_bal']=number_format($request['el'] - $request['el_used'],2,'.',',');
	                $request['mpl_bal']=number_format($request['mpl'] - $request['mpl_used'],2,'.',','); 
	                $request['bl_bal']=number_format($request['bl'] - $request['bl_used'],2,'.',',');
	                $request['bol_bal']=number_format($request['bol'] - $request['bol_used'],2,'.',',');
	                $request['sil_bal']=number_format($request['sil'] - $request['sil_used'],2,'.',',');
	                $request['ul_bal']=number_format($request['ul'] - $request['ul_used'],2,'.',',');	                                     
	            }

	            if ($request['mpl'] == null || $request['mpl'] == ''){
	            	$request['mpl'] = '0.00';
	            }

	            if ($request['mpl_used'] == null || $request['mpl_used'] == ''){
	            	$request['mpl_used'] = '0.00';
	            }
	            	            
	            $request['year'] = date('Y');
                // $request['here']=base_url().'forms/leaves/detail/'.$request['employee_leave_id'];
                $request['here']=base_url();
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';
                $pieces=explode(" ",$request['leaves_date_created']);

                if (CLIENT_DIR == 'firstbalfour'){
                	$request['date_created']= date($this->config->item('display_date_format_email_fb'),strtotime($request['leaves_date_created']));
                }
                else{
                	$request['date_created']= date($this->config->item('display_date_format_email'),strtotime($request['leaves_date_created']));
                }

                $request['number_of_days']= floor((strtotime($request['date_to']) - strtotime($request['date_from'])) / (60 * 60 * 24)) + 1;
        	
				$request['number_of_days'] = 0;
                $leave_dates = $this->db->get_where('employee_leaves_dates', array('cancelled' => 0,'deleted' => 0, 'employee_leave_id' => $request['employee_leave_id']))->result();

                foreach( $leave_dates as $leave_date ){
                	$duration = $this->db->get_where('employee_leaves_duration', array('duration_id' => $leave_date->duration_id))->row();
                	$request['number_of_days'] += $duration->credit / 8;
            	}

            	$this->db->where("('".$request['date_to']."' >= date_from && '".$request['date_to']."' <= date_to)");
            	// $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
				$tkp = $this->db->get('timekeeping_period');

				if($tkp->num_rows() > 0 && $tkp)
					$request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
				else
					$request['payroll_cutoff'] = 'Cutoff not defined';

				if (CLIENT_DIR == 'firstbalfour'){
					$request['date_from']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_from']));
	                $request['date_to']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_to']));					
				}
				else{
					$request['date_from']= date($this->config->item('display_date_format_email'),strtotime($request['date_from']));
	                $request['date_to']= date($this->config->item('display_date_format_email'),strtotime($request['date_to']));
				}

				$ws = $this->system->get_employee_worksched_shift($request['employee_id'], date('Y-m-d'));  
				
				$request['shift_schedule'] = date('g:i',strtotime($ws->shifttime_start)) . "-" . date('g:i a',strtotime($ws->shifttime_end));

				$request['detail_cancel'] = '';
                switch($status_id){
                    case 3:
                        $request['status'] = "approved";
                    break;
                    case 4:
                        $request['status'] = "disapproved";
                    break;
                    case 5:
                        $request['status'] = "cancelled";
                        $employee_leave_id = $request['employee_leave_id'];
                        $leave_dates_detail = $this->db->get_where('employee_leaves_dates', array('deleted' => 0, 'employee_leave_id' => $request['employee_leave_id']))->result();
                        $dqry = $this->db->query("SELECT a.date, b.duration, a.employee_leave_date_id, a.date_cancelled, a.cancelled, a.remarks FROM {$this->db->dbprefix}employee_leaves_dates a 
										LEFT JOIN {$this->db->dbprefix}employee_leaves_duration b
										ON a.duration_id = b.duration_id
										WHERE a.employee_leave_id = '{$employee_leave_id}'")->result();
                        $html_detail = '<p>Inclusive Date/s Detail :</p>
                        				<table style="width:100%;border:1px solid gray;">
                        				<tr>
                        					<td style="width:20%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Date</td>
                        					<td style="width:15%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Duration</td>
                        					<td style="width:15%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Status</td>
                        					<td style="width:20%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Date Cancelled</td>
                        					<td style="width:30%;text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">Remarks</td>
                        				</tr>';
                        foreach ($dqry as $date_affected) {   
                            if($date_affected->cancelled == 0) {                         
                                $html_detail .= '<tr><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">'.date($this->config->item('display_date_format_email'),strtotime($date_affected->date)) . '</td><td colspan="4" style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">' . $date_affected->duration . '</td></tr>';
                            } else {
                                $html_detail .= '<tr><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">'.date($this->config->item('display_date_format_email'),strtotime($date_affected->date)) . '</td><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">' . $date_affected->duration . '</td><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;"><span class="red">Cancelled</span></td><td style="text-align:center;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;"><span class="blue"><i>Date : '.date($this->config->item('display_date_format_email'),strtotime($date_affected->date_cancelled)).'</i></span></td><td style="text-align:left;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;border-top:1px solid gray;">'.nl2br($date_affected->remarks).'</td></tr>';
                            }
                        } 
                        $html_detail .= '</table>';
                        $request['detail_cancel'] = $html_detail;
                    break;
                }

	            $cc_copy = '';

                // decline remarks
                if($decline_remarks)
                	$request['decline_remarks'] = $decline_remarks;
                else
                	$request['decline_remarks'] = '';

				if (CLIENT_DIR  == 'firstbalfour'){
					
					$request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname'];
					if ($request['aux'] != ''){
						$request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname']." ".$request['aux'];
					}

						$cc_copy = $this->system->get_approvers_and_condition($request['employee_id'], $this->module_id, 'email');
						if( ( is_array( $cc_copy  ) && sizeof($cc_copy) > 0 ) ){
							foreach( $cc_copy as $cc_user ){
								$result = $this->db->get_where('user',array('user_id'=> $cc_user['approver']));
								$result = $result->result_array();
						
								foreach ($result as $row) {
									//from reciepients
									$cc[] = $row['email'];
								}
							}
						}
		           		$cc_copy = '';
		                if(isset($cc)) $cc_copy = implode(',', $cc);	
		              					
	           	}

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'leave_status_email');
                $message = $this->template->prep_message($template['body'], $request);
                // $this->template->queue($request['email'], $cc_copy, $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
                $this->template->queue($request['email'], "", $template['subject']." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
            }

        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
	}

	function _send_email(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

       
		$profile_result = $this->db->get_where('profile', array('deleted' => 0))->result();
		$app_form = $this->db->get_where('employee_leaves', array('employee_leave_id' => $this->input->post('record_id')))->row();
		$app_form_id = $app_form->application_form_id;
		$app_employee_id = $app_form->employee_id;

		$app_year = 'YEAR('.$this->db->dbprefix.'employee_leaves.date_to)';

    	if (CLIENT_DIR == 'firstbalfour') {
    		$employee = $this->db->get_where('employee', array('employee_id' => $app_employee_id))->row();
    		$this->db->where('FIND_IN_SET('.$employee->status_id.', employment_status_id)');
        	$this->db->where('employee_type_id', $employee->employee_type);
        	$this->db->where('application_form_id', $app_form_id);
        	$this->db->where('deleted',0);
        	$leave_reset_setup = $this->db->get('employee_type_leave_setup');

    		if ($leave_reset_setup && $leave_reset_setup->num_rows() > 0 ) {
    			$leave_reset = $leave_reset_setup->row();
    			$dateto =  $app_form->date_to;

    			if ($dateto <= $leave_reset->leave_reset_date && $leave_reset->leave_reset_date != NULL ) {
    				$year_date = date('Y', strtotime($leave_reset->leave_reset_date))-1;
    				$app_year = $year_date;
    			}
    				
    		}
    	}
    	
		$this->db->select('*,employee_leaves.date_created AS leaves_date_created');
		$this->db->join('user','user.employee_id=employee_leaves.employee_id', 'left');
		$this->db->join('employee','employee.employee_id=user.employee_id', 'left');
		$this->db->join('employee_leave_balance','employee_leave_balance.employee_id=employee_leaves.employee_id', 'left');
		$this->db->join('form_status','form_status.form_status_id=employee_leaves.form_status_id', 'left');
		$this->db->join('employee_form_type','employee_form_type.application_form_id=employee_leaves.application_form_id', 'left');
		$this->db->join('user_company','user_company.company_id=user.company_id', 'left');
		$this->db->where('employee_leaves.employee_leave_id', $this->input->post('record_id'));
		if($app_form_id != 5)
			// $this->db->where($this->db->dbprefix.'employee_leave_balance.year = '.$app_year.' ');
			//change due to ticket #868. Leave balance display on email notification should be of current year
			$this->db->where($this->db->dbprefix.'employee_leave_balance.year = '.date('Y').' ');
		// else
			// $this->db->where('('. $this->db->dbprefix.'employee_leave_balance.year = YEAR('.$this->db->dbprefix.'employee_leaves.date_to ) OR '.$this->db->dbprefix.'employee_leave_balance.year = YEAR('.$this->db->dbprefix.'employee_leaves.date_from))');

		$this->db->order_by('employee_leave_balance.year', 'desc');
		$request = $this->db->get('employee_leaves');

		if ( $request->num_rows() > 0) {
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$request = $request->row_array();
				$request['reason'] = 'Reason: '.$request['reason'];
				// get reason for el
				if($request['application_form_id'] == 3)
				{
					$this->db->select('employee_leaves_rt.reason_type,calamity_remarks');
					$this->db->join('employee_leaves_rt', 'employee_leaves_el.reason_type_id = employee_leaves_rt.reason_type_id', 'left');
					$el = $this->db->get_where('employee_leaves_el', array("employee_leaves_el.employee_leave_id" => $this->input->post('record_id')));
					if($el && $el->num_rows() > 0)
					{
						$el_reason = $el->row();
						$request['reason'] = 'Reason: '.$el_reason->reason_type.' <br/>Remarks:'.$el_reason->calamity_remarks;
					}
				}

				$this->db->where('leave_id', $this->input->post('record_id'));
				$this->db->where('focus', 1);
				$this->db->order_by('sequence', 'desc');
				$approver_user = $this->db->get('leave_approver');				
				$leave_type = $request['application_form'];
				if($approver_user && $approver_user->num_rows() > 0 ){
					foreach ($approver_user->result() as $a) {
						switch($a->condition){
							case 1:
								if(!isset( $app_array) ) $app_array[] = $a->approver;
								break;
							case 2:
							case 3:
								$app_array[] = $a->approver;
								break;
						}
					}

					$this->db->where_in('user_id', $app_array);
					$result = $this->db->get('user');
					$result = $result->result_array();
					foreach ($result as $row) {
						if (CLIENT_DIR  == 'firstbalfour'){
                            $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname'];
                            if ($row['aux'] != ''){
                                $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname']." ".$row['aux'];
                            }                            							
						}
						else{
							$request['approver_user'] = $row['salutation']." ".$row['lastname'];
						}
					}
				}

				$this->db->where('employee_id',$request['employee_id']);
                $this->db->where('form_status_id =','2');
                $arr=$this->db->get('employee_leaves');
                //echo count($arr);
                if($this->config->item('show_with_carried') == 0)
                {
	                if(count($arr)>0)
	                {
	                    $vl_pen=0;
	                    $sl_pen=0;
	                    $el_pen=0;
	                    $mpl_pen=0;
	                    $bl_pen=0;
	                    $arr=$arr->result_array();
	                    foreach($arr as $key_field=>$key_field_val)
	                    {
	                        if($key_field_val['application_form_id']==1)
	                            $vl_pen++;
	                        if($key_field_val['application_form_id']==2) 
	                            $sl_pen++;
	                        if($key_field_val['application_form_id']==3)
	                            $el_pen++;
	                        if($key_field_val['application_form_id']==4)
	                            $mpl_pen++;
	                        if($key_field_val['application_form_id']==5 || $key_field_val['application_form_id']==6)                
	                            $bl_pen++;
	                    }
	                    $request['vl_pen']=$vl_pen;
	                    $request['sl_pen']=$sl_pen;
	                    $request['el_pen']=$el_pen;
	                    $request['mpl_pen']=$mpl_pen;
	                    $request['bl_pen']=$bl_pen;
	                }
	                else
	                {
	                    $request['vl_pen']='0';
	                    $request['sl_pen']='0';
	                    $request['el_pen']='0';
	                    $request['mpl_pen']='0';
	                    $request['bl_pen']='0';
	                }
	                $total_vl_used = $request['vl_used']+$request['el_used'];
	                $request['vl_bal']=number_format($request['vl'] - $total_vl_used,2,'.',',');
	                $request['sl_bal']=number_format($request['sl'] - $request['sl_used'],2,'.',',');
	                $request['el_bal']=number_format($request['el'] - $request['el_used'],2,'.',',');
	                $request['mpl_bal']=number_format($request['mpl'] - $request['mpl_used'],2,'.',',');
	                $request['bl_bal']=number_format($request['bl'] - $request['bl_used'],2,'.',',');
	                
                } elseif($this->config->item('show_with_carried') == 1) {
	            	if (CLIENT_DIR == 'firstbalfour'){
	            		$request['prev_vl'] = number_format($request['carried_vl'],2,'.',',');
	            		$request['vl_used'] = number_format($request['vl_used'],2,'.',',');	            		
	            		$total_vl_used = $request['vl_used']+$request['el_used'];
	            		$request['vl_bal'] = number_format(($request['vl'] + $request['carried_vl']) - $total_vl_used,2,'.',',');

	            		$request['prev_sl'] = number_format($request['carried_sl'],2,'.',',');
	            		$request['sl_used'] = number_format($request['sl_used'],2,'.',',');
	            		$request['sl_bal'] = number_format(($request['sl'] + $request['carried_sl']) - $request['sl_used'],2,'.',',');

	            		$request['prev_bl'] = number_format($request['carried_bl'],2,'.',',');
	            		$request['bl_used'] = number_format($request['bl_used'],2,'.',',');
	            		$request['bl_bal'] = number_format(($request['bl'] + $request['carried_bl']) - $request['bl_used'],2,'.',',');	
	            	}
	            	else{
	                    if($request['vl'] < $request['vl_used'])
	                    {
	                        $deduct_previous = $request['vl_used'] - $request['vl'];
	                        $request['vl_used'] = $request['vl'];
	                        $request['prev_vl'] = $request['carried_vl'] - $deduct_previous;
	                        $request['vl_bal'] = 0.00;
	                    } else {
		                    $total_vl_used = $request['vl_used']+$request['el_used'];
	                    	$request['vl_bal'] = number_format(($request['vl'] + $request['carried_vl']) - $total_vl_used,2,'.',',');
	                    	$request['prev_vl'] = $request['carried_vl'];
	                    }

	                    if($request['sl'] < $request['sl_used'])
	                    {
	                        $deduct_previous = $request['sl_used'] - $request['sl'];
	                        $request['sl_used'] = $request['sl'];
	                        $request['prev_sl'] = $request['carried_sl'] - $deduct_previous;
	                        $request['sl_bal'] = 0.00;
	                    } else {
	                    	$request['sl_bal'] = number_format(($request['sl'] + $request['carried_sl']) - $request['sl_used'],2,'.',',');
	                    	$request['prev_sl'] = $request['carried_sl'];
	                    }

	                    if($request['bl'] < $request['bl_used'])
	                    {
	                        $deduct_previous = $request['bl_used'] - $request['bl'];
	                        $request['bl_used'] = $request['bl'];
	                        $request['prev_bl'] = $request['carried_bl'] - $deduct_previous;
	                        $request['bl_bal'] = 0.00;
	                    } else {
	                    	$request['bl_bal'] = number_format(($request['bl'] + $request['carried_bl']) - $request['bl_used'],2,'.',',');
	                    	$request['prev_bl'] = $request['carried_bl'];
	                    }	            		
	            	}                	

                    $request['prev_el'] = '0.00';
                    $request['prev_mpl'] = '0.00';

                    // add by tirso. because balance appear as variables
	                $request['el_bal']=number_format($request['el'] - $request['el_used'],2,'.',',');
	                $request['mpl_bal']=number_format($request['mpl'] - $request['mpl_used'],2,'.',',');                     
	                $request['bl_bal']=number_format($request['bl'] - $request['bl_used'],2,'.',',');
	                $request['bol_bal']=number_format($request['bol'] - $request['bol_used'],2,'.',',');
	                $request['sil_bal']=number_format($request['sil'] - $request['sil_used'],2,'.',',');
	                $request['ul_bal']=number_format($request['ul'] - $request['ul_used'],2,'.',',');
	            }

	            if ($request['mpl'] == null || $request['mpl'] == ''){
	            	$request['mpl'] = '0.00';
	            }

	            if ($request['mpl_used'] == null || $request['mpl_used'] == ''){
	            	$request['mpl_used'] = '0.00';
	            }

                // $request['here']=base_url().'forms/leaves/detail/'.$request['employee_leave_id'];
                $request['here']=base_url();                
                $pieces = date('Y-m-d',strtotime($request['leaves_date_created']));

                if (CLIENT_DIR == 'firstbalfour'){
                	$request['date_created']= date($this->config->item('display_date_format_email_fb'),strtotime($request['leaves_date_created']));
                }
                else{
                	$request['date_created']= date($this->config->item('display_date_format_email'),strtotime($request['leaves_date_created']));
                }

                $request['number_of_days']= floor((strtotime($request['date_to']) - strtotime($request['date_from'])) / (60 * 60 * 24)) + 1;
        	
				$request['number_of_days'] = 0;
                $leave_dates = $this->db->get_where('employee_leaves_dates', array('deleted' => 0, 'employee_leave_id' => $request['employee_leave_id']))->result();

                foreach( $leave_dates as $leave_date ){
                	$duration = $this->db->get_where('employee_leaves_duration', array('duration_id' => $leave_date->duration_id))->row();
                	$request['number_of_days'] += $duration->credit / 8;
            	}

            	$this->db->where("('".$request['date_to']."' >= date_from && '".$request['date_to']."' <= date_to)");
            	// $this->db->where($request['date_to'].' BETWEEN date_from AND date_to');
				$tkp = $this->db->get('timekeeping_period');

				if($tkp->num_rows() > 0 && $tkp) {
					$request['cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->cutoff));
					$request['payroll_cutoff'] = date($this->config->item('display_date_format_email'), strtotime($tkp->row()->period_cutoff));
				} else {
					$request['cutoff'] = 'Cutoff not defined';
					$request['payroll_cutoff'] = 'Cutoff not defined';
				}


				$request['year'] = date('Y');
				if (CLIENT_DIR == 'firstbalfour'){
					$request['date_from']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_from']));
	                $request['date_to']= date($this->config->item('display_date_format_email_fb'),strtotime($request['date_to']));					
				}
				else{
					$request['date_from']= date($this->config->item('display_date_format_email'),strtotime($request['date_from']));
	                $request['date_to']= date($this->config->item('display_date_format_email'),strtotime($request['date_to']));
				}

				$ws = $this->system->get_employee_worksched_shift($request['employee_id'], date('Y-m-d'));  
				
				$request['shift_schedule'] = date('g:i',strtotime($ws->shifttime_start)) . "-" . date('g:i a',strtotime($ws->shifttime_end));
				
				// $request['url'] = base_url().'forms/leaves/detail/'.$this->input->post( 'record_id' );				
                $request['url'] = '<a href="'.base_url().'">'.base_url().'</a>';


				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template($this->module_id, 'new_leave_request');
				

				// Approvers.				
				if( is_array( $app_array ) && sizeof($app_array) > 0 ){

					$from = new DateTime( date('Y-m-d', strtotime($request['date_from'])) );
		        	$to = new DateTime( date('Y-m-d', strtotime($request['date_to'])) );
					$interval = $to->diff($from);

					if($app_form->form_status_id <= 2){
						$data['form_status_id'] = 2;
					}else{
						$data['form_status_id'] = $app_form->form_status_id;
					}

					if( $request['location_id'] == '13' ){

						if (($interval->d + 1) >= $this->config->item('mindays_sickleave_validation') && $request['application_form_id'] == 1) {
							if ($this->config->item('require_documents_sl_validation') && $request['documents'] == '' ) {
								$data['form_status_id'] = 6;
							}
						}

						if (CLIENT_DIR == "pioneer") {
							if ($request['application_form_id'] == 1 && ($request['number_of_days'] >= 3 || $request['number_of_days'] >= $this->config->item('mindays_sickleave_validation'))) {
								if (!$this->config->item('require_documents_sl_validation') && $request['documents'] == '' ) {
									$data['form_status_id'] = 6;
								}
								
							}
						}

						//redirect for hr health for validation when newly created
						if( $request['application_form_id'] == 14 && (  $request['form_status_id'] == 1 ) ){
							$data['form_status_id'] = 6;
						}

					}

					
					if (CLIENT_DIR == 'firstbalfour'){
						if (($request['application_form_id'] == 4 || $request['application_form_id'] == 5 || $request['application_form_id'] == 6 || $request['application_form_id'] == 14 || $request['application_form_id'] == 16 || $request['application_form_id'] == 17 || $request['application_form_id'] == 19 || $request['application_form_id'] == 20 || $request['application_form_id'] == 21) && $request['form_status_id'] == 6){
							$data['form_status_id'] = 6;
							$template_hr = $this->template->get_module_template($this->module_id, 'for_hr_validation');
							
							// return;
						}elseif ($data['form_status_id'] == 6) {
							$template_hr = $this->template->get_module_template($this->module_id, 'for_hr_validation');
						}
					}

					if( $data['form_status_id'] == 2 ){

						$this->db->where_in('user_id', $app_array);
					}
					else{

						//Search hr_health account
						foreach($profile_result as $profile_result_row){
							$module_access = unserialize( $profile_result_row->module_access );
							foreach ($module_access as $module_id => $access) {
								if( $module_id == $this->module_id ){
									if (CLIENT_DIR == 'firstbalfour') {
										if( $module_access[$this->module_id]['post'] == 1 && $module_access[$this->module_id]['hr_health'] == 1 && $profile_result_row->profile_id != 1 && $module_access[$this->module_id]['project_hr'] != 1 ){
											$profile_id_result[] = $profile_result_row->profile_id;
										}
										if( $module_access[$this->module_id]['post'] == 1 && $module_access[$this->module_id]['hr_health'] == 1 && $profile_result_row->profile_id != 1 && $module_access[$this->module_id]['project_hr'] == 1 ){
											$profile_project_hr[] = $profile_result_row->profile_id;
										}
									}else{
										if( $module_access[$this->module_id]['hr_health'] == 1 && $profile_result_row->profile_id != 1 ){
											$profile_id_result[] = $profile_result_row->profile_id;
										}
									}
								}
							}
						}

						foreach( $profile_id_result as $profile_id ){
							$qry = "SELECT *
							FROM {$this->db->dbprefix}role
							LEFT JOIN {$this->db->dbprefix}user ON {$this->db->dbprefix}user.role_id = {$this->db->dbprefix}role.role_id
							WHERE (
							{$this->db->dbprefix}role.profile_assoc = '{$profile_id}' OR
							{$this->db->dbprefix}role.profile_assoc LIKE '{$profile_id},%' OR
							{$this->db->dbprefix}role.profile_assoc LIKE '%,{$profile_id}' OR
							{$this->db->dbprefix}role.profile_assoc LIKE '%,{$profile_id},%')";
							$request_role = $this->db->query($qry)->result_array();
								foreach($request_role as $role_info){
									if ( !in_array($role_info['user_id'], array(1,2,3))) {
										$user_id[] = $role_info['user_id'];
									}
									
								}
								
						}

						//search for project_hr access
						/*if (CLIENT_DIR == 'firstbalfour') {
							if ($request['project_name_id'] > 0) {
								foreach ($profile_project_hr as $profile_project) {
									$qry = "SELECT *
									FROM {$this->db->dbprefix}role
									LEFT JOIN {$this->db->dbprefix}user ON {$this->db->dbprefix}user.role_id = {$this->db->dbprefix}role.role_id
									-- LEFT JOIN {$this->db->dbprefix}employee_work_assignment ON {$this->db->dbprefix}user.employee_id = {$this->db->dbprefix}employee_work_assignment.employee_id
									WHERE (
									{$this->db->dbprefix}role.profile_assoc = '{$profile_project}' OR
									{$this->db->dbprefix}role.profile_assoc LIKE '{$profile_project},%' OR
									{$this->db->dbprefix}role.profile_assoc LIKE '%,{$profile_project}' OR
									{$this->db->dbprefix}role.profile_assoc LIKE '%,{$profile_project},%') 
									AND {$this->db->dbprefix}user.project_name_id = {$request['project_name_id']}";
									$request_role = $this->db->query($qry)->result_array();
										foreach($request_role as $role_info){
											if ($request['user_id'] != $role_info['user_id']) {
												$user_id[] = $role_info['user_id'];
											}
										}
								}							
							}	
						}*/

						$this->db->where_in('user_id', $user_id);
						$this->db->where('deleted', 0);
						$this->db->where('inactive', 0);
					}

					if (CLIENT_DIR  == 'firstbalfour'){
						$request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname'];
						if ($request['aux'] != ''){
							$request['employee'] = $request['salutation']." ".$request['firstname']." ".$request['lastname']." ".$request['aux'];
						}
					}

					$result = $this->db->get('user')->result_array();
					
					foreach ($result as $row) {
						$recepients[] = $row['email'];
						if (CLIENT_DIR  == 'firstbalfour'){
                            $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname'];
                            if ($row['aux'] != ''){
                                $request['approver_user'] = $row['salutation']." ".$row['firstname']." ".$row['lastname']." ".$row['aux'];
                            }    							
						}
						else{
							$request['approver_user'] = $row['salutation']." ".$row['lastname'];
						}	

						if (CLIENT_DIR  == 'firstbalfour')
						{
							if ($data['form_status_id'] == 6) {
								
								$message = $this->template->prep_message($template_hr['body'], $request);
								$this->template->queue(trim($row['email']), '', $leave_type." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);

							}else{

								$message = $this->template->prep_message($template['body'], $request);
								$this->template->queue(trim($row['email']), '', $leave_type." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);
							}

						}else{

							$message = $this->template->prep_message($template['body'], $request);
							$this->template->queue(trim($row['email']), '', $leave_type." : ".$request['firstname']." ".$request['middleinitial']." ".$request['lastname'], $message);							
						}

					}

					// If queued successfully set the status to For Approval.
					// original subject = $template['subject']

					if ( true ) {

						$data['email_sent'] = '1';
                    	$data['date_sent'] = date('Y-m-d G:i:s');		

						$this->db->where($this->key_field, $request[$this->key_field]);
						$this->db->update('employee_leaves', $data);

						$this->db->where_in('approver', $app_array);
						$this->db->where('leave_id', $this->input->post('record_id'));
						$this->db->update('leave_approver', array('status' => 2) );
					}

				}
			}			
		}
	}
}