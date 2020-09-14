<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Quickclaim_received extends CI_Controller
{
	function __construct()
    {
        parent::__construct();				
		$this->config->set_item('meta', $this->hdicore->_get_meta());
    }

    function index()
    {
    	show_404();
    }

    function print_coe()
	{


		$user = $this->session->userdata('user');
	
		if ($user && $user->user_id > 0) {
			$this->load->library('pdf');
			$this->load->model('template');

			$this->db->where('code', 'deed_of_release');
			$this->db->where('deleted', 0);
			
			$template = $this->db->get('template')->row();			

			$this->db->select('CONCAT(firstname, " ", lastname) name, user_company.address AS company_address,company, amount_final_pay amount, pres_address1, pres_address2, pres_city, pres_province', false);
			$this->db->where('user.user_id', $this->input->post('employee_id'));
			$this->db->join('employee', 'employee.employee_id = user.user_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('employee_clearance ec', 'ec.employee_id = user.user_id', 'left');
			$this->db->join('employee_clearance_form ecf', 'ecf.employee_clearance_id = ec.employee_clearance_id', 'left');			

			$result = $this->db->get('user');
	
			$vars = $result->row_array();			
			$vars['address'] = implode(' ', array($vars['pres_address1'], $vars['pres_address2'], $vars['pres_city'], $vars['pres_province']));

			if (trim($vars['address']) == '') {
				$vars['address'] = '______';
			}


			$html = $this->template->prep_message($template->body, $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Deed of Release, Waiver, and Quitclaim - ' . $vars['name'] . '.pdf', 'D');			

			exit();
		}

		show_404();

	}

	function print_quickclaim()
	{

		$user = $this->session->userdata('user');
	
		if ($user && $user->user_id > 0) {
			$this->load->library('pdf');
			$this->load->model('template');

			$this->db->where('code', 'deed_of_release');
			$this->db->where('deleted', 0);
			
			$template = $this->db->get('template')->row();			

			$this->db->select('CONCAT(firstname," ",middleinitial," ",lastname," ",aux) name, user_company.address AS company_address,company, amount_final_pay amount, pres_address1, pres_address2, pres_city, CONCAT(city, ", ", province) AS pres_city_desc, pres_province', false);
			$this->db->where('user.user_id', $this->input->post('employee_id'));
			$this->db->join('employee', 'employee.employee_id = user.user_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('employee_clearance ec', 'ec.employee_id = user.user_id', 'left');
			$this->db->join('employee_clearance_form ecf', 'ecf.employee_clearance_id = ec.employee_clearance_id', 'left');	
			$this->db->join('cities', 'employee.pres_city = cities.city_id', 'left');	
			$this->db->join('province', 'cities.province_id = province.province_id ', 'left');	

			$result = $this->db->get('user');
			
			$vars = $result->row_array();			
			$vars['address'] = implode(' ', array($vars['pres_address1'], $vars['pres_address2'], $vars['pres_city_desc']));
			$vars['amount'] = '____________';
			$vars['day'] = '____________';
			$vars['month'] = '____________';
			$vars['year'] = date('Y');
			
			if (trim($vars['address']) == '') {
				$vars['address'] = '______';
			}


			$html = $this->template->prep_message($template->body, $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Deed of Release, Waiver, and Quitclaim - ' . $vars['name'] . '.pdf', 'D');			

			exit();
		}

		show_404();

	}

	function get_template_form(){

		$coe = $this->db->get_where('employee_clearance', array('employee_clearance_id' => $this->input->post('record_id')));
		$response->form = $this->load->view( $this->userinfo['rtheme'].'/employee/clearance/template_form',array('coe' => $coe->row()), true );
		$this->load->view('template/ajax', array('json' => $response));
	}

	function print_coe_template($record_id = 0) {

		// Get from $_POST when the URI is not present.
		if ($record_id == 0) {
			$record_id = $this->input->post('record_id');
		}
		
		$tpl_file = 'coe_with_comp'; //default template
		
		$this->load->library('pdf');
		$this->load->model(array('uitype_detail', 'template'));

		$this->db->where('code', 'coe');
		$this->db->where('deleted', 0);
		
		$template = $this->db->get('template')->row();	
		// if( $this->uri->rsegment(4) )
		// 	$template = $this->template->get_template( $this->uri->rsegment(4) );	
		// else
		// 	$template = $this->template->get_module_template($this->module_id, $tpl_file );

		$record_id = $this->uri->rsegment(3);

		$this->db->select('u.salutation, u.firstname, u.lastname, u.aux, u.middleinitial, c.company, e.employed_date, p.position, d.department');
		$this->db->where('employee_clearance_id',$record_id);
		$this->db->from('employee_clearance ec');
		$this->db->join('user u','u.employee_id = ec.employee_id','left');
		$this->db->join('employee e','e.employee_id = u.employee_id','left');
		$this->db->join('user_company_department d','d.department_id = u.department_id','left');
		$this->db->join('user_position p','p.position_id = u.position_id','left');
		$this->db->join('user_company c','c.company_id = u.company_id','left');
		$result = $this->db->get();

		if( $result->num_rows() > 0 ){

			$record = $result->row();
			$vars = array(
				'date' => date($this->config->item('display_date_format')),
				'day' => date('jS'),
				'month' => date('F'),
				'year' => date('Y'),
				'aux' => $record->salutation,
				'suffix' => ($record->aux == ''? '':$record->aux),
				'name' => $record->firstname." ".$record->middleinitial." ".$record->lastname,
				'company' => $record->company,
				'date_hired' => date("F d, Y",strtotime($record->employed_date)),
				'position' => $record->position,
				'department' => $record->department,
				'compensation' => '          ',
			);
			
			$html = $this->template->prep_message($template->body, $vars, false, true);

			// Prepare and output the PDF.
			$this->pdf->SetFont('helvetica', '', 10);
			$this->pdf->addPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(date('Y-m-d').' Certificate of Employment' . '.pdf', 'D');

		}

	}
}

/* End of file */
/* Location: system/application */