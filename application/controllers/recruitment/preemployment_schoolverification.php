<?php
require_once(APPPATH . 'controllers/includes/Preemployment_Controller.php');

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Preemployment_schoolverification extends Preemployment_Controller {
    protected $_template_id = 'verification';
    
    function __construct() {
        parent::__construct();
    }    
}

/* End of file */
/* Location: system/application */