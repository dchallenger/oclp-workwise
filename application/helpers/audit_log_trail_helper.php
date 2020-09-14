<?php
	function unset_unnecessary_field($post){
		unset($post['record_id']);
		unset($post['prev_search_field']);
		unset($post['date-temp']);
		unset($post['datelate-temp']);
		return $post;
	}

	function flatten($array, $prefix = '') {
	    $result = array();
	    foreach($array as $key=>$value) {
	    	if (is_numeric($key)){
	    		$key = '';
	    	}
	        if(is_array($value)) {
	            $result = $result + flatten($value, $prefix . $key);
	        }
	        else {        	
	            $result[$prefix . $key] = $value;
	        }
	    }
	    return $result;
	}

	function implode_with_key($assoc, $inglue = ' = ', $outglue = ',') {
	    $return = '';
	 
	    foreach ($assoc as $tk => $tv) {
	        $return .= $outglue . $tk . $inglue . $tv;
	    }
	 
	    return substr($return, strlen($outglue));
	}

	function re_arrange_array($arr){
		$re_ar_array_name = array();
		$re_ar_array_label = array();

		if (sizeof($arr) > 0){
			foreach ($arr as $val) {			
				if (isset($val['fields'])){
					foreach ($val['fields'] as $val1) {
						if (isset($val1['value'])){
							if (preg_match('/^\d{4}\-\d{1,2}\-\d{2}$/', $val1['value'])) { 
								$re_ar_array_name[$val1['fieldname']] = date('m/d/Y',strtotime($val1['value']));	
							}  					
							else{
								$re_ar_array_name[$val1['fieldname']] = $val1['value'];	
							}
						}
						$re_ar_array_label[$val1['fieldname']] = $val1['fieldlabel'];
					}
				}
			}
		}

		$arr['ar_name'] = $re_ar_array_name;
		$arr['ar_label'] = $re_ar_array_label;
		return $arr;
	}

	function reassign($arr){
		$ci=& get_instance();		
		$result = array();
		foreach($arr as $key=>$value) {
	    	switch ($key) {
	    		case 'user_id':	        		
	    		case 'employee_id':
	    				$row = $ci->db->get_where('user', array('employee_id' => $value))->row(); 
						$value = $row->firstname .' '. $row->lastname;
	    			break;	       			       	 
	    		case 'form_status_id':
						$value = $ci->db->get_where('form_status', array('form_status_id' => $value))->row()->form_status;
	    			break;	   
	    		case 'region_id':
						$value = $ci->db->get_where('region', array('region_id' => $value))->row()->region;
	    			break;		    			     			       		
	    		case 'application_form_id':
	    				$value = $ci->db->get_where('employee_form_type', array('application_form_id' => $value))->row()->application_form;
	    			break;
	    		case 'date_from':
	    				$key = 'date';
	    		case 'date_to':
	    				$key = 'date';
	    				if ($result['date'] != ''){
	    					$value = $result['date'] .' - '. $value;
	    				}
	    			break;	     
	    		case 'time_start':
	    				$key = 'time';
	    		case 'time_end':
	    				$key = 'time';
	    				if ($result['time'] != ''){
	    					$value = $result['time'] .' - '. $value;
	    				}
	    			break;	
	    		case 'datetime_from':
	    				$key = 'datetime';
	    		case 'datetime_to':
	    				$key = 'datetime';
	    				if ($result['datetime'] != ''){
	    					$value = $result['datetime'] .' - '. $value;
	    				}
	    			break;		        				        			   			
	    		case 'shift_id':
	    				$value = $ci->db->get_where('timekeeping_shift', array('shift_id' => $value))->row()->shift;
	    			break;
	    		case 'time_set_id':
	    				$value = ($value == 1 ? 'Time in' : 'Time out');
	    			break;	  
	    		case 'shift_calendar_id':
	    				$value = $ci->db->get_where('timekeeping_shift_calendar', array('shift_calendar_id' => $value))->row()->shift_calendar;
	    			break;
	    		case 'position_id':
	    				$value = $ci->db->get_where('user_position', array('position_id' => $value))->row()->position;
	    			break;
	    		case 'company_id':
	    				$value = $ci->db->get_where('user_company', array('company_id' => $value))->row()->company;
	    			break;	        				     	        			
	    		case 'team_id':
	    				$value = $ci->db->get_where('user_team', array('team_id' => $value))->row()->team;
	    			break;
	    		case 'department_id':
	    				$value = $ci->db->get_where('user_company_department', array('department_id' => $value))->row()->department;
	    			break;	        			
	    		case 'division_id':
	    				$value = $ci->db->get_where('user_company_division', array('division_id' => $value))->row()->division;
	    			break;	        				
	    		case 'role_id':
	    				$value = $ci->db->get_where('role', array('role_id' => $value))->row()->role;
	    			break;
	    		case 'rank_id':
	    				$value = $ci->db->get_where('user_rank', array('job_rank_id' => $value))->row()->job_rank;
	    			break;	        			
	    		case 'status_id':
	    				$value = $ci->db->get_where('employment_status', array('employment_status_id' => $value))->row()->employment_status;
	    			break;
	    		case 'campaign_id':
	    				$value = $ci->db->get_where('campaign', array('campaign_id' => $value))->row()->campaign;
	    			break;
	    		case 'job_title':
	    				$value = $ci->db->get_where('user_job_title', array('job_title_id' => $value))->row()->job_title;
	    			break;	
	    		case 'location_id':
	    				$value = $ci->db->get_where('user_location', array('location_id' => $value))->row()->location;
	    			break;	
	    		case 'employee_type':
	    		case 'employee_type_id':
	    				$value = $ci->db->get_where('employee_type', array('employee_type_id' => $value))->row()->employee_type;
	    			break;	 	    			    			        				        			        			
	    		case 'inactive':
	    				$value = ($value == 1 ? 'Yes' : 'No');
	    			break;		        					        			        			
	    	}
        	if (!preg_match('/^multiselect/', $key)){	        	
            	$result[$key] = $value;
        	}
		}
		return $result;
	}

	function filter($arr){
		$filter_array = array();	
		(isset($arr['employee_id']) ? array_push($filter_array, $arr['employee_id']) : '');
		(isset($arr['form_status_id']) ? array_push($filter_array, $arr['form_status_id']) : '');
		(isset($arr['application_form_id']) ? array_push($filter_array, $arr['application_form_id']) : '');
		$filter_comman_delimited = implode(',', $filter_array);		
		return $filter_comman_delimited;
	}