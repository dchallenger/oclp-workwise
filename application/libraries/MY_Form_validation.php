<?php

class MY_Form_validation extends CI_Form_validation
{
	public function reset_rules()
	{
		$this->_error_array = array();
		$this->_field_data = array();
	}	
}