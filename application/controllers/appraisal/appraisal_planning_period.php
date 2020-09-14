<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appraisal_planning_period extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists Planning Period Setup.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'Planning Period Setup';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about Planning Period Setup';
		
		$this->load->model('portlet');
		$portlet = $this->portlet->get_ppa();
		
		$dbprefix = $this->db->dbprefix;

		if ((!$this->user_access[$this->module_id]['publish'] && !$this->user_access[$this->module_id]['post'])) {
			$ids = array();
			foreach ($portlet['IGS'] as $igs) {
				if ($igs['count'] != 0) {
					$ids[] = $igs['planning_period_id'];
				}	
				
			}
			if (count($ids) > 0) {
				$this->filter = $this->key_field ." IN (".implode(',', $ids).")";
			}
			else {
				$this->filter = $this->key_field ." IN (0)";	
			}
		}

		if (!$this->user_access[$this->module_id]['post']) {
			if (isset($this->filter) && $this->filter != '') {
				$this->filter .= ' AND '. $dbprefix.'appraisal_planning_period.period_status = 1';
			}
			else {
				$this->filter = ''.$dbprefix.'appraisal_planning_period.period_status = 1';	
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
		if ($this->user_access[$this->module_id]['post']) {
			$data['msg'] = "<h3>".$this->detailview_description . "</h3>";
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
		if ($this->key_field_val && $this->key_field_val > 0) {
				$this->db->where($this->key_field, $this->key_field_val);
				$reminder = $this->db->get('appraisal_email_reminder');
				
				if ($reminder && $reminder->num_rows() > 0) {
					$data['reminder'] = $reminder->result();
				}
			}

		$this->db->where('module_id',$this->module_id);
		$this->db->where('deleted',0);
		$template_result = $this->db->get('template');

		$data['template_result'] = $template_result;


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
			// $data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			$data['scripts'][] = uploadify_script();
			$data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}


			if ($this->input->post('duplicate')) {
				$data['duplicate'] = $this->input->post('record_id');
			}

			$data['content'] = 'editview';
			// $data['buttons'] = 'admin/appraisal/send-email';
			//other views to load
			$data['views'] = array('admin/appraisal/duplicate');
			$data['views_outside_record_form'] = array();
			
			$data['reminder'] = array();

			if ($this->key_field_val && $this->key_field_val > 0) {
				$this->db->where($this->key_field, $this->key_field_val);
				$reminder = $this->db->get('appraisal_email_reminder');
				
				if ($reminder && $reminder->num_rows() > 0) {
					$data['reminder'] = $reminder->result();
				}

				$this->db->where($this->key_field, $this->key_field_val);
				$record = $this->db->get($this->module_table)->row();
				if ($record->duplicate_id > 0) {
					$_POST['duplicate'] = $record->duplicate_id;
				}
			}
			
			$this->db->where('module_id',$this->module_id);
			$this->db->where('deleted',0);
			$template_result = $this->db->get('template');

			$data['template_result'] = $template_result;

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
		$planning_date = $this->input->post('date');
		$planning = $this->input->post('planning');
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');

		$error_on_date_reminder = false;
		$reminder_date_row = '';

		if($error_on_date_reminder){
			$response->msg_type = 'error';
 			$response->msg 		= 'Reminder Date Error on: '.$reminder_date_row;
            $data['json'] = $response;
            $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
            return;
		}
		
		if (strtotime($this->input->post('date_to')) < strtotime($this->input->post('date_from'))) {
			$data['msg'] = 'Planning Date : Invalid date range.';
			$data['msg_type'] = 'error';
			$data['json'] = $data;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			return;
		}
			
		parent::ajax_save();

		//additional module save routine here
		if ($this->input->post('duplicate_id')) {
			$data['duplicate_id'] = $this->input->post('duplicate_id');
		}

		$data['created_by'] = $this->userinfo['user_id'];
		$data['mid_date_from'] = date('Y-m-d',strtotime($this->input->post('mid_date_from')));
		$data['mid_date_to'] = date('Y-m-d',strtotime($this->input->post('mid_date_to')));

		$this->db->where($this->key_field, $this->key_field_val);
		$this->db->update($this->module_table, $data);

		//$this->_send_email($this->key_field_val, 'appraisal_planning_period_to_appraisee');
	}

	function after_ajax_save()
	{


		$this->db->where($this->key_field, $this->key_field_val);
		$result = $this->db->get('appraisal_email_reminder');

		if ($result && $result->num_rows() > 0) {
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->delete('appraisal_email_reminder');
		}

		$reminder = $this->input->post('reminder');
		$planning = $this->input->post('planning');
		$attachment = $this->input->post('attachment');
		$date_array = $this->input->post('date');
		$template = $this->input->post('template');
		$email_sent = $this->input->post('email_sent');
		if ($reminder && $reminder != "") {
			foreach ($reminder as $key => $rem) {
				$date = '';
				if(!empty($date_array[$key])) {
					$date = date( 'Y-m-d', strtotime($date_array[$key]));
				}
				$data['appraisal_email_reminder'] = $planning[$key];
				$data['date'] = $date;
				$data['email_sent'] = $email_sent[$key];
				$data['uploaded_file'] = $attachment[$key];
				$data['template_id'] = $template[$key];
				$data['planning_period_id'] = $this->key_field_val;
				$this->db->insert('appraisal_email_reminder', $data);
			}	
		}

		parent::after_ajax_save();
	}

	function duplicate_records(){
		$this->db->where('planning_period_id',$this->input->post('record_id'));
		$result = $this->db->get('appraisal_planning_period');

		if ($result && $result->num_rows() > 0){
			$info = $result->row_array();
			
			array_shift($info);

			$info['duplicate_id'] = $this->input->post('record_id');
			$info['duplicated_by'] = $this->userinfo['user_id'];
			$info['duplicated_date'] = date('Y-m-d H:i:s');
			$info['period_status'] = 1;

			$this->db->insert('appraisal_planning_period',$info);

			$data['msg'] = 'Record successfully duplicated';
			$data['msg_type'] = 'success';
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
		}
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}

	// END - default module functions
	// START custom module funtions



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
        
        if ($this->user_access[$this->module_id]['delete']) {
        	if ($record['period_status'] != 2) {
            	$actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        	}
        }
       
        $actions .= '<a class="icon-button icon-16-users" tooltip="View details of this record?" href="' . site_url('appraisal/appraisal_planning/index/' . $record[$this->key_field]) . '"></a>';
        
        if ( $this->user_access[$this->module_id]['add'] && ($record['period_status'] == 2)) {
            $actions .= '<a class="icon-button icon-16-document-stack" tooltip="Duplicate" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 

        $actions .= '</span>';

		return $actions;
	}

	function _append_to_select()
	{
		//$this->listview_qry .= ', employee_appraisal.employee_id, user.position_id';
		$this->listview_qry .= ', period_status'; 

	}

	function add_reminder_form(){

		$this->db->where('module_id',$this->module_id);
		$this->db->where('deleted',0);
		$template_result = $this->db->get('template');

		$data = array(
			'count' => $this->input->post('counter_line'),
			'template_result' => $template_result
		);

		$response->status_form = $this->load->view( $this->userinfo['rtheme'].'/admin/appraisal/reminder_form',$data, true );
		$this->load->view('template/ajax', array('json' => $response));

	}

	function get_employees()
	{

		if (IS_AJAX)
		{
			$status_id = $this->input->post('status_id');
			$record_id = $this->input->post('record_id');
			$company_id = $this->input->post('company_id');

			$options = '';
			$where = "status_id IN (".$status_id.")";
			if ($company_id && $company_id != 'null') {
				$where .= ' AND company_id IN ('.$company_id.')';	
			}

			$this->db->where($where);
			$this->db->where('inactive', 0);
			$this->db->where('user.deleted', 0);
			$this->db->join('employee', 'employee.employee_id = user.employee_id');
			$this->db->order_by('firstname,lastname', 'ASC');
			$result = $this->db->get('user');
			

			$this->db->where($this->key_field, $record_id);
			$record = $this->db->get($this->module_table);
			$response['employees'] = '';
			if ($record && $record->num_rows() > 0) {
				$rec = $record->row();
				$employees = $rec->employee_id;
				$response['employees'] = explode(',', $employees);
			}

			if ($result->num_rows() > 0) {
				$employee = $result->result();
				
				foreach ($employee as $emp) {
					$options .= '<option value="'.$emp->employee_id.'">'.$emp->firstname." ".$emp->middleinitial." ".$emp->lastname. " ".$emp->aux.'</option>';
				}

				$response['result'] = $options;
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

		
	}

	function _send_email($appraisal_planning_period_id, $template_code)
	{
		$this->load->model('template');

		$template = $this->template->get_module_template(0, $template_code);

		$this->db->where('planning_period_id',$appraisal_planning_period_id);
		$period_info_result = $this->db->get('appraisal_planning_period');

		if ($period_info_result && $period_info_result ->num_rows() > 0) {
			$period_info = $period_info_result->row();
			$appraisee = $period_info->employee_id;
			$appraisee = explode(',', $appraisee);
			foreach ($appraisee as $key => $value) {
				$appraisee_info_result = $this->db->get_where('user',array("user_id"=>$value));
				if ($appraisee_info_result && $appraisee_info_result->num_rows() > 0){
					$appraisee_info = $appraisee_info_result->row_array();
				}

				$request['appraisee'] = $appraisee_info['firstname']." ".$appraisee_info['lastname'];
		        $request['period'] = $period_info->planning_period;
		        $request['year'] = $period_info->year;
				$request['here']=base_url().'appraisal/appraisal_planning/edit/'.$value.'/'.$appraisal_planning_period_id;
				
				$message = $this->template->prep_message($template['body'], $request);
			    $this->template->queue($appraisee_info['email'], '', $template['subject'], $message);
			}
		}

	    $response->msg = 'Appraisal sent';
		$response->msg_tpe = 'success'; 
	}	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */