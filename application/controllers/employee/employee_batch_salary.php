<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_batch_salary extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists section module.';
		$this->jqgrid_title = " List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a Employee Batch Salary module';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about a Employee Batch Salary module';
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

			//other views to load
			$data['views'] = array();
			$data['views_outside_record_form'] = array();
			// $data['buttons'] = 'employees/employee_batch_salary/edit-buttons';
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


	function after_ajax_save()
	{
			
		$this->db->where($this->key_field, $this->key_field_val);
		$result = $this->db->get('employee_batch_salary')->row();

		$employees = explode(',', $this->input->post('employee_id'));
		// $employees = explode(',', $result->employee_id);
		
		$salary = $this->input->post('salary');
		$current_salary = $this->input->post('current_salary');
		

		$this->db->where('batch_salary_id', $this->key_field_val);
		$record = $this->db->get('employee_salary_adjustment');

		if ($record && $record->num_rows() > 0) {
			$this->db->where('batch_salary_id', $this->key_field_val);
			$this->db->delete('employee_salary_adjustment');
		}
		
		foreach ($employees as $employee) {
			
			$emp['salary'] = $this->encrypt->encode($salary[$employee]);
			$emp['current_salary'] = $this->encrypt->encode($current_salary[$employee]);

			$this->db->where('employee_id', $employee);
		

			// if ($record && $record->num_rows() > 0) {
			// 	$rec = $record->row();

			// 	// if ($rec->employee_id === $employee ) {
			// 		$this->db->where('employee_id', $employee);
			// 		$this->db->where('batch_salary_id', $this->key_field_val);
			// 		$this->db->update('employee_salary_adjustment', $emp);
			// 	// }else{
			// 		// $emp['employee_id'] = $employee;
			// 		// $emp['batch_salary_id'] = $this->key_field_val;
			// 		// $this->db->insert('employee_salary_adjustment', $emp);
			// 	// }
					
			// }else{
				$emp['employee_id'] = $employee;
				$emp['batch_salary_id'] = $this->key_field_val;
				$this->db->insert('employee_salary_adjustment', $emp);
			
			// }

		}
		
		parent::after_ajax_save();                                                                                                     
		
	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
	}
	// END - default module functions

	// START custom module funtions

	function get_department()
	{

		if (IS_AJAX) {
			$company_id = $this->input->post('company_id');
			
			$options = '<option value=" "> </option>';

			if ($company_id > 0) {
				$this->db->where('company_id', $company_id);

				$result   = $this->db->get('user_company_department');
				// $response = $this->_get_default('department_id');
				// dbug($this->db->last_query());

				if ($result->num_rows() > 0) {
					$departments = $result->result();

					foreach ($departments as $department) {
						$options .= '<option value="'.$department->department_id.'">'.$department->department.'</option>';						
					}

				}

				$response['department'] = $options;

			} else {
				$response = array();
			}

			$data['json'] = $response;

			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
			// return;
		}else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}
	}

	function get_employees()
	{

		if (IS_AJAX)
		{
			$department = $this->input->post('department');
			$record_id = $this->input->post('record_id');

			
			$options = '';
			$this->db->where('department_id', $department);
			$this->db->join('employee', 'employee.employee_id = user.employee_id');
			$this->db->order_by('firstname,lastname', 'ASC');
			$result = $this->db->get('user');
			

			if ($result->num_rows() > 0) {
				$employee = $result->result();
				
				foreach ($employee as $emp) {
					$options .= '<option value="'.$emp->employee_id.'">'.$emp->firstname." ".$emp->middleinitial." ".$emp->lastname. " ".$emp->aux.'</option>';		

					if ($record_id != -1) {
						$this->db->where('batch_salary_id', $record_id);
						$this->db->where('employee_id', $emp->employee_id);
						$rec = $this->db->get('employee_salary_adjustment')->row();
						
						if ($rec->employee_id == $emp->employee_id) {
							$response['employee_id'][] = $emp->employee_id;		
						}
					}
				}

				$response['result'] = $options;

			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

		
	}


	function get_employee_info()
	{
		if (IS_AJAX)
		{

			$employees = $this->input->post('employee');
			$record_id = $this->input->post('record_id');
			$emp_id = array();

			$where = "employee.employee_id IN (".$employees.")";

			$this->db->where($where);
			$this->db->join('employee', 'employee.employee_id = user.employee_id');
			$this->db->join('employee_payroll', 'employee.employee_id = employee_payroll.employee_id');
			$this->db->order_by('firstname,lastname', 'ASC');
			$result = $this->db->get('user');
			

			$tr = " ";
			if ($result && $result->num_rows() > 0) {

				$employee = $result->result();
				
				foreach ($employee as $emp) {

					if ($record_id != '-1') {
						
						$this->db->where('batch_salary_id', $record_id);
						$this->db->where('employee_id', $emp->employee_id);
						$rec = $this->db->get('employee_salary_adjustment')->row();
					}

					$tr .= "<tr>";
					$tr .= "<td>".$emp->id_number."</td>
							<td>".$emp->firstname." ".$emp->middleinitial." ".$emp->lastname. " ".$emp->aux."</td>";
					
					if ($rec->employee_id == $emp->employee_id) {
						$tr .= "<td style='width:200px'><input type='hidden' class='input-text current_salary' name='current_salary[".$emp->employee_id."]' value='".$this->encrypt->decode($rec->current_salary)."'>".$this->encrypt->decode($rec->current_salary)."</td>";
						$tr .= "<td style='width:100px'><input type='text' class='input-text new_salary' name='salary[".$emp->employee_id."]' value='".$this->encrypt->decode($rec->salary)."'></td>";

					}else{
						$tr .= "<td style='width:200px'><input type='hidden' class='input-text current_salary' name='current_salary[".$emp->employee_id."]' value='".$this->encrypt->decode($emp->salary)."'>".$this->encrypt->decode($emp->salary)."</td>";
						$tr .= "<td style='width:100px'><input type='text' class='input-text new_salary' name='salary[".$emp->employee_id."]'></td>
							</tr>";
					}
					
				}
				
			}elseif ($record_id != '-1' && $employees == 'undefined') {

				$this->db->where('batch_salary_id', $record_id);
				$this->db->join('employee_salary_adjustment', 'employee_salary_adjustment.employee_id = user.employee_id');
				$this->db->join('employee', 'employee.employee_id = user.employee_id');
				$this->db->order_by('lastname');
				$employees = $this->db->get('user');
				
				foreach ($employees->result() as $emp) {

					$tr .= "<tr>";
					$tr .= "<td>".$emp->id_number."</td>
							<td>".$emp->firstname." ".$emp->middleinitial." ".$emp->lastname. " ".$emp->aux."</td>";
					

					$tr .= "<td style='width:200px'><input type='hidden' class='input-text current_salary' name='current_salary[".$emp->employee_id."]' value='".$this->encrypt->decode($emp->current_salary)."'>".$this->encrypt->decode($emp->current_salary)."</td>";
					$tr .= "<td style='width:100px'>".$this->encrypt->decode($emp->salary)."</td>";

				}

			}
		
			$response['result'] = $tr;
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function get_status()
	{
		if (IS_AJAX)
		{
			$record_id = $this->input->post('record_id');

			$rec = $this->db->get_where('employee_batch_salary', array('batch_salary_id' => $record_id))->row();
			
			$response['status'] = $rec->status;
			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}


	function _default_grid_actions( $module_link = "",  $container = "", $record = array() )
	{

		$rec = $this->db->get_where('employee_batch_salary', array('batch_salary_id' => $record['batch_salary_id']))->row();

		// set default
		if($module_link == "") $module_link = $this->module_link;
		if($container == "") $container = "jqgridcontainer";

		$actions = '<span class="icon-group">';
                
        if ($this->user_access[$this->module_id]['view']) {
            $actions .= '<a class="icon-button icon-16-info" module_link="'.$module_link.'" tooltip="View" href="javascript:void(0)"></a>';
        }
        
		if ( $this->user_access[$this->module_id]['edit'] && $rec->status == 0 ) {
            $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        } 
				
        if ($this->user_access[$this->module_id]['print'] && method_exists($this, 'print_record')) {
            $actions .= '<a class="icon-button icon-16-print print-record" tooltip="Print" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }        
        
        if ($this->user_access[$this->module_id]['delete'] && $rec->status == 0) {
            $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        }

        $actions .= '</span>';

		return $actions;
	}

	// END custom module funtions

}

/* End of file */
/* Location: system/application */