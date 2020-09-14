<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class portlet extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	} 

	function get_user_time_record($employee_id,$date) {
		$this->db->where('deleted',0);
		$this->db->where('employee_id',$employee_id);
		$this->db->where('date',$date);
		$result = $this->db->get('employee_dtr');

		if ($result->num_rows() > 0) {
			return $result->row();
		} else {
			return FALSE;
		}	

	}   

	function get_user_location() {
		$this->db->where('deleted',0);
		$this->db->order_by('location');
		$result = $this->db->get('user_location');

		if ($result->num_rows() > 0) {
			return $result->result();
		} else {
			return FALSE;
		}	

	}   

	function get_user_portlet_state()
	{
		// get portlet list
		//$this->db->order_by('column');
		$this->db->order_by('sequence');		
		$portlets = $this->db->get_where('portlet', array('deleted' => 0, 'inactive' => 0));

		if($portlets->num_rows() > 0)
			$portlets = $portlets->result_array();
		else
			$portlets = array();

		//get portlet state of user
		$portlet_state = $this->_get_user_config('portlet_state', $this->user->user_id);

		if( is_array($portlet_state) && sizeof($portlet_state) > 0 ){
			foreach($portlets as $index => $portlet)
			{
				if ($portlet['portlet_name'] != 'Time Entry') { // added for the time entry on dashboard 20200506 - tirso
					$portlets[$index]['column']   = $portlet_state[$portlet['portlet_id']]['column'];
					$portlets[$index]['sequence'] = $portlet_state[$portlet['portlet_id']]['sequence'];
					$portlets[$index]['is_folded'] = $portlet_state[$portlet['portlet_id']]['is_folded'];

					if( !empty( $portlet_state[$portlet['portlet_id']]['is_wide'] ) ){
						$portlets[$index]['is_wide'] = $portlet_state[$portlet['portlet_id']]['is_wide'];
					}
				}

			}
		}

		foreach ($portlets as $index => $portlet) {
			$sequence[$index] = $portlet['sequence'];
		}

		array_multisort($sequence, SORT_ASC, $portlets);
		
		return $portlets;
	}
	
	
	/* manpower count */
	function get_cbe_non_cbe_ytd_ave($month_year_from,$month_year_to,$count_month){
		$result_array = array();
		$tstampdateto = strtotime($month_year_to);			
		$tstamp_cdate = strtotime($month_year_from);
		$arr_cbe = array();
		$arr_non_cbe = array();

		//get all records as of last dec for cbe
		$last_year_date = date('Y-m-t',strtotime(date('Y-01-01') . '-1 month'));

		$this->db->where('employee.cbe',1);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date', null);
		$this->db->where('employee.employed_date <=', $last_year_date);
		$this->db->where('employee.employed_date <>', '0000-00-00');
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$this->db->group_by('employee.employee_id');
		$result = $this->db->get('user');
		$as_of_last_dec_cbe = $result->num_rows();
		//end cbe previous december

		$this->db->where('employee.cbe',0);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date', null);
		$this->db->where('employee.employed_date <=', $last_year_date);
		$this->db->where('employee.employed_date <>', '0000-00-00');		
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$this->db->group_by('employee.employee_id');
		$result = $this->db->get('user');
		$as_of_last_dec_none_cbe = $result->num_rows();
		//end non cbe previous december

		$where = "FIND_IN_SET('6', segment_2_id)";  
		$this->db->where($where);	
		$this->db->where('employee.cbe',1);	
		$this->db->where('user.deleted',0);
		$this->db->where('user.division_id',0);
		// $this->db->where('user.assignment',1);
		$this->db->where('employee.resigned_date', null);
		$this->db->where('employee.employed_date <=', $last_year_date);
		$this->db->where('employee.employed_date <>', '0000-00-00');		
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		// $this->db->join('employee_work_assignment', 'employee.employee_id = employee_work_assignment.employee_id');	
		$this->db->group_by('employee.employee_id');
		// $this->db->group_by('employee_work_assignment.employee_id');
		$result = $this->db->get('user');

		$as_of_last_dec_support = $result->num_rows();
		//end support previous december


		$count_cbe_init = $as_of_last_dec_cbe;
		$count_non_cbe_init = $as_of_last_dec_none_cbe;
		$count_support_init = $as_of_last_dec_support;
		$ctr = 0;
		while ($tstamp_cdate <=  $tstampdateto) {
			$start_date = date('Y-m-d',$tstamp_cdate);
			$end_date = date('Y-m-t',$tstamp_cdate);

			if (date('Y-m',$tstamp_cdate) == date('Y-m',strtotime($month_year_to))){
				$end_date = date('Y-m-d');				
			}

			//cbe
			$this->db->where('employee.cbe',1);
			$this->db->where('user.deleted',0);		
			$this->db->where('employee.deleted',0);		
			$this->db->where('employee.resigned_date', null);
			$this->db->where('employee.employed_date BETWEEN "'.$start_date.'" AND "'.$end_date.'"');
			$this->db->where('employee.employed_date <>', '0000-00-00');			
			$this->db->join('employee', 'employee.employee_id = user.user_id');
			$this->db->group_by('employee.employee_id');
			$result = $this->db->get('user');
			
			$count_cbe = $result->num_rows();

			if ($ctr == 0){
				$count_cbe += $count_cbe_init;
			}
			else{
				$count_cbe += $arr_cbe[$ctr - 1];
			}

			//non cbe
			$this->db->where('employee.cbe',0);
			$this->db->where('user.deleted',0);		
			$this->db->where('employee.deleted',0);		
			$this->db->where('employee.resigned_date', null);
			$this->db->where('employee.employed_date BETWEEN "'.$start_date.'" AND "'.$end_date.'"');
			$this->db->where('employee.employed_date <>', '0000-00-00');			
			$this->db->join('employee', 'employee.employee_id = user.user_id');
			$this->db->group_by('employee.employee_id');
			$result = $this->db->get('user');
			
			$count_non_cbe = $result->num_rows();

			if ($ctr == 0){
				$count_non_cbe += $count_non_cbe_init;
			}
			else{
				$count_non_cbe += $arr_non_cbe[$ctr - 1];
			}

			//support
			$where = "FIND_IN_SET('6', segment_2_id)";  
			$this->db->where($where);		
			$this->db->where('employee.cbe',1);				
			$this->db->where('user.deleted',0);
			$this->db->where('employee.deleted',0);	
			$this->db->where('user.division_id',0);
			// $this->db->where('employee_work_assignment.assignment',1);
			$this->db->where('employee.resigned_date', null);
			$this->db->where('employee.employed_date BETWEEN "'.$start_date.'" AND "'.$end_date.'"');
			$this->db->where('employee.employed_date <>', '0000-00-00');			
			$this->db->where('employee.cbe',1);			
			$this->db->join('employee', 'employee.employee_id = user.user_id');
			// $this->db->join('employee_work_assignment', 'employee.employee_id = employee_work_assignment.employee_id');
			$this->db->group_by('employee.employee_id');
			$result = $this->db->get('user');
			
			$count_support = $result->num_rows();

			if ($ctr == 0){
				$count_support += $count_support_init;
			}
			else{
				$count_support += $arr_support[$ctr - 1];
			}

			$arr_cbe[] = $count_cbe;
			$arr_non_cbe[] = $count_non_cbe;
			$arr_support[] = $count_support;

			$cdate = date('Y-m-d', strtotime('+1 month', $tstamp_cdate));
			$tstamp_cdate = strtotime($cdate);		
			$ctr++;
		}

		$ytd_ave_cbe = array_sum($arr_cbe) / $count_month;
		$ytd_ave_none_cbe = array_sum($arr_non_cbe) / $count_month;
		$ytd_ave_support = array_sum($arr_support) / $count_month;

		$result_array['cbe_prev_mo'] = $arr_cbe[$ctr - 2];
		$result_array['cbe_current_mo'] = $arr_cbe[$ctr - 1];
		$result_array['cbe_ytd'] = round($ytd_ave_cbe);
		$result_array['noncbe_prev_mo'] = $arr_non_cbe[$ctr - 2];
		$result_array['noncbe_current_mo'] = $arr_non_cbe[$ctr - 1];
		$result_array['noncbe_ytd'] = round($ytd_ave_none_cbe);
		$result_array['support_prev_mo'] = $arr_support[$ctr - 2];
		$result_array['support_current_mo'] = $arr_support[$ctr - 1];
		$result_array['support_ytd'] = round($ytd_ave_support);

		return $result_array;
	}

	function get_count_per_division_ytd_ave($month_year_from,$month_year_to,$count_month,$division_id){
		$result_array = array();
		$tstampdateto = strtotime($month_year_to);			
		$tstamp_cdate = strtotime($month_year_from);
		$arr = array();

		//get all records as of last dec for cbe
		$last_year_date = date('Y-m-t',strtotime(date('Y-01-01') . '-1 month'));

		$this->db->where('user.division_id',$division_id);		
		$this->db->where('user.deleted',0);
		$this->db->where('employee.deleted',0);	
		$this->db->where('employee.cbe',1);	
		// $this->db->where('assignment',1);	
		$this->db->where('employee.resigned_date', null);
		$this->db->where('employee.employed_date <=', $last_year_date);
		$this->db->where('employee.employed_date <>', '0000-00-00');	
		$this->db->join('employee', 'user.employee_id = employee.employee_id');		
		// $this->db->join('user', 'user.user_id = employee.employee_id');
		$this->db->group_by('employee.employee_id');
		$result = $this->db->get('user');
			
		$as_of_last_dec = $result->num_rows();
		//per division previous december

		$count_init = $as_of_last_dec;
		$ctr = 0;
		while ($tstamp_cdate <=  $tstampdateto) {
			$start_date = date('Y-m-d',$tstamp_cdate);
			$end_date = date('Y-m-t',$tstamp_cdate);

			if (date('Y-m',$tstamp_cdate) == date('Y-m',strtotime($month_year_to))){
				$end_date = date('Y-m-d');				
			}

			$this->db->where('user.division_id',$division_id);		
			$this->db->where('user.deleted',0);
			$this->db->where('employee.deleted',0);	
			$this->db->where('employee.cbe',1);		
			// $this->db->where('assignment',1);
			$this->db->where('employee.resigned_date', null);
			$this->db->where('employee.employed_date BETWEEN "'.$start_date.'" AND "'.$end_date.'"');
			$this->db->where('employee.employed_date <>', '0000-00-00');	
			$this->db->join('employee', 'user.employee_id = employee.employee_id');		
			// $this->db->join('user', 'user.user_id = employee.employee_id');
			$this->db->group_by('employee.employee_id');
			$result = $this->db->get('user');
			$count = $result->num_rows();
			
			if ($ctr == 0){
				$count += $count_init;
			}
			else{
				$count += $arr[$ctr - 1];
			}

			$arr[] = $count;

			$cdate = date('Y-m-d', strtotime('+1 month', $tstamp_cdate));
			$tstamp_cdate = strtotime($cdate);		
			$ctr++;
		}

		$ytd_ave = array_sum($arr) / $count_month;

		$result_array['count_prev_mo'] = $arr[$ctr - 2];
		$result_array['count_current_mo'] = $arr[$ctr - 1];
		$result_array['ytd'] = round($ytd_ave);

		return $result_array;
	}

