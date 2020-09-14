<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('get_export_options')) {	

	function get_export_options($module_id) {
		$ci =& get_instance();
		
		$ci->db->where('parent_module_id', $module_id);
		$ci->db->where('deleted', 0);
		$result = $ci->db->get('export_query');		

		if ($result->num_rows() > 0) {
			return $result;
		} else {
			return FALSE;
		}
	}
}

if (!function_exists('get_export_dropdown')) {	

	function get_export_dropdown($module_id) {
		$ci =& get_instance();
		
		$options = get_export_options($module_id);

		if (!$options || $options->num_rows() == 0) {
			return FALSE;
		}

		$return = array('' => 'Select&hellip;');

		foreach ($options->result() as $option) {
			$return[$option->export_query_id] = $option->description;
		}

		return form_dropdown('export_query_id', $return, '', 'id="quick_export_query_id"');
	}
}
