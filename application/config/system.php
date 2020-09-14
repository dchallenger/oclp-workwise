<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['for_auditing']	= array(
	'add',
	'edit',
	'save',
	'ajax_save',
	'delete',
	'detail',
	'change_status',
	'approve_request',
	'decline_request'
);

$config['bypass_accesscheck']	= array(
	'previous_page',
	'back_to_list',
);

/* End of file system.php */
/* Location: ./application/config/system.php */
