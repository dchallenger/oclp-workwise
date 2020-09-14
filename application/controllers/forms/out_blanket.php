<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Out_blanket extends MY_Controller
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

		if (CLIENT_DIR == "firstbalfour") {

			if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
				$dbprefix = $this->db->dbprefix;
				$subordinate_id = array();
				$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
				$subordinates = $this->system->get_subordinates_by_project($emp->employee_id);
				$subordinate_id = array(0);
				if( count($subordinates) > 0 && $subordinates != false){

					$subordinate_id = array();
					$blanket_id = array();
					foreach ($subordinates as $subordinate) {
							$subordinate_id[] = $subordinate['employee_id'];
							$sql = "SELECT * FROM {$dbprefix}{$this->module_table} WHERE FIND_IN_SET({$subordinate['employee_id']}, employee_id)";
							$blanket = $this->db->query($sql);
							
							if ($blanket && $blanket->num_rows() > 0) {
								foreach ($blanket->result() as $value) {
									$blanket_id[] = $value->outblanket_id;	
								}
								
							}
					}

					$blankets = array_unique($blanket_id);

					$this->filter .= $dbprefix.'employee_out_blanket.outblanket_id IN ('.implode(',', $blankets).')';
				}else{
					$this->filter .= $dbprefix.'employee_out_blanket.outblanket_id IN (0)';
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
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/modules/forms/blanket.js"></script>';
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
		parent::ajax_save();

		//additional module save routine here
	}

	function after_ajax_save()
	{
		if ($this->get_msg_type() == 'success') {
			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->delete('employee_out');

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->where('deleted', 0);

			$record = $this->db->get($this->module_table)->row();
			$users = explode(',', $record->employee_id);

			$data['form_status_id']		 = 3;			
			$data['outblanket_id'] 		 = $this->key_field_val;
			$data['date']				 = $record->date;
			$data['time_start']			 = $record->time_start;
			$data['time_end']			 = $record->time_end;
			$data['reason']			 	 = $record->reason;
			$data['documents']			 = $record->documents;

			$insert = array();
			foreach ($users as $user) {
				$data['employee_id'] = $user;
				$insert[] = $data;
			}

			$this->db->insert_batch('employee_out', $insert);
		}

		parent::after_ajax_save();
	}

	function delete(){
		if( IS_AJAX ){
			if($this->user_access[$this->module_id]['delete'] == 1){
				if($this->input->post('record_id')){
					$record_id = explode(',', $this->input->post('record_id'));
					$this->db->where_in($this->key_field, $record_id);
					$this->db->update($this->module_table, array('deleted' => 1));

					$this->db->where_in($this->key_field, $record_id);
					$this->db->update('employee_out', array('deleted' => 1));
					if( $this->db->_error_message() == "" ){
						$response->msg = "Record(s) has been deleted.";
						$response->msg_type = 'success';
					}
					else{
						$response->msg = $this->db->_error_message();
						$response->msg_type = 'error';
					}
				}
				else{
					$response->msg = "Insufficient data supplied.";
					$response->msg_type = 'attention';
				}
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function filter_employees()
	{
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the edit action! Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		} else {
			$record_id = $this->input->post('record_id');

			$defaults = array();
			if ($record_id > 0) {
				$this->db->where('outblanket_id', $record_id);
				$this->db->where('deleted', 0);

				$record = $this->db->get($this->module_table);

				if ($record->num_rows() > 0) {
					$r = $record->row();

					$defaults = explode(',', $r->employee_id);
				}
			}

			$location_id = implode('","', explode(',', $this->input->post('location_id')));
			$city_id = implode('","', explode(',', $this->input->post('city_id')));

			$locwhere = array();
			if ($location_id != '') {
				$locwhere[] = 'location_id IN ("' . $location_id . '")';
			}

			if ($city_id != '') {
				$locwhere[] = 'pres_city IN ("' . $city_id . '")';
			}

			if (count($locwhere) == 0) {
				$locwhere = '1=1';
			} else {
				$locwhere = '(' . implode(' OR ', $locwhere) . ')';
			}			

			$sub_list = '1=1';

			if (CLIENT_DIR == "firstbalfour") {
				if( !( $this->is_superadmin || $this->is_admin ) && $this->user_access[$this->module_id]['project_hr'] == 1 ){
					$subordinate_id = array();
					$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
					$subordinates = $this->system->get_subordinates_by_project($emp->employee_id);


					$subordinate_id = array(0);
					if( count($subordinates) > 0 && $subordinates != false){

						$subordinate_id = array();

						foreach ($subordinates as $subordinate) {
								$subordinate_id[] = $subordinate['employee_id'];
						}
					}		

					$subordinate_list = implode(',', $subordinate_id);
					if( $subordinate_list != "" || $subordinate_list != 0){
						$sub_list = 'e.employee_id IN ('.$subordinate_list.')';
					}
					else{
						if ($subordinates == false ) {
							$sub_list = 'e.employee_id IN (0)';
						}
						
					}
				}	
			}

			$this->db->select('e.employee_id as id, CONCAT(u.firstname, " ", u.middleinitial, " ", u.lastname, " ", u.aux) as name, c.city', false);
			$this->db->from('employee e');
			$this->db->join('user u', 'u.user_id = e.employee_id', 'left');
			$this->db->join('cities c', 'e.pres_city = c.city_id', 'left');
			$this->db->where('e.deleted', 0);
			$this->db->where('resigned', 0);			
			$this->db->where($locwhere, '', false);
			$this->db->where($sub_list, '', false);		
			$this->db->order_by('city,name','ASC');
			$employees = $this->db->get();

			$options = '';

			if ($employees->num_rows() > 0) {
				foreach ($employees->result() as $employee) {
					$a_employee[$employee->city][] = $employee;
				}


				foreach ($a_employee as $city => $emp) {
					$options .= '<optgroup label="' . $city . '">';
					foreach ($emp as $e) {
						if ($e->name != ''){
							$selected = (in_array($e->id, $defaults)) ? ' selected="selected"' : '';

							$options .= '<option value="' . $e->id . '"' . $selected . '>' . $e->name . '</option>';
						}
					}
					$options .= '</optgroup>';
				}				
			}			


			$this->load->view('template/ajax', array('html' => $options));
		}
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */