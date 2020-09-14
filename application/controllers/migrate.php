<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migrate extends CI_Controller {
	function __construct() {
		parent::__construct();
	}

	/**
	 * default 
	 * @return void
	 */
	function index(){
		$tables = array(
			'employee',
			'employee_da',
			'employee_dailytimerecord',
			'employee_dtr',
			'employee_dtr_raw',
			'employee_ir',
			'employee_update',
			'file_upload',
			'recruitment_applicant',
			'recruitment_applicant_education',
			'recruitment_applicant_employment',
			'recruitment_applicant_family',
			'recruitment_applicant_history',
			'recruitment_applicant_references',
			'recruitment_applicant_skills',
			'recruitment_applicant_training',
			'recruitment_candidate_job_offer',
			'recruitment_candidate_job_offer_benefit',
			'recruitment_candidates_appraisal',
			'recruitment_manpower',
			'recruitment_manpower_candidate',
			'recruitment_manpower_settings',
			'recruitment_preemployment',
			'recruitment_preemployment_checklist',
			'recruitment_preemployment_onboarding',
			'role',
			'role_profile',
			'user',
			'user_company',
			'user_config',
			'user_position',
			'user_position_approvers'
		);

		foreach( $tables as $table ){
			echo 'Table: '. $table .'<br/>'; 
			
			$fields = $this->db->list_fields( $table );
			$show_nonexistent = true;

			$this->db->query("SET FOREIGN_KEY_CHECKS = 0");
			$this->db->truncate( $table );

			$data = $this->db->get( $table.'_m' );
			if( $this->db->_error_message() == "" ){
				if( $data->num_rows() > 0 ){
					$insert = array();
					foreach( $data->result() as $row ){
						$temp_fields = $fields;
						if( $show_nonexistent ){
							foreach($fields as $index => $field){
								if( isset( $row->$field ) ){
									$insert[$field]	= $row->$field;
									unset( $temp_fields[$index] );
								}
							}
							
							if( sizeof( $temp_fields ) ) echo "Non-existent fields: " . implode(', ', $temp_fields) . '<br/>';
							$show_nonexistent = false;

						}
						else{
							if( !empty($insert) && sizeof( $insert ) > 0 ){
								foreach( $insert as $column => $value ){
									$insert[$column] = $row->$column;	
								}
							}
						}
						
						if( $table == "employee" || $table == "recruitment_applicant" ){
							$insert['pres_address1'] = $row->address;
							$insert['perm_address1'] = $row->provincial_address;	
						}

						if( !empty( $insert ) && sizeof( $insert  ) > 0 ){
							$this->db->query("SET FOREIGN_KEY_CHECKS = 0");
							$this->db->insert($table, $insert);
							if( $this->db->_error_message() != "" ) echo $this->db->_error_message() .'<br/>';
						}
						else{
							echo 'Nothing to insert!<br/>';
						}
					}
				}
			}
			else{
				echo $this->db->_error_message() .'<br/>';
			}
		}

	}	
}

/* End of file */
/* Location: system/application */