/*	function get_cbe_non_cbe($prev_mo,$today){
		$start_month = date('Y-m-01',strtotime(date('Y-01-01',strtotime($prev_mo)) . '-1 month'));

		//resigned cbe
		$this->db->where('employee.cbe',1);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date <=', $prev_mo);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');

		$count_resigned_cbe_prev_mo = $result->num_rows();

		$this->db->where('employee.cbe',1);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date <=', $today);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');

		$count_resigned_cbe_today = $result->num_rows();

		//resigned non cbe
		$this->db->where('employee.cbe',0);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date <=', $prev_mo);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');

		$count_resigned_non_cbe_prev_month = $result->num_rows();

		$this->db->where('employee.cbe',0);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date <=', $today);
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');

		$count_resigned_non_cbe_today = $result->num_rows();

		//cbe
		$this->db->start_cache();
		$this->db->where('employee.cbe',1);
		$this->db->where('user.deleted',0);		
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date', null);
		$this->db->stop_cache();

		$this->db->where('employee.employed_date <=', $prev_mo);
		//$this->db->where('employee.employed_date BETWEEN "'.$start_month.'" AND "'.$prev_mo.'"');
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$result_array['cbe_prev_mo'] = $result->num_rows() - $count_resigned_cbe_prev_mo;

		$this->db->where('employee.employed_date <=', $today);
		//$this->db->where('employee.employed_date BETWEEN "'.$start_month.'" AND "'.$today.'"');
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$result_array['cbe_current_mo'] = $result->num_rows() - $count_resigned_cbe_today ;

		$this->db->flush_cache();

		//non cbe
		$this->db->start_cache();
		//$where = "!FIND_IN_SET('1', segment_2_id)";  
		//$this->db->where($where);
		//$this->db->where('segment_2_id <>','');		
		$this->db->where('employee.cbe',0);		
		$this->db->where('user.deleted',0);
		$this->db->where('employee.deleted',0);		
		$this->db->where('employee.resigned_date', null);
		$this->db->stop_cache();
		
		$this->db->where('employee.employed_date <=', $prev_mo);
		//$this->db->where('employee.employed_date BETWEEN "'.$start_month.'" AND "'.$prev_mo.'"');
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');

		$result_array['noncbe_prev_mo'] = $result->num_rows() - $count_resigned_non_cbe_prev_month;

		$this->db->where('employee.employed_date <=', $today);
		//$this->db->where('employee.employed_date BETWEEN "'.$start_month.'" AND "'.$today.'"');
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$result_array['noncbe_current_mo'] = $result->num_rows();

		$this->db->flush_cache();

		// support
		$this->db->start_cache();
		$where = "FIND_IN_SET('3', segment_2_id)";  
		$this->db->where($where);		
		$this->db->where('user.deleted',0);
		$this->db->where('employee.resigned_date', null);
		$this->db->stop_cache();
		
		$this->db->where('employee.employed_date <=', $prev_mo);
		//$this->db->where('employee.employed_date BETWEEN "'.$start_month.'" AND "'.$prev_mo.'"');
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');

		$result_array['support_prev_mo'] = $result->num_rows() - $count_resigned_non_cbe_today;

		$this->db->where('employee.employed_date <=', $today);
		//$this->db->where('employee.employed_date BETWEEN "'.$start_month.'" AND "'.$today.'"');
		$this->db->join('employee', 'employee.employee_id = user.user_id');
		$result = $this->db->get('user');
		$result_array['support_current_mo'] = $result->num_rows();

		$this->db->flush_cache();

		return $result_array;
	}

	function get_count_per_division($prev_mo,$today,$division_id){

		$start_month = date('Y-m-01',strtotime(date('Y-01-01',strtotime($prev_mo)) . '-1 month'));

		$this->db->start_cache();
		$this->db->where('employee_work_assignment.division_id',$division_id);		
		$this->db->where('user.deleted',0);
		$this->db->where('employee.deleted',0);
		//$where = "FIND_IN_SET('1', segment_2_id)";  
		//$this->db->where($where);
		//$this->db->where('segment_2_id <>','');		
		$this->db->where('employee.cbe',1);		
		$this->db->where('employee.resigned_date', null);
		$this->db->stop_cache();

		$this->db->where('employee.employed_date <=', $prev_mo);
		//$this->db->where('employee.employed_date BETWEEN "'.$start_month.'" AND "'.$prev_mo.'"');
		$this->db->join('employee', 'employee_work_assignment.employee_id = employee.employee_id COLLATE latin1_general_ci');		
		$this->db->join('user', 'user.user_id = employee.employee_id');
		$result = $this->db->get('employee_work_assignment');
			
		$result_array['count_prev_mo'] = $result->num_rows();

		$this->db->where('employee.employed_date <=', $today);
		//$this->db->where('employee.employed_date BETWEEN "'.$start_month.'" AND "'.$today.'"');
		$this->db->join('employee', 'employee_work_assignment.employee_id = employee.employee_id COLLATE latin1_general_ci');		
		$this->db->join('user', 'user.user_id = employee.employee_id');
		$result = $this->db->get('employee_work_assignment');
		$result_array['count_current_mo'] = $result->num_rows();

		$this->db->flush_cache();

		return $result_array;
	}*/

