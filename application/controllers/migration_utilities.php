<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_utilities extends CI_Controller {

	function __construct() {
		parent::__construct();
	}	

	/**
	 * default 
	 * @return void
	 */
	function index() {		
		//connect to db1: source
		$db_1['hostname'] = 'hdi-sap';
		$db_1['username'] = 'resource';
		$db_1['password'] = 'password';
		$db_1['database'] = 'hr.openaccess.1';
		$db_1['dbdriver'] = 'mysql';
		$db_1['dbprefix'] = "hr_";
		$db_1['pconnect'] = FALSE;
		$db_1['db_debug'] = FALSE;
		$db_1['cache_on'] = FALSE;
		$db_1['cachedir'] = "";
		$db_1['char_set'] = "utf8";
		$db_1['dbcollat'] = "utf8_general_ci";

		//connect to db2: target
		$db_2['hostname'] = 'hdi-sap';
		$db_2['username'] = 'resource';
		$db_2['password'] = 'password';
		$db_2['database'] = 'hr.openaccess';
		$db_2['dbdriver'] = 'mysql';
		$db_2['dbprefix'] = "hr_";
		$db_2['pconnect'] = FALSE;
		$db_2['db_debug'] = FALSE;
		$db_2['cache_on'] = FALSE;
		$db_2['cachedir'] = "";
		$db_2['char_set'] = "utf8";
		$db_2['dbcollat'] = "utf8_general_ci";


		$db_from = $this->load->database($db_1, TRUE);
		$db_to = $this->load->database($db_2, TRUE);	
		
		/* user tables */
		/*
		$tables = array(
			'user',
			'user_company',
			'user_company_department',
			'user_config',
			'user_position',
			'user_position_approvers',
			'employee',
			'employee_accountabilities',
			'employee_affiliates',
			'employee_alternate_contact',
			'employee_approver',
			'employee_benefit',
			'employee_department',
			'employee_division',
			'employee_education',
			'employee_employment',
			'employee_family'
		); */

		/* recruitment tables */
		$tables = array(
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
			'recruitment_manpower_candidates_schedule',
			'recruitment_preemployment',
			'recruitment_preemployment_background',
			'recruitment_preemployment_buddy',
			'recruitment_preemployment_checklist',
			'recruitment_preemployment_onboarding',
			'recruitment_preemployment_schoolverification'
		);


		foreach( $tables as $table ) {
			echo 'Table: '. $table;
			
			if (!$db_to->table_exists($table)) {
				echo ' : <b>ATTENTION: Non-existent table.</b><br />';
				continue;
			}			

			$fields = $db_to->list_fields( $table );

			if (!$db_from->table_exists($table)) {
				echo ': <b>ATTENTION: Table does not exist for connection 2</b>';
				echo '<br/>';
				continue;
			}
			echo '<br/>';
			$show_nonexistent = true;

			$db_to->query("SET FOREIGN_KEY_CHECKS = 0");
			$db_to->truncate( $table );

			$data = $db_from->get( $table );
			if( $db_to->_error_message() == "" ){
				echo 'rows:'.$data->num_rows().'...<br/>';
				
				if( $data->num_rows() > 0 ){
					$insert = array();
					$insert_batch = array();

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
						
						/*
						if( $table == "employee" || $table == "recruitment_applicant" ){
							$insert['pres_address1'] = $row->address;
							$insert['perm_address1'] = $row->provincial_address;	
						}*/

						$insert_batch[] = $insert;
					}

					if( !empty( $insert_batch ) && sizeof( $insert_batch ) > 0 ){
						$db_to->query("SET FOREIGN_KEY_CHECKS = 0");
						$db_to->insert_batch($table, $insert_batch);
						if( $db_to->_error_message() != "" ) echo $db_to->_error_message() .'<br/>';
						echo "<br />insert:";
					}
					else{
						echo 'Nothing to insert!<br/>';
					}					
				}
			}
			else{
				echo $db_to->_error_message() .'<br/>';
			}
		}

	}	

}

/* End of file */
/* Location: system/application */
