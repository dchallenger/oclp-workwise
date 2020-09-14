<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Approver extends MY_Controller
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
		$this->editview_description = 'This page allows saving/editing information about';

		$dbprefix = $this->db->dbprefix;
		$this->filter = $dbprefix."user.inactive = 0 AND ".$dbprefix."employee.resigned = 0";
		$user_id = $this->user->user_id;
		$subordinate_id = array($user_id);
		if( $this->user_access[$this->module_id]['post'] != 1 ){
			$emp = $this->db->get_Where('employee', array('employee_id' => $user_id ))->row();
			$user = $this->db->get_Where('user', array('employee_id' => $user_id ))->row();
			$subordinates = $this->hdicore->get_subordinates($user->position_id, $emp->rank_id, $user_id);
			
			if( count($subordinates) > 0 ){
				foreach ($subordinates as $subordinate) {
					$subordinate_id[] = $subordinate['user_id'];
				}
			}
			$subordinate_list = implode(',', $subordinate_id);
			$this->filter .= ' AND '. $dbprefix.'user.employee_id IN ('.$subordinate_list.')';
		}

		$this->default_sort_col = array('t0firstnamemiddleinitiallastnameaux');
		
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

		$data['approvers'] = $this->db->get_where('employee_approver', array('deleted' => 0, 'employee_id' => $this->input->post('record_id')))->result_array();

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
		$data['buttons'] = 'template/edit-nobuttons';
	
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
		$this->db->join('user_position','user_position.position_id = user.position_id','left');
		$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
		$this->db->where($this->module_table.'.position_id >= 0');
		$this->db->where('user_position.deleted',0);
		if(!empty( $this->filter ) ) $this->db->where( $this->filter );
		if( $this->sensitivity_filter ){
			$fields = $this->db->list_fields($this->module_table);
			if(in_array('sensitivity', $fields) && isset($this->sensitivity[$this->module_id])){
				$this->db->where($this->module_table.'.sensitivity IN ('.implode(',', $this->sensitivity[$this->module_id]).')');
			}
			else{
				$this->db->where($this->module_table.'.sensitivity IN (0)');	
			}	
		}

		if (method_exists($this, '_set_filter')) {
			$this->_set_filter();
		}

		//get list
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

			/* record query */
			//build query
			$this->_set_left_join();
			$this->db->select($this->listview_qry, false);
			$this->db->from($this->module_table);
			$this->db->join('user_position','user_position.position_id = user.position_id','left');
			$this->db->where($this->module_table.'.position_id >= 0');
			$this->db->where($this->module_table.'.deleted = 0 AND '.$search);
			$this->db->where('user_position.deleted',0);
			if(!empty( $this->filter ) ) 	$this->db->where( $this->filter );
			if( $this->sensitivity_filter ){
				if(in_array('sensitivity', $fields) && isset($this->sensitivity[$this->module_id])){
					$this->db->where($this->module_table.'.sensitivity IN ('.implode(',', $this->sensitivity[$this->module_id]).')');
				}	
				else{
					$this->db->where($this->module_table.'.sensitivity IN (0)');	
				}	
			}

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
							}else{
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

	function ajax_save(){
		//additional module save routine here
		
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function get_approvers(){
		$response->approvers = $this->load->view( $this->userinfo['rtheme']. '/employee/approver/approver_settings', '', true );
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
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
        
		if ( $this->user_access[$this->module_id]['edit'] ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        $actions .= '</span>';

		return $actions;
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
            $buttons .= "<div class='icon-label'><a class='icon-16-export' container='".$container."' module_link='".$module_link."' onClick='export_list()' href='javascript:void(0)'><span>Export</span></a></div>";
        }             
        
        $buttons .= "</div>";
                
		return $buttons;
	}

	function export()
	{
		ini_set('memory_limit', "512M");

		$this->db->select("CONCAT(curr_emp.firstname,' ',curr_emp.middlename,' ',curr_emp.lastname,IF(curr_emp.aux <> '',',',''),IF(curr_emp.aux <> '',curr_emp.aux,'')) AS current_emp,
							pos.position,
							cmpny.company,
							module.short_name AS module_name,
							CONCAT(approver.firstname,' ',approver.middlename,' ',approver.lastname,IF(approver.aux <> '',',',''),IF(approver.aux <> '',approver.aux,'')) AS approver_emp,
							hr_approver_condition.approver_condition, 
							IF(".$this->db->dbprefix."employee_approver.approver = 1, 'Yes', 'No') AS approver_bool, 
							IF(".$this->db->dbprefix."employee_approver.email = 1, 'Yes', 'No') AS email_bool,
							employee_approver.employee_id,
							employee_approver.module_id"
							, false);
		$this->db->where('employee_approver.deleted',0);
		$this->db->where('curr_emp.inactive', 0, false);
		$this->db->where('curr_employee_table.resigned', 0, false);
		$this->db->join('user curr_emp', 'curr_emp.employee_id = employee_approver.employee_id', 'left');
		$this->db->join('employee curr_employee_table', 'curr_emp.employee_id = curr_employee_table.employee_id', 'left');
		$this->db->join('user_position pos', 'curr_emp.position_id = pos.position_id', 'left');
		$this->db->join('user_company cmpny', 'curr_emp.company_id = cmpny.company_id', 'left');
		$this->db->join('user approver', 'approver.employee_id = employee_approver.approver_employee_id', 'left');
		$this->db->join('module module', 'module.module_id = employee_approver.module_id', 'left');
		$this->db->join('approver_condition', 'employee_approver.condition = approver_condition.approver_condition_id', 'left');
		$this->db->order_by('employee_approver.employee_id', 'ASC');
		$this->db->order_by('employee_approver.module_id', 'ASC');
		$this->db->order_by('employee_approver.employee_approver_id', 'ASC');
		$emp_approvers = $this->db->get('employee_approver');
		
		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setTitle($query->description)
		            ->setDescription($query->description);
		               
		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();

		//header
		$alphabet  = range('A','Z');

		$alpha_ctr = 0;
		$sub_ctr   = 0;

		// width setting
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);

		//style for cell
		$info_style = array(
			'font' => array(
				// 'bold' => true,
				'italic' => true,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$headerstyle = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK )
			),
			'font' => array(
				'bold' => true,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$module_style = array(
			'font' => array(
				'bold' => true,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			)
		);

		$condition_style = array(
			'font' => array(
				// 'bold' => true,
				'italic' => true,
			),			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$top_btm_border = array(
			'borders' => array(
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN ),
				'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN )
			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A:C')->applyFromArray($info_style);
		$objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($module_style);
		$objPHPExcel->getActiveSheet()->getStyle('F:H')->applyFromArray($condition_style);
		
		//style for cell

		// headers
		$headers = array(
			"A" => "Employee Name",
			"B" => "Position Title",
			"C" => "Company",
			"D" => "Module",
			"E" => "Module Approver",
			"F" => "Condition",
			"G" => "Approver",
			"H" => "E-mail"
			);

		$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

		foreach($headers as $letter => $header):
			$activeSheet->setCellValue($letter.'1', $header);	
			$objPHPExcel->getActiveSheet()->getStyle($letter.'1')->applyFromArray($headerstyle);
		endforeach;

		$line = 2;
		$employee_flag = "";
		$module_flag = "";
		foreach($emp_approvers->result() as $emp_approver):
			if($employee_flag != $emp_approver->employee_id) {
				$objPHPExcel->getActiveSheet()->getStyle('A'.$line.':H'.$line)->applyFromArray($top_btm_border);
				$line++;
				$employee_flag = $emp_approver->employee_id;					
				$activeSheet->setCellValue('A'.$line, $emp_approver->current_emp);
				$activeSheet->setCellValue('B'.$line, $emp_approver->position); 
				$activeSheet->setCellValue('C'.$line, $emp_approver->company);
			}
			if($module_flag != $emp_approver->module_id) {
				$activeSheet->setCellValue('D'.$line, $emp_approver->module_name);
				$activeSheet->setCellValue('E'.$line, $emp_approver->approver_emp);
				$activeSheet->setCellValue('F'.$line, $emp_approver->approver_condition);
				$activeSheet->setCellValue('G'.$line, $emp_approver->approver_bool);
				$activeSheet->setCellValue('H'.$line, $emp_approver->email_bool);	
				$module_flag = $emp_approver->module_id;
			} else {
				$activeSheet->setCellValue('E'.$line, $emp_approver->approver_emp);
				$activeSheet->setCellValue('F'.$line, $emp_approver->approver_condition);
				$activeSheet->setCellValue('G'.$line, $emp_approver->approver_bool);
				$activeSheet->setCellValue('H'.$line, $emp_approver->email_bool);	
			}
			$line++;
		endforeach;

		// Save it as an excel 2003 file
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename=Movement_History_'.date('Y-m-d').'.xls');
		header('Content-Transfer-Encoding: binary');

		$path = 'uploads/dtr_summary/'.date('Y-m-d-').'employee_approver_export.xls';
		
		$objWriter->save($path);

		$response->msg_type = 'success';
		$response->data = $path;
		
		$this->load->view('template/ajax', array('json' => $response));
	}

	// private function _get_employee($emp_id = null, $need = "name")
	// {
	// 	if($emp_id != null)
	// 	{
	// 		$user = $this->db->get_where('employee', array('employee_id' => $emp_id));
	// 		if($user->num_rows() > 0)
	// 		{
	// 			$user = $user->row();
	// 			switch($need){
	// 				case "name":
	// 					return $user->firstname." ".$user->middlename." ".$user->lastname;
	// 				case "position":
	// 					return $this->db->get('user_position', array("position_id" => $user->position_id))->row()->position;
	// 				case "company":
	// 					return $this->db->get('user_company', array("company_id" => $user->company_id))->row()->company;
	// 			}
	// 		} else
	// 			return false;
	// 	} else
	// 		return;
	// }

	function _custom_join(){
		$this->db->join('employee', 'employee.user_id = user.user_id', 'left');
	}	
	// END custom module funtions

}

/* End of file */
/* Location: system/application */