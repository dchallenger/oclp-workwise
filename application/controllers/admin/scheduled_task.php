<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Scheduled_task extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Alert Frequency';
		$this->listview_description = 'This module lists all defined alert frequency(s).';
		$this->jqgrid_title = "Alert Frequency List";
		$this->detailview_title = 'Alert Frequency Info';
		$this->detailview_description = 'This page shows detailed information about a particular alert frequency.';
		$this->editview_title = 'Alert Frequency Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about alert frequency(s).';

		$this->default_sort_col = array('crontask_name');
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

		parent::ajax_save();

		//additional module save routine here
		$this->db->delete('scheduled_task_variable',array($this->key_field => $this->key_field_val));

		foreach( $this->input->post('variable') as $key => $val ){

			$data = array(
				'scheduled_task_id' => $this->key_field_val,
				'crontask_variable' => $key,
				'value' => $val
			);

			$this->db->insert('scheduled_task_variable',$data);

		}

		//spreed frequency
		$this->db->delete('scheduled_task_minute',array($this->key_field => $this->key_field_val));
		$this->db->delete('scheduled_task_hour',array($this->key_field => $this->key_field_val));
		$this->db->delete('scheduled_task_day_of_month',array($this->key_field => $this->key_field_val));
		$this->db->delete('scheduled_task_month',array($this->key_field => $this->key_field_val));
		$this->db->delete('scheduled_task_day_of_week',array($this->key_field => $this->key_field_val));

		$minutes = $this->input->post('minute');
		$minutes = explode(',', $minutes);
		foreach( $minutes as $minute ){
			$this->db->insert('scheduled_task_minute', array($this->key_field => $this->key_field_val, 'minute' => $minute));
		}

		$hours = $this->input->post('hour');
		$hours = explode(',', $hours);
		foreach( $hours as $hour ){
			$this->db->insert('scheduled_task_hour', array($this->key_field => $this->key_field_val, 'hour' => $hour));
		}
		
		$day_of_months = $this->input->post('day_of_month');
		$day_of_months = explode(',', $day_of_months);
		foreach( $day_of_months as $day_of_month ){
			$this->db->insert('scheduled_task_day_of_month', array($this->key_field => $this->key_field_val, 'day_of_month' => $day_of_month));
		}
		
		$months = $this->input->post('month');
		$months = explode(',', $months);
		foreach( $months as $month ){
			$this->db->insert('scheduled_task_month', array($this->key_field => $this->key_field_val, 'month' => $month));
		}
		
		$day_of_weeks = $this->input->post('day_of_week');
		$day_of_weeks = explode(',', $day_of_weeks);
		foreach( $day_of_weeks as $day_of_week ){
			$this->db->insert('scheduled_task_day_of_week', array($this->key_field => $this->key_field_val, 'day_of_week' => $day_of_week));
		}
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions

	function get_record_info(){

		$scheduled_task_info = $this->db->get_where('scheduled_task',array( $this->key_field => $this->input->post('record_id') ))->row();

		$response->implement_type = $scheduled_task_info->hour_implement_type_id;

		$data['json'] = $response;
	    $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

	}

	function get_function_variables(){

		$html = '';

		if( $this->input->post('view') == 'edit' ){

			$function = $this->input->post('function');

		}
		elseif( $this->input->post('view') == 'detail' ){

			$scheduled_task_info = $this->db->get_where('scheduled_task',array( $this->key_field => $this->input->post('record_id') ))->row();

			$function = $scheduled_task_info->crontask_function_id;

		}

		$this->db->where('crontask_function.crontask_function_id',$function);
		$this->db->join('crontask_function','crontask_function.crontask_function_id = crontask_variable.crontask_function_id','left');
		$result = $this->db->get('crontask_variable');

		if( $result->num_rows() > 0 ){

			foreach( $result->result() as $variable_info ){

				$variable_value = $variable_info->default_value;

				$this->db->where('scheduled_task_id',$this->input->post('record_id'));
				$this->db->where('crontask_variable_id',$variable_info->crontask_variable_id);
				$scheduled_task_variable_info = $this->db->get('scheduled_task_variable');

				if( $scheduled_task_variable_info->num_rows() > 0 ){
					$variable_value = $scheduled_task_variable_info->row()->value;
				}

				if( $this->input->post('view') == 'edit' ){

					$html.='
					<div class="form-item odd">
	                    <label class="label-desc gray" for="variable['.$variable_info->crontask_variable_id.']">
	                        '.$variable_info->crontask_variable_label.'
	                    </label>
	                    <div class="text-input-wrap">
	                        <input type="text" class="input-text" value="'.$variable_value.'" name="variable['.$variable_info->crontask_variable.']">
	                    </div>
	                </div>';

            	}
            	else{

            		$html .= '
            		<div class="form-item view odd ">
                    	<label class="label-desc view gray" for="template_id">'.$variable_info->crontask_variable_label.':</label>
                    	<div class="text-input-wrap">'.$variable_value.'</div>		
                	</div>';

            	}


			}


		}
		else{

			$html = "<div style='text-align:center;'>No variables available</div>";

		}

		$data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);

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
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        if ($this->user_access[$this->module_id]['configure']) {
            if($record['t1task_status'] == 'Ready'){
            	$actions .= '<a class="icon-button icon-16-suspend suspend-single" tooltip="Suspend Task" container="'.$container.'" module_link="'.$module_link.'" href="javascript:suspend_task('.$record['scheduled_task_id'].')"></a>';
            	$actions .= '<a class="icon-button icon-16-execute execute-single" tooltip="Execute Task" container="'.$container.'" module_link="'.$module_link.'" href="javascript:execute_task('.$record['scheduled_task_id'].')"></a>';
        	}
        	if($record['t1task_status'] == 'Suspended'){
        		$actions .= '<a class="icon-button icon-16-unsuspend unsuspend-single" tooltip="Unsuspend Task" container="'.$container.'" module_link="'.$module_link.'" href="javascript:unsuspend_task('.$record['scheduled_task_id'].')"></a>';	
        	}
        }       
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }



        $actions .= '</span>';

		return $actions;
	}

	function execute_task(){
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if( !$this->user_access[$this->module_id]['configure']) {
			$response->msg = "You do not have enough privilege to execute the requested action.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}

		$scheduled_task_id = $this->input->post('scheduled_task_id');

		$qry = "select a.scheduled_task_id, a.template_id, a.email_to, a.email_cc, a.email_bcc, b.crontask_function, a.task_status
		FROM {$this->db->dbprefix}scheduled_task a
		LEFT JOIN {$this->db->dbprefix}crontask_function b ON b.crontask_function_id = a.crontask_function_id
		WHERE a.deleted = 0 and  a.scheduled_task_id = {$scheduled_task_id}";

		$scheduled_tasks = $this->db->query( $qry );

		if($scheduled_tasks->num_rows() == 1){
			$task = $scheduled_tasks->row();
			if( $task->task_status == 1 ){
				if( !$this->load->model(CLIENT_DIR.'_cron', 'cron', false, true) ) $this->load->model('cron');
				$response = $this->cron->_execute_task( $task );
			}
			else{
				if( $task->task_status == 2 ){
					$response->msg = "Task is already running.";
					$response->msg_type = "attention";	
				}

				if( $task->task_status == 3 ){
					$response->msg = "Task is already suspended.";
					$response->msg_type = "error";	
				}
			}
		}
		else{
			$response->msg = "Specified task was not found";
			$response->msg_type = "error";
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function change_status(){
		if( !IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'System does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if( !$this->user_access[$this->module_id]['configure']) {
			$response->msg = "You do not have enough privilege to execute the requested action.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}
		
		$task_status = $this->input->post('task_status');
		$scheduled_task_id = $this->input->post('scheduled_task_id');
		$this->db->update('scheduled_task', array('task_status' => $task_status), array('scheduled_task_id' => $scheduled_task_id));

		$response->msg = "Task updated.";
		$response->msg_type = "success";	
		$this->load->view('template/ajax', array('json' => $response));
	}

	function monthly_accumulation($date = false){
		$this->balfour_monthly_accumulation(false,false,false,false,$date);
	}

	function balfour_monthly_accumulation($template_id = false,$email_to = false,$email_cc = false,$email_bcc = false,$date = false)
	{
		$date_now = ($date ? $date : date("Y-m-d"));
		// get leave types
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_type_leave_setup.application_form_id', 'left');
		$this->db->order_by('employee_type_leave_setup.application_form_id', 'ASC');
		$this->db->where('accumulation_type_id', 1);
		$leave_types = $this->db->get('employee_type_leave_setup')->result();

		foreach($leave_types as $leave_type)
		{
			// get data
			$appcode = strtolower($leave_type->application_code);
			$appcode_used = $appcode.'_used';
			$carried = 'carried_'.$appcode;

			// initial query
			$this->db->select("
							  employee.*,
							  user.*,
							  (DATE_FORMAT(FROM_DAYS(TO_DAYS('{$date_now}')-TO_DAYS(leave_accumulation_start_date)), '%Y') + 0) as tenureship
							  ", false);
			$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
			$this->db->where('user.deleted', 0);
			$this->db->where('user.employee_id <>', 1);
			$this->db->where('employee.CBE', $leave_type->CBE);
			$this->db->where('employee.resigned', 0);

			// validate employees to get
			if($leave_type->employee_type_id)
				$this->db->where('employee_type', $leave_type->employee_type_id);

			if($leave_type->employment_status_id)
				$this->db->where_in('status_id', explode(',',$leave_type->employment_status_id));

			if($this->config->item('use_cbe_cba'))
			{
				$this->db->where('CBE', $leave_type->CBE);
				$this->db->where('ECF', $leave_type->CBA);
			} 

			$users = $this->db->get('user');

			if(!$users && $users->num_rows() == 0)
				continue;

			// insert accumulation on all employee's satisfied
			foreach($users->result() as $user) 
			{
				// get data
				//to override if exist in leave setup exemption - tirso
				$exemption_array = $this->system->get_employe_leave_setup_exemption($user->employee_id,$leave_type->leave_setup_id);

				$maximum_tmp = ($exemption_array['maximum'] !== 0 ? $exemption_array['maximum'] : $leave_type->maximum);
				$accumulation_tmp = ($exemption_array['accumulation'] !== 0 ? $exemption_array['accumulation'] : $leave_type->accumulation);
				//

				$tenure = ($user->tenureship == 0 ? 1 : $user->tenureship);
				$accumulation = $accumulation_tmp;
				$prorate_accumulation = 0;

				// to compute and get pro rated
				if ($user->leave_accumulation_start_date != '0000-00-00' && $user->leave_accumulation_start_date != null){
					$employed_date = $user->leave_accumulation_start_date;
					$prev_month = strtotime($date_now . '-1 month');
					$prev_mont_start = date('Y-m-01',$prev_month);
					$prev_mont_end = date('Y-m-t',$prev_month);
					$date_end = new DateTime($prev_mont_end);
					$date_employed = new DateTime($employed_date);
					$no_days = date('t',$prev_month);

					if ($employed_date >= $prev_mont_start && $employed_date <= $prev_mont_end){
						$diff = $date_end->diff($date_employed);
						$no_days_to_prorate = $diff->d + 1;
						$prorate_accumulation = number_format((($accumulation / $no_days) * $no_days_to_prorate),2,'.',',');
						if ($prorate_accumulation < 0){
							$prorate_accumulation = 0;
						}
					}
				}

				$this->db->order_by('tenure', 'DESC');
				$etlc = $this->db->get_where('employee_type_leave_credit', array('leave_setup_id' => $leave_type->leave_setup_id, 'leave_type' => $leave_type->application_form_id, 'tenure <=' => $tenure));

				if($etlc && $etlc->num_rows() > 0)
					$accumulation = $etlc->row()->leave_accumulated;

				$new_value = $accumulation + $prorate_accumulation;

				$e_balance = $this->db->get_where('employee_leave_balance', array('year' => date('Y'), 'employee_id' => $user->user_id, 'deleted' => 0));

				if($e_balance->num_rows() > 0) {

					$balance = $e_balance->row();

					$total_balance = ($accumulation + $balance->{$appcode} + $balance->{$carried}) - $balance->{$appcode_used};
					$new_value = $accumulation + $balance->{$appcode} + $prorate_accumulation;
					
					if($maximum_tmp > 0) {
						if ((($balance->{$appcode} + $balance->{$carried}) - $balance->{$appcode_used}) >= $maximum_tmp){
							if(!$leave_type->convertible)
								$new_value = $balance->{$appcode};
						}
						else{
							if($total_balance >= $maximum_tmp) {
								if(!$leave_type->convertible)
									$new_value = ($maximum_tmp - (($balance->{$appcode} + $balance->{$carried}) - $balance->{$appcode_used})) + $balance->{$appcode};
							}
						}
					}

					$this->db->set($appcode, $new_value, false);
					$this->db->where('leave_balance_id', $balance->leave_balance_id);
					$this->db->update('employee_leave_balance');

				} else {

					$data = array(
						'year' => date('Y'),
						'employee_id' => $user->user_id,
						$appcode => $new_value,
						'deleted' => 0
					);

					$this->db->insert('employee_leave_balance', $data);

				}

			}

		}

		$response->msg_type = "success";
		$response->msg = "Task is successfully executed";		

		return $response;
	}

	protected function _append_to_select()
	{
		$this->listview_qry .= ',crontask_name';
	}

	function _set_left_join() 
	{
		$this->db->join('crontask_function', 'scheduled_task.crontask_function_id = crontask_function.crontask_function_id');
		parent::_set_left_join();
	}	
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>