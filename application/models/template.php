<?php

class template extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	function get_template($template_id = 0) {
		$filter['template_id'] = $template_id;
		$template = $this->db->get_where('template', $filter);
		if ($this->db->_error_message() == "") {
			if ($template->num_rows() > 0) {
				return $template->row_array();
			} else {
				return false;
			}
		} else {
			//dbug( $this->db->_error_message() ); //uncomment to see the error
			return false;
		}
	}

	/**
	 * Use CI parser to substitute vars from $body.
	 * 
	 * Pre-defined vars:
	 * {image_url} , {upload_url}, {logo}
	 * 
	 * @param  string  $body                  Template body.
	 * @param  array   $message_vars          Values to be substituted.
	 * @param  boolean $addslashes            
	 * @param  boolean $use_default_delimiter 
	 * @return string                         
	 */
	function prep_message($body = "", $message_vars = array(), $addslashes = true, $use_default_delimiter = false) {
		
		if ($addslashes) {
			addslashes($body);
		}
		$ci = & get_instance();
		$meta = $ci->config->item('meta');

		// Add the image directory.
		$message_vars['image_url']  = $ci->userinfo['theme'] . '/images/';
		$message_vars['upload_url'] = 'uploads/';
		
		// Add logo path.
		$meta = $ci->config->item('meta');
		$message_vars['logo'] = $meta['logo'];

		$ci->load->library('parser');
		
		// LEGACY: Set parser delimiters to old format "{$VAR}" .
		if (!$use_default_delimiter) {
			$ci->parser->set_delimiters('{$', '}');
		}
		
		return $ci->parser->parse_string($body, $message_vars, TRUE);
	}

	function createRandomPassword() {
		$chars = "abcdefghijkmnopqrstuvwxyz0123456789";
		srand((double) microtime() * 1000000);
		$i = 0;
		$pass = '';

		while ($i <= 7) {
			$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}

	function queue($to = "", $cc = "", $subject = "", $body = "") {
		$data = array(
		    'status' => 'queued',
		    'to' => $to,
		    'cc' => $cc,
		    'subject' => $subject,
		    'body' => $body
		);

		if ($subject != "" && $body != ""){
			$this->db->insert('email_queue', $data);

			if ($this->db->_error_message() != "")
				return $this->db->_error_message();
			else
				return true;
		}
		else{
			return false;
		}
	}

	function queue_with_bcc($to = "", $cc = "", $bcc = "", $subject = "", $body = "") {
		$data = array(
		    'status' => 'queued',
		    'to' => $to,
		    'cc' => $cc,
		    'bcc' => $bcc,
		    'subject' => $subject,
		    'body' => $body
		);

		$this->db->insert('email_queue', $data);
		if ($this->db->_error_message() != "")
			return $this->db->_error_message();
		else
			return true;
	}

	function get_queued() {
		$this->db->where(array('status' => 'queued'));
		$this->db->order_by('timein');
		$this->db->limit(5);
		$mail = $this->db->get('email_queue');
		return $mail;
	}

	function change_status($timein, $status) {
		$this->db->where(array('id' => $timein));
		$this->db->update('email_queue', array('status' => $status));
	}

	function delete_from_queue($id) {
		$data = array(
		    'id' => $id
		);
		$this->db->delete('email_queue', $data);
	}

	// Add "code" column to "template" table before using.
	// Add field "code" on module manager before using.
	function get_module_template($module_id = 0, $template_code = '') {		
		$where = array('module_id' => $module_id, 'code' => $template_code);

		if ($module_id == 0) {
			unset($where['module_id']);
		}

		$template = $this->db->get_where('template', $where);
		if ($this->db->_error_message() == "") {
			if ($template->num_rows() > 0) {
				return $template->row_array();
			} else {
				return false;
			}
		} else {
			//dbug( $this->db->_error_message() ); //uncomment to see the error
			return false;
		}
	}

	function change_retry($id, $status, $retries) {
		$this->db->where(array('id' => $id));
		$this->db->update('email_queue', array('status' => $status, 'retries' => $retries));
	}

}

?>