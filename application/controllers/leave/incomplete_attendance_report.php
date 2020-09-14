<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Incomplete_attendance_report extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Incomplete Attendance Report';
		$this->listview_description = 'This module lists all defined incomplete attendance report(s).';
		$this->jqgrid_title = "Incomplete Attendance Report List";
		$this->detailview_title = 'Incomplete Attendance Report Info';
		$this->detailview_description = 'This page shows detailed information about a particular incomplete attendance report.';
		$this->editview_title = 'Incomplete Attendance Report Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about incomplete attendance report(s).';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'leaves/incomplete_attendance_report/listview';

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

		$this->db->select('period_year');
		$this->db->group_by('period_year');
		$result = $this->db->get('timekeeping_period');

		$data['period_year'] = $result;		

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

		//set Search Qry string
/*		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;*/

		//$date_from = date('Y-m-d', strtotime($this->input->post('dateStart').' -1 day'));
		//$date_to = date('Y-m-d', strtotime($this->input->post('dateEnd').' +1 day'));
/*		$date_from = date('Y-m-d', strtotime($this->input->post('dateStart')));
		$date_to = date('Y-m-d', strtotime($this->input->post('dateEnd')));*/

		$period_id = $this->input->post('period_date');
		$this->db->where('period_id', $period_id);
		$this->db->where('deleted', 0);
		$p = $this->db->get('timekeeping_period')->row();

