<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Adhoc_report extends MY_Controller
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
		$this->load->model('uitype_base');
		$this->load->helper('form');
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'admin/adhoc_report/listview';

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

		$data['module'] = $this->db->order_by('short_name', 'ASC')->get_where('module',array('deleted'=>0,'with_adhoc_report'=>1,'inactive'=>0));

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
		$this->db->where('employee_dtr.deleted = 0 AND '.$search);	

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
			$this->db->where('employee_dtr.deleted = 0 AND '.$search);

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
	        }

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        
	        
	        $result = $this->db->get();
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
					$remarks = "obt";

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
					$remarks = "leave";

				$dtrp = get_form($row->employee_id, 'dtrp', $dummy_p, $row->date, false);
				if ($dtrp->num_rows() > 0)
					$remarks = "dtrp";

				$out = get_form($row->employee_id, 'out', null, $row->date, false);
				if ($out->num_rows() > 0)
					$remarks = "out";

				$et = get_form($row->employee_id, 'et', null, $row->date, false);
				if ($et->num_rows() > 0)
					$remarks = "et";

	            $response->rows[$ctr]['cell'][0] = $row->employee_name;
	            $response->rows[$ctr]['cell'][1] = date($this->config->item('display_date_format'),strtotime($row->date));
	            $response->rows[$ctr]['cell'][2] = $row->time_in;
	            $response->rows[$ctr]['cell'][3] = $row->time_out;
	            $response->rows[$ctr]['cell'][4] = $row->hours_worked;
	            $response->rows[$ctr]['cell'][5] = $absent;
	            $response->rows[$ctr]['cell'][6] = number_format($row->lates / 60,2);
	            $response->rows[$ctr]['cell'][7] = number_format($row->undertime / 60,2);
	            $response->rows[$ctr]['cell'][8] = number_format($row->overtime / 60,2);
	            $response->rows[$ctr]['cell'][9] = $remarks;
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

	function get_fields(){
		$html = '';

		$this->db->where('deleted',0);
		$this->db->where('visible',1);
		$this->db->where('module_id',$this->input->post('module_id'));
		$result = $this->db->get('field');

/*		dbug($this->db->last_query());
		return;*/

		if ($result && $result->num_rows() > 0){
	        $html .= '<select id="fields" multiple="multiple" class="multi-select" style="width:400px;" name="fields[]">';
	            foreach($result->result() as $row){
	            	if ($row->fieldlabel != '' && $row->fieldlabel != '&nbsp;'){
						$field_label = $row->fieldlabel;	            	
					}
					else{
						$field_label = ucwords($row->column);
					}
	            	switch ($row->uitype_id) {
	            		case 5:
	            			$field_label = $row->fieldlabel .' ( Date From - Date To)';
	            			if ($row->table !== 'employee_family') {
	            				$html .= '<option value="'.$row->field_id.'">'.$field_label.'</option>';
	            			}
	            			break;	            		
	            		case 24:
	            			$field_label = $row->fieldlabel .' ( Date From - Date To)';
	            			$html .= '<option value="'.$row->field_id.'">'.$field_label.'</option>';
	            			break;
	            		case 38:
	            			$field_label = $row->fieldlabel .' ( Time Start - Time End)';
	            			$html .= '<option value="'.$row->field_id.'">'.$field_label.'</option>';
	            			break;	            			
	            		case 40:
	            			$field_label = $row->fieldlabel .' ( Datetime From - DateTime To)';
	            			$html .= '<option value="'.$row->field_id.'">'.$field_label.'</option>';
	            			break;	            			
	            		default:
			            	if ($this->db->field_exists($row->fieldname, $row->table)){
			                	$html .= '<option value="'.$row->field_id.'">'.$field_label.'</option>';
			            	}

			            	//especial case which field is not exists in module_manager
			            	if ($row->column == 'department_id'){
			            		$result = $this->db->get_where('field',array("column" => 'department_code', 'deleted' => 0));
			            		if ($result && $result->num_rows() > 0){
			            			$single_row = $result->row();
			            			$html .= '<option value="'.$single_row->field_id.'">'.$single_row->fieldlabel.'</option>';
			            		}
			            	}
	            			break;
	            	}
	            }
	        $html .= '</select>';	
    	}

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function get_fields_selected(){
		$html = '';
		$html2 = '';
		$html3 = '';
		$this->db->where('deleted',0);
		$this->db->where('visible',1);
		$this->db->where_in('field_id',$this->input->post('fields'));
		$result = $this->db->get('field');

/*		dbug($this->db->last_query());
		return;
*/
		if ($result && $result->num_rows() > 0){
			$html3 .= '<select name="orderby" class="orderby" style="width:100%">';
			$html3 .= '<option value="">Order By</option>';
            foreach($result->result() as $row){
				$field_label = $row->fieldlabel;
				$module_id_class = $row->module_id;
            	switch ($row->uitype_id) {
            		case 5:
            			$field_label = $row->fieldlabel .'( Date From - Date To)';
		            	if ($this->db->field_exists($row->column, $row->table)){
		                	$html .= '<li class="item">'.$field_label.'<input type="hidden" name="fields_sorted[]" value="'.$row->field_id.'"></li>';
		            	}
		            	break;
            		case 24:
            			$field_label = $row->fieldlabel .'( Date From - Date To)';
            			$html .= '<li class="item">'.$field_label.'<input type="hidden" name="fields_sorted[]" value="'.$row->field_id.'"></li>';
            			break;
            		case 38:
            			$field_label = $row->fieldlabel .'( Time Start - Time End)';
            			$html .= '<li class="item">'.$field_label.'<input type="hidden" name="fields_sorted[]" value="'.$row->field_id.'"></li>';
            			break;            			
            		case 40:
            			$field_label = $row->fieldlabel .'( DateTime From - DateTime To)';
            			$html .= '<li class="item">'.$field_label.'<input type="hidden" name="fields_sorted[]" value="'.$row->field_id.'"></li>';
            			break;            			
            		default:
		            	if ($this->db->field_exists($row->column, $row->table)){
		                	$html .= '<li class="item">'.$field_label.'<input type="hidden" name="fields_sorted[]" value="'.$row->field_id.'"></li>';
		            	}
            			break;
            	}

            	//use for order by
				if ($this->db->field_exists($row->column, $row->table)){
            		$html3 .= '<option value="'.$row->field_id.'">'.$field_label.'</option>';
            	}

            	$name = $row->fieldname;
            	$value = '';
            	$tabindex = '';
				$field_id = $row->field_id;
				$readonly = false;

				switch($row->uitype_id){
					case 1: // Textfield
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap"><input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text"/></div>';
						break;
					case 3: // Dropdown
						$html2 .= '<label style="float:left">'.$field_label.'</label>'.$this->picklist( $field_id, $name.'[]', $value, false, $tabindex );
						break;
					case 4: // Yes/No
						$html2 .= '<label>'.$field_label.'</label><div class="radio-input-wrap">
								<input type="radio" ' . $tabindex . ' name="'.$name.'" id="'.$name.'-yes" value="1" class="input-radio" '.($value == 1 ? 'checked="checked"' : "").'/>
								<label for="'.$name.'-yes" class="check-radio-label gray">Yes</label>
								<input type="radio" name="'.$name.'" id="'.$name.'-no" value="0" class="input-radio" '.(empty( $value ) ? 'checked="checked"' : "").'/>
								<label for="'.$name.'-no" class="check-radio-label gray">No</label>
							</div>';
						break;
					case 5: // Date
/*						if($value == '0000-00-00') $value = '';
						if( $value != "" )
						{
							$temp = explode('-', $value);
							$value = $temp[1].'/'.$temp[2].'/'.$temp[0];
						}
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
						<input type="hidden" value="'. $value .'" name="'.$name.'" id="'.$name.'" />
						<input type="text" ' . $tabindex . ' name="'.$name.'-temp" id="'.$name.'-temp" value="'. $value .'" class="input-text datepick datepicker" readonly/></div>';
						break; */ 
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
						<input type="hidden" value="" name="'.$name.'_from" id="'.$name.'_from" />
						<input type="text" ' . $tabindex . ' name="'.$name.'-temp-from" id="'.$name.'-temp-from" value="" class="input-text datepicker"/>
						&nbsp;&nbsp;&nbsp;<span class="to">to</span>&nbsp;&nbsp;&nbsp;
						<input type="hidden" value="" name="'.$name.'_to" id="'.$name.'_to" />
						<input type="text" name="'.$name.'-temp-to" id="'.$name.'-temp-to" value="" class="input-text datepicker"/>
						</div>';
						break;						                              
					case 6: // (Salutation then Firstname: Salutaion part)
						$html2 .= '<label>'.$field_label.'</label><div class="select-input-wrap">
							<select ' . $tabindex . ' name="'.$name.'" id="'.$name.'" style="width:15%">
								<option value="Mr." '.($value == "Mr." ? 'selected' : "").'>Mr.</option>
								<option value="Miss" '.($value == "Miss" ? 'selected' : "").'>Miss</option>
								<option value="Mrs." '.($value == "Mrs." ? 'selected' : "").'>Mrs.</option>
							</select>
							';
						break;
					case 7: // (Salutation then Firstname: first name part)
						$html2 .= '<label>'.$field_label.'</label><input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text" style="width:75%"/></div>';
						break;
					case 8: // (Last name and auxilliary: lastname part)
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap"><input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text" style="width:62%"/></div>';
						break;
					case 9: // (Last name and auxilliary: aux part)
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap"><input type="text" name="'.$name.'" id="'.$name.'" ' . $tabindex . ' value="'. $value .'" class="input-text" style="width:25%"/></div>';
						break;
					case 13: // Module Boxy
						$html2 .= '<label>'.$field_label.'</label>'.$this->listview_boxy( $field_id, $name, $value, $tabindex, $readonly );
						break;
					case 14: // field group dropdown
						if($value == ""){
							$html2 .= '<label>'.$field_label.'</label><div class="select-input-wrap fieldgroup-div"></div>';
						}
						else{
							$html2 .= '<label>'.$field_label.'</label><div class="select-input-wrap fieldgroup-div">'. $this->fieldGroup_ddlb($value) .'</div>';
						}
						break;
					case 15: // Textfield datatype with description
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
							<input type="text" name="'.$name.'" id="'.$name.'" ' . $tabindex . ' value="'. $value .'" class="input-text"/>

							<p id="datatype-tooltip" class="form-item-description">
							    Should be separated by a ~ (tilde). Possible values: 
							    <span class="nobr"><strong>V</strong> - Varchar (any character)</span> / 
						    	<span class="nobr"><strong>M</strong> - Mandatory</span> / 
						    	<span class="nobr"><strong>O</strong> - Optional</span> / 
						    	<span class="nobr"><strong>R</strong> - Read Only and Auto-generated value fields</span> / 
						    	<span class="nobr"><strong>E</strong> - Email (if not empty, must be a valid email)</span> / 
						    	<span class="nobr"><strong>U</strong> - URL (if not empty, must be valid URL)</span> / 
						    	<span class="nobr"><strong>I</strong> - Integer</span> / 
						    	<span class="nobr"><strong>F</strong> - Float</span> / 
						    	<span class="nobr"><strong>GE</strong> - Greater or equal</span> / 
						    	<span class="nobr"><strong>GT</strong> - Greater than</span> / 
						    	<span class="nobr"><strong>LE</strong> - Less or Equal</span> / 
						    	<span class="nobr"><strong>LT</strong> - Less Than </span> / 
						    	<span class="nobr"><strong>N</strong> - Numeric.</span> /
						    	<span class="nobr"><strong>UN</strong> - Unique </span>
							</p>
						</div>';
						break;
					case 17: // Two/Single Column Layout
						$html2 .= '<label>'.$field_label.'</label><div class="radio-input-wrap"><input type="radio" ' . $tabindex . ' name="'.$name.'" id="'.$name.'-single" value="1" class="input-radio" '.($value == 1 ? 'checked="checked"' : "").'/><label for="'.$name.'-single" class="check-radio-label gray">Single Column</label><input type="radio" name="'.$name.'" id="'.$name.'-two" value="0" class="input-radio" '.($value == 0 ? 'checked="checked"' : "").'/><label for="'.$name.'-two" class="check-radio-label gray">Two Column</label>
							</div>';
						break;
					case 18: // Picklist Type
						$html2 .= '<label>'.$field_label.'</label><div class="radio-input-wrap">
							<input type="radio" name="'.$name.'" id="'.$name.'-query" ' . $tabindex . ' value="Query" class="input-radio" '.($value == "Query" ? 'checked="checked"' : "").'/>
							<label for="'.$name.'-query" class="check-radio-label gray">Query</label>
							<input type="radio" name="'.$name.'" id="'.$name.'-table" value="Table" class="input-radio" '.($value == "Table" ? 'checked="checked"' : "").'/>
							<label for="'.$name.'-table" class="check-radio-label gray">Table</label>
							<input type="radio" name="'.$name.'" id="'.$name.'-fields" value="Fields" class="input-radio" '.($value == "Fields" ? 'checked="checked"' : "").'/>
							<label for="'.$name.'-fields" class="check-radio-label gray">Fields</label>					
							<input type="radio" name="'.$name.'" id="'.$name.'-fields" value="Function" class="input-radio" '.($value == "Function" ? 'checked="checked"' : "").'/>
							<label for="'.$name.'-fields" class="check-radio-label gray">Function</label>
							</div>';
						break;
					case 19: // Days Multi-select
						if($value != "")
						{
							$value = unserialize($value);
						}
						else{
							$value = array();
						}
						$str = '<label>'.$field_label.'</label><div class="text-input-wrap">';
						for($i=1; $i<8 ; $i++){
							$str .= '<input type="checkbox" name="'.$name.'[]" ' . $tabindex . ' value="'.$i.'" '.(in_array($i, $value) ? 'checked="checked"': "").'> '.int_to_day($i, 'full')."<br/>";
						}
						$str .= '</div>';
						$html2 .= $str;
						break;
					case 20: // Yes/No
						$html2 .= '<label>'.$field_label.'</label><div class="radio-input-wrap">
								<input type="checkbox" ' . $tabindex . ' name="'.$name.'1" id="'.$name.'-yes" value="1"/>
								<label for="'.$name.'-yes" class="check-radio-label gray">Yes</label>
								<input type="checkbox" name="'.$name.'2" id="'.$name.'-no" value="0"/>
								<label for="'.$name.'-no" class="check-radio-label gray">No</label>
							</div>';
						break;						
					case 21: // Multi-select
						$html2 .= '<label>'.$field_label.'</label>' . $this->multiselect($field_id, $name, $value, $tabindex) . '<br style="clear:left;"/>';
						break;
					case 22:
						$dirreader = $this->db->get_where('directory_reader', array('directory_reader_id' => $field_id));

						if($dirreader->num_rows() > 0 && $dirreader->num_rows() == 1)
						{
							$dirresult	= $dirreader->row_array();
							$dir_path	= $dirresult['directory_path'];
							$file_ext	= $dirresult['file_ext'];

							//read icons directory
							$this->load->helper('file');
							$files = get_filenames($dir_path);

							$drop_down = '<label>'.$field_label.'</label><div class="select-input-wrap"><select name="'.$name.'" ' . $tabindex . '>';
							foreach($files as $file)
							{
								if($file == $value)
									$selected = 'selected';
								else
									$selected = '';

								if( !empty($file_ext) )
								{
									if( strpos( strtolower($file), strtolower( '.'.$file_ext ) ) )
									{
										$icons[$file] = $file;
										$drop_down .= '<option value="'.$file.'" '.$selected.'>'.$file.'</option>';
									}
								}
								else{
									$icons[$file] = $file;
									$drop_down .= '<option value="'.$file.'" '.$selected.'>'.$file.'</option>';
								}
							}

							$html2 .= $drop_down .= '</select></div>';
						}
						else
						{
							$html2 .= "Directory associated with field_id ". $field_id ." was not found.";
						}
						break;
					case 23:
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap"><input type="text" name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text" ' . $tabindex . ' readonly="readonly"/></div>';
						break;
					case 24: // Date from - Date to
						$value_from = "";
						$value_to = "";

						if( $value != "" )
						{
							$temp = explode('to', $value);

							if( $temp[0] == " "){
								$value_from = "";
							}
							else{
								$temp[0] = rtrim($temp[0]);
								$value_from = explode('-', $temp[0]);
								$value_from = month_to_int( $value_from[1], true ). '/' .$value_from[0]. '/' . $value_from[2];
							}

							if($temp[1] == " ")
							{
								$value_to = "";
							}
							else{
								$temp[1] = ltrim($temp[1]);
								$value_to = explode('-', $temp[1]);
								$value_to = month_to_int( $value_to[1], true ). '/' .$value_to[0]. '/' . $value_to[2];
							}
						}
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
						<input type="hidden" value="'. $value_from .'" name="'.$name.'_from" id="'.$name.'_from" />
						<input type="text" ' . $tabindex . ' name="'.$name.'-temp-from" id="'.$name.'-temp-from" value="'. $value_from .'" class="input-text datepicker"/>
						&nbsp;&nbsp;&nbsp;<span class="to">to</span>&nbsp;&nbsp;&nbsp;
						<input type="hidden" value="'. $value_to .'" name="'.$name.'_to" id="'.$name.'_to" />
						<input type="text" name="'.$name.'-temp-to" id="'.$name.'-temp-to" value="'. $value_to .'" class="input-text datepicker"/>
						</div>';
						break;
					case 26: //Time From - Time in 24hrs format UI
						if($value!= "")
						{
							$temp = explode(' to ', $value);
							$value_start = explode(':', $temp[0]);
							$value_start_hh = $value_start[0];
							$value_start_mm = $value_start[1];
							
							$value_end = explode(':', $temp[1]);
							$value_end_hh = $value_end[0];
							$value_end_mm = $value_end[1];
						}
						else
						{
							$value_start= "";
							$value_end = "";
							$value_start_hh = "";
							$value_start_mm = "";
							$value_end_mm = "";
							$value_end_hh = "";
						}
						
						
						$time = '<label>'.$field_label.'</label><div class="text-input-wrap">';
						$time .= '<select name="'.$name.'_start_hh" ' . $tabindex . '>';
						
						for($i=0; $i<=23; $i++)
						{
							if($i < 10) $i = '0'.$i;
							
							$time .='<option value="'.$i.'" ';
							if($value_start_hh == $i)
							{
								$time .= 'selected';
							}
							$time .= '>'.$i.'</option>';
						}
						$time .= '</select>';
						
						$time .= ' : <select name="'.$name.'_start_mm">';
						for($i=0; $i<=59; $i++)
						{
							if($i < 10) $i = '0'.$i;
							
							$time .='<option value="'.$i.'" ';
							if($value_start_mm == $i)
							{
								$time .= 'selected';
							}
							$time .= '>'.$i.'</option>';
						}
						
						$time .= '</select>';
						$time .= '&nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;';
						
						
						$time .= '<select name="'.$name.'_end_hh">';
						for($i=0; $i<=23; $i++)
						{
							if($i < 10) $i = '0'.$i;
							$time .='<option value="'.$i.'" ';
							if($value_end_hh == $i)
							{
								$time .= 'selected';
							}
							$time .= '>'.$i.'</option>';
						}
						
						$time .= '</select>';
						$time .= ' : <select name="'.$name.'_end_mm">';
						for($i=0; $i<=59; $i++)
						{
							if($i < 10) $i = '0'.$i;
							$time .='<option value="'.$i.'" ';
							if($value_end_mm == $i)
							{
								$time .= 'selected';
							}
							$time .= '>'.$i.'</option>';
						}
						
						$time .= '</select>';
						$time .= '</div>';
			
						$html2 .= $time;
						break;
					case 27: // Male/Female
						$html2 .= '<label>'.$field_label.'</label><div class="radio-input-wrap"><input type="radio" ' . $tabindex . ' name="'.$name.'" id="'.$name.'-male" value="male" class="input-radio" '.($value == 'male' ? 'checked="checked"' : "").'/><label for="'.$name.'-male" class="check-radio-label gray">Male</label><input type="radio" name="'.$name.'" id="'.$name.'-female" value="female" class="input-radio" '.( $value == 'female' ? 'checked="checked"' : "").'/><label for="'.$name.'-female" class="check-radio-label gray">Female</label>
							</div>';
						break;
					case 5: // Date
						if( $value != "" )
						{
							$temp = explode('-', $value);
							$value = $temp[1].'/'.$temp[2].'/'.$temp[0];
						}
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
						<input type="hidden" value="'. $value .'" name="'.$name.'" id="'.$name.'" />
						<input type="text" ' . $tabindex . ' name="'.$name.'-temp" id="'.$name.'-temp" value="'. $value .'" class="input-text datepicker disabled" disabled="disabled"/></div>';
						break;
		            case 30: // Boolean checkbox.
		                    $html2 .= '<label>'.$field_label.'</label><br /><div style="padding-left:2px">' . form_checkbox($name, 1, ($value == 1), $tabindex) . '</div>';                            
		                    break;
		            case 31:
					case 32: // DateTime			
						if($value == '0000-00-00 00:00:00') $value = '';
						if($value == '1970-01-01 08:00:00') $value = '';
						if($value == '0') $value = '';

						if( $value != "" )
						{
		                    $value = date($this->config->item('edit_datetime_format'), strtotime($value));
						}
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">				
						<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text datetimepicker" readonly/></div>';
						break;   
		            case 33: // jquery UI Time 
		                $value = ($value == '00:00:00') ? '' : date($this->config->item('display_time_format'), strtotime($value));
		                $html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
						<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="" class="input-text timepicker" readonly/></div>';
						break;   				
		            case 34: // jquery UI Month year only
		                if($value == '00:00:00') $value = '';
		                $html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">				
						<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text month-year" readonly/></div>';
						break; 		
					case 37: // jquery UI Minute Second Picker
		                $value = ($value == '00:00:00') ? '' : date($this->config->item('display_mmss_format'), strtotime($value));;
		                $html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
						<input type="text" ' . $tabindex . ' name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text minutesecondpicker" readonly/></div>';
						break;
					case 38: // jquery UI Start - End time picker
						if($value!= "")
						{
							$temp = explode(' to ', $value);
							$value_start = $temp[0];					
							$value_end = $temp[1];
						}
						else
						{
							$value_start= "";
							$value_end = "";
						}
		                $value_start = ($value_start == '00:00:00') ? ' ' : date($this->config->item('display_time_format'), strtotime($value_start));
		                $value_end = ($value_end == '00:00:00') ? ' ' : date($this->config->item('display_time_format'), strtotime($value_end));
		                $html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
						<input type="text" ' . $tabindex . ' name="'.$name.'_start" id="'.$name.'_start" value="" class="input-text start_timepicker timepicker" style="width:35%" readonly/> to 
						<input type="text" name="'.$name.'_end" id="'.$name.'_end" value="" class="input-text end_timepicker timepicker" style="width:35%" readonly/></div>';
						break;  				
					case 35: //number range from - to
						$value_from = 0;
						$value_to = 0;
						if(!empty( $value )){
							$values = explode("to", $value);
							$value_from = trim($values[0]);
							$value_to = trim($values[1]);
						}				
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">
						<input type="text" ' . $tabindex . ' class="input-text text-right" value="'. $value_from .'" name="'.$name.'_from" id="'.$name.'_from" style="width:35%"/>
						&nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
						<input type="text" class="input-text text-right" value="'. $value_to .'" name="'.$name.'_to" id="'.$name.'_to" style="width:35%"/>
						</div>';
						break;                           
					case 36:
						$this->db->where('field_id', $field_id);

						$result = $this->db->get('field_options')->row();

						$options = explode(',', $result->options);

						$fields = '';
						foreach ($options as $key => $option) {
							$option = trim($option);
							$fields .= form_radio($name, $key + 1, ($value == $key + 1), $tabindex) . $option . '<br />';
						}

						$html2 .= '<label>'.$field_label.'</label><br />'.$fields;
						break;
					case 39: // Autocomplete
						$this->db->where('field_id', $field_id);
						$this->db->where('deleted', 0);
						$this->db->limit(1);

						$result = $this->db->get('field_autocomplete');				

						$params = 'id="'. $name . '" ' . $tabindex;

						$disabled = false;

						if ($result->num_rows() > 0) {
							$row = $result->row();
							if ($row->type == 'Query') {
								$results = $this->db->query(str_replace('{dbprefix}', $this->db->dbprefix, $row->table))->result_array();
							} else if ($row->type == 'Function') {

								$module = $this->hdicore->get_module($module_id_class);

								if (!is_loaded($module->class_name)) {
									$path = explode('/', $module->class_path);

									unset($path[count($path) - 1]);
									load_class($module->class_name, 'controllers/' . implode('/', $path));
								}

/*								if (method_exists($module->class_name, $row->table)) {
									$results = call_user_func(array($module->class_name, $row->table));
								}*/ 
								if (function_exists($row->table)) {
									$results = $this->{$row->table}();
								}
								else{
									//$results = $this->uitype_base->get_leave_dropdown();
									$uitbase = new uitype_base;
									if (method_exists($uitbase, $row->table)) {
										$results = call_user_func(array($uitbase, $row->table));
									}
								}
							} else {
								$this->db->where('deleted', 0);
								$results = $this->db->get($row->table)->result_array();						
							}
							
							if ($row->multiple) {
								$params .= ' multiple';
								$value = explode(',', $value);
								$options = array();
							} else {
								$options = array(' ' => ' ');
							}

							if ($results){
								$name = $name.'[]';
								$html2 .= '<label style="float:left">'.$field_label.'</label><div class="multiselect-input-wrap" style="width:400px">';
								$html2 .= '<input type="hidden" name="'. $name.'" id="'.$name.'" value="'.$value.'"/>';
								$html2 .= '<select ' . $tabindex . ' id="multiselect-'. $name.'" name="multiselect-'. $name .'" multiple="multiple" '. ( $disabled ? 'disabled="disable"' : '') .' class="multiselect" style="width:400px">';				

								foreach ($results as $option) {
									$labels = explode(',', $row->label);

									$label = array();
									foreach ($labels as $l) {
										$label[] = $option[$l];
									}							
	/*
									if (trim($row->group_by) != '') {
										$options[$option[$row->group_by]][$option[$row->value]] = implode(' ', $label);
									} else {
										$options[$option[$row->value]] = implode(' ', $label);
									}		*/

									$html2 .=  '<option value="'.$option[$row->value].'">'. implode(' ', $label) .'</option>';
									$options[$option[$row->value]] = implode(' ', $label);
								}
								$html2 .= '
									</select>
								</div><br clear="all"/>';		
							}					
						}

						//$html2 .= '<label>'.$field_label.'</label><div class="select-input-wrap">'. form_dropdown($name, $options, $value, $params) . '</div>';
						break;									
					case 40: // DateTime			
						$value_from = "";
						$value_to = "";

						if( $value != "" )
						{
							$temp = explode('to', $value);

							if( $temp[0] == " "){
								$value_from = "";
							}
							else{
								$value_from = date('m/d/Y h:i a', strtotime( $temp[0]));
							}

							if($temp[1] == " ")
							{
								$value_to = "";
							}
							else{
								$value_to = date('m/d/Y h:i a', strtotime( $temp[1]));
							}
						}
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap">				
						<input type="text" ' . $tabindex . ' name="'.$name.'_from" id="'.$name.'_from" value="'. $value_from .'" class="datepicker input-text datetimepicker" readonly/>
						 to 
						<input type="text" ' . $tabindex . ' name="'.$name.'_to" id="'.$name.'_to" value="'. $value_to .'" class="datepicker input-text datetimepicker" readonly/></div>';
						break; 
					default:
						$html2 .= '<label>'.$field_label.'</label><div class="text-input-wrap"><input type="text" name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text"/></div>';
				}
            }
			$html3 .= '</select>';            
    	}

        $data->html1 = $html;
        $data->html2 = $html2;
        $data->html3 = $html3;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $data));			
	}

	function export($record_id = 0)
	{	
   		$meta = $this->hdicore->_get_meta();

		ini_set('memory_limit', "2048M");
		$this->load->helper('time_upload');

		$module_id = $this->input->post('module_id');
		$fields = $this->input->post('fields');

		$this->db->where('module_id',$module_id);
		$this->db->where('deleted',0);
		$result = $this->db->get('module');

		$result_row = $result->row();
		$parent_table = $result_row->table;
		$parent_key = $result_row->key_field;

		$this->db->select($this->db->dbprefix('field').'.table'.','.$this->db->dbprefix('field').'.column'.','.$this->db->dbprefix('field').'.fieldname'.','.$this->db->dbprefix('field').'.fieldlabel'.','.$this->db->dbprefix('field').'.uitype_id'.','.$this->db->dbprefix('field').'.field_id'.','.$this->db->dbprefix('module').'.key_field');
		$this->db->join($this->db->dbprefix('module'),$this->db->dbprefix('field').'.module_id = '.$this->db->dbprefix('module').'.module_id');
		$this->db->where_in('field_id',$fields);
		$this->db->order_by($this->db->dbprefix('module').'.sequence','ASC');
		$result = $this->db->get('field');

		$related_table = array();
		$select = array();
		$select_to_arrange = array();
		$where = array();
		$groupby = "";

		if ($result && $result->num_rows() > 0){
			$result_array = $result->result_array();
			$result_array_re_ordered = $this->re_order_array($this->input->post('fields_sorted'),$result_array);
			$ctr = 0;
			foreach ($result_array_re_ordered as $row) {
				$table = $row['table'];
	
				if ($table != $parent_table){								
					if (!array_key_exists($table, $related_table)){
						$related_table[$this->db->dbprefix . $table]['join_table'] = $parent_table;
						$related_table[$this->db->dbprefix . $table]['key0'] = $row['key_field'];
						$related_table[$this->db->dbprefix . $table]['key1'] = $row['key_field'];
					}			
				}

				switch ($row['uitype_id']) {
					case 1:	//text field
						$select[] = $table.'.'.$row['fieldname'];
						$uitype[$row['fieldname']] = $row['uitype_id'];
						if (!isset($select_to_arrange[$row['fieldname']])){
							$select_to_arrange[trim($row['fieldname'])] = $row['fieldlabel'];
						}
						else{
							$select_to_arrange[trim($row['fieldname']).'|'.$ctr] = $row['fieldlabel'];	
						}						
						if ($this->input->post($row['fieldname']) && $this->input->post($row['fieldname']) != ''){
							$qs = "'".implode("','",explode(",",$this->input->post($row['fieldname'])))."'";
							$where[] = $this->db->dbprefix . $table.".".$row['fieldname']." IN (".$qs.")";
						}
						break;					
					case 13:
						$this->db->select('a.module_id, a.column, field.table, field.fieldlabel, module.key_field');
						$this->db->from('field_module_link a');
						$this->db->join('module', 'module.module_id = a.module_id', 'left');
						$this->db->join('field', 'field.module_id = a.module_id', 'left');
						$this->db->where(array('a.field_id' => $row['field_id']));
						$relate_module = $this->db->get();
						if($relate_module->num_rows() > 0){
							$relate_module = $relate_module->row_array();
							$column = $relate_module['column'];
							$fieldlabel = $relate_module['fieldlabel'];						
							$table_p = $relate_module['table'];
							if (!array_key_exists($table_p, $related_table)){	
								$related_table[$table_p]['join_table'] = $table;
								$related_table[$table_p]['key0'] =  $relate_module['key_field'];;
								$related_table[$table_p]['key1'] = $row['column'];							
							}						
							if(strpos($column, ',')){
								$colum_lists = explode( ',', $column );
								foreach($colum_lists as $col_index => $column){
									$select[] = $table_p.'.'.$column;
									$uitype[$column] = $row['uitype_id'];
									if (!isset($select_to_arrange[$column])){
										$select_to_arrange[trim($column)] = ucwords($column);
									}
									else{
										$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);												
									}									
								}
							}
							if (trim($this->input->post($row['fieldname'])) && trim($this->input->post($row['fieldname'])) != ''){							
								$where[] = $this->db->dbprefix . $table_p.".".$row['column']." = '".$this->input->post($row['fieldname'])."'";							
							}
						}	
						break;
					case 20:
						$select[] = $table.'.'.$row['fieldname'];
						$uitype[$row['fieldname']] = $row['uitype_id'];
						if (!isset($select_to_arrange[$row['fieldname']])){
							$select_to_arrange[trim($row['fieldname'])] = $row['fieldlabel'];
						}
						else{
							$select_to_arrange[trim($row['fieldname']).'|'.$ctr] = $row['fieldlabel'];	
						}						
						if ($this->input->post($row['fieldname'].'1') && !$this->input->post($row['fieldname'].'2')){
							$where[] = $this->db->dbprefix . $table.".".$row['fieldname']." != ''";
						}
						elseif ($this->input->post($row['fieldname'].'2') && !$this->input->post($row['fieldname'].'1')){
							$where[] = $this->db->dbprefix . $table.".".$row['fieldname']." = ''";	
						}
						break;
					case 21:
						$relate_module = $this->db->get_where('field_multiselect', array('field_id' => $row['field_id']));
						if($relate_module->num_rows() > 0)
						{
							$relate_module = $relate_module->row_array();
							$column = $relate_module['name_column'];							
							if( $relate_module['type'] == "Table"){							
								$table_p = $relate_module['table'];
								$id_column = $relate_module['id_column'];
								if (!array_key_exists($table_p, $related_table)){	
									$related_table[$table_p]['join_table'] = $table;
									$related_table[$table_p]['key0'] = $id_column;
									$related_table[$table_p]['key1'] = $row['column'];							
								}						
								if(strpos($column, ',')){
									$colum_lists = explode( ',', $column );
									foreach($colum_lists as $col_index => $column){
										$select[] = $table_p.'.'.$column;
										$uitype[$column] = $row['uitype_id'];
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
										}											
									}
								}
								else{
									$select[] = $table_p.'.'.$column;
									$uitype[$column] = $row['uitype_id'];	
									if (!isset($select_to_arrange[$column])){
										$select_to_arrange[trim($column)] = ucwords($column);
									}
									else{
										$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
									}																		
								}
								if (trim($this->input->post($row['fieldname'])) && trim($this->input->post($row['fieldname'])) != ''){							
									$where[] = $this->db->dbprefix . $table_p.".".$row['column']." = '".$this->input->post($row['fieldname'])."'";							
								}
							}						
							elseif( $relate_module['type'] == "Query"){
								$table_query = str_replace('{dbprefix}', $this->db->dbprefix, $relate_module['table']);
								preg_match("/\s+FROM\s+`?([a-z\d_]+)`?/i", $table_query, $match);
								$table_p = $match[1];
								$table_tp = $table_p;
								if (preg_match("/".$this->db->dbprefix."/", $table_p)){
									$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
								}
								$table_p = $this->db->dbprefix . $table_tp;																	
								if ($table_tp != $parent_table){	
									if (!array_key_exists($table_p, $related_table)){
										$key0 = '';
										if ($this->db->field_exists($row['column'], $table_p)){
											$key0 = $row['column'];
										}
										else{
											$key0 = $this->find_real_column($table_query,$row['column']);								
										}

										if ($this->db->field_exists($row['column'], $table) && $key0 != ''){
											$related_table[$table_p]['join_table'] = $table;
											$related_table[$table_p]['key1'] = $row['column'];
											$related_table[$table_p]['key0'] = $key0;
										}
										if (trim($this->input->post($row['fieldname'])) && trim($this->input->post($row['fieldname'])) != ''){							
											$where[] = $this->db->dbprefix . $table_p.".".$key0." = '".$this->input->post($row['fieldname'])."'";							
										}										
									}
									if(strpos($column, ',')){
										$colum_lists = explode( ',', $column );
										foreach($colum_lists as $col_index => $column){
											$select[] = $table_p.'.'.$column;
											$uitype[$column] = $row['uitype_id'];
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] = ucwords($column);
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
											}												
										}
									}	
									else{
										$select[] = $table_p.'.'.$column;
										$uitype[$column] = $row['uitype_id'];	
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
										}																			
									}																		
								}																
							}
						}						
					case 39:
						//get picklist
						$picklist = $this->db->get_where('field_autocomplete', array('field_id' => $row['field_id']));				
						$picklist = $picklist->result_array();

						$where_column = '';
						if( $picklist[0]['type'] == "Table"){
							$table_p = $picklist[0]['table'];
							$table_tp = $table_p;
							if (preg_match("/".$this->db->dbprefix."/", $table_p)){
								$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
							}							
							$table_p = $this->db->dbprefix . $table_tp;
							$alias_table = $table_p;
							
							if ($table_tp != $parent_table){	
								if (array_key_exists($table_p, $related_table)){
									$related_table['t'.$ctr]['real_table'] = $table_p;
									$alias_table = 't'.$ctr;	
								}

								$related_table[$alias_table]['join_table'] = $table;

								if ($this->db->field_exists($row['column'], $table)){
									$related_table[$alias_table]['key1'] = $row['column'];	
								}
								else{
									$column = $this->find_real_column($table,$row['column'],'table');								
									if ($column != ''){
										$related_table[$alias_table]['key1'] = $column;	
									}																
								}
								if ($this->db->field_exists($row['column'], $table_p)){
									$related_table[$alias_table]['key0'] = $row['column'];
									$where_column = $row['column'];										
								}
								else{
									$column = $this->find_real_column($table_p,$row['column'],'table');								
									if ($column != ''){
										$related_table[$alias_table]['key0'] = $column;	
										$where_column = $column;											
									}																
								}
								$column = $picklist[0]['label'];
								//$select[] = $table_p.'.'.$column;	
								$uitype[$column] = $row['uitype_id'];								
/*									if (trim($this->input->post($row['fieldname'])) && trim($this->input->post($row['fieldname'])) != ''){							
									$where[] = $this->db->dbprefix . $table_p.".".$where_column." = '".$this->input->post($row['fieldname'])."'";							
								}*/
								if ($this->input->post('multiselect-'.$row['fieldname']) != ''){			
									$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));
									if ($wherein != ''){							
										$where[] = $alias_table.".".$where_column." IN (".$wherein.")";															
									}
								}																															
								if(strpos($picklist[0]['label'], ',')){
									$colum_lists = explode( ',', $picklist[0]['label'] );
									foreach($colum_lists as $col_index => $column){
										$select[] = $alias_table.'.'.$column;
										$uitype[$column] = $row['uitype_id'];
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
										}										
									}
								}
								else{
									$select[] = $alias_table.'.'.$picklist[0]['label'];
									$uitype[$column] = $row['uitype_id'];
									if (!isset($select_to_arrange[$column])){
										$select_to_arrange[trim($column)] = ucwords($column);
									}
									else{
										$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
									}																		
								}										
							}													
						}
						elseif($picklist[0]['type'] == "Query"){
							$key = $picklist[0]['value'];
							$column = $picklist[0]['label'];

							switch ($column) {
								case 'employee':

									$select[] = 'IFNULL(GROUP_CONCAT(CONCAT (firstname," ",lastname)),"") AS reports_to ';
									$select_to_arrange['reports_to'] = 'Reports To';
									$groupby = "user.user_id";
									if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
										$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));

										if ($row['fieldname'] == "reporting_to") {
											$where[] =  "u.employee_id" . " IN (".$wherein.")";																	
										}else{
											$where[] = $this->db->dbprefix . 'user.employee_id' . " IN (".$wherein.")";
										}
																								
									}	

									if (!array_key_exists('hr_employee', $related_table)){
										$related_table['hr_employee']['join_table'] = 'user';
										$related_table['hr_employee']['customize'] = 'FIND_IN_SET(u.employee_id,hr_employee.reporting_to)';																			
									}
								break;
								case 'city':
									
									if ($row['column'] == 'perm_city'){
										$select[] = 'city AS permanent_city ';
										$select_to_arrange['permanent_city'] = 'Permanent City/Municipality Province';
										if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
											$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));

											if ($row['fieldname'] == "perm_city") {
												$where[] =  "perm_city" . " IN (".$wherein.")";																	
											}else{
												$where[] = $this->db->dbprefix . 'perm_city' . " IN (".$wherein.")";
											}
																									
										}	

										if (!array_key_exists('hr_cities', $related_table)){
											$related_table['hr_cities']['join_table'] = 'employee';		
											$related_table['hr_cities']['key0'] = 'city_id';
											$related_table['hr_cities']['key1'] = 'perm_city';
										}
									}
									else{
										$select[] = 'city AS present_city ';
										$select_to_arrange['present_city'] = 'Present City/Municipality Province';
										if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
											$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));

											if ($row['fieldname'] == "pres_city") {
												$where[] =  "pres_city" . " IN (".$wherein.")";																	
											}else{
												$where[] = $this->db->dbprefix . 'pres_city' . " IN (".$wherein.")";
											}
																									
										}	

										if (!array_key_exists('hr_cities', $related_table)){
											$related_table['hr_cities']['join_table'] = 'employee';		
											$related_table['hr_cities']['key0'] = 'city_id';
											$related_table['hr_cities']['key1'] = 'pres_city';
										}
									}

								break;								
								default:
									$picklist_table_query = str_replace('{dbprefix}', $this->db->dbprefix, $picklist[0]['table']);
									//preg_match("/FROM\s+`?([a-z\d_]+)`?/i", $picklist_table_query, $match);							
									preg_match('/(?<=FROM )\S+/i', $picklist_table_query, $match);
									preg_match('/(?<=LEFT JOIN )\S+/i', $picklist_table_query, $match1);
									$match = array_merge($match,$match1);

									foreach ($match as $table_fj) {
										if ($this->db->field_exists($row['column'], $table_fj)){
											$table_p = $table_fj;
											break;
										}
									}

									$table_tp = $table_p;
									if (preg_match("/".$this->db->dbprefix."/", $table_p)){
										$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
									}	
									
									if ($table_tp != ''){
										$table_p = $this->db->dbprefix . $table_tp;	
									}	

									if ($table_tp != $parent_table){	
										if (!array_key_exists($table_p, $related_table)){
											$key0 = '';
											if ($table_p != '' && $this->db->field_exists($row['column'], $table_p)){
												$key0 = $row['column'];
											}
											else{
												$key0 = $this->find_real_column($picklist_table_query,$row['column']);								
											}

											if ($this->db->field_exists($row['column'], $table) && $key0 != ''){
												$related_table[$table_p]['join_table'] = $table;
												$related_table[$table_p]['key1'] = $row['column'];
												$related_table[$table_p]['key0'] = $key0;
												$column = $picklist[0]['label'];
												$select[] = $table_p.'.'.$column;
												$uitype[$column] = $row['uitype_id'];
												if (!isset($select_to_arrange[$column])){
													$select_to_arrange[trim($column)] = ucwords($column);
												}
												else{
													$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
												}											
											}
		/*									if (trim($this->input->post($row['fieldname'])) && trim($this->input->post($row['fieldname'])) != ''){							
												$where[] = $this->db->dbprefix . $table_p.".".$key0." = '".$this->input->post($row['fieldname'])."'";							
											}	
		*/
											if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
												$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));
												if ($wherein != ''){											
													if (preg_match("/".$this->db->dbprefix."/", $table_p)){
														$where[] = $table_p.".".$key0." IN (".$wherein.")";
													}
													else{
														$where[] = $this->db->dbprefix . $table_p.".".$key0." IN (".$wherein.")";
													}
												}																
											}			
										}	
									}									

							}									
						}
						elseif ($picklist[0]['type'] == 'Function') {
							switch ($picklist[0]['value']) {
								case 'application_form_id':
									$related_table['employee_form_type']['join_table'] = $table;
									$related_table['employee_form_type']['key1'] = $picklist[0]['value'];
									$related_table['employee_form_type']['key0'] = $row['column'];
									$column = $picklist[0]['label'];
									if ($this->db->field_exists($column, 'employee_form_type')){
										$select[] = 'employee_form_type.'.$column;							
										$uitype[$column] = $row['uitype_id'];
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
										}											
/*										if (trim($this->input->post($row['fieldname'])) && trim($this->input->post($row['fieldname'])) != ''){							
											$where[] = "hr_employee_form_type.".$row['column']." = '".$this->input->post($row['fieldname'])."'";							
										}*/
										if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
											$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));
											if ($wherein != ''){
												$where[] = $this->db->dbprefix . "employee_form_type.".$row['column']." IN (".$wherein.")";															
											}	
										}																				
									}										
									break;
								case 'employee_id':
								case 'user_id':
									$alias_table = 'hr_user';
									if (array_key_exists($alias_table, $related_table)){
										$related_table['t'.$ctr]['real_table'] = $alias_table;
										$alias_table = 't'.$ctr;	
									}									
									$related_table[$alias_table]['join_table'] = $table;
									$related_table[$alias_table]['key1'] = $row['column'];
									$related_table[$alias_table]['key0'] = $picklist[0]['value'];
									$column = $picklist[0]['label'];
									if(strpos($column, ',')){
										$colum_lists = explode( ',', $column );
										foreach($colum_lists as $col_index => $column){
											$select[] = $alias_table.'.'.$column;											
											$uitype[$column] = $row['uitype_id'];
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] = ucwords($column);
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
											}												
