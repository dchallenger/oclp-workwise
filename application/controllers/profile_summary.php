<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profile_summary extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();		

	}

	public function index(){

			

	}

	public function summarize_role_access(){

		$role_result = $this->db->get_where('role',array( 'deleted' => 0 ))->result();

		foreach( $role_result as $role_record ){

			$data['profile'] = $role_record->role;
			$profile_assoc = explode( ',', $role_record->profile_assoc );
			$summarize_module_access = array();

			foreach( $profile_assoc as $profile_assoc_id ){

					$profile_result = $this->db->get_where('profile',array( 'profile_id' => $profile_assoc_id,  'deleted' => 0 ))->result();

					foreach( $profile_result as $profile_record ){

						$module_access = unserialize($profile_record->module_access);

						foreach( $module_access as $module_id => $access_rights ){

							if( array_key_exists( $module_id , $summarize_module_access ) ){

								$module_action = $this->db->get('module_action')->result();

								foreach( $module_action as $module_action_value ){

									if( $module_access[$module_id][$module_action_value->action] == 1 ){

										$summarize_module_access[$module_id][$module_action_value->action] = 1;
									
									}

								}
							}
							else{
								$summarize_module_access[$module_id] = $access_rights;
							}
						}

					}
			}

			$data['module_access'] = serialize($summarize_module_access);

			$this->db->insert('profile_sum',$data);

			echo "Successfully create profiles for the following roles: ";

			dbug($data['profile']);

		}

	}


}

?>