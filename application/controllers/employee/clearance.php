<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Clearance extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists clearance forms.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a clearance form.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a clearance form.';

		$this->load->vars(array('submodules' => $this->_get_submodules()));
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
		$data['content'] = 'employees/clearance/detailview';

		//other views to load
		$data['views'] = array();

		$this->db->select('CONCAT(user.firstname," ",user.lastname) employee, 
				uc.company, up.position, ud.department, user.user_id, '.$this->module_table.'.status, e.quitclaim_received', 
				false);
		$this->db->where($this->key_field, $this->input->post('record_id'));
		$this->db->where($this->module_table . '.deleted', 0);
		$this->db->join('user user', 'user.user_id = ' . $this->module_table . '.employee_id', 'left');
		$this->db->join('user_company uc', 'user.company_id = uc.company_id', 'left');
		$this->db->join('user_position up', 'user.position_id = up.position_id', 'left');
		$this->db->join('user_company_department ud', 'user.department_id = ud.department_id', 'left');
		$this->db->join('employee e', 'e.employee_id = user.user_id', 'left');

		$data['raw_data'] = $this->db->get($this->module_table)->row_array();

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
			$data['buttons'] = $this->module_link . '/edit-buttons';
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
		if( $this->input->post('record_id')  == '-1' ){

			$this->db->update($this->module_table, array('turn_around_time' => date('Y-m-d')), array($this->key_field => $this->key_field_val));

		}

		//additional module save routine here

	}

	function delete()
	{	
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function after_ajax_save()
    {
    	if($this->input->post('status') === '2'){
    		$this->db->where($this->key_field, $this->key_field_val);
            $record = $this->db->get($this->module_table)->row();

			$this->db->update($this->module_table, array('quitclaim_received' => 1), array($this->key_field => $this->key_field_val));           
	        $this->db->update('employee', array('quitclaim_received' => 1), array('employee_id' => $record->employee_id));

    	}

        parent::after_ajax_save();
       
    }

	private function _get_submodules()
	{			
		$this->load->helper('recruitment');
		$submodules = array();

        // Get children modules and prepare the checklist data.
	    $module_children = $this->hdicore->get_module_child($this->module_id);		
		
        foreach ($module_children as $submodule) {
			$submodules[] = get_checklist_data($submodule);
        }

		return $submodules;		
	}

	function listview()
	{
		$response->msg = "";

		$page = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid
		$sidx = $this->input->post('sidx'); // get index row - i.e. user click to sort
		$sord = $this->input->post('sord'); // get the direction
		$related_module = ( $this->input->post('related_module') ? true : false );

		$view_actions = (isset($_POST['view']) && $_POST['view'] == 'detail') ? false : true ;

		//set columnlist and select qry
		$this->_set_listview_query( '', $view_actions );

		//set Search Qry string
		if($this->input->post('_search') == "true")
			$search = $this->input->post('searchField') == "all" ? $this->_set_search_all_query() : $this->_set_specific_search_query();
		else
			$search = 1;

		if( $this->module == "user" && (!$this->is_admin && !$this->is_superadmin) ) $search .= ' AND '.$this->db->dbprefix.'user.user_id NOT IN (1,2)';


		if (method_exists($this, '_append_to_select')) {
			// Append fields to the SELECT statement via $this->listview_qry
			$this->_append_to_select();
		}

		if (method_exists($this, '_custom_join')) {
			$this->_custom_join();
		}

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		$this->db->where('employee_movement.deleted = 0');
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
		$result = $this->db->get();
		$response->last_query = $this->db->last_query();
		if( $this->db->_error_message() != "" ){
			$response->msg = $this->db->_error_message();
			$response->msg_type = "error";
		}
		else{
			$total_pages = $result->num_rows() > 0 ? ceil($result->num_rows()/$limit) : 0;
			$response->page = $page > $total_pages ? $total_pages : $page;
			$response->total = $total_pages;
			$response->records = $result->num_rows();

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);

			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			$this->db->where('employee_movement.deleted = 0');

			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			
			if (method_exists($this, '_set_filter')) {
				$this->_set_filter();
			}

			if (method_exists($this, '_custom_join')) {
				// Append fields to the SELECT statement via $this->listview_qry
				$this->_custom_join();
			}
			
			if($sidx != ""){
				$this->db->order_by($sidx, $sord);
			}
			else{
				if( is_array($this->default_sort_col) ){
					$sort = implode(', ', $this->default_sort_col);
					$this->db->order_by($sort);
				}
			}
			$start = $limit * $page - $limit;
			$this->db->limit($limit, $start);
			
			$result = $this->db->get();
			//$response->last_query = $this->db->last_query();

			//check what column to add if this is a related module
			if($related_module){
				foreach($this->listview_columns as $column){                                    
					if($column['name'] != "action"){
						$temp = explode('.', $column['name']);
						if(strpos($this->input->post('column'), ',')){
							$column_lists = explode( ',', $this->input->post('column'));
							if( sizeof($temp) > 1 && in_array($temp[1], $column_lists ) ) $column_to_add[] = $column['name'];
						}
						else{
							if( sizeof($temp) > 1  && $temp[1] == $this->input->post('column')) $this->related_module_add_column = $column['name'];
						}
					}
				}
				//in case specified related column not in listview columns, default to 1st column
				if( !isset($this->related_module_add_column) ){
					if(sizeof($column_to_add) > 0)
						$this->related_module_add_column = implode('~', $column_to_add );
					else
						$this->related_module_add_column = $this->listview_columns[0]['name'];
				}
			}

			if( $this->db->_error_message() != "" ){
				$response->msg = $this->db->_error_message();
				$response->msg_type = "error";
			}
			else{
				$response->rows = array();
				if($result->num_rows() > 0){
					$columns_data = $result->field_data();
					$column_type = array();
					foreach($columns_data as $column_data){
						$column_type[$column_data->name] = $column_data->type;
					}
					$this->load->model('uitype_listview');
					$ctr = 0;
					foreach ($result->result_array() as $row){
						$cell = array();
						$cell_ctr = 0;

						foreach($this->listview_columns as $column => $detail){

							if( preg_match('/\./', $detail['name'] ) ) {
								$temp = explode('.', $detail['name']);
								$detail['name'] = $temp[1];
							}
							
							if(sizeof(explode(' AS ', $detail['name'])) > 1 ){
								$as_part = explode(' AS ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}
							else if(sizeof(explode(' as ', $detail['name'])) > 1 ){
								$as_part = explode(' as ', $detail['name']);
								$detail['name'] = strtolower( trim( $as_part[1] ) );
							}

							if( $detail['name'] == 'action'  ){
								if( $view_actions ){
									$cell[$cell_ctr] = ( $related_module ? $this->_default_field_module_link_actions( $row[$this->key_field], $this->input->post('container'), $this->input->post('fmlinkctr'), $row ) : $this->_default_grid_actions( $this->module_link, $this->input->post('container'), $row ) );
									$cell_ctr++;
								}
							}
							elseif( $detail['name'] == 'turn_around_time' ){

								$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
								$date = $this->uitype_listview->fieldValue($this->listview_fields[$cell_ctr]);

								$clearance_date = new DateTime( date('Y-m-d', strtotime($date) ) );
								$current_date = new DateTime(  date('Y-m-d' )  );

								$diff = $clearance_date->diff($current_date);
								$days_left = 30 - $diff->d;

								if( $diff->m >= 1 || $days_left == 0 ){
									$turn_around_time_detail = '<span class="red"><small>Turnaround Time Exceeded</small></span>';
								}
								else{
									if( $days_left == 1 ){
										$turn_around_time_detail = '<span class="blue"><small> '.$days_left.' day left</small></span>';
									}
									else{
										$turn_around_time_detail = '<span class="blue"><small> '.$days_left.' days left</small></span>';
									}
								}

								$cell[$cell_ctr] =  $date . '<br />' . $turn_around_time_detail;
								$cell_ctr++;	

							}
							else{

								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 40) ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 3 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] ) && $this->listview_fields[$cell_ctr]['other_info']['picklist_type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 39 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['type'] ) && $this->listview_fields[$cell_ctr]['other_info']['type'] == 'Query' ) ){
									$cell[$cell_ctr] = "";
									foreach($this->listview_fields[$cell_ctr]['other_info']['picklistvalues'] as $picklist_val)
									{
										if($row[$detail['name']] == $picklist_val['id']) $cell[$cell_ctr] = $picklist_val['value'];
									}
								}
								else if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 39 ) ) && ( isset( $this->listview_fields[$cell_ctr]['other_info']['type'] ) && $this->listview_fields[$cell_ctr]['other_info']['type'] != 'Query' ) ){
									$this->listview_fields[$cell_ctr]['value'] = $row[$detail['name']];
									$cell[$cell_ctr] = $this->uitype_listview->fieldValue( $this->listview_fields[$cell_ctr] );
								}
								else{
									$cell[$cell_ctr] = in_array('I', $this->listview_fields[$cell_ctr]['datatype']) || in_array('F', $this->listview_fields[$cell_ctr]['datatype']) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
								}
								$cell_ctr++;
							}

						}
						$response->rows[$ctr]['id'] = $row[$this->key_field];
						$response->rows[$ctr]['cell'] = $cell;
						$ctr++;
					}
				}
			}
		}
		
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function _set_listview_query( $listview_id = '', $view_actions = true )
	{
		parent::_set_listview_query($listview_id, $view_actions);

		$this->listview_column_names[] = $this->listview_column_names[3];
		$this->listview_column_names[3] = $this->listview_column_names[2];
		$this->listview_column_names[1] = 'Effectivity Date';
		$this->listview_column_names[2] = 'Status';

		$action_column = $this->listview_columns['action'];
		unset($this->listview_columns['action']);
		
		$this->listview_columns[3] = $this->listview_columns[2];
		$this->listview_columns[2] = $this->listview_columns[1];
		$this->listview_columns[1] = array('name' => 'employee_movement.transfer_effectivity_date');
		$this->listview_columns[0] = array('name' => 'employee_name');
		$this->listview_columns['action'] = $action_column;

		$this->listview_fields[] = $this->listview_fields[1];

		$this->listview_fields[3] = array(
			'field_id' => '',
			'uitype_id' => '5',
			'datatype' => array('V'),
		);

		$this->listview_fields[2] = array(
			'field_id' => '1568',
            'uitype_id' => '3',
            'datatype' => array('V'),
            'other_info' => array(
            	'picklist_type' => 'Query',
        		'picklistvalues' => array(array('id'=>'1','value'=>'Pending'),array('id'=>'2','value'=>'Approved'),array('id'=>'3','value'=>'Cancelled'))
            )
		);

		$this->listview_fields[1] = array(
			'field_id' => '',
			'uitype_id' => '5',
			'datatype' => array('V'),
		);

		$this->listview_fields[0] = array(
			'field_id' => '',
			'uitype_id' => '39',
			'datatype' => array('V'),
		);		

		$this->listview_qry .= ',employee_movement.last_day, employee_movement.transfer_effectivity_date ,CONCAT(' . $this->db->dbprefix . 'user.firstname, " ",user.lastname) as employee_name';
	}

	function _set_left_join()
	{
		parent::_set_left_join();

		$this->db->join('employee_movement', 'employee_movement.employee_id = ' . $this->module_table . '.employee_id', 'left');
		$this->db->join('user', 'user.employee_id = ' . $this->module_table . '.employee_id', 'left');
	}

	function get_default_signatories()
	{
		if(IS_AJAX)
		{
			$this->db->select('approver_id');
			$d_signatories = $this->db->get_where('employee_clearance_form_checklist', array('deleted' => 0, 'default' => 1));

			if($d_signatories->num_rows() > 0) {
				$response->status = 'success';
				$response->signatories = $d_signatories->result_array();
			} else 
				$response->status = 'error';

			$this->load->view('template/ajax', array('json' => $response));

		} else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
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
            // $buttons .= "<div class='icon-label'>";
            // $buttons .= "<a class='icon-16-add icon-16-add-listview' related_field='".$related_field."' related_field_value='".$related_field_value."' module_link='".$module_link."' href='javascript:void(0)'>";
            // $buttons .= "<span>".$addtext."</span></a></div>";
        }
        
        /*
        if ($this->user_access[$this->module_id]['delete']) {
            $buttons .= "<div class='icon-label'><a class='icon-16-delete delete-array' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>".$deltext."</span></a></div>";                            
        }
        */

        if ( get_export_options( $this->module_id ) ) {
            $buttons .= "<div class='icon-label'><a class='icon-16-export module-export' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Export</span></a></div>";
            $buttons .= "<div class='icon-label'><a class='icon-16-import module-import' container='".$container."' module_link='".$module_link."' href='javascript:void(0)'><span>Import</span></a></div>";
        }        
        
        $buttons .= "</div>";
                
		return $buttons;
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
        
		if ( $this->user_access[$this->module_id]['edit'] && /*$record['employee_clearancestatus'] != 3*/ $record['email_sent'] != 1) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        /*
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }
        */

        if ($this->user_access[$this->module_id]['cancel'] && $record['employee_clearancestatus'] != 3) {
            $actions .= '<a class="icon-button icon-16-cancel cancel_clearance" tooltip="Cancel Clearance" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	function cancel_clearance(){

		$clearance_info = $this->db->get_where('employee_clearance',array('employee_clearance_id'=>$this->input->post('record_id')))->row();
		$employee_movement_result = $this->db->get_where('employee_movement',array('employee_id'=>$clearance_info->employee_id));

		if( $employee_movement_result->num_rows() > 0 ){

			$employee_movement_info = $employee_movement_result->row();

			if( $employee_movement_info->status == 6 ){

				$this->db->set('resigned', 0);
				$this->db->set('resigned_date', NULL);
				$this->db->set('status_effectivity', NULL);
				$this->db->where('employee_id', $clearance_info->employee_id);
				$this->db->update('employee');

				$lastname = $this->db->get_where('user', array("employee_id" => $clearance_info->employee_id))->row()->lastname;
				$lastname = str_replace(' *', '', $lastname);
				
				$this->db->set('lastname', $lastname);
				$this->db->where('employee_id', $clearance_info->employee_id);
				$this->db->update('user');

				// all credits(regardless what year) will be tagged as uneditable/resigned on leave credits
				$this->db->set('uneditable', 0);
				$this->db->where('employee_id', $clearance_info->employee_id);
				$this->db->update('employee_leave_balance');

				$this->db->set('inactive', 0);
				$this->db->where('employee_id', $clearance_info->employee_id);
				$this->db->update('user');

			}
			
			$this->db->where('employee_id',$clearance_info->employee_id);
			$this->db->update('employee_movement',array('status'=>5));

		}

		$this->db->where('employee_clearance_id',$this->input->post('record_id'));
		$this->db->update('employee_clearance',array( 'status'=> 3 ));

		if( $this->db->_error_message() != "" ){
 			$response->msg = $this->db->_error_message();
 			$response->msg_type = "error";
  		}
  		else{

  			if( $this->db->_error_message() != "" ){
 				$response->msg = $this->db->_error_message();
 				$response->msg_type = "error";
  			}
   			else{

   				$response->msg = "Clearance is successfully cancelled";
 				$response->msg_type = "success";

			} 

		}

		$this->load->view('template/ajax', array('json' => $response));


	}

	function send_email()
	{
		if (IS_AJAX) {
			$record_id = $this->input->post('record_id');
			$this->db->join('user','user.employee_id='.$this->module_table.'.employee_id');
			$records = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row_array();
			
			$vars['employee'] = $records['firstname'] .' '. str_replace('*', ' ', $records['lastname']) ;
			
			$signatories = array();

			$qry = "SELECT *
						FROM hr_employee_clearance_form_checklist
						  JOIN hr_user
						    ON (hr_user.employee_id = hr_employee_clearance_form_checklist.approver_id)
						WHERE employee_clearance_form_checklist_id IN (".$records['signatories'].")";

			$approvers = $this->db->query($qry);

			foreach ($approvers->result_array() as $signatory) {
			
				$signatories['email'][] = $signatory['email'];
				$signatories['signatory'][] = $signatory['salutation'] .' '. $signatory['firstname']. ' ' . $signatory['lastname'];
			}
			
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if ($mail_config) {
				$recepients = array();

				// Load the template.            
				$this->load->model('template');
				$template = $this->template->get_module_template(0, 'clearance_for_approval');
				
				// $this->db->where_in('training_application_id', $record_id);
				// $approver_user = $this->db->get('training_approver');
				
				for ($i=0; $i < count($signatories['email']); $i++) { 
					$recepients = $signatories['email'][$i];
					$vars['approver'] = $signatories['signatory'][$i];

					$message = $this->template->prep_message($template['body'], $vars);
					$this->template->queue($recepients, '', $template['subject'], $message);

				}

                    $this->db->where($this->key_field, $record_id);
					$this->db->update($this->module_table, array('email_sent' => '1'));

					$response->msg_type = 'success';
					$response->msg = 'Email Sent.';

			}

			$data['json'] = $response;			
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	
				
		}else {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function _append_to_select()
	{
		
		$this->listview_qry .= ', email_sent'; 

	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */