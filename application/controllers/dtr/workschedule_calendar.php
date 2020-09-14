<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Workschedule_calendar extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = 'Calendar Work Schedule';
		$this->listview_description = '';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = '';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = '';
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
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['scripts'][] ="<link rel='stylesheet' type='text/css' href='".base_url()."lib/fullcalendar/fullcalendar/fullcalendar.css' />";
		$data['scripts'][] ="<script type='text/javascript' src='".base_url()."lib/fullcalendar/fullcalendar/fullcalendar.js'></script>";
		
		$data['content'] = 'detailview';

		//other views to load
		$data['views'] = array();

		$data['buttons'] = 'template/detail-no-buttons';

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
			$data['scripts'][] ="<link rel='stylesheet' type='text/css' href='".base_url()."lib/fullcalendar/fullcalendar/fullcalendar.css' />";
			$data['scripts'][] ="<script type='text/javascript' src='".base_url()."lib/fullcalendar/fullcalendar/fullcalendar.js'></script>";

			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'editview';

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();

			// $data['buttons'] = $this->module_link.'/save-back-button';

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

		if($this->input->post('record_id') != -1)
		{
			$emp_worksched = $this->db->get_where('workschedule_calendar', array('workschedule_calendar_id' => $this->input->post('record_id')))->row()->employee_id;

			if($this->input->post('employee_id') != $emp_worksched)
			{
				$exploded_employees = explode(',', $emp_worksched);

				$data = $this->_get_value($this->input->post('date_emp_added'));

				foreach($data as $datum)
				{
					$inclusive_dates = $this->_get_inclusive_dates($datum->date_from,$datum->date_to);

					foreach($inclusive_dates as $inclusive_date)
					{
						foreach($exploded_employees as $exploded_employee)
						{
							$saved_data = array( 'employee_id' => $exploded_employee,
												 'date' => $inclusive_date,
												 'shift_id' => -1
											    );

							$this->db->where('employee_id', $exploded_employee);
							$this->db->where('date', $inclusive_date);
							$result = $this->db->get('employee_dtr');

							if($result && $result->num_rows() > 0)
								$this->db->update('employee_dtr', $saved_data, array("employee_id" => $exploded_employee, "date" => $inclusive_date));
							else
								$this->db->insert('employee_dtr', $saved_data);
						}
					}
				}

			}
		}

		$data = array('date_emp_added' => $this->input->post('date_emp_added'), 
					  'date_emp_deleted' => $this->input->post('date_emp_deleted')
					   );

		parent::ajax_save();

		$this->db->update('workschedule_calendar', $data, array($this->key_field => $this->key_field_val));

		$exploded_employees = explode(',', $this->input->post('employee_id')); // $implode_employees = implode("','",explode(',', $this->input->post('employee_id')));


		// delete me
		$data = $this->_get_value($this->input->post('date_emp_deleted'), true);

		foreach($data as $datum)
		{
			$inclusive_dates = $this->_get_inclusive_dates($datum->date_from,$datum->date_to);

			foreach($inclusive_dates as $inclusive_date)
			{
				foreach($exploded_employees as $exploded_employee)
				{
					$saved_data = array( 'employee_id' => $exploded_employee,
										 'date' => $inclusive_date,
										 'shift_id' => -1
									    );

					$this->db->where('employee_id', $exploded_employee);
					$this->db->where('date', $inclusive_date);
					$result = $this->db->get('employee_dtr');

					if($result && $result->num_rows() > 0)
						$this->db->update('employee_dtr', $saved_data, array("employee_id" => $exploded_employee, "date" => $inclusive_date));
					else
						$this->db->insert('employee_dtr', $saved_data);
				}
			}
		}

		$data = $this->_get_value($this->input->post('date_emp_added'));

		foreach($data as $datum)
		{
			$inclusive_dates = $this->_get_inclusive_dates($datum->date_from,$datum->date_to);

			foreach($inclusive_dates as $inclusive_date)
			{
				foreach($exploded_employees as $exploded_employee)
				{
					$saved_data = array( 'employee_id' => $exploded_employee,
										 'date' => $inclusive_date,
										 'shift_id' => $datum->shift_id
									    );

					$this->db->where('employee_id', $exploded_employee);
					$this->db->where('date', $inclusive_date);
					$result = $this->db->get('employee_dtr');

					if($result && $result->num_rows() > 0)
						$this->db->update('employee_dtr', $saved_data, array("employee_id" => $exploded_employee, "date" => $inclusive_date));
					else
						$this->db->insert('employee_dtr', $saved_data);
				}
			}
		}

		$from_to_save = $this->_get_from_to($this->input->post('date_emp_added'));

		if($from_to_save && count($from_to_save) > 0)
			$this->db->update('workschedule_calendar', $from_to_save, array($this->key_field => $this->key_field_val));

		//additional module save routine here

	}

	protected function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			if ($this->input->post('record_id') == '-1') {
				$update['created_date'] = date('Y-m-d H:i:s');
				$update['created_by']   = $this->userinfo['user_id'];
			}

			$update['updated_date'] = date('Y-m-d H:i:s');
			$update['updated_by']   = $this->userinfo['user_id'];

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $update);

		}

		parent::after_ajax_save();
	}

	private function _get_inclusive_dates($strDateFrom = false,$strDateTo = false)
	{
	    // takes two dates formatted as YYYY-MM-DD and creates an
	    // inclusive array of the dates between the from and to dates.

		if($strDateFrom && $strDateTo)
		{
			$strDateFrom = date('Y-m-d', strtotime($strDateFrom));

			$strDateTo = date('Y-m-d', strtotime($strDateTo));

		    $aryRange=array();

		    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
		    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

		    if ($iDateTo>=$iDateFrom)
		    {
		        array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
		        while ($iDateFrom<$iDateTo)
		        {
		            $iDateFrom+=86400; // add 24 hours
		            array_push($aryRange,date('Y-m-d',$iDateFrom));
		        }
		    }
		    return $aryRange;
		} else
			return false;
	}

	private function _get_value($date_shifts = false, $delete = false, $from_to = false)
	{
		if($date_shifts)
		{
			$pieces = explode(',', $date_shifts);

			$obj = new StdClass();

			$returned_value = array();

			$ctr = 0;

			foreach($pieces as $piece)
			{
				if(trim($piece) != '')
				{
					if($from_to) {
						$removed_shift = explode('=', $piece);
						$dates = explode('/', $removed_shift[0]);
						$returned_value[$ctr] = date('Y-m-d', strtotime($dates[0]));
						$ctr++;
						$returned_value[$ctr] = date('Y-m-d', strtotime($dates[1]));
					} else {
						if(!$delete)
						{
							$shift_id = explode('=', $piece);
							$dates = explode('/', $shift_id[0]);

							$obj->shift_id = $shift_id[1];
							$obj->date_from = $dates[0];
							$obj->date_to = $dates[1];
							$returned_value[$ctr] = $obj;
						} else {
							$removed_shift = explode('=', $piece);
							$dates = explode('/', $removed_shift[0]);

							$obj->date_from = $dates[0];
							$obj->date_to = $dates[1];
							$returned_value[$ctr] = $obj;
						}

						unset($obj);
					}

					$ctr++;
				}
			}

			return $returned_value;
		} else
			return false;
	}

	private function _get_from_to($date_emp_added = "")
	{
		if($date_emp_added != "")
		{
			$data = $this->_get_value($date_emp_added,false,true);
			asort($data);
			$from_to['date_from'] = $data[0];
			rsort($data);
			$from_to['date_to'] = $data[0];
			return $from_to;
		} else
			return false;
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

		$like = array(
			'firstname' => $value,
			'lastname' => $value
			);

		$this->db->or_like($like);
		$users = $this->db->get('user');

		if ($users->num_rows() > 0) {
			foreach ($users->result() as $user) {
				$search_string[] = 'FIND_IN_SET("'.$user->user_id.'", wc.employee_id)';
				// $uid[] = $user->user_id;
			}

			
		}

		$search_string = '('. implode(' OR ', $search_string) .')';
		return $search_string;
	}

	function listview()
	{
		$this->load->helper('time_upload');		
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';

		$sql = " SELECT d.department AS department_name, 
       						d.*, 
       						wc.*, 
       						LENGTH(wc.employee_id) - LENGTH(REPLACE(wc.employee_id, ',', '')) + 1 AS employee_total
					 FROM {$this->db->dbprefix}workschedule_calendar wc
					 LEFT JOIN {$this->db->dbprefix}user u
					 ON u.employee_id = wc.employee_id
					 LEFT JOIN {$this->db->dbprefix}employee e
					 ON e.employee_id = wc.employee_id
					 LEFT JOIN {$this->db->dbprefix}user_company_department d
					 ON d.department_id = u.department_id
					 LEFT JOIN {$this->db->dbprefix}campaign c
					 ON c.campaign_id = e.campaign_id
					 WHERE wc.deleted = 0
					 AND {$search}
					 ";

		if(!$this->user_access[$this->module_id]['post'] && !$this->user_access[$this->module_id]['publish'])
			$sql .= 'AND wc.created_by = '.$this->userinfo['user_id'];

		$result = $this->db->query($sql);

		if( $this->db->_error_message() != "" ) {
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else {        

	        $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $result->num_rows();                        

	        $response->msg = "";

       		$sql = " SELECT d.department AS department_name, 
       						d.*, 
       						wc.*, 
       						c.campaign,
       						LENGTH(wc.employee_id) - LENGTH(REPLACE(wc.employee_id, ',', '')) + 1 AS employee_total
					 FROM {$this->db->dbprefix}workschedule_calendar wc
					 LEFT JOIN {$this->db->dbprefix}user u
					 ON u.employee_id = wc.employee_id
					 LEFT JOIN {$this->db->dbprefix}employee e
					 ON e.employee_id = wc.employee_id
					 LEFT JOIN {$this->db->dbprefix}user_company_department d
					 ON d.department_id = u.department_id
					 LEFT JOIN {$this->db->dbprefix}campaign c
					 ON c.campaign_id = e.campaign_id
					 WHERE wc.deleted = 0
					 AND {$search}
					 ";

			if(!$this->user_access[$this->module_id]['post'] && !$this->user_access[$this->module_id]['publish'])
				$sql .= 'AND wc.created_by = '.$this->userinfo['user_id'];

			if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');
	            $sql .= ' ORDER BY `' . $sidx . '` ' . $sord;
	        }

			$start = $limit * $page - $limit;	        

	        $sql .= " LIMIT {$start}, {$limit}";

	        // if(!empty( $this->filter ) ) $this->db->where( $this->filter );

	        $result = $this->db->query($sql);

	        $ctr = 0;

	        foreach ($result->result() as $row) {

	        	$response->rows[$ctr]['id'] = $row->{"workschedule_calendar_id"};
	            $response->rows[$ctr]['cell'][0] = $row->{"department_name"};
	            $response->rows[$ctr]['cell'][1] = $row->{"campaign"};
	            $response->rows[$ctr]['cell'][2] = ($row->date_from == null ? ' ' : date($this->config->item('display_date_format'), strtotime($row->{"date_from"})));
	            $response->rows[$ctr]['cell'][3] = ($row->date_from == null ? ' ' : date($this->config->item('display_date_format'), strtotime($row->{"date_to"})));
	            $pieces = explode(',', $row->employee_id);
	            $response->rows[$ctr]['cell'][4] = (count($pieces) > 4 ? "<u><a onClick='list_emp(".$row->workschedule_calendar_id.")' href='javascript:void(0)' style='color:blue'>".$row->employee_total."</a></u>" : $this->_get_names($pieces));
	            $response->rows[$ctr]['cell'][5] = $this->_default_grid_actions($this->module_link, $this->input->post('container'), $row);

	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
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
        
		// if ( $this->user_access[$this->module_id]['edit'] && date('Y-m-d') <= $record->date_to && $record->created_by == $this->user->user_id) {
        if ($this->user_access[$this->module_id]['edit'] && date('Y-m-d') <= $record->date_to && ($record->created_by == $this->user->user_id || $this->user_access[$this->module_id]['post'])) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 

        if ($this->user_access[$this->module_id]['delete'] && date('Y-m-d') <= $record->date_from && ($record->created_by == $this->user->user_id || $this->user_access[$this->module_id]['post'])) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

   	function _set_listview_query($listview_id = '', $view_actions = true) {

		$this->listview_column_names = array('Department', 'Campaign', 'Date From', 'Date To', 'Employees', 'Action');

		$this->listview_columns = array(

				array('name' => 'Department', 'width' => '180','align' => 'middle'),

				array('name' => 'Campaign', 'width' => '180','align' => 'middle'),

				array('name' => 'Date From', 'width' => '180','align' => 'middle'),

				array('name' => 'Date To', 'width' => '180','align' => 'middle'),

				array('name' => 'Employees', 'width' => '180','align' => 'middle'),

				array('name' => 'Action', 'width' => '180','align' => 'middle')
			);                                     
    }

    private function _get_names($ids = array())
    {
    	if(count($ids))
    	{
    		$this->db->select('CONCAT(firstname," ",middlename," ",lastname) AS full_name', false);
    		$this->db->where_in('employee_id', $ids);
    		$names = array_map('array_pop', $this->db->get('user')->result_array());
    		return implode(',', $names);
    	}
    }

	function delete()
	{
		
		$result = $this->db->get_where('workschedule_calendar', array('workschedule_calendar_id' => $this->input->post('record_id')))->row();

		$data = $this->_get_value($result->date_emp_added);

		$exploded_employees = explode(',', $result->employee_id);

		foreach($data as $datum)
		{
			$inclusive_dates = $this->_get_inclusive_dates($datum->date_from,$datum->date_to);

			foreach($inclusive_dates as $inclusive_date)
			{
				foreach($exploded_employees as $exploded_employee)
				{
					$saved_data = array( 'employee_id' => $exploded_employee,
										 'date' => $inclusive_date,
										 'shift_id' => -1
									    );

					$this->db->where('employee_id', $exploded_employee);
					$this->db->where('date', $inclusive_date);
					$result = $this->db->get('employee_dtr');

					if($result && $result->num_rows() > 0)
						$this->db->update('employee_dtr', $saved_data, array("employee_id" => $exploded_employee, "date" => $inclusive_date));
					else
						$this->db->insert('employee_dtr', $saved_data);
					
				}
			}
		}

		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function get_shift_listing()
	{		
		$status = $this->db->get('timekeeping_shift')->result();

		$status_html = "<option value='-1'>Please Select</option>";

		foreach( $status as $status_record ){
			$status_html .= "<option value='".$status_record->shift_id."'>".$status_record->shift."</option>";
		}

		$response->data = $status_html;

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_source_event($record_id = -1)
	{
		$super_response = array();
		$response = new StdClass();
		$ctr = 0;

		if($record_id != -1)
		{
			$result = $this->db->get_where('workschedule_calendar', array('workschedule_calendar_id' => $record_id,"deleted" => 0));

			if($result && $result->num_rows() > 0)
			{
				$result = $result->row();

				$data = $this->_get_value($result->date_emp_added);

				foreach($data as $datum)
				{
					if($datum->shift_id != '' && $datum->date_from != '' && $datum->date_to != '')
					{
						$shifts = $this->db->get_where('timekeeping_shift', array("shift_id" => $datum->shift_id))->row()->shift;

						$response->title = $shifts;
						$response->start = date('Y-m-d', strtotime($datum->date_from));
						$response->end = date('Y-m-d', strtotime($datum->date_to));
						$response->allDay = '1';
						$response->prev_start = false; // will be used if drag and drop is allowed
						$response->prev_end = false; // will be used if drag and drop is allowed
						$holiday_array->holiday_event = false;

						if(strtotime(date('Y-m-d')) > strtotime($datum->date_from) && strtotime(date('Y-m-d')) > strtotime($datum->date_to)) {
							$response->color = 'red'; 
							$response->holiday_event = true;
							$response->full_title = "Date is no longer editable";
						} else if(strtotime(date('Y-m-d')) >= strtotime($datum->date_from) && strtotime(date('Y-m-d')) <= strtotime($datum->date_to))
							$response->color = 'orange';
						else
							$response->color = 'green';

						$response->shift = $datum->shift_id;

						$super_response[$ctr] = $response;

						unset($response);

						$ctr++;
					}
				}

			}
		} 

		$super_response = $this->_add_holiday_on_source($super_response, $ctr);
		$this->load->view('template/ajax', array('json' => $super_response));

	}

	private function _add_holiday_on_source($response = false, $ctr = 0)
	{
		if(!$response)
			$response = array();
		
		$this->db->like('date_set', date('Y-'));
		$holidays = $this->db->get('holiday');

		if($holidays && $holidays->num_rows() > 0)
		{
			$holidays = $holidays->result();

			foreach($holidays as $holiday)
			{

				$holiday_array->title = (strlen($holiday->holiday) > 26 ? substr($holiday->holiday, 0, 25).'...' : $holiday->holiday );
				$holiday_array->full_title = $holiday->holiday;
				$holiday_array->start = date('Y-m-d', strtotime($holiday->date_set));
				$holiday_array->end = date('Y-m-d', strtotime($holiday->date_set));
				$holiday_array->allDay = '1';
				$holiday_array->color = 'pink';
				$holiday_array->textColor = 'blue';
				$holiday_array->holiday_event = true;

				$response[$ctr] = $holiday_array;

				unset($holiday_array);

				$ctr++;
			}

			return $response;
		} else 
			return $response;
	}

	function list_employee_affected()
	{
		if (!IS_AJAX) {
			header('HTTP/1.1 403 Forbidden');
		} else {

			$worksched = $this->db->get_where('workschedule_calendar', array("workschedule_calendar_id" => $this->input->post('workschedule_calendar_id')));

			if($worksched && $worksched->num_rows() > 0)
			{
				$emps = '<center><b>Employees:</b><br />';
				$employees = explode(',', $worksched->row()->employee_id);
				foreach($employees as $employee)
				{
					$this->db->select('CONCAT(u.firstname," ",u.middlename," ",u.lastname) as full_name', false);
					$emps .= '<i>'.$this->db->get_where('user u', array("employee_id" => $employee))->row()->full_name.'</i><br />';
				}
				$emps .= '</center>';
			}

			$response->data = $emps;

			$this->load->view('template/ajax', array('json' => $response));
		}
	}

	function employee_within()
	{
		$this->db->join('user','employee.employee_id = user.employee_id','left');

		if($this->input->post('department_id') != -1)
			$this->db->where('user.department_id', $this->input->post('department_id'));

		if($this->input->post('campaign') != -1)
			$this->db->where('employee.campaign_id', $this->input->post('campaign'));

		$users = $this->db->get('employee')->result_array();		

        foreach($users as $user){
            $html .= '<option value="'.$user["employee_id"].'">'.$user["firstname"].' '.$user["middlename"].' '.$user["lastname"].'</option>';
        }

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function onedit_emp_added()
	{
		$ws_id = $this->input->post('record_id');

		if($ws_id != -1)
		{
			$result = $this->db->get_where('workschedule_calendar', array('workschedule_calendar_id' => $ws_id))->row();

			$response->data = $result->date_emp_added;

			$response->employee_id = $result->employee_id;

			$this->load->view('template/ajax', array('json' => $response));	
		}
	}

	function fixed_get_campaign()
	{
		if($this->user_access['post'] != 1)
		{
			$emp_id = $this->userinfo['user_id'];

			$this->db->select('employee.*, campaign.campaign');

			$this->db->join('campaign', 'campaign.campaign_id = employee.campaign_id', 'left');

			$user = $this->db->get_where('employee', array('employee_id' => $emp_id))->row();

			$response->campaign = $user->campaign;

			$response->campaign_id = $user->campaign_id;

			$this->db->join('user','employee.employee_id = user.employee_id','left');
			$this->db->where('employee.campaign_id', $user->campaign_id);
			$employees_within = $this->db->get('employee');

			if($employees_within && $employees_within->num_rows() > 0)
			{
				foreach($employees_within->result() as $employee_within)
				{
					$response->employee_id = $employee_within->employee_id;
					$response->full_name = $employee_within->firstname.' '.$employee_within->middlename.' '.$employee_within->lastname;
				}
			}

			$this->load->view('template/ajax', array('json' => $response));	
		}
	}

	function no_post_employee_dropdown()
	{
		$emp_id = $this->userinfo['user_id'];

		$this->db->join('user', 'employee.employee_id = user.employee_id', 'left');
		$user_infos = $this->db->get_where('employee', array('hr_user.employee_id' => $emp_id))->row();

		$emp_subs = $this->hdicore->get_subordinates($user_infos->position_id, $user_infos->rank_id, $emp_id);


		foreach ($emp_subs as $user)
			$html .= '<option value="'.$user["employee_id"].'">'.$user["firstname"].' '.$user["middlename"].' '.$user["lastname"].'</option>';

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function remain_selected()
	{
		if($this->input->post('record_id') != -1)
		{
			$result = $this->db->get_where('workschedule_calendar', array('workschedule_calendar_id' => $this->input->post('record_id')))->row();

			$response->ids = $result->employee_id;

			$this->load->view('template/ajax', array('json' => $response));	
		}
	}

	function get_focus_calendar()
	{

		if($this->input->post('record_id') != -1)
		{
			$result = $this->db->get_where('workschedule_calendar', array('workschedule_calendar_id' => $this->input->post('record_id')));

			if($result && $result->num_rows() > 0) {
				$result = $result->row();
				$response->f_year = date('Y', strtotime($result->date_from));
				$response->f_month = date('m', strtotime($result->date_from)) - 1;
				$response->f_day = date('d', strtotime($result->date_from));
			} 

		} else {
			$response->f_year = date('Y');
			$response->f_month = date('m') - 1;
			$response->f_day = date('d');
		}

		$this->load->view('template/ajax', array('json' => $response));	

	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */