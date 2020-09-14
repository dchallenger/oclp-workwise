<?php
class emailer extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('hdicore');
	}

	function index()
	{

		$this->load->helper('file');
		$this->load->model('template');

		// create log
		$folder = 'logs/emailer';
		$log_file = $folder.'/'.date('Y-m-d').'.txt';
		if(!file_exists($folder)) 
			mkdir($folder, 0777, true);

		// start email
		$mail = $this->template->get_queued();

		if($mail->num_rows() > 0)
		{
			//$mail = $mail->row();
			$mail_config = $this->hdicore->_get_config('outgoing_mailserver');
			$meta = $this->hdicore->_get_meta();
			$this->load->library('email', $mail_config);
			$this->email->set_newline("\r\n");
			$this->email->from($mail_config['smtp_user'], $meta['title']);

			foreach ($mail->result() as $key => $value) {
				$this->template->change_status($value->id, 'sending');

				if (trim($value->to) == '' && trim($value->cc) == '') {
					$this->email->to($mail_config['smtp_user']);
				} else {
					$this->email->to($value->to);
					$this->email->cc($value->cc);
				}
				if (trim($value->bcc) <> '') {
					$this->email->bcc($value->bcc);
				}

				if (trim($value->subject) <> '') {
					$this->email->subject($value->subject);
					$this->email->message($value->body);

					if ( !$this->email->send() ) {
						log_message('error', $this->email->print_debugger());
						//$this->template->change_status($value->id, 'queued');

						if ($value->retries > 10) {
							$this->template->change_retries($value->id, 'error', $value->retries + 1);

							// log message
							$log_msg = date('Ymd H:i:s').' ERROR '.print_r($value,true)."\r\n";
							write_file($log_file, $log_msg, 'a');
						} else {
							$this->template->change_retries($value->id, 'queued', $value->retries + 1);

							// log message
							$log_msg = date('Ymd H:i:s').' RETRY '.print_r($value,true)."\r\n";
							write_file($log_file, $log_msg, 'a');
						}
					} else {

						$this->template->delete_from_queue( $value->id );

						// log message
						$log_msg = date('Ymd H:i:s').' SENT '.print_r($value,true)."\r\n";
						write_file($log_file, $log_msg, 'a');
					}

				} else {
					$this->template->change_retries($value->id, 'error', $value->retries);

					// log message
					$log_msg = date('Ymd H:i:s').' ERROR NO SUBJECT'.print_r($value,true)."\r\n";
					write_file($log_file, $log_msg, 'a');
				}

			}

		}
	}
}	
