<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller
{
	function __construct(){
		parent::__construct();
		$this->userinfo['rtheme'] = $this->config->item('default_theme');
	}

	function index()
	{
		//check if already logged in, redirect if true
		$this->user = $this->session->userdata('user');
		if( $this->user ) redirect( base_url() );

		//set some default variables
		$this->module = 'login';
		$this->method = 'method';
		$this->parent_path = '';
		$this->module_link = 'login';
		$this->module_id = '888';
		$this->module_name = "Login";
		$this->userinfo['theme'] = 'themes/' . $this->config->item('default_theme');
		$this->lang->load('login');

		//load header
		$data['meta'] = $this->hdicore->_get_meta();
		$data['method'] = $this->uri->rsegment(2);
		if( $this->config->item('enable_recaptcha') ) $data['scripts'][] = recaptcha_script();

		if( $this->session->flashdata('flashdata') ){
			$message = $this->session->flashdata('flashdata');
			$data['html_msg'] = ($message['error'] == "true" ?  message_box('error', $message['message'],1) :  message_box('success', $message['message'],1));
		}

		$this->load->view( $this->userinfo['rtheme'].'/template/header', $data );

		//load page content
		$this->load->view( $this->userinfo['rtheme'].'/login' );

		//load header
		$this->load->view( $this->userinfo['rtheme'].'/template/footer' );
	}

	function logout()
	{
		$this->session->unset_userdata('user');
		$this->session->sess_destroy();
		redirect('login');
	}

	function validation()
	{
		$post['login'] = $this->input->post('login');
		$post['password'] = $this->input->post('password');
		$post['deleted'] = 0;
		$post['inactive'] = 0;

		$user = $this->hdicore->_verify_login($post['login'], $post['password'], $post);

		if($user && $user->num_result() == 1 ){
			$user = $user->row();
			if ($this->input->post('rememberme')) {
				$this->session->sess_destroy();
				$this->config->set_item('sess_expiration', -1);
				$this->session->sess_run();
				$this->session->set_userdata('sess_length',(60*60*24*14));
			}
			$this->session->set_userdata('user', $user);
			return true;
		}
		else{
			return false;
		}
	}

	function validate_login()
	{
		if(IS_AJAX){
			$response->msg = "";
			$response->msg_type = 'error';
			$response->login = false;

			$where['user.deleted'] = 0;
			$where['user.inactive'] = 0;

			//check if login exists
			if( $this->hdicore->_verify_login( $this->input->post('login'), '', $where ) ){
				//check if password match
				$user = $this->hdicore->_verify_login( $this->input->post('login'), $this->input->post('password'), $where );
				if( $user ){
					$system_login_check = $this->system->_login_check( $user->user_id );
					if( $system_login_check->login ){
						if ( $this->input->post('rememberme') ){
							$this->config->set_item('sess_expiration', -1);
							$this->session->set_userdata('sess_length',(60*60*24*14));
						}
						$this->session->set_userdata('user', $user);
						$response->msg  = 'Login successful.';
						$response->msg_type = 'success';
						$response->error_field = 'none';
						$response->login = true;
					}
					else{
						$response = $system_login_check;	
					}
				}
				else{
					$response->msg  = 'Password did not match.';
					$response->msg_type = 'error';
					$response->error_field = 'password';
				}
			}
			else{
				$response->msg  = 'Invalid Login/Email';
				$response->msg_type = 'error';
				$response->error_field = 'login';
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

    function verify_email()
    {
		if(IS_AJAX){
			$response->msg = "";
			$response->email = "";

			$this->load->library('form_validation');

			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_email_check');
			$this->form_validation->set_error_delimiters('','');

			if ( $this->form_validation->run() == FALSE ){

				$login_check = $this->db->get_where('user',array('login'=>$this->input->post('email')));

				if( $login_check->num_rows() == 0 ){
					$response->msg = "Invalid Login or Email Address";
					$response->msg_type = "error";
				}
				else{
					$login_info = $login_check->row();

					if( $login_info->email != "" ){
						$response->email = $login_info->email;
					}
					else{
						$response->msg = "Invalid Login or Email Address";
						$response->msg_type = "error";
					}
				}

			}
			else{
				//verify that email is registered and active
				if( !$this->hdicore->_verify_login($this->input->post('email'), '', array('deleted' => 0, 'inactive' => 0)) ){
					$response->msg = "Email is not registered.";
					$response->msg_type = "error";
				}
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}

    }

	function send_reset_password()
	{

		if( IS_AJAX ){
			$user = $this->hdicore->_verify_login( $this->input->post('email'), '', array('deleted' => 0, 'inactive' => 0) );
			$user = $this->hdicore->_get_userinfo($user->user_id);
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			if( $mail_config ){
				//log password reset
				//create hash
				$hash = md5( $this->createRandomString() );
				$datetime = date( 'Y-m-d H:i:s' );
				$expiration =  date( 'Y-m-d H:i:s', strtotime("+10 minutes") );
				$log = array(
					'user_id' => $user->user_id,
					'email' => $user->email,
					'hash' => $hash,
					'datetime_of_request' => $datetime,
					'expiration' => $expiration
				);
				$this->db->insert('password_reset_request', $log);

				//compose the message
				//get meta variables
				$meta = $this->hdicore->_get_meta();
				$this->load->model('template');
				$template = $this->template->get_template( 1 );

				if (CLIENT_DIR == 'firstbalfour'){
                    $message_vars['username'] = $user->salutation.' '. $user->firstname.' '.$user->lastname;
                    if ($user->aux != ''){
                    	$message_vars['username'] = $user->salutation.' '. $user->firstname.' '.$user->lastname.' '.$user->aux;
                    } 					
				}
				else{
					$message_vars['username'] = $user->salutation.' '. $user->firstname.' '.$user->lastname;
				}

				$message_vars['reset_link'] = base_url().'login/password_reset_confirmation/'.$hash;
				$message_vars['expiration'] = date( 'd M Y, h:i:s A' , strtotime($expiration));
				$message_vars['title'] = $meta['title'];
				$message_vars['copyright'] = $meta['copyright'];
				$message = $this->template->prep_message( $template['body'], $message_vars );
				
				$queue_result = $this->template->queue( $this->input->post('email'), '', $template['subject'], $message);

				if( $queue_result ){
					$data['system_msg'] = 'Please check your email on how to retrieve your new password.';
					$data['html_msg'] = message_box('success', $data['system_msg'],1);
					$data['is_error'] = 0;
					$data['send_success'] = 1;
				}
				else{
					$data['system_msg'] = 'Problem encountered, Message was not sent. '.$queue_result;
					$data['html_msg'] = message_box('error',$data['system_msg'],1);
					$data['is_error'] = 1;
					$data['send_success'] = 0;
				}
			}
			else{
				$data['system_msg'] = "Cannot set Outgoing Email configuration.";
				$data['html_msg'] = message_box('error',$data['system_msg'],1);
				$data['is_error']= 1;
				$data['send_success'] = 0;
			}

			$json['json'] = $data;
      $this->load->view($this->userinfo['rtheme'].'/template/ajax', $json);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}

    function reset_password( $email )
    {
        $user = $this->hdicore->_verify_login( $email, '', array('deleted' => 0, 'inactive' => 0) );
        $user = $this->hdicore->_get_userinfo($user->user_id);
		$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
		if( $mail_config ){
			//get meta variables
			$meta = $this->hdicore->_get_meta();
			$this->load->model('template');
			$template = $this->template->get_template( 2 );
			
			if (CLIENT_DIR == 'firstbalfour'){
                $message_vars['username'] = $user->salutation.' '. $user->firstname.' '.$user->lastname;
                if ($user->aux != ''){
                	$message_vars['username'] = $user->salutation.' '. $user->firstname.' '.$user->lastname.' '.$user->aux;
                } 					
			}
			else{
				$message_vars['username'] = $user->salutation.' '. $user->firstname.' '.$user->lastname;
			}

			$message_vars['userlogin'] = $user->login;
			//create new password
			$message_vars['password'] = $this->createRandomString();

			//update user password
			$this->db->where('user_id', $user->user_id);
			$this->db->update('user', array('password' => md5( $message_vars['password'] )));

			$message_vars['link'] = base_url();
			$message_vars['title'] = $meta['title'];
			$message_vars['copyright'] = $meta['copyright'];
			$message = $this->template->prep_message($template['body'], $message_vars);

			$queue_result = $this->template->queue( $email, '', $template['subject'], $message);

			if( $queue_result ){
				$data['system_message'] = 'Login Info has been sent, please check your email.';
				$data['html_msg']       = message_box('success', $data['system_message'],1);
				$data['is_error']       = 0;
				$data['send_success']   = 1;
			}
			else{
				$data['system_msg']   = 'Problem encountered, Message was not sent.'.$this->email->print_debugger();
				$data['html_msg']     = message_box('error',$data['system_msg'],1);
				$data['is_error']     = 1;
				$data['send_success'] = 0;
			}
		}
		else{
			$data['system_msg'] = "Cannot set Outgoing Email configuration.";
			$data['html_msg'] = message_box('error',$data['system_msg'],1);
			$data['is_error']= 1;
			$data['send_success'] = 0;
		}

        return $data;
    }

	function password_reset_confirmation()
	{
		if( $this->uri->segment(3) && $this->uri->segment(3) != "" ){
			$hash = $this->uri->segment(3);
			$reset_request = $this->db->get_where('password_reset_request', array('hash' => $hash));
			if( $reset_request->num_rows() > 0 ){
				$reset_request = $reset_request->row();
				$now = date( 'Y-m-d H:i:s' );
				$date_expi = date('Y-m-d H:i:s',strtotime('+1 day',strtotime($reset_request->expiration)));
				$datetime_diff = strtotime($date_expi) - strtotime($now);
				if($datetime_diff > 0){
					//reset pass
					$send = $this->reset_password( $reset_request->email );
					if($send['is_error'] == 0){
						//delete the log
						$this->db->delete('password_reset_request', array('hash' => $hash));
						$data = array('error' => "false", 'message' => "New Password has been sent to your email.");
						$this->session->set_flashdata('flashdata', $data);
					}
					else{
						$data = array('error' => "true", 'message' => $send['system_msg']);
						$this->session->set_flashdata('flashdata', $data);
					}
				}
				else{
					//expiration reach
					$data = array('error' => "true", 'message' => "This link has expired. Please request a new one.");
					$this->session->set_flashdata('flashdata', $data);
				}
			}
			else{
				$data = array('error' => "true", 'message' => "Data Hash supplied does not exist.");
				$this->session->set_flashdata('flashdata', $data);
			}
		}
		else{
			$data = array('error' => "true", 'message' => "Data supplied was insufficient.");
			$this->session->set_flashdata('flashdata',$data);
		}
		redirect(base_url().'login');
	}

	function createRandomString() {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $string = '' ;

        while ($i <= 7) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $string .= $tmp;
            $i++;
        }
        return $string;
    }

	function validate_recaptcha()
	{
		if(IS_AJAX)
		{
			$post = array();
			foreach( $_POST as $key => $value )
			{
				$post[] = $key.'='.$value;
			}
			$post = implode( '&', $post );
			$post .= '&remoteip='.$this->input->ip_address();
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://www.google.com/recaptcha/api/verify');
			curl_setopt($ch, CURLOPT_POST , 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS , $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$contents = curl_exec ($ch);
			curl_close ($ch);

			$captcha_response = explode( "\n", $contents );
			if( $captcha_response[0] == "true"){
				$response->valid = "true";
				$response->msg = message_box('error','success',1);
			}
			else{
				switch($captcha_response[1]){
					case "unknown":
						$error = "Unknown error.";
						break;
					case "invalid-site-public-key":
						$error = "Invalid reCaptcha Public Key.";
						break;
					case "invalid-site-private-key":
						$error = "Invalid reCaptcha Private Key.";
						break;
					case "invalid-request-cookie":
						$error = "The challenge parameter of the verify script was incorrect.";
						break;
					case "incorrect-captcha-sol":
						$error = "The CAPTCHA solution was incorrect.";
						break;
					case "verify-params-incorrect":
						$error = "The parameters to /verify were incorrect, make sure you are passing all the required parameters.";
						break;
					case "invalid-referrer":
						$error = "reCAPTCHA API keys are tied to a specific domain name for security reasons.";
						break;
					default:
						$error = "An error occured.";
						break;
				}
				$response->valid = "false";
				$response->raw_msg = $error;
				$response->msg = message_box( 'error', $error, 1);
			}

			$data['json'] = $response;
			$this->load->view($this->userinfo['rtheme'].'/template/ajax', $data);
		}
		else{
			$this->session->set_flashdata('flashdata', 'Operation does not allow direct access.<br/>Please contact the System Administrator.');
			redirect(base_url().$this->module_link);
		}
	}
}