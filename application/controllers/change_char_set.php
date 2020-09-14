<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Process requests from cron.php
 *
 * Usage:
 * (Windows)
 * C:\xampp\php\php.exe <HRIS_DIR>cron.php -u <USERNAME> -p <PASSWORD> -m <METHOD>
 *
 * Note:
 * Methods called by cron.php should be declared as private and must begin with an underscore.
 *
 */

class Change_char_set extends CI_Controller
{

	// ------------------------------------------------------------------------
	
	public function __construct()
	{
		parent::__construct();		

		$this->load->add_package_path(MODPATH . CLIENT_DIR);

		// Reload the client.php config, this time the client packages have been added to
		// the config file paths, if any, this will override the default config/client.php
		$this->load->config('client');				
	}

	// ------------------------------------------------------------------------

	/**
	 * Checks user credentials and routes method to use.
	 * 
	 * @return void
	 */
	public function index()
	{		

		ini_set('memory_limit', '256M');
		$this->load->library('encrypt');
		$this->load->helper('file');

		//TARGET CHARSET
		$TARGET_CHARACTER_SET_NAME = "utf8";
		$TARGET_COLLATION_NAME = "utf8_general_ci";

		// create log
		$folder = 'logs/change_char_set';
		$log_file = $folder.'/'.date('Y-m-d').'_columns_altered.txt';
		if(!file_exists($folder)) 
			mkdir($folder, 0777, true);

//ALTER COLUMNS
		// log message
		$log_msg = date('Ymd H:i:s')." START CHANGE COLLATION COLUMN \r\n";
		write_file($log_file, $log_msg, 'a');

		echo "<br><br> CHANGE COLUMN COLLATION<br>";
		$select_qry = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
						WHERE TABLE_SCHEMA = '".$this->db->database."' 
						AND (CHARACTER_SET_NAME != '".$TARGET_CHARACTER_SET_NAME."'
						OR COLLATION_NAME != '".$TARGET_COLLATION_NAME."')";

		$qry = $this->db->query($select_qry);

		if( $qry->num_rows > 0 ){
			$count_columns = 0;
			foreach ($qry->result() as $row) {
			$count_columns++;
				$alter_table_qry = "ALTER TABLE `".$row->TABLE_NAME.
									"` MODIFY `".$row->COLUMN_NAME.
									"` ".$row->COLUMN_TYPE."
									 CHARACTER SET ".$TARGET_CHARACTER_SET_NAME.
									 " COLLATE ".
									 $TARGET_COLLATION_NAME;
			
				$this->db->query($alter_table_qry);
				echo "($count_columns) $alter_table_qry<br>";

			// log message
			$log_msg = date('Ymd H:i:s').' '.$alter_table_qry."  . \r\n";
			write_file($log_file, $log_msg, 'a');
			}

			echo $count_columns." Columns, ALTERED SUCCESFULLY!";

		}else{
			echo "No Columns to Alter";

			$log_msg = date('Ymd H:i:s')." NO COLUMNS TO ALTER \r\n";
			write_file($log_file, $log_msg, 'a');	
		}

		$log_file = $folder.'/'.date('Y-m-d').'_tables_altered.txt';
		if(!file_exists($folder)) 
			mkdir($folder, 0777, true);

//ALTER COLUMNS
		// log message
		$log_msg = date('Ymd H:i:s')." START CHANGE COLLATION TABLES \r\n";
		write_file($log_file, $log_msg, 'a');
		echo "<br><br> CHANGE TABLE COLLATION<br>";
		$select_qry = "SELECT * FROM INFORMATION_SCHEMA.TABLES
						WHERE TABLE_SCHEMA = '".$this->db->database."' 
						AND TABLE_TYPE='BASE TABLE'
						AND TABLE_COLLATION != '".$TARGET_COLLATION_NAME."'";

		$qry = $this->db->query($select_qry);

		if( $qry->num_rows > 0 ){
			$count_columns = 0;
			foreach ($qry->result() as $row) {
			$count_columns++;
				$alter_table_qry = "ALTER TABLE `".$row->TABLE_NAME.
									"` COLLATE ".
									 $TARGET_COLLATION_NAME;
			
				$this->db->query($alter_table_qry);
				echo "($count_columns) $alter_table_qry<br>";

			// log message
			$log_msg = date('Ymd H:i:s').' '.$alter_table_qry."  . \r\n";
			write_file($log_file, $log_msg, 'a');
			}

			echo $count_columns." TABLE, ALTERED SUCCESFULLY!";

		}else{
			echo "No table to Alter";

			$log_msg = date('Ymd H:i:s')." NO TABLE TO ALTER \r\n";
			write_file($log_file, $log_msg, 'a');	
		}


		// log message
		$log_msg = date('Ymd H:i:s')." END CHANGE \r\n";
		write_file($log_file, $log_msg, 'a');	
	}


}