/*		$qry = "SELECT * FROM {$this->db->dbprefix}timekeeping_period WHERE cutoff < '{$p->cutoff}' ORDER BY cutoff DESC LIMIT 1";
		$prevcutof = $this->db->query($qry)->row();
		$prevcutof = date('Y-m-d', strtotime('+1 day', strtotime($prevcutof->cutoff)));*/

		$sql = "SELECT
		id_number as 'Employee ID', CONCAT(lastname, ', ',firstname) as 'Employee Name', Department,
		date as 'Date', time_in1 as 'Time In', time_out1 as 'Time Out', Particulars
		FROM (		
			SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
			u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
			'AWOL' as Particulars
			FROM {$this->db->dbprefix}user u 
			INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
			INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
			INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
		 	JOIN {$this->db->dbprefix}employee_dtr r
		    	ON (r.employee_id = s.employee_id AND r.date = s.date)
			WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
			AND (s.absent = 1 AND e.employee_type = 3)

		 	UNION

		 	SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
			u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
			'UNDERTIME' as Particulars
			FROM {$this->db->dbprefix}user u 
			INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
			INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
			INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
		 	JOIN {$this->db->dbprefix}employee_dtr r
		    	ON (r.employee_id = s.employee_id AND r.date = s.date)
			LEFT JOIN {$this->db->dbprefix}employee_out eout
		    	ON (eout.employee_id = u.employee_id
			        AND eout.date BETWEEN '". $p->date_from ."' AND '". $p->date_to ."'
			        AND (DATE(eout.date_approved) <= '" . $p->cutoff . "' OR DATE(eout.date_approved) = '0000-00-00')
			        AND eout.form_status_id = 3) AND eout.date = s.date AND outblanket_id IS NULL
			WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
			AND ( ROUND(s.undertime, 2) > 0 AND eout.date IS NULL AND e.employee_type = 3)

			UNION

			SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
			u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
			'TARDY' as Particulars
			FROM {$this->db->dbprefix}user u 
			INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
			INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
			INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
		 	JOIN {$this->db->dbprefix}employee_dtr r
		    	ON (r.employee_id = s.employee_id AND r.date = s.date)
			LEFT JOIN {$this->db->dbprefix}employee_et et
		    	ON (et.employee_id = u.employee_id
			        AND et.datelate BETWEEN '". $p->date_from ."' AND '". $p->date_to ."'
			        AND (DATE(et.date_approved) <= '" . $p->cutoff . "' OR DATE(et.date_approved) = '0000-00-00')
			        AND et.form_status_id = 3) AND et.datelate = s.date AND etblanket_id IS NULL  AND s.lates > {$this->config->item('deduction_lates')} / 60
			WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
			AND (ROUND(s.lates, 2) > ROUND({$this->config->item('deduction_lates')} / 60, 2) AND et.datelate IS NULL AND e.employee_type = 3)
			
			UNION

			SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
			u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
			'AWOL' as Particulars
			FROM {$this->db->dbprefix}user u 
			INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
			INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
			INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
		 	JOIN {$this->db->dbprefix}employee_dtr r
		    	ON (r.employee_id = s.employee_id AND r.date = s.date)
			LEFT JOIN {$this->db->dbprefix}employee_leaves l
				ON (l.employee_id = u.employee_id
					AND l.application_form_id in (5,6,7) 
					AND (DATE(l.date_approved) <= '" . $p->cutoff . "' OR DATE(l.date_approved) = '0000-00-00')
					AND l.form_status_id = 3) AND (s.date BETWEEN l.date_from AND l.date_to)
			LEFT JOIN {$this->db->dbprefix}employee_leaves_dates l_date
				ON l_date.employee_leave_id = l.employee_leave_id AND (
					l_date.date BETWEEN '".  $p->date_from . "' AND '" . $p->date_to . "'
				) AND s.date = l_date.date
			WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
			AND (s.lwop > 0 AND l.date_from IS NULL)

		) as t_max ";

		$sql .= " WHERE ";

		switch ($this->input->post('category')) {
			case 1:
					if( $this->input->post('company') && $this->input->post('company') != 'null' ) $sql .= 'company_id IN ('.implode(",",$this->input->post('company')).') AND';
				break;
			case 2:
					if( $this->input->post('division') && $this->input->post('division') != 'null' ) $sql .= 'division_id  IN ('.implode(",",$this->input->post('division')).') AND';		
				break;
			case 3:
					if( $this->input->post('department') && $this->input->post('department') != 'null' ) $sql .= 'department_id  IN ('.implode(",",$this->input->post('department')).') AND';		
				break;
			case 4:
					if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $sql .= 'employee_id  IN ('.implode(",",$this->input->post('employee')).') AND';		
				break;								
		}

		if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $sql .= ' status_id IN ('.implode(",",$this->input->post('employment_status')).') AND';
		if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $sql .= ' employee_type IN ('.implode(",",$this->input->post('employee_type')).') AND';

		$sql .= " date >= employed_date AND IF(resigned_date IS NULL, 1, date <= resigned_date) ORDER BY CONCAT(lastname, ', ',firstname)";
		$this->db->order_by('u.lastname');
		$result = $this->db->query($sql);

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

			$sql = "SELECT
			id_number as 'Employee ID', CONCAT(lastname, ', ',firstname) as 'Employee Name', Department,
			date as 'Date', time_in1 as 'Time In', time_out1 as 'Time Out', Particulars
			FROM (		
				SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
				u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
				'AWOL' as Particulars
				FROM {$this->db->dbprefix}user u 
				INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
				INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
				INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
			 	JOIN {$this->db->dbprefix}employee_dtr r
			    	ON (r.employee_id = s.employee_id AND r.date = s.date)
				WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
				AND (s.absent = 1 AND e.employee_type = 3)

			 	UNION

			 	SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
				u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
				'UNDERTIME' as Particulars
				FROM {$this->db->dbprefix}user u 
				INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
				INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
				INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
			 	JOIN {$this->db->dbprefix}employee_dtr r
			    	ON (r.employee_id = s.employee_id AND r.date = s.date)
				LEFT JOIN {$this->db->dbprefix}employee_out eout
			    	ON (eout.employee_id = u.employee_id
				        AND eout.date BETWEEN '". $p->date_from ."' AND '". $p->date_to ."'
				        AND (DATE(eout.date_approved) <= '" . $p->cutoff . "' OR DATE(eout.date_approved) = '0000-00-00')
				        AND eout.form_status_id = 3) AND eout.date = s.date AND outblanket_id IS NULL
				WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
				AND ( ROUND(s.undertime, 2) > 0 AND eout.date IS NULL AND e.employee_type = 3)

				UNION

				SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
				u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
				'TARDY' as Particulars
				FROM {$this->db->dbprefix}user u 
				INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
				INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
				INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
			 	JOIN {$this->db->dbprefix}employee_dtr r
			    	ON (r.employee_id = s.employee_id AND r.date = s.date)
				LEFT JOIN {$this->db->dbprefix}employee_et et
			    	ON (et.employee_id = u.employee_id
				        AND et.datelate BETWEEN '". $p->date_from ."' AND '". $p->date_to ."'
				        AND (DATE(et.date_approved) <= '" . $p->cutoff . "' OR DATE(et.date_approved) = '0000-00-00')
				        AND et.form_status_id = 3) AND et.datelate = s.date AND etblanket_id IS NULL  AND s.lates > {$this->config->item('deduction_lates')} / 60
				WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
				AND (ROUND(s.lates, 2) > ROUND({$this->config->item('deduction_lates')} / 60, 2) AND et.datelate IS NULL AND e.employee_type = 3)
				
				UNION

				SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
				u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
				'AWOL' as Particulars
				FROM {$this->db->dbprefix}user u 
				INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
				INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
				INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
			 	JOIN {$this->db->dbprefix}employee_dtr r
			    	ON (r.employee_id = s.employee_id AND r.date = s.date)
				LEFT JOIN {$this->db->dbprefix}employee_leaves l
					ON (l.employee_id = u.employee_id
						AND l.application_form_id in (5,6,7) 
						AND (DATE(l.date_approved) <= '" . $p->cutoff . "' OR DATE(l.date_approved) = '0000-00-00')
						AND l.form_status_id = 3) AND (s.date BETWEEN l.date_from AND l.date_to)
				LEFT JOIN {$this->db->dbprefix}employee_leaves_dates l_date
					ON l_date.employee_leave_id = l.employee_leave_id AND (
						l_date.date BETWEEN '".  $p->date_from . "' AND '" . $p->date_to . "'
					) AND s.date = l_date.date
				WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
				AND (s.lwop > 0 AND l.date_from IS NULL)

			) as t_max ";

			$sql .= " WHERE ";

			switch ($this->input->post('category')) {
				case 1:
						if( $this->input->post('company') && $this->input->post('company') != 'null' ) $sql .= 'company_id IN ('.implode(",",$this->input->post('company')).') AND';
					break;
				case 2:
						if( $this->input->post('division') && $this->input->post('division') != 'null' ) $sql .= 'division_id  IN ('.implode(",",$this->input->post('division')).') AND';		
					break;
				case 3:
						if( $this->input->post('department') && $this->input->post('department') != 'null' ) $sql .= 'department_id  IN ('.implode(",",$this->input->post('department')).') AND';		
					break;
				case 4:
						if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $sql .= 'employee_id  IN ('.implode(",",$this->input->post('employee')).') AND';		
					break;								
			}

			if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $sql .= ' status_id IN ('.implode(",",$this->input->post('employment_status')).') AND';
			if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $sql .= ' employee_type IN ('.implode(",",$this->input->post('employee_type')).') AND';

			$sql .= " date >= employed_date AND IF(resigned_date IS NULL, 1, date <= resigned_date)";

	        if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');
	            $sql .= ' ORDER BY `' . $sidx . '` ' . $sord;
	        } else {
	        	$sql .= ' ORDER BY CONCAT(lastname, \', \',firstname), date';
	        }
	        
	        $start = $limit * $page - $limit;
	        $sql .= ' LIMIT '.$start.','.$limit.'';
	        //$this->db->order_by('u.lastname');
	        $result = $this->db->query($sql);
	        //$response->qry = $this->db->last_query();
	        $ctr = 0;
	        foreach ($result->result() as $row) {
	        	$particulars = $row->{"Particulars"};
	        	if (CLIENT_DIR == "hdi"){
		        	if (($row->{"Time In"} == '' && $row->{"Time Out"} != '') || ($row->{"Time In"} != '' && $row->{"Time Out"} == '')){
		        		$particulars = "HALF DAY AWOL";
		        	}
	        	}
	            $response->rows[$ctr]['cell'][0] = $row->{"Employee ID"};
	            $response->rows[$ctr]['cell'][1] = $row->{"Employee Name"};
	            $response->rows[$ctr]['cell'][2] = $row->{"Department"};
	            $response->rows[$ctr]['cell'][3] = $row->{"Date"};
	            $response->rows[$ctr]['cell'][4] = $row->{"Time In"};
	            $response->rows[$ctr]['cell'][5] = $row->{"Time Out"};
	            $response->rows[$ctr]['cell'][6] = $particulars;
	            $ctr++;
	        }
	    }
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', array('json' => $response));
	}

    function _set_listview_query($listview_id = '', $view_actions = true) {
        $this->listview_column_names = array('Employee Id', 'Employee Name', 'Department', 'Date', 'Time In', 'Time Out','Remarks');
        $this->listview_columns[] = array(
            'name' => 'employee id',
            'index' => 'employee id',
            'width' => '100'
        );
        $this->listview_columns[] = array(
            'name' => 'employee name',
            'index' => 'employee name',
            'align' => 'left',
            'width' => '100'
        );
        $this->listview_columns[] = array(
            'name' => 'department',
            'index' => 'department',
            'align' => 'left',
            'width' => '140'
        );
        $this->listview_columns[] = array(
            'name' => 'date',
            'index' => 'date',
            'width' => '100'
        );        

        $this->listview_columns[] = array(
            'name' => 'time in',
            'index' => 'time in',
            'width' => '100'
        );

        $this->listview_columns[] = array(
            'name' => 'time out',
            'index' => 'time out',
            'width' => '100'
        );  
        $this->listview_columns[] = array(
            'name' => 'remarks',
            'index' => 'remarks',
            'width' => '100',
            'sortable' => 'false'
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

	function export() {	
		$this->_excel_export();
	}

	private function _excel_export($record_id = 0)
	{
		
		//$date_from = date('Y-m-d', strtotime($this->input->post('date_period_start').' -1 day'));
		//$date_to = date('Y-m-d', strtotime($this->input->post('date_period_end').' +1 day'));
/*		$date_from = date('Y-m-d', strtotime($this->input->post('date_period_start')));
		$date_to = date('Y-m-d', strtotime($this->input->post('date_period_end')));*/
		$period_id = $this->input->post('period_date');
		$this->db->where('period_id', $period_id);
		$this->db->where('deleted', 0);
		$p = $this->db->get('timekeeping_period')->row();
		
		$sql = "
		SELECT
		id_number as 'Employee ID', CONCAT(lastname, ', ',firstname) as 'Employee Name', Department,
		date as 'Date', time_in1 as 'Time In', time_out1 as 'Time Out', Particulars
		FROM (		
			SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
			u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
			'AWOL' as Particulars
			FROM {$this->db->dbprefix}user u 
			INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
			INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
			INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
		 	JOIN {$this->db->dbprefix}employee_dtr r
		    	ON (r.employee_id = s.employee_id AND r.date = s.date)
			WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
			AND (s.absent = 1 AND e.employee_type = 3)

		 	UNION

		 	SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
			u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
			'UNDERTIME' as Particulars
			FROM {$this->db->dbprefix}user u 
			INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
			INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
			INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
		 	JOIN {$this->db->dbprefix}employee_dtr r
		    	ON (r.employee_id = s.employee_id AND r.date = s.date)
			LEFT JOIN {$this->db->dbprefix}employee_out eout
		    	ON (eout.employee_id = u.employee_id
			        AND eout.date BETWEEN '". $p->date_from ."' AND '". $p->date_to ."'
			        AND (DATE(eout.date_approved) <= '" . $p->cutoff . "' OR DATE(eout.date_approved) = '0000-00-00')
			        AND eout.form_status_id = 3) AND eout.date = s.date AND outblanket_id IS NULL
			WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
			AND ( ROUND(s.undertime, 2) > 0 AND eout.date IS NULL AND e.employee_type = 3)

			UNION

			SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
			u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
			'TARDY' as Particulars
			FROM {$this->db->dbprefix}user u 
			INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
			INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
			INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
		 	JOIN {$this->db->dbprefix}employee_dtr r
		    	ON (r.employee_id = s.employee_id AND r.date = s.date)
			LEFT JOIN {$this->db->dbprefix}employee_et et
		    	ON (et.employee_id = u.employee_id
			        AND et.datelate BETWEEN '". $p->date_from ."' AND '". $p->date_to ."'
			        AND (DATE(et.date_approved) <= '" . $p->cutoff . "' OR DATE(et.date_approved) = '0000-00-00')
			        AND et.form_status_id = 3) AND et.datelate = s.date AND etblanket_id IS NULL  AND s.lates > {$this->config->item('deduction_lates')} / 60
			WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
			AND (ROUND(s.lates, 2) > ROUND({$this->config->item('deduction_lates')} / 60, 2) AND et.datelate IS NULL AND e.employee_type = 3)
			
			UNION

			SELECT e.id_number, u.lastname, u.firstname, d.department, s.date, time(r.time_in1) as time_in1, time(r.time_out1) as time_out1,
			u.deleted, u.company_id, u.division_id, u.department_id, u.employee_id, e.employed_date, e.resigned_date,e.status_id,e.employee_type,
			'AWOL' as Particulars
			FROM {$this->db->dbprefix}user u 
			INNER JOIN {$this->db->dbprefix}dtr_daily_summary s ON (s.employee_id = u.employee_id)
			INNER JOIN {$this->db->dbprefix}user_company_department d ON (u.department_id = d.department_id)
			INNER JOIN {$this->db->dbprefix}employee e ON (u.employee_id = e.employee_id)
		 	JOIN {$this->db->dbprefix}employee_dtr r
		    	ON (r.employee_id = s.employee_id AND r.date = s.date)
			LEFT JOIN {$this->db->dbprefix}employee_leaves l
				ON (l.employee_id = u.employee_id
					AND l.application_form_id in (5,6,7) 
					AND (DATE(l.date_approved) <= '" . $p->cutoff . "' OR DATE(l.date_approved) = '0000-00-00')
					AND l.form_status_id = 3) AND (s.date BETWEEN l.date_from AND l.date_to)
			LEFT JOIN {$this->db->dbprefix}employee_leaves_dates l_date
				ON l_date.employee_leave_id = l.employee_leave_id AND (
					l_date.date BETWEEN '".  $p->date_from . "' AND '" . $p->date_to . "'
				) AND s.date = l_date.date
			WHERE (s.date BETWEEN '". $p->date_from . "' AND '" . $p->date_to . "' AND u.deleted = 0)
			AND (s.lwop > 0 AND l.date_from IS NULL)

		) as t_max ";

		$sql .= " WHERE ";

		switch ($this->input->post('category')) {
			case 1:
					if( $this->input->post('company') && $this->input->post('company') != 'null' ) $sql .= 'company_id IN ('.implode(",",$this->input->post('company')).') AND';
				break;
			case 2:
					if( $this->input->post('division') && $this->input->post('division') != 'null' ) $sql .= 'division_id  IN ('.implode(",",$this->input->post('division')).') AND';		
				break;
			case 3:
					if( $this->input->post('department') && $this->input->post('department') != 'null' ) $sql .= 'department_id  IN ('.implode(",",$this->input->post('department')).') AND';		
				break;
			case 4:
					if( $this->input->post('employee') && $this->input->post('employee') != 'null' ) $sql .= 'employee_id  IN ('.implode(",",$this->input->post('employee')).') AND';		
				break;								
		}

		if( $this->input->post('employment_status') && $this->input->post('employment_status') != 'null' ) $sql .= ' status_id IN ('.implode(",",$this->input->post('employment_status')).') AND';
		if( $this->input->post('employee_type') && $this->input->post('employee_type') != 'null' ) $sql .= ' employee_type IN ('.implode(",",$this->input->post('employee_type')).') AND';

		$sql .= " date >= employed_date AND IF(resigned_date IS NULL, 1, date <= resigned_date) order by CONCAT(lastname, ', ',firstname), date";
        //$sql .= " e.employee_type > 2 AND s.date >= e.employed_date AND IF(e.resigned_date IS NULL, 1, s.date <= e.resigned_date)";

		//$this->db->order_by('u.lastname');
        $result = $this->db->query($sql);

		$query  = $result;
		$fields = $result->list_fields();

		//$export = $this->_export;

		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle("Incomplete Attendance Report")
		            ->setDescription("Incomplete Attendance Report");
		               
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
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);	
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

		foreach ($fields as $field) {
			$xcoor = $alphabet[$alpha_ctr];

			$activeSheet->setCellValueExplicit($xcoor . '6', $field, PHPExcel_Cell_DataType::TYPE_STRING);

			$objPHPExcel->getActiveSheet()->getStyle($xcoor . '6')->applyFromArray($styleArray);
			
			$alpha_ctr++;
		}

		for($ctr=1; $ctr<6; $ctr++){
			$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);
		}

		//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		$activeSheet->setCellValueExplicit('A2', 'Incomplete Attendance Report', PHPExcel_Cell_DataType::TYPE_STRING);

		if( $this->input->post('date_period_start') && $this->input->post('date_period_end') ){
			$activeSheet->setCellValueExplicit('A3', date('F d,Y',strtotime($this->input->post('date_period_start'))).' - '.date('F d,Y',strtotime($this->input->post('date_period_end'))), PHPExcel_Cell_DataType::TYPE_STRING); 
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);

		// contents.
		$line = 7;

		foreach ($query->result() as $row) {
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

		        if ($field == "date"){
		        	if(($row->{$field} != "" && $row->{$field} != "0000-00-00" ))
		        		$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, date($this->config->item('display_date_format'),strtotime($row->{$field})), PHPExcel_Cell_DataType::TYPE_STRING); 
		        }
		        elseif ($field == "Particulars") {
		        	$particulars = $row->{$field};
		        	if (CLIENT_DIR == "hdi"){
			        	if (($row->{"Time In"} == '' && $row->{"Time Out"} != '') || ($row->{"Time In"} != '' && $row->{"Time Out"} == '')){
			        		$particulars = "HALF DAY AWOL";
			        	}
		        	}
		        	$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $particulars, PHPExcel_Cell_DataType::TYPE_STRING); 
		        }
		        else{
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($xcoor . $line, $row->{$field}, PHPExcel_Cell_DataType::TYPE_STRING); 
				}

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
		header('Content-Disposition: attachment;filename='.date('Y-m-d').'-Incomplete_Attendance_Report.xls');
		header('Content-Transfer-Encoding: binary');
		
		$objWriter->save('php://output');		
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
				$company = $this->db->get_where('user_company',array("deleted"=>0))->result_array();		
                $html .= '<select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2:
				$division = $this->db->get_where('user_company_division',array("deleted"=>0))->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 3:
				$department = $this->db->get_where('user_company_department',array("deleted"=>0))->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;		        
		    case 4:
		    	$this->db->where('employee_id <>',0);
				$employee = $this->db->get_where('user',array("deleted"=>0))->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                $html .= '</select>';	
		        break;		        		        
		}	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function get_period_date(){
		$html = '';
		$this->db->where('period_year',$this->input->post('period_year'));
		$this->db->where('deleted',0);
		$result = $this->db->get('timekeeping_period')->result_array();
        foreach($result as $data){
            $html .= '<option value="'.$data["period_id"].'">'.date($this->config->item('display_date_format'),strtotime($data["date_from"])).' to '.date($this->config->item('display_date_format'),strtotime($data["date_to"])).'</option>';
        }

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}		
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>