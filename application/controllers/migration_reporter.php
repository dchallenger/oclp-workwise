<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_reporter extends CI_Controller {

	function __construct() {
		parent::__construct();
	}

	/**
	 * default 
	 * @return void
	 */
	function index(){

		echo 'Start Connecting...';
		echo '<br/>';
		
		//connect to db
		$dbconfig['hostname'] = 'hdione.com';
		$dbconfig['username'] = '';
		$dbconfig['password'] = '';
		$dbconfig['database'] = 'hdi-bodivanceshop';
		$dbconfig['dbdriver'] = 'mysql';
		$dbconfig['dbprefix'] = "";
		$dbconfig['pconnect'] = TRUE;
		$dbconfig['db_debug'] = FALSE;
		$dbconfig['cache_on'] = FALSE;
		$dbconfig['cachedir'] = "";
		$dbconfig['char_set'] = "utf8";
		$dbconfig['dbcollat'] = "utf8_general_ci";

		$data = $this->load->database($dbconfig, TRUE);	
	

		$rec = $data->query("SELECT 
				a.user_nicename, a.user_email, a.user_registered,
				b.meta_value, c.meta_value `address`, d.meta_value `personal`
				FROM wp_users a 
				INNER JOIN wp_usermeta b ON a.id=b.user_id AND b.meta_key='wp_capabilities' AND b.meta_value LIKE '%subscriber%'
				INNER JOIN wp_usermeta c ON a.id=c.user_id AND c.meta_key='user_address_info' 
				INNER JOIN wp_usermeta d ON a.id=d.user_id AND d.meta_key='user_personal_info' 
				ORDER BY a.user_nicename");
		
		if( $data->_error_message() == "" ){
		
			echo 'Record(s): ' . $rec->num_rows() . '<br/>';
			echo 'Creating: d:\registrants.csv' . '<br/>';
			
			$fp = fopen('d:\registrants.csv', 'w');

			$fields = array( 
							'Name', 
							'Email',
							'Registered',
							'Birthdate',
							'Occupation',
							'Organization',
							'Landline',
							'Mobile',
							'Address 1',
							'Address 2',
							'City',
							'Country'
					);
			fputcsv($fp, $fields);
			
			foreach( $rec->result() as $row ){
			
				//$add = 's:258:"a:8:{s:9:"user_add1";s:16:"18 mai thuc loan";s:9:"user_add2";s:2:"HP";s:9:"user_city";s:9:"Porsgrunn";s:10:"user_state";s:6:"Norway";s:12:"user_country";s:6:"Norway";s:15:"user_postalcode";s:0:"";s:8:"landline";s:8:"19282956";s:6:"mobile";s:10:"0509464764";};';
				$add = unserialize( $row->address );
				$address = unserialize( $add );
				
				$per = unserialize( $row->personal );
				$personal = unserialize( $per );
				
				$fields = array( 
								$row->user_nicename, 
								$row->user_email,
								$row->user_registered,
								$personal['birth_year'].'-'.$personal['birth_month'].'-'.$personal['birth_day'],
								trim($personal['occupation']),
								trim($personal['organization']),
								$address['landline'],
								$address['mobile'],
								trim($address['user_add1']),
								trim($address['user_add2']),
								trim($address['user_city']),
								trim($address['user_country'])
						);
				fputcsv($fp, $fields);
				//exit;
			}
			fclose($fp);
			
			echo 'Done.' . '<br/>';
			
		} else {
			echo $data->_error_message() .'<br/>';
		}
	}	
	
}

/* End of file */
/* Location: system/application */
