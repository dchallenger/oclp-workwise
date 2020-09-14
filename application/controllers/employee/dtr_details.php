<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'third_party/tcpdf/config/tcpdf_config.php';
include_once APPPATH.'third_party/tcpdf/tcpdf.php';

class MyTCPDF extends TCPDF {
    // var $htmlHeader;

    // public function setHtmlHeader($htmlHeader) {
    //     $this->htmlHeader = $htmlHeader;
    // }

    public function Footer() {
        // Position at XX mm from bottom
        $this->SetY(-10);
        // Set font
        $this->SetFont('helvetica', '', 10);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');     
    }     
}

class dtr_details extends MY_Controller
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
		$data['content'] = 'employee/dtr_details/listview';
		$data['jqgrid'] = 'employee/dtr_details/jqgrid';

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

		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.employee_id'.',restday,CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as employee_name', false);
		$this->db->select(''.$this->db->dbprefix. 'user_company_department.department'.'');
		$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.','.$this->db->dbprefix('employee_dtr'). '.hours_worked'.','.$this->db->dbprefix('employee_dtr'). '.excused_tardiness'.','.$this->db->dbprefix('employee_dtr').'.lates_display,'.$this->db->dbprefix('employee_dtr'). '.approved_undertime'.',IF('.$this->db->dbprefix('employee_dtr'). '.approved_undertime'.' > 0 , 0, '.$this->db->dbprefix('employee_dtr'). '.undertime'.') as undertime,'.$this->db->dbprefix('employee_dtr').'.overtime'.','.$this->db->dbprefix('employee_dtr').'.ot_nd'.','.$this->db->dbprefix('employee_dtr').'.awol'.'', false);
			// $this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.','.$this->db->dbprefix('employee_dtr'). '.hours_worked'.','.$this->db->dbprefix('employee_dtr'). '.excused_tardiness'.',IF('.$this->db->dbprefix('employee_dtr'). '.excused_tardiness > 0, 0, '.','.$this->db->dbprefix('employee_dtr'). '.lates_display'.') as lates_display,'.$this->db->dbprefix('employee_dtr'). '.approved_undertime'.',IF('.$this->db->dbprefix('employee_dtr'). '.approved_undertime'.' > 0 , 0, '.$this->db->dbprefix('employee_dtr'). '.undertime'.') as undertime,'.$this->db->dbprefix('employee_dtr'). '.overtime'.'', false);
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",1)," ",-1) as date_in,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as time_in',false);
		$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",1)," ",-1) as date_out,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as time_out',false);
		$this->db->from('employee_dtr');
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id');
		$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
		$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");
		$this->db->join($this->db->dbprefix('employee_type'),$this->db->dbprefix('employee_type').'.employee_type_id = '.$this->db->dbprefix('employee').'.employee_type',"left");
		$this->db->join($this->db->dbprefix('employment_status'),$this->db->dbprefix('employment_status').'.employment_status_id = '.$this->db->dbprefix('employee').'.status_id',"left");

		$this->db->where('employee_dtr.deleted = 0 AND '.$search);	
		$this->db->where('IF(resigned_date IS NULL, 1, `date` <= resigned_date)' );
		if ($subordinates == 0){
			$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
		}

        switch ($this->input->post('category')) {
            case 1:
                    if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.company_id ', $this->input->post('company'));
                break;
            case 2:
                    if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.division_id ', $this->input->post('division'));       
                break;
            case 3:
                    if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.department_id ', $this->input->post('department'));
                break;
            case 4:
                    if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       
                break;      
            case 5:
                    if( $this->input->post('section') && $this->input->post('section') != 'null' ) {
                    	$this->db->where_in($this->db->dbprefix('user').'.section_id ', $this->input->post('section'));
                    	// $this->db->where($this->db->dbprefix('employee_work_assignment').'.assignment ',1); //commented since hr_employee_work_assignment is not being used
                    }       
                break;                                                                            
        }
        if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));       

        if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.employee_type ', $this->input->post('employee_type'));       
        if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $this->db->where_in($this->db->dbprefix('employee').'.status_id ', $this->input->post('employment_status'));       

		switch ($this->input->post('category1')) {
			case 2:
					$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
				break;
			case 3:
					$this->db->where($this->db->dbprefix('employee_dtr').'.awol !=',"0");
					$this->db->where('('.$this->db->dbprefix('employee_dtr').'.hours_worked <= 4 AND '.$this->db->dbprefix('employee_dtr').'.overtime = 0 AND ('.$this->db->dbprefix('employee_dtr').'.lates + '.$this->db->dbprefix('employee_dtr').'.overtime) <= 4)', '', false);
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
			$this->db->select('o.date, o.ot_in, o.ot_out');
			$this->db->select('IF(holiday IS NOT NULL, CONCAT("HOLIDAY ",holiday, " "), hr_timekeeping_shift.shift) AS shift', false);
			$this->db->select(''.$this->db->dbprefix('employee_dtr'). '.date'.','.$this->db->dbprefix('employee_dtr'). '.hours_worked'.','.$this->db->dbprefix('employee_dtr'). '.excused_tardiness'.','.$this->db->dbprefix('employee_dtr').'.lates_display,'.$this->db->dbprefix('employee_dtr'). '.approved_undertime'.',IF('.$this->db->dbprefix('employee_dtr'). '.approved_undertime'.' > 0 , 0, '.$this->db->dbprefix('employee_dtr'). '.undertime'.') as undertime,'.$this->db->dbprefix('employee_dtr').'.overtime'.','.$this->db->dbprefix('employee_dtr').'.ot_nd'.','.$this->db->dbprefix('employee_dtr').'.awol'.'', false);
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",1)," ",-1) as date_in,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_in1'.'," ",2)," ",-1) as time_in',false);
			$this->db->select('SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",1)," ",-1) as date_out,SUBSTRING_INDEX(SUBSTRING_INDEX('.$this->db->dbprefix('employee_dtr').'.time_out1'.'," ",2)," ",-1) as time_out',false);
			$this->db->from('employee_dtr');
			$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id');
			$this->db->join($this->db->dbprefix('user_company_department'),$this->db->dbprefix('user').'.department_id = '.$this->db->dbprefix('user_company_department').'.department_id',"left");
			$this->db->join($this->db->dbprefix('employee'),$this->db->dbprefix('employee').'.employee_id = '.$this->db->dbprefix('user').'.employee_id',"left");
			$this->db->join($this->db->dbprefix('employee_type'),$this->db->dbprefix('employee_type').'.employee_type_id = '.$this->db->dbprefix('employee').'.employee_type',"left");
			$this->db->join($this->db->dbprefix('employment_status'),$this->db->dbprefix('employment_status').'.employment_status_id = '.$this->db->dbprefix('employee').'.status_id',"left");			
			$this->db->join('(SELECT employee_id, date, MIN(datetime_from) AS "ot_in", MAX(datetime_to) AS "ot_out" FROM hr_employee_oot GROUP BY employee_id, date) o', 'o.date = '.$this->db->dbprefix('employee_dtr').'.date AND o.employee_id = '.$this->db->dbprefix('employee_dtr').'.employee_id', 'left');
			$this->db->join($this->db->dbprefix('timekeeping_shift'),$this->db->dbprefix('timekeeping_shift').'.shift_id = '.$this->db->dbprefix('employee_dtr').'.shift_id','left');
			$this->db->join($this->db->dbprefix('holiday'),$this->db->dbprefix('employee_dtr').'.date = '.$this->db->dbprefix('holiday').'.date_set','left');
			$this->db->where('employee_dtr.deleted = 0 AND '.$search);
			$this->db->where('IF(resigned_date IS NULL, 1, '.$this->db->dbprefix('employee_dtr').'.`date` <= resigned_date)' );

			if ($subordinates == 0){
				$this->db->where($this->db->dbprefix('employee_dtr').'.employee_id ', $this->userinfo['user_id']);
			}

            switch ($this->input->post('category')) {
                case 1:
                        if( $this->input->post('company') && $this->input->post('company') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.company_id ', $this->input->post('company'));
                    break;
                case 2:
                        if( $this->input->post('division') && $this->input->post('division') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.division_id ', $this->input->post('division'));       
                    break;
                case 3:
                        if( $this->input->post('department') && $this->input->post('department') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.department_id ', $this->input->post('department'));
                    break;
                case 4:
                        if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));
                    break;   
	            case 5:
	                    if( $this->input->post('section') && $this->input->post('section') != 'null' ) $this->db->where_in($this->db->dbprefix('user').'.section_id ', $this->input->post('section'));
	                break;
            }

        	if($this->input->post('employee') && $this->input->post('employee') != 'null' )
        		$this->db->where_in($this->db->dbprefix('user').'.employee_id ', $this->input->post('employee'));

			if($this->input->post('employee_type') && $this->input->post('employee_type') != 'null' )
				$this->db->where_in($this->db->dbprefix('employee').'.employee_type ', $this->input->post('employee_type'));

	        if($this->input->post('employment_status') && $this->input->post('employment_status') != 'null' )
	        	$this->db->where_in($this->db->dbprefix('employee').'.status_id ', $this->input->post('employment_status'));

			switch ($this->input->post('category1')) {
				case 2:
						$this->db->where($this->db->dbprefix('employee_dtr').'.hours_worked >',"0");
					break;
				case 3:
						$this->db->where($this->db->dbprefix('employee_dtr').'.awol !=',"0");
						$this->db->where('('.$this->db->dbprefix('employee_dtr').'.hours_worked <= 4 AND '.$this->db->dbprefix('employee_dtr').'.overtime = 0 AND ('.$this->db->dbprefix('employee_dtr').'.lates + '.$this->db->dbprefix('employee_dtr').'.overtime) <= 4)', '', false);
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

				$dtr_status_in = "";
				$dtr_status_out = "";

				if ($leave && $leave->num_rows() > 0 && $leave->row()->duration_id == 1){
					$dtr_status_in = "Leave";
					$dtr_status_out = "Leave";
				}

				$obt = get_form($row->employee_id, 'obt', $dummy_p, $row->date, false);
				if ($obt->num_rows() > 0) {
					$obts = $obt->result();
					foreach($obts as $obt)
					{
						if ($row->time_in == '0000-00-00 00:00:00' || $row->time_in == '' 
							|| is_null($row->time_in)
							|| strtotime($obt->time_start) < strtotime(date('H:i:s', strtotime($row->time_in)))
							) {
							$dtr_status_in = date('h:i:s a', strtotime($cdate . ' ' . $obt->time_start));
						}

						if ($row->time_out == '0000-00-00 00:00:00' || $row->time_out == '' 
							|| is_null($row->time_out) 
							|| strtotime($obt->time_end) > strtotime(date('H:i:s', strtotime($row->time_out)))
							) { 
							$dtr_status_out = date('h:i:s a', strtotime($cdate . ' ' .$obt->time_end));
						}
					}
				}	

				$dtrp = get_form($row->employee_id, 'dtrp', $dummy_p, $row->date, false);
				if ($dtrp->num_rows() > 0) {
					foreach ($dtrp->result() as $_dtrp) {
						if( $_dtrp->form_status_id == 3 ){
							if ($_dtrp->time_set_id == 1) {
								if ($row->time_in == '0000-00-00 00:00:00' || $row->time_in == '' || is_null($row->time_in)){
									$dtr_status_in = date('h:i:s a', strtotime($_dtrp->time));
								}
							} else {
								if ($row->time_out == '0000-00-00 00:00:00' || $row->time_out == '' || is_null($row->time_out)){
									$dtr_status_out1 = date('h:i:s a', strtotime($_dtrp->time));
								}

							}
						}
					}
				}

/*				if($row->awol > 0 && $dtr_status_in == "" && $dtr_status_out == ""){
					$dtr_status_in = "Absent";
					$dtr_status_out = "Absent";
	            }*/

	            $schedule = $this->system->get_employee_worksched($row->employee_id, $row->date, true);
				$shift_id = $schedule->shift_id;
				$shift = $schedule->shift;

				$cws = get_form($row->employee_id, 'cws', $dummy_p, $row->date, true);

				if ($cws->num_rows() > 0) {
					$cws = $cws->row();
					$shift_id = $schedule->shift_id;
					$shift = $schedule->shift;
				}

			$a_h = array();

			$holiday_exclude = $this->system->holiday_check($cdate, $employee_id, true);

			if ($holiday) {
				foreach ($holiday as $h) {
					$a_h[] = $h['holiday'];
				}

				if ($shift == 'Rest Day') {
					$shift = '<strong>HOLIDAY / REST DAY</strong>';
					$rd = true;
				} else {
					$shift = '<strong>HOLIDAY</strong>';
				}

				$shift .= '<br />' . implode(', ', $a_h);

				if(!$holiday_exclude && !$rd){

					if( $this->hdicore->is_flexi($employee_id) && $this->config->item('with_flexi') ) {
						$shift .= '<br /><i>Flexible</i>';
					}
				}
			}

	            $response->rows[$ctr]['cell'][0] = $row->employee_name;
	            $response->rows[$ctr]['cell'][1] = date($this->config->item('display_date_format'),strtotime($row->date));
	            $response->rows[$ctr]['cell'][2] = $shift;

	            
	            if($dtr_status_in != "" && $dtr_status_out != ""){
	            	$response->rows[$ctr]['cell'][3] = $dtr_status_in;
	            	$response->rows[$ctr]['cell'][4] = $dtr_status_out;
	            }
	            else{
	            	$dtr_status_in = ($row->time_in != '' ? date('h:i:s a', strtotime($row->time_in)): '');
	            	$dtr_status_out = ($row->time_out != '' ? date('h:i:s a', strtotime($row->time_out)): '');

/*	            	if(strtotime(date('H:i:s', strtotime($row->time_out))) < strtotime(date('H:i:s', strtotime($row->time_in)))){
	            		if($dtr_status_out1 != ""){
	            			$dtr_status_in = $dtr_status_in;
	            			$dtr_status_out = $dtr_status_out1;
	            		}
	            		else{
	            			$dtr_status_in = date('m-d-y', strtotime($row->date)).'<br />'.$dtr_status_in;
	            			$dtr_status_out = date('m-d-y', strtotime($row->date.' + 1 days')).'<br />'.$dtr_status_out;	
	            		}
	            	}*/

		            $response->rows[$ctr]['cell'][3] = $dtr_status_in;
		            $response->rows[$ctr]['cell'][4] = $dtr_status_out;
	            }

	            $ndstart = strtotime(date('Y-m-d 22:00:00', $row->date));
	            $tomorrow = date('Y-m-d', strtotime('+1 day', $row->date));
				$ndend   = strtotime(date('Y-m-d 06:00:00', strtotime($tomorrow)));

				$row_ot_8 = ((($row->overtime / 60) - 8) > 0 ? number_format(($row->overtime / 60) - 8,2) : '0.00');
				$row_ot_nd = $row->ot_nd;

				if ($shift_id == 1 && $row->time_out != '' && $row->time_in != '') {
					$row_time_out = $row->time_out;
					$time_out_hrs = date('H', strtotime($row->time_out));
					$time_out_mins = date('i', strtotime($row->time_out));

					if($time_out_mins >= 0 && $time_out_mins < 15) $row_time_out = strtotime(date('Y-m-d '.$time_out_hrs.':00:00', $row->date));
					else if($time_out_mins >= 15 && $time_out_mins < 30) $row_time_out = strtotime(date('Y-m-d '.$time_out_hrs.':15:00', $row->date));
					else if($time_out_mins >= 30 && $time_out_mins < 45) $row_time_out = strtotime(date('Y-m-d '.$time_out_hrs.':30:00', $row->date));
					else if($time_out_mins >= 45 && $time_out_mins < 60) $row_time_out = strtotime(date('Y-m-d '.$time_out_hrs.':45:00', $row->date));

					$row_ot_nd = ($row_time_out - $ndstart) / 60 / 60;
					$row_ot_nd = $row_ot_nd > 0 ? $row_ot_nd : 0;
				}

	            $response->rows[$ctr]['cell'][5] = $row->hours_worked;
	            $response->rows[$ctr]['cell'][6] = ($row->ot_in != '' ? date('h:i:s a', strtotime($row->ot_in)): '');
	            $response->rows[$ctr]['cell'][7] = ($row->ot_out != '' ? date('h:i:s a', strtotime($row->ot_out)): '');
	            $response->rows[$ctr]['cell'][8] = number_format($row->excused_tardiness / 60,2);
	            $response->rows[$ctr]['cell'][9] = number_format($row->lates_display / 60,2);
	            $response->rows[$ctr]['cell'][10] = number_format($row->approved_undertime / 60,2);
	            $response->rows[$ctr]['cell'][11] = number_format($row->undertime / 60,2);
	            $response->rows[$ctr]['cell'][12] = (($row->overtime / 60) > 8 ? '8.00' : number_format($row->overtime / 60,2));
	            $response->rows[$ctr]['cell'][13] = number_format($row_ot_8,2);
	            $response->rows[$ctr]['cell'][14] = number_format($row_ot_nd,2);
	            $response->rows[$ctr]['cell'][15] = number_format($row->awol,2);
	            $ctr++;
	        }
	    }

        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
		$this->listview_column_names = array('Employee<br />Name',
											'Date',
											'Work<br />Shift',
											'IN',
											'OUT',
											'Hours<br />Worked',
											'OT<br />In',
											'OT<br />Out',
											'ET<br />(Hours)',
											'Lates<br />(Hours)',
											'AUT<br />(Hours)',
											'UT<br />(Hours)',
											'OT<br />(Hours)',
											'OT>8<br />(Hours)',
											'ND<br />(Hours)',
											'Absent');

		$this->listview_columns = array(
				array('name' => 'employee_name', 'width' => '180','align' => 'center'),	
				array('name' => 'date'),
				array('name' => 'shift'),
				array('name' => 'time_in1'),
				array('name' => 'time_out1'),
				array('name' => 'hours_worked'),
				array('name' => 'ot_in'),
				array('name' => 'ot_out'),
				array('name' => 'excused_tardiness'),
				array('name' => 'lates_display'),
				array('name' => 'approved_undertime'),
				array('name' => 'undertime'),
				array('name' => 'overtime'),
				array('name' => 'ot_8'),
				array('name' => 'ot_nd'),
				array('name' => 'awol')
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
				$section = $this->db->get('user_section')->result_array();		
                $html .= '<select id="section" multiple="multiple" class="multi-select" style="width:400px;" name="section[]">';
                    foreach($section as $section_record){
                        $html .= '<option value="'.$section_record["section_id"].'">'.$section_record["section"].'</option>';
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
				    case 1: // section
				    	$where = 'company_id IN ('.$this->input->post('category_id').')';
				        break;
				    case 2: // division
						$where = 'division_id IN ('.$this->input->post('category_id').')';
				        break;
				    case 3: // department
				    	$where = 'department_id IN ('.$this->input->post('category_id').')';
				        break;	
				    case 5: // department
				    	$where = 'section_id IN ('.$this->input->post('category_id').')';
				        break;		        
				}	
				$this->db->where($where);
				$this->db->where('user.deleted', 0);
				$employee = $this->db->get('user')->result_array();		

                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                    	$html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
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

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0){
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

		if ($subordinates == 0){
			$employee = " hr_user.employee_id = ".$this->userinfo['user_id']." ";
		}
		else{
			$employee = " hr_user.employee_id  IN (".implode(',',$this->input->post('employee')).") ";
		}

        $this->load->library('pdf');
        $this->pdf = new MyTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$dummy_p->date_to = $this->input->post('dateStart');
		$dummy_p->date_from = $this->input->post('dateEnd');

		$category = "1 ";
        switch ($this->input->post('category')) {
			case 1:
					if( $this->input->post('company') && $this->input->post('company') != 'null' ) {
						$category = "hr_user.company_id IN (".implode(',',$this->input->post('company')).") ";
                    }       
				break;
			case 2:
					if( $this->input->post('division') && $this->input->post('division') != 'null' ) {
						$category = "hr_user.division_id IN (".implode(',',$this->input->post('division')).") ";
                    }       
				break;
			case 3:
					if( $this->input->post('department') && $this->input->post('department') != 'null' ) {
						$category = "hr_user.department_id IN (".implode(',',$this->input->post('department')).") ";
                    }       
				break;
			case 4:
					if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) {
						$category = "hr_user.employee_id IN (".implode(',',$this->input->post('employee')).") ";
                    }       
				break;	
            case 5:
                    if( $this->input->post('section') && $this->input->post('section') != 'null' ) {
						$category = "hr_user.section_id IN (".implode(',',$this->input->post('section')).") ";
                    }       
                break;   															
		}

		$category1 = "1 ";

		switch ($this->input->post('category1')) {
			case 2:
					$category1 = "hr_employee_dtr.hours_worked > 0 ";
				break;
			case 3:
					$category1 = "hr_employee_dtr.awol != 0 AND (hr_employee_dtr.hours_worked <= 4 AND hr_employee_dtr.overtime = 0 AND (hr_employee_dtr.lates + hr_employee_dtr.overtime) <= 4) ";
				break;				
			case 4:
					$category1 = "hr_employee_dtr.lates_display > 0 ";
				break;	
			case 5:
					$category1 = "hr_employee_dtr.undertime > 0 ";
				break;
			case 6:
					$category1 = "hr_employee_dtr.overtime > 0 ";
				break;
			case 8:
					$category1 = "hr_employee_dtr.excused_tardiness > 0 ";
				break;																
		}

		$employee_query = $this->db->query("SELECT DISTINCT 
							hr_employee_dtr.employee_id,
							hr_employee.id_number,
							CONCAT(hr_user.firstname, ' ', hr_user.lastname) AS employee_name,
							hr_user_company.company
						FROM hr_employee_dtr
						JOIN hr_user
							ON hr_user.employee_id = hr_employee_dtr.employee_id
						LEFT JOIN hr_user_company_department
							ON hr_user.department_id = hr_user_company_department.department_id
						LEFT JOIN hr_employee
							ON hr_employee.employee_id = hr_user.employee_id
						LEFT JOIN hr_employment_status
							ON hr_employee.status_id = hr_employment_status.employment_status_id
						LEFT JOIN hr_user_company
							ON hr_user_company.company_id = hr_user.company_id
						LEFT JOIN hr_employee_type 
							ON hr_employee.employee_type = hr_employee_type.employee_type_id
						LEFT JOIN (SELECT employee_id, DATE, MIN(datetime_from) AS 'ot_in', MAX(datetime_to) AS 'ot_out' FROM hr_employee_oot GROUP BY employee_id, DATE) o 
							ON o.date = hr_employee_dtr.date AND o.employee_id = hr_employee_dtr.employee_id
						LEFT JOIN hr_timekeeping_shift 
							ON hr_timekeeping_shift.shift_id = hr_employee_dtr.shift_id
						LEFT JOIN hr_holiday 
							ON hr_employee_dtr.date = hr_holiday.date_set
						WHERE hr_employee_dtr.deleted = 0
							AND ".$category."
							AND IF(resigned_date IS NULL, 1, hr_employee_dtr.date <= resigned_date)
							AND $employee 
							AND (hr_employee_dtr.date BETWEEN '".date('Y-m-d',strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d',strtotime($this->input->post('date_period_end')))."' )
							ORDER BY hr_user.firstname ASC, hr_employee_dtr.date ASC");
		
		if($employee_query && $employee_query->num_rows() > 0){
			foreach($employee_query->result() as $key=>$emp_dtl){
		        $this->pdf->SetMargins(10, 5, 10, true);
		        $this->pdf->SetAutoPageBreak(auto,15);
		        $this->pdf->addPage('L', 'A4', true);
		        $this->pdf->SetFontSize(8);

				$mdate = getdate(date("U"));
		        $mdate = "$mdate[weekday], $mdate[month] $mdate[mday], $mdate[year]";

				$xcel_hed = '';
				$xcel_hed .= '<table style="width:100%;">
								<tr>
		                        	<td style=" width:50%  ; text-align:left   ; font-size:7  ; ">Run Date: '.date("F d, Y H:i A").'</td>
		                            <td style=" width:50%  ; text-align:right  ; font-size:7  ; "></td>
		                        </tr>
		                        <tr>
		                            <td width="100%" style="text-align:center;">'.$emp_dtl->company.'</td>
		                        </tr>
		                        <tr>
		                            <td width="100%" style="text-align:center;">Timesheet Details</td>
		                        </tr>
		                        <tr>
		                            <td width="100%" style="text-align:center;">From '.date('F d, Y',strtotime($this->input->post('date_period_start'))).' to '.date('F d, Y',strtotime($this->input->post('date_period_end'))).'</td>
		                        </tr>
		                        <tr> 
		                            <td width="100%" style="border-bottom:1px solid black;"></td>
		                        </tr>
		                        <tr> 
		                            <td width="100%"></td>
		                        </tr>
		                        <tr> 
		                            <td width="100%  ; text-align:left   ; font-size:8  ; ">Employee Number: '.$emp_dtl->id_number.'</td>
		                        </tr>
		                        <tr> 
		                            <td width="100%  ; text-align:left   ; font-size:8  ; ">Employee Name: '.$emp_dtl->employee_name.'</td>
		                        </tr>
		                        <tr> 
		                            <td width="100%" style="border-bottom:1px solid black;"></td>
		                        </tr>
		                        <tr> 
		                            <td width="100%"></td>
		                        </tr>
		                        <tr> 
		                            <td width="10%" style="text-align:center;">Date</td>
		                            <td width="10%" style="text-align:center;">Workshift</td>
		                            <td width="5%" style="text-align:center;">Time<br />In</td>
		                            <td width="5%" style="text-align:center;">Time<br />Out</td>
		                            <td width="5%" style="text-align:center;">Hours<br />Work</td>
		                            <td width="5%" style="text-align:center;">Overtime<br />In</td>
		                            <td width="5%" style="text-align:center;">Overtime<br />Out</td>
		                            <td width="5%" style="text-align:center;">Excused<br />Tardy</td>
		                            <td width="5%" style="text-align:center;">Lates</td>
		                            <td width="10%" style="text-align:center;">Authorized<br />Undertime</td>
		                            <td width="10%" style="text-align:center;">Undertime</td>
		                            <td width="5%" style="text-align:center;">Overtime</td>
		                            <td width="5%" style="text-align:center;">Overtime<br />>8</td>
		                            <td width="10%" style="text-align:center;">Night<br />Differential</td>
		                            <td width="5%" style="text-align:center;">Absent</td>
		                        </tr>
		                        <tr> 
		                            <td width="100%" style="border-bottom:1px solid black;"></td>
		                        </tr>
		                        <tr> 
		                            <td width="100%"></td>
		                        </tr>';

				$qry = "SELECT hr_employee.employee_id,
							hr_employee.id_number,
							CONCAT(hr_user.firstname, ' ', hr_user.lastname) AS employee_name,
							hr_employment_status.employment_status,
							hr_employee_type.employee_type,
							hr_employee_dtr.date, 
							IF(holiday IS NOT NULL, CONCAT('HOLIDAY ',holiday, ' '), hr_timekeeping_shift.shift) AS shift,
							SUBSTRING_INDEX(SUBSTRING_INDEX(hr_employee_dtr.time_in1, ' ', 2), ' ', -1) AS time_in,
							SUBSTRING_INDEX(SUBSTRING_INDEX(hr_employee_dtr.time_out1, ' ', 2), ' ', -1) AS time_out, 
							hr_employee_dtr.hours_worked, 
							SUBSTRING_INDEX(SUBSTRING_INDEX(ot_in, ' ', 2), ' ', -1) AS overtime_in, 
							SUBSTRING_INDEX(SUBSTRING_INDEX(ot_out, ' ', 2), ' ', -1) AS overtime_out, 
							hr_employee_dtr.excused_tardiness/60 AS excused_tardiness, 
							hr_employee_dtr.lates_display/60 AS lates_display, 
							hr_employee_dtr.approved_undertime/60 AS approved_undertime, 
							IF(hr_employee_dtr.approved_undertime > 0, '0.00', hr_employee_dtr.undertime/60) AS undertime, 
							IF((hr_employee_dtr.overtime/60) > '8.00', 8, (hr_employee_dtr.overtime/60)) AS overtime, 
							IF(((hr_employee_dtr.overtime - 480))/60 > '0.00', (hr_employee_dtr.overtime - 480)/60, 0) AS overtime_8, 
							hr_employee_dtr.ot_nd,
							hr_employee_dtr.awol
						FROM hr_employee_dtr
						JOIN hr_user
							ON hr_user.employee_id = hr_employee_dtr.employee_id
						LEFT JOIN hr_user_company_department
							ON hr_user.department_id = hr_user_company_department.department_id
						LEFT JOIN hr_employee
							ON hr_employee.employee_id = hr_user.employee_id
						LEFT JOIN hr_employment_status
							ON hr_employee.status_id = hr_employment_status.employment_status_id
						LEFT JOIN hr_employee_type 
							ON hr_employee.employee_type = hr_employee_type.employee_type_id
						LEFT JOIN (SELECT employee_id, DATE, MIN(datetime_from) AS 'ot_in', MAX(datetime_to) AS 'ot_out' FROM hr_employee_oot GROUP BY employee_id, DATE) o 
							ON o.date = hr_employee_dtr.date AND o.employee_id = hr_employee_dtr.employee_id
						LEFT JOIN hr_timekeeping_shift 
							ON hr_timekeeping_shift.shift_id = hr_employee_dtr.shift_id
						LEFT JOIN hr_holiday 
							ON hr_employee_dtr.date = hr_holiday.date_set
						WHERE hr_employee_dtr.deleted = 0
							AND IF(resigned_date IS NULL, 1, hr_employee_dtr.date <= resigned_date)
						AND hr_user.employee_id  = ".$emp_dtl->employee_id."
						AND ".$category1."
						AND (hr_employee_dtr.date BETWEEN '".date('Y-m-d',strtotime($this->input->post('date_period_start')))."' AND '".date('Y-m-d',strtotime($this->input->post('date_period_end')))."' )
						ORDER BY hr_user.firstname ASC, hr_employee_dtr.date ASC";

		        $tms_qry = $this->db->query($qry);
// dbug($this->db->last_query());
		        if($tms_qry->num_rows() > 0){
		        	foreach($tms_qry->result() as $key=>$tms_dtl){

		        		$dtr_status_in = ($tms_dtl->time_in != '') ? date('h:i a', strtotime($tms_dtl->time_in)) : '';
		        			
		        		$dtr_status_out = ($tms_dtl->time_out != '') ? date('h:i a', strtotime($tms_dtl->time_out)) : '';
		        			

		        		$overtime_in = ($tms_dtl->overtime_in != '') ? date($this->config->item('display_time_format'), strtotime($tms_dtl->overtime_in)) : '';
		        		$overtime_out = ($tms_dtl->overtime_out != '') ? date($this->config->item('display_time_format'), strtotime($tms_dtl->overtime_out)) : '';

				$holiday = $this->system->holiday_check($tms_dtl->date, $tms_dtl->employee_id);

	            $schedule = $this->system->get_employee_worksched($tms_dtl->employee_id, $tms_dtl->date, true);
				$shift_id = $schedule->shift_id;
				$shift = $schedule->shift;

				$cws = get_form($tms_dtl->employee_id, 'cws', $dummy_p, $tms_dtl->date, true);

				if ($cws->num_rows() > 0) {
					$cws = $cws->row();
					$shift_id = $schedule->shift_id;
					$shift = $schedule->shift;
				}

			$a_h = array();

			$holiday_exclude = $this->system->holiday_check($cdate, $employee_id, true);

			if ($holiday) {
				foreach ($holiday as $h) {
					$a_h[] = $h['holiday'];
				}

				if ($shift == 'Rest Day') {
					$shift = '<strong>HOLIDAY / REST DAY</strong>';
					$rd = true;
				} else {
					$shift = '<strong>HOLIDAY</strong>';
				}

				$shift .= '<br />' . implode(', ', $a_h);

				if(!$holiday_exclude && !$rd){

					if( $this->hdicore->is_flexi($employee_id) && $this->config->item('with_flexi') ) {
						$shift .= '<br /><i>Flexible</i>';
					}
				}
			}


		                $xcel_hed .= '<tr> 
		                            <td width="10%" style="text-align:center;">'.$tms_dtl->date.'</td>
		                            <td width="10%" style="text-align:center;">'.$shift.'</td>';

		                $leave_qry = $this->db->query("SELECT * FROM hr_employee_leaves WHERE DATE(date_from) <= DATE('".$tms_dtl->date."') AND DATE(date_to) >= DATE('".$tms_dtl->date."') AND employee_id = ".$tms_dtl->employee_id." AND form_status_id = 3");
						
						$is_application = false;

						$obt = get_form($tms_dtl->employee_id, 'obt', $dummy_p, $tms_dtl->date, false);
						if ($obt && $obt->num_rows() > 0) {
							$obts = $obt->result();
							foreach($obts as $obt)
							{
								if ($tms_dtl->time_in == '0000-00-00 00:00:00' || $tms_dtl->time_in == '' 
									|| is_null($tms_dtl->time_in)
									|| strtotime($obt->time_start) < strtotime(date('H:i:s', strtotime($tms_dtl->time_in)))
									) {
									$dtr_status_in = date('h:i a', strtotime($cdate . ' ' . $obt->time_start));
									$is_application = true;
								}

								if ($tms_dtl->time_out == '0000-00-00 00:00:00' || $tms_dtl->time_out == '' 
									|| is_null($tms_dtl->time_out) 
									|| strtotime($obt->time_end) < strtotime(date('H:i:s', strtotime($tms_dtl->time_out)))
									) { 
									$dtr_status_out = date('h:i a', strtotime($cdate . ' ' .$obt->time_end));
									$is_application = true;
								}
							}
						}	

						$dtrp = get_form($tms_dtl->employee_id, 'dtrp', $dummy_p, $tms_dtl->date, false);
						if ($dtrp && $dtrp->num_rows() > 0) {
							foreach ($dtrp->result() as $_dtrp) {
								if( $_dtrp->form_status_id == 3 ){
									if ($_dtrp->time_set_id == 1) {
										if ($tms_dtl->time_in == '0000-00-00 00:00:00' || $tms_dtl->time_in == '' || is_null($tms_dtl->time_in)){
											$dtr_status_in = date('h:i a', strtotime($_dtrp->time));
											$is_application = true;
										}
									} else {
										if ($tms_dtl->time_out == '0000-00-00 00:00:00' || $tms_dtl->time_out == '' || is_null($tms_dtl->time_out)){
											$dtr_status_out = date('h:i a', strtotime($_dtrp->time));
											$is_application = true;
										}
									}
								}
							}
						}	

		                if($leave_qry && $leave_qry->num_rows() > 0 && $leave_qry->row()->duration_id == 1){
		                	$xcel_hed .= '<td width="10%" style="text-align:center;">Leave</td>';
		                }
		                else{
		                	if($is_application){
				                $xcel_hed .= '<td width="5%" style="text-align:right;">'.$dtr_status_in.'</td>
				                            <td width="5%" style="text-align:right;">'.$dtr_status_out.'</td>';

		                	}
							else if($tms_dtl->awol > 0 && $dtr_status_in == "" && $dtr_status_out == ""){
			                	$xcel_hed .= '<td width="10%" style="text-align:center;">Absent</td>';
				            }
		                	else{

				                if(strtotime(date('H:i:s', strtotime($tms_dtl->time_out))) > strtotime(date('H:i:s', strtotime($tms_dtl->time_in)))){
				                	$xcel_hed .= '<td width="5%" style="text-align:right;">'.$dtr_status_in.'</td>
				                					<td width="5%" style="text-align:right;">'.$dtr_status_out.'</td>';
				                }
				                else if($dtr_status_in != "" && $dtr_status_out != ""){
				                	$xcel_hed .= '<td width="5%" style="text-align:right;">'.date('m-d-y', strtotime($tms_dtl->date)).'<br />'.$dtr_status_in.'</td>
				                					<td width="5%" style="text-align:right;">'.date('m-d-y', strtotime($tms_dtl->date.' + 1 days')).'<br />'.$dtr_status_out.'</td>';
				                }
				                else{
				                	$xcel_hed .= '<td width="5%" style="text-align:right;"></td>
				                					<td width="5%" style="text-align:right;"></td>';
				                }
				                
		                	}
		                }


		                $xcel_hed .= '<td width="5%" style="text-align:center;">'.number_format($tms_dtl->hours_worked, 2, '.', ',' ).'</td>';

		                if($tms_dtl->awol > 0 && $dtr_status_in == "" && $dtr_status_out == ""){
		                	$xcel_hed .= '<td width="5%" style="text-align:right;"></td>
		                    	        <td width="5%" style="text-align:right;"></td>';
		                }
		                else{
		                	$xcel_hed .= '<td width="5%" style="text-align:right;">'.$overtime_in.'</td>
		                    	        <td width="5%" style="text-align:right;">'.$overtime_out.'</td>';
		                }

	            $ndstart = strtotime(date('Y-m-d 22:00:00', $tms_dtl->date));
	            $tomorrow = date('Y-m-d', strtotime('+1 day', $tms_dtl->date));
				$ndend   = strtotime(date('Y-m-d 06:00:00', strtotime($tomorrow)));

				$row_ot_8 = $tms_dtl->overtime_8;
				$row_ot_nd = $tms_dtl->ot_nd;

				if ($shift_id == 1 && $tms_dtl->time_out != '' && $tms_dtl->time_in != '') {
					$row_time_out = $tms_dtl->time_out;
					$time_out_hrs = date('H', strtotime($tms_dtl->time_out));
					$time_out_mins = date('i', strtotime($tms_dtl->time_out));

					if($time_out_mins >= 0 && $time_out_mins < 15) $row_time_out = strtotime(date('Y-m-d '.$time_out_hrs.':00:00', $tms_dtl->date));
					else if($time_out_mins >= 15 && $time_out_mins < 30) $row_time_out = strtotime(date('Y-m-d '.$time_out_hrs.':15:00', $tms_dtl->date));
					else if($time_out_mins >= 30 && $time_out_mins < 45) $row_time_out = strtotime(date('Y-m-d '.$time_out_hrs.':30:00', $tms_dtl->date));
					else if($time_out_mins >= 45 && $time_out_mins < 60) $row_time_out = strtotime(date('Y-m-d '.$time_out_hrs.':45:00', $tms_dtl->date));

					$row_ot_nd = ($row_time_out - $ndstart) / 60 / 60;
					$row_ot_nd = $row_ot_nd > 0 ? $row_ot_nd : 0;
				}

		                $xcel_hed .= '<td width="5%" style="text-align:center;">'.number_format($tms_dtl->excused_tardiness, 2, '.', ',' ).'</td>
		                            <td width="5%" style="text-align:center;">'.number_format($tms_dtl->lates_display, 2, '.', ',' ).'</td>
		                            <td width="10%" style="text-align:center;">'.number_format($tms_dtl->approved_undertime, 2, '.', ',' ).'</td>
		                            <td width="10%" style="text-align:center;">'.number_format($tms_dtl->undertime, 2, '.', ',' ).'</td>
		                            <td width="5%" style="text-align:center;">'.number_format($tms_dtl->overtime, 2, '.', ',' ).'</td>
		                            <td width="5%" style="text-align:center;">'.number_format($row_ot_8, 2, '.', ',' ).'</td>
		                            <td width="10%" style="text-align:center;">'.number_format($row_ot_nd, 2, '.', ',' ).'</td>
		                            <td width="5%" style="text-align:center;">'.number_format($tms_dtl->awol, 2, '.', ',' ).'</td>
		                        </tr>';

		        	}
		        }

		        $xcel_hed .= '</table>';
		        $this->pdf->writeHTML($xcel_hed, true, false, true, false, '');
			}
		}
		else{
		    $this->pdf->writeHTML("No records found!", true, false, true, false, '');
		}
        $this->pdf->Output(ucwords(str_replace(" ","_","Timesheet_Details"))."_" . date('dmYHis') . '.pdf', 'D');
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>