<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Deed_of_release extends CI_Controller
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

	function print_record()
	{
		$user = $this->session->userdata('user');
	
		if ($user && $user->user_id > 0) {
			$this->load->library('pdf');
			$this->load->model('template');

			$this->db->where('code', 'deed_of_release');
			$this->db->where('deleted', 0);
			
			$template = $this->db->get('template')->row();			

			$this->db->select('CONCAT(firstname, middleinitial, lastname, aux) name, user_company.address AS company_address,company, amount_final_pay amount, pres_address1, pres_address2, pres_city, pres_province', false);
			$this->db->where('user.user_id', $this->input->post('employee_id'));
			$this->db->join('employee', 'employee.employee_id = user.user_id', 'left');
			$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
			$this->db->join('employee_clearance ec', 'ec.employee_id = user.user_id', 'left');
			$this->db->join('employee_clearance_form ecf', 'ecf.employee_clearance_id = ec.employee_clearance_id', 'left');			

			$result = $this->db->get('user');
	
			$vars = $result->row_array();			
			$vars['address'] = implode(' ', array($vars['pres_address1'], $vars['pres_address2'], $vars['pres_city'], $vars['pres_province']));
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
}

/* End of file */
/* Location: system/application */