<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dtr_summary extends MY_Controller
{
	function __construct()
    {
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
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/dtr_summary/listview';
		$data['jqgrid'] = 'employee/dtr_summary/jqgrid';

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

		$data['department'] = $this->db->get('user_company_department')->result_array();

		if($this->user_access[$this->module_id]['post'] != 1) {
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');			
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}

		$data['w_subordinates'] = $subordinates;

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
				
	}
	
	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	// START custom module funtions

	function listview()
	{
		$this->load->helper('time_upload');

        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		$subordinates = 0;
		if($this->user_access[$this->module_id]['post'] != 1) {
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');	
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}

		//filter confidential employees viewing
		if (CLIENT_DIR == 'firstbalfour'){
			$this->db->where('deleted', 0);
			$this->db->where('confidential', 1);
			$segment_2 = $this->db->get('user_company_segment_2');

			$segment2_id = array();
			if ($segment_2->num_rows() > 0) {
				foreach ($segment_2->result() as $segment_2) {
					$segment2_id[] = $segment_2->segment_2_id;
				}
			}
		}
		
		$search = 1;			

		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.employee_id'.',restday,CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as employee_name', false);
		$this->db->select(''.$this->db->dbprefix. 'user_company_department.department'.'');
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.','.$this->db->dbprefix('employee_dtr'). '.hours_worked'.','.$this->db->dbprefix('employee_dtr'). '.excused_tardiness'.','.$this->db->dbprefix('employee_dtr'). '.lates_display'.','.$this->db->dbprefix('employee_dtr'). '.undertime'.','.$this->db->dbprefix('employee_dtr'). '.overtime'.'');
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",1)," ",-1) as date_in,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as time_in',false);
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",1)," ",-1) as date_out,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as time_out',false);
		$this->db->from('employee_dtr');
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");
		$this->db->join($this->db->dbprefix('employee_type'),$this->db->dbprefix('employee_type').'.employee_type_id = '.$this->db->dbprefix('employee').'.employee_type',"left");
		$this->db->join($this->db->dbprefix('employment_status'),$this->db->dbprefix('employment_status').'.employment_status_id = '.$this->db->dbprefix('employee').'.status_id',"left");

		if (CLIENT_DIR == 'firstbalfour'){
			if(count($segment2_id) > 0){
				// $this->db->join($this->db->dbprefix('employee_work_assignment'),$this->db->dbprefix('employee_work_assignment').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");		
				//filter confidential employees viewing
				if(!in_array($this->user->user_id, $this->config->item("can_view_confi_emp"))){
					$this->db->where( $this->db->dbprefix . 'user.segment_2_id NOT REGEXP ("(^|,)'.implode('|', $segment2_id) . '(,|$)")');
				}
			}
		}

		$this->db->where('employee_dtr.deleted = 0 AND '.$search);	
		$this->db->where('IF(resigned_date IS NULL, 1, `date` <= resigned_date)' );
		if ($subordinates == 0){
			$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
		}

		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) 
			$this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee')); 

		switch ($this->input->post('category1')) {
			case 2:
					$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
				break;
			case 3:
					$this->db->where('(hours_worked <= 4 AND overtime = 0 AND (lates + overtime) <= 4)', '', false);
					//$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked <=',"4");
				break;				
			case 4:
					$this->db->where($this->db->dbprefix('employee_dtr').'.lates_display >',"0");			
				break;	
			case 5:
					$this->db->where($this->db->dbprefix('employee_dtr').'.undertime >',"0");					
				break;
			case 6:
				$this->db->where($this->db->dbprefix('employee_dtr').'.overtime >',"0");
				break;
			case 7:
				$this->db->where($this->db->dbprefix('employee_dtr').'.awol !=',"0");
				break;						
			case 8:
					$this->db->where($this->db->dbprefix('employee_dtr').'.excused_tardiness >',"0");			
				break;														
		}

		if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
			$this->db->where('('.$this->db->dbprefix('employee_dtr').'.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" )');
		}

        $result = $this->db->get();   

		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{        
	        $total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
	        $response->page = $page > $total_pages ? $total_pages : $page;
	        $response->total = $total_pages;
	        $response->records = $result->num_rows();                        

	        $response->msg = "";

			$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.employee_id'.',restday,CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as employee_name', false);
			$this->db->select(''.$this->db->dbprefix. 'user_company_department.department'.'');
			$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.','.$this->db->dbprefix('employee_dtr'). '.hours_worked'.','.$this->db->dbprefix('employee_dtr'). '.excused_tardiness'.','.$this->db->dbprefix('employee_dtr'). '.lates_display'.','.$this->db->dbprefix('employee_dtr'). '.undertime'.','.$this->db->dbprefix('employee_dtr'). '.overtime'.'');
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",1)," ",-1) as date_in,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as time_in',false);
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",1)," ",-1) as date_out,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as time_out',false);
			$this->db->from('employee_dtr');
			$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id');
			$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
			$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");
			$this->db->join($this->db->dbprefix('employee_type'),$this->db->dbprefix('employee_type').'.employee_type_id = '.$this->db->dbprefix('employee').'.employee_type',"left");
			$this->db->join($this->db->dbprefix('employment_status'),$this->db->dbprefix('employment_status').'.employment_status_id = '.$this->db->dbprefix('employee').'.status_id',"left");			

			if (CLIENT_DIR == 'firstbalfour'){
				if(count($segment2_id) > 0){
					// $this->db->join($this->db->dbprefix('employee_work_assignment'),$this->db->dbprefix('employee_work_assignment').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");	
					//filter confidential employees viewing
					if(!in_array($this->user->user_id, $this->config->item("can_view_confi_emp"))){
						$this->db->where( $this->db->dbprefix . 'user.segment_2_id NOT REGEXP ("(^|,)'.implode('|', $segment2_id) . '(,|$)")');
					}	
				}
			}

			$this->db->where('employee_dtr.deleted = 0 AND '.$search);
			$this->db->where('IF(resigned_date IS NULL, 1, `date` <= resigned_date)' );

			if ($subordinates == 0){
				$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
			}

			if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       

	        if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.employee_type ', $this->input->post('employee_type'));       
	        if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.status_id ', $this->input->post('employment_status'));       

			switch ($this->input->post('category1')) {
				case 2:
						$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
					break;
				case 3:
						$this->db->where('(hours_worked <= 4 AND overtime = 0 AND (lates + overtime) <= 4)', '', false);
						//$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked <=',"4");
					break;				
				case 4:
						$this->db->where($this->db->dbprefix('employee_dtr').'.lates_display >',"0");			
					break;	
				case 5:
						$this->db->where($this->db->dbprefix('employee_dtr').'.undertime >',"0");					
					break;
				case 6:
					$this->db->where($this->db->dbprefix('employee_dtr').'.overtime >',"0");
					break;
				case 7:
					$this->db->where($this->db->dbprefix('employee_dtr').'.awol !=',"0");
					break;						
				case 8:
						$this->db->where($this->db->dbprefix('employee_dtr').'.excused_tardiness >',"0");			
					break;			
			}

			if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
				$this->db->where('('.$this->db->dbprefix('employee_dtr').'.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" )');
			}

	        if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');
	            if ($sidx == "absent"){
	            	$sidx = "hours_worked";
	            }
	            $this->db->order_by($sidx . ' ' . $sord);
	        } else {
	        	$this->db->order_by('user.firstname ASC');
	        	$this->db->order_by('employee_dtr.date ASC');
	        }

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        
	        
	        $result = $this->db->get();

	        $ctr = 0;
			$dummy_p->date_to = $this->input->post('dateStart');
			$dummy_p->date_from = $this->input->post('dateEnd');
			// echo $this->db->last_query();		        
	        foreach ($result->result() as $row) {
           		$day = date('D',strtotime($row->date));
           		$holiday = $this->system->holiday_check($row->date, $row->employee_id);
           		$array_stack = $this->system->get_employee_rest_day($row->employee_id,$row->date);
           		

				$obt = get_form($row->employee_id, 'obt', $dummy_p, $row->date, false);

				$remarks = "";
				if ($obt->num_rows() > 0)
					$remarks[] = "OBT";

				// Check leave for whole day
				$this->db->select('duration_id, employee_leaves.employee_leave_id');
				$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
				$this->db->where('employee_id', $row->employee_id);
				$this->db->where('(\''. $row->date . '\' BETWEEN date_from and date_to)', '', false);
				$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $row->date . '\')', '',false);
				$this->db->where('form_status_id', 3);
				$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
				$this->db->where('employee_leaves.deleted', 0);

				$leave = $this->db->get('employee_leaves');
				if ($leave->num_rows() > 0)
					$remarks[] = "LEAVE";

				$dtrp = get_form($row->employee_id, 'dtrp', $dummy_p, $row->date, false);
				if ($dtrp->num_rows() > 0)
					$remarks[] = "DTRP";

				$out = get_form($row->employee_id, 'out', null, $row->date, false);
				if ($out->num_rows() > 0)
					$remarks[] = "OUT";

				$et = get_form($row->employee_id, 'et', null, $row->date, false);
				if ($et->num_rows() > 0)
					$remarks[] = "ET";

				$oot = get_form($row->employee_id, 'oot', null, $row->date, false);
				if ($oot->num_rows() > 0)
					$remarks[] = "OT";

				if ($row->restday == 1){
					$remarks[] = "RESTDAY";
				}

				if ($holiday){
					$remarks[] = "HOLIDAY";
				}

				$restday = false;
           		if (in_array($day, $array_stack)){
           			$restday = true;
           		}

	            $absent = 0;
	            if (!$holiday && !$row->restday){
		            if ($row->hours_worked == 0 && number_format($row->overtime / 60,2) == 0){
						$absent = 8;
		            }
		            elseif ($row->hours_worked <= 4 && $row->hours_worked > 0){
		            	if ((number_format($row->lates / 60,2) + number_format($row->undertime / 60,2)) <= 4){
							$absent = 4;
		            	}
		            }
	        	}
	            $response->rows[$ctr]['cell'][0] = $row->employee_name;
	            $response->rows[$ctr]['cell'][1] = date($this->config->item('display_date_format'),strtotime($row->date));
	            $response->rows[$ctr]['cell'][2] = ($row->time_in != '' ? date('h:i:s a', strtotime($row->time_in)): '');
	            $response->rows[$ctr]['cell'][3] = ($row->time_out != '' ? date('h:i:s a', strtotime($row->time_out)): '');
	            $response->rows[$ctr]['cell'][4] = $row->hours_worked;
	            $response->rows[$ctr]['cell'][5] = $absent;
	            $response->rows[$ctr]['cell'][6] = number_format($row->excused_tardiness / 60,2);
	            $response->rows[$ctr]['cell'][7] = number_format($row->lates_display / 60,2);
	            $response->rows[$ctr]['cell'][8] = number_format($row->undertime / 60,2);
	            $response->rows[$ctr]['cell'][9] = number_format($row->overtime / 60,2);
	            $response->rows[$ctr]['cell'][10] = implode('/', $remarks);
	            //$response->rows[$ctr]['cell'][7] = $shit_sched->shift;
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Employee Name', 'Date', 'IN', 'OUT', 'Hours Worked', 'Absent', 'ET (Hours)', 'Lates (Hours)', 'UT (Hours)', 'OT (Hours)',"Remarks"); //, 'Work Shift'

		$this->listview_columns = array(
				array('name' => 'employee_name', 'width' => '180','align' => 'center'),				
				array('name' => 'date'),
				array('name' => 'time_in1'),
				array('name' => 'time_out1'),
				array('name' => 'hours_worked'),
				array('name' => 'absent'),
				array('name' => 'excused_tardiness'),
				array('name' => 'lates_display'),
				array('name' => 'undertime'),
				array('name' => 'overtime'),
				array('name' => 'remarks')
				//array('name' => 'workshift')
			);                                     
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

	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" )
	{
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($addtext == "") $addtext = "Add";
		if($deltext == "") $deltext = "Delete";
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
                            
        if ($this->user_access[$this->module_id]['add']) {
            $buttons .= "<div class='icon-label'>";
            $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            $buttons .= "<span>".$addtext."</span></a></div>";
        }
         
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }

        if ($this->user_access[$this->module_id]['post']) {
	        if ( get_export_options( $this->module_id ) ) {
	            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
	            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
	        }        
    	}
        
        $buttons .= "</div>";

        $buttons="";
                
		return $buttons;
	}

    function populate_category()
    {
        $html = '';
        switch ($this->input->post('category_id')) {
            case 0:
                $html .= '';    
                break;
            case 1: // company
                $this->db->where('deleted', 0);
                $company = $this->db->get('user_company')->result_array();      
                $html .= '<select id="user_company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 2: // division
                $this->db->where('deleted', 0);
                $division = $this->db->get('user_company_division')->result_array();        
                $html .= '<select id="user_company_division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 3: // department
                $this->db->where('deleted', 0);
                $department = $this->db->get('user_company_department')->result_array();        
                $html .= '<select id="user_company_department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';               
                break;                                          
            case 4: // section
                $this->db->where('deleted', 0);
                $company = $this->db->get('user_section')->result_array();      
                $html .= '<select id="user_section" multiple="multiple" class="multi-select" style="width:400px;" name="section[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["section_id"].'">'.$company_record["section"].'</option>';
                    }
                $html .= '</select>';   
                break;   
            case 5: // level
                $this->db->where('deleted', 0);
                $employee_type = $this->db->get('employee_type')->result_array();       
                $html .= '<select id="employee_type" multiple="multiple" class="multi-select" style="width:400px;" name="employee_type[]">';
                    foreach($employee_type as $employee_type_record){
                        $html .= '<option value="'.$employee_type_record["employee_type_id"].'">'.$employee_type_record["employee_type"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 6: // employment status
                $this->db->where('deleted', 0);
                $employment_status = $this->db->get('employment_status')->result_array();       
                $html .= '<select id="employment_status" multiple="multiple" class="multi-select" style="width:400px;" name="employment_status[]">';
                    foreach($employment_status as $employment_status_record){
                        $html .= '<option value="'.$employment_status_record["employment_status_id"].'">'.$employment_status_record["employment_status"].'</option>';
                    }
                $html .= '</select>';   
                break;                                 
            case 7: // employee
                $this->db->where('user.deleted', 0);
                $this->db->where('user.inactive', 0);
                $this->db->join('employee', 'employee.employee_id = user.employee_id');
                $employee = $this->db->get('user')->result_array();     
                $html .= '<select id="user" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                $html .= '</select>';   
                break;  
        }       

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
    }

	function get_employees()
	{
		if (IS_AJAX)
		{
			$html = '';
			if ($this->input->post('category_id') != 'null') {
                switch ($this->input->post('category')) {
                    case 0:
                        $html .= '';    
                        break;
                    case 1: // company
                        $where = 'user.company_id IN ('.$this->input->post('category_id').')';
                        break;
                    case 2: // division
                        $where = 'user.division_id IN ('.$this->input->post('category_id').')';
                        break;
                    case 3: // department
                        $where = 'user.department_id IN ('.$this->input->post('category_id').')';
                        break;  
                    case 4: // section
                        $where = 'user.section_id IN ('.$this->input->post('category_id').')';
                        break;                      
                    case 5: // level
                        $where = 'employee_type IN ('.$this->input->post('category_id').')';
                        break;
                    case 6: // employment status
                        $where = 'status_id IN ('.$this->input->post('category_id').')';
                        break;                                                                                                      
                }	
				$this->db->where($where);
                $this->db->where('user.deleted', 0);
                $this->db->join('employee','user.employee_id = employee.employee_id');
				$result = $this->db->get('user');		

                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';

                if ($result && $result->num_rows() > 0){
                    $employee = $result->result_array();
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                }
                
                $html .= '</select>';  
			}

            $data['html'] = $html;
    		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function get_employee_time_record(){
		$html = '';
		$this->db->where('deleted',0);		
		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1:
				$company = $this->db->get('user_company')->result_array();		
                $html .= '<select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2:
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 3:
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;		        
		    case 4:
				$employee = $this->db->get('user')->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	if ($employee_record["firstname"] != "Super Admin"){
                        	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    	}
                    }
                $html .= '</select>';	
		        break;
		    case 5:
		    	$this->db->where('deleted',0);
				$project = $this->db->get('project_name')->result_array();		
                $html .= '<select id="project" multiple="multiple" class="multi-select" style="width:400px;" name="project[]">';
                    foreach($project as $project_record){
                        $html .= '<option value="'.$project_record["project_name_id"].'">'.$project_record["project_name"].'</option>';
                    }
                $html .= '</select>';	
		        break;		        	        		        		        		        
		}	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function export() {	
		$this->_excel_export();
	}

	// export called using ajax
	function excel_ajax_export()
	{	
		$meta = $this->hdicore->_get_meta();
		ini_set('memory_limit', "512M");
		$this->load->helper('time_upload');
		$subordinates = 0;
		if($this->user_access[$this->module_id]['post'] != 1) {
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');	
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}
		
		//filter confidential employees viewing
		if (CLIENT_DIR == 'firstbalfour'){
			$this->db->where('deleted', 0);
			$this->db->where('confidential', 1);
			$segment_2 = $this->db->get('user_company_segment_2');

			$segment2_id = array();
			if ($segment_2->num_rows() > 0) {
				foreach ($segment_2->result() as $segment_2) {
					$segment2_id[] = $segment_2->segment_2_id;
				}
			}
		}

		$search = 1;

		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.employee_id'.','.$this->db->dbprefix('employee'). '.id_number AS "Employee ID"', false);
		$this->db->select('CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as "Employee Name"', false);
		$this->db->select(''.$this->db->dbprefix('employment_status'). '.employment_status AS "Employment Status"', false);
		$this->db->select(''.$this->db->dbprefix('employee_type'). '.employee_type AS "Employee Type"', false);
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.' as "Date"');		
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as "IN"',false);
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as "OUT"',false);
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.hours_worked'.' as  "Hours Worked",'.$this->db->dbprefix('employee_dtr'). '.excused_tardiness'.' as "ET(Hours)",'.$this->db->dbprefix('employee_dtr'). '.lates_display'.' as "Lates(Hours)",'.$this->db->dbprefix('employee_dtr'). '.undertime'.' as "UT(Hours)",'.$this->db->dbprefix('employee_dtr'). '.overtime'.' as "OT(Hours)"');		
		$this->db->from('employee_dtr');
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id', 'left');
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id', 'left');
		$this->db->join($this->db->dbprefix('employment_status'),$this->db->dbprefix('employee').'.status_id = '.$this->db->dbprefix('employment_status').'.employment_status_id', 'left');
		$this->db->join($this->db->dbprefix('employee_type'),$this->db->dbprefix('employee').'.employee_type = '.$this->db->dbprefix('employee_type').'.employee_type_id', 'left');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->where('employee_dtr.deleted = 0 AND '.$search);	

		if (CLIENT_DIR == 'firstbalfour'){
			if(count($segment2_id) > 0){
				// $this->db->join($this->db->dbprefix('employee_work_assignment'),$this->db->dbprefix('employee_work_assignment').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");		
				//filter confidential employees viewing
				if(!in_array($this->user->user_id, $this->config->item("can_view_confi_emp"))){
					$this->db->where( $this->db->dbprefix . 'user.segment_2_id NOT REGEXP ("(^|,)'.implode('|', $segment2_id) . '(,|$)")');
				}
			}
		}

		if ($subordinates == 0){
			$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
		}

		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) 
			$this->db->where_in($this->db->dbprefix('user').'.employee_id ',$this->input->post('employee'));		

		switch ($this->input->post('category1')) {
			case 2:
					$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
				break;
			case 3:
					$this->db->where('(hours_worked <= 4 AND overtime = 0 AND (lates + overtime) <= 4)', '', false);
				break;				
			case 4:
					$this->db->where($this->db->dbprefix('employee_dtr').'.lates_display >',"0");			
				break;	
			case 5:
					$this->db->where($this->db->dbprefix('employee_dtr').'.undertime >',"0");					
				break;
			case 6:
				$this->db->where($this->db->dbprefix('employee_dtr').'.overtime >',"0");
				break;				
			case 8:
					$this->db->where($this->db->dbprefix('employee_dtr').'.excused_tardiness >',"0");			
				break;																
		}

		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			$this->db->where('('.$this->db->dbprefix('employee_dtr').'.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'" )');
		}

        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            if ($sidx == "absent"){
            	$sidx = "hours_worked";
            }
            $this->db->order_by($sidx . ' ' . $sord);
        }  else {
        	$this->db->order_by('user.firstname ASC');
        	$this->db->order_by('employee_dtr.date ASC');
        }

		$q = $this->db->get();


		$query  = $q;
		$fields = $q->list_fields();

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("DTR Summary Report")
		            ->setDescription("DTR Summary Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);					
		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);					

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$leftstyleArray = array(
			'font' => array(
				'italic' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		if ($this->input->post('dynamic')){
			switch ($this->input->post('category1')) {
				case 2:
						$fields = array_merge(array_diff($fields, array("Absent","ET(Hours)","Lates(Hours)","UT(Hours)","OT(Hours)")));				
					break;
				case 3:
						$fields = array_merge(array_diff($fields, array("Hours Worked","ET(Hours)","Lates(Hours)","UT(Hours)","OT(Hours)")));				
					break;					
				case 4:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","UT(Hours)","OT(Hours)")));				
					break;
				case 5:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","ET(Hours)","Lates(Hours)","OT(Hours)")));				
					break;															
				case 6:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","ET(Hours)","Lates(Hours)","UT(Hours)")));				
					break;					
			}			
		}
		else{
			array_splice($fields, 6, 0, "Absent");			
		}

		unset($fields[0]);
		$fields[] = "Remarks";
		foreach ($fields as $field) {
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			$activeSheet->setCellValue($xcoor . '6', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', $meta['title']);
		$activeSheet->setCellValue('A2', 'DTR Summary Report');
		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			$activeSheet->setCellValue('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($leftstyleArray);

		// contents.
		$line = 7;
		foreach ($query->result() as $row) {
       		$day = date('D',strtotime($row->Date));
       		$holiday = $this->system->holiday_check($row->Date, $row->employee_id);
       		$array_stack = $this->system->get_employee_rest_day($row->employee_id,$row->Date);
       		$restday = false;
       		if (in_array($day, $array_stack)){
       			$restday = true;
       		}	
			$obt = get_form($row->employee_id, 'obt', $dummy_p, $row->Date, false);

			$remarks = "";
			if ($obt->num_rows() > 0)
				$remarks[] = "OBT";

			// Check leave for whole day
			$this->db->select('duration_id, employee_leaves.employee_leave_id');
			$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
			$this->db->where('employee_id', $row->employee_id);
			$this->db->where('(\''. $row->Date . '\' BETWEEN date_from and date_to)', '', false);
			$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $row->Date . '\')', '',false);
			$this->db->where('form_status_id', 3);
			$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
			$this->db->where('employee_leaves.deleted', 0);

			$leave = $this->db->get('employee_leaves');
			if ($leave->num_rows() > 0)
				$remarks[] = "LEAVE";

			$dtrp = get_form($row->employee_id, 'dtrp', $dummy_p, $row->Date, false);
			if ($dtrp->num_rows() > 0)
				$remarks[] = "DTRP";

			$out = get_form($row->employee_id, 'out', null, $row->Date, false);
			if ($out->num_rows() > 0)
				$remarks[] = "OUT";

			$et = get_form($row->employee_id, 'et', null, $row->Date, false);
			if ($et->num_rows() > 0)
				$remarks[] = "ET";       	

			$et = get_form($row->employee_id, 'oot', null, $row->Date, false);
			if ($et->num_rows() > 0)
				$remarks[] = "OT";	

			$sub_ctr   = 0;			
			$alpha_ctr = 0;
			foreach ($fields as $field) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

	            $absent = 0;
	            if (!$holiday && !$row->restday){	            
		            if ($row->{'Hours Worked'} == 0 && number_format($row->{'OT(Hours)'} / 60,2) == 0){
						$absent = 8;
		            }
		            elseif ($row->{'Hours Worked'} <= 4 && $row->{'Hours Worked'} > 0){
		            	if ((number_format($row->{'Lates(Hours)'} / 60,2) + number_format($row->{'UT(Hours)'} / 60,2)) <= 4){
							$absent = 4;
		            	}
		            }
	        	}

	           	if ($field != "Absent" && $field != "Remarks"){
	           		if ($field == "Lates(Hours)" || $field == "UT(Hours)" || $field == "OT(Hours)"){
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($row->{$field} / 60,2));
	           		}
	           		elseif ($field == "Date"){
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, date($this->config->item('display_date_format'),strtotime($row->{$field})));
					}
					else{
						$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});
					}
	           	}
	           	elseif ($field == "Absent"){	           		
	           		$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $absent);
	           	}
				elseif ($field == "Remarks"){
					$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, implode('/', $remarks));
				}
				//$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});

				$alpha_ctr++;
			}

			$line++;
		}

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.date('Y-m-d').'_'.url_title("DTR Summary Report").'.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/dtr_summary/'.url_title("DTR Summary Report").'-'.date('Y-m-d').'.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));

		
	}	

	private function _excel_export($record_id = 0)
	{	
		$this->load->helper('time_upload');
		$subordinates = 0;
		if($this->user_access[$this->module_id]['post'] != 1) {
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');	
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}
		
		//filter confidential employees viewing
		if (CLIENT_DIR == 'firstbalfour'){
			$this->db->where('deleted', 0);
			$this->db->where('confidential', 1);
			$segment_2 = $this->db->get('user_company_segment_2');

			$segment2_id = array();
			if ($segment_2->num_rows() > 0) {
				foreach ($segment_2->result() as $segment_2) {
					$segment2_id[] = $segment_2->segment_2_id;
				}
			}
		}

		$search = 1;

		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.employee_id'.',restday,CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as "Employee Name"', false);
		$this->db->select(''.$this->db->dbprefix('employee'). '.id_number'.' as "ID Number"');		
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.' as "Date"');		
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as "IN"',false);
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as "OUT"',false);
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.hours_worked'.' as  "Hours Worked",'.$this->db->dbprefix('employee_dtr'). '.excused_tardiness'.' as "ET(Hours)",'.$this->db->dbprefix('employee_dtr'). '.lates_display'.' as "Lates(Hours)",'.$this->db->dbprefix('employee_dtr'). '.undertime'.' as "UT(Hours)",'.$this->db->dbprefix('employee_dtr'). '.overtime'.' as "OT(Hours)"');		
		$this->db->from('employee_dtr');
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");
		$this->db->join($this->db->dbprefix('employment_status'),$this->db->dbprefix('employee').'.status_id = '.$this->db->dbprefix('employment_status').'.employment_status_id', 'left');
		$this->db->join($this->db->dbprefix('employee_type'),$this->db->dbprefix('employee').'.employee_type = '.$this->db->dbprefix('employee_type').'.employee_type_id', 'left');		
		$this->db->where('employee_dtr.deleted = 0 AND '.$search);
		$this->db->where('IF(resigned_date IS NULL, 1, `date` <= resigned_date)');	

		if (CLIENT_DIR == 'firstbalfour'){
			if(count($segment2_id) > 0){
				// $this->db->join($this->db->dbprefix('employee_work_assignment'),$this->db->dbprefix('employee_work_assignment').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");		
				//filter confidential employees viewing
				if(!in_array($this->user->user_id, $this->config->item("can_view_confi_emp"))){
					$this->db->where( $this->db->dbprefix . 'user.segment_2_id NOT REGEXP ("(^|,)'.implode('|', $segment2_id) . '(,|$)")');
				}
			}
		}
		
		if ($subordinates == 0){
			$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
		}

		if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) 
			$this->db->where_in($this->db->dbprefix('user').'.employee_id ',$this->input->post('employee'));		

        if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.employee_type ', $this->input->post('employee_type'));       
        if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.status_id ', $this->input->post('employment_status'));       
        
		switch ($this->input->post('category1')) {
			case 2:
					$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
				break;
			case 3:
					$this->db->where('(hours_worked <= 4 AND overtime = 0 AND (lates + overtime) <= 4)', '', false);
				break;				
			case 4:
					$this->db->where($this->db->dbprefix('employee_dtr').'.lates_display >',"0");			
				break;	
			case 5:
					$this->db->where($this->db->dbprefix('employee_dtr').'.undertime >',"0");					
				break;
			case 6:
				$this->db->where($this->db->dbprefix('employee_dtr').'.overtime >',"0");
				break;				
			case 8:
					$this->db->where($this->db->dbprefix('employee_dtr').'.excused_tardiness >',"0");			
				break;																
		}

		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			$this->db->where('('.$this->db->dbprefix('employee_dtr').'.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'" )');
		}

        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            if ($sidx == "absent"){
            	$sidx = "hours_worked";
            }
            $this->db->order_by($sidx . ' ' . $sord);
        }  
        else {
        	$this->db->order_by('user.firstname ASC');
        	$this->db->order_by('employee_dtr.date ASC');
        }         

		$q = $this->db->get();
		
		$query  = $q;
		$fields = $q->list_fields();

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("DTR Summary Report")
		            ->setDescription("DTR Summary Report");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);					
		//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);					

		//Initialize style
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		if ($this->input->post('dynamic')){
			switch ($this->input->post('category1')) {
				case 2:
						$fields = array_merge(array_diff($fields, array("Absent","Lates(Hours)","UT(Hours)","OT(Hours)")));				
					break;
				case 3:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Lates(Hours)","UT(Hours)","OT(Hours)")));				
					break;					
				case 4:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","UT(Hours)","OT(Hours)")));				
					break;
				case 5:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","Lates(Hours)","OT(Hours)")));				
					break;															
				case 6:
						$fields = array_merge(array_diff($fields, array("Hours Worked","Absent","Lates(Hours)","UT(Hours)")));				
					break;					
			}			
		}
		else{
			array_splice($fields, 6, 0, "Absent");			
		}

		unset($fields[0]);
		unset($fields[1]);
		$fields[] = "Remarks";
		foreach ($fields as $field) {
			if ($alpha_ctr >= count($alphabet)) {
				$alpha_ctr = 0;
				$sub_ctr++;
			}

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}

			$activeSheet->setCellValueExplicit($xcoor . '6', $field, PHPExcel_Cell_DataType::TYPE_STRING); 


			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValueExplicit('A2', 'DTR Summary Report', PHPExcel_Cell_DataType::TYPE_STRING); 


		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			
			$activeSheet->setCellValueExplicit('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))), PHPExcel_Cell_DataType::TYPE_STRING); 
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;
		foreach ($query->result() as $row) {
       		$day = date('D',strtotime($row->Date));
       		$holiday = $this->system->holiday_check($row->Date, $row->employee_id);
       		$array_stack = $this->system->get_employee_rest_day($row->employee_id,$row->Date);
       		$restday = false;
       		if (in_array($day, $array_stack)){
       			$restday = true;
       		}	
			$obt = get_form($row->employee_id, 'obt', $dummy_p, $row->Date, false);

			$remarks = "";
			// if ($obt->num_rows() > 0)
			// 	$remarks = "obt";
			if ($obt->num_rows() > 0) {
				$remarks[] = "OBT";
			}

			// Check leave for whole day
			$this->db->select('duration_id, employee_leaves.employee_leave_id');
			$this->db->join('employee_leaves_dates', 'employee_leaves_dates.employee_leave_id = employee_leaves.employee_leave_id', 'left');
			$this->db->where('employee_id', $row->employee_id);
			$this->db->where('(\''. $row->Date . '\' BETWEEN date_from and date_to)', '', false);
			$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.date = \'' . $row->Date . '\')', '',false);
			$this->db->where('form_status_id', 3);
			$this->db->where('IFNULL(blanket_id, ' . $this->db->dbprefix .'employee_leaves_dates.deleted = 0)', '', false);
			$this->db->where('employee_leaves.deleted', 0);

			$leave = $this->db->get('employee_leaves');
			// if ($leave->num_rows() > 0)
			// 	$remarks = "leave";

			// $dtrp = get_form($row->employee_id, 'dtrp', $dummy_p, $row->Date, false);
			// if ($dtrp->num_rows() > 0)
			// 	$remarks = "dtrp";

			// $out = get_form($row->employee_id, 'out', null, $row->Date, false);
			// if ($out->num_rows() > 0)
			// 	$remarks = "out";

			// $et = get_form($row->employee_id, 'et', null, $row->Date, false);
			// if ($et->num_rows() > 0)
			// 	$remarks = "et";   

			// if ($row->restday == 1){
			// 	$remarks = "restday";
			// }
			if ($leave->num_rows() > 0) {
				$remarks[] = "LEAVE";
			}

			$dtrp = get_form($row->employee_id, 'dtrp', $dummy_p, $row->Date, false);
			if ($dtrp->num_rows() > 0) {
				$remarks[] = "DTRP";
			}

			$out = get_form($row->employee_id, 'out', null, $row->Date, false);
			if ($out->num_rows() > 0) {
				$remarks[] = "OUT";
			}

			$et = get_form($row->employee_id, 'et', null, $row->Date, false);
			if ($et->num_rows() > 0) {
				$remarks[] = "ET";
			}

			$oot = get_form($row->employee_id, 'oot', null, $row->Date, false);
			if ($oot->num_rows() > 0) {
				$remarks[] = "OT";
			}

			if ($row->restday == 1){
				$remarks[] = "RESTDAY";
			}

			if ($holiday){
				$remarks[] = "HOLIDAY";
			}

			$sub_ctr   = 0;			
			$alpha_ctr = 0;
			foreach ($fields as $field) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

	            $absent = 0;
	            if (!$holiday && !$row->restday){	            
		            if ($row->{'Hours Worked'} == 0 && number_format($row->{'OT(Hours)'} / 60,2) == 0){
						$absent = 8;
		            }
		            elseif ($row->{'Hours Worked'} <= 4 && $row->{'Hours Worked'} > 0){
		            	if ((number_format($row->{'Lates(Hours)'} / 60,2) + number_format($row->{'UT(Hours)'} / 60,2)) <= 4){
							$absent = 4;
		            	}
		            }
	        	}
	           	if ($field != "Absent" && $field != "Remarks"){
	           		if ($field == "ET(Hours)" || $field == "Lates(Hours)" || $field == "UT(Hours)" || $field == "OT(Hours)"){

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, number_format($row->{$field} / 60,2), PHPExcel_Cell_DataType::TYPE_STRING); 
	           		}
	           		elseif ($field == "Date"){
	           			$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date($this->config->item('display_date_format'),strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 
	
					}
					else{

						$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 
					}
	           	}
	           	elseif ($field == "Absent"){
	           		$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $absent, PHPExcel_Cell_DataType::TYPE_STRING); 
	           	}
				elseif ($field == "Remarks"){
					$remarks = implode('/', $remarks);
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $remarks, PHPExcel_Cell_DataType::TYPE_STRING); 
				}
				if($field == 'IN') {
					$in = '';
					if(!empty($row->{$field})) {
						$in = date('h:i:s a', strtotime($row->{$field}));
					}
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $in, PHPExcel_Cell_DataType::TYPE_STRING); 
				}
				if($field == 'OUT') {
					$out = '';
					if(!empty($row->{$field})) {
						$out = date('h:i:s a', strtotime($row->{$field}));
					}
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $out, PHPExcel_Cell_DataType::TYPE_STRING); 
				}
				//$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});

				$alpha_ctr++;
			}

			$line++;
		}

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.date('Y-m-d').'_'.url_title("DTR Summary Report").'.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
	}		
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>