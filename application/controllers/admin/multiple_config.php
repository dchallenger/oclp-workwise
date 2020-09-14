<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class multiple_config extends MY_Controller
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
		$this->detailview_title = 'System Configuration';
		$this->detailview_description = '<span class="red">Warning! Editing ANY of these Configuration may cause the whole system to malfunction.</span>';
		$this->editview_title = 'Edit System Configuration';
		$this->editview_description = '<span class="red">Warning! Editing ANY of these Configuration may cause the whole system to malfunction.</span>';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		//additional module detail routine here
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'admin/multiple_config/detailview';
		
		//other views to load
		$data['views'] = array();

		$key_name = str_replace(" ","_",strtolower($this->module_name));

		$config = $this->db->get_where( 'config', array( "key" => $key_name) );
		if($config->num_rows() > 0)
		{
			$record_id = $key_name;
		}
		else
		{
			$record_id = '';
		}
		$this->load->model( 'uitype_detail' );
		$data['fieldgroups'] = $this->_record_detail( $record_id );

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

    function _record_detail( $record_id , $quick_edit_flag = false )
	{
		//get field groups
		$this->db->order_by( 'sequence' );
		$fieldgroups = $this->db->get_where( 'fieldgroup', array( "module_id" => $this->module_id, "visible" => 1, "deleted" => 0 ) );
		if( $fieldgroups->num_rows() > 0 )
		{
			$fieldgroups = $fieldgroups->result_array();
			$table_fields = array();
			foreach( $fieldgroups as $fgroup_index => $fg_detail ){				
				//get the field set for field group
				$fields = $this->_get_fieldgroup_fields( $fg_detail['fieldgroup_id'], $quick_edit_flag );								
				if( $fields->num_rows() > 0 ){
					$select = array();
					$fields = $fields->result_array();					
					foreach ($fields as $key => $value) 
					{
						// $fields[$key]['fieldname'] = str_replace(" ","_",strtolower($value['fieldlabel']));
						$fields[$key]['column'] = $fields[$key]['fieldname'];
					}
					foreach( $fields as $field ){
						if (!array_key_exists($field['table'], $table_fields)) 
						{
							$table_fields[$field['table']] = $this->db->list_fields($field['table']);
						}

						if( $field['uitype_id'] == 24 ||  $field['uitype_id'] == 35 || $field['uitype_id'] == 40){
							$select[] = $field['table'].'.'.$field['column'].'_from';
							$select[] = $field['table'].'.'.$field['column'].'_to';
						}
						else if( $field['uitype_id'] == 25 ){
							//do nothing, exclude this field from query
						}
						else if( $field['uitype_id'] == 26 || $field['uitype_id'] == 38){
							$select[] = $field['table'].'.'.$field['column'].'_start';
							$select[] = $field['table'].'.'.$field['column'].'_end';
						}
						else if(in_array($field['column'], $table_fields[$field['table']])) {
							$select[] = $field['table'].'.'.$field['column'];
						}

						//set related tables
						if ( $field['table'] != $this->module_table ) $this->related_table[$field['table']] = array( $this->key_field, $this->key_field );
					}

					if( $record_id != "" )
					{
						//set query for detail/field values
						$this->db->select( implode( ',', $select ) );
						$this->_set_left_join();
						$this->db->from( $this->module_table );
						$this->db->where( $this->module_table.'.'.$this->key_field." = '".$record_id."'" );
						$details2 = $this->db->get();

						$details = $this->hdicore->_get_config($record_id);
						
						if ($this->db->_error_message()) {
							show_error($this->db->_error_message() . '<br />' . $this->db->last_query());
							$this->session->set_flashdata('flashdata', $this->db->_error_message());
							redirect(base_url());
						}

						if( $details2->num_rows() == 1 ) 
						{
							$details = array(0=>$details);
							//finalise array of fields
							$field_array = array();							
							foreach( $fields as $field_index => $field )
							{
								$fields[$key]['column'] = $fields[$key]['fieldname'];
								// $field['fieldname'] = str_replace(" ","_",strtolower($field['fieldlabel']));
								// $field['column'] = str_replace(" ","_",strtolower($field['fieldlabel']));
								if( $field['encrypt'] == 1 ){
									//decrypt the value
									$this->load->library('encrypt');
									$details[0][$field['column']] = $this->encrypt->decode( $details[0][$field['column']] );
								}
								
								//handle value of uitype
								if( $field['uitype_id'] == 24 ){
									$fields[$field_index]['value'] = "";
									if($details[0][$field['column'].'_from'] == '0000-00-00') $details[0][$field['column'].'_from'] = '';
									if($details[0][$field['column'].'_to'] == '0000-00-00') $details[0][$field['column'].'_to'] = '';
									if(!empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y', strtotime($details[0][$field['column'].'_from'])).' to '.date('d-M-Y', strtotime($details[0][$field['column'].'_to']));
									}
									else if(!empty($details[0][$field['column'].'_from']) && empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y', strtotime($details[0][$field['column'].'_from'])).' to ';
									}
									else if(empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= ' to '.date('d-M-Y', strtotime($details[0][$field['column'].'_to']));
									}
								}
								else if( $field['uitype_id'] == 40 ){
									if($details[0][$field['column'].'_from'] == '0000-00-00 00:00:00') $details[0][$field['column'].'_from'] = '';
									if($details[0][$field['column'].'_to'] == '0000-00-00 00:00:00') $details[0][$field['column'].'_to'] = '';
									if(!empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_from'])).' to '.date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_to']));
									}
									else if(!empty($details[0][$field['column'].'_from']) && empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_from'])).' to ';
									}
									else if(empty($details[0][$field['column'].'_from']) && !empty($details[0][$field['column'].'_to'])){
										$fields[$field_index]['value'] .= ' to '.date('d-M-Y h:i:s a', strtotime($details[0][$field['column'].'_to']));
									}
								}
								else if( $field['uitype_id'] == 25 ){
									$fields[$field_index]['value'] = "";
								}
								else if( $field['uitype_id'] == 26){
									$fields[$field_index]['value'] = $details[0][$field['column'].'_start'].' to '.$details[0][$field['column'].'_end'];
								}
								else if($field['uitype_id'] == 38){
									$fields[$field_index]['value'] = date('h:i A' ,strtotime($details[0][$field['column'].'_start']) ) .' to '.date('h:i A' ,strtotime($details[0][$field['column'].'_end']) );
								}
								else if( $field['uitype_id'] == 35 ){
									$fields[$field_index]['value'] = number_format( $details[0][$field['column'].'_from'], 2, '.', ',').' to '.number_format( $details[0][$field['column'].'_to'], 2, '.', ',');
								}
								else if( $field['uitype_id'] == 39 ){
									if(is_array($details[0][$field['column']]))
										$fields[$field_index]['value'] = implode(',',$details[0][$field['column']]);
									else
										$fields[$field_index]['value'] = $details[0][$field['column']];
								}
								else{
									//default uitype
									$fields[$field_index]['value'] = $details[0][$field['column']];
								}
							}

							$fieldgroups[$fgroup_index]['fields'] = $fields;
						}
					}
					else
					{
						foreach($fields as $field_index => $field){
							$fields[$field_index]['value'] = "";
							if( $this->input->post($fields[$field_index]['fieldname']) ){
								$fields[$field_index]['value'] = $this->input->post( $fields[$field_index]['fieldname'] );
							}
						}

						$fieldgroups[$fgroup_index]['fields'] = $fields;
					}					
				}
			}
			return $fieldgroups;
		}
		else{
			return false;
		}
	}

	function _get_fieldgroup_fields($fieldgroup_id = 0, $quick_edit_flag = false)
	{
		$this->db->order_by('sequence');
		//$where = array("fieldgroup_id" => $fieldgroup_id, "visible" => 1, 'deleted' => 0);
		$where = array("fieldgroup_id" => $fieldgroup_id, 'deleted' => 0);
		if( $quick_edit_flag ) $where['quick_edit'] = 1;
		return $this->db->get_where('field', $where);
	}
	
	function edit()
	{
		if($this->user_access[$this->module_id]['edit'] == 1)
		{
			$this->load->helper('form');
			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] = '<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>'.uploadify_script();
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'admin/multiple_config/editview';	
			
			$key_name = str_replace(" ","_",strtolower($this->module_name));


			$config = $this->db->get_where( 'config', array( "key" => $key_name) );
			if($config->num_rows() > 0)
			{
				$record_id = $key_name;
			}
			else
			{
				$record_id = '';
			}
			$this->load->model( 'uitype_edit' );
			$data['fieldgroups'] = $this->_record_detail( $record_id );

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();
			
			$data['meta_raw'] = $this->hdicore->_get_config('meta');
			$data['app_directories'] = $this->hdicore->_get_config('app_directories');
			$data['smtp'] = $this->hdicore->_get_config('outgoing_mailserver');
			
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
		if(IS_AJAX)
		{
			if($this->user_access[$this->module_id]['edit'] == 1){
				$response->msg = 'Data has been successfully saved.';
				$response->msg_type = 'success';	
				$response->record_id = '';

				$this->db->select('field.fieldname, field.column, field.uitype_id, field.datatype, field.fieldlabel, field.encrypt, field.table');
				$this->db->join('fieldgroup', 'fieldgroup.fieldgroup_id = field.fieldgroup_id');

				$where = array( 'field.module_id' => $this->module_id, 
								"field.table" => $this->module_table, 
								'field.deleted' => 0, 
								'fieldgroup.deleted' => 0 
							   );

				if($quick_edit_flag) 
					$where['quick_edit'] = 1;

				$this->db->where( $where );
				$this->db->from('field');

				$fieldset = $this->db->get();
				$fieldset = $fieldset->result_array();

				$meta = array();

				foreach($fieldset as $field_index => $field)
				{
					// $field['fieldname'] = str_replace(" ","_",strtolower($field['fieldlabel']));
					$meta[$field['fieldname']] = $this->input->post($field['fieldname']);
				}

				$key_name = str_replace(" ","_",strtolower($this->module_name));				
				$smtp = base64_encode( serialize($meta) );
				$this->hdicore->_update_config($key_name, $smtp);
				
				if( $this->db->_error_message() != "" )
				{
					$response->msg = $this->db->_error_message();
					$response->msg_type = 'error';
				} 
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = 'attention';
			}		
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

	function get_placeholder()
	{
		$this->db->select('field.fieldname, field.column, field.uitype_id, field.datatype, field.fieldlabel, field.encrypt, field.table, field.tbx_description');
		$this->db->join('fieldgroup', 'fieldgroup.fieldgroup_id = field.fieldgroup_id');

		$where = array( 'field.module_id' => $this->module_id, 
						"field.table" => $this->module_table, 
						'field.deleted' => 0, 
						'fieldgroup.deleted' => 0 
					   );

		if($quick_edit_flag) 
			$where['quick_edit'] = 1;

		$this->db->where( $where );
		$this->db->from('field');

		$fieldset = $this->db->get();
		$fieldset = $fieldset->result_array();

		foreach($fieldset as $field_index => $field)
		{
			if($field['tbx_description'] != "" || $field['tbx_description'] != null)
				$response->{$field['fieldname']} = $field['tbx_description'];
		}

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);

	}
	// END - default module functions
	
	// START custom module funtions
	
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>