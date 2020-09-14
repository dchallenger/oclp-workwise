<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$ci =& get_instance();
$ci->load->helper('recruitment');

if (!function_exists('preemployment_filters')) {	

	function preemployment_filters() {
		$ci =& get_instance();
		$ci->load->helper('recruitment');

		$filters = array();

		$module = $ci->hdicore->get_module('preemployment');

        // Get children modules and prepare the checklist data.
	    $module_children = $ci->hdicore->get_module_child($module->module_id);
		$ctr 	= 0;
		$tables = array();
        foreach ($module_children as $checklist) {
			if ($ci->hdicore->module_active($checklist['module_id'])) {
				 $filters[$ctr] = get_checklist_data($checklist, true);
				 $checklist = $filters[$ctr];


				if ($checklist['code'] == 'preemployment_201' &&
					!$ci->hdicore->module_active('hris_201')
				) {
					continue;
				}


				if (CLIENT_DIR == 'oams' && $checklist['code'] == 'preemployment_for_movement' ){
					$ci->db->join('recruitment_manpower_candidate', 'recruitment_manpower_candidate.candidate_id = recruitment_preemployment.candidate_id');
					$movement = $ci->db->get_where('recruitment_preemployment', array('has_201' => 1, 'is_internal' => 1, 'candidate_status_id' => 13));
					if ($movement->num_rows() > 0 ) {
						$filters[$ctr]['count']  = $movement->num_rows();
					}
					
				}else{
					$filters[$ctr]['count']  = get_checklist_count($checklist['table']);
				}

				$filters[$ctr]['link']   = $checklist['link'];
				$filters[$ctr]['text']   = $checklist['label'];
				$filters[$ctr]['status'] = $checklist['status'];

				$tables[] = $checklist['table'];				
			}

			$ctr++;
        }

        // Get completed.
        if (count($tables) > 0) {
        	$sql = 'SELECT COUNT(preemployment_id) as count ';
        	$sql .= 'FROM ' . $ci->db->dbprefix . $module->table;
        	$sql .= ' WHERE ';

        	$where = '';
        	$i = 0;
        	while ($i < count($tables)) {
        		if( $tables[$i] != "employee" ){
	        		$where .= $module->key_field . ' IN (SELECT ' . $module->key_field . ' FROM ';
	        		$where .= $ci->db->dbprefix . $tables[$i];
	        		$where .= ' WHERE completed = 1 AND deleted = 0) ';
				}
				
				if (isset($tables[$i+1]) && $tables[$i+1] != "employee") {
					$where .= 'AND ';
				}

				$i++;
			}

			$where .= ' AND has_201 = 1 AND ' . $ci->db->dbprefix . $module->table . '.deleted = 0';

			$sql .= $where;

			$result = $ci->db->query($sql);

			if ($result && $result->num_rows() > 0) {
				$completed = $result->row();
				
				$filters[$ctr]['count']  = $completed->count;
				$filters[$ctr]['link']   = site_url($module->class_path . '/filter/pre_complete');
				$filters[$ctr]['text']   = 'Completed';				
				$filters[$ctr]['where_complete'] = $where;	
				$filters[$ctr]['code'] = 'pre_complete';				

			}
        }
        
		return $filters;
	}
}