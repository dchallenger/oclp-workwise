<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nte extends MY_Controller
{
	function __construct(){
		parent::__construct();
		
		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format
		
		$this->listview_title = 'Notice To Explain';
		$this->listview_description = 'This module lists all defined NTE(s).';
		$this->jqgrid_title = "NTE List";
		$this->detailview_title = 'NTE Info';
		$this->detailview_description = 'This page shows detailed information about a particular NTE';
		$this->editview_title = 'NTE Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about NTE';
		
		if( $this->user_access[$this->module_id]['approve'] != 1){
			$this->filter = $this->db->dbprefix.'employee_nte.employee_id = '.$this->user->user_id;	
		}

		if(!$this->is_superadmin && !$this->is_admin && $this->user_access[$this->module_id]['post'] != 1){
			$this->filter = 't1.company_id = '.$this->userinfo['company_id'];
        }
		//set overdue nte
		$this->system->set_overdue_nte();
		
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

		if( $this->user_access[$this->module_id]['view'] != 1 ){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the view action! Please contact the System Administrator.');
			redirect( base_url() . $this->module_link );
		}
		
		if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);

		$data['buttons'] = $this->module_link . '/detail-buttons';
		$this->load->model( 'uitype_detail' );
		if( $this->input->post( 'record_id' ) ){
			$check_record = $this->_record_exist( $this->input->post( 'record_id' ) );			
			if( $check_record->exist ){
				$data['fieldgroups'] = $this->_record_detail( $this->input->post('record_id') );
				$ir_id = $data['fieldgroups'][0]['fields'][2]['value'];
				$data['fieldgroups'][0]['fields'][2]['value'] = $this->system->get_offence( $ir_id );
				$data['fieldgroups'][0]['fields'][7]['value'] = $this->system->get_ir_details( $ir_id );
				$ir_details = $this->db->get_where('employee_ir',array('ir_id'=>$ir_id))->row();
				$involve_employees = explode(',',$ir_details->involved_employees);
				$nte = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')))->row();
				if(!in_array($nte->nte_status_id, array(1,2,4))) $data['buttons'] = $this->module_link. '/backtolist-detail-buttons';
				if( ( $nte->nte_status_id == 3 || $nte->nte_status_id == 4 ) && $this->user_access[$this->module_id]['approve'] == 1 && $this->user_access[$this->module_id]['post'] == 1 && !in_array($this->user->user_id, $involve_employees) ) $data['buttons'] = $this->module_link.'/hr-da-buttons';
				$this->key_field_val = $this->input->post('record_id');
				$data['my_nte'] = $nte->employee_id == $this->user->user_id && in_array($nte->nte_status_id, array(1,2,4)) ? true : false;
			}
			else{
				$data['error'] = $check_record->error_message;
				$data['error2'] = $check_record->error_message2;
			}
			$this->load->vars( $data );
		}
		else{
			$this->session->set_flashdata( 'flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.' );
			redirect( base_url().$this->module_link );
		}
		
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
		if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
		
		if( !$this->input->post( 'record_id' ) ){
				$this->session->set_flashdata( 'flashdata', 'Insufficient data supplied!<br/>Please contact the System Administrator.' );
				redirect( base_url().$this->module_link );
		}
		
		if( $this->input->post( 'record_id' ) == "-1" && $this->user_access[$this->module_id]['add'] != 1 ){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the add action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		if( $this->input->post( 'record_id' ) != "-1" && $this->user_access[$this->module_id]['edit'] != 1 ){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	
		$check_record = $this->_record_exist( $this->input->post( 'record_id' ) );
		if( $check_record->exist || $this->input->post( 'record_id' ) == "-1" ){
			$data['buttons'] = 'template/goback-buttons';
			$nte = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')))->row();
			
			if( $nte->employee_id != $this->user->user_id ){
				$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
				redirect(base_url().$this->module_link.'/detail/'.$this->input->post( 'record_id' ));
			}
			
			$nte->date_issued = date($this->config->item('display_datetime_format'), strtotime($nte->date_issued));
			if(!empty( $nte->date_replied )) $nte->date_replied = date($this->config->item('display_datetime_format'), strtotime($nte->date_replied));
			$employee = $this->hdicore->_get_userinfo( $nte->employee_id );
			$issued_by = $this->hdicore->_get_userinfo( $nte->issued_by );
			$status = $this->db->get_where( 'nte_status', array('nte_status_id' => $nte->nte_status_id) )->row();
			$nte->employee = $employee->firstname.' '.$employee->lastname;
			$nte->issued_by = $issued_by->firstname.' '.$issued_by->lastname;
			$nte->offence = $this->system->get_offence( $nte->ir_id );
			$nte->status = $status->nte_status;
			$nte->details = $this->system->get_ir_details( $nte->ir_id );
			
			if( in_array( $nte->nte_status_id, array(1,2,4) )) $data['buttons'] = $this->module_link . '/edit-reply-buttons';
			
			if( $nte->nte_status_id == 1 ){
				$this->db->update( $this->module_table, array('nte_status_id' => 2), array($this->key_field => $this->input->post( 'record_id' )));	
			}
			
			$data['nte'] = $nte;

			
		}
		else{
			$data['error'] = $check_record->error_message;
			$data['error2'] = $check_record->error_message2;
		}
		$this->load->vars($data);
	
		//additional module edit routine here
		$data['show_wizard_control'] = false;
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
			$data['show_wizard_control'] = true;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
		}
		$data['content'] = $this->module_link.'/editview';
	
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
		if( $this->input->post('record_id') != "-1" && $this->_record_exist( $this->input->post( 'record_id' ) )){
			$nte = $this->db->get_where($this->module_table, array($this->key_field => $this->input->post('record_id')))->row();
			if( $nte->employee_id != $this->user->user_id ){
				$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
				redirect(base_url().$this->module_link);
			}
		}
		
		parent::ajax_save();

		//additional module save routine here
		if( $this->input->post('save_reply') && $this->input->post('save_reply') == "true" && $this->user_access[$this->module_id]['edit'] == 1 ){
			$nte = $this->db->get_where( $this->module_table, array($this->key_field => $this->key_field_val) )->row();
			if( in_array($nte->nte_status_id, array(1,2,4) ) ){
				$this->db->update( $this->module_table, array('date_replied' => date('Y-m-d H:i:s'), 'nte_status_id' => 3), array($this->key_field => $this->key_field_val));
			}
		}
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
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

		/* count query */
		//build query
		$this->_set_left_join();
		$this->db->select($this->listview_qry, false);
		$this->db->from($this->module_table);
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );

		//get list
		$result = $this->db->get();

		// dbug($this->db->last_query());
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
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			
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

							}else if( $detail['name'] == "ir_id" ){
								$cell[$cell_ctr] = $this->system->get_offence( $row[$detail['name']] );	
								$cell_ctr++;
							}else{
								if( in_array( $this->listview_fields[$cell_ctr]['uitype_id'], array( 2, 5, 4, 11, 12, 17, 19, 21, 24, 27, 32, 33, 35, 36, 37, 39) ) ){
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
								else{
									$cell[$cell_ctr] = (is_numeric($row[$detail['name']]) && ($column_type[$detail['name']] != "253" && $column_type[$detail['name']] != "varchar") ) ? number_format($row[$detail['name']], 2, '.', ',') : $row[$detail['name']];
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
	
	function give_da(){
		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
		
		$response->msg = "";
		if( $this->user_access[$this->module_id]['approve'] == 1){

			$nte = $this->db->get_where( $this->module_table, array($this->key_field => $this->input->post('record_id')) )->row();
			$ir = $this->db->get_where( 'employee_ir', array('ir_id' => $nte->ir_id) )->row();
			$offence_no = $this->system->get_da_offence_count($ir ->offence_id, $nte->employee_id );
			$offence_no++;
			
			
			//get the current hr manager
			$hr_manager = $this->system->whois_hrmanager();
			$employee = $this->db->get_where('employee', array('employee_id' => $nte->employee_id))->row();
			$da = array(
				'employee_id' => $nte->employee_id,
				'ir_id' => $nte->ir_id,
				'nte_id' => $nte->nte_id,
				'offence_id' => $ir->offence_id,
				'offence_no' => $offence_no,
				'date_issued' => date('Y-m-d H:i:s'),
				'issued_by' => $this->user->user_id,
				'immediatesup' => $employee->supervisor_id,
				'subhead' => $employee->manager_id,
				'hr_manager' => $hr_manager ? $hr_manager->user_id : 0
			);
			$this->db->insert('employee_da', $da);
			$response->da_id = $this->db->insert_id();
			$response->da_mod = $this->hdicore->get_module('disciplinary_action');
			$this->db->update( $this->module_table, array('nte_status_id' => 6), array($this->key_field => $this->input->post('record_id')));	
			
			//check if there are pending nte for ir
			$pending_nte = $this->system->nte_pending_count($nte->ir_id);

			//if there are no pending nte on ir, change ir status to close
			if( $pending_nte == 0 ){
				$this->db->update('employee_ir', array('ir_status_id' => 4), array('ir_id' => $nte->ir_id));
			}

		}
		else{
			$response->msg = "You dont have sufficient privilege to execute the action! Please contact the System Administrator.";
			$response->msg_type = 'attention';	
		}
		
		$data['json'] = $response;                		
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function acquit(){

		if( !IS_AJAX ){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if( $this->user_access[$this->module_id]['approve'] == 1){

			$this->db->update('employee_nte', array('nte_status_id' => 5), array($this->key_field => $this->input->post('record_id')));

			$nte = $this->db->get_where( $this->module_table, array($this->key_field => $this->input->post('record_id')) )->row();

			//check if there are pending nte for ir
			$pending_nte = $this->system->nte_pending_count($nte->ir_id);

			//if there are no pending nte on ir, change ir status to close
			if( $pending_nte == 0 ){
				$this->db->update('employee_ir', array('ir_status_id' => 4), array('ir_id' => $nte->ir_id));
			}

		}
		else{
			$response->msg = "You dont have sufficient privilege to execute the action! Please contact the System Administrator.";
			$response->msg_type = 'attention';	
		}

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
        
		if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete']) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	
	function print_record( $record_id = 0 ){
		if(!$this->user_access[$this->module_id]['print'] == 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);			
		}

		if ( $record_id == 0 ) $record_id = $this->input->post('record_id');
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$template = $this->template->get_module_template($this->module_id, 'NTE');
		
		// Get applicant details. (This returns the fieldgroup array.
		$check_record = $this->_record_exist($record_id);

		if ($check_record->exist) {
			$vars = get_record_detail_array($record_id);
			$employee = $this->hdicore->_get_userinfo( $record_id );
			$vars['salutation'] = $employee->salutation;
			$vars['date'] = date( $this->config->item('display_datetime_format') );
			
			$nte = $this->db->get_where($this->module_table, array($this->key_field => $record_id))->row();
			$ir = $this->db->get_where('employee_ir', array('ir_id' => $nte ->ir_id))->row();
			$immediate_superior = $this->hdicore->_get_userinfo( $ir->immediate_superior );
			$vars['immediate_superior'] = $immediate_superior->salutation.' '.$immediate_superior->firstname.' '.$immediate_superior->lastname;
			$offence = $this->db->get_where('offence', array('offence_id' => $ir->offence_id))->row();
			$vars['offence'] = $offence->offence;
			$vars['ir_date'] = date( $this->config->item('display_datetime_format'), strtotime( $ir->date_prepared ) );
			$html = $this->template->prep_message($template['body'], $vars, false, true);
			
			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date($this->config->item('display_datetime_format_compact')).'-NTE-'. $record_id .'.pdf', 'D');
		}
		else {
			$this->session->set_flashdata('flashdata', 'The Data you are trying to access does not exist.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */