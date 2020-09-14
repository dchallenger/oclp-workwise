<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee_ecf extends MY_Controller
{
	function __construct()
    {
        parent::__construct();

		//set module variable values
		$this->grid_grouping = "";
		$this->related_table = array(); //table => field format

		$this->listview_title = '';
		$this->listview_description = 'This module lists employee health information.';
		$this->jqgrid_title = "List";
		$this->detailview_title = ' Info';
		$this->detailview_description = 'This page shows detailed information about an employee health information.';
		$this->editview_title = ' Add/Edit';
		$this->editview_description = 'This page allows saving/editing information about an employee health information.';
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

		//$data['buttons'] = $this->module_link . '/detail-buttons';	

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

			//$data['buttons'] = $this->module_link . '/edit-buttons';

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
		
		$affected_dependents = $this->input->post('ecf_affected_dependents');

		// this part will remove the dependent from being an ecf dependent
		if($this->input->post('ecf_fire') == 1) {
			foreach($affected_dependents as $dependent){
				$change_me = $this->db->get_where('employee_family', array("record_id" => $dependent, "deleted" => 0))->row();

				$this->db->set('occupation', $change_me->occupation." (DECEASED)");
				$this->db->set('ecf_dependent', 0);
				$this->db->where('record_id', $dependent);
				$this->db->update('employee_family');
			}

			/* address_ecf_dependent field not found
			$this->db->set('address_ecf_dependent', 0);
			$this->db->where('employee_ecf_id', $this->key_field_val);
			$this->db->update('employee_ecf');
			*/
		} else {
			/* address_ecf_dependent field not found
			$this->db->set('address_ecf_dependent', 0);
			$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->update('employee');
			*/

			$this->db->set('ecf_affected_dependents', ' ');
			$this->db->where('employee_ecf_id', $this->key_field_val);
			$this->db->update('employee_ecf');
		}
		// this part will remove the dependent from being an ecf dependent

		// this part will list the employees who contributed (coordinate with harold to know if needed on payroll)

			// $pieces = explode(',', $this->input->post('contributors'));
			// $table = '';
			// foreach($pieces as $contributors) {
			// 	$list_contributor = array(
			// 		'employee_ecf_id' => $this->key_field_val,
			// 		'employee_affected' => $this->input->post('employee_id'),
			// 		'employee_id_contributor' => $contributors,
			// 		'date_effective' => $this->input->post('ecf_payment_date'),
			// 		'amount_deducted' => $this->input->post('total_amount'),
			// 		'deducted_on_payroll' => 0, 
			// 		);
			// 	$this->db->insert($table, $list_contributor);
			// }

		// this part will list the employees who contributed (coordinate with harold to know if needed on payroll)

		// code for changes or delete this will remove the tags
			//UPDATE table SET fieldname=REPLACE(fieldname,'(DECEASED)','')
		// code for changes or delete this will remove the tags

		//for count used
			$count_used = $this->db->get_where('employee_ecf', array('employee_id' => $this->input->post('employee_id')));
			$this->db->update('employee_ecf', array("count_used" => $count_used->num_rows()), array('employee_ecf_id' => $this->key_field_val));
		//for count used

		//additional module save routine here

	}

	function delete()
	{
		parent::delete();

		//additional module delete routine here
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
        
        if ($this->user_access[$this->module_id]['print']) {
            $actions .= '<a class="icon-button icon-16-export" record_id="'.$record['employee_ecf_id'].'" module_link="'.$module_link.'" href="javascript:void(0)" tooltip="Export List" original-title=""></a>';
        }        
        
        // if ($this->user_access[$this->module_id]['edit'] && $record['t2employee_update_status'] == 'For Approval') {
        //     $actions .= '<a class="icon-button icon-16-edit" tooltip="Edit" href="javascript:void(0)" module_link="'.$module_link.'" ></a>';
        // }

        // if ($this->user_access[$this->module_id]['delete'] && $record['t2employee_update_status'] == 'For Approval') {
        //     $actions .= '<a class="icon-button icon-16-delete delete-single" tooltip="Delete" container="'.$container.'" module_link="'.$module_link.'" href="javascript:void(0)"></a>';
        // }

        $actions .= '</span>';

		return $actions;
	}

	// END - default module functions

	// START custom module funtions
	function after_ajax_save()
	{
		//Updated By column does not exist
		/*
		if ($this->get_msg_type() == 'success') {
			$data['updated_by']   = $this->userinfo['user_id'];
			$data['updated_date'] = date('Y-m-d H:i:s');

			if ($this->input->post('record_id') == '-1') {
				$data['created_by']   = $this->userinfo['user_id'];
				$data['created_date'] = date('Y-m-d H:i:s');
			}

			$this->db->where($this->key_field, $this->key_field_val);
			$this->db->update($this->module_table, $data);
		}
		*/
		parent::after_ajax_save();
	}


	// END custom module funtions


	function call_dependents() {
		if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('*');
			$this->db->where('employee_id', $this->input->post('employee_id'));
			$this->db->where('ecf_dependent', 1);
			$this->db->where('deleted', 0);
			$this->db->not_like('occupation', 'deceased');

			$employee = $this->db->get('employee_family');

			$this->db->select('employee.*, pres.city AS present_city, perm.city AS permanent_city');
			$this->db->where('employee.employee_id', $this->input->post('employee_id'));
			$this->db->where('employee.deleted', 0);
			$this->db->join('cities pres','employee.pres_city = pres.city_id','left');
			$this->db->join('cities perm','employee.perm_city = perm.city_id','left');
			$house = $this->db->get('employee');

			if ($employee->num_rows() == 0 && $house->num_rows() == 0) {
				$response->msg_type = 'error';
				$response->msg 		= 'No specified dependent';
			} else {
				$response->msg_type = 'success';
				$response->data = $employee->result_array();
				if($house->num_rows() > 0)
					$response->house = $house->row_array();
				else
					$response->house = false;
			}			
		}
		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_ecf_members() {
	if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('ecf, employee_id');
			$this->db->where('ecf', 1);
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee');

			// if (!$employee || $employee->num_rows() == 0) {
			// 	//$response->msg_type = 'error';
			// 	//$response->msg 		= 'Family not found.';
			// } else {
				$response->msg_type = 'success';

				$response->data = $employee->result_array();
			// }			
		}

		$this->load->view('template/ajax', array('json' => $response));
	}

	function get_ecf_serialize() {
	if (!IS_AJAX) {
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url() . $this->module_link);
		} else if ($this->user_access[$this->module_id]['view'] != 1) {
			$response->msg_type = 'error';
			$response->msg 		= 'You do not have access to the selected module.';
		} else {
			$this->db->select('ecf');
			$this->db->where('ecf', 1);
			$this->db->where('deleted', 0);

			$employee = $this->db->get('employee');

			// if (!$employee || $employee->num_rows() == 0) {
			// 	//$response->msg_type = 'error';
			// 	//$response->msg 		= 'Family not found.';
			// } else {
			$response->msg_type = 'success';

			$response->data = $employee->result_array();
			$responde=serialize($response->data);
			// }			
		}
		$this->load->view('template/ajax', array('json' => $responde));
	}

	function export_list(){
		// if( $this->user_access[$this->module_id]['edit'] == 1 ){
			if( !isset($_POST['record_id']) && $this->uri->rsegment(3) ) $_POST['record_id'] = $this->uri->rsegment(3);
			$record_id = $this->input->post('record_id');
			
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

			//Default column width
			$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(10);

			//Initialize style
			$styleArray = array(
				'font' => array(
					'bold' => true,
				),			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);

			$stylenumberingArray = array(
				'font' => array(
					'bold' => false,
				),			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			);

			$alpha_ctr=5;
			for($ctr=1; $ctr<5; $ctr++){
				$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);
			}
			// used for regular hours
			//$objPHPExcel->getActiveSheet()->mergeCells($alphabet[0].$ctr.':'.$alphabet[$alpha_ctr - 1].$ctr);		


			//$activeSheet->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
			$activeSheet->getHeaderFooter()->setOddFooter('&L&BPrinted By:'.$userinfo->firstname.' '.$userinfo->lastname.' &RPage &P of &N');

			$this->db->join('user', 'employee_ecf.employee_id = user.employee_id', 'left');
			$this->db->where('employee_ecf.employee_ecf_id', $record_id);
			$employee = $this->db->get('employee_ecf')->row();

			$pieces = explode(',',$employee->ecf_affected_dependents);
			$show_dependents = array();
			foreach($pieces as $ecf_dependent)
			{
				$dependent_name = $this->db->get_where('employee_family',array('record_id' => $ecf_dependent))->row();
			 	$show_dependents[] = $dependent_name->name;
			}
			$show_dependent = implode(', ',$show_dependents);

			// $this->db->get_where('employee_ecf' array('employee_ecf_id' => $record_id))->row;
			$activeSheet->setCellValue('A1', 'Employee Contribution Fund List');
			$activeSheet->setCellValue('A2', $employee->firstname." ".$employee->middlename." ".$employee->lastname);
			// $activeSheet->setCellValue('A3', 'For Dependent/s : ', $employee);
			$activeSheet->setCellValue('A3', 'Effective : '. date($this->config->item('display_date_format'), strtotime($employee->ecf_payment_date)));
			$activeSheet->setCellValue('A4', 'Dependent : '. ($employee->ecf_affected_dependents == null || $employee->ecf_affected_dependents == ' ' ? $employee->ecf_house_dependent : $show_dependent));
			// $activeSheet->setCellValue('A4', 'Dependent/s '. );

			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArray);
			// $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArray);

			// $activeSheet->setCellValue($xcoor . $line, 'GRAND TOTAL ');

			
			$pieces = explode(',',$employee->contributors);

			// $this->db->join('user','employee.employee_id = user.employee_id', 'left');
			// $this->db->where('ecf', 1);
			$this->db->where_in('employee_id', $pieces);
			$this->db->where('deleted', 0);
			// $this->db->where('employee.employee_id <>', $employee->employee_id);
			// $this->db->order_by('employee.firstname');
			$employee_contributors = $this->db->get('user')->result();


			$line = 8;
			$ctr = 1;
			$activeSheet->setCellValue('A7', 'Number');
			$activeSheet->setCellValue('B7', 'Employee Name');
			$activeSheet->setCellValue('E7', 'Amount');


			$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('E7')->applyFromArray($styleArray);

			foreach($employee_contributors as $employee_contributor)
			{
				$activeSheet->setCellValue('A'.$line, $ctr);
				$objPHPExcel->getActiveSheet()->getStyle('A'.$line)->applyFromArray($stylenumberingArray);
				$activeSheet->setCellValue('B'.$line, $employee_contributor->firstname." ".$employee_contributor->middlename." ".$employee_contributor->lastname);
				$activeSheet->setCellValue('E'.$line, $employee->total_amount);
				// $employee_contributor->total_amount=$employee_contributor->total_amount;
				$line++;
				$ctr++;
			}
			$activeSheet->setCellValue('D'.$line, 'TOTAL');
			$activeSheet->setCellValue('E'.$line, ($ctr-1)*$employee->total_amount);
			$objPHPExcel->getActiveSheet()->getStyle('D'.$line)->applyFromArray($styleArray);

			$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

			$filename = ($employee->firstname != '' || $employee->firstname != null ? str_replace(' ', '_', trim($employee->firstname)) : '_');
			$filename .= '_'.($employee->middlename != '' || $employee->firstname != null ? str_replace(' ', '_', trim($employee->middlename)) : '_');
			$filename .= '_'.($employee->lastname != '' || $employee->lastname != null ? str_replace(' ', '_', trim($employee->lastname)) : '_');
			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment;filename='.date('Y-m-d') . ' ECF_'.$filename.'.xls');
			header('Content-Transfer-Encoding: binary');
			
			$objWriter->save('php://output');	
		// }
	}
}

/* End of file */
/* Location: system/application */