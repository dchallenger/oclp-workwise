<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Leave_balance extends MY_Controller
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

		if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";
        }

        $approver= $this->system->is_module_approver( $this->module_id, $this->userinfo['position_id'] );
        if( $approver){
            if( $this->input->post('filter') && $this->input->post('filter') == "for_approval" ){
                $subs = array(0);
                foreach( $approver as $row ){
                    $subordinates = $this->system->get_supervised( $this->user->user_id, $row->position_id  );
                    foreach( $subordinates as $subordinate ){
                        $subs[] = $subordinate['user_id'];
                    }   
                }
                $this->filter = $this->db->dbprefix.$this->module_table.".employee_id IN (". implode(',', $subs) .") AND ".$this->db->dbprefix.$this->module_table.'.form_status_id != 1';
            }
        }

        if ($this->input->post('filter') && $this->input->post('filter') == 'subordinates') {
        	$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
			$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
        	foreach ($subordinates as $sub) {
        		$subs[] = $sub['user_id'];
        	}

        	$this->filter = $this->db->dbprefix.$this->module_table.".employee_id IN (". implode(',', $subs) .")";
        }

        if( $this->input->post('filter') && $this->input->post('filter') == "personal" ){
            $this->filter = $this->module_table.".employee_id = {$this->user->user_id}";    
        }
        
        if (CLIENT_DIR == 'firstbalfour') {
        
	        if( $this->input->post('filter') && $this->input->post('filter') == "project_subordinates" ){

        		if(  !$this->is_superadmin || !$this->is_admin  && $this->user_access[$this->module_id]['project_hr'] == 1 ){
				$subordinate_id = array();
				$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
				$subordinates = $this->system->get_subordinates_by_project($emp->employee_id);
				$subordinate_id = array(0);
				if( count($subordinates) > 0 ){

					$subordinate_id = array();

					foreach ($subordinates as $subordinate) {
							$subordinate_id[] = $subordinate['employee_id'];
					}
				}		

				$subordinate_list = implode(',', $subordinate_id);
				if( $subordinate_list != "" )
					$this->filter = $this->module_table.'.employee_id IN ('.$subordinate_list.')';
				}	

			}
		}
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
		$data['jqgrid'] = $this->module_link . '/jqgrid';

		//Tabs for Listview
        $tabs = array();
        $emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
		$subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
        // $subordinates = $this->hdicore->get_subordinates($this->userinfo['position_id']);

        if( ( $this->is_superadmin || $this->is_admin ) ||  $this->user_access[$this->module_id]['post'] == 1 ){
            $data['filter'] = 'all';
            $tabs[] = '<li class="active" filter="all"><a href="javascript:void(0)">All</li>';
            $tabs[] = '<li filter="personal"><a href="javascript:void(0)">Personal</li>';
        } else if (count($subordinates) > 0) {
        	$data['filter'] = 'subordinates';
        	$tabs[] = '<li class="active" filter="personal"><a href="javascript:void(0)">Personal</li>';
        	$tabs[] = '<li filter="subordinates"><a href="javascript:void(0)">Subordinates</li>';
        } else{
            $data['filter'] = 'personal';
            $tabs[] = '<li class="active" filter="personal"><a href="javascript:void(0)">Personal</li>';
        }
        $approver  = $this->system->is_module_approver( $this->module_id, $this->userinfo['position_id'] );
        if( $approver ){
            $subs = array();
            foreach( $approver as $row ){
                $subordinates = $this->system->get_supervised( $this->user->user_id, $row->position_id  );
                foreach( $subordinates as $subordinate ){
                    $subs[] = $subordinate['user_id'];
                }   
            }
            if( sizeof($subs) > 0 ){
                $tabs[] = '<li filter="for_approval"><a href="javascript:void(0)">For Approval</li>';
            }
        }
        if( sizeof( $tabs ) > 1 ) $data['tab'] = addslashes('<ul id="grid-filter">'. implode('', $tabs) .'</ul>');
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
		//set default columnlist
		$this->_set_listview_query();
		
		//set grid buttons
		$data['jqg_buttons'] = $this->_default_grid_buttons();
		
		//set load jqgrid loadComplete callback
		 $data['jqgrid_loadComplete'] = 'init_filter_tabs();';
		
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
		switch($this->config->item('show_with_carried'))
		{
			case 1:
				$data['content'] = $this->module_link . '/detailview_open';	
				break;
			default:
				$data['content'] = $this->module_link . '/detailview';
				break;
		}

		//other views to load
		$data['views'] = array();

		$this->db->where($this->key_field, $this->input->post('record_id'));
		$this->db->where('deleted', 0);

		$data['balance'] = $this->db->get($this->module_table)->row();

		$tagged = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')))->row();
		if($tagged->uneditable == 1)
			$data['buttons'] = '/template/goback-detail-buttons';

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

	function mybalance(){
		$year = date('Y');
		$balance = $this->db->get_where( $this->module_table, array('year' => $year, 'employee_id' => $this->user->user_id, 'deleted' => 0) );
		if( $balance->num_rows() == 1 ){
			$balance = $balance->row();
			$_POST['record_id'] = $balance->leave_balance_id;
		}
		else{
			$this->session->set_flashdata( 'flashdata', 'No available leave balance for this year yet, please call HR.' );
			redirect( base_url() );
		}

		parent::detail();

		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'detailview';
		$data['buttons'] = 'template/detail-no-buttons';

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
		if( !IS_AJAX ) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		$employee_ids = $this->input->post('employee_id');
		$employee_ids = explode(',', $employee_ids);
		$year = str_replace(",", "", $this->input->post('year'));
		foreach( $employee_ids as $employee_id ){
			$_POST['employee_id'] = $employee_id;
			if( sizeof( $employee_ids ) > 0 ){
				//get the proper record id
				$_POST['record_id'] = "-1";
				$rec = $this->db->get_where($this->module_table, array('year' => $year, 'employee_id' => $employee_id, 'deleted' => 0));
				if( $rec->num_rows() == 1 ){
					$rec = $rec->row();
					$key_field = $this->key_field;
					$_POST['record_id'] = $rec->$key_field;

					$this->db->where('leave_balance_id',$rec->$key_field);
					$this->db->update('employee_leave_balance',array('date_modified' => date('Y-m-d H:i:s'),'manual_edited' => $this->user->user_id));
				}
			}
			parent::ajax_save();
		}
		$this->my_after_ajax_save();
		//additional module save routine here
	}

	function my_after_ajax_save() {
		$this->load->vars(array('json' => $this->get_message()));
		$this->load->view($this->userinfo['rtheme'].'/template/ajax');		
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}

	function after_ajax_save(){

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
		$search_string[] = 'year LIKE "%' . $value . '%"';
		if ($value != '' && is_numeric($value)){
			$search_string[] = '(vl-vl_used) = ' . $value . '';
			$search_string[] = '(sl-sl_used) = ' . $value . '';
			$search_string[] = '(el-el_used) = ' . $value . '';
			$search_string[] = '(mpl-mpl_used) = ' . $value . '';
		}
		$search_string = '('. implode(' OR ', $search_string) .')';		
		return $search_string;
	}

	function _set_specific_search_query()
	{
		$field = $this->input->post('searchField');
		switch ($field) {
			case 'employee_leave_balance.employee_id':
				$field = 'user.firstname';
				break;
			case 'employee_leave_balance.year':
				$field = 'year';
				break;
			case 'employee_leave_balance.vl':
				$field = '(vl-vl_used)';
				break;
			case 'employee_leave_balance.sl':
				$field = '(sl-sl_used)';
				break;
			case 'employee_leave_balance.el':
				$field = '(el-el_used)';
				break;
			case 'employee_leave_balance.mpl':
				$field = '(mpl-mpl_used)';
				break;																			
		}
		$operator =  $this->input->post('searchOper');
		$value =  $this->input->post('searchString');

		foreach( $this->search_columns as $search )
		{
			if($search['jq_index'] == $field) $field = $search['column'];
		}

		$field = strtolower( $field );
		if(sizeof(explode(' as ', $field)) > 1){
			$as_part = explode(' as ', $field);
			$field = strtolower( trim( $as_part[0] ) );
		}

		switch ($operator) {
			case 'eq':
				return $field . ' = "'.$value.'"';
				break;
			case 'ne':
				return $field . ' != "'.$value.'"';
				break;
			case 'lt':
				return $field . ' < "'.$value.'"';
				break;
			case 'le':
				return $field . ' <= "'.$value.'"';
				break;
			case 'gt':
				return $field . ' > "'.$value.'"';
				break;
			case 'ge':
				return $field . ' >= "'.$value.'"';
				break;
			case 'bw':
				return $field . ' REGEXP "^'. $value .'"';
				break;
			case 'bn':
				return $field . ' NOT REGEXP "^'. $value .'"';
				break;
			case 'in':
				return $field . ' IN ('. $value .')';
				break;
			case 'ni':
				return $field . ' NOT IN ('. $value .')';
				break;
			case 'ew':
				return $field . ' LIKE "%'. $value  .'"';
				break;
			case 'en':
				return $field . ' NOT LIKE "%'. $value  .'"';
				break;
			case 'cn':
				return $field . ' LIKE "%'. $value .'%"';
				break;
			case 'nc':
				return $field . ' NOT LIKE "%'. $value .'%"';
				break;
			default:
				return $field . ' LIKE %'. $value .'%';
		}
	}

	function _set_left_join() {
		parent::_set_left_join();

		$this->db->join('user', 'user.user_id = ' . $this->module_table . '.employee_id', 'left');
	}

	function _set_listview_query( $listview_id = '', $view_actions = true ) {
		parent::_set_listview_query($listview_id, $view_actions);

		$emp = $this->hdicore->_get_userinfo($this->user->user_id);
		if(!( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['post'] != 1){
			if ($emp->sex == "male"){
				$cname = "PL Balance";
			}
			else{
				$cname = "ML Balance";			
			}			
		}
		else{
			$cname = "PL/ML Balance";
			if (CLIENT_DIR == 'firstbalfour'){
				$cname = "PL Balance";
			}
		}
		array_unshift($this->listview_column_names, "Department");
		if($this->config->item('remove_el_leave_balance_viewing') == 1)
		{
			$this->listview_column_names[3] = "VL Balance";
			$this->listview_column_names[4] = "SL Balance";
			if (CLIENT_DIR == 'firstbalfour'){
				$this->listview_column_names[5] = "BL Balance";			
				$this->listview_column_names[6] = $cname;				
			}	
			else{
				$this->listview_column_names[5] = $cname;
			}		
		} else {
			$this->listview_column_names[3] = "VL Balance";
			$this->listview_column_names[4] = "SL Balance";
			if (CLIENT_DIR == 'firstbalfour'){
				$this->listview_column_names[5] = "BL Balance";		
				$this->listview_column_names[6] = "EL Balance";
				$this->listview_column_names[7] = $cname;				
			}			
			else{
				$this->listview_column_names[5] = "EL Balance";
				$this->listview_column_names[6] = $cname;			
			}
		}

		$this->listview_qry .= ',user.firstname, user.lastname';		
	}	

	function listview()
	{
		$this->load->helper('time_upload');		
        $page = $this->input->post('page');
        $limit = $this->input->post('rows'); // get how many rows we want to have into the grid
        $sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
        $sord = $this->input->post('sord'); // get the direction        

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;
		
		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';

		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}		
		$this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id');
    	$this->db->from($this->module_table);   

		$total_records =  $this->db->count_all_results();
		//$response->last_query = $this->db->last_query();
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $total_records > 0 ? ceil($total_records/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $total_records;                        

	        $response->msg = "";

	        if ($this->input->post('sidx')) {
	            $sidx = $this->input->post('sidx');
	            $sord = $this->input->post('sord');
	            $this->db->order_by($sidx . ' ' . $sord);
	        }
	        else{
	        	$this->db->order_by('year desc');
	        }

	        $start = $limit * $page - $limit;
	        $this->db->limit($limit, $start);        
	        $this->db->order_by('user_company_department.department', 'asc');
	        $this->db->order_by('user.lastname', 'asc');
	        $this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			if(!empty( $this->filter ) ) $this->db->where( $this->filter );

			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}	        
	        $this->db->join($this->db->dbprefix('user'),$this->db->dbprefix('user').'.employee_id = '.$this->db->dbprefix($this->module_table).'.employee_id');
	        $this->db->join('user_company_department', 'user.department_id = user_company_department.department_id', 'left');
	        $this->db->select('user.*');
	        $this->db->select($this->module_table.'.*');
	        $this->db->select('user_company_department.department');
	        $result = $this->db->get($this->module_table); 
	        $response->qry = $this->db->last_query();
	        $ctr = 0;
	        foreach ($result->result() as $row) {        	
	        	if($this->config->item('remove_el_leave_balance_viewing') == 1)
	        	{
	        		$response->rows[$ctr]['id'] = $row->leave_balance_id;
		            $response->rows[$ctr]['cell'][0] = $row->department;
		            $response->rows[$ctr]['cell'][1] = $row->lastname.', '.$row->firstname;
		            $response->rows[$ctr]['cell'][2] = $row->year;
		            $response->rows[$ctr]['cell'][3] = ($this->config->item('show_with_carried') == 0 ? $row->vl - $row->vl_used : ($row->vl + $row->carried_vl) - $row->vl_used);
		            $response->rows[$ctr]['cell'][4] = ($this->config->item('show_with_carried') == 0 ? $row->sl - $row->sl_used : ($row->sl + $row->carried_sl) - $row->sl_used);
		            $response->rows[$ctr]['cell'][5] = $row->mpl - $row->mpl_used;
		            $response->rows[$ctr]['cell'][6] = $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row);
		        } else {
		        	$response->rows[$ctr]['id'] = $row->leave_balance_id;
		           	$response->rows[$ctr]['cell'][0] = $row->department;
		           	$response->rows[$ctr]['cell'][1] = $row->lastname.', '.$row->firstname;
		            $response->rows[$ctr]['cell'][2] = $row->year;
		            $response->rows[$ctr]['cell'][3] = ($this->config->item('show_with_carried') == 0 ? $row->vl - $row->vl_used : ($row->vl + $row->carried_vl) - $row->vl_used);
		            $response->rows[$ctr]['cell'][4] = ($this->config->item('show_with_carried') == 0 ? $row->sl - $row->sl_used : ($row->sl + $row->carried_sl) - $row->sl_used);
		            $response->rows[$ctr]['cell'][5] = $row->el - $row->el_used;
		            $response->rows[$ctr]['cell'][6] = $row->mpl - $row->mpl_used;
		            $response->rows[$ctr]['cell'][7] = $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row);
		        }
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

        if(!$record->uneditable)
        {
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
	            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
	        }
	    } else
	    	$actions .= '<i>Resigned</i>';

        $actions .= '</span>';

		return $actions;
	}	
	// END - default module functions

	// START custom module funtions
	function _set_filter() {
		$this->db->where($this->module_table.'.employee_id <>', 1);
		if (CLIENT_DIR == 'pioneer'){
			if (date('m') != 12){
				$this->db->where($this->module_table.'.year <=', date('Y'));
			}
		}
	}

	function reset_pl_female(){
		$result = $this->db->query('UPDATE '.$this->db->dbprefix('employee_leave_balance').' elb INNER JOIN '.$this->db->dbprefix('user').' u ON (elb.employee_id = u.employee_id) SET mpl = 0 WHERE 1 AND u.sex = "female"');	
		redirect("dtr/leave_balance");
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */