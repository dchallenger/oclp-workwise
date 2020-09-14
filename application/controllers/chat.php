<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Chat extends MY_Controller
{
	function __construct()
  {
        
				parent::__construct();
				if(!$this->input->post('action')) redirect(base_url());

		$this->openChatBoxes = $this->session->userdata('openChatBoxes');
		if(! $this->openChatBoxes ){
			$this->session->set_userdata( 'openChatBoxes', $this->openChatBoxes );
			$this->openChatBoxes = array();
		}
		
		$this->chatHistory = $this->session->userdata('chatHistory');
		if(! $this->chatHistory ){
			$this->session->set_userdata( 'chatHistory', $this->chatHistory );
			$this->chatHistory = array();
		}
		
		$this->tsChatBoxes = $this->session->userdata('tsChatBoxes');
		if(! $this->tsChatBoxes ){
			$this->session->set_userdata( 'tsChatBoxes', $this->tsChatBoxes );
			$this->tsChatBoxes = array();
		}

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
	function index(){
		switch($this->input->post('action')){
			case "chatheartbeat": $this->chatHeartbeat(); break;
			case "sendchat": $this->sendChat(); break;
			case "closechat": $this->closeChat(); break;
			case "startchatsession": $this->startChatSession(); break;
		}
	}
	// END - default module functions
	
	// START custom module funtions
	function startChatSession() {
		$items = array();
			
		foreach ($this->openChatBoxes as $chatbox => $void) {
			$items = $this->chatBoxSession($chatbox);
		}
		
		$data['username'] = $this->user->user_id;
		$data['items'] = $items;
		$json['json'] = $data;
    $this->load->view($this->userinfo['rtheme'].'/template/ajax', $json);		
		
	}
	
	function chatBoxSession($chatbox) {
		$items = isset($this->chatHistory[$chatbox]) ? $this->chatHistory[$chatbox] : array();
		return $items;	
	}
	
	function chatHeartbeat() {
		$items = array();
		
		$qry = "select * from {$this->db->dbprefix}chat_personal a where (a.to = '".mysql_real_escape_string($this->user->user_id)."' AND recd = 0) order by id ASC";
		$query = $this->db->query( $qry );
		if($query->num_rows() > 0){
			foreach($query->result() as $chat){
				//if (!isset($this->openChatBoxes[$chat->from]) && isset($this->chatHistory[$chat->from])) $items = $this->chatHistory[$chat->from];
				$chat->message = sanitize( $chat->message );
				$chat->message = stripslashes( $chat->message );
				$userinfo = $this->hdicore->_get_userinfo( $chat->from );
				$avatar = (!empty($userinfo->photo) && file_exists($userinfo->photo) ) ? $userinfo->photo : $this->userinfo['theme'].'/images/no-photo.jpg';
				$items[] = array(
					"s" => 0,
					"f" => $chat->from,
					"a" => base_url() . $avatar,
					"n" => $userinfo->firstname.' '.$userinfo->lastname,
					"m" => $chat->message
				);
				
				if (!isset($this->chatHistory[$chat->from])) $this->chatHistory[$chat->from] = array();
				
				$chatHistory[$chat->from][] = array(
					"s" => 0,
					"f" => $chat->from,
					"a" => base_url() . $avatar,
					"n" => $userinfo->firstname.' '.$userinfo->lastname,
					"m" => $chat->message
				);
				$this->session->set_userdata('chatHistory', $this->chatHistory);
				if( isset($this->tsChatBoxes[$chat->from]) ) unset( $this->tsChatBoxes[$chat->from] );
				$this->session->set_userdata( 'tsChatBoxes', $this->tsChatBoxes );
				$openChatBoxes[$chat->from] = $chat->sent;
				$this->session->set_userdata('openChatBoxes', $this->openChatBoxes);
			}
		}
		
		foreach ($this->openChatBoxes as $chatbox => $time) {
			if (!isset($this->tsChatBoxes[$chatbox])) {
				$now = time()-strtotime($time);
				$time = date('g:iA M dS', strtotime($time));
	
				$message = "Sent at $time";
				if ($now > 180) {
					$userinfo = $this->hdicore->_get_userinfo( $chatbox );
					$avatar = (!empty($userinfo->photo) && file_exists($userinfo->photo) ) ? $userinfo->photo : $this->userinfo['theme'].'/images/no-photo.jpg';
					$items[] = array(
						"s" => 2,
						"f" => $chatbox,
						"a" => base_url() . $avatar,
						"n" => $userinfo->firstname.' '.$userinfo->lastname,
						"m" => $message
					);
	
					if (!isset($this->chatHistory[$chatbox])) $this->chatHistory[$chatbox] = array();
					
					$this->chatHistory[$chatbox][] = array(
						"s" => 2,
						"f" => $chatbox,
						"a" => base_url() . $avatar,
						"n" => $userinfo->firstname.' '.$userinfo->lastname,
						"m" => $message
					);
					$this->session->set_userdata('chatHistory', $this->chatHistory);
					$this->tsChatBoxes[$chatbox] = 1;
					$this->session->set_userdata( 'tsChatBoxes', $this->tsChatBoxes );
				}
			}
		}
		
		
		$qry = "update {$this->db->dbprefix}chat_personal a set recd = 1 where a.to = '".mysql_real_escape_string($this->user->user_id)."' and recd = 0";
		$query = $this->db->query( $qry );
		
		$data['items'] = $items;
		$json['json'] = $data;
    $this->load->view($this->userinfo['rtheme'].'/template/ajax', $json);	
	}
	
	function closeChat() {
		if(isset( $this->openChatBoxes[$this->input->post('chatbox')] )) unset( $this->openChatBoxes[$this->input->post('chatbox')] );
		$this->session->set_userdata('openChatBoxes', $this->openChatBoxes);
		$json['json'] = 1;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $json);	
	}
		
	function sendChat() {
		$from = $this->user->user_id;
		$to = $this->input->post('to');
		$message =  $this->input->post('message');
		
		$this->openChatBoxes[$to] = date('Y-m-d H:i:s', time());
		
		$messagesan = sanitize($message);
		if (!isset($this->chatHistory[$to])) $this->chatHistory[$to] = array();
		
		$userinfo = $this->hdicore->_get_userinfo( $to );
		$avatar = (!empty($userinfo->photo) && file_exists($userinfo->photo) ) ? $userinfo->photo : $this->userinfo['theme'].'/images/no-photo.jpg';
		$this->chatHistory[$to][] = array(
			"s" => 1,
			"f" => $to,
			"a" => base_url() . $avatar,
			"n" => $userinfo->firstname.' '.$userinfo->lastname,
			"m" => $messagesan
		);
		
		if(isset($this->tsChatBoxes[$to])) unset($this->tsChatBoxes[$to]);
		$this->session->set_userdata('tsChatBoxes', $this->tsChatBoxes);
		
		$data = array(
			'from' => $from,
			'to' => $to,
			'message' => mysql_real_escape_string($message)
		);
		
		$this->db->insert('chat_personal', $data);
		$json['json'] = 1;
		$this->load->view($this->userinfo['rtheme'].'/template/ajax', $json);	
	}
	
	/**
	 * check wether user has overall access to modu;e
	 * @return void
	 */
	public function _visibility_check(){
		//do nothing, everyone has access to chat
	}

	// END custom module funtions
}

/* End of file */
/* Location: system/application */
?>