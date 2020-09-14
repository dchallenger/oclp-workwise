<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Blanket extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		$this->load->model('forms/leaves_model', 'leaves');

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = $this->module_name; 
		$this->listview_description = 'This module lists blanket leave applications.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a blanket application.';
		$this->editview_title = 'Add/Edit ' . $this->module_name;
		$this->editview_description = 'This page allows saving/editing information about a payroll account';

		if (CLIENT_DIR == "firstbalfour") {

			if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
				$dbprefix = $this->db->dbprefix;
				$subordinate_id = array();
				$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
				$subordinates = $this->system->get_subordinates_by_project($emp->employee_id);
				$subordinate_id = array(0);
				if( count($subordinates) > 0 && $subordinates != false){

					$subordinate_id = array();
					$blanket_id = array();
					foreach ($subordinates as $subordinate) {
							$subordinate_id[] = $subordinate['employee_id'];
							$sql = "SELECT * FROM {$dbprefix}{$this->module_table} WHERE FIND_IN_SET({$subordinate['employee_id']}, employee_id)";
							$blanket = $this->db->query($sql);

							if ($blanket && $blanket->num_rows() > 0) {
								foreach ($blanket->result() as $value) {
									$blanket_id[] = $value->blanket_id;	
								}
								
							}
					}

					$blankets = array_unique($blanket_id);

					$this->filter .= $dbprefix.'employee_leave_blanket.blanket_id IN ('.implode(',', $blankets).')';
				}else{
					$this->filter .= $dbprefix.'employee_leave_blanket.blanket_id IN (0)';
				}		

			}
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

		$data['content'] = 'leaves/detailview';
		$data['record'] = $this->db->get_where($this->module_table, array( $this->key_field => $this->key_field_val ))->row();
		//other views to load
		$data['views'] = array();

		if (!empty($this->module_wizard_form) || $this->input->post('record_id') == '-1') {
			$data['show_wizard_control'] = true;
		}

		$data['record_id'] = $this->input->post('record_id');

		// Get leave dates
		$this->db->select('DISTINCT(date),duration');
		$this->db->join('employee_leaves_duration', 'employee_leaves_dates.duration_id = employee_leaves_duration.duration_id', 'left');
		$this->db->join('employee_leaves', 'employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id', 'left');
		$this->db->where( array( $this->key_field => $this->key_field_val, 'employee_leaves_dates.deleted' => 0 ) );
		$result = $this->db->get('employee_leaves_dates');
		
		if ($result->num_rows() > 0) {
			$data['dates_affected'] = $result->result_array();
		}

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
		$this->db->where('blanket_id', $this->input->post('record_id'));
		$this->db->delete('employee_leaves');		
		//additional module delete routine here
		$this->update_leave_credits(1, true);

		parent::delete();
	}
	// END - default module functions

	// START custom module funtions

	function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, array('application_form_id' => 3));

			$this->db->where('blanket_id', $this->key_field_val);
			$this->db->delete('employee_leaves');

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->where('deleted', 0);

			$record = $this->db->get($this->module_table)->row();
			$users = explode(',', $record->employee_id);

			$data['application_form_id'] = $record->application_form_id;
			$data['form_status_id']		 = 3;
			$data['blanket_id'] 		 = $this->key_field_val;
			$data['date_from']			 = $record->date_from;
			$data['date_to']			 = $record->date_to;
			$data['reason']			 	 = $record->reason;

			$insert = array();
			foreach ($users as $user) {
				$data['employee_id'] = $user;
				$insert[] = $data;
				$this->db->insert('employee_leaves', $data);
				$employee_leave_id = $this->db->insert_id();
				$start_date = date('Y-m-d', strtotime($record->date_from));
				$end_date = date('Y-m-d', strtotime($record->date_to));

				$dates = $this->input->post('dates');
				$duration_id = $this->input->post('duration_id');
				$date_ids = $this->input->post('employee_leave_date_id');
				// Loop through date_ids and determine which should be inserted / updated.
				$ids = array();
				foreach ($date_ids as $key => $date_id) {
					$worksched = $this->system->get_employee_worksched($user, date('Y-m-d', strtotime( $dates[$key] ) ));

					//check shift base on work sched to remove days which fall on rests days	
					switch( date('N', strtotime( date('Y-m-d', strtotime( $dates[$key] ) ) )) ){
						case 1:
							if( !empty( $worksched->monday_shift_id ) ){
								$shift_id = $worksched->monday_shift_id;
							}
							break;
						case 2:
							if( !empty( $worksched->tuesday_shift_id ) ){
								$shift_id = $worksched->tuesday_shift_id;
							}
							break;
						case 3:
							if( !empty( $worksched->wednesday_shift_id ) ){
								$shift_id = $worksched->wednesday_shift_id;	
							}
							break;
						case 4:
							if( !empty( $worksched->thursday_shift_id ) ){
								$shift_id = $worksched->thursday_shift_id;
							}
							break;
						case 5:
							if( !empty( $worksched->friday_shift_id ) ){
								$shift_id = $worksched->friday_shift_id;
							}
							break;
						case 6:
							if( !empty( $worksched->saturday_shift_id ) ){
								$shift_id = $worksched->saturday_shift_id;
							}
							break;
						case 7:
							if( !empty( $worksched->sunday_shift_id ) ){
								$shift_id = $worksched->sunday_shift_id;
							}
							break;	
						}

					$leave_date = array(
						'employee_leave_id' => $employee_leave_id,
						'duration_id' => $duration_id[$key],
						'date' => date('Y-m-d', strtotime( $dates[$key] ) )
					);
						
					$shift = $this->db->get_where('timekeeping_shift', array('shift_id' => $shift_id))->row();
					switch( $duration_id[$key] ){
						case 1:
							$leave_date['time_start'] = $shift->shifttime_start;
							$leave_date['time_end'] = $shift->shifttime_end;
							break;
						case 2:
							$leave_date['time_start'] = $shift->shifttime_start;
							$leave_date['time_end'] = $shift->halfday;
							break;
						case 3:
							$leave_date['time_start'] = $shift->halfday;
							$leave_date['time_end'] = $shift->shifttime_end;
							break;
						// tirso - for first balfour : 2013/10/30
						case 4:
							$leave_date['time_start'] = $shift->shifttime_start;
							$leave_date['time_end'] = $shift->shifttime_end;
							break;								
						default:
							if($this->config->item('client_no') == 2)
							{
								$lv = $this->db->get_where('employee_leaves_duration', array('duration_id' => $duration_id[$key]));
								if($lv && $lv->num_rows() > 0)
								{
									$lv = $lv->row();
									$in_hours = round($lv->credit);
									$new_timeout = date('Y-m-d H:i:s', strtotime('- '.$in_hours.' hour '.$shift->shifttime_end));
									$leave_date['time_start'] = $shift->shifttime_start;
									$leave_date['time_end'] = $new_timeout;
								}
							}
							break;
					}
						
					if(CLIENT_DIR == 'oams') {
						$cred_dur = $this->system->get_cred_duration($this->input->post('employee_id'), date('Y-m-d',strtotime($dates[$key])));
							switch($duration_id[$key])
							{
								case 1:
									$day_credit = $cred_dur->total_work_hours;
								break;
								case 2:
			        				$day_credit = $cred_dur->total_first_half;
			        			break;
			        			case 3:
			        				$day_credit = $cred_dur->total_second_half;
			        			break;
			        			default:
				        			$duration = $this->db->get_where('employee_leaves_duration', array('duration_id' => $duration_id[$key]))->row();
				        			$day_credit = $duration->credit;		
				        		break;
			        		}
		        	} else {
			        		$duration = $this->db->get_where('employee_leaves_duration', array('duration_id' => $duration_id[$key]))->row();
			        		$day_credit = $duration->credit;
		        	}

						$leave_date['credit'] = $day_credit / 8;
						$this->db->insert('employee_leaves_dates', $leave_date);
						$start_date = date('Y-m-d', strtotime($start_date.' +1day'));
				}
			}
		}

		parent::after_ajax_save();
	}

	function update_leave_credits($operation = 0, $internal = false)
	{
		if ($operation == 1) {
			$method = ' + ';
		} else {
			$method = ' - ';
		}

		$this->db->where($this->key_field, $this->input->post('record_id'));
		$this->db->where('deleted', 0);

		$record = $this->db->get($this->module_table)->row();		
		// update leave credits.
		$sql = "SELECT SUM(credit) AS credit, a.employee_id, application_form_id
			FROM {$this->db->dbprefix}employee_leaves a
			LEFT JOIN {$this->db->dbprefix}employee_leaves_dates b ON a.employee_leave_id = b.employee_leave_id
			WHERE 
				blanket_id IS NULL 
			AND a.deleted = 0
			AND b.date BETWEEN '" . $record->date_from . "' AND '". $record->date_to . "'
			AND form_status_id = 3
			GROUP BY employee_id, application_form_id";

		$with_leave = $this->db->query($sql);

		if ($with_leave->num_rows() > 0) {
			foreach ($with_leave->result() as $balance) {
				$this->db->where('employee_id', $balance->employee_id);
				$this->db->where('year', date('Y', strtotime($record->date_from)));

				switch ($balance->application_form_id) {
					case 1: //SL										
						$field = 'sl_used';
						break;
					case 2: //VL
						$field = 'vl_used';
						break;	
					case 3: //EL
						$field = 'el_used';
						break;
					case 4: //EL
						$field = 'bl_used';
						break;
					case 5: //ML
					case 6: //PL
						$field = 'mpl_used';
						break;
				}

				$this->db->set($field, $field . $method . $balance->credit, false);
				$this->db->update('employee_leave_balance');
			}
		}

		$this->db->where($this->key_field, $this->input->post('record_id'));
		$this->db->update($this->module_table, array('credits_reverted' => 1));

		if (!$internal) {
			$this->load->view('template/ajax', 
				array('json' => 
					array('msg' => 'Leave credits updated.', 'msg_type' => 'success')
				)
			);			
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
        
/*		if ( $this->user_access[$this->module_id]['edit'] ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				*/

        // remove as per request on ticket 332
		// if ($record['credits_reverted'] == 0) {
		// 	$actions .= '<a class="process-credits icon-button icon-16-settings" module_link="'.$module_link.'" tooltip="Process leave credits" href="javascript:void(0)"></a>';
		// }

        // if ($this->user_access[$this->module_id]['delete']) {
        //     $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        // }
        // remove as per request on ticket 332

        $actions .= '</span>';

		return $actions;
	}	

	function _set_listview_query( $listview_id = '', $view_actions = true ) 
	{
		parent::_set_listview_query($listview_id = '', $view_actions = true );
		$this->listview_qry .= ', credits_reverted';
	}

	function filter_employees()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		} else {
			$record_id = $this->input->post('record_id');

			$defaults = array();
			if ($record_id > 0) {
				$this->db->where('blanket_id', $record_id);
				$this->db->where('deleted', 0);

				$record = $this->db->get($this->module_table);

				if ($record->num_rows() > 0) {
					$r = $record->row();

					$defaults = explode(',', $r->employee_id);
				}
			}

			$location_id = implode('","', explode(',', $this->input->post('location_id')));
			$city_id = implode('","', explode(',', $this->input->post('city_id')));

			$locwhere = array();
			if ($location_id != '') {
				$locwhere[] = 'location_id IN ("' . $location_id . '")';
			}

			if ($city_id != '') {
				$locwhere[] = 'pres_city IN ("' . $city_id . '")';
			}

			if (count($locwhere) == 0) {
				$locwhere = '1=1';
			} else {
				$locwhere = '(' . implode(' OR ', $locwhere) . ')';
			}			

			$sub_list = '1=1';

			if (CLIENT_DIR == "firstbalfour") {
				if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
					$subordinate_id = array();
					$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
					$subordinates = $this->system->get_subordinates_by_project($emp->employee_id);
					$subordinate_id = array(0);
					if( count($subordinates) > 0 && $subordinates != false){

						$subordinate_id = array();

						foreach ($subordinates as $subordinate) {
								$subordinate_id[] = $subordinate['employee_id'];
						}
					}		

					$subordinate_list = implode(',', $subordinate_id);
					if( $subordinate_list != "" || $subordinate_list != 0){
						$sub_list = 'e.employee_id IN ('.$subordinate_list.')';
					}
					else{
						if ($subordinates == false ) {
							$sub_list = 'e.employee_id IN (0)';
						}
						
					}
				}	
			}
			$this->db->select('e.employee_id as id, CONCAT(u.firstname, " ", u.middleinitial, " ", u.lastname, " ", u.aux) as name, c.city', false);
			$this->db->from('employee e');
			$this->db->join('user u', 'u.user_id = e.employee_id', 'left');
			$this->db->join('cities c', 'e.pres_city = c.city_id', 'left');
			$this->db->where('e.deleted', 0);
			$this->db->where('resigned', 0);			
			$this->db->where($locwhere, '', false);
			$this->db->where($sub_list, '', false);
			$this->db->order_by('name','ASC');

			$employees = $this->db->get();
			
			$options = '';

			if ($employees->num_rows() > 0) {
				foreach ($employees->result() as $employee) {
					$a_employee[$employee->city][] = $employee;
				}


				foreach ($a_employee as $city => $emp) {
					$options .= '<optgroup label="' . $city . '">';
					foreach ($emp as $e) {
						$selected = (in_array($e->id, $defaults)) ? ' selected="selected"' : '';

						if ($e->name){
							$options .= '<option value="' . $e->id . '"' . $selected . '>' . $e->name . '</option>';
						}
					}
					$options .= '</optgroup>';
				}				
			}			

			$this->load->view('template/ajax', array('html' => $options));
		}
	}

	function get_affected_dates( $call_from_within = false ) {
		if (IS_AJAX || $call_from_within) {
			$start_date = date('Y-m-d', strtotime($this->input->post('date_from')));
			$end_date = date('Y-m-d', strtotime($this->input->post('date_to')));
			$days = array();
			$days_ctr = 0;

			while( $start_date <= $end_date ){

						$days[$days_ctr]['date'] = date('m/d/Y', strtotime($start_date));
						$days[$days_ctr]['date2'] = date('Y-m-d',strtotime($start_date));
						$days[$days_ctr]['employee_leave_date_id'] = '0';

						if( $this->input->post('record_id') != "-1" ){
							$this->db->select('DISTINCT(date),duration_id');
							$this->db->join('employee_leaves_dates','employee_leaves.employee_leave_id = employee_leaves_dates.employee_leave_id','left');
							$this->db->join('employee_leave_blanket','employee_leaves.blanket_id = employee_leave_blanket.blanket_id','left');
							$this->db->where('employee_leaves_dates.date',date('Y-m-d', strtotime($start_date)));
							$this->db->where('employee_leave_blanket.blanket_id',$this->input->post('record_id'));
							$leave_date_result = $this->db->get('employee_leaves');
							// echo $this->db->last_query();
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

	// END custom module funtions
}

/* End of file */
/* Location: system/application */