/*	function get_birthday_celebrants() 
	{
		$is_group = $this->db->get_where('user_position', array('deleted' => 0, 'position_id' => $this->userinfo['position_id']))->row();
		$portlet_config = unserialize($is_group->portlet_config);

		$where_in = array();
		if( !($this->is_superadmin || $this->is_admin)  ){
			$where_in = $this->system->get_employee_circle( $this->user->user_id );
		}

		$celebrant_dates = array('today', 'tomorrow', 'later', 'past');

		foreach ($celebrant_dates as $date)
		{
			switch ($date) {
				case 'today':
					$this->db->like('birth_date', date('-m-d'), 'before');
					break;
				case 'tomorrow':
					$date_tomorrow = date('-m-d', strtotime('tomorrow', strtotime(date('Y-m-d'))));
					$this->db->like('birth_date', $date_tomorrow, 'before');
					break;
				case 'later':
					$ctr = 2;
					$where = "( ";
					while ($ctr <= 7) {						
						$like = date('-m-d', strtotime('+' . $ctr . ' days', strtotime(date('Y-m-d'))));
						$ctr++;
						$where .= " birth_date LIKE '%".$like."'";
						if($ctr != 8)
							$where .= " OR ";
					}
					$where .= " )";
					$this->db->where($where);
					break;
				case 'past':
					$this->load->helper('date');
					$today = date('-m-d');
					$month = date('-m');
					$day = $month.'-01';
					$ctr = 1;

					if($day == $today)
						break;
					
					$where = "( ";
					while(  $day != $today ){
						$where .= " birth_date LIKE '%".$day."'";
						if($day != date('-m-d', strtotime("yesterday")))
							$where .= " OR ";
						$ctr++;
						$day = $month.'-'.sprintf("%02d", $ctr);
					}
					$where .=" )";
					$this->db->where($where);
					break;	
			}

			if(sizeof($where_in) > 0 && $portlet_config[4]['access'] == "group") $this->db->where_in('user.user_id', $where_in);
			$this->db->where('user.deleted', 0);
			$this->db->where('employee.resigned_date', null);
			$this->db->join('user_position', 'user.position_id = user_position.position_id');
			$this->db->join('employee', 'employee.user_id = user.user_id');
			$this->db->select('user.*, user_position.position');
			$this->db->limit('30');
			$this->db->order_by('cbe','DESC');
			$this->db->order_by('birth_date','ASC');
			$result = $this->db->get('user');
			$celebrants[$date] = array();

			if ($result->num_rows() > 0) {
				$celebrants[$date] = $result->result();
			}			
		}

		return $celebrants;
	}	
*/
	function get_birthday_celebrants() 
	{
		$is_group = $this->db->get_where('user_position', array('deleted' => 0, 'position_id' => $this->userinfo['position_id']))->row();
		$portlet_config = unserialize($is_group->portlet_config);

		$where_in = array();				
		if(!$this->user_access[$this->module_id]['post']) {
			$where_in = $this->system->get_employee_circle( $this->user->user_id,4);
		}

		$celebrant_dates = array('today', 'tomorrow', 'later', 'past');

		$where = 1;
		foreach ($celebrant_dates as $date)
		{
			switch ($date) {
				case 'today':
					$where = " birth_date LIKE '%".date('-m-d')."'";
					break;
				case 'tomorrow':
					$date_tomorrow = date('-m-d', strtotime('tomorrow', strtotime(date('Y-m-d'))));
					$where = " birth_date LIKE '%".$date_tomorrow."'";
					break;
				case 'later':
					$ctr = 2;
					$where = "( ";
					while ($ctr <= 7) {						
						$like = date('-m-d', strtotime('+' . $ctr . ' days', strtotime(date('Y-m-d'))));
						$ctr++;
						$where .= " birth_date LIKE '%".$like."'";
						if($ctr != 8)
							$where .= " OR ";
					}
					$where .= " )";
					break;
				case 'past':
					$this->load->helper('date');

					if (date('d') == '01'){
						$month = date('-m',strtotime('-1 month'));
						$cur_day = date('t',strtotime('-1 month'));
					}
					else{
						$month = date('-m');
						$cur_day = date('d') - 1;						
					}

					$ctr = 1;

					$where = "( ";
					while( $cur_day >= 1 ){
						$day = $month.'-'.sprintf("%02d", $cur_day);
						$where .= " birth_date LIKE '%".$day."'";
						if($cur_day != 1)
							$where .= " OR ";
						$cur_day--;
					}
					$where .=" )";
					break;	
			}

			$celebrants[$date] = array();

			$sql = "SELECT 
						u.*,
						up.position 
					FROM {$this->db->dbprefix}user u
						JOIN {$this->db->dbprefix}user_position up 
							ON u.position_id = up.position_id
						JOIN {$this->db->dbprefix}employee e 
							ON u.employee_id = e.employee_id
					WHERE {$where}";

			if($portlet_config[4]['access'] == 'personal') {
				$user_id = $this->user->user_id;
				$sql .= " AND u.user_id = '{$user_id}'";
			}
			// if(sizeof($where_in) > 0 && $portlet_config[4]['access'] == "group"){
			if(sizeof($where_in) > 0){
				$whereinexp = implode(',', $where_in);
				$sql .= " AND u.user_id IN ({$whereinexp})";
			}

			$sql .= " AND u.deleted = 0 AND e.resigned_date IS NULL ORDER BY DATE_FORMAT(birth_date,'%m,%d') DESC LIMIT 30";
			$result = $this->db->query($sql);

			if ($result && $result->num_rows() > 0) {
				$celebrants[$date] = $result->result();
			}			
		}

		return $celebrants;
	}	

	function fetch_all()
	{
		$this->db->where('comments.deleted', 0);
		$this->db->where('user.deleted', 0);

		$this->db->join('user', 'user.user_id = comments.user_id');
		$this->db->select('comments.*, user.firstname, user.lastname');

		$this->db->order_by('comment_id', 'desc');

		$comments = $this->db->get('comments');

		if ($comments->num_rows() > 0) {
			return $comments->result();
		} else {
			return FALSE;
		}		
	}

	function fetch_user_comments($user_id, $limit = null)
	{
		$this->db->where('comments.user_id', $user_id);

		if (!is_null($limit)) {
			$this->db->limit($limit);
		}

		return $this->fetch_all();
	}

	function fetch_comment_group($identifier)
	{
		$this->db->where('comment_group_identifier', $identifier);

		return $this->fetch_all();
	}
	
	function _get_comment_group_identifier($celebrant)
	{
		return md5($celebrant->employee_id . date('Ymd', strtotime($celebrant->birth_date)) . 'celebrants');
	}

	function _get_memo_comment_group_identifier($memo)
	{
		return md5($memo['memo_id'] . 'memo');
	}

	function _get_employee_comment_group_identifier($employee)
	{
		return md5($employee['memo_id'] . 'employee_updates');
	}

	//get list of users who are out of office given a particular day
	function get_out_of_office_users( $date = "" ){
		$users = array();
		
		$sl = $this->get_users_on_leave( $date, 1 );
		$vl = $this->get_users_on_leave( $date, 2 );
		$el = $this->get_users_on_leave( $date, 3 );
		$bl = $this->get_users_on_leave( $date, 4 );
		$ml = $this->get_users_on_leave( $date, 5 );
		$pl = $this->get_users_on_leave( $date, 6 );
		$lwop = $this->get_users_on_leave( $date, 7 );
		$obt = $this->get_users_on_leave( $date, 8 );
		
		
		
		return $users;
	}
	
	//get a list of users on leave given a date and a type of leave
	function get_users_on_leave( $date = "", $leave_type_id = 0 ){
		
	}
	
	function get_user_leaves( $user_id = 0, $limit = "" ){
		if( !empty( $user_id ) ) $this->db->where_in('user.user_id' , $user_id);
		$this->db->where('user.deleted', 0);
		$this->db->where('employee_leaves.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_leaves', 'employee_leaves.employee_id = user.employee_id');		
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id');
		$this->db->join('form_status', 'form_status.form_status_id = employee_leaves.form_status_id');
		$this->db->select('user.lastname, user.firstname, employee_form_type.application_form, 
			employee_leaves.date_created, employee_leaves.employee_leave_id, employee_leaves.reason, employee_leaves.approvers, employee_leaves.approver_status, employee_leaves.form_status_id');
		if( $limit != "" ){
			$this->db->limit($limit);
		}
		$this->db->order_by('employee_leaves.date_from', 'DESC');
		$result = $this->db->get('user');

		$leaves = array();

		if ($result->num_rows() > 0) {
			$leaves = $result->result();
		}	

		return $leaves;
	}

	function get_user_oot( $user_id = 0, $limit = "" ){

		if( !empty( $user_id ) ) $this->db->where_in('user.user_id' , $user_id);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_oot.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_oot', 'employee_oot.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_oot.date_created, employee_oot.reason, employee_oot.employee_oot_id, employee_oot.approvers, employee_oot.approver_status, employee_oot.form_status_id');
		if( $limit != "" ){
			$this->db->limit($limit);
		}
		$this->db->order_by('employee_oot.date', 'DESC');
		$result = $this->db->get('user');

		$oot = array();

		if ($result->num_rows() > 0) {
			$oot = $result->result();
		}	
		return $oot;
	}

	function get_user_obt( $user_id = 0, $limit = "" ){
		if( !empty( $user_id ) ) $this->db->where_in('user.user_id' , $user_id);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_obt.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_obt', 'employee_obt.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_obt.date_created, employee_obt.reason, employee_obt.employee_obt_id, employee_obt.approvers, employee_obt.approver_status, employee_obt.form_status_id');
		if( $limit != "" ){
			$this->db->limit($limit);
		}
		$this->db->order_by('employee_obt.date_from', 'DESC');
		$result = $this->db->get('user');

		$obt = array();

		if ($result->num_rows() > 0) {
			$obt = $result->result();
		}	
		return $obt;
	}


	function get_user_out( $user_id = 0, $limit = "" ){
		if( !empty( $user_id ) ) $this->db->where_in('user.user_id' , $user_id);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_out.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_out', 'employee_out.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_out.employee_out_id, employee_out.date_created, employee_out.reason, employee_out.approvers, employee_out.approver_status, employee_out.form_status_id');
		if( $limit != "" ){
			$this->db->limit($limit);
		}
		$this->db->order_by('employee_out.date', 'DESC');
		$result = $this->db->get('user');
		
		$out = array();

		if ($result->num_rows() > 0) {
			$out = $result->result();
		}	
		return $out;
	}

	function get_user_et( $user_id = 0, $limit = "" ){
		if( !empty( $user_id ) ) $this->db->where_in('user.user_id' , $user_id);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_et.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_et', 'employee_et.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_et.date_created, employee_et.employee_et_id, employee_et.reason, employee_et.approvers, employee_et.approver_status, employee_et.form_status_id');
		if( $limit != "" ){
			$this->db->limit($limit);
		}
		$this->db->order_by('employee_et.datelate', 'DESC');
		$result = $this->db->get('user');

		$et = array();

		if ($result->num_rows() > 0) {
			$et = $result->result();
		}	
		return $et;
	}

	function get_user_dtrp( $user_id = 0, $limit = "" ){
		if( !empty( $user_id ) ) $this->db->where_in('user.user_id' , $user_id);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_dtrp.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_dtrp', 'employee_dtrp.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_dtrp.date_created, employee_dtrp.employee_dtrp_id, employee_dtrp.reason, employee_dtrp.approvers, employee_dtrp.approver_status, employee_dtrp.form_status_id');
		if( $limit != "" ){
			$this->db->limit($limit);
		}
		$this->db->order_by('employee_dtrp.date', 'DESC');
		$result = $this->db->get('user');

		$dtrp = array();

		if ($result->num_rows() > 0) {
			$dtrp = $result->result();
		}	
		return $dtrp;
	}

	function get_user_cws( $user_id = 0, $limit = "" ){
		if( !empty( $user_id ) ) $this->db->where_in('user.user_id' , $user_id);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_cws.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_cws', 'employee_cws.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_cws.date_created, employee_cws.employee_cws_id, employee_cws.reason, employee_cws.approvers, employee_cws.approver_status, employee_cws.form_status_id');
		if( $limit != "" ){
			$this->db->limit($limit);
		}
		$this->db->order_by('employee_cws.date_from', 'DESC');
		$result = $this->db->get('user');

		$dtrp = array();

		if ($result->num_rows() > 0) {
			$dtrp = $result->result();
		}	
		return $dtrp;
	}


	function get_no_approval( $approver_id = 0 ){

		$approvals = "";

		$this->db->where('leave_approver.approver', $approver_id);
		$this->db->where('leave_approver.focus', 1);
		$this->db->where('leave_approver.status', 2);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_leaves.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_leaves', 'employee_leaves.employee_id = user.employee_id');
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id');

		$this->db->join('leave_approver', 'leave_approver.leave_id = employee_leaves.employee_leave_id');

		$this->db->join('form_status', 'form_status.form_status_id = employee_leaves.form_status_id');
		$this->db->select('user.lastname, user.firstname, employee_form_type.application_form, 
			employee_leaves.date_created, employee_leaves.reason, employee_leaves.approvers, employee_leaves.approver_status, employee_leaves.form_status_id');
		
		$result = $this->db->get('user');

		if ($result->num_rows() > 0) {

			$record = $result->result();
			foreach($record as $field){
				$approvals++;
			}
		}

		$this->db->where('class_name','Oot');
		$module_id=$this->db->get('module')->row_array();
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);


		$this->db->where('employee_oot.form_status_id', 2);
		$this->db->where('user.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_oot', 'employee_oot.employee_id = user.employee_id');

		$this->db->join('form_approver','employee_oot.employee_oot_id = form_approver.record_id');

		$this->db->select('user.lastname, user.firstname, employee_oot.date_created, employee_oot.reason, employee_oot.approvers, employee_oot.approver_status, employee_oot.form_status_id');
		
		$result = $this->db->get('user');

		if ($result->num_rows() > 0) {
			
			$record = $result->result();
			foreach($record as $field){

				// if ($field->approvers <> ""){
				// 	$approvers = unserialize($field->approvers);
				// 	if(in_array($this->user->user_id, $approvers)){
				// 		$approvals = $approvals+1;
				// 	}
				// }
				$approvals = $approvals+1;
				//$approvals .= '  oot  ';
			}
		}

		$this->db->where('class_name','Obt');
		$module_id=$this->db->get('module')->row_array();
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);

		$this->db->where('employee_obt.form_status_id', 2);
		$this->db->where('user.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_obt', 'employee_obt.employee_id = user.employee_id');

		$this->db->join('form_approver','employee_obt.employee_obt_id = form_approver.record_id');

		$this->db->select('user.lastname, user.firstname, employee_obt.date_created, employee_obt.reason, employee_obt.approvers, employee_obt.approver_status, employee_obt.form_status_id');
		$result = $this->db->get('user');

		if ($result->num_rows() > 0) {
			
			$record = $result->result();
			foreach($record as $field){

				// if ($field->approvers <> ""){
				// 	$approvers = unserialize($field->approvers);
				// 	if(in_array($this->user->user_id, $approvers)){
				// 		$approvals++;
				// 	}
				// }
				$approvals++;
				//$approvals .= '  obt  ';
			}
		}

		$this->db->where('class_name','Out');
		$module_id=$this->db->get('module')->row_array();
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_out.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_out', 'employee_out.employee_id = user.employee_id');

		$this->db->join('form_approver','employee_out.employee_out_id = form_approver.record_id');

		$this->db->select('user.lastname, user.firstname, employee_out.date_created, employee_out.reason, employee_out.approvers, employee_out.approver_status, employee_out.form_status_id');

		$result = $this->db->get('user');

		if ($result->num_rows() > 0) {
			
			$record = $result->result();
			foreach($record as $field){

				// if ($field->approvers <> ""){
				// 	$approvers = unserialize($field->approvers);
				// 	if(in_array($this->user->user_id, $approvers)){
				// 		$approvals++;
				// 	}
				// }
				$approvals++;
				//$approvals .= '  out  ';
			}
		}

		$this->db->where('class_name','Et');
		$module_id=$this->db->get('module')->row_array();
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_et.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_et', 'employee_et.employee_id = user.employee_id');

		$this->db->join('form_approver','employee_et.employee_et_id = form_approver.record_id');

		$this->db->select('user.lastname, user.firstname, employee_et.date_created, employee_et.reason, employee_et.approvers, employee_et.approver_status, employee_et.form_status_id');
		
		$result = $this->db->get('user');

		if ($result->num_rows() > 0) {
			
			$record = $result->result();
			foreach($record as $field){
				
				// if($field->approvers <> ""){
				// 	$approvers = unserialize($field->approvers);
				// 	if(in_array($this->user->user_id, $approvers)){
				// 		$approvals++;
				// 	}
				// }
				$approvals++;
				//$approvals .= '  et  ';
			}
		}

		$this->db->where('class_name','Dtrp');
		$module_id=$this->db->get('module')->row_array();
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_dtrp.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_dtrp', 'employee_dtrp.employee_id = user.employee_id');

		$this->db->join('form_approver','employee_dtrp.employee_dtrp_id = form_approver.record_id');

		$this->db->select('user.lastname, user.firstname, employee_dtrp.date_created, employee_dtrp.reason, employee_dtrp.approvers, employee_dtrp.approver_status, employee_dtrp.form_status_id');
		
		$result = $this->db->get('user');

		if ($result->num_rows() > 0) {
			
			$record = $result->result();
			foreach($record as $field){

				// if ($field->approvers <> ""){
				// 	$approvers = unserialize($field->approvers);
				// 		if(in_array($this->user->user_id, $approvers)){
				// 			$approvals++;
				// 		}
				// }
				$approvals++;
				//$approvals .= '  dtrp  ';
			}
		}

		$this->db->where('class_name','cws');
		$module_id=$this->db->get('module')->row_array();
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_cws.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_cws', 'employee_cws.employee_id = user.employee_id');

		$this->db->join('form_approver','employee_cws.employee_cws_id = form_approver.record_id');

		$this->db->select('user.lastname, user.firstname, employee_cws.date_created, employee_cws.reason, employee_cws.approvers, employee_cws.approver_status, employee_cws.form_status_id');
		
		$result = $this->db->get('user');

		if ($result->num_rows() > 0) {
			
			$record = $result->result();
			foreach($record as $field){
				$approvers = unserialize($field->approvers);
				if ($approvers <> "")
				{
					if(in_array($this->user->user_id, $approvers)){
						$approvals++;
					}
				}
			}
		}

		return $approvals;

	}

	function get_leaves_approval($user_id = false){
    	$leaves_to_approve = $this->system->get_leaves_to_approve( $user_id, '= 2', '= 1', true );
    	$this->db->join('user','user.employee_id = employee_leaves.employee_id');
    	$this->db->where('form_status_id', 2);
    	$this->db->where_in('employee_leave_id', $leaves_to_approve);
    	$this->db->order_by('date_from', 'asc');
    	$for_approval = $this->db->get( "employee_leaves");

    	return $for_approval;
	}

	function get_leaves_for_validation($user_id = false){
    	$qry = "SELECT a.*
		FROM {$this->db->dbprefix}leave_approver a
		LEFT JOIN {$this->db->dbprefix}employee_leaves b ON b.employee_leave_id = a.leave_id
		WHERE {$this->user->user_id} AND b.form_status_id = 6 AND a.focus {$focus} AND
		b.deleted = 0 AND a.status = 6";
        $leaves_to_validate = $this->db->query( $qry );

        if( $leaves_to_validate->num_rows() > 0 ){
        	foreach( $leaves_to_validate->result() as $leave ){
        		$leaves_array[] = $leave->leave_id;
        	}
        }  

    	$this->db->join('user','user.employee_id = employee_leaves.employee_id');
    	$this->db->where('form_status_id', 2);
    	$this->db->where_in('employee_leave_id', $leaves_to_approve);
    	$this->db->order_by('date_from', 'asc');
    	$for_approval = $this->db->get( "employee_leaves");

    	return $for_approval;
	}

	function get_all_forms(){

	}
	
	function get_subordinate_leaves( $user_id = 0 ){

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_leaves.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_leaves', 'employee_leaves.employee_id = user.employee_id');
		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id');
		$this->db->join('form_status', 'form_status.form_status_id = employee_leaves.form_status_id');
		$this->db->select('user.lastname, user.middlename, user.firstname, employee_form_type.application_form, employee_leaves.date_created, employee_leaves.reason, employee_leaves.employee_leave_id, employee_leaves.approvers, employee_leaves.approver_status, employee_leaves.form_status_id');
		$result = $this->db->get('user');

		$leaves = array();

		if ($result->num_rows() > 0) {
			$leaves = $result->result();
		}	

		return $leaves;
	}

    function get_sub_leaves_to_approve($employee_id, $status = 2, $focus = 1){
    	
    	$qry = "SELECT *
		FROM {$this->db->dbprefix}leave_approver a
		LEFT JOIN {$this->db->dbprefix}employee_leaves b ON b.employee_leave_id = a.leave_id
		LEFT JOIN {$this->db->dbprefix}employee c ON b.employee_id = c.employee_id
		LEFT JOIN {$this->db->dbprefix}user d ON c.employee_id = d.employee_id
		LEFT JOIN {$this->db->dbprefix}employee_form_type e ON b.application_form_id = e.application_form_id		
		WHERE a.approver = {$employee_id} AND b.form_status_id = 2 AND a.focus = {$focus} AND
		b.deleted = 0 AND a.status = {$status} ORDER BY date_from";
        $result = $this->db->query( $qry );

		$leaves = array();

		if ($result->num_rows() > 0) {
			$leaves = $result->result();
		}	

		return $leaves;
    }

    function get_training($employee_id)
    {
    	$training_application = false;
    		$qry = "SELECT *
						FROM {$this->db->dbprefix}training_application a
						LEFT JOIN {$this->db->dbprefix}employee c ON a.employee_id = c.employee_id
						LEFT JOIN {$this->db->dbprefix}user d ON c.employee_id = d.employee_id
						LEFT JOIN {$this->db->dbprefix}training_application_status e ON a.status = e.training_application_status_id		
						WHERE a.deleted = 0 AND a.employee_id = {$employee_id}";
	        
	        $result = $this->db->query( $qry );		
	        
			if ($result && $result->num_rows() > 0) {
				$training_application = array();
				$training_application['count'] = 0;
				foreach ($result->result() as $key => $training) {
					if ($training->training_application_type == 1) {
						$training_application['epaf'][$training->training_application_status] += 1;
					}else{
						$training_application['pgsa'][$training->training_application_status] += 1;
					}
				
				}
			}

			$live_qry = "SELECT *
						FROM {$this->db->dbprefix}training_live a
						LEFT JOIN {$this->db->dbprefix}employee c ON a.employee_id = c.employee_id
						LEFT JOIN {$this->db->dbprefix}user d ON c.employee_id = d.employee_id
						LEFT JOIN {$this->db->dbprefix}training_live_status e ON a.status_id = e.training_live_status_id		
						WHERE a.deleted = 0 AND a.employee_id = {$employee_id} ";
	        $live = $this->db->query( $live_qry );
	     
	        if ($live && $live->num_rows() > 0) {
				foreach ($live->result() as $key => $training) {
					
					$training_application['live'][$training->training_live_status] += 1;
				}

			}

			return $training_application;
    }


    function get_sub_training($employee_id, $to_approve, $to_review, $to_evaluate){
    	
    	$training_application = false;
    	
    	if ($to_approve && !$to_evaluate) { // with subordinates

    		$qry = "SELECT *
						FROM {$this->db->dbprefix}training_approver a
						LEFT JOIN {$this->db->dbprefix}training_application b ON b.training_application_id = a.training_application_id
						LEFT JOIN {$this->db->dbprefix}employee c ON b.employee_id = c.employee_id
						LEFT JOIN {$this->db->dbprefix}user d ON c.employee_id = d.employee_id
						LEFT JOIN {$this->db->dbprefix}training_application_status e ON b.status = e.training_application_status_id		
						WHERE a.approver = {$employee_id} AND b.deleted = 0 AND b.status != 3 ";
	        $result = $this->db->query( $qry );		

			if ($result && $result->num_rows() > 0) {
				$training_application = array();
				$training_application['count'] = 0;

				foreach ($result->result() as $key => $training) {
					if ($training->training_application_type == 1) {
						$training_application['epaf'][] = $training;
					}else{
						$training_application['pgsa'][] = $training;
					}
					if ($training->status == 4) {
						$training_application['count'] += 1;	
					}
				}
			}

			return $training_application;	

    	}elseif($to_review && !$to_evaluate){ // for hr_review or with post access

    		$qry = "SELECT *
						FROM {$this->db->dbprefix}training_application a
						LEFT JOIN {$this->db->dbprefix}employee c ON a.employee_id = c.employee_id
						LEFT JOIN {$this->db->dbprefix}user d ON c.employee_id = d.employee_id
						LEFT JOIN {$this->db->dbprefix}training_application_status e ON a.status = e.training_application_status_id		
						WHERE a.deleted = 0 ";
	        $result = $this->db->query( $qry );		
	        
			if ($result && $result->num_rows() > 0) {
				$training_application = array();
				$training_application['count'] = 0;
				foreach ($result->result() as $key => $training) {
					if ($training->training_application_type == 1) {
						$training_application['epaf'][] = $training;
					}else{
						$training_application['pgsa'][] = $training;
					}
					if (in_array($training->status, array(3,4))) {
						$training_application['count'] += 1;	
					}
					
				}
			}

			return $training_application;	

    	}elseif (($to_evaluate) && ($to_review || $to_approve)) {
    		
    		$qry = "SELECT *
						FROM {$this->db->dbprefix}training_live a
						LEFT JOIN {$this->db->dbprefix}employee c ON a.employee_id = c.employee_id
						LEFT JOIN {$this->db->dbprefix}user d ON c.employee_id = d.employee_id
						LEFT JOIN {$this->db->dbprefix}training_live_status e ON a.status_id = e.training_live_status_id		
						WHERE a.deleted = 0 AND a.employee_id IN (".implode(',', $employee_id).") ";
	        $result = $this->db->query( $qry );
	        // dbug($qry);
	        if ($result && $result->num_rows() > 0) {
				$training_application = array();
				
				foreach ($result->result() as $key => $training) {
					
					$training_application[$training->training_live_status] += 1;
				}

			}else{
				$training_application = false;
			}

    		return $training_application;	
    	}
    	
		
    }

	function get_sub_leaves( $approver_id = 0 )
	{
		$this->db->where('leave_approver.approver', $approver_id);
		$this->db->where('leave_approver.focus', 1);
		$this->db->where('leave_approver.status', 2);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_leaves.form_status_id', 2);
		$this->db->where('employee_leaves.deleted',0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_leaves', 'employee_leaves.employee_id = user.employee_id');

		$this->db->join('leave_approver', 'leave_approver.leave_id = employee_leaves.employee_leave_id');

		$this->db->join('employee_form_type', 'employee_form_type.application_form_id = employee_leaves.application_form_id');
		$this->db->join('form_status', 'form_status.form_status_id = employee_leaves.form_status_id');
		$this->db->select('user.lastname, user.middlename, user.firstname, employee_form_type.application_form, employee_leaves.date_created, employee_leaves.reason, employee_leaves.employee_leave_id, employee_leaves.approvers, employee_leaves.approver_status, employee_leaves.form_status_id');
		$result = $this->db->get('user');

		$leaves = array();

		if ($result->num_rows() > 0) {
			$leaves = $result->result();
		}	

		return $leaves;
	}

	function get_sub_oot( $approver_id = 0 , $class_name = "" )
	{
		$this->db->where('class_name',$class_name);
		$module_id=$this->db->get('module')->row_array();

		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('employee_oot.deleted',0);

		$this->db->where('employee_oot.form_status_id', 2);
		$this->db->where('user.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_oot', 'employee_oot.employee_id = user.employee_id');

		$this->db->join('form_approver','employee_oot.employee_oot_id = form_approver.record_id');

		$this->db->select('employee_oot.datetime_from, user.lastname, user.firstname, employee_oot.employee_oot_id, employee_oot.date_created, employee_oot.reason, employee_oot.approvers, employee_oot.approver_status, employee_oot.form_status_id, form_approver.module_id, employee_oot.employee_oot_id as keyfield_val');
		$this->db->order_by('employee_oot.date', 'ASC');
		$result = $this->db->get('user');

		$oot = array();

		if ($result->num_rows() > 0) {
			$oot = $result->result();
		}	
		return $oot;
	}

	function get_sub_obt( $approver_id = 0 , $class_name="")
	{
		$this->db->where('class_name',$class_name);
		$module_id=$this->db->get('module')->row_array();

		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('employee_obt.deleted',0);

		$this->db->where('employee_obt.form_status_id', 2);
		$this->db->where('user.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_obt', 'employee_obt.employee_id = user.employee_id');

		$this->db->join('form_approver','form_approver.record_id = employee_obt.employee_obt_id');

		$this->db->select('employee_obt.date_from, user.lastname, user.firstname, employee_obt.employee_obt_id, employee_obt.date_created, employee_obt.reason, employee_obt.approvers, employee_obt.approver_status, employee_obt.form_status_id, form_approver.module_id, employee_obt.employee_obt_id as keyfield_val');
		$this->db->order_by('employee_obt.date_from', 'ASC');
		$result = $this->db->get('user');

		$obt = array();

		if ($result->num_rows() > 0) {
			$obt = $result->result();
		}	
		return $obt;
	}

	function get_sub_out( $approver_id = 0 , $class_name="")
	{
		$this->db->where('class_name',$class_name);
		$module_id=$this->db->get('module')->row_array();

		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('employee_out.deleted',0);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_out.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_out', 'employee_out.employee_id = user.employee_id');

		$this->db->join('form_approver','form_approver.record_id = employee_out.employee_out_id');

		$this->db->select('employee_out.date , user.lastname, user.firstname, employee_out.employee_out_id, employee_out.date_created, employee_out.reason, employee_out.approvers, employee_out.approver_status, employee_out.form_status_id, form_approver.module_id, employee_out.employee_out_id as keyfield_val');
		$this->db->order_by('employee_out.date', 'ASC');
		$result = $this->db->get('user');

		$out = array();

		if ($result->num_rows() > 0) {
			$out = $result->result();
		}	
		return $out;
	}


	function get_sub_et( $approver_id = 0 , $class_name="")
	{
		$this->db->where('class_name',$class_name);
		$module_id=$this->db->get('module')->row_array();

		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('employee_et.deleted',0);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_et.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_et', 'employee_et.employee_id = user.employee_id');

		$this->db->join('form_approver','form_approver.record_id = employee_et.employee_et_id');

		$this->db->select('employee_et.datelate, user.lastname, user.firstname, employee_et.employee_et_id, employee_et.date_created, employee_et.reason, employee_et.approvers, employee_et.approver_status, employee_et.form_status_id, form_approver.module_id, employee_et.employee_et_id as keyfield_val');
		$this->db->order_by('employee_et.datelate', 'ASC');
		$result = $this->db->get('user');
		$out = array();

		if ($result->num_rows() > 0) {
			$out = $result->result();
		}	

		return $out;
	}

	function get_sub_dtrp( $approver_id = 0 , $class_name="")
	{
		$this->db->where('class_name',$class_name);
		$module_id=$this->db->get('module')->row_array();

		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('employee_dtrp.deleted',0);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_dtrp.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_dtrp', 'employee_dtrp.employee_id = user.employee_id');

		$this->db->join('form_approver','form_approver.record_id = employee_dtrp.employee_dtrp_id');

		$this->db->select('employee_dtrp.date, user.lastname, user.firstname, employee_dtrp.employee_dtrp_id, employee_dtrp.date_created, employee_dtrp.reason, employee_dtrp.approvers, employee_dtrp.approver_status, employee_dtrp.form_status_id, form_approver.module_id, employee_dtrp.employee_dtrp_id as keyfield_val');
		$this->db->order_by('employee_dtrp.date', 'ASC');
		$result = $this->db->get('user');

		$dtrp = array();

		if ($result->num_rows() > 0) {
			$dtrp = $result->result();
		}	
		return $dtrp;
	}

	function get_sub_cws( $approver_id = 0 , $class_name="")
	{
		$this->db->where('class_name',$class_name);
		$module_id=$this->db->get('module')->row_array();

		$this->db->where('form_approver.approver',$approver_id);
		$this->db->where('form_approver.focus',1);
		$this->db->where('form_approver.status',2);
		$this->db->where('form_approver.module_id',$module_id['module_id']);
		$this->db->where('employee_cws.deleted',0);

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_cws.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_cws', 'employee_cws.employee_id = user.employee_id');

		$this->db->join('form_approver','form_approver.record_id = employee_cws.employee_cws_id');

		$this->db->select('employee_cws.date_to, user.lastname, user.firstname, employee_cws.employee_cws_id, employee_cws.date_created, employee_cws.reason, employee_cws.approvers, employee_cws.approver_status, employee_cws.form_status_id, form_approver.module_id, employee_cws.employee_cws_id as keyfield_val');
		$this->db->order_by('employee_cws.date_from', 'ASC');
		$result = $this->db->get('user');

		$dtrp = array();

		if ($result->num_rows() > 0) {
			$dtrp = $result->result();
		}	
		return $dtrp;
	}

	function get_subordinate_oot( $user_id = 0 ){

		$this->db->where('employee_oot.form_status_id', 2);
		$this->db->where('user.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_oot', 'employee_oot.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_oot.employee_oot_id, employee_oot.date_created, employee_oot.reason, employee_oot.approvers, employee_oot.approver_status, employee_oot.form_status_id');
		
		$result = $this->db->get('user');

		$oot = array();

		if ($result->num_rows() > 0) {
			$oot = $result->result();
		}	
		return $oot;
	}

	function get_subordinate_obt( $user_id = 0 ){

		$this->db->where('employee_obt.form_status_id', 2);
		$this->db->where('user.deleted', 0);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_obt', 'employee_obt.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_obt.employee_obt_id, employee_obt.date_created, employee_obt.reason, employee_obt.approvers, employee_obt.approver_status, employee_obt.form_status_id');
		
		$result = $this->db->get('user');

		$obt = array();

		if ($result->num_rows() > 0) {
			$obt = $result->result();
		}	
		return $obt;
	}


	function get_subordinate_out( $user_id = 0 ){
		
		$this->db->where('user.deleted', 0);
		$this->db->where('employee_out.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_out', 'employee_out.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_out.employee_out_id, employee_out.date_created, employee_out.reason, employee_out.approvers, employee_out.approver_status, employee_out.form_status_id');
		
		$result = $this->db->get('user');

		$out = array();

		if ($result->num_rows() > 0) {
			$out = $result->result();
		}	
		return $out;
	}

	function get_subordinate_et( $user_id = 0 ){
		

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_et.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_et', 'employee_et.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_et.employee_et_id, employee_et.date_created, employee_et.reason, employee_et.approvers, employee_et.approver_status, employee_et.form_status_id');
		$result = $this->db->get('user');

		$out = array();

		if ($result->num_rows() > 0) {
			$out = $result->result();
		}	

		return $out;
	}

	function get_subordinate_dtrp( $user_id = 0 ){
		

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_dtrp.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_dtrp', 'employee_dtrp.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_dtrp.employee_dtrp_id, employee_dtrp.date_created, employee_dtrp.reason, employee_dtrp.approvers, employee_dtrp.approver_status, employee_dtrp.form_status_id');
		
		$result = $this->db->get('user');

		$dtrp = array();

		if ($result->num_rows() > 0) {
			$dtrp = $result->result();
		}	
		return $dtrp;
	}

	function get_subordinate_cws( $user_id = 0 ){
		

		$this->db->where('user.deleted', 0);
		$this->db->where('employee_cws.form_status_id', 2);
		$this->db->join('user_position', 'user.position_id = user_position.position_id');
		$this->db->join('employee', 'employee.user_id = user.user_id');
		$this->db->join('employee_cws', 'employee_cws.employee_id = user.employee_id');
		$this->db->select('user.lastname, user.firstname, employee_cws.employee_cws_id, employee_cws.date_created, employee_cws.reason, employee_cws.approvers, employee_cws.approver_status, employee_cws.form_status_id');
		
		$result = $this->db->get('user');

		$dtrp = array();

		if ($result->num_rows() > 0) {
			$dtrp = $result->result();
		}	
		return $dtrp;
	}

	function get_ppa() 
	{
		$tables = array('IGS', 'EA', 'PIDP');

        $this->db->where_in('approver', $this->user->user_id);
        $approver_user = $this->db->get('employee_appraisal_approver');

		$ppa_tables['hideIDP']['visibility'] = false;
		
        if (($approver_user && $approver_user->num_rows() > 0) || $this->is_superadmin) {
			array_push($tables, 'IDP');
			$ppa_tables['hideIDP']['visibility'] = true;
        }

		foreach ($tables as $table) {
			switch ($table) {
				case 'IGS':
					// $this->db->where('period_status = 1');
					$this->db->where('appraisal_planning_period.deleted',0);
					$this->db->order_by('date_from','desc');
					$result1 = $this->db->get('appraisal_planning_period');
					
					$ctr = 0;
					if ( $result1 && $result1->num_rows() > 0 ){
						foreach($result1->result() as $appraisal_planning_period){
							$period_id = $appraisal_planning_period->planning_period_id;

							// if (!$this->is_superadmin) {
								$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
								$subordinates = $this->system->get_employee_all_reporting_to($this->userinfo['user_id']);//$this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
								$this->_subordinate_id = array();
										
								foreach ($subordinates as $subordinate) {
									$this->_subordinate_id[] = $subordinate;
								}

								$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_approver 
										WHERE module_id = (SELECT module_id FROM hr_module WHERE CODE = 'appraisal_planning')
										AND approver_employee_id = ".$this->userinfo['user_id']." 
										AND deleted = 0");
						
								if ($result && $result->num_rows() > 0){
									foreach ($result->result() as $row) {
										if (!in_array($row->employee_id, $this->_subordinate_id)){
											$this->_subordinate_id[] = $row->employee_id;
										}
									}
								}
							// }

							$employees = array();
							$get_subordinate_circle = $this->_subordinate_id;
							

							$this->db->where('appraisal_planning_period.planning_period_id',$period_id);
							$this->db->where('appraisal_planning_period.deleted',0);
							$result = $this->db->get('appraisal_planning_period');

							$appraisal_planning_period_row = $result->row_array();

							if ($result && $result->num_rows() > 0){

								$appraisal_planning_period_employee = explode(',',$result->row()->employee_id);

								foreach( $appraisal_planning_period_employee as $employee_info ){
									if( ( in_array($employee_info, $get_subordinate_circle) || $employee_info == $this->userinfo['user_id'] ) || ( $this->user_access[$this->module_id]['post']) ){

										array_push($employees,$employee_info);
										
									}
								}
							}

							if( count($employees) <= 0 ){
								$employees_imp = 0;
							}
							else{
								//$employees_imp = implode(',',$employees);
								$ppa_tables[$table][$ctr]['count'] = 1;//$user_count;
								$ppa_tables[$table][$ctr]['planning_period_id'] = $appraisal_planning_period->planning_period_id;
								$ppa_tables[$table][$ctr]['planning_period'] = $appraisal_planning_period->planning_period;
								$ppa_tables[$table][$ctr]['planning_date_from'] = $appraisal_planning_period->date_from;
								$ppa_tables[$table][$ctr]['planning_date_to'] = $appraisal_planning_period->date_to;
								$ppa_tables[$table][$ctr]['planning_mid_date_from'] = $appraisal_planning_period->mid_date_from;
								$ppa_tables[$table][$ctr]['planning_mid_date_to'] = $appraisal_planning_period->mid_date_to;
								$ppa_tables[$table][$ctr]['status'] = $appraisal_planning_period->period_status;

								$ctr++;	
							}

/*							$qry_count = "SELECT COUNT(*) as count
											FROM hr_user
											LEFT JOIN hr_employee ON hr_employee.employee_id = hr_user.employee_id
											LEFT JOIN hr_user t1 ON t1.user_id = hr_user.user_id
											LEFT JOIN hr_user_position t2 ON t2.position_id=hr_user.position_id
											LEFT JOIN hr_employee_type t3 ON t3.employee_type_id=hr_employee.employee_type
											LEFT JOIN hr_employment_status t4 ON t4.employment_status_id=hr_employee.status_id
											LEFT JOIN hr_user_company_department t5 ON t5.department_id=hr_user.department_id
											LEFT JOIN hr_user_company_division ON hr_user_company_division.division_id = hr_user.division_id
											LEFT JOIN hr_employee_appraisal_planning ON hr_employee_appraisal_planning.employee_id = hr_user.employee_id && hr_employee_appraisal_planning.appraisal_planning_period_id = ".$period_id."
											WHERE hr_user.deleted = 0 AND 1 AND hr_user.user_id IN (".$employees_imp.") ";
							
							$qry_count_result = $this->db->query($qry_count);
							$user_count = 0;

							if ($qry_count_result && $qry_count_result->num_rows() > 0){
								$user_count = $qry_count_result->row()->count;
							}
							
							$ppa_tables[$table][$ctr]['count'] = 1;//$user_count;
							$ppa_tables[$table][$ctr]['planning_period_id'] = $appraisal_planning_period->planning_period_id;
							$ppa_tables[$table][$ctr]['planning_period'] = $appraisal_planning_period->planning_period;
							$ppa_tables[$table][$ctr]['planning_date_from'] = $appraisal_planning_period->date_from;
							$ppa_tables[$table][$ctr]['planning_date_to'] = $appraisal_planning_period->date_to;
							$ppa_tables[$table][$ctr]['planning_mid_date_from'] = $appraisal_planning_period->mid_date_from;
							$ppa_tables[$table][$ctr]['planning_mid_date_to'] = $appraisal_planning_period->mid_date_to;
							$ppa_tables[$table][$ctr]['status'] = $appraisal_planning_period->period_status;

							$ctr++;*/
						}
					}
					else
					{
						$ppa_tables[$table][$ctr]['count'] = 0;
						$ppa_tables[$table][$ctr]['planning_period_id'] = -1;
						$ppa_tables[$table][$ctr]['planning_period'] = "No Record Found!";
						$ppa_tables[$table][$ctr]['planning_date_from'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['planning_date_to'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['planning_mid_date_from'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['planning_mid_date_to'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['status'] = ' ';
					}

					break;
				case 'EA':

					// $this->db->where('employee_appraisal_period_status_id = 1');
					$this->db->select('( SELECT COUNT(*) FROM hr_employee_appraisal_planning WHERE status = 6 AND appraisal_planning_period_id = hr_employee_appraisal_period.planning_period_id ) AS count');
					$this->db->select('appraisal_comment AS comment, employee_appraisal_period.planning_period_id, planning_period, employee_appraisal_period.date_from, employee_appraisal_period.date_to, employee_appraisal_period_status_id');
					$this->db->join('appraisal_planning_period','employee_appraisal_period.planning_period_id = appraisal_planning_period.planning_period_id');
					$this->db->where('employee_appraisal_period.deleted', 0);
					$this->db->order_by('date_from','desc');
					$result1 = $this->db->get('employee_appraisal_period');

					$ctr = 0;
					if ( $result1 && $result1->num_rows() > 0 ){
						foreach($result1->result() as $employee_appraisal_period){
							$period_id = $employee_appraisal_period->planning_period_id;

							$div_hid = ' division_head_id IS NOT NULL ';

							// if (!$this->is_superadmin) {
								$emp = $this->db->get_Where('employee', array('employee_id' => $this->user->user_id ))->row();
								$subordinates = $this->system->get_employee_all_reporting_to($this->userinfo['user_id']);//$this->hdicore->get_subordinates($this->userinfo['position_id'], $emp->rank_id, $this->user->user_id);
								$this->_subordinate_id = array();
										
								foreach ($subordinates as $subordinate) {
									$this->_subordinate_id[] = $subordinate;
								}

								$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_approver 
										WHERE module_id = (SELECT module_id FROM hr_module WHERE CODE = 'employee_appraisal')
										AND approver_employee_id = ".$this->userinfo['user_id']." 
										AND deleted = 0");

								if ($result && $result->num_rows() > 0){
									foreach ($result->result() as $row) {
										if (!in_array($row->employee_id, $this->_subordinate_id)){
											$this->_subordinate_id[] = $row->employee_id;
										}
									}
								}

								$div_hid = ' division_head_id IS NOT NULL ';
							// }

							$employees = array();
							$get_subordinate_circle = $this->_subordinate_id;
							$this->db->where('appraisal_planning_period.planning_period_id',$period_id);
							$this->db->where('appraisal_planning_period.deleted',0);
							$result = $this->db->get('appraisal_planning_period');
							$appraisal_planning_period_row = $result->row_array();

							if ($result && $result->num_rows() > 0){
								$appraisal_planning_period_employee = explode(',',$result->row()->employee_id);

								foreach( $appraisal_planning_period_employee as $employee_info ){
									if( ( in_array($employee_info, $get_subordinate_circle) || $employee_info == $this->userinfo['user_id'] ) || ( $this->user_access[$this->module_id]['post']) ){
										array_push($employees,$employee_info);
										
									}
								}
							}

							if( count($employees) <= 0 ){
								$employees_imp = 0;
							}
							else{
								$employees_imp = implode(',',$employees);
							}

							$qry_count = "SELECT COUNT(*) AS count
											FROM hr_user
											LEFT JOIN hr_employee ON hr_employee.employee_id=hr_user.employee_id
											LEFT JOIN hr_user t1 ON t1.user_id=hr_user.user_id
											LEFT JOIN hr_employee_type t2 ON t2.employee_type_id=hr_employee.employee_type
											LEFT JOIN hr_employment_status t3 ON t3.employment_status_id=hr_employee.status_id
											LEFT JOIN hr_project_name t4 ON t4.project_name_id=hr_user.employee_id
											LEFT JOIN hr_user_position t5 ON t5.position_id=hr_user.position_id
											LEFT JOIN hr_user_company_department t6 ON t6.department_id=hr_user.department_id
											LEFT JOIN hr_user_company_division ON hr_user_company_division.division_id = hr_user.division_id
											LEFT JOIN hr_employee_appraisal_bsc ON hr_employee_appraisal_bsc.employee_id = hr_user.employee_id && hr_employee_appraisal_bsc.appraisal_period_id = ".$period_id."
											WHERE hr_user.deleted = 0 AND ".$div_hid ." AND hr_user.user_id IN (".$employees_imp.") ";

							$ppa_tables[$table][$ctr]['count'] = 1;//$this->db->query($qry_count)->row()->count;
							$ppa_tables[$table][$ctr]['planning_period_id'] = $employee_appraisal_period->planning_period_id;
							$ppa_tables[$table][$ctr]['planning_period'] = $employee_appraisal_period->planning_period;
							$ppa_tables[$table][$ctr]['planning_date_from'] = $employee_appraisal_period->date_from;
							$ppa_tables[$table][$ctr]['planning_date_to'] = $employee_appraisal_period->date_to;
							$ppa_tables[$table][$ctr]['planning_mid_date_from'] = $appraisal_planning_period->mid_date_from;
							$ppa_tables[$table][$ctr]['planning_mid_date_to'] = $appraisal_planning_period->mid_date_to;
							$ppa_tables[$table][$ctr]['status'] = $employee_appraisal_period->employee_appraisal_period_status_id;
							$ctr++;
						}
					}
					else
					{
						$ppa_tables[$table][$ctr]['count'] = 0;
						$ppa_tables[$table][$ctr]['planning_period_id'] = -1;
						$ppa_tables[$table][$ctr]['planning_period'] = "No Record Found!";
						$ppa_tables[$table][$ctr]['planning_date_from'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['planning_date_to'] = '0000-00-00';
							$ppa_tables[$table][$ctr]['planning_mid_date_from'] = '0000-00-00';
							$ppa_tables[$table][$ctr]['planning_mid_date_to'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['status'] = '';
					}
					break;
				case 'PIDP':
					$this->db->where('individual_development_plan.deleted', 0);
					$this->db->where('individual_development_plan.employee_id', $this->userinfo['user_id']);
					$result1 = $this->db->get('individual_development_plan');

					$ctr = 0;
					if ( $result1 && $result1->num_rows() > 0 ){
						foreach($result1->result() as $individual_development_plan){
							$individual_development_plan_id = $individual_development_plan->individual_development_plan_id;

							$this->db->where('user.employee_id', $individual_development_plan->employee_id);
							$_user_profile = $this->db->get('user')->row();

							$result = $this->db->query($qry_count);
							$count = 0;
							if ($result && $result->num_rows() > 0){
								$count = $result->row()->count;
							}

							$ppa_tables[$table][$ctr]['count'] = $count;
							$ppa_tables[$table][$ctr]['planning_period_id'] = $individual_development_plan->individual_development_plan_id;
							$ppa_tables[$table][$ctr]['planning_period'] = $_user_profile->lastname.', '.$_user_profile->firstname.' '.$_user_profile->middleinitial;
							$ppa_tables[$table][$ctr]['planning_date_from'] = $individual_development_plan->date_created;
							$ppa_tables[$table][$ctr]['planning_date_to'] = '';
							$ppa_tables[$table][$ctr]['status'] = $individual_development_plan->idp_status;
							$ctr++;
						}
					}
					else
					{
						$ppa_tables[$table][$ctr]['count'] = 0;
						$ppa_tables[$table][$ctr]['planning_period_id'] = -1;
						$ppa_tables[$table][$ctr]['planning_period'] = "No Record Found!";
						$ppa_tables[$table][$ctr]['planning_date_from'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['planning_date_to'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['status'] = '';
					}
					break;
				case 'IDP':
					$result = $this->db->query("SELECT * FROM {$this->db->dbprefix}employee_approver 
							WHERE module_id = (SELECT module_id FROM hr_module WHERE CODE = 'idp')
							AND approver_employee_id = ".$this->userinfo['user_id']." 
							AND deleted = 0");

					$employees = array();
					if ($result && $result->num_rows() > 0){
						foreach ($result->result() as $row) {
							array_push($employees, $row->employee_id);
						}
					}

					if( count($employees) <= 0 ){
						$employees_imp = 0;
					}
					else{
						$employees_imp = implode(',',$employees);
					}

					$this->db->where('individual_development_plan.deleted', 0);
					$this->db->where('individual_development_plan.idp_status', 'For Approval');
					
					if(!$this->is_superadmin){
						$this->db->where('individual_development_plan.employee_id IN ('.$employees_imp.')');
					}

					$result1 = $this->db->get('individual_development_plan');

					$ctr = 0;
					if ( $result1 && $result1->num_rows() > 0 ){
						foreach($result1->result() as $individual_development_plan){
							$individual_development_plan_id = $individual_development_plan->individual_development_plan_id;

							$this->db->where('user.employee_id', $individual_development_plan->employee_id);
							$_user_profile = $this->db->get('user')->row();

							$count = 0;

							if ($this->db->query($qry_count) && $this->db->query($qry_count)->num_rows() > 0){
								$count = $this->db->query($qry_count)->row()->count;
							}

							$ppa_tables[$table][$ctr]['count'] = $count;
							$ppa_tables['hideIDP']['count'] = $result1->num_rows();
							$ppa_tables[$table][$ctr]['planning_period_id'] = $individual_development_plan->individual_development_plan_id;
							$ppa_tables[$table][$ctr]['planning_period'] = $_user_profile->lastname.', '.$_user_profile->firstname.' '.$_user_profile->middleinitial;
							$ppa_tables[$table][$ctr]['planning_date_from'] = $individual_development_plan->date_created;
							$ppa_tables[$table][$ctr]['planning_date_to'] = '';
							$ppa_tables[$table][$ctr]['status'] = $individual_development_plan->idp_status;
							$ctr++;
						}
					}
					else
					{
						$ppa_tables[$table][$ctr]['count'] = 0;
						$ppa_tables['hideIDP']['count'] = 0;
						$ppa_tables[$table][$ctr]['planning_period_id'] = -1;
						$ppa_tables[$table][$ctr]['planning_period'] = "No Record Found!";
						$ppa_tables[$table][$ctr]['planning_date_from'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['planning_date_to'] = '0000-00-00';
						$ppa_tables[$table][$ctr]['status'] = '';
					}
					break;
			}		
		}

		return $ppa_tables;
	}	

	function get_update_personal($user_id = 0){

		if( !empty( $user_id ) ) 


		$this->db->where('employee_update.employee_id' , $user_id);
		$this->db->join('user', 'user.employee_id = employee_update.employee_id', 'left');
		$result = $this->db->get('employee_update');

		$update_p = array();

		if ($result->num_rows() > 0) {
			$update_p = $result->result();
		}	

		return $update_p;
	}

	function get_update_subordinates($user_id, $position_id)
	{
		$emp_approver = $this->_is_employee_approver($user_id, 123);
		if($emp_approver && count($emp_approver) > 0) {
			foreach($emp_approver as $row) {
				$subs[] = $row['employee_id'];
			}
		}

        $approver  = $this->system->is_module_approver(123, $position_id);
		if($approver && count($approver) > 0){
			foreach( $approver as $row ){
				$this->db->where('position_id',$row->position_id);
				$emp_id=$this->db->get('user')->result_array();
				foreach($emp_id as $id)
				{
					$have_emp_approver = $this->_have_employee_approver($id['employee_id'], $this->module_id);
					if(!$have_emp_approver)
						$subs[] = $id['employee_id'];
				}
			}
		}

		if(count($subs) > 0)
		{
			$this->db->where_in('employee_update.employee_id', $subs);
			$this->db->where('employee_update_status_id', 1);
			$this->db->join('user', 'employee_update.employee_id = user.employee_id', 'left');
			$result = $this->db->get_where('employee_update');

			return $result->result();

		}
	}

    private function _is_employee_approver($employee_id, $module_id)
    {
    	$this->db->select('employee_id');
    	$result = $this->db->get_where('employee_approver', array('module_id' => $module_id, 'approver_employee_id' => $employee_id, 'deleted' => 0));
    	if($result->num_rows() > 0)
    		return $result->result_array();
    	else
    		return false;
    }

	function can_approve( $rec ) {
		
		if( $rec->form_status_id == 2 && $this->user_access[$this->module_id]['approve'] == 1){
			$approver = $this->db->get_where('leave_approver', array('leave_id' => $rec->employee_leave_id, 'approver' => $this->user->user_id));
			if( $approver->num_rows() == 1 ){
				$approver = $approver->row();
				if( $approver->status == 2 ){
					return true;
				}
			}

			return false;
		}
		return false;
	}

	function can_decline( $rec ) {
		if( $rec->form_status_id == 2 && $this->user_access[$this->module_id]['decline'] == 1){
			$approver = $this->db->get_where('leave_approver', array('leave_id' => $rec->employee_leave_id, 'approver' => $this->user->user_id));
			if( $approver->num_rows() == 1 ){
				$approver = $approver->row();
				if( $approver->status == 2 ){
					return true;
				}
			}

			return false;
		}
		return false;
	}

    function can_approve_forms( $rec ) {
        
        if( $rec->form_status_id == 2 && $this->user_access[$this->module_id]['approve'] == 1){
            $key_field = $this->key_field;
            $approver = $this->db->get_where('form_approver', array('module_id' => $rec->module_id, 'record_id' => $rec->keyfield_val, 'approver' => $this->user->user_id));
            if( $approver->num_rows() == 1 ){
                $approver = $approver->row();
                if( $approver->status == 2 ){
                    return true;
                }
            }

            return false;
        }
        return false;
    }

    function can_decline_forms( $rec ) {
        if( $rec->form_status_id == 2 && $this->user_access[$this->module_id]['decline'] == 1){
            $key_field = $this->key_field;
            $approver = $this->db->get_where('form_approver', array('module_id' => $rec->module_id, 'record_id' => $rec->keyfield_val, 'approver' => $this->user->user_id));
            if( $approver->num_rows() == 1 ){
                $approver = $approver->row();
                if( $approver->status == 2 ){
                    return true;
                }
            }

            return false;
        }
        return false;
    }
}

?>
