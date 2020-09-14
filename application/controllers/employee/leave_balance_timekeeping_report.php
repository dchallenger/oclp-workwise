<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class leave_balance_timekeeping_report extends MY_Controller
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
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about a particular ';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about ';	
    }

	// START - default module functions
	// default jqgrid controller method
	function index()
    {
    	$data['scripts'][] = multiselect_script();
		$data['scripts'][] = jqgrid_listview();	// load jqgrid js and default grid js
		$data['content'] = 'employee/leave_balance/listview';

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

		$data['department'] = $this->db->get('user_company_department')->result_array();

		if($this->user_access[$this->module_id]['post'] != 1) {
			$this->db->where('reporting_to', $this->userinfo['position_id']);
			$this->db->where('deleted', 0);
			$result	= $this->db->get('user_position');			
			if ($result){
				$subordinates = $result->num_rows();
			}
		}
		else{
			$subordinates = 1;
		}

		$data['w_subordinates'] = $subordinates;

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

    function populate_category()
    {
        $html = '';
        switch ($this->input->post('category_id')) {
            case 0:
                $html .= '';    
                break;
            case 1: // company
                $this->db->where('deleted', 0);
                $company = $this->db->get('user_company')->result_array();      
                $html .= '<select id="user_company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 2: // division
                $this->db->where('deleted', 0);
                $division = $this->db->get('user_company_division')->result_array();        
                $html .= '<select id="user_company_division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 3: // department
                $this->db->where('deleted', 0);
                $department = $this->db->get('user_company_department')->result_array();        
                $html .= '<select id="user_company_department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';               
                break;                                          
            case 4: // section
                $this->db->where('deleted', 0);
                $company = $this->db->get('user_section')->result_array();      
                $html .= '<select id="user_section" multiple="multiple" class="multi-select" style="width:400px;" name="section[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["section_id"].'">'.$company_record["section"].'</option>';
                    }
                $html .= '</select>';   
                break;   
            case 5: // level
                $this->db->where('deleted', 0);
                $employee_type = $this->db->get('employee_type')->result_array();       
                $html .= '<select id="employee_type" multiple="multiple" class="multi-select" style="width:400px;" name="employee_type[]">';
                    foreach($employee_type as $employee_type_record){
                        $html .= '<option value="'.$employee_type_record["employee_type_id"].'">'.$employee_type_record["employee_type"].'</option>';
                    }
                $html .= '</select>';   
                break;  
            case 6: // employment status
                $this->db->where('deleted', 0);
                $employment_status = $this->db->get('employment_status')->result_array();       
                $html .= '<select id="employment_status" multiple="multiple" class="multi-select" style="width:400px;" name="employment_status[]">';
                    foreach($employment_status as $employment_status_record){
                        $html .= '<option value="'.$employment_status_record["employment_status_id"].'">'.$employment_status_record["employment_status"].'</option>';
                    }
                $html .= '</select>';   
                break;                                 
            case 7: // employee
                $this->db->where('user.deleted', 0);
                $this->db->join('employee', 'employee.employee_id = user.employee_id');
                $employee = $this->db->get('user')->result_array();     
                $html .= '<select id="user" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                $html .= '</select>';   
                break;  
        }       

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
    }

	function get_employees()
	{
		if (IS_AJAX)
		{
			$html = '';
			if ($this->input->post('category_id') != 'null') {
                switch ($this->input->post('category')) {
                    case 0:
                        $html .= '';    
                        break;
                    case 1: // company
                        $where = 'user.company_id IN ('.$this->input->post('category_id').')';
                        break;
                    case 2: // division
                        $where = 'user.division_id IN ('.$this->input->post('category_id').')';
                        break;
                    case 3: // department
                        $where = 'user.department_id IN ('.$this->input->post('category_id').')';
                        break;  
                    case 4: // section
                        $where = 'user.section_id IN ('.$this->input->post('category_id').')';
                        break;                      
                    case 5: // level
                        $where = 'employee_type IN ('.$this->input->post('category_id').')';
                        break;
                    case 6: // employment status
                        $where = 'status_id IN ('.$this->input->post('category_id').')';
                        break;                                                                                                      
                }	
				$this->db->where($where);
                $this->db->where('user.deleted', 0);
                $this->db->join('employee','user.employee_id = employee.employee_id');
				$result = $this->db->get('user');		

                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';

                if ($result && $result->num_rows() > 0){
                    $employee = $result->result_array();
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["firstname"].'&nbsp;'.$employee_record["lastname"].'</option>';
                    }
                }
                
                $html .= '</select>';  
			}

            $data['html'] = $html;
    		$this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	

		}
		else
		{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		}

	}

	function get_category(){
		$html = '';
		$this->db->where('deleted',0);		
		switch ($this->input->post('category_id')) {
		    case 0:
                $html .= '';	
		        break;
		    case 1:
				$company = $this->db->get('user_company')->result_array();		
                $html .= '<select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">';
                    foreach($company as $company_record){
                        $html .= '<option value="'.$company_record["company_id"].'">'.$company_record["company"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 2:
				$division = $this->db->get('user_company_division')->result_array();		
                $html .= '<select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">';
                    foreach($division as $division_record){
                        $html .= '<option value="'.$division_record["division_id"].'">'.$division_record["division"].'</option>';
                    }
                $html .= '</select>';	
		        break;
		    case 3:
				$department = $this->db->get('user_company_department')->result_array();		
                $html .= '<select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">';
                    foreach($department as $department_record){
                        $html .= '<option value="'.$department_record["department_id"].'">'.$department_record["department"].'</option>';
                    }
                $html .= '</select>';				
		        break;
		    case 4:
				$user_section = $this->db->get('user_section')->result_array();		
                $html .= '<select id="section" multiple="multiple" class="multi-select" style="width:400px;" name="section[]">';
                    foreach($user_section as $user_section_record){
                        $html .= '<option value="'.$user_section_record["section_id"].'">'.$user_section_record["section"].'</option>';
                    }
                $html .= '</select>';	
		        break;		        
		    case 5:
		    	$employee = $this->db->query("SELECT
                                          *
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_leave_balance c
                                            ON a.employee_id = c.employee_id
                                        WHERE b.user_id IS NOT NULL 
                                        AND a.employee_id IS NOT NULL
                                        GROUP BY b.lastname, b.firstname ")->result_array();		
                $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
                    foreach($employee as $employee_record){
                        $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].'&nbsp;'.$employee_record["firstname"].'</option>';
                    }
                $html .= '</select>';	
		        break;	        	        		        		        		        
		}	

        $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);			
	}

	function get_employee_company() {
		$company_id = $this->input->post('company');
		$employee = $this->db->query("SELECT
                                          *
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_leave_balance c
                                            ON a.employee_id = c.employee_id
                                        WHERE b.company_id IN ({$company_id}) 
                                        AND b.user_id IS NOT NULL 
                                        AND a.employee_id IS NOT NULL
                                        GROUP BY b.lastname, b.firstname ")->result_array();

	    $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
            foreach($employee as $employee_record){
                $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].'&nbsp;'.$employee_record["firstname"].'</option>';
            }
        $html .= '</select>';

	    $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function get_employee_division() {
		$division_id = $this->input->post('division');
		$employee = $this->db->query("SELECT
                                          *
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_leave_balance c
                                            ON a.employee_id = c.employee_id
                                        WHERE b.division_id IN ({$division_id}) 
                                        AND b.user_id IS NOT NULL 
                                        AND a.employee_id IS NOT NULL
                                        GROUP BY b.lastname, b.firstname ")->result_array();

	    $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
            foreach($employee as $employee_record){
                $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].'&nbsp;'.$employee_record["firstname"].'</option>';
            }
        $html .= '</select>';

	    $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function get_employee_department() {
		$department_id = $this->input->post('department');
		$employee = $this->db->query("SELECT
                                          *
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_leave_balance c
                                            ON a.employee_id = c.employee_id
                                        WHERE b.department_id IN ({$department_id}) 
                                        AND b.user_id IS NOT NULL 
                                        AND a.employee_id IS NOT NULL
                                        GROUP BY b.lastname, b.firstname ")->result_array();

	    $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
            foreach($employee as $employee_record){
                $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].'&nbsp;'.$employee_record["firstname"].'</option>';
            }
        $html .= '</select>';

	    $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);	
	}

	function get_employee_section() {
		$section_id = $this->input->post('section');
		$employee = $this->db->query("SELECT
                                          *
                                        FROM {$this->db->dbprefix}employee a
                                          LEFT JOIN {$this->db->dbprefix}user b
                                            ON a.employee_id = b.employee_id
                                          LEFT JOIN {$this->db->dbprefix}employee_leave_balance c
                                            ON a.employee_id = c.employee_id
                                        WHERE b.section_id IN ({$section_id}) 
                                        AND b.user_id IS NOT NULL 
                                        AND a.employee_id IS NOT NULL
                                        GROUP BY b.lastname, b.firstname ")->result_array();

	    $html .= '<select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">';
            foreach($employee as $employee_record){
                $html .= '<option value="'.$employee_record["employee_id"].'">'.$employee_record["lastname"].'&nbsp;'.$employee_record["firstname"].'</option>';
            }
        $html .= '</select>';

	    $data['html'] = $html;
        $this->load->view($this->userinfo['rtheme'] . '/template/ajax', $data);
	}

	function export() {	
		$this->_excel_export();
	}

	// export called using ajax
	function _excel_export() {	
		ini_set('memory_limit', "512M");
		$this->load->library('PHPExcel');		
		$this->load->library('PHPExcel/IOFactory');

		$category_id = $this->input->post('category');
		$company_id = $this->input->post('company');
		$division_id = $this->input->post('division');
		$department_id = $this->input->post('department');
		$section_id = $this->input->post('section');
		$employee_id = $this->input->post('employee');
		$year_now = date("Y");

		if(!empty($employee_id)) {
			$employee_id = implode(',', $employee_id);
			$employee_qry = " a.employee_id IN ({$employee_id})";
		}
		
		$query = "SELECT 
						CONCAT(lastname,', ',firstname,' ', middleinitial,' ', aux ) AS employee_name, 
						c.vl,
						c.carried_vl,
						c.vl_used,
						c.el_used,	
						c.sl,
						c.carried_sl,
						c.sl_used,
						b.company_id, 
						d.company,
						b.division_id,
						e.division,
						b.department_id,
						f.department,
						b.section_id,
						g.section
					FROM {$this->db->dbprefix}employee a
						LEFT JOIN {$this->db->dbprefix}user b
							ON a.employee_id = b.employee_id
						LEFT JOIN {$this->db->dbprefix}employee_leave_balance c 
							ON a.employee_id = c.employee_id AND c.deleted = 0
						LEFT JOIN {$this->db->dbprefix}user_company d
							ON b.company_id = d.company_id
						LEFT JOIN {$this->db->dbprefix}user_company_division e
							ON b.division_id = e.division_id
						LEFT JOIN {$this->db->dbprefix}user_company_department f
							ON b.department_id = f.department_id
						LEFT JOIN {$this->db->dbprefix}user_section g
							ON b.section_id = g.section_id
					WHERE c.year = '{$year_now}' 
							AND b.user_id IS NOT NULL 
	                        AND a.employee_id IS NOT NULL  
	                        AND a.resigned = 0
	                        AND b.inactive = 0
	                        AND {$employee_qry}
	                ORDER BY b.lastname, b.firstname";
	                // dbug($query);
		$elb = $this->db->query($query);
		$html = '';
		if($elb && $elb->num_rows() > 0) {
			switch( $category_id ) {
	            case '1':
	            	$company_title = '';
	            	$cnt = 1;
					foreach ($elb->result() as $key => $value) {
						if($company_title == '') {
							$html .= '<table width="100%">
										<tr>
											<td colspan="11" style="text-align:left;border:1px black solid;">
												<b>'.ucwords($value->company).'</b>
											</td>
										</tr>
									  </table>';
						} else {
							if($company_title != $value->company) {
								$html .= '<table width="100%">
										<tr><td colspan="11" style="height:10px;">&nbsp;</td></tr>
										<tr>
											<td colspan="11" style="text-align:left;border:1px black solid;">
												<b>'.ucwords($value->company).'</b>
											</td>
										</tr>
									  </table>';
							}
						}

						$html .= '<table width="100%">
										<tr>
											<td style="text-align:center;width:100px;border:1px black solid;">'.$cnt.'</td>
											<td style="text-align:left;width:200px;;border:1px black solid;">'.ucwords($value->employee_name).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->el_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->vl+$value->carried_vl) - ($value->vl_used+$value->el_used)).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->sl+$value->carried_sl) - $value->sl_used).'</td>
										</tr>
									  </table>';
						$cnt++;
						$company_title = $value->company;
					}
	                break;
	            case '2':
					$division_title = '';
	            	$cnt = 1;
					foreach ($elb->result() as $key => $value) {
						if($division_title == '') {
							$html .= '<table width="100%">
										<tr>
											<td colspan="11" style="text-align:left;border:1px black solid;">
												<b>'.ucwords($value->division).'</b>
											</td>
										</tr>
									  </table>';
						} else {
							if($division_title != $value->division) {
								$html .= '<table width="100%">
										<tr><td colspan="11" style="height:10px;">&nbsp;</td></tr>
										<tr>
											<td colspan="11" style="text-align:left;border:1px black solid;">
												<b>'.ucwords($value->division).'</b>
											</td>
										</tr>
									  </table>';
							}
						}

						$html .= '<table width="100%">
										<tr>
											<td style="text-align:center;width:100px;border:1px black solid;">'.$cnt.'</td>
											<td style="text-align:left;width:200px;;border:1px black solid;">'.ucwords($value->employee_name).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->el_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->vl+$value->carried_vl) - ($value->vl_used+$value->el_used)).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->sl+$value->carried_sl) - $value->sl_used).'</td>
										</tr>
									  </table>';
						$cnt++;
						$division_title = $value->division;
					}
	                break;
	            case '3':
					$department_title = '';
	            	$cnt = 1;
					foreach ($elb->result() as $key => $value) {
						if($department_title == '') {
							$html .= '<table width="100%">
										<tr>
											<td colspan="11" style="text-align:left;border:1px black solid;">
												<b>'.ucwords($value->department).'</b>
											</td>
										</tr>
									  </table>';
						} else {
							if($department_title != $value->department) {
								$html .= '<table width="100%">
										<tr><td colspan="11" style="height:10px;">&nbsp;</td></tr>
										<tr>
											<td colspan="11" style="text-align:left;border:1px black solid;">
												<b>'.ucwords($value->department).'</b>
											</td>
										</tr>
									  </table>';
							}
						}

						$html .= '<table width="100%">
										<tr>
											<td style="text-align:center;width:100px;border:1px black solid;">'.$cnt.'</td>
											<td style="text-align:left;width:200px;;border:1px black solid;">'.ucwords($value->employee_name).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->el_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->vl+$value->carried_vl) - ($value->vl_used+$value->el_used)).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->sl+$value->carried_sl) - $value->sl_used).'</td>
										</tr>
									  </table>';
						$cnt++;
						$department_title = $value->department;
					}   
	                break;
	            case '4':
					$section_title = '';
	            	$cnt = 1;
					foreach ($elb->result() as $key => $value) {
						if($section_title == '') {
							$html .= '<table width="100%">
										<tr>
											<td colspan="11" style="text-align:left;border:1px black solid;">
												<b>'.ucwords($value->section).'</b>
											</td>
										</tr>
									  </table>';
						} else {
							if($section_title != $value->section) {
								$html .= '<table width="100%">
										<tr><td colspan="11" style="height:10px;">&nbsp;</td></tr>
										<tr>
											<td colspan="11" style="text-align:left;border:1px black solid;">
												<b>'.ucwords($value->section).'</b>
											</td>
										</tr>
									  </table>';
							}
						}

						$html .= '<table width="100%">
										<tr>
											<td style="text-align:center;width:100px;border:1px black solid;">'.$cnt.'</td>
											<td style="text-align:left;width:200px;;border:1px black solid;">'.ucwords($value->employee_name).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->el_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->vl+$value->carried_vl) - ($value->vl_used+$value->el_used)).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->sl+$value->carried_sl) - $value->sl_used).'</td>
										</tr>
									  </table>';
						$cnt++;
						$section_title = $value->section;
					}   
	                break;
	            case '5':
	            	$cnt = 1;
					foreach ($elb->result() as $key => $value) {
						
						$html .= '<table width="100%">
										<tr>
											<td style="text-align:center;width:100px;border:1px black solid;">'.$cnt.'</td>
											<td style="text-align:left;width:200px;;border:1px black solid;">'.ucwords($value->employee_name).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->el_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->vl+$value->carried_vl) - ($value->vl_used+$value->el_used)).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->sl+$value->carried_sl) - $value->sl_used).'</td>
										</tr>
									  </table>';
						$cnt++;
					}  
	                break;
	            default:
	            	$cnt = 1;
					foreach ($elb->result() as $key => $value) {
						
						$html .= '<table width="100%">
										<tr>
											<td style="text-align:center;width:100px;border:1px black solid;">'.$cnt.'</td>
											<td style="text-align:left;width:200px;;border:1px black solid;">'.ucwords($value->employee_name).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_vl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->vl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->el_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->vl+$value->carried_vl) - ($value->vl_used+$value->el_used)).'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->carried_sl.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.$value->sl_used.'</td>
											<td style="text-align:right;width:100px;border:1px black solid;">'.(($value->sl+$value->carried_sl) - $value->sl_used).'</td>
										</tr>
									  </table>';
						$cnt++;
					} 	            
	            	break;       
	        }
		}
		$params['header_title'] = '<table>
									<tr>
										<td colspan="11" style="text-align:center;">
											<b>Leave Monitoring Report ( AS of <u>  '.date('F d, Y').'  </u> )</b>
										</td>
									</tr>
									<tr><td colspan="11" style="height:10px;">&nbsp;</td></tr>
									</table>
									<table width="100%">
									<tr>
										<td style="text-align:center;width:200px;border:1px black solid;" colspan="2"><strong>NAME</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>VL Earned</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>Previous YR VL</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>VL Used</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>EL Used</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>VL Balance</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>SL Earned</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>Previous YR SL</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>SL Used</strong></td>
										<td style="text-align:center;width:100px;border:1px black solid;"><strong>SL Balance</strong></td>
									</tr>
									<tr><td colspan="11" style="height:10px;">&nbsp;</td></tr>
								   </table>';
		// dbug($html);
		$params['table'] = $html;
		$this->output->set_header("Content-type: application/vnd.ms-excel");
        $this->output->set_header("Content-Disposition: inline; filename=employee_leave_balance_".date('Ymd-hms').".xls");
        $this->load->view('dtr/ot_summary/overtime_report_excel', $params);  
	}		
	// END custom module funtions
	
}

/* End of file */
/* Location: system/application */
?>