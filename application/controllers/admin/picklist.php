<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Picklist extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Picklist';
		$this->listview_description = 'This module lists all defined picklist.';
		$this->jqgrid_title = "Picklist List";
		$this->detailview_title = 'Picklist Info';
		$this->detailview_description = 'This page shows detailed information about a particular Picklist';
		$this->editview_title = 'Picklist Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about Picklist';
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
		if($this->user_access[$this->module_id]['edit'] == 1)
		{
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
			$data['views'] = array('admin/module/picklist_manager');
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
	function get_picklist_values()
	{
		if( IS_AJAX ){
			$response->msg = "";
			$response->picklist_values = "";
			
			if($this->user_access[$this->module_id]['edit'] == 1){
				if($this->input->post('picklist_id')){
					//get picklist details
					$picklist = $this->db->get_where('picklist', array('picklist_id' =>  $this->input->post('picklist_id') ))->row();
					$id_column = $picklist->picklist_name.'_id';
					$name_column = $picklist->picklist_name;
					$picklist_table = $picklist->picklist_table; 
					if( $this->db->_error_message() == "" ){
						if($picklist->picklist_type == "Table"){
							$this->db->select( $id_column .', '. $name_column.', description' );
							$this->db->from( $picklist_table );
							$this->db->order_by( $name_column );
							$this->db->where( array( 'deleted' => 0 ) );
							$picklistvalues = $this->db->get();
						}
						else{
							$picklistvalues = $this->db->query(str_replace('{dbprefix}', $this->db->dbprefix, $picklist_table ));
						}
						if( $this->db->_error_message() == "" ){
							foreach($picklistvalues->result() as $index => $row) :
							$response->picklist_values .= '<tr class="'.($index % 2 == 0 ? 'even' : 'odd').'">';	
							$response->picklist_values .= '<td>'. $row->$name_column .'</td>';
							$response->picklist_values .= '<td>'.$row->description.'</td>';
							$response->picklist_values .= '<td align="center">';
							if($picklist->picklist_type == "Table"):
								$response->picklist_values .= '<span class="icon-group"><a href="javascript:void(0)" tooltip="Edit" class="icon-button icon-16-edit" onclick="edit_picklist_value(\''.$row->$id_column.'\', \''.$name_column.'\', \''.$picklist_table.'\')"></a><a href="javascript:void(0)" onclick="del_picklist_value(\''.$row->$id_column.'\', \''.$name_column.'\', \''.$picklist_table.'\')" tooltip="Delete" class="icon-button icon-16-delete delete-single"></a></span>';
							endif;
							$response->picklist_values .= '</td>';
							$response->picklist_values .= '</tr>';
							endforeach;
						}
						else{
							$response->msg = $this->db->_error_message();
							$response->msg_type = 'error';
						}
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
	}
	
	function picklist_value_editor()
	{
		if( IS_AJAX ){
			$response->msg = "";
			
			if($this->user_access[$this->module_id]['edit'] == 1){
				$data['value_id'] = $this->input->post('value_id');
				$data['picklist_name'] = $this->input->post('picklist_name');
				$data['picklist_table'] = $this->input->post('picklist_table');
				$response->picklist_value_editor = $this->load->view($this->userinfo['rtheme'].'/admin/module/picklist_value_editor', $data, true);
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = "error";
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}		
	}
	
	function save_picklist_value()
	{
		if(IS_AJAX){
			$response->msg = "";
			
			if($this->user_access[$this->module_id]['edit'] == 1){
				$response->value_id = $this->input->post('value_id');
				$picklist_name = $this->input->post('picklist_name');
				$picklist_table = $this->input->post('picklist_table');
				
				$insert[$picklist_name] = $this->input->post('picklist_value');
				$insert['description'] = $this->input->post('picklist_value_description');
				if($response->value_id == -1){
					$this->db->insert($picklist_table, $insert);
					$response->value_id = $this->db->insert_id();
				}
				else{
					$this->db->where($picklist_name.'_id', $response->value_id);
					$this->db->update($picklist_table, $insert);
				}
				
				$response->msg = "Picklist value successfully saved.";
				$response->msg_type = "success";
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = "error";
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}	
	}
	
	function del_picklist_value()
	{
		if(IS_AJAX){
			$response->msg = "";
			if($this->user_access[$this->module_id]['edit'] == 1){
				$value_id = $this->input->post('value_id');
				$picklist_name = $this->input->post('picklist_name');
				$picklist_table = $this->input->post('picklist_table');
				
				$this->db->where( $picklist_name.'_id', $value_id);
				$this->db->update( $picklist_table, array('deleted' => 1) );
			}
			else{
				$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
				$response->msg_type = "error";
			}
			
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}	
	}
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>