<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ot_summary extends MY_Controller
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
		$data['content'] = 'dtr/ot_summary/listview';

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
		
		$search = 1;			

		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.employee_id'.',CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as employee_name', false);
		$this->db->select(''.$this->db->dbprefix. 'user_company_department.department'.'');
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.','.$this->db->dbprefix('employee_dtr'). '.hours_worked'.','.$this->db->dbprefix('employee_dtr'). '.lates'.','.$this->db->dbprefix('employee_dtr'). '.undertime'.','.$this->db->dbprefix('employee_dtr'). '.overtime'.'');
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",1)," ",-1) as date_in,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as time_in',false);
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",1)," ",-1) as date_out,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as time_out',false);
		$this->db->from('employee_dtr');
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");
		$this->db->where('employee_dtr.deleted = 0 AND '.$search);	
		$this->db->where('IF(resigned_date IS NULL, 1, `date` <= resigned_date)' );
		if ($subordinates == 0){
			$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
		}

		switch ($this->input->post('category')) {
			case 1:
					if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.company_id ',$this->input->post('company'));
				break;
			case 2:
					if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.division_id ',$this->input->post('division'));		
				break;
			case 3:
					if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.department_id ',$this->input->post('department'));
				break;
			case 4:
					if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ',$this->input->post('employee'));		
				break;								
		}

		switch ($this->input->post('category1')) {
			case 2:
					$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
				break;
			case 3:
					$this->db->where('(hours_worked <= 4 AND overtime = 0 AND (lates + overtime) <= 4)', '', false);
					//$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked <=',"4");
				break;				
			case 4:
					$this->db->where($this->db->dbprefix('employee_dtr').'.lates >',"0");			
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
		}

		if( $this->input->post('dateStart') && $this->input->post('dateEnd') ){
			$this->db->where('('.$this->db->dbprefix('employee_dtr').'.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('dateStart'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('dateEnd'))).'" )');
		}

        $result = $this->db->get();   
        //$response->last_query = $this->db->last_query();
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

			$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.employee_id'.',CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as employee_name', false);
			$this->db->select(''.$this->db->dbprefix. 'user_company_department.department'.'');
			$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.','.$this->db->dbprefix('employee_dtr'). '.hours_worked'.','.$this->db->dbprefix('employee_dtr'). '.lates'.','.$this->db->dbprefix('employee_dtr'). '.undertime'.','.$this->db->dbprefix('employee_dtr'). '.overtime'.'');
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",1)," ",-1) as date_in,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as time_in',false);
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",1)," ",-1) as date_out,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as time_out',false);
			$this->db->from('employee_dtr');
			$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id');
			$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
			$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");
			$this->db->where('employee_dtr.deleted = 0 AND '.$search);
			$this->db->where('IF(resigned_date IS NULL, 1, `date` <= resigned_date)' );

			if ($subordinates == 0){
				$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
			}

			switch ($this->input->post('category')) {
				case 1:
						if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.company_id ',$this->input->post('company'));
					break;
				case 2:
						if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.division_id ',$this->input->post('division'));		
					break;
				case 3:
						if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.department_id ',$this->input->post('department'));
					break;
				case 4:
						if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ',$this->input->post('employee'));		
					break;								
			}

			switch ($this->input->post('category1')) {
				case 2:
						$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
					break;
				case 3:
						$this->db->where('(hours_worked <= 4 AND overtime = 0 AND (lates + overtime) <= 4)', '', false);
						//$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked <=',"4");
					break;				
				case 4:
						$this->db->where($this->db->dbprefix('employee_dtr').'.lates >',"0");			
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
	        //$response->last_query = $this->db->last_query();
	        $ctr = 0;
			$dummy_p->date_to = $this->input->post('dateStart');
			$dummy_p->date_from = $this->input->post('dateEnd');		        
	        foreach ($result->result() as $row) {
           		$day = date('D',strtotime($row->date));
           		$holiday = $this->system->holiday_check($row->date, $row->employee_id);
           		$array_stack = $this->system->get_employee_rest_day($row->employee_id,$row->date);
           		$restday = false;
           		if (in_array($day, $array_stack)){
           			$restday = true;
           		}

	            $absent = 0;
	            if (!$holiday && !$restday){
		            if ($row->hours_worked == 0 && number_format($row->overtime / 60,2) == 0){
						$absent = 8;
		            }
		            elseif ($row->hours_worked <= 4 && $row->hours_worked > 0){
		            	if ((number_format($row->lates / 60,2) + number_format($row->undertime / 60,2)) <= 4){
							$absent = 4;
		            	}
		            }
	        	}

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

				$et = get_form($row->employee_id, 'oot', null, $row->date, false);
				if ($et->num_rows() > 0)
					$remarks[] = "OT";

				$mod_absent = $absent;

				//tirso - for hdi only
				if (CLIENT_DIR == "hdi"){
					if (($row->time_in != '00:00:00' && $row->time_out == '00:00:00') || ($row->time_in == '00:00:00' && $row->time_out != '00:00:00')){
						$mod_absent = 4;
					}

					if (number_format($row->lates / 60,2) >= 2){
						if ($row->hours_worked >= 4){
							$mod_absent = 4;
						}
						else{
							$mod_absent = 8;
						}
					}	

					if (number_format($row->undertime / 60,2) >= 2){
						if ($row->hours_worked >= 4){
							$mod_absent = 4;
						}
						else{
							$mod_absent = 8;
						}
					}					
				}

	            $response->rows[$ctr]['cell'][0] = $row->employee_name;
	            $response->rows[$ctr]['cell'][1] = date($this->config->item('display_date_format'),strtotime($row->date));
	            $response->rows[$ctr]['cell'][2] = $row->time_in;
	            $response->rows[$ctr]['cell'][3] = $row->time_out;
	            $response->rows[$ctr]['cell'][4] = $row->hours_worked;
	            $response->rows[$ctr]['cell'][5] = $mod_absent;
	            $response->rows[$ctr]['cell'][6] = number_format($row->lates / 60,2);
	            $response->rows[$ctr]['cell'][7] = number_format($row->undertime / 60,2);
	            $response->rows[$ctr]['cell'][8] = number_format($row->overtime / 60,2);
	            $response->rows[$ctr]['cell'][9] = implode('/', $remarks);
	            //$response->rows[$ctr]['cell'][7] = $shit_sched->shift;
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Employee Name', 'Date', 'IN', 'OUT', 'Hours Worked', 'Absent', 'Lates (Hours)', 'UT (Hours)', 'OT (Hours)',"Remarks"); //, 'Work Shift'

		$this->listview_columns = array(
				array('name' => 'employee_name', 'width' => '180','align' => 'center'),				
				array('name' => 'date'),
				array('name' => 'time_in1'),
				array('name' => 'time_out1'),
				array('name' => 'hours_worked'),
				array('name' => 'absent'),
				array('name' => 'lates'),
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

	function get_employee_time_record(){
		$html = '';
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
		$search = 1;

		$this->db->select('u.employee_id');
		$this->db->select('CONCAT(u.firstname, " ",u.lastname) as "Employee Name"', false);
		$this->db->from('user u');
		$this->db->join('employee_dtr dtr','u.employee_id = dtr.employee_id');
		$this->db->where('overtime >=',1);	
		$this->db->where('u.deleted = 0 AND '.$search);	
		$this->db->where('(dtr.date BETWEEN "'.date('Y-m-d',strtotime($this->input->post('date_period_start'))).'" AND "'.date('Y-m-d',strtotime($this->input->post('date_period_end'))).'" )');

		switch ($this->input->post('category')) {
			case 1:
					if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in('u.company_id ',$this->input->post('company'));
				break;
			case 2:
					if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in('u.division_id ',$this->input->post('division'));		
				break;
			case 3:
					if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in('u.department_id ',$this->input->post('department'));
				break;
			case 4:
					if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in('u.employee_id ',$this->input->post('employee'));		
				break;								
		}

		$this->db->group_by("employee_id"); 

        if ($this->input->post('sidx')) {
            $sidx = $this->input->post('sidx');
            $sord = $this->input->post('sord');
            $this->db->order_by($sidx . ' ' . $sord);
        }

		$q = $this->db->get();

		$query  = $q;

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("OT Summary")
		            ->setDescription("OT Summary");
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');
		$alpha_ctr = 0;
		$sub_ctr   = 0;

		//Default column width
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
/*		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);					
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);	*/				
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);					

		//Initialize style
		$HorizontalCenter = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

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

		$styleArrayBorder = array(
		  	'borders' => array(
		    	'allborders' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	)
		  	)
		);

		$styleArrayBorderGen = array(
			'borders' => array(
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN,
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN,
			    ),
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN,
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN,
			    ),
			) 			
		);

		$styleArrayBorderTopBottom = array(
		  	'borders' => array(
		    	'bottom' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	),
		    	'top' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	),		    	
		  	)
		);

		$styleArrayBorderRight = array(
		  	'borders' => array(
		    	'right' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	),	    	
		  	)
		);

		$styleArrayBorderBottom = array(
		  	'borders' => array(
		    	'bottom' => array(
		      		'style' => PHPExcel_Style_Border::BORDER_THIN
		    	),	    	
		  	)
		);

		$fields = array("Date","Rendered","","Regular","","SAT","SUN/Spec. Hol.","","Legal Hol.","");
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

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArrayBorderRight);
			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '7')->applyFromArray($styleArrayBorderRight);
			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		$alpha_header = $alpha_ctr;

		$objPHPExcel->getActiveSheet()->mergeCells('B6:C6');
		$objPHPExcel->getActiveSheet()->mergeCells('D6:E6');
		$objPHPExcel->getActiveSheet()->mergeCells('G6:H6');
		$objPHPExcel->getActiveSheet()->mergeCells('I6:J6');


		$sub_ctr   = 0;			
		$alpha_ctr = 0;
		$fields = array("Applied","Date Info","OT Hours","125%","135%","25%","130%","140%","200%","210%");
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

			$activeSheet->setCellValue($xcoor . '7', $field);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '7')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){

			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_header - 1].$ctr);

		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValue('A1', 'HDI GROUP');
		$activeSheet->setCellValue('A2', 'OT Summary');
		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			$activeSheet->setCellValue('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))));
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		$fields = array("date_applied","ot_date","ot_rendered","reg1","reg2","sat","sun1","sun2","leg1","leg2");
		// contents.
		$line = 8;
		foreach ($query->result() as $row_employee) {
			$total_meal_pay = 0;
			$total_transpo_pay = 0;
			$sub_ctr   = 0;			
			$alpha_ctr = 0;

			if ($sub_ctr > 0) {
				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
			} else {
				$xcoor = $alphabet[$alpha_ctr];
			}


			$this->db->select('employee_oot_summary.*, employee_oot.date, employee_oot.date_created, employee_oot.datetime_from, employee_oot.datetime_to');
			$this->db->where('employee_oot_summary.employee_id',$row_employee->employee_id);
			$this->db->join('employee_oot','employee_oot.employee_oot_id = employee_oot_summary.employee_oot_id','left');
			$this->db->order_by('employee_oot.date_created','ASC');
			$result = $this->db->get('employee_oot_summary');

			if ($result && $result->num_rows() > 0){
				$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row_employee->{"Employee Name"});

				$line++;
				$total_reg_1 = 0;
				$total_reg_2 = 0;
				$total_sat = 0;
				$total_sun_1 = 0;
				$total_sun_2 = 0;
				$total_leg_1 = 0;
				$total_leg_2 = 0;

				foreach ($result->result() as $row) {
					$rendered_ot = $row->{"ot_rendered"} / 60 ;

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

						switch ($field) {
							case 'date_applied':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, '         ' . date($this->config->item('display_date_hdi'),strtotime($row->{"date_created"})));
								break;
							case 'ot_date':
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, date($this->config->item('display_date_format_hdi'),strtotime($row->{"date"})) ." (". date('h:i a', strtotime($row->{"datetime_from"})) ."-". date('h:i a', strtotime($row->{"datetime_to"})) .")");
								break;		
							case 'ot_rendered':
								$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, number_format($row->{"ot_hour"}, 2));
								break;																			
							case 'reg1':
									$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
									$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($row->{"reg1"}, 2) > 0 ? number_format($row->{"reg1"}, 2) : ""));
									$total_reg_1 += $row->{"reg1"};
								break;																										
							case 'reg2':
									$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
									$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($row->{"reg2"}, 2) > 0 ? number_format($row->{"reg2"}, 2) : ""));
									$total_reg_2 += $row->{"reg2"};
								break;		
							case 'sat':
									$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
									$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($row->{"sat"}, 2) > 0 ? number_format($row->{"sat"}, 2) : ""));
									$total_sat += $row->{"sat"};
								break;	
							case 'sun1':
									$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
									$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($row->{"spec1"}, 2) > 0 ? number_format($row->{"spec1"}, 2) : ""));
									$total_sun_1 += $row->{"spec1"};
								break;
							case 'sun2':
									$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
									$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($row->{"spec2"}, 2) > 0 ? number_format($row->{"spec2"}, 2) : ""));
									$total_sun_2 += $row->{"spec2"};
								break;
							case 'leg1':
									$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
									$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($row->{"leg1"}, 2) > 0 ? number_format($row->{"leg1"}, 2) : ""));
									$total_leg_1 += $row->{"leg1"};
								break;
							case 'leg2':
									$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
									$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($row->{"leg2"}, 2) > 0 ? number_format($row->{"leg2"}, 2) : ""));
									$total_leg_2 += $row->{"leg2"};													
								break;																																		
							default:
								$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $row->{$field});
								break;
						}
						$alpha_ctr++;
					}
					$line++;
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

					switch ($field) {																		
						case 'reg1':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($total_reg_1, 2) > 0 ? number_format($total_reg_1, 2) : ""));
							break;																										
						case 'reg2':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($total_reg_2, 2) > 0 ? number_format($total_reg_2, 2) : ""));
							break;		
						case 'sat':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($total_sat, 2) > 0 ? number_format($total_sat, 2) : ""));
							break;	
						case 'sun1':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($total_sun_1, 2) > 0 ? number_format($total_sun_1, 2) : ""));
						break;	
						case 'sun2':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($total_sun_2, 2) > 0 ? number_format($total_sun_2, 2) : ""));
						break;	
						case 'leg1':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($total_leg_1, 2) > 0 ? number_format($total_leg_1, 2) : ""));
						break;	
						case 'leg2':
							$objPHPExcel->getActiveSheet()->getStyle($xcoor . $line)->getNumberFormat()->setFormatCode("#,##0.00");
							$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, (number_format($total_leg_2, 2) > 0 ? number_format($total_leg_2, 2) : ""));
							break;																																		
					}
					$alpha_ctr++;				
				}		
				$line++;		
			}					
		}



		$objPHPExcel->getActiveSheet()->getStyle('B6:E6')->applyFromArray($styleArrayBorderBottom);
		$objPHPExcel->getActiveSheet()->getStyle('G6:J6')->applyFromArray($styleArrayBorderBottom);
		$objPHPExcel->getActiveSheet()->getStyle('A6:J7')->applyFromArray($styleArrayBorderGen);
		$objPHPExcel->getActiveSheet()->getStyle('A8:J'.($line - 1))->applyFromArray($styleArrayBorder);
		//$objPHPExcel->getActiveSheet()->getStyle('B8:D'.$line)->applyFromArray($HorizontalCenter);
		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title("DTR Summary Report") . '.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/ot_allowances/'.url_title("DTR Summary Report").'-'.date('Y-m-d').'.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));
	}	
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>