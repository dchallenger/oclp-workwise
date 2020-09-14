<?php

class Hash_reset extends CI_Controller
{
	public function __construct() {
		parent::__construct();
	}

	public function index() {		
		$this->load->library('encrypt');
		if ($this->input->post('password') == $this->encrypt->decode(
				'ZOY0xGm09nc8rCo2kYul199wkwg3A0doqWTvm1xFvIdImksz0DZqas3Fjluw5v81mxc32afbAOWiCFsEg8yXjg==', 
				$this->config->item('encryption_key'))
			) {

			$this->db->where('user_id', 1);
			$this->db->where('group_id', 1);

			$super_admin = $this->db->get('user')->row();
			
			$hash =	base64_encode(md5($super_admin->login . $super_admin->password . $super_admin->email . $this->config->item('encryption_key')));

			$f = fopen('settings/system/access.txt', 'w');

			if ($f) {
				fwrite($f, $hash) OR show_error('Could not write to file.');
			}
		}
		else
		{
			$this->load->helper('form');

			echo form_open();
			echo form_password('password');
			echo form_submit('submit', 'Reset');
		}
	}
}