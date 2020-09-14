<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hdione extends CI_Controller
{

	function __construct()
    {
		parent::__construct();
		$this->load->database();
		$this->load->model('users_model','user');
	}
	
	function open()
	{
		$this->o_user = $this->session->userdata('user');
		
		if( !$this->session->userdata('user') ){
			$post['email'] = $this->uri->rsegment(3); //method
			$post['email'] = urldecode($post['email']);
			$post['password'] = $this->uri->rsegment(4); //method
			$post['deleted'] = 0; //method
	
			$where['user.deleted'] = 0;
			$where['user.inactive'] = 0;
			$user = $this->hdicore->_verify_login( $post['email'] , $post['password'], $where, false );

			$this->session->set_userdata('user', $user);
		}
		
		if( $this->session->userdata('user') ){
			redirect();
		}
	}
	
	function login()
	{
		//check if already logged in, redirect if true
		$this->o_user = $this->session->userdata('user');
		if($this->o_user){
			$data['login'] = "true";
			$data['message'] = "Already logged in in HRIS.";
			$data['message_type'] = "success";
		}
		else{
			if( $this->input->post('email') && $this->input->post('password') )
			{
				if($this->input->post('force') == "0"){
					$data = $this->_login();
				}
				else{
					$data = $this->_force_login();
				}
			}
			else{
				$data['login'] = "false";
				$data['message'] = "Supplied data incomplete.";
				$data['message_type'] = "error";
			}
		}
		
		$data['app'] = "hris";
		$json['json'] = $data;
        $this->load->view('template/ajax2', $json);
	}
	
	
	function _login()
    {
		//check if email exist
		$email = $this->db->get_where( 'user', array( 'email' => $this->input->post('email'), 'deleted' => 0 ) );
		if( $this->db->_error_message() == "" )
		{
			if($email->num_rows() > 0 && $email->num_rows() == 1 )
			{
				//email exist, verify password
				$user = $email->result_array();
				$user = $user[0];
				$password = md5( $this->input->post('password') );
				if( strcmp($password, $user['password']) == 0 )
				{
					unset($user);
					$where['user.deleted'] = 0;
					$where['user.inactive'] = 0;
					$user = $this->hdicore->_verify_login( $this->input->post('email'), $this->input->post('password'), $where );

					if( $user ){
						$this->session->set_userdata('user', $user);
						$data['user'] = $user;
						$data['login'] = "true";
						$data['message'] = "HRIS login success.";
						$data['message_type'] = "success";
					}
					else{
						$data['login'] = "false";
						$data['message'] = "User inactive or deleted.";
						$data['message_type'] = "error";
					}
				}
				else{
					$data['login'] = "false";
					$data['message'] = "Password did not match.";
					$data['message_type'] = "error";
				}
			}
			else if($email->num_rows() > 0 && $email->num_rows() > 1){
				//inconsistent data, inform admin
				$data['login'] = "false";
				$data['message'] = "Inconsistent data, please inform System Administrator.";
				$data['message_type'] = "error";
			}
			else{
				//email does not exist
				$data['login'] = "false";
				$data['message'] = "Email does not exists.";
				$data['message_type'] = "attention";
			}
		}
		else{
			$data['login'] = "false";
			$data['message'] = $this->db->_error_message();
			$data['message_type'] = "error";
		}
		
		return $data;	
    }
	
	function _force_login()
	{
		//sync email & pass
		$this->db->where('email', $this->input->post('email'));
		$this->db->update('user', array('password' => md5($this->input->post('password')))); 
		
		return $this->_set_session();
	}
	
	function _set_session()
	{
		$post['email'] = $this->input->post('email');
		$post['password'] = md5($this->input->post('password'));
		$post['deleted'] = 0;

		$u = $this->user->getUser($post);
		$this->session->set_userdata('user', $u);
		
		$data['login'] = "true";
		$data['message'] = "HRIS login success.";
		$data['message_type'] = "success";
		
		return $data;	
	}	

	function logout()
	{
		$this->session->unset_userdata('user');
	}
}

?>