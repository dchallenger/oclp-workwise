<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_system'] = array(
	'class'    => 'Init',
	'function' => 'load',
	'filename' => 'Init.php',
	'filepath' => 'hooks'                                
);

//testing this function, not yet in production
//has problems with dynamically created js where in comments cut the script creting errors
$hook['xdisplay_override'] = array(
	'class'    => 'Minifyhtml',
	'function' => 'minify',
	'filename' => 'Minifyhtml.php',
	'filepath' => 'hooks'
);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */