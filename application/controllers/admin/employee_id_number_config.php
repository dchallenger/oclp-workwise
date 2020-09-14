<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_id_number_config extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists payroll accounts.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a payroll account';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a payroll account';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/detailview.js"></script>';
		$data['content'] = 'employee/employee_id_number_config/detailview';

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

		$this->db->where('employee_id_number_config.deleted',0);
		$this->db->join('employee_id_number_config_type','employee_id_number_config.employee_id_number_config_type_id = employee_id_number_config_type.employee_id_number_config_type_id');
		$result = $this->db->get('employee_id_number_config');

		$data['id_no_config'] = $result;

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

			//additional module edit routine here
			$data['show_wizard_control'] = false;
			$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
			if( !empty($this->module_wizard_form) && $this->input->post('record_id') == '-1' ){
				$data['show_wizard_control'] = true;
				$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview-wizard-form.js"></script>';
			}
			$data['content'] = 'employee/employee_id_number_config/editview';

			$this->db->order_by('employee_id_number_config_type');
			$data['id_no_config_type'] = $this->db->get_where('employee_id_number_config_type',array("deleted"=>0));
			$data['id_no_config'] = $this->db->get_where('employee_id_number_config',array("deleted"=>0));
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
		if(IS_AJAX){
			if($this->user_access[$this->module_id]['edit'] == 1){
				$response->msg = 'Data has been successfully saved.';
				$response->msg_type = 'success';	
				$response->record_id = '';

				$this->db->from('employee_id_number_config');
				$this->db->truncate(); 

				$data = $this->_rebuild_array($_POST['id_number_config'], $employee_id);
				$this->db->insert_batch('employee_id_number_config', $data);	
				
				if( $this->db->_error_message() != "" ){
					$response->msg = $this->db->_error_message();
					$response->msg_type = 'error';
				}

				$result = $this->db->get('employee_id_number_config');
				if ($result && $result->num_rows() > 0){
					$str = '';
                    $ctr = 1;
                    $series = '';					
                    $check_series = false;
					foreach ($result->result() as $row) {
                        if ($row->employee_id_number_config_value != ''){
                            $str .= $row->employee_id_number_config_value;
                        }
                        if ($ctr < $result->num_rows() && $row->employee_id_number_config_value!= ''){
                            $str .= '-';   
                        }
                        $ctr++;

                        if ($row->employee_id_number_config_type_id == 5){
                        	$check_series = true;
                        	$series = $row->employee_id_number_config_value;
                        }  
					}

					if (!$check_series){
						$response->msg = "You haven't selected series number. 'Id No' to apply will applicable to all employees as the same.";
						$response->msg_type = 'attention';
					} 

					$this->db->from('employee_id_number_format');
					$this->db->truncate(); 					
					$this->db->insert('employee_id_number_format', array('employee_id_number_fomat'=>$str,"id_number_format_series"=>$series));		
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

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions
	/**
	 * Rearrange the array to a new array which can be used for insert_batch
	 *
	 * @param array $array
	 * @param int $key
	 *
	 * @return array
	 */
	private function _rebuild_array($array, $fkey = null) {
		if (!is_array($array)) {
			return array();
		}

		$new_array = array();

		$count = count(end($array));
		$index = 0;

		while ($count > $index) {
			foreach ($array as $key => $value) {
				$new_array[$index][$key] = $array[$key][$index];
				if (!is_null($fkey)) {
					$new_array[$index][$this->key_field] = $fkey;
				}
			}

			$index++;
		}

		return $new_array;
	}
	// END custom module funtions

}

/* End of file */
/* Location: system/application */