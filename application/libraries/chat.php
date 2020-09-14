<?php
# include TCPDF
require(APPPATH.'config/chat'.EXT);
require_once($chat['base_directory'].'src/phpfreechat.class.php');

class Chat extends phpFreeChat {
	function __construct( $params ) {
		parent::phpFreeChat( $params );
	}
}
?>