/*											if (trim($this->input->post($row['fieldname'])) && trim($this->input->post($row['fieldname'])) != ''){							
												$where[] = "hr_user.".$row['column']." = '".$this->input->post($row['fieldname'])."'";							
											}*/
											if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
												$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));
												if ($wherein != ''){
													$where[] = $alias_table.".".$row['column']." IN (".$wherein.")";
												}	
											}																				
										}
									}
									else{										
										if ($this->db->field_exists($column, 'employee_form_type')){
											$select[] = $alias_table.$column;							
											$uitype[$column] = $row['uitype_id'];
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] = ucwords($column);
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
											}											
/*											if (trim($this->input->post($row['fieldname'])) && trim($this->input->post($row['fieldname'])) != ''){							
												$where[] = "hr_user.".$row['column']." = '".$this->input->post($row['fieldname'])."'";							
											}*/	
											if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
												$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));
												if ($wherein != ''){
													$where[] = $alias_table.".".$row['column']." IN (".$wherein.")";															
												}	
											}																					
										}	
									}									
									break;									
							}
						}										
						break;
					case 3:
						$realfieldcolumn = $row['column'];
						//get picklist
						$picklist = $this->db->get_where('picklist', array('field_id' => $row['field_id']));
						$picklist = $picklist->result_array();	
						$where_column = '';									
						if( $picklist[0]['picklist_type'] == "Table"){					
							$table_p = $picklist[0]['picklist_table'];					
							$table_tp = $table_p;
							if (preg_match("/".$this->db->dbprefix."/", $table_p)){
								$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
							}	
							$table_p = $this->db->dbprefix . $table_tp;													
							if ($table_tp != $parent_table){
								if (!array_key_exists($table_p, $related_table)){
									$related_table[$table_p]['join_table'] = $table;
									if ($this->db->field_exists($row['column'], $table)){
										$related_table[$table_p]['key1'] = $row['column'];	
									}
									else{
										$column = $this->find_real_column($table,$row['column'],'table');								
										if ($column != ''){
											$related_table[$table_p]['key1'] = $column;	
										}																
									}
									if ($this->db->field_exists($row['column'], $table_p)){
										$related_table[$table_p]['key0'] = $row['column'];
										$where_column = $row['column'];											
									}
									else{
										$column = $this->find_real_column($table_p,$row['column'],'table');								
										if ($column != ''){
											$related_table[$table_p]['key0'] = $column;	
											$where_column = $column;											
										}																
									}
									$column = $picklist[0]['picklist_name'];;
									$select[] = $table_p.'.'.$column;
									$uitype[$column] = $row['uitype_id'];
									if (!isset($select_to_arrange[$column])){
										$select_to_arrange[trim($column)] = ucwords($column);
									}
									else{
										$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
									}	

									if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
										$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));
										if ($wherein != ''){							
											$where[] = $table_p.".".$where_column." IN (".$wherein.")";															
										}	
									}								
								}							
							}
						}
						elseif($picklist[0]['picklist_type'] == "Query"){
							$picklist_table_query = str_replace('{dbprefix}', $this->db->dbprefix, $picklist[0]['picklist_table']);
							preg_match("/\s+FROM\s+`?([a-z\d_]+)`?/i", $picklist_table_query, $match);
							$table_p = $match[1];
							$table_tp = $table_p;								
							if (preg_match("/".$this->db->dbprefix."/", $table_p)){
								$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
							}
							$table_p = $this->db->dbprefix . $table_tp;
							if ($table_tp != $parent_table){	
								if (!array_key_exists($table_p, $related_table)){
									$key0 = '';	

									if ($this->db->field_exists($row['column'], $table_p)){
										$key0 = $row['column'];
									}
									else{
										$key0 = $this->find_real_column($picklist_table_query,$row['column']);								
									}

									if ($this->db->field_exists($row['column'], $table) && $key0 != ''){
										$related_table[$table_p]['join_table'] = $table;
										$related_table[$table_p]['key1'] = $row['column'];
										$related_table[$table_p]['key0'] = $key0;
										$column = $picklist[0]['picklist_name'];
										if ($this->db->field_exists($column, $table_p)){
											$select[] = $table_p.'.'.$column;
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] = ucwords($column);
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
											}																		
										}
										else{
											$column = $this->find_real_column($picklist_table_query,$column);
											if ($column != ''){
												$select[] = $table_p.'.'.$column;
												$uitype[$column] = $row['uitype_id'];
												if (!isset($select_to_arrange[$column])){
													$select_to_arrange[trim($column)] = ucwords($column);
												}
												else{
													$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
												}												
											}
										}	
										if ($this->input->post('multiselect-'.$row['fieldname']) != ''){
											$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));
											if ($wherein != ''){											
												if (preg_match("/".$this->db->dbprefix."/", $table_p)){
													$where[] = $table_p.".".$key0." IN (".$wherein.")";
												}
												else{
													$where[] = $this->db->dbprefix . $table_p.".".$key0." IN (".$wherein.")";
												}
											}
										}																	
									}																								
								}								
							}															
						}
						elseif ($picklist[0]['picklist_type'] == 'Function') {
							switch ($picklist[0]['picklist_name']) {
								case 'application_form_id':
									$related_table['employee_form_type']['join_table'] = $table;
									$related_table['employee_form_type']['key1'] = $key;
									$related_table['employee_form_type']['key0'] = $row['column'];
									$column = $picklist[0]['picklist_name'];
									if ($this->db->field_exists($column, 'employee_form_type')){
										$select[] = 'employee_form_type.'.$column;							
										$uitype[$column] = $row['uitype_id'];
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
										}
										if ($this->input->post('multiselect-'.$row['fieldname']) != ''){		
											$wherein = implode(",", $this->input->post('multiselect-'.$row['fieldname']));
											if ($wherein != ''){
												$where[] = "employee_form_type.".$row['column']." IN (".$wherein.")";															
											}
										}										
									}								
									break;
							}
						}														
						break;
					case 24:
						$field_from = $row['fieldname'].'_from';
						$field_to = $row['fieldname'].'_to';
						$select[] = $table.'.'.$field_from;
						$select[] = $table.'.'.$field_to;					
						$uitype[$field_from] = $row['uitype_id'];	
						$uitype[$field_to] = $row['uitype_id'];												
						$select_to_arrange[$field_from] = ucfirst($row['fieldname']) . ' From';
						$select_to_arrange[$field_to] = ucfirst($row['fieldname']) . ' To';												
						if ($this->input->post('date-temp-from') != '' && $this->input->post('date-temp-to') != ''){
							$where[] = '(date_from >= \'' . date('Y-m-d',strtotime($this->input->post('date-temp-from'))) . '\'
											AND 
										 date_to <= \''. date('Y-m-d',strtotime($this->input->post('date-temp-to') . '+1 day')) . '\')';							
						}
						break;
					case 38:
						$select[] = $table.'.time_start';
						$select[] = $table.'.time_end';					
						$uitype['time_start'] = $row['uitype_id'];						
						$uitype['time_end'] = $row['uitype_id'];	
						$select_to_arrange['time_start'] = 'Time Start';
						$select_to_arrange['time_end'] = 'Time End';												
						if ($this->input->post('time_start') != '' && $this->input->post('time_end') != ''){
							$where[] = '(time_start >= \'' . date('Y-m-d',strtotime($this->input->post('time_start'))) . '\'
											AND 
										 time_end <= \''. date('Y-m-d',strtotime($this->input->post('time_end') . '+1 day')) . '\')';							
						}
						break;						
					case 40:
						$select[] = $table.'.datetime_from';
						$select[] = $table.'.datetime_to';					
						$uitype['datetime_from'] = $row['uitype_id'];						
						$uitype['datetime_to'] = $row['uitype_id'];
						$select_to_arrange['datetime_from'] = 'DateTime From';
						$select_to_arrange['datetime_to'] = 'DateTime To';													
						if ($this->input->post('datetime_from') != '' && $this->input->post('datetime_to') != ''){
							$where[] = '(datetime_from >= \'' . date('Y-m-d',strtotime($this->input->post('datetime_from'))) . '\'
											AND 
										 datetime_to <= \''. date('Y-m-d',strtotime($this->input->post('datetime_to') . '+1 day')) . '\')';
						}
						break;
					case 5:
						// $select[] = $table.'.'.$row['fieldname'] ;
						$select_alias = $table.'.'.$row['fieldname'] ;
						if ($table == 'employee_family' && $row['fieldname'] == 'birth_date') {
							$select_alias = $table.'.'.$row['fieldname']. ' AS ' . $table.'_'.$row['fieldname'];
						}
						$select[] = $select_alias;
						
						$uitype[$row['fieldname']] = $row['uitype_id'];

						if (!isset($select_to_arrange[$row['fieldname']])){
							$select_to_arrange[trim($row['fieldname'])] = $row['fieldlabel'];

						}
						else{
							$select_to_arrange[trim($row['fieldname']).'|'.$ctr] = $row['fieldlabel'];
							if ($table == 'employee_family' && $row['fieldname'] == 'birth_date') {
								$select_to_arrange[trim($table.'_'.$row['fieldname']).'|'.$ctr] = $row['fieldlabel'];
							}
							
						}		

						if ($this->input->post('date-temp-from') != '' && $this->input->post('date-temp-to') != ''){						
							$where[] = DATE.'('.$this->db->dbprefix . $table.".".$row['fieldname'].") BETWEEN '".date('Y-m-d',strtotime($this->input->post($row['fieldname'].'-temp-from')))."' AND '".date('Y-m-d',strtotime($this->input->post($row['fieldname'].'-temp-to')))."'";
						}
						elseif( $this->input->post($row['fieldname'].'-temp-from') != '' && $this->input->post($row['fieldname'].'-temp-to') != '' ){
							$where[] = DATE.'('.$this->db->dbprefix . $table.".".$row['fieldname'].") BETWEEN '".date('Y-m-d',strtotime($this->input->post($row['fieldname'].'-temp-from')))."' AND '".date('Y-m-d',strtotime($this->input->post($row['fieldname'].'-temp-to')))."'";
						}

/*						if ($this->input->post($row['fieldname'].'-temp') && $this->input->post($row['fieldname'].'-temp') != ''){
							$where[] = DATE.'('.$this->db->dbprefix . $table.".".$row['fieldname'].") = '".date('Y-m-d',strtotime($this->input->post($row['fieldname'].'-temp')))."'";
						}	*/				
						break;
					default:
						$select[] = $table.'.'.$row['fieldname'];
						$uitype[$row['fieldname']] = $row['uitype_id'];
						if (!isset($select_to_arrange[$row['fieldname']])){
							$select_to_arrange[trim($row['fieldname'])] = $row['fieldlabel'];
						}
						else{
							$select_to_arrange[trim($row['fieldname']).'|'.$ctr] = $row['fieldlabel'];	
						}						
						if ($this->input->post($row['fieldname']) && $this->input->post($row['fieldname']) != ''){
							if(preg_match('/date/i', $row['fieldname'])) {
								$where[] = DATE.'('.$this->db->dbprefix . $table.".".$row['fieldname'].") = '".date('Y-m-d',strtotime($this->input->post($row['fieldname'])))."'";									
							}	
							else{
								$where[] = $this->db->dbprefix . $table.".".$row['fieldname']." = '".$this->input->post($row['fieldname'])."'";
							}							
						}
						break;
				}		
				$ctr++;
			}
		}

