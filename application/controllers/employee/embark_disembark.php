<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class embark_disembark extends MY_Controller
{
	function __construct(){
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format	
		
		$this->listview_title = 'Vessel';
		$this->listview_description = 'This module lists all defined vessels.';
		$this->jqgrid_title = "Vessel List";
		$this->detailview_title = 'Vessels Info';
		$this->detailview_description = 'This page shows detailed information about a particular vessels.';
		$this->editview_title = 'Vessel Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about vessels.';
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {

		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js	
		$data['scripts'][] = multiselect_script();
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
		$data['scripts'][] = multiselect_script();

		
		//other views to load
		$data['views'] = array();
		$data['buttons'] = 'template/goback-detail-buttons';
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
		$data['scripts'][] ='<script type="text/javascript" src="'.base_url().'lib/js/editview.js"></script>';
		$data['scripts'][] = '<script type="text/javascript" src="' . base_url() . 'lib/jquery/jquery.maskedinput-1.3.min.js"></script>';
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
		
	}	

	function delete()
	{
		parent::delete();
		
		//additional module delete routine here
	}
	// END - default module functions
	
	function _default_grid_buttons( $module_link = "", $related_field = "", $related_field_value = "", $addtext = "", $deltext = "", $container = "" ){
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$buttons = "<div class='icon-label-group'>";                    
        $buttons .= "</div>";
                
		return $buttons;
	}	

	function _default_grid_actions( $module_link = "",  $container = "", $record = array() ){
		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
             $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] ) {
			$actions .= '<a class="icon-button icon-16-embark" tooltip="Embark Employee" container="'.$container.'" module_link="'.$module_link.'" href="javascript:embark('.$record['vessel_id'].')"></a>';
            $actions .= '<a class="icon-button icon-16-disembark" tooltip="Disembark Employee" container="'.$container.'" module_link="'.$module_link.'" href="javascript:disembark('.$record['vessel_id'].')"></a>';
        } 
        
        $actions .= '</span>';

		return $actions;
	}

	function get_embark_form(){
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
		
		if( !$this->user_access[$this->module_id]['edit'] ){
			$response->msg = "You do not have enough privilege to execute the requested action.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}	

		$this->vessel_id = $this->input->post('vessel_id');
		$response->embark_form = $this->load->view( $this->userinfo['rtheme'] . '/' . $this->module_link . '/embark_form', '', true );

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_disembark_form(){
		
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}
		
		if( !$this->user_access[$this->module_id]['edit'] ){
			$response->msg = "You do not have enough privilege to execute the requested action.<br/>Please contact the System Administrator.";
			$response->msg_type = "attention";

			$this->load->view('template/ajax', array('json' => $response));
			return;
		}	

		$this->vessel_id = $this->input->post('vessel_id');
		$response->embark_edit = $this->load->view( $this->userinfo['rtheme'] . '/' . $this->module_link . '/embark_edit', '', true );

		$this->load->view('template/ajax', array('json' => $response));
	}

	function save_embark(){
		/* Insert new transaction : EMBARK */
		if(!IS_AJAX){
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);	
		}

		if( $this->user_access[$this->module_id]['edit'] ){
			
			if($this->input->post('employee_id') != '' && $this->input->post('date_embark') != ''){

			$vessel_id = $this->input->post('vessel_id');
			$date_embark = date("Y-m-d H:i:s", strtotime($this->input->post('date_embark')));
			$report_no = $this->input->post('report_no');
			$embark_reason = $this->input->post('embark_reason');
			$embark_remarks = $this->input->post('embark_remarks');
			
			$employee = explode(',', $this->input->post('employee_id'));
			reset($employee);
			foreach ($employee as $row => $employee_id) {

				$qry = "SELECT embark_count FROM {$this->db->dbprefix}vessel WHERE vessel_id = $vessel_id";
				$e_cnt = $this->db->query($qry)->row();
				$e_cnt = $e_cnt->embark_count + 1;
				
				$this->db->update('vessel',array('embark_count' => $e_cnt), array('deleted' => 0, 'vessel_id' => $vessel_id));

				$this->db->insert('employee_vessel_history',
					array(
						'vessel_id' => $vessel_id, 'employee_id' => $employee_id,
						'date_embark' => $date_embark,'embark_reason' => $embark_reason, 'embark_remarks' => $embark_remarks
						)
					);

				$this->db->insert('employee_vessel_embark_disembark_detail',
					array(
						'vessel_id' => $vessel_id, 'employee_id' => $employee_id,
						'date_embark' => $date_embark,'embark_reason' => $embark_reason, 'embark_remarks' => $embark_remarks
						)
					);
			
				$this->db->update('employee', array('vessel_id' => $vessel_id),array('deleted' => 0,'employee_id' => $employee_id));
				}
				$response->msg = 'Employee successfully embark.';
				$response->msg_type = 'success';
			}
			else{
				$response->msg = 'Kindly check the required field.';
				$response->msg_type = 'error';
			}
		}
		else{
			$response->msg = 'You dont have sufficient privilege, please contact the System Administrator.';
			$response->msg_type = 'error';
		}

		$data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		
	}

	function save_disembark(){
		
		/* DISEMBARK EMPLOYEE from the LIST */
		if(!IS_AJAX){
		$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
		redirect(base_url() . $this->module_link);	
		}
		//disembark save
		if( $this->user_access[$this->module_id]['edit'] ){
			
			if($this->input->post('date_disembark') != ''){

			$employees = $this->input->post('employee_id');
			$disembark_cb = $this->input->post('disembark_cb');
			$vessel_id = $this->input->post('vessel_id');
			$disembark_reason = $this->input->post('d_reason'); 
			$disembark_remarks = $this->input->post('d_remarks');
			$date_disembark = date("Y-m-d H:i:s", strtotime($this->input->post('date_disembark')));
			$date_embark = date("Y-m-d H:i:s", strtotime($this->input->post('date_embark')));


				$count_a = 0;
				foreach($employees as $index => $employee){
					if( $disembark_cb && isset($disembark_cb[$index]) ){
						
						$this->db->update('employee', 
								array('vessel_id' => NULL),
								array('employee_id' => $employee)
							);	

						// insert vessel history
						$this->db->insert('employee_vessel_history',
							array(
								'vessel_id' => $vessel_id, 'date_disembark' => $date_disembark, 
								'disembark_reason' => $disembark_reason, 'disembark_remarks' => $disembark_remarks, 'employee_id' => $employee
								)
							);

						$this->db->update('employee_vessel_embark_disembark_detail',
							array('date_disembark' => $date_disembark, 'disembark_reason' => $disembark_reason, 'disembark_remarks' => $disembark_remarks),
							array('vessel_id' => $vessel_id, 'employee_id' => $employee, 'date_embark' => $date_embark)
							);					

						$qry = "SELECT embark_count, disembark_count FROM {$this->db->dbprefix}vessel WHERE vessel_id = {$vessel_id}";
						$cnt = $this->db->query($qry)->row();
						$e_cnt = $cnt->embark_count - 1;
						$d_cnt = $cnt->disembark_count + 1;

						$this->db->update('vessel',array('embark_count' => $e_cnt, 'disembark_count' => $d_cnt), array('deleted' => 0, 'vessel_id' => $vessel_id));
					}
				}
				$response->msg = 'Employee successfully disembark.';
				$response->msg_type = 'success';
			}
			else{
				$response->msg = 'Kindly check the required field.';
				$response->msg_type = 'error';
			}
		}
		else{
			$response->msg = 'You dont have sufficient privilege, please contact the System Administrator.';
			$response->msg_type = 'error';
		}

		$data['json'] = $response;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);		
	}

}

/* End of file */
/* Location: system/application */

?>


