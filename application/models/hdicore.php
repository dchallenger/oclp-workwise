<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class hdicore extends MY_Model
{
	var $onlineuser = array();
	function __construct()
	{
		parent::__construct();
	}
	
	function get_online_users(){
		$onlineuser = array();
		$this->db->order_by('last_activity', 'DESC');
		$session = $this->db->get('sessions');
		if($session->num_rows() > 0){
			$count_me_in = false;
			foreach($session->result() as $row){
				$now = strtotime(date("Y-m-d H:i:s"));
				$last_activity = round((abs($now - $row->last_activity) / 60), 2);
				$userdata = unserialize($row->user_data);
				if( isset($userdata['user']) ){
					$user = $userdata['user'];
					
					if( !isset( $onlineuser[$user->user_id] ) &&  $user->user_id != $this->user->user_id ){	
						$onlineuser[$user->user_id]['userinfo'] = $this->_get_userinfo( $user->user_id );
						$onlineuser[$user->user_id]['activity'] = "active";
					}

					if( $last_activity >= 5 ) $onlineuser[$user->user_id]['activity'] = "5idle";
					if( $last_activity >= 30 ) $onlineuser[$user->user_id]['activity'] = "30idle";
					if( $last_activity >= 60 ){
						if(isset($onlineuser[$user->user_id])) unset($onlineuser[$user->user_id]);
						if( empty( $row->delete_counter ) )
							$this->db->update('sessions', array('delete_counter' => 1), array('session_id' => $row->session_id));
						else if( $row->delete_counter < 5 ){
							$this->db->update('sessions', array('delete_counter' => ($row->delete_counter+1)), array('session_id' => $row->session_id));
						}
						else{
							$this->db->delete('sessions', array('session_id' => $row->session_id));				
							log_message('error', 'Logout user with user_id = '. $user->user_id .' because activity is idle. Last Activity: '.date("Y-m-d H:i:s", $row->last_activity));
						}
					}
					else{
						$this->db->update('sessions', array('delete_counter' => 0), array('session_id' => $row->session_id));
					}
				}
				else{
					if( $last_activity >= 60 ){
						$this->db->delete('sessions', array('session_id' => $row->session_id));
						log_message('error', 'Logout user because session data is missing. Last Activity: '.$last_activity.' minutes ago ('.$row->last_activity.')');
					}
				}
			}
		}
		return $onlineuser;
	}
	
	function get_offline_users( $online_users = array() ){
		$offlineuser = array();
		$where = array(
			'deleted' => 0,
			'inactive' => 0,
		);
		
		$users = $this->db->get_where('user', $where);
		foreach($users->result() as $user){
			if( !in_array($user->user_id, array_keys($online_users) ) && $user->user_id != $this->user->user_id ){
				$offlineuser[$user->user_id]['userinfo'] = $this->_get_userinfo( $user->user_id );
				$offlineuser[$user->user_id]['activity'] = "30idle";
			}
		}
		return $offlineuser;
	}

	/**
	 * check wether user has overall access to modu;e
	 * @return void
	 */
	public function _visibility_check(){
		//redirect if user doesnt have visibility access on module
		if((!isset($this->user_access[$this->module_id]['visible']) || $this->user_access[$this->module_id]['visible'] != 1)
			|| ($this->router->fetch_method() == 'index' && $this->user_access[$this->module_id]['list'] != 1) // Make sure user has list access.
			){
			if( IS_AJAX ){
				header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden"); exit();
			}else{
				$this->session->set_flashdata('flashdata', 'You dont have sufficient access to the requested module. <span class="red">Please contact the System Administrator.</span>');
				redirect(base_url());
			}
		}
	}
	
	function _set_breadcrumbs()
	{
		//breadcrumbs, browse history, exclude ajax and dashboard
		$breadcrumbs = $this->session->userdata('breadcrumbs');
		if( !$breadcrumbs ) $this->session->set_userdata('breadcrumbs', array());
		if( $this->module != "dashboard" ){
			if( $this->uri->rsegment(4) || ( isset($_POST) && is_array($_POST) && sizeof($_POST) > 0) ){
				$breadcrumb_index = sizeof($breadcrumbs);
		
				$no_crumbs = array('print_contract', 'print_record');
				if( $this->method == "index" || $this->method == "" || in_array( $this->method, $no_crumbs )){
					//destroy old browse history
					$breadcrumbs = array();
					$this->session->unset_userdata('breadcrumbs');
					$breadcrumbs[0]['url'] = base_url().$this->module_link;
					$breadcrumb_index = 1;
					//$breadcrumbs[$breadcrumb_index ]['url'] = base_url().$this->module_link;
				}
				else
					$breadcrumbs[$breadcrumb_index ]['url'] = base_url().$this->module_link.'/'.$this->router->fetch_method();
				
				if( $breadcrumb_index >= 1 ){
					if($breadcrumbs[$breadcrumb_index ]['url'] != $breadcrumbs[$breadcrumb_index - 1]['url']){
						//create array of $_POST
						if( $this->uri->rsegment(4) ){
							$breadcrumbs[$breadcrumb_index ]['post_data']['record_id'] = $this->uri->rsegment(4);
						}
						if( isset( $_POST ) && sizeof($_POST) > 0  && $this->method != "index" ){
							foreach($_POST as $name => $value) {
								$breadcrumbs[$breadcrumb_index ]['post_data'][$name] = $value;
							}				
						}
					}
					else{
						// page refresh with resubmit
						unset($breadcrumbs[$breadcrumb_index ]);
					}
				}
			}
			else{
				//destroy old browse history
				$breadcrumbs = array();
				$this->session->unset_userdata('breadcrumbs');
				$breadcrumbs[0]['url'] = base_url().$this->module_link;	
			}
		}
		else if( $this->module == "Dashboard" && $this->method != "page_not_found" ){
			//destroy old browse history
			$breadcrumbs = array();
			$this->session->unset_userdata('breadcrumbs');
			$breadcrumbs[0]['url'] = base_url().$this->module_link;	
		}
		$this->session->set_userdata('breadcrumbs', $breadcrumbs);
		
	}
	
	// check ig user login/email exists
	function _verify_login( $login = '', $password = '', $where = '', $tomd5 = true )
	{
		if(!empty( $where ) || is_array( $where ) ) $this->db->where( $where );
		if(!empty( $password )) {
			if( $tomd5 )
				$password = md5($password);
			
			$this->db->where( "password = '{$password}'" );
		}
		if(!empty( $login )) $this->db->where( "(login = '{$login}' OR email = '{$login}')" );
		$this->db->select('user_id, login, password, email, photo');
		$login = $this->db->get('user');
		if( $login->num_rows() == 1 )
			return $login->row();
		else
			return false;
	}
	
	function _get_userinfo( $user_id = 0 ){
		$this->db->select('user_id, login, password, salutation, lastname, firstname, middlename, middleinitial, nickname, aux, user.birth_date, user.company_id, company, user.department_id, department, user.position_id, position, user_position.position_level_id, position_level, role_id, user.team_id, team, email, photo, CONCAT("themes/", theme ) theme, theme rtheme, sex');
		$this->db->join('user_team', 'user_team.team_id = user.team_id', 'left');
		$this->db->join('user_company', 'user_company.company_id = user.company_id', 'left');
		$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
		$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
		$this->db->join('user_position_level', 'user_position_level.position_level_id = user_position.position_level_id', 'left');
		$this->db->where(array('user_id' => $user_id));
		$login = $this->db->get('user');
		if( $login->num_rows() == 1 )
			return $login->row();
		else
			return false;
	}
	
	//get children of modules
	function get_module_child( $parent = 0 , $depth = 0 )
	{
		$this->db->order_by('sequence');
		$modules = $this->db->get_where('module', array('deleted' => 0, 'parent_id' => $parent));
		$module = array();
		if($modules->num_rows() > 0){
			foreach($modules->result() as $row){
				$module[] = array(
					'module_id' => $row->module_id,
					'name' => $row->short_name,
					'short_name' => $row->short_name,
					'long_name' => $row->long_name,
					'inactive' => $row->inactive,
					'depth' => $depth,
					'children' => $this->get_module_child( $row->module_id, $depth + 1 )
				);
			}
		}
		else{
			$module = false;
		}

		return $module; 
	}
	
	function add_user( $login, $password ){
		//check if login already is registerd
		$where = array('deleted' => 0);
		$login_exist = $this->_verify_login( $login, '', $where );
		if( !$login_exist ){
			$user = array(
				'login' => $login,
				'password' => md5( $password )
			);
			$this->db->insert('user', $user);
			return $this->db->insert_id();
		}
		else{
			return false;
		}
	}
	
	function _create_user_access_file( $user ){
		$module_access = array();
		
		//get prodiles associated to users role
		$profiles = $this->db->get_where('role_profile', array('role_id' => $user->role_id));
		if($profiles->num_rows() > 0){
			//get user override access
			$override = $this->db->get_where('user_access', array('user_id' => $user->user_id));
			if($override->num_rows() > 0) $override_access = $override->result_array();
			foreach($profiles->result() as $profile){
				//get profile access
				$this->db->select('module_access');
				$mod_access = $this->db->get_where('profile', array('profile_id' => $profile->profile_id))->row();
				$mod_access = !empty( $mod_access->module_access ) ? unserialize( $mod_access->module_access) : array();
				foreach( $mod_access as $mod_id => $access ){
					foreach($access as $action => $value){
						if(!isset($module_access[$mod_id][$action]))
							$module_access[$mod_id][$action] = $mod_access[$mod_id][$action];
						else{
							if($mod_access[$mod_id][$action] == 1) $module_access[$mod_id][$action] = 1;
						}
						
						if(isset($override_access[$mod_id][$action])) $module_access[$mod_id][$action] = $override_access[$mod_id][$action];
					}
				}
			}
		}
		
		return $module_access;
	}

	function _get_role_settings( $role_id ){
		$settings['sensitivity'] = array();
	
		//get profiles associated to users role
		$profiles = $this->db->get_where('role_profile', array('role_id' => $role_id));
		if($profiles->num_rows() > 0){
			foreach($profiles->result() as $profile){
				//get profile access
				$this->db->select('record_sensitivity');
				$record_sensitivity = $this->db->get_where('profile', array('profile_id' => $profile->profile_id))->row();
				$record_sensitivity = !empty( $record_sensitivity->record_sensitivity ) ? unserialize( $record_sensitivity->record_sensitivity) : array();
				foreach( $record_sensitivity as $mod_id => $sensitivity ){
					foreach($sensitivity as $sensitivity_id => $value){
						if( !isset($settings['sensitivity'][$mod_id][$sensitivity_id]) ) $settings['sensitivity'][$mod_id][$sensitivity_id] = $value;
					}
				}	
			}	
		}
	
		return $settings;
	}

	function _create_navigation( $parent_id, $user_access ){
		//get child of parent
		$this->db->select('module_id, class_name, class_path, short_name, long_name, sm_icon, big_icon, is_visible, show_icon_only, description');
		$this->db->order_by("sequence", "asc");
		$query = $this->db->get_where('module', array('inactive'=>'0', 'parent_id' => $parent_id, 'deleted' => 0));
		$result = $query->result_array();
		$nav = array();
		if(!empty($result)){
			foreach($result as $index => $row){
				if(isset($user_access[$row['module_id']]) && $user_access[$row['module_id']]['visible'] === 1){
					$nav[$row['module_id']] = array(
						"class_name" => $row['class_name'],
						"short_name" => $row['short_name'],
						"long_name" => $row['long_name'],
						"description" =>  $row['description'],
						"link" => $row['class_path'],
						"sm_icon" => $row['sm_icon'],
						"big_icon" => $row['big_icon'],
						"is_visible" => $row['is_visible'],
						"show_icon_only" => $row['show_icon_only'],
						"access" =>  $user_access[$row['module_id']],
						"child" =>  $this->_create_navigation( $row['module_id'], $user_access )
					);
				}
			}
		}
		
		return $nav;
	}
	
	/**
	 * Return the module specified by $code.
	 * 
	 * @param mixed $code
	 * @return mixed 
	 *	Model object
	 *	FALSE on error.
	 */
	function get_module($code) {		
		if (!isset($code) || $code == '') {
			return false;
		}
		
		if (is_numeric($code)) {
			$this->db->where('module_id', $code);
		} else {		
			$this->db->where('code', $code);					
		}
		
		$this->db->limit(1);
		
		$result = $this->db->get('module');
		
		if (isset($result) && $result->num_rows() > 0) {
			return $result->row();
		} else {			
			return false;
		}
	}
	
	/**
	 * Return true or false if module exists.
	 * 
	 * @param string $code
	 * @return bool
	 */
	function module_exists($code) {
		if ($this->get_module($code)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Return true or false if module is active/visible.
	 * 
	 * @param string $code
	 * @return bool
	 */
	function module_active($code) {
		$module = $this->get_module($code);
		if ($module && $module->inactive == 0) {
			return true;
		} else {
			return false;
		}
	}
	
	function get_role_profile( $role_id ){
		//get profiles associated with tole
		$profiles = array();
		$assoc_profile = $this->db->get_where('role_profile', array('deleted' => 0, 'role_id' => $role_id));
		if($assoc_profile->num_rows() > 0){
			foreach($assoc_profile->result() as $profile){
				$profiles[] = $profile->profile_id;
			}
		}
		
		return $profiles;
	}

	/**
	 * Get approvers based on position id given.
	 * 
	 * @return object
	 */
	function get_approvers($position_id = null, $module_id = null) {
		if (is_null($position_id)) {
			$position_id = $this->userinfo['position_id'];
		}

		if (is_null($module_id)) {
			$module_id = $this->module_id;
		}

		$this->db->where('module_id', $module_id);
		$this->db->where('deleted', 0);
		$this->db->where('inactive', 0);
		$this->db->limit(1);

		$module = $this->db->get('module')->row();

		$response['admins'] = null;
		$response['msg']    = 'Position approvers not defined. Contact the administrator.';

		// Get approvers based on notification settings.
		if ($module->setup_notification == 1) {
			$notification_approvers = $this->_get_position_approvers_notification($position_id, $module_id);
		}
		
		$approvers = array();

		if ($notification_approvers) {
			$notification_approvers_id = $notification_approvers['ids'];
		} else {
			$notification_approvers_id = array();
		}

		// Getting default based on reporting to
		$this->db->where('position_id', $position_id);
		$this->db->select('reporting_to');
		$this->db->limit(1);
		$this->db->where('deleted', 0);

		$position = $this->db->get('user_position');

		if ($position && $position->num_rows() > 0) {
			$position = $position->row();

			// Get position hierarchy for dropdown.
			$hierarchy = $this->_get_position_hierarchy($position->reporting_to);

			$response['admins'] = $hierarchy;

			if (count($notification_approvers_id) > 0) {
				foreach ($hierarchy as $pos) {
					if ($key = array_search($pos['position_id'], $notification_approvers_id)) {
						unset($notification_approvers_id[$key]);
					}
				}
				$this->db->select('user.*, user_position.position');				
				$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
				$this->db->where_in('user.position_id', $notification_approvers_id);
				$this->db->where('user.deleted', 0);
				$notification_approvers = $this->db->get('user');

				$response['admins'] = array_merge($response['admins'], $notification_approvers->result_array());
			}

			$this->db->where('position_id', $position->reporting_to);
			$this->db->where('deleted', 0);

			$reporting_to = $this->db->get('user');

			$response['success'] = TRUE;

			if ($position->reporting_to == 0) {
				$response['msg'] = 'Please set "Reporting To" under User Position settings.';
				$response['success'] = FALSE;
			} else if ($reporting_to->num_rows() > 0 && !isset($default) ) {
				$default = $reporting_to->row_array();
			} else if ( !isset($default) ){
				$response['msg'] = 'No user with approver position defined. Contact the administrator.';
				$response['success'] = FALSE;
			}
		} else {
			$response['msg'] = 'Please set "Reporting To" under User Position settings.';
			$response['success'] = FALSE;
		}

		return $response;
	}

	private $_hierarchy = array(), $_position_hierarchy = array();

    function _get_position_hierarchy($position_id = 1) {
        $this->db->where('position_id', $position_id);
        $this->db->where('deleted', 0);
        $this->db->limit(1);

        $position = $this->db->get('user_position');

        if ($position->num_rows() > 0) {            
            $this->db->select(array('user.*', 'user_position.position'));
            $this->db->join('user_position', 'user_position.position_id = user.position_id');
            $this->db->where('user.position_id', $position_id);
            $this->db->where('user.deleted', 0);
            $this->db->where('user.user_id <>', 1);
            $result = $this->db->get('user');

            if ($result->num_rows() > 0) {
                $this->_position_hierarchy = array_merge($this->_position_hierarchy, $result->result_array());
            }

            if ($position->row()->reporting_to > 0) {
                $this->_get_position_hierarchy($position->row()->reporting_to);
            }
        }

        return $this->_position_hierarchy;
    }

	private function _get_position_approvers_notification($position_id, $module_id) {
		$this->db->where('position_id', $position_id);
		$this->db->where('module_id', $module_id);
		$this->db->where('approver', 1);

		$approver_position = $this->db->get('user_position_approvers');
		$approver_position_id = array();

		if ($approver_position->num_rows() > 0) {
			foreach ($approver_position->result() as $approver) {
				$approver_position_id[] = $approver->approver_position_id;
			}
						
				// Reversing the array so the arrangement is as seen on notification settings.
			return array('ids' => $approver_position_id);			
		}

		return false;
	}

	/**
	 * Loops through approvers until approver reaches zero.
	 * 
	 * @param  int $position_id
	 * @return array
	 */
	private function _get_approver_hierarchy($position_id) {
		$this->db->where('position_id', $position_id);
		$this->db->limit(1);

		$position = $this->db->get('user_position');

		$this->_hierarchy[] = $position_id;

		if ($position->num_rows() > 0) {
			if ($position->row()->reporting_to > 0 && $position->row()->reporting_to != $position_id) {
				return $this->_get_approver_hierarchy($position->row()->reporting_to);
			}
		}

		return $this->_hierarchy;
	}	

	/**
	 * Return subordinates of position.
	 * 
	 * @param  int $position_id
	 * @return object
	 */
	function get_subordinates( $position_id, $rank_id, $user_id )
	{	    
		return $this->_get_subordinate_hierarchy($position_id, array(), $rank_id, $user_id);
	}

	private function _get_subordinate_hierarchy($position_id, $subordinates = array(), $rank_id = 0, $user_id = 0)
	{
		if( $this->user_access[$this->module_id]['post'] != 1 && !($this->is_superadmin || $this->is_admin) ){
			if ($position_id != 0){
				$this->db->where('reporting_to', $position_id);
				$this->db->where('deleted', 0);
				$under_position	= $this->db->get('user_position');	
			}

			if(!empty($rank_id) ) $rank = $this->db->get_where('user_rank', array('job_rank_id' => $rank_id))->row();
			if ($under_position && $under_position->num_rows() > 0) {
				foreach ($under_position->result() as $sub) {
					$this->db->where('user.position_id', $sub->position_id);
					$this->db->where('user.deleted', 0);

					$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
					$this->db->select('user.*, employee.rank_id, user_company_department.department, user_company_department.department_id, position');
					$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
					$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
					$this->db->order_by('user_company_department.department');
					$users = $this->db->get('user');

					if ($users && $users->num_rows() > 0) {
						$subordinates = array_merge($subordinates, $users->result_array());
					}

					if( (isset($rank) && $rank->first_level_subs == 0) || !isset($rank)){
						$this->db->where('reporting_to', $sub->position_id);
						$this->db->where('deleted', 0);
						$next = $this->db->get('user_position');

						if ($next && $next->num_rows() > 0) {					
							$subordinates = $this->_get_subordinate_hierarchy($sub->position_id, $subordinates);					
						}
					}
				}
			}

			if($user_id != 0){
				//get employees directly reporting to user
				$this->db->select('user.*, employee.rank_id, user_company_department.department, user_company_department.department_id, position');
				$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
				$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
				$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
				$this->db->where("FIND_IN_SET(".$user_id.", ".$this->db->dbprefix."employee.reporting_to)");
				$this->db->where('user.deleted', 0);
				$this->db->where('user.inactive', 0);
				$this->db->where('employee.resigned', 0);
				$users = $this->db->get('user');
			}

			if ($users && $users->num_rows() > 0) {
				if (CLIENT_DIR == 'firstbalfour' || CLIENT_DIR == 'oams'){
					$subordinates = array_merge($subordinates, $users->result_array());
					foreach ($users->result() as $row) {
						$subordinates = $this->_get_subordinate_hierarchy(false, $subordinates, $row->rank_id, $row->user_id);
					}					
				}
				else{
					$subordinates = array_merge($subordinates, $users->result_array());
				}
			}
		}
		else{
			if ($this->user_access[$this->module_id]['project_hr'] == 1 && !($this->is_superadmin || $this->is_admin)){
				if($user_id != 0){
					//get employees directly reporting to user
					$this->db->select('user.*, employee.rank_id, user_company_department.department, user_company_department.department_id, position');
					$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
					$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
					$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
					$this->db->where("FIND_IN_SET(".$user_id.", ".$this->db->dbprefix."employee.reporting_to)");
					$this->db->where('user.deleted', 0);
					$this->db->where('user.inactive', 0);
					$this->db->where('employee.resigned', 0);
					$users = $this->db->get('user');
				}

				if ($users && $users->num_rows() > 0) {
					$subordinates = array_merge($subordinates, $users->result_array());
				}

				if ($users && $users->num_rows() > 0){
					foreach ($users->result() as $row) {
						$subordinates = $this->_get_subordinate_hierarchy(false, $subordinates, $row->rank_id, $row->user_id);
					}
				}
			}
			else{
				$this->db->where('user.user_id !=', 1);
				$this->db->where('user.deleted', 0);
				$this->db->where('employee.resigned', 0);
				$this->db->join('user_company_department', 'user_company_department.department_id = user.department_id', 'left');
				$this->db->select('user.*, user_company_department.department, user_company_department.department_id, position');
				$this->db->join('user_position', 'user_position.position_id = user.position_id', 'left');
				$this->db->join('employee', 'employee.employee_id = user.employee_id', 'left');
				$this->db->order_by('user_company_department.department');	
				$users = $this->db->get('user');
				$subordinates = $users->result_array();
			}
		}

		return $subordinates;
	}

	function compare_date( $date1 = '', $date2 = '', $round = true ){
		$date1 =  date("Y-m-d", strtotime( $date1 ));
		$date2 =  date("Y-m-d", strtotime( $date2 ));

		if( $date1 == $date2 ) $response->relation = "equal";
		if( $date1 < $date2 ) $response->relation = "earlier";
		if( $date1 > $date2 ) $response->relation = "later";

		$response->difference = floor( ( ( strtotime( $date2 ) - strtotime( $date1 ) ) / (60 * 60 * 24) ) );
		
		if( $round == true ){

			$response->difference_months =  round( ( ( strtotime( $date2 ) - strtotime( $date1 ) ) / ( 3600 * 24 * 30 ) ) );

		}
		else{

			$response->difference_months =  ( ( strtotime( $date2 ) - strtotime( $date1 ) ) / ( 3600 * 24 * 30 ) );

		}

		return $response;	
	}

	function get_time_difference( $date1, $date2 ){
		$t1 = StrToTime ( $date1 );
		$t2 = StrToTime ( $date2 );
		$difference = $t2 - $t1;
		$diff->hours = $difference / ( 60 * 60 );
		return $diff;
	}

	function get_file_upload_data( $upload_id = 0 ){
		$file = $this->db->get_where('file_upload', array('upload_id' => $upload_id));
		if($file->num_rows == 1){
			$file = $file->row();
			$this->load->helper('file');
			if( file_exists( $file->upload_path ) ){
				$fileinfo = pathinfo( $file->upload_path );
				$fileinfo['dbrow'] = $file;
				$file_moreinfo = get_file_info( $file->upload_path );
				return array_merge($fileinfo, $file_moreinfo);
			}
			else{
				return substr( basename( $file->upload_path ) , 15) . " is missing.";
			}
		}
		else{
			return "File cannot be retrieved.";
		}
	}

	/* to check on both hr_employee table and hr_employee_dtr_setup if employee is flexi.
	 * parameter: employee_id(int),
	 * return boolean
	 */
	function is_flexi($employee_id = false)
	{
		if($employee_id)
		{
			$this->db->join('employee', 'employee_dtr_setup.employee_id = employee.employee_id', 'left');
			$this->db->where( $this->db->dbprefix.'employee.employee_id = '.$employee_id.'
							  AND ('.$this->db->dbprefix.'employee.flexible_shift = 1 
							  OR '.$this->db->dbprefix.'employee_dtr_setup.flexible_shift = 1)'
							  );
			$flexi = $this->db->get('employee_dtr_setup');
			if($flexi && $flexi->num_rows() > 0)
				return true;
			else
				return false;
		} else
			return 'No Employee_id is set';
	}

	function use_double_shift($employee_id = false, $cdate = false, $cdate_is_tomorrow = false)
	{
		if($employee_id && $cdate)
		{
			$day_ctr = 0;

			do {

				$date_used = date('Y-m-d', strtotime('+ '.$day_ctr.' day '.$cdate));

				$day = strtolower(date('l', strtotime($date_used)));

				$is_rd = $this->system->get_employee_worksched($employee_id, $date_used);

				if(isset($is_rd->has_cws) || isset($is_rd->has_cal_shift))
					$day_shift_id = $is_rd->shift_id;
				else
					$day_shift_id = $is_rd->{$day . '_shift_id'};

				if($day_ctr == 0 && ($day_shift_id == 0 || $day_shift_id == 1) && !$cdate_is_tomorrow)
					return false;

				$day_ctr++;

			} while($day_shift_id == 0 || $day_shift_id == 1);

			$result = $this->db->get_where('employee_ds', array('employee_id' => $employee_id, 'date_used' => $date_used, 'form_status_id' => 3));

			if($result && $result->num_rows() > 0)
			{
				if(!$cdate_is_tomorrow)
				{
					// $this->db->select('double_shift_is_used');

					$ds_satisfied = $this->db->get_where('employee_dtr', array('employee_id' => $employee_id, 'date' => $result->row()->date));

					if($ds_satisfied && $ds_satisfied->num_rows() > 0)
					{
						$ds_satisfied = $ds_satisfied->row();

						if(!$ds_satisfied->double_shift_is_used)
							return false;
						else
							return true;
					}
				} else
					return true;
			} else
				return false;
		}
	}

	// check employee if floating and no recall
	function check_if_floating($employee = false)
	{
		if($employee)
		{
			$qry = "SELECT *
					FROM {$this->db->dbprefix}employee_floating ef
					LEFT JOIN {$this->db->dbprefix}user u
						ON ef.employee_id = u.employee_id
					WHERE ef.deleted = 0
						AND ef.date_from IS NOT NULL
						AND ef.date_to IS NULL
						AND u.deleted = 0
						AND ef.employee_id = {$employee}
					";

			$result = $this->db->query($qry);

			if($result && $result->num_rows() > 0)
				return $result->row();
			else
				return false;
		} else 
			return false;
	}

	// check employee if floating, recall within period
	function check_if_floating_period($employee = false, $date = false)
	{
		if($employee && $date)
		{
			$qry = "SELECT *
					FROM {$this->db->dbprefix}employee_floating ef
					  LEFT JOIN {$this->db->dbprefix}user u
					    ON ef.employee_id = u.employee_id
					WHERE ef.deleted = 0
					    AND u.deleted = 0
					    AND ef.employee_id = {$employee}
					    AND ((ef.date_from IS NOT NULL
					    	  AND ef.date_from <= '{$date}'
					          AND DATE_SUB(ef.date_to, INTERVAL 1 DAY) IS NULL)
					          OR 
					          ('{$date}' BETWEEN ef.date_from
					          AND DATE_SUB(ef.date_to, INTERVAL 1 DAY)))
					";

			$result = $this->db->query($qry);

			if($result && $result->num_rows() > 0)
				return $result->row();
			else
				return false;
		}
	}

	function get_all_floating($date = false)
	{
		if($date) 
		{

			$this->db->join('employee','employee.employee_id = user.employee_id', 'left');
			$emps = $this->db->get_where('user', array('employee.deleted' => 0, 'inactive' => 0, 'resigned' => 0))->result();

			$data = array(0);

			foreach($emps as $emp)
			{
				$qry = "SELECT *
						FROM {$this->db->dbprefix}employee_floating ef
						  LEFT JOIN {$this->db->dbprefix}user u
						    ON ef.employee_id = u.employee_id
						WHERE ef.deleted = 0
						    AND u.deleted = 0
						    AND ef.employee_id = {$emp->employee_id}
						    AND ((ef.date_from IS NOT NULL
						          AND ef.date_to IS NULL
						          AND ef.date_from <= '{$date}')
						          OR ('{$date}' BETWEEN ef.date_from
						              AND DATE_SUB(ef.date_to, INTERVAL 1 DAY)))
						";

				$result = $this->db->query($qry);

				if($result && $result->num_rows() > 0)
					$data[] = $result->row()->employee_id;
			}

			if(count($data))
				return $data;
			else
				return false;
		}
	}
}

?>