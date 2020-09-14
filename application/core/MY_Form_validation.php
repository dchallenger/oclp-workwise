<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {
  function __construct()
  {
    parent::__construct();
  }
  public function _execute($row, $rules, $postdata = NULL, $cycles = 0)
  {
    if ((! is_array($postdata) && strlen($postdata) == 0) || (is_array($postdata) && empty($postdata))) $postdata = NULL;
    return parent::_execute($row, $rules, $postdata, $cycles);
  }
}  