/*		dbug($related_table);
		return;*/

		$search = 1;

		$orderby = implode(",", $this->orderby($this->input->post('orderby')));

		$this->db->select( implode( ',', $select ),false );
		if (sizeof($where) > 0){
			$where = implode(' AND ',$where );	
			
		}else{
			$where = 1;
		}

		$this->db->where($parent_table.'.deleted = 0 AND ' . $where);	

		foreach ($related_table as $table => $join_t_k) {
			if (isset($join_t_k['real_table'])){
				$real_table = $join_t_k['real_table'];
				$this->db->join($real_table .' '. $table, $table .'.'. $join_t_k['key0'] .'='. $join_t_k['join_table'] .'.'. $join_t_k['key1'], 'left');
			}
			if (isset($join_t_k['customize'])){
				$real_table = $join_t_k['join_table'];
				$this->db->join($real_table, $join_t_k['customize'],'left',false);
			}			
			else{
				$this->db->join($table, $table .'.'. $join_t_k['key0'] .'='. $join_t_k['join_table'] .'.'. $join_t_k['key1'], 'left');
			}
		}

		if ($groupby != ''){
			$this->db->group_by($groupby);
		}

		if ($orderby != ''){
			$this->db->order_by($orderby .' '. $this->input->post('sortby'));			
		}

		$q = $this->db->get($parent_table);

/*		dbug($this->db->last_query());
		return;
*/
		$with_records = false;

		if ($q && $q->num_rows() > 0){
			$with_records = true;

			$query  = $q;
			$fields = $q->list_fields();

			//$export = $this->_export;

			$this->load->library('PHPExcel');		
			$this->load->library('PHPExcel/IOFactory');

			$objPHPExcel = new PHPExcel();

			$objPHPExcel->getProperties()->setTitle($this->input->post('title_report'))
			            ->setDescription($this->input->post('title_report'));
			               
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
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);	*/				
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

			$styleArrayBorder = array(
			  	'borders' => array(
			    	'allborders' => array(
			      		'style' => PHPExcel_Style_Border::BORDER_THIN
			    	)
			  	)
			);

			$sorted_fields = $select_to_arrange;
			$fields_sorted_row = array_keys($sorted_fields);

	/*		dbug($sorted_fields);
			dbug($fields_sorted_row);
			dbug($fields);
			return;*/

			foreach ($sorted_fields as $field) {
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}

				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$field = str_replace('_', ' ', $field);
				$activeSheet->setCellValue($xcoor . '6', ucwords($field));

				$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
				
				$alpha_ctr++;
			}

			for($ctr=1; $ctr<6; $ctr++){

				$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);

			}

			if ($alpha_ctr == 26){
				if ($alpha_ctr >= count($alphabet)) {
					$alpha_ctr = 0;
					$sub_ctr++;
				}
								
				if ($sub_ctr > 0) {
					$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				} else {
					$xcoor = $alphabet[$alpha_ctr];
				}

				$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
				$activeSheet->setCellValue($xcoor . '6', "");
			}

			//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
			$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$this->userinfo['firstname'].' '.$this->userinfo['lastname'].' &RPage &P of &N');

			$activeSheet->setCellValue('A1', $meta['title']);
			if ($this->input->post('title_report') != ''){
				$activeSheet->setCellValue('A2', $this->input->post('title_report'));
				$activeSheet->setCellValue('A3', 'As of ' . date('M d, Y'));
			}
			else{
				$activeSheet->setCellValue('A2', 'As of ' . date('M d, Y'));
			}

			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

			// contents.
			$line = 7;
			foreach ($query->result() as $row) {
				$sub_ctr   = 0;			
				$alpha_ctr = 0;
				foreach ($fields_sorted_row as $field) {
					if (preg_match("/\|.*/", $field)){
						$field = preg_replace("/\|.*/", "", $field);
					}							
					if ($alpha_ctr >= count($alphabet)) {
						$alpha_ctr = 0;
						$sub_ctr++;
					}

					if ($sub_ctr > 0) {
						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
					} else {
						$xcoor = $alphabet[$alpha_ctr];
					}

					$uitype_id = $uitype[$field];

					switch ($uitype_id) {
						case 1:
							if ($field == 'salary'){
								$val_enc = $row->{$field};
								$val = number_format($this->encrypt->decode($val_enc),2,'.',',');
							}
							else{
								$val = $row->{$field};
							}
							break;						
						case 4:
							$val = ($row->{$field} == 1 ? 'Yes' : 'No');
							break;
						case 20:
							if ($row->{$field} != ''){
								$result = $this->db->get_where('file_upload',array("upload_id"=>$row->{$field}));
								if ($result && $result->num_rows() > 0){
									$single_row = $result->row();
									$val = $single_row->upload_path;
								}
								else{
									$val = '';
								}
							}
							else{
								$val = '';
							}
							break;
						case 24:
							if ($row->{$field} != '' && $row->{$field} != NULL && $row->{$field} != '0000-00-00'){
								$val = date($this->config->item('display_date_format'),strtotime($row->{$field}));
							}
							else{
								$val = '';
							}
							break;	
						case 38:
							if ($row->{$field} != '' && $row->{$field} != NULL){
								$val = date($this->config->item('display_time_format'),strtotime($row->{$field}));
							}
							break;							
						case 40:
							if ($row->{$field} != '' && $row->{$field} != NULL && $row->{$field} != '0000-00-00 00:00:00'){
								$val = date($this->config->item('display_datetime_format'),strtotime($row->{$field}));
							}
							break;	
						case 36:
							$val = $row->{$field};
							//overriding
							switch ($field) {
								case 'sales_option':
									if ($row->{$field} != '' && $row->{$field} != NULL && $row->{$field} == 1){
										$val = "Sales";
									}
									else{
										$val = "Non-Sales";
									}
									break;
								default:
									if ($row->{$field} != '' && $row->{$field} != NULL && $row->{$field} == 0){
								    	$val = "No";
									}
									else{
										$val = "Yes";
									}
									break;
							}
							break;																	
						default:
							$val = $row->{$field};
							if(preg_match('/date/i', $field)) {
								if ($row->{$field} != '' && $row->{$field} != NULL && $row->{$field} != '0000-00-00 00:00:00' && $row->{$field} != '0000-00-00'){
							    	$val = date($this->config->item('display_date_format'),strtotime($row->{$field}));
								}
								else{
									$val = '';
								}
							}

							switch ($field) {
								case 'id_number':
									$val = '' . $row->{$field};
									break;
							}	

							break;
					}

					$objPHPExcel->getActiveSheet()->getCell($xcoor . $line)->setValueExplicit($val, PHPExcel_Cell_DataType::TYPE_STRING);
					//$objPHPExcel->getActiveSheet()->setCellValue($xcoor . $line, $val);

					$alpha_ctr++;
					$alpha_tmp = $alpha_ctr;

					if ($alpha_ctr == 26){
						if ($alpha_ctr >= count($alphabet)) {
							$alpha_ctr = 0;
							$sub_ctr++;
						}
										
						if ($sub_ctr > 0) {
							$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
						} else {
							$xcoor = $alphabet[$alpha_ctr];
						}

						$xcoor = $alphabet[$sub_ctr - 1] . $alphabet[$alpha_ctr];
						$activeSheet->setCellValue($xcoor . '6', "");
					}			
				}

				$line++;
			}

			$xcoor_tmp = $xcoor;
			if ($alpha_tmp == 26){
				$xcoor_tmp = "Z";
			}

			$objPHPExcel->getActiveSheet()->getStyle('A6:'.$xcoor_tmp.($line - 1))->applyFromArray($styleArrayBorder);

			for($i = 'A'; $i <= $xcoor; $i++) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($i)->setAutoSize(true);											    
			}
			// Save it as an excel 2003 file
			$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment;filename=' . date('Y-m-d') . ' ' .url_title($this->input->post('title_report')) . '.xls');
			header('Content-Transfer-Encoding: binary');

			$path = 'uploads/adhoc_report/'.url_title("Adhoc Report").'-'.strtotime(date('Y-m-d g:i:s')).'.xls';
			
			$objWriter->save($path);
		}

		if ($with_records){
			$response->msg_type = 'success';
		}
		else{
			$response->msg_type = 'attention';
		}

		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));
	}	

	function re_order_array($array1,$array2){
		$ordered_array = array();
		foreach ($array1 as $key => $value) {
			$array_value = $this->find_in_array($array2,$value);
			if (!empty($array_value)){
				$ordered_array[$key] = $array_value;
			}
		}
		return $ordered_array;
	}

	function find_in_array($array,$search_val){
		$array_value = array();
		foreach ($array as $key => $value) {
			if ($value['field_id'] == $search_val){
				$array_value = $value;
			}
		}
		return $array_value;
	}	

	function order_fields($order_fields = '',$parent_table = ''){
		if ($order_fields != ''){
			$select_to_arrange = array();
			$related_table = array();
			$ctr = 0;
			foreach ($order_fields as $value) {
				$this->db->select($this->db->dbprefix('field').'.table'.','.$this->db->dbprefix('field').'.column'.','.$this->db->dbprefix('field').'.fieldname'.','.$this->db->dbprefix('field').'.fieldlabel'.','.$this->db->dbprefix('field').'.uitype_id'.','.$this->db->dbprefix('field').'.field_id'.','.$this->db->dbprefix('module').'.key_field');
				$this->db->join($this->db->dbprefix('module'),$this->db->dbprefix('field').'.module_id = '.$this->db->dbprefix('module').'.module_id');
				$this->db->where('field_id',$value);
				$result = $this->db->get('field');
				if ($result && $result->num_rows() > 0){
					$row = $result->row();
					$table = $row->table;
					switch ($row->uitype_id) {
						case 13:				
							$this->db->select('a.module_id, a.column, field.table, field.fieldlabel, module.key_field');
							$this->db->from('field_module_link a');
							$this->db->join('module', 'module.module_id = a.module_id', 'left');
							$this->db->join('field', 'field.module_id = a.module_id', 'left');
							$this->db->where(array('a.field_id' => $row->field_id));
							$relate_module = $this->db->get();
							if($relate_module->num_rows() > 0){
								$relate_module = $relate_module->row_array();
								$column = $relate_module['column'];
								$fieldlabel = $relate_module['fieldlabel'];						
								$table_p = $relate_module['table'];
								if (!array_key_exists($table_p, $related_table)){	
									$related_table[$table_p]['join_table'] = $table;
									$related_table[$table_p]['key0'] =  $relate_module['key_field'];;
									$related_table[$table_p]['key1'] = $row->column;							
								}						
								if(strpos($column, ',')){
									$colum_lists = explode( ',', $column );
									foreach($colum_lists as $col_index => $column){
										$select[] = $table_p.'.'.$column;									
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);												
										}
									}
								}
							}	
							break;
						case 21:
							$relate_module = $this->db->get_where('field_multiselect', array('field_id' => $row->field_id));
							if($relate_module->num_rows() > 0)
							{
								$relate_module = $relate_module->row_array();							
								$column = $relate_module['name_column'];
								if( $relate_module['type'] == "Table"){								
									$table_p = $relate_module['table'];
									$id_column = $relate_module['id_column'];
									if (!array_key_exists($table_p, $related_table)){	
										$related_table[$table_p]['join_table'] = $table;
										$related_table[$table_p]['key0'] = $id_column;
										$related_table[$table_p]['key1'] = $row->column;							
									}						
									if(strpos($column, ',')){
										$colum_lists = explode( ',', $column );
										foreach($colum_lists as $col_index => $column){
											$select[] = $table_p.'.'.$column;
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] = ucwords($column);
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
											}										
										}
									}
									else{
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
										}									
									}															
								}
								elseif( $relate_module['type'] == "Query"){
									$table_query = str_replace('{dbprefix}', $this->db->dbprefix, $relate_module['table']);
									preg_match("/\s+FROM\s+`?([a-z\d_]+)`?/i", $table_query, $match);
									$table_p = $match[1];
									$table_tp = $table_p;
									if (preg_match("/".$this->db->dbprefix."/", $table_p)){
										$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
									}										
									if ($table_tp != $parent_table){	
										if (!array_key_exists($table_p, $related_table)){
											$key0 = '';
											if ($this->db->field_exists($row->column, $table_p)){
												$key0 = $row->column;
											}
											else{
												$key0 = $this->find_real_column($table_query,$row->column);								
											}

											if ($this->db->field_exists($row->column, $table) && $key0 != ''){
												$related_table[$table_p]['join_table'] = $table;
												$related_table[$table_p]['key1'] = $row->column;
												$related_table[$table_p]['key0'] = $key0;
											}
											if ($this->input->post($row->fieldname) && $this->input->post($row->fieldname) != ''){							
												$where[] = $this->db->dbprefix . $table_p.".".$key0." = '".$this->input->post($row->fieldname)."'";							
											}										
										}
										if(strpos($column, ',')){
											$colum_lists = explode( ',', $column );
											foreach($colum_lists as $col_index => $column){
												$select[] = $table_p.'.'.$column;
												if (!isset($select_to_arrange[$column])){
													$select_to_arrange[trim($column)] = ucwords($column);
												}
												else{
													$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
												}	
											}
										}	
										else{
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] = ucwords($column);
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
											}									
										}																		
									}																
								}								
							}							
						case 39:
							//get picklist
							$picklist = $this->db->get_where('field_autocomplete', array('field_id' => $row->field_id));				
							$picklist = $picklist->result_array();
							if( $picklist[0]['type'] == "Table"){
								$table_p = $picklist[0]['table'];
								$table_tp = $table_p;
								if (preg_match("/".$this->db->dbprefix."/", $table_p)){
									$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
								}								
								if ($table_tp != $parent_table){	
									if (!array_key_exists($table_p, $related_table)){
										$related_table[$table_p]['join_table'] = $table;
										if ($this->db->field_exists($row->column, $table)){
											$related_table[$table_p]['key1'] = $row->column;	
										}
										else{
											$column = $this->find_real_column($table,$row->column,'table');								
											if ($column != ''){
												$related_table[$table_p]['key1'] = $column;	
											}																
										}
										if ($this->db->field_exists($row->column, $table_p)){
											$related_table[$table_p]['key0'] = $row->column;	
										}
										else{
											$column = $this->find_real_column($table_p,$row->column,'table');								
											if ($column != ''){
												$related_table[$table_p]['key0'] = $column;	
											}																
										}
										$column = $picklist[0]['label'];
										//$select[] = $table_p.'.'.$column;	
/*										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
										}	*/																						
									}	
								}						
								if(strpos($picklist[0]['label'], ',')){
									$colum_lists = explode( ',', $picklist[0]['label'] );									
									foreach($colum_lists as $col_index => $column){								
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);												
										}
									}
								}
								else{								
									if (!isset($select_to_arrange[$picklist[0]['label']])){
										$select_to_arrange[trim($picklist[0]['label'])] = ucwords($picklist[0]['label']);
									}
									else{
										$select_to_arrange[trim($picklist[0]['label']).'|'.$ctr] = ucwords($picklist[0]['label']);												
									}																		
								}																										
							}
							elseif($picklist[0]['type'] == "Query"){
								$key = $picklist[0]['value'];
								$column = $picklist[0]['label'];
								$picklist_table_query = str_replace('{dbprefix}', $this->db->dbprefix, $picklist[0]['table']);
								preg_match("/\s+FROM\s+`?([a-z\d_]+)`?/i", $picklist_table_query, $match);
								$table_p = $match[1];
								$table_tp = $table_p;
								if (preg_match("/".$this->db->dbprefix."/", $table_p)){
									$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
								}								
								if ($table_tp != $parent_table){	
									if (!array_key_exists($table_p, $related_table)){
										$key0 = '';
										if ($this->db->field_exists($row->column, $table_p)){
											$key0 = $row->column;
										}
										else{
											$key0 = $this->find_real_column($picklist_table_query,$row->column);								
										}

										if ($this->db->field_exists($row->column, $table) && $key0 != ''){
											$related_table[$table_p]['join_table'] = $table;
											$related_table[$table_p]['key1'] = $row->column;
											$related_table[$table_p]['key0'] = $key0;
											$column = $picklist[0]['label'];
											$select[] = $table_p.'.'.$column;
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] = ucwords($column);
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
											}
										}
									}	
								}										
							}
							elseif ($picklist[0]['type'] == 'Function') {
								switch ($picklist[0]['value']) {
									case 'application_form_id':
										$related_table['employee_form_type']['join_table'] = $table;
										$related_table['employee_form_type']['key1'] = $picklist[0]['value'];
										$related_table['employee_form_type']['key0'] = $row->column;
										$column = $picklist[0]['label'];
										if ($this->db->field_exists($column, 'employee_form_type')){
											$select[] = 'employee_form_type.'.$column;
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] =  ucwords(str_replace('_', ' ', $column));
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] =  ucwords(str_replace('_', ' ', $column));
											}																		
										}										
										break;
									case 'employee_id':
									case 'user_id':
										$related_table['user']['join_table'] = $table;
										$related_table['user']['key1'] = $picklist[0]['value'];
										$related_table['user']['key0'] = $row->column;
										$column = $picklist[0]['label'];
										if(strpos($column, ',')){
											$colum_lists = explode( ',', $column );
											foreach($colum_lists as $col_index => $column){
												$select[] = 'user.'.$column;
												if (!isset($select_to_arrange[$column])){
													$select_to_arrange[trim($column)] =  ucwords(str_replace('_', ' ', $column));
												}
												else{
													$select_to_arrange[trim($column).'|'.$ctr] =  ucwords(str_replace('_', ' ', $column));
												}												
											}
										}
										else{										
											if ($this->db->field_exists($column, 'employee_form_type')){
												$select[] = 'user.'.$column;							
												if (!isset($select_to_arrange[$column])){
													$select_to_arrange[trim($column)] =  ucwords(str_replace('_', ' ', $column));
												}
												else{
													$select_to_arrange[trim($column).'|'.$ctr] =  ucwords(str_replace('_', ' ', $column));
												}										
											}	
										}									
										break;											
								}
							}												
							break;
						case 3:
							$realfieldcolumn = $field['column'];
							//get picklist
							$picklist = $this->db->get_where('picklist', array('field_id' => $row->field_id));
							$picklist = $picklist->result_array();										
							if( $picklist[0]['picklist_type'] == "Table"){					
								$table_p = $picklist[0]['picklist_table'];
								$table_tp = $table_p;
								if (preg_match("/".$this->db->dbprefix."/", $table_p)){
									$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
								}								
								if ($table_tp != $parent_table){
									if (!array_key_exists($table_p, $related_table)){
										$related_table[$table_p]['join_table'] = $table;
										if ($this->db->field_exists($row->column, $table)){
											$related_table[$table_p]['key1'] = $row->column;	
										}
										else{
											$column = $this->find_real_column($table,$row->column,'table');								
											if ($column != ''){
												$related_table[$table_p]['key1'] = $column;	
											}																
										}
										if ($this->db->field_exists($row->column, $table_p)){
											$related_table[$table_p]['key0'] = $row->column;	
										}
										else{
											$column = $this->find_real_column($table_p,$row->column,'table');								
											if ($column != ''){
												$related_table[$table_p]['key0'] = $column;	
											}																
										}
										$column = $picklist[0]['picklist_name'];;
										$select[] = $table_p.'.'.$column;
										if (!isset($select_to_arrange[$column])){
											$select_to_arrange[trim($column)] = ucwords($column);
										}
										else{
											$select_to_arrange[trim($column).'|'.$ctr] = ucwords($column);	
										}
									}							
								}
							}
							elseif($picklist[0]['picklist_type'] == "Query"){
								$picklist_table_query = str_replace('{dbprefix}', $this->db->dbprefix, $picklist[0]['picklist_table']);
								preg_match("/\s+FROM\s+`?([a-z\d_]+)`?/i", $picklist_table_query, $match);
								$table_p = $match[1];	
								$table_tp = $table_p;							
								if (preg_match("/".$this->db->dbprefix."/", $table_p)){
									$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
								}
								if ($table_tp != $parent_table){	
									if (!array_key_exists($table_p, $related_table)){
										$key0 = '';
										if ($this->db->field_exists($row->column, $table_p)){
											$key0 = $row->column;
										}
										else{
											$key0 = $this->find_real_column($picklist_table_query,$row->column);								
										}

										if ($this->db->field_exists($row->column, $table) && $key0 != ''){
											$related_table[$table_p]['join_table'] = $table;
											$related_table[$table_p]['key1'] = $row->column;
											$related_table[$table_p]['key0'] = $key0;
											$column = $picklist[0]['picklist_name'];

											if ($this->db->field_exists($column, $table_p)){
												$select[] = $table_p.'.'.$column;	
												if (!isset($select_to_arrange[$column])){
													$select_to_arrange[trim($column)] = $column;
												}
												else{
													$select_to_arrange[trim($column).'|'.$ctr] = $column;	
												}																			
											}
											else{
												$column = $this->find_real_column($picklist_table_query,$column);
												if ($column != ''){
													$select[] = $table_p.'.'.$column;
													if (!isset($select_to_arrange[$column])){
														$select_to_arrange[trim($column)] = $column;
													}
													else{
														$select_to_arrange[trim($column).'|'.$ctr] = $column;	
													}													
												}
											}									
										}																
									}	
								}										
							}
							elseif ($picklist[0]['picklist_type'] == 'Function') {
								switch ($picklist[0]['picklist_name']) {
									case 'application_form_id':
										$related_table['employee_form_type']['join_table'] = $table;
										$related_table['employee_form_type']['key1'] = $key;
										$related_table['employee_form_type']['key0'] = $row->column;
										$column = $picklist[0]['picklist_name'];
										if ($this->db->field_exists($column, 'employee_form_type')){
											$select[] = 'employee_form_type.'.$column;							
											if (!isset($select_to_arrange[$column])){
												$select_to_arrange[trim($column)] =  ucwords(str_replace('_', ' ', $column));
											}
											else{
												$select_to_arrange[trim($column).'|'.$ctr] =  ucwords(str_replace('_', ' ', $column));
											}	
										}								
										break;
								}
							}															
							break;
						case 24:
							$select[] = $table.'.date_from';
							$select[] = $table.'.date_to';
							$select_to_arrange['date_from'] = 'Date From';
							$select_to_arrange['date_to'] = 'Date To';						
							break;
						case 38:
							$select[] = $table.'.time_start';
							$select[] = $table.'.time_end';
							$select_to_arrange['time_start'] = 'Time Start';
							$select_to_arrange['time_end'] = 'Time End';						
							break;							
						case 40:
							$select[] = $table.'.datetime_from';
							$select[] = $table.'.datetime_to';
							$select_to_arrange['datetime_from'] = 'DateTime From';
							$select_to_arrange['datetime_to'] = 'DateTime To';						
							break;							
						default:
							$select[] = $table.'.'.$row->fieldname;
							if (!isset($select_to_arrange[$row->fieldname])){
								$select_to_arrange[trim($row->fieldname)] = $row->fieldlabel;
							}
							else{
								$select_to_arrange[trim($row->fieldname).'|'.$ctr] = $row->fieldlabel;	
							}								
							break;
					}							
				}
			}
			return $select_to_arrange;
		}
	}

	function orderby($fields = '')
	{	
		$this->db->select($this->db->dbprefix('field').'.table'.','.$this->db->dbprefix('field').'.column'.','.$this->db->dbprefix('field').'.fieldname'.','.$this->db->dbprefix('field').'.fieldlabel'.','.$this->db->dbprefix('field').'.uitype_id'.','.$this->db->dbprefix('field').'.field_id'.','.$this->db->dbprefix('module').'.key_field');
		$this->db->join($this->db->dbprefix('module'),$this->db->dbprefix('field').'.module_id = '.$this->db->dbprefix('module').'.module_id');
		$this->db->where_in('field_id',$fields);
		$this->db->order_by($this->db->dbprefix('module').'.sequence','ASC');
		$result = $this->db->get('field');

		$orderby = array();
		if ($result && $result->num_rows() > 0){
			foreach ($result->result() as $row) {
				$table = $row->table;
				switch ($row->uitype_id) {
					case 13:
						$this->db->select('a.module_id, a.column, field.table, field.fieldlabel, module.key_field');
						$this->db->from('field_module_link a');
						$this->db->join('module', 'module.module_id = a.module_id', 'left');
						$this->db->join('field', 'field.module_id = a.module_id', 'left');
						$this->db->where(array('a.field_id' => $row->field_id));
						$relate_module = $this->db->get();
						if($relate_module->num_rows() > 0){
							$relate_module = $relate_module->row_array();
							$column = $relate_module['column'];
							$table_p = $relate_module['table'];
							if(strpos($column, ',')){
								$colum_lists = explode( ',', $column );
								foreach($colum_lists as $col_index => $column){
									$orderby[] = $table_p.'.'.$column;
								}
							}
						}	
						break;
					case 21:
						$relate_module = $this->db->get_where('field_multiselect', array('field_id' => $row->field_id));
						if($relate_module->num_rows() > 0)
						{
							$relate_module = $relate_module->row_array();
							$column = $relate_module['name_column'];							
							if( $relate_module['type'] == "Table"){							
								$table_p = $relate_module['table'];
								if(strpos($column, ',')){
									$colum_lists = explode( ',', $column );
									foreach($colum_lists as $col_index => $column){
										$orderby[] = $table_p.'.'.$column;
									}
								}
								else{
									$orderby[] = $table_p.'.'.$column;								
								}
							}						
							elseif( $relate_module['type'] == "Query"){
								$table_query = str_replace('{dbprefix}', $this->db->dbprefix, $relate_module['table']);
								preg_match("/\s+FROM\s+`?([a-z\d_]+)`?/i", $table_query, $match);
								$table_p = $match[1];
								$table_tp = $table_p;
								if (preg_match("/".$this->db->dbprefix."/", $table_p)){
									$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
								}									
								if(strpos($column, ',')){
									$colum_lists = explode( ',', $column );
									foreach($colum_lists as $col_index => $column){
										$orderby[] = $table_p.'.'.$column;
									}
								}	
								else{
									$orderby[] = $table_p.'.'.$column;								
								}															
							}
						}						
					case 39:
						//get picklist
						$picklist = $this->db->get_where('field_autocomplete', array('field_id' => $row->field_id));				
						$picklist = $picklist->result_array();
						$where_column = '';
						if( $picklist[0]['type'] == "Table"){
							$table_p = $picklist[0]['table'];
							$table_tp = $table_p;
							if (preg_match("/".$this->db->dbprefix."/", $table_p)){
								$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
							}							
							if(strpos($picklist[0]['label'], ',')){
								$colum_lists = explode( ',', $picklist[0]['label'] );
								foreach($colum_lists as $col_index => $column){
									$orderby[] = $table_p.'.'.$column;
								}
							}
							else{
								$orderby[] = $table_p.'.'.$picklist[0]['label'];								
							}																		
						}
						elseif($picklist[0]['type'] == "Query"){
							$key = $picklist[0]['value'];
							$column = $picklist[0]['label'];
							$picklist_table_query = str_replace('{dbprefix}', $this->db->dbprefix, $picklist[0]['table']);
							preg_match("/\s+FROM\s+`?([a-z\d_]+)`?/i", $picklist_table_query, $match);
							$table_p = $match[1];
							$table_tp = $table_p;
							if (preg_match("/".$this->db->dbprefix."/", $table_p)){
								$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
							}		
							$orderby[] = $table_p.'.'.$column;									
						}
						elseif ($picklist[0]['type'] == 'Function') {
							switch ($picklist[0]['value']) {
								case 'application_form_id':
									$column = $picklist[0]['label'];
									if ($this->db->field_exists($column, 'employee_form_type')){
										$orderby[] = 'employee_form_type.'.$column;																		
									}										
									break;
								case 'employee_id':
								case 'user_id':
									$column = $picklist[0]['label'];
									if(strpos($column, ',')){
										$colum_lists = explode( ',', $column );
										foreach($colum_lists as $col_index => $column){
											$orderby[] = 'user.'.$column;
										}
									}
									else{										
										if ($this->db->field_exists($column, 'employee_form_type')){
											$orderby[] = 'user.'.$column;																	
										}	
									}									
									break;									
							}
						}										
						break;
					case 3:
						$realfieldcolumn = $field['column'];
						//get picklist
						$picklist = $this->db->get_where('picklist', array('field_id' => $row->field_id));
						$picklist = $picklist->result_array();	
						$where_column = '';									
						if( $picklist[0]['picklist_type'] == "Table"){					
							$table_p = $picklist[0]['picklist_table'];
							$table_tp = $table_p;
							if (preg_match("/".$this->db->dbprefix."/", $table_p)){
								$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
							}			
							$column = $picklist[0]['picklist_name'];;
							$orderby[] = $table_p.'.'.$column;
						}
						elseif($picklist[0]['picklist_type'] == "Query"){
							$picklist_table_query = str_replace('{dbprefix}', $this->db->dbprefix, $picklist[0]['picklist_table']);
							preg_match("/\s+FROM\s+`?([a-z\d_]+)`?/i", $picklist_table_query, $match);
							$table_p = $match[1];			
							$table_tp = $table_p;					
							if (preg_match("/".$this->db->dbprefix."/", $table_p)){
								$table_tp = preg_replace("/".$this->db->dbprefix."/", "", $table_p);
							}
							$column = $picklist[0]['picklist_name'];
							if ($this->db->field_exists($column, $table_p)){
								$orderby[] = $table_p.'.'.$column;							
							}
							else{
								$column = $this->find_real_column($picklist_table_query,$column);
								if ($column != ''){
									$orderby[] = $table_p.'.'.$column;
								}
							}										
						}
						elseif ($picklist[0]['picklist_type'] == 'Function') {
							switch ($picklist[0]['picklist_name']) {
								case 'application_form_id':
									$column = $picklist[0]['picklist_name'];
									if ($this->db->field_exists($column, 'employee_form_type')){
										$orderby[] = 'employee_form_type.'.$column;																
									}								
									break;
							}
						}														
						break;
					case 24:
						$orderby[] = $table.'.date_from';
						$orderby[] = $table.'.date_to';					
						break;
					case 38:
						$orderby[] = $table.'.time_start';
						$orderby[] = $table.'.time_end';					
						break;						
					case 40:
						$orderby[] = $table.'.datetime_from';
						$orderby[] = $table.'.datetime_to';					
						break;						
					default:
						$orderby[] = $table.'.'.$row->fieldname;
						break;
				}				
			}
		}
		return $orderby;
	}	

	function find_real_column($str = '',$str_to_search = '',$type = 'string'){
		$column = '';
		if ($type == 'string'){
			if ($str != '' && $str_to_search != ''){
				$startsAt = strpos($str, "SELECT") + strlen("SELECT");
				$endsAt = strpos($str, "FROM", $startsAt);
				$result_str = substr($str, $startsAt, $endsAt - $startsAt);
				$result_str = str_replace(' AS ', ' as ', $result_str);	
				$data = array();
				foreach (explode(",", $result_str) as $cLine) {
				    list ($cKey, $cValue) = explode('as', $cLine, 2);
				    if ($cKey != '' && $cValue != ''){
				    	$data[trim($cKey)] = trim($cValue);
					}
				}

				if (sizeof($data) < 1){
					$result = $this->db->query($str);
					$fields = $result->list_fields();	
					$column	= $fields[0];				
				}
				else{
					$column = array_search($str_to_search, $data);
				}
			}
		}
		elseif ($type == 'table') {
			$fields = $this->db->list_fields($str);
			$column = $fields[0];
		}
		return $column;
	}	

	function fieldGroup_ddlb( $fieldgroup_id = 0 )
	{
		$fg = $this->db->get_where('fieldgroup', array('fieldgroup_id' => $fieldgroup_id));
		if($this->db->_error_message() == "")
		{
			if( $fg->num_rows() > 0 && $fg->num_rows() == 1 )
			{
				$fg = $fg->row_array();
				$module_id = $fg['module_id'];

				//create dropdown
				$this->db->order_by('sequence');
				$field_group = $this->db->get_where('fieldgroup', array('module_id' => $module_id));
				if( $this->db->_error_message() == "" )
				{
					if( $field_group->num_rows() > 0 )
					{
						$field_group = $field_group->result();
						$str = '<select name="fieldgroup_id" id="fieldgroup_id">';
						foreach($field_group as $row)
						{
							$selected = "";
							if( $row->fieldgroup_id == $fieldgroup_id ) $selected = 'selected';
							$str .= '<option value="'. $row->fieldgroup_id .'" '. $selected .'>'. $row->fieldgroup_label .'</option>';
						}
						$str .= '</select>';
						return $str ;
					}
				}
				else{
					return $this->db->_error_message();
				}
			}
			else{
				return "Fieldgroup width fieldgroup_id ". $fieldgroup_id ." was not found.";
			}
		}
		else{
			return $this->db->_error_message();
		}
	}

	function listview_boxy( $field_id = 0, $name = "", $value = "", $tabindex, $readonly )
	{
		if( empty($value) || $value=='undefined'){
			//new module
			$value = "";
			$valuename = "";
		}
		else{
			//get field_module_link
			$this->db->select('a.module_id, a.column, field.table, module.key_field');
			$this->db->from('field_module_link a');
			$this->db->join('module', 'module.module_id = a.module_id', 'left');
			$this->db->join('field', 'field.module_id = a.module_id', 'left');
			$this->db->where(array('a.field_id' => $field_id));

			$module = $this->db->get();
			
			if( $this->db->_error_message() == "" )
			{
				if($module->num_rows() > 0)
				{
					$module = $module->row_array();
					$module_id = $module['module_id'];
					$column = $module['column'];
					$table = $module['table'];
					$key_field = $module['key_field'];
					$this->db->select($column);
					$valuename = $this->db->get_where($table, array($key_field => $value));
					if( $this->db->_error_message() == "" )
					{
						if($valuename->num_rows() > 0)
						{
							$valuename = $valuename->row_array();
							if( strpos($column, ',') )
							{
								$temp_val = array();
								$column_lists = explode( ',', $column);
								foreach($column_lists as $col_index => $column)
								{
									$temp_val[] = $valuename[$column];
								}
								$valuename = implode(' ', $temp_val);
							}
							else{
								if(sizeof(explode(' AS ', $column)) > 1 ){
									$as_part = explode(' AS ', $column);
									$column = strtolower( trim( $as_part[1] ) );
								}
								else if(sizeof(explode(' as ', $column)) > 1 ){
									$as_part = explode(' as ', $column);
									$column = strtolower( trim( $as_part[1] ) );
								}
								$valuename = $valuename[$column];
							}
						}
						else{
							$valuename = "";
							$value = "";
						}
					}
					else{
						return $this->db->_error_message();
					}
				}
			}
			else{
				return $this->db->_error_message();
			}
		}

		if( $this->module != "picklist" )
		{
			$width = '80';
		}
		else{
			$width = '90';
		}
         
		$str = '<div class="text-input-wrap">
				<input type="hidden" name="'.$name.'" id="'.$name.'" value="'. $value .'" class="input-text"/>
				<input type="text" name="'.$name.'-name" id="'.$name.'-name" value="'. $valuename .'" class="input-text disabled" style="width:'.$width.'%" disabled="disabled"/>';
		if( $this->module != "picklist" && !$readonly ) $str .= '<span class="icon-group">
		<a class="icon-button icon-16-add" href="javascript:void(0);" onclick="getRelatedModule(\''.$field_id.'\', \''. $name .'\')"></a><a class="icon-button icon-16-minus" href="javascript:void(0);" onclick="clearField(\''. $name .'\')"></a>
		</span>';
		return 	$str.'</div>';
	}

	function picklist( $field_id = 0, $name = "", $value = "", $disabled = false, $tabindex = '')
	{
		//get detail of dropdown
		$picklist = $this->db->get_where('picklist', array('field_id' => $field_id));
		if( $picklist->num_rows() )
		{
			$picklist = $picklist->row_array();
			$id_column = $picklist['picklist_name'].'_id';
			$name_column = $picklist['picklist_name'];
			$picklist_table = $picklist['picklist_table'];
			$picklist_type = $picklist['picklist_type'];
			$picklist_where = $picklist['where'];

			//get actual values from table
			if($picklist_type == "Table")
			{
				$this->db->select($id_column.', '.$name_column);
				$this->db->from($picklist_table);
				$this->db->order_by($name_column);
				$this->db->where(array('deleted' => 0));
				if( !empty($picklist_where) ) $this->db->where($picklist_where);
				$picklistvalues = $this->db->get()->result_array();
			} else if ($picklist_type == 'Function') {						
				$picklistvalues = $this->{$row->table}();
			} else if($picklist_type == "Query"){
				$picklistvalues = $this->db->query(str_replace('{dbprefix}', $this->db->dbprefix, $picklist_table))->result_array();			
			}

			if( $this->db->_error_message() == "" )
			{
				$str = '<div class="multiselect-input-wrap">';
				$str .= $disabled ?	'<input type="checkbox" name="toggle-'. $name .'"/> ' : '';
				$str .= '<input type="hidden" name="'. $name.'" id="'.$name.'" value="'.$value.'"/>';
				$str .= '<select ' . $tabindex . ' id="multiselect-'. $name.'" name="multiselect-'. $name .'" multiple="multiple" '. ( $disabled ? 'disabled="disable"' : '') .' class="multiselect" style="width:400px">';				
/*				$str .= '<select ' . $tabindex . ' name="'.$name.'" id="'. $name.'" '. ( $disabled ? 'disabled="disable"' : '') .'><option value="">Select&hellip;</option>';				*/
				foreach($picklistvalues as $index => $option)
				{					
					$str .=  '<option value="'.$option[$id_column].'" '.($value == $option[$id_column] ? 'selected' : '').'>'. $option[$name_column] .'</option>';
				}
				$str .= '
					</select>
				</div><br clear="all"/>';
				return $str;
			}
			else{
				return '<div class="text-input-wrap">'. $this->db->_error_message() .'</div>';
			}
		}
		else{
			return '<div class="text-input-wrap">Error! Picklist field not defined</div>';
		}
	}

	function multiselect( $field_id = 0, $name = "", $value = "", $tabindex )
	{
		$str = '<br style="clear:right;"/><div class="multiselect-input-wrap">';
		//get the multiselect details
		$multiselect = $this->db->get_where('field_multiselect', array('field_id' => $field_id));
		if( $this->db->_error_message() == "" )
		{
			if($multiselect->num_rows() > 0)
			{
				$multiselect = $multiselect->row_array();
				$table = $multiselect['table'];
				$id_column = $multiselect['id_column'];
				if( strpos($id_column, '.') ){
					$id_column = explode('.', $id_column);
					$id_column = $id_column[1];	
				}
				$name_column = $multiselect['name_column'];
				$where_cond = $multiselect['where_condition'];
				$type = $multiselect['type'];
				$group_by = $multiselect['optgroup_column'];
				
				if(  $type == "Table" ){
					$this->db->select( $id_column .', '. $name_column );
					if( $table != 'month' && $table != 'day' && $table != 'time_24hr_format' && $table != 'jo_status' ) $this->db->order_by($name_column);
					if(!empty($where_cond)) $this->db->where( $where_cond );
					$options = $this->db->get_where( $table, array('deleted' => 0 ));
				} else if ($type == 'Function') {
					$module = $this->hdicore->get_module($this->module_id);
					
					if (!is_loaded($module->class_name)) {
						$path   = explode('/', $module->class_path);

						unset($path[count($path) - 1]);
						load_class($module->class_name, 'controllers/' . implode('/', $path));
					}

					if (method_exists($module->class_name, $table)) {						
						$options = call_user_func(array($module->class_name, $table));
					} else {						
						$options = $this->{$table}();
					}
				} else{
					if( !empty( $group_by ) ) $table .= " order by {$group_by}";
					$options = $this->db->query( str_replace('{dbprefix}', $this->db->dbprefix, $table) );
				}
				
				if( $this->db->_error_message() == "" )
				{
					if( strpos($name_column, ',') ) $name_column = explode(',', $name_column);

					$values = array();
					$values = explode(',', $value);
					$str .= '<input type="hidden" name="'. $name.'" id="'.$name.'" value="'.$value.'"/>';
					$str .= '<select ' . $tabindex . ' id="multiselect-'. $name.'" name="multiselect-'. $name .'" multiple="multiple" class="multiselect" style="width:400px">';
					$current_optgroup = "";
					foreach($options->result() as $row)
					{
						if( !empty( $group_by ) ){
							if( $current_optgroup != $row->$group_by ){
								if( empty( $current_optgroup ) )
									$prev_optgroup =  $row->$group_by;
								else
									$prev_optgroup = $current_optgroup;
								$current_optgroup = $row->$group_by;
								$str .= '<optgroup label="'. $current_optgroup .'">';
							}
						}
						
						$str .= '<option value="'.$row->$id_column.'" '.( in_array( $row->$id_column, $values ) ? 'selected' : "").'>';
						if( is_array($name_column) )
						{
							$temp = array();
							foreach($name_column as $column)
							{
								$temp[] = $row->$column;
							}
							$str .= implode(' ', $temp);
							unset($temp);
						}
						else{
							$str .= $row->$name_column;
						}

						$str .= '</option>';
						
						if( !empty( $group_by ) ){
							if( $prev_optgroup != $current_optgroup ){
								$str .= '</optgroup>';
							}
						}
					
					}
					$str .= '</select>';
				}
				else{
					$str .= $this->db->_error_message();
				}
			}
			else{
				$str .= '<span class="red">Undefined multiselect.</span>';
			}
		}
		else{
			$str .= $this->db->_error_message();
		}
		$str .= '</div>';
		return $str;
	}

	function multiple_upload( $field_id = 0, $name = "", $value = "" )
	{
		$str = '<div class="text-input-wrap">
			<div id="error-'.$name.'"></div>
			<div id="'.$name.'-upload-container">';
		if( $value != "" )
		{
			$this->db->order_by('upload_id');
			$this->db->where('upload_id IN ('. $value .')');
			$files = $this->db->get('file_upload');
			if( $this->db->_error_message() != "" )
			{
				$str .= $this->db->_error_message();
			}
			else{
				foreach($files->result() as $file)
				{
					$path_info = pathinfo(base_url() . $file->upload_path);
					if( in_array( $path_info['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) )
					{
						$str .= '<div class="nomargin image-wrap">
									<img id="file-'. $name .'-'. $file->upload_id .'" class="enlarge-image" img_target="'.base_url() . $file->upload_path .'" src="'.base_url() . $file->upload_path .'" width="100px">
									<div class="image-delete nomargin multi" field="'.$name.'" upload_id="'. $file->upload_id .'"></div>
								</div>';
					}
					else{
						$str .= '<div class="nomargin image-wrap">
									<a id="file-'. $name .'-'. $file->upload_id .'" href="'.base_url() . $file->upload_path .'" target="_blank"><img src="'. base_url() .$this->userinfo['theme'].'/images/file-icon-md.png"></a>
									<div class="image-delete nomargin multi" field="'.$name.'" upload_id="'. $file->upload_id .'"></div>
								</div>';
					}
				}
			}
		}

		$str .= '
			</div>
			<div class="clear"></div>
			<input id="'.$name.'" name="'.$name.'" type="hidden" value="'. $value .'"/>
			<input id="uploadify-'.$name.'" name="uploadify-'.$name.'" type="file" />
		</div>';
		return $str;
	}	
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>