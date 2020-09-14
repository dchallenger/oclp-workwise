<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_worksched extends MY_Controller
{
	function __construct(){
		parent::__construct();
		
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format
		
		$this->listview_title = '';
		$this->listview_description = 'This module lists all defined (s).';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';
		
        if($this->user_access[$this->module_id]['post'] != 1){
			$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
			$subordinate_id = array(0);
			if( count($subordinates) > 0 ){

				$subordinate_id = array();

				foreach ($subordinates as $subordinate) {
						$subordinate_id[] = $subordinate['user_id'];
				}
			}
			$subordinate_list = implode(',', $subordinate_id);
			if( $subordinate_list != "" )
				$this->filter = $this->module_table.".employee_id IN (". $subordinate_list .")";
			else
				$this->filter = $this->module_table.".employee_id IN (". $this->user->user_id .")";
        }

        $this->default_sort_col = array('user.firstname');
	}

	// START - default module functions
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['list'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
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

	function detail(){
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

	function edit(){
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

	function ajax_save(){
		//check that period is not in conflict
		$date_from = date('Y-m-d', strtotime($this->input->post('date_from')));
		$date_to =  date('Y-m-d', strtotime($this->input->post('date_to')));
		$employee_id = $this->input->post('employee_id');

		$qry = "SELECT *
		FROM {$this->db->dbprefix}{$this->module_table}
		WHERE deleted = 0 AND employee_id = '{$employee_id }' AND
		(
			date_from BETWEEN '{$date_from}' AND '{$date_to}' OR
			(
				(date_from < '{$date_from}' AND date_to > '{$date_from}') OR
				(date_from < '{$date_to}' AND date_to > '{$date_to}')
			)
		 ) AND {$this->key_field} <> {$this->input->post('record_id')}";

/*		(
			( '{$date_from}' BETWEEN date_from AND date_to OR '{$date_to}' BETWEEN date_from AND date_to )
			OR
			( date_from BETWEEN '{$date_from}' AND '{$date_to}' OR date_to BETWEEN '{$date_from}' AND '{$date_to}' )
		)";*/

		$result = $this->db->query( $qry );

		if( $result->num_rows() == 0 ){
			if ($this->input->post('date_from') == "" || $this->input->post('date_to') == ""){
				$response->msg = "Date period is mandatory.";
				$response->msg_type = "error";
				$data['json'] = $response;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
			}
			else{			
				parent::ajax_save();
			}
		}
		else{
	        if ($this->config->item('client_no') == 1){
	            if (date('Y-m-d') >= date('Y-m-d',strtotime($date_to))){
					$response->msg = "Your application exceeded the grace period.";
					$response->msg_type = "error";
					$data['json'] = $response;
					$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	            	
	            }
	        }
	        else{			
				$response->msg = "Date range schedule already exists.";
				$response->msg_type = "error";
				$data['json'] = $response;
				$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			}
		}
		//additional module save routine here
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}

	function send_email() {
		$this->db->join('user','user.employee_id = workschedule_employee.employee_id','left');
		//$this->db->join('workschedule_employee','workschedule_employee.shift_calendar_id = timekeeping_shift_calendar.shift_calendar_id','left');
		$this->db->where('workschedule_employee.workschedule_id',$this->input->post(record_id));
       	$request = $this->db->get('workschedule_employee');
        if (IS_AJAX && !is_null($request) && $request->num_rows() > 0) {
            $mail_config = $this->hdicore->_get_config('outgoing_mailserver');
            if ($mail_config) {
                $recepients = array();
                $request = $request->row_array();

                $const = eval(GROUP_WORKSCHED_EMAIL);
				foreach($const as $sample)
				{
	                $this->db->where('user_id', $sample);
	                $emailApprover=$this->db->get('user')->row_array();
	                $request['approver_user'] = $emailApprover['salutation']." ".$emailApprover['lastname'].", ".$request['approver_user'];
	                $recepients[] = $emailApprover['email'];
	            }

              	$request['date_from'] = date($this->config->item('display_date_format'), strtotime($request['date_from']));
              	$request['date_to'] = date($this->config->item('display_date_format'), strtotime($request['date_to']));
              	$shift_id = $this->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $request['shift_calendar_id']))->row_array();
              	$request['new_sched'] = $shift_id['shift_calendar'];
                // $this->db->where('employee_id', );
                // $to_be_filled=$this->db->get('user')->row_array();

                $request['here']=base_url().'dtr/employee_worksched/detail/'.$this->input->post(record_id);

                // Load the template.            
                $this->load->model('template');
                $template = $this->template->get_module_template($this->module_id, 'EWSE');
                $message = $this->template->prep_message($template['body'], $request);

                // If queued successfully set the status to For Approval.
               	$this->template->queue(implode(',', $recepients), '', $template['subject'], $message);
            
            }
        } else {
            $this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
            redirect(base_url() . $this->module_link);
        }
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
		$search_string[] = $this->db->dbprefix .'user.firstname LIKE "%' . $value . '%"';
		$search_string[] = $this->db->dbprefix .'user.lastname LIKE "%' . $value . '%"';
		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}
	
	function _set_left_join() {
		parent::_set_left_join();

		$this->db->join('user', 'user.user_id = ' . $this->module_table . '.employee_id', 'left');
	}

	function _set_listview_query( $listview_id = '', $view_actions = true ) {
		parent::_set_listview_query($listview_id, $view_actions);

		$this->listview_qry .= ',user.firstname, user.lastname';		
	}	

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
	    $sort_col = array();
	    foreach ($arr as $key=> $row) {
	        $sort_col[$key] = $row[$col];
	    }

	    array_multisort(array_map('strtolower',$sort_col), $dir, $arr);
	}

	function get_subordinates()
	{
		if (is_null($this)) { 
			$ci =& get_instance();
			$emp = $ci->db->get_Where('employee', array('employee_id' => $ci->user->user_id ))->row(); 
			$subs = $ci->hdicore->get_subordinates($ci->userinfo['position_id'], $emp->rank_id, $ci->user->user_id);

			$ci->array_sort_by_column($subs, 'firstname');

            return $subs;
		}

		$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row(); 

		$subs = $this->hdicore->get_subordinates( $this->userinfo['position_id'], $emp->rank_id, $this->user->user_id  );

		$this->array_sort_by_column($subs, 'firstname');

		return $subs;
	}	
	// END - default module functions

	// START custom module funtions
	// END custom module funtions

}

/* End of file */
/* Location: system/application */