<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Day_type extends MY_Controller
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
	}

	// START - default module functions
	// default jqgrid controller method
	function index(){
		if($this->user_access[$this->module_id]['view'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}
		
		$data['content'] = $this->module_link . '/detail';
		$data['scripts'][] = chosen_script();
		if( $this->config->item('use_day_type_matrix') ) $data['content'] = $this->module_link . '/detail_matrix';
			
		
		if($this->session->flashdata('flashdata')){
			$info['flashdata'] = $this->session->flashdata('flashdata');
			$data['flashdata'] = $this->load->view($this->userinfo['rtheme'].'/template/flashdata', $info, true);
		}
		
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
		if($this->user_access[$this->module_id]['edit'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}

		$data['content'] = $this->module_link . '/edit';
	
		
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

	function edit_matrix(){
		if($this->user_access[$this->module_id]['edit'] != 1){
			$this->session->set_flashdata('flashdata', 'You dont have sufficient privilege to execute the list action of '.$this->module_name.'! Please contact the System Administrator.');
			redirect( base_url() );
		}

		$_POST['record_id'] = $this->uri->rsegment(3);

		$this->employment_status = $this->db->get_where('employment_status', array('deleted' => 0, 'employment_status_id' => $this->uri->rsegment(3)))->row();
		$data['content'] = $this->module_link . '/edit-matrix';
	
		
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

	function save(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if($this->user_access[$this->module_id]['edit'] != 1){
			$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
			$response->msg_type = 'attention';
		}
		else{
			if( $this->config->item('use_day_type_matrix') ){
				$employment_status_id = $this->input->post('employment_status_id');
				$this->db->delete('day_type_and_rates_matrix', array('employment_status_id' => $employment_status_id));
				unset($_POST['employment_status_id']);
				$columns = $_POST;
				foreach($columns as $column => $row){
					foreach($row as $day_prefix => $value){
						$check = $this->db->get_where('day_type_and_rates_matrix', array('day_prefix' => $day_prefix, 'employment_status_id' => $employment_status_id));
						if($check->num_rows() == 1){
							$this->db->update('day_type_and_rates_matrix', array($column => $value), array('day_prefix' => $day_prefix, 'employment_status_id' => $employment_status_id));	
						}
						else{
							switch($day_prefix){
								case 'reg':
									$day_type = "Regular";
									break;
								case 'rd':
									$day_type = "Restday";
									break;
								case 'leg':
									$day_type = "Legal Holiday";
									break;
								case 'spe':
									$day_type = "Special Holiday";
									break;
								case 'legrd':
									$day_type = "Legal Holiday Restday";
									break;
								case 'sperd':
									$day_type = "Special Holiday Restday";
									break;
								case 'dob':
									$day_type = "Double Holiday";
									break;
								case 'dobrd':
									$day_type = "Double Holiday Restday";
									break;
							}
							$this->db->insert('day_type_and_rates_matrix', array('day_type' => $day_type, $column => $value, 'day_prefix' => $day_prefix, 'employment_status_id' => $employment_status_id));
						}
					}
				}
			}
			else{
				$columns = $_POST;
				foreach($columns as $column => $row){
					foreach($row as $day_prefix => $value){
						$this->db->update('day_type_and_rates', array($column => $value) ,array('day_prefix' => $day_prefix));
					}
				}
			}
			$response->msg = "Successfully saved day types and rates configuration.";
			$response->msg_type = 'success';
		}
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

	function delete(){
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	function get_day_type_matrix(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

		if($this->user_access[$this->module_id]['edit'] != 1){
			$response->msg = "You dont have sufficient privilege to execute this action! Please contact the System Administrator.";
			$response->msg_type = 'attention';
		}
		else{
			$response->detail = $this->load->view( $this->userinfo['rtheme'] . '/' .$this->module_link . '/day_type_matrix', '', true );
			
			$response->msg = "Successfully saved day types and rates configuration.";
			$response->msg_type = 'success';
		}
		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */
