<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Uniform_quicklink extends CI_Controller
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

	function get_template_form(){
		$response->form = $this->load->view( $this->userinfo['rtheme'].'/employee/uniform_order/template_form',array('user_id' => $this->input->post('record_id')), true );
		$this->load->view('template/ajax', array('json' => $response));
	}

	function save_order() {

		// Get from $_POST when the URI is not present.

		$user_id = $this->input->post('user_id');

		$data = array(
			'year'=>date('Y'),
			'employee_id' => $user_id,
			'order_status_id' => 1,
			'date_ordered' => date('Y-m-d h:i:s')
		);

		$this->db->insert('employee_uniform_order',$data);

		$response->msg = 'Data has been successfully saved.';
		$response->msg_type = 'success';

		$data['json'] = $response;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
	}

}

/* End of file */
/* Location: system/application */