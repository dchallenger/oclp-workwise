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

class uploader_portal extends CI_Controller
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
		$this->load->helper('file');
		$this->load->helper('time_upload');

		// create log
		$folder = 'logs/biometric_logs_portal';
		$log_file = $folder.'/'.date('Y-m-d').'.txt';
		if(!file_exists($folder)) 
			mkdir($folder, 0777, true);

		
		// log message
		$log_msg = date('Ymd H:i:s')." START UPLOADING \r\n";
		write_file($log_file, $log_msg, 'a');

		$db2 = $this->load->database('ms_sql_portal', TRUE);

		$qry = $db2->query('SELECT * FROM VW_OTP_Attendances a WHERE CONVERT(VARCHAR,Date_Stamp,112) BETWEEN CONVERT(VARCHAR,GETDATE()-5,112) AND CONVERT(VARCHAR,GETDATE()+1,112)');

		if( $qry->num_rows > 0 ){
			$msdata = $qry->result();
		}

		$not_exist_biometrics = array();
		$records_read = 0;
		
		// log message
		$log_msg = date('Ymd H:i:s').' '.$qry->num_rows()." record(s) read. \r\n";
		write_file($log_file, $log_msg, 'a');

		$qry->free_result();

		$ctr = 1;
		foreach ($msdata as $row) {
			$this->db->where('biometric_id', $row->Employee_Number);
			$this->db->where('resigned', 0);
			$this->db->where('deleted', 0);
	
			$employee = $this->db->get('employee');

			if ($employee->num_rows() > 0) {

				$employee_id = $employee->row()->employee_id;

				//if (in_array($employee_id, array(369,536))) {

					$info = "Employee No - " . $row->Employee_Number ." Tiem In - ". $row->Time_In ." Time Out - ". $row->Time_Out ." \r\n";
					write_file($log_file, $info, 'a');

					$time_in1 = '';
					$time_out1 = '';

					$date = $row->Date_Stamp;
					if (trim($row->Time_In) !== '' && trim($row->Time_In) != '0000-00-00 00:00:00')
						$time_in1 = date('Y-m-d H:i:s',strtotime($row->Time_In));

					if (trim($row->Time_Out) !== '' && trim($row->Time_Out) != '0000-00-00 00:00:00')
						$time_out1 = date('Y-m-d H:i:s',strtotime($row->Time_Out));

					$where = array(
						'employee_id' => $employee_id,
						'date' => $date
					);

					$to_insert = array(
						'employee_id' => $employee_id,
						'date' => $date,
						'time_in1' => $time_in1,
						'time_out1' => $time_out1,
						'upload_by' => 2
					);						

					$to_update = array(
						'time_in1' => $time_in1,
						'time_out1' => $time_out1,
						'upload_by' => 2
					);	

					$exist_data = $this->db->get_where('employee_dtr', $where);

					if ($exist_data->num_rows() == 0) {
						$this->db->insert('employee_dtr', $to_insert);				
					}
					else {
						$this->db->where($where);
						$this->db->update('employee_dtr', $to_update);				
					}	

					$ctr++;
				//}

			} else {
				// log message
				$log_msg = date('Ymd H:i:s').' '.$row->Employee_Number." does not exist. \r\n";
				write_file($log_file, $log_msg, 'a');

			}		
		}

		// log message
		$log_msg = date('Ymd H:i:s').' '.$ctr." total record(s) updated. \r\n";
		write_file($log_file, $log_msg, 'a');

		// log message
		$log_msg = date('Ymd H:i:s')." END UPLOADING \r\n";
		write_file($log_file, $log_msg, 'a');	
	} 
}