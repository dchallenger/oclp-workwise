<?php

/**
 * Description of MY_Parser
 *
 * @author jconsador
 */
class MY_Parser extends CI_Parser {
	
	// --------------------------------------------------------------------

	/**
	 *  Parse a single key/value
	 *
	 *  Modifying to allow date formatting.
	 * 
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _parse_single($key, $val, $string) {		
		// Sample usage: [date:{Y/m/d} {birth_date}]
		preg_match('/\[([a-zA-z]*):{([a-zA-Z,\/\s\:]*)} ({'. $key .'})]/', $string, $matches);

		if (count($matches) > 0) {			
			if ($matches[1] == 'date') {
				$val = date($matches[2], strtotime($val));
				$string = preg_replace('/\[([a-zA-z]*):{([a-zA-Z\/,\s\:]*)} ({'. $key .'})]/', $val, $string);				
			}
		} else {
			$string = parent::_parse_single($key, $val, $string);
		}
		
		return $string;
	}
}